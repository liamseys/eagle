<?php

namespace App\Observers;

use App\Models\HelpCenter\FormField;
use Illuminate\Support\Str;

class FormFieldObserver
{
    /**
     * Handle the Form "creating" event.
     */
    public function creating(FormField $formField): void
    {
        if (isset($formField->label)) {
            // Generate the base name from the label
            $baseName = Str::slug($formField->label, '_');

            // Append a random number to ensure uniqueness
            $uniqueName = $baseName.'_'.rand(1000, 9999);

            // Assign the generated unique name to the form field
            $formField->name = $uniqueName;
        }

        $formField->sort = FormField::where('form_id', $formField->form_id)->max('sort') + 1;
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
