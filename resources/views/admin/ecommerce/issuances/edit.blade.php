@extends('admin.layouts.app')

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-product.css') }}" rel="stylesheet">
    <script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
    <style>
        #errorMessage {
            list-style-type: none;
            padding: 0;
            margin-bottom: 0px;
        }
        .image_path {
            opacity: 0;
            width: 0;
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">ECOMMERCE</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><a href="{{route('products.index')}}">Issuances</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Issuance</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Edit a Issuance</h4>
            </div>
        </div>
        <form id="updateForm" method="POST" action="{{ route('sales-transaction.issuance.update', $issuance->first()->issuance_no) }}" enctype="multipart/form-data">
            <div class="row row-sm">
       
                @csrf
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="d-block">Release Date</label>
                        <input name="release_date" id="release_date" value="{{ old('release_date', $issuance->first()->release_date) }}" type="text" class="form-control @error('code') is-invalid @enderror" maxlength="150">
                        @error('release_date')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="d-block">Received By</label>
                        <input required name="received_by" id="received_by" value="{{ old('received_by', $issuance->first()->received_by) }}" type="text" class="form-control @error('name') is-invalid @enderror" maxlength="150">
                        @error('received_by')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="d-block">Issued By</label>
                        <input type="text" class="form-control @error('issued_by') is-invalid @enderror" name="issued_by" id="issued_by" value="{{ old('issued_by', $issuance->first()->issued_by) }}">
                        @error('issued_by')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="d-block">Issued Number</label>
                        <input type="text" class="form-control @error('issuance_no') is-invalid @enderror" name="issuance_no" id="issuance_no" value="{{ old('issuance_no', $issuance->first()->issuance_no) }}" disabled="disabled">
                        @error('issuance_no')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            <div class="row row-sm" style="overflow-x: auto">
                <table class="table table-bordered mg-b-10">
                    <tbody>
                        <tr style="background-color:#000000;">
                            <th class="text-white wd-30p">Item</th>
                            <th class="text-white tx-center">Ordered Quantity</th>
                            <th class="text-white tx-center">Issued Quantity</th>
                            <th class="text-white tx-center">Balance</th>
                            <!--<th class="text-white tx-center">Unit Price</th>-->
                            <th class="text-white tx-center">Issuance</th>
                        
                            <!--<th class="text-white tx-center" style="width:20%">Actions</th>-->
                        </tr>
                @forelse($issuance as $details)

                    @php

                    $bal = $details->orderDetails->qty - $details->qty;
                    @endphp
                    
                 
                    <tr class="pd-20">
                        <td class="tx-nowrap">{{$details->orderDetails->product_name}}</td>
                        <td class="tx-right">{{ number_format($details->orderDetails->qty, 2) }}</td>
                        <td class="tx-right">{{ $details->qty }}</td>
                        <td class="tx-right">{{ number_format($bal,2) }}</td>
                        <td class="tx-right">
                            <input class="form-control" type="number" name="deploy{{ $details->issuance_no.$details->orderDetails->id }}" required="required" min="0" value="{{$details->qty}}">                            
                        </td>
                     

                        
                        <!--<td class="tx-right">{{number_format($details->price, 2)}}</td>-->

                        <!--<td>
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#issuanceDetailsModal{{ $details->id }}" class="btn btn-success btn-sm">Issuances</a>
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#issuanceModal{{ $details->id }}" class="btn btn-primary btn-sm">Issue Items</a>
                        </td>-->
                    </tr>
                @empty
                    <tr>
                        <td class="tx-center " colspan="6">No transaction found.</td>
                    </tr>
                @endforelse
                    </tbody>
                </table>
            </div>

                <div class="col-lg-12 mg-t-30">
                    <input class="btn btn-primary btn-sm btn-uppercase" type="submit" value="Update Issuance">
                    <a href="{{ route('sales-transaction.issuance.index') }}" class="btn btn-outline-secondary btn-sm btn-uppercase">Cancel</a>
                </div>
            </div>
        </form>
        <!-- row -->
    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.min.js') }}"></script>

    <script src="{{ asset('lib/jqueryui/jquery-ui.min.js') }}"></script>

    {{--    Image validation--}}
    <script>
        let BANNER_WIDTH = "{{ env('PRODUCT_WIDTH') }}";
        let BANNER_HEIGHT =  "{{ env('PRODUCT_HEIGHT') }}";
        let MAX_IMAGE =  5;
    </script>
    <script src="{{ asset('js/image-upload-validation.js') }}"></script>
    {{--    End Image validation--}}
@endsection


@section('customjs')
	<script>
        var options = {
            filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
            filebrowserImageUpload: '/laravel-filemanager/upload?type=Images&_token=',
            filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
            filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token',
            allowedContent: true,

        };
        let editor = CKEDITOR.replace('long_description', options);
        editor.on('required', function (evt) {
            if ($('.invalid-feedback').length == 1) {
                $('#long_descriptionRequired').show();
            }
            $('#cke_editor1').addClass('is-invalid');
            evt.cancel();
        });

        $(function() {
            let image_count = 1;
            let objUpload;
            let objRemove;
            $('.selectpicker').selectpicker();

            $("#customSwitch1").change(function() {
                if(this.checked) {
                    $('#label_visibility').html('Published');
                }
                else{
                    $('#label_visibility').html('Private');
                }
            });

            $("#customSwitch3").change(function() {
                if(this.checked) {
                    $('#label_visibility3').html('Yes');
                }
                else{
                    $('#label_visibility3').html('No');
                }
            });

            

            $(document).on('click', '.upload', function() {
                objUpload = $(this);
                $('#upload_image').click();
            });

            $('#upload_image').on('change', function (evt) {
                let files = evt.target.files;
                let uploadedImagesLength = $('.productImage').length;
                let totalImages = uploadedImagesLength + files.length;

                if (totalImages > 5) {
                    $('#bannerErrorMessage').html("You can upload up to 5 images only.");
                    $('#prompt-banner-error').modal('show');
                    return false;
                }

                if (totalImages == 5) {
                    $('#addMoreBanner').hide();
                }

                validate_images(evt, upload_image);
            });

            function upload_image(file)
            {
                let data = new FormData();
                data.append("_token", "{{ csrf_token() }}");
                data.append("banner", file);
                $.ajax({
                    data: data,
                    type: "POST",
                    url: "{{ route('products.upload') }}",
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(returnData) {
                        $('#bannersDiv').show();
                        $('#selectImages').hide();
                        console.log(returnData);
                        if (returnData.status == "success") {
                            while ($('input[name="photos['+image_count+'][image_path]"]').length) {
                                image_count += 1;
                            }

                            let radioCheck = (image_count == 1) ? 'checked' : '';

                            $(`<li class="productImage" id="`+image_count+`li">
                                <div class="prodmenu-left" data-toggle="modal" data-target="#image-details" data-id="`+image_count+`" data-name="`+returnData.image_name+`">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal">
                                        <circle cx="12" cy="12" r="1"></circle>
                                        <circle cx="19" cy="12" r="1"></circle>
                                        <circle cx="5" cy="12" r="1"></circle>
                                    </svg>
                                </div>
                                <div class="prodmenu-right" data-toggle="modal" data-target="#remove-image" data-id="`+image_count+`">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </div>
                                <label class="form-check-label radio" for="exampleRadios`+image_count+`" data-toggle="tooltip" data-placement="bottom" title="Set as primary image">
                                    <input class="form-check-input imageRadio" type="radio" name="is_primary" id="exampleRadios`+image_count+`" value="`+image_count+`" `+radioCheck+`>
                                    <input name="photos[`+image_count+`][image_path]" type="hidden" value="`+returnData.image_url+`">
                                    <input name="photos[`+image_count+`][name]" type="hidden" id="`+image_count+`name" value="">
                                    <img src="`+returnData.image_url+`" />
                                    <div class="radio-button"></div>
                                </label>
                            </li>`).insertBefore('#addMoreBanner');
                        }
                    },
                    failed: function() {
                        alert('FAILED NGA!');
                    }
                });
            }

            let selectedImage;
            $('#image-details').on('show.bs.modal', function(e) {
                selectedImage = e.relatedTarget;
                let imageId = $(selectedImage).data('id');
                let imageName = $(selectedImage).data('name');
                $('#fileName').val(imageName);
                $('#changeAlt').val($('#'+imageId+"name").val());
            });

            $('#saveChangeAlt').on('click', function() {
                let imageId = $(selectedImage).data('id');
                $('#'+imageId+"name").val($('#changeAlt').val());
                $('#image-details').modal('hide');
            });

            $('#image-details').on('hide.bs.modal', function() {
                $('#fileName').val('');
                $('#changeName').val('');
                selectedImage = null;
            });

            $('#remove-image').on('show.bs.modal', function(e) {
                selectedImage = e.relatedTarget;
            });

            $('#removeImage').on('click', function() {
                let imageId = $(selectedImage).data('id');
                let attrImageId = $(selectedImage).data('image-id');
                if (attrImageId) {
                    $('#updateForm').prepend('<input type="hidden" name="remove_photos[]" value="'+attrImageId+'">');
                }

                let isChecked = $('#exampleRadios'+imageId).is(':checked');
                $('#'+imageId+"li").remove();
                $('#addMoreBanner').show();
                if (isChecked) {
                    $.each($('.imageRadio'), function () {
                        $(this).prop('checked', true);
                        return false;
                    });
                }
                $('#remove-image').modal('hide');
            });

            $('#image-details').on('hide.bs.modal', function() {
                imageId = 0;
            });
        });
       
        $(document).on('click', '.remove-upload', function() {
            $('#prompt-remove').modal('show');
        });
        $('#btnRemove').on('click', function() {
            $('#updateForm').prepend('<input type="hidden" name="delete_zoom" value="1"/>');            
            $('#zoom_div').show();
            $('#prompt-remove').modal('hide');
            $('#zoomimage_div').remove();
            
        });
    </script>
@endsection
