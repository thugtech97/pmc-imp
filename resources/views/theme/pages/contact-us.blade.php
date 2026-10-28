@extends('theme.main')

@section('pagecss')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row align-items-lg-center col-mb-30 bg-orange">
        <div class="col-lg-6 px-lg-5 py-5">
            <div class="">
                <form id="contactUsForm" class="mb-0" action="{{ route('contact-us') }}" method="post">
                    @method('POST')
                    @csrf
                    <div class="row contact-form">
                        <!-- Heading Title -->
                        <div class="text-center">
                            <img src="{{ asset('theme/images/misc/get-in-touch.jpg') }}" />
                            <p class="mb-2">We'd love to hear from you! Please Fill out the form below for any inquiries, questions, of comments you may have.</p>
                        </div>
                        
                        @if(session()->has('success'))
                            <div class="style-msg successmsg">
                                <div class="sb-msg"><i class="icon-thumbs-up"></i><strong>Success!</strong> {{ session()->get('success') }}</div>
                            </div>
                        @endif
                        
                        @if(session()->has('error'))
                            <div class="style-msg errormsg">
                                <div class="sb-msg"><i class="icon-remove"></i><strong>Error!</strong> {{ session()->get('error') }}</div>
                            </div>
                        @endif


                        <div class="col-md-6 form-group">
                            <input type="text" name="name" value="" class="sm-form-control required" placeholder="Name" />
                        </div>

                        <div class="col-md-6 form-group">
                            <input type="email" name="email" value="" class="required email sm-form-control" placeholder="Email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"/>
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <input type="text" name="contact" id="contact" value="" class="sm-form-control required" placeholder="Landline or Mobile" />
                        </div>

                        <div class="w-100"></div>

                        <div class="col-12 form-group">
                            <input type="text" name="subject" value="" class="required sm-form-control" placeholder="Subject" />
                        </div>

                        <div class="w-100"></div>

                        <div class="col-12 form-group">
                            <textarea class="required sm-form-control" name="message" rows="3" cols="30" placeholder="Type your message here..."></textarea>
                        </div>

                        <div class="col-12 form-group d-flex align-content-center justify-content-center">
                            <script src="https://www.google.com/recaptcha/api.js?hl=en" async="" defer="" ></script>
                            <div class="g-recaptcha" data-sitekey="{{ \Setting::info()->google_recaptcha_sitekey }}"></div><br>
                        </div>

                        <div class="col-12 form-group d-flex align-content-center justify-content-center">
                            <label class="control-label text-danger" for="g-recaptcha-response" id="catpchaError" style="display:none;font-size: 14px;"><i class="fa fa-times-circle-o"></i>The Captcha field is required.</label></br>
                            @if($errors->has('g-recaptcha-response'))
                                @foreach($errors->get('g-recaptcha-response') as $message)
                                    <label class="control-label text-danger" for="g-recaptcha-response"><i class="fa fa-times-circle-o"></i>{{ $message }}</label></br>
                                @endforeach
                            @endif
                        </div>


                        

                        <div class="col-12 form-group d-flex align-content-center justify-content-center">
                            <button type="submit" class="button button-border m-0 button-white border-width-1 border-default h-bg-color bg-white rounded loren-shop-btn-white">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Image -->
        <div class="col-lg-6 px-lg-0 min-vh-50 min-vh-lg-100" style="background: url('{{ \Setting::info()->contact_us_image }}') no-repeat center center; background-size: cover;">
        </div>
    </div>
</div>
        
<div class="">
    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7740.649559195261!2d121.200284!3d14.057989!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x87b5e5f116099c7!2sLorenzana%20Food%20Corporation%20(Lorins)!5e0!3m2!1sen!2sph!4v1643884634922!5m2!1sen!2sph" width="100%" height="35" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
</div>
@endsection

@section('pagejs')
<script>

    /** form validations **/
    $(document).ready(function () {
        //called when key is pressed in textbox
        $("#contact").keypress(function (e) {
            //if the letter is not digit then display error and don't type anything
            var charCode = (e.which) ? e.which : event.keyCode
            if (charCode != 43 && charCode > 31 && (charCode < 48 || charCode > 57))
                return false;
            return true;

        });
    });

    $('#contactUsForm').submit(function (evt) {
        let recaptcha = $("#g-recaptcha-response").val();
        if (recaptcha === "") {
            evt.preventDefault();
            $('#catpchaError').show();
            return false;
        }
    });
</script>
@endsection
