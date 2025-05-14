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
                        <li class="breadcrumb-item" aria-current="page"><a href="{{ route('dashboard') }}">IMP</a></li>
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
                        <input required name="pa_number" id="code" value="{{ $pa_number }}" type="text"
                            class="form-control" maxlength="150" readonly>
                    </div>

                    <div class="form-group">
                        <label class="d-block">Items <i class="tx-danger">*</i></label>
                        <select id="products" name="products[]" multiple="multiple" class="form-control">
                        </select>
                    </div>

                    @if ($role->name === 'MCD Planner')
                        <input type="file" id="bulkUploadInput" accept=".xlsx" style="display:none;">
                        <a class="btn btn-sm btn-info btn-upload  mt-2" type="button" href=""><i
                                class="fa fa-upload"></i> Bulk Items Upload</a>
                    @endif
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-lg-12">
                    <h5>Items:</h5>
                    <table class="table table-bordered" id="mrsItemsTable">
                        <thead>
                            <tr>
                                <th style="width: 5%;">Item#</th>
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
                <input class="btn btn-primary btn-sm btn-uppercase" type="submit" id="btnSave" value="Save">
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
                    data: function(params) {
                        return {
                            title: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.items.map(function(product) {
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
            }).on('select2:select', function(e) {
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

            $('#paForm').on('submit', function(event) {
                event.preventDefault();
                if ($('input[name="selected_items[]"]').length === 0) {
                    alert("Please select at least one item to be included in Purchase Advice.");
                    return false;
                }
                var data = $('#paForm').serializeArray();
                data.push({
                    name: "_token",
                    value: "{{ csrf_token() }}"
                });
                $("#btnSave").prop("disabled", true);
                $.ajax({
                    url: "{{ route('planner_pa.insert') }}",
                    data: $.param(data),
                    type: 'POST',
                    success: function(response) {
                        // Redirect to the URL provided in the response
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            $("#btnSave").prop("disabled", false);
                            console.log(response.message);
                        }
                    },
                    error: function(xhr) {
                        $("#btnSave").prop("disabled", false);
                        console.error('An error occurred:', xhr);
                    }
                });
            });

            $('.btn-upload').on('click', function(e) {
                e.preventDefault();
                $('#bulkUploadInput').click();
            });

            $('#bulkUploadInput').on('change', function(event) {
                var file = event.target.files[0];
                if (!file) return;

                var formData = new FormData();
                formData.append('file', file);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // Show loading indicator
                $('.btn-upload').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');

                $.ajax({
                    url: "{{ route('bulk_upload') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        //$('#mrsItemsTable tbody').empty();
                        response.data.forEach(function(item, index) {
                            $('#mrsItemsTable tbody').append(`
                        <tr>
                            <td><input type="hidden" name="selected_items[]" value="${item.id}">${index + 1}</td>
                            <td>${item.stock_type}</td>
                            <td>${item.inv_code}</td>
                            <td>${item.description}</td>
                            <td>${item.stock_code}</td>
                            <td>${item.oem_id}</td>
                            <td>${item.uom}</td>
                            <td><input type="text" name="par_to_${item.id}" class="form-control" required value="${item.par_to}"></td>
                            <td><input type="number" name="qty_to_order_${item.id}" class="form-control" required value="${item.qty_to_order}"></td>
                            <td><input type="text" name="previous_po_${item.id}" class="form-control" required value="${item.previous_po}"></td>
                        </tr>
                    `);
                        });
                        $('.btn-upload').prop('disabled', false).html('<i class="fa fa-upload"></i> Bulk Items Upload');
                    },
                    error: function(xhr) {
                        console.error('An error occurred:', xhr);
                    }
                });
            });
        });
    </script>
@endsection
