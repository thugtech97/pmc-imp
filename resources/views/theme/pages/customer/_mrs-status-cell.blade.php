@php
    // Determine if revised
    $isRevised = str_contains($sale->status, 'REVISED MRS');

    // Overdue logic (2 days from updated_at)
    $dueDate = $sale->updated_at->copy()->addDays(2);
    $now = now();
    $isOverdue = $now->gt($dueDate);
    $overdueDays = $isOverdue ? $dueDate->diffInDays($now) : 0;

    // Text color logic
    if (strpos($sale->status, 'CANCELLED') !== false) {
        $textClass = 'text-danger';
    } elseif ($isRevised) {
        $textClass = 'text-primary';
    } elseif ($isOverdue) {
        $textClass = 'text-danger';
    } else {
        $textClass = 'text-dark';
    }
@endphp

<span class="{{ $textClass }} fw-bold">

    @if ($sale->received_at)
        <u>
            <i class="icon-print"></i>
            <a href="javascript:;"
               class="print {{ $textClass }}"
               data-order-number="{{ $sale->order_number }}">
                RECEIVED FOR CANVASS ({{ strtoupper($sale->purchaser->name ?? 'N/A') }})
            </a>
        </u>

    @elseif ($sale->approved_at)

        <u>
            <i class="icon-print"></i>
            <a href="javascript:;"
               class="print {{ $textClass }}"
               data-order-number="{{ $sale->order_number }}">
                APPROVED BY MCD MANAGER - PA FOR DELEGATION
            </a>
        </u>

    @else

        {{ strtoupper($sale->status) }}

    @endif

    {{-- Overdue Days --}}
    @if ($isOverdue && !$isRevised && strpos($sale->status, 'CANCELLED') === false)
        ({{ $overdueDays }} DAY{{ $overdueDays > 1 ? 'S' : '' }})
    @endif

    {{-- Promo Hold Info (UNCHANGED LOGIC) --}}
    @if ($sale->hasPromo())
        <br/>
        @php
            $hold = $sale->items->where('promo_id', 1)->count();
            $is_pa = $sale->items->where('promo_id', 1)->whereNotNull('is_pa')->count();
        @endphp
        @if($hold !== $is_pa)
            <span class="text-warning">
                ({{ $sale->items->where('promo_id', 1)->whereNull('is_pa')->count() }}
                OUT OF {{ $sale->items->count() }} ITEMS ON-HOLD)
            </span>
        @endif
    @endif

</span>
