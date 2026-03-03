<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Http\Services\FirebaseService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendPublicMessageNotification
{
    private FirebaseService $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        // Skip if FCM is disabled - avoid unnecessary database queries
        if (!$this->firebaseService->isEnabled()) {
            Log::debug('FCM is disabled, skipping public message notification');
            return;
        }

        try {
            // For public messages, send to all active users with device tokens
            // You may want to adjust this based on your application's requirements

            $senderId = $event->senderId;

            // Fetch sender for display name
            $sender = null;
            if ($senderId) {
                $sender = User::find($senderId);
            }
            $senderName = $sender?->name ?? 'Admin';

            // Extract message content
            $messageContent = $this->extractMessageContent($event->message);

            // Get FCM tokens for all active users (excluding sender if applicable)
            $query = User::whereNotNull('device_token')->where('status', 'active');

            if ($senderId) {
                $query->where('id', '!=', $senderId);
            }

            $userTokens = $query->pluck('device_token')->toArray();

            if (empty($userTokens)) {
                Log::debug('No device tokens found for public message');
                return;
            }

            // Send FCM notification to all users (limit batch size for performance)
            $batchSize = 500;
            $batches = array_chunk($userTokens, $batchSize);
            $totalSuccess = 0;

            foreach ($batches as $batch) {
                $successCount = $this->firebaseService->sendToMultipleTokens(
                    $batch,
                    'Public Announcement',
                    $senderName . ': ' . $messageContent,
                    [
                        'sender_id' => $senderId ? (string)$senderId : 'system',
                        'message_type' => 'public_message',
                        'timestamp' => now()->toIso8601String(),
                    ]
                );
                $totalSuccess += $successCount;
            }

            Log::info('Public message FCM notifications sent', [
                'sender_id' => $senderId,
                'success_count' => $totalSuccess,
                'total_tokens' => count($userTokens),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending public message FCM notification', [
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
        }

        return 'New announcement';
    }
}
