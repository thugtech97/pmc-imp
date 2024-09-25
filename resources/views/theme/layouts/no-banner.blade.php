<!-- Page Title
============================================= -->
@if (auth()->check())
<section class="py-4 py-lg-5 bg-color position-relative">
    <div class="container-fluid">
        <div class="mb-0">
            <div class="before-heading font-secondary text-dark fw-semibold fs-12-f">Materials Control Department</div>
            <h1 class="text-dark mt-2 fs-30 fs-lg-40 mb-0 nols lh-sm">IMF-MRS-PA (IMP) System</h1>
        </div>
    </div>
</section>
@endif

<section id="page-title" class="py-4 py-lg-5">

    <div class="container-fluid clearfix">
        <div class="row">
            <div class="col-lg-6">
                <h2 class="mb-2 mb-lg-0 fw-bold fs-24 fs-lg-28">{{ $page->name }}</h2>
                <span>{{ $page->description }}</span>
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
            <div class="col-lg-6">
                @if(request()->routeIs('profile.sales'))
                    <div class="alert alert-info">
                        <strong>Note: The next person has three days to review or approve it before forwarding to the next level. Please be reminded to raise a request 1 to 2 months earlier before the Date Needed to avoid rush processing of your request. Thank you.</strong>
                    </div>
                @endif
            </div>                   
        </div>
    </div>

</section><!-- #page-title end -->