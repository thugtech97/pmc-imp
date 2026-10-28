<div class="modal effect-scale" id="prompt-delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="" id="frm_delete" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{__('common.delete_confirmation_title')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                        @csrf
                        @method('DELETE ')
                    <input type="hidden" name="id_delete" id="id_delete">
                    <p>Are you sure you want to delete this transaction no: <span id="delete_order_div"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-danger" id="btnDelete">Yes, Cancel</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal effect-scale" id="prompt-multiple-delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('common.delete_mutiple_confirmation_title')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{__('common.delete_mutiple_confirmation')}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" id="btnDeleteMultiple">Yes, Delete</button>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal effect-scale" id="prompt-no-selected" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('common.no_selected_title')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{__('common.no_selected')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal effect-scale" id="prompt-change-delivery-status" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('Delivery Status')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="dd_form" method="POST" action="{{route('sales-transaction.delivery_status')}}">
                @csrf
                @method('POST')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="delivery_status">Status</label>
                        <select id="delivery_status" class="custom-select mg-b-5" name="delivery_status" data-style="btn btn-outline-light btn-md btn-block tx-left" title="- None -" data-width="100%" required="required">
                            <option value="Scheduled for Processing">Scheduled for Processing</option>
                            <option value="Processing">Processing</option>
                            <option value="Ready For delivery">Ready For delivery</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Returned">Returned</option>
                            <option value="On Hold (Out of Stock/For Purchase)">On Hold (Out of Stock/For Purchase)</option>
                        </select>
                        <p class="tx-10 text-danger" id="error">
                            @error('delivery_status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </p>
                    </div>
                    <div class="form-group">
                        <label for="delivery_status">Remarks</label>
                        <textarea name="del_remarks" class="form-control" id="del_remarks" cols="30" rows="4"></textarea>
                    </div>
                </div>
                <input type="hidden" id="del_id" name="del_id" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>

        </div>
    </div>
</div>


<div class="modal effect-scale" id="prompt-change-delivery-status-delivered" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('Delivery Status')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="dd_form" method="POST" action="{{route('sales-transaction.delivery_status')}}">
                @csrf
                @method('POST')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="delivery_status">Status</label>
                        <select id="delivery_status" class="custom-select mg-b-5" name="delivery_status" data-style="btn btn-outline-light btn-md btn-block tx-left" title="- None -" data-width="100%" required="required">
                            <option value="Scheduled for Processing">Scheduled for Processing</option>
                            <option value="Processing">Processing</option>
                            <option value="Ready For delivery">Ready For delivery</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Delivered">Delivered</option>
                            <option value="Returned">Returned</option>
                            <option value="On Hold (Out of Stock/For Purchase)">On Hold (Out of Stock/For Purchase)</option>
                        </select>
                        <p class="tx-10 text-danger" id="error">
                            @error('delivery_status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </p>
                    </div>
                    <div class="form-group">
                        <label for="delivery_status">Remarks</label>
                        <textarea name="del_remarks" class="form-control" id="del_remarks" cols="30" rows="4"></textarea>
                    </div>
                </div>
                <input type="hidden" id="del_id" name="del_id" value="">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>

        </div>
    </div>
</div>


<div class="modal effect-scale" id="prompt-change-status" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" class="form-control" name="id" id="id">
                    <input type="hidden" class="form-control" name="status" id="editStatus">
                    <div class="form-group">
                        <label class="d-block">Payment source *</label>
                        <select id="payment_type" class="selectpicker mg-b-5" name="payment_type" data-style="btn btn-outline-light btn-md btn-block tx-left" title="- None -" data-width="100%">
                            <option value="Gift Certificate">Gift Certificate</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Cash">Cash</option>
                        </select>
                        <p class="tx-10 text-danger" id="error">
                            @error('payment_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="d-block">Amount *</label>
                        <input type="text" class="form-control" name="amount" id="amount">
                        <p class="tx-10 text-danger" id="error">
                            @error('amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="d-block">Payment date *</label>
                        <input type="date" class="form-control" name="payment_date" id="payment_date">
                        <p class="tx-10 text-danger" id="error">
                            @error('payment_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="d-block">Receipt number *</label>
                        <input type="text" class="form-control" name="receipt_number" id="receipt_number">
                        <p class="tx-10 text-danger" id="error">
                            @error('receipt_number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-primary">Update</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal effect-scale" id="prompt-show-added-payments" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Added Payments</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th>Reference #</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </thead>
                        <tbody id="added_payments_tbl">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal effect-scale" id="prompt-show-delivery-history" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Delivery History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </thead>
                        <tbody id="delivery_history_tbl">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal effect-scale" id="show-generate-mrs" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="generate-mrs-form" action="{{ route('mrs.generate_mrs_transactions') }}" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Generate MRS Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <label for="startdate" class="col-2 col-form-label text-end">Start:</label>
                        <div class="col-10">
                            <input
                                required 
                                name="startdate" 
                                type="date" 
                                id="startdate" 
                                class="form-control form-control-sm" 
                                style="width: 140px;" 
                                value=""
                            >
                        </div>
                    </div>
                    <div class="row">
                        <label for="enddate" class="col-2 col-form-label text-end">End:</label>
                        <div class="col-10">
                            <input 
                                required
                                name="enddate" 
                                type="date" 
                                id="enddate" 
                                class="form-control form-control-sm" 
                                style="width: 140px;" 
                                value=""
                            >
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary" id="btnGenerateMRS">Generate</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal effect-scale" id="show-generate-imf" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="generate-imf-form" action="{{ route('imf.generate_imf_transactions') }}" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Generate IMF Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <label for="startdate" class="col-2 col-form-label text-end">Start:</label>
                        <div class="col-10">
                            <input
                                required 
                                name="startdate" 
                                type="date" 
                                id="startdate" 
                                class="form-control form-control-sm" 
                                style="width: 140px;" 
                                value=""
                            >
                        </div>
                    </div>
                    <div class="row">
                        <label for="enddate" class="col-2 col-form-label text-end">End:</label>
                        <div class="col-10">
                            <input 
                                required
                                name="enddate" 
                                type="date" 
                                id="enddate" 
                                class="form-control form-control-sm" 
                                style="width: 140px;" 
                                value=""
                            >
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary" id="btnGenerateMRS">Generate</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal effect-scale" id="show-generate-pa" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="generate-pa-form" action="{{ route('pa.generate_pa_transactions') }}" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Generate PA Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <label for="startdate" class="col-2 col-form-label text-end">Start:</label>
                        <div class="col-10">
                            <input
                                required 
                                name="startdate" 
                                type="date" 
                                id="startdate" 
                                class="form-control form-control-sm" 
                                style="width: 140px;" 
                                value=""
                            >
                        </div>
                    </div>
                    <div class="row">
                        <label for="enddate" class="col-2 col-form-label text-end">End:</label>
                        <div class="col-10">
                            <input 
                                required
                                name="enddate" 
                                type="date" 
                                id="enddate" 
                                class="form-control form-control-sm" 
                                style="width: 140px;" 
                                value=""
                            >
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary" id="btnGenerateMRS">Generate</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal effect-scale" id="printModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Choose File Format</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Select a format to generate the report:</p>

                <!-- Flex container for horizontal layout of radio options -->
                <div class="d-flex justify-content-around">
                    
                    <!-- PDF Option -->
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="fileFormat" id="pdfOption" value="pdf" checked>
                        <label class="form-check-label d-flex flex-column align-items-center" for="pdfOption">
                            <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i> <!-- Large PDF icon -->
                            PDF
                        </label>
                    </div>

                    <!-- Excel Option -->
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="fileFormat" id="excelOption" value="excel">
                        <label class="form-check-label d-flex flex-column align-items-center" for="excelOption">
                            <i class="fas fa-file-excel fa-4x text-success mb-2"></i> <!-- Large Excel icon -->
                            Excel
                        </label>
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times-circle"></i> Close</button>
                <button type="button" class="btn btn-primary" id="generateReportBtn"><i class="fas fa-download"></i> Generate Report</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="mrsDeleteRequestModal" tabindex="-1" role="dialog"
     aria-labelledby="mrsDeleteRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
 
            <!-- Modal Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #1a365d, #2b6cb0); border: none; padding: 20px 28px;">
                <h5 class="modal-title text-white fw-bold" id="mrsDeleteRequestModalLabel" style="font-size: 16px; letter-spacing: 0.4px;">
                    <i class="fa fa-trash-alt mr-2"></i> Request MRS Deletion
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        style="opacity: 1; font-size: 22px; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
 
            <!-- Modal Body -->
            <div class="modal-body" style="padding: 28px;">
 
                <!-- Info Alert -->
                <div class="alert" style="background:#fff3cd; border:1px solid #ffc107; border-radius:8px; font-size:13px; color:#856404; padding: 12px 16px; margin-bottom: 22px;">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    Select the MRS records you want to request for deletion, provide the recipient email and a message explaining the reason.
                </div>
 
                <form id="mrsDeleteRequestForm">
                    @csrf
 
                    <!-- MRS Multi-Select -->
                    <div class="form-group">
                        <label style="font-size: 13px; font-weight: 600; color: #4a5568;">
                            Select MRS Records <span class="text-danger">*</span>
                        </label>
 
                        <!-- Search box -->
                        <div style="position: relative; margin-bottom: 8px;">
                            <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#a0aec0; font-size:13px;">&#128269;</span>
                            <input type="text" id="mrsSearchInput" class="form-control"
                                   placeholder="Search MRS / PA Number..."
                                   style="font-size: 12px; border-radius: 6px; padding-left: 30px;">
                        </div>
 
                        <!-- Scrollable checkboxes container -->
                        <div id="mrsCheckboxList"
                             style="max-height: 240px; overflow-y: auto; border: 1px solid #e2e8f0;
                                    border-radius: 8px; background: #f7fafc;">
 
                            <div class="text-center text-muted py-3" id="mrsLoadingState" style="font-size: 13px;">
                                <i class="fa fa-spinner fa-spin mr-1"></i> Loading MRS records...
                            </div>
                        </div>
 
                        <!-- Selected count badge -->
                        <div style="margin-top: 6px;">
                            <span id="selectedCount"
                                  style="display:inline-block; background:#2b6cb0; color:#fff;
                                         border-radius: 12px; padding: 2px 10px; font-size: 11px; font-weight:600;">
                                0
                            </span>
                            <span style="font-size: 11px; color: #718096; margin-left: 4px;">record(s) selected</span>
                        </div>
                    </div>
 
                    <!-- Recipient Email -->
                    <div class="form-group mt-3">
                        <label style="font-size: 13px; font-weight: 600; color: #4a5568;">
                            Recipient Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="recipient_email" id="recipientEmail"
                               class="form-control" placeholder="e.g. admin@company.com"
                               style="font-size: 13px; border-radius: 6px;">
                        <small class="text-muted" style="font-size: 11px;">
                            The deletion request notification will be sent to this address.
                        </small>
                    </div>
 
                    <!-- Email Body -->
                    <div class="form-group mt-3">
                        <label style="font-size: 13px; font-weight: 600; color: #4a5568;">
                            Message / Reason <span class="text-danger">*</span>
                        </label>
                        <textarea name="email_body" id="emailBody" rows="5"
                                  class="form-control"
                                  style="font-size: 13px; border-radius: 6px; resize: vertical;"
                                  placeholder="Describe the reason for deleting the selected MRS records..."></textarea>
                        <small class="text-muted" style="font-size: 11px;">
                            Max 2,000 characters. The selected MRS numbers will be automatically included in the email.
                        </small>
                    </div>
 
                </form>
            </div>
 
            <!-- Modal Footer -->
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 16px 28px; background: #f7fafc;">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-danger btn-sm px-4" id="sendDeleteRequestBtn">
                    <i class="fa fa-paper-plane mr-1"></i> Send Request
                </button>
            </div>
 
        </div>
    </div>
</div>
 
<style>
    /* ── MRS checkbox row ─────────────────────────────────────── */
    .mrs-checkbox-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 14px;
        border-bottom: 1px solid #edf2f7;
        cursor: pointer;
        transition: background 0.15s;
    }
    .mrs-checkbox-item:last-child  { border-bottom: none; }
    .mrs-checkbox-item:hover       { background: #ebf8ff; }
 
    .mrs-checkbox-item input[type="checkbox"] {
        flex-shrink: 0;
        width: 16px;
        height: 16px;
        margin: 0;
        cursor: pointer;
        accent-color: #2b6cb0;
    }
 
    .mrs-checkbox-item label {
        margin: 0;
        cursor: pointer;
        font-size: 13px;
        color: #2d3748;
        display: flex;
        align-items: center;
        flex: 1;
        gap: 8px;
        flex-wrap: wrap;
    }
 
    .mrs-order-number {
        font-weight: 700;
        color: #1a365d;
        white-space: nowrap;
    }
 
    .mrs-status-badge {
        display: inline-block;
        background: #edf2f7;
        color: #4a5568;
        border-radius: 4px;
        padding: 1px 8px;
        font-size: 11px;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 340px;
    }
 
    .mrs-date {
        margin-left: auto;
        font-size: 11px;
        color: #a0aec0;
        white-space: nowrap;
        flex-shrink: 0;
    }
 
    /* checked row highlight */
    .mrs-checkbox-item.is-checked {
        background: #ebf8ff;
        border-left: 3px solid #2b6cb0;
    }
    .mrs-checkbox-item:not(.is-checked) {
        border-left: 3px solid transparent;
    }
</style>