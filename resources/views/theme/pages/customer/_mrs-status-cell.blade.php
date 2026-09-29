@php
    // Single source of truth — see SalesHeader::getRequestorStatusAttribute().
    $label = $sale->requestor_status;
    $group = $sale->requestor_status_group;

    // Overdue logic (2 days from updated_at). Only meaningful while someone downstream is
    // holding the request: not for a draft, not once it is cancelled, and not while it is
    // sitting with the requestor for revision.
    $tracksOverdue = in_array($group, ['pending', 'process', 'approved'], true);
    $dueDate = $sale->updated_at->copy()->addDays(2);
    $now = now();
    $isOverdue = $tracksOverdue && $now->gt($dueDate);
    $overdueDays = $isOverdue ? $dueDate->diffInDays($now) : 0;

    // The PA only exists (and is only printable) once it is out with purchasing and not held.
    // Delivery is downstream of that, so a partially/fully delivered request still has one.
    $canPrintPa = in_array($group, ['approved', 'partial', 'delivered'], true)
        && $sale->purchaseAdvice
        && (int) $sale->purchaseAdvice->is_hold !== 1;

    if ($group === 'cancelled') {
        $textClass = 'text-danger';
    } elseif ($group === 'action') {
        $textClass = 'text-warning';
    } elseif ($group === 'delivered') {
        $textClass = 'text-success';
    } elseif ($group === 'partial') {
        $textClass = 'text-info';
    } elseif ($isOverdue) {
        $textClass = 'text-danger';
    } elseif ($group === 'process' || $group === 'approved') {
        $textClass = 'text-primary';
    } else {
        $textClass = 'text-dark';
    }
@endphp

<span class="{{ $textClass }} fw-bold">

    @if ($canPrintPa)
        <u>
            <i class="icon-print"></i>
            <a href="javascript:;"
               class="print {{ $textClass }}"
               data-order-number="{{ $sale->order_number }}">
                {{ $label }}
            </a>
        </u>
    @else
        {{ $label }}
    @endif

    {{-- Overdue Days --}}
    @if ($isOverdue)
        ({{ $overdueDays }} DAY{{ $overdueDays > 1 ? 'S' : '' }})
    @endif

    {{-- Marked submitted, but WFS never got it (see MyAccountController::flagMissingFromWfs). --}}
    @if (!empty($sale->wfs_missing))
        <br/>
        <span class="text-danger small fw-normal">Not received by WFS &mdash; no approver can see this. Please resubmit.</span>
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
