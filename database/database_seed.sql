-- =============================================================================
-- RESET & SEED DATABASE CIDP YMS
-- Conclusion Intermodal Dry Port - Yard Management System
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE yard_events;
TRUNCATE TABLE containers;
TRUNCATE TABLE trucks;
TRUNCATE TABLE equipment;
TRUNCATE TABLE trains;
SET FOREIGN_KEY_CHECKS = 1;

-- Alat Berat (4 unit aset tetap terminal)
INSERT INTO equipment (equipment_id, equipment_type, brand_model, operator_name, operator_id, status, fuel_percent, hours_today) VALUES
('RS-01', 'REACH_STACKER', 'Kalmar DRG450-65S5', 'Budi Santoso', 'OP-001', 'idle', 92, 0),
('RS-02', 'REACH_STACKER', 'Kalmar DRG450-65S5', 'Agus Setiawan', 'OP-002', 'idle', 87, 0),
('RS-03', 'REACH_STACKER', 'Sany SRSC45H2', 'Rudi Hermawan', 'OP-003', 'idle', 78, 0),
('RTG-01', 'RTG_CRANE', 'ZPMC RTG 16-Wheel', 'Joko Susilo', 'OP-004', 'idle', 95, 0);

-- 3 Kontainer starter
INSERT INTO containers (container_number, iso_code, size_type, cargo_type, rfid_tag, sscc_code, gross_weight_kg, owner_company, seal_number, customs_status, block, bay, `row`, tier, gate_in_time, status) VALUES
('MSKU7829104', '45G1', '40FT HIGH CUBE', 'dry', 'RFID-CTR-001', '00389912345000000001', 28500.00, 'PT Samudera Pratama Mandiri', 'SEAL-ID-20260929-001', 'SPPB_CLEARED', 'A', '01', '01', '01', '2026-09-29 08:15:00', 'in_yard'),
('TGHU9021845', '22G1', '20FT STANDARD', 'dry', 'RFID-CTR-002', '00389912345000000002', 18200.00, 'PT Indofood CBP Sukses Makmur', 'SEAL-ID-20260929-002', 'SPPB_CLEARED', 'A', '01', '02', '01', '2026-09-29 09:30:00', 'in_yard'),
('EITU9823102', '45R1', '40FT REEFER HC', 'reefer', 'RFID-CTR-003', '00389912345000000003', 26800.00, 'PT Kalbe Farma Tbk', 'SEAL-ID-20260929-003', 'INSPECTION_PENDING', 'REEFER', '01', '01', '01', '2026-09-29 10:45:00', 'in_yard');

-- 2 Truk starter
INSERT INTO trucks (license_plate, rfid_tag, driver_name, company, container_number, status, gate_in_time) VALUES
('B 9182 TE', 'RFID-TRK-001', 'Soleh Marzuki', 'PT Trans Logistik Prima', NULL, 'in_yard', '2026-09-29 08:10:00'),
('B 5567 KJ', 'RFID-TRK-002', 'Hendra Wijaya', 'PT Cepat Aman Logistik', NULL, 'queuing', NULL);

-- 1 Kereta starter
INSERT INTO trains (train_code, origin, destination, total_wagons, loaded_wagons, status, arrival_time) VALUES
('KA 2518', 'Tanjung Priok (JICT)', 'CIDP Cikarang (Rail Siding)', 30, 0, 'scheduled', NULL);

-- 3 Event log awal
INSERT INTO yard_events (event_type, container_number, equipment_id, to_block, to_bay, to_row, to_tier, operator_name, notes) VALUES
('GATE_IN', 'MSKU7829104', NULL, 'A', '01', '01', '01', NULL, 'Kontainer masuk via Gate Lane 1 - VGM 28.500 kg'),
('GATE_IN', 'TGHU9021845', NULL, 'A', '01', '02', '01', NULL, 'Kontainer masuk via Gate Lane 2 - VGM 18.200 kg'),
('GATE_IN', 'EITU9823102', NULL, 'REEFER', '01', '01', '01', NULL, 'Reefer container - langsung ke blok REEFER, power connected');
