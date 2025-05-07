<?php

namespace App\Actions\Forms;

use App\Actions\Tickets\UpdateTicketStatus;
use App\Enums\HelpCenter\Forms\FormFieldType;
use App\Enums\Tickets\TicketStatus;
use App\Models\Client;
use App\Models\HelpCenter\Form;
use App\Models\HelpCenter\FormField;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
                $updateTicketStatus = app(UpdateTicketStatus::class);
                $updateTicketStatus->handle($ticket, TicketStatus::ON_HOLD, [], true);
            }

            return $ticket;
        });
    }

    private function getValidationRules(Form $form): array
    {
        return $form->fields->mapWithKeys(
            fn ($field) => [
                $field->name => collect($this->baseRules($field))
                    ->merge($this->dynamicRules($field))
                    ->unique()
                    ->values()
                    ->toArray(),
            ]
        )->toArray();
    }

    private function baseRules(FormField $field): array
    {
        return match (true) {
            $field->type === FormFieldType::CHECKBOX && $field->is_required => ['required', 'array', 'min:1'],
            $field->type === FormFieldType::CHECKBOX => ['array'],
            $field->is_required => ['required'],
            default => [],
        };
    }

    private function dynamicRules(FormField $field): Collection
    {
        return collect($field->validation_rules ?? [])
            ->map(fn ($rule) => isset($rule['value'])
                ? "{$rule['rule']}:{$rule['value']}"
                : $rule['rule']
            );
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
                'subject' => $form->name,
                'priority' => $form->default_ticket_priority,
                'type' => $form->default_ticket_type,
            ]);
        }

        return Ticket::create([
            'group_id' => $form->default_group_id,
            'subject' => $form->name,
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
                    'value' => $request->input($field->name),
                ];
            })->toArray()
        );
    }
}
