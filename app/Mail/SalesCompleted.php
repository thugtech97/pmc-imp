<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SalesCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public $h;

    public function __construct($sales)
    {
        $this->h = $sales;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         return $this->view('mail.order-details')
            ->subject('Order # '.$this->h->order_numer.' has been placed.');
    }
}
