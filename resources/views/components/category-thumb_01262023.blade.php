<article data-animate="fadeInUp" class="portfolio-item col-lg-2 col-md-2 col-sm-2 col-2 col-mb-2 cat{{$c->id}}" style="position: absolute; left: 0%; top: 691.5px; ">
    <div class="grid-inner rounded bgwhite">
        @if($c->image)
            <div class="portfolio-image">
            <div class="fslider" data-arrows="false" data-speed="650" data-pause="3500" data-animation="fade">
                    <div class="flexslider" style="height: 231.75px;">
                        <div class="slider-wrap">
                            <div class="slide gradent flex-active-slide" data-thumb-alt="" style="width: 100%; float: left; margin-right: -100%; position: relative; opacity: 0; display: block; z-index: 1;text-align: center;">
                                <a href="{{route('catalogue.category_products',$c->id)}}">
                                    <img src="{{ asset('storage/images/'.$c->image) }}" alt="{{$c->name}}" draggable="false">
                                </a>
                            </div>                                                      
                        </div>
                    </div>
                </div>
                <div class="bg-overlay" data-lightbox="gallery">
                    <div class="bg-overlay-content dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;">
                        <a href="{{ asset('storage/images/'.$c->image) }}" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="gallery-item" style="animation-duration: 350ms;"><i class="icon-line-stack-2"></i></a>
                        
                        <a href="{{route('catalogue.category_products',$c->id)}}" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line2-arrow-right"></i></a>
                    </div>
                    <div class="bg-overlay-bg dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
                </div>
            </div>
        @else
            <div class="portfolio-image">
                <div class="fslider" data-arrows="false" data-speed="650" data-pause="3500" data-animation="fade">
                    <div class="flexslider" style="height: 231.75px;">
                        <div class="slider-wrap">
                            
                            @forelse($c->products as $p)
                                <div class="slide gradent @if($loop->last) flex-active-slide @endif" data-thumb-alt="" style="width: 100%; float: left; margin-right: -100%; position: relative; opacity: 0; display: block; z-index: 1;text-align: center;"><a href="{{route('catalogue.category_products',$c->id)}}"><img src="{{ asset('storage/'.$p->photos[1]->path) }}" alt="{{$c->name}}" draggable="false"></a></div>
                            @empty
                            @endforelse
                            
                        </div>
                        <ol class="flex-control-nav flex-control-paging">
                            @for($x=1;$x<=$c->products->count();$x++)
                                <li><a href="#" class="@if($c->products->count() == $x) flex-active @endif">{{$x}}</a></li>
                            @endfor
                        </ol>
                    </div>
                </div>
                <div class="bg-overlay" data-lightbox="gallery">
                    <div class="bg-overlay-content dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;">

                        @forelse($c->products as $p)
                            @if($loop->first)
                                <a href="{{ asset('storage/'.$p->photos[0]->path) }}" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" data-lightbox="gallery-item" style="animation-duration: 350ms;"><i class="icon-line-stack-2"></i></a>
                            @else
                                <a href="{{ asset('storage/'.$p->photos[0]->path) }}" class="d-none" data-lightbox="gallery-item"></a>
                            @endif
                        @empty
                        @endforelse
                        
                        <a href="{{route('catalogue.category_products',$c->id)}}" class="overlay-trigger-icon bg-light text-dark animated fadeOutUpSmall" data-hover-animate="fadeInDownSmall" data-hover-animate-out="fadeOutUpSmall" data-hover-speed="350" style="animation-duration: 350ms;"><i class="icon-line2-arrow-right"></i></a>
                    </div>
                    <div class="bg-overlay-bg dark animated fadeOut" data-hover-animate="fadeIn" style="animation-duration: 600ms;"></div>
                </div>
            </div>
        @endif
        <div class="portfolio-desc center entry-content">
            <h3><a href="{{route('catalogue.category_products',$c->id)}}" style="font-size:14px;">{{$c->description}}</a></h3>
            <span style="font-size:12px;"><a href="{{route('catalogue.category_products',$c->id)}}">View All</a></span>
        </div>
    </div>
</article>