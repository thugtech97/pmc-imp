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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link href="{{ asset('lib/filter-multiselect/filter-multiselect.css') }}" rel="stylesheet">
@endsection

@section('content')

    <div class="container-fluid">
        <div id="loadingSpinner"></div>
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5" style="background-color:white;">
                        <li class="breadcrumb-item" aria-current="page">CMS</li>
                        <li class="breadcrumb-item active" aria-current="page">Received MRS</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Received MRS (For QTY Delivered Entry)</h4>
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
                                        <input name="search" type="search" id="search" class="form-control" style="font-size:12px;width: 170px;"  placeholder="Search MRS Number" value="{{ $filter->search }}">
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
                                        <select name="customer_filter" id="customer_filter" class="form-control" style="font-size:12px; width: 180px;">
                                                <option value="">Department</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->name }}"
                                                    @if(isset($_GET['customer_filter']) and $_GET['customer_filter']==$department->name) selected="selected" @endif 
                                                        >{{ $department->name }}</option>
                                                @endforeach
                                        </select>
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
                                        <a class="btn btn-sm btn-secondary px-4" href="{{route('warehouse_mrs.index')}}">Reset</a>
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
                            <th>MRS Request #</th>
                            <th>PA #</th>
                            <th>Posted Date</th>
                            <th>Department</th>
                            <th>Purchasing Received Date</th>
                            <th>Aging</th>
                            <th>Total Balance</th>
                            <th>MCD Manager Approved At</th>
                            <th>Purchaser</th>
                            <th class="exclude_export">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                @php
                                    $bal = $sale->items->where('promo_id', '!=', 1)->sum('qty_to_order') - $sale->items->where('promo_id', '!=', 1)->sum('qty_ordered');
                                @endphp
                                <tr class="pd-20">
                                    <td><strong> {{$sale->order_number }}</strong></td>
                                    <td><strong> {{$sale->purchaseAdvice->pa_number ?? "N/A" }}</strong></td>
                                    <td>{{ Carbon\Carbon::parse($sale->created_at)->format('m/d/Y') }}</td>
                                    <!--<td class="text-uppercase">{{ $sale->delivery_type }}</td>
                                    <td>{{ $sale->delivery_date }}</td>-->
                                    <td>{{ $sale->user->department->name }}</td>
                                    <td>{{ $sale->received_at ? Carbon\Carbon::parse($sale->received_at)->format('m/d/Y') : 'N/A' }}</td>
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
                                                <span style="{{ $days >= 14 ? 'color: red;' : 'color: blue;' }}">
                                                    {{ $days > 0 ? $days . ' day' . ($days > 1 ? 's' : '') : '' }}
                                                    {{ $days == 0 ? $hours . ' hour' . ($hours > 1 ? 's' : '') : '' }}
                                                </span>
                                            @endif
                                        @else
                                            {{ 'N/A' }}
                                        @endif
                                    </td>              
                                    <td>{{ $sale->received_at ? $bal : 'N/A' }}</td>
                                    <!--<td><a href="{{route('admin.report.delivery_report',$sale->id)}}" target="_blank">{{$sale->delivery_status}}</a></td>-->
                                    <td>{{ \Carbon\Carbon::parse($sale->approved_at)->format('F j, Y h:i A') }}</td>
                                    <td>{{ $sale->purchaser->name ?? '' }}</td>
                                    <td>
                                        <nav class="nav table-options">
                                            <a class="nav-link" href="{{ route('warehouse.view_mrs',$sale->id) }}" title="View MRS"><i data-feather="eye"></i></a>
                                            @if($sale->purchaseAdvice->is_hold == 0 || $sale->purchaseAdvice->is_hold == NULL)
                                                <a class="nav-link print" href="#" title="Print Purchase Advice" data-order-number="{{$sale->order_number}}">
                                                    <i data-feather="printer"></i>
                                                </a>
                                            @endif
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

    <script src="{{ asset('js/listing.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    <script>
        let listingUrl = "{{ route('warehouse_mrs.index') }}";
        let searchType = "{{ $searchType }}";

        $(document).ready(function() {
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
            $('#del_status').filterMultiSelect();
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
        });
    </script>
@endsection
