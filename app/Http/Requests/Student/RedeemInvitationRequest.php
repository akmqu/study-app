<?php

namespace App\Http\Requests\Student;

use App\Models\Invitation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RedeemInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'student';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Invitation::normalizeCode($this->input('code')),
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
            'code' => ['required', 'string', 'size:8'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $invitation = Invitation::findByCode($this->input('code'));
            $student = $this->user();

            if (! $invitation) {
                $validator->errors()->add('code', 'That invitation code was not found.');

                return;
            }

            if ($invitation->status === Invitation::STATUS_REVOKED) {
                $validator->errors()->add('code', 'That invitation code is no longer valid.');

                return;
            }

            if (! $invitation->isPending()) {
                $validator->errors()->add('code', 'That invitation code has already been used.');

                return;
            }

            if ($invitation->isExpired()) {
                $validator->errors()->add('code', 'That invitation code has expired.');

                return;
            }

            if ($invitation->student_id !== null && $invitation->student_id !== $student?->id) {
                $validator->errors()->add('code', 'That invitation code is not intended for your account.');
            }
        });
    }

    public function invitation(): Invitation
    {
        $invitation = Invitation::findByCode($this->validated('code'));

        if (! $invitation || ! $invitation->isAcceptableBy($this->user())) {
            abort(422, 'That invitation code is not available.');
        }

        return $invitation;
    }
}
