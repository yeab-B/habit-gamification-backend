<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'points' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
