<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\OriginalEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserFeedsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        /** @var User $user */
        $user = User::where(['api_token' => 'api_token'])->firstOrFail();

        $categories = Category::factory(10)->create(['user_id' => $user->getKey()]);

        Feed::factory(40)
            ->create([
                'user_id' => $user->getKey(),
            ])
            ->each(function (Feed $feed) use ($user, $categories) {
                Entry::factory(rand(1, 30))->create([
                    'user_id' => $user->getKey(),
                    'feed_id' => $feed->getKey(),
                    'original_entry_id' => fn() => OriginalEntry::factory()->create([
                        'original_feed_id' => $feed->original_feed_id
                    ])->getKey(),
                ]);
                $feed->categories()->syncWithoutDetaching($categories->random(rand(1, rand(1, 5))));
            });

        // todo: mark as read part of entries
        // todo: mark as saved part of entries

        Collection::factory(3)->create(['user_id' => $user->getKey()]);

        // todo: add part of entries to collections
    }
}
