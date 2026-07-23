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
    </style>
@endsection
@section('content')
<div class="container-fluid">
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
            <div class="border py-4 px-3 border-transparent shadow-lg p-lg-5">

                <div class="imf-page-head">
                    <div>
                        <h3>Inventory Maintenance Form</h3>
                        <p>Register new stock items or request updates to existing ones.</p>
                    </div>
                    <a href="{{ route('new-stock.create') }}" class="button button-circle button-xlarge fw-bold fs-14-f nols text-dark h-text-light notextshadow m-0">
                        <i class="icon-line-plus me-2"></i> New IMF
                    </a>
                </div>

                <div class="imf-chips" id="imfChips">
                    <span class="imf-chip active" data-filter="">All</span>
                    <span class="imf-chip" data-filter="SAVED">Saved</span>
                    <span class="imf-chip" data-filter="SUBMITTED">Submitted</span>
                    <span class="imf-chip" data-filter="APPROVED">Approved</span>
                    <span class="imf-chip" data-filter="REJECTED">Rejected</span>
                    <span class="imf-chip" data-filter="CANCELLED">Cancelled</span>
                </div>

                <table id="inventoryTable" class="table table-striped w-100">
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
