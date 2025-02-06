<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'clients',
                'display_name' => 'Clients',
                'description' => 'Full management of clients',
            ],
            [
                'name' => 'tickets',
                'display_name' => 'Tickets',
                'description' => 'Full management of tickets',
            ],
            [
                'name' => 'hc-articles',
                'display_name' => '(Help Center) Articles',
                'description' => 'Full management of help center articles',
            ],
            [
                'name' => 'hc-categories',
                'display_name' => '(Help Center) Categories',
                'description' => 'Full management of help center categories',
            ],
            [
                'name' => 'hc-forms',
                'display_name' => '(Help Center) Forms',
                'description' => 'Full management of help center forms',
            ],
            [
                'name' => 'settings',
                'display_name' => 'Settings',
                'description' => 'Manage all settings, users, and groups',
            ],
        ];

        $i = 0;
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'description' => $permission['description'],
                'sort' => $i++,
            ]);
        }
    }
}
