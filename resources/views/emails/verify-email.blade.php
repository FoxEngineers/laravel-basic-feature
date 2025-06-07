<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email Address</title>
</head>
<body>
    <h1>Hello {{ $user->full_name }},</h1>
    <p>Thank you for registering. Please verify your email address by clicking the button below.</p>
    <p>
        <a href="{{ $url }}" style="display:inline-block;padding:10px 20px;background:#3869d4;color:#fff;text-decoration:none;border-radius:4px;">
            Verify Email
        </a>
    </p>
    <p>If you did not create an account, no further action is required.</p>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
