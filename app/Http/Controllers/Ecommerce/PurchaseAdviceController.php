<?php

namespace App\Http\Controllers\Ecommerce;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;
use App\Helpers\ListingHelper;

use App\Models\Ecommerce\{
    DeliveryStatus, SalesPayment, SalesHeader, SalesDetail, Product, InventoryRequest, InventoryRequestItems, PurchaseAdvice
};

use App\Models\{
    Permission, Page, Issuance, IssuanceItem, Department, ViewLog, User, Role
};

use PDF;

use Auth;

class PurchaseAdviceController extends Controller
{
    private $searchFields = ['order_number','response_code','created_at', 'updated_at'];

    public function __construct()
    {
        //Permission::module_init($this, 'sales_transaction');
    }

    public function index()
    {

        $customConditions = [
            [
                'field' => 'status',
                'operator' => '=',
                'value' => 'active',
                'apply_to_deleted_data' => true
            ],
        ];


        $listing = new ListingHelper('desc',10,'order_number',$customConditions);
        $sales = $listing->simple_search(SalesHeader::class, $this->searchFields);

        $sales = SalesHeader::with('items.issuances')->withSum('issuances', 'qty')->where('id','>','0');
        if(isset($_GET['startdate']) && $_GET['startdate']<>'')
            $sales = $sales->where('created_at','>=',$_GET['startdate']);
        if(isset($_GET['enddate']) && $_GET['enddate']<>'')
            $sales = $sales->where('created_at','<=',$_GET['enddate'].' 23:59:59');
        if(isset($_GET['search']) && $_GET['search']<>'')
            $sales = $sales->where('order_number','like','%'.$_GET['search'].'%');
        if(isset($_GET['customer_filter']) && $_GET['customer_filter']<>'')
            $sales = $sales->where('customer_name','like','%'.$_GET['customer_filter'].'%');
        if(isset($_GET['del_status']) && $_GET['del_status']<>'')
            $sales = $sales->whereIn('status', $_GET['del_status']);
        $sales = $sales->whereIn('status', ['APPROVED (MCD Approver) - PA for Delegation', '(For Purchasing Receival)'])->where('for_pa', 1)->orderBy('id','desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.index',compact('sales','filter','searchType','departments'));
    }

    public function view_mrs(Request $request, $id)
    {
        $sales = SalesHeader::where('id',$id)->first();
        $salesPayments = SalesPayment::where('sales_header_id',$id)->get();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id',$id)->get();
        $totalPayment = SalesPayment::where('sales_header_id',$id)->sum('amount');
        $totalNet = SalesHeader::where('id',$id)->sum('net_amount');
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();

        $purchasers = User::where('role_id', 9)->get();
        
        if($totalNet <= $totalPayment)
        $status = 'PAID';
        
        else $status = 'UNPAID';    
        return view('admin.purchasing.view',compact('sales','salesPayments','salesDetails','status', 'role', 'purchasers'));
    }

    public function create_pa(Request $request, $id)
    {
        $sales = SalesHeader::find($id);
        
        if(empty($sales))
        {
            return back()->with('error','Something went wrong!');
        }

        $sales->update(["is_pa"=>1]);
        return redirect()->route('pa.index')->with('success','Purchase Advice created successfully!');
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

        $listing = new ListingHelper('desc',10,'order_number',$customConditions);
        $sales = $listing->simple_search(SalesHeader::class, $this->searchFields);

        $sales = SalesHeader::with('items.issuances')->withSum('issuances', 'qty')->where('id','>','0');

        if(isset($_GET['startdate']) && $_GET['startdate']<>'')
            $sales = $sales->where('created_at','>=',$_GET['startdate']);
        if(isset($_GET['enddate']) && $_GET['enddate']<>'')
            $sales = $sales->where('created_at','<=',$_GET['enddate'].' 23:59:59');
        if(isset($_GET['search']) && $_GET['search']<>'')
            $sales = $sales->where('order_number','like','%'.$_GET['search'].'%');
        if(isset($_GET['customer_filter']) && $_GET['customer_filter']<>'')
            $sales = $sales->where('customer_name','like','%'.$_GET['customer_filter'].'%');
        if(isset($_GET['del_status']) && $_GET['del_status']<>'')
            $sales = $sales->whereIn('status', $_GET['del_status']);
        $sales = $sales->whereIn('status', ['RECEIVED FOR CANVASS (Purchasing Officer)'])->where('for_pa', 1)->where('is_pa', 1)->orderBy('id','desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.manage',compact('sales','filter','searchType','departments'));
    }

    public function purchaser_index(){
        $customConditions = [
            [
                'field' => 'status',
                'operator' => '=',
                'value' => 'active',
                'apply_to_deleted_data' => true
            ],
        ];


        $listing = new ListingHelper('desc',10,'order_number',$customConditions);
        $sales = $listing->simple_search(SalesHeader::class, $this->searchFields);

        $sales = SalesHeader::with('items.issuances')->withSum('issuances', 'qty')->where('id','>','0');
        if(isset($_GET['startdate']) && $_GET['startdate']<>'')
            $sales = $sales->where('created_at','>=',$_GET['startdate']);
        if(isset($_GET['enddate']) && $_GET['enddate']<>'')
            $sales = $sales->where('created_at','<=',$_GET['enddate'].' 23:59:59');
        if(isset($_GET['search']) && $_GET['search']<>'')
            $sales = $sales->where('order_number','like','%'.$_GET['search'].'%');
        if(isset($_GET['customer_filter']) && $_GET['customer_filter']<>'')
            $sales = $sales->where('customer_name','like','%'.$_GET['customer_filter'].'%');
        if(isset($_GET['del_status']) && $_GET['del_status']<>'')
            $sales = $sales->whereIn('status', $_GET['del_status']);
        $sales = $sales->where('received_by', Auth::id())->where('for_pa', 1)->where('is_pa', 1)->where('status','(For Purchasing Receival)')->orderBy('id','desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.purchaser_index',compact('sales','filter','searchType','departments'));
    }

    public function purchaser_received_index(){
        $customConditions = [
            [
                'field' => 'status',
                'operator' => '=',
                'value' => 'active',
                'apply_to_deleted_data' => true
            ],
        ];


        $listing = new ListingHelper('desc',10,'order_number',$customConditions);
        $sales = $listing->simple_search(SalesHeader::class, $this->searchFields);

        $sales = SalesHeader::with('items.issuances')->withSum('issuances', 'qty')->where('id','>','0');
        if(isset($_GET['startdate']) && $_GET['startdate']<>'')
            $sales = $sales->where('created_at','>=',$_GET['startdate']);
        if(isset($_GET['enddate']) && $_GET['enddate']<>'')
            $sales = $sales->where('created_at','<=',$_GET['enddate'].' 23:59:59');
        if(isset($_GET['search']) && $_GET['search']<>'')
            $sales = $sales->where('order_number','like','%'.$_GET['search'].'%');
        if(isset($_GET['customer_filter']) && $_GET['customer_filter']<>'')
            $sales = $sales->where('customer_name','like','%'.$_GET['customer_filter'].'%');
        if(isset($_GET['del_status']) && $_GET['del_status']<>'')
            $sales = $sales->whereIn('status', $_GET['del_status']);
        $sales = $sales->where('received_by', Auth::id())->where('for_pa', 1)->where('is_pa', 1)->where('status','RECEIVED FOR CANVASS (Purchasing Officer)')->orderBy('id','desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.purchaser_index_received',compact('sales','filter','searchType','departments'));
    }

    public function purchaser_view(Request $request, $id){
        $sales = SalesHeader::where('id',$id)->first();
        $salesPayments = SalesPayment::where('sales_header_id',$id)->get();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id',$id)->get();
        $totalPayment = SalesPayment::where('sales_header_id',$id)->sum('amount');
        $totalNet = SalesHeader::where('id',$id)->sum('net_amount');
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();

        $purchasers = User::where('role_id', 9)->get();
        
        if($totalNet <= $totalPayment)
        $status = 'PAID';
        
        else $status = 'UNPAID';    
        return view('admin.purchasing.purchaser_view',compact('sales','salesPayments','salesDetails','status', 'role', 'purchasers'));
    }

    public function receive_pa(Request $request) 
    {
        //dd($request->all());
        $header_id = $request->sales_header_id;
        $h = SalesHeader::find($header_id);
        
        DB::beginTransaction();
        try {
            foreach ($h->items as $i) {
                $po_no = $request->input('po_no'.$i->id);
                $qty_ordered = $request->input('qty_ordered'.$i->id);
                $po_date_released = $request->input('po_date_released'.$i->id);
                $i->update(["po_no" => $po_no, "qty_ordered" => $qty_ordered, "po_date_released" => $po_date_released]);
            }
            
            $h->update([
                "response_code" => Auth::id(),
            ]);
            DB::commit();
            return back()->with("success", "MRS request details updated.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "An error occurred: " . $e->getMessage());
        }
    }

    public function pa_action(Request $request, $id){
        try{
            $mrs = SalesHeader::find($id);
            $note = $request->query('note', '');
            if ($request->action == "hold-purchaser") {
                $mrs->update(["status" => "HOLD (For MCD Planner re-edit)", "purchaser_note" => $note]);
                return back()->with('success', 'Request on-hold');
            }
        }catch(\Exception $e){
            return back()->with("error", "An error occurred: " . $e->getMessage());
        }
    }

    public function generate_report(Request $request) 
    {
        $salesHeader  = SalesHeader::with('items.issuances')->where('order_number', $request->orderNumber)->first();
        $paHeader = PurchaseAdvice::where("mrs_id", $salesHeader->id)->first();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id', $salesHeader->id)->get();
        //dd($salesDetails);
        $postedDate = $salesHeader->verified_at;
        
        $purchaseAdviceData = [];

        $requestor = explode(":", $salesHeader->requested_by);

        foreach ($salesDetails as $sale) 
        {    
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
            if ($items->isEmpty()) 
            {
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
                    'prepared_by_name' => $requestor[0],
                    'prepared_by_designation' => $requestor[1], 
                    'prepared_by_date' => optional($sale->created_at)->format('Y-m-d h:i:s A') ?? ''
                ];

            } else {
                $itemsWithCostCode = $items->map(function($item) use ($sale, $product, $requestor) {
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
                    $item->prepared_by_name = $requestor[0];
                    $item->prepared_by_designation = $requestor[1]; 
                    $item->prepared_by_date = optional($sale->created_at)->format('Y-m-d h:i:s A') ?? '';
                    return $item;
                });
                $purchaseAdviceData = array_merge($purchaseAdviceData, $itemsWithCostCode->toArray());
            }
        }

        $pdf = \PDF::loadHtml(view('admin.purchasing.components.generate-report', compact('purchaseAdviceData', 'postedDate', 'salesHeader', 'paHeader', 'requestor')));
        $pdf->setPaper("legal", "landscape");
        return $pdf->download('PA-'.$paHeader->pa_number.'.pdf');
    }

    public function generate_report_pa(Request $request) 
    {
        $paHeader = PurchaseAdvice::where("pa_number", $request->paNumber)->first();
        $salesHeader = $paHeader;
        $salesDetails = $paHeader->items;
        //dd($salesDetails);
        $postedDate = $salesHeader->verified_at;
        $purchaseAdviceData = [];

        foreach($salesDetails as $item){
            $purchaseAdviceData[] = [
                'UoM' => $item->product->uom,
                'stock_code' => $item->product->code,
                'cost_code' => $item->cost_code,
                'frequency' => $item->frequency,
                'purpose' => $item->purpose,
                'date_needed' => Carbon::parse($item->date_needed)->format('Y-m-d'),
                'par_to' => $item->par_to,
                'previous_mrs' => $item->previous_mrs,
                'OEM_ID' => $item->product->oem,
                'stock_type' => $item->product->stock_type,
                'inv_code' => $item->product->inv_code,
                'usage_rate_qty' => $item->product->usage_rate_qty,
                'on_hand' => $item->product->on_hand,
                'min_qty' => $item->product->min_qty,
                'max_qty' => $item->product->max_qty,
                'qty_order' => $item->qty_to_order,
                'open_po' => $item->open_po,
                'po_no' => $item->po_no,
                'qty_ordered' => $item->qty_ordered,
                'po_date_released' => $item->po_date_released,
                'is_hold' => 0,
                'item_description' => $item->product->name,
                'order_number' => $item->header->order_number,
                'priority' => $item->header->priority
            ];
        }

        $pdf = \PDF::loadHtml(view('admin.purchasing.components.generate-report', compact('purchaseAdviceData', 'postedDate', 'salesHeader', 'paHeader')));
        $pdf->setPaper("legal", "landscape");
        return $pdf->download('PA-'.$paHeader->pa_number.'.pdf');
    }

    public function planner_pa() {
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
        $salesQuery = PurchaseAdvice::query();
    
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
                        END IN (" . implode(',', array_map(fn($status) => "'$status'", $statuses)) . ")
                    ");
                });
            });
        }
    
        // Define role-based status conditions
        $statusConditions = [
            "MCD Planner" => [],
            "MCD Verifier" => [
                '(For Purchasing Receival)',
                'RECEIVED FOR CANVASS (Purchasing Officer)',
                'APPROVED (MCD PLANNER) - FOR VERIFICATION',
                'VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL',
                'APPROVED (MCD Approver) - PA for Delegation'
            ],
            "MCD Approver" => [
                '(For Purchasing Receival)',
                'RECEIVED FOR CANVASS (Purchasing Officer)',
                'VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL',
                'APPROVED (MCD Approver) - PA for Delegation'
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
            if ($role->name !== "MCD Planner") {
                $salesQuery->whereIn('status', $statusConditions[$role->name]);
            }
    
            if ($role->name === "Purchaser") {
                $salesQuery->where('received_by', Auth::id());
            }
        }
    
        // Paginate the final query
        $sales = $salesQuery->orderBy('id', 'desc')->paginate(10);
    
        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';
    
        // Get distinct statuses for dropdown filter
        $statuses = PurchaseAdvice::distinct()->pluck('status');
    
        // Return the view with compacted variables
        return view('admin.purchasing.planner_pa', compact('sales', 'filter', 'searchType', 'role', 'statuses'));
    }    
    
    public function planner_pa_create(){
        $mrs_numbers = SalesHeader::whereNotNull('planner_at')
            ->whereIn('status', ['RECEIVED FOR CANVASS (Purchasing Officer)', 'APPROVED (MCD Planner) - MRS For Verification', 'HOLD (For MCD Planner re-edit)', 'VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL', 'APPROVED (MCD Approver) - PA for Delegation'])
            ->orWhere('status', 'LIKE', '%FULLY APPROVED%')
            ->orderBy('id', 'desc')->take(1000)->get();
        $pa_number = $this->next_pa_number();

        return view('admin.purchasing.planner_pa_create', compact('mrs_numbers', 'pa_number'));
    }    
    
    public function next_pa_number(){
        $last_order = PurchaseAdvice::whereYear('created_at', Carbon::now()->year)->orderBy('created_at', 'desc')->first();
        preg_match_all('/[A-Z]/', auth()->user()->firstname.' '.auth()->user()->lastname , $matches);
        $initials = implode('', $matches[0]);

        if(empty($last_order)){
            $next_number = $initials."-".date('y')."0001";
        }
        else{
            $order_number = substr($last_order->pa_number, -4);
            if(!isset($order_number)){
                $next_number = $initials."-".date('y')."0001";
            }
            else{
                $next_number = $initials."-".date('y').str_pad((((int)$order_number) + 1), 4, '0', STR_PAD_LEFT);
            }
        }
        return $next_number;
    }

    public function mrs_items(Request $request){
        $items = SalesDetail::whereIn('sales_header_id', $request->ids)
                            ->where('promo_id',1)
                            ->whereNull('is_pa')
                            ->with('header')
                            ->with('product')
                            ->get();
    
        return response()->json(["data" => $items], 200);
    }
    
    public function insert_pa(Request $request){
        $paNumber = $this->next_pa_number();

        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer|exists:ecommerce_sales_details,id',
        ]);

        $selectedItems = $request->input('selected_items');

        DB::transaction(function () use ($paNumber, $selectedItems, $request) {
            $pa = PurchaseAdvice::create([
                "pa_number" => $paNumber, 
                "status" => "APPROVED (MCD PLANNER) - FOR VERIFICATION",
                "created_by" => Auth::id(),
                "planner_remarks" => $request->input('planner_remarks')
            ]);
            foreach ($selectedItems as $itemId) {
                SalesDetail::where('id', $itemId)->update(["is_pa" => $pa->id]);
            }
        });

        return response()->json([
            'message' => 'Purchase advice created successfully',
            'redirect' => route('planner_pa.index'),
        ], 201);
    }

    public function delete_pa($id)
    {
        $sale = PurchaseAdvice::findOrFail($id);
        SalesDetail::where('is_pa', $id)->update(['is_pa' => NULL]);
        $sale->delete();

        return back()->with('success', 'Purchase Advice deleted successfully.');
    }

    public function planner_pa_view(Request $request, $id){
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();
        $paHeader = PurchaseAdvice::where('id',$id)->first();
        $purchasers = User::where('role_id', 9)->get();
        return view('admin.purchasing.planner_pa_view', compact('paHeader', 'role', 'purchasers'));
    }

    public function purchase_action(Request $request, $id){
        try{
            $pa = PurchaseAdvice::find($id);
            $note = $request->query('note', '');
            if ($request->action == "verify") {
                $pa->update(["status" => "VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL", "verified_at" => Carbon::now(), "verified_by" => Auth::id()]);
                return redirect()->route('planner_pa.index')->with('success', 'PA verified.');
            }

            if ($request->action == "approve") {
                $pa->update(["status" => "APPROVED (MCD Approver) - PA for Delegation", "approved_at" => Carbon::now(), "approved_by" => Auth::id()]);
                return redirect()->route('planner_pa.index')->with('success', 'PA approved.');
            }

            if ($request->action == "assign") {
                $pa->update(["status" => "(For Purchasing Receival)", "received_by" => $note]);
                return redirect()->route('planner_pa.index')->with('success', 'PA assigned to '.$pa->purchaser->name.'.');
            }

            if ($request->action == "receive") {
                $pa->update(["status" => "RECEIVED FOR CANVASS (Purchasing Officer)", "received_at" => Carbon::now()]);
                return back()->with('success', 'PA received.');
            }

            if ($request->action == "cancel") {
                $pa->update([
                    "status" => "CANCELLED PURCHASED ADVICE", 
                    "received_by" => NULL, 
                    "received_at" => NULL,
                    "verified_by" => NULL,
                    "verified_at" => NULL,
                    "approved_by" => NULL,
                    "approved_at" => NULL,    
                ]);
                return redirect()->route('planner_pa.index')->with('success', 'PA Cancelled.');
            }

        }catch(\Exception $e){
            return back()->with("error", "An error occurred: " . $e->getMessage());
        }
    }

    public function update_pa(Request $request){
        //dd($request->all());
        $h = PurchaseAdvice::find($request->pa_id);
        
        DB::beginTransaction();
        try {
            foreach ($h->items as $i) {
                $qty_to_order = $request->input('quantityToOrder'.$i->id);
                $previous_mrs = $request->input('previous_no'.$i->id);
                $po_no = $request->input('po_no'.$i->id);
                $qty_ordered = $request->input('qty_ordered'.$i->id);
                $po_date_released = $request->input('po_date_released'.$i->id);
                $i->update([
                    "qty_to_order" => $qty_to_order, 
                    "previous_mrs" => $previous_mrs,
                    "po_no" => $po_no, 
                    "qty_ordered" => $qty_ordered, 
                    "po_date_released" => $po_date_released
                ]);
            }

            $h->update([
                "status" => $h->received_at ? $h->status : 'APPROVED (MCD PLANNER) - FOR VERIFICATION',
                "planner_remarks" => $request->planner_remarks
            ]);
            
            DB::commit();
            return back()->with("success", "PA details now updated.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "An error occurred while updating the PA: " . $e->getMessage());
        }
    }
}
