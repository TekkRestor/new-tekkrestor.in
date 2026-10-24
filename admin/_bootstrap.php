<?php
declare(strict_types=1);
require_once __DIR__ . '/../api/db.php';
$c = app_config();
session_name((string)($c['admin_session_name'] ?? 'tekkrestor_admin'));
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params(['lifetime'=>0,'path'=>'/admin/','secure'=>$secure,'httponly'=>true,'samesite'=>'Strict']);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
function admin_require_login(): void { if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; } }
function admin_csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function admin_verify_csrf(): void { if (!hash_equals((string)($_SESSION['csrf']??''),(string)($_POST['csrf']??''))) { http_response_code(403); exit('Invalid security token. Reload and try again.'); } }
function h(string $s): string { return htmlspecialchars($s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
