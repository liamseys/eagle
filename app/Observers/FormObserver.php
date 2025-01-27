<?php

namespace App\Observers;

use App\Models\HelpCenter\Form;
use Illuminate\Support\Str;

class FormObserver
{
    /**
     * Handle the Form "creating" event.
     */
    public function creating(Form $form): void
    {
        if (auth()->check()) {
            $form->user_id = auth()->id();
        }

        if (isset($form->name)) {
            // Generate the base slug from the name
            $baseSlug = Str::slug($form->name);

            // Prepend an unique ID to ensure the slug is unique
            $uniqueSlug = uniqid().'-'.$baseSlug;

            // Assign the generated unique slug to the form
            $form->slug = $uniqueSlug;
        }

        $form->sort = Form::max('sort') + 1;
    }

    /**
     * Handle the Form "created" event.
     */
    public function created(Form $form): void
    {
        //
    }

    /**
     * Handle the Form "updated" event.
     */
    public function updated(Form $form): void
    {
        //
    }

    /**
     * Handle the Form "deleted" event.
     */
    public function deleted(Form $form): void
    {
        //
    }

    /**
     * Handle the Form "restored" event.
     */
    public function restored(Form $form): void
    {
        //
    }

    /**
     * Handle the Form "force deleted" event.
     */
    public function forceDeleted(Form $form): void
    {
        //
    }
}
