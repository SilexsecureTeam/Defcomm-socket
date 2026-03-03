<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FirebaseService
{
    private string $serverKey;
    private string $projectId;
    private string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    private string $fcmV1Url = 'https://fcm.googleapis.com/v1/projects';

    public function __construct()
    {
        // $this->serverKey = config('firebase.server_key') ?? env('FIREBASE_SERVER_KEY');
        // $this->projectId = config('firebase.project_id') ?? env('FIREBASE_PROJECT_ID');
        $this->serverKey = "";
        $this->projectId = "";
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

    /**
     * Send notification to a single device by FCM token
     *
     * @param string $fcmToken
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        // Check if FCM is enabled
        if (!config('firebase.enabled', true)) {
            Log::debug('FCM is disabled, skipping notification', ['token' => substr($fcmToken, 0, 20)]);
            return false;
        }

        try {
            if (empty($fcmToken)) {
                Log::warning('FCM Token is empty, skipping notification send');
                return false;
            }

            $payload = [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]),
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            if ($response->successful()) {
                Log::info('FCM notification sent successfully', ['token' => substr($fcmToken, 0, 20)]);
                return true;
            }

            Log::error('FCM notification failed', [
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

    /**
     * Send notification to a topic
     *
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        // Check if FCM is enabled
        if (!config('firebase.enabled', true)) {
            Log::debug('FCM is disabled, skipping topic notification', ['topic' => $topic]);
            return false;
        }

        try {
            $payload = [
                'to' => '/topics/' . $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'topic' => $topic,
                ]),
                'priority' => 'high',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            if ($response->successful()) {
                Log::info('FCM topic notification sent successfully', ['topic' => $topic]);
                return true;
            }

            Log::error('FCM topic notification failed', [
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
}
