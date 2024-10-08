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

    <form id="issuanceForm" method="POST" action="{{ route('mrs.update') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="sales_header_id" value="{{ $salesDetails->first()->sales_header_id }}">
        <div class="row row-sm" style="overflow-x: auto">
            <table class="table mg-b-10">
                <thead>
                    <tr style="background-color: #f2f2f2; color: #333; border-bottom: 2px solid #ccc;">
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
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$count}}</td>
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$sales->priority}}</td>
                            <td class="tx-right" style="padding: 10px; text-align: right; border: 1px solid #ddd;">{{$details->product->code}}</td>
                            <td class="tx-nowrap" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product_name}}</td>
                            <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product->oem}}</td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->cost_code}}</td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ (int)$details->qty }}</td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                <input type="number" data-qty="{{ (int)$details->qty }}" name="quantityToOrder{{ $details->id }}" value="{{ $details->qty_to_order > 0 ? (int)$details->qty_to_order : (int)$details->qty }}" class="form-control qty_order" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }} required>
                            </td>
                            <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                <input type="text" name="previous_no{{ $details->id }}" value="{{ $details->previous_mrs }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }} required>
                            </td>

                            @if ($sales->received_at && $role->name === "MCD Planner")
                                <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ $details->po_no }}</td>
                                <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($details->po_date_released)->format('m/d/Y') }}</td>
                                <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ ((int)$details->qty_to_order - (int)$details->qty_ordered) }}</td>
                            @endif

                            {{--  
                            <td class="tx-right">
                                <input type="text" name="open_po{{ $details->id }}" value="{{ $details->open_po }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>

                            --}}
                        </tr>
                        <tr class="pd-20">
                            <td colspan="3" class="tx-right" style="padding: 10px; text-align: right; border: 1px solid #ddd;">
                                <span class="title2">PAR TO: </span><br>
                                <span class="title2">FREQUENCY: </span><br>
                                <span class="title2">DATE NEEDED: </span><br>
                                <span class="title2">PURPOSE: </span>
                            </td>
                            <td colspan="{{ $sales->received_at ? 9 : 6 }}" class="tx-left" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
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
            <div class="col-8">
            </div>
            <div class="col-4">
                @if($sales->budgeted_amount > 0)
                    {{--  
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
                    --}}
                @endif
            </div>
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
                        <button type="button" id="verifyVerifierBtn" class="btn btn-success mt-2" style="width: 140px; text-transform: uppercase;" {{ $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL' ? 'disabled' : '' }}>{{ $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL' ? 'Verified' : 'Verify' }}</button>
                        <button type="button" id="holdVerifierBtn" class="btn btn-danger mt-2 " style="width: 140px; text-transform: uppercase; float: right;">Hold</button>
                     @endif
                     @if ($role->name === "MCD Planner" && !$sales->received_at)
                        <span class="title">NOTE FOR USER</span>
                        <textarea id="note" class="form-control mt-2" placeholder="Add note...">{{ $sales->note_planner }}</textarea>
                        <a href="#" id="holdPlannerBtn" class="btn btn-danger mt-2" style="width: 140px; text-transform: uppercase;">Hold</a>
                        <br><br>
                    @endif
                    @if ($role->name === "MCD Planner" && $sales->status === "HOLD (For MCD Planner re-edit)")
                        <span class="title">NOTE FROM VERIFIER</span>
                        <textarea class="form-control mt-2" placeholder="Add note..." disabled>{{ $sales->note_verifier }}</textarea><br><br>
                        <span class="title">NOTE FROM APPROVER</span>
                        <textarea class="form-control mt-2" placeholder="Add note..." disabled>{{ $sales->note_myrna }}</textarea>
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
                        <textarea id="planner_remarks" class="form-control mt-2" name="planner_remarks" placeholder="Add note..." {{ $sales->status === 'APPROVED (MCD Planner) - MRS For Verification' || $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL' || $sales->received_at ? 'disabled' : '' }}>{{ $sales->planner_remarks }}</textarea>
                        <button type="submit" class="mt-2 btn {{ ($sales->status === 'APPROVED (MCD Planner) - MRS For Verification' || $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL') ? 'btn-success' : 'btn-success'}}" style="width: 140px; text-transform: uppercase;" {{ $sales->status === 'APPROVED (MCD Planner) - MRS For Verification' || $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL' || $sales->received_at ? 'disabled' : '' }}>{{ $sales->status === 'APPROVED (MCD Planner) - MRS For Verification' || $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL' ? 'SUBMITTED' : 'PROCEED'}}</button><br><br>
                     @endif
                    @if($sales->for_pa == 1 && $sales->is_pa == 1)
                        <a href="#" class="btn btn-info print" data-order-number="{{$sales->order_number}}" style="width: 140px; text-transform: uppercase;">PRINT PA</a>
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