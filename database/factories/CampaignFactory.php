<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inputs' => $this->faker->rgbColorAsArray(),
            'rider' => $this->faker->url(),
            'reference_label' => $this->faker->word(),
            'dedication' => $this->faker->word(),
        ];
    }
}
