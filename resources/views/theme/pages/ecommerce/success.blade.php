@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('theme/success-style.css') }}">
    <style>
        .pmc-success-page {
            --success-ink: #222e2b;
            --success-green: #42615b;
            --success-gold: #ecd49a;
            --success-line: #e3ebe6;
            background: #f4f6f3;
            font-family: "DM Sans", sans-serif;
            padding: 48px 0 72px;
            position: relative;
        }
        section#content.py-5.py-lg-6 {
            padding-bottom: 0 !important;
            padding-top: 0 !important;
        }
        section#content.py-5.py-lg-6 .container {
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            width: 100vw !important;
        }
        .pmc-success-page::before {
            background: linear-gradient(135deg, rgba(66, 97, 91, .1), transparent 58%);
            content: '';
            inset: 0;
            pointer-events: none;
            position: absolute;
        }
        .pmc-success-card {
            background: #fff;
            border: 1px solid var(--success-line);
            border-radius: 20px;
            box-shadow: 0 18px 44px rgba(34, 46, 43, .1);
            margin: 0 auto;
            max-width: 760px;
            overflow: hidden;
            padding: 48px 32px 36px;
            position: relative;
            text-align: center;
            z-index: 1;
        }
        .pmc-success-card::before {
            background: linear-gradient(90deg, var(--success-green), var(--success-gold));
            content: '';
            height: 4px;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
        }
        .pmc-success-icon {
            align-items: center;
            background: #edf5f0;
            border: 1px solid #cfe1d6;
            border-radius: 50%;
            color: var(--success-green);
            display: inline-flex;
            font-size: 30px;
            height: 72px;
            justify-content: center;
            margin-bottom: 22px;
            width: 72px;
        }
        .pmc-success-eyebrow {
            color: var(--success-green);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.8px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .pmc-success-card h1 {
            color: var(--success-ink);
            font-size: clamp(26px, 4vw, 36px);
            font-weight: 700;
            line-height: 1.15;
            margin: 0 auto 12px;
        }
        .pmc-success-lead {
            color: #71807a;
            font-size: 14px;
            line-height: 1.7;
            margin: 0 auto 26px;
            max-width: 520px;
        }
        .pmc-success-reference {
            background: #f7faf8;
            border: 1px solid var(--success-line);
            border-radius: 12px;
            margin: 0 auto 24px;
            max-width: 420px;
            padding: 15px 20px;
        }
        .pmc-success-reference-label {
            color: #71807a;
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .pmc-success-reference-number {
            color: var(--success-green);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: .5px;
        }
        .pmc-success-note {
            background: #fffaf0;
            border: 1px solid #eadfbd;
            border-left: 3px solid var(--success-gold);
            border-radius: 9px;
            color: #725b1e;
            font-size: 12px;
            line-height: 1.7;
            margin: 0 auto 30px;
            max-width: 560px;
            padding: 13px 16px;
            text-align: left;
        }
        .pmc-success-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .pmc-success-actions .button {
            align-items: center;
            border-radius: 9px;
            display: inline-flex;
            font-size: 12px;
            height: 44px;
            justify-content: center;
            line-height: 1;
            margin: 0 !important;
            padding: 0 18px;
        }
        .pmc-success-actions .button-primary {
            background: var(--success-green) !important;
            border: 1px solid var(--success-green) !important;
            color: #fff !important;
        }
        .pmc-success-actions .button-primary:hover {
            background: var(--success-ink) !important;
            border-color: var(--success-ink) !important;
        }
        .pmc-success-actions .button-secondary {
            background: transparent !important;
            border: 1px solid #cfdcd4 !important;
            color: var(--success-green) !important;
        }
        .pmc-success-actions .button-secondary:hover {
            background: #f1f6f2 !important;
            border-color: var(--success-green) !important;
        }
        @media (max-width: 575.98px) {
            .pmc-success-page { padding: 28px 12px 48px; }
            .pmc-success-card { padding: 38px 20px 28px; }
            .pmc-success-actions { flex-direction: column; }
            .pmc-success-actions .button { width: 100%; }
        }
    </style>
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

    <section class="pmc-success-page">
        <div class="container">
            <div class="pmc-success-card">
                <div class="pmc-success-icon" aria-hidden="true"><i class="icon-line-check"></i></div>
                <div class="pmc-success-eyebrow">Materials Request System</div>
                <h1>Your request has been saved</h1>
                <p class="pmc-success-lead">Your materials request is ready for review. Please forward it to your department head for approval.</p>

                <div class="pmc-success-reference">
                    <span class="pmc-success-reference-label">Request number</span>
                    <span class="pmc-success-reference-number">{{ $sales->order_number }}</span>
                </div>

                <div class="pmc-success-note">
                    <strong>Next step:</strong> This request has not yet been submitted to MCD. Your department head must review and approve it before processing can continue.
                </div>

                <div class="pmc-success-actions">
                    <a href="{{ route('profile.sales') }}" class="button button-primary fw-bold nols notextshadow text-center"><i class="icon-line-clipboard me-2"></i>View My Requests</a>
                    <a href="{{ route('catalogue.home') }}" class="button button-secondary fw-bold nols notextshadow text-center"><i class="icon-line-plus me-2"></i>Create Another Request</a>
                </div>
            </div>
        </div>
    </section>

    {{--
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
    --}}
    
</div>
@endsection

@section('pagejs')
@endsection
