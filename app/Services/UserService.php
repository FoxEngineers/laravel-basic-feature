<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Create a new user account or handle an existing email.
     *
     *
     * @throws ValidationException
     */
    public function registerUser(array $userData): User
    {
        // Check if user with this email exists
        $existingUser = User::where('email', $userData['email'])->first();

        if ($existingUser) {
            // If user exists and has verified email
            if ($existingUser->email_verified_at !== null) {
                throw ValidationException::withMessages([
                    'email' => [__('This email is already registered. Please sign in or reset your password.')],
                ]);
            }

            // If user exists but isn't verified, delete the old user record
            $existingUser->delete();
        }

        // Create new user
        $user = User::create([
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'full_name' => $userData['first_name'].' '.$userData['last_name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        // Send email verification notification
        event(new Registered($user));

        return $user;
    }
}
