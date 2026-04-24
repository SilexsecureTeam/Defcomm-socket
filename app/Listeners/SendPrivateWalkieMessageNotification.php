<?php

namespace App\Listeners;

use App\Events\PrivateWalkieMessageSent;
use App\Http\Services\FirebaseService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendPrivateWalkieMessageNotification
{
    private FirebaseService $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Handle the event.
     */
    public function handle(PrivateWalkieMessageSent $event): void
    {
        // Skip if FCM is disabled - avoid unnecessary database queries
        if (!$this->firebaseService->isEnabled()) {
            Log::debug('FCM is disabled, skipping walkie message notification');
            return;
        }

        try {
            // Decrypt walkie channel ID and sender ID
            $walkieId = decryptHelper($event->walkieId);
            $senderId = decryptHelper($event->senderId);

            // Fetch walkie channel subscribers (excluding sender)
            // Adjust this based on your walkie-talkie table structure
            $subscribers = DB::table('wailkie_talkie_subscribers')
                ->where('channel_id', $walkieId)
                ->where('user_id', '!=', $senderId)
                ->pluck('user_id')
                ->toArray();

            if (empty($subscribers)) {
                Log::debug('No subscribers found for walkie channel', ['walkie_id' => $walkieId]);
                return;
            }

            // Fetch sender for display name
            $sender = User::find($senderId);
            $senderName = $sender?->name ?? 'Unknown User';

            // Extract message content
            $messageContent = $this->extractMessageContent($event->message);

            // Get FCM tokens for subscribers
            $subscriberTokens = User::whereIn('id', $subscribers)
                ->whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray();

            if (empty($subscriberTokens)) {
                Log::debug('No device tokens found for walkie subscribers', ['walkie_id' => $walkieId]);
                return;
            }

            // Send FCM notification to all subscribers
            $successCount = $this->firebaseService->sendToMultipleTokens(
                $subscriberTokens,
                $event->walkieName ?? 'Walkie Talkie',
                $senderName . ': ' . $messageContent,
                [
                    'walkie_id' => (string)$walkieId,
                    'sender_id' => (string)$senderId,
                    'message_type' => 'walkie_message',
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            Log::info('Walkie message FCM notifications sent', [
                'walkie_id' => $walkieId,
                'sender_id' => $senderId,
                'success_count' => $successCount,
                'total_tokens' => count($subscriberTokens),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending walkie message FCM notification', [
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
