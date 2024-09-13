@extends(auth()->check() ? 'theme.main' : 'theme.main-blank-template')

@section('pagecss')
    
    <style>
        .request-details {
            display: table;
        }

        .request-details span {
            display: table-row;
        }

        .request-details strong {
            display: table-cell;
            padding-right: 15px;
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
    <div class="container-fluid content-wrap">
        <div class="row">
            <div class="col-md-12">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Request No. {{ $order->order_number }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="request-details">
                                <span><strong>Request Date: </strong> <span class="detail-value">{{ $order->created_at }}</span></span>
                                <span><strong>Request Status: </strong> <span class="detail-value">{{ $order->status }}</span></span>
                                <span><strong>Department:</strong> <span class="detail-value">{{ $order->user->department->name }} </span></span>
                                <span><strong>Section:</strong> <span class="detail-value">{{ $order->section }}</span></span>
                                <span><strong>Date Needed:</strong> <span class="detail-value"> {{ $order->delivery_date }}</span></span>
                                <span><strong>Requested By:</strong> <span class="detail-value"> {{ $order->requested_by }}</span></span>
                                <span><strong>Processed By:</strong> <span class="detail-value"> {{ $order->user->name }}</span></span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="request-details">
                                <span><strong>Delivery Type:</strong> <span class="detail-value"> {{ $order->delivery_type }}</span></span>
                                <span><strong>Delivery Address:</strong> <span class="detail-value"> {{ $order->customer_delivery_adress }}</span></span>
                                <span><strong>Budgeted:</strong> <span class="detail-value"> {{ $order->budgeted_amount > 0 ? 'YES' : 'NO' }}</span></span>
                                <span><strong>Budgeted Amount:</strong> <span class="detail-value"> {{ number_format($order->budgeted_amount, 2, '.', ',') }}</span></span>
                                <span><strong>Other Instructions:</strong> <span class="detail-value"> {{ $order->other_instruction }}</span></span>
                                <span><strong>Purpose:</strong> <span class="detail-value"> {{ $order->purpose }}</span></span>
                            </div>
                        </div>
                    </div>
                    <br><br>
                    <div class="table-modal-wrap">
                        <table class="table table-md table-modal" style="font-size:12px !important;">
                            <thead>
                                <tr>
                                    <th>ITEM #</th>
                                    <th>Priority</th>
                                    <th>Stock Code</th>
                                    <th>Item</th>
                                    <th>OEM</th>
                                    <th>UoM</th>
                                    <th>PAR To</th>
                                    <th>Frequency</th>
                                    <th>Purpose</th>
                                    <th>Cost Code</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php

                                    $total_qty = 0;
                                    $total_sales = 0;
                                    $count = 0;
                                @endphp
                                
                                @foreach($order->items as $item)
                                    @php
                                        $count++;
                                        $total_qty += $item->qty;
                                        $total_sales += $item->qty * $item->price;
                                    @endphp
                                    <tr>
                                        <td>{{ $count }}</td>
                                        <td>{{ $order->priority }}</td>
                                        <td>{{ $item->product->code }}</td>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->product->oem ?? "NONE" }}</td>
                                        <td>{{ $item->product->uom }}</td>
                                        <td>{{ (explode(':', $item->par_to)[0]) ? (explode(':', $item->par_to)[0]) : "NONE" }}</td>
                                        <td>{{ $item->frequency }}</td>
                                        <td>{{ $item->purpose }}</td>
                                        <td>{{ $item->cost_code }}</td>
                                        <td>{{ (int)$item->qty }}</td>
                                    </tr>
                                @endforeach

                                @php
                                    
                                    $delivery_discount = \App\Models\Ecommerce\CouponSale::total_discount_delivery($order->id);
                                    $grossAmount = ($total_sales-$order->discount_amount)+($order->delivery_fee_amount-$delivery_discount);

                                @endphp

                                
                            </tbody>
                        </table>
                    </div>
                    <div class="gap-20"></div>
                </div>
            </div>
        </div>
    </div>
@endsection