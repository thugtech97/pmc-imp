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
                        <li class="breadcrumb-item active" aria-current="page">Create PA for SR items</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Create PA for SR items</h4>
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
                        <label class="d-block">Add items manually <i class="tx-danger">*</i></label>
                        <select id="products" name="products[]" multiple="multiple" class="form-control">
                        </select>
                    </div>

                    @if ($role->name === 'MCD Planner')
                        <div class="form-group">
                            <label class="d-block">Or <i class="tx-danger">*</i></label>
                            <input type="file" id="bulkUploadInput" accept=".xlsx" style="display:none;">
                            <a class="btn btn-sm btn-info btn-upload  mt-2" type="button" href=""><i
                                    class="fa fa-upload"></i> Upload Understock Report</a>
                        </div>
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

            <div class="row row-sm">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="d-block">Supporting Documents *</label>
                        <input type="file" name="supporting_documents[]"  id="supporting_documents" class="form-control" multiple required>
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
                placeholder: "Type item description or stock code...",
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
                                    inv_code: product.inv_code,
                                    last_po_ref: product.last_po_ref
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
                            <input type="text" name="previous_po_${selectedData.id}" value="${selectedData.last_po_ref}" class="form-control" required>
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

                // Create FormData from form
                var form = document.getElementById('paForm');
                var formData = new FormData(form);

                //formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: "{{ route('planner_pa.insert') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: () => {
                        $("#btnSave").prop("disabled", true);
                    },
                    success: (response) => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    },
                    error: (xhr) => {
                        console.error(xhr);
                        $("#btnSave").prop("disabled", false);
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

                const $btn = $('.btn-upload');
                const $tbody = $('#mrsItemsTable tbody');

                $.ajax({
                    url: "{{ route('bulk_upload') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    beforeSend: () => {
                        $btn.prop('disabled', true)
                            .html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
                    },

                    success: (res) => {
                        let html = '';

                        res.data?.forEach((item, i) => {
                            html += `
                                <tr>
                                    <td>
                                        <input type="hidden" name="selected_items[]" value="${item.id}">
                                        ${i + 1}
                                    </td>
                                    <td>${item.stock_type}</td>
                                    <td>${item.inv_code}</td>
                                    <td>${item.description}</td>
                                    <td>${item.stock_code}</td>
                                    <td>${item.oem_id}</td>
                                    <td>${item.uom}</td>
                                    <td><input class="form-control" name="par_to_${item.id}" value="${item.par_to}" required></td>
                                    <td><input type="number" class="form-control" name="qty_to_order_${item.id}" value="${item.qty_to_order}" required></td>
                                    <td><input class="form-control" name="previous_po_${item.id}" value="${item.previous_po}" required></td>
                                </tr>
                            `;
                        });

                        $tbody.append(html);
                    },

                    error: (xhr) => {
                        console.error(xhr);
                        $('#errorMessage').text(
                            xhr.responseJSON?.message || 'An error occurred.'
                        );
                        $('#toastDynamicError').toast({ delay: 3000 }).toast('show');
                    },

                    complete: () => {
                        $btn.prop('disabled', false)
                            .html('<i class="fa fa-upload"></i> Upload Understock Report');
                    }
                });
            });
        });
    </script>
@endsection
