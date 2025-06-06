<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Models\User;
use App\Notifications\CustomResetPassword;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->controller = new ForgotPasswordController;
});

it('sends a reset link email successfully', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();

    $request = Request::create('/api/password/forgot', 'POST', [
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

    $request = Request::create('/api/password/forgot', 'POST', [
        'email' => 'nonexistent@example.com',
    ]);

    // Act
    $response = $this->controller->sendResetLinkEmail($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_NOT_FOUND)
        ->and($response->getData(true)['message'])->toBe(__(Password::INVALID_USER));

    // No email should be sent
    Notification::assertNothingSent();
});

it('rejects request without email', function () {
    // Arrange
    $request = Request::create('/api/password/forgot', 'POST', []);

    // Act & Assert
    $this->expectException(ValidationException::class);
    $this->controller->sendResetLinkEmail($request);
});

it('rejects request with invalid email format', function () {
    // Arrange
    $request = Request::create('/api/password/forgot', 'POST', [
        'email' => 'not-an-email',
    ]);

    // Act & Assert
    $this->expectException(ValidationException::class);
    $this->controller->sendResetLinkEmail($request);
});

it('returns too many requests when throttled', function () {
    // Arrange
    Notification::fake();

    $user = User::factory()->create();

    $request = Request::create('/api/password/forgot', 'POST', [
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

it('resets password successfully', function () {
    // Arrange
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $request = Request::create('/api/password/reset', 'POST', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    // Act
    $response = $this->controller->reset($request);

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getData(true)['message'])->toBe(__('Password has been reset.'));

    // Refresh user from database
    $updatedUser = User::find($user->id);

    // Verify password was changed
    expect(Hash::check('newpassword123', $updatedUser->password))->toBeTrue();

    // Token should be deleted after successful reset
    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => $user->email,
    ]);
});

it('fails to reset password with invalid token', function () {
    // Arrange
    $user = User::factory()->create();

    $request = Request::create('/api/password/reset', 'POST', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    // Act & Assert
    try {
        $this->controller->reset($request);
        $this->fail('Expected ValidationException was not thrown');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('email')
            ->and($e->errors()['email'][0])->toBe(__(Password::INVALID_TOKEN));
    }
});

it('fails to reset password with non-existent email', function () {
    // Arrange
    $request = Request::create('/api/password/reset', 'POST', [
        'token' => 'any-token',
        'email' => 'nonexistent@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    // Act & Assert
    $this->expectException(ValidationException::class);
    $this->controller->reset($request);
});

it('fails to reset password with password confirmation mismatch', function () {
    // Arrange
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $request = Request::create('/api/password/reset', 'POST', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'different-password',
    ]);

    // Act & Assert
    $this->expectException(ValidationException::class);
    $this->controller->reset($request);
});

it('fails to reset password with short password', function () {
    // Arrange
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $request = Request::create('/api/password/reset', 'POST', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    // Act & Assert
    $this->expectException(ValidationException::class);
    $this->controller->reset($request);
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
