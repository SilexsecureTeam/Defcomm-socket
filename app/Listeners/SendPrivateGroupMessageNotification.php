<?php

namespace App\Listeners;

use App\Events\PrivateGroupMessageSent;
use App\Http\Services\FirebaseService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendPrivateGroupMessageNotification
{
    private FirebaseService $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Handle the event.
     */
    public function handle(PrivateGroupMessageSent $event): void
    {
        // Skip if FCM is disabled - avoid unnecessary database queries
        if (!$this->firebaseService->isEnabled()) {
            Log::debug('FCM is disabled, skipping private group message notification');
            return;
        }

        try {
            // Decrypt group ID and sender ID
            $groupId = decryptHelper($event->groupId);
            $senderId = decryptHelper($event->senderId);

            // Fetch group members (you'll need to adjust this based on your group structure)
            // This assumes there's a group_members table or similar
            $groupMembers = DB::table('company_group_users')
                ->where('group_id', $groupId)
                ->where('user_id', '!=', $senderId)
                ->pluck('user_id')
                ->toArray();

            if (empty($groupMembers)) {
                Log::debug('No group members found for group message', ['group_id' => $groupId]);
                return;
            }

            // Fetch sender for display name
            $sender = User::find($senderId);
            $senderName = $sender?->name ?? 'Unknown User';

            // Extract message content
            $messageContent = $this->extractMessageContent($event->message);

            // Get FCM tokens for group members
            $memberTokens = User::whereIn('id', $groupMembers)
                ->whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray();

            if (empty($memberTokens)) {
                Log::debug('No device tokens found for group members', ['group_id' => $groupId]);
                return;
            }

            // Send FCM notification to all group members
            $successCount = $this->firebaseService->sendToMultipleTokens(
                $memberTokens,
                $event->groupName ?? 'Group Message',
                $senderName . ': ' . $messageContent,
                [
                    'group_id' => (string)$groupId,
                    'sender_id' => (string)$senderId,
                    'message_type' => 'private_group_message',
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            Log::info('Private group message FCM notifications sent', [
                'group_id' => $groupId,
                'sender_id' => $senderId,
                'success_count' => $successCount,
                'total_tokens' => count($memberTokens),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending private group message FCM notification', [
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
