-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 24, 2025 at 08:53 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `parking_system_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `log_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=250 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `user_id`, `action`, `log_time`) VALUES
(249, 2, 'User logged in', '2025-10-24 16:24:46'),
(248, 8, 'Started parking session #18', '2025-10-24 16:10:52'),
(247, 2, 'User logged in', '2025-10-24 16:05:32'),
(246, 2, 'User logged out', '2025-10-24 16:05:31'),
(245, 2, 'Approved payment #24 for amount $10.00', '2025-10-24 16:04:22'),
(244, 7, 'Ended parking session #16 (freed slot)', '2025-10-24 16:04:03'),
(243, 2, 'Approved payment #23 for amount $10.00', '2025-10-24 16:00:20'),
(242, 8, 'Ended parking session #17 (freed slot)', '2025-10-24 15:59:04'),
(241, 8, 'Started parking session #17', '2025-10-24 15:54:17'),
(240, 2, 'Updated parking slot: A1 to occupied', '2025-10-24 15:46:32'),
(239, 8, 'User logged in', '2025-10-24 15:46:09'),
(238, 8, 'Driver account created', '2025-10-24 15:45:51'),
(237, 2, 'Updated parking slot: A1 to available', '2025-10-24 15:44:27'),
(236, 7, 'Started parking session #16', '2025-10-24 15:44:13'),
(235, 2, 'Added new parking slot: B5', '2025-10-24 15:43:59'),
(234, 2, 'Added new parking slot: B4', '2025-10-24 15:43:52'),
(233, 2, 'Added new parking slot: B3', '2025-10-24 15:42:58'),
(232, 2, 'Added new parking slot: B1', '2025-10-24 15:42:40'),
(231, 2, 'Added new parking slot: A5', '2025-10-24 15:42:34'),
(230, 2, 'Added new parking slot: A4', '2025-10-24 15:42:28'),
(229, 2, 'Added new parking slot: A3', '2025-10-24 15:42:23'),
(228, 2, 'Added new parking slot: A2', '2025-10-24 15:42:08'),
(227, 2, 'Added new parking slot: B2', '2025-10-24 15:41:54'),
(226, 2, 'Added new parking slot: A1', '2025-10-24 15:41:49'),
(225, 7, 'User logged in', '2025-10-24 15:41:25'),
(224, 7, 'Driver account created', '2025-10-24 15:40:58');

-- --------------------------------------------------------

--
-- Table structure for table `parking_sessions`
--

DROP TABLE IF EXISTS `parking_sessions`;
CREATE TABLE IF NOT EXISTS `parking_sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `slot_id` int NOT NULL,
  `vehicle_id` int NOT NULL,
  `start_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` datetime DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `slot_id` (`slot_id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `parking_sessions`
--

INSERT INTO `parking_sessions` (`session_id`, `user_id`, `slot_id`, `vehicle_id`, `start_time`, `end_time`, `status`) VALUES
(18, 8, 11, 7, '2025-10-24 16:10:52', NULL, 'active'),
(17, 8, 8, 7, '2025-10-24 15:54:17', '2025-10-24 16:00:20', 'completed'),
(16, 7, 7, 6, '2025-10-24 15:44:13', '2025-10-24 16:04:22', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `parking_slots`
--

DROP TABLE IF EXISTS `parking_slots`;
CREATE TABLE IF NOT EXISTS `parking_slots` (
  `slot_id` int NOT NULL AUTO_INCREMENT,
  `slot_number` varchar(20) NOT NULL,
  `status` enum('available','occupied','maintenance') DEFAULT 'available',
  `last_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`slot_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `parking_slots`
--

INSERT INTO `parking_slots` (`slot_id`, `slot_number`, `status`, `last_updated`) VALUES
(10, 'A3', 'available', '2025-10-24 15:42:23'),
(9, 'A2', 'available', '2025-10-24 15:42:08'),
(8, 'B2', 'available', '2025-10-24 16:00:20'),
(7, 'A1', 'available', '2025-10-24 16:04:22'),
(11, 'A4', 'occupied', '2025-10-24 16:10:52'),
(12, 'A5', 'available', '2025-10-24 15:42:34'),
(13, 'B1', 'available', '2025-10-24 15:42:40'),
(14, 'B3', 'available', '2025-10-24 15:42:58'),
(15, 'B4', 'available', '2025-10-24 15:43:52'),
(16, 'B5', 'available', '2025-10-24 15:43:59');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash') DEFAULT 'cash',
  `payment_status` enum('pending','approved','declined') DEFAULT 'pending',
  `approved_by` int DEFAULT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `reference_number` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `session_id` (`session_id`),
  KEY `approved_by` (`approved_by`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `session_id`, `amount`, `payment_method`, `payment_status`, `approved_by`, `payment_date`, `reference_number`) VALUES
(24, 16, 10.00, 'cash', 'approved', 2, '2025-10-24 16:04:22', 'PMT-20251024-51C179'),
(23, 17, 10.00, 'cash', 'approved', 2, '2025-10-24 16:00:20', 'PMT-20251024-038257');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(75) NOT NULL,
  `last_name` varchar(75) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('admin','driver') NOT NULL DEFAULT 'driver',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `date_registered` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `first_name`, `last_name`, `full_name`, `email`, `role`, `status`, `last_login`, `date_registered`, `updated_at`) VALUES
(2, 'admin@parksmart.com', '$2y$10$AeSRx1uc9nfg8htCWjWzaeEfAIEfg1agQb4nA.rrqj/5BG7hiTMa.', 'Admin', '', 'Admin User', 'admin@parksmart.com', 'admin', 'active', '2025-10-24 16:24:46', '2025-10-23 00:18:20', '2025-10-24 16:24:46'),
(8, 'jecelleeudilla18@gmail.com', '$2y$10$38EZfsbv91Nnvg6X1ABWIOe/WFurhH9r.o4lxTWwBqYbmIfM9HaCa', 'Jecelle', 'Eudilla', 'Jecelle Eudilla', 'jecelleeudilla18@gmail.com', 'driver', 'active', '2025-10-24 15:46:09', '2025-10-24 15:45:51', '2025-10-24 15:46:09'),
(7, 'renatoastrologo75@gmail.com', '$2y$10$7J.7z3qsRGTJ8G.QukMlDOVZiUD7M4UEpkreNKrJdNUMZ/Q6eitWO', 'Renato', 'Astrologo', 'Renato Astrologo', 'renatoastrologo75@gmail.com', 'driver', 'active', '2025-10-24 15:41:25', '2025-10-24 15:40:58', '2025-10-24 15:41:25');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `vehicle_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `vehicle_type` enum('car','motorcycle','van','truck','suv') NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `date_registered` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`vehicle_id`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `user_id`, `plate_number`, `vehicle_type`, `color`, `is_active`, `date_registered`, `updated_at`) VALUES
(7, 8, '1P215', 'truck', 'Black', 1, '2025-10-24 15:45:51', '2025-10-24 15:45:51'),
(6, 7, '3POC1', 'suv', 'Black', 1, '2025-10-24 15:40:58', '2025-10-24 15:40:58');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
