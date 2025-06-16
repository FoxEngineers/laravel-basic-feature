<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TLE Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | This file contains validation messages organized by field and rule
    | for reuse throughout the application.
    |
    */

    // Email validation messages
    'email' => [
        'required' => 'Email is required.',
        'email' => 'Please enter a valid email address.',
        'regex' => 'Please enter a valid email address.',
        'min' => 'Email must be at least 2 characters.',
        'max' => 'Email cannot exceed 50 characters.',
        'exists' => 'No account found with this email. Please check again or sign up.',
        'inactive' => 'Your account is currently inactive. Please contact support.',
    ],

    // Password validation messages
    'password' => [
        'required' => 'Password is required.',
        'min' => 'Password must be at least 2 characters.',
        'max' => 'Password cannot exceed 50 characters.',
        'invalid' => 'Password must be 8 to 15 characters with at least 1 uppercase, 1 lowercase, 1 numeric, and 1 special character.',
        'confirmed' => 'The password confirmation does not match.',
    ],

    // Password confirmation validation messages
    'password_confirmation' => [
        'required' => 'Confirm password is required.',
    ],

    // Token validation messages
    'token' => [
        'required' => 'Token is required.',
    ],

    // First name validation messages
    'first_name' => [
        'required' => 'First name is required.',
        'regex' => 'First name can only contain letters, apostrophes, hyphens, and spaces.',
        'min' => 'First name must be at least 2 characters.',
        'max' => 'First name cannot exceed 50 characters.',
    ],

    // Last name validation messages
    'last_name' => [
        'required' => 'Last name is required.',
        'regex' => 'Last name can only contain letters, apostrophes, hyphens, and spaces.',
        'min' => 'Last name must be at least 2 characters.',
        'max' => 'Last name cannot exceed 50 characters.',
    ],

    // Active status validation messages
    'is_active' => [
        'required' => 'Active status is required.',
        'boolean' => 'Active status must be true or false.',
    ],
    'user' => [
        'not_found' => 'User not found.',
    ],
];
