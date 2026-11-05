-- ------------------------------------------------------------------
-- Voy por Colombia · base de datos MySQL/MariaDB lista para importar
--
-- Solo hace falta si quieres usar MySQL en lugar de la base SQLite incluida
-- (por ejemplo en un hosting). En phpMyAdmin: pestaña «Importar» → este archivo.
-- Luego crea .env con DB_CONNECTION=mysql y los datos de conexión.
--
-- Incluye departamentos, municipios, categorías y CONTENIDO DE DEMOSTRACIÓN
-- (marcado como demo; se elimina desde Panel › Configuración). No incluye
-- cuentas ni contraseñas. Las imágenes están en public/uploads/demo y marca.
-- ------------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `voy_por_colombia` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `voy_por_colombia`;

-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: voy_por_colombia
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

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
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(30) NOT NULL,
  `entity_type` varchar(30) NOT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `summary` varchar(255) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_log_user_id_foreign` (`user_id`),
  KEY `audit_log_created_at_index` (`created_at`),
  CONSTRAINT `audit_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(190) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(190) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `section` varchar(20) NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(90) NOT NULL,
  `icon` varchar(30) DEFAULT NULL,
  `color` varchar(9) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_section_slug_unique` (`section`,`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,'tourism','Naturaleza','naturaleza','leaf','#2bb673',0),
(2,'tourism','Aventura','aventura','mountain','#ff7a1a',1),
(3,'tourism','Playas','playas','wave','#12b5b0',2),
(4,'tourism','Patrimonio','patrimonio','columns','#a8622f',3),
(5,'tourism','Turismo rural','turismo-rural','farm','#8fbf3a',4),
(6,'tourism','Gastronomía','gastronomia','fork','#ff5a5f',5),
(7,'tourism','Cultura','cultura','mask','#ff3f8e',6),
(8,'tourism','Avistamiento de fauna','avistamiento-de-fauna','bird','#1d6fe0',7),
(9,'tourism','Ciudades','ciudades','city','#7b3fe4',8),
(10,'tourism','Experiencias comunitarias','experiencias-comunitarias','users','#ff7a1a',9),
(11,'gastronomy','Platos típicos','platos-tipicos','fork','#ff6b2c',0),
(12,'gastronomy','Bebidas','bebidas','cup','#12b5b0',1),
(13,'gastronomy','Postres y dulces','postres-y-dulces','star','#ff3f8e',2),
(14,'gastronomy','Ingredientes','ingredientes','leaf','#2bb673',3),
(15,'gastronomy','Recetas','recetas','book','#7b3fe4',4),
(16,'gastronomy','Tradiciones culinarias','tradiciones-culinarias','fire','#c4452f',5),
(17,'gastronomy','Experiencias','experiencias','compass','#1d6fe0',6),
(18,'coffee','Fincas','fincas','farm','#2f7a3a',0),
(19,'coffee','Productores','productores','user','#8a4b2a',1),
(20,'coffee','Proceso','proceso','cycle','#c4452f',2),
(21,'coffee','Preparación','preparacion','cup','#6b3a1a',3),
(22,'coffee','Catas','catas','star','#ff7a1a',4),
(23,'coffee','Rutas cafeteras','rutas-cafeteras','route','#0f9d77',5),
(24,'coffee','Emprendimientos cafeteros','emprendimientos-cafeteros','store','#7b3fe4',6),
(25,'entrepreneurship','Turismo','turismo','compass','#0f9d77',0),
(26,'entrepreneurship','Artesanías','artesanias','hand','#ff7a1a',1),
(27,'entrepreneurship','Gastronomía','gastronomia','fork','#ff5a5f',2),
(28,'entrepreneurship','Café','cafe','coffee','#8a4b2a',3),
(29,'entrepreneurship','Productos culturales','productos-culturales','mask','#ff3f8e',4),
(30,'entrepreneurship','Agroindustria','agroindustria','leaf','#2bb673',5),
(31,'culture','Festividades','festividades','party','#ff3f8e',0),
(32,'culture','Música','musica','music','#1d6fe0',1),
(33,'culture','Danza','danza','dance','#ff7a1a',2),
(34,'culture','Artesanías','artesanias','hand','#a8622f',3),
(35,'culture','Patrimonio','patrimonio','columns','#7b3fe4',4),
(36,'culture','Tradiciones','tradiciones','fire','#2bb673',5),
(37,'culture','Historias locales','historias-locales','book','#12b5b0',6),
(38,'news','Turismo','turismo','compass','#0f9d77',0),
(39,'news','Eventos','eventos','calendar','#ff3f8e',1),
(40,'news','Festividades','festividades','party','#ff7a1a',2),
(41,'news','Territorios','territorios','map','#2bb673',3),
(42,'news','Historias','historias','book','#7b3fe4',4),
(43,'news','Plataforma','plataforma','star','#1d6fe0',5);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `collections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `description` varchar(400) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collections_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collections`
--

LOCK TABLES `collections` WRITE;
/*!40000 ALTER TABLE `collections` DISABLE KEYS */;
INSERT INTO `collections` VALUES
(1,'Paisajes','paisajes','Montañas, mares, selvas y desiertos.',0,'2026-10-27 11:17:35'),
(2,'Sabores','sabores','La cocina de las regiones.',1,'2026-10-27 11:17:35'),
(3,'Cultura cafetera','cultura-cafetera','Fincas, granos y tazas.',2,'2026-10-27 11:17:35'),
(4,'Cultura viva','cultura-viva','Fiestas, música y artesanías.',3,'2026-10-27 11:17:35'),
(5,'Hecho en Colombia','hecho-en-colombia','Productos y oficios de los territorios.',4,'2026-10-27 11:17:35');
/*!40000 ALTER TABLE `collections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `region_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `iso_code` varchar(10) DEFAULT NULL,
  `capital` varchar(100) DEFAULT NULL,
  `summary` varchar(500) DEFAULT NULL,
  `cover_media_id` bigint(20) unsigned DEFAULT NULL,
  `search_name` varchar(120) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_slug_unique` (`slug`),
  KEY `departments_region_id_foreign` (`region_id`),
  CONSTRAINT `departments_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES
(1,2,'Caldas','caldas','CO-CAL','Manizales','Paisaje Cultural Cafetero, nevados y aguas termales.',7,'caldas'),
(2,5,'Amazonas','amazonas','CO-AMA','Leticia','Selva amazónica, el gran río y comunidades que conservan saberes milenarios.',16,'amazonas'),
(3,4,'Meta','meta','CO-MET','Villavicencio','Caño Cristales, llanuras y la cultura llanera.',9,'meta'),
(4,2,'Cundinamarca','cundinamarca','CO-CUN','Bogotá','Lagunas, minas de sal, pueblos de clima templado y páramos.',NULL,'cundinamarca'),
(5,2,'Tolima','tolima','CO-TOL','Ibagué','Capital musical, nevados y el tamal tolimense.',NULL,'tolima'),
(6,2,'Antioquia','antioquia','CO-ANT','Medellín','Montañas, pueblos de zócalos coloridos, café y una capital que se reinventó.',13,'antioquia'),
(7,1,'Atlántico','atlantico','CO-ATL','Barranquilla','Tierra del Carnaval, la brisa caribeña y la desembocadura del Magdalena.',NULL,'atlantico'),
(8,1,'Bolívar','bolivar','CO-BOL','Cartagena de Indias','Ciudad amurallada, islas del Rosario y la memoria de Mompox.',11,'bolivar'),
(9,1,'Cesar','cesar','CO-CES','Valledupar','Cuna del vallenato, a los pies de la Sierra Nevada.',NULL,'cesar'),
(10,1,'Magdalena','magdalena','CO-MAG','Santa Marta','Sierra Nevada, Tayrona y playas donde la selva toca el Caribe.',2,'magdalena'),
(11,1,'Sucre','sucre','CO-SUC','Sincelejo','Golfo de Morrosquillo, manglares y sabanas.',NULL,'sucre'),
(12,1,'Córdoba','cordoba','CO-COR','Montería','Sabanas, ríos y el arte zenú del sombrero vueltiao.',NULL,'cordoba'),
(13,1,'La Guajira','la-guajira','CO-LAG','Riohacha','Desierto que se encuentra con el mar y territorio del pueblo wayuu.',15,'la guajira'),
(14,3,'Chocó','choco','CO-CHO','Quibdó','Selva, ballenas jorobadas y una de las regiones más biodiversas del planeta.',19,'choco'),
(15,3,'Valle del Cauca','valle-del-cauca','CO-VAC','Cali','Salsa, el Pacífico, sabores intensos y biodiversidad.',NULL,'valle del cauca'),
(16,2,'Norte de Santander','norte-de-santander','CO-NSA','Cúcuta','Historia de la independencia, pueblos de montaña y frontera.',NULL,'norte de santander'),
(17,2,'Quindío','quindio','CO-QUI','Armenia','Valle de Cocora, palmas de cera y el corazón del Eje Cafetero.',5,'quindio'),
(18,4,'Vichada','vichada','CO-VID','Puerto Carreño','Parque Nacional Tuparro, ríos y sabanas del Orinoco.',NULL,'vichada'),
(19,5,'Vaupés','vaupes','CO-VAU','Mitú','Selva profunda, raudales y culturas indígenas del Vaupés.',NULL,'vaupes'),
(20,2,'Santander','santander','CO-SAN','Bucaramanga','Cañón del Chicamocha, aventura y pueblos de piedra.',14,'santander'),
(21,2,'Risaralda','risaralda','CO-RIS','Pereira','Termales, café y bosques de niebla.',NULL,'risaralda'),
(22,5,'Putumayo','putumayo','CO-PUT','Mocoa','Cascadas, piedemonte amazónico y medicina tradicional.',23,'putumayo'),
(23,3,'Nariño','narino','CO-NAR','Pasto','Volcanes, la laguna de La Cocha y el Carnaval de Negros y Blancos.',25,'narino'),
(24,5,'Guaviare','guaviare','CO-GUV','San José del Guaviare','Pinturas rupestres, raudales y transición entre llano y selva.',NULL,'guaviare'),
(25,5,'Guainía','guainia','CO-GUA','Inírida','Cerros de Mavecure, ríos de aguas negras y la flor de Inírida.',NULL,'guainia'),
(26,2,'Bogotá D.C.','bogota-d-c','CO-DC','Bogotá','La capital: museos, cerros, barrios históricos y una escena cultural vibrante.',26,'bogota d c'),
(27,4,'Arauca','arauca','CO-ARA','Arauca','Llano abierto, ríos caudalosos y la tradición del joropo.',NULL,'arauca'),
(28,2,'Boyacá','boyaca','CO-BOY','Tunja','Pueblos coloniales, páramos, lagunas sagradas y artesanía en barro.',12,'boyaca'),
(29,4,'Casanare','casanare','CO-CAS','Yopal','Hatos llaneros, avistamiento de fauna y cielos inmensos.',18,'casanare'),
(30,3,'Cauca','cauca','CO-CAU','Popayán','Ciudad blanca, culturas indígenas, volcanes y costa pacífica.',NULL,'cauca'),
(31,5,'Caquetá','caqueta','CO-CAQ','Florencia','Puerta de la Amazonía, con ríos, cascadas y piedemonte.',NULL,'caqueta'),
(32,2,'Huila','huila','CO-HUI','Neiva','San Agustín, el desierto de la Tatacoa y el nacimiento del Magdalena.',20,'huila'),
(33,6,'San Andrés y Providencia','san-andres-y-providencia','CO-SAP','San Andrés','Mar de siete colores, arrecifes y cultura raizal.',4,'san andres y providencia');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entries`
--

DROP TABLE IF EXISTS `entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `section` varchar(20) NOT NULL,
  `title` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `summary` varchar(400) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `municipality_id` bigint(20) unsigned DEFAULT NULL,
  `lat` decimal(10,6) DEFAULT NULL,
  `lng` decimal(10,6) DEFAULT NULL,
  `show_on_map` tinyint(1) NOT NULL DEFAULT 1,
  `cover_media_id` bigint(20) unsigned DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `featured_order` int(11) NOT NULL DEFAULT 0,
  `is_hidden_gem` tinyint(1) NOT NULL DEFAULT 0,
  `event_start` timestamp NULL DEFAULT NULL,
  `event_end` timestamp NULL DEFAULT NULL,
  `author_name` varchar(120) DEFAULT NULL,
  `meta` text DEFAULT NULL,
  `search_text` text DEFAULT NULL,
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entries_section_slug_unique` (`section`,`slug`),
  KEY `entries_cover_media_id_foreign` (`cover_media_id`),
  KEY `entries_created_by_foreign` (`created_by`),
  KEY `entries_updated_by_foreign` (`updated_by`),
  KEY `entries_section_status_published_at_index` (`section`,`status`,`published_at`),
  KEY `entries_department_id_index` (`department_id`),
  KEY `entries_municipality_id_index` (`municipality_id`),
  KEY `entries_event_start_index` (`event_start`),
  CONSTRAINT `entries_cover_media_id_foreign` FOREIGN KEY (`cover_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entries_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entries_municipality_id_foreign` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entries`
--

LOCK TABLES `entries` WRITE;
/*!40000 ALTER TABLE `entries` DISABLE KEYS */;
INSERT INTO `entries` VALUES
(1,'tourism','Cartagena de Indias, la ciudad amurallada','cartagena-de-indias-la-ciudad-amurallada','Balcones con flores, murallas centenarias y atardeceres sobre el Caribe en una ciudad declarada Patrimonio de la Humanidad.','Caminar por el centro histórico es recorrer siglos de historia entre plazas, iglesias y casas de colores. Al caer la tarde, las murallas se llenan de gente que espera el atardecer frente al mar.\n\n## Para no perderse\n- El centro histórico y el barrio Getsemaní.\n- Un paseo por las murallas al atardecer.\n- Las islas cercanas para un día de mar.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-04 11:18:09',8,176,10.423600,-75.550300,1,11,1,1,0,NULL,NULL,NULL,'{\"point_type\":\"Pueblo patrimonial\",\"short_name\":\"Cartagena\",\"tagline\":\"Historia frente al mar\",\"activities\":[\"Recorrido por el centro histórico\",\"Atardecer en las murallas\",\"Paseo a islas cercanas\",\"Gastronomía caribeña\"],\"best_time\":\"Todo el año; de diciembre a abril hay menos lluvias.\",\"altitude\":\"Nivel del mar · clima cálido\"}','cartagena de indias la ciudad amurallada balcones con flores murallas centenarias y atardeceres sobre el caribe en una ciudad declarada patrimonio de la humanidad caminar por el centro historico es recorrer siglos de historia entre plazas iglesias y casas de colores al caer la tarde las murallas se llenan de gente que espera el atardecer frente al mar para no perderse el centro historico y el barrio getsemani un paseo por las murallas al atardecer las islas cercanas para un dia de mar contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar bolivar cartagena de indias playas patrimonio cultura ciudades pueblo patrimonial cartagena historia frente al mar recorrido por el centro historico atardecer en las murallas paseo a islas cercanas gastronomia caribena todo el ano de diciembre a abril hay menos lluvias nivel del mar clima calido',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(2,'tourism','Parque Nacional Natural Tayrona','parque-nacional-natural-tayrona','Donde la selva de la Sierra Nevada se encuentra con playas de arena dorada y grandes rocas.','Senderos entre bosque seco y húmedo conducen a bahías de agua cristalina. Es territorio sagrado para los pueblos de la Sierra Nevada, por lo que el parque cierra algunos periodos del año para su descanso.\n\n## Viaja con respeto\nLleva tus residuos de regreso, respeta las indicaciones de los guardaparques y consulta las fechas de cierre antes de planear.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-19 11:18:09',10,732,11.308900,-74.075300,1,2,1,2,0,NULL,NULL,NULL,'{\"point_type\":\"Playa\",\"short_name\":\"Parque Tayrona\",\"tagline\":\"Donde la selva besa el mar\",\"activities\":[\"Senderismo\",\"Playas\",\"Avistamiento de aves\",\"Camping\"],\"best_time\":\"Consulta los cierres temporales anunciados por Parques Nacionales.\",\"recommendations\":[\"Usa calzado cómodo\",\"Lleva agua y bloqueador biodegradable\",\"No todas las playas son aptas para nadar\"]}','parque nacional natural tayrona donde la selva de la sierra nevada se encuentra con playas de arena dorada y grandes rocas senderos entre bosque seco y humedo conducen a bahias de agua cristalina es territorio sagrado para los pueblos de la sierra nevada por lo que el parque cierra algunos periodos del ano para su descanso viaja con respeto lleva tus residuos de regreso respeta las indicaciones de los guardaparques y consulta las fechas de cierre antes de planear contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar magdalena santa marta naturaleza aventura playas playa parque tayrona donde la selva besa el mar senderismo playas avistamiento de aves camping consulta los cierres temporales anunciados por parques nacionales usa calzado comodo lleva agua y bloqueador biodegradable no todas las playas son aptas para nadar',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(3,'tourism','Caño Cristales, el río de los cinco colores','cano-cristales-el-rio-de-los-cinco-colores','Una planta acuática tiñe el río de rojo, amarillo, verde y rosado durante algunos meses del año.','En la Serranía de La Macarena, el río se convierte en una paleta viva gracias a la *Macarenia clavigera*. La temporada de visita suele ir de junio a noviembre y el acceso es guiado y controlado para proteger el ecosistema.\n\n## Antes de ir\nEl ingreso requiere guías locales y charla de inducción. No se permite usar bloqueador ni repelente en el agua.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-14 11:18:09',3,749,2.264700,-73.792800,1,9,1,3,0,NULL,NULL,NULL,'{\"point_type\":\"Río o laguna\",\"short_name\":\"Caño Cristales\",\"tagline\":\"Un río de colores\",\"activities\":[\"Caminatas guiadas\",\"Baño en pozos autorizados\",\"Fotografía de naturaleza\"],\"best_time\":\"Temporada habitual: junio a noviembre.\",\"how_to_get\":\"Vuelo a La Macarena y traslados con operadores locales autorizados.\"}','cano cristales el rio de los cinco colores una planta acuatica tine el rio de rojo amarillo verde y rosado durante algunos meses del ano en la serrania de la macarena el rio se convierte en una paleta viva gracias a la macarenia clavigera la temporada de visita suele ir de junio a noviembre y el acceso es guiado y controlado para proteger el ecosistema antes de ir el ingreso requiere guias locales y charla de induccion no se permite usar bloqueador ni repelente en el agua contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar meta la macarena naturaleza aventura rio o laguna cano cristales un rio de colores caminatas guiadas bano en pozos autorizados fotografia de naturaleza temporada habitual junio a noviembre vuelo a la macarena y traslados con operadores locales autorizados',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(4,'tourism','Valle de Cocora y las palmas de cera','valle-de-cocora-y-las-palmas-de-cera','Las palmas de cera, árbol nacional de Colombia, se elevan entre la niebla del valle.','La palma de cera del Quindío puede superar los 50 metros de altura y es una especie protegida. Caminos entre potreros, bosque de niebla y puentes colgantes conducen a miradores inolvidables.\n\nMuy cerca está Salento, con su calle Real y sus casas de bahareque de colores.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-26 11:18:09',17,894,4.638600,-75.487500,1,5,1,4,0,NULL,NULL,NULL,'{\"point_type\":\"Sendero\",\"short_name\":\"Valle de Cocora\",\"tagline\":\"Las palmas más altas del mundo\",\"activities\":[\"Caminata por el valle\",\"Cabalgatas\",\"Avistamiento de colibríes\",\"Visita a Salento\"],\"altitude\":\"Entre 1.800 y 2.400 m · clima frío\",\"best_time\":\"Temprano en la mañana, antes de que llegue la niebla.\"}','valle de cocora y las palmas de cera las palmas de cera arbol nacional de colombia se elevan entre la niebla del valle la palma de cera del quindio puede superar los 50 metros de altura y es una especie protegida caminos entre potreros bosque de niebla y puentes colgantes conducen a miradores inolvidables muy cerca esta salento con su calle real y sus casas de bahareque de colores contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar quindio salento naturaleza aventura turismo rural sendero valle de cocora las palmas mas altas del mundo caminata por el valle cabalgatas avistamiento de colibries visita a salento entre 1 800 y 2 400 m clima frio temprano en la manana antes de que llegue la niebla',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(5,'tourism','Desierto de la Tatacoa','desierto-de-la-tatacoa','Laberintos de arcilla roja y gris de día; uno de los cielos más limpios del país de noche.','Este bosque seco tropical esconde cárcavas rojizas en la zona de El Cuzco y formaciones grises en Los Hoyos. Al anochecer, la observación astronómica es la gran protagonista.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-13 11:18:09',32,689,3.233300,-75.166700,1,20,1,5,0,NULL,NULL,NULL,'{\"point_type\":\"Desierto\",\"short_name\":\"Desierto de la Tatacoa\",\"tagline\":\"Un cielo lleno de estrellas\",\"activities\":[\"Observación de estrellas\",\"Caminatas por las cárcavas\",\"Piscinas naturales\",\"Fotografía\"],\"best_time\":\"Temporadas secas para cielos despejados.\",\"recommendations\":[\"Lleva sombrero e hidratación\",\"Evita caminar en las horas de más calor\"]}','desierto de la tatacoa laberintos de arcilla roja y gris de dia uno de los cielos mas limpios del pais de noche este bosque seco tropical esconde carcavas rojizas en la zona de el cuzco y formaciones grises en los hoyos al anochecer la observacion astronomica es la gran protagonista contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar huila villavieja naturaleza aventura desierto desierto de la tatacoa un cielo lleno de estrellas observacion de estrellas caminatas por las carcavas piscinas naturales fotografia temporadas secas para cielos despejados lleva sombrero e hidratacion evita caminar en las horas de mas calor',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(6,'tourism','Cabo de la Vela','cabo-de-la-vela','Desierto, viento y mar en el territorio ancestral del pueblo wayuu.','El Cabo de la Vela, Jepirra para los wayuu, es un lugar sagrado. Aquí el desierto se encuentra con el mar y los atardeceres desde el Pilón de Azúcar son legendarios.\n\nLas rancherías ofrecen hospedaje en chinchorros y la oportunidad de conocer el tejido y la cocina wayuu.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-27 11:18:09',13,704,12.200000,-72.150000,1,15,1,6,0,NULL,NULL,NULL,'{\"point_type\":\"Experiencia comunitaria\",\"short_name\":\"Cabo de la Vela\",\"tagline\":\"Donde el desierto abraza el mar\",\"activities\":[\"Kitesurf\",\"Atardecer en el Pilón de Azúcar\",\"Hospedaje en ranchería\",\"Encuentro con artesanas wayuu\"],\"recommendations\":[\"Viaja con operadores locales\",\"Pide permiso antes de fotografiar personas\"]}','cabo de la vela desierto viento y mar en el territorio ancestral del pueblo wayuu el cabo de la vela jepirra para los wayuu es un lugar sagrado aqui el desierto se encuentra con el mar y los atardeceres desde el pilon de azucar son legendarios las rancherias ofrecen hospedaje en chinchorros y la oportunidad de conocer el tejido y la cocina wayuu contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar la guajira uribia aventura playas cultura experiencias comunitarias experiencia comunitaria cabo de la vela donde el desierto abraza el mar kitesurf atardecer en el pilon de azucar hospedaje en rancheria encuentro con artesanas wayuu viaja con operadores locales pide permiso antes de fotografiar personas',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(7,'tourism','San Andrés, el mar de siete colores','san-andres-el-mar-de-siete-colores','Arrecifes, cayos y una cultura raizal con su propia lengua, música y cocina.','Las tonalidades del mar se deben a la profundidad y al fondo de arena blanca y corales. Más allá de las playas, la isla guarda la cultura raizal, el creole y platos como el *rondón*.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-12 11:18:09',33,910,12.584700,-81.700600,1,4,1,7,0,NULL,NULL,NULL,'{\"point_type\":\"Playa\",\"short_name\":\"San Andrés\",\"tagline\":\"El mar de siete colores\",\"activities\":[\"Buceo y careteo\",\"Vuelta a la isla\",\"Visita a cayos\",\"Gastronomía raizal\"],\"altitude\":\"Nivel del mar · clima cálido\"}','san andres el mar de siete colores arrecifes cayos y una cultura raizal con su propia lengua musica y cocina las tonalidades del mar se deben a la profundidad y al fondo de arena blanca y corales mas alla de las playas la isla guarda la cultura raizal el creole y platos como el rondon contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar san andres y providencia san andres naturaleza playas cultura playa san andres el mar de siete colores buceo y careteo vuelta a la isla visita a cayos gastronomia raizal nivel del mar clima calido',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(8,'tourism','Villa de Leyva','villa-de-leyva','Casas blancas, calles empedradas y una de las plazas más grandes del país.','Un pueblo colonial rodeado de paisajes semidesérticos, fósiles, viñedos y pozos de agua azul. Ideal para recorrer sin prisa.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-12 11:18:09',28,334,5.633300,-73.523600,1,12,0,0,0,NULL,NULL,NULL,'{\"point_type\":\"Pueblo patrimonial\",\"short_name\":\"Villa de Leyva\",\"tagline\":\"Un pueblo detenido en el tiempo\",\"activities\":[\"Recorrido por la plaza mayor\",\"Museo paleontológico\",\"Ciclismo\",\"Mercado campesino\"],\"altitude\":\"2.150 m · clima templado frío\"}','villa de leyva casas blancas calles empedradas y una de las plazas mas grandes del pais un pueblo colonial rodeado de paisajes semideserticos fosiles vinedos y pozos de agua azul ideal para recorrer sin prisa contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar boyaca villa de leyva patrimonio turismo rural cultura pueblo patrimonial villa de leyva un pueblo detenido en el tiempo recorrido por la plaza mayor museo paleontologico ciclismo mercado campesino 2 150 m clima templado frio',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(9,'tourism','Barichara y el Camino Real','barichara-y-el-camino-real','Un pueblo de piedra y tapia pisada desde donde parte un antiguo camino hacia Guane.','Barichara conserva su arquitectura en piedra y tierra. El Camino Real, empedrado, atraviesa el cañón hasta el pequeño pueblo de Guane en una caminata de pocas horas.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-02 11:18:09',20,915,6.634700,-73.223100,1,14,0,0,1,NULL,NULL,NULL,'{\"point_type\":\"Sendero\",\"short_name\":\"Barichara\",\"tagline\":\"El pueblo más lindo de piedra\",\"activities\":[\"Caminata del Camino Real\",\"Talleres de papel artesanal\",\"Miradores del cañón\"],\"best_time\":\"Temprano en la mañana para caminar con fresco.\"}','barichara y el camino real un pueblo de piedra y tapia pisada desde donde parte un antiguo camino hacia guane barichara conserva su arquitectura en piedra y tierra el camino real empedrado atraviesa el canon hasta el pequeno pueblo de guane en una caminata de pocas horas contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar santander barichara aventura patrimonio turismo rural sendero barichara el pueblo mas lindo de piedra caminata del camino real talleres de papel artesanal miradores del canon temprano en la manana para caminar con fresco',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(10,'tourism','Guatapé y la Piedra del Peñol','guatape-y-la-piedra-del-penol','Zócalos de colores que cuentan historias y una roca gigante con vista a un mar de islas.','Los zócalos, relieves pintados en la base de las casas, convierten a Guatapé en una galería al aire libre. Muy cerca, más de 700 escalones llevan a la cima de la Piedra del Peñol.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-17 11:18:09',6,64,6.220800,-75.178100,1,13,1,8,0,NULL,NULL,NULL,'{\"point_type\":\"Mirador\",\"short_name\":\"Guatapé\",\"tagline\":\"Un pueblo pintado de colores\",\"activities\":[\"Subir la Piedra del Peñol\",\"Paseo en lancha por el embalse\",\"Recorrer la calle de los zócalos\"]}','guatape y la piedra del penol zocalos de colores que cuentan historias y una roca gigante con vista a un mar de islas los zocalos relieves pintados en la base de las casas convierten a guatape en una galeria al aire libre muy cerca mas de 700 escalones llevan a la cima de la piedra del penol contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar antioquia guatape naturaleza aventura cultura mirador guatape un pueblo pintado de colores subir la piedra del penol paseo en lancha por el embalse recorrer la calle de los zocalos',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(11,'tourism','Parque Arqueológico de San Agustín','parque-arqueologico-de-san-agustin','Cientos de esculturas monolíticas talladas por una cultura ancestral, en medio de montañas verdes.','Declarado Patrimonio de la Humanidad, el parque reúne estatuas, tumbas y montículos funerarios. Los alrededores guardan el estrecho del Magdalena y otros sitios arqueológicos.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-22 11:18:09',32,681,1.885800,-76.296900,1,24,0,0,0,NULL,NULL,NULL,'{\"point_type\":\"Museo o sitio arqueológico\",\"short_name\":\"San Agustín\",\"tagline\":\"Guardianes de piedra\",\"activities\":[\"Recorrido arqueológico\",\"Estrecho del Magdalena\",\"Cabalgatas a sitios cercanos\"]}','parque arqueologico de san agustin cientos de esculturas monoliticas talladas por una cultura ancestral en medio de montanas verdes declarado patrimonio de la humanidad el parque reune estatuas tumbas y monticulos funerarios los alrededores guardan el estrecho del magdalena y otros sitios arqueologicos contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar huila san agustin naturaleza patrimonio cultura museo o sitio arqueologico san agustin guardianes de piedra recorrido arqueologico estrecho del magdalena cabalgatas a sitios cercanos',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(12,'tourism','Leticia y el río Amazonas','leticia-y-el-rio-amazonas','La puerta a la selva amazónica, en la triple frontera con Brasil y Perú.','Desde Leticia se navega el Amazonas para conocer lagos con delfines rosados, comunidades indígenas y la reserva de selva. Al atardecer, miles de garzas llegan al Parque Santander.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-19 11:18:09',2,5,-4.215300,-69.940600,1,16,1,9,0,NULL,NULL,NULL,'{\"point_type\":\"Avistamiento de fauna\",\"short_name\":\"Amazonas\",\"tagline\":\"El corazón verde del planeta\",\"activities\":[\"Navegación por el Amazonas\",\"Avistamiento de delfines\",\"Caminatas nocturnas guiadas\",\"Visita a comunidades con operadores locales\"]}','leticia y el rio amazonas la puerta a la selva amazonica en la triple frontera con brasil y peru desde leticia se navega el amazonas para conocer lagos con delfines rosados comunidades indigenas y la reserva de selva al atardecer miles de garzas llegan al parque santander contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar amazonas leticia naturaleza cultura avistamiento de fauna experiencias comunitarias avistamiento de fauna amazonas el corazon verde del planeta navegacion por el amazonas avistamiento de delfines caminatas nocturnas guiadas visita a comunidades con operadores locales',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(13,'tourism','Puerto Nariño, el pesebre natural','puerto-narino-el-pesebre-natural','Un pueblo sin carros, con senderos peatonales y lagos donde nadan delfines rosados.','A orillas del río Loretoyacu, Puerto Nariño es un ejemplo de convivencia con la selva. Cerca están los lagos de Tarapoto.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-14 11:18:09',2,9,-3.770300,-70.383100,1,17,0,0,1,NULL,NULL,NULL,'{\"point_type\":\"Experiencia comunitaria\",\"short_name\":\"Puerto Nariño\",\"tagline\":\"Un pueblo sin carros\",\"activities\":[\"Lagos de Tarapoto\",\"Mirador del pueblo\",\"Avistamiento de delfines\"]}','puerto narino el pesebre natural un pueblo sin carros con senderos peatonales y lagos donde nadan delfines rosados a orillas del rio loretoyacu puerto narino es un ejemplo de convivencia con la selva cerca estan los lagos de tarapoto contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar amazonas puerto narino naturaleza avistamiento de fauna experiencias comunitarias experiencia comunitaria puerto narino un pueblo sin carros lagos de tarapoto mirador del pueblo avistamiento de delfines',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(14,'tourism','Nuquí y las ballenas jorobadas','nuqui-y-las-ballenas-jorobadas','Selva que llega al mar, termales y la visita anual de las ballenas jorobadas.','Entre julio y octubre, las ballenas jorobadas llegan al Pacífico colombiano para aparearse y dar a luz. Nuquí combina playas solitarias, cascadas y aguas termales.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-10 11:18:09',14,485,5.712500,-77.270000,1,19,0,0,1,NULL,NULL,NULL,'{\"point_type\":\"Avistamiento de fauna\",\"short_name\":\"Nuquí\",\"tagline\":\"Donde cantan las ballenas\",\"activities\":[\"Avistamiento de ballenas\",\"Termales\",\"Surf\",\"Caminatas a cascadas\"],\"best_time\":\"Temporada de ballenas: julio a octubre.\"}','nuqui y las ballenas jorobadas selva que llega al mar termales y la visita anual de las ballenas jorobadas entre julio y octubre las ballenas jorobadas llegan al pacifico colombiano para aparearse y dar a luz nuqui combina playas solitarias cascadas y aguas termales contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar choco nuqui naturaleza playas avistamiento de fauna experiencias comunitarias avistamiento de fauna nuqui donde cantan las ballenas avistamiento de ballenas termales surf caminatas a cascadas temporada de ballenas julio a octubre',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(15,'tourism','Páramo de Ocetá, Monguí','paramo-de-oceta-mongui','Un bosque de frailejones, lagunas y formaciones rocosas a más de 3.000 metros.','Muchos lo consideran uno de los páramos más bellos del mundo. La caminata parte de Monguí, un pueblo boyacense famoso por sus balones artesanales.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-17 11:18:09',28,267,5.700000,-72.820000,1,28,1,10,1,NULL,NULL,NULL,'{\"point_type\":\"Montaña o páramo\",\"short_name\":\"Páramo de Ocetá\",\"tagline\":\"Un bosque de frailejones\",\"activities\":[\"Caminata guiada al páramo\",\"Lagunas de alta montaña\",\"Taller de balones en Monguí\"],\"altitude\":\"Más de 3.000 m · clima frío de páramo\",\"recommendations\":[\"Contrata guías locales\",\"No pises ni arranques frailejones\"]}','paramo de oceta mongui un bosque de frailejones lagunas y formaciones rocosas a mas de 3 000 metros muchos lo consideran uno de los paramos mas bellos del mundo la caminata parte de mongui un pueblo boyacense famoso por sus balones artesanales contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar boyaca mongui naturaleza aventura turismo rural montana o paramo paramo de oceta un bosque de frailejones caminata guiada al paramo lagunas de alta montana taller de balones en mongui mas de 3 000 m clima frio de paramo contrata guias locales no pises ni arranques frailejones',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(16,'tourism','Sierra Nevada de El Cocuy','sierra-nevada-de-el-cocuy','Picos nevados, lagunas glaciares y caminatas de alta montaña.','El parque protege la mayor masa glaciar de Colombia. El ingreso está regulado y solo se permite por senderos autorizados para proteger el ecosistema y el territorio del pueblo U\'wa.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-10 11:18:09',28,245,6.410000,-72.330000,1,27,0,0,0,NULL,NULL,NULL,'{\"point_type\":\"Montaña o páramo\",\"short_name\":\"El Cocuy\",\"tagline\":\"Nieves eternas\",\"activities\":[\"Senderismo de alta montaña\",\"Lagunas glaciares\"],\"altitude\":\"Hasta más de 5.000 m\"}','sierra nevada de el cocuy picos nevados lagunas glaciares y caminatas de alta montana el parque protege la mayor masa glaciar de colombia el ingreso esta regulado y solo se permite por senderos autorizados para proteger el ecosistema y el territorio del pueblo u wa contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar boyaca el cocuy naturaleza aventura montana o paramo el cocuy nieves eternas senderismo de alta montana lagunas glaciares hasta mas de 5 000 m',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(17,'tourism','Medellín, la ciudad de la eterna primavera','medellin-la-ciudad-de-la-eterna-primavera','Metrocable, arte urbano, parques biblioteca y un clima amable todo el año.','La transformación de Medellín se recorre en sus barrios, en el arte de la Comuna 13 y en el metrocable que sube a las montañas.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-04 11:18:09',6,79,6.247600,-75.565800,1,22,0,0,0,NULL,NULL,NULL,'{\"point_type\":\"Ciudad\",\"short_name\":\"Medellín\",\"tagline\":\"La eterna primavera\",\"activities\":[\"Recorrido de arte urbano\",\"Metrocable\",\"Museos y parques\",\"Feria de las Flores (agosto)\"]}','medellin la ciudad de la eterna primavera metrocable arte urbano parques biblioteca y un clima amable todo el ano la transformacion de medellin se recorre en sus barrios en el arte de la comuna 13 y en el metrocable que sube a las montanas contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar antioquia medellin gastronomia cultura ciudades ciudad medellin la eterna primavera recorrido de arte urbano metrocable museos y parques feria de las flores agosto',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(18,'tourism','Santa Cruz de Mompox','santa-cruz-de-mompox','Una ciudad detenida en el tiempo a orillas del río Magdalena, famosa por su filigrana.','Mompox conserva su trazado colonial frente al río. Sus orfebres mantienen viva la filigrana en plata y su Semana Santa es una de las más tradicionales del país.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-03 11:18:09',8,188,9.238600,-74.426900,1,14,0,0,1,NULL,NULL,NULL,'{\"point_type\":\"Pueblo patrimonial\",\"short_name\":\"Mompox\",\"tagline\":\"Filigrana junto al río\",\"activities\":[\"Recorrido por el centro histórico\",\"Talleres de filigrana\",\"Paseo por la ciénaga de Pijiño\"]}','santa cruz de mompox una ciudad detenida en el tiempo a orillas del rio magdalena famosa por su filigrana mompox conserva su trazado colonial frente al rio sus orfebres mantienen viva la filigrana en plata y su semana santa es una de las mas tradicionales del pais contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar bolivar mompos patrimonio cultura pueblo patrimonial mompox filigrana junto al rio recorrido por el centro historico talleres de filigrana paseo por la cienaga de pijino',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(19,'tourism','Llanos de Casanare','llanos-de-casanare','Hatos llaneros, chigüiros, garzas y atardeceres que parecen no terminar.','En los hatos de Casanare se vive la cultura llanera: trabajo de llano, coleo, joropo y una fauna que se deja ver en las sabanas inundables.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-25 11:18:09',29,388,5.880000,-71.890000,1,18,0,0,1,NULL,NULL,NULL,'{\"point_type\":\"Avistamiento de fauna\",\"short_name\":\"Llanos de Casanare\",\"tagline\":\"Atardeceres infinitos\",\"activities\":[\"Safari llanero\",\"Avistamiento de aves\",\"Vida en el hato\",\"Joropo\"]}','llanos de casanare hatos llaneros chiguiros garzas y atardeceres que parecen no terminar en los hatos de casanare se vive la cultura llanera trabajo de llano coleo joropo y una fauna que se deja ver en las sabanas inundables contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar casanare paz de ariporo naturaleza turismo rural avistamiento de fauna avistamiento de fauna llanos de casanare atardeceres infinitos safari llanero avistamiento de aves vida en el hato joropo',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(20,'tourism','Cascada Fin del Mundo','cascada-fin-del-mundo','Una caminata por la selva del piedemonte amazónico que termina en una caída de agua de unos 70 metros.','El sendero pasa por varios pozos de agua cristalina antes de llegar al borde de la cascada. Es una de las experiencias de naturaleza más populares de Mocoa.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-09-08 11:18:09',22,871,1.136100,-76.646100,1,23,0,0,1,NULL,NULL,NULL,'{\"point_type\":\"Cascada\",\"short_name\":\"Fin del Mundo\",\"tagline\":\"Una cascada en la selva\",\"activities\":[\"Senderismo\",\"Baño en pozos naturales\"],\"recommendations\":[\"Usa calzado con buen agarre\",\"No te acerques al borde de la cascada\"]}','cascada fin del mundo una caminata por la selva del piedemonte amazonico que termina en una caida de agua de unos 70 metros el sendero pasa por varios pozos de agua cristalina antes de llegar al borde de la cascada es una de las experiencias de naturaleza mas populares de mocoa contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar putumayo mocoa naturaleza aventura cascada fin del mundo una cascada en la selva senderismo bano en pozos naturales usa calzado con buen agarre no te acerques al borde de la cascada',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(21,'tourism','Laguna de La Cocha','laguna-de-la-cocha','Un gran lago andino con la isla de La Corota y casitas de colores en sus orillas.','A media hora de Pasto, la laguna sorprende por su tamaño y su neblina. En El Encano se puede probar trucha y visitar el santuario de flora de la isla La Corota.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-22 11:18:09',23,808,1.070000,-77.150000,1,25,0,0,1,NULL,NULL,NULL,'{\"point_type\":\"Río o laguna\",\"short_name\":\"Laguna de La Cocha\",\"tagline\":\"Un lago entre la niebla\",\"activities\":[\"Paseo en lancha\",\"Isla La Corota\",\"Gastronomía de trucha\"],\"altitude\":\"2.760 m · clima frío\"}','laguna de la cocha un gran lago andino con la isla de la corota y casitas de colores en sus orillas a media hora de pasto la laguna sorprende por su tamano y su neblina en el encano se puede probar trucha y visitar el santuario de flora de la isla la corota contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar narino pasto naturaleza turismo rural gastronomia rio o laguna laguna de la cocha un lago entre la niebla paseo en lancha isla la corota gastronomia de trucha 2 760 m clima frio',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(22,'tourism','Bogotá y La Candelaria','bogota-y-la-candelaria','Museos, cerros, barrios coloniales y una de las escenas culturales más intensas de América Latina.','La Candelaria concentra museos, teatros y arte urbano. Desde los cerros orientales se observa la ciudad extendida sobre la sabana.\n\n> Contenido de demostración: verifica horarios, tarifas y condiciones de acceso con fuentes oficiales antes de viajar.','published','2026-10-10 11:18:09',26,167,4.598100,-74.075800,1,26,0,0,0,NULL,NULL,NULL,'{\"point_type\":\"Ciudad\",\"short_name\":\"Bogotá\",\"tagline\":\"La capital a 2.600 metros\",\"activities\":[\"Museos del centro\",\"Subir a un cerro tutelar\",\"Ciclovía dominical\",\"Ajiaco santafereño\"]}','bogota y la candelaria museos cerros barrios coloniales y una de las escenas culturales mas intensas de america latina la candelaria concentra museos teatros y arte urbano desde los cerros orientales se observa la ciudad extendida sobre la sabana contenido de demostracion verifica horarios tarifas y condiciones de acceso con fuentes oficiales antes de viajar bogota d c bogota d c patrimonio cultura ciudades ciudad bogota la capital a 2 600 metros museos del centro subir a un cerro tutelar ciclovia dominical ajiaco santafereno',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(23,'gastronomy','Bandeja paisa','bandeja-paisa','El plato más abundante de Antioquia: fríjoles, arroz, chicharrón, huevo, aguacate, arepa y plátano.','Nacida de la cocina campesina y arriera, la bandeja reúne en un solo plato la energía necesaria para largas jornadas en la montaña.','published','2026-09-05 11:18:09',6,79,6.254920,-75.625760,1,29,1,1,0,NULL,NULL,NULL,'{\"dish_type\":\"Plato típico\",\"ingredients\":[\"Fríjoles rojos\",\"Arroz blanco\",\"Chicharrón\",\"Carne molida\",\"Chorizo\",\"Huevo frito\",\"Tajadas de plátano maduro\",\"Aguacate\",\"Arepa\"],\"origin\":\"Antioquia y el Eje Cafetero\",\"history\":\"Su forma actual se popularizó en el siglo XX, pero sus ingredientes provienen de la alimentación arriera.\",\"point_type\":\"Restaurante o sabor típico\"}','bandeja paisa el plato mas abundante de antioquia frijoles arroz chicharron huevo aguacate arepa y platano nacida de la cocina campesina y arriera la bandeja reune en un solo plato la energia necesaria para largas jornadas en la montana antioquia medellin platos tipicos plato tipico frijoles rojos arroz blanco chicharron carne molida chorizo huevo frito tajadas de platano maduro aguacate arepa antioquia y el eje cafetero su forma actual se popularizo en el siglo xx pero sus ingredientes provienen de la alimentacion arriera restaurante o sabor tipico',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(24,'gastronomy','Ajiaco santafereño','ajiaco-santafereno','Sopa espesa de tres papas, pollo, mazorca y guascas, servida con crema, alcaparras y aguacate.',NULL,'published','2026-10-22 11:18:09',26,167,4.604470,-74.100370,1,30,1,2,0,NULL,NULL,NULL,'{\"dish_type\":\"Receta\",\"ingredients\":[\"Papa criolla\",\"Papa pastusa\",\"Papa sabanera\",\"Pechuga de pollo\",\"Mazorca\",\"Guascas\",\"Crema de leche\",\"Alcaparras\",\"Aguacate\"],\"preparation\":[\"Cocina el pollo con cebolla larga, ajo y sal.\",\"Agrega las papas en trozos y las mazorcas.\",\"Cuando la papa criolla se deshaga y espese, añade las guascas.\",\"Desmecha el pollo y sirve con crema, alcaparras y aguacate.\"],\"origin\":\"Bogotá y el altiplano cundiboyacense\",\"point_type\":\"Restaurante o sabor típico\"}','ajiaco santafereno sopa espesa de tres papas pollo mazorca y guascas servida con crema alcaparras y aguacate bogota d c bogota d c platos tipicos recetas receta papa criolla papa pastusa papa sabanera pechuga de pollo mazorca guascas crema de leche alcaparras aguacate cocina el pollo con cebolla larga ajo y sal agrega las papas en trozos y las mazorcas cuando la papa criolla se deshaga y espese anade las guascas desmecha el pollo y sirve con crema alcaparras y aguacate bogota y el altiplano cundiboyacense restaurante o sabor tipico',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(25,'gastronomy','Arepa de huevo','arepa-de-huevo','Arepa frita, inflada y rellena con un huevo entero: un clásico de la costa Caribe.',NULL,'published','2026-10-10 11:18:09',7,150,10.634130,-75.161700,1,31,1,3,0,NULL,NULL,NULL,'{\"dish_type\":\"Plato típico\",\"ingredients\":[\"Masa de maíz\",\"Huevos\",\"Sal\",\"Aceite para freír\"],\"origin\":\"Costa Caribe; Luruaco celebra un festival dedicado a ella.\",\"point_type\":\"Restaurante o sabor típico\"}','arepa de huevo arepa frita inflada y rellena con un huevo entero un clasico de la costa caribe atlantico luruaco platos tipicos tradiciones culinarias plato tipico masa de maiz huevos sal aceite para freir costa caribe luruaco celebra un festival dedicado a ella restaurante o sabor tipico',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(26,'gastronomy','Sancocho de gallina','sancocho-de-gallina','La sopa del encuentro: se cocina en leña, en olla grande y en familia.',NULL,'published','2026-09-21 11:18:09',15,1080,3.404540,-76.574060,1,32,0,0,0,NULL,NULL,NULL,'{\"dish_type\":\"Plato típico\",\"ingredients\":[\"Gallina criolla\",\"Plátano verde\",\"Yuca\",\"Papa\",\"Mazorca\",\"Cilantro\",\"Cebolla larga\"],\"history\":\"Cada región tiene su versión: de gallina, de pescado, trifásico o de costilla.\",\"point_type\":\"Restaurante o sabor típico\"}','sancocho de gallina la sopa del encuentro se cocina en lena en olla grande y en familia valle del cauca cali platos tipicos tradiciones culinarias plato tipico gallina criolla platano verde yuca papa mazorca cilantro cebolla larga cada region tiene su version de gallina de pescado trifasico o de costilla restaurante o sabor tipico',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(27,'gastronomy','Empanadas de pipián','empanadas-de-pipian','Pequeñas empanadas rellenas de papa colorada y maní, acompañadas con ají de maní.',NULL,'published','2026-10-03 11:18:09',30,424,2.481710,-76.595050,1,33,0,0,1,NULL,NULL,NULL,'{\"dish_type\":\"Plato típico\",\"ingredients\":[\"Masa de maíz\",\"Papa colorada\",\"Maní tostado\",\"Hogao\",\"Ají de maní\"],\"origin\":\"Popayán, ciudad UNESCO de la gastronomía.\",\"point_type\":\"Restaurante o sabor típico\"}','empanadas de pipian pequenas empanadas rellenas de papa colorada y mani acompanadas con aji de mani cauca popayan platos tipicos plato tipico masa de maiz papa colorada mani tostado hogao aji de mani popayan ciudad unesco de la gastronomia restaurante o sabor tipico',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(28,'gastronomy','Frutas que solo conocerás aquí','frutas-que-solo-conoceras-aqui','Lulo, maracuyá, pitahaya, guanábana, uchuva y borojó: un arcoíris de sabores tropicales.','La diversidad de climas hace de Colombia uno de los países con más frutas del mundo. Pruébalas en jugos, salpicones o directamente en las plazas de mercado.','published','2026-10-01 11:18:09',NULL,NULL,NULL,NULL,0,34,1,4,0,NULL,NULL,NULL,'{\"dish_type\":\"Ingrediente\",\"ingredients\":[\"Lulo\",\"Maracuyá\",\"Pitahaya\",\"Guanábana\",\"Uchuva\",\"Borojó\",\"Mango\"],\"point_type\":\"Restaurante o sabor típico\"}','frutas que solo conoceras aqui lulo maracuya pitahaya guanabana uchuva y borojo un arcoiris de sabores tropicales la diversidad de climas hace de colombia uno de los paises con mas frutas del mundo pruebalas en jugos salpicones o directamente en las plazas de mercado bebidas ingredientes ingrediente lulo maracuya pitahaya guanabana uchuva borojo mango restaurante o sabor tipico',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(29,'gastronomy','Cazuela de mariscos','cazuela-de-mariscos','Mariscos en salsa cremosa con leche de coco: el sabor del Caribe en una olla de barro.',NULL,'published','2026-08-29 11:18:09',8,176,10.457920,-75.455730,1,35,0,0,0,NULL,NULL,NULL,'{\"dish_type\":\"Plato típico\",\"ingredients\":[\"Camarones\",\"Calamar\",\"Mejillones\",\"Pescado\",\"Leche de coco\",\"Ají dulce\",\"Cebolla\",\"Ajo\"],\"point_type\":\"Restaurante o sabor típico\"}','cazuela de mariscos mariscos en salsa cremosa con leche de coco el sabor del caribe en una olla de barro bolivar cartagena de indias platos tipicos plato tipico camarones calamar mejillones pescado leche de coco aji dulce cebolla ajo restaurante o sabor tipico',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(30,'gastronomy','Tamal tolimense','tamal-tolimense','Masa de arroz y maíz con carnes, huevo y verduras, envuelta en hoja de plátano.',NULL,'published','2026-10-12 11:18:09',5,1045,4.455490,-75.271320,1,36,0,0,1,NULL,NULL,NULL,'{\"dish_type\":\"Plato típico\",\"ingredients\":[\"Masa de maíz y arroz\",\"Pollo\",\"Cerdo\",\"Huevo\",\"Zanahoria\",\"Arveja\",\"Hoja de plátano\"],\"history\":\"Es protagonista del desayuno de domingo, acompañado de chocolate y arepa.\",\"point_type\":\"Restaurante o sabor típico\"}','tamal tolimense masa de arroz y maiz con carnes huevo y verduras envuelta en hoja de platano tolima ibague platos tipicos tradiciones culinarias plato tipico masa de maiz y arroz pollo cerdo huevo zanahoria arveja hoja de platano es protagonista del desayuno de domingo acompanado de chocolate y arepa restaurante o sabor tipico',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(31,'gastronomy','Lulada caleña','lulada-calena','Lulo picado, limón, azúcar y hielo: la bebida refrescante del Valle del Cauca.',NULL,'published','2026-10-10 11:18:09',15,1080,3.404540,-76.574060,1,37,0,0,0,NULL,NULL,NULL,'{\"dish_type\":\"Bebida\",\"ingredients\":[\"Lulo\",\"Limón\",\"Azúcar\",\"Hielo\",\"Agua\"],\"preparation\":[\"Pica el lulo en trozos pequeños sin licuar.\",\"Agrega jugo de limón y azúcar al gusto.\",\"Completa con agua y hielo y mezcla.\"],\"point_type\":\"Restaurante o sabor típico\"}','lulada calena lulo picado limon azucar y hielo la bebida refrescante del valle del cauca valle del cauca cali bebidas bebida lulo limon azucar hielo agua pica el lulo en trozos pequenos sin licuar agrega jugo de limon y azucar al gusto completa con agua y hielo y mezcla restaurante o sabor tipico',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(32,'coffee','Siembra y cultivo','siembra-y-cultivo','El café arábico crece en las laderas andinas, entre sombra, montaña y lluvias bien distribuidas.',NULL,'published','2026-09-23 11:18:09',NULL,NULL,NULL,NULL,0,8,0,0,0,NULL,NULL,NULL,'{\"step\":\"1\"}','siembra y cultivo el cafe arabico crece en las laderas andinas entre sombra montana y lluvias bien distribuidas proceso 1',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(33,'coffee','Cosecha a mano','cosecha-a-mano','Recolectores seleccionan solo las cerezas maduras, grano por grano, en las laderas.',NULL,'published','2026-10-17 11:18:09',NULL,NULL,NULL,NULL,0,39,0,0,0,NULL,NULL,NULL,'{\"step\":\"2\"}','cosecha a mano recolectores seleccionan solo las cerezas maduras grano por grano en las laderas proceso 2',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(34,'coffee','Despulpado y fermentación','despulpado-y-fermentacion','Se retira la pulpa y el grano fermenta para desarrollar sus sabores; luego se lava.',NULL,'published','2026-09-20 11:18:09',NULL,NULL,NULL,NULL,0,40,0,0,0,NULL,NULL,NULL,'{\"step\":\"3\"}','despulpado y fermentacion se retira la pulpa y el grano fermenta para desarrollar sus sabores luego se lava proceso 3',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(35,'coffee','Secado al sol','secado-al-sol','El café pergamino se seca en patios, marquesinas o silos hasta alcanzar la humedad ideal.',NULL,'published','2026-09-17 11:18:09',NULL,NULL,NULL,NULL,0,40,0,0,0,NULL,NULL,NULL,'{\"step\":\"4\"}','secado al sol el cafe pergamino se seca en patios marquesinas o silos hasta alcanzar la humedad ideal proceso 4',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(36,'coffee','Tostión','tostion','El calor transforma el grano verde en aromas de chocolate, frutas, caramelo o flores.',NULL,'published','2026-10-16 11:18:09',NULL,NULL,NULL,NULL,0,41,0,0,0,NULL,NULL,NULL,'{\"step\":\"5\"}','tostion el calor transforma el grano verde en aromas de chocolate frutas caramelo o flores proceso 5',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(37,'coffee','La taza','la-taza','Molienda, agua y método: el último paso de un largo viaje desde la montaña.',NULL,'published','2026-09-20 11:18:09',NULL,NULL,NULL,NULL,0,38,0,0,0,NULL,NULL,NULL,'{\"step\":\"6\"}','la taza molienda agua y metodo el ultimo paso de un largo viaje desde la montana proceso preparacion 6',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(38,'coffee','Paisaje Cultural Cafetero','paisaje-cultural-cafetero','Patrimonio de la Humanidad desde 2011: montañas, pueblos de bahareque y la cultura del café.','El paisaje abarca zonas de Caldas, Quindío, Risaralda y Valle del Cauca. Su valor está tanto en el territorio como en la tradición de las familias caficultoras.','published','2026-10-12 11:18:09',1,341,4.980000,-75.600000,1,7,1,1,0,NULL,NULL,NULL,'{\"point_type\":\"Finca o cafetal\",\"altitude\":\"Entre 1.000 y 2.000 m\",\"process\":\"Predomina el beneficio lavado\"}','paisaje cultural cafetero patrimonio de la humanidad desde 2011 montanas pueblos de bahareque y la cultura del cafe el paisaje abarca zonas de caldas quindio risaralda y valle del cauca su valor esta tanto en el territorio como en la tradicion de las familias caficultoras caldas chinchina rutas cafeteras finca o cafetal entre 1 000 y 2 000 m predomina el beneficio lavado',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(39,'coffee','Finca La Esperanza (demo)','finca-la-esperanza-demo','Ficha de DEMOSTRACIÓN para mostrar cómo se ve una finca cafetera con recorrido y cata.','Esta finca no existe: es un ejemplo para que el equipo editorial vea cómo se presenta una ficha completa.\n\n## Experiencia\nRecorrido por el cultivo, recolección, beneficio y cata guiada.','published','2026-10-03 11:18:09',17,888,4.680000,-75.660000,1,39,1,2,0,NULL,NULL,NULL,'{\"point_type\":\"Finca o cafetal\",\"altitude\":\"1.850 m\",\"varieties\":\"Castillo, Caturra\",\"process\":\"Lavado\",\"tasting_notes\":\"Panela, naranja, chocolate\",\"contact_email\":\"finca@example.com\",\"address\":\"Dirección de ejemplo\"}','finca la esperanza demo ficha de demostracion para mostrar como se ve una finca cafetera con recorrido y cata esta finca no existe es un ejemplo para que el equipo editorial vea como se presenta una ficha completa experiencia recorrido por el cultivo recoleccion beneficio y cata guiada quindio filandia fincas catas finca o cafetal 1 850 m castillo caturra lavado panela naranja chocolate finca example com direccion de ejemplo',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(40,'coffee','El tinto de todos los días','el-tinto-de-todos-los-dias','Así llamamos al café negro que se ofrece en casas, plazas y oficinas como gesto de bienvenida.',NULL,'published','2026-10-21 11:18:09',NULL,NULL,NULL,NULL,0,38,0,0,0,NULL,NULL,NULL,'{\"tasting_notes\":\"Depende del origen y la tostión\"}','el tinto de todos los dias asi llamamos al cafe negro que se ofrece en casas plazas y oficinas como gesto de bienvenida preparacion depende del origen y la tostion',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(41,'entrepreneurship','Barro y Fuego (demo)','barro-y-fuego-demo','Emprendimiento de DEMOSTRACIÓN: taller ficticio de cerámica para mostrar la ficha de un emprendimiento.',NULL,'published','2026-10-20 11:18:09',28,289,5.540000,-73.630000,1,49,1,0,0,NULL,NULL,NULL,'{\"point_type\":\"Mercado o artesanías\",\"products\":[\"Vasijas utilitarias\",\"Talleres de modelado\",\"Piezas decorativas\"],\"contact_name\":\"Nombre de ejemplo\",\"contact_email\":\"contacto@example.com\",\"website\":\"https:\\/\\/example.com\"}','barro y fuego demo emprendimiento de demostracion taller ficticio de ceramica para mostrar la ficha de un emprendimiento boyaca raquira artesanias mercado o artesanias vasijas utilitarias talleres de modelado piezas decorativas nombre de ejemplo contacto example com https example com',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(42,'entrepreneurship','Hilos del Viento (demo)','hilos-del-viento-demo','Emprendimiento de DEMOSTRACIÓN: colectivo ficticio de tejido para ilustrar la sección.',NULL,'published','2026-09-17 11:18:09',30,432,2.610000,-76.380000,1,50,0,0,0,NULL,NULL,NULL,'{\"point_type\":\"Mercado o artesanías\",\"products\":[\"Ruanas\",\"Mochilas\",\"Tapices\"],\"contact_email\":\"contacto@example.com\"}','hilos del viento demo emprendimiento de demostracion colectivo ficticio de tejido para ilustrar la seccion cauca silvia artesanias productos culturales mercado o artesanias ruanas mochilas tapices contacto example com',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(43,'entrepreneurship','Mercado de la Vereda (demo)','mercado-de-la-vereda-demo','Emprendimiento de DEMOSTRACIÓN: mercado campesino ficticio para mostrar filtros y búsqueda.',NULL,'published','2026-09-27 11:18:09',28,334,5.664760,-73.513590,1,51,0,0,0,NULL,NULL,NULL,'{\"products\":[\"Frutas y verduras\",\"Lácteos\",\"Conservas\"]}','mercado de la vereda demo emprendimiento de demostracion mercado campesino ficticio para mostrar filtros y busqueda boyaca villa de leyva gastronomia agroindustria frutas y verduras lacteos conservas',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(44,'entrepreneurship','Miel del Cañón (demo)','miel-del-canon-demo','Emprendimiento de DEMOSTRACIÓN: apiario ficticio con productos derivados de la miel.',NULL,'published','2026-09-04 11:18:09',20,915,6.651830,-73.225580,1,52,0,0,0,NULL,NULL,NULL,'{\"products\":[\"Miel\",\"Polen\",\"Propóleo\"],\"instagram\":\"ejemplo\"}','miel del canon demo emprendimiento de demostracion apiario ficticio con productos derivados de la miel santander barichara agroindustria miel polen propoleo ejemplo',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(45,'entrepreneurship','Origen Alto (demo)','origen-alto-demo','Emprendimiento de DEMOSTRACIÓN: tostadora ficticia de cafés especiales con experiencias de cata.',NULL,'published','2026-09-30 11:18:09',17,894,4.616650,-75.541680,1,38,0,0,0,NULL,NULL,NULL,'{\"products\":[\"Café tostado\",\"Catas guiadas\",\"Barismo básico\"],\"website\":\"https:\\/\\/example.com\"}','origen alto demo emprendimiento de demostracion tostadora ficticia de cafes especiales con experiencias de cata quindio salento turismo cafe cafe tostado catas guiadas barismo basico https example com',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(46,'culture','Carnaval de Barranquilla','carnaval-de-barranquilla','Obra Maestra del Patrimonio Oral e Inmaterial de la Humanidad: cuatro días de danza, disfraces y música.','Marimondas, congos, cumbia, mapalé y letanías llenan las calles durante los días previos al Miércoles de Ceniza.\n\n> Fechas de referencia: confirma la programación oficial.','published','2026-09-24 11:18:09',7,145,10.968500,-74.781300,1,46,1,1,0,'2027-02-06 09:00:00','2027-02-09 23:00:00',NULL,'{\"point_type\":\"Festividad\",\"venue\":\"Vía 40 y calles de Barranquilla\",\"community\":\"Hacedores del Carnaval, comparsas y cumbiambas\",\"schedule\":\"Sábado a martes antes del Miércoles de Ceniza\"}','carnaval de barranquilla obra maestra del patrimonio oral e inmaterial de la humanidad cuatro dias de danza disfraces y musica marimondas congos cumbia mapale y letanias llenan las calles durante los dias previos al miercoles de ceniza fechas de referencia confirma la programacion oficial atlantico barranquilla festividades musica danza patrimonio festividad via 40 y calles de barranquilla hacedores del carnaval comparsas y cumbiambas sabado a martes antes del miercoles de ceniza',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(47,'culture','Carnaval de Negros y Blancos','carnaval-de-negros-y-blancos','Carrozas monumentales, juego con talco y un mensaje de convivencia en el sur andino.','Reconocido como Patrimonio Cultural Inmaterial de la Humanidad, el carnaval celebra la diversidad con desfiles y artesanos que trabajan todo el año.\n\n> Fechas de referencia: confirma la programación oficial.','published','2026-09-16 11:18:09',23,808,1.213600,-77.281100,1,46,1,2,0,'2027-01-02 09:00:00','2027-01-07 23:00:00',NULL,'{\"point_type\":\"Festividad\",\"venue\":\"Senda del Carnaval, Pasto\",\"schedule\":\"Del 2 al 7 de enero\"}','carnaval de negros y blancos carrozas monumentales juego con talco y un mensaje de convivencia en el sur andino reconocido como patrimonio cultural inmaterial de la humanidad el carnaval celebra la diversidad con desfiles y artesanos que trabajan todo el ano fechas de referencia confirma la programacion oficial narino pasto festividades patrimonio festividad senda del carnaval pasto del 2 al 7 de enero',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(48,'culture','Feria de las Flores','feria-de-las-flores','El desfile de silleteros lleva sobre la espalda arreglos florales de hasta 70 kilos.','La tradición silletera nació en el corregimiento de Santa Elena y hoy es Patrimonio Cultural de la Nación.\n\n> Fechas de referencia: confirma la programación oficial.','published','2026-10-17 11:18:09',6,79,6.244200,-75.581200,1,45,0,0,0,'2027-07-30 09:00:00','2027-08-08 23:00:00',NULL,'{\"point_type\":\"Festividad\",\"community\":\"Silleteros de Santa Elena\",\"schedule\":\"Primeras semanas de agosto\"}','feria de las flores el desfile de silleteros lleva sobre la espalda arreglos florales de hasta 70 kilos la tradicion silletera nacio en el corregimiento de santa elena y hoy es patrimonio cultural de la nacion fechas de referencia confirma la programacion oficial antioquia medellin festividades tradiciones festividad silleteros de santa elena primeras semanas de agosto',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(49,'culture','Festival de la Leyenda Vallenata','festival-de-la-leyenda-vallenata','Acordeón, caja y guacharaca compiten por la corona de rey vallenato.','El vallenato tradicional fue inscrito por la UNESCO en la Lista de Salvaguardia Urgente. El festival reúne a juglares y nuevas generaciones.\n\n> Fechas de referencia: confirma la programación oficial.','published','2026-10-09 11:18:09',9,465,10.463100,-73.253200,1,47,1,3,0,'2027-04-28 09:00:00','2027-05-01 23:00:00',NULL,'{\"point_type\":\"Música y danza\",\"venue\":\"Parque de la Leyenda Vallenata\",\"schedule\":\"Finales de abril\"}','festival de la leyenda vallenata acordeon caja y guacharaca compiten por la corona de rey vallenato el vallenato tradicional fue inscrito por la unesco en la lista de salvaguardia urgente el festival reune a juglares y nuevas generaciones fechas de referencia confirma la programacion oficial cesar valledupar festividades musica musica y danza parque de la leyenda vallenata finales de abril',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(50,'culture','Festival Petronio Álvarez','festival-petronio-alvarez','Marimba, cantos y cocina del Pacífico se toman Cali en uno de los festivales afro más grandes de América.','> Fechas de referencia: confirma la programación oficial.','published','2026-10-21 11:18:09',15,1080,3.451600,-76.532000,1,19,0,0,0,'2027-08-12 16:00:00','2027-08-17 23:00:00',NULL,'{\"point_type\":\"Música y danza\",\"community\":\"Agrupaciones del litoral Pacífico\",\"schedule\":\"Agosto\"}','festival petronio alvarez marimba cantos y cocina del pacifico se toman cali en uno de los festivales afro mas grandes de america fechas de referencia confirma la programacion oficial valle del cauca cali festividades musica musica y danza agrupaciones del litoral pacifico agosto',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(51,'culture','Fiestas de la Independencia de Cartagena','fiestas-de-la-independencia-de-cartagena','Cabildos, desfiles y música conmemoran la independencia de Cartagena del 11 de noviembre de 1811.','> Fechas de referencia: confirma la programación oficial.','published','2026-10-21 11:18:09',8,176,10.423600,-75.550300,1,11,0,0,0,'2026-11-07 10:00:00','2026-11-11 23:00:00',NULL,'{\"point_type\":\"Festividad\",\"schedule\":\"Alrededor del 11 de noviembre\"}','fiestas de la independencia de cartagena cabildos desfiles y musica conmemoran la independencia de cartagena del 11 de noviembre de 1811 fechas de referencia confirma la programacion oficial bolivar cartagena de indias festividades danza festividad alrededor del 11 de noviembre',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(52,'culture','Noche de las Velitas','noche-de-las-velitas','La víspera del 8 de diciembre, familias de todo el país encienden velas y faroles para abrir la Navidad.',NULL,'published','2026-09-09 11:18:09',NULL,NULL,NULL,NULL,0,21,0,0,0,'2026-12-07 18:00:00','2026-12-08 02:00:00',NULL,'{\"schedule\":\"7 de diciembre en la noche\"}','noche de las velitas la vispera del 8 de diciembre familias de todo el pais encienden velas y faroles para abrir la navidad festividades tradiciones 7 de diciembre en la noche',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(53,'culture','Semana Santa en Popayán','semana-santa-en-popayan','Procesiones nocturnas centenarias recorren la ciudad blanca; Patrimonio Inmaterial de la Humanidad.','> Fechas de referencia: confirma la programación oficial.','published','2026-09-13 11:18:09',30,424,2.441800,-76.606300,1,12,0,0,0,'2027-03-23 19:00:00','2027-03-27 23:00:00',NULL,'{\"point_type\":\"Festividad\",\"respect_note\":\"Es una celebración religiosa: guarda silencio durante las procesiones y no uses flash.\"}','semana santa en popayan procesiones nocturnas centenarias recorren la ciudad blanca patrimonio inmaterial de la humanidad fechas de referencia confirma la programacion oficial cauca popayan patrimonio tradiciones festividad es una celebracion religiosa guarda silencio durante las procesiones y no uses flash',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(54,'culture','El sombrero vueltiao','el-sombrero-vueltiao','Trenzado en caña flecha por el pueblo zenú, es símbolo cultural de la nación.','El número de vueltas de trenza indica la finura del sombrero. Tuchín es uno de los centros artesanales más reconocidos.','published','2026-09-23 11:18:09',12,524,9.185600,-75.553100,1,44,1,4,0,NULL,NULL,NULL,'{\"point_type\":\"Mercado o artesanías\",\"community\":\"Pueblo zenú\",\"respect_note\":\"Compra directamente a artesanas y artesanos y valora el tiempo de elaboración.\"}','el sombrero vueltiao trenzado en cana flecha por el pueblo zenu es simbolo cultural de la nacion el numero de vueltas de trenza indica la finura del sombrero tuchin es uno de los centros artesanales mas reconocidos cordoba tuchin artesanias patrimonio mercado o artesanias pueblo zenu compra directamente a artesanas y artesanos y valora el tiempo de elaboracion',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(55,'culture','Mochila wayuu','mochila-wayuu','Cada kanaas, o dibujo tejido, guarda un significado de la cosmovisión wayuu.','Según la tradición, el tejido fue enseñado por Wale\'kerü, la araña tejedora. Una mochila puede tardar semanas en terminarse.','published','2026-09-05 11:18:09',13,704,11.713700,-72.265500,1,42,0,0,0,NULL,NULL,NULL,'{\"point_type\":\"Mercado o artesanías\",\"community\":\"Pueblo wayuu\",\"respect_note\":\"Pide permiso antes de fotografiar y prefiere el comercio justo.\"}','mochila wayuu cada kanaas o dibujo tejido guarda un significado de la cosmovision wayuu segun la tradicion el tejido fue ensenado por wale keru la arana tejedora una mochila puede tardar semanas en terminarse la guajira uribia artesanias tradiciones mercado o artesanias pueblo wayuu pide permiso antes de fotografiar y prefiere el comercio justo',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(56,'culture','La cumbia','la-cumbia','Tambores, gaitas y polleras que giran: un ritmo nacido del encuentro de culturas en el Caribe.','El Banco, Magdalena, celebra cada año el Festival Nacional de la Cumbia.','published','2026-10-01 11:18:09',10,714,9.000000,-73.970000,1,48,0,0,0,NULL,NULL,NULL,'{\"point_type\":\"Música y danza\",\"schedule\":\"Festival Nacional de la Cumbia: mitad de año\"}','la cumbia tambores gaitas y polleras que giran un ritmo nacido del encuentro de culturas en el caribe el banco magdalena celebra cada ano el festival nacional de la cumbia magdalena el banco musica danza musica y danza festival nacional de la cumbia mitad de ano',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(57,'culture','Molas del pueblo guna dule','molas-del-pueblo-guna-dule','Telas superpuestas y recortadas a mano forman figuras que hablan de la naturaleza y el mundo espiritual.',NULL,'published','2026-10-10 11:18:09',6,85,8.506340,-76.677100,1,43,0,0,1,NULL,NULL,NULL,'{\"community\":\"Pueblo guna dule\",\"respect_note\":\"Las molas son parte de la indumentaria tradicional: valora su significado más allá de lo decorativo.\"}','molas del pueblo guna dule telas superpuestas y recortadas a mano forman figuras que hablan de la naturaleza y el mundo espiritual antioquia necocli artesanias pueblo guna dule las molas son parte de la indumentaria tradicional valora su significado mas alla de lo decorativo',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(58,'news','Bienvenidos a Voy por Colombia','bienvenidos-a-voy-por-colombia','Noticia de demostración: así funciona la plataforma para descubrir la diversidad del país.','Esta es una **noticia de demostración**. La plataforma reúne destinos, sabores, café, emprendimientos y cultura en un mapa interactivo con datos geográficos reales.\n\n## Qué puedes hacer\n- Explorar el mapa por departamento y municipio.\n- Guardar tus lugares favoritos.\n- Armar tu propia ruta y compartirla.\n\nTodo el contenido se administra desde el panel privado.','published','2026-10-26 11:18:09',NULL,NULL,NULL,NULL,0,9,1,0,0,NULL,NULL,'Equipo editorial (demo)','[]','bienvenidos a voy por colombia noticia de demostracion asi funciona la plataforma para descubrir la diversidad del pais esta es una noticia de demostracion la plataforma reune destinos sabores cafe emprendimientos y cultura en un mapa interactivo con datos geograficos reales que puedes hacer explorar el mapa por departamento y municipio guardar tus lugares favoritos armar tu propia ruta y compartirla todo el contenido se administra desde el panel privado plataforma',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(59,'news','Temporada de ballenas en el Pacífico','temporada-de-ballenas-en-el-pacifico','Noticia de demostración: entre julio y octubre las ballenas jorobadas visitan la costa pacífica.','Las ballenas jorobadas recorren miles de kilómetros desde aguas australes. Elige operadores que respeten las distancias de observación.\n\nTexto de demostración.','published','2026-10-23 11:18:09',14,485,NULL,NULL,0,19,0,0,0,NULL,NULL,'Equipo editorial (demo)','[]','temporada de ballenas en el pacifico noticia de demostracion entre julio y octubre las ballenas jorobadas visitan la costa pacifica las ballenas jorobadas recorren miles de kilometros desde aguas australes elige operadores que respeten las distancias de observacion texto de demostracion choco nuqui turismo territorios',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(60,'news','Cinco joyas poco conocidas para tu próximo viaje','cinco-joyas-poco-conocidas-para-tu-proximo-viaje','Noticia de demostración: páramos, pueblos sin carros y cascadas que merecen estar en tu lista.','- Páramo de Ocetá, en Monguí.\n- Puerto Nariño, en el Amazonas.\n- Nuquí, en el Chocó.\n- Mompox, a orillas del Magdalena.\n- La Cascada Fin del Mundo, en Mocoa.\n\nTexto de demostración.','published','2026-10-19 11:18:09',NULL,NULL,NULL,NULL,0,28,0,0,0,NULL,NULL,'Equipo editorial (demo)','[]','cinco joyas poco conocidas para tu proximo viaje noticia de demostracion paramos pueblos sin carros y cascadas que merecen estar en tu lista paramo de oceta en mongui puerto narino en el amazonas nuqui en el choco mompox a orillas del magdalena la cascada fin del mundo en mocoa texto de demostracion turismo historias',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(61,'news','Calendario de festividades: fechas para agendar','calendario-de-festividades-fechas-para-agendar','Noticia de demostración: carnavales, ferias y festivales que se viven en todo el país.','Consulta el calendario de la sección Cultura. Las fechas publicadas son de referencia y deben confirmarse con los organizadores.\n\nTexto de demostración.','published','2026-10-15 11:18:09',NULL,NULL,NULL,NULL,0,46,0,0,0,NULL,NULL,'Equipo editorial (demo)','[]','calendario de festividades fechas para agendar noticia de demostracion carnavales ferias y festivales que se viven en todo el pais consulta el calendario de la seccion cultura las fechas publicadas son de referencia y deben confirmarse con los organizadores texto de demostracion eventos festividades',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(62,'news','Turismo respetuoso con las comunidades','turismo-respetuoso-con-las-comunidades','Noticia de demostración: recomendaciones para viajar valorando los territorios y sus saberes.','- Contrata guías y operadores locales.\n- Pide permiso antes de fotografiar personas.\n- Compra directamente a artesanos y productores.\n- Lleva tus residuos de regreso.\n\nTexto de demostración.','published','2026-10-07 11:18:09',NULL,NULL,NULL,NULL,0,42,0,0,0,NULL,NULL,'Equipo editorial (demo)','[]','turismo respetuoso con las comunidades noticia de demostracion recomendaciones para viajar valorando los territorios y sus saberes contrata guias y operadores locales pide permiso antes de fotografiar personas compra directamente a artesanos y productores lleva tus residuos de regreso texto de demostracion territorios historias',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(63,'news','Nueva ruta por el Eje Cafetero (programada)','nueva-ruta-por-el-eje-cafetero-programada','Noticia de demostración PROGRAMADA: se publicará automáticamente en unos días.','Ejemplo de publicación programada: no aparece en la página pública hasta su fecha.','published','2026-10-30 11:18:09',NULL,NULL,NULL,NULL,0,7,0,0,0,NULL,NULL,'Equipo editorial (demo)','[]','nueva ruta por el eje cafetero programada noticia de demostracion programada se publicara automaticamente en unos dias ejemplo de publicacion programada no aparece en la pagina publica hasta su fecha turismo',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(64,'news','Borrador: guía de mercados campesinos','borrador-guia-de-mercados-campesinos','Ejemplo de BORRADOR: solo visible en el panel y en la vista previa.','Texto en preparación.','draft','2026-10-27 11:18:09',NULL,NULL,NULL,NULL,0,51,0,0,0,NULL,NULL,'Equipo editorial (demo)','[]','borrador guia de mercados campesinos ejemplo de borrador solo visible en el panel y en la vista previa texto en preparacion territorios',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(65,'news','Archivo: temporada de fin de año anterior','archivo-temporada-de-fin-de-ano-anterior','Ejemplo de publicación ARCHIVADA: ya no aparece en la página pública.','Contenido archivado.','archived','2025-12-31 11:18:09',NULL,NULL,NULL,NULL,0,25,0,0,0,NULL,NULL,'Equipo editorial (demo)','[]','archivo temporada de fin de ano anterior ejemplo de publicacion archivada ya no aparece en la pagina publica contenido archivado eventos',1,NULL,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09');
/*!40000 ALTER TABLE `entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entry_categories`
--

DROP TABLE IF EXISTS `entry_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entry_categories` (
  `entry_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`entry_id`,`category_id`),
  KEY `entry_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `entry_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entry_categories_entry_id_foreign` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entry_categories`
--

LOCK TABLES `entry_categories` WRITE;
/*!40000 ALTER TABLE `entry_categories` DISABLE KEYS */;
INSERT INTO `entry_categories` VALUES
(1,3),
(1,4),
(1,7),
(1,9),
(2,1),
(2,2),
(2,3),
(3,1),
(3,2),
(4,1),
(4,2),
(4,5),
(5,1),
(5,2),
(6,2),
(6,3),
(6,7),
(6,10),
(7,1),
(7,3),
(7,7),
(8,4),
(8,5),
(8,7),
(9,2),
(9,4),
(9,5),
(10,1),
(10,2),
(10,7),
(11,1),
(11,4),
(11,7),
(12,1),
(12,7),
(12,8),
(12,10),
(13,1),
(13,8),
(13,10),
(14,1),
(14,3),
(14,8),
(14,10),
(15,1),
(15,2),
(15,5),
(16,1),
(16,2),
(17,6),
(17,7),
(17,9),
(18,4),
(18,7),
(19,1),
(19,5),
(19,8),
(20,1),
(20,2),
(21,1),
(21,5),
(21,6),
(22,4),
(22,7),
(22,9),
(23,11),
(24,11),
(24,15),
(25,11),
(25,16),
(26,11),
(26,16),
(27,11),
(28,12),
(28,14),
(29,11),
(30,11),
(30,16),
(31,12),
(32,20),
(33,20),
(34,20),
(35,20),
(36,20),
(37,20),
(37,21),
(38,23),
(39,18),
(39,22),
(40,21),
(41,26),
(42,26),
(42,29),
(43,27),
(43,30),
(44,30),
(45,25),
(45,28),
(46,31),
(46,32),
(46,33),
(46,35),
(47,31),
(47,35),
(48,31),
(48,36),
(49,31),
(49,32),
(50,31),
(50,32),
(51,31),
(51,33),
(52,31),
(52,36),
(53,35),
(53,36),
(54,34),
(54,35),
(55,34),
(55,36),
(56,32),
(56,33),
(57,34),
(58,43),
(59,38),
(59,41),
(60,38),
(60,42),
(61,39),
(61,40),
(62,41),
(62,42),
(63,38),
(64,41),
(65,39);
/*!40000 ALTER TABLE `entry_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entry_media`
--

DROP TABLE IF EXISTS `entry_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entry_media` (
  `entry_id` bigint(20) unsigned NOT NULL,
  `media_id` bigint(20) unsigned NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`entry_id`,`media_id`),
  KEY `entry_media_media_id_foreign` (`media_id`),
  CONSTRAINT `entry_media_entry_id_foreign` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entry_media_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entry_media`
--

LOCK TABLES `entry_media` WRITE;
/*!40000 ALTER TABLE `entry_media` DISABLE KEYS */;
INSERT INTO `entry_media` VALUES
(1,3,1),
(1,11,0),
(1,35,2),
(2,2,0),
(2,3,1),
(3,9,0),
(3,10,1),
(3,53,2),
(3,54,3),
(4,5,0),
(4,6,1),
(4,39,2),
(5,20,0),
(5,21,1),
(6,15,0),
(6,42,1),
(7,3,1),
(7,4,0),
(8,12,0),
(8,51,1),
(9,14,0),
(9,52,1),
(10,13,0),
(11,24,0),
(12,16,0),
(12,17,1),
(13,17,0),
(14,19,0),
(15,28,0),
(16,27,0),
(17,22,0),
(17,29,2),
(17,45,1),
(18,12,0),
(19,18,0),
(20,23,0),
(21,25,0),
(22,26,0),
(22,30,1),
(23,29,0),
(24,30,0),
(25,31,0),
(26,32,0),
(27,33,0),
(28,34,0),
(28,37,1),
(29,35,0),
(30,36,0),
(31,37,0),
(38,7,0),
(38,8,1),
(38,39,2),
(39,38,2),
(39,39,0),
(39,40,1),
(40,38,0),
(40,41,1),
(41,49,0),
(42,50,0),
(43,51,0),
(44,52,0),
(45,38,0),
(45,40,1),
(46,46,0),
(46,48,1),
(47,25,1),
(47,46,0),
(48,45,0),
(49,47,0),
(50,19,0),
(50,47,1),
(51,11,0),
(52,21,0),
(53,12,0),
(54,44,0),
(55,42,0),
(56,47,1),
(56,48,0),
(57,43,0);
/*!40000 ALTER TABLE `entry_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kind` varchar(12) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `mime` varchar(80) DEFAULT NULL,
  `original_name` varchar(190) DEFAULT NULL,
  `size_bytes` int(10) unsigned NOT NULL DEFAULT 0,
  `width` int(10) unsigned DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  `variants` text DEFAULT NULL,
  `embed_provider` varchar(20) DEFAULT NULL,
  `embed_id` varchar(40) DEFAULT NULL,
  `title` varchar(190) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `caption` varchar(500) DEFAULT NULL,
  `credit` varchar(190) DEFAULT NULL,
  `license` varchar(120) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `municipality_id` bigint(20) unsigned DEFAULT NULL,
  `collection_id` bigint(20) unsigned DEFAULT NULL,
  `gallery_section` varchar(20) DEFAULT NULL,
  `in_gallery` tinyint(1) NOT NULL DEFAULT 0,
  `gallery_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `media_department_id_foreign` (`department_id`),
  KEY `media_municipality_id_foreign` (`municipality_id`),
  KEY `media_collection_id_foreign` (`collection_id`),
  KEY `media_uploaded_by_foreign` (`uploaded_by`),
  KEY `media_in_gallery_kind_created_at_index` (`in_gallery`,`kind`,`created_at`),
  CONSTRAINT `media_collection_id_foreign` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_municipality_id_foreign` FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES
(1,'image','marca/97766ba180559de41aaef004.webp','image/webp','portada-paisaje.webp',291156,1672,705,'{\"thumb\":\"marca\\/97766ba180559de41aaef004-thumb.webp\",\"medium\":\"marca\\/97766ba180559de41aaef004-medium.webp\",\"large\":\"marca\\/97766ba180559de41aaef004-large.webp\"}',NULL,NULL,'Paisajes de Colombia','Costa con aguas turquesa, selva, montañas y una cascada al atardecer',NULL,'Imagen de portada · Voy por Colombia',NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,'2026-10-27 11:17:36','2026-10-27 11:17:36'),
(2,'image','demo/a5548edd53c362f84b4943d3.webp','image/webp','caribe.webp',26836,1600,977,'{\"thumb\":\"demo\\/a5548edd53c362f84b4943d3-thumb.webp\",\"medium\":\"demo\\/a5548edd53c362f84b4943d3-medium.webp\",\"large\":\"demo\\/a5548edd53c362f84b4943d3-large.webp\"}',NULL,NULL,'Playa caribeña','Ilustración de playa con palmeras y mar turquesa','Playa caribeña. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,10,732,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:36','2026-10-27 11:17:36'),
(3,'image','demo/a5885abbb8528743c128ca21.webp','image/webp','caribe-2.webp',32960,1400,1400,'{\"thumb\":\"demo\\/a5885abbb8528743c128ca21-thumb.webp\",\"medium\":\"demo\\/a5885abbb8528743c128ca21-medium.webp\",\"large\":\"demo\\/a5885abbb8528743c128ca21-large.webp\"}',NULL,NULL,'Mar Caribe','Ilustración de costa caribeña con palmeras','Mar Caribe. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,10,732,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:37','2026-10-27 11:17:37'),
(4,'image','demo/585e1b3354f4275075b5ce37.webp','image/webp','san-andres.webp',20778,1600,1100,'{\"thumb\":\"demo\\/585e1b3354f4275075b5ce37-thumb.webp\",\"medium\":\"demo\\/585e1b3354f4275075b5ce37-medium.webp\",\"large\":\"demo\\/585e1b3354f4275075b5ce37-large.webp\"}',NULL,NULL,'Mar de siete colores','Ilustración aérea de franjas de mar en tonos azules y verdes','Mar de siete colores. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,33,910,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:38','2026-10-27 11:17:38'),
(5,'image','demo/b4932fcf513100a854d708bc.webp','image/webp','cocora.webp',35334,1600,977,'{\"thumb\":\"demo\\/b4932fcf513100a854d708bc-thumb.webp\",\"medium\":\"demo\\/b4932fcf513100a854d708bc-medium.webp\",\"large\":\"demo\\/b4932fcf513100a854d708bc-large.webp\"}',NULL,NULL,'Valle de palmas de cera','Ilustración de colinas verdes con palmas de cera y neblina','Valle de palmas de cera. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,17,894,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:38','2026-10-27 11:17:38'),
(6,'image','demo/43477be2259331a1b8bc3498.webp','image/webp','cocora-vertical.webp',41734,1100,1500,'{\"thumb\":\"demo\\/43477be2259331a1b8bc3498-thumb.webp\",\"medium\":\"demo\\/43477be2259331a1b8bc3498-medium.webp\",\"large\":\"demo\\/43477be2259331a1b8bc3498-large.webp\"}',NULL,NULL,'Palmas entre la niebla','Ilustración vertical de palmas de cera','Palmas entre la niebla. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,17,894,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:39','2026-10-27 11:17:39'),
(7,'image','demo/5b2221596adbf06bd93d8055.webp','image/webp','paisaje-cafetero.webp',43480,1600,977,'{\"thumb\":\"demo\\/5b2221596adbf06bd93d8055-thumb.webp\",\"medium\":\"demo\\/5b2221596adbf06bd93d8055-medium.webp\",\"large\":\"demo\\/5b2221596adbf06bd93d8055-large.webp\"}',NULL,NULL,'Montañas de café','Ilustración de laderas con surcos de café y una casa campesina','Montañas de café. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,1,341,3,'coffee',1,1,1,NULL,'2026-10-27 11:17:40','2026-10-27 11:17:40'),
(8,'image','demo/2b1e8cc4c317d54836b68984.webp','image/webp','paisaje-cafetero-2.webp',49088,1400,1400,'{\"thumb\":\"demo\\/2b1e8cc4c317d54836b68984-thumb.webp\",\"medium\":\"demo\\/2b1e8cc4c317d54836b68984-medium.webp\",\"large\":\"demo\\/2b1e8cc4c317d54836b68984-large.webp\"}',NULL,NULL,'Surcos cafeteros','Ilustración de cultivos de café en ladera','Surcos cafeteros. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,17,888,3,'coffee',1,0,1,NULL,'2026-10-27 11:17:40','2026-10-27 11:17:40'),
(9,'image','demo/fd610492289b8c827f065527.webp','image/webp','cano-cristales.webp',40410,1600,1100,'{\"thumb\":\"demo\\/fd610492289b8c827f065527-thumb.webp\",\"medium\":\"demo\\/fd610492289b8c827f065527-medium.webp\",\"large\":\"demo\\/fd610492289b8c827f065527-large.webp\"}',NULL,NULL,'Río de cinco colores','Ilustración de río con plantas acuáticas rojas, amarillas y verdes','Río de cinco colores. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,3,749,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:41','2026-10-27 11:17:41'),
(10,'image','demo/e4f497c8d6e508481ee57582.webp','image/webp','cano-cristales-vertical.webp',39436,1100,1500,'{\"thumb\":\"demo\\/e4f497c8d6e508481ee57582-thumb.webp\",\"medium\":\"demo\\/e4f497c8d6e508481ee57582-medium.webp\",\"large\":\"demo\\/e4f497c8d6e508481ee57582-large.webp\"}',NULL,NULL,'Colores del río','Ilustración vertical del río de colores','Colores del río. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,3,749,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:42','2026-10-27 11:17:42'),
(11,'image','demo/b8dae771fbd0f797a867e1fd.webp','image/webp','cartagena.webp',30806,1600,977,'{\"thumb\":\"demo\\/b8dae771fbd0f797a867e1fd-thumb.webp\",\"medium\":\"demo\\/b8dae771fbd0f797a867e1fd-medium.webp\",\"large\":\"demo\\/b8dae771fbd0f797a867e1fd-large.webp\"}',NULL,NULL,'Calle colonial colorida','Ilustración de casas coloniales con balcones y buganvilias','Calle colonial colorida. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,8,176,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:42','2026-10-27 11:17:42'),
(12,'image','demo/72456d9ccad3a83bdc79a75b.webp','image/webp','villa-de-leyva.webp',38124,1600,1100,'{\"thumb\":\"demo\\/72456d9ccad3a83bdc79a75b-thumb.webp\",\"medium\":\"demo\\/72456d9ccad3a83bdc79a75b-medium.webp\",\"large\":\"demo\\/72456d9ccad3a83bdc79a75b-large.webp\"}',NULL,NULL,'Pueblo blanco','Ilustración de casas blancas con balcones de madera','Pueblo blanco. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,28,334,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:43','2026-10-27 11:17:43'),
(13,'image','demo/8cb6d30396335eb81df97bfa.webp','image/webp','guatape.webp',31626,1400,1400,'{\"thumb\":\"demo\\/8cb6d30396335eb81df97bfa-thumb.webp\",\"medium\":\"demo\\/8cb6d30396335eb81df97bfa-medium.webp\",\"large\":\"demo\\/8cb6d30396335eb81df97bfa-large.webp\"}',NULL,NULL,'Zócalos de colores','Ilustración de fachadas de colores vivos','Zócalos de colores. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,6,64,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:44','2026-10-27 11:17:44'),
(14,'image','demo/607bb88fcf047e5ff88db982.webp','image/webp','barichara.webp',28776,1100,1500,'{\"thumb\":\"demo\\/607bb88fcf047e5ff88db982-thumb.webp\",\"medium\":\"demo\\/607bb88fcf047e5ff88db982-medium.webp\",\"large\":\"demo\\/607bb88fcf047e5ff88db982-large.webp\"}',NULL,NULL,'Pueblo de piedra','Ilustración vertical de casas de tapia y teja','Pueblo de piedra. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,20,915,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:44','2026-10-27 11:17:44'),
(15,'image','demo/ea12449c75a4edf0d3abfb69.webp','image/webp','guajira.webp',21062,1600,977,'{\"thumb\":\"demo\\/ea12449c75a4edf0d3abfb69-thumb.webp\",\"medium\":\"demo\\/ea12449c75a4edf0d3abfb69-medium.webp\",\"large\":\"demo\\/ea12449c75a4edf0d3abfb69-large.webp\"}',NULL,NULL,'Dunas junto al mar','Ilustración de dunas naranjas que llegan al mar','Dunas junto al mar. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,13,704,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:45','2026-10-27 11:17:45'),
(16,'image','demo/149f65b443af00bc7a380608.webp','image/webp','amazonas.webp',28970,1600,977,'{\"thumb\":\"demo\\/149f65b443af00bc7a380608-thumb.webp\",\"medium\":\"demo\\/149f65b443af00bc7a380608-medium.webp\",\"large\":\"demo\\/149f65b443af00bc7a380608-large.webp\"}',NULL,NULL,'Río amazónico','Ilustración de río entre selva al atardecer','Río amazónico. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,2,5,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:46','2026-10-27 11:17:46'),
(17,'image','demo/3d4d5a66330927af82b209c8.webp','image/webp','amazonas-vertical.webp',25624,1100,1500,'{\"thumb\":\"demo\\/3d4d5a66330927af82b209c8-thumb.webp\",\"medium\":\"demo\\/3d4d5a66330927af82b209c8-medium.webp\",\"large\":\"demo\\/3d4d5a66330927af82b209c8-large.webp\"}',NULL,NULL,'Atardecer en la selva','Ilustración vertical de selva y río','Atardecer en la selva. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,2,9,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:46','2026-10-27 11:17:46'),
(18,'image','demo/97c7391e52650e23ce2a01aa.webp','image/webp','llanos.webp',26852,1600,977,'{\"thumb\":\"demo\\/97c7391e52650e23ce2a01aa-thumb.webp\",\"medium\":\"demo\\/97c7391e52650e23ce2a01aa-medium.webp\",\"large\":\"demo\\/97c7391e52650e23ce2a01aa-large.webp\"}',NULL,NULL,'Atardecer llanero','Ilustración de llanura con palmas de moriche al atardecer','Atardecer llanero. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,29,388,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:47','2026-10-27 11:17:47'),
(19,'image','demo/f06f116f820434e71057492e.webp','image/webp','pacifico.webp',12786,1600,977,'{\"thumb\":\"demo\\/f06f116f820434e71057492e-thumb.webp\",\"medium\":\"demo\\/f06f116f820434e71057492e-medium.webp\",\"large\":\"demo\\/f06f116f820434e71057492e-large.webp\"}',NULL,NULL,'Ballena en el Pacífico','Ilustración de cola de ballena frente a la selva','Ballena en el Pacífico. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,14,485,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:47','2026-10-27 11:17:47'),
(20,'image','demo/1efe69f5f9901566c5fc03ca.webp','image/webp','tatacoa.webp',19648,1600,1100,'{\"thumb\":\"demo\\/1efe69f5f9901566c5fc03ca-thumb.webp\",\"medium\":\"demo\\/1efe69f5f9901566c5fc03ca-medium.webp\",\"large\":\"demo\\/1efe69f5f9901566c5fc03ca-large.webp\"}',NULL,NULL,'Desierto rojo','Ilustración de formaciones rocosas rojizas con cactus','Desierto rojo. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,32,689,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:48','2026-10-27 11:17:48'),
(21,'image','demo/91665b6fef57c1e49939f9a9.webp','image/webp','tatacoa-noche.webp',14864,1600,1100,'{\"thumb\":\"demo\\/91665b6fef57c1e49939f9a9-thumb.webp\",\"medium\":\"demo\\/91665b6fef57c1e49939f9a9-medium.webp\",\"large\":\"demo\\/91665b6fef57c1e49939f9a9-large.webp\"}',NULL,NULL,'Noche estrellada en el desierto','Ilustración de cielo estrellado sobre el desierto','Noche estrellada en el desierto. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,32,689,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:49','2026-10-27 11:17:49'),
(22,'image','demo/db80fec5b94392d13a26b12f.webp','image/webp','medellin.webp',62670,1600,977,'{\"thumb\":\"demo\\/db80fec5b94392d13a26b12f-thumb.webp\",\"medium\":\"demo\\/db80fec5b94392d13a26b12f-medium.webp\",\"large\":\"demo\\/db80fec5b94392d13a26b12f-large.webp\"}',NULL,NULL,'Ciudad entre montañas','Ilustración de ciudad nocturna con cable aéreo','Ciudad entre montañas. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,6,79,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:49','2026-10-27 11:17:49'),
(23,'image','demo/8c96fac7a2a6e3cb13611713.webp','image/webp','cascada.webp',20810,1100,1500,'{\"thumb\":\"demo\\/8c96fac7a2a6e3cb13611713-thumb.webp\",\"medium\":\"demo\\/8c96fac7a2a6e3cb13611713-medium.webp\",\"large\":\"demo\\/8c96fac7a2a6e3cb13611713-large.webp\"}',NULL,NULL,'Cascada en la selva','Ilustración de cascada entre paredes de roca y selva','Cascada en la selva. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,22,871,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:50','2026-10-27 11:17:50'),
(24,'image','demo/47906b135c90aa8c1f4c8ca1.webp','image/webp','san-agustin.webp',14702,1600,1100,'{\"thumb\":\"demo\\/47906b135c90aa8c1f4c8ca1-thumb.webp\",\"medium\":\"demo\\/47906b135c90aa8c1f4c8ca1-medium.webp\",\"large\":\"demo\\/47906b135c90aa8c1f4c8ca1-large.webp\"}',NULL,NULL,'Estatuas de piedra','Ilustración de esculturas de piedra en un prado','Estatuas de piedra. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,32,681,4,'culture',1,0,1,NULL,'2026-10-27 11:17:50','2026-10-27 11:17:50'),
(25,'image','demo/a6a248b64762e1725b2df596.webp','image/webp','andes-amanecer.webp',19188,1600,977,'{\"thumb\":\"demo\\/a6a248b64762e1725b2df596-thumb.webp\",\"medium\":\"demo\\/a6a248b64762e1725b2df596-medium.webp\",\"large\":\"demo\\/a6a248b64762e1725b2df596-large.webp\"}',NULL,NULL,'Amanecer andino','Ilustración de cordilleras en capas al amanecer','Amanecer andino. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,23,808,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:51','2026-10-27 11:17:51'),
(26,'image','demo/16a8b44c753114b8b0bb5503.webp','image/webp','andes-violeta.webp',20214,1600,977,'{\"thumb\":\"demo\\/16a8b44c753114b8b0bb5503-thumb.webp\",\"medium\":\"demo\\/16a8b44c753114b8b0bb5503-medium.webp\",\"large\":\"demo\\/16a8b44c753114b8b0bb5503-large.webp\"}',NULL,NULL,'Cordillera violeta','Ilustración de montañas en tonos violeta','Cordillera violeta. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,26,167,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:51','2026-10-27 11:17:51'),
(27,'image','demo/e1204738cadfc3e3fccc19aa.webp','image/webp','nevado.webp',22454,1600,1100,'{\"thumb\":\"demo\\/e1204738cadfc3e3fccc19aa-thumb.webp\",\"medium\":\"demo\\/e1204738cadfc3e3fccc19aa-medium.webp\",\"large\":\"demo\\/e1204738cadfc3e3fccc19aa-large.webp\"}',NULL,NULL,'Cumbres nevadas','Ilustración de picos nevados y montañas azules','Cumbres nevadas. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,28,245,1,'tourism',1,0,1,NULL,'2026-10-27 11:17:52','2026-10-27 11:17:52'),
(28,'image','demo/45206016bb497dcf4ebf840e.webp','image/webp','paramo.webp',31700,1600,1100,'{\"thumb\":\"demo\\/45206016bb497dcf4ebf840e-thumb.webp\",\"medium\":\"demo\\/45206016bb497dcf4ebf840e-medium.webp\",\"large\":\"demo\\/45206016bb497dcf4ebf840e-large.webp\"}',NULL,NULL,'Páramo de frailejones','Ilustración de frailejones junto a una laguna de páramo','Páramo de frailejones. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,28,267,1,'tourism',1,1,1,NULL,'2026-10-27 11:17:52','2026-10-27 11:17:52'),
(29,'image','demo/9a00cd7e336adfa3907b4c6d.webp','image/webp','bandeja-paisa.webp',46310,1400,1400,'{\"thumb\":\"demo\\/9a00cd7e336adfa3907b4c6d-thumb.webp\",\"medium\":\"demo\\/9a00cd7e336adfa3907b4c6d-medium.webp\",\"large\":\"demo\\/9a00cd7e336adfa3907b4c6d-large.webp\"}',NULL,NULL,'Bandeja paisa','Ilustración cenital de bandeja paisa','Bandeja paisa. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,6,79,2,'gastronomy',1,1,1,NULL,'2026-10-27 11:17:53','2026-10-27 11:17:53'),
(30,'image','demo/b54b1c534007d9c8c696ea31.webp','image/webp','ajiaco.webp',42126,1400,1400,'{\"thumb\":\"demo\\/b54b1c534007d9c8c696ea31-thumb.webp\",\"medium\":\"demo\\/b54b1c534007d9c8c696ea31-medium.webp\",\"large\":\"demo\\/b54b1c534007d9c8c696ea31-large.webp\"}',NULL,NULL,'Ajiaco','Ilustración cenital de ajiaco con mazorca','Ajiaco. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,26,167,2,'gastronomy',1,0,1,NULL,'2026-10-27 11:17:54','2026-10-27 11:17:54'),
(31,'image','demo/a04dce211d6ed9bc73cd2dd5.webp','image/webp','arepas.webp',29056,1400,1100,'{\"thumb\":\"demo\\/a04dce211d6ed9bc73cd2dd5-thumb.webp\",\"medium\":\"demo\\/a04dce211d6ed9bc73cd2dd5-medium.webp\",\"large\":\"demo\\/a04dce211d6ed9bc73cd2dd5-large.webp\"}',NULL,NULL,'Arepas','Ilustración cenital de arepas doradas','Arepas. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,7,150,2,'gastronomy',1,0,1,NULL,'2026-10-27 11:17:55','2026-10-27 11:17:55'),
(32,'image','demo/ef9e38c830cef0029b34f190.webp','image/webp','sancocho.webp',40086,1400,1400,'{\"thumb\":\"demo\\/ef9e38c830cef0029b34f190-thumb.webp\",\"medium\":\"demo\\/ef9e38c830cef0029b34f190-medium.webp\",\"large\":\"demo\\/ef9e38c830cef0029b34f190-large.webp\"}',NULL,NULL,'Sancocho','Ilustración cenital de sancocho','Sancocho. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,15,1080,2,'gastronomy',1,0,1,NULL,'2026-10-27 11:17:55','2026-10-27 11:17:55'),
(33,'image','demo/fac3cdecc81bea125c71f37f.webp','image/webp','empanadas.webp',39076,1400,1100,'{\"thumb\":\"demo\\/fac3cdecc81bea125c71f37f-thumb.webp\",\"medium\":\"demo\\/fac3cdecc81bea125c71f37f-medium.webp\",\"large\":\"demo\\/fac3cdecc81bea125c71f37f-large.webp\"}',NULL,NULL,'Empanadas','Ilustración cenital de empanadas con ají','Empanadas. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,30,424,2,'gastronomy',1,1,1,NULL,'2026-10-27 11:17:56','2026-10-27 11:17:56'),
(34,'image','demo/314f2fabd6d7002976c41d39.webp','image/webp','frutas.webp',50618,1600,1100,'{\"thumb\":\"demo\\/314f2fabd6d7002976c41d39-thumb.webp\",\"medium\":\"demo\\/314f2fabd6d7002976c41d39-medium.webp\",\"large\":\"demo\\/314f2fabd6d7002976c41d39-large.webp\"}',NULL,NULL,'Frutas de Colombia','Ilustración de frutas tropicales partidas','Frutas de Colombia. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,NULL,NULL,2,'gastronomy',1,1,1,NULL,'2026-10-27 11:17:56','2026-10-27 11:17:56'),
(35,'image','demo/2147b809ef606c3e326d729f.webp','image/webp','cazuela.webp',36802,1400,1400,'{\"thumb\":\"demo\\/2147b809ef606c3e326d729f-thumb.webp\",\"medium\":\"demo\\/2147b809ef606c3e326d729f-medium.webp\",\"large\":\"demo\\/2147b809ef606c3e326d729f-large.webp\"}',NULL,NULL,'Cazuela de mariscos','Ilustración cenital de cazuela de mariscos','Cazuela de mariscos. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,8,176,2,'gastronomy',1,0,1,NULL,'2026-10-27 11:17:57','2026-10-27 11:17:57'),
(36,'image','demo/bcd4993cc14dbfce35f91ef5.webp','image/webp','tamal.webp',39984,1400,1100,'{\"thumb\":\"demo\\/bcd4993cc14dbfce35f91ef5-thumb.webp\",\"medium\":\"demo\\/bcd4993cc14dbfce35f91ef5-medium.webp\",\"large\":\"demo\\/bcd4993cc14dbfce35f91ef5-large.webp\"}',NULL,NULL,'Tamal','Ilustración cenital de tamal en hoja de plátano','Tamal. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,5,1045,2,'gastronomy',1,0,1,NULL,'2026-10-27 11:17:58','2026-10-27 11:17:58'),
(37,'image','demo/1a7a0110621307670471f33a.webp','image/webp','jugos.webp',18930,1100,1400,'{\"thumb\":\"demo\\/1a7a0110621307670471f33a-thumb.webp\",\"medium\":\"demo\\/1a7a0110621307670471f33a-medium.webp\",\"large\":\"demo\\/1a7a0110621307670471f33a-large.webp\"}',NULL,NULL,'Jugos naturales','Ilustración de tres vasos de jugo de frutas','Jugos naturales. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,15,1080,2,'gastronomy',1,0,1,NULL,'2026-10-27 11:17:59','2026-10-27 11:17:59'),
(38,'image','demo/f76bba5b32c405f648895363.webp','image/webp','taza-cafe.webp',36826,1400,1400,'{\"thumb\":\"demo\\/f76bba5b32c405f648895363-thumb.webp\",\"medium\":\"demo\\/f76bba5b32c405f648895363-medium.webp\",\"large\":\"demo\\/f76bba5b32c405f648895363-large.webp\"}',NULL,NULL,'Taza de café','Ilustración cenital de taza de café rodeada de granos','Taza de café. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,NULL,NULL,3,'coffee',1,1,1,NULL,'2026-10-27 11:17:59','2026-10-27 11:17:59'),
(39,'image','demo/e96cf66cd31c7023205d95ea.webp','image/webp','cerezas-cafe.webp',35064,1600,1100,'{\"thumb\":\"demo\\/e96cf66cd31c7023205d95ea-thumb.webp\",\"medium\":\"demo\\/e96cf66cd31c7023205d95ea-medium.webp\",\"large\":\"demo\\/e96cf66cd31c7023205d95ea-large.webp\"}',NULL,NULL,'Cerezas de café','Ilustración de rama con cerezas de café rojas y amarillas','Cerezas de café. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,17,894,3,'coffee',1,0,1,NULL,'2026-10-27 11:18:00','2026-10-27 11:18:00'),
(40,'image','demo/a489862fe1ed0db33f520507.webp','image/webp','granos-cafe.webp',108320,1600,1100,'{\"thumb\":\"demo\\/a489862fe1ed0db33f520507-thumb.webp\",\"medium\":\"demo\\/a489862fe1ed0db33f520507-medium.webp\",\"large\":\"demo\\/a489862fe1ed0db33f520507-large.webp\"}',NULL,NULL,'Granos al sol','Ilustración de granos de café secándose en un patio','Granos al sol. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,1,341,3,'coffee',1,0,1,NULL,'2026-10-27 11:18:00','2026-10-27 11:18:00'),
(41,'image','demo/c71e25cdad741ace13bcbd15.webp','image/webp','metodo-filtrado.webp',12080,1100,1400,'{\"thumb\":\"demo\\/c71e25cdad741ace13bcbd15-thumb.webp\",\"medium\":\"demo\\/c71e25cdad741ace13bcbd15-medium.webp\",\"large\":\"demo\\/c71e25cdad741ace13bcbd15-large.webp\"}',NULL,NULL,'Café filtrado','Ilustración de un método de filtrado de café','Café filtrado. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,NULL,NULL,3,'coffee',1,0,1,NULL,'2026-10-27 11:18:01','2026-10-27 11:18:01'),
(42,'image','demo/cce989c7ad398ef40aa40a10.webp','image/webp','mochila-wayuu.webp',139368,1400,1400,'{\"thumb\":\"demo\\/cce989c7ad398ef40aa40a10-thumb.webp\",\"medium\":\"demo\\/cce989c7ad398ef40aa40a10-medium.webp\",\"large\":\"demo\\/cce989c7ad398ef40aa40a10-large.webp\"}',NULL,NULL,'Tejido geométrico','Ilustración de patrones geométricos de colores inspirados en el tejido','Tejido geométrico. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,13,704,4,'culture',1,1,1,NULL,'2026-10-27 11:18:02','2026-10-27 11:18:02'),
(43,'image','demo/fcc3b5f2360575df7dd5b80e.webp','image/webp','mola.webp',54360,1600,1100,'{\"thumb\":\"demo\\/fcc3b5f2360575df7dd5b80e-thumb.webp\",\"medium\":\"demo\\/fcc3b5f2360575df7dd5b80e-medium.webp\",\"large\":\"demo\\/fcc3b5f2360575df7dd5b80e-large.webp\"}',NULL,NULL,'Capas de color','Ilustración de capas de color superpuestas inspiradas en el arte textil','Capas de color. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,6,85,4,'culture',1,0,1,NULL,'2026-10-27 11:18:03','2026-10-27 11:18:03'),
(44,'image','demo/a02bc9b28868a58ae0daf94e.webp','image/webp','sombrero-vueltiao.webp',126396,1400,1400,'{\"thumb\":\"demo\\/a02bc9b28868a58ae0daf94e-thumb.webp\",\"medium\":\"demo\\/a02bc9b28868a58ae0daf94e-medium.webp\",\"large\":\"demo\\/a02bc9b28868a58ae0daf94e-large.webp\"}',NULL,NULL,'Trenzado en espiral','Ilustración cenital de un sombrero con trenzas blancas y negras','Trenzado en espiral. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,12,524,4,'culture',1,1,1,NULL,'2026-10-27 11:18:04','2026-10-27 11:18:04'),
(45,'image','demo/ae52cc4b42d933124e3b5031.webp','image/webp','silleta-flores.webp',77072,1600,1100,'{\"thumb\":\"demo\\/ae52cc4b42d933124e3b5031-thumb.webp\",\"medium\":\"demo\\/ae52cc4b42d933124e3b5031-medium.webp\",\"large\":\"demo\\/ae52cc4b42d933124e3b5031-large.webp\"}',NULL,NULL,'Flores de feria','Ilustración de flores de colores','Flores de feria. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,6,79,4,'culture',1,0,1,NULL,'2026-10-27 11:18:04','2026-10-27 11:18:04'),
(46,'image','demo/9f4d270a8cc1df61db7c6512.webp','image/webp','carnaval.webp',66672,1600,1100,'{\"thumb\":\"demo\\/9f4d270a8cc1df61db7c6512-thumb.webp\",\"medium\":\"demo\\/9f4d270a8cc1df61db7c6512-medium.webp\",\"large\":\"demo\\/9f4d270a8cc1df61db7c6512-large.webp\"}',NULL,NULL,'Abanico de carnaval','Ilustración de abanico de colores y confeti','Abanico de carnaval. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,7,145,4,'culture',1,1,1,NULL,'2026-10-27 11:18:05','2026-10-27 11:18:05'),
(47,'image','demo/a377e94034067d8f446b1cbd.webp','image/webp','musica.webp',48532,1600,1100,'{\"thumb\":\"demo\\/a377e94034067d8f446b1cbd-thumb.webp\",\"medium\":\"demo\\/a377e94034067d8f446b1cbd-medium.webp\",\"large\":\"demo\\/a377e94034067d8f446b1cbd-large.webp\"}',NULL,NULL,'Tambores y maracas','Ilustración de tambor, maracas y gaita','Tambores y maracas. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,9,465,4,'culture',1,0,1,NULL,'2026-10-27 11:18:06','2026-10-27 11:18:06'),
(48,'image','demo/1e88d0e59c12a4134ed45b1f.webp','image/webp','pollera.webp',77930,1400,1400,'{\"thumb\":\"demo\\/1e88d0e59c12a4134ed45b1f-thumb.webp\",\"medium\":\"demo\\/1e88d0e59c12a4134ed45b1f-medium.webp\",\"large\":\"demo\\/1e88d0e59c12a4134ed45b1f-large.webp\"}',NULL,NULL,'Pollera en movimiento','Ilustración abstracta de una falda de cumbia girando','Pollera en movimiento. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,10,714,4,'culture',1,0,1,NULL,'2026-10-27 11:18:06','2026-10-27 11:18:06'),
(49,'image','demo/49fa24c139ae658c0e218b91.webp','image/webp','ceramica.webp',42414,1600,1100,'{\"thumb\":\"demo\\/49fa24c139ae658c0e218b91-thumb.webp\",\"medium\":\"demo\\/49fa24c139ae658c0e218b91-medium.webp\",\"large\":\"demo\\/49fa24c139ae658c0e218b91-large.webp\"}',NULL,NULL,'Taller de cerámica','Ilustración de vasijas de barro en estanterías','Taller de cerámica. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,28,289,5,'entrepreneurship',1,1,1,NULL,'2026-10-27 11:18:07','2026-10-27 11:18:07'),
(50,'image','demo/035596765349309be5726bee.webp','image/webp','tejidos.webp',215614,1600,1100,'{\"thumb\":\"demo\\/035596765349309be5726bee-thumb.webp\",\"medium\":\"demo\\/035596765349309be5726bee-medium.webp\",\"large\":\"demo\\/035596765349309be5726bee-large.webp\"}',NULL,NULL,'Telares','Ilustración de tejidos colgados de colores','Telares. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,30,432,5,'entrepreneurship',1,0,1,NULL,'2026-10-27 11:18:08','2026-10-27 11:18:08'),
(51,'image','demo/7330d9ba6844b57c9d04b55e.webp','image/webp','mercado.webp',33210,1600,1100,'{\"thumb\":\"demo\\/7330d9ba6844b57c9d04b55e-thumb.webp\",\"medium\":\"demo\\/7330d9ba6844b57c9d04b55e-medium.webp\",\"large\":\"demo\\/7330d9ba6844b57c9d04b55e-large.webp\"}',NULL,NULL,'Mercado campesino','Ilustración de puesto de mercado con frutas','Mercado campesino. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,28,334,5,'entrepreneurship',1,0,1,NULL,'2026-10-27 11:18:08','2026-10-27 11:18:08'),
(52,'image','demo/715dac453ced06a8d54ade95.webp','image/webp','miel.webp',88290,1400,1400,'{\"thumb\":\"demo\\/715dac453ced06a8d54ade95-thumb.webp\",\"medium\":\"demo\\/715dac453ced06a8d54ade95-medium.webp\",\"large\":\"demo\\/715dac453ced06a8d54ade95-large.webp\"}',NULL,NULL,'Miel de abejas','Ilustración de frascos de miel sobre panal','Miel de abejas. Imagen ilustrativa de demostración.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,20,915,5,'entrepreneurship',1,0,1,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(53,'video','demo/2ba4821328c9eba6f50309b6.mp4','video/mp4','video-demo-cano-cristales.mp4',243378,NULL,NULL,NULL,NULL,NULL,'Recorrido por Caño Cristales (demo)',NULL,'Pieza de demostración para probar el reproductor.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,3,749,NULL,NULL,1,0,1,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(54,'audio','demo/b8d9da249bc01dae632964e4.mp3','audio/mpeg','audio-demo-ambiente.mp3',120730,NULL,NULL,NULL,NULL,NULL,'Sonidos del río (audio de demostración)',NULL,'Pieza de demostración para probar el reproductor.','Ilustración de demostración · Voy por Colombia','Demo: reemplazar por fotografía con derechos',NULL,3,749,NULL,NULL,0,0,1,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09');
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'2026_11_04_000001_create_content_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `municipalities`
--

DROP TABLE IF EXISTS `municipalities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `municipalities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `lat` decimal(10,6) DEFAULT NULL,
  `lng` decimal(10,6) DEFAULT NULL,
  `search_name` varchar(140) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `municipalities_department_id_slug_unique` (`department_id`,`slug`),
  KEY `municipalities_search_name_index` (`search_name`),
  CONSTRAINT `municipalities_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `municipalities`
--

LOCK TABLES `municipalities` WRITE;
/*!40000 ALTER TABLE `municipalities` DISABLE KEYS */;
INSERT INTO `municipalities` VALUES
(1,2,'El Encanto','el-encanto',-2.004980,-72.737170,'el encanto'),
(2,2,'La Chorrera','la-chorrera',-0.880340,-73.134070,'la chorrera'),
(3,2,'La Pedrera','la-pedrera',-1.553580,-70.038550,'la pedrera'),
(4,2,'La Victoria (Pacoa)','la-victoria-pacoa',-0.127320,-71.125500,'la victoria pacoa'),
(5,2,'Leticia','leticia',-3.528830,-70.082790,'leticia'),
(6,2,'Mirití-paraná (Campoamor)','miriti-parana-campoamor',-0.675270,-71.124920,'miriti parana campoamor'),
(7,2,'Puerto Alegría','puerto-alegria',-0.931700,-73.746530,'puerto alegria'),
(8,2,'Puerto Arica','puerto-arica',-1.815410,-71.012880,'puerto arica'),
(9,2,'Puerto Nariño','puerto-narino',-3.665340,-70.486110,'puerto narino'),
(10,2,'Santander (Araracuara)','santander-araracuara',-1.215200,-71.830390,'santander araracuara'),
(11,2,'Tarapacá','tarapaca',-2.338110,-69.967610,'tarapaca'),
(12,6,'Abejorral','abejorral',5.814500,-75.436300,'abejorral'),
(13,6,'Abriaquí','abriaqui',6.621770,-76.091940,'abriaqui'),
(14,6,'Alejandría','alejandria',6.361450,-75.107810,'alejandria'),
(15,6,'Amagá','amaga',6.022580,-75.706970,'amaga'),
(16,6,'Amalfi','amalfi',6.986110,-74.964320,'amalfi'),
(17,6,'Andes','andes',5.628360,-75.944550,'andes'),
(18,6,'Angelópolis','angelopolis',6.115580,-75.716730,'angelopolis'),
(19,6,'Angostura','angostura',6.863360,-75.337610,'angostura'),
(20,6,'Anorí','anori',7.153780,-75.140810,'anori'),
(21,6,'Anzá','anza',6.296590,-75.910920,'anza'),
(22,6,'Apartadó','apartado',7.868050,-76.583640,'apartado'),
(23,6,'Arboletes','arboletes',8.589150,-76.425810,'arboletes'),
(24,6,'Argelia','argelia',5.711220,-75.112620,'argelia'),
(25,6,'Armenia','armenia',6.156210,-75.805920,'armenia'),
(26,6,'Barbosa','barbosa',6.449700,-75.319630,'barbosa'),
(27,6,'Bello','bello',6.356830,-75.588640,'bello'),
(28,6,'Belmira','belmira',6.698340,-75.668640,'belmira'),
(29,6,'Betania','betania',5.733820,-75.986880,'betania'),
(30,6,'Betulia','betulia',6.170410,-75.956610,'betulia'),
(31,6,'Briceño','briceno',7.115500,-75.564080,'briceno'),
(32,6,'Buriticá','buritica',6.805320,-75.917290,'buritica'),
(33,6,'Caicedo','caicedo',6.424680,-75.992160,'caicedo'),
(34,6,'Caldas','caldas',6.052620,-75.627540,'caldas'),
(35,6,'Campamento','campamento',7.009370,-75.274610,'campamento'),
(36,6,'Caracolí','caracoli',6.330090,-74.733540,'caracoli'),
(37,6,'Caramanta','caramanta',5.552700,-75.630860,'caramanta'),
(38,6,'Carepa','carepa',7.765330,-76.701750,'carepa'),
(39,6,'Carolina','carolina',6.754650,-75.305890,'carolina'),
(40,6,'Caucasia','caucasia',7.895790,-75.053680,'caucasia'),
(41,6,'Cañasgordas','canasgordas',6.832400,-76.040450,'canasgordas'),
(42,6,'Chigorodó','chigorodo',7.623640,-76.625600,'chigorodo'),
(43,6,'Cisneros','cisneros',6.554810,-75.074630,'cisneros'),
(44,6,'Ciudad Bolívar','ciudad-bolivar',5.836480,-76.018320,'ciudad bolivar'),
(45,6,'Cocorná','cocorna',6.021760,-75.158530,'cocorna'),
(46,6,'Concepción','concepcion',6.377460,-75.211830,'concepcion'),
(47,6,'Concordia','concordia',6.075090,-75.908290,'concordia'),
(48,6,'Copacabana','copacabana',6.338710,-75.496580,'copacabana'),
(49,6,'Cáceres','caceres',7.668160,-75.227560,'caceres'),
(50,6,'Dabeiba','dabeiba',7.012630,-76.373970,'dabeiba'),
(51,6,'Don Matías','don-matias',6.485320,-75.414190,'don matias'),
(52,6,'Ebéjico','ebejico',6.328890,-75.781890,'ebejico'),
(53,6,'El Bagre','el-bagre',7.765340,-74.647070,'el bagre'),
(54,6,'El Carmen de Viboral','el-carmen-de-viboral',5.968090,-75.240480,'el carmen de viboral'),
(55,6,'Entrerrios','entrerrios',6.593100,-75.560730,'entrerrios'),
(56,6,'Envigado','envigado',6.147760,-75.564350,'envigado'),
(57,6,'Fredonia','fredonia',5.882780,-75.680460,'fredonia'),
(58,6,'Frontino','frontino',6.679770,-76.335800,'frontino'),
(59,6,'Giraldo','giraldo',6.667100,-75.953580,'giraldo'),
(60,6,'Girardota','girardota',6.372430,-75.442490,'girardota'),
(61,6,'Granada','granada',6.127660,-75.126260,'granada'),
(62,6,'Guadalupe','guadalupe',6.865530,-75.220070,'guadalupe'),
(63,6,'Guarne','guarne',6.268430,-75.438410,'guarne'),
(64,6,'Guatapé','guatape',6.232890,-75.162450,'guatape'),
(65,6,'Gómez Plata','gomez-plata',6.712500,-75.191220,'gomez plata'),
(66,6,'Heliconia','heliconia',6.219210,-75.738320,'heliconia'),
(67,6,'Hispania','hispania',5.793220,-75.903210,'hispania'),
(68,6,'Itagüí','itagui',6.179800,-75.614660,'itagui'),
(69,6,'Ituango','ituango',7.255590,-75.740550,'ituango'),
(70,6,'Jardín','jardin',5.574600,-75.819730,'jardin'),
(71,6,'Jericó','jerico',5.776960,-75.766250,'jerico'),
(72,6,'La Ceja','la-ceja',5.972620,-75.449360,'la ceja'),
(73,6,'La Estrella','la-estrella',6.137840,-75.653310,'la estrella'),
(74,6,'La Pintada','la-pintada',5.757380,-75.596680,'la pintada'),
(75,6,'La Unión','la-union',5.933440,-75.362360,'la union'),
(76,6,'Liborina','liborina',6.726200,-75.782950,'liborina'),
(77,6,'Maceo','maceo',6.539060,-74.691000,'maceo'),
(78,6,'Marinilla','marinilla',6.189440,-75.294090,'marinilla'),
(79,6,'Medellín','medellin',6.254920,-75.625760,'medellin'),
(80,6,'Montebello','montebello',5.914460,-75.516070,'montebello'),
(81,6,'Murindó','murindo',6.834570,-76.684260,'murindo'),
(82,6,'Mutatá','mutata',7.326380,-76.462770,'mutata'),
(83,6,'Nariño','narino',5.587130,-75.187130,'narino'),
(84,6,'Nechí','nechi',8.022720,-74.724680,'nechi'),
(85,6,'Necoclí','necocli',8.506340,-76.677100,'necocli'),
(86,6,'Olaya','olaya',6.627570,-75.780180,'olaya'),
(87,6,'Peque','peque',7.039900,-75.905280,'peque'),
(88,6,'Peñol','penol',6.234600,-75.227620,'penol'),
(89,6,'Pueblorrico','pueblorrico',5.791540,-75.846850,'pueblorrico'),
(90,6,'Puerto Berrío','puerto-berrio',6.470340,-74.525740,'puerto berrio'),
(91,6,'Puerto Nare','puerto-nare',6.126220,-74.706190,'puerto nare'),
(92,6,'Puerto Triunfo','puerto-triunfo',5.942710,-74.674280,'puerto triunfo'),
(93,6,'Remedios','remedios',7.041360,-74.528260,'remedios'),
(94,6,'Retiro','retiro',6.055900,-75.514350,'retiro'),
(95,6,'Rionegro','rionegro',6.153720,-75.412110,'rionegro'),
(96,6,'Sabanalarga','sabanalarga',6.905660,-75.785520,'sabanalarga'),
(97,6,'Sabaneta','sabaneta',6.142270,-75.612550,'sabaneta'),
(98,6,'Salgar','salgar',5.978770,-76.000170,'salgar'),
(99,6,'San Andrès','san-andres',6.934620,-75.648520,'san andres'),
(100,6,'San Carlos','san-carlos',6.178520,-74.910260,'san carlos'),
(101,6,'San Francisco','san-francisco',5.865550,-74.966680,'san francisco'),
(102,6,'San Jerónimo','san-jeronimo',6.435370,-75.708670,'san jeronimo'),
(103,6,'San José de la Montaña','san-jose-de-la-montana',6.821560,-75.678870,'san jose de la montana'),
(104,6,'San Juan de Urabá','san-juan-de-uraba',8.706780,-76.540820,'san juan de uraba'),
(105,6,'San Luis','san-luis',6.021430,-74.999260,'san luis'),
(106,6,'San Pedro','san-pedro',6.460950,-75.544920,'san pedro'),
(107,6,'San Pedro de Uraba','san-pedro-de-uraba',8.387610,-76.316600,'san pedro de uraba'),
(108,6,'San Rafael','san-rafael',6.311840,-75.001070,'san rafael'),
(109,6,'San Roque','san-roque',6.465200,-74.959040,'san roque'),
(110,6,'San Vicente','san-vicente',6.309030,-75.329110,'san vicente'),
(111,6,'Santa Bárbara','santa-barbara',5.856490,-75.578080,'santa barbara'),
(112,6,'Santa Fe de Antioquia','santa-fe-de-antioquia',6.538080,-75.901910,'santa fe de antioquia'),
(113,6,'Santa Rosa de Osos','santa-rosa-de-osos',6.678020,-75.438260,'santa rosa de osos'),
(114,6,'Santo Domingo','santo-domingo',6.487110,-75.161130,'santo domingo'),
(115,6,'Santuario','santuario',6.130230,-75.259250,'santuario'),
(116,6,'Segovia','segovia',7.268230,-74.601260,'segovia'),
(117,6,'Sonsón','sonson',5.820820,-75.091790,'sonson'),
(118,6,'Sopetrán','sopetran',6.538660,-75.714550,'sopetran'),
(119,6,'Tarazá','taraza',7.482900,-75.423300,'taraza'),
(120,6,'Tarso','tarso',5.866470,-75.830020,'tarso'),
(121,6,'Titiribí','titiribi',6.055050,-75.800980,'titiribi'),
(122,6,'Toledo','toledo',7.030760,-75.712840,'toledo'),
(123,6,'Turbo','turbo',8.153730,-76.576290,'turbo'),
(124,6,'Támesis','tamesis',5.670700,-75.715420,'tamesis'),
(125,6,'Uramita','uramita',6.907530,-76.154350,'uramita'),
(126,6,'Urrao','urrao',6.362030,-76.288730,'urrao'),
(127,6,'Valdivia','valdivia',7.295090,-75.377760,'valdivia'),
(128,6,'Valparaíso','valparaiso',5.636040,-75.622980,'valparaiso'),
(129,6,'Vegachí','vegachi',6.838230,-74.754020,'vegachi'),
(130,6,'Venecia','venecia',5.958770,-75.784330,'venecia'),
(131,6,'Vigia del Fuerte','vigia-del-fuerte',6.444090,-76.654970,'vigia del fuerte'),
(132,6,'Yali','yali',6.723790,-74.752100,'yali'),
(133,6,'Yarumal','yarumal',6.985540,-75.436890,'yarumal'),
(134,6,'Yolombó','yolombo',6.666400,-75.035700,'yolombo'),
(135,6,'Yondó (Casabe)','yondo-casabe',6.927460,-74.153640,'yondo casabe'),
(136,6,'Zaragoza','zaragoza',7.504050,-74.858080,'zaragoza'),
(137,27,'Arauca','arauca',6.828720,-70.690910,'arauca'),
(138,27,'Arauquita','arauquita',6.829030,-71.373420,'arauquita'),
(139,27,'Cravo Norte','cravo-norte',6.347600,-69.951800,'cravo norte'),
(140,27,'Fortul','fortul',6.765800,-71.683560,'fortul'),
(141,27,'Puerto Rondón','puerto-rondon',6.398120,-70.966220,'puerto rondon'),
(142,27,'Saravena','saravena',6.909070,-71.847420,'saravena'),
(143,27,'Tame','tame',6.410110,-71.802930,'tame'),
(144,7,'Baranoa','baranoa',10.808020,-74.921140,'baranoa'),
(145,7,'Barranquilla','barranquilla',10.973220,-74.815290,'barranquilla'),
(146,7,'Campo de la Cruz','campo-de-la-cruz',10.381530,-74.891330,'campo de la cruz'),
(147,7,'Candelaria','candelaria',10.493380,-74.872960,'candelaria'),
(148,7,'Galapa','galapa',10.898440,-74.885780,'galapa'),
(149,7,'Juan de Acosta','juan-de-acosta',10.827650,-75.070540,'juan de acosta'),
(150,7,'Luruaco','luruaco',10.634130,-75.161700,'luruaco'),
(151,7,'Malambo','malambo',10.844830,-74.813370,'malambo'),
(152,7,'Manatí','manati',10.448480,-74.984750,'manati'),
(153,7,'Palmar de Varela','palmar-de-varela',10.692740,-74.765370,'palmar de varela'),
(154,7,'Piojó','piojo',10.744940,-75.152580,'piojo'),
(155,7,'Polonuevo','polonuevo',10.767820,-74.854990,'polonuevo'),
(156,7,'Ponedera','ponedera',10.584650,-74.791000,'ponedera'),
(157,7,'Puerto Colombia','puerto-colombia',11.000840,-74.913990,'puerto colombia'),
(158,7,'Repelón','repelon',10.508160,-75.113060,'repelon'),
(159,7,'Sabanagrande','sabanagrande',10.800540,-74.765000,'sabanagrande'),
(160,7,'Sabanalarga','sabanalarga',10.616320,-74.941840,'sabanalarga'),
(161,7,'Santa Lucía','santa-lucia',10.354430,-74.959700,'santa lucia'),
(162,7,'Santo Tomás','santo-tomas',10.751330,-74.804330,'santo tomas'),
(163,7,'Soledad','soledad',10.909990,-74.778220,'soledad'),
(164,7,'Suan','suan',10.305830,-74.913180,'suan'),
(165,7,'Tubará','tubara',10.906810,-74.984200,'tubara'),
(166,7,'Usiacurí','usiacuri',10.742630,-74.977800,'usiacuri'),
(167,26,'Bogotá D.C.','bogota-d-c',4.604470,-74.100370,'bogota d c'),
(168,8,'Achí','achi',8.630010,-74.475850,'achi'),
(169,8,'Altos del Rosario','altos-del-rosario',8.761660,-74.193310,'altos del rosario'),
(170,8,'Arenal','arenal',8.362590,-74.107020,'arenal'),
(171,8,'Arjona','arjona',10.164960,-75.355850,'arjona'),
(172,8,'Arroyohondo','arroyohondo',10.244990,-75.006050,'arroyohondo'),
(173,8,'Barranco de Loba','barranco-de-loba',8.887600,-74.178710,'barranco de loba'),
(174,8,'Calamar','calamar',10.153630,-75.002520,'calamar'),
(175,8,'Cantagallo','cantagallo',7.218060,-74.113760,'cantagallo'),
(176,8,'Cartagena de Indias','cartagena-de-indias',10.457920,-75.455730,'cartagena de indias'),
(177,8,'Cicuco','cicuco',9.237440,-74.692840,'cicuco'),
(178,8,'Clemencia','clemencia',10.564890,-75.325830,'clemencia'),
(179,8,'Córdoba','cordoba',9.540630,-74.896980,'cordoba'),
(180,8,'El Carmen de Bolívar','el-carmen-de-bolivar',9.669720,-75.082360,'el carmen de bolivar'),
(181,8,'El Guamo','el-guamo',10.039790,-74.930760,'el guamo'),
(182,8,'El Peñon','el-penon',8.846580,-73.899320,'el penon'),
(183,8,'Hatillo de Loba','hatillo-de-loba',8.986280,-74.072030,'hatillo de loba'),
(184,8,'Magangué','magangue',9.300150,-74.825040,'magangue'),
(185,8,'Mahates','mahates',10.182770,-75.181860,'mahates'),
(186,8,'Margarita','margarita',9.042710,-74.214840,'margarita'),
(187,8,'Maria la Baja','maria-la-baja',9.976230,-75.341160,'maria la baja'),
(188,8,'Mompós','mompos',9.130430,-74.542180,'mompos'),
(189,8,'Montecristo','montecristo',8.074070,-74.445620,'montecristo'),
(190,8,'Morales','morales',8.279050,-73.971380,'morales'),
(191,8,'Norosi','norosi',8.533090,-74.089410,'norosi'),
(192,8,'Pinillos','pinillos',8.915960,-74.415830,'pinillos'),
(193,8,'Regidor','regidor',8.699150,-73.842930,'regidor'),
(194,8,'Rioviejo','rioviejo',8.550490,-73.887850,'rioviejo'),
(195,8,'San Cristóbal','san-cristobal',10.371280,-75.073760,'san cristobal'),
(196,8,'San Estanislao','san-estanislao',10.352310,-75.204000,'san estanislao'),
(197,8,'San Fernando','san-fernando',9.074670,-74.369320,'san fernando'),
(198,8,'San Jacinto','san-jacinto',9.823370,-75.106060,'san jacinto'),
(199,8,'San Jacinto del Cauca','san-jacinto-del-cauca',8.213790,-74.739900,'san jacinto del cauca'),
(200,8,'San Juan Nepomuceno','san-juan-nepomuceno',9.994450,-75.127940,'san juan nepomuceno'),
(201,8,'San Martín de Loba','san-martin-de-loba',8.753180,-73.997220,'san martin de loba'),
(202,8,'San Pablo','san-pablo',7.498830,-74.054800,'san pablo'),
(203,8,'Santa Catalina','santa-catalina',10.623590,-75.269740,'santa catalina'),
(204,8,'Santa Rosa','santa-rosa',10.475970,-75.359750,'santa rosa'),
(205,8,'Santa Rosa del Sur','santa-rosa-del-sur',7.796120,-74.262650,'santa rosa del sur'),
(206,8,'Simití','simiti',7.822540,-73.932290,'simiti'),
(207,8,'Soplaviento','soplaviento',10.314060,-75.117930,'soplaviento'),
(208,8,'Talaigua Nuevo','talaigua-nuevo',9.327400,-74.647870,'talaigua nuevo'),
(209,8,'Tiquiso','tiquiso',8.532310,-74.268320,'tiquiso'),
(210,8,'Turbaco','turbaco',10.354100,-75.379690,'turbaco'),
(211,8,'Turbana','turbana',10.246710,-75.444070,'turbana'),
(212,8,'Villanueva','villanueva',10.453190,-75.266280,'villanueva'),
(213,8,'Zambrano','zambrano',9.739180,-74.882480,'zambrano'),
(214,28,'Almeida','almeida',4.953110,-73.383500,'almeida'),
(215,28,'Aquitania','aquitania',5.454230,-72.874560,'aquitania'),
(216,28,'Arcabuco','arcabuco',5.765270,-73.466150,'arcabuco'),
(217,28,'Belén','belen',6.004930,-72.907300,'belen'),
(218,28,'Berbeo','berbeo',5.237240,-73.119460,'berbeo'),
(219,28,'Betéitiva','beteitiva',5.914190,-72.843550,'beteitiva'),
(220,28,'Boavita','boavita',6.337230,-72.615260,'boavita'),
(221,28,'Boyacá','boyaca',5.439280,-73.381480,'boyaca'),
(222,28,'Briceño','briceno',5.676580,-73.921720,'briceno'),
(223,28,'Buenavista','buenavista',5.491470,-73.949510,'buenavista'),
(224,28,'Busbanzá','busbanza',5.843210,-72.875240,'busbanza'),
(225,28,'Caldas','caldas',5.574700,-73.880560,'caldas'),
(226,28,'Campohermoso','campohermoso',5.008810,-73.144720,'campohermoso'),
(227,28,'Cerinza','cerinza',5.960880,-72.971840,'cerinza'),
(228,28,'Chinavita','chinavita',5.195110,-73.339440,'chinavita'),
(229,28,'Chiquinquirá','chiquinquira',5.608920,-73.811510,'chiquinquira'),
(230,28,'Chiscas','chiscas',6.722600,-72.402670,'chiscas'),
(231,28,'Chita','chita',6.062270,-72.450610,'chita'),
(232,28,'Chitaraque','chitaraque',5.986330,-73.421530,'chitaraque'),
(233,28,'Chivatá','chivata',5.553940,-73.279780,'chivata'),
(234,28,'Chivor','chivor',4.864770,-73.356250,'chivor'),
(235,28,'Chíquiza','chiquiza',5.643870,-73.450210,'chiquiza'),
(236,28,'Ciénega','cienega',5.388790,-73.285760,'cienega'),
(237,28,'Combita','combita',5.664730,-73.313270,'combita'),
(238,28,'Coper','coper',5.444280,-74.047780,'coper'),
(239,28,'Corrales','corrales',5.847120,-72.828830,'corrales'),
(240,28,'Covarachia','covarachia',6.522040,-72.741250,'covarachia'),
(241,28,'Cubará','cubara',6.872180,-72.185760,'cubara'),
(242,28,'Cucaita','cucaita',5.534230,-73.446660,'cucaita'),
(243,28,'Cuìtiva','cuitiva',5.589930,-72.929850,'cuitiva'),
(244,28,'Duitama','duitama',5.893550,-73.065700,'duitama'),
(245,28,'El Cocuy','el-cocuy',6.355590,-72.420740,'el cocuy'),
(246,28,'El Espino','el-espino',6.510040,-72.481280,'el espino'),
(247,28,'Firavitoba','firavitoba',5.681320,-73.007010,'firavitoba'),
(248,28,'Floresta','floresta',5.859260,-72.930040,'floresta'),
(249,28,'Gachantivá','gachantiva',5.746120,-73.541720,'gachantiva'),
(250,28,'Gameza','gameza',5.799780,-72.719530,'gameza'),
(251,28,'Garagoa','garagoa',5.096650,-73.315870,'garagoa'),
(252,28,'Guacamayas','guacamayas',6.446220,-72.513090,'guacamayas'),
(253,28,'Guateque','guateque',5.012930,-73.490650,'guateque'),
(254,28,'Guayatá','guayata',4.929040,-73.503250,'guayata'),
(255,28,'Güicán','guican',6.568450,-72.253340,'guican'),
(256,28,'Iza','iza',5.617210,-72.972720,'iza'),
(257,28,'Jenesano','jenesano',5.382610,-73.386150,'jenesano'),
(258,28,'Jericó','jerico',6.156980,-72.597660,'jerico'),
(259,28,'La Capilla','la-capilla',5.114370,-73.458550,'la capilla'),
(260,28,'La Uvita','la-uvita',6.265450,-72.545000,'la uvita'),
(261,28,'La Victoria','la-victoria',5.502660,-74.235870,'la victoria'),
(262,28,'Labranza Grande','labranza-grande',5.523030,-72.567620,'labranza grande'),
(263,28,'Macanal','macanal',4.973710,-73.295350,'macanal'),
(264,28,'Maripí','maripi',5.561570,-74.027760,'maripi'),
(265,28,'Miraflores','miraflores',5.156080,-73.168710,'miraflores'),
(266,28,'Mongua','mongua',5.724870,-72.666110,'mongua'),
(267,28,'Monguí','mongui',5.698100,-72.829970,'mongui'),
(268,28,'Moniquira','moniquira',5.861460,-73.557270,'moniquira'),
(269,28,'Motavita','motavita',5.604880,-73.377900,'motavita'),
(270,28,'Muzo','muzo',5.513810,-74.099440,'muzo'),
(271,28,'Nobsa','nobsa',5.777370,-72.945080,'nobsa'),
(272,28,'Nuevo Colón','nuevo-colon',5.355740,-73.449180,'nuevo colon'),
(273,28,'Oicata','oicata',5.610540,-73.276630,'oicata'),
(274,28,'Otanche','otanche',5.760840,-74.217960,'otanche'),
(275,28,'Pachavita','pachavita',5.140430,-73.400060,'pachavita'),
(276,28,'Paipa','paipa',5.769040,-73.124860,'paipa'),
(277,28,'Pajarito','pajarito',5.376780,-72.694780,'pajarito'),
(278,28,'Panqueba','panqueba',6.444050,-72.454090,'panqueba'),
(279,28,'Pauna','pauna',5.665810,-73.996810,'pauna'),
(280,28,'Paya','paya',5.618720,-72.354700,'paya'),
(281,28,'Paz de Río','paz-de-rio',6.036020,-72.760490,'paz de rio'),
(282,28,'Pesca','pesca',5.499310,-73.090990,'pesca'),
(283,28,'Pisba','pisba',5.767940,-72.436050,'pisba'),
(284,28,'Puerto Boyacá','puerto-boyaca',5.921290,-74.449470,'puerto boyaca'),
(285,28,'Páez','paez',5.085860,-73.005950,'paez'),
(286,28,'Quípama','quipama',5.581800,-74.234710,'quipama'),
(287,28,'Ramiriquí','ramiriqui',5.309280,-73.301780,'ramiriqui'),
(288,28,'Rondón','rondon',5.380460,-73.188350,'rondon'),
(289,28,'Ráquira','raquira',5.499540,-73.630500,'raquira'),
(290,28,'Saboyá','saboya',5.707320,-73.736470,'saboya'),
(291,28,'Samacá','samaca',5.481190,-73.497960,'samaca'),
(292,28,'San Eduardo','san-eduardo',5.231450,-73.045310,'san eduardo'),
(293,28,'San José de Pare','san-jose-de-pare',5.995770,-73.543070,'san jose de pare'),
(294,28,'San Luis de Gaceno','san-luis-de-gaceno',4.836860,-73.134220,'san luis de gaceno'),
(295,28,'San Mateo','san-mateo',6.391210,-72.558450,'san mateo'),
(296,28,'San Miguel de Sema','san-miguel-de-sema',5.534960,-73.755110,'san miguel de sema'),
(297,28,'San Pablo de Borbur','san-pablo-de-borbur',5.678780,-74.114020,'san pablo de borbur'),
(298,28,'Santa María','santa-maria',4.823780,-73.276420,'santa maria'),
(299,28,'Santa Rosa de Viterbo','santa-rosa-de-viterbo',5.897240,-72.985690,'santa rosa de viterbo'),
(300,28,'Santa Sofía','santa-sofia',5.727460,-73.604560,'santa sofia'),
(301,28,'Santana','santana',6.058770,-73.488860,'santana'),
(302,28,'Sativanorte','sativanorte',6.128330,-72.728750,'sativanorte'),
(303,28,'Sativasur','sativasur',6.057460,-72.696740,'sativasur'),
(304,28,'Siachoque','siachoque',5.497850,-73.211730,'siachoque'),
(305,28,'Soatá','soata',6.329700,-72.694890,'soata'),
(306,28,'Socha','socha',5.965460,-72.681540,'socha'),
(307,28,'Socotá','socota',5.919800,-72.539110,'socota'),
(308,28,'Sogamoso','sogamoso',5.670080,-72.909830,'sogamoso'),
(309,28,'Somondoco','somondoco',4.973010,-73.429440,'somondoco'),
(310,28,'Sora','sora',5.578530,-73.443650,'sora'),
(311,28,'Soracá','soraca',5.496390,-73.319690,'soraca'),
(312,28,'Sotaquirá','sotaquira',5.764840,-73.270170,'sotaquira'),
(313,28,'Susacón','susacon',6.215280,-72.706320,'susacon'),
(314,28,'Sutamarchàn','sutamarchan',5.651930,-73.631140,'sutamarchan'),
(315,28,'Sutatenza','sutatenza',5.021560,-73.446070,'sutatenza'),
(316,28,'Sáchica','sachica',5.566370,-73.539040,'sachica'),
(317,28,'Tasco','tasco',5.889660,-72.754250,'tasco'),
(318,28,'Tenza','tenza',5.077480,-73.412810,'tenza'),
(319,28,'Tibana','tibana',5.300780,-73.392120,'tibana'),
(320,28,'Tibasosa','tibasosa',5.752520,-73.010210,'tibasosa'),
(321,28,'Tinjacá','tinjaca',5.578020,-73.675030,'tinjaca'),
(322,28,'Tipacoque','tipacoque',6.417900,-72.695400,'tipacoque'),
(323,28,'Toca','toca',5.572530,-73.154880,'toca'),
(324,28,'Togüí','togui',5.923310,-73.494120,'togui'),
(325,28,'Tota','tota',5.508870,-73.000220,'tota'),
(326,28,'Tunja','tunja',5.524390,-73.378070,'tunja'),
(327,28,'Tununguá','tunungua',5.717960,-73.949510,'tunungua'),
(328,28,'Turmequé','turmeque',5.306830,-73.511910,'turmeque'),
(329,28,'Tuta','tuta',5.684960,-73.205850,'tuta'),
(330,28,'Tutazá','tutaza',6.081010,-72.858130,'tutaza'),
(331,28,'Tópaga','topaga',5.762630,-72.835980,'topaga'),
(332,28,'Umbita','umbita',5.205770,-73.467860,'umbita'),
(333,28,'Ventaquemada','ventaquemada',5.383760,-73.522410,'ventaquemada'),
(334,28,'Villa de Leyva','villa-de-leyva',5.664760,-73.513590,'villa de leyva'),
(335,28,'Viracachá','viracacha',5.436930,-73.261970,'viracacha'),
(336,28,'Zetaquirá','zetaquira',5.286060,-73.165650,'zetaquira'),
(337,1,'Aguadas','aguadas',5.613440,-75.440470,'aguadas'),
(338,1,'Anserma','anserma',5.220880,-75.751540,'anserma'),
(339,1,'Aranzazu','aranzazu',5.262240,-75.472410,'aranzazu'),
(340,1,'Belalcázar','belalcazar',4.992690,-75.821670,'belalcazar'),
(341,1,'Chinchiná','chinchina',4.969790,-75.675670,'chinchina'),
(342,1,'Filadelfia','filadelfia',5.284360,-75.591190,'filadelfia'),
(343,1,'La Dorada','la-dorada',5.636640,-74.698220,'la dorada'),
(344,1,'La Merced','la-merced',5.392860,-75.558710,'la merced'),
(345,1,'Manizales','manizales',5.085610,-75.477980,'manizales'),
(346,1,'Manzanares','manzanares',5.231410,-75.145030,'manzanares'),
(347,1,'Marmato','marmato',5.490090,-75.598880,'marmato'),
(348,1,'Marquetalia','marquetalia',5.304100,-75.055150,'marquetalia'),
(349,1,'Marulanda','marulanda',5.247960,-75.300290,'marulanda'),
(350,1,'Neira','neira',5.180650,-75.506920,'neira'),
(351,1,'Norcasia','norcasia',5.646580,-74.827650,'norcasia'),
(352,1,'Palestina','palestina',5.040570,-75.665690,'palestina'),
(353,1,'Pensilvania','pensilvania',5.419010,-75.166940,'pensilvania'),
(354,1,'Pácora','pacora',5.505170,-75.484390,'pacora'),
(355,1,'Riosucio','riosucio',5.468220,-75.754240,'riosucio'),
(356,1,'Risaralda','risaralda',5.094880,-75.735230,'risaralda'),
(357,1,'Salamina','salamina',5.363580,-75.418260,'salamina'),
(358,1,'Samana','samana',5.466640,-75.002970,'samana'),
(359,1,'San José','san-jose',5.086720,-75.791880,'san jose'),
(360,1,'Supia','supia',5.449600,-75.645370,'supia'),
(361,1,'Victoria','victoria',5.458110,-74.838260,'victoria'),
(362,1,'Villamaria','villamaria',4.948590,-75.422060,'villamaria'),
(363,1,'Viterbo','viterbo',5.052230,-75.884760,'viterbo'),
(364,31,'Albania','albania',1.231160,-75.890430,'albania'),
(365,31,'Belén de los Andaquíes','belen-de-los-andaquies',1.471730,-75.891590,'belen de los andaquies'),
(366,31,'Cartagena del Chairá','cartagena-del-chaira',0.490620,-74.287670,'cartagena del chaira'),
(367,31,'Curillo','curillo',1.113500,-75.967760,'curillo'),
(368,31,'El Doncello','el-doncello',1.702150,-75.268340,'el doncello'),
(369,31,'El Paujil','el-paujil',1.496960,-75.191340,'el paujil'),
(370,31,'Florencia','florencia',1.753500,-75.581190,'florencia'),
(371,31,'La Montañita','la-montanita',1.244330,-75.206150,'la montanita'),
(372,31,'Milán','milan',1.172200,-75.437550,'milan'),
(373,31,'Morelia','morelia',1.383430,-75.671990,'morelia'),
(374,31,'Puerto Rico','puerto-rico',1.931100,-75.120020,'puerto rico'),
(375,31,'San José del Fragua','san-jose-del-fragua',1.310650,-76.096260,'san jose del fragua'),
(376,31,'San Vicente del Caguán','san-vicente-del-caguan',1.190140,-73.880290,'san vicente del caguan'),
(377,31,'Solano','solano',0.197160,-72.560580,'solano'),
(378,31,'Solita','solita',0.935100,-75.667890,'solita'),
(379,31,'Valparaíso','valparaiso',1.121180,-75.653920,'valparaiso'),
(380,29,'Aguazul','aguazul',5.134370,-72.568760,'aguazul'),
(381,29,'Chameza','chameza',5.223060,-72.875320,'chameza'),
(382,29,'Hato Corozal','hato-corozal',6.035580,-71.605180,'hato corozal'),
(383,29,'La Salina','la-salina',6.165770,-72.333620,'la salina'),
(384,29,'Maní','mani',4.671820,-72.220130,'mani'),
(385,29,'Monterrey','monterrey',4.832930,-72.857550,'monterrey'),
(386,29,'Nunchia','nunchia',5.525750,-72.096130,'nunchia'),
(387,29,'Orocué','orocue',4.864160,-71.648020,'orocue'),
(388,29,'Paz de Ariporo','paz-de-ariporo',5.745410,-70.869270,'paz de ariporo'),
(389,29,'Pore','pore',5.638780,-71.922460,'pore'),
(390,29,'Recetor','recetor',5.267790,-72.763870,'recetor'),
(391,29,'Sabanalarga','sabanalarga',4.799310,-73.012540,'sabanalarga'),
(392,29,'Sacama','sacama',6.054830,-72.213280,'sacama'),
(393,29,'San Luis de Palenque','san-luis-de-palenque',5.268830,-71.794610,'san luis de palenque'),
(394,29,'Tamara','tamara',5.855260,-72.200700,'tamara'),
(395,29,'Tauramena','tauramena',4.578050,-72.634240,'tauramena'),
(396,29,'Trinidad','trinidad',5.334890,-71.130180,'trinidad'),
(397,29,'Villanueva','villanueva',4.563890,-72.844010,'villanueva'),
(398,29,'Yopal','yopal',5.258390,-72.253340,'yopal'),
(399,30,'Almaguer','almaguer',1.913990,-76.843640,'almaguer'),
(400,30,'Argelia','argelia',2.340170,-77.265700,'argelia'),
(401,30,'Balboa','balboa',2.083150,-77.210650,'balboa'),
(402,30,'Bolívar','bolivar',1.855920,-76.965800,'bolivar'),
(403,30,'Buenos Aires','buenos-aires',3.047540,-76.626790,'buenos aires'),
(404,30,'Cajibío','cajibio',2.647000,-76.704310,'cajibio'),
(405,30,'Caldono','caldono',2.798190,-76.428570,'caldono'),
(406,30,'Caloto','caloto',3.016740,-76.380140,'caloto'),
(407,30,'Corinto','corinto',3.134540,-76.222610,'corinto'),
(408,30,'El Tambo','el-tambo',2.544590,-76.999460,'el tambo'),
(409,30,'Florencia','florencia',1.690820,-77.086690,'florencia'),
(410,30,'Guachené','guachene',3.157010,-76.388900,'guachene'),
(411,30,'Guapi','guapi',2.397330,-77.695770,'guapi'),
(412,30,'Inzá','inza',2.519970,-76.103680,'inza'),
(413,30,'Jambaló','jambalo',2.827610,-76.316640,'jambalo'),
(414,30,'La Sierra','la-sierra',2.186910,-76.755280,'la sierra'),
(415,30,'La Vega','la-vega',2.066270,-76.769880,'la vega'),
(416,30,'López','lopez',2.957730,-77.180380,'lopez'),
(417,30,'Mercaderes','mercaderes',1.788830,-77.183590,'mercaderes'),
(418,30,'Miranda','miranda',3.252510,-76.256410,'miranda'),
(419,30,'Morales','morales',2.776270,-76.791000,'morales'),
(420,30,'Padilla','padilla',3.200560,-76.333250,'padilla'),
(421,30,'Patía','patia',2.136460,-77.016810,'patia'),
(422,30,'Piamonte','piamonte',1.082620,-76.363500,'piamonte'),
(423,30,'Piendamó','piendamo',2.683520,-76.551510,'piendamo'),
(424,30,'Popayán','popayan',2.481710,-76.595050,'popayan'),
(425,30,'Puerto Tejada','puerto-tejada',3.266170,-76.418510,'puerto tejada'),
(426,30,'Puracé (Coconuco)','purace-coconuco',2.312850,-76.440450,'purace coconuco'),
(427,30,'Páez','paez',2.724310,-75.930520,'paez'),
(428,30,'Rosas','rosas',2.264200,-76.770660,'rosas'),
(429,30,'San Sebastian','san-sebastian',1.887970,-76.678160,'san sebastian'),
(430,30,'Santa Rosa','santa-rosa',1.555370,-76.508110,'santa rosa'),
(431,30,'Santander de Quilichao','santander-de-quilichao',2.981250,-76.510860,'santander de quilichao'),
(432,30,'Silvia','silvia',2.645310,-76.350590,'silvia'),
(433,30,'Sotará (Paispamba)','sotara-paispamba',2.264570,-76.602230,'sotara paispamba'),
(434,30,'Sucre','sucre',2.045890,-76.916110,'sucre'),
(435,30,'Suárez','suarez',2.928960,-76.738650,'suarez'),
(436,30,'Timbiquí','timbiqui',2.729460,-77.486660,'timbiqui'),
(437,30,'Timbío','timbio',2.356260,-76.703270,'timbio'),
(438,30,'Toribío','toribio',2.971730,-76.213500,'toribio'),
(439,30,'Totoró','totoro',2.469970,-76.361660,'totoro'),
(440,30,'Villa Rica','villa-rica',3.189110,-76.465000,'villa rica'),
(441,9,'Aguachica','aguachica',8.263820,-73.620170,'aguachica'),
(442,9,'Agustín Codazzi','agustin-codazzi',9.936670,-73.248490,'agustin codazzi'),
(443,9,'Astrea','astrea',9.503630,-73.945000,'astrea'),
(444,9,'Becerrill','becerrill',9.705780,-73.252770,'becerrill'),
(445,9,'Bosconia','bosconia',9.948260,-73.851320,'bosconia'),
(446,9,'Chimichagua','chimichagua',9.266830,-73.805340,'chimichagua'),
(447,9,'Chiriguaná','chiriguana',9.411910,-73.595970,'chiriguana'),
(448,9,'Curumaní','curumani',9.232600,-73.473450,'curumani'),
(449,9,'El Copey','el-copey',10.185970,-73.910240,'el copey'),
(450,9,'El Paso','el-paso',9.676720,-73.724350,'el paso'),
(451,9,'Gamarra','gamarra',8.379640,-73.700810,'gamarra'),
(452,9,'Gonzalez','gonzalez',8.407030,-73.374850,'gonzalez'),
(453,9,'La Gloria','la-gloria',8.571720,-73.647050,'la gloria'),
(454,9,'La Jagua de Ibirico','la-jagua-de-ibirico',9.533950,-73.367060,'la jagua de ibirico'),
(455,9,'La Paz','la-paz',10.253830,-73.032650,'la paz'),
(456,9,'Manaure Balcón del Cesar','manaure-balcon-del-cesar',10.382550,-73.022060,'manaure balcon del cesar'),
(457,9,'Pailitas','pailitas',8.942160,-73.569890,'pailitas'),
(458,9,'Pelaya','pelaya',8.799440,-73.630310,'pelaya'),
(459,9,'Pueblo Bello','pueblo-bello',10.418210,-73.570960,'pueblo bello'),
(460,9,'Río de Oro','rio-de-oro',8.201940,-73.489720,'rio de oro'),
(461,9,'San Alberto','san-alberto',7.818140,-73.417840,'san alberto'),
(462,9,'San Diego','san-diego',10.193900,-73.231980,'san diego'),
(463,9,'San Martín','san-martin',7.941290,-73.547910,'san martin'),
(464,9,'Tamalameque','tamalameque',8.908780,-73.775270,'tamalameque'),
(465,9,'Valledupar','valledupar',10.566060,-73.309820,'valledupar'),
(466,14,'Acandí','acandi',8.386390,-77.270880,'acandi'),
(467,14,'Alto Baudó (Pie de Pato)','alto-baudo-pie-de-pato',5.631520,-77.081060,'alto baudo pie de pato'),
(468,14,'Atrato (Yuto)','atrato-yuto',5.578770,-76.625570,'atrato yuto'),
(469,14,'Bagadó','bagado',5.506140,-76.176610,'bagado'),
(470,14,'Bahía Solano','bahia-solano',6.172680,-77.329740,'bahia solano'),
(471,14,'Bajo Baudó (Pizarro)','bajo-baudo-pizarro',4.785660,-77.167000,'bajo baudo pizarro'),
(472,14,'Bojayá (Bellavista)','bojaya-bellavista',6.488320,-77.104200,'bojaya bellavista'),
(473,14,'Carmen del Darién  (Curbaradó)','carmen-del-darien-curbarado',7.019540,-77.004570,'carmen del darien curbarado'),
(474,14,'Condoto','condoto',5.061690,-76.512480,'condoto'),
(475,14,'Cértegui','certegui',5.398710,-76.519720,'certegui'),
(476,14,'El Cantón del San Pablo (Managrú)','el-canton-del-san-pablo-managru',5.361560,-76.770620,'el canton del san pablo managru'),
(477,14,'El Carmen','el-carmen',5.781440,-76.189400,'el carmen'),
(478,14,'El Litoral del San Juán (Docordó)','el-litoral-del-san-juan-docordo',4.275030,-76.954270,'el litoral del san juan docordo'),
(479,14,'Istmina','istmina',4.516280,-76.847490,'istmina'),
(480,14,'Juradó','jurado',7.143640,-77.683500,'jurado'),
(481,14,'Lloró','lloro',5.592750,-76.388950,'lloro'),
(482,14,'Medio Atrato (Beté)','medio-atrato-bete',6.022090,-76.678960,'medio atrato bete'),
(483,14,'Medio Baudó(boca de Pepé)','medio-baudo-boca-de-pepe',5.083940,-76.979340,'medio baudo boca de pepe'),
(484,14,'Medio San Juan (Andagoya)','medio-san-juan-andagoya',4.894850,-76.814600,'medio san juan andagoya'),
(485,14,'Nuquí','nuqui',5.617020,-77.263920,'nuqui'),
(486,14,'Nóvita','novita',4.838240,-76.621470,'novita'),
(487,14,'Quibdó','quibdo',5.794500,-76.575060,'quibdo'),
(488,14,'Rio Iró (Santa Rita)','rio-iro-santa-rita',5.203890,-76.438220,'rio iro santa rita'),
(489,14,'Rio Quito (Paimadó)','rio-quito-paimado',5.576970,-76.796300,'rio quito paimado'),
(490,14,'Riosucio','riosucio',7.446210,-77.240720,'riosucio'),
(491,14,'San José del Palmar','san-jose-del-palmar',4.961820,-76.275690,'san jose del palmar'),
(492,14,'Sipí','sipi',4.559080,-76.537880,'sipi'),
(493,14,'Tadó','tado',5.266540,-76.265240,'tado'),
(494,14,'Unguía','unguia',8.123250,-77.088790,'unguia'),
(495,14,'Unión Panamericana ( Animas)','union-panamericana-animas',5.301400,-76.638410,'union panamericana animas'),
(496,12,'Ayapel','ayapel',8.255490,-75.053840,'ayapel'),
(497,12,'Buenavista','buenavista',8.181460,-75.450800,'buenavista'),
(498,12,'Canalete','canalete',8.725500,-76.240160,'canalete'),
(499,12,'Cereté','cerete',8.867520,-75.788830,'cerete'),
(500,12,'Chimá','chima',9.104820,-75.653860,'chima'),
(501,12,'Chinú','chinu',9.053930,-75.374780,'chinu'),
(502,12,'Cienaga de Oro','cienaga-de-oro',8.900280,-75.628420,'cienaga de oro'),
(503,12,'Cotorra','cotorra',9.080370,-75.762780,'cotorra'),
(504,12,'La Apartada','la-apartada',8.049950,-75.299260,'la apartada'),
(505,12,'Lorica','lorica',9.177460,-75.890390,'lorica'),
(506,12,'Los Córdobas','los-cordobas',8.855870,-76.330770,'los cordobas'),
(507,12,'Momil','momil',9.264290,-75.650870,'momil'),
(508,12,'Montelíbano','montelibano',7.942990,-75.906000,'montelibano'),
(509,12,'Montería','monteria',8.556420,-75.956590,'monteria'),
(510,12,'Moñitos','monitos',9.196190,-76.124720,'monitos'),
(511,12,'Planeta Rica','planeta-rica',8.359210,-75.574950,'planeta rica'),
(512,12,'Pueblo Nuevo','pueblo-nuevo',8.498180,-75.401340,'pueblo nuevo'),
(513,12,'Puerto Escondido','puerto-escondido',9.001760,-76.192070,'puerto escondido'),
(514,12,'Puerto Libertador','puerto-libertador',7.714890,-75.767580,'puerto libertador'),
(515,12,'Purísima','purisima',9.260940,-75.735290,'purisima'),
(516,12,'Sahagún','sahagun',8.821620,-75.453420,'sahagun'),
(517,12,'San Andres de Sotavento','san-andres-de-sotavento',9.111720,-75.528240,'san andres de sotavento'),
(518,12,'San Antero','san-antero',9.350090,-75.788610,'san antero'),
(519,12,'San Bernardo del Viento','san-bernardo-del-viento',9.327370,-75.976000,'san bernardo del viento'),
(520,12,'San Carlos','san-carlos',8.707410,-75.690960,'san carlos'),
(521,12,'San José de Uré','san-jose-de-ure',7.694440,-75.607930,'san jose de ure'),
(522,12,'San Pelayo','san-pelayo',8.983250,-75.906170,'san pelayo'),
(523,12,'Tierralta','tierralta',7.738490,-76.226120,'tierralta'),
(524,12,'Tuchín','tuchin',9.223830,-75.549270,'tuchin'),
(525,12,'Valencia','valencia',8.264720,-76.192490,'valencia'),
(526,4,'Agua de Dios','agua-de-dios',4.373980,-74.678020,'agua de dios'),
(527,4,'Albán','alban',4.896880,-74.435500,'alban'),
(528,4,'Anapoima','anapoima',4.563840,-74.521100,'anapoima'),
(529,4,'Anolaima','anolaima',4.790540,-74.461960,'anolaima'),
(530,4,'Apulo','apulo',4.509780,-74.587420,'apulo'),
(531,4,'Arbeláez','arbelaez',4.237650,-74.395750,'arbelaez'),
(532,4,'Beltrán','beltran',4.719300,-74.780560,'beltran'),
(533,4,'Bituima','bituima',4.852730,-74.529080,'bituima'),
(534,4,'Bojacá','bojaca',4.707700,-74.333260,'bojaca'),
(535,4,'Cabrera','cabrera',3.924970,-74.438690,'cabrera'),
(536,4,'Cachipay','cachipay',4.707390,-74.477760,'cachipay'),
(537,4,'Cajicá','cajica',4.929460,-74.032690,'cajica'),
(538,4,'Caparrapi','caparrapi',5.393670,-74.529470,'caparrapi'),
(539,4,'Carmen de Carupa','carmen-de-carupa',5.360760,-73.927430,'carmen de carupa'),
(540,4,'Chaguaní','chaguani',4.951000,-74.623650,'chaguani'),
(541,4,'Chia','chia',4.862470,-74.048360,'chia'),
(542,4,'Chipaque','chipaque',4.425780,-74.082610,'chipaque'),
(543,4,'Choachí','choachi',4.564630,-73.941270,'choachi'),
(544,4,'Chocontá','choconta',5.118570,-73.688880,'choconta'),
(545,4,'Cogua','cogua',5.096500,-73.968510,'cogua'),
(546,4,'Cota','cota',4.778110,-74.137690,'cota'),
(547,4,'Cucunubá','cucunuba',5.226120,-73.786870,'cucunuba'),
(548,4,'Cáqueza','caqueza',4.408390,-73.945910,'caqueza'),
(549,4,'El Colegio','el-colegio',4.559300,-74.427570,'el colegio'),
(550,4,'El Peñon','el-penon',5.267610,-74.303330,'el penon'),
(551,4,'El Rosal','el-rosal',4.878770,-74.237220,'el rosal'),
(552,4,'Facatativá','facatativa',4.833860,-74.339530,'facatativa'),
(553,4,'Fosca','fosca',4.316610,-73.947600,'fosca'),
(554,4,'Funza','funza',4.736400,-74.193850,'funza'),
(555,4,'Fuquene','fuquene',5.416640,-73.769810,'fuquene'),
(556,4,'Fusagasugá','fusagasuga',4.321700,-74.388430,'fusagasuga'),
(557,4,'Fómeque','fomeque',4.522500,-73.795250,'fomeque'),
(558,4,'Gachalá','gachala',4.662650,-73.510450,'gachala'),
(559,4,'Gachancipá','gachancipa',5.009940,-73.874190,'gachancipa'),
(560,4,'Gachetá','gacheta',4.861640,-73.602240,'gacheta'),
(561,4,'Gama','gama',4.724430,-73.601430,'gama'),
(562,4,'Girardot','girardot',4.334780,-74.806350,'girardot'),
(563,4,'Granada','granada',4.523540,-74.338370,'granada'),
(564,4,'Guachetá','guacheta',5.387570,-73.694650,'guacheta'),
(565,4,'Guaduas','guaduas',5.119850,-74.628840,'guaduas'),
(566,4,'Guasca','guasca',4.803510,-73.844840,'guasca'),
(567,4,'Guataquí','guataqui',4.524490,-74.775820,'guataqui'),
(568,4,'Guatavita','guatavita',4.910880,-73.778990,'guatavita'),
(569,4,'Guayabal de Síquima','guayabal-de-siquima',4.868010,-74.476850,'guayabal de siquima'),
(570,4,'Guayabetal','guayabetal',4.234780,-73.833940,'guayabetal'),
(571,4,'Gutierrez','gutierrez',4.175850,-74.001700,'gutierrez'),
(572,4,'Jerusalén','jerusalen',4.579380,-74.690530,'jerusalen'),
(573,4,'Junín','junin',4.702840,-73.700430,'junin'),
(574,4,'La Calera','la-calera',4.708520,-73.937600,'la calera'),
(575,4,'La Mesa','la-mesa',4.648480,-74.474440,'la mesa'),
(576,4,'La Palma','la-palma',5.329310,-74.406740,'la palma'),
(577,4,'La Peña','la-pena',5.202210,-74.407890,'la pena'),
(578,4,'La Vega','la-vega',4.975240,-74.350080,'la vega'),
(579,4,'Lenguazaque','lenguazaque',5.289580,-73.694610,'lenguazaque'),
(580,4,'Machetá','macheta',5.041710,-73.617430,'macheta'),
(581,4,'Madrid','madrid',4.752460,-74.280560,'madrid'),
(582,4,'Manta','manta',4.977630,-73.562340,'manta'),
(583,4,'Medina','medina',4.509390,-73.381590,'medina'),
(584,4,'Mosquera','mosquera',4.667540,-74.228600,'mosquera'),
(585,4,'Nariño','narino',4.400050,-74.815660,'narino'),
(586,4,'Nemocón','nemocon',5.093950,-73.878530,'nemocon'),
(587,4,'Nilo','nilo',4.306060,-74.606750,'nilo'),
(588,4,'Nimaima','nimaima',5.127350,-74.385950,'nimaima'),
(589,4,'Nocaima','nocaima',5.077360,-74.390540,'nocaima'),
(590,4,'Pacho','pacho',5.154730,-74.143650,'pacho'),
(591,4,'Paime','paime',5.385210,-74.169900,'paime'),
(592,4,'Pandi','pandi',4.183500,-74.464230,'pandi'),
(593,4,'Paratebueno','paratebueno',4.371800,-73.190560,'paratebueno'),
(594,4,'Pasca','pasca',4.304650,-74.279900,'pasca'),
(595,4,'Puerto Salgar','puerto-salgar',5.657810,-74.569900,'puerto salgar'),
(596,4,'Pulí','puli',4.696810,-74.689620,'puli'),
(597,4,'Quebradanegra','quebradanegra',5.099700,-74.488770,'quebradanegra'),
(598,4,'Quetame','quetame',4.356450,-73.839520,'quetame'),
(599,4,'Quipile','quipile',4.724030,-74.553270,'quipile'),
(600,4,'Ricaurte','ricaurte',4.296270,-74.722740,'ricaurte'),
(601,4,'San Antonio del Tequendama','san-antonio-del-tequendama',4.600200,-74.345860,'san antonio del tequendama'),
(602,4,'San Bernardo','san-bernardo',4.141730,-74.374520,'san bernardo'),
(603,4,'San Cayetano','san-cayetano',5.309190,-74.072310,'san cayetano'),
(604,4,'San Francisco','san-francisco',4.968530,-74.267530,'san francisco'),
(605,4,'San Juan de Rio Seco','san-juan-de-rio-seco',4.839190,-74.634420,'san juan de rio seco'),
(606,4,'Sasaima','sasaima',4.954890,-74.418470,'sasaima'),
(607,4,'Sesquilé','sesquile',5.007980,-73.803330,'sesquile'),
(608,4,'Sibaté','sibate',4.469370,-74.264200,'sibate'),
(609,4,'Silvania','silvania',4.446980,-74.364350,'silvania'),
(610,4,'Simijaca','simijaca',5.509240,-73.839950,'simijaca'),
(611,4,'Soacha','soacha',4.563960,-74.214320,'soacha'),
(612,4,'Sopo','sopo',4.882760,-73.955520,'sopo'),
(613,4,'Subachoque','subachoque',4.968390,-74.164340,'subachoque'),
(614,4,'Suesca','suesca',5.118230,-73.799620,'suesca'),
(615,4,'Supatá','supata',5.063600,-74.231370,'supata'),
(616,4,'Susa','susa',5.435250,-73.830010,'susa'),
(617,4,'Sutatausa','sutatausa',5.245520,-73.859430,'sutatausa'),
(618,4,'Tabio','tabio',4.952020,-74.081150,'tabio'),
(619,4,'Tausa','tausa',5.187290,-73.914460,'tausa'),
(620,4,'Tena','tena',4.666940,-74.375200,'tena'),
(621,4,'Tenjo','tenjo',4.851300,-74.140080,'tenjo'),
(622,4,'Tibacuy','tibacuy',4.326180,-74.480160,'tibacuy'),
(623,4,'Tibirita','tibirita',5.085380,-73.514240,'tibirita'),
(624,4,'Tocaima','tocaima',4.463330,-74.658630,'tocaima'),
(625,4,'Tocancipá','tocancipa',4.956790,-73.923150,'tocancipa'),
(626,4,'Topaipí','topaipi',5.355980,-74.288480,'topaipi'),
(627,4,'Ubalá','ubala',4.809320,-73.474780,'ubala'),
(628,4,'Ubaque','ubaque',4.509590,-73.992950,'ubaque'),
(629,4,'Ubate','ubate',5.312990,-73.813870,'ubate'),
(630,4,'Une','une',4.258740,-74.102100,'une'),
(631,4,'Venecia','venecia',4.064070,-74.437430,'venecia'),
(632,4,'Vergara','vergara',5.131960,-74.321130,'vergara'),
(633,4,'Vianí','viani',4.921220,-74.538580,'viani'),
(634,4,'Villa Gómez','villa-gomez',5.257240,-74.182240,'villa gomez'),
(635,4,'Villapinzón','villapinzon',5.232900,-73.583680,'villapinzon'),
(636,4,'Villeta','villeta',5.014330,-74.488980,'villeta'),
(637,4,'Viotá','viota',4.427260,-74.485470,'viota'),
(638,4,'Yacopí','yacopi',5.621690,-74.397870,'yacopi'),
(639,4,'Zipacón','zipacon',4.753020,-74.382710,'zipacon'),
(640,4,'Zipaquirá','zipaquira',5.015510,-74.024530,'zipaquira'),
(641,4,'Útica','utica',5.183570,-74.482550,'utica'),
(642,25,'Barranco Mina','barranco-mina',3.373590,-69.410080,'barranco mina'),
(643,25,'Cacahual','cacahual',3.359450,-67.589370,'cacahual'),
(644,25,'Inírida','inirida',3.498060,-68.157960,'inirida'),
(645,25,'La Guadalupe','la-guadalupe',1.334220,-66.978140,'la guadalupe'),
(646,25,'Mapiripana','mapiripana',2.775480,-70.171800,'mapiripana'),
(647,25,'Morichal (Morichal Nuevo)','morichal-morichal-nuevo',2.379600,-69.768930,'morichal morichal nuevo'),
(648,25,'Paná-paná (Campo Alegre)','pana-pana-campo-alegre',2.045100,-69.183070,'pana pana campo alegre'),
(649,25,'Puerto Colombia','puerto-colombia',2.567420,-68.209580,'puerto colombia'),
(650,25,'San Felipe','san-felipe',2.360260,-67.394070,'san felipe'),
(651,24,'Calamar','calamar',1.580350,-73.020430,'calamar'),
(652,24,'El Retorno','el-retorno',2.005010,-71.390450,'el retorno'),
(653,24,'Miraflores','miraflores',1.326160,-71.951170,'miraflores'),
(654,24,'San José del Guaviare','san-jose-del-guaviare',2.554320,-71.526000,'san jose del guaviare'),
(655,32,'Acevedo','acevedo',1.712640,-75.985370,'acevedo'),
(656,32,'Agrado','agrado',2.283110,-75.717780,'agrado'),
(657,32,'Aipe','aipe',3.205510,-75.318240,'aipe'),
(658,32,'Algeciras','algeciras',2.497230,-75.293830,'algeciras'),
(659,32,'Altamira','altamira',2.064760,-75.792440,'altamira'),
(660,32,'Baraya','baraya',3.164080,-74.955050,'baraya'),
(661,32,'Campoalegre','campoalegre',2.659060,-75.331370,'campoalegre'),
(662,32,'Colombia','colombia',3.324670,-74.736660,'colombia'),
(663,32,'Elías','elias',2.010220,-75.957750,'elias'),
(664,32,'Garzón','garzon',2.181390,-75.582200,'garzon'),
(665,32,'Gigante','gigante',2.364980,-75.526440,'gigante'),
(666,32,'Guadalupe','guadalupe',1.990920,-75.695320,'guadalupe'),
(667,32,'Hobo','hobo',2.533530,-75.442570,'hobo'),
(668,32,'Isnos','isnos',1.949080,-76.218450,'isnos'),
(669,32,'La Argentina','la-argentina',2.142030,-76.055650,'la argentina'),
(670,32,'La Plata','la-plata',2.315770,-76.020510,'la plata'),
(671,32,'Neiva','neiva',2.930290,-75.221500,'neiva'),
(672,32,'Nátaga','nataga',2.555610,-75.801400,'nataga'),
(673,32,'Oporapa','oporapa',2.052570,-76.026840,'oporapa'),
(674,32,'Paicol','paicol',2.404900,-75.739670,'paicol'),
(675,32,'Palermo','palermo',2.886720,-75.410400,'palermo'),
(676,32,'Palestina','palestina',1.676740,-76.149150,'palestina'),
(677,32,'Pital','pital',2.264630,-75.838320,'pital'),
(678,32,'Pitalito','pitalito',1.854570,-76.077000,'pitalito'),
(679,32,'Rivera','rivera',2.792400,-75.234060,'rivera'),
(680,32,'Saladoblanco','saladoblanco',2.114500,-76.257680,'saladoblanco'),
(681,32,'San Agustín','san-agustin',1.940580,-76.416420,'san agustin'),
(682,32,'Santa María','santa-maria',2.936230,-75.635680,'santa maria'),
(683,32,'Suaza','suaza',1.870790,-75.797060,'suaza'),
(684,32,'Tarqui','tarqui',2.130060,-75.888750,'tarqui'),
(685,32,'Tello','tello',3.020810,-75.057600,'tello'),
(686,32,'Teruel','teruel',2.851950,-75.759330,'teruel'),
(687,32,'Tesalia','tesalia',2.528700,-75.702290,'tesalia'),
(688,32,'Timaná','timana',1.945310,-75.930820,'timana'),
(689,32,'Villavieja','villavieja',3.277810,-75.147500,'villavieja'),
(690,32,'Yaguará','yaguara',2.641050,-75.521880,'yaguara'),
(691,32,'Íquira','iquira',2.715000,-75.683240,'iquira'),
(692,13,'Albania','albania',11.243950,-72.523370,'albania'),
(693,13,'Barrancas','barrancas',10.968380,-72.651130,'barrancas'),
(694,13,'Dibulla','dibulla',11.075920,-73.475070,'dibulla'),
(695,13,'Distracción','distraccion',10.935500,-72.958030,'distraccion'),
(696,13,'El Molino','el-molino',10.624220,-72.855000,'el molino'),
(697,13,'Fonseca','fonseca',10.806210,-72.772670,'fonseca'),
(698,13,'Hatonuevo','hatonuevo',11.099010,-72.738300,'hatonuevo'),
(699,13,'La Jagua del Pilar','la-jagua-del-pilar',10.470530,-73.072130,'la jagua del pilar'),
(700,13,'Maicao','maicao',11.365750,-72.301040,'maicao'),
(701,13,'Manaure','manaure',11.600900,-72.545540,'manaure'),
(702,13,'Riohacha','riohacha',11.299620,-72.931980,'riohacha'),
(703,13,'San Juan del Cesar','san-juan-del-cesar',10.830020,-73.121700,'san juan del cesar'),
(704,13,'Uribia','uribia',11.971170,-71.738890,'uribia'),
(705,13,'Urumita','urumita',10.483750,-72.975290,'urumita'),
(706,13,'Villanueva','villanueva',10.584140,-72.975240,'villanueva'),
(707,10,'Algarrobo','algarrobo',10.277580,-74.113060,'algarrobo'),
(708,10,'Aracataca','aracataca',10.624970,-73.874570,'aracataca'),
(709,10,'Ariguaní','ariguani',9.830970,-74.099920,'ariguani'),
(710,10,'Cerro de San Antonio','cerro-de-san-antonio',10.318150,-74.836090,'cerro de san antonio'),
(711,10,'Chivolo','chivolo',10.121560,-74.516360,'chivolo'),
(712,10,'Ciénaga','cienaga',10.851550,-73.981810,'cienaga'),
(713,10,'Concordia','concordia',10.220910,-74.756410,'concordia'),
(714,10,'El Banco','el-banco',9.104940,-73.968120,'el banco'),
(715,10,'El Piñón','el-pinon',10.370200,-74.696330,'el pinon'),
(716,10,'El Retén','el-reten',10.673100,-74.355870,'el reten'),
(717,10,'Fundación','fundacion',10.449730,-73.888950,'fundacion'),
(718,10,'Guamal','guamal',9.246970,-74.150450,'guamal'),
(719,10,'Nueva Granada','nueva-granada',9.692810,-74.326120,'nueva granada'),
(720,10,'Pedraza','pedraza',10.157350,-74.833830,'pedraza'),
(721,10,'Pijiño del Carmen','pijino-del-carmen',9.537560,-74.139880,'pijino del carmen'),
(722,10,'Pivijay','pivijay',10.424560,-74.408630,'pivijay'),
(723,10,'Plato','plato',9.778380,-74.602020,'plato'),
(724,10,'Pueblo Viejo','pueblo-viejo',10.860060,-74.376360,'pueblo viejo'),
(725,10,'Remolino','remolino',10.639850,-74.578050,'remolino'),
(726,10,'Sabanas de San Angel','sabanas-de-san-angel',10.091550,-74.264410,'sabanas de san angel'),
(727,10,'Salamina','salamina',10.498880,-74.721510,'salamina'),
(728,10,'San Sebastian de Buenavista','san-sebastian-de-buenavista',9.405450,-74.143170,'san sebastian de buenavista'),
(729,10,'San Zenón','san-zenon',9.302240,-74.383210,'san zenon'),
(730,10,'Santa Ana','santa-ana',9.501900,-74.445110,'santa ana'),
(731,10,'Santa Bárbara de Pinto','santa-barbara-de-pinto',9.497850,-74.659940,'santa barbara de pinto'),
(732,10,'Santa Marta','santa-marta',11.142630,-73.890560,'santa marta'),
(733,10,'Sitionuevo','sitionuevo',10.867360,-74.608670,'sitionuevo'),
(734,10,'Tenerife','tenerife',9.937170,-74.735720,'tenerife'),
(735,10,'Zapayán','zapayan',10.120520,-74.682990,'zapayan'),
(736,10,'Zona Bananera','zona-bananera',10.794770,-74.176070,'zona bananera'),
(737,3,'Acacías','acacias',4.062250,-73.853170,'acacias'),
(738,3,'Barranca de Upia','barranca-de-upia',4.527620,-73.016250,'barranca de upia'),
(739,3,'Cabuyaro','cabuyaro',4.279640,-72.972070,'cabuyaro'),
(740,3,'Castilla la Nueva','castilla-la-nueva',3.794470,-73.525650,'castilla la nueva'),
(741,3,'Cubarral','cubarral',3.850060,-74.108350,'cubarral'),
(742,3,'Cumaral','cumaral',4.229740,-73.318600,'cumaral'),
(743,3,'El Calvario','el-calvario',4.347040,-73.730370,'el calvario'),
(744,3,'El Castillo','el-castillo',3.597020,-73.885220,'el castillo'),
(745,3,'El Dorado','el-dorado',3.714250,-73.832610,'el dorado'),
(746,3,'Fuente de Oro','fuente-de-oro',3.379190,-73.609810,'fuente de oro'),
(747,3,'Granada','granada',3.463100,-73.758850,'granada'),
(748,3,'Guamal','guamal',3.968590,-74.017540,'guamal'),
(749,3,'La Macarena','la-macarena',2.141360,-74.082450,'la macarena'),
(750,3,'Lejanías','lejanias',3.656490,-74.132330,'lejanias'),
(751,3,'Mapiripán','mapiripan',3.108590,-71.921450,'mapiripan'),
(752,3,'Mesetas','mesetas',3.332670,-74.141870,'mesetas'),
(753,3,'Puerto Concordia','puerto-concordia',2.739510,-72.717140,'puerto concordia'),
(754,3,'Puerto Gaitán','puerto-gaitan',4.016650,-71.619120,'puerto gaitan'),
(755,3,'Puerto Lleras','puerto-lleras',3.200060,-73.291280,'puerto lleras'),
(756,3,'Puerto López','puerto-lopez',4.023740,-72.637090,'puerto lopez'),
(757,3,'Puerto Rico','puerto-rico',2.757660,-73.103350,'puerto rico'),
(758,3,'Restrepo','restrepo',4.221480,-73.534080,'restrepo'),
(759,3,'San Carlos de Guaroa','san-carlos-de-guaroa',3.842030,-73.279600,'san carlos de guaroa'),
(760,3,'San Juan de Arama','san-juan-de-arama',3.314100,-73.845420,'san juan de arama'),
(761,3,'San Juanito','san-juanito',4.465930,-73.669190,'san juanito'),
(762,3,'San Martín','san-martin',3.454900,-72.933740,'san martin'),
(763,3,'Uribe','uribe',3.028740,-74.478020,'uribe'),
(764,3,'Villavicencio','villavicencio',4.080550,-73.497470,'villavicencio'),
(765,3,'Vistahermosa','vistahermosa',2.870990,-73.756710,'vistahermosa'),
(766,23,'Albán (San José)','alban-san-jose',1.471290,-77.068110,'alban san jose'),
(767,23,'Aldana','aldana',0.920880,-77.703090,'aldana'),
(768,23,'Ancuya','ancuya',1.225290,-77.531880,'ancuya'),
(769,23,'Arboleda','arboleda',1.473910,-77.129880,'arboleda'),
(770,23,'Barbacoas','barbacoas',1.566460,-78.091020,'barbacoas'),
(771,23,'Belen','belen',1.583640,-77.036550,'belen'),
(772,23,'Buesaco','buesaco',1.313060,-77.119440,'buesaco'),
(773,23,'Chachagüí','chachagui',1.357150,-77.257530,'chachagui'),
(774,23,'Colon','colon',1.642320,-77.046390,'colon'),
(775,23,'Consacá','consaca',1.199890,-77.441690,'consaca'),
(776,23,'Contadero','contadero',0.922870,-77.537740,'contadero'),
(777,23,'Cuaspud','cuaspud',0.874270,-77.736580,'cuaspud'),
(778,23,'Cumbal','cumbal',0.956930,-77.943920,'cumbal'),
(779,23,'Cumbitara','cumbitara',1.700810,-77.574870,'cumbitara'),
(780,23,'Córdoba','cordoba',0.754630,-77.325340,'cordoba'),
(781,23,'El Charco','el-charco',2.216660,-77.994360,'el charco'),
(782,23,'El Peñol','el-penol',1.498000,-77.429800,'el penol'),
(783,23,'El Rosario','el-rosario',1.871150,-77.467110,'el rosario'),
(784,23,'El Tablón de Gómez','el-tablon-de-gomez',1.427510,-76.992050,'el tablon de gomez'),
(785,23,'El Tambo','el-tambo',1.421270,-77.376380,'el tambo'),
(786,23,'Francisco Pizarro (Salahonda)','francisco-pizarro-salahonda',2.092310,-78.593750,'francisco pizarro salahonda'),
(787,23,'Funes','funes',0.944210,-77.402240,'funes'),
(788,23,'Guachucal','guachucal',0.966270,-77.757350,'guachucal'),
(789,23,'Guaitarilla','guaitarilla',1.151690,-77.530030,'guaitarilla'),
(790,23,'Gualmatán','gualmatan',0.929460,-77.582190,'gualmatan'),
(791,23,'Iles','iles',0.978180,-77.519890,'iles'),
(792,23,'Imues','imues',1.073320,-77.490530,'imues'),
(793,23,'Ipiales','ipiales',0.512940,-77.370550,'ipiales'),
(794,23,'La Cruz','la-cruz',1.577710,-76.921620,'la cruz'),
(795,23,'La Florida','la-florida',1.309640,-77.404220,'la florida'),
(796,23,'La Llanada','la-llanada',1.541100,-77.716940,'la llanada'),
(797,23,'La Tola','la-tola',2.578290,-78.227890,'la tola'),
(798,23,'La Unión','la-union',1.616620,-77.141460,'la union'),
(799,23,'Leiva','leiva',1.910830,-77.304570,'leiva'),
(800,23,'Linares','linares',1.392620,-77.525920,'linares'),
(801,23,'Los Andes','los-andes',1.732530,-77.750360,'los andes'),
(802,23,'Magüí (Payán)','magui-payan',1.910920,-78.044780,'magui payan'),
(803,23,'Mallama (Piedrancha)','mallama-piedrancha',1.145880,-77.848720,'mallama piedrancha'),
(804,23,'Mosquera','mosquera',2.444960,-78.440840,'mosquera'),
(805,23,'Nariño','narino',1.286510,-77.351440,'narino'),
(806,23,'Olaya Herrera (Bocas de Satinga)','olaya-herrera-bocas-de-satinga',2.276730,-78.315590,'olaya herrera bocas de satinga'),
(807,23,'Ospina','ospina',1.033660,-77.553310,'ospina'),
(808,23,'Pasto','pasto',1.092720,-77.194010,'pasto'),
(809,23,'Policarpa','policarpa',1.750890,-77.467790,'policarpa'),
(810,23,'Potosí','potosi',0.722550,-77.402480,'potosi'),
(811,23,'Providencia','providencia',1.224510,-77.600110,'providencia'),
(812,23,'Puerres','puerres',0.832540,-77.271850,'puerres'),
(813,23,'Pupiales','pupiales',0.907350,-77.637570,'pupiales'),
(814,23,'Ricaurte','ricaurte',1.137260,-78.051020,'ricaurte'),
(815,23,'Roberto Payán (San José)','roberto-payan-san-jose',1.960320,-78.381160,'roberto payan san jose'),
(816,23,'Samaniego','samaniego',1.439310,-77.674710,'samaniego'),
(817,23,'San Bernardo','san-bernardo',1.535130,-77.025330,'san bernardo'),
(818,23,'San Lorenzo','san-lorenzo',1.537470,-77.206760,'san lorenzo'),
(819,23,'San Pablo','san-pablo',1.680060,-76.947020,'san pablo'),
(820,23,'San Pedro de Cartago','san-pedro-de-cartago',1.534880,-77.108400,'san pedro de cartago'),
(821,23,'Sandoná','sandona',1.285010,-77.469430,'sandona'),
(822,23,'Santa Bárbara (Iscuandé)','santa-barbara-iscuande',2.562070,-77.990280,'santa barbara iscuande'),
(823,23,'Santa Cruz (Guachavés)','santa-cruz-guachaves',1.282070,-77.736040,'santa cruz guachaves'),
(824,23,'Sapuyes','sapuyes',1.040230,-77.709650,'sapuyes'),
(825,23,'Taminango','taminango',1.591060,-77.323910,'taminango'),
(826,23,'Tangua','tangua',1.074010,-77.363680,'tangua'),
(827,23,'Tumaco','tumaco',1.537240,-78.609480,'tumaco'),
(828,23,'Tuquerres','tuquerres',1.099360,-77.637650,'tuquerres'),
(829,23,'Yacuanquer','yacuanquer',1.118720,-77.438200,'yacuanquer'),
(830,16,'Abrego','abrego',8.064880,-73.188810,'abrego'),
(831,16,'Arboledas','arboledas',7.601250,-72.911620,'arboledas'),
(832,16,'Bochalema','bochalema',7.598180,-72.673140,'bochalema'),
(833,16,'Bucarasica','bucarasica',8.073470,-72.920340,'bucarasica'),
(834,16,'Chinácota','chinacota',7.620960,-72.588560,'chinacota'),
(835,16,'Chitagà','chitaga',7.024990,-72.463460,'chitaga'),
(836,16,'Convención','convencion',8.942320,-73.196690,'convencion'),
(837,16,'Cucutilla','cucutilla',7.501650,-72.786660,'cucutilla'),
(838,16,'Cáchira','cachira',7.697880,-73.108750,'cachira'),
(839,16,'Cácota','cacota',7.275040,-72.640770,'cacota'),
(840,16,'Cúcuta','cucuta',8.106610,-72.458180,'cucuta'),
(841,16,'Durania','durania',7.742910,-72.676220,'durania'),
(842,16,'El Carmen','el-carmen',8.893070,-73.341490,'el carmen'),
(843,16,'El Tarra','el-tarra',8.669590,-73.018800,'el tarra'),
(844,16,'El Zulia','el-zulia',8.040960,-72.649160,'el zulia'),
(845,16,'Gramalote','gramalote',7.883970,-72.807110,'gramalote'),
(846,16,'Hacarí','hacari',8.352630,-73.076160,'hacari'),
(847,16,'Herrán','herran',7.519590,-72.508960,'herran'),
(848,16,'La Esperanza','la-esperanza',7.683420,-73.344850,'la esperanza'),
(849,16,'La Playa','la-playa',8.257770,-73.205420,'la playa'),
(850,16,'Labateca','labateca',7.234230,-72.509060,'labateca'),
(851,16,'Los Patios','los-patios',7.711390,-72.541610,'los patios'),
(852,16,'Lourdes','lourdes',7.962800,-72.841340,'lourdes'),
(853,16,'Mutiscua','mutiscua',7.310000,-72.766980,'mutiscua'),
(854,16,'Ocaña','ocana',8.208380,-73.343960,'ocana'),
(855,16,'Pamplona','pamplona',7.364350,-72.663990,'pamplona'),
(856,16,'Pamplonita','pamplonita',7.482460,-72.636200,'pamplonita'),
(857,16,'Puerto Santander','puerto-santander',8.335050,-72.414360,'puerto santander'),
(858,16,'Ragonvalia','ragonvalia',7.595880,-72.507590,'ragonvalia'),
(859,16,'Salazar','salazar',7.772320,-72.892160,'salazar'),
(860,16,'San Calixto','san-calixto',8.487260,-73.160660,'san calixto'),
(861,16,'San Cayetano','san-cayetano',7.861290,-72.610770,'san cayetano'),
(862,16,'Santiago','santiago',7.888230,-72.713450,'santiago'),
(863,16,'Sardinata','sardinata',8.260850,-72.859450,'sardinata'),
(864,16,'Silos','silos',7.186710,-72.777180,'silos'),
(865,16,'Teorama','teorama',8.760420,-73.160090,'teorama'),
(866,16,'Tibú','tibu',8.718340,-72.798830,'tibu'),
(867,16,'Toledo','toledo',7.233080,-72.305980,'toledo'),
(868,16,'Villa Caro','villa-caro',7.922920,-72.980180,'villa caro'),
(869,16,'Villa del Rosario','villa-del-rosario',7.670620,-72.495600,'villa del rosario'),
(870,22,'Colón','colon',1.225950,-76.958750,'colon'),
(871,22,'Mocoa','mocoa',1.304760,-76.688370,'mocoa'),
(872,22,'Orito','orito',0.669870,-76.965460,'orito'),
(873,22,'Puerto Asís','puerto-asis',0.473820,-76.502970,'puerto asis'),
(874,22,'Puerto Caicedo','puerto-caicedo',0.727120,-76.418360,'puerto caicedo'),
(875,22,'Puerto Guzmán','puerto-guzman',0.816380,-76.036450,'puerto guzman'),
(876,22,'Puerto Leguízamo','puerto-leguizamo',0.215190,-75.335410,'puerto leguizamo'),
(877,22,'San Francisco','san-francisco',1.132940,-76.849210,'san francisco'),
(878,22,'San Miguel (La Dorada)','san-miguel-la-dorada',0.307260,-76.894710,'san miguel la dorada'),
(879,22,'Santiago','santiago',1.137610,-77.034710,'santiago'),
(880,22,'Sibundoy','sibundoy',1.207450,-76.909810,'sibundoy'),
(881,22,'Valle del Guamuez','valle-del-guamuez',0.407600,-76.804650,'valle del guamuez'),
(882,22,'Villagarzón','villagarzon',0.899220,-76.745740,'villagarzon'),
(883,17,'Armenia','armenia',4.510420,-75.719370,'armenia'),
(884,17,'Buenavista','buenavista',4.362930,-75.740020,'buenavista'),
(885,17,'Calarcá','calarca',4.482390,-75.637790,'calarca'),
(886,17,'Circasia','circasia',4.601300,-75.665880,'circasia'),
(887,17,'Córdoba','cordoba',4.391560,-75.660190,'cordoba'),
(888,17,'Filandia','filandia',4.675520,-75.679050,'filandia'),
(889,17,'Génova','genova',4.174390,-75.760560,'genova'),
(890,17,'La Tebaida','la-tebaida',4.428680,-75.816240,'la tebaida'),
(891,17,'Montenegro','montenegro',4.520880,-75.823730,'montenegro'),
(892,17,'Pijao','pijao',4.299120,-75.681340,'pijao'),
(893,17,'Quimbaya','quimbaya',4.611910,-75.790250,'quimbaya'),
(894,17,'Salento','salento',4.616650,-75.541680,'salento'),
(895,21,'Apía','apia',5.138500,-75.952130,'apia'),
(896,21,'Balboa','balboa',4.916580,-75.942790,'balboa'),
(897,21,'Belén de Umbría','belen-de-umbria',5.203520,-75.869150,'belen de umbria'),
(898,21,'Dosquebradas','dosquebradas',4.845750,-75.669000,'dosquebradas'),
(899,21,'Guática','guatica',5.317650,-75.815310,'guatica'),
(900,21,'La Celia','la-celia',4.992800,-76.008190,'la celia'),
(901,21,'La Virginia','la-virginia',4.904190,-75.852680,'la virginia'),
(902,21,'Marsella','marsella',4.942850,-75.753200,'marsella'),
(903,21,'Mistrató','mistrato',5.424880,-75.959340,'mistrato'),
(904,21,'Pereira','pereira',4.800540,-75.782390,'pereira'),
(905,21,'Pueblo Rico','pueblo-rico',5.275970,-76.059500,'pueblo rico'),
(906,21,'Quinchia','quinchia',5.324640,-75.713340,'quinchia'),
(907,21,'Santa Rosa de Cabal','santa-rosa-de-cabal',4.816120,-75.532620,'santa rosa de cabal'),
(908,21,'Santuario','santuario',5.076450,-76.027120,'santuario'),
(909,33,'Providencia','providencia',13.351240,-81.374360,'providencia'),
(910,33,'San Andrés','san-andres',12.549640,-81.717150,'san andres'),
(911,20,'Aguada','aguada',6.177680,-73.521430,'aguada'),
(912,20,'Albania','albania',5.793180,-73.788920,'albania'),
(913,20,'Aratoca','aratoca',6.694150,-72.996440,'aratoca'),
(914,20,'Barbosa','barbosa',5.956050,-73.623540,'barbosa'),
(915,20,'Barichara','barichara',6.651830,-73.225580,'barichara'),
(916,20,'Barrancabermeja','barrancabermeja',7.075680,-73.763430,'barrancabermeja'),
(917,20,'Betulia','betulia',7.037030,-73.438960,'betulia'),
(918,20,'Bolivar','bolivar',6.027640,-74.186920,'bolivar'),
(919,20,'Bucaramanga','bucaramanga',7.153900,-73.105350,'bucaramanga'),
(920,20,'Cabrera','cabrera',6.560020,-73.253560,'cabrera'),
(921,20,'California','california',7.376470,-72.903610,'california'),
(922,20,'Capitanejo','capitanejo',6.486880,-72.653500,'capitanejo'),
(923,20,'Carcasí','carcasi',6.661870,-72.568360,'carcasi'),
(924,20,'Cepitá','cepita',6.757010,-72.932880,'cepita'),
(925,20,'Cerrito','cerrito',6.896770,-72.636550,'cerrito'),
(926,20,'Charala','charala',6.248520,-73.155930,'charala'),
(927,20,'Charta','charta',7.281550,-72.970360,'charta'),
(928,20,'Chima','chima',6.360410,-73.420270,'chima'),
(929,20,'Chipatá','chipata',6.065180,-73.631680,'chipata'),
(930,20,'Cimitarra','cimitarra',6.438600,-74.170210,'cimitarra'),
(931,20,'Concepción','concepcion',6.761160,-72.662410,'concepcion'),
(932,20,'Confines','confines',6.345200,-73.235310,'confines'),
(933,20,'Contratación','contratacion',6.304380,-73.500650,'contratacion'),
(934,20,'Coromoro','coromoro',6.236600,-72.984360,'coromoro'),
(935,20,'Curití','curiti',6.599520,-73.031010,'curiti'),
(936,20,'El Carmen de Chucuri','el-carmen-de-chucuri',6.662680,-73.561400,'el carmen de chucuri'),
(937,20,'El Guacamayo','el-guacamayo',6.235990,-73.498130,'el guacamayo'),
(938,20,'El Peñón','el-penon',6.071290,-73.859090,'el penon'),
(939,20,'El Playón','el-playon',7.526580,-73.178790,'el playon'),
(940,20,'Encino','encino',6.080580,-73.064020,'encino'),
(941,20,'Enciso','enciso',6.663400,-72.681060,'enciso'),
(942,20,'Floridablanca','floridablanca',7.077030,-73.067020,'floridablanca'),
(943,20,'Florián','florian',5.791480,-73.965880,'florian'),
(944,20,'Galán','galan',6.664270,-73.324750,'galan'),
(945,20,'Girón','giron',6.971570,-73.175380,'giron'),
(946,20,'Guaca','guaca',6.956590,-72.805150,'guaca'),
(947,20,'Guadalupe','guadalupe',6.239350,-73.403870,'guadalupe'),
(948,20,'Guapotá','guapota',6.328290,-73.330470,'guapota'),
(949,20,'Guavatá','guavata',5.947430,-73.722680,'guavata'),
(950,20,'Gámbita','gambita',5.932190,-73.304800,'gambita'),
(951,20,'Güepsa','guepsa',6.040380,-73.572800,'guepsa'),
(952,20,'Hato','hato',6.569390,-73.363280,'hato'),
(953,20,'Jesús María','jesus-maria',5.881420,-73.780320,'jesus maria'),
(954,20,'Jordán Sube','jordan-sube',6.713220,-73.119380,'jordan sube'),
(955,20,'La Belleza','la-belleza',5.892670,-74.018190,'la belleza'),
(956,20,'La Paz','la-paz',6.202630,-73.623540,'la paz'),
(957,20,'Landázuri','landazuri',6.276250,-73.815630,'landazuri'),
(958,20,'Lebríja','lebrija',7.204040,-73.312420,'lebrija'),
(959,20,'Los Santos','los-santos',6.806680,-73.105980,'los santos'),
(960,20,'Macaravita','macaravita',6.511770,-72.581460,'macaravita'),
(961,20,'Matanza','matanza',7.330380,-73.059220,'matanza'),
(962,20,'Mogotes','mogotes',6.462930,-72.970600,'mogotes'),
(963,20,'Molagavita','molagavita',6.641780,-72.818050,'molagavita'),
(964,20,'Málaga','malaga',6.720710,-72.737800,'malaga'),
(965,20,'Ocamonte','ocamonte',6.353910,-73.121360,'ocamonte'),
(966,20,'Oiba','oiba',6.223710,-73.278040,'oiba'),
(967,20,'Onzaga','onzaga',6.323400,-72.821750,'onzaga'),
(968,20,'Palmar','palmar',6.533600,-73.284080,'palmar'),
(969,20,'Palmas del Socorro','palmas-del-socorro',6.396180,-73.286820,'palmas del socorro'),
(970,20,'Piedecuesta','piedecuesta',6.983510,-73.033590,'piedecuesta'),
(971,20,'Pinchote','pinchote',6.517070,-73.175030,'pinchote'),
(972,20,'Puente Nacional','puente-nacional',5.830990,-73.679590,'puente nacional'),
(973,20,'Puerto Parra','puerto-parra',6.687050,-73.975320,'puerto parra'),
(974,20,'Puerto Wilches','puerto-wilches',7.323500,-73.771620,'puerto wilches'),
(975,20,'Páramo','paramo',6.416990,-73.180000,'paramo'),
(976,20,'Rionegro','rionegro',7.491630,-73.395880,'rionegro'),
(977,20,'Sabana de Torres','sabana-de-torres',7.395420,-73.550700,'sabana de torres'),
(978,20,'San Andres','san-andres',6.784560,-72.810080,'san andres'),
(979,20,'San Benito','san-benito',6.095230,-73.532770,'san benito'),
(980,20,'San Gil','san-gil',6.523830,-73.097440,'san gil'),
(981,20,'San Joaquín','san-joaquin',6.456080,-72.846210,'san joaquin'),
(982,20,'San José de Miranda','san-jose-de-miranda',6.626120,-72.737140,'san jose de miranda'),
(983,20,'San Miguel','san-miguel',6.562000,-72.633930,'san miguel'),
(984,20,'San Vicente de Chucuri','san-vicente-de-chucuri',6.898490,-73.535960,'san vicente de chucuri'),
(985,20,'Santa Barbara','santa-barbara',6.994430,-72.888810,'santa barbara'),
(986,20,'Santa Helena del Opón','santa-helena-del-opon',6.410140,-73.579980,'santa helena del opon'),
(987,20,'Simacota','simacota',6.790630,-73.832510,'simacota'),
(988,20,'Socorro','socorro',6.463610,-73.244360,'socorro'),
(989,20,'Suaita','suaita',6.103000,-73.368250,'suaita'),
(990,20,'Sucre','sucre',6.007860,-73.962160,'sucre'),
(991,20,'Suratá','surata',7.473340,-72.979780,'surata'),
(992,20,'Tona','tona',7.169980,-72.937490,'tona'),
(993,20,'Valle de San Jose','valle-de-san-jose',6.433560,-73.110880,'valle de san jose'),
(994,20,'Vetas','vetas',7.322880,-72.881600,'vetas'),
(995,20,'Villanueva','villanueva',6.668470,-73.155360,'villanueva'),
(996,20,'Vélez','velez',6.119290,-73.693210,'velez'),
(997,20,'Zapatoca','zapatoca',6.793570,-73.277580,'zapatoca'),
(998,11,'Buenavista','buenavista',9.316830,-74.966700,'buenavista'),
(999,11,'Caimito','caimito',8.796150,-75.140420,'caimito'),
(1000,11,'Chalán','chalan',9.567550,-75.321150,'chalan'),
(1001,11,'Corozal','corozal',9.155580,-75.213300,'corozal'),
(1002,11,'Coveñas','covenas',9.383030,-75.665120,'covenas'),
(1003,11,'El Roble','el-roble',9.071010,-75.097720,'el roble'),
(1004,11,'Galeras','galeras',9.134810,-74.983160,'galeras'),
(1005,11,'Guaranda','guaranda',8.337380,-74.686660,'guaranda'),
(1006,11,'La Unión','la-union',8.807670,-75.289190,'la union'),
(1007,11,'Los Palmitos','los-palmitos',9.420670,-75.214540,'los palmitos'),
(1008,11,'Majagual','majagual',8.546900,-74.693880,'majagual'),
(1009,11,'Morroa','morroa',9.394790,-75.326130,'morroa'),
(1010,11,'Ovejas','ovejas',9.541100,-75.186110,'ovejas'),
(1011,11,'Palmito','palmito',9.334490,-75.555600,'palmito'),
(1012,11,'Ricaurte (Coloso)','ricaurte-coloso',9.495510,-75.354040,'ricaurte coloso'),
(1013,11,'Sampués','sampues',9.173860,-75.362100,'sampues'),
(1014,11,'San Benito Abad','san-benito-abad',8.911340,-74.982820,'san benito abad'),
(1015,11,'San Juan de Betulia','san-juan-de-betulia',9.248260,-75.231090,'san juan de betulia'),
(1016,11,'San Luis de Sincé','san-luis-de-since',9.262830,-75.080770,'san luis de since'),
(1017,11,'San Marcos','san-marcos',8.595310,-75.176360,'san marcos'),
(1018,11,'San Onofre','san-onofre',9.781930,-75.492610,'san onofre'),
(1019,11,'San Pedro','san-pedro',9.394750,-75.039020,'san pedro'),
(1020,11,'Santiago de Tolú','santiago-de-tolu',9.530720,-75.534090,'santiago de tolu'),
(1021,11,'Sincelejo','sincelejo',9.310310,-75.430020,'sincelejo'),
(1022,11,'Sucre','sucre',8.869610,-74.734680,'sucre'),
(1023,11,'Tolú Viejo','tolu-viejo',9.452090,-75.457330,'tolu viejo'),
(1024,5,'Alpujarra','alpujarra',3.389400,-74.938200,'alpujarra'),
(1025,5,'Alvarado','alvarado',4.583390,-74.984710,'alvarado'),
(1026,5,'Ambalema','ambalema',4.790690,-74.817530,'ambalema'),
(1027,5,'Anzoátegui','anzoategui',4.623930,-75.203790,'anzoategui'),
(1028,5,'Armero','armero',5.000250,-74.832250,'armero'),
(1029,5,'Ataco','ataco',3.393410,-75.541500,'ataco'),
(1030,5,'Cajamarca','cajamarca',4.417730,-75.499190,'cajamarca'),
(1031,5,'Carmen de Apicala','carmen-de-apicala',4.117430,-74.740750,'carmen de apicala'),
(1032,5,'Casabianca','casabianca',5.019880,-75.176150,'casabianca'),
(1033,5,'Chaparral','chaparral',3.735800,-75.594880,'chaparral'),
(1034,5,'Coello','coello',4.306250,-74.939810,'coello'),
(1035,5,'Coyaima','coyaima',3.778030,-75.118120,'coyaima'),
(1036,5,'Cunday','cunday',4.010100,-74.691650,'cunday'),
(1037,5,'Dolores','dolores',3.580990,-74.822100,'dolores'),
(1038,5,'Espinal','espinal',4.163790,-74.903450,'espinal'),
(1039,5,'Falán','falan',5.077650,-74.940320,'falan'),
(1040,5,'Flandes','flandes',4.242620,-74.830620,'flandes'),
(1041,5,'Fresno','fresno',5.186230,-75.045710,'fresno'),
(1042,5,'Guamo','guamo',4.062950,-74.978370,'guamo'),
(1043,5,'Herveo','herveo',5.062620,-75.250840,'herveo'),
(1044,5,'Honda','honda',5.166970,-74.781740,'honda'),
(1045,5,'Ibagué','ibague',4.455490,-75.271320,'ibague'),
(1046,5,'Icononzo','icononzo',4.127330,-74.532280,'icononzo'),
(1047,5,'Lérida','lerida',4.851660,-74.914500,'lerida'),
(1048,5,'Líbano','libano',4.879620,-75.049050,'libano'),
(1049,5,'Melgar','melgar',4.176710,-74.614750,'melgar'),
(1050,5,'Murillo','murillo',4.842500,-75.201850,'murillo'),
(1051,5,'Natagaima','natagaima',3.544110,-75.106690,'natagaima'),
(1052,5,'Ortega','ortega',3.932760,-75.290830,'ortega'),
(1053,5,'Palocabildo','palocabildo',5.088470,-75.017660,'palocabildo'),
(1054,5,'Piedras','piedras',4.434940,-74.958500,'piedras'),
(1055,5,'Planadas','planadas',3.086800,-75.811770,'planadas'),
(1056,5,'Prado','prado',3.728440,-74.883120,'prado'),
(1057,5,'Purificación','purificacion',3.870580,-74.898980,'purificacion'),
(1058,5,'Rioblanco','rioblanco',3.463670,-75.850800,'rioblanco'),
(1059,5,'Roncesvalles','roncesvalles',4.098440,-75.591500,'roncesvalles'),
(1060,5,'Rovira','rovira',4.215260,-75.326470,'rovira'),
(1061,5,'Saldaña','saldana',3.922790,-75.002270,'saldana'),
(1062,5,'San Antonio','san-antonio',3.925910,-75.509250,'san antonio'),
(1063,5,'San Luis','san-luis',4.063150,-75.138380,'san luis'),
(1064,5,'San Sebastián de Mariquita','san-sebastian-de-mariquita',5.230770,-74.908810,'san sebastian de mariquita'),
(1065,5,'Santa Isabel','santa-isabel',4.744140,-75.222740,'santa isabel'),
(1066,5,'Suárez','suarez',4.004770,-74.825120,'suarez'),
(1067,5,'Valle de San Juan','valle-de-san-juan',4.181540,-75.171500,'valle de san juan'),
(1068,5,'Venadillo','venadillo',4.733140,-74.934680,'venadillo'),
(1069,5,'Villahermosa','villahermosa',4.956500,-75.143030,'villahermosa'),
(1070,5,'Villarrica','villarrica',3.850970,-74.617370,'villarrica'),
(1071,15,'Alcalá','alcala',4.678820,-75.784200,'alcala'),
(1072,15,'Andalucía','andalucia',4.163450,-76.181120,'andalucia'),
(1073,15,'Ansermanuevo','ansermanuevo',4.791650,-76.031440,'ansermanuevo'),
(1074,15,'Argelia','argelia',4.704390,-76.141630,'argelia'),
(1075,15,'Bolívar','bolivar',4.392890,-76.347110,'bolivar'),
(1076,15,'Buenaventura','buenaventura',3.419790,-77.056780,'buenaventura'),
(1077,15,'Buga','buga',3.824300,-76.020240,'buga'),
(1078,15,'Bugalagrande','bugalagrande',4.180560,-76.066070,'bugalagrande'),
(1079,15,'Caicedonia','caicedonia',4.298300,-75.837730,'caicedonia'),
(1080,15,'Cali','cali',3.404540,-76.574060,'cali'),
(1081,15,'Calima','calima',3.918440,-76.631520,'calima'),
(1082,15,'Candelaria','candelaria',3.375140,-76.394740,'candelaria'),
(1083,15,'Cartago','cartago',4.703460,-75.919410,'cartago'),
(1084,15,'Dagua','dagua',3.584240,-76.730870,'dagua'),
(1085,15,'El Cairo','el-cairo',4.744100,-76.228800,'el cairo'),
(1086,15,'El Cerrito','el-cerrito',3.655810,-76.153810,'el cerrito'),
(1087,15,'El Dovio','el-dovio',4.540330,-76.297630,'el dovio'),
(1088,15,'El Águila','el-aguila',4.909060,-76.058500,'el aguila'),
(1089,15,'Florida','florida',3.304450,-76.128290,'florida'),
(1090,15,'Ginebra','ginebra',3.741340,-76.182650,'ginebra'),
(1091,15,'Guacarí','guacari',3.800190,-76.248250,'guacari'),
(1092,15,'Jamundí','jamundi',3.211720,-76.624450,'jamundi'),
(1093,15,'La Cumbre','la-cumbre',3.688700,-76.582680,'la cumbre'),
(1094,15,'La Unión','la-union',4.537870,-76.103390,'la union'),
(1095,15,'La Victoria','la-victoria',4.476980,-75.959210,'la victoria'),
(1096,15,'Obando','obando',4.598740,-75.949150,'obando'),
(1097,15,'Palmira','palmira',3.549090,-76.223950,'palmira'),
(1098,15,'Pradera','pradera',3.415990,-76.196490,'pradera'),
(1099,15,'Restrepo','restrepo',3.806700,-76.534870,'restrepo'),
(1100,15,'Riofrío','riofrio',4.108360,-76.372970,'riofrio'),
(1101,15,'Roldanillo','roldanillo',4.433260,-76.177000,'roldanillo'),
(1102,15,'San Pedro','san-pedro',3.983130,-76.209850,'san pedro'),
(1103,15,'Sevilla','sevilla',4.228610,-75.913180,'sevilla'),
(1104,15,'Toro','toro',4.638130,-76.079940,'toro'),
(1105,15,'Trujillo','trujillo',4.242500,-76.352760,'trujillo'),
(1106,15,'Tuluá','tulua',4.005520,-75.991380,'tulua'),
(1107,15,'Ulloa','ulloa',4.708780,-75.779450,'ulloa'),
(1108,15,'Versalles','versalles',4.637130,-76.217970,'versalles'),
(1109,15,'Vijes','vijes',3.733050,-76.477290,'vijes'),
(1110,15,'Yotoco','yotoco',3.910870,-76.392300,'yotoco'),
(1111,15,'Yumbo','yumbo',3.597630,-76.512260,'yumbo'),
(1112,15,'Zarzal','zarzal',4.358670,-76.052770,'zarzal'),
(1113,19,'Carurú','caruru',1.018750,-71.257270,'caruru'),
(1114,19,'Mitú','mitu',1.014500,-70.547260,'mitu'),
(1115,19,'Pacoa (Cor. Departamental)','pacoa-cor-departamental',0.036760,-70.590540,'pacoa cor departamental'),
(1116,19,'Papunaua(cor. Departamental)','papunaua-cor-departamental',1.705560,-70.811590,'papunaua cor departamental'),
(1117,19,'Taraira','taraira',-0.639970,-69.952510,'taraira'),
(1118,19,'Yavaraté  (Cor. Departamental)','yavarate-cor-departamental',0.872700,-69.660740,'yavarate cor departamental'),
(1119,18,'Cumaribo','cumaribo',4.304060,-69.545970,'cumaribo'),
(1120,18,'La Primavera','la-primavera',5.582930,-69.608810,'la primavera'),
(1121,18,'Puerto Carreño','puerto-carreno',5.824020,-68.141170,'puerto carreno'),
(1122,18,'Santa Rosalía','santa-rosalia',5.029600,-70.644970,'santa rosalia');
/*!40000 ALTER TABLE `municipalities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(190) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `regions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `color` varchar(9) NOT NULL DEFAULT '#0f9d77',
  `summary` varchar(400) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `regions_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES
(1,'Caribe','caribe','#12b5b0','Mar, ciénagas, sabanas y una cultura que se baila.',0),
(2,'Andina','andina','#2bb673','Montañas, páramos, café y pueblos coloniales.',1),
(3,'Pacífica','pacifica','#1d6fe0','Selva húmeda, ballenas, marimba y sabores de mar.',2),
(4,'Orinoquía','orinoquia','#ff7a1a','Llanuras infinitas, ríos y atardeceres de joropo.',3),
(5,'Amazonía','amazonia','#0f5c4a','El pulmón verde: ríos, selva y saberes ancestrales.',4),
(6,'Insular','insular','#7b3fe4','Islas de mar de siete colores y cultura raizal.',5);
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `route_stops`
--

DROP TABLE IF EXISTS `route_stops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `route_stops` (
  `route_id` bigint(20) unsigned NOT NULL,
  `entry_id` bigint(20) unsigned NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`route_id`,`entry_id`),
  KEY `route_stops_entry_id_foreign` (`entry_id`),
  CONSTRAINT `route_stops_entry_id_foreign` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `route_stops_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `route_stops`
--

LOCK TABLES `route_stops` WRITE;
/*!40000 ALTER TABLE `route_stops` DISABLE KEYS */;
INSERT INTO `route_stops` VALUES
(1,1,0,NULL),
(1,2,1,NULL),
(1,6,2,NULL),
(1,18,3,NULL),
(2,4,0,NULL),
(2,10,2,NULL),
(2,38,1,NULL),
(3,5,1,NULL),
(3,11,0,NULL),
(3,20,2,NULL);
/*!40000 ALTER TABLE `route_stops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `routes`
--

DROP TABLE IF EXISTS `routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `routes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `summary` varchar(400) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `duration` varchar(60) DEFAULT NULL,
  `cover_media_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `routes_slug_unique` (`slug`),
  KEY `routes_cover_media_id_foreign` (`cover_media_id`),
  KEY `routes_created_by_foreign` (`created_by`),
  CONSTRAINT `routes_cover_media_id_foreign` FOREIGN KEY (`cover_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `routes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `routes`
--

LOCK TABLES `routes` WRITE;
/*!40000 ALTER TABLE `routes` DISABLE KEYS */;
INSERT INTO `routes` VALUES
(1,'Caribe esencial','caribe-esencial','De la ciudad amurallada a las playas del Tayrona y el desierto que toca el mar.','Ruta sugerida de demostración. Ajusta el orden y los días según tu ritmo y confirma el estado de las vías antes de viajar.','7 a 10 días',2,'published',1,1,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(2,'Eje Cafetero: montaña y taza','eje-cafetero-montana-y-taza','Palmas de cera, pueblos de bahareque y fincas donde nace el café.','Ruta sugerida de demostración. Ajusta el orden y los días según tu ritmo y confirma el estado de las vías antes de viajar.','4 a 6 días',7,'published',1,1,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09'),
(3,'Sur mágico','sur-magico','Estatuas milenarias, un desierto bajo las estrellas y cascadas en el piedemonte amazónico.','Ruta sugerida de demostración. Ajusta el orden y los días según tu ritmo y confirma el estado de las vías antes de viajar.','6 a 8 días',21,'published',1,1,NULL,'2026-10-27 11:18:09','2026-10-27 11:18:09');
/*!40000 ALTER TABLE `routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(20) NOT NULL,
  `title` varchar(80) NOT NULL,
  `tagline` varchar(160) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `cover_media_id` bigint(20) unsigned DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `show_on_home` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sections_section_key_unique` (`section_key`),
  KEY `sections_cover_media_id_foreign` (`cover_media_id`),
  CONSTRAINT `sections_cover_media_id_foreign` FOREIGN KEY (`cover_media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES
(1,'tourism','Turismo','Selva, mar, montaña y desierto en un solo mapa','Explora destinos por departamento y municipio, filtra por experiencias y arma tu ruta.',5,0,1),
(2,'gastronomy','Gastronomía','Un país que se cuenta en la mesa','Platos, bebidas, ingredientes y tradiciones de cada región.',29,1,1),
(3,'coffee','Café','Del grano a la taza','Fincas, productores, procesos, catas y rutas cafeteras.',7,2,1),
(4,'entrepreneurship','Emprendimiento','Hecho en los territorios','Proyectos locales de turismo, artesanía, gastronomía y café.',49,3,1),
(5,'culture','Cultura','Fiestas, música y saberes vivos','Festividades, danzas, artesanías y patrimonio de las comunidades.',46,4,1),
(6,'gallery','Galería de fotos','Colombia en imágenes','Paisajes, rostros y colores en un mosaico para recorrer.',4,5,1),
(7,'news','Noticias','Lo que está pasando en los territorios','Eventos, festividades, novedades e historias para viajar informado.',22,6,1);
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(190) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES
('hero_media_ids','[1]','2026-11-05 08:47:13'),
('hero_spotlight_id','3','2026-11-05 08:47:13'),
('hero_video_id','53','2026-11-05 08:47:13');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'editor',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed
