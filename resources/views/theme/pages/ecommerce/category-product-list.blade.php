@extends('theme.main')

@section('pagecss')
    <style>
        .pagination {
            margin-left: 110px;
            margin-top: 50px;
        }
    </style>
@endsection

@section('content')
<div class="content-wrap">
    <div class="container clearfix">
        <div id="shop" class="shop row grid-container gutter-30 has-init-isotope" data-layout="fitRows" style="position: relative; height: 1484.22px;">
            @foreach($products as $product)
                <div class="product col-lg-3 col-md-4 col-sm-6 col-12" style="position: absolute; left: 0%; top: 0px;">
                    <div class="grid-inner">
                        <!--<div class="item-categories">
                            <div class="">
                                <a href="{{ route('product.front.show',$product->slug) }}" class="d-block h-op-09 op-ts" style="background: url('{{$product->photoPrimary}}') no-repeat center center; background-size: cover; height: 340px;">
                                    <h5 class="text-uppercase ls1 bg-white mb-0">{{$product->name}}</h5>
                                    <h6 class="text-uppercase ls1 bg-white mb-0">Best Seller</h6>
                                </a>
                            </div>
                        </div>-->
                        <div class="product-image">
                            <a href="#"><img src="{{$product->photoPrimary}}" alt="Checked Short Dress"></a>
                            <a href="#"><img src="images/shop/dress/1-1.jpg" alt="Checked Short Dress"></a>
                            <div class="sale-flash badge bg-secondary p-2">
                                @php
                                    $stocks = rand(1, 10);
                                @endphp
                                @if (!$stocks)
                                    Out of Stock
                                @elseif ($stocks <= $product->critical_qty)
                                    Stocks Available: <span style="color: red">{{ $stocks }}</span>
                                @else
                                    Stocks Available: {{ $stocks }}
                                @endif
                            </div>
                            <div class="bg-overlay">
                                <div class="bg-overlay-content align-items-end justify-content-between animated fadeOut" data-hover-animate="fadeIn" data-hover-speed="400" style="animation-duration: 400ms;">
                                    <a href="javascript:;" onclick="add_to_cart('{{$product->id}}',1);" class="btn btn-dark me-2"><i class="icon-shopping-basket"></i></a>
                                    <a href="include/ajax/shop-item.html" class="btn btn-dark" data-lightbox="ajax"><i class="icon-line-expand"></i></a>
                                </div>
                                <div class="bg-overlay-bg bg-transparent"></div>
                            </div>
                        </div>
                        <div class="product-desc">
									<div class="product-title">
                                        <h6>
                                            <a href="{{ route('product.front.show', $product->slug) }}">{{ $product->name }}</a>
                                        </h6>
                                    </div>
									<!--<div class="product-price"><del>$24.99</del> <ins>$12.49</ins></div>-->
									<div class="product-rating">
                                        {{ round($product->reviews->avg('rating'), 0) }}
                                        <i class="icon-star3"></i>
									</div>
								</div>
                        <!--<div class="product-desc">
                            <div class="product-title mb-0"><h4 class="mb-0"><a class="fw-medium" href="{{ route('product.front.show',$product->slug) }}">{{$product->name}}</a></h4></div>
                            @if(Setting::is_promo_product($product->id) > 0)
                                <input type="hidden" id="product_price" value="{{ $product->discountedprice }}">
                                <h5 class="product-price fw-normal">₱{{number_format($product->discountedprice,2)}}</h5>
                            @else
                                <input type="hidden" id="product_price" value="{{ $product->price }}">
                                <h5 class="product-price fw-normal">₱{{number_format($product->price,2)}}</h5>
                            @endif
                        </div>-->
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    {{ $products->links('theme.layouts.pagination') }}
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