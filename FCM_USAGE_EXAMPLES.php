<?php

/**
 * Firebase Cloud Messaging Integration Examples
 * 
 * These examples demonstrate how to use the FCM service in various scenarios.
 * These are NOT actual route handlers, just code snippets for reference.
 * 
 * @see FirebaseService
 * @see FirebaseNotificationHelper
 */

// Example 1: Send notification in a controller
namespace App\Http\Controllers\API;

use App\Http\Services\FirebaseService;
use App\Http\Services\FirebaseNotificationHelper;
use App\Models\User;
use Illuminate\Http\Request;

class ExampleController
{
    /**
     * Example: Send a private message notification
     */
    public function sendPrivateMessageExample(Request $request)
    {
        $sender = auth()->user();
        $receiver = User::find($request->receiver_id);

        // Using helper (recommended for simple cases)
        $helper = new FirebaseNotificationHelper();
        $success = $helper->sendPrivateMessageNotification(
            $receiver,
            $sender,
            "This is a test message",
            null
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification sent' : 'Failed to send notification'
        ]);
    }

    /**
     * Example: Send group message notification
     */
    public function sendGroupMessageExample(Request $request)
    {
        $sender = auth()->user();
        $groupId = $request->group_id;

        // Get group members
        $groupMembers = User::whereIn('id', function ($query) use ($groupId) {
            $query->select('user_id')
                ->from('company_group_users')
                ->where('group_id', $groupId)
                ->where('user_id', '!=', $sender->id);
        })->get();

        $helper = new FirebaseNotificationHelper();
        $sentCount = $helper->sendGroupMessageNotification(
            $groupMembers,
            $sender,
            "Group message content here",
            "Group Name",
            $groupId
        );

        return response()->json([
            'sent_count' => $sentCount,
            'total_members' => count($groupMembers)
        ]);
    }

    /**
     * Example: Using the raw FirebaseService for advanced scenarios
     */
    public function advancedExample(Request $request)
    {
        $firebaseService = new FirebaseService();

        $user = auth()->user();
        if (!$user->device_token) {
            return response()->json(['error' => 'No device token'], 400);
        }

        // Send with custom data structure
        $result = $firebaseService->sendToToken(
            $user->device_token,
            'Custom Title',
            'Custom Body',
            [
                'screen' => 'message_detail',
                'message_id' => '123',
                'sender_avatar' => 'https://example.com/avatar.jpg',
            ]
        );

        return response()->json(['success' => $result]);
    }

    /**
     * Example: Send to multiple users (batch)
     */
    public function sendBroadcastExample(Request $request)
    {
        $firebaseService = new FirebaseService();

        // Get all active users with device tokens
        $users = User::where('status', 'active')
            ->whereNotNull('device_token')
            ->pluck('device_token')
            ->toArray();

        if (empty($users)) {
            return response()->json(['error' => 'No users to notify'], 400);
        }

        // Send to all users
        $successCount = $firebaseService->sendToMultipleTokens(
            $users,
            'Important Announcement',
            $request->message,
            [
                'type' => 'announcement',
                'action_url' => '/announcements/' . $request->announcement_id,
            ]
        );

        return response()->json([
            'sent' => $successCount,
            'total' => count($users)
        ]);
    }

    /**
     * Example: Send to topic (e.g., all users subscribed to "news")
     */
    public function sendToTopicExample(Request $request)
    {
        $firebaseService = new FirebaseService();

        $result = $firebaseService->sendToTopic(
            'news',
            'Breaking News',
            $request->news_content,
            [
                'type' => 'news',
                'category' => 'breaking',
            ]
        );

        return response()->json(['success' => $result]);
    }

    /**
     * Example: Subscribe user to topic
     */
    public function subscribeToTopicExample(Request $request)
    {
        $user = auth()->user();
        $firebaseService = new FirebaseService();

        if (!$user->device_token) {
            return response()->json(['error' => 'No device token'], 400);
        }

        $success = $firebaseService->subscribeToTopic(
            $user->device_token,
            'news'
        );

        if ($success) {
            // Save preference to database
            $user->update(['news_subscribed' => true]);
        }

        return response()->json(['success' => $success]);
    }
}

// Example 2: In a Service class
namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private FirebaseService $firebaseService;
    private FirebaseNotificationHelper $helper;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
        $this->helper = new FirebaseNotificationHelper();
    }

    /**
     * Send notification when user is mentioned
     */
    public function notifyMention($mentionedUser, $mentioner, $context)
    {
        if (!$mentionedUser->device_token) {
            Log::debug('User has no device token', ['user_id' => $mentionedUser->id]);
            return false;
        }

        return $this->firebaseService->sendToToken(
            $mentionedUser->device_token,
            $mentioner->name . ' mentioned you',
            substr($context, 0, 100),
            [
                'type' => 'mention',
                'mentioner_id' => $mentioner->id,
                'context_id' => md5($context),
            ]
        );
    }

    /**
     * Send notification for friend request
     */
    public function notifyFriendRequest($receiver, $sender)
    {
        return $this->helper->sendCustomNotification(
            $receiver->device_token,
            'Friend Request',
            $sender->name . ' sent you a friend request',
            [
                'type' => 'friend_request',
                'sender_id' => $sender->id,
                'action' => 'view_request',
            ]
        );
    }

    /**
     * Send notification for activity (e.g., user joined group)
     */
    public function notifyActivity($users, $activityTitle, $activityDescription)
    {
        return $this->helper->sendAnnouncement(
            $users,
            $activityTitle,
            $activityDescription
        );
    }
}

// Example 3: In Event listener
namespace App\Listeners;

use App\Events\CustomEvent;
use App\Http\Services\FirebaseService;

class CustomEventListener
{
    private FirebaseService $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    public function handle(CustomEvent $event)
    {
        // Send notification based on event
        $this->firebaseService->sendToToken(
            $event->user->device_token,
            $event->title,
            $event->message,
            $event->data ?? []
        );
    }
}

// Example 4: In a Queued Job
namespace App\Jobs;

use App\Http\Services\FirebaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $tokens,
        private string $title,
        private string $message,
        private array $data = []
    ) {}

    public function handle()
    {
        $firebaseService = new FirebaseService();

        // Send to all tokens
        foreach (array_chunk($this->tokens, 500) as $batch) {
            $firebaseService->sendToMultipleTokens(
                $batch,
                $this->title,
                $this->message,
                $this->data
            );
        }
    }
}

// Example 5: In Tinker (testing)
/*
php artisan tinker

// Test 1: Basic send
>>> $service = app('App\Http\Services\FirebaseService');
>>> $service->sendToToken('test_token', 'Test Title', 'Test Body');

// Test 2: Get config
>>> config('firebase.server_key');

// Test 3: Send to user
>>> $user = \App\Models\User::first();
>>> $service->sendToUser($user, 'Hello', 'This is a test');

// Test 4: Check logs
>>> \Illuminate\Support\Facades\Log::info('Test log');

exit
*/
