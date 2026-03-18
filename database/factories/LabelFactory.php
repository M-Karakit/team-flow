<?php

namespace Database\Factories;

use App\Models\Project\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Label\Label>
 */
class LabelFactory extends Factory
{
    protected $model = \App\Models\Label\Label::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name'       => fake()->randomElement([
                'Bug', 'Feature', 'Urgent', 'Design',
                'Performance', 'Security', 'Documentation'
            ]),
            'color' => fake()->hexColor(),
        ];
    }
}
