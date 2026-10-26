<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('teachers.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $employeeId = $this->input('employee_id');

        if (is_string($employeeId)) {
            $employeeId = trim($employeeId);
            $this->merge(['employee_id' => $employeeId === '' ? null : $employeeId]);
        }
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_id')],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:16000'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.unique' => __('This employee ID is already in use.'),
            'employee_id.max' => __('The employee ID may not be greater than 50 characters.'),
            'phone.max' => __('The phone number may not be greater than 30 characters.'),
            'address.max' => __('The address may not be greater than 16,000 characters.'),
            'qualification.max' => __('The qualification may not be greater than 255 characters.'),
            'joining_date.date' => __('Enter a valid joining date.'),
        ];
    }
}
