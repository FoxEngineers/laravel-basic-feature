<?php

use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->controller = new AuthController;
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

    // Mock URL facade to validate signature
    URL::shouldReceive('hasValidSignature')
        ->once()
        ->with($request)
        ->andReturn(true);

    Event::fake();

    $response = $this->controller->verifyEmail($request);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getData(true))->toHaveKeys(['success', 'message', 'data'])
        ->and($response->getData(true)['success'])->toBeTrue()
        ->and($response->getData(true)['message'])->toBe(__('Email verified successfully.'));
});

it('returns success response if email already verified', function () {
    // Create user with verified email
    $user = User::factory()->create(['email_verified_at' => now()]);
    $hash = sha1($user->getEmailForVerification());

    // Mock request
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('route')
        ->with('id')
        ->andReturn($user->id);
    $request->shouldReceive('route')
        ->with('hash')
        ->andReturn($hash);

    // Mock URL facade
    URL::shouldReceive('hasValidSignature')
        ->once()
        ->with($request)
        ->andReturn(true);

    Event::fake();

    $response = $this->controller->verifyEmail($request);

    Event::assertNotDispatched(Verified::class);
    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getData(true)['message'])->toBe(__('Email already verified.'));
});

it('fails email verification with invalid signature', function () {
    $request = Mockery::mock(Request::class);

    // Mock URL facade to return invalid signature
    URL::shouldReceive('hasValidSignature')
        ->once()
        ->with($request)
        ->andReturn(false);

    $response = $this->controller->verifyEmail($request);

    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN)
        ->and($response->getData(true)['message'])->toBe(__('Invalid verification link'));
});

it('fails email verification with invalid user', function () {
    // Mock request with invalid ID
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('route')
        ->with('id')
        ->andReturn(999);

    // URL signature is valid but user doesn't exist
    URL::shouldReceive('hasValidSignature')
        ->once()
        ->with($request)
        ->andReturn(true);

    $response = $this->controller->verifyEmail($request);

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

    // URL signature is valid but hash doesn't match
    URL::shouldReceive('hasValidSignature')
        ->once()
        ->with($request)
        ->andReturn(true);

    // Act
    $response = $this->controller->verifyEmail($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN)
        ->and($response->getData(true)['message'])->toBe(__('Invalid verification link'));
});
