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
	<!-- Beauty Kit Demo Specific Stylesheet -->
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

    @routes
</head>

<body class="stretched side-header">
    <!-- Document Wrapper
    ============================================= -->
    <div>
    <!--<div id="curtain" onclick="closeOffsideNav()"></div>-->

        <!-- Banner
        ============================================= -->
		@include('theme.layouts.banner')

        <!-- Content
        ============================================= -->
        <section id="content" class="{{ request()->path() != '/' ? 'position-relative' : '' }}">
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
		{{-- @include('theme.layouts.footers.pmc-website') --}}
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
