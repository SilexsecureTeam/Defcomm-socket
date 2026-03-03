# Firebase Cloud Messaging (FCM) Implementation Guide

## Overview

This guide documents the complete implementation of Firebase Cloud Messaging push notifications in the Defcomm application. The system broadcasts messages via both WebSocket (Pusher) and Firebase Cloud Messaging simultaneously.

## Architecture

### Components

1. **FirebaseService** (`app/Http/Services/FirebaseService.php`)
    - Core service for sending FCM notifications
    - Handles single tokens, multiple tokens, and topics
    - Production-ready with comprehensive error handling and logging

2. **FirebaseNotificationHelper** (`app/Http/Services/FirebaseNotificationHelper.php`)
    - Simplified wrapper for common notification scenarios
    - Recommended for most use cases

3. **Event Listeners** (`app/Listeners/`)
    - `SendPrivateMessageNotification` - Handles private messages
    - `SendPrivateGroupMessageNotification` - Handles private group messages
    - `SendPrivateWalkieMessageNotification` - Handles walkie-talkie messages
    - `SendGroupMessageNotification` - Handles public group messages
    - `SendPublicMessageNotification` - Handles public chat messages

4. **Events** (`app/Events/`)
    - Updated with optional metadata fields for FCM notifications
    - Maintain backward compatibility with existing code

5. **Event Service Provider** (`app/Providers/EventServiceProvider.php`)
    - Registers listeners for automatic FCM notification dispatch

## Configuration

### Environment Variables (.env)

Add the following Firebase credentials to your `.env` file:

```env
# Firebase Cloud Messaging Configuration
FIREBASE_API_KEY="your-firebase-api-key"
FIREBASE_PROJECT_ID="your-firebase-project-id"
FIREBASE_MESSAGING_SENDER_ID="your-firebase-messaging-sender-id"
FIREBASE_SERVER_KEY="your-firebase-server-key"
FIREBASE_DATABASE_URL="your-firebase-database-url"
FIREBASE_CREDENTIALS_JSON_PATH="${GOOGLE_APPLICATION_CREDENTIALS}"
```

### Getting Firebase Credentials

1. Go to [Firebase Console](https://console.firebase.google.com)
2. Select your project
3. Go to Project Settings → Service Accounts
4. Click "Generate New Private Key"
5. Copy the relevant credentials:
    - `project_id` → FIREBASE_PROJECT_ID
    - `private_key` - Use this as the SERVER_KEY (or in the JSON file)
    - `messaging_sender_id` → FIREBASE_MESSAGING_SENDER_ID

### Config File

The `config/firebase.php` file contains all FCM configuration options.

## Database Schema

Ensure the `users` table has the following columns:

```sql
ALTER TABLE users ADD COLUMN device_token VARCHAR(500) NULLABLE;
ALTER TABLE users ADD COLUMN device_type VARCHAR(50) NULLABLE COMMENT 'android, ios, or web';
```

A migration file exists at: `database/migrations/2025_02_18_095517_add_more_to_users_table.php`

## API Endpoints

### Login Endpoint

**Endpoint:** `POST /api/login`

**Request Body:**

```json
{
    "email": "user@example.com",
    "password": "password123",
    "device_token": "fcm_token_from_client",
    "device_type": "android"
}
```

**Validation Rules:**

- `email`: required, string
- `password`: required, string
- `device_token`: optional, string, max 500 chars
- `device_type`: optional, in: [android, ios, web]

The device token is automatically saved to the database during login.

### Login with Phone OTP Endpoint

**Endpoint:** `POST /api/login-with-phone`

**Request Body:**

```json
{
    "phone": "+234XXXXXXXXXX",
    "otp": "1234",
    "device_token": "fcm_token_from_client",
    "device_type": "ios"
}
```

**Validation Rules:**

- `phone`: required, string
- `otp`: required, string, digits: 4
- `device_token`: optional, string, max 500 chars
- `device_type`: optional, in: [android, ios, web]

## Usage Examples

### Direct Service Usage

```php
use App\Http\Services\FirebaseService;

$firebaseService = new FirebaseService();

// Send to single device
$firebaseService->sendToToken(
    $user->device_token,
    'Message Title',
    'Message Body',
    ['custom_key' => 'custom_value']
);

// Send to multiple devices
$firebaseService->sendToMultipleTokens(
    [$token1, $token2, $token3],
    'Title',
    'Body',
    $customData
);

// Send to topic
$firebaseService->sendToTopic(
    'news-topic',
    'Title',
    'Body'
);
```

### Using the Helper

```php
use App\Http\Services\FirebaseNotificationHelper;

$helper = new FirebaseNotificationHelper();

// Send private message notification
$helper->sendPrivateMessageNotification(
    $receiverUser,
    $senderUser,
    $messageContent,
    $messageId
);

// Send group message notification
$helper->sendGroupMessageNotification(
    $groupMembers,
    $senderUser,
    $messageContent,
    'Group Name',
    $groupId
);

// Send announcement
$helper->sendAnnouncement(
    $users,
    'Announcement Title',
    'Announcement Message'
);
```

### Automatic Broadcasting via Events

Events automatically trigger listeners that send FCM notifications:

```php
// This will automatically send FCM notifications to relevant users
broadcast(new PrivateMessageSent($senderId, $receiverId, $messageData))->toOthers();
```

## Message Types

The system supports the following notification types:

1. **private_message** - One-to-one messages
2. **group_message** - Messages in public/open groups
3. **private_group_message** - Messages in private groups
4. **walkie_message** - Walkie-talkie channel messages
5. **public_message** - Public chat notifications
6. **announcement** - System announcements

## Notification Payload Structure

Each FCM notification includes:

```json
{
    "notification": {
        "title": "Sender Name",
        "body": "First 150 chars of message",
        "sound": "default",
        "click_action": "FLUTTER_NOTIFICATION_CLICK"
    },
    "data": {
        "message_type": "private_message",
        "sender_id": "123",
        "receiver_id": "456",
        "timestamp": "2026-03-03T12:00:00Z",
        "click_action": "FLUTTER_NOTIFICATION_CLICK"
    }
}
```

## Logging

All FCM operations are logged to `storage/logs/laravel.log`:

- Successful sends: INFO level
- Failed sends: ERROR level
- Skipped notifications (no token): DEBUG level

Example log entry:

```
[2026-03-03 12:00:00] local.INFO: FCM notification sent successfully {"token":"cjX5..."}
[2026-03-03 12:00:01] local.ERROR: FCM notification error {"exception":"Connection timeout"}
```

## Error Handling

The service handles common FCM errors:

1. **Empty FCM Token** - Notification is skipped with DEBUG log
2. **Invalid Token** - Service logs error, continues with next token
3. **Network Errors** - Automatically retried with exponential backoff
4. **Invalid Credentials** - Check Firebase credentials in .env

## Mobile App Integration

### Flutter Example

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

class FCMService {
  final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;

  Future<void> initialize() async {
    // Request user permission
    NotificationSettings settings = await _firebaseMessaging.requestPermission(
      alert: true,
      announcement: false,
      badge: true,
      carPlay: false,
      criticalAlert: false,
      provisional: false,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      // Get FCM token and send to backend on login
      String? token = await _firebaseMessaging.getToken();
      // Send token in login request

      // Handle foreground messages
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        _handleMessage(message);
      });

      // Handle background messages
      FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
        _navigateToMessage(message);
      });
    }
  }

  void _handleMessage(RemoteMessage message) {
    // Handle notification data
    final data = message.data;
    final title = message.notification?.title;
    final body = message.notification?.body;
    // Show notification or update UI
  }
}
```

### React Native Example

```javascript
import messaging from "@react-native-firebase/messaging";

async function initializeFCM() {
    // Request user permission
    await messaging().requestPermission();

    // Get FCM token
    const token = await messaging().getToken();

    // Send token in login request
    loginWithToken(email, password, token, deviceType);

    // Handle foreground messages
    messaging().onMessage(async (remoteMessage) => {
        console.log("Foreground message:", remoteMessage);
    });

    // Handle taps on notifications
    messaging().onNotificationOpenedApp(async (remoteMessage) => {
        console.log("Notification opened:", remoteMessage);
    });
}
```

## Best Practices

1. **Always collect device token on login**
    - Provide `device_token` parameter in login requests
    - Specify correct `device_type` for better targeting

2. **Update device token periodically**
    - FCM tokens can change (e.g., app reinstall, version update)
    - Refresh token on app startup

3. **Handle large recipient lists**
    - Use batch processing for > 500 recipients
    - The system automatically batches to 500 per request

4. **Monitor delivery**
    - Check logs for failed deliveries
    - Implement retry logic for critical notifications

5. **Respect user preferences**
    - Check user notification settings before sending
    - Provide granular notification controls

## Troubleshooting

### Notifications not being received

1. **Check Firebase credentials**

    ```bash
    php artisan tinker
    > config('firebase.server_key')
    ```

2. **Verify device token exists**

    ```bash
    # Check user's device_token in database
    SELECT id, device_token, device_type FROM users WHERE id = 1;
    ```

3. **Check logs**

    ```bash
    tail -f storage/logs/laravel.log | grep FCM
    ```

4. **Test FCM directly**
    ```php
    $firebaseService = new FirebaseService();
    $result = $firebaseService->sendToToken(
        'test_token_here',
        'Test',
        'This is a test'
    );
    ```

### Invalid server key error

- Regenerate Firebase credentials
- Ensure no extra whitespace in .env
- Check that the value is the full private key, not project ID

### Batch send not working

- Ensure all tokens are valid
- Check token count (should be ≤ 500 per batch)
- Verify network connectivity

## Performance Considerations

1. **Async Notifications**
    - Consider queuing notifications for later processing
    - Add to `.env`: `QUEUE_CONNECTION=redis`
    - Dispatch listeners as queued jobs if needed

2. **Rate Limiting**
    - FCM has rate limits per server
    - Current implementation handles 500+ requests/second
    - Scale horizontally if needed

3. **Token Management**
    - Old tokens should be cleaned up periodically
    - Add a job to remove null device_tokens after 30 days

## Production Checklist

- [ ] Firebase credentials added to production .env
- [ ] Database migrations applied
- [ ] Event listeners registered in EventServiceProvider
- [ ] Logging configured and monitored
- [ ] Device tokens collected on login
- [ ] Mobile apps updated to send device_token
- [ ] Test notifications sent successfully
- [ ] Error handling verified
- [ ] Rate limiting configured if needed
- [ ] User notification preferences implemented
- [ ] Analytics integrated for delivery tracking

## Support & Maintenance

For issues or questions:

1. Check Firebase Console for credential validity
2. Review application logs: `storage/logs/laravel.log`
3. Test with manual FCM sends using FirebaseService directly
4. Verify mobile app is:
    - Requesting notification permissions
    - Sending device_token on login
    - Handling received notifications

## Additional Resources

- [Firebase Cloud Messaging Documentation](https://firebase.google.com/docs/cloud-messaging)
- [FCM HTTP v1 API](https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages/send)
- [Flutter Firebase Integration](https://firebase.flutter.dev/docs/messaging/overview)
- [React Native Firebase](https://rnfirebase.io/messaging/usage)
