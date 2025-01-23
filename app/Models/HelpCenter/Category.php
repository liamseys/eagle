<?php

namespace App\Models\HelpCenter;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'icon',
        'name',
        'description',
        'sort',
    ];

    /**
     * Category has many articles.
     *
     * @return HasMany
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
