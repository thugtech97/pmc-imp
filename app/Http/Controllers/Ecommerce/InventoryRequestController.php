<?php

namespace App\Http\Controllers\Ecommerce;

use Auth;
use DateTime;
use Illuminate\Http\Request;
use App\Models\AllowedTransaction;
use Illuminate\Support\Facades\{
    File,
    DB
};
use App\Http\Controllers\Controller;
use App\Http\Requests\NewStockRequest;
use App\Services\History;
use App\Services\Notifier;
use App\Helpers\ListingHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Constants\ActionQueue;
use App\Constants\Status;
use App\Models\{
    Page, User, Role
};
use App\Models\Ecommerce\{
    Product,
    InventoryRequest,
    InventoryRequestItems,
    InventoryRequestsOldItem
};

class InventoryRequestController extends Controller
{
    private $searchFields = ['id', 'department', 'type', 'created_at', 'updated_at'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::check())
        {
            $page = new Page;
            $page->name = 'Inventory Maintenance Form';

            // Rows are loaded via DataTables server-side processing (see indexData()).
            return view('theme.pages.customer.new-stock.list', compact(['page']));
        }
        else {
            return redirect()->route('customer-front.login');
        }
    }

    /**
     * DataTables server-side processing feed for the customer IMF list.
     */
    public function indexData(Request $request)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $columns = ['id', 'type', 'department', 'created_at', 'submitted_at', 'status'];

        $base = InventoryRequest::where('user_id', Auth::id());
        $recordsTotal = (clone $base)->count();

        $query = clone $base;

        // Global search box
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Status filter chips
        $statusFilter = $request->input('status_filter');
        if (!empty($statusFilter)) {
            $query->where('status', 'like', "%{$statusFilter}%");
        }

        $recordsFiltered = (clone $query)->count();

        // Ordering
        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columns[$orderColIndex] ?? 'id';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        // Edit: SAVED (draft) or returned by the Planner. Submit to WFS: SAVED only.
        $editableStatuses = [Status::SAVED, Status::HOLD_PLANNER];

        $data = $query->get()->map(function ($r) use ($editableStatuses) {
            $canEdit = in_array($r->status, $editableStatuses);
            $canSubmit = $r->status === Status::SAVED;

            $actions = '<a href="' . route('new-stock.show', $r->id) . '" class="imf-action" title="View"><i class="icon-line-eye"></i></a>';
            if ($canEdit) {
                $actions .= '<a href="' . route('new-stock.edit', $r->id) . '" class="imf-action" title="Edit"><i class="icon-edit"></i></a>';
            }
            if ($canSubmit) {
                $actions .= '<a href="javascript:;" onclick="confirmApproval(' . $r->id . ', \'new\')" class="imf-action" title="Submit for approval"><i class="icon-arrow-alt-circle-right"></i></a>';
            }

            return [
                'id'             => '<span class="fw-bold">#' . $r->id . '</span>'
                    . ($r->revision > 0 ? ' <span style="display:inline-block;background:#f6931d;color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;">Rev' . (int) $r->revision . '</span>' : ''),
                'type'           => '<span class="imf-type imf-type-' . strtolower($r->type) . '">' . strtoupper($r->type) . '</span>',
                'department'     => '<span class="text-uppercase">' . e($r->department) . '</span>',
                'date_prepared'  => $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('M d, Y') : '—',
                'date_submitted' => $r->submitted_at ? \Carbon\Carbon::parse($r->submitted_at)->format('M d, Y') : '—',
                'status'         => trim(view('theme.pages.customer.new-stock._status-badge', ['status' => $r->status])->render()),
                'actions'        => '<div class="text-end text-nowrap">' . $actions . '</div>',
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::check()) {
            $page = new Page;
            $page->name = 'Inventory Maintenance Form (IMF) - New Request';

            return view('theme.pages.customer.new-stock.create', compact(['page']));
        }
        else {
            return redirect()->route('customer-front.login');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        try{

            $department = $request->input('department');
            $section = $request->input('section');
            $division = $request->input('division');
            $updateType = $this->normalizeUpdateType($request->input('update_type'));
            $type = $request->input('type');
            $action = $request->input('action');

            $msg = "Request has been";

            if ($type === "new")
            {
                $new = InventoryRequest::create([
                    "priority" => $request->input("priority.0") ?? $request->input("priority"),
                    "department" => $department,
                    "section" => $section,
                    "division" => $division,
                    "type" => $type,
                    "status" => $action,
                    "user_id" => Auth::id()
                ]);
                $items = $request->except(['_token', 'department', 'type']);
          
                $itemCount = count($request->input('stock_code'));

                for ($i = 0; $i < $itemCount; $i++) 
                {
                    $fields = [
                        'item_description' => $request->input("item_description.$i"),
                        'brand' => $request->input("brand.$i"),
                        'OEM_ID' => $request->input("OEM_ID.$i"),
                        'UoM' => $request->input("UoM.$i"),
                        'usage_rate_qty' => $request->input("usage_rate_qty.$i"),
                        'usage_frequency' => $request->input("usage_frequency.$i"),
                        'purpose' => $request->input("purpose.$i"),
                        'min_qty' => $request->input("min_qty.$i"),
                        'max_qty' => $request->input("max_qty.$i"),
                    ];

                    if (in_array('', $fields)) {
                        continue;
                    }

                    $item = InventoryRequestItems::create(array_merge($fields, ['stock_code' => $request->input("stock_code.$i"), 'imf_no' => $new->id]));

                    $file = $request->file("attachment.$i");
                    $this->upsertAttachedFiles($new->id,  $item->id, $file);
                }

            } else {
                
                $stockCode = $request->input('stock_code');
                $file = $request->file("attachment");

                $product = Product::where('code', $stockCode)->first();
                $inventoryRequestItem = InventoryRequestItems::where('stock_code', $stockCode)->first();
                
                if (!empty($inventoryRequestItem)) {
                    $new = InventoryRequest::create([
                        "priority" => $request->input("priority.0") ?? $request->input("priority"),
                        "department" => $department,
                        "section" => $section,
                        "division" => $division,
                        "update_type" => $updateType,
                        "type" => $type,
                        "status" => $action,
                        "user_id" => Auth::id()]);

                    $item = InventoryRequestItems::create([
                        "stock_code" => $request->input('stock_code'),
                        "item_description" => $request->input('item_description'),
                        "brand" => $request->input('brand'),
                        "OEM_ID" => $request->input('OEM_ID'),
                        "UoM" => $request->input('UoM'),
                        "usage_rate_qty" => $request->input('usage_rate_qty'),
                        "usage_frequency" => $request->input('usage_frequency'),
                        "purpose" => $request->input('purpose'),
                        "min_qty" => $request->input('min_qty'),
                        "max_qty" => $request->input('max_qty'),
                        "imf_no" => $new->id,
                        "product_id" => $product->id,
                    ]);

                    $this->upsertAttachedFiles($item->imf_no,  $item->id, $file);
                    $this->upsertOldItemData($request->input('old-data'), $item->imf_no);

                } else {
                    $new = InventoryRequest::create([
                        "priority" => $request->input("priority.0") ?? $request->input("priority"),
                        "department" => $department,
                        "section" => $section,
                        "division" => $division,
                        "update_type" => $updateType,
                        "type" => $type,
                        "status" => $action,
                        "user_id" => Auth::id()]);

                    $item = InventoryRequestItems::create([
                        "stock_code" => $request->input('stock_code'),
                        "item_description" => $request->input('item_description'),
                        "brand" => $request->input('brand'),
                        "OEM_ID" => $request->input('OEM_ID'),
                        "UoM" => $request->input('UoM'),
                        "usage_rate_qty" => $request->input('usage_rate_qty'),
                        "usage_frequency" => $request->input('usage_frequency'),
                        "purpose" => $request->input('purpose'),
                        "min_qty" => $request->input('min_qty'),
                        "max_qty" => $request->input('max_qty'),
                        "imf_no" => $new->id,
                    ]);
                    $this->upsertAttachedFiles($item->imf_no,  $item->id, $file);
                    //$this->upsertOldItemData($request->input('old-data'), $item->imf_no);
                }
                
            }
            
            $response = [
                'status' => 'success',
                'message' => $msg . ' saved!',
                'redirect' => route('new-stock.index'),
            ];

            return response()->json($response);
        } catch(Exception $e){
            $response = [
                'status' => 'error',
                'message' => $e,
                'redirect' => route('new-stock.index'),
            ];
            return response()->json($response);
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $msg = "Request has been";
            $type = $request->input('type');

            // For "new" and "update" the {id} is the IMF (header) id, so keep its
            // header fields in sync. For "update-item" the {id} is an item id, skip.
            $imf = ($type === "new" || $type === "update") ? InventoryRequest::find($id) : null;
            $originalStatus = $imf ? $imf->status : null;

            if ($imf) {
                $imf->update([
                    'department'  => $request->input('department', $imf->department),
                    'section'     => $request->input('section'),
                    'division'    => $request->input('division'),
                    'priority'    => $request->input('priority', $imf->priority),
                    'update_type' => $this->normalizeUpdateType($request->input('update_type')),
                ]);
            }

            if($type === "new")
            {
                InventoryRequestItems::where("imf_no", $id)->delete();

                $itemCount = count($request->input('stock_code'));
                
                for ($i = 0; $i < $itemCount; $i++) 
                {
                    $fields = [
                        'item_description' => $request->input("item_description.$i"),
                        'brand' => $request->input("brand.$i"),
                        'OEM_ID' => $request->input("OEM_ID.$i"),
                        'UoM' => $request->input("UoM.$i"),
                        'usage_rate_qty' => $request->input("usage_rate_qty.$i"),
                        'usage_frequency' => $request->input("usage_frequency.$i"),
                        'purpose' => $request->input("purpose.$i"),
                        'min_qty' => $request->input("min_qty.$i"),
                        'max_qty' => $request->input("max_qty.$i"),
                    ];

                    if (in_array('', $fields)) {
                        continue;
                    }

                    $item = InventoryRequestItems::create(array_merge($fields, ['stock_code' => $request->input("stock_code.$i"), 'imf_no' => $id]));

                    $file = $request->file("attachment.$i");
                    $this->upsertAttachedFiles($id,  $item->id, $file);
                }
                
            } else {

                $file = $request->file("attachment");
                $columnId = ($type === 'update-item' ? 'id' : 'imf_no');
                $items = InventoryRequestItems::where($columnId, $id)->first();
                
                $items->update([
                    "item_description" => $request->input('item_description'),
                    "brand" => $request->input('brand'),
                    "OEM_ID" => $request->input('OEM_ID'),
                    "UoM" => $request->input('UoM'),
                    "usage_rate_qty" => $request->input('usage_rate_qty'),
                    "usage_frequency" => $request->input('usage_frequency'),
                    "purpose" => $request->input('purpose'),
                    "min_qty" => $request->input('min_qty'),
                    "max_qty" => $request->input('max_qty'),
                ]);

                $this->upsertAttachedFiles($items->imf_no,  $items->id, $file);

                if ($type === 'update') {
                    $this->upsertOldItemData($request->input('old-data'), $id);
                }
            }

            // A department user re-editing a Planner-held IMF sends it back to the
            // MCD Planner queue directly — no re-submission to WFS.
            if ($imf && $originalStatus === Status::HOLD_PLANNER) {
                // Revised after a hold — bump the revision counter (Rev1, Rev2, ...).
                History::context($imf, [
                    'action'          => 'revised',
                    'title'           => 'Revised and resubmitted by the requestor',
                    'requestor_title' => 'Revised - for MCD Planner review',
                ]);
                $imf->update(['status' => Status::APPROVED_WFS, 'note_planner' => null, 'revision' => (int) $imf->revision + 1, 'revised_at' => now()]);
                // The returned IMF is back in the Planner queue — let them know.
                Notifier::toRoleName('MCD Planner', [
                    'title'   => 'IMF Resubmitted',
                    'message' => "IMF #{$imf->id} was revised by the requestor and is back in your queue for review.",
                    'url'     => route('imf.requests.view', $imf->id),
                    'module'  => 'IMF',
                    'status'  => Status::APPROVED_WFS,
                ]);
            }

            $response = [
                'status' => 'success',
                'message' => $msg . ' updated!',
                'redirect' => route('new-stock.index'),
            ];

            return response()->json($response);
        }catch(Exception $e){
            $response = [
                'status' => 'error',
                'message' => $e,
                'redirect' => route('new-stock.index'),
            ];
            return response()->json($response);
        }
    }

    /**
     * Normalize the "Update" purpose sub-types (checkbox group) into a
     * comma-separated string for storage. Accepts array or string input.
     */
    private function normalizeUpdateType($value)
    {
        if (is_array($value)) {
            // Plain closure, not fn() => : the production server runs PHP < 7.4.
            $value = array_filter(array_map('trim', $value), function ($v) {
                return $v !== '';
            });
            return empty($value) ? null : implode(', ', $value);
        }

        return ($value === null || trim($value) === '') ? null : trim($value);
    }

    private function upsertAttachedFiles($imfId, $itemId, $file)
    {
        if ($file) 
        {
            $storagePath = 'public/inventory_items/' . $imfId;
            $filename = $itemId;
            $files = Storage::files($storagePath);

            foreach ($files as $existingFile) {
                $existingFilename = pathinfo($existingFile, PATHINFO_FILENAME);
                if ($existingFilename == $filename) {
                    Storage::delete($existingFile);
                }
            }

            $filePath = $file->storeAs($storagePath, $filename . '.' . $file->getClientOriginalExtension());
        }
    }

    private function upsertOldItemData($requestData, $id) 
    {
        $oldItem = json_decode($requestData, true);

        if (!empty($oldItem)) {
            $combinedChanges = [];

            foreach ($oldItem as $change) {
                if ($change['name'] === 'imf_no') {
                    $id = $change['value'];
                } else {
                    $combinedChanges[$change['name']] = $change['value'];
                }
            }

            if (InventoryRequestsOldItem::where('imf_no', $id)->exists()) {
                InventoryRequestsOldItem::where('imf_no', $id)->update($combinedChanges);
            } else {
                $combinedChanges['imf_no'] = $id;
                InventoryRequestsOldItem::create($combinedChanges);
            }
        }
        
        $inventoryRequest = InventoryRequest::find($id);
        $inventoryRequest->touch();
    }

    public function show($id)
    {
        $request = InventoryRequest::find($id);
        
        if (!$request) {
            abort(404);
        }

        $items = $request->items;
        $oldItems = InventoryRequestsOldItem::where('imf_no', $id)->get();
        $user = User::find($request->user_id);
        $role = Role::where('id', $user->role_id)->first();

        foreach ($items as $key => $item) 
        {
            $storagePath = 'public/inventory_items/' . $id;
            $filename = $item->id;
            $files = Storage::files($storagePath);
        
            foreach ($files as $existingFile) 
            {
                $existingFilename = pathinfo($existingFile, PATHINFO_FILENAME);
                if ($existingFilename == $filename) {
                    $items[$key]->file_path = $existingFile ;
                }
            }
        }

        $page = new Page;
        $page->name = 'Inventory Maintenance Form (IMF) - View Request';

        return view('theme.pages.customer.new-stock.show', compact(['request', 'items', 'oldItems', 'page', 'role']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (Auth::check()) {
            $request = InventoryRequest::find($id);

            if (!$request) {
                abort(404); // Handle the case where the request is not found
            }
            
            $items = $request->items;

            $page = new Page;
            $page->name ='Inventory Maintenance Form (IMF) - Update Request';

            return view('theme.pages.customer.new-stock.edit', compact(['request', 'items', 'page']));
        }else{
            return redirect()->route('customer-front.login');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function updateStatus(Request $request, $id)
    {
        $product_request = InventoryRequest::find($id);
        $product_request->update([
            'status' => $request->status,
            'approved_at' => $request->status == "APPROVED" ? date('Y-m-d') : null
        ]);

        if ($product_request->type != "update") {
            $product = Product::create([
                'code' => $product_request->stock_code,
                'description' => $product_request->item_description,
                'brand' => $product_request->brand,
                'oem' => $product_request->OEM_ID,
                'uom' => $product_request->UoM ?? 'test',
                'name' => 'New Product',
                'slug' => 'new-product',
                'status' => 'PUBLISHED',
                'created_by' => 1
            ]);
        }

        if ($product_request) {
            return response()->json(['message' => 'Item has been approved.', 'status' => 1]);
        }
        else {
            return response()->json(['message' => 'Oops! Something went wrong.', 'status' => 0]);
        }
    }

    public function submitRequest($id, $type)
    {
        $output = $this->submission($id, $type);

        if ($output) {
            return response()->json(['status' => 'success']);
        }
        else {
            return response()->json(['status' => 'error']);
        }
    }

    public function submission($id, $type) {
        
        $product = InventoryRequest::find($id);

        if (!$product) {
            return false;
        }
        $itemPurpose = InventoryRequestItems::where('imf_no', $id)->pluck('purpose')->implode('-');
        $limitedPurposes = Str::limit($itemPurpose, 255);
        $requestor = auth()->user();
        $data = [
            "type" => config('app.name'),
            "transid" => 'IMP-IMF-' . uniqid(),
            "token" => config('app.key'),
            "refno" => $id,
            "sourceapp" => 'IMP-MRS-PA',
            "sourceurl" => route('new-stock.show', $id),
            "requestor" => $requestor->name,
            "department" => $requestor->department->name,
            "email" => $requestor->email,
            "purpose" => $limitedPurposes,
            "name" => $requestor->name,
            "template_id" => config('app.template_id'),
            "locsite" => "",
            "status" => $product->status
        ];

        define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $result = require(__ROOT__ . '\api\wfs-api.php');

        if ($result) {
            History::context($product, [
                'action'          => 'submitted',
                'title'           => 'Submitted to WFS for approval by the requestor',
                'requestor_title' => 'Submitted - for WFS approval',
            ]);
            $product->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now()
            ]);
            return true;
        }

        return false;
    }

    public function updateRequestApproval(){
        $imfs = InventoryRequest::where('status', 'SUBMITTED')->get();
        $ids = "";
        foreach ($imfs as $imf) {
            if ($ids == "") {
                $ids = $imf->id;
            } else {
                $ids = $ids . "," . $imf->id;
            }
        }

        define('__ROOT2__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));

        // Scope the WFS lookup to IMF transactions only (see approval-status-api.php).
        $transidLike = 'IMF';
        $WFSrequests = require(__ROOT2__ . '\api\approval-status-api.php');
        foreach ($WFSrequests as $WFSrequest) {
            $WFSrequestArr = explode('|', $WFSrequest);
            $ref_req_no = $WFSrequestArr[0];
            $status = $WFSrequestArr[1];
            $approved_at = DateTime::createFromFormat('Y-m-d H:i:s',  $WFSrequestArr[2]);
            $approved_by = $WFSrequestArr[3];
            $transno = $WFSrequestArr[4];
            if ($status != "PENDING" && strpos($transno, 'IMF') !== false) {
                $request = InventoryRequest::find($ref_req_no);
                if (!$request) {
                    continue;
                }
                // Only notify when the status actually changes (this endpoint is polled).
                $previousStatus = $request->status;
                $newStatus = ($status == "FULLY APPROVED") ? "APPROVED - WFS" : $status;

                // Polled by the requestor's browser, so the signed-in user is not the
                // one who acted — name the WFS approver instead.
                History::context($request, [
                    'action'          => $status == "FULLY APPROVED" ? 'approved' : 'status',
                    'title'           => 'WFS: ' . $status . ($approved_by ? ' by ' . $approved_by : ''),
                    'requestor_title' => $status == "FULLY APPROVED"
                        ? 'Approved in WFS - for MCD Planner'
                        : strtoupper((string) $status),
                ]);

                $request->update([
                    'status' => $newStatus,
                    'approved_at' => $approved_at,
                    'approved_by' => $approved_by,
                ]);

                // Notify the requestor + the MCD Planner queue once WFS fully approves.
                if ($status == "FULLY APPROVED" && $previousStatus !== $newStatus) {
                    Notifier::toUser($request->user_id, [
                        'title'   => 'IMF Approved (WFS)',
                        'message' => "Your IMF #{$request->id} was approved via WFS and is now with the MCD Planner.",
                        'url'     => route('new-stock.show', $request->id),
                        'module'  => 'IMF',
                        'status'  => 'APPROVED - WFS',
                    ]);
                    Notifier::toRoleName('MCD Planner', [
                        'title'   => 'New IMF for Review',
                        'message' => "IMF #{$request->id} is approved by WFS and awaiting your review.",
                        'url'     => route('imf.requests.view', $request->id),
                        'module'  => 'IMF',
                        'status'  => 'APPROVED - WFS',
                    ]);
                }
            }
        }
    }

    public function imf_requests(Request $request)
    {
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();
        $query = InventoryRequest::query();

        $query->with('items');

        if ($request->has('search')) 
        {
            $searchValue = $request->input('search');
        
            $query->where(function ($q) use ($searchValue) {
                $q->where('id', 'like', "%$searchValue%")
                    ->orWhere('department', 'like', "%$searchValue%")
                    ->orWhere('status', 'like', "%$searchValue%")
                    ->orWhere('type', 'like', "%$searchValue%")
                    ->orWhere('created_at', 'like', "%$searchValue%")
                    ->orWhere('updated_at', 'like', "%$searchValue%")
                    ->orWhereHas('items', function ($subquery) use ($searchValue) {
                        $subquery->where('stock_code', 'like', "%$searchValue%");
                    });
            });
        }
        if($role->name === "MCD Planner"){
            // Both Planner passes (review, then stock code) plus whatever is
            // sitting with the Verifier or the final approver, for reference.
            $statuses = array_merge(
                Status::imfPlannerStages(),
                [Status::FOR_VERIFICATION, Status::APPROVED_MCD],
                Status::imfFinalApproved()
            );
            $query->whereIn('status', $statuses);
        }elseif($role->name === "MCD Verifier"){
            // Everything from the Planner's endorsement onwards — the Verifier
            // acts on FOR_VERIFICATION and keeps sight of what moved past them.
            $query->whereIn('status', array_merge(
                [Status::FOR_VERIFICATION, Status::VERIFIED_MCD, Status::APPROVED_MCD],
                Status::imfFinalApproved()
            ));
        }else{
            // Planning Supervisor (acts) and the MCD Approver (view only) both see
            // what the Planner endorsed plus everything already fully approved.
            $query->whereIn('status', array_merge([Status::APPROVED_MCD], Status::imfFinalApproved()));
        }

        // Whatever is on this role's desk goes to the top of page 1 — the same list
        // behind the sidebar badge and the NEEDS YOUR ACTION flag on the row.
        $actionOrder = ActionQueue::orderCase(ActionQueue::IMF, $role->name);
        if ($actionOrder) {
            $query->orderByRaw($actionOrder);
        }

        $query->orderBy('id', 'desc');

        $imfs = $query->paginate(10);
    
        $filter = [];
        $searchType = 'simple_search';
    
        return view('admin.ecommerce.inventory.imf-index', compact('imfs', 'filter', 'searchType'));
    }
    
    public function imf_request_view($id){

        $request = InventoryRequest::find($id);

        if (!$request) {
            abort(404);
        }

        $items = $request->items;
        $oldItems = InventoryRequestsOldItem::where('imf_no', $id)->get();
        
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();

        return view('admin.ecommerce.inventory.imf-view', compact(['request', 'items', 'oldItems', 'role']));
    }

    /**
     * Columns each desk owns in the line grid. A stale form from one role can
     * never overwrite another role's entries.
     *
     * @return array
     */
    private function imfLineColumns($stage)
    {
        $map = [
            'planner_review' => ['planner_remarks'],
            'verifier'       => ['inventory_code', 'item_class', 'dlt', 'verifier_remarks'],
            'planner_stock'  => ['stock_code'],
        ];

        return isset($map[$stage]) ? $map[$stage] : [];
    }

    /**
     * Save whatever the acting role typed into the line grid of an IMF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ecommerce\InventoryRequest  $imf
     * @param  string  $stage
     * @return void
     */
    private function saveImfLineFields(Request $request, $imf, $stage)
    {
        $columns = $this->imfLineColumns($stage);
        $lines   = $request->input('lines');

        if (empty($columns) || !is_array($lines)) {
            return;
        }

        $items = InventoryRequestItems::where('imf_no', $imf->id)->get()->keyBy('id');

        foreach ($lines as $itemId => $values) {
            $item = $items->get($itemId);

            if (!$item || !is_array($values)) {
                continue;
            }

            $payload = [];
            foreach ($columns as $column) {
                if (!array_key_exists($column, $values)) {
                    continue;
                }

                $value = trim((string) $values[$column]);
                $payload[$column] = $value === '' ? null : $value;
            }

            if (!empty($payload)) {
                $item->update($payload);
            }
        }
    }

    /**
     * Stock codes on a new-item IMF that are already in the item master.
     *
     * Registering under one of those would overwrite an unrelated product, so
     * the IMF is stopped and the code sent back to be checked against Classic.
     * An update IMF is excluded — its product is meant to exist.
     *
     * @param  \App\Models\Ecommerce\InventoryRequest  $imf
     * @return array
     */
    private function imfStockCodesTaken($imf)
    {
        if ($imf->type !== 'new') {
            return [];
        }

        $codes = InventoryRequestItems::where('imf_no', $imf->id)
            ->orderBy('id')
            ->pluck('stock_code')
            ->map(function ($code) {
                return trim((string) $code);
            })
            ->filter(function ($code) {
                return $code !== '' && $code !== 'null';
            })
            ->unique()
            ->values()
            ->all();

        if (empty($codes)) {
            return [];
        }

        return Product::whereIn('code', $codes)
            ->pluck('code')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Line numbers (1-based, as shown on screen) with no value in $column.
     *
     * @param  \App\Models\Ecommerce\InventoryRequest  $imf
     * @param  string  $column
     * @return array
     */
    private function imfLinesMissing($imf, $column)
    {
        $missing = [];

        $items = InventoryRequestItems::where('imf_no', $imf->id)->orderBy('id')->get();
        foreach ($items as $index => $item) {
            $value = trim((string) $item->{$column});

            // "null" as a string is how older rows store an empty stock code.
            if ($value === '' || $value === 'null') {
                $missing[] = $index + 1;
            }
        }

        return $missing;
    }

    public function imf_action(Request $request, $id)
    {
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();
        $roleName = $role ? $role->name : '';

        // The IMF runs: MCD Planner (line remarks) -> MCD Verifier (inventory
        // code, class, DLT per line) -> MCD Planner (stock code generated in
        // Classic) -> Planning Supervisor (final approval). The MCD Approver
        // keeps the screen for reference only.
        $isApprover = $roleName == "Planning Supervisor";
        $isPlanner  = $roleName == "MCD Planner";
        $isVerifier = $roleName == "MCD Verifier";

        if (!$isApprover && !$isPlanner && !$isVerifier) {
            return redirect()->route('imf.requests.view', $id)
                ->with('error', 'Your role cannot act on IMF requests.');
        }

        try{

            $imf = InventoryRequest::find($id);
            if (!$imf) {
                abort(404);
            }

            // Which desk the IMF is sitting on right now. Anything else is a
            // stale post (e.g. a page left open) so an IMF cannot be acted on
            // twice or out of order.
            $atPlannerReview = $isPlanner  && in_array($imf->status, Status::imfPlannerReviewStage());
            $atPlannerStock  = $isPlanner  && in_array($imf->status, Status::imfPlannerStockCodeStage());
            $atVerifier      = $isVerifier && $imf->status === Status::FOR_VERIFICATION;
            $atApprover      = $isApprover && $imf->status === Status::APPROVED_MCD;

            if (!$atPlannerReview && !$atPlannerStock && !$atVerifier && !$atApprover) {
                return redirect()->route('imf.requests.view', $id)
                    ->with('error', 'This IMF is no longer waiting on your action.');
            }

            $action = $request->action;

            if ($atPlannerReview)     $stage = 'planner_review';
            elseif ($atVerifier)      $stage = 'verifier';
            elseif ($atPlannerStock)  $stage = 'planner_stock';
            else                      $stage = 'approver';

            // Whatever was typed into the line grid belongs to the acting desk
            // and is kept even when the IMF is only saved or held.
            if ($action != "reject") {
                $this->saveImfLineFields($request, $imf, $stage);
            }

            /* ---------------- SAVE (stay on the same desk) ---------------- */
            if ($action == "save") {
                return redirect()->route('imf.requests.view', $id)
                    ->with('success', 'Entries saved. The request stays with you until you endorse it.');
            }

            /* ---------------- APPROVE / ENDORSE ---------------- */
            if ($action == "approve")
            {
                /* --- MCD Planner, first pass: endorse to the MCD Verifier --- */
                if ($atPlannerReview) {
                    History::context($imf, [
                        'action'          => 'endorsed',
                        'title'           => 'Reviewed by the MCD Planner and endorsed to the MCD Verifier',
                        'requestor_title' => 'Reviewed by MCD Planner - for MCD Verifier',
                    ]);
                    $imf->update([
                        'status'              => Status::FOR_VERIFICATION,
                        'planner_approved_by' => ($user->name ?? null),
                    ]);

                    Notifier::toRoleName('MCD Verifier', [
                        'title'   => 'IMF for Verification',
                        'message' => "IMF #{$imf->id} was endorsed by the MCD Planner and needs the inventory code, class and DLT per item.",
                        'url'     => route('imf.requests.view', $imf->id),
                        'module'  => 'IMF',
                        'status'  => Status::FOR_VERIFICATION,
                    ]);
                    Notifier::toUser($imf->user_id, [
                        'title'   => 'IMF for Verification',
                        'message' => "Your IMF #{$imf->id} was reviewed by the MCD Planner and is now with the MCD Verifier.",
                        'url'     => route('new-stock.show', $imf->id),
                        'module'  => 'IMF',
                        'status'  => Status::FOR_VERIFICATION,
                    ]);

                    return redirect()->route('imf.requests')
                        ->with('success', 'Request endorsed to the MCD Verifier.');
                }

                /* --- MCD Verifier: inventory code, class and DLT are in --- */
                if ($atVerifier) {
                    // An update IMF is for an item already registered in Classic,
                    // so only a new-item IMF must carry an inventory code.
                    $missing = $imf->type === 'new' ? $this->imfLinesMissing($imf, 'inventory_code') : [];
                    if (!empty($missing)) {
                        return redirect()->route('imf.requests.view', $id)
                            ->with('error', 'Enter the inventory code for item ' . implode(', ', $missing) . ' before verifying.');
                    }

                    History::context($imf, [
                        'action'          => 'verified',
                        'title'           => 'Verified by the MCD Verifier and returned to the MCD Planner for the stock code',
                        'requestor_title' => 'Verified by MCD Verifier - for MCD Planner stock code',
                    ]);
                    $imf->update([
                        'status'               => Status::VERIFIED_MCD,
                        'verified_at'          => now(),
                        'verifier_approved_by' => ($user->name ?? null),
                    ]);

                    Notifier::toRoleName('MCD Planner', [
                        'title'   => 'IMF Verified',
                        'message' => "IMF #{$imf->id} was verified by the MCD Verifier. Generate the stock code in Classic and enter it per item.",
                        'url'     => route('imf.requests.view', $imf->id),
                        'module'  => 'IMF',
                        'status'  => Status::VERIFIED_MCD,
                    ]);
                    Notifier::toUser($imf->user_id, [
                        'title'   => 'IMF Verified',
                        'message' => "Your IMF #{$imf->id} was verified by the MCD Verifier and is back with the MCD Planner for the stock code.",
                        'url'     => route('new-stock.show', $imf->id),
                        'module'  => 'IMF',
                        'status'  => Status::VERIFIED_MCD,
                    ]);

                    return redirect()->route('imf.requests')
                        ->with('success', 'Request verified and returned to the MCD Planner.');
                }

                /* --- MCD Planner, second pass: stock code -> Supervisor --- */
                if ($atPlannerStock) {
                    // The stock code is the whole point of this pass for a new
                    // item; an update IMF already carries the existing code.
                    $missing = $imf->type === 'new' ? $this->imfLinesMissing($imf, 'stock_code') : [];
                    if (!empty($missing)) {
                        return redirect()->route('imf.requests.view', $id)
                            ->with('error', 'Enter the generated stock code for item ' . implode(', ', $missing) . ' before endorsing.');
                    }

                    // Caught here rather than at the Supervisor's desk, where the
                    // code can no longer be corrected.
                    $taken = $this->imfStockCodesTaken($imf);
                    if (!empty($taken)) {
                        return redirect()->route('imf.requests.view', $id)
                            ->with('error', 'Stock code ' . implode(', ', $taken) . ' is already used by another product. Check the code generated in Classic.');
                    }

                    History::context($imf, [
                        'action'          => 'endorsed',
                        'title'           => 'Stock codes entered by the MCD Planner and endorsed to the Planning Supervisor',
                        'requestor_title' => 'Stock code generated - for Planning Supervisor approval',
                    ]);
                    $imf->update([
                        'status'              => Status::APPROVED_MCD,
                        'planner_approved_by' => ($user->name ?? null),
                    ]);

                    Notifier::toRoleName('Planning Supervisor', [
                        'title'   => 'IMF for Approval',
                        'message' => "IMF #{$imf->id} was endorsed by the MCD Planner and awaits your approval.",
                        'url'     => route('imf.requests.view', $imf->id),
                        'module'  => 'IMF',
                        'status'  => Status::APPROVED_MCD,
                    ]);

                    // The requesting department asked to be told as soon as the
                    // stock codes exist, so list them in the notification.
                    $codes = InventoryRequestItems::where('imf_no', $imf->id)
                        ->orderBy('id')
                        ->pluck('stock_code')
                        ->filter(function ($code) {
                            return $code !== null && trim((string) $code) !== '' && $code !== 'null';
                        })
                        ->implode(', ');

                    Notifier::toUser($imf->user_id, [
                        'title'   => 'IMF Stock Code Generated',
                        'message' => $codes !== ''
                            ? "Stock code(s) generated for your IMF #{$imf->id}: {$codes}. It is now with the Planning Supervisor for approval."
                            : "Your IMF #{$imf->id} was endorsed by the MCD Planner to the Planning Supervisor.",
                        'url'     => route('new-stock.show', $imf->id),
                        'module'  => 'IMF',
                        'status'  => Status::APPROVED_MCD,
                    ]);

                    return redirect()->route('imf.requests')
                        ->with('success', 'Stock codes saved. Request endorsed to the Planning Supervisor.');
                }

                /* --- Planning Supervisor: final approval --- */
                if ($imf->type == "new")
                {
                    // Last guard before the item master is written to: another
                    // product may have claimed the code since the Planner typed it.
                    $taken = $this->imfStockCodesTaken($imf);
                    if (!empty($taken)) {
                        return redirect()->route('imf.requests.view', $id)
                            ->with('error', 'Stock code ' . implode(', ', $taken) . ' is already used by another product. Return the IMF to the MCD Planner to correct it.');
                    }

                    // Core function: register each item as a new Product.
                    $items = InventoryRequestItems::where("imf_no", $id)->get();

                    foreach($items as $item)
                    {
                        // The stock code the Planner generated in Classic is the
                        // item master code — the product must carry that exact
                        // code, not a number this app made up.
                        $newProductCode = trim((string) $item->stock_code);

                        if ($newProductCode === '' || $newProductCode === 'null') {
                            // Older IMFs approved before the stock-code stage
                            // existed keep the original next-free-number behaviour.
                            $maxProductCode = DB::table('products')
                                ->select(DB::raw('MAX(CAST(NULLIF(\'0\' + code, \'0\') AS INT)) AS max_numeric_value'))
                                ->whereRaw('code NOT LIKE ?', ['%[a-zA-Z]%'])
                                ->value('max_numeric_value');
                            $newProductCode = $maxProductCode + 1;
                        }

                        // The code is known to be free — imfStockCodesTaken()
                        // stopped the approval otherwise.
                        $product = Product::create([
                            'category_id' => 29,
                            'code' => $newProductCode,
                            'description' => $item->item_description,
                            'brand' => $item->brand,
                            'oem' => $item->OEM_ID,
                            'uom' => $item->UoM ?? 'test',
                            'name' => $item->item_description,
                            'slug' => 'new-product',
                            'status' => 'PUBLISHED',
                            'created_by' => 1
                        ]);

                        $item->update(['product_id' => $product->id]);
                    }

                    $message = "Products inserted!";
                }
                else
                {
                    // Core function: apply the changes to the existing Product.
                    $item = InventoryRequestItems::where("imf_no", $id)->first();
                    $product = Product::where("code", $item->stock_code)->first();

                    if ($product)
                    {
                        $product->update([
                            'description' => $item->item_description,
                            'brand' => $item->brand,
                            'oem' => $item->OEM_ID,
                            'uom' => $item->UoM ?? 'test',
                            'name' => $item->item_description,
                        ]);

                        $item->update(['product_id' => $product->id]);
                    }

                    $message = "Product updated!";
                }

                // Record who acted at this stage for the printed signatory block.
                History::context($imf, [
                    'action'          => 'approved',
                    'title'           => 'Approved by the Planning Supervisor',
                    'requestor_title' => 'Approved by Planning Supervisor',
                ]);
                $imf->update([
                    'status'               => Status::APPROVED_SUPERVISOR,
                    'approved_at'          => now(),
                    'approver_approved_by' => ($user->name ?? null),
                ]);

                // Final approval — the requestor's IMF is done.
                Notifier::toUser($imf->user_id, [
                    'title'   => 'IMF Fully Approved',
                    'message' => "Your IMF #{$imf->id} has been approved by the Planning Supervisor.",
                    'url'     => route('new-stock.show', $imf->id),
                    'module'  => 'IMF',
                    'status'  => Status::APPROVED_SUPERVISOR,
                ]);

                return redirect()->route('imf.requests')->with('success', $message);
            }

            /* ---------------- HOLD / REJECT (remarks required) ---------------- */
            if ($action == "hold" || $action == "reject") {
                $remarks = trim((string) $request->input('remarks'));
                if ($remarks === '') {
                    return redirect()->route('imf.requests.view', $id)
                        ->with('error', 'A remark is required to hold or reject the request.');
                }

                // Each desk holds one step back: Supervisor -> Planner,
                // Planner (stock code) -> Verifier, Verifier -> Planner, and
                // Planner (review) -> the department user who raised the IMF.
                if ($action == "hold") {
                    if ($atApprover) {
                        $status  = Status::HOLD_SUPERVISOR;
                        $message = "Request held and returned to the MCD Planner.";
                        $historyTitle = 'Held by the Planning Supervisor and returned to the MCD Planner';
                        $historyReq   = 'On hold - with MCD Planner for re-edit';
                    } elseif ($atVerifier) {
                        $status  = Status::HOLD_MCD_VERIFIER;
                        $message = "Request held and returned to the MCD Planner.";
                        $historyTitle = 'Held by the MCD Verifier and returned to the MCD Planner';
                        $historyReq   = 'On hold - with MCD Planner for re-edit';
                    } elseif ($atPlannerStock) {
                        $status  = Status::FOR_VERIFICATION;
                        $message = "Request returned to the MCD Verifier.";
                        $historyTitle = 'Returned by the MCD Planner to the MCD Verifier';
                        $historyReq   = 'On hold - with MCD Verifier for re-check';
                    } else {
                        $status  = Status::HOLD_PLANNER;
                        $message = "Request held and returned to the requestor.";
                        $historyTitle = 'Held by the MCD Planner and returned to the requestor';
                        $historyReq   = 'Returned to you for revision - MCD Planner';
                    }
                } else {
                    if ($atApprover) {
                        $status = Status::REJECTED_SUPERVISOR;
                        $historyTitle = 'Rejected by the Planning Supervisor';
                        $historyReq   = 'Rejected by Planning Supervisor';
                    } elseif ($atVerifier) {
                        $status = Status::REJECTED_MCD_VERIFIER;
                        $historyTitle = 'Rejected by the MCD Verifier';
                        $historyReq   = 'Rejected by MCD Verifier';
                    } else {
                        $status = Status::REJECTED_PLANNER;
                        $historyTitle = 'Rejected by the MCD Planner';
                        $historyReq   = 'Rejected by MCD Planner';
                    }
                    $message = "Request rejected.";
                }

                if ($atApprover)      $noteColumn = 'note_verifier';
                elseif ($atVerifier)  $noteColumn = 'note_mcd_verifier';
                else                  $noteColumn = 'note_planner';

                History::context($imf, [
                    'action'          => $action == "hold" ? ($atPlannerReview ? 'returned' : 'held') : 'cancelled',
                    'title'           => $historyTitle,
                    'requestor_title' => $historyReq,
                    'remarks'         => $remarks,
                ]);
                $imf->update(["status" => $status, $noteColumn => $remarks]);

                if ($action == "hold") {
                    if ($atApprover) {
                        // Returned to the MCD Planner for re-decision.
                        Notifier::toRoleName('MCD Planner', [
                            'title'   => 'IMF Returned by Planning Supervisor',
                            'message' => "IMF #{$imf->id} was held by the Planning Supervisor: {$remarks}",
                            'url'     => route('imf.requests.view', $imf->id),
                            'module'  => 'IMF',
                            'status'  => $status,
                        ]);
                        // Keep the requestor informed of the hold too.
                        Notifier::toUser($imf->user_id, [
                            'title'   => 'IMF On Hold',
                            'message' => "Your IMF #{$imf->id} was held by the Planning Supervisor and returned to the MCD Planner: {$remarks}",
                            'url'     => route('new-stock.show', $imf->id),
                            'module'  => 'IMF',
                            'status'  => $status,
                        ]);
                    } elseif ($atVerifier) {
                        Notifier::toRoleName('MCD Planner', [
                            'title'   => 'IMF Returned by MCD Verifier',
                            'message' => "IMF #{$imf->id} was held by the MCD Verifier: {$remarks}",
                            'url'     => route('imf.requests.view', $imf->id),
                            'module'  => 'IMF',
                            'status'  => $status,
                        ]);
                        Notifier::toUser($imf->user_id, [
                            'title'   => 'IMF On Hold',
                            'message' => "Your IMF #{$imf->id} was held by the MCD Verifier and returned to the MCD Planner: {$remarks}",
                            'url'     => route('new-stock.show', $imf->id),
                            'module'  => 'IMF',
                            'status'  => $status,
                        ]);
                    } elseif ($atPlannerStock) {
                        // Sent back to the Verifier to re-check the inventory code.
                        Notifier::toRoleName('MCD Verifier', [
                            'title'   => 'IMF Returned by MCD Planner',
                            'message' => "IMF #{$imf->id} was returned by the MCD Planner for re-check: {$remarks}",
                            'url'     => route('imf.requests.view', $imf->id),
                            'module'  => 'IMF',
                            'status'  => $status,
                        ]);
                    } else {
                        // Returned to the requestor for re-edit.
                        Notifier::toUser($imf->user_id, [
                            'title'   => 'IMF Returned for Revision',
                            'message' => "Your IMF #{$imf->id} was returned by the MCD Planner: {$remarks}",
                            'url'     => route('new-stock.show', $imf->id),
                            'module'  => 'IMF',
                            'status'  => $status,
                        ]);
                    }
                } else {
                    // Rejected — always inform the requestor.
                    Notifier::toUser($imf->user_id, [
                        'title'   => 'IMF Rejected',
                        'message' => "Your IMF #{$imf->id} was rejected: {$remarks}",
                        'url'     => route('new-stock.show', $imf->id),
                        'module'  => 'IMF',
                        'status'  => $status,
                    ]);
                }

                return redirect()->route('imf.requests')->with('success', $message);
            }

            return redirect()->route('imf.requests')->with('error', 'Unknown action.');
        }catch(\Exception $e){
            return redirect()->route('imf.requests')->with('error', $e->getMessage());
        }
    }

    public function download()
    {
        $filePath = storage_path('template/create-new-stock-import-template.xlsx');

        if (File::exists($filePath)) {
            header('Content-disposition: attachment; filename=create-new-stock-import-template.xlsx');
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            readfile($filePath);
            exit;
        }
        else {
            return response()->json(['message' => 'Oops! Something went wrong. File not found.']);
        }
    }

    public function downloadAttachedFiles(Request $request)
    {  
        $filePath = storage_path('app/' . $request->file);
        return response()->download($filePath, basename($filePath));
    }

    
    public function generateReport(Request $request) 
    {
        $InventoryRequestData = InventoryRequest::find($request->id);

        if (!$InventoryRequestData) {
            abort(404);
        }

        $items = $InventoryRequestData->items;
        $oldItems = InventoryRequestsOldItem::where('imf_no', $request->id)->get();
      
        $pdf = \PDF::loadHtml(view('admin.ecommerce.inventory.generate-report', compact('InventoryRequestData', 'items', 'oldItems')));
        $pdf->setPaper("A4", "landscape");
        $revSuffix = $InventoryRequestData->revision > 0 ? '-Rev'.$InventoryRequestData->revision : '';
        return $pdf->download('IMF-'.$InventoryRequestData->id.$revSuffix.'.pdf');
    }    
}
