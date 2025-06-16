<?php

use App\Http\Controllers\User\UserController;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    // Mock the UserService
    $this->userService = Mockery::mock(UserService::class);
    $this->controller = new UserController($this->userService);
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
