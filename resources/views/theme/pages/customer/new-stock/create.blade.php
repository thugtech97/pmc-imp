@extends('theme.main')

@section('pagecss')
<link rel="stylesheet" href="{{ asset('lib/sweetalert2/sweetalert.min.css') }}" type="text/css" />
<link rel="stylesheet" href="{{ asset('lib/js-snackbar/js-snackbar.css') }}" type="text/css" />
<style>
    span {
        color: #aa0707;
    }
    .error-color {
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
            <div class="border py-4 px-3 border-transparent shadow-lg p-lg-5 form-container">
                <form id="imf">
                    @csrf
                    <div class="row" id="inputs-container">
                        <input type="hidden" name="department" value="{{ auth()->user()->department->name }}">
                        <div class="form-group">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="inlineRadio1" value="new" checked="checked">
                                <label class="form-check-label" for="inlineRadio1">New</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="inlineRadio2" value="update">
                                <label class="form-check-label" for="inlineRadio2">Update</label>
                            </div>
                        </div>
                        <input type="hidden" class="form-control" id="row-index">
                        <div class="col-lg-6">
                            <div id="stockCode" class="form-group mb-4">
                                <label for="stock-code" class="fw-semibold text-initial nols">Stock Code</label>
                                <input type="text" id="stock-code" class="form-control form-input" name="stock_code" />
                                <small id="stockCodeHelp" class="form-text"></small>
                            </div>

                            <div class="form-group mb-4">
                                <label for="brand" class="fw-semibold text-initial nols">
                                    Priority <span class="isRequiredField">&#42;</span>
                                </label>
                                <select id="priority" class="form-select" name="priority">
                                    <option value="1">Priority 1</option>
                                    <option value="2">Priority 2</option>
                                    <option value="3">Priority 3</option>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label for="item-description" class="fw-semibold text-initial nols">
                                    Item Description <span>&#42;</span>
                                </label>
                                <textarea id="item-description" class="form-control form-input" name="item_description" required rows="3"></textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label for="brand" class="fw-semibold text-initial nols">
                                    Brand <span class="isRequiredField">&#42;</span>
                                </label>
                                <input type="text" id="brand" class="form-control form-input" name="brand" required />
                            </div>

                            <div class="form-group mb-4">
                                <label for="oem-id" class="fw-semibold text-initial nols">
                                    OEM ID <span class="isRequiredField">&#42;</span>
                                </label>
                                <input type="text" id="oem-id" class="form-control form-input" name="OEM_ID" required />
                            </div>

                            <div class="form-group mb-4">
                                <label for="uom" class="fw-semibold text-initial nols">
                                    Unit of Measure (UoM) <span>&#42;</span> 
                                </label>
                                <input type="text" id="uom" class="form-control form-input" name="UoM" required />
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-rate-qty" class="fw-semibold text-initial nols">
                                            Usage Rate Qty <span>&#42;</span>
                                        </label>
                                        <input type="number" id="usage-rate-qty" class="form-control form-input" name="usage_rate_qty" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-frequency" class="fw-semibold text-initial nols">
                                            Usage Frequency <span>&#42;</span>
                                        </label>
                                        <select name="usage_frequency" id="usage-frequency" class="form-select">
                                            <option value="Daily">Daily</option>
                                            <option value="Weekly">Weekly</option>
                                            <option value="Monthly">Monthly</option>
                                            <option value="Yearly">Yearly</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="min-qty" class="fw-semibold text-initial nols">
                                            Min Qty <span>&#42;</span>
                                        </label>
                                        <input type="number" id="min-qty" class="form-control form-input" name="min_qty" value="1" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="max-qty" class="fw-semibold text-initial nols">
                                            Max Qty <span>&#42;</span>
                                        </label>
                                        <input type="number" id="max-qty" class="form-control form-input" name="max_qty" required />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4" id="purpose-container">
                                <label for="purpose" class="fw-semibold text-initial nols">
                                    Item to be used for/Application/Purpose <span>&#42;</span>
                                </label>
                                <input type="text" id="purpose" class="form-control form-input" name="purpose" required />
                            </div>

                            <div class="form-group">
                                <label for="attach-files" class="fw-semibold text-initial nols">Attach Files</label>
                                <input type="file" class="form-control-file d-block" id="attach-files" name="attachment">
                            </div>
                            <div id="attachmentContainer">
                                <!-- Attachment URL will be displayed here -->
                            </div>
                        </div>
                    </div>
                    <div id="add_section_only">
                        <div class="btn-toolbar justify-content-between" role="toolbar">
                            <div class="btn-group" role="group">
                                <button type="submit" value="add" id="add_item" class="btn btn-success" style="margin-right: 10px; border-radius: 4px">Add Item</button>
                                <button type="submit" value="add_another" id="add_another" class="btn btn-warning" style="border-radius: 4px">Add Another Item?</button>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" id="download_template" class="btn btn-warning mx-2" style="border-radius: 4px; color: #212529">Download Template</button>
                                <button type="button" id="import_csv" class="btn btn-primary" accept=".xlsx" style="border-radius: 4px">Import CSV</button>
                            </div>
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
                            <tbody></tbody>
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
<script src="{{ asset('lib/js-snackbar/js-snackbar.js') }}"></script>
<script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
<script src="{{ asset('lib/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        var isContinue = true;
        var isImportCSV = false;
        var dataArray = [];
        var oldDataArray = [];


        $('#stockCode').hide();
        $('#add_another').hide();
        $('#add_section_only').show();

        var form = new FormData();
        var count = 0;

        $("#stock-code").keyup(function() {

            if ($(this).val() === '') {
                displayStockCodeMessage("Please enter a stock code", false);
                $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('disabled', false)
                return;
            }

            $.ajax({
                url: "{{ route('products.search') }}",
                type: 'POST',
                data: "code=" + $(this).val(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 'success') {
                        if(response.valid == 1){

                            var data = response.data;
                            var attachmentUrl = data.attachments;

                            displayStockCodeMessage("Product found");
                            $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('disabled', false)
                            getOldData(data);

                            $("#item-description").val(data.name);
                            $("#brand").val(data.brand);
                            $("#oem-id").val(data.OEM_ID);
                            $("#uom").val(data.uom);
                            $("#min-qty").val(data.min_qty);
                            $("#max-qty").val(data.max_qty);
                            $("#usage-rate-qty").val(data.usage_rate_qty);
                            $("#usage-frequency").val(data.usage_frequency);
                            $("#purpose").val(data.purpose);
                        }else{
                            displayStockCodeMessage("Stock code is on process!", false);
                            $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('disabled', true)
                        }
                    } else {
                        displayStockCodeMessage("Product not found!", false);
                        $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('disabled', true)
                    }
                },
                error: function(error) {

                }
            })
        });

        let displayStockCodeMessage = function(message, flag = true) {
            
            if (flag) {
                $("#stockCodeHelp").html(`<span style="color:green;">${message}!</span>`);
                return;
            }

            $("#stockCodeHelp").html(`<span>${message}</span>`);
            $('#item-description, #brand, #uom, #oem-id').val("");
        };

        let getOldData = function(data) {
            var currentItem = {
                stock_code: data.code,
                item_description: data.name,
                brand: data.brand,
                OEM_ID: data.oem,
                UoM: data.uom,
                usage_rate_qty: data.usage_rate_qty,
                usage_frequency: data.usage_frequency,
                min_qty: data.min_qty,
                max_qty: data.max_qty,
                purpose: data.purpose,
            };
            
            oldDataArray = Object.entries(currentItem)
                .filter(([name, value]) => value !== null && value !== undefined && value.trim() !== "")
                .map(([name, value]) => ({ name, value }));
        }

        $('input[type=radio][name=type]').change(function() {
            if (this.value == 'new') {
                $('#stockCode').hide();
                $('#add_section_only').show();
                $(".isRequiredField").show();
                $('#purpose-container').show();
                $('#imf')[0].reset();
                if (isContinue) {
                    $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
                } else {
                    $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', false);
                }
            } else if (this.value == 'update') {

                $("#stockCodeHelp").html("");
                $("#brand, #oem-id").prop('required', false);
                $('#item-description, #uom, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
                $('#stockCode').show();
                $(".isRequiredField").hide();
                $('#add_section_only').hide();
                //$('#purpose-container').hide();
            }
        });
        /**
         * =================================================================
         * CREATE/UPDATE STOCK
         * =================================================================
         */

        $('#imf').submit(function(event) {
            event.preventDefault();

            var buttonClicked = $('button:focus').val();

            var selectedRadioValue = $('input[type=radio][name=type]:checked').val();

            if (selectedRadioValue === 'update') {

                if (buttonClicked === 'save') {

                    form = new FormData();
                    var formData = $('#imf').serializeArray();

                    $.each(formData, function(index, field) {
                        var file = $('#attach-files')[0].files[index];
    
                        if (file) {
                            form.append('attachment', file);
                        }
    
                        form.append(field.name, field.value);

                        const oldFieldIndex = oldDataArray.findIndex(oldItem => oldItem.name === field.name);
                        
                        if (oldFieldIndex !== -1 && oldDataArray[oldFieldIndex].value === field.value) {
                            oldDataArray.splice(oldFieldIndex, 1);
                        }
                    });

                    form.append("type", 'update');
                    form.append('action', 'SAVED');
                    form.append('old-data', JSON.stringify(oldDataArray));

                    $.ajax({
                        url: "{{ route('new-stock.store') }}",
                        type: 'POST',
                        data: form,
                        contentType: false,
                        processData: false,
                        success: function(response) {

                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: "success",
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 1500,
                                    backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                                }).then(() => {
                                    window.location.href = response.redirect;
                                });

                            } else {

                                Swal.fire({
                                    icon: "error",
                                    title: "Oops...",
                                    text: "Something went wrong!",
                                    showConfirmButton: false,
                                    timer: 1000,
                                    backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                                });

                            }
                        },
                        error: function(error) {

                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "Something went wrong!",
                                showConfirmButton: false,
                                timer: 1000,
                                backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                            });
                        }
                    });
                }
            } else {
                if (buttonClicked === 'add') {

                    const rowIndex = $('#row-index').val();
                    if (rowIndex !== null && rowIndex !== undefined && rowIndex !== '')
                    {
                        $('#itemTable tbody tr').eq(rowIndex).remove();
                        removeItemFromFormData(rowIndex)
                        updateIndicesAfterDeletion(rowIndex)
                        $('#row-index').val('');
                        $('#add_item').text('Add Item');

                        new SnackBar({
                            message: "Data Successfully Updated!",
                            status: "success",
                            position: 'bc',
                            width: "500px",
                            dismissible: false,
                            container: ".form-container"
                        });
                    }

                    var formData = $('#imf').serializeArray();
                    var tableRow = "<tr>";
                    var itemTableCount = $('#itemTable tbody tr').length;

                    var isDescriptionExists = false;

                    $.each(formData, function(index, field) {
                     
                        var file = $('#attach-files')[0].files[index];
            
                        if (file) {
                            form.append('attachment[' + count + ']', file);
                        }

                        if (field.name === '_token' || field.name === 'department' || field.name === 'type') {
                            if (!form.has(field.name)) {
                                form.append(field.name, field.value);
                            }
                        } else {
                            count = itemTableCount > 0 ? itemTableCount : count;

                            if (field.name !== 'stock_code' && field.value === '') {
                                return false;
                            }

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

                        var excludedFields = ['_token', 'priority', 'department', 'type', 'usage_rate_qty', 'usage_frequency', 'stock_code'];

                        if (excludedFields.indexOf(field.name) === -1  && !isDescriptionExists) {
                            tableRow += "<td>" + field.value + "</td>";
                        }

                    });
                    
                    if (!isDescriptionExists) {
                        tableRow +=
                            `<td>
                                <i class="icon-edit edit-row-btn mx-1" style="color: #48b34c; cursor: pointer;"></i>
                                <i class="icon-trash delete-row-btn" style="color: #f34237; cursor: pointer;"></i>
                            </td>`;
                        
                        tableRow += "</tr>";
                        count++;
                        
                        $('#itemTable tbody').append(tableRow);
                    }
                    
                    $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', false);
                    isContinue = false;
                    $('#add_item').hide();
                    $('#add_another').show();
                    $('#imf')[0].reset();
                }

                if (buttonClicked === 'add_another') {
                    $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
                    $('#add_item').show();
                    $('#add_another').hide();
                    isContinue = true;
                }

                if (buttonClicked === 'save') {
                    if ($('#itemTable tbody tr').length > 0) {

                        form.append('action', 'SAVED');

                        $.ajax({
                            url: "{{ route('new-stock.store') }}",
                            type: 'POST',
                            data: form,
                            contentType: false,
                            processData: false,
                            success: function(response) {

                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: "success",
                                        title: response.message,
                                        showConfirmButton: false,
                                        timer: 1500,
                                        backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                                    }).then(() => {
                                        window.location.href = response.redirect;
                                    });
                                }
                            },
                            error: function(error) {

                                Swal.fire({
                                    icon: "error",
                                    title: "Oops...",
                                    text: "Something went wrong!",
                                    showConfirmButton: false,
                                    timer: 1000,
                                    backdrop: `rgba(0,0,0,0.7) left top no-repeat`
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
                            backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                        })

                    }
                }
            }
        });
        /**
         * =================================================================
         *  TRIGGER IMPORT CSV
         * =================================================================
         */
        $('#import_csv').on('click', function() {

            var fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.xlsx';

            fileInput.click();

            fileInput.addEventListener('change', function(e) {
                var file = e.target.files[0];
                var reader = new FileReader();

                dataArray = [];

                reader.onload = function(event) {
                    var data = new Uint8Array(event.target.result);
                    var workbook = XLSX.read(data, {
                        type: 'array'
                    });

                    var sheetName = workbook.SheetNames[0];
                    var worksheet = workbook.Sheets[sheetName];

                    var jsonData = XLSX.utils.sheet_to_json(worksheet, {
                        header: 1
                    });

                    var tableBody = $('#itemTable tbody');
                    var fieldName = ['item_description', 'brand', 'OEM_ID', 'UoM', 'usage_rate_qty', 'usage_frequency', 'min_qty', 'max_qty', 'purpose'];

                    for (var i = 1; i < jsonData.length; i++) {
                        var row = '<tr>';
                        var rowData = {};

                        // Check if the description already exists
                        var itemDescription = jsonData[i][0]; 

                        if ($('#itemTable tbody tr td:first-child:contains("' + itemDescription + '")').length > 0) {
                            Swal.fire({
                                icon: "warning",
                                title: "Duplicate Item Description",
                                text: "Item Description '" + itemDescription + "' already exists.",
                                showConfirmButton: false,
                                timer: 2000,
                                backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                            });
                            continue;
                        }

                        if (jsonData[i].length !== fieldName.length) {
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "There are missing values in the required fields.",
                                showConfirmButton: false,
                                timer: 1000,
                                backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                            })
                            return;
                        }

                        for (var j = 0; j < jsonData[i].length; j++) {

                            if (j !== 4 && j !== 5) {
                                row += '<td>' + jsonData[i][j] + '</td>';
                            }

                            rowData[fieldName[j]] = jsonData[i][j];
                        }

                        dataArray.push(rowData);

                        // Add the delete button to the row
                        row +=
                        `<td>
                            <i class="icon-edit edit-row-btn mx-1" style="color: #48b34c; cursor: pointer;"></i>
                            <i class="icon-trash delete-row-btn" style="color: #f34237; cursor: pointer;"></i>
                        </td>`;

                        row += '</tr>';
                        tableBody.append(row);
                        isImportCSV = true;
                        $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', false);
                    }

                    appendImportedCSVData();
                };

                reader.readAsArrayBuffer(file);
            });
        });

        function appendImportedCSVData() {

            if (isImportCSV) {
                var formData = $('#imf').serializeArray();
                var itemTableCount = $('#itemTable tbody tr').length;

                count = count > 0 ? count : 0;

                $.each(formData, function(index, field) {

                    if (field.name === '_token' || field.name === 'department' || field.name === 'type') {
                        if (!form.has(field.name)) {
                            form.append(field.name, field.value);
                        }
                    } else {
                        if (dataArray.length > 0) {
                            dataArray.forEach(function(data, dataIndex) {

                                var dataArrayFields = Object.keys(data);

                                if (dataArrayFields.includes(field.name)) 
                                {
                                    var fieldValue = data[field.name];
                                    form.append(`${field.name}[${dataIndex + count}]`, fieldValue);
                                } else {
                                    form.append(`${field.name}[${dataIndex + count}]`, '');
                                }

                            });
                        }
                    }
                });
            }

        }
        /**
         * =================================================================
         *  DOWNLOAD TEMPLATE
         * =================================================================
         */
        $('#download_template').on('click', function() {

            $.ajax({
                type: 'GET',
                url: "{{ route('download.template') }}",
                xhrFields: {
                    responseType: 'blob' 
                },
                success: function(data, status, xhr) {

                    var blob = new Blob([data], { type: xhr.getResponseHeader('Content-Type') });

                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(blob);
                    a.href = url;
                    a.download = 'create-new-stock-import-template.xlsx';
                    document.body.appendChild(a);

                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    new SnackBar({
                        message: "Template Downloaded Successfully!",
                        status: "success",
                        position: 'bc',
                        width: "500px",
                        dismissible: false,
                        container: ".form-container"
                    });
                },
                error: function(xhr, status, error) {

                    new SnackBar({
                        message: error.message,
                        status: "error",
                        position: 'bc',
                        width: "500px",
                        dismissible: false
                    });

                }
            });

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

                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Data has been successfully deleted.",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 1000
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
        
            $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
            $('#add_another').hide();
            $('#add_item').show();
            $('#add_item').text('Update Item');
            isContinue = true;

            const $row = $(this).closest('tr');
            const rowIndex = $row.index();

            $('#row-index').val(rowIndex);

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
    });
</script>
@endsection