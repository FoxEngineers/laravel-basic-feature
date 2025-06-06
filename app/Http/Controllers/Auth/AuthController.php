<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => [__('The provided credentials are incorrect.')],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
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
        $user = User::find($request->route('id'));
        if (! $user) {
            return $this->apiResponse(message: __('Invalid verification link'), status: Response::HTTP_FORBIDDEN);
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return $this->apiResponse(message: __('Invalid verification link'), status: Response::HTTP_FORBIDDEN);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Redirect to frontend after verification using config value
        return redirect()->to(config('app.frontend_verified_redirect_url'));
    }
}
