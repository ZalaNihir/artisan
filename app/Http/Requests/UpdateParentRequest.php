<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('parents.update') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'students' => ['sometimes', 'array'],
            'students.*' => ['array'],
            'students.*.selected' => ['sometimes', 'boolean'],
            'students.*.relationship' => ['nullable', 'string', 'max:50'],
            'students.*.is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
