<?php

use App\Http\Requests\User\RegisterRequest;
use Illuminate\Support\Facades\Validator;

it('validates register request rules with valid data', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($data, $rules, $messages);

    expect($validator->passes())->toBeTrue();
});

it('validates register request rules with missing first name', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('first_name')
        ->and($validator->errors()->get('first_name')[0])->toBe('First name is required.');
});

it('validates register request rules with missing last name', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('last_name')
        ->and($validator->errors()->get('last_name')[0])->toBe('Last name is required.');
});

it('validates register request rules with first name exceeding max length', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'first_name' => str_repeat('A', 51), // Exceeds max length of 50
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('first_name')
        ->and($validator->errors()->get('first_name'))->toContain(__('validation.max.string', ['attribute' => 'first name', 'max' => 50]));
});

it('validates register request rules with first name not matching regex', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'first_name' => 'John123', // Contains numbers which aren't allowed
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('first_name');
});

it('validates register request rules with first name below min length', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'first_name' => 'J', // Below min length of 2
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('first_name')
        ->and($validator->errors()->get('first_name'))->toContain(__('validation.min.string', ['attribute' => 'first name', 'min' => 2]));
});

it('validates register request rules with email exceeding max length', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => str_repeat('a', 40).'@example.com', // Exceeds max length of 50
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('email')
        ->and($validator->errors()->get('email'))->toContain(__('validation.max.string', ['attribute' => 'email', 'max' => 50]));
});

it('validates register request rules with invalid email format', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'invalid-email',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('email')
        ->and($validator->errors()->get('email')[0])->toBe('Please enter a valid email address.');
});

it('validates register request rules with missing password', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('password')
        ->and($validator->errors()->get('password')[0])->toBe('Password is required.');
});

it('validates register request rules with password missing lowercase letters', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'PASSWORD123!',
        'password_confirmation' => 'PASSWORD123!',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('password');
});

it('validates register request rules with password missing uppercase letters', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('password');
});

it('validates register request rules with password missing numbers', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password!',
        'password_confirmation' => 'Password!',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('password');
});

it('validates register request rules with password missing symbols', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('password');
});

it('validates register request rules with password exceeding max length', function () {
    $rules = (new RegisterRequest)->rules();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!Password123!', // 22 characters, exceeds max length of 15
        'password_confirmation' => 'Password123!Password123!',
    ];

    $validator = Validator::make($invalidData, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('password');
});

it('validates register request rules with mismatched password confirmation', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'DifferentPassword123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('password')
        ->and($validator->errors()->get('password')[0])->toBe('The password confirmation does not match.');
});

it('validates register request rules with missing password confirmation', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('password_confirmation')
        ->and($validator->errors()->get('password_confirmation')[0])->toBe('Confirm password is required.');
});

it('validates names with valid special characters', function () {
    $rules = (new RegisterRequest)->rules();

    $validData = [
        'first_name' => "O'Connor-Smith",
        'last_name' => "d'Artagnan",
        'email' => 'john.doe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($validData, $rules);

    expect($validator->passes())->toBeTrue();
});

it('validates register request rules with missing email', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('email')
        ->and($validator->errors()->get('email')[0])->toBe('Email is required.');
});

it('validates register request rules with email below min length', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'a', // Below min length of 2
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toMatchArray(['email'])
        ->and(implode($validator->errors()->get('email')))->toContain('Email must be at least 2 characters.');
});

it('validates register request rules with email missing domain', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@', // Missing domain
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('email')
        ->and(data_get($validator->errors()->get('email'), 0))->toBe('Please enter a valid email address.');
});

it('validates register request rules with email having invalid TLD', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.a', // TLD too short (needs at least 2 chars)
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('email')
        ->and(data_get($validator->errors()->get('email'), 0))->toBe('Please enter a valid email address.');
});

it('validates register request rules with email failing regex but passing basic validation', function () {
    $request = new RegisterRequest;
    $rules = $request->rules();
    $messages = $request->messages();

    $invalidData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@localhost', // Technically valid email but fails the regex
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $validator = Validator::make($invalidData, $rules, $messages);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->keys())->toContain('email')
        ->and(data_get($validator->errors()->get('email'), 0))->toBe('Please enter a valid email address.');
});
