<?php

namespace App\Http\Controllers\Ecommerce;

use Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ListingHelper;
use App\Http\Controllers\Controller;
use App\Services\Notifier;
use Illuminate\Support\Facades\Validator;
use App\Models\{
    Permission, Page, Issuance, IssuanceItem, Department, ViewLog, User, Role
};
use App\Models\Ecommerce\{
    DeliveryStatus, SalesPayment, SalesHeader, SalesDetail, Product, PurchaseAdvice
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
            })
            ->orderByRaw("
                CASE 
                    WHEN status LIKE '%FULLY APPROVED%' THEN 0
                    WHEN status = 'HOLD (For MCD Planner re-edit)' THEN 1
                    WHEN status LIKE '%REVISED MRS%' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('id', 'desc'); // Secondary sorting by ID
        }        

        if ($role->name === "MCD Verifier") {
            $sales = $sales->whereIn('status', [
                    'APPROVED (MCD Planner) - MRS For Verification',
                    'Verified (MCD Verifier) - PA For MCD Manager Approval',
                ])
                ->orderByRaw("
                    CASE 
                        WHEN status = 'APPROVED (MCD Planner) - MRS For Verification' THEN 0 
                        ELSE 1 
                    END
                ") // Prioritize APPROVED (MCD Planner) - MRS For Verification
                ->orderBy('id', 'desc'); // Secondary sorting by ID
        }        

        if ($role->name === "MCD Approver") {
            $sales = $sales->whereIn('status', [
                    'Verified (MCD Verifier) - PA For MCD Manager Approval',
                    'APPROVED (MCD Approver) - PA for Delegation',
                ])
                ->orderByRaw("
                    CASE 
                        WHEN status = 'Verified (MCD Verifier) - PA For MCD Manager Approval' THEN 0 
                        ELSE 1 
                    END
                ") // Prioritize Verified (MCD Verifier) status
                ->orderBy('id', 'desc'); // Secondary sorting by ID
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
            return back()->with("success", "MRS adjustments now updated. Purchase advice now generated.");
        } catch (\Exception $e) {
            DB::rollBack();
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
                $mrs->update(["received_by" => $note, "status" => "(For Purchasing Receival)", "received_at" => null]);
                // $note holds the assigned purchaser's user id.
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
                $mrs->update(["status" => "RECEIVED FOR CANVASS (Purchasing Officer)", "received_at" => Carbon::now()]);
                // Mirror onto the PA so both modules agree on who holds it and from when.
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

    public function dashboard()
    {
        // Posted sales
        $postedCount = SalesHeader::where('status', 'POSTED')->count();

        // In-progress overdue (2 days)
        $inProgressOverdue = SalesHeader::where('status', 'like', '%IN-PROGRESS%')
            ->where('created_at', '<=', now()->subDays(2))
            ->count();

        // All in-progress
        $inProgressCount = SalesHeader::where('status', 'like', '%IN-PROGRESS%')->count();

        // Total sales
        $totalSales = SalesHeader::count();

        // Fully approved but not received (canvassers)
        $approvedNullReceived = SalesHeader::whereNotNull('approved_at')
            ->whereNull('received_by')
            ->count();

        // Approved but not received
        $approvedNotReceived = SalesHeader::whereNotNull('approved_at')
            ->whereNotNull('received_by')
            ->whereNull('received_at')
            ->count();

        // Percentage overdue
        $percentageOverdue = $postedCount > 0
            ? number_format(($inProgressOverdue / $postedCount) * 100, 2)
            : 0;

        return view('admin.ecommerce.sales.mrs-dashboard', compact(
            'postedCount',
            'inProgressOverdue',
            'inProgressCount',
            'totalSales',
            'approvedNotReceived',
            'percentageOverdue',
            'approvedNullReceived'
        ));
    }

    public function fetchMrsRecords(Request $request)
    {
        $type = $request->type;

        $query = SalesHeader::query();

        switch ($type) {
            case 'total':
                // All MRS
                break;
            case 'posted':
                $query->where('status', 'POSTED');
                break;
            case 'in-progress':
                $query->where('status', 'like', '%IN-PROGRESS%');
                break;
            case 'overdue':
                $query->where('status', 'like', '%IN-PROGRESS%')
                    ->where('created_at', '<=', now()->subDays(2));
                break;
            case 'approved_not_received':
                $query->whereNotNull('approved_at')
                    ->whereNull('received_by');
                break;
            case 'approved_no_canvasser':
                $query->whereNotNull('approved_at')
                    ->whereNull('received_by');
                break;
            default:
                $query->limit(50);
        }

        $records = $query->orderBy('created_at', 'desc')
                        ->get(['order_number', 'customer_name', 'requested_by']);

        return response()->json($records);
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
