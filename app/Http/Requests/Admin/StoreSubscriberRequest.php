<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreSubscriberRequest extends FormRequest
{
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'contact' => ['nullable', 'string', 'max:30'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
            'joined_date' => ['required', 'date'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
