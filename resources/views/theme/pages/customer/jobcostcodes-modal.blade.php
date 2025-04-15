<div class="modal fade bs-example-modal-centered" id="jobcostcodes" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Job/Cost Codes</h5>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Radio Selection -->
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="codeType" id="jobCodeRadio" value="JC" checked>
                        <label class="form-check-label" for="jobCodeRadio">Job Code</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="codeType" id="costCodeRadio" value="CC">
                        <label class="form-check-label" for="costCodeRadio">Cost Code</label>
                    </div>
                </div>

                <!-- Search Field -->
                <div class="mb-3">
                    <input type="text" class="form-control" id="codeSearch" placeholder="Search codes...">
                </div>

                <!-- Scrollable List -->
                <div class="list-group overflow-auto" style="max-height: 300px;" id="codeList">
                    <!-- Sample Values will be loaded here -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    