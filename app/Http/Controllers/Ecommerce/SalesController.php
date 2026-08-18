<?php

namespace App\Http\Controllers\Ecommerce;

use Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Constants\ActionQueue;
use App\Helpers\ListingHelper;
use App\Http\Controllers\Controller;
use App\Services\History;
use App\Services\Notifier;
use Illuminate\Support\Facades\Validator;
use App\Models\{
    Permission, Page, Issuance, IssuanceItem, Department, ViewLog, User, Role
};
use App\Models\Ecommerce\{
    DeliveryStatus, SalesPayment, SalesHeader, SalesDetail, Product, PurchaseAdvice, InventoryRequest
};
use App\Constants\Status;

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
        if(isset($_GET['startdate']) && $_GET['startdate']<>''){
            $sales = $sales->where('created_at','>=',$_GET['startdate']);
        }
        if(isset($_GET['enddate']) && $_GET['enddate']<>''){
            $sales = $sales->where('created_at','<=',$_GET['enddate'].' 23:59:59');
        }
        if (isset($_GET['search']) && $_GET['search'] <> '') {
            $search = $_GET['search'];
        
            $sales = $sales->where('order_number', 'like', "%$search%")
                ->orWhereHas('purchaseAdvice', function ($query) use ($search) {
                    $query->where('pa_number', 'like', "%$search%");
                });
        }
        if(isset($_GET['customer_filter']) && $_GET['customer_filter']<>''){
            $sales = $sales->where('customer_name','like','%'.$_GET['customer_filter'].'%');
        }

        if (!empty($_GET['status']) && is_array($_GET['status'])) {
            if ($_GET['status'] === ['HOLD']) {
                $sales = $sales->where('status', 'like', '%HOLD (For MCD Planner re-edit)%');
            } else {
                $statuses = $_GET['status'];
                $sales->where(function ($query) use ($statuses) {
                    $query->whereHas('items', function ($subQuery) use ($statuses) {
                        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                        $subQuery->havingRaw("
                            CASE
                                WHEN SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) = SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'COMPLETED'
                                WHEN SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) > 0 
                                     AND SUM(CASE WHEN promo_id != 1 THEN qty_to_order ELSE 0 END) > SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'PARTIAL'
                                ELSE 'UNSERVED'
                            END IN ($placeholders)
                        ", $statuses);
                    });
                });
            }
        }

        if ($role->name === "MCD Planner") {
            $sales = $sales->where(function ($query) {
                $query->whereIn('status', [
                        'RECEIVED FOR CANVASS (Purchasing Officer)',
                        'APPROVED (MCD Planner) - MRS For Verification',
                        'HOLD (For MCD Planner re-edit)',
                        'Verified (MCD Verifier) - PA For MCD Manager Approval',
                        'APPROVED (MCD Approver) - PA for Delegation',
                        '(For Purchasing Receival)'
                    ])
                    ->orWhere('status', 'LIKE', '%FULLY APPROVED%')
                    ->orWhere('status', 'LIKE', '%REVISED MRS%');
            });
        }

        if ($role->name === "MCD Verifier") {
            $sales = $sales->whereIn('status', [
                    'APPROVED (MCD Planner) - MRS For Verification',
                    'Verified (MCD Verifier) - PA For MCD Manager Approval',
                ]);
        }

        if ($role->name === "MCD Approver") {
            $sales = $sales->whereIn('status', [
                    'Verified (MCD Verifier) - PA For MCD Manager Approval',
                    'APPROVED (MCD Approver) - PA for Delegation',
                ]);
        }

        // Whatever is on this role's desk goes to the top of page 1, in the order
        // App\Constants\ActionQueue lists it — the same list behind the sidebar
        // badge and the NEEDS YOUR ACTION flag on the row.
        $actionOrder = ActionQueue::orderCase(ActionQueue::MRS, $role->name);
        if ($actionOrder) {
            $sales = $sales->orderByRaw($actionOrder)->orderBy('id', 'desc');
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

        Notifier::toUser($order->user_id, [
            'title'   => 'MRS Cancelled',
            'message' => "Your MRS #{$order->order_number} has been cancelled.",
            'url'     => route('profile.sales.view', $order->id),
            'module'  => 'MRS',
            'status'  => $order->status,
        ]);

        return response()->json(['success' => 'Successfully cancelled transaction']);
    }

    public function markAsComplete($id)
    {
        $order = SalesHeader::find($id);
        $order->update(['status' => 'COMPLETED']);

        Notifier::toUser($order->user_id, [
            'title'   => 'MRS Completed',
            'message' => "Your MRS #{$order->order_number} has been completed.",
            'url'     => route('profile.sales.view', $order->id),
            'module'  => 'MRS',
            'status'  => 'COMPLETED',
        ]);

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
    /*
    this line is brought to you by
    */
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
        

        //START RAEVIN UPDATE
        $data = [
            "token" => config('app.key'),
            "transid" => 'MRS'.$sales->order_number
        ];

        // Guarded: a bare define() re-notices if this action runs twice in one PHP process.
        if (!defined('__ROOT__')) {
            define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        }
        $approvers = require(__ROOT__ . '\api\wfs-approvers-api.php');
        //END RAEVIN UPDATE

        return view('admin.ecommerce.sales.view',compact('sales','salesPayments','salesDetails','status', 'role', 'approvers'));
    }

    public function quick_update(Request $request)
    {
        $update = SalesHeader::findOrFail($request->pages)->update([
            'delivery_status' => $request->status
        ]);

        $order = SalesHeader::findOrFail($request->pages);

        Notifier::toUser($order->user_id, [
            'title'   => 'Delivery Status Updated',
            'message' => "The delivery status of your MRS #{$order->order_number} is now: {$request->status}.",
            'url'     => route('profile.sales.view', $order->id),
            'module'  => 'MRS',
            'status'  => $request->status,
        ]);

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

            // Keep the requestor informed of the delivery status change.
            $deliveryOrder = SalesHeader::find($sale);
            if ($deliveryOrder) {
                Notifier::toUser($deliveryOrder->user_id, [
                    'title'   => 'Delivery Status Updated',
                    'message' => "The delivery status of your MRS #{$deliveryOrder->order_number} is now: {$request->delivery_status}." . ($request->del_remarks ? " Remarks: {$request->del_remarks}" : ''),
                    'url'     => route('profile.sales.view', $deliveryOrder->id),
                    'module'  => 'MRS',
                    'status'  => $request->delivery_status,
                ]);
            }

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
        //dd($request->all());
        $header_id = $request->sales_header_id;
        $h = SalesHeader::find($header_id);

        $statusUpper = strtoupper((string) $h->status);

        // A held MRS being re-edited by the planner counts as a revision (Rev1, Rev2, ...).
        // A "REVISED MRS" was already counted by the department user, so it is not re-bumped.
        $mrsWasHeld = strpos($statusUpper, 'HOLD') !== false;

        // Statuses that mean "this is sitting in the planner's queue waiting to be re-edited":
        // held by the canvasser, bounced by the planner to the department user, or revised by
        // that department user and sent back. Deliberately excludes '(For Purchasing Receival)',
        // which also has a received_by but is moving forward, not back.
        $inPlannerReeditQueue = $h->status === 'HOLD (For MCD Planner re-edit)'
            || $h->status === 'REQUEST ON HOLD (Hold by MCD Planner)'
            || strpos($statusUpper, 'REVISED MRS') === 0;

        // Still carrying a received_by means a canvasser is waiting for it — received_by is only
        // ever set at assignment, and the verifier/approver holds clear it. So after the planner
        // re-edits, send it STRAIGHT back to that canvasser: skip verify/approve/re-assignment.
        // This holds across the longer detour too (canvasser hold -> planner holds to the
        // department user -> department user revises -> planner saves).
        $isPurchaserReturn = $inPlannerReeditQueue
            && !$h->received_at
            && $h->received_by;

        DB::beginTransaction();
        try {
            foreach ($h->items as $i) {
                $qty_to_order = $request->input('quantityToOrder'.$i->id);
                $previous_mrs = $request->input('previous_no'.$i->id);
                $open_po = $request->input('open_po'.$i->id);
                $is_hold = $request->input('is_hold'.$i->id);
                $hold_desc = $request->input('hold_desc'.$i->id);
                $qty_delivered = $request->input('qty_delivered'.$i->id);
                $i->update([
                    "promo_id" => $is_hold,
                    "promo_description" => $hold_desc,
                    "qty_to_order" => $qty_to_order, 
                    "previous_mrs" => $previous_mrs, 
                    "open_po" => $open_po,
                    "qty_delivered" => $qty_delivered
                ]);
            }
            $pa = PurchaseAdvice::where("mrs_id", $header_id)->first();
            if(empty($pa)){
                $pa_number = $this->next_pa_number();
                $pa = PurchaseAdvice::create([
                    "pa_number" => $pa_number,
                    "mrs_id" => $header_id
                ]);
            }

            if ($h->received_at) {
                $newStatus = "RECEIVED FOR CANVASS (Purchasing Officer)";   // purchaser editing an already-received MRS
            } elseif ($isPurchaserReturn) {
                $newStatus = "(For Purchasing Receival)";                   // bypass straight back to the canvasser
            } else {
                $newStatus = "APPROVED (MCD Planner) - MRS For Verification";
            }
            // Verify/approve stamps are kept when the MRS is already received, and when it goes
            // straight back to the canvasser (those stages are bypassed, not redone). Only a
            // normal re-entry into the verification queue clears them.
            $keepReviewStamps = $h->received_at || $isPurchaserReturn;

            if ($h->received_at) {
                $plannerTitle = 'Canvass details updated by the Purchasing Officer';
                $plannerReq   = 'Canvass details updated';
            } elseif ($isPurchaserReturn) {
                $plannerTitle = 'Re-edited by the MCD Planner and sent straight back to the canvasser';
                $plannerReq   = 'REVISED BY MCD PLANNER - BACK WITH THE CANVASSER';
            } else {
                $plannerTitle = 'Purchase advice issued by the MCD Planner and sent for verification';
                $plannerReq   = 'FOR MCD VERIFICATION';
            }

            History::context($h, [
                'action'          => $mrsWasHeld ? 'revised' : 'status',
                'title'           => $plannerTitle,
                'requestor_title' => $plannerReq,
                'remarks'         => $request->planner_remarks,
            ]);

            $h->update([
                "status" => $newStatus,
                "adjusted_amount" => $h->received_at ? $h->adjusted_amount : $request->adjusted_amount,
                "for_pa" => 1,
                "is_pa" => 1,
                "planner_by" => $h->received_at ? $h->planner_by : auth()->user()->id,
                "planner_at" => $h->received_at ? $h->planner_at : Carbon::now(),
                "planner_remarks" => /*$h->received_at ? $h->planner_remarks :*/ $request->planner_remarks,
                // Sending (back) for verification: clear stale verify/approve stamps so the
                // MCD Verifier can verify again.
                "verified_at" => $keepReviewStamps ? $h->verified_at : NULL,
                "approved_at" => $keepReviewStamps ? $h->approved_at : NULL,
                // Revision bump when a held MRS is re-edited.
                "revision" => $mrsWasHeld ? (int) $h->revision + 1 : $h->revision,
                "revised_at" => $mrsWasHeld ? now() : $h->revised_at,
            ]);

            // Lift the hold on the PA once the planner has re-edited it, and count it as a
            // revision. Only touched when the PA is actually held, so the normal planner
            // PROCEED / UPDATE path leaves the PA record alone as before.
            $paWasHeld = $pa && ((int) $pa->is_hold === 1
                || strpos(strtoupper((string) $pa->status), 'HOLD') !== false);

            if ($paWasHeld) {
                $paUpdate = [
                    "status"     => $newStatus,
                    "is_hold"    => 0,
                    "revision"   => (int) $pa->revision + 1,
                    "revised_at" => now(),
                ];
                if ($isPurchaserReturn) {
                    $paUpdate["received_by"] = $h->received_by;
                    $paUpdate["received_at"] = NULL;
                }
                History::context($pa, [
                    'action'          => 'revised',
                    'title'           => 'Hold lifted — re-edited by the MCD Planner',
                    'requestor_title' => 'Purchase advice revised by the MCD Planner',
                    'remarks'         => $request->planner_remarks,
                ]);
                $pa->update($paUpdate);
            }

            if ($isPurchaserReturn) {
                // Straight back to the canvasser who returned it — no verifier/approver hop.
                Notifier::toUser($h->received_by, [
                    'title'   => 'MRS Re-edited and Returned to You',
                    'message' => "MRS #{$h->order_number} was re-edited by the MCD Planner and is back with you for receival and canvass.",
                    'url'     => route('purchaser.index'),
                    'module'  => 'MRS',
                    'status'  => '(For Purchasing Receival)',
                ]);
                Notifier::toUser($h->user_id, [
                    'title'   => 'MRS Re-edited by Planner',
                    'message' => "Your MRS #{$h->order_number} was re-edited by the MCD Planner and sent back to the canvasser for canvass.",
                    'url'     => route('profile.sales.view', $h->id),
                    'module'  => 'MRS',
                    'status'  => '(For Purchasing Receival)',
                ]);
            } elseif (!$h->received_at) {
                // Planner issued the PA — hand off to the MCD Verifier and keep the requestor informed.
                Notifier::toRoleName('MCD Verifier', [
                    'title'   => 'MRS for Verification',
                    'message' => "MRS #{$h->order_number} was approved by the MCD Planner and awaits your verification.",
                    'url'     => route('sales-transaction.view', $h->id),
                    'module'  => 'MRS',
                    'status'  => 'APPROVED (MCD Planner) - MRS For Verification',
                ]);
                Notifier::toUser($h->user_id, [
                    'title'   => 'MRS Approved by Planner',
                    'message' => "Your MRS #{$h->order_number} was approved by the MCD Planner and is now for verification.",
                    'url'     => route('profile.sales.view', $h->id),
                    'module'  => 'MRS',
                    'status'  => 'APPROVED (MCD Planner) - MRS For Verification',
                ]);
            }

            DB::commit();

            // Buffered per-line diffs from the loop above — write them now so the
            // trail is complete before the planner is redirected back to the MRS.
            History::flushItemChanges();

            return back()->with("success", "MRS adjustments now updated. Purchase advice now generated.");
        } catch (\Exception $e) {
            DB::rollBack();
            // The buffered item diffs describe edits that never landed.
            History::discardItemChanges();
            return back()->with("error", "An error occurred while updating the issuance: " . $e->getMessage());
        }
    }

    public function next_pa_number()
    {
        $last_order = PurchaseAdvice::whereNotNull('mrs_id')
            ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('created_at', 'desc')
            ->first();
        $firstName = auth()->user()->firstname;
        $lastName = auth()->user()->lastname;
        $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

        if (empty($last_order)) {
            $next_number = $initials . "-" . date('y') . "0001";
        } else {
            $order_number = substr($last_order->pa_number, -4);
            if (!isset($order_number)) {
                $next_number = $initials . "-" . date('y') . "0001";
            } else {
                $next_number = $initials . "-" . date('y') . str_pad(((int)$order_number + 1), 4, '0', STR_PAD_LEFT);
            }
        }
        return $next_number;
    }

    public function mrs_action(Request $request, $id){
        try{
            $mrs = SalesHeader::find($id);
            $note = $request->query('note', '');
            $requestorUrl = route('profile.sales.view', $mrs->id);
            $adminUrl = route('sales-transaction.view', $mrs->id);

            if ($request->action == "verify") {
                History::context($mrs, [
                    'action'          => 'verified',
                    'title'           => 'Verified by the MCD Verifier',
                    'requestor_title' => 'FOR MCD MANAGER APPROVAL',
                    'remarks'         => $note,
                ]);
                $mrs->update(["status" => "Verified (MCD Verifier) - PA For MCD Manager Approval", "verified_at" => Carbon::now()]);
                Notifier::toRoleName('MCD Approver', [
                    'title'   => 'MRS for Approval',
                    'message' => "MRS #{$mrs->order_number} was verified by the MCD Verifier and awaits your approval.",
                    'url'     => $adminUrl,
                    'module'  => 'MRS',
                    'status'  => "Verified (MCD Verifier) - PA For MCD Manager Approval",
                ]);
                Notifier::toUser($mrs->user_id, [
                    'title'   => 'MRS Verified',
                    'message' => "Your MRS #{$mrs->order_number} was verified by the MCD Verifier and is now for approval.",
                    'url'     => $requestorUrl,
                    'module'  => 'MRS',
                    'status'  => "Verified (MCD Verifier) - PA For MCD Manager Approval",
                ]);
                return redirect()->route('sales-transaction.index')->with('success', 'MRS request verified');
            }
            if ($request->action == "hold") {
                // Returned to planner: clear the receipt so aging stops until it is re-received.
                History::context($mrs, [
                    'action'          => 'held',
                    'title'           => 'Held by the MCD Verifier for Planner re-edit',
                    'requestor_title' => 'ON HOLD - WITH MCD PLANNER FOR RE-EDIT',
                    'remarks'         => $note,
                ]);
                $mrs->update(["status" => "HOLD (For MCD Planner re-edit)", "note_verifier" => $note, "hold_by" => Auth::id(), "received_at" => NULL, "received_by" => NULL]);
                Notifier::toRoleName('MCD Planner', [
                    'title'   => 'MRS Returned by Verifier',
                    'message' => "MRS #{$mrs->order_number} was held by the MCD Verifier for re-edit." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => $adminUrl,
                    'module'  => 'MRS',
                    'status'  => "HOLD (For MCD Planner re-edit)",
                ]);
                Notifier::toUser($mrs->user_id, [
                    'title'   => 'MRS On Hold',
                    'message' => "Your MRS #{$mrs->order_number} was held by the MCD Verifier and returned to the MCD Planner for re-edit." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => $requestorUrl,
                    'module'  => 'MRS',
                    'status'  => "HOLD (For MCD Planner re-edit)",
                ]);
                return redirect()->route('sales-transaction.index')->with('success', 'MRS request on-hold');
            }
            if ($request->action == "hold-planner") {
                History::context($mrs, [
                    'action'          => 'returned',
                    'title'           => 'Returned to the requestor by the MCD Planner',
                    'requestor_title' => 'RETURNED TO YOU FOR REVISION - MCD PLANNER',
                    'remarks'         => $note,
                ]);
                $mrs->update(["status" => "REQUEST ON HOLD (Hold by MCD Planner)", "note_planner" => $note]); /** Noted **/
                Notifier::toUser($mrs->user_id, [
                    'title'   => 'MRS On Hold',
                    'message' => "Your MRS #{$mrs->order_number} was placed on hold by the MCD Planner." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => $requestorUrl,
                    'module'  => 'MRS',
                    'status'  => "REQUEST ON HOLD (Hold by MCD Planner)",
                ]);
                return redirect()->route('sales-transaction.index')->with('success', 'MRS request on-hold');
            }
            if ($request->action == "approve-approver") {
                History::context($mrs, [
                    'action'          => 'approved',
                    'title'           => 'Approved by the MCD Approver',
                    'requestor_title' => 'APPROVED BY MCD MANAGER - FOR CANVASSER ASSIGNMENT',
                    'remarks'         => $note,
                ]);
                $mrs->update([
                    "status" => "APPROVED (MCD Approver) - PA for Delegation",
                    "note_myrna" => $note,
                    "approved_at" => Carbon::now() ]);
                Notifier::toUser($mrs->user_id, [
                    'title'   => 'MRS Approved',
                    'message' => "Your MRS #{$mrs->order_number} was approved by the MCD Approver.",
                    'url'     => $requestorUrl,
                    'module'  => 'MRS',
                    'status'  => "APPROVED (MCD Approver) - PA for Delegation",
                ]);
                return redirect()->route('sales-transaction.index')->with('success', 'MRS request approved');
            }
            if ($request->action == "hold-approver") {
                // Returned to planner: clear the receipt so aging stops until it is re-received.
                History::context($mrs, [
                    'action'          => 'held',
                    'title'           => 'Held by the MCD Approver for Planner re-edit',
                    'requestor_title' => 'ON HOLD - WITH MCD PLANNER FOR RE-EDIT',
                    'remarks'         => $note,
                ]);
                $mrs->update(["status" => "HOLD (For MCD Planner re-edit)", "note_myrna" => $note, "hold_by" => Auth::id(), "verified_at" => NULL, "received_at" => NULL, "received_by" => NULL]);
                Notifier::toRoleName('MCD Planner', [
                    'title'   => 'MRS Returned by Approver',
                    'message' => "MRS #{$mrs->order_number} was held by the MCD Approver for re-edit." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => $adminUrl,
                    'module'  => 'MRS',
                    'status'  => "HOLD (For MCD Planner re-edit)",
                ]);
                Notifier::toUser($mrs->user_id, [
                    'title'   => 'MRS On Hold',
                    'message' => "Your MRS #{$mrs->order_number} was held by the MCD Approver and returned to the MCD Planner for re-edit." . ($note ? " Remarks: {$note}" : ''),
                    'url'     => $requestorUrl,
                    'module'  => 'MRS',
                    'status'  => "HOLD (For MCD Planner re-edit)",
                ]);
                return redirect()->route('sales-transaction.index')->with('success', 'MRS request on-hold');
            }

            if ($request->action == "mrs-assign") {
                // $note holds the assigned purchaser's user id.
                $assignedName = optional(User::find($note))->name;
                History::context($mrs, [
                    'action'          => 'assigned',
                    'title'           => 'Delegated to canvasser' . ($assignedName ? ' ' . $assignedName : ''),
                    'requestor_title' => 'ASSIGNED TO CANVASSER' . ($assignedName ? ' (' . strtoupper($assignedName) . ')' : '') . ' - FOR RECEIVING',
                ]);
                $mrs->update(["received_by" => $note, "status" => "(For Purchasing Receival)", "received_at" => null]);
                Notifier::toUser($note, [
                    'title'   => 'MRS Assigned to You',
                    'message' => "MRS #{$mrs->order_number} has been assigned to you for purchasing receival.",
                    // The canvasser's own queue — pa.index is the Purchasing Officer's
                    // delegation list and shows every canvasser's PA.
                    'url'     => route('purchaser.index'),
                    'module'  => 'MRS',
                    'status'  => "(For Purchasing Receival)",
                ]);
                // Tell the requestor a purchaser/canvasser has been assigned.
                Notifier::toUser($mrs->user_id, [
                    'title'   => 'Canvasser Assigned',
                    'message' => "Your MRS #{$mrs->order_number} has been assigned to " . ($mrs->purchaser->name ?? 'a purchaser') . " for purchasing.",
                    'url'     => $requestorUrl,
                    'module'  => 'MRS',
                    'status'  => "(For Purchasing Receival)",
                ]);
                return redirect()->route('pa.index')->with('success', '<b>MRS#'.$mrs->order_number.'</b> successfully assigned to <b>'.$mrs->purchaser->name.'</b>.');
            }
            if($request->action == "purchaser-receive"){
                History::context($mrs, [
                    'action'          => 'received',
                    'title'           => 'Received for canvass',
                    'requestor_title' => 'RECEIVED FOR CANVASS',
                ]);
                $mrs->update(["status" => "RECEIVED FOR CANVASS (Purchasing Officer)", "received_at" => Carbon::now()]);
                // Mirror onto the PA so both modules agree on who holds it and from when.
                if ($mrs->purchaseAdvice) {
                    History::context($mrs->purchaseAdvice, [
                        'action'          => 'received',
                        'title'           => 'Received for canvass',
                        'requestor_title' => 'Purchase advice received for canvass',
                    ]);
                }
                optional($mrs->purchaseAdvice)->update([
                    "status" => "RECEIVED FOR CANVASS (Purchasing Officer)",
                    "received_by" => $mrs->received_by,
                    "received_at" => Carbon::now(),
                ]);
                Notifier::toUser($mrs->user_id, [
                    'title'   => 'MRS Received for Canvass',
                    'message' => "Your MRS #{$mrs->order_number} has been received by the Purchasing Officer for canvass.",
                    'url'     => $requestorUrl,
                    'module'  => 'MRS',
                    'status'  => "RECEIVED FOR CANVASS (Purchasing Officer)",
                ]);
                return back()->with('success', 'MRS received by '.$mrs->purchaser->name.' (Purchaser)');
            }
        }catch(\Exception $e){
            return back()->with("error", "An error occurred: " . $e->getMessage());
        }
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
            History::context($sales, [
                'action'          => 'verified',
                'title'           => 'Verified by the MCD Verifier and subjected to Purchase Advice',
                'requestor_title' => 'FOR MCD MANAGER APPROVAL',
            ]);
            $sales->update(["for_pa" => 1, "status" => "Verified (MCD Verifier) - PA For MCD Manager Approval"]);
            Notifier::toRoleName('MCD Approver', [
                'title'   => 'MRS for Approval',
                'message' => "MRS #{$sales->order_number} was verified by the MCD Verifier and awaits your approval.",
                'url'     => route('sales-transaction.view', $sales->id),
                'module'  => 'MRS',
                'status'  => "Verified (MCD Verifier) - PA For MCD Manager Approval",
            ]);
            Notifier::toUser($sales->user_id, [
                'title'   => 'MRS Verified',
                'message' => "Your MRS #{$sales->order_number} was verified by the MCD Verifier and is now for approval.",
                'url'     => route('profile.sales.view', $sales->id),
                'module'  => 'MRS',
                'status'  => "Verified (MCD Verifier) - PA For MCD Manager Approval",
            ]);
            return back()->with('success', 'MRS successfully subjected for Purchase Advice!');
        }

        if ($role->name === "MCD Planner") {
            History::context($sales, [
                'action'          => 'status',
                'title'           => 'Endorsed by the MCD Planner for verification',
                'requestor_title' => 'FOR MCD VERIFICATION',
            ]);
            $sales->update(["status" => "APPROVED (MCD Planner) - MRS For Verification", "planner_by" => auth()->user()->name, "planner_at" => Carbon::now()]);
            Notifier::toRoleName('MCD Verifier', [
                'title'   => 'MRS for Verification',
                'message' => "MRS #{$sales->order_number} was approved by the MCD Planner and awaits your verification.",
                'url'     => route('sales-transaction.view', $sales->id),
                'module'  => 'MRS',
                'status'  => "APPROVED (MCD Planner) - MRS For Verification",
            ]);
            Notifier::toUser($sales->user_id, [
                'title'   => 'MRS Approved by Planner',
                'message' => "Your MRS #{$sales->order_number} was approved by the MCD Planner and is now for verification.",
                'url'     => route('profile.sales.view', $sales->id),
                'module'  => 'MRS',
                'status'  => "APPROVED (MCD Planner) - MRS For Verification",
            ]);
            return back()->with('success', 'MRS successfully subjected for Verification!');
        }

        return back()->with('error', 'You do not have the required role to perform this action.');
    }

    public function hold_item(Request $request){
        $product = SalesDetail::find($request->id);
        if(!$product){
            return response()->json(["message" => "Not found."], 404);
        }
        $product->update($request->all());
        return response()->json(["message" => "Product status updated"], 200);
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
        $pdf->setPaper("A4", "landscape");
        $revSuffix = $sale->revision > 0 ? '-Rev'.$sale->revision : '';
        return $pdf->download('MRS-'.$sale->order_number.$revSuffix.'.pdf');
    }

    public function pa_aging(Request $request)
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
        $sales = SalesHeader::with('items.issuances')
            ->withSum('issuances', 'qty')
            ->where('id', '>', '0');

        if (!empty($_GET['startdate'])) {
            $sales->where('created_at', '>=', $_GET['startdate']);
        }
        if (!empty($_GET['enddate'])) {
            $sales->where('created_at', '<=', $_GET['enddate'] . ' 23:59:59');
        }
        if (!empty($_GET['search'])) {
            $search = $_GET['search'];
            $sales->where(function ($query) use ($search) {
                $query->where('order_number', 'like', "%$search%")
                    ->orWhereHas('purchaseAdvice', function ($subQuery) use ($search) {
                        $subQuery->where('pa_number', 'like', "%$search%");
                    });
            });
        }
        if (!empty($_GET['customer_filter'])) {
            $sales->where('customer_name', 'like', '%' . $_GET['customer_filter'] . '%');
        }
        $sales = $sales->get();
        $sales = $sales->filter(function ($sale) {
            return $sale->balance_pa() > 0 && !is_null($sale->received_at);
        });        

        $currentPage = request()->get('page', 1);
        $perPage = 10;
        $sales = new LengthAwarePaginator(
            $sales->forPage($currentPage, $perPage),
            $sales->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';
        $departments = Department::all();

        return view('admin.ecommerce.sales.pa-aging', compact('sales', 'filter', 'searchType', 'departments', 'role'));
    }

    /**
     * PA-DP and PA-SR live in the same table and are told apart exactly the way
     * History::paType() and the PA listing tabs do it: a PA carrying a numbered
     * MRS is a DP, anything else is an SR.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type  'DP' | 'SR'
     * @return void
     */
    private function scopePaType($query, $type)
    {
        if ($type === 'DP') {
            $query->whereHas('mrs', function ($m) {
                $m->whereNotNull('order_number')->where('order_number', '!=', '');
            });
            return;
        }

        $query->where(function ($q) {
            $q->whereNull('mrs_id')
                ->orWhereDoesntHave('mrs')
                ->orWhereHas('mrs', function ($m) {
                    $m->whereNull('order_number')->orWhere('order_number', '');
                });
        });
    }

    /**
     * Every module the dashboard covers, and the model each one counts.
     *
     * @return array
     */
    private function dashboardModules()
    {
        return [
            'MRS'   => ['label' => 'MRS',   'model' => SalesHeader::class],
            'IMF'   => ['label' => 'IMF',   'model' => InventoryRequest::class],
            'PA-DP' => ['label' => 'PA-DP', 'model' => PurchaseAdvice::class],
            'PA-SR' => ['label' => 'PA-SR', 'model' => PurchaseAdvice::class],
        ];
    }

    /**
     * The dashboard's drill-down segments — the one definition of what each tile
     * means. Both the tile count and the modal behind it read from here, so a
     * tile can never show one number and then list a different set of records.
     *
     * Predicates follow the same status vocabulary the rest of the system uses:
     * SalesHeader::requestorStatusPartsFor() for MRS, App\Constants\Status for
     * IMF, and the PA status strings PurchaseAdviceController writes.
     *
     * @param  string  $module  'MRS' | 'IMF' | 'PA-DP' | 'PA-SR'
     * @return array
     */
    private function dashboardSegments($module = 'MRS')
    {
        if ($module === 'IMF') {
            return $this->imfSegments();
        }

        if ($module === 'PA-DP' || $module === 'PA-SR') {
            return $this->paSegments($module === 'PA-DP' ? 'DP' : 'SR');
        }

        $noop = function ($q) { };

        return [
            'total' => ['label' => 'All MRS', 'apply' => $noop],

            'draft' => ['label' => 'Saved drafts (not yet submitted)', 'apply' => function ($q) {
                $q->where('status', 'SAVED');
            }],

            'wfs' => ['label' => 'With WFS for approval', 'apply' => function ($q) {
                $q->where(function ($w) {
                    $w->where('status', 'POSTED')->orWhere('status', 'like', '%IN-PROGRESS%');
                });
            }],

            'mcd_pipeline' => ['label' => 'In the MCD pipeline', 'apply' => function ($q) {
                $q->where(function ($w) {
                    $w->where('status', 'like', '%FULLY APPROVED%')
                        ->orWhere('status', 'like', '%REVISED MRS%')
                        ->orWhere('status', 'like', '%MRS For Verification%')
                        ->orWhere('status', 'like', 'Verified (MCD Verifier)%');
                });
            }],

            'for_delegation' => ['label' => 'Approved — awaiting canvasser assignment', 'apply' => function ($q) {
                $q->where('status', 'like', 'APPROVED (MCD Approver)%');
            }],

            'for_receiving' => ['label' => 'Assigned — waiting for the canvasser to receive', 'apply' => function ($q) {
                $q->where('status', '(For Purchasing Receival)');
            }],

            'in_canvass' => ['label' => 'Received for canvass', 'apply' => function ($q) {
                $q->where('status', 'like', 'RECEIVED FOR CANVASS%');
            }],

            'on_hold' => ['label' => 'On hold', 'apply' => function ($q) {
                $q->where(function ($w) {
                    $w->where('status', 'HOLD (For MCD Planner re-edit)')
                        ->orWhere('status', 'like', '%ON-HOLD%');
                });
            }],

            'returned' => ['label' => 'Returned to the requestor for revision', 'apply' => function ($q) {
                $q->where('status', 'REQUEST ON HOLD (Hold by MCD Planner)');
            }],

            'cancelled' => ['label' => 'Cancelled', 'apply' => function ($q) {
                $q->where('status', 'like', '%CANCELLED%');
            }],

            // Sat with an assigned canvasser for more than 2 days without being
            // received. This is the real stall in the flow — the old "overdue"
            // tile measured WFS IN-PROGRESS age, a status only a handful of MRS
            // ever carry, so it read ~0 no matter how backed up the queue was.
            'overdue_receiving' => ['label' => 'Assigned over 2 days ago, still not received', 'apply' => function ($q) {
                $q->where('status', '(For Purchasing Receival)')
                    ->where('approved_at', '<=', now()->subDays(2));
            }],
        ];
    }

    /**
     * IMF segments, keyed off App\Constants\Status so they follow the
     * Planner -> Verifier -> Planner -> Planning Supervisor flow.
     *
     * @return array
     */
    private function imfSegments()
    {
        return [
            'total' => ['label' => 'All IMF', 'apply' => function ($q) { }],

            'draft' => ['label' => 'Saved drafts (not yet submitted)', 'apply' => function ($q) {
                $q->where('status', Status::SAVED);
            }],

            'wfs' => ['label' => 'Submitted — with WFS', 'apply' => function ($q) {
                $q->where('status', Status::SUBMITTED);
            }],

            'with_planner' => ['label' => 'With the MCD Planner', 'apply' => function ($q) {
                $q->whereIn('status', Status::imfPlannerStages());
            }],

            'with_verifier' => ['label' => 'With the MCD Verifier', 'apply' => function ($q) {
                $q->where('status', Status::FOR_VERIFICATION);
            }],

            'with_supervisor' => ['label' => 'With the Planning Supervisor', 'apply' => function ($q) {
                $q->where('status', Status::APPROVED_MCD);
            }],

            'approved' => ['label' => 'Fully approved', 'apply' => function ($q) {
                $q->whereIn('status', Status::imfFinalApproved());
            }],

            'on_hold' => ['label' => 'On hold (returned for re-edit)', 'apply' => function ($q) {
                $q->where('status', 'like', 'HOLD%');
            }],

            'rejected' => ['label' => 'Rejected', 'apply' => function ($q) {
                $q->where('status', 'like', 'REJECTED%');
            }],

            'cancelled' => ['label' => 'Cancelled', 'apply' => function ($q) {
                $q->where('status', 'like', '%CANCELLED%');
            }],
        ];
    }

    /**
     * PA segments, shared by PA-DP and PA-SR — the two differ only by which
     * records they are scoped to, not by the stages they pass through.
     *
     * @param  string  $paType  'DP' | 'SR'
     * @return array
     */
    private function paSegments($paType)
    {
        $isDp = $paType === 'DP';

        // A PA-DP carries no status of its own — purchase_advice.status is NULL
        // for every one of them and the parent MRS holds the real one. That is
        // why the PA listing prints the MRS status for these rows, and why
        // these segments have to read the status where it actually lives.
        // The two sides also spell the same stage differently, hence one
        // pattern per side rather than a shared one.
        $stages = [
            'for_planner' => [
                'label' => 'On hold — with the MCD Planner for re-edit',
                'dp'    => ['=', 'HOLD (For MCD Planner re-edit)'],
                'sr'    => ['=', 'HOLD (For MCD Planner re-edit)'],
            ],
            'for_verifier' => [
                'label' => 'With the MCD Verifier',
                'dp'    => ['=', 'APPROVED (MCD Planner) - MRS For Verification'],
                'sr'    => ['=', 'APPROVED (MCD PLANNER) - FOR VERIFICATION'],
            ],
            'for_approver' => [
                'label' => 'With the MCD Approver',
                'dp'    => ['=', 'Verified (MCD Verifier) - PA For MCD Manager Approval'],
                'sr'    => ['=', 'VERIFIED (MCD Verifier) - PA For MCD Manager APPROVAL'],
            ],
            'for_delegation' => [
                'label' => 'Approved — awaiting canvasser assignment',
                'dp'    => ['like', 'APPROVED (MCD Approver)%'],
                'sr'    => ['like', 'APPROVED (MCD Approver)%'],
            ],
            'for_receiving' => [
                'label' => 'Assigned — waiting to be received',
                'dp'    => ['=', '(For Purchasing Receival)'],
                'sr'    => ['=', '(For Purchasing Receival)'],
            ],
            'in_canvass' => [
                'label' => 'Received for canvass',
                'dp'    => ['like', 'RECEIVED FOR CANVASS%'],
                'sr'    => ['like', 'RECEIVED FOR CANVASS%'],
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'dp'    => ['like', '%CANCELLED%'],
                'sr'    => ['like', '%CANCELLED%'],
            ],
        ];

        $segments = [
            'total' => ['label' => 'All PA', 'apply' => function ($q) use ($paType) {
                $this->scopePaType($q, $paType);
            }],
        ];

        foreach ($stages as $key => $stage) {
            list($operator, $value) = $isDp ? $stage['dp'] : $stage['sr'];

            $segments[$key] = ['label' => $stage['label'], 'apply' => function ($q) use ($paType, $isDp, $operator, $value) {
                $this->scopePaType($q, $paType);

                if ($isDp) {
                    $q->whereHas('mrs', function ($m) use ($operator, $value) {
                        $m->where('status', $operator, $value);
                    });
                } else {
                    $q->where('status', $operator, $value);
                }
            }];
        }

        // received_at follows the status: on the MRS for a DP, on the PA for an SR.
        $segments['stale_canvass'] = ['label' => 'Out for canvass over 14 days', 'apply' => function ($q) use ($paType, $isDp) {
            $this->scopePaType($q, $paType);
            $cutoff = now()->subDays(14);

            if ($isDp) {
                $q->whereHas('mrs', function ($m) use ($cutoff) {
                    $m->where('status', 'like', 'RECEIVED FOR CANVASS%')
                        ->whereNotNull('received_at')
                        ->where('received_at', '<=', $cutoff);
                });
            } else {
                $q->where('status', 'like', 'RECEIVED FOR CANVASS%')
                    ->whereNotNull('received_at')
                    ->where('received_at', '<=', $cutoff);
            }
        }];

        return $segments;
    }


    public function dashboard()
    {
        // ---- Tiles for every module ---------------------------------------
        $modules = [];
        foreach ($this->dashboardModules() as $key => $module) {
            $tiles = [];
            foreach ($this->dashboardSegments($key) as $segKey => $segment) {
                $model = $module['model'];
                $query = $model::query();
                call_user_func($segment['apply'], $query);
                $tiles[$segKey] = ['label' => $segment['label'], 'count' => $query->count()];
            }
            $modules[$key] = ['label' => $module['label'], 'tiles' => $tiles];
        }

        // MRS keeps the richer charts below; these are its raw counts.
        $counts = [];
        foreach ($modules['MRS']['tiles'] as $segKey => $tile) {
            $counts[$segKey] = $tile['count'];
        }

        // ---- Cross-module volume comparison --------------------------------
        $moduleTotalsChart = [
            'labels' => [],
            'data'   => [],
        ];
        foreach ($modules as $module) {
            $moduleTotalsChart['labels'][] = $module['label'];
            $moduleTotalsChart['data'][]   = $module['tiles']['total']['count'];
        }

        // Share of assigned-but-unreceived MRS that have gone stale. The old
        // version divided overdue by the POSTED count — unrelated populations,
        // so it could exceed 100%.
        $percentageOverdue = $counts['for_receiving'] > 0
            ? number_format(($counts['overdue_receiving'] / $counts['for_receiving']) * 100, 1)
            : '0.0';

        // ---- Pipeline breakdown -------------------------------------------
        // One grouped scan, then mapped through the shared status taxonomy so
        // the dashboard tells the same story as every MRS screen.
        $statusTotals = SalesHeader::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $groupLabels = [
            'draft'     => 'Draft',
            'pending'   => 'With WFS',
            'process'   => 'In MCD pipeline',
            'approved'  => 'With canvasser',
            'hold'      => 'On hold',
            'action'    => 'Back with requestor',
            'cancelled' => 'Cancelled',
        ];

        $byGroup = array_fill_keys(array_keys($groupLabels), 0);
        $byStage = [];

        foreach ($statusTotals as $status => $total) {
            $parts = SalesHeader::requestorStatusPartsFor($status);

            if (isset($byGroup[$parts['group']])) {
                $byGroup[$parts['group']] += $total;
            }

            // Strip the trailing "(BY SOMEONE)" so one stage does not fan out
            // into a bucket per actor.
            $stage = trim(preg_replace('/\s*\(.*$/', '', $parts['label']));
            if ($stage === '') {
                $stage = 'UNKNOWN';
            }
            $byStage[$stage] = (isset($byStage[$stage]) ? $byStage[$stage] : 0) + $total;
        }

        arsort($byStage);
        $byStage = array_slice($byStage, 0, 10, true);

        $pipelineChart = [
            'labels' => array_values($groupLabels),
            'data'   => array_values(array_replace(array_fill_keys(array_keys($groupLabels), 0), $byGroup)),
        ];
        $stageChart = [
            'labels' => array_keys($byStage),
            'data'   => array_values($byStage),
        ];

        // ---- 12-month submission trend ------------------------------------
        // CONVERT(char(7), ..., 126) is ISO-8601 truncated to "yyyy-MM";
        // cheaper and more portable across SQL Server versions than FORMAT().
        $trendFrom = now()->startOfMonth()->subMonths(11);
        $monthly = SalesHeader::where('created_at', '>=', $trendFrom)
            ->select(DB::raw("CONVERT(char(7), created_at, 126) as ym"), DB::raw('count(*) as total'))
            ->groupBy(DB::raw("CONVERT(char(7), created_at, 126)"))
            ->pluck('total', 'ym');

        $trendLabels = [];
        $trendData   = [];
        for ($i = 0; $i < 12; $i++) {
            $month = $trendFrom->copy()->addMonths($i);
            $key   = $month->format('Y-m');
            $trendLabels[] = $month->format('M Y');
            $trendData[]   = (int) ($monthly[$key] ?? 0);
        }
        $trendChart = ['labels' => $trendLabels, 'data' => $trendData];

        // ---- Busiest departments ------------------------------------------
        $departmentRows = DB::table('ecommerce_sales_headers as sh')
            ->leftJoin('users as u', 'u.id', '=', 'sh.user_id')
            ->leftJoin('departments as d', 'd.id', '=', 'u.department_id')
            ->whereNull('sh.deleted_at')
            ->select(DB::raw("COALESCE(d.name, 'Unassigned') as dept"), DB::raw('count(*) as total'))
            ->groupBy(DB::raw("COALESCE(d.name, 'Unassigned')"))
            ->orderByRaw('count(*) desc')
            ->limit(8)
            ->get();

        $departmentChart = [
            'labels' => $departmentRows->pluck('dept')->all(),
            'data'   => $departmentRows->pluck('total')->map('intval')->all(),
        ];

        // ---- Ageing of MRS already out for canvass -------------------------
        // Buckets mirror the 14-day threshold the MRS/PA listings turn red at.
        $ageingRows = SalesHeader::whereNotNull('received_at')
            ->where('status', 'like', 'RECEIVED FOR CANVASS%')
            ->select(DB::raw("
                CASE
                    WHEN DATEDIFF(day, received_at, GETDATE()) <= 2  THEN '0-2 days'
                    WHEN DATEDIFF(day, received_at, GETDATE()) <= 7  THEN '3-7 days'
                    WHEN DATEDIFF(day, received_at, GETDATE()) <= 14 THEN '8-14 days'
                    ELSE '15+ days'
                END as bucket
            "), DB::raw('count(*) as total'))
            ->groupBy(DB::raw("
                CASE
                    WHEN DATEDIFF(day, received_at, GETDATE()) <= 2  THEN '0-2 days'
                    WHEN DATEDIFF(day, received_at, GETDATE()) <= 7  THEN '3-7 days'
                    WHEN DATEDIFF(day, received_at, GETDATE()) <= 14 THEN '8-14 days'
                    ELSE '15+ days'
                END
            "))
            ->pluck('total', 'bucket');

        $ageingOrder = ['0-2 days', '3-7 days', '8-14 days', '15+ days'];
        $ageingChart = [
            'labels' => $ageingOrder,
            'data'   => array_map(function ($b) use ($ageingRows) {
                return (int) ($ageingRows[$b] ?? 0);
            }, $ageingOrder),
        ];

        // ---- 12-month trend for IMF and PA, next to the MRS one -------------
        $imfTrend = InventoryRequest::where('created_at', '>=', $trendFrom)
            ->select(DB::raw("CONVERT(char(7), created_at, 126) as ym"), DB::raw('count(*) as total'))
            ->groupBy(DB::raw("CONVERT(char(7), created_at, 126)"))
            ->pluck('total', 'ym');

        $paTrend = PurchaseAdvice::where('created_at', '>=', $trendFrom)
            ->select(DB::raw("CONVERT(char(7), created_at, 126) as ym"), DB::raw('count(*) as total'))
            ->groupBy(DB::raw("CONVERT(char(7), created_at, 126)"))
            ->pluck('total', 'ym');

        $imfTrendData = [];
        $paTrendData  = [];
        for ($i = 0; $i < 12; $i++) {
            $key = $trendFrom->copy()->addMonths($i)->format('Y-m');
            $imfTrendData[] = (int) ($imfTrend[$key] ?? 0);
            $paTrendData[]  = (int) ($paTrend[$key] ?? 0);
        }
        $trendChart['imf'] = $imfTrendData;
        $trendChart['pa']  = $paTrendData;

        return view('admin.ecommerce.sales.mrs-dashboard', compact(
            'modules',
            'counts',
            'percentageOverdue',
            'pipelineChart',
            'stageChart',
            'trendChart',
            'departmentChart',
            'ageingChart',
            'moduleTotalsChart'
        ));
    }

    public function fetchMrsRecords(Request $request)
    {
        $moduleKey = $request->input('module', 'MRS');
        $modules   = $this->dashboardModules();

        if (!isset($modules[$moduleKey])) {
            return response()->json(['error' => 'Unknown module.'], 422);
        }

        $segments = $this->dashboardSegments($moduleKey);
        $type     = $request->input('type', 'total');

        if (!isset($segments[$type])) {
            return response()->json(['error' => 'Unknown segment.'], 422);
        }

        $model = $modules[$moduleKey]['model'];
        $query = $model::query();
        if ($moduleKey === 'MRS') {
            $query->with('user.department');
        } elseif ($moduleKey !== 'IMF') {
            $query->with('mrs');
        }
        call_user_func($segments[$type]['apply'], $query);

        // Capped: "total" used to pull every MRS in the system into one modal.
        $limit   = 200;
        $total   = (clone $query)->count();
        $records = $query->orderBy('created_at', 'desc')->limit($limit)->get();

        return response()->json([
            'module'   => $moduleKey,
            'label'    => $segments[$type]['label'],
            'total'    => $total,
            'shown'    => $records->count(),
            'limit'    => $limit,
            'records'  => $records->map(function ($record) use ($moduleKey) {
                if ($moduleKey === 'IMF') {
                    return [
                        'reference'    => '#' . $record->id,
                        'context'      => strtoupper($record->department ?: '—'),
                        'requested_by' => strtoupper($record->type ?: '—'),
                        'status'       => $record->status,
                        'created_at'   => $record->created_at ? $record->created_at->format('M d, Y') : '—',
                    ];
                }

                if ($moduleKey !== 'MRS') {
                    return [
                        'reference'    => $record->pa_number,
                        'context'      => optional($record->mrs)->order_number ?: '—',
                        'requested_by' => optional($record->planner)->name ?: '—',
                        'status'       => $record->status,
                        'created_at'   => $record->created_at ? $record->created_at->format('M d, Y') : '—',
                    ];
                }

                return [
                    'reference'    => $record->order_number,
                    'context'      => optional(optional($record->user)->department)->name ?: '—',
                    'requested_by' => $record->requested_by ?: (optional($record->user)->name ?: '—'),
                    'status'       => $record->requestor_status,
                    'created_at'   => $record->created_at ? $record->created_at->format('M d, Y') : '—',
                ];
            })->values(),
        ]);
    }

    /**
     * Show the modal data – returns all MRS records available for selection.
     * Called via AJAX when the modal opens.
     */
    public function getMrsListForDeletion(Request $request)
    {
        $mrs = SalesHeader::select('id', 'order_number', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($mrs);
    }

    /**
     * Send a deletion-request email for the selected MRS records.
     *
     * POST /admin/sales-transaction/send-delete-request
     *
     * Request params:
     *   - recipient_email  string   required  Destination email address
     *   - selected_mrs     array    required  Array of SalesHeader IDs
     *   - email_body       string   required  Custom message from the user
     */
    public function sendDeleteRequest(Request $request)
    {
        $request->validate([
            'recipient_email' => 'required|email',
            'selected_mrs'    => 'required|array|min:1',
            'selected_mrs.*'  => 'exists:ecommerce_sales_headers,id',
            'email_body'      => 'required|string|max:2000',
        ]);

        // Fetch order numbers for the selected IDs
        $orderNumbers = SalesHeader::whereIn('id', $request->selected_mrs)
            ->pluck('order_number')
            ->toArray();

        $senderName = auth()->user()->name ?? 'System User';

        try {
            // \Mail::to($request->recipient_email)
            //     ->queue(new \App\Mail\MrsDeleteRequestMail(
            //         $orderNumbers,
            //         $request->email_body,
            //         $senderName
            //     ));

            return response()->json([
                'success' => true,
                'message' => 'Deletion request email sent successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('MRS delete request email failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again.',
            ], 500);
        }
    }
}
