<?php

namespace App\Mail;

use App\Models\SystemMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CallMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $altname;
    public $admail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $altname)
    {
        $this->name = $name;
        $this->altname = $altname;
        $this->admail = SystemMail::where('label', 'call')->first();
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
