<?php
declare(strict_types=1); require __DIR__.'/_bootstrap.php';
if (!empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$error='';
$c=app_config();
$ipHash=hash_hmac('sha256',(string)($_SERVER['REMOTE_ADDR']??'unknown'),(string)($c['rate_limit_secret']??$c['db_pass']));
try {
  db()->prepare('DELETE FROM admin_login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 DAY)')->execute();
  $attemptQuery=db()->prepare('SELECT COUNT(*) FROM admin_login_attempts WHERE ip_hash=? AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
  $attemptQuery->execute([$ipHash]); $attemptCount=(int)$attemptQuery->fetchColumn();
} catch (Throwable $e) { error_log('Admin login throttle error: '.$e->getMessage()); http_response_code(503); exit('Staff login is temporarily unavailable.'); }
if ($_SERVER['REQUEST_METHOD']==='POST') {
  admin_verify_csrf();
  if ($attemptCount>=5) { http_response_code(429); $error='Too many login attempts from this connection. Wait 15 minutes and try again.'; }
  else {
    $u=trim((string)($_POST['username']??'')); $pw=(string)($_POST['password']??'');
    $s=db()->prepare('SELECT id,username,password_hash FROM admin_users WHERE username=? LIMIT 1'); $s->execute([$u]); $a=$s->fetch();
    if ($a && password_verify($pw,$a['password_hash'])) {
      session_regenerate_id(true); $_SESSION['admin_id']=(int)$a['id']; $_SESSION['admin_username']=$a['username']; $_SESSION['csrf']=bin2hex(random_bytes(32)); $_SESSION['last_activity']=time();
      db()->prepare('DELETE FROM admin_login_attempts WHERE ip_hash=?')->execute([$ipHash]);
      db()->prepare('UPDATE admin_users SET last_login_at=NOW() WHERE id=?')->execute([$a['id']]); header('Location: index.php'); exit;
    }
    db()->prepare('INSERT INTO admin_login_attempts(ip_hash,created_at) VALUES(?,NOW())')->execute([$ipHash]); usleep(350000); $error='Invalid username or password.';
  }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Staff Login | TekkRestor</title><link rel="stylesheet" href="/assets/style.css"></head><body><main class="container" style="max-width:560px;padding:4rem 1rem"><div class="form-card"><a class="brand" href="/"><span class="tekk">Tekk</span><span class="restor">Restor</span></a><h1>Support staff login</h1><?php if(isset($_GET['setup'])): ?><div class="success">Administrator created. Initial setup endpoint has been removed.</div><?php endif; ?><?php if($error): ?><div class="notice"><?=h($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=h(admin_csrf())?>"><div class="field"><label for="username">Username</label><input id="username" name="username" required autocomplete="username"></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div><button class="btn" type="submit">Sign in</button></form></div></main></body></html>