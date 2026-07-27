-- MySQL dump 10.13  Distrib 9.7.1, for macos15.7 (arm64)
--
-- Host: localhost    Database: contract_manager
-- ------------------------------------------------------
-- Server version	9.7.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `project_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_type_id` int DEFAULT NULL,
  `project_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'proposed',
  `priority` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `department_id` int DEFAULT NULL,
  `project_manager_person_id` int DEFAULT NULL,
  `sponsor_person_id` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `target_end_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `estimated_budget` decimal(18,2) DEFAULT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `uq_projects_code` (`project_code`),
  KEY `idx_projects_department` (`department_id`),
  KEY `idx_projects_status` (`status`),
  KEY `fk_projects_pm` (`project_manager_person_id`),
  KEY `fk_projects_sponsor` (`sponsor_person_id`),
  KEY `fk_projects_created_by` (`created_by_person_id`),
  KEY `idx_projects_type` (`project_type_id`),
  CONSTRAINT `fk_projects_created_by` FOREIGN KEY (`created_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_pm` FOREIGN KEY (`project_manager_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_sponsor` FOREIGN KEY (`sponsor_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_type` FOREIGN KEY (`project_type_id`) REFERENCES `project_types` (`project_type_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (3,'1234',1,'Test of a new road construction','This is a test of a new Road Construction','proposed','medium',1,1,15,'2026-07-27','2026-08-29',NULL,NULL,1,'2026-07-27 00:13:13','2026-07-27 17:09:15');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_tasks`
--

DROP TABLE IF EXISTS `project_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `parent_task_id` int DEFAULT NULL,
  `task_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('not_started','in_progress','blocked','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started',
  `priority` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `dependency_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'independent',
  `depends_on_task_id` int DEFAULT NULL,
  `assigned_to_person_id` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `sort_order` smallint NOT NULL DEFAULT '0',
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`task_id`),
  KEY `idx_pt_project` (`project_id`),
  KEY `idx_pt_parent` (`parent_task_id`),
  KEY `idx_pt_assignee` (`assigned_to_person_id`),
  KEY `idx_pt_depends_on` (`depends_on_task_id`),
  CONSTRAINT `fk_pt_assignee` FOREIGN KEY (`assigned_to_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pt_depends_on` FOREIGN KEY (`depends_on_task_id`) REFERENCES `project_tasks` (`task_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pt_parent` FOREIGN KEY (`parent_task_id`) REFERENCES `project_tasks` (`task_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pt_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_tasks`
--

LOCK TABLES `project_tasks` WRITE;
/*!40000 ALTER TABLE `project_tasks` DISABLE KEYS */;
INSERT INTO `project_tasks` VALUES (29,3,NULL,'Obtain initial Funding','At Least Start money for the project','not_started','medium','independent',NULL,NULL,NULL,'2026-08-03',NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:21:32'),(30,3,NULL,'Design Contract- RFQ','Develop and send out RFQ','not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(31,3,NULL,'Neighborhood Meeting','Initial Meeting on Road Concept','not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(32,3,NULL,'Obtain Full Funding','Make Sure project budget is fully secure','not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(33,3,NULL,'Road Design - 30% Plans',NULL,'not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(34,3,NULL,'Road Design - 60% Plans',NULL,'not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(35,3,NULL,'Road Design - 90% Plans',NULL,'not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(36,3,NULL,'Utility Relocation Design','All utilities relocation design','not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(37,3,NULL,'Right of Way Acquisition',NULL,'not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(38,3,NULL,'Approval of ROW (NCDOT)','NCDOT approval of Right of way','not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(39,3,NULL,'Construction Drawing Approval- Internal','Internal CD Approval','not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(40,3,NULL,'Construction Drawing Approval - External','Some external group requiring construction drawing approval','not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(41,3,NULL,'Advertise Bid',NULL,'not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31'),(42,3,NULL,'Develop Bid Package','Assemble construction docs & contracts','not_started','medium','independent',NULL,NULL,NULL,NULL,NULL,0,1,'2026-07-27 17:20:31','2026-07-27 17:20:31');
/*!40000 ALTER TABLE `project_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_types`
--

DROP TABLE IF EXISTS `project_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_types` (
  `project_type_id` int NOT NULL AUTO_INCREMENT,
  `project_type_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_type_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`project_type_id`),
  UNIQUE KEY `uq_project_types_name` (`project_type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_types`
--

LOCK TABLES `project_types` WRITE;
/*!40000 ALTER TABLE `project_types` DISABLE KEYS */;
INSERT INTO `project_types` VALUES (1,'Road Construction','Typical Road Construction Project',10,1),(2,'New Building Construction',NULL,20,1),(3,'Building Renovation',NULL,30,1),(4,'Waterline Construction',NULL,40,1),(5,'Sewerline Construction',NULL,50,1),(6,'Sidewalk Construction',NULL,60,1),(7,'Ordinance Revision',NULL,70,1),(8,'Comprehensive Plan',NULL,80,1),(9,'Other Plan Revision/Creation',NULL,90,1);
/*!40000 ALTER TABLE `project_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_type_default_tasks`
--

DROP TABLE IF EXISTS `project_type_default_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_type_default_tasks` (
  `default_task_id` int NOT NULL AUTO_INCREMENT,
  `project_type_id` int NOT NULL,
  `task_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`default_task_id`),
  KEY `idx_ptdt_type` (`project_type_id`),
  CONSTRAINT `fk_ptdt_type` FOREIGN KEY (`project_type_id`) REFERENCES `project_types` (`project_type_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_type_default_tasks`
--

LOCK TABLES `project_type_default_tasks` WRITE;
/*!40000 ALTER TABLE `project_type_default_tasks` DISABLE KEYS */;
INSERT INTO `project_type_default_tasks` VALUES (3,1,'Obtain initial Funding','At Least Start money for the project',10),(4,1,'Design Contract- RFQ','Develop and send out RFQ',20),(5,1,'Road Design - 30% Plans','',50),(6,1,'Road Design - 60% Plans','',60),(7,1,'Road Design - 90% Plans','',70),(8,1,'Neighborhood Meeting','Initial Meeting on Road Concept',30),(9,1,'Utility Relocation Design','All utilities relocation design',80),(10,1,'Right of Way Acquisition','',90),(11,1,'Construction Drawing Approval- Internal','Internal CD Approval',110),(12,1,'Construction Drawing Approval - External','Some external group requiring construction drawing approval',120),(13,1,'Develop Bid Package','Assemble construction docs & contracts',140),(14,1,'Advertise Bid','',130),(15,1,'Obtain Full Funding','Make Sure project budget is fully secure',40),(18,1,'Approval of ROW (NCDOT)','NCDOT approval of Right of way',100);
/*!40000 ALTER TABLE `project_type_default_tasks` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-27 13:25:41
