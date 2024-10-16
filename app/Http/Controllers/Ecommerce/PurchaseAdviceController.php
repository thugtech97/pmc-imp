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
        $sales = $sales->whereIn('status', ['APPROVED (MCD Approver) - PA for Delegation', '(For Purchasing Receival)', 'RECEIVED FOR CANVASS (Purchasing Officer)'])->where('received_by', Auth::id())->where('for_pa', 1)->where('is_pa', 1)->orderBy('id','desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.purchasing.purchaser_index',compact('sales','filter','searchType','departments'));
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
}
