<?php

use App\Http\Controllers\User\UserController;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->controller = new UserController;
});

it('registers a new user successfully', function () {
    // Arrange
    Notification::fake();

    $firstName = 'John';
    $lastName = 'Doe';
    $email = 'john.doe@example.com';
    $userData = [
        'first_name' => $firstName,
        'last_name' => $lastName,
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
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email_verified_at' => null,
    ]);

    // Verify password was hashed
    $user = User::where('email', 'john.doe@example.com')->first();
    expect(Hash::check('password123', $user->password))->toBeTrue();

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
        'full_name' => $user->full_name,
        'email' => $user->email,
    ]);
});
