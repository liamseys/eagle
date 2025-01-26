<?php

namespace App\Http\Controllers;

use App\Models\Form;

class FormController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($locale, Form $form)
    {
        return view('forms.show', [
            'locale' => $locale,
            'form' => $form,
        ]);
    }
}
