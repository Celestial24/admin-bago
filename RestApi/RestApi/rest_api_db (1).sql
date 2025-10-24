-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 07, 2025 at 03:16 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rest_api_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `Products`
--

CREATE TABLE `Products` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(100) NOT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `UnitPrice` decimal(10,2) NOT NULL,
  `StockQuantity` int(11) NOT NULL DEFAULT 0,
  `Supplier` varchar(100) DEFAULT NULL,
  `ReorderLevel` int(11) DEFAULT 0,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Products`
--

INSERT INTO `Products` (`ProductID`, `ProductName`, `Category`, `UnitPrice`, `StockQuantity`, `Supplier`, `ReorderLevel`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'Laptop Pro 15\"', 'Electronics', 1200.00, 10, 'TechWorld Inc.', 2, '2025-09-09 13:55:16', '2025-09-09 13:55:16'),
(2, 'Wireless Mouse', 'Electronics', 25.99, 150, 'Gadget Supply Co.', 20, '2025-09-09 13:55:16', '2025-09-09 13:55:16'),
(3, 'Office Chair', 'Furniture', 199.50, 35, 'Comfort Furnishings', 5, '2025-09-09 13:55:16', '2025-09-09 13:55:16'),
(4, 'Running Shoes', 'Clothing', 75.00, 80, 'Sportify Ltd.', 10, '2025-09-09 13:55:16', '2025-09-09 13:55:16'),
(5, 'Coffee Beans 1kg', 'Grocery', 15.25, 200, 'Fresh Market', 50, '2025-09-09 13:55:16', '2025-09-09 13:55:16'),
(8, 'iphone 15', 'Electronics', 26000.00, 2, 'TechWorld Inc.', 3, '2025-09-09 13:58:17', '2025-09-09 13:58:17'),
(9, ' Shoescharles', 'Clothing', 800.00, 800, 'Sportify Ltd.', 10, '2025-09-09 14:03:54', '2025-09-09 14:04:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `created_at`) VALUES
(5, 'update', 'eve@example.com', '2025-09-06 04:15:29'),
(7, 'perea', 'usejssjr@gmail.com', '2025-09-08 08:01:56'),
(8, 'add', 'okok@example.com', '2025-09-08 08:02:36'),
(9, 'mmother', 'o8kok@example.com', '2025-09-08 09:14:32'),
(10, 'ichan', 'costibolo45@gmail.com', '2025-09-09 06:53:01'),
(12, 'okoksample', 'sample@example.com', '2025-09-09 07:32:46'),
(14, 'addsample', 'sample12@example.com', '2025-09-09 13:48:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Products`
--
ALTER TABLE `Products`
  ADD PRIMARY KEY (`ProductID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Products`
--
ALTER TABLE `Products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
