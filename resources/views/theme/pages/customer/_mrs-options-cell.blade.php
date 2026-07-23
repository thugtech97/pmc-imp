@if (!(strpos($sale->status, 'CANCELLED') !== false))
    <a href="#" onclick="view_items('{{$sale->id}}');" title="View Details" aria-expanded="false">
        <i class="icon-eye"></i>
    </a>
@endif
@if (!$sale->approved_at && !(strpos($sale->status, 'CANCELLED') !== false))
    <a href="javascript:;" onclick="cancel_unpaid_order('{{$sale->id}}')" title="Cancel MRS"><i class="icon-forbidden"></i></a>
@endif
@if ($sale->approved_at)
    <span class="text-success"><i class="icon-check"></i></span>
@endif
@if (strpos($sale->status, 'ON-HOLD') !== false || strpos($sale->status, 'ON HOLD') !== false)
    <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
        <i class="icon-pencil"></i>
    </a>
    <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'resubmitted']) }}" title="Resubmit"><i class="icon-refresh"></i></a>
@endif
@if ($sale->hasPromo() && !$sale->received_at && strpos($sale->status, 'APPROVED (MCD Planner)') !== false)
    <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
        <i class="icon-pencil"></i>
    </a>
    <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'resubmitted']) }}" title="Resubmit"><i class="icon-refresh"></i></a>
@endif
@if ($sale->hasPromo() && $sale->received_at)
    <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
        <i class="icon-pencil"></i>
    </a>
@endif
@if (strpos($sale->status, 'CANCELLED') !== false)
    <a href="#" onclick="view_items('{{$sale->id}}');" title="View Details" aria-expanded="false">
        <i class="icon-eye"></i>
    </a>
@endif

@switch($sale->status)
    @case('SAVED')
    @case('saved')
        <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
            <i class="icon-pencil"></i>
        </a>
        <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'submitted']) }}" title="Submit for Approval"><i class="icon-upload"></i></a>
        @break
    @case('posted')
        <a href="#" title="View Deliveries" onclick="view_deliveries('{{$sale->id}}');"><i class="icon-truck"></i></a>
        @break
@endswitch

@if($sale->issuances->count() > 0)
<a href="javascript:void(0);" data-toggle="modal" data-target="#issuanceDetailsModal{{ $sale->id }}" aria-expanded="false" title="View Issuances">
    <i class="icon-file"></i>
</a>
@endif
