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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NewStockRequest $request)
    {
        $attachments = array();
        $msg = "Request has been ";

        if ($request->attachments) {
            foreach ($request->attachments as $photo) {
                $filename = $photo->store('photos');

                $attachments[] = $filename;
            }
        }

        $data = [
            'stock_code' => $request->stock_code,
            'item_description' => $request->item_description,
            'brand' => $request->brand,
            'OEM_ID' => $request->OEM_ID,
            'UoM' => $request->UoM,
            'usage_rate_qty' => $request->usage_rate_qty,
            'usage_frequency' => $request->usage_frequency,
            'purpose' => $request->purpose,
            'min_qty' => $request->min_qty,
            'max_qty' => $request->max_qty,
            'attachments' => json_encode($attachments),
            'department' => $request->department,
            'status' => 'SAVED',
            'type' => $request->type
        ];

        if ($request->type == "update") {
            $exist = Product::where('code', $request->stock_code)->first();
            
            if (!$exist) return redirect()->back()->with('error', 'Oops! Product code does not match in our database');
        }

        $new = InventoryRequest::create( $data );
        if (!$new) return redirect()->back()->with('error', 'Oops! something went wrong.');

        if ($request->action && $request->action == 'save_and_submit') {
            $output = $this->submission($new->id, $new->type);
            if ($output) $msg .= "submitted and ";
        }

        return redirect()->back()->with('success', $msg . 'saved!');
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
        $page = new Page;
        $page->name = $request->name;

        return view('theme.pages.customer.new-stock.show', compact(['request', 'page']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $attachments = array();

        if ($request->attachments) {
            foreach($request->attachments as $attachment) {
                $attachments[] = $attachment->getClientOriginalName();       
            }
        }

        $attachments = implode(", ", $attachments);
        
        $data = [
            "department" => $request->department,
            "stock_code" => $request->stock_code,
            "item_description" => $request->item_description,
            "brand" => $request->brand,
            "OEM_ID" => $request->OEM_ID,
            "UoM" => $request->UoM,
            "usage_rate_qty" => $request->usage_rate_qty,
            "usage_frequency" => $request->usage_frequency,
            "purpose" => $request->purpose,
            "min_qty" => $request->min_qty,
            "max_qty" => $request->max_qty,
            "attachments" => $attachments
        ];

        $update = InventoryRequest::find($id)->update($data);

        if ($update) {
            return redirect()->back()->with('success', 'New request has been updated.');
        }
        else {
            return redirect()->back()->with('error', 'Oops! something went wrong.');
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
            "transid" => 'ECOM-' . uniqid(),
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
                }
            }
        }
    }
}
