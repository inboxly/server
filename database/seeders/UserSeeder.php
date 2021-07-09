<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\OriginalFeed;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['api_token' => 'api_token']);
        Collection::factory(2)->create(['user_id' => $user->getKey()]);

        $this->subscribeMain($user, [
            'https://lenta.ru/rss/',
        ]);

        $this->subscribeYoutube($user, [
            'https://www.youtube.com/feeds/videos.xml?channel_id=UCb9XEo_1SDNR8Ucpbktrg5A',
        ]);
    }

    private function subscribeMain(User $user, array $urls): void
    {
        foreach ($urls as $url) {
            /** @var OriginalFeed $originalFeed */
            $originalFeed = OriginalFeed::factory()->create([
                'fetcher_key' => 'rss',
                'fetcher_feed_id' => $url,
                'parameters->url' => $url,
            ]);

            /** @var \App\Models\Feed $feed */
            $feed = $user->feeds()->create([
                'original_feed_id' => $originalFeed->id,
                'title' => 'Lenta.ru',
            ]);

            $user->defaultCategory->feeds()->attach($feed);
        }
    }

    private function subscribeYoutube(User $user, array $urls): void
    {
        foreach ($urls as $url) {
            /** @var OriginalFeed $originalFeed */
            $originalFeed = OriginalFeed::factory()->create([
                'fetcher_key' => 'youtube_rss',
                'fetcher_feed_id' => $url,
                'parameters->url' => $url,
            ]);

            /** @var \App\Models\Feed $feed */
            $feed = $user->feeds()->create([
                'original_feed_id' => $originalFeed->id,
            ]);

            /** @var \App\Models\Category $category */
            $category = $user->categories()->create(['title' => 'Youtube']);
            $category->feeds()->attach($feed);
        }
    }
}
