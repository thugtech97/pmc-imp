@extends('theme.main')

@section('pagecss')
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <style>
        thead, tbody, tfoot, tr, td, th {
            padding: 3px;
        }
    </style>
@endsection

@section('content')
<div class="container content-wrap">
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

    <div class="border py-4 px-3 border-transparent shadow-lg p-lg-5">
        <form method="post" action="{{ route('cart.temp_sales') }}" id="chk_form">
            @csrf
            <h3 class="border-bottom pb-3">Review and Place Order</h3>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="shippingType" class="fw-semibold text-initial nols">Costcode</label>
                        <!--<select id="shippingType" class="form-select" name="shipping_type">
                            <option value="Pickup">Pickup</option>
                            <option value="Delivery">Delivery</option>
                        </select>-->
                        <input type="text" class="form-control" name="costcode" required>
                    </div>

                    <div class="form-group">
                        <label for="isBudgeted" class="fw-semibold text-inital nols">Budgeted?</label>
                        <select id="isBudgeted" name="isBudgeted" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="form-group budgetAmount">
                        <label>Budget amount</label>
                        <input type="number" name="budget_amount" class="form-control">
                    </div>

                    <!--<div class="form-group mb-5 mx-wd-400 customerAddress">
                        <textarea class="form-control" id="custAddField" name="customer_address" placeholder="Please enter address..." required="required"></textarea>
                        <div id="custAddField" class="invalid-feedback">
                            Please provide a valid address.
                        </div>
                    </div>-->
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Date Needed</label>
                        <input type="date" class="form-control" name="date_needed" required>
                    </div>

                    <div class="form-group">
                        <label>Justification</label>
                        <input type="text" class="form-control" name="justification" required>
                    </div>
                </div>
            </div>
            <input type="hidden" name="shipping_type" value="Pickup">
            
            <div class="table-responsive mb-5">
                <table class="table table-striped dataTable w-100 mn-wd-1100-f">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Product</th>
                            <!--<th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-20p-f">Unit Price</th>-->
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-20p-f">Quantity</th>
                            <!--<th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray wd-15p-f text-end">Total</th>-->
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalqty = 0; @endphp
                        @foreach($orders as $order)
                            @php
                                $totalqty += $order->qty;
                            @endphp
                            <tr>
                                <td class="align-middle">
                                    <div class="top-cart-item">
                                        <div class="top-cart-item-image wd-70-f ht-70-f">
                                            <a href="#" class="wd-70-f ht-70-f"><img class="wd-70-f ht-70-f" src="{{ $order->product->photoPrimary }}" onerror="this.src='{{ asset('images/1667370521_download.jpg') }}'"  alt="{{ $order->product->name }}"></a>
                                        </div>
                                        <div class="top-cart-item-desc">
                                            <div class="top-cart-item-desc-title">
                                                <a href="#" class="fs-16-f fw-normal lh-base">{{ $order->product->name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <!--<td class="align-middle">
                                    <span class="top-cart-item-price d-block fs-16-f"><del>₱ 60.00</del> ₱ 54.00</span>
                                </td>-->
                                <td class="align-middle">{{ $order->qty }} pc(s)</td>
                                <!--<td class="align-middle">
                                    <div class="top-cart-item-quantity text-end fs-16-f">₱ 108.00</div>
                                </td>-->
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row mb-5">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Attach files</label>
                        <input type="file" name="attachments" class="form-control">
                    </div>

                    <div class="form-group mb-4">
                        <label for="notes" class="fw-semibold text-initial nols">Notes</label>
                        <textarea id="notes" class="form-control form-input" name="notes" rows="6" required></textarea>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card rounded-xxl border-2 border-color">
                        <div class="card-header bg-color">
                            <h3 class="card-title mb-0 py-3 px-lg-4">Cart Summary</h3>
                        </div>
                        <div class="card-body px-lg-5 py-4 py-lg-5">
                            <table class="w-100 mb-0">
                                <tfoot class="border-top">
                                    <tr>
                                        <td>
                                            <div class="text-dark mt-4 ls1 fw-bold text-uppercase fs-20-f">
                                                Total
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="text-dark mt-4 fw-bold text-uppercase fs-20-f">
                                                {{ $totalqty }}
                                            </div>
                                        </td>	
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="javascript:;" class="button button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow px-5" onclick="place_order(event);">Place Order</a>
            </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

<script>
	$(document).ready(function(){
        $('[data-bs-toggle="popover"]').popover();
        
        $('.deliveryDate').hide();
        $('.customerAddress').hide();
        $('.budgetAmount').hide();

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
                $('.budgetAmount').show();
            }
            else {
                $('.budgetAmount').hide();
            }
        })
	});

	function IsEmail(email) {
	    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	    if(!regex.test(email)) {
	        return false;
	    } else{
	        return true;
	    }
	}

	jQuery(window).on( 'pluginTabsReady', function(){
		$( "#processTabs" ).tabs({ show: { effect: "fade", duration: 400 } });
		$( ".tab-linker" ).click(function() {

			var nxt_tab = $(this).attr('rel');

			if(nxt_tab == 2){
				var customer = $('#customer_name').val();
				var email = $('#customer_email').val();
				var contact = $('#customer_contact_number').val();
				var address = $('#customer_address').val();
				var zipcode = $('#customer_delivery_zip').val();

				if(customer.length === 0 || contact.length === 0 || IsEmail(email) == false || zipcode.length === 0 || address.length === 0){
                    swal('Oops...', 'Please check required input fields.', 'error');
		            return false;

                } else {
                    $( "#processTabs" ).tabs("option", "active", $(this).attr('rel') - 1);
					return false;
                }
			} else if(nxt_tab == 3){
				var location = $('#location').val();
				if (location.length === 0) {       
	                swal('Oops...', 'Please select municipality for additional delivery fee.', 'error');
	                return false;
	            }

	            $( "#processTabs" ).tabs("option", "active", $(this).attr('rel') - 1);
				return false;

			} else {
				$( "#processTabs" ).tabs("option", "active", $(this).attr('rel') - 1);
				return false;
			}	
		});
	});

	function update_details(){
		var customer = $('#customer_name').val();
		var email = $('#customer_email').val();
		var contact = $('#customer_contact_number').val();
		var address = $('#customer_address').val();
		var zipcode = $('#customer_delivery_zip').val();
		
		$('#ck_billed_to').html(customer);
		$('#ck_email').html(email);
		$('#ck_contact').html(contact);
		$('#ck_address').html(address);
		$('#ck_zip').html(zipcode);
	}

	function addCommas(nStr){
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }

	function shipping_type(stype){
		$('#shipping_type').val(stype);
	}

	$('#location').change(function(){
		var stype = $('#shipping_type').val();
		var val   = ($(this).val()).split('|');

		var dfee  = parseFloat(val[1])
           
        if(stype == 'stp'){
        	$('#shipping_fee').html('₱0.00');
			$('#delivery_fee').val(0);
        } else {
        	$('#shipping_fee').html('₱'+addCommas(dfee.toFixed(2)));
			$('#delivery_fee').val(dfee);
        }

        compute_total();
    });

    function compute_total(){

        var delivery_fee = parseFloat($('#delivery_fee').val());
        var delivery_discount = parseFloat($('#sf_discount_amount').val());


        var orderAmount = parseFloat($('#totalAmountWithoutCoupon').val());
        var couponDiscount = parseFloat($('#coupon_total_discount').val());

        var orderTotal  = orderAmount-couponDiscount;
        var deliveryFee = delivery_fee-delivery_discount;

        var grandTotal = parseFloat(orderTotal)+parseFloat(deliveryFee);

        $('#span_total_amount').html(addCommas(parseFloat(grandTotal).toFixed(2)));
        $('#total_amount').val(grandTotal.toFixed(2));
    }

    function place_order(e) {  
        if ($('.customerAddress').is(':visible') && !$('#custAddField').val()) {
            e.preventDefault();
            $('#custAddField').addClass('is-invalid');
        }
        else {
            $('#chk_form').submit();
        }
	}

</script>

<script>
	$('#couponManualBtn').click(function(){
        var couponCode = $('#coupon_code').val();
        var grandtotal = parseFloat($('#input_total_due').val());

        if($('#location').val() == ''){
            swal({
                title: '',
                text: "Please select a municipality!",         
            });
            return false;
        }

        $.ajax({
            data: {
                "couponcode": couponCode,
                "_token": "{{ csrf_token() }}",
            },
            type: "post",
            url: "{{route('add-manual-coupon')}}",
            success: function(returnData) {
                // coupon validity label
                    if(returnData.coupon_details['start_time'] == null){
                        var couponStartDate = returnData.coupon_details['start_date'];
                    } else {
                        var couponStartDate = returnData.coupon_details['start_date']+' '+returnData.coupon_details['start_time'];
                    }
                    
                    if(returnData.coupon_details['end_date'] == null){
                        var couponEndDate = '';
                    } else {
                        if(returnData.coupon_details['end_time'] == null){
                            var couponEndDate = ' - '+returnData.coupon_details['end_date'];
                        } else {
                            var couponEndDate = ' - '+returnData.coupon_details['end_date']+' '+returnData.coupon_details['end_time'];
                        }
                    }
                    var couponValidity = couponStartDate+''+couponEndDate;
                //

                if(returnData['not_allowed']){
                    swal({
                        title: '',
                        text: "Sorry, you are not authorized to use this coupon.",         
                    });
                    return false;
                }
                
                if(returnData['exist']){
                    swal({
                        title: '',
                        text: "Coupon already used.",         
                    }); 
                    return false;
                }

                if(returnData['not_exist']){
                    swal({
                        title: '',
                        text: "Coupon not found.",         
                    }); 
                    return false;
                }

                if(returnData['expired']){
                    swal({
                        title: '',
                        text: "Coupon is already expired.",         
                    }); 
                    return false;
                }

                if (returnData['success']) {

                    // coupon validity label
                        if(returnData.coupon_details['start_time'] == null){
                            var couponStartDate = returnData.coupon_details['start_date'];
                        } else {
                            var couponStartDate = returnData.coupon_details['start_date']+' '+returnData.coupon_details['start_time'];
                        }
                        
                        if(returnData.coupon_details['end_date'] == null){
                            var couponEndDate = '';
                        } else {
                            if(returnData.coupon_details['end_time'] == null){
                                var couponEndDate = ' - '+returnData.coupon_details['end_date'];
                            } else {
                                var couponEndDate = ' - '+returnData.coupon_details['end_date']+' '+returnData.coupon_details['end_time'];
                            }
                        }
                        var couponValidity = couponStartDate+''+couponEndDate;
                    //

                    $('#manual-coupon-details').append(
                        '<div id="manual_details'+returnData.coupon_details['id']+'">'+
                        // coupons input
                            '<input type="hidden" id="couponcombination'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['combination']+'">'+
                            '<input type="hidden" id="sfarea'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['area']+'">'+
                            '<input type="hidden" id="sflocation'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['location']+'">'+
                            '<input type="hidden" id="sfdiscountamount'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['location_discount_amount']+'">'+
                            '<input type="hidden" id="sfdiscounttype'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['location_discount_type']+'">'+
                            '<input type="hidden" id="discountpercentage'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['percentage']+'">'+
                            '<input type="hidden" id="discountamount'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['amount']+'">'+
                            '<input type="hidden" id="couponname'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['name']+'">'+
                            '<input type="hidden" id="couponcode'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['coupon_code']+'">'+
                            '<input type="hidden" id="couponterms'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['terms_and_conditions']+'">'+
                            '<input type="hidden" id="coupondesc'+returnData.coupon_details['id']+'" value="'+returnData.coupon_details['description']+'">'+
                            '<input type="hidden" id="couponvalidity'+returnData.coupon_details['id']+'" value="'+couponValidity+'">'+
                        //
                        '</div>'
                    );

                    if(returnData.coupon_details['location'] == null){
                        swal({
                            title: '',
                            text: "Only shipping fee coupon is allowed.",         
                        });

                    } else {
                        if(returnData.coupon_details['amount'] > 0){ 
                            var amountdiscount = parseFloat(returnData.coupon_details['amount']);
                        }

                        if(returnData.coupon_details['percentage'] > 0){
                            var percent  = parseFloat(returnData.coupon_details['percentage'])/100;
                            var discount = parseFloat(grandtotal)*percent;

                            var amountdiscount = parseFloat(discount);
                        }

                        var total = grandtotal-amountdiscount;
                        if(total.toFixed(2) < 1){
                            swal({
                                title: '',
                                text: "The total amount is less than the coupon discount.",         
                            });

                            return false;
                        }
                        
                        use_sf_coupon(returnData.coupon_details['id']);
                    }
                } 
            }
        });
    });

	function myCoupons(){
    	if($('#location').val() == ""){
            swal({
                title: '',
                text: 'Please select your municipality.',
                icon: 'warning'
            });              
            return false;
        }

        var totalAmount = $('#totalAmountWithoutCoupon').val();
        var totalQty = $('#totalQty').val();

        $.ajax({
            type: "GET",
            url: "{{ route('show-coupons') }}",
            data: {
                'total_amount' : totalAmount,
                'total_qty' : totalQty,
                'page_name' : 'checkout',
            },
            success: function( response ) {
                $('#collectibles').empty();

                var arr_selected_coupons = [];
                $("input[name='couponid[]']").each(function() {
                    arr_selected_coupons.push(parseInt($(this).val()));
                });

                $.each(response.coupons, function(key, coupon) {
                    if(coupon.end_date == null){
                        var validity = '';  
                    } else {
                        if(coupon.end_time == null){
                            var validity = ' Valid Till '+coupon.end_date;
                        } else {
                            var validity = ' Valid Till '+coupon.end_date+' '+coupon.end_time;
                        }
                    }

                    if(jQuery.inArray(coupon.id, response.availability) !== -1){

                        if(jQuery.inArray(coupon.id, arr_selected_coupons) !== -1){
                            var usebtn = '<button class="btn btn-success btn-sm" disabled>Applied</button>';
                        } else {
                            var usebtn = '<button class="btn btn-success btn-sm" id="couponBtn'+coupon.id+'" onclick="use_sf_coupon('+coupon.id+')"><span id="btnCpnTxt'+coupon.id+'">Use Coupon</span></button>';
                        }

                        $('#collectibles').append(
                        	'<div class="alert alert-dismissible alert-info mt-3" id="coupondiv'+coupon.id+'">'+

                                '<input type="hidden" id="couponcombination'+coupon.id+'" value="'+coupon.combination+'">'+
                                '<input type="hidden" id="sflocation'+coupon.id+'" value="'+coupon.location+'">'+
                                '<input type="hidden" id="sfdiscountamount'+coupon.id+'" value="'+coupon.location_discount_amount+'">'+
                                '<input type="hidden" id="sfdiscounttype'+coupon.id+'" value="'+coupon.location_discount_type+'">'+
                                '<input type="hidden" id="discountpercentage'+coupon.id+'" value="'+coupon.percentage+'">'+
                                '<input type="hidden" id="discountamount'+coupon.id+'" value="'+coupon.amount+'">'+
                                '<input type="hidden" id="couponname'+coupon.id+'" value="'+coupon.name+'">'+
                                '<input type="hidden" id="couponcode'+coupon.id+'" value="'+coupon.coupon_code+'">'+
                                '<input type="hidden" id="couponterms'+coupon.id+'" value="'+coupon.terms_and_conditions+'">'+
                                '<input type="hidden" id="coupondesc'+coupon.id+'" value="'+coupon.description+'">'+


                                '<div class="title-bottom-border mb-3">'+
                                    '<h4>'+coupon.name+'</h4>'+
                                '</div>'+
                                '<small><strong>'+validity+'</strong></small>'+
                                '<p class="mb-3">'+coupon.description+'</p>'+

                                usebtn+'&nbsp;'+
                                '<button type="button" class="btn btn-secondary btn-sm me-2" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="'+coupon.terms_and_conditions+'">Terms & Condition</button>'+
                            '</div>'
                        );
                    } else {
                        $('#collectibles').append(
                        	'<div class="alert alert-dismissible alert-secondary mt-3">'+
                                '<div class="title-bottom-border mb-3">'+
                                    '<h4>'+coupon.name+'</h4>'+
                                '</div>'+
                                '<small><strong>'+validity+'</strong></small>'+
                                '<p class="mb-3">'+coupon.description+'</p>'+

                                '<button class="btn btn-success btn-sm disabled">Use Coupon</button>&nbsp;'+
                                '<button type="button" class="btn btn-secondary btn-sm me-2" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="'+coupon.terms_and_conditions+'">Terms & Condition</button>'+
                            '</div>'
                        );
                    }

                    $('[data-bs-toggle="popover"]').popover();
                    
                });

                $('#couponModal').modal('show');
            }
        });
    }

    function coupon_counter(cid){
        var limit = $('#coupon_limit').val();
        var counter = $('#coupon_counter').val();
        var solo_coupon_counter = $('#solo_coupon_counter').val();

        var combination = $('#couponcombination'+cid).val();
        if(parseInt(counter) < parseInt(limit)){

            if(combination == 0){
                if(counter > 0){
                    swal({
                        title: '',
                        text: "Coupon cannot be used together with other coupons.",         
                    });
                    return false;
                } else {
                    $('#solo_coupon_counter').val(1);
                    $('#coupon_counter').val(parseInt(counter)+1);
                    return true;
                }
            } else {
                if(solo_coupon_counter > 0){
                    swal({
                        title: '',
                        text: "Unable to use this coupon. A coupon that cannot be used together with other coupon is already been selected.",         
                    });
                    return false;
                } else {
                    $('#coupon_counter').val(parseInt(counter)+1);
                    return true;
                }
            }
        } else {
            swal({
                title: '',
                text: "Maximum of "+limit+" coupon(s) only.",         
            });
            return false;
        }
    }
</script>

<script>
	function use_sf_coupon(cid){
        // check total use shipping fee coupons
        var sfcoupon = parseFloat($('#sf_discount_coupon').val());

        if(sfcoupon == 1){
            swal({
                title: '',
                text: "Only one (1) coupon for shipping fee discount.",         
            });
            return false;
        }
        
        if(coupon_counter(cid)){
            var selectedLocation = $('#location').val();
            var loc = selectedLocation.split('|');

            var couponLocation = $('#sflocation'+cid).val();
            var cLocation = couponLocation.split('|');

            var arr_coupon_location = [];
            $.each(cLocation, function(key, value) {
                arr_coupon_location.push(value);
            });

            if(jQuery.inArray(loc[0], arr_coupon_location) !== -1 || jQuery.inArray('all', arr_coupon_location) !== -1){

                var name  = $('#couponname'+cid).val();
                var terms = $('#couponterms'+cid).val();
                var desc = $('#coupondesc'+cid).val();
                var combination = $('#couponcombination'+cid).val();
                
                $('#couponList').append(
                	'<div class="alert alert-dismissible alert-info mt-3" id="appliedCoupon'+cid+'">'+
                        '<div class="title-bottom-border mb-3">'+
                            '<h4>'+name+'</h4>'+
                        '</div>'+
                        '<p class="mb-3">'+desc+'</p>'+

                        '<input type="hidden" id="coupon_combination'+cid+'" value="'+combination+'">'+
                        '<input type="hidden" name="couponid[]" value="'+cid+'">'+
                        '<input type="hidden" name="coupon_productid[]" value="0">'+

                        '<button type="button" class="btn btn-danger btn-sm sfCouponRemove" id="'+cid+'">Remove</button>&nbsp;'+
                        '<button type="button" class="btn btn-secondary btn-sm me-2" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="'+terms+'">Terms & Condition</button>'+
                    '</div>'
                );

                $('[data-bs-toggle="popover"]').popover();

                

                $('#sf_discount_coupon').val(1);
                var sf_type = $('#sfdiscounttype'+cid).val();
                var sf_discount = parseFloat($('#sfdiscountamount'+cid).val());

                if(sf_type == 'full'){
                    dfee = parseFloat($('#delivery_fee').val());

                    $('#sf_discount_amount').val(dfee);

                    $('#tr_sf_discount').css('display','table-row');
                    $('#shipping_fee_disocunt').html('₱'+addCommas(dfee.toFixed(2)));
                }

                if(sf_type == 'partial'){
                    $('#sf_discount_amount').val(sf_discount.toFixed(2));

                    $('#tr_sf_discount').css('display','table-row');
                    $('#shipping_fee_disocunt').html('₱'+addCommas(sf_discount.toFixed(2)));
                }

                $('#couponBtn'+cid).prop('disabled',true);
                $('#btnCpnTxt'+cid).html('Applied');

                compute_total();
            } else {
                swal({
                    title: '',
                    text: "Selected delivery location is not in the coupon location.",         
                });
            } 
        }
    }

    $(document).on('click', '.sfCouponRemove', function(){  
        var id = $(this).attr("id");  

        $('#tr_sf_discount').css('display','none');
        
        $('#sf_discount_amount').val(0);
        var totalsfdiscoutcounter = $('#sf_discount_coupon').val();
        $('#sf_discount_coupon').val(parseInt(totalsfdiscoutcounter)-1);

        var counter = $('#coupon_counter').val();
        $('#coupon_counter').val(parseInt(counter)-1);

        var combination = $('#coupon_combination'+id).val();
        if(combination == 0){
            $('#solo_coupon_counter').val(0);
        }

        $('#appliedCoupon'+id+'').remove();

        compute_total();
    });
</script>
@endsection