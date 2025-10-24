-- legal_management_system.sql
-- SQL for creating legal_management_system database and required tables

CREATE DATABASE IF NOT EXISTS `legal_management_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `legal_management_system`;

-- Table: employees
CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `employee_id` VARCHAR(20) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_id` (`employee_id`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: documents
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `case_id` VARCHAR(20) DEFAULT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `risk_level` ENUM('low', 'high', 'unknown') DEFAULT 'unknown',
  `risk_score` FLOAT DEFAULT NULL,
  `analysis_date` DATETIME DEFAULT NULL,
  `ai_analysis` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: billing
CREATE TABLE IF NOT EXISTS `billing` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(20) NOT NULL,
  `client` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `due_date` DATE NOT NULL,
  `status` ENUM('paid', 'pending', 'overdue') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invoice` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: contacts (used for contracts)
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `role` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: members
CREATE TABLE IF NOT EXISTS `members` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `employees` (employee_id, name, position, email, phone) VALUES
('E-001', 'Robert Wilson', 'Senior Legal Counsel', 'robert@legalteam.com', '(555) 111-2222'),
('E-002', 'Emily Davis', 'Legal Assistant', 'emily@legalteam.com', '(555) 333-4444'),
('E-003', 'James Miller', 'Paralegal', 'james@legalteam.com', '(555) 555-6666');

INSERT INTO `documents` (name, case_id, file_path) VALUES
('Employment Contract.pdf', 'C-001', 'uploads/documents/Employment Contract.pdf'),
('Supplier Agreement.docx', 'C-002', 'uploads/documents/Supplier Agreement.docx'),
('Incident Report.pdf', 'C-003', 'uploads/documents/Incident Report.pdf');

INSERT INTO `billing` (invoice_number, client, amount, due_date, status) VALUES
('INV-001', 'Hotel Management', 2500.00, '2023-07-15', 'paid'),
('INV-002', 'Restaurant Owner', 1800.00, '2023-08-05', 'pending'),
('INV-003', 'Hotel Chain', 5200.00, '2023-06-30', 'overdue');

INSERT INTO `contacts` (name, email, phone, role) VALUES
('John Smith', 'john@hotelchain.com', '(555) 123-4567', 'Hotel Manager'),
('Sarah Johnson', 'sarah@restaurant.com', '(555) 987-6543', 'Restaurant Owner'),
('Michael Brown', 'michael@supplier.com', '(555) 456-7890', 'Supplier');

INSERT INTO `members` (name, position, email, phone) VALUES
('Robert Wilson', 'Senior Legal Counsel', 'robert@legalteam.com', '(555) 111-2222'),
('Emily Davis', 'Legal Assistant', 'emily@legalteam.com', '(555) 333-4444'),
('James Miller', 'Paralegal', 'james@legalteam.com', '(555) 555-6666');
