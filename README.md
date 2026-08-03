# EMB Chronicles

Custom fertility education, consultation, community, events, FIYFF grant, and administration platform built with PHP 8.2, MySQL/MariaDB, Tailwind CSS, and vanilla JavaScript.

Approved design source: [EMB Chronicles on Google Stitch](https://stitch.withgoogle.com/projects/10184766117659790583)

## Local setup

1. Copy `.env.example` to `.env` and update the application URL and database credentials.
2. Create the database:

   ```powershell
   C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root -e "source database/schema.sql"
   C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root emb_chronicles -e "source database/seed.sql"
   C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root emb_chronicles -e "source database/migrations/20260731_grant_forms.sql"
   ```

   For an existing installation created before appointment payments and email delivery were added, run:

   ```powershell
   C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root emb_chronicles -e "source database/migrations/20260731_email_payments.sql"
   C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root emb_chronicles -e "source database/migrations/20260731_rbac.sql"
   C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root emb_chronicles -e "source database/migrations/20260731_grant_forms.sql"
   C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root emb_chronicles -e "source database/migrations/20260801_editable_page_media.sql"
   C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root emb_chronicles -e "source database/migrations/20260801_availability_maintenance.sql"
   C:\xampp\mysql\bin\mysql.exe --default-character-set=utf8mb4 -u root emb_chronicles -e "source database/migrations/20260803_appointment_types.sql"
   ```

3. Install PHP and frontend dependencies, then build the stylesheet:

   ```powershell
   php composer.phar install
   npm install
   npm run css:build
   ```

4. Start the development server:

   ```powershell
   php -d extension=gd -S 127.0.0.1:8080 -t public public/router.php
   ```

5. Open `http://127.0.0.1:8080`.

## Initial administrator

- Email: `admin@embchronicles.com`
- Password: `ChangeMe123!`

Change this password immediately before the site is made public. Generate a new hash with:

```powershell
php -r "echo password_hash('your-new-password', PASSWORD_DEFAULT), PHP_EOL;"
```

Then update the `admins.password_hash` value in MySQL.

The initial administrator is assigned the protected **Super Administrator** role.

## Managing site images

Every public-facing image can be replaced or removed from the admin panel:

- **Hero slides** manages homepage hero images.
- **Page content** manages the homepage welcome image, both About page images, and the FIYFF hero image.
- **Services** manages service covers and gallery images.
- **Events** manages event and grant covers and gallery images.
- **Testimonials** manages client photos.
- **Site settings** manages the header logo and default social sharing image.

Image fields accept a JPG, PNG, or WebP upload, a local `/uploads/...` path, or a full external URL. Add descriptive alt text whenever an image is present. Removing an image also removes its alt text; removing a hero image deactivates that slide.

Under **Admin → Site settings → Search and social sharing**, set the homepage meta title, meta description, and default link-preview image. The site emits Open Graph and Twitter Card metadata automatically. Individual service and event pages use their own SEO descriptions and cover images, falling back to these global settings when no cover is present.

## Roles and administrator access

Open **Admin → Administrators** to create accounts, activate or deactivate access, reset passwords, and assign one or more roles.

Open **Admin → Roles & access** to create roles and select the admin areas they may use. Starter roles include:

- Super Administrator — unrestricted access, including users and roles;
- Content Manager — events, services, hero slides, testimonials, and page content;
- Client Services — contacts, appointments, payment history, and email logs;
- Grant Reviewer — FIYFF grant applications.

Permissions are enforced on every admin request as well as in the navigation. Administrators cannot grant permissions they do not possess, edit their own role assignment, deactivate themselves, or remove the final active super administrator.

## Production requirements

- PHP 8.2+ with PDO MySQL, fileinfo, mbstring, and GD enabled
- MySQL 8+ or a compatible MariaDB release
- Web root pointed to `public/`
- HTTPS with `SESSION_SECURE=true`
- Writable `public/uploads/` and `storage/logs/`
- A production `.env` that is not web-accessible

Uploaded JPG, PNG, and WebP files are MIME-validated, size-validated, and converted into responsive WebP variants when GD is enabled.

Grant application documents are stored outside the public web root under `storage/grant-documents/`. They are MIME- and size-validated and can be downloaded only by signed-in administrators with the grant-management permission.

## Managed grant forms

Open **Admin → Grant forms** to create a grant application, link it to a published Grant Program event, set opening and closing dates, and build multi-step sections from text, choice, checkbox, and protected file-upload fields.

Published forms use:

```text
https://your-production-domain.example/grants/{form-slug}/apply
```

Applicants can save typed answers on their current device, review all sections, upload protected documents, and receive an SMTP confirmation with their reference code. Applications and documents are reviewed under **Admin → Grant applications**.

## Email delivery

Open **Admin → Site settings → SMTP email delivery**. Configure the SMTP host, port, encryption, username, password, sender details, and admin notification address. Save and send a test email before enabling confirmations.

When enabled, the website sends:

- contact-form acknowledgements and admin notifications;
- appointment request or paid-booking confirmations;
- appointment schedule/status updates selected by an administrator;
- FIYFF application confirmations;
- newsletter welcome messages.

Delivery attempts are available under **Admin → Email log**.

## Paystack appointment payments

Open **Admin → Site settings → Paystack appointment payments**. Start with Paystack test public and secret keys, set the appointment fee in naira, and add this webhook URL in the Paystack dashboard:

```text
https://your-production-domain.example/payments/paystack/webhook
```

The application initializes checkout on the server, redirects clients to Paystack’s hosted checkout, verifies callbacks, validates webhook signatures, checks the exact amount and currency, and records every payment attempt. Appointments can be filtered and managed under **Admin → Appointments**.

Use HTTPS and test keys until the callback, webhook, payment status, and confirmation email all work on the production domain.

## Appointment availability

Open **Admin → Availability** to enable or pause online booking, set the future booking window, minimum notice, daily booking limit, recurring weekly time slots, per-slot capacity, and blocked dates. The public appointment form shows only configured times for the selected weekday. The server rechecks blocked dates and capacity inside a database transaction before accepting a request, so concurrent requests cannot exceed a slot limit.

## CSV import and export

Open **Admin → Import / export** to transfer services, events, testimonials, newsletter subscribers, and grant applications. Available datasets follow the administrator's existing permissions. Export a fresh CSV before editing so the required headers stay intact. Imports are limited to 5 MB, update matching records, and roll back completely if any row is invalid. Grant exports exclude protected application documents.

## Maintenance and deployment messaging

Open **Admin → Site settings → Maintenance and deployment status** to configure the public status message or enable maintenance mode with an optional expected return time and external status-page link. Maintenance mode returns HTTP 503 while keeping administrator access, `/health`, and Paystack callbacks available. The custom 404 page provides navigation and displays the configured website status.

## Coolify deployment

The repository includes a production multi-stage `Dockerfile`. It builds the Tailwind stylesheet, installs production Composer dependencies, runs PHP 8.2 with Apache, enables the required PHP extensions, and exposes port `80`.

Create a Coolify application from this GitHub repository and select **Dockerfile** as the build pack. Set the health-check path to:

```text
/health
```

Connect the application to a MySQL 8 or compatible MariaDB database, then configure these environment variables in Coolify:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_TIMEZONE=Africa/Lagos
SESSION_SECURE=true

DB_HOST=your-internal-mysql-host
DB_PORT=3306
DB_DATABASE=emb_chronicles
DB_USERNAME=emb_chronicles
DB_PASSWORD=use-a-strong-password
DB_BOOTSTRAP=true
DB_WAIT_TIMEOUT=60

UPLOAD_MAX_MB=8
GRANT_UPLOAD_TOTAL_MB=18
```

`DB_BOOTSTRAP=true` waits for MySQL and initializes an empty database on first startup. On later deployments it detects existing tables and skips completed initialization. Set it to `false` if database schema changes are managed separately.

Configure persistent storage for both paths so uploads survive deployments:

```text
/var/www/html/public/uploads
/var/www/html/storage
```

The container does not need SMTP or Paystack secrets as environment variables; configure them after deployment under **Admin → Site settings**. Change the seeded administrator password immediately after the first deployment.

For Paystack, configure the production webhook as:

```text
https://your-domain.example/payments/paystack/webhook
```
