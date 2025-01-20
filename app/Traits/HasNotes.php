<?php

namespace App\Traits;

use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasNotes
{
    /**
     * Get all the notes for the model.
     *
     * @return MorphMany
     */
    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }
}
