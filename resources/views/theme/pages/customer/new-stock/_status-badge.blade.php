@php
    /**
     * Shared IMF status pill. Usage: @include('...new-stock._status-badge', ['status' => $request->status])
     * Colors are inlined so they render consistently regardless of the Bootstrap version.
     */
    $s = strtoupper(trim($status ?? ''));

    $bg = '#6c757d'; // default / SAVED — gray
    $fg = '#ffffff';

    if (strpos($s, 'CANCELLED') !== false || strpos($s, 'DISAPPROVED') !== false || strpos($s, 'REJECTED') !== false) {
        $bg = '#dc3545';                 // red
    } elseif (strpos($s, 'HOLD') !== false) {
        $bg = '#ffc107'; $fg = '#212529'; // amber
    } elseif (strpos($s, 'APPROVER') !== false) {
        $bg = '#198754';                 // green — final MCD Approver approval
    } elseif (strpos($s, 'APPROVED') !== false) {
        $bg = '#3b7ddd';                 // blue/primary
    } elseif (strpos($s, 'SUBMITTED') !== false) {
        $bg = '#0dcaf0'; $fg = '#053b45'; // cyan
    } elseif (strpos($s, 'SAVED') !== false) {
        $bg = '#6c757d';                 // gray
    }
@endphp
<span style="display:inline-block; background:{{ $bg }}; color:{{ $fg }}; border-radius:20px; padding:3px 12px; font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; line-height:1.6; white-space:nowrap;">
    {{ $status ?? '—' }}
</span>
