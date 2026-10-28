@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('css/sweetalert.min.css') }}">
    <link rel="stylesheet" href="{{ asset('lib/js-snackbar/js-snackbar.css') }}" type="text/css" />
    <link href="{{ asset('lib/select2/css/select2.min.css') }}" rel="stylesheet">

    <link href="{{ asset('css/selectize.bootstrap2.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.bootstrap3.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.default.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/selectize.legacy.css') }}" type="text/css" rel="stylesheet"/>

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('lib/datatables.net-dt/css/jquery.dataTables.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}" type="text/css" />

    <style>
        .mrs-page {
            --mrs-ink: #222e2b;
            --mrs-deep: #0b1211;
            --mrs-green: #42615b;
            --mrs-gold: #ecd49a;
            --mrs-line: #e5ebe7;
            --mrs-muted: #71807a;
            color: var(--mrs-ink);
            font-family: "DM Sans", sans-serif;
            padding-bottom: 36px;
        }
        .mrs-page .mrs-heading {
            align-items: flex-end;
            display: flex;
            justify-content: space-between;
            margin: 0;
        }
        .mrs-page .mrs-overview {
            align-items: center;
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(0, 3fr) minmax(0, 7fr);
            justify-content: space-between;
            margin: 20px 0 18px;
        }
        .mrs-page .mrs-overview .mrs-heading {
            min-width: 0;
        }
        .mrs-page .mrs-overview .mrs-summary-row {
            align-items: stretch;
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            min-width: 0;
        }
        .mrs-page .mrs-eyebrow {
            color: var(--mrs-green);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.3px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .mrs-page .mrs-heading h1 {
            color: var(--mrs-ink);
            font-size: 21px;
            font-weight: 700;
            margin: 0;
        }
        .mrs-page .mrs-heading p {
            color: var(--mrs-muted);
            font-size: 12px !important;
            line-height: 1.35;
            margin: 4px 0 0;
        }
        .mrs-page .mrs-summary-card {
            background: #fff;
            border: 1px solid var(--mrs-line);
            border-radius: 9px;
            box-shadow: 0 4px 14px rgba(34, 46, 43, .045);
            min-height: 72px;
            padding: 11px 13px 10px !important;
            position: relative;
            text-align: left !important;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
            width: 100%;
        }
        .mrs-page .mrs-summary-card::before {
            background: var(--mrs-gold);
            border-radius: 2px;
            content: '';
            height: 22px;
            left: 0;
            position: absolute;
            top: 16px;
            width: 2px;
        }
        .mrs-page .mrs-summary-card:hover {
            border-color: rgba(66, 97, 91, .3);
            box-shadow: 0 12px 28px rgba(34, 46, 43, .1);
            transform: translateY(-2px);
        }
        .mrs-page .mrs-summary-card h5 {
            align-items: center;
            color: var(--mrs-muted);
            display: flex;
            font-size: 9px;
            font-weight: 700;
            gap: 5px;
            letter-spacing: .3px;
            margin: 0 0 5px;
            overflow: hidden;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .mrs-page .mrs-summary-card h5 i {
            color: var(--mrs-green);
            flex: 0 0 auto;
            font-size: 13px;
        }
        .mrs-page .mrs-summary-card .display-6 {
            color: var(--mrs-ink) !important;
            font-size: 21px;
            font-weight: 700;
            line-height: 1;
        }
        .mrs-page .mrs-summary-card h5 span {
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mrs-page .mrs-toolbar {
            align-items: center;
            background: #fff;
            border: 1px solid var(--mrs-line);
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(34, 46, 43, .05);
            display: flex;
            gap: 10px;
            justify-content: space-between;
            margin: 18px 0 14px;
            padding: 14px;
        }
        .mrs-page .mrs-filter-group {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }
        .mrs-page .mrs-toolbar-label {
            color: var(--mrs-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
        }
        .mrs-page .mrs-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }
        .mrs-page .mrs-actions .button {
            align-items: center;
            background: var(--mrs-green) !important;
            border: 1px solid var(--mrs-green) !important;
            border-radius: 8px;
            color: #fff !important;
            display: inline-flex;
            font-size: 12px;
            gap: 7px;
            height: 40px;
            justify-content: center;
            margin: 0 !important;
            padding: 0 15px;
            transition: background-color .2s ease, transform .2s ease;
        }
        .mrs-page .mrs-actions .button i {
            flex: 0 0 auto;
            font-size: 14px;
            line-height: 1;
        }
        .mrs-page .mrs-actions .button:last-child {
            background: transparent !important;
            color: var(--mrs-green) !important;
        }
        .mrs-page .mrs-actions .button:hover {
            background: var(--mrs-deep) !important;
            border-color: var(--mrs-deep) !important;
            color: #fff !important;
            transform: translateY(-2px);
        }
        .mrs-page .mrs-table-shell {
            background: #fff;
            border: 1px solid var(--mrs-line);
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(34, 46, 43, .06);
            overflow: hidden;
            padding: 6px 14px 14px;
        }
        .mrs-page #inventoryTable {
            border: 0;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--mrs-ink);
            font-size: 13px;
            margin-top: 8px !important;
            min-width: 900px;
            width: 100% !important;
        }
        .mrs-page .dataTables_wrapper .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
        .mrs-page #inventoryTable thead th {
            background: #f3f6f4;
            border: 0 !important;
            border-bottom: 1px solid var(--mrs-line) !important;
            color: var(--mrs-green);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .7px;
            padding: 14px 12px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .mrs-page #inventoryTable tbody td {
            border: 0 !important;
            border-bottom: 1px solid #edf1ee !important;
            padding: 15px 12px;
            vertical-align: middle;
        }
        .mrs-page #inventoryTable tbody td:nth-child(2) {
            white-space: nowrap;
        }
        .mrs-page #inventoryTable tbody td .mrs-table-value {
            font-family: inherit;
            font-size: 13px;
            font-weight: 400;
            line-height: 1.4;
        }
        .mrs-page #inventoryTable tbody td .mrs-row-actions {
            align-items: center;
            display: flex;
            flex-wrap: nowrap;
            gap: 6px;
            white-space: nowrap;
        }
        .mrs-page #inventoryTable tbody td .mrs-row-actions > a,
        .mrs-page #inventoryTable tbody td .mrs-row-actions > span {
            align-items: center;
            border: 1px solid #dbe4df;
            border-radius: 7px;
            box-sizing: border-box;
            display: inline-flex;
            font-size: 13px;
            height: 30px;
            justify-content: center;
            line-height: 1;
            min-width: 30px;
            padding: 0 8px;
            text-decoration: none;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
        }
        .mrs-page #inventoryTable tbody td .mrs-row-actions > a {
            background: #fff;
            color: var(--mrs-green);
        }
        .mrs-page #inventoryTable tbody td .mrs-row-actions > a:hover,
        .mrs-page #inventoryTable tbody td .mrs-row-actions > a:focus {
            background: #f1f6f3;
            border-color: var(--mrs-green);
            color: var(--mrs-deep);
            transform: translateY(-1px);
        }
        .mrs-page #inventoryTable tbody td .mrs-row-actions > span.text-success {
            background: #effaf3;
            border-color: #b9e3c6;
            color: #238447 !important;
        }
        .mrs-page #inventoryTable tbody tr:last-child td {
            border-bottom: 0 !important;
        }
        .mrs-page #inventoryTable tbody tr {
            transition: background-color .15s ease;
        }
        .mrs-page #inventoryTable tbody tr:hover td {
            background: #f8faf8;
        }
        .mrs-page .dataTables_wrapper .dataTables_length,
        .mrs-page .dataTables_wrapper .dataTables_filter {
            color: var(--mrs-muted);
            font-size: 12px;
            padding: 10px 0 4px;
        }
        .mrs-page .dataTables_wrapper .dataTables_filter input,
        .mrs-page .dataTables_wrapper .dataTables_length select {
            background: #f8faf8;
            border: 1px solid var(--mrs-line);
            border-radius: 7px;
            color: var(--mrs-ink);
            min-height: 34px;
            outline: none;
            padding: 5px 9px;
        }
        .mrs-page .dataTables_wrapper .dataTables_filter {
            align-items: center;
            display: flex;
            justify-content: flex-end;
            margin-left: auto;
        }
        .mrs-page .dataTables_wrapper .dataTables_length {
            align-items: center;
            display: flex;
            margin-right: auto;
        }
        .mrs-page .dataTables_wrapper .dataTables_length label {
            align-items: center;
            display: flex;
            gap: 8px;
            margin: 0;
            white-space: nowrap;
        }
        .mrs-page .dataTables_wrapper .dataTables_length select {
            background: #fff;
            border-color: #d7e2dc;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(34, 46, 43, .04);
            color: var(--mrs-ink);
            cursor: pointer;
            font-size: 12px;
            margin: 0 !important;
            min-width: 66px;
            padding: 7px 25px 7px 10px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .mrs-page .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--mrs-green);
            box-shadow: 0 0 0 3px rgba(66, 97, 91, .1);
            outline: none;
        }
        .mrs-page .dataTables_wrapper .dataTables_filter label {
            align-items: center;
            display: flex;
            gap: 8px;
            margin: 0;
            white-space: nowrap;
        }
        .mrs-page .dataTables_wrapper .dataTables_filter input {
            background: #fff;
            border-color: #d7e2dc;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(34, 46, 43, .04);
            font-size: 12px;
            margin-left: 0 !important;
            min-width: 240px;
            padding: 7px 11px;
            transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
        }
        .mrs-page .dataTables_wrapper .dataTables_filter input::placeholder {
            color: #9aa9a3;
        }
        .mrs-page .dataTables_wrapper .dataTables_filter input:focus {
            background: #fff;
            border-color: var(--mrs-green);
            box-shadow: 0 0 0 3px rgba(66, 97, 91, .1);
        }
        .mrs-page .dataTables_wrapper .dataTables_info {
            color: var(--mrs-muted);
            font-size: 12px;
            padding-top: 15px;
        }
        .mrs-page .dataTables_wrapper .dataTables_paginate {
            padding-top: 10px;
        }
        .mrs-page .dataTables_wrapper .paginate_button {
            border: 1px solid transparent !important;
            border-radius: 6px !important;
            color: var(--mrs-green) !important;
            font-size: 12px;
            margin-left: 3px;
        }
        .mrs-page .dataTables_wrapper .paginate_button.current,
        .mrs-page .dataTables_wrapper .paginate_button:hover {
            background: var(--mrs-green) !important;
            border-color: var(--mrs-green) !important;
            color: #fff !important;
        }
        .mrs-page .imf-chips {
            gap: 7px;
            margin: 0;
        }
        .mrs-page .imf-chip {
            background: #f7f9f7;
            border-color: var(--mrs-line);
            border-radius: 7px;
            color: var(--mrs-muted);
            font-size: 11px;
            padding: 7px 11px;
        }
        .mrs-page .imf-chip:hover {
            border-color: rgba(66, 97, 91, .4);
            color: var(--mrs-green);
        }
        .mrs-page .imf-chip.active {
            background: var(--mrs-green);
            border-color: var(--mrs-green);
            color: #fff;
        }
        @media (max-width: 767.98px) {
            .mrs-page .mrs-overview {
                align-items: stretch;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            .mrs-page .mrs-overview .mrs-heading { flex-basis: auto; }
            .mrs-page .mrs-overview .mrs-summary-row {
                grid-template-columns: 1fr;
            }
            .mrs-page .mrs-heading {
                align-items: flex-start;
            }
            .mrs-page .mrs-heading h1 { font-size: 21px; }
            .mrs-page .mrs-toolbar {
                align-items: stretch;
                flex-direction: column;
            }
            .mrs-page .mrs-filter-group { justify-content: flex-start; }
            .mrs-page .mrs-actions .button { flex: 1 1 180px; }
            .mrs-page .mrs-table-shell { padding: 6px 8px 12px; }
            .mrs-page .dataTables_wrapper .dataTables_filter,
            .mrs-page .dataTables_wrapper .dataTables_filter label,
            .mrs-page .dataTables_wrapper .dataTables_filter input {
                width: 100%;
            }
            .mrs-page .dataTables_wrapper .dataTables_length,
            .mrs-page .dataTables_wrapper .dataTables_length label,
            .mrs-page .dataTables_wrapper .dataTables_length select {
                width: 100%;
            }
            .mrs-page .dataTables_wrapper .dataTables_filter input {
                min-width: 0;
            }
        }

        /* ---- MRS list: status chips + fetching loader (parity with IMF) ---- */
        .imf-chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0 8px; }
        .imf-chip { cursor: pointer; border: 1px solid #dee2e6; background: #fff; color: #495057; border-radius: 20px; padding: 5px 16px; font-size: 12px; font-weight: 600; letter-spacing: .3px; transition: all .15s ease; user-select: none; }
        .imf-chip:hover { border-color: #adb5bd; }
        .imf-chip.active { background: #212529; border-color: #212529; color: #fff; }
        .dataTables_wrapper { position: relative; }
        .dataTables_processing { position: absolute !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: auto !important; height: auto !important; margin: 0 !important; padding: 0 !important; background: rgba(255,255,255,0.80); z-index: 20; border: 0 !important; box-shadow: none !important; color: transparent !important; }
        .dataTables_processing .imf-loader { position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%,-50%) !important; display: flex; flex-direction: column; align-items: center; gap: 10px; color: #3b7ddd; font-weight: 600; font-size: 13px; white-space: nowrap; }
        .imf-loader .imf-spin { width: 38px; height: 38px; border: 3px solid #e3e9f2; border-top-color: #3b7ddd; border-radius: 50%; animation: imfspin .8s linear infinite; }
        @keyframes imfspin { to { transform: rotate(360deg); } }
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

        /* ---- Cancel MRS confirmation ---- */
        #cancel_order .modal-dialog {
            max-width: 430px;
        }

        #cancel_order .modal-content {
            border-radius: 16px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
        }

        #cancel_order .modal-header {
            background: linear-gradient(135deg, #203f3a 0%, #42615b 100%);
            padding: 16px 22px;
        }

        #cancel_order .modal-header .modal-title {
            font-size: 17px;
            letter-spacing: .2px;
        }

        #cancel_order .modal-body {
            max-height: calc(100vh - 185px);
            overflow-y: auto;
            padding: 22px;
            scrollbar-color: #b7c8c1 transparent;
            scrollbar-width: thin;
            text-align: center;
        }

        #cancel_order .cancel-confirm-title {
            color: #1e293b;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        #cancel_order .cancel-confirm-message {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        #cancel_order .modal-footer {
            gap: 8px;
            padding: 12px 22px;
        }

        #cancel_order .modal-footer .btn {
            min-width: 88px;
            margin: 0;
        }

        #cancel_order .btn-cancel-confirm {
            background: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }

        #cancel_order .btn-cancel-confirm:hover,
        #cancel_order .btn-cancel-confirm:focus {
            background: #bb2d3b;
            border-color: #b02a37;
            color: #fff;
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

        /* ============================================================
           MRS detail and edit modal refinements
           ============================================================ */
        #viewdetail .modal-content,
        #editdetail .modal-content {
            border-radius: 16px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
        }

        #viewdetail .modal-header,
        #editdetail .modal-header {
            background: linear-gradient(135deg, #203f3a 0%, #42615b 100%);
            padding: 16px 22px;
        }

        #viewdetail .modal-header .modal-title,
        #editdetail .modal-header .modal-title {
            font-size: 17px;
            letter-spacing: .2px;
        }

        #editdetail .modal-header small {
            align-items: center;
            background: rgba(255, 255, 255, .11);
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 7px;
            display: inline-flex;
            font-size: 11px;
            gap: 4px;
            margin-right: 0 !important;
            min-height: 30px;
            padding: 5px 9px;
        }

        #viewdetail .modal-body,
        #editdetail .modal-body {
            max-height: calc(100vh - 185px);
            overflow-y: auto;
            padding: 22px;
            scrollbar-color: #b7c8c1 transparent;
            scrollbar-width: thin;
        }

        #viewdetail .request-details {
            background: #f7faf8;
            border: 1px solid #e0e9e4;
            border-radius: 11px;
            box-shadow: 0 2px 8px rgba(34, 46, 43, .03);
            padding: 12px 15px;
        }

        #viewdetail .request-details > span {
            display: grid;
            grid-template-columns: minmax(118px, 36%) minmax(0, 1fr);
            min-height: 31px;
            padding: 4px 0;
        }

        #viewdetail .request-details > span + span {
            border-top: 1px solid #e7eeea;
        }

        #viewdetail .request-details strong {
            align-self: start;
            color: #71807a;
            font-size: 10px;
            letter-spacing: .55px;
            padding: 4px 12px 4px 0;
        }

        #viewdetail .request-details .detail-value {
            color: #263532;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.45;
            overflow-wrap: anywhere;
            padding: 4px 0;
        }

        #viewdetail .request-details .detail-value a {
            align-items: center;
            background: #fff;
            border: 1px solid #d8e4de;
            border-radius: 6px;
            color: #42615b;
            display: inline-flex;
            font-size: 12px;
            margin: 2px 6px 2px 0;
            padding: 5px 8px;
            text-decoration: none;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease;
        }

        #viewdetail .request-details .detail-value a:hover {
            background: #edf5f1;
            border-color: #9db8ad;
            color: #203f3a;
        }

        #viewdetail .table-modal,
        #editdetail .table-modal {
            background: #fff;
            border-radius: 11px;
            overflow: hidden;
        }

        #viewdetail .table-modal thead th,
        #editdetail .table-modal thead th {
            background: #edf3f0 !important;
            color: #42615b !important;
            font-size: 10px !important;
            letter-spacing: .6px;
            padding: 12px !important;
        }

        #viewdetail .table-modal tbody td,
        #viewdetail .table-modal tbody th {
            color: #34443f;
            font-size: 12.5px;
            line-height: 1.4;
        }

        #viewdetail .table-modal tbody tr:nth-child(even) td,
        #viewdetail .table-modal tbody tr:nth-child(even) th {
            background: #fbfcfb;
        }

        #viewdetail .table-modal tbody tr:hover td,
        #viewdetail .table-modal tbody tr:hover th {
            background: #f1f7f4 !important;
        }

        #viewdetail .modal-body > .row h5 {
            color: #263532;
            font-size: 12px;
            letter-spacing: 1px;
            margin-top: 8px;
        }

        #viewdetail .dashboard-widget {
            background: #fff;
            border-color: #dfe8e3;
            border-radius: 11px;
            box-shadow: 0 3px 12px rgba(34, 46, 43, .05);
        }

        #viewdetail .dashboard-widget .card-body {
            padding: 14px;
        }

        #viewdetail .dashboard-widget h6 {
            color: #263532;
            font-size: 12px;
            line-height: 1.4;
        }

        #editdetail .editdetailbody > .row {
            background: #f8fbf9;
            border: 1px solid #e2ebe6;
            border-radius: 11px;
            margin: 0 0 18px;
            padding: 17px 7px 3px;
        }

        #editdetail .editdetailbody > .row > [class*="col-"] + [class*="col-"] {
            border-left: 1px solid #e1eae5;
        }

        #editdetail .transaction-status {
            padding: 0 10px;
        }

        #editdetail .form-group {
            margin-bottom: 15px;
        }

        #editdetail .form-group > label {
            color: #52645e;
            font-size: 10px;
            letter-spacing: .65px;
        }

        #editdetail .form-control,
        #editdetail .form-select {
            background-color: #fff;
            min-height: 38px;
        }

        #editdetail textarea.form-control {
            min-height: 86px;
            resize: vertical;
        }

        #editdetail #planner_note_box {
            border: 1px solid #e8d49a !important;
            border-left: 4px solid #d5a929 !important;
            border-radius: 10px !important;
            box-shadow: 0 3px 10px rgba(124, 74, 3, .06) !important;
        }

        #editdetail #add_item_mrs {
            background: #42615b;
            border: 1px solid #42615b;
            box-shadow: 0 3px 8px rgba(66, 97, 91, .18);
            color: #fff;
            margin: 14px 0 0;
            padding: 8px 14px;
            transition: background-color .15s ease, transform .15s ease;
        }

        #editdetail #add_item_mrs:hover {
            background: #203f3a;
            border-color: #203f3a;
            transform: translateY(-1px);
        }

        #viewdetail .modal-footer,
        #editdetail .modal-footer {
            padding: 12px 22px;
        }

        #viewdetail .modal-footer .btn,
        #editdetail .modal-footer .btn {
            min-width: 88px;
        }

        @media (max-width: 767.98px) {
            #viewdetail .modal-body,
            #editdetail .modal-body {
                padding: 15px;
            }

            #editdetail .modal-header small {
                flex: 1 1 100%;
            }

            #viewdetail .request-details > span {
                grid-template-columns: 1fr;
                gap: 1px;
            }

            #viewdetail .request-details strong,
            #viewdetail .request-details .detail-value {
                padding-right: 0;
            }

            #editdetail .editdetailbody > .row > [class*="col-"] + [class*="col-"] {
                border-left: 0;
                border-top: 1px solid #e1eae5;
                margin-top: 5px;
                padding-top: 17px;
            }

            #editdetail .transaction-status {
                padding: 0 3px;
            }
        }
    </style>
@endsection
@section('content')
<div class="container-fluid mrs-page">
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
            <div class="mrs-overview">
                <div class="mrs-heading">
                    <div>
                        <div class="mrs-eyebrow">Materials Request System</div>
                        <h1>My Requests</h1>
                        <p>Track, manage, and review your department purchase requests.</p>
                    </div>
                </div>

                <!-- DASHBOARD SUMMARY -->
                <div class="mrs-summary-row">
                    <div class="card mrs-summary-card">
                        <h5 class="fw-bold"><span>Total of Newly-submitted to WFS</span></h5>
                        <div class="display-6 text-success">{{ $postedCount }}</div>
                    </div>
                    <div class="card mrs-summary-card">
                        <h5 class="fw-bold"><span>Overdue In-Progress (2+ Days)</span></h5>
                        <div class="display-6 text-danger">{{ $inProgressOverdue }}</div>
                    </div>
                    <div class="card mrs-summary-card">
                        <h5 class="fw-bold"><span>% Overdue</span></h5>
                        <div class="display-6 text-primary">{{ $percentageOverdue }}%</div>
                    </div>
                </div>
            </div>
            <div class="mrs-toolbar">
                <div class="mrs-actions">
                <a href="{{ route('cart.front.show') }}" class="button button-dark button-border button-circle button-xlarge fw-bold fs-14-f nols text-dark h-text-light notextshadow"><i class="icon-line-plus" aria-hidden="true"></i><span>Add New MRS</span></a>
                
                <a href="{{ route('export.users') }}?type=deptuser" class="button button-dark button-border button-circle button-xlarge fw-bold fs-14-f nols text-dark h-text-light notextshadow"><i class="icon-line-download" aria-hidden="true"></i><span>Export As Excel</span></a>
                </div>
                <div class="mrs-filter-group">
                    <span class="mrs-toolbar-label">Request status</span>
                    <div class="imf-chips" id="mrsChips">
                <span class="imf-chip active" data-filter="">All</span>
                <span class="imf-chip" data-filter="SAVED">Saved</span>
                <span class="imf-chip" data-filter="POSTED">Posted</span>
                <span class="imf-chip" data-filter="IN-PROGRESS">In-Progress</span>
                {{-- "HOLD", not "ON-HOLD": the MCD holds are stored without the hyphen
                     ("REQUEST ON HOLD (Hold by MCD Planner)", "HOLD (For MCD Planner re-edit)")
                     and were being missed by this chip entirely. --}}
                <span class="imf-chip" data-filter="HOLD">On-Hold</span>
                <span class="imf-chip" data-filter="REQUEST ON HOLD (Hold by MCD Planner)">Returned to Me</span>
                <span class="imf-chip" data-filter="REVISED MRS">Revised</span>
                <span class="imf-chip" data-filter="APPROVED">Approved</span>
                <span class="imf-chip" data-filter="CANCELLED">Cancelled</span>
                    </div>
                </div>
            </div>


            <div class="mrs-table-shell">
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
                <tbody></tbody>
            </table>
            </div>
        </div>
    </div>
</div>

{{-- Shared "View Details" modal (content loaded on demand by view_items) --}}
<div class="modal fade bs-example-modal-centered modal-size" id="viewdetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="viewdetailContent"></div>
    </div>
</div>

@include('theme.pages.customer.edit-mrs')
@include('theme.pages.customer.jobcostcodes-modal')

<div class="modal fade bs-example-modal-centered" id="cancel_order" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cancel-confirm-modal">
            <form action="{{route('my-account.cancel-order')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Cancel MRS</h4>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <p class="cancel-confirm-title">Cancel this request?</p>
                    <p class="cancel-confirm-message">Are you sure you want to cancel this MRS? This action cannot be undone.</p>
                    <input type="hidden" id="orderid" name="orderid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep Request</button>
                    <button type="submit" class="btn btn-cancel-confirm">Yes, Cancel</button>
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
    <!-- DataTables -->
    <script src="{{ asset('lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
    <script src="{{ asset('lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>
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
            var url = "{{ route('profile.sales.details', ['id' => ':id']) }}".replace(':id', salesID);
            $('#viewdetailContent').html('<div class="p-5 text-center"><i class="fa fa-spinner fa-spin" style="font-size:26px;color:#3b7ddd;"></i></div>');
            $('#viewdetail').modal('show');
            $.ajax({
                url: url,
                type: 'GET',
                success: function(html){
                    $('#viewdetailContent').html(html);
                },
                error: function(){
                    $('#viewdetailContent').html('<div class="p-4 text-danger text-center">Unable to load details.</div>');
                }
            });
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
                    $("#request_status").html(data.requestor_status || headers.status)
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

        $(document).on('click', '.print', function(evt) {
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

<script>
    // ===== MRS list: DataTables server-side processing (parity with IMF) =====
    $.extend($.fn.dataTableExt.oStdClasses, {
        'sPageEllipsis': 'paginate_ellipsis',
        'sPageNumber': 'paginate_number',
        'sPageNumbers': 'paginate_numbers'
    });
    $.fn.dataTableExt.oPagination.ellipses = {
        'oDefaults': { 'iShowPages': 3 },
        'fnClickHandler': function(e) {
            var fnCallbackDraw = e.data.fnCallbackDraw, oSettings = e.data.oSettings, sPage = e.data.sPage;
            if ($(this).is('[disabled]')) { return false; }
            oSettings.oApi._fnPageChange(oSettings, sPage);
            fnCallbackDraw(oSettings);
            return true;
        },
        'fnInit': function(oSettings, nPager, fnCallbackDraw) {
            var oClasses = oSettings.oClasses, oLang = oSettings.oLanguage.oPaginate, that = this;
            var iShowPages = oSettings.oInit.iShowPages || this.oDefaults.iShowPages, iShowPagesHalf = Math.floor(iShowPages / 2);
            $.extend(oSettings, { _iShowPages: iShowPages, _iShowPagesHalf: iShowPagesHalf });
            var oFirst = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageFirst + '">' + oLang.sFirst + '</a>'),
                oPrevious = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPagePrevious + '">' + oLang.sPrevious + '</a>'),
                oNumbers = $('<span class="' + oClasses.sPageNumbers + '"></span>'),
                oNext = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageNext + '">' + oLang.sNext + '</a>'),
                oLast = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageLast + '">' + oLang.sLast + '</a>');
            oFirst.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'first' }, that.fnClickHandler);
            oPrevious.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'previous' }, that.fnClickHandler);
            oNext.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'next' }, that.fnClickHandler);
            oLast.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'last' }, that.fnClickHandler);
            $(nPager).append(oFirst, oPrevious, oNumbers, oNext, oLast);
        },
        'fnUpdate': function(oSettings, fnCallbackDraw) {
            var oClasses = oSettings.oClasses, that = this, tableWrapper = oSettings.nTableWrapper;
            this.fnUpdateState(oSettings);
            if (oSettings._iCurrentPage === 1) {
                $('.' + oClasses.sPageFirst, tableWrapper).attr('disabled', true);
                $('.' + oClasses.sPagePrevious, tableWrapper).attr('disabled', true);
            } else {
                $('.' + oClasses.sPageFirst, tableWrapper).removeAttr('disabled');
                $('.' + oClasses.sPagePrevious, tableWrapper).removeAttr('disabled');
            }
            if (oSettings._iTotalPages === 0 || oSettings._iCurrentPage === oSettings._iTotalPages) {
                $('.' + oClasses.sPageNext, tableWrapper).attr('disabled', true);
                $('.' + oClasses.sPageLast, tableWrapper).attr('disabled', true);
            } else {
                $('.' + oClasses.sPageNext, tableWrapper).removeAttr('disabled');
                $('.' + oClasses.sPageLast, tableWrapper).removeAttr('disabled');
            }
            var i, oNumber, oNumbers = $('.' + oClasses.sPageNumbers, tableWrapper);
            oNumbers.html('');
            for (i = oSettings._iFirstPage; i <= oSettings._iLastPage; i++) {
                oNumber = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageNumber + '">' + oSettings.fnFormatNumber(i) + '</a>');
                if (oSettings._iCurrentPage === i) { oNumber.attr('active', true).attr('disabled', true); }
                else { oNumber.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': i - 1 }, that.fnClickHandler); }
                oNumbers.append(oNumber);
            }
            if (1 < oSettings._iFirstPage) { oNumbers.prepend('<span class="' + oClasses.sPageEllipsis + '">...</span>'); }
            if (oSettings._iLastPage < oSettings._iTotalPages) { oNumbers.append('<span class="' + oClasses.sPageEllipsis + '">...</span>'); }
        },
        'fnUpdateState': function(oSettings) {
            var iCurrentPage = Math.ceil((oSettings._iDisplayStart + 1) / oSettings._iDisplayLength),
                iTotalPages = Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength),
                iFirstPage = iCurrentPage - oSettings._iShowPagesHalf, iLastPage = iCurrentPage + oSettings._iShowPagesHalf;
            if (iTotalPages < oSettings._iShowPages) { iFirstPage = 1; iLastPage = iTotalPages; }
            else if (iFirstPage < 1) { iFirstPage = 1; iLastPage = oSettings._iShowPages; }
            else if (iLastPage > iTotalPages) { iFirstPage = (iTotalPages - oSettings._iShowPages) + 1; iLastPage = iTotalPages; }
            $.extend(oSettings, { _iCurrentPage: iCurrentPage, _iTotalPages: iTotalPages, _iFirstPage: iFirstPage, _iLastPage: iLastPage });
        }
    };

    $(function () {
        'use strict';
        var statusFilter = '';

        var table = $('#inventoryTable').DataTable({
            order: [[2, 'desc']],
            pagingType: 'ellipses',
            processing: true,
            serverSide: true,
            columnDefs: [{ orderable: false, targets: [1, 5] }],
            ajax: {
                url: "{{ route('profile.sales.data') }}",
                type: 'GET',
                data: function (d) { d.status_filter = statusFilter; }
            },
            columns: [
                { data: 'mrs_no' },
                { data: 'pa' },
                { data: 'created' },
                { data: 'remarks' },
                { data: 'status' },
                { data: 'options', orderable: false },
            ],
            language: {
                searchPlaceholder: 'Search MRS# / PA# / status',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                emptyTable: 'No MRS found.',
                zeroRecords: 'No matching MRS found.',
                paginate: {
                    first: `<i class="icon-line-chevrons-left"></i>`,
                    next: `<i class="icon-line-chevron-right"></i>`,
                    previous: `<i class="icon-line-chevron-left"></i>`,
                    last: `<i class="icon-line-chevrons-right"></i>`,
                },
                processing: '<div class="imf-loader"><div class="imf-spin"></div><span>Loading requests…</span></div>'
            },
            dom: '<"row mb-2"<"col-md-6"l><"col-md-6 d-flex justify-content-end"f>>r<"table-responsive mb-2"t>ip',
        });

        $('#mrsChips').on('click', '.imf-chip', function () {
            $('#mrsChips .imf-chip').removeClass('active');
            $(this).addClass('active');
            statusFilter = $(this).data('filter') || '';
            table.ajax.reload();
        });
    });
</script>
@endsection

