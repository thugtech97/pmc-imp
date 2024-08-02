@extends('theme.main')

@section('pagecss')
@endsection

@section('content')
<div class="content-wrap">
    <div class="container clearfix">
        @auth
            <div class="row">
                @if (session('announcements'))
                    @foreach (session('announcements') as $announcement)
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            {!! $announcement->content !!}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
        @endauth

        <div class="row gutter-40 col-mb-80">
            <!-- Post Content
            ============================================= -->
            <div class="postcontent col-lg-9 order-lg-last">

                <!-- Shop
                ============================================= -->
                <div id="shop" class="shop row grid-container gutter-20" data-layout="fitRows">
                    @foreach ($products as $product)
                        <div class="product col-md-4 col-sm-6 category{{ $product->category_id }}">
                            <div class="grid-inner">
                                <div class="product-image">
                                    <a href="#"><img src="{{ $product->getPhotoPrimaryAttribute() }}" width="262" height="262"></a>
                                    <a href="#"><img src="{{ $product->getPhotoPrimaryAttribute() }}" width="262" height="262"></a>

                                    <div class="sale-flash badge bg-secondary p-2">
                                        @php
                                            $stocks = rand(1, 10);
                                        @endphp
                                        @if (!$product->inventory)
                                            Out of Stock
                                        @elseif ($product->inventory <= $product->critical_qty)
                                            Stocks Available: <span style="color: red">{{ $product->inventory }}</span>
                                        @else
                                            Stocks Available: {{ $product->inventory }}
                                        @endif
                                    </div>
                                    <div class="bg-overlay">
                                        <div class="bg-overlay-content align-items-end justify-content-between" data-hover-animate="fadeIn" data-hover-speed="400">
                                            <a href="javascript:;" onclick="add_to_cart('{{$product->id}}',1);" class="btn btn-dark me-2"><i class="icon-shopping-cart"></i></a>
                                            <a href="include/ajax/shop-item.html" class="btn btn-dark" data-lightbox="ajax"><i class="icon-line-expand"></i></a>
                                        </div>
                                        <div class="bg-overlay-bg bg-transparent"></div>
                                    </div>
                                </div>
                                <div class="product-desc">
                                    <div class="product-title"><h3><a href="{{ route('product.front.show', $product->slug) }}">{{ $product->name }}</a></h3></div>
                                    <!--<div class="product-price"><ins>P{{ number_format($product->price, 2) }}</ins></div>-->
                                    <div class="product-rating">
                                        {{ round($product->reviews->avg('rating'), 0) }}
                                        <i class="icon-star3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div><!-- #shop end -->
                <span>
                    {{ $products->links() }}
                </span>
            </div><!-- .postcontent end -->

            <!-- Sidebar
            ============================================= -->
            <div class="sidebar col-lg-3">
                <div class="sidebar-widgets-wrap">

                    <div class="widget widget-filter-links">

                        <h4>Select Category</h4>
                        <ul class="custom-filter ps-2" data-container="#shop" data-active-class="active-filter">
                            <li class="widget-filter-reset active-filter"><a href="#" data-filter="*">Clear</a></li>
                            @foreach ($categories as $category)
                                <li><a href="#" data-filter=".category{{ $category->id }}">{{ $category->description }}</a></li>
                            @endforeach
                        </ul>

                    </div>

                    <div class="widget widget-filter-links">

                        <h4>Sort By</h4>
                        <ul class="shop-sorting ps-2">
                            <li class="widget-filter-reset active-filter"><a href="#" data-sort-by="original-order">Clear</a></li>
                            <li><a href="#" data-sort-by="name">Name</a></li>
                            <li><a href="#" data-sort-by="price_lh">Price: Low to High</a></li>
                            <li><a href="#" data-sort-by="price_hl">Price: High to Low</a></li>
                        </ul>

                    </div>

                </div>
            </div><!-- .sidebar end -->
        </div>
    </div>
</div>
@endsection

@section('pagejs')
<script>
    jQuery(document).ready( function($){
        $(window).on( 'pluginIsotopeReady', function(){
            $('#shop').isotope({
                transitionDuration: '0.65s',
                getSortData: {
                    name: '.product-title',
                    price_lh: function( itemElem ) {
                        if( $(itemElem).find('.product-price').find('ins').length > 0 ) {
                            var price = $(itemElem).find('.product-price ins').text();
                        } else {
                            var price = $(itemElem).find('.product-price').text();
                        }

                        priceNum = price.split("$");

                        return parseFloat( priceNum[1] );
                    },
                    price_hl: function( itemElem ) {
                        if( $(itemElem).find('.product-price').find('ins').length > 0 ) {
                            var price = $(itemElem).find('.product-price ins').text();
                        } else {
                            var price = $(itemElem).find('.product-price').text();
                        }

                        priceNum = price.split("$");

                        return parseFloat( priceNum[1] );
                    }
                },
                sortAscending: {
                    name: true,
                    price_lh: true,
                    price_hl: false
                }
            });

            $('.custom-filter:not(.no-count)').children('li:not(.widget-filter-reset)').each( function(){
                var element = $(this),
                    elementFilter = element.children('a').attr('data-filter'),
                    elementFilterContainer = element.parents('.custom-filter').attr('data-container');

                elementFilterCount = Number( jQuery(elementFilterContainer).find( elementFilter ).length );

                element.append('<span>'+ elementFilterCount +'</span>');

            });

            $('.shop-sorting li').click( function() {
                $('.shop-sorting').find('li').removeClass( 'active-filter' );
                $(this).addClass( 'active-filter' );
                var sortByValue = $(this).find('a').attr('data-sort-by');
                $('#shop').isotope({ sortBy: sortByValue });
                return false;
            });
        });
    });

    function add_to_cart(product,qty){
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
