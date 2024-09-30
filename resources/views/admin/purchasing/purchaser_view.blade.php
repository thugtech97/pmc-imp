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

        .title2 {
            font-weight: 600;
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

        .request-details {
            display: table;
        }

        .request-details span {
            display: table-row;
        }

        .request-details strong {
            display: table-cell;
            padding-right: 5px;
            text-align: left;
            white-space: nowrap;
        }

        .request-details .detail-value {
            display: table-cell;
            text-align: left;
        }

        /* Hide arrows in WebKit browsers (Chrome, Safari) */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Hide arrows in Firefox */
        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
@endsection

@section('content')
<div style="margin-left: 100px; margin-right: 100px;">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-5">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="{{route('sales-transaction.index')}}">Order Transaction</a></li>
                </ol>
            </nav>
            <h4 class="mt-4 mg-b-0 tx-spacing--1"> MRS# {{$sales->order_number}} Transaction Summary</h4>
        </div>
        <div>
            <a href="#" id="printDetails" class="btn btn-success btn-sm" data-order="{{$sales->id}}">
                <i class="fas fa-print"></i> Print
            </a>
            <a href="{{ route('sales-transaction.index') }}" class="btn btn-secondary btn-sm">Back to Transaction List</a>
        </div>
    </div>
    <div class="row mx-0 mt-4 mb-3 tx-uppercase">
        <div class="col-7 request-details">
            <span><strong class="title">Request Date:</strong> <span class="detail-value">{{ $sales->created_at }}</span></span>
            <span><strong class="title">Request Status:</strong> <span class="detail-value">{{ strtoupper($sales->status) }}</span></span>
            <span><strong class="title">Department:</strong> <span class="detail-value">{{ $sales->user->department->name }}</span></span>
            <span><strong class="title">Section:</strong> <span class="detail-value">{{ $sales->section }}</span></span>
            <span><strong class="title">Date Needed:</strong> <span class="detail-value">{{ $sales->delivery_date }}</span></span>
            <span><strong class="title">Requested By:</strong> <span class="detail-value">{{ $sales->requested_by }}</span></span>
            <span><strong class="title">Processed By:</strong> <span class="detail-value">{{ strtoupper($sales->user->name) }}</span></span>
        </div>
        <div class="col-5 request-details">
            <span><strong class="title">Delivery Type:</strong> <span class="detail-value">{{$sales->delivery_type }}</span></span>
            <span><strong class="title">Delivery Address:</strong> <span class="detail-value">{{ $sales->customer_delivery_adress }}</span></span>
            <span><strong class="title">Budgeted:</strong> <span class="detail-value">{{ $sales->budgeted_amount > 0 ? 'YES' : 'NO' }}</span></span>
            <span><strong class="title">Budgeted Amount:</strong> <span class="detail-value">{{ number_format($sales->budgeted_amount, 2, '.', ',')}}</span></span>
            <span><strong class="title">Other Instructions:</strong> <span class="detail-value">{{ $sales->other_instruction}}</span></span>
            <span><strong class="title">Purpose:</strong> <span class="detail-value">{{ $sales->purpose}}</span></span>
            <span><strong class="title">Status:</strong> <span class="detail-value badge px-2 text-center">{{ $sales->status}}</span></span>
        </div>
    </div>

    <form id="issuanceForm" method="POST" action="{{ route('purchaser.receive') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="sales_header_id" value="{{ $salesDetails->first()->sales_header_id }}">
        <div class="row row-sm" style="overflow-x: auto">
            <table class="table mg-b-10">
                <thead>
                    <tr style="background-color: #f2f2f2; color: #333; border-bottom: 2px solid #ccc;">
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item#</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Priority#</th>
                        <th width="15%" class="text-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Stock Code</th>
                        <th class="text-left" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">OEM No.</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Cost Code</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Qty to Order</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Previous PO#</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Current PO#</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">PO Date Released</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Qty Ordered</th>
                        {{-- <th width="10%">On Order</th>  --}}
                    </tr>
                </thead>
                <tbody>
                    @php $gross = 0; $discount = 0; $subtotal = 0; $count = 0; @endphp
                    @forelse($salesDetails as $details)
                        @php
                            $count++;
                        @endphp
                        <input type="hidden" name="ecommerce_sales_details_id{{ $details->id }}" value="{{ $details->id }}">
                        <input type="hidden" name="ordered_qty{{ $details->id }}" value="{{ $details->qty }}">
                        
                        <tr class="pd-20" style="border-bottom: none;">
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$count}}</td>
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$sales->priority}}</td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product->code}}</td>
                            <td class="tx-nowrap" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product_name}}</td>
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product->oem}}</td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->cost_code}}</td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                <input type="number" name="quantityToOrder{{ $details->id }}" value="{{ $details->qty_to_order > 0 ? (int)$details->qty_to_order : (int)$details->qty }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                <input type="text" name="previous_no{{ $details->id }}" value="{{ $details->previous_mrs }}" class="form-control" disabled>
                            </td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                <input type="text" name="po_no{{ $details->id }}" value="{{ $details->po_no }}" class="form-control" {{ $sales->status !== "RECEIVED (Purchasing Officer)" ? 'disabled' : '' }} required>
                            </td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                <input type="date" name="po_date_released{{ $details->id }}" value="{{ $details->po_date_released ? \Carbon\Carbon::parse($details->po_date_released)->format('Y-m-d') : '' }}" class="form-control" {{ $sales->status !== "RECEIVED (Purchasing Officer)" ? 'disabled' : '' }} required>
                            </td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                <input type="number" data-qty="{{ $details->qty_to_order }}" name="qty_ordered{{ $details->id }}" value="{{ $details->qty_ordered }}" class="form-control qty_ordered" {{ $sales->status !== "RECEIVED (Purchasing Officer)" ? 'disabled' : '' }} required>
                            </td>

                            {{--  
                            <td class="tx-right">
                                <input type="text" name="open_po{{ $details->id }}" value="{{ $details->open_po }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>

                            --}}
                        </tr>
                        <tr class="pd-20">
                            <td colspan="2" style="padding: 10px; text-align: left; border: 1px solid #ddd;"></td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                <span class="title2">PAR TO: </span><br>
                                <span class="title2">FREQUENCY: </span><br>
                                <span class="title2">DATE NEEDED: </span><br>
                                <span class="title2">PURPOSE: </span>
                            </td>
                            <td colspan="8" class="tx-left" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                {{$details->par_to}}<br>
                                {{$details->frequency}}<br>
                                {{ \Carbon\Carbon::parse($details->date_needed)->format('m/d/Y') }}<br>
                                {{$details->purpose}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="tx-center " colspan="6">No transaction found.</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group"> 
                    <button type="button" id="receivePurchaser" class="btn btn-success mt-2" style="width: 140px; text-transform: uppercase;" {{ $sales->status === 'RECEIVED (Purchasing Officer)' ? 'disabled' : '' }}>{{ $sales->status === 'RECEIVED (Purchasing Officer)' ? 'Received' : 'Receive' }}</button>
                    @if($sales->status === 'RECEIVED (Purchasing Officer)')
                        <button type="submit" class="btn btn-info mt-2" style="width: 140px; text-transform: uppercase; float: right;">Submit</button>
                    @endif
                </div>
            </div>
        </div>
    </form>
    
</div>
@endsection

@section('pagejs')
    <script>
        function issuanceSubmit() {
            $('#issuanceForm').submit();
        }

        $(document).ready(function() {
            //employee_lookup();
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

            $('#receivePurchaser').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent("No-Note");
                var url = "{{ route('mrs.action', ['action' => 'purchaser-receive', 'id' => $sales->id]) }}&note=" + note;
                window.location.href = url;
            });

            $(".qty_ordered").on("keyup", function(){
                var qty_order = parseInt($(this).val());
                var qty = parseInt($(this).data('qty'));
                if(qty_order > qty) {
                    $('#toastDynamicError').toast({
                        delay: 3000
                    });
                    $('#errorMessage').html("Quantity to order should not exceed the requested quantity.");
                    $('#toastDynamicError').toast('show');
                    $(this).val(qty)
                }
                if(qty_order <= 0) {
                    $('#toastDynamicError').toast({
                        delay: 3000
                    });
                    $('#errorMessage').html("Quantity to order cannot be zero or negative.");
                    $('#toastDynamicError').toast('show');
                    $(this).val(qty)
                }
            });
        });
    </script>
@endsection