USE emb_chronicles;

ALTER TABLE appointments
  ADD COLUMN booking_code VARCHAR(48) NULL AFTER id,
  MODIFY COLUMN status ENUM('pending_payment','new','contacted','scheduled','completed','cancelled') NOT NULL DEFAULT 'new',
  ADD COLUMN scheduled_at DATETIME NULL AFTER status,
  ADD COLUMN admin_notes TEXT NULL AFTER scheduled_at,
  ADD COLUMN amount_due BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER admin_notes,
  ADD COLUMN currency CHAR(3) NOT NULL DEFAULT 'NGN' AFTER amount_due,
  ADD COLUMN payment_status ENUM('not_required','pending','paid','failed','refunded') NOT NULL DEFAULT 'not_required' AFTER currency,
  ADD COLUMN payment_reference VARCHAR(100) NULL AFTER payment_status,
  ADD COLUMN paid_at DATETIME NULL AFTER payment_reference,
  ADD COLUMN email_confirmation_sent_at DATETIME NULL AFTER paid_at,
  ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

UPDATE appointments
SET booking_code = CONCAT('EMB-', UPPER(SUBSTRING(SHA2(CONCAT(id, created_at, RAND()), 256), 1, 12)))
WHERE booking_code IS NULL OR booking_code = '';

ALTER TABLE appointments
  MODIFY COLUMN booking_code VARCHAR(48) NOT NULL,
  ADD UNIQUE KEY uq_appointment_booking_code (booking_code),
  ADD UNIQUE KEY uq_appointment_payment_reference (payment_reference),
  ADD INDEX idx_appointments_payment (payment_status, created_at);

CREATE TABLE IF NOT EXISTS appointment_payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  appointment_id BIGINT UNSIGNED NOT NULL,
  reference VARCHAR(100) NOT NULL UNIQUE,
  access_code VARCHAR(100) NULL,
  authorization_url VARCHAR(500) NULL,
  amount BIGINT UNSIGNED NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'NGN',
  status ENUM('initialized','pending','success','failed','abandoned','reversed') NOT NULL DEFAULT 'initialized',
  gateway_response VARCHAR(255) NULL,
  channel VARCHAR(60) NULL,
  paid_at DATETIME NULL,
  raw_response_json JSON NULL,
  raw_webhook_json JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_payment_appointment (appointment_id, created_at),
  INDEX idx_payment_status (status, created_at),
  CONSTRAINT fk_payment_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS email_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recipient VARCHAR(190) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  template_key VARCHAR(100) NOT NULL,
  related_type VARCHAR(80) NULL,
  related_id BIGINT UNSIGNED NULL,
  status ENUM('sent','failed','skipped') NOT NULL,
  error_message TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email_status (status, created_at),
  INDEX idx_email_related (related_type, related_id)
) ENGINE=InnoDB;

INSERT INTO site_settings (`key`, `value`, `type`) VALUES
('smtp_enabled', '0', 'boolean'),
('smtp_host', '', 'text'),
('smtp_port', '587', 'number'),
('smtp_encryption', 'tls', 'text'),
('smtp_username', '', 'text'),
('smtp_password', '', 'secret'),
('smtp_from_email', 'info@embchronicles.com', 'email'),
('smtp_from_name', 'Emb Chronicles', 'text'),
('smtp_reply_to', 'info@embchronicles.com', 'email'),
('smtp_admin_email', 'info@embchronicles.com', 'email'),
('email_confirmations_enabled', '1', 'boolean'),
('paystack_enabled', '0', 'boolean'),
('paystack_public_key', '', 'text'),
('paystack_secret_key', '', 'secret'),
('paystack_currency', 'NGN', 'text'),
('appointment_fee', '0.00', 'money')
ON DUPLICATE KEY UPDATE `type` = VALUES(`type`);
