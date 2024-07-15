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

        input {
            border-color: grey;
            outline: none;
            font-size: 16px;

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
            <h4 class="mt-4 mg-b-0 tx-spacing--1"> Request# {{$sales->order_number}} Transaction Summary</h4>
        </div>
        @if($role->name === "MCD Planner" || $role->name === "MCD Verifier")
        <div>
            <a href="#" id="printDetails" class="btn btn-success btn-sm" data-order="{{$sales->id}}">
                <i class="fas fa-print"></i> Print
            </a>
            <a href="{{ route('sales-transaction.index') }}" class="btn btn-secondary btn-sm">Back to Transaction List</a>
        </div>
        @endif
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
            <div>
                <span class="title">Delivery Instruction:</span> {{ $sales->other_instruction }}
            </div>
        </div>
        <div class="col-6 p-0 m-0">
            <div>
                <span class="title">Posted Date:</span> {{ \Carbon\Carbon::parse($sales->created_at)->format('Y-m-d h:i:s A') }}
            </div>
            <div>
                <span class="title">Date Needed:</span> {{ \Carbon\Carbon::parse($sales->delivery_date)->format('Y-m-d h:i:s A') }}
            </div>
            <div>
                <span class="title">Request Status:</span>
                <span class="badge px-2" 
                    style="background-color: @if($sales->status == 'APPROVED' || $sales->status == 'COMPLETED') #6c9d79; @else #3395ff; @endif">
                    {{ $sales->status }}
                </span>
            </div>
            <div>
                <span class="title">Budgeted:</span>  {{ $sales->budgeted_amount > 0 ? 'YES' : 'NO' }}
            </div>
        </div>
    </div>

    <form id="issuanceForm" method="POST" action="{{ route('mrs.update') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="sales_header_id" value="{{ $salesDetails->first()->sales_header_id }}">
        <div class="row row-sm" style="overflow-x: auto">
            <table class="table mg-b-10">
                <thead>
                    <tr>
                        <th width="10%">Stock Code</th>
                        <th class="text-left">Item</th>
                        <th width="10%">Cost Code</th>
                        <th width="10%">OEM No.</th>
                        <th width="10%">Requested Quantity</th>
                        <th width="10%">Quantity to Request</th>
                        @if ($sales->status != "COMPLETED")
                            <th class="d-none" width="1%">Issuance Quantity</th>
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
                        
                        <tr class="pd-20">
                            <td class="tx-left">{{$details->product->code}}</td>
                            <td class="tx-nowrap">{{$details->product_name}}</td>
                            <td class="tx-right">{{$details->cost_code}}</td>
                            <td class="tx-center">{{$details->product->oem}}</td>
                            <td class="tx-right">{{ number_format($details->qty, 2) }}</td>
                            <td class="tx-right">
                                <input type="number" name="quantityToOrder{{ $details->id }}" value="{{ $details->qty_to_order > 0 ? $details->qty_to_order : $details->qty }}" class="form-control" {{ $role->name === "MCD Verifier" ? 'disabled' : '' }}>
                            </td>
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
            <div class="col-8">
            </div>
            <div class="col-4">
                @if($sales->budgeted_amount > 0)
                    <div class="row mx-0 tx-uppercase">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="budgeted_amount" class="title">Budgeted Amount:</label>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="form-group">
                                <input id="budgeted_amount" value="{{ number_format($sales->budgeted_amount, 2, '.', ',') }}"  type="text" class="form-control" name="budgeted_amount" disabled>
                            </div>
                        </div>
                    </div>
                
                    <div class="row mx-0 tx-uppercase">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="adjusted_amount" class="title">Adjusted Amount:</label>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="form-group">
                                <input id="adjusted_amount" value="{{ $sales->adjusted_amount > 0 ? $sales->adjusted_amount : $sales->budgeted_amount }}" type="number" class="form-control" name="adjusted_amount" {{ $role->name === "MCD Verifier" ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    @if ($role->name === "MCD Verifier")
                        <a href="{{ route('mrs.action',  ['action' => 'verify', 'id' => $sales->id]) }}" class="btn btn-success" style="width: 140px; text-transform: uppercase;">Verify</a>
                        <a href="{{ route('mrs.action',  ['action' => 'hold', 'id' => $sales->id]) }}" class="btn btn-warning" style="width: 140px; text-transform: uppercase;">Hold</a>
                     @endif
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group text-right">
                    @if ($role->name === "MCD Planner")
                        <button type="submit" class="btn {{ ($sales->status === 'APPROVED (MCD Planner)' || $sales->status === 'VERIFIED (MCD Verifier)') ? 'btn-success' : 'btn-success'}}" style="width: 140px; text-transform: uppercase;" {{ $sales->status === 'APPROVED (MCD Planner)' || $sales->status === 'VERIFIED (MCD Verifier)' ? 'disabled' : '' }}>{{ $sales->status === 'APPROVED (MCD Planner)' || $sales->status === 'VERIFIED (MCD Verifier)' ? 'SUBMITTED' : 'PROCEED'}}</button><br><br>
                     @endif
                    @if($sales->for_pa == 1 && $sales->is_pa == 1)
                        <button class="btn btn-info print" data-order-number="{{$sales->order_number}}" style="width: 140px; text-transform: uppercase;">PRINT PA</button>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- 
        <div class="row d-none">
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
                            <button type="submit" class="btn" style="background-color: #6c9d79; color: white; width: 140px; text-transform: uppercase;">Update</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        --}}
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

        $(document).ready(function() {
            $('#printDetails').click(function(e) {
                e.preventDefault(); 
                
                $.ajax({
                    url: "{{route('sales-transaction.generate_report')}}",
                    type: 'GET',
                    data: { id: $(this).attr('data-order') },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data) {
                        if (data instanceof Blob) {
                            const pdfBlob = new Blob([data], { type: 'application/pdf' });
                            const pdfUrl = URL.createObjectURL(pdfBlob);
                            window.open(pdfUrl, '_blank');
                            URL.revokeObjectURL(pdfUrl);
                        }
                    }
                });
            });

            $('.print').click(function(evt) {
                evt.preventDefault();

                var orderNumber = this.getAttribute('data-order-number');

                console.log('Print button clicked', orderNumber);

                $.ajax({
                    url: "{{route('pa.generate_report')}}",
                    type: 'GET',
                    data: { orderNumber: orderNumber },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data) {
                        if (data instanceof Blob) {

                            const pdfBlob = new Blob([data], { type: 'application/pdf' });
                            const pdfUrl = URL.createObjectURL(pdfBlob);

                            window.open(pdfUrl, '_blank');
                            URL.revokeObjectURL(pdfUrl);
                            
                        }
                    }
                });
            }); 
        });
    </script>
@endsection