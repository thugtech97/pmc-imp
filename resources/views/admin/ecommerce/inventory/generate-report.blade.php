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
                    <div class="title-item"><div class="label" style="width: 15%;">PRIORITY:</div> <div class="value"> {{ strtoupper($InventoryRequestData->priority) }}</div></div>
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
                <th class="wd-10p">Brand</th>
                <th class="wd-10p">OEM ID</th>
                <th class="wd-20p">Purpose</th>
                <th class="wd-10p">Min Quantity</th>
                <th class="wd-10p">Max Quantity</th>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        @if ($showStockCodeColumn)
                                <td>{{ $item->stock_code !== "null" ? $item->stock_code : '' }}</td>
                        @endif
                        <td>{{ $item->item_description }}</td>
                        <td>{{ $item->brand }}</td>
                        <td>{{ $item->OEM_ID }}</td>
                        <td>{{ $item->purpose }}</td>
                        <td>{{ $item->min_qty }}</td>
                        <td>{{ $item->max_qty }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    <div style="font-size: 9px; font-family: Arial, sans-serif; line-height: 1.1;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <!-- Legend Section -->
                <td style="width: 40%; vertical-align: top; padding-right: 10px;">
                    <h3 style="font-size: 10px; margin-bottom: 3px;">Legend:</h3>
                    <ul style="margin: 0; padding-left: 10px;">
                        <li><b>Column 1,3-9</b>: For New Stock Item Registration Only.</li>
                        <ul style="margin: 0; padding-left: 10px;">
                            <li>Usage rate based on estimated actual consumption.</li>
                        </ul>
                        <li><b>Column 1-6,10-11</b>: For Min-Max Update Only.</li>
                        <li><b>Column 1-6,9</b>: For Merge Code, Stock type Update, Obsolete.</li>
                        <li><b>Column 1-9</b>: For Usage Rate Update Only.</li>
                        <li><b>Column 1-11</b>: Others (Choose Column to fill in).</li>
                        <ul style="margin: 0; padding-left: 10px;">
                            <li>Bin Location Update (for MCD only).</li>
                        </ul>
                    </ul>
                </td>
    
                <!-- Priority Table Section -->
                <td style="width: 60%; vertical-align: top;">
                    <h1 style="font-size: 12px; margin-bottom: 5px;">PRIORITY NO.: <span style="color: red;">{{ $InventoryRequestData->priority }}</span></h1>
                    <table border="1" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 9px;">
                        <thead>
                            <tr>
                                <th style="padding: 2px;">LABEL</th>
                                <th style="padding: 2px;">EXISTING</th>
                                <th style="padding: 2px;">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Priority 1 -->
                            <tr>
                                <td style="padding: 2px;">Priority 1</td>
                                <td style="padding: 2px;">Very Urgent</td>
                                <td style="padding: 2px;">Can stop operation of the end-user</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">If date needed is earlier than normal DLT</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">For legal compliances (regulatory requirements)</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">Safety and Medical emergency related requirements</td>
                            </tr>
                            <!-- Priority 2 -->
                            <tr>
                                <td style="padding: 2px;">Priority 2</td>
                                <td style="padding: 2px;">High Priority but not urgent</td>
                                <td style="padding: 2px;">Long lead time items (regularly ordered)</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">Can stop operation of the end-user</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">Safety and Medical related requirements but not urgent</td>
                            </tr>
                            <!-- Priority 3 -->
                            <tr>
                                <td style="padding: 2px;">Priority 3</td>
                                <td style="padding: 2px;">Needed but not priority</td>
                                <td style="padding: 2px;">SR Items</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">Project related items (projects are normally planned, thus not urgent)</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">Confirming POs</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">PMS (based on annual PMS Program)</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">CAPEX items (based on CAPEX Budget)</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td style="padding: 2px;">Annual Activities requirements (Detective and preventive maintenance)</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>     
    
</div>
</body>
</html>