-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 11:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `legal_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('paid','pending','overdue') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`id`, `invoice_number`, `client_name`, `amount`, `due_date`, `status`, `created_at`) VALUES
(1, 'INV-001', 'Hotel Management Corp', 2500.00, '2023-07-15', 'paid', '2025-10-09 13:06:58'),
(2, 'INV-002', 'Restaurant Owner LLC', 1800.00, '2023-08-05', 'pending', '2025-10-09 13:06:58'),
(3, 'INV-003', 'Hotel Chain International', 5200.00, '2023-06-30', 'overdue', '2025-10-09 13:06:58'),
(4, 'INV-004', 'Boutique Hotel Group', 3200.00, '2023-09-10', 'pending', '2025-10-09 13:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `id` int(11) NOT NULL,
  `case_id` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('open','pending','closed') DEFAULT 'open',
  `date_filed` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_id`, `title`, `description`, `status`, `date_filed`, `created_at`) VALUES
(1, 'C-001', 'Employment Dispute - Hotel Staff', 'Dispute regarding overtime pay and working conditions for hotel staff members.', 'open', '2023-05-15', '2025-10-09 13:06:58'),
(2, 'C-002', 'Contract Breach - Restaurant Supplier', 'Supplier failed to deliver agreed quantities of ingredients as per contract.', 'pending', '2023-06-22', '2025-10-09 13:06:58'),
(3, 'C-003', 'Customer Injury Claim', 'Customer slipped and fell in restaurant premises, claiming negligence.', 'closed', '2023-04-10', '2025-10-09 13:06:58'),
(4, 'C-004', 'Licensing Agreement Violation', 'Hotel franchise violated terms of licensing agreement with corporate.', 'open', '2023-07-05', '2025-10-09 13:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `role`, `created_at`) VALUES
(1, 'John Smith', 'john@hotelchain.com', '(555) 123-4567', 'Hotel Manager', '2025-10-09 13:06:59'),
(2, 'Sarah Johnson', 'sarah@restaurant.com', '(555) 987-6543', 'Restaurant Owner', '2025-10-09 13:06:59'),
(3, 'Michael Brown', 'michael@supplier.com', '(555) 456-7890', 'Supplier', '2025-10-09 13:06:59'),
(4, 'Emily Davis', 'emily@hotelgroup.com', '(555) 111-2222', 'HR Director', '2025-10-09 13:06:59'),
(5, 'Robert Wilson', 'robert@legal.com', '(555) 333-4444', 'Legal Counsel', '2025-10-09 13:06:59');

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `contract_name` varchar(255) NOT NULL,
  `case_id` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `risk_level` enum('Low','Medium','High') NOT NULL,
  `risk_score` int(11) NOT NULL,
  `risk_factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`risk_factors`)),
  `recommendations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recommendations`)),
  `analysis_summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `case_id` varchar(20) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `date_uploaded` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `document_name`, `case_id`, `file_path`, `date_uploaded`, `created_at`) VALUES
(1, 'Employment Contract.pdf', 'C-001', '/documents/emp_contract_001.pdf', '2023-05-20', '2025-10-09 13:06:58'),
(2, 'Supplier Agreement.docx', 'C-002', '/documents/supplier_agreement_002.docx', '2023-06-25', '2025-10-09 13:06:58'),
(3, 'Incident Report.pdf', 'C-003', '/documents/incident_report_003.pdf', '2023-04-15', '2025-10-09 13:06:58'),
(4, 'Licensing Terms.pdf', 'C-004', '/documents/licensing_terms_004.pdf', '2023-07-10', '2025-10-09 13:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `name`, `position`, `department`, `email`, `phone`, `address`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
(1, 'EMP-001', 'Robert Wilson', 'Senior Legal Counsel', 'Legal', 'robert@legalteam.com', '(555) 111-2222', '123 Main St, City, State', '2022-01-15', 85000.00, 'active', '2025-10-12 04:40:12', '2025-10-12 04:40:12'),
(2, 'EMP-002', 'Emily Davis', 'Legal Assistant', 'Legal', 'emily@legalteam.com', '(555) 333-4444', '456 Oak Ave, City, State', '2022-03-20', 45000.00, 'active', '2025-10-12 04:40:12', '2025-10-12 04:40:12'),
(3, 'EMP-003', 'James Miller', 'Paralegal', 'Legal', 'james@legalteam.com', '(555) 555-6666', '789 Pine Rd, City, State', '2023-01-10', 52000.00, 'active', '2025-10-12 04:40:12', '2025-10-12 04:40:12'),
(4, 'EMP-004', 'Sarah Johnson', 'Legal Secretary', 'Administrative', 'sarah@legalteam.com', '(555) 777-8888', '321 Elm St, City, State', '2021-11-05', 38000.00, 'active', '2025-10-12 04:40:12', '2025-10-12 04:40:12'),
(5, 'EMP-005', 'Michael Brown', 'Junior Attorney', 'Legal', 'michael@legalteam.com', '(555) 999-0000', '654 Maple Dr, City, State', '2023-06-15', 68000.00, 'active', '2025-10-12 04:40:12', '2025-10-12 04:40:12');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `position`, `email`, `phone`, `created_at`) VALUES
(1, 'Robert Wilson', 'Senior Legal Counsel', 'robert@legalteam.com', '(555) 111-2222', '2025-10-09 13:06:59'),
(2, 'Emily Davis', 'Legal Assistant', 'emily@legalteam.com', '(555) 333-4444', '2025-10-09 13:06:59'),
(3, 'James Miller', 'Paralegal', 'james@legalteam.com', '(555) 555-6666', '2025-10-09 13:06:59'),
(4, 'Jennifer Lopez', 'Case Manager', 'jennifer@legalteam.com', '(555) 777-8888', '2025-10-09 13:06:59'),
(5, 'David Chen', 'Legal Researcher', 'david@legalteam.com', '(555) 999-0000', '2025-10-09 13:06:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `pin_hash`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', '2025-10-09 13:06:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_billing_status` (`status`),
  ADD KEY `idx_billing_due_date` (`due_date`);

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `case_id` (`case_id`),
  ADD KEY `idx_cases_status` (`status`),
  ADD KEY `idx_cases_date` (`date_filed`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_documents_case` (`case_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`case_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
