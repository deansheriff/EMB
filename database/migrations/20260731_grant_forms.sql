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

UPDATE permissions
SET name = 'Manage grant forms and applications',
    description = 'Create grant forms, review applications, access protected documents, and assign reviewers.'
WHERE slug = 'grants.manage';

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

SET @grant_form_id_column_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'grant_applications'
    AND COLUMN_NAME = 'form_id'
);
SET @grant_form_id_column_sql = IF(
  @grant_form_id_column_exists = 0,
  'ALTER TABLE grant_applications ADD COLUMN form_id BIGINT UNSIGNED NULL AFTER event_id',
  'SELECT 1'
);
PREPARE grant_form_id_column_stmt FROM @grant_form_id_column_sql;
EXECUTE grant_form_id_column_stmt;
DEALLOCATE PREPARE grant_form_id_column_stmt;

SET @grant_form_snapshot_column_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'grant_applications'
    AND COLUMN_NAME = 'form_snapshot_json'
);
SET @grant_form_snapshot_column_sql = IF(
  @grant_form_snapshot_column_exists = 0,
  'ALTER TABLE grant_applications ADD COLUMN form_snapshot_json JSON NULL AFTER documents_json',
  'SELECT 1'
);
PREPARE grant_form_snapshot_column_stmt FROM @grant_form_snapshot_column_sql;
EXECUTE grant_form_snapshot_column_stmt;
DEALLOCATE PREPARE grant_form_snapshot_column_stmt;

SET @grant_form_fk_exists = (
  SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'grant_applications'
    AND CONSTRAINT_NAME = 'fk_grant_application_form'
);
SET @grant_form_fk_sql = IF(
  @grant_form_fk_exists = 0,
  'ALTER TABLE grant_applications ADD CONSTRAINT fk_grant_application_form FOREIGN KEY (form_id) REFERENCES grant_forms(id) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE grant_form_fk_stmt FROM @grant_form_fk_sql;
EXECUTE grant_form_fk_stmt;
DEALLOCATE PREPARE grant_form_fk_stmt;

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

INSERT INTO grant_forms
  (event_id, title, slug, intro, eligibility_notice, success_message, notification_email, opens_at, closes_at, status, allow_save_progress)
SELECT
  e.id,
  'FIYFF Fertility Support Grant Application',
  'fiyff-fertility-support-grant',
  'We understand that the journey to parenthood can be financially overwhelming. Complete this confidential application so the FIYFF team can carefully review your request for fertility support.',
  'Eligibility allows an application to be reviewed; it does not guarantee selection. Please submit complete and truthful information.',
  'Your FIYFF grant application has been received. We know how much hope and care went into sharing your story.',
  NULL,
  NULL,
  e.event_end,
  'published',
  1
FROM events e
WHERE e.slug = 'fiyff-fertility-support-grant'
  AND NOT EXISTS (SELECT 1 FROM grant_forms gf WHERE gf.slug = 'fiyff-fertility-support-grant')
LIMIT 1;

SET @fiyff_form_id = (SELECT id FROM grant_forms WHERE slug = 'fiyff-fertility-support-grant' LIMIT 1);

INSERT INTO grant_form_fields
  (form_id, section_key, section_title, field_key, label, field_type, help_text, placeholder, options_json, validation_json, is_required, width, sort_order)
SELECT @fiyff_form_id, seed.section_key, seed.section_title, seed.field_key, seed.label, seed.field_type,
       seed.help_text, seed.placeholder, seed.options_json, seed.validation_json, seed.is_required, seed.width, seed.sort_order
FROM (
  SELECT 'applicant' section_key, 'Applicant details' section_title, 'prefix' field_key, 'Prefix' label, 'select' field_type, NULL help_text, NULL placeholder, JSON_ARRAY('Mr.','Mrs.','Miss','Ms.','Dr.','Prof.','Rev.') options_json, NULL validation_json, 0 is_required, 'third' width, 10 sort_order
  UNION ALL SELECT 'applicant','Applicant details','first_name','First name','text',NULL,'First name',NULL,NULL,1,'third',20
  UNION ALL SELECT 'applicant','Applicant details','last_name','Last name','text',NULL,'Last name',NULL,NULL,1,'third',30
  UNION ALL SELECT 'applicant','Applicant details','age','Age','number',NULL,'e.g. 32',NULL,JSON_OBJECT('min',18,'max',100),1,'half',40
  UNION ALL SELECT 'applicant','Applicant details','occupation','Occupation','text',NULL,'Current occupation',NULL,NULL,1,'half',50
  UNION ALL SELECT 'applicant','Applicant details','email','Email address','email',NULL,'hello@example.com',NULL,NULL,1,'half',60
  UNION ALL SELECT 'applicant','Applicant details','phone','Phone / WhatsApp','tel',NULL,'+234...',NULL,NULL,1,'half',70
  UNION ALL SELECT 'applicant','Applicant details','address_street','Street address','text',NULL,'Street and house number',NULL,NULL,1,'full',80
  UNION ALL SELECT 'applicant','Applicant details','address_line2','Address line 2','text','Optional','Apartment, suite, or landmark',NULL,NULL,0,'full',90
  UNION ALL SELECT 'applicant','Applicant details','address_city','City','text',NULL,'City',NULL,NULL,1,'third',100
  UNION ALL SELECT 'applicant','Applicant details','address_state','State','text',NULL,'State',NULL,NULL,1,'third',110
  UNION ALL SELECT 'applicant','Applicant details','address_country','Country','select',NULL,NULL,JSON_ARRAY('Nigeria','Ghana','Kenya','South Africa','United Kingdom','United States','Other'),NULL,1,'third',120
  UNION ALL SELECT 'applicant','Applicant details','passport_photo','Passport photo','file','JPG, PNG, or PDF. Maximum 8 MB.',NULL,NULL,JSON_OBJECT('accept',JSON_ARRAY('image/jpeg','image/png','application/pdf')),1,'half',130
  UNION ALL SELECT 'applicant','Applicant details','valid_id','Valid government ID','file','JPG, PNG, or PDF. Maximum 8 MB.',NULL,NULL,JSON_OBJECT('accept',JSON_ARRAY('image/jpeg','image/png','application/pdf')),1,'half',140
  UNION ALL SELECT 'spouse','Spouse details','spouse_first_name','Spouse first name','text',NULL,'First name',NULL,NULL,1,'half',200
  UNION ALL SELECT 'spouse','Spouse details','spouse_last_name','Spouse last name','text',NULL,'Last name',NULL,NULL,1,'half',210
  UNION ALL SELECT 'spouse','Spouse details','spouse_age','Spouse age','number',NULL,'e.g. 35',NULL,JSON_OBJECT('min',18,'max',100),1,'half',220
  UNION ALL SELECT 'spouse','Spouse details','spouse_occupation','Spouse occupation','text',NULL,'Current occupation',NULL,NULL,1,'half',230
  UNION ALL SELECT 'spouse','Spouse details','spouse_phone','Spouse phone number','tel',NULL,'+234...',NULL,NULL,1,'half',240
  UNION ALL SELECT 'spouse','Spouse details','spouse_photo','Spouse passport photo','file','JPG, PNG, or PDF. Maximum 8 MB.',NULL,NULL,JSON_OBJECT('accept',JSON_ARRAY('image/jpeg','image/png','application/pdf')),1,'half',250
  UNION ALL SELECT 'marriage','Marriage and your story','years_married','Years married','number',NULL,'Number of years',NULL,JSON_OBJECT('min',0,'max',80),1,'half',300
  UNION ALL SELECT 'marriage','Marriage and your story','num_kids','Number of children','number',NULL,'0',NULL,JSON_OBJECT('min',0,'max',20),1,'half',310
  UNION ALL SELECT 'marriage','Marriage and your story','story','Tell us about your journey','textarea','Share the context you would like the review panel to understand.','Your story...',NULL,JSON_OBJECT('minlength',30,'maxlength',3000),1,'full',320
  UNION ALL SELECT 'medical','Medical information','medical_history','Previous medical reports or summary','textarea','Briefly summarize relevant diagnoses, findings, or recommendations.','Medical history...',NULL,JSON_OBJECT('minlength',20,'maxlength',3000),1,'full',400
  UNION ALL SELECT 'medical','Medical information','fertility_treatment','Previous fertility treatment','radio','Have you undergone fertility treatment previously?',NULL,JSON_ARRAY('Yes','No','Other'),NULL,1,'full',410
  UNION ALL SELECT 'medical','Medical information','fertility_treatment_type','Type of treatment','text','Optional','e.g. IUI or IVF',NULL,NULL,0,'half',420
  UNION ALL SELECT 'medical','Medical information','ivf_icsi_procedures','Specialist IVF/ICSI procedures','text','Optional','Please specify',NULL,NULL,0,'half',430
  UNION ALL SELECT 'medical','Medical information','how_heard','How did you hear about us?','text',NULL,'Friend, clinic, or social media',NULL,NULL,1,'half',440
  UNION ALL SELECT 'medical','Medical information','attended_fertility_event','Did you attend the fertility event?','radio',NULL,NULL,JSON_ARRAY('Yes','No'),NULL,1,'half',450
) AS seed
WHERE @fiyff_form_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM grant_form_fields existing WHERE existing.form_id = @fiyff_form_id);

UPDATE events e
JOIN grant_forms gf ON gf.event_id = e.id
SET e.external_link = CONCAT('/grants/', gf.slug, '/apply')
WHERE e.event_type = 'Grant Program';
