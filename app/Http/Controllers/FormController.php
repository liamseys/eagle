<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\HelpCenter\Form;
use App\Models\Ticket;
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

        foreach ($form->fields()->whereNotNull('validation_rules')->get() as $formField) {
            $validationRules[$formField->name] = collect($formField->validation_rules)
                ->map(fn ($ruleSet) => isset($ruleSet['value'])
                    ? "{$ruleSet['rule']}:{$ruleSet['value']}"
                    : $ruleSet['rule']
                )
                ->toArray();
        }

        $request->validate($validationRules);

        $client = Client::firstOrCreate(
            ['email' => $request->get('email')],
            ['name' => $request->get('name')]
        );

        $ticket = Ticket::create([
            'requester_id' => '',
        ]);

        return redirect()->back()->with('status', __('Form was successfully submitted.'));
    }
}
