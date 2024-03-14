<!-- Page Title
============================================= -->
<section class="py-4 py-lg-5 bg-color position-relative">
    <div class="container-fluid">
        <div class="mb-0">
            <div class="before-heading font-secondary text-dark fw-semibold fs-12-f">Materials Control Department</div>
            <h1 class="text-dark mt-2 fs-30 fs-lg-40 mb-0 nols lh-sm">IMF-MRS-PA (IMP) System</h1>
        </div>
    </div>
</section>

<section id="page-title" class="py-4 py-lg-5">

    <div class="container-fluid clearfix">
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

</section><!-- #page-title end -->