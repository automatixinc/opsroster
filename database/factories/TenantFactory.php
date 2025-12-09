<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $attributes = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ];

        if (env('DB_DATABASE') === ':memory:') {
            $attributes['tenancy_db_name'] = ':memory:';
        }

        return $attributes;
    }
}
