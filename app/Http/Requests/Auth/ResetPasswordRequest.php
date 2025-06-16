<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'string', 'min:2', 'max:50', 'email:rfc', 'regex:/@.+\.[a-z]{2,}$/i', 'exists:users,email'],
            'password' => ['required',
                Password::min(8)
                    ->max(15)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed',
            ],
            'password_confirmation' => ['required', 'string', 'min:8', 'max:15'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'token.required' => __('tle-validation.token.required'),
            'email.required' => __('tle-validation.email.required'),
            'email.email' => __('tle-validation.email.email'),
            'email.regex' => __('tle-validation.email.regex'),
            'email.min' => __('tle-validation.email.min'),
            'email.max' => __('tle-validation.email.max'),
            'email.exists' => __('tle-validation.email.exists'),
            'password.required' => __('tle-validation.password.required'),
            'password.min' => __('tle-validation.password.invalid'),
            'password.max' => __('tle-validation.password.invalid'),
            'password.letters' => __('tle-validation.password.invalid'),
            'password.mixed' => __('tle-validation.password.invalid'),
            'password.numbers' => __('tle-validation.password.invalid'),
            'password.symbols' => __('tle-validation.password.invalid'),
            'password_confirmation.required' => __('tle-validation.password_confirmation.required'),
            'password.confirmed' => __('tle-validation.password.confirmed'),
        ];
    }
}
