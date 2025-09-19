<?php

use App\Models\LanguageCode;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Services\FileEncryptorService;
use App\Http\Services\GoogleAiTransService;


function encryptHelperOld($data)
{ 
    return Hashids::encode($data);
}

function decryptHelperOld($data)
{
    try {
        if (empty($data)) {
            return $data;
        }

        $decoded = Hashids::decode($data);

        // If decode worked, return first value
        if (!empty($decoded)) {
            return $decoded[0];
        }

        // If decode failed, just return original
        return $data;
    } catch (\Exception $e) {
        return $data;
    }
}

function encryptHelper(?string $value): ?string
{
    if (empty($value)) {
        return null;
    }

    $encrypted = openssl_encrypt(
        $value,
        'AES-256-ECB',
        config('services.sys.key'),
        OPENSSL_RAW_DATA
    );

    // Base64 URL-safe encode without padding
    return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
}

function decryptHelper(?string $encrypted): ?string
{
    if (empty($encrypted)) {
        return null;
    }

    // Restore padding if removed
    $encrypted = strtr($encrypted, '-_', '+/');
    $encrypted = str_pad($encrypted, strlen($encrypted) % 4 === 0 ? strlen($encrypted) : strlen($encrypted) + 4 - strlen($encrypted) % 4, '=', STR_PAD_RIGHT);

    return openssl_decrypt(
        base64_decode($encrypted),
        'AES-256-ECB',
        config('services.sys.key'),
        OPENSSL_RAW_DATA
    );
}



function forceToArray($value)
{
    // Case 1: JSON string like '["13","12","11"]'
    if (is_string($value)) {
        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return array_values($decoded); // flatten JSON array
        }

        // Case 2: Comma-separated string like "13,12,11"
        if (strpos($value, ',') !== false) {
            return array_map('trim', explode(',', $value));
        }

        // Case 3: Single string
        return [$value];
    }

    // Case 4: Already array
    if (is_array($value)) {
        return array_values($value); // flatten numeric keys
    }

    // Case 5: Number or other type
    return $value;
}

function convertBackToenHelper($data)
{
    // Try to decode JSON into array
    $array = json_decode($data, true);

    // If decoding failed and it's not an array, wrap it into an array
    if (!is_array($array)) {
        return $data;
    }

    // Encrypt each item in the array
    return array_map(function ($item) {
        return encryptHelper($item); // call the global helper
    }, $array);
}

function googleAiTransHelper($text, $source, $target)
{
    try {
        if($source == $target){
            return $text;
        }

        if ($target == null) {
            $target_lang = LanguageCode::first()->code;
        }else{
            $target_lang = LanguageCode::find($target)->code;
        }

        if($source == null){
            $source_lang = LanguageCode::first()->code;
        }else{
            $source_lang = LanguageCode::find($source)->code;
        }

        $res = (new GoogleAiTransService)->translateText($text, $target_lang, $source_lang);
        return $res;
    } catch (\Exception $e) {
        return $text;
    }
}

function googleAiTransSTHelper($audioPath, $source)
{
    try {
        if($source == null){
            $source_lang = LanguageCode::first()->code;
        }else{
            $source_lang = LanguageCode::find($source)->code;
        }

        $res = (new GoogleAiTransService)->speechToText($audioPath, $source_lang);
        return $res;
    } catch (\Exception $e) {
        return $audioPath;
    }
}

function googleAiTransTSHelper($text, $target)
{

    try {
        if($target == null){
            $target_lang = LanguageCode::first()->code;
        }else{
            $target_lang = LanguageCode::find($target)->code;
        }

        $res = (new GoogleAiTransService)->textToSpeech($text, $target_lang);
        return $res;

    } catch (\Exception $e) {
        return $text;
    }

}

function googleAiTransSTENHelper($encryptedPath, $decryptedPath, $source)
{
    try {
        $decryptor = new FileEncryptorService();
        $decryptor->decryptAudio($encryptedPath, $decryptedPath);
        $res = googleAiTransSTHelper($decryptedPath, $source);
        // unlink($decryptedPath);
        return $res;
    } catch (\Exception $e) {
        return null;
    }
}
