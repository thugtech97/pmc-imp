<?php

namespace App\Http\Controllers\Ecommerce;

use App\Constants\ActionQueue;
use App\Exports\PurchaseAdviceReport;
use App\Helpers\ListingHelper;
use App\Http\Controllers\Controller;
use App\Services\History;
use App\Services\Notifier;
use App\Models\{ Permission, Page, Issuance, IssuanceItem, Department, ViewLog, User, Role };
use App\Models\Ecommerce\{ DeliveryStatus, SalesPayment, SalesHeader, SalesDetail, Product, InventoryRequest, InventoryRequestItems, PurchaseAdvice, PurchaseAdviceDetail };
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PurchaseAdviceController extends Controller
{
    private $searchFields = ['order_number', 'response_code', 'created_at', 'updated_at'];

    /** Why the document controls refuse — the same wording on the endpoint and on screen. */
    const PA_DOCUMENTS_LOCKED = 'Supporting documents can only be changed by the MCD Planner while the PA is for verification or on hold for re-edit.';

    /** Why the cancel control refuses — the same wording on the endpoint and on screen. */
    const PA_CANCEL_LOCKED = 'Only the MCD Planner can cancel a PA for SR, and only while it is still open.';

    /** Cancellation is terminal: the same wording on every endpoint that refuses. */
    const PA_CANCELLED_LOCKED = 'This PA has been cancelled and can no longer be updated.';

    public function __construct()
    {
        //Permission::module_init($this, 'sales_transaction');
    }

    private function nullableNumericInput(Request $request, string $key)
    {
        return $request->filled($key) ? $request->input($key) : null;
    }

    /*
    this line is brought to you by
    */
    public function index()
    {
        // This is the Purchasing Officer's delegation queue: it deliberately lists every
        // MRS for PA regardless of who it is assigned to. A Purchaser/Canvasser landing
        // here (e.g. from an older "MRS Assigned to You" notification) would see PAs held
        // by other canvassers, so send them to their own filtered queue instead.
        if ((int) optional(Auth::user())->role_id === 9) {
            return redirect()->route('purchaser.index');
        }

        $customConditions = [
            [
                'field' => 'status',
                'operator' => '=',
                'value' => 'active',
                'apply_to_deleted_data' => true
            ],
        ];


        $listing = new ListingHelper('desc', 10, 'order_number', $customConditions);
        $sales = $listing->simple_search(SalesHeader::class, $this->searchFields);

        $sales = SalesHeader::with('items.issuances')->withSum('issuances', 'qty')->where('id', '>', '0');
        if (isset($_GET['startdate']) && $_GET['startdate'] <> '') {
            $sales = $sales->where('created_at', '>=', $_GET['startdate']);
        }
        if (isset($_GET['enddate']) && $_GET['enddate'] <> '') {
            $sales = $sales->where('created_at', '<=', $_GET['enddate'] . ' 23:59:59');
        }
        if (isset($_GET['search']) && $_GET['search'] <> '') {
            $sales = $sales->where('order_number', 'like', '%' . $_GET['search'] . '%');
        }
        if (isset($_GET['customer_filter']) && $_GET['customer_filter'] <> '') {
            $sales = $sales->where('customer_name', 'like', '%' . $_GET['customer_filter'] . '%');
        }
        // Apply status filters based on final_status
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $statuses = $_GET['status'];
            $sales->where(function ($query) use ($statuses) {
                $query->whereHas('items', function ($subQuery) use ($statuses) {
                    $subQuery->havingRaw("
                        CASE
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) = SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'COMPLETED'
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) > 0 AND SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) > SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'PARTIAL'
                            ELSE 'UNSERVED'
                        END IN (" . implode(',', array_map(function ($status) {
                        return "'$status'"; }, $statuses)) . ")
                    ");
                });
            });
        }

        $sales = $sales->whereIn('status', [
            'APPROVED (MCD Approver) - PA for Delegation',
            '(For Purchasing Receival)'
        ])
            ->whereNull('received_at')
            ->where('for_pa', 1);

        // Undelegated PAs first. Admin has no personal queue here, so it keeps the
        // page's own default rather than losing the ordering altogether.
        $actionOrder = ActionQueue::orderCase(ActionQueue::MRS, ActionQueue::currentRoleName());
        if ($actionOrder === null) {
            $actionOrder = "CASE WHEN status = 'APPROVED (MCD Approver) - PA for Delegation' THEN 0 ELSE 1 END";
        }

        $sales = $sales->orderByRaw($actionOrder)->orderBy('approved_at', 'desc');

        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.index', compact('sales', 'filter', 'searchType', 'departments'));
    }

    public function view_mrs(Request $request, $id)
    {
        // Counterpart of index(): this screen carries the officer's assign/re-assign
        // controls, so it stays out of the Purchaser/Canvasser's reach.
        if ((int) optional(Auth::user())->role_id === 9) {
            return redirect()->route('purchaser.view_mrs', $id);
        }

        $sales = SalesHeader::where('id', $id)->first();
        $salesPayments = SalesPayment::where('sales_header_id', $id)->get();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id', $id)->get();
        $totalPayment = SalesPayment::where('sales_header_id', $id)->sum('amount');
        $totalNet = SalesHeader::where('id', $id)->sum('net_amount');
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();

        $purchasers = User::where('role_id', 9)->get();

        if ($totalNet <= $totalPayment)
            $status = 'PAID';
        else
            $status = 'UNPAID';
        return view('admin.purchasing.view', compact('sales', 'salesPayments', 'salesDetails', 'status', 'role', 'purchasers'));
    }

    public function create_pa(Request $request, $id)
    {
        $sales = SalesHeader::find($id);

        if (empty($sales)) {
            return back()->with('error', 'Something went wrong!');
        }

        $sales->update(["is_pa" => 1]);
        return redirect()->route('pa.index')->with('success', 'Purchase Advice created successfully!');
    }

    public function pa_list()
    {
        $customConditions = [
            [
                'field' => 'status',
                'operator' => '=',
                'value' => 'active',
                'apply_to_deleted_data' => true
            ],
        ];

        $listing = new ListingHelper('desc', 10, 'order_number', $customConditions);
        $sales = $listing->simple_search(SalesHeader::class, $this->searchFields);

        $sales = SalesHeader::with('items.issuances')->withSum('issuances', 'qty')->where('id', '>', '0');
        if (isset($_GET['startdate']) && $_GET['startdate'] <> '') {
            $sales = $sales->where('created_at', '>=', $_GET['startdate']);
        }
        if (isset($_GET['enddate']) && $_GET['enddate'] <> '') {
            $sales = $sales->where('created_at', '<=', $_GET['enddate'] . ' 23:59:59');
        }
        if (isset($_GET['search']) && $_GET['search'] <> '') {
            $sales = $sales->where('order_number', 'like', '%' . $_GET['search'] . '%');
        }
        if (isset($_GET['customer_filter']) && $_GET['customer_filter'] <> '') {
            $sales = $sales->where('customer_name', 'like', '%' . $_GET['customer_filter'] . '%');
        }
        // Apply status filters based on final_status
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $statuses = $_GET['status'];
            $sales->where(function ($query) use ($statuses) {
                $query->whereHas('items', function ($subQuery) use ($statuses) {
                    $subQuery->havingRaw("
                        CASE
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) = SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'COMPLETED'
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) > 0 AND SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) > SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'PARTIAL'
                            ELSE 'UNSERVED'
                        END IN (" . implode(',', array_map(function ($status) {
                        return "'$status'"; }, $statuses)) . ")
                    ");
                });
            });
        }
        /*
        this line is brought to you by
        */
        $sales = $sales->whereIn('status', ['RECEIVED FOR CANVASS (Purchasing Officer)'])->where('for_pa', 1)->where('is_pa', 1)->orderBy('id', 'desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.manage', compact('sales', 'filter', 'searchType', 'departments'));
    }

    public function purchaser_index()
    {
        $customConditions = [
            [
                'field' => 'status',
                'operator' => '=',
                'value' => 'active',
                'apply_to_deleted_data' => true
            ],
        ];


        $listing = new ListingHelper('desc', 10, 'order_number', $customConditions);
        $sales = $listing->simple_search(SalesHeader::class, $this->searchFields);

        $sales = SalesHeader::with('items.issuances', 'purchaseAdvice')->withSum('issuances', 'qty')->where('id', '>', '0');
        if (isset($_GET['startdate']) && $_GET['startdate'] <> '') {
            $sales = $sales->where('created_at', '>=', $_GET['startdate']);
        }
        if (isset($_GET['enddate']) && $_GET['enddate'] <> '') {
            $sales = $sales->where('created_at', '<=', $_GET['enddate'] . ' 23:59:59');
        }
        if (isset($_GET['search']) && $_GET['search'] <> '') {
            $search = $_GET['search'];
            $sales = $sales->where(function ($query) use ($search) {
                $query->where('order_number', 'like', '%' . $search . '%')
                      ->orWhereHas('purchaseAdvice', function ($q) use ($search) {
                          $q->where('pa_number', 'like', '%' . $search . '%');
                      });
            });
        }
        if (isset($_GET['customer_filter']) && $_GET['customer_filter'] <> '') {
            $sales = $sales->where('customer_name', 'like', '%' . $_GET['customer_filter'] . '%');
        }
        // Apply status filters based on final_status
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $statuses = $_GET['status'];
            $sales->where(function ($query) use ($statuses) {
                $query->whereHas('items', function ($subQuery) use ($statuses) {
                    $subQuery->havingRaw("
                        CASE
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) = SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'COMPLETED'
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) > 0 AND SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) > SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'PARTIAL'
                            ELSE 'UNSERVED'
                        END IN (" . implode(',', array_map(function ($status) {
                        return "'$status'"; }, $statuses)) . ")
                    ");
                });
            });
        }

        $sales = $sales->where('received_by', Auth::id())->where('for_pa', 1)->where('is_pa', 1)->where('status', '(For Purchasing Receival)')->orderBy('id', 'desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.purchaser_index', compact('sales', 'filter', 'searchType', 'departments'));
    }

    public function purchaser_received_index()
    {
        $customConditions = [
            [
                'field' => 'status',
                'operator' => '=',
                'value' => 'active',
                'apply_to_deleted_data' => true
            ],
        ];


        $listing = new ListingHelper('desc', 10, 'order_number', $customConditions);
        $sales = $listing->simple_search(SalesHeader::class, $this->searchFields);

        $sales = SalesHeader::with('items.issuances', 'purchaseAdvice')->withSum('issuances', 'qty')->where('id', '>', '0');
        if (isset($_GET['startdate']) && $_GET['startdate'] <> '') {
            $sales = $sales->where('created_at', '>=', $_GET['startdate']);
        }
        if (isset($_GET['enddate']) && $_GET['enddate'] <> '') {
            $sales = $sales->where('created_at', '<=', $_GET['enddate'] . ' 23:59:59');
        }
        if (isset($_GET['search']) && $_GET['search'] <> '') {
            $search = $_GET['search'];
            $sales = $sales->where(function ($query) use ($search) {
                $query->where('order_number', 'like', '%' . $search . '%')
                      ->orWhereHas('purchaseAdvice', function ($q) use ($search) {
                          $q->where('pa_number', 'like', '%' . $search . '%');
                      });
            });
        }
        if (isset($_GET['customer_filter']) && $_GET['customer_filter'] <> '') {
            $sales = $sales->where('customer_name', 'like', '%' . $_GET['customer_filter'] . '%');
        }
        // Apply status filters based on final_status
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $statuses = $_GET['status'];
            $sales->where(function ($query) use ($statuses) {
                $query->whereHas('items', function ($subQuery) use ($statuses) {
                    $subQuery->havingRaw("
                        CASE
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) = SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'COMPLETED'
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) > 0 AND SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) > SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'PARTIAL'
                            ELSE 'UNSERVED'
                        END IN (" . implode(',', array_map(function ($status) {
                        return "'$status'"; }, $statuses)) . ")
                    ");
                });
            });
        }

        $sales = $sales->where('received_by', Auth::id())->where('for_pa', 1)->where('is_pa', 1)->where('status', 'RECEIVED FOR CANVASS (Purchasing Officer)')->orderBy('id', 'desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.purchaser_index_received', compact('sales', 'filter', 'searchType', 'departments'));
    }

    public function purchaser_view(Request $request, $id)
    {
        $sales = SalesHeader::where('id', $id)->first();
        if (empty($sales)) {
            return redirect()->route('purchaser.index')->with('error', 'MRS not found.');
        }

        $user = User::find(Auth::id());

        // A canvasser may only open the MRS assigned to them — anything else belongs to
        // another canvasser's queue.
        if ((int) $user->role_id === 9 && (int) $sales->received_by !== (int) $user->id) {
            return redirect()->route('purchaser.index')
                ->with('error', 'That MRS is assigned to another purchaser/canvasser.');
        }

        $salesPayments = SalesPayment::where('sales_header_id', $id)->get();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id', $id)->get();
        $totalPayment = SalesPayment::where('sales_header_id', $id)->sum('amount');
        $totalNet = SalesHeader::where('id', $id)->sum('net_amount');
        $role = Role::where('id', $user->role_id)->first();

        $purchasers = User::where('role_id', 9)->get();

        if ($totalNet <= $totalPayment)
            $status = 'PAID';
        else
            $status = 'UNPAID';
        return view('admin.purchasing.purchaser_view', compact('sales', 'salesPayments', 'salesDetails', 'status', 'role', 'purchasers'));
    }

    public function receive_pa(Request $request)
    {
        //dd($request->all());
        $header_id = $request->sales_header_id;
        $h = SalesHeader::find($header_id);

        DB::beginTransaction();
        try {
            foreach ($h->items as $i) {
                $po_no = $request->input('po_no' . $i->id);
                $qty_ordered = $request->input('qty_ordered' . $i->id);
                $po_date_released = $request->input('po_date_released' . $i->id);
                $i->update(["po_no" => $po_no, "qty_ordered" => $qty_ordered, "po_date_released" => $po_date_released]);
            }

            $h->update([
                "response_code" => Auth::id(),
            ]);
            DB::commit();

            // Buffered per-line PO edits — write them now so the trail is complete
            // before the canvasser is redirected back to the MRS.
            History::flushItemChanges();

            return back()->with("success", "MRS request details updated.");
        } catch (\Exception $e) {
            DB::rollBack();
            History::discardItemChanges();
            return back()->with("error", "An error occurred: " . $e->getMessage());
        }
    }

    public function pa_action(Request $request, $id)
    {
        try {
            $mrs = SalesHeader::with('purchaseAdvice')->find($id);
            if (!$mrs) {
                return back()->with('error', 'MRS not found.');
            }
            $note = $request->query('note', '');
            if ($request->action == "hold-purchaser") {
                // Returned to the MCD Planner for re-edit. Clear received_at so aging stops and
                // updateIssuance() stops treating it as "already received", but KEEP received_by
                // so the planner's re-edit can route it straight back to this same canvasser
                // (skipping verify / approve / re-assignment). verified_at and approved_at are
                // preserved for the same reason — those stages are bypassed, not redone.
                // MRS and PA move together, so a half-held request can never be left behind.
                DB::transaction(function () use ($mrs, $note) {
                    History::context($mrs, [
                        'action'          => 'returned',
                        'title'           => 'Returned by the Purchasing Officer for MCD Planner re-edit',
                        'requestor_title' => 'On hold - with MCD Planner for re-edit',
                        'remarks'         => $note,
                    ]);
                    $mrs->update([
                        "status" => "HOLD (For MCD Planner re-edit)",
                        "purchaser_note" => $note,
                        "hold_by" => Auth::id(),
                        "received_at" => NULL,
                    ]);
                    // Mirror the hold onto the PA itself, otherwise it stays at "RECEIVED FOR
                    // CANVASS", keeps showing in the canvasser's PA queue (and stays printable),
                    // and the planner's PA list never flags it for re-edit.
                    if ($mrs->purchaseAdvice) {
                        History::context($mrs->purchaseAdvice, [
                            'action'          => 'returned',
                            'title'           => 'Returned by the Purchasing Officer for MCD Planner re-edit',
                            'requestor_title' => 'Purchase advice returned to the MCD Planner for revision',
                            'remarks'         => $note,
                        ]);
                        $mrs->purchaseAdvice->update([
                            "status" => "HOLD (For MCD Planner re-edit)",
                            "purchaser_remarks" => $note,
                            "received_at" => NULL,
                            "is_hold" => 1,
                        ]);
                    }
                });
                // Purchasing Officer returned it: notify the MCD Planner (who acts) and the requestor.
                Notifier::toRoleName('MCD Planner', [
                    'title'   => 'MRS Returned by Purchasing Officer',
                    'message' => "MRS #{$mrs->order_number} was held by the Purchasing Officer for re-edit." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => route('sales-transaction.view', $mrs->id),
                    'module'  => 'MRS',
                    'status'  => 'HOLD (For MCD Planner re-edit)',
                ]);
                Notifier::toUser($mrs->user_id, [
                    'title'   => 'MRS On Hold',
                    'message' => "Your MRS #{$mrs->order_number} was held by the Purchasing Officer and returned to the MCD Planner for re-edit." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => route('profile.sales.view', $mrs->id),
                    'module'  => 'MRS',
                    'status'  => 'HOLD (For MCD Planner re-edit)',
                ]);
                return back()->with('success', 'Request returned to the MCD Planner for re-edit.');
            }

            return back()->with('error', 'Unknown action.');
        } catch (\Exception $e) {
            return back()->with("error", "An error occurred: " . $e->getMessage());
        }
    }

    public function generate_report(Request $request)
    {
        $validated = $request->validate([
            'orderNumber' => 'required|string|exists:ecommerce_sales_headers,order_number',
        ]);
        $salesHeader = SalesHeader::with('items.issuances')
            ->where('order_number', $validated['orderNumber'])
            ->first(); // exists by validation, but still guard

        if (!$salesHeader) {
            return back()->with('error', 'Sales Header not found.');
        }

        $paHeader = PurchaseAdvice::where('mrs_id', $salesHeader->id)->first();
        $salesDetails = SalesDetail::with('issuances.user')
            ->where('sales_header_id', $salesHeader->id)
            ->get();
        $postedDate = $salesHeader->verified_at;
        $purchaseAdviceData = [];
        // "Name:Designation" → [name, designation]
        $requestorRaw = (string)$salesHeader->requested_by;
        $requestor = array_pad(explode(':', $requestorRaw, 2), 2, '');

        foreach ($salesDetails as $sale) {
            // fetch candidate inventory request items matching this product
            $items = InventoryRequestItems::select(
                    'inventory_requests_items.*',
                    'inventory_requests.department',
                    'inventory_requests.type as inventory_requests_type',
                    'inventory_requests.approved_by',
                    'users.name as prepared_by_name',
                    'role.name as prepared_by_designation',
                    'departments.name as prepared_by_department',
                    'inventory_requests.created_at as prepared_by_date'
                )
                ->leftJoin('inventory_requests', 'inventory_requests.id', 'inventory_requests_items.imf_no')
                ->leftJoin('users', 'users.id', 'inventory_requests.user_id')
                ->leftJoin('role', 'role.id', 'users.role_id')
                ->leftJoin('departments', 'departments.id', 'users.department_id')
                ->where('product_id', $sale->product_id)
                ->get();

            // product may be missing; guard with nullsafe + defaults
            $product = Product::find($sale->product_id);

            if ($items->isEmpty()) {
                // fallback row built from sale + product + requestor
                $purchaseAdviceData[] = [
                    'UoM'                    => $product->uom ?? '',
                    'stock_code'             => $product->code ?? '',
                    'cost_code'              => $sale->cost_code,
                    'frequency'              => $sale->frequency,
                    'purpose'                => $sale->purpose,
                    'date_needed'            => $sale->date_needed ? Carbon::parse($sale->date_needed)->format('Y-m-d') : '',
                    'par_to'                 => $sale->par_to,
                    'previous_mrs'           => $sale->previous_mrs,
                    'OEM_ID'                 => $product->oem ?? '',
                    'stock_type'             => $product->stock_type ?? '',
                    'inv_code'               => $product->inv_code ?? '',
                    'usage_rate_qty'         => $product->usage_rate_qty ?? '',
                    'on_hand'                => $product->on_hand ?? 0,
                    'min_qty'                => $product->min_qty ?? 0,
                    'max_qty'                => $product->max_qty ?? 0,
                    'qty_order'              => $sale->qty_to_order,
                    'open_po'                => $sale->open_po,
                    'po_no'                  => $sale->po_no,
                    'qty_ordered'            => $sale->qty_ordered,
                    'po_date_released'       => $sale->po_date_released,
                    'is_hold'                => $sale->promo_id,
                    'item_description'       => $product->name ?? '',
                    'prepared_by_name'       => $requestor[0] ?? '',
                    'prepared_by_designation'=> $requestor[1] ?? '',
                    'prepared_by_date'       => optional($sale->created_at)->format('Y-m-d h:i:s A') ?? '',
                ];
            } else {
                // enrich items with sale/product/requestor fields
                $itemsWithCostCode = $items->map(function ($item) use ($sale, $product, $requestor) {
                    $item->UoM                     = $product->uom ?? '';
                    $item->OEM_ID                  = $product->oem ?? '';
                    $item->cost_code               = $sale->cost_code;
                    $item->po_no                   = $sale->po_no;
                    $item->qty_ordered             = $sale->qty_ordered;
                    $item->frequency               = $sale->frequency;
                    $item->purpose                 = $sale->purpose;
                    $item->date_needed             = $sale->date_needed ? Carbon::parse($sale->date_needed)->format('Y-m-d') : '';
                    $item->par_to                  = $sale->par_to;
                    $item->previous_mrs            = $sale->previous_mrs;
                    $item->qty_order               = $sale->qty_to_order;
                    $item->open_po                 = $sale->open_po;
                    $item->stock_type              = $product->stock_type ?? '';
                    $item->inv_code                = $product->inv_code ?? '';
                    $item->usage_rate_qty          = $product->usage_rate_qty ?? '';
                    $item->on_hand                 = $product->on_hand ?? 0;
                    $item->min_qty                 = $product->min_qty ?? 0;
                    $item->max_qty                 = $product->max_qty ?? 0;
                    $item->po_date_released        = $sale->po_date_released;
                    $item->is_hold                 = $sale->promo_id;
                    $item->item_description        = $product->name ?? '';
                    $item->prepared_by_name        = $requestor[0] ?? '';
                    $item->prepared_by_designation = $requestor[1] ?? '';
                    $item->prepared_by_date        = optional($sale->created_at)->format('Y-m-d h:i:s A') ?? '';
                    return $item;
                });

                $purchaseAdviceData = array_merge($purchaseAdviceData, $itemsWithCostCode->toArray());
            }
        }

        $pdf = \PDF::loadHtml(
            view('admin.purchasing.components.generate-report', [
                'purchaseAdviceData' => $purchaseAdviceData,
                'postedDate'         => $postedDate,
                'salesHeader'        => $salesHeader,
                'paHeader'           => $paHeader,
                'requestor'          => $requestor,
            ])
        )->setPaper('legal', 'landscape');

        $paNumber  = $paHeader->pa_number ?? null;
        $fileLabel = $paNumber ? "PA-{$paNumber}" : "PA-ORDER";
        $fileLabel .= $paHeader->revision > 0 ? '-Rev' . $paHeader->revision : '';
        return $pdf->download($fileLabel . '.pdf');
    }

    public function generate_report_pa(Request $request)
    {
        $paHeader     = PurchaseAdvice::where('pa_number', $request->paNumber)->first();
        $salesHeader  = $paHeader;
        $salesDetails = $paHeader->details;
        $postedDate   = $salesHeader->verified_at;
        $purchaseAdviceData = [];

        foreach ($salesDetails as $item) {
            $qtyOrder   = (int)($item->qty_to_order ?? 0);
            $qtyOrdered = (int)($item->qty_ordered  ?? 0);
            $onHand     = (float)($item->on_hand ?? $item->product->on_hand ?? 0);
            $openPo     = (float)($item->open_po ?? 0);
            $usageRate  = (float)($item->usage_rate_qty ?? $item->product->usage_rate_qty ?? 0);

            // Use stored values from Excel if available, otherwise compute
            if (!is_null($item->rof_months)) {
                $rofMonths = $item->rof_months;
            } else {
                $rofMonths = $usageRate > 0 ? round(($onHand + $openPo) / $usageRate, 2) : 0;
            }

            if (!is_null($item->rof_months_w_request)) {
                $rofMonthsWRequest = $item->rof_months_w_request;
            } else {
                $rofMonthsWRequest = $usageRate > 0 ? round(($onHand + $openPo + $qtyOrder) / $usageRate, 2) : 0;
            }

            $purchaseAdviceData[] = [
                'inv_code'             => $item->product->inv_code,
                'stock_type'           => $item->product->stock_type,
                'stock_code'           => $item->product->code,
                'item_description'     => $item->product->name,
                'OEM_ID'               => $item->product->oem,
                'UoM'                  => $item->product->uom,
                'usage_rate_qty'       => $usageRate,
                'on_hand'              => $onHand,
                'open_po'              => $openPo,
                'dlt'                  => $item->dlt               ?? '',
                'qty_order'            => $qtyOrder,
                'date_needed'          => $item->date_needed        ?? '',
                'qty_per_delivery'     => $item->qty_per_delivery   ?? '',
                'number_of_deliveries' => $item->number_of_deliveries ?? '',
                'class_note'           => $item->class_note         ?? '',
                'par_to'               => $item->par_to,
                'department'           => $item->department         ?? '',
                'previous_mrs'         => $item->previous_po,
                'priority'             => $item->priority_no        ?? '',
                'cost_code'            => $item->cost_code          ?? '',
                'purpose'              => $item->remarks            ?? '',
                'rof_months'           => $rofMonths,
                'rof_months_w_request' => $rofMonthsWRequest,
                'po_no'                => $item->current_po,
                'qty_ordered'          => $qtyOrdered,
                'po_date_released'     => $item->po_date_released,
                'order_number'         => $item->purchaseAdvice->pa_number,
                'frequency'            => $item->frequency          ?? '',
                'is_hold'              => (int) ($item->is_hold ?? 0),
            ];
        }

        $pdf = \PDF::loadHtml(view('admin.purchasing.components.generate-report-pa',
            compact('purchaseAdviceData', 'postedDate', 'salesHeader', 'paHeader')));
        $pdf->setPaper('legal', 'landscape');
        return $pdf->download('PA-' . $paHeader->pa_number . ($paHeader->revision > 0 ? '-Rev' . $paHeader->revision : '') . '.pdf');
    }

    public function generate_report_pa_sr_excel(Request $request)
    {
        $paHeader   = PurchaseAdvice::where('pa_number', $request->query('paNumber'))->first();
        $postedDate = $paHeader->verified_at;
        $purchaseAdviceData = [];

        foreach ($paHeader->details as $item) {
            $qtyOrder   = (int)($item->qty_to_order  ?? 0);
            $qtyOrdered = (int)($item->qty_ordered    ?? 0);
            $onHand     = (float)($item->on_hand      ?? $item->product->on_hand      ?? 0);
            $openPo     = (float)($item->open_po      ?? 0);
            $usageRate  = (float)($item->usage_rate_qty ?? $item->product->usage_rate_qty ?? 0);

            $rofMonths        = !is_null($item->rof_months)
                ? $item->rof_months
                : ($usageRate > 0 ? round(($onHand + $openPo) / $usageRate, 2) : 0);

            $rofMonthsWRequest = !is_null($item->rof_months_w_request)
                ? $item->rof_months_w_request
                : ($usageRate > 0 ? round(($onHand + $openPo + $qtyOrder) / $usageRate, 2) : 0);

            $purchaseAdviceData[] = [
                'inv_code'             => $item->product->inv_code       ?? '',
                'stock_type'           => $item->product->stock_type     ?? '',
                'stock_code'           => $item->product->code           ?? '',
                'item_description'     => $item->product->name           ?? '',
                'OEM_ID'               => $item->product->oem            ?? '',
                'UoM'                  => $item->product->uom            ?? '',
                'usage_rate_qty'       => $usageRate,
                'on_hand'              => $onHand,
                'open_po'              => $openPo,
                'min_qty'              => $item->product->min_qty        ?? '',
                'max_qty'              => $item->product->max_qty        ?? '',
                'dlt'                  => $item->dlt                     ?? '',
                'qty_order'            => $qtyOrder,
                'date_needed'          => $item->date_needed             ?? '',
                'qty_per_delivery'     => $item->qty_per_delivery        ?? '',
                'number_of_deliveries' => $item->number_of_deliveries    ?? '',
                'class_note'           => $item->class_note              ?? '',
                'par_to'               => $item->par_to                  ?? '',
                'department'           => $item->department              ?? '',
                'previous_mrs'         => $item->previous_po             ?? '',
                'priority'             => $item->priority_no             ?? '',
                'cost_code'            => $item->cost_code               ?? '',
                'purpose'              => $item->remarks                 ?? '',
                'rof_months'           => $rofMonths,
                'rof_months_w_request' => $rofMonthsWRequest,
                'po_no'                => $item->current_po              ?? '',
                'qty_ordered'          => $qtyOrdered,
                'po_date_released'     => $item->po_date_released,
                'order_number'         => $paHeader->pa_number,
                'frequency'            => $item->frequency               ?? '',
                'is_hold'              => (int) ($item->is_hold ?? 0),
            ];
        }

        $this->exportPurchaseAdviceSR($paHeader, $purchaseAdviceData, $postedDate);
    }

    private function exportPurchaseAdviceSR($paHeader, array $purchaseAdviceData, $postedDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $centerBold  = [
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $centerBorder = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $headerCell = [
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        // ── Title rows ──────────────────────────────────────────────────
        $lastCol = 'W'; // 23 columns (A–W)
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'PURCHASE ADVISE');
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray(array_merge($centerBold, ['font' => ['bold' => true, 'size' => 14]]));

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'PA-' . ($paHeader->pa_number ?? '') . ($paHeader->revision > 0 ? '-Rev' . $paHeader->revision : ''));
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray($centerBold);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'DATE: ' . ($postedDate ? \Carbon\Carbon::parse($postedDate)->format('F j, Y h:i A') : 'Not Verified') . ($paHeader->revision > 0 && $paHeader->revised_at ? '     |     REVISED: ' . \Carbon\Carbon::parse($paHeader->revised_at)->format('F j, Y h:i A') : ''));
        $sheet->getStyle("A3:{$lastCol}3")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

        // ── Column headers (row 5) ───────────────────────────────────────
        $headers = [
            'No', 'Stock Type', 'Inv. Code', 'Stock Code', 'Stock Description',
            'OEM ID', 'UoM', 'Average Usage (12mos.)', 'SOH', 'Open PO',
            'DLT (Mos.)', 'Qty to Order', 'Date Needed', 'Freq/ Qty per Delivery',
            'No. of Deliveries', 'Classic Note', 'End-user/ PAR To', 'Previous PO',
            'PRIO#', 'Cost Code', 'Remarks', '#OF MONTHS (SOH+OO)', '#OF MONTHS W REQUEST',
        ];

        $row = 5;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
            $sheet->getStyle("{$col}{$row}")->applyFromArray($headerCell);
            $col++;
        }

        // ── Data rows ────────────────────────────────────────────────────
        $row = 6;
        foreach ($purchaseAdviceData as $index => $item) {
            if (($item['is_hold'] ?? 0) == 0) {
                $parTo = $item['department'] ?: explode(':', $item['par_to'] ?? '')[0];

                $data = [
                    $index + 1,
                    $item['stock_type']           ?? '',
                    $item['inv_code']             ?? '',
                    ($item['stock_code'] === 'null' ? '' : ($item['stock_code'] ?? '')),
                    $item['item_description']     ?? '',
                    $item['OEM_ID']               ?? '',
                    $item['UoM']                  ?? '',
                    $item['usage_rate_qty']        ?? '',
                    $item['on_hand']               ?? '',
                    $item['open_po']               ?? '',
                    $item['dlt']                   ?? '',
                    $item['qty_order']             ?? '',
                    $item['date_needed']           ?? '',
                    $item['qty_per_delivery']      ?? '',
                    $item['number_of_deliveries']  ?? '',
                    $item['class_note']            ?? '',
                    $parTo,
                    $item['previous_mrs']          ?? '',
                    $item['priority']              ?? '',
                    $item['cost_code']             ?? '',
                    $item['purpose']               ?? '',
                    $item['rof_months']            ?? '',
                    $item['rof_months_w_request']  ?? '',
                ];

                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue("{$col}{$row}", $value);
                    $sheet->getStyle("{$col}{$row}")->applyFromArray($centerBorder);
                    $col++;
                }
                $row++;
            }
        }

        // ── Footer ───────────────────────────────────────────────────────
        $row += 2;
        $sheet->setCellValue("A{$row}", 'Prepared by:');
        $sheet->setCellValue("C{$row}", 'Reviewed by:');
        $sheet->setCellValue("E{$row}", 'Approved by:');
        $sheet->setCellValue("G{$row}", 'Received by:');

        $row++;
        $sheet->setCellValue("A{$row}", 'Name');
        $sheet->setCellValue("B{$row}", strtoupper($paHeader->planner->name ?? ''));
        $sheet->setCellValue("C{$row}", $paHeader->verified_at ? 'JOHN DALE P. RANOCO' : '');
        $sheet->setCellValue("E{$row}", $paHeader->approved_at ? 'MYRNA G. IMPROSO'    : '');
        $sheet->setCellValue("G{$row}", strtoupper($paHeader->purchaser->name ?? ''));

        $row++;
        $sheet->setCellValue("A{$row}", 'Designation');
        $sheet->setCellValue("B{$row}", 'MCD Planner');
        $sheet->setCellValue("C{$row}", $paHeader->verified_at ? 'Material Planning Supervisor' : '');
        $sheet->setCellValue("E{$row}", $paHeader->approved_at ? 'MCD Manager'                  : '');
        $sheet->setCellValue("G{$row}", $paHeader->received_at ? 'Purchaser'                    : '');

        $row++;
        $sheet->setCellValue("A{$row}", 'Signature');

        $row++;
        $sheet->setCellValue("A{$row}", 'Date');
        $sheet->setCellValue("B{$row}", \Carbon\Carbon::parse($paHeader->created_at)->format('F j, Y h:i A'));
        $sheet->setCellValue("C{$row}", $paHeader->verified_at ? \Carbon\Carbon::parse($paHeader->verified_at)->format('F j, Y h:i A') : '');
        $sheet->setCellValue("E{$row}", $paHeader->approved_at ? \Carbon\Carbon::parse($paHeader->approved_at)->format('F j, Y h:i A') : '');
        $sheet->setCellValue("G{$row}", $paHeader->received_at ? \Carbon\Carbon::parse($paHeader->received_at)->format('F j, Y h:i A') : '');

        // ── Output ───────────────────────────────────────────────────────
        $writer   = new Xlsx($spreadsheet);
        $filename = 'PA-' . $paHeader->pa_number . ($paHeader->revision > 0 ? '-Rev' . $paHeader->revision : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function generate_report_pa_excel(Request $request)
    {
        $salesHeader = SalesHeader::with('items.issuances')->where('order_number', $request->query('orderNumber'))->first();
        $paHeader = PurchaseAdvice::where("mrs_id", $salesHeader->id)->first();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id', $salesHeader->id)->get();
        //dd($salesDetails);
        $postedDate = $salesHeader->verified_at;

        $purchaseAdviceData = [];

        $requestor = explode(":", $salesHeader->requested_by);

        foreach ($salesDetails as $sale) {
            $items = InventoryRequestItems::select(
                'inventory_requests_items.*',
                'inventory_requests.department',
                'inventory_requests.type as inventory_requests_type',
                'inventory_requests.approved_by',
                'users.name as prepared_by_name',
                'role.name as prepared_by_designation',
                'departments.name as prepared_by_department',
                'inventory_requests.created_at as prepared_by_date'
            )
                ->leftJoin('inventory_requests', 'inventory_requests.id', 'inventory_requests_items.imf_no')
                ->leftJoin('users', 'users.id', 'inventory_requests.user_id')
                ->leftJoin('role', 'role.id', 'users.role_id')
                ->leftJoin('departments', 'departments.id', 'users.department_id')
                ->where("product_id", $sale->product_id)
                ->get();

            $product = Product::find($sale->product_id);
            if ($items->isEmpty()) {
                $user = User::select(
                    'users.name as prepared_by_name',
                    'role.name as prepared_by_designation'
                )
                    ->leftJoin('role', 'role.id', 'users.role_id')
                    ->find($sale->created_by);

                $purchaseAdviceData[] = [
                    'UoM' => $product->uom,
                    'stock_code' => $product->code,
                    'cost_code' => $sale->cost_code,
                    'frequency' => $sale->frequency,
                    'purpose' => $sale->purpose,
                    'date_needed' => Carbon::parse($sale->date_needed)->format('Y-m-d'),
                    'par_to' => $sale->par_to,
                    'previous_mrs' => $sale->previous_mrs,
                    'OEM_ID' => $product->oem,
                    'stock_type' => $product->stock_type,
                    'inv_code' => $product->inv_code,
                    'usage_rate_qty' => $product->usage_rate_qty,
                    'on_hand' => $product->on_hand,
                    'min_qty' => $product->min_qty,
                    'max_qty' => $product->max_qty,
                    'qty_order' => $sale->qty_to_order,
                    'open_po' => $sale->open_po,
                    'po_no' => $sale->po_no,
                    'qty_ordered' => $sale->qty_ordered,
                    'po_date_released' => $sale->po_date_released,
                    'is_hold' => $sale->promo_id,
                    'item_description' => $product->name,
                    'prepared_by_name' => $requestor[0] ?? '',
                    'prepared_by_designation' => $requestor[1] ?? '',
                    'prepared_by_date' => optional($sale->created_at)->format('Y-m-d h:i:s A') ?? ''
                ];

            } else {
                $itemsWithCostCode = $items->map(function ($item) use ($sale, $product, $requestor) {
                    $item->UoM = $product->uom;
                    $item->OEM_ID = $product->oem;
                    $item->cost_code = $sale->cost_code;
                    $item->po_no = $sale->po_no;
                    $item->qty_ordered = $sale->qty_ordered;
                    $item->frequency = $sale->frequency;
                    $item->purpose = $sale->purpose;
                    $item->date_needed = Carbon::parse($sale->date_needed)->format('Y-m-d');
                    $item->par_to = $sale->par_to;
                    $item->previous_mrs = $sale->previous_mrs;
                    $item->qty_order = $sale->qty_to_order;
                    $item->open_po = $sale->open_po;
                    $item->stock_type = $product->stock_type;
                    $item->inv_code = $product->inv_code;
                    $item->usage_rate_qty = $product->usage_rate_qty;
                    $item->on_hand = $product->on_hand;
                    $item->min_qty = $product->min_qty;
                    $item->max_qty = $product->max_qty;
                    $item->po_date_released = $sale->po_date_released;
                    $item->is_hold = $sale->promo_id;
                    $item->prepared_by_name = $requestor[0] ?? '';
                    $item->prepared_by_designation = $requestor[1] ?? '';
                    $item->prepared_by_date = optional($sale->created_at)->format('Y-m-d h:i:s A') ?? '';
                    return $item;
                });
                $purchaseAdviceData = array_merge($purchaseAdviceData, $itemsWithCostCode->toArray());
            }
        }
        /*
        $pdf = \PDF::loadHtml(view('admin.purchasing.components.generate-report', compact('purchaseAdviceData', 'postedDate', 'salesHeader', 'paHeader', 'requestor')));
        $pdf->setPaper("legal", "landscape");
        return $pdf->download('PA-'.$paHeader->pa_number.($paHeader->revision > 0 ? '-Rev'.$paHeader->revision : '').'.pdf');

        return Excel::download(new PurchaseAdviceReport($purchaseAdviceData, $postedDate, $salesHeader, $paHeader, $requestor), 'PA-'.$paHeader->pa_number.($paHeader->revision > 0 ? '-Rev'.$paHeader->revision : '').'.xlsx');
        */
        $this->exportPurchaseAdvice($paHeader, $purchaseAdviceData, $salesHeader, $postedDate);

    }

    public function planner_pa()
    {
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();

        $customConditions = [
            [
                'field' => 'status',
                'operator' => '=',
                'value' => 'active',
                'apply_to_deleted_data' => true
            ],
        ];

        $listing = new ListingHelper('desc', 10, 'order_number', $customConditions);
        $salesQuery = PurchaseAdvice::with('details');

        // Apply date filters
        if (isset($_GET['startdate']) && $_GET['startdate'] !== '') {
            $salesQuery->where('created_at', '>=', $_GET['startdate']);
        }
        if (isset($_GET['enddate']) && $_GET['enddate'] !== '') {
            $salesQuery->where('created_at', '<=', $_GET['enddate'] . ' 23:59:59');
        }

        // Apply search filters
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $salesQuery->where('pa_number', 'like', '%' . $_GET['search'] . '%');
        }

        // Apply status filters based on final_status
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $statuses = $_GET['status'];
            $salesQuery->where(function ($query) use ($statuses) {
                $query->whereHas('items', function ($subQuery) use ($statuses) {
                    $subQuery->havingRaw("
                        CASE
                            WHEN SUM(qty_to_order) = SUM(qty_ordered) THEN 'COMPLETED'
                            WHEN SUM(qty_ordered) > 0 AND SUM(qty_to_order) > SUM(qty_ordered) THEN 'PARTIAL'
                            ELSE 'UNSERVED'
                        END IN (" . implode(',', array_map(function ($status) {
                        return "'$status'"; }, $statuses)) . ")
                    ");
                });
            });
        }

        // Define role-based status conditions
        $statusConditions = [
            "MCD Verifier" => [
                'APPROVED (MCD PLANNER) - FOR VERIFICATION',
            ],
            "MCD Approver" => [
                'VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL',
            ],
            "Purchasing Officer" => [
                '(For Purchasing Receival)',
                'APPROVED (MCD Approver) - PA for Delegation'
            ],
            "Purchaser" => [
                '(For Purchasing Receival)',
                'RECEIVED FOR CANVASS (Purchasing Officer)',
            ]
        ];

        if (isset($statusConditions[$role->name])) {
            if ($role->name === "Purchaser") {
                $purchaserFilter = request('purchaser_filter', '');
                if ($purchaserFilter === 'for_receival') {
                    $salesQuery->where('status', '(For Purchasing Receival)');
                } elseif ($purchaserFilter === 'received') {
                    $salesQuery->where('status', 'RECEIVED FOR CANVASS (Purchasing Officer)');
                } else {
                    $salesQuery->whereIn('status', $statusConditions[$role->name]);
                }
                $salesQuery->where('received_by', Auth::id());
            } else {
                $salesQuery->whereIn('status', $statusConditions[$role->name]);
            }
        }

        $activePaType = request('pa_type', 'sr');
        if (!in_array($activePaType, ['sr', 'mrs'], true)) {
            $activePaType = 'sr';
        }

        // "PA for SR" = nothing but a PA behind it; "PA MRS" = raised off a numbered MRS.
        $srScope = function ($query) {
            $query->whereNull('mrs_id')
                ->orWhereDoesntHave('mrs')
                ->orWhereHas('mrs', function ($mrsQuery) {
                    $mrsQuery->whereNull('order_number')
                        ->orWhere('order_number', '');
                });
        };
        $mrsScope = function ($mrsQuery) {
            $mrsQuery->whereNotNull('order_number')
                ->where('order_number', '!=', '');
        };

        $typeCountsQuery = clone $salesQuery;
        $srCountQuery  = (clone $typeCountsQuery)->where($srScope);
        $mrsCountQuery = (clone $typeCountsQuery)->whereHas('mrs', $mrsScope);

        $paSrCount  = (clone $srCountQuery)->count();
        $paMrsCount = (clone $mrsCountQuery)->count();

        // How much of each tab is actually waiting on this role. Without these the
        // sidebar badge can read 6 while the tab you happen to be on shows none of
        // them, which is exactly how the count looks broken.
        $paSrActionCount = ActionQueue::has(ActionQueue::PA, $role->name)
            ? ActionQueue::scope((clone $srCountQuery), ActionQueue::PA, $role->name)->count()
            : 0;
        $paMrsActionCount = ActionQueue::has(ActionQueue::PA, $role->name)
            ? ActionQueue::scope((clone $mrsCountQuery), ActionQueue::PA, $role->name)->count()
            : 0;

        if ($activePaType === 'mrs') {
            $salesQuery->whereHas('mrs', $mrsScope);
        } else {
            $salesQuery->where($srScope);
        }

        // Whatever is on this role's desk goes to the top of page 1 — every role,
        // not just the Planner's held PAs. Same list that drives the sidebar badge
        // and the NEEDS YOUR ACTION flag on the row.
        $actionOrder = ActionQueue::orderCase(ActionQueue::PA, $role->name);
        if ($actionOrder) {
            $salesQuery->orderByRaw($actionOrder);
        }

        $sales = $salesQuery->orderBy('id', 'desc')->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        // Get distinct statuses for dropdown filter
        $statuses = PurchaseAdvice::distinct()->pluck('status');

        // Return the view with compacted variables
        return view('admin.purchasing.planner_pa', compact('sales', 'filter', 'searchType', 'role', 'statuses', 'activePaType', 'paSrCount', 'paMrsCount', 'paSrActionCount', 'paMrsActionCount'));
    }

    public function planner_pa_create()
    {
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();

        $mrs_numbers = SalesHeader::whereNotNull('planner_at')
            ->whereIn('status', ['RECEIVED FOR CANVASS (Purchasing Officer)', 'APPROVED (MCD Planner) - MRS For Verification', 'HOLD (For MCD Planner re-edit)', 'VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL', 'APPROVED (MCD Approver) - PA for Delegation'])
            ->orWhere('status', 'LIKE', '%FULLY APPROVED%')
            ->orderBy('id', 'desc')->take(1000)->get();
        $pa_number = $this->next_pa_number();

        return view('admin.purchasing.planner_pa_create', compact('mrs_numbers', 'pa_number', 'role'));
    }

    public function next_pa_number()
    {
        $user = Auth::user();

        $initials  = strtoupper(substr($user->firstname  ?? '', 0, 1));
        $initials .= strtoupper(substr($user->middlename ?? '', 0, 1));
        $initials .= strtoupper(substr($user->lastname   ?? '', 0, 1));

        $lastOrder = PurchaseAdvice::whereNull('mrs_id')
            ->where('pa_number', 'like', $initials . '-%')
            ->orderBy('id', 'desc')
            ->first();

        $series = 1;
        if ($lastOrder) {
            $parts  = explode('-', $lastOrder->pa_number);
            $series = ((int) end($parts)) + 1;
        }

        return $initials . '-' . str_pad($series, 6, '0', STR_PAD_LEFT);
    }

    public function mrs_items(Request $request)
    {
        $items = SalesDetail::whereIn('sales_header_id', $request->ids)
            ->where('promo_id', 1)
            ->whereNull('is_pa')
            ->with('header')
            ->with('product')
            ->get();

        return response()->json(["data" => $items], 200);
    }

    public function insert_pa(Request $request)
    {
        $paNumber = $this->next_pa_number();

        $request->validate([
            'selected_items'         => 'required|array|min:1',
            'selected_items.*'       => 'integer|exists:products,id',
            'supporting_documents'   => 'nullable|array',
            'supporting_documents.*' => 'file',
        ]);

        $selectedItems = $request->input('selected_items');

        foreach ($selectedItems as $itemId) {
            $request->validate([
                "par_to_{$itemId}"               => 'required|string|max:191',
                "qty_to_order_{$itemId}"         => 'required|integer',
                "previous_po_{$itemId}"          => 'nullable|string|max:191',
                "cost_code_{$itemId}"            => 'nullable|string|max:191',
                "remarks_{$itemId}"              => 'nullable|string|max:1000',
                "priority_no_{$itemId}"          => 'nullable|string|max:191',
                "qty_per_delivery_{$itemId}"     => 'nullable|string|max:191',
                "number_of_deliveries_{$itemId}" => 'nullable|string|max:191',
                "dlt_{$itemId}"                  => 'nullable|numeric',
                "date_needed_{$itemId}"          => 'nullable|string|max:255',
                "class_note_{$itemId}"           => 'nullable|string|max:191',
                "frequency_{$itemId}"            => 'nullable|string|max:191',
                "open_po_{$itemId}"              => 'nullable|string|max:191',
                "department_{$itemId}"           => 'nullable|string|max:255',
                "usage_rate_qty_{$itemId}"       => 'nullable|numeric',
                "on_hand_{$itemId}"              => 'nullable|numeric',
                "rof_months_{$itemId}"           => 'nullable|numeric',
                "rof_months_w_request_{$itemId}" => 'nullable|numeric',
            ]);
        }

        DB::transaction(function () use ($paNumber, $selectedItems, $request) {
            $pa = PurchaseAdvice::create([
                'pa_number'       => $paNumber,
                'status'          => 'APPROVED (MCD PLANNER) - FOR VERIFICATION',
                'created_by'      => Auth::id(),
                'planner_remarks' => $request->input('planner_remarks'),
            ]);

            if ($request->hasFile('supporting_documents')) {
                $paths = [];
                foreach ($request->file('supporting_documents') as $file) {
                    $paths[] = $file->store('supporting_documents/' . $pa->id, 'public');
                }
                History::context($pa, [
                    'action'          => 'created',
                    'title'           => 'Supporting documents attached',
                    'requestor_title' => 'Supporting documents attached',
                ]);
                $pa->update(['supporting_documents' => implode('|', $paths)]);
            }

            foreach ($selectedItems as $itemId) {
                PurchaseAdviceDetail::create([
                    'purchase_advice_id'   => $pa->id,
                    'product_id'           => $itemId,
                    'par_to'               => $request->input("par_to_{$itemId}"),
                    'qty_to_order'         => $request->input("qty_to_order_{$itemId}"),
                    'previous_po'          => $request->input("previous_po_{$itemId}"),
                    'current_po'           => $request->input("current_po_{$itemId}"),
                    'po_date_released'     => $request->input("po_date_released_{$itemId}"),
                    'qty_ordered'          => $request->input("qty_ordered_{$itemId}"),
                    'cost_code'            => $request->input("cost_code_{$itemId}"),
                    'remarks'              => $request->input("remarks_{$itemId}"),
                    'priority_no'          => $request->input("priority_no_{$itemId}"),
                    'qty_per_delivery'     => $request->input("qty_per_delivery_{$itemId}"),
                    'number_of_deliveries' => $request->input("number_of_deliveries_{$itemId}"),
                    'dlt'                  => $request->input("dlt_{$itemId}"),
                    'date_needed'          => $request->input("date_needed_{$itemId}"),
                    'class_note'           => $request->input("class_note_{$itemId}"),
                    'frequency'            => $request->input("frequency_{$itemId}"),
                    'open_po'              => $request->input("open_po_{$itemId}"),
                    'department'           => $request->input("department_{$itemId}"),
                    'usage_rate_qty'       => $this->nullableNumericInput($request, "usage_rate_qty_{$itemId}"),
                    'on_hand'              => $this->nullableNumericInput($request, "on_hand_{$itemId}"),
                    'rof_months'           => $request->input("rof_months_{$itemId}"),
                    'rof_months_w_request' => $request->input("rof_months_w_request_{$itemId}"),
                ]);
            }
        });

        // New SR-type PA created by the planner — send it to the MCD Verifier queue.
        Notifier::toRoleName('MCD Verifier', [
            'title'   => 'New PA (SR) for Verification',
            'message' => "PA {$paNumber} was created by the MCD Planner and awaits your verification.",
            'url'     => route('planner_pa.index'),
            'module'  => 'PA',
            'status'  => 'APPROVED (MCD PLANNER) - FOR VERIFICATION',
        ]);

        return response()->json([
            'message'  => 'Purchase advice created successfully',
            'redirect' => route('planner_pa.index'),
        ], 201);
    }

    public function delete_pa($id)
    {
        $pa = PurchaseAdvice::findOrFail($id);

        // Logged before the delete: the trail outlives the record it describes.
        History::pa($pa, [
            'action'          => 'deleted',
            'title'           => 'Purchase advice deleted',
            'requestor_title' => 'Purchase advice deleted',
        ]);

        DB::transaction(function () use ($pa) {
            // Delete related purchase advice details
            $pa->details()->delete(); // Uses the Eloquent relationship

            // Delete the purchase advice record
            $pa->delete();
        });

        return back()->with('success', 'Purchase Advice deleted successfully.');
    }

    /**
     * MCD Planner cancels a stand-alone PA (PA for SR) straight from the listing.
     *
     * The reason is mandatory: it is stored as the planner's remarks, written to
     * the audit trail, and quoted in the notification sent to whoever was holding
     * the PA at the time, so nobody is left wondering why it disappeared.
     */
    public function cancel_pa(Request $request)
    {
        $request->validate([
            'pa_id'  => 'required|integer|exists:purchase_advice,id',
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'A reason is required before a PA can be cancelled.',
        ]);

        $pa = PurchaseAdvice::findOrFail($request->pa_id);

        if (Auth::user()->role_name() !== 'MCD Planner') {
            return back()->with('error', self::PA_CANCEL_LOCKED);
        }

        // A PA-DP follows the MRS it was raised from; only the stand-alone SR
        // advice is the planner's to pull.
        if (optional($pa->mrs)->order_number) {
            return back()->with('error', self::PA_CANCEL_LOCKED);
        }

        if ($pa->isCancelled()) {
            return back()->with('error', 'PA ' . $pa->pa_number . ' is already cancelled.');
        }

        $reason = trim($request->reason);
        // Read before the update: cancelling clears the very columns that say
        // who has been holding this PA.
        $recipients = $this->paCancelRecipients($pa);

        History::context($pa, [
            'action'          => 'cancelled',
            'title'           => 'Purchase advice cancelled by the MCD Planner',
            'requestor_title' => 'Purchase advice cancelled',
            'remarks'         => $reason,
        ]);
        $pa->update([
            'status'          => 'CANCELLED PURCHASED ADVICE',
            'planner_remarks' => $reason,
            'verified_by'     => null,
            'verified_at'     => null,
            'approved_by'     => null,
            'approved_at'     => null,
            'received_by'     => null,
            'received_at'     => null,
        ]);

        $payload = [
            'title'   => 'PA Cancelled',
            'message' => "PA {$pa->pa_number} was cancelled by the MCD Planner. Reason: {$reason}",
            'url'     => route('pa.pa_view', $pa->id),
            'module'  => 'PA',
            'status'  => 'CANCELLED PURCHASED ADVICE',
        ];
        foreach ($recipients['users'] as $userId) {
            Notifier::toUser($userId, $payload);
        }
        foreach ($recipients['roles'] as $roleName) {
            Notifier::toRoleName($roleName, $payload);
        }

        return back()->with('success', 'PA ' . $pa->pa_number . ' cancelled.');
    }

    /**
     * Who should hear that a PA-SR was pulled: everyone who has already acted on
     * it, plus the desk it was sitting on. A stand-alone PA has no requestor to
     * tell, so this list is the whole audience.
     *
     * @param  \App\Models\Ecommerce\PurchaseAdvice  $pa
     * @return array  ['users' => int[], 'roles' => string[]]
     */
    private function paCancelRecipients(PurchaseAdvice $pa)
    {
        $deskByStatus = [
            'APPROVED (MCD PLANNER) - FOR VERIFICATION'             => 'MCD Verifier',
            'VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL' => 'MCD Approver',
            'APPROVED (MCD Approver) - PA for Delegation'           => 'Purchasing Officer',
        ];

        $roles = [];
        foreach ($deskByStatus as $status => $roleName) {
            if (strcasecmp(trim((string) $pa->status), $status) === 0) {
                $roles[] = $roleName;
            }
        }

        $users = array_values(array_unique(array_filter([
            $pa->verified_by,
            $pa->approved_by,
            $pa->received_by,
        ])));

        return ['users' => $users, 'roles' => $roles];
    }

    // Statuses in which the MCD Planner may still add/remove SR line items.
    private function paItemsEditable(PurchaseAdvice $pa): bool
    {
        return in_array($pa->status, [
            'APPROVED (MCD PLANNER) - FOR VERIFICATION',
            'HOLD (For MCD Planner re-edit)',
        ], true);
    }

    public function add_pa_item(Request $request)
    {
        $request->validate([
            'pa_id'      => 'required|integer|exists:purchase_advice,id',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $pa = PurchaseAdvice::find($request->pa_id);
        if (!$this->paItemsEditable($pa)) {
            return response()->json(['message' => 'Items can only be edited while the PA is for verification.'], 422);
        }

        $product = Product::find($request->product_id);

        $detail = PurchaseAdviceDetail::create([
            'purchase_advice_id' => $pa->id,
            'product_id'         => $product->id,
            'qty_to_order'       => 0,
            'usage_rate_qty'     => $product->usage_rate_qty,
            'on_hand'            => $product->on_hand,
            'previous_po'        => $product->last_po_ref,
        ]);

        History::pa($pa, [
            'action'          => 'item_added',
            'title'           => 'Item added: ' . trim(($product->code ? $product->code . ' ' : '') . $product->name),
            'requestor_title' => 'An item was added to the purchase advice',
        ]);

        return response()->json([
            'message' => 'Item added.',
            'detail'  => [
                'id'             => $detail->id,
                'stock_type'     => $product->stock_type,
                'inv_code'       => $product->inv_code,
                'name'           => $product->name,
                'code'           => $product->code,
                'oem'            => $product->oem,
                'uom'            => $product->uom,
                'usage_rate_qty' => $detail->usage_rate_qty,
                'on_hand'        => $detail->on_hand,
                'previous_po'    => $detail->previous_po,
            ],
        ], 201);
    }

    public function delete_pa_item(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:purchase_advice_details,id']);

        $detail = PurchaseAdviceDetail::find($request->id);
        $pa = $detail ? $detail->purchaseAdvice : null;

        if (!$pa || !$this->paItemsEditable($pa)) {
            return response()->json(['message' => 'Items can only be edited while the PA is for verification.'], 422);
        }

        $itemLabel = $detail->historyItemLabel();
        $detail->delete();

        History::pa($pa, [
            'action'          => 'item_removed',
            'title'           => 'Item removed: ' . $itemLabel,
            'requestor_title' => 'An item was removed from the purchase advice',
        ]);

        return response()->json(['message' => 'Item removed.'], 200);
    }

    /**
     * Supporting documents belong to the MCD Planner, and only while the PA is back
     * in their hands — the same window in which they may add or remove line items.
     * Swapping an attachment after verification or approval would quietly change
     * what the verifier and approver signed off on.
     *
     * @param  \App\Models\Ecommerce\PurchaseAdvice|null  $pa
     * @return bool
     */
    private function paDocumentsEditable($pa)
    {
        if (!$pa) {
            return false;
        }

        $user = User::find(Auth::id());
        $role = $user ? Role::find($user->role_id) : null;

        return $role && $role->name === 'MCD Planner' && $this->paItemsEditable($pa);
    }

    /**
     * The stored attachment paths, in order.
     *
     * @param  \App\Models\Ecommerce\PurchaseAdvice  $pa
     * @return array
     */
    private function supportingDocumentPaths(PurchaseAdvice $pa)
    {
        $raw = trim((string) $pa->supporting_documents);

        return $raw === '' ? [] : array_values(array_filter(explode('|', $raw), 'strlen'));
    }

    /**
     * Keep the uploader's own filename so the planner recognises the document in the
     * list, but sanitise it and step a suffix on collisions — two files both called
     * "quotation.pdf" must not overwrite one another.
     *
     * @param  \App\Models\Ecommerce\PurchaseAdvice  $pa
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    private function storeSupportingDocument(PurchaseAdvice $pa, $file)
    {
        $dir = 'supporting_documents/' . $pa->id;
        $ext = strtolower($file->getClientOriginalExtension());

        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $base = trim(preg_replace('/[^A-Za-z0-9._-]+/', '-', $base), '-.');
        $base = $base === '' ? 'document' : substr($base, 0, 80);

        $name   = $base . ($ext ? '.' . $ext : '');
        $suffix = 1;

        while (Storage::disk('public')->exists($dir . '/' . $name)) {
            $name = $base . '-' . $suffix . ($ext ? '.' . $ext : '');
            $suffix++;
        }

        return $file->storeAs($dir, $name, 'public');
    }

    /**
     * The single writer for the column. The automatic model diff would log the raw
     * pipe-joined path string, which reads as noise in the trail, so it is muted for
     * this write and an entry naming the file that actually moved is written instead.
     *
     * @param  \App\Models\Ecommerce\PurchaseAdvice  $pa
     * @param  array  $paths
     * @param  array  $history
     * @return void
     */
    private function saveSupportingDocuments(PurchaseAdvice $pa, array $paths, array $history)
    {
        History::withoutRecording(function () use ($pa, $paths) {
            $pa->update(['supporting_documents' => count($paths) ? implode('|', $paths) : null]);
        });

        History::pa($pa, $history);
    }

    /**
     * Locate one of this PA's attachments by its stored path. Matching against the
     * stored list is also the access check: a path the column does not contain can
     * never be reached, so no crafted value can touch a file outside this PA.
     *
     * @param  array   $paths
     * @param  string  $path
     * @return int|false
     */
    private function findSupportingDocument(array $paths, $path)
    {
        return array_search($path, $paths, true);
    }

    public function upload_pa_documents(Request $request)
    {
        $request->validate([
            'pa_id'                  => 'required|integer|exists:purchase_advice,id',
            'supporting_documents'   => 'required|array|min:1',
            'supporting_documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:10240',
        ]);

        $pa = PurchaseAdvice::find($request->pa_id);

        if (!$this->paDocumentsEditable($pa)) {
            return response()->json(['message' => self::PA_DOCUMENTS_LOCKED], 422);
        }

        $paths = $this->supportingDocumentPaths($pa);
        $added = [];

        foreach ($request->file('supporting_documents') as $file) {
            $paths[] = $this->storeSupportingDocument($pa, $file);
            $added[] = $file->getClientOriginalName();
        }

        $this->saveSupportingDocuments($pa, $paths, [
            'action'          => 'updated',
            'title'           => 'Supporting document attached: ' . implode(', ', $added),
            'requestor_title' => 'A supporting document was attached to the purchase advice',
        ]);

        return response()->json([
            'message'   => count($added) . ' document(s) attached.',
            'documents' => $pa->fresh()->supportingDocumentList(),
        ], 201);
    }

    public function replace_pa_document(Request $request)
    {
        $request->validate([
            'pa_id'               => 'required|integer|exists:purchase_advice,id',
            'path'                => 'required|string',
            'supporting_document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:10240',
        ]);

        $pa = PurchaseAdvice::find($request->pa_id);

        if (!$this->paDocumentsEditable($pa)) {
            return response()->json(['message' => self::PA_DOCUMENTS_LOCKED], 422);
        }

        $paths = $this->supportingDocumentPaths($pa);
        $index = $this->findSupportingDocument($paths, $request->input('path'));

        if ($index === false) {
            return response()->json(['message' => 'That document is no longer attached to this PA. Reload the page and try again.'], 404);
        }

        $old  = $paths[$index];
        $file = $request->file('supporting_document');

        $paths[$index] = $this->storeSupportingDocument($pa, $file);

        $this->saveSupportingDocuments($pa, $paths, [
            'action'          => 'updated',
            'title'           => 'Supporting document replaced: ' . basename($old) . ' with ' . $file->getClientOriginalName(),
            'requestor_title' => 'A supporting document on the purchase advice was replaced',
        ]);

        // Only once the column no longer points at it — a failed save must leave the
        // PA pointing at a document that still exists on disk.
        Storage::disk('public')->delete($old);

        return response()->json([
            'message'   => 'Document replaced.',
            'documents' => $pa->fresh()->supportingDocumentList(),
        ], 200);
    }

    public function delete_pa_document(Request $request)
    {
        $request->validate([
            'pa_id' => 'required|integer|exists:purchase_advice,id',
            'path'  => 'required|string',
        ]);

        $pa = PurchaseAdvice::find($request->pa_id);

        if (!$this->paDocumentsEditable($pa)) {
            return response()->json(['message' => self::PA_DOCUMENTS_LOCKED], 422);
        }

        $paths = $this->supportingDocumentPaths($pa);
        $index = $this->findSupportingDocument($paths, $request->input('path'));

        if ($index === false) {
            return response()->json(['message' => 'That document is no longer attached to this PA. Reload the page and try again.'], 404);
        }

        $removed = $paths[$index];
        unset($paths[$index]);

        $this->saveSupportingDocuments($pa, array_values($paths), [
            'action'          => 'updated',
            'title'           => 'Supporting document removed: ' . basename($removed),
            'requestor_title' => 'A supporting document was removed from the purchase advice',
        ]);

        Storage::disk('public')->delete($removed);

        return response()->json([
            'message'   => 'Document removed.',
            'documents' => $pa->fresh()->supportingDocumentList(),
        ], 200);
    }

    public function planner_pa_view(Request $request, $id)
    {
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();
        $paHeader = PurchaseAdvice::where('id', $id)->first();
        $purchasers = User::where('role_id', 9)->get();
        return view('admin.purchasing.planner_pa_view', compact('paHeader', 'role', 'purchasers'));
    }

    public function purchase_action(Request $request, $id)
    {
        try {
            $pa = PurchaseAdvice::find($id);
            $note = $request->query('note', '');

            // No verify / approve / hold / assign / receive on a cancelled PA, and
            // no cancelling one twice.
            if ($pa && $pa->isCancelled()) {
                return back()->with('error', self::PA_CANCELLED_LOCKED);
            }

            // Shared payload bits for this PA.
            $paNo       = $pa->pa_number ?? ('#' . $pa->id);
            $paView     = route('pa.pa_view', $pa->id);
            $mrs        = $pa->mrs;
            $requestorId = optional($mrs)->user_id;
            $requestorUrl = $mrs ? route('profile.sales.view', $mrs->id) : null;
            $mrsNo      = optional($mrs)->order_number;

            if ($request->action == "verify") {
                History::context($pa, [
                    'action'          => 'verified',
                    'title'           => 'Verified by the MCD Verifier',
                    'requestor_title' => 'Purchase advice passed MCD verification',
                    'remarks'         => $note,
                ]);
                $pa->update([
                    "status" => "VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL",
                    "verified_at" => Carbon::now(),
                    "verified_by" => Auth::id(),
                    "verifier_remarks" => $note,
                    "is_hold" => 0,
                ]);
                Notifier::toRoleName('MCD Approver', [
                    'title'   => 'PA for Approval',
                    'message' => "PA {$paNo} was verified by the MCD Verifier and awaits your approval.",
                    'url'     => $paView,
                    'module'  => 'PA',
                    'status'  => "VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL",
                ]);
                if ($requestorId) {
                    Notifier::toUser($requestorId, [
                        'title'   => 'PA Verified',
                        'message' => "The Purchase Advice for your MRS #{$mrsNo} was verified by the MCD Verifier and is now for approval.",
                        'url'     => $requestorUrl,
                        'module'  => 'PA',
                        'status'  => "VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL",
                    ]);
                }
                return redirect()->route('planner_pa.index')->with('success', 'PA verified.');
            }

            if ($request->action == "hold-verifier") {
                History::context($pa, [
                    'action'          => 'held',
                    'title'           => 'Held by the MCD Verifier for Planner re-edit',
                    'requestor_title' => 'Purchase advice returned to the MCD Planner for revision',
                    'remarks'         => $note,
                ]);
                $pa->update([
                    "status" => "HOLD (For MCD Planner re-edit)",
                    "verifier_remarks" => $note,
                    "verified_by" => NULL,
                    "verified_at" => NULL,
                    "approved_by" => NULL,
                    "approved_at" => NULL,
                    "received_by" => NULL,
                    "received_at" => NULL,
                    "is_hold" => 1,
                ]);
                // Returned to planner: clear the MRS receipt so aging stops until it is re-received.
                if ($mrs) {
                    History::context($mrs, [
                        'action'               => 'held',
                        'title'                => 'Canvasser assignment cleared (PA held for re-edit)',
                        'remarks'              => $note,
                        'visible_to_requestor' => false,
                    ]);
                }
                optional($pa->mrs)->update(["received_at" => NULL, "received_by" => NULL]);
                Notifier::toRoleName('MCD Planner', [
                    'title'   => 'PA Returned by Verifier',
                    'message' => "PA {$paNo} was held by the MCD Verifier for re-edit." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => $paView,
                    'module'  => 'PA',
                    'status'  => "HOLD (For MCD Planner re-edit)",
                ]);
                if ($requestorId) {
                    Notifier::toUser($requestorId, [
                        'title'   => 'PA On Hold',
                        'message' => "The Purchase Advice for your MRS #{$mrsNo} was held by the MCD Verifier and returned to the MCD Planner." . ($note ? " Remarks: {$note}" : ''),
                        'url'     => $requestorUrl,
                        'module'  => 'PA',
                        'status'  => "HOLD (For MCD Planner re-edit)",
                    ]);
                }
                return redirect()->route('planner_pa.index')->with('success', 'PA returned to planner for revision.');
            }

            if ($request->action == "approve") {
                History::context($pa, [
                    'action'          => 'approved',
                    'title'           => 'Approved by the MCD Approver',
                    'requestor_title' => 'Purchase advice approved by MCD Manager',
                    'remarks'         => $note,
                ]);
                $pa->update([
                    "status" => "APPROVED (MCD Approver) - PA for Delegation",
                    "approved_at" => Carbon::now(),
                    "approved_by" => Auth::id(),
                    "approver_remarks" => $note,
                ]);
                // PA is approved and ready for delegation — the Purchasing Officer assigns a purchaser.
                Notifier::toRoleName('Purchasing Officer', [
                    'title'   => 'PA for Delegation',
                    'message' => "PA {$paNo} was approved by the MCD Approver and is ready for delegation to a purchaser.",
                    'url'     => $paView,
                    'module'  => 'PA',
                    'status'  => "APPROVED (MCD Approver) - PA for Delegation",
                ]);
                if ($requestorId) {
                    Notifier::toUser($requestorId, [
                        'title'   => 'PA Approved',
                        'message' => "The Purchase Advice for your MRS #{$mrsNo} was approved by the MCD Approver.",
                        'url'     => $requestorUrl,
                        'module'  => 'PA',
                        'status'  => "APPROVED (MCD Approver) - PA for Delegation",
                    ]);
                }
                return redirect()->route('planner_pa.index')->with('success', 'PA approved.');
            }

            if ($request->action == "hold-approver") {
                History::context($pa, [
                    'action'          => 'held',
                    'title'           => 'Held by the MCD Approver for Planner re-edit',
                    'requestor_title' => 'Purchase advice returned to the MCD Planner for revision',
                    'remarks'         => $note,
                ]);
                $pa->update([
                    "status" => "HOLD (For MCD Planner re-edit)",
                    "approver_remarks" => $note,
                    "verified_by" => NULL,
                    "verified_at" => NULL,
                    "approved_by" => NULL,
                    "approved_at" => NULL,
                    "received_by" => NULL,
                    "received_at" => NULL,
                    "is_hold" => 1,
                ]);
                // Returned to planner: clear the MRS receipt so aging stops until it is re-received.
                if ($mrs) {
                    History::context($mrs, [
                        'action'               => 'held',
                        'title'                => 'Canvasser assignment cleared (PA held for re-edit)',
                        'remarks'              => $note,
                        'visible_to_requestor' => false,
                    ]);
                }
                optional($pa->mrs)->update(["received_at" => NULL, "received_by" => NULL]);
                Notifier::toRoleName('MCD Planner', [
                    'title'   => 'PA Returned by Approver',
                    'message' => "PA {$paNo} was held by the MCD Approver for re-edit." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => $paView,
                    'module'  => 'PA',
                    'status'  => "HOLD (For MCD Planner re-edit)",
                ]);
                if ($requestorId) {
                    Notifier::toUser($requestorId, [
                        'title'   => 'PA On Hold',
                        'message' => "The Purchase Advice for your MRS #{$mrsNo} was held by the MCD Approver and returned to the MCD Planner." . ($note ? " Remarks: {$note}" : ''),
                        'url'     => $requestorUrl,
                        'module'  => 'PA',
                        'status'  => "HOLD (For MCD Planner re-edit)",
                    ]);
                }
                return redirect()->route('planner_pa.index')->with('success', 'PA returned to planner for revision.');
            }

            if ($request->action == "hold-purchaser") {
                // Purchaser/canvasser returns the PA to the MCD Planner for re-edit.
                // received_by is deliberately KEPT so update_pa() can route it straight
                // back to this same purchaser (bypassing verify/approve/assign).
                History::context($pa, [
                    'action'          => 'returned',
                    'title'           => 'Returned by the Purchaser/Canvasser for re-edit',
                    'requestor_title' => 'Purchase advice returned to the MCD Planner for revision',
                    'remarks'         => $note,
                ]);
                $pa->update([
                    "status" => "HOLD (For MCD Planner re-edit)",
                    "purchaser_remarks" => $note,
                    "received_at" => NULL,
                    "is_hold" => 1,
                ]);
                // Stop MRS aging while it sits with the planner, but keep it tied to the purchaser.
                if ($mrs) {
                    History::context($mrs, [
                        'action'               => 'returned',
                        'title'                => 'Aging paused — PA returned to the MCD Planner',
                        'remarks'              => $note,
                        'visible_to_requestor' => false,
                    ]);
                }
                optional($pa->mrs)->update(["received_at" => NULL]);
                Notifier::toRoleName('MCD Planner', [
                    'title'   => 'PA Returned by Purchaser',
                    'message' => "PA {$paNo} was returned by the Purchaser/Canvasser for re-edit." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => $paView,
                    'module'  => 'PA',
                    'status'  => "HOLD (For MCD Planner re-edit)",
                ]);
                if ($requestorId) {
                    Notifier::toUser($requestorId, [
                        'title'   => 'PA On Hold',
                        'message' => "The Purchase Advice for your MRS #{$mrsNo} was returned by the Purchaser/Canvasser to the MCD Planner." . ($note ? " Remarks: {$note}" : ''),
                        'url'     => $requestorUrl,
                        'module'  => 'PA',
                        'status'  => "HOLD (For MCD Planner re-edit)",
                    ]);
                }
                return redirect()->route('planner_pa.index')->with('success', 'PA returned to planner for revision.');
            }

            if ($request->action == "assign") {
                // $note holds the assigned Purchaser/Canvasser's user id.
                $assignedName = optional(User::find($note))->name;
                $assignTitle  = 'Delegated to canvasser' . ($assignedName ? ' ' . $assignedName : '');

                History::context($pa, [
                    'action'          => 'assigned',
                    'title'           => $assignTitle,
                    'requestor_title' => 'Purchase advice delegated to a canvasser',
                ]);
                $pa->update(["status" => "(For Purchasing Receival)", "received_by" => $note, "received_at" => null]);
                // Assigned but not yet received: mirror on the MRS so aging only starts on receive.
                if ($mrs) {
                    History::context($mrs, [
                        'action'          => 'assigned',
                        'title'           => $assignTitle,
                        'requestor_title' => 'Assigned to a canvasser' . ($assignedName ? ' (' . strtoupper($assignedName) . ')' : ''),
                    ]);
                }
                optional($pa->mrs)->update(["received_by" => $note, "received_at" => null]);
                Notifier::toUser($note, [
                    'title'   => 'PA Assigned to You',
                    'message' => "PA {$paNo} has been assigned to you for purchasing receival and canvass.",
                    'url'     => route('purchaser.index'),
                    'module'  => 'PA',
                    'status'  => "(For Purchasing Receival)",
                ]);
                // Tell the requestor a canvasser has been assigned.
                if ($requestorId) {
                    Notifier::toUser($requestorId, [
                        'title'   => 'Canvasser Assigned',
                        'message' => "The Purchase Advice for your MRS #{$mrsNo} has been assigned to " . ($pa->purchaser->name ?? 'a canvasser') . " for canvass.",
                        'url'     => $requestorUrl,
                        'module'  => 'PA',
                        'status'  => "(For Purchasing Receival)",
                    ]);
                }
                return redirect()->route('planner_pa.index')->with('success', 'PA assigned to ' . $pa->purchaser->name . '.');
            }

            if ($request->action == "receive") {
                History::context($pa, [
                    'action'          => 'received',
                    'title'           => 'Received for canvass',
                    'requestor_title' => 'Purchase advice received for canvass',
                ]);
                $pa->update(["status" => "RECEIVED FOR CANVASS (Purchasing Officer)", "received_at" => Carbon::now()]);
                // Re-receive: restart MRS aging from the current receipt date.
                if ($mrs) {
                    History::context($mrs, [
                        'action'          => 'received',
                        'title'           => 'Received for canvass — aging started',
                        'requestor_title' => 'Received by the canvasser',
                    ]);
                }
                optional($pa->mrs)->update(["received_at" => Carbon::now()]);
                if ($requestorId) {
                    Notifier::toUser($requestorId, [
                        'title'   => 'PA Received for Canvass',
                        'message' => "The Purchase Advice for your MRS #{$mrsNo} has been received by the Purchaser for canvass.",
                        'url'     => $requestorUrl,
                        'module'  => 'PA',
                        'status'  => "RECEIVED FOR CANVASS (Purchasing Officer)",
                    ]);
                }
                return back()->with('success', 'PA received.');
            }

            if ($request->action == "cancel") {
                $cancelData = [
                    "status" => "CANCELLED PURCHASED ADVICE",
                    "received_by" => NULL,
                    "received_at" => NULL,
                    "verified_by" => NULL,
                    "verified_at" => NULL,
                    "approved_by" => NULL,
                    "approved_at" => NULL,
                ];

                $roleName = optional(Auth::user()->role)->name;
                if ((int) Auth::user()->role_id === 7 || $roleName === 'MCD Verifier') {
                    $cancelData["verifier_remarks"] = $note ?: $pa->verifier_remarks;
                } elseif ($roleName === 'MCD Approver') {
                    $cancelData["approver_remarks"] = $note ?: $pa->approver_remarks;
                }

                History::context($pa, [
                    'action'          => 'cancelled',
                    'title'           => 'Purchase advice cancelled by the ' . ($roleName ?: 'MCD'),
                    'requestor_title' => 'Purchase advice cancelled',
                    'remarks'         => $note,
                ]);
                $pa->update($cancelData);
                // Cancelled PA is no longer with a purchaser: clear the MRS receipt so aging stops.
                if ($mrs) {
                    History::context($mrs, [
                        'action'               => 'cancelled',
                        'title'                => 'Canvasser assignment cleared (PA cancelled)',
                        'remarks'              => $note,
                        'visible_to_requestor' => false,
                    ]);
                }
                optional($pa->mrs)->update(["received_at" => NULL, "received_by" => NULL]);
                if ($requestorId) {
                    Notifier::toUser($requestorId, [
                        'title'   => 'PA Cancelled',
                        'message' => "The Purchase Advice for your MRS #{$mrsNo} has been cancelled." . ($note ? " Remarks: {$note}" : ''),
                        'url'     => $requestorUrl,
                        'module'  => 'PA',
                        'status'  => "CANCELLED PURCHASED ADVICE",
                    ]);
                }
                return redirect()->route('planner_pa.index')->with('success', 'PA Cancelled.');
            }

        } catch (\Exception $e) {
            return back()->with("error", "An error occurred: " . $e->getMessage());
        }
    }

    public function update_pa(Request $request)
    {
        $h = PurchaseAdvice::find($request->pa_id);

        // Cancellation is the end of the line — no edits from any role after it.
        if ($h && $h->isCancelled()) {
            return back()->with('error', self::PA_CANCELLED_LOCKED);
        }

        DB::beginTransaction();
        try {
            foreach ($h->details as $i) {
                $qtyToOrder = (int) $request->input('qty_to_order' . $i->id, $i->qty_to_order);
                $qtyOrdered = (int) $request->input('qty_ordered'  . $i->id, 0);
                if ($qtyOrdered > $qtyToOrder) {
                    return back()->withErrors(['qty_ordered' => "Qty Ordered ({$qtyOrdered}) cannot exceed Qty to Order ({$qtyToOrder}) for item #{$i->id}."])->withInput();
                }
            }

            foreach ($h->details as $i) {
                $i->update([
                    'par_to'               => $request->input('par_to'               . $i->id),
                    'qty_to_order'         => $request->input('qty_to_order'         . $i->id),
                    'previous_po'          => $request->input('previous_po'          . $i->id),
                    // Purchaser-only fields are not rendered on the planner's form (they show
                    // only when received_at is set). Fall back to the stored value so a planner
                    // re-edit does NOT wipe the PO#/qty/date the canvasser already entered.
                    'current_po'           => $request->input('current_po'           . $i->id, $i->current_po),
                    'qty_ordered'          => $request->input('qty_ordered'          . $i->id, $i->qty_ordered),
                    'po_date_released'     => $request->input('po_date_released'     . $i->id, $i->po_date_released),
                    'priority_no'          => $request->input('priority_no'          . $i->id),
                    'qty_per_delivery'     => $request->input('qty_per_delivery'     . $i->id),
                    'number_of_deliveries' => $request->input('number_of_deliveries' . $i->id),
                    'cost_code'            => $request->input('cost_code'            . $i->id),
                    'remarks'              => $request->input('remarks'              . $i->id),
                    'dlt'                  => $request->input('dlt'                  . $i->id),
                    'date_needed'          => $request->input('date_needed'          . $i->id),
                    'class_note'           => $request->input('class_note'           . $i->id),
                    'frequency'            => $request->input('frequency'            . $i->id),
                    'open_po'              => $request->input('open_po'              . $i->id),
                    'department'           => $request->input('department'           . $i->id),
                    'usage_rate_qty'       => $this->nullableNumericInput($request, 'usage_rate_qty'       . $i->id),
                    'on_hand'              => $this->nullableNumericInput($request, 'on_hand'              . $i->id),
                    'rof_months'           => $request->input('rof_months'           . $i->id),
                    'rof_months_w_request' => $request->input('rof_months_w_request' . $i->id),
                ]);
            }

            // A PA that is on HOLD, not yet received, but still carries a received_by was
            // returned to the planner by its purchaser/canvasser. After the planner re-edits
            // it, send it STRAIGHT back to that same purchaser (skip verify/approve/assign).
            $isPurchaserReturn = !$h->received_at
                && $h->received_by
                && strpos($h->status, 'HOLD') !== false;

            // A held PA being re-edited by the planner counts as a revision (Rev1, Rev2, ...).
            $paWasHeld = strpos(strtoupper((string) $h->status), 'HOLD') !== false;

            $headerUpdate = [
                'planner_remarks' => $request->input('planner_remarks'),
            ];

            if ($paWasHeld) {
                $headerUpdate['revision'] = (int) $h->revision + 1;
                $headerUpdate['revised_at'] = now();
            }

            if ($h->received_at) {
                $headerUpdate['status']  = $h->status;      // purchaser editing an already-received PA
                $headerUpdate['is_hold'] = $h->is_hold;
            } elseif ($isPurchaserReturn) {
                $headerUpdate['status']  = '(For Purchasing Receival)';  // bypass back to purchaser
                $headerUpdate['is_hold'] = 0;
            } else {
                // Normal re-entry: the PA goes back to the MCD Verifier's queue. It has NOT
                // been verified/approved yet in this cycle, so clear any stale verification/
                // approval stamps — otherwise the view (which gates the Verify button on
                // verified_at) shows "Verified" and the verifier cannot act on it.
                $headerUpdate['status']      = 'APPROVED (MCD PLANNER) - FOR VERIFICATION';
                $headerUpdate['is_hold']     = 0;
                $headerUpdate['verified_at'] = NULL;
                $headerUpdate['verified_by'] = NULL;
                $headerUpdate['approved_at'] = NULL;
                $headerUpdate['approved_by'] = NULL;
            }

            if ($h->received_at) {
                $contextTitle = 'Canvass details updated by the Purchaser/Canvasser';
                $contextReq   = 'Canvass details updated';
            } elseif ($isPurchaserReturn) {
                $contextTitle = 'Re-edited by the MCD Planner and sent straight back to the canvasser';
                $contextReq   = 'Purchase advice revised and returned to the canvasser';
            } else {
                $contextTitle = 'Re-edited by the MCD Planner and re-submitted for verification';
                $contextReq   = 'Purchase advice revised and re-submitted for verification';
            }

            History::context($h, [
                'action'          => $paWasHeld ? 'revised' : 'updated',
                'title'           => $contextTitle,
                'requestor_title' => $contextReq,
                'remarks'         => $request->input('planner_remarks'),
            ]);

            $h->update($headerUpdate);

            DB::commit();

            // The per-item diffs above are buffered so they read as one entry; write
            // them now rather than at request shutdown, so the trail is complete by
            // the time the redirect lands back on the PA screen.
            History::flushItemChanges();

            return back()->with('success', 'PA details now updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            // The buffered item diffs describe edits that never landed.
            History::discardItemChanges();
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function exportPurchaseAdvice($paHeader, $purchaseAdviceData, $salesHeader, $postedDate)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set Header
        $sheet->mergeCells('A1:T1');
        $sheet->setCellValue('A1', 'PURCHASE ADVISE');
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:T2');
        $sheet->setCellValue('A2', 'PA-' . ($paHeader->pa_number ?? '') . ($paHeader->revision > 0 ? '-Rev' . $paHeader->revision : ''));
        $sheet->getStyle('A2:T2')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A3:T3');
        $sheet->setCellValue('A3', 'DATE: ' . ($postedDate ? \Carbon\Carbon::parse($postedDate)->format('F j, Y h:i A') : 'Not Verified') . ($paHeader->revision > 0 && $paHeader->revised_at ? '     |     REVISED: ' . \Carbon\Carbon::parse($paHeader->revised_at)->format('F j, Y h:i A') : ''));
        $sheet->getStyle('A3:T3')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Table Header
        $headers = [
            'No',
            'Stock Type',
            'Inv. Code',
            'Stock Code',
            'Stock Description',
            'OEM ID',
            'UoM',
            'Usage Rate',
            'On Hand',
            'On Order',
            'Min Qty',
            'Max Qty',
            'Qty To Order',
            'Date Needed',
            'Frequency',
            'PARTO',
            'End-users/MRS#',
            'Priority #',
            'Previous PO#',
            'Current PO#',
            'PO Date Released',
            'Qty Ordered',
            'Balance QTY for PO'
        ];

        $row = 5; // Start at row 5 for the table headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
            $sheet->getStyle("{$col}{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $col++;
        }

        // Table Data
        $row++;
        foreach ($purchaseAdviceData as $index => $item) {
            if ($item["is_hold"] == 0) {
                $col = 'A';
                $data = [
                    $index + 1,
                    $item['stock_type'] ?? '',
                    $item['inv_code'] ?? '',
                    $item['stock_code'] === 'null' ? '' : $item['stock_code'],
                    $item['item_description'],
                    $item['OEM_ID'] ?? '',
                    $item['UoM'] ?? '',
                    (int) $item['usage_rate_qty'] ?? '',
                    $item['on_hand'],
                    $item['open_po'] ?? '',
                    $item['min_qty'] ?? '',
                    $item['max_qty'] ?? '',
                    (int) $item['qty_order'] ?? '',
                    $item['date_needed'],
                    $item['frequency'],
                    explode(':', $item['par_to'])[0],
                    $salesHeader->order_number ?? $item['order_number'],
                    $salesHeader->priority ?? $item['priority'],
                    $item['previous_mrs'] ?? '',
                    $item['po_no'] ?? '',
                    isset($item['po_date_released']) ? \Carbon\Carbon::parse($item['po_date_released'])->format('Y-m-d') : '',
                    $item['qty_ordered'] ?? '',
                    ((int) $item['qty_order'] - (int) $item['qty_ordered']),
                ];
                foreach ($data as $value) {
                    $sheet->setCellValue("{$col}{$row}", $value);
                    $sheet->getStyle("{$col}{$row}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $col++;
                }
                $row++;
            }
        }

        // Footer
        $row += 2;
        $sheet->setCellValue("A{$row}", 'Prepared by:');
        $sheet->setCellValue("D{$row}", 'Reviewed by:');
        $sheet->setCellValue("F{$row}", 'Approved by:');
        $sheet->setCellValue("H{$row}", 'Received by:');

        $row++;
        $sheet->setCellValue("A{$row}", 'Name');
        $sheet->setCellValue("B{$row}", strtoupper($salesHeader->planner->name ?? ''));
        $sheet->setCellValue("D{$row}", 'JOHN DALE P. RANOCO');
        $sheet->setCellValue("F{$row}", 'MYRNA G. IMPROSO');
        $sheet->setCellValue("H{$row}", strtoupper($salesHeader->purchaser->name ?? ''));

        $row++;
        $sheet->setCellValue("A{$row}", 'Designation');
        $sheet->setCellValue("B{$row}", 'MCD Planner');
        $sheet->setCellValue("D{$row}", $salesHeader->verified_at ? 'Material Planning Supervisor' : '');
        $sheet->setCellValue("F{$row}", $salesHeader->approved_at ? 'MCD Manager' : '');
        $sheet->setCellValue("H{$row}", $salesHeader->received_at ? 'Purchaser' : '');

        $row++;
        $sheet->setCellValue("A{$row}", 'Signature');
        $sheet->setCellValue("B{$row}", '');
        $sheet->setCellValue("D{$row}", '');
        $sheet->setCellValue("F{$row}", '');
        $sheet->setCellValue("H{$row}", '');

        $row++;
        $sheet->setCellValue("A{$row}", 'Date');
        $sheet->setCellValue("B{$row}", \Carbon\Carbon::parse($salesHeader->planner_at ?? $salesHeader->created_at)->format('F j, Y h:i A'));
        $sheet->setCellValue("D{$row}", $salesHeader->verified_at ? \Carbon\Carbon::parse($salesHeader->verified_at)->format('F j, Y h:i A') : '');
        $sheet->setCellValue("F{$row}", $salesHeader->approved_at ? \Carbon\Carbon::parse($salesHeader->approved_at)->format('F j, Y h:i A') : '');
        $sheet->setCellValue("H{$row}", $salesHeader->received_at ? \Carbon\Carbon::parse($salesHeader->received_at)->format('F j, Y h:i A') : '');

        $writer = new Xlsx($spreadsheet);
        $filename = 'PA-' . $paHeader->pa_number . ($paHeader->revision > 0 ? '-Rev' . $paHeader->revision : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function hold_pa(Request $request)
    {
        $purchaseAdvice = PurchaseAdvice::where('mrs_id', $request->id)->first();
        if (!$purchaseAdvice) {
            return response()->json(["message" => "Not found."], 404);
        }
        if ($purchaseAdvice->isCancelled()) {
            return response()->json(["message" => self::PA_CANCELLED_LOCKED], 422);
        }
        $purchaseAdvice->update($request->all());
        return response()->json(["message" => "Purchase Advice status updated"], 200);
    }

    // Per-line hold for PA SR items (set by the purchaser/canvasser after receiving).
    // Held lines are excluded from the SR print/Excel and from the aging balance.
    public function hold_pa_item(Request $request)
    {
        $item = PurchaseAdviceDetail::find($request->id);
        if (!$item) {
            return response()->json(["message" => "Not found."], 404);
        }
        if (optional($item->purchaseAdvice)->isCancelled()) {
            return response()->json(["message" => self::PA_CANCELLED_LOCKED], 422);
        }

        $data = ['is_hold' => (int) $request->is_hold];
        if ($request->has('hold_remarks')) {
            $data['hold_remarks'] = $request->hold_remarks;
        }
        $item->update($data);

        return response()->json(["message" => "Item hold status updated"], 200);
    }

    public function bulk_upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded.'], 400);
        }

        $file      = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        if (!in_array($extension, ['xlsx', 'xls'])) {
            return response()->json(['error' => 'Invalid file format. Only .xls and .xlsx files are allowed.'], 400);
        }

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 120);
        set_time_limit(120);

        try {
            $filePath = $file->getRealPath();

            $chunkFilter = new class implements IReadFilter {
                private $startRow;
                private $endRow;

                public function setRows($startRow, $endRow)
                {
                    $this->startRow = $startRow;
                    $this->endRow   = $endRow;
                }

                public function readCell($columnAddress, $row, $worksheetName = '')
                {
                    return $row >= $this->startRow && $row <= $this->endRow;
                }
            };

            // --- Read header row (row 4) ---
            $chunkFilter->setRows(4, 4);
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadFilter($chunkFilter);
            $reader->setReadDataOnly(true);
            $headerSpreadsheet = $reader->load($filePath);
            $headerRow         = $headerSpreadsheet->getActiveSheet()->toArray()[3] ?? null;
            $headerSpreadsheet->disconnectWorksheets();
            unset($headerSpreadsheet);

            if (!$headerRow || count($headerRow) < 24) {
                return response()->json(['error' => 'Header row has insufficient columns. Expected 24 columns.'], 400);
            }

            $expectedHeaders = [
                "Location", "Inv Code", "Stock Type", "Stock Code", "Stock Description",
                "OEM ID", "UOM", "Average Unit Price", "Average Monthly UR",
                "On-Hand", "Open PO", "DLT", "Qty To Order", "Date Needed",
                "Qty Per Delivery", "Number Of Deliveries", "Classic Note",
                "End-User/ PAR To", "Previous PO", "PRIO#", "Cost Code", "Remarks",
                "No. Of Months (SOH+OO)", "No. Of Months (SOH+OO+New Reqeust)"
            ];

            $fileHeaders = array_slice(
                array_map('strtoupper', array_map('trim', $headerRow)),
                0, 24
            );
            $fileHeaders = array_map(function ($header) {
                return trim(preg_replace('/\(as of [A-Za-z]+\)/i', '', $header));
            }, $fileHeaders);

            if ($fileHeaders !== array_map('strtoupper', $expectedHeaders)) {
                return response()->json([
                    'error'    => 'Headers not valid!',
                    'expected' => $expectedHeaders,
                    'outcome'  => $fileHeaders
                ], 400);
            }

            // --- Read data in chunks of 100 rows ---
            $products  = [];
            $chunkSize = 100;
            $startRow  = 5;

            while (true) {
                $chunkFilter->setRows($startRow, $startRow + $chunkSize - 1);
                $reader = IOFactory::createReaderForFile($filePath);
                $reader->setReadFilter($chunkFilter);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($filePath);
                $rows        = $spreadsheet->getActiveSheet()->toArray();
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
                gc_collect_cycles();

                $dataRows = array_filter($rows, function ($r) {
                    return !empty(trim((string)($r[3] ?? '')));
                });

                if (empty($dataRows)) {
                    break;
                }

                foreach ($dataRows as $row) {
                    $code = trim((string)($row[3] ?? ''));
                    if ($code === '') {
                        continue;
                    }

                    $product = Product::where('code', $code)->first();
                    if ($product) {
                        $rawQty            = isset($row[12]) ? $row[12] : null;
                        $rawDlt            = isset($row[11]) ? $row[11] : null;
                        $rawQtyPerDelivery = isset($row[14]) ? $row[14] : null;
                        $rawNumDeliveries  = isset($row[15]) ? $row[15] : null;
                        $rawRofMonths      = isset($row[22]) ? $row[22] : null;
                        $rawRofMonthsWReq  = isset($row[23]) ? $row[23] : null;

                        $products[] = [
                            'id'                   => $product->id,
                            'inv_code'             => isset($row[1])  ? $row[1]  : $product->inv_code,
                            'stock_type'           => isset($row[2])  ? $row[2]  : $product->stock_type,
                            'stock_code'           => $product->code,
                            'description'          => $product->name,
                            'oem_id'               => $product->oem,
                            'uom'                  => $product->uom,
                            'usage_rate_qty'       => isset($row[8])  ? $row[8]  : null,
                            'on_hand'              => isset($row[9])  ? $row[9]  : null,
                            'open_po'              => isset($row[10]) ? $row[10] : null,
                            'dlt'                  => is_numeric($rawDlt)            ? (float)$rawDlt           : null,
                            'qty_to_order'         => is_numeric($rawQty)            ? (int)$rawQty             : 0,
                            'date_needed'          => isset($row[13]) ? $row[13] : null,
                            'qty_per_delivery'     => is_numeric($rawQtyPerDelivery) ? (int)round((float)$rawQtyPerDelivery) : null,
                            'number_of_deliveries' => is_numeric($rawNumDeliveries)  ? (int)$rawNumDeliveries   : null,
                            'class_note'           => isset($row[16]) ? $row[16] : null,
                            'par_to'               => isset($row[17]) ? $row[17] : 'N/A',
                            'previous_po'          => isset($row[18]) ? $row[18] : null,
                            'priority_no'          => isset($row[19]) ? $row[19] : null,
                            'cost_code'            => isset($row[20]) ? $row[20] : null,
                            'remarks'              => isset($row[21]) ? $row[21] : null,
                            'rof_months'           => is_numeric($rawRofMonths)     ? (float)$rawRofMonths      : null,
                            'rof_months_w_request' => is_numeric($rawRofMonthsWReq) ? (float)$rawRofMonthsWReq  : null,
                            // no longer in excel
                            'department'           => null,
                            'frequency'            => null,
                        ];
                    }
                }

                if (count($rows) < $chunkSize) {
                    break;
                }

                $startRow += $chunkSize;
            }

            return response()->json(['success' => true, 'data' => $products], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error reading the Excel file: ' . $e->getMessage()], 500);
        }
    }

}
