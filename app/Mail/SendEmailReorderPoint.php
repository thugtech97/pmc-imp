<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailReorderPoint extends Mailable
{
    use Queueable, SerializesModels;

    public $setting;
    public $admin;

    public function __construct($setting, $admin)
    {
        $this->setting = $setting;
        $this->admin = $admin;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         return $this->view('mail.reorder-point')
            ->subject('Your items are about to run out of stocks');
    }
}
