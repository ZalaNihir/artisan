<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('students.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('students', 'email')->ignore($this->route('student'))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
            'teacher_id' => [Rule::requiredIf($this->user()?->hasRole('Admin')), 'nullable', 'integer', 'exists:users,id'],
            'dob' => ['required', 'date'],
            'gender' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }
}
