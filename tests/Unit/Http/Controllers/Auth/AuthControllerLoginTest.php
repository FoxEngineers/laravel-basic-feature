<?php

use App\Constants\Constant;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\PersonalAccessTokenResult;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->controller = new AuthController;
});

it('logs in successfully with valid credentials', function () {
    $email = 'john.doe@example.com';
    $password = 'password123';
    $user = User::factory()->create([
        'email' => $email,
        'password' => Hash::make($password),
    ]);

    $expiresAt = now()->addMinutes(Constant::TOKENS_EXPIRE_IN);

    // We still need to mock the token creation since we can't easily create a real token
    $mockToken = Mockery::mock(Token::class);
    $mockToken->shouldReceive('getAttribute')
        ->with('expires_at')
        ->andReturn($expiresAt);

    $mockTokenResult = Mockery::mock(PersonalAccessTokenResult::class);
    $mockTokenResult->accessToken = 'fake-token';
    $mockTokenResult->shouldReceive('getToken')->andReturn($mockToken);

    // Create a partial mock of the user to override createToken
    $mockUser = Mockery::mock($user)->makePartial();
    $mockUser->shouldReceive('createToken')
        ->once()
        ->with('Personal Access Token')
        ->andReturn($mockTokenResult);

    // Mock auth facade
    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password])
        ->andReturnTrue();

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($mockUser);

    // Create request
    $request = LoginRequest::create('/', 'POST', [
        'email' => $email,
        'password' => $password,
    ]);

    // Act
    $response = $this->controller->login($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $response = $response->getData(true);
    $responseData = data_get($response, 'data');

    expect($response['message'])->toBe(__('Login successful.'))
        ->and($responseData['access_token'])->toBe('fake-token')
        ->and($responseData['token_type'])->toBe('Bearer')
        ->and(Carbon::parse($responseData['expires_at'])->toIso8601String())->toEqual($expiresAt->toIso8601String())
        ->and($responseData['user'])->toMatchArray([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
        ]);
});

it('throws validation exception for non-existent email', function () {
    $email = 'nonexistent@example.com';
    $password = 'anypassword';

    // Make sure no user with this email exists in the database
    User::where('email', $email)->delete();

    $request = LoginRequest::create('/', 'POST', [
        'email' => $email,
        'password' => $password,
    ]);

    // We still need to mock Auth to prevent actual auth attempts
    Auth::shouldReceive('attempt')->never();

    expect(fn () => $this->controller->login($request))->toThrow(
        ValidationException::class
    )->and(function ($exception) {
        expect($exception->errors()['email'][0])->toBe(
            __('No account found with this email. Please check again or sign up.')
        );
    });
});

it('throws validation exception for invalid login credentials', function () {
    $email = 'valid@example.com';
    $password = 'correctPassword';
    $wrongPassword = 'wrongPassword';

    // Create a user with the correct password
    $user = User::factory()->create([
        'email' => $email,
        'password' => Hash::make($password),
    ]);

    $request = LoginRequest::create('/', 'POST', [
        'email' => $email,
        'password' => $wrongPassword, // Using wrong password
    ]);

    // We still need to mock Auth to prevent actual auth attempts
    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $wrongPassword])
        ->andReturnFalse();

    expect(fn () => $this->controller->login($request))->toThrow(
        ValidationException::class
    )->and(function ($exception) {
        expect($exception->errors()['email'][0])->toBe(
            __('Incorrect email or password. Please try again.')
        );
    });
});

it('throws validation exception when email is not verified', function () {
    $email = 'unverified@example.com';
    $password = 'password123';

    // Create an unverified user
    $user = User::factory()->unverified()->create([
        'email' => $email,
        'password' => Hash::make($password),
    ]);

    $request = LoginRequest::create('/', 'POST', [
        'email' => $email,
        'password' => $password,
    ]);

    // We still need to mock Auth for controlled behavior
    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password])
        ->andReturnTrue();

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);

    Auth::shouldReceive('logout')
        ->once();

    expect(fn () => $this->controller->login($request))->toThrow(
        ValidationException::class
    )->and(function ($exception) {
        expect($exception->errors()['email'][0])->toBe(
            __('Your email address is not verified.')
        );
    });
});

it('throws validation exception when account is inactive', function () {
    $email = 'inactive@example.com';
    $password = 'password123';

    // Create an inactive user but with verified email
    $user = User::factory()->create([
        'email' => $email,
        'password' => Hash::make($password),
        'email_verified_at' => now(),
        'is_active' => false,
    ]);

    $request = LoginRequest::create('/', 'POST', [
        'email' => $email,
        'password' => $password,
    ]);

    // We still need to mock Auth for controlled behavior
    Auth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password])
        ->andReturnTrue();

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);

    Auth::shouldReceive('logout')
        ->once();

    expect(fn () => $this->controller->login($request))->toThrow(
        ValidationException::class
    )->and(function ($exception) {
        expect($exception->errors()['email'][0])->toBe(
            __('tle-validation.email.inactive')
        );
    });
});
