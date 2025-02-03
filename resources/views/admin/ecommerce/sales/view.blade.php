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

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }
        /*  hold switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 24px;
        }

        .switch input { 
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #2196F3;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: red;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px red;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(16px);
            -ms-transform: translateX(16px);
            transform: translateX(16px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
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
        @if($role->name === "MCD Planner" || $role->name === "MCD Verifier" || $role->name === "MCD Approver")
        <div>
            <a href="#" id="printDetails" class="btn btn-success btn-sm" data-order="{{$sales->id}}">
                <i class="fas fa-print"></i> Print
            </a>
            <a href="{{ route('sales-transaction.index') }}" class="btn btn-secondary btn-sm">Back to Transaction List</a>
        </div>
        @endif
    </div>
    <div class="row mx-0 mt-4 mb-3 tx-uppercase">
        <div class="col-6 request-details">
            <span><strong class="title">Request Date:</strong> <span class="detail-value">{{ $sales->created_at }}</span></span>
            <span><strong class="title">Request Status:</strong> <span class="detail-value">{{ strtoupper($sales->status) }}</span></span>
            <span><strong class="title">Department:</strong> <span class="detail-value">{{ $sales->user->department->name }}</span></span>
            <span><strong class="title">Section:</strong> <span class="detail-value">{{ $sales->section }}</span></span>
            <span><strong class="title">Date Needed:</strong> <span class="detail-value">{{ $sales->delivery_date }}</span></span>
            <span><strong class="title">Requested By:</strong> <span class="detail-value">{{ $sales->requested_by }}</span></span>
            <span><strong class="title">Processed By:</strong> <span class="detail-value">{{ strtoupper($sales->user->name) }}</span></span>
        </div>
        <div class="col-6 request-details">
            <span><strong class="title">Delivery Type:</strong> <span class="detail-value">{{$sales->delivery_type }}</span></span>
            <span><strong class="title">Delivery Address:</strong> <span class="detail-value">{{ $sales->customer_delivery_adress }}</span></span>
            <span><strong class="title">Budgeted:</strong> <span class="detail-value">{{ $sales->budgeted_amount > 0 ? 'YES' : 'NO' }}</span></span>
            <span><strong class="title">Budgeted Amount:</strong> <span class="detail-value">{{ number_format($sales->budgeted_amount, 2, '.', ',')}}</span></span>
            <span><strong class="title">Other Instructions:</strong> <span class="detail-value">{{ $sales->other_instruction}}</span></span>
            <span><strong class="title">Note:</strong> <span class="detail-value">{{ $sales->purpose}}</span></span>
            @php
                $status = $sales->status === "HOLD (For MCD Planner re-edit)" 
                        ? "HOLD (For MCD Planner re-edit) - Hold by " . ($sales->holder->name ?? 'Unknown Holder') 
                        : $sales->status;
            @endphp
            <span>
                <strong class="title">Status:</strong> 
                <span class="detail-value badge px-2 text-center">{{ $status }}</span>
            </span>
        </div>
    </div>
    @if($sales->order_source)
        <div class="row mx-0 tx-uppercase">
            <a class="btn btn-success" href="{{ asset('storage/' . $sales->order_source) }}" download>
                <i class="fa fa-download"></i> Download attachment
            </a>
        </div>
    @endif
    <form id="issuanceForm" method="POST" action="{{ route('mrs.update') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="sales_header_id" value="{{ $salesDetails->first()->sales_header_id }}">
        <div class="row row-sm" style="overflow-x: auto">
            <table class="table mg-b-10">
                <thead>
                    <tr style="background-color: #f2f2f2; color: #333; border-bottom: 2px solid #ccc;">
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">STATUS</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item#</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Priority#</th>
                        <th width="30%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Stock Code</th>
                        <th class="text-left" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">OEM No.</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Cost Code</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Requested Qty</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Qty to Order</th>
                        <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Previous PO#</th>
                        @if ($sales->received_at)
                            <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Current PO#</th>
                            <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">PO Date Released</th>
                            <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Balance</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $gross = 0; $discount = 0; $subtotal = 0; $count = 0; @endphp

                    @forelse($salesDetails as $details)

                        @php
                        $discount = \App\Models\Ecommerce\CouponSale::total_product_discount($sales->id,$details->product_id,$details->qty,$details->price);
                        $product_subtotal = $details->price*$details->qty;

                        $subtotal += $product_subtotal;

                        $bal = ($details->qty - $details->issuances->sum('qty'));
                        $count++;
                        @endphp
                        
                        <input type="hidden" name="ecommerce_sales_details_id{{ $details->id }}" value="{{ $details->id }}">
                        <input type="hidden" name="ordered_qty{{ $details->id }}" value="{{ $details->qty }}">
                        
                        <tr class="pd-20" style="border-bottom: none;">
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
                                <label class="switch">
                                    <input type="hidden" name="is_hold{{ $details->id }}" value="0">
                                    <input type="checkbox" id="checkbox-{{ $details->id }}" name="is_hold{{ $details->id }}" value="1" {{ $details->promo_id == 0 ? '' : 'checked' }} {{ $role->name === "MCD Planner" ? '' : 'disabled' }}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$count}}</td>
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$sales->priority}}</td>
                            <td class="tx-right" style="padding: 10px; text-align: right; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$details->product->code}}</td>
                            <td class="tx-nowrap" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$details->product_name}}</td>
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$details->product->oem}}</td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$details->cost_code}}</td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{ (int)$details->qty }}</td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
                                <input type="number" data-qty="{{ (int)$details->qty }}" name="quantityToOrder{{ $details->id }}" value="{{ $details->qty_to_order > 0 ? (int)$details->qty_to_order : (int)$details->qty }}" class="form-control qty_order" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }} required>
                            </td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
                                <input type="text" name="previous_no{{ $details->id }}" value="{{ $details->previous_mrs }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }} required>
                            </td>

                            @if ($sales->received_at)
                                <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{ $details->po_no }}</td>
                                <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{ \Carbon\Carbon::parse($details->po_date_released)->format('m/d/Y') }}</td>
                                <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{ ((int)$details->qty_to_order - (int)$details->qty_ordered) }}</td>
                            @endif

                            {{--  
                            <td class="tx-right">
                                <input type="text" name="open_po{{ $details->id }}" value="{{ $details->open_po }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>

                            --}}
                        </tr>
                        <tr class="pd-20">
                            <td colspan="3" style="border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
                                <textarea onblur="onHoldRemarks('{{ $details->id }}', this.value);" name="hold_desc{{ $details->id }}" id="textarea-{{ $details->id }}" placeholder="Type hold remarks here..." style="width: 100%; height: 80px; border: 1px solid #C0C0C0; resize: none;">{{ $details->promo_description }}</textarea>
                            </td>
                            <td class="tx-right" style="padding: 10px; text-align: right; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
                                <span class="title2">PAR TO: </span><br>
                                <span class="title2">FREQUENCY: </span><br>
                                <span class="title2">DATE NEEDED: </span><br>
                                <span class="title2">PURPOSE: </span>
                            </td>
                            <td colspan="{{ $sales->received_at ? 9 : 6 }}" class="tx-left" style="padding: 10px; text-align: left; border: 1px solid #ddd; background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
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
            <div class="col-lg-4">
                <div class="form-group">
                    @if ($role->name === "MCD Verifier" || $role->name === "MCD Approver")
                        <span class="title">PLANNER REMARKS</span>
                        <textarea id="planner_remarks" class="form-control mt-2" placeholder="Add note..." disabled>{{ $sales->planner_remarks }}</textarea>
                        <br><br>
                     @endif
                    @if ($role->name === "MCD Verifier")
                        <span class="title">NOTE FOR PLANNER</span>
                        <textarea id="note_verifier" class="form-control mt-2" placeholder="Add note...">{{ $sales->note_verifier }}</textarea>
                        <button type="button" id="verifyVerifierBtn" class="btn btn-success mt-2" style="width: 140px; text-transform: uppercase;" {{ $sales->status === 'Verified (MCD Verifier) - PA For MCD Manager Approval' ? 'disabled' : '' }}>{{ $sales->status === 'Verified (MCD Verifier) - PA For MCD Manager Approval' ? 'Verified' : 'Verify' }}</button>
                        <button type="button" id="holdVerifierBtn" class="btn btn-danger mt-2 " style="width: 140px; text-transform: uppercase; float: right;">Hold</button>
                     @endif
                     @if ($role->name === "MCD Planner"/* && !$sales->received_at*/)
                        <span class="title">NOTE FOR USER</span>
                        <textarea id="note" class="form-control mt-2" placeholder="Add note...">{{ $sales->note_planner }}</textarea>
                        <a href="#" id="holdPlannerBtn" class="btn btn-danger mt-2" style="width: 140px; text-transform: uppercase;">Hold</a>
                        <br><br>
                    @endif
                    @if ($role->name === "MCD Planner" && ($sales->status === "HOLD (For MCD Planner re-edit)" || $sales->status === "RECEIVED FOR CANVASS (Purchasing Officer)"))
                        @if(!$sales->received_at)
                            @if($sales->note_verifier)
                                <span class="title">NOTE FROM VERIFIER</span>
                                <textarea class="form-control mt-2" placeholder="Add note..." disabled>{{ $sales->note_verifier }}</textarea><br><br>
                            @endif
                            @if($sales->note_myrna)
                                <span class="title">NOTE FROM APPROVER</span>
                                <textarea class="form-control mt-2" placeholder="Add note..." disabled>{{ $sales->note_myrna }}</textarea>
                            @endif
                            @if($sales->purchaser_note)
                                <span class="title">NOTE FROM PURCHASING</span>
                                <textarea class="form-control mt-2" placeholder="Add note..." disabled>{{ $sales->purchaser_note }}</textarea>
                            @endif        
                        @else
                            @if($sales->purchaser_note)
                                <span class="title">NOTE FROM PURCHASER</span>
                                <textarea class="form-control mt-2" placeholder="Add note..." disabled>{{ $sales->purchaser_note }}</textarea>
                            @endif
                        @endif
                    @endif

                    @if ($role->name === "MCD Approver")
                        <span class="title">NOTE FOR PLANNER</span>
                        <textarea id="note_approver" class="form-control" placeholder="Add note...">{{ $sales->note_myrna }}</textarea>
                        <button type="button" id="approverApproverBtn" class="btn btn-success mt-2" style="width: 140px; text-transform: uppercase;" {{ $sales->status === 'APPROVED (MCD Approver) - PA for Delegation' || $sales->status === 'RECEIVED FOR CANVASS (Purchasing Officer)' ? 'disabled' : '' }}>{{ $sales->status === 'APPROVED (MCD Approver) - PA for Delegation' || $sales->status === 'RECEIVED FOR CANVASS (Purchasing Officer)' ? 'APPROVED' : 'APPROVE' }}</button>
                        <button type="button" id="holdApproverBtn" class="btn btn-danger mt-2" style="width: 140px; text-transform: uppercase; float: right;">Hold</button>
                     @endif
                </div>
            </div>
            <div class="col-lg-4">
            </div>
            <div class="col-lg-4">
                <div class="form-group text-right">
                    @if ($role->name === "MCD Planner")
                        <span class="title">PLANNER REMARKS</span>
                        <textarea id="planner_remarks" class="form-control mt-2" name="planner_remarks" placeholder="Add note..." {{ $sales->status === 'APPROVED (MCD Planner) - MRS For Verification' || $sales->status === 'Verified (MCD Verifier) - PA For MCD Manager Approval' || $sales->received_at ? 'disabled' : '' }}>{{ $sales->planner_remarks }}</textarea>
                        <button type="submit" class="mt-2 btn {{ ($sales->status === 'APPROVED (MCD Planner) - MRS For Verification' || $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL') ? 'btn-success' : 'btn-success'}}" style="width: 140px; text-transform: uppercase;" {{ $sales->status === 'APPROVED (MCD Planner) - MRS For Verification' || $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL' || $sales->received_at ? 'disabled' : '' }}>{{ $sales->status === 'APPROVED (MCD Planner) - MRS For Verification' || $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL' || $sales->received_at ? 'SUBMITTED' : 'PROCEED'}}</button><br><br>
                        @if ($sales->received_at)
                            <button type="submit" class="btn btn-primary" style="width: 140px; text-transform: uppercase;">UPDATE</button><br><br>
                        @endif
                    @endif
                    @if($sales->for_pa == 1 && $sales->is_pa == 1)
                        <button type="button" class="btn btn-info print" data-order-number="{{$sales->order_number}}" style="width: 140px; text-transform: uppercase;" {{ $sales->purchaseAdvice->is_hold == 0 || $sales->purchaseAdvice->is_hold == NULL ? '' : 'disabled' }}>PRINT PA</button><br>
                        <small class="text-danger">({{ $sales->purchaseAdvice->is_hold == 0 || $sales->purchaseAdvice->is_hold == NULL ? '' : 'PURCHASE ADVICE ON-HOLD' }})</small>
                    @endif
                </div>
            </div>
        </div>
    </form>
    @include('admin.ecommerce.sales.modals')
</div>
@endsection

@section('pagejs')
    <script>
        function issuanceSubmit() {
            $('#issuanceForm').submit();
        }

        function onHoldRemarks(id, value){
            let data = {
                        id: id,
                        promo_id: $('#checkbox-'+id).is(':checked') ? 1 : 0,
                        promo_description: value,
                        "_token": "{{ csrf_token() }}"
                    }
            updateItemStatus(data);
        }

        function updateItemStatus(data){
            $.ajax({
                url: "{{ route('item.hold') }}",
                type: 'POST',
                data: data,
                success: function(response){
                    console.log(response)
                }
            })
        }

        $(document).ready(function() {
            @foreach($salesDetails as $details)
                @if($details->promo_id == 1)
                    $("#textarea-{{ $details->id }}").slideDown();
                @else
                    $("#textarea-{{ $details->id }}").slideUp();
                @endif
                $('#checkbox-{{ $details->id }}').change(function() {
                    if ($(this).is(':checked')) {
                        $("#textarea-{{ $details->id }}").slideDown();
                    } else {
                        $("#textarea-{{ $details->id }}").slideUp();
                    }
                    let data = {
                        id: '{{ $details->id }}',
                        promo_id: $(this).is(':checked') ? 1 : 0,
                        "_token": "{{ csrf_token() }}"
                    }
                    updateItemStatus(data);
                });
            @endforeach

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
                $('#printModal').modal('show');
                $('#generateReportBtn').click(function() {
                    var selectedFormat = $('input[name="fileFormat"]:checked').val();
                    if (selectedFormat === 'pdf') {
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
                            },
                            error: function(xhr, status, error) {
                                console.error("Error generating PDF:", error);
                            }
                        });
                    } else if (selectedFormat === 'excel') {
                        window.location.href = "{{ route('pa.generate_report_pa_excel') }}?orderNumber=" + orderNumber;
                    }
                    $('#printModal').modal('hide');
                });
            }); 

            $(".qty_order").on("keyup", function(){
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

            //PLANNERS ACTION
            $('#holdPlannerBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note').val());
                var url = "{{ route('mrs.action', ['action' => 'hold-planner', 'id' => $sales->id]) }}&note=" + note;
                window.location.href = url;
            });
            //

            //VERIFIERS ACTION
            $('#verifyVerifierBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note_verifier').val());
                var url = "{{ route('mrs.action', ['action' => 'verify', 'id' => $sales->id]) }}&note=" + note;
                window.location.href = url;
            });
            $('#holdVerifierBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note_verifier').val());
                var url = "{{ route('mrs.action', ['action' => 'hold', 'id' => $sales->id]) }}&note=" + note;
                window.location.href = url;
            });
            //

            //APPROVERS ACTION
            $('#approverApproverBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note_approver').val());
                var url = "{{ route('mrs.action', ['action' => 'approve-approver', 'id' => $sales->id]) }}&note=" + note;
                window.location.href = url;
            });
            $('#holdApproverBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note_approver').val());
                var url = "{{ route('mrs.action', ['action' => 'hold-approver', 'id' => $sales->id]) }}&note=" + note;
                window.location.href = url;
            });
            //
        });
    </script>
@endsection