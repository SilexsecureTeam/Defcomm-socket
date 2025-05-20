<?php

namespace App\Http\Controllers;

use Okolaa\TermiiPHP\Data\Message;
use Okolaa\TermiiPHP\Termii;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Http;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    public function formatPhoneNumber($number)
    {
        // Remove spaces, dashes, or any non-digit characters
        $number = preg_replace('/\D/', '', $number);

        // Check if it starts with '234'
        if (strpos($number, '234') === 0) {
            return $number;
        }

        // If it starts with '0', remove it and add '234'
        if (strpos($number, '0') === 0) {
            $number = substr($number, 1);
        }

        return '234' . $number;
    }

    public function AfricasTalkingSms($phonenumber, $bodymss)
    {
        $username = 'arus'; 
        $apiKey = 'atsk_f7c35095f25ae72156fca901f0988c5d7cd24c0371fa29a2bce09fc6c6906649330aa116';
        // $username = env('AFRICATALK_USERNAME') ?? 'arus';
        // $apiKey = env('AFRICATALK_API_KEY') ?? '45f88bac5a65ac1df51b62df7a916bffd2c23b1354d00e0b68dd432918dcf1cf';
        // use your sandbox app API key for development in the test environment
        //$AT       = new AfricasTalking($username, $apiKey);

        // Get one of the services
        //$sms      = $AT->sms();
        $number = $phonenumber;
        // Use the service
        //$result   = $sms->send($bodymss);
        //
    }

    public function TermiiSms($phone, $bodysms)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'accept' => '*/*',
        ];

        $body = [
            "to" => $this->formatPhoneNumber($phone),
            "from" => "N-Alert",
            "sms" => $bodysms,
            "type" => "plain",
            "channel" => "generic",
            "api_key" => "TLaxzvqyzzIHFakjqpUBJSWWvRmMhUhdhDReAzEzbXzUZzeHslNwBNHiqGnTpg",
            "media" => [
                "url" => "https://media.example.com/file",
                "caption" => "your media file"
            ]
        ];

        Http::withHeaders($headers)->post('https://v3.api.termii.com/api/sms/send', $body);
    }
}
