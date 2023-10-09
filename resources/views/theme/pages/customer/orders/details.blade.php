@extends('theme.main')

@section('content')
    <div class="container content-wrap">
        <div class="row">
            <div class="col-md-12">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Order Number: {{ $order->order_number }}</h4>
                </div>
                <div class="modal-body">
                    <div class="transaction-status">
                        <span><strong>Order Date: </strong>{{ $order->created_at }}</span><br>
                        <span><strong>Order Status: </strong>{{ $order->status }}</span><br>
                    </div>
                    <div class="gap-20">
                        <span><strong>Delivery Type: </strong>{{ $order->delivery_type }}</span><br>
                        <span><strong>Notes: </strong>{{ $order->other_instruction }}</span><br>
                    </div>
                    <br><br>
                    <div class="table-modal-wrap">
                        <table class="table table-md table-modal" style="font-size:12px !important;">
                            <thead>
                                <tr>
                                    <th>Item</th>
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
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->qty . ' ' . $item->uom }}</td>
                                    </tr>
                                @endforeach

                                @php
                                    
                                    $delivery_discount = \App\Models\Ecommerce\CouponSale::total_discount_delivery($order->id);
                                    $grossAmount = ($total_sales-$order->discount_amount)+($order->delivery_fee_amount-$delivery_discount);

                                @endphp

                                <tr style="font-weight:bold;">
                                    <td>Sub total</td>
                                    <td>{{ number_format($total_qty,2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="gap-20"></div>
                </div>
            </div>
        </div>
    </div>
@endsection