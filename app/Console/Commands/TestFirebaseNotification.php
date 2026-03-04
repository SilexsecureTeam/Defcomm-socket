<?php

namespace App\Console\Commands;

use App\Http\Services\FirebaseService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestFirebaseNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:test {--token=} {--user-id=} {--title=Test} {--body=This is a test notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Firebase Cloud Messaging integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Firebase Cloud Messaging Test ===\n');

        // Check Firebase configuration
        $this->checkConfiguration();

        // Get token from options or database
        $token = $this->getToken();
        if (!$token) {
            $this->error('No FCM token available. Please provide --token or --user-id option.');
            return 1;
        }

        // Send test notification
        return $this->sendTestNotification($token);
    }

    /**
     * Check Firebase configuration
     */
    private function checkConfiguration(): void
    {
        $this->info('Checking Firebase configuration...');

        $serverKey = config('firebase.server_key');
        $projectId = config('firebase.project_id');

        if (!$serverKey) {
            $this->warn('⚠ FIREBASE_SERVER_KEY not configured');
            $this->line('  Add FIREBASE_SERVER_KEY to .env file');
        } else {
            $this->info('✓ FIREBASE_SERVER_KEY configured (' . substr($serverKey, 0, 20) . '...)');
        }

        if (!$projectId) {
            $this->warn('⚠ FIREBASE_PROJECT_ID not configured');
            $this->line('  Add FIREBASE_PROJECT_ID to .env file');
        } else {
            $this->info('✓ FIREBASE_PROJECT_ID configured (' . $projectId . ')');
        }

        $this->line('');
    }

    /**
     * Get FCM token from command options or database
     */
    private function getToken(): ?string
    {
        // Check if token provided as option
        $token = $this->option('token');
        if ($token) {
            $this->info("Using provided token: " . substr($token, 0, 30) . '...\n');
            return $token;
        }

        // Check if user ID provided
        $userId = $this->option('user-id');
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return null;
            }

            if (!$user->fcm_token) {
                $this->warn("User {$userId} has no device token.");
                return null;
            }

            $this->info("Using token from user {$userId} ({$user->name})");
            return $user->fcm_token;
        }

        // Try to find first user with device token
        $user = User::whereNotNull('fcm_token')->first();
        if ($user) {
            $this->info("Using token from first available user: {$user->name} (ID: {$user->id})");
            return $user->fcm_token;
        }

        return null;
    }

    /**
     * Send test notification
     */
    private function sendTestNotification(string $token): int
    {
        try {
            $firebaseService = new FirebaseService();

            $title = $this->option('title');
            $body = $this->option('body');

            $this->info('Sending notification...');
            $this->line("  Title: {$title}");
            $this->line("  Body: {$body}");
            $this->line("  Token: " . substr($token, 0, 40) . '...\n');

            $result = $firebaseService->sendToToken(
                $token,
                $title,
                $body,
                [
                    'test' => 'true',
                    'timestamp' => now()->toIso8601String(),
                ]
            );

            if ($result) {
                $this->info('✓ Notification sent successfully!');
                $this->line('');
                $this->info('Note: Notification delivery depends on:');
                $this->line('  1. Valid FCM token');
                $this->line('  2. App installed on device');
                $this->line('  3. App has notification permissions');
                $this->line('  4. Device connected to internet');
                return 0;
            } else {
                $this->error('✗ Failed to send notification');
                $this->line('Check storage/logs/laravel.log for details');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Firebase test command error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}
