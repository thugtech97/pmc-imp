<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class DeliveryCompletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $salesHeader;

    public function __construct($salesHeader)
    {
        $this->salesHeader = $salesHeader;
    }

    public function build()
    {
        return $this->subject(
                'Delivery Completed - ' . ($this->salesHeader->order_number ?? 'N/A')
            )
            ->view('emails.delivery_completed')
            ->with([
                'salesHeader' => $this->salesHeader
            ]);
    }
}
