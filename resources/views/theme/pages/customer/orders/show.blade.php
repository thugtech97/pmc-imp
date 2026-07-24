@extends('theme.main')

@section('pagecss')
<style>
    .mrs-view { max-width: 1120px; margin: 0 auto; }
    .mrs-view a.back-link { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:#6b7280; text-decoration:none; margin-bottom:16px; }
    .mrs-view a.back-link:hover { color:#f6931d; }

    .mrs-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px; }
    .mrs-head h2 { margin:0; font-weight:800; letter-spacing:-.5px; color:#1f2733; }
    .mrs-head .mrs-sub { font-size:13px; color:#8a94a6; margin-top:2px; }
    .mrs-status {
        display:inline-block; padding:7px 16px; border-radius:30px; font-size:12px; font-weight:700;
        text-transform:uppercase; letter-spacing:.4px; color:#fff; white-space:nowrap;
    }
    .st-green { background:#2e9e5b; } .st-red { background:#dc3545; }
    .st-amber { background:#f0a020; } .st-grey { background:#6b7280; } .st-orange { background:#f6931d; }

    .mrs-card { background:#fff; border:1px solid #e9ecf2; border-radius:12px; box-shadow:0 1px 3px rgba(16,24,40,.05); padding:22px 24px; margin-bottom:20px; }
    .mrs-card h5 { font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#f6931d; margin:0 0 16px; }

    .mrs-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:14px 40px; }
    @media (max-width:767px){ .mrs-grid { grid-template-columns:1fr; } }
    .mrs-field { display:flex; flex-direction:column; gap:2px; }
    .mrs-field .lbl { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9aa0a6; }
    .mrs-field .val { font-size:13.5px; color:#1f2733; word-break:break-word; }
    .mrs-field .val a { color:#f6931d; }

    .mrs-note { margin:0 0 20px; padding:14px 18px; background:#fff8e1; border:1px solid #ffe082; border-left:5px solid #ff9800; border-radius:10px; }
    .mrs-note .nt { font-weight:700; text-transform:uppercase; letter-spacing:.5px; font-size:11px; color:#e65100; margin-bottom:4px; }
    .mrs-note .nb { color:#5d4037; font-size:13.5px; line-height:1.5; }

    .mrs-table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; border:1px solid #e9ecf2; border-radius:10px; }
    table.mrs-table { width:100%; border-collapse:collapse; font-size:13px; min-width:900px; }
    table.mrs-table thead th { background:#1f2733; color:#fff; padding:11px 12px; text-align:left; font-weight:600; font-size:12px; white-space:nowrap; }
    table.mrs-table tbody td { padding:10px 12px; border-bottom:1px solid #eef0f3; color:#333; vertical-align:top; }
    table.mrs-table tbody tr:last-child td { border-bottom:0; }
    table.mrs-table .purpose-row th { background:#f7f8fa; padding:8px 12px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#6b7280; border-bottom:1px solid #eef0f3; }
    table.mrs-table .purpose-row td { background:#f7f8fa; border-bottom:1px solid #eef0f3; }
    table.mrs-table .num { text-align:right; }
    table.mrs-table tr.mrs-hold-row td { background:#fff5f5; }

    .approvers-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:16px; }
    .approver { border:1px solid #e9ecf2; border-radius:10px; overflow:hidden; background:#fff; }
    .approver.pending { border-color:#ffd591; box-shadow:0 0 0 2px rgba(246,147,31,.12); }
    .approver .ap-body { padding:14px 16px; }
    .approver .ap-name { font-size:13px; font-weight:700; color:#1f2733; margin:0 0 2px; }
    .approver .ap-desig { font-size:11.5px; color:#8a94a6; font-weight:500; }
    .approver .ap-meta { font-size:11px; color:#9aa0a6; margin-top:8px; line-height:1.5; text-transform:uppercase; letter-spacing:.3px; }
    .approver .ap-status { display:block; text-align:center; color:#fff; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:7px; }
</style>
@endsection

@section('content')
@php
    $st = strtoupper($sale->status ?? '');
    if (str_contains($st, 'CANCELLED') || str_contains($st, 'REJECTED')) { $stClass = 'st-red'; }
    elseif (str_contains($st, 'HOLD')) { $stClass = 'st-amber'; }
    elseif (str_contains($st, 'COMPLETED') || str_contains($st, 'APPROVED') || str_contains($st, 'RECEIVED')) { $stClass = 'st-green'; }
    else { $stClass = 'st-grey'; }

    $paths = [];
    foreach (explode('|', (string) $sale->order_source) as $filePath) {
        if (trim($filePath) === '') { continue; }
        $paths[] = $filePath;
    }
@endphp

<div class="container">
    <div class="mrs-view">
        <a href="{{ route('profile.sales') }}" class="back-link"><i class="icon-line-arrow-left"></i> Back to MRS list</a>

        <div class="mrs-head">
            <div>
                <h2>
                    MRS No. {{ $sale->order_number }}
                    @if ($sale->revision > 0)
                        <span style="display:inline-block;vertical-align:middle;background:#f6931d;color:#fff;font-size:12px;font-weight:700;padding:2px 12px;border-radius:14px;margin-left:8px;">{{ $sale->rev_label }}</span>
                    @endif
                </h2>
                <div class="mrs-sub">Created {{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i A') }}</div>
            </div>
            <span class="mrs-status {{ $stClass }}">{{ $sale->status }}</span>
        </div>

        {{-- Notes from MCD --}}
        @if ($sale->note_planner || $sale->note_verifier)
            <div class="mrs-note">
                <div class="nt">MCD Note</div>
                <div class="nb">{!! $sale->note_planner ?: $sale->note_verifier !!}</div>
            </div>
        @endif

        {{-- Request details --}}
        <div class="mrs-card">
            <h5>Request Details</h5>
            <div class="mrs-grid">
                <div class="mrs-field"><span class="lbl">Request Date</span><span class="val">{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i A') }}</span></div>
                <div class="mrs-field"><span class="lbl">Delivery Type</span><span class="val">{{ $sale->delivery_type ?: 'N/A' }}</span></div>
                <div class="mrs-field"><span class="lbl">Department</span><span class="val">{{ auth()->user()->department->name ?? 'N/A' }}</span></div>
                <div class="mrs-field"><span class="lbl">Delivery Address</span><span class="val">{{ $sale->customer_delivery_adress ?: 'N/A' }}</span></div>
                <div class="mrs-field"><span class="lbl">Section</span><span class="val">{{ $sale->section ?: 'N/A' }}</span></div>
                <div class="mrs-field"><span class="lbl">Budgeted</span><span class="val">{{ $sale->budgeted_amount > 0 ? 'YES' : 'NO' }}</span></div>
                <div class="mrs-field"><span class="lbl">Date Needed</span><span class="val">{{ $sale->delivery_date ?: 'N/A' }}</span></div>
                <div class="mrs-field"><span class="lbl">Budgeted Amount</span><span class="val">{{ number_format((float) $sale->budgeted_amount, 2, '.', ',') }}</span></div>
                <div class="mrs-field"><span class="lbl">Requested By</span><span class="val">{{ $sale->requested_by ?: 'N/A' }}</span></div>
                <div class="mrs-field"><span class="lbl">Other Instructions</span><span class="val">{{ $sale->other_instruction ?: 'N/A' }}</span></div>
                <div class="mrs-field"><span class="lbl">Processed By</span><span class="val">{{ strtoupper($sale->user->name ?? 'N/A') }}</span></div>
                <div class="mrs-field"><span class="lbl">Note</span><span class="val">{{ $sale->purpose ?: 'N/A' }}</span></div>
                <div class="mrs-field" style="grid-column:1 / -1;">
                    <span class="lbl">Attachments</span>
                    <span class="val">
                        @forelse ($paths as $filePath)
                            <a href="{{ asset('storage/' . $filePath) }}" target="_blank" rel="noopener" style="display:block; margin-bottom:4px;"><i class="icon-line-download"></i> {{ basename($filePath) }}</a>
                        @empty
                            None
                        @endforelse
                    </span>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="mrs-card">
            <h5>Requested Items</h5>
            <div class="mrs-table-wrap">
                <table class="mrs-table">
                    <thead>
                        <tr>
                            <th>Item #</th><th>Priority</th><th>Stock Code</th><th>Item</th><th>OEM</th>
                            <th>UoM</th><th>PAR To</th><th>Frequency</th><th>Date Needed</th><th>Cost Code</th>
                            <th class="num">Req. Qty</th><th class="num">Delivered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $index => $item)
                            @php $holdClass = $item->promo_id == 1 ? 'mrs-hold-row' : ''; @endphp
                            <tr class="{{ $holdClass }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $sale->priority }}</td>
                                <td>{{ $item->product->code ?? 'NONE' }}</td>
                                <td>{{ $item->product->name ?? 'NONE' }}</td>
                                <td>{{ $item->product->oem ?? 'NONE' }}</td>
                                <td>{{ $item->product->uom ?? 'NONE' }}</td>
                                <td>{{ explode(':', (string) $item->par_to)[0] ?: 'NONE' }}</td>
                                <td>{{ $item->frequency }}</td>
                                <td>{{ $item->date_needed ? \Carbon\Carbon::parse($item->date_needed)->format('m/d/Y') : '' }}</td>
                                <td>{{ $item->cost_code }}</td>
                                <td class="num">{{ (int) $item->qty }}</td>
                                <td class="num">{{ (int) $item->qty_delivered }}</td>
                            </tr>
                            <tr class="purpose-row {{ $holdClass }}">
                                <th colspan="3">Purpose</th>
                                <td colspan="9">{{ $item->purpose }}</td>
                            </tr>
                            @if ($item->promo_id == 1)
                                <tr class="purpose-row {{ $holdClass }}">
                                    <th colspan="3">Hold Remarks</th>
                                    <td colspan="9">{{ $item->promo_description }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Approvers --}}
        @if (!empty($sale->approvers) && count($sale->approvers) > 0)
            <div class="mrs-card">
                <h5>Approvers</h5>
                <div class="approvers-grid">
                    @foreach ($sale->approvers as $approver)
                        @php
                            $isCurrentPending = ($approver['current_seq'] ?? null) == 1 && empty($approver['updated_at']);
                            $ast = strtoupper($approver['status'] ?? '');
                            $badgeBg = $ast === 'APPROVED' ? '#2e9e5b' : ($ast === 'CANCELLED' ? '#dc3545' : '#95999e');
                            $responded = !empty($approver['updated_at'])
                                ? (is_string($approver['updated_at']) ? $approver['updated_at'] : $approver['updated_at']->format('F d, Y h:i A'))
                                : '—';
                        @endphp
                        <div class="approver {{ $isCurrentPending ? 'pending' : '' }}">
                            <div class="ap-body">
                                <p class="ap-name">[{{ $approver['sequence_number'] ?? '' }}] {{ $approver['approver_name'] ?? '' }}</p>
                                <span class="ap-desig">{{ $approver['designation'] ?? '' }}</span>
                                <div class="ap-meta">Date Responded: {{ $responded }}<br>Response Aging: N/A</div>
                            </div>
                            <span class="ap-status" style="background-color:{{ $badgeBg }};">{{ $approver['status'] ?? 'PENDING' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
