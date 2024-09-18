<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @font-face {
            font-family: 'Lora';
            src: url('{{ public_path('fonts/lora/Lora-Regular.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Lora';
            src: url('{{ public_path('fonts/lora/Lora-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body {
            margin: 0mm !important;
            font-family: 'Lora', serif;
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
            font-size: 10px;
        }

        strong {
            display: inline-block;
            width: 100px;
        }

        .title-item {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        .title-item strong {
            margin-right: 10px;
        }

        .status-label {
            background-color: #28a745;
            color: white;
            border-radius: 12px;
            padding: 3px 8px;
            font-size: 10px;
        }
    </style>
</head>
<body> 
<div class="container-fluid content-wrap">
    <h4 class="modal-title" id="myModalLabel">Request No. {{ $sale->order_number }}</h4>
    <table style="margin-top: 15px">
        <thead>
            <tr>
                <td colspan="10" class="text-bold text-align-center header-style">MRS Transaction Summary</td>
            </tr>
            <tr style="border: 1px solid #dddddd">
                <td colspan="5" class="title" style="border: 0">
                    <div class="title-item"><strong class="text-uppercase">Request Date:</strong> {{ $sale->created_at }} </div>
                    <div class="title-item"><strong class="text-uppercase">Request Status:</strong> {{ $sale->status }} </div>
                    <div class="title-item"><strong class="text-uppercase">Department:</strong> {{ $sale->user->department->name }}</div>
                    <div class="title-item"><strong class="text-uppercase">Section:</strong> {{ $sale->section }}</div>
                    <div class="title-item"><strong class="text-uppercase">Date Needed:</strong> {{ $sale->delivery_date }}</div>
                    <div class="title-item"><strong class="text-uppercase">Requested By:</strong> {{ $sale->requested_by }}</div>
                    <div class="title-item"><strong class="text-uppercase">Processed By:</strong> {{ strtoupper($sale->user->name) }}</div>
                </td>
                <td colspan="5" class="title" style="border: 0">
                    <div class="title-item"><strong class="text-uppercase">Delivery Type:</strong> {{ $sale->delivery_type }} </div>
                    <div class="title-item"><strong class="text-uppercase">Delivery Address:</strong>  {{ $sale->customer_delivery_adress }} </div>
                    <div class="title-item"><strong class="text-uppercase">Budgeted:</strong>  {{ $sale->budgeted_amount > 0 ? 'YES' : 'NO' }} </div>
                    <div class="title-item"><strong class="text-uppercase">Budgeted Amount: </strong> {!! number_format($sale->budgeted_amount, 2, '.', ',') !!} </div> 
                    <div class="title-item"><strong class="text-uppercase">Notes:</strong> {{ $sale->other_instruction }} </div> 
                    <div class="title-item"><strong class="text-uppercase">Purpose:</strong>  {{ $sale->purpose }} </div>
                    <div class="title-item"><strong class="text-uppercase">Status:</strong>  <span class="status-label">{{ $sale->status }}</span> </div>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f2f2f2; color: #333; border-bottom: 2px solid #ccc;">
                <th width="5%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item#</th>
                <th width="5%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Priority#</th>
                <th width="5%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Stock Code</th>
                <th width="25%" class="text-left" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item</th>
                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">OEM No.</th>
                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Cost Code</th>
                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Requested Qty</th>
                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Qty to Order</th>
                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Previous PO#</th>
                @if ($sale->received_at)
                    <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Balance</th>
                @endif
                {{--  
                @if ($sale->status != "COMPLETED")
                    <th class="d-none" width="1%">Issuance Quantity</th>
                @endif
                --}}
            </tr>
            @php 
                $count = 0;
            @endphp

            @forelse($sale->items as $item)
                @php
                    $count++;
                @endphp
                <tr class="pd-20">
                    <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$count}}</td>
                    <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$sale->priority}}</td>
                    <td class="tx-right" style="padding: 10px; text-align: right; border: 1px solid #ddd;">{{$item->product->code}}</td>
                    <td class="tx-nowrap" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$item->product_name}}</td>
                    <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$item->product->oem}}</td>
                    <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$item->cost_code}}</td>
                    <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ (int)$item->qty }}</td>
                    <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ (int)$item->qty_to_order }}</td>
                    <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item->previous_mrs }}</td>
                    @if ($sale->received_at)
                        <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd;">{{ ((int)$item->qty_to_order - (int)$item->qty_ordered) }}</td>
                    @endif
                </tr>
                <tr class="pd-20">
                    <td colspan="3" style="padding: 10px; text-align: right; border: 1px solid #ddd;">
                        <spa>PAR TO: </span><br>
                        <span>FREQUENCY: </span><br>
                        <span>DATE NEEDED: </span><br>
                        <span>PURPOSE: </span>
                    </td>
                    <td colspan="{{ $sale->received_at ? 7 : 6 }}" class="tx-left" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                        {{$item->par_to}}<br>
                        {{$item->frequency}}<br>
                        {{ \Carbon\Carbon::parse($item->date_needed)->format('m/d/Y') }}<br>
                        {{$item->purpose}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="tx-center" colspan="5">No transaction found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</body>
</html>