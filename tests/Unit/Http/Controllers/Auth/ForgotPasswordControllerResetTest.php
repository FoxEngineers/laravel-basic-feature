<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->controller = new ForgotPasswordController;
});

it('resets password successfully', function () {
    // Arrange
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $request = ResetPasswordRequest::create('/api/password/reset', 'POST', [
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

    $request = ResetPasswordRequest::create('/api/password/reset', 'POST', [
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
