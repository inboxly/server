<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Feed;
use App\Models\OriginalFeed;
use App\Models\User;
use Carbon\Carbon;
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
            'name' => Str::ucfirst($this->faker->sentence(3)),
            'subscribed_at' => fn() => $this->generateDate(),
        ];
    }

    /**
     * Generate date with random microseconds
     *
     * @return string
     */
    protected function generateDate(): string
    {
        /** @var Carbon $date */
        static $date;

        $date = $date ? $date->addMinute() : Carbon::now()->subWeeks(2);

        return vsprintf('%s.%s', [
            $date->format('Y-m-d H:i:s'),
            $this->faker->randomNumber(6), // random microseconds
        ]);
    }
}
