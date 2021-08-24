<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Feed;
use App\Models\User;
use Carbon\Carbon;
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
            'https://habr.com/ru/rss/best/daily/?fl=ru'
        ]);

        $this->subscribeYoutube($user, [
            'https://www.youtube.com/feeds/videos.xml?channel_id=UCld68syR8Wi-GY_n4CaoJGA',
        ]);
    }

    private function subscribeMain(User $user, array $urls): void
    {
        foreach ($urls as $url) {
            /** @var Feed $feed */
            $feed = Feed::factory()->create([
                'fetcher_key' => 'rss',
                'fetcher_feed_id' => $url,
                'parameters->url' => $url,
                'next_update_at' => Carbon::now(),
            ]);

            $user->subscribedFeeds()->syncWithoutDetaching($feed);
            $user->mainCategory->feeds()->syncWithoutDetaching($feed);
        }
    }

    private function subscribeYoutube(User $user, array $urls): void
    {
        /** @var \App\Models\Category $category */
        $category = $user->categories()->create(['name' => 'Youtube']);

        foreach ($urls as $url) {
            /** @var Feed $feed */
            $feed = Feed::factory()->create([
                'fetcher_key' => 'youtube_rss',
                'fetcher_feed_id' => $url,
                'parameters->url' => $url,
                'next_update_at' => Carbon::now(),
            ]);

            $user->subscribedFeeds()->syncWithoutDetaching($feed);
            $category->feeds()->syncWithoutDetaching($feed);
        }
    }
}
