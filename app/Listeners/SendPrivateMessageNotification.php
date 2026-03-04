<?php

namespace App\Listeners;

use App\Events\PrivateMessageSent;
use App\Http\Services\FirebaseService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendPrivateMessageNotification
{
    private FirebaseService $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Handle the event.
     */
    public function handle(PrivateMessageSent $event): void
    {
        // Skip if FCM is disabled - avoid unnecessary database queries
        if (!$this->firebaseService->isEnabled()) {
            Log::debug('FCM is disabled, skipping private message notification');
            return;
        }

        try {
            // Decrypt receiver ID to get the actual user ID
            $receiverId = decryptHelper($event->receiverId);
            $senderId = decryptHelper($event->senderId);

            // Skip if receiver is the same as sender (self-message)
            if ($receiverId === $senderId) {
                return;
            }

            // Fetch receiver user to get FCM token
            $receiver = User::find($receiverId);
            if (!$receiver || empty($receiver->fcm_token)) {
                Log::debug('Receiver not found or no device token for private message', [
                    'receiver_id' => $receiverId,
                    'sender_id' => $senderId,
                ]);
                return;
            }

            // Fetch sender for display name
            $sender = User::find($senderId);
            $senderName = $sender?->name ?? 'Unknown User';

            // Extract message content
            $messageContent = $this->extractMessageContent($event->message);

            // Send FCM notification
            $this->firebaseService->sendToUser(
                $receiver,
                $senderName,
                $messageContent,
                [
                    'sender_id' => (string)$senderId,
                    'receiver_id' => (string)$receiverId,
                    'message_type' => 'private_message',
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            Log::info('Private message FCM notification sent', [
                'receiver_id' => $receiverId,
                'sender_id' => $senderId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending private message FCM notification', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Extract message content from the event message data
     */
    private function extractMessageContent($messageData): string
    {
        if (is_array($messageData)) {
            // Try to extract message from common structures
            if (isset($messageData['message'])) {
                return substr($messageData['message'], 0, 150);
            }
            if (isset($messageData['mss_chat']['message'])) {
                return substr($messageData['mss_chat']['message'], 0, 150);
            }
        }

        return 'New message';
    }
}
