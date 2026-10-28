@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="{{ asset('theme/success-style.css') }}">
@endsection

@section('content')
<div class="container">    
    @if (Session::has('success'))
    <div class="alert alert-info" role="alert">
        {{ Session::get('success') }}
    </div>
    @endif

    <section class="py-5 py-lg-6 position-relative">
        <div class="container">
            <div class="border py-4 px-3 border-transparent shadow-lg p-lg-5">
                <h3 class="border-bottom pb-3 text-center" style="color: #444;
                    font-size: 1.5rem;
                    font-family: DM Sans,sans-serif;
                    font-weight: 600;
                    line-height: 1.5;
                    margin: 0 0 30px;
                ">Your request has been submitted</h3>

                <p class="text-center mb-0"></p>
                <p class="text-center fs-34-f color fw-bold"></p>

                <div class="mx-wd-600-f m-auto mb-5">
                    <p class="text-center">You may inform your supervisor for the approval.</p>
                </div>

                <div class="d-flex flex-column flex-lg-row justify-content-center">
                    <a href="{{ route('profile.sales') }}" class="button button-dark button-border button-circle button-xlarge fw-bold mt-2 fs-14-f nols notextshadow text-center">View MRS Requests</a>
                    <a href="{{ route('catalogue.home') }}" class="button button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow text-center">Create Another MRS</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('pagejs')
@endsection