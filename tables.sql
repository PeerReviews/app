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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_referentiel`
--

LOCK TABLES `mdl_referentiel` WRITE;
/*!40000 ALTER TABLE `mdl_referentiel` DISABLE KEYS */;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_keyword`
--

LOCK TABLES `mdl_keyword` WRITE;
/*!40000 ALTER TABLE `mdl_keyword` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_competency`
--

LOCK TABLES `mdl_competency` WRITE;
/*!40000 ALTER TABLE `mdl_competency` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_subcompetency`
--

LOCK TABLES `mdl_subcompetency` WRITE;
/*!40000 ALTER TABLE `mdl_subcompetency` DISABLE KEYS */;
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
  `indexation` int(10) NOT NULL,
  `question` text NOT NULL,
  `global_comment` text NOT NULL,
  `context` text NOT NULL,
  `competency` int(10) NOT NULL,
  `subcompetency` int(10) NOT NULL,
  `referentiel` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `referentiel` (`referentiel`),
  KEY `competency` (`competency`),
  KEY `subcompetency` (`subcompetency`),
  CONSTRAINT `mdl_studentqcm_question_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mdl_studentqcm_question_ibfk_2` FOREIGN KEY (`referentiel`) REFERENCES `mdl_referentiel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mdl_studentqcm_question_ibfk_3` FOREIGN KEY (`competency`) REFERENCES `mdl_competency` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mdl_studentqcm_question_ibfk_4` FOREIGN KEY (`subcompetency`) REFERENCES `mdl_subcompetency` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_question`
--

LOCK TABLES `mdl_studentqcm_question` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `mdl_studentqcm_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_question_keywords`
--

DROP TABLE IF EXISTS `mdl_question_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_question_keywords` (
  `question_id` int(10) NOT NULL,
  `keyword_id` int(10) NOT NULL,
  PRIMARY KEY (`question_id`,`keyword_id`),
  KEY `keyword_id` (`keyword_id`),
  CONSTRAINT `mdl_question_keywords_ibfk_1` FOREIGN KEY (`keyword_id`) REFERENCES `mdl_keyword` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_question_keywords`
--

LOCK TABLES `mdl_question_keywords` WRITE;
/*!40000 ALTER TABLE `mdl_question_keywords` DISABLE KEYS */;
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
  `intro` text NOT NULL,
  `timecreated` int(10) DEFAULT NULL,
  `timemodified` int(10) DEFAULT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `introformat` int(4) NOT NULL DEFAULT 0,
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
INSERT INTO `mdl_studentqcm` VALUES ('SANTÉ','Voici une description',1738588503,1738588503,12,0,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,NULL);
/*!40000 ALTER TABLE `mdl_studentqcm` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-03 14:32:38
