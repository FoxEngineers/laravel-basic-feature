<?php

use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

it('validates register request rules with valid data', function () {
    $rules = (new RegisterRequest)->rules();

    $data = [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $validator = Validator::make($data, $rules);

    expect($validator->passes())->toBeTrue();
});

it('validates register request rules with missing name', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['name'])
        ->and($validator->errors()->get('name'))->toContain(__('validation.required', ['attribute' => 'name']));
});

it('validates register request rules with name exceeding max length', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'name' => str_repeat('A', 256), // Exceeds max length
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['name'])
        ->and($validator->errors()->get('name'))->toContain(__('validation.max.string', ['attribute' => 'name', 'max' => 255]));
});

it('validates register request rules with duplicate email', function () {
    // Fake a user
    $user = User::factory()->create(['email' => 'john.doe@example.com']);

    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'name' => 'John Doe',
        'email' => $user->email, // Duplicate email
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and($validator->errors()->get('email'))->toContain(__('validation.unique', ['attribute' => 'email']));
});

it('validates register request rules with invalid email format', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and($validator->errors()->get('email'))->toContain(__('validation.email', ['attribute' => 'email']));
});

it('validates register request rules with missing password', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password_confirmation' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password'])
        ->and($validator->errors()->get('password'))->toContain(__('validation.required', ['attribute' => 'password']));
});

it('validates register request rules with password below minimum length', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password'])
        ->and($validator->errors()->get('password'))->toContain(__('validation.min.string', ['attribute' => 'password', 'min' => 8]));
});

it('validates register request rules with mismatched password confirmation', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
        'password_confirmation' => 'differentpassword',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password'])
        ->and($validator->errors()->get('password'))->toContain(__('validation.confirmed', ['attribute' => 'password']));
});

it('validates register request rules with missing password confirmation', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['password', 'password_confirmation'])
        ->and($validator->errors()->get('password'))->toContain(__('validation.confirmed', ['attribute' => 'password']))
        ->and($validator->errors()->get('password_confirmation'))->toContain(__('validation.required', ['attribute' => 'password confirmation']));
});
