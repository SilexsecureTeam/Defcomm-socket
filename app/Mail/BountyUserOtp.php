<?php

namespace App\Mail;

use App\Models\SystemMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BountyUserOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $otp;
    public $admail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $otp)
    {
        $this->data = $data;
        $this->otp = $otp;
        $this->admail = SystemMail::where('label', 'bounty')->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.bountyUserOtp');
    }
}
