/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.5.29-MariaDB, for debian-linux-gnu (aarch64)
--
-- Host: localhost    Database: logicnet_crm_25
-- ------------------------------------------------------
-- Server version	10.5.29-MariaDB-0+deb11u1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `cui` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'Compania 3432422','RO3432422','2025-07-23 12:56:31');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_field_values`
--

DROP TABLE IF EXISTS `custom_field_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_field_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_id` int(11) NOT NULL,
  `custom_field_id` int(11) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`),
  KEY `custom_field_id` (`custom_field_id`),
  CONSTRAINT `custom_field_values_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `custom_field_values_ibfk_2` FOREIGN KEY (`custom_field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_field_values`
--

LOCK TABLES `custom_field_values` WRITE;
/*!40000 ALTER TABLE `custom_field_values` DISABLE KEYS */;
INSERT INTO `custom_field_values` VALUES (1,1,1,'TESTCLIENT1','2025-07-23 12:57:08'),(2,1,2,'RO3423224','2025-07-23 12:57:08'),(3,1,3,'test1@test.ro','2025-07-23 12:57:08'),(4,2,4,'TESTFACTURA1','2025-07-23 12:57:36'),(5,2,5,'2002-02-02','2025-07-23 12:57:36'),(6,2,6,'750','2025-07-23 12:57:36'),(7,3,7,'TEST PRODUS 1','2025-07-23 13:15:17'),(8,3,8,'100','2025-07-23 13:15:17'),(9,4,7,'TEST PRODUS 2','2025-07-23 13:15:36'),(10,4,8,'150','2025-07-23 13:15:36'),(11,5,4,'TEST FACTURA 2','2025-07-23 14:36:38'),(12,5,5,'2025-07-10','2025-07-23 14:36:38'),(13,5,6,'1000','2025-07-23 14:36:38');
/*!40000 ALTER TABLE `custom_field_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_fields`
--

DROP TABLE IF EXISTS `custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_type` varchar(50) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_primary_label` tinyint(1) DEFAULT 0,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `entity_id` (`entity_id`),
  CONSTRAINT `custom_fields_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_fields`
--

LOCK TABLES `custom_fields` WRITE;
/*!40000 ALTER TABLE `custom_fields` DISABLE KEYS */;
INSERT INTO `custom_fields` VALUES (1,1,1,'Nume Client','text','nume_client',1,'2025-07-23 12:56:31',1,1),(2,1,1,'CUI','text','cui',1,'2025-07-23 12:56:31',0,1),(3,1,1,'Email','text','email',0,'2025-07-23 12:56:31',0,1),(4,1,2,'Număr Factură','text','numar_factura',1,'2025-07-23 12:56:31',1,1),(5,1,2,'Data Emiterii','date','data_emiterii',1,'2025-07-23 12:56:31',0,1),(6,1,2,'Valoare Totală','number','valoare_totala',1,'2025-07-23 12:56:31',0,1),(7,1,3,'Nume Produs','text','nume_produs',1,'2025-07-23 12:56:31',1,1),(8,1,3,'Preț Unitar','number','pret_unitar',1,'2025-07-23 12:56:31',0,1);
/*!40000 ALTER TABLE `custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entities`
--

DROP TABLE IF EXISTS `entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entities`
--

LOCK TABLES `entities` WRITE;
/*!40000 ALTER TABLE `entities` DISABLE KEYS */;
INSERT INTO `entities` VALUES (1,1,'Clienți','Lista de clienți ai companiei',1,'clienti',NULL,'2025-07-23 12:56:31'),(2,1,'Facturi','Lista de facturi emise',1,'facturi',NULL,'2025-07-23 12:56:31'),(3,1,'Produse','Catalogul de produse sau servicii',1,'produse',NULL,'2025-07-23 12:56:31');
/*!40000 ALTER TABLE `entities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempted_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `records`
--

DROP TABLE IF EXISTS `records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_id` (`entity_id`),
  CONSTRAINT `records_ibfk_1` FOREIGN KEY (`entity_id`) REFERENCES `entities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `records`
--

LOCK TABLES `records` WRITE;
/*!40000 ALTER TABLE `records` DISABLE KEYS */;
INSERT INTO `records` VALUES (1,1,1,'2025-07-23 12:57:08','2025-07-23 12:57:08',0,NULL),(2,1,2,'2025-07-23 12:57:36','2025-07-23 12:57:36',0,NULL),(3,1,3,'2025-07-23 13:15:17','2025-07-23 13:15:17',0,NULL),(4,1,3,'2025-07-23 13:15:36','2025-07-23 13:15:36',0,NULL),(5,1,2,'2025-07-23 14:36:38','2025-07-23 14:36:38',0,NULL);
/*!40000 ALTER TABLE `records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relationship_entities`
--

DROP TABLE IF EXISTS `relationship_entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `relationship_entities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `entity_one_id` int(11) NOT NULL,
  `entity_two_id` int(11) NOT NULL,
  `relationship_type` varchar(20) NOT NULL,
  `entity_one_label` varchar(255) DEFAULT NULL,
  `entity_two_label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_entity_id` (`entity_one_id`),
  KEY `child_entity_id` (`entity_two_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `fk_entity_one` FOREIGN KEY (`entity_one_id`) REFERENCES `entities` (`id`),
  CONSTRAINT `fk_entity_two` FOREIGN KEY (`entity_two_id`) REFERENCES `entities` (`id`),
  CONSTRAINT `relationship_entities_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relationship_entities`
--

LOCK TABLES `relationship_entities` WRITE;
/*!40000 ALTER TABLE `relationship_entities` DISABLE KEYS */;
INSERT INTO `relationship_entities` VALUES (1,1,1,2,'one_many','Client','Facturi'),(2,1,2,3,'many_many','Factură','Produse');
/*!40000 ALTER TABLE `relationship_entities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relationship_fields`
--

DROP TABLE IF EXISTS `relationship_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `relationship_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `relationship_id` int(11) DEFAULT NULL,
  `meta_key` varchar(100) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_type` varchar(50) NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `company_id` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_relationship_fields_relationship` (`relationship_id`),
  CONSTRAINT `fk_relationship_fields_relationship` FOREIGN KEY (`relationship_id`) REFERENCES `relationship_entities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relationship_fields`
--

LOCK TABLES `relationship_fields` WRITE;
/*!40000 ALTER TABLE `relationship_fields` DISABLE KEYS */;
INSERT INTO `relationship_fields` VALUES (1,2,'cantitate','Cantitate','number',1,0,1);
/*!40000 ALTER TABLE `relationship_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relationship_meta`
--

DROP TABLE IF EXISTS `relationship_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `relationship_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `relationship_record_id` int(11) NOT NULL,
  `relationship_field_id` int(11) DEFAULT NULL,
  `meta_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `relationship_id` (`relationship_record_id`),
  KEY `fk_meta_field` (`relationship_field_id`),
  CONSTRAINT `fk_meta_field` FOREIGN KEY (`relationship_field_id`) REFERENCES `relationship_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `relationship_meta_ibfk_1` FOREIGN KEY (`relationship_record_id`) REFERENCES `relationship_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relationship_meta`
--

LOCK TABLES `relationship_meta` WRITE;
/*!40000 ALTER TABLE `relationship_meta` DISABLE KEYS */;
INSERT INTO `relationship_meta` VALUES (38,19,1,'3'),(39,20,1,'3'),(40,22,1,'10');
/*!40000 ALTER TABLE `relationship_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relationship_records`
--

DROP TABLE IF EXISTS `relationship_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `relationship_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `parent_record_id` int(11) NOT NULL,
  `child_record_id` int(11) NOT NULL,
  `relationship_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `left_record_id` (`parent_record_id`),
  KEY `right_record_id` (`child_record_id`),
  CONSTRAINT `relationship_records_ibfk_1` FOREIGN KEY (`parent_record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `relationship_records_ibfk_2` FOREIGN KEY (`child_record_id`) REFERENCES `records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relationship_records`
--

LOCK TABLES `relationship_records` WRITE;
/*!40000 ALTER TABLE `relationship_records` DISABLE KEYS */;
INSERT INTO `relationship_records` VALUES (18,1,1,2,1,'2025-07-23 17:13:41'),(19,1,2,3,2,'2025-07-23 17:18:32'),(20,1,2,4,2,'2025-07-23 17:18:32'),(21,1,1,5,1,'2025-07-23 17:36:38'),(22,1,5,3,2,'2025-07-23 17:36:38');
/*!40000 ALTER TABLE `relationship_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,1,'Admin','2025-07-23 12:56:31');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `company_id` (`company_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,1,'andreica.serban@gmail.com','$2y$10$8nxa5gw3dBvA8eMU8jof/.aghczCfB6HWvEqbksSNHcZZNCefjl8e','2025-07-23 12:56:31');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-24 10:00:35
