@extends('admin.layouts.app')

@section('pagetitle')
    User Management
@endsection

@section('pagecss')
    <link href="{{ asset('lib/bselect/dist/css/bootstrap-select.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('users.index')}}">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit a User</li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">Edit a User</li>
        </div>
    </div>

    <div class="row row-sm">
        <div class="col-lg-6">
            <form action="{{ route('users.update',$user->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="d-block">First Name *</label>
                    <input type="text" name="fname" id="fname" value="{{ old('fname', $user->firstname)}}" class="form-control @error('fname') is-invalid @enderror" required>
                    @error('fname')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="d-block">Middle Name *</label>
                    <input type="text" name="mname" id="mname" value="{{ old('mname', $user->middlename)}}" class="form-control @error('mname') is-invalid @enderror">
                    @error('mname')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="d-block">Last Name *</label>
                    <input type="text" name="lname" id="lname" value="{{ old('lname', $user->lastname)}}" class="form-control @error('lname') is-invalid @enderror" required>
                    @error('lname')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="d-block">Email *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email)}}" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="d-block">Role *</label>
                    <select name="role" class="selectpicker mg-b-5 @error('role') is-invalid @enderror" data-style="btn btn-outline-light btn-md btn-block tx-left" title="Select role" data-width="100%" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ (old("role", $user->role_id) == $role->id ? "selected":"") }}>{{ $role->name }}</option>
                        @endforeach
                        <option value="6" {{ (old("role", $user->role_id) == 6 ? "selected":"") }}>Department User</option>
                    </select>
                    @error('role')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <button class="btn btn-primary btn-sm btn-uppercase" type="submit">Update User</button>
                <a class="btn btn-outline-secondary btn-sm btn-uppercase" href="{{ route('users.index') }}">Cancel</a>
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
        $(function() {
            $('.selectpicker').selectpicker();
        });
    </script>
@endsection
