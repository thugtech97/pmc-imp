@extends('admin.layouts.app')

@section('pagecss')
    <style>
        .row-selected {
            background-color: #92b7da !important;
        }
    </style>
@endsection

@section('content')
    <div class="container pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5">
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('products.index')}}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Inventory History Logs</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Inventory Logs</h4>
            </div>
        </div>

        <div class="row row-sm">


            <!-- Start Pages -->
            <div class="col-md-12">
                <div class="table-list mg-b-10">
                    <div class="table-responsive-lg">
                        <table class="table mg-b-0 table-light table-hover"  style="table-layout: fixed;word-wrap: break-word;">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Product ID</th>
                                    <th style="width: 15%;">Product Name</th>
                                    <th style="width: 10%;">Old Value</th>
                                    <th style="width: 10%;">New Value</th>
                                    <th style="width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr class="row_cb">
                                    <td>{{ $log->product_id }}</td>
                                    <td>{{ $log->product->name }}</td>
                                    <td>{{ $log->old_value }}</td>
                                    <td>{{ $log->new_value }}</td>
                                    <td>
                                        <a href="{{ $log->file }}" target="_blank">Open File</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <th colspan="9" style="text-align: center;"> <p class="text-danger">No logs found.</p></th>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Pages -->
            <div class="col-md-6">
                <div class="text-md-right float-md-right mg-t-5">
                    <div>
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pagejs')
@endsection

@section('customjs')
    <script>
    </script>
@endsection
