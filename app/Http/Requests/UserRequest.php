<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\UserRole;

final class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => $this->stringRule(),
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => $this->enumRule(UserRole::class),
        ];

        // Only require password for new users
        if ($this->isMethod('POST')) {
            $rules['password'] = ['required', 'min:8'];
        } elseif ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['password'] = ['nullable', 'min:8'];

            // Add the current user ID to the unique email rule for updates
            if ($this->user) {
                $rules['email'] = ['required', 'email', 'unique:users,email,' . $this->user->id];
            }
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email Address',
            'password' => 'Password',
            'role' => 'Role',
        ];
    }
}
