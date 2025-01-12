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
use App\Helpers\ListingHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

            $requests = InventoryRequest::where("user_id", Auth::id())->get();
            return view('theme.pages.customer.new-stock.list', compact(['requests', 'page']));
        }
        else {
            return redirect()->route('customer-front.login');
        }
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
            $type = $request->input('type');
            $action = $request->input('action');

            $msg = "Request has been";

            if ($type === "new")
            {
                $new = InventoryRequest::create([
                    "priority" => $request->input("priority.0"),
                    "department" => $department, 
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
                        "priority" => $request->input("priority.0"),
                        "department" => $department, 
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
                        "priority" => $request->input("priority.0"),
                        "department" => $department, 
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
                $request->update([
                    'status' => ($status == "FULLY APPROVED") ? "APPROVED - WFS" : $status,
                    'approved_at' => $approved_at,
                    'approved_by' => $approved_by,
                ]);
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
            $query->where(function ($q) {
                $q->where('status', Status::APPROVED_WFS)
                ->orWhere('status', Status::APPROVED_MCD)
                ->orWhere('status', Status::VERIFIED);
            });
        }else{
            $query->where(function ($q) {
                $q->where('status', Status::APPROVED_MCD)
                ->orWhere('status', Status::VERIFIED);
            });
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

    public function imf_action(Request $request, $id) 
    {
        $user = User::find(Auth::id());
        $role = Role::where('id', $user->role_id)->first();
        try{

            $imf = InventoryRequest::find($id);

            if ($request->action == "approve")
            {    
                if ($request->type == "new") 
                {
                    if($role->name == "MCD Verifier"){
                        $items = InventoryRequestItems::where("imf_no", $id)->get();

                        foreach($items as $item)
                        {
                            $maxProductCode = DB::table('products')
                                ->select(DB::raw('MAX(CAST(NULLIF(\'0\' + code, \'0\') AS INT)) AS max_numeric_value'))
                                ->whereRaw('code NOT LIKE ?', ['%[a-zA-Z]%'])
                                ->value('max_numeric_value');
                            $newProductCode = $maxProductCode + 1;

                            $product = Product::create([
                                'category_id' => 29,
                                'description' => $item->item_description,
                                'brand' => $item->brand,
                                'oem' => $item->OEM_ID,
                                'uom' => $item->UoM ?? 'test',
                                'name' => $item->item_description,
                                'slug' => 'new-product',
                                'status' => 'PUBLISHED',
                                'created_by' => 1
                            ]);

                            $productId = $product->id;
                            $item->update(['product_id' => $productId]);
                        }
                            
                        $status = "VERIFIED - MCD (Verifier)";
                        $message = "Products inserted!";
                    }else{
                        $status = "APPROVED - MCD (Planner)";
                        $message = "Request Approved. Subject for verification!";
                    }
                }
                else 
                {
                    if($role->name == "MCD Verifier"){
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

                        $status = "VERIFIED - MCD (Verifier)";
                        $message = "Product updated!";
                    }else{
                        $status = "APPROVED - MCD (Planner)";
                        $message = "Request Approved. Subject for verification!";
                    }
                }
                
                $imf->update(["status" => $status, "approved_at" => now()]);
                return redirect()->route('imf.requests')->with('success', $message);

            } else {
                $status = ($request->action == "disapprove") ? "CANCELLED - MCD" : "HOLD";
                $message = ($request->action == "disapprove") ? "IMF disapproved and cancelled!" : "IMF put on hold!";
                
                $imf->update(["status" => $status]);
                return redirect()->route('imf.requests')->with('success', $message);
            }
        }catch(Exception $e){
            return redirect()->route('pa.index')->with('error', $e);
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
        return $pdf->download('IMF-'.$InventoryRequestData->id.'.pdf');
    }    
}
