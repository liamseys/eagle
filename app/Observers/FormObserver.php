<?php

namespace App\Observers;

use App\Models\Form;

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
