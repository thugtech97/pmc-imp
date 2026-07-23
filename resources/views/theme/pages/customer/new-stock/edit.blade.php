@extends('theme.main')

@section('pagecss')
<link rel="stylesheet" href="{{ asset('css/sweetalert.min.css') }}">
<link rel="stylesheet" href="{{ asset('lib/js-snackbar/js-snackbar.css') }}" type="text/css" />
<style>
    span.req { color: #aa0707; }

    .imf-form-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:22px; }
    .imf-form-head h3 { margin:0; font-weight:700; }
    .imf-form-head p { margin:2px 0 0; color:#8a94a6; font-size:13px; }
    .imf-card { border:1px solid #e9ecef; border-radius:12px; padding:20px 22px; margin-bottom:20px; background:#fff; }
    .imf-section-head { display:flex; align-items:center; gap:10px; padding-bottom:12px; margin-bottom:18px; border-bottom:1px solid #eef0f2; }
    .imf-section-head i { font-size:20px; color:#3b7ddd; }
    .imf-section-head h5 { margin:0; font-weight:700; font-size:15px; letter-spacing:.3px; }
    .imf-section-head .imf-section-sub { margin-left:auto; font-size:12px; color:#adb5bd; font-weight:500; }

    .imf-type-toggle { display:flex; gap:12px; flex-wrap:wrap; }
    .imf-type-toggle label { flex:1; min-width:180px; border:1.5px solid #e3e6ea; border-radius:10px; padding:14px 16px; margin:0; display:flex; align-items:flex-start; gap:10px; opacity:.55; }
    .imf-type-toggle label.active { border-color:#3b7ddd; background:#f4f8ff; opacity:1; }
    .imf-type-toggle input { margin-top:3px; }
    .imf-type-toggle .imf-type-title { font-weight:700; color:#212529; }
    .imf-type-toggle .imf-type-desc { font-size:12px; color:#8a94a6; }

    .imf-grid-wrap { border:1px solid #eef0f2; border-radius:10px; overflow-x:auto; }
    #newItems { margin:0; min-width:1100px; }
    #newItems thead th { background:#f7f8fa; font-size:11px; letter-spacing:.3px; text-transform:uppercase; color:#6c757d; font-weight:700; border-bottom:1px solid #eef0f2; padding:10px 8px; white-space:nowrap; }
    #newItems td { padding:6px; vertical-align:middle; border-top:1px solid #f1f3f5; }
    #newItems .form-control, #newItems .form-select { font-size:13px; padding:6px 8px; min-width:100px; }
    #newItems textarea.form-control { min-width:200px; }
    #newItems td.item-no-cell { text-align:center; font-weight:700; color:#adb5bd; width:44px; min-width:44px; }
    #newItems td.item-no-cell::before { content: counter(itemno); }
    #newItems tbody { counter-reset: itemno; }
    #newItems tbody tr { counter-increment: itemno; }
    #newItems .grid-del { color:#f34237; cursor:pointer; font-size:16px; }
    .imf-grid-toolbar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-top:14px; }

    .imf-save-bar { position:sticky; bottom:0; z-index:5; background:#fff; border-top:1px solid #eef0f2; padding:14px 4px 4px; margin-top:10px; display:flex; justify-content:flex-end; gap:10px; }
</style>
@endsection
@section('content')
@php
    $isUpdate = $request->type === 'update';
    $item0 = $items[0] ?? null;
    $selectedUpdateTypes = array_filter(array_map('trim', explode(',', $request->update_type ?? '')));
@endphp
<div class="container-fluid">
    <div class="row">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif
        @if (Session::has('success'))<div class="alert alert-info" role="alert">{{ Session::get('success') }}</div>@endif
        @if (Session::has('error'))<div class="alert alert-danger" role="alert">{{ Session::get('error') }}</div>@endif

        <div class="col-lg-12">
            <div class="border py-4 px-3 border-transparent shadow-lg p-lg-5 form-container">

                <div class="imf-form-head">
                    <div>
                        <h3>Edit IMF #{{ $request->id }}</h3>
                        <p>Update the details below and save your changes.</p>
                    </div>
                    <a href="{{ route('new-stock.index') }}" class="button button-dark button-border button-circle button-large fw-bold fs-14-f nols notextshadow m-0">
                        <i class="icon-line-chevron-left me-1"></i> Back
                    </a>
                </div>

                <form id="imf">
                    @csrf
                    <input type="hidden" name="department" value="{{ $request->department }}">

                    {{-- ===== Request Type (locked) ===== --}}
                    <div class="imf-card">
                        <div class="imf-section-head">
                            <i class="icon-line-layers"></i>
                            <h5>Request Type</h5>
                            <span class="imf-section-sub">Type cannot be changed after creation</span>
                        </div>
                        <div class="imf-type-toggle">
                            <label class="{{ $isUpdate ? '' : 'active' }}">
                                <input class="form-check-input" type="radio" name="type" value="new" {{ $isUpdate ? '' : 'checked' }} disabled>
                                <span>
                                    <span class="imf-type-title d-block">New Stock Item</span>
                                    <span class="imf-type-desc">Register one or more brand-new items.</span>
                                </span>
                            </label>
                            <label class="{{ $isUpdate ? 'active' : '' }}">
                                <input class="form-check-input" type="radio" name="type" value="update" {{ $isUpdate ? 'checked' : '' }} disabled>
                                <span>
                                    <span class="imf-type-title d-block">Update</span>
                                    <span class="imf-type-desc">Change details of an existing stock item.</span>
                                </span>
                            </label>
                        </div>
                        {{-- radios are disabled (not submitted) so send the type explicitly --}}
                        <input type="hidden" name="type" value="{{ $request->type }}">
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
                                    <input type="text" class="form-control form-input" value="{{ $request->department }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label for="section" class="fw-semibold text-initial nols">Section</label>
                                    <input type="text" id="section" class="form-control form-input" name="section" value="{{ $request->section }}" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label for="division" class="fw-semibold text-initial nols">Division</label>
                                    <input type="text" id="division" class="form-control form-input" name="division" value="{{ $request->division }}" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-4">
                                    <label for="priority" class="fw-semibold text-initial nols">Priority <span class="req">&#42;</span></label>
                                    <select id="priority" class="form-select" name="priority">
                                        <option value="1" {{ $request->priority == '1' ? 'selected' : '' }}>Priority 1 &mdash; Very Urgent</option>
                                        <option value="2" {{ $request->priority == '2' ? 'selected' : '' }}>Priority 2 &mdash; High Priority</option>
                                        <option value="3" {{ $request->priority == '3' ? 'selected' : '' }}>Priority 3 &mdash; Needed but not priority</option>
                                    </select>
                                </div>
                            </div>

                            @if ($isUpdate)
                            <div class="col-md-4" id="stockCode">
                                <div class="form-group mb-4">
                                    <label for="stock-code" class="fw-semibold text-initial nols">Stock Code</label>
                                    <input type="text" id="stock-code" class="form-control form-input" name="stock_code" value="{{ $item0->stock_code ?? '' }}" readonly />
                                </div>
                            </div>
                            @endif
                        </div>

                        @if ($isUpdate)
                        <div class="form-group mb-0" id="update-type-container">
                            <label class="fw-semibold text-initial nols d-block">Purpose of Update <small class="text-muted">(check all that apply)</small></label>
                            @foreach (['Stock type update', 'Min-max update', 'Merge code', 'Obsolete', 'Bin location update', 'Others'] as $ut)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="update_type[]" id="ut-{{ $loop->index }}" value="{{ $ut }}" {{ in_array($ut, $selectedUpdateTypes) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ut-{{ $loop->index }}">{{ $ut }}</label>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    @if (!$isUpdate)
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
                                <button type="button" id="import_csv" class="btn btn-primary" style="border-radius:6px"><i class="icon-line-upload me-1"></i>Import CSV</button>
                            </div>
                        </div>
                    </div>
                    @else
                    {{-- ===== Item (UPDATE): single item ===== --}}
                    <div class="imf-card" id="updateItemCard">
                        <div class="imf-section-head">
                            <i class="icon-line-package"></i>
                            <h5>Item to Update</h5>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group mb-4">
                                    <label for="item-description" class="fw-semibold text-initial nols">Item Description <span class="req">&#42;</span></label>
                                    <textarea id="item-description" class="form-control form-input" name="item_description" rows="3" required>{{ $item0->item_description ?? '' }}</textarea>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="brand" class="fw-semibold text-initial nols">Brand</label>
                                    <input type="text" id="brand" class="form-control form-input" name="brand" value="{{ $item0->brand ?? '' }}" />
                                </div>
                                <div class="form-group mb-4">
                                    <label for="oem-id" class="fw-semibold text-initial nols">OEM ID</label>
                                    <input type="text" id="oem-id" class="form-control form-input" name="OEM_ID" value="{{ $item0->OEM_ID ?? '' }}" />
                                </div>
                                <div class="form-group mb-4">
                                    <label for="uom" class="fw-semibold text-initial nols">Unit of Measure (UoM) <span class="req">&#42;</span></label>
                                    <input type="text" id="uom" class="form-control form-input" name="UoM" value="{{ $item0->UoM ?? '' }}" required />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="usage-rate-qty" class="fw-semibold text-initial nols">Usage Rate Qty <span class="req">&#42;</span></label>
                                            <input type="number" id="usage-rate-qty" class="form-control form-input" name="usage_rate_qty" value="{{ $item0->usage_rate_qty ?? '' }}" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="usage-frequency" class="fw-semibold text-initial nols">Usage Frequency <span class="req">&#42;</span></label>
                                            <select name="usage_frequency" id="usage-frequency" class="form-select">
                                                @foreach (['Daily', 'Weekly', 'Monthly', 'Yearly'] as $f)
                                                    <option value="{{ $f }}" {{ ($item0->usage_frequency ?? '') == $f ? 'selected' : '' }}>{{ $f }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="min-qty" class="fw-semibold text-initial nols">Min Qty <span class="req">&#42;</span></label>
                                            <input type="number" id="min-qty" class="form-control form-input" name="min_qty" value="{{ $item0->min_qty ?? 1 }}" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="max-qty" class="fw-semibold text-initial nols">Max Qty <span class="req">&#42;</span></label>
                                            <input type="number" id="max-qty" class="form-control form-input" name="max_qty" value="{{ $item0->max_qty ?? '' }}" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="purpose" class="fw-semibold text-initial nols">Item to be used for/Application/Purpose</label>
                                    <input type="text" id="purpose" class="form-control form-input" name="purpose" value="{{ $item0->purpose ?? '' }}" />
                                </div>
                                <div class="form-group">
                                    <label for="attach-files" class="fw-semibold text-initial nols">Attach Files</label>
                                    <input type="file" class="form-control-file d-block" id="attach-files" name="attachment">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="imf-save-bar">
                        <button type="submit" name="action" value="save" class="button button-black button-circle button-xlarge fw-bold fs-14-f nols notextshadow m-0"><i class="icon-line-check me-1"></i>Save Changes</button>
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
<script>
    $(document).ready(function () {
        var IS_UPDATE = {{ $isUpdate ? 'true' : 'false' }};
        var FREQ = ['Daily', 'Weekly', 'Monthly', 'Yearly'];
        var oldDataArray = [];

        function postForm(fd) {
            $.ajax({
                url: "{{ route('imf.update', $request->id) }}",
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    Swal.fire({ icon: 'success', title: response.message || 'Saved!', showConfirmButton: false, timer: 1500, backdrop: 'rgba(0,0,0,0.7) left top no-repeat' })
                        .then(function () { window.location.href = response.redirect; });
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong!', showConfirmButton: false, timer: 1200, backdrop: 'rgba(0,0,0,0.7) left top no-repeat' });
                }
            });
        }

        function warn(text) {
            Swal.fire({ icon: 'warning', title: text, showConfirmButton: false, timer: 1800, backdrop: 'rgba(0,0,0,0.7) left top no-repeat' });
        }

        if (!IS_UPDATE) {
            /* ---------- NEW: repeater grid ---------- */
            function rowHtml(d) {
                d = d || {};
                var opts = FREQ.map(function (f) { return '<option value="' + f + '"' + (d.usage_frequency === f ? ' selected' : '') + '>' + f + '</option>'; }).join('');
                return '<tr>' +
                    '<td class="item-no-cell"></td>' +
                    '<td><input type="hidden" name="stock_code[]" value="' + (d.stock_code && d.stock_code !== 'null' ? d.stock_code : '') + '">' +
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

            var existing = @json($items);
            if (existing && existing.length) { existing.forEach(function (it) { addRow(it); }); }
            else { addRow(); }

            $('#addRow').on('click', function () { addRow(); });
            $('#newItems tbody').on('click', '.grid-del', function () {
                if ($('#newItems tbody tr').length > 1) { $(this).closest('tr').remove(); }
                else {
                    var $tr = $(this).closest('tr');
                    $tr.find('input[type=text], input[type=number], textarea').val('');
                    $tr.find('input[name="min_qty[]"]').val(1);
                    $tr.find('input[type=file]').val('');
                }
            });

            $('#download_template').on('click', function () {
                $.ajax({
                    type: 'GET', url: "{{ route('download.template') }}", xhrFields: { responseType: 'blob' },
                    success: function (data, status, xhr) {
                        var blob = new Blob([data], { type: xhr.getResponseHeader('Content-Type') });
                        var a = document.createElement('a'); var url = window.URL.createObjectURL(blob);
                        a.href = url; a.download = 'create-new-stock-import-template.xlsx';
                        document.body.appendChild(a); a.click(); window.URL.revokeObjectURL(url); document.body.removeChild(a);
                    }
                });
            });

            $('#import_csv').on('click', function () {
                var fileInput = document.createElement('input'); fileInput.type = 'file'; fileInput.accept = '.xlsx'; fileInput.click();
                fileInput.addEventListener('change', function (e) {
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        var wb = XLSX.read(new Uint8Array(event.target.result), { type: 'array' });
                        var sheet = wb.Sheets[wb.SheetNames[0]];
                        var json = XLSX.utils.sheet_to_json(sheet, { header: 1 });
                        var fields = ['item_description', 'brand', 'OEM_ID', 'UoM', 'usage_rate_qty', 'usage_frequency', 'min_qty', 'max_qty', 'purpose'];
                        var added = 0;
                        for (var i = 1; i < json.length; i++) {
                            var line = json[i];
                            if (!line || line.every(function (c) { return c === undefined || c === ''; })) continue;
                            var d = {}; for (var j = 0; j < fields.length; j++) { d[fields[j]] = line[j]; }
                            addRow(d); added++;
                        }
                        if (added) {
                            var $first = $('#newItems tbody tr').first();
                            if (($first.find('textarea[name="item_description[]"]').val() || '').trim() === '') { $first.remove(); }
                        }
                    };
                    reader.readAsArrayBuffer(e.target.files[0]);
                });
            });
        } else {
            /* ---------- UPDATE: capture original values for diff ---------- */
            var current = {
                stock_code: $('#stock-code').val(), item_description: $('#item-description').val(),
                brand: $('#brand').val(), OEM_ID: $('#oem-id').val(), UoM: $('#uom').val(),
                usage_rate_qty: $('#usage-rate-qty').val(), usage_frequency: $('#usage-frequency').val(),
                min_qty: $('#min-qty').val(), max_qty: $('#max-qty').val(), purpose: $('#purpose').val()
            };
            oldDataArray = Object.entries(current)
                .filter(function (e) { return e[1] !== null && e[1] !== undefined && String(e[1]).trim() !== ''; })
                .map(function (e) { return { name: e[0], value: String(e[1]) }; });
        }

        /* ---------- Submit ---------- */
        $('#imf').on('submit', function (e) {
            e.preventDefault();

            if (!IS_UPDATE) {
                var required = ['item_description[]', 'brand[]', 'OEM_ID[]', 'UoM[]', 'usage_rate_qty[]', 'min_qty[]', 'max_qty[]', 'purpose[]'];
                var descriptions = {}, filledRows = 0, invalid = false, dup = false;

                $('#newItems tbody tr').each(function () {
                    var $tr = $(this); var vals = {};
                    $tr.find('input, select, textarea').each(function () {
                        var n = this.name; if (n) vals[n] = (this.type === 'file') ? (this.files.length ? 'file' : '') : $(this).val();
                    });
                    var anyFilled = required.some(function (n) { return (vals[n] || '').toString().trim() !== ''; });
                    if (!anyFilled) { $tr.remove(); return; }
                    filledRows++;
                    if (required.some(function (n) { return (vals[n] || '').toString().trim() === ''; })) { invalid = true; $tr.addClass('table-danger'); } else { $tr.removeClass('table-danger'); }
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
                var fd = new FormData(document.getElementById('imf'));
                fd.append('action', 'SAVED');
                var changed = oldDataArray.filter(function (o) {
                    var live = fd.get(o.name);
                    return live === null || String(live) !== String(o.value);
                });
                fd.append('old-data', JSON.stringify(changed));
                postForm(fd);
            }
        });
    });
</script>
@endsection
