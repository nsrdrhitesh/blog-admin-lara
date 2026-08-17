# Installation Guide (Local Development)

This covers getting the project running on your own machine — XAMPP,
Laravel Herd, Valet, or any local PHP 8.2+ / MySQL setup. For putting it
on a live shared-hosting server, see **DEPLOYMENT.md** instead — the two
guides diverge more than they overlap (composer availability, file
permissions, cron vs local `artisan serve`, etc.), so they're kept separate
rather than one guide with branches everywhere.

## Requirements
- PHP 8.2 or higher, with these extensions: `gd`, `mbstring`, `fileinfo`,
  `pdo_mysql`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`.
  (XAMPP 8.2.x ships with all of these; `gd` is sometimes commented out in
  `php.ini` by default — see the note below if `composer install` fails
  complaining about it.)
- MySQL 5.7+ / MariaDB 10.3+ (phpMyAdmin, included with XAMPP, works fine
  for everything here)
- Composer ([getcomposer.org](https://getcomposer.org))
- Optional: Node.js, **only** if you want to compile the real Tailwind
  build locally (see step 7) — the app runs correctly without it via the
  CDN fallback described in `PHASE-9-NOTES.md`.

## Steps

1. **Extract the ZIP** into your local web root (e.g.
   `C:\xampp\htdocs\blog-cms` on XAMPP, or wherever your local server
   serves from).

2. **Install PHP dependencies**
   ```bash
   composer install
   ```
   If this fails with a message about `ext-gd`, your `php.ini` has the GD
   extension commented out — open it (`php --ini` tells you exactly which
   file), remove the leading `;` from `extension=gd`, restart your web
   server, and retry.

3. **Create your environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set your database credentials** in `.env`:
   ```
   DB_DATABASE=blog_cms
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   Create an empty `blog_cms` database in phpMyAdmin (or
   `mysql -u root -e "CREATE DATABASE blog_cms"`) first — the next step
   populates it, it doesn't create the database itself.

5. **Run migrations and seed the default admin account**
   ```bash
   php artisan migrate --seed
   ```
   This creates every table (see `PHASE-1-NOTES.md` for the full list) and
   one super-admin login: `admin@example.com` / `ChangeMe123!`.
   **Change this password immediately after your first login** — go to
   Profile in the admin sidebar.

6. **Link the public storage disk** (required — without this, every
   uploaded image 404s):
   ```bash
   php artisan storage:link
   ```

7. **Optional: compile the real Tailwind build.** The admin panel works
   out of the box via a CDN fallback, but for a faster, production-quality
   stylesheet:
   ```bash
   npm install
   npm run build:css
   ```
   This produces `public/build/app.css`, which the layout automatically
   prefers over the CDN the moment it exists (see `PHASE-9-NOTES.md`).

8. **Start the queue worker** (in a separate terminal — without this,
   image thumbnail/WebP/AVIF variants never generate; uploads still work,
   they just stay as the original file only):
   ```bash
   php artisan queue:work
   ```
   For quick local testing without a worker running, set
   `QUEUE_CONNECTION=sync` in `.env` instead — jobs then run immediately
   inline. Not recommended for anything beyond local testing (it makes
   every image upload request slow).

9. **Serve the app**
   ```bash
   php artisan serve
   ```
   Or, if using XAMPP directly, just visit `http://localhost/blog-cms/public`
   (Apache serves it directly — no `artisan serve` needed as long as your
   document root path is correct).

10. **Log in** at `/login` with the seeded admin account and start there.

## Troubleshooting
- **500 error, no detail**: set `APP_DEBUG=true` in `.env` temporarily to
  see the real exception, then set it back to `false`. Also check
  `storage/logs/laravel.log`.
- **"Class not found" errors after pulling changes**: run
  `composer dump-autoload`.
- **Images not appearing**: confirm step 6 (`storage:link`) was run, and
  that `storage/app/public` is writable by your web server user.
- **Uploaded images never get thumbnails/WebP variants**: confirm a queue
  worker is running (step 8), or check `QUEUE_CONNECTION` in `.env`.
