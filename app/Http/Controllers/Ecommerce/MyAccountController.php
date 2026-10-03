<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Mail\RevisedMrsNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\History;
use App\Services\Notifier;

use App\Models\Ecommerce\{
    Cart, SalesHeader, SalesDetail, Product
};

use App\Models\{
    Page, User, Role
};

use Auth;
use DateTime;
use Carbon\Carbon;

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
    /*
    this line is brought to you by
    */
    public function orders(Request $request)
    {
        $page = new Page();
        $page->name = 'MRS - For Purchase (DP, Stock Item)';

        // Rows are loaded via DataTables server-side processing (see ordersData()).
        $postedCount = SalesHeader::where('status', 'POSTED')->where('user_id', Auth::id())->count();

        $inProgressOverdue = SalesHeader::where('status', 'like', '%IN-PROGRESS%')
            ->where('created_at', '<=', now()->subDays(2))
            ->where('user_id', Auth::id())
            ->count();

        $percentageOverdue = $postedCount > 0
            ? number_format(($inProgressOverdue / $postedCount) * 100, 2)
            : 0;

        return view('theme.pages.customer.orders', compact('page', 'postedCount', 'inProgressOverdue', 'percentageOverdue'));
    }

    /**
     * DataTables server-side processing feed for the customer MRS list.
     */
    public function ordersData(Request $request)
    {
        if (!Auth::check()) {
            abort(401);
        }

        // index -> orderable DB column. 'pa' (index 1) is a relationship, handled below.
        $columns = ['order_number', 'pa', 'created_at', 'purpose', 'status'];

        $base = SalesHeader::with(['purchaseAdvice', 'items', 'issuances', 'purchaser'])
            ->where('user_id', Auth::id());
        $recordsTotal = (clone $base)->count();

        $query = clone $base;

        // Global search box
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('costcode', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('purchaseAdvice', function ($pa) use ($search) {
                        $pa->where('pa_number', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter chips. "Delivered" is the one chip that is not a stored-status
        // match: the warehouse stamps delivery_status instead of rewriting status.
        $statusFilter = $request->input('status_filter');
        if ($statusFilter === 'DELIVERED') {
            $query->where('delivery_status', 'Delivered');
        } elseif (!empty($statusFilter)) {
            $query->where('status', 'like', "%{$statusFilter}%");
        }

        $recordsFiltered = (clone $query)->count();

        // Ordering (default: newest first)
        $orderColIndex = (int) $request->input('order.0.column', 2);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColIndex] ?? 'created_at';
        if ($orderColumn === 'pa') {
            $orderColumn = 'created_at'; // PA# is a relationship, not a sortable column
        }
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $sales = $query->get();
        $this->flagMissingFromWfs($sales);

        $data = $sales->map(function ($sale) {
            return [
                'mrs_no'  => '<span class="fw-bold">' . e($sale->order_number) . '</span>'
                    . ($sale->revision > 0 ? ' <span style="display:inline-block;background:#f6931d;color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;">Rev' . (int) $sale->revision . '</span>' : ''),
                'pa'      => '<span class="badge2">' . e($sale->purchaseAdvice->pa_number ?? 'N/A') . '</span>',
                'created' => \Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i A'),
                'remarks' => '<span class="small">' . e($sale->purpose) . '</span>',
                'status'  => trim(view('theme.pages.customer._mrs-status-cell', compact('sale'))->render()),
                'options' => trim(view('theme.pages.customer._mrs-options-cell', compact('sale'))->render()),
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * Sets wfs_missing on each POSTED MRS that WFS does not properly have: no
     * transaction of its own, or one no approver can see. Those were marked
     * POSTED by the old submit code even though the WFS side failed, and sit
     * there forever; the flag lets the requestor resubmit them. One WFS query
     * per page; if WFS cannot be read nothing is flagged.
     */
    private function flagMissingFromWfs($sales)
    {
        $posted = $sales->filter(function ($sale) {
            return strtoupper($sale->status) === 'POSTED';
        });
        if ($posted->isEmpty()) {
            return;
        }

        try {
            $refnos = $posted->pluck('id')->all();
            $transidLike = 'MRS%';
            $found = require(base_path('api/wfs-received-api.php'));
        } catch (\Throwable $e) {
            Log::warning('WFS check for POSTED MRS failed', ['error' => $e->getMessage()]);
            return;
        }
        if (!is_array($found)) {
            return;
        }

        foreach ($posted as $sale) {
            $sale->wfs_missing = empty($found[(string) $sale->id]);
        }
    }

    /**
     * Rendered "View Details" modal content for a single MRS (loaded on demand).
     */
    public function orderDetails($id)
    {
        $sale = SalesHeader::with(['items.product', 'purchaseAdvice', 'user'])
            ->where('user_id', Auth::id())
            ->find($id);

        if (!$sale) {
            abort(404);
        }

        // Fetch the WFS approvers for this one MRS (moved out of the list load).
        if (!defined('__ROOT__')) {
            define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        }
        $data = [
            "token"   => config('app.key'),
            "transid" => 'MRS' . $sale->order_number,
            "refno"   => $sale->id,
        ];
        $approvers = require(__ROOT__ . '\api\wfs-approvers-api.php');
        $sale->approvers = collect($approvers);

        return view('theme.pages.customer._mrs-view-details', compact('sale'))->render();
    }

    /**
     * Full-page (themed) MRS detail view — used by notification deep-links so the
     * page renders inside the department layout instead of the bare modal partial.
     */
    public function orderView($id)
    {
        $sale = SalesHeader::with(['items.product', 'purchaseAdvice', 'user'])
            ->where('user_id', Auth::id())
            ->find($id);

        if (!$sale) {
            abort(404);
        }

        if (!defined('__ROOT__')) {
            define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        }
        $data = [
            "token"   => config('app.key'),
            "transid" => 'MRS' . $sale->order_number,
            "refno"   => $sale->id,
        ];
        $approvers = require(__ROOT__ . '\api\wfs-approvers-api.php');
        $sale->approvers = collect($approvers);

        $page = new Page;
        $page->name = 'MRS No. ' . $sale->order_number;

        return view('theme.pages.customer.orders.show', compact('sale', 'page'));
    }

    public function cancel_order(Request $request)
    {
        $sales = SalesHeader::find($request->orderid);
        $data = [
            "type" => config('app.name'),
            "transid" => 'MRS'.$sales->order_number,
            "refno" => $sales->id,
            "token" => config('app.key')
        ];

        define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $result = require(__ROOT__ . '\api\cancel-transaction.php');

        if ($result) {
            History::context($sales, [
                'action'          => 'cancelled',
                'title'           => 'Cancelled by the requestor',
                'requestor_title' => 'CANCELLED (BY YOU)',
            ]);
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
        $sales = SalesHeader::withOrderNumberLock(function () use ($request, $id) {
            return DB::transaction(function () use ($request, $id) {
                $sales = SalesHeader::whereKey($id)->lockForUpdate()->firstOrFail();

                // Once submitted, the number is the MRS's WFS transaction id ('MRS' +
                // number); changing it strands that transaction and frees the number
                // for another MRS to collide with in WFS.
                if ($sales->date_posted) {
                    // keep the number
                } elseif ($request->filled('mrs_no')) {
                    $requestedOrderNumber = $request->mrs_no;

                    if (SalesHeader::orderNumberExists($requestedOrderNumber, $id)) {
                        $requestedOrderNumber = SalesHeader::nextOrderNumber(null, $id);
                        $request->merge(['mrs_no' => $requestedOrderNumber]);
                    }

                    $sales->order_number = $requestedOrderNumber;
                } elseif (SalesHeader::orderNumberExists($sales->order_number, $id)) {
                    $sales->order_number = SalesHeader::nextOrderNumber(null, $id);
                }

                History::context($sales, [
                    'action'          => 'updated',
                    'title'           => 'Request details edited by the requestor',
                    'requestor_title' => 'You updated the request details',
                ]);
                $sales->update([
                    'costcode' => $request->costcode,
                    'priority' => $request->priority,
                    'purpose' => $request->justification,
                    'delivery_date' => Carbon::parse($request->delivery_date)->format('Y-m-d'),
                    'budgeted_amount' => $request->budgeted_amount,
                    'section' => $request->section,
                    'requested_by' => $request->requested_by,
                    'other_instruction' => $request->notes,
                ]);

                return $sales;
            }, 5);
        });

        if ($sales->status === "REQUEST ON HOLD (Hold by MCD Planner)") {
            History::context($sales, [
                'action'          => 'revised',
                'title'           => 'Revised and resubmitted by the requestor',
                'requestor_title' => 'REVISED - FOR MCD PLANNER REVIEW',
            ]);
            $sales->update([
                'status' => 'REVISED MRS - ' .Carbon::now()->format('Y-m-d h:i:s A'),
                // Revised after a hold — bump the revision counter (Rev1, Rev2, ...).
                'revision' => (int) $sales->revision + 1,
                'revised_at' => now(),
            ]);
            // Mail::to([
            //     'aobesoro@philsagamining.com',
            //     'mgimproso@philsagamining.com'
            // ])->queue(new RevisedMrsNotification($sales));
            // In-app: the revised MRS is back in the MCD Planner queue for review.
            Notifier::toRoleName('MCD Planner', [
                'title'   => 'Revised MRS Resubmitted',
                'message' => "MRS #{$sales->order_number} was revised by the requestor and is back in your queue for review.",
                'url'     => route('sales-transaction.view', $sales->id),
                'module'  => 'MRS',
                'status'  => 'REVISED MRS',
            ]);
        }

        if ($request->hasFile('attachment')) {
            $files = $request->file('attachment'); // Get all uploaded files
            if (is_array($files) && isset($files[0])) {
                $this->upsertAttachedFiles($sales, $sales->id, $files); // Use the first file
            } elseif (!is_array($files)) {
                // Handle single file upload (not an array)
                $this->upsertAttachedFiles($sales, $sales->id, $files);
            }
        }

        if (is_array($request->qty)) {
            foreach ($request->qty as $key => $value) {
                $detail = SalesDetail::find($key);
                if ($detail) {
                    $detail->update([
                        "qty" => floatval($value),
                        "cost_code" => $request->cost_code[$key],
                        "par_to" => $request->par_to[$key],
                        "frequency" => $request->frequency[$key],
                        "purpose" => $request->purpose[$key],
                        "date_needed" => $request->date_needed[$key],
                    ]);
                }
            }
        }

        // Buffered per-line edits from the loop above.
        History::flushItemChanges();

        return back()->with('success','MRS Request has been updated.');
    }

    public function next_order_number(){
        return SalesHeader::nextOrderNumber();
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

            $sanitizedFilename = substr(preg_replace('/[^\w-]/', '_', $originalFilename), 0, 80);
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
        $result = $this->submitForApproval($id, $status);

        if ($result['ok']) {
            return redirect()->back()->with('success', trim('Request has been submitted. ' . ($result['notice'] ?? '')));
        }
        else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
    /*
    this line is brought to you by
    */
    public function orderRequest($id, $status)
    {
        $page = new Page;
        $page->name = 'Order Posted';
        $result = $this->submitForApproval($id, $status);

        if ($result['ok']) {
            return view('theme.pages.ecommerce.submitted', compact('page'));
        }
        else {
            // The order-success page has no alert area; the MRS is saved, so send
            // the requestor to My Orders where they can see the error and resubmit.
            return redirect()->route('profile.sales')->with('error', $result['message']);
        }
    }

    // Returns ['ok' => bool, 'message' => string]. A WFS failure (unreachable,
    // not registered, no approver set up, ...) leaves the MRS as it was.
    public function submitForApproval($id, $status)
    {
        try {
            $result = $this->sendToWfs($id);
        } catch (\Throwable $e) {
            $result = [
                'ok'      => false,
                'message' => 'Something went wrong while submitting to WFS. Your request was saved but not submitted. Please try again later or contact IT.',
                'detail'  => get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine(),
            ];
        }

        if (!$result['ok']) {
            Log::error('MRS WFS submission failed', ['mrs_id' => $id, 'user_id' => Auth::id(), 'detail' => $result['detail']]);
        }

        return $result;
    }

    private function sendToWfs($id)
    {
        $product = SalesHeader::find($id);
        if (!$product) {
            return ['ok' => false, 'message' => 'This request no longer exists.', 'detail' => 'SalesHeader ' . $id . ' not found'];
        }
        if ((int) $product->user_id !== (int) Auth::id()) {
            return ['ok' => false, 'message' => 'Only the requestor can submit this request.', 'detail' => 'user ' . Auth::id() . ' tried to submit MRS ' . $id . ' owned by ' . $product->user_id];
        }
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
            // wfs-api.php resets an existing transaction to PENDING only when the
            // status contains 'ON HOLD', but a WFS hold is stored as 'REQUEST ON-HOLD
            // (...) - WFS' (hyphen) — without this the resubmit left WFS on HOLD and
            // the next poll flipped the MRS straight back to on-hold.
            "status" => strpos($product->status, 'ON-HOLD') !== false
                ? 'ON HOLD - WFS'
                : str_replace("'", "", $product->status)
        ];

        $result = require(base_path('api/wfs-api.php'));

        // WFS has no transaction of this MRS's own and its number is held by another
        // MRS (wfs-api.php only says 'transid_taken' then): give it a fresh number
        // and try once more. Safe because there is nothing of its own to strand.
        if (!$result['ok'] && ($result['code'] ?? null) === 'transid_taken') {
            $oldNumber = $product->order_number;
            SalesHeader::withOrderNumberLock(function () use ($product, $oldNumber) {
                return DB::transaction(function () use ($product, $oldNumber) {
                    $newNumber = SalesHeader::nextOrderNumber(null, $product->id);
                    History::context($product, [
                        'action'          => 'updated',
                        'title'           => 'MRS No. changed from ' . $oldNumber . ' to ' . $newNumber . ' (the old number was already used in WFS)',
                        'requestor_title' => 'MRS No. changed to ' . $newNumber,
                    ]);
                    $product->update(['order_number' => $newNumber]);
                });
            });
            Log::warning('MRS renumbered before WFS submission', ['mrs_id' => $id, 'from' => $oldNumber, 'to' => $product->order_number, 'detail' => $result['detail']]);

            $data['transid'] = 'MRS' . $product->order_number;
            $result = require(base_path('api/wfs-api.php'));
            if ($result['ok']) {
                $result['notice'] = 'Its MRS No. was changed from ' . $oldNumber . ' to ' . $product->order_number . ' because the old number was already used in WFS.';
            }
        }

        if ($result['ok']) {
            History::context($product, [
                'action'          => 'submitted',
                'title'           => 'Submitted to WFS for approval by the requestor',
                'requestor_title' => 'SUBMITTED - FOR WFS APPROVAL',
            ]);
            $product->update([
                'status' => 'POSTED',
                'date_posted' => date('Y-m-d H:i:s'),
                //'note_planner' => NULL,
            ]);
            Cart::where('user_id', Auth::id())
            ->whereIn('mrs_details_id', $product->items->pluck('id'))
            ->delete();
        }

        return $result;
    }

    /**
     * Polled by the MRS/IMF list pages (main.blade.php). Answers JSON so the page
     * can tell the requestor when WFS could not be asked, instead of the failure
     * only reaching the browser console.
     */
    public function updateRequestApproval()
    {
        try {
            $error = $this->pollWfsApprovals();
        } catch (\Throwable $e) {
            Log::error('MRS WFS approval poll failed', ['user_id' => Auth::id(), 'error' => get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine()]);
            $error = 'Something went wrong while reading approvals from WFS.';
        }

        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 503);
        }
        return response()->json(['status' => 'ok']);
    }

    // Returns null, or the reason WFS could not be asked (for the requestor).
    private function pollWfsApprovals(){
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

        // Scope the WFS lookup to MRS transactions only (see approval-status-api.php).
        $transidLike = 'MRS';
        $wfsPollError = null;
        $WFSrequests = require(base_path('api/approval-status-api.php'));
        if ($wfsPollError) {
            return $wfsPollError;
        }
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
                if (!$request) {
                    continue;
                }
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

                // Only notify when the status actually changes (this endpoint is polled).
                $previousStatus = $request->status;

                // This endpoint is polled by the requestor's browser, so the signed-in
                // user is not the one who acted — name the WFS approver in the entry
                // instead, or the trail credits every WFS decision to the requestor.
                History::context($request, [
                    'action'          => 'status',
                    'title'           => 'WFS: ' . $status . ($updated_by ? ' by ' . $updated_by : ''),
                    'requestor_title' => SalesHeader::requestorStatusPartsFor($statusText)['label'],
                ]);

                $request->update([
                    'status' => $statusText,
                ]);

                if ($previousStatus !== $statusText) {
                    $requestorUrl = route('profile.sales.view', $request->id);

                    if ($status == "FULLY APPROVED") {
                        Notifier::toUser($request->user_id, [
                            'title'   => 'MRS Approved (WFS)',
                            'message' => "Your MRS #{$request->order_number} was approved via WFS and is now with the MCD Planner.",
                            'url'     => $requestorUrl,
                            'module'  => 'MRS',
                            'status'  => $statusText,
                        ]);
                        Notifier::toRoleName('MCD Planner', [
                            'title'   => 'New MRS for Review',
                            'message' => "MRS #{$request->order_number} is approved by WFS and awaiting your action.",
                            'url'     => route('sales-transaction.view', $request->id),
                            'module'  => 'MRS',
                            'status'  => $statusText,
                        ]);
                    } elseif ($status == "HOLD") {
                        Notifier::toUser($request->user_id, [
                            'title'   => 'MRS On Hold (WFS)',
                            'message' => "Your MRS #{$request->order_number} was placed on hold in WFS.",
                            'url'     => $requestorUrl,
                            'module'  => 'MRS',
                            'status'  => $statusText,
                        ]);
                    } elseif ($status == "CANCELLED") {
                        Notifier::toUser($request->user_id, [
                            'title'   => 'MRS Cancelled (WFS)',
                            'message' => "Your MRS #{$request->order_number} was cancelled in WFS.",
                            'url'     => $requestorUrl,
                            'module'  => 'MRS',
                            'status'  => $statusText,
                        ]);
                    }
                }
            }
        }

        return null;
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
        $mrs = SalesHeader::with('items.product', 'purchaser')->find($request->mrs);

        if ($mrs) {
            return response()->json([
                'headers' => $mrs,
                'hasPromo' => $mrs->hasPromo(),
                // Requestor-facing label; 'headers.status' stays raw for the edit-gating checks.
                'requestor_status' => $mrs->requestor_status,
                'requestor_status_group' => $mrs->requestor_status_group,
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
            $header    = $item->header;
            $itemLabel = $item->historyItemLabel();
            $item->delete();

            History::mrs($header, [
                'action'          => 'item_removed',
                'title'           => 'Item removed by the requestor: ' . $itemLabel,
                'requestor_title' => 'You removed an item: ' . $itemLabel,
            ]);

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

        History::mrs($mrsDetail->header, [
            'action'          => 'item_added',
            'title'           => 'Item added by the requestor: ' . $mrsDetail->historyItemLabel(),
            'requestor_title' => 'You added an item: ' . $mrsDetail->historyItemLabel(),
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
        History::context($sale, [
            'action'          => 'updated',
            'title'           => 'Attachment removed: ' . basename($request->file_path),
            'requestor_title' => 'You removed an attachment: ' . basename($request->file_path),
        ]);
        $sale->update([
            'order_source' => implode('|', $updatedPaths)
        ]);

        return response()->json(['success' => true]);
    }
}
