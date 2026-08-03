CREATE TABLE IF NOT EXISTS appointment_types (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  description TEXT NULL,
  price BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Amount in the currency subunit (kobo for NGN)',
  currency CHAR(3) NOT NULL DEFAULT 'NGN',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_appointment_type_name (name),
  INDEX idx_appointment_types_public (is_active, sort_order, name)
) ENGINE=InnoDB;

INSERT INTO appointment_types (name, description, price, currency, sort_order, is_active) VALUES
('Fertility and Treatment Consultation', 'Personalized guidance concerning fertility challenges, IVF, IUI, treatment preparation, previous treatment experiences and understanding your next steps.', 5000000, 'NGN', 1, 1),
('PGT Consultation', 'Personalized guidance on Preimplantation Genetic Testing (PGT), including the process, timeline, expectations and questions relating to your treatment.', 5000000, 'NGN', 2, 1),
('Embryology Career Consultation', 'Career guidance for science graduates and healthcare professionals interested in clinical embryology, training pathways and career development.', 2500000, 'NGN', 3, 1),
('TTC Community', 'A supportive community for women navigating fertility challenges and trying to conceive.', 0, 'NGN', 4, 1),
('General Enquiry or Partnership', 'Contact EMB Chronicles with a general question or to discuss a partnership.', 0, 'NGN', 5, 1)
ON DUPLICATE KEY UPDATE
description = VALUES(description), price = VALUES(price), currency = VALUES(currency),
sort_order = VALUES(sort_order), is_active = VALUES(is_active);

SET @appointment_type_column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'appointments'
    AND column_name = 'appointment_type_id'
);
SET @appointment_type_alter_sql = IF(
  @appointment_type_column_exists = 0,
  'ALTER TABLE appointments ADD COLUMN appointment_type_id BIGINT UNSIGNED NULL AFTER consultation_type, ADD INDEX idx_appointments_type (appointment_type_id, created_at), ADD CONSTRAINT fk_appointment_type FOREIGN KEY (appointment_type_id) REFERENCES appointment_types(id) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE appointment_type_statement FROM @appointment_type_alter_sql;
EXECUTE appointment_type_statement;
DEALLOCATE PREPARE appointment_type_statement;

INSERT INTO site_settings (`key`, `value`, `type`)
VALUES ('appointment_types_migration', '1', 'boolean')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `type` = VALUES(`type`);
