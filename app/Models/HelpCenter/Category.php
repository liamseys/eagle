<?php

namespace App\Models\HelpCenter;

use App\Observers\HelpCenter\CategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[ObservedBy([CategoryObserver::class])]
class Category extends Model
{
    /** @use HasFactory<\Database\Factories\HelpCenter\CategoryFactory> */
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hc_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'icon',
        'name',
        'description',
        'sort',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Category has many sections.
     *
     * @return HasMany
     */
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    /**
     * Category has many articles.
     *
     * @return HasManyThrough
     */
    public function articles()
    {
        return $this->HasManyThrough(Article::class, Section::class);
    }
}
