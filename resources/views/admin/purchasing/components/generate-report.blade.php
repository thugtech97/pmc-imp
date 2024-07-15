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
        .item-style {
            font-size: 9px;
        }
        .header-style {
            font-size: 13px;
        }

    </style>
</head>
<body>  
    <table>
        <thead>
            <tr>
                <td colspan="20" class="text-bold text-align-center header-style">PURCHASE ADVISE</td>
            </tr>
            <tr>
                <td colspan="20" class="text-bold text-align-center header-style">AB-DPXXXX</td>
            </tr>
            <tr>
                <td colspan="20" class="text-bold text-align-center header-style">DATE: {{ $postedDate }} </td>
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
                <th class="text-align-center" width="3%">Open PO</th>
                <th class="text-align-center" width="3%">Min Qty</th>
                <th class="text-align-center" width="3%">Max Qty</th>
                <th class="text-align-center" width="3%">Qty Order</th>
                <th class="text-align-center" width="5%">Date Needed</th>
                <th class="text-align-center" width="5%">Frequency</th>
                <th class="text-align-center" width="5%">PARTO</th>
                <th class="text-align-center" width="5%">End-users/MRS#</th>
                <th class="text-align-center" width="5%">Priority #</th>
                <th class="text-align-center" width="5%">Previous #</th>
                <th class="text-align-center" width="10%">Cost Code</th>
            </tr>
        </thead>
        @foreach ($purchaseAdviceData as $index => $item)
        <tbody class="item-style">   
                <tr class="item-style">
                    <td class="text-align-center">{{ $index + 1 }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $item['stock_code'] === 'null' ? '' : $item['stock_code'] }}</td>
                    <td>{{ $item['item_description']  ?? '' }}</td>
                    <td>{{ $item['OEM_ID']  ?? '' }}</td>
                    <td>{{ $item['UoM'] ?? '' }}</td>
                    <td class="text-align-center">{{ $item['usage_rate_qty'] ?? '' }}</td>
                    <td></td>
                    <td></td>
                    <td class="text-align-center">{{ $item['min_qty'] ?? '' }}</td>
                    <td class="text-align-center">{{ $item['max_qty'] ?? '' }}</td>
                    <td class="text-align-center">{{ $item['qty_order'] ?? '' }}</td>
                    <td class="text-align-center">{{ $salesHeader->delivery_date }}</td>
                    <td class="text-align-center">{{ $item['usage_frequency']  ?? ''}}</td>
                    <td></td>
                    <td class="text-align-center"></td> 
                    <td class="text-align-center">{{  $salesHeader->priority  }}</td>
                    <td></td>
                    <td class="text-align-center">{{ $item['cost_code'] ?? ''}}</td>
                </tr>
        </tbody>
        @endforeach
    </table>
    <table>
        <tbody>
            <tr>
                <th class="text-align-left" width="10%">Purpose</td>
                <td class="item-style" width="90%">{{ $salesHeader->purpose }}</td>
            </tr>
        </tbody>
    </table>
    <table style="margin-top: 15px">
        <thead>
            <tr class="text-bold">
                <th class="text-align-center" colspan="2">Prepared by:</th>
                <th class="text-align-center">Reviewed by:</th>
                <th class="text-align-center">Received by:</th>
            </tr>
        </thead>
        @if (!empty($purchaseAdviceData[0]))
        <tbody class="item-style">
            <tr>
                <td class="text-bold" width="10%">Name</td>
                <td>{{ $purchaseAdviceData[0]['prepared_by_name'] ?? '' }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-bold" width="10%">Designation</td>
                <td>{{ $purchaseAdviceData[0]['prepared_by_designation'] ?? '' }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-bold" width="10%">Signature</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-bold" width="10%">Date</td>
                <td>{{ $purchaseAdviceData[0]['prepared_by_date'] ?? '' }}</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
        @endif
    </table>
</body>
</html>
