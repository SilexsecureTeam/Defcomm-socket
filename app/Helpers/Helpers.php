<?php

use Vinkla\Hashids\Facades\Hashids;


function encryptHelper($data)
{
    return Hashids::encode($data);
}

function decryptHelper($data)
{
    try{
        // If no data, return it directly
        if (empty($data)) {
            return $data;
        }

        $decoded = Hashids::decode($data);

        // If result is a non-empty array, return the first element
        if (is_array($decoded) && !empty($decoded)) {
            return $decoded[0];
        }

        // If result is a non-empty array, return the first element
        if (!empty($decoded)) {
            return $decoded[0];
        }

        // Otherwise return the original data
        return $data;
    }
    catch (\Exception $e) {
        // If there's an error, return the original data
        return $data;
    }
}
