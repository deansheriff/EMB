CREATE DATABASE IF NOT EXISTS emb_chronicles
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE emb_chronicles;

CREATE TABLE IF NOT EXISTS admins (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(40) NOT NULL DEFAULT 'admin',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(500) NULL,
  is_system TINYINT(1) NOT NULL DEFAULT 0,
  is_super TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  group_name VARCHAR(100) NOT NULL,
  description VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_permissions_group (group_name, name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_roles (
  admin_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (admin_id, role_id),
  CONSTRAINT fk_admin_roles_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
  CONSTRAINT fk_admin_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS site_settings (
  `key` VARCHAR(120) PRIMARY KEY,
  `value` LONGTEXT NULL,
  `type` VARCHAR(30) NOT NULL DEFAULT 'text',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS hero_slides (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  image_path VARCHAR(500) NOT NULL,
  image_alt VARCHAR(255) NOT NULL,
  headline VARCHAR(255) NOT NULL,
  subheading TEXT NULL,
  cta_label VARCHAR(120) NULL,
  cta_link VARCHAR(500) NULL,
  secondary_label VARCHAR(120) NULL,
  secondary_link VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_hero_headline (headline),
  INDEX idx_hero_active_order (is_active, sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS services (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  excerpt TEXT NOT NULL,
  description LONGTEXT NOT NULL,
  cover_image VARCHAR(500) NOT NULL,
  cover_alt VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_pinned TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(320) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_services_public (status, is_pinned, sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS service_media (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(500) NOT NULL,
  alt_text VARCHAR(255) NOT NULL,
  responsive_json JSON NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_cover TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_service_media_service
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  excerpt TEXT NOT NULL,
  description LONGTEXT NOT NULL,
  event_date DATETIME NULL,
  event_end DATETIME NULL,
  timezone VARCHAR(80) NOT NULL DEFAULT 'Africa/Lagos',
  location_mode ENUM('physical','online','hybrid') NOT NULL DEFAULT 'physical',
  location VARCHAR(255) NULL,
  event_type VARCHAR(100) NOT NULL DEFAULT 'Community Event',
  external_link VARCHAR(500) NULL,
  cover_image VARCHAR(500) NOT NULL,
  cover_alt VARCHAR(255) NOT NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  seo_title VARCHAR(255) NULL,
  seo_description VARCHAR(320) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_events_public (status, event_date),
  INDEX idx_events_featured (status, is_featured, event_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS event_media (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(500) NOT NULL,
  alt_text VARCHAR(255) NOT NULL,
  responsive_json JSON NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_cover TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_media_event
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS grant_forms (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  intro TEXT NOT NULL,
  eligibility_notice TEXT NULL,
  success_message TEXT NULL,
  notification_email VARCHAR(190) NULL,
  opens_at DATETIME NULL,
  closes_at DATETIME NULL,
  status ENUM('draft','published','closed') NOT NULL DEFAULT 'draft',
  allow_save_progress TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_grant_forms_public (status, opens_at, closes_at),
  CONSTRAINT fk_grant_form_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS grant_form_fields (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_id BIGINT UNSIGNED NOT NULL,
  section_key VARCHAR(120) NOT NULL,
  section_title VARCHAR(190) NOT NULL,
  field_key VARCHAR(120) NOT NULL,
  label VARCHAR(255) NOT NULL,
  field_type ENUM('text','email','tel','number','textarea','select','radio','file','checkbox') NOT NULL DEFAULT 'text',
  help_text VARCHAR(500) NULL,
  placeholder VARCHAR(255) NULL,
  options_json JSON NULL,
  validation_json JSON NULL,
  is_required TINYINT(1) NOT NULL DEFAULT 0,
  width ENUM('full','half','third') NOT NULL DEFAULT 'full',
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_grant_form_field (form_id, field_key),
  INDEX idx_grant_fields_order (form_id, sort_order, id),
  CONSTRAINT fk_grant_field_form FOREIGN KEY (form_id) REFERENCES grant_forms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS testimonials (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_name VARCHAR(190) NOT NULL,
  photo_path VARCHAR(500) NULL,
  photo_alt VARCHAR(255) NULL,
  quote TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_testimonial_name (client_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS page_content (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_key VARCHAR(120) NOT NULL,
  section_key VARCHAR(120) NOT NULL,
  eyebrow VARCHAR(190) NULL,
  heading VARCHAR(255) NULL,
  content LONGTEXT NOT NULL,
  image_path VARCHAR(500) NULL,
  image_alt VARCHAR(255) NULL,
  link_label VARCHAR(120) NULL,
  link_url VARCHAR(500) NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'published',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_page_section (page_key, section_key)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_submissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(80) NULL,
  topic VARCHAR(120) NULL,
  message TEXT NOT NULL,
  consented_at DATETIME NOT NULL,
  source_page VARCHAR(190) NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  is_archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_contact_inbox (is_archived, is_read, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_code VARCHAR(48) NOT NULL UNIQUE,
  consultation_type VARCHAR(190) NOT NULL,
  preferred_date DATE NULL,
  preferred_time TIME NULL,
  name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(80) NOT NULL,
  preferred_contact VARCHAR(40) NOT NULL,
  message TEXT NULL,
  status ENUM('pending_payment','new','contacted','scheduled','completed','cancelled') NOT NULL DEFAULT 'new',
  scheduled_at DATETIME NULL,
  admin_notes TEXT NULL,
  amount_due BIGINT UNSIGNED NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'NGN',
  payment_status ENUM('not_required','pending','paid','failed','refunded') NOT NULL DEFAULT 'not_required',
  payment_reference VARCHAR(100) NULL UNIQUE,
  paid_at DATETIME NULL,
  email_confirmation_sent_at DATETIME NULL,
  consented_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_appointments_status (status, created_at),
  INDEX idx_appointments_payment (payment_status, created_at)
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS grant_applications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  form_id BIGINT UNSIGNED NULL,
  applicant_code VARCHAR(40) NOT NULL UNIQUE,
  full_name VARCHAR(190) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(80) NOT NULL,
  location VARCHAR(190) NOT NULL,
  answers_json JSON NOT NULL,
  documents_json JSON NULL,
  form_snapshot_json JSON NULL,
  eligibility_complete TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('submitted','in_review','shortlisted','declined','awarded') NOT NULL DEFAULT 'submitted',
  assigned_reviewer BIGINT UNSIGNED NULL,
  internal_notes TEXT NULL,
  consented_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_grant_event_status (event_id, status, created_at),
  CONSTRAINT fk_grant_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  CONSTRAINT fk_grant_application_form FOREIGN KEY (form_id) REFERENCES grant_forms(id) ON DELETE SET NULL,
  CONSTRAINT fk_grant_reviewer FOREIGN KEY (assigned_reviewer) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS grant_application_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  field_id BIGINT UNSIGNED NULL,
  field_key VARCHAR(120) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  storage_path VARCHAR(500) NOT NULL,
  mime_type VARCHAR(120) NOT NULL,
  size_bytes BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_grant_document_application (application_id, created_at),
  CONSTRAINT fk_grant_document_application FOREIGN KEY (application_id) REFERENCES grant_applications(id) ON DELETE CASCADE,
  CONSTRAINT fk_grant_document_field FOREIGN KEY (field_id) REFERENCES grant_form_fields(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  consented_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id BIGINT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id BIGINT UNSIGNED NULL,
  metadata_json JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_created (created_at),
  CONSTRAINT fk_audit_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;
