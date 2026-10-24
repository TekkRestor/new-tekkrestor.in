<?php
declare(strict_types=1);
function app_config(): array {
  static $config = null;
  if ($config !== null) return $config;
  $path = __DIR__ . '/config.php';
  if (!is_file($path)) throw new RuntimeException('Server configuration is not installed.');
  $config = require $path;
  return $config;
}
function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;
  $c = app_config();
  $pdo = new PDO('mysql:host='.$c['db_host'].';dbname='.$c['db_name'].';charset=utf8mb4', $c['db_user'], $c['db_pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
  return $pdo;
}
function json_out(array $data, int $status=200): never {
  http_response_code($status); header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store'); header('X-Content-Type-Options: nosniff'); echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE); exit;
}
function clean_text(string $s, int $max): string {
  $s = trim(strip_tags($s));
  if (function_exists('mb_strlen') && mb_strlen($s) > $max) return mb_substr($s, 0, $max);
  return strlen($s) > $max ? substr($s, 0, $max) : $s;
}
function client_ip_hash(): string {
  // Do not trust X-Forwarded-For unless the hosting proxy is explicitly configured.
  return hash_hmac('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), (string)(app_config()['rate_limit_secret'] ?? app_config()['db_pass']));
}
function enforce_ticket_rate_limit(string $scope, int $limit=5, int $minutes=60): void {
  $pdo = db(); $pdo->prepare('DELETE FROM ticket_rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 DAY)')->execute();
  $hash = client_ip_hash();
  $minutes = max(1, min(1440, (int)$minutes));
  $q = $pdo->prepare('SELECT COUNT(*) FROM ticket_rate_limits WHERE ip_hash=? AND scope=? AND created_at >= DATE_SUB(NOW(), INTERVAL '.$minutes.' MINUTE)');
  $q->execute([$hash, $scope]);
  if ((int)$q->fetchColumn() >= $limit) json_out(['error'=>'Too many requests from this connection. Please wait before trying again or contact support.'],429);
  $pdo->prepare('INSERT INTO ticket_rate_limits(ip_hash,scope,created_at) VALUES(?,?,NOW())')->execute([$hash, $scope]);
}
