@extends('admin.layouts.app')

@section('pagecss')
    <style>
        .table td { padding: 10px; font-size: 13px; vertical-align: middle; }
        .table th { font-size: 12px; white-space: nowrap; }
        .upa-filters label { font-size: 11px; font-weight: 600; text-transform: uppercase; color: #8392a5; margin-bottom: 3px; }
        .upa-filters .form-control { font-size: 12px; }
        .upa-tiles { display: flex; flex-wrap: wrap; gap: 12px; }
        .upa-tile { flex: 1 1 150px; border: 1px solid #e3e7ed; border-radius: 6px; padding: 12px 16px; background: #fff; color: inherit; }
        a.upa-tile:hover { text-decoration: none; border-color: #0168fa; }
        .upa-tile.active { border-color: #0168fa; box-shadow: inset 0 -3px 0 #0168fa; }
        .upa-tile .lbl { font-size: 11px; font-weight: 600; text-transform: uppercase; color: #8392a5; }
        .upa-tile .val { font-size: 22px; font-weight: 600; color: #1c273c; line-height: 1.3; }
        .upa-tile.warn .val { color: #dc3545; }
        .upa-type { display: inline-block; font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; color: #fff; }
        .upa-type.DP { background: #0168fa; }
        .upa-type.SR { background: #10b759; }
        .upa-hold { display: inline-block; font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; background: #dc3545; color: #fff; }
    </style>
@endsection

@section('content')
    @php
        $exportQuery = http_build_query(request()->except('page'));
        $bucketQuery = function ($bucket) {
            $q = request()->except(['page', 'aging']);
            if ($bucket !== '') { $q['aging'] = $bucket; }
            return route('reports.unprocessed_pa') . '?' . http_build_query($q);
        };
    @endphp

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5" style="background-color:white;">
                        <li class="breadcrumb-item" aria-current="page">CMS</li>
                        <li class="breadcrumb-item" aria-current="page">Reports</li>
                        <li class="breadcrumb-item active" aria-current="page">Unprocessed PA</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Unprocessed PA Report</h4>
                <p class="tx-12 tx-color-03 mg-b-0 mg-t-5">
                    PAs received for canvass that still have quantity left to order, as of {{ $report->now->format('m/d/Y h:i A') }}.
                    @if($filters['include_zero'])
                        Cancelled PAs are left out; <strong>fully ordered (zero balance) PAs are included</strong>.
                    @else
                        Cancelled PAs and fully ordered (zero balance) PAs are left out.
                    @endif
                </p>
            </div>
            <div class="mg-t-10 mg-sm-t-0">
                <a class="btn btn-sm btn-success" href="{{ route('reports.unprocessed_pa.export') }}?{{ $exportQuery }}">
                    <i class="fa fa-file-excel"></i> Export to Excel
                </a>
            </div>
        </div>

        {{-- Summary --}}
        <div class="upa-tiles mg-b-20">
            <a class="upa-tile {{ $filters['aging'] === '' ? 'active' : '' }}" href="{{ $bucketQuery('') }}">
                <div class="lbl">Unprocessed PAs</div>
                <div class="val">{{ number_format($summary['total']) }}</div>
            </a>
            <div class="upa-tile">
                <div class="lbl">Total balance qty</div>
                <div class="val">{{ number_format($summary['balance'], 0) }}</div>
            </div>
            @foreach($summary['buckets'] as $bucket => $count)
                <a class="upa-tile {{ in_array($bucket, ['15-30', '31+']) && $count > 0 ? 'warn' : '' }} {{ $filters['aging'] === $bucket ? 'active' : '' }}"
                   href="{{ $bucketQuery($bucket) }}" title="Show only PAs aged {{ $bucket }} days">
                    <div class="lbl">Aging {{ $bucket }} days</div>
                    <div class="val">{{ number_format($count) }}</div>
                </a>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('reports.unprocessed_pa') }}" class="upa-filters mg-b-15">
            <div class="row row-xs align-items-end">
                <div class="col-6 col-md-2 mg-b-10">
                    <label for="search">MRS / PA No.</label>
                    <input type="search" name="search" id="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Search">
                </div>
                <div class="col-6 col-md-1 mg-b-10">
                    <label for="type">Type</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">All</option>
                        <option value="DP" @if($filters['type'] === 'DP') selected @endif>DP (MRS)</option>
                        <option value="SR" @if($filters['type'] === 'SR') selected @endif>SR (Stock)</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 mg-b-10">
                    <label for="department">Department</label>
                    <select name="department" id="department" class="form-control">
                        <option value="">All</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->name }}" @if(\App\Models\Department::normalizeName($filters['department']) === \App\Models\Department::normalizeName($department->name)) selected @endif>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 mg-b-10">
                    <label for="purchaser">Purchaser</label>
                    <select name="purchaser" id="purchaser" class="form-control">
                        <option value="">All</option>
                        @foreach($purchasers as $purchaser)
                            <option value="{{ $purchaser->id }}" @if($filters['purchaser'] === (int) $purchaser->id) selected @endif>{{ $purchaser->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-1 mg-b-10">
                    <label for="aging">Aging</label>
                    <select name="aging" id="aging" class="form-control">
                        <option value="">All</option>
                        @foreach($buckets as $bucket)
                            <option value="{{ $bucket }}" @if($filters['aging'] === $bucket) selected @endif>{{ $bucket }} days</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 mg-b-10">
                    <label for="startdate">Posted from</label>
                    <input type="date" name="startdate" id="startdate" class="form-control" value="{{ $filters['startdate'] }}">
                </div>
                <div class="col-6 col-md-2 mg-b-10">
                    <label for="enddate">Posted to</label>
                    <input type="date" name="enddate" id="enddate" class="form-control" value="{{ $filters['enddate'] }}">
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap">
                <button type="submit" class="btn btn-sm btn-primary px-4 mg-r-5">Search</button>
                <a href="{{ route('reports.unprocessed_pa') }}" class="btn btn-sm btn-secondary px-4 mg-r-15">Reset</a>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="include_zero" name="include_zero" value="1" @if($filters['include_zero']) checked @endif>
                    <label class="custom-control-label tx-12" for="include_zero" style="text-transform:none;font-weight:400;color:inherit;">Include zero balance (fully ordered)</label>
                </div>
            </div>
        </form>

        {{-- Rows --}}
        <div class="table-responsive">
            <table class="table mg-b-0 table-light table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>MRS Request #</th>
                        <th>PA #</th>
                        <th>Posted Date</th>
                        <th>Department</th>
                        <th>Purchaser</th>
                        <th>Purchasing Received Date</th>
                        <th>Aging</th>
                        <th class="text-right">Total Balance</th>
                        <th class="text-center">View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td><span class="upa-type {{ $row->pa_type }}">{{ $row->pa_type }}</span></td>
                            <td><strong>{{ $row->order_number ?: 'N/A' }}</strong></td>
                            <td>
                                <strong>{{ $row->pa_number ?: 'N/A' }}</strong>
                                @if((int) $row->is_hold === 1) <span class="upa-hold" title="Put on hold by Purchasing">HOLD</span> @endif
                            </td>
                            <td>{{ $row->posted_at ? \Carbon\Carbon::parse($row->posted_at)->format('m/d/Y') : 'N/A' }}</td>
                            <td>{{ $row->department ?: ($row->pa_type === 'SR' ? 'Stock Replenishment' : 'N/A') }}</td>
                            <td>{{ $purchaserNames->get($row->received_by, 'N/A') }}</td>
                            <td>{{ $row->received_at ? \Carbon\Carbon::parse($row->received_at)->format('m/d/Y') : 'N/A' }}</td>
                            <td>
                                @if($row->aging_days === null)
                                    N/A
                                @else
                                    <span style="color: {{ (int) $row->aging_days >= 14 ? 'red' : 'blue' }};">
                                        {{ (int) $row->aging_days }} {{ (int) $row->aging_days === 1 ? 'day' : 'days' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-right">{{ rtrim(rtrim(number_format((float) $row->balance, 2), '0'), '.') }}</td>
                            <td class="text-center">
                                @if($row->pa_id)
                                    <a href="{{ route('pa.pa_view', $row->pa_id) }}" title="View Purchase Advice"><i data-feather="eye"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center"><p class="text-danger mg-b-0">No unprocessed PAs for these filters.</p></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="row mg-t-10">
            <div class="col-md-6">
                @if($rows->total() > 0)
                    <p class="tx-gray-400 tx-12 d-inline">Showing {{ $rows->firstItem() }} to {{ $rows->lastItem() }} of {{ $rows->total() }} PAs</p>
                @endif
            </div>
            <div class="col-md-6">
                <div class="text-md-right float-md-right">{{ $rows->links() }}</div>
            </div>
        </div>
    </div>
@endsection
