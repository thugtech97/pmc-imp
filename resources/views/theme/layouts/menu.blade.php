
@php
    $menu = Menu::where('is_active', 1)->first();
@endphp

<ul class="menu-container">
    @foreach ($menu->parent_navigation() as $item)
        @include('theme.layouts.menu-item', ['item' => $item])
    @endforeach

    <!--<li class="menu-item mega-menu sub-menu">
        <a class="menu-link" href="#"><div>Warehouse</div></a>
        <div class="mega-menu-content mega-menu-style-2">
            <div class="container">
                <div class="row" style="padding: 30px 0">
                <h4>Categories</h4>
                @foreach(\App\Helpers\Setting::productCategories() as $category)
                    <ul class="sub-menu-container mega-menu-column col-lg-3" style="padding: 0 10px">

                            <li class="menu-item mega-menu-title sub-menu" style="">
                                <a class="menu-link" href="{{ route('product.front.list',$category->slug) }}"><div>{{ $category->description }}</div></a>
                            </li>

                    </ul>
                    @endforeach
                </div>
            </div>
        </div>
    </li>-->

    <li class="menu-item">
        <a class="menu-link" href="{{ route('catalogue.home') }}"><div>Catalogue</div></a>
    </li>

    <li class="menu-item">
        <a class="menu-link" href="{{ route('new-stock.index') }}"><div>IMF</div></a>
    </li>
</ul>
