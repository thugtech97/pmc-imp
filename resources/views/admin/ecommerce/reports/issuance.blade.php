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

                        <h4 class="mg-b-20 tx-spacing--1">Issuance Report</h4>

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
                                    <label for="category">Item Category (Optional)</label>
                                    
                                    <!--<select id="category" name="filter_category" class="form-control">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request()->input('filter_category') == $category->id ? 'selected' : '' }}>{{ $category->description }}</option>
                                        @endforeach
                                    </select>-->

                                    <select multiple id="filterCategory" name="filter_category[]">
                                        @foreach($categories as $category)
                                            @if ( request()->input('filter_category') )
                                                <option value="{{ $category->id }}" {{ in_array( $category->id, request()->input('filter_category') ) ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @else
                                                <option value="{{ $category->id }}">{{ $category->description }}</option>
                                            @endif
                                        @endforeach
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
                                    <label for="search">Item (Optional)</label>

                                    <input type="text" name="filter" class="form-control" id="search" placeholder="Search for item name" value="{{ request()->input('filter') ?? '' }}">
                                </div>

                                <div class="form-group mx-sm-3 mb-2" style="margin-top:30px">
                                    <button type="submit" class="btn btn-primary mb-2">Filter</button>
                                </div>
                            </div>
                        </form>

                        <table id="example" class="display " style="width:100%;font: normal 13px/150% Arial, sans-serif, Helvetica;">
                            <thead>
                            <tr>
                                <th>Category</th>
                                <th>Item Name</th>
                                <th>Department</th>
                                <th>Issued Quantity</th>
                                <th>Release Date</th>
                                <th>Received By</th>
                                <th>Issued By</th>
                                <th>Encoded By</th>
                                <th>Date Encoded</th>
                            </tr>
                            </thead>
                            <tbody>
                                
                            @forelse($rs as $r)
                                <tr>
                                    <td>{{ $r->orderDetails->category->description }}</td>
                                    <td>{{ $r->orderDetails->product_name }}</td>
                                    <td>{{ optional($r->orderDetails->user->department)->name }}</td>
                                    <td style="text-align:center">{{ $r->items->sum("quantity") }}</td>
                                    <td style="text-align:center">{{ $r->release_date }}</td>
                                    <td style="text-align:center">{{ $r->received_by }}</td>
                                    <td style="text-align:center">{{ $r->issued_by }}</td>
                                    <td style="text-align:center">{{ $r->user->name }}</td>
                                    <td style="text-align:center">{{ $r->created_at }}</td>
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



