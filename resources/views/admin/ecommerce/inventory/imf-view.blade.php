@extends('admin.layouts.app')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('lib/sweetalert2/sweetalert.min.css') }}" type="text/css">
    @include('admin.components._pa-design-system')
    <style>
        /* Old/new comparison shown for an "update" IMF — the only markup this screen
           needs on top of the shared design system. */
        .imf-diff .rowlabel { width: 22%; background: #f8fafc; font-weight: 600; color: var(--pa-text); }
        .imf-diff td.old { background: #f8fafc; color: var(--pa-text-muted); }
        .imf-diff td.new { font-weight: 500; }

        .imf-badges { display: flex; flex-wrap: wrap; gap: 6px; }
        .imf-badges span {
            display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
            background: var(--pa-primary-light); color: var(--pa-primary); border: 1px solid #bfdbfe;
        }
    </style>
@endsection

@section('content')
@php
    $isPlanner  = $role->name === 'MCD Planner';
    // The Planning Supervisor is the final approver. The MCD Approver keeps the
    // screen for reference only — no action bar.
    $isApprover = $role->name === 'Planning Supervisor';
    $isViewer   = $role->name === 'MCD Approver';
    $isUpdate   = $request->type === 'update';

    $showStockCodeColumn = $items->isNotEmpty() && $items->contains(function ($item) {
        return $item->stock_code !== "null" && $item->stock_code !== null && $item->stock_code !== '';
    });

    $statusLower = strtolower((string) $request->status);
    $statusClass = 'status-default';
    if (strpos($statusLower, 'reject') !== false)        $statusClass = 'status-cancelled';
    elseif (strpos($statusLower, 'cancel') !== false)    $statusClass = 'status-cancelled';
    elseif (strpos($statusLower, 'hold') !== false)      $statusClass = 'status-pending';
    elseif (strpos($statusLower, 'approved') !== false)  $statusClass = 'status-approved';

    $selectedUpdateTypes = array_filter(array_map('trim', explode(',', (string) $request->update_type)));

    // Planner acts on fresh WFS-approved items and on items the Supervisor returned.
    $plannerStages  = array_merge([\App\Constants\Status::APPROVED_WFS], \App\Constants\Status::imfFinalHold());
    $canPlannerAct  = $isPlanner && in_array($request->status, $plannerStages);
    // The Planning Supervisor acts once the Planner has endorsed.
    $canApproverAct = $isApprover && $request->status === \App\Constants\Status::APPROVED_MCD;
    $approveLabel   = $isApprover ? 'Approve &amp; Register' : 'Approve &amp; Endorse';
    $holdLabel      = $isApprover ? 'Hold (return to Planner)' : 'Hold (return to requestor)';

    // The print endpoint keys off the item's imf_no; fall back to the IMF id so an
    // IMF with no lines still renders instead of blowing up on $items[0].
    $printRef = optional($items->first())->imf_no ?: $request->id;
@endphp

<div class="container-fluid" style="max-width: 1600px;">

    <div class="pa-page-header d-flex align-items-start justify-content-between flex-wrap" style="gap:14px;">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">CMS</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('imf.requests') }}">IMF Requests</a></li>
                    <li class="breadcrumb-item active">IMF Summary</li>
                </ol>
            </nav>
            <h4>
                IMF# {{ $request->id }}
                @if ($request->revision > 0)
                    <span class="pa-rev-badge">{{ $request->rev_label }}</span>
                @endif
            </h4>
            @if ($request->revision > 0 && $request->revised_at)
                <div class="pa-subtitle">Last revised {{ $request->revised_at->format('M d, Y h:i A') }}</div>
            @endif
        </div>
        <div class="d-flex flex-column align-items-end" style="gap:8px;">
            <div class="d-flex" style="gap:8px;">
                @if ($isPlanner || $isApprover || $isViewer)
                    <a href="#" id="printDetails" class="btn-pa btn-pa-success" data-order="{{ $printRef }}"><i class="fa fa-print"></i> Print IMF</a>
                @endif
                <a href="{{ route('imf.requests') }}" class="btn-pa btn-pa-secondary"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <span class="pa-status-badge {{ $statusClass }}"><i class="fa fa-circle"></i> {{ $request->status }}</span>
        </div>
    </div>

    @if ($request->note_verifier)
        <div class="pa-notice notice-warning">
            <i class="fa fa-reply notice-icon"></i>
            <div>
                <div class="notice-title">Planning Supervisor Remark</div>
                <div class="notice-body">{{ $request->note_verifier }}</div>
            </div>
        </div>
    @endif

    @if ($request->note_planner)
        <div class="pa-notice notice-warning">
            <i class="fa fa-reply-all notice-icon"></i>
            <div>
                <div class="notice-title">Planner Remark</div>
                <div class="notice-body">{{ $request->note_planner }}</div>
            </div>
        </div>
    @endif

    {{-- Reference + timeline --}}
    <div class="row">
        <div class="col-lg-4">
            <div class="pa-card">
                <div class="pa-card-header">
                    <div class="card-icon"><i class="fa fa-file-text-o"></i></div>
                    <div><h6>IMF Reference</h6><p>Form number and request type</p></div>
                </div>
                <div class="pa-card-body">
                    <label class="pa-label">IMF Number</label>
                    <div class="pa-number-display">
                        <i class="fa fa-hashtag pa-number-icon"></i>
                        <span class="pa-number-value">{{ $request->id }}</span>
                        @if ($request->revision > 0)
                            <span class="pa-rev-badge">{{ $request->rev_label }}</span>
                        @endif
                    </div>
                    <label class="pa-label" style="margin-top:16px;">Request Type</label>
                    <div class="pa-number-display">
                        <i class="fa fa-tag pa-number-icon"></i>
                        <span class="pa-number-value">{{ strtoupper($request->type) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="pa-card">
                <div class="pa-card-header">
                    <div class="card-icon"><i class="fa fa-info-circle"></i></div>
                    <div><h6>IMF Timeline</h6><p>Approval and processing status</p></div>
                </div>
                <div class="pa-card-body">
                    <div class="pa-meta-grid">
                        <div class="pa-meta-item">
                            <div class="meta-label">Date Prepared</div>
                            <div class="meta-value">{{ $request->created_at ? $request->created_at->format('M d, Y h:i A') : '—' }}</div>
                        </div>
                        <div class="pa-meta-item">
                            <div class="meta-label">Last Updated</div>
                            <div class="meta-value">{{ $request->updated_at ? $request->updated_at->format('M d, Y h:i A') : '—' }}</div>
                        </div>
                        <div class="pa-meta-item">
                            <div class="meta-label">Submitted At</div>
                            <div class="meta-value {{ !$request->submitted_at ? 'empty' : '' }}">
                                {{ $request->submitted_at ? \Carbon\Carbon::parse($request->submitted_at)->format('M d, Y') : 'Not yet submitted' }}
                            </div>
                        </div>
                        <div class="pa-meta-item">
                            <div class="meta-label">Approved At</div>
                            <div class="meta-value {{ !$request->approved_at ? 'empty' : '' }}">
                                {{ $request->approved_at ? \Carbon\Carbon::parse($request->approved_at)->format('M d, Y') : 'Not yet approved' }}
                            </div>
                        </div>
                        <div class="pa-meta-item">
                            <div class="meta-label">MCD Planner</div>
                            <div class="meta-value {{ !$request->planner_approved_by ? 'empty' : '' }}">
                                {{ $request->planner_approved_by ?: 'Not yet endorsed' }}
                            </div>
                        </div>
                        <div class="pa-meta-item">
                            <div class="meta-label">Planning Supervisor</div>
                            <div class="meta-value {{ !$request->approver_approved_by ? 'empty' : '' }}">
                                {{ $request->approver_approved_by ?: 'Not yet approved' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Requestor details --}}
    <div class="pa-card">
        <div class="pa-card-header">
            <div class="card-icon"><i class="fa fa-user-o"></i></div>
            <div><h6>Request Details</h6><p>Who raised this IMF and where it came from</p></div>
        </div>
        <div class="pa-card-body">
            <div class="pa-meta-grid">
                <div class="pa-meta-item">
                    <div class="meta-label">Department</div>
                    <div class="meta-value {{ !$request->department ? 'empty' : '' }}">{{ $request->department ?: '—' }}</div>
                </div>
                <div class="pa-meta-item">
                    <div class="meta-label">Section</div>
                    <div class="meta-value {{ !$request->section ? 'empty' : '' }}">{{ $request->section ?: '—' }}</div>
                </div>
                <div class="pa-meta-item">
                    <div class="meta-label">Division</div>
                    <div class="meta-value {{ !$request->division ? 'empty' : '' }}">{{ $request->division ?: '—' }}</div>
                </div>
                <div class="pa-meta-item">
                    <div class="meta-label">Priority</div>
                    <div class="meta-value {{ !$request->priority ? 'empty' : '' }}">{{ $request->priority ?: '—' }}</div>
                </div>
                <div class="pa-meta-item">
                    <div class="meta-label">Created By</div>
                    <div class="meta-value">{{ strtoupper(optional($request->user)->name ?: 'N/A') }}</div>
                </div>
                @if ($isUpdate && $showStockCodeColumn)
                    <div class="pa-meta-item">
                        <div class="meta-label">Stock Code</div>
                        <div class="meta-value">{{ optional($items->first())->stock_code }}</div>
                    </div>
                @endif
                @if ($isUpdate && count($selectedUpdateTypes))
                    <div class="pa-meta-item" style="grid-column:1 / -1;">
                        <div class="meta-label">Purpose of Update</div>
                        <div class="imf-badges">
                            @foreach ($selectedUpdateTypes as $ut)<span>{{ $ut }}</span>@endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="pa-card">
        <div class="pa-card-header">
            <div class="card-icon"><i class="fa fa-list"></i></div>
            <div>
                <h6>{{ $isUpdate ? 'Requested Changes' : 'Items' }}</h6>
                <p>{{ $isUpdate ? 'Old value against the requested new value' : $items->count() . ' item(s) in this request' }}</p>
            </div>
        </div>
        <div class="pa-card-body" style="padding:0;">
            <div class="pa-table-wrapper">
                @if ($isUpdate)
                    @php
                        $old = $oldItems[0] ?? null;
                        $new = $items[0] ?? null;
                        // [label, column, carryOver]. carryOver mirrors the original screen:
                        // a field the requestor did not touch shows its current value on the
                        // OLD side and leaves the NEW side blank. Purpose never carries over.
                        $diffRows = [
                            ['Item Description', 'item_description', true],
                            ['Brand',            'brand',            true],
                            ['OEM ID',           'OEM_ID',           true],
                            ['UoM',              'UoM',              true],
                            ['Usage Rate Qty',   'usage_rate_qty',   true],
                            ['Usage Frequency',  'usage_frequency',  true],
                            ['Min Qty',          'min_qty',          true],
                            ['Max Qty',          'max_qty',          true],
                            ['Purpose',          'purpose',          false],
                        ];
                    @endphp
                    <table class="pa-table imf-diff">
                        <thead>
                            <tr>
                                <th style="width:22%;">Field</th>
                                <th style="width:39%;">Old Value</th>
                                <th>
                                    New Value
                                    @if (!empty(optional($new)->file_path))
                                        &nbsp;<a href="#" class="download-link" data-file="{{ $new->file_path }}" style="text-transform:none; letter-spacing:0;">(View Attachment)</a>
                                    @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($diffRows as [$label, $field, $carryOver])
                                @php
                                    $rawOld = $old ? $old->{$field} : '';
                                    $rawNew = $new ? $new->{$field} : '';

                                    if ($carryOver) {
                                        $oldCell = $old ? ($rawOld != '' ? $rawOld : $rawNew) : '';
                                        $newCell = ($old && $rawOld == '') ? '' : $rawNew;
                                    } else {
                                        $oldCell = $rawOld;
                                        $newCell = $rawNew;
                                    }
                                @endphp
                                <tr>
                                    <th class="rowlabel">{{ $label }}</th>
                                    <td class="old">{{ $oldCell !== '' && $oldCell !== null ? $oldCell : '—' }}</td>
                                    <td class="new">{{ $newCell !== '' && $newCell !== null ? $newCell : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="pa-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                @if ($showStockCodeColumn)
                                    <th style="min-width:110px;">Stock Code</th>
                                @endif
                                <th style="min-width:280px;">Item Description</th>
                                <th style="min-width:120px;">Brand</th>
                                <th style="min-width:110px;">OEM ID</th>
                                <th style="min-width:70px;">UoM</th>
                                <th style="min-width:100px;">Usage Rate</th>
                                <th style="min-width:110px;">Frequency</th>
                                <th style="min-width:80px;">Min Qty</th>
                                <th style="min-width:80px;">Max Qty</th>
                                <th style="min-width:200px;">Purpose</th>
                                <th style="width:70px; text-align:center;">File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr>
                                    <td><span class="row-num">{{ $index + 1 }}</span></td>
                                    @if ($showStockCodeColumn)
                                        <td class="mono">{{ $item->stock_code !== "null" ? $item->stock_code : '' }}</td>
                                    @endif
                                    <td style="font-weight:500;">{{ $item->item_description }}</td>
                                    <td>{{ $item->brand }}</td>
                                    <td>{{ $item->OEM_ID }}</td>
                                    <td>{{ $item->UoM }}</td>
                                    <td>{{ $item->usage_rate_qty }}</td>
                                    <td>{{ $item->usage_frequency }}</td>
                                    <td>{{ $item->min_qty }}</td>
                                    <td>{{ $item->max_qty }}</td>
                                    <td>{{ $item->purpose }}</td>
                                    <td style="text-align:center;">
                                        @if (!empty($item->file_path))
                                            <a href="#" class="download-link" data-file="{{ $item->file_path }}" title="View attachment">
                                                <i class="fa fa-file-o"></i>
                                            </a>
                                        @else
                                            <span style="color:var(--pa-text-light);">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $showStockCodeColumn ? 12 : 11 }}">
                                        <div style="padding:40px; text-align:center; color:var(--pa-text-light);">
                                            <i class="fa fa-inbox" style="font-size:28px; display:block; margin-bottom:10px; opacity:0.4;"></i>
                                            <p style="margin:0; font-size:13px;">No items found for this request.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Remarks --}}
    <div class="row">
        <div class="col-lg-6">
            <div class="pa-card">
                <div class="pa-card-header">
                    <div class="card-icon"><i class="fa fa-reply-all"></i></div>
                    <div><h6>Planner Remarks</h6><p>Sent to the department user on hold</p></div>
                </div>
                <div class="pa-card-body">
                    <textarea rows="4" class="pa-textarea" readonly placeholder="No remarks entered.">{{ $request->note_planner }}</textarea>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="pa-card">
                <div class="pa-card-header">
                    <div class="card-icon"><i class="fa fa-check-square-o"></i></div>
                    <div><h6>Planning Supervisor Remarks</h6><p>Approval, hold or rejection notes</p></div>
                </div>
                <div class="pa-card-body">
                    <textarea rows="4" class="pa-textarea" readonly placeholder="No remarks entered.">{{ $request->note_verifier }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Action bar --}}
    @if ($canPlannerAct || $canApproverAct)
        <form id="imfActionForm" method="POST" action="{{ route('imf.action', $request->id) }}">
            @csrf
            <input type="hidden" name="type" value="{{ $request->type }}">
            <input type="hidden" name="action" id="imfActionType">
            <input type="hidden" name="remarks" id="imfActionRemarks">
        </form>
        <div class="pa-action-bar">
            <button type="button" class="btn-pa btn-pa-success" onclick="imfApprove()"><i class="fa fa-thumbs-up"></i> {!! $approveLabel !!}</button>
            <button type="button" class="btn-pa btn-pa-warning" onclick="imfRemark('hold')"><i class="fa fa-undo"></i> {{ $holdLabel }}</button>
            <div class="spacer"></div>
            <button type="button" class="btn-pa btn-pa-danger" onclick="imfRemark('reject')"><i class="fa fa-times"></i> Reject</button>
        </div>
    @endif

    @include('admin.components._document-history', [
        'histories' => $request->histories,
        'title'     => 'IMF History Log',
        'subtitle'  => 'Every change made to this IMF request',
    ])
</div>
@endsection


@section('pagejs')
<script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
<script>
    function imfSubmit(action, remarks) {
        document.getElementById('imfActionType').value = action;
        document.getElementById('imfActionRemarks').value = remarks || '';
        document.getElementById('imfActionForm').submit();
    }
    function imfApprove() {
        Swal.fire({
            title: 'Approve this IMF?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: 'Yes, approve'
        }).then(function (r) { if (r.isConfirmed) imfSubmit('approve', ''); });
    }
    function imfRemark(action) {
        var title = action === 'hold' ? 'Hold &amp; Return' : 'Reject Request';
        Swal.fire({
            title: title,
            input: 'textarea',
            inputLabel: 'Remarks (required)',
            inputPlaceholder: 'Enter the reason...',
            showCancelButton: true,
            confirmButtonColor: action === 'hold' ? '#d97706' : '#dc2626',
            confirmButtonText: action === 'hold' ? 'Hold' : 'Reject',
            inputValidator: function (v) { if (!v || !v.trim()) return 'Remarks are required.'; }
        }).then(function (r) { if (r.isConfirmed) imfSubmit(action, r.value); });
    }

    $(document).ready(function() {
        // Per-item attachment download (same endpoint the department screen uses).
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

        $('#printDetails').click(function(e) {
            e.preventDefault();

            var orderNumber = $(this).attr('data-order');

            $.ajax({
                url: "{{route('imf.generate_report')}}",
                type: 'GET',
                data: { id: orderNumber },
                xhrFields: {
                    responseType: 'blob'
                },
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
    });
</script>
@endsection
