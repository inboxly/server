<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotfixSqliteTypes();

        $this->user = User::factory()->create(['id' => 1, 'api_token' => 'api_token']);
    }

    protected function asUser(): static
    {
        return $this->be($this->user, 'api');
    }

    protected function hotfixSqliteTypes()
    {
        Category::addGlobalScope(fn (Builder $builder) => $builder->withCasts([
            'id' => 'integer',
            'user_id' => 'integer',
        ]));

        Collection::addGlobalScope(fn (Builder $builder) => $builder->withCasts([
            'id' => 'integer',
            'user_id' => 'integer',
        ]));

        Entry::addGlobalScope(fn (Builder $builder) => $builder->withCasts([
            'user_id' => 'integer',
        ]));

        Feed::addGlobalScope(fn (Builder $builder) => $builder->withCasts([
            'user_id' => 'integer',
        ]));

        User::addGlobalScope(fn (Builder $builder) => $builder->withCasts([
            'id' => 'integer',
        ]));
    }
}
