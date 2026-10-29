@extends('catalogue.layouts.main')

@section('pagecss')
    <link rel="stylesheet" type="text/css" href="{{ asset('theme/catalogue/css/settings.css') }}" media="screen" />
    <link rel="stylesheet" type="text/css" href="{{ asset('theme/catalogue/css/layers.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('theme/catalogue/css/navigation.css') }}">

    <style>
        .catalogue-search-results {
            background: #fff;
            border: 1px solid #e3ebe7;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(34, 46, 43, .06);
            margin: 0 0 24px;
            overflow: hidden;
        }

        .catalogue-search-results-header {
            align-items: center;
            background: #f5f8f6;
            border-bottom: 1px solid #e3ebe7;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 20px;
        }

        .catalogue-search-results-header h3 {
            color: #243632;
            font-size: 17px;
            font-weight: 700;
            margin: 0;
        }

        .catalogue-search-results-header p {
            color: #71807a;
            font-size: 12px;
            margin: 3px 0 0;
        }

        .catalogue-search-results-count {
            background: #42615b;
            border-radius: 999px;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 10px;
            white-space: nowrap;
        }

        .catalogue-search-results-wrap {
            overflow-x: auto;
        }

        .catalogue-search-results-table {
            border-collapse: collapse;
            min-width: 620px;
            width: 100%;
        }

        .catalogue-search-results-table th {
            background: #fff;
            color: #71807a;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .7px;
            padding: 12px 20px;
            text-align: left;
            text-transform: uppercase;
        }

        .catalogue-search-results-table td {
            border-top: 1px solid #edf2ef;
            color: #34443f;
            font-size: 13px;
            padding: 13px 20px;
            vertical-align: middle;
        }

        .catalogue-search-results-table tr:hover td {
            background: #f8fbf9;
        }

        .catalogue-search-result-name {
            color: #243632;
            font-weight: 600;
            text-decoration: none;
        }

        .catalogue-search-result-name:hover {
            color: #42615b;
        }

        .catalogue-search-result-code {
            background: #eef4f1;
            border: 1px solid #dce8e2;
            border-radius: 6px;
            color: #42615b;
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            white-space: nowrap;
        }

        .catalogue-search-result-qty {
            border: 1px solid #dce6e1;
            border-radius: 7px;
            min-width: 58px;
            padding: 7px 8px;
            text-align: center;
        }

        .catalogue-search-result-action {
            align-items: center;
            background: #42615b;
            border: 1px solid #42615b;
            border-radius: 7px;
            color: #fff;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            gap: 6px;
            justify-content: center;
            padding: 8px 12px;
            transition: background-color .15s ease, transform .15s ease;
            white-space: nowrap;
        }

        .catalogue-search-result-action:hover {
            background: #203f3a;
            border-color: #203f3a;
            color: #fff;
            transform: translateY(-1px);
        }

        .catalogue-search-empty {
            color: #71807a;
            font-size: 14px;
            padding: 26px 20px;
            text-align: center;
        }

        @media (max-width: 767.98px) {
            .catalogue-search-results-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('content')
    <br />
    <section class="hero-banner mx-4">
        <div class="container-fluid px-4 px-lg-5 hero-intro">
            <div class="before-heading">Materials Control Department</div>
            <h1>IMF-MRS-PA (IMP) System</h1>
        </div>

        <div class="hero-metrics">
            <div class="metric-card">
                <small>Catalog</small>
                <strong>{{ count($categories ?? []) }} categories</strong>
            </div>
            <div class="metric-card">
                <small>Cart Items</small>
                <strong><span class="hero-cart-total">{{ Setting::EcommerceCartTotalItems() }}</span> items</strong>
            </div>
            <div class="metric-card">
                <small>Previous Requests</small>
                <strong>{{ count($previously_ordered ?? []) }} history</strong>
            </div>
        </div>
    </section>

    <section id="content" class="py-4">
        <div class="container-fluid px-4 px-lg-5">
            @if(!$isAuthenticated)
                <input type="hidden" id="isAuthenticated" value="false">
                <div class="auth-banner d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="icon-lock me-2"></i>
                        <span><strong>Authentication Required:</strong> Access is limited to viewing only. Please sign in to request items.</span>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{route('catalogue.home')}}" class="text-decoration-none">Catalogue</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Home</li>
                        </ol>
                    </nav>
                </div>
            @endif

            <div class="catalogue-layout">
                <div class="catalogue-main">
                    <div class="catalogue-heading-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-3">
                        <h2 class="h3 fw-bold mb-0 text-dark">Product Catalogue</h2>

                        <div class="catalogue-heading-actions">
                            <form action="{{ route('catalogue.home') }}" method="GET" role="search" class="catalogue-search-form m-0">
                                <div class="input-group search-input-group">
                                    <input
                                        type="text"
                                        name="query"
                                        class="form-control"
                                        placeholder="Search by name, description, or code..."
                                        aria-label="Search Products"
                                        value="{{ $searchQuery ?? '' }}"
                                        style="border-top-left-radius: 16px; border-bottom-left-radius: 16px"
                                    >
                                    @if (($selectedCategoryId ?? 0) > 0)
                                        <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                                    @endif
                                    <button class="btn btn-search" type="submit" id="button-addon2">
                                        <i class="icon-search"></i>
                                    </button>
                                </div>
                            </form>

                            <details class="category-dropdown">
                                <summary>
                                    <i class="icon-grid"></i>
                                    <span>{{ $selectedCategory ? ucwords(strtolower($selectedCategory->description ?? $selectedCategory->name ?? 'Category')) : 'Browse by Category' }}</span>
                                    <i class="icon-chevron-down category-dropdown-chevron"></i>
                                </summary>
                                <div class="category-dropdown-menu">
                                    <a href="{{ route('catalogue.home') }}">All Categories</a>
                                    @forelse($categories as $category)
                                        <a href="{{ route('catalogue.home', ['category_id' => $category->id]) }}">
                                            {{ ucwords(strtolower($category->description ?? $category->name ?? 'Category')) }}
                                        </a>
                                    @empty
                                        <span class="category-dropdown-empty">No categories found.</span>
                                    @endforelse
                                </div>
                            </details>
                        </div>
                    </div>

                    @if (($searchQuery ?? '') !== '' || (($selectedCategoryId ?? 0) > 0))
                        <section class="catalogue-search-results" aria-labelledby="catalogue-search-results-title">
                            <div class="catalogue-search-results-header">
                                <div>
                                    <h3 id="catalogue-search-results-title">
                                        {{ ($selectedCategory ?? null) ? 'Category results' : 'Search results' }}
                                    </h3>
                                    <p>
                                        @if (($selectedCategory ?? null) && ($searchQuery ?? '') !== '')
                                            Showing “{{ $searchQuery }}” in {{ $selectedCategory->description ?? $selectedCategory->name }}
                                        @elseif (($selectedCategory ?? null))
                                            Showing items in {{ $selectedCategory->description ?? $selectedCategory->name }}
                                        @else
                                            Showing items matching “{{ $searchQuery }}”
                                        @endif
                                    </p>
                                </div>
                                <span class="catalogue-search-results-count">{{ $searchResults->count() }} {{ $searchResults->count() === 1 ? 'item' : 'items' }}</span>
                            </div>

                            @if ($searchResults->isNotEmpty())
                                <div class="catalogue-search-results-wrap">
                                    <table class="catalogue-search-results-table">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Code</th>
                                                <th>Quantity</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($searchResults as $result)
                                                <tr>
                                                    <td>
                                                        <a class="catalogue-search-result-name" href="{{ route('product.front.show', $result->slug) }}">
                                                            {{ ucwords(strtolower(str_replace(',', ', ', $result->name))) }}
                                                        </a>
                                                    </td>
                                                    <td><span class="catalogue-search-result-code">{{ $result->code }}</span></td>
                                                    <td>
                                                        <input type="number" min="1" step="1" value="1" class="catalogue-search-result-qty" id="quantity_search_{{ $result->id }}" aria-label="Quantity for {{ $result->name }}">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="catalogue-search-result-action" onclick="add_to_cart('{{ $result->id }}', $('#quantity_search_{{ $result->id }}').val());">
                                                            <i class="icon-shopping-cart" aria-hidden="true"></i> Add to Cart
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="catalogue-search-empty">No items found. Try another name, description, or code.</div>
                            @endif
                        </section>
                    @endif

                    <div class="catalogue-tabs" role="tablist" aria-label="Catalogue item lists">
                        <button type="button" class="catalogue-tab is-active" id="previously-requested-tab" role="tab" aria-selected="true" aria-controls="previously-requested-panel" data-catalogue-tab="previously-requested-panel">
                                <i class="icon-history" aria-hidden="true"></i>
                            Previously Requested Items
                        </button>
                        <button type="button" class="catalogue-tab" id="newly-added-tab" role="tab" aria-selected="false" aria-controls="newly-added-panel" data-catalogue-tab="newly-added-panel" tabindex="-1">
                                <i class="icon-tag" aria-hidden="true"></i>
                            Newly Added Items
                        </button>
                    </div>

                    <div id="input-fields">

                        <div class="catalogue-tab-panel is-active" id="previously-requested-panel" role="tabpanel" aria-labelledby="previously-requested-tab">
                        <div class="section-shell">
                            <div class="section-header">
                                <h3>Previously Requested Items</h3>
                            </div>

                            <div class="previously-requested-wrap">
                                <table class="previously-requested-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 52%;">Product</th>
                                            <th style="width: 18%;">Code</th>
                                            <th style="width: 14%;">Qty</th>
                                            <th style="width: 16%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($previously_ordered as $po)
                                            <tr>
                                                <td data-label="Product">
                                                    <div class="previously-requested-name">
                                                        <small>
                                                            <a href="{{ route('product.front.show', $po->slug) }}">{{ $po->name }}</a>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td data-label="Code">
                                                    <span class="previously-requested-code">{{ $po->id }}</span>
                                                </td>
                                                <td data-label="Qty">
                                                    <input type="number" min="1" step="1" value="1" class="previously-requested-qty" id="quantity_prev_{{ $po->id }}" aria-label="Quantity for {{ $po->name }}">
                                                </td>
                                                <td data-label="Action">
                                                    <button type="button" class="previously-requested-action" onclick="add_to_cart('{{ $po->id }}', $('#quantity_prev_{{ $po->id }}').val());">
                                                        <i class="icon-shopping-cart"></i> Reorder
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="newly-added-empty">No previously requested items available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        </div>

                        <div class="catalogue-tab-panel" id="newly-added-panel" role="tabpanel" aria-labelledby="newly-added-tab" hidden>
                        <div class="section-shell">
                            <div class="section-header">
                                <h3>Newly Added Items</h3>
                            </div>

                            <div class="newly-added-table-wrap">
                                <table class="newly-added-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 54%;">Product</th>
                                            <th style="width: 18%;">Code</th>
                                            <th style="width: 14%;">Qty</th>
                                            <th style="width: 14%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($newly_added as $po)
                                            <tr>
                                                <td data-label="Product">
                                                    <div class="newly-added-product">
                                                        <div class="product-copy">
                                                            <small>
                                                                <a href="{{ route('product.front.show', $po->slug) }}">{{ $po->name }}</a>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td data-label="Code">
                                                    <span class="newly-added-code">{{ $po->id }}</span>
                                                </td>
                                                <td data-label="Qty">
                                                    <input type="number" min="1" step="1" value="1" class="newly-added-qty" id="quantity{{ $po->id }}" aria-label="Quantity for {{ $po->name }}">
                                                </td>
                                                <td data-label="Action">
                                                    <button type="button" class="newly-added-action d-flex text-nowrap" onclick="add_to_cart('{{ $po->id }}', $('#quantity{{ $po->id }}').val());">
                                                        <i class="icon-shopping-cart"></i> Add to Cart
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="newly-added-empty">No newly added items available at the moment.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </div>

                    </div>
                </div>

                <aside class="catalogue-sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-header">
                            <h3>Cart Details</h3>
                            <span class="mini-badge">{{ Setting::EcommerceCartTotalItems() }}</span>
                        </div>

                        <div class="sidebar-items" id="my-cart-list">
                            @forelse(Setting::cart_items() as $item)
                                <div class="sidebar-item cart-item" data-cart-id="{{ $item->id }}">
                                    <div>
                                        <h4>{{ optional($item->product)->name }}</h4>
                                        <div class="cart-item-actions">
                                            <div class="side-cart-qty" aria-label="Quantity controls">
                                                <button type="button" class="qty-adjust qty-minus" data-cart-id="{{ $item->id }}" data-delta="-1" aria-label="Decrease quantity">−</button>
                                                <span class="qty-value">{{ $item->qty }}</span>
                                                <button type="button" class="qty-adjust qty-plus" data-cart-id="{{ $item->id }}" data-delta="1" aria-label="Increase quantity">+</button>
                                            </div>
                                            <button type="button" class="remove-cart-item" data-cart-id="{{ $item->id }}" aria-label="Remove item">
                                                <i class="icon-trash-alt1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-sidebar">Your cart is currently empty.</div>
                            @endforelse
                        </div>

                        <div class="sidebar-total">
                            <span>Items</span>
                            <strong>{{ Setting::EcommerceCartTotalItems() }}</strong>
                        </div>

                        <div class="sidebar-actions">
                            <a href="{{ route('cart.front.checkout') }}" class="btn btn-primary">Proceed to Checkout</a>
                            <!-- <a href="{{ route('cart.front.show') }}" class="btn btn-outline">View Cart</a> -->
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <div class="sidebar-header">
                            <h3>Quick Info</h3>
                        </div>
                        <ul class="sidebar-note">
                            <li><i class="icon-check-circle"></i><span>Fast request processing with the existing MRS workflow.</span></li>
                            <li><i class="icon-check-circle"></i><span>Secure catalog browsing and order review before checkout.</span></li>
                            <li><i class="icon-check-circle"></i><span>Track quantities and stock availability before final submission.</span></li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@section('pagejs')
    <script src="{{ asset('theme/catalogue/js/functions.js') }}"></script>
    <script src="{{ asset('theme/catalogue/js/jquery.themepunch.tools.min.js') }}"></script>
    <script src="{{ asset('theme/catalogue/js/jquery.themepunch.revolution.min.js') }}"></script>

    <script>
        function renderCartSidebar(items, totalItems) {
            const $container = $('#my-cart-list');
            const count = Number(totalItems || 0);

            if (!items || items.length === 0) {
                $container.html('<div class="empty-sidebar">Your cart is currently empty.</div>');
                $('.mini-badge').text(0);
                $('.top-cart-number').text(0);
                $('.hero-cart-total').text(0);
                $('.sidebar-total strong').text(0);
                return;
            }

            let html = '';

            $.each(items, function(index, item) {
                html += '<div class="sidebar-item cart-item" data-cart-id="' + item.id + '">';
                html += '  <div>';
                html += '    <h4>' + (item.name || '') + '</h4>';
                html += '    <div class="cart-item-actions">';
                html += '      <div class="side-cart-qty" aria-label="Quantity controls">';
                html += '        <button type="button" class="qty-adjust qty-minus" data-cart-id="' + item.id + '" data-delta="-1" aria-label="Decrease quantity">−</button>';
                html += '        <span class="qty-value">' + (item.qty || 0) + '</span>';
                html += '        <button type="button" class="qty-adjust qty-plus" data-cart-id="' + item.id + '" data-delta="1" aria-label="Increase quantity">+</button>';
                html += '      </div>';
                html += '      <button type="button" class="remove-cart-item" data-cart-id="' + item.id + '" aria-label="Remove item"><i class="icon-trash-alt1"></i></button>';
                html += '    </div>';
                html += '  </div>';
                html += '</div>';
            });

            $container.html(html);
            $('.mini-badge').text(count);
            $('.top-cart-number').text(count);
            $('.hero-cart-total').text(count);
            $('.sidebar-total strong').text(count);
            bindCartActions();
        }

        function bindCartActions() {
            $('.qty-adjust').off('click').on('click', function() {
                const cartId = $(this).data('cart-id');
                const delta = Number($(this).data('delta') || 0);
                const currentQty = Number($('.cart-item[data-cart-id="' + cartId + '"] .qty-value').text() || 0);
                const nextQty = currentQty + delta;

                if (nextQty <= 0) {
                    removeCartItem(cartId);
                    return;
                }

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: '{{ route('cart.update') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        orderID: cartId,
                        quantity: nextQty
                    },
                    success: function(response) {
                        if (response && response.success !== false) {
                            renderCartSidebar(response.items, response.totalItems);
                            $.notify('Cart updated', { position: 'bottom right', className: 'success' });
                        }
                    }
                });
            });

            $('.remove-cart-item').off('click').on('click', function() {
                removeCartItem($(this).data('cart-id'));
            });
        }

        function removeCartItem(cartId) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: 'POST',
                url: '{{ route('cart.remove_product') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: cartId
                },
                success: function(response) {
                    if (response && response.success !== false) {
                        renderCartSidebar(response.items, response.totalItems);
                        $.notify('Item removed from cart', { position: 'bottom right', className: 'success' });
                    }
                }
            });
        }

        $(document).ready(function() {
            var isAuthenticated = $('#isAuthenticated').val();
            bindCartActions();

            $('.catalogue-tab').on('click', function() {
                var $tab = $(this);
                var panelId = $tab.data('catalogue-tab');

                $('.catalogue-tab').removeClass('is-active').attr('aria-selected', 'false').attr('tabindex', '-1');
                $tab.addClass('is-active').attr('aria-selected', 'true').removeAttr('tabindex');
                $('.catalogue-tab-panel').attr('hidden', true).removeClass('is-active');
                $('#' + panelId).removeAttr('hidden').addClass('is-active');
            });

            $('.catalogue-tab').on('keydown', function(event) {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                    return;
                }

                var $tabs = $('.catalogue-tab');
                var nextIndex = ($tabs.index(this) + (event.key === 'ArrowRight' ? 1 : $tabs.length - 1)) % $tabs.length;
                $tabs.eq(nextIndex).focus().trigger('click');
                event.preventDefault();
            });

            if (isAuthenticated === 'false') {
                $('.grid-inner a, #top-cart a, .product-image a').click(function(e) {
                    e.preventDefault();
                }).addClass('disabled-link');

                $("#input-fields :input").prop("disabled", true);
            }
        });

        function add_to_cart(product, requestedQty) {
            const qty = Math.max(1, parseInt(requestedQty || $('#quantity' + product).val() || $('#quantity_prev_' + product).val() || 1, 10));

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                data: {
                    "product_id": product,
                    "qty": qty,
                    "_token": "{{ csrf_token() }}",
                },
                type: "post",
                url: "{{route('product.add-to-cart')}}",
                success: function(returnData) {
                    $("#loading-overlay").hide();
                    if (returnData['success']) {
                        $('.top-cart-number').html(returnData['totalItems']);
                        $('.mini-badge').html(returnData['totalItems']);
                        $('.hero-cart-total').html(returnData['totalItems']);
                        if (returnData.items) {
                            renderCartSidebar(returnData.items, returnData.totalItems);
                        }
                        $.notify("Product Added to your cart", {
                            position: "bottom right",
                            className: "success"
                        });
                    } else {
                        $.notify("Reached maximum limit number of items.", {
                            position: "bottom right",
                            className: "error"
                        });
                    }
                }
            });
        }
    </script>
@endsection
