<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CallMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $altname;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $altname)
    {
        $this->name = $name;
        $this->altname = $altname;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.callmail');
    }
}
