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
        
        const sampleJobCodes = localStorage.getItem('JC') ? localStorage.getItem('JC').split(',') : [];
        const sampleCostCodes = localStorage.getItem('CC') ? localStorage.getItem('CC').split(',') : [];
    
        let currentCodes = [];
    
        function updateCodeList(filter = "") {
            codeList.innerHTML = ""; 
            if (filter.trim() === "") return;
    
            currentCodes
                .filter(code => code.toLowerCase().includes(filter.toLowerCase()))
                .forEach(code => {
                    let item = document.createElement("a");
                    item.href = "#";
                    item.classList.add("list-group-item", "list-group-item-action");
                    item.textContent = code;
                    codeList.appendChild(item);
                });
        }
    
        searchInput.addEventListener("input", function () {
            updateCodeList(this.value);
        });
    
        codeList.addEventListener("click", function (event) {
            if (event.target.tagName === "A" && selectedInput) {
                selectedInput.value = event.target.textContent;
                $("#jobcostcodes").modal('hide');
            }
        });
    
        jobCodeRadio.addEventListener("change", function () {
            currentCodes = sampleJobCodes;
            updateCodeList(searchInput.value);
        });
    
        costCodeRadio.addEventListener("change", function () {
            currentCodes = sampleCostCodes;
            updateCodeList(searchInput.value);
        });
    
        currentCodes = sampleJobCodes;
    });
    </script>
    