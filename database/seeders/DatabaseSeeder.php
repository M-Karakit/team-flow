<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionSeeder::class);

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('11223344')
        ]);
        $admin->assignRole('admin');

        $manager = User::factory()->create([
            'name'     => 'Manager User',
            'email'    => 'manager@teamflow.com',
            'password' => bcrypt('11223344'),
        ]);
        $manager->assignRole('manager');

        $teamLeader = User::factory()->create([
            'name'     => 'Team Leader User',
            'email'    => 'teamleader@teamflow.com',
            'password' => bcrypt('11223344'),
        ]);
        $teamLeader->assignRole('team-leader');

        $member = User::factory()->create([
            'name'     => 'Member User',
            'email'    => 'member@teamflow.com',
            'password' => bcrypt('11223344'),
        ]);
        $member->assignRole('member');
    }
}
