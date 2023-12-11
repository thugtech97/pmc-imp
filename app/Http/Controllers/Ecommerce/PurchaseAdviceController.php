<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;
use App\Helpers\ListingHelper;


use App\Models\Ecommerce\{
    DeliveryStatus, SalesPayment, SalesHeader, SalesDetail, Product
};

use App\Models\{
    Permission, Page, Issuance, IssuanceItem, Department, ViewLog
};


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
        $sales = $sales->whereIn('status', ['APPROVED'])->where('for_pa', 1)->whereNull('is_pa')->orWhere('is_pa', 0)->orderBy('id','desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.index',compact('sales','filter','searchType','departments'));
    }

    public function view_mrs(Request $request, $id){
        $sales = SalesHeader::where('id',$id)->first();
        $salesPayments = SalesPayment::where('sales_header_id',$id)->get();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id',$id)->get();
        //dd($salesDetails->first()->issuances);
        $totalPayment = SalesPayment::where('sales_header_id',$id)->sum('amount');
        $totalNet = SalesHeader::where('id',$id)->sum('net_amount');
        if($totalNet <= $totalPayment)
        $status = 'PAID';
        else $status = 'UNPAID';

        
        return view('admin.purchasing.view',compact('sales','salesPayments','salesDetails','status'));
    }

    public function create_pa(Request $request, $id){
        $sales = SalesHeader::find($id);
        if(!$sales){
            return back()->with('error','Something went wrong!');
        }
        $sales->update(["is_pa"=>1]);
        return redirect()->route('pa.index')->with('success','Purchase Advice created successfully!');
    }

    public function pa_list(){
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
        $sales = $sales->whereIn('status', ['APPROVED'])->where('for_pa', 1)->where('is_pa', 1)->orderBy('id','desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.manage',compact('sales','filter','searchType','departments'));
    }
}
