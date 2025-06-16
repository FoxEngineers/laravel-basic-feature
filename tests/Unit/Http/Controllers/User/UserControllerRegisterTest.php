<?php

use App\Http\Controllers\User\UserController;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    // Mock the UserService
    $this->userService = Mockery::mock(UserService::class);
    $this->controller = new UserController($this->userService);
});

it('registers a new user successfully', function () {
    // Arrange
    Notification::fake();
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('commit')->once();

    $firstName = 'John';
    $lastName = 'Doe';
    $email = 'john.doe@example.com';
    $userData = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    // Setup a user object to be returned by the service
    $user = User::factory()->make([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
    ]);

    // Create a mock request that returns the validated data
    $request = Mockery::mock(RegisterRequest::class);
    $request->shouldReceive('validated')->andReturn($userData);

    // Mock the registerUser method to return our user
    $this->userService->shouldReceive('registerUser')
        ->once()
        ->andReturn($user);

    // Act
    $response = $this->controller->register($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_CREATED);

    $responseData = $response->getData(true);
    expect($responseData['message'])->toBe(__('Thanks for registering! Please verify your email address to activate your account.'))
        ->and($responseData['data']['user']['email'])->toBe($email);
});

it('prevents registration with already verified email', function () {
    // Arrange
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('rollBack')->once();

    $userData = [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'verified@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    // Create a mock request that returns the validated data
    $request = Mockery::mock(RegisterRequest::class);
    $request->shouldReceive('validated')->andReturn($userData);
    $request->shouldReceive('all')->andReturn($userData);

    // Mock the service to throw ValidationException
    $this->userService->shouldReceive('registerUser')
        ->once()
        ->andThrow(ValidationException::withMessages([
            'email' => [__('This email is already registered. Please sign in or reset your password.')],
        ]));

    // Act & Assert
    try {
        $this->controller->register($request);
        $this->fail('Expected ValidationException was not thrown');
    } catch (ValidationException $e) {
        expect($e->errors())
            ->toHaveKey('email')
            ->and($e->errors()['email'][0])->toBe(__('This email is already registered. Please sign in or reset your password.'));
    }
});

it('replaces unverified user record when registering with the same email', function () {
    // Arrange
    Notification::fake();
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('commit')->once();

    $userData = [
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    // Create a new user object to return
    $user = User::factory()->make([
        'first_name' => 'New',
        'last_name' => 'User',
        'email' => 'test@example.com',
    ]);

    // Create a mock request that returns the validated data
    $request = Mockery::mock(RegisterRequest::class);
    $request->shouldReceive('validated')->andReturn($userData);

    // Mock the service to return our user
    $this->userService->shouldReceive('registerUser')
        ->once()
        ->with(Mockery::on(function ($arg) use ($userData) {
            return $arg['email'] === $userData['email'];
        }))
        ->andReturn($user);

    // Act
    $response = $this->controller->register($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_CREATED);

    $responseData = $response->getData(true);
    expect($responseData['data']['user']['email'])->toBe('test@example.com');
});

it('handles database errors during registration gracefully', function () {
    // Arrange
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('rollBack')->once();

    $userData = [
        'first_name' => 'Error',
        'last_name' => 'Test',
        'email' => 'error@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    // Create a mock request that returns the validated data
    $request = Mockery::mock(RegisterRequest::class);
    $request->shouldReceive('validated')->andReturn($userData);
    $request->shouldReceive('all')->andReturn($userData);

    // Mock the service to throw an exception
    $this->userService->shouldReceive('registerUser')
        ->once()
        ->andThrow(new Exception('Database error on delete'));

    // Act
    $response = $this->controller->register($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_INTERNAL_SERVER_ERROR);

    $responseData = $response->getData(true);
    expect($responseData['success'])->toBeFalse()
        ->and($responseData['message'])->toBe(__('Registration failed. Please try again later.'));
});
