<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ecommerce\{
    SalesHeader, Product
};
use App\Models\{
    Issuance, Department
};
use App\Helpers\ListingHelper;

class IssuanceController extends Controller
{
    private $searchFields = ['order_number','response_code','created_at', 'updated_at'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
        $issuances = $listing->simple_search(Issuance::class, array("issuance_no", "created_at", "updated_at"));
        $issuances = Issuance::with('orderDetails.header')->orderBy('issuance_no', 'DESC');
        if(isset($_GET['startdate']) && $_GET['startdate']<>'')
            $issuances = $issuances->where('created_at','>=',$_GET['startdate']);
        if(isset($_GET['enddate']) && $_GET['enddate']<>'')
            $issuances = $issuances->where('created_at','<=',$_GET['enddate'].' 23:59:59');
        if(isset($_GET['search']) && $_GET['search']<>'')
            $issuances = $issuances->where('issuance_no','like','%'.$_GET['search'].'%');
        if(isset($_GET['customer_filter']) && $_GET['customer_filter']<>'')
            $issuances = $issuances->with('orderDetails.header.user.department', function($query) {
                return $query->where('name','like','%'.$_GET['customer_filter'].'%');
            });
        $issuances = $issuances->distinct()->select(
            'issuance_no', 
            'release_date', 
            'received_by', 
            'issued_by'
        )->paginate(10);

        $departments = Department::all();

        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        return view('admin.ecommerce.issuances.index', compact('issuances', 'filter', 'searchType', 'departments'));
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
    public function store(Request $request)
    {
        $header_id = $request->sales_header_id;
        $h = SalesHeader::find($header_id);
  
        foreach ($h->items as $i) {
            if ($i->product->inventory <= 0) {
                return back()->with("error", "Unable to issue if out of stock.");
            }
            else {
                $input_qty = $request->input('deploy'.$i->id);

                if ($input_qty != 0) {
                    $issued = Issuance::create([
                        "ecommerce_sales_details_id" => $request->input('ecommerce_sales_details_id'.$i->id),
                        "user_id" => auth()->user()->id,
                        "issuance_no" => $request->issuance_no,
                        "release_date" => date('Y-m-d'),
                        "received_by" => $request->received_by ?? '',
                        "qty" => $input_qty,
                        "issued_by" => $request->issued_by ?? ''
                    ]);

                    $ordered_qty = intval($request->input('ordered_qty'.$i->id));
                    $total_issued = intval($request->input('total_issued'.$i->id));

                    if ($input_qty > $ordered_qty || ($total_issued + $input_qty) > $ordered_qty) {
                        return back()->with("error", "Issuance quantity should not be more than the ordered quantity.");
                    }

                    if (($total_issued + $input_qty) == $ordered_qty) {
                        SalesHeader::find($header_id)->update(["status" => "COMPLETED"]);
                    }
                    else {
                        SalesHeader::find($header_id)->update(["status" => "PARTIAL"]);
                    }

                    $product = Product::find(intval($request->input('product_id'.$i->id)));
                    $product->inventory = $product->inventory - $input_qty;
                    $product->save();
                }
            }
        }

        return back()->with("success", "Issuance has been submitted.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $issuance = Issuance::where('issuance_no',$id)->get();
    

        return view('admin.ecommerce.issuances.edit', compact('issuance'));
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
        $issuance = Issuance::where('issuance_no',$id)->get();

  
        foreach($issuance as $is){
            $is->update([
                'qty' => $request->input('deploy'.$is->issuance_no.$is->orderDetails->id),
                'release_date' => $request->input('release_date'),
                'received_by' => $request->input('received_by'),
                'issued_by' => $request->input('issued_by')
            ]);
            
        }

        return back()->with('success','Successfully updated the issuance no: '.$id);
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
}
