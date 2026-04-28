<?php

namespace App\Models\HelpCenter;

use App\Enums\HelpCenter\Articles\ArticleFeedbackValue;
use Database\Factories\HelpCenter\ArticleFeedbackFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleFeedback extends Model
{
    /** @use HasFactory<ArticleFeedbackFactory> */
    use HasFactory, HasUlids;

    protected $table = 'hc_article_feedback';

    public const UPDATED_AT = null;

    protected $fillable = [
        'article_id',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => ArticleFeedbackValue::class,
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
