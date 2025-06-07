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

### Installation without Laravel Sail

1. Clone the repository:
```bash
git clone [repository-url] project-name
cd project-name
```

2. Create a copy of the `.env.example` file:
```bash
cp .env.example .env
```

3. Install Composer dependencies:
```bash
composer install
```

4. Generate an application key:
```bash
php artisan key:generate
```

5. Run migrations:
```bash
php artisan migrate
```

6. Create a personal access client for Passport:
```bash
php artisan passport:client --personal
```

### Environment Variables

Set these in your `.env`:
```
FRONTEND_URL=http://localhost:3000
FRONTEND_RESET_PASSWORD_URL=http://localhost:3000/reset-password
FRONTEND_VERIFIED_REDIRECT_URL=http://localhost:3000/login
```

## API Features

This project includes a ready-to-use authentication system with the following features:

- **User Registration** with email verification
- **Login** using Laravel Passport (Personal Access Tokens)
- **Logout**
- **Forgot Password** and **Reset Password** (custom frontend reset link)
- **User Profile** (get profile)
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

### Email Verification

- After registration, the email contains a link sent to the user:
  `/email/verify/{id}/{hash}`
- On successful verification, the backend will redirect the user to the URL specified by the `FRONTEND_VERIFIED_REDIRECT_URL` environment variable (default: `${FRONTEND_URL}/login`).

**Note:**
- The backend route `/email/verify/{id}/{hash}` is defined in `routes/web.php` with the `signed` middleware and named `verification.verify`.
- The verification link sent to the user must be signed, which then calls the backend route for verification.
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

## How to Run Tests

### Using Laravel Sail

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

### Without Laravel Sail

- Run all tests:
```bash
php artisan test
```

- Run tests with code coverage:
```bash
php artisan test --coverage
```

- Generate HTML code coverage report:
```bash
php artisan test --coverage-html=coverage
```