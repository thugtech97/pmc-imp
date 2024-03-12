@extends('theme.main')

@section('pagecss')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<style>
    span {
        color: #aa0707;
    }
</style>
<!-- DataTable Stylesheets -->
<link rel="stylesheet" href="{{ asset('lib/datatables.net-dt/css/jquery.dataTables.min.css') }}" type="text/css" />
<link rel="stylesheet" href="{{ asset('lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}" type="text/css" />
<link rel="stylesheet" href="{{ asset('lib/datatables.net-buttons/css/buttons.bootstrap.min.css') }}" type="text/css" />
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (Session::has('success'))
        <div class="alert alert-info" role="alert">
            {{ Session::get('success') }}
        </div>
        @endif

        @if (Session::has('error'))
        <div class="alert alert-danger" role="alert">
            {{ Session::get('error') }}
        </div>
        @endif

        <div class="col-lg-12">
            <div class="border py-4 px-3 border-transparent shadow-lg p-lg-5">
                <form id="imf">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="department" value="INFORMATION AND COMMUNICATIONS TECHNOLOGY">
                        <div class="form-group">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="inlineRadio1" value="new" {{ $request->type == "new" ? 'checked' : '' }}>
                                <label class="form-check-label" for="inlineRadio1">New</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="inlineRadio2" value="update" {{ $request->type == "update" ? 'checked' : '' }}>
                                <label class="form-check-label" for="inlineRadio2">Update</label>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div id="stockCode" class="form-group mb-4">
                                <label for="stock-code" class="fw-semibold text-initial nols">Stock Code</label>
                                <input type="text" disabled id="stock-code" class="form-control form-input" name="stock_code" value="{{ $request->type == "update" ? $items[0]->stock_code : '' }}" />
                                <small id="stockCodeHelp" class="form-text"></small>
                            </div>

                            <div class="form-group mb-4">
                                <label for="item-description" class="fw-semibold text-initial nols">
                                    Item Description <span>&#42;</span>
                                </label>
                                <textarea id="item-description" class="form-control form-input" name="item_description" rows="3" required>{{ $request->type == "update" ? $items[0]->item_description : '' }}</textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label for="brand" class="fw-semibold text-initial nols">Brand</label>
                                <input type="text" id="brand" class="form-control form-input" name="brand" value="{{ $request->type == "update" ? $items[0]->brand : '' }}"/>
                            </div>  

                            <div class="form-group mb-4">
                                <label for="oem-id" class="fw-semibold text-initial nols">OEM ID</label>
                                <input type="text" id="oem-id" class="form-control form-input" name="OEM_ID" value="{{ $request->type == "update" ? $items[0]->OEM_ID : '' }}"/>
                            </div>

                            <div class="form-group mb-4">
                                <label for="uom" class="fw-semibold text-initial nols">Unit of Measure (UoM) <span>&#42;</span> </label>
                                <input type="text" id="uom" class="form-control form-input" name="UoM" value="{{ $request->type == "update" ? $items[0]->UoM : '' }}" required />
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-rate-qty" class="fw-semibold text-initial nols">Usage Rate Qty  <span>&#42;</span> </label>
                                        <input type="number" id="usage-rate-qty" class="form-control form-input" value="{{ $request->type == "update" ? $items[0]->usage_rate_qty : '' }}" name="usage_rate_qty" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-frequency" class="fw-semibold text-initial nols">Usage Frequency  <span>&#42;</span> </label>
                                        <select name="usage_frequency" class="form-select">
                                            <option value="Daily" {{ $request->type == "update" ? (($items[0]->usage_frequency == "Daily") ? 'selected' : '') : '' }}>Daily</option>
                                            <option value="Weekly" {{ $request->type == "update" ? (($items[0]->usage_frequency == "Weekly") ? 'selected' : '') : '' }}>Weekly</option>
                                            <option value="Monthly" {{ $request->type == "update" ? (($items[0]->usage_frequency == "Monthly") ? 'selected' : '') : '' }}>Monthly</option>
                                            <option value="Yearly" {{ $request->type == "update" ? (($items[0]->usage_frequency == "Yearly") ? 'selected' : '') : '' }}>Yearly</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="min-qty" class="fw-semibold text-initial nols">Min Qty  <span>&#42;</span> </label>
                                        <input type="number" id="min-qty" class="form-control form-input" name="min_qty" value="{{ $request->type == "update" ? $items[0]->min_qty : '' }}" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="max-qty" class="fw-semibold text-initial nols">Max Qty  <span>&#42;</span> </label>
                                        <input type="number" id="max-qty" class="form-control form-input" name="max_qty" value="{{ $request->type == "update" ? $items[0]->max_qty : '' }}" required />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="purpose" class="fw-semibold text-initial nols">Item to be used for/Application/Purpose  <span>&#42;</span> </label>
                                <input type="text" id="purpose" class="form-control form-input" name="purpose" value="{{ $request->type == "update" ? $items[0]->purpose : '' }}" required />
                            </div>

                            <div class="form-group">
                                <label for="attach-files" class="fw-semibold text-initial nols">Attach Files</label>
                                <input type="file" class="form-control-file d-block" id="attach-files" name="attachment">
                            </div>
                        </div>
                    </div>
                    <div id="add_section_only">
                        <div class="btn-group">
                            <button type="submit" value="add" id="add_item" class="btn btn-success" style="border-radius: 4px">Add Item</button>&nbsp;
                            <button type="submit" value="update" id="update_item" class="btn btn-success" style="border-radius: 4px">Update Item</button>&nbsp;
                            <button type="submit" value="add_another" id="add_another" class="btn btn-warning" style="border-radius: 4px">Add Another Item?</button>&nbsp;
                        </div>
                        <hr />
                        <table id="itemTable" class="table table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="ls1 fs-14-f fw-bold text-gray wd-20p-f">Item Description</th>
                                    <th scope="col" class="ls1 fs-14-f fw-bold text-gray wd-15p-f">Brand</th>
                                    <th scope="col" class="ls1 fs-14-f fw-bold text-gray wd-15p-f">OEM ID</th>
                                    <th scope="col" class="ls1 fs-14-f fw-bold text-gray wd-15p-f">UoM</th>
                                    <th scope="col" class="d-none ls1 fs-14-f fw-bold text-gray wd-10p-f">UsageRateQty</th>
                                    <th scope="col" class="d-none ls1 fs-14-f fw-bold text-gray wd-10p-f">UsageFrequency</th>
                                    <th scope="col" class="ls1 fs-14-f fw-bold text-gray wd-10p-f">MinQty</th>
                                    <th scope="col" class="ls1 fs-14-f fw-bold text-gray wd-10p-f">MaxQty</th>
                                    <th scope="col" class="ls1 fs-14-f fw-bold text-gray wd-10p-f">Purpose</th>
                                    <th scope="col" class="ls1 fs-14-f fw-bold text-gray wd-10p-f">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                <tr data-id="{{ $item->id }}">
                                    <td>{{ $item->item_description }}</td>
                                    <td>{{ $item->brand }}</td>
                                    <td>{{ $item->OEM_ID }}</td>
                                    <td>{{ $item->UoM }}</td>
                                    <td>{{ $item->min_qty }}</td>
                                    <td>{{ $item->max_qty }}</td>
                                    <td>{{ $item->purpose }}</td>
                                    <td>
                                        <i class="icon-edit edit-row-btn mx-1" style="color: #48b34c; cursor: pointer;"></i>
                                        <i class="icon-trash delete-row-btn" style="color: #f34237; cursor: pointer;"></i>
                                    </td>
                                </tr>
                                @empty

                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-column flex-lg-row flex-md-row justify-content-end">
                        <button type="submit" name="action" value="save" class="button button-black button-circle button-xlarge fw-bold mt-2 fs-14-f nols notextshadow">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('pagejs')
<script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
<script src="{{ asset('lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        'use strict'
        var isContinue = false;
        let stockCodeExists = false;

        $('#stockCode').hide();
        $('#add_another').hide();
        $('#update_item').hide();
        $('#add_section_only').show();

        var form = new FormData();
        var count = 0;
        var type = '{{ $request->type }}';
        var selectedItemId;
        var oldData = [];

        if (type == 'new') {
            $('#stockCode').hide();
            $('#add_section_only').show();
            loadItems();
            if (isContinue) {
                $('#item-description, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
            } else {
                $('#item-description, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', false);
            }
        } else {
            
           var currentItem = {
                stock_code: document.getElementById('stock-code').value,
                item_description: document.getElementById('item-description').value,
                brand: document.getElementById('brand').value,
                OEM_ID: document.getElementById('oem-id').value,
                UoM: document.getElementById('uom').value,
                usage_rate_qty: document.getElementById('usage-rate-qty').value,
                usage_frequency:  document.querySelector('select[name="usage_frequency"]').value,
                min_qty: document.getElementById('min-qty').value,
                max_qty: document.getElementById('max-qty').value,
                purpose: document.getElementById('purpose').value,
            };
            
            oldData = Object.entries(currentItem)
                .filter(([name, value]) => value !== null && value !== undefined && value.trim() !== "")
                .map(([name, value]) => ({ name, value }));

            $('#item-description, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
            $('#stockCode').show();
            $('#add_section_only').hide();
        }

        $('input[type=radio][name=type]').prop('disabled', true);
        /**
         * =================================================================
         * CREATE/UPDATE STOCK
         * =================================================================
        */
        $('#imf').submit(function(event) {
            event.preventDefault();

            var buttonClicked = $('button:focus').val();
            var selectedRadioValue = $('input[type=radio][name=type]:checked').val();

            if (selectedRadioValue === 'update') 
            {

                if (buttonClicked === 'save') {
                    form = new FormData();

                    var formData = $('#imf').serializeArray();

                    $.each(formData, function(index, field) {
                        
                        form.append(field.name, field.value);

                        const oldFieldIndex = oldData.findIndex(oldItem => oldItem.name === field.name);
                        
                        if (oldFieldIndex !== -1 && oldData[oldFieldIndex].value === field.value) {
                            oldData.splice(oldFieldIndex, 1);
                        }
                    }); 

                    form.append("type", 'update');
                    form.append('action', 'SAVED');
                    form.append('old-data', JSON.stringify(oldData));

                    $.ajax({
                        url: "{{ route('imf.update', $request->id) }}",
                        type: 'POST',
                        data: form,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {

                            Swal.fire({
                                icon: "success",
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500,
                                backdrop: `
                                        rgba(0,0,0,0.7)
                                        left top
                                        no-repeat
                                    `
                            }).then(() => {
                                window.location.href = response.redirect;
                            });

                        },
                        error: function(error) {

                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "Something went wrong!",
                                showConfirmButton: false,
                                timer: 1000,
                                backdrop: `
                                        rgba(0,0,0,0.7)
                                        left top
                                        no-repeat
                                    `
                            })

                        }
                    });
                }
                
                if (buttonClicked === 'save_and_submit') {
                    alert("update save and submit ...")
                }
            } 
            else 
            {
                if (buttonClicked === 'add') 
                {
                    var formData = $('#imf').serializeArray();
                    var tableRow = "<tr>";
                    var isDescriptionExists = false;

                    $.each(formData, function(index, field) {
                        if (field.name === '_token' || field.name === 'department' || field.name === 'type') {

                        } else {
                            if (field.name === 'stock_code') {
                                stockCodeExists = true;
                            }

                            // Check if the description already exists
                            if (field.name === 'item_description' && $('#itemTable tbody tr td:first-child:contains("' + field.value + '")').length > 0) {
                                
                                isDescriptionExists = true;
                            
                                Swal.fire({
                                    icon: "warning",
                                    title: "Duplicate Item Description",
                                    text: "Item Description '" + field.value + "' already exists.",
                                    showConfirmButton: false,
                                    timer: 2000,
                                    backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                                });
                                
                                return false;
                            }
                            if (!isDescriptionExists) {
                                form.append(`${field.name}[${count}]`, field.value);
                            }
                        }

                        var excludedFields = ['_token', 'department', 'type', 'usage_rate_qty', 'usage_frequency', 'stock_code'];

                        if (excludedFields.indexOf(field.name) === -1 && !isDescriptionExists) {
                            tableRow += "<td>" + field.value + "</td>";
                        }
                    });

                    if (!isDescriptionExists) {  
                        // If stock_code doesn't exist in the formData, append it with an empty value
                        if (!stockCodeExists) {
                            form.append(`stock_code[${count}]`, '');
                        }

                        // Add the delete button to the row
                        tableRow +=
                        `<td>
                            <i class="icon-edit edit-row-btn mx-1" style="color: #48b34c; cursor: pointer;"></i>
                            <i class="icon-trash delete-row-btn" style="color: #f34237; cursor: pointer;"></i>
                        </td>`;

                        tableRow += "</tr>";
                        count++;

                        $('#itemTable tbody').append(tableRow);
                        
                    }

                    $('#item-description, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', false);
                    isContinue = false;
                    $('#add_item').hide();
                    $('#add_another').show();
                    $('#imf')[0].reset();
                }

                if (buttonClicked === 'add_another') 
                {
                    $('#item-description, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
                    $('#add_item').show();
                    $('#add_another').hide();
                    $('#update_item').hide();
                    isContinue = true;
                }

                if(buttonClicked === 'update') {

                    var formUpdated = new FormData();
                    var formData = $('#imf').serializeArray();

                    $.each(formData, function(index, field) {
                        formUpdated.append(field.name, field.value);
                    });
                    
                    formUpdated.append("type", 'update-item');

                    $.ajax({
                        url: "{{ route('imf.update', ['id' => ':id']) }}".replace(':id', selectedItemId),
                        type: 'POST',
                        data: formUpdated,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: "success",
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500,
                                backdrop: `
                                        rgba(0,0,0,0.7)
                                        left top
                                        no-repeat
                                    `
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(error) {
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "Something went wrong!",
                                showConfirmButton: false,
                                timer: 1000,
                                backdrop: `
                                        rgba(0,0,0,0.7)
                                        left top
                                        no-repeat
                                    `
                            })
                        }
                    });
                }

                if (buttonClicked === 'save') 
                {
                    if ($('#itemTable tbody tr').length > 0) {
                        form.append('action', 'SAVED');

                        $.ajax({
                            url: "{{ route('imf.update', $request->id) }}",
                            type: 'POST',
                            data: form,
                            contentType: false,
                            processData: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {

                                Swal.fire({
                                    icon: "success",
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 1500,
                                    backdrop: `
                                            rgba(0,0,0,0.7)
                                            left top
                                            no-repeat
                                        `
                                }).then(() => {
                                    window.location.href = response.redirect;
                                });

                            },
                            error: function(error) {

                                Swal.fire({
                                    icon: "error",
                                    title: "Oops...",
                                    text: "Something went wrong!",
                                    showConfirmButton: false,
                                    timer: 1000,
                                    backdrop: `
                                            rgba(0,0,0,0.7)
                                            left top
                                            no-repeat
                                        `
                                })

                            }
                        });
                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            title: "Add atleast 1 item..",
                            showConfirmButton: false,
                            timer: 1000,
                            backdrop: `
                                    rgba(0,0,0,0.7)
                                    left top
                                    no-repeat
                                `
                        })

                    }
                }
            }
        });
        /**
         * =================================================================
         * REMOVE/DELETE STOCK
         * =================================================================
         */
        $('#itemTable tbody').on('click', '.delete-row-btn', function() {
            const $row = $(this).closest('tr');
            const index = $row.index();

            Swal.fire({
                title: 'Delete Item',
                text: "Are you sure you want to delete this item?",
                icon: "warning",
                showCancelButton: true,
                allowOutsideClick: false,
                confirmButtonColor: '#2ecc71',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons: true,
                backdrop: `rgba(0,0,0,0.7) left top no-repeat`
            }).then((result) => {

                if(result.isConfirmed) 
                {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Data has been successfully deleted.",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 500
                    });

                    $row.remove();
                    removeItemFromFormData(index);
                    updateIndicesAfterDeletion(index);
                }
                
            });

        });
        
        // Function to remove item from FormData by index
        function removeItemFromFormData(index) {
            const keysToDelete = Array.from(form.keys()).filter(key => key.endsWith(`[${index}]`));
            keysToDelete.forEach(key => form.delete(key));
        }
        // Function to update indices in FormData after row deletion
        function updateIndicesAfterDeletion(startIndex) {
            const newData = new FormData();

            for (let pair of form.entries()) {
                const key = pair[0];
                const value = pair[1];

                const matches = key.match(/^(.+)\[(\d+)\]$/);
                if (matches) {
                    const currentKey = matches[1];
                    const currentIndex = parseInt(matches[2]);

                    if (currentIndex === startIndex) {
                        continue;
                    }

                    if (currentIndex > startIndex) {
                        const updatedKey = `${currentKey}[${currentIndex - 1}]`;
                        newData.append(updatedKey, value);
                    } else {
                        newData.append(key, value);
                    }
                } else {
                    newData.append(key, value);
                }
            }

            form = new FormData();
            for (let pair of newData.entries()) {
                form.append(pair[0], pair[1]);
            }
        }
        
        /**
         * =================================================================
         * UPDATE ITEM/POPULATE DATA IN FORM
         * =================================================================
         */
        $('#itemTable tbody').on('click', '.edit-row-btn', function() {

            $('#item-description, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
            $('#add_another').hide();
            $('#add_item').hide();
            $('#update_item').show();

            isContinue = true;

            const $row = $(this).closest('tr');
            const rowIndex = $row.index();
            $('#row-index').val(rowIndex);
            selectedItemId = $row.data('id');

            for (let pair of form.entries()) {
                const key = pair[0];
                let value = pair[1];

                const matches = key.match(/^(.+)\[(\d+)\]$/);

                if (matches && parseInt(matches[2]) === rowIndex) {

                    const fieldName = matches[1];
                    const columnName = fieldName.replace(/_/g, '-');
                    const inputElement = document.getElementById(`${columnName.toLowerCase()}`);

                    if (inputElement) {
                        inputElement.value = value;
                    }
                }
            }
        });
        /**
         * =================================================================
         * Populate Datatable
         * =================================================================
         */
        function loadItems() {
            form = new FormData();
            var items = @json($request->items);
            var formData = $('#imf').serializeArray();

            $.each(formData, function(index, field) {
                if (field.name === '_token' || field.name === 'department' || field.name === 'type') {
                    if (count == 0) {
                        form.append(field.name, field.value);
                    }
                }
            });
            form.append("type", type);
            for (var i = 0; i < items.length; i++) {
                form.append(`id[${count}]`, items[i].id);
                form.append(`stock_code[${count}]`, items[i].stock_code);
                form.append(`item_description[${count}]`, items[i].item_description);
                form.append(`brand[${count}]`, items[i].brand);
                form.append(`OEM_ID[${count}]`, items[i].OEM_ID);
                form.append(`UoM[${count}]`, items[i].UoM);
                form.append(`usage_rate_qty[${count}]`, items[i].usage_rate_qty);
                form.append(`usage_frequency[${count}]`, items[i].usage_frequency);
                form.append(`min_qty[${count}]`, items[i].min_qty);
                form.append(`max_qty[${count}]`, items[i].max_qty);
                form.append(`purpose[${count}]`, items[i].purpose);
                count++;
            }
        }
    });
</script>
@endsection