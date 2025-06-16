<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        // Customize the reset link notification to use frontend URL
        ResetPassword::createUrlUsing(function ($notifiable, $token) {
            // @codeCoverageIgnoreStart
            return config('app.frontend_reset_password_url').'?token='.$token.'&email='.urlencode($notifiable->getEmailForPasswordReset());
            // @codeCoverageIgnoreEnd
        });

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::INVALID_USER) {
            return $this->apiResponse(false, __('tle-validation.email.exists'), null, Response::HTTP_NOT_FOUND);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return $this->apiResponse(message: __(Password::RESET_LINK_SENT));
        }

        if ($status === Password::RESET_THROTTLED) {
            return $this->apiResponse(false, __(Password::RESET_THROTTLED), null, Response::HTTP_TOO_MANY_REQUESTS);
        }

        return $this->apiResponse(false, __('Unable to send reset link.'), null, Response::HTTP_BAD_REQUEST);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->apiResponse(true, __('Password has been reset.'));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}