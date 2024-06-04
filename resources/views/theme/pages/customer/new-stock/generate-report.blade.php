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
@php
    $showStockCodeColumn = $items->isNotEmpty() && $items->contains(function ($item) {
        return $item->stock_code !== "null" && $item->stock_code !== null && $item->stock_code !== '';
    });
@endphp
<div class="container-fluid content-wrap">
    <div class="status">
        <span class="badge badge-secondary title"> <strong>STATUS:</strong> {{ $InventoryRequestData->status }} </span>
    </div>
    <table style="margin-top: 25px">
        <thead>
            <tr>
                <td colspan="{{ $showStockCodeColumn ? '10' : '9' }}" class="text-bold text-align-center header-style">Inventory Maintenance Form (IMF) Request</td>
            </tr>
            <tr style="border: 1px solid #dddddd">
                <td colspan="{{ $showStockCodeColumn ? '5' : '5' }}" class="title" style="border: 0">
                    <div><span class="text-uppercase">Department:</span> {{ $InventoryRequestData->department }}</div>
                    <div><span class="text-uppercase">Created by:</span> {{ $InventoryRequestData->user->name}}</div>
                    <div><span class="text-uppercase">Type:</span> {{ $InventoryRequestData->type }}</div>
                    @if($InventoryRequestData->type === 'update' && $showStockCodeColumn)
                    <div>
                        <span class="text-uppercase">Stock Code:</span> {{ $items[0]->stock_code }} 
                    </div>
                    @endif
                </td>
                <td colspan="{{ $showStockCodeColumn ? '5' : '4' }}" class="title" style="border: 0">
                    <div><span class="text-uppercase">Created at:</span> {{ \Carbon\Carbon::parse($InventoryRequestData->created_at)->format('Y-m-d h:i:s A') }}</div>
                    <div><span class="text-uppercase">Updated at:</span> {{ \Carbon\Carbon::parse($InventoryRequestData->updated_at)->format('Y-m-d h:i:s A') }}</div>
                    @if($InventoryRequestData->submitted_at)
                        <span class="text-uppercase">Submitted at:</span> {{ \Carbon\Carbon::parse($InventoryRequestData->submitted_at)->format('Y-m-d h:i:s A') }}
                    @endif
                </td>
            </tr>
        </thead>
    </table>
    <div class="row">
        <div class="col-md-12">
            @if($InventoryRequestData->type === 'update') 
            <table class="table" style="border-top: 0">
                <thead>
                    <th width="20%"></th>
                    <th width="40%">Old Value</th>
                    <th class="new-value">
                        <span>New Value</span>
                        @if (!empty($items[0]->file_path))
                            <a href="#" class="download-link" data-file="{{ $items[0]->file_path }}">
                                <span data-bs-toggle="tooltip" title="Download">View Attached File</span> 
                            </a>
                        @endif
                    </th>

                </thead>
                <tbody>
                    <tr>
                        <th>Item Description</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->item_description }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->item_description != '' ? $oldItems[0]->item_description : $items[0]->item_description }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->item_description == '' ? '' : $items[0]->item_description }}</td>
                    </tr>
                    <tr>
                        <th>Brand</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->brand }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->brand != '' ? $oldItems[0]->brand : $items[0]->brand }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->brand == '' ? '' : $items[0]->brand }}</td>
                    </tr>
                    <tr>
                        <th>OEM ID</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->OEM_ID }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->OEM_ID != '' ? $oldItems[0]->OEM_ID : $items[0]->OEM_ID }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->OEM_ID == '' ? '' : $items[0]->OEM_ID }}</td>
                    </tr>
                    <tr>
                        <th>UoM</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->UoM }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->UoM != '' ? $oldItems[0]->UoM : $items[0]->UoM }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->UoM == '' ? '' : $items[0]->UoM }}</td>
                    </tr>
                    <tr>
                        <th>Usage Rate Qty</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->usage_rate_qty }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->usage_rate_qty != '' ? $oldItems[0]->usage_rate_qty : $items[0]->usage_rate_qty }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->usage_rate_qty == '' ? '' : $items[0]->usage_rate_qty }}</td>
                    </tr>
                    <tr>
                        <th>Usage Frequency</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->usage_frequency }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->usage_frequency != '' ? $oldItems[0]->usage_frequency : $items[0]->usage_frequency }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->usage_frequency == '' ? '' : $items[0]->usage_frequency }}</td>
                    </tr>
                    <tr>
                        <th>Min Qty</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->min_qty }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->min_qty != '' ? $oldItems[0]->min_qty : $items[0]->min_qty }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->min_qty == '' ? '' : $items[0]->min_qty }}</td>
                    </tr>
                    <tr>
                        <th>Max Qty</th>
                        @if (empty($oldItems[0]))
                        <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->max_qty }}</td>
                        @else
                        <td class="old-item">{{ $oldItems[0]->max_qty != '' ? $oldItems[0]->max_qty : $items[0]->max_qty }}</td>
                        @endif
                        <td>{{ !empty($oldItems[0]) && $oldItems[0]->max_qty == '' ? '' : $items[0]->max_qty }}</td>
                    </tr>
                    <tr>
                        <th>Purpose</th>
                        <td class="old-item">{{ $oldItems[0]->purpose ?? '' }}</td>
                        <td>{{ $items[0]->purpose }}</td>
                    </tr>
                </tbody>
            </table>
            @else
            <table class="table">
                <thead>
                    @if ($showStockCodeColumn)
                        <th>Stock Code</th>
                    @endif
                    <th class="title">Item Description</th>
                    <th class="title">Brand</th>
                    <th class="title">OEM ID</th>
                    <th class="title">UoM</th>
                    <th class="title" width="12%">Usage Rate Qty</th>
                    <th class="title" width="13%">Usage Frequency</th>
                    <th class="title">Min Qty</th>
                    <th class="title">Max Qty</th>
                    <th class="title" width="20%">Purpose</th>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            @if ($showStockCodeColumn)
                                <td>{{ $item->stock_code !== "null" ? $item->stock_code : '' }}</td>
                            @endif
                            <td class="text-uppercase">{{ $item->item_description }}</td>
                            <td class="text-uppercase">{{ $item->brand }}</td>
                            <td>{{ $item->OEM_ID }}</td>
                            <td>{{ $item->UoM }}</td>
                            <td>{{ $item->usage_rate_qty }}</td>
                            <td>{{ $item->usage_frequency }}</td>
                            <td>{{ $item->min_qty }}</td>
                            <td>{{ $item->max_qty }}</td>
                            <td>{{ $item->purpose}}</td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
</body>
</html>