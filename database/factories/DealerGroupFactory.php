<?php

namespace Database\Factories;

use App\Models\DealerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealerGroupFactory extends Factory
{
    protected $model = DealerGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'code' => $this->faker->unique()->slug(1),
            'delay_minutes' => 0,
            'is_default' => false,
            'sort_order' => 1,
        ];
    }
}

