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
        .highlight-row {
            background-color: #dff0e0 !important;
        }
        #loadingSpinner {
            position: fixed;
            top: 50%;
            left: 50%;
            background-color: rgba(255, 255, 255, 0.8); 
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link href="{{ asset('lib/filter-multiselect/filter-multiselect.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="container pd-x-0">
    
        <!-- Loading spinner -->
        <div class="spinner-border" role="status"  style="width: 4rem; height: 4rem;" id="loadingSpinner">
            <span class="sr-only">Loading...</span>
        </div>
        <!-- Loading spinner -->

        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-1">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5" style="background-color:white;">
                        <li class="breadcrumb-item" aria-current="page">CMS</li>
                        <li class="breadcrumb-item active" aria-current="page">IMF</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Inventory Maintenance Form</h4>
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

                        <div class="col-12 mb-3 p-0">
                            <form class="form-inline d-flex justify-content-end" id="searchForm" style="font-size:12px;">
                                <div class="px-2">
                                    <input name="search" type="search" id="search" class="form-control" style="font-size: 12px; width: 200px;" placeholder="Search">
                                </div>

                                <div>
                                    <button class="btn btn-sm btn-success px-4" type="button" id="btnSearch">Search</button>
                                    <a class="btn btn-sm btn-secondary px-4" href="{{route('imf.requests')}}">Reset</a>
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
                            <th>IMF No</th>
                            <th>Stock Code</th>
                            <th>Department</th>
                            <th>Date Prepared</th>
                            <th>Date Submitted</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($imfs as $imf)
                            <tr class="pd-20 {{ today()->isSameDay($imf->approved_at) && $imf->status === 'APPROVED - WFS' ? 'highlight-row' : '' }}">
                                    <td>{{ $imf->id }}</td>
                                    <td>{{ $imf->type == 'update' ? $imf->items[0]['stock_code'] : '--- N/A ---' }}</td>
                                    <td class="text-uppercase">{{ $imf->department }}</td>
                                    <td>{{ $imf->created_at}}</td>
                                    <td>{{ $imf->submitted_at ?? '-' }}</td>
                                    <td>{{ strtoupper($imf->type) }}</td>
                                    <td><span class="text-success">{{ $imf->status }}</span></td>
                                    <td>
                                        <nav class="nav table-options">
                                            <a class="nav-link" href="{{ route('imf.requests.view', $imf->id) }}" title="View IMF"><i data-feather="eye"></i></a>
                                            @if($imf->status == 'APPROVED - WFS')
                                            <i data-feather="chevrons-down"></i>
                                            @endif
                                            @if($imf->status == 'APPROVED - MCD')
                                            <i data-feather="check-circle"></i>
                                            @endif
                                        </nav>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <th colspan="17" style="text-align: center;"> <p class="text-danger">No WFS APPROVED IMF requests.</p></th>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End Pages -->
            <div class="col-md-6">
                <div class="mg-t-5">
                    @if ($imfs->firstItem() == null)
                        <p class="tx-gray-400 tx-12 d-inline">{{__('common.showing_zero_items')}}</p>
                    @else
                        <p class="tx-gray-400 tx-12 d-inline">Showing {{ $imfs->firstItem() }} to {{ $imfs->lastItem() }} of {{ $imfs->total() }} items</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-md-right float-md-right mg-t-5">
                    <div>
                        {{ $imfs->appends((array) $filter)->links() }}
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

@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ asset('lib/filter-multiselect/filter-multiselect-bundle.min.js') }}"></script>

    <script>
        let listingUrl = "{{ route('imf.requests') }}";
        let searchType = "{{ $searchType }}";

        $(document).ready(function() {
            $('#btnSearch').click(function() {
                $('#loadingSpinner').show();
            });
        })
    </script>

    <script src="{{ asset('js/listing.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
@endsection
