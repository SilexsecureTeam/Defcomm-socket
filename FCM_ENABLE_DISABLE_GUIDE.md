# FCM Enable/Disable Guide

## Overview

Firebase Cloud Messaging (FCM) can be easily enabled or disabled without modifying code. This is useful for:

- Development/Testing environments
- Maintenance periods
- Performance testing
- Gradual rollout

---

## Methods to Enable/Disable FCM

### Method 1: Using Artisan Command (Recommended)

#### Check Current Status

```bash
php artisan fcm:toggle --status
```

Output:

```
=== Firebase Cloud Messaging Status ===

✓ FCM is ENABLED
  Push notifications will be sent to users.

Current .env setting:
  FCM_ENABLED=true
```

#### Enable FCM

```bash
php artisan fcm:toggle --enable
```

Output:

```
✓ FCM has been ENABLED

Note: Clear Laravel cache to apply changes
  Run: php artisan config:clear
```

#### Disable FCM

```bash
php artisan fcm:toggle --disable
```

Output:

```
✓ FCM has been DISABLED

Note: Clear Laravel cache to apply changes
  Run: php artisan config:clear
```

### Method 2: Manual .env Configuration

Edit your `.env` file:

```env
# Enable FCM
FCM_ENABLED=true

# Disable FCM
FCM_ENABLED=false
```

Then clear cache:

```bash
php artisan config:clear
```

### Method 3: Programmatic Check

In your code, you can check if FCM is enabled:

```php
if (config('firebase.enabled')) {
    // FCM is enabled, notifications will be sent
} else {
    // FCM is disabled, notifications will be skipped
}
```

---

## Behavior When FCM is Disabled

When `FCM_ENABLED=false`:

✗ No push notifications are sent
✗ No HTTP requests to Firebase servers
✗ No credit consumption from Firebase quota
✓ Event listeners still execute but skip FCM sending
✓ WebSocket broadcasting still works (Pusher continues)
✓ All operations logged at DEBUG level
✓ No errors or exceptions raised

---

## Use Cases

### Development Environment

Disable FCM during development to avoid sending notifications:

```env
# .env.local
FCM_ENABLED=false
```

Then enable when ready to test notifications.

### Testing

```bash
# Run tests with FCM disabled to speed up tests
FCM_ENABLED=false php artisan test
```

### Maintenance Window

Disable temporarily during maintenance:

```bash
php artisan fcm:toggle --disable

# Do maintenance work...

php artisan fcm:toggle --enable
php artisan config:clear
```

### Performance Testing

Disable FCM to isolate performance impact:

```bash
# Measure with FCM disabled
php artisan fcm:toggle --disable
# Run performance tests...

# Measure with FCM enabled
php artisan fcm:toggle --enable
php artisan config:clear
# Run performance tests again...
```

### Gradual Rollout

Enable FCM only for specific user groups initially:

```php
// In a listener or service
if (config('firebase.enabled') && $user->is_beta_tester) {
    // Send FCM only to beta testers
    $firebaseService->sendToUser($user, $title, $body);
}
```

---

## Configuration Hierarchy

FCM status is determined in this order:

1. **Environment Variable** (`.env`)

    ```env
    FCM_ENABLED=false
    ```

2. **Config File** (`config/firebase.php`)

    ```php
    'enabled' => env('FCM_ENABLED', true),
    ```

3. **Default Value** (if not set)
    - Defaults to `true` (enabled)

---

## Command Reference

### Check Status

```bash
php artisan fcm:toggle --status
```

**Options:**

- `--status` - Display current FCM status

### Enable FCM

```bash
php artisan fcm:toggle --enable
```

**Options:**

- `--enable` - Enable FCM notifications

### Disable FCM

```bash
php artisan fcm:toggle --disable
```

**Options:**

- `--disable` - Disable FCM notifications

### Default (No Arguments)

```bash
php artisan fcm:toggle
```

Shows current status (same as `--status`)

---

## Logging

### When Enabled

FCM operations are logged at INFO/ERROR levels:

```
[2026-03-03 12:00:00] local.INFO: FCM notification sent successfully
[2026-03-03 12:00:01] local.ERROR: FCM notification failed
```

### When Disabled

Skipped notifications are logged at DEBUG level:

```
[2026-03-03 12:00:00] local.DEBUG: FCM is disabled, skipping notification
```

View debug logs:

```bash
tail -f storage/logs/laravel.log | grep "FCM is disabled"
```

---

## Verifying Status

### Check Configuration

```bash
php artisan tinker

# Check config
> config('firebase.enabled')
true

# Check environment variable
> env('FCM_ENABLED')
"true"

exit
```

### Test with Command

```bash
# This will show if FCM is on/off
php artisan fcm:toggle --status
```

### Check Logs

```bash
# Look for FCM activity
grep -i 'fcm' storage/logs/laravel.log

# If disabled, you'll see:
# "FCM is disabled, skipping notification"
```

---

## Common Scenarios

### Local Development

```env
FCM_ENABLED=false
```

Disable in local `.env` to avoid test notifications.

### Staging Environment

```env
FCM_ENABLED=true
FIREBASE_SERVER_KEY="staging-key"
```

Use staging Firebase credentials.

### Production

```env
FCM_ENABLED=true
FIREBASE_SERVER_KEY="production-key"
```

Enable with production Firebase credentials.

### Testing Suite

```bash
# Automatically runs with FCM disabled for speed
FCM_ENABLED=false php artisan test --parallel
```

### Maintenance Mode

```bash
# Disable FCM during updates
php artisan fcm:toggle --disable

# Do maintenance...

# Re-enable when ready
php artisan fcm:toggle --enable
php artisan config:clear
```

---

## Troubleshooting

### Command Not Found

```bash
# Make sure you've created the ToggleFcm.php file
ls app/Console/Commands/ToggleFcm.php

# Rerun cache clear
php artisan config:clear
```

### Changes Not Taking Effect

Always clear cache after modifying .env:

```bash
php artisan config:clear
```

### Permission Denied (when updating .env)

```bash
# Ensure .env is writable
chmod 644 .env

# Or use manual method:
# Edit .env directly in your editor
```

---

## Performance Impact

### With FCM Enabled

- ~10-50ms per notification (network latency dependent)
- Asynchronous when handled by listeners
- Minimal CPU/memory impact

### With FCM Disabled

- ~1ms per notification (just a config check)
- No network requests
- No Firebase quota consumption

---

## Monitoring

Monitor FCM status and activity:

```bash
# View FCM logs in real-time
tail -f storage/logs/laravel.log | grep -i fcm

# Count FCM errors
grep 'FCM notification failed' storage/logs/laravel.log | wc -l

# See disabled notifications
grep 'FCM is disabled' storage/logs/laravel.log

# Check success rate
grep -c 'FCM notification sent successfully' storage/logs/laravel.log
```

---

## Best Practices

1. **Always disable for local development**
    - Prevents test notifications from being sent
    - Saves Firebase quota during development

2. **Test enable/disable workflow**
    - Verify toggling works in your environment
    - Ensure cache clears properly

3. **Monitor logs**
    - Check for disabled notifications in logs
    - Verify normal operation when enabled

4. **Use in CI/CD**
    - Set `FCM_ENABLED=false` for test environments
    - Set `FCM_ENABLED=true` only in production

5. **Document your setup**
    - Note which environments have FCM enabled/disabled
    - Update team documentation

---

## Summary

| Action         | Command                            | Environment |
| -------------- | ---------------------------------- | ----------- |
| Check status   | `php artisan fcm:toggle --status`  | Any         |
| Enable FCM     | `php artisan fcm:toggle --enable`  | Any         |
| Disable FCM    | `php artisan fcm:toggle --disable` | Any         |
| Manual enable  | Set `FCM_ENABLED=true` in .env     | Any         |
| Manual disable | Set `FCM_ENABLED=false` in .env    | Any         |
| Clear cache    | `php artisan config:clear`         | Any         |

---

That's it! FCM can now be easily toggled on and off without modifying your code.
