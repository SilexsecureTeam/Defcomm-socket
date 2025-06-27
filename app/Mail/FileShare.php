<?php

namespace App\Mail;

use App\Models\SystemMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FileShare extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $company;
    public $admail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $company)
    {
        $this->name = $name;
        $this->email = $email;
        $this->company = $company;
        $this->admail = SystemMail::where('label', 'fileshare')->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.fileShare');
    }
}
