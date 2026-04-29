<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trusted Server / Web IPs
    |--------------------------------------------------------------------------
    | Requests coming from these IPs are allowed through without a signature.
    | Typically your web server, load balancer, or internal backend services.

    */

    ///for remembering sake this works for only dedicated server and not for shared hosting as the IP address of the server is shared among multiple users and can change frequently. In such cases, you may need to use other methods of authentication, such as API keys or OAuth tokens, to secure your API endpoints.
    'allowed_ips' => [
        '105.112.125.246', // server
        // '89.117.60.171',  //server
        // '64.29.17.1',
        // '216.198.79.1',
        // '105.112.116.25', // mylocal development machine
        // '102.89.46.233',
        // '127.0.0.1',
        // '::1',               // localhost IPv6
        // '10.0.0.1',          // internal load balancer
        // '192.168.1.100',     // internal web server
        // '41.58.120.45',      // production server
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile App Signature
    |--------------------------------------------------------------------------
    | HMAC-SHA256 secret shared with your mobile app clients.
    | Requests from mobile must include X-Api-Signature and X-Api-Timestamp.
    | Generate with: openssl rand -hex 32
    */
    'mobile_secret' => 'a3f8c2e1d4b7a9f0e6c3d2b1a8f7e4c5d0b9a6f3e2c1d8b5a4f1e0c7d6b3a2f9',

    /*
    |--------------------------------------------------------------------------
    | Replay Attack Window (seconds)
    |--------------------------------------------------------------------------
    | Requests with a timestamp older than this value will be rejected.
    | Default: 300 seconds (5 minutes).
    */
    'replay_ttl' => 300,
];
