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
        .content-wrap {
            background: linear-gradient(135deg, #f4f7fb 0%, #eef4ff 100%);
            padding-top: 40px;
            padding-bottom: 60px;
        }

        .checkout-shell {
            width: calc(100% - 12px);
            max-width: 100%;
            margin: 0 6px;
        }

        #loader {
            display: none;
            height: 14px;
            margin-left: 5px;
            vertical-align: middle;
            width: 14px;
        }

        #loader::before {
            border: 2px solid #dbe5f2;
            border-radius: 50%;
            border-top-color: #2563eb;
            content: "";
            display: inline-block;
            height: 12px;
            width: 12px;
            animation: checkout-loader-spin .75s linear infinite;
        }

        @keyframes checkout-loader-spin {
            to { transform: rotate(360deg); }
        }

        .checkout-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            border-radius: 24px 24px 0 0;
            padding: 28px 30px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
        }

        .checkout-header .eyebrow {
            display: inline-block;
            font-size: 11px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            opacity: 0.8;
            margin-bottom: 8px;
        }

        .checkout-header h3 {
            margin: 0;
            font-size: clamp(1.6rem, 2vw, 2.25rem);
            font-weight: 700;
            color: white;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .checkout-form {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-top: none;
            border-radius: 0 0 24px 24px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            padding: 26px;
        }

        .alert {
            /* border-radius: 16px; */
            border: none;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
            margin-bottom: 18px;
        }

        .checkout-panel {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 20px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
            margin-bottom: 22px;
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(180deg, #f8fafc 0%, #eef4ff 100%);
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            padding: 18px 22px;
        }

        .panel-header h4 {
            margin: 0;
            color: #0f172a;
            font-size: 1.08rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .panel-body {
            padding: 22px;
        }

        .checkout-layout {
            display: block;
            width: 100%;
        }

        .checkout-layout > div,
        .checkout-layout > aside {
            width: 100%;
            max-width: 100%;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #475569;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .form-control,
        .form-select,
        .selectize-input {
            border: 1px solid #dfe7f2 !important;
            background: #fff;
            border-radius: 12px !important;
            min-height: 46px;
            box-shadow: none !important;
            transition: all 0.2s ease;
            color: #0f172a;
        }

        .costcode-option,
        .costcode-option.selectized,
        .costcode-option + .selectize-control .selectize-input {
            border: 2px solid #c7d2fe !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08) !important;
        }

        .form-control:focus,
        .form-select:focus,
        .selectize-input.focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12) !important;
        }

        .form-select {
            padding: 0.625rem 2.5rem 0.625rem 0.9rem;
            appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, #475569 50%), linear-gradient(135deg, #475569 50%, transparent 50%);
            background-position: calc(100% - 18px) calc(50% - 2px), calc(100% - 13px) calc(50% - 2px);
            background-size: 5px 5px, 5px 5px;
            background-repeat: no-repeat;
        }

        .budgetAmount {
            visibility: hidden;
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .modern-table thead th {
            background: transparent;
            color: #475569;
            font-size: 0.72rem !important;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0 10px 12px;
            border: none;
            white-space: nowrap;
            vertical-align: top;
        }

        .modern-table tbody tr {
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
            border-radius: 16px;
        }

        .modern-table tbody td {
            border-top: 1px solid #edf2f7;
            border-bottom: 1px solid #edf2f7;
            padding: 16px 12px;
            vertical-align: middle;
            color: #334155;
            min-width: 100px;
        }

        .modern-table thead th:nth-child(2),
        .modern-table tbody td:nth-child(2) {
            min-width: 260px;
            width: 26%;
        }

        .modern-table thead th:nth-child(5),
        .modern-table tbody td:nth-child(5) {
            min-width: 220px;
            width: 22%;
        }

        .modern-table tbody td:first-child {
            border-left: 1px solid #edf2f7;
            border-radius: 12px 0 0 12px;
        }

        .modern-table tbody td:last-child {
            border-right: 1px solid #edf2f7;
            border-radius: 0 12px 12px 0;
        }

        .top-cart-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-cart-item-image {
            display: none;
        }

        .top-cart-item-desc {
            min-width: 0;
            width: 100%;
        }

        .top-cart-item-desc-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.4;
            margin-bottom: 6px;
        }

        .costcode-option {
            margin-top: 8px;
            width: 100%;
        }

        .purpose-input {
            min-width: 220px;
            width: 100%;
        }

        .summary-card {
            background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
            color: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
            position: static;
            top: auto;
            margin-top: 22px;
            width: 100%;
        }

        .summary-header {
            padding: 22px 22px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .summary-header h3 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: white;
        }

        .summary-body {
            padding: 20px 22px 22px;
        }

        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.16);
            color: rgba(255, 255, 255, 0.82);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row strong {
            color: #fff;
        }

        .summary-total {
            margin-top: 16px;
            padding-top: 18px;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
        }

        .summary-total .label {
            font-size: 0.72rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.8;
        }

        .summary-total .value {
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
            color: #fff;
        }

        .action-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 22px;
            padding-top: 8px;
        }

        .place-request-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            border: none;
            border-radius: 999px;
            color: #fff;
            padding: 14px 30px;
            font-size: 0.96rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            box-shadow: 0 12px 24px rgba(249, 115, 22, 0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .place-request-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(249, 115, 22, 0.34);
        }

        .selectize-control {
            width: 100%;
        }

        .selectize-input {
            padding: 8px 34px 8px 10px !important;
            overflow: hidden;
            border-radius: 12px !important;
        }

        .selectize-dropdown {
            border-radius: 12px;
            border: 1px solid #dfe7f2;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
        }

        #fileList {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        #fileList .file-item {
            background: #eef4ff;
            border: 1px solid #dbeafe;
            border-radius: 999px;
            color: #1d4ed8;
            padding: 6px 10px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        @media (max-width: 991.98px) {
            .checkout-layout {
                display: block;
            }

            .summary-card {
                position: static;
            }
        }

        @media (max-width: 767.98px) {
            .checkout-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .checkout-form {
                padding: 16px;
            }

            .panel-body {
                padding: 16px;
            }

            .modern-table {
                min-width: 880px;
            }
        }

        thead, tbody, tfoot, tr, td, th {
            padding: 3px;
        }

        select {
            border: none;
            outline: none;
            padding: .375rem 2.25rem .375rem .75rem;
            font-size: 16px;
            width: 60%;
        }

        /* hide unecessary and update needed */
        section#page-title.py-4.py-lg-5 {
            display: none;
        }
        section#content.py-5.py-lg-6.position-relative {
            padding-top: 0px !important;
            padding-bottom: 0px !important;
        }
        #content .content-wrap {
            padding: 20px !important;
        }
        .selectize-control.form-control.multi.plugin-remove_button {
            border: none !important;
        }

        /* PMC checkout theme */
        .content-wrap {
            --checkout-ink: #222e2b;
            --checkout-deep: #0b1211;
            --checkout-green: #42615b;
            --checkout-gold: #ecd49a;
            --checkout-line: #e3eae5;
            background: #f4f5f1;
            font-family: "DM Sans", sans-serif;
            padding: 34px 24px 64px !important;
        }

        .checkout-shell {
            margin: 0 auto;
            /* max-width: 1380px; */
            width: 100%;
        }

        .checkout-header {
            background: linear-gradient(135deg, rgba(66, 97, 91, .98), var(--checkout-deep));
            border-radius: 14px 14px 0 0;
            box-shadow: 0 12px 28px rgba(34, 46, 43, .12);
            padding: 28px 32px;
            position: relative;
        }

        .checkout-header::before {
            background: var(--checkout-gold);
            content: '';
            height: 3px;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
        }

        .checkout-header .eyebrow {
            color: var(--checkout-gold);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.7px;
            opacity: 1;
        }

        .checkout-header h3 {
            font-size: clamp(1.45rem, 2vw, 2rem);
            margin-top: 4px;
        }

        .status-pill {
            background: rgba(255, 255, 255, .08);
            border-color: rgba(236, 212, 154, .25);
            color: rgba(255, 255, 255, .86);
            font-size: 12px;
            padding: 9px 14px;
        }

        .checkout-form {
            background: rgba(255, 255, 255, .84);
            border-color: var(--checkout-line);
            border-radius: 0 0 14px 14px;
            box-shadow: 0 20px 45px rgba(34, 46, 43, .08);
            padding: 24px;
        }

        .checkout-form > .alert {
            background: #f7f9f7;
            border: 1px solid var(--checkout-line);
            border-left: 3px solid var(--checkout-gold);
            border-radius: 9px;
            color: #52615b;
            font-size: 12px;
            line-height: 1.6;
            padding: 13px 16px;
        }

        .checkout-form > .alert-warning {
            background: #fffaf0;
            border-left-color: #d6a93b;
            color: #725b1e;
        }

        .checkout-layout {
            display: grid;
            gap: 22px;
            grid-template-columns: minmax(0, 1fr) 300px;
        }

        .checkout-layout > div,
        .checkout-layout > aside {
            max-width: none;
            width: auto;
        }

        .checkout-layout > div {
            display: contents;
        }

        .checkout-layout .request-details-panel {
            grid-column: 1;
        }

        .checkout-layout .products-cart-panel {
            grid-column: 1 / -1;
            min-width: 0;
        }

        .checkout-layout .supporting-details-panel {
            grid-column: 1;
        }

        .checkout-layout > aside {
            grid-column: 2;
            grid-row: 1;
        }

        .products-cart-panel .panel-body {
            padding: 18px 20px 22px;
        }

        .products-cart-panel .table-responsive {
            margin: 0;
            width: 100%;
        }

        .checkout-panel {
            background: #fff;
            border-color: var(--checkout-line);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(34, 46, 43, .05);
            margin-bottom: 18px;
        }

        .panel-header {
            background: #f7f9f7;
            border-bottom-color: var(--checkout-line);
            padding: 16px 20px;
        }

        .panel-header::before {
            background: var(--checkout-gold);
            border-radius: 2px;
            content: '';
            height: 20px;
            margin-right: 10px;
            width: 3px;
        }

        .panel-header h4 {
            color: var(--checkout-ink);
            flex: 1;
            font-size: 15px;
        }

        .panel-body {
            padding: 20px;
        }

        .checkout-form .form-group label {
            color: #64736d;
            font-size: 10px;
            letter-spacing: .7px;
        }

        .checkout-form .form-control,
        .checkout-form .form-select,
        .checkout-form .selectize-input {
            border-color: #dfe7e2 !important;
            border-radius: 8px !important;
            color: var(--checkout-ink);
            min-height: 44px;
        }

        .checkout-form .form-control:focus,
        .checkout-form .form-select:focus,
        .checkout-form .selectize-input.focus {
            border-color: var(--checkout-green) !important;
            box-shadow: 0 0 0 3px rgba(66, 97, 91, .12) !important;
        }

        .costcode-option,
        .costcode-option.selectized,
        .costcode-option + .selectize-control .selectize-input {
            border-color: rgba(66, 97, 91, .42) !important;
            box-shadow: 0 0 0 3px rgba(66, 97, 91, .08) !important;
        }

        .modern-table {
            border-spacing: 0 7px;
        }

        .modern-table thead th {
            color: var(--checkout-green);
            font-size: 10px !important;
            padding: 0 10px 10px;
        }

        .modern-table tbody tr {
            box-shadow: 0 4px 14px rgba(34, 46, 43, .05);
        }

        .modern-table tbody td {
            border-color: #e8efea;
            color: #52615b;
            padding: 13px 11px;
        }

        .modern-table tbody td:first-child {
            border-radius: 9px 0 0 9px;
        }

        .modern-table tbody td:last-child {
            border-radius: 0 9px 9px 0;
        }

        .top-cart-item-desc-title {
            color: var(--checkout-ink);
            font-size: 13px;
        }

        .summary-card {
            background: linear-gradient(150deg, var(--checkout-green), var(--checkout-deep));
            border-radius: 12px;
            box-shadow: 0 14px 30px rgba(34, 46, 43, .16);
            margin-top: 0;
            position: sticky;
            top: 24px;
        }

        .summary-header {
            border-bottom-color: rgba(236, 212, 154, .2);
            padding: 20px 20px 16px;
        }

        .summary-header h3 {
            font-size: 16px;
        }

        .summary-body {
            padding: 17px 20px 20px;
        }

        .summary-row {
            border-bottom-color: rgba(255, 255, 255, .14);
            font-size: 13px;
        }

        .summary-total .value {
            color: var(--checkout-gold);
        }

        .place-request-btn {
            background: var(--checkout-green);
            border: 1px solid var(--checkout-green);
            border-radius: 8px;
            box-shadow: 0 8px 18px rgba(66, 97, 91, .2);
            font-size: 12px;
            letter-spacing: .08em;
            padding: 13px 24px;
            transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
        }

        .place-request-btn:hover {
            background: var(--checkout-deep);
            border-color: var(--checkout-deep);
            box-shadow: 0 12px 22px rgba(34, 46, 43, .22);
            transform: translateY(-2px);
        }

        .selectize-dropdown {
            border-color: var(--checkout-line);
            box-shadow: 0 12px 28px rgba(34, 46, 43, .14);
        }

        #fileList .file-item {
            background: #f3f7f4;
            border-color: #dce8df;
            color: var(--checkout-green);
        }

        @media (max-width: 991.98px) {
            .checkout-layout {
                display: block;
            }

            .checkout-layout > div {
                display: block;
            }

            .checkout-layout .request-details-panel,
            .checkout-layout .products-cart-panel,
            .checkout-layout .supporting-details-panel,
            .checkout-layout > aside {
                grid-column: auto;
                grid-row: auto;
            }

            .summary-card {
                margin-top: 18px;
                position: static;
            }
        }

    </style>
@endsection

@section('content')
@php
    $totalqty = 0;
    foreach ($orders as $order) {
        $totalqty += $order->qty;
    }
@endphp
<div class="container-fluid content-wrap">

    @auth
        <div class="row">
            @if (isset($announcements))
                @foreach ($announcements as $announcement)
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {!! $announcement->content !!}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endforeach
            @endif
        </div>
    @endauth

    <div class="checkout-shell">
        <div class="checkout-header">
            <div>
                <div class="eyebrow">Procurement</div>
                <h3>Review and Place Request</h3>
            </div>
            <div class="status-pill">{{ $totalqty }} item(s) in cart</div>
        </div>

        <div class="checkout-form">
            <div class="alert alert-info">
                <strong>Note: The next person has three days to review or approve it before forwarding to the next level. Please be reminded to raise a request 1 to 2 months earlier before the Date Needed to avoid rush processing of your request. Thank you.</strong>
            </div>
            @if($mrs)
                <div class="alert alert-warning">
                    <strong>There is an existing MRS request that has been SAVED. New items on the cart will be added to the existing SAVED request.</strong>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="post" action="{{ route('cart.temp_sales') }}" id="chk_form" enctype="multipart/form-data">
                @csrf

                <div class="checkout-layout">
                    <div>
                        <div class="checkout-panel request-details-panel">
                            <div class="panel-header">
                                <h4>Request details</h4>
                            </div>
                            <div class="panel-body">
                                <div class="row g-3">
                                    <div class="col-lg-3 col-md-6 col-12 form-group">
                                        <label>Priority #</label>
                                        <select name="priority" required onchange="$('.priority_no').html(this.value)" class="form-select">
                                            <option value="1" {{ $mrs ? ($mrs->priority == '1' ? 'selected' : '') : '' }}>Priority 1</option>
                                            <option value="2" {{ $mrs ? ($mrs->priority == '2' ? 'selected' : '') : '' }}>Priority 2</option>
                                            <option value="3" {{ $mrs ? ($mrs->priority == '3' ? 'selected' : '') : '' }}>Priority 3</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-3 col-md-6 col-12 form-group">
                                        <label>Date Needed</label>
                                        <input type="date" value="{{ $mrs ? $mrs->delivery_date : '' }}" class="form-control date_needed" name="date_needed" onchange="change_date(this.value)" required>
                                    </div>

                                    <div class="col-lg-3 col-md-6 col-12 form-group">
                                        <label for="codeType">Code Type</label>
                                        <select id="codeType" class="form-select" required>
                                            <option value="CC">CC</option>
                                            <option value="JC">JC</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-3 col-md-6 col-12 form-group">
                                        <label for="shippingType"><span id="labelCode">Cost Code</span> <span id="loader" aria-label="Loading"></span></label>
                                        <input type="text" value="{{ $mrs ? $mrs->costcode : '' }}" class="form-control" name="costcode" id="costcode" height="200" required>
                                    </div>

                                    <div class="col-lg-3 col-md-6 col-12 form-group">
                                        <label for="isBudgeted" class="fw-semibold text-inital nols">Budgeted?</label>
                                        <select id="isBudgeted" name="isBudgeted" class="form-select">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-3 col-md-6 col-12 form-group budgetAmount">
                                        <label>Budget amount</label>
                                        <input type="number" value="{{ $mrs ? number_format($mrs->budgeted_amount, 2, '.', '') : '' }}" step="0.01" id="budgeted_amount" name="budgeted_amount" class="form-control">
                                    </div>

                                    <div class="col-lg-6 col-md-12 col-12 form-group">
                                        <label>Note</label>
                                        <input type="text" value="{{ $mrs ? $mrs->purpose : '' }}" class="form-control" name="justification" onkeyup="$('.purpose').val(this.value)" required>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-12 form-group">
                                        <label>Department</label>
                                        <input type="text" class="form-control" name="department" value="{{ auth()->user()->department->name }}" disabled required>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-12 form-group">
                                        <label>Section</label>
                                        <input type="text" value="{{ $mrs ? $mrs->section : '' }}" class="form-control" id="section" name="section" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-panel products-cart-panel">
                            <div class="panel-header">
                                <h4>Products in cart</h4>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table modern-table dataTable w-100 mn-wd-1100-f">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col">Priority#</th>
                                                <th scope="col">Product</th>
                                                <th scope="col">Code</th>
                                                <th scope="col">OEM</th>
                                                <th scope="col">Purpose</th>
                                                <th scope="col">PAR To</th>
                                                <th scope="col">Date Needed</th>
                                                <th scope="col">Frequency</th>
                                                <th scope="col">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($orders as $order)
                                                <tr>
                                                    <td class="align-middle priority_no">
                                                        <center>{{ $mrs ? $mrs->priority : '1' }}</center>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="top-cart-item">
                                                            <div class="top-cart-item-desc">
                                                                <div class="top-cart-item-desc-title">{{ $order->product->name }}</div>
                                                                <div>Cost Code: <select class="costcode-option" name="codes[]" required></select></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle">{{ $order->product->code }}</td>
                                                    <td class="align-middle">{{ $order->product->oem }}</td>
                                                    <td class="align-middle">
                                                        <input type="text" value="{{ $order->mrs_details->purpose ?? '' }}" class="form-control purpose purpose-input" required name="item_purpose[]">
                                                    </td>
                                                    <td class="align-middle">
                                                        @php $parTo = $order->mrs_details->par_to ?? ''; @endphp
                                                        <select class="form-select employees" name="par_to[]">
                                                            <option value="N/A">Select an employee</option>
                                                            @if($parTo !== '' && $parTo !== 'N/A')
                                                                <option value="{{ $parTo }}" selected>{{ trim(explode(':', $parTo)[0]) }}</option>
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td class="align-middle">
                                                        <input type="date" value="{{ \Carbon\Carbon::parse($order->mrs_details->date_needed ?? '')->format('Y-m-d') }}" class="form-control date_needed" required name="item_date_needed[]">
                                                    </td>
                                                    <td class="align-middle">
                                                        <select class="form-select" name="frequency[]" required>
                                                            <option value="Daily" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'Daily' ? 'selected' : '' }}>Daily</option>
                                                            <option value="Weekly" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                                            <option value="Monthly" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                                            <option value="Yearly" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'Yearly' ? 'selected' : '' }}>Yearly</option>
                                                            <option value="As Needed" {{ isset($order->mrs_details) && $order->mrs_details->frequency === 'As Needed' ? 'selected' : '' }}>As Needed</option>
                                                        </select>
                                                    </td>
                                                    <td class="align-middle">{{ $order->qty }} {{ $order->product->uom }}(s)</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-panel supporting-details-panel">
                            <div class="panel-header">
                                <h4>Supporting details</h4>
                            </div>
                            <div class="panel-body">
                                <div class="row g-3">
                                    <div class="col-lg-6 col-12 form-group">
                                        <label>Attach files</label>
                                        <input type="file" id="attachment" name="attachment[]" class="form-control" multiple>
                                        <div id="fileList"></div>
                                    </div>

                                    <div class="col-lg-6 col-12 form-group">
                                        <label for="requested_by">Requested by</label>
                                        <select id="requested_by" name="requested_by" class="form-select" required>
                                            <option value="">Select an employee</option>
                                            @if($mrs && $mrs->requested_by)
                                                <option value="{{ $mrs->requested_by }}" selected>{{ trim(explode(':', $mrs->requested_by)[0]) }}</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-12 form-group mb-0">
                                        <label for="notes">Delivery Instruction</label>
                                        <textarea id="notes" class="form-control form-input" name="notes" rows="6" required>{{ $mrs ? $mrs->other_instruction : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside>
                        <div class="summary-card">
                            <div class="summary-header">
                                <h3>Cart Summary</h3>
                            </div>
                            <div class="summary-body">
                                <div class="summary-row">
                                    <span>Items</span>
                                    <strong>{{ $totalqty }}</strong>
                                </div>
                                <div class="summary-row">
                                    <span>Priority</span>
                                    <strong class="priority_no">{{ $mrs ? $mrs->priority : '1' }}</strong>
                                </div>
                                <div class="summary-row">
                                    <span>Code Type</span>
                                    <strong id="summaryCodeType">CC</strong>
                                </div>
                                <div class="summary-total">
                                    <div class="label">Total</div>
                                    <div class="value">{{ $totalqty }}</div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <input type="hidden" name="shipping_type" value="Pickup">

                <div class="action-bar">
                    <input type="submit" class="place-request-btn" value="Place Request">
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
            $('<span class="file-item">').append($('<i class="icon-file-text me-1">'), $('<span>').text(file.name)).appendTo($list);
        });
    });

    $(document).on('change', '#codeType', function () {
        $('#summaryCodeType').text(this.value);
    });
</script>

@endsection