# SideQuest

SideQuest is a social networking application built with CodeIgniter 4. It includes account registration, a verified-user feed, post sharing, comments, reactions, profile customization, search, recommendations, and optional two-factor authentication.

## Features

- Account registration, login, logout, and password reset
- Email verification gate before users can access the full app
- Optional two-factor authentication with QR setup and recovery codes
- Feed with post creation, editing, deletion, sharing, and infinite scrolling
- Media uploads for posts, including images and videos
- Post reactions, comment reactions, saved posts, hidden posts, and hidden comments
- Public user profiles with follow and unfollow actions
- Profile picture and cover photo uploads
- Search and "People you may know" recommendations
- PHPUnit test suite for app-level coverage

## Tech Stack

- PHP 8.2+
- CodeIgniter 4.7
- MySQL or MariaDB for the main application database
- SQLite in-memory database for default automated tests
- Composer for dependency management
- `pragmarx/google2fa` for two-factor authentication
- `endroid/qr-code` for QR code generation

## Requirements

Make sure your environment includes:

- PHP 8.2 or higher
- Composer
- MySQL or MariaDB
- Apache, Nginx, or the built-in CodeIgniter development server
- PHP extensions: `intl`, `mbstring`, `json`, `mysqlnd`, `curl`

## Getting Started

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Create your environment file:

   ```bash
   copy env .env
   ```

3. Update `.env` with your local settings:

   ```ini
   app.baseURL = 'http://localhost:8080/'

   database.default.hostname = localhost
   database.default.database = sidequest
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

4. Create a database named `sidequest`.

5. Import the included database dump:

   - File: `app/Database/sidequest.sql`
   - This is the quickest way to get the app running with the expected schema and sample data.

6. Make sure the web server points to the `public` directory, not the project root.

7. Start the app with either:

   ```bash
   php spark serve
   ```

   Or configure your local Apache/XAMPP virtual host to serve `public/`.

## Database Notes

- The repository includes incremental migrations in `app/Database/Migrations`.
- The included SQL dump is the most complete bootstrap for local setup.
- Uploaded media is stored under `public/storage/`, so that directory must be writable.

## Auth Flow Notes

- Newly registered users must verify their email before accessing the verified routes such as the feed.
- In the current local setup, the verification page generates and previews a verification link instead of sending a real email.
- Two-factor authentication can be enabled from the security settings page after login.

## Testing

Run the test suite with:

```bash
composer test
```

By default, PHPUnit uses the `tests` database group configured for SQLite in memory.

## Useful Paths

- Application code: `app/`
- Public entry point: `public/index.php`
- Route definitions: `app/Config/Routes.php`
- Main SQL dump: `app/Database/sidequest.sql`
- Tests: `tests/`

## Media Support

- Accepted image types: JPG, JPEG, PNG, GIF, WebP
- Accepted video types: MP4, WebM, MOV
- Feed previews use fixed-ratio containers to keep cards visually stable while media loads
- The full-screen gallery viewer supports touch navigation and pinch-to-zoom behavior for images

## License

This project is distributed under the MIT license. See `LICENSE` for details.
