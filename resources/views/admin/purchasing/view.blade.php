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
                    <li class="breadcrumb-item active" aria-current="page"><a href="{{route('pa.index')}}">Order Transaction</a></li>
                </ol>
            </nav>
            <h4 class="mt-4 mg-b-0 tx-spacing--1"> MRS# {{$sales->order_number}} Transaction Summary</h4>
        </div>
        <div>
            <a href="#" id="printDetails" class="btn btn-success btn-sm" data-order="{{$sales->id}}">
                <i class="fas fa-print"></i> Print
            </a>
            <a href="{{ route('pa.index') }}" class="btn btn-secondary btn-sm">Back to Transaction List</a>
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
            <span><strong class="title">Note:</strong> <span class="detail-value">{{ $sales->purpose}}</span></span>
            <span><strong class="title">Status:</strong> <span class="detail-value badge px-2 text-center">{{ $sales->status}}</span></span>
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
                    <tr>
                        <th width="10%">STATUS</th>
                        <th width="10%">Item#</th>
                        <th width="10%">Priority#</th>
                        <th width="10%" class="text-right">Stock Code</th>
                        <th class="text-left">Item</th>
                        <th width="10%">OEM No.</th>
                        <th width="10%">Cost Code</th>
                        <th width="10%">Qty to Order</th>
                        @if ($sales->status != "COMPLETED")
                            <th class="d-none" width="1%">Issuance Quantity</th>
                        @endif
                        <th width="10%">Previous PO#</th>
                        {{-- <th width="10%">On Order</th>  --}}
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
                            <td class="tx-center" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
                                <label class="switch">
                                    <input type="hidden" name="is_hold{{ $details->id }}" value="0">
                                    <input type="checkbox" name="is_hold{{ $details->id }}" value="1" {{ $details->promo_id == 0 ? '' : 'checked' }}  {{ $role->name === "MCD Planner" ? '' : 'disabled' }}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td class="tx-center" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$count}}</td>
                            <td class="tx-center" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$sales->priority}}</td>
                            <td class="tx-right" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$details->product->code}}</td>
                            <td class="tx-nowrap" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$details->product_name}}</td>
                            <td class="tx-center" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$details->product->oem}}</td>
                            <td class="tx-right" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">{{$details->cost_code}}</td>
                            <td class="tx-right" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
                                <input type="number" name="quantityToOrder{{ $details->id }}" value="{{ $details->qty_to_order > 0 ? (int)$details->qty_to_order : (int)$details->qty }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>
                            <td class="tx-right" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
                                <input type="text" name="previous_no{{ $details->id }}" value="{{ $details->previous_mrs }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>

                            {{--  
                            <td class="tx-right">
                                <input type="text" name="open_po{{ $details->id }}" value="{{ $details->open_po }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>
                            --}}
                        </tr>
                        <tr class="pd-20">
                            <td colspan="4" class="tx-right" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
                                <span class="title2">PAR TO: </span><br>
                                <span class="title2">FREQUENCY: </span><br>
                                <span class="title2">DATE NEEDED: </span><br>
                                <span class="title2">PURPOSE: </span>
                            </td>
                            <td colspan="5" class="tx-left" style="background-color: {{ $details->promo_id === '0' ? '' : '#E9EAEC' }};">
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
            <div class="col-3 request-details">
                <span><strong class="title">Assign To:</strong> 
                    <select type="text" class="form-control employees" id="purchasers">
                        @foreach ($purchasers as $purchaser)
                            <option value="{{ $purchaser->id }}" {{ $purchaser->id == $sales->received_by ? 'selected' : '' }}>{{ $purchaser->name }}</option>
                        @endforeach
                    </select>
                </span><br>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-group"> 
                    <a href="#" id="receiveBtn" class="btn btn-success mt-2" style="width: 140px; text-transform: uppercase;">Assign</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <span class="title">NOTE FOR PLANNER</span>
                    <textarea id="note" class="form-control mt-2" placeholder="Add note...">{{ $sales->purchaser_note }}</textarea>
                    <a href="#" id="holdPurchaserBtn" class="btn btn-danger mt-2" style="width: 140px; text-transform: uppercase;">Hold</a>
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

            $('#receiveBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#purchasers').val());
                var url = "{{ route('mrs.action', ['action' => 'mrs-assign', 'id' => $sales->id]) }}&note=" + note;
                window.location.href = url;
            });

            $('#holdPurchaserBtn').click(function(event) {
                event.preventDefault();
                var note = encodeURIComponent($('#note').val());
                var url = "{{ route('pa.action', ['action' => 'hold-purchaser', 'id' => $sales->id]) }}&note=" + note;
                window.location.href = url;
            });
            
        });

        function employee_lookup() {
            $.ajax({
                type: 'GET',
                url: "{{ route('users.employee_lookup') }}",
                success: function(data){
                    try {
                        var employeesArray = JSON.parse(data);
                        console.log(employeesArray[0]);
                        $('.employees').empty();
                        $('.employees').append('<option value="" disabled selected>Select an employee</option>');
                        employeesArray.forEach(function(employee) {
                            var fullname = employee.fullname.replace(/\*/g, ' ');
                            $('.employees').append('<option value="' + employee.fullnamewithdept + '">' + fullname + '</option>');
                        });
                    } catch (e) {
                        console.error("Error parsing JSON: ", e);
                    }
                }
            });
        }
    </script>
@endsection