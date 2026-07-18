@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('css/sweetalert.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/js-snackbar/js-snackbar.css') }}" type="text/css" />
    <link href="{{ asset('lib/select2/css/select2.min.css') }}" rel="stylesheet">

    <link href="{{ asset('css/selectize.bootstrap2.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.bootstrap3.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.default.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.legacy.css') }}" type="text/css" rel="stylesheet"/>

    <style>
        .modal-size .modal-dialog {
            max-width: 80% !important;
            width: 80% !important;
        }

        .badge {
            background-color: #38c172 !important;
            color: white;
            padding: 4px 8px;
            text-align: center;
            border-radius: 5px;
        }

        .badge2 {
            background-color: #38b8c1 !important;
            color: white;
            padding: 4px 8px;
            text-align: center;
            border-radius: 5px;
        }

        /* ============================================================
           Professional modal theme (scoped to this page)
           ============================================================ */
        .modal .modal-content {
            border: none;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(15, 23, 42, 0.28);
        }

        .modal .modal-dialog {
            margin-top: 1.75rem;
            margin-bottom: 1.75rem;
        }

        .modal .modal-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            color: #fff;
            border-bottom: none;
            padding: 1.1rem 1.5rem;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px 24px;
        }

        .modal .modal-header .modal-title,
        .modal .modal-header h4,
        .modal .modal-header h5 {
            color: #fff;
            font-weight: 700;
            letter-spacing: .3px;
            margin: 0;
        }

        .modal .modal-header small,
        .modal .modal-header small strong {
            color: rgba(255, 255, 255, 0.92);
        }

        .modal .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: .8;
            transition: opacity .15s ease, transform .15s ease;
            margin-left: auto;
        }
        .modal .modal-header .btn-close:hover { opacity: 1; transform: rotate(90deg); }

        .modal .modal-body {
            padding: 1.5rem;
            background: #fff;
        }

        .modal .modal-footer {
            background: #f8fafc;
            border-top: 1px solid #eef2f6;
            padding: .9rem 1.5rem;
        }

        .modal .modal-footer .btn,
        .modal .modal-body .btn {
            border-radius: 8px;
            padding: .5rem 1.4rem;
            font-weight: 600;
            letter-spacing: .2px;
        }

        /* Section heading inside modal body (e.g. "Approvers") */
        .modal .modal-body h5 {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin: 4px 0 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eef2f6;
        }

        /* ---- Info detail grid ---- */
        .request-details {
            display: table;
            width: 100%;
            background: #f8fafc;
            border: 1px solid #eef2f6;
            border-radius: 10px;
            padding: 14px 18px;
        }

        .request-details span {
            display: table-row;
        }

        .request-details strong {
            display: table-cell;
            padding: 5px 18px 5px 0;
            text-align: left;
            white-space: nowrap;
            color: #64748b;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: .4px;
            font-weight: 600;
            vertical-align: top;
        }

        .request-details .detail-value {
            display: table-cell;
            padding: 5px 0;
            text-align: left;
            color: #1e293b;
            font-weight: 500;
        }

        /* ---- Modal tables ---- */
        .modal .table-modal {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100%;
            font-size: 13.5px !important;
            margin: 0 !important;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 0 1px #e9edf1;
        }

        .modal .table-modal thead tr {
            background: #f1f5f9 !important;
            border: none !important;
        }

        .modal .table-modal thead th {
            background: #f1f5f9 !important;
            color: #475569 !important;
            text-transform: uppercase;
            font-size: 11px !important;
            letter-spacing: .4px;
            font-weight: 700 !important;
            border: none !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 11px 12px !important;
            white-space: nowrap;
        }

        .modal .table-modal tbody td,
        .modal .table-modal tbody th {
            border: none !important;
            border-bottom: 1px solid #eef2f6 !important;
            padding: 10px 12px !important;
            vertical-align: middle;
        }

        .modal .table-modal tbody th {
            background: #f8fafc !important;
            color: #475569 !important;
            font-size: 11px !important;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .modal .table-modal tbody tr:hover td {
            background: #f8fafc;
        }

        /* On-hold item rows: warm amber highlight with a left accent */
        .modal .table-modal tbody tr.mrs-hold-row td,
        .modal .table-modal tbody tr.mrs-hold-row th {
            background: #fff8e1 !important;
            color: #7c4a03 !important;
        }
        .modal .table-modal tbody tr.mrs-hold-row td:first-child,
        .modal .table-modal tbody tr.mrs-hold-row th:first-child {
            box-shadow: inset 4px 0 0 #ff9800;
        }
        .modal .table-modal tbody tr.mrs-hold-row:hover td,
        .modal .table-modal tbody tr.mrs-hold-row:hover th {
            background: #fff3cd !important;
        }

        .table-modal-wrap {
            overflow-x: auto;
        }

        /* ---- Approver cards ---- */
        .modal .dashboard-widget {
            border: 1px solid #eef2f6;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 16px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
            transition: box-shadow .15s ease, transform .15s ease;
        }
        .modal .dashboard-widget:hover {
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.10);
            transform: translateY(-2px);
        }
        .modal .dashboard-widget .card-body { padding: 14px 16px; }
        .modal .dashboard-widget > span:last-child {
            display: block;
            padding: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ---- Job/Cost code list ---- */
        .modal #codeList .list-group-item { border-radius: 8px; margin-bottom: 4px; cursor: pointer; }
        .modal #codeList .list-group-item:hover { background: #f1f5f9; }

        /* ---- Status pill in header ---- */
        .modal .modal-header #request_status {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .4px;
            vertical-align: middle;
        }

        /* ---- Form controls inside modals ---- */
        .modal .modal-body .form-group { margin-bottom: 1rem; }

        .modal .modal-body .form-group > label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .modal .modal-body .form-control,
        .modal .modal-body .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: .55rem .8rem;
            font-size: 14px;
            color: #1e293b;
            box-shadow: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .modal .modal-body .form-control:focus,
        .modal .modal-body .form-select:focus {
            border-color: #2c5282;
            box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.12);
            outline: none;
        }

        .modal .modal-body .form-control:disabled,
        .modal .modal-body .form-control[disabled] {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ---- Edit modal item rows: tighter, clearer ---- */
        .modal #mrs_items td { vertical-align: top; padding: 12px !important; }
        .modal #mrs_items td strong { color: #1e293b; }

        /* Cost-code input group (input + search button) */
        .modal .costcode-group {
            display: inline-flex;
            align-items: stretch;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        }
        .modal .costcode-group:focus-within {
            border-color: #2c5282;
            box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.12);
        }
        .modal .costcode-group .costcode-group-label {
            display: inline-flex;
            align-items: center;
            padding: 0 10px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            border-right: 1px solid #e2e8f0;
        }
        .modal .costcode-group .cost_code {
            width: 150px;
            border: none !important;
            padding: 6px 10px !important;
            font-size: 13px;
            color: #1e293b;
            background: transparent;
            outline: none;
        }
        .modal .costcode-search-btn {
            border: none;
            background: linear-gradient(135deg, #2c5282 0%, #1e3a5f 100%);
            color: #fff;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            transition: filter .15s ease;
        }
        .modal .costcode-search-btn:hover { filter: brightness(1.15); }
        .modal .costcode-search-btn:active { filter: brightness(.95); }
        .modal .costcode-search-btn:disabled { opacity: .45; cursor: not-allowed; filter: none; }
        .modal .costcode-search-btn .icon-search { pointer-events: none; }

        /* Remove-row (trash) button */
        .modal #mrs_items .remove-row {
            border-radius: 8px;
            padding: .4rem .6rem;
            line-height: 1;
            box-shadow: 0 2px 5px rgba(220, 38, 38, 0.25);
        }

        .modal #add_item_mrs { border-radius: 8px; font-weight: 600; padding: .45rem 1.1rem; }

        /* Highlight the row currently being added */
        .modal #mrs_items tr.add-item-row td {
            background: #f0f9ff !important;
            box-shadow: inset 0 0 0 9999px rgba(14, 165, 233, 0.03);
        }
        .modal #mrs_items tr.add-item-row td:first-child {
            box-shadow: inset 4px 0 0 #0ea5e9;
        }

        /* ---- Custom searchable product picker ---- */
        .modal .prod-picker { position: relative; }

        .modal .prod-picker-dropdown {
            display: none;
            position: fixed;
            z-index: 1090;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.20);
            max-height: 260px;
            overflow-y: auto;
            padding: 6px;
        }
        .modal .prod-picker-dropdown.show { display: block; }

        .modal .prod-picker-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .12s ease;
        }
        .modal .prod-picker-item:hover { background: #eef4fb; }

        .modal .prod-picker-code {
            flex: 0 0 auto;
            font-size: 11px;
            font-weight: 700;
            color: #1e3a5f;
            background: #e0ecf9;
            border-radius: 6px;
            padding: 2px 8px;
            letter-spacing: .3px;
            white-space: nowrap;
        }
        .modal .prod-picker-name {
            font-size: 13.5px;
            color: #1e293b;
            line-height: 1.3;
        }
        .modal .prod-picker-msg {
            padding: 14px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }
        .modal .prod-picker-input.has-selection {
            border-color: #22c55e !important;
            background: #f0fdf4;
        }

        /* Let the picker dropdown escape the edit table wrapper */
        #editdetail .table-modal-wrap { overflow: visible; }
    </style>
@endsection
@section('content')
@php
    $modals='';
@endphp

<div class="container-fluid">
    <div class="row">
		<div class="col-md-12">
            @if (Session::has('success'))
                <div class="alert alert-info" role="alert">
                    {{ Session::get('success') }}
                </div>
            @endif

            @if (Session::has('error'))
                <div class="alert alert-danger" role="alert">
                    {{ Session::get('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <!-- DASHBOARD SUMMARY -->
            <div class="row mt-4 mb-3">
                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-3">
                        <h5 class="fw-bold">Total of Newly-submitted to WFS</h5>
                        <div class="display-6 text-success">{{ $postedCount }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-3">
                        <h5 class="fw-bold">Overdue In-Progress (2+ Days)</h5>
                        <div class="display-6 text-danger">{{ $inProgressOverdue }}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-3">
                        <h5 class="fw-bold">% Overdue</h5>
                        <div class="display-6 text-primary">{{ $percentageOverdue }}%</div>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap">
                <a href="{{ route('cart.front.show') }}" class="button button-dark button-border button-circle button-xlarge fw-bold fs-14-f nols text-dark h-text-light notextshadow">Add New MRS</a>
                
                <a href="{{ route('export.users') }}?type=deptuser" class="button button-dark button-border button-circle button-xlarge fw-bold fs-14-f nols text-dark h-text-light notextshadow">Export As Excel</a>
            
                <form method="GET" action="{{ route('profile.sales') }}" class="d-flex ms-auto mt-4">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-search"></i> <!-- Font Awesome search icon -->
                    </button>
                </form>
            </div>
            

            <table id="inventoryTable" class="table table-bordered table-striped">
                <thead class="text-center">
                    <tr>
                        <th>MRS#</th>
                        <th>PA#</th>
                        <th>Created Date</th>
                        {{--  
                        <th>Posted Date</th>
                        <!--<th>Ordered</th>
                        <th>Delivered</th>
                        <th>Balance</th>!-->
                        
                        <th>Costcodes</th>
                        --}}
                        <th>Remarks</th>
                        <th>Request Status</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td class="text-center">{{$sale->order_number}}</td>
                            <td class="text-center"><span class="badge2">{{  $sale->purchaseAdvice->pa_number ?? "N/A" }}</span></td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i A') }}
                            </td>
                            {{-- 
                            <td class="text-center">{{ $sale->date_posted ? date('Y-m-d H:i:s', strtotime($sale->date_posted)) : '-' }}</td>
                            <td class="text-center">
                                @php
                                    $costcodes = $sale->items->pluck('cost_code')->unique();
                                @endphp
                                @foreach ($costcodes as $code)
                                    <span class="badge">{{ trim($code) }}</span>
                                    @if (!$loop->last)
                                        </br>
                                    @endif
                                @endforeach
                            </td>
                             --}}
                            <td class="text-center small">
                                {{ $sale->purpose }}
                            </td>
                            {{--
                            <td class="text-center">
                                <span class="{{ strpos($sale->status, 'CANCELLED') !== false ? 'text-danger' : 'text-success' }}">
                                    @if ($sale->received_at)
                                        <u><i class="icon-print"></i> 
                                        <a href="javascript:;" class="print text-success" data-order-number="{{ $sale->order_number }}">
                                            RECEIVED FOR CANVASS ({{ strtoupper($sale->purchaser->name ?? 'N/A') }})
                                        </a></u>
                                    @elseif ($sale->approved_at)
                                    <u><i class="icon-print"></i> 
                                        <a href="javascript:;" class="print text-success" data-order-number="{{ $sale->order_number }}">
                                            APPROVED BY MCD MANAGER - PA FOR DELEGATION
                                        </a></u>
                                    @else
                                        {{ strtoupper($sale->status) }}
                                    @endif
                                    @if ($sale->hasPromo())
                                        <br/>
                                        @php
                                            $hold = $sale->items->where('promo_id', 1)->count();
                                            $is_pa = $sale->items->where('promo_id', 1)->whereNotNull('is_pa')->count();
                                        @endphp
                                        @if($hold !== $is_pa)
                                            <span class="text-warning">
                                                ({{  $sale->items->where('promo_id', 1)->whereNull('is_pa')->count() }} OUT OF {{ $sale->items->count() }} ITEMS ON-HOLD)
                                            </span>
                                        @endif
                                    @endif
                                </span>
                            </td>
                            --}}
                            <td class="text-center">
    @php
        // Determine if revised
        $isRevised = str_contains($sale->status, 'REVISED MRS');

        // Overdue logic (2 days from updated_at)
        $dueDate = $sale->updated_at->copy()->addDays(2);
        $now = now();
        $isOverdue = $now->gt($dueDate);
        $overdueDays = $isOverdue ? $dueDate->diffInDays($now) : 0;

        // Text color logic
        if (strpos($sale->status, 'CANCELLED') !== false) {
            $textClass = 'text-danger';
        } elseif ($isRevised) {
            $textClass = 'text-primary';
        } elseif ($isOverdue) {
            $textClass = 'text-danger';
        } else {
            $textClass = 'text-dark';
        }
    @endphp

    <span class="{{ $textClass }} fw-bold">

        @if ($sale->received_at)
            <u>
                <i class="icon-print"></i> 
                <a href="javascript:;" 
                   class="print {{ $textClass }}" 
                   data-order-number="{{ $sale->order_number }}">
                    RECEIVED FOR CANVASS ({{ strtoupper($sale->purchaser->name ?? 'N/A') }})
                </a>
            </u>

        @elseif ($sale->approved_at)

            <u>
                <i class="icon-print"></i> 
                <a href="javascript:;" 
                   class="print {{ $textClass }}" 
                   data-order-number="{{ $sale->order_number }}">
                    APPROVED BY MCD MANAGER - PA FOR DELEGATION
                </a>
            </u>

        @else

            {{ strtoupper($sale->status) }}

        @endif

        {{-- Overdue Days --}}
        @if ($isOverdue && !$isRevised && strpos($sale->status, 'CANCELLED') === false)
            ({{ $overdueDays }} DAY{{ $overdueDays > 1 ? 'S' : '' }})
        @endif

        {{-- Promo Hold Info (UNCHANGED LOGIC) --}}
        @if ($sale->hasPromo())
            <br/>
            @php
                $hold = $sale->items->where('promo_id', 1)->count();
                $is_pa = $sale->items->where('promo_id', 1)->whereNotNull('is_pa')->count();
            @endphp
            @if($hold !== $is_pa)
                <span class="text-warning">
                    ({{ $sale->items->where('promo_id', 1)->whereNull('is_pa')->count() }} 
                    OUT OF {{ $sale->items->count() }} ITEMS ON-HOLD)
                </span>
            @endif
        @endif

    </span>
</td>                                                        
                            <td>
                                @if (!(strpos($sale->status, 'CANCELLED') !== false))
                                    <a href="#" onclick="view_items('{{$sale->id}}');" title="View Details" aria-expanded="false">
                                        <i class="icon-eye"></i>
                                    </a>
                                @endif
                                @if (!$sale->approved_at && !(strpos($sale->status, 'CANCELLED') !== false))
                                    <a href="javascript:;" onclick="cancel_unpaid_order('{{$sale->id}}')" title="Cancel MRS"><i class="icon-forbidden"></i></a>
                                @endif
                                @if ($sale->approved_at)
                                    <span class="text-success"><i class="icon-check"></i></span>
                                @endif
                                @if (strpos($sale->status, 'ON-HOLD') !== false || strpos($sale->status, 'ON HOLD') !== false)
                                    <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
                                        <i class="icon-pencil"></i>
                                    </a>
                                    <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'resubmitted']) }}" title="Resubmit"><i class="icon-refresh"></i></a>
                                @endif
                                @if ($sale->hasPromo() && !$sale->received_at && strpos($sale->status, 'APPROVED (MCD Planner)') !== false)
                                    <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
                                        <i class="icon-pencil"></i>
                                    </a>
                                    <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'resubmitted']) }}" title="Resubmit"><i class="icon-refresh"></i></a>
                                @endif
                                @if ($sale->hasPromo() && $sale->received_at)
                                    <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
                                        <i class="icon-pencil"></i>
                                    </a>
                                @endif
                                @if (strpos($sale->status, 'CANCELLED') !== false)
                                    <a href="#" onclick="view_items('{{$sale->id}}');" title="View Details" aria-expanded="false">
                                        <i class="icon-eye"></i>
                                    </a>
                                @endif

                                @switch($sale->status)
                                    @case('SAVED')
                                    @case('saved')
                                        <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
                                            <i class="icon-pencil"></i>
                                        </a>
                                        <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'submitted']) }}" title="Submit for Approval"><i class="icon-upload"></i></a>
                                        @break
                                    @case('posted')
                                        <a href="#" title="View Deliveries" onclick="view_deliveries('{{$sale->id}}');"><i class="icon-truck"></i></a>
                                        @break
                                @endswitch

                                {{--@if($sale->status == 'CANCELLED' || $sale->delivery_status == 'Delivered')
                                    <a class="dropdown-item" href="#" onclick="reorder('{{$sale->id}}')">Reorder</a>
                                @endif--}}

                                @if($sale->issuances->count() > 0)
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#issuanceDetailsModal{{ $sale->id }}" aria-expanded="false" title="View Issuances">
                                    <i class="icon-file"></i>
                                </a>
                                @endif
                            </td>
                        </tr>

                        @php
                            $paths = "";
                            foreach (explode('|', $sale->order_source) as $filePath) {
                                $paths .= '<a href="' . asset('storage/' . $filePath) . '" target="_blank" style="display: block; margin-bottom: 5px;">
                                                <i class="icon-download-alt" style="margin-right: 5px;"></i>
                                                ' . basename($filePath) . '
                                        </a>';
                            }

                            $plannerNoteHtml = '';
                            if ($sale->note_planner || $sale->note_verifier) {
                                $plannerNoteHtml = '
                                <div style="margin: 0 0 18px 0; padding: 14px 18px; background: #fff8e1; border: 1px solid #ffe082; border-left: 5px solid #ff9800; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                                    <div style="display:flex; align-items:flex-start;">
                                        <i class="icon-info-sign" style="color:#ff9800; font-size:20px; margin-right:10px; line-height:1.4;"></i>
                                        <div>
                                            <div style="font-weight:700; text-transform:uppercase; letter-spacing:0.5px; font-size:12px; color:#e65100; margin-bottom:4px;">MCD Planner Note</div>
                                            <div style="color:#5d4037; font-size:14px; line-height:1.5;">'.($sale->note_planner ?: '<em style="color:#9e9e9e;">No note provided.</em>').'</div>
                                        </div>
                                    </div>
                                </div>';
                            }

                            $modals .='
                                <div class="modal fade bs-example-modal-centered modal-size" id="viewdetail'.$sale->id.'" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post" class="m-0" action="' . route("my-account.update.order", $sale->id) . '">
                                            <input type="hidden" name="_token" value="'.csrf_token().'">
                                            <input type="hidden" name="_method" value="PUT">

                                            <div class="modal-header">
                                                <h4 class="modal-title" id="myModalLabel">MRS No. '.$sale->order_number.'</h4> 
                                                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="request-details">
                                                            <span><strong>Request Date:</strong> <span class="detail-value">'. $sale->created_at.'</span></span>
                                                            <span><strong>Request Status:</strong> <span class="detail-value">'.strtoupper($sale->status).'</span></span>
                                                            <span><strong>Department:</strong> <span class="detail-value">'.auth()->user()->department->name.'</span></span>
                                                            <span><strong>Section:</strong> <span class="detail-value">'.$sale->section.'</span></span>
                                                            <span><strong>Date Needed:</strong> <span class="detail-value">'.$sale->delivery_date.'</span></span>
                                                            <span><strong>Requested By:</strong> <span class="detail-value">'.$sale->requested_by.'</span></span>
                                                            <span><strong>Processed By:</strong> <span class="detail-value">'.strtoupper($sale->user->name).'</span></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="request-details">
                                                            <span><strong>Delivery Type:</strong> <span class="detail-value">'.$sale->delivery_type.'</span></span>
                                                            <span><strong>Delivery Address:</strong> <span class="detail-value">'.$sale->customer_delivery_adress.'</span></span>
                                                            <span><strong>Budgeted:</strong> <span class="detail-value">'.($sale->budgeted_amount > 0 ? 'YES' : 'NO').'</span></span>
                                                            <span><strong>Budgeted Amount:</strong> <span class="detail-value">'.number_format($sale->budgeted_amount, 2, '.', ',').'</span></span>
                                                            <span><strong>Other Instructions:</strong> <span class="detail-value">'.$sale->other_instruction.'</span></span>
                                                            <span><strong>Note:</strong> <span class="detail-value">'.$sale->purpose.'</span></span>
                                                            <span><strong>Attachment:</strong> 
                                                                <span class="detail-value">
                                                                    '.$paths.'
                                                                </span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="gap-20"></div>
                                                '.$plannerNoteHtml.'
                                                <div class="table-modal-wrap">
                                                    <table class="table table-md table-modal" style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px; margin: 20px 0;">
                                                        <thead>
                                                            <tr style="background-color: #f2f2f2; color: #333; border-bottom: 2px solid #ccc;">
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">ITEM #</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Priority</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Stock Code</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">OEM</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">UoM</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">PAR To</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Frequency</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Date Needed</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Cost Code</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Requested Qty</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Delivered Qty</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';

                                                                $total_qty = 0;
                                                                $total_sales = 0;
                                                                $count = 0;
                                                            foreach($sale->items as $item){
                                                                $count++;
                                                                $total_qty += $item->qty;
                                                                $total_sales += $item->qty * $item->price;
                                                                $holdClass = $item->promo_id == 1 ? 'mrs-hold-row' : '';
                                                                $hold_remarks = $item->promo_id == 1 ? '<tr class="'.$holdClass.'" style="border-bottom: 1px solid #ddd;">
                                                                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="3">HOLD REMARKS</th>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="8">'.$item->promo_description.'</td>
                                                                </tr>' : '';
                                                                $modals.='
                                                                <tr class="'.$holdClass.'" style="border-bottom: 1px solid #ddd;">
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$count.'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$sale->priority.'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.($item->product->code ?? "NONE").'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.($item->product->name ?? "NONE").'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.($item->product->oem ?? "NONE").'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.($item->product->uom ?? "NONE").'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.((explode(':', $item->par_to)[0]) ? explode(':', $item->par_to)[0] : "NONE").'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$item->frequency.'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.\Carbon\Carbon::parse($item->date_needed)->format('m/d/Y').'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$item->cost_code.'</td>
                                                                    <td style="padding: 10px; text-align: right; border: 1px solid #ddd;">'. (int)$item->qty.'</td>
                                                                    <td style="padding: 10px; text-align: right; border: 1px solid #ddd;">'. (int)$item->qty_delivered.'</td>
                                                                </tr>
                                                                <tr class="'.$holdClass.'" style="border-bottom: 1px solid #ddd;">
                                                                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="3">PURPOSE</th>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="8">'.$item->purpose.'</td>
                                                                </tr>'.$hold_remarks;
                                                                
                                                            }

                                                            $delivery_discount = \App\Models\Ecommerce\CouponSale::total_discount_delivery($sale->id);
                                                            $grossAmount = ($total_sales-$sale->discount_amount)+($sale->delivery_fee_amount-$delivery_discount);

                                                            $modals.='
                                                        </tbody>
                                                    </table>
                                                </div>
                                                    
                                                <div class="row">
                                                    <h5>Approvers</h5>
                                                </div>

                                                <div class="row">';

                                                if(!empty($sale->approvers) && count($sale->approvers) > 0){
                                                    foreach ($sale->approvers as $approver) {
                                                        $modals .= '
                                                        <div class="col-lg-4 col-md-6">
                                                            <div class="card dashboard-widget '.($approver['current_seq'] == 1  && $approver['updated_at'] == null ? 'bg-light' : '').'">
                                                                <div class="card-body">
                                                                    <h6 class="tx-bold tx-uppercase mg-b-5 lh-1">
                                                                        <i data-feather="user" class="mg-r-6"></i> ['.$approver['sequence_number'].'] '.$approver['approver_name'].' 
                                                                        <span class="tx-normal">('.$approver['designation'].')</span>
                                                                    </h6>
                                                                    <span class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold">
                                                                        Date Responded: '.(!empty($approver['updated_at']) ? $approver['updated_at']->format('F d, Y h:i A') : '').'<br> 
                                                                        Response Aging: N/A
                                                                    </span>
                                                                </div>
                                                                <span class="text-center text-white" style="background-color:'.(strtoupper($approver['status'] ?? '') === 'APPROVED' ? 'rgb(57, 134, 57)' : (strtoupper($approver['status'] ?? '') === 'CANCELLED' ? 'rgb(219, 83, 83)' : 'rgb(149, 149, 149)')).'">'.$approver['status'].'</span>
                                                            </div>
                                                        </div>';
                                                    }
                                                }

                                                $modals .= '
                                                </div>
                                                <div class="gap-20"></div>
                                            </div>
                                            <div class="modal-footer">
                                                <!--<input type="submit" class="btn btn-primary" value="Update Quantity">-->
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            ';
                        @endphp
                    @empty
                        <tr>
                            <td colspan="8">No MRS found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-end">
                {{ $sales->links() }}
            </div>
        </div>
	</div>
</div>

{!!$modals!!}

@include('theme.pages.customer.edit-mrs')
@include('theme.pages.customer.jobcostcodes-modal')

<div class="modal fade bs-example-modal-centered" id="cancel_order" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable ">
        <div class="modal-content">
            <form action="{{route('my-account.cancel-order')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this MRS?</p>
                    <input type="hidden" id="orderid" name="orderid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('pagejs')
    <script src="{{ asset('js/selectize.js') }}"></script>
    <script src="{{ asset('lib/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('lib/js-snackbar/js-snackbar.js') }}"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
	<script>
        var employees;
        $(document).ready(function(){
            $('#methods').select2({
                closeOnSelect: false,
            });
            employee_lookup();
            if (!localStorage.getItem("CC")) {
                fetch_codes("CC");
            }
            if (!localStorage.getItem("JC")) {
                fetch_codes("JC");
            }
        });

        $(document).on('hidden.bs.modal', '.modal', function () {
            if ($('.modal.show').length) {
                $('body').addClass('modal-open').css({
                    overflow: 'hidden',
                    paddingRight: (window.innerWidth - document.documentElement.clientWidth) + 'px'
                });
            } else {
                // reset when no modals are open
                $('body').css({ overflow: '', paddingRight: '' });
            }
        });

        function prettyDateTime(val){
            if(!val) return '';
            var d = new Date(val);
            if(isNaN(d.getTime())) return val;
            return d.toLocaleString('en-US', {
                year: 'numeric', month: 'short', day: '2-digit',
                hour: '2-digit', minute: '2-digit'
            });
        }

        function fetch_codes(type){
            $.ajax({
                type: 'POST',
                data: {
                    "type": type,
                    "_token": "{{ csrf_token() }}",
                },
                url: "{{ route('code.fetch_codes') }}",
                success: function(data){
                    let values;
                    if(type === "CC"){
                        values = data.map(item => item.Full_GL_Codes).join(',');
                    } else {
                        values = data.map(item => item.FULL_JOB_CODE).join(',');
                    }
                    localStorage.setItem(type, values);
                }
            });
        }
        function employee_lookup() {
            if (localStorage.getItem("EMP") !== null) {
                let values = localStorage.getItem("EMP");
                employees = values;
                initEmpValues(employees.split("|"));
            }else{
                $.ajax({
                    type: 'GET',
                    url: "{{ route('users.employee_lookup') }}",
                    success: function(data){
                        try {
                            var employeesArray = JSON.parse(data);
                            let values = employeesArray.map(item => item.fullnamewithdept).join('|');
                            //console.log(values);
                            employees = values;
                            localStorage.setItem("EMP", employees);
                            initEmpValues(employees.split("|"))
                        } catch (e) {
                            console.error("Error parsing JSON: ", e);
                        }
                    }
                });
            }
        }

        function initEmpValues(employeesArray){
            $('.employees').empty();
            $('.employees').append('<option value="" disabled selected>Select an employee</option>');
            employeesArray.forEach(function(employee) {
                var fullname = employee.split(":")[0];
                $('.employees').append('<option value="' + employee + '">' + fullname + '</option>');
            });
        }

        function changeIsbudget(id, value){
            $("#budgeted_amount"+id).val("");
            if (value == 1) {
                $('#budgetAmount'+id).css('visibility', 'visible');
            } else {
                $('#budgetAmount'+id).css('visibility', 'hidden');
            }
        }

        function view_items(salesID){
            $('#viewdetail'+salesID).modal('show');
        }

        function edit_item(salesID){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                data: {
                    "mrs": salesID,
                    "_token": "{{ csrf_token() }}",
                },
                type: "post",
                url: "{{route('mrs.getDetails')}}",
                success: function(data) {
                    
                    let headers = data.headers
                    let items = data.headers.items
                    
                    let id = headers.id;
                    let url = `{{ route('my-account.update.order', ':id') }}`;
                    url = url.replace(':id', id);
                    $('#edit_form').attr('action', url);
                    let hasPromo = data.hasPromo && !data.received_at && 
                                    !(headers.status.includes("ON HOLD") || 
                                    headers.status.includes("ON-HOLD"));
                    $("#mrs_id").val(headers.id);
                    $("#mrs_no").html(headers.order_number)
                    $("#input_mrs_no").val(headers.order_number)
                    $("#request_date").html(prettyDateTime(headers.created_at))
                    $("#request_status").html(headers.status)
                    $("#planner_note").html(headers.note_planner)
                    $("#planner_note_box").toggle(!!(headers.note_planner || headers.note_verifier))
                    $("#priority_no").val(headers.priority)
                    $("#department").val(headers.customer_name)
                    $("#purpose").val(headers.purpose)
                    $("#requested_by").val(headers.requested_by)
                    $("#date_needed").val(headers.delivery_date)
                    $("#budgeted").val(headers.budgeted_amount)
                    $("#isBudgeted").val(headers.budgeted_amount > 0 ? "1" : "0");
                    $("#section").val(headers.section)
                    $("#notes").val(headers.other_instruction)
                    initSelectize((headers.costcode || "").split(","), false);
                    $(".edit_mrs_field").prop('readonly', false);
                    $(".edit_mrs_select").off('mousedown');
                    $("#add_item_mrs").show();
                    $("#alert_purpose_resubmission").hide();

                    if (headers.order_source && headers.order_source.trim() !== "") {
                        $("#file-display").remove();
                        let filePaths = headers.order_source.split('|');
                        let fileLinksHTML = '<div id="file-display">';
                        filePaths.forEach(filePath => {
                            let fileName = filePath.split('/').pop();
                            fileLinksHTML += `
                                <div data-file="${filePath}" style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <a href="storage/${filePath}" target="_blank" style="margin-right: 10px;">
                                        <i class="icon-download-alt" style="margin-right: 5px;"></i>
                                        ${fileName}
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteFile('${filePath}')" style="font-size: 14px; padding: 1px 4px; line-height: 1;">
                                        <i class="icon-trash" style="font-size: 14px;"></i>
                                    </button>
                                </div>
                            `;
                        });

                        fileLinksHTML += '</div>';
                        $("#attachment").after(fileLinksHTML);
                    }
                    if(hasPromo){
                        $(".edit_mrs_field").prop('readonly', true);
                        $("#alert_purpose_resubmission").show();
                        $(".edit_mrs_select").on('mousedown', function(e){
                            e.preventDefault();
                        });

                    }
                    if(headers.status === "SAVED" /* || headers.received_at */){
                        $("#add_item_mrs").hide();
                    }

                    $("#mrs_items").empty();
                    items.forEach(function(item, index) {
                        let row = `<tr id="row-${item.id}" style="${hasPromo && item.promo_id == 0 ? 'background-color: #C0C0C0;' : ''}">
                                        <td>
                                            <strong>(${item.product.code}) ${item.product.name}</strong>
                                            <p class="mb-1"><small class="text-muted">(${item.uom})</small></p>
                                            <div class="costcode-group">
                                                <span class="costcode-group-label">Costcode</span>
                                                <input type="text" value="${item.cost_code}" class="cost_code" name="cost_code[${item.id}]" ${hasPromo && item.promo_id == 0 ? 'readonly' : 'readonly'} required>
                                                <button type="button" class="costcode-search-btn" title="Search cost / job code" onclick="showJobCostCode(this)" ${hasPromo && item.promo_id == 0 ? 'disabled' : ''}>
                                                    <i class="icon-search"></i>
                                                </button>
                                            </div>
                                             ${ hasPromo && item.promo_id == 1 ? '<p><b>Hold remarks:</b> '+item.promo_description+'</p>' : '' }
                                        </td>
                                        <td>
                                            <select class="form-select par_to" name="par_to[${item.id}]">
                                                <option value="N/A" selected>Select an employee</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select frequency" name="frequency[${item.id}]" required>
                                                <option value="Daily" ${item.frequency === 'Daily' ? 'selected' : ''}>Daily</option>
                                                <option value="Weekly" ${item.frequency === 'Weekly' ? 'selected' : ''}>Weekly</option>
                                                <option value="Monthly" ${item.frequency === 'Monthly' ? 'selected' : ''}>Monthly</option>
                                                <option value="Yearly" ${item.frequency === 'Yearly' ? 'selected' : ''}>Yearly</option>
                                                <option value="As Needed" ${item.frequency === 'As Needed' ? 'selected' : ''}>As Needed</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control purpose_item" name="purpose[${item.id}]" value="${item.purpose}" ${hasPromo && item.promo_id == 0 ? '' : ''} required>
                                        </td>
                                        <td>
                                            <input type="date" class="form-control" name="date_needed[${item.id}]" value="${item.date_needed.split(' ')[0]}" ${hasPromo && item.promo_id == 0 ? 'readonly' : ''} required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="qty[${item.id}]" min="1" value="${parseInt(item.qty)}" ${hasPromo && item.promo_id == 0 ? 'readonly' : ''} required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row" data-id="${item.id}" ${hasPromo && item.promo_id == 0 ? 'disabled' : ''}><i class="icon-trash"></i></button>
                                        </td>
                                    </tr>`;
                        $("#mrs_items").append(row);
                        let selectElement = $(`#row-${item.id} .par_to`); let selectElement2 = $(`#row-${item.id} .frequency`); 
                        let employeesArr = employees.split("|");
                        employeesArr.forEach(function(employee) {
                            let fullname = employee.split(":")[0];
                            let selected = (employee === item.par_to) ? 'selected' : '';
                            selectElement.append('<option value="' + employee + '" ' + selected + '>' + fullname + '</option>');
                        });
                        if(hasPromo && item.promo_id == 0){
                            selectElement.on('mousedown', function(e){ e.preventDefault(); });
                            selectElement2.on('mousedown', function(e){ e.preventDefault(); });
                        }
                        
                    });
                    $('#editdetail').modal('show');
                    
                }
            });
        }

        function add_item(){
            let costcodeOptions = ''; // Initialize an empty string for options
            let costcodes = localStorage.getItem('CC') ? localStorage.getItem('CC').split(',') : []; // Fetch and split CC
            costcodes.forEach(function(cc) {
                costcodeOptions += `<option value="${cc}">${cc}</option>`;
            });
            let row =   `<tr class="add-item-row">
                            <td>
                                <div class="prod-picker">
                                    <input type="text" class="form-control prod-picker-input" placeholder="Search product by name or code…" autocomplete="off">
                                    <input type="hidden" name="product" class="prod-picker-id">
                                    <div class="prod-picker-dropdown"></div>
                                </div>
                                <input type="hidden" value="${$('#mrs_id').val()}" name="mrs_id">
                                <div class="costcode-group mt-2">
                                    <span class="costcode-group-label">Costcode</span>
                                    <input type="text" class="cost_code" name="cost_code_item" readonly required>
                                    <button type="button" class="costcode-search-btn" title="Search cost / job code" onclick="showJobCostCode(this)">
                                        <i class="icon-search"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <select class="form-select par_to_item" name="par_to_item">
                                    <option value="N/A" selected>Select an employee</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-select" name="frequency_item">
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Yearly">Yearly</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="purpose_item">
                            </td>
                            <td>
                                <input type="date" class="form-control" name="date_needed_item" required>
                            </td>
                            <td>
                                <input type="number" class="form-control" name="quantity_item" min="1" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm" onclick="submitItem(this);"><i class="icon-checkmark"></i></button>
                            </td>
                        </tr>`;
            $("#mrs_items").append(row);

            let $newRow = $("#mrs_items tr.add-item-row").last();

            // Populate PAR To employees for the new row
            let selectElement = $newRow.find('.par_to_item');
            let employeesArr = employees.split("|");
            employeesArr.forEach(function(employee) {
                let fullname = employee.split(":")[0];
                selectElement.append('<option value="' + employee + '">' + fullname + '</option>');
            });
        }

        /* ---- Custom searchable product picker ---- */
        function escapeHtml(s){ return $('<div>').text(s == null ? '' : String(s)).html(); }

        // Anchor the fixed dropdown to its input, flipping up when there's no room below
        function positionProdDropdown($picker){
            const $input = $picker.find('.prod-picker-input');
            const $dd    = $picker.find('.prod-picker-dropdown');
            if(!$input.length || !$dd.hasClass('show')) return;

            const rect      = $input[0].getBoundingClientRect();
            const ddHeight  = Math.min($dd[0].scrollHeight, 260);
            const spaceBelow = window.innerHeight - rect.bottom;
            const openUp    = spaceBelow < (ddHeight + 12) && rect.top > spaceBelow;

            $dd.css({
                left:  rect.left + 'px',
                width: rect.width + 'px',
                top:   (openUp ? (rect.top - ddHeight - 4) : (rect.bottom + 4)) + 'px'
            });
        }

        let prodPickerTimer = null;

        $(document).on('input', '.prod-picker-input', function(){
            const $input  = $(this);
            const $picker = $input.closest('.prod-picker');
            const $dd     = $picker.find('.prod-picker-dropdown');
            $picker.find('.prod-picker-id').val('');
            $input.removeClass('has-selection');

            const term = $input.val().trim();
            if(term.length < 1){ $dd.removeClass('show').empty(); return; }

            $dd.addClass('show').html('<div class="prod-picker-msg">Searching…</div>');
            positionProdDropdown($picker);
            clearTimeout(prodPickerTimer);
            prodPickerTimer = setTimeout(function(){
                $.ajax({
                    type: 'POST',
                    url: "{{ route('products.lookup') }}",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { name: term },
                    success: function(resp){
                        const list = Array.isArray(resp) ? resp : [];
                        $picker.data('results', list);
                        if(!list.length){
                            $dd.html('<div class="prod-picker-msg">No products found</div>');
                        } else {
                            let html = '';
                            list.forEach(function(p){
                                html += '<div class="prod-picker-item" data-id="'+p.id+'">'
                                      +   '<span class="prod-picker-code">'+escapeHtml(p.code)+'</span>'
                                      +   '<span class="prod-picker-name">'+escapeHtml(p.name)+'</span>'
                                      + '</div>';
                            });
                            $dd.html(html);
                        }
                        positionProdDropdown($picker);
                    },
                    error: function(){
                        $dd.html('<div class="prod-picker-msg">No products found</div>');
                        positionProdDropdown($picker);
                    }
                });
            }, 250);
        });

        $(document).on('mousedown', '.prod-picker-item', function(e){
            e.preventDefault();
            const $item   = $(this);
            const $picker = $item.closest('.prod-picker');
            const id      = String($item.data('id'));
            const list    = $picker.data('results') || [];
            const prod    = list.filter(function(p){ return String(p.id) === id; })[0];
            const label   = prod ? '('+prod.code+') '+prod.name : $item.find('.prod-picker-name').text();
            $picker.find('.prod-picker-id').val(id);
            $picker.find('.prod-picker-input').val(label).addClass('has-selection');
            $picker.find('.prod-picker-dropdown').removeClass('show').empty();
        });

        $(document).on('focus', '.prod-picker-input', function(){
            const $picker = $(this).closest('.prod-picker');
            const $dd = $picker.find('.prod-picker-dropdown');
            if($dd.children().length){ $dd.addClass('show'); positionProdDropdown($picker); }
        });

        $(document).on('click', function(e){
            if(!$(e.target).closest('.prod-picker').length){
                $('.prod-picker-dropdown').removeClass('show');
            }
        });

        // Keep the fixed dropdown glued to its input while scrolling/resizing (capture = catches inner scrollers)
        window.addEventListener('scroll', function(){
            const el = document.querySelector('.prod-picker-dropdown.show');
            if(el){ positionProdDropdown($(el).closest('.prod-picker')); }
        }, true);
        $(window).on('resize', function(){
            const el = document.querySelector('.prod-picker-dropdown.show');
            if(el){ positionProdDropdown($(el).closest('.prod-picker')); }
        });

        function submitItem(button) {
            const row = $(button).closest('tr');
            const data = {
                mrs_id: row.find('input[name="mrs_id"]').val(),
                product_id: row.find('.prod-picker-id').val(),
                cost_code_item: row.find('input[name="cost_code_item"]').val(),
                par_to_item: row.find('select[name="par_to_item"]').val(),
                frequency_item: row.find('select[name="frequency_item"]').val(),
                purpose_item: row.find('input[name="purpose_item"]').val(),
                date_needed_item: row.find('input[name="date_needed_item"]').val(),
                quantity_item: row.find('input[name="quantity_item"]').val()
            };

            for (let key in data) {
                if (!data[key]) { 
                    console.log('Creating snackbar for:', key);
                    new SnackBar({
                        message: key+" is required.",
                        status: "error",
                        position: 'bc',
                        width: "500px",
                        dismissible: false,
                        container: ".editdetailbody"
                    });
                    return;
                }
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('mrs.saveItem') }}",
                data: data,
                success: function(response) {
                    console.log('Item saved successfully:', response);
                    new SnackBar({
                        message: "Item saved successfully.",
                        status: "success",
                        position: 'bc',
                        width: "500px",
                        dismissible: false,
                        container: ".editdetailbody"
                    });
                    edit_item(data.mrs_id);
                },
                error: function(xhr) {
                    console.error('Error saving item:', xhr);
                    new SnackBar({
                        message: "Error saving item.",
                        status: "error",
                        position: 'bc',
                        width: "500px",
                        dismissible: false,
                        container: ".editdetailbody"
                    });
                    return;
                }
            });
        }

        $(document).on('click', '.remove-row', function() {
            let itemId = $(this).data('id');
            swal({
                title: 'Are you sure?',
                text: "This will exclude the item from your MRS request",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'            
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        data: {
                            "item_id": itemId,
                            "_token": "{{ csrf_token() }}",
                        },
                        type: "post",
                        url: "{{route('mrs.deleteItem')}}",
                        success: function(data) {
                            $(`#row-${itemId}`).remove();
                        }
                    });
                } 
                else {                    
                    swal.close();                   
                }
            });
        });
        
        let selectedInput = null;

        function showJobCostCode(button) {
            selectedInput = button.previousElementSibling; // Get the associated input field
            $("#jobcostcodes").modal('show');
        }

        function view_deliveries(salesID){
            $('#delivery'+salesID).modal('show');
        }

        function cancel_unpaid_order(id){
            $('#orderid').val(id);
            $('#cancel_order').modal('show');
        }

        function deleteFile(filePath) {
            swal({
                title: "Are you sure?",
                text: "This file will be permanently deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: "{{ route('deleteFile') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            file_path: filePath
                        },
                        success: function(response) {
                            if (response.success) {
                                swal("Deleted!", "Your file has been deleted.", "success");
                                // Remove the deleted file element from the DOM
                                $(`div[data-file="${filePath}"]`).remove();
                            } else {
                                swal("Error!", response.message, "error");
                            }
                        },
                        error: function() {
                            swal("Error!", "Something went wrong.", "error");
                        }
                    });
                }
            });
        }

        function initSelectize(value, isClear = true) {
            $('#costcode').val(value);
            $('#costcode').selectize({
                plugins: ['remove_button'],
                delimiter: ',',
                persist: false,
                create: function(input) {
                    let values = localStorage.getItem("CC") || "";
                    let allowedValues = values.split(",");
                    if (!allowedValues.includes(input)) {
                        console.error("Code not found:", input);
                        return false; // Prevent creation of invalid input
                    }
                    return {
                        value: input,
                        text: input
                    };
                },
            });
            if(isClear){
                $('#costcode')[0].selectize.clear();
            }
        }

        $('.print').click(function(evt) {
            evt.preventDefault();
            var orderNumber = this.getAttribute('data-order-number');
            console.log('Print button clicked', orderNumber);
            $.ajax({
                url: "{{route('pa.generate_report_customer')}}",
                type: 'GET',
                data: { orderNumber: orderNumber },
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
	</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("codeSearch");
        const codeList = document.getElementById("codeList");
        const jobCodeRadio = document.getElementById("jobCodeRadio");
        const costCodeRadio = document.getElementById("costCodeRadio");
        
        const sampleJobCodes = localStorage.getItem('JC') ? localStorage.getItem('JC').split(',') : [];
        const sampleCostCodes = localStorage.getItem('CC') ? localStorage.getItem('CC').split(',') : [];
    
        let currentCodes = [];
    
        function updateCodeList(filter = "") {
            codeList.innerHTML = ""; 
            if (filter.trim() === "") return;
    
            currentCodes
                .filter(code => code.toLowerCase().includes(filter.toLowerCase()))
                .forEach(code => {
                    let item = document.createElement("a");
                    item.href = "#";
                    item.classList.add("list-group-item", "list-group-item-action");
                    item.textContent = code;
                    codeList.appendChild(item);
                });
        }
    
        searchInput.addEventListener("input", function () {
            updateCodeList(this.value);
        });
    
        codeList.addEventListener("click", function (event) {
            if (event.target.tagName === "A" && selectedInput) {
                selectedInput.value = event.target.textContent;
                $("#jobcostcodes").modal('hide');
            }
        });
    
        jobCodeRadio.addEventListener("change", function () {
            currentCodes = sampleJobCodes;
            updateCodeList(searchInput.value);
        });
    
        costCodeRadio.addEventListener("change", function () {
            currentCodes = sampleCostCodes;
            updateCodeList(searchInput.value);
        });
    
        currentCodes = sampleJobCodes;
    });
    </script>
@endsection

