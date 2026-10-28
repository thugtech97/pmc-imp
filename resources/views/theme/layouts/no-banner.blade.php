<!-- Page Title
============================================= -->
{{-- Orange system banner removed — the "IMF-MRS-PA (IMP) System" title now lives in the black top navbar (theme/main.blade.php). --}}

<style>
    #page-title.pmc-page-banner {
        background: #f4f5f1;
        padding: 20px 0;
        position: relative;
    }
    #page-title.pmc-page-banner::before {
        background: linear-gradient(135deg, rgba(66, 97, 91, .1), transparent 62%);
        content: '';
        inset: 0;
        pointer-events: none;
        position: absolute;
    }
    #page-title.pmc-page-banner .pmc-page-banner-card {
        align-items: center;
        background: #fff;
        border: 1px solid rgba(34, 46, 43, .1);
        border-radius: 14px;
        box-shadow: 0 12px 30px rgba(34, 46, 43, .08);
        display: flex;
        gap: 28px;
        justify-content: space-between;
        padding: 25px 30px;
        position: relative;
        z-index: 1;
    }
    #page-title.pmc-page-banner .pmc-page-banner-card::before {
        background: #ecd49a;
        border-radius: 2px;
        content: '';
        height: 42px;
        left: 0;
        position: absolute;
        top: 25px;
        width: 3px;
    }
    #page-title.pmc-page-banner .pmc-page-banner-main {
        min-width: 0;
    }
    #page-title.pmc-page-banner h2 {
        color: #222e2b;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: 0;
        line-height: 1.2;
        margin: 0 0 7px;
    }
    #page-title.pmc-page-banner .pmc-page-description {
        color: #71807a;
        display: block;
        font-size: 13px;
        line-height: 1.6;
    }
    #page-title.pmc-page-banner .breadcrumb {
        margin: 13px 0 0;
    }
    #page-title.pmc-page-banner .breadcrumb-item {
        color: #71807a;
        font-size: 11px;
    }
    #page-title.pmc-page-banner .breadcrumb-item a {
        color: #42615b;
        text-decoration: none;
    }
    #page-title.pmc-page-banner .breadcrumb-item a:hover {
        color: #222e2b;
    }
    #page-title.pmc-page-banner .pmc-page-note,
    .pmc-page-banner-aside-floating .pmc-page-note {
        background: #f6f8f6;
        border: 1px solid #e1e9e4;
        border-left: 3px solid #ecd49a;
        border-radius: 9px;
        color: #52615b;
        font-size: 12px;
        line-height: 1.6;
        margin: 0;
        max-width: 480px;
        padding: 13px 16px;
    }
    .pmc-page-banner-aside.pmc-page-banner-aside-floating {
        position: fixed;
        right: 24px;
        top: 88px;
        width: min(480px, calc(100vw - 32px));
        z-index: 1030;
    }
    .pmc-page-banner-aside-floating .pmc-page-note {
        box-shadow: 0 12px 30px rgba(34, 46, 43, .14);
        max-width: none;
        padding-right: 42px;
    }
    .pmc-page-banner-aside-floating .pmc-page-banner-close {
        align-items: center;
        background: #42615b;
        border: 0;
        border-radius: 50%;
        color: #fff;
        cursor: pointer;
        display: flex;
        font-size: 18px;
        height: 26px;
        justify-content: center;
        line-height: 1;
        position: absolute;
        right: 8px;
        top: 8px;
        width: 26px;
        z-index: 1;
    }
    .pmc-page-banner-aside-floating .pmc-page-banner-close:hover {
        background: #222e2b;
    }
    @media (max-width: 991.98px) {
        #page-title.pmc-page-banner .pmc-page-banner-card {
            align-items: stretch;
            flex-direction: column;
            gap: 18px;
        }
        #page-title.pmc-page-banner .pmc-page-note {
            max-width: none;
        }
    }
    @media (max-width: 575.98px) {
        #page-title.pmc-page-banner {
            padding: 18px 0;
        }
        #page-title.pmc-page-banner .pmc-page-banner-card {
            padding: 21px 22px;
        }
        #page-title.pmc-page-banner h2 {
            font-size: 22px;
        }
        .pmc-page-banner-aside.pmc-page-banner-aside-floating {
            right: 16px;
            top: 76px;
            width: calc(100vw - 32px);
        }
    }
</style>

@if(request()->routeIs('profile.sales'))
    <div class="pmc-page-banner-aside pmc-page-banner-aside-floating" role="status">
        <button type="button" class="pmc-page-banner-close" aria-label="Close reminder" onclick="this.closest('.pmc-page-banner-aside').hidden = true;">&times;</button>
        <div class="pmc-page-note">
            <strong>Note: The next person has three days to review or approve it before forwarding to the next level. Please be reminded to raise a request 1 to 2 months earlier before the Date Needed to avoid rush processing of your request. Thank you.</strong>
        </div>
    </div>
@elseif(!request()->routeIs('new-stock.index') && !request()->routeIs('new-stock.create') && !request()->routeIs('new-stock.show') && !request()->routeIs('new-stock.edit') && !request()->routeIs('cart.front.show') && !request()->routeIs('order.success'))
<section id="page-title" class="pmc-page-banner">

    <div class="container-fluid clearfix">
        <div class="pmc-page-banner-card">
            <div class="pmc-page-banner-main">
                <h2>{{ $page->name }}</h2>
                <span class="pmc-page-description">{{ $page->description }}</span>
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
    </div>

</section><!-- #page-title end -->
@endif
