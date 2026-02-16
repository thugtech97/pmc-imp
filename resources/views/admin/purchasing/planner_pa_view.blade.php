@extends('admin.layouts.app')

@section('pagetitle')
    View Purchase Advice
@endsection

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom-product.css') }}" rel="stylesheet">
    <script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
    <style>
        #errorMessage {
            list-style-type: none;
            padding: 0;
            margin-bottom: 0px;
        }
        .image_path {
            opacity: 0;
            width: 0;
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">IMP</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><a>Purchase Advice</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">View Purchase Advice</h4>
            </div>
        </div>
        <form id="paForm" method="POST" action="{{ route('pa.update') }}">
            @csrf
            @method('POST')
            <input type="hidden" name="pa_id" value="{{ $paHeader->id }}">
            <div class="row row-sm">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="d-block">PA Number *</label>
                        <input required name="pa_number" id="code" value="{{ $paHeader->pa_number }}" type="text" class="form-control" maxlength="150" readonly>
                    </div>
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-lg-12">
                    <label class="d-block">Items *</label>
                    <table class="table mg-b-10">
                        <thead>
                            <tr style="background-color: #f2f2f2; color: #333; border-bottom: 2px solid #ccc;">
                                <th width="10%" style="padding: 10px; text-align: center; border: 1px solid #ddd;">#</th>
                                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Stock Type</th>
                                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Inv Code</th>
                                <th width="30%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Item Description</th>
                                <th class="text-left" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Stock Code</th>
                                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">OEM No.</th>
                                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">UoM</th>
                                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">PAR To</th>
                                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">QTY To Order</th>
                                <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Previous PO#</th>
                                @if ($paHeader->received_at)
                                    <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">Current PO#</th>
                                    <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">PO Date Released</th>
                                    <th width="10%" style="padding: 10px; text-align: left; border: 1px solid #ddd;">QTY Ordered</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php $gross = 0; $discount = 0; $subtotal = 0; $count = 0; @endphp
        
                            @forelse($paHeader->details as $details)
                                @php
                                    $count++;
                                @endphp
                                <tr class="pd-20" style="border-bottom: none;">
                                    <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{ $count }}</td>
                                    <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product->stock_type ?? "N/A"}}</td>
                                    <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product->inv_code ?? "N/A"}}</td>
                                    <td class="tx-nowrap" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product->name ?? "N/A"}}</td>
                                    <td class="tx-nowrap" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product->code ?? "N/A"}}</td>
                                    <td class="tx-center" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product->oem ?? "N/A"}}</td>
                                    <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">{{$details->product->uom ?? "N/A"}}</td>
                                    <td class="tx-nowrap" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                        <input type="text" name="par_to{{ $details->id }}" value="{{ $details->par_to }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'readonly' : '' }} required>
                                    </td>    
                                    <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                        <input type="number" name="qty_to_order{{ $details->id }}" value="{{ $details->qty_to_order }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'readonly' : '' }} required>
                                    </td>
                                    <td class="tx-right" style="padding: 10px; text-align: left; border: 1px solid #ddd;">
                                        <input type="text" name="previous_po{{ $details->id }}" value="{{ $details->previous_po }}" class="form-control" {{ $role->name !== "MCD Planner" ? 'readonly' : '' }} required>
                                    </td>
        
                                    @if ($paHeader->received_at)
                                        <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd;">
                                            <input type="text" name="current_po{{ $details->id }}" value="{{ $details->current_po }}" class="form-control" {{ $role->name !== "Purchaser" ? 'readonly' : '' }}>
                                        </td>
                                        <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd;">
                                            <input type="date" name="po_date_released{{ $details->id }}" value="{{ $details->po_date_released ? \Carbon\Carbon::parse($details->po_date_released)->format('Y-m-d') : '' }}" class="form-control" {{ $role->name !== "Purchaser" ? 'readonly' : '' }}>
                                        </td>
                                        <td class="tx-center" style="padding: 10px; text-align: center; border: 1px solid #ddd;">
                                            <input type="number" data-qty="{{ $details->qty_to_order }}" name="qty_ordered{{ $details->id }}" value="{{ $details->qty_ordered }}" class="form-control qty_ordered" {{ $role->name !== "Purchaser" ? 'readonly' : '' }}>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td class="tx-center " colspan="6">No transaction found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>                    
                </div>
            </div>

            <div class="row row-sm">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="d-block">Planner Remarks *</label>
                        <textarea rows="6" required name="planner_remarks" id="planner_remarks" {{ $role->name !== "MCD Planner" ? 'readonly' : '' }} class="form-control">{{ $paHeader->planner_remarks }}</textarea>
                    </div>
                </div>
            </div>

            @if (!empty($paHeader->supporting_documents))
                <div>
                    <label class="d-block font-weight-bold">Supporting Documents</label>
                    <ul class="list-unstyled">
                        @foreach (explode('|', $paHeader->supporting_documents) as $index => $path)
                            <li>
                                <a href="{{ asset('storage/' . $path) }}" target="_blank">
                                    Document {{ $index + 1 }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif


            @if($role->name === "Purchasing Officer")
                <div class="row">
                    <div class="col-3 request-details">
                        <span><strong class="title">Assign To:</strong> 
                            <select type="text" class="form-control employees" id="purchasers">
                                @foreach ($purchasers as $purchaser)
                                    <option value="{{ $purchaser->id }}" {{ $purchaser->id == $paHeader->received_by ? 'selected' : '' }}>{{ $purchaser->name }}</option>
                                @endforeach
                            </select>
                        </span><br>
                    </div>
                </div>
            @endif

            <div class="col-lg-12 mg-t-30">
                @if($role->name == "MCD Verifier")
                    <button {{ $paHeader->verified_at ? 'disabled' : '' }} type="button" id="verifyBtn" class="btn btn-outline-success btn-sm btn-uppercase">
                        {{ $paHeader->verified_at ? 'Verified' : 'Verify' }}
                    </button>
                @endif
                @if($role->name == "MCD Approver")
                    <button {{ $paHeader->approved_at ? 'disabled' : '' }} type="button" id="approveBtn" class="btn btn-outline-success btn-sm btn-uppercase">
                        {{ $paHeader->approved_at ? 'Approved' : 'Approve' }}
                    </button>
                @endif
                @if($role->name === "Purchasing Officer")
                    <button type="button" id="assignBtn" class="btn btn-outline-success btn-sm btn-uppercase">Assign</button>
                @endif
                @if($role->name === "Purchaser")
                    <button {{ $paHeader->received_at ? 'disabled' : '' }} type="button" id="receiveBtn" class="btn btn-outline-success btn-sm btn-uppercase">
                        {{ $paHeader->received_at ? 'Received' : 'Receive' }}
                    </button>
                @endif
                @if($role->name === "MCD Planner")
                    <input type="submit" class="btn btn-outline-success btn-sm btn-uppercase" value="Update">
                @endif
                @if($role->name === "Purchaser" && $paHeader->received_at)
                    <input type="submit" class="btn btn-outline-success btn-sm btn-uppercase" value="Update">
                @endif
                @if($role->name == "MCD Approver" || $role->name === "Purchasing Officer" || $role->name === "Purchaser")
                    <button type="button" id="cancelBtn" class="btn btn-outline-danger btn-sm btn-uppercase">
                        CANCEL
                    </button>
                @endif
                <a href="{{ route('planner_pa.index') }}" class="btn btn-outline-secondary btn-sm btn-uppercase">Back</a>
            </div>
        </form>
    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    <script src="{{ asset('lib/bootstrap-tagsinput/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ asset('lib/jqueryui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/image-upload-validation.js') }}"></script>
@endsection

@section('customjs')
    <script>
        $(document).ready(function() {
            $('#verifyBtn').click(function(event) {
                event.preventDefault();
                var url = "{{ route('pa.purchase_action', ['action' => 'verify', 'id' => $paHeader->id]) }}";
                window.location.href = url;
            });
            $('#approveBtn').click(function(event) {
                event.preventDefault();
                var url = "{{ route('pa.purchase_action', ['action' => 'approve', 'id' => $paHeader->id]) }}";
                window.location.href = url;
            });
            $('#assignBtn').click(function(event) {
                event.preventDefault();
                var note = encodeURIComponent($('#purchasers').val());
                var url = "{{ route('pa.purchase_action', ['action' => 'assign', 'id' => $paHeader->id]) }}&note=" + note;
                window.location.href = url;
            });
            $('#receiveBtn').click(function(event) {
                event.preventDefault();
                var url = "{{ route('pa.purchase_action', ['action' => 'receive', 'id' => $paHeader->id]) }}";
                window.location.href = url;
            });
            $('#cancelBtn').click(function(event) {
                event.preventDefault();
                var url = "{{ route('pa.purchase_action', ['action' => 'cancel', 'id' => $paHeader->id]) }}";
                window.location.href = url;
            });

        });
    </script>
@endsection
