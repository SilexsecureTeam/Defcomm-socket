<?php

namespace App\Http\Services;

/**
 * Firebase Cloud Messaging Helper Service
 * 
 * This is a simplified wrapper for common FCM operations.
 * For direct control, use FirebaseService directly.
 * 
 * @see FirebaseService
 */
class FirebaseNotificationHelper
{
    private FirebaseService $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Send a private message notification
     */
    public function sendPrivateMessageNotification($receiverUser, $senderUser, $messageContent, $messageId = null): bool
    {
        if (!$receiverUser || empty($receiverUser->fcm_token)) {
            return false;
        }

        $title = $senderUser->name ?? 'New Message';
        $body = substr($messageContent, 0, 150);

        return $this->firebaseService->sendToToken(
            $receiverUser->fcm_token,
            $title,
            $body,
            [
                'type' => 'private_message',
                'sender_id' => (string)$senderUser->id,
                'message_id' => $messageId ? (string)$messageId : null,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]
        );
    }

    /**
     * Send a group message notification to multiple members
     */
    public function sendGroupMessageNotification($groupMembers, $senderUser, $messageContent, $groupName, $groupId): int
    {
        if (empty($groupMembers)) {
            return 0;
        }

        $title = $groupName ?? 'Group Message';
        $body = ($senderUser->name ?? 'User') . ': ' . substr($messageContent, 0, 120);

        // Get device tokens for members
        $tokens = [];
        foreach ($groupMembers as $member) {
            if (!empty($member->fcm_token)) {
                $tokens[] = $member->fcm_token;
            }
        }

        if (empty($tokens)) {
            return 0;
        }

        return $this->firebaseService->sendToMultipleTokens(
            $tokens,
            $title,
            $body,
            [
                'type' => 'group_message',
                'group_id' => (string)$groupId,
                'sender_id' => (string)$senderUser->id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]
        );
    }

    /**
     * Send an announcement/system notification
     */
    public function sendAnnouncement($users, $title, $message): int
    {
        if (empty($users)) {
            return 0;
        }

        $tokens = [];
        foreach ($users as $user) {
            if (!empty($user->fcm_token)) {
                $tokens[] = $user->fcm_token;
            }
        }

        if (empty($tokens)) {
            return 0;
        }

        return $this->firebaseService->sendToMultipleTokens(
            $tokens,
            $title,
            substr($message, 0, 150),
            [
                'type' => 'announcement',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]
        );
    }

    /**
     * Send a custom notification
     */
    public function sendCustomNotification($fcmToken, $title, $message, $customData = []): bool
    {
        return $this->firebaseService->sendToToken(
            $fcmToken,
            $title,
            $message,
            array_merge($customData, ['click_action' => 'FLUTTER_NOTIFICATION_CLICK'])
        );
    }

    /**
     * Get the underlying Firebase service
     */
    public function getFirebaseService(): FirebaseService
    {
        return $this->firebaseService;
    }
}
