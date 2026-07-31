<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdjustmentRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:credit,charge'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}