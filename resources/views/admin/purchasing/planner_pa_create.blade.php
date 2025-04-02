@extends('admin.layouts.app')

@section('pagetitle')
    Create Purchase Advice
@endsection

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-product.css') }}" rel="stylesheet">
    <script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
    <link href="{{ asset('lib/select2/css/select2.min.css') }}" rel="stylesheet">
    <style>
        #errorMessage {
            list-style-type: none;
            padding: 0;
            margin-bottom: 0px;
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
                        <label class="d-block">Items <i class="tx-danger">*</i></label>
                        <select id="products" name="products[]" multiple="multiple" class="form-control" required >
                            <option value="1">Hello</option>
                            <option value="1">Hi</option>
                        </select>
                    </div>
                    {{-- 
                    <div class="form-group">
                        <label class="d-block">MRS Number *</label>
                        <select required name="mrs_number" id="mrs_number" data-style="btn btn-outline-light btn-md btn-block tx-left" class="form-control selectpicker" multiple>
                            <option value="">Select MRS Number</option>
                            @foreach($mrs_numbers as $mrs)
                                <option value="{{ $mrs->id }}">{{ $mrs->order_number }}</option>
                            @endforeach
                        </select>
                    </div>
                     --}}
                     
                    @if ($role->name === "MCD Planner")
                        <a class="btn btn-sm btn-info mt-2" type="button" href=""><i class="fa fa-upload"></i> Bulk Items Upload</a>
                    @endif
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-lg-12">
                    <h5>Items:</h5>
                    <table class="table table-bordered" id="mrsItemsTable">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 5%;">Stock Type</th>
                                <th style="width: 5%;">Inv Code</th>
                                <th style="width: 30%;">Item Description</th>
                                <th style="width: 10%;">Stock Code</th>
                                <th style="width: 10%;">OEM ID</th>
                                <th style="width: 5%;">UoM</th>
                                <th style="width: 15%;">PAR To</th>
                                <th style="width: 5%;">QTY To Order</th>
                                <th style="width: 10%;">Previous PO</th>
                            </tr>
                        </thead>
                        <tbody>
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
    <script src="{{ asset('lib/select2/js/select2.min.js') }}"></script>
@endsection

@section('customjs')
    <script>
        $(document).ready(function() {
            $('#products').select2({
                placeholder: "Search for items",
                multiple: true,
                closeOnSelect: false,
                ajax: {
                    url: "{{ route('api.products') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            title: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.items.map(function (product) {
                                return {
                                    id: product.id,
                                    text: product.name,
                                    code: product.code,
                                    oem: product.oem,
                                    uom: product.uom,
                                    stock_type: product.stock_type,
                                    inv_code: product.inv_code
                                };
                            }),
                            pagination: {
                                more: (params.page * 10) < data.total_count
                            }
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function (e) {
                var selectedData = e.params.data;
                $('#mrsItemsTable tbody').append(`
                    <tr>
                        <td>
                            <input type="hidden" name="selected_items[]" value="${selectedData.id}">
                            ${selectedData.id}
                        </td>
                        <td>${selectedData.stock_type}</td>
                        <td>${selectedData.inv_code}</td>
                        <td>${selectedData.text}</td>
                        <td>${selectedData.code}</td>
                        <td>${selectedData.oem}</td>
                        <td>${selectedData.uom}</td>
                        <td>
                            <input type="text" name="par_to_${selectedData.id}" class="form-control" required>
                        </td>
                        <td>
                            <input type="number" name="qty_to_order_${selectedData.id}" class="form-control" required>
                        </td>
                        <td>
                            <input type="text" name="previous_po_${selectedData.id}" class="form-control" required>
                        </td>
                    </tr>
                `);
            });


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
                if ($('input[name="selected_items[]"]').length === 0) {
                    alert("Please select at least one item to be included in Purchase Advice.");
                    return false;
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
