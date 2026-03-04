<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FirebaseService
{
    private string $serverKey;
    private string $projectId;
    private string $fcmV1Url = 'https://fcm.googleapis.com/v1/projects';
    private ?string $accessToken = null;
    private ?int $tokenExpiresAt = null;

    public function __construct()
    {
        $this->serverKey = config('firebase.server_key') ?? env('FIREBASE_SERVER_KEY');
        $this->projectId = config('firebase.project_id') ?? env('FIREBASE_PROJECT_ID');
        // $this->serverKey = "";
        // $this->projectId = "";
    }

    /**
     * Check if FCM is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('firebase.enabled', true);
    }

    public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        // Check if FCM is enabled
        if (!$this->isEnabled()) {
            Log::debug('FCM is disabled, skipping notification', ['token' => substr($fcmToken, 0, 20)]);
            return false;
        }

        try {
            if (empty($fcmToken)) {
                Log::warning('FCM Token is empty, skipping notification send');
                return false;
            }

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                Log::error('FCM: Failed to obtain access token');
                return false;
            }

            $url = "{$this->fcmV1Url}/{$this->projectId}/messages:send";

            // Map data values to strings for FCM v1 requirements
            $formattedData = [];
            foreach ($data as $key => $value) {
                $formattedData[(string)$key] = (string)$value;
            }

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $formattedData,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'category' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info('FCM v1 notification sent successfully', ['token' => substr($fcmToken, 0, 20)]);
                return true;
            }

            Log::error('FCM v1 notification failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('FCM notification error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Send notification to multiple devices by FCM tokens
     *
     * @param array $fcmTokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return int
     */
    public function sendToMultipleTokens(array $fcmTokens, string $title, string $body, array $data = []): int
    {
        // Check if FCM is enabled - early return before processing tokens
        if (!$this->isEnabled()) {
            Log::debug('FCM is disabled, skipping notifications for multiple tokens', [
                'token_count' => count($fcmTokens),
            ]);
            return 0;
        }

        $successCount = 0;

        foreach ($fcmTokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        // Check if FCM is enabled
        if (!$this->isEnabled()) {
            Log::debug('FCM is disabled, skipping topic notification', ['topic' => $topic]);
            return false;
        }

        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return false;
            }

            $url = "{$this->fcmV1Url}/{$this->projectId}/messages:send";

            // Map data values to strings
            $formattedData = [];
            foreach ($data as $key => $value) {
                $formattedData[(string)$key] = (string)$value;
            }

            $payload = [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $formattedData,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info('FCM v1 topic notification sent successfully', ['topic' => $topic]);
                return true;
            }

            Log::error('FCM v1 topic notification failed', [
                'topic' => $topic,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('FCM topic notification error', [
                'topic' => $topic,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send a formatted message notification
     *
     * @param string|array $target FCM token(s) or topic
     * @param string $messageType Type of message (message, group_message, walkie_message, etc.)
     * @param array $messageData
     * @return bool|int
     */
    public function sendFormattedMessage($target, string $messageType, array $messageData): bool|int
    {
        $title = $this->getMessageTitle($messageType, $messageData);
        $body = $this->getMessageBody($messageType, $messageData);
        $data = $this->formatMessageData($messageType, $messageData);

        if (is_array($target)) {
            return $this->sendToMultipleTokens($target, $title, $body, $data);
        }

        if (str_starts_with($target, '/topics/')) {
            return $this->sendToTopic(str_replace('/topics/', '', $target), $title, $body, $data);
        }

        return $this->sendToToken($target, $title, $body, $data);
    }

    /**
     * Get message title based on message type
     *
     * @param string $messageType
     * @param array $messageData
     * @return string
     */
    private function getMessageTitle(string $messageType, array $messageData): string
    {
        return match ($messageType) {
            'private_message' => $messageData['sender_name'] ?? 'New Message',
            'group_message' => $messageData['group_name'] ?? 'Group Message',
            'walkie_message' => $messageData['walkie_name'] ?? 'Walkie Talkie',
            'private_group_message' => $messageData['group_name'] ?? 'Private Group',
            'public_message' => 'Public Chat',
            default => 'New Notification',
        };
    }

    /**
     * Get message body based on message type
     *
     * @param string $messageType
     * @param array $messageData
     * @return string
     */
    private function getMessageBody(string $messageType, array $messageData): string
    {
        return match ($messageType) {
            'private_message', 'group_message', 'walkie_message', 'private_group_message', 'public_message' =>
            $messageData['sender_name'] . ': ' . substr($messageData['message'] ?? 'New message', 0, 100),
            default => 'You have a new notification',
        };
    }

    /**
     * Format message data for FCM payload
     *
     * @param string $messageType
     * @param array $messageData
     * @return array
     */
    private function formatMessageData(string $messageType, array $messageData): array
    {
        $baseData = [
            'message_type' => $messageType,
            'timestamp' => now()->toIso8601String(),
        ];

        return match ($messageType) {
            'private_message' => array_merge($baseData, [
                'sender_id' => $messageData['sender_id'] ?? '',
                'receiver_id' => $messageData['receiver_id'] ?? '',
                'message' => $messageData['message'] ?? '',
                'sender_name' => $messageData['sender_name'] ?? '',
            ]),
            'group_message' => array_merge($baseData, [
                'group_id' => $messageData['group_id'] ?? '',
                'sender_id' => $messageData['sender_id'] ?? '',
                'message' => $messageData['message'] ?? '',
                'sender_name' => $messageData['sender_name'] ?? '',
                'group_name' => $messageData['group_name'] ?? '',
            ]),
            'walkie_message' => array_merge($baseData, [
                'walkie_id' => $messageData['walkie_id'] ?? '',
                'sender_id' => $messageData['sender_id'] ?? '',
                'message' => $messageData['message'] ?? '',
                'sender_name' => $messageData['sender_name'] ?? '',
            ]),
            'private_group_message' => array_merge($baseData, [
                'group_id' => $messageData['group_id'] ?? '',
                'sender_id' => $messageData['sender_id'] ?? '',
                'message' => $messageData['message'] ?? '',
                'sender_name' => $messageData['sender_name'] ?? '',
                'group_name' => $messageData['group_name'] ?? '',
            ]),
            'public_message' => array_merge($baseData, [
                'sender_id' => $messageData['sender_id'] ?? '',
                'message' => $messageData['message'] ?? '',
                'sender_name' => $messageData['sender_name'] ?? '',
            ]),
            default => $baseData,
        };
    }

    /**
     * Send notification to user by user object
     *
     * @param object $user
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendToUser(object $user, string $title, string $body, array $data = []): bool
    {
        if (empty($user->device_token)) {
            return false;
        }

        return $this->sendToToken($user->device_token, $title, $body, $data);
    }

    /**
     * Subscribe device to a topic
     *
     * @param string $fcmToken
     * @param string $topic
     * @return bool
     */
    public function subscribeToTopic(string $fcmToken, string $topic): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post("https://iid.googleapis.com/iid/v1/" . $fcmToken . "/rel/topics/" . $topic);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('FCM topic subscription error', [
                'exception' => $e->getMessage(),
                'topic' => $topic,
            ]);

            return false;
        }
    }

    /**
     * Unsubscribe device from a topic
     *
     * @param string $fcmToken
     * @param string $topic
     * @return bool
     */
    public function unsubscribeFromTopic(string $fcmToken, string $topic): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->delete("https://iid.googleapis.com/iid/v1/" . $fcmToken . "/rel/topics/" . $topic);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('FCM topic unsubscription error', [
                'exception' => $e->getMessage(),
                'topic' => $topic,
            ]);

            return false;
        }
    }

    /**
     * Get OAuth2 access token for FCM v1
     *
     * @return string|null
     */
    private function getAccessToken(): ?string
    {
        // Check cache
        if ($this->accessToken && $this->tokenExpiresAt > time()) {
            return $this->accessToken;
        }

        try {
            $path = config('firebase.credentials_json_path') ?? env('FIREBASE_CREDENTIALS_JSON_PATH');

            if (!$path || !file_exists($path)) {
                Log::error('FCM: Credentials JSON file not found at ' . ($path ?? 'unconfigured path'));
                return null;
            }

            $scopes = ['https://www.googleapis.com/auth/cloud-platform'];
            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $path);
            $token = $credentials->fetchAuthToken();

            if (isset($token['access_token'])) {
                $this->accessToken = $token['access_token'];
                $this->tokenExpiresAt = time() + ($token['expires_in'] ?? 3600) - 60; // 1 min buffer
                return $this->accessToken;
            }

            Log::error('FCM: Failed to fetch auth token', ['response' => $token]);
            return null;
        } catch (Exception $e) {
            Log::error('FCM: Access token exception', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
