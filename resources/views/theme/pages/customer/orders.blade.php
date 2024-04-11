@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <!-- DataTable Stylesheets -->
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-dt/css/jquery.dataTables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-buttons/css/buttons.bootstrap.min.css') }}" type="text/css" />
    <style>
        .modal-size .modal-dialog {
            max-width: 80% !important;
            width: 80% !important; 
        }
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
            <a href="{{ route('cart.front.show') }}" class="button button-dark button-border button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow mb-4">Add New Request</a>
            
            <table id="inventoryTable" class="table table-bordered table-striped">
                <thead class="text-center">
                    <tr>
                        <th>Order#</th>
                        <th>Created Date</th>
                        <th>Posted Date</th>
                        <!--<th>Ordered</th>
                        <th>Delivered</th>
                        <th>Balance</th>!-->
                        <th>Order Status</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @forelse($sales as $sale)
                        <tr>
                            <td>{{$sale->order_number}}</td>
                            <td>{{ $sale->created_at }}</td>
                            <td>{{ $sale->date_posted ? date('Y-m-d H:i:s', strtotime($sale->date_posted)) : '-' }}</td>
                            <?php /*<td class="text-uppercase">{{ $sale->items->sum('qty') }}</td>
                            <td>{{ $sale->issuances->sum('qty') }}</td>
                            <td>{{ $sale->items->sum('qty') - $sale->issuances->sum('qty') }}</td> */ ?>
                            <td>
                                <span class="{{ strtoupper($sale->status) === 'CANCELLED' ? 'text-danger' : 'text-success' }}">
                                    {{ strtoupper($sale->status) }}
                                </span>
                            </td>
                            <td>
                                <a data-bs-toggle="dropdown" href="#" onclick="view_items('{{$sale->id}}');" title="View Details" aria-expanded="false">
                                    <i class="icon-eye"></i>
                                </a>

                                @if ($sale->status != 'completed' && $sale->status != 'CANCELLED')
                                <a href="javascript:;" onclick="cancel_unpaid_order('{{$sale->id}}')" title="Cancel Order"><i class="icon-forbidden"></i></a>
                                @endif

                                @switch($sale->status)
                                    @case('HOLD')
                                        <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'resubmitted']) }}" title="Resubmit"><i class="icon-refresh"></i></a>
                                        @break
                                    @case('SAVED')
                                    @case('saved')
                                        <a data-bs-toggle="dropdown" href="javascript:;" onclick="edit_items('{{$sale->id}}', '{{ $sale->delivery_type }}');" title="Edit Details" aria-expanded="false">
                                            <i class="icon-pencil"></i>
                                        </a>
                                        <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'submitted']) }}" title="Submit for Approval"><i class="icon-upload"></i></a>
                                        @break
                                    @case('posted')
                                        <a href="#" title="View Deliveries" onclick="view_deliveries('{{$sale->id}}');"><i class="icon-truck"></i></a>
                                        @break
                                    @case('CANCELLED')
                                        <!-- <a href="javascript:;" onclick="reorder('{{$sale->id}}')" title="Reorder"><i class="icon-reply"></i></a> -->
                                        @break
                                @endswitch

                                {{--@if($sale->status == 'CANCELLED' || $sale->delivery_status == 'Delivered')
                                    <a class="dropdown-item" href="#" onclick="reorder('{{$sale->id}}')">Reorder</a>
                                @endif--}}

                                @if($sale->issuances->count() > 0)
                                <a data-bs-toggle="dropdown" href="javascript:void(0);" data-toggle="modal" data-target="#issuanceDetailsModal{{ $sale->id }}" aria-expanded="false" title="View Issuances">
                                    <i class="icon-file"></i>
                                </a>
                                @endif
                            </td>
                        </tr>

                        @php
                            $modals .='
                                <div class="modal fade bs-example-modal-centered" id="delivery'.$sale->id.'" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="myModalLabel">'.$sale->order_number.'</h4>
                                                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="transaction-status">
                                                </div>
                                                <div class="gap-20"></div>
                                                <div class="table-modal-wrap">
                                                    <table class="table table-md table-modal">
                                                        <thead>
                                                            <tr>
                                                                <th>Date and Time</th>
                                                                <th>Status</th>
                                                                <th>Remarks</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';
                                                            if($sale->deliveries){
                                                                foreach($sale->deliveries as $delivery){
                                                                    $modals.='
                                                                    <tr>
                                                                        <td>'.$delivery->created_at.'</td>
                                                                        <td>'.$delivery->status.'</td>
                                                                        <td>'.$delivery->remarks.'</td>
                                                                    </tr>
                                                                ';
                                                                }
                                                            }
                                                        $modals .='
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="gap-20"></div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal effect-scale" id="issuanceDetailsModal'.$sale->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalCenterTitle">Issuances</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                ';    
                                                foreach($sale->items as $item) {
                                                    $modals .= '
                                                    <p><strong>Name:</strong> ' . $item->product_name . '</p>
                                                    <p><strong>Code:</strong> ' . $item->product_code . '</p>
                                                    <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Issuance No.</th>
                                                            <th scope="col">Quantity</th>
                                                            <th scope="col">Date Issued</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>';
                                                                foreach($item->issuances as $issuance) {
                                                                    $modals .= '
                                                                    <tr>
                                                                    <td>' . $issuance->issuance_no . '</td>
                                                                    <td>' . $issuance->qty . '</td>
                                                                    <td>' . date('F j, Y', strtotime($issuance->release_date)) . '</td>
                                                                    </tr>';
                                                                }
                                                                $modals .= '
                                                        
                                                    </tbody>
                                                    </table>';
                                                }
                                                $modals .= '
                                            </div>
                                            <div class="modal-footer">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade bs-example-modal-centered modal-size" id="viewdetail'.$sale->id.'" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post" class="m-0" action="' . route("my-account.update.order", $sale->id) . '">
                                            <input type="hidden" name="_token" value="'.csrf_token().'">
                                            <input type="hidden" name="_method" value="PUT">

                                            <div class="modal-header">
                                                <h4 class="modal-title" id="myModalLabel">Order No. '.$sale->order_number.'</h4>
                                                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="transaction-status">
                                                            <span><strong>Order Date:</strong> '.$sale->created_at.'</span><br>
                                                            <span><strong>Order Status:</strong> '.strtoupper($sale->status).'</span><br>
                                                            <span><strong>Delivery Date:</strong> '.$sale->delivery_date.'</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="transaction-status">
                                                            <span><strong>Delivery Type:</strong> '.$sale->delivery_type.'</span><br>
                                                            <span><strong>Delivery Address:</strong> '.$sale->customer_delivery_adress.'</span><br>
                                                            <span><strong>Other Instructions:</strong> '.$sale->other_instruction.'</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="gap-20"></div>
                                                <br><br>
                                                <div class="table-modal-wrap">
                                                    <table class="table table-md table-modal">
                                                        <thead>
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Qty</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';

                                                                $total_qty = 0;
                                                                $total_sales = 0;

                                                            foreach($sale->items as $item){

                                                                $total_qty += $item->qty;
                                                                $total_sales += $item->qty * $item->price;
                                                                $modals.='
                                                                <tr>
                                                                    <td>'.$item->product_name.'</td>
                                                                    <td>'. $item->qty . ' ' . $item->uom .'</td>
                                                                </tr>';
                                                            }

                                                            $delivery_discount = \App\Models\Ecommerce\CouponSale::total_discount_delivery($sale->id);
                                                            $grossAmount = ($total_sales-$sale->discount_amount)+($sale->delivery_fee_amount-$delivery_discount);

                                                            $modals.='
                                                        </tbody>
                                                    </table>
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
                            <td colspan="8">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
	</div>
</div>

{!!$modals!!}

@foreach ($sales as $sale)
    <div class="modal fade bs-example-modal-centered" id="editdetail{{ $sale->id }}" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="{{ route("my-account.update.order", $sale->id) }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="PUT">

                <div class="modal-header">
                    <small style="margin-right:30px"><strong>Order No.</strong> {{ $sale->order_number }}</small>
                    <small style="margin-right:30px"><strong>Order Date:</strong> {{ $sale->created_at }}</small>
                    <small><strong>Order Status:</strong> {{ strtoupper($sale->status) }}</small>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="transaction-status">
                                <div class="form-group">
                                    <strong>Delivery Type:</strong>
                                    <select onchange="changeShippingType('{{ $sale->id }}')" id="shippingType{{ $sale->id }}" class="form-select" name="delivery_type">
                                        <option value="Delivery" {{ $sale->delivery_type == "Delivery" ? "selected" : "" }}>Delivery</option>
                                        <option value="Pickup" {{ $sale->delivery_type == "Pickup" ? "selected" : "" }}>Pickup</option>
                                    </select>
                                </div>
                                <div id="deliveryDate{{ $sale->id }}" class="form-group">
                                    <strong>Delivery Date:</strong>
                                    <input type="date" name="delivery_date" class="form-control" value="{{ $sale->delivery_date }}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="transaction-status">
                                <div id="customerAddress{{ $sale->id }}" class="form-group">
                                    <strong>Delivery Address:</strong> 
                                    <input type="text" class="form-control" name="delivery_address" value="{{ $sale->customer_delivery_adress }}" />
                                </div>
                                <div class="form-group">
                                    <strong>Other Instructions:</strong>
                                    <textarea name="notes" class="form-control">{{ $sale->other_instruction }}</textarea> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="gap-20"></div>
                    <br><br>
                    <div class="table-modal-wrap">
                        <table class="table table-md table-modal">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                    $total_qty = 0;
                                    $total_sales = 0;

                                foreach($sale->items as $item){

                                    $total_qty += $item->qty;
                                    $total_sales += $item->qty * $item->price;
                            @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                            <p><small class="text-muted">({{ $item->uom }})</small></p>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="qty[{{ $item->id }}]" min="1" value="{{ $item->qty }}">
                                        </td>
                                    </tr>
                            @php
                                }

                                $delivery_discount = \App\Models\Ecommerce\CouponSale::total_discount_delivery($sale->id);
                                $grossAmount = ($total_sales-$sale->discount_amount)+($sale->delivery_fee_amount-$delivery_discount);

                            @endphp
                            </tbody>
                        </table>
                    </div>
                    <div class="gap-20"></div>
                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-primary" value="Update">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

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

<div class="modal fade bs-example-modal-centered" id="upload_payment_modal" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable ">
        <div class="modal-content">
            <form action="{{route('order.submit-payment')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Upload Payment</h4>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Amount *</label>
                        <input readonly type="text" name="amount" class="form-control" id="order_amount">
                    </div>

                    <div class="form-group">
                        <label>Receipt # *</label>
                        <input required type="text" name="receipt_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Remarks </label>
                        <textarea class="form-control" name="remarks" rows="5"></textarea>
                    </div>

                    <input required type="file" name="attachments[]" class="form-controlP">

                    <input type="hidden" id="salesID" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('pagejs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script src="{{ asset('lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>
	<script>
        function changeShippingType(id) {
            if ($('#shippingType'+id).val() === "Delivery") {
                $('#customerAddress'+id).show();
                $('#deliveryDate'+id).show();
            }
            else {
                $('#deliveryDate'+id).hide();
                $('#customerAddress'+id).hide();
            }
        }

        function view_items(salesID){
            $('#viewdetail'+salesID).modal('show');
        }

        function edit_items(salesID, deliveryType){
            if (deliveryType === "Delivery") {
                $('#customerAddress'+salesID).show();
                $('#deliveryDate'+salesID).show();
            }
            else {
                $('#deliveryDate'+salesID).hide();
                $('#customerAddress'+salesID).hide();
            }

            $('#editdetail'+salesID).modal('show');
        }

        function view_deliveries(salesID){
            $('#delivery'+salesID).modal('show');
        }

        function cancel_unpaid_order(id){
            $('#orderid').val(id);
            $('#cancel_order').modal('show');
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
		});
	</script>
@endsection

