<?php

namespace App\Mail;

use App\Models\SystemMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $form;
    public $user;
    public $meet;
    public $qrCode;
    public $admail;

    public function __construct($form, $user, $meet)
    {
        $this->form = $form;
        $this->user = $user;
        $this->meet = $meet;
        $this->admail = SystemMail::where('label', 'event')->first();
    }

    public function build()
    {
        $qrData = url("/admin/form/attendance/" . encrypt($this->form->id) . "/" . encrypt($this->user->id));

        // Generate the QR code as a Base64 encoded string
        $png = QrCode::format('png')
            ->size(120)
            ->margin(1)
            ->generate($qrData);

        $this->qrCode = base64_encode($png);
        return $this->view('emails.eventRegistrationMail');
    }
}
