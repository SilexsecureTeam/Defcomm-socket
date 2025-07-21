<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WailkieTalkieChannelInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $channel;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $channel)
    {
        $this->name = $name;
        $this->email = $email;
        $this->channel = $channel;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.wailkieTalkieChannelInvitation');
    }
}
