@extends('admin.layouts.app')

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet">

    <style>
        .table td {
            padding: 10px;
            font-size: 13px;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link href="{{ asset('lib/filter-multiselect/filter-multiselect.css') }}" rel="stylesheet">
@endsection

@section('content')

    <div style="margin-left: 100px; margin-right: 100px;">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5" style="background-color:white;">
                        <li class="breadcrumb-item" aria-current="page">CMS</li>
                        <li class="breadcrumb-item active" aria-current="page">MRS for PA</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">For Purchase Advice</h4>
            </div>
        </div>

        <div class="row row-sm">

            <!-- Start Filters -->
            <div class="col-md-12">
                <div class="filter-buttons">
                    <div class="d-md-flex bd-highlight">
                        <div class="bd-highlight mg-r-10 mg-t-10" style="display:none;">
                            <div class="dropdown d-inline mg-r-5">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{__('common.filters')}}
                                </button>
                                <div class="dropdown-menu">
                                    <form id="filterForm" class="pd-20">
                                       
                                        
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" id="showDeleted" name="showDeleted" class="custom-control-input">
                                                <label class="custom-control-label" for="showDeleted">{{__('common.show_deleted')}}</label>
                                            </div>
                                        </div>
                                        
                                        <button id="filter" type="button" class="btn btn-sm btn-primary" style="dispaly:none;">{{__('common.apply_filters')}}</button>
                                    </form>
                                </div>
                            </div>

                        </div>

                        <div class="bd-highlight mg-t-10 mg-r-5">
                            <form class="form-inline" id="searchForm" style="font-size:12px;">
                             
                                    
                                    <div class="mg-b-10 mg-r-5">
                                        <input name="search" type="search" id="search" class="form-control" style="font-size:12px;width: 150px;"  placeholder="Search Order Number">
                                    </div>

                                    <div class="mg-b-10">
                                        <button class="btn btn-sm btn-info" type="button" id="btnSearch">Search</button>
                                        <a class="btn btn-sm btn-success" href="{{route('pa.index')}}">Reset</a>
                                    </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <!-- End Filters -->

            <!-- Start Pages -->
            <div class="col-md-12">
                <div class="table-list mg-b-10">
                    <table class="table mg-b-0 table-light table-hover" id="table_sales">
                        <thead>
                        <tr>
                            <th>Request #</th>
                            <th>PA #</th>
                            <th>Posted Date</th>
                            <th>Department</th>
                            <th>Received Date</th>
                            <th>Aging</th>
                            <th>Total Balance</th>
                            <th>Request Status</th>
                            <th>Purchaser</th>
                            <th class="exclude_export">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                @php
                                    $bal = $sale->items->sum('qty_to_order') - $sale->items->sum('qty_ordered');
                                @endphp
                                <tr class="pd-20">
                                    <td><strong> {{$sale->order_number }}</strong></td>
                                    <td><strong> {{$sale->purchaseAdvice->pa_number ?? "N/A" }}</strong></td>
                                    <td>{{ $sale->created_at }}</td>
                                    <!--<td class="text-uppercase">{{ $sale->delivery_type }}</td>
                                    <td>{{ $sale->delivery_date }}</td>-->
                                    <td>{{ $sale->user->department->name }}</td>
                                    <td>{{ $sale->received_at ? Carbon\Carbon::parse($sale->received_at)->format('m/d/Y h:i A') : 'N/A' }}</td>
                                    <td>
                                        @if($sale->received_at)
                                            @if($bal == 0)
                                                {{ "✔️" }}
                                            @else
                                                @php
                                                    $receivedAt = Carbon\Carbon::parse($sale->received_at);
                                                    $now = Carbon\Carbon::now();
                                                    $days = $receivedAt->diffInDays($now);
                                                    $hours = $receivedAt->copy()->addDays($days)->diffInHours($now);
                                                @endphp
                                                <span style="color: red;">
                                                    {{ $days > 0 ? $days . ' day' . ($days > 1 ? 's' : '') : '' }}
                                                    {{ $hours > 0 ? $hours . ' hour' . ($hours > 1 ? 's' : '') : '' }}
                                                </span>
                                            @endif
                                        @else
                                            {{ 'N/A' }}
                                        @endif
                                    </td>                                
                                    <td>{{ $sale->received_at ? $bal : 'N/A' }}</td>
                                    <!--<td><a href="{{route('admin.report.delivery_report',$sale->id)}}" target="_blank">{{$sale->delivery_status}}</a></td>-->
                                    <td>{{ strtoupper($sale->status) }}</td>
                                    <td>{{ $sale->purchaser->name ?? '' }}</td>
                                    <td>
                                        <nav class="nav table-options">
                                            <a class="nav-link" href="{{ route('pa.view_mrs',$sale->id) }}" title="View MRS"><i data-feather="eye"></i></a>
                                        </nav>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <th colspan="17" style="text-align: center;"> <p class="text-danger">No MRS subject for PA.</p></th>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End Pages -->
            <div class="col-md-6">
                <div class="mg-t-5">
                    @if ($sales->firstItem() == null)
                        <p class="tx-gray-400 tx-12 d-inline">{{__('common.showing_zero_items')}}</p>
                    @else
                        <p class="tx-gray-400 tx-12 d-inline">Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} items</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-md-right float-md-right mg-t-5">
                    <div>
                        {{ $sales->appends((array) $filter)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="" id="posting_form" style="display:none;" method="post">
        @csrf
        <input type="text" id="pages" name="pages">
        <input type="text" id="status" name="status">
    </form>

    @include('admin.ecommerce.sales.modals')
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ asset('lib/filter-multiselect/filter-multiselect-bundle.min.js') }}"></script>

    <script>
        let listingUrl = "{{ route('pa.index') }}";
        let searchType = "{{ $searchType }}";
    </script>

    <script src="{{ asset('js/listing.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
@endsection
