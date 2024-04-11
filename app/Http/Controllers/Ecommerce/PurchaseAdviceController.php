<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;
use App\Helpers\ListingHelper;

use App\Models\Ecommerce\{
    DeliveryStatus, SalesPayment, SalesHeader, SalesDetail, Product, InventoryRequest, InventoryRequestItems
};

use App\Models\{
    Permission, Page, Issuance, IssuanceItem, Department, ViewLog, User
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

    public function generate_report(Request $request) 
    {
        $salesHeader  = SalesHeader::with('items.issuances')->where('order_number', $request->orderNumber)->first();
        $salesDetails = SalesDetail::with('issuances.user')->where('sales_header_id', $salesHeader->id)->get();

        $postedDate = optional($salesHeader->created_at)->format('Y-m-d h:i:s A') ?? '';
        
        $inventoryRequestItems = [];

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
            
            if ($items->isEmpty()) 
            {
                $product = Product::find($sale->product_id);
                $user = User::select(
                    'users.name as prepared_by_name',
                    'role.name as prepared_by_designation'
                )
                ->leftJoin('role', 'role.id', 'users.role_id')
                ->find($sale->created_by);
        
                $inventoryRequestItems[] = [
                    'UoM' => $product->uom,
                    'stock_code' => $product->code,
                    'OEM_ID' => $product->oem,
                    'qty_order' => $sale->qty,
                    'item_description' => $product->name,
                    'prepared_by_name' => $user->prepared_by_name,
                    'prepared_by_designation' => $user->prepared_by_designation, 
                    'prepared_by_date' => optional($sale->created_at)->format('Y-m-d h:i:s A') ?? ''
                ];

            } else {
                $inventoryRequestItems = array_merge($inventoryRequestItems, $items->toArray());
            }
        }

        $pdf = \PDF::loadHtml(view('admin.purchasing.components.generate-report', compact('inventoryRequestItems', 'postedDate')));
        $pdf->setPaper("A4", "landscape");
        return $pdf->download('print.pdf');
    }
}
