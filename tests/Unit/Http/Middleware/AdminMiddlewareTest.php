<?php

use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

beforeEach(function () {
    $this->middleware = new AdminMiddleware;
    $this->request = new Request;
});

it('allows admin users to access protected route', function () {
    // Arrange
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    Passport::actingAs($admin);

    $called = false;
    $next = function ($request) use (&$called) {
        $called = true;

        return response('OK');
    };

    // Act
    $response = $this->middleware->handle($this->request, $next);

    // Assert
    expect($called)->toBeTrue()
        ->and($response->getContent())->toBe('OK');
});

it('blocks non-admin users from accessing protected route', function () {
    // Arrange
    $regularUser = User::factory()->create([
        'is_admin' => false,
    ]);
    Passport::actingAs($regularUser);

    $next = function ($request) {
        return response('OK');
    };

    // Act
    $response = $this->middleware->handle($this->request, $next);

    // Assert
    expect($response->getStatusCode())->toBe(403)
        ->and(json_decode($response->getContent(), true))->toBe([
            'success' => false,
            'message' => __('Unauthorized. Admin access required.'),
        ]);
});

it('blocks unauthenticated users from accessing protected route', function () {
    // No authentication setup

    $next = function ($request) {
        return response('OK');
    };

    // Act
    $response = $this->middleware->handle($this->request, $next);

    // Assert
    expect($response->getStatusCode())->toBe(403)
        ->and(json_decode($response->getContent(), true))->toBe([
            'success' => false,
            'message' => __('Unauthorized. Admin access required.'),
        ]);
});
