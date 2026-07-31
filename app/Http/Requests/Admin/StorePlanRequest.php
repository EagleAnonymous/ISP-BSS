<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:plans,name'],
            'speed' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'billing_cycle' => ['required', 'string', 'in:monthly,yearly'],
        ];
    }
}
