@extends('admin.layouts.report')

@section('pagetitle')

@endsection

@section('pagecss')
    <!-- vendor css -->
    <link href="{{ asset('lib/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/@fortawesome/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/ionicons/css/ionicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/jqvmap/jqvmap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/filter-multiselect/filter-multiselect.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/daterangepicker/daterangepicker.css') }}" rel="stylesheet">

    <style>
        .row-selected {
            background-color: #92b7da !important;
        }
    </style>
@endsection

@section('content')

    <div style="margin:0px 40px 200px 40px;">

                        <h4 class="mg-b-20 tx-spacing--1">MRS Report</h4>

                        <form>
                            <div class="form-row align-items-center">
                                <div class="form-group col-md-3">
                                    <label for="pets">Department</label>
                                    
                                    <select multiple id="pets" name="filter_department[]">
                                        @foreach($departments as $department)
                                            @if ( request()->input('filter_department') )
                                                <option value="{{ $department->id }}" {{ in_array( $department->id, request()->input('filter_department') ) ? 'selected' : '' }}>{{ $department->name }}</option>
                                            @else
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-2">
                                    <label for="daterange">Start and End Date</label>
                                    
                                    <input type="text" class="form-control" id="daterange" name="filter_daterange" value="{{ request()->input('filter_daterange') ?? date('m/d/Y') . '-' . date('m/d/Y') }}" />
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="category">Request Status (Optional)</label>

                                    <select multiple id="filterCategory" name="filter_category[]">
                                        <option value="APPROVED">APPROVED</option>
                                        <option value="PARTIAL">PARTIAL</option>
                                        <option value="COMPLETED">COMPLETED</option>
                                    </select>
                                </div>

                                <!--<div class="form-group col-md-2">
                                    <label for="item">Item (Optional)</label>
                                    
                                    <select id="item" name="filter_item" class="form-control">
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>-->

                                <div class="form-group col-md-2">
                                    <label for="search">Request # (Optional)</label>

                                    <input type="text" name="filter" class="form-control" id="search" placeholder="Search for order number" value="{{ request()->input('filter') ?? '' }}">
                                </div>

                                <div class="form-group mx-sm-3 mb-2" style="margin-top:30px">
                                    <button type="submit" class="btn btn-primary mb-2">Filter</button>
                                </div>
                            </div>
                        </form>

                        <table id="example" class="display " style="width:100%;font: normal 13px/150% Arial, sans-serif, Helvetica;">
                            <thead>
                            <tr>
                                <th>Request #</th>
                                <th>Posted Date</th>
                                <th>Department</th>
                                <th>Total Balance</th>
                                <th>Request Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                
                            @forelse($rs as $r)
                                <tr>
                                    <td>{{ $r->order_number }}</td>
                                    <td>{{ $r->date_posted }}</td>
                                    <td>{{ optional($r->user->department)->name }}</td>
                                    <td style="text-align:center">{{ $r->issuances_sum_qty ?? 0.00 }}</td>
                                    <td style="text-align:center">{{ $r->status }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">No report result.</td>
                                </tr>
                            @endforelse

                            </tbody>

                        </table>

    </div>

@endsection

@section('pagejs')
    <script src="{{ asset('js/moment.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/prismjs/prism.js') }}"></script>
    <script src="{{ asset('lib/jqueryui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('lib/filter-multiselect/filter-multiselect-bundle.min.js') }}"></script>
    <script src="{{ asset('lib/daterangepicker/daterangepicker.min.js') }}"></script>
@endsection

@section('customjs')
<script src="{{ asset('js/datatables/Buttons-1.6.1/js/buttons.colVis.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('input[name="filter_daterange"]').daterangepicker({
            opens: 'left'
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        });

        $('#pets').filterMultiSelect();
        $('#filterCategory').filterMultiSelect();

        $('#example').DataTable( {

            dom: 'Brtip',
            pageLength: 20,
            buttons: [
                {
                    extend: 'print',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'copy',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'csv',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                 // {
                //     extend: 'pdf',
                //     exportOptions: {
                //         columns: ':visible'
                //     }
                // },
                {   
                    extend: 'pdfHtml5',
                    text: 'PDF',
                    exportOptions: {
                        modifier: {
                            page: 'current'
                        }
                    },
                    orientation : 'landscape',
                    pageSize : 'LEGAL'
                },
                'colvis'
            ],
            columnDefs: [ {
                targets: [],
                visible: false
            } ]
        } );
    } );
</script>
@endsection



