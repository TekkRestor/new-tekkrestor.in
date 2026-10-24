CREATE TABLE tickets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ticket_id VARCHAR(32) NOT NULL UNIQUE,
  tracking_token_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  company VARCHAR(160) NULL,
  category VARCHAR(100) NOT NULL,
  priority ENUM('Urgent','High','Medium','Low') NOT NULL DEFAULT 'Medium',
  subject VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  device_type VARCHAR(80) NULL,
  attachment_path VARCHAR(255) NULL,
  attachment_name VARCHAR(255) NULL,
  status ENUM('Open','In Progress','Waiting for Customer','Resolved','Closed') NOT NULL DEFAULT 'Open',
  admin_notes TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_ticket_email (email),
  INDEX idx_status_priority (status, priority),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_audit_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ticket_id VARCHAR(32) NOT NULL,
  admin_user_id BIGINT UNSIGNED NULL,
  actor VARCHAR(120) NOT NULL,
  action VARCHAR(80) NOT NULL,
  detail VARCHAR(500) NULL,
  old_value VARCHAR(255) NULL,
  new_value VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_ticket_created (ticket_id, created_at),
  CONSTRAINT fk_audit_admin FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_rate_limits (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip_hash CHAR(64) NOT NULL,
  scope VARCHAR(40) NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_rate_ip_scope_created (ip_hash, scope, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip_hash CHAR(64) NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_admin_login_ip_created (ip_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
