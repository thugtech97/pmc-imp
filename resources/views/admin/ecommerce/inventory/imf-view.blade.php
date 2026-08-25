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

        /* Columns the desk currently holding the IMF is expected to fill in. */
        .pa-table thead th.edit-col { background: #fefce8; color: var(--pa-warning); }
        .pa-table tbody td.edit-col { background: #fefce8; }
        .imf-empty-cell { color: var(--pa-text-light); }

        /* Stock code that is already in the item master — raised on the refused
           endorsement so the Planner sees the clashing item on this screen. */
        .imf-clash { border-top: 1px solid #fecaca; background: #fef2f2; border-left: 5px solid #dc2626; }
        .imf-clash-head { display: flex; align-items: flex-start; gap: 12px; padding: 14px 18px; }
        .imf-clash-head .fa-exclamation-triangle { font-size: 20px; color: #dc2626; margin-top: 2px; }
        .imf-clash-title { font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; color: #991b1b; }
        .imf-clash-body { font-size: 13px; color: #7f1d1d; margin-top: 3px; }
        .imf-clash-close {
            margin-left: auto; border: 0; background: transparent; color: #991b1b;
            font-size: 20px; line-height: 1; cursor: pointer; padding: 0 2px; opacity: .6;
        }
        .imf-clash-close:hover { opacity: 1; }
        .imf-clash table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        .imf-clash th {
            text-align: left; padding: 8px 12px; background: #fee2e2; color: #991b1b;
            font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: .3px;
            border-top: 1px solid #fecaca; white-space: nowrap;
        }
        .imf-clash td { padding: 9px 12px; border-top: 1px solid #fecaca; color: var(--pa-text); vertical-align: top; }
        .imf-clash td.code { font-family: monospace; font-weight: 700; color: #b91c1c; white-space: nowrap; }
        .imf-clash .muted { color: #9f1239; opacity: .75; }
        .pa-table td.edit-col input.imf-clash-input { border-color: #dc2626; background: #fff1f2; }
        .imf-clash-actions { padding: 0 18px 14px 50px; }
        .imf-clash-actions .btn-pa { font-size: 12px; padding: 7px 14px; }

        /* A line the MCD Planner ruled on: it updates the item already on file
           instead of registering a new one. The Supervisor signs on this. */
        .imf-ack {
            margin-top: 6px; padding: 6px 9px; border-radius: 6px;
            background: #fffbeb; border: 1px solid #fde68a; font-size: 11.5px; line-height: 1.45;
        }
        .imf-ack .imf-ack-head { font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: .3px; font-size: 10.5px; }
        .imf-ack .imf-ack-who { color: var(--pa-text-muted); }
        .imf-ack .imf-ack-note { color: var(--pa-text); font-style: italic; margin-top: 2px; }
    </style>
@endsection

@section('content')
@php
    $isPlanner  = $role->name === 'MCD Planner';
    // The Planning Supervisor is the final approver. The MCD Approver keeps the
    // screen for reference only — no action bar.
    $isApprover = $role->name === 'Planning Supervisor';
    $isVerifier = $role->name === 'MCD Verifier';
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

    // Where the IMF sits right now: Planner review -> MCD Verifier -> Planner
    // stock code -> Planning Supervisor. Each desk only edits its own columns.
    $atPlannerReview = $isPlanner  && in_array($request->status, \App\Constants\Status::imfPlannerReviewStage());
    $atPlannerStock  = $isPlanner  && in_array($request->status, \App\Constants\Status::imfPlannerStockCodeStage());
    $atVerifier      = $isVerifier && $request->status === \App\Constants\Status::FOR_VERIFICATION;
    $atApprover      = $isApprover && $request->status === \App\Constants\Status::APPROVED_MCD;
    $canAct          = $atPlannerReview || $atPlannerStock || $atVerifier || $atApprover;
    // Only the three working desks type into the grid; the Supervisor just approves.
    $canEditLines    = $atPlannerReview || $atVerifier || $atPlannerStock;

    if ($atPlannerReview) {
        $approveLabel = 'Endorse to MCD Verifier';
        $holdLabel    = 'Hold (return to requestor)';
    } elseif ($atVerifier) {
        $approveLabel = 'Verify &amp; Return to Planner';
        $holdLabel    = 'Hold (return to Planner)';
    } elseif ($atPlannerStock) {
        $approveLabel = 'Approve &amp; Endorse to Supervisor';
        $holdLabel    = 'Return to MCD Verifier';
    } else {
        $approveLabel = 'Approve &amp; Register';
        $holdLabel    = 'Hold (return to Planner)';
    }

    // Set when an endorsement was just refused because a stock code is taken.
    $stockCodeClashes = session('stock_code_conflicts', []);
    $clashLines = array_map(function ($clash) {
        return $clash['line'];
    }, $stockCodeClashes);

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
                @if ($isPlanner || $isApprover || $isVerifier || $isViewer)
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

    @if ($request->note_mcd_verifier)
        <div class="pa-notice notice-warning">
            <i class="fa fa-check-circle-o notice-icon"></i>
            <div>
                <div class="notice-title">MCD Verifier Remark</div>
                <div class="notice-body">{{ $request->note_mcd_verifier }}</div>
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
                                {{ $request->submitted_at ? \Carbon\Carbon::parse($request->submitted_at)->format('M d, Y h:i A') : 'Not yet submitted' }}
                            </div>
                        </div>
                        <div class="pa-meta-item">
                            <div class="meta-label">Department Head (WFS)</div>
                            <div class="meta-value {{ !$request->approved_by ? 'empty' : '' }}">
                                {{ $request->approved_by ?: 'Not yet approved' }}
                                @if ($request->dept_head_signed_at)
                                    <small class="d-block text-muted">{{ $request->dept_head_signed_at->format('M d, Y h:i A') }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="pa-meta-item">
                            <div class="meta-label">MCD Planner</div>
                            <div class="meta-value {{ !$request->planner_approved_by ? 'empty' : '' }}">
                                {{ $request->planner_approved_by ?: 'Not yet endorsed' }}
                                @if ($request->planner_reviewed_at)
                                    <small class="d-block text-muted">Reviewed: {{ $request->planner_reviewed_at->format('M d, Y h:i A') }}</small>
                                @endif
                                @if ($request->planner_stock_at)
                                    <small class="d-block text-muted">Stock code: {{ $request->planner_stock_at->format('M d, Y h:i A') }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="pa-meta-item">
                            <div class="meta-label">MCD Verifier</div>
                            <div class="meta-value {{ !$request->verifier_approved_by ? 'empty' : '' }}">
                                {{ $request->verifier_approved_by ?: 'Not yet verified' }}
                                @if ($request->verifier_signed_at)
                                    <small class="d-block text-muted">{{ $request->verifier_signed_at->format('M d, Y h:i A') }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="pa-meta-item">
                            <div class="meta-label">Planning Supervisor</div>
                            <div class="meta-value {{ !$request->approver_approved_by ? 'empty' : '' }}">
                                {{ $request->approver_approved_by ?: 'Not yet approved' }}
                                @if ($request->supervisor_signed_at)
                                    <small class="d-block text-muted">{{ $request->supervisor_signed_at->format('M d, Y h:i A') }}</small>
                                @endif
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
        <div class="col-lg-4">
            <div class="pa-card">
                <div class="pa-card-header">
                    <div class="card-icon"><i class="fa fa-reply-all"></i></div>
                    <div><h6>Planner Remarks</h6><p>Sent on hold or on a return</p></div>
                </div>
                <div class="pa-card-body">
                    <textarea rows="4" class="pa-textarea" readonly placeholder="No remarks entered.">{{ $request->note_planner }}</textarea>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="pa-card">
                <div class="pa-card-header">
                    <div class="card-icon"><i class="fa fa-check-circle-o"></i></div>
                    <div><h6>MCD Verifier Remarks</h6><p>Hold or rejection notes</p></div>
                </div>
                <div class="pa-card-body">
                    <textarea rows="4" class="pa-textarea" readonly placeholder="No remarks entered.">{{ $request->note_mcd_verifier }}</textarea>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
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

    {{-- MCD coding grid + action bar. One form: the line entries post together
         with whatever action the desk presses. --}}
    @if ($canAct)
        <form id="imfActionForm" method="POST" action="{{ route('imf.action', $request->id) }}">
        @csrf
        <input type="hidden" name="type" value="{{ $request->type }}">
        <input type="hidden" name="action" id="imfActionType">
        <input type="hidden" name="remarks" id="imfActionRemarks">
        <input type="hidden" name="stock_code_override" id="imfStockOverride" value="0">
        <input type="hidden" name="stock_code_override_note" id="imfStockOverrideNote">
    @endif

        <div class="pa-card">
            <div class="pa-card-header">
                <div class="card-icon"><i class="fa fa-barcode"></i></div>
                <div>
                    <h6>MCD Verification &amp; Coding</h6>
                    <p>
                        @if ($atPlannerReview)
                            Enter your remark per line, then endorse to the MCD Verifier
                        @elseif ($atVerifier)
                            Enter the inventory code, class and DLT of every item, then return it to the MCD Planner
                        @elseif ($atPlannerStock)
                            Enter the stock code generated in Classic per item, then endorse to the Planning Supervisor
                        @else
                            Inventory code, class, DLT and stock code per line item
                        @endif
                    </p>
                </div>
            </div>
            <div class="pa-card-body" style="padding:0;">
                <div class="pa-table-wrapper">
                    <table class="pa-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th style="min-width:240px;">Item Description</th>
                                <th class="{{ $atVerifier ? 'edit-col' : '' }}" style="min-width:130px;">Inventory Code</th>
                                <th class="{{ $atVerifier ? 'edit-col' : '' }}" style="min-width:100px;">Class</th>
                                <th class="{{ $atVerifier ? 'edit-col' : '' }}" style="min-width:100px;">DLT</th>
                                <th class="{{ $atPlannerStock ? 'edit-col' : '' }}" style="min-width:130px;">Stock Code</th>
                                <th class="{{ $atPlannerReview ? 'edit-col' : '' }}" style="min-width:200px;">Planner Remark</th>
                                <th class="{{ $atVerifier ? 'edit-col' : '' }}" style="min-width:200px;">Verifier Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                @php
                                    $lineStockCode = ($item->stock_code && $item->stock_code !== 'null') ? $item->stock_code : '';
                                @endphp
                                <tr>
                                    <td><span class="row-num">{{ $index + 1 }}</span></td>
                                    <td style="font-weight:500;">{{ $item->item_description }}</td>

                                    <td class="{{ $atVerifier ? 'edit-col' : '' }}">
                                        @if ($atVerifier)
                                            <input type="text" class="form-control" name="lines[{{ $item->id }}][inventory_code]" value="{{ $item->inventory_code }}" placeholder="Inventory code">
                                        @else
                                            <span class="mono">{{ $item->inventory_code ?: '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="{{ $atVerifier ? 'edit-col' : '' }}">
                                        @if ($atVerifier)
                                            <input type="text" class="form-control" name="lines[{{ $item->id }}][item_class]" value="{{ $item->item_class }}" placeholder="Class">
                                        @else
                                            {{ $item->item_class ?: '—' }}
                                        @endif
                                    </td>
                                    <td class="{{ $atVerifier ? 'edit-col' : '' }}">
                                        @if ($atVerifier)
                                            <input type="text" class="form-control" name="lines[{{ $item->id }}][dlt]" value="{{ $item->dlt }}" placeholder="DLT">
                                        @else
                                            {{ $item->dlt ?: '—' }}
                                        @endif
                                    </td>

                                    <td class="{{ $atPlannerStock ? 'edit-col' : '' }}">
                                        @if ($atPlannerStock)
                                            <input type="text" class="form-control {{ in_array($index + 1, $clashLines) ? 'imf-clash-input' : '' }}" name="lines[{{ $item->id }}][stock_code]" value="{{ $lineStockCode }}" placeholder="From Classic">
                                        @else
                                            <span class="mono">{{ $lineStockCode ?: '—' }}</span>
                                        @endif

                                        {{-- The Planner ruled that this code belongs to an item
                                             already on file, so the Supervisor is approving an
                                             update to it rather than a new registration. --}}
                                        @if ($item->stock_code_override)
                                            <div class="imf-ack">
                                                <div class="imf-ack-head"><i class="fa fa-exchange"></i> Will update the existing item</div>
                                                <div class="imf-ack-who">
                                                    Acknowledged by {{ $item->stock_code_override_by ?: 'the MCD Planner' }}
                                                    @if ($item->stock_code_override_at)
                                                        &middot; {{ $item->stock_code_override_at->format('M d, Y h:i A') }}
                                                    @endif
                                                </div>
                                                @if ($item->stock_code_override_note)
                                                    <div class="imf-ack-note">&ldquo;{{ $item->stock_code_override_note }}&rdquo;</div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    <td class="{{ $atPlannerReview ? 'edit-col' : '' }}">
                                        @if ($atPlannerReview)
                                            <input type="text" class="form-control" style="min-width:180px;" name="lines[{{ $item->id }}][planner_remarks]" value="{{ $item->planner_remarks }}" placeholder="Remark for this item">
                                        @else
                                            {{ $item->planner_remarks ?: '—' }}
                                        @endif
                                    </td>
                                    <td class="{{ $atVerifier ? 'edit-col' : '' }}">
                                        @if ($atVerifier)
                                            <input type="text" class="form-control" style="min-width:180px;" name="lines[{{ $item->id }}][verifier_remarks]" value="{{ $item->verifier_remarks }}" placeholder="Remark for this item">
                                        @else
                                            {{ $item->verifier_remarks ?: '—' }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div style="padding:30px; text-align:center; color:var(--pa-text-light);">
                                            <p style="margin:0; font-size:13px;">No items found for this request.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Endorsement refused: the code the Planner typed is already
                     carried by an item in the master file, so show what it
                     clashes with here rather than in a toast that vanishes. --}}
                @if (!empty($stockCodeClashes))
                    <div class="imf-clash" id="imfStockClash">
                        <div class="imf-clash-head">
                            <i class="fa fa-exclamation-triangle"></i>
                            <div>
                                <div class="imf-clash-title">
                                    Stock code already existing &mdash;
                                    {{ count($stockCodeClashes) }} {{ count($stockCodeClashes) === 1 ? 'item' : 'items' }} not endorsed
                                </div>
                                <div class="imf-clash-body">
                                    The code{{ count($stockCodeClashes) === 1 ? '' : 's' }} below
                                    {{ count($stockCodeClashes) === 1 ? 'is' : 'are' }} already used by an item in the master
                                    file. Registering under {{ count($stockCodeClashes) === 1 ? 'it' : 'them' }} would overwrite
                                    that item, so this IMF was not endorsed. Check the code generated in Classic, correct the
                                    line, then endorse again.
                                </div>
                            </div>
                            <button type="button" class="imf-clash-close" aria-label="Dismiss"
                                    onclick="$('#imfStockClash').remove();">&times;</button>
                        </div>
                        @if ($atPlannerStock)
                            {{-- A clash is either a typo or an item that is already in
                                 Classic and was raised as new by mistake. Only the
                                 Planner can tell those apart. --}}
                            <div class="imf-clash-actions">
                                <button type="button" class="btn-pa btn-pa-warning" onclick="imfOverrideStockCode()">
                                    <i class="fa fa-exchange"></i> The item already exists &mdash; update it instead
                                </button>
                            </div>
                        @endif
                        <table>
                            <thead>
                                <tr>
                                    <th>Line</th>
                                    <th>Stock Code</th>
                                    <th>Item on This IMF</th>
                                    <th>Existing Item Using the Code</th>
                                    <th>Brand</th>
                                    <th>UoM</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockCodeClashes as $clash)
                                    <tr>
                                        <td>{{ $clash['line'] }}</td>
                                        <td class="code">{{ $clash['stock_code'] }}</td>
                                        <td>
                                            {{ $clash['item_description'] ?: '—' }}
                                            @if (!empty($clash['item_brand']) || !empty($clash['item_uom']))
                                                <div class="muted">{{ $clash['item_brand'] ?: '—' }} &middot; {{ $clash['item_uom'] ?: '—' }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $clash['product_name'] ?: '—' }}</strong>
                                            @if (!empty($clash['source_imf']))
                                                <div class="muted">Registered through IMF# {{ $clash['source_imf'] }}</div>
                                            @elseif (!empty($clash['product_status']))
                                                <div class="muted">{{ $clash['product_status'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $clash['product_brand'] ?: '—' }}</td>
                                        <td>{{ $clash['product_uom'] ?: '—' }}</td>
                                        <td>{{ $clash['product_created_at'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Action bar --}}
        @if ($canAct)
            <div class="pa-action-bar">
                <button type="button" class="btn-pa btn-pa-success" onclick="imfApprove()"><i class="fa fa-thumbs-up"></i> {!! $approveLabel !!}</button>
                @if ($canEditLines)
                    <button type="button" class="btn-pa btn-pa-secondary" onclick="imfSubmit('save', '')"><i class="fa fa-save"></i> Save Entries</button>
                @endif
                <button type="button" class="btn-pa btn-pa-warning" onclick="imfRemark('hold')"><i class="fa fa-undo"></i> {{ $holdLabel }}</button>
                <div class="spacer"></div>
                <button type="button" class="btn-pa btn-pa-danger" onclick="imfRemark('reject')"><i class="fa fa-times"></i> Reject</button>
            </div>
        </form>
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
            title: @json($atPlannerReview ? 'Endorse to the MCD Verifier?' : ($atVerifier ? 'Verify and return to the MCD Planner?' : ($atPlannerStock ? 'Endorse to the Planning Supervisor?' : 'Approve this IMF?'))),
            text: @json($atVerifier ? 'The inventory code, class and DLT you entered will be saved.' : ($atPlannerStock ? 'The stock codes you entered will be saved and the requestor notified.' : '')),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: 'Yes, proceed'
        }).then(function (r) { if (r.isConfirmed) imfSubmit('approve', ''); });
    }
    // "Proceed anyway" on a taken stock code. Registering it as new is never
    // offered: products.code has no unique index, so a second row would go in
    // silently. The line is applied to the item already on file instead.
    function imfOverrideStockCode() {
        var clashes = @json(array_map(function ($clash) {
            return 'Item ' . $clash['line'] . ' — code ' . $clash['stock_code'] . ' → "' . $clash['product_name'] . '"';
        }, $stockCodeClashes));

        Swal.fire({
            title: 'Update the existing item instead?',
            html: '<div style="text-align:left; font-size:13px;">'
                + '<p>These lines will <strong>update the item already on file</strong> under that stock code. '
                + 'No new item is registered, and the details on this IMF overwrite what is there now.</p>'
                + '<ul style="padding-left:18px; margin-bottom:12px;"><li>' + clashes.join('</li><li>') + '</li></ul>'
                + '<p>The Planning Supervisor sees this decision and your reason before approving.</p></div>',
            input: 'textarea',
            inputLabel: 'Why is the existing item the right one? (required)',
            inputPlaceholder: 'e.g. Item is already registered in Classic — this IMF was raised as new by mistake.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            confirmButtonText: 'Yes, update the existing item',
            cancelButtonText: 'No, let me fix the code',
            inputValidator: function (v) { if (!v || !v.trim()) return 'A reason is required.'; }
        }).then(function (r) {
            if (!r.isConfirmed) return;
            document.getElementById('imfStockOverride').value = '1';
            document.getElementById('imfStockOverrideNote').value = r.value;
            imfSubmit('approve', '');
        });
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
