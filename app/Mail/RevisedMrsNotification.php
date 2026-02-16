<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RevisedMrsNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $sales;

    public function __construct($sales)
    {
        $this->sales = $sales;
    }

    public function build()
    {
        return $this->subject('PMC-IMP: Revised MRS Created (' . ($this->sales->order_number ?? 'N/A') . ')')
                    ->view('emails.revised_mrs')
                    ->with([
                        'sales' => $this->sales,
                    ]);
    }
}
