<?php

namespace App\Mail;

use App\Models\SystemMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $form;
    public $user;
    public $admail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($form, $user)
    {
        $this->form = $form;
        $this->user = $user;
        $this->admail = SystemMail::where('label', 'event')->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.eventRegistrationMail');
    }
}
