<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MrsDeleteRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $selectedMrs;
    public string $emailBody;
    public string $senderName;

    /**
     * Create a new message instance.
     *
     * @param array  $selectedMrs  Array of MRS order numbers selected for deletion
     * @param string $emailBody    Custom body text entered by the user
     * @param string $senderName  Name of the user sending the request
     */
    public function __construct(array $selectedMrs, string $emailBody, string $senderName)
    {
        $this->selectedMrs  = $selectedMrs;
        $this->emailBody    = $emailBody;
        $this->senderName   = $senderName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('MRS Deletion Request – ' . implode(', ', $this->selectedMrs))
            ->view('emails.mrs_delete_request');
    }
}