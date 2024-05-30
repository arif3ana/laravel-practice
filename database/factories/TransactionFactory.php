<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Transaction::class;
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'category_id' => $this->faker->randomElement([1, 2, 3, 4]),
            'description' => $this->faker->sentence(),
            'transaction_date' => $this->faker->date(),
        ];
    }
}
