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
	<link rel="icon" href="{{ asset('img/pmc-logo.png') }}" type="image/x-icon">

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

	{{-- Department navigation styling --}}
    <style>
		:root {
			--pmc-header-ink: #181818;
			--pmc-header-green: #14a800;
			--pmc-header-green-dark: #a27b2c;
			--pmc-header-muted: #5e6d62;
			--pmc-header-line: #e4ebe5;
			--pmc-header-surface: #ffffff;
		}
        #header,
        #header.full-header,
        #header #header-wrap,
        #header.sticky-header,
        #header.sticky-header #header-wrap {
			background: var(--pmc-header-surface) !important;
			border-bottom: 1px solid var(--pmc-header-line) !important;
			border-right: 0 !important;
			box-shadow: 0 2px 10px rgba(19, 43, 25, .06);
		}
		#header::before {
			background: var(--pmc-header-green);
			content: '';
			height: 2px;
			left: 0;
			opacity: 1;
			position: absolute;
			right: 0;
			top: 0;
			z-index: 2;
        }
		#header #header-wrap {
			transition: box-shadow .25s ease;
			border-right: 0 !important;
		}
		#header .header-wrap-clone {
			height: 64px !important;
		}
		#header .header-row {
			align-items: center;
			border-right: 0 !important;
			height: 64px;
			min-height: 64px;
			font-family: "DM Sans", sans-serif;
		}
        #header #logo {
			align-items: center;
			border-right: 0 !important;
			display: flex;
			margin-right: 12px !important;
			padding: 0;
		}
		#header #logo .standard-logo { display: flex !important; }
		#header #logo .retina-logo { display: none !important; }
		#header #logo a { align-items: center; display: flex; }
		#header #logo img {
			height: 42px;
			object-fit: contain;
			display: block;
			width: 60px;
		}
        #header .dept-brand {
			line-height: 1.2;
			margin-left: 4px;
			margin-right: 18px;
			padding-left: 0;
        }
        #header .dept-brand .dept-brand-eyebrow {
			color: var(--pmc-header-muted);
			font-size: 8px;
			font-weight: 700;
			letter-spacing: 1px;
			text-transform: uppercase;
        }
        #header .dept-brand .dept-brand-name {
			color: var(--pmc-header-ink);
			font-size: 13px;
			font-weight: 700;
			letter-spacing: 0;
        }
        #header .primary-menu {
			align-items: center;
			border-right: 0 !important;
			display: flex;
			font-size: 13px;
			margin-left: 14px;
		}
		#header .primary-menu .menu-container {
			align-items: center;
			border-right: 0 !important;
			display: flex;
			gap: 2px;
			height: 64px;
		}
		#header .primary-menu .menu-item { align-items: center; display: flex; height: 100%; }
        #header .primary-menu .menu-container > .menu-item > .menu-link {
			background: transparent !important;
			border: 1px solid transparent;
			border-radius: 0;
			color: var(--pmc-header-green-dark) !important;
			font-size: 13px;
			font-weight: 500;
			line-height: 1.2 !important;
			margin: 0;
			padding: 8px 13px 7px;
            position: relative;
			transition: color .2s ease;
        }
        #header .primary-menu .menu-container > .menu-item:hover > .menu-link,
        #header .primary-menu .menu-container > .menu-item.current > .menu-link {
			background: transparent !important;
			border-color: transparent;
			color: var(--pmc-header-green-dark) !important;
        }
		#header .primary-menu .menu-container > .menu-item > .menu-link::after {
			background: var(--pmc-header-green-dark);
			bottom: 1px;
			content: '';
			height: 2px;
			left: 13px;
			opacity: 0;
			position: absolute;
			right: 13px;
			transform: scaleX(.55);
			transition: opacity .2s ease, transform .2s ease;
		}
		#header .primary-menu .menu-container > .menu-item:hover > .menu-link::after,
		#header .primary-menu .menu-container > .menu-item.current > .menu-link::after {
			opacity: 1;
			transform: scaleX(1);
		}
		#header .header-misc {
			align-items: center;
			border-left: 0;
			display: flex;
			gap: 8px;
			margin-left: auto;
			padding-left: 12px;
		}
        #header .header-misc .header-misc-icon > a.button-circle,
        #header .app-notif-toggle {
			background: #fff !important;
			border: 1px solid #d7e1d8 !important;
			color: #405044 !important;
			font-size: 13px;
			box-shadow: none !important;
            text-shadow: none !important;
			transition: background-color .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
        }
		#header #top-account .button-circle {
			background: #42615b !important;
			border: 1px solid #42615b !important;
			border-radius: 9px !important;
			color: #fff !important;
			height: 34px !important;
			min-width: 84px;
			padding: 0 16px !important;
			width: auto !important;
		}
		#header #top-account .button-circle:hover {
			background: #222e2b !important;
			border-color: #222e2b !important;
			color: #fff !important;
			transform: translateY(-1px);
		}
		#header #top-account .pmc-header-signin,
		#header #top-account .pmc-header-account {
			background: #9a7628 !important;
			border-color: #9a7628 !important;
			color: #fff !important;
			gap: 7px;
		}
		#header #top-account .pmc-header-signin:hover,
		#header #top-account .pmc-header-signin:focus,
		#header #top-account .pmc-header-account:hover,
		#header #top-account .pmc-header-account:focus {
			background: #755719 !important;
			border-color: #755719 !important;
			box-shadow: 0 0 0 3px rgba(154, 118, 40, .22) !important;
			color: #fff !important;
		}
		#header #top-account .pmc-header-signin:hover i,
		#header #top-account .pmc-header-signin:focus i,
		#header #top-account .pmc-header-account:hover i,
		#header #top-account .pmc-header-account:focus i {
			animation: pmc-signin-icon-enter .45s ease-out both;
		}
		@keyframes pmc-signin-icon-enter {
			0% { opacity: 0; transform: translateX(-12px); }
			65% { opacity: 1; transform: translateX(2px); }
			100% { opacity: 1; transform: translateX(0); }
		}
		@media (prefers-reduced-motion: reduce) {
			#header #top-account .pmc-header-signin:hover i,
			#header #top-account .pmc-header-signin:focus i,
			#header #top-account .pmc-header-account:hover i,
			#header #top-account .pmc-header-account:focus i { animation: none; }
		}
		#header #top-account .top-account-text {
			font-size: 13px;
			font-weight: 600;
			/* line-height: 1; */
			white-space: nowrap;
		}
        #header .header-misc .header-misc-icon > a.button-circle i,
		#header .app-notif-toggle svg { color: var(--pmc-header-green-dark) !important; }
        #header .header-misc .header-misc-icon > a.button-circle:hover,
        #header .app-notif-toggle:hover {
			background: #f0f8ef !important;
			border-color: #b9dfb4 !important;
			color: var(--pmc-header-green-dark) !important;
			transform: translateY(-1px);
        }
		#header .header-misc .header-misc-icon > a.button-circle:hover i,
		#header .app-notif-toggle:hover svg {
			color: var(--pmc-header-green-dark) !important;
			fill: var(--pmc-header-green-dark) !important;
			stroke: var(--pmc-header-green-dark) !important;
		}
		#header #top-account .pmc-header-signin i,
		#header #top-account .pmc-header-signin:hover i,
		#header #top-account .pmc-header-account i,
		#header #top-account .pmc-header-account:hover i {
			color: #fff !important;
			font-size: 14px;
			line-height: 1;
		}
		.popover .pmc-account-menu {
			min-width: 218px;
			padding: 3px;
		}
		.popover .pmc-account-menu .dropdown-item {
			align-items: center;
			border-radius: 8px;
			color: #35423b !important;
			display: flex;
			font-size: 12px;
			gap: 10px;
			letter-spacing: .2px;
			margin: 2px 0;
			padding: 11px 12px !important;
			transition: background-color .2s ease, color .2s ease, transform .2s ease;
		}
		.popover .pmc-account-menu .dropdown-item + .dropdown-item {
			border-top: 1px solid #edf0eb;
			border-radius: 0;
			margin-top: 0;
			padding-top: 12px !important;
		}
		.popover .pmc-account-menu .dropdown-item:hover,
		.popover .pmc-account-menu .dropdown-item:focus {
			background: #f7f1df !important;
			color: #755719 !important;
			transform: translateX(2px);
		}
		.popover .pmc-account-menu .dropdown-item i {
			color: #9a7628;
			font-size: 16px;
			line-height: 1;
			margin: 0 !important;
			text-align: center;
			width: 18px;
		}
		#header #primary-menu-trigger,
		#header .svg-trigger path { color: var(--pmc-header-green-dark); stroke: var(--pmc-header-green-dark); }
		#content.pmc-orders-content,
		#content.pmc-no-top-content { padding-top: 0 !important; }
        @media (max-width: 991.98px) {
			#header .header-row { height: 60px; min-height: 60px; }
			#header .header-wrap-clone { height: 60px !important; }
			#header #logo { margin-right: 0 !important; }
			#header .dept-brand { display: none !important; }
			#header .header-misc { border-left: 0; margin-left: auto; padding-left: 0; }
			#header .primary-menu { display: block; margin-left: 0; }
			#header .primary-menu .menu-container {
				background: #fff;
				border-top: 1px solid var(--pmc-header-line);
				box-shadow: 0 14px 24px rgba(19, 43, 25, .1);
				display: block;
				height: auto;
				padding: 8px 16px 16px;
			}
			#header .primary-menu .menu-container > .menu-item {
				border-top: 1px solid #edf2ed;
				display: block;
				height: auto;
			}
			#header .primary-menu .menu-container > .menu-item > .menu-link {
				border-radius: 10px;
				margin: 4px 0;
				padding: 12px 14px;
			}
        }
		@media (max-width: 575.98px) {
			#header #logo img { height: 38px; width: 54px; }
			#header .header-misc { gap: 4px; }
		}
    </style>

    @routes
</head>

<body class="stretched">

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
									<a href="#"><img src="{{ asset('images/no-image.png') }}" alt="Cart Image 1" /></a>
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
    @php($isCustomerLoginPage = request()->routeIs('customer-front.login'))
    <div id="wrapper" class="clearfix">
    <!--<div id="curtain" onclick="closeOffsideNav()"></div>-->

    @if (!$isCustomerLoginPage)
        <!-- Header
        ============================================= -->
        {{-- @include('catalogue.layouts.header') --}}
        <header id="header" class="full-header" data-sticky-shrink="false" data-mobile-sticky="true">
			<div id="header-wrap">
				<div class="container">

					<div class="header-row">

						<!-- Logo
						============================================= -->
						<div id="logo">
							<a href="{{ route('home') }}" class="standard-logo"><img src="{{ asset('images/logo.svg') }}" alt="Canvas Logo"></a>
							<a href="{{ route('home') }}" class="retina-logo"><img src="{{ asset('images/logo.svg') }}" alt="Canvas Logo"></a>
						</div><!-- #logo end -->

						<!-- System title (moved here from the old orange banner) -->
						<div class="dept-brand d-none d-lg-flex flex-column justify-content-center">
							<span class="dept-brand-eyebrow">Materials Control Department</span>
							<span class="dept-brand-name">IMF-MRS-PA (IMP) System</span>
						</div>

						<div id="primary-menu-trigger">
							<svg class="svg-trigger" viewBox="0 0 100 100"><path d="m 30,33 h 40 c 3.722839,0 7.5,3.126468 7.5,8.578427 0,5.451959 -2.727029,8.421573 -7.5,8.421573 h -20"></path><path d="m 30,50 h 40"></path><path d="m 70,67 h -40 c 0,0 -7.5,-0.802118 -7.5,-8.365747 0,-7.563629 7.5,-8.634253 7.5,-8.634253 h 20"></path></svg>
						</div>

						<!-- Primary Navigation
						============================================= -->
						<nav class="primary-menu">
							<ul class="menu-container">
								<li class="menu-item {{ request()->path() == '/' ? 'current' : '' }}">
									<a class="menu-link" href="{{ route('home') }}">
										<div>Home</div>
									</a>
								</li>

								<li class="menu-item {{ request()->routeIs('catalogue.home') ? 'current' : '' }}">
									<a class="menu-link" href="{{ route('catalogue.home') }}">
										<div>CATALOGUE</div>
									</a>
								</li>

								@if (Auth::check())
									<li class="menu-item {{ request()->path() == 'my-orders' ? 'current' : '' }}">
										<a class="menu-link" href="{{ route('profile.sales') }}">
											<div>MRS</div>
										</a>
									</li>
									<li class="menu-item {{ request()->path() == 'inventory/new-stock' ? 'current' : '' }}">
										<a class="menu-link" href="{{ route('new-stock.index') }}">
											<div>IMF</div>
										</a>
									</li>
								@endif
							</ul>

						</nav><!-- #primary-menu end -->

						<div class="header-misc">
							<!-- Notification Bell
							============================================= -->
							@if (Auth::check())
								<div id="top-notif" class="header-misc-icon">
									@include('notifications._bell', ['variant' => 'theme'])
								</div><!-- #top-notif end -->
							@endif

							<!-- Top Account
							============================================= -->
							<div id="top-account" class="header-misc-icon">
								@if (Auth::check())
					<a href="javascript:;" class="button button-circle border-0 button-large ht-40-f wd-40-f d-flex align-items-center justify-content-center p-0 pmc-header-account" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-html="true" data-bs-content='<div class="pmc-account-menu">
										<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('customer.manage-account') }}"><i class="icon-user-cog" aria-hidden="true"></i><span>Manage Account</span></a>
										<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ asset('files/IMP System User Manual for Dept. User (February 19, 2026).pdf') }}" target="_blank" rel="noopener"><i class="icon-line-book-open" aria-hidden="true"></i><span>User Manual</span></a>
                                    	<!--<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('profile.sales') }}"><i class="icon-line-shopping-bag fs-5 me-3"></i> MRS Request</a>-->
										<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('account.logout') }}"><i class="icon-line-log-out" aria-hidden="true"></i><span>Sign Out</span></a>
					</div>'><i class="icon-line-head" aria-hidden="true"></i><span class="top-account-text">Account</span></a>
								@else
					<a href="{{ route('customer-front.login') }}" class="button button-circle border-0 button-large ht-40-f wd-40-f d-flex align-items-center justify-content-center p-0 pmc-header-signin" aria-label="Sign In"><i class="icon-line-log-in" aria-hidden="true"></i><span class="top-account-text">Sign In</span></a>
								@endif
							</div><!-- #top-account end -->

							<!-- Top Cart
							=============================================
							<div id="top-cart" class="header-misc-icon">
								<a href="{{ route('cart.front.show') }}" class="side-panel-trigger1 button button-large button-circle ht-40-f wd-40-f d-flex align-items-center justify-content-center p-0 h-bg-black notextshadow"><i class="icon-line-bag fs-18 fs-lg-20"></i><span class="top-cart-number op-1 bg-dark">{{ Setting::EcommerceCartTotalItems() }}</span></a>
							</div><!-- #top-cart end -->
						</div>

					</div>

				</div>

			</div>

		</header>

        <!-- Banner
        ============================================= -->
		@include('theme.layouts.banner')
    @endif

        <!-- Content
        ============================================= -->
        <section id="content" class="{{ !$isCustomerLoginPage && request()->path() != '/' ? 'py-5 py-lg-6 position-relative' : '' }} {{ request()->routeIs('profile.sales') ? 'pmc-orders-content' : '' }} {{ request()->routeIs('new-stock.index') || request()->routeIs('new-stock.show') || request()->routeIs('new-stock.edit') ? 'pmc-no-top-content' : '' }} {{ request()->routeIs('new-stock.show') ? 'pmc-imf-detail-content' : '' }} {{ request()->routeIs('new-stock.edit') ? 'pmc-imf-edit-content' : '' }}">
            @yield('content')
        </section>

    @if (!$isCustomerLoginPage)
        <div class="alert text-center cookiealert show" role="alert" id="popupPrivacy" style="display: none;">
            {!! \Setting::info()->data_privacy_popup_content !!} <a href="{{ route('privacy-policy') }}" target="_blank">Learn more</a>
            <button type="button" id="cookieAcceptBarConfirm" class="btn btn-primary btn-sm acceptcookies px-3 mt-3 mt-lg-0" aria-label="Close">
                I agree
            </button>
        </div>

        <!-- Footer
        ============================================= -->
        @if (request()->path() === '/')
            @include('theme.layouts.footers.pmc-website')
        @endif
        {{--@include('theme.layouts.footer')--}}
    @endif

    </div>

    <!-- Go To Top
    ============================================= -->
    <div id="gotoTop" class="icon-angle-up rounded-circle bg-color3"></div>

    <!-- External JavaScripts
    ============================================= -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('theme/js/jquery.js') }}"></script>
    <script src="{{ asset('theme/js/plugins.min.js') }}"></script>
    <script>
        // The bundled theme plugins use the legacy jQuery Easing `def` API.
        // Keep it mapped to a real easing function for current jQuery versions.
        (function ($) {
            if (!$ || !$.easing) return;
            if (typeof $.easing.easeOutQuad !== 'function') {
                $.easing.easeOutQuad = function (x, t, b, c, d) {
                    return -c * (t /= d) * (t - 2) + b;
                };
            }
            $.easing.def = 'easeOutQuad';
        })(window.jQuery);
    </script>

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

		var route_name = (window.location.pathname).split("/");
		var route_final = route_name[route_name.length - 1];
      	console.log(route_final);
		if(route_final === "my-orders" || route_final === "new-stock"){
			WFS();
        	//setInterval(WFS, 10000);
		}

		// Queue a failed approval poll for the page's WFS indicator
		// (_wfs-status-js), which shows it to the requestor.
		function wfsPollFailed(jqXHR) {
			var msg = (jqXHR.responseJSON && jqXHR.responseJSON.message) || 'The request to WFS failed.';
			(window.wfsPollErrors = window.wfsPollErrors || []).push(msg);
			if (window.wfsShowPollErrors) {
				window.wfsShowPollErrors();
			}
		}

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
					wfsPollFailed(jqXHR);
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
					wfsPollFailed(jqXHR);
				}
			});
		}
    </script>

    @yield('pagejs')

</body>
</html>
