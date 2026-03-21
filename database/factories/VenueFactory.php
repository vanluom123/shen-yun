<?php

namespace Database\Factories;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

class VenueFactory extends Factory
{
    protected $model = Venue::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->address(),
            'timezone' => 'Asia/Ho_Chi_Minh',
        ];
    }
}
