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
    $itemCols = 14;
    $paOnHold = optional($sales->purchaseAdvice)->is_hold == 1;
    $canEdit  = $sales->received_at && !$paOnHold;
@endphp

<div class="container-fluid" style="max-width: 1600px;">

    <div class="pa-page-header d-flex align-items-start justify-content-between flex-wrap" style="gap:14px;">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">CMS</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('purchaser.index') }}">Order Transaction</a></li>
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
                <a href="{{ route('purchaser.index') }}" class="btn-pa btn-pa-secondary"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <span class="pa-status-badge {{ $statusClass }}"><i class="fa fa-circle"></i> {{ $status }}</span>
        </div>
    </div>

    @if ($paOnHold)
        <div class="pa-notice notice-warning">
            <i class="fa fa-pause-circle-o notice-icon"></i>
            <div>
                <div class="notice-title">Purchase advice on-hold</div>
                <div class="notice-body">This PA is with the MCD Planner for re-edit. It will come back to you for canvass once they are done.</div>
            </div>
        </div>
    @endif

    @include('admin.purchasing.components._mrs-summary-cards', ['sales' => $sales, 'attachments' => $attachments])

    <form id="issuanceForm" method="POST" action="{{ route('purchaser.receive') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="sales_header_id" value="{{ $sales->id }}">

        {{-- Items --}}
        <div class="pa-card">
            <div class="pa-card-header">
                <div class="card-icon"><i class="fa fa-list"></i></div>
                <div><h6>Items</h6><p>{{ count($salesDetails) }} item(s) to canvass</p></div>
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
                                <th style="min-width:110px;">Qty To Order</th>
                                <th style="min-width:120px;">Previous PO#</th>
                                <th style="min-width:120px;" class="purchaser-col">Current PO#</th>
                                <th style="min-width:130px;" class="purchaser-col">PO Date Released</th>
                                <th style="min-width:110px;" class="purchaser-col">Qty Ordered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 0; @endphp
                            @forelse($salesDetails as $details)
                                @php
                                    $count++;
                                    $held = (int) $details->promo_id === 1;
                                    // Held lines are excluded from canvass, so their PO fields stay locked.
                                    $lineEditable = $sales->received_at && $details->promo_id === '0';
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
                                    <td class="mono">{{ $details->product->code ?? 'N/A' }}</td>
                                    <td style="font-weight:500;">{{ $details->product->name ?? 'N/A' }}</td>
                                    <td>{{ $details->product->uom ?? 'N/A' }}</td>
                                    <td>{{ $details->product->oem ?? 'N/A' }}</td>
                                    <td>{{ $details->cost_code }}</td>
                                    <td>
                                        <input type="number" name="quantityToOrder{{ $details->id }}" value="{{ $details->qty_to_order > 0 ? (int) $details->qty_to_order : (int) $details->qty }}" class="form-control" {{ $role->name !== 'MCD Planner' ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <input type="text" name="previous_no{{ $details->id }}" value="{{ $details->previous_mrs }}" class="form-control" disabled>
                                    </td>
                                    <td class="purchaser-col">
                                        <input type="text" name="po_no{{ $details->id }}" value="{{ $details->po_no }}" class="form-control" {{ $lineEditable ? '' : 'disabled' }}>
                                    </td>
                                    <td class="purchaser-col">
                                        <input type="date" name="po_date_released{{ $details->id }}" value="{{ $details->po_date_released ? \Carbon\Carbon::parse($details->po_date_released)->format('Y-m-d') : '' }}" class="form-control" {{ $lineEditable ? '' : 'disabled' }}>
                                    </td>
                                    <td class="purchaser-col">
                                        <input type="number" data-qty="{{ $details->qty_to_order }}" name="qty_ordered{{ $details->id }}" value="{{ $details->qty_ordered }}" class="form-control qty_ordered" {{ $lineEditable ? '' : 'disabled' }}>
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

        {{-- Remarks --}}
        @if ($sales->received_at)
            <div class="row">
                <div class="col-lg-6">
                    <div class="pa-card">
                        <div class="pa-card-header">
                            <div class="card-icon"><i class="fa fa-reply-all"></i></div>
                            <div><h6>Note For Planner</h6><p>Required when returning this MRS for re-edit</p></div>
                        </div>
                        <div class="pa-card-body">
                            <textarea rows="5" id="note" class="pa-textarea" placeholder="State why you are returning this to the MCD Planner..." {{ $paOnHold ? 'disabled' : '' }}>{{ $sales->purchaser_note }}</textarea>
                        </div>
                    </div>
                </div>
                @if ($sales->planner_remarks)
                    <div class="col-lg-6">
                        <div class="pa-card">
                            <div class="pa-card-header">
                                <div class="card-icon"><i class="fa fa-comment-o"></i></div>
                                <div><h6>Planner Remarks</h6><p>Instructions from the MCD Planner</p></div>
                            </div>
                            <div class="pa-card-body">
                                <textarea rows="5" class="pa-textarea" readonly>{{ $sales->planner_remarks }}</textarea>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Action bar --}}
        <div class="pa-action-bar">
            @if ($sales->received_at)
                <span class="btn-done"><i class="fa fa-check-circle"></i> Received</span>
            @else
                <button type="button" id="receivePurchaser" class="btn-pa btn-pa-success"><i class="fa fa-inbox"></i> Receive</button>
            @endif

            @if ($sales->received_at)
                <button type="submit" class="btn-pa btn-pa-primary" {{ $paOnHold ? 'disabled' : '' }}>
                    <i class="fa fa-save"></i> {{ $sales->response_code ? 'Update' : 'Submit' }}
                </button>
                <div class="spacer"></div>
                <button type="button" id="holdPurchaserBtn" class="btn-pa btn-pa-warning" {{ $paOnHold ? 'disabled' : '' }}>
                    <i class="fa fa-undo"></i> Hold &amp; Return to Planner
                </button>
                @if ($paOnHold)
                    <span class="pa-action-note">Purchase advice on-hold</span>
                @endif
            @endif
        </div>
    </form>
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
            //employee_lookup();
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

            $('#receivePurchaser').click(function(event) {
                event.preventDefault(); // Prevent the default link click behavior
                var note = encodeURIComponent("No-Note");
                var url = "{{ route('mrs.action', ['action' => 'purchaser-receive', 'id' => $sales->id]) }}&note=" + note;
                adminConfirm({ title: 'Receive this MRS for canvass?', confirmButtonText: 'Yes, receive', confirmButtonColor: '#2ecc71' }, function () {
                    window.location.href = url;
                });
            });

            $(".qty_ordered").on("keyup", function(){
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
                /*
                if(qty_order <= 0) {
                    $('#toastDynamicError').toast({
                        delay: 3000
                    });
                    $('#errorMessage').html("Quantity to order cannot be zero or negative.");
                    $('#toastDynamicError').toast('show');
                    $(this).val(qty)
                }
                */
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
                adminConfirm({
                    title: 'Return this to the MCD Planner for re-edit?',
                    text: 'Once the planner re-edits it, it comes straight back to you for canvass.',
                    icon: 'warning',
                    confirmButtonText: 'Yes, hold',
                    confirmButtonColor: '#f0ad4e'
                }, function () {
                    window.location.href = url;
                });
            });

            // Confirm before submitting received PO details.
            $('#issuanceForm').on('submit', function(event) {
                if ($(this).data('confirmed')) { return; }
                event.preventDefault();
                var form = this;
                adminConfirm({ title: 'Save these details?', confirmButtonText: 'Yes, save', confirmButtonColor: '#2ecc71' }, function () {
                    $(form).data('confirmed', true);
                    form.submit();
                });
            });
        });
    </script>
@endsection