<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        $frontendVerificationURL = config('app.frontend_verification_route');

        $hash = sha1($notifiable->getEmailForVerification());
        // Generate a signed URL directly with our own params
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addHours((int) config('auth.verification.expire', 24)),
            [
                'id' => $notifiable->getKey(),
                'hash' => $hash,
            ]
        );

        // Parse the generated URL to extract the signature and expiration
        $url = parse_url($verifyUrl);
        parse_str($url['query'] ?? '', $params);

        // Build the frontend verification URL
        return $frontendVerificationURL.'?'.http_build_query([
            'id' => $notifiable->getKey(),
            'hash' => $hash,
            'expires' => $params['expires'],
            'signature' => $params['signature'],
        ]);
    }

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('Verify Your Email Address'))
            ->view('emails.verify-email', [
                'url' => $verificationUrl,
                'user' => $notifiable,
            ]);
    }
}
