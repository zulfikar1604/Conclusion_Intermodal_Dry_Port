-- =============================================================================
-- DATABASE: cidp_yms
-- MODUL 1: AUTENTIKASI PENGGUNA (HANYA TABEL LOGIN)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `cidp_yms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cidp_yms`;

-- -----------------------------------------------------------------------------
-- TABEL: login
-- Menyimpan data akun pengguna untuk autentikasi sistem
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `login`;
CREATE TABLE `login` (
  `iduser` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- DATA AWAL (SEED PENGUJIAN LOGIN)
-- Email    : admin@cidp.ac.id
-- Password : admin123 (Di-hash dengan BCRYPT)
-- -----------------------------------------------------------------------------
INSERT INTO `login` (`iduser`, `nama`, `email`, `password`, `role`) VALUES
(1, 'Zulfikar Jafarudin Fatah', 'admin@cidp.ac.id', '$2y$10$.CDPWh7dKJDgnM8WBHhadu9eGmo9yOMPLSGDEjKePZnZPc8uvvb3y', 'superadmin');
