@extends('admin.layouts.app')

@section('pagecss')
    <style>
        .table td {
            padding: 10px;
            font-size: 13px;
        }
    </style>
@endsection

@section('content')
<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-5">
                    <li class="breadcrumb-item" aria-current="page">CMS</li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="{{route('pa.index')}}">MRS for PA</a></li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1"> Order# {{$sales->order_number}} Transaction Summary</h4>
        </div>
        <div>
            <a href="{{ route('pa.index') }}" class="btn btn-secondary btn-sm">Back to Transaction List</a>
        </div>
    </div>

    <div class="row row-sm mg-b-10" style="background-color:#FDE9DE;">

        <div class="col-sm-6 col-lg-8 mg-t-10">
            <!--<label class="tx-sans tx-uppercase tx-10 tx-medium tx-spacing-1 tx-color-03">Customer Details</label>
            <p class="mg-b-3 tx-semibold">{{ optional($sales)->customer_name }}</p>
            <p class="mg-b-3">{{$sales->customer_details->email}}</p>
            <p class="mg-b-20">Mobile No: {{$sales->customer_contact_number}}</p>-->

            <label class="tx-sans tx-uppercase tx-10 tx-medium tx-spacing-1 tx-color-03">Order Details</label>
            <!--<p class="mg-b-3">Invoice No.: {{$sales->order_number}}</p>-->
            <p class="mg-b-3">Posted Date: {{ date('F d, Y', strtotime($sales->created_at))}}</p>
            <p class="mg-b-3">Delivery Type: {{ $sales->delivery_type }}</p>
            <p class="mg-b-3">Order Status: <span class="tx-success tx-semibold">{{ strtoupper($sales->status) }}</span></p>
            <!--<p class="mg-b-3">Delivery Status: <span class="tx-success tx-semibold tx-uppercase">{{$sales->delivery_status}}</span></p>-->

            @if ($sales->delivery_type == 'Delivery')
                <p class="mg-b-3 mg-t-20">Delivery Address: {{$sales->customer_delivery_adress}}</p>
            @endif
            <p class="mg-b-3 mg-t-10">Notes: {{$sales->other_instruction}}</p>

        </div>
        <!--<div class="col-sm-6 col-lg-4 mg-t-10">
            <div class="mg-b-20">
                <center><img height="100px" src="{{ asset('storage/logos/'.Setting::info()->company_logo) }}" alt=""></center>
                <p>Lorenzana Food Corporation<br>Royal Goldcraft Warehouse Compound,<br> Magsaysay Road, Brgy. San Antonio San Pedro <br>4023 Laguna</p>
            </div>
        </div>-->
    </div>

    <form id="issuanceForm" method="POST" action="{{ route('sales-transaction.issuance') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="sales_header_id" value="{{ $salesDetails->first()->sales_header_id }}">
        <div class="row row-sm" style="overflow-x: auto">
            <table class="table table-bordered mg-b-10">
                <tbody>
                    <tr style="background-color:#000000;">
                        <th class="text-white wd-30p">Item</th>
                        <th class="text-white tx-center">Ordered Quantity</th>
                       
                        <!--<th class="text-white tx-center" style="width:20%">Actions</th>-->
                    </tr>
                    @php $gross = 0; $discount = 0; $subtotal = 0; @endphp
                    @forelse($salesDetails as $details)
                        <tr class="pd-20">
                            <td class="tx-nowrap">{{$details->product_name}}</td>
                            <td class="tx-right">{{ number_format($details->qty, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="tx-center " colspan="6">No transaction found.</td>
                        </tr>
                    @endforelse

                    @php
                    $delivery_discount = \App\Models\Ecommerce\CouponSale::total_discount_delivery($sales->id);
                    $net_amount = ($subtotal-$sales->discount_amount)+($sales->delivery_fee_amount-$delivery_discount);
                    @endphp

                    <!--<tr>
                        <td  class="tx-right" colspan="3"><strong>Subtotal:</strong></td>
                        <td class="tx-right"><strong>{{number_format($subtotal, 2)}}</strong></td>
                    </tr>-->

                    @if($sales->discount_amount > 0)
                    <tr>
                        <td  class="tx-right" colspan="3"><strong>Coupon Discount:</strong></td>
                        <td class="tx-right"><strong>{{number_format($sales->discount_amount, 2)}}</strong></td>
                    </tr>
                    @endif

                    <!--<tr>
                        <td  class="tx-right" colspan="3"><strong>Delivery Fee:</strong></td>
                        <td class="tx-right"><strong>{{number_format($sales->delivery_fee_amount, 2)}}</strong></td>
                    </tr>-->

                    @if($delivery_discount > 0)
                    <tr>
                        <td  class="tx-right" colspan="3"><strong>Delivery Discount:</strong></td>
                        <td class="tx-right"><strong>{{number_format($delivery_discount, 2)}}</strong></td>
                    </tr>
                    @endif

                    <!--<tr>
                        <td  class="tx-right" colspan="3"><strong>Grand Total:</strong></td>
                        <td class="tx-right"><strong>{{ number_format($net_amount, 2) }}</strong></td>
                    </tr>-->
                </tbody>
            </table>
        </div>
    </form>
    <div>
        <a href="{{ route('pa.create_pa', $sales->id) }}" class="btn btn-primary btn-sm">Create Purchase Advice</a>
    </div>

</div>
@endsection

@section('pagejs')
    <script>
        function issuanceSubmit() {
            $('#issuanceForm').submit();
        }
    </script>
@endsection