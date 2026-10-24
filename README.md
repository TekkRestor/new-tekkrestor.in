# TekkRestor Website — Pre-Launch Hardening Package

Microsoft Fluent-inspired independent TekkRestor design. Theme tokens: `#0F6CBD` primary, `#115EA3` hover, `#0C3B5E` deep blue, `#EBF3FC` pale blue, white/slate neutrals, and `#107C10` sustainability accent. This is not an official Microsoft website or product.

## Included
- Responsive multi-page static website and service landing pages.
- LocalBusiness/Service JSON-LD, canonical metadata, sitemap and robots rules.
- Privacy policy starter page (review and complete before launch).
- PHP/MySQL ticket creation and private-token lookup.
- Ticket confirmation shows both ticket ID and private tracking token; copy and print controls.
- Rate limiting and honeypot for public ticket submissions.
- Staff dashboard with session login, password hash, CSRF validation, login throttling, ticket search/filter, status updates, internal notes and audit events.
- Attachment validation and configurable storage outside the public web root.

## Deployment on Hostinger/cPanel
1. Back up the live site. Deploy this package to a staging subdomain first.
2. Confirm the production business address, pin code, phone, email, service area and exact map coordinates. The current coordinate is an unverified approximate value; change it in the JSON-LD if needed.
3. Upload the contents of `tekkrestor-site/` into the staging document root. Keep the final URL structure consistent with canonical tags and sitemap.
4. Create a dedicated MySQL database/user in cPanel. Import `database.sql` using phpMyAdmin.
5. Copy `api/config.example.php` to `api/config.php`. Enter database credentials, verified email addresses, site URL, and an absolute `private_upload_dir` outside `public_html` (example `/home/CPANEL_USERNAME/tekkrestor_private_uploads`). Set a random `rate_limit_secret`. The staff account is created through `/admin/setup.php` after the database is configured; use a unique password of at least 14 characters. Do not leave placeholders.
6. Generate a password hash on a PHP-enabled machine/terminal: `php -r "echo password_hash('YOUR-LONG-UNIQUE-PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"`. Use a unique long password and store secrets outside source control. The config file is denied by `.htaccess`, but the best practice is to store configuration outside the public document root where your hosting allows it.
7. Ensure PHP 8.1+ with PDO MySQL, Fileinfo and mbstring enabled. Ensure the configured private upload directory is writable by PHP and is outside the web root.
8. Visit `/admin/setup.php` over HTTPS once to create the first administrator. The setup endpoint attempts to delete itself after successful creation; verify that it is gone, and manually delete it through File Manager if it remains. Then sign in at `/admin/login.php`. Test ticket creation, status updates, search/filter, secure attachment download, token lookup and logout. Restrict staff access further at hosting/firewall level if possible.
9. Email currently uses PHP `mail()` as a transport handoff. It is **not verified delivery** and may be unreliable. For production, replace the mail transport with authenticated SMTP using a maintained mailer (for example PHPMailer installed via Composer or hosting-provided SMTP integration), then test delivery, bounces and logs. Do not rely on the current mail() path as proof of delivery. Configure SPF, DKIM and DMARC for your sending domain.
10. Set up automated database backups and test restoration. Define ticket/attachment retention and deletion. Do not ask users to submit passwords or unnecessary sensitive data.
11. Confirm HTTPS and redirect policy on the actual host. Test every URL, form and endpoint on staging before replacing the live site.
12. Submit `https://tekkrestor.in/sitemap.xml` in Google Search Console only after production deployment and URL validation.

## Pre-launch checklist
- [ ] Verify actual business address, public access status and coordinates.
- [ ] Replace all configuration placeholders; use strong secrets.
- [ ] Configure and test authenticated SMTP, SPF, DKIM, DMARC.
- [ ] Confirm upload directory is outside the document root.
- [ ] Test all ticket workflows and staff authentication.
- [ ] Complete privacy notice with effective date, retention period and actual provider details.
- [ ] Validate structured data with Google Rich Results Test and Schema Markup Validator.
- [ ] Crawl for broken links, 404s, redirects, canonical mismatch and duplicate titles.
- [ ] Run mobile/desktop PageSpeed Insights on every priority landing page.
- [ ] Confirm backups, recovery, error logs and abuse monitoring.
- [ ] Publish only genuine testimonials and substantiated service/availability claims.
- [ ] Do not claim certified recycling or certified data destruction unless applicable authorization and downstream partner evidence are confirmed.

## Honest readiness note
This package hardens the code and adds important workflows, but cannot be considered fully production-verified until database credentials, real hosting, SMTP, exact location details, operational SLA, privacy practices and live performance tests are supplied and validated. A PageSpeed score of 95+ or Google ranking is not guaranteed. The objective is a substantially improved pre-hosting baseline, not a claim that every external/operational requirement is already met.
