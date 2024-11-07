<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>

    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="SemiColonWeb" />

    <!-- Stylesheets
    ============================================= -->
    <!--<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400i,700|Raleway:300,400,500,600,700,800,900|Roboto:700,500,900,400&display=swap" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('theme/catalogue/css/bootstrap.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('theme/catalogue/style.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('theme/catalogue/css/dark.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('theme/catalogue/css/font-icons.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('theme/catalogue/css/animate.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('theme/catalogue/css/magnific-popup.css') }}" type="text/css" />

    <!-- <link rel="stylesheet" href="{{ asset('theme/css/custom.css') }}" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />-->
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/style.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/swiper.css') }}" type="text/css" />
	
	<!-- Beauty Kit Demo Specific Stylesheet -->
	<link rel="stylesheet" href="{{ asset('css/beauty-kit.css') }}" type="text/css" />
	<!-- / -->

	<link rel="stylesheet" href="{{ asset('css/font-icons.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/fontawesome.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/animate.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/magnific-popup.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/slick.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/slick-theme.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/custom.css') }}" type="text/css" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="icon" href="{{ asset('img/pmc-logo.png') }}" type="image/x-icon">

    @yield('pagecss')
    <!-- Document Title
    ============================================= -->
    <title>MCD - Product Catalogue</title>

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
						<h4 class="fw-medium font-body mb-0 sidebar-cart-number">{{ Setting::EcommerceCartTotalItems() }}</h4>
					</div>
					<a href="{{ route('cart.front.proceed_checkout') }}" class="button button-circle button-xlarge fw-bold mt-2 fs-14-f text-dark h-text-light btn-block text-center notextshadow">Checkout</a>
					<a href="{{ route('cart.front.show') }}" class="button button-dark button-border button-circle button-xlarge fw-bold mt-2 fs-14-f btn-block text-center notextshadow">View Cart</a>
				</div>
			</div>

		</div>

	</div>

    <div id="wrapper" class="clearfix">

        @include('catalogue.layouts.header')


                @yield('content')

        <!-- Footer
        ============================================= -->
        @include('theme.layouts.footers.pmc-website')
        {{--@include('theme.layouts.footer')--}}

    </div>

    <div id="gotoTop" class="icon-angle-up"></div>


    <script src="{{ asset('theme/catalogue/js/jquery.js') }}"></script>
    <script src="{{ asset('theme/catalogue/js/plugins.min.js') }}"></script>
    <script src="{{ asset('js/notify.js') }}"></script>


    @yield('pagejs')

</body>
</html>
