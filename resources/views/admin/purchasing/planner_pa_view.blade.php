@extends('admin.layouts.app')

@section('pagetitle')
    View Purchase Advice
@endsection

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-product.css') }}" rel="stylesheet">
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
                elseif (strpos($statusLower, 'approved') !== false)  $statusClass = 'status-approved';
                elseif (strpos($statusLower, 'verif') !== false)     $statusClass = 'status-approved';
                elseif (strpos($statusLower, 'pending') !== false)   $statusClass = 'status-pending';
                $isPlanner   = $role->name === 'MCD Planner';
                $isPurchaser = $role->name === 'Purchaser';
            @endphp
            <span class="pa-status-badge {{ $statusClass }}">
                <i class="fa fa-circle"></i> {{ $status }}
            </span>
        </div>

        <form id="paForm" method="POST" action="{{ route('pa.update') }}">
            @csrf
            @method('POST')
            <input type="hidden" name="pa_id" value="{{ $paHeader->id }}">

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
                                    <div class="meta-value">{{ $paHeader->creator->name ?? 'N/A' }}</div>
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
                                    <div class="meta-value {{ !$paHeader->receiver ? 'empty' : '' }}">
                                        {{ $paHeader->receiver ? $paHeader->receiver->name : 'Unassigned' }}
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
                        <p>{{ $paHeader->details->count() }} item(s) in this purchase advice</p>
                    </div>
                </div>
                <div class="pa-card-body" style="padding: 0;">
                    <div class="pa-table-wrapper">
                        <table class="pa-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Stock Type</th>
                                    <th>Inv Code</th>
                                    <th style="min-width:180px;">Item Description</th>
                                    <th>Stock Code</th>
                                    <th>OEM No.</th>
                                    <th>UoM</th>
                                    <th style="min-width:110px;">PAR To</th>
                                    <th style="min-width:90px;">QTY To Order</th>
                                    <th style="min-width:110px;">Date Needed</th>
                                    <th style="min-width:100px;">QTY/Delivery</th>
                                    <th style="min-width:100px;">No. Deliveries</th>
                                    <th style="min-width:100px;">Class Note</th>
                                    <th style="min-width:110px;">Previous PO#</th>
                                    <th style="min-width:90px;">Priority No</th>
                                    <th style="min-width:100px;">Cost Code</th>
                                    <th style="min-width:150px;">Remarks</th>
                                    <th style="min-width:70px;">DLT</th>
                                    <th style="min-width:80px;">Open PO</th>
                                    <th style="min-width:90px;">ROF Months</th>
                                    <th style="min-width:110px;">ROF Months W/ Req</th>
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
                                    <tr>
                                        <td><span class="row-num">{{ $count }}</span>
                                            {{-- hidden fields for rof values --}}
                                            <input type="hidden" name="rof_months{{ $details->id }}"           value="{{ $details->rof_months }}">
                                            <input type="hidden" name="rof_months_w_request{{ $details->id }}" value="{{ $details->rof_months_w_request }}">
                                        </td>
                                        <td>{{ $details->product->stock_type ?? 'N/A' }}</td>
                                        <td>{{ $details->product->inv_code  ?? 'N/A' }}</td>
                                        <td style="font-weight:500;">{{ $details->product->name ?? 'N/A' }}</td>
                                        <td style="font-family:'DM Mono',monospace;font-size:12px;">{{ $details->product->code ?? 'N/A' }}</td>
                                        <td>{{ $details->product->oem  ?? 'N/A' }}</td>
                                        <td>{{ $details->product->uom  ?? 'N/A' }}</td>

                                        <td><input type="text"   name="par_to{{ $details->id }}"               value="{{ $details->par_to }}"               class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="number" name="qty_to_order{{ $details->id }}"         value="{{ $details->qty_to_order }}"         class="form-control" {{ !$isPlanner ? 'readonly' : '' }} required></td>
                                        <td><input type="text"   name="date_needed{{ $details->id }}"          value="{{ $details->date_needed }}"          class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="number" name="qty_per_delivery{{ $details->id }}"     value="{{ $details->qty_per_delivery }}"     class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="number" name="number_of_deliveries{{ $details->id }}" value="{{ $details->number_of_deliveries }}" class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="class_note{{ $details->id }}"           value="{{ $details->class_note }}"           class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="previous_po{{ $details->id }}"          value="{{ $details->previous_po }}"          class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="priority_no{{ $details->id }}"          value="{{ $details->priority_no }}"          class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="cost_code{{ $details->id }}"            value="{{ $details->cost_code }}"            class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="text"   name="remarks{{ $details->id }}"              value="{{ $details->remarks }}"              class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        <td><input type="number" name="dlt{{ $details->id }}"                  value="{{ $details->dlt }}"                  class="form-control" {{ !$isPlanner ? 'readonly' : '' }} step="0.01"></td>
                                        <td><input type="text"   name="open_po{{ $details->id }}"              value="{{ $details->open_po }}"              class="form-control" {{ !$isPlanner ? 'readonly' : '' }}></td>
                                        {{-- ROF values displayed readonly --}}
                                        <td><input type="number" value="{{ $details->rof_months }}"           class="form-control" step="0.01" readonly style="background:#f8fafc;"></td>
                                        <td><input type="number" value="{{ $details->rof_months_w_request }}" class="form-control" step="0.01" readonly style="background:#f8fafc;"></td>

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
                                    <tr>
                                        <td colspan="21">
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
                <div class="col-lg-6">
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
                <div class="col-lg-6">
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
                @if ($role->name == 'MCD Verifier')
                    @if ($paHeader->verified_at)
                        <span class="btn-done"><i class="fa fa-check-circle"></i> Verified</span>
                    @else
                        <button type="button" id="verifyBtn" class="btn-pa btn-pa-success"><i class="fa fa-check"></i> Verify</button>
                    @endif
                @endif

                @if ($role->name == 'MCD Approver')
                    @if ($paHeader->approved_at)
                        <span class="btn-done"><i class="fa fa-check-circle"></i> Approved</span>
                    @else
                        <button type="button" id="approveBtn" class="btn-pa btn-pa-success"><i class="fa fa-thumbs-up"></i> Approve</button>
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
                @endif

                @if ($role->name == 'MCD Approver' || $role->name === 'Purchasing Officer' || $isPurchaser)
                    <button type="button" id="cancelBtn" class="btn-pa btn-pa-danger"><i class="fa fa-times"></i> Cancel PA</button>
                @endif

                <a href="{{ route('planner_pa.index') }}" class="btn-pa btn-pa-secondary" style="margin-left: auto;">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
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
@endsection

@section('customjs')
    <script>
        $(document).ready(function() {
            $('#verifyBtn').click(function(e) {
                e.preventDefault();
                window.location.href = "{{ route('pa.purchase_action', ['action' => 'verify', 'id' => $paHeader->id]) }}";
            });
            $('#approveBtn').click(function(e) {
                e.preventDefault();
                window.location.href = "{{ route('pa.purchase_action', ['action' => 'approve', 'id' => $paHeader->id]) }}";
            });
            $('#assignBtn').click(function(e) {
                e.preventDefault();
                var note = encodeURIComponent($('#purchasers').val());
                window.location.href = "{{ route('pa.purchase_action', ['action' => 'assign', 'id' => $paHeader->id]) }}&note=" + note;
            });
            $('#receiveBtn').click(function(e) {
                e.preventDefault();
                window.location.href = "{{ route('pa.purchase_action', ['action' => 'receive', 'id' => $paHeader->id]) }}";
            });
            $('#cancelBtn').click(function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to cancel this Purchase Advice?')) {
                    window.location.href = "{{ route('pa.purchase_action', ['action' => 'cancel', 'id' => $paHeader->id]) }}";
                }
            });
        });
    </script>
@endsection