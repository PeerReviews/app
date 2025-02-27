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
  `isCustom` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `competency` (`competency`),
  CONSTRAINT `mdl_subcompetency_ibfk_1` FOREIGN KEY (`competency`) REFERENCES `mdl_competency` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_subcompetency`
--

LOCK TABLES `mdl_subcompetency` WRITE;
/*!40000 ALTER TABLE `mdl_subcompetency` DISABLE KEYS */;
INSERT INTO `mdl_subcompetency` VALUES (1,'artere',1,0),(2,'test',1,0),(3,'test2',1,0),(4,'test3',1,1),(5,'mot',1,1),(6,'test4',1,1);
/*!40000 ALTER TABLE `mdl_subcompetency` ENABLE KEYS */;
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
INSERT INTO `mdl_studentqcm` VALUES ('SANTÉ',NULL,1738588503,1738624705,12,NULL,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,1738537200,NULL,5,3,2,3);
/*!40000 ALTER TABLE `mdl_studentqcm` ENABLE KEYS */;
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
  `popTypeId` int(10) DEFAULT NULL,
  `grade` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `referentiel` (`referentiel`),
  KEY `competency` (`competency`),
  KEY `subcompetency` (`subcompetency`),
  KEY `fk_popId` (`popId`),
  KEY `fk_mdl_studentqcm_question_poptypeid` (`popTypeId`),
  CONSTRAINT `fk_mdl_studentqcm_question_poptypeid` FOREIGN KEY (`popTypeId`) REFERENCES `mdl_question_pop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_popId` FOREIGN KEY (`popId`) REFERENCES `mdl_studentqcm_pop` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mdl_studentqcm_question_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_question`
--

LOCK TABLES `mdl_studentqcm_question` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_question` DISABLE KEYS */;
INSERT INTO `mdl_studentqcm_question` VALUES (54,3,'q1','comment1','santé',1,1,2,0,'QCM',0,NULL,NULL,NULL),(55,4,'q2','comment2','santé',1,1,2,0,'QCM',0,NULL,NULL,NULL),(57,3,'Quel est le rôle principal de l\'artère coronaire ?','Question sur la physiologie cardiaque.','Système cardiovasculaire',1,1,2,0,'QCM',0,NULL,NULL,NULL),(58,3,'Quel est le rôle principal de l\'artère coronaire ?','Question sur la physiologie cardiaque.','Système cardiovasculaire',1,1,2,1,'QCM',0,NULL,NULL,NULL),(59,3,'Quel est le rôle principal du ventricule gauche du cœur ?','Question sur la physiologie cardiaque.','Système cardiovasculaire',1,1,2,1,'QCU',0,NULL,NULL,NULL),(61,3,'Quelles sont les étapes du traitement médicamenteux d\'une insuffisance cardiaque ?','Question sur le traitement des maladies cardiaques.','Système cardiovasculaire',1,1,2,1,'TCS',0,NULL,NULL,NULL),(77,2,'question pop',NULL,NULL,1,4,2,0,'QCU',1,16,NULL,NULL),(78,2,'question pop',NULL,NULL,1,4,2,0,'QCU',1,17,NULL,NULL),(79,2,'a',NULL,NULL,1,1,2,0,'QCU',1,18,NULL,NULL),(80,2,'aa',NULL,NULL,1,1,2,0,'QCU',1,19,NULL,NULL),(93,2,'test','','contexte',0,0,0,0,'QCM',0,NULL,NULL,NULL),(94,2,'Question',NULL,'',NULL,NULL,NULL,0,'QCM',0,NULL,NULL,NULL),(95,6,'Quelle est la principale cause des caries dentaires ?*','Les caries sont causées par la prolifération de bactéries qui attaquent l’émail des dents en présence de sucres.','Sant&eacute; bucco-dentaire',1,1,2,1,'QCM',0,NULL,NULL,5),(96,6,'Quel est l’organe le plus grand du corps humain ?','L’organe le plus grand du corps humain est essentiel à la protection et à la régulation thermique.  ','Anatomie humaine &nbsp;',1,6,2,1,'QCM',0,NULL,NULL,5),(97,6,'Quel nutriment est la principale source d’énergie pour le corps ?','L’énergie est indispensable pour le bon fonctionnement du corps humain, et elle provient principalement d’un type de nutriment.','Nutrition',1,4,2,1,'QCM',0,NULL,NULL,5),(98,6,'Quelle est la principale fonction des globules rouges ?','Les globules rouges jouent un rôle fondamental dans le transport d’un élément vital dans l’organisme.  ','Syst&egrave;me sanguin',1,4,2,1,'QCM',0,NULL,NULL,5),(99,6,'Quelle maladie est causée par une carence en fer ?','Un manque de fer peut entraîner une maladie affectant l’oxygénation du corps.','Maladies et nutrition',1,4,2,1,'QCM',0,NULL,NULL,5),(100,6,'Quelle est la principale cause du cancer du poumon ?','Le cancer du poumon est une des maladies les plus répandues et évitables grâce à la prévention.','Maladies et pr&eacute;vention&nbsp;',1,4,2,1,'QCU',0,NULL,NULL,5),(101,6,'Quelle est la principale source de vitamine C ?','La vitamine C est essentielle pour le système immunitaire et la santé de la peau. Elle se trouve principalement dans certains fruits et légumes.','Nutrition et vitamines',1,4,2,1,'QCU',0,NULL,NULL,5),(102,6,'Pourquoi est-il important de se laver les mains régulièrement ?','Se laver les mains réduit considérablement la propagation des maladies infectieuses et limite les contaminations croisées.','Hygi&egrave;ne et pr&eacute;vention',1,1,2,1,'QCU',0,NULL,NULL,5),(103,6,'Quel est l\'organe responsable de la production d\'insuline ?','L\'insuline est une hormone essentielle pour réguler le taux de sucre dans le sang.','Diab&egrave;te et m&eacute;tabolisme',1,1,2,1,'QCU',1,27,1,5),(104,6,'Quelle vitamine est principalement synthétisée grâce à l’exposition au soleil ?','Certaines vitamines sont produites naturellement par le corps humain sous certaines conditions.','Nutrition et sant&eacute;&nbsp;',1,4,2,1,'QCM',1,28,2,5),(105,6,'Quel est l’effet principal d’une consommation excessive de sel ?','Un excès de sel dans l’alimentation peut entraîner des conséquences graves sur la santé.','Nutrition et maladies',1,6,2,1,'QCM',1,29,3,5),(106,6,'Quel est l’effet principal d’une consommation excessive de sel ?','Un excès de sel dans l’alimentation peut entraîner des conséquences graves sur la santé. ','Nutrition et maladies',1,6,2,1,'QCM',1,30,3,5),(107,6,'Quelle est la meilleure méthode pour éviter les infections virales ?','Les virus se propagent facilement, mais certaines précautions peuvent réduire les risques.','Pr&eacute;vention et hygi&egrave;ne&nbsp;',1,4,2,1,'QCU',1,31,3,5),(108,6,'Quelle est la meilleure façon de réduire le risque d’ostéoporose ?','L’ostéoporose fragilise les os, augmentant le risque de fractures.','Sant&eacute; des os&nbsp;',1,1,2,1,'QCU',1,32,3,5),(109,6,'Quel est l’organe qui filtre les toxines du sang ?','Certains organes sont essentiels pour éliminer les substances nocives du corps.','Syst&egrave;me digestif et m&eacute;tabolisme',1,4,2,1,'QCM',1,33,4,5),(110,6,'Quelle est la maladie neurodégénérative la plus fréquente ?','Certaines maladies affectent progressivement les fonctions cérébrales avec l’âge.','Maladies et vieillissement',1,6,2,1,'QCU',1,34,4,5),(111,6,'Quel est le principal facteur de risque des maladies cardiovasculaires ?','Les maladies du cœur sont influencées par plusieurs facteurs, mais un en particulier est prédominant.','Sant&eacute; cardiovasculaire',1,6,2,1,'QCM',1,35,4,5),(112,6,'Quelle est la principale cause du diabète de type 2 ?','Le diabète de type 2 est une maladie influencée par le mode de vie.','Maladies m&eacute;taboliques&nbsp;',1,4,2,1,'QCU',1,36,4,5),(113,6,'Confiance en vos compétences en communication avec les patients','Cette question vise à évaluer la perception des étudiants en santé quant à leurs compétences en communication médicale. Un bon niveau de communication permet une meilleure adhésion des patients aux traitements et renforce la relation thérapeutique.','Dans le cadre de votre formation en sant&eacute;, vous &ecirc;tes amen&eacute;(e) &agrave; interagir avec des patients pr&eacute;sentant des pr&eacute;occupations vari&eacute;es. La communication est un &eacute;l&eacute;ment cl&eacute; pour &eacute;tablir une relation de confiance et garantir une prise en charge efficace.',1,4,2,1,'TCS',0,NULL,NULL,5),(114,6,'Dans quelle mesure vous sentez-vous capable de gérer efficacement votre stress en situation d’urgence médicale ?','Cette question permet d’évaluer la perception des étudiants quant à leur capacité à faire face au stress en situation critique. Une gestion efficace du stress est indispensable pour éviter les erreurs médicales et assurer une prise en charge optimale des patients.','Le domaine de la sant&eacute; expose r&eacute;guli&egrave;rement les professionnels &agrave; des situations stressantes, notamment lors d&rsquo;urgences vitales. La capacit&eacute; &agrave; g&eacute;rer son stress est essentielle pour prendre des d&eacute;cisions rapides et appropri&eacute;es.',1,4,2,1,'TCS',0,NULL,NULL,5);
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
  `answer` text DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `indexation` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `mdl_studentqcm_answer_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `mdl_studentqcm_question` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=379 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_answer`
--

LOCK TABLES `mdl_studentqcm_answer` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_answer` DISABLE KEYS */;
INSERT INTO `mdl_studentqcm_answer` VALUES (181,54,1,'Réponse correcte 1 pour q1','Explication 1 pour q1',0),(182,54,0,'Réponse incorrecte 2 pour q1','Explication 2 pour q1',0),(183,54,0,'Réponse incorrecte 3 pour q1','Explication 3 pour q1',0),(184,54,1,'Réponse correcte 4 pour q1','Explication 4 pour q1',0),(185,54,0,'Réponse incorrecte 5 pour q1','Explication 5 pour q1',0),(186,55,0,'Réponse incorrecte 1 pour q2','Explication 1 pour q2',0),(187,55,1,'Réponse correcte 2 pour q2','Explication 2 pour q2',0),(188,55,0,'Réponse incorrecte 3 pour q2','Explication 3 pour q2',0),(189,55,1,'Réponse correcte 4 pour q2','Explication 4 pour q2',0),(190,55,0,'Réponse incorrecte 5 pour q2','Explication 5 pour q2',0),(191,57,1,'Le rôle principal de l\'artère coronaire est de fournir du sang riche en oxygène au cœur.','L\'artère coronaire irrigue le muscle cardiaque pour lui fournir l\'oxygène nécessaire à son bon fonctionnement.',0),(192,57,0,'L\'artère coronaire a pour rôle de filtrer les toxines du sang.','Cette affirmation est incorrecte. L\'artère coronaire est responsable de l\'irrigation du cœur en oxygène, et non de la filtration des toxines.',0),(193,57,0,'Les artères coronaires alimentent l\'estomac en oxygène.','Cette réponse est incorrecte. Les artères coronaires irriguent le cœur et non l\'estomac.',0),(194,57,0,'L\'artère coronaire transporte le sang riche en dioxyde de carbone vers le cœur.','Cette réponse est incorrecte. L\'artère coronaire transporte du sang oxygéné vers le cœur, et non du sang riche en dioxyde de carbone.',0),(195,57,0,'L\'artère coronaire transporte les nutriments vers le cœur.','Bien que les artères coronaires transportent du sang, elles n\'apportent pas directement de nutriments, mais de l\'oxygène au cœur.',0),(196,59,1,'Le ventricule gauche pompe le sang oxygéné vers l\'aorte pour le distribuer dans tout le corps.','Le ventricule gauche est responsable de l\'éjection du sang oxygéné dans l\'aorte, permettant ainsi son transport vers tous les organes et tissus du corps.',0),(197,59,0,'Le ventricule gauche pompe le sang désoxygéné vers les poumons.','Cette réponse est incorrecte. Le ventricule droit pompe le sang désoxygéné vers les poumons, tandis que le ventricule gauche envoie du sang oxygéné dans l\'aorte.',0),(198,59,0,'Le ventricule gauche est responsable de la filtration du sang.','Cette réponse est incorrecte. La filtration du sang n\'est pas le rôle du ventricule gauche. Le cœur pompe simplement le sang vers les poumons ou le reste du corps.',0),(199,59,0,'Le ventricule gauche régule la pression sanguine dans les veines.','Cette réponse est incorrecte. Le rôle du ventricule gauche est de pomper le sang dans l\'aorte, pas de réguler la pression dans les veines.',0),(200,59,0,'Le ventricule gauche transporte le sang des veines vers les artères.','Cette réponse est incorrecte. Le ventricule gauche transporte le sang depuis l\'oreillette gauche vers l\'aorte, pas des veines vers les artères.',0),(201,61,0,'Pas du tout','',0),(202,61,0,'Plutôt pas du tout','',0),(203,61,1,'Plutôt d\'accord','',0),(204,61,0,'Plutôt d\'accord','',0),(205,61,0,'Tout à fait','',0),(269,93,0,'r&eacute;ponse 1',NULL,1),(270,93,0,NULL,NULL,2),(271,93,0,NULL,NULL,3),(272,93,0,NULL,NULL,4),(273,93,1,NULL,NULL,5),(274,94,0,NULL,NULL,1),(275,94,0,NULL,NULL,2),(276,94,0,NULL,NULL,3),(277,94,0,NULL,NULL,4),(278,94,0,NULL,NULL,5),(279,95,1,'Consommation excessive de sucre','Les bact&eacute;ries transforment le sucre en acide qui attaque l&rsquo;&eacute;mail. &nbsp;',1),(280,95,1,'Manque de calcium','Le calcium est important pour les dents, mais ne cause pas directement les caries.',2),(281,95,0,'Mauvaise haleine','La mauvaise haleine est un sympt&ocirc;me, pas une cause des caries.&nbsp;',3),(282,95,0,'Consommation de prot&eacute;ines','Les prot&eacute;ines n\'ont pas d\'effet direct sur les caries. &nbsp;',4),(283,95,0,'Trop de sommeil','Le sommeil n\'a pas d\'impact direct sur les caries.&nbsp;',5),(284,96,1,'Peau','La peau est le plus grand organe du corps humain. &nbsp;',1),(285,96,0,'Foie','Le foie est un organe interne volumineux, mais plus petit que la peau.',2),(286,96,0,'Poumons','Les poumons sont grands, mais pas l&rsquo;organe le plus &eacute;tendu. &nbsp;',3),(287,96,0,'Cerveau','Le cerveau est crucial, mais plus petit en surface. &nbsp;',4),(288,96,0,'Intestins','Les intestins sont longs mais ne sont pas l&rsquo;organe le plus grand.&nbsp;',5),(289,97,1,'Glucides','Les glucides sont la source principale d&rsquo;&eacute;nergie pour l&rsquo;organisme. &nbsp;',1),(290,97,1,'Prot&eacute;ines','Elles servent &agrave; la construction musculaire, mais ne sont pas la principale source d&rsquo;&eacute;nergie.',2),(291,97,0,'Lipides','Ils stockent l&rsquo;&eacute;nergie mais ne sont pas la source primaire.',3),(292,97,0,'Fibres','Elles aident &agrave; la digestion mais ne fournissent pas d&rsquo;&eacute;nergie.',4),(293,97,0,'Eau','L&rsquo;eau est essentielle mais ne fournit pas d&rsquo;&eacute;nergie.',5),(294,98,0,'Combattre les infections','Ce r&ocirc;le appartient aux globules blancs.',1),(295,98,1,'Transporter l&rsquo;oxyg&egrave;ne','Les globules rouges transportent l&rsquo;oxyg&egrave;ne vers les cellules. &nbsp;',2),(296,98,0,'Produire des hormones','Les hormones sont produites par des glandes.',3),(297,98,0,'R&eacute;guler la temp&eacute;rature corporelle','La thermor&eacute;gulation est assur&eacute;e par d&rsquo;autres m&eacute;canismes.',4),(298,98,0,'Dissoudre les graisses','Les globules rouges n&rsquo;ont pas ce r&ocirc;le.',5),(299,99,0,'Ost&eacute;oporose','L&rsquo;ost&eacute;oporose est caus&eacute;e par un manque de calcium.',1),(300,99,0,'Diab&egrave;te','Le diab&egrave;te est li&eacute; &agrave; l&rsquo;insuline, pas au fer.',2),(301,99,1,'An&eacute;mie','L&rsquo;an&eacute;mie est due &agrave; une insuffisance de fer dans le sang.',3),(302,99,0,'Hypertension','L&rsquo;hypertension est li&eacute;e au sel et au stress.',4),(303,99,0,'Scl&eacute;rose en plaques','Cette maladie est d&rsquo;origine neurologique.',5),(304,100,0,'Manque de sommeil','Il peut affecter la sant&eacute;, mais pas causer ce cancer.',1),(305,100,0,'Trop d&rsquo;&eacute;cran','Les &eacute;crans ne causent pas directement ce cancer.',2),(306,100,0,'Manque d&rsquo;exercice','Le manque d&rsquo;activit&eacute; physique a d&rsquo;autres impacts sur la sant&eacute;.&nbsp;',3),(307,100,0,'Mauvaise alimentation','Elle peut jouer un r&ocirc;le, mais ce n&rsquo;est pas la cause principale.',4),(308,100,1,'Tabagisme','Le tabac est la premi&egrave;re cause du cancer du poumon.',5),(309,101,1,'Orange','Les oranges sont riches en vitamine C.',1),(310,101,0,'Banane','Les bananes contiennent peu de vitamine C.',2),(311,101,0,'Pain complet','Le pain complet ne contient presque pas de vitamine C.',3),(312,101,0,'Viande rouge','La viande rouge n\'est pas une source de vitamine C.',4),(313,101,0,'Fromage','Le fromage contient principalement du calcium et des prot&eacute;ines.',5),(314,102,1,'&Eacute;limine les bact&eacute;ries et virus','Le lavage des mains tue les agents pathog&egrave;nes.',1),(315,102,0,'Favorise la digestion','Se laver les mains n\'a aucun effet sur la digestion.',2),(316,102,0,'Am&eacute;liore la concentration','Le lavage des mains ne joue aucun r&ocirc;le sur la concentration.',3),(317,102,0,'Renforce les muscles','L\'hygi&egrave;ne des mains n\'affecte pas les muscles.',4),(318,102,0,'Augmente l\'&eacute;nergie','Se laver les mains ne donne pas d\'&eacute;nergie suppl&eacute;mentaire.',5),(319,103,1,'Pancr&eacute;as','Le pancr&eacute;as produit l\'insuline pour r&eacute;guler la glyc&eacute;mie.',1),(320,103,0,'Foie','Le foie stocke du glucose mais ne produit pas d\'insuline.',2),(321,103,0,'Reins','Les reins filtrent le sang mais ne produisent pas d\'insuline.',3),(322,103,0,'C&oelig;ur','Le c&oelig;ur pompe le sang mais n\'intervient pas dans la r&eacute;gulation du glucose.',4),(323,103,0,'Poumons','Les poumons assurent la respiration et ne produisent pas d\'insuline.&nbsp;',5),(324,104,0,'Vitamine B12','Elle est pr&eacute;sente dans les produits animaux.',1),(325,104,0,'Vitamine K','Elle est surtout produite par la flore intestinale.',2),(326,104,1,'Vitamine D','Elle est produite sous l&rsquo;effet des rayons UV du soleil.',3),(327,104,0,'Vitamine A','Pr&eacute;sente dans les carottes et le foie, mais non produite par le soleil.',4),(328,104,0,'Vitamine C','Elle provient essentiellement des fruits et l&eacute;gumes.',5),(329,105,1,'Hypertension art&eacute;rielle','Trop de sel peut augmenter la pression sanguine.',1),(330,105,0,'Diab&egrave;te','Le diab&egrave;te est li&eacute; au sucre, pas au sel.',2),(331,105,0,'Perte de m&eacute;moire','Il n&rsquo;y a pas de lien direct avec la m&eacute;moire.',3),(332,105,0,'Carence en fer','Le sel n&rsquo;interf&egrave;re pas avec l&rsquo;absorption du fer.&nbsp;',4),(333,105,0,'Fractures osseuses','Le sel ne cause pas directement de fractures.',5),(334,106,1,'Hypertension art&eacute;rielle','Trop de sel peut augmenter la pression sanguine.',1),(335,106,0,'Diab&egrave;te','Le diab&egrave;te est li&eacute; au sucre, pas au sel.',2),(336,106,0,'Perte de m&eacute;moire','Il n&rsquo;y a pas de lien direct avec la m&eacute;moire.',3),(337,106,0,'Carence en fer','Le sel n&rsquo;interf&egrave;re pas avec l&rsquo;absorption du fer.',4),(338,106,0,'Fractures osseuses','Le sel ne cause pas directement de fractures.',5),(339,107,0,'Dormir 10 heures par nuit','Le sommeil est essentiel, mais pas suffisant contre les infections.',1),(340,107,0,'Faire du sport','Le sport renforce l&rsquo;immunit&eacute;, mais ne pr&eacute;vient pas directement les infections.',2),(341,107,0,'Boire beaucoup d&rsquo;eau','L&rsquo;hydratation est importante mais ne pr&eacute;vient pas directement les infections virales.',3),(342,107,1,'Se laver les mains r&eacute;guli&egrave;rement','Une bonne hygi&egrave;ne des mains limite la transmission des virus.',4),(343,107,0,'Prendre des antibiotiques','Les antibiotiques sont inefficaces contre les virus.',5),(344,108,1,'Consommer du calcium et de la vitamine D','Ces nutriments sont essentiels &agrave; la sant&eacute; des os.',1),(345,108,0,'Boire du caf&eacute;','La caf&eacute;ine peut au contraire nuire &agrave; la fixation du calcium.',2),(346,108,0,'&Eacute;viter l&rsquo;exposition au soleil','La vitamine D produite par le soleil est b&eacute;n&eacute;fique.',3),(347,108,0,'R&eacute;duire les prot&eacute;ines','Un apport en prot&eacute;ines est n&eacute;cessaire pour la structure osseuse.',4),(348,108,0,'Ne pas marcher trop souvent','L&rsquo;exercice physique est au contraire b&eacute;n&eacute;fique pour les os.',5),(349,109,1,'Foie','Il d&eacute;toxifie le sang et transforme les substances nocives.',1),(350,109,0,'Reins','Ils filtrent les d&eacute;chets, mais le foie g&egrave;re les toxines.',2),(351,109,0,'C&oelig;ur','Il pompe le sang mais ne filtre pas les toxines.',3),(352,109,0,'Poumons','Ils &eacute;liminent le CO₂ mais ne filtrent pas les toxines.',4),(353,109,0,'Estomac','Il dig&egrave;re les aliments mais ne filtre pas le sang.',5),(354,110,0,'Syndrome de Down','Ce n&rsquo;est pas une maladie neurod&eacute;g&eacute;n&eacute;rative.&nbsp;',1),(355,110,0,'Huntington','Rare et d&rsquo;origine g&eacute;n&eacute;tique.',2),(356,110,1,'Maladie d&rsquo;Alzheimer','C&rsquo;est la forme la plus courante de d&eacute;mence.',3),(357,110,0,'Maladie de Parkinson','Elle affecte le mouvement mais est moins fr&eacute;quente qu&rsquo;Alzheimer.',4),(358,110,0,'Scl&eacute;rose en plaques','Maladie neurologique, mais pas la plus fr&eacute;quente.',5),(359,111,1,'Tabagisme','Fumer augmente consid&eacute;rablement le risque cardiovasculaire.',1),(360,111,0,'Manque de sommeil','Il peut jouer un r&ocirc;le, mais ce n&rsquo;est pas le principal facteur.',2),(361,111,0,'Consommation de fruits','Au contraire, les fruits sont b&eacute;n&eacute;fiques.',3),(362,111,0,'Exposition au froid','Elle peut aggraver certains probl&egrave;mes cardiaques, mais ce n&rsquo;est pas le principal facteur.',4),(363,111,0,'&Eacute;coute excessive de musique forte','Aucun lien direct avec les maladies cardiovasculaires.',5),(364,112,1,'Alimentation riche en sucre et manque d&rsquo;exercice','Ces facteurs augmentent le risque de r&eacute;sistance &agrave; l&rsquo;insuline.',1),(365,112,0,'Manque de sommeil','Il peut influencer le m&eacute;tabolisme, mais ce n&rsquo;est pas la cause principale.',2),(366,112,0,'Trop d&rsquo;exposition au soleil','Aucun lien direct.',3),(367,112,0,'D&eacute;shydratation chronique','Boire de l&rsquo;eau est important, mais ce n&rsquo;est pas la cause principale.',4),(368,112,0,'Trop de prot&eacute;ines','Les prot&eacute;ines ne sont pas impliqu&eacute;es directement dans le diab&egrave;te.',5),(369,113,0,'Pas du tout capable',NULL,1),(370,113,1,'Peu capable',NULL,2),(371,113,0,'Modérément capable',NULL,3),(372,113,0,'Plutôt capable',NULL,4),(373,113,0,'Tout à fait capable',NULL,5),(374,114,0,'Pas du tout capable',NULL,1),(375,114,1,'Peu capable',NULL,2),(376,114,0,'Modérément capable',NULL,3),(377,114,0,'Plutôt capable',NULL,4),(378,114,0,'Tout à fait capable',NULL,5);
/*!40000 ALTER TABLE `mdl_studentqcm_answer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_studentqcm_evaluation`
--

DROP TABLE IF EXISTS `mdl_studentqcm_evaluation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_studentqcm_evaluation` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `question_id` int(10) NOT NULL,
  `explanation` text NOT NULL,
  `userid` bigint(20) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `grade` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `fk_userid` (`userid`),
  CONSTRAINT `fk_userid` FOREIGN KEY (`userid`) REFERENCES `mdl_user` (`id`),
  CONSTRAINT `mdl_studentqcm_evaluation_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `mdl_studentqcm_question` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_evaluation`
--

LOCK TABLES `mdl_studentqcm_evaluation` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_evaluation` DISABLE KEYS */;
INSERT INTO `mdl_studentqcm_evaluation` VALUES (1,54,'évaluation modifiée 4',2,1,NULL),(2,57,'Test évaluation 1',2,1,NULL),(6,95,'La structure de la question est claire, avec un contexte pertinent et des réponses logiques. Cependant, l\'option \"Manque de calcium\" pourrait être mieux précisée en mentionnant qu\'il ne cause pas directement les caries. Sinon, le texte est sans faute et bien formulé. Un ajout d\'informations dans les explications renforcerait la clarté.',4,1,2),(7,96,'La structure est claire, et la question bien formulée. Les réponses sont précises et logiques, ce qui facilite la compréhension. Toutefois, pour plus de clarté, il serait utile d’ajouter un petit détail dans l’explication, par exemple en mentionnant que la peau couvre tout le corps, ce qui justifie sa taille. Aucun souci d’orthographe à signaler. C’est une question solide.',4,1,2),(8,97,'La question est bien formulée et claire. Les réponses sont concises et compréhensibles, ce qui permet de saisir facilement la réponse correcte. Il serait utile de mentionner brièvement que les glucides sont utilisés rapidement par l’organisme, ce qui explique leur rôle énergétique. Aucun problème d\'orthographe, le texte est fluide et précis.',4,1,2),(9,98,'La question est claire et les options sont bien définies. L’explication de la fonction des globules rouges est bien présentée, et la distinction avec les globules blancs est pertinente. Il serait cependant intéressant d’ajouter une brève mention sur l’importance de l’hémoglobine dans ce transport. Aucune faute d\'orthographe n’est présente.',4,1,2),(10,99,'La question est simple et la réponse correcte est évidente. L’explication de la carence en fer est bien formulée, mais il pourrait être utile de préciser davantage les symptômes de l’anémie pour rendre l’information plus complète. Aucun problème d’orthographe ou de formulation ici.',4,1,2),(11,100,'La question est pertinente et bien formulée. Le lien entre le tabagisme et le cancer du poumon est bien mis en avant. L’explication globale est claire, mais il serait intéressant d’ajouter que la prévention par l’arrêt du tabac peut significativement réduire le risque. Aucun souci d\'orthographe.',4,1,2),(12,101,'La question est claire et bien structurée. L\'explication globale sur l\'importance de la vitamine C est pertinente et concise. Les options sont bien choisies, et la réponse correcte est évidente. Il serait intéressant d’ajouter un petit détail concernant d\'autres fruits riches en vitamine C, comme le kiwi, pour compléter l’information. Aucun souci orthographique, tout est bien écrit.',4,1,2),(13,102,'Cette question est très pertinente et bien structurée. L\'explication globale met en évidence l\'importance du lavage des mains pour prévenir les infections. Les options sont claires, et la réponse correcte est évidente. L’option \"Favorise la digestion\" pourrait être davantage détaillée pour éviter toute confusion. Le texte est bien rédigé, sans erreurs d\'orthographe.',4,1,2),(14,103,'La question est claire et bien formulée. L’explication globale est pertinente et aide à bien comprendre le rôle de l’insuline. Les options sont adaptées, et la distinction entre les organes est bien faite. Il pourrait être utile d\'ajouter un rappel sur le rôle du pancréas dans la régulation de la glycémie pour encore plus de clarté. Le texte est sans faute et fluide.',4,1,2),(15,104,'La question est simple et claire. L\'explication est pertinente, et la réponse correcte est évidente. Les options sont bien choisies, et la distinction entre les vitamines est correcte. Il serait intéressant d’ajouter un petit détail sur les effets bénéfiques de la vitamine D pour renforcer la réponse. Le texte est bien rédigé et sans erreur.',4,1,2),(16,105,'La question est pertinente et bien formulée. L’explication globale sur l’effet du sel est simple et efficace. L\'option \"Carence en fer\" pourrait prêter à confusion, mais elle est bien justifiée. Cette question est informative et facilement compréhensible. Le texte est fluide et sans erreur.',4,1,2),(17,106,'La question est pertinente et bien formulée. L’explication globale sur l’effet du sel est simple et efficace. L\'option \"Carence en fer\" pourrait prêter à confusion, mais elle est bien justifiée. Cette question est informative et facilement compréhensible. Le texte est fluide et sans erreur.',4,1,2),(18,107,'La question est pertinente et bien formulée. L’explication sur le lavage des mains est très claire, et la réponse correcte est évidente. L\'option \"Prendre des antibiotiques\" est bien placée pour être éliminée, car elle est incorrecte. L\'ajout d’un conseil supplémentaire pour renforcer l\'immunité pourrait enrichir la réponse. Aucune faute d\'orthographe à signaler.',4,1,2),(19,108,'La question est bien formulée et permet de tester des connaissances en matière de santé des os. L’explication sur la vitamine D et le calcium est correcte et facile à comprendre. Les options sont bien choisies, et la réponse correcte est évidente. L\'option \"Réduire les protéines\" peut porter à confusion, car les protéines sont nécessaires à la santé des os. Aucun problème d\'orthographe ici.',4,1,2),(20,109,'La question est claire, et l’explication sur la fonction du foie est bien présentée. Les options sont logiques, et la réponse correcte est bien mise en avant. Un petit détail sur le rôle complémentaire des reins aurait ajouté un plus à l’explication. Aucun problème d’orthographe, le texte est fluide et clair.',4,1,2),(21,110,'La question est bien formulée, et les options sont adaptées. L’explication de la maladie d’Alzheimer est claire et permet de bien comprendre le lien avec le vieillissement. L\'option \"Sclérose en plaques\" pourrait porter à confusion pour certains, mais elle est bien justifiée. Aucune faute d\'orthographe, texte bien rédigé.',4,1,2),(22,111,'La question est pertinente et les options bien choisies. L’explication sur le tabagisme comme principal facteur de risque est correcte. Une mention des autres facteurs, comme une alimentation riche en graisses, pourrait compléter la réponse. Aucun souci d\'orthographe ici, tout est bien rédigé.',4,1,2),(23,112,'La question est très pertinente et bien formulée. L\'explication sur les causes du diabète de type 2 est correcte et concise. L\'option \"Trop d\'exposition au soleil\" peut prêter à confusion, mais elle est clairement incorrecte. Le texte est fluide, et aucune faute d\'orthographe n\'est à signaler.',4,1,2),(24,95,'Le format est efficace, mais l\'option \"Mauvaise haleine\" pourrait être reformulée pour éviter toute confusion, car elle est un symptôme et non une cause. Le texte est fluide et sans fautes, mais un peu plus de détails dans la réponse correcte sur le sucre serait utile pour mieux comprendre son impact.',8,1,4),(25,96,'La question est bien construite et permet de tester la connaissance de l’anatomie. L’explication globale est simple et pertinente. L’option \"Cerveau\" pourrait prêter à confusion pour certains, car il est un organe important. Mais globalement, la question est bien structurée et sans erreur. Un petit ajout sur la fonction de la peau enrichirait la réponse.',8,1,4),(26,97,'Les options sont clairement énoncées et la question bien construite. L\'explication globale est bonne, mais il serait intéressant de préciser que les glucides sont la première source d’énergie avant que les lipides ne soient utilisés en cas de besoin. Rien à redire sur l\'orthographe. Une question efficace et facile à comprendre.',8,1,4),(27,98,'Bonne question, avec des réponses qui se distinguent facilement. L’explication de la fonction des globules rouges est claire, mais l’ajout d’un détail sur l’hémoglobine renforcerait encore la réponse. Aucune erreur orthographique à signaler, le texte est bien écrit et fluide.',8,1,4),(28,99,'La question est claire et directe, et les options sont bien choisies. Une mention supplémentaire sur les effets de l’anémie sur l’organisme rendrait l\'explication encore plus complète. Le texte est sans faute, et les informations sont bien structurées.',8,1,4),(29,100,'La question est concise et bien construite. Les options sont adaptées, et la réponse correcte est évidente. L\'explication pourrait être légèrement enrichie en parlant des autres facteurs de risque, même s’ils sont moins significatifs que le tabagisme. Le texte est sans erreur et bien écrit.',8,1,4),(30,101,'La question est bien formulée, et la réponse correcte est facile à identifier. L\'explication est concise et explicite. L\'option \"Banane\" pourrait prêter à confusion pour certains, mais elle est bien justifiée ici. Une mention supplémentaire sur d\'autres sources courantes de vitamine C enrichirait l\'explication. Le texte est fluide et sans fautes.',8,1,4),(31,102,'La question est directe et facile à comprendre, avec des options bien choisies. L’explication sur la prévention des maladies est pertinente. Cependant, l’option \"Améliore la concentration\" pourrait prêter à confusion, donc un petit rappel sur son inexactitude serait bénéfique. Le texte est fluide, et aucune faute d\'orthographe n’est présente.',8,1,4),(32,103,'La question est très précise et bien construite, et les options permettent de bien tester les connaissances. L’explication est concise, mais l’ajout de détails sur la fonction de l’insuline renforcerait l’intérêt de la question. Rien à redire sur l\'orthographe, le texte est fluide et sans erreur.',8,1,4),(33,104,'La question est claire et bien structurée. La réponse correcte, la vitamine D, est bien mise en évidence. Il serait utile de mentionner que l’exposition au soleil doit être modérée pour éviter les risques liés aux UV. Aucun problème d\'orthographe, tout est écrit correctement.',8,1,4),(34,105,'La question est directe et claire, et les options sont bien adaptées. L’explication sur l’hypertension est correcte, mais une brève mention des autres risques du sel (comme les maladies rénales) aurait enrichi la réponse. Rien à redire sur l\'orthographe, le texte est fluide et bien rédigé.',8,1,4),(35,106,'La question est directe et claire, et les options sont bien adaptées. L’explication sur l’hypertension est correcte, mais une brève mention des autres risques du sel (comme les maladies rénales) aurait enrichi la réponse. Rien à redire sur l\'orthographe, le texte est fluide et bien rédigé.',8,1,4),(36,107,'La question est bien formulée et facile à comprendre. Les options sont adaptées, et la réponse correcte est bien expliquée. Il pourrait être utile de mentionner que le lavage des mains doit être effectué à des moments spécifiques pour être réellement efficace. Le texte est fluide, et il n’y a pas d’erreurs d’orthographe.',8,1,4),(37,108,'Cette question est claire et bien structurée. Les options sont logiques et permettent de tester les connaissances sur l\'ostéoporose. L’explication globale est pertinente. Il serait intéressant d’ajouter un rappel sur l’importance de l’exercice physique pour les os. Le texte est fluide et sans fautes.',8,1,4),(38,109,'La question est concise et bien formulée. Les options sont adaptées et la réponse correcte est évidente. Une mention des différentes fonctions du foie, en plus de la détoxification, renforcerait l\'explication. Le texte est fluide et sans erreurs d\'orthographe.',8,1,4),(39,110,'La question est claire, et les options sont bien choisies. L’explication sur la maladie d\'Alzheimer est concise, mais il serait utile d\'ajouter un petit rappel sur les symptômes pour plus de contexte. Rien à redire sur l\'orthographe, tout est bien rédigé et fluide.',8,1,4),(40,111,'La question est claire et bien formulée. L’explication sur l\'impact du tabagisme est pertinente et directe. L’option \"Exposition au froid\" peut être un peu déroutante, mais elle est bien expliquée. Le texte est fluide et sans fautes d\'orthographe.',8,1,4),(41,112,'Cette question est claire et pertinente. L’explication sur l’alimentation et le manque d\'exercice est juste. L’option \"Trop de protéines\" est bien choisie pour être éliminée. Un petit ajout sur l’importance de la gestion du poids aurait enrichi la réponse. Le texte est fluide et bien rédigé.',8,1,4);
/*!40000 ALTER TABLE `mdl_studentqcm_evaluation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_question_pop`
--

DROP TABLE IF EXISTS `mdl_question_pop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_question_pop` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nbqcm` int(10) DEFAULT NULL,
  `nbqcu` int(10) DEFAULT NULL,
  `refId` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `refId` (`refId`),
  CONSTRAINT `mdl_question_pop_ibfk_1` FOREIGN KEY (`refId`) REFERENCES `mdl_studentqcm` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_question_pop`
--

LOCK TABLES `mdl_question_pop` WRITE;
/*!40000 ALTER TABLE `mdl_question_pop` DISABLE KEYS */;
INSERT INTO `mdl_question_pop` VALUES (1,0,1,12),(2,1,0,12),(3,2,2,12),(4,2,2,12);
/*!40000 ALTER TABLE `mdl_question_pop` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_question_keywords`
--

LOCK TABLES `mdl_question_keywords` WRITE;
/*!40000 ALTER TABLE `mdl_question_keywords` DISABLE KEYS */;
INSERT INTO `mdl_question_keywords` VALUES (1,51,1),(2,51,2),(3,52,1),(4,52,2),(5,53,1),(6,53,2),(7,56,1),(8,56,2),(9,62,6),(10,62,21),(37,63,15),(38,63,16),(42,63,17),(44,63,18),(45,64,6),(46,65,17),(47,66,17),(48,67,17),(49,68,19),(50,69,13),(51,70,4),(52,71,13),(53,72,19),(54,73,7),(55,74,7),(56,75,18),(57,76,5),(58,77,5),(59,78,5),(60,79,1),(61,80,1),(62,81,1),(63,82,1),(64,83,14),(65,84,2),(68,85,13),(67,85,14),(66,85,21),(69,87,9),(70,87,22),(71,91,3),(72,91,4),(73,95,1),(74,96,16),(75,97,5),(76,98,10),(77,99,11),(78,100,4),(79,101,10),(80,102,2),(81,103,1),(82,104,9),(83,105,17),(84,106,17),(85,107,21),(86,108,1),(87,109,14),(88,110,15),(89,111,17),(90,112,10),(91,113,5),(92,114,4);
/*!40000 ALTER TABLE `mdl_question_keywords` ENABLE KEYS */;
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
  `isCustom` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_subcompetency` (`subcompetency`),
  CONSTRAINT `fk_subcompetency` FOREIGN KEY (`subcompetency`) REFERENCES `mdl_subcompetency` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_keyword`
--

LOCK TABLES `mdl_keyword` WRITE;
/*!40000 ALTER TABLE `mdl_keyword` DISABLE KEYS */;
INSERT INTO `mdl_keyword` VALUES (1,'sang',1,0),(2,'globule',1,0),(3,'mot',4,1),(4,'mot2',4,1),(5,'test',4,1),(6,'test2',4,1),(7,'test3',4,1),(8,'test4',4,1),(9,'flex',4,1),(10,'hidden',4,1),(11,'ajout',4,1),(12,'test_ajout',4,1),(13,'tessst',4,1),(14,'tesssst',4,1),(15,'test',6,1),(16,'test2',6,1),(17,'pos',6,1),(18,'pos2',6,1),(19,'pos3',6,1),(20,'pos4',6,1),(21,'tessssst',4,1),(22,'test5',4,1),(23,'test6',4,1),(24,'test7',4,1),(25,'test8',4,1);
/*!40000 ALTER TABLE `mdl_keyword` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_assignedqcm`
--

LOCK TABLES `mdl_studentqcm_assignedqcm` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_assignedqcm` DISABLE KEYS */;
INSERT INTO `mdl_studentqcm_assignedqcm` VALUES (1,2,3,4,NULL),(2,3,6,4,NULL),(3,4,6,3,NULL),(4,8,6,4,NULL);
/*!40000 ALTER TABLE `mdl_studentqcm_assignedqcm` ENABLE KEYS */;
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
  `popTypeId` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `popTypeId` (`popTypeId`),
  CONSTRAINT `mdl_studentqcm_pop_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mdl_studentqcm_pop_ibfk_2` FOREIGN KEY (`popTypeId`) REFERENCES `mdl_question_pop` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_studentqcm_pop`
--

LOCK TABLES `mdl_studentqcm_pop` WRITE;
/*!40000 ALTER TABLE `mdl_studentqcm_pop` DISABLE KEYS */;
INSERT INTO `mdl_studentqcm_pop` VALUES (1,2,1),(2,2,1),(3,2,1),(4,2,1),(5,2,1),(6,2,1),(7,2,1),(8,2,1),(9,2,1),(10,2,1),(11,2,2),(12,2,1),(13,2,1),(14,2,1),(15,2,1),(16,2,1),(17,2,1),(18,2,1),(19,2,1),(20,2,1),(21,2,1),(22,2,4),(23,2,4),(24,2,4),(25,2,4),(26,2,4),(27,6,1),(28,6,2),(29,6,3),(30,6,3),(31,6,3),(32,6,3),(33,6,4),(34,6,4),(35,6,4),(36,6,4);
/*!40000 ALTER TABLE `mdl_studentqcm_pop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_teachers`
--

DROP TABLE IF EXISTS `mdl_teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_teachers` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userId` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `mdl_teachers_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_teachers`
--

LOCK TABLES `mdl_teachers` WRITE;
/*!40000 ALTER TABLE `mdl_teachers` DISABLE KEYS */;
INSERT INTO `mdl_teachers` VALUES (1,7);
/*!40000 ALTER TABLE `mdl_teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mdl_students`
--

DROP TABLE IF EXISTS `mdl_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mdl_students` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userId` bigint(10) NOT NULL,
  `isTierTemps` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `mdl_students_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `mdl_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mdl_students`
--

LOCK TABLES `mdl_students` WRITE;
/*!40000 ALTER TABLE `mdl_students` DISABLE KEYS */;
INSERT INTO `mdl_students` VALUES (1,3,0),(2,4,0),(3,6,0),(4,8,0);
/*!40000 ALTER TABLE `mdl_students` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-26 16:20:31