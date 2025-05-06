<?php

namespace App\Http\Requests\Api;

use App\Enums\Tickets\TicketPriority;
use App\Enums\Tickets\TicketType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'requester_id' => ['nullable', 'exists:clients,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'subject' => ['required', 'string', 'max:255'],
            'priority' => [Rule::enum(TicketPriority::class)],
            'type' => ['required', Rule::enum(TicketType::class)],
        ];
    }
}
