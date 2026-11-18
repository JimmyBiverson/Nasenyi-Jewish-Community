# Nasenyi Jewish Community

The official website for the Nasenyi Abayudaya Community showcases its history, community life, stories, events, and ways to give. The Laravel app includes a staff dashboard for publishing and managing the site.

## Run locally with Laragon

Use PHP 8.5, Composer, Node.js 22.12 or later, and MySQL. Keep an existing `.env` and application key. For a fresh checkout, copy `.env.example` to `.env`, configure the MySQL database `nasenyi`, and run `php artisan key:generate` once.

```powershell
composer install
npm ci
npm run build
php artisan migrate --seed
php artisan storage:link
php artisan nasenyi:admin
php artisan serve
```

Open the [public site](http://127.0.0.1:8000/) or [staff login](http://127.0.0.1:8000/admin/login). The staff setup command prompts privately for the password. The seed imports public editorial content and preserves records already created or edited. This application uses Laravel migrations for `nasenyi`. Keep the WordPress SQL backup separate.

## Staff access

Administrators manage content, site settings, staff accounts, contact messages, and donation reports. Editors manage content, gallery photos, events, and donation causes. Set the recipient under **Settings → Contact email** and configure Laravel's mail transport to enable contact notification delivery. Contact submissions appear in the Inbox even without an email recipient. Event dates entered in the dashboard use East Africa Time.

## Test donations with Pesapal

Payments start disabled. Set sandbox credentials in `.env`, use `PESAPAL_ENVIRONMENT=sandbox`, and configure `PESAPAL_PUBLIC_URL` with a public HTTPS endpoint for the local sandbox. Register that endpoint by running `php artisan pesapal:register-ipn` and set the returned `PESAPAL_NOTIFICATION_ID`. Set `PESAPAL_ENABLED=true` once these are in place. Enable USD in `PESAPAL_CURRENCIES` only when it is available to the merchant account.

Use `php artisan queue:work database` to process verified donation receipts. Keep the local mailer set to `log` while testing transaction emails. Keep credentials, backups, and database records outside version control.

## Checks

```powershell
php artisan test
npm run build
```

The suite uses isolated SQLite databases and mocked Pesapal responses. Local pages send no-index metadata and serve a disallow-all robots file. Set the canonical HTTPS `APP_URL`, mail transport, queue worker, storage link, and production indexing environment only when preparing a deployment.
