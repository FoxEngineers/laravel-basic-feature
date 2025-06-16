<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\User;
use App\Notifications\CustomResetPassword;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->controller = new ForgotPasswordController;
});

it('sends a reset link email successfully', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();

    $request = ForgotPasswordRequest::create('/api/password/forgot', 'POST', [
        'email' => $user->email,
    ]);

    // Act
    $response = $this->controller->sendResetLinkEmail($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getData(true)['message'])->toBe(__(Password::RESET_LINK_SENT));

    // Verify notification was sent
    Notification::assertSentTo(
        $user,
        CustomResetPassword::class,
        function ($notification) use ($user) {
            // Test that URL is generated correctly
            ResetPassword::createUrlUsing(function ($notifiable, $token) {
                return config('app.frontend_reset_password_url').'?token='.$token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
            });

            // Verify token exists in the password_resets table
            $this->assertDatabaseHas('password_reset_tokens', [
                'email' => $user->email,
            ]);

            return true;
        }
    );
});

it('returns not found when email does not exist', function () {
    // Arrange
    Notification::fake();

    $request = ForgotPasswordRequest::create('/api/password/forgot', 'POST', [
        'email' => 'nonexistent@example.com',
    ]);

    // Act
    $response = $this->controller->sendResetLinkEmail($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND)
        ->and($response->getData(true)['message'])->toBe(__('tle-validation.email.exists'));

    // No email should be sent
    Notification::assertNothingSent();
});

it('returns too many requests when throttled', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();

    $request = ForgotPasswordRequest::create('/api/password/forgot', 'POST', [
        'email' => $user->email,
    ]);

    // Mock Password facade to return RESET_THROTTLED
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andReturn(Password::RESET_THROTTLED);

    // Act
    $response = $this->controller->sendResetLinkEmail($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_TOO_MANY_REQUESTS)
        ->and($response->getData(true)['message'])->toBe(__(Password::RESET_THROTTLED));

    // No email should be sent when throttled
    Notification::assertNothingSent();
});

it('tests the custom reset password email content', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();
    $token = 'test-token';

    // Act
    $user->notify(new CustomResetPassword($token));

    // Assert
    Notification::assertSentTo(
        $user,
        CustomResetPassword::class,
        function ($notification, $channels) use ($user, $token) {
            $mailMessage = $notification->toMail($user);

            // Check subject
            expect($mailMessage->subject)->toBe(__('Reset Password Notification'))
                ->and($mailMessage->markdown)->toBe('emails.reset-password');

            // Verify view is correct

            // Check view data
            $expectedUrl = config('app.frontend_reset_password_url').'?token='.$token.'&email='.urlencode($user->email);
            expect($mailMessage->viewData['url'])->toBe($expectedUrl)
                ->and($mailMessage->viewData['user'])->toBe($user);

            return true;
        }
    );
});

it('returns bad request when unable to send reset link', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();

    $request = ForgotPasswordRequest::create('/api/password/forgot', 'POST', [
        'email' => $user->email,
    ]);

    // Mock Password facade to return an unexpected status
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andReturn('unexpected_status');

    // Act
    $response = $this->controller->sendResetLinkEmail($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_BAD_REQUEST)
        ->and($response->getData(true)['message'])->toBe(__('Unable to send reset link.'));

    // No email should be sent
    Notification::assertNothingSent();
});
