@extends('theme.main')

@section('pagecss')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<!-- DataTable Stylesheets -->
<link rel="stylesheet" href="{{ asset('lib/datatables.net-dt/css/jquery.dataTables.min.css') }}" type="text/css" />
<link rel="stylesheet" href="{{ asset('lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}" type="text/css" />
<link rel="stylesheet" href="{{ asset('lib/datatables.net-buttons/css/buttons.bootstrap.min.css') }}" type="text/css" />
@endsection
@section('content')
<div class="container">
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
                                <input class="form-check-input" type="radio" name="type" id="inlineRadio1" value="new" checked="checked">
                                <label class="form-check-label" for="inlineRadio1">New</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="inlineRadio2" value="update">
                                <label class="form-check-label" for="inlineRadio2">Update</label>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div id="stockCode" class="form-group mb-4">
                                <label for="stock-code" class="fw-semibold text-initial nols">Stock Code</label>
                                <input type="text" id="stock-code" class="form-control form-input" name="stock_code" />
                                <small id="stockCodeHelp" class="form-text"></small>
                            </div>

                            <div class="form-group mb-4">
                                <label for="item-description" class="fw-semibold text-initial nols">Item Description</label>
                                <textarea id="item-description" class="form-control form-input" name="item_description" required rows="3"></textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label for="brand" class="fw-semibold text-initial nols">Brand</label>
                                <input type="text" id="brand" class="form-control form-input" name="brand" required />
                            </div>

                            <div class="form-group mb-4">
                                <label for="oem-id" class="fw-semibold text-initial nols">OEM ID</label>
                                <input type="text" id="oem-id" class="form-control form-input" name="OEM_ID" required />
                            </div>

                            <div class="form-group mb-4">
                                <label for="uom" class="fw-semibold text-initial nols">Unit of Measure (UoM)</label>
                                <input type="text" id="uom" class="form-control form-input" name="UoM" required />
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-rate-qty" class="fw-semibold text-initial nols">Usage Rate Qty</label>
                                        <input type="number" id="usage-rate-qty" class="form-control form-input" name="usage_rate_qty" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-frequency" class="fw-semibold text-initial nols">Usage Frequency</label>
                                        <!--<input type="text" id="usage-frequency" class="form-control form-input" name="usage_frequency" />
                                        <small id="emailHelp" class="form-text text-muted">(D/W/M/Y, etc)</small>-->
                                        <select name="usage_frequency" class="form-select">
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
                                        <label for="min-qty" class="fw-semibold text-initial nols">Min Qty</label>
                                        <input type="number" id="min-qty" class="form-control form-input" name="min_qty" value="1" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="max-qty" class="fw-semibold text-initial nols">Max Qty</label>
                                        <input type="number" id="max-qty" class="form-control form-input" name="max_qty" required />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="purpose" class="fw-semibold text-initial nols">Item to be used for/Application/Purpose</label>
                                <input type="text" id="purpose" class="form-control form-input" name="purpose" required />
                            </div>

                            <div class="form-group">
                                <label for="attach-files" class="fw-semibold text-initial nols">Attach Files</label>
                                <input type="file" class="form-control-file d-block" id="attach-files" name="attachment">
                            </div>
                        </div>
                    </div>
                    <div id="add_section_only">
                        <div class="btn-toolbar justify-content-between" role="toolbar">
                            <div class="btn-group" role="group">
                                <button type="submit" value="add" id="add_item" class="btn btn-success" style="margin-right: 10px;">Add Item</button>
                                <button type="submit" value="add_another" id="add_another" class="btn btn-warning">Add Another Item?</button>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" id="import_csv" class="btn btn-primary" accept=".xlsx">Import CSV</button>
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
                                </tr>
                            </thead>
                            <tbody>

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
<script src="{{ asset('lib/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        var isContinue = true;
        var isImportCSV = false;
        var dataArray = []; 


        $('#stockCode').hide();
        $('#add_another').hide();
        $('#add_section_only').show();

        var form = new FormData();
        var count = 0;

        $("#stock-code").keyup(function() {
            $.ajax({
                url: "{{ route('products.search') }}",
                type: 'POST',
                data: "code=" + $(this).val(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {

                    if (response.status === 'success') {
                        var data = response.data;
                        $("#stockCodeHelp").html("<p style=\"color:green;\">Product found!</p>");
                        $("#item-description").val(data.name);
                        $("#brand").val(data.brand);
                        $("#oem-id").val(data.oem);
                        $("#uom").val(data.uom);
                    } else {
                        $("#stockCodeHelp").html("<p style=\"color:red;\">Product not found!</p>");
                        $('#item-description, #brand, #uom, #oem-id').val("");
                    }
                },
                error: function(error) {

                }
            })
        });

        $('input[type=radio][name=type]').change(function() {
            if (this.value == 'new') {
                $('#stockCode').hide();
                $('#add_section_only').show();
                $('#imf')[0].reset();
                if (isContinue) {
                    $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
                } else {
                    $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', false);
                }
            } else if (this.value == 'update') {
                $("#stockCodeHelp").html("");
                $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
                $('#stockCode').show();
                $('#add_section_only').hide();
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
            
            if (selectedRadioValue === 'update') 
            {
                if (buttonClicked === 'save') {

                    form = new FormData();
                    var formData = $('#imf').serializeArray();

                    $.each(formData, function(index, field) {
                        form.append(field.name, field.value);
                    });

                    form.append('action', 'SAVED');

                    $.ajax({
                        url: "{{ route('new-stock.store') }}",
                        type: 'POST',
                        data: form,
                        contentType: false,
                        processData: false,
                        success: function(response) {

                            if (response.status === 'success') 
                            {
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
                            else 
                            {

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
            } 
            else 
            {
                if (buttonClicked === 'add') 
                { 
                    var formData = $('#imf').serializeArray();
                    var tableRow = "<tr>";
                    var itemTableCount = $('#itemTable tbody tr').length; 

                    $.each(formData, function(index, field) {
                    
                        if (field.name === '_token' || field.name === 'department' || field.name === 'type') 
                        {
                            if (!form.has(field.name)) {
                                form.append(field.name, field.value);
                            }
                        } 
                        else 
                        {
                            count = itemTableCount > 0 ? itemTableCount : count;
                            
                            if (field.name !== 'stock_code' && field.value === '') {
                                return false; 
                            }

                            form.append(`${field.name}[${count}]`, field.value);
                        }

                        var excludedFields = ['_token', 'department', 'type', 'usage_rate_qty', 'usage_frequency', 'stock_code'];

                        if (excludedFields.indexOf(field.name) === -1) 
                        {
                            tableRow += "<td>" + field.value + "</td>";
                        }

                    });

                    tableRow += "</tr>";
                    count++;

                    $('#itemTable tbody').append(tableRow);
                    $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', false);
                    isContinue = false;
                    $('#add_item').hide();
                    $('#add_another').show();
                    $('#imf')[0].reset();
                }

                if (buttonClicked === 'add_another') 
                {
                    $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
                    $('#add_item').show();
                    $('#add_another').hide();
                    isContinue = true;
                }

                if (buttonClicked === 'save') 
                {
                    if ($('#itemTable tbody tr').length > 0) {
                        
                        form.append('action', 'SAVED');

                        $.ajax({
                            url: "{{ route('new-stock.store') }}",
                            type: 'POST',
                            data: form,
                            contentType: false,
                            processData: false,
                            success: function(response) {

                                if (response.status === 'success') 
                                {
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
                        
                    } 
                    else {
                    
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
        $('#import_csv').on('click', function () {

                var fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = '.xlsx'; 

                fileInput.click();
                
                fileInput.addEventListener('change', function (e) {
                var file = e.target.files[0];
                var reader = new FileReader();

                dataArray = [];
                
                reader.onload = function (event) {
                    var data = new Uint8Array(event.target.result);
                    var workbook = XLSX.read(data, { type: 'array' });

                    var sheetName = workbook.SheetNames[0];
                    var worksheet = workbook.Sheets[sheetName];

                    var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });   

                    var tableBody = $('#itemTable tbody');
                    var fieldName = ['item_description', 'brand', 'OEM_ID', 'UoM', 'usage_rate_qty', 'usage_frequency', 'min_qty', 'max_qty', 'purpose'];
                    
                    for (var i = 1; i < jsonData.length; i++) {
                        var row = '<tr>';
                        var rowData = {};

                        if(jsonData[i].length !== fieldName.length){
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

                if(isImportCSV) 
                {
                    var formData = $('#imf').serializeArray();
                    var itemTableCount = $('#itemTable tbody tr').length; 
                    
                    count = count > 0 ? count : 0;

                    $.each(formData, function(index, field) {

                        if (field.name === '_token' || field.name === 'department' || field.name === 'type') 
                        {
                            if (!form.has(field.name)) {
                                form.append(field.name, field.value);
                            }
                        } 
                        else 
                        {
                            if (dataArray.length > 0) {
                                dataArray.forEach(function(data, dataIndex) {

                                    var dataArrayFields = Object.keys(data);
                                    
                                    if (dataArrayFields.includes(field.name)) {
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
    });
</script>
@endsection