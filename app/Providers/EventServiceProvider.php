<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\PrivateMessageSent;
use App\Events\PrivateGroupMessageSent;
use App\Events\PrivateWalkieMessageSent;
use App\Events\GroupMessageSent;
use App\Events\MessageSent;
use App\Listeners\SendPrivateMessageNotification;
use App\Listeners\SendPrivateGroupMessageNotification;
use App\Listeners\SendPrivateWalkieMessageNotification;
use App\Listeners\SendGroupMessageNotification;
use App\Listeners\SendPublicMessageNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PrivateMessageSent::class => [
            SendPrivateMessageNotification::class,
        ],
        PrivateGroupMessageSent::class => [
            SendPrivateGroupMessageNotification::class,
        ],
        PrivateWalkieMessageSent::class => [
            SendPrivateWalkieMessageNotification::class,
        ],
        GroupMessageSent::class => [
            SendGroupMessageNotification::class,
        ],
        MessageSent::class => [
            SendPublicMessageNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
