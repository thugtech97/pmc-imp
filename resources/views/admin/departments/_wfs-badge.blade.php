{{-- WFS connection status for Admin > Departments (needs $wfsAvailable). --}}
@php $wfsUserCount = $wfsAvailable ? count(\App\Services\WfsDirectory::users()) : 0; @endphp
@if ($wfsAvailable)
    <span class="badge badge-success mg-l-10 tx-12 tx-normal d-inline-flex align-items-center"
          style="padding: 5px 9px; border-radius: 999px;"
          title="IMP can reach WFS. Approver lists come from its {{ $wfsUserCount }} active users.">
        <span style="width:7px;height:7px;border-radius:50%;background:#fff;display:inline-block;margin-right:6px;"></span>
        WFS connected · {{ number_format($wfsUserCount) }} active users
    </span>
@else
    <span class="badge badge-danger mg-l-10 tx-12 tx-normal d-inline-flex align-items-center"
          style="padding: 5px 9px; border-radius: 999px;"
          title="IMP could not connect to WFS. Approver names and lists are unavailable, and MRS/IMF can't be submitted until it is back.">
        <span style="width:7px;height:7px;border-radius:50%;background:#fff;display:inline-block;margin-right:6px;"></span>
        WFS not connected
    </span>
@endif
