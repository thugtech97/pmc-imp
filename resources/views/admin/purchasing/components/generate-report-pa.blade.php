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
        }
        @font-face {
            font-family: 'Lora';
            src: url('{{ public_path('fonts/lora/Lora-Bold.ttf') }}') format('truetype');
            font-weight: bold;
        }
        body { margin: 0; font-family: 'Lora', serif; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 3px; text-align: left; }
        th { font-size: 8px; background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-bold   { font-weight: bold; }
        .header-style { font-size: 12px; }
        .item-style   { font-size: 8px; }
        .logo { width: 90px; height: auto; }
        .sub-row td { background: #fafafa; font-size: 8px; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <td colspan="22" class="text-bold text-center header-style">PURCHASE ADVISE</td>
                <td colspan="2" rowspan="3" class="text-bold text-center header-style">
                    <img src="{{ public_path('img/pmc-logo.png') }}" alt="Logo" class="logo">
                </td>
            </tr>
            <tr>
                <td colspan="22" class="text-bold text-center header-style">PA-{{ $paHeader->pa_number ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="22" class="text-bold text-center header-style">
                    DATE: {{ $postedDate ? \Carbon\Carbon::parse($postedDate)->format('F j, Y h:i A') : 'Not Verified' }}
                </td>
            </tr>

            {{-- Column Headers matching the image --}}
            <tr>
                <th class="text-center" width="1%">No</th>
                <th class="text-center" width="4%">Stock Type</th>
                <th class="text-center" width="3%">Inv. Code</th>
                <th class="text-center" width="4%">Stock Code</th>
                <th class="text-center" width="13%">Stock Description</th>
                <th class="text-center" width="5%">OEM ID</th>
                <th class="text-center" width="3%">UoM</th>
                <th class="text-center" width="5%">Average Usage (12mos.)</th>
                <th class="text-center" width="3%">SOH</th>
                <th class="text-center" width="3%">Open PO</th>
                <th class="text-center" width="3%">DLT (Mos.)</th>
                <th class="text-center" width="4%">Qty to Order</th>
                <th class="text-center" width="7%">Date Needed</th>
                <th class="text-center" width="4%">Frequency/ Qty per Delivery</th>
                <th class="text-center" width="4%">No. of Deliveries</th>
                <th class="text-center" width="3%">Class/ c Note</th>
                <th class="text-center" width="5%">End-user/ PAR To</th>
                <th class="text-center" width="5%">Previous PO</th>
                <th class="text-center" width="3%">PRIO#</th>
                <th class="text-center" width="5%">Cost Code</th>
                <th class="text-center" width="7%">Remarks</th>
                <th class="text-center" width="4%">ROF MONTHS (SOH+OO)</th>
                <th class="text-center" width="4%">ROF MONTHS W REQUEST</th>
                <th class="text-center" width="4%">Current PO#</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($purchaseAdviceData as $index => $item)
            @if($item['is_hold'] == 0)
                <tr class="item-style">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item['stock_type']  ?? '' }}</td>
                    <td class="text-center">{{ $item['inv_code']    ?? '' }}</td>
                    <td class="text-center">{{ $item['stock_code'] === 'null' ? '' : ($item['stock_code'] ?? '') }}</td>
                    <td>{{ $item['item_description'] ?? '' }}</td>
                    <td class="text-center">{{ $item['OEM_ID']      ?? '' }}</td>
                    <td class="text-center">{{ $item['UoM']         ?? '' }}</td>
                    <td class="text-center">{{ $item['usage_rate_qty'] ?? '' }}</td>
                    <td class="text-center">{{ $item['on_hand']     ?? '' }}</td>
                    <td class="text-center">{{ $item['open_po']     ?? '' }}</td>
                    <td class="text-center">{{ $item['dlt']         ?? '' }}</td>
                    <td class="text-center">{{ $item['qty_order']   ?? '' }}</td>
                    <td class="text-center">{{ $item['date_needed'] ?? '' }}</td>
                    <td class="text-center">{{ $item['qty_per_delivery']     ?? '' }}</td>
                    <td class="text-center">{{ $item['number_of_deliveries'] ?? '' }}</td>
                    <td class="text-center">{{ $item['class_note']  ?? '' }}</td>
                    <td class="text-center">{{ explode(':', $item['par_to'])[0] ?? '' }}</td>
                    <td class="text-center">{{ $item['previous_mrs'] ?? '' }}</td>
                    <td class="text-center">{{ $item['priority']    ?? '' }}</td>
                    <td class="text-center">{{ $item['cost_code']   ?? '' }}</td>
                    <td>{{ $item['purpose']             ?? '' }}</td>
                    <td class="text-center">{{ $item['rof_months']           ?? '' }}</td>
                    <td class="text-center">{{ $item['rof_months_w_request'] ?? '' }}</td>
                    <td class="text-center">{{ $item['po_no']       ?? '' }}</td>
                </tr>
                {{-- Cost code and purpose sub-rows preserved --}}
                <tr class="sub-row item-style">
                    <td colspan="4"><strong>Cost code:</strong></td>
                    <td colspan="20">{{ $item['cost_code'] ?? '' }}</td>
                </tr>
                <tr class="sub-row item-style">
                    <td colspan="4"><strong>Purpose:</strong></td>
                    <td colspan="20">{{ $item['purpose'] ?? '' }}</td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

    @if ($salesHeader->planner_remarks)
        <table style="margin-top: 6px;">
            <tbody>
                <tr>
                    <th width="10%">Planner Remarks</th>
                    <td class="item-style" width="90%">{{ $salesHeader->planner_remarks }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <table style="margin-top: 15px;">
        <thead>
            <tr class="text-bold">
                <th class="text-center" colspan="2">Prepared by:</th>
                <th class="text-center">Reviewed by:</th>
                <th class="text-center">Approved by:</th>
                <th class="text-center">Received by:</th>
            </tr>
        </thead>
        @if (!empty($purchaseAdviceData[0]))
        <tbody class="item-style">
            <tr>
                <td class="text-bold" width="10%">Name</td>
                <td>{{ strtoupper($salesHeader->creator->name ?? '') }}</td>
                <td>{{ $salesHeader->verified_at  ? 'JOHN DALE P. RANOCO'  : '' }}</td>
                <td>{{ $salesHeader->approved_at  ? 'MYRNA G. IMPROSO'     : '' }}</td>
                <td>{{ $salesHeader->received_at  ? strtoupper($salesHeader->receiver->name ?? '') : '' }}</td>
            </tr>
            <tr>
                <td class="text-bold">Designation</td>
                <td>MCD Planner</td>
                <td>{{ $salesHeader->verified_at  ? 'Material Planning Supervisor' : '' }}</td>
                <td>{{ $salesHeader->approved_at  ? 'MCD Manager'                  : '' }}</td>
                <td>{{ $salesHeader->received_at  ? 'Purchaser'                    : '' }}</td>
            </tr>
            <tr>
                <td class="text-bold">Signature</td>
                <td></td><td></td><td></td><td></td>
            </tr>
            <tr>
                <td class="text-bold">Date</td>
                <td>{{ \Carbon\Carbon::parse($salesHeader->created_at)->format('F j, Y h:i A') }}</td>
                <td>{{ $salesHeader->verified_at ? \Carbon\Carbon::parse($salesHeader->verified_at)->format('F j, Y h:i A') : '' }}</td>
                <td>{{ $salesHeader->approved_at ? \Carbon\Carbon::parse($salesHeader->approved_at)->format('F j, Y h:i A') : '' }}</td>
                <td>{{ $salesHeader->received_at ? \Carbon\Carbon::parse($salesHeader->received_at)->format('F j, Y h:i A') : '' }}</td>
            </tr>
        </tbody>
        @endif
    </table>
</body>
</html>