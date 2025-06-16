<?php

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;

it('validates login request rules with valid data', function () {
    $rules = (new LoginRequest)->rules();

    $data = [
        'email' => 'john.doe@example.com',
        'password' => 'password123',
    ];

    $validator = Validator::make($data, $rules);

    expect($validator->passes())->toBeTrue();
});

it('validates login request rules with missing email', function () {
    $request = new LoginRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'password' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and(data_get($validator->errors()->get('email'), 0))
        ->toContain(__('Email is required.'));
});

it('validates login request rules with invalid email format', function () {
    $request = new LoginRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'email' => 'invalid-email',
        'password' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and(data_get($validator->errors()->get('email'), 0))
        ->toContain(__('Please enter a valid email address.'));
});

it('validates login request rules with missing password', function () {
    $request = new LoginRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'email' => 'john.doe@example.com',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password'])
        ->and(data_get($validator->errors()->get('password'), 0))
        ->toContain(__('Password is required.'));
});

it('validates login request rules with non-string password', function () {
    $request = new LoginRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'email' => 'john.doe@example.com',
        'password' => 12345, // Non-string password
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password'])
        ->and($validator->errors()->get('password'))->toContain(__('validation.string', ['attribute' => 'password']));
});

it('validates login request rules with email containing spaces', function () {
    $request = new LoginRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'email' => 'john doe@example.com',
        'password' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and(data_get($validator->errors()->get('email'), '0'))->toContain('Please enter a valid email address.');
});

it('validates login request rules with email shorter than minimum length', function () {
    $request = new LoginRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'email' => '@', // Just an @ symbol, shorter than min:2
        'password' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and(implode($validator->errors()->get('email')))->toContain('Email must be at least 2 characters.');
});

it('validates login request rules with email longer than maximum length', function () {
    $request = new LoginRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $longEmail = str_repeat('a', 40).'@example.com'; // More than 50 characters total
    $invalidData = [
        'email' => $longEmail,
        'password' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and(data_get($validator->errors()->get('email'), 0))->toContain('Email cannot exceed 50 characters.');
});

it('validates login request rules with password shorter than minimum length', function () {
    $request = new LoginRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'email' => 'john.doe@example.com',
        'password' => 'p', // Only 1 character
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password'])
        ->and(data_get($validator->errors()->get('password'), 0))->toContain('Password must be at least 2 characters.');
});

it('validates login request rules with password longer than maximum length', function () {
    $request = new LoginRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'email' => 'john.doe@example.com',
        'password' => str_repeat('a', 51), // 51 characters
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password'])
        ->and(data_get($validator->errors()->get('password'), 0))->toContain('Password cannot exceed 50 characters.');
});
