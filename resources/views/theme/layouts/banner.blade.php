@if(isset($page) && $page->album && count($page->album->banners) > 0 && $page->album->is_main_banner())
    @include('theme.layouts.home-slider')
@elseif(isset($page) && $page->album && count($page->album->banners) > 1 && !$page->album->is_main_banner())
    @include('theme.layouts.page-slider')
@elseif(isset($page) && (isset($page->album->banners) && (count($page->album->banners) == 1 && !$page->album->is_main_banner()) || !empty($page->image_url)))
    @include('theme.layouts.page-banner')
@else
    @include('theme.layouts.no-banner')
@endif
