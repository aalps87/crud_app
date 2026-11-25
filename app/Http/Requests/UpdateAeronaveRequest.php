<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAeronaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prefixo' => ['required', 'string', 'max:10', Rule::unique('aeronaves', 'prefixo')->ignore($this->aeronave)],
            'modelo' => ['required', 'string', 'max:30'],
            'tripulacao' => ['required', 'integer', 'min:1', 'max:1000'],
            'data_fab' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'is_public' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ];
    }
}
