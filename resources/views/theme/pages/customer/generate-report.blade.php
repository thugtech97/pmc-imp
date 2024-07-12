<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0mm !important;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        th {
            font-size: 10px;
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-align-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold
        }
        .header-style {
            font-size: 16px;
        }
        .title {
            font-size: 11px
        }
        .text-uppercase{
            font-weight: bold;
        }
        .content-wrap {
            position: relative;
        }
        .status {
            position: absolute;
            top: 0;
            right: 0;
        }
        tbody {
            font-size: 9px;
        }
    </style>
</head>
<body>  
<div class="container-fluid content-wrap">
    <h4 class="modal-title" id="myModalLabel">Request No. {{ $sale->order_number }}</h4>
    <table style="margin-top: 15px">
        <thead>
            <tr>
                <td colspan="2" class="text-bold text-align-center header-style">MRS - For Purchase</td>
            </tr>
            <tr style="border: 1px solid #dddddd">
                <td class="title" style="border: 0">
                    <div><span class="text-uppercase">Order Date:</span> {{ $sale->created_at }} </div>
                    <div><span class="text-uppercase">Request Status:</span>  {{ strtoupper($sale->status) }} </div>
                    <div><span class="text-uppercase">Delivery Date:</span> {{  $sale->delivery_date }}</div>
                </td>
                <td class="title" style="border: 0">
                    <div><span class="text-uppercase">Delivery Type:</span> {{ $sale->delivery_type }} </div>
                    <div><span class="text-uppercase">Delivery Address:</span>  {{ $sale->customer_delivery_adress }} </div>
                    <div><span class="text-uppercase">Other Instructions:</span>  {{ $sale->other_instruction }} </div>   
                </td>
            </tr>
        </thead>
    </table>
    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <thead>
                    <th class="title">Item</th>
                    <th class="title">Qty</th>
                </thead>
                <tbody>
                    @forelse($sale->items as $item)
                    $total_qty += $item->qty;
                    $total_sales += $item->qty * $item->price;
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->qty . ' ' . $item->uom }}</td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>