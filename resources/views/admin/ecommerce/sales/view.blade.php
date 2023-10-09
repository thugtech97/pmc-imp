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
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="{{route('sales-transaction.index')}}">Order Transaction</a></li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1"> Order# {{$sales->order_number}} Transaction Summary</h4>
        </div>
        <div>
            <a href="{{ route('sales-transaction.index') }}" class="btn btn-secondary btn-sm">Back to Transaction List</a>
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
                        <th class="text-white tx-center">Issued Quantity</th>
                        <th class="text-white tx-center">Balance</th>
                        <!--<th class="text-white tx-center">Unit Price</th>-->
                        @if ($sales->status != "COMPLETED")
                            <th class="text-white tx-center">Issuance Quantity</th>
                        @endif
                       
                        <!--<th class="text-white tx-center" style="width:20%">Actions</th>-->
                    </tr>
                    @php $gross = 0; $discount = 0; $subtotal = 0; @endphp
                    @forelse($salesDetails as $details)

                        @php
                        $discount = \App\Models\Ecommerce\CouponSale::total_product_discount($sales->id,$details->product_id,$details->qty,$details->price);
                        $product_subtotal = $details->price*$details->qty;

                        $subtotal += $product_subtotal;

                        $bal = ($details->qty - $details->issuances->sum('qty'));
                        @endphp
                        
                        <input type="hidden" name="ecommerce_sales_details_id{{ $details->id }}" value="{{ $details->id }}">
                        <input type="hidden" name="ordered_qty{{ $details->id }}" value="{{ $details->qty }}">
                        <input type="hidden" name="total_issued{{ $details->id }}" value="{{ $details->issuances->sum('qty') }}">
                        
                        <input type="hidden" name="product_id{{ $details->id }}" value="{{ $details->product_id }}">
                        
                        <tr class="pd-20">
                            <td class="tx-nowrap">{{$details->product_name}}</td>
                            <td class="tx-right">{{ number_format($details->qty, 2) }}</td>
                            <td class="tx-right">
                                @if ($details->issuances->sum('qty') > 0)
                                <a href="javascript:;" data-toggle="modal" data-target="#issuanceModal{{ $details->id }}">
                                    {{ number_format($details->issuances->sum('qty'), 2) }}
                                </a>
                                @else
                                    {{ number_format($details->issuances->sum('qty'), 2) }}
                                @endif
                            </td>
                            <td class="tx-right">{{ number_format($bal,2) }}</td>
                            
                            @if ($sales->status != "COMPLETED")
                                <td class="tx-right">
                                    @if($bal > 0)
                                        <input type="number" name="deploy{{ $details->id }}" required="required" value="0" min="0" max="{{ $bal }}">
                                    @endif
                                </td>
                            @endif

                          
                            <!--<td class="tx-right">{{number_format($details->price, 2)}}</td>-->

                            <!--<td>
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#issuanceDetailsModal{{ $details->id }}" class="btn btn-success btn-sm">Issuances</a>
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#issuanceModal{{ $details->id }}" class="btn btn-primary btn-sm">Issue Items</a>
                            </td>-->
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

        <div class="row">
            <div class="col-md-6"></div>

            @if ($sales->status != "COMPLETED")
                <div class="col-md-6">
                    <div class="form-group text-right">
                        <label for="issuance_no">Issuance #:</label>
                        <input id="issuance_no" type="text" name="issuance_no" required="required">
                    </div>
                    <div class="form-group text-right">
                        <label for="issuedBy">Issued by:</label>
                        <input id="issuedBy" type="text" name="issued_by" required="required">
                    </div>

                    <div class="form-group text-right">
                        <label for="receivedBy">Received by:</label>
                        <input id="receivedBy" type="text" name="received_by" required="required">
                    </div>

                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </div>
            @endif
        </div>
    </form>

    <!-- Modal -->
    @foreach($salesDetails as $details)
        <div class="modal fade" id="issuanceModal{{ $details->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Issuances</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                            <th scope="col">#</th>
                            <th scope="col">Date Released</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Released By</th>
                            <th scope="col">Encoded By</th>
                            <th scope="col">Encoded Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details->issuances as $issuance)
                                @if ($issuance->qty > 0)
                                    <tr>
                                        <td scope="row">{{ $issuance->issuance_no }}</td>
                                        <td>{{ $issuance->release_date }}</td>
                                        <td>{{ $issuance->qty }}</td>
                                        <td>{{ $issuance->issued_by }}</td>
                                        <td>{{ $issuance->user->name }}</td>
                                        <td>{{ $issuance->created_at }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@section('pagejs')
    <script>
        function issuanceSubmit() {
            $('#issuanceForm').submit();
        }
    </script>
@endsection