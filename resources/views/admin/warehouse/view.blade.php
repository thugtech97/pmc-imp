@extends('admin.layouts.app')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('lib/sweetalert2/sweetalert.min.css') }}" type="text/css">
    @include('admin.components._pa-design-system')
    <style>
        /* Inline stand-in for the Hold column, which this screen does not show. */
        .wh-hold-tag {
            display: inline-block;
            margin-left: 6px;
            padding: 1px 7px;
            border-radius: 20px;
            background: var(--pa-danger-light);
            color: var(--pa-danger);
            border: 1px solid #fecaca;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            vertical-align: middle;
        }

        /* Lines purchasing has not ordered yet: visible, but locked for delivery entry.
           Amber keeps them distinct from the grey row-held treatment. */
        .pa-table tbody tr.row-not-ordered > td { background: #fffbeb; }
        .pa-table tbody tr.row-not-ordered:hover > td { background: #fef6dc; }
        .pa-table tbody tr.row-not-ordered > td:first-child { box-shadow: inset 3px 0 0 var(--pa-warning); }

        .wh-notordered-tag {
            display: inline-block;
            margin-left: 6px;
            padding: 1px 7px;
            border-radius: 20px;
            background: var(--pa-warning-light);
            color: var(--pa-warning);
            border: 1px solid #fde68a;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            vertical-align: middle;
        }

        /* Persisted "Delivered" marker on the delivery progress card. */
        .wh-delivered-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            background: var(--pa-success-light);
            color: var(--pa-success);
            border: 1px solid #a7f3d0;
            font-size: 11.5px;
            font-weight: 700;
        }

        .wh-delivered-note {
            margin: 14px 0 0;
            font-size: 11.5px;
            color: var(--pa-text-muted);
        }
    </style>
@endsection

@section('content')
@php
    // Status badge — same resolution/tone rules as the MCD MRS screen so the two read alike.
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
    $itemCols = 13;

    // Delivery totals ignore held lines, matching totalQtyOrdered()/totalQtyDelivered().
    $totalOrdered   = $sales->totalQtyOrdered();
    $totalDelivered = $sales->totalQtyDelivered();
    $totalBalance   = $totalOrdered - $totalDelivered;

    $paOnHold   = optional($sales->purchaseAdvice)->is_hold == 1;
    $canIssue   = (bool) $sales->received_at;

    // The persisted marker, stamped by WarehouseController::update() once every ordered
    // qty has been delivered. Distinct from the derived label next to it.
    $isDelivered      = $sales->delivery_status === 'Delivered';
    $deliveryMarkedAt = $deliveryMarkedAt ?? null;

    // Lines purchasing has not ordered yet have nothing to receive against, so they are shown
    // but locked. If none of the lines are orderable there is nothing to save.
    $deliverableCount = 0;
    foreach ($salesDetails as $d) {
        if ((float) $d->qty_ordered > 0 && (int) $d->promo_id !== 1) {
            $deliverableCount++;
        }
    }
    $hasDeliverables = $deliverableCount > 0;
@endphp

<div class="container-fluid" style="max-width: 1600px;">

    <div class="pa-page-header d-flex align-items-start justify-content-between flex-wrap" style="gap:14px;">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">CMS</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('warehouse_mrs.index') }}">Warehouse MRS</a></li>
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
                <a href="{{ route('warehouse_mrs.index') }}" class="btn-pa btn-pa-secondary"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <span class="pa-status-badge {{ $statusClass }}"><i class="fa fa-circle"></i> {{ $status }}</span>
        </div>
    </div>

    @if (!$canIssue)
        <div class="pa-notice notice-warning">
            <i class="fa fa-clock-o notice-icon"></i>
            <div>
                <div class="notice-title">Not yet received by Purchasing</div>
                <div class="notice-body">Delivered quantities can only be recorded once this MRS has been received for canvass.</div>
            </div>
        </div>
    @endif

    @include('admin.purchasing.components._mrs-summary-cards', ['sales' => $sales, 'attachments' => $attachments])

    {{-- Delivery progress --}}
    <div class="pa-card">
        <div class="pa-card-header">
            <div class="card-icon"><i class="fa fa-truck"></i></div>
            <div><h6>Delivery Progress</h6><p>Totals across all items not on hold</p></div>
        </div>
        <div class="pa-card-body">
            <div class="pa-meta-grid">
                <div class="pa-meta-item">
                    <div class="meta-label">Total Qty Ordered</div>
                    <div class="meta-value">{{ (int) $totalOrdered }}</div>
                </div>
                <div class="pa-meta-item">
                    <div class="meta-label">Total Qty Delivered</div>
                    <div class="meta-value">{{ (int) $totalDelivered }}</div>
                </div>
                <div class="pa-meta-item">
                    <div class="meta-label">Balance</div>
                    <div class="meta-value {{ $totalBalance <= 0 ? '' : 'empty' }}">{{ (int) $totalBalance }}</div>
                </div>
                <div class="pa-meta-item">
                    <div class="meta-label">Fulfillment</div>
                    <div class="meta-value">{{ $sales->getDeliveryStatusLabel() }}</div>
                </div>
                <div class="pa-meta-item">
                    <div class="meta-label">Delivery Status</div>
                    <div class="meta-value">
                        @if ($isDelivered)
                            <span class="wh-delivered-tag"><i class="fa fa-check-circle"></i> {{ $sales->delivery_status }}</span>
                        @else
                            {{ $sales->delivery_status ?: '—' }}
                        @endif
                    </div>
                </div>
            </div>
            @if ($isDelivered && $deliveryMarkedAt)
                <p class="wh-delivered-note">
                    Marked delivered {{ $deliveryMarkedAt->created_at->format('M d, Y h:i A') }}
                    by {{ optional($deliveryMarkedAt->user)->name ?? 'Unknown user' }}.
                </p>
            @endif
        </div>
    </div>

    <form id="issuanceForm" method="POST" action="{{ route('warehouse_mrs.update-qty-delivered') }}">
        @csrf
        @method('POST')
        <input type="hidden" name="sales_header_id" value="{{ $sales->id }}">

        {{-- Items --}}
        <div class="pa-card">
            <div class="pa-card-header">
                <div class="card-icon"><i class="fa fa-list"></i></div>
                <div>
                    <h6>Items</h6>
                    <p>
                        {{ count($salesDetails) }} item(s) in this request
                        @if (count($salesDetails) > $deliverableCount)
                            &mdash; {{ $deliverableCount }} open for delivery entry
                        @endif
                    </p>
                </div>
            </div>
            <div class="pa-card-body" style="padding:0;">
                <div class="pa-table-wrapper">
                    <table class="pa-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                {{-- Delivery columns lead the table: they are what this screen is for. --}}
                                <th style="min-width:100px;" class="purchaser-col">Qty Ordered</th>
                                <th style="min-width:130px;" class="purchaser-col">Qty Delivered</th>
                                <th style="min-width:80px;">Priority#</th>
                                <th style="min-width:110px;">Stock Code</th>
                                <th style="min-width:300px;">Item</th>
                                <th style="min-width:70px;">UoM</th>
                                <th style="min-width:110px;">OEM No.</th>
                                <th style="min-width:110px;">Cost Code</th>
                                <th style="min-width:110px;">Qty To Order</th>
                                <th style="min-width:120px;">Previous PO#</th>
                                <th style="min-width:110px;">Current PO#</th>
                                <th style="min-width:130px;">PO Date Released</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 0; @endphp
                            @forelse($salesDetails as $details)
                                @php
                                    $count++;
                                    $held = (int) $details->promo_id === 1;

                                    // Purchasing has not ordered this line yet, so there is no
                                    // quantity to receive against it.
                                    $notOrdered = (float) $details->qty_ordered <= 0;
                                @endphp
                                <input type="hidden" name="ecommerce_sales_details_id{{ $details->id }}" value="{{ $details->id }}">
                                <input type="hidden" name="ordered_qty{{ $details->id }}" value="{{ $details->qty }}">

                                <tr class="{{ $held ? 'row-held' : ($notOrdered ? 'row-not-ordered' : '') }}">
                                    <td><span class="row-num">{{ $count }}</span></td>
                                    <td class="purchaser-col">{{ (int) $details->qty_ordered }}</td>
                                    <td class="purchaser-col">
                                        <input type="number" data-qty="{{ (int) $details->qty_ordered }}" name="qty_delivered{{ $details->id }}" value="{{ $details->qty_delivered }}" class="form-control qty_delivered"
                                            {{ $canIssue && !$held && !$notOrdered ? '' : 'disabled' }}
                                            title="{{ $notOrdered ? 'Purchasing has not ordered this item yet.' : '' }}">
                                    </td>
                                    <td>{{ $sales->priority }}</td>
                                    <td class="mono">{{ $details->product->code ?? 'N/A' }}</td>
                                    <td style="font-weight:500;">
                                        {{ $details->product->name ?? 'N/A' }}
                                        {{-- No Hold column here, so held lines say so inline — otherwise
                                             their disabled Qty Delivered box looks like a bug. --}}
                                        @if ($held)
                                            <span class="wh-hold-tag" title="{{ $details->promo_description ?: 'On hold' }}">On hold</span>
                                        @elseif ($notOrdered)
                                            <span class="wh-notordered-tag" title="Purchasing has not ordered this item yet.">Not yet ordered</span>
                                        @endif
                                    </td>
                                    <td>{{ $details->product->uom ?? 'N/A' }}</td>
                                    <td>{{ $details->product->oem ?? 'N/A' }}</td>
                                    <td>{{ $details->cost_code }}</td>
                                    <td>{{ $details->qty_to_order > 0 ? (int) $details->qty_to_order : (int) $details->qty }}</td>
                                    <td class="mono">{{ $details->previous_mrs ?: '—' }}</td>
                                    <td class="mono">{{ $details->po_no ?: '—' }}</td>
                                    <td>{{ $details->po_date_released ? \Carbon\Carbon::parse($details->po_date_released)->format('m/d/Y') : '—' }}</td>
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

        {{-- Action bar --}}
        <div class="pa-action-bar">
            @if ($canIssue)
                <button type="submit" class="btn-pa btn-pa-success" {{ $paOnHold || !$hasDeliverables ? 'disabled' : '' }}>
                    <i class="fa fa-save"></i> {{ $sales->response_code ? 'Update Delivered Qty' : 'Submit Delivered Qty' }}
                </button>
                @if ($paOnHold)
                    <span class="pa-action-note">Purchase advice on-hold</span>
                @endif
            @else
                <span class="btn-done"><i class="fa fa-lock"></i> Awaiting Purchasing</span>
            @endif
        </div>
    </form>

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

        $(document).ready(function() {
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

            // Delivered qty can never exceed what was ordered from the supplier.
            $(".qty_delivered").on("keyup change", function(){
                var delivered = parseInt($(this).val());
                var ordered = parseInt($(this).data('qty'));
                if (delivered > ordered) {
                    $('#errorMessage').html("Quantity delivered should not exceed the quantity ordered.");
                    $('#toastDynamicError').toast({ delay: 3000 });
                    $('#toastDynamicError').toast('show');
                    $(this).val(ordered);
                }
                if (delivered < 0) {
                    $(this).val(0);
                }
            });

            // Confirm before saving delivered quantities.
            $('#issuanceForm').on('submit', function(event) {
                if ($(this).data('confirmed')) { return; }
                event.preventDefault();
                var form = this;
                adminConfirm({ title: 'Save the delivered quantities?', confirmButtonText: 'Yes, save', confirmButtonColor: '#2ecc71' }, function () {
                    $(form).data('confirmed', true);
                    form.submit();
                });
            });
        });
    </script>
@endsection
