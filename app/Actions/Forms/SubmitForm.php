<?php

namespace App\Actions\Forms;

use App\Enums\Tickets\TicketStatus;
use App\Models\Client;
use App\Models\HelpCenter\Form;
use App\Models\Ticket;
use App\Notifications\TicketEscalationRequired;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SubmitForm
{
    public function handle(Request $request)
    {
        $form = Form::findOrFail($request->get('form_id'));

        $validationRules = $this->getValidationRules($form);
        $request->validate($validationRules);

        return DB::transaction(function () use ($request, $form) {
            $ticket = $this->createTicketBasedOnClientSettings($request, $form);
            $this->attachTicketFields($ticket, $form, $request);

            if (array_key_exists('require_escalation', $form->settings) && $form->settings['require_escalation'] === true) {
                $ticket->update(['status' => TicketStatus::ON_HOLD]);

                if ($ticket->requester) {
                    $notificationDelay = now()->addMinutes(10);

                    $ticket->requester->notify((new TicketEscalationRequired($ticket))->delay($notificationDelay));
                }
            }

            return $ticket;
        });
    }

    private function getValidationRules(Form $form): array
    {
        $validationRules = [];

        foreach ($form->fields()->whereNotNull('validation_rules')->get() as $formField) {
            $validationRules[$formField->name] = collect($formField->validation_rules)
                ->map(fn ($ruleSet) => isset($ruleSet['value'])
                    ? "{$ruleSet['rule']}:{$ruleSet['value']}"
                    : $ruleSet['rule']
                )
                ->toArray();
        }

        return $validationRules;
    }

    private function createTicketBasedOnClientSettings(Request $request, Form $form): Ticket
    {
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

            return $client->tickets()->create([
                'group_id' => $form->default_group_id,
                'subject' => 'Testing',
                'priority' => $form->default_ticket_priority,
                'type' => $form->default_ticket_type,
            ]);
        }

        return Ticket::create([
            'group_id' => $form->default_group_id,
            'subject' => 'Testing',
            'priority' => $form->default_ticket_priority,
            'type' => $form->default_ticket_type,
        ]);
    }

    private function attachTicketFields(Ticket $ticket, Form $form, Request $request): void
    {
        $ticket->fields()->createMany(
            $form->fields->map(function ($field) use ($request) {
                return [
                    'form_field_id' => $field->id,
                    'value' => $request->get($field->name),
                ];
            })->toArray()
        );
    }
}
