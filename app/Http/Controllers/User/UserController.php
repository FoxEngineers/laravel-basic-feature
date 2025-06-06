<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Send email verification notification using queue
        event(new Registered($user));

        return $this->apiResponse(
            true,
            __('Registration successful. Please check your email for verification link.'),
            ['user' => new UserResource($user)],
            Response::HTTP_CREATED
        );
    }

    public function me(Request $request)
    {
        // Check if user is authenticated
        $user = $request->user();

        if (! $user) {
            return $this->apiResponse(
                false,
                __('Unauthenticated.'),
                [],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->apiResponse(
            true,
            'User profile fetched successfully.',
            ['user' => new UserResource($user)],
            Response::HTTP_OK
        );
    }
}
