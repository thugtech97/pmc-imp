@extends(auth()->check() ? 'theme.main' : 'theme.main-blank-template')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('css/sweetalert.min.css') }}">
    <link href="{{ asset('lib/@fortawesome/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        .imf-doc-head { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:22px; }
        .imf-doc-head h3 { margin:0; font-weight:700; }
        .imf-doc-head p { margin:2px 0 0; color:#8a94a6; font-size:13px; }
        .imf-actions { display:flex; gap:8px; flex-wrap:wrap; }

        .imf-meta { display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:14px 24px;
            border:1px solid #eef0f2; border-radius:12px; padding:20px 22px; margin-bottom:22px; }
        .imf-meta .meta-label { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#adb5bd; font-weight:700; margin-bottom:2px; }
        .imf-meta .meta-value { font-weight:600; color:#212529; }
        .imf-purpose-badges { margin-top:6px; }
        .imf-purpose-badges span { display:inline-block; background:#eef2f7; color:#3b5876; border-radius:14px; padding:3px 10px; font-size:11px; font-weight:600; margin:2px 4px 2px 0; }

        .imf-section-head { display:flex; align-items:center; gap:10px; padding-bottom:12px; margin-bottom:16px; border-bottom:1px solid #eef0f2; }
        .imf-section-head i { font-size:20px; color:#3b7ddd; }
        .imf-section-head h5 { margin:0; font-weight:700; font-size:15px; letter-spacing:.3px; }

        .imf-table { width:100%; }
        .imf-table thead th { background:#f7f8fa; font-size:11px; letter-spacing:.3px; text-transform:uppercase; color:#6c757d; font-weight:700; padding:10px; border-bottom:1px solid #eef0f2; }
        .imf-table tbody td { padding:10px; border-top:1px solid #f1f3f5; vertical-align:middle; font-size:13px; }
        .imf-table tbody tr:hover { background:#fafbfc; }
        .imf-diff .old { background:#fff6f6; color:#a15b5b; }
        .imf-diff .new { background:#f4faf6; color:#2f7d52; font-weight:600; }
        .imf-diff th.rowlabel { text-align:left; background:#fafbfc; font-weight:700; color:#495057; }
        .imf-download { color:#3b7ddd; }
    </style>
@endsection
@section('content')
    @php
        $showStockCodeColumn = $items->isNotEmpty() && $items->contains(function ($item) {
            return $item->stock_code !== "null" && $item->stock_code !== null && $item->stock_code !== '';
        });
        $isUpdate = $request->type === 'update';
        $selectedUpdateTypes = array_filter(array_map('trim', explode(',', $request->update_type ?? '')));
    @endphp

    <div class="container-fluid content-wrap">

        <div class="imf-doc-head">
            <div>
                <h3>Inventory Maintenance Form #{{ $request->id }}</h3>
                <p>@include('theme.pages.customer.new-stock._status-badge', ['status' => $request->status])</p>
            </div>
            @if(auth()->check())
            <div class="imf-actions">
                <a href="{{ route('new-stock.index') }}" class="btn btn-secondary px-3"><i class="icon-line-chevron-left me-1"></i> Back</a>
                <a class="btn btn-success" style="cursor:pointer;" id="print" data-order-number="{{ $request->items[0]['imf_no'] ?? $request->id }}">
                    <i class="fas fa-print"></i> Print
                </a>
                @if($request->status == 'SAVED')
                <a onclick="confirmApproval({{ $request->id }}, 'new')" href="javascript:;" class="btn btn-dark px-3">
                    <i class="icon-arrow-alt-circle-right me-1"></i> Submit
                </a>
                @endif
            </div>
            @endif
        </div>

        {{-- Meta --}}
        <div class="imf-meta">
            <div>
                <div class="meta-label">Type</div>
                <div class="meta-value text-uppercase">{{ $request->type }}</div>
            </div>
            <div>
                <div class="meta-label">Department</div>
                <div class="meta-value text-uppercase">{{ $request->department }}</div>
            </div>
            <div>
                <div class="meta-label">Section</div>
                <div class="meta-value text-uppercase">{{ $request->section ?? '—' }}</div>
            </div>
            <div>
                <div class="meta-label">Division</div>
                <div class="meta-value text-uppercase">{{ $request->division ?? '—' }}</div>
            </div>
            <div>
                <div class="meta-label">Priority</div>
                <div class="meta-value">{{ $request->priority ?? '—' }}</div>
            </div>
            <div>
                <div class="meta-label">Prepared by</div>
                <div class="meta-value">{{ $request->user->name ?? '—' }}</div>
            </div>
            <div>
                <div class="meta-label">Date Prepared</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y h:i A') }}</div>
            </div>
            @if($request->submitted_at)
            <div>
                <div class="meta-label">Submitted</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($request->submitted_at)->format('M d, Y h:i A') }}</div>
            </div>
            @endif
            @if($isUpdate && $showStockCodeColumn)
            <div>
                <div class="meta-label">Stock Code</div>
                <div class="meta-value">{{ $items[0]->stock_code }}</div>
            </div>
            @endif
            @if($isUpdate && count($selectedUpdateTypes))
            <div style="grid-column:1/-1;">
                <div class="meta-label">Purpose of Update</div>
                <div class="imf-purpose-badges">
                    @foreach($selectedUpdateTypes as $ut)<span>{{ $ut }}</span>@endforeach
                </div>
            </div>
            @endif
        </div>

        @if($request->note_planner)
        <div style="border:1px solid #ffe082; border-left:5px solid #ff9800; background:#fff8e1; border-radius:8px; padding:12px 16px; margin-bottom:18px;">
            <div style="font-weight:700; text-transform:uppercase; letter-spacing:.5px; font-size:12px; color:#e65100; margin-bottom:3px;">
                Returned by MCD Planner — please review &amp; re-edit
            </div>
            <div style="color:#5d4037; font-size:14px;">{{ $request->note_planner }}</div>
        </div>
        @endif

        {{-- Items --}}
        <div class="imf-section-head">
            <i class="icon-line-package"></i>
            <h5>{{ $isUpdate ? 'Requested Changes' : 'Items' }}</h5>
        </div>

        @if($isUpdate)
            <div class="table-responsive">
                <table class="imf-table imf-diff">
                    <thead>
                        <tr>
                            <th style="width:22%"></th>
                            <th style="width:39%">Old Value</th>
                            <th>
                                New Value
                                @if (!empty($items[0]->file_path))
                                    &nbsp;<a href="#" class="download-link imf-download" data-file="{{ $items[0]->file_path }}"><small>(View Attachment)</small></a>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $old = $oldItems[0] ?? null;
                            $new = $items[0] ?? null;
                            $rows = [
                                ['Item Description', 'item_description'], ['Brand', 'brand'], ['OEM ID', 'OEM_ID'],
                                ['UoM', 'UoM'], ['Usage Rate Qty', 'usage_rate_qty'], ['Usage Frequency', 'usage_frequency'],
                                ['Min Qty', 'min_qty'], ['Max Qty', 'max_qty'], ['Purpose', 'purpose'],
                            ];
                        @endphp
                        @foreach($rows as [$label, $field])
                            @php
                                $oldVal = $old ? $old->{$field} : '';
                                $newVal = $new ? $new->{$field} : '';
                                $displayOld = ($old && $oldVal !== '' && $oldVal !== null) ? $oldVal : '';
                            @endphp
                            <tr>
                                <th class="rowlabel">{{ $label }}</th>
                                <td class="old">{{ $displayOld }}</td>
                                <td class="new">{{ $newVal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="table-responsive">
                <table class="imf-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            @if ($showStockCodeColumn)<th>Stock Code</th>@endif
                            <th>Item Description</th>
                            <th>Brand</th>
                            <th>OEM ID</th>
                            <th>UoM</th>
                            <th>Usage Rate</th>
                            <th>Frequency</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Purpose</th>
                            <th class="text-center">File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                @if ($showStockCodeColumn)<td>{{ $item->stock_code !== "null" ? $item->stock_code : '' }}</td>@endif
                                <td class="text-uppercase">{{ $item->item_description }}</td>
                                <td class="text-uppercase">{{ $item->brand }}</td>
                                <td>{{ $item->OEM_ID }}</td>
                                <td>{{ $item->UoM }}</td>
                                <td>{{ $item->usage_rate_qty }}</td>
                                <td>{{ $item->usage_frequency }}</td>
                                <td>{{ $item->min_qty }}</td>
                                <td>{{ $item->max_qty }}</td>
                                <td>{{ $item->purpose }}</td>
                                <td class="text-center">
                                    @if (!empty($item->file_path))
                                    <a href="#" class="download-link imf-download" data-file="{{ $item->file_path }}" title="View">
                                        <i class="fas fa-file-download"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center text-muted py-4">No items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection


@section('pagejs')
<script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.download-link').click(function(e) {
            e.preventDefault();
            var filePath = $(this).data('file');
            var downloadUrl = "{{ route('download.files') }}?file=" + encodeURIComponent(filePath);
            var link = document.createElement('a');
            link.href = downloadUrl;
            link.target = '_blank';
            link.download = filePath;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });

    $('#print').click(function(evt) {
        evt.preventDefault();
        var imfNo = this.getAttribute('data-order-number');
        $.ajax({
            url: "{{route('imf-request.generate_report')}}",
            type: 'GET',
            data: { id: imfNo },
            xhrFields: { responseType: 'blob' },
            success: function(data) {
                if (data instanceof Blob) {
                    const pdfBlob = new Blob([data], { type: 'application/pdf' });
                    const pdfUrl = URL.createObjectURL(pdfBlob);
                    window.open(pdfUrl, '_blank');
                    URL.revokeObjectURL(pdfUrl);
                }
            }
        });
    });

    function confirmApproval(id, type) {
        Swal.fire({
            title: 'Submit for Approval',
            text: "Are you sure you want to submit this IMF for approval?",
            icon: "question",
            showCancelButton: true,
            allowOutsideClick: false,
            confirmButtonColor: '#2ecc71',
            confirmButtonText: 'Yes, submit!',
            backdrop: `rgba(0,0,0,0.7) left top no-repeat`
        }).then((result) => {
            if(result.isConfirmed) {
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content") } });
                var url = "{{ route('new-stock.submit.request', ['id' => ":id", 'type' => ":type"]) }}";
                url = url.replace(':id', id);
                url = url.replace(':type', type);
                $.ajax({
                    type: 'GET',
                    url: url,
                    beforeSend: function () { Swal.showLoading(); },
                    success: function(response) {
                        Swal.fire({ icon: "success", title: 'IMF Submitted!', text: 'The IMF has been successfully submitted for approval.', showConfirmButton: false, timer: 1500, backdrop: `rgba(0,0,0,0.7) left top no-repeat` })
                            .then(() => { window.location.reload(true); });
                    },
                    error: function() {
                        Swal.fire({ icon: "success", title: 'IMF Submitted!', text: 'The IMF has been successfully submitted for approval.', showConfirmButton: false, timer: 1500, backdrop: `rgba(0,0,0,0.7) left top no-repeat` })
                            .then(() => { window.location.reload(true); });
                    }
                });
            }
        });
    }
</script>
@endsection
