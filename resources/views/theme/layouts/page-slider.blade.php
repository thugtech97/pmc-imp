<section id="slider" class="slick-carousel clearfix sub-banner">
    <div id="page-title" class="bg-transparent border-0">
        <div class="container clearfix">
            <div class="title-head">
                <h1 class="text-light">{{ $page->name }}</h1>
            </div>
            @if(isset($breadcrumb))
            <ol class="breadcrumb">
                @foreach($breadcrumb as $link => $url)
                    @if($loop->last)
                        <li class="breadcrumb-item active limiter" aria-current="page">{{$link}}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{$url}}">{{$link}}</a></li>
                    @endif
                @endforeach
            </ol>
            @endif
        </div>
    </div>
    
    
    <div class="slider-parallax-inner">
        <div class="swiper-container swiper-parent">
            <div class="slick-wrapper" id="banner">
                @foreach ($page->album->banners as $banner)
                <div class="swiper-slide dark" style="background-image: url('{{ $banner->image_path }}');"></div>
                
                {{--<div class="swiper-slide" style="background-image: url('images/banners/image2.jpg'); background-position: center top;"></div>--}}
                @endforeach
            </div>
        </div>
    </div>
</section>
