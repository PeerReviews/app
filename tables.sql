-- MySQL dump 10.13  Distrib 8.0.40, for Linux (aarch64)
--
-- Host: localhost    Database: moodle
-- ------------------------------------------------------
-- Server version	11.6.2-MariaDB-ubu2404

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
-- Table structure for table `mdl_referentiel`
--

DROP TABLE IF EXISTS `mdl_referentiel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_referentiel` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_referentiel`
--

LOCK TABLES `mdl_referentiel` WRITE;
/*!40000 ALTER TABLE `mdl_referentiel` DISABLE KEYS */;
INSERT INTO `mdl_referentiel` VALUES (1,'Test'),(2,'cardiaque');
/*!40000 ALTER TABLE `mdl_referentiel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_keyword`
--

DROP TABLE IF EXISTS `mdl_keyword`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_keyword` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) NOT NULL COMMENT 'Mot-clé',
  `subcompetency` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_subcompetency` (`subcompetency`),
  CONSTRAINT `fk_subcompetency` FOREIGN KEY (`subcompetency`) REFERENCES `mdl_subcompetency` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_keyword`
--

LOCK TABLES `mdl_keyword` WRITE;
/*!40000 ALTER TABLE `mdl_keyword` DISABLE KEYS */;
INSERT INTO `mdl_keyword` VALUES (1,'sang',1),(2,'globule',1);
/*!40000 ALTER TABLE `mdl_keyword` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_competency`
--

DROP TABLE IF EXISTS `mdl_competency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_competency` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `referentiel` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `referentiel` (`referentiel`),
  CONSTRAINT `mdl_competency_ibfk_1` FOREIGN KEY (`referentiel`) REFERENCES `mdl_referentiel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency`
--

LOCK TABLES `mdl_competency` WRITE;
/*!40000 ALTER TABLE `mdl_competency` DISABLE KEYS */;
INSERT INTO `mdl_competency` VALUES (1,'coeur',2);
/*!40000 ALTER TABLE `mdl_competency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_subcompetency`
--

DROP TABLE IF EXISTS `mdl_subcompetency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_subcompetency` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `competency` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `competency` (`competency`),
  CONSTRAINT `mdl_subcompetency_ibfk_1` FOREIGN KEY (`competency`) REFERENCES `mdl_competency` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_subcompetency`
--

LOCK TABLES `mdl_subcompetency` WRITE;
/*!40000 ALTER TABLE `mdl_subcompetency` DISABLE KEYS */;
INSERT INTO `mdl_subcompetency` VALUES (1,'artere',1);
/*!40000 ALTER TABLE `mdl_subcompetency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_studentqcm_question`
--

DROP TABLE IF EXISTS `mdl_studentqcm_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_studentqcm_question` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(20) NOT NULL,
  `question` text DEFAULT NULL,
  `global_comment` text DEFAULT NULL,
  `context` text DEFAULT NULL,
  `competency` int(10) DEFAULT NULL,
  `subcompetency` int(10) DEFAULT NULL,
  `referentiel` int(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `type` enum('QCM','QCU','TCS') NOT NULL,
  `isPop` tinyint(1) NOT NULL DEFAULT 0,
  `popId` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `referentiel` (`referentiel`),
  KEY `competency` (`competency`),
  KEY `subcompetency` (`subcompetency`),
  KEY `fk_popId` (`popId`),
  CONSTRAINT `fk_popId` FOREIGN KEY (`popId`) REFERENCES `mdl_studentqcm_pop` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mdl_studentqcm_question_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mdl_studentqcm_question_ibfk_2` FOREIGN KEY (`referentiel`) REFERENCES `mdl_referentiel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mdl_studentqcm_question_ibfk_3` FOREIGN KEY (`competency`) REFERENCES `mdl_competency` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mdl_studentqcm_question_ibfk_4` FOREIGN KEY (`subcompetency`) REFERENCES `mdl_subcompetency` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_question`
--

LOCK TABLES `mdl_studentqcm_question` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_question` DISABLE KEYS */;
INSERT INTO `mdl_studentqcm_question` VALUES (2,2,'Question','Explication globale ','Contexte',1,1,2,0,'QCM',0,NULL),(52,2,'Question test','Explication','Contexte',1,1,2,0,'QCM',0,NULL),(53,2,'Question','Explication','Contexte',1,1,2,0,'QCM',0,NULL),(54,3,'q1','comment1','santé',1,1,2,0,'QCM',0,NULL),(55,4,'q2','comment2','santé',1,1,2,0,'QCM',0,NULL),(56,2,'Question QCU','bla','Contexte',1,1,2,0,'QCU',0,NULL);
/*!40000 ALTER TABLE `mdl_studentqcm_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_studentqcm_answer`
--

DROP TABLE IF EXISTS `mdl_studentqcm_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_studentqcm_answer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `question_id` int(10) NOT NULL,
  `isTrue` tinyint(1) NOT NULL DEFAULT 0,
  `answer` text NOT NULL,
  `explanation` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `mdl_studentqcm_answer_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `mdl_studentqcm_question` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_answer`
--

LOCK TABLES `mdl_studentqcm_answer` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_answer` DISABLE KEYS */;
INSERT INTO `mdl_studentqcm_answer` VALUES (6,2,1,'r','e'),(7,2,0,'r','e'),(8,2,0,'r','e'),(9,2,0,'r','e'),(10,2,0,'r','e'),(166,52,0,'r&eacute;ponse 1','expli 1'),(167,52,1,'r&eacute;ponse 2','expli 2'),(168,52,0,'r&eacute;ponse 3','expli 3'),(169,52,0,'r&eacute;ponse 4','expli 4'),(170,52,0,'r&eacute;ponse 5','expli 5'),(171,53,1,'1','e1'),(172,53,0,'2','e2'),(173,53,0,'3','e3'),(174,53,0,'4','e4'),(175,53,0,'5','e5'),(176,56,1,'bla','bla'),(177,56,0,'bla','bla'),(178,56,0,'bla','bla'),(179,56,0,'bla','bla'),(180,56,0,'bla','bla');
/*!40000 ALTER TABLE `mdl_studentqcm_answer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_studentqcm_assignedqcm`
--

DROP TABLE IF EXISTS `mdl_studentqcm_assignedqcm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_studentqcm_assignedqcm` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(10) NOT NULL,
  `prod1_id` bigint(10) NOT NULL,
  `prod2_id` bigint(10) NOT NULL,
  `prod3_id` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user` (`user_id`),
  KEY `fk_prod1` (`prod1_id`),
  KEY `fk_prod2` (`prod2_id`),
  KEY `fk_prod3` (`prod3_id`),
  CONSTRAINT `fk_prod1` FOREIGN KEY (`prod1_id`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prod2` FOREIGN KEY (`prod2_id`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prod3` FOREIGN KEY (`prod3_id`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_assignedqcm`
--

LOCK TABLES `mdl_studentqcm_assignedqcm` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_assignedqcm` DISABLE KEYS */;
INSERT INTO `mdl_studentqcm_assignedqcm` VALUES (1,2,3,4,NULL);
/*!40000 ALTER TABLE `mdl_studentqcm_assignedqcm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_question_keywords`
--

DROP TABLE IF EXISTS `mdl_question_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_question_keywords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) NOT NULL,
  `keyword_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_question_keyword` (`question_id`,`keyword_id`),
  KEY `mdl_question_keywords_ibfk_1` (`keyword_id`),
  CONSTRAINT `mdl_question_keywords_ibfk_1` FOREIGN KEY (`keyword_id`) REFERENCES `mdl_keyword` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_question_keywords`
--

LOCK TABLES `mdl_question_keywords` WRITE;
/*!40000 ALTER TABLE `mdl_question_keywords` DISABLE KEYS */;
INSERT INTO `mdl_question_keywords` VALUES (1,51,1),(2,51,2),(3,52,1),(4,52,2),(5,53,1),(6,53,2),(7,56,1),(8,56,2);
/*!40000 ALTER TABLE `mdl_question_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_studentqcm`
--

DROP TABLE IF EXISTS `mdl_studentqcm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_studentqcm` (
  `name` text NOT NULL,
  `intro` text DEFAULT NULL,
  `timecreated` int(10) DEFAULT NULL,
  `timemodified` int(10) DEFAULT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `introformat` int(4) DEFAULT 0,
  `start_date_1` int(10) NOT NULL,
  `end_date_1` int(10) NOT NULL,
  `end_date_tt_1` int(10) NOT NULL,
  `start_date_2` int(10) NOT NULL,
  `end_date_2` int(10) NOT NULL,
  `end_date_tt_2` int(10) NOT NULL,
  `start_date_3` int(10) NOT NULL,
  `end_date_3` int(10) NOT NULL,
  `end_date_tt_3` int(10) NOT NULL,
  `referentiel` int(11) DEFAULT NULL,
  `nbQcm` int(10) NOT NULL DEFAULT 0,
  `nbQcu` int(10) NOT NULL DEFAULT 0,
  `nbTcs` int(10) NOT NULL DEFAULT 0,
  `nbPop` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_referentiel` (`referentiel`),
  CONSTRAINT `FK_referentiel` FOREIGN KEY (`referentiel`) REFERENCES `mdl_referentiel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='Each record is one page and its config data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm`
--

LOCK TABLES `mdl_studentqcm` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm` DISABLE KEYS */;
INSERT INTO `mdl_studentqcm` VALUES ('SANTÉ',NULL,1738588503,1738624705,12,NULL,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,NULL,5,3,2,10);
/*!40000 ALTER TABLE `mdl_studentqcm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_studentqcm_pop`
--

DROP TABLE IF EXISTS `mdl_studentqcm_pop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_studentqcm_pop` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `mdl_studentqcm_pop_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_pop`
--

LOCK TABLES `mdl_studentqcm_pop` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_pop` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_studentqcm_pop` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `mdl_studentqcm_tierstemps`
--

DROP TABLE IF EXISTS `mdl_studentqcm_tierstemps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_studentqcm_tierstemps` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,

  PRIMARY KEY (`id`)
  -- CONSTRAINT `mdl_studentqcm_tierstemps_id_fk_1` FOREIGN KEY (`id`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_tierstemps`
--

LOCK TABLES `mdl_studentqcm_tierstemps` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_tierstemps` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_studentqcm_tierstemps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_studentqcm_prof`
--

DROP TABLE IF EXISTS `mdl_studentqcm_prof`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_studentqcm_prof` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,

  PRIMARY KEY (`id`)
  -- CONSTRAINT `mdl_studentqcm_prof_id_fk_1` FOREIGN KEY (`id`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_prof`
--

LOCK TABLES `mdl_studentqcm_prof` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_prof` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_studentqcm_prof` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_question_pop`
--

DROP TABLE IF EXISTS `mdl_question_pop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_question_pop` (
  `id` int(10) NOT NULL,
  `nbqcm` int(10),
  `nbqcu` int(10),
  `refId` int(100) NOT NULL,

  PRIMARY KEY (`id`),
  CONSTRAINT `mdl_question_pop_refId_fk_1` FOREIGN KEY (`refId`) REFERENCES `mdl_referentiel` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_question_pop`
--

LOCK TABLES `mdl_question_pop` WRITE;
/*!40000 ALTER TABLE `mdl_question_pop` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_question_pop` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-11 10:30:24
