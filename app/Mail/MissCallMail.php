<?php

namespace App\Mail;

use App\Models\SystemMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MissCallMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sender;
    public $reciever;
    public $admail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sender, $reciever)
    {
        $this->sender = $sender;
        $this->reciever = $reciever;
        $this->admail = SystemMail::where('label', 'miss_call')->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.misscallmail');
    }
}
