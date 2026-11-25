<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends StoreUserRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'email' => ['required', 'string', 'email', 'max:250', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}
