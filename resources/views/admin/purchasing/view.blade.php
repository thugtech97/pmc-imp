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
                        <th width="10%">Requested Qty</th>
                        <th width="10%">Qty to Order</th>
                        @if ($sales->status != "COMPLETED")
                            <th class="d-none" width="1%">Issuance Quantity</th>
                        @endif
                        <th width="10%">Previous#</th>
                        <th width="10%">OpenPO</th>
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
                                <input type="number" name="quantityToOrder{{ $details->id }}" value="{{ $details->qty_to_order > 0 ? $details->qty_to_order : $details->qty }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>
                            <td class="tx-right">
                                <input type="text" name="previous_no{{ $details->id }}" value="{{ $details->previous_mrs }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>
                            <td class="tx-right">
                                <input type="text" name="open_po{{ $details->id }}" value="{{ $details->open_po }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'disabled' : '' }}>
                            </td>
                        </tr>
                        <tr class="pd-20">
                            <td class="tx-left"><span class="title">PURPOSE: </span></td>
                            <td colspan="7" class="tx-left">{{$details->purpose}}</td>
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
                <span><strong class="title">Received by:</strong> 
                    <select type="text" class="form-control employees" id="purchasers">
                        @foreach ($purchasers as $purchaser)
                            <option value="{{ $purchaser->name }}">{{ $purchaser->name }}</option>
                        @endforeach
                    </select>
                </span><br>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-group"> 
                    <a href="#" id="receiveBtn" class="btn btn-success mt-2" style="width: 140px; text-transform: uppercase;">Receive</a>
                </div>
            </div>
        </div>
    </form>
    
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
                var url = "{{ route('mrs.action', ['action' => 'mrs-receive', 'id' => $sales->id]) }}&note=" + note;
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