<header id="header" class="{{ request()->is('home') || request()->is('/') ? 'transparent-header page-section dark' : 'full-header' }}" data-sticky-class="not-dark">
    <div id="header-wrap">
        <div class="container">
            <div class="header-row">

                <!-- Logo
                ============================================= -->
                <div id="logo">
                <a href="{{ route('home') }}" class="standard-logo" data-dark-logo="{{ asset('theme/catalogue/images/pmc.png') }}"><img src="{{ asset('theme/catalogue/images/pmc.png') }}" alt="PMC"></a>
                    <!--<a href="{{ route('home') }}" class="standard-logo"><img src="{{ Setting::get_company_logo_storage_path() }}" alt="Canvas Logo"></a>
                    <a href="{{ route('home') }}" class="retina-logo"><img src="{{ Setting::get_company_logo_storage_path() }}" alt="Canvas Logo"></a>-->
                </div><!-- #logo end -->

                <div class="header-misc">
                    <!-- Top Search
                    ============================================= -->
                    <div id="top-search" class="header-misc-icon">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-lg dropdown-toggle p-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-user"></i>
                            </button>
                            <div class="dropdown-menu" style="">
                                @if(Auth::check())
                                    <a class="dropdown-item" href="{{ route('customer.manage-account') }}">{{ auth()->user()->fullname }}</a>

                                    <a class="dropdown-item" href="{{ route('customer.manage-account') }}">Manage Account</a>
                                    <a class="dropdown-item" href="{{ route('profile.sales') }}">MRS Request</a>

                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                                    <form id="logout-form" action="{{ route('account.logout') }}" method="get" style="display: none;">
                                        @csrf
                                    </form>
                                @else
                                    <a class="dropdown-item" href="{{ route('customer-front.login') }}">Sign In</a>
                                    <a class="dropdown-item" href="{{ route('customer-front.sign-up') }}">Sign Up</a>
                                @endif
                            </div>
                        </div>
                    </div><!-- #top-search end -->

                    <!-- Top Cart
                    ============================================= -->
                    <div id="top-cart" class="header-misc-icon d-none d-sm-block">
                        <a href="javascript:;" id="top-cart-trigger"><i class="icon-line-bag"></i><span class="top-cart-number">{{ Setting::EcommerceCartTotalItems() }}</span></a>
                        <div class="top-cart-content">
                            <div class="top-cart-title">
                                <h4>Shopping Cart</h4>
                            </div>
                            <div class="top-cart-items">
                                <!--<div class="top-cart-item">
                                    <div class="top-cart-item-image">
                                        <a href="#"><img src="{{ asset('theme/images/shop/small/1.jpg') }}" alt="Blue Round-Neck Tshirt" /></a>
                                    </div>
                                    <div class="top-cart-item-desc">
                                        <div class="top-cart-item-desc-title">
                                            <a href="#">Blue Round-Neck Tshirt with a Button</a>
                                            <span class="top-cart-item-price d-block">$19.99</span>
                                        </div>
                                        <div class="top-cart-item-quantity">x 2</div>
                                    </div>
                                </div>-->
                                @forelse (Setting::cart_items() as $items)
                                    <div class="top-cart-item">
                                        <div class="top-cart-item-image">
                                            <a href="#"><img src="{{ asset('theme/images/shop/small/6.jpg') }}" alt="Light Blue Denim Dress" /></a>
                                        </div>
                                        <div class="top-cart-item-desc">
                                            <div class="top-cart-item-desc-title">
                                                <a href="#">Light Blue Denim Dress</a>
                                                <!--<span class="top-cart-item-price d-block">$24.99</span>-->
                                            </div>
                                            <div class="top-cart-item-quantity">x 3</div>
                                        </div>
                                    </div>
                                @empty
                                    <p>No items added on the card yet.</p>
                                @endforelse
                            </div>
                            <div class="top-cart-action">
                                <!--<span class="top-checkout-price">$114.95</span>-->
                                <a href="{{ route('cart.front.show') }}" class="button button-3d button-small m-0">View Cart</a>
                            </div>
                        </div>
                    </div><!-- #top-cart end -->

                </div>

                <!-- Mobile Menu Icon
                ============================================= -->
                <div id="primary-menu-trigger">
                    <svg class="svg-trigger" viewBox="0 0 100 100"><path d="m 30,33 h 40 c 3.722839,0 7.5,3.126468 7.5,8.578427 0,5.451959 -2.727029,8.421573 -7.5,8.421573 h -20"></path><path d="m 30,50 h 40"></path><path d="m 70,67 h -40 c 0,0 -7.5,-0.802118 -7.5,-8.365747 0,-7.563629 7.5,-8.634253 7.5,-8.634253 h 20"></path></svg>
                </div>

                <nav class="primary-menu with-arrows order-lg-1 order-last px-0">
                    @include('theme.layouts.menu')
                </nav>
            </div>
        </div>
    </div>
    <div class="header-wrap-clone"></div>
</header>

