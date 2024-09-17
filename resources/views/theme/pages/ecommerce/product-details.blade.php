@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
@endsection

@section('content')
<div class="content-wrap" style="overflow: visible;">
    <div class="container clearfix">
        <div class="single-product">
            <div class="product">
                <div class="row gutter-40">
                    <div class="col-md-6">
                        <!-- Product Single - Gallery
                        ============================================= -->
                        <div class="product-image border-orange">
                            <div class="fslider" data-easing="easeInQuad" data-arrows="true" data-thumbs="false" data-sync="thumb-image">
                                <div class="flexslider" id="ewan">
                                    <div class="slider-wrap" data-lightbox="gallery">
                                        @forelse($product->photos as $photo)
                                            <div class="slide" data-thumb="{{ asset('storage/'.$photo->path) }}">
                                                <a href="{{ asset('storage/'.$photo->path) }}" title="{{$product->name}}" data-lightbox="gallery-item">
                                                    <img src="{{ asset('storage/'.$photo->path) }}" alt="{{$product->name}}" onerror="this.src='{{ asset('images/1667370521_download.jpg') }}'">
                                                </a>
                                            </div>
                                        @empty
                                            <a href="{{$product->PhotoPrimary}}" title="{{$product->name}}" data-lightbox="gallery-item">
                                                <img src="{{$product->PhotoPrimary}}" alt="{{$product->name}}" onerror="this.src='{{ asset('images/1667370521_download.jpg') }}'">
                                            </a>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Product Single - Gallery End -->

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

                    <div class="col-md-6 product-desc position-lg-sticky h-100">
                        <h1>{{$product->name}}</h1>

                        <div class="d-flex align-items-center justify-content-between">
                            <!-- Product Single - Price
                            ============================================= -->
                            <div class="product-price"><ins>{{ $product->inventory > 0 ? 'In Stock' : 'Out of Stock' }}</ins></div><!-- Product Single - Price End -->

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
                                                            <span class="comment-avatar clearfix">
                                                            <img alt='Image' src='https://0.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s=60' height='60' width='60' /></span>
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
                                                            <span class="comment-avatar clearfix">
                                                            <img alt='Image' src='https://0.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s=60' height='60' width='60' /></span>
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
        <div class="text-center pt-6 mb-5">
            <h2 class="h1 fw-normal mb-4 loren-title loren-title-white"><en>Related</en> Products</h2>
        </div>
        @endif

        <!-- Categories -->
        <div id="oc-portfolio" class="owl-carousel portfolio-carousel carousel-widget" data-pagi="false" data-items-xs="1" data-items-sm="2" data-items-md="3" data-items-lg="4">
            @foreach($relatedProducts as $product)
            <div class="portfolio-item">
                <div class="item-categories">
                    <div class="">
                        <a href="{{ route('product.front.show',$product->slug) }}" class="d-block h-op-09 op-ts" style="background: url('{{$product->photoPrimary}}') no-repeat center center; background-size: cover; height: 340px;">
                            <h5 class="text-uppercase ls1 bg-white mb-0">{{$product->name}}</h5>
                        </a>
                    </div>
                </div>
                <div class="product-desc">
                    <div class="product-title mb-0"><h4 class="mb-0"><a class="fw-medium" href="{{ route('product.front.show',$product->slug) }}">{{$product->name}}</a></h4></div>
                    <h5 class="product-price fw-normal">₱{{number_format($product->price,2)}}</h5>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('pagejs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
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
