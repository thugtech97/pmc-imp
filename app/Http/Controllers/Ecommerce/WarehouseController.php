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
use App\Models\Ecommerce\DeliveryStatus;
use App\Mail\DeliveryCompletedNotification;
use Illuminate\Support\Facades\Mail;
use App\Services\Notifier;

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
        // Delivery status filter. The warehouse listing measures ordered-vs-delivered (see
        // SalesHeader::getDeliveryStatusLabel()), not to-order-vs-ordered like the purchasing
        // screens, so the CASE here has to mirror that or the filter contradicts the balance
        // column. Statuses are whitelisted before they reach the raw SQL.
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $statuses = array_intersect((array) $_GET['status'], ['COMPLETED', 'PARTIAL', 'UNSERVED']);

            if (count($statuses)) {
                $statusList = "'" . implode("','", $statuses) . "'";

                $sales->whereHas('items', function ($subQuery) use ($statusList) {
                    $subQuery->havingRaw("
                        CASE
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) > 0
                                 AND SUM(CASE WHEN promo_id != 1 THEN qty_delivered ELSE 0 END) >= SUM(CASE WHEN promo_id != 1 THEN qty_ordered ELSE 0 END) THEN 'COMPLETED'
                            WHEN SUM(CASE WHEN promo_id != 1 THEN qty_delivered ELSE 0 END) > 0 THEN 'PARTIAL'
                            ELSE 'UNSERVED'
                        END IN ({$statusList})
                    ");
                });
            }
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

        // Who stamped this MRS as delivered, and when — shown on the delivery progress card.
        $deliveryMarkedAt = DeliveryStatus::with('user')
            ->where('order_id', $id)
            ->where('status', 'Delivered')
            ->latest()
            ->first();

        if($totalNet <= $totalPayment)
        $status = 'PAID';

        else $status = 'UNPAID';
        return view('admin.warehouse.view',compact('sales','salesPayments','salesDetails','status', 'role', 'purchasers', 'deliveryMarkedAt'));
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request)
    {
        $header_id = $request->sales_header_id;
        $h = SalesHeader::with('items.product')->find($header_id);

        if (!$h) {
            return back()->with("error", "MRS request not found.");
        }

        DB::beginTransaction();
        try {
            $changes = [];

            foreach ($h->items as $i) {
                $qty_delivered = $request->input('qty_delivered'.$i->id);

                // Held and not-yet-ordered lines render their input disabled, so the browser
                // posts nothing for them — skip those instead of blanking the qty on record.
                if ($qty_delivered === null || $qty_delivered === '') {
                    continue;
                }

                $old = (float) $i->qty_delivered;
                $new = (float) $qty_delivered;

                // Untouched lines are re-posted verbatim on every save. Writing them anyway
                // would bump updated_at and, worse, make the requestor's notification claim a
                // change that never happened.
                if (abs($old - $new) < 0.0001) {
                    continue;
                }

                $i->update(["qty_delivered" => $qty_delivered]);

                $changes[] = [
                    'name' => optional($i->product)->name ?: ('Item #' . $i->id),
                    'from' => $this->formatQty($old),
                    'to'   => $this->formatQty($new),
                ];
            }

            // response_code is owned by the purchasing flow (PurchaseAdviceController), so the
            // warehouse must not stamp it here — it would clobber who released the PA.

            // Recompute off the rows we just wrote, not the stale ones loaded above.
            $h->load('items.product');
            $totalOrdered   = $h->totalQtyOrdered();
            $totalDelivered = $h->totalQtyDelivered();

            $isComplete  = $totalOrdered > 0 && $totalDelivered >= $totalOrdered;
            $wasComplete = $h->delivery_status === 'Delivered';

            // Only act on the transition. Without this the completion bell re-fires on every
            // save once the totals sit at parity, and a downward correction would leave the
            // header claiming 'Delivered' forever.
            $justCompleted = $isComplete && !$wasComplete;
            $reopened      = !$isComplete && $wasComplete;

            if ($justCompleted || $reopened) {
                $newStatus = $isComplete ? 'Delivered' : 'Processing';

                $h->update(['delivery_status' => $newStatus]);

                // Same audit table the manual status change writes to, so both routes show up
                // in one trail. Deliberately NOT reusing SalesController::delivery_status()'s
                // 'Delivered' branch — that also books a cash payment, which has no business
                // firing off an internal warehouse issuance.
                DeliveryStatus::create([
                    'order_id' => $h->id,
                    'user_id'  => Auth::id(),
                    'status'   => $newStatus,
                    'remarks'  => $isComplete
                        ? 'Auto-marked from Warehouse MRS: all ordered quantities delivered.'
                        : 'Auto-reverted from Warehouse MRS: delivered quantity dropped below ordered.',
                ]);
            }

            DB::commit();

            $h->load('user');

            // Tell the requestor about every delivered-qty change, not just the final one.
            // Completion gets its own wording so the last notification still reads as the
            // milestone; anything else reports what actually moved.
            if (count($changes) || $justCompleted) {
                if ($justCompleted && optional($h->user)->email) {
                    // Mail::to($h->user->email)
                    //     ->queue(new DeliveryCompletedNotification($h));
                }

                $summary = count($changes) ? $this->summariseQtyChanges($changes) : '';

                if ($justCompleted) {
                    $title       = 'MRS Fully Delivered';
                    $notifyBody  = "All items for your MRS #{$h->order_number} have been delivered."
                                 . ($summary ? " Latest update: {$summary}." : '');
                    $notifyState = 'DELIVERED';
                } else {
                    $title       = 'MRS Delivery Updated';
                    $notifyBody  = "Delivered quantity updated on your MRS #{$h->order_number}: {$summary}. "
                                 . "Outstanding balance: " . $this->formatQty($totalOrdered - $totalDelivered) . ".";
                    $notifyState = 'DELIVERY UPDATED';
                }

                Notifier::toUser($h->user_id, [
                    'title'   => $title,
                    'message' => $notifyBody,
                    'url'     => route('profile.sales.view', $h->id),
                    'module'  => 'MRS',
                    'status'  => $notifyState,
                ]);
            }

            $message = "No changes to save.";
            if (count($changes)) {
                $message = count($changes) . " item(s) updated. The requestor has been notified.";
            }
            if ($justCompleted) {
                $message = "MRS request details updated. This MRS is now marked as DELIVERED.";
            } elseif ($reopened) {
                $message = "MRS request details updated. Delivery is no longer complete, so the status was set back to Processing.";
            }

            return back()->with("success", $message);
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with("error", "An error occurred: " . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        //
    }

    /**
     * Quantities are decimals ("1.00") but are whole numbers in practice — trim the noise so
     * a notification reads "2" rather than "2.00", while still surviving a genuine "1.5".
     */
    private function formatQty($value)
    {
        $value = (float) $value;

        if ($value == (int) $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    /**
     * Human-readable summary of the delivered-qty edits for the requestor's bell message.
     * Caps the item list so an MRS with 20 changed lines does not produce a wall of text.
     */
    private function summariseQtyChanges(array $changes)
    {
        $shown = array_slice($changes, 0, 3);
        $parts = [];

        foreach ($shown as $c) {
            $parts[] = $c['name'] . ' (' . $c['from'] . ' to ' . $c['to'] . ')';
        }

        $summary   = implode(', ', $parts);
        $remaining = count($changes) - count($shown);

        if ($remaining > 0) {
            $summary .= ' and ' . $remaining . ' more item(s)';
        }

        return $summary;
    }
}
