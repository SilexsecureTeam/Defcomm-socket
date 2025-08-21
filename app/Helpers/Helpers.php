<?php

use Vinkla\Hashids\Facades\Hashids;


function encryptHelper($data)
{ 
    return Hashids::encode($data);
}

function decryptHelper($data)
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
