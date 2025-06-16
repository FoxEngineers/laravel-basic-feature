<?php

use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->userService = new UserService;
});

it('registers a new user', function () {
    // Arrange
    Event::fake();
    $userData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
    ];

    // Act
    $user = $this->userService->registerUser($userData);

    // Assert
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->first_name)->toBe($userData['first_name'])
        ->and($user->last_name)->toBe($userData['last_name'])
        ->and($user->full_name)->toBe($userData['first_name'].' '.$userData['last_name'])
        ->and($user->email)->toBe($userData['email'])
        ->and($user->email_verified_at)->toBeNull()
        ->and(Hash::check($userData['password'], $user->password))->toBeTrue();

    Event::assertDispatched(Registered::class, function ($event) use ($user) {
        return $event->user->id === $user->id;
    });
});

it('throws validation exception for existing verified email', function () {
    // Arrange
    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'email_verified_at' => now(),
    ]);

    $userData = [
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'existing@example.com',
        'password' => 'password123',
    ];

    // Act & Assert
    expect(fn () => $this->userService->registerUser($userData))
        ->toThrow(ValidationException::class, 'This email is already registered. Please sign in or reset your password.');

    // Verify the existing user wasn't deleted
    $this->assertDatabaseHas('users', ['id' => $existingUser->id, 'email' => $existingUser->email]);
});

it('handles existing user with unverified email by replacing them', function () {
    // Arrange
    Event::fake();
    $existingUser = User::factory()->create([
        'email' => 'unverified@example.com',
        'email_verified_at' => null,
    ]);

    $userData = [
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'unverified@example.com',
        'password' => 'password123',
    ];

    // Act
    $user = $this->userService->registerUser($userData);

    // Assert
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->email)->toBe($userData['email'])
        ->and($user->id)->not->toBe($existingUser->id);

    // Verify the old user record was deleted
    $this->assertDatabaseMissing('users', ['id' => $existingUser->id]);

    // Verify a new record was created
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => $userData['email'],
        'first_name' => $userData['first_name'],
        'last_name' => $userData['last_name'],
    ]);

    Event::assertDispatched(Registered::class);
});

it('creates proper full name from first and last name', function () {
    // Arrange
    Event::fake();
    $userData = [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane.smith@example.com',
        'password' => 'securepassword',
    ];

    // Act
    $user = $this->userService->registerUser($userData);

    // Assert
    expect($user->full_name)->toBe('Jane Smith');
});

it('hashes the password correctly', function () {
    // Arrange
    Event::fake();
    $plainPassword = 'securepassword123';
    $userData = [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test.user@example.com',
        'password' => $plainPassword,
    ];

    // Act
    $user = $this->userService->registerUser($userData);

    // Assert
    expect($user->password)->not->toBe($plainPassword)
        ->and(Hash::check($plainPassword, $user->password))->toBeTrue();
});
