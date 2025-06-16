<?php

use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Validator;

it('passes validation with valid email', function () {
    $request = new ForgotPasswordRequest;

    $validator = Validator::make(
        ['email' => 'test@example.com'],
        $request->rules(),
        $request->messages()
    );

    expect($validator->fails())->toBeFalse();
});

it('requires email field', function () {
    $request = new ForgotPasswordRequest;

    $validator = Validator::make(
        ['email' => ''],
        $request->rules(),
        $request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Email is required.'));
});

it('validates email format', function () {
    $request = new ForgotPasswordRequest;

    $validator = Validator::make(
        ['email' => 'invalid-email'],
        $request->rules(),
        $request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Please enter a valid email address.'));
});

it('requires domain in email', function () {
    $request = new ForgotPasswordRequest;

    $validator = Validator::make(
        ['email' => 'test@'],
        $request->rules(),
        $request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Please enter a valid email address.'));
});

it('enforces minimum email length', function () {
    $request = new ForgotPasswordRequest;

    $validator = Validator::make(
        ['email' => 'a'],
        $request->rules(),
        $request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toContain(__('Email must be at least 2 characters.'));
});

it('enforces maximum email length', function () {
    $request = new ForgotPasswordRequest;
    $longEmail = str_repeat('a', 40).'@'.str_repeat('b', 10).'.com';

    $validator = Validator::make(
        ['email' => $longEmail],
        $request->rules(),
        $request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Email cannot exceed 50 characters.'));
});

it('validates regex pattern for email', function () {
    $request = new ForgotPasswordRequest;

    $validator = Validator::make(
        ['email' => 'test@example'],
        $request->rules(),
        $request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Please enter a valid email address.'));
});

it('rejects email with spaces', function () {
    $request = new ForgotPasswordRequest;

    $validator = Validator::make(
        ['email' => 'test user@example.com'],
        $request->rules(),
        $request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Please enter a valid email address.'));
});
