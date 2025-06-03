-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 03, 2025 at 07:24 AM
-- Server version: 10.11.11-MariaDB-0ubuntu0.24.04.2
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `management_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'available',
  `added_date` date DEFAULT NULL,
  `taken_by` varchar(100) DEFAULT NULL,
  `taken_date` date DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `unit` text NOT NULL DEFAULT 'M',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `description`, `status`, `added_date`, `taken_by`, `taken_date`, `value`, `unit`, `created_at`, `updated_at`) VALUES
(2, 'BELDEN UTP 305M', 'BELDEN UTP 305M', 'taken', NULL, 'yono', '2025-06-02', 300.00, 'M', '2025-06-02 06:47:49', '2025-06-02 06:48:02'),
(3, 'UTP 100CM', 'UTP CAT 6E', 'available', NULL, NULL, NULL, 100.00, 'CM', '2025-06-02 06:59:23', '2025-06-02 06:59:23');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `log_message` text NOT NULL,
  `log_time` timestamp NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `log_message`, `log_time`, `user_id`) VALUES
(1, 'Failed login: username \"admin\" not found.', '2025-06-02 06:42:03', NULL),
(2, 'User \"admin\" successfully logged in.', '2025-06-02 06:42:53', NULL),
(3, 'User \'admin\' added new inventory item: \'Belden UTP 305M\'.', '2025-06-02 06:43:30', NULL),
(4, 'User \'admin\' added new inventory item: \'BELDEN UTP 305M\'.', '2025-06-02 06:47:49', NULL),
(5, 'User \'admin\' added new inventory item: \'UTP 100CM\'.', '2025-06-02 06:59:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `material_request`
--

CREATE TABLE `material_request` (
  `id` int(11) NOT NULL,
  `No` varchar(255) DEFAULT NULL,
  `requester_name` varchar(100) NOT NULL,
  `require_for` varchar(255) DEFAULT NULL,
  `china_controller` varchar(100) DEFAULT NULL,
  `aknowledge_by` varchar(100) DEFAULT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `request_date` timestamp NULL DEFAULT current_timestamp(),
  `last_changed` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mr_item`
--

CREATE TABLE `mr_item` (
  `id` int(11) NOT NULL,
  `mr_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `pn` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `inventory_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `enable_website` tinyint(4) NOT NULL DEFAULT 1,
  `enable_register` tinyint(4) NOT NULL DEFAULT 0,
  `enable_login` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `enable_website`, `enable_register`, `enable_login`) VALUES
(1, 1, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `taken_values`
--

CREATE TABLE `taken_values` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` text NOT NULL DEFAULT '',
  `taken_by` text NOT NULL DEFAULT '',
  `taken_date` datetime NOT NULL DEFAULT current_timestamp(),
  `taken_value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `taken_values`
--

INSERT INTO `taken_values` (`id`, `item_id`, `item_name`, `taken_by`, `taken_date`, `taken_value`) VALUES
(2, 2, 'BELDEN UTP 305M', 'yono', '2025-06-02 06:47:00', 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_path` text NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `profile_path`, `email`, `created_at`) VALUES
(1, 'admin', '$2a$12$FdLE5DERDC2hBBgB4Vois.aq6s9R0u53Jx3ztaI7426BDkFodkgdC', '', 'admin@example.com', '2025-06-02 06:42:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `material_request`
--
ALTER TABLE `material_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mr_item`
--
ALTER TABLE `mr_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mr_id` (`mr_id`),
  ADD KEY `inventory_item_id` (`inventory_item_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taken_values`
--
ALTER TABLE `taken_values`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `material_request`
--
ALTER TABLE `material_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mr_item`
--
ALTER TABLE `mr_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `taken_values`
--
ALTER TABLE `taken_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `material_request`
--
ALTER TABLE `material_request`
  ADD CONSTRAINT `material_request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mr_item`
--
ALTER TABLE `mr_item`
  ADD CONSTRAINT `mr_item_ibfk_1` FOREIGN KEY (`mr_id`) REFERENCES `material_request` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mr_item_ibfk_2` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
