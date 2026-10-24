<?php
declare(strict_types=1);
require __DIR__.'/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error'=>'Method not allowed.'],405);
try {
  $c = app_config();
  if (!empty($_POST['website'] ?? '')) json_out(['error'=>'Unable to process request.'],422); // honeypot
  enforce_ticket_rate_limit('ticket_create', 5, 60);
  $name=clean_text((string)($_POST['full_name']??''),120);
  $email=filter_var(trim((string)($_POST['email']??'')),FILTER_VALIDATE_EMAIL);
  $phone=clean_text((string)($_POST['phone']??''),30);
  $company=clean_text((string)($_POST['company']??''),160);
  $category=clean_text((string)($_POST['category']??''),100);
  $priority=(string)($_POST['priority']??'Medium');
  $subject=clean_text((string)($_POST['subject']??''),180);
  $description=clean_text((string)($_POST['description']??''),8000);
  $device=clean_text((string)($_POST['device_type']??'Not specified'),80);
  $allowedPriority=['Urgent','High','Medium','Low'];
  $allowedCategories=['Computer & Laptop Repair','Managed IT Support','Networking & Wi-Fi','CCTV & Surveillance','Cloud & Cybersecurity','Email / Microsoft 365 / Google Workspace','Printer / Peripheral','E-Waste Assessment','Other'];
  if (!$name || !$email || !$phone || !$subject || strlen($description)<10 || !in_array($priority,$allowedPriority,true) || !in_array($category,$allowedCategories,true) || ($_POST['consent']??'')!=='yes') json_out(['error'=>'Please complete all required fields correctly.'],422);
  $ticket='TKT-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
  $token=bin2hex(random_bytes(24)); $tokenHash=password_hash($token,PASSWORD_DEFAULT); $filePath=null; $fileName=null;
  if (!empty($_FILES['attachment']['name'])) {
    $f=$_FILES['attachment'];
    if (($f['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || (int)$f['size']>(int)$c['max_upload_bytes'] || !is_uploaded_file($f['tmp_name'])) json_out(['error'=>'Attachment upload failed or exceeds the 5 MB limit.'],422);
    $allowed=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','pdf'=>'application/pdf','txt'=>'text/plain','log'=>'text/plain','csv'=>'text/plain','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    $ext=strtolower(pathinfo((string)$f['name'],PATHINFO_EXTENSION)); $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset($allowed[$ext]) || $mime!==$allowed[$ext]) json_out(['error'=>'File type not allowed.'],422);
    $dir=(string)($c['private_upload_dir']??''); $docRoot=realpath((string)($_SERVER['DOCUMENT_ROOT']??''));
    if (!$dir || !str_starts_with($dir,'/') || ($docRoot && (str_starts_with(realpath($dir) ?: $dir, $docRoot) || str_starts_with($dir, rtrim($docRoot,'/').'/')))) json_out(['error'=>'Private attachment storage is not configured outside the public web root. Please contact support.'],503);
    if (!is_dir($dir) && !mkdir($dir,0700,true)) throw new RuntimeException('Unable to prepare secure upload storage.');
    @chmod($dir,0700); $fileName=clean_text(basename((string)$f['name']),180); $stored=bin2hex(random_bytes(24)).'.'.$ext;
    if (!move_uploaded_file($f['tmp_name'],rtrim($dir,'/').'/'.$stored)) throw new RuntimeException('Unable to store attachment.');
    @chmod(rtrim($dir,'/').'/'.$stored,0600); $filePath=$stored;
  }
  $stmt=db()->prepare('INSERT INTO tickets(ticket_id,tracking_token_hash,full_name,email,phone,company,category,priority,subject,description,device_type,attachment_path,attachment_name,status,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
  $stmt->execute([$ticket,$tokenHash,$name,$email,$phone,$company,$category,$priority,$subject,$description,$device,$filePath,$fileName,'Open']);
  db()->prepare('INSERT INTO ticket_audit_log(ticket_id,admin_user_id,actor,action,detail,created_at) VALUES(?,NULL,?,?,?,NOW())')->execute([$ticket,'system','created','Ticket submitted by customer']);
  $site=rtrim((string)($c['site_url']??'https://tekkrestor.in'),'/');
  $body="New support ticket: $ticket
Name: $name
Email: $email
Phone: $phone
Company: $company
Category: $category
Priority: $priority
Subject: $subject
Description:
$description
Attachment: ".($fileName?'Yes':'No')."

Ticket details are available in the staff dashboard.";
  $from=filter_var((string)$c['from_email'],FILTER_VALIDATE_EMAIL) ?: 'contact@tekkrestor.in';
  $headers="From: TekkRestor Support <{$from}>
Reply-To: ".str_replace(["
","
"],'',(string)$email)."
Content-Type: text/plain; charset=UTF-8";
  $customerBody="Your TekkRestor support ticket has been received.

Ticket ID: $ticket
Priority: $priority

Private tracking token: $token
Keep this token private. Anyone with both the ticket ID and token can view ticket status.
Track your ticket: $site/tickets/lookup.html

Initial response targets are subject to your support agreement and business hours.

TekkRestor Support";
  $mailAdmin=@mail((string)$c['support_email'],'New support ticket '.$ticket,$body,$headers);
  $mailCustomer=@mail((string)$email,'TekkRestor ticket confirmation '.$ticket,$customerBody,"From: TekkRestor Support <{$from}>
Content-Type: text/plain; charset=UTF-8");
  json_out(['ticket_id'=>$ticket,'tracking_token'=>$token,'message'=>'Ticket saved. '.(($mailAdmin&&$mailCustomer)?'Email was handed to the hosting mail system; delivery is not guaranteed.':'Email delivery could not be confirmed. Save the ticket ID and tracking token now.')],201);
} catch (Throwable $e) { error_log('Ticket create error: '.$e->getMessage()); json_out(['error'=>'A server error prevented ticket submission. Please contact support directly.'],500); }
