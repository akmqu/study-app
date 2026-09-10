<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'tutor';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_name' => trim((string) $this->input('student_name')),
            'subject' => trim((string) $this->input('subject')),
            'price' => $this->input('price') === '' || $this->input('price') === null
                ? null
                : $this->input('price'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}
