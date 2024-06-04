@extends('admin.layouts.app')

@section('pagecss')
    <style>
        .table td {
            padding: 8px;
            font-size: 13px;
        }
        
        .table th {
            padding: 10px;
            color: black;
        }
        
        .title {
            font-weight: bold;
            color: #212529;
        }
        .badge {
            display: inline-block;
            padding: 8px;
            font-size: 0.8em;
            font-weight: bold;
            text-align: center;
            color: #fff; 
            background-color: #0168FA;
            border-radius: 0.25em;
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
<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-5">
                    <li class="breadcrumb-item" aria-current="page">CMS</li>
                    <li class="breadcrumb-item active" aria-current="page">IMF Details</li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">IMF Summary</h4>
        </div>
    </div>

    <div class="row row-sm p-0 mg-b-10">
        <div class="col-6 p-0">
            <a href="{{ route('imf.requests') }}" class="btn btn-secondary btn-sm">Back to Transaction List</a>
            @if($role->name === "MCD Planner" || $role->name === "MCD Verifier")
            <a href="#" id="printDetails" class="btn btn-success btn-sm" data-order="{{$request->items[0]['imf_no']}}">
                <i class="fas fa-print"></i> Print
            </a>
            @endif
        </div>
    <div class="col-6 d-flex justify-content-end align-items-center p-0">
            <span class="badge"><strong>STATUS:</strong> {{ $request->status }} </span>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-6 p-0 text-uppercase">
            @if($request->type === 'update' && $request->status === 'SUBMITTED')
            <div>
                <span class="title">IMF NO:</span> {{$request->items[0]['imf_no']}}
            </div>
            @endif
            <div>
                <span class="title">Department:</span> {{ $request->department }}
            </div>
            <div>
                <span class="title">Created By:</span> {{ $request->user->name }}
            </div>
            <div>
                <span class="title">Type:</span> {{ $request->type }}
            </div>
            @if($request->type === 'update' && $showStockCodeColumn)
            <div>
                <span class="title">Stock Code:</span> {{ $items[0]->stock_code }} 
            </div>
            @endif
        </div>
        <div class="col-6 p-0 text-uppercase">
            <div>
                <span class="title">Created At:</span> {{ $request->created_at->format('d-m-Y h:i:s A')  }}
            </div>
            <div>
                <span class="title">Updated By:</span> {{ $request->updated_at->format('d-m-Y h:i:s A')  }}
            </div>
            <div>
                <span class="title">Submitted At:</span> {{ \Carbon\Carbon::parse($request->submitted_at)->format('d-m-Y') }}
            </div>
            <div>
                <span class="title">Approved At:</span> {{ \Carbon\Carbon::parse($request->approved_at)->format('d-m-Y') }}
            </div>
        </div>
    </div>

    <form>
        @csrf
        <input type="hidden" name="sales_header_id">
        <div class="row row-sm" style="overflow-x: auto">
            @if($request->type === 'update') 
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
                    <th class="wd-20p">Purpose</th>
                    <th class="wd-10p">Min Quantity</th>
                    <th class="wd-10p">Brand</th>
                    <th class="wd-10p">Max Quantity</th>
                    <th class="wd-10p">OEM ID</th>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            @if ($showStockCodeColumn)
                                    <td>{{ $item->stock_code !== "null" ? $item->stock_code : '' }}</td>
                            @endif
                            <td>{{ $item->item_description }}</td>
                            <td>{{ $item->purpose }}</td>
                            <td>{{ $item->min_qty }}</td>
                            <td>{{ $item->brand }}</td>
                            <td>{{ $item->max_qty }}</td>
                            <td>{{ $item->OEM_ID }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </form>
    @if(auth()->user()->role_id == 4 && $request->status !== 'APPROVED - MCD') 
        <div>
            <a href="{{ route('imf.action',  ['action' => 'approve', 'type' => $request->type, 'id' => $request->id]) }}" class="btn btn-primary btn-sm">Approve</a>
            <a href="{{ route('imf.action',  ['action' => 'disapprove', 'type' => $request->type, 'id' => $request->id]) }}" class="btn btn-danger btn-sm">Disapprove</a>
        </div>
    @endif
</div>
@endsection


@section('pagejs')
<script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#printDetails').click(function(e) {
            e.preventDefault(); 

            var orderNumber = $(this).attr('data-order');

            $.ajax({
                url: "{{route('imf.generate_report')}}",
                type: 'GET',
                data: { id: orderNumber },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
                    if (data instanceof Blob) {
                        const pdfBlob = new Blob([data], { type: 'application/pdf' });
                        const pdfUrl = URL.createObjectURL(pdfBlob);
                        window.open(pdfUrl, '_blank');
                        URL.revokeObjectURL(pdfUrl);
                    }
                }
            });
        });
    });
</script>
@endsection 

