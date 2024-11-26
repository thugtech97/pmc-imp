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

        .status-label-info {
            background-color: #0099ff;
            color: white;
            border-radius: 10px;
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
                <td colspan="9" class="text-bold text-align-center header-style">PURCHASE ADVICE TRANSACTIONS</td>
            </tr>
            <tr>
                <td colspan="9" class="text-bold text-align-center header-style">{{ \Carbon\Carbon::parse($request->startdate)->format('F j, Y') }} to {{ \Carbon\Carbon::parse($request->enddate)->format('F j, Y') }}</td>
            </tr>
            <tr>
                <th class="text-align-center" width="10%">PA Number</th>
                <th class="text-align-center" width="10%">MRS Number</th>
                <th class="text-align-center" width="10%">Date Created</th>
                <th class="text-align-center" width="12%">MCD Manager Approved At</th>
                <th class="text-align-center" width="18%">Current PO#</th>
                <th class="text-align-center" width="12%">Purchasing Received Date</th>
                <th class="text-align-center" width="7%">Aging</th>
                <th class="text-align-center" width="6%">Total Balance</th>
                <th class="text-align-center" width="15%">Status</th>
            </tr>                       
        </thead>
        <tbody class="item-style">   
            @forelse ($headers as $pa)
                @php
                    $bal = $pa->items->sum('qty_to_order') - $pa->items->sum('qty_ordered');
                @endphp
                <tr class="item-style">
                    <td class="text-align-center">{{ $pa->pa_number }}</td>
                    <td class="text-align-center">
                        @if (!optional($pa->mrs)->order_number)
                            @foreach ($pa->mrs_numbers() as $num)
                                <span class="status-label-info">{{ $num}}</span>
                            @endforeach
                        @else
                            <strong><span class="status-label-info">{{ $pa->mrs->order_number}}</span></strong>
                        @endif
                    </td>
                    <td class="text-align-center">
                        {{ ($createdAt = \Carbon\Carbon::parse($pa->created_at))->isToday() ? $createdAt->diffForHumans() : $createdAt->format('F j, Y h:i A') }} ({{ $pa->planner->name ?? "N/A" }})
                    </td>
                    <td class="text-align-center">{{ \Carbon\Carbon::parse($pa->approved_at)->format('F j, Y h:i A') }}</td>
                    <td class="text-align-center">
                        @foreach ($pa->items as $item)
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
                    <td class="text-align-center">{{ $pa->received_at ? Carbon\Carbon::parse($pa->received_at)->format('F j, Y h:i A') : 'N/A' }}</td>
                    <td class="text-align-center">
                        @if($pa->received_at)
                            @if($bal == 0)
                                {{ "Completed" }}
                            @else
                                @php
                                    $receivedAt = Carbon\Carbon::parse($pa->received_at);
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
                    <td class="text-align-center">{{ $pa->received_at ? $bal : 'N/A' }}</td>
                    <td class="text-align-center">
                        @if (!optional($pa->mrs)->order_number)
                            @if (str_contains($pa->status, 'CANCELLED'))
                                <span style="color: red;">{{ strtoupper($pa->status) }}</span>
                            @else
                                <span style="color: green;">{{ strtoupper($pa->status) }}</span>
                            @endif
                        @else
                            @if (str_contains($pa->mrs->status, 'CANCELLED'))
                                <span style="color: red;">{{ strtoupper($pa->mrs->status) }}</span>
                            @else
                                <span style="color: green;">{{ strtoupper($pa->mrs->status) }}</span>
                            @endif
                        @endif
                    </td>
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
