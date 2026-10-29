@extends('theme.main')

@section('pagecss')
<style>
    .pmc-login-page {
        --login-ink: #222e2b;
        --login-deep: #0b1211;
        --login-green: #42615b;
        --login-gold: #ecd49a;
        --login-line: rgba(236, 212, 154, .2);
        background: #111a18;
        box-sizing: border-box;
        font-family: "DM Sans", sans-serif;
        overflow: hidden;
        padding: 56px 0 72px;
        min-height: 100vh;
        min-height: 100dvh;
        position: relative;
    }
    .pmc-login-page *,
    .pmc-login-page *::before,
    .pmc-login-page *::after {
        box-sizing: border-box;
    }
    .pmc-login-page::before {
        background-image:
            linear-gradient(135deg, rgba(11, 18, 17, .78), rgba(34, 46, 43, .58)),
            url('{{ asset('images/login-bg.jpg') }}');
        background-position: center;
        background-size: cover;
        content: '';
        filter: blur(7px);
        inset: 0;
        opacity: .9;
        pointer-events: none;
        position: absolute;
        transform: scale(1.06);
    }
    .pmc-login-page:hover::before {
        animation: pmc-login-bg-drift 10s ease-in-out infinite alternate;
    }
    @keyframes pmc-login-bg-drift {
        0% {
            transform: scale(1.06) translate3d(0, 0, 0) rotate(0deg);
        }
        100% {
            transform: scale(1.1) translate3d(-10px, -6px, 0) rotate(.35deg);
        }
    }
    .pmc-login-shell {
        background: #fff;
        /* border: 1px solid rgba(34, 46, 43, .1); */
        border-radius: 20px;
        box-shadow: 0 22px 60px rgba(34, 46, 43, .14);
        display: grid;
        grid-template-columns: minmax(250px, .82fr) minmax(360px, 1.18fr);
        margin: 0 auto;
        max-width: 940px;
        overflow: hidden;
        position: relative;
        z-index: 1;
    }
    .pmc-login-intro {
        background-color: var(--login-ink);
        background-image:
            linear-gradient(145deg, rgba(66, 97, 91, .9), rgba(11, 18, 17, .98)),
            repeating-linear-gradient(125deg, transparent 0 32px, rgba(236, 212, 154, .05) 33px, transparent 34px 66px);
        color: #fff;
        min-height: 560px;
        padding: 48px 40px;
        position: relative;
    }
    .pmc-login-intro::after {
        animation: pmc-login-orbit 16s ease-in-out infinite;
        border: 1px solid var(--login-line);
        border-radius: 50%;
        content: '';
        height: 210px;
        opacity: .65;
        position: absolute;
        right: -94px;
        top: -74px;
        width: 210px;
    }
    @keyframes pmc-login-orbit {
        0%, 100% {
            transform: translate3d(0, 0, 0) rotate(0deg);
        }
        50% {
            transform: translate3d(-18px, 24px, 0) rotate(12deg);
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .pmc-login-page:hover::before,
        .pmc-login-intro::after {
            animation: none;
        }
    }
    .pmc-login-logo {
        background: rgba(255, 255, 255, .96);
        border-radius: 12px;
        height: 54px;
        object-fit: contain;
        padding: 7px 10px;
        width: 80px;
    }
    .pmc-login-kicker {
        color: var(--login-gold);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.7px;
        margin: 70px 0 16px;
        text-transform: uppercase;
    }
    .pmc-login-intro h1 {
        color: #fff;
        font-size: clamp(28px, 3vw, 40px);
        font-weight: 700;
        letter-spacing: 0;
        line-height: 1.1;
        margin: 0 0 20px;
    }
    .pmc-login-intro p {
        color: rgba(255, 255, 255, .7);
        font-size: 14px;
        line-height: 1.75;
        margin: 0;
        max-width: 270px;
    }
    .pmc-login-mark {
        border: 1px solid var(--login-line);
        border-radius: 50%;
        bottom: 42px;
        color: var(--login-gold);
        display: grid;
        font-size: 25px;
        height: 58px;
        place-items: center;
        position: absolute;
        width: 58px;
    }
    .pmc-login-form {
        padding: 54px 58px 48px;
    }
    .pmc-login-form-header {
        margin-bottom: 34px;
    }
    .pmc-login-form-header h2 {
        color: var(--login-ink);
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px;
    }
    .pmc-login-form-header p {
        color: #75817d;
        font-size: 13px;
        margin: 0;
    }
    .pmc-login-alert {
        border: 0;
        border-radius: 10px;
        font-size: 13px;
        margin-bottom: 20px;
    }
    .pmc-login-form .form-group {
        margin-bottom: 22px;
    }
    .pmc-login-form label {
        color: var(--login-ink);
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .2px;
        margin-bottom: 8px;
    }
    .pmc-login-form .form-input {
        background: #f8faf8;
        border: 1px solid #dfe5e1;
        border-radius: 9px;
        box-shadow: none;
        color: var(--login-ink);
        font-size: 14px;
        height: 48px;
        padding: 0 15px;
        transition: background-color .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .pmc-login-form .form-input:focus {
        background: #fff;
        border-color: var(--login-green);
        box-shadow: 0 0 0 3px rgba(66, 97, 91, .12);
    }
    .pmc-login-forgot {
        color: var(--login-green);
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }
    .pmc-login-forgot:hover {
        color: #9d7400;
    }
    .pmc-login-form .recaptcha {
        margin: 4px 0 22px;
        min-height: 78px;
        max-width: 100%;
        overflow: hidden;
    }
    .pmc-login-actions {
        display: grid;
        gap: 10px;
        grid-template-columns: 1.35fr .65fr;
    }
    .pmc-login-actions .button {
        border-radius: 9px;
        font-size: 13px;
        height: 48px;
        margin: 0 !important;
        padding: 0 16px;
        transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
    }
    .pmc-login-actions .button:hover {
        transform: translateY(-2px);
    }
    .pmc-login-submit {
        background: var(--login-green) !important;
        color: #fff !important;
    }
    .pmc-login-submit:hover {
        background: var(--login-ink) !important;
    }
    .pmc-login-cancel {
        background: transparent !important;
        border: 1px solid #d7dfdb !important;
        color: var(--login-ink) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    .pmc-login-cancel:hover {
        background: #f1f5f2 !important;
        border-color: var(--login-green) !important;
    }
    @media (max-width: 767.98px) {
        .pmc-login-page {
            padding: 32px 12px 48px;
        }
        .pmc-login-shell {
            display: block;
        }
        .pmc-login-intro {
            min-height: 0;
            padding: 30px 28px 34px;
        }
        .pmc-login-kicker {
            margin-top: 38px;
        }
        .pmc-login-intro p {
            max-width: 360px;
        }
        .pmc-login-mark {
            bottom: 30px;
            right: 28px;
        }
        .pmc-login-form {
            padding: 34px 28px 32px;
        }
    }
    @media (max-width: 420px) {
        .pmc-login-form .recaptcha {
            transform: scale(.86);
            transform-origin: left top;
            width: 304px;
        }
        .pmc-login-actions {
            grid-template-columns: 1fr;
        }
    }

    /* force on file styling */
    section#content.py-lg-6 {
        padding-bottom: 0rem !important;
        padding-top: 0rem !important;
    }
</style>
@endsection

@section('content')
<section class="pmc-login-page">
    <div class="container">
        <div class="pmc-login-shell">
            <div class="pmc-login-intro">
                <img class="pmc-login-logo" src="{{ asset('images/logo.svg') }}" alt="PMC">
                <div class="pmc-login-kicker">Materials Control Department</div>
                <h1>Welcome back.</h1>
                <p>Sign in to continue to the IMF-MRS-PA system and manage your department requests.</p>
                <div class="pmc-login-mark" aria-hidden="true"><i class="icon-line-lock"></i></div>
            </div>

            <div class="pmc-login-form">
                @if($message = Session::get('error'))
                    <div class="alert alert-danger d-flex align-items-center pmc-login-alert" role="alert">
                        <i data-feather="alert-circle" class="mg-r-10"></i> {{ $message }}
                    </div>
                @endif

                @if($message = Session::get('success'))
                    <div class="alert alert-success d-flex align-items-center pmc-login-alert" role="alert">
                        <i data-feather="alert-circle" class="mg-r-10"></i> {{ $message }}
                    </div>
                @endif

                <div class="pmc-login-form-header">
                    <i id="loginForm" class="icon-line-head" aria-hidden="true"></i>
                    <h2>Log in to your account</h2>
                    <p>Use your registered email and password to continue.</p>
                </div>

                <form action="{{ route('customer-front.customer_login') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="email-address">Email Address</label>
                        <input type="email" id="email-address" class="form-control form-input" name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" class="form-control form-input" name="password" required>
                        <div class="d-flex justify-content-end mt-2">
                            <a class="pmc-login-forgot" href="{{ route('customer-front.forgot_password') }}">Forgot password?</a>
                        </div>
                    </div>

                    <div class="g-recaptcha recaptcha" id="g-recaptcha1"></div>

                    <div class="pmc-login-actions">
                        <button type="submit" class="button fw-bold nols notextshadow text-center w-100 pmc-login-submit">Login</button>
                        <a href="/" class="button fw-bold nols notextshadow text-center w-100 pmc-login-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
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

