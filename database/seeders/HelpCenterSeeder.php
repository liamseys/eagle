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
        $user = User::first() ?? User::factory()->create();

        $category = Category::updateOrCreate(
            ['name' => 'Manage your account'],
            [
                'icon' => 'heroicon-o-user-circle',
                'description' => 'Help with account settings, your password, and personal info.',
            ]
        );

        $basicSettings = Section::updateOrCreate(
            ['category_id' => $category->id, 'name' => 'Manage basic settings'],
            ['description' => 'Update your name, email, and account preferences.']
        );

        $notifications = Section::updateOrCreate(
            ['category_id' => $category->id, 'name' => 'Manage notifications'],
            ['description' => 'Control email alerts and in-app notification settings.']
        );

        $articles = [
            // Basic Settings
            [
                'section_id' => $basicSettings->id,
                'title' => 'Updating your profile information',
                'description' => 'Learn how to keep your personal details up to date.',
                'body' => 'To update your profile information, navigate to the Settings page from your dashboard. Here you can change your display name, profile picture, and other personal details. Don’t forget to click "Save Changes" at the bottom of the page.',
            ],
            [
                'section_id' => $basicSettings->id,
                'title' => 'How to change your password',
                'description' => 'A step-by-step guide to updating your account password.',
                'body' => 'For security reasons, we recommend changing your password regularly. Go to Settings > Security, enter your current password, and then provide your new password twice. Your new password must be at least 8 characters long and include a mix of letters, numbers, and symbols.',
            ],
            [
                'section_id' => $basicSettings->id,
                'title' => 'Changing your primary email address',
                'description' => 'How to update the email address associated with your account.',
                'body' => 'You can change your primary email address under Account Settings. After entering your new email, we will send a verification link to that address. You must click the link in the email to confirm the change before it takes effect.',
            ],
            [
                'section_id' => $basicSettings->id,
                'title' => 'Managing your account language',
                'description' => 'Choose your preferred language for the application interface.',
                'body' => 'Our application supports multiple languages. To change your preference, go to Settings > Preferences and select your desired language from the dropdown menu. The interface will update immediately once you save your choice.',
            ],
            [
                'section_id' => $basicSettings->id,
                'title' => 'Deleting your account',
                'description' => 'Information on how to permanently close your account.',
                'body' => 'We are sorry to see you go. If you wish to delete your account, please visit the bottom of the Account Settings page. Please note that this action is permanent and all your data will be irrecoverable. We recommend downloading any important information before proceeding.',
            ],

            // Notifications
            [
                'section_id' => $notifications->id,
                'title' => 'Customizing email notifications',
                'description' => 'Control which emails you receive from us.',
                'body' => 'You can manage which email alerts you receive by visiting Settings > Notifications. Here you can toggle notifications for newsletters, account activity, and marketing updates. Simply switch the toggles to your preference and changes are saved automatically.',
            ],
            [
                'section_id' => $notifications->id,
                'title' => 'Configuring in-app alerts',
                'description' => 'Stay informed with real-time updates while using the app.',
                'body' => 'In-app alerts keep you updated on new messages and system status. You can customize these in the Notification settings. You can choose to see them as banners or just as badges on the notification bell icon.',
            ],
            [
                'section_id' => $notifications->id,
                'title' => 'Setting up desktop notifications',
                'description' => 'Receive alerts on your computer even when the app is in the background.',
                'body' => 'To enable desktop notifications, go to Settings > Notifications and click "Enable Browser Notifications." Your browser will prompt you to grant permission. Once allowed, you will receive real-time alerts directly on your desktop.',
            ],
            [
                'section_id' => $notifications->id,
                'title' => 'Managing marketing preferences',
                'description' => 'Decide how you want to hear about our latest features.',
                'body' => 'We occasionally send updates about new features and promotions. If you prefer not to receive these, you can opt-out in the Notifications section of your settings. You will still receive essential account-related emails even if you opt-out of marketing.',
            ],
            [
                'section_id' => $notifications->id,
                'title' => 'Snoozing all notifications',
                'description' => 'How to temporarily silence all alerts.',
                'body' => 'If you need some focus time, you can snooze all notifications. Go to the notification bell, click the gear icon, and select a duration for the "Do Not Disturb" mode. All alerts will be held until the timer expires or you manually resume them.',
            ],
        ];

        foreach ($articles as $article) {
            Article::create(array_merge($article, [
                'author_id' => $user->id,
                'status' => ArticleStatus::PUBLISHED,
                'is_public' => true,
            ]));
        }
    }
}
