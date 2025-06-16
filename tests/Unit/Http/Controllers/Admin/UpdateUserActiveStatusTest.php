<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Requests\Admin\User\UpdateUserActiveStatusRequest;
use App\Models\PassportToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

beforeEach(function () {
    $this->controller = new UserController;

    // Create mock users
    $this->admin = User::factory()->create([
        'is_admin' => true,
        'is_active' => true,
    ]);

    $this->activeUser = User::factory()->create([
        'is_active' => true,
    ]);

    $this->inactiveUser = User::factory()->create([
        'is_active' => false,
    ]);
});

it('successfully activates an inactive user', function () {
    // Mock authentication
    Passport::actingAs($this->admin);

    // Create request with 'is_active' => true
    $request = UpdateUserActiveStatusRequest::create('/', 'PATCH', [
        'is_active' => true,
    ]);

    // Call controller method
    $response = $this->controller->updateActiveStatus($request, $this->inactiveUser->id);
    $responseData = json_decode($response->getContent(), true);

    // Assert response is correct
    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(200)
        ->and($responseData)->toHaveKeys(['success', 'message', 'data'])
        ->and($responseData['data'])->toHaveKey('user')
        ->and($responseData['success'])->toBeTrue()
        ->and($responseData['message'])->toBe('User has been activated successfully')
        ->and($this->inactiveUser->fresh()->is_active)->toBeTrue();
});

it('successfully deactivates an active user', function () {
    // Mock authentication
    Passport::actingAs($this->admin);

    // Create request with 'is_active' => false
    $request = UpdateUserActiveStatusRequest::create('/', 'PATCH', [
        'is_active' => false,
    ]);

    // Call controller method
    $response = $this->controller->updateActiveStatus($request, $this->activeUser->id);
    $responseData = json_decode($response->getContent(), true);

    // Assert response is correct
    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(200)
        ->and($responseData['success'])->toBeTrue()
        ->and($responseData['message'])->toBe('User has been deactivated successfully')
        ->and($this->activeUser->fresh()->is_active)->toBeFalse();
});

it('prevents admin from changing their own active status', function () {
    // Mock authentication
    Passport::actingAs($this->admin);

    // Create request with 'is_active' => false
    $request = UpdateUserActiveStatusRequest::create('/', 'PATCH', [
        'is_active' => false,
    ]);

    // Call controller method trying to update the admin's own status
    $response = $this->controller->updateActiveStatus($request, $this->admin->id);
    $responseData = json_decode($response->getContent(), true);

    // Assert response indicates error
    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(403)
        ->and($responseData['success'])->toBeFalse()
        ->and($responseData['message'])->toBe('You cannot deactivate your own account.')
        ->and($this->admin->fresh()->is_active)->toBeTrue();
});

it('returns 404 when user not found', function () {
    // Mock authentication
    Passport::actingAs($this->admin);

    // Create request with 'is_active' => false
    $request = UpdateUserActiveStatusRequest::create('/', 'PATCH', [
        'is_active' => false,
    ]);

    // Use a non-existent ID
    $nonExistentId = User::max('id') + 100;

    // Call controller method
    $response = $this->controller->updateActiveStatus($request, $nonExistentId);
    $responseData = json_decode($response->getContent(), true);

    // Assert response is not found
    expect($response)->toBeInstanceOf(JsonResponse::class)
        ->and($response->getStatusCode())->toBe(404)
        ->and($responseData['success'])->toBeFalse()
        ->and($responseData['message'])->toBe(__('tle-validation.user.not_found'));
});

it('deletes user tokens when deactivating a user', function () {
    // Create a user with a token
    $user = User::factory()->create(['is_active' => true]);

    // Create a client and token for the user
    Client::factory()->asPersonalAccessTokenClient()->create();
    $user->createToken('Personal Access Token');

    // Verify token exists before deactivation
    expect(PassportToken::where('user_id', $user->id)->count())->toBe(1);

    // Mock authentication
    Passport::actingAs($this->admin);

    // Create request to deactivate the user
    $request = UpdateUserActiveStatusRequest::create('/', 'PATCH', [
        'is_active' => false,
    ]);

    // Call controller method
    $this->controller->updateActiveStatus($request, $user->id);

    // Verify user was deactivated
    expect($user->fresh()->is_active)->toBeFalse()
        ->and(PassportToken::where('user_id', $user->id)->count())->toBe(0);
});
