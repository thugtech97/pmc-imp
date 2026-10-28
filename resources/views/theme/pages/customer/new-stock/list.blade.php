@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('css/sweetalert.min.css') }}">
    <!-- DataTable Stylesheets -->
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-dt/css/jquery.dataTables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-buttons/css/buttons.bootstrap.min.css') }}" type="text/css" />
    <style>
        .imf-page-head {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px; margin-bottom: 4px;
        }
        .imf-page-head h3 { margin: 0; font-weight: 700; }
        .imf-page-head p { margin: 2px 0 0; color: #8a94a6; font-size: 13px; }
        .imf-chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0 8px; }
        .imf-chip {
            cursor: pointer; border: 1px solid #dee2e6; background: #fff; color: #495057;
            border-radius: 20px; padding: 5px 16px; font-size: 12px; font-weight: 600;
            letter-spacing: .3px; transition: all .15s ease; user-select: none;
        }
        .imf-chip:hover { border-color: #adb5bd; }
        .imf-chip.active { background: #212529; border-color: #212529; color: #fff; }
        .imf-type { font-size: 11px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase;
            padding: 2px 10px; border-radius: 6px; }
        .imf-type-new { background: #e7f1ff; color: #3b7ddd; }
        .imf-type-update { background: #fff3cd; color: #997404; }
        #inventoryTable td { vertical-align: middle; }
        .imf-action { color: #495057; font-size: 17px; margin: 0 5px; transition: color .15s; }
        .imf-action:hover { color: #212529; }
        .imf-page #inventoryTable tbody td .imf-row-actions {
            align-items: center;
            display: flex;
            flex-wrap: nowrap;
            gap: 6px;
            justify-content: flex-end;
            white-space: nowrap;
        }
        .imf-page #inventoryTable tbody td .imf-row-actions > a {
            align-items: center;
            background: #fff;
            border: 1px solid #dbe4df;
            border-radius: 7px;
            box-sizing: border-box;
            color: var(--imf-green, #42615b);
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
        .imf-page #inventoryTable tbody td .imf-row-actions > a:hover,
        .imf-page #inventoryTable tbody td .imf-row-actions > a:focus {
            background: #f1f6f3;
            border-color: var(--imf-green, #42615b);
            color: var(--imf-deep, #0b1211);
            transform: translateY(-1px);
        }
        .imf-empty { text-align: center; padding: 40px 10px; color: #adb5bd; }
        .imf-empty i { font-size: 42px; display: block; margin-bottom: 10px; }

        /* Fetching loader overlay (DataTables server-side processing) */
        .dataTables_wrapper { position: relative; }
        /* Let DataTables toggle visibility (display block/none) — we only restyle it as a full overlay. */
        .dataTables_processing {
            position: absolute !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            width: auto !important; height: auto !important; margin: 0 !important; padding: 0 !important;
            background: rgba(255, 255, 255, 0.80); z-index: 20;
            border: 0 !important; box-shadow: none !important; color: transparent !important;
        }
        .dataTables_processing .imf-loader {
            position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important;
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            color: #3b7ddd; font-weight: 600; font-size: 13px; white-space: nowrap;
        }
        .imf-loader .imf-spin { width: 38px; height: 38px; border: 3px solid #e3e9f2; border-top-color: #3b7ddd; border-radius: 50%; animation: imfspin .8s linear infinite; }
        @keyframes imfspin { to { transform: rotate(360deg); } }

        /* Match the Materials Request System visual language. */
        .imf-page {
            --imf-ink: #222e2b;
            --imf-deep: #0b1211;
            --imf-green: #42615b;
            --imf-gold: #ecd49a;
            --imf-line: #e5ebe7;
            --imf-muted: #71807a;
            color: var(--imf-ink);
            font-family: "DM Sans", sans-serif;
            padding-bottom: 36px;
        }
        .imf-page .imf-page-head {
            align-items: flex-end;
            margin: 28px 0 22px;
        }
        .imf-page .imf-page-head h3 {
            color: var(--imf-ink);
            font-size: 30px;
            font-weight: 700;
        }
        .imf-page .imf-page-head p {
            color: var(--imf-muted);
            font-size: 12px !important;
            margin: 7px 0 0;
        }
        .imf-page .imf-page-head > div:first-child::before {
            color: var(--imf-green);
            content: 'INVENTORY MAINTENANCE';
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.6px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .imf-page .imf-page-head > div:first-child { flex: 1; }
        .imf-page .imf-page-head > a {
            background: var(--imf-green) !important;
            border: 1px solid var(--imf-green) !important;
            border-radius: 8px;
            color: #fff !important;
            font-size: 12px;
            height: 40px;
            padding: 0 15px;
            transition: background-color .2s ease, transform .2s ease;
        }
        .imf-page .imf-page-head > a:hover {
            background: var(--imf-deep) !important;
            border-color: var(--imf-deep) !important;
            transform: translateY(-2px);
        }
        .imf-page .imf-chips {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
        }
        .imf-page .imf-chip {
            background: #fff;
            border: 1px solid var(--imf-line);
            border-radius: 7px;
            color: var(--imf-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .4px;
            padding: 7px 12px;
            text-transform: uppercase;
        }
        .imf-page .imf-chip:hover,
        .imf-page .imf-chip.active {
            background: var(--imf-green);
            border-color: var(--imf-green);
            color: #fff;
        }
        .imf-page #imfChips .imf-chip {
            text-transform: none;
        }
        .imf-page .imf-toolbar {
            align-items: center;
            background: #fff;
            border: 1px solid var(--imf-line);
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(34, 46, 43, .05);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin: 18px 0 14px;
            padding: 14px;
        }
        .imf-page .imf-toolbar-label {
            color: var(--imf-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            margin-right: 2px;
            text-transform: uppercase;
        }
        .imf-page .imf-table-shell {
            background: #fff;
            border: 1px solid var(--imf-line);
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(34, 46, 43, .06);
            overflow: hidden;
            padding: 6px 14px 14px;
        }
        .imf-page #inventoryTable {
            border: 0;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--imf-ink);
            font-size: 13px;
            margin-top: 8px !important;
        }
        .imf-page #inventoryTable thead th {
            background: #f3f6f4;
            border: 0 !important;
            border-bottom: 1px solid var(--imf-line) !important;
            color: var(--imf-green);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .7px;
            padding: 14px 12px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .imf-page #inventoryTable tbody td {
            border: 0 !important;
            border-bottom: 1px solid #edf1ee !important;
            padding: 15px 12px;
            vertical-align: middle;
        }
        .imf-page #inventoryTable tbody tr:last-child td { border-bottom: 0 !important; }
        .imf-page #inventoryTable tbody tr { transition: background-color .15s ease; }
        .imf-page #inventoryTable tbody tr:hover td { background: #f8faf8; }
        .imf-page .dataTables_wrapper .dataTables_length,
        .imf-page .dataTables_wrapper .dataTables_filter {
            color: var(--imf-muted);
            font-size: 12px;
            padding: 10px 0 4px;
        }
        .imf-page .dataTables_wrapper .dataTables_filter input,
        .imf-page .dataTables_wrapper .dataTables_length select {
            background: #f8faf8;
            border: 1px solid var(--imf-line);
            border-radius: 7px;
            color: var(--imf-ink);
            min-height: 34px;
            outline: none;
            padding: 5px 9px;
        }
        .imf-page .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--imf-green);
            box-shadow: 0 0 0 3px rgba(66, 97, 91, .1);
        }
        .imf-page .dataTables_wrapper .dataTables_info {
            color: var(--imf-muted);
            font-size: 12px;
            padding-top: 15px;
        }
        .imf-page .dataTables_wrapper .dataTables_paginate { padding-top: 10px; }
        .imf-page .dataTables_wrapper .paginate_button {
            border: 1px solid transparent !important;
            border-radius: 6px !important;
            color: var(--imf-green) !important;
            font-size: 12px;
            margin-left: 3px;
        }
        .imf-page .dataTables_wrapper .paginate_button.current,
        .imf-page .dataTables_wrapper .paginate_button:hover {
            background: var(--imf-green) !important;
            border-color: var(--imf-green) !important;
            color: #fff !important;
        }
        @media (max-width: 767.98px) {
            .imf-page .imf-page-head { align-items: flex-start; }
            .imf-page .imf-page-head > a { width: 100%; }
            .imf-page .imf-toolbar { align-items: flex-start; flex-direction: column; }
            .imf-page .imf-toolbar-label { margin: 0; }
            .imf-page .imf-table-shell { padding-left: 8px; padding-right: 8px; }
        }

        /* Keep the inventory list header consistent with the MRS page. */
        .imf-page .imf-page-head {
            align-items: center;
            gap: 24px;
            margin: 20px 0 18px;
        }
        .imf-page .imf-page-head > div:first-child::before {
            color: var(--imf-green);
            content: 'INVENTORY MAINTENANCE SYSTEM';
            font-size: 9px;
            letter-spacing: 1.3px;
            margin-bottom: 5px;
        }
        .imf-page .imf-page-head h3 {
            font-size: 21px;
            line-height: 1.2;
            margin: 0;
        }
        .imf-page .imf-page-head p {
            font-size: 12px !important;
            line-height: 1.35;
            margin: 4px 0 0;
        }
        .imf-page .imf-page-head > a {
            align-items: center;
            display: inline-flex;
            flex: 0 0 auto;
            gap: 7px;
            justify-content: center;
            white-space: nowrap;
        }
        .imf-page .imf-page-head > a i {
            font-size: 14px;
            line-height: 1;
            margin: 0 !important;
        }
        .imf-page .imf-toolbar {
            margin-top: 0;
        }
        @media (max-width: 767.98px) {
            .imf-page .imf-page-head {
                align-items: stretch;
                flex-direction: column;
                gap: 12px;
            }
            .imf-page .imf-page-head > a {
                width: 100%;
            }
        }

        /* Match the polished DataTables controls used on /my-orders. */
        .imf-page .dataTables_wrapper .dataTables_filter {
            align-items: center;
            display: flex;
            justify-content: flex-end;
            margin-left: auto;
        }
        .imf-page .dataTables_wrapper .dataTables_filter label,
        .imf-page .dataTables_wrapper .dataTables_length label {
            align-items: center;
            display: flex;
            gap: 8px;
            margin: 0;
            white-space: nowrap;
        }
        .imf-page .dataTables_wrapper .dataTables_filter input {
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
        .imf-page .dataTables_wrapper .dataTables_filter input::placeholder {
            color: #9aa9a3;
        }
        .imf-page .dataTables_wrapper .dataTables_filter input:focus {
            background: #fff;
            border-color: var(--imf-green);
            box-shadow: 0 0 0 3px rgba(66, 97, 91, .1);
        }
        .imf-page .dataTables_wrapper .dataTables_length {
            align-items: center;
            display: flex;
            margin-right: auto;
        }
        .imf-page .dataTables_wrapper .dataTables_length select {
            background: #fff;
            border-color: #d7e2dc;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(34, 46, 43, .04);
            color: var(--imf-ink);
            cursor: pointer;
            font-size: 12px;
            margin: 0 !important;
            min-width: 66px;
            padding: 7px 25px 7px 10px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .imf-page .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--imf-green);
            box-shadow: 0 0 0 3px rgba(66, 97, 91, .1);
            outline: none;
        }
        .imf-page #inventoryTable,
        .imf-page #inventoryTable th,
        .imf-page #inventoryTable td,
        .imf-page #inventoryTable .imf-type,
        .imf-page #inventoryTable .imf-action {
            font-size: 13px !important;
        }
        @media (max-width: 767.98px) {
            .imf-page .dataTables_wrapper .dataTables_filter,
            .imf-page .dataTables_wrapper .dataTables_filter label,
            .imf-page .dataTables_wrapper .dataTables_filter input,
            .imf-page .dataTables_wrapper .dataTables_length,
            .imf-page .dataTables_wrapper .dataTables_length label,
            .imf-page .dataTables_wrapper .dataTables_length select {
                width: 100%;
            }
            .imf-page .dataTables_wrapper .dataTables_filter input {
                min-width: 0;
            }
        }
    </style>
@endsection
@section('content')
<div class="container-fluid imf-page">
    <div class="row">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

        <span onclick="closeNav()" class="dark-curtain"></span>

        <div class="col-lg-12 col-md-5 col-sm-12">
            <span onclick="openNav()" class="button button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow mb-4 d-lg-none"><span class="icon-line-chevron-left me-2"></span> My Account</span>
        </div>

        <div class="col-lg-12">
            <div>

                <div class="imf-page-head">
                    <div>
                        <h3>Inventory Maintenance Form</h3>
                        <p>Register new stock items or request updates to existing ones.</p>
                    </div>
                    <a href="{{ route('new-stock.create') }}" class="button button-circle button-xlarge fw-bold fs-14-f nols text-dark h-text-light notextshadow m-0">
                        <i class="icon-line-plus me-2"></i> New IMF
                    </a>
                </div>

                <div class="imf-toolbar">
                    <span class="imf-toolbar-label">Request status</span>
                    <div class="imf-chips" id="imfChips">
                        <span class="imf-chip active" data-filter="">All</span>
                        <span class="imf-chip" data-filter="SAVED">Saved</span>
                        <span class="imf-chip" data-filter="SUBMITTED">Submitted</span>
                        <span class="imf-chip" data-filter="APPROVED">Approved</span>
                        <span class="imf-chip" data-filter="REJECTED">Rejected</span>
                        <span class="imf-chip" data-filter="CANCELLED">Cancelled</span>
                    </div>
                </div>

                <div class="imf-table-shell">
                <table id="inventoryTable" class="table w-100">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">IMF No</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Type</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Department</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Date Prepared</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Date Submitted</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Status</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray text-end">Options</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('pagejs')
    <script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
    <script src="{{ asset('lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>
	<script>
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
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content")
                        }
                    });

                    var url = "{{ route('new-stock.submit.request', ['id' => ":id", 'type' => ":type"]) }}";
                    url = url.replace(':id', id);
                    url = url.replace(':type', type);

                    $.ajax({
                        type: 'GET',
                        url: url,
                        beforeSend: function () {
                            Swal.showLoading();
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: "success",
                                title: 'IMF Submitted!',
                                text: 'The IMF has been successfully submitted for approval.',
                                showConfirmButton: false,
                                timer: 1500,
                                backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                            }).then(() => {
                                window.location.reload(true);
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: "success",
                                title: 'IMF Submitted!',
                                text: 'The IMF has been successfully submitted for approval.',
                                showConfirmButton: false,
                                timer: 1500,
                                backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                            }).then(() => {
                                window.location.reload(true);
                            });
                        }
                    });
                }

            });
        }

        $.extend($.fn.dataTableExt.oStdClasses, {
				'sPageEllipsis': 'paginate_ellipsis',
				'sPageNumber': 'paginate_number',
				'sPageNumbers': 'paginate_numbers'
		});

		$.fn.dataTableExt.oPagination.ellipses = {
				'oDefaults': {
						'iShowPages': 3
				},
				'fnClickHandler': function(e) {
						var fnCallbackDraw = e.data.fnCallbackDraw,
								oSettings = e.data.oSettings,
								sPage = e.data.sPage;

						if ($(this).is('[disabled]')) {
								return false;
						}

						oSettings.oApi._fnPageChange(oSettings, sPage);
						fnCallbackDraw(oSettings);

						return true;
				},
				// fnInit is called once for each instance of pager
				'fnInit': function(oSettings, nPager, fnCallbackDraw) {
						var oClasses = oSettings.oClasses,
								oLang = oSettings.oLanguage.oPaginate,
								that = this;

						var iShowPages = oSettings.oInit.iShowPages || this.oDefaults.iShowPages,
								iShowPagesHalf = Math.floor(iShowPages / 2);

						$.extend(oSettings, {
								_iShowPages: iShowPages,
								_iShowPagesHalf: iShowPagesHalf,
						});

						var oFirst = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageFirst + '">' + oLang.sFirst + '</a>'),
								oPrevious = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPagePrevious + '">' + oLang.sPrevious + '</a>'),
								oNumbers = $('<span class="' + oClasses.sPageNumbers + '"></span>'),
								oNext = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageNext + '">' + oLang.sNext + '</a>'),
								oLast = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageLast + '">' + oLang.sLast + '</a>');

						oFirst.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'first' }, that.fnClickHandler);
						oPrevious.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'previous' }, that.fnClickHandler);
						oNext.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'next' }, that.fnClickHandler);
						oLast.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'last' }, that.fnClickHandler);

						// Draw
						$(nPager).append(oFirst, oPrevious, oNumbers, oNext, oLast);
				},
				// fnUpdate is only called once while table is rendered
				'fnUpdate': function(oSettings, fnCallbackDraw) {
						var oClasses = oSettings.oClasses,
								that = this;

						var tableWrapper = oSettings.nTableWrapper;

						// Update stateful properties
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

						// Erase
						oNumbers.html('');

						for (i = oSettings._iFirstPage; i <= oSettings._iLastPage; i++) {
								oNumber = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageNumber + '">' + oSettings.fnFormatNumber(i) + '</a>');

								if (oSettings._iCurrentPage === i) {
										oNumber.attr('active', true).attr('disabled', true);
								} else {
										oNumber.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': i - 1 }, that.fnClickHandler);
								}

								// Draw
								oNumbers.append(oNumber);
						}

						// Add ellipses
						if (1 < oSettings._iFirstPage) {
								oNumbers.prepend('<span class="' + oClasses.sPageEllipsis + '">...</span>');
						}

						if (oSettings._iLastPage < oSettings._iTotalPages) {
								oNumbers.append('<span class="' + oClasses.sPageEllipsis + '">...</span>');
						}
				},
				// fnUpdateState used to be part of fnUpdate
				// The reason for moving is so we can access current state info before fnUpdate is called
				'fnUpdateState': function(oSettings) {
						var iCurrentPage = Math.ceil((oSettings._iDisplayStart + 1) / oSettings._iDisplayLength),
								iTotalPages = Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength),
								iFirstPage = iCurrentPage - oSettings._iShowPagesHalf,
								iLastPage = iCurrentPage + oSettings._iShowPagesHalf;

						if (iTotalPages < oSettings._iShowPages) {
								iFirstPage = 1;
								iLastPage = iTotalPages;
						} else if (iFirstPage < 1) {
								iFirstPage = 1;
								iLastPage = oSettings._iShowPages;
						} else if (iLastPage > iTotalPages) {
								iFirstPage = (iTotalPages - oSettings._iShowPages) + 1;
								iLastPage = iTotalPages;
						}

						$.extend(oSettings, {
								_iCurrentPage: iCurrentPage,
								_iTotalPages: iTotalPages,
								_iFirstPage: iFirstPage,
								_iLastPage: iLastPage
						});
				}
		};
		$(function(){
			'use strict'

			var statusFilter = '';

			var table = $('#inventoryTable').DataTable({
                order: [[0, 'desc']],
				pagingType: 'ellipses',
				processing: true,
				serverSide: true,
                columnDefs: [{ orderable: false, targets: 6 }],
				ajax: {
					url: "{{ route('new-stock.data') }}",
					type: 'GET',
					data: function (d) { d.status_filter = statusFilter; }
				},
				columns: [
					{ data: 'id' },
					{ data: 'type' },
					{ data: 'department' },
					{ data: 'date_prepared' },
					{ data: 'date_submitted' },
					{ data: 'status' },
					{ data: 'actions', orderable: false },
				],
				language: {
					searchPlaceholder: 'Search',
					sSearch: '',
					lengthMenu: 'Show _MENU_ entries',
					emptyTable: 'No IMF requests yet.',
					zeroRecords: 'No matching requests found.',
					paginate: {
						first: `<i class="icon-line-chevrons-left"></i>`,
						next: `<i class="icon-line-chevron-right"></i>`,
						previous: `<i class="icon-line-chevron-left"></i>`,
						last: `<i class="icon-line-chevrons-right"></i>`,
					},
					processing: '<div class="imf-loader"><div class="imf-spin"></div><span>Loading requests…</span></div>'
				},
				dom: '<"d-flex flex-column-reverse flex-lg-row flex-md-row justify-content-between mb-2" <"col1"<"#table-append1">><"col2"B>><"row" <"col-md-6"l><"col-md-6 d-flex flex-column flex-lg-row flex-md-row justify-content-end"f<"#table-append2">>>r<"table-responsive mb-4"t>ip',
			});

            // Status filter chips -> reload from the server with the chosen status
            $('#imfChips').on('click', '.imf-chip', function() {
                $('#imfChips .imf-chip').removeClass('active');
                $(this).addClass('active');
                statusFilter = $(this).data('filter') || '';
                table.ajax.reload();
            });
		});
	</script>
@endsection
