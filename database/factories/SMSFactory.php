<?php

namespace Database\Factories;

use App\Models\SMS;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template TModel of \App\Models\SMS
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class SMSFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = SMS::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from' => '0917825' . $this->faker->numberBetween(1000,9999),
            'to' => '0917301' . $this->faker->numberBetween(1000,9999),
            'message' => $this->faker->sentence()
        ];
    }
}
