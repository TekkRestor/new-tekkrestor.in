<?php
declare(strict_types=1);
require __DIR__.'/db.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') json_out(['error'=>'Method not allowed.'],405);
try {
  enforce_ticket_rate_limit('ticket_lookup',20,15);
  $id=clean_text((string)($_POST['ticket_id']??''),40); $token=(string)($_POST['token']??'');
  if (!$id || !$token || strlen($token)>200) json_out(['error'=>'Ticket ID and tracking token are required.'],422);
  $s=db()->prepare('SELECT ticket_id,tracking_token_hash,status,category,subject,updated_at FROM tickets WHERE ticket_id=? LIMIT 1');
  $s->execute([$id]); $r=$s->fetch();
  if (!$r || !password_verify($token,$r['tracking_token_hash'])) json_out(['error'=>'Ticket not found or credentials are incorrect.'],404);
  unset($r['tracking_token_hash']); json_out($r);
} catch (Throwable $e) { error_log('Ticket lookup error: '.$e->getMessage()); json_out(['error'=>'Ticket tracking is temporarily unavailable.'],500); }
