<?php

namespace Database\Seeders;

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\HelpCenter\Article;
use App\Models\HelpCenter\Category;
use App\Models\HelpCenter\Section;
use App\Models\User;
use Illuminate\Database\Seeder;

class HelpCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = Category::factory()->create([
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

        Article::factory()->create([
            'author_id' => User::inRandomOrder()->first()->id,
            'section_id' => Section::inRandomOrder()->first()->id,
            'status' => ArticleStatus::PUBLISHED,
            'is_public' => true,
        ]);
    }
}
