USE emb_chronicles;

ALTER TABLE admins
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role,
  ADD COLUMN last_login_at DATETIME NULL AFTER is_active;

CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(500) NULL,
  is_system TINYINT(1) NOT NULL DEFAULT 0,
  is_super TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  group_name VARCHAR(100) NOT NULL,
  description VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_permissions_group (group_name, name)
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE admin_roles (
  admin_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (admin_id, role_id),
  CONSTRAINT fk_admin_roles_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
  CONSTRAINT fk_admin_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO roles (name, slug, description, is_system, is_super) VALUES
('Super Administrator', 'super-administrator', 'Unrestricted access to every administration feature, including users and roles.', 1, 1),
('Content Manager', 'content-manager', 'Manages public content, services, events, hero slides, and testimonials.', 1, 0),
('Client Services', 'client-services', 'Manages enquiries, appointments, payment records, and email delivery history.', 1, 0),
('Grant Reviewer', 'grant-reviewer', 'Reviews and manages FIYFF grant applications.', 1, 0);

INSERT INTO permissions (slug, name, group_name, description) VALUES
('dashboard.view', 'View dashboard', 'General', 'Open the admin dashboard and see permitted summaries.'),
('events.manage', 'Manage events', 'Content', 'Create, update, publish, and delete events.'),
('services.manage', 'Manage services', 'Content', 'Create, update, publish, and delete services.'),
('heroes.manage', 'Manage hero slides', 'Content', 'Manage homepage hero slides and media.'),
('testimonials.manage', 'Manage testimonials', 'Content', 'Create, update, show, and hide testimonials.'),
('content.manage', 'Manage page content', 'Content', 'Edit managed page sections and supporting media.'),
('grants.manage', 'Manage grant applications', 'Programs', 'Review grant applications and assign reviewers.'),
('contacts.manage', 'Manage contact submissions', 'Client Services', 'Read, archive, and delete contact submissions.'),
('appointments.manage', 'Manage appointments', 'Client Services', 'Manage bookings, payment history, scheduling, and client updates.'),
('email_log.view', 'View email log', 'Client Services', 'Review transactional email delivery history and failures.'),
('settings.manage', 'Manage site settings', 'Configuration', 'Manage branding, SMTP, Paystack, and global settings.'),
('users.manage', 'Manage administrators', 'Access Control', 'Create, update, activate, and deactivate admin users.'),
('roles.manage', 'Manage roles', 'Access Control', 'Create roles and assign permissions within the current administrator’s authority.');

INSERT INTO admin_roles (admin_id, role_id)
SELECT a.id, r.id FROM admins a JOIN roles r ON r.slug = 'super-administrator'
WHERE a.email = 'admin@embchronicles.com';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'content-manager' AND p.slug IN
('dashboard.view','events.manage','services.manage','heroes.manage','testimonials.manage','content.manage');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'client-services' AND p.slug IN
('dashboard.view','contacts.manage','appointments.manage','email_log.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug = 'grant-reviewer' AND p.slug IN ('dashboard.view','grants.manage');
