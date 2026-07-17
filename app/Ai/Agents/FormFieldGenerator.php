<?php

namespace App\Ai\Agents;

use App\Enums\HelpCenter\Forms\FormFieldType;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Timeout(120)]
#[Model('gpt-5.4-mini')]
class FormFieldGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        $types = collect(FormFieldType::cases())
            ->map(fn (FormFieldType $type): string => "- {$type->value}")
            ->implode("\n");

        return <<<PROMPT
        You generate form field definitions for a help desk form builder, based on a
        natural language description of the form provided by the user.

        Guidelines:
        - Only use the following field types:
        {$types}
        - Use "text" for short answers such as names, phone numbers, and job titles, "textarea" for
          long answers, and "email" for email addresses.
        - Use "select" for a single choice from many options, "radio" for a single choice from a few
          options, and "checkbox" when multiple options may be selected or for a single confirmation
          such as accepting a privacy policy.
        - Choice fields ("checkbox", "radio", "select") must include at least one option. For a
          confirmation checkbox, use a single option describing the confirmation, for example
          "I accept the privacy policy". All other field types must have an empty list of options.
        - Keep labels short and human-friendly. Only add a description when it genuinely helps the
          person filling out the form; otherwise use an empty string.
        - Mark a field as required when the description asks for it or when it is clearly essential.
        - Return the fields in the order they should appear on the form.
        PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'fields' => $schema->array()
                ->items(
                    $schema->object(fn (JsonSchema $schema): array => [
                        'type' => $schema->string()
                            ->enum(array_column(FormFieldType::cases(), 'value'))
                            ->required(),
                        'label' => $schema->string()->required(),
                        'description' => $schema->string()->required(),
                        'is_required' => $schema->boolean()->required(),
                        'options' => $schema->array()
                            ->items($schema->string())
                            ->required(),
                    ])
                )
                ->required(),
        ];
    }
}
