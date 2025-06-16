<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'min:2', 'max:50', 'email:rfc', 'regex:/@.+\.[a-z]{2,}$/i'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => __('tle-validation.email.required'),
            'email.email' => __('tle-validation.email.email'),
            'email.regex' => __('tle-validation.email.regex'),
            'email.min' => __('tle-validation.email.min'),
            'email.max' => __('tle-validation.email.max'),
        ];
    }
}
