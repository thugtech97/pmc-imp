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

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("codeSearch");
    const codeList = document.getElementById("codeList");
    const jobCodeRadio = document.getElementById("jobCodeRadio");
    const costCodeRadio = document.getElementById("costCodeRadio");

    // Sample values for Job Codes and Cost Codes
    const sampleJobCodes = localStorage.getItem('JC') ? localStorage.getItem('JC').split(',') : [];
    const sampleCostCodes = localStorage.getItem('CC') ? localStorage.getItem('CC').split(',') : [];

    function updateCodeList(codes) {
        codeList.innerHTML = "";
        codes.forEach(code => {
            let item = document.createElement("a");
            item.href = "#";
            item.classList.add("list-group-item", "list-group-item-action");
            item.textContent = code;
            codeList.appendChild(item);
        });
    }

    searchInput.addEventListener("input", function () {
        let filter = this.value.toLowerCase();
        document.querySelectorAll("#codeList a").forEach(item => {
            item.style.display = item.textContent.toLowerCase().includes(filter) ? "" : "none";
        });
    });

    document.getElementById("codeList").addEventListener("click", function (event) {
        if (event.target.tagName === "A" && selectedInput) {
            selectedInput.value = event.target.textContent; // Update the input field
            $("#jobcostcodes").modal('hide'); // Close the modal
        }
    });

    jobCodeRadio.addEventListener("change", () => updateCodeList(sampleJobCodes));
    costCodeRadio.addEventListener("change", () => updateCodeList(sampleCostCodes));

    // Load default (Job Codes) on modal open
    updateCodeList(sampleJobCodes);
});
</script>