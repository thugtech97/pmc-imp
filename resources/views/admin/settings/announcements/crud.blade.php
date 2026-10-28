@extends('admin.layouts.app')

@section('pagetitle')
    isset($edit) ? 'Edit Announcement ' . $edit->name : 'Create Announcement'
@endsection

@section('pagecss')
    <script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
@endsection

@section('content')

<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('announcements.index')}}">Announcements</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ isset($edit) ? 'Edit Announcement ' . $edit->name : 'Create Announcement' }}</li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">{{ isset($edit) ? 'Edit Announcement ' . $edit->name : 'Create Announcement' }}</h4>
        </div>
    </div>
    <form method="post" action="{{ isset($edit) ? route('announcements.update', $edit->id) : route('announcements.store') }}" enctype="multipart/form-data">
        <div class="row row-sm">
            <div class="col-lg-6">
                @csrf
                @if (isset($edit))
                    @method('PUT')
                @else
                    @method('POST')
                @endif
                <div class="form-group">
                    <label class="d-block">Name *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ isset($edit) ? $edit->name : old('name') }}" required @htmlValidationMessage({{__('standard.empty_all_field')}})>
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror

                    <small id="page_slug"></small>
                    @error('slug')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-lg-6">
                <label class="d-block">Post Date</label>
                <input type="date" class="form-control @error('name') is-invalid @enderror" name="schedule" id="name" value="{{ isset($edit) ? $edit->schedule : old('schedule') }}" required @htmlValidationMessage({{__('standard.empty_all_field')}})>
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-lg-12">
                <div class="form-group">
                    <label class="d-block">Content <span class="tx-danger">*</span></label>
                    <textarea required name="content" id="editor1" rows="10" cols="80">
                        {!! isset($edit) ? $edit->content : old('content') !!}
                    </textarea>
                    <span class="invalid-feedback" role="alert" id="contentRequired" style="...">
                        <strong>The content field is required</strong>
                    </span>
                    <script>
                        // Replace the <textarea id="editor1"> with a CKEditor
                        // instance, using default configuration.
                        var options = {
                            filebrowserImageBrowseUrl: '{{ env('APP_URL') }}/laravel-filemanager?type=Images',
                            filebrowserImageUpload: '{{ env('APP_URL') }}/laravel-filemanager/upload?type=Images&_token={{ csrf_token() }}',
                            filebrowserBrowseUrl: '{{ env('APP_URL') }}/laravel-filemanager?type=Files',
                            filebrowserUploadUrl: '{{ env('APP_URL') }}/laravel-filemanager/upload?type=Files&_token={{ csrf_token() }}',
                            allowedContent: true,
                        };
                        let editor = CKEDITOR.replace('content', options);
                        editor.on('required', function (evt) {
                            if($('.invalid-feedback').length == 1){
                                $('#contentRequired').show();
                            }
                            $('#cke_editor1').addClass('is-invalid');
                            evt.cancel();
                        });
                    </script>
                    @error('content')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    <div class="alert alert-danger" id="contentRequired" style="display: none;">The content field is required</div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="form-group">
                    <label class="d-block">Post Location</label>
                    <select id="location" class="form-control" name="location">
                        <option value="after-login" {{ isset($edit) && $edit->location == 'after-login' ? 'selected' : '' }}>After Login</option>
                        <option value="upon-ordering" {{ isset($edit) && $edit->location == 'upon-ordering' ? 'selected' : '' }}>Upon Ordering</option>
                        <option value="after-checkout" {{ isset($edit) && $edit->location == 'after-checkout' ? 'selected' : '' }}>After Checkout</option>
                        <option value="product" {{ isset($edit) && $edit->location == 'product' ? 'selected' : '' }}>On Product</option>
                    </select>
                </div>
            </div>

            <div id="productForm" class="col-lg-6">
                <div class="form-group">
                    <label class="d-block">Product</label>
                    <select class="form-control" name="product_id">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ isset($edit) && $edit->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-12 mg-t-30">
                <input class="btn btn-primary btn-sm btn-uppercase" type="submit" value="Save Announcement">
                <a href="{{ route('announcements.index') }}" class="btn btn-outline-secondary btn-sm btn-uppercase">Cancel</a>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="preview-banner" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel3" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content tx-14">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel3">Preview</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="owl-carousel owl-theme" id="previewCarousel">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary tx-13" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagejs')
    <script>
        // jQuery Typing
        (function(f){function l(g,h){function d(a){if(!e){e=true;c.start&&c.start(a,b)}}function i(a,j){if(e){clearTimeout(k);k=setTimeout(function(){e=false;c.stop&&c.stop(a,b)},j>=0?j:c.delay)}}var c=f.extend({start:null,stop:null,delay:400},h),b=f(g),e=false,k;b.keypress(d);b.keydown(function(a){if(a.keyCode===8||a.keyCode===46)d(a)});b.keyup(i);b.blur(function(a){i(a,0)})}f.fn.typing=function(g){return this.each(function(h,d){l(d,g)})}})(jQuery);

        $(document).ready( function($){
            $('#productForm').hide();
            
            if ($('#location').val() === 'product') {
                $('#productForm').show();
            }

            $('#location').change( function() {
                if ($(this).val() === 'product') {
                    $('#productForm').show();
                }
                else {
                    $('#productForm').hide();
                }
            });

            $('#icons-filter').typing({
                stop: function (event, $elem) {
                    var filterValue = $elem.val(),
                        count = 0;

                    if( $elem.val() ) {

                        $(".icons-list li").each(function(){
                            if ($(this).text().search(new RegExp(filterValue, "i")) < 0) {
                                $(this).fadeOut();
                            } else {
                                $(this).show();
                                count++
                            }
                        });
                    } else {
                        $(".icons-list li").show();
                    }

                    count = 0;
                },
                delay: 500
            });

        });
    </script>
    <script>
        let jsPage = "";
        let jsHtml = "";
        let jsStyle = "";
    </script>
    <script src="{{ asset('lib/custom-grapesjs/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/owl.carousel/owl.carousel.js') }}"></script>
    <script src="{{ asset('js/file-upload-validation.js') }}"></script>
    <script src="{{ asset('vendor/laravel-filemanager/js/stand-alone-button-2.js') }}"></script>

    <script src="{{ asset('lib/custom-grapesjs/grapesjs/dist/grapes.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-blocks-basic.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-pkurg-bootstrap4-plugin.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-lory-slider.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-touch.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-parser-postcss.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-tooltip.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-tui-image-editor.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-typed.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-style-bg.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/tui-code-snippet.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/tui-color-picker.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-plugin-ckeditor.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-plugin-export.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-blocks-bootstrap4.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/b4bulder-custom-blocks.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-preset-webpage.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-plugin-animation.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/grapesjs-plugins/grapesjs-swiper-slider.min.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/assets/js/custom-grapesjs.js') }}"></script>
    <script src="{{ asset('lib/custom-grapesjs/assets/js/bamburgh.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.0.0/typed.min.js"></script>
@endsection


@section('customjs')
<script>
        // jQuery Typing
        (function(f){function l(g,h){function d(a){if(!e){e=true;c.start&&c.start(a,b)}}function i(a,j){if(e){clearTimeout(k);k=setTimeout(function(){e=false;c.stop&&c.stop(a,b)},j>=0?j:c.delay)}}var c=f.extend({start:null,stop:null,delay:400},h),b=f(g),e=false,k;b.keypress(d);b.keydown(function(a){if(a.keyCode===8||a.keyCode===46)d(a)});b.keyup(i);b.blur(function(a){i(a,0)})}f.fn.typing=function(g){return this.each(function(h,d){l(d,g)})}})(jQuery);

        jQuery(document).ready( function($){

            $('#icons-filter').typing({
                stop: function (event, $elem) {
                    var filterValue = $elem.val(),
                        count = 0;

                    if( $elem.val() ) {

                        $(".icons-list li").each(function(){
                            if ($(this).text().search(new RegExp(filterValue, "i")) < 0) {
                                $(this).fadeOut();
                            } else {
                                $(this).show();
                                count++
                            }
                        });
                    } else {
                        $(".icons-list li").show();
                    }

                    count = 0;
                },
                delay: 500
            });

        });
    </script>
@endsection
