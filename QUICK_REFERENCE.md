# Firebase Cloud Messaging - Quick Reference Guide

## 🚀 Pre-Deployment Checklist

### Phase 1: Configuration (5 mins)

- [ ] Get Firebase credentials from Firebase Console
- [ ] Add FIREBASE\_\* variables to .env file
- [ ] Verify credentials in .env (no typos/spaces)
- [ ] Run `php artisan config:clear`

### Phase 2: Database (2 mins)

- [ ] Check migration exists: `2025_02_18_095517_add_more_to_users_table.php`
- [ ] Run migrations: `php artisan migrate`
- [ ] Verify columns added: `device_token`, `device_type`

### Phase 3: Testing (5 mins)

- [ ] Test Firebase connection: `php artisan firebase:test`
- [ ] Check for errors in logs
- [ ] Verify event listeners registered
- [ ] Check: `php artisan tinker` → `config('firebase.server_key')`

### Phase 4: Mobile App Integration (10 mins)

- [ ] Update mobile app to request notification permissions
- [ ] Collect FCM token on app startup
- [ ] Send device_token in login request
- [ ] Set correct device_type (android/ios/web)

### Phase 5: Verification (10 mins)

- [ ] Create test user and log in with device_token
- [ ] Send test message
- [ ] Check: Notification received on device
- [ ] Check logs for "FCM notification sent successfully"
- [ ] Verify no errors in storage/logs/laravel.log

---

## 📋 File Locations Reference

```
📁 config/
  └─ firebase.php                          (Configuration)

📁 app/Http/Services/
  ├─ FirebaseService.php                   (Main service)
  └─ FirebaseNotificationHelper.php        (Helper)

📁 app/Listeners/
  ├─ SendPrivateMessageNotification.php
  ├─ SendPrivateGroupMessageNotification.php
  ├─ SendPrivateWalkieMessageNotification.php
  ├─ SendGroupMessageNotification.php
  └─ SendPublicMessageNotification.php

📁 app/Events/
  ├─ PrivateMessageSent.php                (Updated)
  ├─ PrivateGroupMessageSent.php           (Updated)
  ├─ PrivateWalkieMessageSent.php          (Updated)
  ├─ GroupMessageSent.php                  (Updated)
  └─ MessageSent.php                       (Updated)

📁 app/Console/Commands/
  └─ TestFirebaseNotification.php          (Test command)

📁 app/Providers/
  └─ EventServiceProvider.php              (Updated)

📁 app/Http/Controllers/API/
  └─ AuthController.php                    (Updated)

📄 Documentation:
  ├─ FCM_IMPLEMENTATION_GUIDE.md           (Detailed guide)
  ├─ FCM_USAGE_EXAMPLES.php                (Code examples)
  ├─ IMPLEMENTATION_SUMMARY.md             (Summary)
  ├─ FCM_SETUP.sh                          (Setup script)
  └─ .env                                  (Credentials)
```

---

## 🔑 Firebase Credentials Setup

Get from [Firebase Console](https://console.firebase.google.com):

1. Project Settings → Service Accounts
2. Generate New Private Key
3. Copy values to .env:

```env
FIREBASE_SERVER_KEY="AIzaSyCBG2d......"
FIREBASE_PROJECT_ID="my-project-id"
FIREBASE_MESSAGING_SENDER_ID="123456789"
FIREBASE_API_KEY="AIzaSyClP2f......"
FIREBASE_DATABASE_URL="https://my-project.firebaseio.com"
```

---

## 🧪 Common Commands

### Test FCM

```bash
php artisan firebase:test
php artisan firebase:test --user-id=1
php artisan firebase:test --token="ABC123"
```

### Check Configuration

```bash
php artisan tinker
> config('firebase.server_key')
> config('firebase.project_id')
```

### View Logs

```bash
tail -f storage/logs/laravel.log | grep FCM
```

### Run Migrations

```bash
php artisan migrate
```

### Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📱 API Endpoints

### Login

```
POST /api/login
{
  "email": "user@example.com",
  "password": "password",
  "device_token": "FCM_TOKEN_HERE",
  "device_type": "android"
}
```

### Login with Phone

```
POST /api/login-with-phone
{
  "phone": "+234XXXXXXXXXX",
  "otp": "1234",
  "device_token": "FCM_TOKEN_HERE",
  "device_type": "ios"
}
```

---

## 🔍 Troubleshooting

### No notifications received?

1. Check user has device_token: `SELECT id, device_token FROM users WHERE id=1;`
2. Check logs: `tail -f storage/logs/laravel.log`
3. Test command: `php artisan firebase:test --user-id=1`
4. Verify app has notification permissions

### Invalid server key?

1. Verify no extra spaces in .env
2. Check credentials in Firebase Console
3. Regenerate if needed
4. Clear cache: `php artisan config:clear`

### Configuration not loading?

1. Clear cache: `php artisan config:clear`
2. Verify .env syntax
3. Check environment: `php artisan env`
4. Restart Laravel app

### Event listeners not firing?

1. Check EventServiceProvider.php has all 5 listeners
2. Verify events exist: `.env` → `EVENTS_PATH`
3. Check event namespace matches listener

---

## 📊 Notification Types

| Type            | Event                    | Listener                             | Target              |
| --------------- | ------------------------ | ------------------------------------ | ------------------- |
| Private Message | PrivateMessageSent       | SendPrivateMessageNotification       | Single user         |
| Private Group   | PrivateGroupMessageSent  | SendPrivateGroupMessageNotification  | Group members       |
| Walkie-Talkie   | PrivateWalkieMessageSent | SendPrivateWalkieMessageNotification | Channel subscribers |
| Public Group    | GroupMessageSent         | SendGroupMessageNotification         | Group members       |
| Public Chat     | MessageSent              | SendPublicMessageNotification        | All users           |

---

## 🛠️ Development Tips

### Using Helper (Recommended)

```php
$helper = new FirebaseNotificationHelper();
$helper->sendPrivateMessageNotification($receiver, $sender, "message");
```

### Using Service (Advanced)

```php
$service = new FirebaseService();
$service->sendToToken($token, "Title", "Body", []);
```

### Testing in Tinker

```php
php artisan tinker
> $service = app('App\Http\Services\FirebaseService')
> $service->sendToToken('test_token', 'Test', 'Message')
> exit
```

---

## 📈 Performance Tips

1. **Batch Notifications**: Use multiple tokens at once (max 500)
2. **Queue Jobs**: Dispatch large sends as background jobs
3. **Check Tokens**: Remove invalid tokens regularly
4. **Monitor Logs**: Watch for repeated failures
5. **Cache Config**: Run `php artisan config:cache` in production

---

## 🔒 Security Reminders

✓ Never commit real Firebase keys to git
✓ Use .env for all sensitive credentials
✓ Validate device_token before saving
✓ Implement rate limiting for high volume
✓ Log all notification sends
✓ Encrypt device tokens in transit
✓ Use HTTPS only (not HTTP)

---

## 📞 Need Help?

1. Check [FCM_IMPLEMENTATION_GUIDE.md](FCM_IMPLEMENTATION_GUIDE.md)
2. Review [FCM_USAGE_EXAMPLES.php](FCM_USAGE_EXAMPLES.php)
3. Search logs: `grep FCM storage/logs/laravel.log`
4. Test command: `php artisan firebase:test`
5. Firebase Console: https://console.firebase.google.com

---

## ✅ Deployment Checklist (Final)

Before going live:

- [ ] All Firebase credentials configured
- [ ] Database migrations applied
- [ ] Test notification sent and received
- [ ] Mobile apps updated with device_token support
- [ ] Logs configured and monitored
- [ ] Error handling verified
- [ ] User preferences implemented
- [ ] Rate limiting configured
- [ ] Backup/recovery plan ready
- [ ] Team trained on system

---

**Status**: Production Ready ✓
**Last Updated**: March 3, 2026
**Version**: 1.0
