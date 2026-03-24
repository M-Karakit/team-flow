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

        // ✅ firstOrCreate prevents duplicates on re-run
        $admin = User::firstOrCreate(
            ['email' => 'admin@teamflow.com'],
            [
                'name'              => 'Admin User',
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $manager = User::firstOrCreate(
            ['email' => 'manager@teamflow.com'],
            [
                'name'              => 'Manager User',
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->syncRoles(['manager']);

        $teamLeader = User::firstOrCreate(
            ['email' => 'teamleader@teamflow.com'],
            [
                'name'              => 'Team Leader User',
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $teamLeader->syncRoles(['team-leader']);

        $member = User::firstOrCreate(
            ['email' => 'member@teamflow.com'],
            [
                'name'              => 'Member User',
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $member->syncRoles(['member']);

        // Only create extra data if no projects exist yet
        if (Project::count() === 0) {
            $members = User::factory(10)->create();
            $members->each(fn($user) => $user->assignRole('member'));

            $projects = Project::factory(5)->create([
                'owner_id' => $manager->id,
                'status'   => 'active',
            ]);

            $projects->each(function ($project) use ($manager, $teamLeader, $members) {
                $project->members()->attach($manager->id, [
                    'role'      => 'manager',
                    'joined_at' => now(),
                ]);

                $project->members()->attach(
                    $members->random(5)->pluck('id')->toArray(),
                    ['role' => 'member', 'joined_at' => now()]
                );

                $labels = collect([
                    ['name' => 'Bug',         'color' => '#ef4444'],
                    ['name' => 'Feature',     'color' => '#3b82f6'],
                    ['name' => 'Urgent',      'color' => '#f97316'],
                    ['name' => 'Design',      'color' => '#8b5cf6'],
                    ['name' => 'Performance', 'color' => '#10b981'],
                ])->map(fn($label) => $project->labels()->create($label));

                $tasks = Task::factory(10)->create([
                    'project_id'  => $project->id,
                    'created_by'  => $manager->id,
                    'assigned_to' => $members->random()->id,
                ]);

                $tasks->each(function ($task) use ($labels) {
                    $task->labels()->sync(
                        $labels->random(rand(1, 3))->pluck('id')->toArray()
                    );
                });

                $tasks->each(function ($task) use ($members) {
                    $comments = Comment::factory(2)->create([
                        'commentable_id'   => $task->id,
                        'commentable_type' => Task::class,
                        'user_id'          => $members->random()->id,
                    ]);

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
}
