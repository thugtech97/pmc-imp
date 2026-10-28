@php
    $categoryName = $c->description ?? $c->name ?? 'Category';
    $iconMap = [
        'hardware' => 'icon-tools',
        'electrical' => 'icon-bolt',
        'plumbing' => 'icon-water',
        'office' => 'icon-briefcase',
        'janitorial' => 'icon-broom',
        'safety' => 'icon-shield',
        'building' => 'icon-building',
        'general' => 'icon-grid',
        'maintenance' => 'icon-wrench',
        'cleaning' => 'icon-spray',
        'medical' => 'icon-plus-circle',
        'automotive' => 'icon-car',
        'furniture' => 'icon-chair',
        'paint' => 'icon-palette',
    ];

    $normalizedKey = strtolower(trim($categoryName));
    $iconClass = 'icon-folder';
    foreach ($iconMap as $key => $value) {
        if (str_contains($normalizedKey, $key)) {
            $iconClass = $value;
            break;
        }
    }
@endphp

<div class="col-6 col-md-4 col-lg-3">
    <a href="{{ route('catalogue.category_products', $c->id) }}" class="text-decoration-none text-dark d-flex flex-column h-100 category-modern-link">
        <div class="category-modern-card h-100">
            <div class="category-modern-icon-wrap">
                <i class="{{ $iconClass }} category-modern-icon"></i>
            </div>
            <div class="category-modern-body">
                <h3 class="category-modern-title" title="{{ $categoryName }}">
                    {{ ucwords(strtolower($categoryName)) }}
                </h3>
                <span class="category-modern-meta">View all</span>
            </div>
        </div>
    </a>
</div>