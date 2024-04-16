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
		.prod-name {
		   overflow: hidden;
		   text-overflow: ellipsis;
		   display: -webkit-box;
		   -webkit-line-clamp: 3; /* number of lines to show */
		           line-clamp: 3; 
		   -webkit-box-orient: vertical;
		}
        a.disabled-link {
            pointer-events: none;
            cursor: default;
            color: #999;
			text-decoration: none;
        }
    </style>
@endsection
 
@section('content')

		<section class="py-4 py-lg-5 bg-color position-relative">
			<div class="container-fluid">
				<div class="mb-0">
					<div class="before-heading font-secondary text-dark fw-semibold fs-12-f">Materials Control Department</div>
					<h1 class="text-dark mt-2 fs-30 fs-lg-40 mb-0 nols lh-sm">E-commerce and Warehousing System</h1>
				</div>
			</div>
		</section>
		<section id="page-title" class="page-title-nobg">

			<div style="margin: 0px 122px;">
				@if(!$isAuthenticated)
					<input type="hidden" id="isAuthenticated" value="false">
					<div class="alert alert-danger" role="alert">
						Authentication is required. Kindly Sign in. Access is limited to viewing only.
					</div>
				@endif
			</div>

			<div class="container-fluid clearfix">
				<h1>Product Catalogue</h1>
				<span></span>
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="{{route('catalogue.home')}}">Catalogue</a></li>
					<li class="breadcrumb-item active" aria-current="page">Home</li>
				</ol>
			</div>

		</section>

		<!-- Content
		============================================= -->
		<section id="content" style="background-color: #F8F8F8 !important;">
			<div class="content-wrap">
				
				<div class="container-fluid clearfix">
					<div class="row">
						<form action="{{ route('catalogue.search') }}" method="POST" role="search">
							@csrf

							<h4>Search Products</h4>
							
							<div class="input-group mb-3">
								<input type="text" name="query" class="form-control" placeholder="Search by name / description / item code" aria-label="Recipient's username" aria-describedby="button-addon2">
								<button class="btn btn-outline-primary" type="submit" id="button-addon2"><i class="icon icon-search"></i></button>
							</div>
						</form>
					</div>

					<div id="input-fields">
						<div class="fancy-title title-center title-border topmargin">
							<h3>Previously Ordered Items</h3>
						</div>

						<div id="oc-products" class="owl-carousel products-carousel carousel-widget" data-pagi="false" data-items-xs="1" data-items-sm="2" data-items-md="4" data-items-lg="6">

							@forelse($previously_ordered as $po)
								@php
									$data = [];
									$data['code'] = $po->id;
									$data['inventory'] = 100;
									$data['name'] = $po->name;
									$data['image'] = $po->PhotoSmall;
									$data['slug'] = $po->slug;
									$data['id'] = $po->id;
								@endphp
								<x-product-thumb :data="$data"/>
								
							@empty

							@endforelse
						</div>
						

						<div class="fancy-title title-center title-border topmargin">
							<h3>Newly Added Items</h3>
						</div>

						<div id="oc-products" class="owl-carousel products-carousel carousel-widget" data-pagi="false" data-items-xs="1" data-items-sm="2" data-items-md="4" data-items-lg="6">

							@forelse($newly_added as $po)
								@php
									$data = [];
									$data['code'] = $po->id;
									$data['inventory'] = 100;
									$data['name'] = $po->name;
									$data['image'] = $po->PhotoSmall;
									$data['slug'] = $po->slug;
									$data['id'] = $po->id;
								@endphp
								<x-product-thumb :data="$data"/>
								
							@empty

							@endforelse

						</div>
						
						<div class="fancy-title title-center title-border topmargin">
							<h3>Browse by Category</h3>
						</div>

						<div id="portfolio" class="portfolio row grid-container gutter-20 has-init-isotope" data-layout="fitRows" style="position: relative; height: 1037.25px;">

							@forelse($categories as $c)
								<x-category-thumb :c="$c"/>
							@empty
							@endforelse

						</div>
					</div>
					<!-- #portfolio end -->
				</div>
			</div>
		</section>
		


@endsection

@section('pagejs')

	<!-- Footer Scripts
	============================================= -->
	<script src="{{ asset('theme/catalogue/js/functions.js') }}"></script>

	<!-- SLIDER REVOLUTION 5.x SCRIPTS  -->

	<script src="{{ asset('theme/catalogue/js/jquery.themepunch.tools.min.js') }}"></script>
	<script src="{{ asset('theme/catalogue/js/jquery.themepunch.revolution.min.js') }}"></script>

	<script src="{{ asset('theme/catalogue/js/extensions/revolution.extension.video.min.js') }}"></script>
	<script src="{{ asset('theme/catalogue/js/extensions/revolution.extension.slideanims.min.js') }}"></script>
	<script src="{{ asset('theme/catalogue/js/extensions/revolution.extension.actions.min.js') }}"></script>
	<script src="{{ asset('theme/catalogue/js/extensions/revolution.extension.layeranimation.min.js') }}"></script>
	<script src="{{ asset('theme/catalogue/js/extensions/revolution.extension.navigation.min.js') }}"></script>
	<script src="{{ asset('theme/catalogue/js/extensions/revolution.extension.parallax.min.js') }}"></script>

	<script>
		$(document).ready(function() {

			var isAuthenticated = $('#isAuthenticated').val();
			
			if (isAuthenticated === 'false') {
				$('.grid-inner a, #top-cart a, .product-image a').click(function(e) {
					e.preventDefault();
				}).addClass('disabled-link');

				// Disable form inputs within the element with ID 'content'
				$("#input-fields :input").prop("disabled", true);
			}
		});
	</script>

	<script>
		var tpj=jQuery;
		var revapi6;
		var $ = jQuery.noConflict();

		tpj(document).ready(function() {
			if(tpj("#rev_slider_6_1").revolution == undefined){
				revslider_showDoubleJqueryError("#rev_slider_6_1");
			}else{
				revapi6 = tpj("#rev_slider_6_1").show().revolution({
					sliderType:"hero",
					jsFileLocation:"catalogue/js/",
					sliderLayout:"fullscreen",
					dottedOverlay:"none",
					delay:9000,
					navigation: {
					},
					responsiveLevels:[1240,1024,778,480],
					visibilityLevels:[1240,1024,778,480],
					gridwidth:[1240,1024,778,480],
					gridheight:[868,768,960,720],
					lazyType:"none",
					parallax: {
						type:"scroll",
						origo:"slidercenter",
						speed:400,
						levels:[10,15,20,25,30,35,40,-10,-15,-20,-25,-30,-35,-40,-45,55],
						type:"scroll",
					},
					shadow:0,
					spinner:"off",
					autoHeight:"off",
					fullScreenAutoWidth:"off",
					fullScreenAlignForce:"off",
					fullScreenOffsetContainer: "",
					fullScreenOffset: "",
					disableProgressBar:"on",
					hideThumbsOnMobile:"off",
					hideSliderAtLimit:0,
					hideCaptionAtLimit:0,
					hideAllCaptionAtLilmit:0,
					debugMode:false,
					fallbacks: {
						simplifyAll:"off",
						disableFocusListener:false,
					}
				});
			}
		});	
	</script>

	<script>
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
                        // console.log(returnData);
                        $('.top-cart-number').html(returnData['totalItems']);

                        $.notify("Product Added to your cart",{
                            position:"bottom right",
                            className: "success"
                        });

                    } else {
                        swal({
                            toast: true,
                            position: 'center',
                            title: "Warning!",
                            text: "We have insufficient inventory for this item.",
                            type: "warning",
                            showCancelButton: true,
                            timerProgressBar: true,
                            closeOnCancel: false

                        });
                    }
                }
            });
        }
	</script>
@endsection
