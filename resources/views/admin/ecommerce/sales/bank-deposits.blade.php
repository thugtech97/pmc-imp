@extends('admin.layouts.app')

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet">

    <style>
        .table td {
            padding: 10px;
            font-size: 13px;
        }
    </style>
@endsection

@section('content')

    <div class="container pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5" style="background-color:white;">
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sales Transaction</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Bank Deposits</h4>
            </div>
        </div>

        <div class="row row-sm">


            <!-- Start Pages -->
            <div class="col-md-12">
                <div class="table-list mg-b-10">
                    <table class="table mg-b-0 table-light table-hover" id="table_sales">
                        <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Customer</th>
                            <th>Request Status</th>
                            <th>Payment Date</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                            <th>is Verified ?</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        @forelse($payments as $payment)
                            <tr class="pd-20">
                                <td><strong> {{$payment->order_details->order_number }}</strong></td>
                                <td>{{ $payment->order_details->customer_name }}</td>
                                <td>{{ $payment->order_details->delivery_status }}</td>
                                <td>{{ $payment->payment_date }}</td>
                                <td>{{ number_format($payment->amount,2) }}</td>
                                <td>{{ $payment->order_details->payment_status }}</td>
                                <td>{{ $payment->is_verified == 0 ? 'No' : 'Yes' }}</td>
                                <td>
                                    <nav class="nav table-options">
                                        <a class="dropdown-item off-canvas-menu" href="#details{{$payment->id}}" title="View Details"><i data-feather="eye"></i></a>
                                    </nav>
                                </td>
                            </tr>
                            <div id="details{{$payment->id}}" class="off-canvas off-canvas-overlay off-canvas-right wd-600">
                                <a href="#" class="close"><i data-feather="x"></i></a>
                                <div class="pd-25 ht-100p tx-13">
                                    <div class="contact-content-sidebar mg-t-20">
                                        <h5 id="contactName" class="tx-18 tx-xl-20 mg-b-5">{{$payment->receipt_number }}</h5>
                                        @if($payment->is_verified == 0)
                                            <span class="badge badge-danger">Not Verified</span>
                                        @else
                                            <span class="badge badge-success">Verified</span>
                                        @endif

                                        <hr class="mg-y-20"> 
                                        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-10">Payment Details</label>
                                        <a href="#" class="nav-link">Type : {{ $payment->payment_type }}</a>
                                        <a href="#" class="nav-link">Date : {{ $payment->payment_date }}</a>
                                        <a href="#" class="nav-link">Amount : {{ number_format($payment->amount,2) }}</a>
                                        <a href="#" class="nav-link">Remarks : {{ $payment->remarks}}</a>

                                        <hr class="mg-y-20">
                                        <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-15">Order Details</label>
                                        <nav class="nav flex-column contact-content-nav mg-b-25">
                                            <a href="#" class="nav-link">Order Number : {{$payment->order_details->order_number }}</a>
                                            <a href="#" class="nav-link">Status : {{$payment->order_details->delivery_status }}</a>
                                            <a href="" class="nav-link">Customer : {{$payment->order_details->customer_name }}</a>
                                            <a href="" class="nav-link">Email : {{$payment->order_details->customer_email }}</a>
                                            <a href="" class="nav-link">Contact # : {{$payment->order_details->customer_contact_number }}</a>
                                            <a href="" class="nav-link">Delivery Address : {{$payment->order_details->customer_delivery_adress }}</a>
                                        </nav>
                                    </div>

                                    <hr class="mg-y-20">
                                    <label class="tx-10 tx-medium tx-spacing-1 tx-color-03 tx-uppercase tx-sans mg-b-15">Attachments</label>
                                    <br>
                                    <a href="{{ asset('storage/payments/'.$payment->id.'/'.$payment->attachment) }}" target="_blank">{{ $payment->attachment }}</a><br>

                                    @if($payment->is_verified == 0)
                                    <hr class="mg-y-20">
                                    <a href="{{ route('validate-payment',[$payment->id, 1]) }}" class="btn btn-sm btn-success">Verified</a>
                                    <a href="{{ route('validate-payment',[$payment->id, 0]) }}" class="btn btn-sm btn-danger">Unverified</a>
                                    @endif
                                    
                                </div>
                            </div>
                        @empty
                            <tr>
                                <th colspan="8" style="text-align: center;"> <p class="text-danger">No Bank Deposits found.</p></th>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End Pages -->
            <div class="col-md-6" style="display:none;">
                <div class="mg-t-5">
                    @if ($payments->firstItem() == null)
                        <p class="tx-gray-400 tx-12 d-inline">{{__('common.showing_zero_items')}}</p>
                    @else
                        <p class="tx-gray-400 tx-12 d-inline">Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} items</p>
                    @endif
                </div>
            </div>
            
        </div>
    </div>

    <form action="" id="posting_form" style="display:none;" method="post">
        @csrf
        <input type="text" id="pages" name="pages">
        <input type="text" id="status" name="status">
    </form>

    @include('admin.ecommerce.sales.modals')
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>

    <script>
        let listingUrl = "{{ route('sales-transaction.index') }}";

    </script>

    <script src="{{ asset('js/listing.js') }}"></script>

    <script>
        $(function(){
            'use strict'

            $('.off-canvas-menu').on('click', function(e){
                e.preventDefault();
                var target = $(this).attr('href');
                $(target).addClass('show');

                $('.backdrop').css({
                  opacity: 1,
                  visibility: 'visible'
                });
            });


            $('.off-canvas .close').on('click', function(e){
                e.preventDefault();
                $(this).closest('.off-canvas').removeClass('show');
                $('.backdrop').css({
                  opacity: 0,
                  visibility: 'hidden'
                });
            })

            $(document).on('click touchstart', function(e){
                e.stopPropagation();

                // closing of sidebar menu when clicking outside of it
                if(!$(e.target).closest('.off-canvas-menu').length) {
                    var offCanvas = $(e.target).closest('.off-canvas').length;
                    if(!offCanvas) {
                        $('.off-canvas.show').removeClass('show');
                        $('.backdrop').css({
                          opacity: 0,
                          visibility: 'hidden'
                        });
                    }
                }
            });
        });
    </script>
@endsection

@section('customjs')
    <script>

        function delete_sales(x,order_number){
            $('#frm_delete').attr('action',"{{route('sales-transaction.destroy',"x")}}");
            $('#id_delete').val(x);
            $('#delete_order_div').html(order_number);
            $('#prompt-delete').modal('show');
        }

        function show_added_payments(id){
            $.ajax({
                type: "GET",
                url: "{{ route('display.added-payments') }}",
                data: { id : id },
                success: function( response ) {
                    $('#added_payments_tbl').html(response);
                    $('#prompt-show-added-payments').modal('show');
                }
            });
        }

        function show_delivery_history(id){
            $.ajax({
                type: "GET",
                url: "{{ route('display.delivery-history') }}",
                data: { id : id },
                success: function( response ) {
                    $('#delivery_history_tbl').html(response);
                    $('#prompt-show-delivery-history').modal('show');
                }
            });
        }

        function post_form(id,status,pages){

            $('#posting_form').attr('action',id);
            $('#pages').val(pages);
            $('#status').val(status);
            $('#posting_form').submit();
        }

        $(".js-range-slider").ionRangeSlider({
            grid: true,
            from: selected,
            values: perPage
        });


        $('#prompt-change-status').on('show.bs.modal', function (e) {
            //get data-id attribute of the clicked element
            let sales = e.relatedTarget;
            let salesId = $(sales).data('id');
            let salesStatus = $(sales).data('status');
            let formAction = "{{ route('sales-transaction.quick_update', 0) }}".split('/');
            formAction.pop();
            let editFormAction = formAction.join('/') + "/" + salesId;
            $('#editForm').attr('action', editFormAction);
            $('#id').val(salesId);
            $('#editStatus').val(salesStatus);

        });

        function change_delivery_status(id){
            var checked = $('.cb:checked');
            
            var count = checked.length;

            if(count == 1){
                checked.each(function () {
                    $('#del_id').val($(this).val());
                });
            }

            if(count > 1) {

                var ids = [];
                checked.each(function(){
                    ids.push(parseInt($(this).val()));
                });

                $('#del_id').val(ids.join(','));
            }
            if(count < 1){
                $('#del_id').val(id);
            }

            $('#prompt-change-delivery-status').modal('show');
        }
    </script>
@endsection
