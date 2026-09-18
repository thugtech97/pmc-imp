@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('css/sweetalert.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}"/>
    <!--
    <link
		rel="stylesheet"
		href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/css/selectize.default.min.css"
		integrity="sha512-pTaEn+6gF1IeWv3W1+7X7eM60TFu/agjgoHmYhAfLEU8Phuf6JKiiE8YmsNC0aCgQv4192s4Vai8YZ6VNM6vyQ=="
		crossorigin="anonymous"
		referrerpolicy="no-referrer"
	/>!-->
    <link href="{{ asset('css/selectize.bootstrap2.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.bootstrap3.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.default.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.legacy.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('lib/select2/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/employee-picker.css') }}" rel="stylesheet">
    <style>
        /* ============================================================
           Checkout / Place Request — visual parity with the MRS pages
           ============================================================ */
        .chk-page { padding-top: 12px; }

        .chk-card {
            background: #fff;
            border: 1px solid #eef2f6;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }
        .chk-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            color: #fff;
            padding: 1.25rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px 24px;
        }
        .chk-header .chk-eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            opacity: .8;
            margin-bottom: 2px;
        }
        .chk-header h3 {
            color: #fff;
            font-weight: 700;
            letter-spacing: .3px;
            margin: 0;
            font-size: 22px;
        }
        .chk-header .chk-sub {
            color: rgba(255, 255, 255, .85);
            font-size: 13px;
            margin-top: 4px;
        }
        .chk-header .chk-status {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .4px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .chk-body { padding: 1.5rem 1.75rem 1.75rem; }

        /* ---- Callouts ---- */
        .chk-callout {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13.5px;
            line-height: 1.5;
            margin-bottom: 12px;
            border: 1px solid transparent;
        }
        .chk-callout i { font-size: 16px; line-height: 1.4; flex: 0 0 auto; }
        .chk-callout-info    { background: #eff6ff; border-color: #bfdbfe; color: #1e3a8a; }
        .chk-callout-warning { background: #fff8e1; border-color: #fde68a; color: #7c4a03; }
        .chk-callout-danger  { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .chk-callout .btn-close { margin-left: auto; font-size: 11px; }

        /* ---- Sections ---- */
        .chk-section { margin-top: 1.5rem; }
        .chk-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin: 0 0 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eef2f6;
        }
        .chk-section-title .chk-count {
            display: inline-block;
            min-width: 24px;
            padding: 2px 8px;
            border-radius: 999px;
            background: #e0ecf9;
            color: #1e3a5f;
            font-size: 11px;
            text-align: center;
        }
        .chk-section-title .chk-hint {
            margin-left: auto;
            font-size: 11.5px;
            font-weight: 500;
            text-transform: none;
            letter-spacing: 0;
            color: #64748b;
        }

        /* ---- Form controls ---- */
        .chk-body .form-group { margin-bottom: 0; }
        .chk-body .form-group > label,
        .chk-body .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            margin-bottom: 6px;
        }
        .chk-body .form-group > label .req { color: #dc2626; margin-left: 2px; }
        .chk-body .form-control,
        .chk-body .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: .55rem .8rem;
            font-size: 14px;
            color: #1e293b;
            box-shadow: none;
            width: 100%;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .chk-body .form-control:focus,
        .chk-body .form-select:focus {
            border-color: #2c5282;
            box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.12);
            outline: none;
        }
        .chk-body .form-control:disabled,
        .chk-body .form-control[disabled] { background: #f1f5f9; color: #64748b; }
        .chk-body .form-control.is-invalid { border-color: #dc2626; }
        .chk-body textarea.form-control { min-height: 120px; resize: vertical; }
        .chk-body #loader { color: #2c5282; margin-left: 4px; }

        /* Select2 employee picker + selectize cost-code box: match the inputs */
        .chk-body .select2-container--default .emp-picker-container .select2-selection--single {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            height: 42px;
        }
        .chk-body .select2-container--default .emp-picker-container .select2-selection__rendered { line-height: 40px; font-size: 14px; }
        .chk-body .select2-container--default .emp-picker-container .select2-selection__arrow { height: 40px; }
        .chk-body .select2-container--default.select2-container--focus.emp-picker-container .select2-selection--single,
        .chk-body .select2-container--default.select2-container--open.emp-picker-container .select2-selection--single {
            border-color: #2c5282;
            box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.12);
        }
        .chk-body .selectize-control.multi .selectize-input {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 10px;
            min-height: 42px;
            box-shadow: none;
            font-size: 14px;
        }
        .chk-body .selectize-control.multi .selectize-input.focus {
            border-color: #2c5282;
            box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.12);
        }
        .chk-body .selectize-control.multi .selectize-input > div {
            background: #e0ecf9;
            color: #1e3a5f;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            padding: 2px 8px;
        }
        .chk-body .selectize-dropdown { border-radius: 8px; border-color: #e2e8f0; box-shadow: 0 10px 24px rgba(15, 23, 42, .12); }

        /* ---- Items table ---- */
        .chk-table-wrap { overflow-x: auto; border-radius: 10px; box-shadow: 0 0 0 1px #e9edf1; }
        .chk-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100%;
            min-width: 1100px;
            font-size: 13.5px;
            margin: 0 !important;
        }
        .chk-table thead th {
            background: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: .4px;
            font-weight: 700;
            border: none;
            border-bottom: 2px solid #e2e8f0;
            padding: 11px 12px;
            white-space: nowrap;
        }
        .chk-table tbody td {
            border: none;
            border-bottom: 1px solid #eef2f6;
            padding: 12px;
            vertical-align: middle;
            background: #fff;
        }
        .chk-table tbody tr:last-child td { border-bottom: none; }
        .chk-table tbody tr:hover td { background: #f8fafc; }
        .chk-table .form-control,
        .chk-table .form-select { padding: .45rem .7rem; font-size: 13px; }
        .chk-table .col-priority { width: 70px; text-align: center; }
        .chk-table .col-product  { min-width: 280px; }
        .chk-table .col-qty      { width: 110px; white-space: nowrap; }
        .chk-table .col-parto    { min-width: 200px; }
        .chk-table .col-date     { width: 160px; }
        .chk-table .col-freq     { width: 140px; }
        .chk-table .col-purpose  { min-width: 180px; }

        .chk-pill {
            display: inline-block;
            min-width: 30px;
            padding: 3px 10px;
            border-radius: 999px;
            background: #e0ecf9;
            color: #1e3a5f;
            font-weight: 700;
            font-size: 12px;
            text-align: center;
        }

        .chk-product { display: flex; align-items: center; gap: 12px; }
        .chk-product-img {
            flex: 0 0 auto;
            width: 56px;
            height: 56px;
            border-radius: 8px;
            border: 1px solid #eef2f6;
            background: #fff;
            object-fit: cover;
        }
        .chk-product-name { font-weight: 600; color: #1e293b; line-height: 1.3; }
        .chk-product-meta { font-size: 12px; color: #64748b; margin-top: 2px; }
        .chk-code-chip {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 12px;
            font-weight: 600;
            color: #1e3a5f;
            background: #e0ecf9;
            border-radius: 6px;
            padding: 2px 8px;
            letter-spacing: .3px;
        }
        .chk-code-select {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #64748b;
        }
        .chk-code-select .costcode-option {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fff;
            color: #1e293b;
            font-size: 12.5px;
            font-weight: 500;
            text-transform: none;
            letter-spacing: 0;
            padding: 4px 26px 4px 8px;
            min-width: 150px;
            width: auto;
        }
        .chk-code-select .costcode-option:focus { outline: none; border-color: #2c5282; box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.12); }

        .chk-qty { font-weight: 700; color: #1e293b; }
        .chk-qty small { font-weight: 500; color: #64748b; }

        /* ---- Attachments ---- */
        .chk-dropzone {
            border: 1.5px dashed #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            padding: 14px 16px;
            transition: border-color .15s ease, background .15s ease;
        }
        .chk-dropzone:hover { border-color: #2c5282; background: #f1f5f9; }
        .chk-dropzone input[type="file"] { padding: .4rem .6rem; background: #fff; }
        .chk-dropzone .chk-dropzone-hint { font-size: 12px; color: #64748b; margin-top: 8px; }
        #fileList { margin-top: 10px; display: flex; flex-wrap: wrap; gap: 6px; }
        #fileList .chk-file {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #1e293b;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 3px 10px;
        }
        #fileList .chk-file i { color: #2c5282; }

        /* ---- Summary card ---- */
        .chk-summary {
            border: 1px solid #eef2f6;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            position: sticky;
            top: 90px;
        }
        .chk-summary-head {
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 18px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #475569;
        }
        .chk-summary-body { padding: 6px 18px 14px; }
        .chk-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px dashed #eef2f6;
            font-size: 13.5px;
        }
        .chk-summary-row:last-child { border-bottom: none; }
        .chk-summary-row .k { color: #64748b; font-weight: 600; font-size: 11.5px; text-transform: uppercase; letter-spacing: .4px; }
        .chk-summary-row .v { color: #1e293b; font-weight: 600; text-align: right; }
        .chk-summary-total {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 12px 18px 16px;
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            color: #fff;
        }
        .chk-summary-total .k { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; opacity: .85; }
        .chk-summary-total .v { font-size: 28px; font-weight: 800; letter-spacing: -.5px; }

        /* ---- Footer / submit ---- */
        .chk-footer {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .chk-footer .chk-footnote { font-size: 12.5px; color: #64748b; }
        .chk-submit {
            border: none;
            border-radius: 10px;
            padding: .8rem 2rem;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: .3px;
            color: #fff;
            background: linear-gradient(135deg, #2c5282 0%, #1e3a5f 100%);
            box-shadow: 0 8px 20px rgba(30, 58, 95, 0.28);
            transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
        }
        .chk-submit:hover { filter: brightness(1.1); transform: translateY(-1px); box-shadow: 0 12px 26px rgba(30, 58, 95, 0.32); }
        .chk-submit:active { transform: translateY(0); }

        @media (max-width: 767.98px) {
            .chk-body { padding: 1.1rem 1rem 1.25rem; }
            .chk-header { padding: 1rem 1.1rem; }
            .chk-summary { position: static; }
        }
    </style>
@endsection

@section('content')
<div class="container-fluid content-wrap chk-page">
    @auth
        @if (isset($announcements))
            @foreach ($announcements as $announcement)
                <div class="chk-callout chk-callout-warning" role="alert">
                    <i class="icon-warning-sign"></i>
                    <div>{!! $announcement->content !!}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="$(this).closest('.chk-callout').remove()"></button>
                </div>
            @endforeach
        @endif
    @endauth

    @php
        $itemCount = count($orders);
        $deptName  = auth()->user()->department->name;
    @endphp

    <div class="chk-card">
        <div class="chk-header">
            <div>
                <div class="chk-eyebrow">Material Requisition</div>
                <h3>Review and Place Request</h3>
                <div class="chk-sub">{{ $deptName }} &middot; {{ $itemCount }} {{ Str::plural('item', $itemCount) }} in cart</div>
            </div>
            <span class="chk-status">
                @if($mrs)
                    Adding to saved MRS {{ $mrs->order_number }}
                @else
                    New request
                @endif
            </span>
        </div>

        <div class="chk-body">
            <div class="chk-callout chk-callout-info">
                <i class="icon-info-sign"></i>
                <div>
                    <strong>Note:</strong> The next person has three days to review or approve it before forwarding to the next level.
                    Please be reminded to raise a request 1 to 2 months earlier before the Date Needed to avoid rush processing of your request. Thank you.
                </div>
            </div>
            @if($mrs)
                <div class="chk-callout chk-callout-warning">
                    <i class="icon-warning-sign"></i>
                    <div><strong>There is an existing MRS request that has been SAVED.</strong> New items on the cart will be added to the existing SAVED request.</div>
                </div>
            @endif
            @if (session('error'))
                <div class="chk-callout chk-callout-danger" role="alert">
                    <i class="icon-remove-sign"></i>
                    <div><strong>{{ session('error') }}</strong></div>
                    <button type="button" class="btn-close" aria-label="Close" onclick="$(this).closest('.chk-callout').remove()"></button>
                </div>
            @endif

            <form method="post" action="{{ route('cart.temp_sales') }}" id="chk_form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="shipping_type" value="Pickup">

                {{-- ---------------- Request details ---------------- --}}
                <section class="chk-section">
                    <h5 class="chk-section-title">Request details</h5>
                    <div class="row g-3">
                        <div class="col-6 col-md-3 form-group">
                            <label>Priority # <span class="req">*</span></label>
                            <select name="priority" required onchange="$('.priority_no').html(this.value)" class="form-select">
                                <option value="1" {{ $mrs ? ($mrs->priority == '1' ? 'selected' : '') : '' }}>Priority 1</option>
                                <option value="2" {{ $mrs ? ($mrs->priority == '2' ? 'selected' : '') : '' }}>Priority 2</option>
                                <option value="3" {{ $mrs ? ($mrs->priority == '3' ? 'selected' : '') : '' }}>Priority 3</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3 form-group">
                            <label>Date Needed <span class="req">*</span></label>
                            <input type="date" value="{{ $mrs ? $mrs->delivery_date : '' }}" class="form-control date_needed" name="date_needed" onchange="change_date(this.value)" required>
                        </div>

                        <div class="col-6 col-md-3 form-group">
                            <label for="codeType">Code Type <span class="req">*</span></label>
                            <select id="codeType" class="form-select" required>
                                <option value="CC">CC</option>
                                <option value="JC">JC</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3 form-group">
                            <label for="costcode"><span id="labelCode">Cost Code</span> <span class="req">*</span> <span id="loader"><i class="fa fa-spin icon-reload"></i></span></label>
                            <input type="text" value="{{ $mrs ? $mrs->costcode : '' }}" class="form-control" name="costcode" id="costcode" required>
                        </div>

                        <div class="col-6 col-md-3 form-group">
                            <label for="isBudgeted">Budgeted?</label>
                            <select id="isBudgeted" name="isBudgeted" class="form-select">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3 form-group budgetAmount">
                            <label for="budgeted_amount">Budget amount</label>
                            <input type="number" value="{{ $mrs ? number_format($mrs->budgeted_amount, 2, '.', '') : '' }}" step="0.01" id="budgeted_amount" name="budgeted_amount" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Department</label>
                            <input type="text" class="form-control" name="department" value="{{ $deptName }}" disabled required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="section">Section <span class="req">*</span></label>
                            <input type="text" value="{{ $mrs ? $mrs->section : '' }}" class="form-control" id="section" name="section" placeholder="Start typing your section" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Note <span class="req">*</span></label>
                            <input type="text" value="{{ $mrs ? $mrs->purpose : '' }}" class="form-control" name="justification" onkeyup="$('.purpose').val(this.value)" placeholder="Overall purpose of this request" required>
                        </div>
                    </div>
                </section>

                {{-- ---------------- Items ---------------- --}}
                <section class="chk-section">
                    <h5 class="chk-section-title">
                        Items <span class="chk-count">{{ $itemCount }}</span>
                        <span class="chk-hint">Set the purpose, PAR-to employee, date needed and frequency for each line.</span>
                    </h5>

                    <div class="chk-table-wrap">
                        <table class="table chk-table">
                            <thead>
                                <tr>
                                    <th class="col-priority">Priority#</th>
                                    <th class="col-product">Product</th>
                                    <th>Code</th>
                                    <th>OEM</th>
                                    <th class="col-purpose">Purpose</th>
                                    <th class="col-parto">PAR To</th>
                                    <th class="col-date">Date Needed</th>
                                    <th class="col-freq">Frequency</th>
                                    <th class="col-qty">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalqty = 0; @endphp
                                @foreach($orders as $order)
                                    @php
                                        $totalqty += $order->qty;
                                        $parTo = $order->mrs_details->par_to ?? '';
                                    @endphp
                                    <tr>
                                        <td class="col-priority">
                                            <span class="chk-pill priority_no">{{ $mrs ? $mrs->priority : '1' }}</span>
                                        </td>
                                        <td class="col-product">
                                            <div class="chk-product">
                                                <img class="chk-product-img" src="{{ $order->product->photoPrimary }}" onerror="this.src='{{ asset('images/1667370521_download.jpg') }}'" alt="{{ $order->product->name }}">
                                                <div>
                                                    <div class="chk-product-name">{{ $order->product->name }}</div>
                                                    <div class="chk-product-meta">{{ $order->product->uom }}</div>
                                                    <label class="chk-code-select">
                                                        Cost code
                                                        <select class="costcode-option" name="codes[]" required></select>
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="chk-code-chip">{{ $order->product->code }}</span></td>
                                        <td>{{ $order->product->oem }}</td>
                                        <td class="col-purpose">
                                            <input type="text" value="{{ $order->mrs_details->purpose ?? '' }}" class="form-control purpose" required name="item_purpose[]" placeholder="Purpose">
                                        </td>
                                        <td class="col-parto">
                                            <select class="form-select employees" name="par_to[]">
                                                <option value="N/A">Select an employee</option>
                                                @if($parTo !== '' && $parTo !== 'N/A')
                                                    <option value="{{ $parTo }}" selected>{{ trim(explode(':', $parTo)[0]) }}</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td class="col-date">
                                            <input type="date" value="{{ \Carbon\Carbon::parse($order->mrs_details->date_needed ?? '')->format('Y-m-d') }}" class="form-control date_needed" required name="item_date_needed[]">
                                        </td>
                                        <td class="col-freq">
                                            <select class="form-select" name="frequency[]" required>
                                                <option value="Daily" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'Daily' ? 'selected' : '' }}>Daily</option>
                                                <option value="Weekly" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                                <option value="Monthly" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                                <option value="Yearly" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'Yearly' ? 'selected' : '' }}>Yearly</option>
                                                <option value="As Needed" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'As Needed' ? 'selected' : '' }}>As Needed</option>
                                            </select>
                                        </td>
                                        <td class="col-qty">
                                            <span class="chk-qty">{{ $order->qty }} <small>{{ $order->product->uom }}(s)</small></span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- ---------------- Delivery, requestor, attachments + summary ---------------- --}}
                <section class="chk-section">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-12 form-group">
                                    <label for="requested_by">Requested by <span class="req">*</span></label>
                                    <select id="requested_by" name="requested_by" class="form-select" required>
                                        <option value="">Select an employee</option>
                                        @if($mrs && $mrs->requested_by)
                                            <option value="{{ $mrs->requested_by }}" selected>{{ trim(explode(':', $mrs->requested_by)[0]) }}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-12 form-group">
                                    <label for="notes">Delivery Instruction <span class="req">*</span></label>
                                    <textarea id="notes" class="form-control form-input" name="notes" rows="5" placeholder="Where and how the items should be delivered" required>{{ $mrs ? $mrs->other_instruction : '' }}</textarea>
                                </div>

                                <div class="col-12 form-group">
                                    <label for="attachment">Attach files</label>
                                    <div class="chk-dropzone">
                                        <input type="file" id="attachment" name="attachment[]" class="form-control" multiple>
                                        <div class="chk-dropzone-hint">Quotations, specifications, or any supporting document. You can select more than one file.</div>
                                        <div id="fileList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="chk-summary">
                                <div class="chk-summary-head">Request summary</div>
                                <div class="chk-summary-body">
                                    <div class="chk-summary-row"><span class="k">Department</span><span class="v">{{ $deptName }}</span></div>
                                    <div class="chk-summary-row"><span class="k">Items</span><span class="v">{{ $itemCount }}</span></div>
                                    <div class="chk-summary-row"><span class="k">Priority</span><span class="v"><span class="chk-pill priority_no">{{ $mrs ? $mrs->priority : '1' }}</span></span></div>
                                    <div class="chk-summary-row"><span class="k">Date needed</span><span class="v" id="summary_date_needed">{{ $mrs ? $mrs->delivery_date : '—' }}</span></div>
                                    <div class="chk-summary-row"><span class="k">Request type</span><span class="v">{{ $mrs ? 'Add to saved MRS' : 'New MRS' }}</span></div>
                                </div>
                                <div class="chk-summary-total">
                                    <span class="k">Total quantity</span>
                                    <span class="v">{{ $totalqty }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="chk-footer">
                    <div class="chk-footnote"><span class="req">*</span> Required fields. Placing the request forwards it to your department's approver.</div>
                    <button type="submit" class="chk-submit"><i class="icon-paper-plane me-2"></i>Place Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{--<input type="hidden" id="totalAmountWithoutCoupon" value="{{number_format($subtotal,2,'.','')}}">--}}
<input type="hidden" id="totalQty" value="{{$totalqty}}">

<input type="hidden" id="coupon_limit" value="{{ Setting::info()->coupon_limit }}">
{{--<input type="hidden" id="solo_coupon_counter" value="{{$soloCouponCounter}}"> --}}

@include('theme.pages.ecommerce.modal')

@endsection

@section('pagejs')
<script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('js/sweetalert.min.js') }}"></script>
<!--<script
    src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.15.2/js/selectize.min.js"
    integrity="sha512-IOebNkvA/HZjMM7MxL0NYeLYEalloZ8ckak+NDtOViP7oiYzG5vn6WVXyrJDiJPhl4yRdmNAG49iuLmhkUdVsQ=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer">
</script>
!-->
<script src="{{ asset('js/selectize.js') }}"></script>
<script src="{{ asset('lib/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('js/employee-picker.js') }}"></script>

<script>
    /*
    this line is brought to you by
    */

    var mrs = "{{ $mrs }}";

    function asLocalDate(date) {
        return date.getFullYear()
            + '-' + (date.getMonth() + 1).toString().padStart(2, '0')
            + '-' + date.getDate().toString().padStart(2, '0');
    }

    // An MRS raised today cannot also be needed today — it still has to be
    // reviewed and approved, so tomorrow is the soonest it can be needed.
    var EARLIEST_DATE_NEEDED = (function () {
        var d = new Date();
        d.setDate(d.getDate() + 1);
        return asLocalDate(d);
    })();

	$(document).ready(function(){
        $('[data-bs-toggle="popover"]').popover();
        $('.deliveryDate').hide();
        $('.customerAddress').hide();
        $('.budgetAmount').css('visibility', 'hidden');
        if(mrs){
            var decodedJson = mrs.replace(/&quot;/g, '"');
            var jsonObject = JSON.parse(decodedJson);
            $('.budgetAmount').css('visibility', parseInt(jsonObject.budgeted_amount) > 0 ? 'visible' : 'hidden');
            $('#isBudgeted').val(parseInt(jsonObject.budgeted_amount) > 0 ? '1' : '0');

            var options = ($("#costcode").val()).split(",");
            initSelectize(options, false)
        }else{
            getCodes($('#codeType').val());
        }
        if(!mrs){
            $('.date_needed').val(EARLIEST_DATE_NEEDED);
            $('#summary_date_needed').text(EARLIEST_DATE_NEEDED);
        }
        // toISOString() is UTC, which reads as yesterday here before 8am, so the
        // floor is built from the local date instead.
        $('.date_needed').attr('min', EARLIEST_DATE_NEEDED);

        // The min attribute alone is not enough: a page left open overnight, or
        // a browser that ignores it, would still post a date that is now today.
        $('#chk_form').on('submit', function (e) {
            var tooSoon = [];

            $('.date_needed').each(function () {
                var value = $(this).val();
                $(this).removeClass('is-invalid');

                if (!value || value < EARLIEST_DATE_NEEDED) {
                    tooSoon.push(this);
                }
            });

            if (tooSoon.length) {
                e.preventDefault();
                $(tooSoon).addClass('is-invalid');
                $(tooSoon[0]).focus();
                swal(
                    'Date needed is too soon',
                    'A request raised today cannot also be needed today — it still has to be reviewed and approved. '
                        + 'Set the date needed to ' + EARLIEST_DATE_NEEDED + ' or later.',
                    'error'
                );
                return false;
            }
        });

        $('#shippingType').on('change', function() {
            if (this.value === "Delivery") {
                $('.customerAddress').show();
                $('.deliveryDate').show();
            }
            else {
                $('.deliveryDate').hide();
                $('.customerAddress').hide();
            }
        })

        $('#isBudgeted').on('change', function() {
            if (this.value == 1) {
                $('.budgetAmount').css('visibility', 'visible');
            } else {
                $('.budgetAmount').css('visibility', 'hidden');
            }
        });

        $('#codeType').on('change', function() {
            getCodes(this.value)
        });

        var sections = "{{ $sections }}";
        
        $("#section").autocomplete({
            source: JSON.parse(sections.replace(/&quot;/g, '"'))
        });
        

        EmployeePicker.setup("{{ route('users.employee_search') }}");
        EmployeePicker.init('#requested_by');
        EmployeePicker.init('.employees', { emptyValue: 'N/A' });
    });

    function getCodes(type){
        $("#loader").show();
        if ($('#costcode')[0].selectize) {
            $('#costcode')[0].selectize.destroy();
        }
        $("#costcode").prop('disabled', true);
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
                        $("#labelCode").html("Cost Code");
                        values = data.map(item => item.Full_GL_Codes).join(',');
                    } else { 
                        $("#labelCode").html("Job Code");
                        values = data.map(item => item.FULL_JOB_CODE).join(',');
                    }
                    localStorage.setItem(type, values);
                    $("#loader").hide();
                    $("#costcode").prop('disabled', false);
                    initSelectize(values);
                }
            });
        /*
        }
        */
    }

    function initSelectize(value, isClear = true) {
        $('#costcode').val(value);
        $('#costcode').selectize({
            plugins: ['remove_button'],
            delimiter: ',',
            persist: false,
            create: function(input) {
                // Get allowed values from localStorage
                let values = localStorage.getItem($('#codeType').val()) || "";
                let allowedValues = values.split(",");

                // Check if the input exists in allowedValues, if not, prevent creation
                if (!allowedValues.includes(input)) {
                    console.error("Code not found:", input);
                    return false; // Prevent creation of invalid input
                }

                return {
                    value: input,
                    text: input
                };
            },
            onChange: function(input) {
                if (!input) return;

                var options = input.split(",");
                var lastInput = options.slice(-1)[0];

                // Get allowed values from localStorage
                let values = localStorage.getItem($('#codeType').val()) || "";
                let allowedValues = values.split(",");

                // Prevent appending if lastInput is not in allowedValues
                if (!allowedValues.includes(lastInput)) {
                    console.error("Code not found:", lastInput);
                    return;
                }

                $('.costcode-option').empty();
                options.forEach(function(option) {
                    $('.costcode-option').append(new Option(option, option));
                });
            }
        });
        if(isClear){
            $('#costcode')[0].selectize.clear();
        }else{
            $('.costcode-option').empty();
            value.forEach(function(option) {
                $('.costcode-option').append(new Option(option, option));
            });
        }
    }

    function change_date(date){
        $('.date_needed').val(date);
        $('#summary_date_needed').text(date || '—');
    }

    // Show the chosen attachments as chips under the file input.
    $(document).on('change', '#attachment', function () {
        var $list = $('#fileList').empty();
        $.each(this.files || [], function (_, file) {
            $('<span class="chk-file">').append($('<i class="icon-file-text">'), $('<span>').text(file.name)).appendTo($list);
        });
    });
</script>

@endsection