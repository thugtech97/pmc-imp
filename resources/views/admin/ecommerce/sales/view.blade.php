@extends('admin.layouts.app')

@section('pagecss')
    <style>
        .table td {
            padding: 10px;
            font-size: 13px;
        }
        .table th {
            font-size: 14px;
            text-transform: uppercase;
            color: black !important;
            text-align: center;
        }
        .title {
            font-weight: bold;
            color: #212529;
        }
        .text-left {            
            text-align: left !important;

        }
        .badge {
            display: inline-block;
            font-size: 13px;
            font-weight: bold;
            color: #fff; 
            background-color: #3395ff;
            border-radius: 0.25em;
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
            <h4 class="mt-4 mg-b-0 tx-spacing--1"> Order# {{$sales->order_number}} Transaction Summary</h4>
        </div>
        <div>
            <a href="{{ route('sales-transaction.index') }}" class="btn btn-secondary btn-sm">Back to Transaction List</a>
        </div>
    </div>
    <div class="row mx-0 mt-4 mb-3 tx-uppercase">
        <div class="col-6 p-0 m-0">
            <div>
                <span class="title">Department:</span> {{ $sales->customer_name }}
            </div>
            <div>
                <span class="title">Requested by:</span> {{ $sales->user->name}}</td>
            </div>
            <div>
                <span class="title">Delivery Type:</span> {{ $sales->delivery_type}}</td>
            </div>
        </div>
        <div class="col-6 p-0 m-0">
            <div>
                <span class="title">Posted Date:</span> {{ \Carbon\Carbon::parse($sales->created_at)->format('Y-m-d h:i:s A') }}
            </div>
            <div>
                <span class="title">Delivery Date:</span> {{ \Carbon\Carbon::parse($sales->delivery_date)->format('Y-m-d h:i:s A') }}
            </div>
            <div>
                <span class="title">Order Status:</span>
                <span class="badge px-2" 
                    style="background-color: @if($sales->status == 'APPROVED' || $sales->status == 'COMPLETED') #6c9d79; @else #3395ff; @endif">
                    {{ $sales->status }}
                </span>
            </div>
            @if ($sales->delivery_type == 'Delivery')
            <div>
                <span class="title">Delivery Address:</span>  {{ $sales->customer_delivery_adress }}
            </div>
            @endif
        </div>
        <div class="col-12 p-0 m-0">
            <span class="title">Delivery Instruction:</span> {{ $sales->other_instruction }}
        </div>
    </div>
    <form id="issuanceForm" method="POST" action="{{ route('sales-transaction.issuance') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="sales_header_id" value="{{ $salesDetails->first()->sales_header_id }}">
        <div class="row row-sm" style="overflow-x: auto">
            <table class="table mg-b-10">
                <thead>
                    <tr>
                        <th class="text-left">Item</th>
                        <th width="10%">Ordered Quantity</th>
                        <th width="10%">Issued Quantity</th>
                        <th width="10%">Balance</th>
                        @if ($sales->status != "COMPLETED")
                            <th width="1%">Issuance Quantity</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
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
                            
                            @if ($sales->status !== "COMPLETED")
                                <td class="tx-right">
                                    @if($bal > 0)
                                        <input 
                                            type="number" 
                                            class="form-control text-right" 
                                            name="deploy{{ $details->id }}" 
                                            value="0" 
                                            min="0" 
                                            max="{{ $bal }}"
                                            required="required"
                                        >
                                    @endif
                                </td>
                            @endif
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

                    @if($sales->discount_amount > 0)
                    <tr>
                        <td  class="tx-right" colspan="3"><strong>Coupon Discount:</strong></td>
                        <td class="tx-right"><strong>{{number_format($sales->discount_amount, 2)}}</strong></td>
                    </tr>
                    @endif

                    @if($delivery_discount > 0)
                    <tr>
                        <td  class="tx-right" colspan="3"><strong>Delivery Discount:</strong></td>
                        <td class="tx-right"><strong>{{number_format($delivery_discount, 2)}}</strong></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-7"></div>
            @if ($sales->status != "COMPLETED")
            <div class="col-5">
                <div class="row p-0 m-0">
                    <div class="col-2 p-0">
                        <div class="form-group">
                            <label for="issuance_no" class="text-right">Issuance #:</label>
                        </div>
                    </div>
                    <div class="col-10 p-0">
                        <div class="form-group">
                            <input id="issuance_no" type="text" class="form-control" name="issuance_no" required="required">
                        </div>
                    </div>
                </div>
                <div class="row p-0 m-0">
                    <div class="col-2 p-0">
                        <div class="form-group">
                            <label for="issuedBy" class="text-right">Issued by:</label>
                        </div>
                    </div>
                    <div class="col-10 p-0">
                        <div class="form-group">
                            <input id="issuedBy" type="text" class="form-control" name="issued_by" required="required">
                        </div>
                    </div>
                </div>
                <div class="row p-0 m-0">
                    <div class="col-2 p-0">
                        <div class="form-group">
                            <label for="receivedBy" class="text-right">Received by:</label>
                        </div>
                    </div>
                    <div class="col-10 p-0">
                        <div class="form-group">
                            <input id="receivedBy" type="text" class="form-control" name="received_by" required="required">
                        </div>
                    </div>
                </div>
                <div class="row p-0 m-0">
                    <div class="col-md-12 p-0 text-right">
                        <div class="form-group">
                            <button type="submit" class="btn" style="background-color: #6c9d79; color: white; width: 140px; text-transform: uppercase;">Submit</button>
                        </div>
                    </div>
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