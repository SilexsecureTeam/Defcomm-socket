<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\testWebsocket;
use App\Events\TestPrivateWesocketEvent;

class TestController extends Controller
{
    public function test()
    {
        event(new testWebsocket);
        broadcast(new TestPrivateWesocketEvent(5));
    }
}
