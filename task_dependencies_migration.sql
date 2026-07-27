-- Migration: task dependency tracking for project_tasks
-- Lives in project_manager_app repo, shared contract_manager database.
-- Safe to re-run (idempotent).
--
-- Adds a dependency_type ('independent' | 'dependent') and an optional
-- depends_on_task_id (self-referencing FK) to project_tasks so a task can
-- require another task in the same project to be completed first before it
-- can be moved to in_progress/completed.

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'project_tasks' AND COLUMN_NAME = 'dependency_type'
);
SET @sql := IF(@col_exists = 0,
  "ALTER TABLE `project_tasks` ADD COLUMN `dependency_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'independent' AFTER `priority`",
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'project_tasks' AND COLUMN_NAME = 'depends_on_task_id'
);
SET @sql := IF(@col_exists = 0,
  "ALTER TABLE `project_tasks` ADD COLUMN `depends_on_task_id` int DEFAULT NULL AFTER `dependency_type`",
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'project_tasks' AND CONSTRAINT_NAME = 'fk_pt_depends_on'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `project_tasks`
     ADD KEY `idx_pt_depends_on` (`depends_on_task_id`),
     ADD CONSTRAINT `fk_pt_depends_on` FOREIGN KEY (`depends_on_task_id`) REFERENCES `project_tasks` (`task_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
