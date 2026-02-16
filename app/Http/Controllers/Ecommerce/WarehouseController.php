<?php

namespace App\Http\Controllers\Ecommerce;

use Exception;
use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Helpers\ListingHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Ecommerce\SalesDetail;
use App\Models\Ecommerce\SalesHeader;
use App\Models\Ecommerce\SalesPayment;
use App\Mail\DeliveryCompletedNotification;
use Illuminate\Support\Facades\Mail;

class WarehouseController extends Controller
{
    private $searchFields = ['order_number','response_code','created_at', 'updated_at'];
    
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
        if(isset($_GET['startdate']) && $_GET['startdate']<>''){
            $sales = $sales->where('created_at','>=',$_GET['startdate']);
        }
        if(isset($_GET['enddate']) && $_GET['enddate']<>''){
            $sales = $sales->where('created_at','<=',$_GET['enddate'].' 23:59:59');
        }
        if(isset($_GET['search']) && $_GET['search']<>''){
            $sales = $sales->where('order_number','like','%'.$_GET['search'].'%');
        }
        if(isset($_GET['customer_filter']) && $_GET['customer_filter']<>''){
            $sales = $sales->where('customer_name','like','%'.$_GET['customer_filter'].'%');
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
                        END IN (" . implode(',', array_map(function($status) { return "'$status'"; }, $statuses)) . ")
                    ");
                });
            });
        }

        $sales = $sales->whereNotNull('received_at')->orderBy('id', 'desc');
        $sales = $sales->paginate(10);

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $departments = Department::all();

        return view('admin.warehouse.index',compact('sales','filter','searchType','departments'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Request $request, $id){
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
        return view('admin.warehouse.view',compact('sales','salesPayments','salesDetails','status', 'role', 'purchasers'));
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request)
    {
        //dd($request->all());
        $header_id = $request->sales_header_id;
        $h = SalesHeader::find($header_id);
        
        DB::beginTransaction();
        try {
            foreach ($h->items as $i) {
                $qty_delivered = $request->input('qty_delivered'.$i->id);
                $i->update(["qty_delivered" => $qty_delivered]);
            }
            
            //$h->update(["response_code" => Auth::id()]);

            DB::commit();

            // Refresh relationship
            $h->load('items', 'user');

            $totalOrdered = $h->totalQtyOrdered();
            $totalDelivered = $h->totalQtyDelivered();

            if ($totalDelivered >= $totalOrdered && $totalOrdered > 0) {

                if ($h->user->email) {
                    Mail::to($h->user->email)
                        ->send(new DeliveryCompletedNotification($h));
                }
            }

            return back()->with("success", "MRS request details updated.");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with("error", "An error occurred: " . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        //
    }
}
