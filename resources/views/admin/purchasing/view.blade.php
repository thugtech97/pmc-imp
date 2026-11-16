@extends('admin.layouts.app')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('lib/sweetalert2/sweetalert.min.css') }}" type="text/css">
    @include('admin.components._pa-design-system')
@endsection

@section('content')
@php
    $status = $sales->status;
    if ($sales->status === 'HOLD (For MCD Planner re-edit)') {
        $status = 'HOLD (For MCD Planner re-edit) - Hold by ' . ($sales->holder->name ?? 'Unknown Holder');
    }
    if ($sales->status === 'RECEIVED FOR CANVASS (Purchasing Officer)') {
        $status = 'RECEIVED FOR CANVASS (' . ($sales->purchaser->name ?? 'Unknown Purchaser') . ')';
    }

    $statusLower = strtolower((string) $sales->status);
    $statusClass = 'status-default';
    if (strpos($statusLower, 'cancel') !== false)        $statusClass = 'status-cancelled';
    elseif (strpos($statusLower, 'hold') !== false)      $statusClass = 'status-pending';
    elseif (strpos($statusLower, 'approved') !== false)  $statusClass = 'status-approved';
    elseif (strpos($statusLower, 'verif') !== false)     $statusClass = 'status-approved';

    $attachments = array_values(array_filter(array_map('trim', explode('|', (string) $sales->order_source))));
    $itemCols = 11;

    // Once a canvasser holds it, "Assign" becomes "Re-assign" (its own endpoint, with a reason).
    $hasCanvasser      = (bool) $sales->received_by;
    $heldByCanvasser   = $sales->isHeldByCanvasser();
    $reassignBlocker   = $hasCanvasser ? $sales->canvasserReassignBlocker() : null;
    $canReassign       = $hasCanvasser && $reassignBlocker === null;
    $currentCanvasser  = optional($sales->purchaser)->name ?? 'Unknown canvasser';
    if ($heldByCanvasser) {
        $canvasserStage = 'Held — with the MCD Planner for re-edit';
    } elseif ($sales->received_at) {
        $canvasserStage = 'Received ' . \Carbon\Carbon::parse($sales->received_at)->format('M d, Y h:i A');
    } else {
        $canvasserStage = 'Waiting to be received';
    }
@endphp

<div class="container-fluid" style="max-width: 1600px;">

    <div class="pa-page-header d-flex align-items-start justify-content-between flex-wrap" style="gap:14px;">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">CMS</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pa.index') }}">Order Transaction</a></li>
                    <li class="breadcrumb-item active">MRS Summary</li>
                </ol>
            </nav>
            <h4>
                MRS# {{ $sales->order_number }}
                @if ($sales->revision > 0)
                    <span class="pa-rev-badge">{{ $sales->rev_label }}</span>
                @endif
            </h4>
            @if ($sales->revision > 0 && $sales->revised_at)
                <div class="pa-subtitle">Last revised {{ $sales->revised_at->format('M d, Y h:i A') }}</div>
            @endif
        </div>
        <div class="d-flex flex-column align-items-end" style="gap:8px;">
            <div class="d-flex" style="gap:8px;">
                <a href="#" id="printDetails" class="btn-pa btn-pa-success" data-order="{{ $sales->id }}"><i class="fa fa-print"></i> Print MRS</a>
                <a href="{{ route('pa.index') }}" class="btn-pa btn-pa-secondary"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <span class="pa-status-badge {{ $statusClass }}"><i class="fa fa-circle"></i> {{ $status }}</span>
        </div>
    </div>

    @include('admin.purchasing.components._mrs-summary-cards', ['sales' => $sales, 'attachments' => $attachments])

    <form id="issuanceForm" method="POST" action="{{ route('mrs.update') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="sales_header_id" value="{{ $sales->id }}">

        {{-- Items --}}
        <div class="pa-card">
            <div class="pa-card-header">
                <div class="card-icon"><i class="fa fa-list"></i></div>
                <div><h6>Items</h6><p>{{ count($salesDetails) }} item(s) in this request</p></div>
            </div>
            <div class="pa-card-body" style="padding:0;">
                <div class="pa-table-wrapper">
                    <table class="pa-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th style="min-width:70px;">Hold</th>
                                <th style="min-width:200px;">Hold Remarks</th>
                                <th style="min-width:80px;">Priority#</th>
                                <th style="min-width:300px;">Item Description</th>
                                <th style="min-width:110px;">Stock Code</th>
                                <th style="min-width:70px;">UoM</th>
                                <th style="min-width:110px;">OEM No.</th>
                                <th style="min-width:110px;">Cost Code</th>
                                <th style="min-width:110px;">Qty To Order</th>
                                <th style="min-width:120px;">Previous PO#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 0; @endphp
                            @forelse($salesDetails as $details)
                                @php
                                    $count++;
                                    $held = (int) $details->promo_id === 1;
                                @endphp
                                <input type="hidden" name="ecommerce_sales_details_id{{ $details->id }}" value="{{ $details->id }}">
                                <input type="hidden" name="ordered_qty{{ $details->id }}" value="{{ $details->qty }}">

                                <tr class="{{ $held ? 'row-held' : '' }}">
                                    <td><span class="row-num">{{ $count }}</span></td>
                                    <td style="text-align:center;">
                                        <label class="switch">
                                            <input type="hidden" name="is_hold{{ $details->id }}" value="0">
                                            <input type="checkbox" id="checkbox-{{ $details->id }}" name="is_hold{{ $details->id }}" value="1" {{ $details->promo_id == 0 ? '' : 'checked' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <textarea rows="2" onblur="onHoldRemarks('{{ $details->id }}', this.value);" name="hold_desc{{ $details->id }}" id="textarea-{{ $details->id }}"
                                            class="pa-textarea" style="min-height:56px; font-size:12.5px; padding:6px 8px;"
                                            placeholder="Type hold remarks here...">{{ $details->promo_description }}</textarea>
                                    </td>
                                    <td>{{ $sales->priority }}</td>
                                    <td style="font-weight:500;">{{ $details->product->name ?? 'N/A' }}</td>
                                    <td class="mono">{{ $details->product->code ?? 'N/A' }}</td>
                                    <td>{{ $details->product->uom ?? 'N/A' }}</td>
                                    <td>{{ $details->product->oem ?? 'N/A' }}</td>
                                    <td>{{ $details->cost_code }}</td>
                                    <td>
                                        <input type="number" name="quantityToOrder{{ $details->id }}" value="{{ $details->qty_to_order > 0 ? (int) $details->qty_to_order : (int) $details->qty }}" class="form-control" {{ $role->name !== 'MCD Planner' ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <input type="text" name="previous_no{{ $details->id }}" value="{{ $details->previous_mrs }}" class="form-control" {{ $role->name !== 'MCD Planner' ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                                @include('admin.purchasing.components._mrs-item-subrow', ['details' => $details, 'itemCols' => $itemCols, 'held' => $held])
                            @empty
                                <tr>
                                    <td colspan="{{ $itemCols }}">
                                        <div style="padding:40px; text-align:center; color:var(--pa-text-light);">
                                            <i class="fa fa-inbox" style="font-size:28px; display:block; margin-bottom:10px; opacity:0.4;"></i>
                                            <p style="margin:0; font-size:13px;">No items found for this request.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Assign + remarks --}}
        <div class="row">
            <div class="col-lg-5">
                @if (!$hasCanvasser)
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div class="card-icon"><i class="fa fa-user-plus"></i></div>
                        <div><h6>Assign Canvasser</h6><p>Select who will canvass this request</p></div>
                    </div>
                    <div class="pa-card-body">
                        <label class="pa-label">Assign To</label>
                        <select class="pa-select employees" id="purchasers">
                            @foreach ($purchasers as $purchaser)
                                <option value="{{ $purchaser->id }}" {{ $purchaser->id == $sales->received_by ? 'selected' : '' }}>{{ $purchaser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @else
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div class="card-icon"><i class="fa fa-exchange"></i></div>
                        <div><h6>Re-assign Canvasser</h6><p>Move this MRS to another canvasser</p></div>
                    </div>
                    <div class="pa-card-body">
                        <div style="margin-bottom:12px;">
                            <label class="pa-label">Currently With</label>
                            <div style="font-weight:600;">{{ $currentCanvasser }}</div>
                            <div style="font-size:12px; color:var(--pa-text-light);">{{ $canvasserStage }}</div>
                        </div>
                        @if ($canReassign)
                            <label class="pa-label" for="reassignTo">Re-assign To</label>
                            <select class="pa-select" id="reassignTo" name="canvasser_id" form="reassignForm">
                                <option value="" selected disabled>Select a canvasser</option>
                                @foreach ($purchasers as $purchaser)
                                    @if ((int) $purchaser->id !== (int) $sales->received_by)
                                        <option value="{{ $purchaser->id }}">{{ $purchaser->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <label class="pa-label" for="reassignReason" style="margin-top:10px;">Reason</label>
                            <textarea rows="2" id="reassignReason" name="reason" form="reassignForm" maxlength="500" class="pa-textarea"
                                placeholder="e.g. Not in {{ $currentCanvasser }}'s line of items"></textarea>
                            <p style="font-size:12px; color:var(--pa-text-light); margin:8px 0 0;">
                                @if ($heldByCanvasser)
                                    It stays with the MCD Planner. When the planner updates it, it goes to the new canvasser.
                                @else
                                    The new canvasser must receive it. PO details already entered are kept.
                                @endif
                            </p>
                        @else
                            <p style="font-size:12.5px; margin:0; color:var(--pa-text-light);"><i class="fa fa-info-circle"></i> {{ $reassignBlocker }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            <div class="col-lg-7">
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div class="card-icon"><i class="fa fa-reply-all"></i></div>
                        <div><h6>Note For Planner</h6><p>Required when returning this MRS for re-edit</p></div>
                    </div>
                    <div class="pa-card-body">
                        <textarea rows="5" id="note" class="pa-textarea" placeholder="State why you are returning this to the MCD Planner...">{{ $sales->purchaser_note }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action bar --}}
        <div class="pa-action-bar">
            @if (!$hasCanvasser)
                <a href="#" id="receiveBtn" class="btn-pa btn-pa-success"><i class="fa fa-user-plus"></i> Assign</a>
            @elseif ($canReassign)
                <a href="#" id="reassignBtn" class="btn-pa btn-pa-success"><i class="fa fa-exchange"></i> Re-assign</a>
            @endif
            <div class="spacer"></div>
            {{-- Already sitting with the planner: nothing to return. --}}
            @if (!$sales->isInPlannerReeditQueue())
                <a href="#" id="holdPurchaserBtn" class="btn-pa btn-pa-warning"><i class="fa fa-undo"></i> Hold &amp; Return to Planner</a>
            @endif
        </div>
    </form>

    {{-- Fields live in the Re-assign card above via form="reassignForm" (forms cannot nest). --}}
    @if ($canReassign)
        <form id="reassignForm" method="POST" action="{{ route('pa.reassign_mrs', $sales->id) }}" style="display:none;">
            @csrf
        </form>
    @endif

    @include('admin.components._document-history', [
        'histories' => $sales->histories,
        'title'     => 'MRS History Log',
        'subtitle'  => 'Every change made to MRS ' . $sales->order_number,
    ])
</div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
    <script>
        // Shared Yes/Cancel confirmation for admin actions (falls back to native confirm).
        function adminConfirm(opts, onConfirm) {
            var cfg = Object.assign({
                title: 'Are you sure?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#9aa0a6',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }, opts || {});
            if (window.Swal) {
                Swal.fire(cfg).then(function (r) { if (r.isConfirmed) onConfirm(); });
            } else if (confirm(cfg.title)) {
                onConfirm();
            }
        }

        function issuanceSubmit() {
            $('#issuanceForm').submit();
        }

        function onHoldRemarks(id, value){
            let data = {
                        id: id,
                        promo_id: $('#checkbox-'+id).is(':checked') ? 1 : 0,
                        promo_description: value,
                        "_token": "{{ csrf_token() }}"
                    }
            updateItemStatus(data);
        }

        function updateItemStatus(data){
            $.ajax({
                url: "{{ route('item.hold') }}",
                type: 'POST',
                data: data,
                success: function(response){
                    console.log(response)
                }
            })
        }

        $(document).ready(function() {
            //employee_lookup();
            @foreach($salesDetails as $details)
                @if($details->promo_id == 1)
                    $("#textarea-{{ $details->id }}").slideDown();
                @else
                    $("#textarea-{{ $details->id }}").slideUp();
                @endif
                $('#checkbox-{{ $details->id }}').change(function() {
                    if ($(this).is(':checked')) {
                        $("#textarea-{{ $details->id }}").slideDown();
                    } else {
                        $("#textarea-{{ $details->id }}").slideUp();
                    }
                    let data = {
                        id: '{{ $details->id }}',
                        promo_id: $(this).is(':checked') ? 1 : 0,
                        "_token": "{{ csrf_token() }}"
                    }
                    updateItemStatus(data);
                });
            @endforeach
            $('#printDetails').click(function(e) {
                e.preventDefault(); 
                
                $.ajax({
                    url: "{{route('sales-transaction.generate_report')}}",
                    type: 'GET',
                    data: { id: $(this).attr('data-order') },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data) {
                        if (data instanceof Blob) {
                            const pdfBlob = new Blob([data], { type: 'application/pdf' });
                            const pdfUrl = URL.createObjectURL(pdfBlob);
                            window.open(pdfUrl, '_blank');
                            URL.revokeObjectURL(pdfUrl);
                        }
                    }
                });
            });

            $('#receiveBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#purchasers').val());
                var purchaserName = $('#purchasers option:selected').text().trim();
                var url = "{{ route('mrs.action', ['action' => 'mrs-assign', 'id' => $sales->id]) }}&note=" + note;
                adminConfirm({ title: 'Assign this MRS to ' + (purchaserName || 'the selected purchaser') + '?', confirmButtonText: 'Yes, assign', confirmButtonColor: '#2ecc71' }, function () {
                    window.location.href = url;
                });
            });

            $('#reassignBtn').click(function(event) {
                event.preventDefault();
                var to = $('#reassignTo').val();
                var reason = $.trim($('#reassignReason').val());
                var warn = function (text, $focus) {
                    if (window.Swal) { Swal.fire({ icon: 'warning', title: 'Required', text: text }); } else { alert(text); }
                    $focus.focus();
                };
                if (!to) { return warn('Select the canvasser to re-assign to.', $('#reassignTo')); }
                if (!reason) { return warn('State why this MRS is being re-assigned.', $('#reassignReason')); }
                var toName = $('#reassignTo option:selected').text().trim();
                adminConfirm({
                    title: 'Re-assign to ' + toName + '?',
                    text: @json('It will be taken from ' . $currentCanvasser . '.' . ($heldByCanvasser ? ' It stays with the MCD Planner until they update it.' : '')),
                    confirmButtonText: 'Yes, re-assign',
                    confirmButtonColor: '#2ecc71'
                }, function () {
                    $('#reassignForm').submit();
                });
            });

            $('#holdPurchaserBtn').click(function(event) {
                event.preventDefault();
                var note = $('#note').val().trim();
                if (!note) {
                    if (window.Swal) {
                        Swal.fire({ icon: 'warning', title: 'Remarks required', text: 'Please state why you are returning this to the MCD Planner.' });
                    } else {
                        alert('Please state why you are returning this to the MCD Planner.');
                    }
                    $('#note').focus();
                    return;
                }
                var url = "{{ route('pa.action', ['action' => 'hold-purchaser', 'id' => $sales->id]) }}&note=" + encodeURIComponent(note);
                adminConfirm({ title: 'Return this to the MCD Planner for re-edit?', icon: 'warning', confirmButtonText: 'Yes, hold', confirmButtonColor: '#f0ad4e' }, function () {
                    window.location.href = url;
                });
            });

            // Confirm before submitting changes.
            $('#issuanceForm').on('submit', function(event) {
                if ($(this).data('confirmed')) { return; }
                event.preventDefault();
                var form = this;
                adminConfirm({ title: 'Save these changes?', confirmButtonText: 'Yes, save', confirmButtonColor: '#2ecc71' }, function () {
                    $(form).data('confirmed', true);
                    form.submit();
                });
            });
            
        });

        function employee_lookup() {
            $.ajax({
                type: 'GET',
                url: "{{ route('users.employee_lookup') }}",
                success: function(data){
                    try {
                        var employeesArray = JSON.parse(data);
                        console.log(employeesArray[0]);
                        $('.employees').empty();
                        $('.employees').append('<option value="" disabled selected>Select an employee</option>');
                        employeesArray.forEach(function(employee) {
                            var fullname = employee.fullname.replace(/\*/g, ' ');
                            $('.employees').append('<option value="' + employee.fullnamewithdept + '">' + fullname + '</option>');
                        });
                    } catch (e) {
                        console.error("Error parsing JSON: ", e);
                    }
                }
            });
        }
    </script>
@endsection