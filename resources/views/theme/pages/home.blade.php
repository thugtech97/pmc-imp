@extends('theme.main')

@section('pagecss')
@endsection

@php
    $contents = $page->contents;

    

    $featuredProducts = \App\Models\Ecommerce\Product::where('is_featured', 1)->where('status', 'PUBLISHED')->get();
    if($featuredProducts->count()){

        $featuredProductsHTML = '<div class="container-fluid" style="background-color:#fff5f0;">
                <div class="container pb-5">
                    <div class="text-center pt-6 mb-5">
                        <h2 class="h1 fw-normal mb-4 loren-title loren-title-white"><en>Our</en> Products</h2>
                    </div>
                    <div id="oc-portfolio" class="owl-carousel portfolio-carousel carousel-widget" data-pagi="false" data-items-xs="1" data-items-sm="2" data-items-md="3" data-items-lg="4">';

                        foreach($featuredProducts as $key => $product) {
                            $featuredProductsHTML .= '
                            <div class="portfolio-item">
                                <div class=" item-categories">
                                    <div class="">
                                        <a href="'. route('product.front.show',$product->slug) .'" class="d-block h-op-09 op-ts" style="background: url('.$product->photoPrimary.') no-repeat center center; background-size: cover; height: 340px;">
                                            <h5 class="text-uppercase ls1 bg-white mb-0">'. $product->name .'</h5>
                                        </a>
                                    </div>
                                </div>
                                <div class="product-desc">
                                    <div class="product-title mb-0"><h4 class="mb-0"><a class="fw-medium" href="'. route('product.front.show',$product->slug) .'">'. $product->name .'</a></h4></div>
                                    <h5 class="product-price fw-normal">₱'. number_format($product->price,2) .'</h5>
                                </div>
                            </div>';
                        }
                        
                    $featuredProductsHTML .= '</div>
                    <p class="text-center"><br><br><a href="'. route('product.list') .'" class="button button-border m-0 button-white border-width-1 border-default h-bg-color bg-white rounded loren-shop-btn-orange">Shop Now</a></p>
                </div>
            </div>';

    } else {
        $featuredProductsHTML = '';
    }


    $featuredArticles = Article::where('is_featured', 1)->where('status', 'Published')->get();
    if($featuredArticles->count()) {

        $featuredArticlesHTML = '<section class="background-custom2 py-6">
                                    <div class="container mt-lg-6 pt-lg-6">
                                        <h2 class="text-secondary text-center pt-lg-6">Latest Articles</h2>
                                        <div class="gpd-divider" id="i5ix9"></div>
                                        <p class="text-center mb-5">Find out more what were up to at Philsaga</p>
                                        <div class="row">';

        $prefooter = asset('theme/images/pre-footer.jpg');

        foreach ($featuredArticles as $index => $article) {
            $imageUrl = (empty($article->thumbnail_url)) ? asset('theme/images/misc/no-image.jpg') : $article->thumbnail_url;

            
            $featuredArticlesHTML .= '<div class="col-lg-4 d-flex mb-5 mb-lg-0">
                                        <div class="card rounded-0 d-flex flex-fill">
                                            <img class="card-img-top rounded-0" src="'. $imageUrl .'" alt="Card image cap">
                                            <div class="card-body p-4">
                                                <div class="d-flex flex-column flex-md-row flex-lg-row justify-content-start">
                                                    <div class="d-flex align-items-center me-3">
                                                        <i class="icon-square fs-7px color-custom1 me-2"></i>
                                                        <h6 class="mb-0"><span>By: </span>'. $article->user->name .'</h6>
                                                    </div>
                                                    <div class="d-flex align-items-center me-3">
                                                        <i class="icon-square fs-7px color-custom1 me-2"></i>
                                                        <h6 class="mb-0"><span>On: </span>'. $article->date_posted() .'</h6>
                                                    </div>
                                                </div>
                                                <hr />
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="icon-leaf color me-3"></i>
                                                    <h6 class="mb-0 font-secondary fst-italic fw-normal text-secondary">'. $article->category->name .'</h6>
                                                </div>
                                                <h5 class="card-title mb-3">'. $article->name .'</h5>
                                                <p class="card-text fs-15px excerpt-4">'. $article->teaser .'</p>
                                                <a href="'. $article->get_url() .'" class="button button-small border-bottom text-capitalize font-primary fw-medium">Read More</a>
                                            </div>
                                        </div>
                                    </div>';

            if (Article::has_featured_limit() && $index >= env('FEATURED_NEWS_LIMIT')) {
                break;
            }
        }

        $featuredArticlesHTML .= '</div>
                <div class="d-flex justify-content-center mt-5">
                    <a href="'.route('news.front.index').'" class="button button-large button-custom1 button-circle text-capitalize font-primary fw-medium">View All Articles</a>
                </div>
            </div>
        </section>';

        $keywords   = ['{Featured Articles}', '{Featured Products}'];
        $variables  = [$featuredArticlesHTML, $featuredProductsHTML];

        $contents = str_replace($keywords,$variables,$contents);

    } else {

        $keywords   = ['{Featured Articles}', '{Featured Products}'];
        $variables  = ['', $featuredProductsHTML];

        $contents = str_replace($keywords,$variables,$contents);
    } 

    


@endphp

@section('content')
    {!! $contents !!}
@endsection

@section('pagejs')
@endsection

@section('customjs')
@endsection
