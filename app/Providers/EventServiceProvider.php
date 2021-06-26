<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Category;
use App\Models\Feed;
use App\Models\OriginalEntry;
use App\Models\User;
use App\Observers\CategoryObserver;
use App\Observers\FeedObserver;
use App\Observers\OriginalEntryObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        OriginalEntry::observe(OriginalEntryObserver::class);
        User::observe(UserObserver::class);
        Category::observe(CategoryObserver::class);
        Feed::observe(FeedObserver::class);
    }
}
