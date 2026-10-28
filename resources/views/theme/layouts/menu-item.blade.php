@php $page = $item->page; @endphp
@if (!empty($page) && $item->is_page_type() && $page->is_published())
    <li class="menu-item mega-menu mega-menu-full @if(url()->current() == $page->get_url() || ($page->id == 1 && url()->current() == env('APP_URL'))) current @endif">
        <a href="{{$page->get_url()}}" class="menu-link">
            <div>
                @if (!empty($page->label))
                    {{ $page->label }} 
                @else
                    {{ $page->name }} 
                @endif
            </div>
        </a>
        <div class="mega-menu-content border-bottom-0">
            <div class="container">
                <div class="row">
                    @if ($item->has_sub_menus())
                        <ul class="sub-menu-container mega-menu-column col-lg-auto">
                            @foreach ($item->sub_pages as $subItem)
                                @include('theme.layouts.menu-item', ['item' => $subItem])
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </li>
@elseif ($item->is_external_type())
    <li class="menu-item mega-menu mega-menu-full" >
        <a href="{{ $item->uri }}" class="menu-link" target="{{ $item->target }}"><div>{{ $item->label }}</div></a>
        <div class="mega-menu-content border-bottom-0">
            <div class="container">
                <div class="row">
                    @if ($item->has_sub_menus())
                        <ul class="sub-menu-container">
                            @foreach ($item->sub_pages as $subItem)
                                @include('theme.layouts.menu-item', ['item' => $subItem])
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </li>
@endif