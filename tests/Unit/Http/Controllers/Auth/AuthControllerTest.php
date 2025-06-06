<?php

use App\Constants\Constant;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Client;
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
            'name' => $user->name,
            'email' => $user->email,
        ]);
});

it('throws validation exception for invalid login credentials', function () {
    $request = LoginRequest::create('/', 'POST', [
        'email' => 'invalid@example.com',
        'password' => 'wrongpassword',
    ]);

    Auth::shouldReceive('attempt')->once()->with(['email' => 'invalid@example.com', 'password' => 'wrongpassword'])->andReturnFalse();

    expect(fn () => $this->controller->login($request))->toThrow(ValidationException::class, __('The provided credentials are incorrect.'));
});

it('logs out successfully', function () {
    // Create client and user
    Client::factory()->asPersonalAccessTokenClient()->create();
    $user = User::factory()->create();

    $user->createToken('Personal Access Token');

    Auth::shouldReceive('user')->once()->andReturn($user);

    $response = $this->controller->logout();

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getData(true)['message'])->toBe(__('Logged out successfully.'))
        ->and($user->token())->toBeNull();
});

it('handles logout when no user is authenticated', function () {
    Auth::shouldReceive('user')->once()->andReturnNull();

    $response = $this->controller->logout();

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getData(true)['message'])->toBe(__('Logged out successfully.'));
});

it('verifies email successfully', function () {
    // Create user using factory
    $user = User::factory()->create(['email_verified_at' => null]);
    $hash = sha1($user->getEmailForVerification());

    // Mock request for verification
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('route')
        ->with('id')
        ->andReturn($user->id);
    $request->shouldReceive('route')
        ->with('hash')
        ->andReturn($hash);

    Event::fake();

    $response = $this->controller->verifyEmail($request);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
    expect($response->getTargetUrl())->toBe(config('app.frontend_verified_redirect_url'));
});

it('fails email verification with invalid user', function () {
    // Mock request with invalid ID
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('route')
        ->with('id')
        ->andReturn(999);
    $request->shouldReceive('route')
        ->with('hash')
        ->andReturn(sha1('nonexistent@example.com'));

    $response = $this->controller->verifyEmail($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN)
        ->and($response->getData(true)['message'])->toBe(__('Invalid verification link'));
});

it('fails email verification with invalid hash', function () {
    // Create user using factory
    $user = User::factory()->create();

    // Mock request with invalid hash
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('route')
        ->with('id')
        ->andReturn($user->id);
    $request->shouldReceive('route')
        ->with('hash')
        ->andReturn('invalid-hash');

    // Act
    $response = $this->controller->verifyEmail($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN)
        ->and($response->getData(true)['message'])->toBe(__('Invalid verification link'));
});
