<?php

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\Client;
use App\Models\Group;
use App\Models\HelpCenter\Article;
use App\Models\HelpCenter\Category;
use App\Models\HelpCenter\Section;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $user = User::create([
            'name' => env('ADMIN_NAME', 'Admin'),
            'email' => env('ADMIN_EMAIL', 'admin@example.com'),
            'email_verified_at' => now(),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'admin')),
            'is_active' => true,
        ]);

        $group = Group::create([
            'name' => 'CES',
            'description' => 'Central Escalation Support (CES) team',
        ]);

        $user->groups()->attach($group);

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

        $category = Category::create([
            'icon' => 'heroicon-c-user-circle',
            'name' => 'Manage your account',
            'description' => 'Help with account settings, your password, and personal info.',
        ]);

        $category->sections()->createMany([
            [
                'name' => 'Manage basic settings',
                'description' => 'Update your name, email, and account preferences.',
            ],
            [
                'name' => 'Manage notifications',
                'description' => 'Control email alerts and in-app notification settings.',
            ],
        ]);

        Article::create([
            'author_id' => User::inRandomOrder()->first()->id,
            'section_id' => Section::inRandomOrder()->first()->id,
            'slug' => '68295013cee02-aut-quia-minus-animi-omnis-qui-non-commodi-exc',
            'title' => 'Aut quia minus animi omnis qui non commodi excepturi.',
            'description' => 'Omnis nihil rerum ipsa. Nostrum vitae sint sed. Quis eum nesciunt animi et vel expedita. Et ut ut facere officia accusamus eum nemo.',
            'body' => 'Earum eaque odio minus. Ea quis et id rerum. Sint quia excepturi itaque perspiciatis et. Sed qui eum saepe atque ducimus repellat.',
            'status' => ArticleStatus::PUBLISHED,
            'is_public' => true,
            'sort' => 1,
        ]);

        Client::create([
            'name' => 'Liam Seys',
            'email' => 'liam.seys@gmail.com',
            'phone' => '+32 489 00 00 00',
            'locale' => 'nl_BE',
            'timezone' => 'Europe/Brussels',
            'password' => Hash::make('password'),
        ]);

        $permissions = Permission::all();
        $user->permissions()->sync($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
