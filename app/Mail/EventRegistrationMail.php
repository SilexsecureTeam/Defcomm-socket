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
        // $this->qrCode = base64_encode(QrCode::format('png')
        //     ->size(200)
        //     ->margin(1)
        //     ->generate($qrData, public_path('qr/test.png')));

        $path = public_path('qr');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // File name
        $fileName = time() . '_qr.png';

        // Generate QR and save it as png
        QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($qrData, $path . '/' . $fileName);

        // Get full path
        $fullPath = $path . '/' . $fileName;

        // Convert the saved PNG to Base64
        $qrCode = base64_encode(file_get_contents($fullPath));

        return $this->view('emails.eventRegistrationMail')->with([
            "qrCode" => $qrCode,
            "fileName" => $fileName
        ]);
    }
}
