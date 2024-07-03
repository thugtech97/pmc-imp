<?php

namespace App\Http\Controllers\Ecommerce;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Ecommerce\{
    Cart, SalesHeader, SalesDetail
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


    public function orders()
    {
        $sales = SalesHeader::with(['issuances', 'items', 'items.issuances'])->where('user_id',Auth::id())->get();
        $page = new Page();
        $page->name = 'MRS - For Purchase (DP, Stock Item)';
        return view('theme.pages.customer.orders',compact('sales','page'));
    }  

    public function cancel_order(Request $request)
    {
        $sales = SalesHeader::find($request->orderid);
        $sales->update(['status' => 'CANCELLED', 'delivery_status' => 'CANCELLED']);

        return back()->with('success','Order #:'.$sales->order_number.' has been cancelled.');
    }

    public function reorder(Request $request) {
        $sales = SalesHeader::find($request->order_id);
        $sales->update(["delivery_status" => "Scheduled for Processing", "status" => "SAVED"]);
        
        return back()->with('success','Order #:'.$sales->order_number.' has been reordered.');
    }

    public function updateOrder(Request $request, $id) {
        $sales = SalesHeader::find($id);
        $sales->update([
            "delivery_type" => $request->delivery_type,
            "delivery_date" => date('Y-m-d', strtotime($request->delivery_date)),
            "customer_delivery_adress" => $request->delivery_address,
            "other_instruction" => $request->notes,
        ]);

        foreach ($request->qty as $key => $value) {
            $detail = SalesDetail::find($key);
            $detail->update(["qty" => floatval($value)]);
        }

        return back()->with('success','Order has been updated.');
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
        /*
        if ($status == "resubmitted") {
            $url = config('workflow.resubmit');
            $post = [
                'status' => $status,
                'refno' => $id
            ];
        }
        else {
            $url = config('workflow.staging');
            $post = [
                'token' => "base64:WJLP4AV3dsQY0jxtZQxkv3uEe1fZ5Yno5nTkdMnwR1A=",
                'refno' => $id,
                'sourceapp' => config('app.name'),
                'sourceurl' => route('my-account.order.details', $id),
                'type' => 'New Order Request',
                'requestor' => auth()->user()->name ?? '',
                'total_amount' => 0,
                'department' => auth()->user()->department->name ?? '',
                'transid' => 'IMP-MRS-' . uniqid(),
                'status' => $status,
                'approval_url' => route('my-account.order.approval', $id),
                'converted_amount' => 0,
                'email' => auth()->user()->email ?? '',
                'currency' => "test",
                'purpose' => "test",
                'name' => "test"
            ];
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $res = curl_exec($ch);
        $response = json_decode($res);

        if ($response) {
            if ($response->status) {
                $product_request = SalesHeader::find($id);
                $product_request->update([
                    'status' => $status == 'resubmitted' ? 'RESUBMITTED' : 'ON REVIEW',
                    'date_posted' => date('Y-m-d H:i:s')
                ]);

                return $response;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }

        curl_close($ch);

        */
        $product = SalesHeader::find($id);
        $user = auth()->user();
        $data = [
            "type" => config('app.name'),
            "transid" => 'IMP-MRS-' . uniqid(),
            "token" => config('app.key'),
            "refno" => $id,
            "sourceapp" => 'IMP-MRS-PA',
            "sourceurl" => route('my-account.order.details', $id),
            "requestor" => $user->name,
            "department" => $user->department->name,
            "email" => $user->email,
            "purpose" => 'TEST PURPOSE - MRS',
            "name" => $user->name,
            "template_id" => config('app.template_id'),
            "locsite" => ""
        ];

        define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $result = require(__ROOT__ . '\api\wfs-api.php');

        if ($result) {
            $product->update([
                'status' => 'POSTED',
                'date_posted' => date('Y-m-d H:i:s')
            ]);
            return true;
        }

        return false;

    }

    public function updateRequestApproval(){
        $mrss = SalesHeader::where('status', 'POSTED')
                   ->orWhere('status', 'IN-PROGRESS')
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
            if ($status != "PENDING") {
                $request = SalesHeader::find($ref_req_no);
                $request->update([
                    'status' => ($status == "FULLY APPROVED") ? "APPROVED" : $status
                ]);
            }
        }
    }

    public function viewDetails($id)
    {
        $page = new Page;
        $page->name = 'Order Details';
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
}
