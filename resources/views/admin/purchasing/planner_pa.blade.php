@extends('admin.layouts.app')

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link href="{{ asset('lib/filter-multiselect/filter-multiselect.css') }}" rel="stylesheet">
    <style>
        .table td {
            padding: 10px;
            font-size: 13px;
        }
        #del_status .dropdown-toggle {
            width: 160px; 
        }
        #loadingSpinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            font-size: 15px;
            width: 1em;
            height: 1em;
            border-radius: 50%;
            text-indent: -9999em;
            animation: mulShdSpin 1.1s infinite ease;
            transform: translateZ(0);
        }
        @keyframes mulShdSpin {
            0%,
            100% {
                box-shadow: 0em -2.6em 0em 0em #ffffff, 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.5), -1.8em -1.8em 0 0em rgba(255,255,255, 0.7);
            }
            12.5% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.7), 1.8em -1.8em 0 0em #ffffff, 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.5);
            }
            25% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.5), 1.8em -1.8em 0 0em rgba(255,255,255, 0.7), 2.5em 0em 0 0em #ffffff, 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            37.5% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.5), 2.5em 0em 0 0em rgba(255,255,255, 0.7), 1.75em 1.75em 0 0em #ffffff, 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            50% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.5), 1.75em 1.75em 0 0em rgba(255,255,255, 0.7), 0em 2.5em 0 0em #ffffff, -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            62.5% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.5), 0em 2.5em 0 0em rgba(255,255,255, 0.7), -1.8em 1.8em 0 0em #ffffff, -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            75% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.5), -1.8em 1.8em 0 0em rgba(255,255,255, 0.7), -2.6em 0em 0 0em #ffffff, -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            87.5% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.5), -2.6em 0em 0 0em rgba(255,255,255, 0.7), -1.8em -1.8em 0 0em #ffffff;
            }
        }
        body.search-active::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh; 
            background-color: rgba(0, 0, 0, 0.5); 
            z-index: 999; 
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        
        <div id="loadingSpinner"></div>

        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5" style="background-color:white;">
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Purchase Advice</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Manage Purchase Advice</h4>
                @if ($role->name === "MCD Planner")
                <a class="btn btn-sm btn-info mt-2" type="button" href="{{ route('planner_pa.create') }}"><i class="fa fa-plus"></i> Create Purchase Advice</a>
                @endif
                <a class="btn btn-sm btn-info mt-2" href="javascript:;" onclick="$('#show-generate-pa').modal('show');"><i class="fa fa-print"></i> Generate Report</a>
            </div>
        </div>

        <div class="row row-sm">

            <!-- Start Filters -->
            <div class="col-md-12">
                <div class="filter-buttons">
                    <div class="row m-0 p-0">
                        <div class="bd-highlight mg-r-10 mg-t-10" style="display:none;">
                            <div class="dropdown d-inline mg-r-5">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{__('common.filters')}}
                                </button>
                                <div class="dropdown-menu">
                                    <form id="filterForm" class="pd-20">
                                      
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" id="showDeleted" name="showDeleted" class="custom-control-input" @if ($filter->showDeleted) checked @endif>
                                                <label class="custom-control-label" for="showDeleted">{{__('common.show_deleted')}}</label>
                                            </div>
                                        </div>
                                        
                                        <button id="filter" type="button" class="btn btn-sm btn-primary" style="dispaly:none;">{{__('common.apply_filters')}}</button>
                                    </form>
                                </div>
                            </div>

                        </div>

                        <div class="col-12 mx-0 mb-2 p-0">
                            <form class="form-inline" id="searchForm" style="font-size: 12px;">
                                    <div class="col-2 p-0 m-0">
                                        <input name="search" type="search" id="search" class="form-control" style="font-size:12px;width: 170px;"  placeholder="Search PA Number" value="{{ $filter->search }}">
                                    </div>
                                    <div class="col-2 p-0 m-0 row">
                                        <div class="col-2 p-0 align-self-center">
                                            Start:
                                        </div>
                                        <div class="col-10 p-0 align-self-center">
                                            <input name="startdate" type="date" id="startdate" style="font-size:12px; width: 140px;" class="form-control"
                                                value="@if(isset($_GET['startdate'])  && strlen($_GET['startdate'])>1){{ date('Y-m-d',strtotime($_GET['startdate'])) }}@endif" >
                                        </div>
                                    </div>
                                    <div class="col-2 p-0 m-0 row">
                                        <div class="col-2 p-0 align-self-center">
                                            End:
                                        </div>
                                        <div class="col-10 p-0 align-self-center">
                                        <input name="enddate" type="date" id="enddate" style="font-size: 12px; width: 140px;" class="form-control"
                                            value="@if(isset($_GET['enddate'])  && strlen($_GET['enddate'])>1 ){{ date('Y-m-d',strtotime($_GET['enddate'])) }}@endif">
                                        </div>
                                    </div>
                                    <div class="col-2 p-0 m-0 text-center">
                                        <select multiple name="status[]" id="del_status" class="form-control" style="font-size: 12px; width: 200px;">
                                            <option value="COMPLETED" {{ isset($_GET['status']) && in_array("COMPLETED", $_GET['status']) ? 'selected' : '' }}>COMPLETED</option>
                                            <option value="PARTIAL" {{ isset($_GET['status']) && in_array("PARTIAL", $_GET['status']) ? 'selected' : '' }}>PARTIAL</option>
                                            <option value="UNSERVED" {{ isset($_GET['status']) && in_array("UNSERVED", $_GET['status']) ? 'selected' : '' }}>UNSERVED</option>
                                        </select>
                                    </div>
                                    <div class="col-2 p-0 m-0 text-center">
                                        <button class="btn btn-sm btn-success px-4" type="button" id="btnSearch">Search</button>
                                        <a class="btn btn-sm btn-secondary px-4" href="{{route('planner_pa.index')}}">Reset</a>
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
                            <th>PA #</th>
                            <th>MRS #</th>
                            <th>Created At</th>
                            <th>MCD Manager Approved At</th>
                            <th>Current PO</th>
                            <th>Purchaser Received At</th>
                            <th>Aging</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th class="exclude_export"></th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                @php
                                    $bal = (!optional($sale->mrs)->order_number) ? $sale->items->sum('qty_to_order') - $sale->items->sum('qty_ordered') 
                                    : $sale->mrs->items->where('promo_id', '!=', 1)->sum('qty_to_order') - $sale->mrs->items->where('promo_id', '!=', 1)->sum('qty_ordered');
                                @endphp
                                <tr class="pd-20">
                                    <td><strong>{{ $sale->pa_number }}</strong></td>
                                    <td>
                                        @if (!optional($sale->mrs)->order_number)
                                            @foreach ($sale->mrs_numbers() as $mrs)
                                                <span class="badge bg-primary text-white">{{ $mrs}}</span>
                                            @endforeach
                                        @else
                                            <strong><span class="badge bg-primary text-white">{{ $sale->mrs->order_number}}</span></strong>
                                        @endif
                                    </td>
                                    <td>{{ ($createdAt = \Carbon\Carbon::parse($sale->created_at))->isToday() ? $createdAt->diffForHumans() : $createdAt->format('F j, Y h:i A') }} ({{ $sale->planner->name ?? "N/A" }})</td>
                                    <td>
                                        @if (!optional($sale->mrs)->order_number)
                                            {{ $sale->approved_at ? \Carbon\Carbon::parse($sale->approved_at)->format('F j, Y h:i A') : 'N/A' }}
                                        @else
                                            {{ $sale->mrs->approved_at ? \Carbon\Carbon::parse($sale->mrs->approved_at)->format('F j, Y h:i A') : 'N/A' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (!optional($sale->mrs)->order_number)
                                            @foreach ($sale->items as $item)
                                                @if (!empty($item->po_no))
                                                    @php
                                                        $badgeClass = ($item->qty_to_order == $item->qty_ordered) ? 'badge bg-success text-white' : 'badge bg-danger text-white';
                                                    @endphp
                                                    <span class="{{ $badgeClass }}">
                                                        {{ $item->po_no }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        @else
                                            @foreach ($sale->mrs->items as $item)
                                                @if (!empty($item->po_no))
                                                    @php
                                                        $badgeClass = ($item->qty_to_order == $item->qty_ordered) ? 'badge bg-success text-white' : 'badge bg-danger text-white';
                                                    @endphp
                                                    <span class="{{ $badgeClass }}">
                                                        {{ $item->po_no }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>                                               
                                    <td>
                                        @if (!optional($sale->mrs)->order_number)
                                            {{ $sale->received_at ? Carbon\Carbon::parse($sale->received_at)->format('F j, Y h:i A') : 'N/A' }}
                                        @else
                                        {{ $sale->mrs->received_at ? Carbon\Carbon::parse($sale->mrs->received_at)->format('F j, Y h:i A') : 'N/A' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (!optional($sale->mrs)->order_number)
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
                                                    <span style="{{ $days >= 14 ? 'color: red;' : 'color: blue;' }}">
                                                        {{ $days > 0 ? $days . ' day' . ($days > 1 ? 's' : '') : '' }}
                                                        {{ $days == 0 ? $hours . ' hour' . ($hours > 1 ? 's' : '') : '' }}
                                                    </span>
                                                @endif
                                            @else
                                                {{ 'N/A' }}
                                            @endif
                                        @else
                                            @if($sale->mrs->received_at)
                                                @if($bal == 0)
                                                    {{ "✔️" }}
                                                @else
                                                    @php
                                                        $receivedAt = Carbon\Carbon::parse($sale->mrs->received_at);
                                                        $now = Carbon\Carbon::now();
                                                        $days = $receivedAt->diffInDays($now);
                                                        $hours = $receivedAt->copy()->addDays($days)->diffInHours($now);
                                                    @endphp
                                                    <span style="{{ $days >= 14 ? 'color: red;' : 'color: blue;' }}">
                                                        {{ $days > 0 ? $days . ' day' . ($days > 1 ? 's' : '') : '' }}
                                                        {{ $days == 0 ? $hours . ' hour' . ($hours > 1 ? 's' : '') : '' }}
                                                    </span>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ (!optional($sale->mrs)->order_number) ? ($sale->received_at ? $bal : 'N/A') : ($sale->mrs->received_at ? $bal : 'N/A') }}</td>
                                    <td>
                                        @if (!optional($sale->mrs)->order_number)
                                            @if (str_contains($sale->status, 'CANCELLED'))
                                                <span class="text-danger">{{ strtoupper($sale->status) }}</span>
                                            @else
                                                <span class="text-success">{{ strtoupper($sale->status) }}</span>
                                            @endif
                                        @else
                                            @if (str_contains($sale->mrs->status, 'CANCELLED'))
                                                <span class="text-danger">{{ strtoupper($sale->mrs->status) }}</span>
                                            @else
                                                <span class="text-success">{{ strtoupper($sale->mrs->status) }}</span>
                                            @endif
                                        @endif
                                    </td>                                    
                                    <td>
                                        <nav class="nav table-options">
                                            @if (!optional($sale->mrs)->order_number)
                                                <a class="nav-link" href="{{ route('pa.pa_view',$sale->id) }}" title="View PA"><i data-feather="eye"></i></a>
                                                <a class="nav-link print2" href="#" title="Print Purchase Advice" data-pa-number="{{ $sale->pa_number }}">
                                                    <i data-feather="printer"></i>
                                                </a>
                                                @if($role->name === "MCD Planner")
                                                    <a class="nav-link" href="{{ route('pa.delete_pa', $sale->id) }}" 
                                                        onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this PA?')) document.getElementById('delete-form-{{ $sale->id }}').submit();" 
                                                        title="Delete PA">
                                                        <i data-feather="trash"></i>
                                                    </a>
                                                    <form id="delete-form-{{ $sale->id }}" action="{{ route('pa.delete_pa', $sale->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @endif
                                            @endif
                                            @if (optional($sale->mrs)->order_number)
                                                <a class="nav-link print" href="#" title="Print Purchase Advice" data-order-number="{{ $sale->mrs->order_number }}">
                                                    <i data-feather="printer"></i>
                                                </a>
                                            @endif
                                        </nav>
                                    </td>
                                </tr>                            
                            @empty
                                <tr>
                                    <th colspan="10" style="text-align: center;"> <p class="text-danger">No Purchase Advice found.</p></th>
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
        let listingUrl = "{{ route('planner_pa.index') }}";
        let searchType = "{{ $searchType }}";
    </script>

    <script src="{{ asset('js/listing.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
@endsection

@section('customjs')
    <script>
        $('#del_status').filterMultiSelect();

        function post_form(id,status,pages){
            $('#posting_form').attr('action',id);
            $('#pages').val(pages);
            $('#status').val(status);
            $('#posting_form').submit();
        }

        $(".js-range-slider").ionRangeSlider({
            grid: true,
            from: selected,
            values: perPage
        });


        $('#prompt-change-status').on('show.bs.modal', function (e) {
            //get data-id attribute of the clicked element
            let sales = e.relatedTarget;
            let salesId = $(sales).data('id');
            let salesStatus = $(sales).data('status');
            let formAction = "{{ route('sales-transaction.quick_update', 0) }}".split('/');
            formAction.pop();
            let editFormAction = formAction.join('/') + "/" + salesId;
            $('#editForm').attr('action', editFormAction);
            $('#id').val(salesId);
            $('#editStatus').val(salesStatus);

        });

        function change_delivery_status(id,status){
            var checked = $('.cb:checked');
            
            var count = checked.length;

            if(count == 1){
                checked.each(function () {
                    $('body #del_id').val($(this).val());
                });
            }

            if(count > 1) {

                var ids = [];
                checked.each(function(){
                    ids.push(parseInt($(this).val()));
                });

                $('body #del_id').val(ids.join(','));
            }
            if(count < 1){
                $('body #del_id').val(id);
            }
            if(status != 'Delivered'){
                $('#prompt-change-delivery-status').modal('show');
            } else {
                $('#prompt-change-delivery-status-delivered').modal('show');
            }
            
        }

        $(function() {
            $('#search').keypress(function(event) {
                if (event.which === 13) 
                { 
                    event.preventDefault();
                }
            });

            $('#btnSearch').click(function() {
                $('body').addClass('search-active');
                $('#loadingSpinner').show();
            });
        });

        $('.print').click(function(evt) {
            evt.preventDefault();

            var orderNumber = this.getAttribute('data-order-number');
            console.log('Print button clicked', orderNumber);
            $('#printModal').modal('show');
            $('#generateReportBtn').click(function() {
                var selectedFormat = $('input[name="fileFormat"]:checked').val();
                if (selectedFormat === 'pdf') {
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
                        },
                        error: function(xhr, status, error) {
                            console.error("Error generating PDF:", error);
                        }
                    });
                } else if (selectedFormat === 'excel') {
                    window.location.href = "{{ route('pa.generate_report_pa_excel') }}?orderNumber=" + orderNumber;
                }
                $('#printModal').modal('hide');
            });
        });
        
        $('.print2').click(function(evt) {
            evt.preventDefault();

            var paNumber = this.getAttribute('data-pa-number');

            console.log('Print button clicked', paNumber);

            $.ajax({
                url: "{{route('pa.generate_report_pa')}}",
                type: 'GET',
                data: { paNumber: paNumber },
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

        $('#generate-pa-form').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            
            // Get form data
            var formData = $(this).serialize();
            
            // Perform AJAX request
            $.ajax({
                url: $(this).attr('action'),
                method: 'GET',
                data: formData,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    console.log('Form submitted successfully:', response);
                    if (response instanceof Blob) {
                        const pdfBlob = new Blob([response], { type: 'application/pdf' });
                        const pdfUrl = URL.createObjectURL(pdfBlob);

                        window.open(pdfUrl, '_blank');
                        URL.revokeObjectURL(pdfUrl);

                    }
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('Error occurred:', error);
                }
            });
        });
    </script>
@endsection
