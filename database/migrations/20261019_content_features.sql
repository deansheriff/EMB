CREATE TABLE IF NOT EXISTS blog_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  excerpt TEXT NOT NULL,
  content LONGTEXT NOT NULL,
  cover_image VARCHAR(500) NULL,
  cover_alt VARCHAR(255) NULL,
  author_name VARCHAR(190) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(320) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_blog_public (status, published_at),
  INDEX idx_blog_featured (status, is_featured, published_at)
) ENGINE=InnoDB;

SET @testimonial_column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'testimonials' AND column_name = 'submitter_email'
);
SET @testimonial_column_sql = IF(
  @testimonial_column_exists = 0,
  'ALTER TABLE testimonials ADD COLUMN submitter_email VARCHAR(190) NULL AFTER client_name',
  'SELECT 1'
);
PREPARE testimonial_column_statement FROM @testimonial_column_sql;
EXECUTE testimonial_column_statement;
DEALLOCATE PREPARE testimonial_column_statement;

SET @testimonial_column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'testimonials' AND column_name = 'source'
);
SET @testimonial_column_sql = IF(
  @testimonial_column_exists = 0,
  'ALTER TABLE testimonials ADD COLUMN source ENUM(''admin'',''public'') NOT NULL DEFAULT ''admin'' AFTER quote',
  'SELECT 1'
);
PREPARE testimonial_column_statement FROM @testimonial_column_sql;
EXECUTE testimonial_column_statement;
DEALLOCATE PREPARE testimonial_column_statement;

SET @testimonial_column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'testimonials' AND column_name = 'approval_status'
);
SET @testimonial_column_sql = IF(
  @testimonial_column_exists = 0,
  'ALTER TABLE testimonials ADD COLUMN approval_status ENUM(''pending'',''approved'',''rejected'') NOT NULL DEFAULT ''approved'' AFTER source',
  'SELECT 1'
);
PREPARE testimonial_column_statement FROM @testimonial_column_sql;
EXECUTE testimonial_column_statement;
DEALLOCATE PREPARE testimonial_column_statement;

SET @testimonial_column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'testimonials' AND column_name = 'consented_at'
);
SET @testimonial_column_sql = IF(
  @testimonial_column_exists = 0,
  'ALTER TABLE testimonials ADD COLUMN consented_at DATETIME NULL AFTER approval_status',
  'SELECT 1'
);
PREPARE testimonial_column_statement FROM @testimonial_column_sql;
EXECUTE testimonial_column_statement;
DEALLOCATE PREPARE testimonial_column_statement;

SET @testimonial_column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'testimonials' AND column_name = 'reviewed_by'
);
SET @testimonial_column_sql = IF(
  @testimonial_column_exists = 0,
  'ALTER TABLE testimonials ADD COLUMN reviewed_by BIGINT UNSIGNED NULL AFTER consented_at',
  'SELECT 1'
);
PREPARE testimonial_column_statement FROM @testimonial_column_sql;
EXECUTE testimonial_column_statement;
DEALLOCATE PREPARE testimonial_column_statement;

SET @testimonial_column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'testimonials' AND column_name = 'reviewed_at'
);
SET @testimonial_column_sql = IF(
  @testimonial_column_exists = 0,
  'ALTER TABLE testimonials ADD COLUMN reviewed_at DATETIME NULL AFTER reviewed_by',
  'SELECT 1'
);
PREPARE testimonial_column_statement FROM @testimonial_column_sql;
EXECUTE testimonial_column_statement;
DEALLOCATE PREPARE testimonial_column_statement;

SET @testimonial_name_index_exists = (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'testimonials' AND index_name = 'uq_testimonial_name'
);
SET @testimonial_name_index_sql = IF(
  @testimonial_name_index_exists > 0,
  'ALTER TABLE testimonials DROP INDEX uq_testimonial_name',
  'SELECT 1'
);
PREPARE testimonial_name_index_statement FROM @testimonial_name_index_sql;
EXECUTE testimonial_name_index_statement;
DEALLOCATE PREPARE testimonial_name_index_statement;

SET @testimonial_moderation_index_exists = (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = DATABASE() AND table_name = 'testimonials' AND index_name = 'idx_testimonials_moderation'
);
SET @testimonial_moderation_index_sql = IF(
  @testimonial_moderation_index_exists = 0,
  'ALTER TABLE testimonials ADD INDEX idx_testimonials_moderation (approval_status, is_visible, created_at)',
  'SELECT 1'
);
PREPARE testimonial_moderation_index_statement FROM @testimonial_moderation_index_sql;
EXECUTE testimonial_moderation_index_statement;
DEALLOCATE PREPARE testimonial_moderation_index_statement;

UPDATE testimonials
SET source = 'admin', approval_status = 'approved', reviewed_at = COALESCE(reviewed_at, updated_at)
WHERE source = 'admin';

INSERT INTO permissions (slug, name, group_name, description)
VALUES ('blog.manage', 'Manage blog posts', 'Content', 'Create, update, publish, and delete blog posts.')
ON DUPLICATE KEY UPDATE name = VALUES(name), group_name = VALUES(group_name), description = VALUES(description);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'content-manager' AND p.slug = 'blog.manage';

UPDATE roles
SET description = 'Manages public content, services, events, blog posts, hero slides, and testimonials.'
WHERE slug = 'content-manager';

INSERT INTO site_settings (`key`, `value`, `type`) VALUES
('testimonial_submissions_enabled', '1', 'boolean'),
('testimonial_auto_approve', '0', 'boolean'),
('content_features_migration', '1', 'boolean')
ON DUPLICATE KEY UPDATE `type` = VALUES(`type`);
