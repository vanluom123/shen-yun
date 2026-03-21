<?php

namespace Database\Factories;

use App\Models\SessionTemplate;
use App\Models\TemplateSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TemplateSlot>
 */
class TemplateSlotFactory extends Factory
{
    protected $model = TemplateSlot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_template_id' => SessionTemplate::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'time' => fake()->time('H:i'),
            'default_capacity' => fake()->numberBetween(10, 100),
        ];
    }
}
