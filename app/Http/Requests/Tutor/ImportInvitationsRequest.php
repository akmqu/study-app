<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;

class ImportInvitationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'tutor';
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'extensions:xlsx',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Choose an Excel file.',
            'file.extensions' => 'The file must be an .xlsx Excel file.',
            'file.max' => 'The Excel file may not be larger than 5 MB.',
        ];
    }
}