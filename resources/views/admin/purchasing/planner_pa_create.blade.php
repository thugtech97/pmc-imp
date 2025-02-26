@extends('admin.layouts.app')

@section('pagetitle')
    Create Purchase Advice
@endsection

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-product.css') }}" rel="stylesheet">
    <script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
    <style>
        #errorMessage {
            list-style-type: none;
            padding: 0;
            margin-bottom: 0px;
        }
        .image_path {
            opacity: 0;
            width: 0;
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">IMP</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><a>Purchase Advice</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Purchase Advice</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Create Purchase Advice</h4>
            </div>
        </div>
        <form id="paForm">
            <div class="row row-sm">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="d-block">PA Number *</label>
                        <input required name="pa_number" id="code" value="{{ $pa_number }}" type="text" class="form-control" maxlength="150" readonly>
                    </div>
                    <div class="form-group">
                        <label class="d-block">MRS Number *</label>
                        <select required name="mrs_number" id="mrs_number" data-style="btn btn-outline-light btn-md btn-block tx-left" class="form-control selectpicker" multiple>
                            <option value="">Select MRS Number</option>
                            @foreach($mrs_numbers as $mrs)
                                <option value="{{ $mrs->id }}">{{ $mrs->order_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-lg-12">
                    <h5>Items for Selected MRS</h5>
                    <table class="table table-bordered" id="mrsItemsTable">
                        <thead>
                            <tr>
                                <th style="width: 5%;"></th> <!-- Checkbox column -->
                                <th style="width: 15%;">MRS Number</th>
                                <th style="width: 30%;">Item Description</th>
                                <th style="width: 15%;">Requested Quantity</th>
                                <th style="width: 10%;">Stock Code</th>
                                <th style="width: 10%;">Cost Code</th>
                                <th style="width: 15%;">PAR To</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="tx-center p-2">No items.</td>
                            </tr>
                            <!-- Rows will be dynamically populated based on selected MRS -->
                        </tbody>
                    </table>                    
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="d-block">Planner Remarks *</label>
                        <textarea required name="planner_remarks" id="planner_remarks" class="form-control"></textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 mg-t-30">
                <input class="btn btn-primary btn-sm btn-uppercase" type="submit" value="Save">
                <a href="{{ route('planner_pa.index') }}" class="btn btn-outline-secondary btn-sm btn-uppercase">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ asset('lib/jqueryui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/image-upload-validation.js') }}"></script>
@endsection

@section('customjs')
    <script>
        $(document).ready(function() {
            $('#mrs_number').on('change', function() {
                var mrsId = $(this).val();
                if (mrsId) {
                    $.ajax({
                        url: "{{ route('mrs.items') }}",
                        data: { 
                            ids: mrsId,
                            "_token": "{{ csrf_token() }}"
                        },
                        type: 'POST',
                        success: function(data) {
                            $('#mrsItemsTable tbody').empty();
                            if(data.data.length){
                                data.data.forEach(function(item) {
                                    $('#mrsItemsTable tbody').append(`
                                        <tr>
                                            <td><center><input type="checkbox" name="selected_items[]" value="${item.id}"></center></td>
                                            <td>${item.header.order_number}</td>
                                            <td>${item.product.name}</td>
                                            <td>${parseInt(item.qty)}</td>
                                            <td>${item.product.code}</td>
                                            <td>${item.cost_code}</td>
                                            <td>${item.par_to}</td>
                                        </tr>
                                    `);
                                });
                                return;
                            }
                            $('#mrsItemsTable tbody').html(`<tr>
                                <td colspan="7" class="tx-center p-2">No items.</td>
                            </tr>`);
                        },
                        error: function(xhr) {
                            $('#mrsItemsTable tbody').html(`<tr>
                                <td colspan="7" class="tx-center p-2">No items.</td>
                            </tr>`);
                            console.error('An error occurred:', xhr);
                        }
                    });
                } else {
                    $('#mrsItemsTable tbody').empty();
                }
            });

            $('#paForm').on('submit', function(event) {
                event.preventDefault();
                
                if ($('input[name="selected_items[]"]:checked').length === 0) {
                    alert("Please select at least one item to be included in Purchase Advice.");
                    return;
                }
                var data = $('#paForm').serializeArray();
                data.push({ name: "_token", value: "{{ csrf_token() }}" });

                $.ajax({
                    url: "{{ route('planner_pa.insert') }}",
                    data: $.param(data),
                    type: 'POST',
                    success: function(response) {
                        // Redirect to the URL provided in the response
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            console.log(response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('An error occurred:', xhr);
                    }
                });

            });
        });
    </script>
@endsection
