<?php

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => null,
    ]);
    $this->notification = new CustomVerifyEmail;
    Config::set('app.frontend_verification_route', 'https://example.com/verify-email');
    Config::set('auth.verification.expire', 24);
});

it('builds a proper verification URL', function () {
    // Create the reflection method to access the protected method
    $reflectionMethod = new \ReflectionMethod(CustomVerifyEmail::class, 'verificationUrl');
    $reflectionMethod->setAccessible(true);

    // Get the verification URL
    $url = $reflectionMethod->invoke($this->notification, $this->user);

    // Parse the URL
    $parsedUrl = parse_url($url);
    parse_str($parsedUrl['query'], $queryParams);

    // Assertions
    expect($parsedUrl['scheme'])->toBe('https')
        ->and($parsedUrl['host'])->toBe('example.com')
        ->and($parsedUrl['path'])->toBe('/verify-email')
        ->and($queryParams)->toHaveKeys(['id', 'hash', 'expires', 'signature'])
        ->and((int) $queryParams['id'])->toBe($this->user->id)
        ->and($queryParams['hash'])->toBe(sha1($this->user->getEmailForVerification()));
});

it('creates an email message with correct attributes', function () {
    // Create a fixed URL for testing
    $mockUrl = 'https://example.com/verify-email?id=1&hash=abcdef&expires=1234567890&signature=xyz123';

    // Mock the notification class to return our fixed URL
    $notificationMock = $this->partialMock(CustomVerifyEmail::class);
    $notificationMock->shouldAllowMockingProtectedMethods()
        ->shouldReceive('verificationUrl')
        ->once()
        ->andReturn($mockUrl);

    // Get the mail message using our mocked notification
    $mailMessage = $notificationMock->toMail($this->user);

    // Assertions
    expect($mailMessage)->toBeInstanceOf(MailMessage::class)
        ->and($mailMessage->subject)->toBe('Verify Your Email Address')
        ->and($mailMessage->viewData)->toHaveKeys(['url', 'user'])
        // We don't directly compare the URL, as it's provided by our mock
        ->and($mailMessage->viewData['url'])->toBe($mockUrl)
        ->and($mailMessage->viewData['user'])->toBe($this->user)
        ->and($mailMessage->view)->toBe('emails.verify-email');
});

it('uses correct expiration time from config', function () {
    // Set a specific expiration time for the test
    Config::set('auth.verification.expire', 48);

    // Mock Carbon now to have a fixed value
    $fixedNow = Carbon::create(2023, 1, 1, 12, 0, 0);
    Carbon::setTestNow($fixedNow);

    // Mock URL facade to capture the expiration time
    URL::shouldReceive('temporarySignedRoute')
        ->once()
        ->withArgs(function ($name, $expiration, $parameters) use ($fixedNow) {
            // Verify the expiration time is 48 hours from now
            return $name === 'verification.verify' &&
                   $expiration->equalTo($fixedNow->copy()->addHours(48)) &&
                   isset($parameters['id']) &&
                   isset($parameters['hash']);
        })
        ->andReturn('https://app.test/api/v1/auth/verify-email/123?expires=1234567890&signature=abc123');

    // Create reflection method to access the protected method
    $reflectionMethod = new \ReflectionMethod(CustomVerifyEmail::class, 'verificationUrl');
    $reflectionMethod->setAccessible(true);

    // Call the method
    $reflectionMethod->invoke($this->notification, $this->user);

    // Clean up the mock
    Carbon::setTestNow();
});

it('includes correct user id and hash in verification url', function () {
    // Create a user with specific email for predictable hash
    $user = User::factory()->create([
        'id' => 12345,
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);

    $expectedHash = sha1('test@example.com');

    // Mock URL facade to verify parameters
    URL::shouldReceive('temporarySignedRoute')
        ->once()
        ->withArgs(function ($name, $expiration, $parameters) use ($user, $expectedHash) {
            return $name === 'verification.verify' &&
                   $parameters['id'] === $user->getKey() &&
                   $parameters['hash'] === $expectedHash;
        })
        ->andReturn('https://app.test/api/v1/auth/verify-email/12345?expires=1234567890&signature=abc123');

    // Create reflection method to access the protected method
    $reflectionMethod = new \ReflectionMethod(CustomVerifyEmail::class, 'verificationUrl');
    $reflectionMethod->setAccessible(true);

    // Call the method
    $url = $reflectionMethod->invoke($this->notification, $user);

    // Parse the URL
    $parsedUrl = parse_url($url);
    parse_str($parsedUrl['query'], $queryParams);

    // Assertions
    expect((int) $queryParams['id'])->toBe(12345)
        ->and($queryParams['hash'])->toBe($expectedHash);
});

it('uses frontend verification url from config', function () {
    // Set custom frontend URL
    $customUrl = 'https://custom-app.example.com/custom-verify';
    Config::set('app.frontend_verification_route', $customUrl);

    // Mock URL facade
    URL::shouldReceive('temporarySignedRoute')
        ->once()
        ->andReturn('https://app.test/api/v1/auth/verify-email/123?expires=1234567890&signature=abc123');

    // Create reflection method to access the protected method
    $reflectionMethod = new \ReflectionMethod(CustomVerifyEmail::class, 'verificationUrl');
    $reflectionMethod->setAccessible(true);

    // Call the method
    $url = $reflectionMethod->invoke($this->notification, $this->user);

    // Parse the URL
    $parsedUrl = parse_url($url);

    // Assertions
    expect($parsedUrl['scheme'].'://'.$parsedUrl['host'].$parsedUrl['path'])->toBe($customUrl);
});

it('preserves signature and expiration in the frontend url', function () {
    // Create mock URL with known signature and expiration
    URL::shouldReceive('temporarySignedRoute')
        ->once()
        ->andReturn('https://app.test/api/v1/auth/verify-email/123?expires=1609459200&signature=test-signature-123');

    // Create reflection method to access the protected method
    $reflectionMethod = new \ReflectionMethod(CustomVerifyEmail::class, 'verificationUrl');
    $reflectionMethod->setAccessible(true);

    // Call the method
    $url = $reflectionMethod->invoke($this->notification, $this->user);

    // Parse the URL
    $parsedUrl = parse_url($url);
    parse_str($parsedUrl['query'], $queryParams);

    // Assertions
    expect($queryParams['expires'])->toBe('1609459200')
        ->and($queryParams['signature'])->toBe('test-signature-123');
});
