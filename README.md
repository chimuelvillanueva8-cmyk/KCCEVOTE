# KEVS - KCC E-Voting System

KEVS is a CodeIgniter 4 application for student registration, election setup, candidate and voter management, attendance, and online voting.

## Requirements

- PHP 8.1 or later with `intl`, `mbstring`, `mysqli`, `curl`, `fileinfo`, `gd`, and the PhpSpreadsheet extensions `dom`, `simplexml`, `xml`, `xmlreader`, `xmlwriter`, `zip`, and `zlib` (plus standard PHP extensions such as `ctype`, `filter`, `iconv`, and `libxml`)
- MySQL or MariaDB
- Composer
- Apache with `mod_rewrite` and `.htaccess` overrides enabled, or an equivalent web-server configuration

## Local setup

1. Install dependencies with `composer install`.
2. Copy `env` to `.env` and configure the database, session save path, and local base URL.
3. Create the database schema using the deployment procedure documented for your database.
4. Point the web server document root at `public/`; do not expose the repository root.
5. Start the app with `php spark serve` for local development, or configure Apache to serve `public/`.

There are no default administrator or student login credentials. For a first administrator, generate a password hash on the server with `php -r 'echo password_hash(readline("Initial admin password: "), PASSWORD_DEFAULT), PHP_EOL;'`, then insert the username and generated hash into `admins` using a trusted database client. Do not store the plaintext password or reuse sample credentials.

`TestStudentSeeder` and `SampleElectionSeeder` contain development/demo data and refuse to run when `CI_ENVIRONMENT` is `production`.

## Production configuration

Set `CI_ENVIRONMENT = production` and configure every production value in `.env` or the deployment environment. At minimum, set:

- `app.baseURL` to the public HTTPS URL
- `database.default.hostname`, `database.default.database`, `database.default.username`, and `database.default.password` to a dedicated database account with only the permissions the app requires
- `session.savePath` to an absolute, writable path outside the public document root
- `AZURE_FACE_ENDPOINT` and `AZURE_FACE_KEY` if face verification is enabled

Production mode rejects a missing or non-HTTPS `app.baseURL`, forces HTTPS, disables database debug mode, uses secure session cookies, and applies CSRF and security-header filters. Keep `.env`, `writable/`, application source, and `vendor/` outside the web document root. Back up the database and private uploads, protect and rotate credentials, and test restore procedures before launch.

The uploaded student ID images and OCR temporary files are not public. The Apache rules under `public/uploads/` and `public/assets/uploads/` rely on `AllowOverride` being enabled; configure equivalent deny rules if using another server.

## Tests and checks

Run `php spark routes` to check application boot and route registration, and `php vendor/bin/phpunit` to run the PHPUnit suite. Current unit tests cover face-descriptor validation and campus-location checks; they do not replace end-to-end tests for authentication, authorization, CSRF, uploads, election eligibility, or concurrent ballot submission before production use.

Students with legacy image-only face enrollment must be re-enrolled by an administrator to use face login; password login remains available. Campus checks rely on browser-provided coordinates, which can be spoofed and should not be treated as a trusted anti-fraud control.

## Election-data limitation

The current schema deliberately retains the existing association between a student's identity and their candidate selections. This preserves the current data model and workflow, but it does **not** provide secret ballots. Do not use this system for elections that require ballot secrecy without redesigning and migrating vote storage.


