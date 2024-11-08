<ul class="nav nav-aside">
    <li class="nav-item">
        <a href="{{route('home')}}" target="_blank" class="nav-link">
            <i data-feather="external-link"></i>
            <span>View Website</span>
        </a>
    </li>

    @if (auth()->user()->has_access_to_module('announcements'))
        <li class="nav-item @if (request()->routeIs('sales-transaction*') || \Route::current()->getName() == 'bank-deposits') active show @endif">
            <a href="{{ route('announcements.index') }}" class="nav-link"><i data-feather="volume-2"></i> <span>Announcements</span></a>
        </li>
    @endif

    @if (auth()->user()->role_id == 1)
        <li class="nav-label mg-t-25">CMS</li>
        <li class="nav-item @if (url()->current() == route('dashboard')) active @endif">
            <a href="{{ route('dashboard') }}" class="nav-link"><i data-feather="home"></i><span>Dashboard</span></a>
        </li>
        
        @if (auth()->user()->has_access_to_pages_module())
            <li class="nav-item with-sub @if (request()->routeIs('pages*')) active show @endif">
                <a href="" class="nav-link"><i data-feather="layers"></i> <span>Pages</span></a>
                <ul>
                    <li @if (\Route::current()->getName() == 'pages.edit' || \Route::current()->getName() == 'pages.index' || \Route::current()->getName() == 'pages.index.advance-search') class="active" @endif><a href="{{ route('pages.index') }}">Manage Pages</a></li>
                    @if(auth()->user()->has_access_to_route('pages.create'))
                    <li @if (\Route::current()->getName() == 'pages.create') class="active" @endif><a href="{{ route('pages.create') }}">Create a Page</a></li>
                    @endif

                </ul>
            </li>
        @endif

        @if (auth()->user()->has_access_to_albums_module())
            <li class="nav-item with-sub @if (request()->routeIs('albums*')) active show @endif">
                <a href="#" class="nav-link"><i data-feather="image"></i> <span>Banners</span></a>
                <ul>
                    <!--<li @if (url()->current() == route('albums.edit', 1)) class="active" @endif><a href="{{ route('albums.edit', 1) }}">Manage Home Banner</a></li>-->
                    <li @if (\Route::current()->getName() == 'albums.index' || (\Route::current()->getName() == 'albums.edit' && url()->current() != route('albums.edit', 1))) class="active" @endif><a href="{{ route('albums.index') }}">Manage Subpage Banners</a></li>
                    @if(auth()->user()->has_access_to_route('albums.create'))
                        <li @if (\Route::current()->getName() == 'albums.create') class="active" @endif><a href="{{ route('albums.create') }}">Create an Album</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if (auth()->user()->has_access_to_file_manager_module())
            <li class="nav-item @if (\Route::current()->getName() == 'file-manager.index') active @endif">
                <a href="{{ route('file-manager.index') }}" class="nav-link"><i data-feather="folder"></i> <span>Files</span></a>
            </li>
        @endif

        {{--@if (auth()->user()->has_access_to_menu_module())
            <li class="nav-item with-sub @if (request()->routeIs('menus*')) active show @endif">
                <a href="" class="nav-link"><i data-feather="menu"></i> <span>Menu</span></a>
                <ul>
                    <li @if (\Route::current()->getName() == 'menus.edit' || \Route::current()->getName() == 'menus.index') class="active" @endif><a href="{{ route('menus.index') }}">Manage Menu</a></li>
                    <li @if (\Route::current()->getName() == 'menus.create') class="active" @endif><a href="{{ route('menus.create') }}">Create a Menu</a></li>
                </ul>
            </li>
        @endif--}}
        @if (auth()->user()->has_access_to_news_module() || auth()->user()->has_access_to_news_categories_module())
            <li class="nav-item with-sub @if (request()->routeIs('news*') || request()->routeIs('news-categories*')) active show @endif">
                <a href="" class="nav-link"><i data-feather="edit"></i> <span>News</span></a>
                <ul>
                    @if (auth()->user()->has_access_to_news_module())
                        <li @if (\Route::current()->getName() == 'news.index' || \Route::current()->getName() == 'news.edit'  || \Route::current()->getName() == 'news.index.advance-search') class="active" @endif><a href="{{ route('news.index') }}">Manage News</a></li>
                        <li @if (\Route::current()->getName() == 'news.create') class="active" @endif><a href="{{ route('news.create') }}">Create a News</a></li>
                    @endif
                    @if (auth()->user()->has_access_to_news_categories_module())
                        <li @if (\Route::current()->getName() == 'news-categories.index' || \Route::current()->getName() == 'news-categories.edit') class="active" @endif><a href="{{ route('news-categories.index') }}">Manage Categories</a></li>
                        <li @if (\Route::current()->getName() == 'news-categories.create') class="active" @endif><a href="{{ route('news-categories.create') }}">Create a Category</a></li>
                    @endif
                </ul>
            </li> 
        @endif
        

        <li class="nav-item with-sub @if (request()->routeIs('account*') || request()->routeIs('website-settings*') || request()->routeIs('audit*')) active show @endif">
            <a href="" class="nav-link"><i data-feather="settings"></i> <span>Settings</span></a>
            <ul>
                <li @if (\Route::current()->getName() == 'account.edit') class="active" @endif><a href="{{ route('account.edit') }}">Account Settings</a></li>

                @if (auth()->user()->has_access_to_website_settings_module())
                    <li @if (\Route::current()->getName() == 'website-settings.edit') class="active" @endif><a href="{{ route('website-settings.edit') }}">Website Settings</a></li>
                @endif

                @if (auth()->user()->has_access_to_audit_logs_module())
                    <li @if (\Route::current()->getName() == 'audit-logs.index') class="active" @endif><a href="{{ route('audit-logs.index') }}">Audit Trail</a></li>
                @endif
            </ul>
        </li>
        @if (auth()->user()->is_an_admin())
            <li class="nav-item with-sub @if (request()->routeIs('users*')) active show @endif">
                <a href="" class="nav-link"><i data-feather="users"></i> <span>Users</span></a>
                <ul>
                    <li @if (\Route::current()->getName() == 'users.index' || \Route::current()->getName() == 'users.edit') class="active" @endif><a href="{{ route('users.index') }}">Manage Users</a></li>
                    <li @if (\Route::current()->getName() == 'users.create') class="active" @endif><a href="{{ route('users.create') }}">Create a User</a></li>
                </ul>
            </li>
        @endif
        @if (auth()->user()->is_an_admin())
            <li class="nav-item with-sub @if (request()->routeIs('role*') || request()->routeIs('access*') || request()->routeIs('permission*')) active show @endif">
                <a href="" class="nav-link"><i data-feather="user"></i> <span>Account Management</span></a>
                <ul>
                    <li @if (request()->routeIs('role*')) class="active" @endif><a href="{{ route('role.index') }}">Roles</a></li>
                    <li @if (request()->routeIs('access*')) class="active" @endif><a href="{{ route('access.index') }}">Access Rights</a></li>
                    @if (env('APP_DEBUG') == "false")
                        <li @if (request()->routeIs('permission*')) class="active" @endif><a href="{{ route('permission.index') }}">Permissions</a></li>
                    @endif
                </ul>
            </li>
        @endif
    @endif

    <!-- MCD USER SECTION !-->
    <?php /*@if (auth()->user()->has_access_to_module('customer') || auth()->user()->has_access_to_module('sales_transaction') || auth()->user()->has_access_to_module('products') || auth()->user()->has_access_to_module('product_category') || auth()->user()->has_access_to_module('inventory') || auth()->user()->has_access_to_module('promos') || auth()->user()->has_access_to_module('delivery_flat_rate') || auth()->user()->has_access_to_module('coupons'))*/ ?>
    @if (auth()->user()->role_id == 8 || auth()->user()->role_id == 4 || auth()->user()->role_id == 7 || auth()->user()->role_id == 1)
        <li class="nav-label mg-t-25">MCD</li>
    @endif

    <?php /*@if (auth()->user()->has_access_to_module('customer'))*/ ?>
    @if (auth()->user()->role_id == 4 || auth()->user()->role_id == 7 || auth()->user()->role_id == 1)
        <li class="nav-item with-sub @if (request()->routeIs('customers*') || \Route::current()->getName() == 'customer.signup-verification')) active show @endif">
            <a href="#" class="nav-link"><i data-feather="users"></i> <span>Customers</span></a>
            <ul>
                <li @if (\Route::current()->getName() == 'customers.index') class="active" @endif><a href="{{ route('customers.index') }}">Manage Customers</a></li>
            </ul>
        </li>
    @endif

    <?php /*@if (auth()->user()->has_access_to_module('sales_transaction')) */ ?>
    @if (auth()->user()->role_id == 8 || auth()->user()->role_id == 4 || auth()->user()->role_id == 7 || auth()->user()->role_id == 1)
        <li class="nav-item with-sub @if (request()->routeIs('sales-transaction*') || (\Route::current()->getName() == 'imf.requests') || (\Route::current()->getName() == 'imf.requests.view') || (\Route::current()->getName() == 'planner_pa.index') || \Route::current()->getName() == 'planner_pa.create' || \Route::current()->getName() == 'planner_pa.edit') active show @endif">
            <a href="" class="nav-link"><i data-feather="users"></i> <span>Transactions</span></a>
            <ul>
                <li @if (\Route::current()->getName() == 'imf.requests') class="active" @endif><a href="{{ route('imf.requests') }}">Manage IMF Requests</a></li>
                <li @if (request()->routeIs('sales-transaction*')) class="active" @endif><a href="{{ route('sales-transaction.index') }}">Manage MRS Requests</a></li>
                <li @if ( \Route::current()->getName() == 'planner_pa.index' || \Route::current()->getName() == 'planner_pa.create' || \Route::current()->getName() == 'planner_pa.edit') class="active" @endif><a href="{{ route('planner_pa.index') }}">Manage Purchase Advice</a></li>
                
                {{-- <li @if (\Route::current()->getName() == 'sales-transaction.issuance.index') class="active" @endif><a href="{{ route('sales-transaction.issuance.index') }}">Manage Issuances</a></li>  --}}
            </ul>
        </li>
    @endif

    <?php /*@if(auth()->user()->has_access_to_module('products') || auth()->user()->has_access_to_module('product_category'))*/ ?>
    @if (auth()->user()->role_id == 4 || auth()->user()->role_id == 1)
        <li class="nav-item with-sub @if (request()->routeIs('products*') || request()->routeIs('product-categories*')) active show @endif">
            <a href="" class="nav-link"><i data-feather="box"></i> <span>Stocks</span></a>
            <ul>
                <li @if (\Route::current()->getName() == 'products.index' || \Route::current()->getName() == 'products.edit') class="active" @endif><a href="{{ route('products.index') }}">Manage Stocks</a></li>
                <li @if (\Route::current()->getName() == 'products.create') class="active" @endif><a href="{{ route('products.create') }}">Creates Stock</a></li>
                    
                <li @if (\Route::current()->getName() == 'product-categories.index' || \Route::current()->getName() == 'product-categories.edit') class="active" @endif><a href="{{ route('product-categories.index') }}">Manage Categories</a></li>
                <li @if (\Route::current()->getName() == 'product-categories.create') class="active" @endif><a href="{{ route('product-categories.create') }}">Create a Category</a></li>

                <li @if (\Route::current()->getName() == 'product-inventory-history.list') class="active" @endif><a href="{{ route('product-inventory-history.list') }}">Inventory History Logs</a></li>

                {{--<li @if (\Route::current()->getName() == 'product-favorite.list') class="active" @endif><a href="{{ route('product-favorite.list') }}">Favorites</a></li>--}}

                {{--<li @if (\Route::current()->getName() == 'product-wishlist.list') class="active" @endif><a href="{{ route('product-wishlist.list') }}">Wishlist</a></li>--}}
            </ul>
        </li>
    @endif


    <?php /*@if (auth()->user()->has_access_to_module('customer') || auth()->user()->has_access_to_module('sales_transaction') || auth()->user()->has_access_to_module('products') || auth()->user()->has_access_to_module('product_category') || auth()->user()->has_access_to_module('inventory') || auth()->user()->has_access_to_module('promos') || auth()->user()->has_access_to_module('delivery_flat_rate') || auth()->user()->has_access_to_module('coupons'))*/ ?>
    @if (auth()->user()->role_id == 5 || auth()->user()->role_id == 1)
        <li class="nav-label mg-t-25">Purchasing Section</li>
    @endif

    <?php /*@if (auth()->user()->has_access_to_module('sales_transaction')) */ ?>
    @if (auth()->user()->role_id == 5 || auth()->user()->role_id == 1)
        <li class="nav-item with-sub @if (request()->routeIs('sales-transaction*') || \Route::current()->getName() == 'bank-deposits') active show @endif">
            <a href="" class="nav-link"><i data-feather="users"></i> <span>Purchase Advice</span></a>
            <ul>
                <li @if (\Route::current()->getName() == 'pa.index') class="active" @endif><a href="{{ route('pa.index') }}">For PA</a></li>
                <li @if (\Route::current()->getName() == 'pa.manage') class="active" @endif><a href="{{ route('pa.manage') }}">Manage PA</a></li>
            </ul>
        </li>
    @endif
    @if (auth()->user()->role_id == 9 || auth()->user()->role_id == 1)
        <li class="nav-item with-sub @if (request()->routeIs('sales-transaction*') || \Route::current()->getName() == 'bank-deposits') active show @endif">
            <a href="" class="nav-link"><i data-feather="users"></i> <span>Assigned MRS</span></a>
            <ul>
                <li @if (\Route::current()->getName() == 'purchaser.index') class="active" @endif><a href="{{ route('purchaser.index') }}">For Receive</a></li>
            </ul>
        </li>
    @endif

{{-- 

    <li class="nav-label mg-t-25">Reports</li>
    <?php /*@if(auth()->user()->has_access_to_route('report.sales-transaction')) */ ?>
    <!--<li class="nav-item @if (\Route::current()->getName() == 'report.sales-transaction')) active show @endif">
        <a href="{{ route('report.sales-transaction') }}" class="nav-link" target="_blank"><i data-feather="file"></i> <span>MRS Request</span></a>
    </li>-->
    <li class="nav-item @if (\Route::current()->getName() == 'report.sales-transaction')) active show @endif">
        <a href="{{ route('admin.report.issuances') }}" class="nav-link" target="_blank"><i data-feather="file"></i> <span>Issuances</span></a>
    </li>

    <li class="nav-item @if (\Route::current()->getName() == 'report.sales-transaction')) active show @endif">
        <a href="{{ route('admin.report.mrs') }}" class="nav-link" target="_blank"><i data-feather="file"></i> <span>MRS</span></a>
    </li>

    <li class="nav-item @if (\Route::current()->getName() == 'report.sales-transaction')) active show @endif">
        <a href="{{ route('admin.report.fast-moving-items') }}" class="nav-link" target="_blank"><i data-feather="file"></i> <span>Fast Moving Items</span></a>
    </li>

 --}}

</ul>
