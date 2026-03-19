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
        if (!$this->firebaseService->isEnabled()) {
            Log::debug('FCM is disabled, skipping private message notification');
            return;
        }

        try {
            $receiverId = decryptHelper($event->receiverId);
            $senderId   = decryptHelper($event->senderId);

            if ($receiverId === $senderId) {
                return;
            }

            $receiver = User::find($receiverId);
            if (!$receiver || empty($receiver->fcm_token)) {
                return;
            }

            $sender     = User::find($senderId);
            $senderName = $sender?->name ?? 'Unknown User';

            // Use encrypted IDs so Flutter can navigate to the correct chat.
            $senderIdEn   = encryptHelper((string)$senderId);
            $receiverIdEn = encryptHelper((string)$receiverId);

            // Determine message state from the broadcast payload.
            $messageArray = is_array($event->message) ? $event->message : [];
            $state        = $messageArray['state'] ?? 'text';

            if ($state === 'call') {
                // ── Incoming call ──────────────────────────────────────────────
                // Extract meeting ID from "__call_control__invite|{meetingId}"
                $rawMessage = $messageArray['message'] ?? '';
                $meetingId  = '';
                if (str_contains($rawMessage, '|')) {
                    $meetingId = trim(explode('|', $rawMessage, 2)[1]);
                }

                // Data-only (no notification block) so only CallKit ring UI shows.
                $this->firebaseService->sendDataOnlyToToken(
                    $receiver->fcm_token,
                    [
                        'message_type' => 'call',
                        'caller_name'  => $senderName,
                        'caller_id'    => $senderIdEn,
                        'meeting_id'   => $meetingId,
                        'timestamp'    => now()->toIso8601String(),
                    ]
                );

                Log::info('Call FCM notification sent', [
                    'receiver_id' => $receiverId,
                    'meeting_id'  => $meetingId,
                ]);
            } else {
                // ── Regular chat message ───────────────────────────────────────
                $messageContent = $this->extractMessageContent($event->message);

                $this->firebaseService->sendToUser(
                    $receiver,
                    $senderName,
                    $messageContent,
                    [
                        'sender_id'    => $senderIdEn,
                        'receiver_id'  => $receiverIdEn,
                        'message_type' => 'private_message',
                        'timestamp'    => now()->toIso8601String(),
                    ]
                );

                Log::info('Private message FCM notification sent', [
                    'receiver_id' => $receiverId,
                    'sender_id'   => $senderId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending private message FCM notification', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
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
