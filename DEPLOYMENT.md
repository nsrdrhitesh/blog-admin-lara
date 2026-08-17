# Deployment Guide (Shared Hosting)

Written for typical cPanel-style shared hosting (the kind the original
spec targeted — "runs on shared hosting without requiring Node.js on the
server"). If you're deploying to a VPS, Forge, or a platform like
Railway/Render instead, most of this still applies but you likely have
better tools available (real cron, persistent queue workers, SSH as
standard) — use them instead of the workarounds below where you can.

## Before you upload anything

**Build these locally first — the server never runs Composer, npm, or
Node in this setup:**

```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build:css   # optional — see PHASE-9-NOTES.md
```

`--no-dev` matters: it skips dev-only packages (Pint, PHPUnit, Faker) that
have no reason to exist on a production server and just add attack
surface and disk usage for nothing.

## What to upload

Upload the **entire project**, including the `vendor/` folder you just
generated (shared hosting has no Composer to run, so `vendor/` has to
arrive pre-built) — everything except:
- `node_modules/` (never needed on the server, even if you ran `npm install` locally)
- `.git/` if present
- `tests/` (optional to exclude — harmless either way, just dead weight)

## Document root: two scenarios

**If your host lets you set the document root to a subfolder** (common on
modern cPanel — check "Domains" → your domain → document root): point it
directly at the project's `public/` folder. This is the cleaner setup and
is what `public/.htaccess` (already in this project) is built for. Nothing
else to configure.

**If you can't change the document root** (common on shared/addon domains
where you're stuck serving from the account's root folder): the project
root already includes a **root-level `.htaccess`** that transparently
rewrites every request into `public/` for you. You don't need to do
anything extra — it's already there and explains itself in its own
comments if you want to understand exactly what it's doing.

## Database

1. Create a MySQL database and user through your host's control panel
   (cPanel's "MySQL Databases" tool, typically).
2. Import the schema one of two ways:
   - **Preferred, if you have terminal/SSH access** (many hosts include a
     "Terminal" app in cPanel even without full SSH):
     ```bash
     php artisan migrate --seed
     ```
   - **phpMyAdmin only, no terminal access**: import
     `database/sql/schema.sql` directly. Read the warning at the top of
     that file first — it's a hand-maintained convenience copy of the real
     migrations, not the authoritative source. You'll also need to
     manually insert the roles/permissions/admin-user rows the
     `RolePermissionSeeder` normally creates (or run that seeder later if
     you get terminal access at any point — it's idempotent, safe to run
     even after manual data entry, since every row uses `firstOrCreate`).

## `.env` on the server

Copy `.env.example` to `.env` and fill in real values. A few settings
specifically matter more in production than they did locally:

```
APP_ENV=production
APP_DEBUG=false          # never true on a live site — leaks stack traces
APP_URL=https://yourdomain.com
SESSION_SECURE_COOKIE=true   # add this line if your site is HTTPS-only (it should be)
```

Then, from wherever you have PHP CLI access (terminal, SSH, or a one-off
cron job — see below):

```bash
php artisan key:generate
php artisan storage:link
```

## Optimization commands

Run these after every deploy (new release, config change, or route
change) — they precompile config/routes/views into cached PHP files so
the framework doesn't re-parse them on every request:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Or all at once:
```bash
php artisan optimize
```

**Important**: `config:cache` freezes your `.env` values into the cache —
if you change `.env` after running it, your changes won't take effect
until you run `php artisan config:clear` (or re-run `config:cache`).
This trips people up constantly; it's not a bug, it's what the command is
for.

## The queue worker problem on shared hosting

Image variant generation (thumbnails, WebP, AVIF — Phase 4) runs through
Laravel's queue, which normally means a long-running `php artisan
queue:work` process. **Most shared hosting doesn't allow long-running
background processes** — the host will kill them.

The practical workaround, and what this project is set up for by default
(`QUEUE_CONNECTION=database`): use a **cron job** to process the queue in
short bursts instead of one continuous worker:

```
* * * * * php /home/yourusername/yourproject/artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

This runs every minute, processes whatever's waiting, and exits cleanly
(`--stop-when-empty`) rather than staying alive — which is exactly what a
cron-based host expects. Add this through cPanel's "Cron Jobs" tool, not
by editing crontab directly (most shared hosts don't allow SSH access to
crontab even when they allow a cron *tool* in the panel).

If your host genuinely doesn't offer cron at all, the fallback is setting
`QUEUE_CONNECTION=sync` in `.env` — every image upload then processes
inline within the request instead of being queued. Slower per-upload,
but correct, and needs zero infrastructure.

## Laravel Scheduler

The spec calls for Laravel's Scheduler (`routes/console.php`). Wire it up
the same way as the queue — one cron entry, running every minute, and
Laravel decides internally what actually needs to run:

```
* * * * * php /home/yourusername/yourproject/artisan schedule:run >> /dev/null 2>&1
```

This project doesn't currently define any scheduled tasks in
`routes/console.php` beyond the default `inspire` command — this entry is
here so the infrastructure is ready the moment you add one (e.g., a future
"publish scheduled posts" task reading the `scheduled_at` column already
on every blog).

## File permissions

`storage/` and `bootstrap/cache/` need to be writable by whatever user
your web server runs as (commonly `www-data`, `apache`, or your cPanel
account's own user, depending on host). If you get permission-denied
errors in `storage/logs/laravel.log`:

```bash
chmod -R 775 storage bootstrap/cache
```

(Exact ownership depends on your host's setup — cPanel-based shared
hosting usually already runs PHP as your account's own user, in which
case the default permissions from unzipping are already correct and this
step is a no-op.)

## Post-deploy checklist

- [ ] `.env` has `APP_DEBUG=false` and real `APP_KEY`
- [ ] `php artisan migrate --seed` run (or SQL imported) and the seeded
      admin password changed
- [ ] `php artisan storage:link` run — test by uploading one image
- [ ] `php artisan optimize` run
- [ ] Cron entries added for `queue:work` and `schedule:run`
- [ ] Visit `/sitemap.xml`, `/rss.xml`, `/robots.txt` to confirm the
      public routes from Phase 6 resolve correctly
- [ ] Visit `/up` (Laravel's built-in health check route, registered in
      `bootstrap/app.php`) — should return a 200
