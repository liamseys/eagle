<?php

namespace App\Http\Controllers;

use App\Actions\Forms\SubmitForm;
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

    public function submit(Request $request, SubmitForm $submitForm)
    {
        $submitForm->handle($request);

        return redirect()->back()->with('status', __('Form was successfully submitted.'));
    }
}
