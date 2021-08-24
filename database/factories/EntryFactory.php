<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Entry;
use App\Models\Feed;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Entry::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        /** @var string $text */
        $text = $this->faker->paragraphs(rand(3, 20), true);

        return [
            'feed_id' => fn() => Feed::factory()->create()->getKey(),
            'external_id' => $this->faker->unique()->uuid,
            'id' => fn(array $attrs) => sha1(join('', [$attrs['feed_id'], $this->faker->unique()->uuid])),
            'name' => $this->faker->unique()->word,
            'summary' => Str::limit($text, 300),
            'content' => $text,
            'url' => fn() => $this->faker->unique()->url,
            'image' => "https://placeimg.com/640/480/{$this->faker->unique()->uuid}",
            'author' => [
                'name' => $this->faker->userName,
                'image' => $this->faker->imageUrl(100, 100),
                'url' => $this->faker->url,
            ],
            'created_at' => fn() => $this->generateDate(),
            'updated_at' => fn(array $attr) => $attr['created_at'] ?? fn() => $this->generateDate(),
        ];
    }

    /**
     * @return $this
     */
    public function today(): self
    {
        return $this->state([
            'created_at' => fn() => $this->generateDateToday()
        ]);
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

    /**
     * Generate today date with random microseconds
     *
     * @return string
     */
    protected function generateDateToday(): string
    {
        /** @var Carbon $date */
        static $date;

        $date = $date ? $date->addMinute() : Carbon::now();

        return vsprintf('%s.%s', [
            $date->format('Y-m-d H:i:s'),
            $this->faker->randomNumber(6), // random microseconds
        ]);
    }
}
