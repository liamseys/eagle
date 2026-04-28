<?php

namespace App\Livewire;

use App\Enums\HelpCenter\Articles\ArticleFeedbackValue;
use App\Models\HelpCenter\Article;
use Livewire\Component;

class ArticleFeedback extends Component
{
    public Article $article;

    public ?string $submittedValue = null;

    public function mount(Article $article): void
    {
        $this->article = $article;
        $this->submittedValue = session()->get($this->sessionKey());
    }

    public function submit(string $value): void
    {
        if ($this->submittedValue !== null) {
            return;
        }

        $feedbackValue = ArticleFeedbackValue::tryFrom($value);

        if ($feedbackValue === null) {
            return;
        }

        $this->article->feedback()->create([
            'value' => $feedbackValue,
        ]);

        $this->submittedValue = $feedbackValue->value;

        session()->put($this->sessionKey(), $feedbackValue->value);
    }

    public function render()
    {
        return view('livewire.article-feedback', [
            'options' => ArticleFeedbackValue::cases(),
        ]);
    }

    private function sessionKey(): string
    {
        return "article_feedback.{$this->article->id}";
    }
}
