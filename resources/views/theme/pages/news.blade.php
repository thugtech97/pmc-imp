@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('theme/sysu/plugins/jssocials/jssocials.css') }}" />
    <link rel="stylesheet" href="{{ asset('theme/sysu/plugins/jssocials/jssocials-theme-flat.min.css') }}" />

    <style>
        {{ str_replace(array("'", "&#039;"), "", $news->styles ) }}
    </style>
@endsection

@section('content')
<div class="container content-wrap">
    <div class="row">
        <div class="col-lg-3">
            <span onclick="openNav()" class="d-lg-none mb-4 btn btn-primary btn-bg"><i class="icon-list-alt"></i></span>
            
            <div id="mySidenav">
                <a href="javascript:void(0)" class="closebtn d-lg-none" onclick="closeNav()">&times;</a>
              
                <div>
                    <h3 class="mb-4 loren-title-white text-uppercase fw-bold">Search</h3>
                </div>
                
                <form class="mb-0" id="frm_search">
                    <div class="input-group pb-5">
                        <input type="text" name="searchtxt" id="searchtxt" class="form-control" placeholder="Search news" aria-label="Search news" aria-describedby="button-addon2" />
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="button-addon2"><span class="icon-search"></span></button>
                        </div>
                    </div>
                </form>
            </div>
              
            <div>
                <h3 class="mb-4 loren-title-white text-uppercase fw-bold">Category</h3>
            </div>
            {!! $categories !!}
                
                
            <div>
                <h3 class="mb-4 loren-title-white text-uppercase fw-bold">Latest News</h3>
            </div>
            
            <div class="tab-content clearfix">
                <div id="popular-post-list-sidebar">
                    @foreach($latestArticles as $latest)
                        <div class="spost clearfix">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="entry-image">
                                        <a href="{{ route('news.front.show',$latest->slug) }}" class="nobg">
                                            @if($latest->thumbnail_url)
                                                <img class="rounded-circle" src="{{ $latest->thumbnail_url }}" alt="">
                                            @else
                                                <img class="rounded-circle" src="{{ asset('theme/images/news/no-image.jpg') }}" alt="">
                                            @endif
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="entry-c">
                                        <div class="entry-title">
                                            <h4><a href="{{ route('news.front.show',$latest->slug) }}">{{ $latest->name }}</a></h4>
                                        </div>
                                        <ul class="entry-meta">
                                            <li><i class="icon-calendar3"></i> {{ date('F d, Y',strtotime($latest->date)) }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div>
                <h2 class="mb-4 loren-title-white text-uppercase fw-bold">{{$news->name}}</h2>
            </div>
            <div class="entry clearfix">
                <ul class="entry-meta clearfix">
                    <li><i class="icon-calendar3"></i> {{ Setting::date_for_news_list($news->date) }}</li>
                </ul>
                @if (!empty($news->image_url))
                    <div class="entry-image">
                        <a href="#"><img src="{{$news->image_url}}" alt=""></a>
                    </div>
                @endif

                <div class="entry-content notopmargin">

                    {!! $news->contents !!}


                    <div class="clear"></div>

                    <div class="si-share noborder clearfix">
                        <span>Share this Post:</span>
                        <div class="share_link"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagejs')
<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(function() {
        $('#frm_search').on('submit', function(e) {
            e.preventDefault();
            window.location.href = "{{route('news.front.index')}}?type=searchbox&criteria="+$('#searchtxt').val();
        });
    });
</script>
@endsection
