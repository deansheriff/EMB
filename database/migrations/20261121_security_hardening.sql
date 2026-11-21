USE emb_chronicles;

-- Database-backed rate limiting for admin sign-in and public forms.
CREATE TABLE IF NOT EXISTS rate_limits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bucket VARCHAR(60) NOT NULL,
  key_hash CHAR(64) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rate_limits_lookup (bucket, key_hash, created_at),
  INDEX idx_rate_limits_created (created_at)
) ENGINE=InnoDB;

-- Deleting an event must never silently delete its grant applications.
SET @grant_fk_cascade = (
  SELECT COUNT(*) FROM information_schema.referential_constraints
  WHERE constraint_schema = DATABASE()
    AND table_name = 'grant_applications'
    AND constraint_name = 'fk_grant_event'
    AND delete_rule = 'CASCADE'
);
SET @grant_fk_sql = IF(@grant_fk_cascade > 0, 'ALTER TABLE grant_applications DROP FOREIGN KEY fk_grant_event', 'SELECT 1');
PREPARE grant_fk_statement FROM @grant_fk_sql;
EXECUTE grant_fk_statement;
DEALLOCATE PREPARE grant_fk_statement;

SET @grant_fk_missing = (
  SELECT COUNT(*) = 0 FROM information_schema.referential_constraints
  WHERE constraint_schema = DATABASE()
    AND table_name = 'grant_applications'
    AND constraint_name = 'fk_grant_event'
);
SET @grant_fk_sql = IF(
  @grant_fk_missing,
  'ALTER TABLE grant_applications ADD CONSTRAINT fk_grant_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE RESTRICT',
  'SELECT 1'
);
PREPARE grant_fk_statement FROM @grant_fk_sql;
EXECUTE grant_fk_statement;
DEALLOCATE PREPARE grant_fk_statement;

-- Speeds up the per-recipient daily cap on automatic emails.
SET @email_index_missing = (
  SELECT COUNT(*) = 0 FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'email_logs' AND index_name = 'idx_email_recipient'
);
SET @email_index_sql = IF(@email_index_missing, 'ALTER TABLE email_logs ADD INDEX idx_email_recipient (recipient, created_at)', 'SELECT 1');
PREPARE email_index_statement FROM @email_index_sql;
EXECUTE email_index_statement;
DEALLOCATE PREPARE email_index_statement;

-- Branding (logo and social sharing image) can be delegated without full site settings.
INSERT INTO permissions (slug, name, group_name, description) VALUES
('branding.manage', 'Manage branding', 'Content', 'Change the site logo and the default social sharing image.')
ON DUPLICATE KEY UPDATE name = VALUES(name), group_name = VALUES(group_name), description = VALUES(description);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'content-manager' AND p.slug = 'branding.manage';

INSERT INTO site_settings (`key`, `value`, `type`) VALUES ('security_hardening_migration', '1', 'boolean')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
