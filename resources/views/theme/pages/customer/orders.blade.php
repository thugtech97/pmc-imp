@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <!-- DataTable Stylesheets -->
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-dt/css/jquery.dataTables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}" type="text/css" />
	<link rel="stylesheet" href="{{ asset('lib/datatables.net-buttons/css/buttons.bootstrap.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('lib/js-snackbar/js-snackbar.css') }}" type="text/css" />

    <style>
        .modal-size .modal-dialog {
            max-width: 80% !important;
            width: 80% !important; 
        }

        .badge {
            background-color: #38c172 !important;
            color: white;
            padding: 4px 8px;
            text-align: center;
            border-radius: 5px;
        }

        .request-details {
            display: table;
        }

        .request-details span {
            display: table-row;
        }

        .request-details strong {
            display: table-cell;
            padding-right: 15px;
            text-align: left;
            white-space: nowrap;
            color: black;
        }

        .request-details .detail-value {
            display: table-cell;
            text-align: left;
            color: black;
        }
    </style>
@endsection
@section('content')
@php
    $modals='';
@endphp

<div class="container-fluid">
    <div class="row">
		<div class="col-md-12">
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

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <a href="{{ route('cart.front.show') }}" class="button button-dark button-border button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow mb-4">Add New MRS</a>
            <a href="{{ route('export.users') }}?type=deptuser" class="button button-dark button-border button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow mb-4">Export As Excel</a>

            <table id="inventoryTable" class="table table-bordered table-striped">
                <thead class="text-center">
                    <tr>
                        <th>MRS#</th>
                        <th>Created Date</th>
                        <th>Posted Date</th>
                        <!--<th>Ordered</th>
                        <th>Delivered</th>
                        <th>Balance</th>!-->
                        <th>Costcodes</th>
                        <th>Request Status</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td class="text-center">{{$sale->order_number}}</td>
                            <td class="text-center">{{ $sale->created_at }}</td>
                            <td class="text-center">{{ $sale->date_posted ? date('Y-m-d H:i:s', strtotime($sale->date_posted)) : '-' }}</td>
                            <td class="text-center">
                                @php
                                    $costcodes = explode(',', $sale->costcode);
                                @endphp
                                @foreach ($costcodes as $code)
                                    <span class="badge">{{ trim($code) }}</span>
                                    @if (!$loop->last)
                                        </br>
                                    @endif
                                @endforeach
                            </td>
                            <td class="text-center">
                                <span class="{{ strpos($sale->status, 'CANCELLED') !== false ? 'text-danger' : 'text-success' }}">
                                    @if ($sale->received_at)
                                        <u><i class="icon-print"></i> 
                                        <a href="javascript:;" class="print text-success" data-order-number="{{ $sale->order_number }}">
                                            RECEIVED FOR CANVASS ({{ strtoupper($sale->purchaser->name) }})
                                        </a></u>
                                    @elseif ($sale->approved_at)
                                    <u><i class="icon-print"></i> 
                                        <a href="javascript:;" class="print text-success" data-order-number="{{ $sale->order_number }}">
                                            APPROVED BY MCD MANAGER - PA FOR DELEGATION
                                        </a></u>
                                    @else
                                        {{ strtoupper($sale->status) }}
                                    @endif
                                    @if ($sale->hasPromo())
                                        <br/>
                                        @php
                                            $hold = $sale->items->where('promo_id', 1)->count();
                                            $is_pa = $sale->items->where('promo_id', 1)->whereNotNull('is_pa')->count();
                                        @endphp
                                        @if($hold !== $is_pa)
                                            <span class="text-warning">
                                                ({{  $sale->items->where('promo_id', 1)->whereNull('is_pa')->count() }} OUT OF {{ $sale->items->count() }} ITEMS ON-HOLD)
                                            </span>
                                        @endif
                                    @endif
                                </span>
                            </td>                                                        
                            <td>
                                @if (!(strpos($sale->status, 'CANCELLED') !== false))
                                    <a href="#" onclick="view_items('{{$sale->id}}');" title="View Details" aria-expanded="false">
                                        <i class="icon-eye"></i>
                                    </a>
                                @endif
                                @if (!$sale->approved_at && !(strpos($sale->status, 'CANCELLED') !== false))
                                    <a href="javascript:;" onclick="cancel_unpaid_order('{{$sale->id}}')" title="Cancel MRS"><i class="icon-forbidden"></i></a>
                                @endif
                                @if ($sale->approved_at)
                                    <span class="text-success"><i class="icon-check"></i></span>
                                @endif
                                @if (strpos($sale->status, 'ON-HOLD') !== false || strpos($sale->status, 'ON HOLD') !== false)
                                    <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
                                        <i class="icon-pencil"></i>
                                    </a>
                                    <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'resubmitted']) }}" title="Resubmit"><i class="icon-refresh"></i></a>
                                @endif
                                @if ($sale->hasPromo() && !$sale->received_at && strpos($sale->status, 'APPROVED (MCD Planner)') !== false)
                                    <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
                                        <i class="icon-pencil"></i>
                                    </a>
                                    <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'resubmitted']) }}" title="Resubmit"><i class="icon-refresh"></i></a>
                                @endif
                                @if ($sale->hasPromo() && $sale->received_at)
                                    <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
                                        <i class="icon-pencil"></i>
                                    </a>
                                @endif
                                @if (strpos($sale->status, 'CANCELLED') !== false)
                                    <a href="#" onclick="view_items('{{$sale->id}}');" title="View Details" aria-expanded="false">
                                        <i class="icon-eye"></i>
                                    </a>
                                @endif

                                @switch($sale->status)
                                    @case('SAVED')
                                    @case('saved')
                                        <a href="javascript:;" onclick="edit_item('{{$sale->id}}');" title="Edit Details" aria-expanded="false">
                                            <i class="icon-pencil"></i>
                                        </a>
                                        <a href="{{ route('my-account.submit.request', ['id' => $sale->id, 'status' => 'submitted']) }}" title="Submit for Approval"><i class="icon-upload"></i></a>
                                        @break
                                    @case('posted')
                                        <a href="#" title="View Deliveries" onclick="view_deliveries('{{$sale->id}}');"><i class="icon-truck"></i></a>
                                        @break
                                @endswitch

                                {{--@if($sale->status == 'CANCELLED' || $sale->delivery_status == 'Delivered')
                                    <a class="dropdown-item" href="#" onclick="reorder('{{$sale->id}}')">Reorder</a>
                                @endif--}}

                                @if($sale->issuances->count() > 0)
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#issuanceDetailsModal{{ $sale->id }}" aria-expanded="false" title="View Issuances">
                                    <i class="icon-file"></i>
                                </a>
                                @endif
                            </td>
                        </tr>

                        @php
                            $paths = "";
                            foreach (explode('|', $sale->order_source) as $filePath) {
                                $paths .= '<a href="' . asset('storage/' . $filePath) . '" target="_blank" style="display: block; margin-bottom: 5px;">
                                                <i class="icon-download-alt" style="margin-right: 5px;"></i>
                                                ' . basename($filePath) . '
                                        </a>';
                            }
                            $modals .='
                                <div class="modal fade bs-example-modal-centered modal-size" id="viewdetail'.$sale->id.'" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post" class="m-0" action="' . route("my-account.update.order", $sale->id) . '">
                                            <input type="hidden" name="_token" value="'.csrf_token().'">
                                            <input type="hidden" name="_method" value="PUT">

                                            <div class="modal-header">
                                                <h4 class="modal-title" id="myModalLabel">MRS No. '.$sale->order_number.'</h4> 
                                                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="request-details">
                                                            <span><strong>Request Date:</strong> <span class="detail-value">'. $sale->created_at.'</span></span>
                                                            <span><strong>Request Status:</strong> <span class="detail-value">'.strtoupper($sale->status).'</span></span>
                                                            <span><strong>Department:</strong> <span class="detail-value">'.auth()->user()->department->name.'</span></span>
                                                            <span><strong>Section:</strong> <span class="detail-value">'.$sale->section.'</span></span>
                                                            <span><strong>Date Needed:</strong> <span class="detail-value">'.$sale->delivery_date.'</span></span>
                                                            <span><strong>Requested By:</strong> <span class="detail-value">'.$sale->requested_by.'</span></span>
                                                            <span><strong>Processed By:</strong> <span class="detail-value">'.strtoupper($sale->user->name).'</span></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="request-details">
                                                            <span><strong>Delivery Type:</strong> <span class="detail-value">'.$sale->delivery_type.'</span></span>
                                                            <span><strong>Delivery Address:</strong> <span class="detail-value">'.$sale->customer_delivery_adress.'</span></span>
                                                            <span><strong>Budgeted:</strong> <span class="detail-value">'.($sale->budgeted_amount > 0 ? 'YES' : 'NO').'</span></span>
                                                            <span><strong>Budgeted Amount:</strong> <span class="detail-value">'.number_format($sale->budgeted_amount, 2, '.', ',').'</span></span>
                                                            <span><strong>Other Instructions:</strong> <span class="detail-value">'.$sale->other_instruction.'</span></span>
                                                            <span><strong>Note:</strong> <span class="detail-value">'.$sale->purpose.'</span></span>
                                                            <span><strong>Attachment:</strong> 
                                                                <span class="detail-value">
                                                                    '.$paths.'
                                                                </span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="gap-20"></div>
                                                <br><br>
                                                <div class="table-modal-wrap">
                                                    <table class="table table-md table-modal" style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px; margin: 20px 0;">
                                                        <thead>
                                                            <tr style="background-color: #f2f2f2; color: #333; border-bottom: 2px solid #ccc;">
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">ITEM #</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Priority</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Stock Code</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">OEM</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">UoM</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">PAR To</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Frequency</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Date Needed</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Cost Code</th>
                                                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Qty</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';

                                                                $total_qty = 0;
                                                                $total_sales = 0;
                                                                $count = 0;
                                                            foreach($sale->items as $item){
                                                                $count++;
                                                                $total_qty += $item->qty;
                                                                $total_sales += $item->qty * $item->price;
                                                                $modals.='
                                                                <tr style="border-bottom: 1px solid #ddd;">
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$count.'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$sale->priority.'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$item->product->code.'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$item->product_name.'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.($item->product->oem ?? "NONE").'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$item->product->uom.'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.((explode(':', $item->par_to)[0]) ? explode(':', $item->par_to)[0] : "NONE").'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$item->frequency.'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.\Carbon\Carbon::parse($item->date_needed)->format('m/d/Y').'</td>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;">'.$item->cost_code.'</td>
                                                                    <td style="padding: 10px; text-align: right; border: 1px solid #ddd;">'. (int)$item->qty.'</td>
                                                                </tr>
                                                                <tr style="border-bottom: 1px solid #ddd;">
                                                                    <th style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="2">PURPOSE</th>
                                                                    <td style="padding: 10px; text-align: left; border: 1px solid #ddd;" colspan="9">'.$item->purpose.'</td>
                                                                </tr>';
                                                                
                                                            }

                                                            $delivery_discount = \App\Models\Ecommerce\CouponSale::total_discount_delivery($sale->id);
                                                            $grossAmount = ($total_sales-$sale->discount_amount)+($sale->delivery_fee_amount-$delivery_discount);

                                                            $modals.='
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="gap-20"></div>';

                                                if ($sale->note_planner || $sale->note_verifier) {
                                                    $modals .= '
                                                    <br><br>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="request-details">
                                                                <span><strong>MCD PLANNER NOTE:</strong> <span class="detail-value">'. $sale->note_planner.'</span></span>
                                                            </div>
                                                        </div>
                                                    </div>';
                                                }
                                                $modals .= '
                                            </div>
                                            <div class="modal-footer">
                                                <!--<input type="submit" class="btn btn-primary" value="Update Quantity">-->
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            ';
                        @endphp
                    @empty
                        <tr>
                            <td colspan="8">No MRS found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
	</div>
</div>

{!!$modals!!}

@include('theme.pages.customer.edit-mrs')

<div class="modal fade bs-example-modal-centered" id="reorder_form" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable ">
        <div class="modal-content">
            <form action="{{route('my-account.reorder')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reorder?</p>
                    <input type="hidden" id="order_id" name="order_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade bs-example-modal-centered" id="cancel_order" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable ">
        <div class="modal-content">
            <form action="{{route('my-account.cancel-order')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this MRS?</p>
                    <input type="hidden" id="orderid" name="orderid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade bs-example-modal-centered" id="upload_payment_modal" tabindex="-1" role="dialog" aria-labelledby="centerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable ">
        <div class="modal-content">
            <form action="{{route('order.submit-payment')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Upload Payment</h4>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Amount *</label>
                        <input readonly type="text" name="amount" class="form-control" id="order_amount">
                    </div>

                    <div class="form-group">
                        <label>Receipt # *</label>
                        <input required type="text" name="receipt_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Remarks </label>
                        <textarea class="form-control" name="remarks" rows="5"></textarea>
                    </div>

                    <input required type="file" name="attachments[]" class="form-controlP">

                    <input type="hidden" id="salesID" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('pagejs')
    <script src="{{ asset('lib/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('lib/js-snackbar/js-snackbar.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script src="{{ asset('lib/datatables.net/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-dt/js/dataTables.dataTables.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
	<script src="{{ asset('lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js') }}"></script>
	<script>
        var employees;
        $(document).ready(function(){
            employee_lookup();
        });
        /*
        function changeShippingType(id) {
            if ($('#shippingType'+id).val() === "Delivery") {
                $('#customerAddress'+id).show();
                $('#deliveryDate'+id).show();
            }
            else {
                $('#deliveryDate'+id).hide();
                $('#customerAddress'+id).hide();
            }
        }
        */

        function employee_lookup() {
            if (localStorage.getItem("EMP") !== null) {
                let values = localStorage.getItem("EMP");
                employees = values;
                initEmpValues(employees.split("|"));
            }else{
                $.ajax({
                    type: 'GET',
                    url: "{{ route('users.employee_lookup') }}",
                    success: function(data){
                        try {
                            var employeesArray = JSON.parse(data);
                            let values = employeesArray.map(item => item.fullnamewithdept).join('|');
                            //console.log(values);
                            employees = values;
                            localStorage.setItem("EMP", employees);
                            initEmpValues(employees.split("|"))
                        } catch (e) {
                            console.error("Error parsing JSON: ", e);
                        }
                    }
                });
            }
        }

        function initEmpValues(employeesArray){
            $('.employees').empty();
            $('.employees').append('<option value="" disabled selected>Select an employee</option>');
            employeesArray.forEach(function(employee) {
                var fullname = employee.split(":")[0];
                $('.employees').append('<option value="' + employee + '">' + fullname + '</option>');
            });
        }

        function changeIsbudget(id, value){
            $("#budgeted_amount"+id).val("");
            if (value == 1) {
                $('#budgetAmount'+id).css('visibility', 'visible');
            } else {
                $('#budgetAmount'+id).css('visibility', 'hidden');
            }
        }

        function view_items(salesID){
            $('#viewdetail'+salesID).modal('show');
        }

        function edit_item(salesID){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                data: {
                    "mrs": salesID,
                    "_token": "{{ csrf_token() }}",
                },
                type: "post",
                url: "{{route('mrs.getDetails')}}",
                success: function(data) {
                    
                    let headers = data.headers
                    let items = data.headers.items
                    
                    let id = headers.id;
                    let url = `{{ route('my-account.update.order', ':id') }}`;
                    url = url.replace(':id', id);
                    $('#edit_form').attr('action', url);
                    let hasPromo = data.hasPromo && !data.received_at && 
                                    !(headers.status.includes("ON HOLD") || 
                                    headers.status.includes("ON-HOLD"));
                    $("#mrs_id").val(headers.id);
                    $("#mrs_no").html(headers.order_number)
                    $("#request_date").html(headers.created_at)
                    $("#request_status").html(headers.status)
                    $("#planner_note").html(headers.note_planner)
                    $("#priority_no").val(headers.priority)
                    $("#department").val(headers.customer_name)
                    $("#purpose").val(headers.purpose)
                    $("#requested_by").val(headers.requested_by)
                    $("#date_needed").val(headers.delivery_date)
                    $("#budgeted").val(headers.budgeted_amount)
                    $("#isBudgeted").val(headers.budgeted_amount > 0 ? "1" : "0");
                    $("#section").val(headers.section)
                    $("#notes").val(headers.other_instruction)
                    $(".edit_mrs_field").prop('readonly', false);
                    $(".edit_mrs_select").off('mousedown');
                    $("#add_item_mrs").show();
                    $("#alert_purpose_resubmission").hide();

                    if (headers.order_source) {
                        $("#file-display").remove();
                        let filePaths = headers.order_source.split('|');
                        let fileLinksHTML = '<div id="file-display">';
                        filePaths.forEach(filePath => {
                            let fileName = filePath.split('/').pop();
                            fileLinksHTML += `
                                <a href="storage/${filePath}" target="_blank" style="display: block; margin-bottom: 5px;">
                                    <i class="icon-download-alt" style="margin-right: 5px;"></i>
                                    ${fileName}
                                </a>
                            `;
                        });

                        fileLinksHTML += '</div>';
                        $("#attachment").after(fileLinksHTML);
                    }


                    if(hasPromo){
                        $(".edit_mrs_field").prop('readonly', true);
                        $("#alert_purpose_resubmission").show();
                        $(".edit_mrs_select").on('mousedown', function(e){
                            e.preventDefault();
                        });

                    }
                    if(headers.status === "SAVED" /* || headers.received_at */){
                        $("#add_item_mrs").hide();
                    }

                    $("#mrs_items").empty();
                    items.forEach(function(item, index) {
                        let costcodeOptions = '';
                        let costcodes = localStorage.getItem('CC') ? localStorage.getItem('CC').split(',') : [];
                        costcodes.forEach(function(cc) {
                            let selected = (cc === item.cost_code) ? 'selected' : '';
                            costcodeOptions += `<option value="${cc}" ${selected}>${cc}</option>`;
                        });

                        let row = `<tr id="row-${item.id}" style="${hasPromo && item.promo_id == 0 ? 'background-color: #C0C0C0;' : ''}">
                                        <td>
                                            <strong>(${item.product.code}) ${item.product_name}</strong>
                                            <p><small class="text-muted">(${item.uom})<br>Costcode: 
                                                <select class="cost_code" name="cost_code[${item.id}]" ${hasPromo && item.promo_id == 0 ? 'disabled' : ''} required>
                                                    ${costcodeOptions}
                                                </select>
                                                </small></p>
                                             ${ hasPromo && item.promo_id == 1 ? '<p><b>Hold remarks:</b> '+item.promo_description+'</p>' : '' }
                                        </td>
                                        <td>
                                            <select class="form-select par_to" name="par_to[${item.id}]">
                                                <option value="N/A" selected>Select an employee</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select frequency" name="frequency[${item.id}]" required>
                                                <option value="Daily" ${item.frequency === 'Daily' ? 'selected' : ''}>Daily</option>
                                                <option value="Weekly" ${item.frequency === 'Weekly' ? 'selected' : ''}>Weekly</option>
                                                <option value="Monthly" ${item.frequency === 'Monthly' ? 'selected' : ''}>Monthly</option>
                                                <option value="Yearly" ${item.frequency === 'Yearly' ? 'selected' : ''}>Yearly</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control purpose_item" name="purpose[${item.id}]" value="${item.purpose}" ${hasPromo && item.promo_id == 0 ? 'readonly' : ''} required>
                                        </td>
                                        <td>
                                            <input type="date" class="form-control" name="date_needed[${item.id}]" value="${item.date_needed.split(' ')[0]}" ${hasPromo && item.promo_id == 0 ? 'readonly' : ''} required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="qty[${item.id}]" min="1" value="${parseInt(item.qty)}" ${hasPromo && item.promo_id == 0 ? 'readonly' : ''} required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row" data-id="${item.id}" ${hasPromo && item.promo_id == 0 ? 'disabled' : ''}><i class="icon-trash"></i></button>
                                        </td>
                                    </tr>`;
                        $("#mrs_items").append(row);
                        let selectElement = $(`#row-${item.id} .par_to`); let selectElement2 = $(`#row-${item.id} .frequency`); 
                        let employeesArr = employees.split("|");
                        employeesArr.forEach(function(employee) {
                            let fullname = employee.split(":")[0];
                            let selected = (employee === item.par_to) ? 'selected' : '';
                            selectElement.append('<option value="' + employee + '" ' + selected + '>' + fullname + '</option>');
                        });
                        if(hasPromo && item.promo_id == 0){
                            selectElement.on('mousedown', function(e){ e.preventDefault(); });
                            selectElement2.on('mousedown', function(e){ e.preventDefault(); });
                        }
                        
                    });
                    $('#editdetail').modal('show');
                    
                }
            });
        }

        function add_item(){
            let costcodeOptions = ''; // Initialize an empty string for options
            let costcodes = localStorage.getItem('CC') ? localStorage.getItem('CC').split(',') : []; // Fetch and split CC
            costcodes.forEach(function(cc) {
                costcodeOptions += `<option value="${cc}">${cc}</option>`;
            });
            let row =   `<tr>
                            <td>
                                <input list="products" placeholder="Search products here..." name="product" class="form-control" id="product" onblur="product_search(this.value)">
                                <datalist id="products">
                                </datalist>
                                <input type="hidden" value="${$('#mrs_id').val()}" name="mrs_id">
                                <strong></strong>
                                <p><small class="text-muted"><br>Costcode: 
                                    <select id="cost_code_item" name="cost_code_item" required>
                                        ${costcodeOptions}
                                    </select>
                            </td>
                            <td>
                                <select class="form-select par_to_item" name="par_to_item">
                                    <option value="N/A" selected>Select an employee</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-select" name="frequency_item">
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Yearly">Yearly</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="purpose_item">
                            </td>
                            <td>
                                <input type="date" class="form-control" name="date_needed_item" required>
                            </td>
                            <td>
                                <input type="number" class="form-control" name="quantity_item" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm" onclick="submitItem(this);"><i class="icon-checkmark"></i></button>
                            </td>
                        </tr>`;
            $("#mrs_items").append(row);
            let selectElement = $(`.par_to_item`);
            let employeesArr = employees.split("|");
            employeesArr.forEach(function(employee) {
                let fullname = employee.split(":")[0];
                selectElement.append('<option value="' + employee + '">' + fullname + '</option>');
            });
        }

        function product_search(name){
            $.ajax({
                type: 'POST',
                url: "{{ route('products.lookup') }}",
                data: { name: name },
                success: function(response) {
                    const datalist = document.getElementById('products');
                    datalist.innerHTML = '';

                    response.forEach(option => {
                        const opt = document.createElement('option');
                        opt.value = option.name;
                        opt.text = option.code;
                        opt.setAttribute('data-id', option.id);
                        datalist.appendChild(opt);
                    });

                    var textbox = document.getElementById("product");
                    textbox.addEventListener(
                        "input",
                        function (e) {
                            var value = e.target.value;
                            var selectedOption = Array.from(document.getElementById('products').options).find(option => option.value === value);
                            
                            if (selectedOption) {
                                var productId = selectedOption.getAttribute('data-id');
                                $("#product").data('product-id', productId);
                            }
                        },
                        false
                    );
                },
                error: function(xhr) {
                    console.error('Error fetching products:', xhr);
                }
            });
        }

        function submitItem(button) {
            const row = $(button).closest('tr');
            const data = {
                mrs_id: row.find('input[name="mrs_id"]').val(),
                product_id: row.find('input[name="product"]').data('product-id'),
                cost_code_item: row.find('select[name="cost_code_item"]').val(),
                par_to_item: row.find('select[name="par_to_item"]').val(),
                frequency_item: row.find('select[name="frequency_item"]').val(),
                purpose_item: row.find('input[name="purpose_item"]').val(),
                date_needed_item: row.find('input[name="date_needed_item"]').val(),
                quantity_item: row.find('input[name="quantity_item"]').val()
            };

            for (let key in data) {
                if (!data[key]) { 
                    console.log('Creating snackbar for:', key);
                    new SnackBar({
                        message: key+" is required.",
                        status: "error",
                        position: 'bc',
                        width: "500px",
                        dismissible: false,
                        container: ".editdetailbody"
                    });
                    return;
                }
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('mrs.saveItem') }}",
                data: data,
                success: function(response) {
                    console.log('Item saved successfully:', response);
                    new SnackBar({
                        message: "Item saved successfully.",
                        status: "success",
                        position: 'bc',
                        width: "500px",
                        dismissible: false,
                        container: ".editdetailbody"
                    });
                    edit_item(data.mrs_id);
                },
                error: function(xhr) {
                    console.error('Error saving item:', xhr);
                }
            });
        }

        $(document).on('click', '.remove-row', function() {
            let itemId = $(this).data('id');
            swal({
                title: 'Are you sure?',
                text: "This will exclude the item from your MRS request",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'            
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        data: {
                            "item_id": itemId,
                            "_token": "{{ csrf_token() }}",
                        },
                        type: "post",
                        url: "{{route('mrs.deleteItem')}}",
                        success: function(data) {
                            $(`#row-${itemId}`).remove();
                        }
                    });
                } 
                else {                    
                    swal.close();                   
                }
            });
        });
        

        function view_deliveries(salesID){
            $('#delivery'+salesID).modal('show');
        }

        function cancel_unpaid_order(id){
            $('#orderid').val(id);
            $('#cancel_order').modal('show');
        }

        function reorder(id) {
            $('#order_id').val(id);
            $('#reorder_form').modal('show');
        }

        function upload_payment(salesID, grossAmount){
            var amount = parseFloat(grossAmount);

            $('#salesID').val(salesID);
            $('#order_amount').val(amount.toFixed(2));
            $('#upload_payment_modal').modal('show');
        }

        $.extend($.fn.dataTableExt.oStdClasses, {
				'sPageEllipsis': 'paginate_ellipsis',
				'sPageNumber': 'paginate_number',
				'sPageNumbers': 'paginate_numbers'
		});
		
		$.fn.dataTableExt.oPagination.ellipses = {
				'oDefaults': {
						'iShowPages': 3
				},
				'fnClickHandler': function(e) {
						var fnCallbackDraw = e.data.fnCallbackDraw,
								oSettings = e.data.oSettings,
								sPage = e.data.sPage;
		
						if ($(this).is('[disabled]')) {
								return false;
						}
		
						oSettings.oApi._fnPageChange(oSettings, sPage);
						fnCallbackDraw(oSettings);
		
						return true;
				},
				// fnInit is called once for each instance of pager
				'fnInit': function(oSettings, nPager, fnCallbackDraw) {
						var oClasses = oSettings.oClasses,
								oLang = oSettings.oLanguage.oPaginate,
								that = this;
		
						var iShowPages = oSettings.oInit.iShowPages || this.oDefaults.iShowPages,
								iShowPagesHalf = Math.floor(iShowPages / 2);
		
						$.extend(oSettings, {
								_iShowPages: iShowPages,
								_iShowPagesHalf: iShowPagesHalf,
						});
		
						var oFirst = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageFirst + '">' + oLang.sFirst + '</a>'),
								oPrevious = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPagePrevious + '">' + oLang.sPrevious + '</a>'),
								oNumbers = $('<span class="' + oClasses.sPageNumbers + '"></span>'),
								oNext = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageNext + '">' + oLang.sNext + '</a>'),
								oLast = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageLast + '">' + oLang.sLast + '</a>');
		
						oFirst.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'first' }, that.fnClickHandler);
						oPrevious.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'previous' }, that.fnClickHandler);
						oNext.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'next' }, that.fnClickHandler);
						oLast.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': 'last' }, that.fnClickHandler);
		
						// Draw
						$(nPager).append(oFirst, oPrevious, oNumbers, oNext, oLast);
				},
				// fnUpdate is only called once while table is rendered
				'fnUpdate': function(oSettings, fnCallbackDraw) {
						var oClasses = oSettings.oClasses,
								that = this;
		
						var tableWrapper = oSettings.nTableWrapper;
		
						// Update stateful properties
						this.fnUpdateState(oSettings);
		
						if (oSettings._iCurrentPage === 1) {
								$('.' + oClasses.sPageFirst, tableWrapper).attr('disabled', true);
								$('.' + oClasses.sPagePrevious, tableWrapper).attr('disabled', true);
						} else {
								$('.' + oClasses.sPageFirst, tableWrapper).removeAttr('disabled');
								$('.' + oClasses.sPagePrevious, tableWrapper).removeAttr('disabled');
						}
		
						if (oSettings._iTotalPages === 0 || oSettings._iCurrentPage === oSettings._iTotalPages) {
								$('.' + oClasses.sPageNext, tableWrapper).attr('disabled', true);
								$('.' + oClasses.sPageLast, tableWrapper).attr('disabled', true);
						} else {
								$('.' + oClasses.sPageNext, tableWrapper).removeAttr('disabled');
								$('.' + oClasses.sPageLast, tableWrapper).removeAttr('disabled');
						}
		
						var i, oNumber, oNumbers = $('.' + oClasses.sPageNumbers, tableWrapper);
		
						// Erase
						oNumbers.html('');
		
						for (i = oSettings._iFirstPage; i <= oSettings._iLastPage; i++) {
								oNumber = $('<a class="' + oClasses.sPageButton + ' ' + oClasses.sPageNumber + '">' + oSettings.fnFormatNumber(i) + '</a>');
		
								if (oSettings._iCurrentPage === i) {
										oNumber.attr('active', true).attr('disabled', true);
								} else {
										oNumber.click({ 'fnCallbackDraw': fnCallbackDraw, 'oSettings': oSettings, 'sPage': i - 1 }, that.fnClickHandler);
								}
		
								// Draw
								oNumbers.append(oNumber);
						}
		
						// Add ellipses
						if (1 < oSettings._iFirstPage) {
								oNumbers.prepend('<span class="' + oClasses.sPageEllipsis + '">...</span>');
						}
		
						if (oSettings._iLastPage < oSettings._iTotalPages) {
								oNumbers.append('<span class="' + oClasses.sPageEllipsis + '">...</span>');
						}
				},
				// fnUpdateState used to be part of fnUpdate
				// The reason for moving is so we can access current state info before fnUpdate is called
				'fnUpdateState': function(oSettings) {
						var iCurrentPage = Math.ceil((oSettings._iDisplayStart + 1) / oSettings._iDisplayLength),
								iTotalPages = Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength),
								iFirstPage = iCurrentPage - oSettings._iShowPagesHalf,
								iLastPage = iCurrentPage + oSettings._iShowPagesHalf;
		
						if (iTotalPages < oSettings._iShowPages) {
								iFirstPage = 1;
								iLastPage = iTotalPages;
						} else if (iFirstPage < 1) {
								iFirstPage = 1;
								iLastPage = oSettings._iShowPages;
						} else if (iLastPage > iTotalPages) {
								iFirstPage = (iTotalPages - oSettings._iShowPages) + 1;
								iLastPage = iTotalPages;
						}
		
						$.extend(oSettings, {
								_iCurrentPage: iCurrentPage,
								_iTotalPages: iTotalPages,
								_iFirstPage: iFirstPage,
								_iLastPage: iLastPage
						});
				}
		};
		$(function(){
			'use strict'

			$('#inventoryTable').DataTable({
                order: [[1, 'desc']],
				pagingType: 'ellipses',
				language: {
					searchPlaceholder: 'Search',
					sSearch: '',
					lengthMenu: 'Show _MENU_ entries',
					paginate: {
						first: `<i class="icon-line-chevrons-left"></i>`,
						next: `<i class="icon-line-chevron-right"></i>`,
						previous: `<i class="icon-line-chevron-left"></i>`,
						last: `<i class="icon-line-chevrons-right"></i>`,
					},
					processing: '<i class="fa fa-spinner fa-spin" style="font-size:24px;color:rgb(75, 183, 245);"></i>'
				},
				dom: '<"d-flex flex-column-reverse flex-lg-row flex-md-row justify-content-between mb-2" <"col1"<"#table-append1">><"col2"B>><"row" <"col-md-6"l><"col-md-6 d-flex flex-column flex-lg-row flex-md-row justify-content-end"f<"#table-append2">>><"table-responsive mb-4"t>ip',
			});
		});

        $('.print').click(function(evt) {
            evt.preventDefault();

            var orderNumber = this.getAttribute('data-order-number');

            console.log('Print button clicked', orderNumber);

            $.ajax({
                url: "{{route('pa.generate_report_customer')}}",
                type: 'GET',
                data: { orderNumber: orderNumber },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data) {
                    if (data instanceof Blob) {

                        const pdfBlob = new Blob([data], { type: 'application/pdf' });
                        const pdfUrl = URL.createObjectURL(pdfBlob);

                        window.open(pdfUrl, '_blank');
                        URL.revokeObjectURL(pdfUrl);
                        
                    }
                }
            });
        }); 
	</script>
@endsection

