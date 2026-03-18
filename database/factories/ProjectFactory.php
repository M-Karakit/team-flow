<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = \App\Models\Project\Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->catchPhrase(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['active', 'on_hold', 'archived']),
            'due_date' => fake()->dateTimeBetween('now', '+6 months'),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function archived(): static
    {
        return $this->state(['status' => 'archived']);
    }

    public function overdue(): static
    {
        return $this->state([
            'due_date' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }
}
