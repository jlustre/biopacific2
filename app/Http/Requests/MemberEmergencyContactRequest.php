<?php

namespace App\Http\Requests;

use App\Models\MemberEmergencyContact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberEmergencyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'string', 'max:64', Rule::in(MemberEmergencyContact::relationshipOptions())],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'address1' => ['nullable', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:32'],
            'zip' => ['nullable', 'string', 'max:20'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
