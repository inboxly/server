<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OriginalFeed;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OriginalFeedFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OriginalFeed::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'fetcher_key' => 'rss',
            'fetcher_feed_id' => $url = $this->faker->url,
            'parameters' => ['url' => $url],
            'name' => Str::ucfirst($this->faker->sentence(3)),
            'summary' => $this->faker->sentence,
            'url' => $this->faker->url,
            'image' => "https://placeimg.com/640/480/{$this->faker->unique()->uuid}",
            'author' => "{$this->faker->lastName} {$this->faker->firstName}",
            'language' => $this->faker->languageCode,
            'next_update_at' => null,
        ];
    }
}
