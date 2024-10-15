<?php

namespace App\Http\Controllers\Ecommerce;


use App\Mail\SalesCompleted;
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Helpers\Setting;


// Models
use App\Models\Ecommerce\{
    Cart, Product, Deliverablecities, SalesPayment, SalesHeader, SalesDetail, CouponCartDiscount, CustomerCoupon, CouponCart, CouponSale, Coupon, PaymentOption
};


use App\Models\PaynamicsLog;
use App\Models\{
    Page, User, Announcement
};


use Carbon\Carbon;
use Redirect;
use Storage;
use DateTime;
use Session;
use Auth;
use DB;


class CartController extends Controller
{
    public function add_to_cart(Request $request)
    {
        $product = Product::whereId($request->product_id)->first();

        $promo = DB::table('promos')->join('promo_products','promos.id','=','promo_products.promo_id')->where('promos.status','ACTIVE')->where('promos.is_expire',0)->where('promo_products.product_id',$request->product_id);

        if($promo->count() > 0){
            $discount = $promo->max('promos.discount');

            $percentage = ($discount/100);
            $discountedAmount = ($product->price * $percentage);

            $price = number_format(($product->price - $discountedAmount),2,'.','');
        } else {
            $price = number_format($product->price,2,'.','');
        }

        if (auth()->check()) {
            
            $cart = Cart::where('user_id', Auth::id())
                ->count();
            
            if($cart < 10){
                $save = Cart::create([
                    'product_id' => $request->product_id,
                    'user_id' => Auth::id(),
                    'qty' => $request->qty,
                    'price' => $price
                ]);
                return response()->json([
                    'success' => true,
                    'totalItems' => Setting::EcommerceCartTotalItems()
                ]);
            }else{
                return response()->json([
                    'success' => false
                ]);
            }
        }
    }

    public function cart()
    {
        if (auth()->check()) {

            // reset coupon carts of customer
            CouponCartDiscount::where('customer_id',Auth::id())->delete();
            CouponCart::where('customer_id',Auth::id())->delete();

            $cart = Cart::where('user_id',Auth::id())->with('product')->get();
            $totalProducts = $cart->count();
            
            $existing_order = SalesHeader::where([
                "user_id" => Auth::id(),
                "status" => "SAVED"
            ])->first();
        } else {
            $cart = session('cart', []);
            $totalProducts = count(session('cart', []));
        }

        $page = new Page();
        $page->name = 'Cart';

        return view('theme.pages.ecommerce.cart', compact('cart', 'totalProducts','page', 'existing_order'));
    }

    public function remove_product(Request $request)
    {
        if (auth()->check()) {
            Cart::whereId($request->order_id)->delete();
        } else {
            $cart = session('cart', []);
            $index = (int) $request->order_id;
            if (isset($cart[$index])) {
                unset($cart[$index]);
            }
            session(['cart' => $cart]);
        }

        return back()->with('cart_success', 'Product has been removed.');
    }

    public function cart_update(Request $request)
    {
        if (auth()->check()) {
            if (Cart::where('user_id', auth()->id())->count() == 0) {
                return;
            }

            $qry = Cart::find($request->orderID);

            $qry->update([
                'qty' => $request->quantity
            ]);

            $cart_qty = $qry->first();

            $price_before = $cart_qty->product->price*$cart_qty->qty;


            $carts = Cart::where('user_id', auth()->id())->get();
            $total_promo_discount = 0;
            $subtotal = 0;

            foreach($carts as $cart){

                $promo_discount = $cart->product->price-$cart->product->discountedprice;
                $total_promo_discount += $promo_discount*$cart->qty;
                $subtotal += $cart->product->price*$cart->qty;
            }
        } else {
            $cart = session('cart', []);
                foreach ($cart as $key => $order) {
                    if ($order->product_id == $request->orderID) {
                        $cart[$key]->qty = $request->quantity;
                        break;
                    }
                }
            session(['cart' => $cart]);
        }

        return response()->json([
            'success' => true,
            // 'total_promo_discount' => $total_promo_discount,
            // 'subtotal' => $subtotal,
            // 'recordid' => $request->orderID,
            // 'price_before' => $price_before
        ]);
    }

    public function proceed_checkout(Request $request)
    {
        if(auth()->check()){

            if (Cart::where('user_id', auth()->id())->count() == 0) {
                return redirect()->route('product.list');
            }

            if(Auth::user()->role_id <> 6){
                abort(403, 'Administrator accounts are not authorized to create MRS Requests.');
            }

            $data   = $request->all();
            $cartId = $data['cart_id'];
            $qty    = $data['quantity'];
            $price  = $data['product_price'] ?? 0;

            foreach($cartId as $key => $cart) {
                $cartItem = Cart::find($cart);
                if ($cartItem) {
                    $cartItem->update([
                        'qty' => $qty[$key],
                        'price' => $price[$key] ?? 0
                    ]);
                }
            }

            /*
            if($request->coupon_counter > 0){
                $data     = $request->all();
                $coupons  = $data['couponid'];
                $product  = $data['coupon_productid'];
                $usage    = $data['couponUsage'];
                $discount = $data['discount'];

                foreach($coupons as $key => $c){
                    $coupon = Coupon::find($c);

                    if($coupon->status == 'ACTIVE'){
                        CouponCart::create([
                            'customer_id' => Auth::id(),
                            'product_id' => $product[$key] == 0 ? 0 : $product[$key],
                            'coupon_id' => $coupon->id,
                            'total_usage' => $usage[$key],
                            'discount' => $discount[$key]
                        ]);
                    }
                }
            }

            CouponCartDiscount::create([
                'customer_id' => Auth::id(),
                'coupon_discount' => $request->coupon_total_discount
            ]);
            */

            return redirect()->route('cart.front.checkout');
        } else {
            return redirect()->route('customer-front.login');
        }
    }

    public function checkout()
    {
        $page = new Page();
        $page->name = 'Checkout';

        $customer  = User::find(Auth::id());
        $orders    = Cart::where('user_id',Auth::id())->get();
        $locations = Deliverablecities::where('status','PUBLISHED')->orderBy('name')->get();
        $cart      = CouponCartDiscount::where('customer_id',Auth::id())->first();

        $coupons   = CouponCart::where('customer_id', Auth::id())->get();
        $announcements = Announcement::where([
            'location' => 'upon-ordering',
            'status' => 'active'
        ])->get();
        $mrs = SalesHeader::where([
            "user_id" => Auth::id(),
            "status" => "SAVED"
        ])->first();
        $sections = SalesHeader::distinct()->pluck('section');
        return view('theme.pages.ecommerce.checkout', compact('orders','locations', 'cart', 'coupons', 'customer', 'page', 'announcements', 'mrs', 'sections'));
    }

    public function next_order_number(){
        $last_order = SalesHeader::whereDate('created_at', Carbon::today())->orderBy('created_at','desc')->first();
        if(empty($last_order)){
            $next_number = date('Ymd')."-0001";
        }
        else{
            $order_number = explode("-",$last_order->order_number);
            if(!isset($order_number[1])){
                $next_number = date('Ymd')."-0001";
            }
            else{

                $next_number = date('Ymd')."-".str_pad(($order_number[1] + 1), 4, '0', STR_PAD_LEFT);
            }
        }
        return $next_number;
    }

    public function save_sales(Request $request)
    {
        $total_cart_items = Cart::where('user_id',Auth::id())->count();

        $customer_name = Auth::user()->fullName;
        $customer_contact_number =  $request->customer_contact_number ?? Auth::user()->mobile;

        $coupon_total_discount = number_format($request->coupon_total_discount,2,'.','');

        $totalPrice  = number_format($request->total_amount,2,'.','');
        $deliveryFee = number_format($request->delivery_fee,2,'.','');
        $orderNumber = $this->next_order_number();

        $requestData = $request->all();
        $requestData['user_id'] = Auth::id();
        $requestData['order_number'] = $orderNumber;
        $requestData['customer_name'] = auth()->user()->department->name;
        $requestData['customer_delivery_adress'] = $request->customer_address ?? 'N/A';
        $requestData['delivery_fee_amount'] = $deliveryFee;
        $requestData['delivery_type'] = $request->shipping_type;
        $requestData['gross_amount'] = number_format($totalPrice,2,'.','');
        $requestData['net_amount'] = number_format($totalPrice,2,'.','');
        $requestData['discount_amount'] = $coupon_total_discount;
        $requestData['payment_type'] = $request->payment_type;
        $requestData['delivery_status'] = $request->payment_type == 'bank_deposit' ? 'Waiting for Payment' : 'Scheduled for Processing';
        $requestData['delivery_date'] = $request->date_needed ?? date('Y-m-d');
        $requestData['costcode'] = $request->costcode;
        $requestData['status'] = 'SAVED';
        $requestData['other_instruction'] = $request->notes;
        $requestData['purpose'] = $request->justification;
        $requestData['priority'] = $request->priority;
        $requestData['section'] = $request->section;
        $requestData['requested_by'] = $request->requested_by;
        $requestData['budgeted_amount'] = $request->budgeted_amount;

        $existing_order = SalesHeader::where([
            "user_id" => Auth::id(),
            "status" => "SAVED"
        ])->first();
        
        //dd($requestData);
        if ($existing_order) {
            $existing_order->update($requestData);
            $salesHeader = $existing_order;
        }
        else {
            $salesHeader = SalesHeader::create($requestData);
        }
                
        session::put('shid', $salesHeader->id);
        SalesDetail::where('sales_header_id', $salesHeader->id)->delete();

        $carts = Cart::where('user_id',Auth::id())->get();
        $code_index = 0;

        $grand_gross = 0;
        $grand_tax = 0;

        $coupon_code = 0;
        $coupon_amount = 0;
        $totalQty = 0;

        $cost_codes = $request->codes;
        $par_to = $request->par_to;
        $item_date_needed = $request->item_date_needed;
        $frequency = $request->frequency;
        $item_purpose = $request->item_purpose;

        foreach ($carts as $cart) {

            $totalQty += $cart->qty;

            $product = $cart->product;
            $gross_amount = (number_format($cart->price,2,'.','') * $cart->qty);
            $tax_amount = $gross_amount - ($gross_amount/1.12);
            $grand_gross += $gross_amount;
            $grand_tax += $tax_amount;


            $data['price'] = number_format($cart->price,2,'.','');
            $data['tax'] = $data['price'] - ($data['price']/1.12);
            $data['other_cost'] = 0;
            $data['net_price'] = $data['price'] - ($data['tax'] + $data['other_cost']);

            $mrsDetail = SalesDetail::create([
                'sales_header_id' => $salesHeader->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_category' => $product->category_id,
                'price' => number_format($cart->price,2,'.',''),
                'tax_amount' => number_format($tax_amount,2,'.',''),
                'promo_id' => 0,
                'promo_description' => '',
                'discount_amount' => 0,
                'gross_amount' => number_format($gross_amount,2,'.',''),
                'net_amount' => number_format($gross_amount,2,'.',''),
                'qty' => $cart->qty,
                'uom' => $product->uom,
                'cost_code' => isset($cost_codes[$code_index]) ? $cost_codes[$code_index] : null,
                'par_to' => isset($par_to[$code_index]) ? $par_to[$code_index] : null,
                'date_needed' => isset($item_date_needed[$code_index]) ? $item_date_needed[$code_index] : null,
                'frequency' => isset($frequency[$code_index]) ? $frequency[$code_index] : null,
                'purpose' => isset($item_purpose[$code_index]) ? $item_purpose[$code_index] : null,
                'created_by' => Auth::id()
            ]);
            $cart->update(['mrs_details_id' => $mrsDetail->id]);
            $code_index+=1;

        }

        
        session::put('shid', $salesHeader->id);

        /*
        if ($request->coupon_counter > 0)
        {
            $data = $request->all();
            $coupons = $data['couponid'];
            foreach($coupons as $coupon){
                $exist = CouponCart::where('customer_id',Auth::id())->where('coupon_id',$coupon)->exists();
                if(!$exist){
                   CouponCart::create([
                        'coupon_id' => $coupon,
                        'customer_id' => Auth::id()
                    ]);
                }
            }

            $this->update_coupon_status($request,$salesHeader->id);
        }
        */

        //Cart::where('user_id', Auth::id())->delete();

        return redirect(route('order.success'));
    }

    public function success()
    {
        $page = new Page;
        $page->name = 'Order Saved';

        $sales = SalesHeader::with('items.product')->find(session::get('shid'));

        $announcements = Announcement::where([
            'location' => 'after-checkout',
            'status' => 'active'
        ])->get();

        return view('theme.pages.ecommerce.success',compact('sales','page','announcements'));
    }

    public function generate_payment(Request $request){
        $salesHeader = SalesHeader::where('order_number',$request->order)->first();
        $sign = $this->generateSignature('2amqVf04H9','PH00125',$request->order,str_replace(".", "", number_format($request->amount,2,'.','')),'PHP');
        $payment = $this->ipay($salesHeader,$request->amount,$sign);
        return response()->json([
                'success' => true,
                'order_number' => $request->order,
                'customer_contact_number' => Auth::user()->contact_mobile,
                'amount' => number_format($request->amount,2,'.',''),
                'signature' => $sign
            ]);
    }

    public function remove_cart_coupon()
    {
        CouponCart::where('customer_id',Auth::id())->delete();
    }

    public function update_coupon_status($request,$salesid)
    {

        $data = $request->all();

        if(isset($request->freeproductid)){
            $freeproducts = $data['freeproductid'];
            // if has free products
            foreach($freeproducts as $productid){
                $product = Product::find($productid);

                SalesDetail::create([
                    'sales_header_id' => $salesid,
                    'product_id' => $productid,
                    'product_name' => $product->name,
                    'product_category' => $product->category_id,
                    'price' => 0,
                    'tax_amount' => 0,
                    'promo_id' => 0,
                    'promo_description' => '',
                    'discount_amount' => 0,
                    'gross_amount' => 0,
                    'net_amount' => 0,
                    'qty' => 1,
                    'uom' => $product->uom,
                    'created_by' => Auth::id()
                ]);
            }
        }

        $coupons = $data['couponid'];
        foreach($coupons as $c){
            $coupon = Coupon::find($c);

            $cart = CouponCart::where('customer_id',Auth::id())->where('coupon_id',$coupon->id);

            if($cart->exists()){
                $ct = $cart->first();

                if(isset($ct->product_id)){
                    $productid = $ct->product_id;
                } else {
                    $productid = NULL;
                }
            } else {
                $productid = NULL;
            }

            CouponSale::create([
                'customer_id' => Auth::id(),
                'coupon_id' => $c,
                'coupon_code' => $coupon->coupon_code,
                'sales_header_id' => $salesid,
                'product_id' => $productid
            ]);
        }
    }

    public function submit_payment(Request $request)
    {
        $requestData = $request->all();
        $requestData['sales_header_id'] = $request->id;
        $requestData['payment_type'] = 'Bank Deposit';
        $requestData['payment_date'] = Carbon::today();
        $requestData['status'] = 'PAID';
        $requestData['created_by'] = Auth::id();

        $payment = SalesPayment::create($requestData);
        SalesHeader::find($request->id)->update(['delivery_status' => 'Waiting for Validation']);

        $folder = 'payments/'.$payment->id;
        if($request->hasFile('attachments')){
            foreach($request->file('attachments') as $file){
                $fileName = $file->getClientOriginalName();

                SalesPayment::find($payment->id)->update(['attachment' => $fileName]);
                Storage::disk('public')->putFileAs($folder, $file, $fileName);
            }
        }

        return back()->with('success', 'Payment has been uploaded');
    }
}
