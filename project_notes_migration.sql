-- Migration: project_notes table
-- Lives in project_manager_app repo, shared contract_manager database.
-- Safe to re-run (idempotent).
--
-- Free-form dated notes attached to a project. Each note records who wrote
-- it (person_id, defaults to the logged-in user at creation time), the date
-- the note applies to, and the note text itself.

CREATE TABLE IF NOT EXISTS `project_notes` (
  `note_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `person_id` int DEFAULT NULL,
  `note_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`note_id`),
  KEY `idx_project_notes_project` (`project_id`),
  KEY `fk_project_notes_person` (`person_id`),
  CONSTRAINT `fk_project_notes_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_project_notes_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
