<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\odel=Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
