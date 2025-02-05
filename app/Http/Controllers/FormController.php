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

        $createClient = $form->settings['create_client'] ?? false;
        $nameFieldKey = $form->settings['client_name_field'] ?? null;
        $emailFieldKey = $form->settings['client_email_field'] ?? null;

        $nameFieldExists = $nameFieldKey && $form->fields()->where('name', $nameFieldKey)->exists();
        $emailFieldExists = $emailFieldKey && $form->fields()->where('name', $emailFieldKey)->exists();

        if ($createClient && $nameFieldExists && $emailFieldExists) {
            $client = Client::firstOrCreate(
                ['email' => $request->get($emailFieldKey)],
                ['name' => $request->get($nameFieldKey)]
            );

            $client->tickets()->create([
                'group_id' => $form->default_group_id,
                'subject' => 'Testing',
                'priority' => $form->default_ticket_priority,
                'type' => $form->default_ticket_type,
            ]);
        } else {
            Ticket::create([
                'group_id' => $form->default_group_id,
                'subject' => 'Testing',
                'priority' => $form->default_ticket_priority,
                'type' => $form->default_ticket_type,
            ]);
        }

        return redirect()->back()->with('status', __('Form was successfully submitted.'));
    }
}
