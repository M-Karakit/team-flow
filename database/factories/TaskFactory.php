<?php

namespace Database\Factories;

use App\Models\Project\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task\Task>
 */
class TaskFactory extends Factory
{
    protected $model = \App\Models\Task\Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'created_by'  => User::factory(),
            'assigned_to' => null,
            'title'       => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status'      => fake()->randomElement(['todo', 'in_progress', 'in_review', 'done']),
            'priority'    => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'due_date'    => fake()->optional()->dateTimeBetween('now', '+2 months'),
            'order'       => fake()->numberBetween(1, 100),
        ];
    }

    public function todo(): static
    {
        return $this->state(['status' => 'todo']);
    }

    public function done(): static
    {
        return $this->state(['status' => 'done']);
    }

    public function highPriority(): static
    {
        return $this->state(['priority' => 'high']);
    }

    public function overdue(): static
    {
        return $this->state([
            'due_date' => fake()->dateTimeBetween('-2 months', '-1 day'),
            'status'   => fake()->randomElement(['todo', 'in_progress']),
        ]);
    }
}
