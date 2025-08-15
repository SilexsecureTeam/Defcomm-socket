<?php

namespace App\Http\Services;

use Jenssegers\Agent\Agent;
use App\Models\UserLoginLog;
use App\Models\UserLoginDevice;
use Stevebauman\Location\Facades\Location;

class AuthService
{
    public function loginLog($user, $request)
    {
        // Get IP
        $ip = $request->ip();
        // Get Device Info
        $agent = new Agent();
        $browser = $agent->browser();
        $device = $agent->device();
        $os = $agent->platform();
        $userAgent = $request->header('User-Agent');

        // Get Location Info
        $locationData = Location::get($ip);

        $deviveLogin = UserLoginDevice::where(
            [
                'user_id'    => $user->id,
                'browser'    => $browser,
                'device'     => $device ?: 'Unknown',
                'os'         => $os,
            ])->first();

        $deviveLogin = UserLoginDevice::updateOrCreate([
            'user_id'    => $user->id,
            'browser'    => $browser,
            'device'     => $device ?: 'Unknown',
            'os'         => $os,
        ],[
            'ip_address' => $ip,
            'country'    => $locationData->countryName ?? null,
            'region'     => $locationData->regionName ?? null,
            'city'       => $locationData->cityName ?? null,
            'lat'        => $locationData->latitude ?? null,
            'lon'        => $locationData->longitude ?? null,
            'user_agent' => $userAgent,
            'last_used_at' => now(),
        ]);

        $deve = $deviveLogin ? $deviveLogin->status : 'active';
        
        UserLoginLog::create([
            'user_id'    => $user->id,
            'ip_address' => $ip,
            'browser'    => $browser,
            'device'     => $device ?: 'Unknown',
            'os'         => $os,
            'country'    => $locationData->countryName ?? null,
            'region'     => $locationData->regionName ?? null,
            'city'       => $locationData->cityName ?? null,
            'lat'        => $locationData->latitude ?? null,
            'lon'        => $locationData->longitude ?? null,
            'user_agent' => $userAgent,
            'device_id'  => $deviveLogin->id,
            'attempt' => $deve ?? 'active',
        ]);

        return $deve ?? 'active';
    }
}