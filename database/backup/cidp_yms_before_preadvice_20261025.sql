-- MariaDB dump 10.19  Distrib 10.4.22-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: cidp_yms
-- ------------------------------------------------------
-- Server version	10.4.22-MariaDB

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
-- Table structure for table `billing_invoices`
--

DROP TABLE IF EXISTS `billing_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(30) NOT NULL,
  `container_number` varchar(20) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `dwell_days` int(11) DEFAULT 1,
  `lolo_amount` decimal(12,2) DEFAULT 0.00,
  `storage_amount` decimal(12,2) DEFAULT 0.00,
  `reefer_power_amount` decimal(12,2) DEFAULT 0.00,
  `cfs_amount` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL,
  `payment_status` enum('PAID','PENDING','OVERDUE') DEFAULT 'PENDING',
  `payment_method` varchar(50) DEFAULT 'VA Mandiri / BNI',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_invoices`
--

LOCK TABLES `billing_invoices` WRITE;
/*!40000 ALTER TABLE `billing_invoices` DISABLE KEYS */;
INSERT INTO `billing_invoices` VALUES (1,'INV-2026-10-001','MSKU9821450','PT Samsung Electronics Indonesia','Laden Impor Full Handling',2,450000.00,300000.00,0.00,750000.00,1500000.00,'PAID','Virtual Account BCA','2026-10-01 09:15:00','2026-10-01 14:31:53'),(2,'INV-2026-10-002','TCKU8291048','PT Chandra Asri Petrochemical','Laden Impor Lift-Off & Storage',1,350000.00,150000.00,0.00,0.00,500000.00,'PAID','Bank Mandiri Corporate','2026-10-01 10:20:00','2026-10-01 14:31:53'),(3,'INV-2026-10-003','SEGU1982731','PT Pou Yuen Indonesia (Nike Export)','Ekspor Lo-Lo & SOLAS VGM Weighed',1,450000.00,150000.00,0.00,0.00,675000.00,'PAID','VA Bank Permata','2026-10-01 11:45:00','2026-10-01 14:31:53'),(4,'INV-2026-10-004','CMAU7234910','PT Kalbe Farma Tbk','Reefer Power & Temperature Monitoring',1,450000.00,150000.00,650000.00,0.00,1250000.00,'PAID','Corporate Direct Debit','2026-10-01 12:10:00','2026-10-01 14:31:53'),(5,'INV-2026-10-005','COSU6019283','PT Trina Solar Cells Indonesia','Ekspor Lo-Lo & Storage Depot',2,450000.00,300000.00,0.00,0.00,750000.00,'PENDING','Pending Payment',NULL,'2026-10-01 14:31:53'),(6,'INV-2026-10-006','ONEU6612988','PT Epson Indonesia','Jasa Pemeriksaan Fisik Jalur Merah',3,450000.00,450000.00,0.00,350000.00,1250000.00,'PENDING','Pending Payment',NULL,'2026-10-01 14:31:53'),(7,'INV-W-20261016-7a32','MSKU9182374','PT Samudera Logistik Prima','Penimbangan Gantry 80T SOLAS VGM',1,0.00,0.00,0.00,0.00,75000.00,'PAID','Bank Transfer (Mandiri H2H)','2026-10-16 15:28:31','2026-10-16 15:24:12'),(8,'INV-L-20261016-3e45','MSKU9182374','PT Samudera Logistik Prima','Lift-Off Heavy Equipment Stacking',1,325000.00,0.00,0.00,0.00,325000.00,'PAID','Bank Transfer (Mandiri H2H)','2026-10-16 15:28:31','2026-10-16 15:25:08');
/*!40000 ALTER TABLE `billing_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cfs_jobs`
--

DROP TABLE IF EXISTS `cfs_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfs_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_number` varchar(30) NOT NULL,
  `job_type` enum('STRIPPING','STUFFING','CROSS_DOCK') NOT NULL,
  `container_number` varchar(20) NOT NULL,
  `dock_ramp` varchar(10) NOT NULL,
  `shipper_consignee` varchar(150) DEFAULT NULL,
  `bl_number` varchar(50) DEFAULT NULL,
  `total_packages` int(11) DEFAULT 0,
  `total_weight_kg` decimal(10,2) DEFAULT 0.00,
  `total_cbm` decimal(8,2) DEFAULT 0.00,
  `customs_status` varchar(30) DEFAULT 'SPPB_CLEARED',
  `status` enum('PENDING','IN_PROGRESS','COMPLETED','HOLD') DEFAULT 'IN_PROGRESS',
  `operator_name` varchar(100) DEFAULT 'Eko Prasetyo',
  `start_time` datetime DEFAULT NULL,
  `completed_time` datetime DEFAULT NULL,
  `tally_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_number` (`job_number`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cfs_jobs`
--

LOCK TABLES `cfs_jobs` WRITE;
/*!40000 ALTER TABLE `cfs_jobs` DISABLE KEYS */;
INSERT INTO `cfs_jobs` VALUES (1,'CFS-JOB-2610-01','STRIPPING','MSKU9821450','Dock 02','PT Samsung Electronics Indonesia','BL-MAEU-982104',450,18500.00,48.50,'SPPB_CLEARED','COMPLETED','Tally Master Joko','2026-10-01 08:30:00','2026-10-13 23:16:00','Pembongkaran palet IC & Semiconductor berjalan lancar, segel intak.','2026-10-01 14:31:53'),(2,'CFS-JOB-2610-02','STUFFING','SEGU1982731','Dock 04','PT Pou Yuen Indonesia (Nike)','BL-MSCU-198273',820,24500.00,62.00,'SPPB_CLEARED','IN_PROGRESS','Tally Master Bambang','2026-10-01 10:45:00',NULL,'Pemuatan karton sepatu ekspor Nike ke Los Angeles, dipasangi dunnage bag.','2026-10-01 14:31:53'),(3,'CFS-JOB-2610-03','CROSS_DOCK','CMAU7234910','Dock 01','PT Kalbe Farma Tbk','BL-CMA-723491',120,12400.00,28.00,'SPPB_CLEARED','COMPLETED','Tally Master Rudi','2026-10-01 09:00:00','2026-10-13 23:16:40','Cross-docking langsung vaksin reefer ke armada berpendingin Kalbe.\n[2026-10-13 23:16:04] Update tally: Ditambahkan 5 palet. Catatan: \n[2026-10-13 23:16:08] Update tally: Ditambahkan 5 palet. Catatan: \n[2026-10-13 23:16:17] Update tally: Ditambahkan 5 palet. Catatan: \n[2026-10-13 23:16:21] Update tally: Ditambahkan 5 palet. Catatan: ','2026-10-01 14:31:53');
/*!40000 ALTER TABLE `cfs_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cfs_pallets`
--

DROP TABLE IF EXISTS `cfs_pallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfs_pallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) DEFAULT NULL,
  `pallet_sscc` varchar(35) NOT NULL,
  `container_origin` varchar(20) DEFAULT NULL,
  `commodity_name` varchar(150) NOT NULL,
  `consignee` varchar(150) NOT NULL,
  `category` enum('machinery','electronics','fmcg','textile','automotive','general') DEFAULT 'general',
  `carton_count` int(11) DEFAULT 1,
  `gross_weight_kg` decimal(10,2) DEFAULT 0.00,
  `cbm` decimal(8,2) DEFAULT 0.00,
  `rack_location` varchar(25) NOT NULL,
  `customs_doc` varchar(50) DEFAULT 'SPPB-CEISA',
  `customs_status` enum('CLEARED','HOLD','RED_LANE') DEFAULT 'CLEARED',
  `status` enum('IN_RACK','STAGING_OUT','DISPATCHED','HOLD') DEFAULT 'IN_RACK',
  `entry_date` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pallet_sscc` (`pallet_sscc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cfs_pallets`
--

LOCK TABLES `cfs_pallets` WRITE;
/*!40000 ALTER TABLE `cfs_pallets` DISABLE KEYS */;
/*!40000 ALTER TABLE `cfs_pallets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `containers`
--

DROP TABLE IF EXISTS `containers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `containers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `container_number` varchar(20) NOT NULL COMMENT 'Nomor kontainer ISO 6346 (mis: MSKU9182374)',
  `iso_code` varchar(10) DEFAULT '42G1' COMMENT 'Kode ISO ukuran/tipe',
  `size_type` varchar(30) DEFAULT '40FT HIGH CUBE',
  `cargo_type` enum('dry','reefer','dg','empty') DEFAULT 'dry' COMMENT 'Tipe kargo',
  `rfid_tag` varchar(50) DEFAULT NULL COMMENT 'ID Tag RFID UHF ISO 18000-6C',
  `sscc_code` varchar(25) DEFAULT NULL COMMENT 'Serial Shipping Container Code (SSCC-18)',
  `gross_weight_kg` decimal(10,2) DEFAULT 0.00,
  `owner_company` varchar(100) DEFAULT NULL COMMENT 'Perusahaan pemilik/pengirim',
  `seal_number` varchar(30) DEFAULT NULL,
  `customs_status` varchar(30) DEFAULT 'SPPB_CLEARED',
  `block` varchar(10) DEFAULT NULL COMMENT 'Blok penumpukan (A, B, C, D, E, REEFER, DG)',
  `bay` varchar(5) DEFAULT NULL COMMENT 'Posisi Bay (01-20)',
  `row` varchar(5) DEFAULT NULL COMMENT 'Posisi Row (01-06)',
  `tier` varchar(5) DEFAULT NULL COMMENT 'Posisi Tier/tingkat (01-04)',
  `gate_in_time` datetime DEFAULT NULL COMMENT 'Waktu masuk gate',
  `gate_out_time` datetime DEFAULT NULL,
  `status` enum('in_yard','in_transit','gate_out','on_rail') DEFAULT 'in_yard',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `containers`
--

LOCK TABLES `containers` WRITE;
/*!40000 ALTER TABLE `containers` DISABLE KEYS */;
INSERT INTO `containers` VALUES (1,'MSKU9821450','45G1','40HC High Cube','dry','RFID-CN-001','089912345000000018',31450.00,'Maersk Line','ML-ID-98211','SPPB_CLEARED','A','01','01','01','2026-10-01 07:15:00',NULL,'in_yard','2026-10-01 14:31:53'),(2,'TCKU8291048','22G1','20GP Standard','dry','RFID-CN-002','089912345000000025',21500.00,'CNC Line','CNC-ID-82910','SPPB_CLEARED','A','01','01','02','2026-10-13 23:14:33','2026-10-13 23:32:53','gate_out','2026-10-01 14:31:53'),(3,'EMCU5518200','22G1','20GP Standard','dry','RFID-CN-003','089912345000000032',19800.00,'Evergreen Marine','EMC-JKT-55182','SPPB_CLEARED','A','01','02','01','2026-10-01 08:00:00',NULL,'in_yard','2026-10-01 14:31:53'),(4,'ONEU6612988','42G1','40GP Standard','dry','RFID-CN-004','089912345000000049',28900.00,'Ocean Network Express','ONE-ID-66129','RED_LANE_CHECK','B','05','03','01','2026-10-01 08:45:00',NULL,'in_yard','2026-10-01 14:31:53'),(5,'TGHU7718290','22G1','20GP Standard','dry','RFID-CN-005','089912345000000056',22400.00,'Yang Ming Line','YML-ID-77182','PLP_TRANSIT','B','05','03','02','2026-10-01 09:10:00',NULL,'in_yard','2026-10-01 14:31:53'),(6,'SEGU1982731','45G1','40HC High Cube','dry','RFID-CN-006','089912345000000063',26850.00,'Mediterranean Shipping Co','MSC-EXP-19827','SPPB_CLEARED','A','01','01','03','2026-10-13 18:27:45',NULL,'in_yard','2026-10-01 14:31:53'),(7,'TEMU4910285','22G1','20GP Standard','dry','RFID-CN-007','089912345000000070',18400.00,'Tex Tainer','TEX-EXP-49102','SPPB_CLEARED','C','10','01','01','2026-10-01 10:00:00',NULL,'in_yard','2026-10-01 14:31:53'),(8,'MEDU7731902','22G1','20GP Standard','dry','RFID-CN-008','089912345000000087',22100.00,'MSC Line','MED-EXP-77319','SPPB_CLEARED','C','10','01','02','2026-10-01 10:15:00',NULL,'in_yard','2026-10-01 14:31:53'),(9,'TLLU5529103','45G1','40HC High Cube','dry','RFID-CN-009','089912345000000094',29300.00,'Trans Liner','TLL-EXP-55291','SPPB_CLEARED','C','10','02','01','2026-10-01 11:00:00',NULL,'in_yard','2026-10-01 14:31:53'),(10,'COSU6019283','45G1','40HC High Cube','dry','RFID-CN-010','089912345000000100',31200.00,'COSCO Shipping','COS-EXP-60192','SPPB_CLEARED','C','11','01','01','2026-10-01 11:30:00',NULL,'in_yard','2026-10-01 14:31:53'),(11,'MSKU1109288','45G1','40HC High Cube','dry','RFID-CN-011','089912345000000117',30150.00,'Maersk Line','MSK-EXP-11092','SPPB_CLEARED','A','01','02','02','2026-10-13 21:37:37',NULL,'in_yard','2026-10-01 14:31:53'),(12,'SUDU3109284','22G1','20GP Standard','empty','RFID-CN-012','089912345000000124',2250.00,'Hamburg Sud','NONE-EMPTY','SPPB_CLEARED','A','01','01','04','2026-10-13 18:29:21',NULL,'in_yard','2026-10-01 14:31:53'),(13,'KKFU1928016','22G1','20GP Standard','empty','RFID-CN-013','089912345000000131',2300.00,'K-Line','NONE-EMPTY','SPPB_CLEARED','REEFER','02','01','01','2026-09-30 09:20:00',NULL,'in_yard','2026-10-01 14:31:53'),(14,'FSCU6628107','22G1','20GP Standard','empty','RFID-CN-014','089912345000000148',2200.00,'Florens Container','NONE-EMPTY','SPPB_CLEARED','REEFER','02','02','01','2026-09-30 16:30:00',NULL,'in_yard','2026-10-01 14:31:53'),(15,'NYKU3918201','22G1','20GP Standard','empty','RFID-CN-015','089912345000000155',2220.00,'NYK Line','NONE-EMPTY','SPPB_CLEARED','DG','01','01','01','2026-10-01 06:10:00',NULL,'in_yard','2026-10-01 14:31:53'),(16,'CMAU7234910','45R1','40RF High Cube Reefer','reefer','RFID-CN-016','089912345000000162',29100.00,'CMA CGM','CMA-RF-72349','SPPB_CLEARED','DG','01','02','01','2026-10-01 08:15:00',NULL,'in_yard','2026-10-01 14:31:53'),(17,'HASU8820194','45R1','40RF High Cube Reefer','reefer','RFID-CN-017','089912345000000179',27900.00,'Hapag-Lloyd','HL-RF-88201','SPPB_CLEARED','EMPTY','01','01','01','2026-10-01 09:40:00',NULL,'in_yard','2026-10-01 14:31:53'),(18,'HLXU4401923','20TK','20ft ISO Tank Container','dg','RFID-CN-018','089912345000000186',24800.00,'Hapag-Lloyd','DG-IMO-44019','DOC_HOLD','EMPTY','01','01','02','2026-10-01 10:20:00',NULL,'in_yard','2026-10-01 14:31:53'),(19,'EGHU9910245','45G1','40HC High Cube','dry','RFID-CN-019','089912345000000193',30200.00,'Evergreen Marine','EMC-OUT-99102','SPPB_CLEARED','EMPTY','01','01','03','2026-10-01 06:00:00','2026-10-01 11:30:00','in_yard','2026-10-01 14:31:53'),(22,'MSCU9918294','45G1','40FT HIGH CUBE','dry',NULL,NULL,28500.00,'Mediterranean Shipping',NULL,'SPPB_CLEARED','B','01','01','02',NULL,NULL,'in_yard','2026-10-13 23:31:49'),(23,'MSKU9182374','42G1','40FT HIGH CUBE','dry','E28011700000020489110011',NULL,34850.00,'PT Samudera Logistik Prima',NULL,'SPPB_CLEARED','B','08','03','02','2026-10-16 15:22:45',NULL,'in_yard','2026-10-16 15:22:45'),(24,'MSKU4419283','42G1','40FT HIGH CUBE','dry','E280412121383525','(00)3899123453090000',26400.00,'PT Kereta Api Logistik',NULL,'SPPB_CLEARED','A','01','01','02',NULL,NULL,'in_yard','2026-10-20 19:06:07');
/*!40000 ALTER TABLE `containers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_id` varchar(20) NOT NULL COMMENT 'Kode alat (RS-01, RS-02, RS-03, RTG-01)',
  `equipment_type` varchar(30) DEFAULT 'REACH_STACKER',
  `brand_model` varchar(50) DEFAULT 'Kalmar DRG450-65S5',
  `operator_name` varchar(100) DEFAULT NULL COMMENT 'Nama operator aktif',
  `operator_id` varchar(20) DEFAULT NULL COMMENT 'ID operator',
  `status` enum('idle','operating','carrying','maintenance') DEFAULT 'idle',
  `gps_x` decimal(8,2) DEFAULT 0.00 COMMENT 'Posisi X di denah yard (piksel/grid)',
  `gps_y` decimal(8,2) DEFAULT 0.00 COMMENT 'Posisi Y di denah yard (piksel/grid)',
  `current_container` varchar(20) DEFAULT NULL COMMENT 'Kontainer sedang diangkut',
  `last_block` varchar(10) DEFAULT NULL COMMENT 'Blok terakhir dikunjungi',
  `fuel_percent` int(11) DEFAULT 85,
  `hours_today` decimal(4,1) DEFAULT 0.0,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment`
--

LOCK TABLES `equipment` WRITE;
/*!40000 ALTER TABLE `equipment` DISABLE KEYS */;
INSERT INTO `equipment` VALUES (1,'RS-01','REACH_STACKER','Kalmar DRG450-65S5','Budi Santoso','OP-001','idle',0.00,0.00,NULL,'A',92,0.0,'2026-10-13 18:27:01'),(2,'RS-02','REACH_STACKER','Kalmar DRG450-65S5','Agus Setiawan','OP-002','operating',480.00,250.00,NULL,'B',78,0.0,'2026-10-21 01:34:12'),(3,'RS-03','REACH_STACKER','Sany SRSC45H2','Rudi Hermawan','OP-003','idle',0.00,0.00,NULL,'REEFER',78,0.0,'2026-10-13 18:27:01'),(4,'RTG-01','RTG_CRANE','ZPMC RTG 16-Wheel','Joko Susilo','OP-004','operating',0.00,0.00,NULL,'RAIL',95,0.0,'2026-10-13 18:27:01');
/*!40000 ALTER TABLE `equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login`
--

DROP TABLE IF EXISTS `login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login` (
  `iduser` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'admin',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iduser`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login`
--

LOCK TABLES `login` WRITE;
/*!40000 ALTER TABLE `login` DISABLE KEYS */;
INSERT INTO `login` VALUES (1,'Zulfikar Jafarudin Fatah','admin@cidp.ac.id','$2y$10$.CDPWh7dKJDgnM8WBHhadu9eGmo9yOMPLSGDEjKePZnZPc8uvvb3y','superadmin','2026-09-17 21:24:00'),(2,'Operator Kalmar RS-03','operator@cidp.ac.id','$2y$10$.CDPWh7dKJDgnM8WBHhadu9eGmo9yOMPLSGDEjKePZnZPc8uvvb3y','staf','2026-09-17 21:24:00'),(3,'PT Samudera Logistik Prima (Shipper)','client@cidp.ac.id','$2y$10$.CDPWh7dKJDgnM8WBHhadu9eGmo9yOMPLSGDEjKePZnZPc8uvvb3y','user','2026-09-17 21:24:00'),(4,'Supir Armada B 9481 UEK','driver@cidp.ac.id','$2y$10$.CDPWh7dKJDgnM8WBHhadu9eGmo9yOMPLSGDEjKePZnZPc8uvvb3y','driver','2026-09-17 21:24:00');
/*!40000 ALTER TABLE `login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_lokasi`
--

DROP TABLE IF EXISTS `master_lokasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_lokasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blok` varchar(10) NOT NULL,
  `bagian` varchar(20) NOT NULL,
  `rak` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'kosong',
  `pallet_sscc` varchar(35) DEFAULT NULL,
  `commodity_name` varchar(150) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lokasi` (`blok`,`bagian`,`rak`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_lokasi`
--

LOCK TABLES `master_lokasi` WRITE;
/*!40000 ALTER TABLE `master_lokasi` DISABLE KEYS */;
INSERT INTO `master_lokasi` VALUES (1,'1','A',1,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(2,'1','A',2,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(3,'1','A',3,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(4,'1','A',4,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(5,'1','B',1,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(6,'1','B',2,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(7,'1','B',3,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(8,'1','B',4,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(9,'1','C',1,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(10,'1','C',2,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(11,'1','C',3,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(12,'1','C',4,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(13,'1','D',1,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(14,'1','D',2,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(15,'1','D',3,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(16,'1','D',4,'terisi',NULL,'Mesin & Traksi Berat','2026-10-13 11:57:48'),(17,'1','E',1,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(18,'1','E',2,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(19,'1','E',3,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(20,'1','E',4,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(21,'2','A',1,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(22,'2','A',2,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(23,'2','A',3,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(24,'2','A',4,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(25,'2','B',1,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(26,'2','B',2,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(27,'2','B',3,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(28,'2','B',4,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(29,'2','C',1,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(30,'2','C',2,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(31,'2','C',3,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(32,'2','C',4,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(33,'2','D',1,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(34,'2','D',2,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(35,'2','D',3,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(36,'2','D',4,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(37,'2','E',1,'terisi',NULL,'Elektronik & Solar Inverter','2026-10-13 11:57:48'),(38,'2','E',2,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(39,'2','E',3,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(40,'2','E',4,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(41,'3','A',1,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(42,'3','A',2,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(43,'3','A',3,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(44,'3','A',4,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(45,'3','B',1,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(46,'3','B',2,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(47,'3','B',3,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(48,'3','B',4,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(49,'3','C',1,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(50,'3','C',2,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(51,'3','C',3,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(52,'3','C',4,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(53,'3','D',1,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(54,'3','D',2,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(55,'3','D',3,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(56,'3','D',4,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(57,'3','E',1,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(58,'3','E',2,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(59,'3','E',3,'terisi',NULL,'FMCG Food & Flavor','2026-10-13 11:57:48'),(60,'3','E',4,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(61,'4','A',1,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(62,'4','A',2,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(63,'4','A',3,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(64,'4','A',4,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(65,'4','B',1,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(66,'4','B',2,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(67,'4','B',3,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(68,'4','B',4,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(69,'4','C',1,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(70,'4','C',2,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(71,'4','C',3,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(72,'4','C',4,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(73,'4','D',1,'terisi',NULL,'Tekstil & Garment Ekspor','2026-10-13 11:57:48'),(74,'4','D',2,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(75,'4','D',3,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(76,'4','D',4,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(77,'4','E',1,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(78,'4','E',2,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(79,'4','E',3,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(80,'4','E',4,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(81,'5','A',1,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(82,'5','A',2,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(83,'5','A',3,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(84,'5','A',4,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(85,'5','B',1,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(86,'5','B',2,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(87,'5','B',3,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(88,'5','B',4,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(89,'5','C',1,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(90,'5','C',2,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(91,'5','C',3,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(92,'5','C',4,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(93,'5','D',1,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(94,'5','D',2,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(95,'5','D',3,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(96,'5','D',4,'terisi',NULL,'Bahan Kimia Non-B3 & Polimer','2026-10-13 11:57:48'),(97,'5','E',1,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(98,'5','E',2,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(99,'5','E',3,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(100,'5','E',4,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(101,'6','A',1,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(102,'6','A',2,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(103,'6','A',3,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(104,'6','A',4,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(105,'6','B',1,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(106,'6','B',2,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(107,'6','B',3,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(108,'6','B',4,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(109,'6','C',1,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(110,'6','C',2,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(111,'6','C',3,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(112,'6','C',4,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(113,'6','D',1,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(114,'6','D',2,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(115,'6','D',3,'kosong',NULL,NULL,'2026-10-13 11:57:48'),(116,'6','D',4,'kosong',NULL,NULL,'2026-10-13 11:57:48');
/*!40000 ALTER TABLE `master_lokasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trains`
--

DROP TABLE IF EXISTS `trains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `train_code` varchar(30) NOT NULL COMMENT 'Kode KA (KA-LOG-JKT-SMG)',
  `origin` varchar(50) DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `total_wagons` int(11) DEFAULT 20,
  `loaded_wagons` int(11) DEFAULT 0,
  `status` enum('scheduled','arrived','loading','unloading','departed') DEFAULT 'scheduled',
  `arrival_time` datetime DEFAULT NULL,
  `departure_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trains`
--

LOCK TABLES `trains` WRITE;
/*!40000 ALTER TABLE `trains` DISABLE KEYS */;
INSERT INTO `trains` VALUES (1,'KA 2518 (Inbound Impor)','Tanjung Priok (Pasoso/JICT)','CIDP Cikarang (Track-01)',30,24,'unloading',NULL,NULL,'2026-09-29 17:28:08'),(2,'KA 2519 (Outbound Ekspor)','CIDP Cikarang (Track-02)','Tanjung Priok (Pasoso/JICT)',30,26,'loading',NULL,NULL,'2026-10-01 14:04:35'),(3,'KA 2520 (Inbound Impor)','Tanjung Priok (Pasoso/JICT)','CIDP Cikarang (Track-01)',30,30,'scheduled',NULL,NULL,'2026-10-01 14:04:35'),(4,'KA 2521 (Outbound Ekspor)','CIDP Cikarang (Track-02)','Tanjung Priok (Pasoso/JICT)',30,0,'scheduled',NULL,NULL,'2026-10-01 14:04:35');
/*!40000 ALTER TABLE `trains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trucks`
--

DROP TABLE IF EXISTS `trucks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trucks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_plate` varchar(20) NOT NULL,
  `rfid_tag` varchar(50) DEFAULT NULL COMMENT 'Tag RFID armada',
  `driver_name` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `job_type` enum('drop_off','pick_up') NOT NULL DEFAULT 'drop_off',
  `mission_type` varchar(50) DEFAULT 'DROP_EXPORT',
  `container_number` varchar(20) DEFAULT NULL,
  `destination` varchar(100) DEFAULT 'Yard Blok A',
  `target_block` varchar(15) DEFAULT 'A',
  `do_number` varchar(50) DEFAULT NULL,
  `weight_gross` decimal(10,2) DEFAULT 0.00,
  `weight_tare` decimal(10,2) DEFAULT 0.00,
  `status` enum('queuing','at_gate','in_yard','loading','gate_out') DEFAULT 'queuing',
  `gate_in_time` datetime DEFAULT NULL,
  `gate_out_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trucks`
--

LOCK TABLES `trucks` WRITE;
/*!40000 ALTER TABLE `trucks` DISABLE KEYS */;
INSERT INTO `trucks` VALUES (1,'B 9128 UXT','RFID-TRK-101','Joko Prasetyo','PT Samudera Logistik','drop_off','DROP_EXPORT','SEGU1982731','Yard Blok B (Ekspor)','B','DO-EXP/2026/0912',31450.00,13200.00,'gate_out','2026-10-13 18:27:45','2026-10-13 18:27:55','2026-10-01 14:31:53'),(2,'B 9801 TEU','RFID-TRK-102','Hendra Gunawan','PT Puninar Jaya','pick_up','PICKUP_IMPORT','TCKU8291048','Yard Blok A','A','DO-IMP/2026/0844',12800.00,12800.00,'gate_out','2026-10-13 23:14:33','2026-10-13 23:32:53','2026-10-01 14:31:53'),(3,'B 9044 KKG','RFID-TRK-103','Asep Saepudin','PT Dunex Logistics','drop_off','EMPTY_RETURN','SUDU3109284','Depo Kosong Blok C','C','DO-MTY/2026/0129',15100.00,12850.00,'gate_out','2026-10-13 18:29:21','2026-10-13 18:29:32','2026-10-01 14:31:53'),(4,'B 9422 PQR','RFID-TRK-104','Supardi Santoso','PT Kamadjaja Logistics','pick_up','EMPTY_RELEASE','MSCU9918294','Depo Kosong Blok C','C','DO-REL/2026/0511',12500.00,12500.00,'in_yard','2026-10-13 23:32:43',NULL,'2026-10-01 14:31:53'),(5,'B 9555 KAA','RFID-TRK-105','Agus Wahyudi','PT Cipta Krida Logistik','drop_off','DUAL_CYCLE','MSKU1109288','Blok B (Drop) -> Blok A (Pick)','B','DO-DUAL/2026/0077',32800.00,13500.00,'gate_out','2026-10-13 21:37:37','2026-10-13 21:37:47','2026-10-01 14:31:53'),(6,'B 9201 FZ','RFID-TRK-106','Dedi Kusnandar','PT Iron Bird Trucking','drop_off','DROP_EXPORT','CMAU7234910','Blok REEFER (Cold Plug)','REEFER','DO-EXP/2026/0920',33200.00,13400.00,'at_gate','2026-10-01 13:50:00',NULL,'2026-10-01 14:31:53'),(7,'B 9631 XA','RFID-TRK-107','Eko Purnomo','PT Linfox Logistics','drop_off','DROP_EXPORT','COSU6019283','Yard Blok B (Ekspor)','B','DO-EXP/2026/0921',34100.00,13600.00,'at_gate','2026-10-01 14:05:00',NULL,'2026-10-01 14:31:53'),(8,'B 9345 UIK','RFID-TRK-108','Bambang Supriyadi','PT Pos Logistik','pick_up','PICKUP_IMPORT','EMCU5518200','Yard Blok A (Slot A-02-01)','A','DO-IMP/2026/0811',32300.00,12500.00,'in_yard','2026-10-01 12:40:00',NULL,'2026-10-01 14:31:53'),(9,'B 9712 TY','RFID-TRK-109','Wahyu Hidayat','PT Siba Surya','drop_off','DROP_EXPORT','TEMU4910285','Yard Blok B (Slot B-01-02)','B','DO-EXP/2026/0890',31800.00,13400.00,'loading','2026-10-01 12:15:00',NULL,'2026-10-01 14:31:53'),(10,'B 9890 BCD','RFID-TRK-110','Slamet Riyadi','PT Lookman Djaja','drop_off','DROP_EXPORT','MEDU7731902','Yard Blok B (Slot B-02-01)','B','DO-EXP/2026/0895',34600.00,12500.00,'in_yard','2026-10-01 11:50:00',NULL,'2026-10-01 14:31:53'),(11,'B 9112 KLM','RFID-TRK-111','Iwan Setiawan','PT Sumber Alfaria','drop_off','DROP_EXPORT','HASU8820194','Blok REEFER (Slot R-01-02)','REEFER','DO-EXP/2026/0905',40400.00,12500.00,'loading','2026-10-01 11:20:00',NULL,'2026-10-01 14:31:53'),(12,'B 9011 ZAA','RFID-TRK-112','Anton Wijaya','PT Dunex Logistics','pick_up','PICKUP_IMPORT','EGHU9910245','Pabrik Samsung Cikarang','A','DO-IMP/2026/0750',42700.00,12500.00,'gate_out','2026-10-01 08:30:00','2026-10-01 10:15:00','2026-10-01 14:31:53'),(13,'B 9234 VVV','RFID-TRK-113','Rudi Hartono','PT Samudera Logistik','drop_off','DROP_EXPORT','YMLU5501984','Yard Blok B -> Gate Out','B','DO-EXP/2026/0820',32600.00,12500.00,'gate_out','2026-10-01 09:10:00','2026-10-01 11:05:00','2026-10-01 14:31:53'),(14,'B 9777 WX','RFID-TRK-114','Deni Kurnia','PT Puninar Jaya','drop_off','EMPTY_RETURN','FSCU6628107','Depo Kosong Blok C','C','DO-MTY/2026/0098',14700.00,12500.00,'gate_out','2026-10-01 09:45:00','2026-10-01 11:40:00','2026-10-01 14:31:53'),(15,'B 9812 UIX','E28011700000020489110011','Rahmat Hidayat','PT Samudera Logistik Prima','drop_off','DROP_EXPORT','MSKU9182374','Yard Blok A','A',NULL,34850.00,0.00,'loading','2026-10-16 15:22:45',NULL,'2026-10-16 15:22:45');
/*!40000 ALTER TABLE `trucks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `yard_events`
--

DROP TABLE IF EXISTS `yard_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `yard_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(30) NOT NULL COMMENT 'LIFT_OFF, LIFT_ON, RELOCATION, GATE_IN, GATE_OUT, RAIL_LOAD, RAIL_UNLOAD',
  `container_number` varchar(20) DEFAULT NULL,
  `equipment_id` varchar(20) DEFAULT NULL,
  `from_block` varchar(10) DEFAULT NULL,
  `from_bay` varchar(5) DEFAULT NULL,
  `from_row` varchar(5) DEFAULT NULL,
  `from_tier` varchar(5) DEFAULT NULL,
  `to_block` varchar(10) DEFAULT NULL,
  `to_bay` varchar(5) DEFAULT NULL,
  `to_row` varchar(5) DEFAULT NULL,
  `to_tier` varchar(5) DEFAULT NULL,
  `operator_name` varchar(100) DEFAULT NULL,
  `billable_amount` decimal(12,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `yard_events`
--

LOCK TABLES `yard_events` WRITE;
/*!40000 ALTER TABLE `yard_events` DISABLE KEYS */;
INSERT INTO `yard_events` VALUES (1,'GATE_IN_ANPR','MSKU9821450','HW-01 ANPR & OCR',NULL,NULL,NULL,NULL,'A','01','01','01','Bambang Supriyadi',75000.00,'Deteksi loop induksi & ANPR Lane 1. Armada B 9011 ZAA masuk membawa box impor.','2026-10-01 14:31:53'),(2,'VGM_WEIGHED','SEGU1982731','HW-04 Weighbridge 80',NULL,NULL,NULL,NULL,'B','01','01','01','Petugas Timbangan',75000.00,'Penimbangan jembatan timbang 80T untuk B 9128 UXT: Bruto 31.450 kg. Sertifikat SOLAS VGM terbit.','2026-10-01 14:31:53'),(3,'CRANE_LIFT_OFF','TCKU8291048','RS-01 (Reach Stacker',NULL,NULL,NULL,NULL,'A','01','02','01','Operator RS Budi S.',350000.00,'Spreader mengunci 4 corner casting, peti kemas ditumpuk di Blok A-01-02-01.','2026-10-01 14:31:53'),(4,'CUSTOMS_SPPB','MSKU9821450','Gateway CEISA 4.0',NULL,NULL,NULL,NULL,'A','01','01','01','Sistem Bea Cukai KPPBC',0.00,'Surat Persetujuan Pengeluaran Barang (SPPB) Jalur Hijau diterbitkan otomatis.','2026-10-01 14:31:53'),(5,'GATE_OUT','EGHU9910245','Petugas Gate Out',NULL,NULL,NULL,NULL,'A','04','01','01','Petugas Gate Outbound',0.00,'Rilis Gate Out armada B 9011 ZAA. Validasi pabean SPPB lolos, billing lunas. Palang keluar terbuka.','2026-10-01 14:31:53'),(6,'RELOCATION','ONEU7718293','RS-02','A','03','03','01','A','01','05','02','Agus Setiawan',250000.00,'Relokasi box oleh RS-02 (Agus Setiawan) via 3D Virtual Simulator','2026-10-04 20:51:06'),(7,'CUSTOMS_CLEARANCE','E-SEAL-VERIFIED','SEAL-JT701',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Bea Cukai KPU Priok/Cikarang',0.00,'Jointech JT701 GPS Smart E-Seal #JT701-191118 diverifikasi via RFID nirkabel. Kawat utuh (INTACT). SPPB #SPPB-45163/KPU.01/2026 terbit otomatis.','2026-10-08 01:29:02'),(8,'CUSTOMS_CLEARANCE','E-SEAL-VERIFIED','SEAL-JT701',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Bea Cukai KPU Priok/Cikarang',0.00,'Jointech JT701 GPS Smart E-Seal #JT701-610716 diverifikasi via RFID nirkabel. Kawat utuh (INTACT). SPPB #SPPB-52736/KPU.01/2026 terbit otomatis.','2026-10-08 01:29:14'),(9,'GATE_IN','SEGU1982731','GATE-01',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Petugas Gerbang Timbangan',50000.00,'Truk B 9128 UXT (Joko Prasetyo) Gate-In [DROP-OFF (Antar Barang)]. Timbang: Gross 29555kg, Tare 12295kg. Status: SOLAS VERIFIED (PASS).','2026-10-13 18:27:45'),(10,'GATE_OUT','SEGU1982731',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,'Truk B 9128 UXT GATE-OUT sasis kosong setelah menurunkan kontainer di yard (Drop-Off Selesai).','2026-10-13 18:27:55'),(11,'GATE_IN','SUDU3109284','GATE-01',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Petugas Gerbang Timbangan',50000.00,'Truk B 9044 KKG (Asep Saepudin) Gate-In [DROP-OFF (Antar Barang)]. Timbang: Gross 29868kg, Tare 11068kg. Status: SOLAS VERIFIED (PASS).','2026-10-13 18:29:21'),(12,'GATE_OUT','SUDU3109284',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,'Truk B 9044 KKG GATE-OUT sasis kosong setelah menurunkan kontainer di yard (Drop-Off Selesai).','2026-10-13 18:29:32'),(13,'GATE_IN','MSKU1109288','GATE-01',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Petugas Gerbang Timbangan',50000.00,'Truk B 9555 KAA (Agus Wahyudi) Gate-In [DROP-OFF (Antar Barang)]. Timbang: Gross 30449kg, Tare 12094kg. Status: SOLAS VERIFIED (PASS).','2026-10-13 21:37:37'),(14,'GATE_OUT','MSKU1109288',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,'Truk B 9555 KAA GATE-OUT sasis kosong setelah menurunkan kontainer di yard (Drop-Off Selesai).','2026-10-13 21:37:47'),(15,'GATE_IN_ANPR','TCKU8291048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'HW-01 ANPR & OCR',0.00,'Deteksi ANPR Lane 1 (Inbound) untuk B 9801 TEU (Misi: PICKUP_IMPORT). Palang masuk terbuka otomatis.','2026-10-13 23:14:33'),(16,'VGM_WEIGHED','TCKU8291048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'HW-04 Weighbridge 80T',75000.00,'SOLAS VGM Verified: Bruto 12.800 kg, Tara 12.800 kg, Netto 0 kg untuk B 9801 TEU ke Blok A.','2026-10-13 23:14:33'),(17,'RELOCATION','MSCU9918294','RS-02','B','01','01','01','B','01','01','02','Agus Setiawan',250000.00,'Relokasi box oleh RS-02 (Agus Setiawan) via 3D Virtual Simulator','2026-10-13 23:31:49'),(18,'GATE_IN','MSCU9918294','GATE-01',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Petugas Gerbang Timbangan',50000.00,'Truk B 9422 PQR (Supardi Santoso) Gate-In [PICK-UP (Ambil Barang)]. Timbang: Gross 11220kg, Tare 11220kg. Status: EMPTY CHASSIS (TARE RECORDED) - PICK-UP PERMITTED.','2026-10-13 23:32:43'),(19,'GATE_OUT','TCKU8291048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,'Truk B 9801 TEU GATE-OUT membawa kontainer TCKU8291048 keluar dari dry port (Pick-Up Selesai).','2026-10-13 23:32:53'),(20,'GATE_IN','MSKU9182374',NULL,'OUTSIDE',NULL,NULL,NULL,'GATE',NULL,NULL,NULL,NULL,0.00,'Truk B 9812 UIX terdeteksi di Gate Inbound oleh OCR & RFID','2026-10-16 15:22:45'),(21,'WEIGHBRIDGE_VGM','MSKU9182374',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,75000.00,'Penimbangan SOLAS VGM: 34,850 kg','2026-10-16 15:24:12'),(22,'LIFT_OFF','MSKU9182374','RS-02',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,325000.00,'Pengangkatan kontainer dari truk B 9812 UIX oleh RS-02','2026-10-16 15:25:08'),(23,'STACKING','MSKU9182374','RS-02',NULL,NULL,NULL,NULL,'B','08','03','02',NULL,0.00,'Kontainer berhasil ditumpuk di B-08-03-02','2026-10-16 15:26:39'),(24,'CUSTOMS_CEISA','MSKU9182374',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,'Status CEISA 4.0 DJBC: SPPB_CLEARED (SPPB-092811/KPU.01/2026)','2026-10-16 15:28:06'),(25,'BILLING_PAID','MSKU9182374',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,'Pelunasan tagihan terminal via Bank Mandiri Host-to-Host','2026-10-16 15:28:31'),(26,'RAIL_UNLOAD','MSKU4419283','RTG-01',NULL,NULL,NULL,NULL,'A','01','01','02','Joko Susilo (RTG-01)',350000.00,'Bongkar kontainer dari Rangkaian KA-LOG-JKT-SMG oleh RTG-01 ke Blok A Bay 01','2026-10-20 19:06:07');
/*!40000 ALTER TABLE `yard_events` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-25 11:28:50
