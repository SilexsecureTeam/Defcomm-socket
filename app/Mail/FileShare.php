<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileShare extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $company;
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
