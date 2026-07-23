@php
    $paths = '';
    foreach (explode('|', $sale->order_source) as $filePath) {
        if (trim($filePath) === '') { continue; }
        $paths .= '<a href="' . asset('storage/' . $filePath) . '" target="_blank" style="display: block; margin-bottom: 5px;">
                        <i class="icon-download-alt" style="margin-right: 5px;"></i>
                        ' . basename($filePath) . '
                    </a>';
    }
@endphp
<form method="post" class="m-0" action="{{ route('my-account.update.order', $sale->id) }}">
    @csrf
    <input type="hidden" name="_method" value="PUT">

    <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">MRS No. {{ $sale->order_number }}</h4>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="request-details">
                    <span><strong>Request Date:</strong> <span class="detail-value">{{ $sale->created_at }}</span></span>
                    <span><strong>Request Status:</strong> <span class="detail-value">{{ strtoupper($sale->status) }}</span></span>
                    <span><strong>Department:</strong> <span class="detail-value">{{ auth()->user()->department->name ?? '' }}</span></span>
                    <span><strong>Section:</strong> <span class="detail-value">{{ $sale->section }}</span></span>
                    <span><strong>Date Needed:</strong> <span class="detail-value">{{ $sale->delivery_date }}</span></span>
                    <span><strong>Requested By:</strong> <span class="detail-value">{{ $sale->requested_by }}</span></span>
                    <span><strong>Processed By:</strong> <span class="detail-value">{{ strtoupper($sale->user->name ?? '') }}</span></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="request-details">
                    <span><strong>Delivery Type:</strong> <span class="detail-value">{{ $sale->delivery_type }}</span></span>
                    <span><strong>Delivery Address:</strong> <span class="detail-value">{{ $sale->customer_delivery_adress }}</span></span>
                    <span><strong>Budgeted:</strong> <span class="detail-value">{{ $sale->budgeted_amount > 0 ? 'YES' : 'NO' }}</span></span>
                    <span><strong>Budgeted Amount:</strong> <span class="detail-value">{{ number_format($sale->budgeted_amount, 2, '.', ',') }}</span></span>
                    <span><strong>Other Instructions:</strong> <span class="detail-value">{{ $sale->other_instruction }}</span></span>
                    <span><strong>Note:</strong> <span class="detail-value">{{ $sale->purpose }}</span></span>
                    <span><strong>Attachment:</strong>
                        <span class="detail-value">
                            {!! $paths !!}
                        </span>
                    </span>
                </div>
            </div>
        </div>
        <div class="gap-20"></div>

        @if ($sale->note_planner || $sale->note_verifier)
            <div style="margin: 0 0 18px 0; padding: 14px 18px; background: #fff8e1; border: 1px solid #ffe082; border-left: 5px solid #ff9800; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                <div style="display:flex; align-items:flex-start;">
                    <i class="icon-info-sign" style="color:#ff9800; font-size:20px; margin-right:10px; line-height:1.4;"></i>
                    <div>
                        <div style="font-weight:700; text-transform:uppercase; letter-spacing:0.5px; font-size:12px; color:#e65100; margin-bottom:4px;">MCD Planner Note</div>
                        <div style="color:#5d4037; font-size:14px; line-height:1.5;">{!! $sale->note_planner ?: '<em style="color:#9e9e9e;">No note provided.</em>' !!}</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="table-modal-wrap">
            <table class="table table-md table-modal" style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px; margin: 20px 0;">
                <thead>
                    <tr style="background-color: #f2f2f2; color: #333; border-bottom: 2px solid #ccc;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">ITEM #</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Priority</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Stock Code</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">OEM</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">UoM</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">PAR To</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Frequency</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Date Needed</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Cost Code</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Requested Qty</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Delivered Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $index => $item)
                        @php $holdClass = $item->promo_id == 1 ? 'mrs-hold-row' : ''; @endphp
                        <tr class="{{ $holdClass }}" style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $index + 1 }}</td>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $sale->priority }}</td>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item->product->code ?? 'NONE' }}</td>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item->product->name ?? 'NONE' }}</td>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item->product->oem ?? 'NONE' }}</td>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item->product->uom ?? 'NONE' }}</td>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ explode(':', $item->par_to)[0] ?: 'NONE' }}</td>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item->frequency }}</td>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($item->date_needed)->format('m/d/Y') }}</td>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $item->cost_code }}</td>
                            <td style="padding: 10px; text-align: right; border: 1px solid #ddd;">{{ (int) $item->qty }}</td>
                            <td style="padding: 10px; text-align: right; border: 1px solid #ddd;">{{ (int) $item->qty_delivered }}</td>
                        </tr>
                        <tr class="{{ $holdClass }}" style="border-bottom: 1px solid #ddd;">
                            <th style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="3">PURPOSE</th>
                            <td style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="9">{{ $item->purpose }}</td>
                        </tr>
                        @if ($item->promo_id == 1)
                            <tr class="{{ $holdClass }}" style="border-bottom: 1px solid #ddd;">
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="3">HOLD REMARKS</th>
                                <td style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="9">{{ $item->promo_description }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row">
            <h5>Approvers</h5>
        </div>

        <div class="row">
            @if (!empty($sale->approvers) && count($sale->approvers) > 0)
                @foreach ($sale->approvers as $approver)
                    @php
                        $isCurrentPending = ($approver['current_seq'] ?? null) == 1 && empty($approver['updated_at']);
                        $st = strtoupper($approver['status'] ?? '');
                        $badgeBg = $st === 'APPROVED' ? 'rgb(57, 134, 57)' : ($st === 'CANCELLED' ? 'rgb(219, 83, 83)' : 'rgb(149, 149, 149)');
                        $responded = !empty($approver['updated_at'])
                            ? (is_string($approver['updated_at']) ? $approver['updated_at'] : $approver['updated_at']->format('F d, Y h:i A'))
                            : '';
                    @endphp
                    <div class="col-lg-4 col-md-6">
                        <div class="card dashboard-widget {{ $isCurrentPending ? 'bg-light' : '' }}">
                            <div class="card-body">
                                <h6 class="tx-bold tx-uppercase mg-b-5 lh-1">
                                    <i data-feather="user" class="mg-r-6"></i> [{{ $approver['sequence_number'] ?? '' }}] {{ $approver['approver_name'] ?? '' }}
                                    <span class="tx-normal">({{ $approver['designation'] ?? '' }})</span>
                                </h6>
                                <span class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold">
                                    Date Responded: {{ $responded }}<br>
                                    Response Aging: N/A
                                </span>
                            </div>
                            <span class="text-center text-white" style="background-color:{{ $badgeBg }}">{{ $approver['status'] ?? '' }}</span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="gap-20"></div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</form>
