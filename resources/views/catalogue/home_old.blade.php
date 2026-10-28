<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>

	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="author" content="SemiColonWeb" />

	<!-- Stylesheets
	============================================= -->
	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400i,700|Raleway:300,400,500,600,700,800,900|Roboto:700,500,900,400&display=swap" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="{{ asset('theme/catalogue/css/bootstrap.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('theme/catalogue/style.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('theme/catalogue/css/dark.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('theme/catalogue/css/font-icons.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('theme/catalogue/css/animate.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('theme/catalogue/css/magnific-popup.css') }}" type="text/css" />

	<!-- <link rel="stylesheet" href="{{ asset('theme/css/custom.css') }}" type="text/css" /> -->
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<!-- SLIDER REVOLUTION 5.x CSS SETTINGS  -->
	<link rel="stylesheet" type="text/css" href="{{ asset('theme/catalogue/css/settings.css') }}" media="screen" />
	<link rel="stylesheet" type="text/css" href="{{ asset('theme/catalogue/css/layers.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('theme/catalogue/css/navigation.css') }}">

	<style>
		#footbady {
 			background-image: url("{{ asset('theme/images/world-map.png') }}") no-repeat center center; 
 			background-size: 100%;
		}
	</style>
	<!-- Document Title
	============================================= -->
	<title>MCD - Product Catalogue</title>

</head>

<body class="stretched">

	<!-- Document Wrapper
	============================================= -->
	<div id="wrapper" class="clearfix">

		<!-- Header
		============================================= -->
		
		<header id="header" class="full-header transparent-header">
			<div id="header-wrap">
				<div class="container">
					<div class="header-row">

						<!-- Logo
						============================================= -->
						<div id="logo">
							<a href="index.html" class="standard-logo" data-dark-logo="{{ asset('theme/catalogue/images/pmc.png') }}"><img src="{{ asset('theme/catalogue/images/pmc.png') }}" alt="PMC"></a>
						
						</div><!-- #logo end -->

						<div class="header-misc">

							<!-- Top Search
							============================================= -->
							<div id="top-search" class="header-misc-icon">
								<a href="#" id="top-search-trigger"><i class="icon-line-search"></i><i class="icon-line-cross"></i></a>
							</div><!-- #top-search end -->

							<!-- Top Cart
							============================================= -->
							<div id="top-cart" class="header-misc-icon d-none d-sm-block">
								<a href="#" id="top-cart-trigger"><i class="icon-line-bag"></i><span class="top-cart-number">5</span></a>
								<div class="top-cart-content">
									<div class="top-cart-title">
										<h4>Shopping Cart</h4>
									</div>
									<div class="top-cart-items">
										<div class="top-cart-item">
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
										</div>
										<div class="top-cart-item">
											<div class="top-cart-item-image">
												<a href="#"><img src="{{ asset('theme/images/shop/small/6.jpg') }}" alt="Light Blue Denim Dress" /></a>
											</div>
											<div class="top-cart-item-desc">
												<div class="top-cart-item-desc-title">
													<a href="#">Light Blue Denim Dress</a>
													<span class="top-cart-item-price d-block">$24.99</span>
												</div>
												<div class="top-cart-item-quantity">x 3</div>
											</div>
										</div>
									</div>
									<div class="top-cart-action">
										<span class="top-checkout-price">$114.95</span>
										<a href="#" class="button button-3d button-small m-0">View Cart</a>
									</div>
								</div>
							</div><!-- #top-cart end -->

						</div>

						<div id="primary-menu-trigger">
							<svg class="svg-trigger" viewBox="0 0 100 100"><path d="m 30,33 h 40 c 3.722839,0 7.5,3.126468 7.5,8.578427 0,5.451959 -2.727029,8.421573 -7.5,8.421573 h -20"></path><path d="m 30,50 h 40"></path><path d="m 70,67 h -40 c 0,0 -7.5,-0.802118 -7.5,-8.365747 0,-7.563629 7.5,-8.634253 7.5,-8.634253 h 20"></path></svg>
						</div>

						<!-- Primary Navigation
						============================================= -->
						<nav class="primary-menu" style="width:80%;text-align: center;">

							<span style="font-size: 20px;">Philsaga Mining Corporation - Product Catalogue</span>

						</nav><!-- #primary-menu end -->

						<form class="top-search-form" action="search.html" method="get">
							<input type="text" name="q" class="form-control" value="" placeholder="Type &amp; Hit Enter.." autocomplete="off">
						</form>

					</div>
				</div>
			</div>
			<div class="header-wrap-clone"></div>
		</header>
		<!-- #header end -->

		<!-- Slider
		============================================= -->
		<section id="slider" class="slider-element revslider-wrap include-header">

			<div id="rev_slider_6_1_wrapper" class="rev_slider_wrapper fullscreen-container" data-alias="inspiration-header" style="background-color:transparent;padding:0px;">
				<!-- START REVOLUTION SLIDER 5.2.6 fullscreen mode -->
				<div id="rev_slider_6_1" class="rev_slider fullscreenbanner" data-version="5.2.6">
					<ul>	<!-- SLIDE  -->
						<li data-index="rs-18" data-transition="fade" data-slotamount="default" data-hideafterloop="0" data-hideslideonmobile="off"  data-easein="default" data-easeout="default" data-masterspeed="500"  data-rotate="0"  data-saveperformance="off"  data-title="Slide" data-param1="" data-param2="" data-param3="" data-param4="" data-param5="" data-param6="" data-param7="" data-param8="" data-param9="" data-param10="" data-description="">
							<!-- MAIN IMAGE -->
							<img src="{{ asset('theme/catalogue/demos/assets/images/transparent.png') }}" style='background-color:#f9f9f9' alt="Image"  data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" data-bgparallax="off" class="rev-slidebg" data-no-retina>
							<!-- LAYERS -->

							<!-- LAYER NR. 1 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-8"
								 id="slide-18-layer-4"
								 data-x="['left','left','left','left']" data-hoffset="['-264','-264','-371','-416']"
								 data-y="['top','top','top','top']" data-voffset="['-1','-1','-180','-203']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"

								 data-transform_in="x:left;rZ:-90deg;s:2500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="500"
								data-basealign="slide"
								data-responsive_offset="on"


								style="z-index: 5;"><img src="{{ asset('theme/catalogue/demos/assets/images/header_penpot.png') }}" alt="Image" data-ww="['650px','650px','650px','650px']" data-hh="['500px','500px','500px','500px']" data-no-retina> </div>

							<!-- LAYER NR. 2 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-9"
								 id="slide-18-layer-5"
								 data-x="['right','right','right','right']" data-hoffset="['230','230','-70','-144']"
								 data-y="['top','top','top','top']" data-voffset="['-150','-150','-233','-261']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"

								 data-transform_in="x:right;y:-500px;rZ:90deg;s:2500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="500"
								data-basealign="slide"
								data-responsive_offset="on"


								style="z-index: 6;"><img src="{{ asset('theme/catalogue/images/skullguard2.png') }}" alt="Image" data-ww="['700px','700px','700px','700px']" data-hh="['600px','600px','600px','600px']" data-no-retina> </div>

							<!-- LAYER NR. 3 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-10"
								 id="slide-18-layer-6"
								 data-x="['left','left','left','left']" data-hoffset="['-372','-372','-540','-638']"
								 data-y="['bottom','bottom','bottom','bottom']" data-voffset="['-484','-484','-520','-522']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"

								 data-transform_in="x:left;rZ:45deg;s:2500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="650"
								data-basealign="slide"
								data-responsive_offset="on"


								style="z-index: 7;"><img src="{{ asset('theme/catalogue/demos/assets/images/header_papers.png') }}" alt="Image" data-ww="['900px','900px','900px','900px']" data-hh="['1000px','1000px','1000px','1000px']" data-no-retina> </div>

							<!-- LAYER NR. 4 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-12"
								 id="slide-18-layer-9"
								 data-x="['left','left','left','left']" data-hoffset="['134','134','-2','-57']"
								 data-y="['bottom','bottom','bottom','bottom']" data-voffset="['67','67','50','6']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"

								 data-transform_in="y:bottom;rZ:90deg;s:1500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="1050"
								data-basealign="slide"
								data-responsive_offset="on"


								style="z-index: 8;"><img src="{{ asset('theme/catalogue/demos/assets/images/header_marker.png') }}" alt="Image" data-ww="['200px','200px','200px','200px']" data-hh="['300px','300px','300px','300px']" data-no-retina> </div>

							<!-- LAYER NR. 5 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-11"
								 id="slide-18-layer-10"
								 data-x="['left','left','left','left']" data-hoffset="['181','181','30','-39']"
								 data-y="['bottom','bottom','bottom','bottom']" data-voffset="['-170','-170','-190','-212']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"

								 data-transform_in="y:bottom;rZ:-90deg;s:1500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="1250"
								data-basealign="slide"
								data-responsive_offset="on"


								style="z-index: 9;"><img src="{{ asset('theme/catalogue/demos/assets/images/header_edding.png') }}" alt="Image" data-ww="['300px','300px','300px','300px']" data-hh="['400px','400px','400px','400px']" data-no-retina> </div>

							<!-- LAYER NR. 6 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-12"
								 id="slide-18-layer-11"
								 data-x="['left','left','left','left']" data-hoffset="['393','393','201','81']"
								 data-y="['bottom','bottom','bottom','bottom']" data-voffset="['218','218','183','191']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"

								 data-transform_in="y:bottom;rZ:-90deg;s:1500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="500"
								data-basealign="slide"
								data-responsive_offset="on"


								style="z-index: 10;"><img src="{{ asset('theme/catalogue/demos/assets/images/header_paperclip.png') }}" alt="Image" data-ww="['50px','50px','50px','50px']" data-hh="['100px','100px','100px','100px']" data-no-retina> </div>

							<!-- LAYER NR. 7 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-11"
								 id="slide-18-layer-12"
								 data-x="['left','left','left','left']" data-hoffset="['346','346','165','36']"
								 data-y="['bottom','bottom','bottom','bottom']" data-voffset="['255','255','237','231']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;rZ:310;"

								 data-transform_in="y:bottom;rZ:90deg;s:2000;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="550"
								data-basealign="slide"
								data-responsive_offset="on"


								style="z-index: 11;"><img src="{{ asset('theme/catalogue/demos/assets/images/header_paperclip.png') }}" alt="Image" data-ww="['50px','50px','50px','50px']" data-hh="['100px','100px','100px','100px']" data-no-retina> </div>

							<!-- LAYER NR. 8 
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-9"
								 id="slide-18-layer-7"
								 data-x="['right','right','right','right']" data-hoffset="['-364','-364','-479','-553']"
								 data-y="['bottom','bottom','bottom','bottom']" data-voffset="['-155','-155','-180','-202']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"

								 data-transform_in="x:right;rZ:-65deg;s:1500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="700"
								data-basealign="slide"
								data-responsive_offset="on"

							

								style="z-index: 12;"><img src="{{ asset('theme/catalogue/demos/assets/images/header_tablet.png') }}" alt="Image" data-ww="['730px','730px','730px','730px']" data-hh="['520px','520px','520px','520px']" data-no-retina> </div>
								-->

							<!-- LAYER NR. 9 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-11"
								 id="slide-18-layer-8"
								 data-x="['right','right','right','right']" data-hoffset="['222','222','105','8']"
								 data-y="['bottom','bottom','bottom','bottom']" data-voffset="['-100','-100','-17','-10']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"

								 data-transform_in="x:right;rZ:90deg;s:1500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="500"
								data-basealign="slide"
								data-responsive_offset="on"


								style="z-index: 13;"><img src="{{ asset('theme/catalogue/images/pngegg.png') }}" alt="Image" data-ww="['500px','500px','500px','500px']" data-hh="['500px','500px','500px','500px']" data-no-retina> </div> 
							<!-- LAYER NR. 10 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-2"
								 id="slide-18-layer-1"
								 data-x="['center','center','center','center']" data-hoffset="['0','0','0','0']"
								 data-y="['middle','middle','middle','middle']" data-voffset="['-70','-70','-70','-90']"
											data-fontsize="['80','80','80','60']"
								data-lineheight="['80','80','80','60']"
								data-width="['none','none','none','360']"
								data-height="none"
								data-whitespace="['nowrap','nowrap','nowrap','normal']"
								data-transform_idle="o:1;"

								 data-transform_in="y:-50px;rX:-45deg;sX:2;sY:2;opacity:0;s:1500;e:Power4.easeOut;"
								 data-transform_out="y:30px;rX:45deg;sX:0.8;sY:0.8;opacity:0;s:600;e:Power2.easeInOut;"
								data-start="510"
								data-splitin="none"
								data-splitout="none"
								data-responsive_offset="on"

								 data-end="2650"

								style="z-index: 14; white-space: nowrap; font-size: 80px; line-height: 80px; font-weight: 700; color: rgba(0, 0, 0, 1.00);font-family:Roboto;text-align:center;">Office Furnitures</div>

							<!-- LAYER NR. 11 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-2"
								 id="slide-18-layer-15"
								 data-x="['center','center','center','center']" data-hoffset="['0','0','0','0']"
								 data-y="['middle','middle','middle','middle']" data-voffset="['-70','-70','-70','-90']"
											data-fontsize="['80','80','80','60']"
								data-lineheight="['80','80','80','60']"
								data-width="['none','none','none','360']"
								data-height="none"
								data-whitespace="['nowrap','nowrap','nowrap','normal']"
								data-transform_idle="o:1;"

								 data-transform_in="y:-50px;rX:-45deg;sX:2;sY:2;opacity:0;s:1500;e:Power4.easeOut;"
								 data-transform_out="y:30px;rX:45deg;sX:0.8;sY:0.8;opacity:0;s:600;e:Power2.easeInOut;"
								data-start="2940"
								data-splitin="none"
								data-splitout="none"
								data-responsive_offset="on"

								 data-end="5100"

								style="z-index: 15; white-space: nowrap; font-size: 80px; line-height: 80px; font-weight: 700; color: rgba(0, 0, 0, 1.00);font-family:Roboto;text-align:center;">Tools and Equipments </div>

							<!-- LAYER NR. 12 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-2"
								 id="slide-18-layer-16"
								 data-x="['center','center','center','center']" data-hoffset="['0','0','0','0']"
								 data-y="['middle','middle','middle','middle']" data-voffset="['-70','-70','-70','-90']"
											data-fontsize="['80','80','80','60']"
								data-lineheight="['80','80','80','60']"
								data-width="['none','none','none','400']"
								data-height="none"
								data-whitespace="['nowrap','nowrap','nowrap','normal']"
								data-transform_idle="o:1;"

								 data-transform_in="y:-50px;rX:-45deg;sX:2;sY:2;opacity:0;s:1500;e:Power4.easeOut;"
								 data-transform_out="y:30px;rX:45deg;sX:0.8;sY:0.8;opacity:0;s:600;e:Power2.easeInOut;"
								data-start="5390"
								data-splitin="none"
								data-splitout="none"
								data-responsive_offset="on"


								style="z-index: 16; white-space: nowrap; font-size: 80px; line-height: 80px; font-weight: 700; color: rgba(0, 0, 0, 1.00);font-family:Roboto;text-align:center;">PPE Supplies </div>

							<!-- LAYER NR. 13 -->
							<div class="tp-caption   tp-resizeme rs-parallaxlevel-2"
								 id="slide-18-layer-2"
								 data-x="['center','center','center','center']" data-hoffset="['-8','-8','-8','-8']"
								 data-y="['middle','middle','middle','middle']" data-voffset="['10','10','10','-10']"
											data-fontsize="['20','20','20','25']"
								data-lineheight="['20','20','20','30']"
								data-width="['none','none','none','360']"
								data-height="none"
								data-whitespace="['nowrap','nowrap','nowrap','normal']"
								data-transform_idle="o:1;"

								 data-transform_in="y:50px;rX:45deg;sX:2;sY:2;opacity:0;s:1500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="600"
								data-splitin="none"
								data-splitout="none"
								data-responsive_offset="on"


								style="z-index: 17; white-space: nowrap; font-size: 20px; line-height: 20px; font-weight: 400; color: rgba(68, 68, 68, 1.00);font-family:Roboto;text-align:center;">Welcome to PMC - MCD Product Catalogue </div>

							<!-- LAYER NR. 14 -->
							<div class="tp-caption rev-btn  rs-parallaxlevel-3"
								 id="slide-18-layer-3"
								 data-x="['center','center','center','center']" data-hoffset="['0','0','0','0']"
								 data-y="['middle','middle','middle','middle']" data-voffset="['92','92','92','92']"
											data-width="none"
								data-height="none"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"
									data-transform_hover="o:1;rX:0;rY:0;rZ:0;z:0;s:150;e:Power1.easeInOut;"
									data-style_hover="c:rgba(255, 255, 255, 1.00);bg:rgba(32, 85, 199, 1.00);"

								 data-transform_in="y:100px;rX:90deg;opacity:0;s:1500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="700"
								data-splitin="none"
								data-splitout="none"
								data-actions='[{"event":"click","action":"scrollbelow","offset":"-60px","delay":""}]'
								data-responsive_offset="on"
								data-responsive="off"

								style="z-index: 18; white-space: nowrap; font-size: 15px; line-height: 50px; font-weight: 700; color: rgba(255, 255, 255, 1.00);font-family:Roboto;background-color:rgba(41, 106, 245, 1.00);padding:0px 30px 0px 30px;border-color:rgba(0, 0, 0, 1.00);border-radius:3px 3px 3px 3px;outline:none;box-shadow:none;box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;letter-spacing:2px;cursor:pointer;">START BROWSING </div>

							<!-- LAYER NR. 15 -->
							<div class="tp-caption rev-scroll-btn revs-dark  rs-parallaxlevel-5"
								 id="slide-18-layer-13"
								 data-x="['center','center','center','center']" data-hoffset="['0','0','0','0']"
								 data-y="['bottom','bottom','bottom','bottom']" data-voffset="['50','50','50','50']"
											data-width="35"
								data-height="55"
								data-whitespace="nowrap"
								data-transform_idle="o:1;"

								 data-transform_in="y:-50px;opacity:0;s:1500;e:Power4.easeOut;"
								 data-transform_out="opacity:0;s:300;"
								data-start="800"
								data-splitin="none"
								data-splitout="none"
								data-actions='[{"event":"click","action":"scrollbelow","offset":"-60px","delay":""}]'
								data-basealign="slide"
								data-responsive_offset="on"
								data-responsive="off"

								style="z-index: 19; min-width: 35px; max-width: 35px; max-width: 55px; max-width: 55px; white-space: nowrap; font-size: px; line-height: px; font-weight: 400;border-color:rgba(51, 51, 51, 1.00);border-style:solid;border-width:3px;border-radius:23px 23px 23px 23px;box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;cursor:pointer;">
								<span></span>
							</div>
						</li>
					</ul>
					<div class="tp-bannertimer tp-bottom" style="visibility: hidden !important;"></div>
				</div>
			</div><!-- END REVOLUTION SLIDER -->

		</section>

		<!-- Content
		============================================= -->
		<section id="content">
			<div class="content-wrap">
				<div class="container clearfix">

					<div class="grid-filter-wrap">

						<!-- Portfolio Filter
						============================================= -->
						<ul class="grid-filter" data-container="#portfolio">
							<li class="activeFilter"><a href="#" data-filter="*">Show All</a></li>
							<li><a href="#" data-filter=".pf-icons">Icons</a></li>
							<li class=""><a href="#" data-filter=".pf-illustrations">Illustrations</a></li>
							<li class=""><a href="#" data-filter=".pf-uielements">UI Elements</a></li>
							<li><a href="#" data-filter=".pf-media">Media</a></li>
							<li class=""><a href="#" data-filter=".pf-graphics">Graphics</a></li>
						</ul><!-- .grid-filter end -->

						<div class="grid-shuffle rounded" data-container="#portfolio">
							<i class="icon-random"></i>
						</div>

					</div>

					<!-- Portfolio Items
					============================================= -->
					<div id="portfolio" class="portfolio row grid-container gutter-20 has-init-isotope" data-layout="fitRows" style="position: relative; height: 1037.25px;">

						<!-- Portfolio Item: Start -->
						<article class="portfolio-item col-lg-3 col-md-4 col-sm-6 col-12 pf-media pf-icons" style="position: absolute; left: 0%; top: 0px;">
							<!-- Grid Inner: Start -->
							<div class="grid-inner">
								<!-- Image: Start -->
								<div class="portfolio-image">
									<a href="portfolio-single.html">
										<img src="{{ asset('theme/images/portfolio/4/1.jpg') }}" alt="Open Imagination">
									</a>
									<!-- Overlay: Start -->
									<div class="bg-overlay">
										<div class="bg-overlay-content dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
											<a href="{{ asset('theme/images/portfolio/full/1.jpg') }}" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="image" title="Image" style="animation-duration: 350ms;"><i class="icon-line-plus"></i></a>
											<a href="portfolio-single.html" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line-ellipsis"></i></a>
										</div>
										<div class="bg-overlay-bg dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
									</div>
									<!-- Overlay: End -->
								</div>
								<!-- Image: End -->
								<!-- Decription: Start -->
								<div class="portfolio-desc">
									<h3><a href="portfolio-single.html">Open Imagination</a></h3>
									<span><a href="#">Media</a>, <a href="#">Icons</a></span>
								</div>
								<!-- Description: End -->
							</div>
							<!-- Grid Inner: End -->
						</article>
						<!-- Portfolio Item: End -->

						<article class="portfolio-item col-lg-3 col-md-4 col-sm-6 col-12 pf-illustrations" style="position: absolute; left: 25%; top: 0px;">
							<div class="grid-inner">
								<div class="portfolio-image">
									<a href="portfolio-single.html">
										<img src="{{ asset('theme/images/portfolio/4/2.jpg') }}" alt="Locked Steel Gate">
									</a>
									<div class="bg-overlay">
										<div class="bg-overlay-content dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
											<a href="{{ asset('theme/images/portfolio/full/2.jpg') }}" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="image" title="Image" style="animation-duration: 350ms;"><i class="icon-line-plus"></i></a>
											<a href="portfolio-single.html" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line-ellipsis"></i></a>
										</div>
										<div class="bg-overlay-bg dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
									</div>
								</div>
								<div class="portfolio-desc">
									<h3><a href="portfolio-single.html">Locked Steel Gate</a></h3>
									<span><a href="#">Illustrations</a></span>
								</div>
							</div>
						</article>

						<article class="portfolio-item col-lg-3 col-md-4 col-sm-6 col-12 pf-graphics pf-uielements" style="position: absolute; left: 50%; top: 0px;">
							<div class="grid-inner">
								<div class="portfolio-image">
									<a href="#">
										<img src="{{ asset('theme/images/portfolio/4/3.jpg') }}" alt="Mac Sunglasses">
									</a>
									<div class="bg-overlay">
										<div class="bg-overlay-content dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
											<a href="https://vimeo.com/89396394" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="iframe" style="animation-duration: 350ms;"><i class="icon-line-play"></i></a>
											<a href="portfolio-single.html" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line-ellipsis"></i></a>
										</div>
										<div class="bg-overlay-bg dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
									</div>
								</div>
								<div class="portfolio-desc">
									<h3><a href="portfolio-single-video.html">Mac Sunglasses</a></h3>
									<span><a href="#">Graphics</a>, <a href="#">UI Elements</a></span>
								</div>
							</div>
						</article>

						

						<article class="portfolio-item col-lg-3 col-md-4 col-sm-6 col-12 pf-uielements pf-media" style="position: absolute; left: 0%; top: 345.75px;">
							<div class="grid-inner">
								<div class="portfolio-image">
									<a href="portfolio-single.html">
										<img src="{{ asset('theme/images/portfolio/4/5.jpg') }}" alt="Console Activity">
									</a>
									<div class="bg-overlay">
										<div class="bg-overlay-content dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
											<a href="{{ asset('theme/images/portfolio/full/5.jpg') }}" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="image" title="Image" style="animation-duration: 350ms;"><i class="icon-line-plus"></i></a>
											<a href="portfolio-single.html" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line-ellipsis"></i></a>
										</div>
										<div class="bg-overlay-bg dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
									</div>
								</div>
								<div class="portfolio-desc">
									<h3><a href="portfolio-single.html">Console Activity</a></h3>
									<span><a href="#">UI Elements</a>, <a href="#">Media</a></span>
								</div>
							</div>
						</article>


						<article class="portfolio-item col-lg-3 col-md-4 col-sm-6 col-12 pf-uielements pf-icons" style="position: absolute; left: 50%; top: 345.75px;">
							<div class="grid-inner">
								<div class="portfolio-image">
									<a href="portfolio-single-video.html">
										<img src="{{ asset('theme/images/portfolio/4/7.jpg') }}" alt="Backpack Contents">
									</a>
									<div class="bg-overlay">
										<div class="bg-overlay-content dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
											<a href="https://www.youtube.com/watch?v=kuceVNBTJio" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="iframe" style="animation-duration: 350ms;"><i class="icon-line-play"></i></a>
											<a href="portfolio-single.html" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line-ellipsis"></i></a>
										</div>
										<div class="bg-overlay-bg dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
									</div>
								</div>
								<div class="portfolio-desc">
									<h3><a href="portfolio-single-video.html">Backpack Contents</a></h3>
									<span><a href="#">UI Elements</a>, <a href="#">Icons</a></span>
								</div>
							</div>
						</article>

						<article class="portfolio-item col-lg-3 col-md-4 col-sm-6 col-12 pf-graphics" style="position: absolute; left: 75%; top: 345.75px;">
							<div class="grid-inner">
								<div class="portfolio-image">
									<a href="portfolio-single.html">
										<img src="{{ asset('theme/images/portfolio/4/8.jpg') }}" alt="Sunset Bulb Glow">
									</a>
									<div class="bg-overlay">
										<div class="bg-overlay-content dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
											<a href="{{ asset('theme/images/portfolio/full/8.jpg') }}" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="image" title="Image" style="animation-duration: 350ms;"><i class="icon-line-plus"></i></a>
											<a href="portfolio-single.html" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line-ellipsis"></i></a>
										</div>
										<div class="bg-overlay-bg dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
									</div>
								</div>
								<div class="portfolio-desc">
									<h3><a href="portfolio-single.html">Sunset Bulb Glow</a></h3>
									<span><a href="#">Graphics</a></span>
								</div>
							</div>
						</article>

						<article class="portfolio-item col-lg-3 col-md-4 col-sm-6 col-12 pf-illustrations pf-icons" style="position: absolute; left: 0%; top: 691.5px;">
							<div class="grid-inner">
								<div class="portfolio-image">
									<div class="fslider" data-arrows="false" data-speed="650" data-pause="3500" data-animation="fade">
										<div class="flexslider" style="height: 231.75px;">
											<div class="slider-wrap">
												<div class="slide" data-thumb-alt="" style="width: 100%; float: left; margin-right: -100%; position: relative; opacity: 0; display: block; z-index: 1;"><a href="portfolio-single-gallery.html"><img src="{{ asset('theme/images/portfolio/4/9.jpg') }}" alt="Bridge Side" draggable="false"></a></div>
												<div class="slide" data-thumb-alt="" style="width: 100%; float: left; margin-right: -100%; position: relative; opacity: 0; display: block; z-index: 1;"><a href="portfolio-single-gallery.html"><img src="{{ asset('theme/images/portfolio/4/9-1.jpg') }}" alt="Bridge Side" draggable="false"></a></div>
												<div class="slide flex-active-slide" data-thumb-alt="" style="width: 100%; float: left; margin-right: -100%; position: relative; opacity: 1; display: block; z-index: 2;"><a href="portfolio-single-gallery.html"><img src="{{ asset('theme/images/portfolio/4/9-2.jpg') }}" alt="Bridge Side" draggable="false"></a></div>
											</div>
										<ol class="flex-control-nav flex-control-paging"><li><a href="#" class="">1</a></li><li><a href="#" class="">2</a></li><li><a href="#" class="flex-active">3</a></li></ol></div>
									</div>
									<div class="bg-overlay" data-lightbox="gallery">
										<div class="bg-overlay-content dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
											<a href="{{ asset('theme/images/portfolio/full/9.jpg') }}" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="gallery-item" style="animation-duration: 350ms;"><i class="icon-line-stack-2"></i></a>
											<a href="{{ asset('theme/images/portfolio/full/9-1.jpg') }}" class="d-none" data-lightbox="gallery-item"></a>
											<a href="{{ asset('theme/images/portfolio/full/9-2.jpg') }}" class="d-none" data-lightbox="gallery-item"></a>
											<a href="portfolio-single-gallery.html" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line-ellipsis"></i></a>
										</div>
										<div class="bg-overlay-bg dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
									</div>
								</div>
								<div class="portfolio-desc">
									<h3><a href="portfolio-single.html">Bridge Side</a></h3>
									<span><a href="#">Illustrations</a>, <a href="#">Icons</a></span>
								</div>
							</div>
						</article>

						<article class="portfolio-item col-lg-3 col-md-4 col-sm-6 col-12 pf-graphics pf-media pf-uielements" style="position: absolute; left: 25%; top: 691.5px;">
							<div class="grid-inner">
								<div class="portfolio-image">
									<a href="portfolio-single-video.html">
										<img src="{{ asset('theme/images/portfolio/4/10.jpg') }}" alt="Study Table">
									</a>
									<div class="bg-overlay">
										<div class="bg-overlay-content dark not-animated" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
											<a href="https://vimeo.com/91973305" class="overlay-trigger-icon bg-light text-dark not-animated" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="iframe" style="animation-duration: 350ms;"><i class="icon-line-play"></i></a>
											<a href="portfolio-single.html" class="overlay-trigger-icon bg-light text-dark not-animated" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line-ellipsis"></i></a>
										</div>
										<div class="bg-overlay-bg dark not-animated" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
									</div>
								</div>
								<div class="portfolio-desc">
									<h3><a href="portfolio-single-video.html">Study Table</a></h3>
									<span><a href="#">Graphics</a>, <a href="#">Media</a></span>
								</div>
							</div>
						</article>

						<article class="portfolio-item col-lg-3 col-md-4 col-sm-6 col-12 pf-media pf-icons" style="position: absolute; left: 50%; top: 691.5px;">
							<div class="grid-inner">
								<div class="portfolio-image">
									<a href="portfolio-single.html">
										<img src="{{ asset('theme/images/portfolio/4/11.jpg') }}" alt="Workspace Stuff">
									</a>
									<div class="bg-overlay">
										<div class="bg-overlay-content dark not-animated" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
											<a href="{{ asset('theme/images/portfolio/full/11.jpg') }}" class="overlay-trigger-icon bg-light text-dark not-animated" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="image" title="Image" style="animation-duration: 350ms;"><i class="icon-line-plus"></i></a>
											<a href="portfolio-single.html" class="overlay-trigger-icon bg-light text-dark not-animated" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line-ellipsis"></i></a>
										</div>
										<div class="bg-overlay-bg dark not-animated" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
									</div>
								</div>
								<div class="portfolio-desc">
									<h3><a href="portfolio-single.html">Workspace Stuff</a></h3>
									<span><a href="#">Media</a>, <a href="#">Icons</a></span>
								</div>
							</div>
						</article>

						

					</div><!-- #portfolio end -->

				</div>
			</div>
		</section>
		<section id="content">
			<div class="content-wrap">
				<div class="container clearfix">

					<div class="text-center"><a href="rs-demos.html" class="btn btn-secondary btn-lg w-100" style="max-width: 20rem;"><i class="icon-line-arrow-left me-2" style="position: relative; top: 1px;"></i> Back to All Demos</a></div>

				</div>
			</div>
		</section><!-- #content end -->

		<!-- Footer
		============================================= -->
		<footer id="footer" class="dark">
			<div class="container">

				<!-- Footer Widgets
				============================================= -->
				<div class="footer-widgets-wrap">

					<div class="row col-mb-50">
						<div class="col-lg-8">
							<div class="row col-mb-50">
								<div class="col-md-4">

									<div class="widget clearfix">

										<img src="{{ asset('theme/images/footer-widget-logo.png') }}" alt="Image" class="footer-logo">

										<p>We believe in <strong>Simple</strong>, <strong>Creative</strong> &amp; <strong>Flexible</strong> Design Standards.</p>

										<div id="footbady">
											<address>
												<strong>Headquarters:</strong><br>
												795 Folsom Ave, Suite 600<br>
												San Francisco, CA 94107<br>
											</address>
											<abbr title="Phone Number"><strong>Phone:</strong></abbr> (1) 8547 632521<br>
											<abbr title="Fax"><strong>Fax:</strong></abbr> (1) 11 4752 1433<br>
											<abbr title="Email Address"><strong>Email:</strong></abbr> info@canvas.com
										</div>

									</div>

								</div>

								<div class="col-md-4">

									<div class="widget widget_links clearfix">

										<h4>Blogroll</h4>

										<ul>
											<li><a href="https://codex.wordpress.org/">Documentation</a></li>
											<li><a href="https://wordpress.org/support/forum/requests-and-feedback">Feedback</a></li>
											<li><a href="https://wordpress.org/extend/plugins/">Plugins</a></li>
											<li><a href="https://wordpress.org/support/">Support Forums</a></li>
											<li><a href="https://wordpress.org/extend/themes/">Themes</a></li>
											<li><a href="https://wordpress.org/news/">Canvas Blog</a></li>
											<li><a href="https://planet.wordpress.org/">Canvas Planet</a></li>
										</ul>

									</div>

								</div>

								<div class="col-md-4">

									<div class="widget clearfix">
										<h4>Recent Posts</h4>

										<div class="posts-sm row col-mb-30" id="post-list-footer">
											<div class="entry col-12">
												<div class="grid-inner row">
													<div class="col">
														<div class="entry-title">
															<h4><a href="#">Lorem ipsum dolor sit amet, consectetur</a></h4>
														</div>
														<div class="entry-meta">
															<ul>
																<li>10th July 2021</li>
															</ul>
														</div>
													</div>
												</div>
											</div>

											<div class="entry col-12">
												<div class="grid-inner row">
													<div class="col">
														<div class="entry-title">
															<h4><a href="#">Elit Assumenda vel amet dolorum quasi</a></h4>
														</div>
														<div class="entry-meta">
															<ul>
																<li>10th July 2021</li>
															</ul>
														</div>
													</div>
												</div>
											</div>

											<div class="entry col-12">
												<div class="grid-inner row">
													<div class="col">
														<div class="entry-title">
															<h4><a href="#">Debitis nihil placeat, illum est nisi</a></h4>
														</div>
														<div class="entry-meta">
															<ul>
																<li>10th July 2021</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

								</div>
							</div>

						</div>

						<div class="col-lg-4">

							<div class="row col-mb-50">
								<div class="col-md-4 col-lg-12">
									<div class="widget clearfix" style="margin-bottom: -20px;">

										<div class="row">
											<div class="col-lg-6 bottommargin-sm">
												<div class="counter counter-small"><span data-from="50" data-to="15065421" data-refresh-interval="80" data-speed="3000" data-comma="true"></span></div>
												<h5 class="mb-0">Total Downloads</h5>
											</div>

											<div class="col-lg-6 bottommargin-sm">
												<div class="counter counter-small"><span data-from="100" data-to="18465" data-refresh-interval="50" data-speed="2000" data-comma="true"></span></div>
												<h5 class="mb-0">Clients</h5>
											</div>
										</div>

									</div>
								</div>

								<div class="col-md-5 col-lg-12">
									<div class="widget subscribe-widget clearfix">
										<h5><strong>Subscribe</strong> to Our Newsletter to get Important News, Amazing Offers &amp; Inside Scoops:</h5>
										<div class="widget-subscribe-form-result"></div>
										<form id="widget-subscribe-form" action="include/subscribe.php" method="post" class="mb-0">
											<div class="input-group mx-auto">
												<div class="input-group-text"><i class="icon-email2"></i></div>
												<input type="email" id="widget-subscribe-form-email" name="widget-subscribe-form-email" class="form-control required email" placeholder="Enter your Email">
												<button class="btn btn-success" type="submit">Subscribe</button>
											</div>
										</form>
									</div>
								</div>

								<div class="col-md-3 col-lg-12">
									<div class="widget clearfix" style="margin-bottom: -20px;">

										<div class="row">
											<div class="col-6 col-md-12 col-lg-6 clearfix bottommargin-sm">
												<a href="#" class="social-icon si-dark si-colored si-facebook mb-0" style="margin-right: 10px;">
													<i class="icon-facebook"></i>
													<i class="icon-facebook"></i>
												</a>
												<a href="#"><small style="display: block; margin-top: 3px;"><strong>Like us</strong><br>on Facebook</small></a>
											</div>
											<div class="col-6 col-md-12 col-lg-6 clearfix">
												<a href="#" class="social-icon si-dark si-colored si-rss mb-0" style="margin-right: 10px;">
													<i class="icon-rss"></i>
													<i class="icon-rss"></i>
												</a>
												<a href="#"><small style="display: block; margin-top: 3px;"><strong>Subscribe</strong><br>to RSS Feeds</small></a>
											</div>
										</div>

									</div>
								</div>

							</div>

						</div>
					</div>

				</div><!-- .footer-widgets-wrap end -->

			</div>

			<!-- Copyrights
			============================================= -->
			<div id="copyrights">
				<div class="container">

					<div class="row col-mb-30">

						<div class="col-md-6 text-center text-md-start">
							Copyrights &copy; 2020 All Rights Reserved by Canvas Inc.<br>
							<div class="copyright-links"><a href="#">Terms of Use</a> / <a href="#">Privacy Policy</a></div>
						</div>

						<div class="col-md-6 text-center text-md-end">
							<div class="d-flex justify-content-center justify-content-md-end">
								<a href="#" class="social-icon si-small si-borderless si-facebook">
									<i class="icon-facebook"></i>
									<i class="icon-facebook"></i>
								</a>

								<a href="#" class="social-icon si-small si-borderless si-twitter">
									<i class="icon-twitter"></i>
									<i class="icon-twitter"></i>
								</a>

								<a href="#" class="social-icon si-small si-borderless si-gplus">
									<i class="icon-gplus"></i>
									<i class="icon-gplus"></i>
								</a>

								<a href="#" class="social-icon si-small si-borderless si-pinterest">
									<i class="icon-pinterest"></i>
									<i class="icon-pinterest"></i>
								</a>

								<a href="#" class="social-icon si-small si-borderless si-vimeo">
									<i class="icon-vimeo"></i>
									<i class="icon-vimeo"></i>
								</a>

								<a href="#" class="social-icon si-small si-borderless si-github">
									<i class="icon-github"></i>
									<i class="icon-github"></i>
								</a>

								<a href="#" class="social-icon si-small si-borderless si-yahoo">
									<i class="icon-yahoo"></i>
									<i class="icon-yahoo"></i>
								</a>

								<a href="#" class="social-icon si-small si-borderless si-linkedin">
									<i class="icon-linkedin"></i>
									<i class="icon-linkedin"></i>
								</a>
							</div>

							<div class="clear"></div>

							<i class="icon-envelope2"></i> info@canvas.com <span class="middot">&middot;</span> <i class="icon-headphones"></i> +1-11-6541-6369 <span class="middot">&middot;</span> <i class="icon-skype2"></i> CanvasOnSkype
						</div>

					</div>

				</div>
			</div><!-- #copyrights end -->
		</footer><!-- #footer end -->

	</div><!-- #wrapper end -->

	<!-- Go To Top
	============================================= -->
	<div id="gotoTop" class="icon-angle-up"></div>

	<!-- JavaScripts
	============================================= -->
	<script src="{{ asset('theme/catalogue/js/jquery.js') }}"></script>
	<script src="{{ asset('theme/catalogue/js/plugins.min.js') }}"></script>

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

</body>
</html>