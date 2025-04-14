<?php

namespace App\Models\HelpCenter;

use App\Enums\HelpCenter\Articles\ArticleStatus;
use App\Models\User;
use App\Observers\HelpCenter\ArticleObserver;
use App\Traits\HasPublicScope;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Tags\HasTags;

#[ObservedBy([ArticleObserver::class])]
class Article extends Model
{
    /** @use HasFactory<\Database\Factories\HelpCenter\ArticleFactory> */
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
}
