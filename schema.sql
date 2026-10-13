-- ========================================================================
-- DATABASE SCHEMA: Jarvis Monitor
-- Target: MySQL 5.7+ / MariaDB 10.3+ (InnoDB, utf8mb4)
-- Siap di-import langsung di phpMyAdmin cPanel
-- ========================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------
-- 1. Table: projects
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(191) NOT NULL UNIQUE,
  `repo_url` VARCHAR(255) NULL,
  `client_name` VARCHAR(191) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 2. Table: changelogs
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `changelogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_name` VARCHAR(191) NOT NULL,
  `commit_message` TEXT NOT NULL,
  `commit_type` ENUM('fitur', 'bugfix', 'update') NOT NULL DEFAULT 'update',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_changelogs_project` (`project_name`, `created_at`),
  CONSTRAINT `fk_changelogs_project` FOREIGN KEY (`project_name`) REFERENCES `projects` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 3. Table: tickets
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_name` VARCHAR(191) NOT NULL,
  `reporter_name` VARCHAR(191) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'Bug',
  `issue_description` TEXT NOT NULL,
  `dev_reply` TEXT NULL,
  `replied_at` DATETIME NULL,
  `image_url` VARCHAR(255) NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Open',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_tickets_project` (`project_name`, `status`),
  CONSTRAINT `fk_tickets_project` FOREIGN KEY (`project_name`) REFERENCES `projects` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 4. Table: features
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `features` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_name` VARCHAR(191) NOT NULL,
  `feature_name` VARCHAR(191) NOT NULL,
  `description` TEXT NULL,
  `doc_url` VARCHAR(255) NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_features_project` (`project_name`, `created_at`),
  CONSTRAINT `fk_features_project` FOREIGN KEY (`project_name`) REFERENCES `projects` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- 5. Table: timetables (Roadmap Bulanan IT)
-- ------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `timetables` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_name` VARCHAR(191) NOT NULL,
  `task_name` VARCHAR(191) NOT NULL,
  `target_month` VARCHAR(50) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Planned',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_timetables_project` (`project_name`, `target_month`),
  CONSTRAINT `fk_timetables_project` FOREIGN KEY (`project_name`) REFERENCES `projects` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
