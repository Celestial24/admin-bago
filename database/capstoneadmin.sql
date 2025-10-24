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
-- Database: `capstoneadmin`
--

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('banquet','meeting','conference','outdoor','dining','lounge') NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `amenities` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `name`, `type`, `capacity`, `location`, `description`, `hourly_rate`, `amenities`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Grand Ballroom', 'banquet', 300, 'Main Building - 1st Floor', 'Elegant ballroom with crystal chandeliers and marble floors. Perfect for weddings, galas, and large corporate events.', 450.00, 'Stage, Sound System, Projector, Dance Floor, Catering Kitchen', NULL, 'active', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(2, 'Executive Boardroom', 'meeting', 20, 'Business Center - 3rd Floor', 'Professional meeting space with premium leather chairs and advanced audio-visual equipment.', 120.00, 'Smart TV, Video Conferencing, Whiteboard, WiFi, Refreshment Center', NULL, 'active', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(3, 'Sunset Terrace', 'outdoor', 150, 'Garden Wing - Outdoor', 'Beautiful outdoor venue with panoramic views, perfect for cocktail parties and sunset receptions.', 280.00, 'Outdoor Bar, Lighting, Sound System, Weather Protection', NULL, 'active', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(4, 'Royal Conference Hall', 'conference', 200, 'Convention Center - 2nd Floor', 'State-of-the-art conference facility with theater-style seating and professional presentation tools.', 350.00, 'Projector, Microphones, Stage, Green Room, Registration Area', NULL, 'active', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(5, 'Private Dining Room', 'dining', 40, 'Main Restaurant - 1st Floor', 'Intimate dining space with elegant decor, perfect for family gatherings and business dinners.', 150.00, 'Dedicated Server, Custom Menu, Wine Cellar Access', NULL, 'active', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(6, 'Sky Lounge', 'lounge', 80, 'Penthouse Level - 15th Floor', 'Modern lounge with stunning city views, ideal for networking events and social gatherings.', 200.00, 'Bar, Lounge Seating, Music System, City View', NULL, 'active', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(7, 'Ocean View Room', 'meeting', 15, 'Beach Wing - 2nd Floor', 'Cozy meeting room with ocean views and natural lighting.', 95.00, 'Ocean View, Whiteboard, WiFi, Coffee Service', NULL, 'active', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(8, 'Garden Pavilion', 'outdoor', 100, 'Hotel Gardens', 'Charming outdoor pavilion surrounded by lush gardens, perfect for intimate weddings.', 180.00, 'Garden Setting, Dance Floor, Decorative Lighting', NULL, 'active', '2025-10-09 11:47:10', '2025-10-09 11:47:10');

-- --------------------------------------------------------

--
-- Stand-in structure for view `monthly_reservations`
-- (See below for the actual view)
--
CREATE TABLE `monthly_reservations` (
`year` int(4)
,`month` int(2)
,`total_reservations` bigint(21)
,`total_revenue` decimal(32,2)
,`average_amount` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `guests_count` int(11) NOT NULL,
  `special_requirements` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `facility_id`, `customer_name`, `customer_email`, `customer_phone`, `event_type`, `event_date`, `start_time`, `end_time`, `guests_count`, `special_requirements`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'John Smith', 'john.smith@email.com', '+1-555-0101', 'Wedding', '2025-10-16', '14:00:00', '22:00:00', 250, 'Require vegetarian meal options and champagne toast', 3600.00, 'confirmed', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(2, 2, 'Tech Solutions Inc.', 'events@techsolutions.com', '+1-555-0102', 'Business Meeting', '2025-10-12', '09:00:00', '17:00:00', 15, 'Need video conferencing setup and whiteboard markers', 960.00, 'confirmed', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(3, 3, 'Sarah Johnson', 'sarah.j@email.com', '+1-555-0103', 'Birthday Party', '2025-10-19', '18:00:00', '23:00:00', 80, 'Birthday cake and special decoration required', 1400.00, 'confirmed', '2025-10-09 11:47:10', '2025-10-09 11:47:54'),
(4, 4, 'Global Marketing Summit', 'info@globalmarketing.com', '+1-555-0104', 'Conference', '2025-10-23', '08:00:00', '18:00:00', 180, 'Multiple presentation setups and breakout sessions', 3500.00, 'confirmed', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(5, 5, 'Robert Wilson', 'robert.wilson@email.com', '+1-555-0105', 'Anniversary Dinner', '2025-10-14', '19:00:00', '22:00:00', 25, 'Private chef tasting menu with wine pairing', 450.00, 'confirmed', '2025-10-09 11:47:10', '2025-10-09 11:47:56'),
(6, 6, 'Startup Network', 'contact@startupnetwork.com', '+1-555-0106', 'Networking Event', '2025-10-11', '17:00:00', '21:00:00', 60, 'Standing reception with cocktail service', 800.00, 'confirmed', '2025-10-09 11:47:10', '2025-10-09 11:47:10'),
(7, 7, 'Creative Designs LLC', 'team@creativedesigns.com', '+1-555-0107', 'Team Meeting', '2025-10-09', '10:00:00', '12:00:00', 12, 'Need extra whiteboard space', 190.00, 'confirmed', '2025-10-09 11:47:10', '2025-10-09 11:47:57'),
(8, 8, 'Maria Garcia', 'maria.garcia@email.com', '+1-555-0108', 'Engagement Party', '2025-10-30', '16:00:00', '20:00:00', 75, 'Floral decorations and string quartet', 720.00, 'confirmed', '2025-10-09 11:47:10', '2025-10-09 11:47:48'),
(9, 2, 'roel', 'roel@gmail.com', '09092345651', 'Conference', '2025-10-09', '09:00:00', '12:00:00', 2, 'none', 360.00, 'confirmed', '2025-10-09 12:14:57', '2025-10-09 12:15:07'),
(10, 1, 'mark angelo barnido', 'markangelobarnido09@gmail.com', '09272369166', 'Wedding', '2026-11-21', '09:00:00', '12:00:00', 50, '', 1350.00, 'confirmed', '2025-10-09 12:26:24', '2025-10-09 12:26:56');

-- --------------------------------------------------------

--
-- Stand-in structure for view `reservation_details`
-- (See below for the actual view)
--
CREATE TABLE `reservation_details` (
`id` int(11)
,`facility_id` int(11)
,`customer_name` varchar(255)
,`customer_email` varchar(255)
,`customer_phone` varchar(20)
,`event_type` varchar(100)
,`event_date` date
,`start_time` time
,`end_time` time
,`guests_count` int(11)
,`special_requirements` text
,`total_amount` decimal(10,2)
,`status` enum('pending','confirmed','cancelled','completed')
,`created_at` timestamp
,`updated_at` timestamp
,`facility_name` varchar(255)
,`facility_type` enum('banquet','meeting','conference','outdoor','dining','lounge')
,`facility_capacity` int(11)
,`facility_location` varchar(255)
,`hourly_rate` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') DEFAULT 'staff',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@hotelfacilities.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 'active', NULL, '2025-10-09 11:47:10'),
(2, 'manager', 'manager@hotelfacilities.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Facility Manager', 'manager', 'active', NULL, '2025-10-09 11:47:10'),
(3, 'staff', 'staff@hotelfacilities.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Front Desk Staff', 'staff', 'active', NULL, '2025-10-09 11:47:10');

-- --------------------------------------------------------

--
-- Structure for view `monthly_reservations`
--
DROP TABLE IF EXISTS `monthly_reservations`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `monthly_reservations`  AS SELECT year(`reservations`.`event_date`) AS `year`, month(`reservations`.`event_date`) AS `month`, count(0) AS `total_reservations`, sum(`reservations`.`total_amount`) AS `total_revenue`, avg(`reservations`.`total_amount`) AS `average_amount` FROM `reservations` WHERE `reservations`.`status` = 'confirmed' GROUP BY year(`reservations`.`event_date`), month(`reservations`.`event_date`) ;

-- --------------------------------------------------------

--
-- Structure for view `reservation_details`
--
DROP TABLE IF EXISTS `reservation_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `reservation_details`  AS SELECT `r`.`id` AS `id`, `r`.`facility_id` AS `facility_id`, `r`.`customer_name` AS `customer_name`, `r`.`customer_email` AS `customer_email`, `r`.`customer_phone` AS `customer_phone`, `r`.`event_type` AS `event_type`, `r`.`event_date` AS `event_date`, `r`.`start_time` AS `start_time`, `r`.`end_time` AS `end_time`, `r`.`guests_count` AS `guests_count`, `r`.`special_requirements` AS `special_requirements`, `r`.`total_amount` AS `total_amount`, `r`.`status` AS `status`, `r`.`created_at` AS `created_at`, `r`.`updated_at` AS `updated_at`, `f`.`name` AS `facility_name`, `f`.`type` AS `facility_type`, `f`.`capacity` AS `facility_capacity`, `f`.`location` AS `facility_location`, `f`.`hourly_rate` AS `hourly_rate` FROM (`reservations` `r` join `facilities` `f` on(`r`.`facility_id` = `f`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_facilities_type` (`type`),
  ADD KEY `idx_facilities_status` (`status`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reservations_facility_date` (`facility_id`,`event_date`),
  ADD KEY `idx_reservations_status` (`status`),
  ADD KEY `idx_reservations_event_date` (`event_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
