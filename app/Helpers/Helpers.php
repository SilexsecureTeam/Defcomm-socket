<?php

use Vinkla\Hashids\Facades\Hashids;


function encryptHelper($data)
{
    return Hashids::encode($data);
}

function decryptHelper($data)
{
    return Hashids::decode($data);
}