<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        body { margin: 0; font-family: 'Lora', serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 3px; text-align: left; }
        th { font-size: 8.5px; background-color: #f2f2f2; font-weight: bold; white-space: normal; word-wrap: break-word; }
        .tc  { text-align: center; }
        .tb  { font-weight: bold; }
        .hs  { font-size: 14px; }
        .is  { font-size: 11px; }
        .logo { width: 90px; height: auto; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <td colspan="22" class="tb tc hs">PURCHASE ADVISE</td>
                <td colspan="1" rowspan="3" class="tb tc hs">
                    <img src="{{ public_path('img/pmc-logo.png') }}" alt="Logo" class="logo">
                </td>
            </tr>
            <tr>
                <td colspan="22" class="tb tc hs">PA-{{ $paHeader->pa_number ?? '' }}</td>
            </tr>
            <tr>
                <td colspan="22" class="tb tc hs">
                    DATE: {{ $postedDate ? \Carbon\Carbon::parse($postedDate)->format('F j, Y h:i A') : 'Not Verified' }}
                </td>
            </tr>

            <tr>
                <th class="tc" width="1%">No</th>
                <th class="tc" width="3%">Stock Type</th>
                <th class="tc" width="3%">Inv. Code</th>
                <th class="tc" width="4%">Stock Code</th>
                <th class="tc" width="16%">Stock Description</th>
                <th class="tc" width="3%">OEM ID</th>
                <th class="tc" width="2%">UoM</th>
                <th class="tc" width="4%">Avg Mo. UR</th>
                <th class="tc" width="2%">SOH</th>
                <th class="tc" width="2%">Open PO</th>
                <th class="tc" width="2%">DLT (Mos.)</th>
                <th class="tc" width="3%">Qty to Order</th>
                <th class="tc" width="13%">Remarks</th>
                <th class="tc" width="4%">Freq/ Qty per Del.</th>
                <th class="tc" width="2%">No. Del.</th>
                <th class="tc" width="3%">Class. Note</th>
                <th class="tc" width="6%">End-user/ PAR To</th>
                <th class="tc" width="5%">Previous PO</th>
                <th class="tc" width="2%">PRIO#</th>
                <th class="tc" width="4%">Cost Code</th>
                <th class="tc" width="5%">Date Needed</th>
                <th class="tc" width="4%">#MOS (SOH+OO)</th>
                <th class="tc" width="4%">#MOS W REQ</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($purchaseAdviceData as $index => $item)
            @if($item['is_hold'] == 0)
                <tr class="is">
                    <td class="tc">{{ $index + 1 }}</td>
                    <td class="tc">{{ $item['stock_type']    ?? '' }}</td>
                    <td class="tc">{{ $item['inv_code']      ?? '' }}</td>
                    <td class="tc">{{ $item['stock_code'] === 'null' ? '' : ($item['stock_code'] ?? '') }}</td>
                    <td>{{ $item['item_description']          ?? '' }}</td>
                    <td class="tc">{{ $item['OEM_ID']         ?? '' }}</td>
                    <td class="tc">{{ $item['UoM']            ?? '' }}</td>
                    <td class="tc">{{ $item['usage_rate_qty'] ?? '' }}</td>
                    <td class="tc">{{ $item['on_hand']        ?? '' }}</td>
                    <td class="tc">{{ $item['open_po']        ?? '' }}</td>
                    <td class="tc">{{ $item['dlt']            ?? '' }}</td>
                    <td class="tc">{{ $item['qty_order']      ?? '' }}</td>
                    <td>{{ $item['purpose']                   ?? '' }}</td>
                    <td class="tc">{{ $item['qty_per_delivery']     ?? '' }}</td>
                    <td class="tc">{{ $item['number_of_deliveries'] ?? '' }}</td>
                    <td class="tc">{{ $item['class_note']    ?? '' }}</td>
                    <td class="tc">{{ $item['department'] ?: explode(':', $item['par_to'])[0] }}</td>
                    <td class="tc">{{ $item['previous_mrs']  ?? '' }}</td>
                    <td class="tc">{{ $item['priority']      ?? '' }}</td>
                    <td class="tc">{{ $item['cost_code']     ?? '' }}</td>
                    <td class="tc">{{ $item['date_needed']    ?? '' }}</td>
                    <td class="tc">{{ $item['rof_months']           ?? '' }}</td>
                    <td class="tc">{{ $item['rof_months_w_request'] ?? '' }}</td>
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
                    <td class="is" width="90%">{{ $salesHeader->planner_remarks }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <table style="margin-top: 15px;">
        <thead>
            <tr class="tb">
                <th class="tc" colspan="2">Prepared by:</th>
                <th class="tc">Reviewed by:</th>
                <th class="tc">Approved by:</th>
                <th class="tc">Received by:</th>
            </tr>
        </thead>
        @if (!empty($purchaseAdviceData[0]))
        <tbody class="is">
            <tr>
                <td class="tb" width="10%">Name</td>
                <td>{{ strtoupper($salesHeader->planner->name ?? '') }}</td>
                <td>{{ $salesHeader->verified_at ? 'JOHN DALE P. RANOCO'  : '' }}</td>
                <td>{{ $salesHeader->approved_at ? 'MYRNA G. IMPROSO'     : '' }}</td>
                <td>{{ $salesHeader->received_at ? strtoupper($salesHeader->receiver ? $salesHeader->receiver->name : '') : '' }}</td>
            </tr>
            <tr>
                <td class="tb">Designation</td>
                <td>MCD Planner</td>
                <td>{{ $salesHeader->verified_at ? 'Material Planning Supervisor' : '' }}</td>
                <td>{{ $salesHeader->approved_at ? 'MCD Manager'                  : '' }}</td>
                <td>{{ $salesHeader->received_at ? 'Purchaser'                    : '' }}</td>
            </tr>
            <tr>
                <td class="tb">Signature</td>
                <td></td><td></td><td></td><td></td>
            </tr>
            <tr>
                <td class="tb">Date</td>
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