@extends('catalogue.layouts.main')

@section('pagecss')
    <link rel="stylesheet" type="text/css" href="{{ asset('theme/catalogue/css/settings.css') }}" media="screen" />
    <link rel="stylesheet" type="text/css" href="{{ asset('theme/catalogue/css/layers.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('theme/catalogue/css/navigation.css') }}">

    <style>
        #footbady {
            background-image: url("{{ asset('theme/images/world-map.png') }}") no-repeat center center;
            background-size: 100%;
        }
        .bgwhite{
        	background-color: white !important;
        	padding: 30px;
        }
        .border{
        	border-color: #F8F8F8;
        }
        .rounded {
		    .border-radius( 5px );
		}
		.gradent{
			background: rgb(247,247,247);
			background: radial-gradient(circle, rgba(247,247,247,1) 6%, rgba(245,249,248,0.8995973389355743) 61%, rgba(250,250,250,0.9668242296918768) 96%);
		}
		.is-active {
			background: #f6931d;
			padding: 4px 10px !important;
			color: #fff;
		}
		.is-active a {
			color: #fff !important;
		}
		.prod-name {
		   overflow: hidden;
		   text-overflow: ellipsis;
		   display: -webkit-box;
		   -webkit-line-clamp: 3; /* number of lines to show */
		           line-clamp: 3; 
		   -webkit-box-orient: vertical;
		}
    </style>
@endsection

@section('content')
	<section class="py-4 py-lg-5 bg-color position-relative">
		<div class="container">
			<div class="mb-0">
				<div class="before-heading font-secondary text-dark fw-semibold fs-12-f">Materials Control Department</div>
				<h1 class="text-dark mt-2 fs-30 fs-lg-40 mb-0 nols lh-sm">E-commerce and Warehousing System</h1>
			</div>
		</div>
	</section>
	
		<section id="page-title" class="page-title-nobg">

			<div class="container clearfix">
				<h1>SEARCH RESULTS {{ $category_name ? 'FOR CATEGORY "' . $category_name . '"' : '' }}</h1>
				<span>Product Catalogue</span>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="{{route('catalogue.home')}}">Catalogue</a></li>
					<li class="breadcrumb-item active" aria-current="page">Search Results</li>
				</ol>
			</div>

		</section>
		<section id="content" style="background-color: #F8F8F8 !important;">
			<div class="content-wrap">

				<div class="container clearfix">
					<div class="row">
						<div class="col-md-6">
							<form action="{{ route('catalogue.search') }}" method="POST" role="search">
								@csrf

								<h4>Did not find what you are looking for? Request an item <a href="{{ route('new-stock.index') }}">here</a></h4>
								
								<input type="hidden" name="category_id" value="{{ $category_id ?? '' }}">

								<div class="input-group mb-3">
									<input type="text" name="query" class="form-control" placeholder="Search by name / description / item code" value="{{ $query }}" aria-label="Recipient's username" aria-describedby="button-addon2">
									<button class="btn btn-outline-primary" type="submit" id="button-addon2"><i class="icon icon-search"></i></button>
								</div>
							</form>
						</div>
						<div class="col-md-6">
							<h4>Browse by Category:</h4>
							<select name="cat" id="cat" class="form-control" onchange="go_to_category_page($(this).val())">
								<option selected="selected"> - Select - </option>
								@forelse(\App\Models\Ecommerce\ProductCategory::all() as $c)
									<option value="{{$c->id}}" {{ $category_id == $c->id ? 'selected="selected"' : '' }}>{{ucwords(strtolower($c->description))}}</option>								
								@empty
								@endforelse
							</select>
						</div>
						
					</div>
				

					<div class="row gutter-40 col-mb-80">

						<div class="postcontent col-lg-12 order-lg-last">

							<div class="fancy-title title-border">
								<h3>Products</h3>
							</div>

							<div class="row posts-md col-mb-30">


								@forelse($products as $p)
									@php
										$images = \App\Models\Ecommerce\ProductPhoto::where('product_id',$p->id)->get();
										$small = $images->where('description','small.png')->first();
										$catalogue = $images->where('description','catalogue.png')->first();
									@endphp
									<div class="entry col-sm-6 col-md-3 grid-container">
										<a class="grid-item" href="{{ route('product.front.show', $p->slug) }}">
											<div class="grid-inner rounded bgwhite" style="padding: 10px;">
												<div class=" text-center" style="padding-bottom:10px;">
													<span class="btn btn-danger btn-sm">{{$p->code}}</span>
													<!--<span style="float:right">ID: {{ $p->id }}</span>-->
												</div>
												

                                                <div class="entry-image rounded gradent">
													@if (isset($small->path))
                                                    	<img src="{{ asset('storage/' . $p->PhotoSmall) }}" onerror="this.src='{{ asset('images/1667370521_download.jpg') }}'" alt="Image" class="img-fluid" style="max-height:200px;">
													@else
														<img src="{{ asset('images/no-image.png') }}" onerror="this.src='{{ asset('images/1667370521_download.jpg') }}'" alt="Image" class="img-fluid" style="height:200px;">
													@endif

                                                    <div class="sale-flash badge bg-secondary p-2">
                                                        @php
                                                            $stocks = $p->inventory;
                                                        @endphp
                                                        @if (!$stocks)
                                                            Out of Stock
                                                        @elseif ($stocks <= $p->critical_qty)
                                                            Stocks Available: <span style="color: red">{{ $stocks }}</span>
                                                        @else
                                                            Stocks Available: {{ $stocks }}
                                                        @endif
                                                    </div>

                                                    <div class="bg-overlay">
                                                        <div class="bg-overlay-content align-items-end justify-content-between" data-hover-animate="fadeIn" data-hover-speed="400">
                                                            <a href="javascript:;" onclick="add_to_cart('{{$p->id}}');" class="btn btn-dark" style="margin-right:140px"><i class="icon-shopping-cart"></i></a>
                                                            <a href="{{ asset('storage/'. optional($catalogue)->path) }}" class="btn btn-dark" data-lightbox="image" data-title="{{$p->code}}" data-footer="{{ucwords(strtolower(str_replace(',',', ',$p->name)))}}"><i class="icon-line-expand"></i></a>
                                                        </div>
                                                        <div class="bg-overlay-bg bg-transparent"></div>
                                                    </div>
                                                </div>

												<div class="entry-content">
													<p class="mb-0" style="font-size:14px;height:60px;" class="prod-name">
                                                        <a href="{{ route('product.front.show', $p->slug) }}">
                                                            {{ucwords(strtolower(str_replace(',',', ',$p->name)))}}
                                                        </a>
                                                    </p>

													<div class="quantity quantity-large me-0 w-100 justify-content-center flex-nowrap mt-3">
														<input type="button" value="-" class="minus border-top border-bottom">
														<input type="text" name="quantity[]" class="qty fs-12px wd-40-f border-0 border-top border-bottom" value="1" id="quantity{{$p->id}}" />
														<input type="button" value="+" class="plus border-top border-bottom">
													</div>
												</div>
											</div>
										</a>
									</div>
								@empty
									<div class="alert alert-warning" role="alert">
									  No product found for this category
									</div>
								@endforelse

                                {{ $products->appends(Request::all())->links() }}
							</div>
						</div>
						<div class="sidebar col-lg-3" style="display:none;">
							<div class="sidebar-widgets-wrap bgwhite rounded">

								<div class="widget widget_links clearfix">

									<h4>{{ $products->total() }} Found Results</h4>
                                    <p>You entered "{{ $query }}"</p>
								</div>

							</div>

							<div class="sidebar-widgets-wrap bgwhite mt-3 rounded">

								<div class="widget widget_links clearfix">

									<h4>Categories</h4>
									<ul>
										@forelse(\App\Models\Ecommerce\ProductCategory::all() as $c)
											<li class="{{ $category_id == $c->id ? 'is-active' : '' }}"><a href="{{route('catalogue.category_products',$c->id)}}">{{ucwords(strtolower($c->description))}}</a></li>
										@empty
										@endforelse
									</ul>

								</div>

							</div>
						</div>
					</div>

				</div>
			</div>
		</section>



@endsection

@section('pagejs')

	<!-- Footer Scripts
	============================================= -->
	<script src="{{ asset('theme/catalogue/js/functions.js') }}"></script>


	<script>
		$(document).ready(function() {

			$( ".bgwhite" ).hover(
				function() {
					$(this).addClass('shadow').css('cursor', 'pointer');
				}, function() {
					$(this).removeClass('shadow');
				}
				);

		});

		function go_to_category_page(i){
			window.location.href = "/PMC-ECOM/public/catalogue/category-products/"+i;
		}

        function add_to_cart(product){
			const qty = $('#quantity' + product).val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                data: {
                    "product_id": product,
                    "qty": qty,
                    "_token": "{{ csrf_token() }}",
                },
                type: "post",
                url: "{{route('product.add-to-cart')}}",
                success: function(returnData) {
                    $("#loading-overlay").hide();
                    if (returnData['success']) {
                        console.log(returnData);
                        $('.top-cart-number').html(returnData['totalItems']);

                        $.notify("Product Added to your cart",{
                            position:"bottom right",
                            className: "success"
                        });

                    } else {
						$.notify("Unable to add more than 10 items.",{
                            position:"bottom right",
                            className: "error"
                        });
                    }
                }
            });
        }
	</script>
@endsection
