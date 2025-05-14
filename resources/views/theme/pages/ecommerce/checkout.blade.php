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
    <style>
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
    </style>
@endsection

@section('content')
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

    <div class="border py-4 px-3 border-transparent shadow-lg p-lg-5">
        <div class="alert alert-info">
            <strong>Note: The next person has three days to review or approve it before forwarding to the next level. Please be reminded to raise a request 1 to 2 months earlier before the Date Needed to avoid rush processing of your request. Thank you.</strong>
        </div>
        @if($mrs)
            <div class="alert alert-warning">
                <strong>There is an existing MRS request that has been SAVED. New items on the cart will be added to the existing SAVED request.</strong>
            </div>
        @endif
        <form method="post" action="{{ route('cart.temp_sales') }}" id="chk_form" enctype="multipart/form-data">
            @csrf
            <h3 class="border-bottom pb-3">Review and Place Request</h3>
            <div class="row">
                <div class="col-3 form-group">
                    <label>Priority #</label>
                    <select name="priority" required onchange="$('.priority_no').html(this.value)" class="form-select">
                        <option value="1" {{ $mrs ? ($mrs->priority == '1' ? 'selected' : '') : '' }}>Priority 1</option>
                        <option value="2" {{ $mrs ? ($mrs->priority == '2' ? 'selected' : '') : '' }}>Priority 2</option>
                        <option value="3" {{ $mrs ? ($mrs->priority == '3' ? 'selected' : '') : '' }}>Priority 3</option>
                    </select>
                </div>
                
                <div class="col-3 form-group">
                    <label>Date Needed</label>
                    <input type="date" value="{{ $mrs ? $mrs->delivery_date : '' }}"  class="form-control date_needed" name="date_needed" onchange="change_date(this.value)" required>
                </div>

                <div class="col-3 form-group">
                    <label for="codeType">Code Type</label>
                    <select id="codeType" class="form-select" required>
                        <option value="CC">CC</option>
                        <option value="JC">JC</option>
                    </select>
                </div>

                <div class="col-3 form-group">
                    <label for="shippingType"><span id="labelCode">Cost Code</span> <span id="loader"><i class="fa fa-spin icon-reload"></i></span></label>
                    <input type="text" value="{{ $mrs ? $mrs->costcode : '' }}" class="form-control" name="costcode" id="costcode" height="200" required>
                </div>

                <div class="col-3 form-group">
                    <label for="isBudgeted" class="fw-semibold text-inital nols">Budgeted?</label>
                    <select id="isBudgeted" name="isBudgeted" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <div class="col-3 form-group budgetAmount">
                    <label>Budget amount</label>
                    <input type="number" value="{{ $mrs ? number_format($mrs->budgeted_amount, 2, '.', '') : '' }}" step="0.01" id="budgeted_amount" name="budgeted_amount" class="form-control">
                </div>

                <div class="col-6 form-group">
                    <label>Note</label>
                    <input type="text" value="{{ $mrs ? $mrs->purpose : '' }}" class="form-control" name="justification" onkeyup="$('.purpose').val(this.value)" required>
                </div>

                <div class="col-6 form-group">
                    <label>Department</label>
                    <input type="text" class="form-control" name="department" value="{{ auth()->user()->department->name }}" disabled required>
                </div>
                
                <div class="col-6 form-group">
                    <label>Section</label>
                    <input type="text" value="{{ $mrs ? $mrs->section : '' }}" class="form-control" id="section" name="section" required>
                </div>
            </div>
            <input type="hidden" name="shipping_type" value="Pickup">
            
            <div class="table-responsive mb-5">
                <table class="table table-striped dataTable w-100 mn-wd-1100-f">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Priority#</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Product</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Code</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">OEM</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Purpose</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">PAR To</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Date Needed</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Frequency</th>
                            <th scope="col" class="ls1 fs-14-f fw-bold text-uppercase text-gray">Quantity</th>
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
                                <td class="align-middle priority_no">
                                    <center>
                                        @if($mrs)
                                            {{ $mrs->priority }}
                                        @endif
                                    </center>
                                </td>
                                <td class="align-middle">
                                    <div class="top-cart-item">
                                        <div class="top-cart-item-image wd-70-f ht-70-f">
                                            <a href="#" class="wd-70-f ht-70-f"><img class="wd-70-f ht-70-f" src="{{ $order->product->photoPrimary }}" onerror="this.src='{{ asset('images/1667370521_download.jpg') }}'"  alt="{{ $order->product->name }}"></a>
                                        </div>
                                        <div class="top-cart-item-desc">
                                            <div class="top-cart-item-desc-title">
                                                <a href="#" class="fs-16-f fw-normal lh-base">{{ $order->product->name }}</a>
                                                Cost Code: <select class="costcode-option" name="codes[]" required></select>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    {{ $order->product->code }}
                                </td>
                                <td class="align-middle">
                                    {{ $order->product->oem }}
                                </td>
                                <td class="align-middle">
                                    <input type="text" value="{{ $order->mrs_details->purpose ?? '' }}" class="form-control purpose" required name="item_purpose[]">
                                </td>
                                <td class="align-middle">
                                    <select class="form-select employees" name="par_to[]" data-par-to="{{ $order->mrs_details->par_to ?? ''}}">
                                        
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
                                    </select>
                                </td>
                                
                                
                                <td class="align-middle">{{ $order->qty }} {{ $order->product->uom }}(s)</td>
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
                        <input type="file" name="attachment[]" class="form-control" multiple>
                        <div id="fileList" style="margin-top: 10px;"></div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="notes">Delivery Instruction</label>
                        <textarea id="notes" class="form-control form-input" name="notes" rows="6" required>{{ $mrs ? $mrs->other_instruction : '' }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="requested_by" class="fw-semibold text-inital nols">Requested by</label>
                        <select id="requested_by" name="requested_by" class="form-select" required >
                            
                        </select>
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
                <input type="submit" class="button button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow px-5" value="Place Request">
            </div>
        </form>
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

<script>
    /*
    this line is brought to you by
    */

    var mrs = "{{ $mrs }}";
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
            var date = new Date();
            var year = date.getFullYear();
            var month = (date.getMonth() + 1).toString().padStart(2, '0');
            var day = date.getDate().toString().padStart(2, '0');
            var formattedDate = `${year}-${month}-${day}`;
            $('.date_needed').val(formattedDate);
        }
        var today = new Date().toISOString().split('T')[0];
        $('.date_needed').attr('min', today);

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
        

        employee_lookup();
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

    function employee_lookup() {
        if (localStorage.getItem("EMP") !== null) {
            let values = localStorage.getItem("EMP");
            initEmpValues(values.split("|"));
        }else{
            $.ajax({
                type: 'GET',
                url: "{{ route('users.employee_lookup') }}",
                success: function(data){
                    try {
                        var employeesArray = JSON.parse(data);
                        let values = employeesArray.map(item => item.fullnamewithdept).join('|');
                        //console.log(values);
                        localStorage.setItem("EMP", values);
                        initEmpValues(values.split("|"))
                    } catch (e) {
                        console.error("Error parsing JSON: ", e);
                    }
                }
            });
        }
    }

    function initEmpValues(employeesArray){
        $('#requested_by').empty();
        $('.employees').empty();
        $('#requested_by').append('<option value="" disabled selected>Select an employee</option>');
        $('.employees').append('<option value="N/A" selected>Select an employee</option>');
        employeesArray.forEach(function(employee) {
            var fullname = employee.split(":")[0];
            $('.employees').append('<option value="' + employee + '">' + fullname + '</option>');
            $('#requested_by').append('<option value="' + employee + '">' + fullname + '</option>');
        });
        if(mrs){
            var decodedJson = mrs.replace(/&quot;/g, '"');
            var jsonObject = JSON.parse(decodedJson);
            $("#requested_by").val(jsonObject.requested_by);
            $('.employees').each(function() {
                var $select = $(this);
                var parToValue = $select.data('par-to');  // Get the par_to value from the data attribute

                // Find the option that matches parToValue and set it as selected
                $select.find('option').each(function() {
                    if ($(this).val() === parToValue) {
                        $(this).prop('selected', true);
                    }
                });

            });
        }
    }

    function change_date(date){
        $('.date_needed').val(date);
    }
</script>

@endsection