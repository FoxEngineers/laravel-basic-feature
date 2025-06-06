<?php

use App\Http\Controllers\User\UserController;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->controller = new UserController;
});

it('registers a new user successfully', function () {
    // Arrange
    Event::fake();
    Notification::fake();

    $name = 'John Doe';
    $email = 'john.doe@example.com';
    $userData = [
        'name' => $name,
        'email' => $email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $request = RegisterRequest::create('/', 'POST', $userData);

    // Act
    $response = $this->controller->register($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_CREATED);

    $responseData = $response->getData(true);
    expect($responseData['message'])->toBe(__('Registration successful. Please check your email for verification link.'));

    // Check user was created in the database
    $this->assertDatabaseHas('users', [
        'email' => $email,
        'name' => $name,
        'email_verified_at' => null,
    ]);

    // Verify password was hashed
    $user = User::where('email', 'john.doe@example.com')->first();
    expect(Hash::check('password123', $user->password))->toBeTrue();

    // Check event was dispatched
    Event::assertDispatched(Registered::class, function ($event) use ($user) {
        return $event->user->id === $user->id;
    });

    // Verify notification would have been sent
    Notification::assertNothingSent();

    // Manually trigger verification notification to test it
    $user->sendEmailVerificationNotification();

    // Check that the verification email was sent
    Notification::assertSentTo(
        [$user],
        CustomVerifyEmail::class,
        function ($notification, $channels) use ($user) {
            $mailMessage = $notification->toMail($user);

            // Assert the mail has correct subject
            expect($mailMessage->subject)->toBe(__('Verify Your Email Address'))
                ->and($mailMessage->view)->toBe('emails.verify-email');

            // Assert the mail has the correct data
            $viewData = $mailMessage->viewData;
            expect($viewData)->toHaveKey('user')
                ->and($viewData)->toHaveKey('url')
                ->and($viewData['user'])->toBe($user);

            // Verify URL is a valid signed URL
            $url = $viewData['url'];

            // Create a request from the URL to check the signature
            $request = Request::create($url);
            expect(URL::hasValidSignature($request))->toBeTrue();

            return true;
        }
    );
});

it('gets authenticated user information', function () {
    $user = User::factory()->create();

    $request = Request::create('/api/me');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    // Act
    $response = $this->controller->me($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $responseData = $response->getData(true);
    expect($responseData['data']['user'])->toMatchArray([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ]);
});

it('returns unauthorized when getting user info without authentication', function () {
    // Create request with no authenticated user
    $request = Request::create('/api/me');
    $request->setUserResolver(function () {
        return null;
    });

    // Act
    $response = $this->controller->me($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_UNAUTHORIZED)
        ->and($response->getData(true)['message'])->toBe(__('Unauthenticated.'));
});
