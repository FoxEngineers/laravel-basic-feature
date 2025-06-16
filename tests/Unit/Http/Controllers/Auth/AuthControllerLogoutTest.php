<?php

use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Client;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->controller = new AuthController;
});

it('logout successfully', function () {
    // Create client and user
    $client = Client::factory()->asPersonalAccessTokenClient()->create();
    $user = User::factory()->create();

    $token = $user->createToken('Personal Access Token')->getToken();
    $accessToken = new AccessToken([
        'oauth_access_token_id' => $token->getKey(),
        'oauth_client_id' => $client->getKey(),
        'oauth_user_id' => $user->getKey(),
        'oauth_scopes' => [],
    ]);

    expect($accessToken->id)->toEqual($token->getKey());

    $user->withAccessToken($accessToken);

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);

    // Act
    $response = $this->controller->logout();

    // Assert
    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getData(true)['message'])->toBe(__('Logged out successfully.'));
});

it('handles logout when no user is authenticated', function () {
    Auth::shouldReceive('user')->once()->andReturnNull();

    $response = $this->controller->logout();

    expect($response->getStatusCode())->toBe(Response::HTTP_OK)
        ->and($response->getData(true)['message'])->toBe(__('Logged out successfully.'));
});
