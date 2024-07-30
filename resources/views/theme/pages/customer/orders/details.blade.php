@extends(auth()->check() ? 'theme.main' : 'theme.main-blank-template')

@section('content')
    <div class="container content-wrap">
        <div class="row">
            <div class="col-md-12">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Request No. {{ $order->order_number }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <span><strong>Request Date: </strong>{{ $order->created_at }}</span><br>
                            <span><strong>Request Status: </strong>{{ $order->status }}</span><br>
                            <span><strong>Department:</strong> {{ $order->user->department->name }} </span><br>
                            <span><strong>Section:</strong> {{ $order->section }}</span><br>
                            <span><strong>Date Needed:</strong> {{ $order->delivery_date }}</span><br>
                            <span><strong>Requested By:</strong> {{ $order->user->name }}</span>
                        </div>
                        <div class="col-lg-6">
                            <span><strong>Delivery Type:</strong> {{ $order->delivery_type }}</span><br>
                            <span><strong>Delivery Address:</strong> {{ $order->customer_delivery_adress }}</span><br>
                            <span><strong>Budgeted:</strong> {{ $order->budgeted_amount > 0 ? 'YES' : 'NO' }}</span><br>
                            <span><strong>Budgeted Amount:</strong> {{ number_format($order->budgeted_amount, 2, '.', ',') }}</span><br>
                            <span><strong>Other Instructions:</strong> {{ $order->other_instruction }}</span><br>
                            <span><strong>Purpose:</strong> {{ $order->purpose }}</span>
                        </div>
                    </div>
                    <br><br>
                    <div class="table-modal-wrap">
                        <table class="table table-md table-modal" style="font-size:12px !important;">
                            <thead>
                                <tr>
                                    <th>Stock Code</th>
                                    <th>Item</th>
                                    <th>Cost Code</th>
                                    <th>OEM</th>
                                    <th>UoM</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php

                                    $total_qty = 0;
                                    $total_sales = 0;
                                @endphp
                                
                                @foreach($order->items as $item)
                                    @php
                                        $total_qty += $item->qty;
                                        $total_sales += $item->qty * $item->price;
                                    @endphp
                                    
                                    <tr>
                                        <td>{{ $item->product->code }}</td>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->cost_code }}</td>
                                        <td>{{ $item->product->oem }}</td>
                                        <td>{{ $item->product->uom }}</td>
                                        <td>{{ $item->qty }}</td>
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