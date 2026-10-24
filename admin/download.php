<?php
declare(strict_types=1);
require __DIR__.'/_bootstrap.php';
admin_require_login();
$id=clean_text((string)($_GET['ticket']??''),32);
if (!$id || !preg_match('/^TKT-[0-9]{8}-[A-F0-9]{6}$/',$id)) { http_response_code(400); exit('Invalid ticket ID.'); }
$q=db()->prepare('SELECT attachment_path,attachment_name FROM tickets WHERE ticket_id=? LIMIT 1'); $q->execute([$id]); $r=$q->fetch();
if (!$r || empty($r['attachment_path'])) { http_response_code(404); exit('Attachment not found.'); }
$c=app_config(); $base=rtrim((string)($c['private_upload_dir']??''),'/'); $name=basename((string)$r['attachment_path']);
if (!$base || !$name || !preg_match('/^[a-f0-9]{48}\.[a-z0-9]{2,5}$/',$name)) { http_response_code(404); exit('Attachment not found.'); }
$path=$base.'/'.$name;
if (!is_file($path) || !is_readable($path)) { http_response_code(404); exit('Attachment not found.'); }
$download=basename((string)($r['attachment_name']??'attachment')); $download=preg_replace('/[^A-Za-z0-9._ -]/','_', $download) ?: 'attachment';
header('Content-Type: application/octet-stream'); header('X-Content-Type-Options: nosniff'); header('Content-Disposition: attachment; filename="'.str_replace('"','',$download).'"'); header('Content-Length: '.filesize($path)); header('Cache-Control: no-store'); readfile($path); exit;
