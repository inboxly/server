<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OriginalEntry;
use App\Models\OriginalFeed;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OriginalEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OriginalEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $text = $this->faker->paragraphs(rand(3, 20), true);

        return [
            'original_feed_id' => fn() => OriginalFeed::factory()->create()->getKey(),
            'external_id' => $this->faker->unique()->uuid,
            'title' => Str::ucfirst($this->faker->sentence),
            'description' => Str::limit($text, 300),
            'text' => $text,
            'link' => $this->faker->url,
            'image' => "https://placeimg.com/640/480/{$this->faker->unique()->uuid}",
            'author' => [
                'name' => $this->faker->userName,
                'image' => $this->faker->imageUrl(100, 100),
                'link' => $this->faker->url,
            ],
            'created_at' => fn() => $this->generateDate(),
            'updated_at' => fn(array $attr) => $attr['created_at'] ?? fn() => $this->generateDate(),
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
