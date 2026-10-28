{{--
    Shared MRS (PA-DP) header cards — reference numbers, timeline, requestor details and
    attachments. Included by every PA-DP screen (MCD Planner / Verifier / Approver,
    Purchasing Officer, Canvasser) so they stay identical to each other and to PA SR.

    Expects: $sales (SalesHeader), $attachments (array of storage paths).
--}}
<div class="row">
    <div class="col-lg-4">
        <div class="pa-card">
            <div class="pa-card-header">
                <div class="card-icon"><i class="fa fa-file-text-o"></i></div>
                <div><h6>MRS Reference</h6><p>Request and purchase advice numbers</p></div>
            </div>
            <div class="pa-card-body">
                <label class="pa-label">MRS Number</label>
                <div class="pa-number-display">
                    <i class="fa fa-hashtag pa-number-icon"></i>
                    <span class="pa-number-value">{{ $sales->order_number }}</span>
                </div>
                <label class="pa-label" style="margin-top:16px;">PA Number</label>
                <div class="pa-number-display">
                    <i class="fa fa-hashtag pa-number-icon"></i>
                    <span class="pa-number-value">{{ optional($sales->purchaseAdvice)->pa_number ?? 'Not yet generated' }}</span>
                    @if (optional($sales->purchaseAdvice)->revision > 0)
                        <span class="pa-rev-badge">Rev{{ (int) $sales->purchaseAdvice->revision }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="pa-card">
            <div class="pa-card-header">
                <div class="card-icon"><i class="fa fa-info-circle"></i></div>
                <div><h6>MRS Timeline</h6><p>Approval and processing status</p></div>
            </div>
            <div class="pa-card-body">
                <div class="pa-meta-grid">
                    <div class="pa-meta-item">
                        <div class="meta-label">Request Date</div>
                        <div class="meta-value">{{ $sales->created_at ? \Carbon\Carbon::parse($sales->created_at)->format('M d, Y') : '—' }}</div>
                    </div>
                    <div class="pa-meta-item">
                        <div class="meta-label">Planner At</div>
                        <div class="meta-value {{ !$sales->planner_at ? 'empty' : '' }}">{{ $sales->planner_at ? \Carbon\Carbon::parse($sales->planner_at)->format('M d, Y') : 'Not yet processed' }}</div>
                    </div>
                    <div class="pa-meta-item">
                        <div class="meta-label">Verified At</div>
                        <div class="meta-value {{ !$sales->verified_at ? 'empty' : '' }}">{{ $sales->verified_at ? \Carbon\Carbon::parse($sales->verified_at)->format('M d, Y') : 'Not yet verified' }}</div>
                    </div>
                    <div class="pa-meta-item">
                        <div class="meta-label">Approved At</div>
                        <div class="meta-value {{ !$sales->approved_at ? 'empty' : '' }}">{{ $sales->approved_at ? \Carbon\Carbon::parse($sales->approved_at)->format('M d, Y') : 'Not yet approved' }}</div>
                    </div>
                    <div class="pa-meta-item">
                        <div class="meta-label">Received At</div>
                        <div class="meta-value {{ !$sales->received_at ? 'empty' : '' }}">{{ $sales->received_at ? \Carbon\Carbon::parse($sales->received_at)->format('M d, Y') : 'Not yet received' }}</div>
                    </div>
                    <div class="pa-meta-item">
                        <div class="meta-label">Canvasser</div>
                        <div class="meta-value {{ !$sales->purchaser ? 'empty' : '' }}">{{ $sales->purchaser->name ?? 'Unassigned' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="pa-card">
    <div class="pa-card-header">
        <div class="card-icon"><i class="fa fa-user-o"></i></div>
        <div><h6>Request Details</h6><p>Who raised this MRS and how it should be delivered</p></div>
    </div>
    <div class="pa-card-body">
        <div class="pa-meta-grid">
            <div class="pa-meta-item">
                <div class="meta-label">Department</div>
                <div class="meta-value">{{ optional(optional($sales->user)->department)->name ?? 'N/A' }}</div>
            </div>
            <div class="pa-meta-item">
                <div class="meta-label">Section</div>
                <div class="meta-value {{ !$sales->section ? 'empty' : '' }}">{{ $sales->section ?: '—' }}</div>
            </div>
            <div class="pa-meta-item">
                <div class="meta-label">Requested By</div>
                <div class="meta-value {{ !$sales->requested_by ? 'empty' : '' }}">{{ $sales->requested_by ?: '—' }}</div>
            </div>
            <div class="pa-meta-item">
                <div class="meta-label">Processed By</div>
                <div class="meta-value">{{ optional($sales->user)->name ?? 'N/A' }}</div>
            </div>
            <div class="pa-meta-item">
                <div class="meta-label">Date Needed</div>
                <div class="meta-value {{ !$sales->delivery_date ? 'empty' : '' }}">{{ $sales->delivery_date ?: '—' }}</div>
            </div>
            <div class="pa-meta-item">
                <div class="meta-label">Delivery Type</div>
                <div class="meta-value {{ !$sales->delivery_type ? 'empty' : '' }}">{{ $sales->delivery_type ?: '—' }}</div>
            </div>
            <div class="pa-meta-item">
                <div class="meta-label">Delivery Address</div>
                <div class="meta-value {{ !$sales->customer_delivery_adress ? 'empty' : '' }}">{{ $sales->customer_delivery_adress ?: '—' }}</div>
            </div>
            <div class="pa-meta-item">
                <div class="meta-label">Budgeted</div>
                <div class="meta-value">{{ $sales->budgeted_amount > 0 ? 'YES — ' . number_format($sales->budgeted_amount, 2, '.', ',') : 'NO' }}</div>
            </div>
            <div class="pa-meta-item">
                <div class="meta-label">Other Instructions</div>
                <div class="meta-value {{ !$sales->other_instruction ? 'empty' : '' }}">{{ $sales->other_instruction ?: '—' }}</div>
            </div>
            <div class="pa-meta-item">
                <div class="meta-label">Note</div>
                <div class="meta-value {{ !$sales->purpose ? 'empty' : '' }}">{{ $sales->purpose ?: '—' }}</div>
            </div>
        </div>
    </div>
</div>

@if (count($attachments))
    <div class="pa-card">
        <div class="pa-card-header">
            <div class="card-icon"><i class="fa fa-paperclip"></i></div>
            <div><h6>Supporting Documents</h6><p>Attached files for this request</p></div>
        </div>
        <div class="pa-card-body">
            <ul class="doc-list">
                @foreach ($attachments as $file)
                    <li><a href="{{ asset('storage/' . $file) }}" download><i class="fa fa-file-o"></i> {{ basename($file) }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
