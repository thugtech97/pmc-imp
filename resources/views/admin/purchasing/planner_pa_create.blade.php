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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --pa-bg: #f4f6f9; --pa-white: #ffffff; --pa-border: #e2e8f0;
            --pa-border-dark: #cbd5e1; --pa-text: #1e293b; --pa-text-muted: #64748b;
            --pa-text-light: #94a3b8; --pa-primary: #1d4ed8; --pa-primary-light: #eff6ff;
            --pa-primary-hover: #1e40af; --pa-accent: #0ea5e9; --pa-success: #059669;
            --pa-success-light: #ecfdf5; --pa-warning: #d97706; --pa-warning-light: #fffbeb;
            --pa-danger: #dc2626; --pa-danger-light: #fef2f2;
            --pa-shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --pa-radius: 10px; --pa-radius-sm: 6px;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--pa-bg); }
        .pa-page-header { margin-bottom: 28px; }
        .pa-page-header .breadcrumb { background: none; padding: 0; margin-bottom: 6px; font-size: 12px; }
        .pa-page-header .breadcrumb-item a { color: var(--pa-text-muted); text-decoration: none; }
        .pa-page-header .breadcrumb-item.active { color: var(--pa-primary); font-weight: 500; }
        .pa-page-header h4 { font-size: 22px; font-weight: 600; color: var(--pa-text); letter-spacing: -0.4px; margin: 0; }

        .pa-card { background: var(--pa-white); border: 1px solid var(--pa-border); border-radius: var(--pa-radius); box-shadow: var(--pa-shadow-sm); margin-bottom: 20px; overflow: hidden; }
        .pa-card-header { padding: 16px 22px; border-bottom: 1px solid var(--pa-border); display: flex; align-items: center; gap: 10px; background: #fafbfd; }
        .pa-card-header .card-icon { width: 32px; height: 32px; border-radius: 8px; background: var(--pa-primary-light); display: flex; align-items: center; justify-content: center; color: var(--pa-primary); font-size: 14px; flex-shrink: 0; }
        .pa-card-header h6 { margin: 0; font-size: 13px; font-weight: 600; color: var(--pa-text); }
        .pa-card-header p { margin: 0; font-size: 11.5px; color: var(--pa-text-muted); }
        .pa-card-body { padding: 22px; }

        .pa-label { font-size: 12px; font-weight: 600; color: var(--pa-text); letter-spacing: 0.3px; text-transform: uppercase; margin-bottom: 7px; display: block; }
        .pa-label .required { color: var(--pa-danger); margin-left: 2px; }
        .pa-textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--pa-border-dark); border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13.5px; color: var(--pa-text); background: var(--pa-white); resize: vertical; min-height: 90px; transition: border-color 0.15s, box-shadow 0.15s; outline: none; }
        .pa-textarea:focus { border-color: var(--pa-primary); box-shadow: 0 0 0 3px rgba(29,78,216,0.1); }

        .pa-number-display { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: var(--pa-primary-light); border: 1px solid #bfdbfe; border-radius: var(--pa-radius-sm); }
        .pa-number-display .pa-number-icon { color: var(--pa-primary); font-size: 16px; }
        .pa-number-display .pa-number-value { font-family: 'DM Mono', monospace; font-size: 14px; font-weight: 500; color: var(--pa-primary); letter-spacing: 0.5px; }
        .pa-number-display .pa-number-label { font-size: 11px; color: var(--pa-text-muted); margin-left: auto; background: white; padding: 2px 8px; border-radius: 20px; border: 1px solid #bfdbfe; }

        .btn-upload-styled { display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; background: var(--pa-white); border: 1.5px dashed var(--pa-border-dark); border-radius: var(--pa-radius-sm); color: var(--pa-text-muted); font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-upload-styled:hover { border-color: var(--pa-primary); color: var(--pa-primary); background: var(--pa-primary-light); text-decoration: none; }
        .btn-upload-styled.uploading { border-style: solid; border-color: var(--pa-accent); color: var(--pa-accent); background: #f0f9ff; }

        #uploadAlert { border-radius: var(--pa-radius-sm); font-size: 13px; padding: 11px 16px; border-width: 1px; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
        #uploadAlert.alert-success { background: var(--pa-success-light); border-color: #a7f3d0; color: #065f46; }
        #uploadAlert.alert-danger  { background: var(--pa-danger-light);  border-color: #fecaca; color: #991b1b; }
        #uploadAlert.alert-warning { background: var(--pa-warning-light); border-color: #fde68a; color: #92400e; }

        .pa-table-wrapper { border-radius: var(--pa-radius-sm); border: 1px solid var(--pa-border); overflow: auto; }
        .pa-table { width: 100%; border-collapse: collapse; font-size: 12.5px; margin: 0; }
        .pa-table thead tr { background: #f1f5f9; }
        .pa-table thead th { padding: 10px 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--pa-text-muted); border-bottom: 1px solid var(--pa-border); border-right: 1px solid var(--pa-border); white-space: nowrap; }
        .pa-table thead th:last-child { border-right: none; }
        .pa-table tbody tr { border-bottom: 1px solid var(--pa-border); transition: background 0.1s; }
        .pa-table tbody tr:last-child { border-bottom: none; }
        .pa-table tbody tr:hover { background: #fafbff; }
        .pa-table tbody td { padding: 8px 10px; color: var(--pa-text); border-right: 1px solid var(--pa-border); vertical-align: middle; }
        .pa-table tbody td:last-child { border-right: none; }

        .row-num { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--pa-primary-light); color: var(--pa-primary); border-radius: 50%; font-size: 11px; font-weight: 600; }
        .pa-include-check { width: 16px; height: 16px; cursor: pointer; accent-color: var(--pa-primary); }
        .btn-row-delete { width: 28px; height: 28px; border: 1px solid #fecaca; border-radius: 4px; background: var(--pa-danger-light); color: var(--pa-danger); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; }
        .btn-row-delete:hover { background: var(--pa-danger); color: white; }
        .pa-item-row.is-excluded { background: #f8fafc; color: var(--pa-text-light); }
        .pa-item-row.is-excluded input.form-control { background: #f1f5f9; color: var(--pa-text-light); }

        .pa-table .form-control { height: 32px; padding: 0 8px; font-size: 12.5px; border: 1px solid var(--pa-border-dark); border-radius: 4px; font-family: 'DM Sans', sans-serif; min-width: 80px; }
        .pa-table .form-control:focus { border-color: var(--pa-primary); box-shadow: 0 0 0 2px rgba(29,78,216,0.1); outline: none; }

        .pa-table-empty { padding: 48px 20px; text-align: center; color: var(--pa-text-light); }
        .pa-table-empty i { font-size: 32px; display: block; margin-bottom: 10px; opacity: 0.4; }
        .pa-table-empty p { font-size: 13px; margin: 0; }

        .pa-file-input { width: 100%; padding: 8px 12px; border: 1px solid var(--pa-border-dark); border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13px; color: var(--pa-text); background: var(--pa-white); cursor: pointer; }
        .pa-file-input::-webkit-file-upload-button { padding: 5px 14px; background: var(--pa-primary-light); border: 1px solid #bfdbfe; border-radius: 4px; color: var(--pa-primary); font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 500; cursor: pointer; margin-right: 10px; }

        .pa-action-bar { background: var(--pa-white); border: 1px solid var(--pa-border); border-radius: var(--pa-radius); padding: 16px 22px; display: flex; align-items: center; gap: 10px; box-shadow: var(--pa-shadow-sm); }

        .btn-pa-primary { display: inline-flex; align-items: center; gap: 7px; padding: 9px 22px; background: var(--pa-primary); color: white; border: none; border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s; text-decoration: none; }
        .btn-pa-primary:hover { background: var(--pa-primary-hover); color: white; text-decoration: none; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(29,78,216,0.25); }
        .btn-pa-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn-pa-secondary { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; background: var(--pa-white); color: var(--pa-text-muted); border: 1px solid var(--pa-border-dark); border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s; text-decoration: none; }
        .btn-pa-secondary:hover { background: #f8fafc; color: var(--pa-text); border-color: #94a3b8; text-decoration: none; }

        .select2-container--default .select2-selection--multiple { border: 1px solid var(--pa-border-dark) !important; border-radius: var(--pa-radius-sm) !important; min-height: 38px !important; font-family: 'DM Sans', sans-serif !important; }
        .select2-container--default .select2-selection--multiple .select2-selection__choice { background: var(--pa-primary-light) !important; border: 1px solid #bfdbfe !important; color: var(--pa-primary) !important; border-radius: 4px !important; font-size: 12px !important; }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color: var(--pa-primary) !important; }

        .or-divider { display: flex; align-items: center; gap: 10px; margin: 4px 0 12px; color: var(--pa-text-light); font-size: 11.5px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .or-divider::before, .or-divider::after { content: ''; flex: 1; height: 1px; background: var(--pa-border); }

        @keyframes rowFadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
        .pa-table tbody tr { animation: rowFadeIn 0.2s ease; }
    </style>
@endsection

@section('content')
    <div class="container-fluid" style="max-width: 1600px;">

        <div class="pa-page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">IMP</a></li>
                    <li class="breadcrumb-item active">Purchase Advice</li>
                    <li class="breadcrumb-item active">Create PA for SR Items</li>
                </ol>
            </nav>
            <h4>Create Purchase Advice</h4>
        </div>

        <form id="paForm">

            <div class="row">
                <div class="col-lg-4">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-file-text-o"></i></div>
                            <div><h6>PA Reference</h6><p>Auto-generated PA number</p></div>
                        </div>
                        <div class="pa-card-body">
                            <label class="pa-label">PA Number</label>
                            <div class="pa-number-display">
                                <i class="fa fa-hashtag pa-number-icon"></i>
                                <span class="pa-number-value">{{ $pa_number }}</span>
                                <span class="pa-number-label">Auto-generated</span>
                            </div>
                            <input type="hidden" name="pa_number" value="{{ $pa_number }}">
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-plus-circle"></i></div>
                            <div><h6>Add Items</h6><p>Search manually or upload understock report</p></div>
                        </div>
                        <div class="pa-card-body">
                            <label class="pa-label">Search Item <span class="required">*</span></label>
                            <select id="products" name="products[]" multiple="multiple" style="width:100%"></select>

                            @if ($role->name === 'MCD Planner')
                                <div class="or-divider" style="margin-top: 16px;">or</div>
                                <input type="file" id="bulkUploadInput" accept=".xlsx" style="display:none;">
                                <a class="btn-upload-styled btn-upload" href="#">
                                    <i class="fa fa-upload"></i> Upload Understock Report
                                </a>
                                <span style="font-size: 11.5px; color: var(--pa-text-light); margin-left: 10px;">
                                    Accepts .xlsx files only
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="pa-card">
                <div class="pa-card-header">
                    <div class="card-icon"><i class="fa fa-list"></i></div>
                    <div><h6>Selected Items</h6><p>Review and fill in required fields before saving</p></div>
                    <div style="margin-left: auto;">
                        <div id="uploadAlert" class="alert" style="display:none; margin:0; padding: 7px 14px; font-size: 12.5px;"></div>
                    </div>
                </div>
                <div class="pa-card-body" style="padding: 0;">
                    <div class="pa-table-wrapper" style="border: none; border-radius: 0;">
                        <table class="pa-table" id="mrsItemsTable">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th style="width:70px;">Include</th>
                                    <th style="width:55px;">Delete</th>
                                    <th>Stock Type</th>
                                    <th>Inv Code</th>
                                    <th style="min-width:180px;">Item Description</th>
                                    <th>Stock Code</th>
                                    <th>OEM ID</th>
                                    <th>UoM</th>
                                    <th style="min-width:110px;">PAR To <span style="color:#ef4444;">*</span></th>
                                    <th style="min-width:90px;">QTY Order <span style="color:#ef4444;">*</span></th>
                                    <th style="min-width:110px;">Date Needed</th>
                                    <th style="min-width:100px;">QTY/Delivery</th>
                                    <th style="min-width:100px;">No. Deliveries</th>
                                    <th style="min-width:100px;">Class Note</th>
                                    <th style="min-width:110px;">Previous PO</th>
                                    <th style="min-width:90px;">Priority No</th>
                                    <th style="min-width:100px;">Cost Code</th>
                                    <th style="min-width:150px;">Remarks</th>
                                    <th style="min-width:70px;">DLT</th>
                                    <th style="min-width:80px;">Open PO</th>
                                    <th style="min-width:90px;">ROF Months</th>
                                    <th style="min-width:110px;">ROF Months W/ Req</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <tr id="emptyStateRow">
                                    <td colspan="23">
                                        <div class="pa-table-empty">
                                            <i class="fa fa-inbox"></i>
                                            <p>No items added yet. Search above or upload an understock report.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Remarks + Documents --}}
            <div class="row">
                <div class="col-lg-6">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-comment-o"></i></div>
                            <div><h6>Planner Remarks</h6><p>Notes or instructions for this PA</p></div>
                        </div>
                        <div class="pa-card-body">
                            <textarea name="planner_remarks" id="planner_remarks" class="pa-textarea"
                                placeholder="Enter remarks, special instructions, or notes..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-paperclip"></i></div>
                            <div><h6>Supporting Documents</h6><p>Attach relevant files for this request</p></div>
                        </div>
                        <div class="pa-card-body">
                            <label class="pa-label">Attach Files</label>
                            <input type="file" name="supporting_documents[]" id="supporting_documents"
                                class="pa-file-input" multiple>
                            <p style="font-size: 11.5px; color: var(--pa-text-light); margin-top: 7px; margin-bottom: 0;">
                                <i class="fa fa-info-circle"></i>
                                You may attach multiple files. Accepted formats: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pa-action-bar">
                <button type="submit" class="btn-pa-primary" id="btnSave">
                    <i class="fa fa-save"></i> Save Purchase Advice
                </button>
                <a href="{{ route('planner_pa.index') }}" class="btn-pa-secondary">
                    <i class="fa fa-arrow-left"></i> Cancel
                </a>
                <span style="margin-left: auto; font-size: 12px; color: var(--pa-text-light);">
                    <i class="fa fa-lock" style="margin-right: 4px;"></i>
                    All fields marked <span style="color: var(--pa-danger);">*</span> are required
                </span>
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

            var itemCount = 0;

            function hideEmptyState() {
                $('#emptyStateRow').hide();
            }

            function refreshItemRows() {
                var $rows = $('#itemsTableBody tr.pa-item-row');
                itemCount = $rows.length;

                $rows.each(function(index) {
                    $(this).find('.row-num').text(index + 1);
                });

                if (itemCount === 0) {
                    $('#emptyStateRow').show();
                }
            }

            function buildRow(id, data) {
                return '<tr class="pa-item-row" data-product-id="' + id + '">' +
                    '<td><span class="row-num">' + itemCount + '</span>' +
                        '<input type="hidden" name="rof_months_' + id + '" value="' + (data.rof_months ?? '') + '">' +
                        '<input type="hidden" name="rof_months_w_request_' + id + '" value="' + (data.rof_months_w_request ?? '') + '">' +
                    '</td>' +
                    '<td style="text-align:center;"><input type="checkbox" class="pa-include-check" name="selected_items[]" value="' + id + '" checked></td>' +
                    '<td style="text-align:center;"><button type="button" class="btn-row-delete" title="Delete row"><i class="fa fa-trash"></i></button></td>' +
                    '<td>' + (data.stock_type ?? '') + '</td>' +
                    '<td>' + (data.inv_code ?? '') + '</td>' +
                    '<td style="font-weight:500;">' + (data.description ?? data.text ?? '') + '</td>' +
                    '<td style="font-family:\'DM Mono\',monospace;font-size:12px;">' + (data.stock_code ?? data.code ?? '') + '</td>' +
                    '<td>' + (data.oem_id ?? data.oem ?? '') + '</td>' +
                    '<td>' + (data.uom ?? '') + '</td>' +
                    '<td><input type="text"   class="form-control" name="par_to_' + id + '"               value="' + (data.par_to ?? '')               + '" required></td>' +
                    '<td><input type="number" class="form-control" name="qty_to_order_' + id + '"         value="' + (data.qty_to_order ?? 0)           + '" required></td>' +
                    '<td><input type="text"   class="form-control" name="date_needed_' + id + '"          value="' + (data.date_needed ?? '')            + '"></td>' +
                    '<td><input type="number" class="form-control" name="qty_per_delivery_' + id + '"     value="' + (data.qty_per_delivery ?? '')       + '"></td>' +
                    '<td><input type="number" class="form-control" name="number_of_deliveries_' + id + '" value="' + (data.number_of_deliveries ?? '')   + '"></td>' +
                    '<td><input type="text"   class="form-control" name="class_note_' + id + '"           value="' + (data.class_note ?? '')             + '"></td>' +
                    '<td><input type="text"   class="form-control" name="previous_po_' + id + '"          value="' + (data.previous_po ?? data.last_po_ref ?? '') + '"></td>' +
                    '<td><input type="text"   class="form-control" name="priority_no_' + id + '"          value="' + (data.priority_no ?? '')            + '"></td>' +
                    '<td><input type="text"   class="form-control" name="cost_code_' + id + '"            value="' + (data.cost_code ?? '')              + '"></td>' +
                    '<td><input type="text"   class="form-control" name="remarks_' + id + '"              value="' + (data.remarks ?? '')                + '"></td>' +
                    '<td><input type="number" class="form-control" name="dlt_' + id + '"                  value="' + (data.dlt ?? '')                    + '" step="0.01"></td>' +
                    '<td><input type="text"   class="form-control" name="open_po_' + id + '"              value="' + (data.open_po ?? '')                + '"></td>' +
                    '<td><input type="number" class="form-control" name="rof_months_display_' + id + '"   value="' + (data.rof_months ?? '')             + '" step="0.01" readonly style="background:#f8fafc;"></td>' +
                    '<td><input type="number" class="form-control" name="rof_w_req_display_' + id + '"    value="' + (data.rof_months_w_request ?? '')   + '" step="0.01" readonly style="background:#f8fafc;"></td>' +
                '</tr>';
            }

            // -------------------------------------------------------
            // Select2 - Manual item search and add
            // -------------------------------------------------------
            $('#products').select2({
                placeholder: "Type item description or stock code...",
                multiple: true,
                closeOnSelect: false,
                ajax: {
                    url: "{{ route('api.products') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { title: params.term, page: params.page || 1 };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items.map(function(product) {
                                var itemText = [product.code, product.name].filter(Boolean).join(' - ');

                                return {
                                    id:          product.id,
                                    text:        itemText,
                                    description: product.name,
                                    code:        product.code,
                                    stock_code:  product.code,
                                    oem:         product.oem,
                                    uom:         product.uom,
                                    stock_type:  product.stock_type,
                                    inv_code:    product.inv_code,
                                    last_po_ref: product.last_po_ref
                                };
                            }),
                            pagination: { more: (params.page * 10) < data.total_count }
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function(e) {
                var d = e.params.data;
                itemCount++;
                hideEmptyState();
                $('#itemsTableBody').append(buildRow(d.id, d));
                refreshItemRows();
            });

            $('#itemsTableBody').on('change', '.pa-include-check', function() {
                var $row = $(this).closest('tr');
                var included = $(this).is(':checked');

                $row.toggleClass('is-excluded', !included);
                $row.find('input.form-control').prop('disabled', !included);
                $row.find('input[type="hidden"]').prop('disabled', !included);
            });

            $('#itemsTableBody').on('click', '.btn-row-delete', function() {
                var $row = $(this).closest('tr');
                var productId = String($row.data('product-id'));
                var selectedValues = $('#products').val() || [];

                $('#products').val(selectedValues.filter(function(value) {
                    return String(value) !== productId;
                })).trigger('change');

                $row.remove();
                refreshItemRows();
            });

            // -------------------------------------------------------
            // PA Form Submit
            // -------------------------------------------------------
            $('#paForm').on('submit', function(event) {
                event.preventDefault();

                if ($('input[name="selected_items[]"]:checked').length === 0) {
                    alert("Please select at least one item to be included in Purchase Advice.");
                    return false;
                }

                var formData = new FormData(document.getElementById('paForm'));

                $.ajax({
                    url: "{{ route('planner_pa.insert') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    beforeSend: function() {
                        $("#btnSave").prop("disabled", true)
                            .html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                    },
                    success: function(response) {
                        if (response.redirect) window.location.href = response.redirect;
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $("#btnSave").prop("disabled", false)
                            .html('<i class="fa fa-save"></i> Save Purchase Advice');
                    }
                });
            });

            // -------------------------------------------------------
            // Bulk Upload
            // -------------------------------------------------------
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

                var $btn   = $('.btn-upload');
                var $tbody = $('#itemsTableBody');
                var $alert = $('#uploadAlert');

                $alert.hide().removeClass('alert-success alert-danger alert-warning').html('');

                $.ajax({
                    url: "{{ route('bulk_upload') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    beforeSend: function() {
                        $btn.addClass('uploading')
                            .html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
                    },

                    success: function(res) {
                        if (!res.data || res.data.length === 0) {
                            $alert.addClass('alert-warning')
                                .html('<i class="fa fa-exclamation-triangle"></i> No matching products found in the uploaded file.')
                                .show();
                            return;
                        }

                        hideEmptyState();
                        var html = '';
                        $.each(res.data, function(i, item) {
                            itemCount++;
                            html += buildRow(item.id, item);
                        });

                        $tbody.append(html);
                        refreshItemRows();
                        $alert.addClass('alert-success')
                            .html('<i class="fa fa-check-circle"></i> <strong>' + res.data.length + ' item(s)</strong> loaded from the uploaded file.')
                            .show();
                    },

                    error: function(xhr) {
                        console.error(xhr);
                        var message = (xhr.responseJSON && xhr.responseJSON.error)
                                   ? xhr.responseJSON.error
                                   : (xhr.responseJSON && xhr.responseJSON.message)
                                   ? xhr.responseJSON.message
                                   : 'An unexpected error occurred. Please try again.';
                        $alert.addClass('alert-danger')
                            .html('<i class="fa fa-times-circle"></i> ' + message)
                            .show();
                    },

                    complete: function() {
                        $btn.removeClass('uploading')
                            .html('<i class="fa fa-upload"></i> Upload Understock Report');
                        $('#bulkUploadInput').val('');
                    }
                });
            });

        });
    </script>
@endsection
