@extends('theme.main')

@section('pagecss')
<link rel="stylesheet" href="{{ asset('lib/sweetalert2/sweetalert.min.css') }}" type="text/css" />
<link rel="stylesheet" href="{{ asset('lib/js-snackbar/js-snackbar.css') }}" type="text/css" />
<style>
    #content {
        padding-top: 1rem !important;
    }

    span.req { color: #aa0707; }
    .error-color { color: #aa0707; }

    /* ---- IMF form redesign ---- */
    .imf-create-page {
        --imf-ink: #222e2b;
        --imf-deep: #203f3a;
        --imf-green: #42615b;
        --imf-gold: #ecd49a;
        --imf-line: #e5ebe7;
        --imf-muted: #71807a;
        color: var(--imf-ink);
        font-family: "DM Sans", sans-serif;
        padding-bottom: 36px;
    }
    .imf-create-page .form-container {
        background: #fff;
        border: 1px solid var(--imf-line) !important;
        border-radius: 14px;
        box-shadow: 0 10px 28px rgba(34, 46, 43, .06) !important;
        padding: 26px 30px !important;
    }
    .imf-form-head { align-items:center; display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:22px; }
    .imf-form-head h3 { color:var(--imf-ink); font-size:21px; margin:0; font-weight:700; }
    .imf-form-head p { color:var(--imf-muted); font-size:12px; line-height:1.35; margin:4px 0 0; }
    .imf-form-head .button {
        align-items:center;
        background:transparent !important;
        border:1px solid var(--imf-green) !important;
        border-radius:8px;
        color:var(--imf-green) !important;
        display:inline-flex;
        font-size:12px;
        height:40px;
        padding:0 15px;
        transition:background-color .2s ease, color .2s ease, transform .2s ease;
    }
    .imf-form-head .button:hover { background:var(--imf-green) !important; color:#fff !important; transform:translateY(-1px); }
    .imf-card { background:#fff; border:1px solid var(--imf-line); border-radius:12px; box-shadow:0 4px 14px rgba(34, 46, 43, .045); margin-bottom:18px; padding:20px 22px; }
    .imf-section-head { align-items:center; border-bottom:1px solid #eef2ef; display:flex; gap:10px; margin-bottom:18px; padding-bottom:12px; }
    .imf-section-head i { color:var(--imf-green); font-size:19px; }
    .imf-section-head h5 { color:var(--imf-ink); font-size:14px; font-weight:700; letter-spacing:.3px; margin:0; }
    .imf-section-head .imf-section-sub { color:var(--imf-muted); font-size:11px; font-weight:500; margin-left:auto; }

    .imf-create-page label { color:#52645e; font-size:11px; letter-spacing:.25px; }
    .imf-create-page .form-control,
    .imf-create-page .form-select { border:1px solid #d7e2dc; border-radius:8px; color:var(--imf-ink); min-height:38px; transition:border-color .15s ease, box-shadow .15s ease; }
    .imf-create-page .form-control:focus,
    .imf-create-page .form-select:focus { border-color:var(--imf-green); box-shadow:0 0 0 3px rgba(66, 97, 91, .1); }

    /* Request-type radio cards */
    .imf-type-toggle { display:flex; gap:12px; flex-wrap:wrap; }
    .imf-type-toggle label { align-items:flex-start; border:1px solid #d7e2dc; border-radius:9px; cursor:pointer; display:flex; flex:1; gap:10px; min-width:180px; padding:14px 16px; transition:all .15s; }
    .imf-type-toggle input { margin-top:3px; }
    .imf-type-toggle label:hover { border-color:rgba(66, 97, 91, .45); }
    .imf-type-toggle label.active { background:#f1f6f3; border-color:var(--imf-green); }
    .imf-type-toggle .imf-type-title { color:var(--imf-ink); font-weight:700; }
    .imf-type-toggle .imf-type-desc { color:var(--imf-muted); font-size:12px; }

    /* Repeater grid */
    .imf-grid-wrap { border:1px solid var(--imf-line); border-radius:10px; overflow-x:auto; }
    #newItems { margin:0; min-width:1100px; }
    #newItems thead th { background:#f3f6f4; border-bottom:1px solid var(--imf-line); color:var(--imf-green); font-size:10px; font-weight:700; letter-spacing:.7px; padding:12px 8px; text-transform:uppercase; white-space:nowrap; }
    #newItems td { border-top:1px solid #edf1ee; padding:6px; vertical-align:middle; }
    #newItems .form-control, #newItems .form-select { font-size:13px; padding:6px 8px; min-width:100px; }
    #newItems textarea.form-control { min-width:200px; }
    #newItems td.item-no-cell { color:var(--imf-muted); font-weight:700; min-width:44px; text-align:center; width:44px; }
    #newItems td.item-no-cell::before { content: counter(itemno); }
    #newItems tbody { counter-reset: itemno; }
    #newItems tbody tr { counter-increment: itemno; }
    #newItems .grid-del { color:#c74b4b; cursor:pointer; font-size:16px; }
    .imf-grid-toolbar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-top:14px; }

    /* Sticky save bar */
    .imf-grid-toolbar .btn { border-radius:8px !important; font-size:12px; font-weight:700; height:38px; }
    .imf-save-bar { background:#fff; border-top:1px solid var(--imf-line); bottom:0; display:flex; gap:10px; justify-content:flex-end; margin-top:10px; padding:14px 4px 4px; position:sticky; z-index:5; }
    .imf-save-bar .button { align-items:center; background:var(--imf-green) !important; border:1px solid var(--imf-green) !important; border-radius:8px; color:#fff !important; display:inline-flex; font-size:12px; height:40px; padding:0 16px; }
    .imf-save-bar .button:hover { background:var(--imf-deep) !important; border-color:var(--imf-deep) !important; }
    @media (max-width:767.98px) {
        .imf-create-page .form-container { padding:18px 12px !important; }
        .imf-card { padding:16px 14px; }
        .imf-form-head { align-items:flex-start; flex-direction:column; }
        .imf-form-head .button { width:100%; }
        .imf-section-head .imf-section-sub { font-size:10px; }
    }
</style>
@include('theme.pages.customer.new-stock._update-items-css')
@endsection
@section('content')
<div class="container-fluid imf-create-page">
    <div class="row">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (Session::has('success'))
        <div class="alert alert-info" role="alert">{{ Session::get('success') }}</div>
        @endif
        @if (Session::has('error'))
        <div class="alert alert-danger" role="alert">{{ Session::get('error') }}</div>
        @endif

        <div class="col-lg-12">
            <div class="border py-4 px-3 border-transparent shadow-lg p-lg-5 form-container">

                <div class="imf-form-head">
                    <div>
                        <h3>New Inventory Maintenance Form</h3>
                        <p>Fill in the header, add your items in the grid, then Save.</p>
                    </div>
                    <a href="{{ route('new-stock.index') }}" class="button button-dark button-border button-circle button-large fw-bold fs-14-f nols notextshadow m-0">
                        <i class="icon-line-chevron-left me-1"></i> Back
                    </a>
                </div>

                <form id="imf">
                    @csrf
                    <input type="hidden" name="department" value="{{ auth()->user()->department->name }}">

                    {{-- ===== Request Type ===== --}}
                    <div class="imf-card">
                        <div class="imf-section-head">
                            <i class="icon-line-layers"></i>
                            <h5>Request Type</h5>
                        </div>
                        <div class="imf-type-toggle">
                            <label for="inlineRadio1" class="active">
                                <input class="form-check-input" type="radio" name="type" id="inlineRadio1" value="new" checked="checked">
                                <span>
                                    <span class="imf-type-title d-block">New Stock Item</span>
                                    <span class="imf-type-desc">Register one or more brand-new items.</span>
                                </span>
                            </label>
                            <label for="inlineRadio2">
                                <input class="form-check-input" type="radio" name="type" id="inlineRadio2" value="update">
                                <span>
                                    <span class="imf-type-title d-block">Update</span>
                                    <span class="imf-type-desc">Change details of one or more existing stock items.</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- ===== Form Details ===== --}}
                    <div class="imf-card">
                        <div class="imf-section-head">
                            <i class="icon-line-file-text"></i>
                            <h5>Form Details</h5>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label class="fw-semibold text-initial nols">Department</label>
                                    <input type="text" class="form-control form-input" value="{{ auth()->user()->department->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label for="section" class="fw-semibold text-initial nols">Section</label>
                                    <input type="text" id="section" class="form-control form-input" name="section" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label for="division" class="fw-semibold text-initial nols">Division</label>
                                    <input type="text" id="division" class="form-control form-input" name="division" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label for="priority" class="fw-semibold text-initial nols">Priority <span class="req">&#42;</span></label>
                                    <select id="priority" class="form-select" name="priority">
                                        <option value="1">Priority 1 &mdash; Very Urgent</option>
                                        <option value="2">Priority 2 &mdash; High Priority</option>
                                        <option value="3">Priority 3 &mdash; Needed but not priority</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        {{-- Update purpose sub-types (shown only for "Update") --}}
                        <div class="form-group mb-0" id="update-type-container" style="display:none;">
                            <label class="fw-semibold text-initial nols d-block">Purpose of Update <small class="text-muted">(check all that apply)</small></label>
                            @foreach (['Stock type update', 'Min-max update', 'Merge code', 'Obsolete', 'Bin location update', 'Others'] as $ut)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="update_type[]" id="ut-{{ $loop->index }}" value="{{ $ut }}">
                                    <label class="form-check-label" for="ut-{{ $loop->index }}">{{ $ut }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ===== Items (NEW): repeater grid ===== --}}
                    <div class="imf-card" id="newItemsCard">
                        <div class="imf-section-head">
                            <i class="icon-line-package"></i>
                            <h5>Items</h5>
                            <span class="imf-section-sub">Add a row per item</span>
                        </div>

                        <div class="imf-grid-wrap">
                            <table id="newItems" class="table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Item Description <span class="req">*</span></th>
                                        <th>Brand <span class="req">*</span></th>
                                        <th>OEM ID <span class="req">*</span></th>
                                        <th>UoM <span class="req">*</span></th>
                                        <th>Usage Rate <span class="req">*</span></th>
                                        <th>Frequency <span class="req">*</span></th>
                                        <th>Min <span class="req">*</span></th>
                                        <th>Max <span class="req">*</span></th>
                                        <th>Purpose <span class="req">*</span></th>
                                        <th>Attachment</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="imf-grid-toolbar">
                            <button type="button" id="addRow" class="btn btn-success" style="border-radius:6px"><i class="icon-line-plus me-1"></i>Add Row</button>
                            <div class="btn-group" role="group">
                                <button type="button" id="download_template" class="btn btn-warning mx-2" style="border-radius:6px; color:#212529"><i class="icon-line-download me-1"></i>Download Template</button>
                                <button type="button" id="import_csv" class="btn btn-primary" accept=".xlsx" style="border-radius:6px"><i class="icon-line-upload me-1"></i>Import CSV</button>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Items (UPDATE): one row per existing stock item ===== --}}
                    @include('theme.pages.customer.new-stock._update-items', ['hidden' => true])

                    <div class="imf-save-bar">
                        <button type="submit" name="action" value="save" class="button button-black button-circle button-xlarge fw-bold fs-14-f nols notextshadow m-0"><i class="icon-line-check me-1"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('pagejs')
<script src="{{ asset('lib/js-snackbar/js-snackbar.js') }}"></script>
<script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
<script src="{{ asset('lib/xlsx/xlsx.full.min.js') }}"></script>
@include('theme.pages.customer.new-stock._update-items-js')
<script>
    $(document).ready(function () {
        var FREQ = ['Daily', 'Weekly', 'Monthly', 'Yearly', 'As Needed'];

        /* ============================================================
         * Repeater grid (NEW items)
         * ============================================================ */
        function rowHtml(d) {
            d = d || {};
            var opts = FREQ.map(function (f) {
                return '<option value="' + f + '"' + (d.usage_frequency === f ? ' selected' : '') + '>' + f + '</option>';
            }).join('');
            return '<tr>' +
                '<td class="item-no-cell"></td>' +
                '<td><input type="hidden" name="stock_code[]" value="">' +
                    '<textarea name="item_description[]" rows="1" class="form-control form-input">' + (d.item_description || '') + '</textarea></td>' +
                '<td><input name="brand[]" class="form-control form-input" value="' + (d.brand || '') + '"></td>' +
                '<td><input name="OEM_ID[]" class="form-control form-input" value="' + (d.OEM_ID || '') + '"></td>' +
                '<td><input name="UoM[]" class="form-control form-input" value="' + (d.UoM || '') + '"></td>' +
                '<td><input type="number" name="usage_rate_qty[]" class="form-control form-input" value="' + (d.usage_rate_qty || '') + '"></td>' +
                '<td><select name="usage_frequency[]" class="form-select">' + opts + '</select></td>' +
                '<td><input type="number" name="min_qty[]" class="form-control form-input" value="' + (d.min_qty || 1) + '"></td>' +
                '<td><input type="number" name="max_qty[]" class="form-control form-input" value="' + (d.max_qty || '') + '"></td>' +
                '<td><input name="purpose[]" class="form-control form-input" value="' + (d.purpose || '') + '"></td>' +
                '<td><input type="file" name="attachment[]" class="form-control-file"></td>' +
                '<td class="text-center"><i class="icon-trash grid-del" title="Remove"></i></td>' +
            '</tr>';
        }

        function addRow(d) { $('#newItems tbody').append(rowHtml(d)); }

        addRow(); // start with one empty row

        $('#addRow').on('click', function () { addRow(); });

        $('#newItems tbody').on('click', '.grid-del', function () {
            if ($('#newItems tbody tr').length > 1) {
                $(this).closest('tr').remove();
            } else {
                // keep at least one row — just clear it
                var $tr = $(this).closest('tr');
                $tr.find('input[type=text], input[type=number], textarea').val('');
                $tr.find('input[name="min_qty[]"]').val(1);
                $tr.find('input[type=file]').val('');
            }
        });

        /* ============================================================
         * Request-type toggle (New grid  vs  Update single item)
         * ============================================================ */
        function setMode(type) {
            $('.imf-type-toggle label').removeClass('active');
            $('input[name=type]:checked').closest('label').addClass('active');

            // The hidden grid's inputs are disabled so they stay out of the post —
            // both grids use stock_code[], item_description[], ...
            if (type === 'update') {
                $('#newItemsCard').hide();
                $('#newItems').find('input, select, textarea').prop('disabled', true);

                $('#updItemsCard').show();
                ImfUpdateGrid.enable(true);
                $('#update-type-container').show();
            } else {
                $('#updItemsCard').hide();
                ImfUpdateGrid.enable(false);

                $('#newItemsCard').show();
                $('#newItems').find('input, select, textarea').prop('disabled', false);

                $('#update-type-container').hide().find('input[type=checkbox]').prop('checked', false);
            }
        }
        setMode('new');

        $('input[type=radio][name=type]').on('change', function () { setMode(this.value); });

        /* ============================================================
         * Submit
         * ============================================================ */
        function postForm(fd, redirect) {
            $.ajax({
                url: "{{ route('new-stock.store') }}",
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({ icon: 'success', title: response.message, showConfirmButton: false, timer: 1500, backdrop: 'rgba(0,0,0,0.7) left top no-repeat' })
                            .then(function () { window.location.href = response.redirect; });
                    } else if (typeof response.message === 'string' && response.message !== '') {
                        Swal.fire({ icon: 'warning', title: 'Not saved', text: response.message, backdrop: 'rgba(0,0,0,0.7) left top no-repeat' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong!', showConfirmButton: false, timer: 1200, backdrop: 'rgba(0,0,0,0.7) left top no-repeat' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong!', showConfirmButton: false, timer: 1200, backdrop: 'rgba(0,0,0,0.7) left top no-repeat' });
                }
            });
        }

        function warn(text) {
            Swal.fire({ icon: 'warning', title: text, showConfirmButton: false, timer: 1800, backdrop: 'rgba(0,0,0,0.7) left top no-repeat' });
        }

        $('#imf').on('submit', function (e) {
            e.preventDefault();
            var type = $('input[name=type]:checked').val();

            if (type === 'new') {
                // Drop completely-empty rows; validate the rest.
                var required = ['item_description[]', 'brand[]', 'OEM_ID[]', 'UoM[]', 'usage_rate_qty[]', 'min_qty[]', 'max_qty[]', 'purpose[]'];
                var descriptions = {};
                var filledRows = 0, invalid = false, dup = false;

                $('#newItems tbody tr').each(function () {
                    var $tr = $(this);
                    var vals = {};
                    $tr.find('input, select, textarea').each(function () {
                        var n = this.name; if (n) vals[n] = (this.type === 'file') ? (this.files.length ? 'file' : '') : $(this).val();
                    });
                    var anyFilled = required.some(function (n) { return (vals[n] || '').toString().trim() !== ''; });
                    if (!anyFilled) { $tr.remove(); return; }   // blank row → drop it

                    filledRows++;
                    var missing = required.some(function (n) { return (vals[n] || '').toString().trim() === ''; });
                    if (missing) { invalid = true; $tr.addClass('table-danger'); } else { $tr.removeClass('table-danger'); }

                    var desc = (vals['item_description[]'] || '').toString().trim().toLowerCase();
                    if (desc) { if (descriptions[desc]) { dup = true; } descriptions[desc] = true; }
                });

                if (filledRows === 0) { warn('Add at least one item.'); return; }
                if (invalid) { warn('Please complete all required fields in the highlighted rows.'); return; }
                if (dup) { warn('Duplicate item descriptions are not allowed.'); return; }

                var fd = new FormData(document.getElementById('imf'));
                fd.append('action', 'SAVED');
                postForm(fd);
            } else {
                // UPDATE — one row per existing item. The "old" values are
                // snapshotted server side from the same source the rows were filled from.
                var problem = ImfUpdateGrid.validate();
                if (problem) { warn(problem); return; }

                var fd = new FormData(document.getElementById('imf'));  // new-item grid is disabled → excluded
                fd.set('type', 'update');
                fd.append('action', 'SAVED');
                postForm(fd);
            }
        });

        /* ============================================================
         * Download template
         * ============================================================ */
        $('#download_template').on('click', function () {
            $.ajax({
                type: 'GET',
                url: "{{ route('download.template') }}",
                xhrFields: { responseType: 'blob' },
                success: function (data, status, xhr) {
                    var blob = new Blob([data], { type: xhr.getResponseHeader('Content-Type') });
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(blob);
                    a.href = url; a.download = 'create-new-stock-import-template.xlsx';
                    document.body.appendChild(a); a.click();
                    window.URL.revokeObjectURL(url); document.body.removeChild(a);
                    new SnackBar({ message: 'Template Downloaded Successfully!', status: 'success', position: 'bc', width: '500px', dismissible: false, container: '.form-container' });
                },
                error: function () {
                    new SnackBar({ message: 'Unable to download template.', status: 'error', position: 'bc', width: '500px', dismissible: false });
                }
            });
        });

        /* ============================================================
         * Import CSV / XLSX → append rows to the grid
         * ============================================================ */
        $('#import_csv').on('click', function () {
            var fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xlsx';
            fileInput.click();

            fileInput.addEventListener('change', function (e) {
                var file = e.target.files[0];
                var reader = new FileReader();
                reader.onload = function (event) {
                    var workbook = XLSX.read(new Uint8Array(event.target.result), { type: 'array' });
                    var sheet = workbook.Sheets[workbook.SheetNames[0]];
                    var json = XLSX.utils.sheet_to_json(sheet, { header: 1 });
                    var fields = ['item_description', 'brand', 'OEM_ID', 'UoM', 'usage_rate_qty', 'usage_frequency', 'min_qty', 'max_qty', 'purpose'];
                    var added = 0;

                    for (var i = 1; i < json.length; i++) {
                        var line = json[i];
                        if (!line || line.every(function (c) { return c === undefined || c === ''; })) continue;
                        var d = {};
                        for (var j = 0; j < fields.length; j++) { d[fields[j]] = line[j]; }
                        addRow(d);
                        added++;
                    }

                    if (added) {
                        // remove the leading blank starter row if it is still empty
                        var $first = $('#newItems tbody tr').first();
                        if (($first.find('textarea[name="item_description[]"]').val() || '').trim() === '') { $first.remove(); }
                        new SnackBar({ message: added + ' item(s) imported.', status: 'success', position: 'bc', width: '500px', dismissible: false, container: '.form-container' });
                    } else {
                        new SnackBar({ message: 'No rows found in the file.', status: 'error', position: 'bc', width: '500px', dismissible: false });
                    }
                };
                reader.readAsArrayBuffer(file);
            });
        });
    });
</script>
@endsection
