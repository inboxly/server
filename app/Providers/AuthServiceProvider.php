<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Category;
use App\Models\Collection;
use App\Policies\CategoryPolicy;
use App\Policies\CollectionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Collection::class => CollectionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
