{{-- Items of an Update IMF: one row per existing stock item. Typing a stock
     code looks the item up and fills in its current values; a value that
     differs from the current one is highlighted. Script: _update-items-js. --}}
<div class="imf-card" id="updItemsCard" @if (!empty($hidden)) style="display:none;" @endif>
    <div class="imf-section-head">
        <i class="icon-line-package"></i>
        <h5>Items to Update</h5>
        <span class="imf-section-sub">Enter a stock code per row to auto-fill its current details, then change what needs updating</span>
    </div>

    <div class="imf-grid-wrap">
        <table id="updItems" class="table imf-items-grid">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Stock Code <span class="req">*</span></th>
                    <th>Item Description <span class="req">*</span></th>
                    <th>Brand</th>
                    <th>OEM ID</th>
                    <th>UoM <span class="req">*</span></th>
                    <th>Usage Rate <span class="req">*</span></th>
                    <th>Frequency <span class="req">*</span></th>
                    <th>Min <span class="req">*</span></th>
                    <th>Max <span class="req">*</span></th>
                    <th>Purpose</th>
                    <th>Attachment</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="imf-grid-toolbar">
        <button type="button" id="updAddRow" class="btn btn-success" style="border-radius:6px"><i class="icon-line-plus me-1"></i>Add Item</button>
        <small class="text-muted"><span class="upd-legend"></span> Highlighted values differ from the item's current details.</small>
    </div>
</div>
