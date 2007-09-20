-- ----------------------------------------------------------------------------
--   Gemeinschaft database
--   This file was created with
--   mysqldump --opt --skip-extended-insert --databases asterisk > asterisk.sql
--   
--   $Revision$
-- ----------------------------------------------------------------------------


-- MySQL dump 10.11
--
-- Host: localhost    Database: asterisk
-- ------------------------------------------------------
-- Server version	5.0.32-Debian_7etch1-log

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

--
-- Table structure for table `area_codes`
--

DROP TABLE IF EXISTS `area_codes`;
CREATE TABLE `area_codes` (
  `cc` varchar(4) character set ascii NOT NULL,
  `ac` varchar(8) character set ascii NOT NULL,
  `area_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`cc`,`ac`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `area_codes`
--

LOCK TABLES `area_codes` WRITE;
/*!40000 ALTER TABLE `area_codes` DISABLE KEYS */;
INSERT INTO `area_codes` VALUES ('49','0201','Essen');
INSERT INTO `area_codes` VALUES ('49','0202','Wuppertal');
INSERT INTO `area_codes` VALUES ('49','0203','Duisburg');
INSERT INTO `area_codes` VALUES ('49','02041','Bottrop');
INSERT INTO `area_codes` VALUES ('49','02043','Gladbeck');
INSERT INTO `area_codes` VALUES ('49','02045','Bottrop-Kirchhellen');
INSERT INTO `area_codes` VALUES ('49','02051','Velbert');
INSERT INTO `area_codes` VALUES ('49','02052','Velbert-Langenberg');
INSERT INTO `area_codes` VALUES ('49','02053','Velbert-Neviges');
INSERT INTO `area_codes` VALUES ('49','02054','Essen-Kettwig');
INSERT INTO `area_codes` VALUES ('49','02056','Heiligenhaus');
INSERT INTO `area_codes` VALUES ('49','02058','Wülfrath');
INSERT INTO `area_codes` VALUES ('49','02064','Dinslaken');
INSERT INTO `area_codes` VALUES ('49','02065','Duisburg-Rheinhausen');
INSERT INTO `area_codes` VALUES ('49','02066','Duisburg-Homberg');
INSERT INTO `area_codes` VALUES ('49','0208','Oberhausen Rheinl');
INSERT INTO `area_codes` VALUES ('49','0209','Gelsenkirchen');
INSERT INTO `area_codes` VALUES ('49','02102','Ratingen');
INSERT INTO `area_codes` VALUES ('49','02103','Hilden');
INSERT INTO `area_codes` VALUES ('49','02104','Mettmann');
INSERT INTO `area_codes` VALUES ('49','0211','Düsseldorf');
INSERT INTO `area_codes` VALUES ('49','0212','Solingen');
INSERT INTO `area_codes` VALUES ('49','02129','Haan Rheinl');
INSERT INTO `area_codes` VALUES ('49','02131','Neuss');
INSERT INTO `area_codes` VALUES ('49','02132','Meerbusch-Büderich');
INSERT INTO `area_codes` VALUES ('49','02133','Dormagen');
INSERT INTO `area_codes` VALUES ('49','02137','Neuss-Norf');
INSERT INTO `area_codes` VALUES ('49','0214','Leverkusen');
INSERT INTO `area_codes` VALUES ('49','02150','Meerbusch-Lank');
INSERT INTO `area_codes` VALUES ('49','02151','Krefeld');
INSERT INTO `area_codes` VALUES ('49','02152','Kempen');
INSERT INTO `area_codes` VALUES ('49','02153','Nettetal-Lobberich');
INSERT INTO `area_codes` VALUES ('49','02154','Willich');
INSERT INTO `area_codes` VALUES ('49','02156','Willich-Anrath');
INSERT INTO `area_codes` VALUES ('49','02157','Nettetal-Kaldenkirchen');
INSERT INTO `area_codes` VALUES ('49','02158','Grefrath b Krefeld');
INSERT INTO `area_codes` VALUES ('49','02159','Meerbusch-Osterath');
INSERT INTO `area_codes` VALUES ('49','02161','Mönchengladbach');
INSERT INTO `area_codes` VALUES ('49','02162','Viersen');
INSERT INTO `area_codes` VALUES ('49','02163','Schwalmtal Niederrhein');
INSERT INTO `area_codes` VALUES ('49','02164','Jüchen-Otzenrath');
INSERT INTO `area_codes` VALUES ('49','02165','Jüchen');
INSERT INTO `area_codes` VALUES ('49','02166','Mönchengladbach-Rheydt');
INSERT INTO `area_codes` VALUES ('49','02171','Leverkusen-Opladen');
INSERT INTO `area_codes` VALUES ('49','02173','Langenfeld Rheinland');
INSERT INTO `area_codes` VALUES ('49','02174','Burscheid Rheinl');
INSERT INTO `area_codes` VALUES ('49','02175','Leichlingen Rheinland');
INSERT INTO `area_codes` VALUES ('49','02181','Grevenbroich');
INSERT INTO `area_codes` VALUES ('49','02182','Grevenbroich-Kapellen');
INSERT INTO `area_codes` VALUES ('49','02183','Rommerskirchen');
INSERT INTO `area_codes` VALUES ('49','02191','Remscheid');
INSERT INTO `area_codes` VALUES ('49','02192','Hückeswagen');
INSERT INTO `area_codes` VALUES ('49','02193','Dabringhausen');
INSERT INTO `area_codes` VALUES ('49','02195','Radevormwald');
INSERT INTO `area_codes` VALUES ('49','02196','Wermelskirchen');
INSERT INTO `area_codes` VALUES ('49','02202','Bergisch Gladbach');
INSERT INTO `area_codes` VALUES ('49','02203','Köln-Porz');
INSERT INTO `area_codes` VALUES ('49','02204','Bensberg');
INSERT INTO `area_codes` VALUES ('49','02205','Rösrath');
INSERT INTO `area_codes` VALUES ('49','02206','Overath');
INSERT INTO `area_codes` VALUES ('49','02207','Kürten-Dürscheid');
INSERT INTO `area_codes` VALUES ('49','02208','Niederkassel');
INSERT INTO `area_codes` VALUES ('49','0221','Köln');
INSERT INTO `area_codes` VALUES ('49','02222','Bornheim Rheinl');
INSERT INTO `area_codes` VALUES ('49','02223','Königswinter');
INSERT INTO `area_codes` VALUES ('49','02224','Bad Honnef');
INSERT INTO `area_codes` VALUES ('49','02225','Meckenheim Rheinl');
INSERT INTO `area_codes` VALUES ('49','02226','Rheinbach');
INSERT INTO `area_codes` VALUES ('49','02227','Bornheim-Merten');
INSERT INTO `area_codes` VALUES ('49','02228','Remagen-Rolandseck');
INSERT INTO `area_codes` VALUES ('49','02232','Brühl Rheinl');
INSERT INTO `area_codes` VALUES ('49','02233','Hürth Rheinl');
INSERT INTO `area_codes` VALUES ('49','02234','Frechen');
INSERT INTO `area_codes` VALUES ('49','02235','Erftstadt');
INSERT INTO `area_codes` VALUES ('49','02236','Wesseling Rheinl');
INSERT INTO `area_codes` VALUES ('49','02237','Kerpen Rheinl-Türnich');
INSERT INTO `area_codes` VALUES ('49','02238','Pulheim');
INSERT INTO `area_codes` VALUES ('49','02241','Siegburg');
INSERT INTO `area_codes` VALUES ('49','02242','Hennef Sieg');
INSERT INTO `area_codes` VALUES ('49','02243','Eitorf');
INSERT INTO `area_codes` VALUES ('49','02244','Königswinter-Oberpleis');
INSERT INTO `area_codes` VALUES ('49','02245','Much');
INSERT INTO `area_codes` VALUES ('49','02246','Lohmar');
INSERT INTO `area_codes` VALUES ('49','02247','Neunkirchen-Seelscheid');
INSERT INTO `area_codes` VALUES ('49','02248','Hennef-Uckerath');
INSERT INTO `area_codes` VALUES ('49','02251','Euskirchen');
INSERT INTO `area_codes` VALUES ('49','02252','Zülpich');
INSERT INTO `area_codes` VALUES ('49','02253','Bad Münstereifel');
INSERT INTO `area_codes` VALUES ('49','02254','Weilerswist');
INSERT INTO `area_codes` VALUES ('49','02255','Euskirchen-Flamersheim');
INSERT INTO `area_codes` VALUES ('49','02256','Mechernich-Satzvey');
INSERT INTO `area_codes` VALUES ('49','02257','Reckerscheid');
INSERT INTO `area_codes` VALUES ('49','02261','Gummersbach');
INSERT INTO `area_codes` VALUES ('49','02262','Wiehl');
INSERT INTO `area_codes` VALUES ('49','02263','Engelskirchen');
INSERT INTO `area_codes` VALUES ('49','02264','Marienheide');
INSERT INTO `area_codes` VALUES ('49','02265','Reichshof-Eckenhagen');
INSERT INTO `area_codes` VALUES ('49','02266','Lindlar');
INSERT INTO `area_codes` VALUES ('49','02267','Wipperfürth');
INSERT INTO `area_codes` VALUES ('49','02268','Kürten');
INSERT INTO `area_codes` VALUES ('49','02269','Kierspe-Rönsahl');
INSERT INTO `area_codes` VALUES ('49','02271','Bergheim Erft');
INSERT INTO `area_codes` VALUES ('49','02272','Bedburg Erft');
INSERT INTO `area_codes` VALUES ('49','02273','Kerpen-Horrem');
INSERT INTO `area_codes` VALUES ('49','02274','Elsdorf Rheinl');
INSERT INTO `area_codes` VALUES ('49','02275','Kerpen-Buir');
INSERT INTO `area_codes` VALUES ('49','0228','Bonn');
INSERT INTO `area_codes` VALUES ('49','02291','Waldbröl');
INSERT INTO `area_codes` VALUES ('49','02292','Windeck Sieg');
INSERT INTO `area_codes` VALUES ('49','02293','Nümbrecht');
INSERT INTO `area_codes` VALUES ('49','02294','Morsbach Sieg');
INSERT INTO `area_codes` VALUES ('49','02295','Ruppichteroth');
INSERT INTO `area_codes` VALUES ('49','02296','Reichshof-Brüchermühle');
INSERT INTO `area_codes` VALUES ('49','02297','Wildbergerhütte');
INSERT INTO `area_codes` VALUES ('49','02301','Holzwickede');
INSERT INTO `area_codes` VALUES ('49','02302','Witten');
INSERT INTO `area_codes` VALUES ('49','02303','Unna');
INSERT INTO `area_codes` VALUES ('49','02304','Schwerte');
INSERT INTO `area_codes` VALUES ('49','02305','Castrop-Rauxel');
INSERT INTO `area_codes` VALUES ('49','02306','Lünen');
INSERT INTO `area_codes` VALUES ('49','02307','Kamen');
INSERT INTO `area_codes` VALUES ('49','02308','Unna-Hemmerde');
INSERT INTO `area_codes` VALUES ('49','02309','Waltrop');
INSERT INTO `area_codes` VALUES ('49','0231','Dortmund');
INSERT INTO `area_codes` VALUES ('49','02323','Herne');
INSERT INTO `area_codes` VALUES ('49','02324','Hattingen Ruhr');
INSERT INTO `area_codes` VALUES ('49','02325','Wanne-Eickel');
INSERT INTO `area_codes` VALUES ('49','02327','Bochum-Wattenscheid');
INSERT INTO `area_codes` VALUES ('49','02330','Herdecke');
INSERT INTO `area_codes` VALUES ('49','02331','Hagen Westf');
INSERT INTO `area_codes` VALUES ('49','02332','Gevelsberg');
INSERT INTO `area_codes` VALUES ('49','02333','Ennepetal');
INSERT INTO `area_codes` VALUES ('49','02334','Hagen-Hohenlimburg');
INSERT INTO `area_codes` VALUES ('49','02335','Wetter Ruhr');
INSERT INTO `area_codes` VALUES ('49','02336','Schwelm');
INSERT INTO `area_codes` VALUES ('49','02337','Hagen-Dahl');
INSERT INTO `area_codes` VALUES ('49','02338','Breckerfeld');
INSERT INTO `area_codes` VALUES ('49','02339','Sprockhövel-Haßlinghausen');
INSERT INTO `area_codes` VALUES ('49','0234','Bochum');
INSERT INTO `area_codes` VALUES ('49','02351','Lüdenscheid');
INSERT INTO `area_codes` VALUES ('49','02352','Altena Westf');
INSERT INTO `area_codes` VALUES ('49','02353','Halver');
INSERT INTO `area_codes` VALUES ('49','02354','Meinerzhagen');
INSERT INTO `area_codes` VALUES ('49','02355','Schalksmühle');
INSERT INTO `area_codes` VALUES ('49','02357','Herscheid Westf');
INSERT INTO `area_codes` VALUES ('49','02358','Meinerzhagen-Valbert');
INSERT INTO `area_codes` VALUES ('49','02359','Kierspe');
INSERT INTO `area_codes` VALUES ('49','02360','Haltern-Lippramsdorf');
INSERT INTO `area_codes` VALUES ('49','02361','Recklinghausen');
INSERT INTO `area_codes` VALUES ('49','02362','Dorsten');
INSERT INTO `area_codes` VALUES ('49','02363','Datteln');
INSERT INTO `area_codes` VALUES ('49','02364','Haltern Westf');
INSERT INTO `area_codes` VALUES ('49','02365','Marl');
INSERT INTO `area_codes` VALUES ('49','02366','Herten Westf');
INSERT INTO `area_codes` VALUES ('49','02367','Henrichenburg');
INSERT INTO `area_codes` VALUES ('49','02368','Oer-Erkenschwick');
INSERT INTO `area_codes` VALUES ('49','02369','Dorsten-Wulfen');
INSERT INTO `area_codes` VALUES ('49','02371','Iserlohn');
INSERT INTO `area_codes` VALUES ('49','02372','Hemer');
INSERT INTO `area_codes` VALUES ('49','02373','Menden Sauerland');
INSERT INTO `area_codes` VALUES ('49','02374','Iserlohn-Letmathe');
INSERT INTO `area_codes` VALUES ('49','02375','Balve');
INSERT INTO `area_codes` VALUES ('49','02377','Wickede Ruhr');
INSERT INTO `area_codes` VALUES ('49','02378','Fröndenberg-Langschede');
INSERT INTO `area_codes` VALUES ('49','02379','Menden-Asbeck');
INSERT INTO `area_codes` VALUES ('49','02381','Hamm Westf');
INSERT INTO `area_codes` VALUES ('49','02382','Ahlen Westf');
INSERT INTO `area_codes` VALUES ('49','02383','Bönen');
INSERT INTO `area_codes` VALUES ('49','02384','Welver');
INSERT INTO `area_codes` VALUES ('49','02385','Hamm-Rhynern');
INSERT INTO `area_codes` VALUES ('49','02387','Drensteinfurt-Walstedde');
INSERT INTO `area_codes` VALUES ('49','02388','Hamm-Uentrop');
INSERT INTO `area_codes` VALUES ('49','02389','Werne');
INSERT INTO `area_codes` VALUES ('49','02391','Plettenberg');
INSERT INTO `area_codes` VALUES ('49','02392','Werdohl');
INSERT INTO `area_codes` VALUES ('49','02393','Sundern-Allendorf');
INSERT INTO `area_codes` VALUES ('49','02394','Neuenrade-Affeln');
INSERT INTO `area_codes` VALUES ('49','02395','Finnentrop-Rönkhausen');
INSERT INTO `area_codes` VALUES ('49','02401','Baesweiler');
INSERT INTO `area_codes` VALUES ('49','02402','Stolberg Rheinl');
INSERT INTO `area_codes` VALUES ('49','02403','Eschweiler Rheinl');
INSERT INTO `area_codes` VALUES ('49','02404','Alsdorf Rheinl');
INSERT INTO `area_codes` VALUES ('49','02405','Würselen');
INSERT INTO `area_codes` VALUES ('49','02406','Herzogenrath');
INSERT INTO `area_codes` VALUES ('49','02407','Herzogenrath-Kohlscheid');
INSERT INTO `area_codes` VALUES ('49','02408','Aachen-Kornelimünster');
INSERT INTO `area_codes` VALUES ('49','02409','Stolberg-Gressenich');
INSERT INTO `area_codes` VALUES ('49','0241','Aachen');
INSERT INTO `area_codes` VALUES ('49','02421','Düren');
INSERT INTO `area_codes` VALUES ('49','02422','Kreuzau');
INSERT INTO `area_codes` VALUES ('49','02423','Langerwehe');
INSERT INTO `area_codes` VALUES ('49','02424','Vettweiss');
INSERT INTO `area_codes` VALUES ('49','02425','Nideggen-Embken');
INSERT INTO `area_codes` VALUES ('49','02426','Nörvenich');
INSERT INTO `area_codes` VALUES ('49','02427','Nideggen');
INSERT INTO `area_codes` VALUES ('49','02428','Niederzier');
INSERT INTO `area_codes` VALUES ('49','02429','Hürtgenwald');
INSERT INTO `area_codes` VALUES ('49','02431','Erkelenz');
INSERT INTO `area_codes` VALUES ('49','02432','Wassenberg');
INSERT INTO `area_codes` VALUES ('49','02433','Hückelhoven');
INSERT INTO `area_codes` VALUES ('49','02434','Wegberg');
INSERT INTO `area_codes` VALUES ('49','02435','Erkelenz-Lövenich');
INSERT INTO `area_codes` VALUES ('49','02436','Wegberg-Rödgen');
INSERT INTO `area_codes` VALUES ('49','02440','Nettersheim-Tondorf');
INSERT INTO `area_codes` VALUES ('49','02441','Kall');
INSERT INTO `area_codes` VALUES ('49','02443','Mechernich');
INSERT INTO `area_codes` VALUES ('49','02444','Schleiden-Gemünd');
INSERT INTO `area_codes` VALUES ('49','02445','Schleiden Eifel');
INSERT INTO `area_codes` VALUES ('49','02446','Heimbach Eifel');
INSERT INTO `area_codes` VALUES ('49','02447','Dahlem b Kall');
INSERT INTO `area_codes` VALUES ('49','02448','Hellenthal-Rescheid');
INSERT INTO `area_codes` VALUES ('49','02449','Blankenheim Ahr');
INSERT INTO `area_codes` VALUES ('49','02451','Geilenkirchen');
INSERT INTO `area_codes` VALUES ('49','02452','Heinsberg Rheinl');
INSERT INTO `area_codes` VALUES ('49','02453','Heinsberg-Randerath');
INSERT INTO `area_codes` VALUES ('49','02454','Gangelt');
INSERT INTO `area_codes` VALUES ('49','02455','Waldfeucht');
INSERT INTO `area_codes` VALUES ('49','02456','Selfkant');
INSERT INTO `area_codes` VALUES ('49','02461','Jülich');
INSERT INTO `area_codes` VALUES ('49','02462','Linnich');
INSERT INTO `area_codes` VALUES ('49','02463','Titz');
INSERT INTO `area_codes` VALUES ('49','02464','Aldenhoven b Jülich');
INSERT INTO `area_codes` VALUES ('49','02465','Inden');
INSERT INTO `area_codes` VALUES ('49','02471','Roetgen Eifel');
INSERT INTO `area_codes` VALUES ('49','02472','Monschau');
INSERT INTO `area_codes` VALUES ('49','02473','Simmerath');
INSERT INTO `area_codes` VALUES ('49','02474','Nideggen-Schmidt');
INSERT INTO `area_codes` VALUES ('49','02482','Hellenthal');
INSERT INTO `area_codes` VALUES ('49','02484','Mechernich-Eiserfey');
INSERT INTO `area_codes` VALUES ('49','02485','Schleiden-Dreiborn');
INSERT INTO `area_codes` VALUES ('49','02486','Nettersheim');
INSERT INTO `area_codes` VALUES ('49','02501','Münster-Hiltrup');
INSERT INTO `area_codes` VALUES ('49','02502','Nottuln');
INSERT INTO `area_codes` VALUES ('49','02504','Telgte');
INSERT INTO `area_codes` VALUES ('49','02505','Altenberge Westf');
INSERT INTO `area_codes` VALUES ('49','02506','Münster-Wolbeck');
INSERT INTO `area_codes` VALUES ('49','02507','Havixbeck');
INSERT INTO `area_codes` VALUES ('49','02508','Drensteinfurt');
INSERT INTO `area_codes` VALUES ('49','02509','Nottuln-Appelhülsen');
INSERT INTO `area_codes` VALUES ('49','0251','Münster');
INSERT INTO `area_codes` VALUES ('49','02520','Wadersloh-Diestedde');
INSERT INTO `area_codes` VALUES ('49','02521','Beckum');
INSERT INTO `area_codes` VALUES ('49','02522','Oelde');
INSERT INTO `area_codes` VALUES ('49','02523','Wadersloh');
INSERT INTO `area_codes` VALUES ('49','02524','Ennigerloh');
INSERT INTO `area_codes` VALUES ('49','02525','Beckum-Neubeckum');
INSERT INTO `area_codes` VALUES ('49','02526','Sendenhorst');
INSERT INTO `area_codes` VALUES ('49','02527','Lippetal-Lippborg');
INSERT INTO `area_codes` VALUES ('49','02528','Ennigerloh-Enniger');
INSERT INTO `area_codes` VALUES ('49','02529','Oelde-Stromberg');
INSERT INTO `area_codes` VALUES ('49','02532','Ostbevern');
INSERT INTO `area_codes` VALUES ('49','02533','Münster-Nienberge');
INSERT INTO `area_codes` VALUES ('49','02534','Münster-Roxel');
INSERT INTO `area_codes` VALUES ('49','02535','Sendenhorst-Albersloh');
INSERT INTO `area_codes` VALUES ('49','02536','Münster-Albachten');
INSERT INTO `area_codes` VALUES ('49','02538','Drensteinfurt-Rinkerode');
INSERT INTO `area_codes` VALUES ('49','02541','Coesfeld');
INSERT INTO `area_codes` VALUES ('49','02542','Gescher');
INSERT INTO `area_codes` VALUES ('49','02543','Billerbeck Westf');
INSERT INTO `area_codes` VALUES ('49','02545','Rosendahl-Darfeld');
INSERT INTO `area_codes` VALUES ('49','02546','Coesfeld-Lette');
INSERT INTO `area_codes` VALUES ('49','02547','Rosendahl-Osterwick');
INSERT INTO `area_codes` VALUES ('49','02548','Dülmen-Rorup');
INSERT INTO `area_codes` VALUES ('49','02551','Steinfurt-Burgsteinfurt');
INSERT INTO `area_codes` VALUES ('49','02552','Steinfurt-Borghorst');
INSERT INTO `area_codes` VALUES ('49','02553','Ochtrup');
INSERT INTO `area_codes` VALUES ('49','02554','Laer Kr Steinfurt');
INSERT INTO `area_codes` VALUES ('49','02555','Schöppingen');
INSERT INTO `area_codes` VALUES ('49','02556','Metelen');
INSERT INTO `area_codes` VALUES ('49','02557','Wettringen Kr Steinfurt');
INSERT INTO `area_codes` VALUES ('49','02558','Horstmar');
INSERT INTO `area_codes` VALUES ('49','02561','Ahaus');
INSERT INTO `area_codes` VALUES ('49','02562','Gronau Westfalen');
INSERT INTO `area_codes` VALUES ('49','02563','Stadtlohn');
INSERT INTO `area_codes` VALUES ('49','02564','Vreden');
INSERT INTO `area_codes` VALUES ('49','02565','Gronau-Epe');
INSERT INTO `area_codes` VALUES ('49','02566','Legden');
INSERT INTO `area_codes` VALUES ('49','02567','Ahaus-Alstätte');
INSERT INTO `area_codes` VALUES ('49','02568','Heek');
INSERT INTO `area_codes` VALUES ('49','02571','Greven Westf');
INSERT INTO `area_codes` VALUES ('49','02572','Emsdetten');
INSERT INTO `area_codes` VALUES ('49','02573','Nordwalde');
INSERT INTO `area_codes` VALUES ('49','02574','Saerbeck');
INSERT INTO `area_codes` VALUES ('49','02575','Greven-Reckenfeld');
INSERT INTO `area_codes` VALUES ('49','02581','Warendorf');
INSERT INTO `area_codes` VALUES ('49','02582','Everswinkel');
INSERT INTO `area_codes` VALUES ('49','02583','Sassenberg');
INSERT INTO `area_codes` VALUES ('49','02584','Warendorf-Milte');
INSERT INTO `area_codes` VALUES ('49','02585','Warendorf-Hoetmar');
INSERT INTO `area_codes` VALUES ('49','02586','Beelen');
INSERT INTO `area_codes` VALUES ('49','02587','Ennigerloh-Westkirchen');
INSERT INTO `area_codes` VALUES ('49','02588','Harsewinkel-Greffen');
INSERT INTO `area_codes` VALUES ('49','02590','Dülmen-Buldern');
INSERT INTO `area_codes` VALUES ('49','02591','Lüdinghausen');
INSERT INTO `area_codes` VALUES ('49','02592','Selm');
INSERT INTO `area_codes` VALUES ('49','02593','Ascheberg Westf');
INSERT INTO `area_codes` VALUES ('49','02594','Dülmen');
INSERT INTO `area_codes` VALUES ('49','02595','Olfen');
INSERT INTO `area_codes` VALUES ('49','02596','Nordkirchen');
INSERT INTO `area_codes` VALUES ('49','02597','Senden Westf');
INSERT INTO `area_codes` VALUES ('49','02598','Senden-Ottmarsbocholt');
INSERT INTO `area_codes` VALUES ('49','02599','Ascheberg-Herbern');
INSERT INTO `area_codes` VALUES ('49','02601','Nauort');
INSERT INTO `area_codes` VALUES ('49','02602','Montabaur');
INSERT INTO `area_codes` VALUES ('49','02603','Bad Ems');
INSERT INTO `area_codes` VALUES ('49','02604','Nassau Lahn');
INSERT INTO `area_codes` VALUES ('49','02605','Löf');
INSERT INTO `area_codes` VALUES ('49','02606','Winningen Mosel');
INSERT INTO `area_codes` VALUES ('49','02607','Kobern-Gondorf');
INSERT INTO `area_codes` VALUES ('49','02608','Welschneudorf');
INSERT INTO `area_codes` VALUES ('49','0261','Koblenz a Rhein');
INSERT INTO `area_codes` VALUES ('49','02620','Neuhäusel Westerw');
INSERT INTO `area_codes` VALUES ('49','02621','Lahnstein');
INSERT INTO `area_codes` VALUES ('49','02622','Bendorf Rhein');
INSERT INTO `area_codes` VALUES ('49','02623','Ransbach-Baumbach');
INSERT INTO `area_codes` VALUES ('49','02624','Höhr-Grenzhausen');
INSERT INTO `area_codes` VALUES ('49','02625','Ochtendung');
INSERT INTO `area_codes` VALUES ('49','02626','Selters Westerwald');
INSERT INTO `area_codes` VALUES ('49','02627','Braubach');
INSERT INTO `area_codes` VALUES ('49','02628','Rhens');
INSERT INTO `area_codes` VALUES ('49','02630','Mülheim-Kärlich');
INSERT INTO `area_codes` VALUES ('49','02631','Neuwied');
INSERT INTO `area_codes` VALUES ('49','02632','Andernach');
INSERT INTO `area_codes` VALUES ('49','02633','Brohl-Lützing');
INSERT INTO `area_codes` VALUES ('49','02634','Rengsdorf');
INSERT INTO `area_codes` VALUES ('49','02635','Rheinbrohl');
INSERT INTO `area_codes` VALUES ('49','02636','Burgbrohl');
INSERT INTO `area_codes` VALUES ('49','02637','Weissenthurm');
INSERT INTO `area_codes` VALUES ('49','02638','Waldbreitbach');
INSERT INTO `area_codes` VALUES ('49','02639','Anhausen Kr Neuwied');
INSERT INTO `area_codes` VALUES ('49','02641','Bad Neuenahr-Ahrweiler');
INSERT INTO `area_codes` VALUES ('49','02642','Remagen');
INSERT INTO `area_codes` VALUES ('49','02643','Altenahr');
INSERT INTO `area_codes` VALUES ('49','02644','Linz am Rhein');
INSERT INTO `area_codes` VALUES ('49','02645','Vettelschoss');
INSERT INTO `area_codes` VALUES ('49','02646','Königsfeld Eifel');
INSERT INTO `area_codes` VALUES ('49','02647','Kesseling');
INSERT INTO `area_codes` VALUES ('49','02651','Mayen');
INSERT INTO `area_codes` VALUES ('49','02652','Mendig');
INSERT INTO `area_codes` VALUES ('49','02653','Kaisersesch');
INSERT INTO `area_codes` VALUES ('49','02654','Polch');
INSERT INTO `area_codes` VALUES ('49','02655','Weibern');
INSERT INTO `area_codes` VALUES ('49','02656','Virneburg');
INSERT INTO `area_codes` VALUES ('49','02657','Uersfeld');
INSERT INTO `area_codes` VALUES ('49','02661','Bad Marienberg Westerwald');
INSERT INTO `area_codes` VALUES ('49','02662','Hachenburg');
INSERT INTO `area_codes` VALUES ('49','02663','Westerburg Westerw');
INSERT INTO `area_codes` VALUES ('49','02664','Rennerod');
INSERT INTO `area_codes` VALUES ('49','02666','Freilingen Westerw');
INSERT INTO `area_codes` VALUES ('49','02667','Stein-Neukirch');
INSERT INTO `area_codes` VALUES ('49','02671','Cochem');
INSERT INTO `area_codes` VALUES ('49','02672','Treis-Karden');
INSERT INTO `area_codes` VALUES ('49','02673','Ellenz-Poltersdorf');
INSERT INTO `area_codes` VALUES ('49','02674','Bad Bertrich');
INSERT INTO `area_codes` VALUES ('49','02675','Ediger-Eller');
INSERT INTO `area_codes` VALUES ('49','02676','Ulmen');
INSERT INTO `area_codes` VALUES ('49','02677','Lutzerath');
INSERT INTO `area_codes` VALUES ('49','02678','Büchel b Cochem');
INSERT INTO `area_codes` VALUES ('49','02680','Mündersbach');
INSERT INTO `area_codes` VALUES ('49','02681','Altenkirchen Westerwald');
INSERT INTO `area_codes` VALUES ('49','02682','Hamm Sieg');
INSERT INTO `area_codes` VALUES ('49','02683','Asbach Westerw');
INSERT INTO `area_codes` VALUES ('49','02684','Puderbach Westerw');
INSERT INTO `area_codes` VALUES ('49','02685','Flammersfeld');
INSERT INTO `area_codes` VALUES ('49','02686','Weyerbusch');
INSERT INTO `area_codes` VALUES ('49','02687','Horhausen Westerwald');
INSERT INTO `area_codes` VALUES ('49','02688','Kroppach');
INSERT INTO `area_codes` VALUES ('49','02689','Dierdorf');
INSERT INTO `area_codes` VALUES ('49','02691','Adenau');
INSERT INTO `area_codes` VALUES ('49','02692','Kelberg');
INSERT INTO `area_codes` VALUES ('49','02693','Antweiler');
INSERT INTO `area_codes` VALUES ('49','02694','Wershofen');
INSERT INTO `area_codes` VALUES ('49','02695','Insul');
INSERT INTO `area_codes` VALUES ('49','02696','Nohn Eifel');
INSERT INTO `area_codes` VALUES ('49','02697','Blankenheim-Ahrhütte');
INSERT INTO `area_codes` VALUES ('49','0271','Siegen');
INSERT INTO `area_codes` VALUES ('49','02721','Lennestadt');
INSERT INTO `area_codes` VALUES ('49','02722','Attendorn');
INSERT INTO `area_codes` VALUES ('49','02723','Kirchhundem');
INSERT INTO `area_codes` VALUES ('49','02724','Finnentrop-Serkenrode');
INSERT INTO `area_codes` VALUES ('49','02725','Lennestadt-Oedingen');
INSERT INTO `area_codes` VALUES ('49','02732','Kreuztal');
INSERT INTO `area_codes` VALUES ('49','02733','Hilchenbach');
INSERT INTO `area_codes` VALUES ('49','02734','Freudenberg Westf');
INSERT INTO `area_codes` VALUES ('49','02735','Neunkirchen Siegerl');
INSERT INTO `area_codes` VALUES ('49','02736','Burbach Siegerl');
INSERT INTO `area_codes` VALUES ('49','02737','Netphen-Deuz');
INSERT INTO `area_codes` VALUES ('49','02738','Netphen');
INSERT INTO `area_codes` VALUES ('49','02739','Wilnsdorf');
INSERT INTO `area_codes` VALUES ('49','02741','Betzdorf');
INSERT INTO `area_codes` VALUES ('49','02742','Wissen');
INSERT INTO `area_codes` VALUES ('49','02743','Daaden');
INSERT INTO `area_codes` VALUES ('49','02744','Herdorf');
INSERT INTO `area_codes` VALUES ('49','02745','Brachbach Sieg');
INSERT INTO `area_codes` VALUES ('49','02747','Molzhain');
INSERT INTO `area_codes` VALUES ('49','02750','Diedenshausen');
INSERT INTO `area_codes` VALUES ('49','02751','Bad Berleburg');
INSERT INTO `area_codes` VALUES ('49','02752','Bad Laasphe');
INSERT INTO `area_codes` VALUES ('49','02753','Erndtebrück');
INSERT INTO `area_codes` VALUES ('49','02754','Bad Laasphe-Feudingen');
INSERT INTO `area_codes` VALUES ('49','02755','Bad Berleburg-Schwarzenau');
INSERT INTO `area_codes` VALUES ('49','02758','Bad Berleburg-Girkhausen');
INSERT INTO `area_codes` VALUES ('49','02759','Bad Berleburg-Aue');
INSERT INTO `area_codes` VALUES ('49','02761','Olpe Biggesee');
INSERT INTO `area_codes` VALUES ('49','02762','Wenden Südsauerland');
INSERT INTO `area_codes` VALUES ('49','02763','Drolshagen-Bleche');
INSERT INTO `area_codes` VALUES ('49','02764','Welschen Ennest');
INSERT INTO `area_codes` VALUES ('49','02770','Eschenburg');
INSERT INTO `area_codes` VALUES ('49','02771','Dillenburg');
INSERT INTO `area_codes` VALUES ('49','02772','Herborn Hess');
INSERT INTO `area_codes` VALUES ('49','02773','Haiger');
INSERT INTO `area_codes` VALUES ('49','02774','Dietzhölztal');
INSERT INTO `area_codes` VALUES ('49','02775','Driedorf');
INSERT INTO `area_codes` VALUES ('49','02776','Bad Endbach-Hartenrod');
INSERT INTO `area_codes` VALUES ('49','02777','Breitscheid Hess');
INSERT INTO `area_codes` VALUES ('49','02778','Siegbach');
INSERT INTO `area_codes` VALUES ('49','02779','Greifenstein-Beilstein');
INSERT INTO `area_codes` VALUES ('49','02801','Xanten');
INSERT INTO `area_codes` VALUES ('49','02802','Alpen');
INSERT INTO `area_codes` VALUES ('49','02803','Wesel-Büderich');
INSERT INTO `area_codes` VALUES ('49','02804','Xanten-Marienbaum');
INSERT INTO `area_codes` VALUES ('49','0281','Wesel');
INSERT INTO `area_codes` VALUES ('49','02821','Kleve Niederrhein');
INSERT INTO `area_codes` VALUES ('49','02822','Emmerich');
INSERT INTO `area_codes` VALUES ('49','02823','Goch');
INSERT INTO `area_codes` VALUES ('49','02824','Kalkar');
INSERT INTO `area_codes` VALUES ('49','02825','Uedem');
INSERT INTO `area_codes` VALUES ('49','02826','Kranenburg Niederrhein');
INSERT INTO `area_codes` VALUES ('49','02827','Goch-Hassum');
INSERT INTO `area_codes` VALUES ('49','02828','Emmerich-Elten');
INSERT INTO `area_codes` VALUES ('49','02831','Geldern');
INSERT INTO `area_codes` VALUES ('49','02832','Kevelaer');
INSERT INTO `area_codes` VALUES ('49','02833','Kerken');
INSERT INTO `area_codes` VALUES ('49','02834','Straelen');
INSERT INTO `area_codes` VALUES ('49','02835','Issum');
INSERT INTO `area_codes` VALUES ('49','02836','Wachtendonk');
INSERT INTO `area_codes` VALUES ('49','02837','Weeze');
INSERT INTO `area_codes` VALUES ('49','02838','Sonsbeck');
INSERT INTO `area_codes` VALUES ('49','02839','Straelen-Herongen');
INSERT INTO `area_codes` VALUES ('49','02841','Moers');
INSERT INTO `area_codes` VALUES ('49','02842','Kamp-Lintfort');
INSERT INTO `area_codes` VALUES ('49','02843','Rheinberg');
INSERT INTO `area_codes` VALUES ('49','02844','Rheinberg-Orsoy');
INSERT INTO `area_codes` VALUES ('49','02845','Neukirchen-Vluyn');
INSERT INTO `area_codes` VALUES ('49','02850','Rees-Haldern');
INSERT INTO `area_codes` VALUES ('49','02851','Rees');
INSERT INTO `area_codes` VALUES ('49','02852','Hamminkeln');
INSERT INTO `area_codes` VALUES ('49','02853','Schermbeck');
INSERT INTO `area_codes` VALUES ('49','02855','Voerde Niederrhein');
INSERT INTO `area_codes` VALUES ('49','02856','Hamminkeln-Brünen');
INSERT INTO `area_codes` VALUES ('49','02857','Rees-Mehr');
INSERT INTO `area_codes` VALUES ('49','02858','Hünxe');
INSERT INTO `area_codes` VALUES ('49','02859','Wesel-Bislich');
INSERT INTO `area_codes` VALUES ('49','02861','Borken Westf');
INSERT INTO `area_codes` VALUES ('49','02862','Südlohn');
INSERT INTO `area_codes` VALUES ('49','02863','Velen');
INSERT INTO `area_codes` VALUES ('49','02864','Reken');
INSERT INTO `area_codes` VALUES ('49','02865','Raesfeld');
INSERT INTO `area_codes` VALUES ('49','02866','Dorsten-Rhade');
INSERT INTO `area_codes` VALUES ('49','02867','Heiden Kr Borken');
INSERT INTO `area_codes` VALUES ('49','02871','Bocholt');
INSERT INTO `area_codes` VALUES ('49','02872','Rhede Westf');
INSERT INTO `area_codes` VALUES ('49','02873','Isselburg-Werth');
INSERT INTO `area_codes` VALUES ('49','02874','Isselburg');
INSERT INTO `area_codes` VALUES ('49','02902','Warstein');
INSERT INTO `area_codes` VALUES ('49','02903','Meschede-Freienohl');
INSERT INTO `area_codes` VALUES ('49','02904','Bestwig');
INSERT INTO `area_codes` VALUES ('49','02905','Bestwig-Ramsbeck');
INSERT INTO `area_codes` VALUES ('49','0291','Meschede');
INSERT INTO `area_codes` VALUES ('49','02921','Soest');
INSERT INTO `area_codes` VALUES ('49','02922','Werl');
INSERT INTO `area_codes` VALUES ('49','02923','Lippetal-Herzfeld');
INSERT INTO `area_codes` VALUES ('49','02924','Möhnesee');
INSERT INTO `area_codes` VALUES ('49','02925','Warstein-Allagen');
INSERT INTO `area_codes` VALUES ('49','02927','Neuengeseke');
INSERT INTO `area_codes` VALUES ('49','02928','Soest-Ostönnen');
INSERT INTO `area_codes` VALUES ('49','02931','Arnsberg');
INSERT INTO `area_codes` VALUES ('49','02932','Neheim-Hüsten');
INSERT INTO `area_codes` VALUES ('49','02933','Sundern Sauerland');
INSERT INTO `area_codes` VALUES ('49','02934','Sundern-Altenhellefeld');
INSERT INTO `area_codes` VALUES ('49','02935','Sundern-Hachen');
INSERT INTO `area_codes` VALUES ('49','02937','Arnsberg-Oeventrop');
INSERT INTO `area_codes` VALUES ('49','02938','Ense');
INSERT INTO `area_codes` VALUES ('49','02941','Lippstadt');
INSERT INTO `area_codes` VALUES ('49','02942','Geseke');
INSERT INTO `area_codes` VALUES ('49','02943','Erwitte');
INSERT INTO `area_codes` VALUES ('49','02944','Rietberg-Mastholte');
INSERT INTO `area_codes` VALUES ('49','02945','Lippstadt-Benninghausen');
INSERT INTO `area_codes` VALUES ('49','02947','Anröchte');
INSERT INTO `area_codes` VALUES ('49','02948','Lippstadt-Rebbeke');
INSERT INTO `area_codes` VALUES ('49','02951','Büren');
INSERT INTO `area_codes` VALUES ('49','02952','Rüthen');
INSERT INTO `area_codes` VALUES ('49','02953','Wünnenberg');
INSERT INTO `area_codes` VALUES ('49','02954','Rüthen-Oestereiden');
INSERT INTO `area_codes` VALUES ('49','02955','Büren-Wewelsburg');
INSERT INTO `area_codes` VALUES ('49','02957','Wünnenberg-Haaren');
INSERT INTO `area_codes` VALUES ('49','02958','Büren-Harth');
INSERT INTO `area_codes` VALUES ('49','02961','Brilon');
INSERT INTO `area_codes` VALUES ('49','02962','Olsberg');
INSERT INTO `area_codes` VALUES ('49','02963','Brilon-Messinghausen');
INSERT INTO `area_codes` VALUES ('49','02964','Brilon-Alme');
INSERT INTO `area_codes` VALUES ('49','02971','Schmallenberg-Dorlar');
INSERT INTO `area_codes` VALUES ('49','02972','Schmallenberg');
INSERT INTO `area_codes` VALUES ('49','02973','Eslohe Sauerland');
INSERT INTO `area_codes` VALUES ('49','02974','Schmallenberg-Fredeburg');
INSERT INTO `area_codes` VALUES ('49','02975','Schmallenberg-Oberkirchen');
INSERT INTO `area_codes` VALUES ('49','02977','Schmallenberg-Bödefeld');
INSERT INTO `area_codes` VALUES ('49','02981','Winterberg Westf');
INSERT INTO `area_codes` VALUES ('49','02982','Medebach');
INSERT INTO `area_codes` VALUES ('49','02983','Winterberg-Siedlinghausen');
INSERT INTO `area_codes` VALUES ('49','02984','Hallenberg');
INSERT INTO `area_codes` VALUES ('49','02985','Winterberg-Niedersfeld');
INSERT INTO `area_codes` VALUES ('49','02991','Marsberg-Bredelar');
INSERT INTO `area_codes` VALUES ('49','02992','Marsberg');
INSERT INTO `area_codes` VALUES ('49','02993','Marsberg-Canstein');
INSERT INTO `area_codes` VALUES ('49','02994','Marsberg-Westheim');
INSERT INTO `area_codes` VALUES ('49','030','Berlin');
INSERT INTO `area_codes` VALUES ('49','03301','Oranienburg');
INSERT INTO `area_codes` VALUES ('49','03302','Hennigsdorf');
INSERT INTO `area_codes` VALUES ('49','03303','Birkenwerder');
INSERT INTO `area_codes` VALUES ('49','03304','Velten');
INSERT INTO `area_codes` VALUES ('49','033051','Nassenheide');
INSERT INTO `area_codes` VALUES ('49','033053','Zehlendorf Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033054','Liebenwalde');
INSERT INTO `area_codes` VALUES ('49','033055','Kremmen');
INSERT INTO `area_codes` VALUES ('49','033056','Mühlenbeck Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','03306','Gransee');
INSERT INTO `area_codes` VALUES ('49','03307','Zehdenick');
INSERT INTO `area_codes` VALUES ('49','033080','Marienthal Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033082','Menz Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033083','Schulzendorf Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033084','Gutengermendorf');
INSERT INTO `area_codes` VALUES ('49','033085','Seilershof');
INSERT INTO `area_codes` VALUES ('49','033086','Grieben Kr Oberhavel');
INSERT INTO `area_codes` VALUES ('49','033087','Bredereiche');
INSERT INTO `area_codes` VALUES ('49','033088','Falkenthal');
INSERT INTO `area_codes` VALUES ('49','033089','Himmelpfort');
INSERT INTO `area_codes` VALUES ('49','033093','Fürstenberg Havel');
INSERT INTO `area_codes` VALUES ('49','033094','Löwenberg');
INSERT INTO `area_codes` VALUES ('49','0331','Potsdam');
INSERT INTO `area_codes` VALUES ('49','033200','Bergholz-Rehbrücke');
INSERT INTO `area_codes` VALUES ('49','033201','Gross Glienicke');
INSERT INTO `area_codes` VALUES ('49','033202','Töplitz');
INSERT INTO `area_codes` VALUES ('49','033203','Kleinmachnow');
INSERT INTO `area_codes` VALUES ('49','033204','Beelitz Mark');
INSERT INTO `area_codes` VALUES ('49','033205','Michendorf');
INSERT INTO `area_codes` VALUES ('49','033206','Fichtenwalde');
INSERT INTO `area_codes` VALUES ('49','033207','Gross Kreutz');
INSERT INTO `area_codes` VALUES ('49','033208','Fahrland');
INSERT INTO `area_codes` VALUES ('49','033209','Caputh');
INSERT INTO `area_codes` VALUES ('49','03321','Nauen Brandenb');
INSERT INTO `area_codes` VALUES ('49','03322','Falkensee');
INSERT INTO `area_codes` VALUES ('49','033230','Börnicke Kr Havelland');
INSERT INTO `area_codes` VALUES ('49','033231','Pausin');
INSERT INTO `area_codes` VALUES ('49','033232','Brieselang');
INSERT INTO `area_codes` VALUES ('49','033233','Ketzin');
INSERT INTO `area_codes` VALUES ('49','033234','Wustermark');
INSERT INTO `area_codes` VALUES ('49','033235','Friesack');
INSERT INTO `area_codes` VALUES ('49','033237','Paulinenaue');
INSERT INTO `area_codes` VALUES ('49','033238','Senzke');
INSERT INTO `area_codes` VALUES ('49','033239','Gross Behnitz');
INSERT INTO `area_codes` VALUES ('49','03327','Werder Havel');
INSERT INTO `area_codes` VALUES ('49','03328','Teltow');
INSERT INTO `area_codes` VALUES ('49','03329','Stahnsdorf');
INSERT INTO `area_codes` VALUES ('49','03331','Angermünde');
INSERT INTO `area_codes` VALUES ('49','03332','Schwedt/Oder');
INSERT INTO `area_codes` VALUES ('49','033331','Casekow');
INSERT INTO `area_codes` VALUES ('49','033332','Gartz Oder');
INSERT INTO `area_codes` VALUES ('49','033333','Tantow');
INSERT INTO `area_codes` VALUES ('49','033334','Greiffenberg');
INSERT INTO `area_codes` VALUES ('49','033335','Pinnow Kr Uckermark');
INSERT INTO `area_codes` VALUES ('49','033336','Passow Kr Uckermark');
INSERT INTO `area_codes` VALUES ('49','033337','Altkünkendorf');
INSERT INTO `area_codes` VALUES ('49','033338','Stolpe/Oder');
INSERT INTO `area_codes` VALUES ('49','03334','Eberswalde');
INSERT INTO `area_codes` VALUES ('49','03335','Finowfurt');
INSERT INTO `area_codes` VALUES ('49','033361','Joachimsthal');
INSERT INTO `area_codes` VALUES ('49','033362','Liepe Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033363','Altenhof Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033364','Gross Ziethen Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033365','Lüdersdorf Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033366','Chorin');
INSERT INTO `area_codes` VALUES ('49','033367','Friedrichswalde Brandenb');
INSERT INTO `area_codes` VALUES ('49','033368','Hohensaaten');
INSERT INTO `area_codes` VALUES ('49','033369','Oderberg');
INSERT INTO `area_codes` VALUES ('49','03337','Biesenthal Brandenb');
INSERT INTO `area_codes` VALUES ('49','03338','Bernau Brandenb');
INSERT INTO `area_codes` VALUES ('49','033393','Gross Schönebeck Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033394','Blumberg Kr Barnim');
INSERT INTO `area_codes` VALUES ('49','033395','Zerpenschleuse');
INSERT INTO `area_codes` VALUES ('49','033396','Klosterfelde');
INSERT INTO `area_codes` VALUES ('49','033397','Wandlitz');
INSERT INTO `area_codes` VALUES ('49','033398','Werneuchen');
INSERT INTO `area_codes` VALUES ('49','03341','Strausberg');
INSERT INTO `area_codes` VALUES ('49','03342','Neuenhagen b Berlin');
INSERT INTO `area_codes` VALUES ('49','033432','Müncheberg');
INSERT INTO `area_codes` VALUES ('49','033433','Buckow Märk Schweiz');
INSERT INTO `area_codes` VALUES ('49','033434','Herzfelde b Strausberg');
INSERT INTO `area_codes` VALUES ('49','033435','Rehfelde');
INSERT INTO `area_codes` VALUES ('49','033436','Prötzel');
INSERT INTO `area_codes` VALUES ('49','033437','Reichenberg b Strausberg');
INSERT INTO `area_codes` VALUES ('49','033438','Altlandsberg');
INSERT INTO `area_codes` VALUES ('49','033439','Fredersdorf-Vogelsdorf');
INSERT INTO `area_codes` VALUES ('49','03344','Bad Freienwalde');
INSERT INTO `area_codes` VALUES ('49','033451','Heckelberg');
INSERT INTO `area_codes` VALUES ('49','033452','Neulewin');
INSERT INTO `area_codes` VALUES ('49','033454','Wölsickendorf/Wollenberg');
INSERT INTO `area_codes` VALUES ('49','033456','Wriezen');
INSERT INTO `area_codes` VALUES ('49','033457','Altreetz');
INSERT INTO `area_codes` VALUES ('49','033458','Falkenberg Mark');
INSERT INTO `area_codes` VALUES ('49','03346','Seelow');
INSERT INTO `area_codes` VALUES ('49','033470','Lietzen');
INSERT INTO `area_codes` VALUES ('49','033472','Golzow b Seelow');
INSERT INTO `area_codes` VALUES ('49','033473','Zechin');
INSERT INTO `area_codes` VALUES ('49','033474','Neutrebbin');
INSERT INTO `area_codes` VALUES ('49','033475','Letschin');
INSERT INTO `area_codes` VALUES ('49','033476','Neuhardenberg');
INSERT INTO `area_codes` VALUES ('49','033477','Trebnitz b Müncheberg');
INSERT INTO `area_codes` VALUES ('49','033478','Gross Neuendorf');
INSERT INTO `area_codes` VALUES ('49','033479','Küstrin-Kietz');
INSERT INTO `area_codes` VALUES ('49','0335','Frankfurt (Oder)');
INSERT INTO `area_codes` VALUES ('49','033601','Podelzig');
INSERT INTO `area_codes` VALUES ('49','033602','Alt Zeschdorf');
INSERT INTO `area_codes` VALUES ('49','033603','Falkenhagen b Seelow');
INSERT INTO `area_codes` VALUES ('49','033604','Lebus');
INSERT INTO `area_codes` VALUES ('49','033605','Boossen');
INSERT INTO `area_codes` VALUES ('49','033606','Müllrose');
INSERT INTO `area_codes` VALUES ('49','033607','Briesen Mark');
INSERT INTO `area_codes` VALUES ('49','033608','Jacobsdorf Mark');
INSERT INTO `area_codes` VALUES ('49','033609','Brieskow-Finkenheerd');
INSERT INTO `area_codes` VALUES ('49','03361','Fürstenwalde Spree');
INSERT INTO `area_codes` VALUES ('49','03362','Erkner');
INSERT INTO `area_codes` VALUES ('49','033631','Bad Saarow-Pieskow');
INSERT INTO `area_codes` VALUES ('49','033632','Hangelsberg');
INSERT INTO `area_codes` VALUES ('49','033633','Spreenhagen');
INSERT INTO `area_codes` VALUES ('49','033634','Berkenbrück Kr Oder-Spree');
INSERT INTO `area_codes` VALUES ('49','033635','Arensdorf Kr Oder-Spree');
INSERT INTO `area_codes` VALUES ('49','033636','Steinhöfel Kr Oder-Spree');
INSERT INTO `area_codes` VALUES ('49','033637','Beerfelde');
INSERT INTO `area_codes` VALUES ('49','033638','Rüdersdorf b Berlin');
INSERT INTO `area_codes` VALUES ('49','03364','Eisenhüttenstadt');
INSERT INTO `area_codes` VALUES ('49','033652','Neuzelle');
INSERT INTO `area_codes` VALUES ('49','033653','Ziltendorf');
INSERT INTO `area_codes` VALUES ('49','033654','Fünfeichen');
INSERT INTO `area_codes` VALUES ('49','033655','Grunow Kr Oder-Spree');
INSERT INTO `area_codes` VALUES ('49','033656','Bahro');
INSERT INTO `area_codes` VALUES ('49','033657','Steinsdorf Brandenb');
INSERT INTO `area_codes` VALUES ('49','03366','Beeskow');
INSERT INTO `area_codes` VALUES ('49','033671','Lieberose');
INSERT INTO `area_codes` VALUES ('49','033672','Pfaffendorf b Beeskow');
INSERT INTO `area_codes` VALUES ('49','033673','Weichensdorf');
INSERT INTO `area_codes` VALUES ('49','033674','Trebatsch');
INSERT INTO `area_codes` VALUES ('49','033675','Tauche');
INSERT INTO `area_codes` VALUES ('49','033676','Friedland b Beeskow');
INSERT INTO `area_codes` VALUES ('49','033677','Glienicke b Beeskow');
INSERT INTO `area_codes` VALUES ('49','033678','Storkow Mark');
INSERT INTO `area_codes` VALUES ('49','033679','Wendisch Rietz');
INSERT INTO `area_codes` VALUES ('49','033701','Grossbeeren');
INSERT INTO `area_codes` VALUES ('49','033702','Wünsdorf');
INSERT INTO `area_codes` VALUES ('49','033703','Sperenberg');
INSERT INTO `area_codes` VALUES ('49','033704','Baruth Mark');
INSERT INTO `area_codes` VALUES ('49','033708','Rangsdorf');
INSERT INTO `area_codes` VALUES ('49','03371','Luckenwalde');
INSERT INTO `area_codes` VALUES ('49','03372','Jüterbog');
INSERT INTO `area_codes` VALUES ('49','033731','Trebbin');
INSERT INTO `area_codes` VALUES ('49','033732','Hennickendorf b Luckenwalde');
INSERT INTO `area_codes` VALUES ('49','033733','Stülpe');
INSERT INTO `area_codes` VALUES ('49','033734','Felgentreu');
INSERT INTO `area_codes` VALUES ('49','033741','Niedergörsdorf');
INSERT INTO `area_codes` VALUES ('49','033742','Oehna Brandenb');
INSERT INTO `area_codes` VALUES ('49','033743','Blönsdorf');
INSERT INTO `area_codes` VALUES ('49','033744','Hohenseefeld');
INSERT INTO `area_codes` VALUES ('49','033745','Petkus');
INSERT INTO `area_codes` VALUES ('49','033746','Werbig b Jüterbog');
INSERT INTO `area_codes` VALUES ('49','033747','Marzahna');
INSERT INTO `area_codes` VALUES ('49','033748','Treuenbrietzen');
INSERT INTO `area_codes` VALUES ('49','03375','Königs Wusterhausen');
INSERT INTO `area_codes` VALUES ('49','033760','Münchehofe Kr Dahme-Spreewald');
INSERT INTO `area_codes` VALUES ('49','033762','Zeuthen');
INSERT INTO `area_codes` VALUES ('49','033763','Bestensee');
INSERT INTO `area_codes` VALUES ('49','033764','Mittenwalde Mark');
INSERT INTO `area_codes` VALUES ('49','033765','Märkisch Buchholz');
INSERT INTO `area_codes` VALUES ('49','033766','Teupitz');
INSERT INTO `area_codes` VALUES ('49','033767','Friedersdorf b Berlin');
INSERT INTO `area_codes` VALUES ('49','033768','Prieros');
INSERT INTO `area_codes` VALUES ('49','033769','Töpchin');
INSERT INTO `area_codes` VALUES ('49','03377','Zossen Brandenb');
INSERT INTO `area_codes` VALUES ('49','03378','Ludwigsfelde');
INSERT INTO `area_codes` VALUES ('49','03379','Mahlow');
INSERT INTO `area_codes` VALUES ('49','03381','Brandenburg an der Havel');
INSERT INTO `area_codes` VALUES ('49','03382','Lehnin');
INSERT INTO `area_codes` VALUES ('49','033830','Ziesar');
INSERT INTO `area_codes` VALUES ('49','033831','Weseram');
INSERT INTO `area_codes` VALUES ('49','033832','Rogäsen');
INSERT INTO `area_codes` VALUES ('49','033833','Wollin b Brandenburg');
INSERT INTO `area_codes` VALUES ('49','033834','Pritzerbe');
INSERT INTO `area_codes` VALUES ('49','033835','Golzow b Brandenburg');
INSERT INTO `area_codes` VALUES ('49','033836','Butzow b Brandenburg');
INSERT INTO `area_codes` VALUES ('49','033837','Brielow');
INSERT INTO `area_codes` VALUES ('49','033838','Päwesin');
INSERT INTO `area_codes` VALUES ('49','033839','Wusterwitz');
INSERT INTO `area_codes` VALUES ('49','033841','Belzig');
INSERT INTO `area_codes` VALUES ('49','033843','Niemegk');
INSERT INTO `area_codes` VALUES ('49','033844','Brück Brandenb');
INSERT INTO `area_codes` VALUES ('49','033845','Borkheide');
INSERT INTO `area_codes` VALUES ('49','033846','Dippmannsdorf');
INSERT INTO `area_codes` VALUES ('49','033847','Görzke');
INSERT INTO `area_codes` VALUES ('49','033848','Raben');
INSERT INTO `area_codes` VALUES ('49','033849','Wiesenburg Mark');
INSERT INTO `area_codes` VALUES ('49','03385','Rathenow');
INSERT INTO `area_codes` VALUES ('49','03386','Premnitz');
INSERT INTO `area_codes` VALUES ('49','033870','Zollchow b Rathenow');
INSERT INTO `area_codes` VALUES ('49','033872','Hohennauen');
INSERT INTO `area_codes` VALUES ('49','033873','Grosswudicke');
INSERT INTO `area_codes` VALUES ('49','033874','Stechow Brandenb');
INSERT INTO `area_codes` VALUES ('49','033875','Rhinow');
INSERT INTO `area_codes` VALUES ('49','033876','Buschow');
INSERT INTO `area_codes` VALUES ('49','033877','Nitzahn');
INSERT INTO `area_codes` VALUES ('49','033878','Nennhausen');
INSERT INTO `area_codes` VALUES ('49','03391','Neuruppin');
INSERT INTO `area_codes` VALUES ('49','033920','Walsleben b Neuruppin');
INSERT INTO `area_codes` VALUES ('49','033921','Zechlinerhütte');
INSERT INTO `area_codes` VALUES ('49','033922','Karwesee');
INSERT INTO `area_codes` VALUES ('49','033923','Flecken Zechlin');
INSERT INTO `area_codes` VALUES ('49','033924','Rägelin');
INSERT INTO `area_codes` VALUES ('49','033925','Wustrau-Altfriesack');
INSERT INTO `area_codes` VALUES ('49','033926','Herzberg Mark');
INSERT INTO `area_codes` VALUES ('49','033928','Wildberg Brandenb');
INSERT INTO `area_codes` VALUES ('49','033929','Gühlen-Glienicke');
INSERT INTO `area_codes` VALUES ('49','033931','Rheinsberg Mark');
INSERT INTO `area_codes` VALUES ('49','033932','Fehrbellin');
INSERT INTO `area_codes` VALUES ('49','033933','Lindow Mark');
INSERT INTO `area_codes` VALUES ('49','03394','Wittstock Dosse');
INSERT INTO `area_codes` VALUES ('49','03395','Pritzwalk');
INSERT INTO `area_codes` VALUES ('49','033962','Heiligengrabe');
INSERT INTO `area_codes` VALUES ('49','033963','Wulfersdorf b Wittstock');
INSERT INTO `area_codes` VALUES ('49','033964','Fretzdorf');
INSERT INTO `area_codes` VALUES ('49','033965','Herzsprung b Wittstock');
INSERT INTO `area_codes` VALUES ('49','033966','Dranse');
INSERT INTO `area_codes` VALUES ('49','033967','Freyenstein');
INSERT INTO `area_codes` VALUES ('49','033968','Meyenburg Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033969','Stepenitz');
INSERT INTO `area_codes` VALUES ('49','033970','Neustadt Dosse');
INSERT INTO `area_codes` VALUES ('49','033971','Kyritz Brandenb');
INSERT INTO `area_codes` VALUES ('49','033972','Breddin');
INSERT INTO `area_codes` VALUES ('49','033973','Zernitz b Neustadt Dosse');
INSERT INTO `area_codes` VALUES ('49','033974','Dessow');
INSERT INTO `area_codes` VALUES ('49','033975','Dannenwalde Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033976','Wutike');
INSERT INTO `area_codes` VALUES ('49','033977','Gumtow');
INSERT INTO `area_codes` VALUES ('49','033978','Segeletz');
INSERT INTO `area_codes` VALUES ('49','033979','Wusterhausen Dosse');
INSERT INTO `area_codes` VALUES ('49','033981','Putlitz');
INSERT INTO `area_codes` VALUES ('49','033982','Hoppenrade Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033983','Gross Pankow Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033984','Blumenthal b Pritzwalk');
INSERT INTO `area_codes` VALUES ('49','033986','Falkenhagen Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','033989','Sadenbeck');
INSERT INTO `area_codes` VALUES ('49','0340','Dessau Anh');
INSERT INTO `area_codes` VALUES ('49','0341','Leipzig');
INSERT INTO `area_codes` VALUES ('49','034202','Delitzsch');
INSERT INTO `area_codes` VALUES ('49','034203','Zwenkau');
INSERT INTO `area_codes` VALUES ('49','034204','Schkeuditz');
INSERT INTO `area_codes` VALUES ('49','034205','Markranstädt');
INSERT INTO `area_codes` VALUES ('49','034206','Rötha');
INSERT INTO `area_codes` VALUES ('49','034207','Zwochau');
INSERT INTO `area_codes` VALUES ('49','034208','Löbnitz b Delitzsch');
INSERT INTO `area_codes` VALUES ('49','03421','Torgau');
INSERT INTO `area_codes` VALUES ('49','034221','Schildau Gneisenaustadt');
INSERT INTO `area_codes` VALUES ('49','034222','Arzberg b Torgau');
INSERT INTO `area_codes` VALUES ('49','034223','Dommitzsch');
INSERT INTO `area_codes` VALUES ('49','034224','Belgern Sachs');
INSERT INTO `area_codes` VALUES ('49','03423','Eilenburg');
INSERT INTO `area_codes` VALUES ('49','034241','Jesewitz');
INSERT INTO `area_codes` VALUES ('49','034242','Hohenpriessnitz');
INSERT INTO `area_codes` VALUES ('49','034243','Bad Düben');
INSERT INTO `area_codes` VALUES ('49','034244','Mockrehna');
INSERT INTO `area_codes` VALUES ('49','03425','Wurzen');
INSERT INTO `area_codes` VALUES ('49','034261','Kühren b Wurzen');
INSERT INTO `area_codes` VALUES ('49','034262','Falkenhain b Wurzen');
INSERT INTO `area_codes` VALUES ('49','034263','Hohburg');
INSERT INTO `area_codes` VALUES ('49','034291','Borsdorf');
INSERT INTO `area_codes` VALUES ('49','034292','Brandis b Wurzen');
INSERT INTO `area_codes` VALUES ('49','034293','Naunhof b Grimma');
INSERT INTO `area_codes` VALUES ('49','034294','Rackwitz');
INSERT INTO `area_codes` VALUES ('49','034295','Krensitz');
INSERT INTO `area_codes` VALUES ('49','034296','Groitzsch b Pegau');
INSERT INTO `area_codes` VALUES ('49','034297','Liebertwolkwitz');
INSERT INTO `area_codes` VALUES ('49','034298','Taucha b Leipzig');
INSERT INTO `area_codes` VALUES ('49','034299','Gaschwitz');
INSERT INTO `area_codes` VALUES ('49','03431','Döbeln');
INSERT INTO `area_codes` VALUES ('49','034321','Leisnig');
INSERT INTO `area_codes` VALUES ('49','034322','Rosswein');
INSERT INTO `area_codes` VALUES ('49','034324','Ostrau Sachs');
INSERT INTO `area_codes` VALUES ('49','034325','Mochau-Lüttewitz');
INSERT INTO `area_codes` VALUES ('49','034327','Waldheim Sachs');
INSERT INTO `area_codes` VALUES ('49','034328','Hartha b Döbeln');
INSERT INTO `area_codes` VALUES ('49','03433','Borna Stadt');
INSERT INTO `area_codes` VALUES ('49','034341','Geithain');
INSERT INTO `area_codes` VALUES ('49','034342','Neukieritzsch');
INSERT INTO `area_codes` VALUES ('49','034343','Regis-Breitingen');
INSERT INTO `area_codes` VALUES ('49','034344','Kohren-Sahlis');
INSERT INTO `area_codes` VALUES ('49','034345','Bad Lausick');
INSERT INTO `area_codes` VALUES ('49','034346','Narsdorf');
INSERT INTO `area_codes` VALUES ('49','034347','Oelzschau  b Borna');
INSERT INTO `area_codes` VALUES ('49','034348','Frohburg');
INSERT INTO `area_codes` VALUES ('49','03435','Oschatz');
INSERT INTO `area_codes` VALUES ('49','034361','Dahlen Sachs');
INSERT INTO `area_codes` VALUES ('49','034362','Mügeln b Oschatz');
INSERT INTO `area_codes` VALUES ('49','034363','Cavertitz');
INSERT INTO `area_codes` VALUES ('49','034364','Wermsdorf');
INSERT INTO `area_codes` VALUES ('49','03437','Grimma');
INSERT INTO `area_codes` VALUES ('49','034381','Colditz');
INSERT INTO `area_codes` VALUES ('49','034382','Nerchau');
INSERT INTO `area_codes` VALUES ('49','034383','Trebsen Mulde');
INSERT INTO `area_codes` VALUES ('49','034384','Grossbothen');
INSERT INTO `area_codes` VALUES ('49','034385','Mutzschen');
INSERT INTO `area_codes` VALUES ('49','034386','Dürrweitzschen b Grimma');
INSERT INTO `area_codes` VALUES ('49','03441','Zeitz');
INSERT INTO `area_codes` VALUES ('49','034422','Osterfeld');
INSERT INTO `area_codes` VALUES ('49','034423','Heuckewalde');
INSERT INTO `area_codes` VALUES ('49','034424','Reuden b Zeitz');
INSERT INTO `area_codes` VALUES ('49','034425','Droyssig');
INSERT INTO `area_codes` VALUES ('49','034426','Kayna');
INSERT INTO `area_codes` VALUES ('49','03443','Weissenfels Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034441','Hohenmölsen');
INSERT INTO `area_codes` VALUES ('49','034443','Teuchern');
INSERT INTO `area_codes` VALUES ('49','034444','Lützen');
INSERT INTO `area_codes` VALUES ('49','034445','Stößen');
INSERT INTO `area_codes` VALUES ('49','034446','Grosskorbetha');
INSERT INTO `area_codes` VALUES ('49','03445','Naumburg Saale');
INSERT INTO `area_codes` VALUES ('49','034461','Nebra Unstrut');
INSERT INTO `area_codes` VALUES ('49','034462','Laucha Unstrut');
INSERT INTO `area_codes` VALUES ('49','034463','Bad Kösen');
INSERT INTO `area_codes` VALUES ('49','034464','Freyburg Unstrut');
INSERT INTO `area_codes` VALUES ('49','034465','Bad Bibra');
INSERT INTO `area_codes` VALUES ('49','034466','Janisroda');
INSERT INTO `area_codes` VALUES ('49','034467','Eckartsberga');
INSERT INTO `area_codes` VALUES ('49','03447','Altenburg Thür');
INSERT INTO `area_codes` VALUES ('49','03448','Meuselwitz Thür');
INSERT INTO `area_codes` VALUES ('49','034491','Schmölln Thür');
INSERT INTO `area_codes` VALUES ('49','034492','Lucka');
INSERT INTO `area_codes` VALUES ('49','034493','Gößnitz Thür');
INSERT INTO `area_codes` VALUES ('49','034494','Ehrenhain');
INSERT INTO `area_codes` VALUES ('49','034495','Dobitschen');
INSERT INTO `area_codes` VALUES ('49','034496','Nöbdenitz');
INSERT INTO `area_codes` VALUES ('49','034497','Langenleuba-Niederhain');
INSERT INTO `area_codes` VALUES ('49','034498','Rositz');
INSERT INTO `area_codes` VALUES ('49','0345','Halle Saale');
INSERT INTO `area_codes` VALUES ('49','034600','Ostrau Saalkreis');
INSERT INTO `area_codes` VALUES ('49','034601','Teutschenthal');
INSERT INTO `area_codes` VALUES ('49','034602','Landsberg Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034603','Nauendorf Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034604','Niemberg');
INSERT INTO `area_codes` VALUES ('49','034605','Gröbers');
INSERT INTO `area_codes` VALUES ('49','034606','Teicha Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034607','Wettin');
INSERT INTO `area_codes` VALUES ('49','034609','Salzmünde');
INSERT INTO `area_codes` VALUES ('49','03461','Merseburg Saale');
INSERT INTO `area_codes` VALUES ('49','03462','Bad Dürrenberg');
INSERT INTO `area_codes` VALUES ('49','034632','Mücheln Geiseltal');
INSERT INTO `area_codes` VALUES ('49','034633','Braunsbedra');
INSERT INTO `area_codes` VALUES ('49','034635','Bad Lauchstädt');
INSERT INTO `area_codes` VALUES ('49','034636','Schafstädt');
INSERT INTO `area_codes` VALUES ('49','034637','Frankleben');
INSERT INTO `area_codes` VALUES ('49','034638','Zöschen');
INSERT INTO `area_codes` VALUES ('49','034639','Wallendorf Luppe');
INSERT INTO `area_codes` VALUES ('49','03464','Sangerhausen');
INSERT INTO `area_codes` VALUES ('49','034651','Rossla');
INSERT INTO `area_codes` VALUES ('49','034652','Allstedt');
INSERT INTO `area_codes` VALUES ('49','034653','Rottleberode');
INSERT INTO `area_codes` VALUES ('49','034654','Stolberg Harz');
INSERT INTO `area_codes` VALUES ('49','034656','Wallhausen Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034658','Hayn Harz');
INSERT INTO `area_codes` VALUES ('49','034659','Blankenheim b Sangerhausen');
INSERT INTO `area_codes` VALUES ('49','03466','Artern Unstrut');
INSERT INTO `area_codes` VALUES ('49','034671','Bad Frankenhausen Kyffhäuser');
INSERT INTO `area_codes` VALUES ('49','034672','Rossleben');
INSERT INTO `area_codes` VALUES ('49','034673','Heldrungen');
INSERT INTO `area_codes` VALUES ('49','034691','Könnern');
INSERT INTO `area_codes` VALUES ('49','034692','Alsleben Saale');
INSERT INTO `area_codes` VALUES ('49','03471','Bernburg Saale');
INSERT INTO `area_codes` VALUES ('49','034721','Nienburg Saale');
INSERT INTO `area_codes` VALUES ('49','034722','Preusslitz');
INSERT INTO `area_codes` VALUES ('49','03473','Aschersleben Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034741','Frose');
INSERT INTO `area_codes` VALUES ('49','034742','Sylda');
INSERT INTO `area_codes` VALUES ('49','034743','Ermsleben');
INSERT INTO `area_codes` VALUES ('49','034745','Winningen Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034746','Giersleben');
INSERT INTO `area_codes` VALUES ('49','03475','Lutherstadt Eisleben');
INSERT INTO `area_codes` VALUES ('49','03476','Hettstedt Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','034771','Querfurt');
INSERT INTO `area_codes` VALUES ('49','034772','Helbra');
INSERT INTO `area_codes` VALUES ('49','034773','Schwittersdorf');
INSERT INTO `area_codes` VALUES ('49','034774','Röblingen am See');
INSERT INTO `area_codes` VALUES ('49','034775','Wippra');
INSERT INTO `area_codes` VALUES ('49','034776','Rothenschirmbach');
INSERT INTO `area_codes` VALUES ('49','034779','Abberode');
INSERT INTO `area_codes` VALUES ('49','034781','Greifenhagen');
INSERT INTO `area_codes` VALUES ('49','034782','Mansfeld Südharz');
INSERT INTO `area_codes` VALUES ('49','034783','Gerbstedt');
INSERT INTO `area_codes` VALUES ('49','034785','Sandersleben');
INSERT INTO `area_codes` VALUES ('49','034901','Roßlau Elbe');
INSERT INTO `area_codes` VALUES ('49','034903','Coswig Anhalt');
INSERT INTO `area_codes` VALUES ('49','034904','Oranienbaum');
INSERT INTO `area_codes` VALUES ('49','034905','Wörlitz');
INSERT INTO `area_codes` VALUES ('49','034906','Raguhn');
INSERT INTO `area_codes` VALUES ('49','034907','Jeber-Bergfrieden');
INSERT INTO `area_codes` VALUES ('49','034909','Aken Elbe');
INSERT INTO `area_codes` VALUES ('49','03491','Lutherstadt Wittenberg');
INSERT INTO `area_codes` VALUES ('49','034920','Kropstädt');
INSERT INTO `area_codes` VALUES ('49','034921','Kemberg');
INSERT INTO `area_codes` VALUES ('49','034922','Mühlanger');
INSERT INTO `area_codes` VALUES ('49','034923','Cobbelsdorf');
INSERT INTO `area_codes` VALUES ('49','034924','Zahna');
INSERT INTO `area_codes` VALUES ('49','034925','Bad Schmiedeberg');
INSERT INTO `area_codes` VALUES ('49','034926','Pretzsch Elbe');
INSERT INTO `area_codes` VALUES ('49','034927','Globig-Bleddin');
INSERT INTO `area_codes` VALUES ('49','034928','Seegrehna');
INSERT INTO `area_codes` VALUES ('49','034929','Straach');
INSERT INTO `area_codes` VALUES ('49','03493','Bitterfeld');
INSERT INTO `area_codes` VALUES ('49','03494','Wolfen');
INSERT INTO `area_codes` VALUES ('49','034953','Gräfenhainichen');
INSERT INTO `area_codes` VALUES ('49','034954','Roitzsch b Bitterfeld');
INSERT INTO `area_codes` VALUES ('49','034955','Gossa');
INSERT INTO `area_codes` VALUES ('49','034956','Zörbig');
INSERT INTO `area_codes` VALUES ('49','03496','Köthen Anhalt');
INSERT INTO `area_codes` VALUES ('49','034973','Osternienburg');
INSERT INTO `area_codes` VALUES ('49','034975','Görzig Kr Köthen');
INSERT INTO `area_codes` VALUES ('49','034976','Gröbzig');
INSERT INTO `area_codes` VALUES ('49','034977','Quellendorf');
INSERT INTO `area_codes` VALUES ('49','034978','Radegast Kr Köthen');
INSERT INTO `area_codes` VALUES ('49','034979','Wulfen Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','03501','Pirna');
INSERT INTO `area_codes` VALUES ('49','035020','Struppen');
INSERT INTO `area_codes` VALUES ('49','035021','Königstein Sächs Schweiz');
INSERT INTO `area_codes` VALUES ('49','035022','Bad Schandau');
INSERT INTO `area_codes` VALUES ('49','035023','Bad Gottleuba');
INSERT INTO `area_codes` VALUES ('49','035024','Stadt Wehlen');
INSERT INTO `area_codes` VALUES ('49','035025','Liebstadt');
INSERT INTO `area_codes` VALUES ('49','035026','Dürrröhrsdorf-Dittersbach');
INSERT INTO `area_codes` VALUES ('49','035027','Weesenstein');
INSERT INTO `area_codes` VALUES ('49','035028','Krippen');
INSERT INTO `area_codes` VALUES ('49','035032','Langenhennersdorf');
INSERT INTO `area_codes` VALUES ('49','035033','Rosenthal Sächs Schweiz');
INSERT INTO `area_codes` VALUES ('49','03504','Dippoldiswalde');
INSERT INTO `area_codes` VALUES ('49','035052','Kipsdorf Kurort');
INSERT INTO `area_codes` VALUES ('49','035053','Glashütte Sachs');
INSERT INTO `area_codes` VALUES ('49','035054','Lauenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','035055','Höckendorf b Dippoldiswalde');
INSERT INTO `area_codes` VALUES ('49','035056','Altenberg Sachs');
INSERT INTO `area_codes` VALUES ('49','035057','Hermsdorf Erzgeb');
INSERT INTO `area_codes` VALUES ('49','035058','Pretzschendorf');
INSERT INTO `area_codes` VALUES ('49','0351','Dresden');
INSERT INTO `area_codes` VALUES ('49','035200','Arnsdorf b Dresden');
INSERT INTO `area_codes` VALUES ('49','035201','Langebrück');
INSERT INTO `area_codes` VALUES ('49','035202','Klingenberg Sachs');
INSERT INTO `area_codes` VALUES ('49','035203','Tharandt');
INSERT INTO `area_codes` VALUES ('49','035204','Wilsdruff');
INSERT INTO `area_codes` VALUES ('49','035205','Ottendorf-Okrilla');
INSERT INTO `area_codes` VALUES ('49','035206','Kreischa b Dresden');
INSERT INTO `area_codes` VALUES ('49','035207','Moritzburg');
INSERT INTO `area_codes` VALUES ('49','035208','Radeburg');
INSERT INTO `area_codes` VALUES ('49','035209','Mohorn');
INSERT INTO `area_codes` VALUES ('49','03521','Meissen');
INSERT INTO `area_codes` VALUES ('49','03522','Grossenhain  Sachs');
INSERT INTO `area_codes` VALUES ('49','03523','Coswig  b Dresden');
INSERT INTO `area_codes` VALUES ('49','035240','Tauscha b Großenhain');
INSERT INTO `area_codes` VALUES ('49','035241','Lommatzsch');
INSERT INTO `area_codes` VALUES ('49','035242','Nossen');
INSERT INTO `area_codes` VALUES ('49','035243','Weinböhla');
INSERT INTO `area_codes` VALUES ('49','035244','Krögis');
INSERT INTO `area_codes` VALUES ('49','035245','Burkhardswalde-Munzig');
INSERT INTO `area_codes` VALUES ('49','035246','Ziegenhain Sachs');
INSERT INTO `area_codes` VALUES ('49','035247','Zehren Sachs');
INSERT INTO `area_codes` VALUES ('49','035248','Schönfeld b Großenhain');
INSERT INTO `area_codes` VALUES ('49','035249','Basslitz');
INSERT INTO `area_codes` VALUES ('49','03525','Riesa');
INSERT INTO `area_codes` VALUES ('49','035263','Gröditz b Riesa');
INSERT INTO `area_codes` VALUES ('49','035264','Strehla');
INSERT INTO `area_codes` VALUES ('49','035265','Glaubitz');
INSERT INTO `area_codes` VALUES ('49','035266','Heyda b Riesa');
INSERT INTO `area_codes` VALUES ('49','035267','Diesbar-Seusslitz');
INSERT INTO `area_codes` VALUES ('49','035268','Stauchitz');
INSERT INTO `area_codes` VALUES ('49','03528','Radeberg');
INSERT INTO `area_codes` VALUES ('49','03529','Heidenau Sachs');
INSERT INTO `area_codes` VALUES ('49','03531','Finsterwalde');
INSERT INTO `area_codes` VALUES ('49','035322','Doberlug-Kirchhain');
INSERT INTO `area_codes` VALUES ('49','035323','Sonnewalde');
INSERT INTO `area_codes` VALUES ('49','035324','Crinitz');
INSERT INTO `area_codes` VALUES ('49','035325','Rückersdorf b Finsterwalde');
INSERT INTO `area_codes` VALUES ('49','035326','Schönborn Kr Elbe-Elster');
INSERT INTO `area_codes` VALUES ('49','035327','Priessen');
INSERT INTO `area_codes` VALUES ('49','035329','Dollenchen');
INSERT INTO `area_codes` VALUES ('49','03533','Elsterwerda');
INSERT INTO `area_codes` VALUES ('49','035341','Bad Liebenwerda');
INSERT INTO `area_codes` VALUES ('49','035342','Mühlberg Elbe');
INSERT INTO `area_codes` VALUES ('49','035343','Hirschfeld b Elsterwerda');
INSERT INTO `area_codes` VALUES ('49','03535','Herzberg Elster');
INSERT INTO `area_codes` VALUES ('49','035361','Schlieben');
INSERT INTO `area_codes` VALUES ('49','035362','Schönewalde b Herzberg');
INSERT INTO `area_codes` VALUES ('49','035363','Fermerswalde');
INSERT INTO `area_codes` VALUES ('49','035364','Lebusa');
INSERT INTO `area_codes` VALUES ('49','035365','Falkenberg Elster');
INSERT INTO `area_codes` VALUES ('49','03537','Jessen Elster');
INSERT INTO `area_codes` VALUES ('49','035383','Elster Elbe');
INSERT INTO `area_codes` VALUES ('49','035384','Steinsdorf b Jessen');
INSERT INTO `area_codes` VALUES ('49','035385','Annaburg');
INSERT INTO `area_codes` VALUES ('49','035386','Prettin');
INSERT INTO `area_codes` VALUES ('49','035387','Seyda');
INSERT INTO `area_codes` VALUES ('49','035388','Klöden');
INSERT INTO `area_codes` VALUES ('49','035389','Holzdorf Elster');
INSERT INTO `area_codes` VALUES ('49','03541','Calau');
INSERT INTO `area_codes` VALUES ('49','03542','Lübbenau Spreewald');
INSERT INTO `area_codes` VALUES ('49','035433','Vetschau');
INSERT INTO `area_codes` VALUES ('49','035434','Altdöbern');
INSERT INTO `area_codes` VALUES ('49','035435','Gollmitz b Calau');
INSERT INTO `area_codes` VALUES ('49','035436','Laasow b Calau');
INSERT INTO `area_codes` VALUES ('49','035439','Zinnitz');
INSERT INTO `area_codes` VALUES ('49','03544','Luckau Brandenb');
INSERT INTO `area_codes` VALUES ('49','035451','Dahme Brandenb');
INSERT INTO `area_codes` VALUES ('49','035452','Golssen');
INSERT INTO `area_codes` VALUES ('49','035453','Drahnsdorf');
INSERT INTO `area_codes` VALUES ('49','035454','Uckro');
INSERT INTO `area_codes` VALUES ('49','035455','Walddrehna');
INSERT INTO `area_codes` VALUES ('49','035456','Terpt');
INSERT INTO `area_codes` VALUES ('49','03546','Lübben Spreewald');
INSERT INTO `area_codes` VALUES ('49','035471','Birkenhainchen');
INSERT INTO `area_codes` VALUES ('49','035472','Schlepzig');
INSERT INTO `area_codes` VALUES ('49','035473','Neu Lübbenau');
INSERT INTO `area_codes` VALUES ('49','035474','Schönwalde b Lübben');
INSERT INTO `area_codes` VALUES ('49','035475','Straupitz');
INSERT INTO `area_codes` VALUES ('49','035476','Wittmannsdorf-Bückchen');
INSERT INTO `area_codes` VALUES ('49','035477','Rietzneuendorf-Friedrichshof');
INSERT INTO `area_codes` VALUES ('49','035478','Goyatz');
INSERT INTO `area_codes` VALUES ('49','0355','Cottbus');
INSERT INTO `area_codes` VALUES ('49','035600','Döbern NL');
INSERT INTO `area_codes` VALUES ('49','035601','Peitz');
INSERT INTO `area_codes` VALUES ('49','035602','Drebkau');
INSERT INTO `area_codes` VALUES ('49','035603','Burg Spreewald');
INSERT INTO `area_codes` VALUES ('49','035604','Krieschow');
INSERT INTO `area_codes` VALUES ('49','035605','Komptendorf');
INSERT INTO `area_codes` VALUES ('49','035606','Briesen b Cottbus');
INSERT INTO `area_codes` VALUES ('49','035607','Jänschwalde');
INSERT INTO `area_codes` VALUES ('49','035608','Gross Ossnig');
INSERT INTO `area_codes` VALUES ('49','035609','Drachhausen');
INSERT INTO `area_codes` VALUES ('49','03561','Guben');
INSERT INTO `area_codes` VALUES ('49','03562','Forst Lausitz');
INSERT INTO `area_codes` VALUES ('49','03563','Spremberg');
INSERT INTO `area_codes` VALUES ('49','03564','Schwarze Pumpe');
INSERT INTO `area_codes` VALUES ('49','035691','Bärenklau NL');
INSERT INTO `area_codes` VALUES ('49','035692','Kerkwitz');
INSERT INTO `area_codes` VALUES ('49','035693','Lauschütz');
INSERT INTO `area_codes` VALUES ('49','035694','Gosda b Klinge');
INSERT INTO `area_codes` VALUES ('49','035695','Simmersdorf');
INSERT INTO `area_codes` VALUES ('49','035696','Briesnig');
INSERT INTO `area_codes` VALUES ('49','035697','Bagenz');
INSERT INTO `area_codes` VALUES ('49','035698','Hornow');
INSERT INTO `area_codes` VALUES ('49','03571','Hoyerswerda');
INSERT INTO `area_codes` VALUES ('49','035722','Lauta b Hoyerswerda');
INSERT INTO `area_codes` VALUES ('49','035723','Bernsdorf OL');
INSERT INTO `area_codes` VALUES ('49','035724','Lohsa');
INSERT INTO `area_codes` VALUES ('49','035725','Wittichenau');
INSERT INTO `area_codes` VALUES ('49','035726','Groß Särchen');
INSERT INTO `area_codes` VALUES ('49','035727','Burghammer');
INSERT INTO `area_codes` VALUES ('49','035728','Uhyst Spree');
INSERT INTO `area_codes` VALUES ('49','03573','Senftenberg');
INSERT INTO `area_codes` VALUES ('49','03574','Lauchhammer');
INSERT INTO `area_codes` VALUES ('49','035751','Welzow');
INSERT INTO `area_codes` VALUES ('49','035752','Ruhland');
INSERT INTO `area_codes` VALUES ('49','035753','Großräschen');
INSERT INTO `area_codes` VALUES ('49','035754','Klettwitz');
INSERT INTO `area_codes` VALUES ('49','035755','Ortrand');
INSERT INTO `area_codes` VALUES ('49','035756','Hosena');
INSERT INTO `area_codes` VALUES ('49','03576','Weisswasser');
INSERT INTO `area_codes` VALUES ('49','035771','Bad Muskau');
INSERT INTO `area_codes` VALUES ('49','035772','Rietschen');
INSERT INTO `area_codes` VALUES ('49','035773','Schleife');
INSERT INTO `area_codes` VALUES ('49','035774','Boxberg Sachs');
INSERT INTO `area_codes` VALUES ('49','035775','Pechern');
INSERT INTO `area_codes` VALUES ('49','03578','Kamenz');
INSERT INTO `area_codes` VALUES ('49','035792','Ossling');
INSERT INTO `area_codes` VALUES ('49','035793','Elstra');
INSERT INTO `area_codes` VALUES ('49','035795','Königsbrück');
INSERT INTO `area_codes` VALUES ('49','035796','Panschwitz-Kuckau');
INSERT INTO `area_codes` VALUES ('49','035797','Schwepnitz');
INSERT INTO `area_codes` VALUES ('49','03581','Görlitz');
INSERT INTO `area_codes` VALUES ('49','035820','Zodel');
INSERT INTO `area_codes` VALUES ('49','035822','Hagenwerder');
INSERT INTO `area_codes` VALUES ('49','035823','Ostritz');
INSERT INTO `area_codes` VALUES ('49','035825','Kodersdorf');
INSERT INTO `area_codes` VALUES ('49','035826','Königshain b Görlitz');
INSERT INTO `area_codes` VALUES ('49','035827','Nieder-Seifersdorf');
INSERT INTO `area_codes` VALUES ('49','035828','Reichenbach OL');
INSERT INTO `area_codes` VALUES ('49','035829','Gersdorf b Görlitz');
INSERT INTO `area_codes` VALUES ('49','03583','Zittau');
INSERT INTO `area_codes` VALUES ('49','035841','Großschönau Sachs');
INSERT INTO `area_codes` VALUES ('49','035842','Oderwitz');
INSERT INTO `area_codes` VALUES ('49','035843','Hirschfelde b Zittau');
INSERT INTO `area_codes` VALUES ('49','035844','Oybin Kurort');
INSERT INTO `area_codes` VALUES ('49','03585','Löbau');
INSERT INTO `area_codes` VALUES ('49','03586','Neugersdorf ,Sachs');
INSERT INTO `area_codes` VALUES ('49','035872','Neusalza-Spremberg');
INSERT INTO `area_codes` VALUES ('49','035873','Herrnhut');
INSERT INTO `area_codes` VALUES ('49','035874','Bernstadt a d Eigen');
INSERT INTO `area_codes` VALUES ('49','035875','Obercunnersdorf b Löbau');
INSERT INTO `area_codes` VALUES ('49','035876','Weissenberg Sachs');
INSERT INTO `area_codes` VALUES ('49','035877','Cunewalde');
INSERT INTO `area_codes` VALUES ('49','03588','Niesky');
INSERT INTO `area_codes` VALUES ('49','035891','Rothenburg OL');
INSERT INTO `area_codes` VALUES ('49','035892','Horka OL');
INSERT INTO `area_codes` VALUES ('49','035893','Mücka');
INSERT INTO `area_codes` VALUES ('49','035894','Hähnichen');
INSERT INTO `area_codes` VALUES ('49','035895','Klitten');
INSERT INTO `area_codes` VALUES ('49','03591','Bautzen');
INSERT INTO `area_codes` VALUES ('49','03592','Kirschau');
INSERT INTO `area_codes` VALUES ('49','035930','Seitschen');
INSERT INTO `area_codes` VALUES ('49','035931','Königswartha');
INSERT INTO `area_codes` VALUES ('49','035932','Guttau');
INSERT INTO `area_codes` VALUES ('49','035933','Neschwitz');
INSERT INTO `area_codes` VALUES ('49','035934','Grossdubrau');
INSERT INTO `area_codes` VALUES ('49','035935','Kleinwelka');
INSERT INTO `area_codes` VALUES ('49','035936','Sohland Spree');
INSERT INTO `area_codes` VALUES ('49','035937','Prischwitz');
INSERT INTO `area_codes` VALUES ('49','035938','Großpostwitz OL');
INSERT INTO `area_codes` VALUES ('49','035939','Hochkirch');
INSERT INTO `area_codes` VALUES ('49','03594','Bischofswerda');
INSERT INTO `area_codes` VALUES ('49','035951','Neukirch Lausitz');
INSERT INTO `area_codes` VALUES ('49','035952','Großröhrsdorf OL');
INSERT INTO `area_codes` VALUES ('49','035953','Burkau');
INSERT INTO `area_codes` VALUES ('49','035954','Grossharthau');
INSERT INTO `area_codes` VALUES ('49','035955','Pulsnitz');
INSERT INTO `area_codes` VALUES ('49','03596','Neustadt i Sa');
INSERT INTO `area_codes` VALUES ('49','035971','Sebnitz');
INSERT INTO `area_codes` VALUES ('49','035973','Stolpen');
INSERT INTO `area_codes` VALUES ('49','035974','Hinterhermsdorf');
INSERT INTO `area_codes` VALUES ('49','035975','Hohnstein');
INSERT INTO `area_codes` VALUES ('49','03601','Mühlhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036020','Ebeleben');
INSERT INTO `area_codes` VALUES ('49','036021','Schlotheim');
INSERT INTO `area_codes` VALUES ('49','036022','Grossengottern');
INSERT INTO `area_codes` VALUES ('49','036023','Horsmar');
INSERT INTO `area_codes` VALUES ('49','036024','Diedorf b Mühlhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036025','Körner');
INSERT INTO `area_codes` VALUES ('49','036026','Struth b Mühlhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036027','Lengenfeld Unterm Stein');
INSERT INTO `area_codes` VALUES ('49','036028','Kammerforst  Thür');
INSERT INTO `area_codes` VALUES ('49','036029','Menteroda');
INSERT INTO `area_codes` VALUES ('49','03603','Bad Langensalza');
INSERT INTO `area_codes` VALUES ('49','036041','Bad Tennstedt');
INSERT INTO `area_codes` VALUES ('49','036042','Tonna');
INSERT INTO `area_codes` VALUES ('49','036043','Kirchheilingen');
INSERT INTO `area_codes` VALUES ('49','03605','Leinefelde');
INSERT INTO `area_codes` VALUES ('49','03606','Heiligenstadt Heilbad');
INSERT INTO `area_codes` VALUES ('49','036071','Teistungen');
INSERT INTO `area_codes` VALUES ('49','036072','Weißenborn-Lüderode');
INSERT INTO `area_codes` VALUES ('49','036074','Worbis');
INSERT INTO `area_codes` VALUES ('49','036075','Dingelstädt Eichsfeld');
INSERT INTO `area_codes` VALUES ('49','036076','Niederorschel');
INSERT INTO `area_codes` VALUES ('49','036077','Grossbodungen');
INSERT INTO `area_codes` VALUES ('49','036081','Arenshausen');
INSERT INTO `area_codes` VALUES ('49','036082','Ershausen');
INSERT INTO `area_codes` VALUES ('49','036083','Uder');
INSERT INTO `area_codes` VALUES ('49','036084','Heuthen');
INSERT INTO `area_codes` VALUES ('49','036085','Reinholterode');
INSERT INTO `area_codes` VALUES ('49','036087','Wüstheuterode');
INSERT INTO `area_codes` VALUES ('49','0361','Erfurt');
INSERT INTO `area_codes` VALUES ('49','036200','Elxleben b Arnstadt');
INSERT INTO `area_codes` VALUES ('49','036201','Walschleben');
INSERT INTO `area_codes` VALUES ('49','036202','Neudietendorf');
INSERT INTO `area_codes` VALUES ('49','036203','Vieselbach');
INSERT INTO `area_codes` VALUES ('49','036204','Stotternheim');
INSERT INTO `area_codes` VALUES ('49','036205','Gräfenroda');
INSERT INTO `area_codes` VALUES ('49','036206','Grossfahner');
INSERT INTO `area_codes` VALUES ('49','036207','Plaue Thür');
INSERT INTO `area_codes` VALUES ('49','036208','Ermstedt');
INSERT INTO `area_codes` VALUES ('49','036209','Klettbach');
INSERT INTO `area_codes` VALUES ('49','03621','Gotha Thür');
INSERT INTO `area_codes` VALUES ('49','03622','Waltershausen  Thür');
INSERT INTO `area_codes` VALUES ('49','03623','Friedrichroda');
INSERT INTO `area_codes` VALUES ('49','03624','Ohrdruf');
INSERT INTO `area_codes` VALUES ('49','036252','Tambach-Dietharz Thür Wald');
INSERT INTO `area_codes` VALUES ('49','036253','Georgenthal Thür Wald');
INSERT INTO `area_codes` VALUES ('49','036254','Friedrichswerth');
INSERT INTO `area_codes` VALUES ('49','036255','Goldbach b Gotha');
INSERT INTO `area_codes` VALUES ('49','036256','Wechmar');
INSERT INTO `area_codes` VALUES ('49','036257','Luisenthal Thür');
INSERT INTO `area_codes` VALUES ('49','036258','Friemar');
INSERT INTO `area_codes` VALUES ('49','036259','Tabarz Thür Wald');
INSERT INTO `area_codes` VALUES ('49','03628','Arnstadt');
INSERT INTO `area_codes` VALUES ('49','03629','Stadtilm');
INSERT INTO `area_codes` VALUES ('49','03631','Nordhausen Thür');
INSERT INTO `area_codes` VALUES ('49','03632','Sondershausen');
INSERT INTO `area_codes` VALUES ('49','036330','Grossberndten');
INSERT INTO `area_codes` VALUES ('49','036331','Ilfeld');
INSERT INTO `area_codes` VALUES ('49','036332','Ellrich');
INSERT INTO `area_codes` VALUES ('49','036333','Heringen Helme');
INSERT INTO `area_codes` VALUES ('49','036334','Wolkramshausen');
INSERT INTO `area_codes` VALUES ('49','036335','Grosswechsungen');
INSERT INTO `area_codes` VALUES ('49','036336','Klettenberg');
INSERT INTO `area_codes` VALUES ('49','036337','Schiedungen');
INSERT INTO `area_codes` VALUES ('49','036338','Bleicherode');
INSERT INTO `area_codes` VALUES ('49','03634','Sömmerda');
INSERT INTO `area_codes` VALUES ('49','03635','Kölleda');
INSERT INTO `area_codes` VALUES ('49','03636','Greussen');
INSERT INTO `area_codes` VALUES ('49','036370','Grossenehrich');
INSERT INTO `area_codes` VALUES ('49','036371','Schlossvippach');
INSERT INTO `area_codes` VALUES ('49','036372','Kleinneuhausen');
INSERT INTO `area_codes` VALUES ('49','036373','Buttstädt');
INSERT INTO `area_codes` VALUES ('49','036374','Weissensee');
INSERT INTO `area_codes` VALUES ('49','036375','Kindelbrück');
INSERT INTO `area_codes` VALUES ('49','036376','Straussfurt');
INSERT INTO `area_codes` VALUES ('49','036377','Rastenberg');
INSERT INTO `area_codes` VALUES ('49','036378','Ostramondra');
INSERT INTO `area_codes` VALUES ('49','036379','Holzengel');
INSERT INTO `area_codes` VALUES ('49','03641','Jena');
INSERT INTO `area_codes` VALUES ('49','036421','Camburg');
INSERT INTO `area_codes` VALUES ('49','036422','Reinstädt Thür');
INSERT INTO `area_codes` VALUES ('49','036423','Orlamünde');
INSERT INTO `area_codes` VALUES ('49','036424','Kahla Thür');
INSERT INTO `area_codes` VALUES ('49','036425','Isserstedt');
INSERT INTO `area_codes` VALUES ('49','036426','Ottendorf b Stadtroda');
INSERT INTO `area_codes` VALUES ('49','036427','Dornburg Saale');
INSERT INTO `area_codes` VALUES ('49','036428','Stadtroda');
INSERT INTO `area_codes` VALUES ('49','03643','Weimar Thür');
INSERT INTO `area_codes` VALUES ('49','03644','Apolda');
INSERT INTO `area_codes` VALUES ('49','036450','Kranichfeld');
INSERT INTO `area_codes` VALUES ('49','036451','Buttelstedt');
INSERT INTO `area_codes` VALUES ('49','036452','Berlstedt');
INSERT INTO `area_codes` VALUES ('49','036453','Mellingen');
INSERT INTO `area_codes` VALUES ('49','036454','Magdala');
INSERT INTO `area_codes` VALUES ('49','036458','Bad Berka');
INSERT INTO `area_codes` VALUES ('49','036459','Blankenhain Thür');
INSERT INTO `area_codes` VALUES ('49','036461','Bad Sulza');
INSERT INTO `area_codes` VALUES ('49','036462','Ossmannstedt');
INSERT INTO `area_codes` VALUES ('49','036463','Gebstedt');
INSERT INTO `area_codes` VALUES ('49','036464','Wormstedt');
INSERT INTO `area_codes` VALUES ('49','036465','Oberndorf b Apolda');
INSERT INTO `area_codes` VALUES ('49','03647','Pößneck');
INSERT INTO `area_codes` VALUES ('49','036481','Neustadt an der Orla');
INSERT INTO `area_codes` VALUES ('49','036482','Triptis');
INSERT INTO `area_codes` VALUES ('49','036483','Ziegenrück');
INSERT INTO `area_codes` VALUES ('49','036484','Knau b Pößneck');
INSERT INTO `area_codes` VALUES ('49','0365','Gera');
INSERT INTO `area_codes` VALUES ('49','036601','Hermsdorf Thür');
INSERT INTO `area_codes` VALUES ('49','036602','Ronneburg Thür');
INSERT INTO `area_codes` VALUES ('49','036603','Weida');
INSERT INTO `area_codes` VALUES ('49','036604','Münchenbernsdorf');
INSERT INTO `area_codes` VALUES ('49','036605','Bad Köstritz');
INSERT INTO `area_codes` VALUES ('49','036606','Kraftsdorf');
INSERT INTO `area_codes` VALUES ('49','036607','Niederpöllnitz');
INSERT INTO `area_codes` VALUES ('49','036608','Seelingstädt b Gera');
INSERT INTO `area_codes` VALUES ('49','03661','Greiz');
INSERT INTO `area_codes` VALUES ('49','036621','Elsterberg b Plauen');
INSERT INTO `area_codes` VALUES ('49','036622','Triebes');
INSERT INTO `area_codes` VALUES ('49','036623','Berga Elster');
INSERT INTO `area_codes` VALUES ('49','036624','Teichwolframsdorf');
INSERT INTO `area_codes` VALUES ('49','036625','Langenwetzendorf');
INSERT INTO `area_codes` VALUES ('49','036626','Auma');
INSERT INTO `area_codes` VALUES ('49','036628','Zeulenroda');
INSERT INTO `area_codes` VALUES ('49','03663','Schleiz');
INSERT INTO `area_codes` VALUES ('49','036640','Remptendorf');
INSERT INTO `area_codes` VALUES ('49','036642','Harra');
INSERT INTO `area_codes` VALUES ('49','036643','Thimmendorf');
INSERT INTO `area_codes` VALUES ('49','036644','Hirschberg Saale');
INSERT INTO `area_codes` VALUES ('49','036645','Mühltroff');
INSERT INTO `area_codes` VALUES ('49','036646','Tanna  b Schleiz');
INSERT INTO `area_codes` VALUES ('49','036647','Saalburg Thür');
INSERT INTO `area_codes` VALUES ('49','036648','Dittersdorf b Schleiz');
INSERT INTO `area_codes` VALUES ('49','036649','Gefell b Schleiz');
INSERT INTO `area_codes` VALUES ('49','036651','Lobenstein');
INSERT INTO `area_codes` VALUES ('49','036652','Wurzbach');
INSERT INTO `area_codes` VALUES ('49','036653','Lehesten Thür Wald');
INSERT INTO `area_codes` VALUES ('49','036691','Eisenberg Thür');
INSERT INTO `area_codes` VALUES ('49','036692','Bürgel');
INSERT INTO `area_codes` VALUES ('49','036693','Crossen an der Elster');
INSERT INTO `area_codes` VALUES ('49','036694','Schkölen Thür');
INSERT INTO `area_codes` VALUES ('49','036695','Söllmnitz');
INSERT INTO `area_codes` VALUES ('49','036701','Lichte');
INSERT INTO `area_codes` VALUES ('49','036702','Lauscha');
INSERT INTO `area_codes` VALUES ('49','036703','Gräfenthal');
INSERT INTO `area_codes` VALUES ('49','036704','Steinheid');
INSERT INTO `area_codes` VALUES ('49','036705','Oberweißbach Thür Wald');
INSERT INTO `area_codes` VALUES ('49','03671','Saalfeld Saale');
INSERT INTO `area_codes` VALUES ('49','03672','Rudolstadt');
INSERT INTO `area_codes` VALUES ('49','036730','Sitzendorf');
INSERT INTO `area_codes` VALUES ('49','036731','Unterloquitz');
INSERT INTO `area_codes` VALUES ('49','036732','Könitz');
INSERT INTO `area_codes` VALUES ('49','036733','Kaulsdorf');
INSERT INTO `area_codes` VALUES ('49','036734','Leutenberg');
INSERT INTO `area_codes` VALUES ('49','036735','Probstzella');
INSERT INTO `area_codes` VALUES ('49','036736','Arnsgereuth');
INSERT INTO `area_codes` VALUES ('49','036737','Drognitz');
INSERT INTO `area_codes` VALUES ('49','036738','Königsee');
INSERT INTO `area_codes` VALUES ('49','036739','Rottenbach');
INSERT INTO `area_codes` VALUES ('49','036741','Bad Blankenburg');
INSERT INTO `area_codes` VALUES ('49','036742','Uhlstädt');
INSERT INTO `area_codes` VALUES ('49','036743','Teichel');
INSERT INTO `area_codes` VALUES ('49','036744','Remda');
INSERT INTO `area_codes` VALUES ('49','03675','Sonneberg Thür');
INSERT INTO `area_codes` VALUES ('49','036761','Heubisch');
INSERT INTO `area_codes` VALUES ('49','036762','Steinach Thür');
INSERT INTO `area_codes` VALUES ('49','036764','Neuhaus-Schierschnitz');
INSERT INTO `area_codes` VALUES ('49','036766','Schalkau');
INSERT INTO `area_codes` VALUES ('49','03677','Ilmenau Thür');
INSERT INTO `area_codes` VALUES ('49','036781','Grossbreitenbach');
INSERT INTO `area_codes` VALUES ('49','036782','Schmiedefeld a Rennsteig');
INSERT INTO `area_codes` VALUES ('49','036783','Gehren Thür');
INSERT INTO `area_codes` VALUES ('49','036784','Stützerbach');
INSERT INTO `area_codes` VALUES ('49','036785','Gräfinau-Angstedt');
INSERT INTO `area_codes` VALUES ('49','03679','Neuhaus a Rennweg');
INSERT INTO `area_codes` VALUES ('49','03681','Suhl');
INSERT INTO `area_codes` VALUES ('49','03682','Zella-Mehlis');
INSERT INTO `area_codes` VALUES ('49','03683','Schmalkalden');
INSERT INTO `area_codes` VALUES ('49','036840','Trusetal');
INSERT INTO `area_codes` VALUES ('49','036841','Schleusingen');
INSERT INTO `area_codes` VALUES ('49','036842','Oberhof Thür');
INSERT INTO `area_codes` VALUES ('49','036843','Benshausen');
INSERT INTO `area_codes` VALUES ('49','036844','Rohr Thür');
INSERT INTO `area_codes` VALUES ('49','036845','Gehlberg');
INSERT INTO `area_codes` VALUES ('49','036846','Suhl-Dietzhausen');
INSERT INTO `area_codes` VALUES ('49','036847','Steinbach-Hallenberg');
INSERT INTO `area_codes` VALUES ('49','036848','Wernshausen');
INSERT INTO `area_codes` VALUES ('49','036849','Kleinschmalkalden');
INSERT INTO `area_codes` VALUES ('49','03685','Hildburghausen');
INSERT INTO `area_codes` VALUES ('49','03686','Eisfeld');
INSERT INTO `area_codes` VALUES ('49','036870','Masserberg');
INSERT INTO `area_codes` VALUES ('49','036871','Bad Colberg-Heldburg');
INSERT INTO `area_codes` VALUES ('49','036873','Themar');
INSERT INTO `area_codes` VALUES ('49','036874','Schönbrunn b Hildburghaus');
INSERT INTO `area_codes` VALUES ('49','036875','Straufhain-Streufdorf');
INSERT INTO `area_codes` VALUES ('49','036878','Oberland');
INSERT INTO `area_codes` VALUES ('49','03691','Eisenach Thür');
INSERT INTO `area_codes` VALUES ('49','036920','Grossenlupnitz');
INSERT INTO `area_codes` VALUES ('49','036921','Wutha-Farnroda');
INSERT INTO `area_codes` VALUES ('49','036922','Gerstungen');
INSERT INTO `area_codes` VALUES ('49','036923','Treffurt');
INSERT INTO `area_codes` VALUES ('49','036924','Mihla');
INSERT INTO `area_codes` VALUES ('49','036925','Marksuhl');
INSERT INTO `area_codes` VALUES ('49','036926','Creuzburg');
INSERT INTO `area_codes` VALUES ('49','036927','Unterellen');
INSERT INTO `area_codes` VALUES ('49','036928','Neuenhof  Thür');
INSERT INTO `area_codes` VALUES ('49','036929','Ruhla');
INSERT INTO `area_codes` VALUES ('49','03693','Meiningen');
INSERT INTO `area_codes` VALUES ('49','036940','Oepfershausen');
INSERT INTO `area_codes` VALUES ('49','036941','Wasungen');
INSERT INTO `area_codes` VALUES ('49','036943','Bettenhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036944','Rentwertshausen');
INSERT INTO `area_codes` VALUES ('49','036945','Henneberg');
INSERT INTO `area_codes` VALUES ('49','036946','Erbenhausen Thür');
INSERT INTO `area_codes` VALUES ('49','036947','Jüchsen');
INSERT INTO `area_codes` VALUES ('49','036948','Römhild');
INSERT INTO `area_codes` VALUES ('49','036949','Obermaßfeld-Grimmenthal');
INSERT INTO `area_codes` VALUES ('49','03695','Bad Salzungen');
INSERT INTO `area_codes` VALUES ('49','036961','Bad Liebenstein');
INSERT INTO `area_codes` VALUES ('49','036962','Vacha');
INSERT INTO `area_codes` VALUES ('49','036963','Dorndorf Rhön');
INSERT INTO `area_codes` VALUES ('49','036964','Dermbach Rhön');
INSERT INTO `area_codes` VALUES ('49','036965','Stadtlengsfeld');
INSERT INTO `area_codes` VALUES ('49','036966','Kaltennordheim');
INSERT INTO `area_codes` VALUES ('49','036967','Geisa');
INSERT INTO `area_codes` VALUES ('49','036968','Rossdorf Rhön');
INSERT INTO `area_codes` VALUES ('49','036969','Merkers');
INSERT INTO `area_codes` VALUES ('49','0371','Chemnitz Sachs');
INSERT INTO `area_codes` VALUES ('49','037200','Wittgensdorf b Chemnitz');
INSERT INTO `area_codes` VALUES ('49','037202','Claussnitz b Chemnitz');
INSERT INTO `area_codes` VALUES ('49','037203','Gersdorf b Chemnitz');
INSERT INTO `area_codes` VALUES ('49','037204','Lichtenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','037206','Frankenberg');
INSERT INTO `area_codes` VALUES ('49','037207','Hainichen');
INSERT INTO `area_codes` VALUES ('49','037208','Auerswalde');
INSERT INTO `area_codes` VALUES ('49','037209','Einsiedel b Chemnitz');
INSERT INTO `area_codes` VALUES ('49','03721','Meinersdorf');
INSERT INTO `area_codes` VALUES ('49','03722','Limbach-Oberfrohna');
INSERT INTO `area_codes` VALUES ('49','03723','Hohenstein-Ernstthal');
INSERT INTO `area_codes` VALUES ('49','03724','Burgstädt');
INSERT INTO `area_codes` VALUES ('49','03725','Zschopau');
INSERT INTO `area_codes` VALUES ('49','03726','Flöha');
INSERT INTO `area_codes` VALUES ('49','03727','Mittweida');
INSERT INTO `area_codes` VALUES ('49','037291','Augustusburg');
INSERT INTO `area_codes` VALUES ('49','037292','Oederan');
INSERT INTO `area_codes` VALUES ('49','037293','Eppendorf Sachs');
INSERT INTO `area_codes` VALUES ('49','037294','Grünhainichen');
INSERT INTO `area_codes` VALUES ('49','037295','Lugau Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037296','Stollberg Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037297','Thum Sachs');
INSERT INTO `area_codes` VALUES ('49','037298','Oelsnitz Erzgeb');
INSERT INTO `area_codes` VALUES ('49','03731','Freiberg Sachs');
INSERT INTO `area_codes` VALUES ('49','037320','Mulda Sachs');
INSERT INTO `area_codes` VALUES ('49','037321','Frankenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','037322','Brand-Erbisdorf');
INSERT INTO `area_codes` VALUES ('49','037323','Lichtenberg Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037324','Reinsberg Sachs');
INSERT INTO `area_codes` VALUES ('49','037325','Niederbobritzsch');
INSERT INTO `area_codes` VALUES ('49','037326','Frauenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','037327','Rechenberg-Bienenmühle');
INSERT INTO `area_codes` VALUES ('49','037328','Grossschirma');
INSERT INTO `area_codes` VALUES ('49','037329','Grosshartmannsdorf');
INSERT INTO `area_codes` VALUES ('49','03733','Annaberg-Buchholz');
INSERT INTO `area_codes` VALUES ('49','037341','Ehrenfriedersdorf');
INSERT INTO `area_codes` VALUES ('49','037342','Cranzahl');
INSERT INTO `area_codes` VALUES ('49','037343','Jöhstadt');
INSERT INTO `area_codes` VALUES ('49','037344','Crottendorf Sachs');
INSERT INTO `area_codes` VALUES ('49','037346','Geyer');
INSERT INTO `area_codes` VALUES ('49','037347','Bärenstein Kr Annaberg');
INSERT INTO `area_codes` VALUES ('49','037348','Oberwiesenthal Kurort');
INSERT INTO `area_codes` VALUES ('49','037349','Scheibenberg');
INSERT INTO `area_codes` VALUES ('49','03735','Marienberg Sachs');
INSERT INTO `area_codes` VALUES ('49','037360','Olbernhau');
INSERT INTO `area_codes` VALUES ('49','037361','Neuhausen Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037362','Seiffen Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037363','Zöblitz');
INSERT INTO `area_codes` VALUES ('49','037364','Reitzenhain Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037365','Sayda');
INSERT INTO `area_codes` VALUES ('49','037366','Rübenau');
INSERT INTO `area_codes` VALUES ('49','037367','Lengefeld Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037368','Deutschneudorf');
INSERT INTO `area_codes` VALUES ('49','037369','Wolkenstein');
INSERT INTO `area_codes` VALUES ('49','03737','Rochlitz');
INSERT INTO `area_codes` VALUES ('49','037381','Penig');
INSERT INTO `area_codes` VALUES ('49','037382','Geringswalde');
INSERT INTO `area_codes` VALUES ('49','037383','Lunzenau');
INSERT INTO `area_codes` VALUES ('49','037384','Wechselburg');
INSERT INTO `area_codes` VALUES ('49','03741','Plauen');
INSERT INTO `area_codes` VALUES ('49','037421','Oelsnitz Vogtl');
INSERT INTO `area_codes` VALUES ('49','037422','Markneukirchen');
INSERT INTO `area_codes` VALUES ('49','037423','Adorf Vogtl');
INSERT INTO `area_codes` VALUES ('49','037430','Eichigt');
INSERT INTO `area_codes` VALUES ('49','037431','Mehltheuer Vogtl');
INSERT INTO `area_codes` VALUES ('49','037432','Pausa Vogtl');
INSERT INTO `area_codes` VALUES ('49','037433','Gutenfürst');
INSERT INTO `area_codes` VALUES ('49','037434','Bobenneukirchen');
INSERT INTO `area_codes` VALUES ('49','037435','Reuth b Plauen');
INSERT INTO `area_codes` VALUES ('49','037436','Weischlitz');
INSERT INTO `area_codes` VALUES ('49','037437','Bad Elster');
INSERT INTO `area_codes` VALUES ('49','037438','Bad Brambach');
INSERT INTO `area_codes` VALUES ('49','037439','Jocketa');
INSERT INTO `area_codes` VALUES ('49','03744','Auerbach Vogtl.');
INSERT INTO `area_codes` VALUES ('49','03745','Falkenstein Vogtl');
INSERT INTO `area_codes` VALUES ('49','037462','Rothenkirchen Vogtl');
INSERT INTO `area_codes` VALUES ('49','037463','Bergen Vogtl');
INSERT INTO `area_codes` VALUES ('49','037464','Schöneck Vogtl');
INSERT INTO `area_codes` VALUES ('49','037465','Tannenbergsthal Vogtl');
INSERT INTO `area_codes` VALUES ('49','037467','Klingenthal Sachs');
INSERT INTO `area_codes` VALUES ('49','037468','Treuen Vogtl');
INSERT INTO `area_codes` VALUES ('49','0375','Zwickau');
INSERT INTO `area_codes` VALUES ('49','037600','Neumark Sachs');
INSERT INTO `area_codes` VALUES ('49','037601','Mülsen Skt Jacob');
INSERT INTO `area_codes` VALUES ('49','037602','Kirchberg Sachs');
INSERT INTO `area_codes` VALUES ('49','037603','Wildenfels');
INSERT INTO `area_codes` VALUES ('49','037604','Mosel');
INSERT INTO `area_codes` VALUES ('49','037605','Hartenstein Sachs');
INSERT INTO `area_codes` VALUES ('49','037606','Lengenfeld Vogtl');
INSERT INTO `area_codes` VALUES ('49','037607','Ebersbrunn Sachs');
INSERT INTO `area_codes` VALUES ('49','037608','Waldenburg Sachs');
INSERT INTO `area_codes` VALUES ('49','037609','Wolkenburg Mulde');
INSERT INTO `area_codes` VALUES ('49','03761','Werdau Sachs');
INSERT INTO `area_codes` VALUES ('49','03762','Crimmitschau');
INSERT INTO `area_codes` VALUES ('49','03763','Glauchau');
INSERT INTO `area_codes` VALUES ('49','03764','Meerane');
INSERT INTO `area_codes` VALUES ('49','03765','Reichenbach Vogtl');
INSERT INTO `area_codes` VALUES ('49','03771','Aue Sachs');
INSERT INTO `area_codes` VALUES ('49','03772','Schneeberg Erzgeb');
INSERT INTO `area_codes` VALUES ('49','03773','Johanngeorgenstadt');
INSERT INTO `area_codes` VALUES ('49','03774','Schwarzenberg');
INSERT INTO `area_codes` VALUES ('49','037752','Eibenstock');
INSERT INTO `area_codes` VALUES ('49','037754','Zwönitz');
INSERT INTO `area_codes` VALUES ('49','037755','Schönheide Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037756','Breitenbrunn Erzgeb');
INSERT INTO `area_codes` VALUES ('49','037757','Rittersgrün');
INSERT INTO `area_codes` VALUES ('49','0381','Rostock');
INSERT INTO `area_codes` VALUES ('49','038201','Gelbensande');
INSERT INTO `area_codes` VALUES ('49','038202','Volkenshagen');
INSERT INTO `area_codes` VALUES ('49','038203','Bad Doberan');
INSERT INTO `area_codes` VALUES ('49','038204','Broderstorf');
INSERT INTO `area_codes` VALUES ('49','038205','Tessin b Rostock');
INSERT INTO `area_codes` VALUES ('49','038206','Graal-Müritz Seeheilbad');
INSERT INTO `area_codes` VALUES ('49','038207','Stäbelow');
INSERT INTO `area_codes` VALUES ('49','038208','Kavelstorf');
INSERT INTO `area_codes` VALUES ('49','038209','Sanitz b Rostock');
INSERT INTO `area_codes` VALUES ('49','03821','Ribnitz-Damgarten');
INSERT INTO `area_codes` VALUES ('49','038220','Wustrow Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038221','Marlow');
INSERT INTO `area_codes` VALUES ('49','038222','Semlow');
INSERT INTO `area_codes` VALUES ('49','038223','Saal Vorpom');
INSERT INTO `area_codes` VALUES ('49','038224','Gresenhorst');
INSERT INTO `area_codes` VALUES ('49','038225','Trinwillershagen');
INSERT INTO `area_codes` VALUES ('49','038226','Dierhagen Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038227','Lüdershagen b Barth');
INSERT INTO `area_codes` VALUES ('49','038228','Dettmannsdorf-Kölzow');
INSERT INTO `area_codes` VALUES ('49','038229','Bad Sülze');
INSERT INTO `area_codes` VALUES ('49','038231','Barth');
INSERT INTO `area_codes` VALUES ('49','038232','Zingst Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038233','Prerow Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038234','Born a Darß');
INSERT INTO `area_codes` VALUES ('49','038292','Kröpelin');
INSERT INTO `area_codes` VALUES ('49','038293','Kühlungsborn Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038294','Neubukow');
INSERT INTO `area_codes` VALUES ('49','038295','Satow b Bad Doberan');
INSERT INTO `area_codes` VALUES ('49','038296','Rerik Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038297','Moitin');
INSERT INTO `area_codes` VALUES ('49','038300','Insel Hiddensee');
INSERT INTO `area_codes` VALUES ('49','038301','Putbus');
INSERT INTO `area_codes` VALUES ('49','038302','Sagard');
INSERT INTO `area_codes` VALUES ('49','038303','Sellin Ostseebad');
INSERT INTO `area_codes` VALUES ('49','038304','Garz Rügen');
INSERT INTO `area_codes` VALUES ('49','038305','Gingst');
INSERT INTO `area_codes` VALUES ('49','038306','Samtens');
INSERT INTO `area_codes` VALUES ('49','038307','Poseritz');
INSERT INTO `area_codes` VALUES ('49','038308','Göhren Rügen');
INSERT INTO `area_codes` VALUES ('49','038309','Trent');
INSERT INTO `area_codes` VALUES ('49','03831','Stralsund');
INSERT INTO `area_codes` VALUES ('49','038320','Tribsees');
INSERT INTO `area_codes` VALUES ('49','038321','Martensdorf b Stralsund');
INSERT INTO `area_codes` VALUES ('49','038322','Richtenberg');
INSERT INTO `area_codes` VALUES ('49','038323','Prohn');
INSERT INTO `area_codes` VALUES ('49','038324','Velgast');
INSERT INTO `area_codes` VALUES ('49','038325','Rolofshagen');
INSERT INTO `area_codes` VALUES ('49','038326','Grimmen');
INSERT INTO `area_codes` VALUES ('49','038327','Elmenhorst Vorpom');
INSERT INTO `area_codes` VALUES ('49','038328','Miltzow');
INSERT INTO `area_codes` VALUES ('49','038331','Rakow Vorpom');
INSERT INTO `area_codes` VALUES ('49','038332','Gross Bisdorf');
INSERT INTO `area_codes` VALUES ('49','038333','Horst b Grimmen');
INSERT INTO `area_codes` VALUES ('49','038334','Grammendorf');
INSERT INTO `area_codes` VALUES ('49','03834','Greifswald');
INSERT INTO `area_codes` VALUES ('49','038351','Mesekenhagen');
INSERT INTO `area_codes` VALUES ('49','038352','Kemnitz b Greifswald');
INSERT INTO `area_codes` VALUES ('49','038353','Gützkow b Greifswald');
INSERT INTO `area_codes` VALUES ('49','038354','Wusterhusen');
INSERT INTO `area_codes` VALUES ('49','038355','Züssow');
INSERT INTO `area_codes` VALUES ('49','038356','Behrenhoff');
INSERT INTO `area_codes` VALUES ('49','03836','Wolgast');
INSERT INTO `area_codes` VALUES ('49','038370','Kröslin');
INSERT INTO `area_codes` VALUES ('49','038371','Karlshagen');
INSERT INTO `area_codes` VALUES ('49','038372','Usedom');
INSERT INTO `area_codes` VALUES ('49','038373','Katzow');
INSERT INTO `area_codes` VALUES ('49','038374','Lassan b Wolgast');
INSERT INTO `area_codes` VALUES ('49','038375','Koserow');
INSERT INTO `area_codes` VALUES ('49','038376','Zirchow');
INSERT INTO `area_codes` VALUES ('49','038377','Zinnowitz');
INSERT INTO `area_codes` VALUES ('49','038378','Heringsdorf Seebad');
INSERT INTO `area_codes` VALUES ('49','038379','Benz Usedom');
INSERT INTO `area_codes` VALUES ('49','03838','Bergen auf Rügen');
INSERT INTO `area_codes` VALUES ('49','038391','Altenkirchen Rügen');
INSERT INTO `area_codes` VALUES ('49','038392','Sassnitz');
INSERT INTO `area_codes` VALUES ('49','038393','Binz Ostseebad');
INSERT INTO `area_codes` VALUES ('49','03841','Wismar Meckl');
INSERT INTO `area_codes` VALUES ('49','038422','Neukloster');
INSERT INTO `area_codes` VALUES ('49','038423','Bad Kleinen');
INSERT INTO `area_codes` VALUES ('49','038424','Bobitz');
INSERT INTO `area_codes` VALUES ('49','038425','Kirchdorf Poel');
INSERT INTO `area_codes` VALUES ('49','038426','Neuburg-Steinhausen');
INSERT INTO `area_codes` VALUES ('49','038427','Blowatz');
INSERT INTO `area_codes` VALUES ('49','038428','Hohenkirchen b Wismar');
INSERT INTO `area_codes` VALUES ('49','038429','Glasin');
INSERT INTO `area_codes` VALUES ('49','03843','Güstrow');
INSERT INTO `area_codes` VALUES ('49','03844','Schwaan');
INSERT INTO `area_codes` VALUES ('49','038450','Tarnow b Bützow');
INSERT INTO `area_codes` VALUES ('49','038451','Hoppenrade b Güstrow');
INSERT INTO `area_codes` VALUES ('49','038452','Lalendorf');
INSERT INTO `area_codes` VALUES ('49','038453','Mistorf');
INSERT INTO `area_codes` VALUES ('49','038454','Kritzkow');
INSERT INTO `area_codes` VALUES ('49','038455','Plaaz');
INSERT INTO `area_codes` VALUES ('49','038456','Langhagen b Güstrow');
INSERT INTO `area_codes` VALUES ('49','038457','Krakow am See');
INSERT INTO `area_codes` VALUES ('49','038458','Zehna');
INSERT INTO `area_codes` VALUES ('49','038459','Laage');
INSERT INTO `area_codes` VALUES ('49','038461','Bützow');
INSERT INTO `area_codes` VALUES ('49','038462','Baumgarten Meckl');
INSERT INTO `area_codes` VALUES ('49','038464','Bernitt');
INSERT INTO `area_codes` VALUES ('49','038466','Jürgenshagen');
INSERT INTO `area_codes` VALUES ('49','03847','Sternberg');
INSERT INTO `area_codes` VALUES ('49','038481','Witzin');
INSERT INTO `area_codes` VALUES ('49','038482','Warin');
INSERT INTO `area_codes` VALUES ('49','038483','Brüel');
INSERT INTO `area_codes` VALUES ('49','038484','Ventschow');
INSERT INTO `area_codes` VALUES ('49','038485','Dabel');
INSERT INTO `area_codes` VALUES ('49','038486','Gustävel');
INSERT INTO `area_codes` VALUES ('49','038488','Demen');
INSERT INTO `area_codes` VALUES ('49','0385','Schwerin Meckl');
INSERT INTO `area_codes` VALUES ('49','03860','Raben Steinfeld');
INSERT INTO `area_codes` VALUES ('49','03861','Plate');
INSERT INTO `area_codes` VALUES ('49','03863','Crivitz');
INSERT INTO `area_codes` VALUES ('49','03865','Holthusen');
INSERT INTO `area_codes` VALUES ('49','03866','Cambs');
INSERT INTO `area_codes` VALUES ('49','03867','Lübstorf');
INSERT INTO `area_codes` VALUES ('49','03868','Rastow');
INSERT INTO `area_codes` VALUES ('49','03869','Dümmer');
INSERT INTO `area_codes` VALUES ('49','03871','Parchim');
INSERT INTO `area_codes` VALUES ('49','038720','Grebbin');
INSERT INTO `area_codes` VALUES ('49','038721','Ziegendorf');
INSERT INTO `area_codes` VALUES ('49','038722','Raduhn');
INSERT INTO `area_codes` VALUES ('49','038723','Kladrum');
INSERT INTO `area_codes` VALUES ('49','038724','Siggelkow');
INSERT INTO `area_codes` VALUES ('49','038725','Gross Godems');
INSERT INTO `area_codes` VALUES ('49','038726','Spornitz');
INSERT INTO `area_codes` VALUES ('49','038727','Mestlin');
INSERT INTO `area_codes` VALUES ('49','038728','Domsühl');
INSERT INTO `area_codes` VALUES ('49','038729','Marnitz');
INSERT INTO `area_codes` VALUES ('49','038731','Lübz');
INSERT INTO `area_codes` VALUES ('49','038732','Gallin b Lübz');
INSERT INTO `area_codes` VALUES ('49','038733','Karbow-Vietlübbe');
INSERT INTO `area_codes` VALUES ('49','038735','Plau am See');
INSERT INTO `area_codes` VALUES ('49','038736','Goldberg Meckl');
INSERT INTO `area_codes` VALUES ('49','038737','Ganzlin');
INSERT INTO `area_codes` VALUES ('49','038738','Karow b Lübz');
INSERT INTO `area_codes` VALUES ('49','03874','Ludwigslust Meckl');
INSERT INTO `area_codes` VALUES ('49','038750','Malliss');
INSERT INTO `area_codes` VALUES ('49','038751','Picher');
INSERT INTO `area_codes` VALUES ('49','038752','Zierzow b Ludwigslust');
INSERT INTO `area_codes` VALUES ('49','038753','Wöbbelin');
INSERT INTO `area_codes` VALUES ('49','038754','Leussow b Ludwigslust');
INSERT INTO `area_codes` VALUES ('49','038755','Eldena');
INSERT INTO `area_codes` VALUES ('49','038756','Grabow Meckl');
INSERT INTO `area_codes` VALUES ('49','038757','Neustadt-Glewe');
INSERT INTO `area_codes` VALUES ('49','038758','Dömitz');
INSERT INTO `area_codes` VALUES ('49','038759','Tewswoos');
INSERT INTO `area_codes` VALUES ('49','03876','Perleberg');
INSERT INTO `area_codes` VALUES ('49','03877','Wittenberge');
INSERT INTO `area_codes` VALUES ('49','038780','Lanz Brandenb');
INSERT INTO `area_codes` VALUES ('49','038781','Mellen');
INSERT INTO `area_codes` VALUES ('49','038782','Reetz b Perleberg');
INSERT INTO `area_codes` VALUES ('49','038783','Dallmin');
INSERT INTO `area_codes` VALUES ('49','038784','Kleinow Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','038785','Berge b Perleberg');
INSERT INTO `area_codes` VALUES ('49','038787','Glöwen');
INSERT INTO `area_codes` VALUES ('49','038788','Gross Warnow');
INSERT INTO `area_codes` VALUES ('49','038789','Wolfshagen b Perleberg');
INSERT INTO `area_codes` VALUES ('49','038791','Bad Wilsnack');
INSERT INTO `area_codes` VALUES ('49','038792','Lenzen (Elbe)');
INSERT INTO `area_codes` VALUES ('49','038793','Dergenthin');
INSERT INTO `area_codes` VALUES ('49','038794','Cumlosen');
INSERT INTO `area_codes` VALUES ('49','038796','Viesecke');
INSERT INTO `area_codes` VALUES ('49','038797','Karstädt Kr Prignitz');
INSERT INTO `area_codes` VALUES ('49','03881','Grevesmühlen');
INSERT INTO `area_codes` VALUES ('49','038821','Lüdersdorf Meckl');
INSERT INTO `area_codes` VALUES ('49','038822','Diedrichshagen b Grevesmühlen');
INSERT INTO `area_codes` VALUES ('49','038823','Selmsdorf');
INSERT INTO `area_codes` VALUES ('49','038824','Mallentin');
INSERT INTO `area_codes` VALUES ('49','038825','Klütz');
INSERT INTO `area_codes` VALUES ('49','038826','Dassow');
INSERT INTO `area_codes` VALUES ('49','038827','Kalkhorst');
INSERT INTO `area_codes` VALUES ('49','038828','Schönberg Meckl');
INSERT INTO `area_codes` VALUES ('49','03883','Hagenow');
INSERT INTO `area_codes` VALUES ('49','038841','Neuhaus Elbe');
INSERT INTO `area_codes` VALUES ('49','038842','Lüttenmark');
INSERT INTO `area_codes` VALUES ('49','038843','Bennin');
INSERT INTO `area_codes` VALUES ('49','038844','Gülze');
INSERT INTO `area_codes` VALUES ('49','038845','Kaarssen');
INSERT INTO `area_codes` VALUES ('49','038847','Boizenburg Elbe');
INSERT INTO `area_codes` VALUES ('49','038848','Vellahn');
INSERT INTO `area_codes` VALUES ('49','038850','Gammelin');
INSERT INTO `area_codes` VALUES ('49','038851','Zarrentin Meckl');
INSERT INTO `area_codes` VALUES ('49','038852','Wittenburg');
INSERT INTO `area_codes` VALUES ('49','038853','Drönnewitz b Hagenow');
INSERT INTO `area_codes` VALUES ('49','038854','Redefin');
INSERT INTO `area_codes` VALUES ('49','038855','Lübtheen');
INSERT INTO `area_codes` VALUES ('49','038856','Pritzier b Hagenow');
INSERT INTO `area_codes` VALUES ('49','038858','Lassahn');
INSERT INTO `area_codes` VALUES ('49','038859','Alt Zachun');
INSERT INTO `area_codes` VALUES ('49','03886','Gadebusch');
INSERT INTO `area_codes` VALUES ('49','038871','Mühlen Eichsen');
INSERT INTO `area_codes` VALUES ('49','038872','Rehna');
INSERT INTO `area_codes` VALUES ('49','038873','Carlow');
INSERT INTO `area_codes` VALUES ('49','038874','Lützow');
INSERT INTO `area_codes` VALUES ('49','038875','Schlagsdorf b Gadebusch');
INSERT INTO `area_codes` VALUES ('49','038876','Roggendorf');
INSERT INTO `area_codes` VALUES ('49','039000','Beetzendorf');
INSERT INTO `area_codes` VALUES ('49','039001','Apenburg');
INSERT INTO `area_codes` VALUES ('49','039002','Oebisfelde');
INSERT INTO `area_codes` VALUES ('49','039003','Jübar');
INSERT INTO `area_codes` VALUES ('49','039004','Köckte b Gardelegen');
INSERT INTO `area_codes` VALUES ('49','039005','Kusey');
INSERT INTO `area_codes` VALUES ('49','039006','Miesterhorst');
INSERT INTO `area_codes` VALUES ('49','039007','Tangeln');
INSERT INTO `area_codes` VALUES ('49','039008','Kunrau');
INSERT INTO `area_codes` VALUES ('49','039009','Badel');
INSERT INTO `area_codes` VALUES ('49','03901','Salzwedel');
INSERT INTO `area_codes` VALUES ('49','03902','Diesdorf Altm');
INSERT INTO `area_codes` VALUES ('49','039030','Brunau');
INSERT INTO `area_codes` VALUES ('49','039031','Dähre');
INSERT INTO `area_codes` VALUES ('49','039032','Mahlsdorf b Salzwedel');
INSERT INTO `area_codes` VALUES ('49','039033','Wallstawe');
INSERT INTO `area_codes` VALUES ('49','039034','Fleetmark');
INSERT INTO `area_codes` VALUES ('49','039035','Kuhfelde');
INSERT INTO `area_codes` VALUES ('49','039036','Binde');
INSERT INTO `area_codes` VALUES ('49','039037','Pretzier');
INSERT INTO `area_codes` VALUES ('49','039038','Henningen');
INSERT INTO `area_codes` VALUES ('49','039039','Bonese');
INSERT INTO `area_codes` VALUES ('49','03904','Haldensleben');
INSERT INTO `area_codes` VALUES ('49','039050','Bartensleben');
INSERT INTO `area_codes` VALUES ('49','039051','Calvörde');
INSERT INTO `area_codes` VALUES ('49','039052','Erxleben b Haldensleben');
INSERT INTO `area_codes` VALUES ('49','039053','Süplingen');
INSERT INTO `area_codes` VALUES ('49','039054','Flechtingen');
INSERT INTO `area_codes` VALUES ('49','039055','Hörsingen');
INSERT INTO `area_codes` VALUES ('49','039056','Klüden');
INSERT INTO `area_codes` VALUES ('49','039057','Rätzlingen Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','039058','Uthmöden');
INSERT INTO `area_codes` VALUES ('49','039059','Wegenstedt');
INSERT INTO `area_codes` VALUES ('49','039061','Weferlingen');
INSERT INTO `area_codes` VALUES ('49','039062','Bebertal');
INSERT INTO `area_codes` VALUES ('49','03907','Gardelegen');
INSERT INTO `area_codes` VALUES ('49','039080','Kalbe Milde');
INSERT INTO `area_codes` VALUES ('49','039081','Kakerbeck Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','039082','Mieste');
INSERT INTO `area_codes` VALUES ('49','039083','Messdorf');
INSERT INTO `area_codes` VALUES ('49','039084','Lindstedt');
INSERT INTO `area_codes` VALUES ('49','039085','Zichtau');
INSERT INTO `area_codes` VALUES ('49','039086','Jävenitz');
INSERT INTO `area_codes` VALUES ('49','039087','Jerchel Altmark');
INSERT INTO `area_codes` VALUES ('49','039088','Letzlingen');
INSERT INTO `area_codes` VALUES ('49','039089','Bismark Altmark');
INSERT INTO `area_codes` VALUES ('49','03909','Klötze Altmark');
INSERT INTO `area_codes` VALUES ('49','0391','Magdeburg');
INSERT INTO `area_codes` VALUES ('49','039200','Gommern');
INSERT INTO `area_codes` VALUES ('49','039201','Wolmirstedt');
INSERT INTO `area_codes` VALUES ('49','039202','Gross Ammensleben');
INSERT INTO `area_codes` VALUES ('49','039203','Barleben');
INSERT INTO `area_codes` VALUES ('49','039204','Niederndodeleben');
INSERT INTO `area_codes` VALUES ('49','039205','Langenweddingen');
INSERT INTO `area_codes` VALUES ('49','039206','Eichenbarleben');
INSERT INTO `area_codes` VALUES ('49','039207','Colbitz');
INSERT INTO `area_codes` VALUES ('49','039208','Loitsche');
INSERT INTO `area_codes` VALUES ('49','039209','Wanzleben');
INSERT INTO `area_codes` VALUES ('49','03921','Burg b Magdeburg');
INSERT INTO `area_codes` VALUES ('49','039221','Möckern b Magdeburg');
INSERT INTO `area_codes` VALUES ('49','039222','Möser');
INSERT INTO `area_codes` VALUES ('49','039223','Theessen');
INSERT INTO `area_codes` VALUES ('49','039224','Büden');
INSERT INTO `area_codes` VALUES ('49','039225','Altengrabow');
INSERT INTO `area_codes` VALUES ('49','039226','Hohenziatz');
INSERT INTO `area_codes` VALUES ('49','03923','Zerbst');
INSERT INTO `area_codes` VALUES ('49','039241','Leitzkau');
INSERT INTO `area_codes` VALUES ('49','039242','Prödel');
INSERT INTO `area_codes` VALUES ('49','039243','Nedlitz b Zerbst');
INSERT INTO `area_codes` VALUES ('49','039244','Steutz');
INSERT INTO `area_codes` VALUES ('49','039245','Loburg');
INSERT INTO `area_codes` VALUES ('49','039246','Lindau Anh');
INSERT INTO `area_codes` VALUES ('49','039247','Güterglück');
INSERT INTO `area_codes` VALUES ('49','039248','Dobritz');
INSERT INTO `area_codes` VALUES ('49','03925','Stassfurt');
INSERT INTO `area_codes` VALUES ('49','039262','Güsten Anh');
INSERT INTO `area_codes` VALUES ('49','039263','Unseburg');
INSERT INTO `area_codes` VALUES ('49','039264','Kroppenstedt');
INSERT INTO `area_codes` VALUES ('49','039265','Löderburg');
INSERT INTO `area_codes` VALUES ('49','039266','Förderstedt');
INSERT INTO `area_codes` VALUES ('49','039267','Schneidlingen');
INSERT INTO `area_codes` VALUES ('49','039268','Egeln');
INSERT INTO `area_codes` VALUES ('49','03928','Schönebeck Elbe');
INSERT INTO `area_codes` VALUES ('49','039291','Calbe Saale');
INSERT INTO `area_codes` VALUES ('49','039292','Biederitz');
INSERT INTO `area_codes` VALUES ('49','039293','Dreileben');
INSERT INTO `area_codes` VALUES ('49','039294','Gross Rosenburg');
INSERT INTO `area_codes` VALUES ('49','039295','Zuchau');
INSERT INTO `area_codes` VALUES ('49','039296','Welsleben');
INSERT INTO `area_codes` VALUES ('49','039297','Eickendorf Kr Schönebeck');
INSERT INTO `area_codes` VALUES ('49','039298','Barby Elbe');
INSERT INTO `area_codes` VALUES ('49','03931','Stendal');
INSERT INTO `area_codes` VALUES ('49','039320','Schinne');
INSERT INTO `area_codes` VALUES ('49','039321','Arneburg');
INSERT INTO `area_codes` VALUES ('49','039322','Tangermünde');
INSERT INTO `area_codes` VALUES ('49','039323','Schönhausen Elbe');
INSERT INTO `area_codes` VALUES ('49','039324','Kläden b Stendal');
INSERT INTO `area_codes` VALUES ('49','039325','Vinzelberg');
INSERT INTO `area_codes` VALUES ('49','039327','Klietz');
INSERT INTO `area_codes` VALUES ('49','039328','Rochau');
INSERT INTO `area_codes` VALUES ('49','039329','Möringen');
INSERT INTO `area_codes` VALUES ('49','03933','Genthin');
INSERT INTO `area_codes` VALUES ('49','039341','Redekin');
INSERT INTO `area_codes` VALUES ('49','039342','Gladau');
INSERT INTO `area_codes` VALUES ('49','039343','Jerichow');
INSERT INTO `area_codes` VALUES ('49','039344','Güsen');
INSERT INTO `area_codes` VALUES ('49','039345','Parchen');
INSERT INTO `area_codes` VALUES ('49','039346','Tucheim');
INSERT INTO `area_codes` VALUES ('49','039347','Kade');
INSERT INTO `area_codes` VALUES ('49','039348','Klitsche');
INSERT INTO `area_codes` VALUES ('49','039349','Parey Elbe');
INSERT INTO `area_codes` VALUES ('49','03935','Tangerhütte');
INSERT INTO `area_codes` VALUES ('49','039361','Lüderitz');
INSERT INTO `area_codes` VALUES ('49','039362','Grieben b Tangerhütte');
INSERT INTO `area_codes` VALUES ('49','039363','Angern');
INSERT INTO `area_codes` VALUES ('49','039364','Dolle');
INSERT INTO `area_codes` VALUES ('49','039365','Bellingen b Stendal');
INSERT INTO `area_codes` VALUES ('49','039366','Kehnert');
INSERT INTO `area_codes` VALUES ('49','03937','Osterburg Altmark');
INSERT INTO `area_codes` VALUES ('49','039382','Kamern');
INSERT INTO `area_codes` VALUES ('49','039383','Sandau Elbe');
INSERT INTO `area_codes` VALUES ('49','039384','Arendsee Altmark');
INSERT INTO `area_codes` VALUES ('49','039386','Seehausen Altmark');
INSERT INTO `area_codes` VALUES ('49','039387','Havelberg');
INSERT INTO `area_codes` VALUES ('49','039388','Goldbeck Altm');
INSERT INTO `area_codes` VALUES ('49','039389','Schollene');
INSERT INTO `area_codes` VALUES ('49','039390','Iden');
INSERT INTO `area_codes` VALUES ('49','039391','Lückstedt');
INSERT INTO `area_codes` VALUES ('49','039392','Rönnebeck Sachs-Ahn');
INSERT INTO `area_codes` VALUES ('49','039393','Werben Elbe');
INSERT INTO `area_codes` VALUES ('49','039394','Hohenberg-Krusemark');
INSERT INTO `area_codes` VALUES ('49','039395','Wanzer');
INSERT INTO `area_codes` VALUES ('49','039396','Neukirchen Altmark');
INSERT INTO `area_codes` VALUES ('49','039397','Geestgottberg');
INSERT INTO `area_codes` VALUES ('49','039398','Gross Garz');
INSERT INTO `area_codes` VALUES ('49','039399','Kleinau');
INSERT INTO `area_codes` VALUES ('49','039400','Wefensleben');
INSERT INTO `area_codes` VALUES ('49','039401','Neuwegersleben');
INSERT INTO `area_codes` VALUES ('49','039402','Völpke');
INSERT INTO `area_codes` VALUES ('49','039403','Gröningen Sachs-Ahn');
INSERT INTO `area_codes` VALUES ('49','039404','Ausleben');
INSERT INTO `area_codes` VALUES ('49','039405','Hötensleben');
INSERT INTO `area_codes` VALUES ('49','039406','Harbke');
INSERT INTO `area_codes` VALUES ('49','039407','Seehausen Börde');
INSERT INTO `area_codes` VALUES ('49','039408','Hadmersleben');
INSERT INTO `area_codes` VALUES ('49','039409','Eilsleben');
INSERT INTO `area_codes` VALUES ('49','03941','Halberstadt');
INSERT INTO `area_codes` VALUES ('49','039421','Osterwieck');
INSERT INTO `area_codes` VALUES ('49','039422','Badersleben');
INSERT INTO `area_codes` VALUES ('49','039423','Wegeleben');
INSERT INTO `area_codes` VALUES ('49','039424','Schwanebeck Sachs-Anh');
INSERT INTO `area_codes` VALUES ('49','039425','Dingelstedt a Huy');
INSERT INTO `area_codes` VALUES ('49','039426','Hessen');
INSERT INTO `area_codes` VALUES ('49','039427','Ströbeck');
INSERT INTO `area_codes` VALUES ('49','039428','Pabstorf');
INSERT INTO `area_codes` VALUES ('49','03943','Wernigerode');
INSERT INTO `area_codes` VALUES ('49','03944','Blankenburg Harz');
INSERT INTO `area_codes` VALUES ('49','039451','Wasserleben');
INSERT INTO `area_codes` VALUES ('49','039452','Ilsenburg');
INSERT INTO `area_codes` VALUES ('49','039453','Derenburg');
INSERT INTO `area_codes` VALUES ('49','039454','Elbingerode Harz');
INSERT INTO `area_codes` VALUES ('49','039455','Schierke');
INSERT INTO `area_codes` VALUES ('49','039456','Altenbrak');
INSERT INTO `area_codes` VALUES ('49','039457','Benneckenstein Harz');
INSERT INTO `area_codes` VALUES ('49','039458','Heudeber');
INSERT INTO `area_codes` VALUES ('49','039459','Hasselfelde');
INSERT INTO `area_codes` VALUES ('49','03946','Quedlinburg');
INSERT INTO `area_codes` VALUES ('49','03947','Thale');
INSERT INTO `area_codes` VALUES ('49','039481','Hedersleben b Aschersleben');
INSERT INTO `area_codes` VALUES ('49','039482','Gatersleben');
INSERT INTO `area_codes` VALUES ('49','039483','Ballenstedt');
INSERT INTO `area_codes` VALUES ('49','039484','Harzgerode');
INSERT INTO `area_codes` VALUES ('49','039485','Gernrode Harz');
INSERT INTO `area_codes` VALUES ('49','039487','Friedrichsbrunn');
INSERT INTO `area_codes` VALUES ('49','039488','Güntersberge');
INSERT INTO `area_codes` VALUES ('49','039489','Strassberg Harz');
INSERT INTO `area_codes` VALUES ('49','03949','Oschersleben Bode');
INSERT INTO `area_codes` VALUES ('49','0395','Neubrandenburg');
INSERT INTO `area_codes` VALUES ('49','039600','Zwiedorf');
INSERT INTO `area_codes` VALUES ('49','039601','Friedland Meckl');
INSERT INTO `area_codes` VALUES ('49','039602','Kleeth');
INSERT INTO `area_codes` VALUES ('49','039603','Burg Stargard');
INSERT INTO `area_codes` VALUES ('49','039604','Wildberg b Altentreptow');
INSERT INTO `area_codes` VALUES ('49','039605','Gross Nemerow');
INSERT INTO `area_codes` VALUES ('49','039606','Glienke');
INSERT INTO `area_codes` VALUES ('49','039607','Kotelow');
INSERT INTO `area_codes` VALUES ('49','039608','Staven');
INSERT INTO `area_codes` VALUES ('49','03961','Altentreptow');
INSERT INTO `area_codes` VALUES ('49','03962','Penzlin b Waren');
INSERT INTO `area_codes` VALUES ('49','03963','Woldegk');
INSERT INTO `area_codes` VALUES ('49','03964','Bredenfelde b Strasburg');
INSERT INTO `area_codes` VALUES ('49','03965','Burow b Altentreptow');
INSERT INTO `area_codes` VALUES ('49','03966','Cölpin');
INSERT INTO `area_codes` VALUES ('49','03967','Oertzenhof b Strasburg');
INSERT INTO `area_codes` VALUES ('49','03968','Schönbeck Meckl');
INSERT INTO `area_codes` VALUES ('49','03969','Siedenbollentin');
INSERT INTO `area_codes` VALUES ('49','03971','Anklam');
INSERT INTO `area_codes` VALUES ('49','039721','Liepen b Anklam');
INSERT INTO `area_codes` VALUES ('49','039722','Sarnow b Anklam');
INSERT INTO `area_codes` VALUES ('49','039723','Krien');
INSERT INTO `area_codes` VALUES ('49','039724','Klein Bünzow');
INSERT INTO `area_codes` VALUES ('49','039726','Ducherow');
INSERT INTO `area_codes` VALUES ('49','039727','Spantekow');
INSERT INTO `area_codes` VALUES ('49','039728','Medow b Anklam');
INSERT INTO `area_codes` VALUES ('49','03973','Pasewalk');
INSERT INTO `area_codes` VALUES ('49','039740','Nechlin');
INSERT INTO `area_codes` VALUES ('49','039741','Jatznick');
INSERT INTO `area_codes` VALUES ('49','039742','Brüssow b Pasewalk');
INSERT INTO `area_codes` VALUES ('49','039743','Zerrenthin');
INSERT INTO `area_codes` VALUES ('49','039744','Rothenklempenow');
INSERT INTO `area_codes` VALUES ('49','039745','Hetzdorf b Strasburg');
INSERT INTO `area_codes` VALUES ('49','039746','Krackow');
INSERT INTO `area_codes` VALUES ('49','039747','Züsedom');
INSERT INTO `area_codes` VALUES ('49','039748','Viereck');
INSERT INTO `area_codes` VALUES ('49','039749','Grambow b Pasewalk');
INSERT INTO `area_codes` VALUES ('49','039751','Penkun');
INSERT INTO `area_codes` VALUES ('49','039752','Blumenhagen b Strasburg');
INSERT INTO `area_codes` VALUES ('49','039753','Strasburg');
INSERT INTO `area_codes` VALUES ('49','039754','Löcknitz Vorpom');
INSERT INTO `area_codes` VALUES ('49','03976','Torgelow b Ueckermünde');
INSERT INTO `area_codes` VALUES ('49','039771','Ueckermünde');
INSERT INTO `area_codes` VALUES ('49','039772','Rothemühl');
INSERT INTO `area_codes` VALUES ('49','039773','Altwarp');
INSERT INTO `area_codes` VALUES ('49','039774','Mönkebude');
INSERT INTO `area_codes` VALUES ('49','039775','Ahlbeck b Torgelow');
INSERT INTO `area_codes` VALUES ('49','039776','Hintersee');
INSERT INTO `area_codes` VALUES ('49','039777','Borkenfriede');
INSERT INTO `area_codes` VALUES ('49','039778','Ferdinandshof b Torgelow');
INSERT INTO `area_codes` VALUES ('49','039779','Eggesin');
INSERT INTO `area_codes` VALUES ('49','03981','Neustrelitz');
INSERT INTO `area_codes` VALUES ('49','039820','Triepkendorf');
INSERT INTO `area_codes` VALUES ('49','039821','Carpin');
INSERT INTO `area_codes` VALUES ('49','039822','Kratzeburg');
INSERT INTO `area_codes` VALUES ('49','039823','Rechlin');
INSERT INTO `area_codes` VALUES ('49','039824','Hohenzieritz');
INSERT INTO `area_codes` VALUES ('49','039825','Wokuhl');
INSERT INTO `area_codes` VALUES ('49','039826','Blankensee b Neustrelitz');
INSERT INTO `area_codes` VALUES ('49','039827','Schwarz b Neustrelitz');
INSERT INTO `area_codes` VALUES ('49','039828','Wustrow Kr Mecklenburg-Strelitz');
INSERT INTO `area_codes` VALUES ('49','039829','Blankenförde');
INSERT INTO `area_codes` VALUES ('49','039831','Feldberg Meckl');
INSERT INTO `area_codes` VALUES ('49','039832','Wesenberg Meckl');
INSERT INTO `area_codes` VALUES ('49','039833','Mirow Kr Neustrelitz');
INSERT INTO `area_codes` VALUES ('49','03984','Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039851','Göritz b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039852','Schönermark b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039853','Holzendorf b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039854','Kleptow');
INSERT INTO `area_codes` VALUES ('49','039855','Parmen-Weggun');
INSERT INTO `area_codes` VALUES ('49','039856','Beenz b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039857','Drense');
INSERT INTO `area_codes` VALUES ('49','039858','Bietikow');
INSERT INTO `area_codes` VALUES ('49','039859','Fürstenwerder');
INSERT INTO `area_codes` VALUES ('49','039861','Gramzow b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039862','Schmölln b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039863','Seehausen b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','03987','Templin');
INSERT INTO `area_codes` VALUES ('49','039881','Ringenwalde b Templin');
INSERT INTO `area_codes` VALUES ('49','039882','Gollin');
INSERT INTO `area_codes` VALUES ('49','039883','Groß Dölln');
INSERT INTO `area_codes` VALUES ('49','039884','Hassleben b Prenzlau');
INSERT INTO `area_codes` VALUES ('49','039885','Jakobshagen');
INSERT INTO `area_codes` VALUES ('49','039886','Milmersdorf');
INSERT INTO `area_codes` VALUES ('49','039887','Gerswalde');
INSERT INTO `area_codes` VALUES ('49','039888','Lychen');
INSERT INTO `area_codes` VALUES ('49','039889','Boitzenburg');
INSERT INTO `area_codes` VALUES ('49','03991','Waren Müritz');
INSERT INTO `area_codes` VALUES ('49','039921','Ankershagen');
INSERT INTO `area_codes` VALUES ('49','039922','Dambeck b Röbel');
INSERT INTO `area_codes` VALUES ('49','039923','Priborn');
INSERT INTO `area_codes` VALUES ('49','039924','Stuer');
INSERT INTO `area_codes` VALUES ('49','039925','Wredenhagen');
INSERT INTO `area_codes` VALUES ('49','039926','Grabowhöfe');
INSERT INTO `area_codes` VALUES ('49','039927','Nossentiner Hütte');
INSERT INTO `area_codes` VALUES ('49','039928','Möllenhagen');
INSERT INTO `area_codes` VALUES ('49','039929','Jabel b Waren');
INSERT INTO `area_codes` VALUES ('49','039931','Röbel Müritz');
INSERT INTO `area_codes` VALUES ('49','039932','Malchow b Waren');
INSERT INTO `area_codes` VALUES ('49','039933','Vollrathsruhe');
INSERT INTO `area_codes` VALUES ('49','039934','Groß Plasten');
INSERT INTO `area_codes` VALUES ('49','03994','Malchin');
INSERT INTO `area_codes` VALUES ('49','039951','Faulenrost');
INSERT INTO `area_codes` VALUES ('49','039952','Grammentin');
INSERT INTO `area_codes` VALUES ('49','039953','Schwinkendorf');
INSERT INTO `area_codes` VALUES ('49','039954','Stavenhagen Reuterstadt');
INSERT INTO `area_codes` VALUES ('49','039955','Jürgenstorf Meckl');
INSERT INTO `area_codes` VALUES ('49','039956','Neukalen');
INSERT INTO `area_codes` VALUES ('49','039957','Gielow');
INSERT INTO `area_codes` VALUES ('49','039959','Dargun');
INSERT INTO `area_codes` VALUES ('49','03996','Teterow');
INSERT INTO `area_codes` VALUES ('49','039971','Gnoien');
INSERT INTO `area_codes` VALUES ('49','039972','Walkendorf');
INSERT INTO `area_codes` VALUES ('49','039973','Altkalen');
INSERT INTO `area_codes` VALUES ('49','039975','Thürkow');
INSERT INTO `area_codes` VALUES ('49','039976','Groß Bützin');
INSERT INTO `area_codes` VALUES ('49','039977','Jördenstorf');
INSERT INTO `area_codes` VALUES ('49','039978','Gross Roge');
INSERT INTO `area_codes` VALUES ('49','03998','Demmin');
INSERT INTO `area_codes` VALUES ('49','039991','Daberkow');
INSERT INTO `area_codes` VALUES ('49','039992','Görmin');
INSERT INTO `area_codes` VALUES ('49','039993','Hohenmocker');
INSERT INTO `area_codes` VALUES ('49','039994','Metschow');
INSERT INTO `area_codes` VALUES ('49','039995','Nossendorf');
INSERT INTO `area_codes` VALUES ('49','039996','Törpin');
INSERT INTO `area_codes` VALUES ('49','039997','Jarmen');
INSERT INTO `area_codes` VALUES ('49','039998','Loitz b Demmin');
INSERT INTO `area_codes` VALUES ('49','039999','Tutow');
INSERT INTO `area_codes` VALUES ('49','040','Hamburg');
INSERT INTO `area_codes` VALUES ('49','04101','Pinneberg');
INSERT INTO `area_codes` VALUES ('49','04102','Ahrensburg');
INSERT INTO `area_codes` VALUES ('49','04103','Wedel');
INSERT INTO `area_codes` VALUES ('49','04104','Aumühle b Hamburg');
INSERT INTO `area_codes` VALUES ('49','04105','Seevetal');
INSERT INTO `area_codes` VALUES ('49','04106','Quickborn Kr Pinneberg');
INSERT INTO `area_codes` VALUES ('49','04107','Siek Kr Stormarn');
INSERT INTO `area_codes` VALUES ('49','04108','Rosengarten Kr Harburg');
INSERT INTO `area_codes` VALUES ('49','04109','Tangstedt Bz Hamburg');
INSERT INTO `area_codes` VALUES ('49','04120','Ellerhoop');
INSERT INTO `area_codes` VALUES ('49','04121','Elmshorn');
INSERT INTO `area_codes` VALUES ('49','04122','Uetersen');
INSERT INTO `area_codes` VALUES ('49','04123','Barmstedt');
INSERT INTO `area_codes` VALUES ('49','04124','Glückstadt');
INSERT INTO `area_codes` VALUES ('49','04125','Seestermühe');
INSERT INTO `area_codes` VALUES ('49','04126','Horst Holstein');
INSERT INTO `area_codes` VALUES ('49','04127','Westerhorn');
INSERT INTO `area_codes` VALUES ('49','04128','Kollmar');
INSERT INTO `area_codes` VALUES ('49','04129','Haseldorf');
INSERT INTO `area_codes` VALUES ('49','04131','Lüneburg');
INSERT INTO `area_codes` VALUES ('49','04132','Amelinghausen');
INSERT INTO `area_codes` VALUES ('49','04133','Wittorf Kr Lüneburg');
INSERT INTO `area_codes` VALUES ('49','04134','Embsen Kr Lünebeburg');
INSERT INTO `area_codes` VALUES ('49','04135','Kirchgellersen');
INSERT INTO `area_codes` VALUES ('49','04136','Scharnebeck');
INSERT INTO `area_codes` VALUES ('49','04137','Barendorf');
INSERT INTO `area_codes` VALUES ('49','04138','Betzendorf Kr Lünebeburg');
INSERT INTO `area_codes` VALUES ('49','04139','Hohnstorf Elbe');
INSERT INTO `area_codes` VALUES ('49','04140','Estorf Kr Stade');
INSERT INTO `area_codes` VALUES ('49','04141','Stade');
INSERT INTO `area_codes` VALUES ('49','04142','Steinkirchen Kr Stade');
INSERT INTO `area_codes` VALUES ('49','04143','Drochtersen');
INSERT INTO `area_codes` VALUES ('49','04144','Himmelpforten');
INSERT INTO `area_codes` VALUES ('49','04146','Stade-Bützfleth');
INSERT INTO `area_codes` VALUES ('49','04148','Drochtersen-Assel');
INSERT INTO `area_codes` VALUES ('49','04149','Fredenbeck');
INSERT INTO `area_codes` VALUES ('49','04151','Schwarzenbek');
INSERT INTO `area_codes` VALUES ('49','04152','Geesthacht');
INSERT INTO `area_codes` VALUES ('49','04153','Lauenburg Elbe');
INSERT INTO `area_codes` VALUES ('49','04154','Trittau');
INSERT INTO `area_codes` VALUES ('49','04155','Büchen');
INSERT INTO `area_codes` VALUES ('49','04156','Talkau');
INSERT INTO `area_codes` VALUES ('49','04158','Roseburg');
INSERT INTO `area_codes` VALUES ('49','04159','Basthorst');
INSERT INTO `area_codes` VALUES ('49','04161','Buxtehude');
INSERT INTO `area_codes` VALUES ('49','04162','Jork');
INSERT INTO `area_codes` VALUES ('49','04163','Horneburg Niederelbe');
INSERT INTO `area_codes` VALUES ('49','04164','Harsefeld');
INSERT INTO `area_codes` VALUES ('49','04165','Hollenstedt Nordheide');
INSERT INTO `area_codes` VALUES ('49','04166','Ahlerstedt');
INSERT INTO `area_codes` VALUES ('49','04167','Apensen');
INSERT INTO `area_codes` VALUES ('49','04168','Neu Wulmstorf-Elstorf');
INSERT INTO `area_codes` VALUES ('49','04169','Sauensiek');
INSERT INTO `area_codes` VALUES ('49','04171','Winsen Luhe');
INSERT INTO `area_codes` VALUES ('49','04172','Salzhausen');
INSERT INTO `area_codes` VALUES ('49','04173','Wulfsen');
INSERT INTO `area_codes` VALUES ('49','04174','Stelle Kr Harburg');
INSERT INTO `area_codes` VALUES ('49','04175','Egestorf Nordheide');
INSERT INTO `area_codes` VALUES ('49','04176','Marschacht');
INSERT INTO `area_codes` VALUES ('49','04177','Drage Elbe');
INSERT INTO `area_codes` VALUES ('49','04178','Radbruch');
INSERT INTO `area_codes` VALUES ('49','04179','Winsen-Tönnhausen');
INSERT INTO `area_codes` VALUES ('49','04180','Königsmoor');
INSERT INTO `area_codes` VALUES ('49','04181','Buchholz in der Nordheide');
INSERT INTO `area_codes` VALUES ('49','04182','Tostedt');
INSERT INTO `area_codes` VALUES ('49','04183','Jesteburg');
INSERT INTO `area_codes` VALUES ('49','04184','Hanstedt Nordheide');
INSERT INTO `area_codes` VALUES ('49','04185','Marxen Auetal');
INSERT INTO `area_codes` VALUES ('49','04186','Buchholz-Trelde');
INSERT INTO `area_codes` VALUES ('49','04187','Holm-Seppensen');
INSERT INTO `area_codes` VALUES ('49','04188','Welle Nordheide');
INSERT INTO `area_codes` VALUES ('49','04189','Undeloh');
INSERT INTO `area_codes` VALUES ('49','04191','Kaltenkirchen Holst');
INSERT INTO `area_codes` VALUES ('49','04192','Bad Bramstedt');
INSERT INTO `area_codes` VALUES ('49','04193','Henstedt-Ulzburg');
INSERT INTO `area_codes` VALUES ('49','04194','Sievershütten');
INSERT INTO `area_codes` VALUES ('49','04195','Hartenholm');
INSERT INTO `area_codes` VALUES ('49','04202','Achim b Bremen');
INSERT INTO `area_codes` VALUES ('49','04203','Weyhe b Bremen');
INSERT INTO `area_codes` VALUES ('49','04204','Thedinghausen');
INSERT INTO `area_codes` VALUES ('49','04205','Ottersberg');
INSERT INTO `area_codes` VALUES ('49','04206','Stuhr-Heiligenrode');
INSERT INTO `area_codes` VALUES ('49','04207','Oyten');
INSERT INTO `area_codes` VALUES ('49','04208','Grasberg');
INSERT INTO `area_codes` VALUES ('49','04209','Schwanewede');
INSERT INTO `area_codes` VALUES ('49','0421','Bremen');
INSERT INTO `area_codes` VALUES ('49','04221','Delmenhorst');
INSERT INTO `area_codes` VALUES ('49','04222','Ganderkesee');
INSERT INTO `area_codes` VALUES ('49','04223','Ganderkesee-Bookholzberg');
INSERT INTO `area_codes` VALUES ('49','04224','Gross Ippener');
INSERT INTO `area_codes` VALUES ('49','04230','Verden-Walle');
INSERT INTO `area_codes` VALUES ('49','04231','Verden Aller');
INSERT INTO `area_codes` VALUES ('49','04232','Langwedel Kr Verden');
INSERT INTO `area_codes` VALUES ('49','04233','Blender');
INSERT INTO `area_codes` VALUES ('49','04234','Dörverden');
INSERT INTO `area_codes` VALUES ('49','04235','Langwedel-Etelsen');
INSERT INTO `area_codes` VALUES ('49','04236','Kirchlinteln');
INSERT INTO `area_codes` VALUES ('49','04237','Bendingbostel');
INSERT INTO `area_codes` VALUES ('49','04238','Neddenaverbergen');
INSERT INTO `area_codes` VALUES ('49','04239','Dörverden-Westen');
INSERT INTO `area_codes` VALUES ('49','04240','Syke-Heiligenfelde');
INSERT INTO `area_codes` VALUES ('49','04241','Bassum');
INSERT INTO `area_codes` VALUES ('49','04242','Syke');
INSERT INTO `area_codes` VALUES ('49','04243','Twistringen');
INSERT INTO `area_codes` VALUES ('49','04244','Harpstedt');
INSERT INTO `area_codes` VALUES ('49','04245','Neuenkirchen b Bassum');
INSERT INTO `area_codes` VALUES ('49','04246','Twistringen-Heiligenloh');
INSERT INTO `area_codes` VALUES ('49','04247','Affinghausen');
INSERT INTO `area_codes` VALUES ('49','04248','Bassum-Neubruchhausen');
INSERT INTO `area_codes` VALUES ('49','04249','Bassum-Nordwohlde');
INSERT INTO `area_codes` VALUES ('49','04251','Hoya');
INSERT INTO `area_codes` VALUES ('49','04252','Bruchhausen-Vilsen');
INSERT INTO `area_codes` VALUES ('49','04253','Asendorf Kr Diepholz');
INSERT INTO `area_codes` VALUES ('49','04254','Eystrup');
INSERT INTO `area_codes` VALUES ('49','04255','Martfeld');
INSERT INTO `area_codes` VALUES ('49','04256','Hilgermissen');
INSERT INTO `area_codes` VALUES ('49','04257','Schweringen');
INSERT INTO `area_codes` VALUES ('49','04258','Schwarme');
INSERT INTO `area_codes` VALUES ('49','04260','Visselhövede-Wittorf');
INSERT INTO `area_codes` VALUES ('49','04261','Rotenburg Wümme');
INSERT INTO `area_codes` VALUES ('49','04262','Visselhövede');
INSERT INTO `area_codes` VALUES ('49','04263','Scheessel');
INSERT INTO `area_codes` VALUES ('49','04264','Sottrum Kr Rotenburg');
INSERT INTO `area_codes` VALUES ('49','04265','Fintel');
INSERT INTO `area_codes` VALUES ('49','04266','Brockel');
INSERT INTO `area_codes` VALUES ('49','04267','Lauenbrück');
INSERT INTO `area_codes` VALUES ('49','04268','Bötersen');
INSERT INTO `area_codes` VALUES ('49','04269','Ahausen-Kirchwalsede');
INSERT INTO `area_codes` VALUES ('49','04271','Sulingen');
INSERT INTO `area_codes` VALUES ('49','04272','Siedenburg');
INSERT INTO `area_codes` VALUES ('49','04273','Kirchdorf b Sulingen');
INSERT INTO `area_codes` VALUES ('49','04274','Varrel b Sulingen');
INSERT INTO `area_codes` VALUES ('49','04275','Ehrenburg');
INSERT INTO `area_codes` VALUES ('49','04276','Borstel b Sulingen');
INSERT INTO `area_codes` VALUES ('49','04277','Schwaförden');
INSERT INTO `area_codes` VALUES ('49','04281','Zeven');
INSERT INTO `area_codes` VALUES ('49','04282','Sittensen');
INSERT INTO `area_codes` VALUES ('49','04283','Tarmstedt');
INSERT INTO `area_codes` VALUES ('49','04284','Selsingen');
INSERT INTO `area_codes` VALUES ('49','04285','Rhade b Zeven');
INSERT INTO `area_codes` VALUES ('49','04286','Gyhum');
INSERT INTO `area_codes` VALUES ('49','04287','Heeslingen-Boitzen');
INSERT INTO `area_codes` VALUES ('49','04288','Horstedt Kr Rotenburg');
INSERT INTO `area_codes` VALUES ('49','04289','Kirchtimke');
INSERT INTO `area_codes` VALUES ('49','04292','Ritterhude');
INSERT INTO `area_codes` VALUES ('49','04293','Ottersberg-Fischerhude');
INSERT INTO `area_codes` VALUES ('49','04294','Riede Kr Verden');
INSERT INTO `area_codes` VALUES ('49','04295','Emtinghausen');
INSERT INTO `area_codes` VALUES ('49','04296','Schwanewede-Aschwarden');
INSERT INTO `area_codes` VALUES ('49','04297','Ottersberg-Posthausen');
INSERT INTO `area_codes` VALUES ('49','04298','Lilienthal');
INSERT INTO `area_codes` VALUES ('49','04302','Kirchbarkau');
INSERT INTO `area_codes` VALUES ('49','04303','Schlesen');
INSERT INTO `area_codes` VALUES ('49','04305','Westensee');
INSERT INTO `area_codes` VALUES ('49','04307','Raisdorf');
INSERT INTO `area_codes` VALUES ('49','04308','Schwedeneck');
INSERT INTO `area_codes` VALUES ('49','0431','Kiel');
INSERT INTO `area_codes` VALUES ('49','04320','Heidmühlen');
INSERT INTO `area_codes` VALUES ('49','04321','Neumünster');
INSERT INTO `area_codes` VALUES ('49','04322','Bordesholm');
INSERT INTO `area_codes` VALUES ('49','04323','Bornhöved');
INSERT INTO `area_codes` VALUES ('49','04324','Brokstedt');
INSERT INTO `area_codes` VALUES ('49','04326','Wankendorf');
INSERT INTO `area_codes` VALUES ('49','04327','Grossenaspe');
INSERT INTO `area_codes` VALUES ('49','04328','Rickling');
INSERT INTO `area_codes` VALUES ('49','04329','Langwedel Holst');
INSERT INTO `area_codes` VALUES ('49','04330','Emkendorf');
INSERT INTO `area_codes` VALUES ('49','04331','Rendsburg');
INSERT INTO `area_codes` VALUES ('49','04332','Hamdorf b Rendsburg');
INSERT INTO `area_codes` VALUES ('49','04333','Erfde');
INSERT INTO `area_codes` VALUES ('49','04334','Bredenbek b Rendsburg');
INSERT INTO `area_codes` VALUES ('49','04335','Hohn b Rendsburg');
INSERT INTO `area_codes` VALUES ('49','04336','Owschlag');
INSERT INTO `area_codes` VALUES ('49','04337','Jevenstedt');
INSERT INTO `area_codes` VALUES ('49','04338','Alt Duvenstedt');
INSERT INTO `area_codes` VALUES ('49','04339','Christiansholm');
INSERT INTO `area_codes` VALUES ('49','04340','Achterwehr');
INSERT INTO `area_codes` VALUES ('49','04342','Preetz Kr Plön');
INSERT INTO `area_codes` VALUES ('49','04343','Laboe');
INSERT INTO `area_codes` VALUES ('49','04344','Schönberg Holstein');
INSERT INTO `area_codes` VALUES ('49','04346','Gettorf');
INSERT INTO `area_codes` VALUES ('49','04347','Flintbek');
INSERT INTO `area_codes` VALUES ('49','04348','Schönkirchen');
INSERT INTO `area_codes` VALUES ('49','04349','Dänischenhagen');
INSERT INTO `area_codes` VALUES ('49','04351','Eckernförde');
INSERT INTO `area_codes` VALUES ('49','04352','Damp');
INSERT INTO `area_codes` VALUES ('49','04353','Ascheffel');
INSERT INTO `area_codes` VALUES ('49','04354','Fleckeby');
INSERT INTO `area_codes` VALUES ('49','04355','Rieseby');
INSERT INTO `area_codes` VALUES ('49','04356','Gross Wittensee');
INSERT INTO `area_codes` VALUES ('49','04357','Sehestedt Eider');
INSERT INTO `area_codes` VALUES ('49','04358','Loose b Eckernförde');
INSERT INTO `area_codes` VALUES ('49','04361','Oldenburg in Holstein');
INSERT INTO `area_codes` VALUES ('49','04362','Heiligenhafen');
INSERT INTO `area_codes` VALUES ('49','04363','Lensahn');
INSERT INTO `area_codes` VALUES ('49','04364','Dahme Kr Ostholstein');
INSERT INTO `area_codes` VALUES ('49','04365','Heringsdorf Holst');
INSERT INTO `area_codes` VALUES ('49','04366','Grömitz-Cismar');
INSERT INTO `area_codes` VALUES ('49','04367','Grossenbrode');
INSERT INTO `area_codes` VALUES ('49','04371','Burg auf Fehmarn');
INSERT INTO `area_codes` VALUES ('49','04372','Westfehmarn');
INSERT INTO `area_codes` VALUES ('49','04381','Lütjenburg');
INSERT INTO `area_codes` VALUES ('49','04382','Wangels');
INSERT INTO `area_codes` VALUES ('49','04383','Grebin');
INSERT INTO `area_codes` VALUES ('49','04384','Selent');
INSERT INTO `area_codes` VALUES ('49','04385','Hohenfelde b Kiel');
INSERT INTO `area_codes` VALUES ('49','04392','Nortorf b Neumünster');
INSERT INTO `area_codes` VALUES ('49','04393','Boostedt');
INSERT INTO `area_codes` VALUES ('49','04394','Bokhorst');
INSERT INTO `area_codes` VALUES ('49','04401','Brake Unterweser');
INSERT INTO `area_codes` VALUES ('49','04402','Rastede');
INSERT INTO `area_codes` VALUES ('49','04403','Bad Zwischenahn');
INSERT INTO `area_codes` VALUES ('49','04404','Elsfleth');
INSERT INTO `area_codes` VALUES ('49','04405','Edewecht');
INSERT INTO `area_codes` VALUES ('49','04406','Berne');
INSERT INTO `area_codes` VALUES ('49','04407','Wardenburg');
INSERT INTO `area_codes` VALUES ('49','04408','Hude Oldenburg');
INSERT INTO `area_codes` VALUES ('49','04409','Westerstede-Ocholt');
INSERT INTO `area_codes` VALUES ('49','0441','Oldenburg (Oldb)');
INSERT INTO `area_codes` VALUES ('49','04421','Wilhelmshaven');
INSERT INTO `area_codes` VALUES ('49','04422','Sande Kr Friesl');
INSERT INTO `area_codes` VALUES ('49','04423','Fedderwarden');
INSERT INTO `area_codes` VALUES ('49','04425','Wangerland-Hooksiel');
INSERT INTO `area_codes` VALUES ('49','04426','Wangerland-Horumersiel');
INSERT INTO `area_codes` VALUES ('49','04431','Wildeshausen');
INSERT INTO `area_codes` VALUES ('49','04432','Dötlingen-Brettorf');
INSERT INTO `area_codes` VALUES ('49','04433','Dötlingen');
INSERT INTO `area_codes` VALUES ('49','04434','Colnrade');
INSERT INTO `area_codes` VALUES ('49','04435','Grossenkneten');
INSERT INTO `area_codes` VALUES ('49','04441','Vechta');
INSERT INTO `area_codes` VALUES ('49','04442','Lohne Oldenburg');
INSERT INTO `area_codes` VALUES ('49','04443','Dinklage');
INSERT INTO `area_codes` VALUES ('49','04444','Goldenstedt');
INSERT INTO `area_codes` VALUES ('49','04445','Visbek Kr Vechta');
INSERT INTO `area_codes` VALUES ('49','04446','Bakum Kr Vechta');
INSERT INTO `area_codes` VALUES ('49','04447','Vechta-Langförden');
INSERT INTO `area_codes` VALUES ('49','04451','Varel Jadebusen');
INSERT INTO `area_codes` VALUES ('49','04452','Zetel-Neuenburg');
INSERT INTO `area_codes` VALUES ('49','04453','Zetel');
INSERT INTO `area_codes` VALUES ('49','04454','Jade');
INSERT INTO `area_codes` VALUES ('49','04455','Jade-Schweiburg');
INSERT INTO `area_codes` VALUES ('49','04456','Varel-Altjührden');
INSERT INTO `area_codes` VALUES ('49','04458','Wiefelstede-Spohle');
INSERT INTO `area_codes` VALUES ('49','04461','Jever');
INSERT INTO `area_codes` VALUES ('49','04462','Wittmund');
INSERT INTO `area_codes` VALUES ('49','04463','Wangerland');
INSERT INTO `area_codes` VALUES ('49','04464','Wittmund-Carolinensiel');
INSERT INTO `area_codes` VALUES ('49','04465','Friedeburg Ostfriesl');
INSERT INTO `area_codes` VALUES ('49','04466','Wittmund-Ardorf');
INSERT INTO `area_codes` VALUES ('49','04467','Wittmund-Funnix');
INSERT INTO `area_codes` VALUES ('49','04468','Friedeburg-Reepsholt');
INSERT INTO `area_codes` VALUES ('49','04469','Wangerooge');
INSERT INTO `area_codes` VALUES ('49','04471','Cloppenburg');
INSERT INTO `area_codes` VALUES ('49','04472','Lastrup');
INSERT INTO `area_codes` VALUES ('49','04473','Emstek');
INSERT INTO `area_codes` VALUES ('49','04474','Garrel');
INSERT INTO `area_codes` VALUES ('49','04475','Molbergen');
INSERT INTO `area_codes` VALUES ('49','04477','Lastrup-Hemmelte');
INSERT INTO `area_codes` VALUES ('49','04478','Cappeln Oldenburg');
INSERT INTO `area_codes` VALUES ('49','04479','Molbergen-Peheim');
INSERT INTO `area_codes` VALUES ('49','04480','Ovelgönne-Strückhausen');
INSERT INTO `area_codes` VALUES ('49','04481','Hatten-Sandkrug');
INSERT INTO `area_codes` VALUES ('49','04482','Hatten');
INSERT INTO `area_codes` VALUES ('49','04483','Ovelgönne-Großenmeer');
INSERT INTO `area_codes` VALUES ('49','04484','Hude-Wüsting');
INSERT INTO `area_codes` VALUES ('49','04485','Elsfleth-Huntorf');
INSERT INTO `area_codes` VALUES ('49','04486','Edewecht-Friedrichsfehn');
INSERT INTO `area_codes` VALUES ('49','04487','Grossenkneten-Huntlosen');
INSERT INTO `area_codes` VALUES ('49','04488','Westerstede');
INSERT INTO `area_codes` VALUES ('49','04489','Apen');
INSERT INTO `area_codes` VALUES ('49','04491','Friesoythe');
INSERT INTO `area_codes` VALUES ('49','04492','Saterland');
INSERT INTO `area_codes` VALUES ('49','04493','Friesoythe-Gehlenberg');
INSERT INTO `area_codes` VALUES ('49','04494','Bösel Oldenburg');
INSERT INTO `area_codes` VALUES ('49','04495','Friesoythe-Thüle');
INSERT INTO `area_codes` VALUES ('49','04496','Friesoythe-Markhausen');
INSERT INTO `area_codes` VALUES ('49','04497','Barßel-Harkebrügge');
INSERT INTO `area_codes` VALUES ('49','04498','Saterland-Ramsloh');
INSERT INTO `area_codes` VALUES ('49','04499','Barssel');
INSERT INTO `area_codes` VALUES ('49','04501','Kastorf Holst');
INSERT INTO `area_codes` VALUES ('49','04502','Lübeck-Travemünde');
INSERT INTO `area_codes` VALUES ('49','04503','Timmendorfer Strand');
INSERT INTO `area_codes` VALUES ('49','04504','Ratekau');
INSERT INTO `area_codes` VALUES ('49','04505','Stockelsdorf-Curau');
INSERT INTO `area_codes` VALUES ('49','04506','Stockelsdorf-Krumbeck');
INSERT INTO `area_codes` VALUES ('49','04508','Krummesse');
INSERT INTO `area_codes` VALUES ('49','04509','Groß Grönau');
INSERT INTO `area_codes` VALUES ('49','0451','Lübeck');
INSERT INTO `area_codes` VALUES ('49','04521','Eutin');
INSERT INTO `area_codes` VALUES ('49','04522','Plön');
INSERT INTO `area_codes` VALUES ('49','04523','Malente');
INSERT INTO `area_codes` VALUES ('49','04524','Scharbeutz-Pönitz');
INSERT INTO `area_codes` VALUES ('49','04525','Ahrensbök');
INSERT INTO `area_codes` VALUES ('49','04526','Ascheberg Holstein');
INSERT INTO `area_codes` VALUES ('49','04527','Bosau');
INSERT INTO `area_codes` VALUES ('49','04528','Schönwalde am Bungsberg');
INSERT INTO `area_codes` VALUES ('49','04529','Süsel-Bujendorf');
INSERT INTO `area_codes` VALUES ('49','04531','Bad Oldesloe');
INSERT INTO `area_codes` VALUES ('49','04532','Bargteheide');
INSERT INTO `area_codes` VALUES ('49','04533','Reinfeld Holstein');
INSERT INTO `area_codes` VALUES ('49','04534','Steinburg Kr Storman');
INSERT INTO `area_codes` VALUES ('49','04535','Nahe');
INSERT INTO `area_codes` VALUES ('49','04536','Steinhorst Lauenb');
INSERT INTO `area_codes` VALUES ('49','04537','Sülfeld Holst');
INSERT INTO `area_codes` VALUES ('49','04539','Westerau');
INSERT INTO `area_codes` VALUES ('49','04541','Ratzeburg');
INSERT INTO `area_codes` VALUES ('49','04542','Mölln Lauenb');
INSERT INTO `area_codes` VALUES ('49','04543','Nusse');
INSERT INTO `area_codes` VALUES ('49','04544','Berkenthin');
INSERT INTO `area_codes` VALUES ('49','04545','Seedorf Lauenb');
INSERT INTO `area_codes` VALUES ('49','04546','Mustin Lauenburg');
INSERT INTO `area_codes` VALUES ('49','04547','Gudow Lauenb');
INSERT INTO `area_codes` VALUES ('49','04550','Bühnsdorf');
INSERT INTO `area_codes` VALUES ('49','04551','Bad Segeberg');
INSERT INTO `area_codes` VALUES ('49','04552','Leezen');
INSERT INTO `area_codes` VALUES ('49','04553','Geschendorf');
INSERT INTO `area_codes` VALUES ('49','04554','Wahlstedt');
INSERT INTO `area_codes` VALUES ('49','04555','Seedorf b Bad Segeberg');
INSERT INTO `area_codes` VALUES ('49','04556','Ahrensbök-Gnissau');
INSERT INTO `area_codes` VALUES ('49','04557','Blunk');
INSERT INTO `area_codes` VALUES ('49','04558','Todesfelde');
INSERT INTO `area_codes` VALUES ('49','04559','Wensin');
INSERT INTO `area_codes` VALUES ('49','04561','Neustadt in Holstein');
INSERT INTO `area_codes` VALUES ('49','04562','Grömitz');
INSERT INTO `area_codes` VALUES ('49','04563','Scharbeutz-Haffkrug');
INSERT INTO `area_codes` VALUES ('49','04564','Schashagen');
INSERT INTO `area_codes` VALUES ('49','04602','Freienwill');
INSERT INTO `area_codes` VALUES ('49','04603','Havetoft');
INSERT INTO `area_codes` VALUES ('49','04604','Grossenwiehe');
INSERT INTO `area_codes` VALUES ('49','04605','Medelby');
INSERT INTO `area_codes` VALUES ('49','04606','Wanderup');
INSERT INTO `area_codes` VALUES ('49','04607','Janneby');
INSERT INTO `area_codes` VALUES ('49','04608','Handewitt');
INSERT INTO `area_codes` VALUES ('49','04609','Eggebek');
INSERT INTO `area_codes` VALUES ('49','0461','Flensburg');
INSERT INTO `area_codes` VALUES ('49','04621','Schleswig');
INSERT INTO `area_codes` VALUES ('49','04622','Taarstedt');
INSERT INTO `area_codes` VALUES ('49','04623','Böklund');
INSERT INTO `area_codes` VALUES ('49','04624','Kropp');
INSERT INTO `area_codes` VALUES ('49','04625','Jübek');
INSERT INTO `area_codes` VALUES ('49','04626','Treia');
INSERT INTO `area_codes` VALUES ('49','04627','Dörpstedt');
INSERT INTO `area_codes` VALUES ('49','04630','Barderup');
INSERT INTO `area_codes` VALUES ('49','04631','Glücksburg Ostsee');
INSERT INTO `area_codes` VALUES ('49','04632','Steinbergkirche');
INSERT INTO `area_codes` VALUES ('49','04633','Satrup');
INSERT INTO `area_codes` VALUES ('49','04634','Husby');
INSERT INTO `area_codes` VALUES ('49','04635','Sörup');
INSERT INTO `area_codes` VALUES ('49','04636','Langballig');
INSERT INTO `area_codes` VALUES ('49','04637','Sterup');
INSERT INTO `area_codes` VALUES ('49','04638','Tarp');
INSERT INTO `area_codes` VALUES ('49','04639','Schafflund');
INSERT INTO `area_codes` VALUES ('49','04641','Süderbrarup');
INSERT INTO `area_codes` VALUES ('49','04642','Kappeln Schlei');
INSERT INTO `area_codes` VALUES ('49','04643','Gelting Angeln');
INSERT INTO `area_codes` VALUES ('49','04644','Karby');
INSERT INTO `area_codes` VALUES ('49','04646','Mohrkirch');
INSERT INTO `area_codes` VALUES ('49','04651','Sylt');
INSERT INTO `area_codes` VALUES ('49','04661','Niebüll');
INSERT INTO `area_codes` VALUES ('49','04662','Leck');
INSERT INTO `area_codes` VALUES ('49','04663','Süderlügum');
INSERT INTO `area_codes` VALUES ('49','04664','Neukirchen b Niebüll');
INSERT INTO `area_codes` VALUES ('49','04665','Emmelsbüll-Horsbüll');
INSERT INTO `area_codes` VALUES ('49','04666','Ladelund');
INSERT INTO `area_codes` VALUES ('49','04667','Dagebüll');
INSERT INTO `area_codes` VALUES ('49','04668','Klanxbüll');
INSERT INTO `area_codes` VALUES ('49','04671','Bredstedt');
INSERT INTO `area_codes` VALUES ('49','04672','Langenhorn');
INSERT INTO `area_codes` VALUES ('49','04673','Joldelund');
INSERT INTO `area_codes` VALUES ('49','04674','Ockholm');
INSERT INTO `area_codes` VALUES ('49','04681','Wyk auf Föhr');
INSERT INTO `area_codes` VALUES ('49','04682','Amrum');
INSERT INTO `area_codes` VALUES ('49','04683','Oldsum');
INSERT INTO `area_codes` VALUES ('49','04684','Langeneß Hallig');
INSERT INTO `area_codes` VALUES ('49','04702','Sandstedt');
INSERT INTO `area_codes` VALUES ('49','04703','Loxstedt-Donnern');
INSERT INTO `area_codes` VALUES ('49','04704','Drangstedt');
INSERT INTO `area_codes` VALUES ('49','04705','Wremen');
INSERT INTO `area_codes` VALUES ('49','04706','Schiffdorf');
INSERT INTO `area_codes` VALUES ('49','04707','Langen-Neuenwalde');
INSERT INTO `area_codes` VALUES ('49','04708','Ringstedt');
INSERT INTO `area_codes` VALUES ('49','0471','Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04721','Cuxhaven');
INSERT INTO `area_codes` VALUES ('49','04722','Cuxhaven-Altenbruch');
INSERT INTO `area_codes` VALUES ('49','04723','Cuxhaven-Altenwalde');
INSERT INTO `area_codes` VALUES ('49','04724','Cuxhaven-Lüdingworth');
INSERT INTO `area_codes` VALUES ('49','04725','Helgoland');
INSERT INTO `area_codes` VALUES ('49','04731','Nordenham');
INSERT INTO `area_codes` VALUES ('49','04732','Stadland-Rodenkirchen');
INSERT INTO `area_codes` VALUES ('49','04733','Butjadingen-Burhave');
INSERT INTO `area_codes` VALUES ('49','04734','Stadland-Seefeld');
INSERT INTO `area_codes` VALUES ('49','04735','Butjadingen-Stollhamm');
INSERT INTO `area_codes` VALUES ('49','04736','Butjadingen-Tossens');
INSERT INTO `area_codes` VALUES ('49','04737','Stadland-Schwei');
INSERT INTO `area_codes` VALUES ('49','04740','Loxstedt-Dedesdorf');
INSERT INTO `area_codes` VALUES ('49','04741','Nordholz b Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04742','Dorum');
INSERT INTO `area_codes` VALUES ('49','04743','Langen b Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04744','Loxstedt');
INSERT INTO `area_codes` VALUES ('49','04745','Bad Bederkesa');
INSERT INTO `area_codes` VALUES ('49','04746','Hagen b Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04747','Beverstedt');
INSERT INTO `area_codes` VALUES ('49','04748','Stubben b Bremerhaven');
INSERT INTO `area_codes` VALUES ('49','04749','Schiffdorf-Geestenseth');
INSERT INTO `area_codes` VALUES ('49','04751','Otterndorf');
INSERT INTO `area_codes` VALUES ('49','04752','Neuhaus Oste');
INSERT INTO `area_codes` VALUES ('49','04753','Balje');
INSERT INTO `area_codes` VALUES ('49','04754','Bülkau');
INSERT INTO `area_codes` VALUES ('49','04755','Ihlienworth');
INSERT INTO `area_codes` VALUES ('49','04756','Odisheim');
INSERT INTO `area_codes` VALUES ('49','04757','Wanna');
INSERT INTO `area_codes` VALUES ('49','04758','Nordleda');
INSERT INTO `area_codes` VALUES ('49','04761','Bremervörde');
INSERT INTO `area_codes` VALUES ('49','04762','Kutenholz');
INSERT INTO `area_codes` VALUES ('49','04763','Gnarrenburg');
INSERT INTO `area_codes` VALUES ('49','04764','Gnarrenburg-Klenkendorf');
INSERT INTO `area_codes` VALUES ('49','04765','Ebersdorf b Bremervörde');
INSERT INTO `area_codes` VALUES ('49','04766','Basdahl');
INSERT INTO `area_codes` VALUES ('49','04767','Bremervörde-Bevern');
INSERT INTO `area_codes` VALUES ('49','04768','Hipstedt');
INSERT INTO `area_codes` VALUES ('49','04769','Bremervörde-Iselersheim');
INSERT INTO `area_codes` VALUES ('49','04770','Wischhafen');
INSERT INTO `area_codes` VALUES ('49','04771','Hemmoor');
INSERT INTO `area_codes` VALUES ('49','04772','Oberndorf Oste');
INSERT INTO `area_codes` VALUES ('49','04773','Lamstedt');
INSERT INTO `area_codes` VALUES ('49','04774','Hechthausen');
INSERT INTO `area_codes` VALUES ('49','04775','Grossenwörden');
INSERT INTO `area_codes` VALUES ('49','04776','Osten-Altendorf');
INSERT INTO `area_codes` VALUES ('49','04777','Cadenberge');
INSERT INTO `area_codes` VALUES ('49','04778','Wingst');
INSERT INTO `area_codes` VALUES ('49','04779','Freiburg Elbe');
INSERT INTO `area_codes` VALUES ('49','04791','Osterholz-Scharmbeck');
INSERT INTO `area_codes` VALUES ('49','04792','Worpswede');
INSERT INTO `area_codes` VALUES ('49','04793','Hambergen');
INSERT INTO `area_codes` VALUES ('49','04794','Worpswede-Ostersode');
INSERT INTO `area_codes` VALUES ('49','04795','Garlstedt');
INSERT INTO `area_codes` VALUES ('49','04796','Teufelsmoor');
INSERT INTO `area_codes` VALUES ('49','04802','Wrohm');
INSERT INTO `area_codes` VALUES ('49','04803','Pahlen');
INSERT INTO `area_codes` VALUES ('49','04804','Nordhastedt');
INSERT INTO `area_codes` VALUES ('49','04805','Schafstedt');
INSERT INTO `area_codes` VALUES ('49','04806','Sarzbüttel');
INSERT INTO `area_codes` VALUES ('49','0481','Heide Holst');
INSERT INTO `area_codes` VALUES ('49','04821','Itzehoe');
INSERT INTO `area_codes` VALUES ('49','04822','Kellinghusen');
INSERT INTO `area_codes` VALUES ('49','04823','Wilster');
INSERT INTO `area_codes` VALUES ('49','04824','Krempe');
INSERT INTO `area_codes` VALUES ('49','04825','Burg Dithmarschen');
INSERT INTO `area_codes` VALUES ('49','04826','Hohenlockstedt');
INSERT INTO `area_codes` VALUES ('49','04827','Wacken');
INSERT INTO `area_codes` VALUES ('49','04828','Lägerdorf');
INSERT INTO `area_codes` VALUES ('49','04829','Wewelsfleth');
INSERT INTO `area_codes` VALUES ('49','04830','Süderhastedt');
INSERT INTO `area_codes` VALUES ('49','04832','Meldorf');
INSERT INTO `area_codes` VALUES ('49','04833','Wesselburen');
INSERT INTO `area_codes` VALUES ('49','04834','Büsum');
INSERT INTO `area_codes` VALUES ('49','04835','Albersdorf Holst');
INSERT INTO `area_codes` VALUES ('49','04836','Hennstedt Dithm');
INSERT INTO `area_codes` VALUES ('49','04837','Neuenkirchen Dithm');
INSERT INTO `area_codes` VALUES ('49','04838','Tellingstedt');
INSERT INTO `area_codes` VALUES ('49','04839','Wöhrden Dithm');
INSERT INTO `area_codes` VALUES ('49','04841','Husum Nordsee');
INSERT INTO `area_codes` VALUES ('49','04842','Nordstrand');
INSERT INTO `area_codes` VALUES ('49','04843','Viöl');
INSERT INTO `area_codes` VALUES ('49','04844','Pellworm');
INSERT INTO `area_codes` VALUES ('49','04845','Ostenfeld Husum');
INSERT INTO `area_codes` VALUES ('49','04846','Hattstedt');
INSERT INTO `area_codes` VALUES ('49','04847','Oster-Ohrstedt');
INSERT INTO `area_codes` VALUES ('49','04848','Rantrum');
INSERT INTO `area_codes` VALUES ('49','04849','Hooge');
INSERT INTO `area_codes` VALUES ('49','04851','Marne');
INSERT INTO `area_codes` VALUES ('49','04852','Brunsbüttel');
INSERT INTO `area_codes` VALUES ('49','04853','Sankt Michaelisdonn');
INSERT INTO `area_codes` VALUES ('49','04854','Friedrichskoog');
INSERT INTO `area_codes` VALUES ('49','04855','Eddelak');
INSERT INTO `area_codes` VALUES ('49','04856','Kronprinzenkoog');
INSERT INTO `area_codes` VALUES ('49','04857','Barlt');
INSERT INTO `area_codes` VALUES ('49','04858','Sankt Margarethen Holst');
INSERT INTO `area_codes` VALUES ('49','04859','Windbergen');
INSERT INTO `area_codes` VALUES ('49','04861','Tönning');
INSERT INTO `area_codes` VALUES ('49','04862','Garding');
INSERT INTO `area_codes` VALUES ('49','04863','Sankt Peter-Ording');
INSERT INTO `area_codes` VALUES ('49','04864','Oldenswort');
INSERT INTO `area_codes` VALUES ('49','04865','Osterhever');
INSERT INTO `area_codes` VALUES ('49','04871','Hohenwestedt');
INSERT INTO `area_codes` VALUES ('49','04872','Hanerau-Hademarschen');
INSERT INTO `area_codes` VALUES ('49','04873','Aukrug');
INSERT INTO `area_codes` VALUES ('49','04874','Todenbüttel');
INSERT INTO `area_codes` VALUES ('49','04875','Stafstedt');
INSERT INTO `area_codes` VALUES ('49','04876','Reher Holst');
INSERT INTO `area_codes` VALUES ('49','04877','Hennstedt b Itzehoe');
INSERT INTO `area_codes` VALUES ('49','04881','Friedrichstadt');
INSERT INTO `area_codes` VALUES ('49','04882','Lunden');
INSERT INTO `area_codes` VALUES ('49','04883','Süderstapel');
INSERT INTO `area_codes` VALUES ('49','04884','Schwabstedt');
INSERT INTO `area_codes` VALUES ('49','04885','Bergenhusen');
INSERT INTO `area_codes` VALUES ('49','04892','Schenefeld Mittelholst');
INSERT INTO `area_codes` VALUES ('49','04893','Hohenaspe');
INSERT INTO `area_codes` VALUES ('49','04902','Jemgum-Ditzum');
INSERT INTO `area_codes` VALUES ('49','04903','Wymeer');
INSERT INTO `area_codes` VALUES ('49','0491','Leer Ostfriesland');
INSERT INTO `area_codes` VALUES ('49','04920','Wirdum');
INSERT INTO `area_codes` VALUES ('49','04921','Emden Stadt');
INSERT INTO `area_codes` VALUES ('49','04922','Borkum');
INSERT INTO `area_codes` VALUES ('49','04923','Krummhörn-Pewsum');
INSERT INTO `area_codes` VALUES ('49','04924','Moormerland-Oldersum');
INSERT INTO `area_codes` VALUES ('49','04925','Hinte');
INSERT INTO `area_codes` VALUES ('49','04926','Krummhörn-Greetsiel');
INSERT INTO `area_codes` VALUES ('49','04927','Krummhörn-Loquard');
INSERT INTO `area_codes` VALUES ('49','04928','Ihlow-Riepe');
INSERT INTO `area_codes` VALUES ('49','04929','Ihlow Kr Aurich');
INSERT INTO `area_codes` VALUES ('49','04931','Norden');
INSERT INTO `area_codes` VALUES ('49','04932','Norderney');
INSERT INTO `area_codes` VALUES ('49','04933','Dornum Ostfriesl');
INSERT INTO `area_codes` VALUES ('49','04934','Marienhafe');
INSERT INTO `area_codes` VALUES ('49','04935','Juist');
INSERT INTO `area_codes` VALUES ('49','04936','Grossheide');
INSERT INTO `area_codes` VALUES ('49','04938','Hagermarsch');
INSERT INTO `area_codes` VALUES ('49','04939','Baltrum');
INSERT INTO `area_codes` VALUES ('49','04941','Aurich');
INSERT INTO `area_codes` VALUES ('49','04942','Südbrookmerland');
INSERT INTO `area_codes` VALUES ('49','04943','Grossefehn');
INSERT INTO `area_codes` VALUES ('49','04944','Wiesmoor');
INSERT INTO `area_codes` VALUES ('49','04945','Grossefehn-Timmel');
INSERT INTO `area_codes` VALUES ('49','04946','Grossefehn-Bagband');
INSERT INTO `area_codes` VALUES ('49','04947','Aurich-Ogenbargen');
INSERT INTO `area_codes` VALUES ('49','04948','Wiesmoor-Marcardsmoor');
INSERT INTO `area_codes` VALUES ('49','04950','Holtland');
INSERT INTO `area_codes` VALUES ('49','04951','Weener');
INSERT INTO `area_codes` VALUES ('49','04952','Rhauderfehn');
INSERT INTO `area_codes` VALUES ('49','04953','Bunde');
INSERT INTO `area_codes` VALUES ('49','04954','Moormerland');
INSERT INTO `area_codes` VALUES ('49','04955','Westoverledingen');
INSERT INTO `area_codes` VALUES ('49','04956','Uplengen');
INSERT INTO `area_codes` VALUES ('49','04957','Detern');
INSERT INTO `area_codes` VALUES ('49','04958','Jemgum');
INSERT INTO `area_codes` VALUES ('49','04959','Dollart');
INSERT INTO `area_codes` VALUES ('49','04961','Papenburg');
INSERT INTO `area_codes` VALUES ('49','04962','Papenburg-Aschendorf');
INSERT INTO `area_codes` VALUES ('49','04963','Dörpen');
INSERT INTO `area_codes` VALUES ('49','04964','Rhede Ems');
INSERT INTO `area_codes` VALUES ('49','04965','Surwold');
INSERT INTO `area_codes` VALUES ('49','04966','Neubörger');
INSERT INTO `area_codes` VALUES ('49','04967','Rhauderfehn-Burlage');
INSERT INTO `area_codes` VALUES ('49','04968','Neulehe');
INSERT INTO `area_codes` VALUES ('49','04971','Esens');
INSERT INTO `area_codes` VALUES ('49','04972','Langeoog');
INSERT INTO `area_codes` VALUES ('49','04973','Wittmund-Burhafe');
INSERT INTO `area_codes` VALUES ('49','04974','Neuharlingersiel');
INSERT INTO `area_codes` VALUES ('49','04975','Westerholt Ostfriesl');
INSERT INTO `area_codes` VALUES ('49','04976','Spiekeroog');
INSERT INTO `area_codes` VALUES ('49','04977','Blomberg Ostfriesl');
INSERT INTO `area_codes` VALUES ('49','05021','Nienburg Weser');
INSERT INTO `area_codes` VALUES ('49','05022','Wietzen');
INSERT INTO `area_codes` VALUES ('49','05023','Liebenau Kr Nienburg Weser');
INSERT INTO `area_codes` VALUES ('49','05024','Rohrsen Kr Nienburg Weser');
INSERT INTO `area_codes` VALUES ('49','05025','Estorf Weser');
INSERT INTO `area_codes` VALUES ('49','05026','Steimbke');
INSERT INTO `area_codes` VALUES ('49','05027','Linsburg');
INSERT INTO `area_codes` VALUES ('49','05028','Pennigsehl');
INSERT INTO `area_codes` VALUES ('49','05031','Wunstorf');
INSERT INTO `area_codes` VALUES ('49','05032','Neustadt am Rübenberge');
INSERT INTO `area_codes` VALUES ('49','05033','Wunstorf-Grossenheidorn');
INSERT INTO `area_codes` VALUES ('49','05034','Neustadt-Hagen');
INSERT INTO `area_codes` VALUES ('49','05035','Gross Munzel');
INSERT INTO `area_codes` VALUES ('49','05036','Neustadt-Schneeren');
INSERT INTO `area_codes` VALUES ('49','05037','Bad Rehburg');
INSERT INTO `area_codes` VALUES ('49','05041','Springe Deister');
INSERT INTO `area_codes` VALUES ('49','05042','Bad Münder am Deister');
INSERT INTO `area_codes` VALUES ('49','05043','Lauenau');
INSERT INTO `area_codes` VALUES ('49','05044','Springe-Eldagsen');
INSERT INTO `area_codes` VALUES ('49','05045','Springe-Bennigsen');
INSERT INTO `area_codes` VALUES ('49','05051','Bergen Kr Celle');
INSERT INTO `area_codes` VALUES ('49','05052','Hermannsburg');
INSERT INTO `area_codes` VALUES ('49','05053','Faßberg-Müden');
INSERT INTO `area_codes` VALUES ('49','05054','Bergen-Sülze');
INSERT INTO `area_codes` VALUES ('49','05055','Fassberg');
INSERT INTO `area_codes` VALUES ('49','05056','Winsen-Meissendorf');
INSERT INTO `area_codes` VALUES ('49','05060','Bodenburg');
INSERT INTO `area_codes` VALUES ('49','05062','Holle b Hildesheim');
INSERT INTO `area_codes` VALUES ('49','05063','Bad Salzdetfurth');
INSERT INTO `area_codes` VALUES ('49','05064','Groß Düngen');
INSERT INTO `area_codes` VALUES ('49','05065','Sibbesse');
INSERT INTO `area_codes` VALUES ('49','05066','Sarstedt');
INSERT INTO `area_codes` VALUES ('49','05067','Bockenem');
INSERT INTO `area_codes` VALUES ('49','05068','Elze Leine');
INSERT INTO `area_codes` VALUES ('49','05069','Nordstemmen');
INSERT INTO `area_codes` VALUES ('49','05071','Schwarmstedt');
INSERT INTO `area_codes` VALUES ('49','05072','Neustadt-Mandelsloh');
INSERT INTO `area_codes` VALUES ('49','05073','Neustadt-Esperke');
INSERT INTO `area_codes` VALUES ('49','05074','Rodewald');
INSERT INTO `area_codes` VALUES ('49','05082','Langlingen');
INSERT INTO `area_codes` VALUES ('49','05083','Hohne b Celle');
INSERT INTO `area_codes` VALUES ('49','05084','Hambühren');
INSERT INTO `area_codes` VALUES ('49','05085','Burgdorf-Ehlershausen');
INSERT INTO `area_codes` VALUES ('49','05086','Celle-Scheuen');
INSERT INTO `area_codes` VALUES ('49','05101','Pattensen');
INSERT INTO `area_codes` VALUES ('49','05102','Laatzen');
INSERT INTO `area_codes` VALUES ('49','05103','Wennigsen Deister');
INSERT INTO `area_codes` VALUES ('49','05105','Barsinghausen');
INSERT INTO `area_codes` VALUES ('49','05108','Gehrden Han');
INSERT INTO `area_codes` VALUES ('49','05109','Ronnenberg');
INSERT INTO `area_codes` VALUES ('49','0511','Hannover');
INSERT INTO `area_codes` VALUES ('49','05121','Hildesheim');
INSERT INTO `area_codes` VALUES ('49','05123','Schellerten');
INSERT INTO `area_codes` VALUES ('49','05126','Algermissen');
INSERT INTO `area_codes` VALUES ('49','05127','Harsum');
INSERT INTO `area_codes` VALUES ('49','05128','Hohenhameln');
INSERT INTO `area_codes` VALUES ('49','05129','Söhlde');
INSERT INTO `area_codes` VALUES ('49','05130','Wedemark');
INSERT INTO `area_codes` VALUES ('49','05131','Garbsen');
INSERT INTO `area_codes` VALUES ('49','05132','Lehrte');
INSERT INTO `area_codes` VALUES ('49','05135','Burgwedel-Fuhrberg');
INSERT INTO `area_codes` VALUES ('49','05136','Burgdorf Kr Hannover');
INSERT INTO `area_codes` VALUES ('49','05137','Seelze');
INSERT INTO `area_codes` VALUES ('49','05138','Sehnde');
INSERT INTO `area_codes` VALUES ('49','05139','Burgwedel');
INSERT INTO `area_codes` VALUES ('49','05141','Celle');
INSERT INTO `area_codes` VALUES ('49','05142','Eschede');
INSERT INTO `area_codes` VALUES ('49','05143','Winsen Aller');
INSERT INTO `area_codes` VALUES ('49','05144','Wathlingen');
INSERT INTO `area_codes` VALUES ('49','05145','Beedenbostel');
INSERT INTO `area_codes` VALUES ('49','05146','Wietze');
INSERT INTO `area_codes` VALUES ('49','05147','Uetze-Hänigsen');
INSERT INTO `area_codes` VALUES ('49','05148','Steinhorst Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05149','Wienhausen');
INSERT INTO `area_codes` VALUES ('49','05151','Hameln');
INSERT INTO `area_codes` VALUES ('49','05152','Hessisch Oldendorf');
INSERT INTO `area_codes` VALUES ('49','05153','Salzhemmendorf');
INSERT INTO `area_codes` VALUES ('49','05154','Aerzen');
INSERT INTO `area_codes` VALUES ('49','05155','Emmerthal');
INSERT INTO `area_codes` VALUES ('49','05156','Coppenbrügge');
INSERT INTO `area_codes` VALUES ('49','05157','Emmerthal-Börry');
INSERT INTO `area_codes` VALUES ('49','05158','Hemeringen');
INSERT INTO `area_codes` VALUES ('49','05159','Coppenbrügge-Bisperode');
INSERT INTO `area_codes` VALUES ('49','05161','Walsrode');
INSERT INTO `area_codes` VALUES ('49','05162','Fallingbostel');
INSERT INTO `area_codes` VALUES ('49','05163','Fallingbostel-Dorfmark');
INSERT INTO `area_codes` VALUES ('49','05164','Hodenhagen');
INSERT INTO `area_codes` VALUES ('49','05165','Rethem  Aller');
INSERT INTO `area_codes` VALUES ('49','05166','Walsrode-Kirchboitzen');
INSERT INTO `area_codes` VALUES ('49','05167','Walsrode-Westenholz');
INSERT INTO `area_codes` VALUES ('49','05168','Walsrode-Stellichte');
INSERT INTO `area_codes` VALUES ('49','05171','Peine');
INSERT INTO `area_codes` VALUES ('49','05172','Ilsede');
INSERT INTO `area_codes` VALUES ('49','05173','Uetze');
INSERT INTO `area_codes` VALUES ('49','05174','Lahstedt');
INSERT INTO `area_codes` VALUES ('49','05175','Lehrte-Arpke');
INSERT INTO `area_codes` VALUES ('49','05176','Edemissen');
INSERT INTO `area_codes` VALUES ('49','05177','Edemissen-Abbensen');
INSERT INTO `area_codes` VALUES ('49','05181','Alfeld Leine');
INSERT INTO `area_codes` VALUES ('49','05182','Gronau  Leine');
INSERT INTO `area_codes` VALUES ('49','05183','Lamspringe');
INSERT INTO `area_codes` VALUES ('49','05184','Freden Leine');
INSERT INTO `area_codes` VALUES ('49','05185','Duingen');
INSERT INTO `area_codes` VALUES ('49','05186','Salzhemmendorf-Wallensen');
INSERT INTO `area_codes` VALUES ('49','05187','Delligsen');
INSERT INTO `area_codes` VALUES ('49','05190','Soltau-Emmingen');
INSERT INTO `area_codes` VALUES ('49','05191','Soltau');
INSERT INTO `area_codes` VALUES ('49','05192','Munster');
INSERT INTO `area_codes` VALUES ('49','05193','Schneverdingen');
INSERT INTO `area_codes` VALUES ('49','05194','Bispingen');
INSERT INTO `area_codes` VALUES ('49','05195','Neuenkirchen b Soltau');
INSERT INTO `area_codes` VALUES ('49','05196','Wietzendorf');
INSERT INTO `area_codes` VALUES ('49','05197','Soltau-Frielingen');
INSERT INTO `area_codes` VALUES ('49','05198','Schneverdingen-Wintermoor');
INSERT INTO `area_codes` VALUES ('49','05199','Schneverdingen-Heber');
INSERT INTO `area_codes` VALUES ('49','05201','Halle Westf');
INSERT INTO `area_codes` VALUES ('49','05202','Oerlinghausen');
INSERT INTO `area_codes` VALUES ('49','05203','Werther Westf');
INSERT INTO `area_codes` VALUES ('49','05204','Steinhagen  Westf');
INSERT INTO `area_codes` VALUES ('49','05205','Bielefeld-Sennestadt');
INSERT INTO `area_codes` VALUES ('49','05206','Bielefeld-Jöllenbeck');
INSERT INTO `area_codes` VALUES ('49','05207','Schloss Holte-Stukenbrock');
INSERT INTO `area_codes` VALUES ('49','05208','Leopoldshöhe');
INSERT INTO `area_codes` VALUES ('49','05209','Gütersloh-Friedrichsdorf');
INSERT INTO `area_codes` VALUES ('49','0521','Bielefeld');
INSERT INTO `area_codes` VALUES ('49','05221','Herford');
INSERT INTO `area_codes` VALUES ('49','05222','Bad Salzuflen');
INSERT INTO `area_codes` VALUES ('49','05223','Bünde');
INSERT INTO `area_codes` VALUES ('49','05224','Enger Westf');
INSERT INTO `area_codes` VALUES ('49','05225','Spenge');
INSERT INTO `area_codes` VALUES ('49','05226','Bruchmühlen Westf');
INSERT INTO `area_codes` VALUES ('49','05228','Vlotho-Exter');
INSERT INTO `area_codes` VALUES ('49','05231','Detmold');
INSERT INTO `area_codes` VALUES ('49','05232','Lage Lippe');
INSERT INTO `area_codes` VALUES ('49','05233','Steinheim Westf');
INSERT INTO `area_codes` VALUES ('49','05234','Horn-Bad Meinberg');
INSERT INTO `area_codes` VALUES ('49','05235','Blomberg Lippe');
INSERT INTO `area_codes` VALUES ('49','05236','Blomberg-Grossenmarpe');
INSERT INTO `area_codes` VALUES ('49','05237','Augustdorf');
INSERT INTO `area_codes` VALUES ('49','05238','Nieheim-Himmighausen');
INSERT INTO `area_codes` VALUES ('49','05241','Gütersloh');
INSERT INTO `area_codes` VALUES ('49','05242','Rheda-Wiedenbrück');
INSERT INTO `area_codes` VALUES ('49','05244','Rietberg');
INSERT INTO `area_codes` VALUES ('49','05245','Herzebrock-Clarholz');
INSERT INTO `area_codes` VALUES ('49','05246','Verl');
INSERT INTO `area_codes` VALUES ('49','05247','Harsewinkel');
INSERT INTO `area_codes` VALUES ('49','05248','Langenberg Kr Gütersloh');
INSERT INTO `area_codes` VALUES ('49','05250','Delbrück Westf');
INSERT INTO `area_codes` VALUES ('49','05251','Paderborn');
INSERT INTO `area_codes` VALUES ('49','05252','Bad Lippspringe');
INSERT INTO `area_codes` VALUES ('49','05253','Bad Driburg');
INSERT INTO `area_codes` VALUES ('49','05254','Paderborn-Schloss Neuhaus');
INSERT INTO `area_codes` VALUES ('49','05255','Altenbeken');
INSERT INTO `area_codes` VALUES ('49','05257','Hövelhof');
INSERT INTO `area_codes` VALUES ('49','05258','Salzkotten');
INSERT INTO `area_codes` VALUES ('49','05259','Bad Driburg-Neuenheerse');
INSERT INTO `area_codes` VALUES ('49','05261','Lemgo');
INSERT INTO `area_codes` VALUES ('49','05262','Extertal');
INSERT INTO `area_codes` VALUES ('49','05263','Barntrup');
INSERT INTO `area_codes` VALUES ('49','05264','Kalletal');
INSERT INTO `area_codes` VALUES ('49','05265','Dörentrup');
INSERT INTO `area_codes` VALUES ('49','05266','Lemgo-Kirchheide');
INSERT INTO `area_codes` VALUES ('49','05271','Höxter');
INSERT INTO `area_codes` VALUES ('49','05272','Brakel Westf');
INSERT INTO `area_codes` VALUES ('49','05273','Beverungen');
INSERT INTO `area_codes` VALUES ('49','05274','Nieheim');
INSERT INTO `area_codes` VALUES ('49','05275','Höxter-Ottbergen');
INSERT INTO `area_codes` VALUES ('49','05276','Marienmünster');
INSERT INTO `area_codes` VALUES ('49','05277','Höxter-Fürstenau');
INSERT INTO `area_codes` VALUES ('49','05278','Höxter-Ovenhausen');
INSERT INTO `area_codes` VALUES ('49','05281','Bad Pyrmont');
INSERT INTO `area_codes` VALUES ('49','05282','Schieder-Schwalenberg');
INSERT INTO `area_codes` VALUES ('49','05283','Lügde-Rischenau');
INSERT INTO `area_codes` VALUES ('49','05284','Schwalenberg');
INSERT INTO `area_codes` VALUES ('49','05285','Bad Pyrmont-Kleinenberg');
INSERT INTO `area_codes` VALUES ('49','05286','Ottenstein Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05292','Lichtenau-Atteln');
INSERT INTO `area_codes` VALUES ('49','05293','Paderborn-Dahl');
INSERT INTO `area_codes` VALUES ('49','05294','Hövelhof-Espeln');
INSERT INTO `area_codes` VALUES ('49','05295','Lichtenau Westf');
INSERT INTO `area_codes` VALUES ('49','05300','Salzgitter-Üfingen');
INSERT INTO `area_codes` VALUES ('49','05301','Lehre-Essenrode');
INSERT INTO `area_codes` VALUES ('49','05302','Vechelde');
INSERT INTO `area_codes` VALUES ('49','05303','Wendeburg');
INSERT INTO `area_codes` VALUES ('49','05304','Meine');
INSERT INTO `area_codes` VALUES ('49','05305','Sickte');
INSERT INTO `area_codes` VALUES ('49','05306','Cremlingen');
INSERT INTO `area_codes` VALUES ('49','05307','Braunschweig-Wenden');
INSERT INTO `area_codes` VALUES ('49','05308','Lehre');
INSERT INTO `area_codes` VALUES ('49','05309','Lehre-Wendhausen');
INSERT INTO `area_codes` VALUES ('49','0531','Braunschweig');
INSERT INTO `area_codes` VALUES ('49','05320','Torfhaus');
INSERT INTO `area_codes` VALUES ('49','05321','Goslar');
INSERT INTO `area_codes` VALUES ('49','05322','Bad Harzburg');
INSERT INTO `area_codes` VALUES ('49','05323','Clausthal-Zellerfeld');
INSERT INTO `area_codes` VALUES ('49','05324','Vienenburg');
INSERT INTO `area_codes` VALUES ('49','05325','Goslar-Hahnenklee');
INSERT INTO `area_codes` VALUES ('49','05326','Langelsheim');
INSERT INTO `area_codes` VALUES ('49','05327','Bad Grund  Harz');
INSERT INTO `area_codes` VALUES ('49','05328','Altenau Harz');
INSERT INTO `area_codes` VALUES ('49','05329','Schulenberg im Oberharz');
INSERT INTO `area_codes` VALUES ('49','05331','Wolfenbüttel');
INSERT INTO `area_codes` VALUES ('49','05332','Schöppenstedt');
INSERT INTO `area_codes` VALUES ('49','05333','Dettum');
INSERT INTO `area_codes` VALUES ('49','05334','Hornburg Kr Wolfenbüttel');
INSERT INTO `area_codes` VALUES ('49','05335','Schladen');
INSERT INTO `area_codes` VALUES ('49','05336','Semmenstedt');
INSERT INTO `area_codes` VALUES ('49','05337','Kissenbrück');
INSERT INTO `area_codes` VALUES ('49','05339','Gielde');
INSERT INTO `area_codes` VALUES ('49','05341','Salzgitter');
INSERT INTO `area_codes` VALUES ('49','05344','Lengede');
INSERT INTO `area_codes` VALUES ('49','05345','Baddeckenstedt');
INSERT INTO `area_codes` VALUES ('49','05346','Liebenburg');
INSERT INTO `area_codes` VALUES ('49','05347','Burgdorf b Salzgitter');
INSERT INTO `area_codes` VALUES ('49','05351','Helmstedt');
INSERT INTO `area_codes` VALUES ('49','05352','Schöningen');
INSERT INTO `area_codes` VALUES ('49','05353','Königslutter am Elm');
INSERT INTO `area_codes` VALUES ('49','05354','Jerxheim');
INSERT INTO `area_codes` VALUES ('49','05355','Frellstedt');
INSERT INTO `area_codes` VALUES ('49','05356','Helmstedt-Barmke');
INSERT INTO `area_codes` VALUES ('49','05357','Grasleben');
INSERT INTO `area_codes` VALUES ('49','05358','Bahrdorf-Mackendorf');
INSERT INTO `area_codes` VALUES ('49','05361','Wolfsburg');
INSERT INTO `area_codes` VALUES ('49','05362','Wolfsburg-Fallersleben');
INSERT INTO `area_codes` VALUES ('49','05363','Wolfsburg-Vorsfelde');
INSERT INTO `area_codes` VALUES ('49','05364','Velpke');
INSERT INTO `area_codes` VALUES ('49','05365','Wolfsburg-Neindorf');
INSERT INTO `area_codes` VALUES ('49','05366','Jembke');
INSERT INTO `area_codes` VALUES ('49','05367','Rühen');
INSERT INTO `area_codes` VALUES ('49','05368','Parsau');
INSERT INTO `area_codes` VALUES ('49','05371','Gifhorn');
INSERT INTO `area_codes` VALUES ('49','05372','Meinersen');
INSERT INTO `area_codes` VALUES ('49','05373','Hillerse Kr Gifhorn');
INSERT INTO `area_codes` VALUES ('49','05374','Isenbüttel');
INSERT INTO `area_codes` VALUES ('49','05375','Müden Aller');
INSERT INTO `area_codes` VALUES ('49','05376','Wesendorf Kr Gifhorn');
INSERT INTO `area_codes` VALUES ('49','05377','Ehra-Lessien');
INSERT INTO `area_codes` VALUES ('49','05378','Sassenburg-Platendorf');
INSERT INTO `area_codes` VALUES ('49','05379','Sassenburg-Grussendorf');
INSERT INTO `area_codes` VALUES ('49','05381','Seesen');
INSERT INTO `area_codes` VALUES ('49','05382','Bad Gandersheim');
INSERT INTO `area_codes` VALUES ('49','05383','Lutter am Barenberge');
INSERT INTO `area_codes` VALUES ('49','05384','Seesen-Groß Rhüden');
INSERT INTO `area_codes` VALUES ('49','05401','Georgsmarienhütte');
INSERT INTO `area_codes` VALUES ('49','05402','Bissendorf Kr Osnabrück');
INSERT INTO `area_codes` VALUES ('49','05403','Bad Iburg');
INSERT INTO `area_codes` VALUES ('49','05404','Westerkappeln');
INSERT INTO `area_codes` VALUES ('49','05405','Hasbergen Kr Osnabrück');
INSERT INTO `area_codes` VALUES ('49','05406','Belm');
INSERT INTO `area_codes` VALUES ('49','05407','Wallenhorst');
INSERT INTO `area_codes` VALUES ('49','05409','Hilter am Teutoburger Wald');
INSERT INTO `area_codes` VALUES ('49','0541','Osnabrück');
INSERT INTO `area_codes` VALUES ('49','05421','Dissen am Teutoburger Wald');
INSERT INTO `area_codes` VALUES ('49','05422','Melle');
INSERT INTO `area_codes` VALUES ('49','05423','Versmold');
INSERT INTO `area_codes` VALUES ('49','05424','Bad Rothenfelde');
INSERT INTO `area_codes` VALUES ('49','05425','Borgholzhausen');
INSERT INTO `area_codes` VALUES ('49','05426','Glandorf');
INSERT INTO `area_codes` VALUES ('49','05427','Melle-Buer');
INSERT INTO `area_codes` VALUES ('49','05428','Melle-Neuenkirchen');
INSERT INTO `area_codes` VALUES ('49','05429','Melle-Wellingholzhausen');
INSERT INTO `area_codes` VALUES ('49','05431','Quakenbrück');
INSERT INTO `area_codes` VALUES ('49','05432','Löningen');
INSERT INTO `area_codes` VALUES ('49','05433','Badbergen');
INSERT INTO `area_codes` VALUES ('49','05434','Essen Oldenburg');
INSERT INTO `area_codes` VALUES ('49','05435','Berge b Quakenbrück');
INSERT INTO `area_codes` VALUES ('49','05436','Nortrup');
INSERT INTO `area_codes` VALUES ('49','05437','Menslage');
INSERT INTO `area_codes` VALUES ('49','05438','Bakum-Lüsche');
INSERT INTO `area_codes` VALUES ('49','05439','Bersenbrück');
INSERT INTO `area_codes` VALUES ('49','05441','Diepholz');
INSERT INTO `area_codes` VALUES ('49','05442','Barnstorf Kr Diepholz');
INSERT INTO `area_codes` VALUES ('49','05443','Lemförde');
INSERT INTO `area_codes` VALUES ('49','05444','Wagenfeld');
INSERT INTO `area_codes` VALUES ('49','05445','Drebber');
INSERT INTO `area_codes` VALUES ('49','05446','Rehden');
INSERT INTO `area_codes` VALUES ('49','05447','Lembruch');
INSERT INTO `area_codes` VALUES ('49','05448','Barver');
INSERT INTO `area_codes` VALUES ('49','05451','Ibbenbüren');
INSERT INTO `area_codes` VALUES ('49','05452','Mettingen Westf');
INSERT INTO `area_codes` VALUES ('49','05453','Recke');
INSERT INTO `area_codes` VALUES ('49','05454','Hörstel-Riesenbeck');
INSERT INTO `area_codes` VALUES ('49','05455','Tecklenburg-Brochterbeck');
INSERT INTO `area_codes` VALUES ('49','05456','Westerkappeln-Velpe');
INSERT INTO `area_codes` VALUES ('49','05457','Hopsten-Schale');
INSERT INTO `area_codes` VALUES ('49','05458','Hopsten');
INSERT INTO `area_codes` VALUES ('49','05459','Hörstel');
INSERT INTO `area_codes` VALUES ('49','05461','Bramsche Hase');
INSERT INTO `area_codes` VALUES ('49','05462','Ankum');
INSERT INTO `area_codes` VALUES ('49','05464','Alfhausen');
INSERT INTO `area_codes` VALUES ('49','05465','Neuenkirchen b Bramsche');
INSERT INTO `area_codes` VALUES ('49','05466','Merzen');
INSERT INTO `area_codes` VALUES ('49','05467','Voltlage');
INSERT INTO `area_codes` VALUES ('49','05468','Bramsche-Engter');
INSERT INTO `area_codes` VALUES ('49','05471','Bohmte');
INSERT INTO `area_codes` VALUES ('49','05472','Bad Essen');
INSERT INTO `area_codes` VALUES ('49','05473','Ostercappeln');
INSERT INTO `area_codes` VALUES ('49','05474','Stemwede-Dielingen');
INSERT INTO `area_codes` VALUES ('49','05475','Bohmte-Hunteburg');
INSERT INTO `area_codes` VALUES ('49','05476','Ostercappeln-Venne');
INSERT INTO `area_codes` VALUES ('49','05481','Lengerich Westf');
INSERT INTO `area_codes` VALUES ('49','05482','Tecklenburg');
INSERT INTO `area_codes` VALUES ('49','05483','Lienen');
INSERT INTO `area_codes` VALUES ('49','05484','Lienen-Kattenvenne');
INSERT INTO `area_codes` VALUES ('49','05485','Ladbergen');
INSERT INTO `area_codes` VALUES ('49','05491','Damme Dümmer');
INSERT INTO `area_codes` VALUES ('49','05492','Steinfeld Oldenburg');
INSERT INTO `area_codes` VALUES ('49','05493','Neuenkirchen Kr Vechta');
INSERT INTO `area_codes` VALUES ('49','05494','Holdorf Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05495','Vörden Kr Vechta');
INSERT INTO `area_codes` VALUES ('49','05502','Dransfeld');
INSERT INTO `area_codes` VALUES ('49','05503','Nörten-Hardenberg');
INSERT INTO `area_codes` VALUES ('49','05504','Friedland Kr Göttingen');
INSERT INTO `area_codes` VALUES ('49','05505','Hardegsen');
INSERT INTO `area_codes` VALUES ('49','05506','Adelebsen');
INSERT INTO `area_codes` VALUES ('49','05507','Ebergötzen');
INSERT INTO `area_codes` VALUES ('49','05508','Gleichen-Rittmarshausen');
INSERT INTO `area_codes` VALUES ('49','05509','Rosdorf Kr Göttingen');
INSERT INTO `area_codes` VALUES ('49','0551','Göttingen');
INSERT INTO `area_codes` VALUES ('49','05520','Braunlage');
INSERT INTO `area_codes` VALUES ('49','05521','Herzberg am Harz');
INSERT INTO `area_codes` VALUES ('49','05522','Osterode am Harz');
INSERT INTO `area_codes` VALUES ('49','05523','Bad Sachsa');
INSERT INTO `area_codes` VALUES ('49','05524','Bad Lauterberg im Harz');
INSERT INTO `area_codes` VALUES ('49','05525','Walkenried');
INSERT INTO `area_codes` VALUES ('49','05527','Duderstadt');
INSERT INTO `area_codes` VALUES ('49','05528','Gieboldehausen');
INSERT INTO `area_codes` VALUES ('49','05529','Rhumspringe');
INSERT INTO `area_codes` VALUES ('49','05531','Holzminden');
INSERT INTO `area_codes` VALUES ('49','05532','Stadtoldendorf');
INSERT INTO `area_codes` VALUES ('49','05533','Bodenwerder');
INSERT INTO `area_codes` VALUES ('49','05534','Eschershausen a d Lenne');
INSERT INTO `area_codes` VALUES ('49','05535','Polle');
INSERT INTO `area_codes` VALUES ('49','05536','Holzminden-Neuhaus');
INSERT INTO `area_codes` VALUES ('49','05541','Hann. Münden');
INSERT INTO `area_codes` VALUES ('49','05542','Witzenhausen');
INSERT INTO `area_codes` VALUES ('49','05543','Staufenberg Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05544','Reinhardshagen');
INSERT INTO `area_codes` VALUES ('49','05545','Hedemünden');
INSERT INTO `area_codes` VALUES ('49','05546','Scheden');
INSERT INTO `area_codes` VALUES ('49','05551','Northeim');
INSERT INTO `area_codes` VALUES ('49','05552','Katlenburg');
INSERT INTO `area_codes` VALUES ('49','05553','Kalefeld');
INSERT INTO `area_codes` VALUES ('49','05554','Moringen');
INSERT INTO `area_codes` VALUES ('49','05555','Moringen-Fredelsloh');
INSERT INTO `area_codes` VALUES ('49','05556','Lindau Harz');
INSERT INTO `area_codes` VALUES ('49','05561','Einbeck');
INSERT INTO `area_codes` VALUES ('49','05562','Dassel-Markoldendorf');
INSERT INTO `area_codes` VALUES ('49','05563','Kreiensen');
INSERT INTO `area_codes` VALUES ('49','05564','Dassel');
INSERT INTO `area_codes` VALUES ('49','05565','Einbeck-Wenzen');
INSERT INTO `area_codes` VALUES ('49','05571','Uslar');
INSERT INTO `area_codes` VALUES ('49','05572','Bodenfelde');
INSERT INTO `area_codes` VALUES ('49','05573','Uslar-Volpriehausen');
INSERT INTO `area_codes` VALUES ('49','05574','Oberweser');
INSERT INTO `area_codes` VALUES ('49','05582','Sankt Andreasberg');
INSERT INTO `area_codes` VALUES ('49','05583','Braunlage-Hohegeiss');
INSERT INTO `area_codes` VALUES ('49','05584','Hattorf am Harz');
INSERT INTO `area_codes` VALUES ('49','05585','Herzberg-Sieber');
INSERT INTO `area_codes` VALUES ('49','05586','Wieda');
INSERT INTO `area_codes` VALUES ('49','05592','Gleichen-Bremke');
INSERT INTO `area_codes` VALUES ('49','05593','Bovenden-Lenglern');
INSERT INTO `area_codes` VALUES ('49','05594','Bovenden-Reyershausen');
INSERT INTO `area_codes` VALUES ('49','05601','Schauenburg');
INSERT INTO `area_codes` VALUES ('49','05602','Hessisch Lichtenau');
INSERT INTO `area_codes` VALUES ('49','05603','Gudensberg');
INSERT INTO `area_codes` VALUES ('49','05604','Grossalmerode');
INSERT INTO `area_codes` VALUES ('49','05605','Kaufungen Hess');
INSERT INTO `area_codes` VALUES ('49','05606','Zierenberg');
INSERT INTO `area_codes` VALUES ('49','05607','Fuldatal');
INSERT INTO `area_codes` VALUES ('49','05608','Söhrewald');
INSERT INTO `area_codes` VALUES ('49','05609','Ahnatal');
INSERT INTO `area_codes` VALUES ('49','0561','Kassel');
INSERT INTO `area_codes` VALUES ('49','05621','Bad Wildungen');
INSERT INTO `area_codes` VALUES ('49','05622','Fritzlar');
INSERT INTO `area_codes` VALUES ('49','05623','Edertal');
INSERT INTO `area_codes` VALUES ('49','05624','Bad Emstal');
INSERT INTO `area_codes` VALUES ('49','05625','Naumburg Hess');
INSERT INTO `area_codes` VALUES ('49','05626','Bad Zwesten');
INSERT INTO `area_codes` VALUES ('49','05631','Korbach');
INSERT INTO `area_codes` VALUES ('49','05632','Willingen Upland');
INSERT INTO `area_codes` VALUES ('49','05633','Diemelsee');
INSERT INTO `area_codes` VALUES ('49','05634','Waldeck-Sachsenhausen');
INSERT INTO `area_codes` VALUES ('49','05635','Vöhl');
INSERT INTO `area_codes` VALUES ('49','05636','Lichtenfels-Goddelsheim');
INSERT INTO `area_codes` VALUES ('49','05641','Warburg');
INSERT INTO `area_codes` VALUES ('49','05642','Warburg-Scherfede');
INSERT INTO `area_codes` VALUES ('49','05643','Borgentreich');
INSERT INTO `area_codes` VALUES ('49','05644','Willebadessen-Peckelsheim');
INSERT INTO `area_codes` VALUES ('49','05645','Borgentreich-Borgholz');
INSERT INTO `area_codes` VALUES ('49','05646','Willebadessen');
INSERT INTO `area_codes` VALUES ('49','05647','Lichtenau-Kleinenberg');
INSERT INTO `area_codes` VALUES ('49','05648','Brakel-Gehrden');
INSERT INTO `area_codes` VALUES ('49','05650','Cornberg');
INSERT INTO `area_codes` VALUES ('49','05651','Eschwege');
INSERT INTO `area_codes` VALUES ('49','05652','Bad Sooden-Allendorf');
INSERT INTO `area_codes` VALUES ('49','05653','Sontra');
INSERT INTO `area_codes` VALUES ('49','05654','Herleshausen');
INSERT INTO `area_codes` VALUES ('49','05655','Wanfried');
INSERT INTO `area_codes` VALUES ('49','05656','Waldkappel');
INSERT INTO `area_codes` VALUES ('49','05657','Meissner');
INSERT INTO `area_codes` VALUES ('49','05658','Wehretal');
INSERT INTO `area_codes` VALUES ('49','05659','Ringgau');
INSERT INTO `area_codes` VALUES ('49','05661','Melsungen');
INSERT INTO `area_codes` VALUES ('49','05662','Felsberg Hess');
INSERT INTO `area_codes` VALUES ('49','05663','Spangenberg');
INSERT INTO `area_codes` VALUES ('49','05664','Morschen');
INSERT INTO `area_codes` VALUES ('49','05665','Guxhagen');
INSERT INTO `area_codes` VALUES ('49','05671','Hofgeismar');
INSERT INTO `area_codes` VALUES ('49','05672','Bad Karlshafen');
INSERT INTO `area_codes` VALUES ('49','05673','Immenhausen Hess');
INSERT INTO `area_codes` VALUES ('49','05674','Grebenstein');
INSERT INTO `area_codes` VALUES ('49','05675','Trendelburg');
INSERT INTO `area_codes` VALUES ('49','05676','Liebenau Hess');
INSERT INTO `area_codes` VALUES ('49','05677','Calden-Westuffeln');
INSERT INTO `area_codes` VALUES ('49','05681','Homberg Efze');
INSERT INTO `area_codes` VALUES ('49','05682','Borken Hessen');
INSERT INTO `area_codes` VALUES ('49','05683','Wabern Hess');
INSERT INTO `area_codes` VALUES ('49','05684','Frielendorf');
INSERT INTO `area_codes` VALUES ('49','05685','Knüllwald');
INSERT INTO `area_codes` VALUES ('49','05686','Schwarzenborn Knüll');
INSERT INTO `area_codes` VALUES ('49','05691','Bad Arolsen');
INSERT INTO `area_codes` VALUES ('49','05692','Wolfhagen');
INSERT INTO `area_codes` VALUES ('49','05693','Volkmarsen');
INSERT INTO `area_codes` VALUES ('49','05694','Diemelstadt');
INSERT INTO `area_codes` VALUES ('49','05695','Twistetal');
INSERT INTO `area_codes` VALUES ('49','05696','Bad Arolsen-Landau');
INSERT INTO `area_codes` VALUES ('49','05702','Petershagen-Lahde');
INSERT INTO `area_codes` VALUES ('49','05703','Hille');
INSERT INTO `area_codes` VALUES ('49','05704','Petershagen-Friedewalde');
INSERT INTO `area_codes` VALUES ('49','05705','Petershagen-Windheim');
INSERT INTO `area_codes` VALUES ('49','05706','Porta Westfalica');
INSERT INTO `area_codes` VALUES ('49','05707','Petershagen Weser');
INSERT INTO `area_codes` VALUES ('49','0571','Minden Westf');
INSERT INTO `area_codes` VALUES ('49','05721','Stadthagen');
INSERT INTO `area_codes` VALUES ('49','05722','Bückeburg');
INSERT INTO `area_codes` VALUES ('49','05723','Bad Nenndorf');
INSERT INTO `area_codes` VALUES ('49','05724','Obernkirchen');
INSERT INTO `area_codes` VALUES ('49','05725','Lindhorst b Stadthagen');
INSERT INTO `area_codes` VALUES ('49','05726','Wiedensahl');
INSERT INTO `area_codes` VALUES ('49','05731','Bad Oeynhausen');
INSERT INTO `area_codes` VALUES ('49','05732','Löhne');
INSERT INTO `area_codes` VALUES ('49','05733','Vlotho');
INSERT INTO `area_codes` VALUES ('49','05734','Bergkirchen Westf');
INSERT INTO `area_codes` VALUES ('49','05741','Lübbecke');
INSERT INTO `area_codes` VALUES ('49','05742','Preussisch Oldendorf');
INSERT INTO `area_codes` VALUES ('49','05743','Espelkamp-Gestringen');
INSERT INTO `area_codes` VALUES ('49','05744','Hüllhorst');
INSERT INTO `area_codes` VALUES ('49','05745','Stemwede-Levern');
INSERT INTO `area_codes` VALUES ('49','05746','Rödinghausen');
INSERT INTO `area_codes` VALUES ('49','05751','Rinteln');
INSERT INTO `area_codes` VALUES ('49','05752','Auetal-Hattendorf');
INSERT INTO `area_codes` VALUES ('49','05753','Auetal-Bernsen');
INSERT INTO `area_codes` VALUES ('49','05754','Extertal-Bremke');
INSERT INTO `area_codes` VALUES ('49','05755','Kalletal-Varenholz');
INSERT INTO `area_codes` VALUES ('49','05761','Stolzenau');
INSERT INTO `area_codes` VALUES ('49','05763','Uchte');
INSERT INTO `area_codes` VALUES ('49','05764','Steyerberg');
INSERT INTO `area_codes` VALUES ('49','05765','Raddestorf');
INSERT INTO `area_codes` VALUES ('49','05766','Rehburg-Loccum');
INSERT INTO `area_codes` VALUES ('49','05767','Warmsen');
INSERT INTO `area_codes` VALUES ('49','05768','Petershagen-Heimsen');
INSERT INTO `area_codes` VALUES ('49','05769','Steyerberg-Voigtei');
INSERT INTO `area_codes` VALUES ('49','05771','Rahden Westf');
INSERT INTO `area_codes` VALUES ('49','05772','Espelkamp');
INSERT INTO `area_codes` VALUES ('49','05773','Stemwede-Wehdem');
INSERT INTO `area_codes` VALUES ('49','05774','Wagenfeld-Ströhen');
INSERT INTO `area_codes` VALUES ('49','05775','Diepenau');
INSERT INTO `area_codes` VALUES ('49','05776','Preussisch Ströhen');
INSERT INTO `area_codes` VALUES ('49','05777','Diepenau-Essern');
INSERT INTO `area_codes` VALUES ('49','05802','Wrestedt');
INSERT INTO `area_codes` VALUES ('49','05803','Rosche');
INSERT INTO `area_codes` VALUES ('49','05804','Rätzlingen Kr Uelzen');
INSERT INTO `area_codes` VALUES ('49','05805','Oetzen');
INSERT INTO `area_codes` VALUES ('49','05806','Barum b Bad Bevensen');
INSERT INTO `area_codes` VALUES ('49','05807','Altenmedingen');
INSERT INTO `area_codes` VALUES ('49','05808','Gerdau');
INSERT INTO `area_codes` VALUES ('49','0581','Uelzen');
INSERT INTO `area_codes` VALUES ('49','05820','Suhlendorf');
INSERT INTO `area_codes` VALUES ('49','05821','Bad Bevensen');
INSERT INTO `area_codes` VALUES ('49','05822','Ebstorf');
INSERT INTO `area_codes` VALUES ('49','05823','Bienenbüttel');
INSERT INTO `area_codes` VALUES ('49','05824','Bad Bodenteich');
INSERT INTO `area_codes` VALUES ('49','05825','Wieren');
INSERT INTO `area_codes` VALUES ('49','05826','Suderburg');
INSERT INTO `area_codes` VALUES ('49','05827','Unterlüß');
INSERT INTO `area_codes` VALUES ('49','05828','Himbergen');
INSERT INTO `area_codes` VALUES ('49','05829','Wriedel');
INSERT INTO `area_codes` VALUES ('49','05831','Wittingen');
INSERT INTO `area_codes` VALUES ('49','05832','Hankensbüttel');
INSERT INTO `area_codes` VALUES ('49','05833','Brome');
INSERT INTO `area_codes` VALUES ('49','05834','Wittingen-Knesebeck');
INSERT INTO `area_codes` VALUES ('49','05835','Wahrenholz');
INSERT INTO `area_codes` VALUES ('49','05836','Wittingen-Radenbeck');
INSERT INTO `area_codes` VALUES ('49','05837','Sprakensehl');
INSERT INTO `area_codes` VALUES ('49','05838','Gross Oesingen');
INSERT INTO `area_codes` VALUES ('49','05839','Wittingen-Ohrdorf');
INSERT INTO `area_codes` VALUES ('49','05840','Schnackenburg');
INSERT INTO `area_codes` VALUES ('49','05841','Lüchow Wendland');
INSERT INTO `area_codes` VALUES ('49','05842','Schnega');
INSERT INTO `area_codes` VALUES ('49','05843','Wustrow');
INSERT INTO `area_codes` VALUES ('49','05844','Clenze');
INSERT INTO `area_codes` VALUES ('49','05845','Bergen Dumme');
INSERT INTO `area_codes` VALUES ('49','05846','Gartow Niedersachs');
INSERT INTO `area_codes` VALUES ('49','05848','Trebel');
INSERT INTO `area_codes` VALUES ('49','05849','Waddeweitz');
INSERT INTO `area_codes` VALUES ('49','05850','Neetze');
INSERT INTO `area_codes` VALUES ('49','05851','Dahlenburg');
INSERT INTO `area_codes` VALUES ('49','05852','Bleckede');
INSERT INTO `area_codes` VALUES ('49','05853','Neu Darchau');
INSERT INTO `area_codes` VALUES ('49','05854','Bleckede-Barskamp');
INSERT INTO `area_codes` VALUES ('49','05855','Nahrendorf');
INSERT INTO `area_codes` VALUES ('49','05857','Bleckede-Brackede');
INSERT INTO `area_codes` VALUES ('49','05858','Hitzacker-Wietzetze');
INSERT INTO `area_codes` VALUES ('49','05859','Thomasburg');
INSERT INTO `area_codes` VALUES ('49','05861','Dannenberg Elbe');
INSERT INTO `area_codes` VALUES ('49','05862','Hitzacker Elbe');
INSERT INTO `area_codes` VALUES ('49','05863','Zernien');
INSERT INTO `area_codes` VALUES ('49','05864','Jameln');
INSERT INTO `area_codes` VALUES ('49','05865','Gusborn');
INSERT INTO `area_codes` VALUES ('49','05872','Stoetze');
INSERT INTO `area_codes` VALUES ('49','05873','Eimke');
INSERT INTO `area_codes` VALUES ('49','05874','Soltendieck');
INSERT INTO `area_codes` VALUES ('49','05875','Emmendorf');
INSERT INTO `area_codes` VALUES ('49','05882','Gorleben');
INSERT INTO `area_codes` VALUES ('49','05883','Lemgow');
INSERT INTO `area_codes` VALUES ('49','05901','Fürstenau b Bramsche');
INSERT INTO `area_codes` VALUES ('49','05902','Freren');
INSERT INTO `area_codes` VALUES ('49','05903','Emsbüren');
INSERT INTO `area_codes` VALUES ('49','05904','Lengerich Emsl');
INSERT INTO `area_codes` VALUES ('49','05905','Beesten');
INSERT INTO `area_codes` VALUES ('49','05906','Lünne');
INSERT INTO `area_codes` VALUES ('49','05907','Geeste');
INSERT INTO `area_codes` VALUES ('49','05908','Wietmarschen-Lohne');
INSERT INTO `area_codes` VALUES ('49','05909','Wettrup');
INSERT INTO `area_codes` VALUES ('49','0591','Lingen (Ems)');
INSERT INTO `area_codes` VALUES ('49','05921','Nordhorn');
INSERT INTO `area_codes` VALUES ('49','05922','Bad Bentheim');
INSERT INTO `area_codes` VALUES ('49','05923','Schüttorf');
INSERT INTO `area_codes` VALUES ('49','05924','Bad Bentheim-Gildehaus');
INSERT INTO `area_codes` VALUES ('49','05925','Wietmarschen');
INSERT INTO `area_codes` VALUES ('49','05926','Engden');
INSERT INTO `area_codes` VALUES ('49','05931','Meppen');
INSERT INTO `area_codes` VALUES ('49','05932','Haren Ems');
INSERT INTO `area_codes` VALUES ('49','05933','Lathen');
INSERT INTO `area_codes` VALUES ('49','05934','Haren-Rütenbrock');
INSERT INTO `area_codes` VALUES ('49','05935','Twist-Schöninghsdorf');
INSERT INTO `area_codes` VALUES ('49','05936','Twist');
INSERT INTO `area_codes` VALUES ('49','05937','Geeste-Gross Hesepe');
INSERT INTO `area_codes` VALUES ('49','05939','Sustrum');
INSERT INTO `area_codes` VALUES ('49','05941','Neuenhaus Dinkel');
INSERT INTO `area_codes` VALUES ('49','05942','Uelsen');
INSERT INTO `area_codes` VALUES ('49','05943','Emlichheim');
INSERT INTO `area_codes` VALUES ('49','05944','Hoogstede');
INSERT INTO `area_codes` VALUES ('49','05945','Wilsum');
INSERT INTO `area_codes` VALUES ('49','05946','Georgsdorf');
INSERT INTO `area_codes` VALUES ('49','05947','Laar Vechte');
INSERT INTO `area_codes` VALUES ('49','05948','Itterbeck');
INSERT INTO `area_codes` VALUES ('49','05951','Werlte');
INSERT INTO `area_codes` VALUES ('49','05952','Sögel');
INSERT INTO `area_codes` VALUES ('49','05953','Börger');
INSERT INTO `area_codes` VALUES ('49','05954','Lorup');
INSERT INTO `area_codes` VALUES ('49','05955','Esterwegen');
INSERT INTO `area_codes` VALUES ('49','05956','Rastdorf');
INSERT INTO `area_codes` VALUES ('49','05957','Lindern Oldenburg');
INSERT INTO `area_codes` VALUES ('49','05961','Haselünne');
INSERT INTO `area_codes` VALUES ('49','05962','Herzlake');
INSERT INTO `area_codes` VALUES ('49','05963','Bawinkel');
INSERT INTO `area_codes` VALUES ('49','05964','Lähden');
INSERT INTO `area_codes` VALUES ('49','05965','Klein Berssen');
INSERT INTO `area_codes` VALUES ('49','05966','Meppen-Apeldorn');
INSERT INTO `area_codes` VALUES ('49','05971','Rheine');
INSERT INTO `area_codes` VALUES ('49','05973','Neuenkirchen Kr Steinfurt');
INSERT INTO `area_codes` VALUES ('49','05975','Rheine-Mesum');
INSERT INTO `area_codes` VALUES ('49','05976','Salzbergen');
INSERT INTO `area_codes` VALUES ('49','05977','Spelle');
INSERT INTO `area_codes` VALUES ('49','05978','Hörstel-Dreierwalde');
INSERT INTO `area_codes` VALUES ('49','06002','Ober-Mörlen');
INSERT INTO `area_codes` VALUES ('49','06003','Rosbach v d Höhe');
INSERT INTO `area_codes` VALUES ('49','06004','Lich-Eberstadt');
INSERT INTO `area_codes` VALUES ('49','06007','Rosbach-Rodheim');
INSERT INTO `area_codes` VALUES ('49','06008','Echzell');
INSERT INTO `area_codes` VALUES ('49','06020','Heigenbrücken');
INSERT INTO `area_codes` VALUES ('49','06021','Aschaffenburg');
INSERT INTO `area_codes` VALUES ('49','06022','Obernburg a Main');
INSERT INTO `area_codes` VALUES ('49','06023','Alzenau i Ufr');
INSERT INTO `area_codes` VALUES ('49','06024','Schöllkrippen');
INSERT INTO `area_codes` VALUES ('49','06026','Grossostheim');
INSERT INTO `area_codes` VALUES ('49','06027','Stockstadt a Main');
INSERT INTO `area_codes` VALUES ('49','06028','Sulzbach a Main');
INSERT INTO `area_codes` VALUES ('49','06029','Mömbris');
INSERT INTO `area_codes` VALUES ('49','06031','Friedberg Hess');
INSERT INTO `area_codes` VALUES ('49','06032','Bad Nauheim');
INSERT INTO `area_codes` VALUES ('49','06033','Butzbach');
INSERT INTO `area_codes` VALUES ('49','06034','Wöllstadt');
INSERT INTO `area_codes` VALUES ('49','06035','Reichelsheim Wetterau');
INSERT INTO `area_codes` VALUES ('49','06036','Wölfersheim');
INSERT INTO `area_codes` VALUES ('49','06039','Karben');
INSERT INTO `area_codes` VALUES ('49','06041','Glauburg');
INSERT INTO `area_codes` VALUES ('49','06042','Büdingen Hess');
INSERT INTO `area_codes` VALUES ('49','06043','Nidda');
INSERT INTO `area_codes` VALUES ('49','06044','Schotten Hess');
INSERT INTO `area_codes` VALUES ('49','06045','Gedern');
INSERT INTO `area_codes` VALUES ('49','06046','Ortenberg Hess');
INSERT INTO `area_codes` VALUES ('49','06047','Altenstadt Hess');
INSERT INTO `area_codes` VALUES ('49','06048','Büdingen-Eckartshausen');
INSERT INTO `area_codes` VALUES ('49','06049','Kefenrod');
INSERT INTO `area_codes` VALUES ('49','06050','Biebergemünd');
INSERT INTO `area_codes` VALUES ('49','06051','Gelnhausen');
INSERT INTO `area_codes` VALUES ('49','06052','Bad Orb');
INSERT INTO `area_codes` VALUES ('49','06053','Wächtersbach');
INSERT INTO `area_codes` VALUES ('49','06054','Birstein');
INSERT INTO `area_codes` VALUES ('49','06055','Freigericht');
INSERT INTO `area_codes` VALUES ('49','06056','Bad Soden-Salmünster');
INSERT INTO `area_codes` VALUES ('49','06057','Flörsbachtal');
INSERT INTO `area_codes` VALUES ('49','06058','Gründau');
INSERT INTO `area_codes` VALUES ('49','06059','Jossgrund');
INSERT INTO `area_codes` VALUES ('49','06061','Michelstadt');
INSERT INTO `area_codes` VALUES ('49','06062','Erbach Odenw');
INSERT INTO `area_codes` VALUES ('49','06063','Bad König');
INSERT INTO `area_codes` VALUES ('49','06066','Michelstadt-Vielbrunn');
INSERT INTO `area_codes` VALUES ('49','06068','Beerfelden');
INSERT INTO `area_codes` VALUES ('49','06071','Dieburg');
INSERT INTO `area_codes` VALUES ('49','06073','Babenhausen Hess');
INSERT INTO `area_codes` VALUES ('49','06074','Rödermark');
INSERT INTO `area_codes` VALUES ('49','06078','Gross-Umstadt');
INSERT INTO `area_codes` VALUES ('49','06081','Usingen');
INSERT INTO `area_codes` VALUES ('49','06082','Niederreifenberg');
INSERT INTO `area_codes` VALUES ('49','06083','Weilrod');
INSERT INTO `area_codes` VALUES ('49','06084','Schmitten Taunus');
INSERT INTO `area_codes` VALUES ('49','06085','Waldsolms');
INSERT INTO `area_codes` VALUES ('49','06086','Grävenwiesbach');
INSERT INTO `area_codes` VALUES ('49','06087','Waldems');
INSERT INTO `area_codes` VALUES ('49','06092','Heimbuchenthal');
INSERT INTO `area_codes` VALUES ('49','06093','Laufach');
INSERT INTO `area_codes` VALUES ('49','06094','Weibersbrunn');
INSERT INTO `area_codes` VALUES ('49','06095','Bessenbach');
INSERT INTO `area_codes` VALUES ('49','06096','Wiesen Unterfr');
INSERT INTO `area_codes` VALUES ('49','06101','Bad Vilbel');
INSERT INTO `area_codes` VALUES ('49','06102','Neu-Isenburg');
INSERT INTO `area_codes` VALUES ('49','06103','Langen Hess');
INSERT INTO `area_codes` VALUES ('49','06104','Heusenstamm');
INSERT INTO `area_codes` VALUES ('49','06105','Mörfelden-Walldorf');
INSERT INTO `area_codes` VALUES ('49','06106','Rodgau');
INSERT INTO `area_codes` VALUES ('49','06107','Kelsterbach');
INSERT INTO `area_codes` VALUES ('49','06108','Mühlheim am Main');
INSERT INTO `area_codes` VALUES ('49','06109','Frankfurt-Bergen-Enkheim');
INSERT INTO `area_codes` VALUES ('49','0611','Wiesbaden');
INSERT INTO `area_codes` VALUES ('49','06120','Aarbergen');
INSERT INTO `area_codes` VALUES ('49','06122','Hofheim-Wallau');
INSERT INTO `area_codes` VALUES ('49','06123','Eltville am Rhein');
INSERT INTO `area_codes` VALUES ('49','06124','Bad Schwalbach');
INSERT INTO `area_codes` VALUES ('49','06126','Idstein');
INSERT INTO `area_codes` VALUES ('49','06127','Niedernhausen Taunus');
INSERT INTO `area_codes` VALUES ('49','06128','Taunusstein');
INSERT INTO `area_codes` VALUES ('49','06129','Schlangenbad');
INSERT INTO `area_codes` VALUES ('49','06130','Schwabenheim an der Selz');
INSERT INTO `area_codes` VALUES ('49','06131','Mainz');
INSERT INTO `area_codes` VALUES ('49','06132','Ingelheim am Rhein');
INSERT INTO `area_codes` VALUES ('49','06133','Oppenheim');
INSERT INTO `area_codes` VALUES ('49','06134','Mainz-Kastel');
INSERT INTO `area_codes` VALUES ('49','06135','Bodenheim Rhein');
INSERT INTO `area_codes` VALUES ('49','06136','Nieder-Olm');
INSERT INTO `area_codes` VALUES ('49','06138','Mommenheim');
INSERT INTO `area_codes` VALUES ('49','06139','Budenheim');
INSERT INTO `area_codes` VALUES ('49','06142','Rüsselsheim');
INSERT INTO `area_codes` VALUES ('49','06144','Bischofsheim b Rüsselsheim');
INSERT INTO `area_codes` VALUES ('49','06145','Flörsheim am Main');
INSERT INTO `area_codes` VALUES ('49','06146','Hochheim am Main');
INSERT INTO `area_codes` VALUES ('49','06147','Trebur');
INSERT INTO `area_codes` VALUES ('49','06150','Weiterstadt');
INSERT INTO `area_codes` VALUES ('49','06151','Darmstadt');
INSERT INTO `area_codes` VALUES ('49','06152','Gross-Gerau');
INSERT INTO `area_codes` VALUES ('49','06154','Ober-Ramstadt');
INSERT INTO `area_codes` VALUES ('49','06155','Griesheim Hess');
INSERT INTO `area_codes` VALUES ('49','06157','Pfungstadt');
INSERT INTO `area_codes` VALUES ('49','06158','Riedstadt');
INSERT INTO `area_codes` VALUES ('49','06159','Messel');
INSERT INTO `area_codes` VALUES ('49','06161','Brensbach');
INSERT INTO `area_codes` VALUES ('49','06162','Reinheim Odenw');
INSERT INTO `area_codes` VALUES ('49','06163','Höchst i Odw');
INSERT INTO `area_codes` VALUES ('49','06164','Reichelsheim Odenwald');
INSERT INTO `area_codes` VALUES ('49','06165','Breuberg');
INSERT INTO `area_codes` VALUES ('49','06166','Fischbachtal');
INSERT INTO `area_codes` VALUES ('49','06167','Modautal');
INSERT INTO `area_codes` VALUES ('49','06171','Oberursel Taunus');
INSERT INTO `area_codes` VALUES ('49','06172','Bad Homburg v d Höhe');
INSERT INTO `area_codes` VALUES ('49','06173','Kronberg im Taunus');
INSERT INTO `area_codes` VALUES ('49','06174','Königstein im Taunus');
INSERT INTO `area_codes` VALUES ('49','06175','Friedrichsdorf Taunus');
INSERT INTO `area_codes` VALUES ('49','06181','Hanau');
INSERT INTO `area_codes` VALUES ('49','06182','Seligenstadt');
INSERT INTO `area_codes` VALUES ('49','06183','Erlensee');
INSERT INTO `area_codes` VALUES ('49','06184','Langenselbold');
INSERT INTO `area_codes` VALUES ('49','06185','Hammersbach Hess');
INSERT INTO `area_codes` VALUES ('49','06186','Grosskrotzenburg');
INSERT INTO `area_codes` VALUES ('49','06187','Schöneck');
INSERT INTO `area_codes` VALUES ('49','06188','Kahl a Main');
INSERT INTO `area_codes` VALUES ('49','06190','Hattersheim a Main');
INSERT INTO `area_codes` VALUES ('49','06192','Hofheim am Taunus');
INSERT INTO `area_codes` VALUES ('49','06195','Kelkheim Taunus');
INSERT INTO `area_codes` VALUES ('49','06196','Bad Soden am Taunus');
INSERT INTO `area_codes` VALUES ('49','06198','Eppstein');
INSERT INTO `area_codes` VALUES ('49','06201','Weinheim Bergstr');
INSERT INTO `area_codes` VALUES ('49','06202','Schwetzingen');
INSERT INTO `area_codes` VALUES ('49','06203','Ladenburg');
INSERT INTO `area_codes` VALUES ('49','06204','Viernheim');
INSERT INTO `area_codes` VALUES ('49','06205','Hockenheim');
INSERT INTO `area_codes` VALUES ('49','06206','Lampertheim');
INSERT INTO `area_codes` VALUES ('49','06207','Wald-Michelbach');
INSERT INTO `area_codes` VALUES ('49','06209','Mörlenbach');
INSERT INTO `area_codes` VALUES ('49','0621','Mannheim');
INSERT INTO `area_codes` VALUES ('49','06220','Wilhelmsfeld');
INSERT INTO `area_codes` VALUES ('49','06221','Heidelberg');
INSERT INTO `area_codes` VALUES ('49','06222','Wiesloch');
INSERT INTO `area_codes` VALUES ('49','06223','Neckargemünd');
INSERT INTO `area_codes` VALUES ('49','06224','Sandhausen Baden');
INSERT INTO `area_codes` VALUES ('49','06226','Meckesheim');
INSERT INTO `area_codes` VALUES ('49','06227','Walldorf Baden');
INSERT INTO `area_codes` VALUES ('49','06228','Schönau Odenw');
INSERT INTO `area_codes` VALUES ('49','06229','Neckarsteinach');
INSERT INTO `area_codes` VALUES ('49','06231','Hochdorf-Assenheim');
INSERT INTO `area_codes` VALUES ('49','06232','Speyer');
INSERT INTO `area_codes` VALUES ('49','06233','Frankenthal Pfalz');
INSERT INTO `area_codes` VALUES ('49','06234','Mutterstadt');
INSERT INTO `area_codes` VALUES ('49','06235','Schifferstadt');
INSERT INTO `area_codes` VALUES ('49','06236','Neuhofen Pfalz');
INSERT INTO `area_codes` VALUES ('49','06237','Maxdorf');
INSERT INTO `area_codes` VALUES ('49','06238','Dirmstein');
INSERT INTO `area_codes` VALUES ('49','06239','Bobenheim-Roxheim');
INSERT INTO `area_codes` VALUES ('49','06241','Worms');
INSERT INTO `area_codes` VALUES ('49','06242','Osthofen');
INSERT INTO `area_codes` VALUES ('49','06243','Monsheim');
INSERT INTO `area_codes` VALUES ('49','06244','Westhofen Rheinhess');
INSERT INTO `area_codes` VALUES ('49','06245','Biblis');
INSERT INTO `area_codes` VALUES ('49','06246','Eich Rheinhess');
INSERT INTO `area_codes` VALUES ('49','06247','Worms-Pfeddersheim');
INSERT INTO `area_codes` VALUES ('49','06249','Guntersblum');
INSERT INTO `area_codes` VALUES ('49','06251','Bensheim');
INSERT INTO `area_codes` VALUES ('49','06252','Heppenheim Bergstraße');
INSERT INTO `area_codes` VALUES ('49','06253','Fürth Odenw');
INSERT INTO `area_codes` VALUES ('49','06254','Lautertal Odenwald');
INSERT INTO `area_codes` VALUES ('49','06255','Lindenfels');
INSERT INTO `area_codes` VALUES ('49','06256','Lampertheim-Hüttenfeld');
INSERT INTO `area_codes` VALUES ('49','06257','Seeheim-Jugenheim');
INSERT INTO `area_codes` VALUES ('49','06258','Gernsheim');
INSERT INTO `area_codes` VALUES ('49','06261','Mosbach Baden');
INSERT INTO `area_codes` VALUES ('49','06262','Aglasterhausen');
INSERT INTO `area_codes` VALUES ('49','06263','Neckargerach');
INSERT INTO `area_codes` VALUES ('49','06264','Neudenau');
INSERT INTO `area_codes` VALUES ('49','06265','Billigheim Baden');
INSERT INTO `area_codes` VALUES ('49','06266','Hassmersheim');
INSERT INTO `area_codes` VALUES ('49','06267','Fahrenbach Baden');
INSERT INTO `area_codes` VALUES ('49','06268','Hüffenhardt');
INSERT INTO `area_codes` VALUES ('49','06269','Gundelsheim Württ');
INSERT INTO `area_codes` VALUES ('49','06271','Eberbach Baden');
INSERT INTO `area_codes` VALUES ('49','06272','Hirschhorn Neckar');
INSERT INTO `area_codes` VALUES ('49','06274','Waldbrunn Odenw');
INSERT INTO `area_codes` VALUES ('49','06275','Rothenberg Odenw');
INSERT INTO `area_codes` VALUES ('49','06276','Hesseneck');
INSERT INTO `area_codes` VALUES ('49','06281','Buchen Odenwald');
INSERT INTO `area_codes` VALUES ('49','06282','Walldürn');
INSERT INTO `area_codes` VALUES ('49','06283','Hardheim Odenw');
INSERT INTO `area_codes` VALUES ('49','06284','Mudau');
INSERT INTO `area_codes` VALUES ('49','06285','Walldürn-Altheim');
INSERT INTO `area_codes` VALUES ('49','06286','Walldürn-Rippberg');
INSERT INTO `area_codes` VALUES ('49','06287','Limbach Baden');
INSERT INTO `area_codes` VALUES ('49','06291','Adelsheim');
INSERT INTO `area_codes` VALUES ('49','06292','Seckach');
INSERT INTO `area_codes` VALUES ('49','06293','Schefflenz');
INSERT INTO `area_codes` VALUES ('49','06294','Krautheim Jagst');
INSERT INTO `area_codes` VALUES ('49','06295','RosenbergBaden');
INSERT INTO `area_codes` VALUES ('49','06296','Ahorn Baden');
INSERT INTO `area_codes` VALUES ('49','06297','Ravenstein Baden');
INSERT INTO `area_codes` VALUES ('49','06298','Möckmühl');
INSERT INTO `area_codes` VALUES ('49','06301','Otterbach Pfalz');
INSERT INTO `area_codes` VALUES ('49','06302','Winnweiler');
INSERT INTO `area_codes` VALUES ('49','06303','Enkenbach-Alsenborn');
INSERT INTO `area_codes` VALUES ('49','06304','Wolfstein Pfalz');
INSERT INTO `area_codes` VALUES ('49','06305','Hochspeyer');
INSERT INTO `area_codes` VALUES ('49','06306','Trippstadt');
INSERT INTO `area_codes` VALUES ('49','06307','Schopp');
INSERT INTO `area_codes` VALUES ('49','06308','Olsbrücken');
INSERT INTO `area_codes` VALUES ('49','0631','Kaiserslautern');
INSERT INTO `area_codes` VALUES ('49','06321','Neustadt an der Weinstraße');
INSERT INTO `area_codes` VALUES ('49','06322','Bad Dürkheim');
INSERT INTO `area_codes` VALUES ('49','06323','Edenkoben');
INSERT INTO `area_codes` VALUES ('49','06324','Hassloch');
INSERT INTO `area_codes` VALUES ('49','06325','Lambrecht Pfalz');
INSERT INTO `area_codes` VALUES ('49','06326','Deidesheim');
INSERT INTO `area_codes` VALUES ('49','06327','Neustadt-Lachen');
INSERT INTO `area_codes` VALUES ('49','06328','Elmstein');
INSERT INTO `area_codes` VALUES ('49','06329','Weidenthal Pfalz');
INSERT INTO `area_codes` VALUES ('49','06331','Pirmasens');
INSERT INTO `area_codes` VALUES ('49','06332','Zweibrücken');
INSERT INTO `area_codes` VALUES ('49','06333','Waldfischbach-Burgalben');
INSERT INTO `area_codes` VALUES ('49','06334','Thaleischweiler-Fröschen');
INSERT INTO `area_codes` VALUES ('49','06335','Trulben');
INSERT INTO `area_codes` VALUES ('49','06336','Dellfeld');
INSERT INTO `area_codes` VALUES ('49','06337','Grossbundenbach');
INSERT INTO `area_codes` VALUES ('49','06338','Hornbach Pfalz');
INSERT INTO `area_codes` VALUES ('49','06339','Grosssteinhausen');
INSERT INTO `area_codes` VALUES ('49','06340','Wörth-Schaidt');
INSERT INTO `area_codes` VALUES ('49','06341','Landau in der Pfalz');
INSERT INTO `area_codes` VALUES ('49','06342','Schweigen-Rechtenbach');
INSERT INTO `area_codes` VALUES ('49','06343','Bad Bergzabern');
INSERT INTO `area_codes` VALUES ('49','06344','Schwegenheim');
INSERT INTO `area_codes` VALUES ('49','06345','Albersweiler');
INSERT INTO `area_codes` VALUES ('49','06346','Annweiler am Trifels');
INSERT INTO `area_codes` VALUES ('49','06347','Hochstadt Pfalz');
INSERT INTO `area_codes` VALUES ('49','06348','Offenbach an der Queich');
INSERT INTO `area_codes` VALUES ('49','06349','Billigheim-Ingenheim');
INSERT INTO `area_codes` VALUES ('49','06351','Eisenberg Pfalz');
INSERT INTO `area_codes` VALUES ('49','06352','Kirchheimbolanden');
INSERT INTO `area_codes` VALUES ('49','06353','Freinsheim');
INSERT INTO `area_codes` VALUES ('49','06355','Albisheim Pfrimm');
INSERT INTO `area_codes` VALUES ('49','06356','Carlsberg Pfalz');
INSERT INTO `area_codes` VALUES ('49','06357','Standenbühl');
INSERT INTO `area_codes` VALUES ('49','06358','Kriegsfeld');
INSERT INTO `area_codes` VALUES ('49','06359','Grünstadt');
INSERT INTO `area_codes` VALUES ('49','06361','Rockenhausen');
INSERT INTO `area_codes` VALUES ('49','06362','Alsenz');
INSERT INTO `area_codes` VALUES ('49','06363','Niederkirchen');
INSERT INTO `area_codes` VALUES ('49','06364','Nußbach Pfalz');
INSERT INTO `area_codes` VALUES ('49','06371','Landstuhl');
INSERT INTO `area_codes` VALUES ('49','06372','Bruchmühlbach-Miesau');
INSERT INTO `area_codes` VALUES ('49','06373','Schönenberg-Kübelberg');
INSERT INTO `area_codes` VALUES ('49','06374','Weilerbach');
INSERT INTO `area_codes` VALUES ('49','06375','Wallhalben');
INSERT INTO `area_codes` VALUES ('49','06381','Kusel');
INSERT INTO `area_codes` VALUES ('49','06382','Lauterecken');
INSERT INTO `area_codes` VALUES ('49','06383','Glan-Münchweiler');
INSERT INTO `area_codes` VALUES ('49','06384','Konken');
INSERT INTO `area_codes` VALUES ('49','06385','Reichenbach-Steegen');
INSERT INTO `area_codes` VALUES ('49','06386','Altenkirchen Pfalz');
INSERT INTO `area_codes` VALUES ('49','06387','Sankt Julian');
INSERT INTO `area_codes` VALUES ('49','06391','Dahn');
INSERT INTO `area_codes` VALUES ('49','06392','Hauenstein Pfalz');
INSERT INTO `area_codes` VALUES ('49','06393','Fischbach bei Dahn');
INSERT INTO `area_codes` VALUES ('49','06394','Bundenthal');
INSERT INTO `area_codes` VALUES ('49','06395','Münchweiler an der Rodalb');
INSERT INTO `area_codes` VALUES ('49','06396','Hinterweidenthal');
INSERT INTO `area_codes` VALUES ('49','06397','Leimen Pfalz');
INSERT INTO `area_codes` VALUES ('49','06398','Vorderweidenthal');
INSERT INTO `area_codes` VALUES ('49','06400','Mücke');
INSERT INTO `area_codes` VALUES ('49','06401','Grünberg Hess');
INSERT INTO `area_codes` VALUES ('49','06402','Hungen');
INSERT INTO `area_codes` VALUES ('49','06403','Linden Hess');
INSERT INTO `area_codes` VALUES ('49','06404','Lich Hess');
INSERT INTO `area_codes` VALUES ('49','06405','Laubach Hess');
INSERT INTO `area_codes` VALUES ('49','06406','Lollar');
INSERT INTO `area_codes` VALUES ('49','06407','Rabenau Hess');
INSERT INTO `area_codes` VALUES ('49','06408','Buseck');
INSERT INTO `area_codes` VALUES ('49','06409','Biebertal');
INSERT INTO `area_codes` VALUES ('49','0641','Giessen');
INSERT INTO `area_codes` VALUES ('49','06420','Lahntal');
INSERT INTO `area_codes` VALUES ('49','06421','Marburg');
INSERT INTO `area_codes` VALUES ('49','06422','Kirchhain');
INSERT INTO `area_codes` VALUES ('49','06423','Wetter Hessen');
INSERT INTO `area_codes` VALUES ('49','06424','Ebsdorfergrund');
INSERT INTO `area_codes` VALUES ('49','06425','Rauschenberg Hess');
INSERT INTO `area_codes` VALUES ('49','06426','Fronhausen');
INSERT INTO `area_codes` VALUES ('49','06427','Cölbe-Schönstadt');
INSERT INTO `area_codes` VALUES ('49','06428','Stadtallendorf');
INSERT INTO `area_codes` VALUES ('49','06429','Schweinsberg Hess');
INSERT INTO `area_codes` VALUES ('49','06430','Hahnstätten');
INSERT INTO `area_codes` VALUES ('49','06431','Limburg a d Lahn');
INSERT INTO `area_codes` VALUES ('49','06432','Diez');
INSERT INTO `area_codes` VALUES ('49','06433','Hadamar');
INSERT INTO `area_codes` VALUES ('49','06434','Bad Camberg');
INSERT INTO `area_codes` VALUES ('49','06435','Wallmerod');
INSERT INTO `area_codes` VALUES ('49','06436','Dornburg Hess');
INSERT INTO `area_codes` VALUES ('49','06438','Hünfelden');
INSERT INTO `area_codes` VALUES ('49','06439','Holzappel');
INSERT INTO `area_codes` VALUES ('49','06440','Kölschhausen');
INSERT INTO `area_codes` VALUES ('49','06441','Wetzlar');
INSERT INTO `area_codes` VALUES ('49','06442','Braunfels');
INSERT INTO `area_codes` VALUES ('49','06443','Ehringshausen Dill');
INSERT INTO `area_codes` VALUES ('49','06444','Bischoffen');
INSERT INTO `area_codes` VALUES ('49','06445','Schöffengrund');
INSERT INTO `area_codes` VALUES ('49','06446','Hohenahr');
INSERT INTO `area_codes` VALUES ('49','06447','Langgöns-Niederkleen');
INSERT INTO `area_codes` VALUES ('49','06449','Ehringshausen-Katzenfurt');
INSERT INTO `area_codes` VALUES ('49','06451','Frankenberg Eder');
INSERT INTO `area_codes` VALUES ('49','06452','Battenberg Eder');
INSERT INTO `area_codes` VALUES ('49','06453','Gemünden Wohra');
INSERT INTO `area_codes` VALUES ('49','06454','Lichtenfels-Sachsenberg');
INSERT INTO `area_codes` VALUES ('49','06455','Frankenau Hess');
INSERT INTO `area_codes` VALUES ('49','06456','Haina Kloster');
INSERT INTO `area_codes` VALUES ('49','06457','Burgwald Eder');
INSERT INTO `area_codes` VALUES ('49','06458','Rosenthal Hess');
INSERT INTO `area_codes` VALUES ('49','06461','Biedenkopf');
INSERT INTO `area_codes` VALUES ('49','06462','Gladenbach');
INSERT INTO `area_codes` VALUES ('49','06464','Angelburg');
INSERT INTO `area_codes` VALUES ('49','06465','Breidenbach b Biedenkopf');
INSERT INTO `area_codes` VALUES ('49','06466','Dautphetal-Friedensdorf');
INSERT INTO `area_codes` VALUES ('49','06467','Hatzfeld Eder');
INSERT INTO `area_codes` VALUES ('49','06468','Dautphetal-Mornshausen');
INSERT INTO `area_codes` VALUES ('49','06471','Weilburg');
INSERT INTO `area_codes` VALUES ('49','06472','Weilmünster');
INSERT INTO `area_codes` VALUES ('49','06473','Leun');
INSERT INTO `area_codes` VALUES ('49','06474','Villmar-Aumenau');
INSERT INTO `area_codes` VALUES ('49','06475','Weilmünster-Wolfenhausen');
INSERT INTO `area_codes` VALUES ('49','06476','Mengerskirchen');
INSERT INTO `area_codes` VALUES ('49','06477','Greifenstein-Nenderoth');
INSERT INTO `area_codes` VALUES ('49','06478','Greifenstein-Ulm');
INSERT INTO `area_codes` VALUES ('49','06479','Waldbrunn Westerwald');
INSERT INTO `area_codes` VALUES ('49','06482','Runkel');
INSERT INTO `area_codes` VALUES ('49','06483','Selters Taunus');
INSERT INTO `area_codes` VALUES ('49','06484','Beselich');
INSERT INTO `area_codes` VALUES ('49','06485','Nentershausen Westerw');
INSERT INTO `area_codes` VALUES ('49','06486','Katzenelnbogen');
INSERT INTO `area_codes` VALUES ('49','06500','Waldrach');
INSERT INTO `area_codes` VALUES ('49','06501','Konz');
INSERT INTO `area_codes` VALUES ('49','06502','Schweich');
INSERT INTO `area_codes` VALUES ('49','06503','Hermeskeil');
INSERT INTO `area_codes` VALUES ('49','06504','Thalfang');
INSERT INTO `area_codes` VALUES ('49','06505','Kordel');
INSERT INTO `area_codes` VALUES ('49','06506','Welschbillig');
INSERT INTO `area_codes` VALUES ('49','06507','Neumagen-Dhron');
INSERT INTO `area_codes` VALUES ('49','06508','Hetzerath Mosel');
INSERT INTO `area_codes` VALUES ('49','06509','Büdlich');
INSERT INTO `area_codes` VALUES ('49','0651','Trier');
INSERT INTO `area_codes` VALUES ('49','06522','Mettendorf');
INSERT INTO `area_codes` VALUES ('49','06523','Holsthum');
INSERT INTO `area_codes` VALUES ('49','06524','Rodershausen');
INSERT INTO `area_codes` VALUES ('49','06525','Irrel');
INSERT INTO `area_codes` VALUES ('49','06526','Bollendorf');
INSERT INTO `area_codes` VALUES ('49','06527','Oberweis');
INSERT INTO `area_codes` VALUES ('49','06531','Bernkastel-Kues');
INSERT INTO `area_codes` VALUES ('49','06532','Zeltingen-Rachtig');
INSERT INTO `area_codes` VALUES ('49','06533','Morbach Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06534','Mülheim Mosel');
INSERT INTO `area_codes` VALUES ('49','06535','Osann-Monzel');
INSERT INTO `area_codes` VALUES ('49','06536','Kleinich');
INSERT INTO `area_codes` VALUES ('49','06541','Traben-Trarbach');
INSERT INTO `area_codes` VALUES ('49','06542','Bullay');
INSERT INTO `area_codes` VALUES ('49','06543','Büchenbeuren');
INSERT INTO `area_codes` VALUES ('49','06544','Rhaunen');
INSERT INTO `area_codes` VALUES ('49','06545','Blankenrath');
INSERT INTO `area_codes` VALUES ('49','06550','Irrhausen');
INSERT INTO `area_codes` VALUES ('49','06551','Prüm');
INSERT INTO `area_codes` VALUES ('49','06552','Olzheim');
INSERT INTO `area_codes` VALUES ('49','06553','Schönecken');
INSERT INTO `area_codes` VALUES ('49','06554','Waxweiler');
INSERT INTO `area_codes` VALUES ('49','06555','Bleialf');
INSERT INTO `area_codes` VALUES ('49','06556','Pronsfeld');
INSERT INTO `area_codes` VALUES ('49','06557','Hallschlag');
INSERT INTO `area_codes` VALUES ('49','06558','Büdesheim Eifel');
INSERT INTO `area_codes` VALUES ('49','06559','Leidenborn');
INSERT INTO `area_codes` VALUES ('49','06561','Bitburg');
INSERT INTO `area_codes` VALUES ('49','06562','Speicher');
INSERT INTO `area_codes` VALUES ('49','06563','Kyllburg');
INSERT INTO `area_codes` VALUES ('49','06564','Neuerburg Eifel');
INSERT INTO `area_codes` VALUES ('49','06565','Dudeldorf');
INSERT INTO `area_codes` VALUES ('49','06566','Körperich');
INSERT INTO `area_codes` VALUES ('49','06567','Oberkail');
INSERT INTO `area_codes` VALUES ('49','06568','Wolsfeld');
INSERT INTO `area_codes` VALUES ('49','06569','Bickendorf');
INSERT INTO `area_codes` VALUES ('49','06571','Wittlich');
INSERT INTO `area_codes` VALUES ('49','06572','Manderscheid Eifel');
INSERT INTO `area_codes` VALUES ('49','06573','Gillenfeld');
INSERT INTO `area_codes` VALUES ('49','06574','Hasborn');
INSERT INTO `area_codes` VALUES ('49','06575','Landscheid');
INSERT INTO `area_codes` VALUES ('49','06578','Salmtal');
INSERT INTO `area_codes` VALUES ('49','06580','Zemmer');
INSERT INTO `area_codes` VALUES ('49','06581','Saarburg');
INSERT INTO `area_codes` VALUES ('49','06582','Freudenburg');
INSERT INTO `area_codes` VALUES ('49','06583','Palzem');
INSERT INTO `area_codes` VALUES ('49','06584','Wellen Mosel');
INSERT INTO `area_codes` VALUES ('49','06585','Ralingen');
INSERT INTO `area_codes` VALUES ('49','06586','Beuren Hochwald');
INSERT INTO `area_codes` VALUES ('49','06587','Zerf');
INSERT INTO `area_codes` VALUES ('49','06588','Pluwig');
INSERT INTO `area_codes` VALUES ('49','06589','Kell am See');
INSERT INTO `area_codes` VALUES ('49','06591','Gerolstein');
INSERT INTO `area_codes` VALUES ('49','06592','Daun');
INSERT INTO `area_codes` VALUES ('49','06593','Hillesheim Eifel');
INSERT INTO `area_codes` VALUES ('49','06594','Birresborn');
INSERT INTO `area_codes` VALUES ('49','06595','Dockweiler');
INSERT INTO `area_codes` VALUES ('49','06596','Üdersdorf');
INSERT INTO `area_codes` VALUES ('49','06597','Jünkerath');
INSERT INTO `area_codes` VALUES ('49','06599','Weidenbach b Gerolstein');
INSERT INTO `area_codes` VALUES ('49','0661','Fulda');
INSERT INTO `area_codes` VALUES ('49','06620','Philippsthal Werra');
INSERT INTO `area_codes` VALUES ('49','06621','Bad Hersfeld');
INSERT INTO `area_codes` VALUES ('49','06622','Bebra');
INSERT INTO `area_codes` VALUES ('49','06623','Rotenburg a d Fulda');
INSERT INTO `area_codes` VALUES ('49','06624','Heringen Werra');
INSERT INTO `area_codes` VALUES ('49','06625','Niederaula');
INSERT INTO `area_codes` VALUES ('49','06626','Wildeck-Obersuhl');
INSERT INTO `area_codes` VALUES ('49','06627','Nentershausen Hess');
INSERT INTO `area_codes` VALUES ('49','06628','Oberaula');
INSERT INTO `area_codes` VALUES ('49','06629','Schenklengsfeld');
INSERT INTO `area_codes` VALUES ('49','06630','Schwalmtal-Storndorf');
INSERT INTO `area_codes` VALUES ('49','06631','Alsfeld');
INSERT INTO `area_codes` VALUES ('49','06633','Homberg Ohm');
INSERT INTO `area_codes` VALUES ('49','06634','Gemünden Felda');
INSERT INTO `area_codes` VALUES ('49','06635','Kirtorf');
INSERT INTO `area_codes` VALUES ('49','06636','Romrod');
INSERT INTO `area_codes` VALUES ('49','06637','Feldatal');
INSERT INTO `area_codes` VALUES ('49','06638','Schwalmtal-Renzendorf');
INSERT INTO `area_codes` VALUES ('49','06639','Ottrau');
INSERT INTO `area_codes` VALUES ('49','06641','Lauterbach Hessen');
INSERT INTO `area_codes` VALUES ('49','06642','Schlitz');
INSERT INTO `area_codes` VALUES ('49','06643','Herbstein');
INSERT INTO `area_codes` VALUES ('49','06644','Grebenhain');
INSERT INTO `area_codes` VALUES ('49','06645','Ulrichstein');
INSERT INTO `area_codes` VALUES ('49','06646','Grebenau');
INSERT INTO `area_codes` VALUES ('49','06647','Herbstein-Stockhausen');
INSERT INTO `area_codes` VALUES ('49','06648','Bad Salzschlirf');
INSERT INTO `area_codes` VALUES ('49','06650','Hosenfeld');
INSERT INTO `area_codes` VALUES ('49','06651','Rasdorf');
INSERT INTO `area_codes` VALUES ('49','06652','Hünfeld');
INSERT INTO `area_codes` VALUES ('49','06653','Burghaun');
INSERT INTO `area_codes` VALUES ('49','06654','Gersfeld Rhön');
INSERT INTO `area_codes` VALUES ('49','06655','Neuhof Kr Fulda');
INSERT INTO `area_codes` VALUES ('49','06656','Ebersburg');
INSERT INTO `area_codes` VALUES ('49','06657','Hofbieber');
INSERT INTO `area_codes` VALUES ('49','06658','Poppenhausen Wasserkuppe');
INSERT INTO `area_codes` VALUES ('49','06659','Eichenzell');
INSERT INTO `area_codes` VALUES ('49','06660','Steinau-Marjoss');
INSERT INTO `area_codes` VALUES ('49','06661','Schlüchtern');
INSERT INTO `area_codes` VALUES ('49','06663','Steinau an der Straße');
INSERT INTO `area_codes` VALUES ('49','06664','Sinntal-Sterbfritz');
INSERT INTO `area_codes` VALUES ('49','06665','Sinntal-Altengronau');
INSERT INTO `area_codes` VALUES ('49','06666','Freiensteinau');
INSERT INTO `area_codes` VALUES ('49','06667','Steinau-Ulmbach');
INSERT INTO `area_codes` VALUES ('49','06668','Birstein-Lichenroth');
INSERT INTO `area_codes` VALUES ('49','06669','Neuhof-Hauswurz');
INSERT INTO `area_codes` VALUES ('49','06670','Ludwigsau Hess');
INSERT INTO `area_codes` VALUES ('49','06672','Eiterfeld');
INSERT INTO `area_codes` VALUES ('49','06673','Haunetal');
INSERT INTO `area_codes` VALUES ('49','06674','Friedewald Hess');
INSERT INTO `area_codes` VALUES ('49','06675','Breitenbach a Herzberg');
INSERT INTO `area_codes` VALUES ('49','06676','Hohenroda Hess');
INSERT INTO `area_codes` VALUES ('49','06677','Neuenstein Hess');
INSERT INTO `area_codes` VALUES ('49','06678','Wildeck-Hönebach');
INSERT INTO `area_codes` VALUES ('49','06681','Hilders');
INSERT INTO `area_codes` VALUES ('49','06682','Tann Rhön');
INSERT INTO `area_codes` VALUES ('49','06683','Ehrenberg Rhön');
INSERT INTO `area_codes` VALUES ('49','06684','Hofbieber-Schwarzbach');
INSERT INTO `area_codes` VALUES ('49','06691','Schwalmstadt');
INSERT INTO `area_codes` VALUES ('49','06692','Neustadt Hessen');
INSERT INTO `area_codes` VALUES ('49','06693','Neuental');
INSERT INTO `area_codes` VALUES ('49','06694','Neukirchen Knüll');
INSERT INTO `area_codes` VALUES ('49','06695','Jesberg');
INSERT INTO `area_codes` VALUES ('49','06696','Gilserberg');
INSERT INTO `area_codes` VALUES ('49','06697','Willingshausen');
INSERT INTO `area_codes` VALUES ('49','06698','Schrecksbach');
INSERT INTO `area_codes` VALUES ('49','06701','Sprendlingen Rheinhess');
INSERT INTO `area_codes` VALUES ('49','06703','Wöllstein Rheinhess');
INSERT INTO `area_codes` VALUES ('49','06704','Langenlonsheim');
INSERT INTO `area_codes` VALUES ('49','06706','Wallhausen Nahe');
INSERT INTO `area_codes` VALUES ('49','06707','Windesheim');
INSERT INTO `area_codes` VALUES ('49','06708','Bad Münster am Stein-Ebernburg');
INSERT INTO `area_codes` VALUES ('49','06709','Fürfeld Kr Bad Kreuznach');
INSERT INTO `area_codes` VALUES ('49','0671','Bad Kreuznach');
INSERT INTO `area_codes` VALUES ('49','06721','Bingen am Rhein');
INSERT INTO `area_codes` VALUES ('49','06722','Rüdesheim am Rhein');
INSERT INTO `area_codes` VALUES ('49','06723','Oestrich-Winkel');
INSERT INTO `area_codes` VALUES ('49','06724','Stromberg Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06725','Gau-Algesheim');
INSERT INTO `area_codes` VALUES ('49','06726','Lorch Rheingau');
INSERT INTO `area_codes` VALUES ('49','06727','Gensingen');
INSERT INTO `area_codes` VALUES ('49','06728','Ober-Hilbersheim');
INSERT INTO `area_codes` VALUES ('49','06731','Alzey');
INSERT INTO `area_codes` VALUES ('49','06732','Wörrstadt');
INSERT INTO `area_codes` VALUES ('49','06733','Gau-Odernheim');
INSERT INTO `area_codes` VALUES ('49','06734','Flonheim');
INSERT INTO `area_codes` VALUES ('49','06735','Eppelsheim');
INSERT INTO `area_codes` VALUES ('49','06736','Bechenheim');
INSERT INTO `area_codes` VALUES ('49','06737','Köngernheim');
INSERT INTO `area_codes` VALUES ('49','06741','St Goar');
INSERT INTO `area_codes` VALUES ('49','06742','Boppard');
INSERT INTO `area_codes` VALUES ('49','06743','Bacharach');
INSERT INTO `area_codes` VALUES ('49','06744','Oberwesel');
INSERT INTO `area_codes` VALUES ('49','06745','Gondershausen');
INSERT INTO `area_codes` VALUES ('49','06746','Pfalzfeld');
INSERT INTO `area_codes` VALUES ('49','06747','Emmelshausen');
INSERT INTO `area_codes` VALUES ('49','06751','Bad Sobernheim');
INSERT INTO `area_codes` VALUES ('49','06752','Kirn Nahe');
INSERT INTO `area_codes` VALUES ('49','06753','Meisenheim');
INSERT INTO `area_codes` VALUES ('49','06754','Martinstein');
INSERT INTO `area_codes` VALUES ('49','06755','Odernheim am Glan');
INSERT INTO `area_codes` VALUES ('49','06756','Winterbach Soonwald');
INSERT INTO `area_codes` VALUES ('49','06757','Becherbach bei Kirn');
INSERT INTO `area_codes` VALUES ('49','06758','Waldböckelheim');
INSERT INTO `area_codes` VALUES ('49','06761','Simmern Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06762','Kastellaun');
INSERT INTO `area_codes` VALUES ('49','06763','Kirchberg Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06764','Rheinböllen');
INSERT INTO `area_codes` VALUES ('49','06765','Gemünden Hunsrück');
INSERT INTO `area_codes` VALUES ('49','06766','Kisselbach');
INSERT INTO `area_codes` VALUES ('49','06771','St Goarshausen');
INSERT INTO `area_codes` VALUES ('49','06772','Nastätten');
INSERT INTO `area_codes` VALUES ('49','06773','Kamp-Bornhofen');
INSERT INTO `area_codes` VALUES ('49','06774','Kaub');
INSERT INTO `area_codes` VALUES ('49','06775','Strüth Taunus');
INSERT INTO `area_codes` VALUES ('49','06776','Dachsenhausen');
INSERT INTO `area_codes` VALUES ('49','06781','Idar-Oberstein');
INSERT INTO `area_codes` VALUES ('49','06782','Birkenfeld Nahe');
INSERT INTO `area_codes` VALUES ('49','06783','Baumholder');
INSERT INTO `area_codes` VALUES ('49','06784','Weierbach');
INSERT INTO `area_codes` VALUES ('49','06785','Herrstein');
INSERT INTO `area_codes` VALUES ('49','06786','Kempfeld');
INSERT INTO `area_codes` VALUES ('49','06787','Niederbrombach');
INSERT INTO `area_codes` VALUES ('49','06788','Sien');
INSERT INTO `area_codes` VALUES ('49','06789','Heimbach Nahe');
INSERT INTO `area_codes` VALUES ('49','06802','Völklingen-Lauterbach');
INSERT INTO `area_codes` VALUES ('49','06803','Mandelbachtal-Ommersheim');
INSERT INTO `area_codes` VALUES ('49','06804','Mandelbachtal');
INSERT INTO `area_codes` VALUES ('49','06805','Kleinblittersdorf');
INSERT INTO `area_codes` VALUES ('49','06806','Heusweiler');
INSERT INTO `area_codes` VALUES ('49','06809','Grossrosseln');
INSERT INTO `area_codes` VALUES ('49','0681','Saarbrücken');
INSERT INTO `area_codes` VALUES ('49','06821','Neunkirchen Saar');
INSERT INTO `area_codes` VALUES ('49','06824','Ottweiler');
INSERT INTO `area_codes` VALUES ('49','06825','Illingen Saar');
INSERT INTO `area_codes` VALUES ('49','06826','Bexbach');
INSERT INTO `area_codes` VALUES ('49','06827','Eppelborn');
INSERT INTO `area_codes` VALUES ('49','06831','Saarlouis');
INSERT INTO `area_codes` VALUES ('49','06832','Beckingen-Reimsbach');
INSERT INTO `area_codes` VALUES ('49','06833','Rehlingen-Siersburg');
INSERT INTO `area_codes` VALUES ('49','06834','Bous');
INSERT INTO `area_codes` VALUES ('49','06835','Beckingen');
INSERT INTO `area_codes` VALUES ('49','06836','Überherrn');
INSERT INTO `area_codes` VALUES ('49','06837','Wallerfangen');
INSERT INTO `area_codes` VALUES ('49','06838','Saarwellingen');
INSERT INTO `area_codes` VALUES ('49','06841','Homburg Saar');
INSERT INTO `area_codes` VALUES ('49','06842','Blieskastel');
INSERT INTO `area_codes` VALUES ('49','06843','Gersheim');
INSERT INTO `area_codes` VALUES ('49','06844','Blieskastel-Altheim');
INSERT INTO `area_codes` VALUES ('49','06848','Homburg-Einöd');
INSERT INTO `area_codes` VALUES ('49','06849','Kirkel');
INSERT INTO `area_codes` VALUES ('49','06851','St Wendel');
INSERT INTO `area_codes` VALUES ('49','06852','Nohfelden');
INSERT INTO `area_codes` VALUES ('49','06853','Marpingen');
INSERT INTO `area_codes` VALUES ('49','06854','Oberthal Saar');
INSERT INTO `area_codes` VALUES ('49','06855','Freisen');
INSERT INTO `area_codes` VALUES ('49','06856','St Wendel-Niederkirchen');
INSERT INTO `area_codes` VALUES ('49','06857','Namborn');
INSERT INTO `area_codes` VALUES ('49','06858','Ottweiler-Fürth');
INSERT INTO `area_codes` VALUES ('49','06861','Merzig');
INSERT INTO `area_codes` VALUES ('49','06864','Mettlach');
INSERT INTO `area_codes` VALUES ('49','06865','Mettlach-Orscholz');
INSERT INTO `area_codes` VALUES ('49','06866','Perl-Nennig');
INSERT INTO `area_codes` VALUES ('49','06867','Perl');
INSERT INTO `area_codes` VALUES ('49','06868','Mettlach-Tünsdorf');
INSERT INTO `area_codes` VALUES ('49','06869','Merzig-Silwingen');
INSERT INTO `area_codes` VALUES ('49','06871','Wadern');
INSERT INTO `area_codes` VALUES ('49','06872','Losheim am See');
INSERT INTO `area_codes` VALUES ('49','06873','Nonnweiler');
INSERT INTO `area_codes` VALUES ('49','06874','Wadern-Nunkirchen');
INSERT INTO `area_codes` VALUES ('49','06875','Nonnweiler-Primstal');
INSERT INTO `area_codes` VALUES ('49','06876','Weiskirchen Saar');
INSERT INTO `area_codes` VALUES ('49','06881','Lebach');
INSERT INTO `area_codes` VALUES ('49','06887','Schmelz Saar');
INSERT INTO `area_codes` VALUES ('49','06888','Lebach-Steinbach');
INSERT INTO `area_codes` VALUES ('49','06893','Saarbrücken-Ensheim');
INSERT INTO `area_codes` VALUES ('49','06894','St Ingbert');
INSERT INTO `area_codes` VALUES ('49','06897','Sulzbach Saar');
INSERT INTO `area_codes` VALUES ('49','06898','Völklingen');
INSERT INTO `area_codes` VALUES ('49','069','Frankfurt am Main');
INSERT INTO `area_codes` VALUES ('49','07021','Kirchheim unter Teck');
INSERT INTO `area_codes` VALUES ('49','07022','Nürtingen');
INSERT INTO `area_codes` VALUES ('49','07023','Weilheim an der Teck');
INSERT INTO `area_codes` VALUES ('49','07024','Wendlingen am Neckar');
INSERT INTO `area_codes` VALUES ('49','07025','Neuffen');
INSERT INTO `area_codes` VALUES ('49','07026','Lenningen');
INSERT INTO `area_codes` VALUES ('49','07031','Böblingen');
INSERT INTO `area_codes` VALUES ('49','07032','Herrenberg');
INSERT INTO `area_codes` VALUES ('49','07033','Weil Der Stadt');
INSERT INTO `area_codes` VALUES ('49','07034','Ehningen');
INSERT INTO `area_codes` VALUES ('49','07041','Mühlacker');
INSERT INTO `area_codes` VALUES ('49','07042','Vaihingen an der Enz');
INSERT INTO `area_codes` VALUES ('49','07043','Maulbronn');
INSERT INTO `area_codes` VALUES ('49','07044','Mönsheim');
INSERT INTO `area_codes` VALUES ('49','07045','Oberderdingen');
INSERT INTO `area_codes` VALUES ('49','07046','Zaberfeld');
INSERT INTO `area_codes` VALUES ('49','07051','Calw');
INSERT INTO `area_codes` VALUES ('49','07052','Bad Liebenzell');
INSERT INTO `area_codes` VALUES ('49','07053','Bad Teinach-Zavelstein');
INSERT INTO `area_codes` VALUES ('49','07054','Wildberg Württ');
INSERT INTO `area_codes` VALUES ('49','07055','Neuweiler Kr Calw');
INSERT INTO `area_codes` VALUES ('49','07056','Gechingen');
INSERT INTO `area_codes` VALUES ('49','07062','Beilstein Württ');
INSERT INTO `area_codes` VALUES ('49','07063','Bad Wimpfen');
INSERT INTO `area_codes` VALUES ('49','07066','Bad Rappenau-Bonfeld');
INSERT INTO `area_codes` VALUES ('49','07071','Tübingen');
INSERT INTO `area_codes` VALUES ('49','07072','Gomaringen');
INSERT INTO `area_codes` VALUES ('49','07073','Ammerbuch');
INSERT INTO `area_codes` VALUES ('49','07081','Bad Wildbad');
INSERT INTO `area_codes` VALUES ('49','07082','Neuenbürg Württ');
INSERT INTO `area_codes` VALUES ('49','07083','Bad Herrenalb');
INSERT INTO `area_codes` VALUES ('49','07084','Schömberg b Neuenbürg');
INSERT INTO `area_codes` VALUES ('49','07085','Enzklösterle');
INSERT INTO `area_codes` VALUES ('49','0711','Stuttgart');
INSERT INTO `area_codes` VALUES ('49','07121','Reutlingen');
INSERT INTO `area_codes` VALUES ('49','07122','St Johann Württ');
INSERT INTO `area_codes` VALUES ('49','07123','Metzingen Württ');
INSERT INTO `area_codes` VALUES ('49','07124','Trochtelfingen Hohenz');
INSERT INTO `area_codes` VALUES ('49','07125','Bad Urach');
INSERT INTO `area_codes` VALUES ('49','07126','Burladingen-Melchingen');
INSERT INTO `area_codes` VALUES ('49','07127','Neckartenzlingen');
INSERT INTO `area_codes` VALUES ('49','07128','Sonnenbühl');
INSERT INTO `area_codes` VALUES ('49','07129','Lichtenstein Württ');
INSERT INTO `area_codes` VALUES ('49','07130','Löwenstein Württ');
INSERT INTO `area_codes` VALUES ('49','07131','Heilbronn Neckar');
INSERT INTO `area_codes` VALUES ('49','07132','Neckarsulm');
INSERT INTO `area_codes` VALUES ('49','07133','Lauffen am Neckar');
INSERT INTO `area_codes` VALUES ('49','07134','Weinsberg');
INSERT INTO `area_codes` VALUES ('49','07135','Brackenheim');
INSERT INTO `area_codes` VALUES ('49','07136','Bad Friedrichshall');
INSERT INTO `area_codes` VALUES ('49','07138','Schwaigern');
INSERT INTO `area_codes` VALUES ('49','07139','Neuenstadt am Kocher');
INSERT INTO `area_codes` VALUES ('49','07141','Ludwigsburg Württ');
INSERT INTO `area_codes` VALUES ('49','07142','Bietigheim-Bissingen');
INSERT INTO `area_codes` VALUES ('49','07143','Besigheim');
INSERT INTO `area_codes` VALUES ('49','07144','Marbach am Neckar');
INSERT INTO `area_codes` VALUES ('49','07145','Markgröningen');
INSERT INTO `area_codes` VALUES ('49','07146','Remseck am Neckar');
INSERT INTO `area_codes` VALUES ('49','07147','Sachsenheim Württ');
INSERT INTO `area_codes` VALUES ('49','07148','Grossbottwar');
INSERT INTO `area_codes` VALUES ('49','07150','Korntal-Münchingen');
INSERT INTO `area_codes` VALUES ('49','07151','Waiblingen');
INSERT INTO `area_codes` VALUES ('49','07152','Leonberg Württ');
INSERT INTO `area_codes` VALUES ('49','07153','Plochingen');
INSERT INTO `area_codes` VALUES ('49','07154','Kornwestheim');
INSERT INTO `area_codes` VALUES ('49','07156','Ditzingen');
INSERT INTO `area_codes` VALUES ('49','07157','Waldenbuch');
INSERT INTO `area_codes` VALUES ('49','07158','Neuhausen auf den Fildern');
INSERT INTO `area_codes` VALUES ('49','07159','Renningen');
INSERT INTO `area_codes` VALUES ('49','07161','Göppingen');
INSERT INTO `area_codes` VALUES ('49','07162','Süßen');
INSERT INTO `area_codes` VALUES ('49','07163','Ebersbach an der Fils');
INSERT INTO `area_codes` VALUES ('49','07164','Boll Kr Göppingen');
INSERT INTO `area_codes` VALUES ('49','07165','Göppingen-Hohenstaufen');
INSERT INTO `area_codes` VALUES ('49','07166','Adelberg');
INSERT INTO `area_codes` VALUES ('49','07171','Schwäbisch Gmünd');
INSERT INTO `area_codes` VALUES ('49','07172','Lorch Württ');
INSERT INTO `area_codes` VALUES ('49','07173','Heubach');
INSERT INTO `area_codes` VALUES ('49','07174','Mögglingen');
INSERT INTO `area_codes` VALUES ('49','07175','Leinzell');
INSERT INTO `area_codes` VALUES ('49','07176','Spraitbach');
INSERT INTO `area_codes` VALUES ('49','07181','Schorndorf Württ');
INSERT INTO `area_codes` VALUES ('49','07182','Welzheim');
INSERT INTO `area_codes` VALUES ('49','07183','Rudersberg Württ');
INSERT INTO `area_codes` VALUES ('49','07184','Kaisersbach');
INSERT INTO `area_codes` VALUES ('49','07191','Backnang');
INSERT INTO `area_codes` VALUES ('49','07192','Murrhardt');
INSERT INTO `area_codes` VALUES ('49','07193','Sulzbach an der Murr');
INSERT INTO `area_codes` VALUES ('49','07194','Spiegelberg');
INSERT INTO `area_codes` VALUES ('49','07195','Winnenden');
INSERT INTO `area_codes` VALUES ('49','07202','Karlsbad');
INSERT INTO `area_codes` VALUES ('49','07203','Walzbachtal');
INSERT INTO `area_codes` VALUES ('49','07204','Malsch-Völkersbach');
INSERT INTO `area_codes` VALUES ('49','0721','Karlsruhe');
INSERT INTO `area_codes` VALUES ('49','07220','Forbach-Hundsbach');
INSERT INTO `area_codes` VALUES ('49','07221','Baden-Baden');
INSERT INTO `area_codes` VALUES ('49','07222','Rastatt');
INSERT INTO `area_codes` VALUES ('49','07223','Bühl Baden');
INSERT INTO `area_codes` VALUES ('49','07224','Gernsbach');
INSERT INTO `area_codes` VALUES ('49','07225','Gaggenau');
INSERT INTO `area_codes` VALUES ('49','07226','Bühl-Sand');
INSERT INTO `area_codes` VALUES ('49','07227','Lichtenau Baden');
INSERT INTO `area_codes` VALUES ('49','07228','Forbach');
INSERT INTO `area_codes` VALUES ('49','07229','Iffezheim');
INSERT INTO `area_codes` VALUES ('49','07231','Pforzheim');
INSERT INTO `area_codes` VALUES ('49','07232','Königsbach-Stein');
INSERT INTO `area_codes` VALUES ('49','07233','Niefern-Öschelbronn');
INSERT INTO `area_codes` VALUES ('49','07234','Tiefenbronn');
INSERT INTO `area_codes` VALUES ('49','07235','Unterreichenbach Kr Calw');
INSERT INTO `area_codes` VALUES ('49','07236','Keltern');
INSERT INTO `area_codes` VALUES ('49','07237','Neulingen Enzkreis');
INSERT INTO `area_codes` VALUES ('49','07240','Pfinztal');
INSERT INTO `area_codes` VALUES ('49','07242','Rheinstetten');
INSERT INTO `area_codes` VALUES ('49','07243','Ettlingen');
INSERT INTO `area_codes` VALUES ('49','07244','Weingarten Baden');
INSERT INTO `area_codes` VALUES ('49','07245','Durmersheim');
INSERT INTO `area_codes` VALUES ('49','07246','Malsch Kr Karlsruhe');
INSERT INTO `area_codes` VALUES ('49','07247','Linkenheim-Hochstetten');
INSERT INTO `area_codes` VALUES ('49','07248','Marxzell');
INSERT INTO `area_codes` VALUES ('49','07249','Stutensee');
INSERT INTO `area_codes` VALUES ('49','07250','Kraichtal');
INSERT INTO `area_codes` VALUES ('49','07251','Bruchsal');
INSERT INTO `area_codes` VALUES ('49','07252','Bretten');
INSERT INTO `area_codes` VALUES ('49','07253','Bad Schönborn');
INSERT INTO `area_codes` VALUES ('49','07254','Waghäusel');
INSERT INTO `area_codes` VALUES ('49','07255','Graben-Neudorf');
INSERT INTO `area_codes` VALUES ('49','07256','Philippsburg');
INSERT INTO `area_codes` VALUES ('49','07257','Bruchsal-Untergrombach');
INSERT INTO `area_codes` VALUES ('49','07258','Oberderdingen-Flehingen');
INSERT INTO `area_codes` VALUES ('49','07259','Östringen-Odenheim');
INSERT INTO `area_codes` VALUES ('49','07260','Sinsheim-Hilsbach');
INSERT INTO `area_codes` VALUES ('49','07261','Sinsheim');
INSERT INTO `area_codes` VALUES ('49','07262','Eppingen');
INSERT INTO `area_codes` VALUES ('49','07263','Waibstadt');
INSERT INTO `area_codes` VALUES ('49','07264','Bad Rappenau');
INSERT INTO `area_codes` VALUES ('49','07265','Angelbachtal');
INSERT INTO `area_codes` VALUES ('49','07266','Kirchardt');
INSERT INTO `area_codes` VALUES ('49','07267','Gemmingen');
INSERT INTO `area_codes` VALUES ('49','07268','Bad Rappenau-Obergimpern');
INSERT INTO `area_codes` VALUES ('49','07269','Sulzfeld Baden');
INSERT INTO `area_codes` VALUES ('49','07271','Wörth am Rhein');
INSERT INTO `area_codes` VALUES ('49','07272','Rülzheim');
INSERT INTO `area_codes` VALUES ('49','07273','Hagenbach Pfalz');
INSERT INTO `area_codes` VALUES ('49','07274','Germersheim');
INSERT INTO `area_codes` VALUES ('49','07275','Kandel');
INSERT INTO `area_codes` VALUES ('49','07276','Herxheim  bei Landau Pfalz');
INSERT INTO `area_codes` VALUES ('49','07277','Wörth-Büchelberg');
INSERT INTO `area_codes` VALUES ('49','07300','Roggenburg');
INSERT INTO `area_codes` VALUES ('49','07302','Pfaffenhofen a d Roth');
INSERT INTO `area_codes` VALUES ('49','07303','Illertissen');
INSERT INTO `area_codes` VALUES ('49','07304','Blaustein Württ');
INSERT INTO `area_codes` VALUES ('49','07305','Erbach Donau');
INSERT INTO `area_codes` VALUES ('49','07306','Vöhringen Iller');
INSERT INTO `area_codes` VALUES ('49','07307','Senden Iller');
INSERT INTO `area_codes` VALUES ('49','07308','Nersingen');
INSERT INTO `area_codes` VALUES ('49','07309','Weissenhorn');
INSERT INTO `area_codes` VALUES ('49','0731','Ulm Donau');
INSERT INTO `area_codes` VALUES ('49','07321','Heidenheim a d Brenz');
INSERT INTO `area_codes` VALUES ('49','07322','Giengen a d Brenz');
INSERT INTO `area_codes` VALUES ('49','07323','Gerstetten');
INSERT INTO `area_codes` VALUES ('49','07324','Herbrechtingen');
INSERT INTO `area_codes` VALUES ('49','07325','Sontheim a d Brenz');
INSERT INTO `area_codes` VALUES ('49','07326','Neresheim');
INSERT INTO `area_codes` VALUES ('49','07327','Dischingen');
INSERT INTO `area_codes` VALUES ('49','07328','Königsbronn');
INSERT INTO `area_codes` VALUES ('49','07329','Steinheim am Albuch');
INSERT INTO `area_codes` VALUES ('49','07331','Geislingen an der Steige');
INSERT INTO `area_codes` VALUES ('49','07332','Lauterstein');
INSERT INTO `area_codes` VALUES ('49','07333','Laichingen');
INSERT INTO `area_codes` VALUES ('49','07334','Deggingen');
INSERT INTO `area_codes` VALUES ('49','07335','Wiesensteig');
INSERT INTO `area_codes` VALUES ('49','07336','Lonsee');
INSERT INTO `area_codes` VALUES ('49','07337','Nellingen Alb');
INSERT INTO `area_codes` VALUES ('49','07340','Neenstetten');
INSERT INTO `area_codes` VALUES ('49','07343','Buch b Illertissen');
INSERT INTO `area_codes` VALUES ('49','07344','Blaubeuren');
INSERT INTO `area_codes` VALUES ('49','07345','Langenau Württ');
INSERT INTO `area_codes` VALUES ('49','07346','Illerkirchberg');
INSERT INTO `area_codes` VALUES ('49','07347','Dietenheim');
INSERT INTO `area_codes` VALUES ('49','07348','Beimerstetten');
INSERT INTO `area_codes` VALUES ('49','07351','Biberach an der Riß');
INSERT INTO `area_codes` VALUES ('49','07352','Ochsenhausen');
INSERT INTO `area_codes` VALUES ('49','07353','Schwendi');
INSERT INTO `area_codes` VALUES ('49','07354','Erolzheim');
INSERT INTO `area_codes` VALUES ('49','07355','Hochdorf Riß');
INSERT INTO `area_codes` VALUES ('49','07356','Schemmerhofen');
INSERT INTO `area_codes` VALUES ('49','07357','Attenweiler');
INSERT INTO `area_codes` VALUES ('49','07358','Eberhardzell-Füramoos');
INSERT INTO `area_codes` VALUES ('49','07361','Aalen');
INSERT INTO `area_codes` VALUES ('49','07362','Bopfingen');
INSERT INTO `area_codes` VALUES ('49','07363','Lauchheim');
INSERT INTO `area_codes` VALUES ('49','07364','Oberkochen');
INSERT INTO `area_codes` VALUES ('49','07365','Essingen Württ');
INSERT INTO `area_codes` VALUES ('49','07366','Abtsgmünd');
INSERT INTO `area_codes` VALUES ('49','07367','Aalen-Ebnat');
INSERT INTO `area_codes` VALUES ('49','07371','Riedlingen Württ');
INSERT INTO `area_codes` VALUES ('49','07373','Zwiefalten');
INSERT INTO `area_codes` VALUES ('49','07374','Uttenweiler');
INSERT INTO `area_codes` VALUES ('49','07375','Obermarchtal');
INSERT INTO `area_codes` VALUES ('49','07376','Langenenslingen');
INSERT INTO `area_codes` VALUES ('49','07381','Münsingen');
INSERT INTO `area_codes` VALUES ('49','07382','Römerstein');
INSERT INTO `area_codes` VALUES ('49','07383','Münsingen-Buttenhausen');
INSERT INTO `area_codes` VALUES ('49','07384','Schelklingen-Hütten');
INSERT INTO `area_codes` VALUES ('49','07385','Gomadingen');
INSERT INTO `area_codes` VALUES ('49','07386','Hayingen');
INSERT INTO `area_codes` VALUES ('49','07387','Hohenstein Württ');
INSERT INTO `area_codes` VALUES ('49','07388','Pfronstetten');
INSERT INTO `area_codes` VALUES ('49','07389','Heroldstatt');
INSERT INTO `area_codes` VALUES ('49','07391','Ehingen Donau');
INSERT INTO `area_codes` VALUES ('49','07392','Laupheim');
INSERT INTO `area_codes` VALUES ('49','07393','Munderkingen');
INSERT INTO `area_codes` VALUES ('49','07394','Schelklingen');
INSERT INTO `area_codes` VALUES ('49','07395','Ehingen-Dächingen');
INSERT INTO `area_codes` VALUES ('49','07402','Fluorn-Winzeln');
INSERT INTO `area_codes` VALUES ('49','07403','Dunningen');
INSERT INTO `area_codes` VALUES ('49','07404','Epfendorf');
INSERT INTO `area_codes` VALUES ('49','0741','Rottweil');
INSERT INTO `area_codes` VALUES ('49','07420','Deisslingen');
INSERT INTO `area_codes` VALUES ('49','07422','Schramberg');
INSERT INTO `area_codes` VALUES ('49','07423','Oberndorf am Neckar');
INSERT INTO `area_codes` VALUES ('49','07424','Spaichingen');
INSERT INTO `area_codes` VALUES ('49','07425','Trossingen');
INSERT INTO `area_codes` VALUES ('49','07426','Gosheim');
INSERT INTO `area_codes` VALUES ('49','07427','Schömberg b Balingen');
INSERT INTO `area_codes` VALUES ('49','07428','Rosenfeld');
INSERT INTO `area_codes` VALUES ('49','07429','Egesheim');
INSERT INTO `area_codes` VALUES ('49','07431','Albstadt-Ebingen');
INSERT INTO `area_codes` VALUES ('49','07432','Albstadt-Tailfingen');
INSERT INTO `area_codes` VALUES ('49','07433','Balingen');
INSERT INTO `area_codes` VALUES ('49','07434','Winterlingen');
INSERT INTO `area_codes` VALUES ('49','07435','Albstadt-Laufen');
INSERT INTO `area_codes` VALUES ('49','07436','Messstetten-Oberdigisheim');
INSERT INTO `area_codes` VALUES ('49','07440','Bad Rippoldsau');
INSERT INTO `area_codes` VALUES ('49','07441','Freudenstadt');
INSERT INTO `area_codes` VALUES ('49','07442','Baiersbronn');
INSERT INTO `area_codes` VALUES ('49','07443','Dornstetten');
INSERT INTO `area_codes` VALUES ('49','07444','Alpirsbach');
INSERT INTO `area_codes` VALUES ('49','07445','Pfalzgrafenweiler');
INSERT INTO `area_codes` VALUES ('49','07446','Lossburg');
INSERT INTO `area_codes` VALUES ('49','07447','Baiersbronn-Schwarzenberg');
INSERT INTO `area_codes` VALUES ('49','07448','Seewald');
INSERT INTO `area_codes` VALUES ('49','07449','Baiersbronn-Obertal');
INSERT INTO `area_codes` VALUES ('49','07451','Horb am Neckar');
INSERT INTO `area_codes` VALUES ('49','07452','Nagold');
INSERT INTO `area_codes` VALUES ('49','07453','Altensteig Württ');
INSERT INTO `area_codes` VALUES ('49','07454','Sulz am Neckar');
INSERT INTO `area_codes` VALUES ('49','07455','Dornhan');
INSERT INTO `area_codes` VALUES ('49','07456','Haiterbach');
INSERT INTO `area_codes` VALUES ('49','07457','Rottenburg-Ergenzingen');
INSERT INTO `area_codes` VALUES ('49','07458','Ebhausen');
INSERT INTO `area_codes` VALUES ('49','07459','Nagold-Hochdorf');
INSERT INTO `area_codes` VALUES ('49','07461','Tuttlingen');
INSERT INTO `area_codes` VALUES ('49','07462','Immendingen');
INSERT INTO `area_codes` VALUES ('49','07463','Mühlheim an der Donau');
INSERT INTO `area_codes` VALUES ('49','07464','Talheim Kr Tuttlingen');
INSERT INTO `area_codes` VALUES ('49','07465','Emmingen-Liptingen');
INSERT INTO `area_codes` VALUES ('49','07466','Beuron');
INSERT INTO `area_codes` VALUES ('49','07467','Neuhausen ob Eck');
INSERT INTO `area_codes` VALUES ('49','07471','Hechingen');
INSERT INTO `area_codes` VALUES ('49','07472','Rottenburg am Neckar');
INSERT INTO `area_codes` VALUES ('49','07473','Mössingen');
INSERT INTO `area_codes` VALUES ('49','07474','Haigerloch');
INSERT INTO `area_codes` VALUES ('49','07475','Burladingen');
INSERT INTO `area_codes` VALUES ('49','07476','Bisingen');
INSERT INTO `area_codes` VALUES ('49','07477','Jungingen b Hechingen');
INSERT INTO `area_codes` VALUES ('49','07478','Hirrlingen');
INSERT INTO `area_codes` VALUES ('49','07482','Horb-Dettingen');
INSERT INTO `area_codes` VALUES ('49','07483','Horb-Mühringen');
INSERT INTO `area_codes` VALUES ('49','07484','Simmersfeld');
INSERT INTO `area_codes` VALUES ('49','07485','Empfingen');
INSERT INTO `area_codes` VALUES ('49','07486','Horb-Altheim');
INSERT INTO `area_codes` VALUES ('49','07502','Wolpertswende');
INSERT INTO `area_codes` VALUES ('49','07503','Wilhelmsdorf Württ');
INSERT INTO `area_codes` VALUES ('49','07504','Horgenzell');
INSERT INTO `area_codes` VALUES ('49','07505','Fronreute');
INSERT INTO `area_codes` VALUES ('49','07506','Wangen-Leupolz');
INSERT INTO `area_codes` VALUES ('49','0751','Ravensburg');
INSERT INTO `area_codes` VALUES ('49','07520','Bodnegg');
INSERT INTO `area_codes` VALUES ('49','07522','Wangen im Allgäu');
INSERT INTO `area_codes` VALUES ('49','07524','Bad Waldsee');
INSERT INTO `area_codes` VALUES ('49','07525','Aulendorf');
INSERT INTO `area_codes` VALUES ('49','07527','Wolfegg');
INSERT INTO `area_codes` VALUES ('49','07528','Neukirch b Tettnang');
INSERT INTO `area_codes` VALUES ('49','07529','Waldburg Württ');
INSERT INTO `area_codes` VALUES ('49','07531','Konstanz');
INSERT INTO `area_codes` VALUES ('49','07532','Meersburg');
INSERT INTO `area_codes` VALUES ('49','07533','Allensbach');
INSERT INTO `area_codes` VALUES ('49','07534','Reichenau Baden');
INSERT INTO `area_codes` VALUES ('49','07541','Friedrichshafen');
INSERT INTO `area_codes` VALUES ('49','07542','Tettnang');
INSERT INTO `area_codes` VALUES ('49','07543','Kressbronn am Bodensee');
INSERT INTO `area_codes` VALUES ('49','07544','Markdorf');
INSERT INTO `area_codes` VALUES ('49','07545','Immenstaad am Bodensee');
INSERT INTO `area_codes` VALUES ('49','07546','Oberteuringen');
INSERT INTO `area_codes` VALUES ('49','07551','Überlingen Bodensee');
INSERT INTO `area_codes` VALUES ('49','07552','Pfullendorf');
INSERT INTO `area_codes` VALUES ('49','07553','Salem Baden');
INSERT INTO `area_codes` VALUES ('49','07554','Heiligenberg Baden');
INSERT INTO `area_codes` VALUES ('49','07555','Deggenhausertal');
INSERT INTO `area_codes` VALUES ('49','07556','Uhldingen-Mühlhofen');
INSERT INTO `area_codes` VALUES ('49','07557','Herdwangen-Schönach');
INSERT INTO `area_codes` VALUES ('49','07558','Illmensee');
INSERT INTO `area_codes` VALUES ('49','07561','Leutkirch im Allgäu');
INSERT INTO `area_codes` VALUES ('49','07562','Isny im Allgäu');
INSERT INTO `area_codes` VALUES ('49','07563','Kisslegg');
INSERT INTO `area_codes` VALUES ('49','07564','Bad Wurzach');
INSERT INTO `area_codes` VALUES ('49','07565','Aichstetten Kr Ravensburg');
INSERT INTO `area_codes` VALUES ('49','07566','Argenbühl');
INSERT INTO `area_codes` VALUES ('49','07567','Leutkirch-Friesenhofen');
INSERT INTO `area_codes` VALUES ('49','07568','Bad Wurzach-Hauerz');
INSERT INTO `area_codes` VALUES ('49','07569','Isny-Eisenbach');
INSERT INTO `area_codes` VALUES ('49','07570','Sigmaringen-Gutenstein');
INSERT INTO `area_codes` VALUES ('49','07571','Sigmaringen');
INSERT INTO `area_codes` VALUES ('49','07572','Mengen Württ');
INSERT INTO `area_codes` VALUES ('49','07573','Stetten am kalten Markt');
INSERT INTO `area_codes` VALUES ('49','07574','Gammertingen');
INSERT INTO `area_codes` VALUES ('49','07575','Messkirch');
INSERT INTO `area_codes` VALUES ('49','07576','Krauchenwies');
INSERT INTO `area_codes` VALUES ('49','07577','Veringenstadt');
INSERT INTO `area_codes` VALUES ('49','07578','Wald Hohenz');
INSERT INTO `area_codes` VALUES ('49','07579','Schwenningen Baden');
INSERT INTO `area_codes` VALUES ('49','07581','Saulgau');
INSERT INTO `area_codes` VALUES ('49','07582','Bad Buchau');
INSERT INTO `area_codes` VALUES ('49','07583','Bad Schussenried');
INSERT INTO `area_codes` VALUES ('49','07584','Altshausen');
INSERT INTO `area_codes` VALUES ('49','07585','Ostrach');
INSERT INTO `area_codes` VALUES ('49','07586','Herbertingen');
INSERT INTO `area_codes` VALUES ('49','07587','Hosskirch');
INSERT INTO `area_codes` VALUES ('49','07602','Oberried Breisgau');
INSERT INTO `area_codes` VALUES ('49','0761','Freiburg im Breisgau');
INSERT INTO `area_codes` VALUES ('49','07620','Schopfheim-Gersbach');
INSERT INTO `area_codes` VALUES ('49','07621','Lörrach');
INSERT INTO `area_codes` VALUES ('49','07622','Schopfheim');
INSERT INTO `area_codes` VALUES ('49','07623','Rheinfelden Baden');
INSERT INTO `area_codes` VALUES ('49','07624','Grenzach-Wyhlen');
INSERT INTO `area_codes` VALUES ('49','07625','Zell im Wiesental');
INSERT INTO `area_codes` VALUES ('49','07626','Kandern');
INSERT INTO `area_codes` VALUES ('49','07627','Steinen Kr Lörrach');
INSERT INTO `area_codes` VALUES ('49','07628','Efringen-Kirchen');
INSERT INTO `area_codes` VALUES ('49','07629','Tegernau Baden');
INSERT INTO `area_codes` VALUES ('49','07631','Müllheim Baden');
INSERT INTO `area_codes` VALUES ('49','07632','Badenweiler');
INSERT INTO `area_codes` VALUES ('49','07633','Staufen im Breisgau');
INSERT INTO `area_codes` VALUES ('49','07634','Sulzburg');
INSERT INTO `area_codes` VALUES ('49','07635','Schliengen');
INSERT INTO `area_codes` VALUES ('49','07636','Münstertal Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07641','Emmendingen');
INSERT INTO `area_codes` VALUES ('49','07642','Endingen Kaiserstuh');
INSERT INTO `area_codes` VALUES ('49','07643','Herbolzheim Breisgau');
INSERT INTO `area_codes` VALUES ('49','07644','Kenzingen');
INSERT INTO `area_codes` VALUES ('49','07645','Freiamt');
INSERT INTO `area_codes` VALUES ('49','07646','Weisweil Breisgau');
INSERT INTO `area_codes` VALUES ('49','07651','Titisee-Neustadt');
INSERT INTO `area_codes` VALUES ('49','07652','Hinterzarten');
INSERT INTO `area_codes` VALUES ('49','07653','Lenzkirch');
INSERT INTO `area_codes` VALUES ('49','07654','Löffingen');
INSERT INTO `area_codes` VALUES ('49','07655','Feldberg-Altglashütten');
INSERT INTO `area_codes` VALUES ('49','07656','Schluchsee');
INSERT INTO `area_codes` VALUES ('49','07657','Eisenbach Hochschwarzwald');
INSERT INTO `area_codes` VALUES ('49','07660','St Peter Schwarzw');
INSERT INTO `area_codes` VALUES ('49','07661','Kirchzarten');
INSERT INTO `area_codes` VALUES ('49','07662','Vogtsburg im Kaiserstuh');
INSERT INTO `area_codes` VALUES ('49','07663','Eichstetten');
INSERT INTO `area_codes` VALUES ('49','07664','Freiburg-Tiengen');
INSERT INTO `area_codes` VALUES ('49','07665','March Breisgau');
INSERT INTO `area_codes` VALUES ('49','07666','Denzlingen');
INSERT INTO `area_codes` VALUES ('49','07667','Breisach am Rhein');
INSERT INTO `area_codes` VALUES ('49','07668','Ihringen');
INSERT INTO `area_codes` VALUES ('49','07669','St Märgen');
INSERT INTO `area_codes` VALUES ('49','07671','Todtnau');
INSERT INTO `area_codes` VALUES ('49','07672','St Blasien');
INSERT INTO `area_codes` VALUES ('49','07673','Schönau im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07674','Todtmoos');
INSERT INTO `area_codes` VALUES ('49','07675','Bernau Baden');
INSERT INTO `area_codes` VALUES ('49','07676','Feldberg Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07681','Waldkirch Breisgau');
INSERT INTO `area_codes` VALUES ('49','07682','Elzach');
INSERT INTO `area_codes` VALUES ('49','07683','Simonswald');
INSERT INTO `area_codes` VALUES ('49','07684','Glottertal');
INSERT INTO `area_codes` VALUES ('49','07685','Gutach-Bleibach');
INSERT INTO `area_codes` VALUES ('49','07702','Blumberg Baden');
INSERT INTO `area_codes` VALUES ('49','07703','Bonndorf im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07704','Geisingen Baden');
INSERT INTO `area_codes` VALUES ('49','07705','Wolterdingen Schwarzw');
INSERT INTO `area_codes` VALUES ('49','07706','Oberbaldingen');
INSERT INTO `area_codes` VALUES ('49','07707','Bräunlingen');
INSERT INTO `area_codes` VALUES ('49','07708','Geisingen-Leipferdingen');
INSERT INTO `area_codes` VALUES ('49','07709','Wutach');
INSERT INTO `area_codes` VALUES ('49','0771','Donaueschingen');
INSERT INTO `area_codes` VALUES ('49','07720','Schwenningen a Neckar');
INSERT INTO `area_codes` VALUES ('49','07721','Villingen i Schwarzw');
INSERT INTO `area_codes` VALUES ('49','07722','Triberg im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07723','Furtwangen im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07724','St Georgen im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07725','Königsfeld im Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07726','Bad Dürrheim');
INSERT INTO `area_codes` VALUES ('49','07727','Vöhrenbach');
INSERT INTO `area_codes` VALUES ('49','07728','Niedereschach');
INSERT INTO `area_codes` VALUES ('49','07729','Tennenbronn');
INSERT INTO `area_codes` VALUES ('49','07731','Singen Hohentwiel');
INSERT INTO `area_codes` VALUES ('49','07732','Radolfzell am Bodensee');
INSERT INTO `area_codes` VALUES ('49','07733','Engen Hegau');
INSERT INTO `area_codes` VALUES ('49','07734','Gailingen');
INSERT INTO `area_codes` VALUES ('49','07735','Öhningen');
INSERT INTO `area_codes` VALUES ('49','07736','Tengen');
INSERT INTO `area_codes` VALUES ('49','07738','Steisslingen');
INSERT INTO `area_codes` VALUES ('49','07739','Hilzingen');
INSERT INTO `area_codes` VALUES ('49','07741','Tiengen Hochrhein');
INSERT INTO `area_codes` VALUES ('49','07742','Klettgau');
INSERT INTO `area_codes` VALUES ('49','07743','Ühlingen-Birkendorf');
INSERT INTO `area_codes` VALUES ('49','07744','Stühlingen');
INSERT INTO `area_codes` VALUES ('49','07745','Jestetten');
INSERT INTO `area_codes` VALUES ('49','07746','Wutöschingen');
INSERT INTO `area_codes` VALUES ('49','07747','Berau');
INSERT INTO `area_codes` VALUES ('49','07748','Grafenhausen Hochschwarzw');
INSERT INTO `area_codes` VALUES ('49','07751','Waldshut');
INSERT INTO `area_codes` VALUES ('49','07753','Albbruck');
INSERT INTO `area_codes` VALUES ('49','07754','Görwihl');
INSERT INTO `area_codes` VALUES ('49','07755','Weilheim Kr Waldshut');
INSERT INTO `area_codes` VALUES ('49','07761','Bad Säckingen');
INSERT INTO `area_codes` VALUES ('49','07762','Wehr Baden');
INSERT INTO `area_codes` VALUES ('49','07763','Murg');
INSERT INTO `area_codes` VALUES ('49','07764','Herrischried');
INSERT INTO `area_codes` VALUES ('49','07765','Rickenbach Hotzenw');
INSERT INTO `area_codes` VALUES ('49','07771','Stockach');
INSERT INTO `area_codes` VALUES ('49','07773','Bodman-Ludwigshafen');
INSERT INTO `area_codes` VALUES ('49','07774','Eigeltingen');
INSERT INTO `area_codes` VALUES ('49','07775','Mühlingen');
INSERT INTO `area_codes` VALUES ('49','07777','Sauldorf');
INSERT INTO `area_codes` VALUES ('49','07802','Oberkirch Baden');
INSERT INTO `area_codes` VALUES ('49','07803','Gengenbach');
INSERT INTO `area_codes` VALUES ('49','07804','Oppenau');
INSERT INTO `area_codes` VALUES ('49','07805','Appenweier');
INSERT INTO `area_codes` VALUES ('49','07806','Bad Peterstal-Griesbach');
INSERT INTO `area_codes` VALUES ('49','07807','Neuried Ortenaukreis');
INSERT INTO `area_codes` VALUES ('49','07808','Hohberg b Offenburg');
INSERT INTO `area_codes` VALUES ('49','0781','Offenburg');
INSERT INTO `area_codes` VALUES ('49','07821','Lahr Schwarzwald');
INSERT INTO `area_codes` VALUES ('49','07822','Ettenheim');
INSERT INTO `area_codes` VALUES ('49','07823','Seelbach Schutter');
INSERT INTO `area_codes` VALUES ('49','07824','Schwanau');
INSERT INTO `area_codes` VALUES ('49','07825','Kippenheim');
INSERT INTO `area_codes` VALUES ('49','07826','Schuttertal');
INSERT INTO `area_codes` VALUES ('49','07831','Hausach');
INSERT INTO `area_codes` VALUES ('49','07832','Haslach  im Kinzigtal');
INSERT INTO `area_codes` VALUES ('49','07833','Hornberg Schwarzwaldbahn');
INSERT INTO `area_codes` VALUES ('49','07834','Wolfach');
INSERT INTO `area_codes` VALUES ('49','07835','Zell am Harmersbach');
INSERT INTO `area_codes` VALUES ('49','07836','Schiltach');
INSERT INTO `area_codes` VALUES ('49','07837','Oberharmersbach');
INSERT INTO `area_codes` VALUES ('49','07838','Nordrach');
INSERT INTO `area_codes` VALUES ('49','07839','Schapbach');
INSERT INTO `area_codes` VALUES ('49','07841','Achern');
INSERT INTO `area_codes` VALUES ('49','07842','Kappelrodeck');
INSERT INTO `area_codes` VALUES ('49','07843','Renchen');
INSERT INTO `area_codes` VALUES ('49','07844','Rheinau');
INSERT INTO `area_codes` VALUES ('49','07851','Kehl');
INSERT INTO `area_codes` VALUES ('49','07852','Willstätt');
INSERT INTO `area_codes` VALUES ('49','07853','Kehl-Bodersweier');
INSERT INTO `area_codes` VALUES ('49','07854','Kehl-Goldscheuer');
INSERT INTO `area_codes` VALUES ('49','07903','Mainhardt');
INSERT INTO `area_codes` VALUES ('49','07904','Ilshofen');
INSERT INTO `area_codes` VALUES ('49','07905','Langenburg');
INSERT INTO `area_codes` VALUES ('49','07906','Braunsbach');
INSERT INTO `area_codes` VALUES ('49','07907','Schwäbisch Hall-Sulzdorf');
INSERT INTO `area_codes` VALUES ('49','0791','Schwäbisch Hall');
INSERT INTO `area_codes` VALUES ('49','07930','Boxberg Baden');
INSERT INTO `area_codes` VALUES ('49','07931','Bad Mergentheim');
INSERT INTO `area_codes` VALUES ('49','07932','Niederstetten Württ');
INSERT INTO `area_codes` VALUES ('49','07933','Creglingen');
INSERT INTO `area_codes` VALUES ('49','07934','Weikersheim');
INSERT INTO `area_codes` VALUES ('49','07935','Schrozberg');
INSERT INTO `area_codes` VALUES ('49','07936','Schrozberg-Bartenstein');
INSERT INTO `area_codes` VALUES ('49','07937','Dörzbach');
INSERT INTO `area_codes` VALUES ('49','07938','Mulfingen Jagst');
INSERT INTO `area_codes` VALUES ('49','07939','Schrozberg-Spielbach');
INSERT INTO `area_codes` VALUES ('49','07940','Künzelsau');
INSERT INTO `area_codes` VALUES ('49','07941','Öhringen');
INSERT INTO `area_codes` VALUES ('49','07942','Neuenstein Württ');
INSERT INTO `area_codes` VALUES ('49','07943','Schöntal Jagst');
INSERT INTO `area_codes` VALUES ('49','07944','Kupferzell');
INSERT INTO `area_codes` VALUES ('49','07945','Wüstenrot');
INSERT INTO `area_codes` VALUES ('49','07946','Bretzfeld');
INSERT INTO `area_codes` VALUES ('49','07947','Forchtenberg');
INSERT INTO `area_codes` VALUES ('49','07948','Öhringen-Ohrnberg');
INSERT INTO `area_codes` VALUES ('49','07949','Pfedelbach-Untersteinbach');
INSERT INTO `area_codes` VALUES ('49','07950','Schnelldorf');
INSERT INTO `area_codes` VALUES ('49','07951','Crailsheim');
INSERT INTO `area_codes` VALUES ('49','07952','Gerabronn');
INSERT INTO `area_codes` VALUES ('49','07953','Blaufelden');
INSERT INTO `area_codes` VALUES ('49','07954','Kirchberg an der Jagst');
INSERT INTO `area_codes` VALUES ('49','07955','Wallhausen  Württ');
INSERT INTO `area_codes` VALUES ('49','07957','Kressberg');
INSERT INTO `area_codes` VALUES ('49','07958','Rot Am See-Brettheim');
INSERT INTO `area_codes` VALUES ('49','07959','Frankenhardt');
INSERT INTO `area_codes` VALUES ('49','07961','Ellwangen Jagst');
INSERT INTO `area_codes` VALUES ('49','07962','Fichtenau');
INSERT INTO `area_codes` VALUES ('49','07963','Adelmannsfelden');
INSERT INTO `area_codes` VALUES ('49','07964','Stödtlen');
INSERT INTO `area_codes` VALUES ('49','07965','Ellwangen-Röhlingen');
INSERT INTO `area_codes` VALUES ('49','07966','Unterschneidheim');
INSERT INTO `area_codes` VALUES ('49','07967','Jagstzell');
INSERT INTO `area_codes` VALUES ('49','07971','Gaildorf');
INSERT INTO `area_codes` VALUES ('49','07972','Gschwend b Gaildorf');
INSERT INTO `area_codes` VALUES ('49','07973','Obersontheim');
INSERT INTO `area_codes` VALUES ('49','07974','Bühlerzell');
INSERT INTO `area_codes` VALUES ('49','07975','Untergröningen');
INSERT INTO `area_codes` VALUES ('49','07976','Sulzbach-Laufen');
INSERT INTO `area_codes` VALUES ('49','07977','Oberrot b Gaildorf');
INSERT INTO `area_codes` VALUES ('49','08020','Weyarn');
INSERT INTO `area_codes` VALUES ('49','08021','Waakirchen');
INSERT INTO `area_codes` VALUES ('49','08022','Tegernsee');
INSERT INTO `area_codes` VALUES ('49','08023','Bayrischzell');
INSERT INTO `area_codes` VALUES ('49','08024','Holzkirchen');
INSERT INTO `area_codes` VALUES ('49','08025','Miesbach');
INSERT INTO `area_codes` VALUES ('49','08026','Hausham');
INSERT INTO `area_codes` VALUES ('49','08027','Dietramszell');
INSERT INTO `area_codes` VALUES ('49','08028','Fischbachau');
INSERT INTO `area_codes` VALUES ('49','08029','Kreuth  b Tegernsee');
INSERT INTO `area_codes` VALUES ('49','08031','Rosenheim Oberbay');
INSERT INTO `area_codes` VALUES ('49','08032','Rohrdorf Kr Rosenheim');
INSERT INTO `area_codes` VALUES ('49','08033','Oberaudorf');
INSERT INTO `area_codes` VALUES ('49','08034','Brannenburg');
INSERT INTO `area_codes` VALUES ('49','08035','Raubling');
INSERT INTO `area_codes` VALUES ('49','08036','Stephanskirchen Simssee');
INSERT INTO `area_codes` VALUES ('49','08038','Vogtareuth');
INSERT INTO `area_codes` VALUES ('49','08039','Rott a Inn');
INSERT INTO `area_codes` VALUES ('49','08041','Bad Tölz');
INSERT INTO `area_codes` VALUES ('49','08042','Lenggries');
INSERT INTO `area_codes` VALUES ('49','08043','Jachenau');
INSERT INTO `area_codes` VALUES ('49','08045','Lenggries-Fall');
INSERT INTO `area_codes` VALUES ('49','08046','Bad Heilbrunn');
INSERT INTO `area_codes` VALUES ('49','08051','Prien a Chiemsee');
INSERT INTO `area_codes` VALUES ('49','08052','Aschau i Chiemgau');
INSERT INTO `area_codes` VALUES ('49','08053','Bad Endorf');
INSERT INTO `area_codes` VALUES ('49','08054','Breitbrunn a Chiemsee');
INSERT INTO `area_codes` VALUES ('49','08055','Halfing');
INSERT INTO `area_codes` VALUES ('49','08056','Eggstätt');
INSERT INTO `area_codes` VALUES ('49','08057','Aschau-Sachrang');
INSERT INTO `area_codes` VALUES ('49','08061','Bad Aibling');
INSERT INTO `area_codes` VALUES ('49','08062','Bruckmühl Mangfall');
INSERT INTO `area_codes` VALUES ('49','08063','Feldkirchen-Westerham');
INSERT INTO `area_codes` VALUES ('49','08064','Au b Bad Aibling');
INSERT INTO `area_codes` VALUES ('49','08065','Tuntenhausen-Schönau');
INSERT INTO `area_codes` VALUES ('49','08066','Bad Feilnbach');
INSERT INTO `area_codes` VALUES ('49','08067','Tuntenhausen');
INSERT INTO `area_codes` VALUES ('49','08071','Wasserburg a Inn');
INSERT INTO `area_codes` VALUES ('49','08072','Haag i OB');
INSERT INTO `area_codes` VALUES ('49','08073','Gars a Inn');
INSERT INTO `area_codes` VALUES ('49','08074','Schnaitsee');
INSERT INTO `area_codes` VALUES ('49','08075','Amerang');
INSERT INTO `area_codes` VALUES ('49','08076','Pfaffing');
INSERT INTO `area_codes` VALUES ('49','08081','Dorfen Stadt');
INSERT INTO `area_codes` VALUES ('49','08082','Schwindegg');
INSERT INTO `area_codes` VALUES ('49','08083','Isen');
INSERT INTO `area_codes` VALUES ('49','08084','Taufkirchen Vils');
INSERT INTO `area_codes` VALUES ('49','08085','Sankt Wolfgang');
INSERT INTO `area_codes` VALUES ('49','08086','Buchbach Oberbay');
INSERT INTO `area_codes` VALUES ('49','08091','Kirchseeon');
INSERT INTO `area_codes` VALUES ('49','08092','Grafing b München');
INSERT INTO `area_codes` VALUES ('49','08093','Glonn  Kr Ebersberg');
INSERT INTO `area_codes` VALUES ('49','08094','Steinhöring');
INSERT INTO `area_codes` VALUES ('49','08095','Aying');
INSERT INTO `area_codes` VALUES ('49','08102','Höhenkirchen-Siegertsbrunn');
INSERT INTO `area_codes` VALUES ('49','08104','Sauerlach');
INSERT INTO `area_codes` VALUES ('49','08105','Gilching');
INSERT INTO `area_codes` VALUES ('49','08106','Vaterstetten');
INSERT INTO `area_codes` VALUES ('49','0811','Hallbergmoos');
INSERT INTO `area_codes` VALUES ('49','08121','Markt Schwaben');
INSERT INTO `area_codes` VALUES ('49','08122','Erding');
INSERT INTO `area_codes` VALUES ('49','08123','Moosinning');
INSERT INTO `area_codes` VALUES ('49','08124','Forstern Oberbay');
INSERT INTO `area_codes` VALUES ('49','08131','Dachau');
INSERT INTO `area_codes` VALUES ('49','08133','Haimhausen Oberbay');
INSERT INTO `area_codes` VALUES ('49','08134','Odelzhausen');
INSERT INTO `area_codes` VALUES ('49','08135','Sulzemoos');
INSERT INTO `area_codes` VALUES ('49','08136','Markt Indersdorf');
INSERT INTO `area_codes` VALUES ('49','08137','Petershausen');
INSERT INTO `area_codes` VALUES ('49','08138','Schwabhausen b Dachau');
INSERT INTO `area_codes` VALUES ('49','08139','Röhrmoos');
INSERT INTO `area_codes` VALUES ('49','08141','Fürstenfeldbruck');
INSERT INTO `area_codes` VALUES ('49','08142','Olching');
INSERT INTO `area_codes` VALUES ('49','08143','Inning a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08144','Grafrath');
INSERT INTO `area_codes` VALUES ('49','08145','Mammendorf');
INSERT INTO `area_codes` VALUES ('49','08146','Moorenweis');
INSERT INTO `area_codes` VALUES ('49','08151','Starnberg');
INSERT INTO `area_codes` VALUES ('49','08152','Herrsching a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08153','Wessling');
INSERT INTO `area_codes` VALUES ('49','08157','Feldafing');
INSERT INTO `area_codes` VALUES ('49','08158','Tutzing');
INSERT INTO `area_codes` VALUES ('49','08161','Freising');
INSERT INTO `area_codes` VALUES ('49','08165','Neufahrn b Freising');
INSERT INTO `area_codes` VALUES ('49','08166','Allershausen Oberbay');
INSERT INTO `area_codes` VALUES ('49','08167','Zolling');
INSERT INTO `area_codes` VALUES ('49','08168','Attenkirchen');
INSERT INTO `area_codes` VALUES ('49','08170','Straßlach-Dingharting');
INSERT INTO `area_codes` VALUES ('49','08171','Wolfratshausen');
INSERT INTO `area_codes` VALUES ('49','08176','Egling b Wolfratshausen');
INSERT INTO `area_codes` VALUES ('49','08177','Münsing Starnberger See');
INSERT INTO `area_codes` VALUES ('49','08178','Icking');
INSERT INTO `area_codes` VALUES ('49','08179','Eurasburg a d Loisach');
INSERT INTO `area_codes` VALUES ('49','08191','Landsberg a Lech');
INSERT INTO `area_codes` VALUES ('49','08192','Schondorf a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08193','Geltendorf');
INSERT INTO `area_codes` VALUES ('49','08194','Vilgertshofen');
INSERT INTO `area_codes` VALUES ('49','08195','Weil Kr Landsberg a Lech');
INSERT INTO `area_codes` VALUES ('49','08196','Pürgen');
INSERT INTO `area_codes` VALUES ('49','08202','Althegnenberg');
INSERT INTO `area_codes` VALUES ('49','08203','Grossaitingen');
INSERT INTO `area_codes` VALUES ('49','08204','Mickhausen');
INSERT INTO `area_codes` VALUES ('49','08205','Dasing');
INSERT INTO `area_codes` VALUES ('49','08206','Egling a d Paar');
INSERT INTO `area_codes` VALUES ('49','08207','Affing');
INSERT INTO `area_codes` VALUES ('49','08208','Eurasburg b Augsburg');
INSERT INTO `area_codes` VALUES ('49','0821','Augsburg');
INSERT INTO `area_codes` VALUES ('49','08221','Günzburg');
INSERT INTO `area_codes` VALUES ('49','08222','Burgau Schwab');
INSERT INTO `area_codes` VALUES ('49','08223','Ichenhausen');
INSERT INTO `area_codes` VALUES ('49','08224','Offingen Donau');
INSERT INTO `area_codes` VALUES ('49','08225','Jettingen-Scheppach');
INSERT INTO `area_codes` VALUES ('49','08226','Bibertal');
INSERT INTO `area_codes` VALUES ('49','08230','Gablingen');
INSERT INTO `area_codes` VALUES ('49','08231','Königsbrunn b Augsburg');
INSERT INTO `area_codes` VALUES ('49','08232','Schwabmünchen');
INSERT INTO `area_codes` VALUES ('49','08233','Kissing');
INSERT INTO `area_codes` VALUES ('49','08234','Bobingen');
INSERT INTO `area_codes` VALUES ('49','08236','Fischach');
INSERT INTO `area_codes` VALUES ('49','08237','Aindling');
INSERT INTO `area_codes` VALUES ('49','08238','Gessertshausen');
INSERT INTO `area_codes` VALUES ('49','08239','Langenneufnach');
INSERT INTO `area_codes` VALUES ('49','08241','Buchloe');
INSERT INTO `area_codes` VALUES ('49','08243','Fuchstal');
INSERT INTO `area_codes` VALUES ('49','08245','Türkheim Wertach');
INSERT INTO `area_codes` VALUES ('49','08246','Waal');
INSERT INTO `area_codes` VALUES ('49','08247','Bad Wörishofen');
INSERT INTO `area_codes` VALUES ('49','08248','Lamerdingen');
INSERT INTO `area_codes` VALUES ('49','08249','Ettringen  Wertach');
INSERT INTO `area_codes` VALUES ('49','08250','Hilgertshausen-Tandern');
INSERT INTO `area_codes` VALUES ('49','08251','Aichach');
INSERT INTO `area_codes` VALUES ('49','08252','Schrobenhausen');
INSERT INTO `area_codes` VALUES ('49','08253','Pöttmes');
INSERT INTO `area_codes` VALUES ('49','08254','Altomünster');
INSERT INTO `area_codes` VALUES ('49','08257','Inchenhofen');
INSERT INTO `area_codes` VALUES ('49','08258','Sielenbach');
INSERT INTO `area_codes` VALUES ('49','08259','Schiltberg');
INSERT INTO `area_codes` VALUES ('49','08261','Mindelheim');
INSERT INTO `area_codes` VALUES ('49','08262','Mittelneufnach');
INSERT INTO `area_codes` VALUES ('49','08263','Breitenbrunn Schwab');
INSERT INTO `area_codes` VALUES ('49','08265','Pfaffenhausen Schwab');
INSERT INTO `area_codes` VALUES ('49','08266','Kirchheim i Schw');
INSERT INTO `area_codes` VALUES ('49','08267','Dirlewang');
INSERT INTO `area_codes` VALUES ('49','08268','Tussenhausen');
INSERT INTO `area_codes` VALUES ('49','08269','Unteregg b Mindelheim');
INSERT INTO `area_codes` VALUES ('49','08271','Meitingen');
INSERT INTO `area_codes` VALUES ('49','08272','Wertingen');
INSERT INTO `area_codes` VALUES ('49','08273','Nordendorf');
INSERT INTO `area_codes` VALUES ('49','08274','Buttenwiesen');
INSERT INTO `area_codes` VALUES ('49','08276','Baar Schwaben');
INSERT INTO `area_codes` VALUES ('49','08281','Thannhausen Schwab');
INSERT INTO `area_codes` VALUES ('49','08282','Krumbach Schwaben');
INSERT INTO `area_codes` VALUES ('49','08283','Neuburg a d Kammel');
INSERT INTO `area_codes` VALUES ('49','08284','Ziemetshausen');
INSERT INTO `area_codes` VALUES ('49','08285','Burtenbach');
INSERT INTO `area_codes` VALUES ('49','08291','Zusmarshausen');
INSERT INTO `area_codes` VALUES ('49','08292','Dinkelscherben');
INSERT INTO `area_codes` VALUES ('49','08293','Welden b Augsburg');
INSERT INTO `area_codes` VALUES ('49','08294','Horgau');
INSERT INTO `area_codes` VALUES ('49','08295','Altenmünster Schwab');
INSERT INTO `area_codes` VALUES ('49','08296','Villenbach');
INSERT INTO `area_codes` VALUES ('49','08302','Görisried');
INSERT INTO `area_codes` VALUES ('49','08303','Waltenhofen');
INSERT INTO `area_codes` VALUES ('49','08304','Wildpoldsried');
INSERT INTO `area_codes` VALUES ('49','08306','Ronsberg');
INSERT INTO `area_codes` VALUES ('49','0831','Kempten Allgäu');
INSERT INTO `area_codes` VALUES ('49','08320','Missen-Wilhams');
INSERT INTO `area_codes` VALUES ('49','08321','Sonthofen');
INSERT INTO `area_codes` VALUES ('49','08322','Oberstdorf');
INSERT INTO `area_codes` VALUES ('49','08323','Immenstadt i Allgäu');
INSERT INTO `area_codes` VALUES ('49','08324','Hindelang');
INSERT INTO `area_codes` VALUES ('49','08325','Oberstaufen-Thalkirchdorf');
INSERT INTO `area_codes` VALUES ('49','08326','Fischen  i Allgäu');
INSERT INTO `area_codes` VALUES ('49','08327','Rettenberg');
INSERT INTO `area_codes` VALUES ('49','08328','Balderschwang');
INSERT INTO `area_codes` VALUES ('49','08329','Riezlern (Österreich)');
INSERT INTO `area_codes` VALUES ('49','08330','Legau');
INSERT INTO `area_codes` VALUES ('49','08331','Memmingen');
INSERT INTO `area_codes` VALUES ('49','08332','Ottobeuren');
INSERT INTO `area_codes` VALUES ('49','08333','Babenhausen Schwab');
INSERT INTO `area_codes` VALUES ('49','08334','Bad Grönenbach');
INSERT INTO `area_codes` VALUES ('49','08335','Fellheim');
INSERT INTO `area_codes` VALUES ('49','08336','Erkheim');
INSERT INTO `area_codes` VALUES ('49','08337','Altenstadt Iller');
INSERT INTO `area_codes` VALUES ('49','08338','Böhen');
INSERT INTO `area_codes` VALUES ('49','08340','Baisweil');
INSERT INTO `area_codes` VALUES ('49','08341','Kaufbeuren');
INSERT INTO `area_codes` VALUES ('49','08342','Marktoberdorf');
INSERT INTO `area_codes` VALUES ('49','08343','Aitrang');
INSERT INTO `area_codes` VALUES ('49','08344','Westendorf b Kaufbeuren');
INSERT INTO `area_codes` VALUES ('49','08345','Stöttwang');
INSERT INTO `area_codes` VALUES ('49','08346','Pforzen');
INSERT INTO `area_codes` VALUES ('49','08347','Friesenried');
INSERT INTO `area_codes` VALUES ('49','08348','Bidingen');
INSERT INTO `area_codes` VALUES ('49','08349','Stötten a Auerberg');
INSERT INTO `area_codes` VALUES ('49','08361','Nesselwang');
INSERT INTO `area_codes` VALUES ('49','08362','Füssen');
INSERT INTO `area_codes` VALUES ('49','08363','Pfronten');
INSERT INTO `area_codes` VALUES ('49','08364','Seeg');
INSERT INTO `area_codes` VALUES ('49','08365','Wertach');
INSERT INTO `area_codes` VALUES ('49','08366','Oy-Mittelberg');
INSERT INTO `area_codes` VALUES ('49','08367','Roßhaupten Forggensee');
INSERT INTO `area_codes` VALUES ('49','08368','Halblech');
INSERT INTO `area_codes` VALUES ('49','08369','Rückholz');
INSERT INTO `area_codes` VALUES ('49','08370','Wiggensbach');
INSERT INTO `area_codes` VALUES ('49','08372','Obergünzburg');
INSERT INTO `area_codes` VALUES ('49','08373','Altusried');
INSERT INTO `area_codes` VALUES ('49','08374','Dietmannsried');
INSERT INTO `area_codes` VALUES ('49','08375','Weitnau');
INSERT INTO `area_codes` VALUES ('49','08376','Sulzberg Allgäu');
INSERT INTO `area_codes` VALUES ('49','08377','Unterthingau');
INSERT INTO `area_codes` VALUES ('49','08378','Buchenberg b Kempten');
INSERT INTO `area_codes` VALUES ('49','08379','Waltenhofen-Oberdorf');
INSERT INTO `area_codes` VALUES ('49','08380','Achberg');
INSERT INTO `area_codes` VALUES ('49','08381','Lindenberg  i Allgäu');
INSERT INTO `area_codes` VALUES ('49','08382','Lindau Bodensee');
INSERT INTO `area_codes` VALUES ('49','08383','Grünenbach Allgäu');
INSERT INTO `area_codes` VALUES ('49','08384','Röthenbach Allgäu');
INSERT INTO `area_codes` VALUES ('49','08385','Hergatz');
INSERT INTO `area_codes` VALUES ('49','08386','Oberstaufen');
INSERT INTO `area_codes` VALUES ('49','08387','Weiler-Simmerberg');
INSERT INTO `area_codes` VALUES ('49','08388','Hergensweiler');
INSERT INTO `area_codes` VALUES ('49','08389','Weissensberg');
INSERT INTO `area_codes` VALUES ('49','08392','Markt Rettenbach');
INSERT INTO `area_codes` VALUES ('49','08393','Holzgünz');
INSERT INTO `area_codes` VALUES ('49','08394','Lautrach');
INSERT INTO `area_codes` VALUES ('49','08395','Tannheim Württ');
INSERT INTO `area_codes` VALUES ('49','08402','Münchsmünster');
INSERT INTO `area_codes` VALUES ('49','08403','Pförring');
INSERT INTO `area_codes` VALUES ('49','08404','Oberdolling');
INSERT INTO `area_codes` VALUES ('49','08405','Stammham b Ingolstadt');
INSERT INTO `area_codes` VALUES ('49','08406','Böhmfeld');
INSERT INTO `area_codes` VALUES ('49','08407','Grossmehring');
INSERT INTO `area_codes` VALUES ('49','0841','Ingolstadt Donau');
INSERT INTO `area_codes` VALUES ('49','08421','Eichstätt Bay');
INSERT INTO `area_codes` VALUES ('49','08422','Dollnstein');
INSERT INTO `area_codes` VALUES ('49','08423','Titting');
INSERT INTO `area_codes` VALUES ('49','08424','Nassenfels');
INSERT INTO `area_codes` VALUES ('49','08426','Walting Kr Eichstätt');
INSERT INTO `area_codes` VALUES ('49','08427','Wellheim');
INSERT INTO `area_codes` VALUES ('49','08431','Neuburg  a d Donau');
INSERT INTO `area_codes` VALUES ('49','08432','Burgheim');
INSERT INTO `area_codes` VALUES ('49','08433','Königsmoos');
INSERT INTO `area_codes` VALUES ('49','08434','Rennertshofen');
INSERT INTO `area_codes` VALUES ('49','08435','Ehekirchen');
INSERT INTO `area_codes` VALUES ('49','08441','Pfaffenhofen a d Ilm');
INSERT INTO `area_codes` VALUES ('49','08442','Wolnzach');
INSERT INTO `area_codes` VALUES ('49','08443','Hohenwart Paar');
INSERT INTO `area_codes` VALUES ('49','08444','Schweitenkirchen');
INSERT INTO `area_codes` VALUES ('49','08445','Gerolsbach');
INSERT INTO `area_codes` VALUES ('49','08446','Pörnbach');
INSERT INTO `area_codes` VALUES ('49','08450','Ingolstadt-Zuchering');
INSERT INTO `area_codes` VALUES ('49','08452','Geisenfeld');
INSERT INTO `area_codes` VALUES ('49','08453','Reichertshofen Oberbay');
INSERT INTO `area_codes` VALUES ('49','08454','Karlshuld');
INSERT INTO `area_codes` VALUES ('49','08456','Lenting');
INSERT INTO `area_codes` VALUES ('49','08457','Vohburg a d Donau');
INSERT INTO `area_codes` VALUES ('49','08458','Gaimersheim');
INSERT INTO `area_codes` VALUES ('49','08459','Manching');
INSERT INTO `area_codes` VALUES ('49','08460','Berching-Holnstein');
INSERT INTO `area_codes` VALUES ('49','08461','Beilngries');
INSERT INTO `area_codes` VALUES ('49','08462','Berching');
INSERT INTO `area_codes` VALUES ('49','08463','Greding');
INSERT INTO `area_codes` VALUES ('49','08464','Dietfurt a d Altmühl');
INSERT INTO `area_codes` VALUES ('49','08465','Kipfenberg');
INSERT INTO `area_codes` VALUES ('49','08466','Denkendorf Oberbay');
INSERT INTO `area_codes` VALUES ('49','08467','Kinding');
INSERT INTO `area_codes` VALUES ('49','08468','Altmannstein-Pondorf');
INSERT INTO `area_codes` VALUES ('49','08469','Freystadt-Burggriesbach');
INSERT INTO `area_codes` VALUES ('49','08501','Thyrnau');
INSERT INTO `area_codes` VALUES ('49','08502','Fürstenzell');
INSERT INTO `area_codes` VALUES ('49','08503','Neuhaus a Inn');
INSERT INTO `area_codes` VALUES ('49','08504','Tittling');
INSERT INTO `area_codes` VALUES ('49','08505','Hutthurm');
INSERT INTO `area_codes` VALUES ('49','08506','Bad Höhenstadt');
INSERT INTO `area_codes` VALUES ('49','08507','Neuburg a Inn');
INSERT INTO `area_codes` VALUES ('49','08509','Ruderting');
INSERT INTO `area_codes` VALUES ('49','0851','Passau');
INSERT INTO `area_codes` VALUES ('49','08531','Pocking');
INSERT INTO `area_codes` VALUES ('49','08532','Griesbach i Rottal');
INSERT INTO `area_codes` VALUES ('49','08533','Rotthalmünster');
INSERT INTO `area_codes` VALUES ('49','08534','Tettenweis');
INSERT INTO `area_codes` VALUES ('49','08535','Haarbach');
INSERT INTO `area_codes` VALUES ('49','08536','Kößlarn');
INSERT INTO `area_codes` VALUES ('49','08537','Bad Füssing-Aigen');
INSERT INTO `area_codes` VALUES ('49','08538','Pocking-Hartkirchen');
INSERT INTO `area_codes` VALUES ('49','08541','Vilshofen Niederbay');
INSERT INTO `area_codes` VALUES ('49','08542','Ortenburg');
INSERT INTO `area_codes` VALUES ('49','08543','Aidenbach');
INSERT INTO `area_codes` VALUES ('49','08544','Eging a See');
INSERT INTO `area_codes` VALUES ('49','08545','Hofkirchen Bay');
INSERT INTO `area_codes` VALUES ('49','08546','Windorf-Otterskirchen');
INSERT INTO `area_codes` VALUES ('49','08547','Osterhofen-Gergweis');
INSERT INTO `area_codes` VALUES ('49','08548','Vilshofen-Sandbach');
INSERT INTO `area_codes` VALUES ('49','08549','Vilshofen-Pleinting');
INSERT INTO `area_codes` VALUES ('49','08550','Philippsreut');
INSERT INTO `area_codes` VALUES ('49','08551','Freyung');
INSERT INTO `area_codes` VALUES ('49','08552','Grafenau Niederbay');
INSERT INTO `area_codes` VALUES ('49','08553','Spiegelau');
INSERT INTO `area_codes` VALUES ('49','08554','Schönberg Niederbay');
INSERT INTO `area_codes` VALUES ('49','08555','Perlesreut');
INSERT INTO `area_codes` VALUES ('49','08556','Haidmühle');
INSERT INTO `area_codes` VALUES ('49','08557','Mauth');
INSERT INTO `area_codes` VALUES ('49','08558','Hohenau Niederbay');
INSERT INTO `area_codes` VALUES ('49','08561','Pfarrkirchen Niederbay');
INSERT INTO `area_codes` VALUES ('49','08562','Triftern');
INSERT INTO `area_codes` VALUES ('49','08563','Bad Birnbach Rottal');
INSERT INTO `area_codes` VALUES ('49','08564','Johanniskirchen');
INSERT INTO `area_codes` VALUES ('49','08565','Dietersburg-Baumgarten');
INSERT INTO `area_codes` VALUES ('49','08571','Simbach a Inn');
INSERT INTO `area_codes` VALUES ('49','08572','Tann Niederbay');
INSERT INTO `area_codes` VALUES ('49','08573','Ering');
INSERT INTO `area_codes` VALUES ('49','08574','Wittibreut');
INSERT INTO `area_codes` VALUES ('49','08581','Waldkirchen Niederbay');
INSERT INTO `area_codes` VALUES ('49','08582','Röhrnbach');
INSERT INTO `area_codes` VALUES ('49','08583','Neureichenau');
INSERT INTO `area_codes` VALUES ('49','08584','Breitenberg Niederbay');
INSERT INTO `area_codes` VALUES ('49','08585','Grainet');
INSERT INTO `area_codes` VALUES ('49','08586','Hauzenberg');
INSERT INTO `area_codes` VALUES ('49','08591','Obernzell');
INSERT INTO `area_codes` VALUES ('49','08592','Wegscheid Niederbay');
INSERT INTO `area_codes` VALUES ('49','08593','Untergriesbach');
INSERT INTO `area_codes` VALUES ('49','0861','Traunstein');
INSERT INTO `area_codes` VALUES ('49','08621','Trostberg');
INSERT INTO `area_codes` VALUES ('49','08622','Tacherting- Peterskirchen');
INSERT INTO `area_codes` VALUES ('49','08623','Kirchweidach');
INSERT INTO `area_codes` VALUES ('49','08624','Obing');
INSERT INTO `area_codes` VALUES ('49','08628','Kienberg Oberbay');
INSERT INTO `area_codes` VALUES ('49','08629','Palling');
INSERT INTO `area_codes` VALUES ('49','08630','Oberneukirchen');
INSERT INTO `area_codes` VALUES ('49','08631','Mühldorf a Inn');
INSERT INTO `area_codes` VALUES ('49','08633','Tüßling');
INSERT INTO `area_codes` VALUES ('49','08634','Garching a d Alz');
INSERT INTO `area_codes` VALUES ('49','08635','Pleiskirchen');
INSERT INTO `area_codes` VALUES ('49','08636','Ampfing');
INSERT INTO `area_codes` VALUES ('49','08637','Lohkirchen');
INSERT INTO `area_codes` VALUES ('49','08638','Waldkraiburg');
INSERT INTO `area_codes` VALUES ('49','08639','Neumarkt-Sankt Veit');
INSERT INTO `area_codes` VALUES ('49','08640','Reit Im Winkl');
INSERT INTO `area_codes` VALUES ('49','08641','Grassau Kr Traunstein');
INSERT INTO `area_codes` VALUES ('49','08642','Übersee');
INSERT INTO `area_codes` VALUES ('49','08649','Schleching');
INSERT INTO `area_codes` VALUES ('49','08650','Marktschellenberg');
INSERT INTO `area_codes` VALUES ('49','08651','Bad Reichenhall');
INSERT INTO `area_codes` VALUES ('49','08652','Berchtesgaden');
INSERT INTO `area_codes` VALUES ('49','08654','Freilassing');
INSERT INTO `area_codes` VALUES ('49','08656','Anger');
INSERT INTO `area_codes` VALUES ('49','08657','Ramsau b Berchtesgaden');
INSERT INTO `area_codes` VALUES ('49','08661','Grabenstätt Chiemsee');
INSERT INTO `area_codes` VALUES ('49','08662','Siegsdorf Kr Traunstein');
INSERT INTO `area_codes` VALUES ('49','08663','Ruhpolding');
INSERT INTO `area_codes` VALUES ('49','08664','Chieming');
INSERT INTO `area_codes` VALUES ('49','08665','Inzell');
INSERT INTO `area_codes` VALUES ('49','08666','Teisendorf');
INSERT INTO `area_codes` VALUES ('49','08667','Seeon-Seebruck');
INSERT INTO `area_codes` VALUES ('49','08669','Traunreut');
INSERT INTO `area_codes` VALUES ('49','08670','Reischach Kr Altötting');
INSERT INTO `area_codes` VALUES ('49','08671','Altötting');
INSERT INTO `area_codes` VALUES ('49','08677','Burghausen Salzach');
INSERT INTO `area_codes` VALUES ('49','08678','Marktl');
INSERT INTO `area_codes` VALUES ('49','08679','Burgkirchen a d Alz');
INSERT INTO `area_codes` VALUES ('49','08681','Waging a See');
INSERT INTO `area_codes` VALUES ('49','08682','Laufen Salzach');
INSERT INTO `area_codes` VALUES ('49','08683','Tittmoning');
INSERT INTO `area_codes` VALUES ('49','08684','Fridolfing');
INSERT INTO `area_codes` VALUES ('49','08685','Kirchanschöring');
INSERT INTO `area_codes` VALUES ('49','08686','Petting');
INSERT INTO `area_codes` VALUES ('49','08687','Taching-Tengling');
INSERT INTO `area_codes` VALUES ('49','08702','Wörth a d Isar');
INSERT INTO `area_codes` VALUES ('49','08703','Essenbach');
INSERT INTO `area_codes` VALUES ('49','08704','Altdorf-Pfettrach');
INSERT INTO `area_codes` VALUES ('49','08705','Altfraunhofen');
INSERT INTO `area_codes` VALUES ('49','08706','Vilsheim');
INSERT INTO `area_codes` VALUES ('49','08707','Adlkofen');
INSERT INTO `area_codes` VALUES ('49','08708','Weihmichl-Unterneuhausen');
INSERT INTO `area_codes` VALUES ('49','08709','Eching Niederbay');
INSERT INTO `area_codes` VALUES ('49','0871','Landshut');
INSERT INTO `area_codes` VALUES ('49','08721','Eggenfelden');
INSERT INTO `area_codes` VALUES ('49','08722','Gangkofen');
INSERT INTO `area_codes` VALUES ('49','08723','Arnstorf');
INSERT INTO `area_codes` VALUES ('49','08724','Massing');
INSERT INTO `area_codes` VALUES ('49','08725','Wurmannsquick');
INSERT INTO `area_codes` VALUES ('49','08726','Schönau Niederbay');
INSERT INTO `area_codes` VALUES ('49','08727','Falkenberg Niederbay');
INSERT INTO `area_codes` VALUES ('49','08728','Geratskirchen');
INSERT INTO `area_codes` VALUES ('49','08731','Dingolfing');
INSERT INTO `area_codes` VALUES ('49','08732','Frontenhausen');
INSERT INTO `area_codes` VALUES ('49','08733','Mengkofen');
INSERT INTO `area_codes` VALUES ('49','08734','Reisbach Niederbay');
INSERT INTO `area_codes` VALUES ('49','08735','Gangkofen-Kollbach');
INSERT INTO `area_codes` VALUES ('49','08741','Vilsbiburg');
INSERT INTO `area_codes` VALUES ('49','08742','Velden Vils');
INSERT INTO `area_codes` VALUES ('49','08743','Geisenhausen');
INSERT INTO `area_codes` VALUES ('49','08744','Gerzen');
INSERT INTO `area_codes` VALUES ('49','08745','Bodenkirchen');
INSERT INTO `area_codes` VALUES ('49','08751','Mainburg');
INSERT INTO `area_codes` VALUES ('49','08752','Au i d Hallertau');
INSERT INTO `area_codes` VALUES ('49','08753','Elsendorf Niederbay');
INSERT INTO `area_codes` VALUES ('49','08754','Volkenschwand');
INSERT INTO `area_codes` VALUES ('49','08756','Nandlstadt');
INSERT INTO `area_codes` VALUES ('49','08761','Moosburg a d Isar');
INSERT INTO `area_codes` VALUES ('49','08762','Wartenberg Oberbay');
INSERT INTO `area_codes` VALUES ('49','08764','Mauern Kr Freising');
INSERT INTO `area_codes` VALUES ('49','08765','Bruckberg Niederbay');
INSERT INTO `area_codes` VALUES ('49','08766','Gammelsdorf');
INSERT INTO `area_codes` VALUES ('49','08771','Ergoldsbach');
INSERT INTO `area_codes` VALUES ('49','08772','Mallersdorf-Pfaffenberg');
INSERT INTO `area_codes` VALUES ('49','08773','Neufahrn i NB');
INSERT INTO `area_codes` VALUES ('49','08774','Bayerbach b Ergoldsbach');
INSERT INTO `area_codes` VALUES ('49','08781','Rottenburg a d Laaber');
INSERT INTO `area_codes` VALUES ('49','08782','Pfeffenhausen');
INSERT INTO `area_codes` VALUES ('49','08783','Rohr i NB');
INSERT INTO `area_codes` VALUES ('49','08784','Hohenthann');
INSERT INTO `area_codes` VALUES ('49','08785','Rottenburg-Oberroning');
INSERT INTO `area_codes` VALUES ('49','08801','Seeshaupt');
INSERT INTO `area_codes` VALUES ('49','08802','Huglfing');
INSERT INTO `area_codes` VALUES ('49','08803','Peissenberg');
INSERT INTO `area_codes` VALUES ('49','08805','Hohenpeissenberg');
INSERT INTO `area_codes` VALUES ('49','08806','Utting a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08807','Dießen a Ammersee');
INSERT INTO `area_codes` VALUES ('49','08808','Pähl');
INSERT INTO `area_codes` VALUES ('49','08809','Wessobrunn');
INSERT INTO `area_codes` VALUES ('49','0881','Weilheim i OB');
INSERT INTO `area_codes` VALUES ('49','08821','Garmisch-Partenkirchen');
INSERT INTO `area_codes` VALUES ('49','08822','Oberammergau');
INSERT INTO `area_codes` VALUES ('49','08823','Mittenwald');
INSERT INTO `area_codes` VALUES ('49','08824','Oberau Loisach');
INSERT INTO `area_codes` VALUES ('49','08825','Krün');
INSERT INTO `area_codes` VALUES ('49','08841','Murnau a Staffelsee');
INSERT INTO `area_codes` VALUES ('49','08845','Bad Kohlgrub');
INSERT INTO `area_codes` VALUES ('49','08846','Uffing a Staffelsee');
INSERT INTO `area_codes` VALUES ('49','08847','Obersöchering');
INSERT INTO `area_codes` VALUES ('49','08851','Kochel a See');
INSERT INTO `area_codes` VALUES ('49','08856','Penzberg');
INSERT INTO `area_codes` VALUES ('49','08857','Benediktbeuern');
INSERT INTO `area_codes` VALUES ('49','08858','Kochel-Walchensee');
INSERT INTO `area_codes` VALUES ('49','08860','Bernbeuren');
INSERT INTO `area_codes` VALUES ('49','08861','Schongau');
INSERT INTO `area_codes` VALUES ('49','08862','Steingaden Oberbay');
INSERT INTO `area_codes` VALUES ('49','08867','Rottenbuch Oberbay');
INSERT INTO `area_codes` VALUES ('49','08868','Schwabsoien');
INSERT INTO `area_codes` VALUES ('49','08869','Kinsau');
INSERT INTO `area_codes` VALUES ('49','089','München');
INSERT INTO `area_codes` VALUES ('49','0906','Donauwörth');
INSERT INTO `area_codes` VALUES ('49','09070','Tapfheim');
INSERT INTO `area_codes` VALUES ('49','09071','Dillingen a d Donau');
INSERT INTO `area_codes` VALUES ('49','09072','Lauingen Donau');
INSERT INTO `area_codes` VALUES ('49','09073','Gundelfingen a d Donau');
INSERT INTO `area_codes` VALUES ('49','09074','Höchstädt a d Donau');
INSERT INTO `area_codes` VALUES ('49','09075','Glött');
INSERT INTO `area_codes` VALUES ('49','09076','Wittislingen');
INSERT INTO `area_codes` VALUES ('49','09077','Bachhagel');
INSERT INTO `area_codes` VALUES ('49','09078','Mertingen');
INSERT INTO `area_codes` VALUES ('49','09080','Harburg Schwaben');
INSERT INTO `area_codes` VALUES ('49','09081','Nördlingen');
INSERT INTO `area_codes` VALUES ('49','09082','Oettingen i Bay');
INSERT INTO `area_codes` VALUES ('49','09083','Möttingen');
INSERT INTO `area_codes` VALUES ('49','09084','Bissingen Schwab');
INSERT INTO `area_codes` VALUES ('49','09085','Alerheim');
INSERT INTO `area_codes` VALUES ('49','09086','Fremdingen');
INSERT INTO `area_codes` VALUES ('49','09087','Marktoffingen');
INSERT INTO `area_codes` VALUES ('49','09088','Mönchsdeggingen');
INSERT INTO `area_codes` VALUES ('49','09089','Bissingen-Unterringingen');
INSERT INTO `area_codes` VALUES ('49','09090','Rain Lech');
INSERT INTO `area_codes` VALUES ('49','09091','Monheim Schwab');
INSERT INTO `area_codes` VALUES ('49','09092','Wemding');
INSERT INTO `area_codes` VALUES ('49','09093','Polsingen');
INSERT INTO `area_codes` VALUES ('49','09094','Tagmersheim');
INSERT INTO `area_codes` VALUES ('49','09097','Marxheim');
INSERT INTO `area_codes` VALUES ('49','09099','Kaisheim');
INSERT INTO `area_codes` VALUES ('49','09101','Langenzenn');
INSERT INTO `area_codes` VALUES ('49','09102','Wilhermsdorf');
INSERT INTO `area_codes` VALUES ('49','09103','Cadolzburg');
INSERT INTO `area_codes` VALUES ('49','09104','Emskirchen');
INSERT INTO `area_codes` VALUES ('49','09105','Grosshabersdorf');
INSERT INTO `area_codes` VALUES ('49','09106','Markt Erlbach');
INSERT INTO `area_codes` VALUES ('49','09107','Trautskirchen');
INSERT INTO `area_codes` VALUES ('49','0911','Nürnberg');
INSERT INTO `area_codes` VALUES ('49','09120','Leinburg');
INSERT INTO `area_codes` VALUES ('49','09122','Schwabach');
INSERT INTO `area_codes` VALUES ('49','09123','Lauf a d Pegnitz');
INSERT INTO `area_codes` VALUES ('49','09126','Eckental');
INSERT INTO `area_codes` VALUES ('49','09127','Rosstal Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09128','Feucht');
INSERT INTO `area_codes` VALUES ('49','09129','Wendelstein');
INSERT INTO `area_codes` VALUES ('49','09131','Erlangen');
INSERT INTO `area_codes` VALUES ('49','09132','Herzogenaurach');
INSERT INTO `area_codes` VALUES ('49','09133','Baiersdorf Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09134','Neunkirchen a Brand');
INSERT INTO `area_codes` VALUES ('49','09135','Heßdorf Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09141','Weißenburg i Bay');
INSERT INTO `area_codes` VALUES ('49','09142','Treuchtlingen');
INSERT INTO `area_codes` VALUES ('49','09143','Pappenheim Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09144','Pleinfeld');
INSERT INTO `area_codes` VALUES ('49','09145','Solnhofen');
INSERT INTO `area_codes` VALUES ('49','09146','Markt Berolzheim');
INSERT INTO `area_codes` VALUES ('49','09147','Nennslingen');
INSERT INTO `area_codes` VALUES ('49','09148','Ettenstatt');
INSERT INTO `area_codes` VALUES ('49','09149','Weissenburg-Suffersheim');
INSERT INTO `area_codes` VALUES ('49','09151','Hersbruck');
INSERT INTO `area_codes` VALUES ('49','09152','Hartenstein Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09153','Schnaittach');
INSERT INTO `area_codes` VALUES ('49','09154','Pommelsbrunn');
INSERT INTO `area_codes` VALUES ('49','09155','Simmelsdorf');
INSERT INTO `area_codes` VALUES ('49','09156','Neuhaus a d Pegnitz');
INSERT INTO `area_codes` VALUES ('49','09157','Alfeld Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09158','Offenhausen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09161','Neustadt a d Aisch');
INSERT INTO `area_codes` VALUES ('49','09162','Scheinfeld');
INSERT INTO `area_codes` VALUES ('49','09163','Dachsbach');
INSERT INTO `area_codes` VALUES ('49','09164','Langenfeld Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09165','Sugenheim');
INSERT INTO `area_codes` VALUES ('49','09166','Münchsteinach');
INSERT INTO `area_codes` VALUES ('49','09167','Oberscheinfeld');
INSERT INTO `area_codes` VALUES ('49','09170','Schwanstetten');
INSERT INTO `area_codes` VALUES ('49','09171','Roth Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09172','Georgensgmünd');
INSERT INTO `area_codes` VALUES ('49','09173','Thalmässing');
INSERT INTO `area_codes` VALUES ('49','09174','Hilpoltstein');
INSERT INTO `area_codes` VALUES ('49','09175','Spalt');
INSERT INTO `area_codes` VALUES ('49','09176','Allersberg');
INSERT INTO `area_codes` VALUES ('49','09177','Heideck');
INSERT INTO `area_codes` VALUES ('49','09178','Abenberg Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09179','Freystadt');
INSERT INTO `area_codes` VALUES ('49','09180','Pyrbaum');
INSERT INTO `area_codes` VALUES ('49','09181','Neumarkt i d Opf');
INSERT INTO `area_codes` VALUES ('49','09182','Velburg');
INSERT INTO `area_codes` VALUES ('49','09183','Burgthann');
INSERT INTO `area_codes` VALUES ('49','09184','Deining Oberpf');
INSERT INTO `area_codes` VALUES ('49','09185','Mühlhausen Oberpf');
INSERT INTO `area_codes` VALUES ('49','09186','Lauterhofen Oberpf');
INSERT INTO `area_codes` VALUES ('49','09187','Altdorf b Nürnberg');
INSERT INTO `area_codes` VALUES ('49','09188','Postbauer-Heng');
INSERT INTO `area_codes` VALUES ('49','09189','Berg b Neumarkt i d Opf');
INSERT INTO `area_codes` VALUES ('49','09190','Heroldsbach');
INSERT INTO `area_codes` VALUES ('49','09191','Forchheim Oberfr');
INSERT INTO `area_codes` VALUES ('49','09192','Gräfenberg');
INSERT INTO `area_codes` VALUES ('49','09193','Höchstadt a d Aisch');
INSERT INTO `area_codes` VALUES ('49','09194','Ebermannstadt');
INSERT INTO `area_codes` VALUES ('49','09195','Adelsdorf Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09196','Wiesenttal');
INSERT INTO `area_codes` VALUES ('49','09197','Egloffstein');
INSERT INTO `area_codes` VALUES ('49','09198','Heiligenstadt i Ofr');
INSERT INTO `area_codes` VALUES ('49','09199','Kunreuth');
INSERT INTO `area_codes` VALUES ('49','09201','Gesees');
INSERT INTO `area_codes` VALUES ('49','09202','Waischenfeld');
INSERT INTO `area_codes` VALUES ('49','09203','Neudrossenfeld');
INSERT INTO `area_codes` VALUES ('49','09204','Plankenfels');
INSERT INTO `area_codes` VALUES ('49','09205','Vorbach');
INSERT INTO `area_codes` VALUES ('49','09206','Mistelgau-Obernsees');
INSERT INTO `area_codes` VALUES ('49','09207','Königsfeld Oberfr');
INSERT INTO `area_codes` VALUES ('49','09208','Bindlach');
INSERT INTO `area_codes` VALUES ('49','09209','Emtmannsberg');
INSERT INTO `area_codes` VALUES ('49','0921','Bayreuth');
INSERT INTO `area_codes` VALUES ('49','09220','Kasendorf-Azendorf');
INSERT INTO `area_codes` VALUES ('49','09221','Kulmbach');
INSERT INTO `area_codes` VALUES ('49','09222','Presseck');
INSERT INTO `area_codes` VALUES ('49','09223','Rugendorf');
INSERT INTO `area_codes` VALUES ('49','09225','Stadtsteinach');
INSERT INTO `area_codes` VALUES ('49','09227','Neuenmarkt');
INSERT INTO `area_codes` VALUES ('49','09228','Thurnau');
INSERT INTO `area_codes` VALUES ('49','09229','Mainleus');
INSERT INTO `area_codes` VALUES ('49','09231','Marktredwitz');
INSERT INTO `area_codes` VALUES ('49','09232','Wunsiedel');
INSERT INTO `area_codes` VALUES ('49','09233','Arzberg Oberfr');
INSERT INTO `area_codes` VALUES ('49','09234','Neusorg');
INSERT INTO `area_codes` VALUES ('49','09235','Thierstein');
INSERT INTO `area_codes` VALUES ('49','09236','Nagel');
INSERT INTO `area_codes` VALUES ('49','09238','Röslau');
INSERT INTO `area_codes` VALUES ('49','09241','Pegnitz');
INSERT INTO `area_codes` VALUES ('49','09242','Gößweinstein');
INSERT INTO `area_codes` VALUES ('49','09243','Pottenstein');
INSERT INTO `area_codes` VALUES ('49','09244','Betzenstein');
INSERT INTO `area_codes` VALUES ('49','09245','Obertrubach');
INSERT INTO `area_codes` VALUES ('49','09246','Pegnitz-Trockau');
INSERT INTO `area_codes` VALUES ('49','09251','Münchberg');
INSERT INTO `area_codes` VALUES ('49','09252','Helmbrechts');
INSERT INTO `area_codes` VALUES ('49','09253','Weissenstadt');
INSERT INTO `area_codes` VALUES ('49','09254','Gefrees');
INSERT INTO `area_codes` VALUES ('49','09255','Marktleugast');
INSERT INTO `area_codes` VALUES ('49','09256','Stammbach');
INSERT INTO `area_codes` VALUES ('49','09257','Zell Oberfr');
INSERT INTO `area_codes` VALUES ('49','09260','Wilhelmsthal Oberfr');
INSERT INTO `area_codes` VALUES ('49','09261','Kronach');
INSERT INTO `area_codes` VALUES ('49','09262','Wallenfels');
INSERT INTO `area_codes` VALUES ('49','09263','Ludwigsstadt');
INSERT INTO `area_codes` VALUES ('49','09264','Küps');
INSERT INTO `area_codes` VALUES ('49','09265','Pressig');
INSERT INTO `area_codes` VALUES ('49','09266','Mitwitz');
INSERT INTO `area_codes` VALUES ('49','09267','Nordhalben');
INSERT INTO `area_codes` VALUES ('49','09268','Teuschnitz');
INSERT INTO `area_codes` VALUES ('49','09269','Tettau Kr Kronach');
INSERT INTO `area_codes` VALUES ('49','09270','Creussen');
INSERT INTO `area_codes` VALUES ('49','09271','Thurnau-Alladorf');
INSERT INTO `area_codes` VALUES ('49','09272','Fichtelberg');
INSERT INTO `area_codes` VALUES ('49','09273','Bad Berneck i Fichtelgebirge');
INSERT INTO `area_codes` VALUES ('49','09274','Hollfeld');
INSERT INTO `area_codes` VALUES ('49','09275','Speichersdorf');
INSERT INTO `area_codes` VALUES ('49','09276','Bischofsgrün');
INSERT INTO `area_codes` VALUES ('49','09277','Warmensteinach');
INSERT INTO `area_codes` VALUES ('49','09278','Weidenberg');
INSERT INTO `area_codes` VALUES ('49','09279','Mistelgau');
INSERT INTO `area_codes` VALUES ('49','09280','Selbitz Oberfr');
INSERT INTO `area_codes` VALUES ('49','09281','Hof Saale');
INSERT INTO `area_codes` VALUES ('49','09282','Naila');
INSERT INTO `area_codes` VALUES ('49','09283','Rehau');
INSERT INTO `area_codes` VALUES ('49','09284','Schwarzenbach a d Saale');
INSERT INTO `area_codes` VALUES ('49','09285','Kirchenlamitz');
INSERT INTO `area_codes` VALUES ('49','09286','Oberkotzau');
INSERT INTO `area_codes` VALUES ('49','09287','Selb');
INSERT INTO `area_codes` VALUES ('49','09288','Bad Steben');
INSERT INTO `area_codes` VALUES ('49','09289','Schwarzenbach a Wald');
INSERT INTO `area_codes` VALUES ('49','09292','Konradsreuth');
INSERT INTO `area_codes` VALUES ('49','09293','Berg Oberfr');
INSERT INTO `area_codes` VALUES ('49','09294','Regnitzlosau');
INSERT INTO `area_codes` VALUES ('49','09295','Töpen');
INSERT INTO `area_codes` VALUES ('49','09302','Rottendorf Unterfr');
INSERT INTO `area_codes` VALUES ('49','09303','Eibelstadt');
INSERT INTO `area_codes` VALUES ('49','09305','Estenfeld');
INSERT INTO `area_codes` VALUES ('49','09306','Kist');
INSERT INTO `area_codes` VALUES ('49','09307','Altertheim');
INSERT INTO `area_codes` VALUES ('49','0931','Würzburg');
INSERT INTO `area_codes` VALUES ('49','09321','Kitzingen');
INSERT INTO `area_codes` VALUES ('49','09323','Iphofen');
INSERT INTO `area_codes` VALUES ('49','09324','Dettelbach');
INSERT INTO `area_codes` VALUES ('49','09325','Kleinlangheim');
INSERT INTO `area_codes` VALUES ('49','09326','Markt Einersheim');
INSERT INTO `area_codes` VALUES ('49','09331','Ochsenfurt');
INSERT INTO `area_codes` VALUES ('49','09332','Marktbreit');
INSERT INTO `area_codes` VALUES ('49','09333','Sommerhausen');
INSERT INTO `area_codes` VALUES ('49','09334','Giebelstadt');
INSERT INTO `area_codes` VALUES ('49','09335','Aub Kr Würzburg');
INSERT INTO `area_codes` VALUES ('49','09336','Bütthard');
INSERT INTO `area_codes` VALUES ('49','09337','Gaukönigshofen');
INSERT INTO `area_codes` VALUES ('49','09338','Röttingen Unterfr');
INSERT INTO `area_codes` VALUES ('49','09339','Ippesheim');
INSERT INTO `area_codes` VALUES ('49','09340','Königheim-Brehmen');
INSERT INTO `area_codes` VALUES ('49','09341','Tauberbischofsheim');
INSERT INTO `area_codes` VALUES ('49','09342','Wertheim');
INSERT INTO `area_codes` VALUES ('49','09343','Lauda-Königshofen');
INSERT INTO `area_codes` VALUES ('49','09344','Gerchsheim');
INSERT INTO `area_codes` VALUES ('49','09345','Külsheim Baden');
INSERT INTO `area_codes` VALUES ('49','09346','Grünsfeld');
INSERT INTO `area_codes` VALUES ('49','09347','Wittighausen');
INSERT INTO `area_codes` VALUES ('49','09348','Werbach-Gamburg');
INSERT INTO `area_codes` VALUES ('49','09349','Werbach-Wenkheim');
INSERT INTO `area_codes` VALUES ('49','09350','Eussenheim-Hundsbach');
INSERT INTO `area_codes` VALUES ('49','09351','Gemünden a Main');
INSERT INTO `area_codes` VALUES ('49','09352','Lohr a Main');
INSERT INTO `area_codes` VALUES ('49','09353','Karlstadt');
INSERT INTO `area_codes` VALUES ('49','09354','Rieneck');
INSERT INTO `area_codes` VALUES ('49','09355','Frammersbach');
INSERT INTO `area_codes` VALUES ('49','09356','Burgsinn');
INSERT INTO `area_codes` VALUES ('49','09357','Gräfendorf Bay');
INSERT INTO `area_codes` VALUES ('49','09358','Gössenheim');
INSERT INTO `area_codes` VALUES ('49','09359','Karlstadt-Wiesenfeld');
INSERT INTO `area_codes` VALUES ('49','09360','Thüngen');
INSERT INTO `area_codes` VALUES ('49','09363','Arnstein Unterfr');
INSERT INTO `area_codes` VALUES ('49','09364','Zellingen');
INSERT INTO `area_codes` VALUES ('49','09365','Rimpar');
INSERT INTO `area_codes` VALUES ('49','09366','Geroldshausen Unterfr');
INSERT INTO `area_codes` VALUES ('49','09367','Unterpleichfeld');
INSERT INTO `area_codes` VALUES ('49','09369','Uettingen');
INSERT INTO `area_codes` VALUES ('49','09371','Miltenberg');
INSERT INTO `area_codes` VALUES ('49','09372','Klingenberg a Main');
INSERT INTO `area_codes` VALUES ('49','09373','Amorbach');
INSERT INTO `area_codes` VALUES ('49','09374','Eschau');
INSERT INTO `area_codes` VALUES ('49','09375','Freudenberg Baden');
INSERT INTO `area_codes` VALUES ('49','09376','Collenberg');
INSERT INTO `area_codes` VALUES ('49','09377','Freudenberg-Boxtal');
INSERT INTO `area_codes` VALUES ('49','09378','Eichenbühl-Riedern');
INSERT INTO `area_codes` VALUES ('49','09381','Volkach');
INSERT INTO `area_codes` VALUES ('49','09382','Gerolzhofen');
INSERT INTO `area_codes` VALUES ('49','09383','Wiesentheid');
INSERT INTO `area_codes` VALUES ('49','09384','Schwanfeld');
INSERT INTO `area_codes` VALUES ('49','09385','Kolitzheim');
INSERT INTO `area_codes` VALUES ('49','09386','Prosselsheim');
INSERT INTO `area_codes` VALUES ('49','09391','Marktheidenfeld');
INSERT INTO `area_codes` VALUES ('49','09392','Faulbach Unterfr');
INSERT INTO `area_codes` VALUES ('49','09393','Rothenfels Unterfr');
INSERT INTO `area_codes` VALUES ('49','09394','Esselbach');
INSERT INTO `area_codes` VALUES ('49','09395','Triefenstein');
INSERT INTO `area_codes` VALUES ('49','09396','Urspringen b Lohr');
INSERT INTO `area_codes` VALUES ('49','09397','Wertheim-Dertingen');
INSERT INTO `area_codes` VALUES ('49','09398','Birkenfeld b Würzburg');
INSERT INTO `area_codes` VALUES ('49','09401','Neutraubling');
INSERT INTO `area_codes` VALUES ('49','09402','Regenstauf');
INSERT INTO `area_codes` VALUES ('49','09403','Donaustauf');
INSERT INTO `area_codes` VALUES ('49','09404','Nittendorf');
INSERT INTO `area_codes` VALUES ('49','09405','Bad Abbach');
INSERT INTO `area_codes` VALUES ('49','09406','Mintraching');
INSERT INTO `area_codes` VALUES ('49','09407','Wenzenbach');
INSERT INTO `area_codes` VALUES ('49','09408','Altenthann');
INSERT INTO `area_codes` VALUES ('49','09409','Pielenhofen');
INSERT INTO `area_codes` VALUES ('49','0941','Regensburg');
INSERT INTO `area_codes` VALUES ('49','09420','Feldkirchen Niederbay');
INSERT INTO `area_codes` VALUES ('49','09421','Straubing');
INSERT INTO `area_codes` VALUES ('49','09422','Bogen Niederbay');
INSERT INTO `area_codes` VALUES ('49','09423','Geiselhöring');
INSERT INTO `area_codes` VALUES ('49','09424','Strasskirchen');
INSERT INTO `area_codes` VALUES ('49','09426','Oberschneiding');
INSERT INTO `area_codes` VALUES ('49','09427','Leiblfing');
INSERT INTO `area_codes` VALUES ('49','09428','Kirchroth');
INSERT INTO `area_codes` VALUES ('49','09429','Rain Niederbay');
INSERT INTO `area_codes` VALUES ('49','09431','Schwandorf');
INSERT INTO `area_codes` VALUES ('49','09433','Nabburg');
INSERT INTO `area_codes` VALUES ('49','09434','Bodenwöhr');
INSERT INTO `area_codes` VALUES ('49','09435','Schwarzenfeld');
INSERT INTO `area_codes` VALUES ('49','09436','Nittenau');
INSERT INTO `area_codes` VALUES ('49','09438','Fensterbach');
INSERT INTO `area_codes` VALUES ('49','09439','Neunburg-Kemnath');
INSERT INTO `area_codes` VALUES ('49','09441','Kelheim');
INSERT INTO `area_codes` VALUES ('49','09442','Riedenburg');
INSERT INTO `area_codes` VALUES ('49','09443','Abensberg');
INSERT INTO `area_codes` VALUES ('49','09444','Siegenburg');
INSERT INTO `area_codes` VALUES ('49','09445','Neustadt a d Donau');
INSERT INTO `area_codes` VALUES ('49','09446','Altmannstein');
INSERT INTO `area_codes` VALUES ('49','09447','Essing');
INSERT INTO `area_codes` VALUES ('49','09448','Hausen Niederbay');
INSERT INTO `area_codes` VALUES ('49','09451','Schierling');
INSERT INTO `area_codes` VALUES ('49','09452','Langquaid');
INSERT INTO `area_codes` VALUES ('49','09453','Thalmassing');
INSERT INTO `area_codes` VALUES ('49','09454','Aufhausen Oberpf');
INSERT INTO `area_codes` VALUES ('49','09461','Roding');
INSERT INTO `area_codes` VALUES ('49','09462','Falkenstein Oberpf');
INSERT INTO `area_codes` VALUES ('49','09463','Wald Oberpf');
INSERT INTO `area_codes` VALUES ('49','09464','Walderbach');
INSERT INTO `area_codes` VALUES ('49','09465','Neukirchen-Balbini');
INSERT INTO `area_codes` VALUES ('49','09466','Stamsried');
INSERT INTO `area_codes` VALUES ('49','09467','Michelsneukirchen');
INSERT INTO `area_codes` VALUES ('49','09468','Zell Oberpf');
INSERT INTO `area_codes` VALUES ('49','09469','Roding-Neubäu');
INSERT INTO `area_codes` VALUES ('49','09471','Burglengenfeld');
INSERT INTO `area_codes` VALUES ('49','09472','Hohenfels  Oberpf');
INSERT INTO `area_codes` VALUES ('49','09473','Kallmünz');
INSERT INTO `area_codes` VALUES ('49','09474','Schmidmühlen');
INSERT INTO `area_codes` VALUES ('49','09480','Sünching');
INSERT INTO `area_codes` VALUES ('49','09481','Pfatter');
INSERT INTO `area_codes` VALUES ('49','09482','Wörth a d Donau');
INSERT INTO `area_codes` VALUES ('49','09484','Brennberg');
INSERT INTO `area_codes` VALUES ('49','09491','Hemau');
INSERT INTO `area_codes` VALUES ('49','09492','Parsberg');
INSERT INTO `area_codes` VALUES ('49','09493','Beratzhausen');
INSERT INTO `area_codes` VALUES ('49','09495','Breitenbrunn Oberpf');
INSERT INTO `area_codes` VALUES ('49','09497','Seubersdorf i d Opf');
INSERT INTO `area_codes` VALUES ('49','09498','Laaber');
INSERT INTO `area_codes` VALUES ('49','09499','Painten');
INSERT INTO `area_codes` VALUES ('49','09502','Frensdorf');
INSERT INTO `area_codes` VALUES ('49','09503','Oberhaid Oberfr');
INSERT INTO `area_codes` VALUES ('49','09504','Stadelhofen');
INSERT INTO `area_codes` VALUES ('49','09505','Litzendorf');
INSERT INTO `area_codes` VALUES ('49','0951','Bamberg');
INSERT INTO `area_codes` VALUES ('49','09521','Hassfurt');
INSERT INTO `area_codes` VALUES ('49','09522','Eltmann');
INSERT INTO `area_codes` VALUES ('49','09523','Hofheim i Ufr');
INSERT INTO `area_codes` VALUES ('49','09524','Zeil a Main');
INSERT INTO `area_codes` VALUES ('49','09525','Königsberg i Bay');
INSERT INTO `area_codes` VALUES ('49','09526','Riedbach');
INSERT INTO `area_codes` VALUES ('49','09527','Knetzgau');
INSERT INTO `area_codes` VALUES ('49','09528','Donnersdorf');
INSERT INTO `area_codes` VALUES ('49','09529','Oberaurach');
INSERT INTO `area_codes` VALUES ('49','09531','Ebern');
INSERT INTO `area_codes` VALUES ('49','09532','Maroldsweisach');
INSERT INTO `area_codes` VALUES ('49','09533','Untermerzbach');
INSERT INTO `area_codes` VALUES ('49','09534','Burgpreppach');
INSERT INTO `area_codes` VALUES ('49','09535','Pfarrweisach');
INSERT INTO `area_codes` VALUES ('49','09536','Kirchlauter');
INSERT INTO `area_codes` VALUES ('49','09542','Schesslitz');
INSERT INTO `area_codes` VALUES ('49','09543','Hirschaid');
INSERT INTO `area_codes` VALUES ('49','09544','Baunach');
INSERT INTO `area_codes` VALUES ('49','09545','Buttenheim');
INSERT INTO `area_codes` VALUES ('49','09546','Burgebrach');
INSERT INTO `area_codes` VALUES ('49','09547','Zapfendorf');
INSERT INTO `area_codes` VALUES ('49','09548','Mühlhausen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09549','Lisberg');
INSERT INTO `area_codes` VALUES ('49','09551','Burgwindheim');
INSERT INTO `area_codes` VALUES ('49','09552','Burghaslach');
INSERT INTO `area_codes` VALUES ('49','09553','Ebrach Oberfr');
INSERT INTO `area_codes` VALUES ('49','09554','Untersteinbach Unterfr');
INSERT INTO `area_codes` VALUES ('49','09555','Schlüsselfeld-Aschbach');
INSERT INTO `area_codes` VALUES ('49','09556','Geiselwind');
INSERT INTO `area_codes` VALUES ('49','09560','Grub a Forst');
INSERT INTO `area_codes` VALUES ('49','09561','Coburg');
INSERT INTO `area_codes` VALUES ('49','09562','Sonnefeld');
INSERT INTO `area_codes` VALUES ('49','09563','Rödental');
INSERT INTO `area_codes` VALUES ('49','09564','Bad Rodach');
INSERT INTO `area_codes` VALUES ('49','09565','Untersiemau');
INSERT INTO `area_codes` VALUES ('49','09566','Meeder');
INSERT INTO `area_codes` VALUES ('49','09567','Seßlach-Gemünda');
INSERT INTO `area_codes` VALUES ('49','09568','Neustadt b Coburg');
INSERT INTO `area_codes` VALUES ('49','09569','Sesslach');
INSERT INTO `area_codes` VALUES ('49','09571','Lichtenfels Bay');
INSERT INTO `area_codes` VALUES ('49','09572','Burgkunstadt');
INSERT INTO `area_codes` VALUES ('49','09573','Staffelstein Oberfr');
INSERT INTO `area_codes` VALUES ('49','09574','Marktzeuln');
INSERT INTO `area_codes` VALUES ('49','09575','Weismain');
INSERT INTO `area_codes` VALUES ('49','09576','Lichtenfels-Isling');
INSERT INTO `area_codes` VALUES ('49','09602','Neustadt a d Waldnaab');
INSERT INTO `area_codes` VALUES ('49','09603','Floss');
INSERT INTO `area_codes` VALUES ('49','09604','Wernberg-Köblitz');
INSERT INTO `area_codes` VALUES ('49','09605','Weiherhammer');
INSERT INTO `area_codes` VALUES ('49','09606','Pfreimd');
INSERT INTO `area_codes` VALUES ('49','09607','Luhe-Wildenau');
INSERT INTO `area_codes` VALUES ('49','09608','Kohlberg Oberpf');
INSERT INTO `area_codes` VALUES ('49','0961','Weiden i d Opf');
INSERT INTO `area_codes` VALUES ('49','09621','Amberg Oberpf');
INSERT INTO `area_codes` VALUES ('49','09622','Hirschau Oberpf');
INSERT INTO `area_codes` VALUES ('49','09624','Ensdorf Oberpf');
INSERT INTO `area_codes` VALUES ('49','09625','Kastl b Amberg');
INSERT INTO `area_codes` VALUES ('49','09626','Hohenburg');
INSERT INTO `area_codes` VALUES ('49','09627','Freudenberg Oberpf');
INSERT INTO `area_codes` VALUES ('49','09628','Ursensollen');
INSERT INTO `area_codes` VALUES ('49','09631','Tirschenreuth');
INSERT INTO `area_codes` VALUES ('49','09632','Waldsassen');
INSERT INTO `area_codes` VALUES ('49','09633','Mitterteich');
INSERT INTO `area_codes` VALUES ('49','09634','Wiesau');
INSERT INTO `area_codes` VALUES ('49','09635','Bärnau');
INSERT INTO `area_codes` VALUES ('49','09636','Plößberg');
INSERT INTO `area_codes` VALUES ('49','09637','Falkenberg Oberpf');
INSERT INTO `area_codes` VALUES ('49','09638','Neualbenreuth');
INSERT INTO `area_codes` VALUES ('49','09639','Mähring');
INSERT INTO `area_codes` VALUES ('49','09641','Grafenwöhr');
INSERT INTO `area_codes` VALUES ('49','09642','Kemnath Stadt');
INSERT INTO `area_codes` VALUES ('49','09643','Auerbach i d Opf');
INSERT INTO `area_codes` VALUES ('49','09644','Pressath');
INSERT INTO `area_codes` VALUES ('49','09645','Eschenbach i d Opf');
INSERT INTO `area_codes` VALUES ('49','09646','Freihung');
INSERT INTO `area_codes` VALUES ('49','09647','Kirchenthumbach');
INSERT INTO `area_codes` VALUES ('49','09648','Neustadt a Kulm');
INSERT INTO `area_codes` VALUES ('49','09651','Vohenstrauss');
INSERT INTO `area_codes` VALUES ('49','09652','Waidhaus');
INSERT INTO `area_codes` VALUES ('49','09653','Eslarn');
INSERT INTO `area_codes` VALUES ('49','09654','Pleystein');
INSERT INTO `area_codes` VALUES ('49','09655','Tännesberg');
INSERT INTO `area_codes` VALUES ('49','09656','Moosbach b Vohenstrauß');
INSERT INTO `area_codes` VALUES ('49','09657','Waldthurn');
INSERT INTO `area_codes` VALUES ('49','09658','Georgenberg');
INSERT INTO `area_codes` VALUES ('49','09659','Leuchtenberg');
INSERT INTO `area_codes` VALUES ('49','09661','Sulzbach-Rosenberg');
INSERT INTO `area_codes` VALUES ('49','09662','Vilseck');
INSERT INTO `area_codes` VALUES ('49','09663','Neukirchen b Sulzbach-Rosenberg');
INSERT INTO `area_codes` VALUES ('49','09664','Hahnbach');
INSERT INTO `area_codes` VALUES ('49','09665','Königstein Oberpf');
INSERT INTO `area_codes` VALUES ('49','09666','Illschwang');
INSERT INTO `area_codes` VALUES ('49','09671','Oberviechtach');
INSERT INTO `area_codes` VALUES ('49','09672','Neunburg vorm Wald');
INSERT INTO `area_codes` VALUES ('49','09673','Tiefenbach Oberpf');
INSERT INTO `area_codes` VALUES ('49','09674','Schönsee');
INSERT INTO `area_codes` VALUES ('49','09675','Altendorf a Nabburg');
INSERT INTO `area_codes` VALUES ('49','09676','Winklarn');
INSERT INTO `area_codes` VALUES ('49','09677','Oberviechtach-Pullenried');
INSERT INTO `area_codes` VALUES ('49','09681','Windischeschenbach');
INSERT INTO `area_codes` VALUES ('49','09682','Erbendorf');
INSERT INTO `area_codes` VALUES ('49','09683','Friedenfels');
INSERT INTO `area_codes` VALUES ('49','09701','Sandberg Unterfr');
INSERT INTO `area_codes` VALUES ('49','09704','Euerdorf');
INSERT INTO `area_codes` VALUES ('49','09708','Bad Bocklet');
INSERT INTO `area_codes` VALUES ('49','0971','Bad Kissingen');
INSERT INTO `area_codes` VALUES ('49','09720','Üchtelhausen');
INSERT INTO `area_codes` VALUES ('49','09721','Schweinfurt');
INSERT INTO `area_codes` VALUES ('49','09722','Werneck');
INSERT INTO `area_codes` VALUES ('49','09723','Röthlein');
INSERT INTO `area_codes` VALUES ('49','09724','Stadtlauringen');
INSERT INTO `area_codes` VALUES ('49','09725','Poppenhausen Unterfr');
INSERT INTO `area_codes` VALUES ('49','09726','Euerbach');
INSERT INTO `area_codes` VALUES ('49','09727','Schonungen-Marktsteinach');
INSERT INTO `area_codes` VALUES ('49','09728','Wülfershausen Unterfr');
INSERT INTO `area_codes` VALUES ('49','09729','Grettstadt');
INSERT INTO `area_codes` VALUES ('49','09732','Hammelburg');
INSERT INTO `area_codes` VALUES ('49','09733','Münnerstadt');
INSERT INTO `area_codes` VALUES ('49','09734','Burkardroth');
INSERT INTO `area_codes` VALUES ('49','09735','Massbach');
INSERT INTO `area_codes` VALUES ('49','09736','Oberthulba');
INSERT INTO `area_codes` VALUES ('49','09737','Wartmannsroth');
INSERT INTO `area_codes` VALUES ('49','09738','Rottershausen');
INSERT INTO `area_codes` VALUES ('49','09741','Bad Brückenau');
INSERT INTO `area_codes` VALUES ('49','09742','Kalbach Rhön');
INSERT INTO `area_codes` VALUES ('49','09744','Zeitlofs-Detter');
INSERT INTO `area_codes` VALUES ('49','09745','Wildflecken');
INSERT INTO `area_codes` VALUES ('49','09746','Zeitlofs');
INSERT INTO `area_codes` VALUES ('49','09747','Geroda Bay');
INSERT INTO `area_codes` VALUES ('49','09748','Motten');
INSERT INTO `area_codes` VALUES ('49','09749','Oberbach Unterfr');
INSERT INTO `area_codes` VALUES ('49','09761','Bad Königshofen i Grabfeld');
INSERT INTO `area_codes` VALUES ('49','09762','Saal a d Saale');
INSERT INTO `area_codes` VALUES ('49','09763','Sulzdorf a d Lederhecke');
INSERT INTO `area_codes` VALUES ('49','09764','Höchheim');
INSERT INTO `area_codes` VALUES ('49','09765','Trappstadt');
INSERT INTO `area_codes` VALUES ('49','09766','Grosswenkheim');
INSERT INTO `area_codes` VALUES ('49','09771','Bad Neustadt a d Saale');
INSERT INTO `area_codes` VALUES ('49','09772','Bischofsheim a d Rhön');
INSERT INTO `area_codes` VALUES ('49','09773','Unsleben');
INSERT INTO `area_codes` VALUES ('49','09774','Oberelsbach');
INSERT INTO `area_codes` VALUES ('49','09775','Schönau a d Brend');
INSERT INTO `area_codes` VALUES ('49','09776','Mellrichstadt');
INSERT INTO `area_codes` VALUES ('49','09777','Ostheim v d Rhön');
INSERT INTO `area_codes` VALUES ('49','09778','Fladungen');
INSERT INTO `area_codes` VALUES ('49','09779','Nordheim v d Rhön');
INSERT INTO `area_codes` VALUES ('49','09802','Ansbach-Katterbach');
INSERT INTO `area_codes` VALUES ('49','09803','Colmberg');
INSERT INTO `area_codes` VALUES ('49','09804','Aurach');
INSERT INTO `area_codes` VALUES ('49','09805','Burgoberbach');
INSERT INTO `area_codes` VALUES ('49','0981','Ansbach');
INSERT INTO `area_codes` VALUES ('49','09820','Lehrberg');
INSERT INTO `area_codes` VALUES ('49','09822','Bechhofen a d Heide');
INSERT INTO `area_codes` VALUES ('49','09823','Leutershausen');
INSERT INTO `area_codes` VALUES ('49','09824','Dietenhofen');
INSERT INTO `area_codes` VALUES ('49','09825','Herrieden');
INSERT INTO `area_codes` VALUES ('49','09826','Weidenbach Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09827','Lichtenau Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09828','Rügland');
INSERT INTO `area_codes` VALUES ('49','09829','Flachslanden');
INSERT INTO `area_codes` VALUES ('49','09831','Gunzenhausen');
INSERT INTO `area_codes` VALUES ('49','09832','Wassertrüdingen');
INSERT INTO `area_codes` VALUES ('49','09833','Heidenheim Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09834','Theilenhofen');
INSERT INTO `area_codes` VALUES ('49','09835','Ehingen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09836','Gunzenhausen-Cronheim');
INSERT INTO `area_codes` VALUES ('49','09837','Haundorf');
INSERT INTO `area_codes` VALUES ('49','09841','Bad Windsheim');
INSERT INTO `area_codes` VALUES ('49','09842','Uffenheim');
INSERT INTO `area_codes` VALUES ('49','09843','Burgbernheim');
INSERT INTO `area_codes` VALUES ('49','09844','Obernzenn');
INSERT INTO `area_codes` VALUES ('49','09845','Oberdachstetten');
INSERT INTO `area_codes` VALUES ('49','09846','Ipsheim');
INSERT INTO `area_codes` VALUES ('49','09847','Ergersheim');
INSERT INTO `area_codes` VALUES ('49','09848','Simmershofen');
INSERT INTO `area_codes` VALUES ('49','09851','Dinkelsbühl');
INSERT INTO `area_codes` VALUES ('49','09852','Feuchtwangen');
INSERT INTO `area_codes` VALUES ('49','09853','Wilburgstetten');
INSERT INTO `area_codes` VALUES ('49','09854','Wittelshofen');
INSERT INTO `area_codes` VALUES ('49','09855','Dentlein a Forst');
INSERT INTO `area_codes` VALUES ('49','09856','Dürrwangen');
INSERT INTO `area_codes` VALUES ('49','09857','Schopfloch Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09861','Rothenburg ob der Tauber');
INSERT INTO `area_codes` VALUES ('49','09865','Adelshofen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09867','Geslau');
INSERT INTO `area_codes` VALUES ('49','09868','Schillingsfürst');
INSERT INTO `area_codes` VALUES ('49','09869','Wettringen Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09871','Windsbach');
INSERT INTO `area_codes` VALUES ('49','09872','Heilsbronn');
INSERT INTO `area_codes` VALUES ('49','09873','Abenberg-Wassermungenau');
INSERT INTO `area_codes` VALUES ('49','09874','Neuendettelsau');
INSERT INTO `area_codes` VALUES ('49','09875','Wolframs-Eschenbach');
INSERT INTO `area_codes` VALUES ('49','09876','Rohr Mittelfr');
INSERT INTO `area_codes` VALUES ('49','09901','Hengersberg Bay');
INSERT INTO `area_codes` VALUES ('49','09903','Schöllnach');
INSERT INTO `area_codes` VALUES ('49','09904','Lalling');
INSERT INTO `area_codes` VALUES ('49','09905','Bernried Niederbay');
INSERT INTO `area_codes` VALUES ('49','09906','Mariaposching');
INSERT INTO `area_codes` VALUES ('49','09907','Zenting');
INSERT INTO `area_codes` VALUES ('49','09908','Schöfweg');
INSERT INTO `area_codes` VALUES ('49','0991','Deggendorf');
INSERT INTO `area_codes` VALUES ('49','09920','Bischofsmais');
INSERT INTO `area_codes` VALUES ('49','09921','Regen');
INSERT INTO `area_codes` VALUES ('49','09922','Zwiesel');
INSERT INTO `area_codes` VALUES ('49','09923','Teisnach');
INSERT INTO `area_codes` VALUES ('49','09924','Bodenmais');
INSERT INTO `area_codes` VALUES ('49','09925','Bayerisch Eisenstein');
INSERT INTO `area_codes` VALUES ('49','09926','Frauenau');
INSERT INTO `area_codes` VALUES ('49','09927','Kirchberg Wald');
INSERT INTO `area_codes` VALUES ('49','09928','Kirchdorf i Wald');
INSERT INTO `area_codes` VALUES ('49','09929','Ruhmannsfelden');
INSERT INTO `area_codes` VALUES ('49','09931','Plattling');
INSERT INTO `area_codes` VALUES ('49','09932','Osterhofen');
INSERT INTO `area_codes` VALUES ('49','09933','Wallersdorf');
INSERT INTO `area_codes` VALUES ('49','09935','Stephansposching');
INSERT INTO `area_codes` VALUES ('49','09936','Wallerfing');
INSERT INTO `area_codes` VALUES ('49','09937','Oberpöring');
INSERT INTO `area_codes` VALUES ('49','09938','Moos Niederbay');
INSERT INTO `area_codes` VALUES ('49','09941','Kötzting');
INSERT INTO `area_codes` VALUES ('49','09942','Viechtach');
INSERT INTO `area_codes` VALUES ('49','09943','Lam Oberpf');
INSERT INTO `area_codes` VALUES ('49','09944','Miltach');
INSERT INTO `area_codes` VALUES ('49','09945','Arnbruck');
INSERT INTO `area_codes` VALUES ('49','09946','Hohenwarth b Kötzing');
INSERT INTO `area_codes` VALUES ('49','09947','Neukirchen b Hl Blut');
INSERT INTO `area_codes` VALUES ('49','09948','Eschlkam');
INSERT INTO `area_codes` VALUES ('49','09951','Landau a d Isar');
INSERT INTO `area_codes` VALUES ('49','09952','Eichendorf');
INSERT INTO `area_codes` VALUES ('49','09953','Pilsting');
INSERT INTO `area_codes` VALUES ('49','09954','SimbachNiederbay');
INSERT INTO `area_codes` VALUES ('49','09955','Mamming');
INSERT INTO `area_codes` VALUES ('49','09956','Eichendorf-Aufhausen');
INSERT INTO `area_codes` VALUES ('49','09961','Mitterfels');
INSERT INTO `area_codes` VALUES ('49','09962','Schwarzach Niederbay');
INSERT INTO `area_codes` VALUES ('49','09963','Konzell');
INSERT INTO `area_codes` VALUES ('49','09964','Stallwang');
INSERT INTO `area_codes` VALUES ('49','09965','Sankt Englmar');
INSERT INTO `area_codes` VALUES ('49','09966','Wiesenfelden');
INSERT INTO `area_codes` VALUES ('49','09971','Cham');
INSERT INTO `area_codes` VALUES ('49','09972','Waldmünchen');
INSERT INTO `area_codes` VALUES ('49','09973','Furth i Wald');
INSERT INTO `area_codes` VALUES ('49','09974','Traitsching');
INSERT INTO `area_codes` VALUES ('49','09975','Waldmünchen-Geigant');
INSERT INTO `area_codes` VALUES ('49','09976','Rötz');
INSERT INTO `area_codes` VALUES ('49','09977','Arnschwang');
INSERT INTO `area_codes` VALUES ('49','09978','Schönthal Oberpf');
/*!40000 ALTER TABLE `area_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ast_cdr`
--

DROP TABLE IF EXISTS `ast_cdr`;
CREATE TABLE `ast_cdr` (
  `calldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `clid` varchar(50) collate latin1_general_ci NOT NULL default '',
  `src` varchar(50) collate latin1_general_ci NOT NULL default '',
  `dst` varchar(50) collate latin1_general_ci NOT NULL default '',
  `dcontext` varchar(50) collate latin1_general_ci NOT NULL default '',
  `channel` varchar(50) collate latin1_general_ci NOT NULL default '',
  `dstchannel` varchar(50) collate latin1_general_ci NOT NULL default '',
  `lastapp` varchar(50) collate latin1_general_ci NOT NULL default '',
  `lastdata` varchar(80) collate latin1_general_ci NOT NULL default '',
  `duration` mediumint(8) unsigned NOT NULL default '0',
  `billsec` mediumint(8) unsigned NOT NULL default '0',
  `disposition` varchar(20) collate latin1_general_ci NOT NULL default '',
  `amaflags` tinyint(3) unsigned NOT NULL default '0',
  `accountcode` varchar(20) collate latin1_general_ci NOT NULL default '',
  `userfield` varchar(255) collate latin1_general_ci NOT NULL default '',
  KEY `calldate` (`calldate`),
  KEY `accountcode` (`accountcode`),
  KEY `src_disposition` (`src`,`disposition`(4)),
  KEY `dst_disposition` (`dst`,`disposition`(4))
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_cdr`
--

LOCK TABLES `ast_cdr` WRITE;
/*!40000 ALTER TABLE `ast_cdr` DISABLE KEYS */;
INSERT INTO `ast_cdr` VALUES ('2007-05-10 12:37:45','\"Homer Simpson\" <2001>','2001','h','default','Local/*800001@default-2de1,2','','NoOp','Finish if-to-internal-users-self-79',1,0,'NO ANSWER',3,'','');
INSERT INTO `ast_cdr` VALUES ('2007-05-10 12:37:45','\"Homer Simpson\" <2001>','2001','h','to-internal-users-self','SIP/2001-0a004f38','Local/*800001@default-2de1,1','NoOp','Finish if-to-internal-users-self-79',4,3,'ANSWERED',3,'','');
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
  KEY `interface` (`interface`(15))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_queue_members`
--

LOCK TABLES `ast_queue_members` WRITE;
/*!40000 ALTER TABLE `ast_queue_members` DISABLE KEYS */;
INSERT INTO `ast_queue_members` VALUES ('5000',1,'SIP/2001',23,0);
INSERT INTO `ast_queue_members` VALUES ('5000',1,'SIP/2002',24,0);
/*!40000 ALTER TABLE `ast_queue_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ast_queues`
--

DROP TABLE IF EXISTS `ast_queues`;
CREATE TABLE `ast_queues` (
  `_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(20) character set ascii NOT NULL default '',
  `_host_id` mediumint(8) unsigned NOT NULL default '1',
  `_title` varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  `musicclass` varchar(50) character set ascii default NULL,
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
  KEY `host_name` (`_host_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_queues`
--

LOCK TABLES `ast_queues` WRITE;
/*!40000 ALTER TABLE `ast_queues` DISABLE KEYS */;
INSERT INTO `ast_queues` VALUES (1,'5000',1,'Support-Schlange','default',NULL,NULL,10,'no','yes',NULL,NULL,60,90,NULL,'yes',5,NULL,5,NULL,'rrmemory','strict','strict',NULL,NULL,NULL,'no',NULL,0,NULL);
/*!40000 ALTER TABLE `ast_queues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ast_sipfriends`
--

DROP TABLE IF EXISTS `ast_sipfriends`;
CREATE TABLE `ast_sipfriends` (
  `_user_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(25) character set ascii NOT NULL default '',
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
  KEY `mailbox` (`mailbox`(20)),
  KEY `context` (`context`(25))
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
INSERT INTO `ast_sipfriends` VALUES (22,'2000','5826899294','friend','dynamic',NULL,'from-internal-users','Bart Simpson <2000>','2000','1','1','__user_id=22;__user_name=2000',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (23,'2001','4813474487','friend','dynamic',NULL,'from-internal-users','Homer Simpson <2001>','2001','1','1','__user_id=23;__user_name=2001',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (24,'2002','6907087521','friend','dynamic',NULL,'from-internal-users','Marge Simpson <2002>','2002','1','1','__user_id=24;__user_name=2002',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (25,'2003','9293349941','friend','dynamic',NULL,'from-internal-users','Lisa Simpson <2003>','2003','1','1','__user_id=25;__user_name=2003',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (28,'950011','7364863263482634','friend','dynamic',NULL,'from-internal-nobody','Namenlos-28 <950011>','','1','1','__user_id=28;__user_name=950011',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (29,'950012','7364863263482634','friend','dynamic',NULL,'from-internal-nobody','Namenlos-29 <950012>','','1','1','__user_id=29;__user_name=950012',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
INSERT INTO `ast_sipfriends` VALUES (30,'950013','3707760381117896','friend','dynamic',NULL,'from-internal-nobody','Namenlos-13 <950013>','','1','1','__user_id=30;__user_name=950013',20,'default',NULL,NULL,NULL,0,NULL,NULL,NULL);
/*!40000 ALTER TABLE `ast_sipfriends` ENABLE KEYS */;
UNLOCK TABLES;

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
  KEY `mailbox_context` (`mailbox`,`context`(20))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `ast_voicemail`
--

LOCK TABLES `ast_voicemail` WRITE;
/*!40000 ALTER TABLE `ast_voicemail` DISABLE KEYS */;
INSERT INTO `ast_voicemail` VALUES (9,22,'2000','default','123','','Bart Simpson','germany','no','no');
INSERT INTO `ast_voicemail` VALUES (10,23,'2001','default','123','','Homer Simpson','germany','no','no');
INSERT INTO `ast_voicemail` VALUES (11,24,'2002','default','123','','Marge Simpson','germany','no','no');
INSERT INTO `ast_voicemail` VALUES (12,25,'2003','default','123','','Lisa Simpson','germany','no','no');
/*!40000 ALTER TABLE `ast_voicemail` ENABLE KEYS */;
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
  UNIQUE KEY `user_regex` (`user_id`,`regexp`)
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
  `active` enum('no','std','var') character set ascii NOT NULL default 'no',
  PRIMARY KEY  (`user_id`,`source`,`case`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `callforwards`
--

LOCK TABLES `callforwards` WRITE;
/*!40000 ALTER TABLE `callforwards` DISABLE KEYS */;
INSERT INTO `callforwards` VALUES (23,'internal','unavail',20,'90001','','std');
INSERT INTO `callforwards` VALUES (24,'internal','always',0,'888','8','no');
INSERT INTO `callforwards` VALUES (24,'internal','busy',0,'888','8','no');
INSERT INTO `callforwards` VALUES (24,'internal','unavail',18,'888','8','no');
INSERT INTO `callforwards` VALUES (24,'internal','offline',0,'888','8','no');
INSERT INTO `callforwards` VALUES (24,'external','always',0,'99999','66','no');
INSERT INTO `callforwards` VALUES (24,'external','busy',0,'99999','66','no');
INSERT INTO `callforwards` VALUES (24,'external','unavail',18,'99999','66','no');
INSERT INTO `callforwards` VALUES (24,'external','offline',0,'99999','66','no');
/*!40000 ALTER TABLE `callforwards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `callwaiting`
--

DROP TABLE IF EXISTS `callwaiting`;
CREATE TABLE `callwaiting` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
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
  PRIMARY KEY  (`user_id`)
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
  KEY `host_ext` (`host_id`,`ext`)
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
  KEY `timestamp` (`timestamp`),
  KEY `user_timestamp` (`user_id`,`timestamp`),
  KEY `user_type_number_timestamp` (`user_id`,`type`,`number`(10),`timestamp`),
  KEY `user_type_timestamp` (`user_id`,`type`,`timestamp`)
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
  `title` varchar(50) collate utf8_unicode_ci NOT NULL,
  `type` varchar(20) character set ascii NOT NULL default 'balance',
  PRIMARY KEY  (`id`),
  KEY `title` (`title`(8))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `gate_grps`
--

LOCK TABLES `gate_grps` WRITE;
/*!40000 ALTER TABLE `gate_grps` DISABLE KEYS */;
INSERT INTO `gate_grps` VALUES (5,'SIP-ISDN-GWs intern','balance');
INSERT INTO `gate_grps` VALUES (6,'ISDN (PRI)','balance');
INSERT INTO `gate_grps` VALUES (7,'GSM-GW T-Mobile','balance');
INSERT INTO `gate_grps` VALUES (8,'GSM-GW Vodafone','balance');
INSERT INTO `gate_grps` VALUES (9,'SIP-GW (sipgate.de)','balance');
INSERT INTO `gate_grps` VALUES (10,'SIP-GW (dus.net)','balance');
/*!40000 ALTER TABLE `gate_grps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gates`
--

DROP TABLE IF EXISTS `gates`;
CREATE TABLE `gates` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `grp_id` smallint(5) unsigned NOT NULL,
  `type` varchar(10) character set ascii NOT NULL default 'sip',
  `name` varchar(25) character set ascii NOT NULL,
  `title` varchar(50) collate utf8_unicode_ci NOT NULL,
  `allow_out` tinyint(1) unsigned NOT NULL default '1',
  `allow_in` tinyint(1) unsigned NOT NULL,
  `dialstr` varchar(50) character set ascii NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`(10)),
  KEY `grp_title` (`grp_id`,`title`(10)),
  KEY `grp_allow_out` (`grp_id`,`allow_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `gates`
--

LOCK TABLES `gates` WRITE;
/*!40000 ALTER TABLE `gates` DISABLE KEYS */;
INSERT INTO `gates` VALUES (5,6,'zap','gw_5_zaptel_span_1','Zaptel Span 1',1,0,'Zap/r1/{number}');
INSERT INTO `gates` VALUES (6,6,'zap','gw_6_zaptel_span_2','Zaptel Span 2',1,0,'Zap/r2/{number}');
INSERT INTO `gates` VALUES (7,5,'sip','gw_7_sip_isdn_intern_a','SIP-ISDN intern A',1,0,'SIP/{number}@{peer}');
INSERT INTO `gates` VALUES (8,5,'sip','gw_8_sip_isdn_intern_b','SIP-ISDN intern B',1,0,'SIP/{number}@{peer}');
INSERT INTO `gates` VALUES (9,8,'sip','gw_9_sip_gsm_vodafone','SIP-GSM Vodafone',1,1,'SIP/{number}@{peer}');
/*!40000 ALTER TABLE `gates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hosts`
--

DROP TABLE IF EXISTS `hosts`;
CREATE TABLE `hosts` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `host` varchar(50) character set ascii NOT NULL default '',
  `comment` varchar(50) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `hosts`
--

LOCK TABLES `hosts` WRITE;
/*!40000 ALTER TABLE `hosts` DISABLE KEYS */;
INSERT INTO `hosts` VALUES (1,'192.168.1.130','ast 1');
INSERT INTO `hosts` VALUES (2,'192.168.1.140','ast 2');
/*!40000 ALTER TABLE `hosts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instant_messaging`
--

DROP TABLE IF EXISTS `instant_messaging`;
CREATE TABLE `instant_messaging` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `type` varchar(25) character set ascii NOT NULL default '',
  `contact` varchar(80) character set ascii NOT NULL default '',
  PRIMARY KEY  (`user_id`,`type`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `instant_messaging`
--

LOCK TABLES `instant_messaging` WRITE;
/*!40000 ALTER TABLE `instant_messaging` DISABLE KEYS */;
INSERT INTO `instant_messaging` VALUES (1,'jabber','homer@jabber.simpson');
/*!40000 ALTER TABLE `instant_messaging` ENABLE KEYS */;
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
  KEY `uid_number` (`user_id`,`number`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `pb_prv`
--

LOCK TABLES `pb_prv` WRITE;
/*!40000 ALTER TABLE `pb_prv` DISABLE KEYS */;
INSERT INTO `pb_prv` VALUES (32,23,'','Testuser1','1234');
INSERT INTO `pb_prv` VALUES (33,23,'','Testuser2','12345');
INSERT INTO `pb_prv` VALUES (34,23,'','Testuser3-1','654321');
INSERT INTO `pb_prv` VALUES (35,23,'','Testuser4','123456');
INSERT INTO `pb_prv` VALUES (36,23,'','Testuser5','123456');
INSERT INTO `pb_prv` VALUES (37,23,'','Testuser6','123456');
INSERT INTO `pb_prv` VALUES (41,23,'','Testuser10','123456');
INSERT INTO `pb_prv` VALUES (42,23,'','Testuser11','123456');
INSERT INTO `pb_prv` VALUES (43,23,'','Testuser12','123456');
INSERT INTO `pb_prv` VALUES (44,23,'','Testuser13','123456');
INSERT INTO `pb_prv` VALUES (45,23,'','Testuser14','123456');
INSERT INTO `pb_prv` VALUES (46,23,'','Testuser15','123456');
INSERT INTO `pb_prv` VALUES (47,23,'','Testuser16-1','123456');
INSERT INTO `pb_prv` VALUES (48,23,'','Testuser17','123456');
INSERT INTO `pb_prv` VALUES (49,23,'','Testuser18','123456');
INSERT INTO `pb_prv` VALUES (50,23,'','Testuser19','123456');
INSERT INTO `pb_prv` VALUES (51,23,'','Testuser20','123456');
INSERT INTO `pb_prv` VALUES (52,23,'','Testuser99','1234');
INSERT INTO `pb_prv` VALUES (53,23,'HANS','TEST','1234');
INSERT INTO `pb_prv` VALUES (54,23,'','abc','123');
INSERT INTO `pb_prv` VALUES (56,23,'','abc3','123');
INSERT INTO `pb_prv` VALUES (57,23,'PETER','TEST','555');
/*!40000 ALTER TABLE `pb_prv` ENABLE KEYS */;
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
  PRIMARY KEY  (`id`),
  KEY `mac_addr` (`mac_addr`),
  KEY `user_id` (`user_id`),
  KEY `added` (`added`),
  KEY `type` (`type`),
  KEY `nobody_index` (`nobody_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `phones`
--

LOCK TABLES `phones` WRITE;
/*!40000 ALTER TABLE `phones` DISABLE KEYS */;
INSERT INTO `phones` VALUES (1,'snom360','000413233C9F',NULL,0,0);
INSERT INTO `phones` VALUES (2,'snom360','000413231C76',24,0,0);
INSERT INTO `phones` VALUES (3,'snom360','000413233483',11,0,0);
INSERT INTO `phones` VALUES (7,'snom360','001122334455',28,0,1174112992);
INSERT INTO `phones` VALUES (8,'snom360','0004132308A4',25,0,1174119746);
INSERT INTO `phones` VALUES (9,'snom360','000413000000',30,13,1177010534);
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
INSERT INTO `pickupgroups` VALUES (1,'Homer und Marge');
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
  KEY `user_id` (`user_id`)
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
  PRIMARY KEY  (`queue_id`,`source`,`case`)
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
  `ast_call_id` varchar(20) character set ascii default NULL,
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
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `queue_log`
--

LOCK TABLES `queue_log` WRITE;
/*!40000 ALTER TABLE `queue_log` DISABLE KEYS */;
INSERT INTO `queue_log` VALUES (NULL,1172387005,'QUEUESTART',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `queue_log` VALUES (1,1172387014,'_ENTER',NULL,'1172387014.0',NULL,'2000',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `queue_log` VALUES (1,1172387017,'_CONNECT',NULL,'1172387014.0',23,NULL,NULL,NULL,3,NULL,NULL,NULL);
INSERT INTO `queue_log` VALUES (1,1172387036,'_COMPLETE','AGENT','1172387014.0',23,NULL,NULL,NULL,3,NULL,19,NULL);
INSERT INTO `queue_log` VALUES (1,1172387045,'_ENTER',NULL,'1172387045.2',NULL,'2000',NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `queue_log` VALUES (1,1172387048,'_CONNECT',NULL,'1172387045.2',24,NULL,NULL,NULL,3,NULL,NULL,NULL);
INSERT INTO `queue_log` VALUES (1,1172387066,'_COMPLETE','TRANSFER','1172387045.2',24,NULL,NULL,NULL,3,NULL,18,'2001@default');
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
  PRIMARY KEY  (`user_id`,`src`)
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
  `gw_grp_id_1` smallint(5) unsigned NOT NULL default '0',
  `gw_grp_id_2` smallint(5) unsigned NOT NULL default '0',
  `gw_grp_id_3` smallint(5) unsigned NOT NULL default '0',
  `descr` varchar(150) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ord` (`ord`),
  KEY `active_mo` (`active`,`d_mo`,`ord`),
  KEY `active_tu` (`active`,`d_tu`,`ord`),
  KEY `active_we` (`active`,`d_we`,`ord`),
  KEY `active_th` (`active`,`d_th`,`ord`),
  KEY `active_fr` (`active`,`d_fr`,`ord`),
  KEY `active_sa` (`active`,`d_sa`,`ord`),
  KEY `active_su` (`active`,`d_su`,`ord`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `routes`
--

LOCK TABLES `routes` WRITE;
/*!40000 ALTER TABLE `routes` DISABLE KEYS */;
INSERT INTO `routes` VALUES (5,1,3,'^11[0-7]$',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,7,9,'Notrufnummern etc.');
INSERT INTO `routes` VALUES (6,1,4,'^19222$',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,7,9,'Notruf Rettungsdienst');
INSERT INTO `routes` VALUES (7,1,14,'^0900',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'Mehrwertnummern');
INSERT INTO `routes` VALUES (8,1,8,'^118',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'Auskünfte (u.U. teuer, können vermitteln!)');
INSERT INTO `routes` VALUES (9,1,10,'^09009',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Mehrwertnummern (Dialer)');
INSERT INTO `routes` VALUES (10,1,12,'^09005',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Mehrwertnummern (\"Erwachsenenunterhaltung\")');
INSERT INTO `routes` VALUES (11,1,16,'^0902',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Televoting (14 ct/Anruf)');
INSERT INTO `routes` VALUES (12,1,18,'^019[1-4]',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Internet-Zugänge');
INSERT INTO `routes` VALUES (13,1,20,'^070[01]',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'private Vanity-Nummern');
INSERT INTO `routes` VALUES (14,1,22,'^080[01]',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'Mehrwertnummern (kostenlos)');
INSERT INTO `routes` VALUES (15,1,24,'^01805',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Mehrwertnummern (Hotlines/\"Erwachsenenunterhaltung)');
INSERT INTO `routes` VALUES (16,1,26,'^01802001033',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Handvermittlung ins Ausland (teuer)');
INSERT INTO `routes` VALUES (17,1,28,'^0180',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'Mehrwertnummern');
INSERT INTO `routes` VALUES (18,1,30,'^0137',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Televoting (25-100 ct/Anruf)');
INSERT INTO `routes` VALUES (19,1,32,'^012[0-9]',1,1,1,1,1,1,1,'00:00:00','24:00:00',0,0,0,'Innovative Dienste (teuer)');
INSERT INTO `routes` VALUES (20,1,34,'^032',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,0,0,'ortsunabhängig, unklare Tarifierung, GSM vermeiden');
INSERT INTO `routes` VALUES (21,1,36,'^0151',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'T-Mobile D1');
INSERT INTO `routes` VALUES (22,1,38,'^016[01489]',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'T-Mobile D1');
INSERT INTO `routes` VALUES (23,1,40,'^017[015]',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'T-Mobile D1');
INSERT INTO `routes` VALUES (24,1,42,'^0152',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'Vodafone D2');
INSERT INTO `routes` VALUES (25,1,44,'^0162',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'Vodafone D2');
INSERT INTO `routes` VALUES (26,1,46,'^017[234]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'Vodafone D2');
INSERT INTO `routes` VALUES (27,1,48,'^015[57]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'E-Plus');
INSERT INTO `routes` VALUES (28,1,50,'^0163',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'E-Plus');
INSERT INTO `routes` VALUES (29,1,52,'^017[78]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'E-Plus');
INSERT INTO `routes` VALUES (30,1,54,'^0156',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'MobilCom');
INSERT INTO `routes` VALUES (31,1,56,'^0159',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'O2');
INSERT INTO `routes` VALUES (32,1,58,'^017[69]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'O2');
INSERT INTO `routes` VALUES (33,1,60,'^0150',1,1,1,1,1,1,1,'00:00:00','24:00:00',7,8,6,'Group3G');
INSERT INTO `routes` VALUES (34,1,62,'^01[5-7]',1,1,1,1,1,1,1,'00:00:00','24:00:00',8,7,6,'andere Handy-Gespräche');
INSERT INTO `routes` VALUES (35,1,64,'^0[2-9][0-9]',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,10,0,'Ortsnetze');
INSERT INTO `routes` VALUES (36,1,66,'^00',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,9,0,'international');
INSERT INTO `routes` VALUES (37,1,68,'^',1,1,1,1,1,1,1,'00:00:00','24:00:00',6,9,0,'alles andere');
/*!40000 ALTER TABLE `routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `softkeys`
--

DROP TABLE IF EXISTS `softkeys`;
CREATE TABLE `softkeys` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `phone` varchar(20) NOT NULL default '',
  `key` varchar(10) NOT NULL default '',
  `number` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`phone`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

--
-- Dumping data for table `softkeys`
--

LOCK TABLES `softkeys` WRITE;
/*!40000 ALTER TABLE `softkeys` DISABLE KEYS */;
INSERT INTO `softkeys` VALUES (23,'snom','f1','');
INSERT INTO `softkeys` VALUES (23,'snom','f10','');
INSERT INTO `softkeys` VALUES (23,'snom','f11','2211');
INSERT INTO `softkeys` VALUES (23,'snom','f3','44');
INSERT INTO `softkeys` VALUES (23,'snom','f8','99');
/*!40000 ALTER TABLE `softkeys` ENABLE KEYS */;
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
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`),
  KEY `lastname_firstname` (`lastname`(15),`firstname`(15)),
  KEY `firstname_lastname` (`firstname`(15),`lastname`(15)),
  KEY `nobody_index` (`nobody_index`),
  KEY `host_id` (`host_id`),
  KEY `current_ip` (`current_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (5,'nobody-00001','','','','','',1,1,'192.168.1.249','');
INSERT INTO `users` VALUES (6,'nobody-00002','','','','','',2,1,NULL,'');
INSERT INTO `users` VALUES (7,'nobody-00003','','','','','',3,1,'192.168.1.202','');
INSERT INTO `users` VALUES (8,'nobody-00004','','','','','',4,1,NULL,'');
INSERT INTO `users` VALUES (9,'nobody-00005','','','','','',5,1,'192.168.1.202','');
INSERT INTO `users` VALUES (10,'nobody-00006','','','','','',6,1,'192.168.1.247','');
INSERT INTO `users` VALUES (11,'nobody-00007','','','','','',7,1,NULL,'');
INSERT INTO `users` VALUES (12,'nobody-00008','','','','','',8,1,NULL,'');
INSERT INTO `users` VALUES (13,'nobody-00009','','','','','',9,1,'192.168.1.202','');
INSERT INTO `users` VALUES (14,'nobody-00010','','','','','',10,1,'192.168.1.201','');
INSERT INTO `users` VALUES (22,'47110001','123','Bart','Simpson','','',NULL,1,NULL,'');
INSERT INTO `users` VALUES (23,'47110002','123','Homer','Simpson','','',NULL,2,'192.168.1.247','');
INSERT INTO `users` VALUES (24,'47110003','123','Marge','Simpson','','',NULL,1,'192.168.1.249','');
INSERT INTO `users` VALUES (25,'47110004','123','Lisa','Simpson','','',NULL,1,'192.168.1.247','');
INSERT INTO `users` VALUES (28,'nobody-00011','','','','','',11,1,NULL,'');
INSERT INTO `users` VALUES (29,'nobody-00012','','','','','',12,1,'192.168.1.201','');
INSERT INTO `users` VALUES (30,'nobody-00013','','','','','',13,1,'192.168.1.109','');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_external_numbers`
--

DROP TABLE IF EXISTS `users_external_numbers`;
CREATE TABLE `users_external_numbers` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `number` varchar(25) character set latin1 collate latin1_general_ci NOT NULL default '',
  PRIMARY KEY  (`user_id`,`number`),
  KEY `number` (`number`)
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
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `vm`
--

LOCK TABLES `vm` WRITE;
/*!40000 ALTER TABLE `vm` DISABLE KEYS */;
INSERT INTO `vm` VALUES (5,0,0);
INSERT INTO `vm` VALUES (6,0,0);
INSERT INTO `vm` VALUES (22,0,0);
INSERT INTO `vm` VALUES (23,0,1);
INSERT INTO `vm` VALUES (24,0,0);
INSERT INTO `vm` VALUES (25,0,0);
/*!40000 ALTER TABLE `vm` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-09-30  16:07:12
