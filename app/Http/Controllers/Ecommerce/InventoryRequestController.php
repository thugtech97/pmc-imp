<?php

namespace App\Http\Controllers\Ecommerce;

use Auth;
use DateTime;
use App\Models\Page;
use Illuminate\Http\Request;
use App\Models\Ecommerce\Product;
use App\Models\AllowedTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewStockRequest;
use App\Models\Ecommerce\InventoryRequest;
use App\Models\Ecommerce\InventoryRequestItems;

class InventoryRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::check()) {
            $page = new Page;
            $page->name = 'Inventory Maintenance Form';

            $requests = InventoryRequest::all();

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

            $items = $request->except(['_token', 'department', 'type']);
            $new = InventoryRequest::create(["department" => $department, "type" => $type, "status" => $action, "user_id" => Auth::id()]);
            
            if($type === "new"){
                $itemCount = count($request->input('stock_code'));
                for ($i = 0; $i < $itemCount; $i++) {
                    InventoryRequestItems::create([
                        "stock_code" => $request->input("stock_code.$i"),
                        "item_description" => $request->input("item_description.$i"),
                        "brand" => $request->input("brand.$i"),
                        "OEM_ID" => $request->input("OEM_ID.$i"),
                        "UoM" => $request->input("UoM.$i"),
                        "usage_rate_qty" => $request->input("usage_rate_qty.$i"),
                        "usage_frequency" => $request->input("usage_frequency.$i"),
                        "purpose" => $request->input("purpose.$i"),
                        "min_qty" => $request->input("min_qty.$i"),
                        "max_qty" => $request->input("max_qty.$i"),
                        "imf_no" => $new->id,
                    ]);
                }
            }else{
                InventoryRequestItems::create([
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
            }
            
            $response = [
                'status' => 'success',
                'message' => $msg . ' saved!',
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $request = InventoryRequest::find($id);

        if (!$request) {
            abort(404); // Handle the case where the request is not found
        }
        $items = $request->items;

        $page = new Page;
        $page->name = 'Inventory Maintenance Form (IMF) - View Request';;

        return view('theme.pages.customer.new-stock.show', compact(['request', 'items', 'page']));
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        //return $request->all();
        
        try{
            $msg = "Request has been";
            $type = $request->input('type');
            if($type === "new"){
                InventoryRequestItems::where("imf_no", $id)->delete();
                $itemCount = count($request->input('stock_code'));
                for ($i = 0; $i < $itemCount; $i++) {
                    InventoryRequestItems::create([
                        "stock_code" => $request->input("stock_code.$i"),
                        "item_description" => $request->input("item_description.$i"),
                        "brand" => $request->input("brand.$i"),
                        "OEM_ID" => $request->input("OEM_ID.$i"),
                        "UoM" => $request->input("UoM.$i"),
                        "usage_rate_qty" => $request->input("usage_rate_qty.$i"),
                        "usage_frequency" => $request->input("usage_frequency.$i"),
                        "purpose" => $request->input("purpose.$i"),
                        "min_qty" => $request->input("min_qty.$i"),
                        "max_qty" => $request->input("max_qty.$i"),
                        "imf_no" => $id,
                    ]);
                }
            }else{
                $items = InventoryRequestItems::where("imf_no", $id);
                $items->update([
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
                'status' => 'DRAFT',
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
            return response()->json(['success' => 'Request has been submitted.']);
        }
        else {
            return response()->json(['error' => 'Oops! Something went wrong.']);
        }
    }

    public function submission($id, $type) {
        
        $product = InventoryRequest::find($id);

        if (!$product) {
            return false;
        }

        $requestor = auth()->user();
        $data = [
            "type" => config('app.name'),
            "transid" => 'IMP-IMF-' . uniqid(),
            "token" => config('app.key'),
            "refno" => $id,
            "sourceapp" => 'IMP-MRS-PA',
            "sourceurl" => route('new-stock.show', $id),
            "requestor" => $requestor->name,
            "department" => 'INFORMATION AND COMMUNICATIONS TECHNOLOGY',
            "email" => $requestor->email,
            "purpose" => $product->purpose,
            "name" => $requestor->name,
            "template_id" => config('app.template_id'),
            "locsite" => ""
        ];

        define('__ROOT__', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $result = require(__ROOT__ . '\api\wfs-api.php');

        if ($result) {
            $product->update([
                'status' => 'POSTED',
                'submitted_at' => now()
            ]);
            return true;
        }

        return false;
    }

    public function updateRequestApproval(){
        $imfs = InventoryRequest::where('status', 'POSTED')->get();
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
            if ($status != "PENDING") {
                $request = InventoryRequest::find($ref_req_no);
                $request->update([
                    'status' => ($status == "FULLY APPROVED") ? "APPROVED" : $status,
                    'approved_at' => $approved_at,
                    'approved_by' => $approved_by,
                ]);
                if ($request->type != "update") {
                    $maxProductCode = DB::table('products')
                        ->select(DB::raw('MAX(CAST(NULLIF(\'0\' + code, \'0\') AS INT)) AS max_numeric_value'))
                        ->whereRaw('code NOT LIKE ?', ['%[a-zA-Z]%'])
                        ->value('max_numeric_value');
                    $newProductCode = $maxProductCode + 1;
                    $product = Product::create([
                        'code' => $newProductCode,
                        'category_id' => 29,
                        'description' => $request->item_description,
                        'brand' => $request->brand,
                        'oem' => $request->OEM_ID,
                        'uom' => $request->UoM ?? 'test',
                        'name' => $request->item_description,
                        'slug' => 'new-product',
                        'status' => 'DRAFT',
                        'created_by' => 1
                    ]);
                    $request->update(['stock_code' => $newProductCode]);
                }else{

                }
            }
        }
    }
}
