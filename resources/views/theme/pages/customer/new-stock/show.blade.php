@extends(auth()->check() ? 'theme.main' : 'theme.main-blank-template')

@section('pagecss')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <style>
        .row.status {
            display: flex;
            align-items: center;
            justify-content: center;

            .text-right {
                text-align: right;

                .badge {
                    display: inline-block;
                    padding: 0.7rem;
                    font-size: 0.9em;
                    font-weight: bold;
                    color: #fff; 
                    background-color: #3395ff;
                    border-radius: 0.25em;
                }
            }
        }
        .title {
            font-weight: bold;
            color: #212529;
        }
        .old-item {
            background-color: #f0f1f2 !important;
        }
    </style>
@endsection
@section('content')
    @php
        $showStockCodeColumn = $items->isNotEmpty() && $items->contains(function ($item) {
            return $item->stock_code !== "null" && $item->stock_code !== null && $item->stock_code !== '';
        });
    @endphp
    <div class="container-fluid content-wrap">
        <div class="row">
            <div class="col-md-12">
                <div class="row status">
                    <div class="col-6"> 
                        @if(auth()->check())
                        <a href="{{ route('new-stock.index') }}" class="btn btn-secondary px-3">
                            Back
                        </a>
                        @endif
                    </div>
                    <div class="col-6 text-right">
                       <span class="badge"> <strong>STATUS:</strong> {{ $request->status }} </span>
                    </div>
                </div>
                <div class="row mx-0 mt-4 mb-3">
                    <table class="m-0 text-uppercase">
                        @if($request->type === 'update' && $request->status === 'SUBMITTED')
                        <tr>
                            <td><span class="title">IMF NO:</span> {{$request->items[0]['imf_no']}} </td>
                            <td></td>
                        </tr>
                        @endif
                        <tr>
                            <td><span class="title">Department:</span> {{ $request->department }}</td>
                            <td><span class="title">Created at:</span> {{ $request->created_at  }}</td>
                        </tr>
                        <tr>
                            <td><span class="title">Created by:</span> {{ $request->user->name}}</td>
                            <td> 
                                @if($request->updated_at != $request->created_at)
                                <span class="title">Update by:</span> {{ $request->updated_at }} 
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><span class="title">Type:</span> {{ $request->type }}</td>
                            <td>
                                @if($request->submitted_at)
                                <span class="title">Submitted at:</span> {{ $request->submitted_at }}
                                @endif
                            </td>
                        </tr>
                        @if($request->type === 'update' && $showStockCodeColumn)
                        <tr>
                            <td><span class="title">Stock Code:</span> {{ $items[0]->stock_code }} </td>
                            <td></td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if($request->type === 'update') 
                <table class="table">
                    <thead>
                        <th></th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </thead>
                    <tbody>
                        <tr>
                            <th width="20%">Item Description</th>
                            @if (!empty($oldItems[0]))
                            <td class="old-item">{{ $oldItems[0]->item_description ?? '' }}</td>
                            @endif
                            <td>{{ $items[0]->item_description }}</td>
                        </tr>
                        <tr>
                            <th width="20%">Brand</th>
                            @if (!empty($oldItems[0]))
                            <td class="old-item">{{ $oldItems[0]->brand ?? '' }}</td>
                            @endif
                            <td>{{ $items[0]->brand }}</td>
                        </tr>
                        <tr>
                            <th width="20%">OEM ID</th>
                            @if (!empty($oldItems[0]))
                            <td class="old-item">{{ $oldItems[0]->OEM_ID ?? '' }}</td>
                            @endif
                            <td>{{ $items[0]->OEM_ID }}</td>
                        </tr>
                        <tr>
                            <th width="20%">OuM</th>
                            @if (!empty($oldItems[0]))
                            <td class="old-item">{{ $oldItems[0]->UoM ?? '' }}</td>
                            @endif
                            <td>{{ $items[0]->UoM }}</td>
                        </tr>
                        <tr>
                            <th width="20%">Usage Rate Qty</th>
                            @if (!empty($oldItems[0]))
                            <td class="old-item">{{ $oldItems[0]->usage_rate_qty ?? '' }}</td>
                            @endif
                            <td>{{ $items[0]->usage_rate_qty }}</td>
                        </tr>
                        <tr>
                            <th width="20%">Usage Frequency</th>
                            @if (!empty($oldItems[0]))
                            <td class="old-item">{{ $oldItems[0]->usage_frequency ?? '' }}</td>
                            @endif
                            <td>{{ $items[0]->usage_frequency }}</td>
                        </tr>
                        <tr>
                            <th width="20%">Min Qty</th>
                            @if (!empty($oldItems[0]))
                            <td class="old-item">{{ $oldItems[0]->min_qty ?? '' }}</td>
                            @endif
                            <td>{{ $items[0]->min_qty }}</td>
                        </tr>
                        <tr>
                            <th width="20%">Max Qty</th>
                            @if (!empty($oldItems[0]))
                            <td class="old-item">{{ $oldItems[0]->max_qty ?? '' }}</td>
                            @endif
                            <td>{{ $items[0]->max_qty }}</td>
                        </tr>
                        <tr>
                            <th width="20%">Purpose</th>
                            @if (!empty($oldItems[0]))
                            <td class="old-item">{{ $oldItems[0]->purpose ?? '' }}</td>
                            @endif
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
                        <th>Item Description</th>
                        <th>Brand</th>
                        <th>OEM ID</th>
                        <th>OuM</th>
                        <th>Usage Rate Qty</th>
                        <th>Usage Frequency</th>
                        <th>Min Qty</th>
                        <th>Max Qty</th>
                        <th width="20%">Purpose</th>
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
                            <tr>
                                <td colspan="8" style="text-align: center;">No requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
@endsection
