<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use App\Helpers\ListingHelper;


use App\Models\Ecommerce\{
    ProductCategory, DeliveryStatus, SalesPayment, SalesDetail, SalesHeader, CouponSale, Product, InventoryRequest, PurchaseAdvice
};

use App\Models\User;
use App\Models\Issuance;
use App\Models\Department;

use Auth;
use DB;
use PDF;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportsController extends Controller
{

    public function inventory_reorder_point(Request $request)
    {
        
        $rs = Product::where('reorder_point','>',0)->get();
        

        return view('admin.ecommerce.reports.inventory.inventory_reorder_point',compact('rs'));

    }

    public function inventory_list(Request $request)
    {
        
        $rs = Product::all();
        

        return view('admin.reports.inventory.list',compact('rs'));

    }

    public function sales_list(Request $request)
    {
        
        $qry = "SELECT h.*,d.*,h.created_at as hcreated,h.id as hid,p.category_id,c.name as catname,p.brand,p.code,pay.payment_date as pdate,p.brand FROM `ecommerce_sales_details` d 
            left join ecommerce_sales_headers h on h.id=d.sales_header_id 
            left join ecommerce_sales_payments pay on pay.sales_header_id=d.sales_header_id
            left join products p on p.id=d.product_id 
            left join product_categories c on c.id=p.category_id
            where h.id>0 and h.status<>'CANCELLED' and h.delivery_status<>'CANCELLED'
            ";
       
        if(isset($_GET['customer']) && $_GET['customer']<>''){
            $qry.= " and h.customer_name='".$_GET['customer']."'";
        }

        if(isset($_GET['product']) && $_GET['product']<>''){
            $qry.= " and d.product_id='".$_GET['product']."'";
        }

        if(isset($_GET['category']) && $_GET['category']<>''){
            $qry.= " and p.category_id='".$_GET['category']."'";
        }

        if(isset($_GET['payment_status']) && $_GET['payment_status']<>''){
            $qry.= " and h.payment_status='".$_GET['payment_status']."'";
        }

        if(isset($_GET['del_status']) && $_GET['del_status']<>''){
            $qry.= " and h.delivery_status='".$_GET['del_status']."'";
        }
      
        if(isset($_GET['start']) && strlen($_GET['start'])>=1){
             $qry.= " and h.created_at >='".$_GET['start']." 00:00:00.000' and h.created_at <='".$_GET['end']." 23:59:59.999'";
            
        }


        $rs = DB::select($qry. " ORDER BY h.id desc");

        return view('admin.ecommerce.reports.sales-transaction',compact('rs'));

    }



    public function delivery_report($id)
    {
        $rs = SalesHeader::whereId($id)->first();
        
        return view('admin.ecommerce.reports.delivery_report',compact('rs'));

    }

    public function delivery_status(Request $request)
    {
        $rs = '';

            $rs = DB::select("SELECT h.*,d.*,h.created_at as hcreated           
                    FROM `ecommerce_sales_details` d 
                    left join ecommerce_sales_headers h on h.id=d.sales_header_id 
                    where h.payment_status='PAID'
                     ");
        return view('admin.reports.delivery_status',compact('rs'));

    }



    public function stock_card(Product $id)
    {
     
        $rs = '';   

        $sales = \DB::table('ecommerce_sales_details')
                ->leftJoin('ecommerce_sales_headers', 'ecommerce_sales_details.sales_header_id', '=', 'ecommerce_sales_headers.id')
                ->where('ecommerce_sales_details.product_id','=',$id->id)
                ->where('ecommerce_sales_headers.payment_status','=','PAID')
                ->where('ecommerce_sales_headers.status','=','active')
                ->select('ecommerce_sales_headers.created_at as created', 'ecommerce_sales_details.qty as qty','ecommerce_sales_headers.order_number as ref',
                    \DB::raw('"sales" as type'))
                ->get();

        $inventory = \DB::table('inventory_receiver_details')
                ->leftJoin('inventory_receiver_header', 'inventory_receiver_details.header_id', '=', 'inventory_receiver_header.id')
                ->where('inventory_receiver_details.product_id','=',$id->id)
                ->where('inventory_receiver_header.status','=','POSTED')
                ->select('inventory_receiver_header.posted_at as created', 'inventory_receiver_details.inventory as qty','inventory_receiver_header.id as ref',
                    \DB::raw('"inventory" as type'))
                ->get();

        $rs = $sales->merge($inventory)->sortBy('created');
       
        return view('admin.reports.product.stockcard',compact('rs','id'));
    }

    public function coupon_list(Request $request)
    {
        $qry = "SELECT h.*,c.*, cs.coupon_code, cs.customer_id FROM `coupon_sales` cs 
            left join ecommerce_sales_headers h on h.id = cs.sales_header_id 
            left join coupons c on c.id = cs.coupon_id
            where cs.id > 0";

       
        if(isset($_GET['coupon_code']) && $_GET['coupon_code']<>''){
            $qry.= " and cs.coupon_code = '".$_GET['coupon_code']."' ";
        }

        if(isset($_GET['customer']) && strlen($_GET['customer'])>=1){
            $qry.= " and cs.customer_id = '".$_GET['customer']."' ";
        }

        if(isset($_GET['start']) && strlen($_GET['start'])>=1){
            $qry.= " and h.created_at >='".$_GET['start']."' and h.created_at <='".$_GET['end']."'";
        }
   
      
        $rs = DB::select($qry);

        return view('admin.reports.coupon.list',compact('rs'));
    }

    public function issuances(Request $request)
    {
        $departments = Department::all();
        $products = Product::all();
        $categories = ProductCategory::all();
        $query = Issuance::with("items");
        
        if ($request->filter_department) {
            $department = $request->filter_department;

            $query->whereHas('orderDetails.user.department', function($query) use ($department) {
                return $query->whereIn('id', $department);
            });
        }
        if ($request->filter_daterange) {
            $dates = explode(" - ", $request->filter_daterange);
            $from = date('Y-m-d', strtotime($dates[0]));
            $to = date('Y-m-d', strtotime($dates[1]));

            $query->whereBetween('release_date', [$from, $to]);
        }
        if ($request->filter_category) {
            $category = $request->filter_category;

            $query->whereHas('orderDetails.category', function($query) use ($category) {
                return $query->whereIn('id', $category);
            });
        }
        if ($request->filter) {
            $filter = $request->filter;

            $query->whereHas('orderDetails', function($query) use ($filter) {
                return $query->where('product_name', 'LIKE', '%' . $filter . '%');
            });
        }

        $rs = $query->get();

        return view('admin.ecommerce.reports.issuance', compact('rs', 'departments', 'products', 'categories'));
    }

    public function mrs(Request $request)
    {
        $departments = Department::all();
        $products = Product::all();
        $query = SalesHeader::with(array("items", "user"))->withSum('issuances', 'qty');
        
        if ($request->filter_department) {
            $department = $request->filter_department;

            $query->whereHas('user.department', function($query) use ($department) {
                return $query->whereIn('id', $department);
            });
        }
        if ($request->filter_daterange) {
            $dates = explode(" - ", $request->filter_daterange);
            $from = date('Y-m-d', strtotime($dates[0]));
            $to = date('Y-m-d', strtotime($dates[1]));

            $query->whereBetween('created_at', [$from, $to]);
        }
        if ($request->filter_category) {
            $category = $request->filter_category;

            $query->whereIn('status', $category);
        }
        if ($request->filter) {
            $filter = $request->filter;

            $query->where('order_number', 'LIKE', '%' . $filter . '%');
        }

        $rs = $query->get();

        return view('admin.ecommerce.reports.mrs', compact('rs', 'departments', 'products'));
    }

    public function fastMovingItems(Request $request)
    {
        $departments = Department::all();
        $products = Product::all();
        $query = Product::with(['orders' => function($query) {
            return $query->whereBetween('created_at', array(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()))
            ->selectRaw('SUM(qty) / COUNT(DISTINCT created_at) AS order_average');
        }])->paginate(20);
        /*$query = Product::join('ecommerce_sales_details as orders', 'products.id', '=', 'orders.product_id')
        ->whereBetween('orders.created_at', array("2022-12-01", Carbon::now()->endOfMonth()))
        ->selectRaw('SUM(orders.qty) / COUNT(DISTINCT orders.created_at) AS order_average')
        ->paginate(20);*/
        /*$query = Product::with(["releasedOrders" => function($query) {
            return $query->whereBetween('issuances.created_at', array("2022-12-01", Carbon::now()->endOfMonth()))
            ->selectRaw('SUM(issuances.qty) / COUNT(DISTINCT issuances.created_at) AS order_average');
        }])->take(20);*/
        dd($query);
        
        if ($request->filter_department) {
            $department = $request->filter_department;

            $query->whereHas('user.department', function($query) use ($department) {
                return $query->whereIn('id', $department);
            });
        }
        if ($request->filter_daterange) {
            $dates = explode(" - ", $request->filter_daterange);
            $from = date('Y-m-d', strtotime($dates[0]));
            $to = date('Y-m-d', strtotime($dates[1]));

            $query->whereBetween('created_at', [$from, $to]);
        }
        if ($request->filter_category) {
            $category = $request->filter_category;

            $query->whereIn('status', $category);
        }
        if ($request->filter) {
            $filter = $request->filter;

            $query->where('order_number', 'LIKE', '%' . $filter . '%');
        }

        $rs = $query->get();

        return view('admin.ecommerce.reports.fast-moving-items', compact('rs', 'departments', 'products'));
    }


    public function generate_mrs_transactions(Request $request){

        $headers = SalesHeader::where('created_at', '>=', $request->startdate)->where('created_at', '<=', $request->enddate . ' 23:59:59')->get();

        $pdf = \PDF::loadHtml(view('admin.ecommerce.reports.components.generate-mrs-report', compact('headers', 'request')));
        $pdf->setPaper("legal", "landscape");
        
        return $pdf->download('MRS('.$request->startdate.'_to_'.$request->enddate.').pdf');
    

        //return response()->json(["message"=>"yeah"], 200);
    }

    public function generate_imf_transactions(Request $request){

        $headers = InventoryRequest::with('items')->where('created_at', '>=', $request->startdate)->where('created_at', '<=', $request->enddate . ' 23:59:59')->get();


        $pdf = \PDF::loadHtml(view('admin.ecommerce.reports.components.generate-imf-report', compact('headers', 'request')));
        $pdf->setPaper("legal", "portrait");
        
        return $pdf->download('IMF('.$request->startdate.'_to_'.$request->enddate.').pdf');

    }

    public function generate_pa_transactions(Request $request){

        $headers = PurchaseAdvice::whereNull('mrs_id')->where('created_at', '>=', $request->startdate)->where('created_at', '<=', $request->enddate . ' 23:59:59')->get();

        $pdf = \PDF::loadHtml(view('admin.ecommerce.reports.components.generate-pa-report', compact('headers', 'request')));
        $pdf->setPaper("legal", "landscape");
        
        return $pdf->download('PA('.$request->startdate.'_to_'.$request->enddate.').pdf');
    
    }

    public function exportMRS(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'MRS No.');
        $sheet->setCellValue('B1', 'PA No.');
        $sheet->setCellValue('C1', 'Posted Date');
        $sheet->setCellValue('D1', 'Department');
        $sheet->setCellValue('E1', 'Purchaser Date Received');
        $sheet->setCellValue('F1', 'Aging');
        $sheet->setCellValue('G1', 'Total Balance');
        $sheet->setCellValue('H1', 'Status');

        $sheet->setCellValue('I1', 'Stock Code');
        $sheet->setCellValue('J1', 'Stock Description');
        $sheet->setCellValue('K1', 'OEM ID');
        $sheet->setCellValue('L1', 'UoM');
        $sheet->setCellValue('M1', 'Frequency');
        $sheet->setCellValue('N1', 'PAR To');
        $sheet->setCellValue('O1', 'Previous PO#');
        $sheet->setCellValue('P1', 'Current PO#');
        $sheet->setCellValue('Q1', 'PO Date Released');
        $sheet->setCellValue('R1', 'QTY Ordered');
        $sheet->setCellValue('S1', 'Balance QTY for PO');
        
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // White text
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'], // Black background
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        $type = $request->query('type');
        $mrss = SalesHeader::when($type, function ($query) {
            return $query->where('user_id', Auth::id());
        })
        ->orderBy('created_at', 'DESC')
        ->get();        

        $row = 2;
        $colors = ['#d9d9d9', '#b3d9ff'];
        $colorIndex = 0;

        foreach ($mrss as $mrs) {
            $items = $mrs->items->where('promo_id', '!=', 1);
            $bal = $items->sum('qty_to_order') - $items->sum('qty_ordered');
            $receivedAt = Carbon::parse($mrs->received_at);
            $now = Carbon::now();
            $days = $receivedAt->diffInDays($now);
            $hours = $receivedAt->copy()->addDays($days)->diffInHours($now);

            $timeString = '';
            if ($days > 0) {
                $timeString = $days . ' day' . ($days > 1 ? 's' : '');
            } elseif ($hours > 0) {
                $timeString = $hours . ' hour' . ($hours > 1 ? 's' : '');
            } else {
                $timeString = '0 hours';
            }

            foreach ($mrs->items as $item) {
                // Set alternating background color
                $fillColor = $colors[$colorIndex % 2];

                // Apply styles to the row
                $sheet->getStyle("A$row:S$row")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => ltrim($fillColor, '#')],
                    ],
                ]);

                // Set cell values
                $sheet->setCellValue('A' . $row, $mrs->order_number);
                $sheet->setCellValue('B' . $row, $mrs->purchaseAdvice->pa_number ?? "N/A");
                $sheet->setCellValue('C' . $row, Carbon::parse($mrs->created_at)->format('m/d/Y'));
                $sheet->setCellValue('D' . $row, $mrs->user->department->name);
                $sheet->setCellValue('E' . $row, $mrs->received_at ? Carbon::parse($mrs->received_at)->format('m/d/Y') : 'N/A');
                $sheet->setCellValue('F' . $row, $timeString);
                $sheet->setCellValue('G' . $row, $mrs->received_at ? $bal : 'N/A');
                $sheet->setCellValue('H' . $row, strtoupper($mrs->status));

                $sheet->setCellValue('I' . $row, $item->product->code);
                $sheet->setCellValue('J' . $row, $item->product->name);
                $sheet->setCellValue('K' . $row, $item->product->oem);
                $sheet->setCellValue('L' . $row, $item->product->uom);
                $sheet->setCellValue('M' . $row, $item->frequency);
                $sheet->setCellValue('N' . $row, $item->par_to);
                $sheet->setCellValue('O' . $row, $item->previous_mrs);
                $sheet->setCellValue('P' . $row, $item->po_no);
                $sheet->setCellValue('Q' . $row, $item->po_date_released ? Carbon::parse($item->po_date_released)->format('m/d/Y') : "");
                $sheet->setCellValue('R' . $row, $item->qty_ordered);
                $sheet->setCellValue('S' . $row, ((int)$item->qty_to_order - (int)$item->qty_ordered));

                $row++;
            }

            // Alternate the color index after processing each MRS record
            $colorIndex++;
        }
        

        $fileName = 'IMP-MRS.xlsx';
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path($fileName);
        $writer->save($filePath);
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function exportPA(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'MRS No.');
        $sheet->setCellValue('B1', 'PA No.');
        $sheet->setCellValue('C1', 'Posted Date');
        $sheet->setCellValue('D1', 'Department');
        $sheet->setCellValue('E1', 'Purchasing Date Received');
        $sheet->setCellValue('F1', 'Aging');
        $sheet->setCellValue('G1', 'Total Balance');
        $sheet->setCellValue('H1', 'Status');
        $sheet->setCellValue('I1', 'Purchaser');
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sales = SalesHeader::with('items.issuances')->withSum('issuances', 'qty')->where('id', '>', '0')->whereIn('status', ['RECEIVED FOR CANVASS (Purchasing Officer)'])->where('for_pa', 1)->where('is_pa', 1)->orderBy('id', 'desc')->get();   

        $row = 2;
        $colors = ['#d9d9d9', '#b3d9ff'];
        $colorIndex = 0;

        foreach ($sales as $sale) {
            $bal = $sale->items->where('promo_id', '!=', 1)->sum('qty_to_order') - $sale->items->where('promo_id', '!=', 1)->sum('qty_ordered');
            $output = "";
            if ($sale->received_at) {
                if ($bal == 0) {
                    $output .= "✔️";
                } else {
                    $receivedAt = Carbon::parse($sale->received_at);
                    $now = Carbon::now();
                    $days = $receivedAt->diffInDays($now);
                    $hours = $receivedAt->copy()->addDays($days)->diffInHours($now);
        
                    $color = ($days >= 14) ? 'red' : 'blue';
                    $timeText = '';
        
                    if ($days > 0) {
                        $timeText .= $days . ' day' . ($days > 1 ? 's' : '');
                    } elseif ($days == 0) {
                        $timeText .= $hours . ' hour' . ($hours > 1 ? 's' : '');
                    }
        
                    $output .= $timeText;
                }
            } else {
                $output .= 'N/A';
            }

            $fillColor = $colors[$colorIndex % 2];
            $sheet->getStyle("A$row:I$row")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => ltrim($fillColor, '#')],
                ],
            ]);
            $sheet->setCellValue('A' . $row, $sale->order_number);
            $sheet->setCellValue('B' . $row, $sale->purchaseAdvice->pa_number ?? "N/A");
            $sheet->setCellValue('C' . $row, Carbon::parse($sale->created_at)->format('m/d/Y'));
            $sheet->setCellValue('D' . $row, $sale->user->department->name ?? "N/A");
            $sheet->setCellValue('E' . $row, $sale->received_at ? Carbon::parse($sale->received_at)->format('m/d/Y') : 'N/A');
            $sheet->setCellValue('F' . $row, $output);
            $sheet->setCellValue('G' . $row, $sale->received_at ? $bal : 'N/A');
            $sheet->setCellValue('H' . $row, strtoupper($sale->status));
            $sheet->setCellValue('I' . $row, $sale->purchaser ? $sale->purchaser->name : 'N/A');
            $row++;
        }
        

        $fileName = 'IMP-PA-DATA.xlsx';
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path($fileName);
        $writer->save($filePath);
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
