<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $meet;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $meet)
    {
        $this->name = $name;
        $this->email = $email;
        $this->meet = $meet;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.meetingInvitation');
    }
}
