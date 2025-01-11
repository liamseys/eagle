<?php

namespace App\Observers;

use App\Models\FormField;

class FormFieldObserver
{
    /**
     * Handle the Form "creating" event.
     */
    public function creating(FormField $formField): void
    {
        $formField->sort = FormField::max('sort') + 1;
    }

    /**
     * Handle the FormField "created" event.
     */
    public function created(FormField $formField): void
    {
        //
    }

    /**
     * Handle the FormField "updated" event.
     */
    public function updated(FormField $formField): void
    {
        //
    }

    /**
     * Handle the FormField "deleted" event.
     */
    public function deleted(FormField $formField): void
    {
        //
    }

    /**
     * Handle the FormField "restored" event.
     */
    public function restored(FormField $formField): void
    {
        //
    }

    /**
     * Handle the FormField "force deleted" event.
     */
    public function forceDeleted(FormField $formField): void
    {
        //
    }
}
