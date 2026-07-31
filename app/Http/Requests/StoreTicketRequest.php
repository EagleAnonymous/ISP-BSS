<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * The route's own `role:admin`/`role:technical_staff` middleware already
     * restricts who can reach this form, so no extra check is needed here —
     * same pattern as GenerateInvoicesRequest.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subscriber_id' => ['required', 'integer', 'exists:subscribers,id'],
            'category' => ['required', 'string', 'in:no_connection,slow_connection,billing_concern,installation_request,equipment_issue,other'],
            'subject' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
        ];
    }
}
