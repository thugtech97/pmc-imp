@extends('admin.layouts.app')

@section('pagecss')
    <style>
        .table td {
            padding: 10px;
            font-size: 13px;
        }
    </style>
@endsection

@section('content')
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
        <div>
            <a href="{{ route('imf.requests') }}" class="btn btn-secondary btn-sm">Back to Transaction List</a>
        </div>
    </div>

    <div class="row row-sm mg-b-10" style="background-color:#FDE9DE;">
        <div class="col-sm-6 col-lg-8 mg-t-10">
            <p class="mg-b-3">Department: <span class="tx-success tx-semibold">{{ $request->department }}</span></p>
            <p class="mg-b-3">Section:</p>
            <p class="mg-b-3">Division:</p>
            <p class="mg-b-3">Request Type: <span class="tx-success tx-semibold">{{ strtoupper($request->type) }}</span> </p>
        </div>
        
    </div>

    <form>
        @csrf
        <input type="hidden" name="sales_header_id">
        <div class="row row-sm" style="overflow-x: auto">
            <table class="table table-bordered mg-b-10">
                <tbody>
                    <tr style="background-color:#000000;">
                        <th class="text-white wd-10p">Stock Code</th>
                        <th class="text-white wd-30p">Item Description</th>
                        <th class="text-white wd-20p">Purpose</th>
                        <th class="text-white wd-10p">Min Quantity</th>
                        <th class="text-white wd-10p">Brand</th>
                        <th class="text-white wd-10p">Max Quantity</th>
                        <th class="text-white wd-10p">OEM ID</th>
                    </tr>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->stock_code }}</td>
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
        </div>
    </form>
    <div>
        <a href="{{ route('imf.action',  ['action' => 'approve', 'type' => $request->type, 'id' => $request->id]) }}" class="btn btn-primary btn-sm">Approve</a>
        <a href="{{ route('imf.action',  ['action' => 'disapprove', 'type' => $request->type, 'id' => $request->id]) }}" class="btn btn-danger btn-sm">Disapprove</a>
    </div>

</div>
@endsection