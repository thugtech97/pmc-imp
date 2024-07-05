<?php

namespace App\Http\Controllers\Ecommerce;

use Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ListingHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\{
    Permission, Page, Issuance, IssuanceItem, Department, ViewLog, User, Role
};
use App\Models\Ecommerce\{
    DeliveryStatus, SalesPayment, SalesHeader, SalesDetail, Product
};

class SalesController extends Controller
{
    private $searchFields = ['order_number','response_code','created_at', 'updated_at'];

    public function __construct() {

    }

    public function index()
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

        if($role->name === "MCD Planner"){
            $sales = $sales->whereIn('status', ['APPROVED', 'PARTIAL', 'COMPLETED', 'VERIFIED (MCD Verifier)'])->orderBy('id','desc');
        }

        if($role->name === "MCD Verifier"){
            $sales = $sales->whereIn('status', ['APPROVED (MCD Planner)', 'VERIFIED (MCD Verifier)'])->orderBy('id','desc');
        }

        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.ecommerce.sales.index',compact('sales','filter','searchType','departments','role'));
    }

    public function bank_deposits()
    {
        $payments = SalesPayment::where('payment_type','Bank Deposit')->orderBy('created_at','desc')->paginate(10);

        return view('admin.ecommerce.sales.bank-deposits',compact('payments'));
    }

    public function validate_payment($id, $status)
    {
        $payment = SalesPayment::find($id);
        $payment->update([
            'is_verified' => $status
        ]);

        if ($status == 1)
        {
            $pstatus = 'PAID';
        } else {
            $pstatus = 'UNPAID';
        }

        SalesHeader::find($payment->sales_header_id)->update([
            'delivery_status' => $status == 1 ? 'Scheduled for Processing' : 'CANCELLED',
            'payment_status' => $pstatus
        ]);

        return back()->with('success','Payment has been updated.');
    }

    public function store(Request $request)
    {
        // TODO
    }

    public function destroy($id)
    {
        $sale = SalesHeader::findOrFail($id)->delete();

        return response()->json(['success' => 'Successfully deleted transaction']);
    }

    public function cancel($id)
    {
        $order = SalesHeader::with('items.issuances')->find($id);

        foreach ($order->items as $item) {
            if ($item->issuances->count() > 0) 
            {
                $order->update(['status' => 'partially cancelled']);
                break;
            }
            else {
                $order->update(['status' => 'cancelled']);
            }
        }

        return response()->json(['success' => 'Successfully cancelled transaction']);
    }

    public function markAsComplete($id)
    {
        $order = SalesHeader::find($id)->update(['status' => 'COMPLETED']);

        return back()->with(['success' => 'Successfully completed transaction']);
    }

    public function update(Request $request)
    {
        $save = SalesPayment::create([
            'sales_header_id' => $request->id,
            'payment_type' => $request->payment_type,
            'amount' => $request->amount,
            'status'  => (isset($request->status) ? 'PAID' : 'UNPAID'),
            'payment_date'  => $request->payment_date,
            'receipt_number'  => $request->receipt_number,
            'created_by' => Auth::id()
        ]);

        $sales = SalesHeader::where('id',$request->id)->first();
        $totalPayment = SalesPayment::where('sales_header_id',$request->id)->sum('amount');
        $total = $totalPayment + $request->amount;

        if ($total >= $sales->net_amount)
            $status = 'PAID';
        else $status = 'UNPAID';

        $save = SalesHeader::findOrFail($request->id)->update([
            'payment_status' => $status
        ]);

        return back()->with('success','Successfully updated payment!');
    }

    public function show($id)
    {
        $sales = SalesHeader::with('user')->where('id',$id)->first();
        $salesPayments = SalesPayment::where('sales_header_id',$id)->get();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id',$id)->get();
        $totalPayment = SalesPayment::where('sales_header_id',$id)->sum('amount');
        $totalNet = SalesHeader::where('id',$id)->sum('net_amount');
        
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();

        if ($totalNet <= $totalPayment)
        $status = 'PAID';
        else $status = 'UNPAID';

        ViewLog::create([
            "module" => "mrs",
            "user_id" => auth()->user()->id,
            "viewed_at" => date("Y-m-d H:i:s")
        ]);
        
        return view('admin.ecommerce.sales.view',compact('sales','salesPayments','salesDetails','status', 'role'));
    }

    public function quick_update(Request $request)
    {
        $update = SalesHeader::findOrFail($request->pages)->update([
            'delivery_status' => $request->status
        ]);

        $order = SalesHeader::findOrFail($request->pages);

        return back()->with('success','Successfully updated delivery status!');
    }

    public function delivery_status(Request $request)
    {
        $sales = explode(",", $request->del_id);

        foreach($sales as $sale){
            logger($sale);
            $update = SalesHeader::whereId($sale)->update([
                'delivery_status' => $request->delivery_status
            ]);

            $update_delivery_table = DeliveryStatus::create([
                'order_id' => $sale,
                'user_id' => Auth::id(),
                'status' => $request->delivery_status,
                'remarks' => $request->del_remarks
            ]);

            if ($request->delivery_status == 'Delivered')
            {
                $order = SalesHeader::find($sale);
                $order->update(['payment_status' => 'PAID']);
                SalesPayment::create([
                    'sales_header_id' => $sale,
                    'payment_type' => 'Cash',
                    'amount' => $order->gross_amount,
                    'status' => 'PAID',
                    'payment_date' => today(),
                    'receipt_number' => Str::random(10),
                    'created_by' => Auth::id()
                ]);
            }
        }

        $order = SalesHeader::findOrFail($request->del_id);

        return back()->with('success','Successfully updated delivery status!'); 
    }

    public function view_payment($id)
    {
        $salesPayments = SalesPayment::where('sales_header_id',$id)->get();
        $totalPayment = SalesPayment::where('sales_header_id',$id)->sum('amount');
        $totalNet = SalesHeader::where('id',$id)->sum('net_amount');
        $remainingPayment = $totalNet - $totalPayment;

        return view('admin.ecommerce.sales.payment',compact('salesPayments','totalPayment','totalNet','remainingPayment'));
    }

    public function cancel_product(Request $request)
    {
        return $request;
    }

    public function display_payments(Request $request)
    {
        $input = $request->all();

        $payments = SalesPayment::where('sales_header_id',$request->id)->get();

        return view('admin.ecommerce.sales.added-payments-result',compact('payments'));
    }

    public function display_delivery(Request $request)
    {
        $input = $request->all();

        $delivery = DeliveryStatus::where('order_id',$request->id)->get();

        return view('admin.ecommerce.sales.delivery_history',compact('delivery'));
    }

    public function updateIssuance(Request $request) 
    {
        // TODO
    }

    public function for_pa(Request $request, $id)
    {
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();
        $sales = SalesHeader::find($id);

        if (!$sales) {
            return back()->with('error', 'Something went wrong!');
        }

        if ($role->name === "MCD Verifier") {
            $sales->update(["for_pa" => 1, "status" => "VERIFIED (MCD Verifier)"]);
            return back()->with('success', 'MRS successfully subjected for Purchase Advice!');
        }

        if ($role->name === "MCD Planner") {
            $sales->update(["status" => "APPROVED (MCD Planner)"]);
            return back()->with('success', 'MRS successfully subjected for Verification!');
        }

        return back()->with('error', 'You do not have the required role to perform this action.');
    }

    public function generateReport(Request $request) 
    {
        $sale = SalesHeader::with(['user', 'issuances', 'items', 'items.issuances'])->where('id', $request->id)->first();
        $salesPayments = SalesPayment::where('sales_header_id', $request->id)->get();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id', $request->id)->get();
        $totalPayment = SalesPayment::where('sales_header_id', $request->id)->sum('amount');
        $totalNet = SalesHeader::where('id', $request->id)->sum('net_amount');

        if ($totalNet <= $totalPayment)
        $status = 'PAID';
        else $status = 'UNPAID';


        if (!$sale) {
            abort(404);
        }

        $pdf = \PDF::loadHtml(view('admin.ecommerce.sales.generate-report', compact('sale','salesPayments','salesDetails','status')));
        $pdf->setPaper("A4", "portrait");
        return $pdf->download('print.pdf');
    }  
}