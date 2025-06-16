<?php

use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    $this->request = new ResetPasswordRequest;
    $this->validData = [
        'token' => 'valid-token',
        'email' => 'test@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ];
});

it('passes validation with valid data', function () {
    // Create a user for the email exists validation
    $user = User::factory()->create([
        'email' => $this->validData['email'],
    ]);

    $validator = Validator::make(
        $this->validData,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeFalse();

    // Clean up
    $user->delete();
});

it('requires token field', function () {
    $data = $this->validData;
    $data['token'] = '';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('token'))->toBe(__('Token is required.'));
});

it('validates email is required', function () {
    $data = $this->validData;
    $data['email'] = '';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Email is required.'));
});

it('validates email format', function () {
    $data = $this->validData;
    $data['email'] = 'invalid-email';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Please enter a valid email address.'));
});

it('validates email minimum length', function () {
    $data = $this->validData;
    $data['email'] = 'a';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toContain(__('Email must be at least 2 characters.'));
});

it('validates email maximum length', function () {
    $data = $this->validData;
    $data['email'] = str_repeat('a', 40).'@'.str_repeat('b', 10).'.com';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Email cannot exceed 50 characters.'));
});

it('validates email domain format', function () {
    $data = $this->validData;
    $data['email'] = 'test@example';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('Please enter a valid email address.'));
});

it('validates email exists in users table', function () {
    $data = $this->validData;
    // Ensuring email doesn't exist in the database
    $data['email'] = 'nonexistent@example.com';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBe(__('No account found with this email. Please check again or sign up.'));
});

it('validates password is required', function () {
    $data = $this->validData;
    $data['password'] = '';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('password'))->toBe(__('Password is required.'));
});

it('validates password minimum length', function () {
    $data = $this->validData;
    $data['password'] = 'Pass1!';
    $data['password_confirmation'] = 'Pass1!';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue();
});

it('validates password maximum length', function () {
    $data = $this->validData;
    $data['password'] = 'Password123456789!';
    $data['password_confirmation'] = 'Password123456789!';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue();
});

it('validates password has letters', function () {
    $data = $this->validData;
    $data['password'] = '12345678!';
    $data['password_confirmation'] = '12345678!';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue();
});

it('validates password has mixed case', function () {
    $data = $this->validData;
    $data['password'] = 'password1!';
    $data['password_confirmation'] = 'password1!';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue();
});

it('validates password has numbers', function () {
    $data = $this->validData;
    $data['password'] = 'Password!';
    $data['password_confirmation'] = 'Password!';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue();
});

it('validates password has symbols', function () {
    $data = $this->validData;
    $data['password'] = 'Password1';
    $data['password_confirmation'] = 'Password1';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue();
});

it('validates password confirmation is required', function () {
    $data = $this->validData;
    $data['password_confirmation'] = '';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('password_confirmation'))->toBe(__('Confirm password is required.'));
});

it('validates password confirmation matches', function () {
    $data = $this->validData;
    $data['password_confirmation'] = 'DifferentPassword1!';

    $validator = Validator::make(
        $data,
        $this->request->rules(),
        $this->request->messages()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('password'))->toBe(__('The password confirmation does not match.'));
});
