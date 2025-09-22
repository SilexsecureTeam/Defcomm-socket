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

function base62_encode(string $data): string
{
    $hex = bin2hex($data);
    $num = gmp_init($hex, 16);

    $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $base = strlen($alphabet);
    $encoded = '';

    while (gmp_cmp($num, 0) > 0) {
        [$num, $rem] = gmp_div_qr($num, $base);
        $encoded .= $alphabet[gmp_intval($rem)];
    }

    return strrev($encoded);
}

function base62_decode(string $data): string
{
    $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $base = strlen($alphabet);
    $num = gmp_init(0, 10);

    for ($i = 0; $i < strlen($data); $i++) {
        $pos = strpos($alphabet, $data[$i]);
        $num = gmp_add(gmp_mul($num, $base), $pos);
    }

    $hex = gmp_strval($num, 16);
    if (strlen($hex) % 2 !== 0) {
        $hex = '0' . $hex;
    }

    return hex2bin($hex);
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

    return base62_encode($encrypted);
}

function decryptHelper(?string $encrypted): ?string
{
    if (empty($encrypted)) {
        return null;
    }

    $binary = base62_decode($encrypted);

    return openssl_decrypt(
        $binary,
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
        if ($source == $target) {
            return $text;
        }

        if ($target == null) {
            $target_lang = LanguageCode::first()->code;
        } else {
            $target_lang = LanguageCode::find($target)->code;
        }

        if ($source == null) {
            $source_lang = LanguageCode::first()->code;
        } else {
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
        if ($source == null) {
            $source_lang = LanguageCode::first()->code;
        } else {
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
        if ($target == null) {
            $target_lang = LanguageCode::first()->code;
        } else {
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
