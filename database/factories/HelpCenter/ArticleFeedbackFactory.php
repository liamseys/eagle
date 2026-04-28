<?php

namespace Database\Factories\HelpCenter;

use App\Enums\HelpCenter\Articles\ArticleFeedbackValue;
use App\Models\HelpCenter\Article;
use App\Models\HelpCenter\ArticleFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleFeedback>
 */
class ArticleFeedbackFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'value' => fake()->randomElement(ArticleFeedbackValue::cases()),
        ];
    }

    public function positive(): self
    {
        return $this->state(['value' => ArticleFeedbackValue::Positive]);
    }

    public function neutral(): self
    {
        return $this->state(['value' => ArticleFeedbackValue::Neutral]);
    }

    public function negative(): self
    {
        return $this->state(['value' => ArticleFeedbackValue::Negative]);
    }
}
