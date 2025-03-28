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
