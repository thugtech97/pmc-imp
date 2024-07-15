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
            font-size: 10px;
        }
    </style>
</head>
<body> 
<div class="container-fluid content-wrap">
    <h4 class="modal-title" id="myModalLabel">Request No. {{ $sale->order_number }}</h4>
    <table style="margin-top: 15px">
        <thead>
            <tr>
                <td colspan="{{ $sale->status != 'COMPLETED' ? '10' : '5' }}" class="text-bold text-align-center header-style">MRS Transaction Summary</td>
            </tr>
            <tr style="border: 1px solid #dddddd">
                <td colspan="{{ $sale->status != 'COMPLETED' ? '6' : '3' }}" class="title" style="border: 0">
                    <div>
                        <strong class="title text-uppercase">Department:</strong> {{ $sale->customer_name }} 
                    </div>
                    <div>
                        <strong class="title text-uppercase">Requested by:</strong> {{ $sale->user->name }} 
                    </div>
                    <div>
                        <strong class="title text-uppercase">Delivery Type:</strong> 
                    </div>
                    <div>
                        <strong class="title text-uppercase">Budgeted:</strong> {{ $sale->budgeted_amount > 0 ? 'YES' : 'NO'}}
                    </div>
                    <div>
                        <strong class="title text-uppercase">Purpose:</strong> {{ $sale->purpose }}
                    </div>
                </td>
                <td colspan="{{ $sale->status != 'COMPLETED' ? '4' : '2' }}" class="title" style="border: 0">
                    <div><span class="text-uppercase">Posted Date:</span> {{ $sale->created_at }} </div>
                    <div><span class="text-uppercase">Date Needed:</span>  {{ $sale->delivery_date }} </div>
                    <div><span class="text-uppercase">Request Status:</span>  {{ $sale->status }} </div>
                    <div><span class="text-uppercase">Budgeted Amount: P </span> {!! number_format($sale->budgeted_amount, 2, '.', ',') !!} </div> 
                    <div><span class="text-uppercase">Adjusted Amount: P </span> {!! number_format($sale->adjusted_amount, 2, '.', ',') !!} </div>        
                </td>
            </tr>
            <tr>
                <td colspan="{{ $sale->status != 'COMPLETED' ? '10' : '5' }}" class="title">
                    <strong>Delivery Instruction:</strong> {{ $sale->other_instruction }}</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th width="10%">Stock Code</th>
                <th colspan="5" class="text-left">Item</th>
                <th width="10%">Cost Code</th>
                <th width="10%">OEM No.</th>
                <th width="10%">Requested Quantity</th>
                <th width="10%">Quantity to Request</th>
                {{--  
                @if ($sale->status != "COMPLETED")
                    <th class="d-none" width="1%">Issuance Quantity</th>
                @endif
                --}}
            </tr>
            @php 
                $total_qty = 0; 
                $total_sales = 0; 
            @endphp

            @forelse($sale->items as $item)
                @php
                    $total_qty += $item->qty;
                    $total_sales += $item->qty * $item->price;
                    $bal = ($item->qty - $item->issuances->sum('qty'));
                @endphp
                <tr class="pd-20">
                    <td class="tx-left">{{ $item->product->code }}</td>
                    <td colspan="5" class="tx-nowrap">{{ $item->product_name }}</td>
                    <td class="tx-right">{{ $item->cost_code }}</td>
                    <td class="tx-right">{{ $item->product->oem }}</td>
                    <td class="tx-right">{{ number_format($item->qty, 2) }}</td>
                    <td class="tx-right">
                        {{ number_format($item->qty_to_order, 2) }}
                        {{--  
                        @if ($item->issuances->sum('qty') > 0)
                            <a href="javascript:;" data-toggle="modal" data-target="#issuanceModal{{ $item->id }}">
                                {{ number_format($item->issuances->sum('qty'), 2) }}
                            </a>
                        @else
                            {{ number_format($item->issuances->sum('qty'), 2) }}
                        @endif
                        --}}
                    </td>
                    {{--  
                    @if ($sale->status !== "COMPLETED")
                        <td class="tx-right d-none">
                            @if($bal > 0)
                                <input 
                                    type="number" 
                                    class="form-control text-right" 
                                    name="deploy{{ $item->id }}" 
                                    value="0" 
                                    min="0" 
                                    max="{{ $bal }}"
                                    required="required"
                                >
                            @endif
                        </td>
                    @endif
                    --}}
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