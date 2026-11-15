@extends('admin.layouts.app')

@php $editing = $department->exists; @endphp

@section('pagetitle')
    {{ $editing ? 'Edit Department' : 'Create a Department' }}
@endsection

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
    <style>
        .approver-card { border: 1px solid #e5e9f2; border-radius: 6px; padding: 16px 18px 4px; margin-bottom: 18px; }
        .approver-card h6 { font-weight: 700; margin-bottom: 2px; }
        .approver-card .hint { color: #8392a5; font-size: 12px; margin-bottom: 14px; }
        .bootstrap-select .dropdown-menu li small { display: block; padding-left: 0; }
    </style>
@endsection

@section('content')
<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                    <li class="breadcrumb-item" aria-current="page">Admin</li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('departments.index') }}">Departments</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1 d-flex align-items-center flex-wrap">
                {{ $editing ? 'Edit Department' : 'Create a Department' }}
                @include('admin.departments._wfs-badge')
            </h4>
        </div>
    </div>

    @if (!$wfsAvailable)
        <div class="alert alert-warning">Could not connect to WFS, so the approver lists are empty. You can still save the department name; set the approvers once WFS is reachable.</div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <form action="{{ $editing ? route('departments.update', $department->id) : route('departments.store') }}" method="post">
                @csrf
                @if ($editing) @method('PUT') @endif

                <div class="form-group">
                    <label class="d-block">Department Name *</label>
                    <input type="text" name="name" value="{{ old('name', $department->name) }}" maxlength="150"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                    @if ($editing)
                        <small class="tx-color-03">{{ number_format($department->users()->count()) }} user(s) belong to this department.</small>
                    @endif
                </div>

                @php
                    $sections = [
                        ['title' => '1st Approver', 'hint' => 'Approves MRS and IMF requests from this department (WFS step 1).',
                         'main' => 'wfs_approver_id', 'alternate' => 'wfs_alternate_approver_id'],
                        ['title' => '2nd Approver — MRS only', 'hint' => 'Second approval step for MRS requests (WFS step 2). IMF requests stop at the 1st approver.',
                         'main' => 'wfs_approver2_id', 'alternate' => 'wfs_alternate_approver2_id'],
                    ];
                @endphp

                @foreach ($sections as $section)
                    <div class="approver-card">
                        <h6>{{ $section['title'] }}</h6>
                        <div class="hint">{{ $section['hint'] }}</div>
                        <div class="row">
                            @foreach (['main' => 'Main approver', 'alternate' => 'Alternate approver'] as $kind => $label)
                                @php
                                    $field = $section[$kind];
                                    $current = old($field, $department->{$field});
                                @endphp
                                <div class="col-md-6 form-group">
                                    <label class="d-block">{{ $label }}</label>
                                    <select name="{{ $field }}" class="selectpicker @error($field) is-invalid @enderror"
                                            data-live-search="true" data-size="10" data-width="100%"
                                            data-style="btn btn-outline-light btn-md btn-block tx-left"
                                            title="— None —">
                                        <option value="">— None —</option>
                                        @foreach ($wfsUsers as $user)
                                            <option value="{{ $user['id'] }}" {{ (string) $current === (string) $user['id'] ? 'selected' : '' }}
                                                    data-subtext="{{ $user['designation'] }}"
                                                    data-tokens="{{ $user['department'] }} {{ $user['division'] }}">{{ $user['name'] }}</option>
                                        @endforeach
                                        @if ($current && !isset($wfsUsers[(int) $current]))
                                            <option value="{{ $current }}" selected>WFS user #{{ $current }} (not active in WFS)</option>
                                        @endif
                                    </select>
                                    @error($field)<span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <button class="btn btn-primary btn-sm btn-uppercase" type="submit">{{ $editing ? 'Save Changes' : 'Create Department' }}</button>
                <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary btn-sm btn-uppercase">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
@endsection

@section('customjs')
    <script>
        $(function () {
            $('.selectpicker').selectpicker();
        });
    </script>
@endsection
