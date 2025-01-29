<?php

namespace App\Http\Controllers;

use App\Models\HelpCenter\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($locale, Form $form)
    {
        if (! $form->is_active) {
            abort(404);
        }

        return view('forms.show', [
            'locale' => $locale,
            'form' => $form,
        ]);
    }

    public function submit(Request $request)
    {
        $form = Form::findOrFail($request->get('form_id'));

        $validationRules = [];
        foreach ($form->formFields as $formField) {
            $validationRules[$formField->name] = $formField->validation_rules;
        }

        $validatedData = $request->validate($validationRules);

        dd($validatedData);
    }
}
