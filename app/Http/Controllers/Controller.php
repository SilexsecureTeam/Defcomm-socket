<?php

namespace App\Http\Controllers;

use Okolaa\TermiiPHP\Data\Message;
use Okolaa\TermiiPHP\Termii;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function AfricasTalkingSms($phonenumber, $bodymss)
    {
        $username = 'arus'; 
        $apiKey = 'atsk_f7c35095f25ae72156fca901f0988c5d7cd24c0371fa29a2bce09fc6c6906649330aa116';
        // $username = env('AFRICATALK_USERNAME') ?? 'arus';
        // $apiKey = env('AFRICATALK_API_KEY') ?? '45f88bac5a65ac1df51b62df7a916bffd2c23b1354d00e0b68dd432918dcf1cf';
        // use your sandbox app API key for development in the test environment
        $AT       = new AfricasTalking($username, $apiKey);

        // Get one of the services
        $sms      = $AT->sms();
        $number = $phonenumber;
        // Use the service
        $result   = $sms->send($bodymss);
        //
    }

    public function TermiiSms($phonenumber, $bodymss) {
        $termii = Termii::initialize('tsk_W854dRapXlKBydVFkKzcRYd0bS');
        $message = new Message(
            to: $phonenumber,
            from: "N-Alert",
            sms: $bodymss,
            type: "plain",
            channel: \Okolaa\TermiiPHP\Enums\MessageChannel::DND,
            // media: null,
            // time_to_live: 0
        );
        $response = $termii->messagingApi()->send($message);
    }
}
