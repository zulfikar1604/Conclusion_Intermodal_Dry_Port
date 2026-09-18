-- 1. containers - Kontainer di yard
CREATE TABLE IF NOT EXISTS `containers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `container_number` VARCHAR(20) NOT NULL COMMENT 'Nomor kontainer ISO 6346 (mis: MSKU9182374)',
    `iso_code` VARCHAR(10) DEFAULT '42G1' COMMENT 'Kode ISO ukuran/tipe',
    `size_type` VARCHAR(30) DEFAULT '40FT HIGH CUBE',
    `cargo_type` ENUM('dry','reefer','dg','empty') DEFAULT 'dry' COMMENT 'Tipe kargo',
    `rfid_tag` VARCHAR(50) COMMENT 'ID Tag RFID UHF ISO 18000-6C',
    `sscc_code` VARCHAR(25) COMMENT 'Serial Shipping Container Code (SSCC-18)',
    `gross_weight_kg` DECIMAL(10,2) DEFAULT 0,
    `owner_company` VARCHAR(100) COMMENT 'Perusahaan pemilik/pengirim',
    `seal_number` VARCHAR(30),
    `customs_status` VARCHAR(30) DEFAULT 'SPPB_CLEARED',
    `block` VARCHAR(10) COMMENT 'Blok penumpukan (A, B, C, D, E, REEFER, DG)',
    `bay` VARCHAR(5) COMMENT 'Posisi Bay (01-20)',
    `row` VARCHAR(5) COMMENT 'Posisi Row (01-06)',
    `tier` VARCHAR(5) COMMENT 'Posisi Tier/tingkat (01-04)',
    `gate_in_time` DATETIME COMMENT 'Waktu masuk gate',
    `status` ENUM('in_yard','in_transit','gate_out','on_rail') DEFAULT 'in_yard',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. equipment - Alat berat (Reach Stacker, RTG)
CREATE TABLE IF NOT EXISTS `equipment` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `equipment_id` VARCHAR(20) NOT NULL COMMENT 'Kode alat (RS-01, RS-02, RS-03, RTG-01)',
    `equipment_type` VARCHAR(30) DEFAULT 'REACH_STACKER',
    `brand_model` VARCHAR(50) DEFAULT 'Kalmar DRG450-65S5',
    `operator_name` VARCHAR(100) COMMENT 'Nama operator aktif',
    `operator_id` VARCHAR(20) COMMENT 'ID operator',
    `status` ENUM('idle','operating','carrying','maintenance') DEFAULT 'idle',
    `gps_x` DECIMAL(8,2) DEFAULT 0 COMMENT 'Posisi X di denah yard (piksel/grid)',
    `gps_y` DECIMAL(8,2) DEFAULT 0 COMMENT 'Posisi Y di denah yard (piksel/grid)',
    `current_container` VARCHAR(20) DEFAULT NULL COMMENT 'Kontainer sedang diangkut',
    `last_block` VARCHAR(10) COMMENT 'Blok terakhir dikunjungi',
    `fuel_percent` INT DEFAULT 85,
    `hours_today` DECIMAL(4,1) DEFAULT 0,
    `last_updated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. yard_events - Log semua kejadian yard
CREATE TABLE IF NOT EXISTS `yard_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_type` VARCHAR(30) NOT NULL COMMENT 'LIFT_OFF, LIFT_ON, RELOCATION, GATE_IN, GATE_OUT, RAIL_LOAD, RAIL_UNLOAD',
    `container_number` VARCHAR(20),
    `equipment_id` VARCHAR(20),
    `from_block` VARCHAR(10),
    `from_bay` VARCHAR(5),
    `from_row` VARCHAR(5),
    `from_tier` VARCHAR(5),
    `to_block` VARCHAR(10),
    `to_bay` VARCHAR(5),
    `to_row` VARCHAR(5),
    `to_tier` VARCHAR(5),
    `operator_name` VARCHAR(100),
    `billable_amount` DECIMAL(12,2) DEFAULT 0,
    `notes` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. trucks - Data truk di terminal
CREATE TABLE IF NOT EXISTS `trucks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `license_plate` VARCHAR(20) NOT NULL,
    `rfid_tag` VARCHAR(50) COMMENT 'Tag RFID armada',
    `driver_name` VARCHAR(100),
    `company` VARCHAR(100),
    `container_number` VARCHAR(20),
    `status` ENUM('queuing','at_gate','in_yard','loading','gate_out') DEFAULT 'queuing',
    `gate_in_time` DATETIME,
    `gate_out_time` DATETIME,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. trains - Data kereta api logistik
CREATE TABLE IF NOT EXISTS `trains` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `train_code` VARCHAR(30) NOT NULL COMMENT 'Kode KA (KA-LOG-JKT-SMG)',
    `origin` VARCHAR(50),
    `destination` VARCHAR(50),
    `total_wagons` INT DEFAULT 20,
    `loaded_wagons` INT DEFAULT 0,
    `status` ENUM('scheduled','arrived','loading','unloading','departed') DEFAULT 'scheduled',
    `arrival_time` DATETIME,
    `departure_time` DATETIME,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data

TRUNCATE TABLE `containers`;
INSERT INTO `containers` (`container_number`, `iso_code`, `size_type`, `cargo_type`, `rfid_tag`, `sscc_code`, `gross_weight_kg`, `owner_company`, `seal_number`, `customs_status`, `block`, `bay`, `row`, `tier`, `gate_in_time`, `status`) VALUES
('MSKU9182374', '42G1', '40FT HIGH CUBE', 'dry', 'E280117000000001', '(00)389912345000000001', 25400.00, 'PT Samudera Logistik Prima', 'SN102938', 'SPPB_CLEARED', 'A', '01', '01', '01', DATE_SUB(NOW(), INTERVAL 2 DAY), 'in_yard'),
('TCLU1234567', '22G1', '20FT STANDARD', 'dry', 'E280117000000002', '(00)389912345000000002', 14500.00, 'PT Evergreen Shipping Indonesia', 'SN102939', 'SPPB_CLEARED', 'A', '01', '01', '02', DATE_SUB(NOW(), INTERVAL 1 DAY), 'in_yard'),
('TEMU8888888', '42G1', '40FT HIGH CUBE', 'dry', 'E280117000000003', '(00)389912345000000003', 28000.00, 'Maersk Indonesia', 'SN102940', 'SPPB_CLEARED', 'A', '01', '02', '01', DATE_SUB(NOW(), INTERVAL 3 DAY), 'in_yard'),
('CSQU7777777', '22G1', '20FT STANDARD', 'dry', 'E280117000000004', '(00)389912345000000004', 18000.00, 'CMA CGM Logistics', 'SN102941', 'SPPB_CLEARED', 'B', '05', '03', '01', DATE_SUB(NOW(), INTERVAL 12 HOUR), 'in_yard'),
('FCIU5555555', '42G1', '40FT HIGH CUBE', 'dry', 'E280117000000005', '(00)389912345000000005', 22000.00, 'PT Meratus Line', 'SN102942', 'SPPB_CLEARED', 'B', '05', '03', '02', DATE_SUB(NOW(), INTERVAL 1 DAY), 'in_yard'),
('HLXU4444444', '22G1', '20FT STANDARD', 'dry', 'E280117000000006', '(00)389912345000000006', 15000.00, 'PT Samudera Logistik Prima', 'SN102943', 'SPPB_CLEARED', 'B', '06', '01', '01', DATE_SUB(NOW(), INTERVAL 2 DAY), 'in_yard'),
('CMAU3333333', '42G1', '40FT HIGH CUBE', 'dry', 'E280117000000007', '(00)389912345000000007', 26000.00, 'CMA CGM Logistics', 'SN102944', 'SPPB_CLEARED', 'C', '10', '02', '01', DATE_SUB(NOW(), INTERVAL 3 DAY), 'in_yard'),
('OOLU2222222', '22G1', '20FT STANDARD', 'dry', 'E280117000000008', '(00)389912345000000008', 19000.00, 'PT Evergreen Shipping Indonesia', 'SN102945', 'SPPB_CLEARED', 'C', '10', '02', '02', DATE_SUB(NOW(), INTERVAL 5 HOUR), 'in_yard'),
('MSKU1111111', '42G1', '40FT HIGH CUBE', 'dry', 'E280117000000009', '(00)389912345000000009', 24500.00, 'Maersk Indonesia', 'SN102946', 'SPPB_CLEARED', 'C', '11', '01', '01', DATE_SUB(NOW(), INTERVAL 1 DAY), 'in_yard'),
('TCLU9999999', '22G1', '20FT STANDARD', 'dry', 'E280117000000010', '(00)389912345000000010', 17000.00, 'PT Meratus Line', 'SN102947', 'SPPB_CLEARED', 'C', '11', '01', '02', DATE_SUB(NOW(), INTERVAL 2 DAY), 'in_yard'),
('TEMU7777777', '45R1', '40FT REEFER', 'reefer', 'E280117000000011', '(00)389912345000000011', 27000.00, 'Maersk Indonesia', 'SN102948', 'SPPB_CLEARED', 'REEFER', '01', '01', '01', DATE_SUB(NOW(), INTERVAL 1 DAY), 'in_yard'),
('CSQU6666666', '45R1', '40FT REEFER', 'reefer', 'E280117000000012', '(00)389912345000000012', 28500.00, 'CMA CGM Logistics', 'SN102949', 'SPPB_CLEARED', 'REEFER', '01', '01', '02', DATE_SUB(NOW(), INTERVAL 2 DAY), 'in_yard'),
('FCIU4444444', '22R1', '20FT REEFER', 'reefer', 'E280117000000013', '(00)389912345000000013', 18500.00, 'PT Samudera Logistik Prima', 'SN102950', 'SPPB_CLEARED', 'REEFER', '02', '01', '01', DATE_SUB(NOW(), INTERVAL 12 HOUR), 'in_yard'),
('HLXU3333333', '22R1', '20FT REEFER', 'reefer', 'E280117000000014', '(00)389912345000000014', 19000.00, 'PT Meratus Line', 'SN102951', 'SPPB_CLEARED', 'REEFER', '02', '01', '02', DATE_SUB(NOW(), INTERVAL 1 DAY), 'in_yard'),
('CMAU5555555', '42G1', '40FT HIGH CUBE', 'dg', 'E280117000000015', '(00)389912345000000015', 24000.00, 'CMA CGM Logistics', 'SN102952', 'SPPB_CLEARED', 'DG', '01', '01', '01', DATE_SUB(NOW(), INTERVAL 1 DAY), 'in_yard'),
('OOLU6666666', '22G1', '20FT STANDARD', 'dg', 'E280117000000016', '(00)389912345000000016', 16000.00, 'PT Evergreen Shipping Indonesia', 'SN102953', 'SPPB_CLEARED', 'DG', '01', '02', '01', DATE_SUB(NOW(), INTERVAL 2 DAY), 'in_yard'),
('MSKU2222222', '42G1', '40FT HIGH CUBE', 'empty', 'E280117000000017', '(00)389912345000000017', 4000.00, 'Maersk Indonesia', NULL, 'EMPTY', 'EMPTY', '01', '01', '01', DATE_SUB(NOW(), INTERVAL 5 DAY), 'in_yard'),
('TCLU3333333', '22G1', '20FT STANDARD', 'empty', 'E280117000000018', '(00)389912345000000018', 2200.00, 'PT Samudera Logistik Prima', NULL, 'EMPTY', 'EMPTY', '01', '01', '02', DATE_SUB(NOW(), INTERVAL 4 DAY), 'in_yard'),
('TEMU4444444', '42G1', '40FT HIGH CUBE', 'empty', 'E280117000000019', '(00)389912345000000019', 4000.00, 'CMA CGM Logistics', NULL, 'EMPTY', 'EMPTY', '01', '02', '01', DATE_SUB(NOW(), INTERVAL 6 DAY), 'in_yard');

TRUNCATE TABLE `equipment`;
INSERT INTO `equipment` (`equipment_id`, `equipment_type`, `brand_model`, `operator_name`, `operator_id`, `status`, `gps_x`, `gps_y`, `current_container`, `last_block`, `fuel_percent`, `hours_today`) VALUES
('RS-01', 'REACH_STACKER', 'Kalmar DRG450-65S5', 'Budi Santoso', 'OPR-001', 'idle', 150.00, 200.00, NULL, 'A', 85, 3.5),
('RS-02', 'REACH_STACKER', 'Kalmar DRG450-65S5', 'Agus Setiawan', 'OPR-002', 'operating', 350.00, 300.00, 'MSKU9182374', 'B', 70, 4.2),
('RS-03', 'REACH_STACKER', 'Kalmar DRG450-65S5', 'Rudi Hermawan', 'OPR-003', 'idle', 500.00, 150.00, NULL, 'REEFER', 90, 2.1),
('RTG-01', 'RTG_CRANE', 'Konecranes RTG', 'Joko Susilo', 'OPR-004', 'operating', 100.00, 450.00, NULL, 'RAIL', 60, 5.0);

TRUNCATE TABLE `yard_events`;
INSERT INTO `yard_events` (`event_type`, `container_number`, `equipment_id`, `from_block`, `from_bay`, `from_row`, `from_tier`, `to_block`, `to_bay`, `to_row`, `to_tier`, `operator_name`, `notes`, `created_at`) VALUES
('GATE_IN', 'MSKU9182374', NULL, NULL, NULL, NULL, NULL, 'A', '01', '01', '01', 'System', 'Container entered yard', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('LIFT_OFF', 'MSKU9182374', 'RS-01', 'GATE', NULL, NULL, NULL, 'A', '01', '01', '01', 'Budi Santoso', 'Placed in yard', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('GATE_IN', 'TEMU7777777', NULL, NULL, NULL, NULL, NULL, 'REEFER', '01', '01', '01', 'System', 'Reefer container entered', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('LIFT_OFF', 'TEMU7777777', 'RS-03', 'GATE', NULL, NULL, NULL, 'REEFER', '01', '01', '01', 'Rudi Hermawan', 'Placed in reefer block and plugged', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('RELOCATION', 'MSKU9182374', 'RS-02', 'A', '01', '01', '01', 'B', '05', '01', '01', 'Agus Setiawan', 'Relocated for better access', DATE_SUB(NOW(), INTERVAL 2 HOUR));

TRUNCATE TABLE `trucks`;
INSERT INTO `trucks` (`license_plate`, `rfid_tag`, `driver_name`, `company`, `container_number`, `status`, `gate_in_time`, `gate_out_time`) VALUES
('B 9182 TE', 'RFID-TRK-001', 'Soleh', 'PT Trans Logistik', NULL, 'queuing', NULL, NULL),
('B 1234 XY', 'RFID-TRK-002', 'Hendra', 'PT Cepat Aman', 'MSKU1111111', 'loading', DATE_SUB(NOW(), INTERVAL 30 MINUTE), NULL),
('D 5555 ZZ', 'RFID-TRK-003', 'Maman', 'CV Jaya Abadi', NULL, 'in_yard', DATE_SUB(NOW(), INTERVAL 15 MINUTE), NULL),
('B 7777 AB', 'RFID-TRK-004', 'Tono', 'PT Logistik Utama', 'TCLU9999999', 'at_gate', NULL, NULL),
('L 8888 KL', 'RFID-TRK-005', 'Yanto', 'PT Lintas Pulau', 'CSQU7777777', 'gate_out', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 10 MINUTE));

TRUNCATE TABLE `trains`;
INSERT INTO `trains` (`train_code`, `origin`, `destination`, `total_wagons`, `loaded_wagons`, `status`, `arrival_time`, `departure_time`) VALUES
('KA-LOG-JKT-SMG', 'Jakarta (Tanjung Priok)', 'Semarang (Tawang)', 20, 15, 'arrived', DATE_SUB(NOW(), INTERVAL 4 HOUR), NULL),
('KA-LOG-SMG-SBY', 'Semarang (Tawang)', 'Surabaya (Pasar Turi)', 30, 0, 'scheduled', NULL, NULL);
