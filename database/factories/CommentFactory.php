<?php

namespace Database\Factories;

use App\Models\Task\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = \App\models\Comment\Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'body'             => fake()->paragraph(),
            'parent_id'        => null,
            'commentable_id'   => Task::factory(),
            'commentable_type' => Task::class,
        ];
    }
}
