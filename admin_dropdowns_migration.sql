-- Migration: admin-manageable dropdowns + project types + default tasks
-- for project_manager_app. Lives in the SAME shared database as contracts_app
-- (contract_manager). Safe to re-run (idempotent).
--
-- Converts the fixed ENUM columns for status/priority/funding source type
-- to plain VARCHAR(50), with the valid value list now driven by admin-editable
-- lookup tables (project_statuses, project_priorities, project_funding_source_types,
-- project_document_types) instead of being baked into the schema. Existing
-- data is preserved since ENUM values map 1:1 to the seeded lookup rows.

-- ── Lookup: Project Statuses ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_statuses` (
  `project_status_id` int          NOT NULL AUTO_INCREMENT,
  `status_name`        varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`        varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order`         smallint     NOT NULL DEFAULT 0,
  `is_active`          tinyint(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`project_status_id`),
  UNIQUE KEY `uq_project_statuses_name` (`status_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `project_statuses` (`status_name`, `description`, `sort_order`) VALUES
  ('proposed',  'Proposed but not yet started', 10),
  ('active',    'Actively underway',            20),
  ('on_hold',   'Temporarily paused',            30),
  ('completed', 'Finished',                       40),
  ('cancelled', 'Cancelled / will not proceed',   50);

-- ── Lookup: Priorities (shared by Projects and Tasks) ───────────────────
CREATE TABLE IF NOT EXISTS `project_priorities` (
  `project_priority_id` int          NOT NULL AUTO_INCREMENT,
  `priority_name`        varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`          varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order`           smallint     NOT NULL DEFAULT 0,
  `is_active`            tinyint(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`project_priority_id`),
  UNIQUE KEY `uq_project_priorities_name` (`priority_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `project_priorities` (`priority_name`, `description`, `sort_order`) VALUES
  ('low',      'Low priority',      10),
  ('medium',   'Medium priority',   20),
  ('high',     'High priority',     30),
  ('critical', 'Critical priority', 40);

-- ── Lookup: Funding Source Types ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_funding_source_types` (
  `funding_source_type_id` int          NOT NULL AUTO_INCREMENT,
  `type_name`               varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`             varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order`              smallint     NOT NULL DEFAULT 0,
  `is_active`               tinyint(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`funding_source_type_id`),
  UNIQUE KEY `uq_pfst_name` (`type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `project_funding_source_types` (`type_name`, `description`, `sort_order`) VALUES
  ('grant',           'Grant funding',            10),
  ('bond',             'Bond financing',            20),
  ('general_fund',     'General fund',              30),
  ('enterprise_fund',  'Enterprise fund',           40),
  ('impact_fee',       'Impact fee revenue',        50),
  ('other',            'Other / miscellaneous',     60);

-- ── Lookup: Document Types ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_document_types` (
  `document_type_id` int          NOT NULL AUTO_INCREMENT,
  `type_name`          varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`        varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order`         smallint     NOT NULL DEFAULT 0,
  `is_active`          tinyint(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`document_type_id`),
  UNIQUE KEY `uq_pdt_name` (`type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `project_document_types` (`type_name`, `description`, `sort_order`) VALUES
  ('plan',            'Plans / drawings',          10),
  ('permit',           'Permits',                   20),
  ('contract',         'Contract / agreement',      30),
  ('report',           'Report / study',            40),
  ('correspondence',   'Correspondence',            50),
  ('photo',            'Photos',                     60),
  ('other',            'Other',                      70);

-- ── App settings (key/value, e.g. document root path) ────────────────────
CREATE TABLE IF NOT EXISTS `pm_settings` (
  `setting_key`   varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text         COLLATE utf8mb4_unicode_ci,
  `description`   varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at`    timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pm_settings` (`setting_key`, `setting_value`, `description`) VALUES
  ('document_root_path', '', 'Absolute directory path where uploaded project documents are stored. Leave blank to use the app default (storage/projects).');

-- ── Project Types ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_types` (
  `project_type_id`          int          NOT NULL AUTO_INCREMENT,
  `project_type_name`        varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_type_description` text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order`                smallint     NOT NULL DEFAULT 0,
  `is_active`                 tinyint(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`project_type_id`),
  UNIQUE KEY `uq_project_types_name` (`project_type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `project_types` (`project_type_name`, `sort_order`) VALUES
  ('Road Construction',              10),
  ('New Building Construction',      20),
  ('Building Renovation',            30),
  ('Waterline Construction',         40),
  ('Sewerline Construction',         50),
  ('Sidewalk Construction',          60),
  ('Ordinance Revision',             70),
  ('Comprehensive Plan',             80),
  ('Other Plan Revision/Creation',   90);

-- ── Default Tasks per Project Type (many-to-many-ish: task belongs to one type) ──
CREATE TABLE IF NOT EXISTS `project_type_default_tasks` (
  `default_task_id`  int          NOT NULL AUTO_INCREMENT,
  `project_type_id`  int          NOT NULL,
  `task_name`        varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`      text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order`       smallint     NOT NULL DEFAULT 0,
  PRIMARY KEY (`default_task_id`),
  KEY `idx_ptdt_type` (`project_type_id`),
  CONSTRAINT `fk_ptdt_type` FOREIGN KEY (`project_type_id`) REFERENCES `project_types` (`project_type_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Link Projects to a Project Type (additive, nullable) ─────────────────
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'project_type_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `projects` ADD COLUMN `project_type_id` int DEFAULT NULL AFTER `project_code`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND CONSTRAINT_NAME = 'fk_projects_type'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `projects`
     ADD KEY `idx_projects_type` (`project_type_id`),
     ADD CONSTRAINT `fk_projects_type` FOREIGN KEY (`project_type_id`) REFERENCES `project_types` (`project_type_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── Convert fixed ENUMs to plain VARCHAR so the lookup tables above are the
--    single source of truth for valid values (safe/idempotent to re-run) ──
ALTER TABLE `projects`               MODIFY COLUMN `status`      varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'proposed';
ALTER TABLE `projects`               MODIFY COLUMN `priority`    varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium';
ALTER TABLE `project_tasks`          MODIFY COLUMN `priority`    varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium';
ALTER TABLE `project_funding_sources` MODIFY COLUMN `source_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other';
