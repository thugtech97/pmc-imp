@extends('admin.layouts.app')


@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('product-categories.index')}}">Stocks</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create a Stock Category</li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">Create a Stock Category</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('product-categories.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('POST')
                    <div class="form-group">
                        <label class="d-block">Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name')}}" class="form-control @error('name') is-invalid @enderror" maxlength="150">
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <small id="category_slug"></small>
                    </div>

                    <div class="form-group">
                        <label class="d-block">Parent Category</label>
                        <select id="parentPage" class="selectpicker mg-b-5 @error('parent_page') is-invalid @enderror" name="parent_page" data-style="btn btn-outline-light btn-md btn-block tx-left" title="- None -" data-width="100%">
                            <option value="0" selected>- None -</option>
                            @foreach ($productCategories as $productCategory)
                                <option value="{{ $productCategory->id }}">{{ $productCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="d-block">Description *</label>
                        <textarea required rows="3" class="form-control @error('description') is-invalid @enderror" name="description">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="d-block">Page Visibility</label>
                        <div class="custom-control custom-switch @error('visibility') is-invalid @enderror">
                            <input type="checkbox" class="custom-control-input" name="visibility" {{ (old("visibility") ? "checked":"") }} id="customSwitch1">
                            <label class="custom-control-label" id="label_visibility" for="customSwitch1">Private</label>
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('category_image') ? 'has-error' : '' }}">
                        <label class="d-block">Featured Image</label>
                        <div class="custom-file">
                            <input type="file" class="form-control" id="category_image" name="category_image">
                            <span class="text-danger tx-12">{{ $errors->first('category_image') }}</span>
                        </div>
                        <p class="tx-10">
                            Maximum file size: 1MB <br /> File extension: PNG, JPG, SVG
                        </p>
                        @if(empty($productCategory->image))
                            <div id="image_div" style="display:none;">
                                <img src="" id="img_temp" height="100" width="300" alt="Company Logo">  <br /><br />
                            </div>
                        @else
                            <div>
                                <img src="{{ asset('storage/logos/'.$productCategory->image) }}" id="img_temp" height="100" width="300" alt="Company Logo" style="max-width: 100%;">  <br /><br />
                                <button type="button" class="btn btn-danger btn-xs btn-uppercase remove-logo" type="button" data-id=""><i data-feather="x"></i> Remove Logo</button>
                            </div>
                        @endif
                    </div>

                    <button class="btn btn-primary btn-sm btn-uppercase" type="submit">Create Category</button>
                    <a class="btn btn-outline-secondary btn-sm btn-uppercase" href="{{ route('product-categories.index') }}">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
@endsection

@section('customjs')
    <script>
        $(document).on('click', '.remove-logo', function() {
            $('#prompt-remove-logo').modal('show');
        });

        // Company Logo
        $("#category_image").change(function() {
            readLogo(this);
        });

        function readLogo(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#img_temp').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
                $('#image_div').show();
                $('.remove-logo').hide();
            }
        }
        
        $(function() {
            $('.selectpicker').selectpicker();
        });

        $("#customSwitch1").change(function() {
            if(this.checked) {
                $('#label_visibility').html('Published');
            }
            else{
                $('#label_visibility').html('Private');
            }
        });

        /** Generation of the page slug **/
        jQuery('#name').change(function(){

            var url = $('#name').val();

            $.ajaxSetup({
                headers:{
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content")
                }
            })

            $.ajax({
                type: "POST",
                url: "/admin/product-category-get-slug",
                data: { url: url }
            })

            .done(function(response){
                slug_url = '{{env('APP_URL')}}/product-categories/'+response;
                $('#category_slug').html("<a target='_blank' href='"+slug_url+"'>"+slug_url+"</a>");
            });
        });
    </script>
@endsection
