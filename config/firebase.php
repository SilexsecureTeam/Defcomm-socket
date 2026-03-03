<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Firebase Cloud Messaging settings for sending push notifications
    |
    */

    'server_key' => env('FIREBASE_SERVER_KEY'),

    'project_id' => env('FIREBASE_PROJECT_ID'),

    'api_key' => env('FIREBASE_API_KEY'),

    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),

    'database_url' => env('FIREBASE_DATABASE_URL'),

    'credentials_json_path' => env('FIREBASE_CREDENTIALS_JSON_PATH'),

    /*
    |--------------------------------------------------------------------------
    | FCM Settings
    |--------------------------------------------------------------------------
    */

    'fcm' => [
        'url' => 'https://fcm.googleapis.com/fcm/send',
        'v1_url' => 'https://fcm.googleapis.com/v1/projects',
        'timeout' => 10,
        'priority' => 'high',
        'retry_count' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'enabled' => env('FCM_NOTIFICATIONS_ENABLED', true),
        'sound' => 'default',
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        'max_retries' => 3,
    ],
];
