#!/bin/bash

# FCM Implementation Quick Setup Guide
# This file provides quick reference for setting up FCM

echo "=== Firebase Cloud Messaging Setup Checklist ==="
echo ""

echo "1. Environment Variables Setup"
echo "   Add to .env file:"
echo "   - FIREBASE_API_KEY"
echo "   - FIREBASE_PROJECT_ID"
echo "   - FIREBASE_MESSAGING_SENDER_ID"
echo "   - FIREBASE_SERVER_KEY"
echo "   - FIREBASE_DATABASE_URL"
echo ""

echo "2. Database Setup"
echo "   Run migration:"
echo "   php artisan migrate"
echo ""

echo "3. Test FCM Connection"
echo "   php artisan tinker"
echo "   > app('App\Http\Services\FirebaseService')->sendToToken('test_token', 'Test', 'Test body')"
echo ""

echo "4. Check Logs"
echo "   tail -f storage/logs/laravel.log | grep FCM"
echo ""

echo "5. Verify Configuration"
echo "   php artisan config:cache"
echo "   php artisan config:clear"
echo ""

echo "6. Files Modified/Created:"
echo "   ✓ .env - Firebase credentials"
echo "   ✓ config/firebase.php - Configuration"
echo "   ✓ app/Http/Services/FirebaseService.php - Main service"
echo "   ✓ app/Http/Services/FirebaseNotificationHelper.php - Helper"
echo "   ✓ app/Listeners/Send*MessageNotification.php - Event listeners (5 files)"
echo "   ✓ app/Events/*.php - Updated with metadata (5 events)"
echo "   ✓ app/Providers/EventServiceProvider.php - Updated"
echo "   ✓ app/Http/Controllers/API/AuthController.php - Updated login functions"
echo ""

echo "7. Mobile App Integration"
echo "   - Ensure app collects FCM token"
echo "   - Send fcm_token in login requests"
echo "   - Set correct device_type (android/ios/web)"
echo ""

echo "=== Implementation Complete ==="
