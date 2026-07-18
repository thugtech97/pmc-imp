@extends('admin.layouts.app')

@section('pagetitle')
    View Purchase Advice
@endsection

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/select2/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-product.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('lib/sweetalert2/sweetalert.min.css') }}" type="text/css">
    <script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --pa-bg: #f4f6f9; --pa-white: #ffffff; --pa-border: #e2e8f0;
            --pa-border-dark: #cbd5e1; --pa-text: #1e293b; --pa-text-muted: #64748b;
            --pa-text-light: #94a3b8; --pa-primary: #1d4ed8; --pa-primary-light: #eff6ff;
            --pa-primary-hover: #1e40af; --pa-success: #059669; --pa-success-light: #ecfdf5;
            --pa-warning: #d97706; --pa-warning-light: #fffbeb; --pa-danger: #dc2626;
            --pa-danger-light: #fef2f2; --pa-shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --pa-radius: 10px; --pa-radius-sm: 6px;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--pa-bg); }
        .pa-page-header { margin-bottom: 28px; }
        .pa-page-header .breadcrumb { background: none; padding: 0; margin-bottom: 6px; font-size: 12px; }
        .pa-page-header .breadcrumb-item a { color: var(--pa-text-muted); text-decoration: none; }
        .pa-page-header .breadcrumb-item.active { color: var(--pa-primary); font-weight: 500; }
        .pa-page-header h4 { font-size: 22px; font-weight: 600; color: var(--pa-text); letter-spacing: -0.4px; margin: 0; }

        .pa-status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
        .pa-status-badge.status-pending   { background: var(--pa-warning-light); color: var(--pa-warning); border: 1px solid #fde68a; }
        .pa-status-badge.status-approved  { background: var(--pa-success-light); color: var(--pa-success); border: 1px solid #a7f3d0; }
        .pa-status-badge.status-cancelled { background: var(--pa-danger-light);  color: var(--pa-danger);  border: 1px solid #fecaca; }
        .pa-status-badge.status-default   { background: var(--pa-primary-light); color: var(--pa-primary); border: 1px solid #bfdbfe; }

        .pa-card { background: var(--pa-white); border: 1px solid var(--pa-border); border-radius: var(--pa-radius); box-shadow: var(--pa-shadow-sm); margin-bottom: 20px; overflow: hidden; }
        .pa-card-header { padding: 16px 22px; border-bottom: 1px solid var(--pa-border); display: flex; align-items: center; gap: 10px; background: #fafbfd; }
        .pa-card-header .card-icon { width: 32px; height: 32px; border-radius: 8px; background: var(--pa-primary-light); display: flex; align-items: center; justify-content: center; color: var(--pa-primary); font-size: 14px; flex-shrink: 0; }
        .pa-card-header h6 { margin: 0; font-size: 13px; font-weight: 600; color: var(--pa-text); }
        .pa-card-header p  { margin: 0; font-size: 11.5px; color: var(--pa-text-muted); }
        .pa-card-body { padding: 22px; }

        .pa-number-display { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: var(--pa-primary-light); border: 1px solid #bfdbfe; border-radius: var(--pa-radius-sm); }
        .pa-number-display .pa-number-icon { color: var(--pa-primary); font-size: 16px; }
        .pa-number-display .pa-number-value { font-family: 'DM Mono', monospace; font-size: 14px; font-weight: 500; color: var(--pa-primary); }

        .pa-meta-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; }
        .pa-meta-item .meta-label { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--pa-text-light); margin-bottom: 4px; }
        .pa-meta-item .meta-value { font-size: 13px; font-weight: 500; color: var(--pa-text); }
        .pa-meta-item .meta-value.empty { color: var(--pa-text-light); font-style: italic; font-weight: 400; }

        .pa-label { font-size: 12px; font-weight: 600; color: var(--pa-text); letter-spacing: 0.3px; text-transform: uppercase; margin-bottom: 7px; display: block; }
        .pa-textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--pa-border-dark); border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13.5px; color: var(--pa-text); background: var(--pa-white); resize: vertical; min-height: 90px; outline: none; transition: border-color 0.15s; }
        .pa-textarea:focus { border-color: var(--pa-primary); box-shadow: 0 0 0 3px rgba(29,78,216,0.1); }
        .pa-textarea[readonly] { background: #f8fafc; color: var(--pa-text-muted); }

        .pa-table-wrapper { overflow: auto; }
        .pa-table { width: 100%; border-collapse: collapse; font-size: 12.5px; margin: 0; }
        .pa-table thead tr { background: #f1f5f9; }
        .pa-table thead th { padding: 10px 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--pa-text-muted); border-bottom: 1px solid var(--pa-border); border-right: 1px solid var(--pa-border); white-space: nowrap; }
        .pa-table thead th:last-child { border-right: none; }
        .pa-table thead th.purchaser-col { background: #fefce8; color: var(--pa-warning); }
        .pa-table tbody tr { border-bottom: 1px solid var(--pa-border); transition: background 0.1s; }
        .pa-table tbody tr:hover { background: #fafbff; }
        .pa-table tbody td { padding: 8px 10px; color: var(--pa-text); border-right: 1px solid var(--pa-border); vertical-align: middle; }
        .pa-table tbody td:last-child { border-right: none; }
        .pa-table tbody td.purchaser-col { background: #fefce8; }

        .row-num { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--pa-primary-light); color: var(--pa-primary); border-radius: 50%; font-size: 11px; font-weight: 600; }

        .pa-table .form-control { height: 32px; padding: 0 8px; font-size: 12.5px; border: 1px solid var(--pa-border-dark); border-radius: 4px; font-family: 'DM Sans', sans-serif; min-width: 80px; }
        .pa-table .form-control:focus { border-color: var(--pa-primary); box-shadow: 0 0 0 2px rgba(29,78,216,0.1); outline: none; }
        .pa-table .form-control[readonly] { background: #f8fafc; color: var(--pa-text-muted); cursor: default; }

        .doc-list { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 8px; }
        .doc-list li a { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px; background: var(--pa-primary-light); border: 1px solid #bfdbfe; border-radius: var(--pa-radius-sm); color: var(--pa-primary); font-size: 12.5px; font-weight: 500; text-decoration: none; transition: all 0.15s; }
        .doc-list li a:hover { background: #dbeafe; }

        .pa-select { width: 100%; height: 38px; padding: 0 12px; border: 1px solid var(--pa-border-dark); border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13.5px; color: var(--pa-text); background: var(--pa-white); outline: none; }
        .pa-select:focus { border-color: var(--pa-primary); box-shadow: 0 0 0 3px rgba(29,78,216,0.1); }

        .pa-action-bar { background: var(--pa-white); border: 1px solid var(--pa-border); border-radius: var(--pa-radius); padding: 16px 22px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; box-shadow: var(--pa-shadow-sm); }

        .btn-pa { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s; text-decoration: none; border: none; }
        .btn-pa:hover { transform: translateY(-1px); text-decoration: none; }
        .btn-pa:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
        .btn-pa-primary   { background: var(--pa-primary); color: white; }
        .btn-pa-primary:hover { background: var(--pa-primary-hover); color: white; box-shadow: 0 4px 12px rgba(29,78,216,0.25); }
        .btn-pa-success   { background: var(--pa-success); color: white; }
        .btn-pa-success:hover { background: #047857; color: white; box-shadow: 0 4px 12px rgba(5,150,105,0.25); }
        .btn-pa-danger    { background: var(--pa-danger); color: white; }
        .btn-pa-danger:hover { background: #b91c1c; color: white; }
        .btn-pa-secondary { background: var(--pa-white); color: var(--pa-text-muted); border: 1px solid var(--pa-border-dark); }
        .btn-pa-secondary:hover { background: #f8fafc; color: var(--pa-text); }
        .btn-pa-warning { background: var(--pa-warning); color: white; }
        .btn-pa-warning:hover { background: #b45309; color: white; }
        .btn-done { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; background: var(--pa-success-light); color: var(--pa-success); border: 1px solid #a7f3d0; cursor: default; }
    </style>
@endsection

@section('content')
    <div class="container-fluid" style="max-width: 1600px;">

        <div class="pa-page-header d-flex align-items-start justify-content-between">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">IMP</a></li>
                        <li class="breadcrumb-item active">Purchase Advice</li>
                        <li class="breadcrumb-item active">View</li>
                    </ol>
                </nav>
                <h4>View Purchase Advice</h4>
            </div>
            @php
                $status      = $paHeader->status ?? '';
                $statusLower = strtolower($status);
                $statusClass = 'status-default';
                if (strpos($statusLower, 'cancel') !== false)        $statusClass = 'status-cancelled';
                elseif (strpos($statusLower, 'hold') !== false)      $statusClass = 'status-pending';
                elseif (strpos($statusLower, 'approved') !== false)  $statusClass = 'status-approved';
                elseif (strpos($statusLower, 'verif') !== false)     $statusClass = 'status-approved';
                elseif (strpos($statusLower, 'pending') !== false)   $statusClass = 'status-pending';
                $isPlanner   = $role->name === 'MCD Planner';
                $isVerifier  = (int) $role->id === 7 || $role->name === 'MCD Verifier';
                $isApprover  = $role->name === 'MCD Approver';
                $isPurchaser = $role->name === 'Purchaser';
                $isSr        = !optional($paHeader->mrs)->order_number; // PA SR = no linked MRS number
                // Held + still tied to a purchaser + not yet received = returned by the purchaser/canvasser.
                // When the planner re-edits this one, it bypasses verify/approve/assign and goes straight back.
                $returnedByPurchaser = strpos($paHeader->status, 'HOLD') !== false
                    && $paHeader->received_by
                    && !$paHeader->received_at;
                // Planner may add/remove SR items while it is for verification or held for re-edit.
                $canEditItems = $isPlanner
                    && $isSr
                    && in_array($paHeader->status, [
                        'APPROVED (MCD PLANNER) - FOR VERIFICATION',
                        'HOLD (For MCD Planner re-edit)',
                    ], true);
            @endphp
            <div class="d-flex flex-column align-items-end" style="gap:8px;">
                @if ($isSr)
                    <button type="button" id="printPaBtn" class="btn btn-sm btn-info" data-pa-number="{{ $paHeader->pa_number }}">
                        <i class="fa fa-print"></i> Print PA
                    </button>
                @endif
                <span class="pa-status-badge {{ $statusClass }}">
                    <i class="fa fa-circle"></i> {{ $status }}
                </span>
            </div>
        </div>

        <form id="paForm" method="POST" action="{{ route('pa.update') }}">
            @csrf
            @method('POST')
            <input type="hidden" name="pa_id" value="{{ $paHeader->id }}">

            @if ($returnedByPurchaser)
                <div class="alert alert-warning d-flex align-items-start" style="border-left:5px solid #f59e0b; border-radius:10px; gap:10px;">
                    <i class="fa fa-reply-all" style="font-size:20px; color:#d97706; margin-top:2px;"></i>
                    <div>
                        <strong style="text-transform:uppercase; letter-spacing:.3px;">Returned by Canvasser/Purchaser for re-edit</strong>
                        <div style="font-size:13px; margin-top:2px;">
                            {{ $paHeader->purchaser->name ?? 'The purchaser' }} sent this PA back.
                            @if ($isPlanner)
                                After you edit and click <strong>Update</strong>, it will go <strong>straight back</strong> to {{ $paHeader->purchaser->name ?? 'the purchaser' }} for canvass &mdash; skipping verification, approval, and re-assignment.
                            @endif
                        </div>
                        @if (!empty($paHeader->purchaser_remarks))
                            <div style="margin-top:6px; font-size:13px;"><strong>Reason:</strong> {{ $paHeader->purchaser_remarks }}</div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- PA Info Row --}}
            <div class="row">
                <div class="col-lg-4">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-file-text-o"></i></div>
                            <div><h6>PA Reference</h6><p>Purchase advice details</p></div>
                        </div>
                        <div class="pa-card-body">
                            <label class="pa-label">PA Number</label>
                            <div class="pa-number-display">
                                <i class="fa fa-hashtag pa-number-icon"></i>
                                <span class="pa-number-value">{{ $paHeader->pa_number }}</span>
                            </div>
                            <input type="hidden" name="pa_number" value="{{ $paHeader->pa_number }}">
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-info-circle"></i></div>
                            <div><h6>PA Timeline</h6><p>Approval and processing status</p></div>
                        </div>
                        <div class="pa-card-body">
                            <div class="pa-meta-grid">
                                <div class="pa-meta-item">
                                    <div class="meta-label">Created By</div>
                                    <div class="meta-value">{{ $paHeader->planner->name ?? 'N/A' }}</div>
                                </div>
                                <div class="pa-meta-item">
                                    <div class="meta-label">Created At</div>
                                    <div class="meta-value">{{ $paHeader->created_at ? \Carbon\Carbon::parse($paHeader->created_at)->format('M d, Y') : '—' }}</div>
                                </div>
                                <div class="pa-meta-item">
                                    <div class="meta-label">Verified At</div>
                                    <div class="meta-value {{ !$paHeader->verified_at ? 'empty' : '' }}">
                                        {{ $paHeader->verified_at ? \Carbon\Carbon::parse($paHeader->verified_at)->format('M d, Y') : 'Not yet verified' }}
                                    </div>
                                </div>
                                <div class="pa-meta-item">
                                    <div class="meta-label">Approved At</div>
                                    <div class="meta-value {{ !$paHeader->approved_at ? 'empty' : '' }}">
                                        {{ $paHeader->approved_at ? \Carbon\Carbon::parse($paHeader->approved_at)->format('M d, Y') : 'Not yet approved' }}
                                    </div>
                                </div>
                                <div class="pa-meta-item">
                                    <div class="meta-label">Received At</div>
                                    <div class="meta-value {{ !$paHeader->received_at ? 'empty' : '' }}">
                                        {{ $paHeader->received_at ? \Carbon\Carbon::parse($paHeader->received_at)->format('M d, Y') : 'Not yet received' }}
                                    </div>
                                </div>
                                <div class="pa-meta-item">
                                    <div class="meta-label">Assigned To</div>
                                    <div class="meta-value {{ !$paHeader->purchaser ? 'empty' : '' }}">
                                        {{ $paHeader->purchaser ? $paHeader->purchaser->name : 'Unassigned' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="pa-card">
                <div class="pa-card-header">
                    <div class="card-icon"><i class="fa fa-list"></i></div>
                    <div>
                        <h6>Items</h6>
                        <p><span id="itemCount">{{ $paHeader->details->count() }}</span> item(s) in this purchase advice</p>
                    </div>
                </div>
                <div class="pa-card-body" style="padding: 0;">
                    @if ($canEditItems)
                        <div style="padding:14px 16px; border-bottom:1px solid var(--pa-border); display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                            <label style="font-size:12px; font-weight:700; color:var(--pa-text-muted); text-transform:uppercase; letter-spacing:.4px; margin:0; white-space:nowrap;">
                                <i class="fa fa-plus-circle" style="color:var(--pa-success);"></i> Add Item
                            </label>
                            <select id="addItemSelect" style="min-width:340px; flex:1;"></select>
                            <span style="font-size:12px; color:var(--pa-text-light);">Search by description or stock code, then fill in the new row and click <strong>Update</strong>.</span>
                        </div>
                    @endif
                    <div class="pa-table-wrapper">
                        <table class="pa-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    @if ($paHeader->received_at)
                                        <th style="min-width:60px;" class="purchaser-col">Hold</th>
                                        <th style="min-width:170px;" class="purchaser-col">Hold Remarks</th>
                                    @endif
                                    <th>Stock Type</th>
                                    <th>Inv Code</th>
                                    <th style="min-width:380px;">Item Description</th>
                                    <th>Stock Code</th>
                                    <th>OEM No.</th>
                                    <th>UoM</th>
                                    <th style="min-width:110px;">Average Monthly UR</th>
                                    <th style="min-width:90px;">On-Hand</th>
                                    <th style="min-width:80px;">Open PO</th>
                                    <th style="min-width:110px;">PAR To</th>
                                    <th style="min-width:90px;">QTY To Order</th>
                                    <th style="min-width:110px;">Date Needed</th>
                                    <th style="min-width:100px;">QTY/Delivery</th>
                                    <th style="min-width:100px;">No. Deliveries</th>
                                    <th style="min-width:100px;">Classic Note</th>
                                    <th style="min-width:110px;">Previous PO#</th>
                                    <th style="min-width:90px;">Priority No</th>
                                    <th style="min-width:100px;">Cost Code</th>
                                    <th style="min-width:150px;">Remarks</th>
                                    <th style="min-width:70px;">DLT</th>
                                    <th style="min-width:90px;">SOH+OO</th>
                                    <th style="min-width:110px;">SOH+OO+QO</th>
                                    @if ($paHeader->received_at)
                                        <th style="min-width:110px;" class="purchaser-col">Current PO#</th>
                                        <th style="min-width:130px;" class="purchaser-col">PO Date Released</th>
                                        <th style="min-width:100px;" class="purchaser-col">QTY Ordered</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php $count = 0; @endphp
                                @forelse($paHeader->details as $details)
                                    @php $count++; @endphp
                                    <tr data-detail-id="{{ $details->id }}">
                                        <td>
                                            <span class="row-num">{{ $count }}</span>
                                            @if ($canEditItems)
                                                <button type="button" class="btn-item-delete" data-id="{{ $details->id }}" title="Remove this item"
                                                    style="display:block; margin-top:6px; border:none; background:#fef2f2; color:#dc2626; border-radius:6px; padding:3px 7px; cursor:pointer;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                            {{-- hidden fields for rof values --}}
                                            <input type="hidden" name="rof_months{{ $details->id }}"           value="{{ $details->rof_months }}">
                                            <input type="hidden" name="rof_months_w_request{{ $details->id }}" value="{{ $details->rof_months_w_request }}">
                                        </td>
                                        @if ($paHeader->received_at)
                                            <td class="purchaser-col" style="text-align:center; vertical-align:middle;">
                                                <input type="checkbox" class="pa-hold-check" data-id="{{ $details->id }}" value="1" {{ (int) $details->is_hold === 1 ? 'checked' : '' }} {{ !$isPurchaser ? 'disabled' : '' }} title="Hold this line item">
                                            </td>
                                            <td class="purchaser-col">
                                                <input type="text" class="form-control pa-hold-remarks" data-id="{{ $details->id }}" value="{{ $details->hold_remarks }}" placeholder="Hold remarks..." {{ !$isPurchaser ? 'readonly' : '' }} style="{{ (int) $details->is_hold === 1 ? '' : 'display:none;' }}">
                                            </td>
                                        @endif
                                        <td>{{ $details->product->stock_type ?? 'N/A' }}</td>
                                        <td>{{ $details->product->inv_code  ?? 'N/A' }}</td>
                                        <td style="font-weight:500;">{{ $details->product->name ?? 'N/A' }}</td>
                                        <td style="font-family:'DM Mono',monospace;font-size:12px;">{{ $details->product->code ?? 'N/A' }}</td>
                                        <td>{{ $details->product->oem  ?? 'N/A' }}</td>
                                        <td>{{ $details->product->uom  ?? 'N/A' }}</td>
                                        <td><input type="number" name="usage_rate_qty{{ $details->id }}" value="{{ $details->usage_rate_qty ?? $details->product->usage_rate_qty }}" class="form-control ur-val" step="0.01" {{ !$isPlanner ? 'readonly style="background:#f8fafc;"' : '' }}></td>
                                        <td><input type="number" name="on_hand{{ $details->id }}"        value="{{ $details->on_hand ?? $details->product->on_hand }}"               class="form-control on-hand-val" step="0.01" {{ !$isPlanner ? 'readonly style="background:#f8fafc;"' : '' }}></td>
                                        <td><input type="text"   name="open_po{{ $details->id }}"              value="{{ $details->open_po }}"              class="form-control open-po-input" {{ !$isPlanner ? 'readonly' : '' }}></td>

                                        <td><input type="text"   name="par_to{{ $details->id }}"               value="{{ $details->par_to }}"               class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="number" name="qty_to_order{{ $details->id }}"         value="{{ $details->qty_to_order }}"         class="form-control qty-order-input" {{ !$isPlanner ? 'readonly' : '' }} required></td>
                                        <td><input type="text"   name="date_needed{{ $details->id }}"          value="{{ $details->date_needed }}"          class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="qty_per_delivery{{ $details->id }}"     value="{{ $details->qty_per_delivery }}"     class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="number_of_deliveries{{ $details->id }}" value="{{ $details->number_of_deliveries }}" class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="class_note{{ $details->id }}"           value="{{ $details->class_note }}"           class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="previous_po{{ $details->id }}"          value="{{ $details->previous_po }}"          class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="priority_no{{ $details->id }}"          value="{{ $details->priority_no }}"          class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="cost_code{{ $details->id }}"            value="{{ $details->cost_code }}"            class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="remarks{{ $details->id }}"              value="{{ $details->remarks }}"              class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="number" name="dlt{{ $details->id }}"                  value="{{ $details->dlt }}"                  class="form-control" {{ !$isPlanner ? 'readonly' : '' }} step="0.01"></td>
                                        {{-- ROF values displayed readonly --}}
                                        <td><input type="number" value="{{ $details->rof_months }}"           class="form-control" step="0.01" readonly style="background:#f8fafc;"></td>
                                        <td><input type="number" value="{{ $details->rof_months_w_request }}" class="form-control rof-w-req-display" step="0.01" readonly style="background:#f8fafc;"></td>

                                        @if ($paHeader->received_at)
                                            <td class="purchaser-col">
                                                <input type="text"   name="current_po{{ $details->id }}"       value="{{ $details->current_po }}" class="form-control" {{ !$isPurchaser ? 'readonly' : '' }}>
                                            </td>
                                            <td class="purchaser-col">
                                                <input type="date"   name="po_date_released{{ $details->id }}" value="{{ $details->po_date_released ? \Carbon\Carbon::parse($details->po_date_released)->format('Y-m-d') : '' }}" class="form-control" {{ !$isPurchaser ? 'readonly' : '' }}>
                                            </td>
                                            <td class="purchaser-col">
                                                <input type="number" name="qty_ordered{{ $details->id }}"      value="{{ $details->qty_ordered }}" data-qty="{{ $details->qty_to_order }}" class="form-control qty_ordered" {{ !$isPurchaser ? 'readonly' : '' }}>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr id="itemsEmptyRow">
                                        <td colspan="{{ $paHeader->received_at ? 28 : 23 }}">
                                            <div style="padding: 40px; text-align: center; color: var(--pa-text-light);">
                                                <i class="fa fa-inbox" style="font-size:28px; display:block; margin-bottom:10px; opacity:0.4;"></i>
                                                <p style="margin:0; font-size:13px;">No items found for this purchase advice.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Remarks + Documents --}}
            <div class="row">
                <div class="col-lg-3">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-comment-o"></i></div>
                            <div><h6>Planner Remarks</h6><p>Notes or instructions for this PA</p></div>
                        </div>
                        <div class="pa-card-body">
                            <textarea rows="5" name="planner_remarks" id="planner_remarks" class="pa-textarea"
                                {{ !$isPlanner ? 'readonly' : '' }}
                                placeholder="No remarks entered.">{{ $paHeader->planner_remarks }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-reply"></i></div>
                            <div><h6>Verifier Remarks</h6><p>Revision, hold, or cancellation notes</p></div>
                        </div>
                        <div class="pa-card-body">
                            <textarea rows="5" name="verifier_remarks" id="verifier_remarks" class="pa-textarea"
                                {{ !$isVerifier ? 'readonly' : '' }}
                                placeholder="Add notes for the planner...">{{ $paHeader->verifier_remarks }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-check-square-o"></i></div>
                            <div><h6>Approver Remarks</h6><p>Approval or cancellation notes</p></div>
                        </div>
                        <div class="pa-card-body">
                            <textarea rows="5" name="approver_remarks" id="approver_remarks" class="pa-textarea"
                                {{ !$isApprover ? 'readonly' : '' }}
                                placeholder="Add approver notes...">{{ $paHeader->approver_remarks }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-reply-all"></i></div>
                            <div><h6>Purchaser Return Reason</h6><p>Why the canvasser sent it back</p></div>
                        </div>
                        <div class="pa-card-body">
                            <textarea rows="5" name="purchaser_remarks" id="purchaser_remarks" class="pa-textarea"
                                {{ !($isPurchaser && $paHeader->received_at) ? 'readonly' : '' }}
                                placeholder="{{ $isPurchaser ? 'State why you are returning this PA to the planner...' : 'No return reason entered.' }}">{{ $paHeader->purchaser_remarks }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-paperclip"></i></div>
                            <div><h6>Supporting Documents</h6><p>Attached files for this request</p></div>
                        </div>
                        <div class="pa-card-body">
                            @if (!empty($paHeader->supporting_documents))
                                <ul class="doc-list">
                                    @foreach (explode('|', $paHeader->supporting_documents) as $index => $path)
                                        <li>
                                            <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                                <i class="fa fa-file-o"></i> Document {{ $index + 1 }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p style="color: var(--pa-text-light); font-size: 13px; margin: 0; font-style: italic;">No supporting documents attached.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assign To --}}
            @if ($role->name === 'Purchasing Officer')
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div class="card-icon"><i class="fa fa-user-o"></i></div>
                        <div><h6>Assign Purchaser</h6><p>Select a purchaser to handle this PA</p></div>
                    </div>
                    <div class="pa-card-body">
                        <div class="row">
                            <div class="col-lg-4">
                                <label class="pa-label">Assign To</label>
                                <select class="pa-select" id="purchasers">
                                    @foreach ($purchasers as $purchaser)
                                        <option value="{{ $purchaser->id }}"
                                            {{ $purchaser->id == $paHeader->received_by ? 'selected' : '' }}>
                                            {{ $purchaser->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Action Bar --}}
            <div class="pa-action-bar">
                @if ($isVerifier)
                    @if ($paHeader->verified_at)
                        <span class="btn-done"><i class="fa fa-check-circle"></i> Verified</span>
                    @else
                        <button type="button" id="verifyBtn" class="btn-pa btn-pa-success"><i class="fa fa-check"></i> Verify</button>
                        <button type="button" id="holdVerifierBtn" class="btn-pa btn-pa-warning"><i class="fa fa-undo"></i> Hold for Revision</button>
                    @endif
                @endif

                @if ($isApprover)
                    @if ($paHeader->approved_at)
                        <span class="btn-done"><i class="fa fa-check-circle"></i> Approved</span>
                    @else
                        <button type="button" id="approveBtn" class="btn-pa btn-pa-success"><i class="fa fa-thumbs-up"></i> Approve</button>
                        <button type="button" id="holdApproverBtn" class="btn-pa btn-pa-warning"><i class="fa fa-undo"></i> Hold for Revision</button>
                    @endif
                @endif

                @if ($role->name === 'Purchasing Officer')
                    <button type="button" id="assignBtn" class="btn-pa btn-pa-warning"><i class="fa fa-user-plus"></i> Assign</button>
                @endif

                @if ($isPurchaser)
                    @if ($paHeader->received_at)
                        <span class="btn-done"><i class="fa fa-check-circle"></i> Received</span>
                    @else
                        <button type="button" id="receiveBtn" class="btn-pa btn-pa-success"><i class="fa fa-inbox"></i> Receive</button>
                    @endif
                @endif

                @if ($isPlanner)
                    <button type="submit" class="btn-pa btn-pa-primary"><i class="fa fa-save"></i> Update</button>
                @endif
                @if ($isPurchaser && $paHeader->received_at)
                    <button type="submit" class="btn-pa btn-pa-primary"><i class="fa fa-save"></i> Update</button>
                    <button type="button" id="returnPlannerBtn" class="btn-pa btn-pa-warning"><i class="fa fa-reply-all"></i> Return to Planner</button>
                @endif

                @if ($isVerifier || $role->name == 'MCD Approver' || $role->name === 'Purchasing Officer' || $isPurchaser)
                    <button type="button" id="cancelBtn" class="btn-pa btn-pa-danger"><i class="fa fa-times"></i> Cancel PA</button>
                @endif

                <a href="{{ route('planner_pa.index') }}" class="btn-pa btn-pa-secondary" style="margin-left: auto;">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </form>
    </div>

    {{-- Print PA (PDF/Excel) format chooser --}}
    <div class="modal effect-scale" id="printModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-alt"></i> Choose File Format</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Select a format to generate the report:</p>
                    <div class="d-flex justify-content-around">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="fileFormat" id="pdfOption" value="pdf" checked>
                            <label class="form-check-label d-flex flex-column align-items-center" for="pdfOption">
                                <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i> PDF
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="fileFormat" id="excelOption" value="excel">
                            <label class="form-check-label d-flex flex-column align-items-center" for="excelOption">
                                <i class="fas fa-file-excel fa-4x text-success mb-2"></i> Excel
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times-circle"></i> Close</button>
                    <button type="button" class="btn btn-primary" id="generateReportBtn"><i class="fas fa-download"></i> Generate Report</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ asset('lib/jqueryui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/image-upload-validation.js') }}"></script>
    <script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
@endsection

@section('customjs')
    <script>
        $(document).ready(function() {
            // ---- PA SR per-line hold (purchaser/canvasser) ----
            function savePaItemHold(id) {
                var $check   = $('.pa-hold-check[data-id="' + id + '"]');
                var $remarks = $('.pa-hold-remarks[data-id="' + id + '"]');
                $.ajax({
                    url: "{{ route('pa.hold_pa_item') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        is_hold: $check.is(':checked') ? 1 : 0,
                        hold_remarks: $remarks.val(),
                        _token: "{{ csrf_token() }}"
                    }
                });
            }

            $(document).on('change', '.pa-hold-check', function () {
                var id = $(this).data('id');
                var $remarks = $('.pa-hold-remarks[data-id="' + id + '"]');
                if ($(this).is(':checked')) { $remarks.show(); } else { $remarks.hide(); }
                savePaItemHold(id);
            });

            $(document).on('blur', '.pa-hold-remarks', function () {
                savePaItemHold($(this).data('id'));
            });

            // ---- Print PA (PDF / Excel) ----
            $('#printPaBtn').on('click', function () {
                var paNumber = $(this).data('pa-number');
                $('#printModal').modal('show');

                $('#generateReportBtn').off('click').on('click', function () {
                    var selectedFormat = $('input[name="fileFormat"]:checked').val();

                    if (selectedFormat === 'pdf') {
                        $.ajax({
                            url: "{{ route('pa.generate_report_pa') }}",
                            type: 'GET',
                            data: { paNumber: paNumber },
                            xhrFields: { responseType: 'blob' },
                            success: function (data) {
                                if (data instanceof Blob) {
                                    const pdfUrl = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }));
                                    window.open(pdfUrl, '_blank');
                                    URL.revokeObjectURL(pdfUrl);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('Error generating PDF:', error);
                            }
                        });
                    } else if (selectedFormat === 'excel') {
                        window.location.href = "{{ route('pa.generate_report_pa_sr_excel') }}?paNumber=" + encodeURIComponent(paNumber);
                    }

                    $('#printModal').modal('hide');
                });
            });

            function verifierNote() {
                return $('#verifier_remarks').length ? $('#verifier_remarks').val().trim() : '';
            }

            function approverNote() {
                return $('#approver_remarks').length ? $('#approver_remarks').val().trim() : '';
            }

            function actionUrl(action, note) {
                return "{{ route('pa.purchase_action', ['action' => '__ACTION__', 'id' => $paHeader->id]) }}"
                    .replace('__ACTION__', action)
                    + '&note=' + encodeURIComponent(note || '');
            }

            function showWarning(message) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Remarks required',
                        text: message,
                        confirmButtonColor: '#1d4ed8'
                    });
                    return;
                }

                alert(message);
            }

            function confirmAction(options, onConfirm) {
                if (window.Swal) {
                    Swal.fire({
                        icon: options.icon || 'question',
                        title: options.title,
                        text: options.text,
                        showCancelButton: true,
                        confirmButtonText: options.confirmButtonText || 'Yes, continue',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: options.confirmButtonColor || '#1d4ed8',
                        cancelButtonColor: '#64748b',
                        reverseButtons: true
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            onConfirm();
                        }
                    });
                    return;
                }

                if (confirm(options.text || options.title)) {
                    onConfirm();
                }
            }

            $('#verifyBtn').click(function(e) {
                e.preventDefault();
                confirmAction({
                    icon: 'question',
                    title: 'Verify Purchase Advice?',
                    text: 'This will forward the PA to the MCD Approver.',
                    confirmButtonText: 'Yes, verify',
                    confirmButtonColor: '#059669'
                }, function() {
                    window.location.href = actionUrl('verify', verifierNote());
                });
            });
            $('#holdVerifierBtn').click(function(e) {
                e.preventDefault();
                var note = verifierNote();
                if (!note) {
                    showWarning('Please add verifier remarks before holding this PA for revision.');
                    return;
                }
                confirmAction({
                    icon: 'warning',
                    title: 'Hold for revision?',
                    text: 'This will return the PA to the planner for correction.',
                    confirmButtonText: 'Yes, hold PA',
                    confirmButtonColor: '#d97706'
                }, function() {
                    window.location.href = actionUrl('hold-verifier', note);
                });
            });
            $('#approveBtn').click(function(e) {
                e.preventDefault();
                confirmAction({
                    icon: 'question',
                    title: 'Approve Purchase Advice?',
                    text: 'This will approve the PA for delegation.',
                    confirmButtonText: 'Yes, approve',
                    confirmButtonColor: '#059669'
                }, function() {
                    window.location.href = actionUrl('approve', approverNote());
                });
            });
            $('#holdApproverBtn').click(function(e) {
                e.preventDefault();
                var note = approverNote();
                if (!note) {
                    showWarning('Please add approver remarks before holding this PA for revision.');
                    return;
                }
                confirmAction({
                    icon: 'warning',
                    title: 'Hold for revision?',
                    text: 'This will return the PA to the planner for correction.',
                    confirmButtonText: 'Yes, hold PA',
                    confirmButtonColor: '#d97706'
                }, function() {
                    window.location.href = actionUrl('hold-approver', note);
                });
            });
            $('#assignBtn').click(function(e) {
                e.preventDefault();
                var purchaserName = $('#purchasers option:selected').text().trim();
                confirmAction({
                    icon: 'question',
                    title: 'Assign Purchase Advice?',
                    text: 'This will assign the PA to ' + purchaserName + '.',
                    confirmButtonText: 'Yes, assign',
                    confirmButtonColor: '#d97706'
                }, function() {
                    var note = encodeURIComponent($('#purchasers').val());
                    window.location.href = "{{ route('pa.purchase_action', ['action' => 'assign', 'id' => $paHeader->id]) }}&note=" + note;
                });
            });
            $('#receiveBtn').click(function(e) {
                e.preventDefault();
                confirmAction({
                    icon: 'question',
                    title: 'Receive Purchase Advice?',
                    text: 'This will mark the PA as received for canvass.',
                    confirmButtonText: 'Yes, receive',
                    confirmButtonColor: '#059669'
                }, function() {
                    window.location.href = "{{ route('pa.purchase_action', ['action' => 'receive', 'id' => $paHeader->id]) }}";
                });
            });
            $('#returnPlannerBtn').click(function(e) {
                e.preventDefault();
                var note = $('#purchaser_remarks').length ? $('#purchaser_remarks').val().trim() : '';
                if (!note) {
                    showWarning('Please state your return reason before sending this PA back to the planner.');
                    $('#purchaser_remarks').focus();
                    return;
                }
                confirmAction({
                    icon: 'warning',
                    title: 'Return to planner for re-edit?',
                    text: 'This sends the PA back to the MCD Planner. Once they re-edit it, it will come straight back to you for canvass.',
                    confirmButtonText: 'Yes, return PA',
                    confirmButtonColor: '#d97706'
                }, function() {
                    window.location.href = actionUrl('hold-purchaser', note);
                });
            });

            // ---- Add / delete SR line items (planner, while editable) ----
            @if ($canEditItems)
                function esc(s){ return $('<div>').text(s == null ? '' : String(s)).html(); }

                function renumberRows(){
                    var n = 0;
                    $('.pa-table tbody tr[data-detail-id]').each(function(){
                        n++;
                        $(this).find('.row-num').first().text(n);
                    });
                    $('#itemCount').text(n);
                }

                function appendItemRow(d){
                    var id = d.id;
                    var row = ''
                        + '<tr data-detail-id="' + id + '">'
                        +   '<td>'
                        +     '<span class="row-num"></span>'
                        +     '<button type="button" class="btn-item-delete" data-id="' + id + '" title="Remove this item" style="display:block; margin-top:6px; border:none; background:#fef2f2; color:#dc2626; border-radius:6px; padding:3px 7px; cursor:pointer;"><i class="fa fa-trash"></i></button>'
                        +     '<input type="hidden" name="rof_months' + id + '" value="">'
                        +     '<input type="hidden" name="rof_months_w_request' + id + '" value="">'
                        +   '</td>'
                        +   '<td>' + esc(d.stock_type || 'N/A') + '</td>'
                        +   '<td>' + esc(d.inv_code || 'N/A') + '</td>'
                        +   '<td style="font-weight:500;">' + esc(d.name || 'N/A') + '</td>'
                        +   '<td style="font-family:\'DM Mono\',monospace;font-size:12px;">' + esc(d.code || 'N/A') + '</td>'
                        +   '<td>' + esc(d.oem || 'N/A') + '</td>'
                        +   '<td>' + esc(d.uom || 'N/A') + '</td>'
                        +   '<td><input type="number" name="usage_rate_qty' + id + '" value="' + esc(d.usage_rate_qty) + '" class="form-control ur-val" step="0.01"></td>'
                        +   '<td><input type="number" name="on_hand' + id + '" value="' + esc(d.on_hand) + '" class="form-control on-hand-val" step="0.01"></td>'
                        +   '<td><input type="text" name="open_po' + id + '" value="" class="form-control open-po-input"></td>'
                        +   '<td><input type="text" name="par_to' + id + '" value="" class="form-control" required></td>'
                        +   '<td><input type="number" name="qty_to_order' + id + '" value="0" class="form-control qty-order-input" required></td>'
                        +   '<td><input type="text" name="date_needed' + id + '" value="" class="form-control"></td>'
                        +   '<td><input type="text" name="qty_per_delivery' + id + '" value="" class="form-control"></td>'
                        +   '<td><input type="text" name="number_of_deliveries' + id + '" value="" class="form-control"></td>'
                        +   '<td><input type="text" name="class_note' + id + '" value="" class="form-control"></td>'
                        +   '<td><input type="text" name="previous_po' + id + '" value="' + esc(d.previous_po) + '" class="form-control"></td>'
                        +   '<td><input type="text" name="priority_no' + id + '" value="" class="form-control"></td>'
                        +   '<td><input type="text" name="cost_code' + id + '" value="" class="form-control"></td>'
                        +   '<td><input type="text" name="remarks' + id + '" value="" class="form-control"></td>'
                        +   '<td><input type="number" name="dlt' + id + '" value="" class="form-control" step="0.01"></td>'
                        +   '<td><input type="number" value="" class="form-control" step="0.01" readonly style="background:#f8fafc;"></td>'
                        +   '<td><input type="number" value="" class="form-control rof-w-req-display" step="0.01" readonly style="background:#f8fafc;"></td>'
                        + '</tr>';
                    $('#itemsEmptyRow').remove();
                    $('.pa-table tbody').append(row);
                    renumberRows();
                }

                $('#addItemSelect').select2({
                    placeholder: 'Search item by description or stock code…',
                    width: '100%',
                    ajax: {
                        url: "{{ route('api.products') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function(params){ return { title: params.term, page: params.page || 1 }; },
                        processResults: function(data, params){
                            params.page = params.page || 1;
                            return {
                                results: (data.items || []).map(function(p){
                                    return { id: p.id, text: [p.code, p.name].filter(Boolean).join(' - ') };
                                }),
                                pagination: { more: (params.page * 10) < data.total_count }
                            };
                        },
                        cache: true
                    }
                }).on('select2:select', function(e){
                    var pid = e.params.data.id;
                    var $sel = $(this);
                    $.ajax({
                        url: "{{ route('pa.add_item') }}",
                        type: 'POST',
                        data: { pa_id: {{ $paHeader->id }}, product_id: pid, _token: "{{ csrf_token() }}" },
                        success: function(resp){
                            appendItemRow(resp.detail);
                            $sel.val(null).trigger('change');
                        },
                        error: function(xhr){
                            Swal.fire({ icon:'error', title:'Could not add item', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Please try again.' });
                            $sel.val(null).trigger('change');
                        }
                    });
                });

                $(document).on('click', '.btn-item-delete', function(){
                    var id = $(this).data('id');
                    var $row = $(this).closest('tr');
                    confirmAction({
                        icon: 'warning',
                        title: 'Remove this item?',
                        text: 'This will delete the line item from the purchase advice.',
                        confirmButtonText: 'Yes, remove',
                        confirmButtonColor: '#dc2626'
                    }, function(){
                        $.ajax({
                            url: "{{ route('pa.delete_item') }}",
                            type: 'POST',
                            data: { id: id, _token: "{{ csrf_token() }}" },
                            success: function(){
                                $row.remove();
                                renumberRows();
                            },
                            error: function(xhr){
                                Swal.fire({ icon:'error', title:'Could not remove item', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Please try again.' });
                            }
                        });
                    });
                });
            @endif

            $('#cancelBtn').click(function(e) {
                e.preventDefault();
                var note = verifierNote();
                @if ($isVerifier)
                    if (!note) {
                        showWarning('Please add verifier remarks before cancelling this PA.');
                        return;
                    }
                @endif
                @if ($isApprover)
                    note = approverNote();
                    if (!note) {
                        showWarning('Please add approver remarks before cancelling this PA.');
                        return;
                    }
                @endif
                confirmAction({
                    icon: 'warning',
                    title: 'Cancel Purchase Advice?',
                    text: 'This action will cancel the PA and clear approval/receiving progress.',
                    confirmButtonText: 'Yes, cancel PA',
                    confirmButtonColor: '#dc2626'
                }, function() {
                    window.location.href = actionUrl('cancel', note);
                });
            });

            // Recalculate SOH+OO+QO when ur, on-hand, open_po or qty_to_order changes
            $('#paForm').on('input', '.ur-val, .on-hand-val, .open-po-input, .qty-order-input', function() {
                var $row   = $(this).closest('tr');
                var did    = $row.data('detail-id');
                var ur     = parseFloat($row.find('.ur-val').val()) || 0;
                var onHand = parseFloat($row.find('.on-hand-val').val()) || 0;
                var openPo = parseFloat($row.find('.open-po-input').val()) || 0;
                var qtyOrd = parseFloat($row.find('.qty-order-input').val()) || 0;

                var rofWithReq = ur > 0 ? Math.round(((onHand + openPo + qtyOrd) / ur) * 100) / 100 : 0;

                $row.find('.rof-w-req-display').val(rofWithReq);
                $row.find('input[name="rof_months_w_request' + did + '"]').val(rofWithReq);
            });

            // Validate qty_ordered does not exceed qty_to_order on submit
            $('#paForm').on('submit', function(e) {
                var errors = [];
                $('.qty_ordered').each(function(index) {
                    var ordered  = parseFloat($(this).val()) || 0;
                    var maxQty   = parseFloat($(this).data('qty')) || 0;
                    if (ordered > maxQty) {
                        errors.push('Row ' + (index + 1) + ': Qty Ordered (' + ordered + ') exceeds Qty to Order (' + maxQty + ').');
                    }
                });
                if (errors.length > 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Qty Ordered',
                        html: errors.join('<br>'),
                    });
                }
            });
        });
    </script>
@endsection
