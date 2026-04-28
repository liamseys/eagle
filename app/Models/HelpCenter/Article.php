<?php

namespace App\Models\HelpCenter;

use App\Enums\HelpCenter\Articles\ArticleFeedbackValue;
use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\User;
use App\Observers\HelpCenter\ArticleObserver;
use App\Traits\HasPublicScope;
use Database\Factories\HelpCenter\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Tags\HasTags;

#[ObservedBy([ArticleObserver::class])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasPublicScope, HasTags, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hc_articles';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'author_id',
        'section_id',
        'slug',
        'title',
        'description',
        'body',
        'status',
        'sort',
        'is_public',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The author of the article.
     *
     * @return BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * The section of the article.
     *
     * @return BelongsTo
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * The category of the article.
     *
     * @return HasOneThrough
     */
    public function category()
    {
        return $this->hasOneThrough(Category::class, Section::class, 'id', 'id', 'section_id', 'category_id');
    }

    /**
     * Scope a query to only include draft articles.
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('status', ArticleStatus::DRAFT);
    }

    /**
     * Scope a query to only include published articles.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ArticleStatus::PUBLISHED);
    }

    /**
     * Get the label attribute of the instance.
     */
    public function getLabelAttribute(): string
    {
        return $this->title;
    }

    /**
     * Feedback submitted for the article.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(ArticleFeedback::class);
    }

    /**
     * Eager-load aggregated feedback counts as `feedback_{value}_count` attributes.
     */
    public function scopeWithFeedbackCounts(Builder $query): void
    {
        $query->withCount([
            'feedback as feedback_positive_count' => fn (Builder $q) => $q->where('value', ArticleFeedbackValue::Positive),
            'feedback as feedback_neutral_count' => fn (Builder $q) => $q->where('value', ArticleFeedbackValue::Neutral),
            'feedback as feedback_negative_count' => fn (Builder $q) => $q->where('value', ArticleFeedbackValue::Negative),
        ]);
    }

    /**
     * Counts of feedback by value, lazily loaded from the relation if not pre-aggregated.
     *
     * @return array{positive:int, neutral:int, negative:int, total:int}
     */
    public function feedbackCounts(): array
    {
        $attributes = $this->getAttributes();

        $hasPreloadedCounts = array_key_exists('feedback_positive_count', $attributes)
            && array_key_exists('feedback_neutral_count', $attributes)
            && array_key_exists('feedback_negative_count', $attributes);

        if ($hasPreloadedCounts) {
            $positive = (int) $attributes['feedback_positive_count'];
            $neutral = (int) $attributes['feedback_neutral_count'];
            $negative = (int) $attributes['feedback_negative_count'];
        } else {
            $counts = $this->feedback()
                ->selectRaw('value, COUNT(*) as aggregate')
                ->groupBy('value')
                ->pluck('aggregate', 'value');

            $positive = (int) ($counts[ArticleFeedbackValue::Positive->value] ?? 0);
            $neutral = (int) ($counts[ArticleFeedbackValue::Neutral->value] ?? 0);
            $negative = (int) ($counts[ArticleFeedbackValue::Negative->value] ?? 0);
        }

        return [
            'positive' => $positive,
            'neutral' => $neutral,
            'negative' => $negative,
            'total' => $positive + $neutral + $negative,
        ];
    }

    /**
     * Aggregate sentiment band based on feedback distribution.
     *
     * @return 'good'|'mixed'|'poor'|null
     */
    public function feedbackSentiment(): ?string
    {
        $counts = $this->feedbackCounts();

        if ($counts['total'] === 0) {
            return null;
        }

        $positivePct = ($counts['positive'] / $counts['total']) * 100;
        $negativePct = ($counts['negative'] / $counts['total']) * 100;

        return match (true) {
            $positivePct >= 70 => 'good',
            $negativePct >= 50 => 'poor',
            default => 'mixed',
        };
    }
}
