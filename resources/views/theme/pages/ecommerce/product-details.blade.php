@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('css/sweetalert.min.css') }}">
    <style>
        .product-detail-page {
            --detail-ink: #202522;
            --detail-muted: #68716c;
            --detail-accent: #c86b3c;
            --detail-accent-dark: #934523;
            --detail-paper: #fffdf9;
            --detail-line: #e7e2d9;
            background: #f5f1ea;
        }

        .product-detail-page .container {
            position: relative;
            z-index: 1;
        }

        .product-detail-page .single-product {
            padding: 24px 0 12px;
        }

        .product-detail-page .product > .row {
            align-items: flex-start;
        }

        .product-detail-page .product-desc {
            padding: 20px 18px 15px;
            color: var(--detail-ink);
        }

        .product-detail-page .product-detail-panel {
            order: 1;
        }

        .product-detail-page .product-reviews-panel {
            order: 2;
            padding: 20px 18px 15px;
        }

        .product-detail-page .product-reviews-panel > reviews,
        .product-detail-page .product-reviews-panel > review-form {
            display: block;
            padding: 24px;
            border: 1px solid var(--detail-line);
            border-radius: 14px;
            background: var(--detail-paper);
            box-shadow: 0 12px 28px rgba(53, 43, 32, .07);
        }

        .product-detail-page .product-reviews-panel hr {
            border-color: var(--detail-line);
        }

        .product-detail-page .product-desc > h1 {
            margin-bottom: 18px;
            color: var(--detail-ink);
            font-size: clamp(2rem, 4vw, 3.25rem);
            font-weight: 600;
            line-height: 1.08;
            letter-spacing: -.02em;
        }

        .product-detail-page .product-eyebrow {
            margin-bottom: 12px;
            color: var(--detail-accent-dark);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .product-detail-page .product-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #367253;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .product-detail-page .product-status::before {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            content: '';
        }

        .product-detail-page .product-rating {
            color: var(--detail-accent);
            letter-spacing: 2px;
        }

        .product-detail-page .line {
            margin: 24px 0;
            border-top-color: var(--detail-line);
        }

        .product-detail-page .cart {
            padding: 15px;
            border: 1px solid var(--detail-line);
            border-radius: 12px;
            background: var(--detail-paper);
        }

        .product-detail-page .quantity {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--detail-line);
            border-radius: 8px;
            overflow: hidden;
        }

        .product-detail-page .quantity .minus,
        .product-detail-page .quantity .plus,
        .product-detail-page .quantity .qty {
            width: 38px;
            height: 38px;
            border: 0;
            background: transparent;
            color: var(--detail-ink);
            text-align: center;
        }

        .product-detail-page .quantity .minus,
        .product-detail-page .quantity .plus {
            cursor: pointer;
            font-size: 1.1rem;
            transition: background .2s ease;
        }

        .product-detail-page .quantity .minus:hover,
        .product-detail-page .quantity .plus:hover {
            background: #f0ebe2;
        }

        .product-detail-page .quantity .qty {
            border-right: 1px solid var(--detail-line);
            border-left: 1px solid var(--detail-line);
            font-weight: 600;
        }

        .product-detail-page .add-to-cart {
            flex: 1;
            margin-left: 14px !important;
            border-radius: 8px;
            background: var(--detail-accent);
            box-shadow: 0 8px 18px rgba(200, 107, 60, .22);
            font-weight: 700;
            letter-spacing: .04em;
            text-align: center;
            transition: background .2s ease, transform .2s ease, box-shadow .2s ease;
        }

        .product-detail-page .add-to-cart:hover {
            background: var(--detail-accent-dark);
            box-shadow: 0 12px 24px rgba(147, 69, 35, .22);
            transform: translateY(-2px);
        }

        .product-detail-page .product-desc > p {
            max-width: 600px;
            color: var(--detail-muted);
            font-size: 1rem;
            line-height: 1.8;
        }

        .product-detail-page .product-meta {
            border: 1px solid var(--detail-line);
            border-radius: 12px;
            background: var(--detail-paper);
            box-shadow: none;
        }

        .product-detail-page .product-meta .card-body {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 28px;
            color: var(--detail-muted);
        }

        .product-detail-page .product-meta span {
            display: block;
            font-size: .82rem;
        }

        .product-detail-page .product-meta .sku,
        .product-detail-page .product-meta a {
            color: var(--detail-ink);
            font-weight: 600;
        }

        .product-detail-page .tabs {
            margin-top: 32px !important;
        }

        .product-detail-page .tab-nav {
            border-bottom-color: var(--detail-line);
        }

        .product-detail-page .tab-nav li a {
            color: var(--detail-muted);
            font-size: .85rem;
            font-weight: 600;
        }

        .product-detail-page .tab-nav li.ui-tabs-active a {
            color: var(--detail-accent-dark);
        }

        .product-detail-page .tab-content {
            color: var(--detail-muted);
            line-height: 1.7;
        }

        .product-detail-page .related-heading {
            margin: 48px 0 26px;
        }

        .product-detail-page .related-heading h2 {
            color: var(--detail-ink);
            font-size: 2rem;
            font-weight: 600 !important;
        }

        .product-detail-page .related-heading h2::after {
            display: block;
            width: 42px;
            height: 3px;
            margin: 14px auto 0;
            background: var(--detail-accent);
            content: '';
        }

        .product-detail-page .related-card {
            min-height: 188px;
            margin: 8px 10px 18px;
            padding: 24px;
            border: 1px solid var(--detail-line);
            border-radius: 14px;
            background: var(--detail-paper);
            box-shadow: 0 8px 22px rgba(53, 43, 32, .06);
            transition: box-shadow .25s ease, transform .25s ease, border-color .25s ease;
        }

        .product-detail-page .related-card:hover {
            border-color: rgba(200, 107, 60, .5);
            box-shadow: 0 18px 30px rgba(53, 43, 32, .12);
            transform: translateY(-6px);
        }

        .product-detail-page .related-card-link {
            display: flex;
            min-height: 132px;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-detail-page .related-card-index {
            color: var(--detail-accent);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .14em;
        }

        .product-detail-page .related-card .product-title h4 {
            margin-top: 18px;
            color: var(--detail-ink);
            font-size: 1.15rem;
            line-height: 1.25;
        }

        .product-detail-page .related-card .product-price {
            margin: 14px 0 0;
            color: var(--detail-accent-dark);
            font-size: 1rem;
        }

        @media (max-width: 767px) {
            .product-detail-page .product-desc {
                padding: 30px 0 15px;
            }

            .product-detail-page .cart {
                gap: 12px;
                flex-wrap: wrap;
            }

            .product-detail-page .add-to-cart {
                flex-basis: 100%;
                margin-left: 0 !important;
            }
        }

        /* force on file styling */
        section#content.py-lg-6 {
            padding-bottom: 0rem !important;
            padding-top: 0rem !important;
        }
    </style>
@endsection

@section('content')
<div class="content-wrap product-detail-page" style="overflow: visible;">
    <div class="container clearfix">
        <div class="single-product">
            <div class="product">
                <div class="row gutter-40">
                    <div class="col-md-4 product-reviews-panel">
                        <reviews :product="{{ $product }}"></reviews>

                        @if (auth()->check())
                            <hr />

                            <review-form
                                :product="{{ $product }}"
                                :user="{{ auth()->user() }}"
                            >
                            </review-form>
                        @endif
                    </div>

                    <div class="col-md-8 product-desc product-detail-panel position-lg-sticky h-100">
                        <div class="product-eyebrow">Product details</div>
                        <h1>{{$product->name}}</h1>

                        <div class="d-flex align-items-center justify-content-between">
                            <!-- Product Single - Price
                            ============================================= -->
                            <div class="product-price product-status"><ins>{{ $product->inventory > 0 ? 'In Stock' : 'Out of Stock' }}</ins></div><!-- Product Single - Price End -->

                            <!-- Product Single - Rating
                            ============================================= -->
                            <div class="product-rating">
                                <i class="icon-star3"></i>
                                <i class="icon-star3"></i>
                                <i class="icon-star3"></i>
                                <i class="icon-star-half-full"></i>
                                <i class="icon-star-empty"></i>
                            </div><!-- Product Single - Rating End -->

                        </div>

                        <div class="line"></div>

                        <!-- Product Single - Quantity & Cart Button
                        ============================================= -->
                        <form class="cart mb-0 d-flex justify-content-between align-items-center">
                            <div class="quantity clearfix">
                                <input type="button" value="-" class="minus">
                                <input id="qty" type="number" step="1" min="1" name="quantity" value="1" title="Qty" class="qty" />
                                <input type="button" value="+" class="plus">
                            </div>
                            <a href="javascript:;" onclick="add_to_cart('{{$product->id}}');" class="add-to-cart button m-0">Add to cart</a>
                        </form><!-- Product Single - Quantity & Cart Button End -->

                        <div class="line"></div>

                        <!-- Product Single - Short Description
                        ============================================= -->
                        <p>{{ $product->short_description ?? 'There is no short description to this product.' }}</p>
                        <!--<ul class="iconlist">
                            <li><i class="icon-caret-right"></i> Dynamic Color Options</li>
                            <li><i class="icon-caret-right"></i> Lots of Size Options</li>
                            <li><i class="icon-caret-right"></i> 30-Day Return Policy</li>
                        </ul><!-- Product Single - Short Description End -->

                        <!-- Product Single - Meta
                        ============================================= -->
                        <div class="card product-meta">
                            <div class="card-body">
                                <span itemprop="productID" class="sku_wrapper">SKU: <span class="sku">{{ $product->code }}</span></span>
                                <span class="posted_in">Category: <a href="#" rel="tag">{{ $product->category->name }}</a></span>
                                <!--<span class="tagged_as">Tags: <a href="#" rel="tag">Pink</a>, <a href="#" rel="tag">Short</a>, <a href="#" rel="tag">Dress</a>, <a href="#" rel="tag">Printed</a>.</span>-->
                            </div>
                        </div><!-- Product Single - Meta End -->

                        <div class="tabs clearfix my-5 mb-0" id="tab-1">

                            <ul class="tab-nav clearfix">
                                @if ($product->description)
                                    <li><a href="#tabs-1"><i class="icon-align-justify2"></i><span class="d-none d-md-inline-block"> Description</span></a></li>
                                @endif
                                <li><a href="#tabs-2"><i class="icon-info-sign"></i><span class="d-none d-md-inline-block"> Additional Information</span></a></li>
                                <!--<li><a href="#tabs-3"><i class="icon-star3"></i><span class="d-none d-md-inline-block"> Reviews (2)</span></a></li>-->
                            </ul>

                            <div class="tab-container">

                                <div class="tab-content clearfix" id="tabs-1">
                                    <p>{{ $product->description }}</p>
                                </div>
                                <div class="tab-content clearfix" id="tabs-2">

                                    <table class="table table-striped table-bordered">
                                        <tbody>
                                            <tr>
                                                <td>Size</td>
                                                <td>Small, Medium &amp; Large</td>
                                            </tr>
                                            <tr>
                                                <td>Color</td>
                                                <td>Pink &amp; White</td>
                                            </tr>
                                            <tr>
                                                <td>Waist</td>
                                                <td>26 cm</td>
                                            </tr>
                                            <tr>
                                                <td>Length</td>
                                                <td>40 cm</td>
                                            </tr>
                                            <tr>
                                                <td>Chest</td>
                                                <td>33 inches</td>
                                            </tr>
                                            <tr>
                                                <td>Fabric</td>
                                                <td>Cotton, Silk &amp; Synthetic</td>
                                            </tr>
                                            <tr>
                                                <td>Warranty</td>
                                                <td>3 Months</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                                <div class="tab-content clearfix" id="tabs-3">

                                    <div id="reviews" class="clearfix">

                                        <ol class="commentlist clearfix">

                                            <li class="comment even thread-even depth-1" id="li-comment-1">
                                                <div id="comment-1" class="comment-wrap clearfix">

                                                    <div class="comment-meta">
                                                        <div class="comment-author vcard">
                                                            <span class="comment-avatar clearfix"></span>
                                                        </div>
                                                    </div>

                                                    <div class="comment-content clearfix">
                                                        <div class="comment-author">John Doe<span><a href="#" title="Permalink to this comment">April 24, 2021 at 10:46AM</a></span></div>
                                                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quo perferendis aliquid tenetur. Aliquid, tempora, sit aliquam officiis nihil autem eum at repellendus facilis quaerat consequatur commodi laborum saepe non nemo nam maxime quis error tempore possimus est quasi reprehenderit fuga!</p>
                                                        <div class="review-comment-ratings">
                                                            <i class="icon-star3"></i>
                                                            <i class="icon-star3"></i>
                                                            <i class="icon-star3"></i>
                                                            <i class="icon-star3"></i>
                                                            <i class="icon-star-half-full"></i>
                                                        </div>
                                                    </div>

                                                    <div class="clear"></div>

                                                </div>
                                            </li>

                                            <li class="comment even thread-even depth-1" id="li-comment-2">
                                                <div id="comment-2" class="comment-wrap clearfix">

                                                    <div class="comment-meta">
                                                        <div class="comment-author vcard">
                                                            <span class="comment-avatar clearfix"></span>
                                                        </div>
                                                    </div>

                                                    <div class="comment-content clearfix">
                                                        <div class="comment-author">Mary Jane<span><a href="#" title="Permalink to this comment">June 16, 2021 at 6:00PM</a></span></div>
                                                        <p>Quasi, blanditiis, neque ipsum numquam odit asperiores hic dolor necessitatibus libero sequi amet voluptatibus ipsam velit qui harum temporibus cum nemo iste aperiam explicabo fuga odio ratione sint fugiat consequuntur vitae adipisci delectus eum incidunt possimus tenetur excepturi at accusantium quod doloremque reprehenderit aut expedita labore error atque?</p>
                                                        <div class="review-comment-ratings">
                                                            <i class="icon-star3"></i>
                                                            <i class="icon-star3"></i>
                                                            <i class="icon-star3"></i>
                                                            <i class="icon-star-empty"></i>
                                                            <i class="icon-star-empty"></i>
                                                        </div>
                                                    </div>

                                                    <div class="clear"></div>

                                                </div>
                                            </li>

                                        </ol>

                                        <!-- Modal Reviews
                                        ============================================= -->
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#reviewFormModal" class="button button-3d m-0 float-end">Add a Review</a>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- Product Single - Share
                        ============================================= -->
                        <!--<div class="si-share d-flex justify-content-between align-items-center mt-4">
                            <span>Share:</span>
                            <div>
                                <a href="#" class="social-icon si-borderless si-facebook">
                                    <i class="icon-facebook"></i>
                                    <i class="icon-facebook"></i>
                                </a>
                                <a href="#" class="social-icon si-borderless si-twitter">
                                    <i class="icon-twitter"></i>
                                    <i class="icon-twitter"></i>
                                </a>
                                <a href="#" class="social-icon si-borderless si-pinterest">
                                    <i class="icon-pinterest"></i>
                                    <i class="icon-pinterest"></i>
                                </a>
                                <a href="#" class="social-icon si-borderless si-gplus">
                                    <i class="icon-gplus"></i>
                                    <i class="icon-gplus"></i>
                                </a>
                                <a href="#" class="social-icon si-borderless si-rss">
                                    <i class="icon-rss"></i>
                                    <i class="icon-rss"></i>
                                </a>
                                <a href="#" class="social-icon si-borderless si-email3">
                                    <i class="icon-email3"></i>
                                    <i class="icon-email3"></i>
                                </a>
                            </div>
                        </div>--><!-- Product Single - Share End -->

                    </div>
                    <!--<div class="col-md-6">
                        {!! $product->description !!}

                        <a href="javascript:void(0)" onclick="add_to_cart('{{$product->id}}',1);" class="button button-large fw-medium color button-light bg-white px-lg-4 add-to-cart m-0 mb-3 border rounded"><i style="position: relative; top: -2px;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="var(--themecolor)" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M62.54543,144H188.10132a16,16,0,0,0,15.74192-13.13783L216,64H48Z" opacity="0.2"></path><path d="M184,184H69.81818L41.92162,30.56892A8,8,0,0,0,34.05066,24H16" fill="none" stroke="var(--themecolor)" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><circle cx="80" cy="204" r="20" fill="none" stroke="var(--themecolor)" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></circle><circle cx="184" cy="204" r="20" fill="none" stroke="var(--themecolor)" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></circle><path d="M62.54543,144H188.10132a16,16,0,0,0,15.74192-13.13783L216,64H48" fill="none" stroke="var(--themecolor)" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path></svg></i> Add to cart</a>
                    </div>-->
                </div>
            </div>
        </div>

        <div class="line"></div>

        @if(count($relatedProducts))
        <!-- Heading Title -->
        <div class="text-center related-heading">
            <h2 class="h1 fw-normal mb-4 loren-title loren-title-white"><en>Related</en> Products</h2>
        </div>
        @endif

        <!-- Categories -->
        <div id="oc-portfolio" class="owl-carousel portfolio-carousel carousel-widget" data-pagi="false" data-items-xs="1" data-items-sm="2" data-items-md="3" data-items-lg="4">
            @foreach($relatedProducts as $product)
            <div class="portfolio-item related-card">
                <a href="{{ route('product.front.show',$product->slug) }}" class="related-card-link">
                    <span class="related-card-index">EXPLORE ITEM</span>
                    <div class="product-title mb-0"><h4 class="mb-0">{{$product->name}}</h4></div>
                    <h5 class="product-price fw-normal">₱{{number_format($product->price,2)}}</h5>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('pagejs')
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script>
        function add_to_cart(product){
            const qty = $('#qty').val();

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
                        $('.sidebar-cart-number').html(returnData['totalItems']);
                        $('.top-cart-number').html(returnData['totalItems']);

                        $.notify("Product Added to your cart",{
                            position:"bottom right",
                            className: "success"
                        });

                    } else {
                        $.notify("Reached maximum limit number of items..",{
                            position:"bottom right",
                            className: "error"
                        });
                    }
                }
            });
        }
    </script>
@endsection
