<article data-animate="fadeInUp" class="portfolio-item col-lg-3 col-md-3 col-sm-3 col-3 col-mb-2 cat{{$c->id}}" style="position: absolute; left: 0%; top: 691.5px; ">
    <div class="grid-inner rounded bgwhite">
        
        <div class="portfolio-image">

            <div class="fslider" data-arrows="false" data-speed="650" data-pause="3500" data-animation="fade">
                <div class="flexslider">
                    <div class="slider-wrap">
                        <div class="slide gradent flex-active-slide" data-thumb-alt="" style="width: 100%; float: left; margin-right: -100%; position: relative; opacity: 0; display: block; z-index: 1;text-align: center;">
                            <a href="{{route('catalogue.category_products',$c->id)}}">
                                <img src="{{ asset($c->Photo) }}" alt="{{$c->name}}" draggable="false" style="height:200px;">
                            </a>
                        </div>                                                      
                    </div>
                </div>
            </div>
            
        </div>
        
        <div class="portfolio-desc center entry-content" style="margin-top:5px;padding:5px;">
            <h4><a href="{{route('catalogue.category_products',$c->id)}}" style="font-size:14px;line-height:1;">{{ucwords(strtolower($c->description))}}</a></h4>
            <span style="font-size:12px;"><a href="{{route('catalogue.category_products',$c->id)}}">View All</a></span>
        </div>
    </div>
</article>