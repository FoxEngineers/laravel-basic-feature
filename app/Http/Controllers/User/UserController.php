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
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'full_name' => $request->first_name.' '.$request->last_name,
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
        return $this->apiResponse(
            true,
            'User profile fetched successfully.',
            ['user' => new UserResource($request->user())],
            Response::HTTP_OK
        );
    }
}
