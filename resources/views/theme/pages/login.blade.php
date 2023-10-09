@extends('theme.main')

@section('content')
<div class="container position-relative d-flex align-items-stretch justify-content-center overflow-visible">
    <div class="position-relative rounded bg-white shadow-lg w-100 px-4 py-4 z-4" style="max-width: 440px;">
        @if($message = Session::get('error'))
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i data-feather="alert-circle" class="mg-r-10"></i> {{ $message }}
            </div>
        @endif

        @if($message = Session::get('success'))
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i data-feather="alert-circle" class="mg-r-10"></i> {{ $message }}
            </div>
        @endif
        <i id="loginForm" class="d-block mx-auto i-circled background-custom1 i-alt i-xlarge icon-wpforms h2 mb-4" style="font-size: 36px;cursor:initial"></i>
        <h3 class="text-center">Log In to your Account</h3>
        <form action="{{ route('customer-front.customer_login') }}" method="post">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="form-group mb-4">
                        <label for="email-address" class="fw-semibold text-initial nols">Email Address</label>
                        <input type="email" id="email-address" class="form-control form-input" name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group mb-4">
                        <label for="password" class="fw-semibold text-initial nols">Password</label>
                        <input type="password" id="password" class="form-control form-input mb-3" name="password" required>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('customer-front.forgot_password') }}">Forgot password?</a>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="g-recaptcha recaptcha mt-2" id="g-recaptcha1"></div>
                </div>

                <div class="col-12">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button type="submit" class="button button-circle button-xlarge fw-bold mt-2 fs-14-f nols text-dark h-text-light notextshadow text-center w-100">Login</button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="button button-black button-circle button-xlarge fw-bold mt-2 fs-14-f nols notextshadow text-center w-100">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('pagejs')
    <script>
        /** form validations **/
        $(document).ready(function () {
            //called when key is pressed in textbox
            $('#mobile').keypress(function (e) {
                //if the letter is not digit then display error and don't type anything
                var charCode = (e.which) ? e.which : event.keyCode
                if (charCode != 43 && charCode > 31 && (charCode < 48 || charCode > 57))
                    return false;
                return true;

            });
        });

        $(".show_hide_password a").on('click', function(event) {
            event.preventDefault();
            if($(this).parent().parent().siblings('input').attr("type") == "text"){
                $(this).parent().parent().siblings('input').attr('type', 'password');
                $(this).children('i').addClass( "icon-eye-slash" );
                $(this).children('i').removeClass( "icon-eye" );
            }else if($(this).parent().parent().siblings('input').attr("type") == "password"){
                $(this).parent().parent().siblings('input').attr('type', 'text');
                $(this).children('i').removeClass( "icon-eye-slash" );
                $(this).children('i').addClass( "icon-eye" );
            }
        });
    </script>
@endsection

