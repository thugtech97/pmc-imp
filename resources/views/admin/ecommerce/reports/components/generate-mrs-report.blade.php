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
            padding: 3px;
            text-align: left;
        }
        th {
            font-size: 12px;
            background-color: #f2f2f2;
        }
        .text-align-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold
        }
        .item-style {
            font-size: 11px;
        }
        .header-style {
            font-size: 14px;
        }
        .mt {
            margin-top: 0.5px;
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

        .status-label-success {
            background-color: #0000FF;
            color: white;
            border-radius: 10px;
            padding: 3px 8px;
        }

        .status-label-danger {
            background-color: #FF0000;
            color: white;
            border-radius: 12px;
            padding: 3px 8px;
        }

    </style>
</head>
<body>  
    <table>
        <thead>
            <tr>
                <td colspan="2" rowspan="4" class="text-bold text-align-center header-style"><img src="{{ public_path('img/pmc-logo.png') }}" alt="Logo" class="logo"></td>
                <td colspan="5" class="text-bold header-style">PHILSAGA MINING CORPORATION</td>
                <td colspan="2" class="text-bold header-style">Warehouse @ Mill Site | Mine Site</td>
            </tr>
            <tr>
                <td colspan="5" class="text-bold header-style">Material Control Department</td>
                <td colspan="2" class="text-bold header-style">Phone: Local 2107 | 2134</td>
            </tr>
            <tr>
                <td colspan="5"></td>
                <td colspan="2" class="text-bold header-style">Fax: </td>
            </tr>
            <tr>
                <td colspan="5" class="text-bold text-align-center header-style"></td>
                <td colspan="2" class="text-bold header-style">Email: mcd@philsagamining.com</td>
            </tr>
            <tr>
                <td colspan="9" class="text-bold text-align-center header-style">MRS TRANSACTIONS</td>
            </tr>
            <tr>
                <td colspan="9" class="text-bold text-align-center header-style">{{ \Carbon\Carbon::parse($request->startdate)->format('F j, Y') }} to {{ \Carbon\Carbon::parse($request->enddate)->format('F j, Y') }}</td>
            </tr>
            <tr>
                <th class="text-align-center" width="10%">MRS Number</th>
                <th class="text-align-center" width="10%">PA Number</th>
                <th class="text-align-center" width="10%">Date Created</th>
                <th class="text-align-center" width="15%">Department</th>
                <th class="text-align-center" width="20%">Current PO#</th>
                <th class="text-align-center" width="15%">Purchasing Received Date</th>
                <th class="text-align-center" width="8%">Aging</th>
                <th class="text-align-center" width="7%">Total Balance</th>
                <th class="text-align-center" width="15%">Request Status</th>
            </tr>            
        </thead>
        <tbody class="item-style">   
            @forelse ($headers as $mrs)
                @php
                    $bal = $mrs->items->where('promo_id', '!=', 1)->sum('qty_to_order') - $mrs->items->where('promo_id', '!=', 1)->sum('qty_ordered');
                @endphp
                <tr class="item-style">
                    <td class="text-align-center">{{ $mrs->order_number }}</td>
                    <td class="text-align-center">{{ $mrs->purchaseAdvice->pa_number ?? "N/A" }}</td>
                    <td class="text-align-center">{{ Carbon\Carbon::parse($mrs->created_at)->format('m/d/Y') }}</td>
                    <td class="text-align-center">{{ $mrs->user->department->name }}</td>
                    <td class="text-align-center">
                        @foreach ($mrs->items as $item)
                            @if (!empty($item->po_no))
                                @php
                                    $badgeClass = ($item->qty_to_order == $item->qty_ordered) ? 'status-label-success' : 'status-label-danger';
                                @endphp
                                <span class="{{ $badgeClass }}">
                                    {{ $item->po_no }}
                                </span>
                            @endif
                        @endforeach
                    </td>
                    <td class="text-align-center">{{ $mrs->received_at ? Carbon\Carbon::parse($mrs->received_at)->format('F j, Y h:i A') : 'N/A' }}</td>
                    <td class="text-align-center">
                        @if($mrs->received_at)
                            @if($bal == 0)
                                <span style="color: green;">{{ "Complete" }}</span>
                            @else
                                @php
                                    $receivedAt = Carbon\Carbon::parse($mrs->received_at);
                                    $now = Carbon\Carbon::now();
                                    $days = $receivedAt->diffInDays($now);
                                    $hours = $receivedAt->copy()->addDays($days)->diffInHours($now);
                                @endphp
                                <span style="{{ $days >= 14 ? 'color: red;' : 'color: blue;' }}">
                                    {{ $days > 0 ? $days . ' day' . ($days > 1 ? 's' : '') : '' }}
                                    {{ $days == 0 ? $hours . ' hour' . ($hours > 1 ? 's' : '') : '' }}
                                </span>
                            @endif
                        @else
                            {{ 'N/A' }}
                        @endif
                    </td>
                    <td class="text-align-center">{{ $mrs->received_at ? $bal : 'N/A' }}</td>
                    <td class="text-align-center">{{ strtoupper($mrs->status) }}</td>
                </tr>
            @empty
                <tr class="item-style">
                    <td colspan="9" class="text-align-center">No records found</td>
                </tr>
            @endforelse
            
        </tbody>
    </table>
    <table style="margin-top: 15px">
        <thead>
            <tr class="text-bold">
                <th class="text-align-center" colspan="2">Prepared by:</th>
                <th class="text-align-center">Reviewed by:</th>
            </tr>
        </thead>
        <tbody class="item-style">
            <tr>
                <td class="text-bold" width="10%">Name</td>
                <td>{{ strtoupper(\Auth::user()->name) }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-bold" width="10%">Designation</td>
                <td>{{ \Auth::user()->role_name() }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
