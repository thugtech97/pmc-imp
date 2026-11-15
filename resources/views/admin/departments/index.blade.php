@extends('admin.layouts.app')

@section('pagetitle')
    Departments
@endsection

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('lib/sweetalert2/sweetalert.min.css') }}" type="text/css">
    <style>
        .dept-approver { line-height: 1.35; }
        .dept-approver .alt { color: #8392a5; font-size: 12px; }
        .dept-approver .missing { color: #dc3545; font-weight: 600; }
        .dept-approver .gone { color: #dc3545; }
        .dept-filter .btn.active { pointer-events: none; }
    </style>
@endsection

@section('content')
    <div class="container pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5">
                        <li class="breadcrumb-item" aria-current="page">Admin</li>
                        <li class="breadcrumb-item active" aria-current="page">Departments</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1 d-flex align-items-center flex-wrap">
                    Manage Departments
                    @include('admin.departments._wfs-badge')
                </h4>
                <p class="tx-13 tx-color-03 mg-b-0 mg-t-5">
                    WFS approvers for each department. <b>1st approver</b> approves MRS and IMF; <b>2nd approver</b> is the second step for MRS.
                    The alternate can approve in place of the main approver.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if (!$wfsAvailable)
            <div class="alert alert-warning" role="alert">
                Could not connect to WFS, so approver names can't be shown right now (their WFS user ids are shown instead).
            </div>
        @endif
        @if ($missingCount > 0 && $filter !== 'missing')
            <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert">
                <span><b>{{ $missingCount }}</b> department(s) with users have no 1st approver — their MRS/IMF can't be submitted to WFS until one is set.</span>
                <a href="{{ route('departments.index', ['filter' => 'missing']) }}" class="btn btn-sm btn-warning mg-l-10">Show them</a>
            </div>
        @endif

        <div class="d-md-flex align-items-center mg-b-10">
            <div class="btn-group btn-group-sm dept-filter mg-b-10 mg-md-b-0" role="group">
                <a href="{{ route('departments.index', array_filter(['search' => $search])) }}" class="btn btn-outline-secondary {{ $filter === '' ? 'active' : '' }}">All</a>
                <a href="{{ route('departments.index', array_filter(['search' => $search, 'filter' => 'in_use'])) }}" class="btn btn-outline-secondary {{ $filter === 'in_use' ? 'active' : '' }}">With users</a>
                <a href="{{ route('departments.index', array_filter(['search' => $search, 'filter' => 'missing'])) }}" class="btn btn-outline-secondary {{ $filter === 'missing' ? 'active' : '' }}">Missing 1st approver ({{ $missingCount }})</a>
            </div>

            <form class="form-inline mg-md-l-auto mg-b-10 mg-md-b-0" method="GET" action="{{ route('departments.index') }}">
                @if ($filter !== '')<input type="hidden" name="filter" value="{{ $filter }}">@endif
                <div class="search-form mg-r-10">
                    <input name="search" type="search" class="form-control" placeholder="Search by name" value="{{ $search }}">
                    <button class="btn filter" type="submit"><i data-feather="search"></i></button>
                </div>
            </form>

            <form method="POST" action="{{ route('departments.import-wfs') }}" class="d-inline mg-r-5 js-confirm-import">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm" {{ $wfsAvailable ? '' : 'disabled' }}>Import current approvers from WFS</button>
            </form>
            <a class="btn btn-primary btn-sm" href="{{ route('departments.create') }}">Create a Department</a>
        </div>

        <div class="table-list mg-b-10">
            <div class="table-responsive-lg">
                <table class="table mg-b-0 table-light table-hover" style="width:100%;">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 26%;">Department</th>
                            <th scope="col" style="width: 8%;">Users</th>
                            <th scope="col" style="width: 26%;">1st Approver <span class="tx-color-03 tx-normal">(MRS &amp; IMF)</span></th>
                            <th scope="col" style="width: 26%;">2nd Approver <span class="tx-color-03 tx-normal">(MRS)</span></th>
                            <th scope="col" style="width: 14%;" class="text-right">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $department)
                            @php
                                $show = function ($id) use ($wfsAvailable) {
                                    if (!$id) return null;
                                    $label = \App\Services\WfsDirectory::label($id);
                                    if ($label) return e($label);
                                    return $wfsAvailable
                                        ? '<span class="gone">WFS user #' . (int) $id . ' (not active in WFS)</span>'
                                        : 'WFS user #' . (int) $id;
                                };
                            @endphp
                            <tr>
                                <th><strong>{{ $department->name }}</strong></th>
                                <td>{{ number_format($department->users_count) }}</td>
                                <td class="dept-approver">
                                    @if ($department->wfs_approver_id)
                                        {!! $show($department->wfs_approver_id) !!}
                                        <div class="alt">Alternate: {!! $show($department->wfs_alternate_approver_id) ?: '—' !!}</div>
                                    @else
                                        <span class="{{ $department->users_count ? 'missing' : 'tx-color-03' }}">Not set</span>
                                    @endif
                                </td>
                                <td class="dept-approver">
                                    @if ($department->wfs_approver2_id)
                                        {!! $show($department->wfs_approver2_id) !!}
                                        <div class="alt">Alternate: {!! $show($department->wfs_alternate_approver2_id) ?: '—' !!}</div>
                                    @else
                                        <span class="tx-color-03">Not set</span>
                                    @endif
                                </td>
                                <td>
                                    <nav class="nav table-options justify-content-end flex-nowrap">
                                        <a class="nav-link" href="{{ route('departments.edit', $department->id) }}" title="Edit department"><i data-feather="edit"></i></a>
                                        <form method="POST" action="{{ route('departments.destroy', $department->id) }}" class="d-inline js-confirm-delete"
                                              data-name="{{ $department->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="nav-link btn btn-link pd-0 {{ $department->users_count ? 'disabled' : '' }}"
                                                    title="{{ $department->users_count ? 'Has users — move them first' : 'Delete department' }}"
                                                    {{ $department->users_count ? 'disabled' : '' }}><i data-feather="trash-2"></i></button>
                                        </form>
                                    </nav>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center"><p class="text-danger mg-b-0">No departments found.</p></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-md-flex align-items-center justify-content-between">
            <p class="tx-gray-400 tx-12 mg-b-0">
                @if ($departments->total())
                    Showing {{ $departments->firstItem() }} to {{ $departments->lastItem() }} of {{ $departments->total() }} departments
                @else
                    Showing 0 departments
                @endif
            </p>
            <div>{{ $departments->links() }}</div>
        </div>
    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/sweetalert2/sweetalert2@11.js') }}"></script>
@endsection

@section('customjs')
    <script>
        // SweetAlert confirms for "Import current approvers from WFS" and Delete.
        $(document).on('submit', 'form.js-confirm-import', function (e) {
            var form = this;
            if (form.dataset.confirmed) return;
            e.preventDefault();
            Swal.fire({
                icon: 'question',
                title: 'Import approvers from WFS?',
                html: 'Every <b>empty</b> approver field will be filled from WFS users, matched by department name (the old way).<br><br>'
                    + 'Approvers already set are <b>not</b> changed.',
                showCancelButton: true,
                confirmButtonText: 'Yes, import',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0168fa',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (!result.isConfirmed) return;
                form.dataset.confirmed = '1';
                Swal.fire({
                    title: 'Importing…',
                    text: 'Reading approvers from WFS. Please wait.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () { Swal.showLoading(); }
                });
                form.submit();
            });
        });

        $(document).on('submit', 'form.js-confirm-delete', function (e) {
            var form = this;
            if (form.dataset.confirmed) return;
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Delete department?',
                text: '"' + $(form).data('name') + '" will be permanently deleted.',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                if (!result.isConfirmed) return;
                form.dataset.confirmed = '1';
                form.submit();
            });
        });
    </script>
@endsection
