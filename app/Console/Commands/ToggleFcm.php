<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ToggleFcm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:toggle {--enable} {--disable} {--status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable, disable, or check Firebase Cloud Messaging status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check status
        if ($this->option('status')) {
            return $this->checkStatus();
        }

        // Enable FCM
        if ($this->option('enable')) {
            return $this->enableFcm();
        }

        // Disable FCM
        if ($this->option('disable')) {
            return $this->disableFcm();
        }

        // Default: show current status
        return $this->checkStatus();
    }

    /**
     * Check current FCM status
     */
    private function checkStatus(): int
    {
        $enabled = config('firebase.enabled', true);

        $this->info('=== Firebase Cloud Messaging Status ===');
        $this->line('');

        if ($enabled) {
            $this->info('✓ FCM is ENABLED');
            $this->line('  Push notifications will be sent to users.');
        } else {
            $this->warn('✗ FCM is DISABLED');
            $this->line('  Push notifications will NOT be sent.');
        }

        $this->line('');
        $this->info('Current .env setting:');
        $envStatus = $this->getEnvStatus();
        $this->line('  FCM_ENABLED=' . $envStatus);
        $this->line('');

        return 0;
    }

    /**
     * Enable FCM
     */
    private function enableFcm(): int
    {
        if (config('firebase.enabled', true)) {
            $this->info('✓ FCM is already enabled');
            return 0;
        }

        if ($this->updateEnvFile('FCM_ENABLED', 'true')) {
            $this->info('✓ FCM has been ENABLED');
            $this->line('');
            $this->warn('Note: Clear Laravel cache to apply changes');
            $this->line('  Run: php artisan config:clear');
            $this->line('');

            Log::info('FCM enabled via command');

            return 0;
        }

        $this->error('✗ Failed to enable FCM');
        $this->line('  Please manually edit .env and set: FCM_ENABLED=true');

        return 1;
    }

    /**
     * Disable FCM
     */
    private function disableFcm(): int
    {
        if (!config('firebase.enabled', true)) {
            $this->info('✓ FCM is already disabled');
            return 0;
        }

        if ($this->updateEnvFile('FCM_ENABLED', 'false')) {
            $this->info('✓ FCM has been DISABLED');
            $this->line('');
            $this->warn('Note: Clear Laravel cache to apply changes');
            $this->line('  Run: php artisan config:clear');
            $this->line('');

            Log::warning('FCM disabled via command');

            return 0;
        }

        $this->error('✗ Failed to disable FCM');
        $this->line('  Please manually edit .env and set: FCM_ENABLED=false');

        return 1;
    }

    /**
     * Update .env file with new FCM_ENABLED value
     */
    private function updateEnvFile(string $key, string $value): bool
    {
        try {
            $envPath = base_path('.env');

            if (!file_exists($envPath)) {
                $this->error('.env file not found');
                return false;
            }

            $envContent = file_get_contents($envPath);

            // Check if FCM_ENABLED already exists
            if (strpos($envContent, 'FCM_ENABLED=') !== false) {
                // Replace existing value
                $envContent = preg_replace(
                    '/FCM_ENABLED\s*=\s*.*/i',
                    'FCM_ENABLED=' . $value,
                    $envContent
                );
            } else {
                // Add new line before other Firebase settings
                $envContent = preg_replace(
                    '/(# Firebase Cloud Messaging Configuration)/i',
                    "$1\nFCM_ENABLED=" . $value,
                    $envContent
                );
            }

            // Write back to file
            file_put_contents($envPath, $envContent);

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating .env file', ['exception' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get current FCM_ENABLED value from .env
     */
    private function getEnvStatus(): string
    {
        try {
            $envPath = base_path('.env');
            if (!file_exists($envPath)) {
                return 'Unknown (no .env file)';
            }

            $envContent = file_get_contents($envPath);
            if (preg_match('/FCM_ENABLED\s*=\s*(\w+)/i', $envContent, $matches)) {
                return $matches[1];
            }

            return 'Not set (defaults to true)';
        } catch (\Exception $e) {
            return 'Error reading .env';
        }
    }
}
