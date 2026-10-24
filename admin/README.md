# Staff ticket dashboard

1. Import the current `database.sql`.
2. Configure `api/config.php`, PHP 8.1+ and PDO MySQL.
3. Visit `/admin/setup.php` once and create a unique strong admin password.
4. Immediately delete `admin/setup.php` from the server.
5. Sign in at `/admin/login.php`.
6. Confirm HTTPS is active before handling customer information.

The dashboard supports searching, status filtering, status changes, internal notes and an audit record of status changes. It displays customer contact details and must be restricted to authorized staff. Use a unique admin password, keep PHP updated, back up the database, and test recovery. SMTP/email notifications on status updates are not included.
