## Setup Instructions

### Installation with Laravel Sail (docker)

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

7. Run Passport commands to generate keys:
```bash
php artisan passport:keys
```

8. Create a personal access client for Passport:
```bash
./vendor/bin/sail artisan passport:client --personal
```

9. Run seeder to create the default user:
```bash
./vendor/bin/sail artisan db:seed
```

### Installation without Laravel Sail (docker)

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

6. Run Passport commands to generate keys:
```bash
php artisan passport:keys
```

7. Create a personal access client for Passport:
```bash
php artisan passport:client --personal
```

8. Run seeder to create the default user:
```bash
php artisan db:seed
```

### Environment Variables

Set these in your `.env`:
```
FRONTEND_URL=http://localhost:3000
FRONTEND_RESET_PASSWORD_URL=http://localhost:3000/reset-password
FRONTEND_VERIFICATION_ROUTE=http://localhost:3000/verify-email
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
| GET    | `/email/verify/{id}/{hash}` | Verify email (called by frontend) | No           |

### Email Verification

- After registration, a verification email is sent via queue.
- The email contains a link to the frontend verification page with all required parameters:
  `${FRONTEND_VERIFICATION_ROUTE}?id={id}&hash={hash}&expires={timestamp}&signature={signature}`
- The frontend should extract the query parameters and make a GET request to:
  `/email/verify/{id}/{hash}?expires={timestamp}&signature={signature}`
- On successful verification, the backend returns a JSON response that your frontend can handle.

**Note:**  
- The backend route `/email/verify/{id}/{hash}` is defined in `routes/api.php` with the `signed` middleware and named `verification.verify`.  
- The verification link expires after the period specified in `AUTH_VERIFICATION_EXPIRE` (default: 24 hours).
- All verification parameters must be preserved when redirecting to maintain the link's validity.

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