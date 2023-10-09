@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('theme/success-style.css') }}">
@endsection

@section('content')
<div class="container">
    @auth
        <div class="row">
            @if (isset($announcements))
                @foreach ($announcements as $announcement)
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {!! $announcement->content !!}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endforeach
            @endif
        </div>
    @endauth
    
    @if (Session::has('success'))
    <div class="alert alert-info" role="alert">
        {{ Session::get('success') }}
    </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="py-5 py-lg-6 position-relative">
        <div class="container">
            <div class="border py-4 px-3 border-transparent shadow-lg p-lg-5">
                <h3 class="border-bottom pb-3 text-center" style="color: #444;
                    font-size: 1.5rem;
                    font-family: DM Sans,sans-serif;
                    font-weight: 600;
                    line-height: 1.5;
                    margin: 0 0 30px;
                ">Your order has been successfully saved</h3>

                <p class="text-center mb-0">Your order number is</p>
                <p class="text-center fs-34-f color fw-bold">{{ $sales->order_number }}</p>

                <div class="mx-wd-600-f m-auto mb-5">
                    <p class="text-center">Note: This order is not yet submitted to MCD you need to submit this to your head for approval.</p>
                </div>

                <div class="d-flex flex-column flex-lg-row justify-content-center">
                    <a href="{{ route('profile.sales') }}" class="button button-dark button-border button-circle button-xlarge fw-bold mt-2 fs-14-f nols notextshadow text-center">View my requests</a>
                    <a href="{{ route('catalogue.home') }}" class="button button-dark button-border button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow text-center">Add more product</a>
                    <a href="{{ route('my-account.submit.order.request', ['id' => $sales->id, 'status' => 'submitted']) }}" class="button button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow text-center">Submit for Approval</a>
                </div>
            </div>
        </div>
    </section>

    <div class="mb-3">
        <h4 class="mb-2 mb-lg-0 fw-bold fs-24 fs-lg-28">Summary</h4>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group mb-5 mx-wd-400">
                <label for="shippingType" class="fw-semibold text-initial nols">Delivery Type</label>
                <br />
                {{ $sales->delivery_type }}
            </div>

            @if ($sales->delivery_type == "Delivery")
                <div class="form-group mb-5 mx-wd-400 customerAddress">
                    <label for="shippingType" class="fw-semibold text-initial nols">Address</label>
                    <br />
                    {{ $sales->customer_delivery_adress }}
                </div>
            @endif
        </div>
        
        <div class="col-lg-6">
            <div class="form-group mb-4">
                <label for="notes" class="fw-semibold text-initial nols">Notes</label>
                <br />
                {{ $sales->other_instruction }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="table-lg-responsive table-md-responsive">
            <table class="table table-striped dataTable w-100 wd-lg-1100-f">
                <thead class="thead-light">
                    <tr class="d-none d-lg-table-row">
                        <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-20p-f cart-product-name">Product</th>
                        <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-15p-f cart-product-quantity">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $grandtotal = 0; $totalproducts = 0; $available_stock = 0; 

                        $cproducts  = '';
                        $totalCartProducts = 0;
                    @endphp

                    @forelse($sales->items as $key => $order)
                        @php 
                            $totalproducts += 1;
                            $grandtotal += $order->product->discountedprice*$order->qty;

                            if($order->product->inventory == 0){
                                $available_stock++;
                            }


                            $cproducts .= $order->product_id.'|';
                            $totalCartProducts++;

                            if(Auth::check()){
                                $orderID = $order->id;
                            } else {
                                $orderID = $key;
                            }
                        @endphp

                        <tr class="d-flex d-lg-table-row flex-column flex-fill w-100 border border-lg-0 mb-3 mb-lg-0 cart_item">
                            <input type="hidden" name="cart_id[]" value="{{ $orderID }}">

                            <input type="hidden" id="iteration{{$order->product_id}}" value="{{$orderID}}">
                            <input type="hidden" id="record_id{{$orderID}}" name="record_id[{{$orderID}}]" value="{{$orderID}}">

                            <input type="hidden" name="productid[]" id="pp{{$orderID}}" value="{{$order->product_id}}">
                            <input type="hidden" name="productbrand[]" data-productid="{{$order->product_id}}" id="pp{{$orderID}}" value="{{$order->product->brand}}">
                            <input type="hidden" name="productcatid[]" data-productid="{{$order->product_id}}" id="pp{{$orderID}}" value="{{$order->product->category_id}}">

                            <td class="cart-product-name align-middle">
                                <div class="top-cart-item mx-wd-300">
                                    <div class="top-cart-item-image wd-70-f ht-70-f">
                                        <a href="{{ route('product.front.show',$order->product->slug)}}" class="wd-70-f ht-70-f"><img class="wd-70-f ht-70-f" src="{{$order->product->photoPrimary}}" alt="{{ $order->product->name }}"></a>
                                    </div>
                                    <div class="top-cart-item-desc">
                                        <div class="top-cart-item-desc-title">
                                            <a href="{{ route('product.front.show',$order->product->slug)}}" class="fs-16-f fw-normal lh-base">{{ $order->product->name }}</a>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!--<td class="cart-product-thumbnail">
                                <a href="{{ route('product.front.show',$order->product->slug)}}"><img width="64" height="64" src="{{$order->product->photoPrimary}}" alt="{{ $order->product->name }}"></a>
                            </td>

                            <td class="cart-product-name">
                                <a href="{{ route('product.front.show',$order->product->slug)}}">{{ $order->product->name }}</a>
                            </td>

                            <!--<td class="cart-product-price">
                                <span class="amount">₱{{ number_format($order->product->discountedprice,2) }}</span>
                            </td>-->

                            <td class="align-middle cart-product-quantity">
                                {{$order->qty}}
                            </td>

                            <!--<td class="cart-product-subtotal">
                                <input type="hidden" id="product_name_{{$orderID}}" value="{{$order->product->name}}">
                                <input type="hidden" name="product_price[]" id="input_order{{$orderID}}_product_price" value="{{$order->product->discountedprice}}">


                                <input type="hidden" id="price{{$orderID}}" value="{{number_format($order->product->discountedprice,2,'.','')}}">
                                <input type="hidden" class="input_product_total_price" data-id="{{$orderID}}" data-productid="{{$order->product_id}}" id="input_order{{$orderID}}_product_total_price" value="{{$order->product->discountedprice*$order->qty}}">

                                <!-- Coupon Inputs
                                <input type="hidden" class="cart_product_reward" id="cart_product_reward{{$orderID}}" value="0">
                                <input type="hidden" class="cart_product_discount" id="cart_product_discount{{$orderID}}" value="0">

                                <span class="amount" id="order{{$orderID}}_total_price">₱{{ number_format($order->product->discountedprice*$order->qty,2) }}</span>
                            </td>-->
                        </tr>
                    @empty
                        @php $totalproducts = 0; @endphp
                        <tr class="d-flex d-lg-table-row flex-column flex-fill w-100 border border-lg-0 mb-3 mb-lg-0 cart_item">
                            <td colspan="6" class="align-middle text-center">Your shopping cart is <strong>empty</strong>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
</div>
@endsection

@section('pagejs')
@endsection