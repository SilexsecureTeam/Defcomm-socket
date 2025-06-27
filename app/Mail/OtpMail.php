<?php

namespace App\Mail;

use App\Models\SystemMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $otp;
    public $admail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $otp)
    {
        $this->name = $name;
        $this->otp = $otp;
        $this->admail = SystemMail::where('label', 'otp')->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.otpmail');
    }
}
