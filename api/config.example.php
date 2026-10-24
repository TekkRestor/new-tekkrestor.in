<?php
// Copy to config.php. Store secrets securely and never commit config.php to a public repository.
return [
  'db_host' => 'localhost',
  'db_name' => 'CPANEL_DATABASE_NAME',
  'db_user' => 'CPANEL_DATABASE_USER',
  'db_pass' => 'REPLACE_WITH_A_LONG_RANDOM_DATABASE_PASSWORD',
  'support_email' => 'contact@tekkrestor.in',
  'from_email' => 'support@tekkrestor.in',
  'site_url' => 'https://tekkrestor.in',
  // Use an absolute path OUTSIDE public_html, e.g. /home/CPANEL_USERNAME/tekkrestor_private_uploads
  'private_upload_dir' => '/home/CPANEL_USERNAME/tekkrestor_private_uploads',
  'max_upload_bytes' => 5 * 1024 * 1024,
  // Optional secret for pseudonymizing rate-limit keys; set a random value unique to this deployment.
  'rate_limit_secret' => 'REPLACE_WITH_A_LONG_RANDOM_SECRET',
];
