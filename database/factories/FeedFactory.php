<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feed::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'fetcher_key' => 'rss',
            'fetcher_feed_id' => fn() => $this->faker->unique()->url,
            'id' => fn(array $attrs) => sha1(join('', [$attrs['fetcher_key'], $attrs['fetcher_feed_id']])),
            'parameters' => fn (array $attrs) => ['url' => $attrs['fetcher_feed_id']],
            'url' => fn() => $this->faker->unique()->url,
        ];
    }
}
