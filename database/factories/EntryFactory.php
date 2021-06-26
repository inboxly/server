<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Entry;
use App\Models\Feed;
use App\Models\OriginalEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'user_id' => fn() => User::factory()->create()->getKey(),
            'feed_id' => fn() => Feed::factory()->create()->getKey(),
            'original_entry_id' => fn() => OriginalEntry::factory()->create()->getKey(),
            'read_at' => null,
            'saved_at' => null,
        ];
    }

    /**
     * Add read state
     *
     * @return \Database\Factories\EntryFactory
     */
    public function read(): EntryFactory
    {
        return $this->state([
            'read_at' => fn() => $this->generateDate(),
        ]);
    }

    /**
     * Add saved state
     *
     * @return \Database\Factories\EntryFactory
     */
    public function saved(): EntryFactory
    {
        return $this->state([
            'saved_at' => fn() => $this->generateDate(),
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
}
