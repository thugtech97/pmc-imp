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
                                <label for="item-description" class="fw-semibold text-initial nols">Item Description</label>
                                <textarea id="item-description" class="form-control form-input" name="item_description" required rows="3">{{ $request->type == "update" ? $items[0]->item_description : '' }}</textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label for="brand" class="fw-semibold text-initial nols">Brand</label>
                                <input type="text" id="brand" class="form-control form-input" name="brand" value="{{ $request->type == "update" ? $items[0]->brand : '' }}" required />
                            </div>

                            <div class="form-group mb-4">
                                <label for="oem-id" class="fw-semibold text-initial nols">OEM ID</label>
                                <input type="text" id="oem-id" class="form-control form-input" name="OEM_ID" value="{{ $request->type == "update" ? $items[0]->OEM_ID : '' }}" required />
                            </div>

                            <div class="form-group mb-4">
                                <label for="uom" class="fw-semibold text-initial nols">Unit of Measure (UoM)</label>
                                <input type="text" id="uom" class="form-control form-input" name="UoM" value="{{ $request->type == "update" ? $items[0]->UoM : '' }}" required />
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-rate-qty" class="fw-semibold text-initial nols">Usage Rate Qty</label>
                                        <input type="number" id="usage-rate-qty" class="form-control form-input" value="{{ $request->type == "update" ? $items[0]->usage_rate_qty : '' }}" name="usage_rate_qty" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="usage-frequency" class="fw-semibold text-initial nols">Usage Frequency</label>
                                        <!--<input type="text" id="usage-frequency" class="form-control form-input" name="usage_frequency" />
                                        <small id="emailHelp" class="form-text text-muted">(D/W/M/Y, etc)</small>-->
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
                                        <label for="min-qty" class="fw-semibold text-initial nols">Min Qty</label>
                                        <input type="number" id="min-qty" class="form-control form-input" name="min_qty" value="{{ $request->type == "update" ? $items[0]->min_qty : '' }}" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label for="max-qty" class="fw-semibold text-initial nols">Max Qty</label>
                                        <input type="number" id="max-qty" class="form-control form-input" name="max_qty" value="{{ $request->type == "update" ? $items[0]->max_qty : '' }}" required />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="purpose" class="fw-semibold text-initial nols">Item to be used for/Application/Purpose</label>
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
                            <button type="submit" value="add" id="add_item" class="btn btn-success">Add Item</button>&nbsp;
                            <button type="submit" value="add_another" id="add_another" class="btn btn-warning">Add Another Item?</button>&nbsp;
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
                                @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->item_description }}</td>
                                    <td>{{ $item->brand }}</td>
                                    <td>{{ $item->OEM_ID }}</td>
                                    <td>{{ $item->UoM }}</td>
                                    <td>{{ $item->min_qty }}</td>
                                    <td>{{ $item->max_qty }}</td>
                                    <td>{{ $item->purpose }}</td>
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
        $('#add_section_only').show();

        var form = new FormData();
        var count = 0;
        var type = '{{ $request->type }}';

        if (type == 'new') {
            $('#stockCode').hide();
            $('#add_section_only').show();
            loadItems();
            if (isContinue) {
                $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
            } else {
                $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', false);
            }
        } else {
            $('#item-description, #brand, #uom, #oem-id, #usage-rate-qty, #usage-frequency, #min-qty, #max-qty, #purpose').prop('required', true);
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
                    });

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

                    $.each(formData, function(index, field) {
                        if (field.name === '_token' || field.name === 'department' || field.name === 'type') {

                        } else {
                            if (field.name === 'stock_code') {
                                stockCodeExists = true;
                            }
                            form.append(`${field.name}[${count}]`, field.value);
                        }

                        var excludedFields = ['_token', 'department', 'type', 'usage_rate_qty', 'usage_frequency', 'stock_code'];
                        if (excludedFields.indexOf(field.name) === -1) {
                            tableRow += "<td>" + field.value + "</td>";
                        }
                    });

                    // If stock_code doesn't exist in the formData, append it with an empty value
                    if (!stockCodeExists) {
                        form.append(`stock_code[${count}]`, '');
                    }

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