@extends(auth()->check() ? 'theme.main' : 'theme.main-blank-template')

@section('pagecss')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
     <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .action-buttons {
            display: flex;
            align-items: center;
        }
        .action-buttons .btn,
        .action-buttons .print-link {
            margin-right: 10px;
        }
        .action-buttons .print-link {
            display: flex;
            align-items: center;
            padding: 0;
        }
        .row.status {
            display: flex;
            align-items: center;
            justify-content: center;

            .text-right {
                text-align: right;

                .badge {
                    display: inline-block;
                    padding: 0.7rem;
                    font-size: 0.9em;
                    font-weight: bold;
                    color: #fff; 
                    background-color: #3395ff;
                    border-radius: 0.25em;
                }
            }
        }
        .title {
            font-weight: bold;
            color: #212529;
        }
        .old-item {
            background-color: #f0f1f2 !important;
        }
        .new-value {
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
    </style>
@endsection
@section('content')
    @php
        $showStockCodeColumn = $items->isNotEmpty() && $items->contains(function ($item) {
            return $item->stock_code !== "null" && $item->stock_code !== null && $item->stock_code !== '';
        });
    @endphp
    <div class="container-fluid content-wrap">
        <div class="row">
            <div class="col-md-12">
                <div class="row status">
                    <div class="col-6"> 
                        @if(auth()->check())       
                        <div class="action-buttons">
                            <a href="{{ route('new-stock.index') }}" class="btn btn-secondary px-3">
                                Back
                            </a>
                            @if($role->name === "MCD Planner")
                            <a 
                                class="print-link btn btn-success" 
                                style="padding: 6px;"
                                title="Print Purchase Advice" 
                                id="print" 
                                data-order-number="{{$request->items[0]['imf_no']}}"
                            >
                                <i class="fas fa-print"></i> <span class="px-1">Print</span>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="col-6 text-right">
                       <span class="badge"> <strong>STATUS:</strong> {{ $request->status }} </span>
                    </div>
                </div>
                <div class="row mx-0 mt-4 mb-3">
                    <table class="m-0 text-uppercase">
                        @if($request->type === 'update' && $request->status === 'SUBMITTED')
                        <tr>
                            <td><span class="title">IMF NO:</span> {{$request->items[0]['imf_no']}} </td>
                            <td></td>
                        </tr>
                        @endif
                        <tr>
                            <td><span class="title">Department:</span> {{ $request->department }}</td>
                            <td><span class="title">Created at:</span> {{ \Carbon\Carbon::parse($request->created_at)->format('Y-m-d h:i:s A') }}</td>
                        </tr>
                        <tr>
                            <td><span class="title">Created by:</span> {{ $request->user->name}}</td>
                            <td> 
                                @if($request->updated_at != $request->created_at)
                                <span class="title">Updated at:</span> {{ \Carbon\Carbon::parse($request->updated_at)->format('Y-m-d h:i:s A') }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><span class="title">Type:</span> {{ $request->type }}</td>
                            <td>
                                @if($request->submitted_at)
                                <span class="title">Submitted at:</span> {{ \Carbon\Carbon::parse($request->submitted_at)->format('Y-m-d h:i:s A') }}
                                @endif
                            </td>
                        </tr>
                        @if($request->type === 'update' && $showStockCodeColumn)
                        <tr>
                            <td><span class="title">Stock Code:</span> {{ $items[0]->stock_code }} </td>
                            <td></td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if($request->type === 'update') 
                <table class="table">
                    <thead>
                        <th width="20%"></th>
                        <th width="40%">Old Value</th>
                        <th class="new-value">
                            <span>New Value</span>
                            @if (!empty($items[0]->file_path))
                                <a href="#" class="download-link" data-file="{{ $items[0]->file_path }}">
                                    <span data-bs-toggle="tooltip" title="Download">View Attached File</span> 
                                </a>
                            @endif
                        </th>

                    </thead>
                    <tbody>
                        <tr>
                            <th>Item Description</th>
                            @if (empty($oldItems[0]))
                            <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->item_description }}</td>
                            @else
                            <td class="old-item">{{ $oldItems[0]->item_description != '' ? $oldItems[0]->item_description : $items[0]->item_description }}</td>
                            @endif
                            <td>{{ !empty($oldItems[0]) && $oldItems[0]->item_description == '' ? '' : $items[0]->item_description }}</td>
                        </tr>
                        <tr>
                            <th>Brand</th>
                            @if (empty($oldItems[0]))
                            <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->brand }}</td>
                            @else
                            <td class="old-item">{{ $oldItems[0]->brand != '' ? $oldItems[0]->brand : $items[0]->brand }}</td>
                            @endif
                            <td>{{ !empty($oldItems[0]) && $oldItems[0]->brand == '' ? '' : $items[0]->brand }}</td>
                        </tr>
                        <tr>
                            <th>OEM ID</th>
                            @if (empty($oldItems[0]))
                            <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->OEM_ID }}</td>
                            @else
                            <td class="old-item">{{ $oldItems[0]->OEM_ID != '' ? $oldItems[0]->OEM_ID : $items[0]->OEM_ID }}</td>
                            @endif
                            <td>{{ !empty($oldItems[0]) && $oldItems[0]->OEM_ID == '' ? '' : $items[0]->OEM_ID }}</td>
                        </tr>
                        <tr>
                            <th>UoM</th>
                            @if (empty($oldItems[0]))
                            <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->UoM }}</td>
                            @else
                            <td class="old-item">{{ $oldItems[0]->UoM != '' ? $oldItems[0]->UoM : $items[0]->UoM }}</td>
                            @endif
                            <td>{{ !empty($oldItems[0]) && $oldItems[0]->UoM == '' ? '' : $items[0]->UoM }}</td>
                        </tr>
                        <tr>
                            <th>Usage Rate Qty</th>
                            @if (empty($oldItems[0]))
                            <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->usage_rate_qty }}</td>
                            @else
                            <td class="old-item">{{ $oldItems[0]->usage_rate_qty != '' ? $oldItems[0]->usage_rate_qty : $items[0]->usage_rate_qty }}</td>
                            @endif
                            <td>{{ !empty($oldItems[0]) && $oldItems[0]->usage_rate_qty == '' ? '' : $items[0]->usage_rate_qty }}</td>
                        </tr>
                        <tr>
                            <th>Usage Frequency</th>
                            @if (empty($oldItems[0]))
                            <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->usage_frequency }}</td>
                            @else
                            <td class="old-item">{{ $oldItems[0]->usage_frequency != '' ? $oldItems[0]->usage_frequency : $items[0]->usage_frequency }}</td>
                            @endif
                            <td>{{ !empty($oldItems[0]) && $oldItems[0]->usage_frequency == '' ? '' : $items[0]->usage_frequency }}</td>
                        </tr>
                        <tr>
                            <th>Min Qty</th>
                            @if (empty($oldItems[0]))
                            <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->min_qty }}</td>
                            @else
                            <td class="old-item">{{ $oldItems[0]->min_qty != '' ? $oldItems[0]->min_qty : $items[0]->min_qty }}</td>
                            @endif
                            <td>{{ !empty($oldItems[0]) && $oldItems[0]->min_qty == '' ? '' : $items[0]->min_qty }}</td>
                        </tr>
                        <tr>
                            <th>Max Qty</th>
                            @if (empty($oldItems[0]))
                            <td class="old-item">{{ empty($oldItems[0]) ? '' : $oldItems[0]->max_qty }}</td>
                            @else
                            <td class="old-item">{{ $oldItems[0]->max_qty != '' ? $oldItems[0]->max_qty : $items[0]->max_qty }}</td>
                            @endif
                            <td>{{ !empty($oldItems[0]) && $oldItems[0]->max_qty == '' ? '' : $items[0]->max_qty }}</td>
                        </tr>
                        <tr>
                            <th>Purpose</th>
                            <td class="old-item">{{ $oldItems[0]->purpose ?? '' }}</td>
                            <td>{{ $items[0]->purpose }}</td>
                        </tr>
                    </tbody>
                </table>
                @else
                <table class="table">
                    <thead>
                        @if ($showStockCodeColumn)
                            <th>Stock Code</th>
                        @endif
                        <th>Item Description</th>
                        <th>Brand</th>
                        <th>OEM ID</th>
                        <th>UoM</th>
                        <th>Usage Rate Qty</th>
                        <th>Usage Frequency</th>
                        <th>Min Qty</th>
                        <th>Max Qty</th>
                        <th width="20%">Purpose</th>
                        <th width="1%">Attachments</th>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                @if ($showStockCodeColumn)
                                    <td>{{ $item->stock_code !== "null" ? $item->stock_code : '' }}</td>
                                @endif
                                <td class="text-uppercase">{{ $item->item_description }}</td>
                                <td class="text-uppercase">{{ $item->brand }}</td>
                                <td>{{ $item->OEM_ID }}</td>
                                <td>{{ $item->UoM }}</td>
                                <td>{{ $item->usage_rate_qty }}</td>
                                <td>{{ $item->usage_frequency }}</td>
                                <td>{{ $item->min_qty }}</td>
                                <td>{{ $item->max_qty }}</td>
                                <td>{{ $item->purpose}}</td>
                                <td style="text-align: center;">
                                    @if (!empty($item->file_path))
                                    <a href="#" class="download-link" data-file="{{ $item->file_path }}" data-bs-toggle="tooltip" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-arrow-down-fill" viewBox="0 0 16 16">
                                            <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1m-1 4v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L7.5 11.293V7.5a.5.5 0 0 1 1 0"/>
                                        </svg>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center;">No requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @endif
            </div>
            <div class="row status">
                <div class="col-6"> 
                    
                </div>
                <div class="col-6 text-right">
                    @if(auth()->check() && $request->status == 'SAVED')
                    <a onclick="confirmApproval( {{ $request->id }}, 'new')" href="javascript:;" class="btn btn-success px-3">
                        Submit
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection


@section('pagejs')
<script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.download-link').click(function(e) {
            e.preventDefault(); 

            var filePath = $(this).data('file');
            var downloadUrl = "{{ route('download.files') }}?file=" + encodeURIComponent(filePath);

            var link = document.createElement('a');
            link.href = downloadUrl;
            link.target = '_blank';
            link.download = filePath; 

            document.body.appendChild(link);
            link.click();

            document.body.removeChild(link);
        });
    });

    $('#print').click(function(evt) {
        evt.preventDefault();

        var imfNo = this.getAttribute('data-order-number');
        
        $.ajax({
            url: "{{route('imf-request.generate_report')}}",
            type: 'GET',
            data: { id: imfNo },
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

    function confirmApproval(id, type) {
        Swal.fire({
            title: 'Submit for Approval',
            text: "Are you sure you want to submit this IMF for approval?",
            icon: "question",
            showCancelButton: true,
            allowOutsideClick: false,
            confirmButtonColor: '#2ecc71',
            confirmButtonText: 'Yes, submit!',
            backdrop: `rgba(0,0,0,0.7) left top no-repeat`
        }).then((result) => {

            if(result.isConfirmed) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content")
                    }
                });

                var url = "{{ route('new-stock.submit.request', ['id' => ":id", 'type' => ":type"]) }}";
                url = url.replace(':id', id);
                url = url.replace(':type', type);

                $.ajax({
                    type: 'GET',
                    url: url,
                    beforeSend: function () {
                        Swal.showLoading();
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: "success",
                            title: 'IMF Submitted!',
                            text: 'The IMF has been successfully submitted for approval.',
                            showConfirmButton: false,
                            timer: 1500,
                            backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                        }).then(() => {
                            window.location.reload(true);
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: "success",
                            title: 'IMF Submitted!',
                            text: 'The IMF has been successfully submitted for approval.',
                            showConfirmButton: false,
                            timer: 1500,
                            backdrop: `rgba(0,0,0,0.7) left top no-repeat`
                        }).then(() => {
                            window.location.reload(true);
                        });

                    }
                });
            }

        });
    }
</script>
@endsection 

