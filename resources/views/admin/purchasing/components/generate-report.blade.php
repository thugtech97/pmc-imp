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
        .text-align-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold
        }
        .item-style {
            font-size: 9px;
        }
        .header-style {
            font-size: 13px;
        }
        .mt {
            margin-top: 0.5px;
        }

    </style>
</head>
<body>  
    <table>
        <thead>
            <tr>
                <td colspan="22" class="text-bold text-align-center header-style">PURCHASE ADVISE</td>
            </tr>
            <tr>
                <td colspan="22" class="text-bold text-align-center header-style">PA-{{ $paHeader->pa_number ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="22" class="text-bold text-align-center header-style">DATE: {{ $postedDate }} </td>
            </tr>
            <tr>
                <th class="text-align-center" width="1%">No</th>
                <th class="text-align-center" width="5%">Stock Type</th>
                <th class="text-align-center" width="5%">Inv. Code</th>
                <th class="text-align-center" width="5%">Stock Code</th>
                <th class="text-align-center" width="15%">Stock Description</th>
                <th class="text-align-center" width="10%">OEM ID</th>
                <th class="text-align-center" width="5%">UoM</th>
                <th class="text-align-center" width="5%">Usage Rate </th>
                <th class="text-align-center" width="3%">On hand</th>
                <th class="text-align-center" width="3%">On Order</th>
                <th class="text-align-center" width="3%">Min Qty</th>
                <th class="text-align-center" width="3%">Max Qty</th>
                <th class="text-align-center" width="3%">Qty To Order</th>
                <th class="text-align-center" width="5%">Date Needed</th>
                <th class="text-align-center" width="5%">Frequency</th>
                <th class="text-align-center" width="5%">PARTO</th>
                <th class="text-align-center" width="5%">End-users/MRS#</th>
                <th class="text-align-center" width="5%">Priority #</th>
                <th class="text-align-center" width="5%">Previous PO#</th>
                <th class="text-align-center" width="5%">PO#</th>
                <th class="text-align-center" width="5%">Qty Ordered</th>
                <th class="text-align-center" width="5%">Balance QTY for PO</th>
            </tr>
        </thead>
        @foreach ($purchaseAdviceData as $index => $item)
        <tbody class="item-style">   
                <tr class="item-style">
                    <td class="text-align-center">{{ $index + 1 }}</td>
                    <td class="text-align-center">{{ $item['stock_type'] ?? '' }}</td>
                    <td class="text-align-center">{{ $item['inv_code'] ?? '' }}</td>
                    <td>{{ $item['stock_code'] === 'null' ? '' : $item['stock_code'] }}</td>
                    <td>{{ $item['item_description'] }}</td>
                    <td>{{ $item['OEM_ID']  ?? '' }}</td>
                    <td>{{ $item['UoM'] ?? '' }}</td>
                    <td class="text-align-center">{{ (int)$item['usage_rate_qty'] ?? '' }}</td>
                    <td class="text-align-center">{{ $item['on_hand'] }}</td>
                    <td class="text-align-center">{{ $item['open_po'] ?? '' }}</td>
                    <td class="text-align-center">{{ $item['min_qty'] ?? '' }}</td>
                    <td class="text-align-center">{{ $item['max_qty'] ?? '' }}</td>
                    <td class="text-align-center">{{ (int)$item['qty_order'] ?? '' }}</td>
                    <td class="text-align-center">{{ $item['date_needed'] }}</td>
                    <td class="text-align-center">{{ $item['frequency']}}</td>
                    <td class="text-align-center">{{ explode(':', $item['par_to'])[0] }}</td>
                    <td class="text-align-center">{{ $salesHeader->order_number }}</td> 
                    <td class="text-align-center">{{  $salesHeader->priority  }}</td>
                    <td class="text-align-center">{{ $item['previous_mrs']  ?? ''}}</td>
                    <td class="text-align-center">{{  $item['po_no'] ?? ''  }}</td>
                    <td class="text-align-center">{{  $item['qty_ordered'] ?? ''  }}</td>
                    <td class="text-align-center">{{ ((int)$item['qty_order'] - (int)$item['qty_ordered']) }}</td>
                </tr>
                <tr>
                    <td colspan="4">
                        Cost code:
                    </td>
                    <td colspan="18">
                        {{ $item['cost_code'] ?? ''}}
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        Purpose:
                    </td>
                    <td colspan="18">
                        {{ $item['purpose'] ?? ''}}
                    </td>
                </tr>
        </tbody>
        @endforeach
    </table>
    <table>
        <tbody>
            <tr>
                <th class="text-align-left" width="10%">
                    MRS Purpose
                </td>
                <td class="item-style" width="90%">
                    {{ $salesHeader->purpose }}
                </td>
            </tr>
        </tbody>
    </table>
    <table>
        <tbody>
            <tr>
                <th class="text-align-left" width="10%">
                    Planner Remarks
                </td>
                <td class="item-style" width="90%">
                    {{ $salesHeader->planner_remarks }}
                </td>
            </tr>
        </tbody>
    </table>
    <table style="margin-top: 15px">
        <thead>
            <tr class="text-bold">
                <th class="text-align-center" colspan="2">Prepared by:</th>
                <th class="text-align-center">Reviewed by:</th>
                <th class="text-align-center">Approved by:</th>
                <th class="text-align-center">Received by:</th>
            </tr>
        </thead>
        @if (!empty($purchaseAdviceData[0]))
        <tbody class="item-style">
            <tr>
                <td class="text-bold" width="10%">Name</td>
                <td>{{ strtoupper($salesHeader->planner->name) }}</td>
                <td>{{ $salesHeader->verified_at ? 'JOHN DALE P. RANOCO' : '' }}</td>
                <td>{{ $salesHeader->approved_at ? 'MYRNA G. IMPROSO' : '' }}</td>
                <td>{{ $salesHeader->received_at ? strtoupper($salesHeader->purchaser->name) : '' }}</td>
            </tr>
            <tr>
                <td class="text-bold" width="10%">Designation</td>
                <td>MCD Planner</td>
                <td>{{ $salesHeader->verified_at ? 'Material Planning Supervisor' : '' }}</td>
                <td>{{ $salesHeader->approved_at ? 'MCD Manager' : '' }}</td>
                <td>{{ $salesHeader->received_at ? 'Purchaser' : '' }}</td>
            </tr>
            <tr>
                <td class="text-bold" width="10%">Signature</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-bold" width="10%">Date</td>
                <td>{{ \Carbon\Carbon::parse($salesHeader->planner_at)->format('m/d/Y g:i:s A') ?? ""}}</td>
                <td>{{ $salesHeader->verified_at ? \Carbon\Carbon::parse($salesHeader->verified_at)->format('m/d/Y g:i:s A') : '' }}</td>
                <td>{{ $salesHeader->approved_at ? \Carbon\Carbon::parse($salesHeader->approved_at)->format('m/d/Y g:i:s A') : '' }}</td>
                <td>{{ $salesHeader->received_at ? \Carbon\Carbon::parse($salesHeader->received_at)->format('m/d/Y g:i:s A') : '' }}</td>

            </tr>
        </tbody>
        @endif
    </table>
</body>
</html>
