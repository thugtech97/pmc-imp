<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Ecommerce\{
    Cart, SalesHeader, SalesDetail, Product
};

use App\Models\{
    Page, User, Role
};

use Auth;
use DateTime;

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

    public function orders(Request $request)
    {
        $query = SalesHeader::with(['issuances', 'items', 'items.issuances'])
            ->where('user_id', Auth::id());

        if ($request->has('search') && !empty($request->search)) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(10);

        $page = new Page();
        $page->name = 'MRS - For Purchase (DP, Stock Item)';

        return view('theme.pages.customer.orders', compact('sales', 'page'));
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
        $sales = SalesHeader::find($id);
        $sales->update([
            "priority" => $request->priority,
            "purpose" => $request->justification,
            "delivery_date" => date('Y-m-d', strtotime($request->delivery_date)),
            "budgeted_amount" => $request->budgeted_amount,
            "section" => $request->section,
            "requested_by" => $request->requested_by,
            "other_instruction" => $request->notes,
        ]);

        if ($request->hasFile('attachment')) {
            $files = $request->file('attachment'); // Get all uploaded files
            if (is_array($files) && isset($files[0])) {
                $this->upsertAttachedFiles($sales, $sales->id, $files); // Use the first file
            } elseif (!is_array($files)) {
                // Handle single file upload (not an array)
                $this->upsertAttachedFiles($sales, $sales->id, $files);
            }
        }

        foreach ($request->qty as $key => $value) {
            $detail = SalesDetail::find($key);
            $detail->update([
                "qty" => floatval($value),
                "cost_code" => $request->cost_code[$key],
                "par_to" => $request->par_to[$key],
                "cost_code" => $request->cost_code[$key],
                "frequency" => $request->frequency[$key],
                "purpose" => $request->purpose[$key],
                "date_needed" => $request->date_needed[$key],
            ]);
        }

        return back()->with('success','MRS Request has been updated.');
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
                'note_planner' => NULL,
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
