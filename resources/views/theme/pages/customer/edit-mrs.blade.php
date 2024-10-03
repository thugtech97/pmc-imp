<div class="modal fade bs-example-modal-centered modal-size" id="editdetail" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="" id="edit_form">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PUT">

            <div class="modal-header">
                <small style="margin-right:30px"><strong>MRS No.</strong> <span id="mrs_no"></span></small>
                <small style="margin-right:30px"><strong>Request Date:</strong> <span id="request_date"></span></small>
                <small><strong>Request Status:</strong> <span id="request_status"></span></small>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="transaction-status">
                            <div class="form-group">
                                <label>Priority #</label>
                                <input type="number" class="form-control" name="priority" value="" id="priority_no">
                            </div>
                            <div class="form-group">
                                <label for="isBudgeted" class="fw-semibold text-inital nols">Budgeted?</label>
                                <select id="isBudgeted" onchange="" name="isBudgeted" class="form-select">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" class="form-control" name="department" value="" disabled id="department">
                            </div>
                            <div class="form-group">
                                <label>PURPOSE</label>
                                <input type="text" id="purpose" class="form-control" onkeyup="/*$('.purpose_item').val(this.value)*/" name="justification" value="">
                            </div>
                            <div class="form-group">
                                <label for="requested_by" class="fw-semibold text-inital nols">Requested by</label>
                                <select id="requested_by" name="requested_by" class="form-select employees">
                                    
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="transaction-status">
                            <div id="deliveryDate" class="form-group">
                                <label>Date Needed:</label>
                                <input type="date" id="date_needed" name="delivery_date" class="form-control" value="" />
                            </div>
                            <div class="form-group" id="budgetAmount">
                                <label>Budget amount</label>
                                <input type="number" id="budgeted" step="0.0001" id="budgeted_amount" name="budgeted_amount" class="form-control" value="">
                            </div>
                            <div class="form-group">
                                <label>Section</label>
                                <input type="text" id="section" class="form-control" name="section" value="">
                            </div>
                            <div class="form-group">
                                <label>Other Instructions:</label>
                                <textarea name="notes" id="notes" class="form-control"></textarea> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gap-20"></div>
                <br><br>
                <div class="table-modal-wrap">
                    <table class="table table-md table-modal">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Item</th>
                                <th style="width: 25%;">PAR To</th>
                                <th style="width: 10%;">Frequency</th>
                                <th style="width: 15%;">Purpose</th>
                                <th style="width: 10%;">Date Needed</th>
                                <th style="width: 10%;">Qty</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="mrs_items">
                        
                        </tbody>
                    </table>
                </div>
                <div class="gap-20"></div>
            </div>
            <div class="modal-footer">
                <input type="submit" class="btn btn-primary" value="Update">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>