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

    <div class="container pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5" style="background-color:white;">
                        <li class="breadcrumb-item" aria-current="page">CMS</li>
                        <li class="breadcrumb-item active" aria-current="page">Manage Purchase Advice</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Purchase Advice List</h4>
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
                            <th>Order #</th>
                            <th>Posted Date</th>
                            <th>Department</th>
                            <th>Order Status</th>
                            <th class="exclude_export">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                @php
                                    $bal = $sale->items->sum('qty') - $sale->issuances_sum_qty;
                                @endphp
                                <tr class="pd-20">
                                    <td><strong> {{$sale->order_number }}</strong></td>
                                    <td>{{ $sale->created_at }}</td>
                                    <td>{{ $sale->user->department->name }}</td>
                                    <td>{{ strtoupper($sale->status) }}</td>
                                    <td>
                                        <nav class="nav table-options">
                                            <a class="nav-link" href="#" title="Print Purchase Advice" id="print" data-order-number="{{$sale->order_number}}">
                                                <i data-feather="printer"></i>
                                            </a>
                                        </nav>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <th colspan="17" style="text-align: center;"> <p class="text-danger">No Purchase Advice.</p></th>
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
    <script src="{{ asset('js/listing.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    <script>
        let listingUrl = "{{ route('pa.manage') }}";
        let searchType = "{{ $searchType }}";

        $(document).ready(function() {
            $('#print').click(function(evt) {
                evt.preventDefault();

                var orderNumber = this.getAttribute('data-order-number');

                console.log('Print button clicked', orderNumber);

                $.ajax({
                    url: "{{route('pa.generate_report')}}",
                    type: 'GET',
                    data: { orderNumber: orderNumber },
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
