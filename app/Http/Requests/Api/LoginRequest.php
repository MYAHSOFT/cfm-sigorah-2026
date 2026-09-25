<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string'],
            'password'    => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Le code animatrice est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }
}
