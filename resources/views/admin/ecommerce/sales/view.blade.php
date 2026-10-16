@extends('admin.layouts.app')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('lib/sweetalert2/sweetalert.min.css') }}" type="text/css">
    @include('admin.components._pa-design-system')
@endsection

@section('content')
@php
    $isPlanner  = $role->name === 'MCD Planner';
    $isVerifier = $role->name === 'MCD Verifier';
    $isApprover = $role->name === 'MCD Approver';
    $isMcd      = $isPlanner || $isVerifier || $isApprover;

    // Status shown in the badge — resolve the actor so it reads the same as the old screen.
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

    // Sitting in the planner's re-edit queue, not received, but still tied to a canvasser =
    // it came back from that canvasser. Mirrors updateIssuance()'s bypass check, including
    // the detour via the department user, so the planner sees where Proceed will send it.
    $reeditQueue = $sales->status === 'HOLD (For MCD Planner re-edit)'
        || $sales->status === 'REQUEST ON HOLD (Hold by MCD Planner)'
        || strpos(strtoupper((string) $sales->status), 'REVISED MRS') === 0;
    $returnedByPurchaser = $reeditQueue && $sales->received_by && !$sales->received_at;

    $attachments = array_values(array_filter(array_map('trim', explode('|', (string) $sales->order_source))));
    $itemCols = $sales->received_at ? 16 : 12;

    $isSubmitted = $sales->status === 'VERIFIED (MCD Verifier) - MRS For MCD Manager APPROVAL' || $sales->received_at;
    $paOnHold = optional($sales->purchaseAdvice)->is_hold == 1;
@endphp

<div class="container-fluid" style="max-width: 1600px;">

    <div class="pa-page-header d-flex align-items-start justify-content-between flex-wrap" style="gap:14px;">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">CMS</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sales-transaction.index') }}">Order Transaction</a></li>
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
            @if ($isMcd)
                <div class="d-flex" style="gap:8px;">
                    <a href="#" id="printDetails" class="btn-pa btn-pa-success" data-order="{{ $sales->id }}"><i class="fa fa-print"></i> Print MRS</a>
                    <a href="{{ route('sales-transaction.index') }}" class="btn-pa btn-pa-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            @endif
            <span class="pa-status-badge {{ $statusClass }}"><i class="fa fa-circle"></i> {{ $status }}</span>
        </div>
    </div>

    @if ($returnedByPurchaser)
        <div class="pa-notice notice-warning">
            <i class="fa fa-reply-all notice-icon"></i>
            <div>
                <div class="notice-title">Returned by Canvasser/Purchaser for re-edit</div>
                <div class="notice-body">
                    {{ $sales->purchaser->name ?? 'The canvasser' }} sent this back.
                    @if ($isPlanner)
                        After you edit and click <strong>Proceed</strong>, it goes <strong>straight back</strong> to {{ $sales->purchaser->name ?? 'the canvasser' }} for canvass &mdash; skipping verification, approval and re-assignment.
                    @endif
                </div>
                @if (!empty($sales->purchaser_note))
                    <div class="notice-body"><strong>Reason:</strong> {{ $sales->purchaser_note }}</div>
                @endif
            </div>
        </div>
    @endif

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
                                <th style="min-width:110px;">Stock Code</th>
                                <th style="min-width:300px;">Item</th>
                                <th style="min-width:70px;">UoM</th>
                                <th style="min-width:110px;">OEM No.</th>
                                <th style="min-width:110px;">Cost Code</th>
                                <th style="min-width:100px;">Requested Qty</th>
                                <th style="min-width:110px;">Qty To Order</th>
                                <th style="min-width:120px;">Previous PO#</th>
                                @if ($sales->received_at)
                                    <th style="min-width:110px;" class="purchaser-col">Current PO#</th>
                                    <th style="min-width:130px;" class="purchaser-col">PO Date Released</th>
                                    <th style="min-width:90px;"  class="purchaser-col">Balance</th>
                                    <th style="min-width:130px;" class="purchaser-col">Qty Received/Delivered</th>
                                @endif
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
                                            <input type="checkbox" id="checkbox-{{ $details->id }}" name="is_hold{{ $details->id }}" value="1" {{ $details->promo_id == 0 ? '' : 'checked' }} {{ $isPlanner ? '' : 'disabled' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <textarea rows="2" onblur="onHoldRemarks('{{ $details->id }}', this.value);" name="hold_desc{{ $details->id }}" id="textarea-{{ $details->id }}"
                                            class="pa-textarea" style="min-height:56px; font-size:12.5px; padding:6px 8px;"
                                            placeholder="Type hold remarks here...">{{ $details->promo_description }}</textarea>
                                    </td>
                                    <td>{{ $sales->priority }}</td>
                                    <td class="mono">{{ $details->product->code ?? 'N/A' }}</td>
                                    <td style="font-weight:500;">{{ $details->product->name ?? 'N/A' }}</td>
                                    <td>{{ $details->product->uom ?? 'N/A' }}</td>
                                    <td>{{ $details->product->oem ?? 'N/A' }}</td>
                                    <td>{{ $details->cost_code }}</td>
                                    <td>{{ (int) $details->qty }}</td>
                                    <td>
                                        <input type="number" data-qty="{{ (int) $details->qty }}" name="quantityToOrder{{ $details->id }}" value="{{ $details->qty_to_order > 0 ? (int) $details->qty_to_order : (int) $details->qty }}" class="form-control qty_order" {{ $isPlanner ? '' : 'disabled' }} required>
                                    </td>
                                    <td>
                                        <input type="text" name="previous_no{{ $details->id }}" value="{{ $details->previous_mrs }}" class="form-control" {{ $isPlanner ? '' : 'disabled' }} required>
                                    </td>
                                    @if ($sales->received_at)
                                        <td class="purchaser-col mono">{{ $details->po_no ?: '—' }}</td>
                                        <td class="purchaser-col">{{ $details->po_date_released ? \Carbon\Carbon::parse($details->po_date_released)->format('m/d/Y') : '—' }}</td>
                                        <td class="purchaser-col">{{ ((int) $details->qty_to_order - (int) $details->qty_ordered) }}</td>
                                        <td class="purchaser-col">
                                            <input type="number" data-qty="{{ $details->qty_to_order }}" name="qty_delivered{{ $details->id }}" value="{{ $details->qty_delivered }}" class="form-control qty_delivered">
                                        </td>
                                    @endif
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

        {{-- Remarks --}}
        <div class="row">
            <div class="col-lg-3">
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div class="card-icon"><i class="fa fa-comment-o"></i></div>
                        <div><h6>Planner Remarks</h6><p>Notes or instructions for this MRS</p></div>
                    </div>
                    <div class="pa-card-body">
                        <textarea rows="5" class="pa-textarea"
                            @if ($isPlanner) name="planner_remarks" id="planner_remarks" required @else id="planner_remarks_ro" readonly @endif
                            placeholder="{{ $isPlanner ? 'Add note...' : 'No remarks entered.' }}">{{ $sales->planner_remarks }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div class="card-icon"><i class="fa fa-reply"></i></div>
                        <div><h6>Verifier Remarks</h6><p>Notes for the planner</p></div>
                    </div>
                    <div class="pa-card-body">
                        <textarea rows="5" class="pa-textarea"
                            @if ($isVerifier) id="note_verifier" @else id="note_verifier_ro" readonly @endif
                            placeholder="{{ $isVerifier ? 'Add note...' : 'No remarks entered.' }}">{{ $sales->note_verifier }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div class="card-icon"><i class="fa fa-check-square-o"></i></div>
                        <div><h6>Approver Remarks</h6><p>Approval or hold notes</p></div>
                    </div>
                    <div class="pa-card-body">
                        <textarea rows="5" class="pa-textarea"
                            @if ($isApprover) id="note_approver" @else id="note_approver_ro" readonly @endif
                            placeholder="{{ $isApprover ? 'Add note...' : 'No remarks entered.' }}">{{ $sales->note_myrna }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="pa-card">
                    <div class="pa-card-header">
                        <div class="card-icon"><i class="fa fa-reply-all"></i></div>
                        <div><h6>{{ $isPlanner ? 'Note For Requestor' : 'Purchasing Remarks' }}</h6><p>{{ $isPlanner ? 'Sent to the department user on hold' : 'Why the canvasser sent it back' }}</p></div>
                    </div>
                    <div class="pa-card-body">
                        @if ($isPlanner)
                            <textarea rows="5" id="note" class="pa-textarea" placeholder="State what the requestor needs to revise...">{{ $sales->note_planner }}</textarea>
                            @if (!empty($sales->purchaser_note))
                                <label class="pa-label" style="margin-top:14px;">Note From Purchasing</label>
                                <textarea rows="3" class="pa-textarea" style="min-height:60px;" readonly>{{ $sales->purchaser_note }}</textarea>
                            @endif
                        @else
                            <textarea rows="5" class="pa-textarea" readonly placeholder="No return reason entered.">{{ $sales->purchaser_note }}</textarea>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Action bar --}}
        <div class="pa-action-bar">
            @if ($isVerifier)
                @if ($sales->verified_at)
                    <span class="btn-done"><i class="fa fa-check-circle"></i> Verified</span>
                @else
                    <button type="button" id="verifyVerifierBtn" class="btn-pa btn-pa-success"><i class="fa fa-check"></i> Verify</button>
                    <button type="button" id="holdVerifierBtn" class="btn-pa btn-pa-warning"><i class="fa fa-undo"></i> Hold for Revision</button>
                @endif
            @endif

            @if ($isApprover)
                @if ($sales->approved_at)
                    <span class="btn-done"><i class="fa fa-check-circle"></i> Approved</span>
                @else
                    <button type="button" id="approverApproverBtn" class="btn-pa btn-pa-success"><i class="fa fa-thumbs-up"></i> Approve</button>
                    <button type="button" id="holdApproverBtn" class="btn-pa btn-pa-warning"><i class="fa fa-undo"></i> Hold for Revision</button>
                @endif
            @endif

            @if ($isPlanner)
                <button type="submit" class="btn-pa btn-pa-success" {{ $isSubmitted ? 'disabled' : '' }}>
                    <i class="fa fa-paper-plane"></i> {{ $isSubmitted ? 'Submitted' : 'Proceed' }}
                </button>
                @if ($sales->received_at)
                    <button type="submit" class="btn-pa btn-pa-primary"><i class="fa fa-save"></i> Update</button>
                @endif
                <a href="#" id="holdPlannerBtn" class="btn-pa btn-pa-danger"><i class="fa fa-hand-paper-o"></i> Hold &amp; Return to Requestor</a>
            @endif

            @if ($sales->for_pa == 1 && $sales->is_pa == 1)
                <div class="spacer"></div>
                <button type="button" class="btn-pa btn-pa-info print" data-order-number="{{ $sales->order_number }}" {{ $paOnHold ? 'disabled' : '' }}>
                    <i class="fa fa-print"></i> Print PA
                </button>
                @if ($paOnHold)
                    <span class="pa-action-note">Purchase advice on-hold</span>
                @endif
            @endif
        </div>
    </form>

    @if (!empty($approvers))
        <div class="pa-card" style="margin-top:20px;">
            <div class="pa-card-header">
                <div class="card-icon"><i class="fa fa-users"></i></div>
                <div><h6>WFS Approvers</h6><p>Approval trail for this request</p></div>
            </div>
            <div class="pa-card-body">
                <div class="row" style="row-gap:14px;">
                    @foreach ($approvers as $approver)
                        <div class="col-lg-4 col-md-6">
                            <div class="pa-approver {{ $approver['current_seq'] == 1 ? 'is-current' : '' }}">
                                <div class="ap-body">
                                    <p class="ap-name"><i class="fa fa-user-o"></i> [{{ $approver['sequence_number'] }}] {{ $approver['approver_name'] }}</p>
                                    <div class="ap-meta">{{ $approver['designation'] }}</div>
                                    <div class="ap-meta" style="margin-top:4px;">
                                        Responded: {{ $approver['updated_at'] ? $approver['updated_at']->format('M d, Y h:i A') : '—' }}
                                    </div>
                                </div>
                                <span class="ap-status {{ $approver['current_seq'] == 0 && $approver['status'] == 'APPROVED' ? 'is-approved' : '' }}">{{ $approver['status'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @include('admin.components._document-history', [
        'histories' => $sales->histories,
        'title'     => 'MRS History Log',
        'subtitle'  => 'Every change made to MRS ' . $sales->order_number,
    ])

    @include('admin.ecommerce.sales.modals')
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

            $('.print').click(function(evt) {
                evt.preventDefault();

                var orderNumber = this.getAttribute('data-order-number');
                console.log('Print button clicked', orderNumber);
                $('#printModal').modal('show');
                $('#generateReportBtn').click(function() {
                    var selectedFormat = $('input[name="fileFormat"]:checked').val();
                    if (selectedFormat === 'pdf') {
                        $.ajax({
                            url: "{{route('pa.generate_report')}}",
                            type: 'GET',
                            data: { orderNumber: orderNumber },
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
                            },
                            error: function(xhr, status, error) {
                                console.error("Error generating PDF:", error);
                            }
                        });
                    } else if (selectedFormat === 'excel') {
                        window.location.href = "{{ route('pa.generate_report_pa_excel') }}?orderNumber=" + orderNumber;
                    }
                    $('#printModal').modal('hide');
                });
            }); 

            $(".qty_order").on("keyup", function(){
                var qty_order = parseInt($(this).val());
                var qty = parseInt($(this).data('qty'));
                if(qty_order > qty) {
                    $('#toastDynamicError').toast({
                        delay: 3000
                    });
                    $('#errorMessage').html("Quantity to order should not exceed the requested quantity.");
                    $('#toastDynamicError').toast('show');
                    $(this).val(qty)
                }
                if(qty_order <= 0) {
                    $('#toastDynamicError').toast({
                        delay: 3000
                    });
                    $('#errorMessage').html("Quantity to order cannot be zero or negative.");
                    $('#toastDynamicError').toast('show');
                    $(this).val(qty)
                }
            });

            //PLANNERS ACTION
            $('#holdPlannerBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note').val());
                var url = "{{ route('mrs.action', ['action' => 'hold-planner', 'id' => $sales->id]) }}&note=" + note;
                adminConfirm({ title: 'Put this MRS on hold?', confirmButtonText: 'Yes, hold', confirmButtonColor: '#f0ad4e' }, function () {
                    window.location.href = url;
                });
            });
            //

            //VERIFIERS ACTION
            $('#verifyVerifierBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note_verifier').val());
                var url = "{{ route('mrs.action', ['action' => 'verify', 'id' => $sales->id]) }}&note=" + note;
                adminConfirm({ title: 'Verify this MRS?', confirmButtonText: 'Yes, verify', confirmButtonColor: '#2ecc71' }, function () {
                    window.location.href = url;
                });
            });
            $('#holdVerifierBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note_verifier').val());
                var url = "{{ route('mrs.action', ['action' => 'hold', 'id' => $sales->id]) }}&note=" + note;
                adminConfirm({ title: 'Hold and return this MRS to the Planner?', confirmButtonText: 'Yes, hold', confirmButtonColor: '#f0ad4e' }, function () {
                    window.location.href = url;
                });
            });
            //

            //APPROVERS ACTION
            $('#approverApproverBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note_approver').val());
                var url = "{{ route('mrs.action', ['action' => 'approve-approver', 'id' => $sales->id]) }}&note=" + note;
                adminConfirm({ title: 'Approve this MRS?', confirmButtonText: 'Yes, approve', confirmButtonColor: '#2ecc71' }, function () {
                    window.location.href = url;
                });
            });
            $('#holdApproverBtn').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent($('#note_approver').val());
                var url = "{{ route('mrs.action', ['action' => 'hold-approver', 'id' => $sales->id]) }}&note=" + note;
                adminConfirm({ title: 'Hold and return this MRS to the Planner?', confirmButtonText: 'Yes, hold', confirmButtonColor: '#f0ad4e' }, function () {
                    window.location.href = url;
                });
            });
            //

            // Planner PROCEED / UPDATE (generate PA / for verification) — confirm before submit.
            $('#issuanceForm').on('submit', function(event) {
                if ($(this).data('confirmed')) { return; }
                event.preventDefault();
                var form = this;
                adminConfirm({ title: 'Submit this MRS for verification?', confirmButtonText: 'Yes, proceed', confirmButtonColor: '#2ecc71' }, function () {
                    $(form).data('confirmed', true);
                    form.submit();
                });
            });
        });
    </script>
@endsection