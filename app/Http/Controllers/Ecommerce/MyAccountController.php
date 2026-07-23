<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Mail\RevisedMrsNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

use App\Models\Ecommerce\{
    Cart, SalesHeader, SalesDetail, Product
};

use App\Models\{
    Page, User, Role
};

use Auth;
use DateTime;
use Carbon\Carbon;

class MyAccountController extends Controller
{
    public function manage_account(Request $request)
    {
        $page = new Page;
        $page->name = 'My Account';

        $member = auth()->user();
        $user = auth()->user();

        return view('theme.pages.customer.manage-account', compact('member', 'user', 'page'));
    }

    public function update_personal_info(Request $request)
    {
        $requestData = $request->except(['_token']);
        $requestData['name'] = $request->firstname.' '.$request->lastname;

        User::whereId(Auth::id())->update($requestData);

        return redirect()->back()->with('success', 'Account details has been updated');
    }

    public function change_password()
    {
        $page = new Page();
        $page->name = 'Change Password';

        return view('theme.pages.customer.change-password',compact('page'));
    }

    public function update_password(Request $request)
    {
        $personalInfo = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) {
                if (!\Hash::check($value, auth()->user()->password)) {
                    return $fail(__('The current password is incorrect.'));
                }
            }],
            'password' => [
                'required',
                'min:8',
                'max:150', 
                'regex:/[a-z]/', // must contain at least one lowercase letter
                'regex:/[A-Z]/', // must contain at least one uppercase letter
                'regex:/[0-9]/', // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character              
            ],
            'confirm_password' => 'required|same:password',
        ]);

        auth()->user()->update(['password' => bcrypt($personalInfo['password'])]);

        return back()->with('success', 'Password has been updated');
    }
    /*
    this line is brought to you by
    */
    public function orders(Request $request)
    {
        $page = new Page();
        $page->name = 'MRS - For Purchase (DP, Stock Item)';

        // Rows are loaded via DataTables server-side processing (see ordersData()).
        $postedCount = SalesHeader::where('status', 'POSTED')->where('user_id', Auth::id())->count();

        $inProgressOverdue = SalesHeader::where('status', 'like', '%IN-PROGRESS%')
            ->where('created_at', '<=', now()->subDays(2))
            ->where('user_id', Auth::id())
            ->count();

        $percentageOverdue = $postedCount > 0
            ? number_format(($inProgressOverdue / $postedCount) * 100, 2)
            : 0;

        return view('theme.pages.customer.orders', compact('page', 'postedCount', 'inProgressOverdue', 'percentageOverdue'));
    }

    /**
     * DataTables server-side processing feed for the customer MRS list.
     */
    public function ordersData(Request $request)
    {
        if (!Auth::check()) {
            abort(401);
        }

        // index -> orderable DB column. 'pa' (index 1) is a relationship, handled below.
        $columns = ['order_number', 'pa', 'created_at', 'purpose', 'status'];

        $base = SalesHeader::with(['purchaseAdvice', 'items', 'issuances', 'purchaser'])
            ->where('user_id', Auth::id());
        $recordsTotal = (clone $base)->count();

        $query = clone $base;

        // Global search box
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('costcode', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('purchaseAdvice', function ($pa) use ($search) {
                        $pa->where('pa_number', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter chips
        $statusFilter = $request->input('status_filter');
        if (!empty($statusFilter)) {
            $query->where('status', 'like', "%{$statusFilter}%");
        }

        $recordsFiltered = (clone $query)->count();

        // Ordering (default: newest first)
        $orderColIndex = (int) $request->input('order.0.column', 2);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColIndex] ?? 'created_at';
        if ($orderColumn === 'pa') {
            $orderColumn = 'created_at'; // PA# is a relationship, not a sortable column
        }
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $data = $query->get()->map(function ($sale) {
            return [
                'mrs_no'  => '<span class="fw-bold">' . e($sale->order_number) . '</span>',
                'pa'      => '<span class="badge2">' . e($sale->purchaseAdvice->pa_number ?? 'N/A') . '</span>',
                'created' => \Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i A'),
                'remarks' => '<span class="small">' . e($sale->purpose) . '</span>',
                'status'  => trim(view('theme.pages.customer._mrs-status-cell', compact('sale'))->render()),
                'options' => trim(view('theme.pages.customer._mrs-options-cell', compact('sale'))->render()),
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * Rendered "View Details" modal content for a single MRS (loaded on demand).
     */
    public function orderDetails($id)
    {
        $sale = SalesHeader::with(['items.product', 'purchaseAdvice', 'user'])
            ->where('user_id', Auth::id())
            ->find($id);

        if (!$sale) {
            abort(404);
        }

        // Fetch the WFS approvers for this one MRS (moved out of the list load).
        if (!defined('__ROOT__')) {
            define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        }
        $data = [
            "token"   => config('app.key'),
            "transid" => 'MRS' . $sale->order_number,
        ];
        $approvers = require(__ROOT__ . '\api\wfs-approvers-api.php');
        $sale->approvers = collect($approvers);

        return view('theme.pages.customer._mrs-view-details', compact('sale'))->render();
    }

    public function cancel_order(Request $request)
    {
        $sales = SalesHeader::find($request->orderid);
        $data = [
            "type" => config('app.name'),
            "transid" => 'MRS'.$sales->order_number,
            "token" => config('app.key')
        ];

        define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $result = require(__ROOT__ . '\api\cancel-transaction.php');

        if ($result) {
            $sales->update(['status' => 'REQUEST CANCELLED (Cancelled by '.auth()->user()->name.')', 'delivery_status' => 'CANCELLED']);
            Cart::where('user_id', Auth::id())
                ->whereIn('mrs_details_id', $sales->items->pluck('id'))
                ->delete();
            return back()->with('success','Request #:'.$sales->order_number.' has been cancelled.');
        }
        return back()->with('error','Unable to cancel request no:'.$sales->order_number.'.');
    }

    public function reorder(Request $request) {
        $sales = SalesHeader::find($request->order_id);
        $sales->update(["delivery_status" => "Scheduled for Processing", "status" => "SAVED"]);
        
        return back()->with('success','Request #:'.$sales->order_number.' has been reordered.');
    }

    public function updateOrder(Request $request, $id) {
        //dd($request->all());
        $sales = SalesHeader::withOrderNumberLock(function () use ($request, $id) {
            return DB::transaction(function () use ($request, $id) {
                $sales = SalesHeader::whereKey($id)->lockForUpdate()->firstOrFail();

                if ($request->filled('mrs_no')) {
                    $requestedOrderNumber = $request->mrs_no;

                    if (SalesHeader::orderNumberExists($requestedOrderNumber, $id)) {
                        $requestedOrderNumber = SalesHeader::nextOrderNumber(null, $id);
                        $request->merge(['mrs_no' => $requestedOrderNumber]);
                    }

                    $sales->order_number = $requestedOrderNumber;
                } elseif (SalesHeader::orderNumberExists($sales->order_number, $id)) {
                    $sales->order_number = SalesHeader::nextOrderNumber(null, $id);
                }

                $sales->update([
                    'costcode' => $request->costcode,
                    'priority' => $request->priority,
                    'purpose' => $request->justification,
                    'delivery_date' => Carbon::parse($request->delivery_date)->format('Y-m-d'),
                    'budgeted_amount' => $request->budgeted_amount,
                    'section' => $request->section,
                    'requested_by' => $request->requested_by,
                    'other_instruction' => $request->notes,
                ]);

                return $sales;
            }, 5);
        });

        if ($sales->status === "REQUEST ON HOLD (Hold by MCD Planner)") {
            $sales->update([
                'status' => 'REVISED MRS - ' .Carbon::now()->format('Y-m-d h:i:s A')
            ]);
            // Mail::to([
            //     'aobesoro@philsagamining.com',
            //     'mgimproso@philsagamining.com'
            // ])->queue(new RevisedMrsNotification($sales));
        }

        if ($request->hasFile('attachment')) {
            $files = $request->file('attachment'); // Get all uploaded files
            if (is_array($files) && isset($files[0])) {
                $this->upsertAttachedFiles($sales, $sales->id, $files); // Use the first file
            } elseif (!is_array($files)) {
                // Handle single file upload (not an array)
                $this->upsertAttachedFiles($sales, $sales->id, $files);
            }
        }

        if (is_array($request->qty)) {
            foreach ($request->qty as $key => $value) {
                $detail = SalesDetail::find($key);
                if ($detail) {
                    $detail->update([
                        "qty" => floatval($value),
                        "cost_code" => $request->cost_code[$key],
                        "par_to" => $request->par_to[$key],
                        "frequency" => $request->frequency[$key],
                        "purpose" => $request->purpose[$key],
                        "date_needed" => $request->date_needed[$key],
                    ]);
                }
            }
        }

        return back()->with('success','MRS Request has been updated.');
    }

    public function next_order_number(){
        return SalesHeader::nextOrderNumber();
    }

    private function upsertAttachedFiles($mrs, $mrsId, $files)
    {
        $dbPaths = [];

        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }
            $storagePath = 'public/mrs/' . $mrsId;
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            $sanitizedFilename = preg_replace('/[^\w-]/', '_', $originalFilename);
            $filePath = $file->storeAs($storagePath, $sanitizedFilename . '.' . $file->getClientOriginalExtension());
            $dbPaths[] = str_replace('public/', '', $filePath);
        }

        if (!empty($dbPaths)) {
            $existingPaths = $mrs->order_source ? explode('|', $mrs->order_source) : [];
            $updatedPaths = array_merge($existingPaths, $dbPaths);
            $mrs->update(['order_source' => implode('|', $updatedPaths)]);
        }
    }

    public function submitRequest($id, $status)
    {
        $request = $this->submitForApproval($id, $status);

        if ($request) {
            return redirect()->back()->with('success', 'Request has been submitted.');
        }
        else {
            return redirect()->back()->with('error', 'Oops! Something went wrong');
        }
    }
    /*
    this line is brought to you by
    */
    public function orderRequest($id, $status)
    {
        $page = new Page;
        $page->name = 'Order Posted';
        $request = $this->submitForApproval($id, $status);

        if ($request) {
            return view('theme.pages.ecommerce.submitted', compact('page'));
        }
        else {
            return redirect()->back()->with('error', 'Oops! Something went wrong');
        }
    }

    public function submitForApproval($id, $status)
    {
        $product = SalesHeader::find($id);
        $user = auth()->user();
        $data = [
            "type" => config('app.name'),
            "transid" => 'MRS'.$product->order_number,
            "token" => config('app.key'),
            "refno" => $id,
            "sourceapp" => 'IMP-MRS-PA',
            "sourceurl" => route('my-account.order.details', $id),
            "requestor" => str_replace("'", "", $user->name),
            "department" => str_replace("'", "", $user->department->name),
            "email" => str_replace("'", "", $user->email),
            "purpose" => str_replace("'", "", $product->purpose),
            "name" => str_replace("'", "", $user->name),
            "template_id" => config('app.template_id'),
            "locsite" => "",
            "status" => str_replace("'", "", $product->status)
        ];

        define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $result = require(__ROOT__ . '\api\wfs-api.php');

        if ($result) {
            $product->update([
                'status' => 'POSTED',
                'date_posted' => date('Y-m-d H:i:s'),
                //'note_planner' => NULL,
            ]);
            Cart::where('user_id', Auth::id())
            ->whereIn('mrs_details_id', $product->items->pluck('id'))
            ->delete();
            return true;
        }

        return false;

    }

    public function updateRequestApproval(){
        $mrss = SalesHeader::where('status', 'POSTED')
                ->orWhere('status', 'LIKE', '%IN-PROGRESS%')
                ->orWhere('status', 'LIKE', '%ON-HOLD%')
                ->where('user_id', Auth::id())
                ->get();
        $ids = "";
        foreach ($mrss as $mrs) {
            if ($ids == "") {
                $ids = $mrs->id;
            } else {
                $ids = $ids . "," . $mrs->id;
            }
        }

        define('__ROOT2__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));

        // Scope the WFS lookup to MRS transactions only (see approval-status-api.php).
        $transidLike = 'MRS';
        $WFSrequests = require(__ROOT2__ . '\api\approval-status-api.php');
        foreach ($WFSrequests as $WFSrequest) {
            $WFSrequestArr = explode('|', $WFSrequest);
            $ref_req_no = $WFSrequestArr[0];
            $status = $WFSrequestArr[1];
            $approved_at = DateTime::createFromFormat('Y-m-d H:i:s',  $WFSrequestArr[2]);
            $approved_by = $WFSrequestArr[3];
            $transno = $WFSrequestArr[4];
            $updated_by = $WFSrequestArr[5];
            if ($status != "PENDING" && strpos($transno, 'MRS') !== false) {
                $request = SalesHeader::find($ref_req_no);
                if (!$request) {
                    continue;
                }
                $statusText = $status;

                if ($status == "FULLY APPROVED") {
                    $statusText = "FULLY APPROVED (Approved by ".$updated_by.") - WFS";
                } elseif ($status == "IN-PROGRESS") {
                    $statusText = "IN-PROGRESS (Approved by ".$updated_by.") - WFS";
                } elseif ($status == "HOLD") {
                    $statusText = "REQUEST ON-HOLD (Hold by ".$updated_by.") - WFS";
                } elseif ($status == "CANCELLED") {
                    $statusText = "REQUEST CANCELLED (Cancelled by ".$updated_by.") - WFS";
                }

                $request->update([
                    'status' => $statusText,
                ]);
            }
        }
    }

    public function viewDetails($id)
    {
        $page = new Page;
        $page->name = 'Request Details';
        $order = SalesHeader::find($id);

        return view('theme.pages.customer.orders.details', compact('order', 'page'));
    }

    public function approvalStatus(Request $request, $id)
    {
        $product_request = SalesHeader::find($id);
        $product_request->update(['status' => $request->status]);

        if ($product_request) {
            return response()->json(['message' => 'Item has been approved.', 'status' => 1]);
        }
        else {
            return response()->json(['message' => 'Oops! Something went wrong.', 'status' => 0]);
        }
    }

    public function getDetails(Request $request){
        $mrs = SalesHeader::with('items.product')->find($request->mrs);

        if ($mrs) {
            return response()->json([
                'headers' => $mrs,
                'hasPromo' => $mrs->hasPromo(),
                'items' => $mrs->items->map(function($item) {
                    return [
                        'item' => $item,
                        'product' => $item->product,
                    ];
                })
            ], 200);
        } else {
            return response()->json(['error' => 'MRS not found'], 404); 
        }
    }

    public function deleteItem(Request $request){
        $item = SalesDetail::find($request->item_id);

        if ($item) {
            $item->delete();
            return response()->json(['message' => 'Item deleted successfully.'], 200);
        } else {
            return response()->json(['error' => 'Item not found.'], 404);
        }
    }

    public function saveItem(Request $request){
        $product = Product::find($request->product_id);
        
        $mrsDetail = SalesDetail::create([
            'sales_header_id' => $request->mrs_id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_category' => $product->category_id,
            'price' => 0,
            'tax_amount' => 0,
            'promo_id' => 1,
            'promo_description' => '',
            'discount_amount' => 0,
            'gross_amount' => 0,
            'net_amount' => 0,
            'qty' => $request->quantity_item,
            'uom' => $product->uom,
            'cost_code' => $request->cost_code_item,
            'par_to' => $request->par_to_item,
            'date_needed' => $request->date_needed_item,
            'frequency' => $request->frequency_item,
            'purpose' => $request->purpose_item,
            'created_by' => Auth::id()
        ]);
        return response()->json(['message' => 'Item saved successfully.'], 200);
    }

    public function deleteFile(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);
        $sale = SalesHeader::where('order_source', 'like', "%{$request->file_path}%")->first();
        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'File not found in records.']);
        }
        if (Storage::exists('public/' . $request->file_path)) {
            Storage::delete('public/' . $request->file_path);
        }
        $paths = explode('|', $sale->order_source);
        $updatedPaths = array_filter($paths, function ($path) use ($request) {
            return $path !== $request->file_path;
        });
        // Update the database
        $sale->update([
            'order_source' => implode('|', $updatedPaths)
        ]);

        return response()->json(['success' => true]);
    }
}
