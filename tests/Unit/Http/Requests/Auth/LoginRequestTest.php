<?php

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

it('validates login request rules with valid data', function () {
    // Fake a user
    $user = User::factory()->create(['email' => 'john.doe@example.com']);

    $rules = (new LoginRequest)->rules();

    $data = [
        'email' => $user->email,
        'password' => 'password123',
    ];

    $validator = Validator::make($data, $rules);

    expect($validator->passes())->toBeTrue();
});

it('validates login request rules with missing email', function () {
    $rules = (new LoginRequest)->rules();

    $invalidData = [
        'password' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and($validator->errors()->get('email'))->toContain(__('validation.required', ['attribute' => 'email']));
});

it('validates login request rules with invalid email format', function () {
    $rules = (new LoginRequest)->rules();

    $invalidData = [
        'email' => 'invalid-email',
        'password' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and($validator->errors()->get('email'))->toContain(__('validation.email', ['attribute' => 'email']));
});

it('validates login request rules with missing password', function () {
    $rules = (new LoginRequest)->rules();

    $invalidData = [
        'email' => 'john.doe@example.com',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password'])
        ->and($validator->errors()->get('password'))->toContain(__('validation.required', ['attribute' => 'password']));
});

it('validates login request rules with non-string password', function () {
    $rules = (new LoginRequest)->rules();

    $invalidData = [
        'email' => 'john.doe@example.com',
        'password' => 12345, // Non-string password
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password'])
        ->and($validator->errors()->get('password'))->toContain(__('validation.string', ['attribute' => 'password']));
});
