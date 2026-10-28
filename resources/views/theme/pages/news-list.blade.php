@extends('theme.main')

@section('pagecss')
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
                
                <div>
                    <h3 class="mb-4 loren-title-white text-uppercase fw-bold">Category</h3>
                </div>
                {!! $categories !!}

                <div>
                    <h3 class="mb-4 loren-title-white text-uppercase fw-bold">Archives</h3>
                </div>
                {!! $dates !!}
            </div>
        </div>
        
        <div class="col-lg-9">
            
            <div class="row">
                @if(isset($_GET['type']))
                    @if($_GET['type'] == 'searchbox')
                        <div class="col-12">
                            @if($totalSearchedArticle > 0)
                                <div class="style-msg successmsg">
                                    <div class="sb-msg"><i class="icon-thumbs-up"></i><strong>Woo hoo!</strong> We found <strong>(<span>{{ $totalSearchedArticle }}</span>)</strong> matching results.</div>
                                </div>
                            @else
                                <div class="style-msg2 errormsg">
                                    <div class="msgtitle p-0 border-0">
                                        <div class="sb-msg">
                                            <i class="icon-thumbs-up"></i><strong>Uh oh</strong>! <span><strong>{{ app('request')->input('criteria') }}</strong></span> you say? Sorry, no results!
                                        </div>
                                    </div>
                                    <div class="sb-msg">
                                        <ul>
                                            <li>Check the spelling of your keywords.</li>
                                            <li>Try using fewer, different or more general keywords.</li>
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                @forelse($articles as $article) 
                    <div class="col-md-4">
                        <div class="entry clearfix">
                            <div class="entry-image">
                                <a href="{{ route('news.front.show',$article->slug) }}">
                                    @if($article->thumbnail_url)
                                        <img class="image_fade" src="{{ $article->thumbnail_url }}" alt="{{ $article->name }}">
                                    @else
                                        <img class="image_fade" src="{{ asset('storage/news_image/news_thumbnail/No_Image_Available.png')}}" alt="{{ $article->name }}">
                                    @endif
                                </a>
                            </div>
                            <div class="entry-title">
                                <h2><a href="{{ route('news.front.show',$article->slug) }}">{{ $article->name }}</a></h2>
                            </div>
                            <ul class="entry-meta clearfix pb-3">
                                <li><i class="icon-calendar3"></i> {{ Setting::date_for_news_list($article->date) }}</li>
                            </ul>
                            <div class="entry-content">
                                <p>{{ $article->teaser }}</p>
                                <a href="{{ route('news.front.show',$article->slug) }}"class="more-link">Read More</a>
                            </div>
                        </div>
                    </div>
                @empty

                @endforelse
            </div>
                
            {{ $articles->links('theme.layouts.pagination') }}
        </div>
    </div>
</div>
@endsection

@section('pagejs')
    <script>
        $('#frm_search').on('submit', function(e) {
            e.preventDefault();
            console.log('sasasa');
            window.location.href = "{{route('news.front.index')}}?type=searchbox&criteria="+$('#searchtxt').val();
        });
    </script>
@endsection
