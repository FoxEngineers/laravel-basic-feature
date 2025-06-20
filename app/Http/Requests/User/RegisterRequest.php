<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        $nameRegex = '/^[a-zA-Z\' -]+$/';
        $nameRules = ['required', 'string', 'min:2', 'max:50', "regex:$nameRegex"];

        return [
            'first_name' => $nameRules,
            'last_name' => $nameRules,
            'email' => ['required', 'string', 'min:2', 'max:50', 'email:rfc', 'regex:/@.+\.[a-z]{2,}$/i'],
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

    public function messages(): array
    {
        return [
            'first_name.required' => __('tle-validation.first_name.required'),
            'first_name.regex' => __('tle-validation.first_name.regex'),
            'first_name.min' => __('tle-validation.first_name.min'),
            'first_name.max' => __('tle-validation.first_name.max'),
            'last_name.required' => __('tle-validation.last_name.required'),
            'last_name.regex' => __('tle-validation.last_name.regex'),
            'last_name.min' => __('tle-validation.last_name.min'),
            'last_name.max' => __('tle-validation.last_name.max'),
            'email.required' => __('tle-validation.email.required'),
            'email.email' => __('tle-validation.email.email'),
            'email.regex' => __('tle-validation.email.regex'),
            'email.min' => __('tle-validation.email.min'),
            'email.max' => __('tle-validation.email.max'),
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
