<?php

namespace Database\Seeders;

use App\Models\Comment\Comment;
use App\Models\Project\Project;
use App\Models\Task\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    // database/seeders/DatabaseSeeder.php

    public function run(): void
    {
        $this->call(RolesAndPermissionSeeder::class);

        // Fixed users — easy to login with
        $admin = User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@teamflow.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $manager = User::factory()->create([
            'name'     => 'Manager User',
            'email'    => 'manager@teamflow.com',
            'password' => bcrypt('password'),
        ]);
        $manager->assignRole('manager');

        $teamLeader = User::factory()->create([
            'name'     => 'Team Leader User',
            'email'    => 'teamleader@teamflow.com',
            'password' => bcrypt('password'),
        ]);
        $teamLeader->assignRole('team-leader');

        $member = User::factory()->create([
            'name'     => 'Member User',
            'email'    => 'member@teamflow.com',
            'password' => bcrypt('password'),
        ]);
        $member->assignRole('member');

        // Extra random members
        $members = User::factory(10)->create();
        $members->each(fn($user) => $user->assignRole('member'));

        // Create projects for manager
        $projects = Project::factory(5)->create([
            'owner_id' => $manager->id,
            'status'   => 'active',
        ]);

        $projects->each(function ($project) use ($manager, $teamLeader, $members) {
            // Attach manager as manager in pivot
            $project->members()->attach($manager->id, [
                'role'      => 'manager',
                'joined_at' => now(),
            ]);

            // Attach 5 random members
            $project->members()->attach(
                $members->random(5)->pluck('id')->toArray(),
                ['role' => 'member', 'joined_at' => now()]
            );

            // Create default labels
            $labels = collect([
                ['name' => 'Bug',         'color' => '#ef4444'],
                ['name' => 'Feature',     'color' => '#3b82f6'],
                ['name' => 'Urgent',      'color' => '#f97316'],
                ['name' => 'Design',      'color' => '#8b5cf6'],
                ['name' => 'Performance', 'color' => '#10b981'],
            ])->map(fn($label) => $project->labels()->create($label));

            // Create 10 tasks per project
            $tasks = Task::factory(10)->create([
                'project_id' => $project->id,
                'created_by' => $manager->id,
                'assigned_to' => $members->random()->id,
            ]);

            // Attach random labels to tasks
            $tasks->each(function ($task) use ($labels) {
                $task->labels()->sync(
                    $labels->random(rand(1, 3))->pluck('id')->toArray()
                );
            });

            // Add comments to tasks
            $tasks->each(function ($task) use ($members) {
                // 2 comments per task
                $comments = Comment::factory(2)->create([
                    'commentable_id'   => $task->id,
                    'commentable_type' => Task::class,
                    'user_id'          => $members->random()->id,
                ]);

                // 1 reply per comment
                $comments->each(function ($comment) use ($members) {
                    Comment::factory()->create([
                        'commentable_id'   => $comment->commentable_id,
                        'commentable_type' => $comment->commentable_type,
                        'user_id'          => $members->random()->id,
                        'parent_id'        => $comment->id,
                    ]);
                });
            });
        });
    }
}
