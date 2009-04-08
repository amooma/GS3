-- ----------------------------------------------------------------------------
--   Gemeinschaft database
--   This file was created with
--   mysqldump --opt --skip-extended-insert --databases asterisk > asterisk.sql
--   (that's what usr/share/doc/gemeinschaft/get-database-dump.php does)
--   
--   $Revision$
-- ----------------------------------------------------------------------------


-- MySQL dump 10.11
--
-- Host: localhost    Database: asterisk
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch3-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `asterisk`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `asterisk` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE `asterisk`;

---
---  Table structure for table `agents`        
---

DROP TABLE IF EXISTS `agents`;
CREATE TABLE IF NOT EXISTS `agents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(20) collate utf8_unicode_ci NOT NULL,
  `firstname` varchar(20) collate utf8_unicode_ci NOT NULL,
  `number` varchar(25) character set ascii NOT NULL,
  `pin` varchar(10) character set ascii collate ascii_bin NOT NULL default '',
  `user_id` int(10) unsigned NOT NULL default 0,
  `paused` tinyint(1) NOT NULL default 0,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

---
---  Table structure for table `agent_queues`                        
---

DROP TABLE IF EXISTS `agent_queues`;
CREATE TABLE IF NOT EXISTS `agent_queues` (
  `agent_id` int(10) unsigned NOT NULL,
  `queue_id` int(10) unsigned NOT NULL,
  KEY `agent_id` (`agent_id`),
  KEY `queue_id` (`queue_id`),
  CONSTRAINT `agent_queues_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`),
  CONSTRAINT `agent_queues_ibfk_2` FOREIGN KEY (`queue_id`) REFERENCES `ast_queues` (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Table structure for table `area_codes`
--

DROP TABLE IF EXISTS `area_codes`;
CREATE TABLE `area_codes` (
  `cc` varchar(4) character set ascii NOT NULL,
  `ac` varchar(8) character set ascii NOT NULL,
  `is_ac` tinyint(1) unsigned NOT NULL,
  `area_name` varchar(80) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`cc`,`ac`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `area_codes`
--

LOCK TABLES `area_codes` WRITE;
/*!40000 ALTER TABLE `area_codes` DISABLE KEYS */;
INSERT INTO `area_codes` VALUES ('49','0116',0,'Harmonisierte Dienste von sozialem Wert');
INSERT INTO `area_codes` VALUES ('49','012',0,'Neuartige Dienste');
INSERT INTO `area_codes` VALUES ('49','0137',0,'Massenverkehr zu bestimmten Zielen (MaBeZ)');
INSERT INTO `area_codes` VALUES ('49','0138',0,'Massenverkehr zu bestimmten Zielen (MaBeZ)');
INSERT INTO `area_codes` VALUES ('49','0150',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0151',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0152',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0153',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0154',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0155',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0156',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0157',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0158',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0159',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0160',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0161',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0162',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0163',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0164',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0165',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0166',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0167',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0168',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0169',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0170',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0171',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0172',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0173',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0174',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0175',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0176',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0177',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0178',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','0179',0,'Mobilfunk');
INSERT INTO `area_codes` VALUES ('49','018',0,'Nutzergruppen');
INSERT INTO `area_codes` VALUES ('49','0180',0,'Geteilte-Kosten-Dienste');
INSERT INTO `area_codes` VALUES ('49','0181',0,'Internationale Virtuelle Private Netze (IVPN)');
INSERT INTO `area_codes` VALUES ('49','019',0,'Online-Dienste etc.');
INSERT INTO `area_codes` VALUES ('49','0198',0,'Routingnummern?');
INSERT INTO `area_codes` VALUES ('49','01987',0,'Routingnummern für 116xxx');
INSERT INTO `area_codes` VALUES ('49','01988',0,'Routingnummern für entgeltfreie Mehrwertdienste');
INSERT INTO `area_codes` VALUES ('49','01989',0,'Routingnummern für Auskunftsdienste');
INSERT INTO `area_codes` VALUES ('49','0199',0,'Netzinterne Verkehrslenkung');
INSERT INTO `area_codes` VALUES ('49','0201',1,'Essen');
INSERT INTO `area_codes` VALUES ('49','0202',1,'Wuppertal');
INSERT INTO `area_codes` VALUES ('49','0203',1,'Duisburg');
INSERT INTO `area_codes` VALUES ('49','02041',1,'Bottrop');
INSERT INTO `area_codes` VALUES ('49','02043',1,'Gladbeck');
INSERT INTO `area_codes` VALUES ('49','02045',1,'Bottrop-Kirchhellen');
INSERT INTO `area_codes` VALUES ('49','02051',1,'Velbert');
INSERT INTO `area_codes` VALUES ('49','02052',1,'Velbert-Langenberg');
INSERT INTO `area_codes` VALUES ('49','02053',1,'Velbert-Neviges');
INSERT INTO `area_codes` VALUES ('49','02054',1,'Essen-Kettwig');
INSERT INTO `area_codes` VALUES ('49','02056',1,'Heiligenhaus');
INSERT INTO `area_codes` VALUES ('49','02058',1,'Wülfrath');
INSERT INTO `area_codes` VALUES ('49','02064',1,'Dinslaken');
INSERT INTO `area_codes` VALUES ('49','02065',1,'Duisburg-Rheinhausen');
INSERT INTO `area_codes` VALUES ('49','02066',1,'Duisburg-Homberg');
INSERT INTO `area_codes` VALUES ('49','0208',1,'Oberhausen Rheinl');
INSERT INTO `area_codes` VALUES ('49','0209',1,'Gelsenkirchen');
INSERT INTO `area_codes` VALUES ('49','02102',1,'Ratingen');
INSERT INTO `area_codes` VALUES ('49','02103',1,'Hilden');
INSERT INTO `area_codes` VALUES ('49','02104',1,'Mettmann');
INSERT INTO `area_codes` VALUES ('49','0211',1,'Düsseldorf');
INSERT INTO `area_codes` VALUES ('49','0212',1,'Solingen');
INSERT INTO `area_codes` VALUES ('49','02129',1,'Haan Rheinl');
INSERT INTO `area_codes` VALUES ('49','02131',1,'Neuss');
INSERT INTO `area_codes` VALUES ('49','02132',1,'Meerbusch-Büderich');
INSERT INTO `area_codes` VALUES ('49','02133',1,'Dormagen');
INSERT INTO `area_codes` VALUES ('49','02137',1,'Neuss-Norf');
INSERT INTO `area_codes` VALUES ('49','0214',1,'Leverkusen');
INSERT INTO `area_codes` VALUES ('49','02150',1,'Meerbusch-Lank');
INSERT INTO `area_codes` VALUES ('49','02151',1,'Krefeld');
INSERT INTO `area_codes` VALUES ('49','02152',1,'Kempen');
INSERT INTO `area_codes` VALUES ('49','02153',1,'Nettetal-Lobberich');
INSERT INTO `area_codes` VALUES ('49','02154',1,'Willich');
INSERT INTO `area_codes` VALUES ('49','02156',1,'Willich-Anrath');
INSERT INTO `area_codes` VALUES ('49','02157',1,'Nettetal-Kaldenkirchen');
INSERT INTO `area_codes` VALUES ('49','02158',1,'Grefrath b Krefeld');
INSERT INTO `area_codes` VALUES ('49','02159',1,'Meerbusch-Osterath');
INSERT INTO `area_codes` VALUES ('49','02161',1,'Mönchengladbach');
INSERT INTO `area_codes` VALUES ('49','02162',1,'Viersen');
INSERT INTO `area_codes` VALUES ('49','02163',1,'Schwalmtal Niederrhein');
INSERT INTO `area_codes` VALUES ('49','02164',1,'Jüchen-Otzenrath');
INSERT INTO `area_codes` VALUES ('49','02165',1,'Jüchen');
INSERT INTO `area_codes` VALUES ('49','02166',1,'Mönchengladbach-Rheydt');
INSERT INTO `area_codes` VALUES ('49','02171',1,'Leverkusen-Opladen');
INSERT INTO `area_codes` VALUES ('49','02173',1,'Langenfeld Rheinland');
INSERT INTO `area_codes` VALUES ('49','02174',1,'Burscheid Rheinl');
INSERT INTO `area_codes` VALUES ('49','02175',1,'Leichlingen Rheinland');
INSERT INTO `area_codes` VALUES ('49','02181',1,'Grevenbroich');
INSERT INTO `area_codes` VALUES ('49','02182',1,'Grevenbroich-Kapellen');
INSERT INTO `area_codes` VALUES ('49','02183',1,'Rommerskirchen');
INSERT INTO `area_codes` VALUES ('49','02191',1,'Remscheid');
INSERT INTO `area_codes` VALUES ('49','02192',1,'Hückeswagen');
INSERT INTO `area_codes` VALUES ('49','02193',1,'Dabringhausen');
INSERT INTO `area_codes` VALUES ('49','02195',1,'Radevormwald');
INSERT INTO `area_codes` VALUES ('49','02196',1,'Wermelskirchen');
INSERT INTO `area_codes` VALUES ('49','02202',1,'Bergisch Gladbach');
INSERT INTO `area_codes` VALUES ('49','02203',1,'Köln-Porz');
INSERT INTO `area_codes` VALUES ('49','02204',1,'Bensberg');
INSERT INTO `area_codes` VALUES ('49','02205',1,'Rösrath');
INSERT INTO `area_codes` VALUES ('49','02206',1,'Overath');
INSERT INTO `area_codes` VALUES ('49','02207',1,'Kürten-Dürscheid');
INSERT INTO `area_codes` VALUES ('49','02208',1,'Niederkassel');
INSERT INTO `area_codes` VALUES ('49','0221',1,'Köln');
INSERT INTO `area_codes` VALUES ('49','02222',1,'Bornheim Rheinl');
INSERT INTO `area_codes` VALUES ('49','02223',1,'Königswinter');
INSERT INTO `area_codes` VALUES ('49','02224',1,'Bad Honnef');
INSERT INTO `area_codes` VALUES ('49','02225',1,'Meckenheim Rheinl');
INSERT INTO `area_codes` VALUES ('49','02226',1,'Rheinbach');
INSERT INTO `area_codes` VALUES ('49','02227',1,'Bornheim-Merten');
INSERT INTO `area_codes` VALUES ('49','02228',1,'Remagen-Rolandseck');
INSERT INTO `area_codes` VALUES ('49','02232',1,'Brühl Rheinl');
INSERT INTO `area_codes` VALUES ('49','02233',1,'Hürth Rheinl');
INSERT INTO `area_codes` VALUES ('49','02234',1,'Frechen');
INSERT INTO `area_codes` VALUES ('49','02235',1,'Erftstadt');
INSERT INTO `area_codes` VALUES ('49','02236',1,'Wesseling Rheinl');
INSERT INTO `area_codes` VALUES ('49','02237',1,'Kerpen Rheinl-Türnich');
INSERT INTO `area_codes` VALUES ('49','02238',1,'Pulheim');
INSERT INTO `area_codes` VALUES ('49','02241',1,'Siegburg');
INSERT INTO `area_codes` VALUES ('49','02242',1,'Hennef Sieg');
INSERT INTO `area_codes` VALUES ('49','02243',1,'Eitorf');
INSERT INTO `area_codes` VALUES ('49','02244',1,'Königswinter-Oberpleis');
INSERT INTO `area_codes` VALUES ('49','02245',1,'Much');
INSERT INTO `area_codes` VALUES ('49','02246',1,'Lohmar');
INSERT INTO `area_codes` VALUES ('49','02247',1,'Neunkirchen-Seelscheid');
INSERT INTO `area_codes` VALUES ('49','02248',1,'Hennef-Uckerath');
INSERT INTO `area_codes` VALUES ('49','02251',1,'Euskirchen');
INSERT INTO `area_codes` VALUES ('49','02252',1,'Zülpich');
INSERT INTO `area_codes` VALUES ('49','02253',1,'Bad Münstereifel');
INSERT INTO `area_codes` VALUES ('49','02254',1,'Weilerswist');
INSERT INTO `area_codes` VALUES ('49','02255',1,'Euskirchen-Flamersheim');
INSERT INTO `area_codes` VALUES ('49','02256',1,'Mechernich-Satzvey');
INSERT INTO `area_codes` VALUES ('49','02257',1,'Reckerscheid');
INSERT INTO `area_codes` VALUES ('49','02261',1,'Gummersbach');
INSERT INTO `area_codes` VALUES ('49','02262',1,'Wiehl');
INSERT INTO `area_codes` VALUES ('49','02263',1,'Engelskirchen');
INSERT INTO `area_codes` VALUES ('49','02264',1,'Marienheide');
INSERT INTO `area_codes` VALUES ('49','02265',1,'Reichshof-Eckenhagen');
INSERT INTO `area_codes` VALUES ('49','02266',1,'Lindlar');
INSERT INTO `area_codes` VALUES ('49','02267',1,'Wipperfürth');
INSERT INTO `area_codes` VALUES ('49','02268',1,'Kürten');
INSERT INTO `area_codes` VALUES ('49','02269',1,'Kierspe-Rönsahl');
INSERT INTO `area_codes` VALUES ('49','02271',1,'Bergheim Erft');
INSERT INTO `area_codes` VALUES ('49','02272',1,'Bedburg Erft');
INSERT INTO `area_codes` VALUES ('49','02273',1,'Kerpen-Horrem');
INSERT INTO `area_codes` VALUES ('49','02274',1,'Elsdorf Rheinl');
INSERT INTO `area_codes` VALUES ('49','02275',1,'Kerpen-Buir');
INSERT INTO `area_codes` VALUES ('49','0228',1,'Bonn');
INSERT INTO `area_codes` VALUES ('49','02291',1,'Waldbröl');
INSERT INTO `area_codes` VALUES ('49','02292',1,'Windeck Sieg');
INSERT INTO `area_codes` VALUES ('49','02293',1,'Nümbrecht');
INSERT INTO `area_codes` VALUES ('49','02294',1,'Morsbach Sieg');
INSERT INTO `area_codes` VALUES ('49','02295',1,'Ruppichteroth');
INSERT INTO `area_codes` VALUES ('49','02296',1,'Reichshof-Brüchermühle');
INSERT INTO `area_codes` VALUES ('49','02297',1,'Wildbergerhütte');
INSERT INTO `area_codes` VALUES ('49','02301',1,'Holzwickede');
INSERT INTO `area_codes` VALUES ('49','02302',1,'Witten');
INSERT INTO `area_codes` VALUES ('49','02303',1,'Unna');
INSERT INTO `area_codes` VALUES ('49','02304',1,'Schwerte');
INSERT INTO `area_codes` VALUES ('49','02305',1,'Castrop-Rauxel');
INSERT INTO `area_codes` VALUES ('49','02306',1,'Lünen');
INSERT INTO `area_codes` VALUES ('49','02307',1,'Kamen');
INSERT INTO `area_codes` VALUES ('49','02308',1,'Unna-Hemmerde');
INSERT INTO `area_codes` VALUES ('49','02309',1,'Waltrop');
INSERT INTO `area_codes` VALUES ('49','0231',1,'Dortmund');
INSERT INTO `area_codes` VALUES ('49','02323',1,'Herne');
INSERT INTO `area_codes` VALUES ('49','02324',1,'Hattingen Ruhr');
INSERT INTO `area_codes` VALUES ('49','02325',1,'Wanne-Eickel');
INSERT INTO `area_codes` VALUES ('49','02327',1,'Bochum-Wattenscheid');
INSERT INTO `area_codes` VALUES ('49','02330',1,'Herdecke');
INSERT INTO `area_codes` VALUES ('49','02331',1,'Hagen Westf');
INSERT INTO `area_codes` VALUES ('49','02332',1,'Gevelsberg');
INSERT INTO `area_codes` VALUES ('49','02333',1,'Ennepetal');
INSERT INTO `area_codes` VALUES ('49','02334',1,'Hagen-Hohenlimburg');
INSERT INTO `area_codes` VALUES ('49','02335',1,'Wetter Ruhr');
INSERT INTO `area_codes` VALUES ('49','02336',1,'Schwelm');
INSERT INTO `area_codes` VALUES ('49','02337',1,'Hagen-Dahl');
INSERT INTO `area_codes` VALUES ('49','02338',1,'Breckerfeld');
INSERT INTO `area_codes` VALUES ('49','02339',1,'Sprockhövel-Haßlinghausen');
INSERT INTO `area_codes` VALUES ('49','0234',1,'Bochum');
INSERT INTO `area_codes` VALUES ('49','02351',1,'Lüdenscheid');
INSERT INTO `area_codes` VALUES ('49','02352',1,'Altena Westf');
INSERT INTO `area_codes` VALUES ('49','02353',1,'Halver');
INSERT INTO `area_codes` VALUES ('49','02354',1,'Meinerzhagen');
INSERT INTO `area_codes` VALUES ('49','02355',1,'Schalksmühle');
INSERT INTO `area_codes` VALUES ('49','02357',1,'Herscheid Westf');
INSERT INTO `area_codes` VALUES ('49','02358',1,'Meinerzhagen-Valbert');
INSERT INTO `area_codes` VALUES ('49','02359',1,'Kierspe');
INSERT INTO `area_codes` VALUES ('49','02360',1,'Haltern-Lippramsdorf');
INSERT INTO `area_codes` VALUES ('49','02361',1,'Recklinghausen');
INSERT INTO `area_codes` VALUES ('49','02362',1,'Dorsten');
INSERT INTO `area_codes` VALUES ('49','02363',1,'Datteln');
INSERT INTO `area_codes` VALUES ('49','02364',1,'Haltern Westf');
INSERT INTO `area_codes` VALUES ('49','02365',1,'Marl');
INSERT INTO `area_codes` VALUES ('49','02366',1,'Herten Westf');
INSERT INTO `area_codes` VALUES ('49','02367',1,'Henrichenburg');
INSERT INTO `area_codes` VALUES ('49','02368',1,'Oer-Erkenschwick');
INSERT INTO `area_codes` VALUES ('49','02369',1,'Dorsten-Wulfen');
INSERT INTO `area_codes` VALUES ('49','02371',1,'Iserlohn');
INSERT INTO `area_codes` VALUES ('49','02372',1,'Hemer');
INSERT INTO `area_codes` VALUES ('49','02373',1,'Menden Sauerland');
INSERT INTO `area_codes` VALUES ('49','02374',1,'Iserlohn-Letmathe');
INSERT INTO `area_codes` VALUES ('49','02375',1,'Balve');
INSERT INTO `area_codes` VALUES ('49','02377',1,'Wickede Ruhr');
INSERT INTO `area_codes` VALUES ('49','02378',1,'Fröndenberg-Langschede');
INSERT INTO `area_codes` VALUES ('49','02379',1,'Menden-Asbeck');
INSERT INTO `area_codes` VALUES ('49','02381',1,'Hamm Westf');
INSERT INTO `area_codes` VALUES ('49','02382',1,'Ahlen Westf');
INSERT INTO `area_codes` VALUES ('49','02383',1,'Bönen');
INSERT INTO `area_codes` VALUES ('49','02384',1,'Welver');
INSERT INTO `area_codes` VALUES ('49','02385',1,'Hamm-Rhynern');
INSERT INTO `area_codes` VALUES ('49','02387',1,'Drensteinfurt-Walstedde');
INSERT INTO `area_codes` VALUES ('49','02388',1,'Hamm-Uentrop');
INSERT INTO `area_codes` VALUES ('49','02389',1,'Werne');
INSERT INTO `area_codes` VALUES ('49','02391',1,'Plettenberg');
INSERT INTO `area_codes` VALUES ('49','02392',1,'Werdohl');
INSERT INTO `area_codes` VALUES ('49','02393',1,'Sundern-Allendorf');
INSERT INTO `area_codes` VALUES ('49','02394',1,'Neuenrade-Affeln');
INSERT INTO `area_codes` VALUES ('49','02395',1,'Finnentrop-Rönkhausen');
INSERT INTO `area_codes` VALUES ('49','02401',1,'Baesweiler');
INSERT INTO `area_codes` VALUES ('49','02402',1,'Stolberg Rheinl');
INSERT INTO `area_codes` VALUES ('49','02403',1,'Eschweiler Rheinl');
INSERT INTO `area_codes` VALUES ('49','02404',1,'Alsdorf Rheinl');
INSERT INTO `area_codes` VALUES ('49','02405',1,'Würselen');
INSERT INTO `area_codes` VALUES ('49','02406',1,'Herzogenrath');
INSERT INTO `area_codes` VALUES ('49','02407',1,'Herzogenrath-Kohlscheid');
INSERT INTO `area_codes` VALUES ('49','02408',1,'Aachen-Kornelimünster');
INSERT INTO `area_codes` VALUES ('49','02409',1,'Stolberg-Gressenich');
INSERT INTO `area_codes` VALUES ('49','0241',1,'Aachen');
INSERT INTO `area_codes` VALUES ('49','02421',1,'Düren');
INSERT INTO `area_codes` VALUES ('49','02422',1,'Kreuzau');
INSERT INTO `area_codes` VALUES ('49','02423',1,'Langerwehe');
INSERT INTO `area_codes` VALUES ('49','02424',1,'Vettweiss');
INSERT INTO `area_codes` VALUES ('49','02425',1,'Nideggen-Embken');
INSERT INTO `area_codes` VALUES ('49','02426',1,'Nörvenich');
INSERT INTO `area_codes` VALUES ('49','02427',1,'Nideggen');
INSERT INTO `area_codes` VALUES ('49','02428',1,'Niederzier');
INSERT INTO `area_codes` VALUES ('49','02429',1,'Hürtgenwald');
INSERT INTO `area_codes` VALUES ('49','02431',1,'Erkelenz');
INSERT INTO `area_codes` VALUES ('49','02432',1,'Wassenberg');
INSERT INTO `area_codes` VALUES ('49','02433',1,'Hückelhoven');
INSERT INTO `area_codes` VALUES ('49','02434',1,'Wegberg');
INSERT INTO `area_codes` VALUES ('49','02435',1,'Erkelenz-Lövenich');
INSERT INTO `area_codes` VALUES ('49','02436',1,'Wegberg-Rödgen');
INSERT INTO `area_codes` VALUES ('49','02440',1,'Nettersheim-Tondorf');
INSERT INTO `area_codes` VALUES ('49','02441',1,'Kall');
INSERT INTO `area_codes` VALUES ('49','02443',1,'Mechernich');
INSERT INTO `area_codes` VALUES ('49','02444',1,'Schleiden-Gemünd');
INSERT INTO `area_codes` VALUES ('49','02445',1,'Schleiden Eifel');
INSERT INTO `area_codes` VALUES ('49','02446',1,'Heimbach Eifel');
INSERT INTO `area_codes` VALUES ('49','02447',1,'Dahlem b Kall');
INSERT INTO `area_codes` VALUES ('49','02448',1,'Hellenthal-Rescheid');
INSERT INTO `area_codes` VALUES ('49','02449',1,'Blankenheim Ahr');
INSERT INTO `area_codes` VALUES ('49','02451',1,'Geilenkirchen');
INSERT INTO `area_codes` VALUES ('49','02452',1,'Heinsberg Rheinl');
INSERT INTO `area_codes` VALUES ('49','02453',1,'Heinsberg-Randerath');
INSERT INTO `area_codes` VALUES ('49','02454',1,'Gangelt');
INSERT INTO `area_codes` VALUES ('49','02455',1,'Waldfeucht');
INSERT INTO `area_codes` VALUES ('49','02456',1,'Selfkant');
INSERT INTO `area_codes` VALUES ('49','02461',1,'Jülich');
INSERT INTO `area_codes` VALUES ('49','02462',1,'Linnich');
INSERT INTO `area_codes` VALUES ('49','02463',1,'Titz');
INSERT INTO `area_codes` VALUES ('49','02464',1,'Aldenhoven b Jülich');
INSERT INTO `area_codes` VALUES ('49','02465',1,'Inden');
INSERT INTO `area_codes` VALUES ('49','02471',1,'Roetgen Eifel');
INSERT INTO `area_codes` VALUES ('49','02472',1,'Monschau');
INSERT INTO `area_codes` VALUES ('49','02473',1,'Simmerath');
INSERT INTO `area_codes` VALUES ('49','02474',1,'Nideggen-Schmidt');
INSERT INTO `area_codes` VALUES ('49','02482',1,'Hellenthal');
INSERT INTO `area_codes` VALUES ('49','02484',1,'Mechernich-Eiserfey');
INSERT INTO `area_codes` VALUES ('49','02485',1,'Schleiden-Dreiborn');
INSERT INTO `area_codes` VALUES ('49','02486',1,'Nettersheim');
INSERT INTO `area_codes` VALUES ('49','02501',1,'Münster-Hiltrup');
INSERT INTO `area_codes` VALUES ('49','02502',1,'Nottuln');
INSERT INTO `area_codes` VALUES ('49','02504',1,'Telgte');
INSERT INTO `area_codes` VALUES ('49','02505',1,'Altenberge Westf');
INSERT INTO `area_codes` VALUES ('49','02506',1,'Münster-Wolbeck');
INSERT INTO `area_codes` VALUES ('49','02507',1,'Havixbeck');
INSERT INTO `area_codes` VALUES ('49','02508',1,'Drensteinfurt');
INSERT INTO `area_codes` VALUES ('49','02509',1,'Nottuln-Appelhülsen');
INSERT INTO `area_codes` VALUES ('49','0251',1,'Münster');
INSERT INTO `area_codes` VALUES ('49','02520',1,'Wadersloh-Diestedde');
INSERT INTO `area_codes` VALUES ('49','02521',1,'Beckum');
INSERT INTO `area_codes` VALUES ('49','02522',1,'Oelde');
INSERT INTO `area_codes` VALUES ('49','02523',1,'Wadersloh');
INSERT INTO `area_codes` VALUES ('49','02524',1,'Ennigerloh');
INSERT INTO `area_codes` VALUES ('49','02525',1,'Beckum-Neubeckum');
INSERT INTO `area_codes` VALUES ('49','02526',1,'Sendenhorst');
INSERT INTO `area_codes` VALUES ('49','02527',1,'Lippetal-Lippborg');
INSERT INTO `area_codes` VALUES ('49','02528',1,'Ennigerloh-Enniger');
INSERT INTO `area_codes` VALUES ('49','02529',1,'Oelde-Stromberg');
INSERT INTO `area_codes` VALUES ('49','02532',1,'Ostbevern');
INSERT INTO `area_codes` VALUES ('49','02533',1,'Münster-Nienberge');
INSERT INTO `area_codes` VALUES ('49','02534',1,'Münster-Roxel');
INSERT INTO `area_codes` VALUES ('49','02535',1,'Sendenhorst-Albersloh');
INSERT INTO `area_codes` VALUES ('49','02536',1,'Münster-Albachten');
INSERT INTO `area_codes` VALUES ('49','02538',1,'Drensteinfurt-Rinkerode');
INSERT INTO `area_codes` VALUES ('49','02541',1,'Coesfeld');
INSERT INTO `area_codes` VALUES ('49','02542',1,'Gescher');
INSERT INTO `area_codes` VALUES ('49','02543',1,'Billerbeck Westf');
INSERT INTO `area_codes` VALUES ('49','02545',1,'Rosendahl-Darfeld');
INSERT INTO `area_codes` VALUES ('49','02546',1,'Coesfeld-Lette');
INSERT INTO `area_codes` VALUES ('49','02547',1,'Rosendahl-Osterwick');
INSERT INTO `area_codes` VALUES ('49','02548',1,'Dülmen-Rorup');
INSERT INTO `area_codes` VALUES ('49','02551',1,'Steinfurt-Burgsteinfurt');
INSERT INTO `area_codes` VALUES ('49','02552',1,'Steinfurt-Borghorst');
INSERT INTO `area_codes` VALUES ('49','02553',1,'Ochtrup');
INSERT INTO `area_codes` VALUES ('49','02554',1,'Laer Kr Steinfurt');
INSERT INTO `area_codes` VALUES ('49','02555',1,'Schöppingen');
INSERT INTO `area_codes` VALUES ('49','02556',1,'Metelen');
INSERT INTO `area_codes` VALUES ('49','02557',1,'Wettringen Kr Steinfurt');
INSERT INTO `area_codes` VALUES ('49','02558',1,'Horstmar');
INSERT INTO `area_codes` VALUES ('49','02561',1,'Ahaus');
INSERT INTO `area_codes` VALUES ('49','02562',1,'Gronau Westfalen');
INSERT INTO `area_codes` VALUES ('49','02563',1,'Stadtlohn');
INSERT INTO `area_codes` VALUES ('49','02564',1,'Vreden');
INSERT INTO `area_codes` VALUES ('49','02565',1,'Gronau-Epe');
INSERT INTO `area_codes` VALUES ('49','02566',1,'Legden');
INSERT INTO `area_codes` VALUES ('49','02567',1,'Ahaus-Alstätte');
INSERT INTO `area_codes` VALUES ('49','02568',1,'Heek');
INSERT INTO `area_codes` VALUES ('49','02571',1,'Greven Westf');
INSERT INTO `area_codes` VALUES ('49','02572',1,'Emsdetten');
INSERT INTO `area_codes` VALUES ('49','02573',1,'Nordwalde');
INSERT INTO `area_codes` VALUES ('49','02574',1,'Saerbeck');
INSERT INTO `area_codes` VALUES ('49','02575',1,'Greven-Reckenfeld');
INSERT INTO `area_codes` VALUES ('49','02581',1,'Warendorf');
INSERT INTO `area_codes` VALUES ('49','02582',1,'Everswinkel');
INSERT INTO `area_codes` VALUES ('49','02583',1,'Sassenberg');
INSERT INTO `area_codes` VALUES ('49','02584',1,'Warendorf-Milte');
INSERT INTO `area_codes` VALUES ('49','02585',1,'Warendorf-Hoetmar');
INSERT INTO `area_codes` VALUES ('49','02586',1,'Beelen');
INSERT INTO `area_codes` VALUES ('49','02587',1,'Ennigerloh-Westkirchen');
INSERT INTO `area_codes` VALUES ('49','02588',1,'Harsewinkel-Greffen');
INSERT INTO `area_codes` VALUES ('49','02590',1,'Dülmen-Buldern');
INSERT INTO `area_codes` VALUES ('49','02591',1,'Lüdinghausen');
INSERT INTO `area_codes` VALUES ('49','02592',1,'Selm');
INSERT INTO `area_codes` VALUES ('49','02593',1,'Ascheberg Westf');
INSERT INTO `area_codes` VALUES ('49','02594',1,'Dülmen');
INSERT INTO `area_codes` VALUES ('49','02595',1,'Olfen');
INSERT INTO `area_codes` VALUES ('49','02596',1,'Nordkirchen');
INSERT INTO `area_codes` VALUES ('49','02597',1,'Senden Westf');
INSERT INTO `area_codes` VALUES ('49','02598',1,'Senden-Ottmarsbocholt');
INSERT INTO `area_codes` VALUES ('49','02599',1,'Ascheberg-Herbern');
INSERT INTO `area_codes` VALUES ('49','02601',1,'Nauort');
INSERT INTO `area_codes` VALUES ('49','02602',1,'Montabaur');
INSERT INTO `area_codes` VALUES ('49','02603',1,'Bad Ems');
INSERT INTO `area_codes` VALUES ('49','02604',1,'Nassau Lahn');
INSERT INTO `area_codes` VALUES ('49','02605',1,'Löf');
INSERT INTO `area_codes` VALUES ('49','02606',1,'Winningen Mosel');
INSERT INTO `area_codes` VALUES ('49','02607',1,'Kobern-Gondorf');
INSERT INTO `area_codes` VALUES ('49','02608',1,'Welschneudorf');
INSERT INTO `area_codes` VALUES ('49','0261',1,'Koblenz a Rhein');
INSERT INTO `area_codes` VALUES ('49','02620',1,'Neuhäusel Westerw');
INSERT INTO `area_codes` VALUES ('49','02621',1,'Lahnstein');
INSERT INTO `area_codes` VALUES ('49','02622',1,'Bendorf Rhein');
INSERT INTO `area_codes` VALUES ('49','02623',1,'Ransbach-Baumbach');
INSERT INTO `area_codes` VALUES ('49','02624',1,'Höhr-Grenzhausen');
INSERT INTO `area_codes` VALUES ('49','02625',1,'Ochtendung');
INSERT INTO `area_codes` VALUES ('49','02626',1,'Selters Westerwald');
INSERT INTO `area_codes` VALUES ('49','02627',1,'Braubach');
INSERT INTO `area_codes` VALUES ('49','02628',1,'Rhens');
INSERT INTO `area_codes` VALUES ('49','02630',1,'Mülheim-Kärlich');
INSERT INTO `area_codes` VALUES ('49','02631',1,'Neuwied');
INSERT INTO `area_codes` VALUES ('49','02632',1,'Andernach');
INSERT INTO `area_codes` VALUES ('49','02633',1,'Brohl-Lützing');
INSERT INTO `area_codes` VALUES ('49','02634',1,'Rengsdorf');
INSERT INTO `area_codes` VALUES ('49','02635',1,'Rheinbrohl');
INSERT INTO `area_codes` VALUES ('49','02636',1,'Burgbrohl');
INSERT INTO `area_codes` VALUES ('49','02637',1,'Weissenthurm');
INSERT INTO `area_codes` VALUES ('49','02638',1,'Waldbreitbach');
INSERT INTO `area_codes` VALUES ('49','02639',1,'Anhausen Kr Neuwied');
INSERT INTO `area_codes` VALUES ('49','02641',1,'Bad Neuenahr-Ahrweiler');
INSERT INTO `area_codes` VALUES ('49','02642',1,'Remagen');
INSERT INTO `area_codes` VALUES ('49','02643',1,'Altenahr');
INSERT INTO `area_codes` VALUES ('49','02644',1,'Linz am Rhein');
INSERT INTO `area_codes` VALUES ('49','02645',1,'Vettelschoss');
INSERT INTO `area_codes` VALUES ('49','02646',1,'Königsfeld Eifel');
INSERT INTO `area_codes` VALUES ('49','02647',1,'Kesseling');
INSERT INTO `area_codes` VALUES ('49','02651',1,'Mayen');
INSERT INTO `area_codes` VALUES ('49','02652',1,'Mendig');
INSERT INTO `area_codes` VALUES ('49','02653',1,'Kaisersesch');
INSERT INTO `area_codes` VALUES ('49','02654',1,'Polch');
INSERT INTO `area_codes` VALUES ('49','02655',1,'Weibern');
INSERT INTO `area_codes` VALUES ('49','02656',1,'Virneburg');
INSERT INTO `area_codes` VALUES ('49','02657',1,'Uersfeld');
INSERT INTO `area_codes` VALUES ('49','02661',1,'Bad Marienberg Westerwald');
INSERT INTO `area_codes` VALUES ('49','02662',1,'Hachenburg');
INSERT INTO `area_codes` VALUES ('49','02663',1,'Westerburg Westerw');
INSERT INTO `area_codes` VALUES ('49','02664',1,'Rennerod');
INSERT INTO `area_codes` VALUES ('49','02666',1,'Freilingen Westerw');
INSERT INTO `area_codes` VALUES ('49','02667',1,'Stein-Neukirch');
INSERT INTO `area_codes` VALUES ('49','02671',1,'Cochem');
INSERT INTO `area_codes` VALUES ('49','02672',1,'Treis-Karden');
INSERT INTO `area_codes` VALUES ('49','02673',1,'Ellenz-Poltersdorf');
INSERT INTO `area_codes` VALUES ('49','02674',1,'Bad Bertrich');
INSERT INTO `area_codes` VALUES ('49','02675',1,'Ediger-Eller');
INSERT INTO `area_codes` VALUES ('49','02676',1,'Ulmen');
INSERT INTO `area_codes` VALUES ('49','02677',1,'Lutzerath');
INSERT INTO `area_codes` VALUES ('49','02678',1,'Büchel b Cochem');
INSERT INTO `area_codes` VALUES ('49','02680',1,'Mündersbach');
INSERT INTO `area_codes` VALUES ('49','02681',1,'Altenkirchen Westerwald');
INSERT INTO `area_codes` VALUES ('49','02682',1,'Hamm Sieg');
INSERT INTO `area_codes` VALUES ('49','02683',1,'Asbach Westerw');
INSERT INTO `area_codes` VALUES ('49','02684',1,'Puderbach Westerw');
INSERT INTO `area_codes` VALUES ('49','02685',1,'Flammersfeld');
INSERT INTO `area_codes` VALUES ('49','02686',1,'Weyerbusch');
INSERT INTO `area_codes` VALUES ('49','02687',1,'Horhausen Westerwald');
INSERT INTO `area_codes` VALUES ('49','02688',1,'Kroppach');
INSERT INTO `area_codes` VALUES ('49','02689',1,'Dierdorf');
INSERT INTO `area_codes` VALUES ('49','02691',1,'Adenau');
INSERT INTO `area_codes` VALUES ('49','02692',1,'Kelberg');
INSERT INTO `area_codes` VALUES ('49','02693',1,'Antweiler');
INSERT INTO `area_codes` VALUES ('49','02694',1,'Wershofen');
INSERT INTO `area_codes` VALUES ('49','02695',1,'Insul');
INSERT INTO `area_codes` VALUES ('49','02696',1,'Nohn Eifel');
INSERT INTO `area_codes` VALUES ('49','02697',1,'Blankenheim-Ahrhütte');
INSERT INTO `area_codes` VALUES ('49','0271',1,'Siegen');
INSERT INTO `area_codes` VALUES ('49','02721',1,'Lennestadt');
INSERT INTO `area_codes` VALUES ('49','02722',1,'Attendorn');
INSERT INTO `area_codes` VALUES ('49','02723',1,'Kirchhundem');
INSERT INTO `area_codes` VALUES ('49','02724',1,'Finnentrop-Serkenrode');
INSERT INTO `area_codes` VALUES ('49','02725',1,'Lennestadt-Oedingen');
INSERT INTO `area_codes` VALUES ('49','02732',1,'Kreuztal');
INSERT INTO `area_codes` VALUES ('49','02733',1,'Hilchenbach');
INSERT INTO `area_codes` VALUES ('49','02734',1,'Freudenberg Westf');
INSERT INTO `area_codes` VALUES ('49','02735',1,'Neunkirchen Siegerl');
INSERT INTO `area_codes` VALUES ('49','02736',1,'Burbach Siegerl');
INSERT INTO `area_codes` VALUES ('49','02737',1,'Netphen-Deuz');
INSERT INTO `area_codes` VALUES ('49','02738',1,'Netphen');
INSERT INTO `area_codes` VALUES ('49','02739',1,'Wilnsdorf');
INSERT INTO `area_codes` VALUES ('49','02741',1,'Betzdorf');
INSERT INTO `area_codes` VALUES ('49','02742',1,'Wissen');
INSERT INTO `area_codes` VALUES ('49','02743',1,'Daaden');
INSERT INTO `area_codes` VALUES ('49','02744',1,'Herdorf');
INSERT INTO `area_codes` VALUES ('49','02745',1,'Brachbach Sieg');
INSERT INTO `area_codes` VALUES ('49','02747',1,'Molzhain');
INSERT INTO `area_codes` VALUES ('49','02750',1,'Diedenshausen');
INSERT INTO `area_codes` VALUES ('49','02751',1,'Bad Berleburg');
INSERT INTO `area_codes` VALUES ('49','02752',1,'Bad Laasphe');
INSERT INTO `area_codes` VALUES ('49','02753',1,'Erndtebrück');
INSERT INTO `area_codes` VALUES ('49','02754',1,'Bad Laasphe-Feudingen');
INSERT INTO `area_codes` VALUES ('49','02755',1,'Bad Berleburg-Schwarzenau');
INSERT INTO `area_codes` VALUES ('49','02758',1,'Bad Berleburg-Girkhausen');
INSERT INTO `area_codes` VALUES ('49','02759',1,'Bad Berleburg-Aue');
INSERT INTO `area_codes` VALUES ('49','02761',1,'Olpe Biggesee');
INSERT INTO `area_codes` VALUES ('49','02762',1,'Wenden Südsauerland');
INSERT INTO `area_codes` VALUES ('49','02763',1,'Drolshagen-Bleche');
INSERT INTO `area_codes` VALUES ('49','02764',1,'Welschen Ennest');
INSERT INTO `area_codes` VALUES ('49','02770',1,'Eschenburg');
INSERT INTO `area_codes` VALUES ('49','02771',1,'Dillenburg');
INSERT INTO `area_codes` VALUES ('49','02772',1,'Herborn Hess');
INSERT INTO `area_codes` VALUES ('49','02773',1,'Haiger');
INSERT INTO `area_codes` VALUES ('49','02774',1,'Dietzhölztal');
INSERT INTO `area_codes` VALUES ('49','02775',1,'Driedorf');
INSERT INTO `area_codes` VALUES ('49','02776',1,'Bad Endbach-Hartenrod');
INSERT INTO `area_codes` VALUES ('49','02777',1,'Breitscheid Hess');
INSERT INTO `area_codes` VALUES ('49','02778',1,'Siegbach');
INSERT INTO `area_codes` VALUES ('49','02779',1,'Greifenstein-Beilstein');
INSERT INTO `area_codes` VALUES ('49','02801',1,'Xanten');
INSERT INTO `area_codes` VALUES ('49','02802',1,'Alpen');
INSERT INTO `area_codes` VALUES ('49','02803',1,'Wesel-Büderich');
INSERT INTO `area_codes` VALUES ('49','02804',1,'Xanten-Marienbaum');
INSERT INTO `area_codes` VALUES ('49','0281',1,'Wesel');
INSERT INTO `area_codes` VALUES ('49','02821',1,'Kleve Niederrhein');
INSERT INTO `area_codes` VALUES ('49','02822',1,'Emmerich');
INSERT INTO `area_codes` VALUES ('49','02823',1,'Goch');
INSERT INTO `area_codes` VALUES ('49','02824',1,'Kalkar');
INSERT INTO `area_codes` VALUES ('49','02825',1,'Uedem');
INSERT INTO `area_codes` VALUES ('49','02826',1,'Kranenburg Niederrhein');
INSERT INTO `area_codes` VALUES ('49','02827',1,'Goch-Hassum');
INSERT INTO `area_codes` VALUES ('49','02828',1,'Emmerich-Elten');
INSERT INTO `area_codes` VALUES ('49','02831',1,'Geldern');
INSERT INTO `area_codes` VALUES ('49','02832',1,'Kevelaer');
INSERT INTO `area_codes` VALUES ('49','02833',1,'Kerken');
INSERT INTO `area_codes` VALUES ('49','02834',1,'Straelen');
INSERT INTO `area_codes` VALUES ('49','02835',1,'Issum');
INSERT INTO `area_codes` VALUES ('49','02836',1,'Wachtendonk');
INSERT INTO `area_codes` VALUES ('49','02837',1,'Weeze');
INSERT INTO `area_codes` VALUES ('49','02838',1,'Sonsbeck');
INSERT INTO `area_codes` VALUES ('49','02839',1,'Straelen-Herongen');
INSERT INTO `area_codes` VALUES ('49','02841',1,'Moers');
INSERT INTO `area_codes` VALUES ('49','02842',1,'Kamp-Lintfort');
INSERT INTO `area_codes` VALUES ('49','02843',1,'Rheinberg');
INSERT INTO `area_codes` VALUES ('49','02844',1,'Rheinberg-Orsoy');
INSERT INTO `area_codes` VALUES ('49','02845',1,'Neukirchen-Vluyn');
INSERT INTO `area_codes` VALUES ('49','02850',1,'Rees-Haldern');
INSERT INTO `area_codes` VALUES ('49','02851',1,'Rees');
INSERT INTO `area_codes` VALUES ('49','02852',1,'Hamminkeln');
INSERT INTO `area_codes` VALUES ('49','02853',1,'Schermbeck');
INSERT INTO `area_codes` VALUES ('49','02855',1,'Voerde Niederrhein');
INSERT INTO `area_codes` VALUES ('49','02856',1,'Hamminkeln-Brünen');
INSERT INTO `area_codes` VALUES ('49','02857',1,'Rees-Mehr');
INSERT INTO `area_codes` VALUES ('49','02858',1,'Hünxe');
INSERT INTO `area_codes` VALUES ('49','02859',1,'Wesel-Bislich');
INSERT INTO `area_codes` VALUES ('49','02861',1,'Borken Westf');
INSERT INTO `area_codes` VALUES ('49','02862',1,'Südlohn');
INSERT INTO `area_codes` VALUES ('49','02863',1,'Velen');
INSERT INTO `area_codes` VALUES ('49','02864',1,'Reken');
INSERT INTO `area_codes` VALUES ('49','02865',1,'Raesfeld');
INSERT INTO `area_codes` VALUES ('49','02866',1,'Dorsten-Rhade');
INSERT INTO `area_codes` VALUES ('49','02867',1,'Heiden Kr Borken');
INSERT INTO `area_codes` VALUES ('49','02871',1,'Bocholt');
INSERT INTO `area_codes` VALUES ('49','02872',1,'Rhede Westf');
INSERT INTO `area_codes` VALUES ('49','02873',1,'Isselburg-Werth');
INSERT INTO `area_codes` VALUES ('49','02874',1,'Isselburg');
INSERT INTO `area_codes` VALUES ('49','02902',1,'Warstein');
INSERT INTO `area_codes` VALUES ('49','02903',1,'Meschede-Freienohl');
INSERT INTO `area_codes` VALUES ('49','02904',1,'Bestwig');
INSERT INTO `area_codes` VALUES ('49','02905',1,'Bestwig-Ramsbeck');
INSERT INTO `area_codes` VALUES ('49','0291',1,'Meschede');
INSERT INTO `area_codes` VALUES ('49','02921',1,'Soest');
INSERT INTO `area_codes` VALUES ('49','02922',1,'Werl');
INSERT INTO `area_codes` VALUES ('49','02923',1,'Lippetal-Herzfeld');
INSERT INTO `area_codes` VALUES ('49','02924',1,'Möhnesee');
INSERT INTO `area_codes` VALUES ('49','02925',1,'Warstein-Allagen');
INSERT INTO `area_codes` VALUES ('49','02927',1,'Neuengeseke');
INSERT INTO `area_codes` VALUES ('49','02928',1,'Soest-Ostönnen');
INSERT INTO `area_codes` VALUES ('49','02931',1,'Arnsberg');
INSERT INTO `area_codes` VALUES ('49','02932',1,'Neheim-Hüsten');
INSERT INTO `area_codes` VALUES ('49','02933',1,'Sundern Sauerland');
INSERT INTO `area_codes` VALUES ('49','02934',1,'Sundern-Altenhellefeld');
INSERT INTO `area_codes` VALUES ('49','02935',1,'Sundern-Hachen');
INSERT INTO `area_codes` VALUES ('49','02937',1,'Arnsberg-Oeventrop');
INSERT INTO `area_codes` VALUES ('49','02938',1,'Ense');
INSERT INTO `area_codes` VALUES ('49','02941',1,'Lippstadt');
INSERT INTO `area_codes` VALUES ('49','02942',1,'Geseke');
INSERT INTO `area_codes` VALUES ('49','02943',1,'Erwitte');
INSERT INTO `area_codes` VALUES ('49','02944',1,'Rietberg-Mastholte');
INSERT INTO `area_codes` VALUES ('49','02945',1,'Lippstadt-Benninghausen');
INSERT INTO `area_codes` VALUES ('49','02947',1,'Anröchte');
INSERT INTO `area_codes` VALUES ('49','02948',1,'Lippstadt-Rebbeke');
INSERT INTO `area_codes` VALUES ('49','02951',1,'Büren');
INSERT INTO `area_codes` VALUES ('49','02952',1,'Rüthen');
INSERT INTO `area_codes` VALUES ('49','02953',1,'Wünnenberg');
INSERT INTO `area_codes` VALUES ('49','02954',1,'Rüthen-Oestereiden');
INSERT INTO `area_codes` VALUES ('49','02955',1,'Büren-Wewelsburg');
INSERT INTO `area_codes` VALUES ('49','02957',1,'Wünnenberg-Haaren');
INSERT INTO `area_codes` VALUES ('49','02958',1,'Büren-Harth');
INSERT INTO `area_codes` VALUES ('49','02961',1,'Brilon');
INSERT INTO `area_codes` VALUES ('49','02962',1,'Olsberg');
INSERT INTO `area_codes` VALUES ('49','02963',1,'Brilon-Messinghausen');
INSERT INTO `area_codes` VALUES ('49','02964',1,'Brilon-Alme');
INSERT INTO `area_codes` VALUES ('49','02971',1,'Schmallenberg-Dorlar');
INSERT INTO `area_codes` VALUES ('49','02972',1,'Schmallenberg');
INSERT INTO `area_codes` VALUES ('49','02973',1,'Eslohe Sauerland');
INSERT INTO `area_codes` VALUES ('49','02974',1,'Schmallenberg-Fredeburg');
INSERT INTO `area_codes` VALUES ('49','02975',1,'Schmallenberg-Oberkirchen');
INSERT INTO `area_codes` VALUES ('49','02977',1,'Schmallenberg-Bödefeld');
INSERT INTO `area_codes` VALUES ('49','02981',1,'Winterberg Westf');
INSERT INTO `area_codes` VALUES ('49','02982',1,'Medebach');
INSERT INTO `area_codes` VALUES ('49','02983',1,'Winterberg-Siedlinghausen');
INSERT INTO `area_codes` VALUES ('49','02984',1,'Hallenberg');
INSERT INTO `area_codes` VALUES ('49','02985',1,'Winterberg-Niedersfeld');
INSERT INTO `area_codes` VALUES ('49','02991',1,'Marsberg-Bredelar');
INSERT INTO `area_codes` VALUES ('49','02992',1,'Marsberg');
INSERT INTO `area_codes` VALUES ('49','02993',1,'Marsberg-Canstein');
INSERT INTO `area_codes` VALUES ('49','02994',1,'Marsberg-Westheim');
INSERT INTO `area_codes` VALUES ('49','030',1,'Berlin');
INSERT INTO `area_codes` VALUES ('49','031',0,'Testrufnummern');
INSERT INTO `area_codes` VALUES ('49','032',0,'Nationale Teilnehmerrufnummern');
INSERT INTO `area_codes` VALUES ('49','03301',1,'Oranienburg');
INSERT INTO `area_codes` VALUES ('49','03302',1,'Hennigsdorf');
INSERT INTO `area_codes` VALUES ('49','03303',1,'Birkenwerder');
INSERT INTO `area_codes` VALUES ('49','03304',1,'Velten');
INSERT INTO `area_codes` VALUES ('49','033051',1,'Nassenheide');
INSERT INTO `area_codes` VALUES ('49','033053',1,'Zehlendorf Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033054',1,'Liebenwalde');
INSERT INTO `area_codes` VALUES ('49','033055',1,'Kremmen');
INSERT INTO `area_codes` VALUES ('49','033056',1,'Mühlenbeck Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','03306',1,'Gransee');
INSERT INTO `area_codes` VALUES ('49','03307',1,'Zehdenick');
INSERT INTO `area_codes` VALUES ('49','033080',1,'Marienthal Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033082',1,'Menz Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033083',1,'Schulzendorf Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033084',1,'Gutengermendorf');
INSERT INTO `area_codes` VALUES ('49','033085',1,'Seilershof');
INSERT INTO `area_codes` VALUES ('49','033086',1,'Grieben Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033087',1,'Bredereiche');
INSERT INTO `area_codes` VALUES ('49','033088',1,'Falkenthal');
INSERT INTO `area_codes` VALUES ('49','033089',1,'Himmelpfort');
INSERT INTO `area_codes` VALUES ('49','033093',1,'Fürstenberg Havel');
INSERT INTO `area_codes` VALUES ('49','033094',1,'Löwenberg');
INSERT INTO `area_codes` VALUES ('49','0331',1,'Potsdam');
INSERT INTO `area_codes` VALUES ('49','033200',1,'Bergholz-Rehbrücke');
INSERT INTO `area_codes` VALUES ('49','033201',1,'Gross Glienicke');
INSERT INTO `area_codes` VALUES ('49','033202',1,'Töplitz');
INSERT INTO `area_codes` VALUES ('49','033203',1,'Kleinmachnow');
INSERT INTO `area_codes` VALUES ('49','033204',1,'Beelitz Mark');
INSERT INTO `area_codes` VALUES ('49','033205',1,'Michendorf');
INSERT INTO `area_codes` VALUES ('49','033206',1,'Fichtenwalde');
INSERT INTO `area_codes` VALUES ('49','033207',1,'Gross Kreutz');
INSERT INTO `area_codes` VALUES ('49','033208',1,'Fahrland');
INSERT INTO `area_codes` VALUES ('49','033209',1,'Caputh');
INSERT INTO `area_codes` VALUES ('49','03321',1,'Nauen Brandenb');
INSERT INTO `area_codes` VALUES ('49','03322',1,'Falkensee');
INSERT INTO `area_codes` VALUES ('49','033230',1,'Börnicke Kr Havelland');
INSERT INTO `area_codes` VALUES ('49','033231',1,'Pausin');
INSERT INTO `area_codes` VALUES ('49','033232',1,'Brieselang');
INSERT INTO `area_codes` VALUES ('49','033233',1,'Ketzin');
INSERT INTO `area_codes` VALUES ('49','033234',1,'Wustermark');
INSERT INTO `area_codes` VALUES ('49','033235',1,'Friesack');
INSERT INTO `area_codes` VALUES ('49','033237',1,'Paulinenaue');
INSERT INTO `area_codes` VALUES ('49','033238',1,'Senzke');
INSERT INTO `area_codes` VALUES ('49','033239',1,'Gross Behnitz');
INSERT INTO `area_codes` VALUES ('49','03327',1,'Werder Havel');
INSERT INTO `area_codes` VALUES ('49','03328',1,'Teltow');
INSERT INTO `area_codes` VALUES ('49','03329',1,'Stahnsdorf');
INSERT INTO `area_codes` VALUES ('49','03331',1,'Angermünde');
INSERT INTO `area_codes` VALUES ('49','03332',1,'Schwedt/Oder');
INSERT INTO `area_codes` VALUES ('49','033331',1,'Casekow');
INSERT INTO `area_codes` VALUES ('49','033332',1,'Gartz Oder');
INSERT INTO `area_codes` VALUES ('49','033333',1,'Tantow');
INSERT INTO `area_codes` VALUES ('49','033334',1,'Greiffenberg');
INSERT INTO `area_codes` VALUES ('49','033335',1,'Pinnow Kr Uckermark');
INSERT INTO `area_codes` VALUES ('49','033336',1,'Passow Kr Uckermark');
INSERT INTO `area_codes` VALUES ('49','033337',1,'Altkünkendorf');
INSERT INTO `area_codes` VALUES ('49','033338',1,'Stolpe/Oder');
INSERT INTO `area_codes` VALUES ('49','03334',1,'Eberswalde');
INSERT INTO `area_codes` VALUES ('49','03335',1,'Finowfurt');
INSERT INTO `area_codes` VALUES ('49','033361',1,'Joachimsthal');
INSERT INTO `area_codes` VALUES ('49','033362',1,'Liepe Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033363',1,'Altenhof Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033364',1,'Gross Ziethen Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033365',1,'Lüdersdorf Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033366',1,'Chorin');
INSERT INTO `area_codes` VALUES ('49','033367',1,'Friedrichswalde Brandenb');
INSERT INTO `area_codes` VALUES ('49','033368',1,'Hohensaaten');
INSERT INTO `area_codes` VALUES ('49','033369',1,'Oderberg');
INSERT INTO `area_codes` VALUES ('49','03337',1,'Biesenthal Brandenb');
INSERT INTO `area_codes` VALUES ('49','03338',1,'Bernau Brandenb');
INSERT INTO `area_codes` VALUES ('49','033393',1,'Gross Schönebeck Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033394',1,'Blumberg Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033395',1,'Zerpenschleuse');
INSERT INTO `area_codes` VALUES ('49','033396',1,'Klosterfelde');
INSERT INTO `area_codes` VALUES ('49','033397',1,'Wandlitz');
INSERT INTO `area_codes` VALUES ('49','033398',1,'Werneuchen');
INSERT INTO `area_codes` VALUES ('49','03341',1,'Strausberg');
INSERT INTO `area_codes` VALUES ('49','03342',1,'Neuenhagen b Berlin');
INSERT INTO `area_codes` VALUES ('49','033432',1,'Müncheberg');
INSERT INTO `area_codes` VALUES ('49','033433',1,'Buckow Märk Schweiz');
INSERT INTO `area_codes` VALUES ('49','033434',1,'Herzfelde b Strausberg');
INSERT INTO `area_codes` VALUES ('49','033435',1,'Rehfelde');
INSERT INTO `area_codes` VALUES ('49','033436',1,'Prötzel');
INSERT INTO `area_codes` VALUES ('49','033437',1,'Reichenberg b Strausberg');
INSERT INTO `area_codes` VALUES ('49','033438',1,'Altlandsberg');
INSERT INTO `area_codes` VALUES ('49','033439',1,'Fredersdorf-Vogelsdorf');
INSERT INTO `area_codes` VALUES ('49','03344',1,'Bad Freienwalde');
INSERT INTO `area_codes` VALUES ('49','033451',1,'Heckelberg');
INSERT INTO `area_codes` VALUES ('49','033452',1,'Neulewin');
INSERT INTO `area_codes` VALUES ('49','033454',1,'Wölsickendorf/Wollenberg');
INSERT INTO `area_codes` VALUES ('49','033456',1,'Wriezen');
INSERT INTO `area_codes` VALUES ('49','033457',1,'Altreetz');
INSERT INTO `area_codes` VALUES ('49','033458',1,'Falkenberg Mark');
INSERT INTO `area_codes` VALUES ('49','03346',1,'Seelow');
INSERT INTO `area_codes` VALUES ('49','033470',1,'Lietzen');
INSERT INTO `area_codes` VALUES ('49','033472',1,'Golzow b Seelow');
INSERT INTO `area_codes` VALUES ('49','033473',1,'Zechin');
INSERT INTO `area_codes` VALUES ('49','033474',1,'Neutrebbin');
INSERT INTO `area_codes` VALUES ('49','033475',1,'Letschin');
INSERT INTO `area_codes` VALUES ('49','033476',1,'Neuhardenberg');
INSERT INTO `area_codes` VALUES ('49','033477',1,'Trebnitz b Müncheberg');
INSERT INTO `area_codes` VALUES ('49','033478',1,'Gross Neuendorf');
INSERT INTO `area_codes` VALUES ('49','033479',1,'Küstrin-Kietz');
INSERT INTO `area_codes` VALUES ('49','0335',1,'Frankfurt (Oder)');
INSERT INTO `area_codes` VALUES ('49','033601',1,'Podelzig');
INSERT INTO `area_codes` VALUES ('49','033602',1,'Alt Zeschdorf');
INSERT INTO `area_codes` VALUES ('49','033603',1,'Falkenhagen b Seelow');
INSERT INTO `area_codes` VALUES ('49','033604',1,'Lebus');
INSERT INTO `area_codes` VALUES ('49','033605',1,'Boossen');
INSERT INTO `area_codes` VALUES ('49','033606',1,'Müllrose');
INSERT INTO `area_codes` VALUES ('49','033607',1,'Briesen Mark');
INSERT INTO `area_codes` VALUES ('49','033608',1,'Jacobsdorf Mark');
INSERT INTO `area_codes` VALUES ('49','033609',1,'Brieskow-Finkenheerd');
INSERT INTO `area_codes` VALUES ('49','03361',1,'Fürstenwalde Spree');
INSERT INTO `area_codes` VALUES ('49','03362',1,'Erkner');
INSERT INTO `area_codes` VALUES ('49','033631',1,'Bad Saarow-Pieskow');
INSERT INTO `area_codes` VALUES ('49','033632',1,'Hangelsberg');
INSERT INTO `area_codes` VALUES ('49','033633',1,'Spreenhagen');
INSERT INTO `area_codes` VALUES ('49','033634',1,'Berkenbrück Kr Oder-Spree');
INSERT INTO `area_codes` VALUES ('49','033635',1,'Arensdorf Kr Oder-Spree');
INSERT INTO `area_codes` VALUES ('49','033636',1,'Steinhöfel Kr Oder-Spree');
INSERT INTO `area_codes` VALUES ('49','033637',1,'Beerfelde');
INSERT INTO `area_codes` VALUES ('49','033638',1,'Rüdersdorf b Berlin');
INSERT INTO `area_codes` VALUES ('49','03364',1,'Eisenhüttenstadt');
INSERT INTO `area_codes` VALUES ('49','033652',1,'Neuzelle');
INSERT INTO `area_codes` VALUES ('49','033653',1,'Ziltendorf');
INSERT INTO `area_codes` VALUES ('49','033654',1,'Fünfeichen');
INSERT INTO `area_codes` VALUES ('49','033655',1,'Grunow Kr Oder-Spree');
INSERT INTO `area_codes` VALUES ('49','033656',1,'Bahro');
INSERT INTO `area_codes` VALUES ('49','033657',1,'Steinsdorf Brandenb');
INSERT INTO `area_codes` VALUES ('49','03366',1,'Beeskow');
INSERT INTO `area_codes` VALUES ('49','033671',1,'Lieberose');
INSERT INTO `area_codes` VALUES ('49','033672',1,'Pfaffendorf b Beeskow');
INSERT INTO `area_codes` VALUES ('49','033673',1,'Weichensdorf');
INSERT INTO `area_codes` VALUES ('49','033674',1,'Trebatsch');
INSERT INTO `area_codes` VALUES ('49','033675',1,'Tauche');
INSERT INTO `area_codes` VALUES ('49','033676',1,'Friedland b Beeskow');
INSERT INTO `area_codes` VALUES ('49','033677',1,'Glienicke b Beeskow');
INSERT INTO `area_codes` VALUES ('49','033678',1,'Storkow Mark');
INSERT INTO `area_codes` VALUES ('49','033679',1,'Wendisch Rietz');
INSERT INTO `area_codes` VALUES ('49','033701',1,'Grossbeeren');
INSERT INTO `area_codes` VALUES ('49','033702',1,'Wünsdorf');
INSERT INTO `area_codes` VALUES ('49','033703',1,'Sperenberg');
INSERT INTO `area_codes` VALUES ('49','033704',1,'Baruth Mark');
INSERT INTO `area_codes` VALUES ('49','033708',1,'Rangsdorf');
INSERT INTO `area_codes` VALUES ('49','03371',1,'Luckenwalde');
INSERT INTO `area_codes` VALUES ('49','03372',1,'Jüterbog');
INSERT INTO `area_codes` VALUES ('49','033731',1,'Trebbin');
INSERT INTO `area_codes` VALUES ('49','033732',1,'Hennickendorf b Luckenwalde');
INSERT INTO `area_codes` VALUES ('49','033733',1,'Stülpe');
INSERT INTO `area_codes` VALUES ('49','033734',1,'Felgentreu');
INSERT INTO `area_codes` VALUES ('49','033741',1,'Niedergörsdorf');
INSERT INTO `area_codes` VALUES ('49','033742',1,'Oehna Brandenb');
INSERT INTO `area_codes` VALUES ('49','033743',1,'Blönsdorf');
INSERT INTO `area_codes` VALUES ('49','033744',1,'Hohenseefeld');
INSERT INTO `area_codes` VALUES ('49','033745',1,'Petkus');
INSERT INTO `area_codes` VALUES ('49','033746',1,'Werbig b Jüterbog');
INSERT INTO `area_codes` VALUES ('49','033747',1,'Marzahna');
INSERT INTO `area_codes` VALUES ('49','033748',1,'Treuenbrietzen');
INSERT INTO `area_codes` VALUES ('49','03375',1,'Königs Wusterhausen');
INSERT INTO `area_codes` VALUES ('49','033760',1,'Münchehofe Kr Dahme-Spreewald');
INSERT INTO `area_codes` VALUES ('49','033762',1,'Zeuthen');
INSERT INTO `area_codes` VALUES ('49','033763',1,'Bestensee');
INSERT INTO `area_codes` VALUES ('49','033764',1,'Mittenwalde Mark');
INSERT INTO `area_codes` VALUES ('49','033765',1,'Märkisch Buchholz');
INSERT INTO `area_codes` VALUES ('49','033766',1,'Teupitz');
INSERT INTO `area_codes` VALUES ('49','033767',1,'Friedersdorf b Berlin');
INSERT INTO `area_codes` VALUES ('49','033768',1,'Prieros');
INSERT INTO `area_codes` VALUES ('49','033769',1,'Töpchin');
INSERT INTO `area_codes` VALUES ('49','03377',1,'Zossen Brandenb');
INSERT INTO `area_codes` VALUES ('49','03378',1,'Ludwigsfelde');
INSERT INTO `area_codes` VALUES ('49','03379',1,'Mahlow');
INSERT INTO `area_codes` VALUES ('49','03381',1,'Brandenburg an der Havel');
INSERT INTO `area_codes` VALUES ('49','03382',1,'Lehnin');
INSERT INTO `area_codes` VALUES ('49','033830',1,'Ziesar');
INSERT INTO `area_codes` VALUES ('49','033831',1,'Weseram');
INSERT INTO `area_codes` VALUES ('49','033832',1,'Rogäsen');
INSERT INTO `area_codes` VALUES ('49','033833',1,'Wollin b Brandenburg');
INSERT INTO `area_codes` VALUES ('49','033834',1,'Pritzerbe');
INSERT INTO `area_codes` VALUES ('49','033835',1,'Golzow b Brandenburg');
INSERT INTO `area_codes` VALUES ('49','033836',1,'Butzow b Brandenburg');
INSERT INTO `area_codes` VALUES ('49','033837',1,'Brielow');
INSERT INTO `area_codes` VALUES ('49','033838',1,'Päwesin');
INSERT INTO `area_codes` VALUES ('49','033839',1,'Wusterwitz');
INSERT INTO `area_codes` VALUES ('49','033841',1,'Belzig');
INSERT INTO `area_codes` VALUES ('49','033843',1,'Niemegk');
INSERT INTO `area_codes` VALUES ('49','033844',1,'Brück Brandenb');
INSERT INTO `area_codes` VALUES ('49','033845',1,'Borkheide');
INSERT INTO `area_codes` VALUES ('49','033846',1,'Dippmannsdorf');
INSERT INTO `area_codes` VALUES ('49','033847',1,'Görzke');
INSERT INTO `area_codes` VALUES ('49','033848',1,'Raben');
INSERT INTO `area_codes` VALUES ('49','033849',1,'Wiesenburg Mark');
INSERT INTO `area_codes` VALUES ('49','03385',1,'Rathenow');
INSERT INTO `area_codes` VALUES ('49','03386',1,'Premnitz');
INSERT INTO `area_codes` VALUES ('49','033870',1,'Zollchow b Rathenow');
INSERT INTO `area_codes` VALUES ('49','033872',1,'Hohennauen');
INSERT INTO `area_codes` VALUES ('49','033873',1,'Grosswudicke');
INSERT INTO `area_codes` VALUES ('49','033874',1,'Stechow Brandenb');
INSERT INTO `area_codes` VALUES ('49','033875',1,'Rhinow');
INSERT INTO `area_codes` VALUES ('49','033876',1,'Buschow');
INSERT INTO `area_codes` VALUES ('49','033877',1,'Nitzahn');
INSERT INTO `area_codes` VALUES ('49','033878',1,'Nennhausen');
INSERT INTO `area_codes` VALUES ('49','03391',1,'Neuruppin');
INSERT INTO `area_codes` VALUES ('49','033920',1,'Walsleben b Neuruppin');
INSERT INTO `area_codes` VALUES ('49','033921',1,'Zechlinerhütte');
INSERT INTO `area_codes` VALUES ('49','033922',1,'Karwesee');
INSERT INTO `area_codes` VALUES ('49','033923',1,'Flecken Zechlin');
INSERT INTO `area_codes` VALUES ('49','033924',1,'Rägelin');
INSERT INTO `area_codes` VALUES ('49','033925',1,'Wustrau-Altfriesack');
INSERT INTO `area_codes` VALUES ('49','033926',1,'Herzberg Mark');
INSERT INTO `area_codes` VALUES ('49','033928',1,'Wildberg Brandenb');
INSERT INTO `area_codes` VALUES ('49','033929',1,'Gühlen-Glienicke');
INSERT INTO `area_codes` VALUES ('49','033931',1,'Rheinsberg Mark');
INSERT INTO `area_codes` VALUES ('49','033932',1,'Fehrbellin');
INSERT INTO `area_codes` VALUES ('49','033933',1,'Lindow Mark');
INSERT INTO `area_codes` VALUES ('49','03394',1,'Wittstock Dosse');
INSERT INTO `area_codes` VALUES ('49','03395',1,'Pritzwalk');
INSERT INTO `area_codes` VALUES ('49','033962',1,'Heiligengrabe');
INSERT INTO `area_codes` VALUES ('49','033963',1,'Wulfersdorf b Wittstock');
INSERT INTO `area_codes` VALUES ('49','033964',1,'Fretzdorf');
INSERT INTO `area_codes` VALUES ('49','033965',1,'Herzsprung b Wittstock');
INSERT INTO `area_codes` VALUES ('49','033966',1,'Dranse');
INSERT INTO `area_codes` VALUES ('49','033967',1,'Freyenstein');
INSERT INTO `area_codes` VALUES ('49','033968',1,'Meyenburg Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033969',1,'Stepenitz');
INSERT INTO `area_codes` VALUES ('49','033970',1,'Neustadt Dosse');
INSERT INTO `area_codes` VALUES ('49','033971',1,'Kyritz Brandenb');
INSERT INTO `area_codes` VALUES ('49','033972',1,'Breddin');
INSERT INTO `area_codes` VALUES ('49','033973',1,'Zernitz b Neustadt Dosse');
INSERT INTO `area_codes` VALUES ('49','033974',1,'Dessow');
INSERT INTO `area_codes` VALUES ('49','033975',1,'Dannenwalde Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033976',1,'Wutike');
INSERT INTO `area_codes` VALUES ('49','033977',1,'Gumtow');
INSERT INTO `area_codes` VALUES ('49','033978',1,'Segeletz');
INSERT INTO `area_codes` VALUES ('49','033979',1,'Wusterhausen Dosse');
INSERT INTO `area_codes` VALUES ('49','033981',1,'Putlitz');
INSERT INTO `area_codes` VALUES ('49','033982',1,'Hoppenrade Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033983',1,'Gross Pankow Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033984',1,'Blumenthal b Pritzwalk');
INSERT INTO `area_codes` VALUES ('49','033986',1,'Falkenhagen Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033989',1,'Sadenbeck');
INSERT INTO `area_codes` VALUES ('49','0340',1,'Dessau Anh');
INSERT INTO `area_codes` VALUES ('49','0341',1,'Leipzig');
INSERT INTO `area_codes` VALUES ('49','034202',1,'Delitzsch');
INSERT INTO `area_codes` VALUES ('49','034203',1,'Zwenkau');
INSERT INTO `area_codes` VALUES ('49','034204',1,'Schkeuditz');
INSERT INTO `area_codes` VALUES ('49','034205',1,'Markranstädt');
INSERT INTO `area_codes` VALUES ('49','034206',1,'Rötha');
INSERT INTO `area_codes` VALUES ('49','034207',1,'Zwochau');
INSERT INTO `area_codes` VALUES ('49','034208',1,'Löbnitz b Delitzsch');
INSERT INTO `area_codes` VALUES ('49','03421',1,'Torgau');
INSERT INTO `area_codes` VALUES ('49','034221',1,'Schildau Gneisenaustadt');
INSERT INTO `area_codes` VALUES ('49','034222',1,'Arzberg b Torgau');
INSERT INTO `area_codes` VALUES ('49','034223',1,'Dommitzsch');
INSERT INTO `area_codes` VALUES ('49','034224',1,'Belgern Sachs');
INSERT INTO `area_codes` VALUES ('49','03423',1,'Eilenburg');
INSERT INTO `area_codes` VALUES ('49','034241',1,'Jesewitz');
INSERT INTO `area_codes` VALUES ('49','034242',1,'Hohenpriessnitz');
INSERT INTO `area_codes` VALUES ('49','034243',1,'Bad Düben');
INSERT INTO `area_codes` VALUES ('49','034244',1,'Mockrehna');
INSERT INTO `area_codes` VALUES ('49','03425',1,'Wurzen');
INSERT INTO `area_codes` VALUES ('49','034261',1,'Kühren b Wurzen');
INSERT INTO `area_codes` VALUES ('49','034262',1,'Falkenhain b Wurzen');
INSERT INTO `area_codes` VALUES ('49','034263',1,'Hohburg');
INSERT INTO `area_codes` VALUES ('49','034291',1,'Borsdorf');
INSERT INTO `area_codes` VALUES ('49','034292',1,'Brandis b Wurzen');
INSERT INTO `area_codes` VALUES ('49','034293',1,'Naunhof b Grimma');
INSERT INTO `area_codes` VALUES ('49','034294',1,'Rackwitz');
INSERT INTO `area_codes` VALUES ('49','034295',1,'Krensitz');
INSERT INTO `area_codes` VALUES ('49','034296',1,'Groitzsch b Pegau');
INSERT INTO `area_codes` VALUES ('49','034297',1,'Liebertwolkwitz');
INSERT INTO `area_codes` VALUES ('49','034298',1,'Taucha b Leipzig');
INSERT INTO `area_codes` VALUES ('49','034299',1,'Gaschwitz');
INSERT INTO `area_codes` VALUES ('49','03431',1,'Döbeln');
INSERT INTO `area_codes` VALUES ('49','034321',1,'Leisnig');
INSERT INTO `area_codes` VALUES ('49','034322',1,'Rosswein');
INSERT INTO `area_codes` VALUES ('49','034324',1,'Ostrau Sachs');
INSERT INTO `area_codes` VALUES ('49','034325',1,'Mochau-Lüttewitz');
INSERT INTO `area_codes` VALUES ('49','034327',1,'Waldheim Sachs');
INSERT INTO `area_codes` VALUES ('49','034328',1,'Hartha b Döbeln');
INSERT INTO `area_codes` VALUES ('49','03433',1,'Borna Stadt');
INSERT INTO `area_codes` VALUES ('49','034341',1,'Geithain');
INSERT INTO `area_codes` VALUES ('49','034342',1,'Neukieritzsch');
INSERT INTO `area_codes` VALUES ('49','034343',1,'Regis-Breitingen');
INSERT INTO `area_codes` VALUES ('49','034344',1,'Kohren-Sahlis');
INSERT INTO `area_codes` VALUES ('49','034345',1,'Bad Lausick');
INSERT INTO `area_codes` VALUES ('49','034346',1,'Narsdorf');
INSERT INTO `area_codes` VALUES ('49','034347',1,'Oelzschau  b Borna');
INSERT INTO `area_codes` VALUES ('49','034348',1,'Frohburg');
INSERT INTO `area_codes` VALUES ('49','03435',1,'Oschatz');
INSERT INTO `area_codes` VALUES ('49','034361',1,'Dahlen Sachs');
INSERT INTO `area_codes` VALUES ('49','034362',1,'Mügeln b Oschatz');
INSERT INTO `area_codes` VALUES ('49','034363',1,'Cavertitz');
INSERT INTO `area_codes` VALUES ('49','034364',1,'Wermsdorf');
INSERT INTO `area_codes` VALUES ('49','03437',1,'Grimma');
INSERT INTO `area_codes` VALUES ('49','034381',1,'Colditz');
INSERT INTO `area_codes` VALUES ('49','034382',1,'Nerchau');
INSERT INTO `area_codes` VALUES ('49','034383',1,'Trebsen Mulde');
INSERT INTO `area_codes` VALUES ('49','034384',1,'Grossbothen');
INSERT INTO `area_codes` VALUES ('49','034385',1,'Mutzschen');
INSERT INTO `area_codes` VALUES ('49','034386',1,'Dürrweitzschen b Grimma');
INSERT INTO `area_codes` VALUES ('49','03441',1,'Zeitz');
INSERT INTO `area_codes` VALUES ('49','034422',1,'Osterfeld');
INSERT INTO `area_codes` VALUES ('49','034423',1,'Heuckewalde');
INSERT INTO `area_codes` VALUES ('49','034424',1,'Reuden b Zeitz');
INSERT INTO `area_codes` VALUES ('49','034425',1,'Droyssig');
INSERT INTO `area_codes` VALUES ('49','034426',1,'Kayna');
INSERT INTO `area_codes` VALUES ('49','03443',1,'Weissenfels Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034441',1,'Hohenmölsen');
INSERT INTO `area_codes` VALUES ('49','034443',1,'Teuchern');
INSERT INTO `area_codes` VALUES ('49','034444',1,'Lützen');
INSERT INTO `area_codes` VALUES ('49','034445',1,'Stößen');
INSERT INTO `area_codes` VALUES ('49','034446',1,'Grosskorbetha');
INSERT INTO `area_codes` VALUES ('49','03445',1,'Naumburg Saale');
INSERT INTO `area_codes` VALUES ('49','034461',1,'Nebra Unstrut');
INSERT INTO `area_codes` VALUES ('49','034462',1,'Laucha Unstrut');
INSERT INTO `area_codes` VALUES ('49','034463',1,'Bad Kösen');
INSERT INTO `area_codes` VALUES ('49','034464',1,'Freyburg Unstrut');
INSERT INTO `area_codes` VALUES ('49','034465',1,'Bad Bibra');
INSERT INTO `area_codes` VALUES ('49','034466',1,'Janisroda');
INSERT INTO `area_codes` VALUES ('49','034467',1,'Eckartsberga');
INSERT INTO `area_codes` VALUES ('49','03447',1,'Altenburg Thür');
INSERT INTO `area_codes` VALUES ('49','03448',1,'Meuselwitz Thür');
INSERT INTO `area_codes` VALUES ('49','034491',1,'Schmölln Thür');
INSERT INTO `area_codes` VALUES ('49','034492',1,'Lucka');
INSERT INTO `area_codes` VALUES ('49','034493',1,'Gößnitz Thür');
INSERT INTO `area_codes` VALUES ('49','034494',1,'Ehrenhain');
INSERT INTO `area_codes` VALUES ('49','034495',1,'Dobitschen');
INSERT INTO `area_codes` VALUES ('49','034496',1,'Nöbdenitz');
INSERT INTO `area_codes` VALUES ('49','034497',1,'Langenleuba-Niederhain');
INSERT INTO `area_codes` VALUES ('49','034498',1,'Rositz');
INSERT INTO `area_codes` VALUES ('49','0345',1,'Halle Saale');
INSERT INTO `area_codes` VALUES ('49','034600',1,'Ostrau Saalkreis');
INSERT INTO `area_codes` VALUES ('49','034601',1,'Teutschenthal');
INSERT INTO `area_codes` VALUES ('49','034602',1,'Landsberg Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034603',1,'Nauendorf Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034604',1,'Niemberg');
INSERT INTO `area_codes` VALUES ('49','034605',1,'Gröbers');
INSERT INTO `area_codes` VALUES ('49','034606',1,'Teicha Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034607',1,'Wettin');
INSERT INTO `area_codes` VALUES ('49','034609',1,'Salzmünde');
INSERT INTO `area_codes` VALUES ('49','03461',1,'Merseburg Saale');
INSERT INTO `area_codes` VALUES ('49','03462',1,'Bad Dürrenberg');
INSERT INTO `area_codes` VALUES ('49','034632',1,'Mücheln Geiseltal');
INSERT INTO `area_codes` VALUES ('49','034633',1,'Braunsbedra');
INSERT INTO `area_codes` VALUES ('49','034635',1,'Bad Lauchstädt');
INSERT INTO `area_codes` VALUES ('49','034636',1,'Schafstädt');
INSERT INTO `area_codes` VALUES ('49','034637',1,'Frankleben');
INSERT INTO `area_codes` VALUES ('49','034638',1,'Zöschen');
INSERT INTO `area_codes` VALUES ('49','034639',1,'Wallendorf Luppe');
INSERT INTO `area_codes` VALUES ('49','03464',1,'Sangerhausen');
INSERT INTO `area_codes` VALUES ('49','034651',1,'Rossla');
INSERT INTO `area_codes` VALUES ('49','034652',1,'Allstedt');
INSERT INTO `area_codes` VALUES ('49','034653',1,'Rottleberode');
INSERT INTO `area_codes` VALUES ('49','034654',1,'Stolberg Harz');
INSERT INTO `area_codes` VALUES ('49','034656',1,'Wallhausen Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034658',1,'Hayn Harz');
INSERT INTO `area_codes` VALUES ('49','034659',1,'Blankenheim b Sangerhausen');
INSERT INTO `area_codes` VALUES ('49','03466',1,'Artern Unstrut');
INSERT INTO `area_codes` VALUES ('49','034671',1,'Bad Frankenhausen Kyffhäuser');
INSERT INTO `area_codes` VALUES ('49','034672',1,'Rossleben');
INSERT INTO `area_codes` VALUES ('49','034673',1,'Heldrungen');
INSERT INTO `area_codes` VALUES ('49','034691',1,'Könnern');
INSERT INTO `area_codes` VALUES ('49','034692',1,'Alsleben Saale');
INSERT INTO `area_codes` VALUES ('49','03471',1,'Bernburg Saale');
INSERT INTO `area_codes` VALUES ('49','034721',1,'Nienburg Saale');
INSERT INTO `area_codes` VALUES ('49','034722',1,'Preusslitz');
INSERT INTO `area_codes` VALUES ('49','03473',1,'Aschersleben Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034741',1,'Frose');
INSERT INTO `area_codes` VALUES ('49','034742',1,'Sylda');
INSERT INTO `area_codes` VALUES ('49','034743',1,'Ermsleben');
INSERT INTO `area_codes` VALUES ('49','034745',1,'Winningen Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034746',1,'Giersleben');
INSERT INTO `area_codes` VALUES ('49','03475',1,'Lutherstadt Eisleben');
INSERT INTO `area_codes` VALUES ('49','03476',1,'Hettstedt Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034771',1,'Querfurt');
INSERT INTO `area_codes` VALUES ('49','034772',1,'Helbra');
INSERT INTO `area_codes` VALUES ('49','034773',1,'Schwittersdorf');
INSERT INTO `area_codes` VALUES ('49','034774',1,'Röblingen am See');
INSERT INTO `area_codes` VALUES ('49','034775',1,'Wippra');
INSERT INTO `area_codes` VALUES ('49','034776',1,'Rothenschirmbach');
INSERT INTO `area_codes` VALUES ('49','034779',1,'Abberode');
INSERT INTO `area_codes` VALUES ('49','034781',1,'Greifenhagen');
INSERT INTO `area_codes` VALUES ('49','034782',1,'Mansfeld Südharz');
INSERT INTO `area_codes` VALUES ('49','034783',1,'Gerbstedt');
INSERT INTO `area_codes` VALUES ('49','034785',1,'Sandersleben');
INSERT INTO `area_codes` VALUES ('49','034901',1,'Roßlau Elbe');
INSERT INTO `area_codes` VALUES ('49','034903',1,'Coswig Anhalt');
INSERT INTO `area_codes` VALUES ('49','034904',1,'Oranienbaum');
INSERT INTO `area_codes` VALUES ('49','034905',1,'Wörlitz');
INSERT INTO `area_codes` VALUES ('49','034906',1,'Raguhn');
INSERT INTO `area_codes` VALUES ('49','034907',1,'Jeber-Bergfrieden');
INSERT INTO `area_codes` VALUES ('49','034909',1,'Aken Elbe');
INSERT INTO `area_codes` VALUES ('49','03491',1,'Lutherstadt Wittenberg');
INSERT INTO `area_codes` VALUES ('49','034920',1,'Kropstädt');
INSERT INTO `area_codes` VALUES ('49','034921',1,'Kemberg');
INSERT INTO `area_codes` VALUES ('49','034922',1,'Mühlanger');
INSERT INTO `area_codes` VALUES ('49','034923',1,'Cobbelsdorf');
INSERT INTO `area_codes` VALUES ('49','034924',1,'Zahna');
INSERT INTO `area_codes` VALUES ('49','034925',1,'Bad Schmiedeberg');
INSERT INTO `area_codes` VALUES ('49','034926',1,'Pretzsch Elbe');
INSERT INTO `area_codes` VALUES ('49','034927',1,'Globig-Bleddin');
INSERT INTO `area_codes` VALUES ('49','034928',1,'Seegrehna');
INSERT INTO `area_codes` VALUES ('49','034929',1,'Straach');
INSERT INTO `area_codes` VALUES ('49','03493',1,'Bitterfeld');
INSERT INTO `area_codes` VALUES ('49','03494',1,'Wolfen');
INSERT INTO `area_codes` VALUES ('49','034953',1,'Gräfenhainichen');
INSERT INTO `area_codes` VALUES ('49','034954',1,'Roitzsch b Bitterfeld');
INSERT INTO `area_codes` VALUES ('49','034955',1,'Gossa');
INSERT INTO `area_codes` VALUES ('49','034956',1,'Zörbig');
INSERT INTO `area_codes` VALUES ('49','03496',1,'Köthen Anhalt');
INSERT INTO `area_codes` VALUES ('49','034973',1,'Osternienburg');
INSERT INTO `area_codes` VALUES ('49','034975',1,'Görzig Kr Köthen');
INSERT INTO `area_codes` VALUES ('49','034976',1,'Gröbzig');
INSERT INTO `area_codes` VALUES ('49','034977',1,'Quellendorf');
INSERT INTO `area_codes` VALUES ('49','034978',1,'Radegast Kr Köthen');
INSERT INTO `area_codes` VALUES ('49','034979',1,'Wulfen Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','03501',1,'Pirna');
INSERT INTO `area_codes` VALUES ('49','035020',1,'Struppen');
INSERT INTO `area_codes` VALUES ('49','035021',1,'Königstein Sächs Schweiz');
INSERT INTO `area_codes` VALUES ('49','035022',1,'Bad Schandau');
INSERT INTO `area_codes` VALUES ('49','035023',1,'Bad Gottleuba');
INSERT INTO `area_codes` VALUES ('49','035024',1,'Stadt Wehlen');
INSERT INTO `area_codes` VALUES ('49','035025',1,'Liebstadt');
INSERT INTO `area_codes` VALUES ('49','035026',1,'Dürrröhrsdorf-Dittersbach');
INSERT INTO `area_codes` VALUES ('49','035027',1,'Weesenstein');
INSERT INTO `area_codes` VALUES ('49','035028',1,'Krippen');
INSERT INTO `area_codes` VALUES ('49','035032',1,'Langenhennersdorf');
INSERT INTO `area_codes` VALUES ('49','035033',1,'Rosenthal Sächs Schweiz');
INSERT INTO `area_codes` VALUES ('49','03504',1,'Dippoldiswalde');
INSERT INTO `area_codes` VALUES ('49','035052',1,'Kipsdorf Kurort');
INSERT INTO `area_codes` VALUES ('49','035053',1,'Glashütte Sachs');
INSERT INTO `area_codes` VALUES ('49','035054',1,'Lauenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','035055',1,'Höckendorf b Dippoldiswalde');
INSERT INTO `area_codes` VALUES ('49','035056',1,'Altenberg Sachs');
INSERT INTO `area_codes` VALUES ('49','035057',1,'Hermsdorf Erzgeb');
INSERT INTO `area_codes` VALUES ('49','035058',1,'Pretzschendorf');
INSERT INTO `area_codes` VALUES ('49','0351',1,'Dresden');
INSERT INTO `area_codes` VALUES ('49','035200',1,'Arnsdorf b Dresden');
INSERT INTO `area_codes` VALUES ('49','035201',1,'Langebrück');
INSERT INTO `area_codes` VALUES ('49','035202',1,'Klingenberg Sachs');
INSERT INTO `area_codes` VALUES ('49','035203',1,'Tharandt');
INSERT INTO `area_codes` VALUES ('49','035204',1,'Wilsdruff');
INSERT INTO `area_codes` VALUES ('49','035205',1,'Ottendorf-Okrilla');
INSERT INTO `area_codes` VALUES ('49','035206',1,'Kreischa b Dresden');
INSERT INTO `area_codes` VALUES ('49','035207',1,'Moritzburg');
INSERT INTO `area_codes` VALUES ('49','035208',1,'Radeburg');
INSERT INTO `area_codes` VALUES ('49','035209',1,'Mohorn');
INSERT INTO `area_codes` VALUES ('49','03521',1,'Meissen');
INSERT INTO `area_codes` VALUES ('49','03522',1,'Grossenhain  Sachs');
INSERT INTO `area_codes` VALUES ('49','03523',1,'Coswig  b Dresden');
INSERT INTO `area_codes` VALUES ('49','035240',1,'Tauscha b Großenhain');
INSERT INTO `area_codes` VALUES ('49','035241',1,'Lommatzsch');
INSERT INTO `area_codes` VALUES ('49','035242',1,'Nossen');
INSERT INTO `area_codes` VALUES ('49','035243',1,'Weinböhla');
INSERT INTO `area_codes` VALUES ('49','035244',1,'Krögis');
INSERT INTO `area_codes` VALUES ('49','035245',1,'Burkhardswalde-Munzig');
INSERT INTO `area_codes` VALUES ('49','035246',1,'Ziegenhain Sachs');
INSERT INTO `area_codes` VALUES ('49','035247',1,'Zehren Sachs');
INSERT INTO `area_codes` VALUES ('49','035248',1,'Schönfeld b Großenhain');
INSERT INTO `area_codes` VALUES ('49','035249',1,'Basslitz');
INSERT INTO `area_codes` VALUES ('49','03525',1,'Riesa');
INSERT INTO `area_codes` VALUES ('49','035263',1,'Gröditz b Riesa');
INSERT INTO `area_codes` VALUES ('49','035264',1,'Strehla');
INSERT INTO `area_codes` VALUES ('49','035265',1,'Glaubitz');
INSERT INTO `area_codes` VALUES ('49','035266',1,'Heyda b Riesa');
INSERT INTO `area_codes` VALUES ('49','035267',1,'Diesbar-Seusslitz');
INSERT INTO `area_codes` VALUES ('49','035268',1,'Stauchitz');
INSERT INTO `area_codes` VALUES ('49','03528',1,'Radeberg');
INSERT INTO `area_codes` VALUES ('49','03529',1,'Heidenau Sachs');
INSERT INTO `area_codes` VALUES ('49','03531',1,'Finsterwalde');
INSERT INTO `area_codes` VALUES ('49','035322',1,'Doberlug-Kirchhain');
INSERT INTO `area_codes` VALUES ('49','035323',1,'Sonnewalde');
INSERT INTO `area_codes` VALUES ('49','035324',1,'Crinitz');
INSERT INTO `area_codes` VALUES ('49','035325',1,'Rückersdorf b Finsterwalde');
INSERT INTO `area_codes` VALUES ('49','035326',1,'Schönborn Kr Elbe-Elster');
INSERT INTO `area_codes` VALUES ('49','035327',1,'Priessen');
INSERT INTO `area_codes` VALUES ('49','035329',1,'Dollenchen');
INSERT INTO `area_codes` VALUES ('49','03533',1,'Elsterwerda');
INSERT INTO `area_codes` VALUES ('49','035341',1,'Bad Liebenwerda');
INSERT INTO `area_codes` VALUES ('49','035342',1,'Mühlberg Elbe');
INSERT INTO `area_codes` VALUES ('49','035343',1,'Hirschfeld b Elsterwerda');
INSERT INTO `area_codes` VALUES ('49','03535',1,'Herzberg Elster');
INSERT INTO `area_codes` VALUES ('49','035361',1,'Schlieben');
INSERT INTO `area_codes` VALUES ('49','035362',1,'Schönewalde b Herzberg');
INSERT INTO `area_codes` VALUES ('49','035363',1,'Fermerswalde');
INSERT INTO `area_codes` VALUES ('49','035364',1,'Lebusa');
INSERT INTO `area_codes` VALUES ('49','035365',1,'Falkenberg Elster');
INSERT INTO `area_codes` VALUES ('49','03537',1,'Jessen Elster');
INSERT INTO `area_codes` VALUES ('49','035383',1,'Elster Elbe');
INSERT INTO `area_codes` VALUES ('49','035384',1,'Steinsdorf b Jessen');
INSERT INTO `area_codes` VALUES ('49','035385',1,'Annaburg');
INSERT INTO `area_codes` VALUES ('49','035386',1,'Prettin');
INSERT INTO `area_codes` VALUES ('49','035387',1,'Seyda');
INSERT INTO `area_codes` VALUES ('49','035388',1,'Klöden');
INSERT INTO `area_codes` VALUES ('49','035389',1,'Holzdorf Elster');
INSERT INTO `area_codes` VALUES ('49','03541',1,'Calau');
INSERT INTO `area_codes` VALUES ('49','03542',1,'Lübbenau Spreewald');
INSERT INTO `area_codes` VALUES ('49','035433',1,'Vetschau');
INSERT INTO `area_codes` VALUES ('49','035434',1,'Altdöbern');
INSERT INTO `area_codes` VALUES ('49','035435',1,'Gollmitz b Calau');
INSERT INTO `area_codes` VALUES ('49','035436',1,'Laasow b Calau');
INSERT INTO `area_codes` VALUES ('49','035439',1,'Zinnitz');
INSERT INTO `area_codes` VALUES ('49','03544',1,'Luckau Brandenb');
INSERT INTO `area_codes` VALUES ('49','035451',1,'Dahme Brandenb');
INSERT INTO `area_codes` VALUES ('49','035452',1,'Golssen');
INSERT INTO `area_codes` VALUES ('49','035453',1,'Drahnsdorf');
INSERT INTO `area_codes` VALUES ('49','035454',1,'Uckro');
INSERT INTO `area_codes` VALUES ('49','035455',1,'Walddrehna');
INSERT INTO `area_codes` VALUES ('49','035456',1,'Terpt');
INSERT INTO `area_codes` VALUES ('49','03546',1,'Lübben Spreewald');
INSERT INTO `area_codes` VALUES ('49','035471',1,'Birkenhainchen');
INSERT INTO `area_codes` VALUES ('49','035472',1,'Schlepzig');
INSERT INTO `area_codes` VALUES ('49','035473',1,'Neu Lübbenau');
INSERT INTO `area_codes` VALUES ('49','035474',1,'Schönwalde b Lübben');
INSERT INTO `area_codes` VALUES ('49','035475',1,'Straupitz');
INSERT INTO `area_codes` VALUES ('49','035476',1,'Wittmannsdorf-Bückchen');
INSERT INTO `area_codes` VALUES ('49','035477',1,'Rietzneuendorf-Friedrichshof');
INSERT INTO `area_codes` VALUES ('49','035478',1,'Goyatz');
INSERT INTO `area_codes` VALUES ('49','0355',1,'Cottbus');
INSERT INTO `area_codes` VALUES ('49','035600',1,'Döbern NL');
INSERT INTO `area_codes` VALUES ('49','035601',1,'Peitz');
INSERT INTO `area_codes` VALUES ('49','035602',1,'Drebkau');
INSERT INTO `area_codes` VALUES ('49','035603',1,'Burg Spreewald');
INSERT INTO `area_codes` VALUES ('49','035604',1,'Krieschow');
INSERT INTO `area_codes` VALUES ('49','035605',1,'Komptendorf');
INSERT INTO `area_codes` VALUES ('49','035606',1,'Briesen b Cottbus');
INSERT INTO `area_codes` VALUES ('49','035607',1,'Jänschwalde');
INSERT INTO `area_codes` VALUES ('49','035608',1,'Gross Ossnig');
INSERT INTO `area_codes` VALUES ('49','035609',1,'Drachhausen');
INSERT INTO `area_codes` VALUES ('49','03561',1,'Guben');
INSERT INTO `area_codes` VALUES ('49','03562',1,'Forst Lausitz');
INSERT INTO `area_codes` VALUES ('49','03563',1,'Spremberg');
INSERT INTO `area_codes` VALUES ('49','03564',1,'Schwarze Pumpe');
INSERT INTO `area_codes` VALUES ('49','035691',1,'Bärenklau NL');
INSERT INTO `area_codes` VALUES ('49','035692',1,'Kerkwitz');
INSERT INTO `area_codes` VALUES ('49','035693',1,'Lauschütz');
INSERT INTO `area_codes` VALUES ('49','035694',1,'Gosda b Klinge');
INSERT INTO `area_codes` VALUES ('49','035695',1,'Simmersdorf');
INSERT INTO `area_codes` VALUES ('49','035696',1,'Briesnig');
INSERT INTO `area_codes` VALUES ('49','035697',1,'Bagenz');
INSERT INTO `area_codes` VALUES ('49','035698',1,'Hornow');
INSERT INTO `area_codes` VALUES ('49','03571',1,'Hoyerswerda');
INSERT INTO `area_codes` VALUES ('49','035722',1,'Lauta b Hoyerswerda');
INSERT INTO `area_codes` VALUES ('49','035723',1,'Bernsdorf OL');
INSERT INTO `area_codes` VALUES ('49','035724',1,'Lohsa');
INSERT INTO `area_codes` VALUES ('49','035725',1,'Wittichenau');
INSERT INTO `area_codes` VALUES ('49','035726',1,'Groß Särchen');
INSERT INTO `area_codes` VALUES ('49','035727',1,'Burghammer');
INSERT INTO `area_codes` VALUES ('49','035728',1,'Uhyst Spree');
INSERT INTO `area_codes` VALUES ('49','03573',1,'Senftenberg');
INSERT INTO `area_codes` VALUES ('49','03574',1,'Lauchhammer');
INSERT INTO `area_codes` VALUES ('49','035751',1,'Welzow');
INSERT INTO `area_codes` VALUES ('49','035752',1,'Ruhland');
INSERT INTO `area_codes` VALUES ('49','035753',1,'Großräschen');
INSERT INTO `area_codes` VALUES ('49','035754',1,'Klettwitz');
INSERT INTO `area_codes` VALUES ('49','035755',1,'Ortrand');
INSERT INTO `area_codes` VALUES ('49','035756',1,'Hosena');
INSERT INTO `area_codes` VALUES ('49','03576',1,'Weisswasser');
INSERT INTO `area_codes` VALUES ('49','035771',1,'Bad Muskau');
INSERT INTO `area_codes` VALUES ('49','035772',1,'Rietschen');
INSERT INTO `area_codes` VALUES ('49','035773',1,'Schleife');
INSERT INTO `area_codes` VALUES ('49','035774',1,'Boxberg Sachs');
INSERT INTO `area_codes` VALUES ('49','035775',1,'Pechern');
INSERT INTO `area_codes` VALUES ('49','03578',1,'Kamenz');
INSERT INTO `area_codes` VALUES ('49','035792',1,'Ossling');
INSERT INTO `area_codes` VALUES ('49','035793',1,'Elstra');
INSERT INTO `area_codes` VALUES ('49','035795',1,'Königsbrück');
INSERT INTO `area_codes` VALUES ('49','035796',1,'Panschwitz-Kuckau');
INSERT INTO `area_codes` VALUES ('49','035797',1,'Schwepnitz');
INSERT INTO `area_codes` VALUES ('49','03581',1,'Görlitz');
INSERT INTO `area_codes` VALUES ('49','035820',1,'Zodel');
INSERT INTO `area_codes` VALUES ('49','035822',1,'Hagenwerder');
INSERT INTO `area_codes` VALUES ('49','035823',1,'Ostritz');
INSERT INTO `area_codes` VALUES ('49','035825',1,'Kodersdorf');
INSERT INTO `area_codes` VALUES ('49','035826',1,'Königshain b Görlitz');
INSERT INTO `area_codes` VALUES ('49','035827',1,'Nieder-Seifersdorf');
INSERT INTO `area_codes` VALUES ('49','035828',1,'Reichenbach OL');
INSERT INTO `area_codes` VALUES ('49','035829',1,'Gersdorf b Görlitz');
INSERT INTO `area_codes` VALUES ('49','03583',1,'Zittau');
INSERT INTO `area_codes` VALUES ('49','035841',1,'Großschönau Sachs');
INSERT INTO `area_codes` VALUES ('49','035842',1,'Oderwitz');
INSERT INTO `area_codes` VALUES ('49','035843',1,'Hirschfelde b Zittau');
INSERT INTO `area_codes` VALUES ('49','035844',1,'Oybin Kurort');
INSERT INTO `area_codes` VALUES ('49','03585',1,'Löbau');
INSERT INTO `area_codes` VALUES ('49','03586',1,'Neugersdorf ,Sachs');
INSERT INTO `area_codes` VALUES ('49','035872',1,'Neusalza-Spremberg');
INSERT INTO `area_codes` VALUES ('49','035873',1,'Herrnhut');
INSERT INTO `area_codes` VALUES ('49','035874',1,'Bernstadt a d Eigen');
INSERT INTO `area_codes` VALUES ('49','035875',1,'Obercunnersdorf b Löbau');
INSERT INTO `area_codes` VALUES ('49','035876',1,'Weissenberg Sachs');
INSERT INTO `area_codes` VALUES ('49','035877',1,'Cunewalde');
INSERT INTO `area_codes` VALUES ('49','03588',1,'Niesky');
INSERT INTO `area_codes` VALUES ('49','035891',1,'Rothenburg OL');
INSERT INTO `area_codes` VALUES ('49','035892',1,'Horka OL');
INSERT INTO `area_codes` VALUES ('49','035893',1,'Mücka');
INSERT INTO `area_codes` VALUES ('49','035894',1,'Hähnichen');
INSERT INTO `area_codes` VALUES ('49','035895',1,'Klitten');
INSERT INTO `area_codes` VALUES ('49','03591',1,'Bautzen');
INSERT INTO `area_codes` VALUES ('49','03592',1,'Kirschau');
INSERT INTO `area_codes` VALUES ('49','035930',1,'Seitschen');
INSERT INTO `area_codes` VALUES ('49','035931',1,'Königswartha');
INSERT INTO `area_codes` VALUES ('49','035932',1,'Guttau');
INSERT INTO `area_codes` VALUES ('49','035933',1,'Neschwitz');
INSERT INTO `area_codes` VALUES ('49','035934',1,'Grossdubrau');
INSERT INTO `area_codes` VALUES ('49','035935',1,'Kleinwelka');
INSERT INTO `area_codes` VALUES ('49','035936',1,'Sohland Spree');
INSERT INTO `area_codes` VALUES ('49','035937',1,'Prischwitz');
INSERT INTO `area_codes` VALUES ('49','035938',1,'Großpostwitz OL');
INSERT INTO `area_codes` VALUES ('49','035939',1,'Hochkirch');
INSERT INTO `area_codes` VALUES ('49','03594',1,'Bischofswerda');
INSERT INTO `area_codes` VALUES ('49','035951',1,'Neukirch Lausitz');
INSERT INTO `area_codes` VALUES ('49','035952',1,'Großröhrsdorf OL');
INSERT INTO `area_codes` VALUES ('49','035953',1,'Burkau');
INSERT INTO `area_codes` VALUES ('49','035954',1,'Grossharthau');
INSERT INTO `area_codes` VALUES ('49','035955',1,'Pulsnitz');
INSERT INTO `area_codes` VALUES ('49','03596',1,'Neustadt i Sa');
INSERT INTO `area_codes` VALUES ('49','035971',1,'Sebnitz');
INSERT INTO `area_codes` VALUES ('49','035973',1,'Stolpen');
INSERT INTO `area_codes` VALUES ('49','035974',1,'Hinterhermsdorf');
INSERT INTO `area_codes` VALUES ('49','035975',1,'Hohnstein');
INSERT INTO `area_codes` VALUES ('49','03601',1,'Mühlhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036020',1,'Ebeleben');
INSERT INTO `area_codes` VALUES ('49','036021',1,'Schlotheim');
INSERT INTO `area_codes` VALUES ('49','036022',1,'Grossengottern');
INSERT INTO `area_codes` VALUES ('49','036023',1,'Horsmar');
INSERT INTO `area_codes` VALUES ('49','036024',1,'Diedorf b Mühlhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036025',1,'Körner');
INSERT INTO `area_codes` VALUES ('49','036026',1,'Struth b Mühlhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036027',1,'Lengenfeld Unterm Stein');
INSERT INTO `area_codes` VALUES ('49','036028',1,'Kammerforst  Thür');
INSERT INTO `area_codes` VALUES ('49','036029',1,'Menteroda');
INSERT INTO `area_codes` VALUES ('49','03603',1,'Bad Langensalza');
INSERT INTO `area_codes` VALUES ('49','036041',1,'Bad Tennstedt');
INSERT INTO `area_codes` VALUES ('49','036042',1,'Tonna');
INSERT INTO `area_codes` VALUES ('49','036043',1,'Kirchheilingen');
INSERT INTO `area_codes` VALUES ('49','03605',1,'Leinefelde');
INSERT INTO `area_codes` VALUES ('49','03606',1,'Heiligenstadt Heilbad');
INSERT INTO `area_codes` VALUES ('49','036071',1,'Teistungen');
INSERT INTO `area_codes` VALUES ('49','036072',1,'Weißenborn-Lüderode');
INSERT INTO `area_codes` VALUES ('49','036074',1,'Worbis');
INSERT INTO `area_codes` VALUES ('49','036075',1,'Dingelstädt Eichsfeld');
INSERT INTO `area_codes` VALUES ('49','036076',1,'Niederorschel');
INSERT INTO `area_codes` VALUES ('49','036077',1,'Grossbodungen');
INSERT INTO `area_codes` VALUES ('49','036081',1,'Arenshausen');
INSERT INTO `area_codes` VALUES ('49','036082',1,'Ershausen');
INSERT INTO `area_codes` VALUES ('49','036083',1,'Uder');
INSERT INTO `area_codes` VALUES ('49','036084',1,'Heuthen');
INSERT INTO `area_codes` VALUES ('49','036085',1,'Reinholterode');
INSERT INTO `area_codes` VALUES ('49','036087',1,'Wüstheuterode');
INSERT INTO `area_codes` VALUES ('49','0361',1,'Erfurt');
INSERT INTO `area_codes` VALUES ('49','036200',1,'Elxleben b Arnstadt');
INSERT INTO `area_codes` VALUES ('49','036201',1,'Walschleben');
INSERT INTO `area_codes` VALUES ('49','036202',1,'Neudietendorf');
INSERT INTO `area_codes` VALUES ('49','036203',1,'Vieselbach');
INSERT INTO `area_codes` VALUES ('49','036204',1,'Stotternheim');
INSERT INTO `area_codes` VALUES ('49','036205',1,'Gräfenroda');
INSERT INTO `area_codes` VALUES ('49','036206',1,'Grossfahner');
INSERT INTO `area_codes` VALUES ('49','036207',1,'Plaue Thür');
INSERT INTO `area_codes` VALUES ('49','036208',1,'Ermstedt');
INSERT INTO `area_codes` VALUES ('49','036209',1,'Klettbach');
INSERT INTO `area_codes` VALUES ('49','03621',1,'Gotha Thür');
INSERT INTO `area_codes` VALUES ('49','03622',1,'Waltershausen  Thür');
INSERT INTO `area_codes` VALUES ('49','03623',1,'Friedrichroda');
INSERT INTO `area_codes` VALUES ('49','03624',1,'Ohrdruf');
INSERT INTO `area_codes` VALUES ('49','036252',1,'Tambach-Dietharz Thür Wald');
INSERT INTO `area_codes` VALUES ('49','036253',1,'Georgenthal Thür Wald');
INSERT INTO `area_codes` VALUES ('49','036254',1,'Friedrichswerth');
INSERT INTO `area_codes` VALUES ('49','036255',1,'Goldbach b Gotha');
INSERT INTO `area_codes` VALUES ('49','036256',1,'Wechmar');
INSERT INTO `area_codes` VALUES ('49','036257',1,'Luisenthal Thür');
INSERT INTO `area_codes` VALUES ('49','036258',1,'Friemar');
INSERT INTO `area_codes` VALUES ('49','036259',1,'Tabarz Thür Wald');
INSERT INTO `area_codes` VALUES ('49','03628',1,'Arnstadt');
INSERT INTO `area_codes` VALUES ('49','03629',1,'Stadtilm');
INSERT INTO `area_codes` VALUES ('49','03631',1,'Nordhausen Thür');
INSERT INTO `area_codes` VALUES ('49','03632',1,'Sondershausen');
INSERT INTO `area_codes` VALUES ('49','036330',1,'Grossberndten');
INSERT INTO `area_codes` VALUES ('49','036331',1,'Ilfeld');
INSERT INTO `area_codes` VALUES ('49','036332',1,'Ellrich');
INSERT INTO `area_codes` VALUES ('49','036333',1,'Heringen Helme');
INSERT INTO `area_codes` VALUES ('49','036334',1,'Wolkramshausen');
INSERT INTO `area_codes` VALUES ('49','036335',1,'Grosswechsungen');
INSERT INTO `area_codes` VALUES ('49','036336',1,'Klettenberg');
INSERT INTO `area_codes` VALUES ('49','036337',1,'Schiedungen');
INSERT INTO `area_codes` VALUES ('49','036338',1,'Bleicherode');
INSERT INTO `area_codes` VALUES ('49','03634',1,'Sömmerda');
INSERT INTO `area_codes` VALUES ('49','03635',1,'Kölleda');
INSERT INTO `area_codes` VALUES ('49','03636',1,'Greussen');
INSERT INTO `area_codes` VALUES ('49','036370',1,'Grossenehrich');
INSERT INTO `area_codes` VALUES ('49','036371',1,'Schlossvippach');
INSERT INTO `area_codes` VALUES ('49','036372',1,'Kleinneuhausen');
INSERT INTO `area_codes` VALUES ('49','036373',1,'Buttstädt');
INSERT INTO `area_codes` VALUES ('49','036374',1,'Weissensee');
INSERT INTO `area_codes` VALUES ('49','036375',1,'Kindelbrück');
INSERT INTO `area_codes` VALUES ('49','036376',1,'Straussfurt');
INSERT INTO `area_codes` VALUES ('49','036377',1,'Rastenberg');
INSERT INTO `area_codes` VALUES ('49','036378',1,'Ostramondra');
INSERT INTO `area_codes` VALUES ('49','036379',1,'Holzengel');
INSERT INTO `area_codes` VALUES ('49','03641',1,'Jena');
INSERT INTO `area_codes` VALUES ('49','036421',1,'Camburg');
INSERT INTO `area_codes` VALUES ('49','036422',1,'Reinstädt Thür');
INSERT INTO `area_codes` VALUES ('49','036423',1,'Orlamünde');
INSERT INTO `area_codes` VALUES ('49','036424',1,'Kahla Thür');
INSERT INTO `area_codes` VALUES ('49','036425',1,'Isserstedt');
INSERT INTO `area_codes` VALUES ('49','036426',1,'Ottendorf b Stadtroda');
INSERT INTO `area_codes` VALUES ('49','036427',1,'Dornburg Saale');
INSERT INTO `area_codes` VALUES ('49','036428',1,'Stadtroda');
INSERT INTO `area_codes` VALUES ('49','03643',1,'Weimar Thür');
INSERT INTO `area_codes` VALUES ('49','03644',1,'Apolda');
INSERT INTO `area_codes` VALUES ('49','036450',1,'Kranichfeld');
INSERT INTO `area_codes` VALUES ('49','036451',1,'Buttelstedt');
INSERT INTO `area_codes` VALUES ('49','036452',1,'Berlstedt');
INSERT INTO `area_codes` VALUES ('49','036453',1,'Mellingen');
INSERT INTO `area_codes` VALUES ('49','036454',1,'Magdala');
INSERT INTO `area_codes` VALUES ('49','036458',1,'Bad Berka');
INSERT INTO `area_codes` VALUES ('49','036459',1,'Blankenhain Thür');
INSERT INTO `area_codes` VALUES ('49','036461',1,'Bad Sulza');
INSERT INTO `area_codes` VALUES ('49','036462',1,'Ossmannstedt');
INSERT INTO `area_codes` VALUES ('49','036463',1,'Gebstedt');
INSERT INTO `area_codes` VALUES ('49','036464',1,'Wormstedt');
INSERT INTO `area_codes` VALUES ('49','036465',1,'Oberndorf b Apolda');
INSERT INTO `area_codes` VALUES ('49','03647',1,'Pößneck');
INSERT INTO `area_codes` VALUES ('49','036481',1,'Neustadt an der Orla');
INSERT INTO `area_codes` VALUES ('49','036482',1,'Triptis');
INSERT INTO `area_codes` VALUES ('49','036483',1,'Ziegenrück');
INSERT INTO `area_codes` VALUES ('49','036484',1,'Knau b Pößneck');
INSERT INTO `area_codes` VALUES ('49','0365',1,'Gera');
INSERT INTO `area_codes` VALUES ('49','036601',1,'Hermsdorf Thür');
INSERT INTO `area_codes` VALUES ('49','036602',1,'Ronneburg Thür');
INSERT INTO `area_codes` VALUES ('49','036603',1,'Weida');
INSERT INTO `area_codes` VALUES ('49','036604',1,'Münchenbernsdorf');
INSERT INTO `area_codes` VALUES ('49','036605',1,'Bad Köstritz');
INSERT INTO `area_codes` VALUES ('49','036606',1,'Kraftsdorf');
INSERT INTO `area_codes` VALUES ('49','036607',1,'Niederpöllnitz');
INSERT INTO `area_codes` VALUES ('49','036608',1,'Seelingstädt b Gera');
INSERT INTO `area_codes` VALUES ('49','03661',1,'Greiz');
INSERT INTO `area_codes` VALUES ('49','036621',1,'Elsterberg b Plauen');
INSERT INTO `area_codes` VALUES ('49','036622',1,'Triebes');
INSERT INTO `area_codes` VALUES ('49','036623',1,'Berga Elster');
INSERT INTO `area_codes` VALUES ('49','036624',1,'Teichwolframsdorf');
INSERT INTO `area_codes` VALUES ('49','036625',1,'Langenwetzendorf');
INSERT INTO `area_codes` VALUES ('49','036626',1,'Auma');
INSERT INTO `area_codes` VALUES ('49','036628',1,'Zeulenroda');
INSERT INTO `area_codes` VALUES ('49','03663',1,'Schleiz');
INSERT INTO `area_codes` VALUES ('49','036640',1,'Remptendorf');
INSERT INTO `area_codes` VALUES ('49','036642',1,'Harra');
INSERT INTO `area_codes` VALUES ('49','036643',1,'Thimmendorf');
INSERT INTO `area_codes` VALUES ('49','036644',1,'Hirschberg Saale');
INSERT INTO `area_codes` VALUES ('49','036645',1,'Mühltroff');
INSERT INTO `area_codes` VALUES ('49','036646',1,'Tanna  b Schleiz');
INSERT INTO `area_codes` VALUES ('49','036647',1,'Saalburg Thür');
INSERT INTO `area_codes` VALUES ('49','036648',1,'Dittersdorf b Schleiz');
INSERT INTO `area_codes` VALUES ('49','036649',1,'Gefell b Schleiz');
INSERT INTO `area_codes` VALUES ('49','036651',1,'Lobenstein');
INSERT INTO `area_codes` VALUES ('49','036652',1,'Wurzbach');
INSERT INTO `area_codes` VALUES ('49','036653',1,'Lehesten Thür Wald');
INSERT INTO `area_codes` VALUES ('49','036691',1,'Eisenberg Thür');
INSERT INTO `area_codes` VALUES ('49','036692',1,'Bürgel');
INSERT INTO `area_codes` VALUES ('49','036693',1,'Crossen an der Elster');
INSERT INTO `area_codes` VALUES ('49','036694',1,'Schkölen Thür');
INSERT INTO `area_codes` VALUES ('49','036695',1,'Söllmnitz');
INSERT INTO `area_codes` VALUES ('49','036701',1,'Lichte');
INSERT INTO `area_codes` VALUES ('49','036702',1,'Lauscha');
INSERT INTO `area_codes` VALUES ('49','036703',1,'Gräfenthal');
INSERT INTO `area_codes` VALUES ('49','036704',1,'Steinheid');
INSERT INTO `area_codes` VALUES ('49','036705',1,'Oberweißbach Thür Wald');
INSERT INTO `area_codes` VALUES ('49','03671',1,'Saalfeld Saale');
INSERT INTO `area_codes` VALUES ('49','03672',1,'Rudolstadt');
INSERT INTO `area_codes` VALUES ('49','036730',1,'Sitzendorf');
INSERT INTO `area_codes` VALUES ('49','036731',1,'Unterloquitz');
INSERT INTO `area_codes` VALUES ('49','036732',1,'Könitz');
INSERT INTO `area_codes` VALUES ('49','036733',1,'Kaulsdorf');
INSERT INTO `area_codes` VALUES ('49','036734',1,'Leutenberg');
INSERT INTO `area_codes` VALUES ('49','036735',1,'Probstzella');
INSERT INTO `area_codes` VALUES ('49','036736',1,'Arnsgereuth');
INSERT INTO `area_codes` VALUES ('49','036737',1,'Drognitz');
INSERT INTO `area_codes` VALUES ('49','036738',1,'Königsee');
INSERT INTO `area_codes` VALUES ('49','036739',1,'Rottenbach');
INSERT INTO `area_codes` VALUES ('49','036741',1,'Bad Blankenburg');
INSERT INTO `area_codes` VALUES ('49','036742',1,'Uhlstädt');
INSERT INTO `area_codes` VALUES ('49','036743',1,'Teichel');
INSERT INTO `area_codes` VALUES ('49','036744',1,'Remda');
INSERT INTO `area_codes` VALUES ('49','03675',1,'Sonneberg Thür');
INSERT INTO `area_codes` VALUES ('49','036761',1,'Heubisch');
INSERT INTO `area_codes` VALUES ('49','036762',1,'Steinach Thür');
INSERT INTO `area_codes` VALUES ('49','036764',1,'Neuhaus-Schierschnitz');
INSERT INTO `area_codes` VALUES ('49','036766',1,'Schalkau');
INSERT INTO `area_codes` VALUES ('49','03677',1,'Ilmenau Thür');
INSERT INTO `area_codes` VALUES ('49','036781',1,'Grossbreitenbach');
INSERT INTO `area_codes` VALUES ('49','036782',1,'Schmiedefeld a Rennsteig');
INSERT INTO `area_codes` VALUES ('49','036783',1,'Gehren Thür');
INSERT INTO `area_codes` VALUES ('49','036784',1,'Stützerbach');
INSERT INTO `area_codes` VALUES ('49','036785',1,'Gräfinau-Angstedt');
INSERT INTO `area_codes` VALUES ('49','03679',1,'Neuhaus a Rennweg');
INSERT INTO `area_codes` VALUES ('49','03681',1,'Suhl');
INSERT INTO `area_codes` VALUES ('49','03682',1,'Zella-Mehlis');
INSERT INTO `area_codes` VALUES ('49','03683',1,'Schmalkalden');
INSERT INTO `area_codes` VALUES ('49','036840',1,'Trusetal');
INSERT INTO `area_codes` VALUES ('49','036841',1,'Schleusingen');
INSERT INTO `area_codes` VALUES ('49','036842',1,'Oberhof Thür');
INSERT INTO `area_codes` VALUES ('49','036843',1,'Benshausen');
INSERT INTO `area_codes` VALUES ('49','036844',1,'Rohr Thür');
INSERT INTO `area_codes` VALUES ('49','036845',1,'Gehlberg');
INSERT INTO `area_codes` VALUES ('49','036846',1,'Suhl-Dietzhausen');
INSERT INTO `area_codes` VALUES ('49','036847',1,'Steinbach-Hallenberg');
INSERT INTO `area_codes` VALUES ('49','036848',1,'Wernshausen');
INSERT INTO `area_codes` VALUES ('49','036849',1,'Kleinschmalkalden');
INSERT INTO `area_codes` VALUES ('49','03685',1,'Hildburghausen');
INSERT INTO `area_codes` VALUES ('49','03686',1,'Eisfeld');
INSERT INTO `area_codes` VALUES ('49','036870',1,'Masserberg');
INSERT INTO `area_codes` VALUES ('49','036871',1,'Bad Colberg-Heldburg');
INSERT INTO `area_codes` VALUES ('49','036873',1,'Themar');
INSERT INTO `area_codes` VALUES ('49','036874',1,'Schönbrunn b Hildburghaus');
INSERT INTO `area_codes` VALUES ('49','036875',1,'Straufhain-Streufdorf');
INSERT INTO `area_codes` VALUES ('49','036878',1,'Oberland');
INSERT INTO `area_codes` VALUES ('49','03691',1,'Eisenach Thür');
INSERT INTO `area_codes` VALUES ('49','036920',1,'Grossenlupnitz');
INSERT INTO `area_codes` VALUES ('49','036921',1,'Wutha-Farnroda');
INSERT INTO `area_codes` VALUES ('49','036922',1,'Gerstungen');
INSERT INTO `area_codes` VALUES ('49','036923',1,'Treffurt');
INSERT INTO `area_codes` VALUES ('49','036924',1,'Mihla');
INSERT INTO `area_codes` VALUES ('49','036925',1,'Marksuhl');
INSERT INTO `area_codes` VALUES ('49','036926',1,'Creuzburg');
INSERT INTO `area_codes` VALUES ('49','036927',1,'Unterellen');
INSERT INTO `area_codes` VALUES ('49','036928',1,'Neuenhof  Thür');
INSERT INTO `area_codes` VALUES ('49','036929',1,'Ruhla');
INSERT INTO `area_codes` VALUES ('49','03693',1,'Meiningen');
INSERT INTO `area_codes` VALUES ('49','036940',1,'Oepfershausen');
INSERT INTO `area_codes` VALUES ('49','036941',1,'Wasungen');
INSERT INTO `area_codes` VALUES ('49','036943',1,'Bettenhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036944',1,'Rentwertshausen');
INSERT INTO `area_codes` VALUES ('49','036945',1,'Henneberg');
INSERT INTO `area_codes` VALUES ('49','036946',1,'Erbenhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036947',1,'Jüchsen');
INSERT INTO `area_codes` VALUES ('49','036948',1,'Römhild');
INSERT INTO `area_codes` VALUES ('49','036949',1,'Obermaßfeld-Grimmenthal');
INSERT INTO `area_codes` VALUES ('49','03695',1,'Bad Salzungen');
INSERT INTO `area_codes` VALUES ('49','036961',1,'Bad Liebenstein');
INSERT INTO `area_codes` VALUES ('49','036962',1,'Vacha');
INSERT INTO `area_codes` VALUES ('49','036963',1,'Dorndorf Rhön');
INSERT INTO `area_codes` VALUES ('49','036964',1,'Dermbach Rhön');
INSERT INTO `area_codes` VALUES ('49','036965',1,'Stadtlengsfeld');
INSERT INTO `area_codes` VALUES ('49','036966',1,'Kaltennordheim');
INSERT INTO `area_codes` VALUES ('49','036967',1,'Geisa');
INSERT INTO `area_codes` VALUES ('49','036968',1,'Rossdorf Rhön');
INSERT INTO `area_codes` VALUES ('49','036969',1,'Merkers');
INSERT INTO `area_codes` VALUES ('49','0371',1,'Chemnitz Sachs');
INSERT INTO `area_codes` VALUES ('49','037200',1,'Wittgensdorf b Chemnitz');
INSERT INTO `area_codes` VALUES ('49','037202',1,'Claussnitz b Chemnitz');
INSERT INTO `area_codes` VALUES ('49','037203',1,'Gersdorf b Chemnitz');
INSERT INTO `area_codes` VALUES ('49','037204',1,'Lichtenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','037206',1,'Frankenberg');
INSERT INTO `area_codes` VALUES ('49','037207',1,'Hainichen');
INSERT INTO `area_codes` VALUES ('49','037208',1,'Auerswalde');
INSERT INTO `area_codes` VALUES ('49','037209',1,'Einsiedel b Chemnitz');
INSERT INTO `area_codes` VALUES ('49','03721',1,'Meinersdorf');
INSERT INTO `area_codes` VALUES ('49','03722',1,'Limbach-Oberfrohna');
INSERT INTO `area_codes` VALUES ('49','03723',1,'Hohenstein-Ernstthal');
INSERT INTO `area_codes` VALUES ('49','03724',1,'Burgstädt');
INSERT INTO `area_codes` VALUES ('49','03725',1,'Zschopau');
INSERT INTO `area_codes` VALUES ('49','03726',1,'Flöha');
INSERT INTO `area_codes` VALUES ('49','03727',1,'Mittweida');
INSERT INTO `area_codes` VALUES ('49','037291',1,'Augustusburg');
INSERT INTO `area_codes` VALUES ('49','037292',1,'Oederan');
INSERT INTO `area_codes` VALUES ('49','037293',1,'Eppendorf Sachs');
INSERT INTO `area_codes` VALUES ('49','037294',1,'Grünhainichen');
INSERT INTO `area_codes` VALUES ('49','037295',1,'Lugau Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037296',1,'Stollberg Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037297',1,'Thum Sachs');
INSERT INTO `area_codes` VALUES ('49','037298',1,'Oelsnitz Erzgeb');
INSERT INTO `area_codes` VALUES ('49','03731',1,'Freiberg Sachs');
INSERT INTO `area_codes` VALUES ('49','037320',1,'Mulda Sachs');
INSERT INTO `area_codes` VALUES ('49','037321',1,'Frankenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','037322',1,'Brand-Erbisdorf');
INSERT INTO `area_codes` VALUES ('49','037323',1,'Lichtenberg Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037324',1,'Reinsberg Sachs');
INSERT INTO `area_codes` VALUES ('49','037325',1,'Niederbobritzsch');
INSERT INTO `area_codes` VALUES ('49','037326',1,'Frauenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','037327',1,'Rechenberg-Bienenmühle');
INSERT INTO `area_codes` VALUES ('49','037328',1,'Grossschirma');
INSERT INTO `area_codes` VALUES ('49','037329',1,'Grosshartmannsdorf');
INSERT INTO `area_codes` VALUES ('49','03733',1,'Annaberg-Buchholz');
INSERT INTO `area_codes` VALUES ('49','037341',1,'Ehrenfriedersdorf');
INSERT INTO `area_codes` VALUES ('49','037342',1,'Cranzahl');
INSERT INTO `area_codes` VALUES ('49','037343',1,'Jöhstadt');
INSERT INTO `area_codes` VALUES ('49','037344',1,'Crottendorf Sachs');
INSERT INTO `area_codes` VALUES ('49','037346',1,'Geyer');
INSERT INTO `area_codes` VALUES ('49','037347',1,'Bärenstein Kr Annaberg');
INSERT INTO `area_codes` VALUES ('49','037348',1,'Oberwiesenthal Kurort');
INSERT INTO `area_codes` VALUES ('49','037349',1,'Scheibenberg');
INSERT INTO `area_codes` VALUES ('49','03735',1,'Marienberg Sachs');
INSERT INTO `area_codes` VALUES ('49','037360',1,'Olbernhau');
INSERT INTO `area_codes` VALUES ('49','037361',1,'Neuhausen Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037362',1,'Seiffen Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037363',1,'Zöblitz');
INSERT INTO `area_codes` VALUES ('49','037364',1,'Reitzenhain Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037365',1,'Sayda');
INSERT INTO `area_codes` VALUES ('49','037366',1,'Rübenau');
INSERT INTO `area_codes` VALUES ('49','037367',1,'Lengefeld Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037368',1,'Deutschneudorf');
INSERT INTO `area_codes` VALUES ('49','037369',1,'Wolkenstein');
INSERT INTO `area_codes` VALUES ('49','03737',1,'Rochlitz');
INSERT INTO `area_codes` VALUES ('49','037381',1,'Penig');
INSERT INTO `area_codes` VALUES ('49','037382',1,'Geringswalde');
INSERT INTO `area_codes` VALUES ('49','037383',1,'Lunzenau');
INSERT INTO `area_codes` VALUES ('49','037384',1,'Wechselburg');
INSERT INTO `area_codes` VALUES ('49','03741',1,'Plauen');
INSERT INTO `area_codes` VALUES ('49','037421',1,'Oelsnitz Vogtl');
INSERT INTO `area_codes` VALUES ('49','037422',1,'Markneukirchen');
INSERT INTO `area_codes` VALUES ('49','037423',1,'Adorf Vogtl');
INSERT INTO `area_codes` VALUES ('49','037430',1,'Eichigt');
INSERT INTO `area_codes` VALUES ('49','037431',1,'Mehltheuer Vogtl');
INSERT INTO `area_codes` VALUES ('49','037432',1,'Pausa Vogtl');
INSERT INTO `area_codes` VALUES ('49','037433',1,'Gutenfürst');
INSERT INTO `area_codes` VALUES ('49','037434',1,'Bobenneukirchen');
INSERT INTO `area_codes` VALUES ('49','037435',1,'Reuth b Plauen');
INSERT INTO `area_codes` VALUES ('49','037436',1,'Weischlitz');
INSERT INTO `area_codes` VALUES ('49','037437',1,'Bad Elster');
INSERT INTO `area_codes` VALUES ('49','037438',1,'Bad Brambach');
INSERT INTO `area_codes` VALUES ('49','037439',1,'Jocketa');
INSERT INTO `area_codes` VALUES ('49','03744',1,'Auerbach Vogtl.');
INSERT INTO `area_codes` VALUES ('49','03745',1,'Falkenstein Vogtl');
INSERT INTO `area_codes` VALUES ('49','037462',1,'Rothenkirchen Vogtl');
INSERT INTO `area_codes` VALUES ('49','037463',1,'Bergen Vogtl');
INSERT INTO `area_codes` VALUES ('49','037464',1,'Schöneck Vogtl');
INSERT INTO `area_codes` VALUES ('49','037465',1,'Tannenbergsthal Vogtl');
INSERT INTO `area_codes` VALUES ('49','037467',1,'Klingenthal Sachs');
INSERT INTO `area_codes` VALUES ('49','037468',1,'Treuen Vogtl');
INSERT INTO `area_codes` VALUES ('49','0375',1,'Zwickau');
INSERT INTO `area_codes` VALUES ('49','037600',1,'Neumark Sachs');
INSERT INTO `area_codes` VALUES ('49','037601',1,'Mülsen Skt Jacob');
INSERT INTO `area_codes` VALUES ('49','037602',1,'Kirchberg Sachs');
INSERT INTO `area_codes` VALUES ('49','037603',1,'Wildenfels');
INSERT INTO `area_codes` VALUES ('49','037604',1,'Mosel');
INSERT INTO `area_codes` VALUES ('49','037605',1,'Hartenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','037606',1,'Lengenfeld Vogtl');
INSERT INTO `area_codes` VALUES ('49','037607',1,'Ebersbrunn Sachs');
INSERT INTO `area_codes` VALUES ('49','037608',1,'Waldenburg Sachs');
INSERT INTO `area_codes` VALUES ('49','037609',1,'Wolkenburg Mulde');
INSERT INTO `area_codes` VALUES ('49','03761',1,'Werdau Sachs');
INSERT INTO `area_codes` VALUES ('49','03762',1,'Crimmitschau');
INSERT INTO `area_codes` VALUES ('49','03763',1,'Glauchau');
INSERT INTO `area_codes` VALUES ('49','03764',1,'Meerane');
INSERT INTO `area_codes` VALUES ('49','03765',1,'Reichenbach Vogtl');
INSERT INTO `area_codes` VALUES ('49','03771',1,'Aue Sachs');
INSERT INTO `area_codes` VALUES ('49','03772',1,'Schneeberg Erzgeb');
INSERT INTO `area_codes` VALUES ('49','03773',1,'Johanngeorgenstadt');
INSERT INTO `area_codes` VALUES ('49','03774',1,'Schwarzenberg');
INSERT INTO `area_codes` VALUES ('49','037752',1,'Eibenstock');
INSERT INTO `area_codes` VALUES ('49','037754',1,'Zwönitz');
INSERT INTO `area_codes` VALUES ('49','037755',1,'Schönheide Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037756',1,'Breitenbrunn Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037757',1,'Rittersgrün');
INSERT INTO `area_codes` VALUES ('49','0381',1,'Rostock');
INSERT INTO `area_codes` VALUES ('49','038201',1,'Gelbensande');
INSERT INTO `area_codes` VALUES ('49','038202',1,'Volkenshagen');
INSERT INTO `area_codes` VALUES ('49','038203',1,'Bad Doberan');
INSERT INTO `area_codes` VALUES ('49','038204',1,'Broderstorf');
INSERT INTO `area_codes` VALUES ('49','038205',1,'Tessin b Rostock');
INSERT INTO `area_codes` VALUES ('49','038206',1,'Graal-Müritz Seeheilbad');
INSERT INTO `area_codes` VALUES ('49','038207',1,'Stäbelow');
INSERT INTO `area_codes` VALUES ('49','038208',1,'Kavelstorf');
INSERT INTO `area_codes` VALUES ('49','038209',1,'Sanitz b Rostock');
INSERT INTO `area_codes` VALUES ('49','03821',1,'Ribnitz-Damgarten');
INSERT INTO `area_codes` VALUES ('49','038220',1,'Wustrow Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038221',1,'Marlow');
INSERT INTO `area_codes` VALUES ('49','038222',1,'Semlow');
INSERT INTO `area_codes` VALUES ('49','038223',1,'Saal Vorpom');
INSERT INTO `area_codes` VALUES ('49','038224',1,'Gresenhorst');
INSERT INTO `area_codes` VALUES ('49','038225',1,'Trinwillershagen');
INSERT INTO `area_codes` VALUES ('49','038226',1,'Dierhagen Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038227',1,'Lüdershagen b Barth');
INSERT INTO `area_codes` VALUES ('49','038228',1,'Dettmannsdorf-Kölzow');
INSERT INTO `area_codes` VALUES ('49','038229',1,'Bad Sülze');
INSERT INTO `area_codes` VALUES ('49','038231',1,'Barth');
INSERT INTO `area_codes` VALUES ('49','038232',1,'Zingst Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038233',1,'Prerow Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038234',1,'Born a Darß');
INSERT INTO `area_codes` VALUES ('49','038292',1,'Kröpelin');
INSERT INTO `area_codes` VALUES ('49','038293',1,'Kühlungsborn Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038294',1,'Neubukow');
INSERT INTO `area_codes` VALUES ('49','038295',1,'Satow b Bad Doberan');
INSERT INTO `area_codes` VALUES ('49','038296',1,'Rerik Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038297',1,'Moitin');
INSERT INTO `area_codes` VALUES ('49','038300',1,'Insel Hiddensee');
INSERT INTO `area_codes` VALUES ('49','038301',1,'Putbus');
INSERT INTO `area_codes` VALUES ('49','038302',1,'Sagard');
INSERT INTO `area_codes` VALUES ('49','038303',1,'Sellin Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038304',1,'Garz Rügen');
INSERT INTO `area_codes` VALUES ('49','038305',1,'Gingst');
INSERT INTO `area_codes` VALUES ('49','038306',1,'Samtens');
INSERT INTO `area_codes` VALUES ('49','038307',1,'Poseritz');
INSERT INTO `area_codes` VALUES ('49','038308',1,'Göhren Rügen');
INSERT INTO `area_codes` VALUES ('49','038309',1,'Trent');
INSERT INTO `area_codes` VALUES ('49','03831',1,'Stralsund');
INSERT INTO `area_codes` VALUES ('49','038320',1,'Tribsees');
INSERT INTO `area_codes` VALUES ('49','038321',1,'Martensdorf b Stralsund');
INSERT INTO `area_codes` VALUES ('49','038322',1,'Richtenberg');
INSERT INTO `area_codes` VALUES ('49','038323',1,'Prohn');
INSERT INTO `area_codes` VALUES ('49','038324',1,'Velgast');
INSERT INTO `area_codes` VALUES ('49','038325',1,'Rolofshagen');
INSERT INTO `area_codes` VALUES ('49','038326',1,'Grimmen');
INSERT INTO `area_codes` VALUES ('49','038327',1,'Elmenhorst Vorpom');
INSERT INTO `area_codes` VALUES ('49','038328',1,'Miltzow');
INSERT INTO `area_codes` VALUES ('49','038331',1,'Rakow Vorpom');
INSERT INTO `area_codes` VALUES ('49','038332',1,'Gross Bisdorf');
INSERT INTO `area_codes` VALUES ('49','038333',1,'Horst b Grimmen');
INSERT INTO `area_codes` VALUES ('49','038334',1,'Grammendorf');
INSERT INTO `area_codes` VALUES ('49','03834',1,'Greifswald');
INSERT INTO `area_codes` VALUES ('49','038351',1,'Mesekenhagen');
INSERT INTO `area_codes` VALUES ('49','038352',1,'Kemnitz b Greifswald');
INSERT INTO `area_codes` VALUES ('49','038353',1,'Gützkow b Greifswald');
INSERT INTO `area_codes` VALUES ('49','038354',1,'Wusterhusen');
INSERT INTO `area_codes` VALUES ('49','038355',1,'Züssow');
INSERT INTO `area_codes` VALUES ('49','038356',1,'Behrenhoff');
INSERT INTO `area_codes` VALUES ('49','03836',1,'Wolgast');
INSERT INTO `area_codes` VALUES ('49','038370',1,'Kröslin');
INSERT INTO `area_codes` VALUES ('49','038371',1,'Karlshagen');
INSERT INTO `area_codes` VALUES ('49','038372',1,'Usedom');
INSERT INTO `area_codes` VALUES ('49','038373',1,'Katzow');
INSERT INTO `area_codes` VALUES ('49','038374',1,'Lassan b Wolgast');
INSERT INTO `area_codes` VALUES ('49','038375',1,'Koserow');
INSERT INTO `area_codes` VALUES ('49','038376',1,'Zirchow');
INSERT INTO `area_codes` VALUES ('49','038377',1,'Zinnowitz');
INSERT INTO `area_codes` VALUES ('49','038378',1,'Heringsdorf Seebad');
INSERT INTO `area_codes` VALUES ('49','038379',1,'Benz Usedom');
INSERT INTO `area_codes` VALUES ('49','03838',1,'Bergen auf Rügen');
INSERT INTO `area_codes` VALUES ('49','038391',1,'Altenkirchen Rügen');
INSERT INTO `area_codes` VALUES ('49','038392',1,'Sassnitz');
INSERT INTO `area_codes` VALUES ('49','038393',1,'Binz Ostseebad');
INSERT INTO `area_codes` VALUES ('49','03841',1,'Wismar Meckl');
INSERT INTO `area_codes` VALUES ('49','038422',1,'Neukloster');
INSERT INTO `area_codes` VALUES ('49','038423',1,'Bad Kleinen');
INSERT INTO `area_codes` VALUES ('49','038424',1,'Bobitz');
INSERT INTO `area_codes` VALUES ('49','038425',1,'Kirchdorf Poel');
INSERT INTO `area_codes` VALUES ('49','038426',1,'Neuburg-Steinhausen');
INSERT INTO `area_codes` VALUES ('49','038427',1,'Blowatz');
INSERT INTO `area_codes` VALUES ('49','038428',1,'Hohenkirchen b Wismar');
INSERT INTO `area_codes` VALUES ('49','038429',1,'Glasin');
INSERT INTO `area_codes` VALUES ('49','03843',1,'Güstrow');
INSERT INTO `area_codes` VALUES ('49','03844',1,'Schwaan');
INSERT INTO `area_codes` VALUES ('49','038450',1,'Tarnow b Bützow');
INSERT INTO `area_codes` VALUES ('49','038451',1,'Hoppenrade b Güstrow');
INSERT INTO `area_codes` VALUES ('49','038452',1,'Lalendorf');
INSERT INTO `area_codes` VALUES ('49','038453',1,'Mistorf');
INSERT INTO `area_codes` VALUES ('49','038454',1,'Kritzkow');
INSERT INTO `area_codes` VALUES ('49','038455',1,'Plaaz');
INSERT INTO `area_codes` VALUES ('49','038456',1,'Langhagen b Güstrow');
INSERT INTO `area_codes` VALUES ('49','038457',1,'Krakow am See');
INSERT INTO `area_codes` VALUES ('49','038458',1,'Zehna');
INSERT INTO `area_codes` VALUES ('49','038459',1,'Laage');
INSERT INTO `area_codes` VALUES ('49','038461',1,'Bützow');
INSERT INTO `area_codes` VALUES ('49','038462',1,'Baumgarten Meckl');
INSERT INTO `area_codes` VALUES ('49','038464',1,'Bernitt');
INSERT INTO `area_codes` VALUES ('49','038466',1,'Jürgenshagen');
INSERT INTO `area_codes` VALUES ('49','03847',1,'Sternberg');
INSERT INTO `area_codes` VALUES ('49','038481',1,'Witzin');
INSERT INTO `area_codes` VALUES ('49','038482',1,'Warin');
INSERT INTO `area_codes` VALUES ('49','038483',1,'Brüel');
INSERT INTO `area_codes` VALUES ('49','038484',1,'Ventschow');
INSERT INTO `area_codes` VALUES ('49','038485',1,'Dabel');
INSERT INTO `area_codes` VALUES ('49','038486',1,'Gustävel');
INSERT INTO `area_codes` VALUES ('49','038488',1,'Demen');
INSERT INTO `area_codes` VALUES ('49','0385',1,'Schwerin Meckl');
INSERT INTO `area_codes` VALUES ('49','03860',1,'Raben Steinfeld');
INSERT INTO `area_codes` VALUES ('49','03861',1,'Plate');
INSERT INTO `area_codes` VALUES ('49','03863',1,'Crivitz');
INSERT INTO `area_codes` VALUES ('49','03865',1,'Holthusen');
INSERT INTO `area_codes` VALUES ('49','03866',1,'Cambs');
INSERT INTO `area_codes` VALUES ('49','03867',1,'Lübstorf');
INSERT INTO `area_codes` VALUES ('49','03868',1,'Rastow');
INSERT INTO `area_codes` VALUES ('49','03869',1,'Dümmer');
INSERT INTO `area_codes` VALUES ('49','03871',1,'Parchim');
INSERT INTO `area_codes` VALUES ('49','038720',1,'Grebbin');
INSERT INTO `area_codes` VALUES ('49','038721',1,'Ziegendorf');
INSERT INTO `area_codes` VALUES ('49','038722',1,'Raduhn');
INSERT INTO `area_codes` VALUES ('49','038723',1,'Kladrum');
INSERT INTO `area_codes` VALUES ('49','038724',1,'Siggelkow');
INSERT INTO `area_codes` VALUES ('49','038725',1,'Gross Godems');
INSERT INTO `area_codes` VALUES ('49','038726',1,'Spornitz');
INSERT INTO `area_codes` VALUES ('49','038727',1,'Mestlin');
INSERT INTO `area_codes` VALUES ('49','038728',1,'Domsühl');
INSERT INTO `area_codes` VALUES ('49','038729',1,'Marnitz');
INSERT INTO `area_codes` VALUES ('49','038731',1,'Lübz');
INSERT INTO `area_codes` VALUES ('49','038732',1,'Gallin b Lübz');
INSERT INTO `area_codes` VALUES ('49','038733',1,'Karbow-Vietlübbe');
INSERT INTO `area_codes` VALUES ('49','038735',1,'Plau am See');
INSERT INTO `area_codes` VALUES ('49','038736',1,'Goldberg Meckl');
INSERT INTO `area_codes` VALUES ('49','038737',1,'Ganzlin');
INSERT INTO `area_codes` VALUES ('49','038738',1,'Karow b Lübz');
INSERT INTO `area_codes` VALUES ('49','03874',1,'Ludwigslust Meckl');
INSERT INTO `area_codes` VALUES ('49','038750',1,'Malliss');
INSERT INTO `area_codes` VALUES ('49','038751',1,'Picher');
INSERT INTO `area_codes` VALUES ('49','038752',1,'Zierzow b Ludwigslust');
INSERT INTO `area_codes` VALUES ('49','038753',1,'Wöbbelin');
INSERT INTO `area_codes` VALUES ('49','038754',1,'Leussow b Ludwigslust');
INSERT INTO `area_codes` VALUES ('49','038755',1,'Eldena');
INSERT INTO `area_codes` VALUES ('49','038756',1,'Grabow Meckl');
INSERT INTO `area_codes` VALUES ('49','038757',1,'Neustadt-Glewe');
INSERT INTO `area_codes` VALUES ('49','038758',1,'Dömitz');
INSERT INTO `area_codes` VALUES ('49','038759',1,'Tewswoos');
INSERT INTO `area_codes` VALUES ('49','03876',1,'Perleberg');
INSERT INTO `area_codes` VALUES ('49','03877',1,'Wittenberge');
INSERT INTO `area_codes` VALUES ('49','038780',1,'Lanz Brandenb');
INSERT INTO `area_codes` VALUES ('49','038781',1,'Mellen');
INSERT INTO `area_codes` VALUES ('49','038782',1,'Reetz b Perleberg');
INSERT INTO `area_codes` VALUES ('49','038783',1,'Dallmin');
INSERT INTO `area_codes` VALUES ('49','038784',1,'Kleinow Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','038785',1,'Berge b Perleberg');
INSERT INTO `area_codes` VALUES ('49','038787',1,'Glöwen');
INSERT INTO `area_codes` VALUES ('49','038788',1,'Gross Warnow');
INSERT INTO `area_codes` VALUES ('49','038789',1,'Wolfshagen b Perleberg');
INSERT INTO `area_codes` VALUES ('49','038791',1,'Bad Wilsnack');
INSERT INTO `area_codes` VALUES ('49','038792',1,'Lenzen (Elbe)');
INSERT INTO `area_codes` VALUES ('49','038793',1,'Dergenthin');
INSERT INTO `area_codes` VALUES ('49','038794',1,'Cumlosen');
INSERT INTO `area_codes` VALUES ('49','038796',1,'Viesecke');
INSERT INTO `area_codes` VALUES ('49','038797',1,'Karstädt Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','03881',1,'Grevesmühlen');
INSERT INTO `area_codes` VALUES ('49','038821',1,'Lüdersdorf Meckl');
INSERT INTO `area_codes` VALUES ('49','038822',1,'Diedrichshagen b Grevesmühlen');
INSERT INTO `area_codes` VALUES ('49','038823',1,'Selmsdorf');
INSERT INTO `area_codes` VALUES ('49','038824',1,'Mallentin');
INSERT INTO `area_codes` VALUES ('49','038825',1,'Klütz');
INSERT INTO `area_codes` VALUES ('49','038826',1,'Dassow');
INSERT INTO `area_codes` VALUES ('49','038827',1,'Kalkhorst');
INSERT INTO `area_codes` VALUES ('49','038828',1,'Schönberg Meckl');
INSERT INTO `area_codes` VALUES ('49','03883',1,'Hagenow');
INSERT INTO `area_codes` VALUES ('49','038841',1,'Neuhaus Elbe');
INSERT INTO `area_codes` VALUES ('49','038842',1,'Lüttenmark');
INSERT INTO `area_codes` VALUES ('49','038843',1,'Bennin');
INSERT INTO `area_codes` VALUES ('49','038844',1,'Gülze');
INSERT INTO `area_codes` VALUES ('49','038845',1,'Kaarssen');
INSERT INTO `area_codes` VALUES ('49','038847',1,'Boizenburg Elbe');
INSERT INTO `area_codes` VALUES ('49','038848',1,'Vellahn');
INSERT INTO `area_codes` VALUES ('49','038850',1,'Gammelin');
INSERT INTO `area_codes` VALUES ('49','038851',1,'Zarrentin Meckl');
INSERT INTO `area_codes` VALUES ('49','038852',1,'Wittenburg');
INSERT INTO `area_codes` VALUES ('49','038853',1,'Drönnewitz b Hagenow');
INSERT INTO `area_codes` VALUES ('49','038854',1,'Redefin');
INSERT INTO `area_codes` VALUES ('49','038855',1,'Lübtheen');
INSERT INTO `area_codes` VALUES ('49','038856',1,'Pritzier b Hagenow');
INSERT INTO `area_codes` VALUES ('49','038858',1,'Lassahn');
INSERT INTO `area_codes` VALUES ('49','038859',1,'Alt Zachun');
INSERT INTO `area_codes` VALUES ('49','03886',1,'Gadebusch');
INSERT INTO `area_codes` VALUES ('49','038871',1,'Mühlen Eichsen');
INSERT INTO `area_codes` VALUES ('49','038872',1,'Rehna');
INSERT INTO `area_codes` VALUES ('49','038873',1,'Carlow');
INSERT INTO `area_codes` VALUES ('49','038874',1,'Lützow');
INSERT INTO `area_codes` VALUES ('49','038875',1,'Schlagsdorf b Gadebusch');
INSERT INTO `area_codes` VALUES ('49','038876',1,'Roggendorf');
INSERT INTO `area_codes` VALUES ('49','039000',1,'Beetzendorf');
INSERT INTO `area_codes` VALUES ('49','039001',1,'Apenburg');
INSERT INTO `area_codes` VALUES ('49','039002',1,'Oebisfelde');
INSERT INTO `area_codes` VALUES ('49','039003',1,'Jübar');
INSERT INTO `area_codes` VALUES ('49','039004',1,'Köckte b Gardelegen');
INSERT INTO `area_codes` VALUES ('49','039005',1,'Kusey');
INSERT INTO `area_codes` VALUES ('49','039006',1,'Miesterhorst');
INSERT INTO `area_codes` VALUES ('49','039007',1,'Tangeln');
INSERT INTO `area_codes` VALUES ('49','039008',1,'Kunrau');
INSERT INTO `area_codes` VALUES ('49','039009',1,'Badel');
INSERT INTO `area_codes` VALUES ('49','03901',1,'Salzwedel');
INSERT INTO `area_codes` VALUES ('49','03902',1,'Diesdorf Altm');
INSERT INTO `area_codes` VALUES ('49','039030',1,'Brunau');
INSERT INTO `area_codes` VALUES ('49','039031',1,'Dähre');
INSERT INTO `area_codes` VALUES ('49','039032',1,'Mahlsdorf b Salzwedel');
INSERT INTO `area_codes` VALUES ('49','039033',1,'Wallstawe');
INSERT INTO `area_codes` VALUES ('49','039034',1,'Fleetmark');
INSERT INTO `area_codes` VALUES ('49','039035',1,'Kuhfelde');
INSERT INTO `area_codes` VALUES ('49','039036',1,'Binde');
INSERT INTO `area_codes` VALUES ('49','039037',1,'Pretzier');
INSERT INTO `area_codes` VALUES ('49','039038',1,'Henningen');
INSERT INTO `area_codes` VALUES ('49','039039',1,'Bonese');
INSERT INTO `area_codes` VALUES ('49','03904',1,'Haldensleben');
INSERT INTO `area_codes` VALUES ('49','039050',1,'Bartensleben');
INSERT INTO `area_codes` VALUES ('49','039051',1,'Calvörde');
INSERT INTO `area_codes` VALUES ('49','039052',1,'Erxleben b Haldensleben');
INSERT INTO `area_codes` VALUES ('49','039053',1,'Süplingen');
INSERT INTO `area_codes` VALUES ('49','039054',1,'Flechtingen');
INSERT INTO `area_codes` VALUES ('49','039055',1,'Hörsingen');
INSERT INTO `area_codes` VALUES ('49','039056',1,'Klüden');
INSERT INTO `area_codes` VALUES ('49','039057',1,'Rätzlingen Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','039058',1,'Uthmöden');
INSERT INTO `area_codes` VALUES ('49','039059',1,'Wegenstedt');
INSERT INTO `area_codes` VALUES ('49','039061',1,'Weferlingen');
INSERT INTO `area_codes` VALUES ('49','039062',1,'Bebertal');
INSERT INTO `area_codes` VALUES ('49','03907',1,'Gardelegen');
INSERT INTO `area_codes` VALUES ('49','039080',1,'Kalbe Milde');
INSERT INTO `area_codes` VALUES ('49','039081',1,'Kakerbeck Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','039082',1,'Mieste');
INSERT INTO `area_codes` VALUES ('49','039083',1,'Messdorf');
INSERT INTO `area_codes` VALUES ('49','039084',1,'Lindstedt');
INSERT INTO `area_codes` VALUES ('49','039085',1,'Zichtau');
INSERT INTO `area_codes` VALUES ('49','039086',1,'Jävenitz');
INSERT INTO `area_codes` VALUES ('49','039087',1,'Jerchel Altmark');
INSERT INTO `area_codes` VALUES ('49','039088',1,'Letzlingen');
INSERT INTO `area_codes` VALUES ('49','039089',1,'Bismark Altmark');
INSERT INTO `area_codes` VALUES ('49','03909',1,'Klötze Altmark');
INSERT INTO `area_codes` VALUES ('49','0391',1,'Magdeburg');
INSERT INTO `area_codes` VALUES ('49','039200',1,'Gommern');
INSERT INTO `area_codes` VALUES ('49','039201',1,'Wolmirstedt');
INSERT INTO `area_codes` VALUES ('49','039202',1,'Gross Ammensleben');
INSERT INTO `area_codes` VALUES ('49','039203',1,'Barleben');
INSERT INTO `area_codes` VALUES ('49','039204',1,'Niederndodeleben');
INSERT INTO `area_codes` VALUES ('49','039205',1,'Langenweddingen');
INSERT INTO `area_codes` VALUES ('49','039206',1,'Eichenbarleben');
INSERT INTO `area_codes` VALUES ('49','039207',1,'Colbitz');
INSERT INTO `area_codes` VALUES ('49','039208',1,'Loitsche');
INSERT INTO `area_codes` VALUES ('49','039209',1,'Wanzleben');
INSERT INTO `area_codes` VALUES ('49','03921',1,'Burg b Magdeburg');
INSERT INTO `area_codes` VALUES ('49','039221',1,'Möckern b Magdeburg');
INSERT INTO `area_codes` VALUES ('49','039222',1,'Möser');
INSERT INTO `area_codes` VALUES ('49','039223',1,'Theessen');
INSERT INTO `area_codes` VALUES ('49','039224',1,'Büden');
INSERT INTO `area_codes` VALUES ('49','039225',1,'Altengrabow');
INSERT INTO `area_codes` VALUES ('49','039226',1,'Hohenziatz');
INSERT INTO `area_codes` VALUES ('49','03923',1,'Zerbst');
INSERT INTO `area_codes` VALUES ('49','039241',1,'Leitzkau');
INSERT INTO `area_codes` VALUES ('49','039242',1,'Prödel');
INSERT INTO `area_codes` VALUES ('49','039243',1,'Nedlitz b Zerbst');
INSERT INTO `area_codes` VALUES ('49','039244',1,'Steutz');
INSERT INTO `area_codes` VALUES ('49','039245',1,'Loburg');
INSERT INTO `area_codes` VALUES ('49','039246',1,'Lindau Anh');
INSERT INTO `area_codes` VALUES ('49','039247',1,'Güterglück');
INSERT INTO `area_codes` VALUES ('49','039248',1,'Dobritz');
INSERT INTO `area_codes` VALUES ('49','03925',1,'Stassfurt');
INSERT INTO `area_codes` VALUES ('49','039262',1,'Güsten Anh');
INSERT INTO `area_codes` VALUES ('49','039263',1,'Unseburg');
INSERT INTO `area_codes` VALUES ('49','039264',1,'Kroppenstedt');
INSERT INTO `area_codes` VALUES ('49','039265',1,'Löderburg');
INSERT INTO `area_codes` VALUES ('49','039266',1,'Förderstedt');
INSERT INTO `area_codes` VALUES ('49','039267',1,'Schneidlingen');
INSERT INTO `area_codes` VALUES ('49','039268',1,'Egeln');
INSERT INTO `area_codes` VALUES ('49','03928',1,'Schönebeck Elbe');
INSERT INTO `area_codes` VALUES ('49','039291',1,'Calbe Saale');
INSERT INTO `area_codes` VALUES ('49','039292',1,'Biederitz');
INSERT INTO `area_codes` VALUES ('49','039293',1,'Dreileben');
INSERT INTO `area_codes` VALUES ('49','039294',1,'Gross Rosenburg');
INSERT INTO `area_codes` VALUES ('49','039295',1,'Zuchau');
INSERT INTO `area_codes` VALUES ('49','039296',1,'Welsleben');
INSERT INTO `area_codes` VALUES ('49','039297',1,'Eickendorf Kr Schönebeck');
INSERT INTO `area_codes` VALUES ('49','039298',1,'Barby Elbe');
INSERT INTO `area_codes` VALUES ('49','03931',1,'Stendal');
INSERT INTO `area_codes` VALUES ('49','039320',1,'Schinne');
INSERT INTO `area_codes` VALUES ('49','039321',1,'Arneburg');
INSERT INTO `area_codes` VALUES ('49','039322',1,'Tangermünde');
INSERT INTO `area_codes` VALUES ('49','039323',1,'Schönhausen Elbe');
INSERT INTO `area_codes` VALUES ('49','039324',1,'Kläden b Stendal');
INSERT INTO `area_codes` VALUES ('49','039325',1,'Vinzelberg');
INSERT INTO `area_codes` VALUES ('49','039327',1,'Klietz');
INSERT INTO `area_codes` VALUES ('49','039328',1,'Rochau');
INSERT INTO `area_codes` VALUES ('49','039329',1,'Möringen');
INSERT INTO `area_codes` VALUES ('49','03933',1,'Genthin');
INSERT INTO `area_codes` VALUES ('49','039341',1,'Redekin');
INSERT INTO `area_codes` VALUES ('49','039342',1,'Gladau');
INSERT INTO `area_codes` VALUES ('49','039343',1,'Jerichow');
INSERT INTO `area_codes` VALUES ('49','039344',1,'Güsen');
INSERT INTO `area_codes` VALUES ('49','039345',1,'Parchen');
INSERT INTO `area_codes` VALUES ('49','039346',1,'Tucheim');
INSERT INTO `area_codes` VALUES ('49','039347',1,'Kade');
INSERT INTO `area_codes` VALUES ('49','039348',1,'Klitsche');
INSERT INTO `area_codes` VALUES ('49','039349',1,'Parey Elbe');
INSERT INTO `area_codes` VALUES ('49','03935',1,'Tangerhütte');
INSERT INTO `area_codes` VALUES ('49','039361',1,'Lüderitz');
INSERT INTO `area_codes` VALUES ('49','039362',1,'Grieben b Tangerhütte');
INSERT INTO `area_codes` VALUES ('49','039363',1,'Angern');
INSERT INTO `area_codes` VALUES ('49','039364',1,'Dolle');
INSERT INTO `area_codes` VALUES ('49','039365',1,'Bellingen b Stendal');
INSERT INTO `area_codes` VALUES ('49','039366',1,'Kehnert');
INSERT INTO `area_codes` VALUES ('49','03937',1,'Osterburg Altmark');
INSERT INTO `area_codes` VALUES ('49','039382',1,'Kamern');
INSERT INTO `area_codes` VALUES ('49','039383',1,'Sandau Elbe');
INSERT INTO `area_codes` VALUES ('49','039384',1,'Arendsee Altmark');
INSERT INTO `area_codes` VALUES ('49','039386',1,'Seehausen Altmark');
INSERT INTO `area_codes` VALUES ('49','039387',1,'Havelberg');
INSERT INTO `area_codes` VALUES ('49','039388',1,'Goldbeck Altm');
INSERT INTO `area_codes` VALUES ('49','039389',1,'Schollene');
INSERT INTO `area_codes` VALUES ('49','039390',1,'Iden');
INSERT INTO `area_codes` VALUES ('49','039391',1,'Lückstedt');
INSERT INTO `area_codes` VALUES ('49','039392',1,'Rönnebeck Sachs-Ahn');
INSERT INTO `area_codes` VALUES ('49','039393',1,'Werben Elbe');
INSERT INTO `area_codes` VALUES ('49','039394',1,'Hohenberg-Krusemark');
INSERT INTO `area_codes` VALUES ('49','039395',1,'Wanzer');
INSERT INTO `area_codes` VALUES ('49','039396',1,'Neukirchen Altmark');
INSERT INTO `area_codes` VALUES ('49','039397',1,'Geestgottberg');
INSERT INTO `area_codes` VALUES ('49','039398',1,'Gross Garz');
INSERT INTO `area_codes` VALUES ('49','039399',1,'Kleinau');
INSERT INTO `area_codes` VALUES ('49','039400',1,'Wefensleben');
INSERT INTO `area_codes` VALUES ('49','039401',1,'Neuwegersleben');
INSERT INTO `area_codes` VALUES ('49','039402',1,'Völpke');
INSERT INTO `area_codes` VALUES ('49','039403',1,'Gröningen Sachs-Ahn');
INSERT INTO `area_codes` VALUES ('49','039404',1,'Ausleben');
INSERT INTO `area_codes` VALUES ('49','039405',1,'Hötensleben');
INSERT INTO `area_codes` VALUES ('49','039406',1,'Harbke');
INSERT INTO `area_codes` VALUES ('49','039407',1,'Seehausen Börde');
INSERT INTO `area_codes` VALUES ('49','039408',1,'Hadmersleben');
INSERT INTO `area_codes` VALUES ('49','039409',1,'Eilsleben');
INSERT INTO `area_codes` VALUES ('49','03941',1,'Halberstadt');
INSERT INTO `area_codes` VALUES ('49','039421',1,'Osterwieck');
INSERT INTO `area_codes` VALUES ('49','039422',1,'Badersleben');
INSERT INTO `area_codes` VALUES ('49','039423',1,'Wegeleben');
INSERT INTO `area_codes` VALUES ('49','039424',1,'Schwanebeck Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','039425',1,'Dingelstedt a Huy');
INSERT INTO `area_codes` VALUES ('49','039426',1,'Hessen');
INSERT INTO `area_codes` VALUES ('49','039427',1,'Ströbeck');
INSERT INTO `area_codes` VALUES ('49','039428',1,'Pabstorf');
INSERT INTO `area_codes` VALUES ('49','03943',1,'Wernigerode');
INSERT INTO `area_codes` VALUES ('49','03944',1,'Blankenburg Harz');
INSERT INTO `area_codes` VALUES ('49','039451',1,'Wasserleben');
INSERT INTO `area_codes` VALUES ('49','039452',1,'Ilsenburg');
INSERT INTO `area_codes` VALUES ('49','039453',1,'Derenburg');
INSERT INTO `area_codes` VALUES ('49','039454',1,'Elbingerode Harz');
INSERT INTO `area_codes` VALUES ('49','039455',1,'Schierke');
INSERT INTO `area_codes` VALUES ('49','039456',1,'Altenbrak');
INSERT INTO `area_codes` VALUES ('49','039457',1,'Benneckenstein Harz');
INSERT INTO `area_codes` VALUES ('49','039458',1,'Heudeber');
INSERT INTO `area_codes` VALUES ('49','039459',1,'Hasselfelde');
INSERT INTO `area_codes` VALUES ('49','03946',1,'Quedlinburg');
INSERT INTO `area_codes` VALUES ('49','03947',1,'Thale');
INSERT INTO `area_codes` VALUES ('49','039481',1,'Hedersleben b Aschersleben');
INSERT INTO `area_codes` VALUES ('49','039482',1,'Gatersleben');
INSERT INTO `area_codes` VALUES ('49','039483',1,'Ballenstedt');
INSERT INTO `area_codes` VALUES ('49','039484',1,'Harzgerode');
INSERT INTO `area_codes` VALUES ('49','039485',1,'Gernrode Harz');
INSERT INTO `area_codes` VALUES ('49','039487',1,'Friedrichsbrunn');
INSERT INTO `area_codes` VALUES ('49','039488',1,'Güntersberge');
INSERT INTO `area_codes` VALUES ('49','039489',1,'Strassberg Harz');
INSERT INTO `area_codes` VALUES ('49','03949',1,'Oschersleben Bode');
INSERT INTO `area_codes` VALUES ('49','0395',1,'Neubrandenburg');
INSERT INTO `area_codes` VALUES ('49','039600',1,'Zwiedorf');
INSERT INTO `area_codes` VALUES ('49','039601',1,'Friedland Meckl');
INSERT INTO `area_codes` VALUES ('49','039602',1,'Kleeth');
INSERT INTO `area_codes` VALUES ('49','039603',1,'Burg Stargard');
INSERT INTO `area_codes` VALUES ('49','039604',1,'Wildberg b Altentreptow');
INSERT INTO `area_codes` VALUES ('49','039605',1,'Gross Nemerow');
INSERT INTO `area_codes` VALUES ('49','039606',1,'Glienke');
INSERT INTO `area_codes` VALUES ('49','039607',1,'Kotelow');
INSERT INTO `area_codes` VALUES ('49','039608',1,'Staven');
INSERT INTO `area_codes` VALUES ('49','03961',1,'Altentreptow');
INSERT INTO `area_codes` VALUES ('49','03962',1,'Penzlin b Waren');
INSERT INTO `area_codes` VALUES ('49','03963',1,'Woldegk');
INSERT INTO `area_codes` VALUES ('49','03964',1,'Bredenfelde b Strasburg');
INSERT INTO `area_codes` VALUES ('49','03965',1,'Burow b Altentreptow');
INSERT INTO `area_codes` VALUES ('49','03966',1,'Cölpin');
INSERT INTO `area_codes` VALUES ('49','03967',1,'Oertzenhof b Strasburg');
INSERT INTO `area_codes` VALUES ('49','03968',1,'Schönbeck Meckl');
INSERT INTO `area_codes` VALUES ('49','03969',1,'Siedenbollentin');
INSERT INTO `area_codes` VALUES ('49','03971',1,'Anklam');
INSERT INTO `area_codes` VALUES ('49','039721',1,'Liepen b Anklam');
INSERT INTO `area_codes` VALUES ('49','039722',1,'Sarnow b Anklam');
INSERT INTO `area_codes` VALUES ('49','039723',1,'Krien');
INSERT INTO `area_codes` VALUES ('49','039724',1,'Klein Bünzow');
INSERT INTO `area_codes` VALUES ('49','039726',1,'Ducherow');
INSERT INTO `area_codes` VALUES ('49','039727',1,'Spantekow');
INSERT INTO `area_codes` VALUES ('49','039728',1,'Medow b Anklam');
INSERT INTO `area_codes` VALUES ('49','03973',1,'Pasewalk');
INSERT INTO `area_codes` VALUES ('49','039740',1,'Nechlin');
INSERT INTO `area_codes` VALUES ('49','039741',1,'Jatznick');
INSERT INTO `area_codes` VALUES ('49','039742',1,'Brüssow b Pasewalk');
INSERT INTO `area_codes` VALUES ('49','039743',1,'Zerrenthin');
INSERT INTO `area_codes` VALUES ('49','039744',1,'Rothenklempenow');
INSERT INTO `area_codes` VALUES ('49','039745',1,'Hetzdorf b Strasburg');
INSERT INTO `area_codes` VALUES ('49','039746',1,'Krackow');
INSERT INTO `area_codes` VALUES ('49','039747',1,'Züsedom');
INSERT INTO `area_codes` VALUES ('49','039748',1,'Viereck');
INSERT INTO `area_codes` VALUES ('49','039749',1,'Grambow b Pasewalk');
INSERT INTO `area_codes` VALUES ('49','039751',1,'Penkun');
INSERT INTO `area_codes` VALUES ('49','039752',1,'Blumenhagen b Strasburg');
INSERT INTO `area_codes` VALUES ('49','039753',1,'Strasburg');
INSERT INTO `area_codes` VALUES ('49','039754',1,'Löcknitz Vorpom');
INSERT INTO `area_codes` VALUES ('49','03976',1,'Torgelow b Ueckermünde');
INSERT INTO `area_codes` VALUES ('49','039771',1,'Ueckermünde');
INSERT INTO `area_codes` VALUES ('49','039772',1,'Rothemühl');
INSERT INTO `area_codes` VALUES ('49','039773',1,'Altwarp');
INSERT INTO `area_codes` VALUES ('49','039774',1,'Mönkebude');
INSERT INTO `area_codes` VALUES ('49','039775',1,'Ahlbeck b Torgelow');
INSERT INTO `area_codes` VALUES ('49','039776',1,'Hintersee');
INSERT INTO `area_codes` VALUES ('49','039777',1,'Borkenfriede');
INSERT INTO `area_codes` VALUES ('49','039778',1,'Ferdinandshof b Torgelow');
INSERT INTO `area_codes` VALUES ('49','039779',1,'Eggesin');
INSERT INTO `area_codes` VALUES ('49','03981',1,'Neustrelitz');
INSERT INTO `area_codes` VALUES ('49','039820',1,'Triepkendorf');
INSERT INTO `area_codes` VALUES ('49','039821',1,'Carpin');
INSERT INTO `area_codes` VALUES ('49','039822',1,'Kratzeburg');
INSERT INTO `area_codes` VALUES ('49','039823',1,'Rechlin');
INSERT INTO `area_codes` VALUES ('49','039824',1,'Hohenzieritz');
INSERT INTO `area_codes` VALUES ('49','039825',1,'Wokuhl');
INSERT INTO `area_codes` VALUES ('49','039826',1,'Blankensee b Neustrelitz');
INSERT INTO `area_codes` VALUES ('49','039827',1,'Schwarz b Neustrelitz');
INSERT INTO `area_codes` VALUES ('49','039828',1,'Wustrow Kr Mecklenburg-Strelitz');
INSERT INTO `area_codes` VALUES ('49','039829',1,'Blankenförde');
INSERT INTO `area_codes` VALUES ('49','039831',1,'Feldberg Meckl');
INSERT INTO `area_codes` VALUES ('49','039832',1,'Wesenberg Meckl');
INSERT INTO `area_codes` VALUES ('49','039833',1,'Mirow Kr Neustrelitz');
INSERT INTO `area_codes` VALUES ('49','03984',1,'Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039851',1,'Göritz b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039852',1,'Schönermark b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039853',1,'Holzendorf b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039854',1,'Kleptow');
INSERT INTO `area_codes` VALUES ('49','039855',1,'Parmen-Weggun');
INSERT INTO `area_codes` VALUES ('49','039856',1,'Beenz b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039857',1,'Drense');
INSERT INTO `area_codes` VALUES ('49','039858',1,'Bietikow');
INSERT INTO `area_codes` VALUES ('49','039859',1,'Fürstenwerder');
INSERT INTO `area_codes` VALUES ('49','039861',1,'Gramzow b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039862',1,'Schmölln b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039863',1,'Seehausen b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','03987',1,'Templin');
INSERT INTO `area_codes` VALUES ('49','039881',1,'Ringenwalde b Templin');
INSERT INTO `area_codes` VALUES ('49','039882',1,'Gollin');
INSERT INTO `area_codes` VALUES ('49','039883',1,'Groß Dölln');
INSERT INTO `area_codes` VALUES ('49','039884',1,'Hassleben b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039885',1,'Jakobshagen');
INSERT INTO `area_codes` VALUES ('49','039886',1,'Milmersdorf');
INSERT INTO `area_codes` VALUES ('49','039887',1,'Gerswalde');
INSERT INTO `area_codes` VALUES ('49','039888',1,'Lychen');
INSERT INTO `area_codes` VALUES ('49','039889',1,'Boitzenburg');
INSERT INTO `area_codes` VALUES ('49','03991',1,'Waren Müritz');
INSERT INTO `area_codes` VALUES ('49','039921',1,'Ankershagen');
INSERT INTO `area_codes` VALUES ('49','039922',1,'Dambeck b Röbel');
INSERT INTO `area_codes` VALUES ('49','039923',1,'Priborn');
INSERT INTO `area_codes` VALUES ('49','039924',1,'Stuer');
INSERT INTO `area_codes` VALUES ('49','039925',1,'Wredenhagen');
INSERT INTO `area_codes` VALUES ('49','039926',1,'Grabowhöfe');
INSERT INTO `area_codes` VALUES ('49','039927',1,'Nossentiner Hütte');
INSERT INTO `area_codes` VALUES ('49','039928',1,'Möllenhagen');
INSERT INTO `area_codes` VALUES ('49','039929',1,'Jabel b Waren');
INSERT INTO `area_codes` VALUES ('49','039931',1,'Röbel Müritz');
INSERT INTO `area_codes` VALUES ('49','039932',1,'Malchow b Waren');
INSERT INTO `area_codes` VALUES ('49','039933',1,'Vollrathsruhe');
INSERT INTO `area_codes` VALUES ('49','039934',1,'Groß Plasten');
INSERT INTO `area_codes` VALUES ('49','03994',1,'Malchin');
INSERT INTO `area_codes` VALUES ('49','039951',1,'Faulenrost');
INSERT INTO `area_codes` VALUES ('49','039952',1,'Grammentin');
INSERT INTO `area_codes` VALUES ('49','039953',1,'Schwinkendorf');
INSERT INTO `area_codes` VALUES ('49','039954',1,'Stavenhagen Reuterstadt');
INSERT INTO `area_codes` VALUES ('49','039955',1,'Jürgenstorf Meckl');
INSERT INTO `area_codes` VALUES ('49','039956',1,'Neukalen');
INSERT INTO `area_codes` VALUES ('49','039957',1,'Gielow');
INSERT INTO `area_codes` VALUES ('49','039959',1,'Dargun');
INSERT INTO `area_codes` VALUES ('49','03996',1,'Teterow');
INSERT INTO `area_codes` VALUES ('49','039971',1,'Gnoien');
INSERT INTO `area_codes` VALUES ('49','039972',1,'Walkendorf');
INSERT INTO `area_codes` VALUES ('49','039973',1,'Altkalen');
INSERT INTO `area_codes` VALUES ('49','039975',1,'Thürkow');
INSERT INTO `area_codes` VALUES ('49','039976',1,'Groß Bützin');
INSERT INTO `area_codes` VALUES ('49','039977',1,'Jördenstorf');
INSERT INTO `area_codes` VALUES ('49','039978',1,'Gross Roge');
INSERT INTO `area_codes` VALUES ('49','03998',1,'Demmin');
INSERT INTO `area_codes` VALUES ('49','039991',1,'Daberkow');
INSERT INTO `area_codes` VALUES ('49','039992',1,'Görmin');
INSERT INTO `area_codes` VALUES ('49','039993',1,'Hohenmocker');
INSERT INTO `area_codes` VALUES ('49','039994',1,'Metschow');
INSERT INTO `area_codes` VALUES ('49','039995',1,'Nossendorf');
INSERT INTO `area_codes` VALUES ('49','039996',1,'Törpin');
INSERT INTO `area_codes` VALUES ('49','039997',1,'Jarmen');
INSERT INTO `area_codes` VALUES ('49','039998',1,'Loitz b Demmin');
INSERT INTO `area_codes` VALUES ('49','039999',1,'Tutow');
INSERT INTO `area_codes` VALUES ('49','040',1,'Hamburg');
INSERT INTO `area_codes` VALUES ('49','04101',1,'Pinneberg');
INSERT INTO `area_codes` VALUES ('49','04102',1,'Ahrensburg');
INSERT INTO `area_codes` VALUES ('49','04103',1,'Wedel');
INSERT INTO `area_codes` VALUES ('49','04104',1,'Aumühle b Hamburg');
INSERT INTO `area_codes` VALUES ('49','04105',1,'Seevetal');
INSERT INTO `area_codes` VALUES ('49','04106',1,'Quickborn Kr Pinneberg');
INSERT INTO `area_codes` VALUES ('49','04107',1,'Siek Kr Stormarn');
INSERT INTO `area_codes` VALUES ('49','04108',1,'Rosengarten Kr Harburg');
INSERT INTO `area_codes` VALUES ('49','04109',1,'Tangstedt Bz Hamburg');
INSERT INTO `area_codes` VALUES ('49','04120',1,'Ellerhoop');
INSERT INTO `area_codes` VALUES ('49','04121',1,'Elmshorn');
INSERT INTO `area_codes` VALUES ('49','04122',1,'Uetersen');
INSERT INTO `area_codes` VALUES ('49','04123',1,'Barmstedt');
INSERT INTO `area_codes` VALUES ('49','04124',1,'Glückstadt');
INSERT INTO `area_codes` VALUES ('49','04125',1,'Seestermühe');
INSERT INTO `area_codes` VALUES ('49','04126',1,'Horst Holstein');
INSERT INTO `area_codes` VALUES ('49','04127',1,'Westerhorn');
INSERT INTO `area_codes` VALUES ('49','04128',1,'Kollmar');
INSERT INTO `area_codes` VALUES ('49','04129',1,'Haseldorf');
INSERT INTO `area_codes` VALUES ('49','04131',1,'Lüneburg');
INSERT INTO `area_codes` VALUES ('49','04132',1,'Amelinghausen');
INSERT INTO `area_codes` VALUES ('49','04133',1,'Wittorf Kr Lüneburg');
INSERT INTO `area_codes` VALUES ('49','04134',1,'Embsen Kr Lünebeburg');
INSERT INTO `area_codes` VALUES ('49','04135',1,'Kirchgellersen');
INSERT INTO `area_codes` VALUES ('49','04136',1,'Scharnebeck');
INSERT INTO `area_codes` VALUES ('49','04137',1,'Barendorf');
INSERT INTO `area_codes` VALUES ('49','04138',1,'Betzendorf Kr Lünebeburg');
INSERT INTO `area_codes` VALUES ('49','04139',1,'Hohnstorf Elbe');
INSERT INTO `area_codes` VALUES ('49','04140',1,'Estorf Kr Stade');
INSERT INTO `area_codes` VALUES ('49','04141',1,'Stade');
INSERT INTO `area_codes` VALUES ('49','04142',1,'Steinkirchen Kr Stade');
INSERT INTO `area_codes` VALUES ('49','04143',1,'Drochtersen');
INSERT INTO `area_codes` VALUES ('49','04144',1,'Himmelpforten');
INSERT INTO `area_codes` VALUES ('49','04146',1,'Stade-Bützfleth');
INSERT INTO `area_codes` VALUES ('49','04148',1,'Drochtersen-Assel');
INSERT INTO `area_codes` VALUES ('49','04149',1,'Fredenbeck');
INSERT INTO `area_codes` VALUES ('49','04151',1,'Schwarzenbek');
INSERT INTO `area_codes` VALUES ('49','04152',1,'Geesthacht');
INSERT INTO `area_codes` VALUES ('49','04153',1,'Lauenburg Elbe');
INSERT INTO `area_codes` VALUES ('49','04154',1,'Trittau');
INSERT INTO `area_codes` VALUES ('49','04155',1,'Büchen');
INSERT INTO `area_codes` VALUES ('49','04156',1,'Talkau');
INSERT INTO `area_codes` VALUES ('49','04158',1,'Roseburg');
INSERT INTO `area_codes` VALUES ('49','04159',1,'Basthorst');
INSERT INTO `area_codes` VALUES ('49','04161',1,'Buxtehude');
INSERT INTO `area_codes` VALUES ('49','04162',1,'Jork');
INSERT INTO `area_codes` VALUES ('49','04163',1,'Horneburg Niederelbe');
INSERT INTO `area_codes` VALUES ('49','04164',1,'Harsefeld');
INSERT INTO `area_codes` VALUES ('49','04165',1,'Hollenstedt Nordheide');
INSERT INTO `area_codes` VALUES ('49','04166',1,'Ahlerstedt');
INSERT INTO `area_codes` VALUES ('49','04167',1,'Apensen');
INSERT INTO `area_codes` VALUES ('49','04168',1,'Neu Wulmstorf-Elstorf');
INSERT INTO `area_codes` VALUES ('49','04169',1,'Sauensiek');
INSERT INTO `area_codes` VALUES ('49','04171',1,'Winsen Luhe');
INSERT INTO `area_codes` VALUES ('49','04172',1,'Salzhausen');
INSERT INTO `area_codes` VALUES ('49','04173',1,'Wulfsen');
INSERT INTO `area_codes` VALUES ('49','04174',1,'Stelle Kr Harburg');
INSERT INTO `area_codes` VALUES ('49','04175',1,'Egestorf Nordheide');
INSERT INTO `area_codes` VALUES ('49','04176',1,'Marschacht');
INSERT INTO `area_codes` VALUES ('49','04177',1,'Drage Elbe');
INSERT INTO `area_codes` VALUES ('49','04178',1,'Radbruch');
INSERT INTO `area_codes` VALUES ('49','04179',1,'Winsen-Tönnhausen');
INSERT INTO `area_codes` VALUES ('49','04180',1,'Königsmoor');
INSERT INTO `area_codes` VALUES ('49','04181',1,'Buchholz in der Nordheide');
INSERT INTO `area_codes` VALUES ('49','04182',1,'Tostedt');
INSERT INTO `area_codes` VALUES ('49','04183',1,'Jesteburg');
INSERT INTO `area_codes` VALUES ('49','04184',1,'Hanstedt Nordheide');
INSERT INTO `area_codes` VALUES ('49','04185',1,'Marxen Auetal');
INSERT INTO `area_codes` VALUES ('49','04186',1,'Buchholz-Trelde');
INSERT INTO `area_codes` VALUES ('49','04187',1,'Holm-Seppensen');
INSERT INTO `area_codes` VALUES ('49','04188',1,'Welle Nordheide');
INSERT INTO `area_codes` VALUES ('49','04189',1,'Undeloh');
INSERT INTO `area_codes` VALUES ('49','04191',1,'Kaltenkirchen Holst');
INSERT INTO `area_codes` VALUES ('49','04192',1,'Bad Bramstedt');
INSERT INTO `area_codes` VALUES ('49','04193',1,'Henstedt-Ulzburg');
INSERT INTO `area_codes` VALUES ('49','04194',1,'Sievershütten');
INSERT INTO `area_codes` VALUES ('49','04195',1,'Hartenholm');
INSERT INTO `area_codes` VALUES ('49','04202',1,'Achim b Bremen');
INSERT INTO `area_codes` VALUES ('49','04203',1,'Weyhe b Bremen');
INSERT INTO `area_codes` VALUES ('49','04204',1,'Thedinghausen');
INSERT INTO `area_codes` VALUES ('49','04205',1,'Ottersberg');
INSERT INTO `area_codes` VALUES ('49','04206',1,'Stuhr-Heiligenrode');
INSERT INTO `area_codes` VALUES ('49','04207',1,'Oyten');
INSERT INTO `area_codes` VALUES ('49','04208',1,'Grasberg');
INSERT INTO `area_codes` VALUES ('49','04209',1,'Schwanewede');
INSERT INTO `area_codes` VALUES ('49','0421',1,'Bremen');
INSERT INTO `area_codes` VALUES ('49','04221',1,'Delmenhorst');
INSERT INTO `area_codes` VALUES ('49','04222',1,'Ganderkesee');
INSERT INTO `area_codes` VALUES ('49','04223',1,'Ganderkesee-Bookholzberg');
INSERT INTO `area_codes` VALUES ('49','04224',1,'Gross Ippener');
INSERT INTO `area_codes` VALUES ('49','04230',1,'Verden-Walle');
INSERT INTO `area_codes` VALUES ('49','04231',1,'Verden Aller');
INSERT INTO `area_codes` VALUES ('49','04232',1,'Langwedel Kr Verden');
INSERT INTO `area_codes` VALUES ('49','04233',1,'Blender');
INSERT INTO `area_codes` VALUES ('49','04234',1,'Dörverden');
INSERT INTO `area_codes` VALUES ('49','04235',1,'Langwedel-Etelsen');
INSERT INTO `area_codes` VALUES ('49','04236',1,'Kirchlinteln');
INSERT INTO `area_codes` VALUES ('49','04237',1,'Bendingbostel');
INSERT INTO `area_codes` VALUES ('49','04238',1,'Neddenaverbergen');
INSERT INTO `area_codes` VALUES ('49','04239',1,'Dörverden-Westen');
INSERT INTO `area_codes` VALUES ('49','04240',1,'Syke-Heiligenfelde');
INSERT INTO `area_codes` VALUES ('49','04241',1,'Bassum');
INSERT INTO `area_codes` VALUES ('49','04242',1,'Syke');
INSERT INTO `area_codes` VALUES ('49','04243',1,'Twistringen');
INSERT INTO `area_codes` VALUES ('49','04244',1,'Harpstedt');
INSERT INTO `area_codes` VALUES ('49','04245',1,'Neuenkirchen b Bassum');
INSERT INTO `area_codes` VALUES ('49','04246',1,'Twistringen-Heiligenloh');
INSERT INTO `area_codes` VALUES ('49','04247',1,'Affinghausen');
INSERT INTO `area_codes` VALUES ('49','04248',1,'Bassum-Neubruchhausen');
INSERT INTO `area_codes` VALUES ('49','04249',1,'Bassum-Nordwohlde');
INSERT INTO `area_codes` VALUES ('49','04251',1,'Hoya');
INSERT INTO `area_codes` VALUES ('49','04252',1,'Bruchhausen-Vilsen');
INSERT INTO `area_codes` VALUES ('49','04253',1,'Asendorf Kr Diepholz');
INSERT INTO `area_codes` VALUES ('49','04254',1,'Eystrup');
INSERT INTO `area_codes` VALUES ('49','04255',1,'Martfeld');
INSERT INTO `area_codes` VALUES ('49','04256',1,'Hilgermissen');
INSERT INTO `area_codes` VALUES ('49','04257',1,'Schweringen');
INSERT INTO `area_codes` VALUES ('49','04258',1,'Schwarme');
INSERT INTO `area_codes` VALUES ('49','04260',1,'Visselhövede-Wittorf');
INSERT INTO `area_codes` VALUES ('49','04261',1,'Rotenburg Wümme');
INSERT INTO `area_codes` VALUES ('49','04262',1,'Visselhövede');
INSERT INTO `area_codes` VALUES ('49','04263',1,'Scheessel');
INSERT INTO `area_codes` VALUES ('49','04264',1,'Sottrum Kr Rotenburg');
INSERT INTO `area_codes` VALUES ('49','04265',1,'Fintel');
INSERT INTO `area_codes` VALUES ('49','04266',1,'Brockel');
INSERT INTO `area_codes` VALUES ('49','04267',1,'Lauenbrück');
INSERT INTO `area_codes` VALUES ('49','04268',1,'Bötersen');
INSERT INTO `area_codes` VALUES ('49','04269',1,'Ahausen-Kirchwalsede');
INSERT INTO `area_codes` VALUES ('49','04271',1,'Sulingen');
INSERT INTO `area_codes` VALUES ('49','04272',1,'Siedenburg');
INSERT INTO `area_codes` VALUES ('49','04273',1,'Kirchdorf b Sulingen');
INSERT INTO `area_codes` VALUES ('49','04274',1,'Varrel b Sulingen');
INSERT INTO `area_codes` VALUES ('49','04275',1,'Ehrenburg');
INSERT INTO `area_codes` VALUES ('49','04276',1,'Borstel b Sulingen');
INSERT INTO `area_codes` VALUES ('49','04277',1,'Schwaförden');
INSERT INTO `area_codes` VALUES ('49','04281',1,'Zeven');
INSERT INTO `area_codes` VALUES ('49','04282',1,'Sittensen');
INSERT INTO `area_codes` VALUES ('49','04283',1,'Tarmstedt');
INSERT INTO `area_codes` VALUES ('49','04284',1,'Selsingen');
INSERT INTO `area_codes` VALUES ('49','04285',1,'Rhade b Zeven');
INSERT INTO `area_codes` VALUES ('49','04286',1,'Gyhum');
INSERT INTO `area_codes` VALUES ('49','04287',1,'Heeslingen-Boitzen');
INSERT INTO `area_codes` VALUES ('49','04288',1,'Horstedt Kr Rotenburg');
INSERT INTO `area_codes` VALUES ('49','04289',1,'Kirchtimke');
INSERT INTO `area_codes` VALUES ('49','04292',1,'Ritterhude');
INSERT INTO `area_codes` VALUES ('49','04293',1,'Ottersberg-Fischerhude');
INSERT INTO `area_codes` VALUES ('49','04294',1,'Riede Kr Verden');
INSERT INTO `area_codes` VALUES ('49','04295',1,'Emtinghausen');
INSERT INTO `area_codes` VALUES ('49','04296',1,'Schwanewede-Aschwarden');
INSERT INTO `area_codes` VALUES ('49','04297',1,'Ottersberg-Posthausen');
INSERT INTO `area_codes` VALUES ('49','04298',1,'Lilienthal');
INSERT INTO `area_codes` VALUES ('49','04302',1,'Kirchbarkau');
INSERT INTO `area_codes` VALUES ('49','04303',1,'Schlesen');
INSERT INTO `area_codes` VALUES ('49','04305',1,'Westensee');
INSERT INTO `area_codes` VALUES ('49','04307',1,'Raisdorf');
INSERT INTO `area_codes` VALUES ('49','04308',1,'Schwedeneck');
INSERT INTO `area_codes` VALUES ('49','0431',1,'Kiel');
INSERT INTO `area_codes` VALUES ('49','04320',1,'Heidmühlen');
INSERT INTO `area_codes` VALUES ('49','04321',1,'Neumünster');
INSERT INTO `area_codes` VALUES ('49','04322',1,'Bordesholm');
INSERT INTO `area_codes` VALUES ('49','04323',1,'Bornhöved');
INSERT INTO `area_codes` VALUES ('49','04324',1,'Brokstedt');
INSERT INTO `area_codes` VALUES ('49','04326',1,'Wankendorf');
INSERT INTO `area_codes` VALUES ('49','04327',1,'Grossenaspe');
INSERT INTO `area_codes` VALUES ('49','04328',1,'Rickling');
INSERT INTO `area_codes` VALUES ('49','04329',1,'Langwedel Holst');
INSERT INTO `area_codes` VALUES ('49','04330',1,'Emkendorf');
INSERT INTO `area_codes` VALUES ('49','04331',1,'Rendsburg');
INSERT INTO `area_codes` VALUES ('49','04332',1,'Hamdorf b Rendsburg');
INSERT INTO `area_codes` VALUES ('49','04333',1,'Erfde');
INSERT INTO `area_codes` VALUES ('49','04334',1,'Bredenbek b Rendsburg');
INSERT INTO `area_codes` VALUES ('49','04335',1,'Hohn b Rendsburg');
INSERT INTO `area_codes` VALUES ('49','04336',1,'Owschlag');
INSERT INTO `area_codes` VALUES ('49','04337',1,'Jevenstedt');
INSERT INTO `area_codes` VALUES ('49','04338',1,'Alt Duvenstedt');
INSERT INTO `area_codes` VALUES ('49','04339',1,'Christiansholm');
INSERT INTO `area_codes` VALUES ('49','04340',1,'Achterwehr');
INSERT INTO `area_codes` VALUES ('49','04342',1,'Preetz Kr Plön');
INSERT INTO `area_codes` VALUES ('49','04343',1,'Laboe');
INSERT INTO `area_codes` VALUES ('49','04344',1,'Schönberg Holstein');
INSERT INTO `area_codes` VALUES ('49','04346',1,'Gettorf');
INSERT INTO `area_codes` VALUES ('49','04347',1,'Flintbek');
INSERT INTO `area_codes` VALUES ('49','04348',1,'Schönkirchen');
INSERT INTO `area_codes` VALUES ('49','04349',1,'Dänischenhagen');
INSERT INTO `area_codes` VALUES ('49','04351',1,'Eckernförde');
INSERT INTO `area_codes` VALUES ('49','04352',1,'Damp');
INSERT INTO `area_codes` VALUES ('49','04353',1,'Ascheffel');
INSERT INTO `area_codes` VALUES ('49','04354',1,'Fleckeby');
INSERT INTO `area_codes` VALUES ('49','04355',1,'Rieseby');
INSERT INTO `area_codes` VALUES ('49','04356',1,'Gross Wittensee');
INSERT INTO `area_codes` VALUES ('49','04357',1,'Sehestedt Eider');
INSERT INTO `area_codes` VALUES ('49','04358',1,'Loose b Eckernförde');
INSERT INTO `area_codes` VALUES ('49','04361',1,'Oldenburg in Holstein');
INSERT INTO `area_codes` VALUES ('49','04362',1,'Heiligenhafen');
INSERT INTO `area_codes` VALUES ('49','04363',1,'Lensahn');
INSERT INTO `area_codes` VALUES ('49','04364',1,'Dahme Kr Ostholstein');
INSERT INTO `area_codes` VALUES ('49','04365',1,'Heringsdorf Holst');
INSERT INTO `area_codes` VALUES ('49','04366',1,'Grömitz-Cismar');
INSERT INTO `area_codes` VALUES ('49','04367',1,'Grossenbrode');
INSERT INTO `area_codes` VALUES ('49','04371',1,'Burg auf Fehmarn');
INSERT INTO `area_codes` VALUES ('49','04372',1,'Westfehmarn');
INSERT INTO `area_codes` VALUES ('49','04381',1,'Lütjenburg');
INSERT INTO `area_codes` VALUES ('49','04382',1,'Wangels');
INSERT INTO `area_codes` VALUES ('49','04383',1,'Grebin');
INSERT INTO `area_codes` VALUES ('49','04384',1,'Selent');
INSERT INTO `area_codes` VALUES ('49','04385',1,'Hohenfelde b Kiel');
INSERT INTO `area_codes` VALUES ('49','04392',1,'Nortorf b Neumünster');
INSERT INTO `area_codes` VALUES ('49','04393',1,'Boostedt');
INSERT INTO `area_codes` VALUES ('49','04394',1,'Bokhorst');
INSERT INTO `area_codes` VALUES ('49','04401',1,'Brake Unterweser');
INSERT INTO `area_codes` VALUES ('49','04402',1,'Rastede');
INSERT INTO `area_codes` VALUES ('49','04403',1,'Bad Zwischenahn');
INSERT INTO `area_codes` VALUES ('49','04404',1,'Elsfleth');
INSERT INTO `area_codes` VALUES ('49','04405',1,'Edewecht');
INSERT INTO `area_codes` VALUES ('49','04406',1,'Berne');
INSERT INTO `area_codes` VALUES ('49','04407',1,'Wardenburg');
INSERT INTO `area_codes` VALUES ('49','04408',1,'Hude Oldenburg');
INSERT INTO `area_codes` VALUES ('49','04409',1,'Westerstede-Ocholt');
INSERT INTO `area_codes` VALUES ('49','0441',1,'Oldenburg (Oldb)');
INSERT INTO `area_codes` VALUES ('49','04421',1,'Wilhelmshaven');
INSERT INTO `area_codes` VALUES ('49','04422',1,'Sande Kr Friesl');
INSERT INTO `area_codes` VALUES ('49','04423',1,'Fedderwarden');
INSERT INTO `area_codes` VALUES ('49','04425',1,'Wangerland-Hooksiel');
INSERT INTO `area_codes` VALUES ('49','04426',1,'Wangerland-Horumersiel');
INSERT INTO `area_codes` VALUES ('49','04431',1,'Wildeshausen');
INSERT INTO `area_codes` VALUES ('49','04432',1,'Dötlingen-Brettorf');
INSERT INTO `area_codes` VALUES ('49','04433',1,'Dötlingen');
INSERT INTO `area_codes` VALUES ('49','04434',1,'Colnrade');
INSERT INTO `area_codes` VALUES ('49','04435',1,'Grossenkneten');
INSERT INTO `area_codes` VALUES ('49','04441',1,'Vechta');
INSERT INTO `area_codes` VALUES ('49','04442',1,'Lohne Oldenburg');
INSERT INTO `area_codes` VALUES ('49','04443',1,'Dinklage');
INSERT INTO `area_codes` VALUES ('49','04444',1,'Goldenstedt');
INSERT INTO `area_codes` VALUES ('49','04445',1,'Visbek Kr Vechta');
INSERT INTO `area_codes` VALUES ('49','04446',1,'Bakum Kr Vechta');
INSERT INTO `area_codes` VALUES ('49','04447',1,'Vechta-Langförden');
INSERT INTO `area_codes` VALUES ('49','04451',1,'Varel Jadebusen');
INSERT INTO `area_codes` VALUES ('49','04452',1,'Zetel-Neuenburg');
INSERT INTO `area_codes` VALUES ('49','04453',1,'Zetel');
INSERT INTO `area_codes` VALUES ('49','04454',1,'Jade');
INSERT INTO `area_codes` VALUES ('49','04455',1,'Jade-Schweiburg');
INSERT INTO `area_codes` VALUES ('49','04456',1,'Varel-Altjührden');
INSERT INTO `area_codes` VALUES ('49','04458',1,'Wiefelstede-Spohle');
INSERT INTO `area_codes` VALUES ('49','04461',1,'Jever');
INSERT INTO `area_codes` VALUES ('49','04462',1,'Wittmund');
INSERT INTO `area_codes` VALUES ('49','04463',1,'Wangerland');
INSERT INTO `area_codes` VALUES ('49','04464',1,'Wittmund-Carolinensiel');
INSERT INTO `area_codes` VALUES ('49','04465',1,'Friedeburg Ostfriesl');
INSERT INTO `area_codes` VALUES ('49','04466',1,'Wittmund-Ardorf');
INSERT INTO `area_codes` VALUES ('49','04467',1,'Wittmund-Funnix');
INSERT INTO `area_codes` VALUES ('49','04468',1,'Friedeburg-Reepsholt');
INSERT INTO `area_codes` VALUES ('49','04469',1,'Wangerooge');
INSERT INTO `area_codes` VALUES ('49','04471',1,'Cloppenburg');
INSERT INTO `area_codes` VALUES ('49','04472',1,'Lastrup');
INSERT INTO `area_codes` VALUES ('49','04473',1,'Emstek');
INSERT INTO `area_codes` VALUES ('49','04474',1,'Garrel');
INSERT INTO `area_codes` VALUES ('49','04475',1,'Molbergen');
INSERT INTO `area_codes` VALUES ('49','04477',1,'Lastrup-Hemmelte');
INSERT INTO `area_codes` VALUES ('49','04478',1,'Cappeln Oldenburg');
INSERT INTO `area_codes` VALUES ('49','04479',1,'Molbergen-Peheim');
INSERT INTO `area_codes` VALUES ('49','04480',1,'Ovelgönne-Strückhausen');
INSERT INTO `area_codes` VALUES ('49','04481',1,'Hatten-Sandkrug');
INSERT INTO `area_codes` VALUES ('49','04482',1,'Hatten');
INSERT INTO `area_codes` VALUES ('49','04483',1,'Ovelgönne-Großenmeer');
INSERT INTO `area_codes` VALUES ('49','04484',1,'Hude-Wüsting');
INSERT INTO `area_codes` VALUES ('49','04485',1,'Elsfleth-Huntorf');
INSERT INTO `area_codes` VALUES ('49','04486',1,'Edewecht-Friedrichsfehn');
INSERT INTO `area_codes` VALUES ('49','04487',1,'Grossenkneten-Huntlosen');
INSERT INTO `area_codes` VALUES ('49','04488',1,'Westerstede');
INSERT INTO `area_codes` VALUES ('49','04489',1,'Apen');
INSERT INTO `area_codes` VALUES ('49','04491',1,'Friesoythe');
INSERT INTO `area_codes` VALUES ('49','04492',1,'Saterland');
INSERT INTO `area_codes` VALUES ('49','04493',1,'Friesoythe-Gehlenberg');
INSERT INTO `area_codes` VALUES ('49','04494',1,'Bösel Oldenburg');
INSERT INTO `area_codes` VALUES ('49','04495',1,'Friesoythe-Thüle');
INSERT INTO `area_codes` VALUES ('49','04496',1,'Friesoythe-Markhausen');
INSERT INTO `area_codes` VALUES ('49','04497',1,'Barßel-Harkebrügge');
INSERT INTO `area_codes` VALUES ('49','04498',1,'Saterland-Ramsloh');
INSERT INTO `area_codes` VALUES ('49','04499',1,'Barssel');
INSERT INTO `area_codes` VALUES ('49','04501',1,'Kastorf Holst');
INSERT INTO `area_codes` VALUES ('49','04502',1,'Lübeck-Travemünde');
INSERT INTO `area_codes` VALUES ('49','04503',1,'Timmendorfer Strand');
INSERT INTO `area_codes` VALUES ('49','04504',1,'Ratekau');
INSERT INTO `area_codes` VALUES ('49','04505',1,'Stockelsdorf-Curau');
INSERT INTO `area_codes` VALUES ('49','04506',1,'Stockelsdorf-Krumbeck');
INSERT INTO `area_codes` VALUES ('49','04508',1,'Krummesse');
INSERT INTO `area_codes` VALUES ('49','04509',1,'Groß Grönau');
INSERT INTO `area_codes` VALUES ('49','0451',1,'Lübeck');
INSERT INTO `area_codes` VALUES ('49','04521',1,'Eutin');
INSERT INTO `area_codes` VALUES ('49','04522',1,'Plön');
INSERT INTO `area_codes` VALUES ('49','04523',1,'Malente');
INSERT INTO `area_codes` VALUES ('49','04524',1,'Scharbeutz-Pönitz');
INSERT INTO `area_codes` VALUES ('49','04525',1,'Ahrensbök');
INSERT INTO `area_codes` VALUES ('49','04526',1,'Ascheberg Holstein');
INSERT INTO `area_codes` VALUES ('49','04527',1,'Bosau');
INSERT INTO `area_codes` VALUES ('49','04528',1,'Schönwalde am Bungsberg');
INSERT INTO `area_codes` VALUES ('49','04529',1,'Süsel-Bujendorf');
INSERT INTO `area_codes` VALUES ('49','04531',1,'Bad Oldesloe');
INSERT INTO `area_codes` VALUES ('49','04532',1,'Bargteheide');
INSERT INTO `area_codes` VALUES ('49','04533',1,'Reinfeld Holstein');
INSERT INTO `area_codes` VALUES ('49','04534',1,'Steinburg Kr Storman');
INSERT INTO `area_codes` VALUES ('49','04535',1,'Nahe');
INSERT INTO `area_codes` VALUES ('49','04536',1,'Steinhorst Lauenb');
INSERT INTO `area_codes` VALUES ('49','04537',1,'Sülfeld Holst');
INSERT INTO `area_codes` VALUES ('49','04539',1,'Westerau');
INSERT INTO `area_codes` VALUES ('49','04541',1,'Ratzeburg');
INSERT INTO `area_codes` VALUES ('49','04542',1,'Mölln Lauenb');
INSERT INTO `area_codes` VALUES ('49','04543',1,'Nusse');
INSERT INTO `area_codes` VALUES ('49','04544',1,'Berkenthin');
INSERT INTO `area_codes` VALUES ('49','04545',1,'Seedorf Lauenb');
INSERT INTO `area_codes` VALUES ('49','04546',1,'Mustin Lauenburg');
INSERT INTO `area_codes` VALUES ('49','04547',1,'Gudow Lauenb');
INSERT INTO `area_codes` VALUES ('49','04550',1,'Bühnsdorf');
INSERT INTO `area_codes` VALUES ('49','04551',1,'Bad Segeberg');
INSERT INTO `area_codes` VALUES ('49','04552',1,'Leezen');
INSERT INTO `area_codes` VALUES ('49','04553',1,'Geschendorf');
INSERT INTO `area_codes` VALUES ('49','04554',1,'Wahlstedt');
INSERT INTO `area_codes` VALUES ('49','04555',1,'Seedorf b Bad Segeberg');
INSERT INTO `area_codes` VALUES ('49','04556',1,'Ahrensbök-Gnissau');
INSERT INTO `area_codes` VALUES ('49','04557',1,'Blunk');
INSERT INTO `area_codes` VALUES ('49','04558',1,'Todesfelde');
INSERT INTO `area_codes` VALUES ('49','04559',1,'Wensin');
INSERT INTO `area_codes` VALUES ('49','04561',1,'Neustadt in Holstein');
INSERT INTO `area_codes` VALUES ('49','04562',1,'Grömitz');
INSERT INTO `area_codes` VALUES ('49','04563',1,'Scharbeutz-Haffkrug');
INSERT INTO `area_codes` VALUES ('49','04564',1,'Schashagen');
INSERT INTO `area_codes` VALUES ('49','04602',1,'Freienwill');
INSERT INTO `area_codes` VALUES ('49','04603',1,'Havetoft');
INSERT INTO `area_codes` VALUES ('49','04604',1,'Grossenwiehe');
INSERT INTO `area_codes` VALUES ('49','04605',1,'Medelby');
INSERT INTO `area_codes` VALUES ('49','04606',1,'Wanderup');
INSERT INTO `area_codes` VALUES ('49','04607',1,'Janneby');
INSERT INTO `area_codes` VALUES ('49','04608',1,'Handewitt');
INSERT INTO `area_codes` VALUES ('49','04609',1,'Eggebek');
INSERT INTO `area_codes` VALUES ('49','0461',1,'Flensburg');
INSERT INTO `area_codes` VALUES ('49','04621',1,'Schleswig');
INSERT INTO `area_codes` VALUES ('49','04622',1,'Taarstedt');
INSERT INTO `area_codes` VALUES ('49','04623',1,'Böklund');
INSERT INTO `area_codes` VALUES ('49','04624',1,'Kropp');
INSERT INTO `area_codes` VALUES ('49','04625',1,'Jübek');
INSERT INTO `area_codes` VALUES ('49','04626',1,'Treia');
INSERT INTO `area_codes` VALUES ('49','04627',1,'Dörpstedt');
INSERT INTO `area_codes` VALUES ('49','04630',1,'Barderup');
INSERT INTO `area_codes` VALUES ('49','04631',1,'Glücksburg Ostsee');
INSERT INTO `area_codes` VALUES ('49','04632',1,'Steinbergkirche');
INSERT INTO `area_codes` VALUES ('49','04633',1,'Satrup');
INSERT INTO `area_codes` VALUES ('49','04634',1,'Husby');
INSERT INTO `area_codes` VALUES ('49','04635',1,'Sörup');
INSERT INTO `area_codes` VALUES ('49','04636',1,'Langballig');
INSERT INTO `area_codes` VALUES ('49','04637',1,'Sterup');
INSERT INTO `area_codes` VALUES ('49','04638',1,'Tarp');
INSERT INTO `area_codes` VALUES ('49','04639',1,'Schafflund');
INSERT INTO `area_codes` VALUES ('49','04641',1,'Süderbrarup');
INSERT INTO `area_codes` VALUES ('49','04642',1,'Kappeln Schlei');
INSERT INTO `area_codes` VALUES ('49','04643',1,'Gelting Angeln');
INSERT INTO `area_codes` VALUES ('49','04644',1,'Karby');
INSERT INTO `area_codes` VALUES ('49','04646',1,'Mohrkirch');
INSERT INTO `area_codes` VALUES ('49','04651',1,'Sylt');
INSERT INTO `area_codes` VALUES ('49','04661',1,'Niebüll');
INSERT INTO `area_codes` VALUES ('49','04662',1,'Leck');
INSERT INTO `area_codes` VALUES ('49','04663',1,'Süderlügum');
INSERT INTO `area_codes` VALUES ('49','04664',1,'Neukirchen b Niebüll');
INSERT INTO `area_codes` VALUES ('49','04665',1,'Emmelsbüll-Horsbüll');
INSERT INTO `area_codes` VALUES ('49','04666',1,'Ladelund');
INSERT INTO `area_codes` VALUES ('49','04667',1,'Dagebüll');
INSERT INTO `area_codes` VALUES ('49','04668',1,'Klanxbüll');
INSERT INTO `area_codes` VALUES ('49','04671',1,'Bredstedt');
INSERT INTO `area_codes` VALUES ('49','04672',1,'Langenhorn');
INSERT INTO `area_codes` VALUES ('49','04673',1,'Joldelund');
INSERT INTO `area_codes` VALUES ('49','04674',1,'Ockholm');
INSERT INTO `area_codes` VALUES ('49','04681',1,'Wyk auf Föhr');
INSERT INTO `area_codes` VALUES ('49','04682',1,'Amrum');
INSERT INTO `area_codes` VALUES ('49','04683',1,'Oldsum');
INSERT INTO `area_codes` VALUES ('49','04684',1,'Langeneß Hallig');
INSERT INTO `area_codes` VALUES ('49','04702',1,'Sandstedt');
INSERT INTO `area_codes` VALUES ('49','04703',1,'Loxstedt-Donnern');
INSERT INTO `area_codes` VALUES ('49','04704',1,'Drangstedt');
INSERT INTO `area_codes` VALUES ('49','04705',1,'Wremen');
INSERT INTO `area_codes` VALUES ('49','04706',1,'Schiffdorf');
INSERT INTO `area_codes` VALUES ('49','04707',1,'Langen-Neuenwalde');
INSERT INTO `area_codes` VALUES ('49','04708',1,'Ringstedt');
INSERT INTO `area_codes` VALUES ('49','0471',1,'Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04721',1,'Cuxhaven');
INSERT INTO `area_codes` VALUES ('49','04722',1,'Cuxhaven-Altenbruch');
INSERT INTO `area_codes` VALUES ('49','04723',1,'Cuxhaven-Altenwalde');
INSERT INTO `area_codes` VALUES ('49','04724',1,'Cuxhaven-Lüdingworth');
INSERT INTO `area_codes` VALUES ('49','04725',1,'Helgoland');
INSERT INTO `area_codes` VALUES ('49','04731',1,'Nordenham');
INSERT INTO `area_codes` VALUES ('49','04732',1,'Stadland-Rodenkirchen');
INSERT INTO `area_codes` VALUES ('49','04733',1,'Butjadingen-Burhave');
INSERT INTO `area_codes` VALUES ('49','04734',1,'Stadland-Seefeld');
INSERT INTO `area_codes` VALUES ('49','04735',1,'Butjadingen-Stollhamm');
INSERT INTO `area_codes` VALUES ('49','04736',1,'Butjadingen-Tossens');
INSERT INTO `area_codes` VALUES ('49','04737',1,'Stadland-Schwei');
INSERT INTO `area_codes` VALUES ('49','04740',1,'Loxstedt-Dedesdorf');
INSERT INTO `area_codes` VALUES ('49','04741',1,'Nordholz b Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04742',1,'Dorum');
INSERT INTO `area_codes` VALUES ('49','04743',1,'Langen b Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04744',1,'Loxstedt');
INSERT INTO `area_codes` VALUES ('49','04745',1,'Bad Bederkesa');
INSERT INTO `area_codes` VALUES ('49','04746',1,'Hagen b Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04747',1,'Beverstedt');
INSERT INTO `area_codes` VALUES ('49','04748',1,'Stubben b Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04749',1,'Schiffdorf-Geestenseth');
INSERT INTO `area_codes` VALUES ('49','04751',1,'Otterndorf');
INSERT INTO `area_codes` VALUES ('49','04752',1,'Neuhaus Oste');
INSERT INTO `area_codes` VALUES ('49','04753',1,'Balje');
INSERT INTO `area_codes` VALUES ('49','04754',1,'Bülkau');
INSERT INTO `area_codes` VALUES ('49','04755',1,'Ihlienworth');
INSERT INTO `area_codes` VALUES ('49','04756',1,'Odisheim');
INSERT INTO `area_codes` VALUES ('49','04757',1,'Wanna');
INSERT INTO `area_codes` VALUES ('49','04758',1,'Nordleda');
INSERT INTO `area_codes` VALUES ('49','04761',1,'Bremervörde');
INSERT INTO `area_codes` VALUES ('49','04762',1,'Kutenholz');
INSERT INTO `area_codes` VALUES ('49','04763',1,'Gnarrenburg');
INSERT INTO `area_codes` VALUES ('49','04764',1,'Gnarrenburg-Klenkendorf');
INSERT INTO `area_codes` VALUES ('49','04765',1,'Ebersdorf b Bremervörde');
INSERT INTO `area_codes` VALUES ('49','04766',1,'Basdahl');
INSERT INTO `area_codes` VALUES ('49','04767',1,'Bremervörde-Bevern');
INSERT INTO `area_codes` VALUES ('49','04768',1,'Hipstedt');
INSERT INTO `area_codes` VALUES ('49','04769',1,'Bremervörde-Iselersheim');
INSERT INTO `area_codes` VALUES ('49','04770',1,'Wischhafen');
INSERT INTO `area_codes` VALUES ('49','04771',1,'Hemmoor');
INSERT INTO `area_codes` VALUES ('49','04772',1,'Oberndorf Oste');
INSERT INTO `area_codes` VALUES ('49','04773',1,'Lamstedt');
INSERT INTO `area_codes` VALUES ('49','04774',1,'Hechthausen');
INSERT INTO `area_codes` VALUES ('49','04775',1,'Grossenwörden');
INSERT INTO `area_codes` VALUES ('49','04776',1,'Osten-Altendorf');
INSERT INTO `area_codes` VALUES ('49','04777',1,'Cadenberge');
INSERT INTO `area_codes` VALUES ('49','04778',1,'Wingst');
INSERT INTO `area_codes` VALUES ('49','04779',1,'Freiburg Elbe');
INSERT INTO `area_codes` VALUES ('49','04791',1,'Osterholz-Scharmbeck');
INSERT INTO `area_codes` VALUES ('49','04792',1,'Worpswede');
INSERT INTO `area_codes` VALUES ('49','04793',1,'Hambergen');
INSERT INTO `area_codes` VALUES ('49','04794',1,'Worpswede-Ostersode');
INSERT INTO `area_codes` VALUES ('49','04795',1,'Garlstedt');
INSERT INTO `area_codes` VALUES ('49','04796',1,'Teufelsmoor');
INSERT INTO `area_codes` VALUES ('49','04802',1,'Wrohm');
INSERT INTO `area_codes` VALUES ('49','04803',1,'Pahlen');
INSERT INTO `area_codes` VALUES ('49','04804',1,'Nordhastedt');
INSERT INTO `area_codes` VALUES ('49','04805',1,'Schafstedt');
INSERT INTO `area_codes` VALUES ('49','04806',1,'Sarzbüttel');
INSERT INTO `area_codes` VALUES ('49','0481',1,'Heide Holst');
INSERT INTO `area_codes` VALUES ('49','04821',1,'Itzehoe');
INSERT INTO `area_codes` VALUES ('49','04822',1,'Kellinghusen');
INSERT INTO `area_codes` VALUES ('49','04823',1,'Wilster');
INSERT INTO `area_codes` VALUES ('49','04824',1,'Krempe');
INSERT INTO `area_codes` VALUES ('49','04825',1,'Burg Dithmarschen');
INSERT INTO `area_codes` VALUES ('49','04826',1,'Hohenlockstedt');
INSERT INTO `area_codes` VALUES ('49','04827',1,'Wacken');
INSERT INTO `area_codes` VALUES ('49','04828',1,'Lägerdorf');
INSERT INTO `area_codes` VALUES ('49','04829',1,'Wewelsfleth');
INSERT INTO `area_codes` VALUES ('49','04830',1,'Süderhastedt');
INSERT INTO `area_codes` VALUES ('49','04832',1,'Meldorf');
INSERT INTO `area_codes` VALUES ('49','04833',1,'Wesselburen');
INSERT INTO `area_codes` VALUES ('49','04834',1,'Büsum');
INSERT INTO `area_codes` VALUES ('49','04835',1,'Albersdorf Holst');
INSERT INTO `area_codes` VALUES ('49','04836',1,'Hennstedt Dithm');
INSERT INTO `area_codes` VALUES ('49','04837',1,'Neuenkirchen Dithm');
INSERT INTO `area_codes` VALUES ('49','04838',1,'Tellingstedt');
INSERT INTO `area_codes` VALUES ('49','04839',1,'Wöhrden Dithm');
INSERT INTO `area_codes` VALUES ('49','04841',1,'Husum Nordsee');
INSERT INTO `area_codes` VALUES ('49','04842',1,'Nordstrand');
INSERT INTO `area_codes` VALUES ('49','04843',1,'Viöl');
INSERT INTO `area_codes` VALUES ('49','04844',1,'Pellworm');
INSERT INTO `area_codes` VALUES ('49','04845',1,'Ostenfeld Husum');
INSERT INTO `area_codes` VALUES ('49','04846',1,'Hattstedt');
INSERT INTO `area_codes` VALUES ('49','04847',1,'Oster-Ohrstedt');
INSERT INTO `area_codes` VALUES ('49','04848',1,'Rantrum');
INSERT INTO `area_codes` VALUES ('49','04849',1,'Hooge');
INSERT INTO `area_codes` VALUES ('49','04851',1,'Marne');
INSERT INTO `area_codes` VALUES ('49','04852',1,'Brunsbüttel');
INSERT INTO `area_codes` VALUES ('49','04853',1,'Sankt Michaelisdonn');
INSERT INTO `area_codes` VALUES ('49','04854',1,'Friedrichskoog');
INSERT INTO `area_codes` VALUES ('49','04855',1,'Eddelak');
INSERT INTO `area_codes` VALUES ('49','04856',1,'Kronprinzenkoog');
INSERT INTO `area_codes` VALUES ('49','04857',1,'Barlt');
INSERT INTO `area_codes` VALUES ('49','04858',1,'Sankt Margarethen Holst');
INSERT INTO `area_codes` VALUES ('49','04859',1,'Windbergen');
INSERT INTO `area_codes` VALUES ('49','04861',1,'Tönning');
INSERT INTO `area_codes` VALUES ('49','04862',1,'Garding');
INSERT INTO `area_codes` VALUES ('49','04863',1,'Sankt Peter-Ording');
INSERT INTO `area_codes` VALUES ('49','04864',1,'Oldenswort');
INSERT INTO `area_codes` VALUES ('49','04865',1,'Osterhever');
INSERT INTO `area_codes` VALUES ('49','04871',1,'Hohenwestedt');
INSERT INTO `area_codes` VALUES ('49','04872',1,'Hanerau-Hademarschen');
INSERT INTO `area_codes` VALUES ('49','04873',1,'Aukrug');
INSERT INTO `area_codes` VALUES ('49','04874',1,'Todenbüttel');
INSERT INTO `area_codes` VALUES ('49','04875',1,'Stafstedt');
INSERT INTO `area_codes` VALUES ('49','04876',1,'Reher Holst');
INSERT INTO `area_codes` VALUES ('49','04877',1,'Hennstedt b Itzehoe');
INSERT INTO `area_codes` VALUES ('49','04881',1,'Friedrichstadt');
INSERT INTO `area_codes` VALUES ('49','04882',1,'Lunden');
INSERT INTO `area_codes` VALUES ('49','04883',1,'Süderstapel');
INSERT INTO `area_codes` VALUES ('49','04884',1,'Schwabstedt');
INSERT INTO `area_codes` VALUES ('49','04885',1,'Bergenhusen');
INSERT INTO `area_codes` VALUES ('49','04892',1,'Schenefeld Mittelholst');
INSERT INTO `area_codes` VALUES ('49','04893',1,'Hohenaspe');
INSERT INTO `area_codes` VALUES ('49','04902',1,'Jemgum-Ditzum');
INSERT INTO `area_codes` VALUES ('49','04903',1,'Wymeer');
INSERT INTO `area_codes` VALUES ('49','0491',1,'Leer Ostfriesland');
INSERT INTO `area_codes` VALUES ('49','04920',1,'Wirdum');
INSERT INTO `area_codes` VALUES ('49','04921',1,'Emden Stadt');
INSERT INTO `area_codes` VALUES ('49','04922',1,'Borkum');
INSERT INTO `area_codes` VALUES ('49','04923',1,'Krummhörn-Pewsum');
INSERT INTO `area_codes` VALUES ('49','04924',1,'Moormerland-Oldersum');
INSERT INTO `area_codes` VALUES ('49','04925',1,'Hinte');
INSERT INTO `area_codes` VALUES ('49','04926',1,'Krummhörn-Greetsiel');
INSERT INTO `area_codes` VALUES ('49','04927',1,'Krummhörn-Loquard');
INSERT INTO `area_codes` VALUES ('49','04928',1,'Ihlow-Riepe');
INSERT INTO `area_codes` VALUES ('49','04929',1,'Ihlow Kr Aurich');
INSERT INTO `area_codes` VALUES ('49','04931',1,'Norden');
INSERT INTO `area_codes` VALUES ('49','04932',1,'Norderney');
INSERT INTO `area_codes` VALUES ('49','04933',1,'Dornum Ostfriesl');
INSERT INTO `area_codes` VALUES ('49','04934',1,'Marienhafe');
INSERT INTO `area_codes` VALUES ('49','04935',1,'Juist');
INSERT INTO `area_codes` VALUES ('49','04936',1,'Grossheide');
INSERT INTO `area_codes` VALUES ('49','04938',1,'Hagermarsch');
INSERT INTO `area_codes` VALUES ('49','04939',1,'Baltrum');
INSERT INTO `area_codes` VALUES ('49','04941',1,'Aurich');
INSERT INTO `area_codes` VALUES ('49','04942',1,'Südbrookmerland');
INSERT INTO `area_codes` VALUES ('49','04943',1,'Grossefehn');
INSERT INTO `area_codes` VALUES ('49','04944',1,'Wiesmoor');
INSERT INTO `area_codes` VALUES ('49','04945',1,'Grossefehn-Timmel');
INSERT INTO `area_codes` VALUES ('49','04946',1,'Grossefehn-Bagband');
INSERT INTO `area_codes` VALUES ('49','04947',1,'Aurich-Ogenbargen');
INSERT INTO `area_codes` VALUES ('49','04948',1,'Wiesmoor-Marcardsmoor');
INSERT INTO `area_codes` VALUES ('49','04950',1,'Holtland');
INSERT INTO `area_codes` VALUES ('49','04951',1,'Weener');
INSERT INTO `area_codes` VALUES ('49','04952',1,'Rhauderfehn');
INSERT INTO `area_codes` VALUES ('49','04953',1,'Bunde');
INSERT INTO `area_codes` VALUES ('49','04954',1,'Moormerland');
INSERT INTO `area_codes` VALUES ('49','04955',1,'Westoverledingen');
INSERT INTO `area_codes` VALUES ('49','04956',1,'Uplengen');
INSERT INTO `area_codes` VALUES ('49','04957',1,'Detern');
INSERT INTO `area_codes` VALUES ('49','04958',1,'Jemgum');
INSERT INTO `area_codes` VALUES ('49','04959',1,'Dollart');
INSERT INTO `area_codes` VALUES ('49','04961',1,'Papenburg');
INSERT INTO `area_codes` VALUES ('49','04962',1,'Papenburg-Aschendorf');
INSERT INTO `area_codes` VALUES ('49','04963',1,'Dörpen');
INSERT INTO `area_codes` VALUES ('49','04964',1,'Rhede Ems');
INSERT INTO `area_codes` VALUES ('49','04965',1,'Surwold');
INSERT INTO `area_codes` VALUES ('49','04966',1,'Neubörger');
INSERT INTO `area_codes` VALUES ('49','04967',1,'Rhauderfehn-Burlage');
INSERT INTO `area_codes` VALUES ('49','04968',1,'Neulehe');
INSERT INTO `area_codes` VALUES ('49','04971',1,'Esens');
INSERT INTO `area_codes` VALUES ('49','04972',1,'Langeoog');
INSERT INTO `area_codes` VALUES ('49','04973',1,'Wittmund-Burhafe');
INSERT INTO `area_codes` VALUES ('49','04974',1,'Neuharlingersiel');
INSERT INTO `area_codes` VALUES ('49','04975',1,'Westerholt Ostfriesl');
INSERT INTO `area_codes` VALUES ('49','04976',1,'Spiekeroog');
INSERT INTO `area_codes` VALUES ('49','04977',1,'Blomberg Ostfriesl');
INSERT INTO `area_codes` VALUES ('49','0500',0,'(Reserve für Telekommunikationsdienste)');
INSERT INTO `area_codes` VALUES ('49','0501',0,'(Reserve für Telekommunikationsdienste)');
INSERT INTO `area_codes` VALUES ('49','05021',1,'Nienburg Weser');
INSERT INTO `area_codes` VALUES ('49','05022',1,'Wietzen');
INSERT INTO `area_codes` VALUES ('49','05023',1,'Liebenau Kr Nienburg Weser');
INSERT INTO `area_codes` VALUES ('49','05024',1,'Rohrsen Kr Nienburg Weser');
INSERT INTO `area_codes` VALUES ('49','05025',1,'Estorf Weser');
INSERT INTO `area_codes` VALUES ('49','05026',1,'Steimbke');
INSERT INTO `area_codes` VALUES ('49','05027',1,'Linsburg');
INSERT INTO `area_codes` VALUES ('49','05028',1,'Pennigsehl');
INSERT INTO `area_codes` VALUES ('49','05031',1,'Wunstorf');
INSERT INTO `area_codes` VALUES ('49','05032',1,'Neustadt am Rübenberge');
INSERT INTO `area_codes` VALUES ('49','05033',1,'Wunstorf-Grossenheidorn');
INSERT INTO `area_codes` VALUES ('49','05034',1,'Neustadt-Hagen');
INSERT INTO `area_codes` VALUES ('49','05035',1,'Gross Munzel');
INSERT INTO `area_codes` VALUES ('49','05036',1,'Neustadt-Schneeren');
INSERT INTO `area_codes` VALUES ('49','05037',1,'Bad Rehburg');
INSERT INTO `area_codes` VALUES ('49','05041',1,'Springe Deister');
INSERT INTO `area_codes` VALUES ('49','05042',1,'Bad Münder am Deister');
INSERT INTO `area_codes` VALUES ('49','05043',1,'Lauenau');
INSERT INTO `area_codes` VALUES ('49','05044',1,'Springe-Eldagsen');
INSERT INTO `area_codes` VALUES ('49','05045',1,'Springe-Bennigsen');
INSERT INTO `area_codes` VALUES ('49','05051',1,'Bergen Kr Celle');
INSERT INTO `area_codes` VALUES ('49','05052',1,'Hermannsburg');
INSERT INTO `area_codes` VALUES ('49','05053',1,'Faßberg-Müden');
INSERT INTO `area_codes` VALUES ('49','05054',1,'Bergen-Sülze');
INSERT INTO `area_codes` VALUES ('49','05055',1,'Fassberg');
INSERT INTO `area_codes` VALUES ('49','05056',1,'Winsen-Meissendorf');
INSERT INTO `area_codes` VALUES ('49','05060',1,'Bodenburg');
INSERT INTO `area_codes` VALUES ('49','05062',1,'Holle b Hildesheim');
INSERT INTO `area_codes` VALUES ('49','05063',1,'Bad Salzdetfurth');
INSERT INTO `area_codes` VALUES ('49','05064',1,'Groß Düngen');
INSERT INTO `area_codes` VALUES ('49','05065',1,'Sibbesse');
INSERT INTO `area_codes` VALUES ('49','05066',1,'Sarstedt');
INSERT INTO `area_codes` VALUES ('49','05067',1,'Bockenem');
INSERT INTO `area_codes` VALUES ('49','05068',1,'Elze Leine');
INSERT INTO `area_codes` VALUES ('49','05069',1,'Nordstemmen');
INSERT INTO `area_codes` VALUES ('49','05071',1,'Schwarmstedt');
INSERT INTO `area_codes` VALUES ('49','05072',1,'Neustadt-Mandelsloh');
INSERT INTO `area_codes` VALUES ('49','05073',1,'Neustadt-Esperke');
INSERT INTO `area_codes` VALUES ('49','05074',1,'Rodewald');
INSERT INTO `area_codes` VALUES ('49','05082',1,'Langlingen');
INSERT INTO `area_codes` VALUES ('49','05083',1,'Hohne b Celle');
INSERT INTO `area_codes` VALUES ('49','05084',1,'Hambühren');
INSERT INTO `area_codes` VALUES ('49','05085',1,'Burgdorf-Ehlershausen');
INSERT INTO `area_codes` VALUES ('49','05086',1,'Celle-Scheuen');
INSERT INTO `area_codes` VALUES ('49','05101',1,'Pattensen');
INSERT INTO `area_codes` VALUES ('49','05102',1,'Laatzen');
INSERT INTO `area_codes` VALUES ('49','05103',1,'Wennigsen Deister');
INSERT INTO `area_codes` VALUES ('49','05105',1,'Barsinghausen');
INSERT INTO `area_codes` VALUES ('49','05108',1,'Gehrden Han');
INSERT INTO `area_codes` VALUES ('49','05109',1,'Ronnenberg');
INSERT INTO `area_codes` VALUES ('49','0511',1,'Hannover');
INSERT INTO `area_codes` VALUES ('49','05121',1,'Hildesheim');
INSERT INTO `area_codes` VALUES ('49','05123',1,'Schellerten');
INSERT INTO `area_codes` VALUES ('49','05126',1,'Algermissen');
INSERT INTO `area_codes` VALUES ('49','05127',1,'Harsum');
INSERT INTO `area_codes` VALUES ('49','05128',1,'Hohenhameln');
INSERT INTO `area_codes` VALUES ('49','05129',1,'Söhlde');
INSERT INTO `area_codes` VALUES ('49','05130',1,'Wedemark');
INSERT INTO `area_codes` VALUES ('49','05131',1,'Garbsen');
INSERT INTO `area_codes` VALUES ('49','05132',1,'Lehrte');
INSERT INTO `area_codes` VALUES ('49','05135',1,'Burgwedel-Fuhrberg');
INSERT INTO `area_codes` VALUES ('49','05136',1,'Burgdorf Kr Hannover');
INSERT INTO `area_codes` VALUES ('49','05137',1,'Seelze');
INSERT INTO `area_codes` VALUES ('49','05138',1,'Sehnde');
INSERT INTO `area_codes` VALUES ('49','05139',1,'Burgwedel');
INSERT INTO `area_codes` VALUES ('49','05141',1,'Celle');
INSERT INTO `area_codes` VALUES ('49','05142',1,'Eschede');
INSERT INTO `area_codes` VALUES ('49','05143',1,'Winsen Aller');
INSERT INTO `area_codes` VALUES ('49','05144',1,'Wathlingen');
INSERT INTO `area_codes` VALUES ('49','05145',1,'Beedenbostel');
INSERT INTO `area_codes` VALUES ('49','05146',1,'Wietze');
INSERT INTO `area_codes` VALUES ('49','05147',1,'Uetze-Hänigsen');
INSERT INTO `area_codes` VALUES ('49','05148',1,'Steinhorst Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05149',1,'Wienhausen');
INSERT INTO `area_codes` VALUES ('49','05151',1,'Hameln');
INSERT INTO `area_codes` VALUES ('49','05152',1,'Hessisch Oldendorf');
INSERT INTO `area_codes` VALUES ('49','05153',1,'Salzhemmendorf');
INSERT INTO `area_codes` VALUES ('49','05154',1,'Aerzen');
INSERT INTO `area_codes` VALUES ('49','05155',1,'Emmerthal');
INSERT INTO `area_codes` VALUES ('49','05156',1,'Coppenbrügge');
INSERT INTO `area_codes` VALUES ('49','05157',1,'Emmerthal-Börry');
INSERT INTO `area_codes` VALUES ('49','05158',1,'Hemeringen');
INSERT INTO `area_codes` VALUES ('49','05159',1,'Coppenbrügge-Bisperode');
INSERT INTO `area_codes` VALUES ('49','05161',1,'Walsrode');
INSERT INTO `area_codes` VALUES ('49','05162',1,'Fallingbostel');
INSERT INTO `area_codes` VALUES ('49','05163',1,'Fallingbostel-Dorfmark');
INSERT INTO `area_codes` VALUES ('49','05164',1,'Hodenhagen');
INSERT INTO `area_codes` VALUES ('49','05165',1,'Rethem  Aller');
INSERT INTO `area_codes` VALUES ('49','05166',1,'Walsrode-Kirchboitzen');
INSERT INTO `area_codes` VALUES ('49','05167',1,'Walsrode-Westenholz');
INSERT INTO `area_codes` VALUES ('49','05168',1,'Walsrode-Stellichte');
INSERT INTO `area_codes` VALUES ('49','05171',1,'Peine');
INSERT INTO `area_codes` VALUES ('49','05172',1,'Ilsede');
INSERT INTO `area_codes` VALUES ('49','05173',1,'Uetze');
INSERT INTO `area_codes` VALUES ('49','05174',1,'Lahstedt');
INSERT INTO `area_codes` VALUES ('49','05175',1,'Lehrte-Arpke');
INSERT INTO `area_codes` VALUES ('49','05176',1,'Edemissen');
INSERT INTO `area_codes` VALUES ('49','05177',1,'Edemissen-Abbensen');
INSERT INTO `area_codes` VALUES ('49','05181',1,'Alfeld Leine');
INSERT INTO `area_codes` VALUES ('49','05182',1,'Gronau  Leine');
INSERT INTO `area_codes` VALUES ('49','05183',1,'Lamspringe');
INSERT INTO `area_codes` VALUES ('49','05184',1,'Freden Leine');
INSERT INTO `area_codes` VALUES ('49','05185',1,'Duingen');
INSERT INTO `area_codes` VALUES ('49','05186',1,'Salzhemmendorf-Wallensen');
INSERT INTO `area_codes` VALUES ('49','05187',1,'Delligsen');
INSERT INTO `area_codes` VALUES ('49','05190',1,'Soltau-Emmingen');
INSERT INTO `area_codes` VALUES ('49','05191',1,'Soltau');
INSERT INTO `area_codes` VALUES ('49','05192',1,'Munster');
INSERT INTO `area_codes` VALUES ('49','05193',1,'Schneverdingen');
INSERT INTO `area_codes` VALUES ('49','05194',1,'Bispingen');
INSERT INTO `area_codes` VALUES ('49','05195',1,'Neuenkirchen b Soltau');
INSERT INTO `area_codes` VALUES ('49','05196',1,'Wietzendorf');
INSERT INTO `area_codes` VALUES ('49','05197',1,'Soltau-Frielingen');
INSERT INTO `area_codes` VALUES ('49','05198',1,'Schneverdingen-Wintermoor');
INSERT INTO `area_codes` VALUES ('49','05199',1,'Schneverdingen-Heber');
INSERT INTO `area_codes` VALUES ('49','05201',1,'Halle Westf');
INSERT INTO `area_codes` VALUES ('49','05202',1,'Oerlinghausen');
INSERT INTO `area_codes` VALUES ('49','05203',1,'Werther Westf');
INSERT INTO `area_codes` VALUES ('49','05204',1,'Steinhagen  Westf');
INSERT INTO `area_codes` VALUES ('49','05205',1,'Bielefeld-Sennestadt');
INSERT INTO `area_codes` VALUES ('49','05206',1,'Bielefeld-Jöllenbeck');
INSERT INTO `area_codes` VALUES ('49','05207',1,'Schloss Holte-Stukenbrock');
INSERT INTO `area_codes` VALUES ('49','05208',1,'Leopoldshöhe');
INSERT INTO `area_codes` VALUES ('49','05209',1,'Gütersloh-Friedrichsdorf');
INSERT INTO `area_codes` VALUES ('49','0521',1,'Bielefeld');
INSERT INTO `area_codes` VALUES ('49','05221',1,'Herford');
INSERT INTO `area_codes` VALUES ('49','05222',1,'Bad Salzuflen');
INSERT INTO `area_codes` VALUES ('49','05223',1,'Bünde');
INSERT INTO `area_codes` VALUES ('49','05224',1,'Enger Westf');
INSERT INTO `area_codes` VALUES ('49','05225',1,'Spenge');
INSERT INTO `area_codes` VALUES ('49','05226',1,'Bruchmühlen Westf');
INSERT INTO `area_codes` VALUES ('49','05228',1,'Vlotho-Exter');
INSERT INTO `area_codes` VALUES ('49','05231',1,'Detmold');
INSERT INTO `area_codes` VALUES ('49','05232',1,'Lage Lippe');
INSERT INTO `area_codes` VALUES ('49','05233',1,'Steinheim Westf');
INSERT INTO `area_codes` VALUES ('49','05234',1,'Horn-Bad Meinberg');
INSERT INTO `area_codes` VALUES ('49','05235',1,'Blomberg Lippe');
INSERT INTO `area_codes` VALUES ('49','05236',1,'Blomberg-Grossenmarpe');
INSERT INTO `area_codes` VALUES ('49','05237',1,'Augustdorf');
INSERT INTO `area_codes` VALUES ('49','05238',1,'Nieheim-Himmighausen');
INSERT INTO `area_codes` VALUES ('49','05241',1,'Gütersloh');
INSERT INTO `area_codes` VALUES ('49','05242',1,'Rheda-Wiedenbrück');
INSERT INTO `area_codes` VALUES ('49','05244',1,'Rietberg');
INSERT INTO `area_codes` VALUES ('49','05245',1,'Herzebrock-Clarholz');
INSERT INTO `area_codes` VALUES ('49','05246',1,'Verl');
INSERT INTO `area_codes` VALUES ('49','05247',1,'Harsewinkel');
INSERT INTO `area_codes` VALUES ('49','05248',1,'Langenberg Kr Gütersloh');
INSERT INTO `area_codes` VALUES ('49','05250',1,'Delbrück Westf');
INSERT INTO `area_codes` VALUES ('49','05251',1,'Paderborn');
INSERT INTO `area_codes` VALUES ('49','05252',1,'Bad Lippspringe');
INSERT INTO `area_codes` VALUES ('49','05253',1,'Bad Driburg');
INSERT INTO `area_codes` VALUES ('49','05254',1,'Paderborn-Schloss Neuhaus');
INSERT INTO `area_codes` VALUES ('49','05255',1,'Altenbeken');
INSERT INTO `area_codes` VALUES ('49','05257',1,'Hövelhof');
INSERT INTO `area_codes` VALUES ('49','05258',1,'Salzkotten');
INSERT INTO `area_codes` VALUES ('49','05259',1,'Bad Driburg-Neuenheerse');
INSERT INTO `area_codes` VALUES ('49','05261',1,'Lemgo');
INSERT INTO `area_codes` VALUES ('49','05262',1,'Extertal');
INSERT INTO `area_codes` VALUES ('49','05263',1,'Barntrup');
INSERT INTO `area_codes` VALUES ('49','05264',1,'Kalletal');
INSERT INTO `area_codes` VALUES ('49','05265',1,'Dörentrup');
INSERT INTO `area_codes` VALUES ('49','05266',1,'Lemgo-Kirchheide');
INSERT INTO `area_codes` VALUES ('49','05271',1,'Höxter');
INSERT INTO `area_codes` VALUES ('49','05272',1,'Brakel Westf');
INSERT INTO `area_codes` VALUES ('49','05273',1,'Beverungen');
INSERT INTO `area_codes` VALUES ('49','05274',1,'Nieheim');
INSERT INTO `area_codes` VALUES ('49','05275',1,'Höxter-Ottbergen');
INSERT INTO `area_codes` VALUES ('49','05276',1,'Marienmünster');
INSERT INTO `area_codes` VALUES ('49','05277',1,'Höxter-Fürstenau');
INSERT INTO `area_codes` VALUES ('49','05278',1,'Höxter-Ovenhausen');
INSERT INTO `area_codes` VALUES ('49','05281',1,'Bad Pyrmont');
INSERT INTO `area_codes` VALUES ('49','05282',1,'Schieder-Schwalenberg');
INSERT INTO `area_codes` VALUES ('49','05283',1,'Lügde-Rischenau');
INSERT INTO `area_codes` VALUES ('49','05284',1,'Schwalenberg');
INSERT INTO `area_codes` VALUES ('49','05285',1,'Bad Pyrmont-Kleinenberg');
INSERT INTO `area_codes` VALUES ('49','05286',1,'Ottenstein Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05292',1,'Lichtenau-Atteln');
INSERT INTO `area_codes` VALUES ('49','05293',1,'Paderborn-Dahl');
INSERT INTO `area_codes` VALUES ('49','05294',1,'Hövelhof-Espeln');
INSERT INTO `area_codes` VALUES ('49','05295',1,'Lichtenau Westf');
INSERT INTO `area_codes` VALUES ('49','05300',1,'Salzgitter-Üfingen');
INSERT INTO `area_codes` VALUES ('49','05301',1,'Lehre-Essenrode');
INSERT INTO `area_codes` VALUES ('49','05302',1,'Vechelde');
INSERT INTO `area_codes` VALUES ('49','05303',1,'Wendeburg');
INSERT INTO `area_codes` VALUES ('49','05304',1,'Meine');
INSERT INTO `area_codes` VALUES ('49','05305',1,'Sickte');
INSERT INTO `area_codes` VALUES ('49','05306',1,'Cremlingen');
INSERT INTO `area_codes` VALUES ('49','05307',1,'Braunschweig-Wenden');
INSERT INTO `area_codes` VALUES ('49','05308',1,'Lehre');
INSERT INTO `area_codes` VALUES ('49','05309',1,'Lehre-Wendhausen');
INSERT INTO `area_codes` VALUES ('49','0531',1,'Braunschweig');
INSERT INTO `area_codes` VALUES ('49','05320',1,'Torfhaus');
INSERT INTO `area_codes` VALUES ('49','05321',1,'Goslar');
INSERT INTO `area_codes` VALUES ('49','05322',1,'Bad Harzburg');
INSERT INTO `area_codes` VALUES ('49','05323',1,'Clausthal-Zellerfeld');
INSERT INTO `area_codes` VALUES ('49','05324',1,'Vienenburg');
INSERT INTO `area_codes` VALUES ('49','05325',1,'Goslar-Hahnenklee');
INSERT INTO `area_codes` VALUES ('49','05326',1,'Langelsheim');
INSERT INTO `area_codes` VALUES ('49','05327',1,'Bad Grund  Harz');
INSERT INTO `area_codes` VALUES ('49','05328',1,'Altenau Harz');
INSERT INTO `area_codes` VALUES ('49','05329',1,'Schulenberg im Oberharz');
INSERT INTO `area_codes` VALUES ('49','05331',1,'Wolfenbüttel');
INSERT INTO `area_codes` VALUES ('49','05332',1,'Schöppenstedt');
INSERT INTO `area_codes` VALUES ('49','05333',1,'Dettum');
INSERT INTO `area_codes` VALUES ('49','05334',1,'Hornburg Kr Wolfenbüttel');
INSERT INTO `area_codes` VALUES ('49','05335',1,'Schladen');
INSERT INTO `area_codes` VALUES ('49','05336',1,'Semmenstedt');
INSERT INTO `area_codes` VALUES ('49','05337',1,'Kissenbrück');
INSERT INTO `area_codes` VALUES ('49','05339',1,'Gielde');
INSERT INTO `area_codes` VALUES ('49','05341',1,'Salzgitter');
INSERT INTO `area_codes` VALUES ('49','05344',1,'Lengede');
INSERT INTO `area_codes` VALUES ('49','05345',1,'Baddeckenstedt');
INSERT INTO `area_codes` VALUES ('49','05346',1,'Liebenburg');
INSERT INTO `area_codes` VALUES ('49','05347',1,'Burgdorf b Salzgitter');
INSERT INTO `area_codes` VALUES ('49','05351',1,'Helmstedt');
INSERT INTO `area_codes` VALUES ('49','05352',1,'Schöningen');
INSERT INTO `area_codes` VALUES ('49','05353',1,'Königslutter am Elm');
INSERT INTO `area_codes` VALUES ('49','05354',1,'Jerxheim');
INSERT INTO `area_codes` VALUES ('49','05355',1,'Frellstedt');
INSERT INTO `area_codes` VALUES ('49','05356',1,'Helmstedt-Barmke');
INSERT INTO `area_codes` VALUES ('49','05357',1,'Grasleben');
INSERT INTO `area_codes` VALUES ('49','05358',1,'Bahrdorf-Mackendorf');
INSERT INTO `area_codes` VALUES ('49','05361',1,'Wolfsburg');
INSERT INTO `area_codes` VALUES ('49','05362',1,'Wolfsburg-Fallersleben');
INSERT INTO `area_codes` VALUES ('49','05363',1,'Wolfsburg-Vorsfelde');
INSERT INTO `area_codes` VALUES ('49','05364',1,'Velpke');
INSERT INTO `area_codes` VALUES ('49','05365',1,'Wolfsburg-Neindorf');
INSERT INTO `area_codes` VALUES ('49','05366',1,'Jembke');
INSERT INTO `area_codes` VALUES ('49','05367',1,'Rühen');
INSERT INTO `area_codes` VALUES ('49','05368',1,'Parsau');
INSERT INTO `area_codes` VALUES ('49','05371',1,'Gifhorn');
INSERT INTO `area_codes` VALUES ('49','05372',1,'Meinersen');
INSERT INTO `area_codes` VALUES ('49','05373',1,'Hillerse Kr Gifhorn');
INSERT INTO `area_codes` VALUES ('49','05374',1,'Isenbüttel');
INSERT INTO `area_codes` VALUES ('49','05375',1,'Müden Aller');
INSERT INTO `area_codes` VALUES ('49','05376',1,'Wesendorf Kr Gifhorn');
INSERT INTO `area_codes` VALUES ('49','05377',1,'Ehra-Lessien');
INSERT INTO `area_codes` VALUES ('49','05378',1,'Sassenburg-Platendorf');
INSERT INTO `area_codes` VALUES ('49','05379',1,'Sassenburg-Grussendorf');
INSERT INTO `area_codes` VALUES ('49','05381',1,'Seesen');
INSERT INTO `area_codes` VALUES ('49','05382',1,'Bad Gandersheim');
INSERT INTO `area_codes` VALUES ('49','05383',1,'Lutter am Barenberge');
INSERT INTO `area_codes` VALUES ('49','05384',1,'Seesen-Groß Rhüden');
INSERT INTO `area_codes` VALUES ('49','05401',1,'Georgsmarienhütte');
INSERT INTO `area_codes` VALUES ('49','05402',1,'Bissendorf Kr Osnabrück');
INSERT INTO `area_codes` VALUES ('49','05403',1,'Bad Iburg');
INSERT INTO `area_codes` VALUES ('49','05404',1,'Westerkappeln');
INSERT INTO `area_codes` VALUES ('49','05405',1,'Hasbergen Kr Osnabrück');
INSERT INTO `area_codes` VALUES ('49','05406',1,'Belm');
INSERT INTO `area_codes` VALUES ('49','05407',1,'Wallenhorst');
INSERT INTO `area_codes` VALUES ('49','05409',1,'Hilter am Teutoburger Wald');
INSERT INTO `area_codes` VALUES ('49','0541',1,'Osnabrück');
INSERT INTO `area_codes` VALUES ('49','05421',1,'Dissen am Teutoburger Wald');
INSERT INTO `area_codes` VALUES ('49','05422',1,'Melle');
INSERT INTO `area_codes` VALUES ('49','05423',1,'Versmold');
INSERT INTO `area_codes` VALUES ('49','05424',1,'Bad Rothenfelde');
INSERT INTO `area_codes` VALUES ('49','05425',1,'Borgholzhausen');
INSERT INTO `area_codes` VALUES ('49','05426',1,'Glandorf');
INSERT INTO `area_codes` VALUES ('49','05427',1,'Melle-Buer');
INSERT INTO `area_codes` VALUES ('49','05428',1,'Melle-Neuenkirchen');
INSERT INTO `area_codes` VALUES ('49','05429',1,'Melle-Wellingholzhausen');
INSERT INTO `area_codes` VALUES ('49','05431',1,'Quakenbrück');
INSERT INTO `area_codes` VALUES ('49','05432',1,'Löningen');
INSERT INTO `area_codes` VALUES ('49','05433',1,'Badbergen');
INSERT INTO `area_codes` VALUES ('49','05434',1,'Essen Oldenburg');
INSERT INTO `area_codes` VALUES ('49','05435',1,'Berge b Quakenbrück');
INSERT INTO `area_codes` VALUES ('49','05436',1,'Nortrup');
INSERT INTO `area_codes` VALUES ('49','05437',1,'Menslage');
INSERT INTO `area_codes` VALUES ('49','05438',1,'Bakum-Lüsche');
INSERT INTO `area_codes` VALUES ('49','05439',1,'Bersenbrück');
INSERT INTO `area_codes` VALUES ('49','05441',1,'Diepholz');
INSERT INTO `area_codes` VALUES ('49','05442',1,'Barnstorf Kr Diepholz');
INSERT INTO `area_codes` VALUES ('49','05443',1,'Lemförde');
INSERT INTO `area_codes` VALUES ('49','05444',1,'Wagenfeld');
INSERT INTO `area_codes` VALUES ('49','05445',1,'Drebber');
INSERT INTO `area_codes` VALUES ('49','05446',1,'Rehden');
INSERT INTO `area_codes` VALUES ('49','05447',1,'Lembruch');
INSERT INTO `area_codes` VALUES ('49','05448',1,'Barver');
INSERT INTO `area_codes` VALUES ('49','05451',1,'Ibbenbüren');
INSERT INTO `area_codes` VALUES ('49','05452',1,'Mettingen Westf');
INSERT INTO `area_codes` VALUES ('49','05453',1,'Recke');
INSERT INTO `area_codes` VALUES ('49','05454',1,'Hörstel-Riesenbeck');
INSERT INTO `area_codes` VALUES ('49','05455',1,'Tecklenburg-Brochterbeck');
INSERT INTO `area_codes` VALUES ('49','05456',1,'Westerkappeln-Velpe');
INSERT INTO `area_codes` VALUES ('49','05457',1,'Hopsten-Schale');
INSERT INTO `area_codes` VALUES ('49','05458',1,'Hopsten');
INSERT INTO `area_codes` VALUES ('49','05459',1,'Hörstel');
INSERT INTO `area_codes` VALUES ('49','05461',1,'Bramsche Hase');
INSERT INTO `area_codes` VALUES ('49','05462',1,'Ankum');
INSERT INTO `area_codes` VALUES ('49','05464',1,'Alfhausen');
INSERT INTO `area_codes` VALUES ('49','05465',1,'Neuenkirchen b Bramsche');
INSERT INTO `area_codes` VALUES ('49','05466',1,'Merzen');
INSERT INTO `area_codes` VALUES ('49','05467',1,'Voltlage');
INSERT INTO `area_codes` VALUES ('49','05468',1,'Bramsche-Engter');
INSERT INTO `area_codes` VALUES ('49','05471',1,'Bohmte');
INSERT INTO `area_codes` VALUES ('49','05472',1,'Bad Essen');
INSERT INTO `area_codes` VALUES ('49','05473',1,'Ostercappeln');
INSERT INTO `area_codes` VALUES ('49','05474',1,'Stemwede-Dielingen');
INSERT INTO `area_codes` VALUES ('49','05475',1,'Bohmte-Hunteburg');
INSERT INTO `area_codes` VALUES ('49','05476',1,'Ostercappeln-Venne');
INSERT INTO `area_codes` VALUES ('49','05481',1,'Lengerich Westf');
INSERT INTO `area_codes` VALUES ('49','05482',1,'Tecklenburg');
INSERT INTO `area_codes` VALUES ('49','05483',1,'Lienen');
INSERT INTO `area_codes` VALUES ('49','05484',1,'Lienen-Kattenvenne');
INSERT INTO `area_codes` VALUES ('49','05485',1,'Ladbergen');
INSERT INTO `area_codes` VALUES ('49','05491',1,'Damme Dümmer');
INSERT INTO `area_codes` VALUES ('49','05492',1,'Steinfeld Oldenburg');
INSERT INTO `area_codes` VALUES ('49','05493',1,'Neuenkirchen Kr Vechta');
INSERT INTO `area_codes` VALUES ('49','05494',1,'Holdorf Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05495',1,'Vörden Kr Vechta');
INSERT INTO `area_codes` VALUES ('49','05502',1,'Dransfeld');
INSERT INTO `area_codes` VALUES ('49','05503',1,'Nörten-Hardenberg');
INSERT INTO `area_codes` VALUES ('49','05504',1,'Friedland Kr Göttingen');
INSERT INTO `area_codes` VALUES ('49','05505',1,'Hardegsen');
INSERT INTO `area_codes` VALUES ('49','05506',1,'Adelebsen');
INSERT INTO `area_codes` VALUES ('49','05507',1,'Ebergötzen');
INSERT INTO `area_codes` VALUES ('49','05508',1,'Gleichen-Rittmarshausen');
INSERT INTO `area_codes` VALUES ('49','05509',1,'Rosdorf Kr Göttingen');
INSERT INTO `area_codes` VALUES ('49','0551',1,'Göttingen');
INSERT INTO `area_codes` VALUES ('49','05520',1,'Braunlage');
INSERT INTO `area_codes` VALUES ('49','05521',1,'Herzberg am Harz');
INSERT INTO `area_codes` VALUES ('49','05522',1,'Osterode am Harz');
INSERT INTO `area_codes` VALUES ('49','05523',1,'Bad Sachsa');
INSERT INTO `area_codes` VALUES ('49','05524',1,'Bad Lauterberg im Harz');
INSERT INTO `area_codes` VALUES ('49','05525',1,'Walkenried');
INSERT INTO `area_codes` VALUES ('49','05527',1,'Duderstadt');
INSERT INTO `area_codes` VALUES ('49','05528',1,'Gieboldehausen');
INSERT INTO `area_codes` VALUES ('49','05529',1,'Rhumspringe');
INSERT INTO `area_codes` VALUES ('49','05531',1,'Holzminden');
INSERT INTO `area_codes` VALUES ('49','05532',1,'Stadtoldendorf');
INSERT INTO `area_codes` VALUES ('49','05533',1,'Bodenwerder');
INSERT INTO `area_codes` VALUES ('49','05534',1,'Eschershausen a d Lenne');
INSERT INTO `area_codes` VALUES ('49','05535',1,'Polle');
INSERT INTO `area_codes` VALUES ('49','05536',1,'Holzminden-Neuhaus');
INSERT INTO `area_codes` VALUES ('49','05541',1,'Hann. Münden');
INSERT INTO `area_codes` VALUES ('49','05542',1,'Witzenhausen');
INSERT INTO `area_codes` VALUES ('49','05543',1,'Staufenberg Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05544',1,'Reinhardshagen');
INSERT INTO `area_codes` VALUES ('49','05545',1,'Hedemünden');
INSERT INTO `area_codes` VALUES ('49','05546',1,'Scheden');
INSERT INTO `area_codes` VALUES ('49','05551',1,'Northeim');
INSERT INTO `area_codes` VALUES ('49','05552',1,'Katlenburg');
INSERT INTO `area_codes` VALUES ('49','05553',1,'Kalefeld');
INSERT INTO `area_codes` VALUES ('49','05554',1,'Moringen');
INSERT INTO `area_codes` VALUES ('49','05555',1,'Moringen-Fredelsloh');
INSERT INTO `area_codes` VALUES ('49','05556',1,'Lindau Harz');
INSERT INTO `area_codes` VALUES ('49','05561',1,'Einbeck');
INSERT INTO `area_codes` VALUES ('49','05562',1,'Dassel-Markoldendorf');
INSERT INTO `area_codes` VALUES ('49','05563',1,'Kreiensen');
INSERT INTO `area_codes` VALUES ('49','05564',1,'Dassel');
INSERT INTO `area_codes` VALUES ('49','05565',1,'Einbeck-Wenzen');
INSERT INTO `area_codes` VALUES ('49','05571',1,'Uslar');
INSERT INTO `area_codes` VALUES ('49','05572',1,'Bodenfelde');
INSERT INTO `area_codes` VALUES ('49','05573',1,'Uslar-Volpriehausen');
INSERT INTO `area_codes` VALUES ('49','05574',1,'Oberweser');
INSERT INTO `area_codes` VALUES ('49','05582',1,'Sankt Andreasberg');
INSERT INTO `area_codes` VALUES ('49','05583',1,'Braunlage-Hohegeiss');
INSERT INTO `area_codes` VALUES ('49','05584',1,'Hattorf am Harz');
INSERT INTO `area_codes` VALUES ('49','05585',1,'Herzberg-Sieber');
INSERT INTO `area_codes` VALUES ('49','05586',1,'Wieda');
INSERT INTO `area_codes` VALUES ('49','05592',1,'Gleichen-Bremke');
INSERT INTO `area_codes` VALUES ('49','05593',1,'Bovenden-Lenglern');
INSERT INTO `area_codes` VALUES ('49','05594',1,'Bovenden-Reyershausen');
INSERT INTO `area_codes` VALUES ('49','05601',1,'Schauenburg');
INSERT INTO `area_codes` VALUES ('49','05602',1,'Hessisch Lichtenau');
INSERT INTO `area_codes` VALUES ('49','05603',1,'Gudensberg');
INSERT INTO `area_codes` VALUES ('49','05604',1,'Grossalmerode');
INSERT INTO `area_codes` VALUES ('49','05605',1,'Kaufungen Hess');
INSERT INTO `area_codes` VALUES ('49','05606',1,'Zierenberg');
INSERT INTO `area_codes` VALUES ('49','05607',1,'Fuldatal');
INSERT INTO `area_codes` VALUES ('49','05608',1,'Söhrewald');
INSERT INTO `area_codes` VALUES ('49','05609',1,'Ahnatal');
INSERT INTO `area_codes` VALUES ('49','0561',1,'Kassel');
INSERT INTO `area_codes` VALUES ('49','05621',1,'Bad Wildungen');
INSERT INTO `area_codes` VALUES ('49','05622',1,'Fritzlar');
INSERT INTO `area_codes` VALUES ('49','05623',1,'Edertal');
INSERT INTO `area_codes` VALUES ('49','05624',1,'Bad Emstal');
INSERT INTO `area_codes` VALUES ('49','05625',1,'Naumburg Hess');
INSERT INTO `area_codes` VALUES ('49','05626',1,'Bad Zwesten');
INSERT INTO `area_codes` VALUES ('49','05631',1,'Korbach');
INSERT INTO `area_codes` VALUES ('49','05632',1,'Willingen Upland');
INSERT INTO `area_codes` VALUES ('49','05633',1,'Diemelsee');
INSERT INTO `area_codes` VALUES ('49','05634',1,'Waldeck-Sachsenhausen');
INSERT INTO `area_codes` VALUES ('49','05635',1,'Vöhl');
INSERT INTO `area_codes` VALUES ('49','05636',1,'Lichtenfels-Goddelsheim');
INSERT INTO `area_codes` VALUES ('49','05641',1,'Warburg');
INSERT INTO `area_codes` VALUES ('49','05642',1,'Warburg-Scherfede');
INSERT INTO `area_codes` VALUES ('49','05643',1,'Borgentreich');
INSERT INTO `area_codes` VALUES ('49','05644',1,'Willebadessen-Peckelsheim');
INSERT INTO `area_codes` VALUES ('49','05645',1,'Borgentreich-Borgholz');
INSERT INTO `area_codes` VALUES ('49','05646',1,'Willebadessen');
INSERT INTO `area_codes` VALUES ('49','05647',1,'Lichtenau-Kleinenberg');
INSERT INTO `area_codes` VALUES ('49','05648',1,'Brakel-Gehrden');
INSERT INTO `area_codes` VALUES ('49','05650',1,'Cornberg');
INSERT INTO `area_codes` VALUES ('49','05651',1,'Eschwege');
INSERT INTO `area_codes` VALUES ('49','05652',1,'Bad Sooden-Allendorf');
INSERT INTO `area_codes` VALUES ('49','05653',1,'Sontra');
INSERT INTO `area_codes` VALUES ('49','05654',1,'Herleshausen');
INSERT INTO `area_codes` VALUES ('49','05655',1,'Wanfried');
INSERT INTO `area_codes` VALUES ('49','05656',1,'Waldkappel');
INSERT INTO `area_codes` VALUES ('49','05657',1,'Meissner');
INSERT INTO `area_codes` VALUES ('49','05658',1,'Wehretal');
INSERT INTO `area_codes` VALUES ('49','05659',1,'Ringgau');
INSERT INTO `area_codes` VALUES ('49','05661',1,'Melsungen');
INSERT INTO `area_codes` VALUES ('49','05662',1,'Felsberg Hess');
INSERT INTO `area_codes` VALUES ('49','05663',1,'Spangenberg');
INSERT INTO `area_codes` VALUES ('49','05664',1,'Morschen');
INSERT INTO `area_codes` VALUES ('49','05665',1,'Guxhagen');
INSERT INTO `area_codes` VALUES ('49','05671',1,'Hofgeismar');
INSERT INTO `area_codes` VALUES ('49','05672',1,'Bad Karlshafen');
INSERT INTO `area_codes` VALUES ('49','05673',1,'Immenhausen Hess');
INSERT INTO `area_codes` VALUES ('49','05674',1,'Grebenstein');
INSERT INTO `area_codes` VALUES ('49','05675',1,'Trendelburg');
INSERT INTO `area_codes` VALUES ('49','05676',1,'Liebenau Hess');
INSERT INTO `area_codes` VALUES ('49','05677',1,'Calden-Westuffeln');
INSERT INTO `area_codes` VALUES ('49','05681',1,'Homberg Efze');
INSERT INTO `area_codes` VALUES ('49','05682',1,'Borken Hessen');
INSERT INTO `area_codes` VALUES ('49','05683',1,'Wabern Hess');
INSERT INTO `area_codes` VALUES ('49','05684',1,'Frielendorf');
INSERT INTO `area_codes` VALUES ('49','05685',1,'Knüllwald');
INSERT INTO `area_codes` VALUES ('49','05686',1,'Schwarzenborn Knüll');
INSERT INTO `area_codes` VALUES ('49','05691',1,'Bad Arolsen');
INSERT INTO `area_codes` VALUES ('49','05692',1,'Wolfhagen');
INSERT INTO `area_codes` VALUES ('49','05693',1,'Volkmarsen');
INSERT INTO `area_codes` VALUES ('49','05694',1,'Diemelstadt');
INSERT INTO `area_codes` VALUES ('49','05695',1,'Twistetal');
INSERT INTO `area_codes` VALUES ('49','05696',1,'Bad Arolsen-Landau');
INSERT INTO `area_codes` VALUES ('49','05702',1,'Petershagen-Lahde');
INSERT INTO `area_codes` VALUES ('49','05703',1,'Hille');
INSERT INTO `area_codes` VALUES ('49','05704',1,'Petershagen-Friedewalde');
INSERT INTO `area_codes` VALUES ('49','05705',1,'Petershagen-Windheim');
INSERT INTO `area_codes` VALUES ('49','05706',1,'Porta Westfalica');
INSERT INTO `area_codes` VALUES ('49','05707',1,'Petershagen Weser');
INSERT INTO `area_codes` VALUES ('49','0571',1,'Minden Westf');
INSERT INTO `area_codes` VALUES ('49','05721',1,'Stadthagen');
INSERT INTO `area_codes` VALUES ('49','05722',1,'Bückeburg');
INSERT INTO `area_codes` VALUES ('49','05723',1,'Bad Nenndorf');
INSERT INTO `area_codes` VALUES ('49','05724',1,'Obernkirchen');
INSERT INTO `area_codes` VALUES ('49','05725',1,'Lindhorst b Stadthagen');
INSERT INTO `area_codes` VALUES ('49','05726',1,'Wiedensahl');
INSERT INTO `area_codes` VALUES ('49','05731',1,'Bad Oeynhausen');
INSERT INTO `area_codes` VALUES ('49','05732',1,'Löhne');
INSERT INTO `area_codes` VALUES ('49','05733',1,'Vlotho');
INSERT INTO `area_codes` VALUES ('49','05734',1,'Bergkirchen Westf');
INSERT INTO `area_codes` VALUES ('49','05741',1,'Lübbecke');
INSERT INTO `area_codes` VALUES ('49','05742',1,'Preussisch Oldendorf');
INSERT INTO `area_codes` VALUES ('49','05743',1,'Espelkamp-Gestringen');
INSERT INTO `area_codes` VALUES ('49','05744',1,'Hüllhorst');
INSERT INTO `area_codes` VALUES ('49','05745',1,'Stemwede-Levern');
INSERT INTO `area_codes` VALUES ('49','05746',1,'Rödinghausen');
INSERT INTO `area_codes` VALUES ('49','05751',1,'Rinteln');
INSERT INTO `area_codes` VALUES ('49','05752',1,'Auetal-Hattendorf');
INSERT INTO `area_codes` VALUES ('49','05753',1,'Auetal-Bernsen');
INSERT INTO `area_codes` VALUES ('49','05754',1,'Extertal-Bremke');
INSERT INTO `area_codes` VALUES ('49','05755',1,'Kalletal-Varenholz');
INSERT INTO `area_codes` VALUES ('49','05761',1,'Stolzenau');
INSERT INTO `area_codes` VALUES ('49','05763',1,'Uchte');
INSERT INTO `area_codes` VALUES ('49','05764',1,'Steyerberg');
INSERT INTO `area_codes` VALUES ('49','05765',1,'Raddestorf');
INSERT INTO `area_codes` VALUES ('49','05766',1,'Rehburg-Loccum');
INSERT INTO `area_codes` VALUES ('49','05767',1,'Warmsen');
INSERT INTO `area_codes` VALUES ('49','05768',1,'Petershagen-Heimsen');
INSERT INTO `area_codes` VALUES ('49','05769',1,'Steyerberg-Voigtei');
INSERT INTO `area_codes` VALUES ('49','05771',1,'Rahden Westf');
INSERT INTO `area_codes` VALUES ('49','05772',1,'Espelkamp');
INSERT INTO `area_codes` VALUES ('49','05773',1,'Stemwede-Wehdem');
INSERT INTO `area_codes` VALUES ('49','05774',1,'Wagenfeld-Ströhen');
INSERT INTO `area_codes` VALUES ('49','05775',1,'Diepenau');
INSERT INTO `area_codes` VALUES ('49','05776',1,'Preussisch Ströhen');
INSERT INTO `area_codes` VALUES ('49','05777',1,'Diepenau-Essern');
INSERT INTO `area_codes` VALUES ('49','05802',1,'Wrestedt');
INSERT INTO `area_codes` VALUES ('49','05803',1,'Rosche');
INSERT INTO `area_codes` VALUES ('49','05804',1,'Rätzlingen Kr Uelzen');
INSERT INTO `area_codes` VALUES ('49','05805',1,'Oetzen');
INSERT INTO `area_codes` VALUES ('49','05806',1,'Barum b Bad Bevensen');
INSERT INTO `area_codes` VALUES ('49','05807',1,'Altenmedingen');
INSERT INTO `area_codes` VALUES ('49','05808',1,'Gerdau');
INSERT INTO `area_codes` VALUES ('49','0581',1,'Uelzen');
INSERT INTO `area_codes` VALUES ('49','05820',1,'Suhlendorf');
INSERT INTO `area_codes` VALUES ('49','05821',1,'Bad Bevensen');
INSERT INTO `area_codes` VALUES ('49','05822',1,'Ebstorf');
INSERT INTO `area_codes` VALUES ('49','05823',1,'Bienenbüttel');
INSERT INTO `area_codes` VALUES ('49','05824',1,'Bad Bodenteich');
INSERT INTO `area_codes` VALUES ('49','05825',1,'Wieren');
INSERT INTO `area_codes` VALUES ('49','05826',1,'Suderburg');
INSERT INTO `area_codes` VALUES ('49','05827',1,'Unterlüß');
INSERT INTO `area_codes` VALUES ('49','05828',1,'Himbergen');
INSERT INTO `area_codes` VALUES ('49','05829',1,'Wriedel');
INSERT INTO `area_codes` VALUES ('49','05831',1,'Wittingen');
INSERT INTO `area_codes` VALUES ('49','05832',1,'Hankensbüttel');
INSERT INTO `area_codes` VALUES ('49','05833',1,'Brome');
INSERT INTO `area_codes` VALUES ('49','05834',1,'Wittingen-Knesebeck');
INSERT INTO `area_codes` VALUES ('49','05835',1,'Wahrenholz');
INSERT INTO `area_codes` VALUES ('49','05836',1,'Wittingen-Radenbeck');
INSERT INTO `area_codes` VALUES ('49','05837',1,'Sprakensehl');
INSERT INTO `area_codes` VALUES ('49','05838',1,'Gross Oesingen');
INSERT INTO `area_codes` VALUES ('49','05839',1,'Wittingen-Ohrdorf');
INSERT INTO `area_codes` VALUES ('49','05840',1,'Schnackenburg');
INSERT INTO `area_codes` VALUES ('49','05841',1,'Lüchow Wendland');
INSERT INTO `area_codes` VALUES ('49','05842',1,'Schnega');
INSERT INTO `area_codes` VALUES ('49','05843',1,'Wustrow');
INSERT INTO `area_codes` VALUES ('49','05844',1,'Clenze');
INSERT INTO `area_codes` VALUES ('49','05845',1,'Bergen Dumme');
INSERT INTO `area_codes` VALUES ('49','05846',1,'Gartow Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05848',1,'Trebel');
INSERT INTO `area_codes` VALUES ('49','05849',1,'Waddeweitz');
INSERT INTO `area_codes` VALUES ('49','05850',1,'Neetze');
INSERT INTO `area_codes` VALUES ('49','05851',1,'Dahlenburg');
INSERT INTO `area_codes` VALUES ('49','05852',1,'Bleckede');
INSERT INTO `area_codes` VALUES ('49','05853',1,'Neu Darchau');
INSERT INTO `area_codes` VALUES ('49','05854',1,'Bleckede-Barskamp');
INSERT INTO `area_codes` VALUES ('49','05855',1,'Nahrendorf');
INSERT INTO `area_codes` VALUES ('49','05857',1,'Bleckede-Brackede');
INSERT INTO `area_codes` VALUES ('49','05858',1,'Hitzacker-Wietzetze');
INSERT INTO `area_codes` VALUES ('49','05859',1,'Thomasburg');
INSERT INTO `area_codes` VALUES ('49','05861',1,'Dannenberg Elbe');
INSERT INTO `area_codes` VALUES ('49','05862',1,'Hitzacker Elbe');
INSERT INTO `area_codes` VALUES ('49','05863',1,'Zernien');
INSERT INTO `area_codes` VALUES ('49','05864',1,'Jameln');
INSERT INTO `area_codes` VALUES ('49','05865',1,'Gusborn');
INSERT INTO `area_codes` VALUES ('49','05872',1,'Stoetze');
INSERT INTO `area_codes` VALUES ('49','05873',1,'Eimke');
INSERT INTO `area_codes` VALUES ('49','05874',1,'Soltendieck');
INSERT INTO `area_codes` VALUES ('49','05875',1,'Emmendorf');
INSERT INTO `area_codes` VALUES ('49','05882',1,'Gorleben');
INSERT INTO `area_codes` VALUES ('49','05883',1,'Lemgow');
INSERT INTO `area_codes` VALUES ('49','05901',1,'Fürstenau b Bramsche');
INSERT INTO `area_codes` VALUES ('49','05902',1,'Freren');
INSERT INTO `area_codes` VALUES ('49','05903',1,'Emsbüren');
INSERT INTO `area_codes` VALUES ('49','05904',1,'Lengerich Emsl');
INSERT INTO `area_codes` VALUES ('49','05905',1,'Beesten');
INSERT INTO `area_codes` VALUES ('49','05906',1,'Lünne');
INSERT INTO `area_codes` VALUES ('49','05907',1,'Geeste');
INSERT INTO `area_codes` VALUES ('49','05908',1,'Wietmarschen-Lohne');
INSERT INTO `area_codes` VALUES ('49','05909',1,'Wettrup');
INSERT INTO `area_codes` VALUES ('49','0591',1,'Lingen (Ems)');
INSERT INTO `area_codes` VALUES ('49','05921',1,'Nordhorn');
INSERT INTO `area_codes` VALUES ('49','05922',1,'Bad Bentheim');
INSERT INTO `area_codes` VALUES ('49','05923',1,'Schüttorf');
INSERT INTO `area_codes` VALUES ('49','05924',1,'Bad Bentheim-Gildehaus');
INSERT INTO `area_codes` VALUES ('49','05925',1,'Wietmarschen');
INSERT INTO `area_codes` VALUES ('49','05926',1,'Engden');
INSERT INTO `area_codes` VALUES ('49','05931',1,'Meppen');
INSERT INTO `area_codes` VALUES ('49','05932',1,'Haren Ems');
INSERT INTO `area_codes` VALUES ('49','05933',1,'Lathen');
INSERT INTO `area_codes` VALUES ('49','05934',1,'Haren-Rütenbrock');
INSERT INTO `area_codes` VALUES ('49','05935',1,'Twist-Schöninghsdorf');
INSERT INTO `area_codes` VALUES ('49','05936',1,'Twist');
INSERT INTO `area_codes` VALUES ('49','05937',1,'Geeste-Gross Hesepe');
INSERT INTO `area_codes` VALUES ('49','05939',1,'Sustrum');
INSERT INTO `area_codes` VALUES ('49','05941',1,'Neuenhaus Dinkel');
INSERT INTO `area_codes` VALUES ('49','05942',1,'Uelsen');
INSERT INTO `area_codes` VALUES ('49','05943',1,'Emlichheim');
INSERT INTO `area_codes` VALUES ('49','05944',1,'Hoogstede');
INSERT INTO `area_codes` VALUES ('49','05945',1,'Wilsum');
INSERT INTO `area_codes` VALUES ('49','05946',1,'Georgsdorf');
INSERT INTO `area_codes` VALUES ('49','05947',1,'Laar Vechte');
INSERT INTO `area_codes` VALUES ('49','05948',1,'Itterbeck');
INSERT INTO `area_codes` VALUES ('49','05951',1,'Werlte');
INSERT INTO `area_codes` VALUES ('49','05952',1,'Sögel');
INSERT INTO `area_codes` VALUES ('49','05953',1,'Börger');
INSERT INTO `area_codes` VALUES ('49','05954',1,'Lorup');
INSERT INTO `area_codes` VALUES ('49','05955',1,'Esterwegen');
INSERT INTO `area_codes` VALUES ('49','05956',1,'Rastdorf');
INSERT INTO `area_codes` VALUES ('49','05957',1,'Lindern Oldenburg');
INSERT INTO `area_codes` VALUES ('49','05961',1,'Haselünne');
INSERT INTO `area_codes` VALUES ('49','05962',1,'Herzlake');
INSERT INTO `area_codes` VALUES ('49','05963',1,'Bawinkel');
INSERT INTO `area_codes` VALUES ('49','05964',1,'Lähden');
INSERT INTO `area_codes` VALUES ('49','05965',1,'Klein Berssen');
INSERT INTO `area_codes` VALUES ('49','05966',1,'Meppen-Apeldorn');
INSERT INTO `area_codes` VALUES ('49','05971',1,'Rheine');
INSERT INTO `area_codes` VALUES ('49','05973',1,'Neuenkirchen Kr Steinfurt');
INSERT INTO `area_codes` VALUES ('49','05975',1,'Rheine-Mesum');
INSERT INTO `area_codes` VALUES ('49','05976',1,'Salzbergen');
INSERT INTO `area_codes` VALUES ('49','05977',1,'Spelle');
INSERT INTO `area_codes` VALUES ('49','05978',1,'Hörstel-Dreierwalde');
INSERT INTO `area_codes` VALUES ('49','0600',0,'(Reserve für Telekommunikationsdienste)');
INSERT INTO `area_codes` VALUES ('49','06002',1,'Ober-Mörlen');
INSERT INTO `area_codes` VALUES ('49','06003',1,'Rosbach v d Höhe');
INSERT INTO `area_codes` VALUES ('49','06004',1,'Lich-Eberstadt');
INSERT INTO `area_codes` VALUES ('49','06007',1,'Rosbach-Rodheim');
INSERT INTO `area_codes` VALUES ('49','06008',1,'Echzell');
INSERT INTO `area_codes` VALUES ('49','0601',0,'(Reserve für Telekommunikationsdienste)');
INSERT INTO `area_codes` VALUES ('49','06020',1,'Heigenbrücken');
INSERT INTO `area_codes` VALUES ('49','06021',1,'Aschaffenburg');
INSERT INTO `area_codes` VALUES ('49','06022',1,'Obernburg a Main');
INSERT INTO `area_codes` VALUES ('49','06023',1,'Alzenau i Ufr');
INSERT INTO `area_codes` VALUES ('49','06024',1,'Schöllkrippen');
INSERT INTO `area_codes` VALUES ('49','06026',1,'Grossostheim');
INSERT INTO `area_codes` VALUES ('49','06027',1,'Stockstadt a Main');
INSERT INTO `area_codes` VALUES ('49','06028',1,'Sulzbach a Main');
INSERT INTO `area_codes` VALUES ('49','06029',1,'Mömbris');
INSERT INTO `area_codes` VALUES ('49','06031',1,'Friedberg Hess');
INSERT INTO `area_codes` VALUES ('49','06032',1,'Bad Nauheim');
INSERT INTO `area_codes` VALUES ('49','06033',1,'Butzbach');
INSERT INTO `area_codes` VALUES ('49','06034',1,'Wöllstadt');
INSERT INTO `area_codes` VALUES ('49','06035',1,'Reichelsheim Wetterau');
INSERT INTO `area_codes` VALUES ('49','06036',1,'Wölfersheim');
INSERT INTO `area_codes` VALUES ('49','06039',1,'Karben');
INSERT INTO `area_codes` VALUES ('49','06041',1,'Glauburg');
INSERT INTO `area_codes` VALUES ('49','06042',1,'Büdingen Hess');
INSERT INTO `area_codes` VALUES ('49','06043',1,'Nidda');
INSERT INTO `area_codes` VALUES ('49','06044',1,'Schotten Hess');
INSERT INTO `area_codes` VALUES ('49','06045',1,'Gedern');
INSERT INTO `area_codes` VALUES ('49','06046',1,'Ortenberg Hess');
INSERT INTO `area_codes` VALUES ('49','06047',1,'Altenstadt Hess');
INSERT INTO `area_codes` VALUES ('49','06048',1,'Büdingen-Eckartshausen');
INSERT INTO `area_codes` VALUES ('49','06049',1,'Kefenrod');
INSERT INTO `area_codes` VALUES ('49','06050',1,'Biebergemünd');
INSERT INTO `area_codes` VALUES ('49','06051',1,'Gelnhausen');
INSERT INTO `area_codes` VALUES ('49','06052',1,'Bad Orb');
INSERT INTO `area_codes` VALUES ('49','06053',1,'Wächtersbach');
INSERT INTO `area_codes` VALUES ('49','06054',1,'Birstein');
INSERT INTO `area_codes` VALUES ('49','06055',1,'Freigericht');
INSERT INTO `area_codes` VALUES ('49','06056',1,'Bad Soden-Salmünster');
INSERT INTO `area_codes` VALUES ('49','06057',1,'Flörsbachtal');
INSERT INTO `area_codes` VALUES ('49','06058',1,'Gründau');
INSERT INTO `area_codes` VALUES ('49','06059',1,'Jossgrund');
INSERT INTO `area_codes` VALUES ('49','06061',1,'Michelstadt');
INSERT INTO `area_codes` VALUES ('49','06062',1,'Erbach Odenw');
INSERT INTO `area_codes` VALUES ('49','06063',1,'Bad König');
INSERT INTO `area_codes` VALUES ('49','06066',1,'Michelstadt-Vielbrunn');
INSERT INTO `area_codes` VALUES ('49','06068',1,'Beerfelden');
INSERT INTO `area_codes` VALUES ('49','06071',1,'Dieburg');
INSERT INTO `area_codes` VALUES ('49','06073',1,'Babenhausen Hess');
INSERT INTO `area_codes` VALUES ('49','06074',1,'Rödermark');
INSERT INTO `area_codes` VALUES ('49','06078',1,'Gross-Umstadt');
INSERT INTO `area_codes` VALUES ('49','06081',1,'Usingen');
INSERT INTO `area_codes` VALUES ('49','06082',1,'Niederreifenberg');
INSERT INTO `area_codes` VALUES ('49','06083',1,'Weilrod');
INSERT INTO `area_codes` VALUES ('49','06084',1,'Schmitten Taunus');
INSERT INTO `area_codes` VALUES ('49','06085',1,'Waldsolms');
INSERT INTO `area_codes` VALUES ('49','06086',1,'Grävenwiesbach');
INSERT INTO `area_codes` VALUES ('49','06087',1,'Waldems');
INSERT INTO `area_codes` VALUES ('49','06092',1,'Heimbuchenthal');
INSERT INTO `area_codes` VALUES ('49','06093',1,'Laufach');
INSERT INTO `area_codes` VALUES ('49','06094',1,'Weibersbrunn');
INSERT INTO `area_codes` VALUES ('49','06095',1,'Bessenbach');
INSERT INTO `area_codes` VALUES ('49','06096',1,'Wiesen Unterfr');
INSERT INTO `area_codes` VALUES ('49','06101',1,'Bad Vilbel');
INSERT INTO `area_codes` VALUES ('49','06102',1,'Neu-Isenburg');
INSERT INTO `area_codes` VALUES ('49','06103',1,'Langen Hess');
INSERT INTO `area_codes` VALUES ('49','06104',1,'Heusenstamm');
INSERT INTO `area_codes` VALUES ('49','06105',1,'Mörfelden-Walldorf');
INSERT INTO `area_codes` VALUES ('49','06106',1,'Rodgau');
INSERT INTO `area_codes` VALUES ('49','06107',1,'Kelsterbach');
INSERT INTO `area_codes` VALUES ('49','06108',1,'Mühlheim am Main');
INSERT INTO `area_codes` VALUES ('49','06109',1,'Frankfurt-Bergen-Enkheim');
INSERT INTO `area_codes` VALUES ('49','0611',1,'Wiesbaden');
INSERT INTO `area_codes` VALUES ('49','06120',1,'Aarbergen');
INSERT INTO `area_codes` VALUES ('49','06122',1,'Hofheim-Wallau');
INSERT INTO `area_codes` VALUES ('49','06123',1,'Eltville am Rhein');
INSERT INTO `area_codes` VALUES ('49','06124',1,'Bad Schwalbach');
INSERT INTO `area_codes` VALUES ('49','06126',1,'Idstein');
INSERT INTO `area_codes` VALUES ('49','06127',1,'Niedernhausen Taunus');
INSERT INTO `area_codes` VALUES ('49','06128',1,'Taunusstein');
INSERT INTO `area_codes` VALUES ('49','06129',1,'Schlangenbad');
INSERT INTO `area_codes` VALUES ('49','06130',1,'Schwabenheim an der Selz');
INSERT INTO `area_codes` VALUES ('49','06131',1,'Mainz');
INSERT INTO `area_codes` VALUES ('49','06132',1,'Ingelheim am Rhein');
INSERT INTO `area_codes` VALUES ('49','06133',1,'Oppenheim');
INSERT INTO `area_codes` VALUES ('49','06134',1,'Mainz-Kastel');
INSERT INTO `area_codes` VALUES ('49','06135',1,'Bodenheim Rhein');
INSERT INTO `area_codes` VALUES ('49','06136',1,'Nieder-Olm');
INSERT INTO `area_codes` VALUES ('49','06138',1,'Mommenheim');
INSERT INTO `area_codes` VALUES ('49','06139',1,'Budenheim');
INSERT INTO `area_codes` VALUES ('49','06142',1,'Rüsselsheim');
INSERT INTO `area_codes` VALUES ('49','06144',1,'Bischofsheim b Rüsselsheim');
INSERT INTO `area_codes` VALUES ('49','06145',1,'Flörsheim am Main');
INSERT INTO `area_codes` VALUES ('49','06146',1,'Hochheim am Main');
INSERT INTO `area_codes` VALUES ('49','06147',1,'Trebur');
INSERT INTO `area_codes` VALUES ('49','06150',1,'Weiterstadt');
INSERT INTO `area_codes` VALUES ('49','06151',1,'Darmstadt');
INSERT INTO `area_codes` VALUES ('49','06152',1,'Gross-Gerau');
INSERT INTO `area_codes` VALUES ('49','06154',1,'Ober-Ramstadt');
INSERT INTO `area_codes` VALUES ('49','06155',1,'Griesheim Hess');
INSERT INTO `area_codes` VALUES ('49','06157',1,'Pfungstadt');
INSERT INTO `area_codes` VALUES ('49','06158',1,'Riedstadt');
INSERT INTO `area_codes` VALUES ('49','06159',1,'Messel');
INSERT INTO `area_codes` VALUES ('49','06161',1,'Brensbach');
INSERT INTO `area_codes` VALUES ('49','06162',1,'Reinheim Odenw');
INSERT INTO `area_codes` VALUES ('49','06163',1,'Höchst i Odw');
INSERT INTO `area_codes` VALUES ('49','06164',1,'Reichelsheim Odenwald');
INSERT INTO `area_codes` VALUES ('49','06165',1,'Breuberg');
INSERT INTO `area_codes` VALUES ('49','06166',1,'Fischbachtal');
INSERT INTO `area_codes` VALUES ('49','06167',1,'Modautal');
INSERT INTO `area_codes` VALUES ('49','06171',1,'Oberursel Taunus');
INSERT INTO `area_codes` VALUES ('49','06172',1,'Bad Homburg v d Höhe');
INSERT INTO `area_codes` VALUES ('49','06173',1,'Kronberg im Taunus');
INSERT INTO `area_codes` VALUES ('49','06174',1,'Königstein im Taunus');
INSERT INTO `area_codes` VALUES ('49','06175',1,'Friedrichsdorf Taunus');
INSERT INTO `area_codes` VALUES ('49','06181',1,'Hanau');
INSERT INTO `area_codes` VALUES ('49','06182',1,'Seligenstadt');
INSERT INTO `area_codes` VALUES ('49','06183',1,'Erlensee');
INSERT INTO `area_codes` VALUES ('49','06184',1,'Langenselbold');
INSERT INTO `area_codes` VALUES ('49','06185',1,'Hammersbach Hess');
INSERT INTO `area_codes` VALUES ('49','06186',1,'Grosskrotzenburg');
INSERT INTO `area_codes` VALUES ('49','06187',1,'Schöneck');
INSERT INTO `area_codes` VALUES ('49','06188',1,'Kahl a Main');
INSERT INTO `area_codes` VALUES ('49','06190',1,'Hattersheim a Main');
INSERT INTO `area_codes` VALUES ('49','06192',1,'Hofheim am Taunus');
INSERT INTO `area_codes` VALUES ('49','06195',1,'Kelkheim Taunus');
INSERT INTO `area_codes` VALUES ('49','06196',1,'Bad Soden am Taunus');
INSERT INTO `area_codes` VALUES ('49','06198',1,'Eppstein');
INSERT INTO `area_codes` VALUES ('49','06201',1,'Weinheim Bergstr');
INSERT INTO `area_codes` VALUES ('49','06202',1,'Schwetzingen');
INSERT INTO `area_codes` VALUES ('49','06203',1,'Ladenburg');
INSERT INTO `area_codes` VALUES ('49','06204',1,'Viernheim');
INSERT INTO `area_codes` VALUES ('49','06205',1,'Hockenheim');
INSERT INTO `area_codes` VALUES ('49','06206',1,'Lampertheim');
INSERT INTO `area_codes` VALUES ('49','06207',1,'Wald-Michelbach');
INSERT INTO `area_codes` VALUES ('49','06209',1,'Mörlenbach');
INSERT INTO `area_codes` VALUES ('49','0621',1,'Mannheim');
INSERT INTO `area_codes` VALUES ('49','06220',1,'Wilhelmsfeld');
INSERT INTO `area_codes` VALUES ('49','06221',1,'Heidelberg');
INSERT INTO `area_codes` VALUES ('49','06222',1,'Wiesloch');
INSERT INTO `area_codes` VALUES ('49','06223',1,'Neckargemünd');
INSERT INTO `area_codes` VALUES ('49','06224',1,'Sandhausen Baden');
INSERT INTO `area_codes` VALUES ('49','06226',1,'Meckesheim');
INSERT INTO `area_codes` VALUES ('49','06227',1,'Walldorf Baden');
INSERT INTO `area_codes` VALUES ('49','06228',1,'Schönau Odenw');
INSERT INTO `area_codes` VALUES ('49','06229',1,'Neckarsteinach');
INSERT INTO `area_codes` VALUES ('49','06231',1,'Hochdorf-Assenheim');
INSERT INTO `area_codes` VALUES ('49','06232',1,'Speyer');
INSERT INTO `area_codes` VALUES ('49','06233',1,'Frankenthal Pfalz');
INSERT INTO `area_codes` VALUES ('49','06234',1,'Mutterstadt');
INSERT INTO `area_codes` VALUES ('49','06235',1,'Schifferstadt');
INSERT INTO `area_codes` VALUES ('49','06236',1,'Neuhofen Pfalz');
INSERT INTO `area_codes` VALUES ('49','06237',1,'Maxdorf');
INSERT INTO `area_codes` VALUES ('49','06238',1,'Dirmstein');
INSERT INTO `area_codes` VALUES ('49','06239',1,'Bobenheim-Roxheim');
INSERT INTO `area_codes` VALUES ('49','06241',1,'Worms');
INSERT INTO `area_codes` VALUES ('49','06242',1,'Osthofen');
INSERT INTO `area_codes` VALUES ('49','06243',1,'Monsheim');
INSERT INTO `area_codes` VALUES ('49','06244',1,'Westhofen Rheinhess');
INSERT INTO `area_codes` VALUES ('49','06245',1,'Biblis');
INSERT INTO `area_codes` VALUES ('49','06246',1,'Eich Rheinhess');
INSERT INTO `area_codes` VALUES ('49','06247',1,'Worms-Pfeddersheim');
INSERT INTO `area_codes` VALUES ('49','06249',1,'Guntersblum');
INSERT INTO `area_codes` VALUES ('49','06251',1,'Bensheim');
INSERT INTO `area_codes` VALUES ('49','06252',1,'Heppenheim Bergstraße');
INSERT INTO `area_codes` VALUES ('49','06253',1,'Fürth Odenw');
INSERT INTO `area_codes` VALUES ('49','06254',1,'Lautertal Odenwald');
INSERT INTO `area_codes` VALUES ('49','06255',1,'Lindenfels');
INSERT INTO `area_codes` VALUES ('49','06256',1,'Lampertheim-Hüttenfeld');
INSERT INTO `area_codes` VALUES ('49','06257',1,'Seeheim-Jugenheim');
INSERT INTO `area_codes` VALUES ('49','06258',1,'Gernsheim');
INSERT INTO `area_codes` VALUES ('49','06261',1,'Mosbach Baden');
INSERT INTO `area_codes` VALUES ('49','06262',1,'Aglasterhausen');
INSERT INTO `area_codes` VALUES ('49','06263',1,'Neckargerach');
INSERT INTO `area_codes` VALUES ('49','06264',1,'Neudenau');
INSERT INTO `area_codes` VALUES ('49','06265',1,'Billigheim Baden');
INSERT INTO `area_codes` VALUES ('49','06266',1,'Hassmersheim');
INSERT INTO `area_codes` VALUES ('49','06267',1,'Fahrenbach Baden');
INSERT INTO `area_codes` VALUES ('49','06268',1,'Hüffenhardt');
INSERT INTO `area_codes` VALUES ('49','06269',1,'Gundelsheim Württ');
INSERT INTO `area_codes` VALUES ('49','06271',1,'Eberbach Baden');
INSERT INTO `area_codes` VALUES ('49','06272',1,'Hirschhorn Neckar');
INSERT INTO `area_codes` VALUES ('49','06274',1,'Waldbrunn Odenw');
INSERT INTO `area_codes` VALUES ('49','06275',1,'Rothenberg Odenw');
INSERT INTO `area_codes` VALUES ('49','06276',1,'Hesseneck');
INSERT INTO `area_codes` VALUES ('49','06281',1,'Buchen Odenwald');
INSERT INTO `area_codes` VALUES ('49','06282',1,'Walldürn');
INSERT INTO `area_codes` VALUES ('49','06283',1,'Hardheim Odenw');
INSERT INTO `area_codes` VALUES ('49','06284',1,'Mudau');
INSERT INTO `area_codes` VALUES ('49','06285',1,'Walldürn-Altheim');
INSERT INTO `area_codes` VALUES ('49','06286',1,'Walldürn-Rippberg');
INSERT INTO `area_codes` VALUES ('49','06287',1,'Limbach Baden');
INSERT INTO `area_codes` VALUES ('49','06291',1,'Adelsheim');
INSERT INTO `area_codes` VALUES ('49','06292',1,'Seckach');
INSERT INTO `area_codes` VALUES ('49','06293',1,'Schefflenz');
INSERT INTO `area_codes` VALUES ('49','06294',1,'Krautheim Jagst');
INSERT INTO `area_codes` VALUES ('49','06295',1,'RosenbergBaden');
INSERT INTO `area_codes` VALUES ('49','06296',1,'Ahorn Baden');
INSERT INTO `area_codes` VALUES ('49','06297',1,'Ravenstein Baden');
INSERT INTO `area_codes` VALUES ('49','06298',1,'Möckmühl');
INSERT INTO `area_codes` VALUES ('49','06301',1,'Otterbach Pfalz');
INSERT INTO `area_codes` VALUES ('49','06302',1,'Winnweiler');
INSERT INTO `area_codes` VALUES ('49','06303',1,'Enkenbach-Alsenborn');
INSERT INTO `area_codes` VALUES ('49','06304',1,'Wolfstein Pfalz');
INSERT INTO `area_codes` VALUES ('49','06305',1,'Hochspeyer');
INSERT INTO `area_codes` VALUES ('49','06306',1,'Trippstadt');
INSERT INTO `area_codes` VALUES ('49','06307',1,'Schopp');
INSERT INTO `area_codes` VALUES ('49','06308',1,'Olsbrücken');
INSERT INTO `area_codes` VALUES ('49','0631',1,'Kaiserslautern');
INSERT INTO `area_codes` VALUES ('49','06321',1,'Neustadt an der Weinstraße');
INSERT INTO `area_codes` VALUES ('49','06322',1,'Bad Dürkheim');
INSERT INTO `area_codes` VALUES ('49','06323',1,'Edenkoben');
INSERT INTO `area_codes` VALUES ('49','06324',1,'Hassloch');
INSERT INTO `area_codes` VALUES ('49','06325',1,'Lambrecht Pfalz');
INSERT INTO `area_codes` VALUES ('49','06326',1,'Deidesheim');
INSERT INTO `area_codes` VALUES ('49','06327',1,'Neustadt-Lachen');
INSERT INTO `area_codes` VALUES ('49','06328',1,'Elmstein');
INSERT INTO `area_codes` VALUES ('49','06329',1,'Weidenthal Pfalz');
INSERT INTO `area_codes` VALUES ('49','06331',1,'Pirmasens');
INSERT INTO `area_codes` VALUES ('49','06332',1,'Zweibrücken');
INSERT INTO `area_codes` VALUES ('49','06333',1,'Waldfischbach-Burgalben');
INSERT INTO `area_codes` VALUES ('49','06334',1,'Thaleischweiler-Fröschen');
INSERT INTO `area_codes` VALUES ('49','06335',1,'Trulben');
INSERT INTO `area_codes` VALUES ('49','06336',1,'Dellfeld');
INSERT INTO `area_codes` VALUES ('49','06337',1,'Grossbundenbach');
INSERT INTO `area_codes` VALUES ('49','06338',1,'Hornbach Pfalz');
INSERT INTO `area_codes` VALUES ('49','06339',1,'Grosssteinhausen');
INSERT INTO `area_codes` VALUES ('49','06340',1,'Wörth-Schaidt');
INSERT INTO `area_codes` VALUES ('49','06341',1,'Landau in der Pfalz');
INSERT INTO `area_codes` VALUES ('49','06342',1,'Schweigen-Rechtenbach');
INSERT INTO `area_codes` VALUES ('49','06343',1,'Bad Bergzabern');
INSERT INTO `area_codes` VALUES ('49','06344',1,'Schwegenheim');
INSERT INTO `area_codes` VALUES ('49','06345',1,'Albersweiler');
INSERT INTO `area_codes` VALUES ('49','06346',1,'Annweiler am Trifels');
INSERT INTO `area_codes` VALUES ('49','06347',1,'Hochstadt Pfalz');
INSERT INTO `area_codes` VALUES ('49','06348',1,'Offenbach an der Queich');
INSERT INTO `area_codes` VALUES ('49','06349',1,'Billigheim-Ingenheim');
INSERT INTO `area_codes` VALUES ('49','06351',1,'Eisenberg Pfalz');
INSERT INTO `area_codes` VALUES ('49','06352',1,'Kirchheimbolanden');
INSERT INTO `area_codes` VALUES ('49','06353',1,'Freinsheim');
INSERT INTO `area_codes` VALUES ('49','06355',1,'Albisheim Pfrimm');
INSERT INTO `area_codes` VALUES ('49','06356',1,'Carlsberg Pfalz');
INSERT INTO `area_codes` VALUES ('49','06357',1,'Standenbühl');
INSERT INTO `area_codes` VALUES ('49','06358',1,'Kriegsfeld');
INSERT INTO `area_codes` VALUES ('49','06359',1,'Grünstadt');
INSERT INTO `area_codes` VALUES ('49','06361',1,'Rockenhausen');
INSERT INTO `area_codes` VALUES ('49','06362',1,'Alsenz');
INSERT INTO `area_codes` VALUES ('49','06363',1,'Niederkirchen');
INSERT INTO `area_codes` VALUES ('49','06364',1,'Nußbach Pfalz');
INSERT INTO `area_codes` VALUES ('49','06371',1,'Landstuhl');
INSERT INTO `area_codes` VALUES ('49','06372',1,'Bruchmühlbach-Miesau');
INSERT INTO `area_codes` VALUES ('49','06373',1,'Schönenberg-Kübelberg');
INSERT INTO `area_codes` VALUES ('49','06374',1,'Weilerbach');
INSERT INTO `area_codes` VALUES ('49','06375',1,'Wallhalben');
INSERT INTO `area_codes` VALUES ('49','06381',1,'Kusel');
INSERT INTO `area_codes` VALUES ('49','06382',1,'Lauterecken');
INSERT INTO `area_codes` VALUES ('49','06383',1,'Glan-Münchweiler');
INSERT INTO `area_codes` VALUES ('49','06384',1,'Konken');
INSERT INTO `area_codes` VALUES ('49','06385',1,'Reichenbach-Steegen');
INSERT INTO `area_codes` VALUES ('49','06386',1,'Altenkirchen Pfalz');
INSERT INTO `area_codes` VALUES ('49','06387',1,'Sankt Julian');
INSERT INTO `area_codes` VALUES ('49','06391',1,'Dahn');
INSERT INTO `area_codes` VALUES ('49','06392',1,'Hauenstein Pfalz');
INSERT INTO `area_codes` VALUES ('49','06393',1,'Fischbach bei Dahn');
INSERT INTO `area_codes` VALUES ('49','06394',1,'Bundenthal');
INSERT INTO `area_codes` VALUES ('49','06395',1,'Münchweiler an der Rodalb');
INSERT INTO `area_codes` VALUES ('49','06396',1,'Hinterweidenthal');
INSERT INTO `area_codes` VALUES ('49','06397',1,'Leimen Pfalz');
INSERT INTO `area_codes` VALUES ('49','06398',1,'Vorderweidenthal');
INSERT INTO `area_codes` VALUES ('49','06400',1,'Mücke');
INSERT INTO `area_codes` VALUES ('49','06401',1,'Grünberg Hess');
INSERT INTO `area_codes` VALUES ('49','06402',1,'Hungen');
INSERT INTO `area_codes` VALUES ('49','06403',1,'Linden Hess');
INSERT INTO `area_codes` VALUES ('49','06404',1,'Lich Hess');
INSERT INTO `area_codes` VALUES ('49','06405',1,'Laubach Hess');
INSERT INTO `area_codes` VALUES ('49','06406',1,'Lollar');
INSERT INTO `area_codes` VALUES ('49','06407',1,'Rabenau Hess');
INSERT INTO `area_codes` VALUES ('49','06408',1,'Buseck');
INSERT INTO `area_codes` VALUES ('49','06409',1,'Biebertal');
INSERT INTO `area_codes` VALUES ('49','0641',1,'Giessen');
INSERT INTO `area_codes` VALUES ('49','06420',1,'Lahntal');
INSERT INTO `area_codes` VALUES ('49','06421',1,'Marburg');
INSERT INTO `area_codes` VALUES ('49','06422',1,'Kirchhain');
INSERT INTO `area_codes` VALUES ('49','06423',1,'Wetter Hessen');
INSERT INTO `area_codes` VALUES ('49','06424',1,'Ebsdorfergrund');
INSERT INTO `area_codes` VALUES ('49','06425',1,'Rauschenberg Hess');
INSERT INTO `area_codes` VALUES ('49','06426',1,'Fronhausen');
INSERT INTO `area_codes` VALUES ('49','06427',1,'Cölbe-Schönstadt');
INSERT INTO `area_codes` VALUES ('49','06428',1,'Stadtallendorf');
INSERT INTO `area_codes` VALUES ('49','06429',1,'Schweinsberg Hess');
INSERT INTO `area_codes` VALUES ('49','06430',1,'Hahnstätten');
INSERT INTO `area_codes` VALUES ('49','06431',1,'Limburg a d Lahn');
INSERT INTO `area_codes` VALUES ('49','06432',1,'Diez');
INSERT INTO `area_codes` VALUES ('49','06433',1,'Hadamar');
INSERT INTO `area_codes` VALUES ('49','06434',1,'Bad Camberg');
INSERT INTO `area_codes` VALUES ('49','06435',1,'Wallmerod');
INSERT INTO `area_codes` VALUES ('49','06436',1,'Dornburg Hess');
INSERT INTO `area_codes` VALUES ('49','06438',1,'Hünfelden');
INSERT INTO `area_codes` VALUES ('49','06439',1,'Holzappel');
INSERT INTO `area_codes` VALUES ('49','06440',1,'Kölschhausen');
INSERT INTO `area_codes` VALUES ('49','06441',1,'Wetzlar');
INSERT INTO `area_codes` VALUES ('49','06442',1,'Braunfels');
INSERT INTO `area_codes` VALUES ('49','06443',1,'Ehringshausen Dill');
INSERT INTO `area_codes` VALUES ('49','06444',1,'Bischoffen');
INSERT INTO `area_codes` VALUES ('49','06445',1,'Schöffengrund');
INSERT INTO `area_codes` VALUES ('49','06446',1,'Hohenahr');
INSERT INTO `area_codes` VALUES ('49','06447',1,'Langgöns-Niederkleen');
INSERT INTO `area_codes` VALUES ('49','06449',1,'Ehringshausen-Katzenfurt');
INSERT INTO `area_codes` VALUES ('49','06451',1,'Frankenberg Eder');
INSERT INTO `area_codes` VALUES ('49','06452',1,'Battenberg Eder');
INSERT INTO `area_codes` VALUES ('49','06453',1,'Gemünden Wohra');
INSERT INTO `area_codes` VALUES ('49','06454',1,'Lichtenfels-Sachsenberg');
INSERT INTO `area_codes` VALUES ('49','06455',1,'Frankenau Hess');
INSERT INTO `area_codes` VALUES ('49','06456',1,'Haina Kloster');
INSERT INTO `area_codes` VALUES ('49','06457',1,'Burgwald Eder');
INSERT INTO `area_codes` VALUES ('49','06458',1,'Rosenthal Hess');
INSERT INTO `area_codes` VALUES ('49','06461',1,'Biedenkopf');
INSERT INTO `area_codes` VALUES ('49','06462',1,'Gladenbach');
INSERT INTO `area_codes` VALUES ('49','06464',1,'Angelburg');
INSERT INTO `area_codes` VALUES ('49','06465',1,'Breidenbach b Biedenkopf');
INSERT INTO `area_codes` VALUES ('49','06466',1,'Dautphetal-Friedensdorf');
INSERT INTO `area_codes` VALUES ('49','06467',1,'Hatzfeld Eder');
INSERT INTO `area_codes` VALUES ('49','06468',1,'Dautphetal-Mornshausen');
INSERT INTO `area_codes` VALUES ('49','06471',1,'Weilburg');
INSERT INTO `area_codes` VALUES ('49','06472',1,'Weilmünster');
INSERT INTO `area_codes` VALUES ('49','06473',1,'Leun');
INSERT INTO `area_codes` VALUES ('49','06474',1,'Villmar-Aumenau');
INSERT INTO `area_codes` VALUES ('49','06475',1,'Weilmünster-Wolfenhausen');
INSERT INTO `area_codes` VALUES ('49','06476',1,'Mengerskirchen');
INSERT INTO `area_codes` VALUES ('49','06477',1,'Greifenstein-Nenderoth');
INSERT INTO `area_codes` VALUES ('49','06478',1,'Greifenstein-Ulm');
INSERT INTO `area_codes` VALUES ('49','06479',1,'Waldbrunn Westerwald');
INSERT INTO `area_codes` VALUES ('49','06482',1,'Runkel');
INSERT INTO `area_codes` VALUES ('49','06483',1,'Selters Taunus');
INSERT INTO `area_codes` VALUES ('49','06484',1,'Beselich');
INSERT INTO `area_codes` VALUES ('49','06485',1,'Nentershausen Westerw');
INSERT INTO `area_codes` VALUES ('49','06486',1,'Katzenelnbogen');
INSERT INTO `area_codes` VALUES ('49','06500',1,'Waldrach');
INSERT INTO `area_codes` VALUES ('49','06501',1,'Konz');
INSERT INTO `area_codes` VALUES ('49','06502',1,'Schweich');
INSERT INTO `area_codes` VALUES ('49','06503',1,'Hermeskeil');
INSERT INTO `area_codes` VALUES ('49','06504',1,'Thalfang');
INSERT INTO `area_codes` VALUES ('49','06505',1,'Kordel');
INSERT INTO `area_codes` VALUES ('49','06506',1,'Welschbillig');
INSERT INTO `area_codes` VALUES ('49','06507',1,'Neumagen-Dhron');
INSERT INTO `area_codes` VALUES ('49','06508',1,'Hetzerath Mosel');
INSERT INTO `area_codes` VALUES ('49','06509',1,'Büdlich');
INSERT INTO `area_codes` VALUES ('49','0651',1,'Trier');
INSERT INTO `area_codes` VALUES ('49','06522',1,'Mettendorf');
INSERT INTO `area_codes` VALUES ('49','06523',1,'Holsthum');
INSERT INTO `area_codes` VALUES ('49','06524',1,'Rodershausen');
INSERT INTO `area_codes` VALUES ('49','06525',1,'Irrel');
INSERT INTO `area_codes` VALUES ('49','06526',1,'Bollendorf');
INSERT INTO `area_codes` VALUES ('49','06527',1,'Oberweis');
INSERT INTO `area_codes` VALUES ('49','06531',1,'Bernkastel-Kues');
INSERT INTO `area_codes` VALUES ('49','06532',1,'Zeltingen-Rachtig');
INSERT INTO `area_codes` VALUES ('49','06533',1,'Morbach Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06534',1,'Mülheim Mosel');
INSERT INTO `area_codes` VALUES ('49','06535',1,'Osann-Monzel');
INSERT INTO `area_codes` VALUES ('49','06536',1,'Kleinich');
INSERT INTO `area_codes` VALUES ('49','06541',1,'Traben-Trarbach');
INSERT INTO `area_codes` VALUES ('49','06542',1,'Bullay');
INSERT INTO `area_codes` VALUES ('49','06543',1,'Büchenbeuren');
INSERT INTO `area_codes` VALUES ('49','06544',1,'Rhaunen');
INSERT INTO `area_codes` VALUES ('49','06545',1,'Blankenrath');
INSERT INTO `area_codes` VALUES ('49','06550',1,'Irrhausen');
INSERT INTO `area_codes` VALUES ('49','06551',1,'Prüm');
INSERT INTO `area_codes` VALUES ('49','06552',1,'Olzheim');
INSERT INTO `area_codes` VALUES ('49','06553',1,'Schönecken');
INSERT INTO `area_codes` VALUES ('49','06554',1,'Waxweiler');
INSERT INTO `area_codes` VALUES ('49','06555',1,'Bleialf');
INSERT INTO `area_codes` VALUES ('49','06556',1,'Pronsfeld');
INSERT INTO `area_codes` VALUES ('49','06557',1,'Hallschlag');
INSERT INTO `area_codes` VALUES ('49','06558',1,'Büdesheim Eifel');
INSERT INTO `area_codes` VALUES ('49','06559',1,'Leidenborn');
INSERT INTO `area_codes` VALUES ('49','06561',1,'Bitburg');
INSERT INTO `area_codes` VALUES ('49','06562',1,'Speicher');
INSERT INTO `area_codes` VALUES ('49','06563',1,'Kyllburg');
INSERT INTO `area_codes` VALUES ('49','06564',1,'Neuerburg Eifel');
INSERT INTO `area_codes` VALUES ('49','06565',1,'Dudeldorf');
INSERT INTO `area_codes` VALUES ('49','06566',1,'Körperich');
INSERT INTO `area_codes` VALUES ('49','06567',1,'Oberkail');
INSERT INTO `area_codes` VALUES ('49','06568',1,'Wolsfeld');
INSERT INTO `area_codes` VALUES ('49','06569',1,'Bickendorf');
INSERT INTO `area_codes` VALUES ('49','06571',1,'Wittlich');
INSERT INTO `area_codes` VALUES ('49','06572',1,'Manderscheid Eifel');
INSERT INTO `area_codes` VALUES ('49','06573',1,'Gillenfeld');
INSERT INTO `area_codes` VALUES ('49','06574',1,'Hasborn');
INSERT INTO `area_codes` VALUES ('49','06575',1,'Landscheid');
INSERT INTO `area_codes` VALUES ('49','06578',1,'Salmtal');
INSERT INTO `area_codes` VALUES ('49','06580',1,'Zemmer');
INSERT INTO `area_codes` VALUES ('49','06581',1,'Saarburg');
INSERT INTO `area_codes` VALUES ('49','06582',1,'Freudenburg');
INSERT INTO `area_codes` VALUES ('49','06583',1,'Palzem');
INSERT INTO `area_codes` VALUES ('49','06584',1,'Wellen Mosel');
INSERT INTO `area_codes` VALUES ('49','06585',1,'Ralingen');
INSERT INTO `area_codes` VALUES ('49','06586',1,'Beuren Hochwald');
INSERT INTO `area_codes` VALUES ('49','06587',1,'Zerf');
INSERT INTO `area_codes` VALUES ('49','06588',1,'Pluwig');
INSERT INTO `area_codes` VALUES ('49','06589',1,'Kell am See');
INSERT INTO `area_codes` VALUES ('49','06591',1,'Gerolstein');
INSERT INTO `area_codes` VALUES ('49','06592',1,'Daun');
INSERT INTO `area_codes` VALUES ('49','06593',1,'Hillesheim Eifel');
INSERT INTO `area_codes` VALUES ('49','06594',1,'Birresborn');
INSERT INTO `area_codes` VALUES ('49','06595',1,'Dockweiler');
INSERT INTO `area_codes` VALUES ('49','06596',1,'Üdersdorf');
INSERT INTO `area_codes` VALUES ('49','06597',1,'Jünkerath');
INSERT INTO `area_codes` VALUES ('49','06599',1,'Weidenbach b Gerolstein');
INSERT INTO `area_codes` VALUES ('49','0661',1,'Fulda');
INSERT INTO `area_codes` VALUES ('49','06620',1,'Philippsthal Werra');
INSERT INTO `area_codes` VALUES ('49','06621',1,'Bad Hersfeld');
INSERT INTO `area_codes` VALUES ('49','06622',1,'Bebra');
INSERT INTO `area_codes` VALUES ('49','06623',1,'Rotenburg a d Fulda');
INSERT INTO `area_codes` VALUES ('49','06624',1,'Heringen Werra');
INSERT INTO `area_codes` VALUES ('49','06625',1,'Niederaula');
INSERT INTO `area_codes` VALUES ('49','06626',1,'Wildeck-Obersuhl');
INSERT INTO `area_codes` VALUES ('49','06627',1,'Nentershausen Hess');
INSERT INTO `area_codes` VALUES ('49','06628',1,'Oberaula');
INSERT INTO `area_codes` VALUES ('49','06629',1,'Schenklengsfeld');
INSERT INTO `area_codes` VALUES ('49','06630',1,'Schwalmtal-Storndorf');
INSERT INTO `area_codes` VALUES ('49','06631',1,'Alsfeld');
INSERT INTO `area_codes` VALUES ('49','06633',1,'Homberg Ohm');
INSERT INTO `area_codes` VALUES ('49','06634',1,'Gemünden Felda');
INSERT INTO `area_codes` VALUES ('49','06635',1,'Kirtorf');
INSERT INTO `area_codes` VALUES ('49','06636',1,'Romrod');
INSERT INTO `area_codes` VALUES ('49','06637',1,'Feldatal');
INSERT INTO `area_codes` VALUES ('49','06638',1,'Schwalmtal-Renzendorf');
INSERT INTO `area_codes` VALUES ('49','06639',1,'Ottrau');
INSERT INTO `area_codes` VALUES ('49','06641',1,'Lauterbach Hessen');
INSERT INTO `area_codes` VALUES ('49','06642',1,'Schlitz');
INSERT INTO `area_codes` VALUES ('49','06643',1,'Herbstein');
INSERT INTO `area_codes` VALUES ('49','06644',1,'Grebenhain');
INSERT INTO `area_codes` VALUES ('49','06645',1,'Ulrichstein');
INSERT INTO `area_codes` VALUES ('49','06646',1,'Grebenau');
INSERT INTO `area_codes` VALUES ('49','06647',1,'Herbstein-Stockhausen');
INSERT INTO `area_codes` VALUES ('49','06648',1,'Bad Salzschlirf');
INSERT INTO `area_codes` VALUES ('49','06650',1,'Hosenfeld');
INSERT INTO `area_codes` VALUES ('49','06651',1,'Rasdorf');
INSERT INTO `area_codes` VALUES ('49','06652',1,'Hünfeld');
INSERT INTO `area_codes` VALUES ('49','06653',1,'Burghaun');
INSERT INTO `area_codes` VALUES ('49','06654',1,'Gersfeld Rhön');
INSERT INTO `area_codes` VALUES ('49','06655',1,'Neuhof Kr Fulda');
INSERT INTO `area_codes` VALUES ('49','06656',1,'Ebersburg');
INSERT INTO `area_codes` VALUES ('49','06657',1,'Hofbieber');
INSERT INTO `area_codes` VALUES ('49','06658',1,'Poppenhausen Wasserkuppe');
INSERT INTO `area_codes` VALUES ('49','06659',1,'Eichenzell');
INSERT INTO `area_codes` VALUES ('49','06660',1,'Steinau-Marjoss');
INSERT INTO `area_codes` VALUES ('49','06661',1,'Schlüchtern');
INSERT INTO `area_codes` VALUES ('49','06663',1,'Steinau an der Straße');
INSERT INTO `area_codes` VALUES ('49','06664',1,'Sinntal-Sterbfritz');
INSERT INTO `area_codes` VALUES ('49','06665',1,'Sinntal-Altengronau');
INSERT INTO `area_codes` VALUES ('49','06666',1,'Freiensteinau');
INSERT INTO `area_codes` VALUES ('49','06667',1,'Steinau-Ulmbach');
INSERT INTO `area_codes` VALUES ('49','06668',1,'Birstein-Lichenroth');
INSERT INTO `area_codes` VALUES ('49','06669',1,'Neuhof-Hauswurz');
INSERT INTO `area_codes` VALUES ('49','06670',1,'Ludwigsau Hess');
INSERT INTO `area_codes` VALUES ('49','06672',1,'Eiterfeld');
INSERT INTO `area_codes` VALUES ('49','06673',1,'Haunetal');
INSERT INTO `area_codes` VALUES ('49','06674',1,'Friedewald Hess');
INSERT INTO `area_codes` VALUES ('49','06675',1,'Breitenbach a Herzberg');
INSERT INTO `area_codes` VALUES ('49','06676',1,'Hohenroda Hess');
INSERT INTO `area_codes` VALUES ('49','06677',1,'Neuenstein Hess');
INSERT INTO `area_codes` VALUES ('49','06678',1,'Wildeck-Hönebach');
INSERT INTO `area_codes` VALUES ('49','06681',1,'Hilders');
INSERT INTO `area_codes` VALUES ('49','06682',1,'Tann Rhön');
INSERT INTO `area_codes` VALUES ('49','06683',1,'Ehrenberg Rhön');
INSERT INTO `area_codes` VALUES ('49','06684',1,'Hofbieber-Schwarzbach');
INSERT INTO `area_codes` VALUES ('49','06691',1,'Schwalmstadt');
INSERT INTO `area_codes` VALUES ('49','06692',1,'Neustadt Hessen');
INSERT INTO `area_codes` VALUES ('49','06693',1,'Neuental');
INSERT INTO `area_codes` VALUES ('49','06694',1,'Neukirchen Knüll');
INSERT INTO `area_codes` VALUES ('49','06695',1,'Jesberg');
INSERT INTO `area_codes` VALUES ('49','06696',1,'Gilserberg');
INSERT INTO `area_codes` VALUES ('49','06697',1,'Willingshausen');
INSERT INTO `area_codes` VALUES ('49','06698',1,'Schrecksbach');
INSERT INTO `area_codes` VALUES ('49','06701',1,'Sprendlingen Rheinhess');
INSERT INTO `area_codes` VALUES ('49','06703',1,'Wöllstein Rheinhess');
INSERT INTO `area_codes` VALUES ('49','06704',1,'Langenlonsheim');
INSERT INTO `area_codes` VALUES ('49','06706',1,'Wallhausen Nahe');
INSERT INTO `area_codes` VALUES ('49','06707',1,'Windesheim');
INSERT INTO `area_codes` VALUES ('49','06708',1,'Bad Münster am Stein-Ebernburg');
INSERT INTO `area_codes` VALUES ('49','06709',1,'Fürfeld Kr Bad Kreuznach');
INSERT INTO `area_codes` VALUES ('49','0671',1,'Bad Kreuznach');
INSERT INTO `area_codes` VALUES ('49','06721',1,'Bingen am Rhein');
INSERT INTO `area_codes` VALUES ('49','06722',1,'Rüdesheim am Rhein');
INSERT INTO `area_codes` VALUES ('49','06723',1,'Oestrich-Winkel');
INSERT INTO `area_codes` VALUES ('49','06724',1,'Stromberg Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06725',1,'Gau-Algesheim');
INSERT INTO `area_codes` VALUES ('49','06726',1,'Lorch Rheingau');
INSERT INTO `area_codes` VALUES ('49','06727',1,'Gensingen');
INSERT INTO `area_codes` VALUES ('49','06728',1,'Ober-Hilbersheim');
INSERT INTO `area_codes` VALUES ('49','06731',1,'Alzey');
INSERT INTO `area_codes` VALUES ('49','06732',1,'Wörrstadt');
INSERT INTO `area_codes` VALUES ('49','06733',1,'Gau-Odernheim');
INSERT INTO `area_codes` VALUES ('49','06734',1,'Flonheim');
INSERT INTO `area_codes` VALUES ('49','06735',1,'Eppelsheim');
INSERT INTO `area_codes` VALUES ('49','06736',1,'Bechenheim');
INSERT INTO `area_codes` VALUES ('49','06737',1,'Köngernheim');
INSERT INTO `area_codes` VALUES ('49','06741',1,'St Goar');
INSERT INTO `area_codes` VALUES ('49','06742',1,'Boppard');
INSERT INTO `area_codes` VALUES ('49','06743',1,'Bacharach');
INSERT INTO `area_codes` VALUES ('49','06744',1,'Oberwesel');
INSERT INTO `area_codes` VALUES ('49','06745',1,'Gondershausen');
INSERT INTO `area_codes` VALUES ('49','06746',1,'Pfalzfeld');
INSERT INTO `area_codes` VALUES ('49','06747',1,'Emmelshausen');
INSERT INTO `area_codes` VALUES ('49','06751',1,'Bad Sobernheim');
INSERT INTO `area_codes` VALUES ('49','06752',1,'Kirn Nahe');
INSERT INTO `area_codes` VALUES ('49','06753',1,'Meisenheim');
INSERT INTO `area_codes` VALUES ('49','06754',1,'Martinstein');
INSERT INTO `area_codes` VALUES ('49','06755',1,'Odernheim am Glan');
INSERT INTO `area_codes` VALUES ('49','06756',1,'Winterbach Soonwald');
INSERT INTO `area_codes` VALUES ('49','06757',1,'Becherbach bei Kirn');
INSERT INTO `area_codes` VALUES ('49','06758',1,'Waldböckelheim');
INSERT INTO `area_codes` VALUES ('49','06761',1,'Simmern Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06762',1,'Kastellaun');
INSERT INTO `area_codes` VALUES ('49','06763',1,'Kirchberg Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06764',1,'Rheinböllen');
INSERT INTO `area_codes` VALUES ('49','06765',1,'Gemünden Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06766',1,'Kisselbach');
INSERT INTO `area_codes` VALUES ('49','06771',1,'St Goarshausen');
INSERT INTO `area_codes` VALUES ('49','06772',1,'Nastätten');
INSERT INTO `area_codes` VALUES ('49','06773',1,'Kamp-Bornhofen');
INSERT INTO `area_codes` VALUES ('49','06774',1,'Kaub');
INSERT INTO `area_codes` VALUES ('49','06775',1,'Strüth Taunus');
INSERT INTO `area_codes` VALUES ('49','06776',1,'Dachsenhausen');
INSERT INTO `area_codes` VALUES ('49','06781',1,'Idar-Oberstein');
INSERT INTO `area_codes` VALUES ('49','06782',1,'Birkenfeld Nahe');
INSERT INTO `area_codes` VALUES ('49','06783',1,'Baumholder');
INSERT INTO `area_codes` VALUES ('49','06784',1,'Weierbach');
INSERT INTO `area_codes` VALUES ('49','06785',1,'Herrstein');
INSERT INTO `area_codes` VALUES ('49','06786',1,'Kempfeld');
INSERT INTO `area_codes` VALUES ('49','06787',1,'Niederbrombach');
INSERT INTO `area_codes` VALUES ('49','06788',1,'Sien');
INSERT INTO `area_codes` VALUES ('49','06789',1,'Heimbach Nahe');
INSERT INTO `area_codes` VALUES ('49','06802',1,'Völklingen-Lauterbach');
INSERT INTO `area_codes` VALUES ('49','06803',1,'Mandelbachtal-Ommersheim');
INSERT INTO `area_codes` VALUES ('49','06804',1,'Mandelbachtal');
INSERT INTO `area_codes` VALUES ('49','06805',1,'Kleinblittersdorf');
INSERT INTO `area_codes` VALUES ('49','06806',1,'Heusweiler');
INSERT INTO `area_codes` VALUES ('49','06809',1,'Grossrosseln');
INSERT INTO `area_codes` VALUES ('49','0681',1,'Saarbrücken');
INSERT INTO `area_codes` VALUES ('49','06821',1,'Neunkirchen Saar');
INSERT INTO `area_codes` VALUES ('49','06824',1,'Ottweiler');
INSERT INTO `area_codes` VALUES ('49','06825',1,'Illingen Saar');
INSERT INTO `area_codes` VALUES ('49','06826',1,'Bexbach');
INSERT INTO `area_codes` VALUES ('49','06827',1,'Eppelborn');
INSERT INTO `area_codes` VALUES ('49','06831',1,'Saarlouis');
INSERT INTO `area_codes` VALUES ('49','06832',1,'Beckingen-Reimsbach');
INSERT INTO `area_codes` VALUES ('49','06833',1,'Rehlingen-Siersburg');
INSERT INTO `area_codes` VALUES ('49','06834',1,'Bous');
INSERT INTO `area_codes` VALUES ('49','06835',1,'Beckingen');
INSERT INTO `area_codes` VALUES ('49','06836',1,'Überherrn');
INSERT INTO `area_codes` VALUES ('49','06837',1,'Wallerfangen');
INSERT INTO `area_codes` VALUES ('49','06838',1,'Saarwellingen');
INSERT INTO `area_codes` VALUES ('49','06841',1,'Homburg Saar');
INSERT INTO `area_codes` VALUES ('49','06842',1,'Blieskastel');
INSERT INTO `area_codes` VALUES ('49','06843',1,'Gersheim');
INSERT INTO `area_codes` VALUES ('49','06844',1,'Blieskastel-Altheim');
INSERT INTO `area_codes` VALUES ('49','06848',1,'Homburg-Einöd');
INSERT INTO `area_codes` VALUES ('49','06849',1,'Kirkel');
INSERT INTO `area_codes` VALUES ('49','06851',1,'St Wendel');
INSERT INTO `area_codes` VALUES ('49','06852',1,'Nohfelden');
INSERT INTO `area_codes` VALUES ('49','06853',1,'Marpingen');
INSERT INTO `area_codes` VALUES ('49','06854',1,'Oberthal Saar');
INSERT INTO `area_codes` VALUES ('49','06855',1,'Freisen');
INSERT INTO `area_codes` VALUES ('49','06856',1,'St Wendel-Niederkirchen');
INSERT INTO `area_codes` VALUES ('49','06857',1,'Namborn');
INSERT INTO `area_codes` VALUES ('49','06858',1,'Ottweiler-Fürth');
INSERT INTO `area_codes` VALUES ('49','06861',1,'Merzig');
INSERT INTO `area_codes` VALUES ('49','06864',1,'Mettlach');
INSERT INTO `area_codes` VALUES ('49','06865',1,'Mettlach-Orscholz');
INSERT INTO `area_codes` VALUES ('49','06866',1,'Perl-Nennig');
INSERT INTO `area_codes` VALUES ('49','06867',1,'Perl');
INSERT INTO `area_codes` VALUES ('49','06868',1,'Mettlach-Tünsdorf');
INSERT INTO `area_codes` VALUES ('49','06869',1,'Merzig-Silwingen');
INSERT INTO `area_codes` VALUES ('49','06871',1,'Wadern');
INSERT INTO `area_codes` VALUES ('49','06872',1,'Losheim am See');
INSERT INTO `area_codes` VALUES ('49','06873',1,'Nonnweiler');
INSERT INTO `area_codes` VALUES ('49','06874',1,'Wadern-Nunkirchen');
INSERT INTO `area_codes` VALUES ('49','06875',1,'Nonnweiler-Primstal');
INSERT INTO `area_codes` VALUES ('49','06876',1,'Weiskirchen Saar');
INSERT INTO `area_codes` VALUES ('49','06881',1,'Lebach');
INSERT INTO `area_codes` VALUES ('49','06887',1,'Schmelz Saar');
INSERT INTO `area_codes` VALUES ('49','06888',1,'Lebach-Steinbach');
INSERT INTO `area_codes` VALUES ('49','06893',1,'Saarbrücken-Ensheim');
INSERT INTO `area_codes` VALUES ('49','06894',1,'St Ingbert');
INSERT INTO `area_codes` VALUES ('49','06897',1,'Sulzbach Saar');
INSERT INTO `area_codes` VALUES ('49','06898',1,'Völklingen');
INSERT INTO `area_codes` VALUES ('49','069',1,'Frankfurt am Main');
INSERT INTO `area_codes` VALUES ('49','0700',0,'Persönliche Rufnummern');
INSERT INTO `area_codes` VALUES ('49','0701',0,'(Reserve für Persönliche Rufnummern)');
INSERT INTO `area_codes` VALUES ('49','07021',1,'Kirchheim unter Teck');
INSERT INTO `area_codes` VALUES ('49','07022',1,'Nürtingen');
INSERT INTO `area_codes` VALUES ('49','07023',1,'Weilheim an der Teck');
INSERT INTO `area_codes` VALUES ('49','07024',1,'Wendlingen am Neckar');
INSERT INTO `area_codes` VALUES ('49','07025',1,'Neuffen');
INSERT INTO `area_codes` VALUES ('49','07026',1,'Lenningen');
INSERT INTO `area_codes` VALUES ('49','07031',1,'Böblingen');
INSERT INTO `area_codes` VALUES ('49','07032',1,'Herrenberg');
INSERT INTO `area_codes` VALUES ('49','07033',1,'Weil Der Stadt');
INSERT INTO `area_codes` VALUES ('49','07034',1,'Ehningen');
INSERT INTO `area_codes` VALUES ('49','07041',1,'Mühlacker');
INSERT INTO `area_codes` VALUES ('49','07042',1,'Vaihingen an der Enz');
INSERT INTO `area_codes` VALUES ('49','07043',1,'Maulbronn');
INSERT INTO `area_codes` VALUES ('49','07044',1,'Mönsheim');
INSERT INTO `area_codes` VALUES ('49','07045',1,'Oberderdingen');
INSERT INTO `area_codes` VALUES ('49','07046',1,'Zaberfeld');
INSERT INTO `area_codes` VALUES ('49','07051',1,'Calw');
INSERT INTO `area_codes` VALUES ('49','07052',1,'Bad Liebenzell');
INSERT INTO `area_codes` VALUES ('49','07053',1,'Bad Teinach-Zavelstein');
INSERT INTO `area_codes` VALUES ('49','07054',1,'Wildberg Württ');
INSERT INTO `area_codes` VALUES ('49','07055',1,'Neuweiler Kr Calw');
INSERT INTO `area_codes` VALUES ('49','07056',1,'Gechingen');
INSERT INTO `area_codes` VALUES ('49','07062',1,'Beilstein Württ');
INSERT INTO `area_codes` VALUES ('49','07063',1,'Bad Wimpfen');
INSERT INTO `area_codes` VALUES ('49','07066',1,'Bad Rappenau-Bonfeld');
INSERT INTO `area_codes` VALUES ('49','07071',1,'Tübingen');
INSERT INTO `area_codes` VALUES ('49','07072',1,'Gomaringen');
INSERT INTO `area_codes` VALUES ('49','07073',1,'Ammerbuch');
INSERT INTO `area_codes` VALUES ('49','07081',1,'Bad Wildbad');
INSERT INTO `area_codes` VALUES ('49','07082',1,'Neuenbürg Württ');
INSERT INTO `area_codes` VALUES ('49','07083',1,'Bad Herrenalb');
INSERT INTO `area_codes` VALUES ('49','07084',1,'Schömberg b Neuenbürg');
INSERT INTO `area_codes` VALUES ('49','07085',1,'Enzklösterle');
INSERT INTO `area_codes` VALUES ('49','0711',1,'Stuttgart');
INSERT INTO `area_codes` VALUES ('49','07121',1,'Reutlingen');
INSERT INTO `area_codes` VALUES ('49','07122',1,'St Johann Württ');
INSERT INTO `area_codes` VALUES ('49','07123',1,'Metzingen Württ');
INSERT INTO `area_codes` VALUES ('49','07124',1,'Trochtelfingen Hohenz');
INSERT INTO `area_codes` VALUES ('49','07125',1,'Bad Urach');
INSERT INTO `area_codes` VALUES ('49','07126',1,'Burladingen-Melchingen');
INSERT INTO `area_codes` VALUES ('49','07127',1,'Neckartenzlingen');
INSERT INTO `area_codes` VALUES ('49','07128',1,'Sonnenbühl');
INSERT INTO `area_codes` VALUES ('49','07129',1,'Lichtenstein Württ');
INSERT INTO `area_codes` VALUES ('49','07130',1,'Löwenstein Württ');
INSERT INTO `area_codes` VALUES ('49','07131',1,'Heilbronn Neckar');
INSERT INTO `area_codes` VALUES ('49','07132',1,'Neckarsulm');
INSERT INTO `area_codes` VALUES ('49','07133',1,'Lauffen am Neckar');
INSERT INTO `area_codes` VALUES ('49','07134',1,'Weinsberg');
INSERT INTO `area_codes` VALUES ('49','07135',1,'Brackenheim');
INSERT INTO `area_codes` VALUES ('49','07136',1,'Bad Friedrichshall');
INSERT INTO `area_codes` VALUES ('49','07138',1,'Schwaigern');
INSERT INTO `area_codes` VALUES ('49','07139',1,'Neuenstadt am Kocher');
INSERT INTO `area_codes` VALUES ('49','07141',1,'Ludwigsburg Württ');
INSERT INTO `area_codes` VALUES ('49','07142',1,'Bietigheim-Bissingen');
INSERT INTO `area_codes` VALUES ('49','07143',1,'Besigheim');
INSERT INTO `area_codes` VALUES ('49','07144',1,'Marbach am Neckar');
INSERT INTO `area_codes` VALUES ('49','07145',1,'Markgröningen');
INSERT INTO `area_codes` VALUES ('49','07146',1,'Remseck am Neckar');
INSERT INTO `area_codes` VALUES ('49','07147',1,'Sachsenheim Württ');
INSERT INTO `area_codes` VALUES ('49','07148',1,'Grossbottwar');
INSERT INTO `area_codes` VALUES ('49','07150',1,'Korntal-Münchingen');
INSERT INTO `area_codes` VALUES ('49','07151',1,'Waiblingen');
INSERT INTO `area_codes` VALUES ('49','07152',1,'Leonberg Württ');
INSERT INTO `area_codes` VALUES ('49','07153',1,'Plochingen');
INSERT INTO `area_codes` VALUES ('49','07154',1,'Kornwestheim');
INSERT INTO `area_codes` VALUES ('49','07156',1,'Ditzingen');
INSERT INTO `area_codes` VALUES ('49','07157',1,'Waldenbuch');
INSERT INTO `area_codes` VALUES ('49','07158',1,'Neuhausen auf den Fildern');
INSERT INTO `area_codes` VALUES ('49','07159',1,'Renningen');
INSERT INTO `area_codes` VALUES ('49','07161',1,'Göppingen');
INSERT INTO `area_codes` VALUES ('49','07162',1,'Süßen');
INSERT INTO `area_codes` VALUES ('49','07163',1,'Ebersbach an der Fils');
INSERT INTO `area_codes` VALUES ('49','07164',1,'Boll Kr Göppingen');
INSERT INTO `area_codes` VALUES ('49','07165',1,'Göppingen-Hohenstaufen');
INSERT INTO `area_codes` VALUES ('49','07166',1,'Adelberg');
INSERT INTO `area_codes` VALUES ('49','07171',1,'Schwäbisch Gmünd');
INSERT INTO `area_codes` VALUES ('49','07172',1,'Lorch Württ');
INSERT INTO `area_codes` VALUES ('49','07173',1,'Heubach');
INSERT INTO `area_codes` VALUES ('49','07174',1,'Mögglingen');
INSERT INTO `area_codes` VALUES ('49','07175',1,'Leinzell');
INSERT INTO `area_codes` VALUES ('49','07176',1,'Spraitbach');
INSERT INTO `area_codes` VALUES ('49','07181',1,'Schorndorf Württ');
INSERT INTO `area_codes` VALUES ('49','07182',1,'Welzheim');
INSERT INTO `area_codes` VALUES ('49','07183',1,'Rudersberg Württ');
INSERT INTO `area_codes` VALUES ('49','07184',1,'Kaisersbach');
INSERT INTO `area_codes` VALUES ('49','07191',1,'Backnang');
INSERT INTO `area_codes` VALUES ('49','07192',1,'Murrhardt');
INSERT INTO `area_codes` VALUES ('49','07193',1,'Sulzbach an der Murr');
INSERT INTO `area_codes` VALUES ('49','07194',1,'Spiegelberg');
INSERT INTO `area_codes` VALUES ('49','07195',1,'Winnenden');
INSERT INTO `area_codes` VALUES ('49','07202',1,'Karlsbad');
INSERT INTO `area_codes` VALUES ('49','07203',1,'Walzbachtal');
INSERT INTO `area_codes` VALUES ('49','07204',1,'Malsch-Völkersbach');
INSERT INTO `area_codes` VALUES ('49','0721',1,'Karlsruhe');
INSERT INTO `area_codes` VALUES ('49','07220',1,'Forbach-Hundsbach');
INSERT INTO `area_codes` VALUES ('49','07221',1,'Baden-Baden');
INSERT INTO `area_codes` VALUES ('49','07222',1,'Rastatt');
INSERT INTO `area_codes` VALUES ('49','07223',1,'Bühl Baden');
INSERT INTO `area_codes` VALUES ('49','07224',1,'Gernsbach');
INSERT INTO `area_codes` VALUES ('49','07225',1,'Gaggenau');
INSERT INTO `area_codes` VALUES ('49','07226',1,'Bühl-Sand');
INSERT INTO `area_codes` VALUES ('49','07227',1,'Lichtenau Baden');
INSERT INTO `area_codes` VALUES ('49','07228',1,'Forbach');
INSERT INTO `area_codes` VALUES ('49','07229',1,'Iffezheim');
INSERT INTO `area_codes` VALUES ('49','07231',1,'Pforzheim');
INSERT INTO `area_codes` VALUES ('49','07232',1,'Königsbach-Stein');
INSERT INTO `area_codes` VALUES ('49','07233',1,'Niefern-Öschelbronn');
INSERT INTO `area_codes` VALUES ('49','07234',1,'Tiefenbronn');
INSERT INTO `area_codes` VALUES ('49','07235',1,'Unterreichenbach Kr Calw');
INSERT INTO `area_codes` VALUES ('49','07236',1,'Keltern');
INSERT INTO `area_codes` VALUES ('49','07237',1,'Neulingen Enzkreis');
INSERT INTO `area_codes` VALUES ('49','07240',1,'Pfinztal');
INSERT INTO `area_codes` VALUES ('49','07242',1,'Rheinstetten');
INSERT INTO `area_codes` VALUES ('49','07243',1,'Ettlingen');
INSERT INTO `area_codes` VALUES ('49','07244',1,'Weingarten Baden');
INSERT INTO `area_codes` VALUES ('49','07245',1,'Durmersheim');
INSERT INTO `area_codes` VALUES ('49','07246',1,'Malsch Kr Karlsruhe');
INSERT INTO `area_codes` VALUES ('49','07247',1,'Linkenheim-Hochstetten');
INSERT INTO `area_codes` VALUES ('49','07248',1,'Marxzell');
INSERT INTO `area_codes` VALUES ('49','07249',1,'Stutensee');
INSERT INTO `area_codes` VALUES ('49','07250',1,'Kraichtal');
INSERT INTO `area_codes` VALUES ('49','07251',1,'Bruchsal');
INSERT INTO `area_codes` VALUES ('49','07252',1,'Bretten');
INSERT INTO `area_codes` VALUES ('49','07253',1,'Bad Schönborn');
INSERT INTO `area_codes` VALUES ('49','07254',1,'Waghäusel');
INSERT INTO `area_codes` VALUES ('49','07255',1,'Graben-Neudorf');
INSERT INTO `area_codes` VALUES ('49','07256',1,'Philippsburg');
INSERT INTO `area_codes` VALUES ('49','07257',1,'Bruchsal-Untergrombach');
INSERT INTO `area_codes` VALUES ('49','07258',1,'Oberderdingen-Flehingen');
INSERT INTO `area_codes` VALUES ('49','07259',1,'Östringen-Odenheim');
INSERT INTO `area_codes` VALUES ('49','07260',1,'Sinsheim-Hilsbach');
INSERT INTO `area_codes` VALUES ('49','07261',1,'Sinsheim');
INSERT INTO `area_codes` VALUES ('49','07262',1,'Eppingen');
INSERT INTO `area_codes` VALUES ('49','07263',1,'Waibstadt');
INSERT INTO `area_codes` VALUES ('49','07264',1,'Bad Rappenau');
INSERT INTO `area_codes` VALUES ('49','07265',1,'Angelbachtal');
INSERT INTO `area_codes` VALUES ('49','07266',1,'Kirchardt');
INSERT INTO `area_codes` VALUES ('49','07267',1,'Gemmingen');
INSERT INTO `area_codes` VALUES ('49','07268',1,'Bad Rappenau-Obergimpern');
INSERT INTO `area_codes` VALUES ('49','07269',1,'Sulzfeld Baden');
INSERT INTO `area_codes` VALUES ('49','07271',1,'Wörth am Rhein');
INSERT INTO `area_codes` VALUES ('49','07272',1,'Rülzheim');
INSERT INTO `area_codes` VALUES ('49','07273',1,'Hagenbach Pfalz');
INSERT INTO `area_codes` VALUES ('49','07274',1,'Germersheim');
INSERT INTO `area_codes` VALUES ('49','07275',1,'Kandel');
INSERT INTO `area_codes` VALUES ('49','07276',1,'Herxheim  bei Landau Pfalz');
INSERT INTO `area_codes` VALUES ('49','07277',1,'Wörth-Büchelberg');
INSERT INTO `area_codes` VALUES ('49','07300',1,'Roggenburg');
INSERT INTO `area_codes` VALUES ('49','07302',1,'Pfaffenhofen a d Roth');
INSERT INTO `area_codes` VALUES ('49','07303',1,'Illertissen');
INSERT INTO `area_codes` VALUES ('49','07304',1,'Blaustein Württ');
INSERT INTO `area_codes` VALUES ('49','07305',1,'Erbach Donau');
INSERT INTO `area_codes` VALUES ('49','07306',1,'Vöhringen Iller');
INSERT INTO `area_codes` VALUES ('49','07307',1,'Senden Iller');
INSERT INTO `area_codes` VALUES ('49','07308',1,'Nersingen');
INSERT INTO `area_codes` VALUES ('49','07309',1,'Weissenhorn');
INSERT INTO `area_codes` VALUES ('49','0731',1,'Ulm Donau');
INSERT INTO `area_codes` VALUES ('49','07321',1,'Heidenheim a d Brenz');
INSERT INTO `area_codes` VALUES ('49','07322',1,'Giengen a d Brenz');
INSERT INTO `area_codes` VALUES ('49','07323',1,'Gerstetten');
INSERT INTO `area_codes` VALUES ('49','07324',1,'Herbrechtingen');
INSERT INTO `area_codes` VALUES ('49','07325',1,'Sontheim a d Brenz');
INSERT INTO `area_codes` VALUES ('49','07326',1,'Neresheim');
INSERT INTO `area_codes` VALUES ('49','07327',1,'Dischingen');
INSERT INTO `area_codes` VALUES ('49','07328',1,'Königsbronn');
INSERT INTO `area_codes` VALUES ('49','07329',1,'Steinheim am Albuch');
INSERT INTO `area_codes` VALUES ('49','07331',1,'Geislingen an der Steige');
INSERT INTO `area_codes` VALUES ('49','07332',1,'Lauterstein');
INSERT INTO `area_codes` VALUES ('49','07333',1,'Laichingen');
INSERT INTO `area_codes` VALUES ('49','07334',1,'Deggingen');
INSERT INTO `area_codes` VALUES ('49','07335',1,'Wiesensteig');
INSERT INTO `area_codes` VALUES ('49','07336',1,'Lonsee');
INSERT INTO `area_codes` VALUES ('49','07337',1,'Nellingen Alb');
INSERT INTO `area_codes` VALUES ('49','07340',1,'Neenstetten');
INSERT INTO `area_codes` VALUES ('49','07343',1,'Buch b Illertissen');
INSERT INTO `area_codes` VALUES ('49','07344',1,'Blaubeuren');
INSERT INTO `area_codes` VALUES ('49','07345',1,'Langenau Württ');
INSERT INTO `area_codes` VALUES ('49','07346',1,'Illerkirchberg');
INSERT INTO `area_codes` VALUES ('49','07347',1,'Dietenheim');
INSERT INTO `area_codes` VALUES ('49','07348',1,'Beimerstetten');
INSERT INTO `area_codes` VALUES ('49','07351',1,'Biberach an der Riß');
INSERT INTO `area_codes` VALUES ('49','07352',1,'Ochsenhausen');
INSERT INTO `area_codes` VALUES ('49','07353',1,'Schwendi');
INSERT INTO `area_codes` VALUES ('49','07354',1,'Erolzheim');
INSERT INTO `area_codes` VALUES ('49','07355',1,'Hochdorf Riß');
INSERT INTO `area_codes` VALUES ('49','07356',1,'Schemmerhofen');
INSERT INTO `area_codes` VALUES ('49','07357',1,'Attenweiler');
INSERT INTO `area_codes` VALUES ('49','07358',1,'Eberhardzell-Füramoos');
INSERT INTO `area_codes` VALUES ('49','07361',1,'Aalen');
INSERT INTO `area_codes` VALUES ('49','07362',1,'Bopfingen');
INSERT INTO `area_codes` VALUES ('49','07363',1,'Lauchheim');
INSERT INTO `area_codes` VALUES ('49','07364',1,'Oberkochen');
INSERT INTO `area_codes` VALUES ('49','07365',1,'Essingen Württ');
INSERT INTO `area_codes` VALUES ('49','07366',1,'Abtsgmünd');
INSERT INTO `area_codes` VALUES ('49','07367',1,'Aalen-Ebnat');
INSERT INTO `area_codes` VALUES ('49','07371',1,'Riedlingen Württ');
INSERT INTO `area_codes` VALUES ('49','07373',1,'Zwiefalten');
INSERT INTO `area_codes` VALUES ('49','07374',1,'Uttenweiler');
INSERT INTO `area_codes` VALUES ('49','07375',1,'Obermarchtal');
INSERT INTO `area_codes` VALUES ('49','07376',1,'Langenenslingen');
INSERT INTO `area_codes` VALUES ('49','07381',1,'Münsingen');
INSERT INTO `area_codes` VALUES ('49','07382',1,'Römerstein');
INSERT INTO `area_codes` VALUES ('49','07383',1,'Münsingen-Buttenhausen');
INSERT INTO `area_codes` VALUES ('49','07384',1,'Schelklingen-Hütten');
INSERT INTO `area_codes` VALUES ('49','07385',1,'Gomadingen');
INSERT INTO `area_codes` VALUES ('49','07386',1,'Hayingen');
INSERT INTO `area_codes` VALUES ('49','07387',1,'Hohenstein Württ');
INSERT INTO `area_codes` VALUES ('49','07388',1,'Pfronstetten');
INSERT INTO `area_codes` VALUES ('49','07389',1,'Heroldstatt');
INSERT INTO `area_codes` VALUES ('49','07391',1,'Ehingen Donau');
INSERT INTO `area_codes` VALUES ('49','07392',1,'Laupheim');
INSERT INTO `area_codes` VALUES ('49','07393',1,'Munderkingen');
INSERT INTO `area_codes` VALUES ('49','07394',1,'Schelklingen');
INSERT INTO `area_codes` VALUES ('49','07395',1,'Ehingen-Dächingen');
INSERT INTO `area_codes` VALUES ('49','07402',1,'Fluorn-Winzeln');
INSERT INTO `area_codes` VALUES ('49','07403',1,'Dunningen');
INSERT INTO `area_codes` VALUES ('49','07404',1,'Epfendorf');
INSERT INTO `area_codes` VALUES ('49','0741',1,'Rottweil');
INSERT INTO `area_codes` VALUES ('49','07420',1,'Deisslingen');
INSERT INTO `area_codes` VALUES ('49','07422',1,'Schramberg');
INSERT INTO `area_codes` VALUES ('49','07423',1,'Oberndorf am Neckar');
INSERT INTO `area_codes` VALUES ('49','07424',1,'Spaichingen');
INSERT INTO `area_codes` VALUES ('49','07425',1,'Trossingen');
INSERT INTO `area_codes` VALUES ('49','07426',1,'Gosheim');
INSERT INTO `area_codes` VALUES ('49','07427',1,'Schömberg b Balingen');
INSERT INTO `area_codes` VALUES ('49','07428',1,'Rosenfeld');
INSERT INTO `area_codes` VALUES ('49','07429',1,'Egesheim');
INSERT INTO `area_codes` VALUES ('49','07431',1,'Albstadt-Ebingen');
INSERT INTO `area_codes` VALUES ('49','07432',1,'Albstadt-Tailfingen');
INSERT INTO `area_codes` VALUES ('49','07433',1,'Balingen');
INSERT INTO `area_codes` VALUES ('49','07434',1,'Winterlingen');
INSERT INTO `area_codes` VALUES ('49','07435',1,'Albstadt-Laufen');
INSERT INTO `area_codes` VALUES ('49','07436',1,'Messstetten-Oberdigisheim');
INSERT INTO `area_codes` VALUES ('49','07440',1,'Bad Rippoldsau');
INSERT INTO `area_codes` VALUES ('49','07441',1,'Freudenstadt');
INSERT INTO `area_codes` VALUES ('49','07442',1,'Baiersbronn');
INSERT INTO `area_codes` VALUES ('49','07443',1,'Dornstetten');
INSERT INTO `area_codes` VALUES ('49','07444',1,'Alpirsbach');
INSERT INTO `area_codes` VALUES ('49','07445',1,'Pfalzgrafenweiler');
INSERT INTO `area_codes` VALUES ('49','07446',1,'Lossburg');
INSERT INTO `area_codes` VALUES ('49','07447',1,'Baiersbronn-Schwarzenberg');
INSERT INTO `area_codes` VALUES ('49','07448',1,'Seewald');
INSERT INTO `area_codes` VALUES ('49','07449',1,'Baiersbronn-Obertal');
INSERT INTO `area_codes` VALUES ('49','07451',1,'Horb am Neckar');
INSERT INTO `area_codes` VALUES ('49','07452',1,'Nagold');
INSERT INTO `area_codes` VALUES ('49','07453',1,'Altensteig Württ');
INSERT INTO `area_codes` VALUES ('49','07454',1,'Sulz am Neckar');
INSERT INTO `area_codes` VALUES ('49','07455',1,'Dornhan');
INSERT INTO `area_codes` VALUES ('49','07456',1,'Haiterbach');
INSERT INTO `area_codes` VALUES ('49','07457',1,'Rottenburg-Ergenzingen');
INSERT INTO `area_codes` VALUES ('49','07458',1,'Ebhausen');
INSERT INTO `area_codes` VALUES ('49','07459',1,'Nagold-Hochdorf');
INSERT INTO `area_codes` VALUES ('49','07461',1,'Tuttlingen');
INSERT INTO `area_codes` VALUES ('49','07462',1,'Immendingen');
INSERT INTO `area_codes` VALUES ('49','07463',1,'Mühlheim an der Donau');
INSERT INTO `area_codes` VALUES ('49','07464',1,'Talheim Kr Tuttlingen');
INSERT INTO `area_codes` VALUES ('49','07465',1,'Emmingen-Liptingen');
INSERT INTO `area_codes` VALUES ('49','07466',1,'Beuron');
INSERT INTO `area_codes` VALUES ('49','07467',1,'Neuhausen ob Eck');
INSERT INTO `area_codes` VALUES ('49','07471',1,'Hechingen');
INSERT INTO `area_codes` VALUES ('49','07472',1,'Rottenburg am Neckar');
INSERT INTO `area_codes` VALUES ('49','07473',1,'Mössingen');
INSERT INTO `area_codes` VALUES ('49','07474',1,'Haigerloch');
INSERT INTO `area_codes` VALUES ('49','07475',1,'Burladingen');
INSERT INTO `area_codes` VALUES ('49','07476',1,'Bisingen');
INSERT INTO `area_codes` VALUES ('49','07477',1,'Jungingen b Hechingen');
INSERT INTO `area_codes` VALUES ('49','07478',1,'Hirrlingen');
INSERT INTO `area_codes` VALUES ('49','07482',1,'Horb-Dettingen');
INSERT INTO `area_codes` VALUES ('49','07483',1,'Horb-Mühringen');
INSERT INTO `area_codes` VALUES ('49','07484',1,'Simmersfeld');
INSERT INTO `area_codes` VALUES ('49','07485',1,'Empfingen');
INSERT INTO `area_codes` VALUES ('49','07486',1,'Horb-Altheim');
INSERT INTO `area_codes` VALUES ('49','07502',1,'Wolpertswende');
INSERT INTO `area_codes` VALUES ('49','07503',1,'Wilhelmsdorf Württ');
INSERT INTO `area_codes` VALUES ('49','07504',1,'Horgenzell');
INSERT INTO `area_codes` VALUES ('49','07505',1,'Fronreute');
INSERT INTO `area_codes` VALUES ('49','07506',1,'Wangen-Leupolz');
INSERT INTO `area_codes` VALUES ('49','0751',1,'Ravensburg');
INSERT INTO `area_codes` VALUES ('49','07520',1,'Bodnegg');
INSERT INTO `area_codes` VALUES ('49','07522',1,'Wangen im Allgäu');
INSERT INTO `area_codes` VALUES ('49','07524',1,'Bad Waldsee');
INSERT INTO `area_codes` VALUES ('49','07525',1,'Aulendorf');
INSERT INTO `area_codes` VALUES ('49','07527',1,'Wolfegg');
INSERT INTO `area_codes` VALUES ('49','07528',1,'Neukirch b Tettnang');
INSERT INTO `area_codes` VALUES ('49','07529',1,'Waldburg Württ');
INSERT INTO `area_codes` VALUES ('49','07531',1,'Konstanz');
INSERT INTO `area_codes` VALUES ('49','07532',1,'Meersburg');
INSERT INTO `area_codes` VALUES ('49','07533',1,'Allensbach');
INSERT INTO `area_codes` VALUES ('49','07534',1,'Reichenau Baden');
INSERT INTO `area_codes` VALUES ('49','07541',1,'Friedrichshafen');
INSERT INTO `area_codes` VALUES ('49','07542',1,'Tettnang');
INSERT INTO `area_codes` VALUES ('49','07543',1,'Kressbronn am Bodensee');
INSERT INTO `area_codes` VALUES ('49','07544',1,'Markdorf');
INSERT INTO `area_codes` VALUES ('49','07545',1,'Immenstaad am Bodensee');
INSERT INTO `area_codes` VALUES ('49','07546',1,'Oberteuringen');
INSERT INTO `area_codes` VALUES ('49','07551',1,'Überlingen Bodensee');
INSERT INTO `area_codes` VALUES ('49','07552',1,'Pfullendorf');
INSERT INTO `area_codes` VALUES ('49','07553',1,'Salem Baden');
INSERT INTO `area_codes` VALUES ('49','07554',1,'Heiligenberg Baden');
INSERT INTO `area_codes` VALUES ('49','07555',1,'Deggenhausertal');
INSERT INTO `area_codes` VALUES ('49','07556',1,'Uhldingen-Mühlhofen');
INSERT INTO `area_codes` VALUES ('49','07557',1,'Herdwangen-Schönach');
INSERT INTO `area_codes` VALUES ('49','07558',1,'Illmensee');
INSERT INTO `area_codes` VALUES ('49','07561',1,'Leutkirch im Allgäu');
INSERT INTO `area_codes` VALUES ('49','07562',1,'Isny im Allgäu');
INSERT INTO `area_codes` VALUES ('49','07563',1,'Kisslegg');
INSERT INTO `area_codes` VALUES ('49','07564',1,'Bad Wurzach');
INSERT INTO `area_codes` VALUES ('49','07565',1,'Aichstetten Kr Ravensburg');
INSERT INTO `area_codes` VALUES ('49','07566',1,'Argenbühl');
INSERT INTO `area_codes` VALUES ('49','07567',1,'Leutkirch-Friesenhofen');
INSERT INTO `area_codes` VALUES ('49','07568',1,'Bad Wurzach-Hauerz');
INSERT INTO `area_codes` VALUES ('49','07569',1,'Isny-Eisenbach');
INSERT INTO `area_codes` VALUES ('49','07570',1,'Sigmaringen-Gutenstein');
INSERT INTO `area_codes` VALUES ('49','07571',1,'Sigmaringen');
INSERT INTO `area_codes` VALUES ('49','07572',1,'Mengen Württ');
INSERT INTO `area_codes` VALUES ('49','07573',1,'Stetten am kalten Markt');
INSERT INTO `area_codes` VALUES ('49','07574',1,'Gammertingen');
INSERT INTO `area_codes` VALUES ('49','07575',1,'Messkirch');
INSERT INTO `area_codes` VALUES ('49','07576',1,'Krauchenwies');
INSERT INTO `area_codes` VALUES ('49','07577',1,'Veringenstadt');
INSERT INTO `area_codes` VALUES ('49','07578',1,'Wald Hohenz');
INSERT INTO `area_codes` VALUES ('49','07579',1,'Schwenningen Baden');
INSERT INTO `area_codes` VALUES ('49','07581',1,'Saulgau');
INSERT INTO `area_codes` VALUES ('49','07582',1,'Bad Buchau');
INSERT INTO `area_codes` VALUES ('49','07583',1,'Bad Schussenried');
INSERT INTO `area_codes` VALUES ('49','07584',1,'Altshausen');
INSERT INTO `area_codes` VALUES ('49','07585',1,'Ostrach');
INSERT INTO `area_codes` VALUES ('49','07586',1,'Herbertingen');
INSERT INTO `area_codes` VALUES ('49','07587',1,'Hosskirch');
INSERT INTO `area_codes` VALUES ('49','07602',1,'Oberried Breisgau');
INSERT INTO `area_codes` VALUES ('49','0761',1,'Freiburg im Breisgau');
INSERT INTO `area_codes` VALUES ('49','07620',1,'Schopfheim-Gersbach');
INSERT INTO `area_codes` VALUES ('49','07621',1,'Lörrach');
INSERT INTO `area_codes` VALUES ('49','07622',1,'Schopfheim');
INSERT INTO `area_codes` VALUES ('49','07623',1,'Rheinfelden Baden');
INSERT INTO `area_codes` VALUES ('49','07624',1,'Grenzach-Wyhlen');
INSERT INTO `area_codes` VALUES ('49','07625',1,'Zell im Wiesental');
INSERT INTO `area_codes` VALUES ('49','07626',1,'Kandern');
INSERT INTO `area_codes` VALUES ('49','07627',1,'Steinen Kr Lörrach');
INSERT INTO `area_codes` VALUES ('49','07628',1,'Efringen-Kirchen');
INSERT INTO `area_codes` VALUES ('49','07629',1,'Tegernau Baden');
INSERT INTO `area_codes` VALUES ('49','07631',1,'Müllheim Baden');
INSERT INTO `area_codes` VALUES ('49','07632',1,'Badenweiler');
INSERT INTO `area_codes` VALUES ('49','07633',1,'Staufen im Breisgau');
INSERT INTO `area_codes` VALUES ('49','07634',1,'Sulzburg');
INSERT INTO `area_codes` VALUES ('49','07635',1,'Schliengen');
INSERT INTO `area_codes` VALUES ('49','07636',1,'Münstertal Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07641',1,'Emmendingen');
INSERT INTO `area_codes` VALUES ('49','07642',1,'Endingen Kaiserstuh');
INSERT INTO `area_codes` VALUES ('49','07643',1,'Herbolzheim Breisgau');
INSERT INTO `area_codes` VALUES ('49','07644',1,'Kenzingen');
INSERT INTO `area_codes` VALUES ('49','07645',1,'Freiamt');
INSERT INTO `area_codes` VALUES ('49','07646',1,'Weisweil Breisgau');
INSERT INTO `area_codes` VALUES ('49','07651',1,'Titisee-Neustadt');
INSERT INTO `area_codes` VALUES ('49','07652',1,'Hinterzarten');
INSERT INTO `area_codes` VALUES ('49','07653',1,'Lenzkirch');
INSERT INTO `area_codes` VALUES ('49','07654',1,'Löffingen');
INSERT INTO `area_codes` VALUES ('49','07655',1,'Feldberg-Altglashütten');
INSERT INTO `area_codes` VALUES ('49','07656',1,'Schluchsee');
INSERT INTO `area_codes` VALUES ('49','07657',1,'Eisenbach Hochschwarzwald');
INSERT INTO `area_codes` VALUES ('49','07660',1,'St Peter Schwarzw');
INSERT INTO `area_codes` VALUES ('49','07661',1,'Kirchzarten');
INSERT INTO `area_codes` VALUES ('49','07662',1,'Vogtsburg im Kaiserstuh');
INSERT INTO `area_codes` VALUES ('49','07663',1,'Eichstetten');
INSERT INTO `area_codes` VALUES ('49','07664',1,'Freiburg-Tiengen');
INSERT INTO `area_codes` VALUES ('49','07665',1,'March Breisgau');
INSERT INTO `area_codes` VALUES ('49','07666',1,'Denzlingen');
INSERT INTO `area_codes` VALUES ('49','07667',1,'Breisach am Rhein');
INSERT INTO `area_codes` VALUES ('49','07668',1,'Ihringen');
INSERT INTO `area_codes` VALUES ('49','07669',1,'St Märgen');
INSERT INTO `area_codes` VALUES ('49','07671',1,'Todtnau');
INSERT INTO `area_codes` VALUES ('49','07672',1,'St Blasien');
INSERT INTO `area_codes` VALUES ('49','07673',1,'Schönau im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07674',1,'Todtmoos');
INSERT INTO `area_codes` VALUES ('49','07675',1,'Bernau Baden');
INSERT INTO `area_codes` VALUES ('49','07676',1,'Feldberg Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07681',1,'Waldkirch Breisgau');
INSERT INTO `area_codes` VALUES ('49','07682',1,'Elzach');
INSERT INTO `area_codes` VALUES ('49','07683',1,'Simonswald');
INSERT INTO `area_codes` VALUES ('49','07684',1,'Glottertal');
INSERT INTO `area_codes` VALUES ('49','07685',1,'Gutach-Bleibach');
INSERT INTO `area_codes` VALUES ('49','07702',1,'Blumberg Baden');
INSERT INTO `area_codes` VALUES ('49','07703',1,'Bonndorf im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07704',1,'Geisingen Baden');
INSERT INTO `area_codes` VALUES ('49','07705',1,'Wolterdingen Schwarzw');
INSERT INTO `area_codes` VALUES ('49','07706',1,'Oberbaldingen');
INSERT INTO `area_codes` VALUES ('49','07707',1,'Bräunlingen');
INSERT INTO `area_codes` VALUES ('49','07708',1,'Geisingen-Leipferdingen');
INSERT INTO `area_codes` VALUES ('49','07709',1,'Wutach');
INSERT INTO `area_codes` VALUES ('49','0771',1,'Donaueschingen');
INSERT INTO `area_codes` VALUES ('49','07720',1,'Schwenningen a Neckar');
INSERT INTO `area_codes` VALUES ('49','07721',1,'Villingen i Schwarzw');
INSERT INTO `area_codes` VALUES ('49','07722',1,'Triberg im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07723',1,'Furtwangen im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07724',1,'St Georgen im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07725',1,'Königsfeld im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07726',1,'Bad Dürrheim');
INSERT INTO `area_codes` VALUES ('49','07727',1,'Vöhrenbach');
INSERT INTO `area_codes` VALUES ('49','07728',1,'Niedereschach');
INSERT INTO `area_codes` VALUES ('49','07729',1,'Tennenbronn');
INSERT INTO `area_codes` VALUES ('49','07731',1,'Singen Hohentwiel');
INSERT INTO `area_codes` VALUES ('49','07732',1,'Radolfzell am Bodensee');
INSERT INTO `area_codes` VALUES ('49','07733',1,'Engen Hegau');
INSERT INTO `area_codes` VALUES ('49','07734',1,'Gailingen');
INSERT INTO `area_codes` VALUES ('49','07735',1,'Öhningen');
INSERT INTO `area_codes` VALUES ('49','07736',1,'Tengen');
INSERT INTO `area_codes` VALUES ('49','07738',1,'Steisslingen');
INSERT INTO `area_codes` VALUES ('49','07739',1,'Hilzingen');
INSERT INTO `area_codes` VALUES ('49','07741',1,'Tiengen Hochrhein');
INSERT INTO `area_codes` VALUES ('49','07742',1,'Klettgau');
INSERT INTO `area_codes` VALUES ('49','07743',1,'Ühlingen-Birkendorf');
INSERT INTO `area_codes` VALUES ('49','07744',1,'Stühlingen');
INSERT INTO `area_codes` VALUES ('49','07745',1,'Jestetten');
INSERT INTO `area_codes` VALUES ('49','07746',1,'Wutöschingen');
INSERT INTO `area_codes` VALUES ('49','07747',1,'Berau');
INSERT INTO `area_codes` VALUES ('49','07748',1,'Grafenhausen Hochschwarzw');
INSERT INTO `area_codes` VALUES ('49','07751',1,'Waldshut');
INSERT INTO `area_codes` VALUES ('49','07753',1,'Albbruck');
INSERT INTO `area_codes` VALUES ('49','07754',1,'Görwihl');
INSERT INTO `area_codes` VALUES ('49','07755',1,'Weilheim Kr Waldshut');
INSERT INTO `area_codes` VALUES ('49','07761',1,'Bad Säckingen');
INSERT INTO `area_codes` VALUES ('49','07762',1,'Wehr Baden');
INSERT INTO `area_codes` VALUES ('49','07763',1,'Murg');
INSERT INTO `area_codes` VALUES ('49','07764',1,'Herrischried');
INSERT INTO `area_codes` VALUES ('49','07765',1,'Rickenbach Hotzenw');
INSERT INTO `area_codes` VALUES ('49','07771',1,'Stockach');
INSERT INTO `area_codes` VALUES ('49','07773',1,'Bodman-Ludwigshafen');
INSERT INTO `area_codes` VALUES ('49','07774',1,'Eigeltingen');
INSERT INTO `area_codes` VALUES ('49','07775',1,'Mühlingen');
INSERT INTO `area_codes` VALUES ('49','07777',1,'Sauldorf');
INSERT INTO `area_codes` VALUES ('49','07802',1,'Oberkirch Baden');
INSERT INTO `area_codes` VALUES ('49','07803',1,'Gengenbach');
INSERT INTO `area_codes` VALUES ('49','07804',1,'Oppenau');
INSERT INTO `area_codes` VALUES ('49','07805',1,'Appenweier');
INSERT INTO `area_codes` VALUES ('49','07806',1,'Bad Peterstal-Griesbach');
INSERT INTO `area_codes` VALUES ('49','07807',1,'Neuried Ortenaukreis');
INSERT INTO `area_codes` VALUES ('49','07808',1,'Hohberg b Offenburg');
INSERT INTO `area_codes` VALUES ('49','0781',1,'Offenburg');
INSERT INTO `area_codes` VALUES ('49','07821',1,'Lahr Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07822',1,'Ettenheim');
INSERT INTO `area_codes` VALUES ('49','07823',1,'Seelbach Schutter');
INSERT INTO `area_codes` VALUES ('49','07824',1,'Schwanau');
INSERT INTO `area_codes` VALUES ('49','07825',1,'Kippenheim');
INSERT INTO `area_codes` VALUES ('49','07826',1,'Schuttertal');
INSERT INTO `area_codes` VALUES ('49','07831',1,'Hausach');
INSERT INTO `area_codes` VALUES ('49','07832',1,'Haslach  im Kinzigtal');
INSERT INTO `area_codes` VALUES ('49','07833',1,'Hornberg Schwarzwaldbahn');
INSERT INTO `area_codes` VALUES ('49','07834',1,'Wolfach');
INSERT INTO `area_codes` VALUES ('49','07835',1,'Zell am Harmersbach');
INSERT INTO `area_codes` VALUES ('49','07836',1,'Schiltach');
INSERT INTO `area_codes` VALUES ('49','07837',1,'Oberharmersbach');
INSERT INTO `area_codes` VALUES ('49','07838',1,'Nordrach');
INSERT INTO `area_codes` VALUES ('49','07839',1,'Schapbach');
INSERT INTO `area_codes` VALUES ('49','07841',1,'Achern');
INSERT INTO `area_codes` VALUES ('49','07842',1,'Kappelrodeck');
INSERT INTO `area_codes` VALUES ('49','07843',1,'Renchen');
INSERT INTO `area_codes` VALUES ('49','07844',1,'Rheinau');
INSERT INTO `area_codes` VALUES ('49','07851',1,'Kehl');
INSERT INTO `area_codes` VALUES ('49','07852',1,'Willstätt');
INSERT INTO `area_codes` VALUES ('49','07853',1,'Kehl-Bodersweier');
INSERT INTO `area_codes` VALUES ('49','07854',1,'Kehl-Goldscheuer');
INSERT INTO `area_codes` VALUES ('49','07903',1,'Mainhardt');
INSERT INTO `area_codes` VALUES ('49','07904',1,'Ilshofen');
INSERT INTO `area_codes` VALUES ('49','07905',1,'Langenburg');
INSERT INTO `area_codes` VALUES ('49','07906',1,'Braunsbach');
INSERT INTO `area_codes` VALUES ('49','07907',1,'Schwäbisch Hall-Sulzdorf');
INSERT INTO `area_codes` VALUES ('49','0791',1,'Schwäbisch Hall');
INSERT INTO `area_codes` VALUES ('49','07930',1,'Boxberg Baden');
INSERT INTO `area_codes` VALUES ('49','07931',1,'Bad Mergentheim');
INSERT INTO `area_codes` VALUES ('49','07932',1,'Niederstetten Württ');
INSERT INTO `area_codes` VALUES ('49','07933',1,'Creglingen');
INSERT INTO `area_codes` VALUES ('49','07934',1,'Weikersheim');
INSERT INTO `area_codes` VALUES ('49','07935',1,'Schrozberg');
INSERT INTO `area_codes` VALUES ('49','07936',1,'Schrozberg-Bartenstein');
INSERT INTO `area_codes` VALUES ('49','07937',1,'Dörzbach');
INSERT INTO `area_codes` VALUES ('49','07938',1,'Mulfingen Jagst');
INSERT INTO `area_codes` VALUES ('49','07939',1,'Schrozberg-Spielbach');
INSERT INTO `area_codes` VALUES ('49','07940',1,'Künzelsau');
INSERT INTO `area_codes` VALUES ('49','07941',1,'Öhringen');
INSERT INTO `area_codes` VALUES ('49','07942',1,'Neuenstein Württ');
INSERT INTO `area_codes` VALUES ('49','07943',1,'Schöntal Jagst');
INSERT INTO `area_codes` VALUES ('49','07944',1,'Kupferzell');
INSERT INTO `area_codes` VALUES ('49','07945',1,'Wüstenrot');
INSERT INTO `area_codes` VALUES ('49','07946',1,'Bretzfeld');
INSERT INTO `area_codes` VALUES ('49','07947',1,'Forchtenberg');
INSERT INTO `area_codes` VALUES ('49','07948',1,'Öhringen-Ohrnberg');
INSERT INTO `area_codes` VALUES ('49','07949',1,'Pfedelbach-Untersteinbach');
INSERT INTO `area_codes` VALUES ('49','07950',1,'Schnelldorf');
INSERT INTO `area_codes` VALUES ('49','07951',1,'Crailsheim');
INSERT INTO `area_codes` VALUES ('49','07952',1,'Gerabronn');
INSERT INTO `area_codes` VALUES ('49','07953',1,'Blaufelden');
INSERT INTO `area_codes` VALUES ('49','07954',1,'Kirchberg an der Jagst');
INSERT INTO `area_codes` VALUES ('49','07955',1,'Wallhausen  Württ');
INSERT INTO `area_codes` VALUES ('49','07957',1,'Kressberg');
INSERT INTO `area_codes` VALUES ('49','07958',1,'Rot Am See-Brettheim');
INSERT INTO `area_codes` VALUES ('49','07959',1,'Frankenhardt');
INSERT INTO `area_codes` VALUES ('49','07961',1,'Ellwangen Jagst');
INSERT INTO `area_codes` VALUES ('49','07962',1,'Fichtenau');
INSERT INTO `area_codes` VALUES ('49','07963',1,'Adelmannsfelden');
INSERT INTO `area_codes` VALUES ('49','07964',1,'Stödtlen');
INSERT INTO `area_codes` VALUES ('49','07965',1,'Ellwangen-Röhlingen');
INSERT INTO `area_codes` VALUES ('49','07966',1,'Unterschneidheim');
INSERT INTO `area_codes` VALUES ('49','07967',1,'Jagstzell');
INSERT INTO `area_codes` VALUES ('49','07971',1,'Gaildorf');
INSERT INTO `area_codes` VALUES ('49','07972',1,'Gschwend b Gaildorf');
INSERT INTO `area_codes` VALUES ('49','07973',1,'Obersontheim');
INSERT INTO `area_codes` VALUES ('49','07974',1,'Bühlerzell');
INSERT INTO `area_codes` VALUES ('49','07975',1,'Untergröningen');
INSERT INTO `area_codes` VALUES ('49','07976',1,'Sulzbach-Laufen');
INSERT INTO `area_codes` VALUES ('49','07977',1,'Oberrot b Gaildorf');
INSERT INTO `area_codes` VALUES ('49','0800',0,'Entgeltfreie Telefondienste');
INSERT INTO `area_codes` VALUES ('49','0801',0,'(Reserve für Entgeltfreie Telefondienste)');
INSERT INTO `area_codes` VALUES ('49','08020',1,'Weyarn');
INSERT INTO `area_codes` VALUES ('49','08021',1,'Waakirchen');
INSERT INTO `area_codes` VALUES ('49','08022',1,'Tegernsee');
INSERT INTO `area_codes` VALUES ('49','08023',1,'Bayrischzell');
INSERT INTO `area_codes` VALUES ('49','08024',1,'Holzkirchen');
INSERT INTO `area_codes` VALUES ('49','08025',1,'Miesbach');
INSERT INTO `area_codes` VALUES ('49','08026',1,'Hausham');
INSERT INTO `area_codes` VALUES ('49','08027',1,'Dietramszell');
INSERT INTO `area_codes` VALUES ('49','08028',1,'Fischbachau');
INSERT INTO `area_codes` VALUES ('49','08029',1,'Kreuth  b Tegernsee');
INSERT INTO `area_codes` VALUES ('49','08031',1,'Rosenheim Oberbay');
INSERT INTO `area_codes` VALUES ('49','08032',1,'Rohrdorf Kr Rosenheim');
INSERT INTO `area_codes` VALUES ('49','08033',1,'Oberaudorf');
INSERT INTO `area_codes` VALUES ('49','08034',1,'Brannenburg');
INSERT INTO `area_codes` VALUES ('49','08035',1,'Raubling');
INSERT INTO `area_codes` VALUES ('49','08036',1,'Stephanskirchen Simssee');
INSERT INTO `area_codes` VALUES ('49','08038',1,'Vogtareuth');
INSERT INTO `area_codes` VALUES ('49','08039',1,'Rott a Inn');
INSERT INTO `area_codes` VALUES ('49','08041',1,'Bad Tölz');
INSERT INTO `area_codes` VALUES ('49','08042',1,'Lenggries');
INSERT INTO `area_codes` VALUES ('49','08043',1,'Jachenau');
INSERT INTO `area_codes` VALUES ('49','08045',1,'Lenggries-Fall');
INSERT INTO `area_codes` VALUES ('49','08046',1,'Bad Heilbrunn');
INSERT INTO `area_codes` VALUES ('49','08051',1,'Prien a Chiemsee');
INSERT INTO `area_codes` VALUES ('49','08052',1,'Aschau i Chiemgau');
INSERT INTO `area_codes` VALUES ('49','08053',1,'Bad Endorf');
INSERT INTO `area_codes` VALUES ('49','08054',1,'Breitbrunn a Chiemsee');
INSERT INTO `area_codes` VALUES ('49','08055',1,'Halfing');
INSERT INTO `area_codes` VALUES ('49','08056',1,'Eggstätt');
INSERT INTO `area_codes` VALUES ('49','08057',1,'Aschau-Sachrang');
INSERT INTO `area_codes` VALUES ('49','08061',1,'Bad Aibling');
INSERT INTO `area_codes` VALUES ('49','08062',1,'Bruckmühl Mangfall');
INSERT INTO `area_codes` VALUES ('49','08063',1,'Feldkirchen-Westerham');
INSERT INTO `area_codes` VALUES ('49','08064',1,'Au b Bad Aibling');
INSERT INTO `area_codes` VALUES ('49','08065',1,'Tuntenhausen-Schönau');
INSERT INTO `area_codes` VALUES ('49','08066',1,'Bad Feilnbach');
INSERT INTO `area_codes` VALUES ('49','08067',1,'Tuntenhausen');
INSERT INTO `area_codes` VALUES ('49','08071',1,'Wasserburg a Inn');
INSERT INTO `area_codes` VALUES ('49','08072',1,'Haag i OB');
INSERT INTO `area_codes` VALUES ('49','08073',1,'Gars a Inn');
INSERT INTO `area_codes` VALUES ('49','08074',1,'Schnaitsee');
INSERT INTO `area_codes` VALUES ('49','08075',1,'Amerang');
INSERT INTO `area_codes` VALUES ('49','08076',1,'Pfaffing');
INSERT INTO `area_codes` VALUES ('49','08081',1,'Dorfen Stadt');
INSERT INTO `area_codes` VALUES ('49','08082',1,'Schwindegg');
INSERT INTO `area_codes` VALUES ('49','08083',1,'Isen');
INSERT INTO `area_codes` VALUES ('49','08084',1,'Taufkirchen Vils');
INSERT INTO `area_codes` VALUES ('49','08085',1,'Sankt Wolfgang');
INSERT INTO `area_codes` VALUES ('49','08086',1,'Buchbach Oberbay');
INSERT INTO `area_codes` VALUES ('49','08091',1,'Kirchseeon');
INSERT INTO `area_codes` VALUES ('49','08092',1,'Grafing b München');
INSERT INTO `area_codes` VALUES ('49','08093',1,'Glonn  Kr Ebersberg');
INSERT INTO `area_codes` VALUES ('49','08094',1,'Steinhöring');
INSERT INTO `area_codes` VALUES ('49','08095',1,'Aying');
INSERT INTO `area_codes` VALUES ('49','08102',1,'Höhenkirchen-Siegertsbrunn');
INSERT INTO `area_codes` VALUES ('49','08104',1,'Sauerlach');
INSERT INTO `area_codes` VALUES ('49','08105',1,'Gilching');
INSERT INTO `area_codes` VALUES ('49','08106',1,'Vaterstetten');
INSERT INTO `area_codes` VALUES ('49','0811',1,'Hallbergmoos');
INSERT INTO `area_codes` VALUES ('49','08121',1,'Markt Schwaben');
INSERT INTO `area_codes` VALUES ('49','08122',1,'Erding');
INSERT INTO `area_codes` VALUES ('49','08123',1,'Moosinning');
INSERT INTO `area_codes` VALUES ('49','08124',1,'Forstern Oberbay');
INSERT INTO `area_codes` VALUES ('49','08131',1,'Dachau');
INSERT INTO `area_codes` VALUES ('49','08133',1,'Haimhausen Oberbay');
INSERT INTO `area_codes` VALUES ('49','08134',1,'Odelzhausen');
INSERT INTO `area_codes` VALUES ('49','08135',1,'Sulzemoos');
INSERT INTO `area_codes` VALUES ('49','08136',1,'Markt Indersdorf');
INSERT INTO `area_codes` VALUES ('49','08137',1,'Petershausen');
INSERT INTO `area_codes` VALUES ('49','08138',1,'Schwabhausen b Dachau');
INSERT INTO `area_codes` VALUES ('49','08139',1,'Röhrmoos');
INSERT INTO `area_codes` VALUES ('49','08141',1,'Fürstenfeldbruck');
INSERT INTO `area_codes` VALUES ('49','08142',1,'Olching');
INSERT INTO `area_codes` VALUES ('49','08143',1,'Inning a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08144',1,'Grafrath');
INSERT INTO `area_codes` VALUES ('49','08145',1,'Mammendorf');
INSERT INTO `area_codes` VALUES ('49','08146',1,'Moorenweis');
INSERT INTO `area_codes` VALUES ('49','08151',1,'Starnberg');
INSERT INTO `area_codes` VALUES ('49','08152',1,'Herrsching a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08153',1,'Wessling');
INSERT INTO `area_codes` VALUES ('49','08157',1,'Feldafing');
INSERT INTO `area_codes` VALUES ('49','08158',1,'Tutzing');
INSERT INTO `area_codes` VALUES ('49','08161',1,'Freising');
INSERT INTO `area_codes` VALUES ('49','08165',1,'Neufahrn b Freising');
INSERT INTO `area_codes` VALUES ('49','08166',1,'Allershausen Oberbay');
INSERT INTO `area_codes` VALUES ('49','08167',1,'Zolling');
INSERT INTO `area_codes` VALUES ('49','08168',1,'Attenkirchen');
INSERT INTO `area_codes` VALUES ('49','08170',1,'Straßlach-Dingharting');
INSERT INTO `area_codes` VALUES ('49','08171',1,'Wolfratshausen');
INSERT INTO `area_codes` VALUES ('49','08176',1,'Egling b Wolfratshausen');
INSERT INTO `area_codes` VALUES ('49','08177',1,'Münsing Starnberger See');
INSERT INTO `area_codes` VALUES ('49','08178',1,'Icking');
INSERT INTO `area_codes` VALUES ('49','08179',1,'Eurasburg a d Loisach');
INSERT INTO `area_codes` VALUES ('49','08191',1,'Landsberg a Lech');
INSERT INTO `area_codes` VALUES ('49','08192',1,'Schondorf a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08193',1,'Geltendorf');
INSERT INTO `area_codes` VALUES ('49','08194',1,'Vilgertshofen');
INSERT INTO `area_codes` VALUES ('49','08195',1,'Weil Kr Landsberg a Lech');
INSERT INTO `area_codes` VALUES ('49','08196',1,'Pürgen');
INSERT INTO `area_codes` VALUES ('49','08202',1,'Althegnenberg');
INSERT INTO `area_codes` VALUES ('49','08203',1,'Grossaitingen');
INSERT INTO `area_codes` VALUES ('49','08204',1,'Mickhausen');
INSERT INTO `area_codes` VALUES ('49','08205',1,'Dasing');
INSERT INTO `area_codes` VALUES ('49','08206',1,'Egling a d Paar');
INSERT INTO `area_codes` VALUES ('49','08207',1,'Affing');
INSERT INTO `area_codes` VALUES ('49','08208',1,'Eurasburg b Augsburg');
INSERT INTO `area_codes` VALUES ('49','0821',1,'Augsburg');
INSERT INTO `area_codes` VALUES ('49','08221',1,'Günzburg');
INSERT INTO `area_codes` VALUES ('49','08222',1,'Burgau Schwab');
INSERT INTO `area_codes` VALUES ('49','08223',1,'Ichenhausen');
INSERT INTO `area_codes` VALUES ('49','08224',1,'Offingen Donau');
INSERT INTO `area_codes` VALUES ('49','08225',1,'Jettingen-Scheppach');
INSERT INTO `area_codes` VALUES ('49','08226',1,'Bibertal');
INSERT INTO `area_codes` VALUES ('49','08230',1,'Gablingen');
INSERT INTO `area_codes` VALUES ('49','08231',1,'Königsbrunn b Augsburg');
INSERT INTO `area_codes` VALUES ('49','08232',1,'Schwabmünchen');
INSERT INTO `area_codes` VALUES ('49','08233',1,'Kissing');
INSERT INTO `area_codes` VALUES ('49','08234',1,'Bobingen');
INSERT INTO `area_codes` VALUES ('49','08236',1,'Fischach');
INSERT INTO `area_codes` VALUES ('49','08237',1,'Aindling');
INSERT INTO `area_codes` VALUES ('49','08238',1,'Gessertshausen');
INSERT INTO `area_codes` VALUES ('49','08239',1,'Langenneufnach');
INSERT INTO `area_codes` VALUES ('49','08241',1,'Buchloe');
INSERT INTO `area_codes` VALUES ('49','08243',1,'Fuchstal');
INSERT INTO `area_codes` VALUES ('49','08245',1,'Türkheim Wertach');
INSERT INTO `area_codes` VALUES ('49','08246',1,'Waal');
INSERT INTO `area_codes` VALUES ('49','08247',1,'Bad Wörishofen');
INSERT INTO `area_codes` VALUES ('49','08248',1,'Lamerdingen');
INSERT INTO `area_codes` VALUES ('49','08249',1,'Ettringen  Wertach');
INSERT INTO `area_codes` VALUES ('49','08250',1,'Hilgertshausen-Tandern');
INSERT INTO `area_codes` VALUES ('49','08251',1,'Aichach');
INSERT INTO `area_codes` VALUES ('49','08252',1,'Schrobenhausen');
INSERT INTO `area_codes` VALUES ('49','08253',1,'Pöttmes');
INSERT INTO `area_codes` VALUES ('49','08254',1,'Altomünster');
INSERT INTO `area_codes` VALUES ('49','08257',1,'Inchenhofen');
INSERT INTO `area_codes` VALUES ('49','08258',1,'Sielenbach');
INSERT INTO `area_codes` VALUES ('49','08259',1,'Schiltberg');
INSERT INTO `area_codes` VALUES ('49','08261',1,'Mindelheim');
INSERT INTO `area_codes` VALUES ('49','08262',1,'Mittelneufnach');
INSERT INTO `area_codes` VALUES ('49','08263',1,'Breitenbrunn Schwab');
INSERT INTO `area_codes` VALUES ('49','08265',1,'Pfaffenhausen Schwab');
INSERT INTO `area_codes` VALUES ('49','08266',1,'Kirchheim i Schw');
INSERT INTO `area_codes` VALUES ('49','08267',1,'Dirlewang');
INSERT INTO `area_codes` VALUES ('49','08268',1,'Tussenhausen');
INSERT INTO `area_codes` VALUES ('49','08269',1,'Unteregg b Mindelheim');
INSERT INTO `area_codes` VALUES ('49','08271',1,'Meitingen');
INSERT INTO `area_codes` VALUES ('49','08272',1,'Wertingen');
INSERT INTO `area_codes` VALUES ('49','08273',1,'Nordendorf');
INSERT INTO `area_codes` VALUES ('49','08274',1,'Buttenwiesen');
INSERT INTO `area_codes` VALUES ('49','08276',1,'Baar Schwaben');
INSERT INTO `area_codes` VALUES ('49','08281',1,'Thannhausen Schwab');
INSERT INTO `area_codes` VALUES ('49','08282',1,'Krumbach Schwaben');
INSERT INTO `area_codes` VALUES ('49','08283',1,'Neuburg a d Kammel');
INSERT INTO `area_codes` VALUES ('49','08284',1,'Ziemetshausen');
INSERT INTO `area_codes` VALUES ('49','08285',1,'Burtenbach');
INSERT INTO `area_codes` VALUES ('49','08291',1,'Zusmarshausen');
INSERT INTO `area_codes` VALUES ('49','08292',1,'Dinkelscherben');
INSERT INTO `area_codes` VALUES ('49','08293',1,'Welden b Augsburg');
INSERT INTO `area_codes` VALUES ('49','08294',1,'Horgau');
INSERT INTO `area_codes` VALUES ('49','08295',1,'Altenmünster Schwab');
INSERT INTO `area_codes` VALUES ('49','08296',1,'Villenbach');
INSERT INTO `area_codes` VALUES ('49','08302',1,'Görisried');
INSERT INTO `area_codes` VALUES ('49','08303',1,'Waltenhofen');
INSERT INTO `area_codes` VALUES ('49','08304',1,'Wildpoldsried');
INSERT INTO `area_codes` VALUES ('49','08306',1,'Ronsberg');
INSERT INTO `area_codes` VALUES ('49','0831',1,'Kempten Allgäu');
INSERT INTO `area_codes` VALUES ('49','08320',1,'Missen-Wilhams');
INSERT INTO `area_codes` VALUES ('49','08321',1,'Sonthofen');
INSERT INTO `area_codes` VALUES ('49','08322',1,'Oberstdorf');
INSERT INTO `area_codes` VALUES ('49','08323',1,'Immenstadt i Allgäu');
INSERT INTO `area_codes` VALUES ('49','08324',1,'Hindelang');
INSERT INTO `area_codes` VALUES ('49','08325',1,'Oberstaufen-Thalkirchdorf');
INSERT INTO `area_codes` VALUES ('49','08326',1,'Fischen  i Allgäu');
INSERT INTO `area_codes` VALUES ('49','08327',1,'Rettenberg');
INSERT INTO `area_codes` VALUES ('49','08328',1,'Balderschwang');
INSERT INTO `area_codes` VALUES ('49','08329',1,'Riezlern (Österreich)');
INSERT INTO `area_codes` VALUES ('49','08330',1,'Legau');
INSERT INTO `area_codes` VALUES ('49','08331',1,'Memmingen');
INSERT INTO `area_codes` VALUES ('49','08332',1,'Ottobeuren');
INSERT INTO `area_codes` VALUES ('49','08333',1,'Babenhausen Schwab');
INSERT INTO `area_codes` VALUES ('49','08334',1,'Bad Grönenbach');
INSERT INTO `area_codes` VALUES ('49','08335',1,'Fellheim');
INSERT INTO `area_codes` VALUES ('49','08336',1,'Erkheim');
INSERT INTO `area_codes` VALUES ('49','08337',1,'Altenstadt Iller');
INSERT INTO `area_codes` VALUES ('49','08338',1,'Böhen');
INSERT INTO `area_codes` VALUES ('49','08340',1,'Baisweil');
INSERT INTO `area_codes` VALUES ('49','08341',1,'Kaufbeuren');
INSERT INTO `area_codes` VALUES ('49','08342',1,'Marktoberdorf');
INSERT INTO `area_codes` VALUES ('49','08343',1,'Aitrang');
INSERT INTO `area_codes` VALUES ('49','08344',1,'Westendorf b Kaufbeuren');
INSERT INTO `area_codes` VALUES ('49','08345',1,'Stöttwang');
INSERT INTO `area_codes` VALUES ('49','08346',1,'Pforzen');
INSERT INTO `area_codes` VALUES ('49','08347',1,'Friesenried');
INSERT INTO `area_codes` VALUES ('49','08348',1,'Bidingen');
INSERT INTO `area_codes` VALUES ('49','08349',1,'Stötten a Auerberg');
INSERT INTO `area_codes` VALUES ('49','08361',1,'Nesselwang');
INSERT INTO `area_codes` VALUES ('49','08362',1,'Füssen');
INSERT INTO `area_codes` VALUES ('49','08363',1,'Pfronten');
INSERT INTO `area_codes` VALUES ('49','08364',1,'Seeg');
INSERT INTO `area_codes` VALUES ('49','08365',1,'Wertach');
INSERT INTO `area_codes` VALUES ('49','08366',1,'Oy-Mittelberg');
INSERT INTO `area_codes` VALUES ('49','08367',1,'Roßhaupten Forggensee');
INSERT INTO `area_codes` VALUES ('49','08368',1,'Halblech');
INSERT INTO `area_codes` VALUES ('49','08369',1,'Rückholz');
INSERT INTO `area_codes` VALUES ('49','08370',1,'Wiggensbach');
INSERT INTO `area_codes` VALUES ('49','08372',1,'Obergünzburg');
INSERT INTO `area_codes` VALUES ('49','08373',1,'Altusried');
INSERT INTO `area_codes` VALUES ('49','08374',1,'Dietmannsried');
INSERT INTO `area_codes` VALUES ('49','08375',1,'Weitnau');
INSERT INTO `area_codes` VALUES ('49','08376',1,'Sulzberg Allgäu');
INSERT INTO `area_codes` VALUES ('49','08377',1,'Unterthingau');
INSERT INTO `area_codes` VALUES ('49','08378',1,'Buchenberg b Kempten');
INSERT INTO `area_codes` VALUES ('49','08379',1,'Waltenhofen-Oberdorf');
INSERT INTO `area_codes` VALUES ('49','08380',1,'Achberg');
INSERT INTO `area_codes` VALUES ('49','08381',1,'Lindenberg  i Allgäu');
INSERT INTO `area_codes` VALUES ('49','08382',1,'Lindau Bodensee');
INSERT INTO `area_codes` VALUES ('49','08383',1,'Grünenbach Allgäu');
INSERT INTO `area_codes` VALUES ('49','08384',1,'Röthenbach Allgäu');
INSERT INTO `area_codes` VALUES ('49','08385',1,'Hergatz');
INSERT INTO `area_codes` VALUES ('49','08386',1,'Oberstaufen');
INSERT INTO `area_codes` VALUES ('49','08387',1,'Weiler-Simmerberg');
INSERT INTO `area_codes` VALUES ('49','08388',1,'Hergensweiler');
INSERT INTO `area_codes` VALUES ('49','08389',1,'Weissensberg');
INSERT INTO `area_codes` VALUES ('49','08392',1,'Markt Rettenbach');
INSERT INTO `area_codes` VALUES ('49','08393',1,'Holzgünz');
INSERT INTO `area_codes` VALUES ('49','08394',1,'Lautrach');
INSERT INTO `area_codes` VALUES ('49','08395',1,'Tannheim Württ');
INSERT INTO `area_codes` VALUES ('49','08402',1,'Münchsmünster');
INSERT INTO `area_codes` VALUES ('49','08403',1,'Pförring');
INSERT INTO `area_codes` VALUES ('49','08404',1,'Oberdolling');
INSERT INTO `area_codes` VALUES ('49','08405',1,'Stammham b Ingolstadt');
INSERT INTO `area_codes` VALUES ('49','08406',1,'Böhmfeld');
INSERT INTO `area_codes` VALUES ('49','08407',1,'Grossmehring');
INSERT INTO `area_codes` VALUES ('49','0841',1,'Ingolstadt Donau');
INSERT INTO `area_codes` VALUES ('49','08421',1,'Eichstätt Bay');
INSERT INTO `area_codes` VALUES ('49','08422',1,'Dollnstein');
INSERT INTO `area_codes` VALUES ('49','08423',1,'Titting');
INSERT INTO `area_codes` VALUES ('49','08424',1,'Nassenfels');
INSERT INTO `area_codes` VALUES ('49','08426',1,'Walting Kr Eichstätt');
INSERT INTO `area_codes` VALUES ('49','08427',1,'Wellheim');
INSERT INTO `area_codes` VALUES ('49','08431',1,'Neuburg  a d Donau');
INSERT INTO `area_codes` VALUES ('49','08432',1,'Burgheim');
INSERT INTO `area_codes` VALUES ('49','08433',1,'Königsmoos');
INSERT INTO `area_codes` VALUES ('49','08434',1,'Rennertshofen');
INSERT INTO `area_codes` VALUES ('49','08435',1,'Ehekirchen');
INSERT INTO `area_codes` VALUES ('49','08441',1,'Pfaffenhofen a d Ilm');
INSERT INTO `area_codes` VALUES ('49','08442',1,'Wolnzach');
INSERT INTO `area_codes` VALUES ('49','08443',1,'Hohenwart Paar');
INSERT INTO `area_codes` VALUES ('49','08444',1,'Schweitenkirchen');
INSERT INTO `area_codes` VALUES ('49','08445',1,'Gerolsbach');
INSERT INTO `area_codes` VALUES ('49','08446',1,'Pörnbach');
INSERT INTO `area_codes` VALUES ('49','08450',1,'Ingolstadt-Zuchering');
INSERT INTO `area_codes` VALUES ('49','08452',1,'Geisenfeld');
INSERT INTO `area_codes` VALUES ('49','08453',1,'Reichertshofen Oberbay');
INSERT INTO `area_codes` VALUES ('49','08454',1,'Karlshuld');
INSERT INTO `area_codes` VALUES ('49','08456',1,'Lenting');
INSERT INTO `area_codes` VALUES ('49','08457',1,'Vohburg a d Donau');
INSERT INTO `area_codes` VALUES ('49','08458',1,'Gaimersheim');
INSERT INTO `area_codes` VALUES ('49','08459',1,'Manching');
INSERT INTO `area_codes` VALUES ('49','08460',1,'Berching-Holnstein');
INSERT INTO `area_codes` VALUES ('49','08461',1,'Beilngries');
INSERT INTO `area_codes` VALUES ('49','08462',1,'Berching');
INSERT INTO `area_codes` VALUES ('49','08463',1,'Greding');
INSERT INTO `area_codes` VALUES ('49','08464',1,'Dietfurt a d Altmühl');
INSERT INTO `area_codes` VALUES ('49','08465',1,'Kipfenberg');
INSERT INTO `area_codes` VALUES ('49','08466',1,'Denkendorf Oberbay');
INSERT INTO `area_codes` VALUES ('49','08467',1,'Kinding');
INSERT INTO `area_codes` VALUES ('49','08468',1,'Altmannstein-Pondorf');
INSERT INTO `area_codes` VALUES ('49','08469',1,'Freystadt-Burggriesbach');
INSERT INTO `area_codes` VALUES ('49','08501',1,'Thyrnau');
INSERT INTO `area_codes` VALUES ('49','08502',1,'Fürstenzell');
INSERT INTO `area_codes` VALUES ('49','08503',1,'Neuhaus a Inn');
INSERT INTO `area_codes` VALUES ('49','08504',1,'Tittling');
INSERT INTO `area_codes` VALUES ('49','08505',1,'Hutthurm');
INSERT INTO `area_codes` VALUES ('49','08506',1,'Bad Höhenstadt');
INSERT INTO `area_codes` VALUES ('49','08507',1,'Neuburg a Inn');
INSERT INTO `area_codes` VALUES ('49','08509',1,'Ruderting');
INSERT INTO `area_codes` VALUES ('49','0851',1,'Passau');
INSERT INTO `area_codes` VALUES ('49','08531',1,'Pocking');
INSERT INTO `area_codes` VALUES ('49','08532',1,'Griesbach i Rottal');
INSERT INTO `area_codes` VALUES ('49','08533',1,'Rotthalmünster');
INSERT INTO `area_codes` VALUES ('49','08534',1,'Tettenweis');
INSERT INTO `area_codes` VALUES ('49','08535',1,'Haarbach');
INSERT INTO `area_codes` VALUES ('49','08536',1,'Kößlarn');
INSERT INTO `area_codes` VALUES ('49','08537',1,'Bad Füssing-Aigen');
INSERT INTO `area_codes` VALUES ('49','08538',1,'Pocking-Hartkirchen');
INSERT INTO `area_codes` VALUES ('49','08541',1,'Vilshofen Niederbay');
INSERT INTO `area_codes` VALUES ('49','08542',1,'Ortenburg');
INSERT INTO `area_codes` VALUES ('49','08543',1,'Aidenbach');
INSERT INTO `area_codes` VALUES ('49','08544',1,'Eging a See');
INSERT INTO `area_codes` VALUES ('49','08545',1,'Hofkirchen Bay');
INSERT INTO `area_codes` VALUES ('49','08546',1,'Windorf-Otterskirchen');
INSERT INTO `area_codes` VALUES ('49','08547',1,'Osterhofen-Gergweis');
INSERT INTO `area_codes` VALUES ('49','08548',1,'Vilshofen-Sandbach');
INSERT INTO `area_codes` VALUES ('49','08549',1,'Vilshofen-Pleinting');
INSERT INTO `area_codes` VALUES ('49','08550',1,'Philippsreut');
INSERT INTO `area_codes` VALUES ('49','08551',1,'Freyung');
INSERT INTO `area_codes` VALUES ('49','08552',1,'Grafenau Niederbay');
INSERT INTO `area_codes` VALUES ('49','08553',1,'Spiegelau');
INSERT INTO `area_codes` VALUES ('49','08554',1,'Schönberg Niederbay');
INSERT INTO `area_codes` VALUES ('49','08555',1,'Perlesreut');
INSERT INTO `area_codes` VALUES ('49','08556',1,'Haidmühle');
INSERT INTO `area_codes` VALUES ('49','08557',1,'Mauth');
INSERT INTO `area_codes` VALUES ('49','08558',1,'Hohenau Niederbay');
INSERT INTO `area_codes` VALUES ('49','08561',1,'Pfarrkirchen Niederbay');
INSERT INTO `area_codes` VALUES ('49','08562',1,'Triftern');
INSERT INTO `area_codes` VALUES ('49','08563',1,'Bad Birnbach Rottal');
INSERT INTO `area_codes` VALUES ('49','08564',1,'Johanniskirchen');
INSERT INTO `area_codes` VALUES ('49','08565',1,'Dietersburg-Baumgarten');
INSERT INTO `area_codes` VALUES ('49','08571',1,'Simbach a Inn');
INSERT INTO `area_codes` VALUES ('49','08572',1,'Tann Niederbay');
INSERT INTO `area_codes` VALUES ('49','08573',1,'Ering');
INSERT INTO `area_codes` VALUES ('49','08574',1,'Wittibreut');
INSERT INTO `area_codes` VALUES ('49','08581',1,'Waldkirchen Niederbay');
INSERT INTO `area_codes` VALUES ('49','08582',1,'Röhrnbach');
INSERT INTO `area_codes` VALUES ('49','08583',1,'Neureichenau');
INSERT INTO `area_codes` VALUES ('49','08584',1,'Breitenberg Niederbay');
INSERT INTO `area_codes` VALUES ('49','08585',1,'Grainet');
INSERT INTO `area_codes` VALUES ('49','08586',1,'Hauzenberg');
INSERT INTO `area_codes` VALUES ('49','08591',1,'Obernzell');
INSERT INTO `area_codes` VALUES ('49','08592',1,'Wegscheid Niederbay');
INSERT INTO `area_codes` VALUES ('49','08593',1,'Untergriesbach');
INSERT INTO `area_codes` VALUES ('49','0861',1,'Traunstein');
INSERT INTO `area_codes` VALUES ('49','08621',1,'Trostberg');
INSERT INTO `area_codes` VALUES ('49','08622',1,'Tacherting- Peterskirchen');
INSERT INTO `area_codes` VALUES ('49','08623',1,'Kirchweidach');
INSERT INTO `area_codes` VALUES ('49','08624',1,'Obing');
INSERT INTO `area_codes` VALUES ('49','08628',1,'Kienberg Oberbay');
INSERT INTO `area_codes` VALUES ('49','08629',1,'Palling');
INSERT INTO `area_codes` VALUES ('49','08630',1,'Oberneukirchen');
INSERT INTO `area_codes` VALUES ('49','08631',1,'Mühldorf a Inn');
INSERT INTO `area_codes` VALUES ('49','08633',1,'Tüßling');
INSERT INTO `area_codes` VALUES ('49','08634',1,'Garching a d Alz');
INSERT INTO `area_codes` VALUES ('49','08635',1,'Pleiskirchen');
INSERT INTO `area_codes` VALUES ('49','08636',1,'Ampfing');
INSERT INTO `area_codes` VALUES ('49','08637',1,'Lohkirchen');
INSERT INTO `area_codes` VALUES ('49','08638',1,'Waldkraiburg');
INSERT INTO `area_codes` VALUES ('49','08639',1,'Neumarkt-Sankt Veit');
INSERT INTO `area_codes` VALUES ('49','08640',1,'Reit Im Winkl');
INSERT INTO `area_codes` VALUES ('49','08641',1,'Grassau Kr Traunstein');
INSERT INTO `area_codes` VALUES ('49','08642',1,'Übersee');
INSERT INTO `area_codes` VALUES ('49','08649',1,'Schleching');
INSERT INTO `area_codes` VALUES ('49','08650',1,'Marktschellenberg');
INSERT INTO `area_codes` VALUES ('49','08651',1,'Bad Reichenhall');
INSERT INTO `area_codes` VALUES ('49','08652',1,'Berchtesgaden');
INSERT INTO `area_codes` VALUES ('49','08654',1,'Freilassing');
INSERT INTO `area_codes` VALUES ('49','08656',1,'Anger');
INSERT INTO `area_codes` VALUES ('49','08657',1,'Ramsau b Berchtesgaden');
INSERT INTO `area_codes` VALUES ('49','08661',1,'Grabenstätt Chiemsee');
INSERT INTO `area_codes` VALUES ('49','08662',1,'Siegsdorf Kr Traunstein');
INSERT INTO `area_codes` VALUES ('49','08663',1,'Ruhpolding');
INSERT INTO `area_codes` VALUES ('49','08664',1,'Chieming');
INSERT INTO `area_codes` VALUES ('49','08665',1,'Inzell');
INSERT INTO `area_codes` VALUES ('49','08666',1,'Teisendorf');
INSERT INTO `area_codes` VALUES ('49','08667',1,'Seeon-Seebruck');
INSERT INTO `area_codes` VALUES ('49','08669',1,'Traunreut');
INSERT INTO `area_codes` VALUES ('49','08670',1,'Reischach Kr Altötting');
INSERT INTO `area_codes` VALUES ('49','08671',1,'Altötting');
INSERT INTO `area_codes` VALUES ('49','08677',1,'Burghausen Salzach');
INSERT INTO `area_codes` VALUES ('49','08678',1,'Marktl');
INSERT INTO `area_codes` VALUES ('49','08679',1,'Burgkirchen a d Alz');
INSERT INTO `area_codes` VALUES ('49','08681',1,'Waging a See');
INSERT INTO `area_codes` VALUES ('49','08682',1,'Laufen Salzach');
INSERT INTO `area_codes` VALUES ('49','08683',1,'Tittmoning');
INSERT INTO `area_codes` VALUES ('49','08684',1,'Fridolfing');
INSERT INTO `area_codes` VALUES ('49','08685',1,'Kirchanschöring');
INSERT INTO `area_codes` VALUES ('49','08686',1,'Petting');
INSERT INTO `area_codes` VALUES ('49','08687',1,'Taching-Tengling');
INSERT INTO `area_codes` VALUES ('49','08702',1,'Wörth a d Isar');
INSERT INTO `area_codes` VALUES ('49','08703',1,'Essenbach');
INSERT INTO `area_codes` VALUES ('49','08704',1,'Altdorf-Pfettrach');
INSERT INTO `area_codes` VALUES ('49','08705',1,'Altfraunhofen');
INSERT INTO `area_codes` VALUES ('49','08706',1,'Vilsheim');
INSERT INTO `area_codes` VALUES ('49','08707',1,'Adlkofen');
INSERT INTO `area_codes` VALUES ('49','08708',1,'Weihmichl-Unterneuhausen');
INSERT INTO `area_codes` VALUES ('49','08709',1,'Eching Niederbay');
INSERT INTO `area_codes` VALUES ('49','0871',1,'Landshut');
INSERT INTO `area_codes` VALUES ('49','08721',1,'Eggenfelden');
INSERT INTO `area_codes` VALUES ('49','08722',1,'Gangkofen');
INSERT INTO `area_codes` VALUES ('49','08723',1,'Arnstorf');
INSERT INTO `area_codes` VALUES ('49','08724',1,'Massing');
INSERT INTO `area_codes` VALUES ('49','08725',1,'Wurmannsquick');
INSERT INTO `area_codes` VALUES ('49','08726',1,'Schönau Niederbay');
INSERT INTO `area_codes` VALUES ('49','08727',1,'Falkenberg Niederbay');
INSERT INTO `area_codes` VALUES ('49','08728',1,'Geratskirchen');
INSERT INTO `area_codes` VALUES ('49','08731',1,'Dingolfing');
INSERT INTO `area_codes` VALUES ('49','08732',1,'Frontenhausen');
INSERT INTO `area_codes` VALUES ('49','08733',1,'Mengkofen');
INSERT INTO `area_codes` VALUES ('49','08734',1,'Reisbach Niederbay');
INSERT INTO `area_codes` VALUES ('49','08735',1,'Gangkofen-Kollbach');
INSERT INTO `area_codes` VALUES ('49','08741',1,'Vilsbiburg');
INSERT INTO `area_codes` VALUES ('49','08742',1,'Velden Vils');
INSERT INTO `area_codes` VALUES ('49','08743',1,'Geisenhausen');
INSERT INTO `area_codes` VALUES ('49','08744',1,'Gerzen');
INSERT INTO `area_codes` VALUES ('49','08745',1,'Bodenkirchen');
INSERT INTO `area_codes` VALUES ('49','08751',1,'Mainburg');
INSERT INTO `area_codes` VALUES ('49','08752',1,'Au i d Hallertau');
INSERT INTO `area_codes` VALUES ('49','08753',1,'Elsendorf Niederbay');
INSERT INTO `area_codes` VALUES ('49','08754',1,'Volkenschwand');
INSERT INTO `area_codes` VALUES ('49','08756',1,'Nandlstadt');
INSERT INTO `area_codes` VALUES ('49','08761',1,'Moosburg a d Isar');
INSERT INTO `area_codes` VALUES ('49','08762',1,'Wartenberg Oberbay');
INSERT INTO `area_codes` VALUES ('49','08764',1,'Mauern Kr Freising');
INSERT INTO `area_codes` VALUES ('49','08765',1,'Bruckberg Niederbay');
INSERT INTO `area_codes` VALUES ('49','08766',1,'Gammelsdorf');
INSERT INTO `area_codes` VALUES ('49','08771',1,'Ergoldsbach');
INSERT INTO `area_codes` VALUES ('49','08772',1,'Mallersdorf-Pfaffenberg');
INSERT INTO `area_codes` VALUES ('49','08773',1,'Neufahrn i NB');
INSERT INTO `area_codes` VALUES ('49','08774',1,'Bayerbach b Ergoldsbach');
INSERT INTO `area_codes` VALUES ('49','08781',1,'Rottenburg a d Laaber');
INSERT INTO `area_codes` VALUES ('49','08782',1,'Pfeffenhausen');
INSERT INTO `area_codes` VALUES ('49','08783',1,'Rohr i NB');
INSERT INTO `area_codes` VALUES ('49','08784',1,'Hohenthann');
INSERT INTO `area_codes` VALUES ('49','08785',1,'Rottenburg-Oberroning');
INSERT INTO `area_codes` VALUES ('49','08801',1,'Seeshaupt');
INSERT INTO `area_codes` VALUES ('49','08802',1,'Huglfing');
INSERT INTO `area_codes` VALUES ('49','08803',1,'Peissenberg');
INSERT INTO `area_codes` VALUES ('49','08805',1,'Hohenpeissenberg');
INSERT INTO `area_codes` VALUES ('49','08806',1,'Utting a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08807',1,'Dießen a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08808',1,'Pähl');
INSERT INTO `area_codes` VALUES ('49','08809',1,'Wessobrunn');
INSERT INTO `area_codes` VALUES ('49','0881',1,'Weilheim i OB');
INSERT INTO `area_codes` VALUES ('49','08821',1,'Garmisch-Partenkirchen');
INSERT INTO `area_codes` VALUES ('49','08822',1,'Oberammergau');
INSERT INTO `area_codes` VALUES ('49','08823',1,'Mittenwald');
INSERT INTO `area_codes` VALUES ('49','08824',1,'Oberau Loisach');
INSERT INTO `area_codes` VALUES ('49','08825',1,'Krün');
INSERT INTO `area_codes` VALUES ('49','08841',1,'Murnau a Staffelsee');
INSERT INTO `area_codes` VALUES ('49','08845',1,'Bad Kohlgrub');
INSERT INTO `area_codes` VALUES ('49','08846',1,'Uffing a Staffelsee');
INSERT INTO `area_codes` VALUES ('49','08847',1,'Obersöchering');
INSERT INTO `area_codes` VALUES ('49','08851',1,'Kochel a See');
INSERT INTO `area_codes` VALUES ('49','08856',1,'Penzberg');
INSERT INTO `area_codes` VALUES ('49','08857',1,'Benediktbeuern');
INSERT INTO `area_codes` VALUES ('49','08858',1,'Kochel-Walchensee');
INSERT INTO `area_codes` VALUES ('49','08860',1,'Bernbeuren');
INSERT INTO `area_codes` VALUES ('49','08861',1,'Schongau');
INSERT INTO `area_codes` VALUES ('49','08862',1,'Steingaden Oberbay');
INSERT INTO `area_codes` VALUES ('49','08867',1,'Rottenbuch Oberbay');
INSERT INTO `area_codes` VALUES ('49','08868',1,'Schwabsoien');
INSERT INTO `area_codes` VALUES ('49','08869',1,'Kinsau');
INSERT INTO `area_codes` VALUES ('49','089',1,'München');
INSERT INTO `area_codes` VALUES ('49','0900',0,'Premium-Rate-Dienste');
INSERT INTO `area_codes` VALUES ('49','0901',0,'(Reserve für Telekommunikationsdienste)');
INSERT INTO `area_codes` VALUES ('49','0902',0,'(Reserve für Telekommunikationsdienste)');
INSERT INTO `area_codes` VALUES ('49','0903',0,'(Reserve für Telekommunikationsdienste)');
INSERT INTO `area_codes` VALUES ('49','0904',0,'(Reserve für Telekommunikationsdienste)');
INSERT INTO `area_codes` VALUES ('49','0905',0,'(Reserve für Telekommunikationsdienste)');
INSERT INTO `area_codes` VALUES ('49','0906',1,'Donauwörth');
INSERT INTO `area_codes` VALUES ('49','09070',1,'Tapfheim');
INSERT INTO `area_codes` VALUES ('49','09071',1,'Dillingen a d Donau');
INSERT INTO `area_codes` VALUES ('49','09072',1,'Lauingen Donau');
INSERT INTO `area_codes` VALUES ('49','09073',1,'Gundelfingen a d Donau');
INSERT INTO `area_codes` VALUES ('49','09074',1,'Höchstädt a d Donau');
INSERT INTO `area_codes` VALUES ('49','09075',1,'Glött');
INSERT INTO `area_codes` VALUES ('49','09076',1,'Wittislingen');
INSERT INTO `area_codes` VALUES ('49','09077',1,'Bachhagel');
INSERT INTO `area_codes` VALUES ('49','09078',1,'Mertingen');
INSERT INTO `area_codes` VALUES ('49','09080',1,'Harburg Schwaben');
INSERT INTO `area_codes` VALUES ('49','09081',1,'Nördlingen');
INSERT INTO `area_codes` VALUES ('49','09082',1,'Oettingen i Bay');
INSERT INTO `area_codes` VALUES ('49','09083',1,'Möttingen');
INSERT INTO `area_codes` VALUES ('49','09084',1,'Bissingen Schwab');
INSERT INTO `area_codes` VALUES ('49','09085',1,'Alerheim');
INSERT INTO `area_codes` VALUES ('49','09086',1,'Fremdingen');
INSERT INTO `area_codes` VALUES ('49','09087',1,'Marktoffingen');
INSERT INTO `area_codes` VALUES ('49','09088',1,'Mönchsdeggingen');
INSERT INTO `area_codes` VALUES ('49','09089',1,'Bissingen-Unterringingen');
INSERT INTO `area_codes` VALUES ('49','09090',1,'Rain Lech');
INSERT INTO `area_codes` VALUES ('49','09091',1,'Monheim Schwab');
INSERT INTO `area_codes` VALUES ('49','09092',1,'Wemding');
INSERT INTO `area_codes` VALUES ('49','09093',1,'Polsingen');
INSERT INTO `area_codes` VALUES ('49','09094',1,'Tagmersheim');
INSERT INTO `area_codes` VALUES ('49','09097',1,'Marxheim');
INSERT INTO `area_codes` VALUES ('49','09099',1,'Kaisheim');
INSERT INTO `area_codes` VALUES ('49','09101',1,'Langenzenn');
INSERT INTO `area_codes` VALUES ('49','09102',1,'Wilhermsdorf');
INSERT INTO `area_codes` VALUES ('49','09103',1,'Cadolzburg');
INSERT INTO `area_codes` VALUES ('49','09104',1,'Emskirchen');
INSERT INTO `area_codes` VALUES ('49','09105',1,'Grosshabersdorf');
INSERT INTO `area_codes` VALUES ('49','09106',1,'Markt Erlbach');
INSERT INTO `area_codes` VALUES ('49','09107',1,'Trautskirchen');
INSERT INTO `area_codes` VALUES ('49','0911',1,'Nürnberg');
INSERT INTO `area_codes` VALUES ('49','09120',1,'Leinburg');
INSERT INTO `area_codes` VALUES ('49','09122',1,'Schwabach');
INSERT INTO `area_codes` VALUES ('49','09123',1,'Lauf a d Pegnitz');
INSERT INTO `area_codes` VALUES ('49','09126',1,'Eckental');
INSERT INTO `area_codes` VALUES ('49','09127',1,'Rosstal Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09128',1,'Feucht');
INSERT INTO `area_codes` VALUES ('49','09129',1,'Wendelstein');
INSERT INTO `area_codes` VALUES ('49','09131',1,'Erlangen');
INSERT INTO `area_codes` VALUES ('49','09132',1,'Herzogenaurach');
INSERT INTO `area_codes` VALUES ('49','09133',1,'Baiersdorf Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09134',1,'Neunkirchen a Brand');
INSERT INTO `area_codes` VALUES ('49','09135',1,'Heßdorf Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09141',1,'Weißenburg i Bay');
INSERT INTO `area_codes` VALUES ('49','09142',1,'Treuchtlingen');
INSERT INTO `area_codes` VALUES ('49','09143',1,'Pappenheim Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09144',1,'Pleinfeld');
INSERT INTO `area_codes` VALUES ('49','09145',1,'Solnhofen');
INSERT INTO `area_codes` VALUES ('49','09146',1,'Markt Berolzheim');
INSERT INTO `area_codes` VALUES ('49','09147',1,'Nennslingen');
INSERT INTO `area_codes` VALUES ('49','09148',1,'Ettenstatt');
INSERT INTO `area_codes` VALUES ('49','09149',1,'Weissenburg-Suffersheim');
INSERT INTO `area_codes` VALUES ('49','09151',1,'Hersbruck');
INSERT INTO `area_codes` VALUES ('49','09152',1,'Hartenstein Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09153',1,'Schnaittach');
INSERT INTO `area_codes` VALUES ('49','09154',1,'Pommelsbrunn');
INSERT INTO `area_codes` VALUES ('49','09155',1,'Simmelsdorf');
INSERT INTO `area_codes` VALUES ('49','09156',1,'Neuhaus a d Pegnitz');
INSERT INTO `area_codes` VALUES ('49','09157',1,'Alfeld Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09158',1,'Offenhausen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09161',1,'Neustadt a d Aisch');
INSERT INTO `area_codes` VALUES ('49','09162',1,'Scheinfeld');
INSERT INTO `area_codes` VALUES ('49','09163',1,'Dachsbach');
INSERT INTO `area_codes` VALUES ('49','09164',1,'Langenfeld Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09165',1,'Sugenheim');
INSERT INTO `area_codes` VALUES ('49','09166',1,'Münchsteinach');
INSERT INTO `area_codes` VALUES ('49','09167',1,'Oberscheinfeld');
INSERT INTO `area_codes` VALUES ('49','09170',1,'Schwanstetten');
INSERT INTO `area_codes` VALUES ('49','09171',1,'Roth Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09172',1,'Georgensgmünd');
INSERT INTO `area_codes` VALUES ('49','09173',1,'Thalmässing');
INSERT INTO `area_codes` VALUES ('49','09174',1,'Hilpoltstein');
INSERT INTO `area_codes` VALUES ('49','09175',1,'Spalt');
INSERT INTO `area_codes` VALUES ('49','09176',1,'Allersberg');
INSERT INTO `area_codes` VALUES ('49','09177',1,'Heideck');
INSERT INTO `area_codes` VALUES ('49','09178',1,'Abenberg Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09179',1,'Freystadt');
INSERT INTO `area_codes` VALUES ('49','09180',1,'Pyrbaum');
INSERT INTO `area_codes` VALUES ('49','09181',1,'Neumarkt i d Opf');
INSERT INTO `area_codes` VALUES ('49','09182',1,'Velburg');
INSERT INTO `area_codes` VALUES ('49','09183',1,'Burgthann');
INSERT INTO `area_codes` VALUES ('49','09184',1,'Deining Oberpf');
INSERT INTO `area_codes` VALUES ('49','09185',1,'Mühlhausen Oberpf');
INSERT INTO `area_codes` VALUES ('49','09186',1,'Lauterhofen Oberpf');
INSERT INTO `area_codes` VALUES ('49','09187',1,'Altdorf b Nürnberg');
INSERT INTO `area_codes` VALUES ('49','09188',1,'Postbauer-Heng');
INSERT INTO `area_codes` VALUES ('49','09189',1,'Berg b Neumarkt i d Opf');
INSERT INTO `area_codes` VALUES ('49','09190',1,'Heroldsbach');
INSERT INTO `area_codes` VALUES ('49','09191',1,'Forchheim Oberfr');
INSERT INTO `area_codes` VALUES ('49','09192',1,'Gräfenberg');
INSERT INTO `area_codes` VALUES ('49','09193',1,'Höchstadt a d Aisch');
INSERT INTO `area_codes` VALUES ('49','09194',1,'Ebermannstadt');
INSERT INTO `area_codes` VALUES ('49','09195',1,'Adelsdorf Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09196',1,'Wiesenttal');
INSERT INTO `area_codes` VALUES ('49','09197',1,'Egloffstein');
INSERT INTO `area_codes` VALUES ('49','09198',1,'Heiligenstadt i Ofr');
INSERT INTO `area_codes` VALUES ('49','09199',1,'Kunreuth');
INSERT INTO `area_codes` VALUES ('49','09201',1,'Gesees');
INSERT INTO `area_codes` VALUES ('49','09202',1,'Waischenfeld');
INSERT INTO `area_codes` VALUES ('49','09203',1,'Neudrossenfeld');
INSERT INTO `area_codes` VALUES ('49','09204',1,'Plankenfels');
INSERT INTO `area_codes` VALUES ('49','09205',1,'Vorbach');
INSERT INTO `area_codes` VALUES ('49','09206',1,'Mistelgau-Obernsees');
INSERT INTO `area_codes` VALUES ('49','09207',1,'Königsfeld Oberfr');
INSERT INTO `area_codes` VALUES ('49','09208',1,'Bindlach');
INSERT INTO `area_codes` VALUES ('49','09209',1,'Emtmannsberg');
INSERT INTO `area_codes` VALUES ('49','0921',1,'Bayreuth');
INSERT INTO `area_codes` VALUES ('49','09220',1,'Kasendorf-Azendorf');
INSERT INTO `area_codes` VALUES ('49','09221',1,'Kulmbach');
INSERT INTO `area_codes` VALUES ('49','09222',1,'Presseck');
INSERT INTO `area_codes` VALUES ('49','09223',1,'Rugendorf');
INSERT INTO `area_codes` VALUES ('49','09225',1,'Stadtsteinach');
INSERT INTO `area_codes` VALUES ('49','09227',1,'Neuenmarkt');
INSERT INTO `area_codes` VALUES ('49','09228',1,'Thurnau');
INSERT INTO `area_codes` VALUES ('49','09229',1,'Mainleus');
INSERT INTO `area_codes` VALUES ('49','09231',1,'Marktredwitz');
INSERT INTO `area_codes` VALUES ('49','09232',1,'Wunsiedel');
INSERT INTO `area_codes` VALUES ('49','09233',1,'Arzberg Oberfr');
INSERT INTO `area_codes` VALUES ('49','09234',1,'Neusorg');
INSERT INTO `area_codes` VALUES ('49','09235',1,'Thierstein');
INSERT INTO `area_codes` VALUES ('49','09236',1,'Nagel');
INSERT INTO `area_codes` VALUES ('49','09238',1,'Röslau');
INSERT INTO `area_codes` VALUES ('49','09241',1,'Pegnitz');
INSERT INTO `area_codes` VALUES ('49','09242',1,'Gößweinstein');
INSERT INTO `area_codes` VALUES ('49','09243',1,'Pottenstein');
INSERT INTO `area_codes` VALUES ('49','09244',1,'Betzenstein');
INSERT INTO `area_codes` VALUES ('49','09245',1,'Obertrubach');
INSERT INTO `area_codes` VALUES ('49','09246',1,'Pegnitz-Trockau');
INSERT INTO `area_codes` VALUES ('49','09251',1,'Münchberg');
INSERT INTO `area_codes` VALUES ('49','09252',1,'Helmbrechts');
INSERT INTO `area_codes` VALUES ('49','09253',1,'Weissenstadt');
INSERT INTO `area_codes` VALUES ('49','09254',1,'Gefrees');
INSERT INTO `area_codes` VALUES ('49','09255',1,'Marktleugast');
INSERT INTO `area_codes` VALUES ('49','09256',1,'Stammbach');
INSERT INTO `area_codes` VALUES ('49','09257',1,'Zell Oberfr');
INSERT INTO `area_codes` VALUES ('49','09260',1,'Wilhelmsthal Oberfr');
INSERT INTO `area_codes` VALUES ('49','09261',1,'Kronach');
INSERT INTO `area_codes` VALUES ('49','09262',1,'Wallenfels');
INSERT INTO `area_codes` VALUES ('49','09263',1,'Ludwigsstadt');
INSERT INTO `area_codes` VALUES ('49','09264',1,'Küps');
INSERT INTO `area_codes` VALUES ('49','09265',1,'Pressig');
INSERT INTO `area_codes` VALUES ('49','09266',1,'Mitwitz');
INSERT INTO `area_codes` VALUES ('49','09267',1,'Nordhalben');
INSERT INTO `area_codes` VALUES ('49','09268',1,'Teuschnitz');
INSERT INTO `area_codes` VALUES ('49','09269',1,'Tettau Kr Kronach');
INSERT INTO `area_codes` VALUES ('49','09270',1,'Creussen');
INSERT INTO `area_codes` VALUES ('49','09271',1,'Thurnau-Alladorf');
INSERT INTO `area_codes` VALUES ('49','09272',1,'Fichtelberg');
INSERT INTO `area_codes` VALUES ('49','09273',1,'Bad Berneck i Fichtelgebirge');
INSERT INTO `area_codes` VALUES ('49','09274',1,'Hollfeld');
INSERT INTO `area_codes` VALUES ('49','09275',1,'Speichersdorf');
INSERT INTO `area_codes` VALUES ('49','09276',1,'Bischofsgrün');
INSERT INTO `area_codes` VALUES ('49','09277',1,'Warmensteinach');
INSERT INTO `area_codes` VALUES ('49','09278',1,'Weidenberg');
INSERT INTO `area_codes` VALUES ('49','09279',1,'Mistelgau');
INSERT INTO `area_codes` VALUES ('49','09280',1,'Selbitz Oberfr');
INSERT INTO `area_codes` VALUES ('49','09281',1,'Hof Saale');
INSERT INTO `area_codes` VALUES ('49','09282',1,'Naila');
INSERT INTO `area_codes` VALUES ('49','09283',1,'Rehau');
INSERT INTO `area_codes` VALUES ('49','09284',1,'Schwarzenbach a d Saale');
INSERT INTO `area_codes` VALUES ('49','09285',1,'Kirchenlamitz');
INSERT INTO `area_codes` VALUES ('49','09286',1,'Oberkotzau');
INSERT INTO `area_codes` VALUES ('49','09287',1,'Selb');
INSERT INTO `area_codes` VALUES ('49','09288',1,'Bad Steben');
INSERT INTO `area_codes` VALUES ('49','09289',1,'Schwarzenbach a Wald');
INSERT INTO `area_codes` VALUES ('49','09292',1,'Konradsreuth');
INSERT INTO `area_codes` VALUES ('49','09293',1,'Berg Oberfr');
INSERT INTO `area_codes` VALUES ('49','09294',1,'Regnitzlosau');
INSERT INTO `area_codes` VALUES ('49','09295',1,'Töpen');
INSERT INTO `area_codes` VALUES ('49','09302',1,'Rottendorf Unterfr');
INSERT INTO `area_codes` VALUES ('49','09303',1,'Eibelstadt');
INSERT INTO `area_codes` VALUES ('49','09305',1,'Estenfeld');
INSERT INTO `area_codes` VALUES ('49','09306',1,'Kist');
INSERT INTO `area_codes` VALUES ('49','09307',1,'Altertheim');
INSERT INTO `area_codes` VALUES ('49','0931',1,'Würzburg');
INSERT INTO `area_codes` VALUES ('49','09321',1,'Kitzingen');
INSERT INTO `area_codes` VALUES ('49','09323',1,'Iphofen');
INSERT INTO `area_codes` VALUES ('49','09324',1,'Dettelbach');
INSERT INTO `area_codes` VALUES ('49','09325',1,'Kleinlangheim');
INSERT INTO `area_codes` VALUES ('49','09326',1,'Markt Einersheim');
INSERT INTO `area_codes` VALUES ('49','09331',1,'Ochsenfurt');
INSERT INTO `area_codes` VALUES ('49','09332',1,'Marktbreit');
INSERT INTO `area_codes` VALUES ('49','09333',1,'Sommerhausen');
INSERT INTO `area_codes` VALUES ('49','09334',1,'Giebelstadt');
INSERT INTO `area_codes` VALUES ('49','09335',1,'Aub Kr Würzburg');
INSERT INTO `area_codes` VALUES ('49','09336',1,'Bütthard');
INSERT INTO `area_codes` VALUES ('49','09337',1,'Gaukönigshofen');
INSERT INTO `area_codes` VALUES ('49','09338',1,'Röttingen Unterfr');
INSERT INTO `area_codes` VALUES ('49','09339',1,'Ippesheim');
INSERT INTO `area_codes` VALUES ('49','09340',1,'Königheim-Brehmen');
INSERT INTO `area_codes` VALUES ('49','09341',1,'Tauberbischofsheim');
INSERT INTO `area_codes` VALUES ('49','09342',1,'Wertheim');
INSERT INTO `area_codes` VALUES ('49','09343',1,'Lauda-Königshofen');
INSERT INTO `area_codes` VALUES ('49','09344',1,'Gerchsheim');
INSERT INTO `area_codes` VALUES ('49','09345',1,'Külsheim Baden');
INSERT INTO `area_codes` VALUES ('49','09346',1,'Grünsfeld');
INSERT INTO `area_codes` VALUES ('49','09347',1,'Wittighausen');
INSERT INTO `area_codes` VALUES ('49','09348',1,'Werbach-Gamburg');
INSERT INTO `area_codes` VALUES ('49','09349',1,'Werbach-Wenkheim');
INSERT INTO `area_codes` VALUES ('49','09350',1,'Eussenheim-Hundsbach');
INSERT INTO `area_codes` VALUES ('49','09351',1,'Gemünden a Main');
INSERT INTO `area_codes` VALUES ('49','09352',1,'Lohr a Main');
INSERT INTO `area_codes` VALUES ('49','09353',1,'Karlstadt');
INSERT INTO `area_codes` VALUES ('49','09354',1,'Rieneck');
INSERT INTO `area_codes` VALUES ('49','09355',1,'Frammersbach');
INSERT INTO `area_codes` VALUES ('49','09356',1,'Burgsinn');
INSERT INTO `area_codes` VALUES ('49','09357',1,'Gräfendorf Bay');
INSERT INTO `area_codes` VALUES ('49','09358',1,'Gössenheim');
INSERT INTO `area_codes` VALUES ('49','09359',1,'Karlstadt-Wiesenfeld');
INSERT INTO `area_codes` VALUES ('49','09360',1,'Thüngen');
INSERT INTO `area_codes` VALUES ('49','09363',1,'Arnstein Unterfr');
INSERT INTO `area_codes` VALUES ('49','09364',1,'Zellingen');
INSERT INTO `area_codes` VALUES ('49','09365',1,'Rimpar');
INSERT INTO `area_codes` VALUES ('49','09366',1,'Geroldshausen Unterfr');
INSERT INTO `area_codes` VALUES ('49','09367',1,'Unterpleichfeld');
INSERT INTO `area_codes` VALUES ('49','09369',1,'Uettingen');
INSERT INTO `area_codes` VALUES ('49','09371',1,'Miltenberg');
INSERT INTO `area_codes` VALUES ('49','09372',1,'Klingenberg a Main');
INSERT INTO `area_codes` VALUES ('49','09373',1,'Amorbach');
INSERT INTO `area_codes` VALUES ('49','09374',1,'Eschau');
INSERT INTO `area_codes` VALUES ('49','09375',1,'Freudenberg Baden');
INSERT INTO `area_codes` VALUES ('49','09376',1,'Collenberg');
INSERT INTO `area_codes` VALUES ('49','09377',1,'Freudenberg-Boxtal');
INSERT INTO `area_codes` VALUES ('49','09378',1,'Eichenbühl-Riedern');
INSERT INTO `area_codes` VALUES ('49','09381',1,'Volkach');
INSERT INTO `area_codes` VALUES ('49','09382',1,'Gerolzhofen');
INSERT INTO `area_codes` VALUES ('49','09383',1,'Wiesentheid');
INSERT INTO `area_codes` VALUES ('49','09384',1,'Schwanfeld');
INSERT INTO `area_codes` VALUES ('49','09385',1,'Kolitzheim');
INSERT INTO `area_codes` VALUES ('49','09386',1,'Prosselsheim');
INSERT INTO `area_codes` VALUES ('49','09391',1,'Marktheidenfeld');
INSERT INTO `area_codes` VALUES ('49','09392',1,'Faulbach Unterfr');
INSERT INTO `area_codes` VALUES ('49','09393',1,'Rothenfels Unterfr');
INSERT INTO `area_codes` VALUES ('49','09394',1,'Esselbach');
INSERT INTO `area_codes` VALUES ('49','09395',1,'Triefenstein');
INSERT INTO `area_codes` VALUES ('49','09396',1,'Urspringen b Lohr');
INSERT INTO `area_codes` VALUES ('49','09397',1,'Wertheim-Dertingen');
INSERT INTO `area_codes` VALUES ('49','09398',1,'Birkenfeld b Würzburg');
INSERT INTO `area_codes` VALUES ('49','09401',1,'Neutraubling');
INSERT INTO `area_codes` VALUES ('49','09402',1,'Regenstauf');
INSERT INTO `area_codes` VALUES ('49','09403',1,'Donaustauf');
INSERT INTO `area_codes` VALUES ('49','09404',1,'Nittendorf');
INSERT INTO `area_codes` VALUES ('49','09405',1,'Bad Abbach');
INSERT INTO `area_codes` VALUES ('49','09406',1,'Mintraching');
INSERT INTO `area_codes` VALUES ('49','09407',1,'Wenzenbach');
INSERT INTO `area_codes` VALUES ('49','09408',1,'Altenthann');
INSERT INTO `area_codes` VALUES ('49','09409',1,'Pielenhofen');
INSERT INTO `area_codes` VALUES ('49','0941',1,'Regensburg');
INSERT INTO `area_codes` VALUES ('49','09420',1,'Feldkirchen Niederbay');
INSERT INTO `area_codes` VALUES ('49','09421',1,'Straubing');
INSERT INTO `area_codes` VALUES ('49','09422',1,'Bogen Niederbay');
INSERT INTO `area_codes` VALUES ('49','09423',1,'Geiselhöring');
INSERT INTO `area_codes` VALUES ('49','09424',1,'Strasskirchen');
INSERT INTO `area_codes` VALUES ('49','09426',1,'Oberschneiding');
INSERT INTO `area_codes` VALUES ('49','09427',1,'Leiblfing');
INSERT INTO `area_codes` VALUES ('49','09428',1,'Kirchroth');
INSERT INTO `area_codes` VALUES ('49','09429',1,'Rain Niederbay');
INSERT INTO `area_codes` VALUES ('49','09431',1,'Schwandorf');
INSERT INTO `area_codes` VALUES ('49','09433',1,'Nabburg');
INSERT INTO `area_codes` VALUES ('49','09434',1,'Bodenwöhr');
INSERT INTO `area_codes` VALUES ('49','09435',1,'Schwarzenfeld');
INSERT INTO `area_codes` VALUES ('49','09436',1,'Nittenau');
INSERT INTO `area_codes` VALUES ('49','09438',1,'Fensterbach');
INSERT INTO `area_codes` VALUES ('49','09439',1,'Neunburg-Kemnath');
INSERT INTO `area_codes` VALUES ('49','09441',1,'Kelheim');
INSERT INTO `area_codes` VALUES ('49','09442',1,'Riedenburg');
INSERT INTO `area_codes` VALUES ('49','09443',1,'Abensberg');
INSERT INTO `area_codes` VALUES ('49','09444',1,'Siegenburg');
INSERT INTO `area_codes` VALUES ('49','09445',1,'Neustadt a d Donau');
INSERT INTO `area_codes` VALUES ('49','09446',1,'Altmannstein');
INSERT INTO `area_codes` VALUES ('49','09447',1,'Essing');
INSERT INTO `area_codes` VALUES ('49','09448',1,'Hausen Niederbay');
INSERT INTO `area_codes` VALUES ('49','09451',1,'Schierling');
INSERT INTO `area_codes` VALUES ('49','09452',1,'Langquaid');
INSERT INTO `area_codes` VALUES ('49','09453',1,'Thalmassing');
INSERT INTO `area_codes` VALUES ('49','09454',1,'Aufhausen Oberpf');
INSERT INTO `area_codes` VALUES ('49','09461',1,'Roding');
INSERT INTO `area_codes` VALUES ('49','09462',1,'Falkenstein Oberpf');
INSERT INTO `area_codes` VALUES ('49','09463',1,'Wald Oberpf');
INSERT INTO `area_codes` VALUES ('49','09464',1,'Walderbach');
INSERT INTO `area_codes` VALUES ('49','09465',1,'Neukirchen-Balbini');
INSERT INTO `area_codes` VALUES ('49','09466',1,'Stamsried');
INSERT INTO `area_codes` VALUES ('49','09467',1,'Michelsneukirchen');
INSERT INTO `area_codes` VALUES ('49','09468',1,'Zell Oberpf');
INSERT INTO `area_codes` VALUES ('49','09469',1,'Roding-Neubäu');
INSERT INTO `area_codes` VALUES ('49','09471',1,'Burglengenfeld');
INSERT INTO `area_codes` VALUES ('49','09472',1,'Hohenfels  Oberpf');
INSERT INTO `area_codes` VALUES ('49','09473',1,'Kallmünz');
INSERT INTO `area_codes` VALUES ('49','09474',1,'Schmidmühlen');
INSERT INTO `area_codes` VALUES ('49','09480',1,'Sünching');
INSERT INTO `area_codes` VALUES ('49','09481',1,'Pfatter');
INSERT INTO `area_codes` VALUES ('49','09482',1,'Wörth a d Donau');
INSERT INTO `area_codes` VALUES ('49','09484',1,'Brennberg');
INSERT INTO `area_codes` VALUES ('49','09491',1,'Hemau');
INSERT INTO `area_codes` VALUES ('49','09492',1,'Parsberg');
INSERT INTO `area_codes` VALUES ('49','09493',1,'Beratzhausen');
INSERT INTO `area_codes` VALUES ('49','09495',1,'Breitenbrunn Oberpf');
INSERT INTO `area_codes` VALUES ('49','09497',1,'Seubersdorf i d Opf');
INSERT INTO `area_codes` VALUES ('49','09498',1,'Laaber');
INSERT INTO `area_codes` VALUES ('49','09499',1,'Painten');
INSERT INTO `area_codes` VALUES ('49','09502',1,'Frensdorf');
INSERT INTO `area_codes` VALUES ('49','09503',1,'Oberhaid Oberfr');
INSERT INTO `area_codes` VALUES ('49','09504',1,'Stadelhofen');
INSERT INTO `area_codes` VALUES ('49','09505',1,'Litzendorf');
INSERT INTO `area_codes` VALUES ('49','0951',1,'Bamberg');
INSERT INTO `area_codes` VALUES ('49','09521',1,'Hassfurt');
INSERT INTO `area_codes` VALUES ('49','09522',1,'Eltmann');
INSERT INTO `area_codes` VALUES ('49','09523',1,'Hofheim i Ufr');
INSERT INTO `area_codes` VALUES ('49','09524',1,'Zeil a Main');
INSERT INTO `area_codes` VALUES ('49','09525',1,'Königsberg i Bay');
INSERT INTO `area_codes` VALUES ('49','09526',1,'Riedbach');
INSERT INTO `area_codes` VALUES ('49','09527',1,'Knetzgau');
INSERT INTO `area_codes` VALUES ('49','09528',1,'Donnersdorf');
INSERT INTO `area_codes` VALUES ('49','09529',1,'Oberaurach');
INSERT INTO `area_codes` VALUES ('49','09531',1,'Ebern');
INSERT INTO `area_codes` VALUES ('49','09532',1,'Maroldsweisach');
INSERT INTO `area_codes` VALUES ('49','09533',1,'Untermerzbach');
INSERT INTO `area_codes` VALUES ('49','09534',1,'Burgpreppach');
INSERT INTO `area_codes` VALUES ('49','09535',1,'Pfarrweisach');
INSERT INTO `area_codes` VALUES ('49','09536',1,'Kirchlauter');
INSERT INTO `area_codes` VALUES ('49','09542',1,'Schesslitz');
INSERT INTO `area_codes` VALUES ('49','09543',1,'Hirschaid');
INSERT INTO `area_codes` VALUES ('49','09544',1,'Baunach');
INSERT INTO `area_codes` VALUES ('49','09545',1,'Buttenheim');
INSERT INTO `area_codes` VALUES ('49','09546',1,'Burgebrach');
INSERT INTO `area_codes` VALUES ('49','09547',1,'Zapfendorf');
INSERT INTO `area_codes` VALUES ('49','09548',1,'Mühlhausen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09549',1,'Lisberg');
INSERT INTO `area_codes` VALUES ('49','09551',1,'Burgwindheim');
INSERT INTO `area_codes` VALUES ('49','09552',1,'Burghaslach');
INSERT INTO `area_codes` VALUES ('49','09553',1,'Ebrach Oberfr');
INSERT INTO `area_codes` VALUES ('49','09554',1,'Untersteinbach Unterfr');
INSERT INTO `area_codes` VALUES ('49','09555',1,'Schlüsselfeld-Aschbach');
INSERT INTO `area_codes` VALUES ('49','09556',1,'Geiselwind');
INSERT INTO `area_codes` VALUES ('49','09560',1,'Grub a Forst');
INSERT INTO `area_codes` VALUES ('49','09561',1,'Coburg');
INSERT INTO `area_codes` VALUES ('49','09562',1,'Sonnefeld');
INSERT INTO `area_codes` VALUES ('49','09563',1,'Rödental');
INSERT INTO `area_codes` VALUES ('49','09564',1,'Bad Rodach');
INSERT INTO `area_codes` VALUES ('49','09565',1,'Untersiemau');
INSERT INTO `area_codes` VALUES ('49','09566',1,'Meeder');
INSERT INTO `area_codes` VALUES ('49','09567',1,'Seßlach-Gemünda');
INSERT INTO `area_codes` VALUES ('49','09568',1,'Neustadt b Coburg');
INSERT INTO `area_codes` VALUES ('49','09569',1,'Sesslach');
INSERT INTO `area_codes` VALUES ('49','09571',1,'Lichtenfels Bay');
INSERT INTO `area_codes` VALUES ('49','09572',1,'Burgkunstadt');
INSERT INTO `area_codes` VALUES ('49','09573',1,'Staffelstein Oberfr');
INSERT INTO `area_codes` VALUES ('49','09574',1,'Marktzeuln');
INSERT INTO `area_codes` VALUES ('49','09575',1,'Weismain');
INSERT INTO `area_codes` VALUES ('49','09576',1,'Lichtenfels-Isling');
INSERT INTO `area_codes` VALUES ('49','09602',1,'Neustadt a d Waldnaab');
INSERT INTO `area_codes` VALUES ('49','09603',1,'Floss');
INSERT INTO `area_codes` VALUES ('49','09604',1,'Wernberg-Köblitz');
INSERT INTO `area_codes` VALUES ('49','09605',1,'Weiherhammer');
INSERT INTO `area_codes` VALUES ('49','09606',1,'Pfreimd');
INSERT INTO `area_codes` VALUES ('49','09607',1,'Luhe-Wildenau');
INSERT INTO `area_codes` VALUES ('49','09608',1,'Kohlberg Oberpf');
INSERT INTO `area_codes` VALUES ('49','0961',1,'Weiden i d Opf');
INSERT INTO `area_codes` VALUES ('49','09621',1,'Amberg Oberpf');
INSERT INTO `area_codes` VALUES ('49','09622',1,'Hirschau Oberpf');
INSERT INTO `area_codes` VALUES ('49','09624',1,'Ensdorf Oberpf');
INSERT INTO `area_codes` VALUES ('49','09625',1,'Kastl b Amberg');
INSERT INTO `area_codes` VALUES ('49','09626',1,'Hohenburg');
INSERT INTO `area_codes` VALUES ('49','09627',1,'Freudenberg Oberpf');
INSERT INTO `area_codes` VALUES ('49','09628',1,'Ursensollen');
INSERT INTO `area_codes` VALUES ('49','09631',1,'Tirschenreuth');
INSERT INTO `area_codes` VALUES ('49','09632',1,'Waldsassen');
INSERT INTO `area_codes` VALUES ('49','09633',1,'Mitterteich');
INSERT INTO `area_codes` VALUES ('49','09634',1,'Wiesau');
INSERT INTO `area_codes` VALUES ('49','09635',1,'Bärnau');
INSERT INTO `area_codes` VALUES ('49','09636',1,'Plößberg');
INSERT INTO `area_codes` VALUES ('49','09637',1,'Falkenberg Oberpf');
INSERT INTO `area_codes` VALUES ('49','09638',1,'Neualbenreuth');
INSERT INTO `area_codes` VALUES ('49','09639',1,'Mähring');
INSERT INTO `area_codes` VALUES ('49','09641',1,'Grafenwöhr');
INSERT INTO `area_codes` VALUES ('49','09642',1,'Kemnath Stadt');
INSERT INTO `area_codes` VALUES ('49','09643',1,'Auerbach i d Opf');
INSERT INTO `area_codes` VALUES ('49','09644',1,'Pressath');
INSERT INTO `area_codes` VALUES ('49','09645',1,'Eschenbach i d Opf');
INSERT INTO `area_codes` VALUES ('49','09646',1,'Freihung');
INSERT INTO `area_codes` VALUES ('49','09647',1,'Kirchenthumbach');
INSERT INTO `area_codes` VALUES ('49','09648',1,'Neustadt a Kulm');
INSERT INTO `area_codes` VALUES ('49','09651',1,'Vohenstrauss');
INSERT INTO `area_codes` VALUES ('49','09652',1,'Waidhaus');
INSERT INTO `area_codes` VALUES ('49','09653',1,'Eslarn');
INSERT INTO `area_codes` VALUES ('49','09654',1,'Pleystein');
INSERT INTO `area_codes` VALUES ('49','09655',1,'Tännesberg');
INSERT INTO `area_codes` VALUES ('49','09656',1,'Moosbach b Vohenstrauß');
INSERT INTO `area_codes` VALUES ('49','09657',1,'Waldthurn');
INSERT INTO `area_codes` VALUES ('49','09658',1,'Georgenberg');
INSERT INTO `area_codes` VALUES ('49','09659',1,'Leuchtenberg');
INSERT INTO `area_codes` VALUES ('49','09661',1,'Sulzbach-Rosenberg');
INSERT INTO `area_codes` VALUES ('49','09662',1,'Vilseck');
INSERT INTO `area_codes` VALUES ('49','09663',1,'Neukirchen b Sulzbach-Rosenberg');
INSERT INTO `area_codes` VALUES ('49','09664',1,'Hahnbach');
INSERT INTO `area_codes` VALUES ('49','09665',1,'Königstein Oberpf');
INSERT INTO `area_codes` VALUES ('49','09666',1,'Illschwang');
INSERT INTO `area_codes` VALUES ('49','09671',1,'Oberviechtach');
INSERT INTO `area_codes` VALUES ('49','09672',1,'Neunburg vorm Wald');
INSERT INTO `area_codes` VALUES ('49','09673',1,'Tiefenbach Oberpf');
INSERT INTO `area_codes` VALUES ('49','09674',1,'Schönsee');
INSERT INTO `area_codes` VALUES ('49','09675',1,'Altendorf a Nabburg');
INSERT INTO `area_codes` VALUES ('49','09676',1,'Winklarn');
INSERT INTO `area_codes` VALUES ('49','09677',1,'Oberviechtach-Pullenried');
INSERT INTO `area_codes` VALUES ('49','09681',1,'Windischeschenbach');
INSERT INTO `area_codes` VALUES ('49','09682',1,'Erbendorf');
INSERT INTO `area_codes` VALUES ('49','09683',1,'Friedenfels');
INSERT INTO `area_codes` VALUES ('49','09701',1,'Sandberg Unterfr');
INSERT INTO `area_codes` VALUES ('49','09704',1,'Euerdorf');
INSERT INTO `area_codes` VALUES ('49','09708',1,'Bad Bocklet');
INSERT INTO `area_codes` VALUES ('49','0971',1,'Bad Kissingen');
INSERT INTO `area_codes` VALUES ('49','09720',1,'Üchtelhausen');
INSERT INTO `area_codes` VALUES ('49','09721',1,'Schweinfurt');
INSERT INTO `area_codes` VALUES ('49','09722',1,'Werneck');
INSERT INTO `area_codes` VALUES ('49','09723',1,'Röthlein');
INSERT INTO `area_codes` VALUES ('49','09724',1,'Stadtlauringen');
INSERT INTO `area_codes` VALUES ('49','09725',1,'Poppenhausen Unterfr');
INSERT INTO `area_codes` VALUES ('49','09726',1,'Euerbach');
INSERT INTO `area_codes` VALUES ('49','09727',1,'Schonungen-Marktsteinach');
INSERT INTO `area_codes` VALUES ('49','09728',1,'Wülfershausen Unterfr');
INSERT INTO `area_codes` VALUES ('49','09729',1,'Grettstadt');
INSERT INTO `area_codes` VALUES ('49','09732',1,'Hammelburg');
INSERT INTO `area_codes` VALUES ('49','09733',1,'Münnerstadt');
INSERT INTO `area_codes` VALUES ('49','09734',1,'Burkardroth');
INSERT INTO `area_codes` VALUES ('49','09735',1,'Massbach');
INSERT INTO `area_codes` VALUES ('49','09736',1,'Oberthulba');
INSERT INTO `area_codes` VALUES ('49','09737',1,'Wartmannsroth');
INSERT INTO `area_codes` VALUES ('49','09738',1,'Rottershausen');
INSERT INTO `area_codes` VALUES ('49','09741',1,'Bad Brückenau');
INSERT INTO `area_codes` VALUES ('49','09742',1,'Kalbach Rhön');
INSERT INTO `area_codes` VALUES ('49','09744',1,'Zeitlofs-Detter');
INSERT INTO `area_codes` VALUES ('49','09745',1,'Wildflecken');
INSERT INTO `area_codes` VALUES ('49','09746',1,'Zeitlofs');
INSERT INTO `area_codes` VALUES ('49','09747',1,'Geroda Bay');
INSERT INTO `area_codes` VALUES ('49','09748',1,'Motten');
INSERT INTO `area_codes` VALUES ('49','09749',1,'Oberbach Unterfr');
INSERT INTO `area_codes` VALUES ('49','09761',1,'Bad Königshofen i Grabfeld');
INSERT INTO `area_codes` VALUES ('49','09762',1,'Saal a d Saale');
INSERT INTO `area_codes` VALUES ('49','09763',1,'Sulzdorf a d Lederhecke');
INSERT INTO `area_codes` VALUES ('49','09764',1,'Höchheim');
INSERT INTO `area_codes` VALUES ('49','09765',1,'Trappstadt');
INSERT INTO `area_codes` VALUES ('49','09766',1,'Grosswenkheim');
INSERT INTO `area_codes` VALUES ('49','09771',1,'Bad Neustadt a d Saale');
INSERT INTO `area_codes` VALUES ('49','09772',1,'Bischofsheim a d Rhön');
INSERT INTO `area_codes` VALUES ('49','09773',1,'Unsleben');
INSERT INTO `area_codes` VALUES ('49','09774',1,'Oberelsbach');
INSERT INTO `area_codes` VALUES ('49','09775',1,'Schönau a d Brend');
INSERT INTO `area_codes` VALUES ('49','09776',1,'Mellrichstadt');
INSERT INTO `area_codes` VALUES ('49','09777',1,'Ostheim v d Rhön');
INSERT INTO `area_codes` VALUES ('49','09778',1,'Fladungen');
INSERT INTO `area_codes` VALUES ('49','09779',1,'Nordheim v d Rhön');
INSERT INTO `area_codes` VALUES ('49','09802',1,'Ansbach-Katterbach');
INSERT INTO `area_codes` VALUES ('49','09803',1,'Colmberg');
INSERT INTO `area_codes` VALUES ('49','09804',1,'Aurach');
INSERT INTO `area_codes` VALUES ('49','09805',1,'Burgoberbach');
INSERT INTO `area_codes` VALUES ('49','0981',1,'Ansbach');
INSERT INTO `area_codes` VALUES ('49','09820',1,'Lehrberg');
INSERT INTO `area_codes` VALUES ('49','09822',1,'Bechhofen a d Heide');
INSERT INTO `area_codes` VALUES ('49','09823',1,'Leutershausen');
INSERT INTO `area_codes` VALUES ('49','09824',1,'Dietenhofen');
INSERT INTO `area_codes` VALUES ('49','09825',1,'Herrieden');
INSERT INTO `area_codes` VALUES ('49','09826',1,'Weidenbach Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09827',1,'Lichtenau Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09828',1,'Rügland');
INSERT INTO `area_codes` VALUES ('49','09829',1,'Flachslanden');
INSERT INTO `area_codes` VALUES ('49','09831',1,'Gunzenhausen');
INSERT INTO `area_codes` VALUES ('49','09832',1,'Wassertrüdingen');
INSERT INTO `area_codes` VALUES ('49','09833',1,'Heidenheim Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09834',1,'Theilenhofen');
INSERT INTO `area_codes` VALUES ('49','09835',1,'Ehingen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09836',1,'Gunzenhausen-Cronheim');
INSERT INTO `area_codes` VALUES ('49','09837',1,'Haundorf');
INSERT INTO `area_codes` VALUES ('49','09841',1,'Bad Windsheim');
INSERT INTO `area_codes` VALUES ('49','09842',1,'Uffenheim');
INSERT INTO `area_codes` VALUES ('49','09843',1,'Burgbernheim');
INSERT INTO `area_codes` VALUES ('49','09844',1,'Obernzenn');
INSERT INTO `area_codes` VALUES ('49','09845',1,'Oberdachstetten');
INSERT INTO `area_codes` VALUES ('49','09846',1,'Ipsheim');
INSERT INTO `area_codes` VALUES ('49','09847',1,'Ergersheim');
INSERT INTO `area_codes` VALUES ('49','09848',1,'Simmershofen');
INSERT INTO `area_codes` VALUES ('49','09851',1,'Dinkelsbühl');
INSERT INTO `area_codes` VALUES ('49','09852',1,'Feuchtwangen');
INSERT INTO `area_codes` VALUES ('49','09853',1,'Wilburgstetten');
INSERT INTO `area_codes` VALUES ('49','09854',1,'Wittelshofen');
INSERT INTO `area_codes` VALUES ('49','09855',1,'Dentlein a Forst');
INSERT INTO `area_codes` VALUES ('49','09856',1,'Dürrwangen');
INSERT INTO `area_codes` VALUES ('49','09857',1,'Schopfloch Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09861',1,'Rothenburg ob der Tauber');
INSERT INTO `area_codes` VALUES ('49','09865',1,'Adelshofen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09867',1,'Geslau');
INSERT INTO `area_codes` VALUES ('49','09868',1,'Schillingsfürst');
INSERT INTO `area_codes` VALUES ('49','09869',1,'Wettringen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09871',1,'Windsbach');
INSERT INTO `area_codes` VALUES ('49','09872',1,'Heilsbronn');
INSERT INTO `area_codes` VALUES ('49','09873',1,'Abenberg-Wassermungenau');
INSERT INTO `area_codes` VALUES ('49','09874',1,'Neuendettelsau');
INSERT INTO `area_codes` VALUES ('49','09875',1,'Wolframs-Eschenbach');
INSERT INTO `area_codes` VALUES ('49','09876',1,'Rohr Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09901',1,'Hengersberg Bay');
INSERT INTO `area_codes` VALUES ('49','09903',1,'Schöllnach');
INSERT INTO `area_codes` VALUES ('49','09904',1,'Lalling');
INSERT INTO `area_codes` VALUES ('49','09905',1,'Bernried Niederbay');
INSERT INTO `area_codes` VALUES ('49','09906',1,'Mariaposching');
INSERT INTO `area_codes` VALUES ('49','09907',1,'Zenting');
INSERT INTO `area_codes` VALUES ('49','09908',1,'Schöfweg');
INSERT INTO `area_codes` VALUES ('49','0991',1,'Deggendorf');
INSERT INTO `area_codes` VALUES ('49','09920',1,'Bischofsmais');
INSERT INTO `area_codes` VALUES ('49','09921',1,'Regen');
INSERT INTO `area_codes` VALUES ('49','09922',1,'Zwiesel');
INSERT INTO `area_codes` VALUES ('49','09923',1,'Teisnach');
INSERT INTO `area_codes` VALUES ('49','09924',1,'Bodenmais');
INSERT INTO `area_codes` VALUES ('49','09925',1,'Bayerisch Eisenstein');
INSERT INTO `area_codes` VALUES ('49','09926',1,'Frauenau');
INSERT INTO `area_codes` VALUES ('49','09927',1,'Kirchberg Wald');
INSERT INTO `area_codes` VALUES ('49','09928',1,'Kirchdorf i Wald');
INSERT INTO `area_codes` VALUES ('49','09929',1,'Ruhmannsfelden');
INSERT INTO `area_codes` VALUES ('49','09931',1,'Plattling');
INSERT INTO `area_codes` VALUES ('49','09932',1,'Osterhofen');
INSERT INTO `area_codes` VALUES ('49','09933',1,'Wallersdorf');
INSERT INTO `area_codes` VALUES ('49','09935',1,'Stephansposching');
INSERT INTO `area_codes` VALUES ('49','09936',1,'Wallerfing');
INSERT INTO `area_codes` VALUES ('49','09937',1,'Oberpöring');
INSERT INTO `area_codes` VALUES ('49','09938',1,'Moos Niederbay');
INSERT INTO `area_codes` VALUES ('49','09941',1,'Kötzting');
INSERT INTO `area_codes` VALUES ('49','09942',1,'Viechtach');
INSERT INTO `area_codes` VALUES ('49','09943',1,'Lam Oberpf');
INSERT INTO `area_codes` VALUES ('49','09944',1,'Miltach');
INSERT INTO `area_codes` VALUES ('49','09945',1,'Arnbruck');
INSERT INTO `area_codes` VALUES ('49','09946',1,'Hohenwarth b Kötzing');
INSERT INTO `area_codes` VALUES ('49','09947',1,'Neukirchen b Hl Blut');
INSERT INTO `area_codes` VALUES ('49','09948',1,'Eschlkam');
INSERT INTO `area_codes` VALUES ('49','09951',1,'Landau a d Isar');
INSERT INTO `area_codes` VALUES ('49','09952',1,'Eichendorf');
INSERT INTO `area_codes` VALUES ('49','09953',1,'Pilsting');
INSERT INTO `area_codes` VALUES ('49','09954',1,'SimbachNiederbay');
INSERT INTO `area_codes` VALUES ('49','09955',1,'Mamming');
INSERT INTO `area_codes` VALUES ('49','09956',1,'Eichendorf-Aufhausen');
INSERT INTO `area_codes` VALUES ('49','09961',1,'Mitterfels');
INSERT INTO `area_codes` VALUES ('49','09962',1,'Schwarzach Niederbay');
INSERT INTO `area_codes` VALUES ('49','09963',1,'Konzell');
INSERT INTO `area_codes` VALUES ('49','09964',1,'Stallwang');
INSERT INTO `area_codes` VALUES ('49','09965',1,'Sankt Englmar');
INSERT INTO `area_codes` VALUES ('49','09966',1,'Wiesenfelden');
INSERT INTO `area_codes` VALUES ('49','09971',1,'Cham');
INSERT INTO `area_codes` VALUES ('49','09972',1,'Waldmünchen');
INSERT INTO `area_codes` VALUES ('49','09973',1,'Furth i Wald');
INSERT INTO `area_codes` VALUES ('49','09974',1,'Traitsching');
INSERT INTO `area_codes` VALUES ('49','09975',1,'Waldmünchen-Geigant');
INSERT INTO `area_codes` VALUES ('49','09976',1,'Rötz');
INSERT INTO `area_codes` VALUES ('49','09977',1,'Arnschwang');
INSERT INTO `area_codes` VALUES ('49','09978',1,'Schönthal Oberpf');
INSERT INTO `area_codes` VALUES ('49','11',0,'diverse netzinterne Nutzung');
INSERT INTO `area_codes` VALUES ('49','115',0,'Behördenruf');
INSERT INTO `area_codes` VALUES ('49','116',0,'Harmonisierte Dienste von sozialem Wert');
INSERT INTO `area_codes` VALUES ('49','118',0,'Auskunftsdienste');
/*!40000 ALTER TABLE `area_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ast_cdr`
--

DROP TABLE IF EXISTS `ast_cdr`;
CREATE TABLE `ast_cdr` (
  `_id` int(10) unsigned NOT NULL auto_increment,
  `calldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `uniqueid` varchar(32) character set ascii collate ascii_bin NOT NULL,
  `clid` varchar(80) collate utf8_unicode_ci NOT NULL default '',
  `src` varchar(30) collate ascii_general_ci NOT NULL default '',
  `dst` varchar(30) collate ascii_general_ci NOT NULL default '',
  `dcontext` varchar(50) collate ascii_general_ci NOT NULL default '',
  `channel` varchar(60) collate ascii_general_ci NOT NULL default '',
  `dstchannel` varchar(60) collate ascii_general_ci NOT NULL default '',
  `lastapp` varchar(30) collate ascii_general_ci NOT NULL default '',
  `lastdata` varchar(80) collate ascii_general_ci NOT NULL default '',
  `duration` mediumint(8) unsigned NOT NULL default '0',
  `billsec` mediumint(8) unsigned NOT NULL default '0',
  `disposition` varchar(15) collate ascii_general_ci NOT NULL default '',
  `amaflags` tinyint(3) unsigned NOT NULL default '0',
  `accountcode` varchar(25) collate ascii_general_ci NOT NULL default '',
  `userfield` varchar(255) collate ascii_general_ci NOT NULL default '',
  PRIMARY KEY  (`_id`),
  KEY `calldate` (`calldate`),
  KEY `accountcode` (`accountcode`),
  KEY `src_disposition` (`src`(25),`disposition`(4)),
  KEY `dst_disposition` (`dst`(25),`disposition`(4)),
  KEY `uniqueid` (`uniqueid`(25))
) ENGINE=MyISAM DEFAULT CHARSET=ascii COLLATE=ascii_general_ci;

--
-- Dumping data for table `ast_cdr`
--

LOCK TABLES `ast_cdr` WRITE;
/*!40000 ALTER TABLE `ast_cdr` DISABLE KEYS */;
/*!40000 ALTER TABLE `ast_cdr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ast_queue_members`
--

DROP TABLE IF EXISTS `ast_queue_members`;
CREATE TABLE `ast_queue_members` (
  `queue_name` varchar(20) character set ascii NOT NULL default '',
  `_queue_id` int(10) unsigned NOT NULL default '0',
  `interface` varchar(25) character set ascii NOT NULL default '',
  `_user_id` int(10) unsigned NOT NULL default '0',
  `penalty` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`queue_name`,`interface`),
  UNIQUE KEY `queue_id_user_id` (`_queue_id`,`_user_id`),
  KEY `_user_id` (`_user_id`),
  KEY `interface` (`interface`(15)),
  CONSTRAINT `ast_queue_members_ibfk_1` FOREIGN KEY (`_queue_id`) REFERENCES `ast_queues` (`_id`),
  CONSTRAINT `ast_queue_members_ibfk_2` FOREIGN KEY (`_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_queue_members`
--

LOCK TABLES `ast_queue_members` WRITE;
/*!40000 ALTER TABLE `ast_queue_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `ast_queue_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ast_queues`
--

DROP TABLE IF EXISTS `ast_queues`;
CREATE TABLE `ast_queues` (
  `_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(10) character set ascii NOT NULL default '',
  `_host_id` mediumint(8) unsigned NOT NULL default '1',
  `_title` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `musicclass` varchar(50) character set ascii default NULL,
  `_sysrec_id` int(10) unsigned NOT NULL default '0',
  `announce` varchar(10) character set ascii default NULL,
  `context` varchar(50) character set ascii default NULL,
  `timeout` smallint(5) unsigned default NULL,
  `autopause` varchar(5) character set ascii default NULL,
  `setinterfacevar` varchar(5) character set ascii default NULL,
  `monitor_join` tinyint(1) unsigned default NULL,
  `monitor_format` varchar(50) character set ascii default NULL,
  `periodic_announce_frequency` tinyint(3) unsigned default NULL,
  `announce_frequency` tinyint(3) unsigned default NULL,
  `announce_round_seconds` tinyint(3) unsigned default NULL,
  `announce_holdtime` varchar(5) character set ascii default NULL,
  `retry` tinyint(3) unsigned default NULL,
  `wrapuptime` tinyint(3) unsigned default NULL,
  `maxlen` tinyint(3) unsigned default NULL,
  `servicelevel` smallint(5) unsigned default NULL,
  `strategy` varchar(20) character set ascii default NULL,
  `joinempty` varchar(10) character set ascii default NULL,
  `leavewhenempty` varchar(10) character set ascii default NULL,
  `eventmemberstatus` tinyint(1) unsigned default NULL,
  `eventwhencalled` tinyint(1) unsigned default NULL,
  `reportholdtime` tinyint(1) unsigned default NULL,
  `ringinuse` varchar(5) character set ascii default NULL,
  `memberdelay` tinyint(3) unsigned default NULL,
  `weight` tinyint(3) unsigned NOT NULL default '0',
  `timeoutrestart` tinyint(1) unsigned default NULL,
  PRIMARY KEY  (`_id`),
  UNIQUE KEY `name` (`name`),
  KEY `host_name` (`_host_id`,`name`),
  CONSTRAINT `ast_queues_ibfk_1` FOREIGN KEY (`_host_id`) REFERENCES `hosts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_queues`
--

LOCK TABLES `ast_queues` WRITE;
/*!40000 ALTER TABLE `ast_queues` DISABLE KEYS */;
INSERT INTO `ast_queues` VALUES (1,'5000',1,'Support-Schlange','default',0,NULL,NULL,10,'no','yes',NULL,NULL,60,90,NULL,'yes',5,NULL,5,NULL,'rrmemory','strict','yes',NULL,NULL,NULL,'no',NULL,0,NULL);
/*!40000 ALTER TABLE `ast_queues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ast_sipfriends`
--

DROP TABLE IF EXISTS `ast_sipfriends`;
CREATE TABLE `ast_sipfriends` (
  `_user_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(16) character set ascii NOT NULL default '',
  `secret` varchar(16) character set ascii NOT NULL default '1234',
  `type` enum('friend','user','peer') character set ascii NOT NULL default 'friend',
  `host` varchar(50) collate latin1_general_ci NOT NULL default 'dynamic',
  `defaultip` varchar(15) character set ascii default NULL,
  `context` varchar(50) character set ascii NOT NULL default 'from-internal-users',
  `callerid` varchar(80) collate latin1_general_ci NOT NULL default '',
  `mailbox` varchar(25) character set ascii NOT NULL default '',
  `callgroup` varchar(20) character set ascii NOT NULL default '1',
  `pickupgroup` varchar(20) character set ascii NOT NULL default '1',
  `setvar` varchar(50) character set ascii NOT NULL default '',
  `call-limit` tinyint(3) unsigned NOT NULL default '20',
  `subscribecontext` varchar(50) character set ascii NOT NULL default 'default',
  `regcontext` varchar(50) character set ascii default NULL,
  `ipaddr` varchar(15) character set ascii default NULL,
  `port` varchar(5) character set ascii default NULL,
  `regseconds` int(10) unsigned NOT NULL default '0',
  `username` varchar(25) character set ascii default NULL,
  `regserver` varchar(50) character set ascii default NULL,
  `fullcontact` varchar(100) character set ascii default NULL,
  PRIMARY KEY  (`_user_id`),
  UNIQUE KEY `name` (`name`),
  KEY `host` (`host`(25)),
  KEY `mailbox` (`mailbox`(10)),
  KEY `context` (`context`(25)),
  CONSTRAINT `ast_sipfriends_ibfk_1` FOREIGN KEY (`_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_sipfriends`
--

LOCK TABLES `ast_sipfriends` WRITE;
/*!40000 ALTER TABLE `ast_sipfriends` DISABLE KEYS */;
INSERT INTO `ast_sipfriends` VALUES (5,'950001','2602729062','friend','dynamic',NULL,'from-internal-nobody','Namenlos-5 <950001>','','1','1','__user_id=5;__user_name=950001',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (6,'950002','7581463327','friend','dynamic',NULL,'from-internal-nobody','Namenlos-6 <950002>','','1','1','__user_id=6;__user_name=950002',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (7,'950003','2099129726','friend','dynamic',NULL,'from-internal-nobody','Namenlos-7 <950003>','','1','1','__user_id=7;__user_name=950003',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (8,'950004','4751258926','friend','dynamic',NULL,'from-internal-nobody','Namenlos-8 <950004>','','1','1','__user_id=8;__user_name=950004',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (9,'950005','7458905728','friend','dynamic',NULL,'from-internal-nobody','Namenlos-9 <950005>','','1','1','__user_id=9;__user_name=950005',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (10,'950006','4040752142','friend','dynamic',NULL,'from-internal-nobody','Namenlos-10 <950006>','','1','1','__user_id=10;__user_name=950006',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (11,'950007','5827043803','friend','dynamic',NULL,'from-internal-nobody','Namenlos-11 <950007>','','1','1','__user_id=11;__user_name=950007',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (12,'950008','7012962864','friend','dynamic',NULL,'from-internal-nobody','Namenlos-12 <950008>','','1','1','__user_id=12;__user_name=950008',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (13,'950009','7583683190','friend','dynamic',NULL,'from-internal-nobody','Namenlos-13 <950009>','','1','1','__user_id=13;__user_name=950009',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (14,'950010','6879527634','friend','dynamic',NULL,'from-internal-nobody','Namenlos-14 <950010>','','1','1','__user_id=14;__user_name=950010',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (22,'2000','5826899294','friend','dynamic',NULL,'from-internal-users','Hans Muster <2000>','2000','1','1','__user_id=22;__user_name=2000',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (23,'2001','4813474487','friend','dynamic',NULL,'from-internal-users','Peter Muster <2001>','2001','1','1','__user_id=23;__user_name=2001',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (24,'2002','6907087521','friend','dynamic',NULL,'from-internal-users','Anna Muster <2002>','2002','1','1','__user_id=24;__user_name=2002',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (25,'2003','9293349941','friend','dynamic',NULL,'from-internal-users','Lisa Muster <2003>','2003','1','1','__user_id=25;__user_name=2003',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (28,'950011','7364863263482634','friend','dynamic',NULL,'from-internal-nobody','Namenlos-28 <950011>','','1','1','__user_id=28;__user_name=950011',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (29,'950012','7364863263482634','friend','dynamic',NULL,'from-internal-nobody','Namenlos-29 <950012>','','1','1','__user_id=29;__user_name=950012',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (30,'950013','3707760381117896','friend','dynamic',NULL,'from-internal-nobody','Namenlos-13 <950013>','','1','1','__user_id=30;__user_name=950013',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
/*!40000 ALTER TABLE `ast_sipfriends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ast_sipfriends_gs`
--

DROP TABLE IF EXISTS `ast_sipfriends_gs`;
/*!50001 DROP VIEW IF EXISTS `ast_sipfriends_gs`*/;
/*!50001 DROP TABLE IF EXISTS `ast_sipfriends_gs`*/;
/*!50001 CREATE TABLE `ast_sipfriends_gs` (
  `_user_id` int(10) unsigned,
  `name` varchar(10),
  `secret` varchar(16),
  `type` enum('friend','user','peer'),
  `host` varchar(50),
  `defaultip` varchar(15),
  `context` varchar(50),
  `callerid` varchar(80),
  `mailbox` varchar(25),
  `callgroup` varchar(20),
  `pickupgroup` varchar(20),
  `setvar` varchar(50),
  `call-limit` tinyint(3) unsigned,
  `subscribecontext` varchar(50),
  `regcontext` varchar(50),
  `ipaddr` varchar(15),
  `port` varchar(5),
  `regseconds` int(10) unsigned,
  `username` varchar(25),
  `regserver` varchar(50),
  `fullcontact` varchar(100)
) */;

--
-- Table structure for table `ast_voicemail`
--

DROP TABLE IF EXISTS `ast_voicemail`;
CREATE TABLE `ast_voicemail` (
  `_uniqueid` int(10) unsigned NOT NULL auto_increment,
  `_user_id` int(10) unsigned default NULL,
  `mailbox` varchar(10) character set ascii NOT NULL default '',
  `context` varchar(50) character set ascii NOT NULL default 'default',
  `password` varchar(10) character set ascii NOT NULL default '0000',
  `email` varchar(50) character set ascii NOT NULL default '',
  `fullname` varchar(50) collate latin1_general_ci NOT NULL default '',
  `tz` varchar(25) character set ascii default 'germany',
  `attach` enum('no','yes') character set ascii NOT NULL default 'no',
  `delete` enum('no','yes') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`_uniqueid`),
  UNIQUE KEY `context_mailbox` (`context`,`mailbox`),
  KEY `fullname` (`fullname`(20)),
  KEY `_user_id` (`_user_id`),
  KEY `mailbox_context` (`mailbox`,`context`(20)),
  CONSTRAINT `ast_voicemail_ibfk_1` FOREIGN KEY (`_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_voicemail`
--

LOCK TABLES `ast_voicemail` WRITE;
/*!40000 ALTER TABLE `ast_voicemail` DISABLE KEYS */;
INSERT INTO `ast_voicemail` VALUES (9,22,'2000','default','123','','Hans Muster','germany','no','no');
INSERT INTO `ast_voicemail` VALUES (10,23,'2001','default','123','','Peter Muster','germany','no','no');
INSERT INTO `ast_voicemail` VALUES (11,24,'2002','default','123','','Anna Muster','germany','no','no');
INSERT INTO `ast_voicemail` VALUES (12,25,'2003','default','123','','Lisa Muster','germany','no','no');
/*!40000 ALTER TABLE `ast_voicemail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `boi_perms`
--

DROP TABLE IF EXISTS `boi_perms`;
CREATE TABLE `boi_perms` (
  `user_id` int(10) unsigned NOT NULL,
  `host_id` mediumint(8) unsigned NOT NULL,
  `roles` varchar(8) character set ascii NOT NULL,
  PRIMARY KEY  (`user_id`,`host_id`),
  UNIQUE KEY `host_user` (`host_id`,`user_id`),
  CONSTRAINT `boi_perms_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `boi_perms_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `boi_perms`
--

LOCK TABLES `boi_perms` WRITE;
/*!40000 ALTER TABLE `boi_perms` DISABLE KEYS */;
/*!40000 ALTER TABLE `boi_perms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `call_completion_waiting`
--

DROP TABLE IF EXISTS `call_completion_waiting`;
CREATE TABLE `call_completion_waiting` (
  `from_ext` varchar(15) character set ascii NOT NULL default '',
  `from_host_id` mediumint(8) unsigned NOT NULL default '0',
  `from_user_id` int(10) unsigned default NULL,
  `to_ext` varchar(15) character set ascii NOT NULL default '',
  `to_host_id` mediumint(8) unsigned NOT NULL default '0',
  `to_user_id` int(10) unsigned default NULL,
  `t_init` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`from_ext`,`to_ext`),
  UNIQUE KEY `to_from` (`to_ext`,`from_ext`),
  KEY `t_init` (`t_init`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `call_completion_waiting`
--

LOCK TABLES `call_completion_waiting` WRITE;
/*!40000 ALTER TABLE `call_completion_waiting` DISABLE KEYS */;
/*!40000 ALTER TABLE `call_completion_waiting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `callblocking`
--

DROP TABLE IF EXISTS `callblocking`;
CREATE TABLE `callblocking` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `regexp` varchar(40) character set ascii NOT NULL default '',
  `pin` varchar(10) character set ascii collate ascii_bin NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_regex` (`user_id`,`regexp`),
  CONSTRAINT `callblocking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `callblocking`
--

LOCK TABLES `callblocking` WRITE;
/*!40000 ALTER TABLE `callblocking` DISABLE KEYS */;
INSERT INTO `callblocking` VALUES (1,23,'11.*99','111');
INSERT INTO `callblocking` VALUES (2,24,'^[0]','222');
INSERT INTO `callblocking` VALUES (3,24,'^0190','');
/*!40000 ALTER TABLE `callblocking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `callforwards`
--

DROP TABLE IF EXISTS `callforwards`;
CREATE TABLE `callforwards` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `source` enum('internal','external') character set ascii NOT NULL default 'internal',
  `case` enum('always','busy','unavail','offline') character set ascii NOT NULL default 'always',
  `timeout` tinyint(3) unsigned NOT NULL default '20',
  `number_std` varchar(50) character set ascii NOT NULL default '',
  `number_var` varchar(50) character set ascii NOT NULL default '',
  `number_vml` varchar(20) character set ascii NOT NULL default '',
  `active` enum('no','std','var','vml') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`user_id`,`source`,`case`),
  CONSTRAINT `callforwards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `callforwards`
--

LOCK TABLES `callforwards` WRITE;
/*!40000 ALTER TABLE `callforwards` DISABLE KEYS */;
/*!40000 ALTER TABLE `callforwards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `callwaiting`
--

DROP TABLE IF EXISTS `callwaiting`;
CREATE TABLE `callwaiting` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  CONSTRAINT `callwaiting_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `callwaiting`
--

LOCK TABLES `callwaiting` WRITE;
/*!40000 ALTER TABLE `callwaiting` DISABLE KEYS */;
INSERT INTO `callwaiting` VALUES (24,0);
/*!40000 ALTER TABLE `callwaiting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clir`
--

DROP TABLE IF EXISTS `clir`;
CREATE TABLE `clir` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `internal_restrict` enum('no','once','yes') character set ascii NOT NULL default 'no',
  `external_restrict` enum('no','once','yes') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`user_id`),
  CONSTRAINT `clir_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `clir`
--

LOCK TABLES `clir` WRITE;
/*!40000 ALTER TABLE `clir` DISABLE KEYS */;
INSERT INTO `clir` VALUES (5,'no','no');
INSERT INTO `clir` VALUES (6,'no','no');
INSERT INTO `clir` VALUES (7,'no','no');
INSERT INTO `clir` VALUES (8,'no','no');
INSERT INTO `clir` VALUES (23,'no','no');
INSERT INTO `clir` VALUES (24,'no','no');
/*!40000 ALTER TABLE `clir` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conferences`
--

DROP TABLE IF EXISTS `conferences`;
CREATE TABLE `conferences` (
  `ext` varchar(10) character set latin1 collate latin1_general_ci NOT NULL default '',
  `host_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ext`),
  KEY `host_ext` (`host_id`,`ext`),
  CONSTRAINT `conferences_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `conferences`
--

LOCK TABLES `conferences` WRITE;
/*!40000 ALTER TABLE `conferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `conferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dial_log`
--

DROP TABLE IF EXISTS `dial_log`;
CREATE TABLE `dial_log` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `type` enum('in','out','missed') character set ascii NOT NULL default 'out',
  `timestamp` int(10) unsigned NOT NULL default '0',
  `number` varchar(50) character set ascii NOT NULL default '',
  `remote_name` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `remote_user_id` int(10) unsigned default NULL,
  `queue_id` int(10) unsigned default NULL,
  KEY `timestamp` (`timestamp`),
  KEY `user_timestamp` (`user_id`,`timestamp`),
  KEY `user_type_number_timestamp` (`user_id`,`type`,`number`(10),`timestamp`),
  KEY `user_type_timestamp` (`user_id`,`type`,`timestamp`),
  KEY `remote_user_id` (`remote_user_id`),
  KEY `queue_id` (`queue_id`),
  CONSTRAINT `dial_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `dial_log_ibfk_2` FOREIGN KEY (`remote_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `dial_log_ibfk_3` FOREIGN KEY (`queue_id`) REFERENCES `ast_queues` (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dial_log`
--

LOCK TABLES `dial_log` WRITE;
/*!40000 ALTER TABLE `dial_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `dial_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gate_grps`
--

DROP TABLE IF EXISTS `gate_grps`;
CREATE TABLE `gate_grps` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(20) character set ascii NOT NULL,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL,
  `type` varchar(20) character set ascii NOT NULL default 'balance',
  `allow_in` tinyint(1) unsigned NOT NULL default '1',
  `in_dest_search` varchar(50) character set ascii collate ascii_bin NOT NULL,
  `in_dest_replace` varchar(25) character set ascii collate ascii_bin NOT NULL,
  `in_cid_search` varchar(50) character set ascii collate ascii_bin NOT NULL,
  `in_cid_replace` varchar(25) character set ascii collate ascii_bin NOT NULL,
  `out_cid_search` varchar(50) character set ascii collate ascii_bin NOT NULL,
  `out_cid_replace` varchar(25) character set ascii collate ascii_bin NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `title` (`title`(8))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `gate_grps`
--

LOCK TABLES `gate_grps` WRITE;
/*!40000 ALTER TABLE `gate_grps` DISABLE KEYS */;
INSERT INTO `gate_grps` VALUES (5,'campus','SIP-ISDN-GWs intern','balance',1,'','','','','','');
INSERT INTO `gate_grps` VALUES (6,'pstn','ISDN (PRI)','balance',1,'^(?:(?:0049|0)2631)?123(.*)','$1','','','^(.*)','0251702$1');
INSERT INTO `gate_grps` VALUES (7,'gsm-t-mobile','GSM-GW T-Mobile','balance',0,'','','','','','');
INSERT INTO `gate_grps` VALUES (8,'gsm-vodafone','GSM-GW Vodafone','balance',0,'','','','','','');
INSERT INTO `gate_grps` VALUES (9,'sipgate','SIP-GW (sipgate.de)','balance',0,'','','','','','');
INSERT INTO `gate_grps` VALUES (10,'dusnet','SIP-GW (dus.net)','balance',0,'','','','','','');
INSERT INTO `gate_grps` VALUES (12,'isdn-bri','ISDN (BRI)','balance',1,'^(?:(?:0049|0)2631)?1234','','','','','');
/*!40000 ALTER TABLE `gate_grps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gates`
--

DROP TABLE IF EXISTS `gates`;
CREATE TABLE `gates` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `grp_id` smallint(5) unsigned default NULL,
  `type` varchar(10) character set ascii NOT NULL default 'sip',
  `name` varchar(25) character set ascii NOT NULL,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL,
  `allow_out` tinyint(1) unsigned NOT NULL default '1',
  `dialstr` varchar(50) character set ascii NOT NULL,
  `host` varchar(50) collate utf8_unicode_ci default NULL,
  `user` varchar(35) collate utf8_unicode_ci default NULL,
  `pwd` varchar(35) collate utf8_unicode_ci default NULL,
  `hw_port` tinyint(3) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `grp_title` (`grp_id`,`title`(10)),
  KEY `grp_allow_out` (`grp_id`,`allow_out`),
  KEY `type` (`type`),
  CONSTRAINT `gates_ibfk_1` FOREIGN KEY (`grp_id`) REFERENCES `gate_grps` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `gates`
--

LOCK TABLES `gates` WRITE;
/*!40000 ALTER TABLE `gates` DISABLE KEYS */;
INSERT INTO `gates` VALUES (5,6,'zap','gw_5_prispan1','PRI Span 1',1,'Zap/r1/{number:1}',NULL,NULL,NULL,NULL);
INSERT INTO `gates` VALUES (6,6,'zap','gw_6_prispan2','PRI Span 2',1,'Zap/r2/{number:1}',NULL,NULL,NULL,NULL);
INSERT INTO `gates` VALUES (7,5,'sip','gw_7_sipisdninterna','SIP-ISDN intern A',1,'SIP/{number:1}@{gateway}','sip.example.com','','',NULL);
INSERT INTO `gates` VALUES (8,5,'sip','gw_8_sipisdninternb','SIP-ISDN intern B',1,'SIP/{number:1}@{gateway}','sip.example.com','','',NULL);
INSERT INTO `gates` VALUES (9,8,'sip','gw_9_sipgsmvodafone','SIP-GSM Vodafone',1,'SIP/{number:1}@{gateway}','sip.example.com','','',NULL);
INSERT INTO `gates` VALUES (16,12,'misdn','gw_16_briport1','BRI Port 1',1,'mISDN/g:{gateway}/{number:1}',NULL,NULL,NULL,1);
INSERT INTO `gates` VALUES (17,12,'misdn','gw_17_briport2','BRI Port 2',1,'mISDN/g:{gateway}/{number:1}',NULL,NULL,NULL,2);
INSERT INTO `gates` VALUES (18,12,'misdn','gw_18_briport3','BRI Port 3',1,'mISDN/g:{gateway}/{number:1}',NULL,NULL,NULL,3);
INSERT INTO `gates` VALUES (19,12,'misdn','gw_19_briport4','BRI Port 4',1,'mISDN/g:{gateway}/{number:1}',NULL,NULL,NULL,4);
/*!40000 ALTER TABLE `gates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `host_params`
--

DROP TABLE IF EXISTS `host_params`;
CREATE TABLE `host_params` (
  `host_id` mediumint(8) unsigned NOT NULL,
  `param` varchar(25) character set ascii NOT NULL,
  `value` varchar(100) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`host_id`,`param`),
  KEY `param_value` (`param`,`value`(20)),
  CONSTRAINT `host_params_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `host_params`
--

LOCK TABLES `host_params` WRITE;
/*!40000 ALTER TABLE `host_params` DISABLE KEYS */;
/*!40000 ALTER TABLE `host_params` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hosts`
--

DROP TABLE IF EXISTS `hosts`;
CREATE TABLE `hosts` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `host` varchar(15) character set ascii NOT NULL default '',
  `comment` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `is_foreign` tinyint(1) unsigned NOT NULL default '0',
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `host` (`host`),
  KEY `group_id` (`group_id`),
  KEY `is_foreign_id` (`is_foreign`,`id`),
  KEY `is_foreign_host` (`is_foreign`,`host`),
  KEY `foreign_comment` (`is_foreign`,`comment`(20)),
  KEY `comment` (`comment`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `hosts`
--

LOCK TABLES `hosts` WRITE;
/*!40000 ALTER TABLE `hosts` DISABLE KEYS */;
INSERT INTO `hosts` VALUES (1,'192.168.1.130','Gemeinschaft 1',0,0);
/*!40000 ALTER TABLE `hosts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `huntgroups`
--

CREATE TABLE IF NOT EXISTS `huntgroups` (
  `number` int(10) unsigned NOT NULL default '0',
  `strategy` enum('linear','parallel') collate utf8_unicode_ci NOT NULL,
  `sequence_no` int(10) unsigned NOT NULL default '1',
  `user_id` int(10) unsigned NOT NULL default '0',
  `timeout` int(10) unsigned NOT NULL default '0',
  CONSTRAINT `huntgroups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `huntgroups_callforwards`
--

CREATE TABLE IF NOT EXISTS `huntgroups_callforwards` (
  `huntgroup` int(10) unsigned NOT NULL default '0',
  `source` enum('internal','external') character set ascii NOT NULL default 'internal',
  `case` enum('always','full','timeout','empty') character set ascii NOT NULL default 'always',
  `timeout` tinyint(3) unsigned NOT NULL default '20',
  `number_std` varchar(50) character set ascii NOT NULL default '',
  `number_var` varchar(50) character set ascii NOT NULL default '',
  `active` enum('no','std','var') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`huntgroup`,`source`,`case`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `instant_messaging`
--

DROP TABLE IF EXISTS `instant_messaging`;
CREATE TABLE `instant_messaging` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `type` varchar(25) character set ascii NOT NULL default '',
  `contact` varchar(80) character set ascii NOT NULL default '',
  PRIMARY KEY  (`user_id`,`type`),
  KEY `type` (`type`),
  CONSTRAINT `instant_messaging_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `instant_messaging`
--

LOCK TABLES `instant_messaging` WRITE;
/*!40000 ALTER TABLE `instant_messaging` DISABLE KEYS */;
INSERT INTO `instant_messaging` VALUES (22,'jabber','test@example.com');
/*!40000 ALTER TABLE `instant_messaging` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `itemized_bill`
--

DROP TABLE IF EXISTS `itemized_bill`;
CREATE TABLE `itemized_bill` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `start` datetime NOT NULL,
  `dur` mediumint(8) unsigned NOT NULL default '0',
  `is_out` tinyint(1) unsigned NOT NULL,
  `ext` varchar(10) character set ascii NOT NULL,
  `remote` varchar(25) character set ascii NOT NULL,
  `tariff_zone` char(4) character set ascii NOT NULL,
  `units` mediumint(8) unsigned NOT NULL,
  `charge` float NOT NULL default '0',
  `cur` char(3) character set ascii NOT NULL,
  `vat` float NOT NULL,
  `cdr_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `start_ext_remote_dur` (`start`,`ext`,`remote`,`dur`),
  KEY `ext` (`ext`),
  KEY `cdr_id` (`cdr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `itemized_bill`
--

LOCK TABLES `itemized_bill` WRITE;
/*!40000 ALTER TABLE `itemized_bill` DISABLE KEYS */;
/*!40000 ALTER TABLE `itemized_bill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pb_ldap`
--

DROP TABLE IF EXISTS `pb_ldap`;
CREATE TABLE `pb_ldap` (
  `user` varchar(20) character set ascii NOT NULL default '',
  `lastname` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `firstname` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `number` varchar(25) character set ascii NOT NULL default '',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `user_number` (`user`,`number`),
  KEY `updated` (`updated`),
  KEY `lastname_firstname` (`lastname`(15),`firstname`(15),`number`(7)),
  KEY `firstname_lastname` (`firstname`(15),`lastname`(10),`number`(7)),
  KEY `number` (`number`,`lastname`(15),`firstname`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pb_ldap`
--

LOCK TABLES `pb_ldap` WRITE;
/*!40000 ALTER TABLE `pb_ldap` DISABLE KEYS */;
INSERT INTO `pb_ldap` VALUES ('012345','TEST','HANS','123','2007-05-24 07:28:28');
/*!40000 ALTER TABLE `pb_ldap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pb_prv`
--

DROP TABLE IF EXISTS `pb_prv`;
CREATE TABLE `pb_prv` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `firstname` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `lastname` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `number` varchar(25) character set ascii NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `uid_lastname_firstname` (`user_id`,`lastname`(15),`firstname`(10)),
  KEY `uid_firstname_lastname` (`user_id`,`firstname`(10),`lastname`(10)),
  KEY `uid_number` (`user_id`,`number`(10)),
  CONSTRAINT `pb_prv_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pb_prv`
--

LOCK TABLES `pb_prv` WRITE;
/*!40000 ALTER TABLE `pb_prv` DISABLE KEYS */;
/*!40000 ALTER TABLE `pb_prv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phone_msgs`
--

DROP TABLE IF EXISTS `phone_msgs`;
CREATE TABLE `phone_msgs` (
  `user_id` int(10) unsigned NOT NULL,
  `text` varchar(250) collate utf8_unicode_ci NOT NULL,
  `modified` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`user_id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `phone_msgs`
--

LOCK TABLES `phone_msgs` WRITE;
/*!40000 ALTER TABLE `phone_msgs` DISABLE KEYS */;
/*!40000 ALTER TABLE `phone_msgs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phones`
--

DROP TABLE IF EXISTS `phones`;
CREATE TABLE `phones` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(30) character set ascii NOT NULL default '',
  `mac_addr` varchar(12) character set ascii NOT NULL default '',
  `user_id` int(10) unsigned default NULL,
  `nobody_index` mediumint(8) unsigned NOT NULL default '0',
  `added` int(10) unsigned NOT NULL default '0',
  `firmware_cur` varchar(25) collate ascii_general_ci NOT NULL default '',
  `fw_manual_update` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `mac_addr` (`mac_addr`),
  KEY `user_id` (`user_id`),
  KEY `added` (`added`),
  KEY `type` (`type`),
  KEY `nobody_index` (`nobody_index`),
  CONSTRAINT `phones_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `phones`
--

LOCK TABLES `phones` WRITE;
/*!40000 ALTER TABLE `phones` DISABLE KEYS */;
INSERT INTO `phones` VALUES (1,'snom-360','000413233C9F',NULL,1,1174110000,'',0);
INSERT INTO `phones` VALUES (2,'snom-360','000413231C76',NULL,2,1174110000,'',0);
INSERT INTO `phones` VALUES (3,'snom-360','000413233483',NULL,3,1174110000,'',0);
INSERT INTO `phones` VALUES (8,'snom-360','0004132308A4',NULL,4,1174119746,'',0);
INSERT INTO `phones` VALUES (9,'snom-360','000413000000',NULL,5,1177010534,'',0);
/*!40000 ALTER TABLE `phones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pickupgroups`
--

DROP TABLE IF EXISTS `pickupgroups`;
CREATE TABLE `pickupgroups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pickupgroups`
--

LOCK TABLES `pickupgroups` WRITE;
/*!40000 ALTER TABLE `pickupgroups` DISABLE KEYS */;
INSERT INTO `pickupgroups` VALUES (1,'Buchhaltung');
/*!40000 ALTER TABLE `pickupgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pickupgroups_users`
--

DROP TABLE IF EXISTS `pickupgroups_users`;
CREATE TABLE `pickupgroups_users` (
  `group_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pickupgroups_users_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `pickupgroups` (`id`),
  CONSTRAINT `pickupgroups_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pickupgroups_users`
--

LOCK TABLES `pickupgroups_users` WRITE;
/*!40000 ALTER TABLE `pickupgroups_users` DISABLE KEYS */;
INSERT INTO `pickupgroups_users` VALUES (1,24);
INSERT INTO `pickupgroups_users` VALUES (1,23);
INSERT INTO `pickupgroups_users` VALUES (1,22);
/*!40000 ALTER TABLE `pickupgroups_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prov_jobs`
--

DROP TABLE IF EXISTS `prov_jobs`;
CREATE TABLE `prov_jobs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `inserted` int(10) unsigned NOT NULL default '0',
  `running` tinyint(1) unsigned NOT NULL default '0',
  `trigger` enum('client','server') character set ascii NOT NULL default 'client',
  `phone_id` int(10) unsigned NOT NULL,
  `type` varchar(10) character set ascii NOT NULL default 'settings',
  `immediate` tinyint(1) unsigned NOT NULL default '0',
  `minute` varchar(20) character set ascii collate ascii_bin NOT NULL default '*',
  `hour` varchar(20) character set ascii collate ascii_bin NOT NULL default '*',
  `day` varchar(20) character set ascii collate ascii_bin NOT NULL default '*',
  `month` varchar(20) character set ascii collate ascii_bin NOT NULL default '*',
  `dow` varchar(20) character set ascii collate ascii_bin NOT NULL default '*',
  `data` varchar(100) character set ascii collate ascii_bin NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `phone_id` (`phone_id`),
  KEY `immediate` (`immediate`),
  KEY `inserted` (`inserted`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `prov_jobs`
--

LOCK TABLES `prov_jobs` WRITE;
/*!40000 ALTER TABLE `prov_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `prov_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prov_param_profiles`
--

DROP TABLE IF EXISTS `prov_param_profiles`;
CREATE TABLE `prov_param_profiles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `is_group_profile` tinyint(1) unsigned NOT NULL default '1',
  `title` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `is_group_profile_title` (`is_group_profile`,`title`(45)),
  KEY `title` (`title`(45))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `prov_param_profiles`
--

LOCK TABLES `prov_param_profiles` WRITE;
/*!40000 ALTER TABLE `prov_param_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `prov_param_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prov_params`
--

DROP TABLE IF EXISTS `prov_params`;
CREATE TABLE `prov_params` (
  `profile_id` int(10) unsigned NOT NULL,
  `phone_type` varchar(20) character set ascii NOT NULL,
  `param` varchar(50) character set ascii NOT NULL,
  `index` smallint(5) NOT NULL default '-1',
  `value` varchar(100) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`profile_id`,`phone_type`,`param`,`index`),
  KEY `phone_type_param_index` (`phone_type`,`param`,`index`),
  CONSTRAINT `prov_params_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `prov_param_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `prov_params`
--

LOCK TABLES `prov_params` WRITE;
/*!40000 ALTER TABLE `prov_params` DISABLE KEYS */;
/*!40000 ALTER TABLE `prov_params` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue_callforwards`
--

DROP TABLE IF EXISTS `queue_callforwards`;
CREATE TABLE `queue_callforwards` (
  `queue_id` int(10) unsigned NOT NULL default '0',
  `source` enum('internal','external') character set ascii NOT NULL default 'internal',
  `case` enum('always','full','timeout','empty') character set ascii NOT NULL default 'always',
  `timeout` tinyint(3) unsigned NOT NULL default '20',
  `number_std` varchar(50) character set ascii NOT NULL default '',
  `number_var` varchar(50) character set ascii NOT NULL default '',
  `active` enum('no','std','var') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`queue_id`,`source`,`case`),
  CONSTRAINT `queue_callforwards_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `ast_queues` (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `queue_callforwards`
--

LOCK TABLES `queue_callforwards` WRITE;
/*!40000 ALTER TABLE `queue_callforwards` DISABLE KEYS */;
INSERT INTO `queue_callforwards` VALUES (1,'external','always',20,'2001','','std');
INSERT INTO `queue_callforwards` VALUES (1,'external','full',0,'','123','var');
/*!40000 ALTER TABLE `queue_callforwards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue_log`
--

DROP TABLE IF EXISTS `queue_log`;
CREATE TABLE `queue_log` (
  `queue_id` int(10) unsigned default NULL,
  `timestamp` int(10) unsigned NOT NULL default '0',
  `event` varchar(15) character set ascii NOT NULL default '',
  `reason` varchar(10) character set ascii default NULL,
  `ast_call_id` varchar(32) character set ascii collate ascii_bin default NULL,
  `user_id` int(10) unsigned default NULL,
  `caller` varchar(50) collate utf8_unicode_ci default NULL,
  `pos` mediumint(8) unsigned default NULL,
  `origpos` mediumint(8) unsigned default NULL,
  `waittime` mediumint(8) unsigned default NULL,
  `logindur` int(10) unsigned default NULL,
  `calldur` mediumint(8) unsigned default NULL,
  `info` varchar(50) character set ascii default NULL,
  KEY `queue_timestamp` (`queue_id`,`timestamp`),
  KEY `queue_event_timestamp` (`queue_id`,`event`,`timestamp`),
  KEY `queue_event_reason_timestamp` (`queue_id`,`event`,`reason`,`timestamp`),
  KEY `timestamp` (`timestamp`),
  KEY `ast_call_id` (`ast_call_id`(25)),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `queue_log`
--

LOCK TABLES `queue_log` WRITE;
/*!40000 ALTER TABLE `queue_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ringtones`
--

DROP TABLE IF EXISTS `ringtones`;
CREATE TABLE `ringtones` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `src` enum('internal','external') collate latin1_general_ci NOT NULL default 'internal',
  `bellcore` tinyint(3) unsigned NOT NULL default '1',
  `file` varchar(40) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`user_id`,`src`),
  CONSTRAINT `ringtones_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ringtones`
--

LOCK TABLES `ringtones` WRITE;
/*!40000 ALTER TABLE `ringtones` DISABLE KEYS */;
INSERT INTO `ringtones` VALUES (23,'internal',1,'somefile');
INSERT INTO `ringtones` VALUES (23,'external',2,NULL);
/*!40000 ALTER TABLE `ringtones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `routes`
--

DROP TABLE IF EXISTS `routes`;
CREATE TABLE `routes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `ord` int(10) unsigned NOT NULL,
  `pattern` varchar(30) character set ascii NOT NULL,
  `d_mo` tinyint(1) unsigned NOT NULL default '1',
  `d_tu` tinyint(1) unsigned NOT NULL default '1',
  `d_we` tinyint(1) unsigned NOT NULL default '1',
  `d_th` tinyint(1) unsigned NOT NULL default '1',
  `d_fr` tinyint(1) unsigned NOT NULL default '1',
  `d_sa` tinyint(1) unsigned NOT NULL default '1',
  `d_su` tinyint(1) unsigned NOT NULL default '1',
  `h_from` time NOT NULL default '00:00:00',
  `h_to` time NOT NULL default '24:00:00',
  `user_grp_id` mediumint(8) unsigned default NULL,
  `gw_grp_id_1` smallint(5) unsigned NOT NULL default '0',
  `gw_grp_id_2` smallint(5) unsigned NOT NULL default '0',
  `gw_grp_id_3` smallint(5) unsigned NOT NULL default '0',
  `lcrprfx` varchar(6) character set ascii NOT NULL,
  `descr` varchar(150) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ord` (`ord`),
  KEY `active_mo` (`active`,`d_mo`,`ord`),
  KEY `active_tu` (`active`,`d_tu`,`ord`),
  KEY `active_we` (`active`,`d_we`,`ord`),
  KEY `active_th` (`active`,`d_th`,`ord`),
  KEY `active_fr` (`active`,`d_fr`,`ord`),
  KEY `active_sa` (`active`,`d_sa`,`ord`),
  KEY `active_su` (`active`,`d_su`,`ord`),
  KEY `user_grp_id` (`user_grp_id`),
  CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`user_grp_id`) REFERENCES `user_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `routes`
--

LOCK TABLES `routes` WRITE;
/*!40000 ALTER TABLE `routes` DISABLE KEYS */;
INSERT INTO `routes` VALUES (5,0,3,'^011[0-7]$',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,7,9,'','Notrufnummern etc.');
INSERT INTO `routes` VALUES (6,0,4,'^019222$',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,7,9,'','Notruf Rettungsdienst');
INSERT INTO `routes` VALUES (7,0,14,'^00900',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,0,0,'','Mehrwertnummern');
INSERT INTO `routes` VALUES (8,0,8,'^0118',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,0,0,'','Auskünfte (u.U. teuer, können vermitteln)');
INSERT INTO `routes` VALUES (9,0,10,'^009009',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,0,0,0,'','Mehrwertnummern (Dialer)');
INSERT INTO `routes` VALUES (10,0,12,'^009005',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,0,0,0,'','Mehrwertnummern (Erwachsenenunterhaltung)');
INSERT INTO `routes` VALUES (11,0,16,'^00902',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,0,0,0,'','Televoting (14 ct/Anruf)');
INSERT INTO `routes` VALUES (12,0,18,'^0019[1-4]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,0,0,0,'','Internet-Zugänge');
INSERT INTO `routes` VALUES (13,0,20,'^0070[01]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,0,0,'','private Vanity-Nummern');
INSERT INTO `routes` VALUES (14,0,22,'^0080[01]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,0,0,'','Mehrwertnummern (kostenlos)');
INSERT INTO `routes` VALUES (15,0,24,'^001805',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,0,0,0,'','Mehrwertnummern (Hotlines/Erwachsenenunterhaltung)');
INSERT INTO `routes` VALUES (16,0,26,'^001802001033',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,0,0,0,'','Handvermittlung ins Ausland (teuer)');
INSERT INTO `routes` VALUES (17,0,28,'^00180',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,0,0,'','Mehrwertnummern');
INSERT INTO `routes` VALUES (18,0,30,'^00137',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,0,0,0,'','Televoting (25-100 ct/Anruf)');
INSERT INTO `routes` VALUES (19,0,32,'^0012[0-9]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,0,0,0,'','Innovative Dienste (teuer)');
INSERT INTO `routes` VALUES (20,0,34,'^0032',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,0,0,'','ortsunabhängig, unklare Tarifierung, GSM vermeiden');
INSERT INTO `routes` VALUES (21,0,36,'^00151',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,7,8,6,'','T-Mobile D1');
INSERT INTO `routes` VALUES (22,0,38,'^0016[01489]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,7,8,6,'','T-Mobile D1');
INSERT INTO `routes` VALUES (23,0,40,'^0017[015]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,7,8,6,'','T-Mobile D1');
INSERT INTO `routes` VALUES (24,0,42,'^00152',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,8,7,6,'','Vodafone D2');
INSERT INTO `routes` VALUES (25,0,44,'^00162',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,8,7,6,'','Vodafone D2');
INSERT INTO `routes` VALUES (26,0,46,'^0017[234]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,8,7,6,'','Vodafone D2');
INSERT INTO `routes` VALUES (27,0,48,'^0015[57]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,8,7,6,'','E-Plus');
INSERT INTO `routes` VALUES (28,0,50,'^00163',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,8,7,6,'','E-Plus');
INSERT INTO `routes` VALUES (29,0,52,'^0017[78]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,8,7,6,'','E-Plus');
INSERT INTO `routes` VALUES (30,0,54,'^00156',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,7,8,6,'','MobilCom');
INSERT INTO `routes` VALUES (31,0,56,'^00159',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,8,7,6,'','O2');
INSERT INTO `routes` VALUES (32,0,58,'^0017[69]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,8,7,6,'','O2');
INSERT INTO `routes` VALUES (33,0,60,'^00150',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,7,8,6,'','Group3G');
INSERT INTO `routes` VALUES (34,0,62,'^001[5-7]',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,8,7,6,'','andere Handy-Gespräche');
INSERT INTO `routes` VALUES (35,0,64,'^00[1-9][0-9]{2}',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,10,0,'','Ortsnetze');
INSERT INTO `routes` VALUES (36,0,66,'^000',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,9,0,'','international');
INSERT INTO `routes` VALUES (37,1,68,'^0',1,1,1,1,1,1,1,'00:00:00','24:00:00',NULL,6,9,0,'','alles andere');
/*!40000 ALTER TABLE `routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `routes_in`
--

DROP TABLE IF EXISTS `routes_in`;
CREATE TABLE `routes_in` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `gate_grp_id` smallint(5) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `ord` int(10) unsigned NOT NULL,
  `pattern` varchar(30) character set ascii NOT NULL,
  `d_mo` tinyint(3) unsigned NOT NULL default '1',
  `d_tu` tinyint(3) unsigned NOT NULL default '1',
  `d_we` tinyint(3) unsigned NOT NULL default '1',
  `d_th` tinyint(3) unsigned NOT NULL default '1',
  `d_fr` tinyint(3) unsigned NOT NULL default '1',
  `d_sa` tinyint(3) unsigned NOT NULL default '1',
  `d_su` tinyint(3) unsigned NOT NULL default '1',
  `h_from` time NOT NULL default '00:00:00',
  `h_to` time NOT NULL default '24:00:00',
  `to_ext` varchar(10) character set ascii NOT NULL,
  `descr` varchar(150) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `gategrp_ord` (`gate_grp_id`,`ord`),
  KEY `ggrp_active_mo` (`gate_grp_id`,`active`,`d_mo`,`ord`),
  KEY `ggrp_active_tu` (`gate_grp_id`,`active`,`d_tu`,`ord`),
  KEY `ggrp_active_we` (`gate_grp_id`,`active`,`d_we`,`ord`),
  KEY `ggrp_active_th` (`gate_grp_id`,`active`,`d_th`,`ord`),
  KEY `ggrp_active_fr` (`gate_grp_id`,`active`,`d_fr`,`ord`),
  KEY `ggrp_active_sa` (`gate_grp_id`,`active`,`d_sa`,`ord`),
  KEY `ggrp_active_su` (`gate_grp_id`,`active`,`d_su`,`ord`),
  CONSTRAINT `routes_in_ibfk_1` FOREIGN KEY (`gate_grp_id`) REFERENCES `gate_grps` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `routes_in`
--

LOCK TABLES `routes_in` WRITE;
/*!40000 ALTER TABLE `routes_in` DISABLE KEYS */;
INSERT INTO `routes_in` VALUES (1,6,1,99999,'^(.*)',1,1,1,1,1,1,1,'00:00:00','24:00:00','$1','1:1 DID -> Extension');
INSERT INTO `routes_in` VALUES (2,6,0,12,'^5000',1,1,1,1,1,1,1,'00:00:00','24:00:00','123','5000 auf 123');
-- INSERT INTO `routes_in` VALUES (3,6,0,4,'6(.*)',1,1,1,1,1,1,1,'00:00:00','24:00:00','fax-$1','Fax');
INSERT INTO `routes_in` VALUES (4,6,0,10,'^5000',1,1,1,1,1,0,0,'08:00:00','18:00:00','5000','5000 auf Queue wenn geöffnet');
/*!40000 ALTER TABLE `routes_in` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `softkey_profiles`
--

DROP TABLE IF EXISTS `softkey_profiles`;
CREATE TABLE `softkey_profiles` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `is_user_profile` tinyint(1) unsigned NOT NULL default '0',
  `title` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `title` (`title`(45)),
  KEY `is_user_profile_title` (`is_user_profile`,`title`(45))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `softkey_profiles`
--

LOCK TABLES `softkey_profiles` WRITE;
/*!40000 ALTER TABLE `softkey_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `softkey_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `softkeys`
--

DROP TABLE IF EXISTS `softkeys`;
CREATE TABLE `softkeys` (
  `profile_id` int(10) unsigned NOT NULL default '0',
  `phone_type` varchar(20) NOT NULL,
  `key` varchar(10) NOT NULL,
  `function` varchar(15) NOT NULL,
  `data` varchar(100) NOT NULL,
  `label` varchar(40) character set utf8 collate utf8_unicode_ci NOT NULL,
  `user_writeable` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`profile_id`,`phone_type`,`key`),
  KEY `phone_type_key` (`phone_type`,`key`),
  KEY `phone_type_function` (`phone_type`,`function`),
  CONSTRAINT `softkeys_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `softkey_profiles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

--
-- Dumping data for table `softkeys`
--

LOCK TABLES `softkeys` WRITE;
/*!40000 ALTER TABLE `softkeys` DISABLE KEYS */;
/*!40000 ALTER TABLE `softkeys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `systemrecordings`
--

DROP TABLE IF EXISTS `systemrecordings`;
CREATE TABLE IF NOT EXISTS `systemrecordings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `md5hashname` char(32) collate utf8_unicode_ci NOT NULL,
  `description` varchar(150) collate utf8_unicode_ci NOT NULL,
  `length` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `md5hashname` (`md5hashname`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE `user_groups` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `lft` mediumint(8) unsigned NOT NULL,
  `rgt` mediumint(8) unsigned NOT NULL,
  `name` varchar(20) character set ascii NOT NULL,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL,
  `softkey_profile_id` int(10) unsigned default NULL,
  `prov_param_profile_id` int(10) unsigned default NULL,
  `show_ext_modules` tinyint(1) unsigned NOT NULL default '255',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `lft` (`lft`),
  UNIQUE KEY `rgt` (`rgt`),
  KEY `softkey_profile_id` (`softkey_profile_id`),
  KEY `title` (`title`(40)),
  KEY `lft_rgt` (`lft`,`rgt`),
  KEY `prov_param_profile_id` (`prov_param_profile_id`),
  CONSTRAINT `user_groups_ibfk_6` FOREIGN KEY (`prov_param_profile_id`) REFERENCES `prov_param_profiles` (`id`),
  CONSTRAINT `user_groups_ibfk_5` FOREIGN KEY (`softkey_profile_id`) REFERENCES `softkey_profiles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_groups`
--

LOCK TABLES `user_groups` WRITE;
/*!40000 ALTER TABLE `user_groups` DISABLE KEYS */;
INSERT INTO `user_groups` VALUES (1,1,2,'root-node','Root node',NULL,NULL,255);
/*!40000 ALTER TABLE `user_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_watchlist`
--

DROP TABLE IF EXISTS `user_watchlist`;
CREATE TABLE `user_watchlist` (
  `user_id` int(10) unsigned NOT NULL,
  `buddy_user_id` int(10) unsigned NOT NULL,
  `status` enum('pnd','ack','nak') character set ascii COLLATE ascii_general_ci NOT NULL default 'pnd',
  PRIMARY KEY  (`user_id`,`buddy_user_id`),
  KEY `buddy_user_id_user_id` (`buddy_user_id`,`user_id`),
  CONSTRAINT `user_watchlist_ibfk_2` FOREIGN KEY (`buddy_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_watchlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_watchlist`
--

LOCK TABLES `user_watchlist` WRITE;
/*!40000 ALTER TABLE `user_watchlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_watchlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` varchar(20) character set ascii NOT NULL default '',
  `pin` varchar(10) character set ascii collate ascii_bin NOT NULL default '',
  `firstname` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `lastname` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `honorific` varchar(30) collate utf8_unicode_ci NOT NULL default '',
  `email` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  `nobody_index` mediumint(8) unsigned default NULL,
  `host_id` mediumint(8) unsigned default '1',
  `current_ip` varchar(15) character set ascii default NULL,
  `user_comment` varchar(200) collate utf8_unicode_ci NOT NULL default '',
  `group_id` mediumint(8) unsigned default NULL,
  `softkey_profile_id` int(10) unsigned default NULL,
  `prov_param_profile_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`),
  KEY `lastname_firstname` (`lastname`(15),`firstname`(15)),
  KEY `firstname_lastname` (`firstname`(15),`lastname`(15)),
  KEY `nobody_index` (`nobody_index`),
  KEY `host_id` (`host_id`),
  KEY `current_ip` (`current_ip`),
  KEY `group_id` (`group_id`),
  KEY `softkey_profile_id` (`softkey_profile_id`),
  KEY `prov_param_profile_id` (`prov_param_profile_id`),
  CONSTRAINT `users_ibfk_4` FOREIGN KEY (`softkey_profile_id`) REFERENCES `softkey_profiles` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `users_ibfk_5` FOREIGN KEY (`prov_param_profile_id`) REFERENCES `prov_param_profiles` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `users_ibfk_3` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (5,'nobody-00001','','','','','',1,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (6,'nobody-00002','','','','','',2,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (7,'nobody-00003','','','','','',3,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (8,'nobody-00004','','','','','',4,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (9,'nobody-00005','','','','','',5,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (10,'nobody-00006','','','','','',6,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (11,'nobody-00007','','','','','',7,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (12,'nobody-00008','','','','','',8,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (13,'nobody-00009','','','','','',9,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (14,'nobody-00010','','','','','',10,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (22,'hans','123','Hans','Muster','','',NULL,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (23,'peter','123','Peter','Muster','','',NULL,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (24,'anna','123','Anna','Muster','','',NULL,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (25,'lisa','123','Lisa','Muster','','',NULL,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (28,'nobody-00011','','','','','',11,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (29,'nobody-00012','','','','','',12,1,NULL,'',NULL,NULL,NULL);
INSERT INTO `users` VALUES (30,'nobody-00013','','','','','',13,1,NULL,'',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

-- 
-- Table structure for table `users_callerids`
-- 

DROP TABLE IF EXISTS `users_callerids`;
CREATE TABLE IF NOT EXISTS `users_callerids` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `number` varchar(25) character set ascii NOT NULL,
  `dest` enum('internal','external') character set ascii NOT NULL default 'external',
  `selected` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`number`,`dest`),
  CONSTRAINT `users_callerids_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `users_external_numbers`
--

DROP TABLE IF EXISTS `users_external_numbers`;
CREATE TABLE `users_external_numbers` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `number` varchar(25) character set latin1 collate latin1_general_ci NOT NULL default '',
  PRIMARY KEY  (`user_id`,`number`),
  KEY `number` (`number`),
  CONSTRAINT `users_external_numbers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users_external_numbers`
--

LOCK TABLES `users_external_numbers` WRITE;
/*!40000 ALTER TABLE `users_external_numbers` DISABLE KEYS */;
INSERT INTO `users_external_numbers` VALUES (23,'001701234567');
INSERT INTO `users_external_numbers` VALUES (23,'950001');
/*!40000 ALTER TABLE `users_external_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vm`
--

DROP TABLE IF EXISTS `vm`;
CREATE TABLE `vm` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `internal_active` tinyint(1) unsigned NOT NULL default '0',
  `external_active` tinyint(1) unsigned NOT NULL default '0',
  `email_notify` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  CONSTRAINT `vm_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `vm`
--

LOCK TABLES `vm` WRITE;
/*!40000 ALTER TABLE `vm` DISABLE KEYS */;
INSERT INTO `vm` VALUES (5,0,0,0);
INSERT INTO `vm` VALUES (6,0,0,0);
INSERT INTO `vm` VALUES (22,0,0,0);
INSERT INTO `vm` VALUES (23,0,1,0);
INSERT INTO `vm` VALUES (24,0,0,0);
INSERT INTO `vm` VALUES (25,0,0,0);
/*!40000 ALTER TABLE `vm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vm_msgs`
--

DROP TABLE IF EXISTS `vm_msgs`;
CREATE TABLE `vm_msgs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `host_id` mediumint(8) unsigned NOT NULL,
  `mbox` varchar(8) character set ascii NOT NULL,
  `user_id` int(10) unsigned default NULL,
  `orig_mbox` varchar(8) character set ascii NOT NULL,
  `folder` varchar(10) character set ascii NOT NULL,
  `file` varchar(10) character set ascii NOT NULL,
  `orig_time` int(10) unsigned NOT NULL,
  `dur` smallint(5) unsigned NOT NULL,
  `callerchan` varchar(40) character set ascii NOT NULL,
  `cidnum` varchar(25) character set ascii NOT NULL,
  `cidname` varchar(30) collate utf8_unicode_ci NOT NULL,
  `listened_to` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `mbox_folder_origtime` (`mbox`,`folder`,`orig_time`),
  KEY `origtime_callerchan` (`orig_time`,`callerchan`(20)),
  KEY `hostid` (`host_id`),
  KEY `userid_folder_origtime` (`user_id`,`folder`,`orig_time`),
  KEY `mbox_origtime_callerchan` (`mbox`,`orig_time`,`callerchan`(20)),
  CONSTRAINT `vm_msgs_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `hosts` (`id`),
  CONSTRAINT `vm_msgs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `vm_msgs`
--

LOCK TABLES `vm_msgs` WRITE;
/*!40000 ALTER TABLE `vm_msgs` DISABLE KEYS */;
/*!40000 ALTER TABLE `vm_msgs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Current Database: `asterisk`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `asterisk` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE `asterisk`;

--
-- View structure for view `ast_sipfriends_gs`
--

/*!50001 DROP TABLE IF EXISTS `ast_sipfriends_gs`*/;
/*!50001 DROP VIEW IF EXISTS `ast_sipfriends_gs`*/;
/*!50001 CREATE ALGORITHM=MERGE */
/*!50013 DEFINER=CURRENT_USER() SQL SECURITY INVOKER */
/*!50001 VIEW `ast_sipfriends_gs` AS (select `s`.`_user_id` AS `_user_id`,`s`.`name` AS `name`,`s`.`secret` AS `secret`,`s`.`type` AS `type`,`s`.`host` AS `host`,`s`.`defaultip` AS `defaultip`,`s`.`context` AS `context`,`s`.`callerid` AS `callerid`,`s`.`mailbox` AS `mailbox`,`s`.`callgroup` AS `callgroup`,`s`.`pickupgroup` AS `pickupgroup`,`s`.`setvar` AS `setvar`,`s`.`call-limit` AS `call-limit`,`s`.`subscribecontext` AS `subscribecontext`,`s`.`regcontext` AS `regcontext`,`s`.`ipaddr` AS `ipaddr`,`s`.`port` AS `port`,`s`.`regseconds` AS `regseconds`,`s`.`username` AS `username`,`s`.`regserver` AS `regserver`,`s`.`fullcontact` AS `fullcontact` from ((`ast_sipfriends` `s` join `users` `u` on((`u`.`id` = `s`.`_user_id`))) join `hosts` `h` on((`h`.`id` = `u`.`host_id`))) where (`h`.`is_foreign` = 0)) WITH CASCADED CHECK OPTION */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2008-11-10  12:00:00
