<?php

namespace Database\Seeders;

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Enums\HelpCenter\Forms\FormFieldType;
use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use App\Models\HelpCenter\Article;
use App\Models\HelpCenter\Category;
use App\Models\HelpCenter\Form;
use App\Models\HelpCenter\FormField;
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

        $this->seedForms($user, $basicSettings, $notifications);
    }

    /**
     * Seed help center forms aligned with the seeded articles.
     */
    private function seedForms(User $user, Section $basicSettings, Section $notifications): void
    {
        $forms = [
            [
                'section_id' => $basicSettings->id,
                'name' => 'Update profile information',
                'description' => 'Submit a request to update the personal details on your account.',
                'default_ticket_type' => TicketType::TASK,
                'default_ticket_priority' => TicketPriority::NORMAL,
                'fields' => [
                    [
                        'type' => FormFieldType::TEXT,
                        'label' => 'Full name',
                        'description' => 'The full name we should display on your account.',
                        'is_required' => true,
                        'validation_rules' => [
                            ['rule' => 'string', 'value' => null],
                            ['rule' => 'max', 'value' => '120'],
                        ],
                    ],
                    [
                        'type' => FormFieldType::EMAIL,
                        'label' => 'Current email',
                        'description' => 'The email address currently on file.',
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::TEXT,
                        'label' => 'Phone number',
                        'description' => 'Optional contact number, in international format.',
                        'is_required' => false,
                    ],
                    [
                        'type' => FormFieldType::TEXTAREA,
                        'label' => 'What would you like to update',
                        'description' => 'Describe what should change in your profile.',
                        'is_required' => true,
                    ],
                ],
            ],
            [
                'section_id' => $basicSettings->id,
                'name' => 'Reset password request',
                'description' => 'Tell us how to help you regain access to your account.',
                'default_ticket_type' => TicketType::PROBLEM,
                'default_ticket_priority' => TicketPriority::HIGH,
                'fields' => [
                    [
                        'type' => FormFieldType::EMAIL,
                        'label' => 'Account email',
                        'description' => 'The email address used to sign in.',
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::SELECT,
                        'label' => 'Account type',
                        'options' => [
                            'personal' => 'Personal',
                            'business' => 'Business',
                            'enterprise' => 'Enterprise',
                        ],
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::RADIO,
                        'label' => 'Reason for reset',
                        'options' => [
                            'forgot' => 'I forgot my password',
                            'compromised' => 'I think my account was compromised',
                            'rotating' => 'Routine password rotation',
                            'other' => 'Other',
                        ],
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::TEXTAREA,
                        'label' => 'Additional details',
                        'description' => 'Anything else our team should know.',
                        'is_required' => false,
                    ],
                ],
            ],
            [
                'section_id' => $basicSettings->id,
                'name' => 'Change email address',
                'description' => 'Move your account to a new primary email address.',
                'default_ticket_type' => TicketType::TASK,
                'default_ticket_priority' => TicketPriority::NORMAL,
                'fields' => [
                    [
                        'type' => FormFieldType::EMAIL,
                        'label' => 'Current email',
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::EMAIL,
                        'label' => 'New email',
                        'description' => 'You will receive a verification link at this address.',
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::TEXTAREA,
                        'label' => 'Reason for change',
                        'is_required' => false,
                    ],
                    [
                        'type' => FormFieldType::CHECKBOX,
                        'label' => 'Confirmation',
                        'options' => [
                            'has_access' => 'I confirm I have access to the new email address',
                            'understands' => 'I understand sign-in credentials will change',
                        ],
                        'is_required' => true,
                    ],
                ],
            ],
            [
                'section_id' => $basicSettings->id,
                'name' => 'Account deletion request',
                'description' => 'Permanently close your account. This cannot be undone.',
                'default_ticket_type' => TicketType::PROBLEM,
                'default_ticket_priority' => TicketPriority::URGENT,
                'fields' => [
                    [
                        'type' => FormFieldType::EMAIL,
                        'label' => 'Account email',
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::SELECT,
                        'label' => 'Reason for leaving',
                        'options' => [
                            'switching' => 'Switching to a different product',
                            'privacy' => 'Privacy concerns',
                            'unused' => 'No longer needed',
                            'cost' => 'Too expensive',
                            'other' => 'Other',
                        ],
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::TEXTAREA,
                        'label' => 'Feedback',
                        'description' => 'Help us improve — what could we have done better?',
                        'is_required' => false,
                    ],
                    [
                        'type' => FormFieldType::DATE,
                        'label' => 'Preferred deletion date',
                        'description' => 'Leave empty to delete as soon as possible.',
                        'is_required' => false,
                    ],
                    [
                        'type' => FormFieldType::CHECKBOX,
                        'label' => 'Acknowledgement',
                        'options' => [
                            'irreversible' => 'I understand this action is permanent and irreversible',
                            'data_loss' => 'I have downloaded any data I want to keep',
                        ],
                        'is_required' => true,
                    ],
                ],
            ],
            [
                'section_id' => $notifications->id,
                'name' => 'Notification preferences support',
                'description' => 'Need help configuring how and when you receive notifications? Tell us what you need.',
                'default_ticket_type' => TicketType::QUESTION,
                'default_ticket_priority' => TicketPriority::LOW,
                'fields' => [
                    [
                        'type' => FormFieldType::EMAIL,
                        'label' => 'Account email',
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::SELECT,
                        'label' => 'Preferred channel',
                        'options' => [
                            'email' => 'Email',
                            'desktop' => 'Desktop',
                            'both' => 'Both email and desktop',
                            'none' => 'No notifications',
                        ],
                        'is_required' => true,
                    ],
                    [
                        'type' => FormFieldType::CHECKBOX,
                        'label' => 'Notification categories',
                        'description' => 'Pick everything you want to keep receiving.',
                        'options' => [
                            'product_updates' => 'Product updates',
                            'newsletter' => 'Newsletter',
                            'marketing' => 'Marketing and promotions',
                            'security' => 'Security alerts',
                        ],
                        'is_required' => false,
                    ],
                    [
                        'type' => FormFieldType::DATETIME_LOCAL,
                        'label' => 'Snooze notifications until',
                        'description' => 'Optional — pause all alerts until a specific date and time.',
                        'is_required' => false,
                    ],
                    [
                        'type' => FormFieldType::TEXTAREA,
                        'label' => 'Additional context',
                        'is_required' => false,
                    ],
                ],
            ],
        ];

        foreach ($forms as $formData) {
            $fields = $formData['fields'];
            unset($formData['fields']);

            $form = Form::create(array_merge($formData, [
                'user_id' => $user->id,
                'settings' => [],
                'is_public' => true,
                'is_active' => true,
            ]));

            foreach ($fields as $field) {
                FormField::create(array_merge($field, [
                    'form_id' => $form->id,
                ]));
            }
        }
    }
}
