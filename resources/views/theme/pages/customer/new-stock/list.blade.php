@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <!-- DataTable Stylesheets -->
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-dt/css/jquery.dataTables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-buttons/css/buttons.bootstrap.min.css') }}" type="text/css" />
@endsection
@section('content')
@php
    $modals='';
@endphp

<div class="container">
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
                <a href="#addNew" class="button button-dark button-border button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow mb-4" data-bs-toggle="modal">Add New Request</a>
                <table id="inventoryTable" class="table table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-20p-f">Item Description</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-20p-f">Date Prepared</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-20p-f">Date Submitted</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-15p-f">Purpose</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-15p-f">Type</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-15p-f">Status</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-10p-f">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td>{{ $request->item_description }}</td>
                                <td>{{ $request->created_at}}</td>
                                <td>{{ $request->submitted_at ?? '-' }}</td>
                                <td class="text-uppercase">{{ $request->purpose }}</td>
                                <td>{{ strtoupper($request->type) }}</td>
                                <td><span class="text-success">{{ $request->status }}</span></td>
                                <td>
                                    <nav class="nav table-options justify-content-end flex-nowrap">
                                        @if ($request->status != 'APPROVED' && $request->status != 'POSTED')
                                            <a href="javascript:;" onclick="editItem( {{ $request->id }} )" style="margin-right: 4px">
                                                <i class="icon-edit"></i>
                                            </a>

                                            <a onclick="confirmApproval( {{ $request->id }}, 'new')" href="javascript:;" style="margin-right: 4px">
                                                <i class="icon-arrow-alt-circle-right" title="Submit"></i>
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('new-stock.show', $request->id) }}" title="View" style="margin-right: 4px">
                                            <i class="icon-line-eye"></i>
                                        </a>
                                    </nav>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{!!$modals!!}

<div class="modal fade bs-example-modal-centered" id="reorder_form" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable ">
        <div class="modal-content">
            <form action="{{route('my-account.reorder')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reorder?</p>
                    <input type="hidden" id="order_id" name="order_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                    <p>Are you sure you want to cancel this order?</p>
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

<div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="requestNewItemModal" aria-hidden="true">
    <form id="formRequest" action="{{ route('new-stock.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content tx-14">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel3">Request Form</h3>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body px-3 px-lg-4">
                    <div class="row">
                        <input type="hidden" name="department" value="INFORMATION AND COMMUNICATIONS TECHNOLOGY">
                        
                        <div class="col-lg-6">
                            <div class="form-group mb-4">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio1" value="new" checked="checked">
                                    <label class="form-check-label" for="inlineRadio1">New</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio2" value="update">
                                    <label class="form-check-label" for="inlineRadio2">Update</label>
                                </div>
                            </div>
                            
                            <div id="stockCode" class="form-group mb-4">
                                <label for="stock-code" class="fw-semibold text-initial nols">Stock Code</label>
                                <input type="text" id="stock-code" class="form-control form-input" name="stock_code"/>
                                <small id="stockCodeHelp" class="form-text"></small>
                            </div>

                            <div class="form-group mb-4">
                                <label for="item-description" class="fw-semibold text-initial nols">Item Description</label>
                                <textarea id="item-description" class="form-control form-input" name="item_description" required="required" rows="3"></textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label for="brand" class="fw-semibold text-initial nols">Brand</label>
                                <input type="text" id="brand" class="form-control form-input" name="brand" />
                            </div>

                            <div class="form-group mb-4">
                                <label for="oem-id" class="fw-semibold text-initial nols">OEM ID</label>
                                <input type="text" id="oem-id" class="form-control form-input" name="OEM_ID" />
                            </div>

                            <div class="form-group mb-4">
                                <label for="uom" class="fw-semibold text-initial nols">Unit of Measure (UoM)</label>
                                <input type="text" id="uom" class="form-control form-input" name="UoM" />
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-rate-qty" class="fw-semibold text-initial nols">Usage Rate Qty</label>
                                        <input type="number" id="usage-rate-qty" class="form-control form-input" name="usage_rate_qty" />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-frequency" class="fw-semibold text-initial nols">Usage Frequency</label>
                                        <!--<input type="text" id="usage-frequency" class="form-control form-input" name="usage_frequency" />
                                        <small id="emailHelp" class="form-text text-muted">(D/W/M/Y, etc)</small>-->
                                        <select name="usage_frequency" class="form-select">
                                            <option value="Daily">Daily</option>
                                            <option value="Weekly">Weekly</option>
                                            <option value="Monthly">Monthly</option>
                                            <option value="Yearly">Yearly</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="min-qty" class="fw-semibold text-initial nols">Min Qty</label>
                                        <input type="number" id="min-qty" class="form-control form-input" name="min_qty" value="1" />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="max-qty" class="fw-semibold text-initial nols">Max Qty</label>
                                        <input type="number" id="max-qty" class="form-control form-input" name="max_qty" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="purpose" class="fw-semibold text-initial nols">Item to be used for/Application/Purpose</label>
                                <input type="text" id="purpose" class="form-control form-input" name="purpose" />
                            </div>

                            <div class="form-group mb-4">
                                <label for="attach-files" class="fw-semibold text-initial nols">Attach Files</label>
                                <input type="file" class="form-control-file d-block" id="attach-files" name="attachments[]" multiple>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-block d-lg-flex d-md-flex">
                    <div class="d-flex flex-column flex-lg-row flex-md-row justify-content-end">
                        <button type="button" class="button button-black button-border button-circle button-xlarge fw-bold mt-2 fs-14-f nols notextshadow" data-bs-toggle="modal" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="action" value="save" class="button button-black button-circle button-xlarge fw-bold mt-2 fs-14-f nols notextshadow">Save</button>
                        <button type="submit" name="action" value="save_and_submit" class="button button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow">Save & Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@foreach($requests as $request)
    <div class="modal fade bs-example-modal-centered" id="editModal{{ $request->id }}" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('new-stock.update', $request->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Edit item {{ $request->id }}</h4>
                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Department</p>
                                    <input type="text" class="form-control" id="recipient-name" name="department" value="{{ $request->department }}">
                                </div>

                                <!--<div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Stock Code</p>
                                    <input type="text" class="form-control" id="recipient-name" name="stock_code" required="required" value="{{ $request->stock_code }}">
                                </div>-->

                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Item Description: (from Generic to Specific)</p>
                                    <textarea class="form-control" id="message-text" name="item_description" required="required">{{ $request->item_description }}</textarea>
                                </div>

                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Brand</p>
                                    <input type="text" class="form-control" id="recipient-name" name="brand" value="{{ $request->brand }}">
                                </div>

                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">OEM ID</p>
                                    <input type="text" class="form-control" id="recipient-name" name="OEM_ID" value="{{ $request->OEM_ID }}">
                                </div>

                                <!--<div class="form-group">
                                    <p for="recipient-name" class="col-form-label">UoM</p>
                                    <input type="text" class="form-control" id="recipient-name" name="UoM"  value="{{ $request->UoM }}">
                                </div>-->

                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Usage Rate Qty</p>
                                    <input type="number" class="form-control" id="recipient-name" name="usage_rate_qty" value="{{ $request->usage_rate_qty }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Usage Frequency (D/W/M/Y, etc)</p>
                                    <input type="text" class="form-control" id="recipient-name" name="usage_frequency" value="{{ $request->usage_frequency }}">
                                </div>

                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Item to be used for/Application/Purpose</p>
                                    <input type="text" class="form-control" id="recipient-name" name="purpose" value="{{ $request->purpose }}">
                                </div>

                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Min. Qty</p>
                                    <input type="number" class="form-control" id="recipient-name" name="min_qty" value="{{ $request->min_qty }}">
                                </div>

                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Max Qty</p>
                                    <input type="number" class="form-control" id="recipient-name" name="max_qty" value="{{ $request->max_qty }}">
                                </div>

                                <div class="form-group">
                                    <p for="recipient-name" class="col-form-label">Attach Files</p>
                                    <input type="file" class="form-control" id="recipient-name" name="attachments[]" multiple>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@endsection

@section('pagejs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script src="{{ asset('lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>
	<script>
        function confirmApproval(id, type) {
            swal({
            title: 'Are you sure?',
            text: "Do you really want to submit this IMF for approval?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2ecc71',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, submit!'
            },
            function(isConfirm) {
                if (isConfirm) {
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
                        success: function(response) {
                            if (response.success) {
                                swal({
                                    title: 'Good job!',
                                    text: 'You clicked the button!',
                                    type: 'success'
                                }, function() {
                                    location.reload();
                                })
                            }
                        }
                    });
                }
                else {
                    swal.close();
                }
            });
        }

		function view_items(salesID){
            $('#detail'+salesID).modal('show');
        }

        function view_deliveries(salesID){
            $('#delivery'+salesID).modal('show');
        }

        function cancel_unpaid_order(id){
            $('#orderid').val(id);
            $('#cancel_order').modal('show');
        }

        function editItem(id) {
            $('#editModal' + id).modal('show')
        }

        function reorder(id) {
            $('#order_id').val(id);
            $('#reorder_form').modal('show');
        }

        function upload_payment(salesID, grossAmount){
            var amount = parseFloat(grossAmount);

            $('#salesID').val(salesID);
            $('#order_amount').val(amount.toFixed(2));
            $('#upload_payment_modal').modal('show');
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

			$('#inventoryTable').DataTable({
                order: [[1, 'desc']],
				pagingType: 'ellipses',
				language: {
					searchPlaceholder: 'Search',
					sSearch: '',
					lengthMenu: 'Show _MENU_ entries',
					paginate: {
						first: `<i class="icon-line-chevrons-left"></i>`,
						next: `<i class="icon-line-chevron-right"></i>`,
						previous: `<i class="icon-line-chevron-left"></i>`,
						last: `<i class="icon-line-chevrons-right"></i>`,
					},
					processing: '<i class="fa fa-spinner fa-spin" style="font-size:24px;color:rgb(75, 183, 245);"></i>'
				},
				dom: '<"d-flex flex-column-reverse flex-lg-row flex-md-row justify-content-between mb-2" <"col1"<"#table-append1">><"col2"B>><"row" <"col-md-6"l><"col-md-6 d-flex flex-column flex-lg-row flex-md-row justify-content-end"f<"#table-append2">>><"table-responsive mb-4"t>ip',
			});

            $('#stockCode').hide();

            $("#stock-code").on("input", function() {
                var input = $(this).val();

                $.ajax({
                    type: "GET",
                    url: "{{ route('product.search') }}",
                    data: {q: input},
                    success: function(product) {
                        $('#formRequest').find('input, select, textarea').not('#stock-code').val('');
                        if (!$.isEmptyObject(product)) {
                            $('#item-description').val(product.description);
                            $('#brand').val(product.brand);
                            $('#oem-id').val(product.oem);
                            $('#uom').val(product.uom);
                            $('#stockCodeHelp').text('Product code has been found.').removeClass('text-danger').addClass('text-success');
                        }
                        else {
                            $('#stockCodeHelp').text('Product code does not match.').removeClass('text-success').addClass('text-danger');
                        }
                    }
                });
            });

            $('input[type=radio][name=type]').change(function() {
                if (this.value == 'new') {
                    $('#stockCode').hide();
                }
                else if (this.value == 'update') {
                    $('#stockCode').show();
                }
            });
		});
	</script>
@endsection

