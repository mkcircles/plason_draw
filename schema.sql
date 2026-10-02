-- Plascon Draw Database Schema Updates
-- Ensures drawn_numbers table exists with proper indexes and types

CREATE TABLE IF NOT EXISTS `drawn_numbers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `msisdn` VARCHAR(32) NOT NULL,
  `file_used` VARCHAR(100) DEFAULT NULL,
  `region` VARCHAR(50) DEFAULT NULL,
  `draw_type` VARCHAR(50) DEFAULT 'daily',
  `prize` VARCHAR(100) DEFAULT NULL,
  `drawn_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  INDEX `idx_drawn_msisdn` (`msisdn`),
  INDEX `idx_drawn_region` (`region`),
  INDEX `idx_drawn_type` (`draw_type`),
  INDEX `idx_drawn_at` (`drawn_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
