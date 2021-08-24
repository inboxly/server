<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Entry;
use App\Models\ReadState;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadStateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReadState::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'entry_id' => fn() => Entry::factory()->create()->getKey(),
            'feed_id' => fn(array $attrs) => $attrs['feed_id'],
            'read_at' => null,
        ];
    }
}
