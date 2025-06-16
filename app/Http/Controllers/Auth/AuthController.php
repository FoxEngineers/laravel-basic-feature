<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (! User::where('email', $request->email)->exists()) {
            throw ValidationException::withMessages([
                'email' => [__('tle-validation.email.exists')],
            ]);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => [__('Incorrect email or password. Please try again.')],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => [__('Your email address is not verified.')],
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => [__('tle-validation.email.inactive')],
            ]);
        }

        $token = $user->createToken('Personal Access Token');

        return $this->apiResponse(
            message: __('Login successful.'),
            data: [
                'access_token' => $token->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->getToken()->expires_at,
                'user' => new UserResource($user),
            ],
            status: Response::HTTP_OK
        );
    }

    public function logout()
    {
        $user = Auth::user();
        if ($user && $user->token()) {
            $user->token()->revoke();
        }

        return $this->apiResponse(message: __('Logged out successfully.'), status: Response::HTTP_OK);
    }

    public function verifyEmail(Request $request)
    {
        $errResponse = $this->apiResponse(
            false,
            __('Invalid verification link'),
            [],
            Response::HTTP_FORBIDDEN
        );

        // Check if the URL signature is valid
        if (! URL::hasValidSignature($request)) {
            return $errResponse;
        }

        $user = User::find($request->route('id'));
        if (! $user) {
            return $errResponse;
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return $errResponse;
        }

        // Check if user is already verified
        if ($user->hasVerifiedEmail()) {
            return $this->apiResponse(
                true,
                __('Email already verified.'),
                [],
                Response::HTTP_OK
            );
        }

        // Verify the user
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->apiResponse(
            true,
            __('Email verified successfully.'),
            [],
            Response::HTTP_OK
        );
    }
}
