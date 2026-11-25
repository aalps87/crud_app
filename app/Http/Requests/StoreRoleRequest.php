<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250', 'unique:roles,name'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do papel.',
            'name.max' => 'O nome pode ter no máximo 250 caracteres.',
            'name.unique' => 'Já existe um papel com este nome.',
            'permissions.required' => 'Selecione ao menos uma permissão.',
            'permissions.*.exists' => 'Permissão inválida.',
        ];
    }
}
