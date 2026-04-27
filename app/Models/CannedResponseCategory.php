<?php

namespace App\Models;

use Database\Factories\CannedResponseCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CannedResponseCategory extends Model
{
    /** @use HasFactory<CannedResponseCategoryFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * A category has many canned responses.
     */
    public function cannedResponses(): HasMany
    {
        return $this->hasMany(CannedResponse::class);
    }
}
