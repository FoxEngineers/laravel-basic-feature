<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## API Features

This project includes a ready-to-use authentication system with the following features:

- **User Registration** with email verification
- **Login** using Laravel Passport (Personal Access Tokens)
- **Logout**
- **Forgot Password** and **Reset Password** (custom frontend reset link)
- **User Profile** (get and update profile)
- **Consistent API Response Structure**

### Endpoints

| Method | Endpoint                  | Description                        | Auth Required |
|--------|---------------------------|------------------------------------|--------------|
| POST   | `/register`               | Register new user                  | No           |
| POST   | `/auth/login`             | Login and get access token         | No           |
| POST   | `/auth/logout`            | Logout (revoke token)              | Yes          |
| GET    | `/me`                     | Get current user profile           | Yes          |
| POST   | `/password/forgot`        | Send password reset email          | No           |
| POST   | `/password/reset`         | Reset password                     | No           |
| GET    | `/email/verify/{id}/{hash}` | Verify email (called by frontend) | No           |

### Email Verification

- After registration, a verification email is sent via queue.
- The email contains a link to the frontend:  
  `${FRONTEND_URL}/verify-email/{id}/{hash}`
- The frontend should call the backend `/email/verify/{id}/{hash}` endpoint (using a signed URL).
- On successful verification, the backend will redirect the user to the URL specified by the `FRONTEND_VERIFIED_REDIRECT_URL` environment variable (default: `${FRONTEND_URL}/login`).

**Note:**  
- The backend route `/email/verify/{id}/{hash}` is defined in `routes/web.php` with the `signed` middleware and named `verification.verify`.  
- The verification link sent to the user must be signed and point to the frontend, which then calls the backend route for verification.
- The redirect URL after successful verification is fully configurable via the `FRONTEND_VERIFIED_REDIRECT_URL` environment variable.

### Password Reset

- The reset email contains a link to the frontend:  
  `${FRONTEND_RESET_PASSWORD_URL}?token={token}&email={email}`
- The frontend collects the token and email, then calls `/password/reset` with the new password.

### API Response Structure

All API responses use the following structure:
```json
{
  "success": true,
  "message": "Some message",
  "data": { ... }
}
```

### Environment Variables

Set these in your `.env`:
```
FRONTEND_URL=http://localhost:3000
FRONTEND_RESET_PASSWORD_URL=http://localhost:3000/reset-password
FRONTEND_VERIFIED_REDIRECT_URL=http://localhost:3000/login
```

## Setup Instructions

### Installation with Laravel Sail

1. Clone the repository:
```bash
git clone [repository-url] project-name
cd project-name
```

2. Create a copy of the `.env.example` file:
```bash
cp .env.example .env
```

3. Install Composer dependencies using a Docker container:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

4. Start Laravel Sail:
```bash
./vendor/bin/sail up -d
```

5. Generate an application key:
```bash
./vendor/bin/sail artisan key:generate
```

6. Run migrations:
```bash
./vendor/bin/sail artisan migrate
```

7. Create a personal access client for Passport:
```bash
./vendor/bin/sail artisan passport:client --personal
```

## How to Run Tests

You can run tests using Laravel Sail with the following commands:

- Run all tests:
```bash
./vendor/bin/sail artisan test
```

- Run tests with code coverage:
```bash
./vendor/bin/sail artisan test --coverage
```

- Generate HTML code coverage report:
```bash
./vendor/bin/sail artisan test --coverage-html=coverage
```

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).