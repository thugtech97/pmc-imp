<style>
	#header.catalogue-header {
		--catalogue-ink: #1d2927;
		--catalogue-muted: #70807a;
		--catalogue-accent: #b86b45;
		--catalogue-accent-dark: #7f4329;
		--catalogue-line: #e5e8e1;
	}

	#header.catalogue-header .menu-item > .menu-link {
		position: relative;
		min-width: 78px;
		padding: 10px 13px 9px;
		border-radius: 11px;
		color: var(--catalogue-muted);
		font-size: .71rem;
		font-weight: 700;
		letter-spacing: .1em;
		line-height: 1.2;
		text-align: center;
		text-decoration: none !important;
		text-transform: uppercase;
		transition: background .2s ease, color .2s ease, transform .2s ease;
	}

	#header.catalogue-header .menu-item > .menu-link i {
		color: inherit;
		font-size: 20px !important;
	}

	#header.catalogue-header .menu-item > .menu-link:hover,
	#header.catalogue-header .menu-item.current > .menu-link {
		/* background: #f3e9e1; */
		color: var(--catalogue-accent-dark);
		transform: translateY(-1px);
	}

	#header.catalogue-header .menu-item.current > .menu-link::after {
		position: absolute;
		right: 16px;
		bottom: 5px;
		left: 16px;
		height: 2px;
		border-radius: 2px;
		/* background: var(--catalogue-accent); */
		content: '';
	}

	#header.catalogue-header .header-misc-icon > a {
		position: relative;
		border: 1px solid var(--catalogue-line) !important;
		background: #fff;
		color: var(--catalogue-ink);
		box-shadow: none;
		transition: border-color .2s ease, background .2s ease, color .2s ease, transform .2s ease;
	}

	#header.catalogue-header .header-misc-icon > a:hover {
		border-color: #ddb39d !important;
		background: #fff7f2;
		color: var(--catalogue-accent-dark);
		transform: translateY(-2px);
	}

	#header.catalogue-header .top-cart-number {
		top: -3px;
		right: -3px;
		min-width: 18px;
		height: 18px;
		padding: 0 4px;
		border: 2px solid #fff;
		border-radius: 10px;
		background: var(--catalogue-accent) !important;
		font-size: 10px;
		font-weight: 700;
		line-height: 14px;
	}

	@media (max-width: 991px) {
		#header.catalogue-header .header-misc-icon > a {
			width: 42px !important;
			height: 42px !important;
		}
	}
</style>

<header id="header" class="catalogue-header" data-mobile-sticky="true">
			<div id="header-wrap" class="overflow-hidden">
				<div class="container">

					<div class="header-row justify-content-lg-between">

						<!-- Logo
						============================================= -->
						<div id="logo" class="justify-content-lg-center">
							<a href="/" class="standard-logo"><img src="{{ asset('images/logo.svg') }}" alt="Canvas Logo"></a>
							<a href="/" class="retina-logo"><img src="{{ asset('images/logo.svg') }}" alt="Canvas Logo"></a>
						</div><!-- #logo end -->

						<div id="primary-menu-trigger">
							<svg class="svg-trigger" viewBox="0 0 100 100"><path d="m 30,33 h 40 c 3.722839,0 7.5,3.126468 7.5,8.578427 0,5.451959 -2.727029,8.421573 -7.5,8.421573 h -20"></path><path d="m 30,50 h 40"></path><path d="m 70,67 h -40 c 0,0 -7.5,-0.802118 -7.5,-8.365747 0,-7.563629 7.5,-8.634253 7.5,-8.634253 h 20"></path></svg>
						</div>

						<div class="header-misc flex-lg-column mb-lg-4 ms-0">
							<!-- Top Account
							============================================= -->
							<div id="top-account" class="header-misc-icon m-0">
								@if (Auth::check())
									<a href="javascript:;" class="button button-circle button-border border-0 button-large ht-40-f wd-40-f ht-lg-50-f wd-lg-50-f d-flex align-items-center justify-content-center p-0" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-html="true" data-bs-content='
										<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('customer.manage-account') }}"><i class="icon-line-head fs-5 me-3"></i> Manage Account</a>
                                    	<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('profile.sales') }}"><i class="icon-line-shopping-bag fs-5 me-3"></i> MRS Request</a>
										<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('account.logout') }}"><i class="icon-line-log-out fs-5 me-3"></i> Sign Out</a>
									'><i class="icon-line-head fs-18 fs-lg-20"></i></a>
								@else
									<a href="javascript:;" class="button button-circle button-border border-0 button-large ht-40-f wd-40-f ht-lg-50-f wd-lg-50-f d-flex align-items-center justify-content-center p-0" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-html="true" data-bs-content='<a class="dropdown-item fs-12-f ls1 fw-semibold py-2" href="{{ route('customer-front.customer_login') }}"><i class="icon-line-log-in fw-semibold me-2"></i> Sign In</a>'><i class="icon-line-head fs-18 fs-lg-20"></i></a>
								@endif
							</div><!-- #top-account end -->

							<!-- Top Cart
							============================================= -->
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
								<li class="menu-item {{ request()->path() == 'catalogue/home' ? 'current' : '' }}">
									<a class="menu-link" href="{{ route('catalogue.home') }}">
										<i class="icon-line-grid me-0 fs-24-f my-1 d-none d-lg-block"></i>
										<div>Catalogue</div>
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