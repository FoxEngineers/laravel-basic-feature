<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'min:2', 'max:50', 'email:rfc', 'regex:/@.+\.[a-z]{2,}$/i'],
            'password' => ['required', 'string', 'min:2', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('tle-validation.email.required'),
            'email.email' => __('tle-validation.email.email'),
            'email.regex' => __('tle-validation.email.regex'),
            'email.min' => __('tle-validation.email.min'),
            'email.max' => __('tle-validation.email.max'),
            'password.required' => __('tle-validation.password.required'),
            'password.min' => __('tle-validation.password.min'),
            'password.max' => __('tle-validation.password.max'),
        ];
    }
}
