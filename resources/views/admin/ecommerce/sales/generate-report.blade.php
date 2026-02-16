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
            font-size: 12px
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

        .title-item {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .label {
            display: table-cell;
            font-weight: bold;
            width: 30%;
            padding-right: 20px;
            text-align: left;
        }

        .value {
            display: table-cell;
            flex: 1;
            text-align: left;
        }

        .status-label {
            background-color: #28a745;
            color: white;
            border-radius: 12px;
            padding: 3px 8px;
            font-size: 10px;
        }

        .logo-container {
            position: absolute;
            top: 0;
            right: 0;
        }
        .logo {
            width: 80px;
            height: auto;
        }
    </style>
</head>
<body> 
<div class="container-fluid content-wrap">
    <div class="logo-container">
        <img src="{{ public_path('img/pmc-logo.png') }}" alt="Logo" class="logo">
    </div>
    <h4 class="modal-title" id="myModalLabel">Request No. {{ $sale->order_number }}</h4>
    <table style="margin-top: 15px">
        <thead>
            <tr style="border: 1px solid black;">
                <td colspan="{{ $sale->received_at ? 10 : 9 }}"  style="border: 1px solid black;" class="text-bold text-align-center header-style">MRS Transaction Summary</td>
            </tr>
            <tr style="border: 1px solid black;">
                <td colspan="5" class="title" style="border: 0">
                    <div class="title-item"><div class="label">Request Date:</div> <div class="value">{{ $sale->created_at }}</div></div>
                    <div class="title-item"><div class="label">Request Status:</div> <div class="value">{{ $sale->status }}</div></div>
                    <div class="title-item"><div class="label">Department:</div> <div class="value">{{ optional(optional($sale->user)->department)->name ?? 'N/A' }}</div></div>
                    <div class="title-item"><div class="label">Section:</div> <div class="value">{{ $sale->section }}</div></div>
                    <div class="title-item"><div class="label">Date Needed:</div> <div class="value">{{ $sale->delivery_date }}</div></div>
                    <div class="title-item"><div class="label">Requested By:</div> <div class="value">{{ $sale->requested_by }}</div></div>
                    <div class="title-item"><div class="label">Processed By:</div> <div class="value">{{ strtoupper($sale->user->name) }}</div></div>
                </td>
                <td colspan="{{ $sale->received_at ? 5 : 4 }}" class="title" style="border: 0">
                    <div class="title-item"><div class="label">Delivery Type:</div> <div class="value">{{ $sale->delivery_type }} </div></div>
                    <div class="title-item"><div class="label">Delivery Address:</div>  <div class="value">{{ $sale->customer_delivery_adress }} </div></div>
                    <div class="title-item"><div class="label">Budgeted:</div>  <div class="value">{{ $sale->budgeted_amount > 0 ? 'YES' : 'NO' }} </div></div>
                    <div class="title-item"><div class="label">Budgeted Amount: </div> <div class="value">{!! number_format($sale->budgeted_amount, 2, '.', ',') !!} </div></div>
                    <div class="title-item"><div class="label">Notes:</div> <div class="value">{{ $sale->other_instruction }} </div></div>
                    <div class="title-item"><div class="label">Purpose:</div>  <div class="value">{{ $sale->purpose }} </div></div>
                    <div class="title-item"><div class="label">Status:</div>  <span class="status-label">{{ $sale->status }}</span> </div>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f2f2f2; color: black;">
                <th width="5%" style="padding: 5px; text-align: center; border: 1px solid black;">Item#</th>
                <th width="5%" style="padding: 5px; text-align: center; border: 1px solid black;">Priority#</th>
                <th width="5%" style="padding: 5px; text-align: center; border: 1px solid black;">Stock Code</th>
                <th width="25%" style="padding: 5px; text-align: center; border: 1px solid black;">Item</th>
                <th width="10%" style="padding: 5px; text-align: center; border: 1px solid black;">OEM No.</th>
                <th width="10%" style="padding: 5px; text-align: center; border: 1px solid black;">Cost Code</th>
                <th width="10%" style="padding: 5px; text-align: center; border: 1px solid black;">Requested Qty</th>
                <th width="10%" style="padding: 5px; text-align: center; border: 1px solid black;">Qty to Order</th>
                <th width="10%" style="padding: 5px; text-align: center; border: 1px solid black;">Previous PO#</th>
                @if ($sale->received_at)
                    <th width="10%" style="padding: 5px; text-align: center; border: 1px solid black;">Balance</th>
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
                <tr class="pd-20" style="color: black;">
                    <td class="tx-center" style="padding: 5px; text-align: center; border: 1px solid black;">{{$count}}</td>
                    <td class="tx-center" style="padding: 5px; text-align: center; border: 1px solid black;">{{$sale->priority}}</td>
                    <td class="tx-right" style="padding: 5px; text-align: right; border: 1px solid black;">{{$item->product->code}}</td>
                    <td class="tx-nowrap" style="padding: 5px; text-align: left; border: 1px solid black;">{{$item->product->name}}</td>
                    <td class="tx-center" style="padding: 5px; text-align: center; border: 1px solid black;">{{$item->product->oem}}</td>
                    <td class="tx-right" style="padding: 5px; text-align: center; border: 1px solid black;">{{$item->cost_code}}</td>
                    <td class="tx-right" style="padding: 5px; text-align: center; border: 1px solid black;">{{ (int)$item->qty }}</td>
                    <td class="tx-right" style="padding: 5px; text-align: center; border: 1px solid black;">{{ (int)$item->qty_to_order }}</td>
                    <td class="tx-right" style="padding: 5px; text-align: center; border: 1px solid black;">{{ $item->previous_mrs }}</td>
                    @if ($sale->received_at)
                        <td class="tx-center" style="padding: 5px; text-align: center; border: 1px solid black;">{{ ((int)$item->qty_to_order - (int)$item->qty_ordered) }}</td>
                    @endif
                </tr>
                <tr class="pd-20" style="color: black;">
                    <td colspan="3" style="padding: 5px; text-align: right; border: 1px solid black;">
                        <spa>PAR TO: </span><br>
                        <span>FREQUENCY: </span><br>
                        <span>DATE NEEDED: </span><br>
                        <span>PURPOSE: </span>
                    </td>
                    <td colspan="{{ $sale->received_at ? 7 : 6 }}" class="tx-left" style="padding: 5px; text-align: left; border: 1px solid black;">
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