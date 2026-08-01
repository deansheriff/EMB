CREATE TABLE IF NOT EXISTS appointment_availability_slots (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  weekday TINYINT UNSIGNED NOT NULL COMMENT 'ISO-8601 weekday: 1=Monday, 7=Sunday',
  start_time TIME NOT NULL,
  duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 60,
  capacity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_availability_weekday_time (weekday, start_time),
  INDEX idx_availability_active (is_active, weekday, start_time)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointment_blocked_dates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  blocked_date DATE NOT NULL UNIQUE,
  reason VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_blocked_date (blocked_date)
) ENGINE=InnoDB;

SET @availability_column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'appointments'
    AND column_name = 'availability_slot_id'
);
SET @availability_alter_sql = IF(
  @availability_column_exists = 0,
  'ALTER TABLE appointments ADD COLUMN availability_slot_id BIGINT UNSIGNED NULL AFTER preferred_time, ADD INDEX idx_appointments_availability (availability_slot_id, preferred_date, preferred_time), ADD CONSTRAINT fk_appointment_availability_slot FOREIGN KEY (availability_slot_id) REFERENCES appointment_availability_slots(id) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE availability_statement FROM @availability_alter_sql;
EXECUTE availability_statement;
DEALLOCATE PREPARE availability_statement;

SET @booking_date_index_exists = (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'appointments'
    AND index_name = 'idx_appointments_booking_date'
);
SET @booking_date_index_sql = IF(
  @booking_date_index_exists = 0,
  'ALTER TABLE appointments ADD INDEX idx_appointments_booking_date (preferred_date, status, availability_slot_id)',
  'SELECT 1'
);
PREPARE booking_date_index_statement FROM @booking_date_index_sql;
EXECUTE booking_date_index_statement;
DEALLOCATE PREPARE booking_date_index_statement;

SET @availability_constraint_exists = (
  SELECT COUNT(*) FROM information_schema.table_constraints
  WHERE constraint_schema = DATABASE()
    AND table_name = 'appointments'
    AND constraint_name = 'fk_appointment_availability_slot'
    AND constraint_type = 'FOREIGN KEY'
);
SET @availability_constraint_sql = IF(
  @availability_constraint_exists = 0,
  'ALTER TABLE appointments ADD CONSTRAINT fk_appointment_availability_slot FOREIGN KEY (availability_slot_id) REFERENCES appointment_availability_slots(id) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE availability_constraint_statement FROM @availability_constraint_sql;
EXECUTE availability_constraint_statement;
DEALLOCATE PREPARE availability_constraint_statement;

INSERT INTO site_settings (`key`, `value`, `type`) VALUES
('appointment_booking_enabled', '1', 'boolean'),
('appointment_booking_window_days', '60', 'number'),
('appointment_min_notice_hours', '24', 'number'),
('appointment_daily_limit', '6', 'number'),
('maintenance_enabled', '0', 'boolean'),
('maintenance_title', 'We are making the website better', 'text'),
('maintenance_message', 'The public website is temporarily unavailable while an update is completed. Please check back shortly.', 'textarea'),
('maintenance_end_at', '', 'datetime'),
('deployment_status_message', 'All website services are operational.', 'text'),
('deployment_status_url', '', 'url')
ON DUPLICATE KEY UPDATE `key` = VALUES(`key`);

INSERT INTO appointment_availability_slots (weekday, start_time, duration_minutes, capacity, is_active)
SELECT defaults.weekday, defaults.start_time, defaults.duration_minutes, defaults.capacity, defaults.is_active
FROM (
  SELECT 1 weekday, '10:00:00' start_time, 60 duration_minutes, 1 capacity, 1 is_active UNION ALL
  SELECT 1, '12:00:00', 60, 1, 1 UNION ALL SELECT 1, '14:00:00', 60, 1, 1 UNION ALL
  SELECT 2, '10:00:00', 60, 1, 1 UNION ALL SELECT 2, '12:00:00', 60, 1, 1 UNION ALL SELECT 2, '14:00:00', 60, 1, 1 UNION ALL
  SELECT 3, '10:00:00', 60, 1, 1 UNION ALL SELECT 3, '12:00:00', 60, 1, 1 UNION ALL SELECT 3, '14:00:00', 60, 1, 1 UNION ALL
  SELECT 4, '10:00:00', 60, 1, 1 UNION ALL SELECT 4, '12:00:00', 60, 1, 1 UNION ALL SELECT 4, '14:00:00', 60, 1, 1 UNION ALL
  SELECT 5, '10:00:00', 60, 1, 1 UNION ALL SELECT 5, '12:00:00', 60, 1, 1 UNION ALL SELECT 5, '14:00:00', 60, 1, 1
) AS defaults
WHERE NOT EXISTS (SELECT 1 FROM appointment_availability_slots LIMIT 1);

-- Written last so an interrupted migration is retried on the next deployment.
INSERT INTO site_settings (`key`, `value`, `type`)
VALUES ('availability_maintenance_migration', '1', 'system')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
