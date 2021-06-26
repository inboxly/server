<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Feed;
use App\Models\OriginalFeed;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'user_id' => fn() => User::factory()->create()->getKey(),
            'original_feed_id' => fn() => OriginalFeed::factory()->create()->getKey(),
            'title' => Str::ucfirst($this->faker->sentence(3)),
        ];
    }
}
