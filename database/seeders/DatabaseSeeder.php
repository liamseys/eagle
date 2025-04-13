<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Permission;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        $group = Group::factory()->create([
            'name' => 'CES',
            'description' => 'Central Escalation Support (CES) team',
        ]);

        $user->groups()->attach($group);

        $this->call([
            PermissionSeeder::class,
            HelpCenterSeeder::class,
            ClientSeeder::class,
        ]);

        $permissions = Permission::all();
        $user->permissions()->sync($permissions);
    }
}
