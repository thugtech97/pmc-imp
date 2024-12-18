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
            border: 1px solid black;
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
            font-size: 9px;
        }

        .title-item {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .label {
            display: table-cell;
            font-weight: bold;
            font-size: 10px;
            text-align: left;
        }

        .value {
            font-size: 10px;
            display: table-cell;
            flex: 2;
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
            width: 100px;
            height: auto;
        }
    </style>
</head>
<body>  
@php
    $showStockCodeColumn = $items->isNotEmpty() && $items->contains(function ($item) {
        return $item->stock_code !== "null" && $item->stock_code !== null && $item->stock_code !== '';
    });
@endphp
<div class="container-fluid content-wrap">
    <div class="logo-container">
        <img src="{{ public_path('img/pmc-logo.png') }}" alt="Logo" class="logo">
    </div>
    <h6><strong>STATUS:</strong> <span class="status-label">{{  $InventoryRequestData->status }}  </span></h6>
    <br>
    <table>
        <thead>
            <tr>
                <td colspan="2" class="text-bold text-align-center header-style">IMF Request</td>
            </tr>
            <tr style="border: 1px solid black;">
                <td class="title" style="border: 0">
                    <div class="title-item"><div class="label" style="width: 15%;">IMF NO:</div> <div class="value"> {{$InventoryRequestData->items[0]['imf_no']}}</div></div>
                    <div class="title-item"><div class="label" style="width: 15%;">DEPARTMENT:</div> <div class="value"> {{ $InventoryRequestData->department }}</div></div>
                    <div class="title-item"><div class="label" style="width: 15%;">CREATED BY:</div> <div class="value"> {{ strtoupper($InventoryRequestData->user->name) }}</div></div>
                    <div class="title-item"><div class="label" style="width: 15%;">TYPE:</div> <div class="value"> {{ strtoupper($InventoryRequestData->type) }}</div></div>
                    @if($InventoryRequestData->type === 'update' && $showStockCodeColumn)
                        <div class="title-item"><div class="label" style="width: 15%;">STOCK CODE:</div> <div class="value"> {{ $items[0]->stock_code }}</div></div>
                    @endif
                </td>
                <td class="title" style="border: 0">
                    <div class="title-item"><div class="label" style="width: 30%;">CREATED AT:</div> <div class="value"> {{ $InventoryRequestData->created_at }}</div></div>
                    <div class="title-item"><div class="label" style="width: 30%;">UPDATED AT:</div> <div class="value"> {{ $InventoryRequestData->updated_at }}</div></div>
                    <div class="title-item"><div class="label" style="width: 30%;">SUBMITTED AT:</div> <div class="value"> {{ $InventoryRequestData->submitted_at }}</div></div>
                    <div class="title-item"><div class="label" style="width: 30%;">APPROVED AT:</div> <div class="value"> {{ $InventoryRequestData->approved_at }}</div></div>
                </td>
            </tr>
        </thead>
    </table>
    <div class="row">
        @if($InventoryRequestData->type === 'update') 
            <table class="table mg-b-10">
                <thead style="background-color: #f3f4f7">
                    <th></th>
                    <th>Old Value</th>
                    <th>New Value</th>
                </thead>
                <tbody>
                    <tr>
                        <th width="20%">Item Description</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->item_description }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->item_description != '' ? $oldItems[0]->item_description : $items[0]->item_description }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->item_description == '' ? '' : $items[0]->item_description }}</td>
                    </tr>
                    <tr>
                        <th width="20%">Brand</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->brand }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->brand != '' ? $oldItems[0]->brand : $items[0]->brand }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->brand == '' ? '' : $items[0]->brand }}</td>
                    </tr>
                    <tr>
                        <th width="20%">OEM ID</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->OEM_ID }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->OEM_ID != '' ? $oldItems[0]->OEM_ID : $items[0]->OEM_ID }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->OEM_ID == '' ? '' : $items[0]->OEM_ID }}</td>
                    </tr>
                    <tr>
                        <th width="20%">UoM</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->UoM }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->UoM != '' ? $oldItems[0]->UoM : $items[0]->UoM }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->UoM == '' ? '' : $items[0]->UoM }}</td>
                    </tr>
                    <tr>
                        <th width="20%">Usage Rate Qty</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->usage_rate_qty }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->usage_rate_qty != '' ? $oldItems[0]->usage_rate_qty : $items[0]->usage_rate_qty }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->usage_rate_qty == '' ? '' : $items[0]->usage_rate_qty }}</td>
                    </tr>
                    <tr>
                        <th width="20%">Usage Frequency</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->usage_frequency }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->usage_frequency != '' ? $oldItems[0]->usage_frequency : $items[0]->usage_frequency }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->usage_frequency == '' ? '' : $items[0]->usage_frequency }}</td>
                    </tr>
                    <tr>
                        <th width="20%">Min Qty</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->min_qty }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->min_qty != '' ? $oldItems[0]->min_qty : $items[0]->min_qty }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->min_qty == '' ? '' : $items[0]->min_qty }}</td>
                    </tr>
                    <tr>
                        <th width="20%">Max Qty</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->max_qty }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->max_qty != '' ? $oldItems[0]->max_qty : $items[0]->max_qty }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->max_qty == '' ? '' : $items[0]->max_qty }}</td>
                    </tr>
                    <tr>
                        <th width="20%">Purpose</th>
                        <td class="old-item">{{ $oldItems[0]->purpose ?? '' }}</td>
                        <td>{{ $items[0]->purpose }}</td>
                    </tr>
                </tbody>
            </table>
        @else
        <table class="table table-striped mg-b-10">
            <thead>
                @if ($showStockCodeColumn)
                    <th class="wd-10p">Stock Code</th>
                @endif
                <th class="wd-30p">Item Description</th>
                <th class="wd-20p">Purpose</th>
                <th class="wd-10p">Min Quantity</th>
                <th class="wd-10p">Brand</th>
                <th class="wd-10p">Max Quantity</th>
                <th class="wd-10p">OEM ID</th>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        @if ($showStockCodeColumn)
                                <td>{{ $item->stock_code !== "null" ? $item->stock_code : '' }}</td>
                        @endif
                        <td>{{ $item->item_description }}</td>
                        <td>{{ $item->purpose }}</td>
                        <td>{{ $item->min_qty }}</td>
                        <td>{{ $item->brand }}</td>
                        <td>{{ $item->max_qty }}</td>
                        <td>{{ $item->OEM_ID }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
</body>
</html>