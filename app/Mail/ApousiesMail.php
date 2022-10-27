<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApousiesMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = \App\Models\Setting::getValueOf('schoolName') . '. Ενημέρωση για απουσίες της ημέρας';
        return $this->subject($subject)->markdown('emails.apousies')->with([
            'data' => $this->data,
        ]);
    }
}
