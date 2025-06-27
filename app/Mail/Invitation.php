<?php

namespace App\Mail;

use App\Models\SystemMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Invitation extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $encrypt;
    public $otp;
    public $admail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $encrypt, $otp)
    {
        $this->name = $name;
        $this->email = $email;
        $this->encrypt = $encrypt;
        $this->otp = $otp;
        $this->admail = SystemMail::where('label', 'invitation_account')->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.invitation');
    }
}
