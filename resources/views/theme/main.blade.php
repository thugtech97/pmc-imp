<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>

    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="SemiColonWeb" />

    <!-- Stylesheets
    ============================================= -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/style.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/swiper.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/beauty-kit.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/font-icons.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/fontawesome.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/animate.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/magnific-popup.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/slick.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/slick-theme.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/custom.css') }}" type="text/css" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">

    <style>
        @php
            $jsStyle = str_replace(array("'", "&#039;"), "", old('styles', $page->styles) );
            echo $jsStyle;
        @endphp
    </style>

    <!-- Document Title
    ============================================= -->
    @if (isset($page->name) && $page->name == 'Home')
        <title>{{ Setting::info()->company_name }}</title>
    @else
        <title>{{ (empty($page->meta_title) ? $page->name:$page->meta_title) }} | {{ Setting::info()->company_name }}</title>
    @endif

    @if(!empty($page->meta_description))
        <meta name="description" content="{{ $page->meta_description }}">
    @endif

    @if(!empty($page->meta_keyword))
        <meta name="keywords" content="{{ $page->meta_keyword }}">
    @endif

    @yield('pagecss')

    @routes
</head>

<body class="stretched side-header">

    <!-- Cart Panel Background
    ============================================= -->
    <div class="body-overlay"></div>

    <!-- Cart Side Panel
	============================================= -->
	<div id="side-panel" class="bg-white">

		<!-- Cart Side Panel Close Icon
		============================================= -->
		<div id="side-panel-trigger-close" class="side-panel-trigger position-relative h-40px z-2"><a href="#" class="d-block d-flex justify-content-end mt-1"><i class="icon-line-cross px-3 py-2"></i></a></div>

		<div class="side-panel-wrap">

			<div class="top-cart d-flex flex-column h-100">
				<div class="top-cart-title">
					<h4 class="d-flex align-items-center fs-18-f ls1 fw-bold text-uppercase">Shopping Cart <span class="background-custom1 w-auto text-center text-white round-corner px-2 ms-2">3</span></h4>
				</div>

				<div id="top-cart-items">
					<!-- Cart Items
					============================================= -->
					<div class="top-cart-items">
						@foreach(Setting::cart_items() as $item)
							<div class="top-cart-item">
								<div class="top-cart-item-image border-0">
									<a href="#"><img src="https://via.placeholder.com/44x44" alt="Cart Image 1" /></a>
								</div>
								<div class="top-cart-item-desc">
									<div class="top-cart-item-desc-title">
										<a href="#" class="fw-medium">{{ $item->product->name }}</a>
										<span class="top-cart-item-price d-block"><del></del> </span>
										<div class="d-flex mt-2 align-items-center">
											<div class="quantity quantity-small my-2 me-0 w-100 justify-content-start">
												<input type="button" value="-" class="minus" disabled>
												<input type="number" step="1" min="1" name="quantity" value="{{ $item->qty }}" id="sidebarQty{{$item->ID}}" title="Qty" class="qty fs-12px wd-40-f border-0" disabled>
												<input type="button" value="+" class="plus" disabled>
											</div>
											<a href="#" class="fw-normal color fs-18px ms-3"><i class="icon-trash-alt1"></i></a>
										</div>
									</div>
									<div class="top-cart-item-quantity ps-3 wd-85-f text-end"></div>
								</div>
							</div>
						@endforeach
					</div>
				</div>

				<div class="px-3 mt-auto text-black-50 text-smaller background-custom2"></div>

				<!-- Cart Price and Button
				============================================= -->
				<div class="top-cart-action flex-column align-items-stretch position-sticky bottom-0 bg-white">
					<div class="text-black-50 text-smaller mb-3">
						<!--<span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="var(--themecolor)" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M54.46089,201.53911c-9.204-9.204-3.09935-28.52745-7.78412-39.85C41.82037,149.95168,24,140.50492,24,127.99963,24,115.4945,41.82047,106.048,46.67683,94.31079c4.68477-11.32253-1.41993-30.6459,7.78406-39.8499s28.52746-3.09935,39.85-7.78412C106.04832,41.82037,115.49508,24,128.00037,24c12.50513,0,21.95163,17.82047,33.68884,22.67683,11.32253,4.68477,30.6459-1.41993,39.8499,7.78406s3.09935,28.52746,7.78412,39.85C214.17963,106.04832,232,115.49508,232,128.00037c0,12.50513-17.82047,21.95163-22.67683,33.68884-4.68477,11.32253,1.41993,30.6459-7.78406,39.8499s-28.52745,3.09935-39.85,7.78412C149.95168,214.17963,140.50492,232,127.99963,232c-12.50513,0-21.95163-17.82047-33.68884-22.67683C82.98826,204.6384,63.66489,210.7431,54.46089,201.53911Z" opacity="0.2"></path><path d="M54.46089,201.53911c-9.204-9.204-3.09935-28.52745-7.78412-39.85C41.82037,149.95168,24,140.50492,24,127.99963,24,115.4945,41.82047,106.048,46.67683,94.31079c4.68477-11.32253-1.41993-30.6459,7.78406-39.8499s28.52746-3.09935,39.85-7.78412C106.04832,41.82037,115.49508,24,128.00037,24c12.50513,0,21.95163,17.82047,33.68884,22.67683,11.32253,4.68477,30.6459-1.41993,39.8499,7.78406s3.09935,28.52746,7.78412,39.85C214.17963,106.04832,232,115.49508,232,128.00037c0,12.50513-17.82047,21.95163-22.67683,33.68884-4.68477,11.32253,1.41993,30.6459-7.78406,39.8499s-28.52745,3.09935-39.85,7.78412C149.95168,214.17963,140.50492,232,127.99963,232c-12.50513,0-21.95163-17.82047-33.68884-22.67683C82.98826,204.6384,63.66489,210.7431,54.46089,201.53911Z" fill="none" stroke="var(--themecolor)" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><polyline points="172 104 113.333 160 84 132" fill="none" stroke="var(--themecolor)" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline></svg> You save ₱ 10.00 on this order.</span>-->
					</div>
					<div class="d-flex justify-content-between align-items-center mb-2">
						<small class="fs-16-f ls1 fw-bold text-uppercase text-dark">Total</small>
						<h4 class="fw-medium font-body mb-0">{{ Setting::EcommerceCartTotalItems() }}</h4>
					</div>
					<a href="{{ route('cart.front.show') }}" class="button button-circle button-xlarge fw-bold mt-2 fs-14-f text-dark h-text-light btn-block text-center notextshadow">Checkout</a>
					<a href="{{ route('cart.front.show') }}" class="button button-dark button-border button-circle button-xlarge fw-bold mt-2 fs-14-f btn-block text-center notextshadow">View Cart</a>
				</div>
			</div>

		</div>

	</div>

    <!-- Document Wrapper
    ============================================= -->
    <div id="wrapper" class="clearfix">
    <!--<div id="curtain" onclick="closeOffsideNav()"></div>-->

        <!-- Header
        ============================================= -->
        {{-- @include('catalogue.layouts.header') --}}
        <header id="header" data-mobile-sticky="true">
			<div id="header-wrap" class="overflow-hidden">
				<div class="container">

					<div class="header-row justify-content-lg-between">

						<!-- Logo
						============================================= -->
						<div id="logo" class="justify-content-lg-center">
							<a href="{{ route('home') }}" class="standard-logo"><img src="{{ asset('images/logo.svg') }}" alt="Canvas Logo"></a>
							<a href="{{ route('home') }}" class="retina-logo"><img src="{{ asset('images/logo.svg') }}" alt="Canvas Logo"></a>
						</div><!-- #logo end -->

						<div id="primary-menu-trigger">
							<svg class="svg-trigger" viewBox="0 0 100 100"><path d="m 30,33 h 40 c 3.722839,0 7.5,3.126468 7.5,8.578427 0,5.451959 -2.727029,8.421573 -7.5,8.421573 h -20"></path><path d="m 30,50 h 40"></path><path d="m 70,67 h -40 c 0,0 -7.5,-0.802118 -7.5,-8.365747 0,-7.563629 7.5,-8.634253 7.5,-8.634253 h 20"></path></svg>
						</div>

						<div class="header-misc flex-lg-column mb-lg-4 ms-0">
							<!-- Top Account
							============================================= -->
							<div id="top-account" class="header-misc-icon m-0">
								@if (Auth::check())
									<a href="javascript:;" class="button button-circle border-0 button-large ht-40-f wd-40-f ht-lg-50-f wd-lg-50-f d-flex align-items-center justify-content-center p-0" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-html="true" data-bs-content='
										<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('customer.manage-account') }}"><i class="icon-line-head fs-5 me-3"></i> Manage Account</a>
                                    	<!--<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('profile.sales') }}"><i class="icon-line-shopping-bag fs-5 me-3"></i> MRS Request</a>-->
										<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('account.logout') }}"><i class="icon-line-log-out fs-5 me-3"></i> Sign Out</a>
									'><i class="icon-line-head fs-18 fs-lg-20"></i></a>
								@else
									<a href="javascript:;" class="button button-circle border-0 button-large ht-40-f wd-40-f ht-lg-50-f wd-lg-50-f d-flex align-items-center justify-content-center p-0" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-html="true" data-bs-content='<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('customer-front.customer_login') }}"><i class="icon-line-log-in fw-semibold me-2"></i> Sign In</a>'><i class="icon-line-head fs-18 fs-lg-20"></i></a>
								@endif
							</div><!-- #top-account end -->

							<!-- Top Cart
							=============================================
							<div id="top-cart" class="header-misc-icon m-0">
								<a href="{{ route('cart.front.show') }}" class="side-panel-trigger1 button button-large button-circle ht-40-f wd-40-f ht-lg-50-f wd-lg-50-f d-flex align-items-center justify-content-center p-0 h-bg-black notextshadow"><i class="icon-line-bag fs-18 fs-lg-20"></i><span class="top-cart-number op-1 bg-dark">{{ Setting::EcommerceCartTotalItems() }}</span></a>
							</div><!-- #top-cart end -->
						</div>

						<!-- Primary Navigation
						============================================= -->
						<nav class="primary-menu">
							<ul class="menu-container">
								<li class="menu-item {{ request()->path() == '/' ? 'current' : '' }}">
									<a class="menu-link" href="{{ route('home') }}">
										<i class="icon-line-home me-0 fs-24-f my-1 d-none d-lg-block"></i>
										<div>Home</div>
									</a>
								</li>
								
								@if (Auth::check())
									<li class="menu-item {{ request()->path() == 'my-orders' ? 'current' : '' }}">
										<a class="menu-link" href="{{ route('profile.sales') }}">
											<i class="icon-line-shopping-bag me-0 fs-24-f my-1 d-none d-lg-block"></i>
											<div>MRS</div>
										</a>
									</li>
									<li class="menu-item {{ request()->path() == 'inventory/new-stock' ? 'current' : '' }}">
										<a class="menu-link" href="{{ route('new-stock.index') }}">
											<i class="icon-line-square me-0 fs-24-f my-1 d-none d-lg-block"></i>
											<div>IMF</div>
										</a>
									</li>
								@endif
							</ul>

						</nav><!-- #primary-menu end -->

					</div>

				</div>

			</div>

		</header>

        <!-- Banner
        ============================================= -->
		@include('theme.layouts.banner')

        <!-- Content
        ============================================= -->
        <section id="content" class="{{ request()->path() != '/' ? 'py-5 py-lg-6 position-relative' : '' }}">
            @yield('content')
        </section>

        <div class="alert text-center cookiealert show" role="alert" id="popupPrivacy" style="display: none;">
            {!! \Setting::info()->data_privacy_popup_content !!} <a href="{{ route('privacy-policy') }}" target="_blank">Learn more</a>
            <button type="button" id="cookieAcceptBarConfirm" class="btn btn-primary btn-sm acceptcookies px-3 mt-3 mt-lg-0" aria-label="Close">
                I agree
            </button>
        </div>

        <!-- Footer
        ============================================= -->
		@include('theme.layouts.footers.pmc-website')
        {{--@include('theme.layouts.footer')--}}

    </div>

    <!-- Go To Top
    ============================================= -->
    <div id="gotoTop" class="icon-angle-up rounded-circle bg-color3"></div>

    <!-- External JavaScripts
    ============================================= -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('theme/js/jquery.js') }}"></script>
    <script src="{{ asset('theme/js/plugins.min.js') }}"></script>

    <!-- Footer Scripts
    ============================================= -->
    <!--<script type="text/javascript">
        var bannerFxIn = "fadeIn";
        var bannerFxOut = "fadeOut";
        var bannerCaptionFxIn = "fadeInUp";
        var autoPlayTimeout = "4000";
        var bannerID = "banner";
    </script>-->
    <script type="text/javascript">
		var bannerFxIn = "fadeIn";
		var bannerFxOut = "slideOutLeft";
		var bannerCaptionFxIn = "fadeInUp";
		var autoPlayTimeout = 4000;
		var bannerID = "banner";
	</script>

    <script src="{{ asset('theme/js/components/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('theme/js/components/bs-filestyle.js') }}"></script>
    <script src="{{ asset('theme/js/functions.js') }}"></script>
    <script src="{{ asset('js/notify.js') }}"></script>
    <?php /*<script src="lib/smooth-scrollbar/smooth-scrollbar.js"></script>*/ ?>

	<script>
		var Scrollbar = window.Scrollbar;

		//Scrollbar.init(document.querySelector('#top-cart-items'));
	</script>

    <script type="text/javascript">
        $(document).ready(function() {
            if(localStorage.getItem('popState') != 'shown'){
                $('#popupPrivacy').delay(1000).fadeIn();
            }
        });

        $('#cookieAcceptBarConfirm').click(function() // You are clicking the close button
        {
            $('#popupPrivacy').fadeOut(); // Now the pop up is hidden.
            localStorage.setItem('popState','shown');
        });

		WFS();
        //setInterval(WFS, 10000);

		function WFS(){
			$.ajax({
				url: '{!! route('new-stock.updateRequestApproval') !!}',
				type: 'GET',
				async: false,
				success: function(response) {
					console.log('Updated IMF Request Approval..');
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error('updateRequestApproval AJAX Request Error:', textStatus, errorThrown);
				}
			});

			$.ajax({
				url: '{!! route('mrs.updateRequestApproval') !!}',
				type: 'GET',
				async: false,
				success: function(response) {
					console.log('Updated MRS Request Approval..');
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error('updateRequestApproval AJAX Request Error:', textStatus, errorThrown);
				}
			});
		}
    </script>

    @yield('pagejs')

</body>
</html>
