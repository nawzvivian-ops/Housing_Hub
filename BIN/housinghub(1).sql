-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 15, 2026 at 07:58 AM
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
-- Database: `housinghub`
--

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

DROP TABLE IF EXISTS `amenities`;
CREATE TABLE IF NOT EXISTS `amenities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `cost_type` enum('Free','Paid') NOT NULL DEFAULT 'Free',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `name`, `cost_type`) VALUES
(1, 'Electricity Connection', 'Paid'),
(2, 'Water Connection', 'Paid'),
(3, 'Fencing', 'Paid'),
(4, 'Security Cameras', 'Paid'),
(5, 'Street Lighting', 'Free'),
(6, 'Parking Lot', 'Paid'),
(7, 'Warehouse Space', 'Paid'),
(8, 'Swimming', 'Free'),
(9, 'Gym', 'Paid'),
(10, 'Office Space', 'Paid'),
(11, 'Internet', 'Paid'),
(12, 'Security', 'Free'),
(13, 'maintenance', 'Free'),
(14, 'WIFI', 'Free'),
(15, 'Storage Units', 'Paid'),
(16, 'Waste Disposal', 'Paid'),
(17, 'Electricity', 'Paid'),
(18, 'Water', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_id` int NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `experience` text,
  `resume` varchar(255) DEFAULT NULL,
  `cover_letter` varchar(255) DEFAULT NULL,
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','reviewed','rejected','hired') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `full_name`, `email`, `phone`, `address`, `experience`, `resume`, `cover_letter`, `applied_at`, `status`) VALUES
(1, 1, 'Kasana Naume', 'kasananaume@gmail.com', '0774842215', 'kasana-Luwero', 'Skilled personnel and keeping people safe and their properties.', '1769161362_concept note.docx', NULL, '2026-01-23 09:42:42', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_messages`
--

DROP TABLE IF EXISTS `chatbot_messages`;
CREATE TABLE IF NOT EXISTS `chatbot_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `sender` enum('user','ai') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chatbot_messages`
--

INSERT INTO `chatbot_messages` (`id`, `user_id`, `message`, `sender`, `created_at`, `admin_read`) VALUES
(1, 0, 'how do i login', 'user', '2026-02-25 18:14:00', 0),
(2, 0, 'I can only assist with HousingHub services.', 'ai', '2026-02-25 18:14:00', 0),
(3, 0, 'okey', 'user', '2026-02-25 18:14:10', 0),
(4, 0, 'I can only assist with HousingHub services.', 'ai', '2026-02-25 18:14:10', 0),
(5, 0, 'features', 'user', '2026-02-25 18:14:21', 0),
(6, 0, 'I can only assist with HousingHub services.', 'ai', '2026-02-25 18:14:21', 0),
(7, 0, 'how do i pay rent', 'user', '2026-02-25 18:15:34', 0),
(8, 0, 'You can pay rent through your dashboard under Rent Collection.', 'ai', '2026-02-25 18:15:34', 0),
(9, 0, 'how do i register', 'user', '2026-02-25 18:15:58', 0),
(10, 0, 'Click Register at the top menu to create your account.', 'ai', '2026-02-25 18:15:58', 0),
(11, 0, 'WHATS YOUR NAME', 'user', '2026-02-25 18:44:17', 0),
(12, 0, 'I can only assist with HousingHub services.', 'ai', '2026-02-25 18:44:17', 0),
(13, 0, 'WHATS YOUR NAME', 'user', '2026-02-25 18:44:28', 0),
(14, 0, 'I can only assist with HousingHub services.', 'ai', '2026-02-25 18:44:28', 0),
(15, 0, 'WHAT DOES HOUSING HUB DO', 'user', '2026-02-25 18:44:47', 0),
(16, 0, 'I can only assist with HousingHub services.', 'ai', '2026-02-25 18:44:47', 0);

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

DROP TABLE IF EXISTS `commissions`;
CREATE TABLE IF NOT EXISTS `commissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `broker_id` int DEFAULT NULL,
  `property_id` int DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','paid') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `broker_id` (`broker_id`),
  KEY `property_id` (`property_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
CREATE TABLE IF NOT EXISTS `complaints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `category` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `property_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_property` (`user_id`,`property_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `property_id`, `created_at`) VALUES
(1, 6, 1, '2026-01-19 18:28:34'),
(2, 6, 2, '2026-01-19 18:29:01'),
(3, 12, 1, '2026-01-30 06:40:15');

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

DROP TABLE IF EXISTS `guests`;
CREATE TABLE IF NOT EXISTS `guests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int DEFAULT NULL,
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `visiting_tenant_id` int DEFAULT NULL,
  `property_id` int DEFAULT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `visit_type` enum('property','tenant') DEFAULT 'property',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `tenant_id`, `fullname`, `phone`, `visiting_tenant_id`, `property_id`, `check_in`, `check_out`, `created_at`, `email`, `password`, `status`, `visit_type`) VALUES
(1, NULL, 'Mukulu Mashal', '0764164872', 1, 7, '0000-00-00 00:00:00', '2026-01-31 07:05:00', '2026-01-31 10:35:38', 'mukulumashal@gmail.com', NULL, 'Rejected', 'property'),
(2, NULL, 'Mukulu Mashal', '0764164872', 1, 7, '0000-00-00 00:00:00', '2026-01-31 07:05:00', '2026-01-31 10:51:46', 'mukulumashal@gmail.com', NULL, 'Approved', 'property');

-- --------------------------------------------------------

--
-- Table structure for table `inspections`
--

DROP TABLE IF EXISTS `inspections`;
CREATE TABLE IF NOT EXISTS `inspections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `tenant_id` int DEFAULT NULL,
  `inspector_name` varchar(255) NOT NULL,
  `inspection_date` date NOT NULL,
  `situation` varchar(100) NOT NULL,
  `notes` text,
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `notified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `freebies` text,
  `slug` varchar(100) DEFAULT NULL,
  `status` enum('open','taken') DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `description`, `location`, `type`, `freebies`, `slug`, `status`, `created_at`) VALUES
(1, 'Security Guard', 'Ensure safety of properties and staff.', 'Kampala, Uganda', 'Full-time', 'Training, uniform provided, stable monthly pay', 'security-guard', 'open', '2026-01-18 08:38:28'),
(2, 'Field Assistant', 'Assist with deliveries and property visits.', 'Kampala, Uganda', 'Full-time / Part-time', 'Transport allowance, on-the-job training', 'field-assistant', 'open', '2026-01-18 08:38:28'),
(3, 'Receptionist', 'Greet visitors and handle office tasks.', 'Kampala, Uganda', 'Full-time', 'Friendly environment, training', 'receptionist', 'open', '2026-01-18 08:41:32'),
(4, 'Electricians', 'For electricity solutions..', 'jinja, Uganda', 'Full-time', 'Transport allowance, friendlyteam', 'Electricians', 'open', '2026-01-18 08:47:09'),
(5, 'Plumbers', 'Assist with water blockages and other related water challenges.', 'Mukono, Uganda', 'Part-time', 'Transport allowance', 'Plumbers', 'open', '2026-01-18 08:47:09');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

DROP TABLE IF EXISTS `job_applications`;
CREATE TABLE IF NOT EXISTS `job_applications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `resume` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
CREATE TABLE IF NOT EXISTS `leads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `broker_id` int DEFAULT NULL,
  `property_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `notes` text,
  `status` enum('new','follow_up','closed') DEFAULT 'new',
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `broker_id` (`broker_id`),
  KEY `property_id` (`property_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leases`
--

DROP TABLE IF EXISTS `leases`;
CREATE TABLE IF NOT EXISTS `leases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `lease_type` enum('Rent','Lease') DEFAULT 'Rent',
  `rent_amount` decimal(12,2) DEFAULT NULL,
  `payment_frequency` enum('Monthly','Quarterly') DEFAULT 'Monthly',
  `deposit` decimal(12,2) DEFAULT NULL,
  `status` enum('Active','Expired','Terminated') DEFAULT 'Active',
  `agreement_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `leases`
--

INSERT INTO `leases` (`id`, `tenant_id`, `lease_type`, `rent_amount`, `payment_frequency`, `deposit`, `status`, `agreement_file`) VALUES
(1, 2, 'Lease', 5000000.00, 'Monthly', NULL, 'Active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

DROP TABLE IF EXISTS `maintenance`;
CREATE TABLE IF NOT EXISTS `maintenance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int DEFAULT NULL,
  `tenant_id` int DEFAULT NULL,
  `issue` text,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

DROP TABLE IF EXISTS `maintenance_requests`;
CREATE TABLE IF NOT EXISTS `maintenance_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `tenant_id` int DEFAULT NULL,
  `issue` varchar(255) NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `assigned_staff` int DEFAULT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` date DEFAULT NULL,
  `notified` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `assigned_staff` (`assigned_staff`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_role` enum('staff','tenant') NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `tenant_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('unread','read') DEFAULT 'unread',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `is_read`, `tenant_id`, `title`, `message`, `date`, `status`) VALUES
(1, 0, 0, 1, 'Rent Reminder', 'Your rent is due on 1st march.', '2026-01-30 22:42:29', 'unread'),
(2, 0, 0, 1, 'Maintenance', 'Water system inspection scheduled.', '2026-01-30 22:42:29', 'unread'),
(3, 0, 0, 1, 'Visitor Request', 'You have a visitor request from Mukulu Mashal.', '2026-01-31 13:51:46', 'unread'),
(4, 0, 0, 1, 'Guest Request Update', 'Your visitor request from Mukulu Mashal has been Approved.', '2026-02-01 09:37:16', 'unread'),
(5, 0, 0, 1, 'Guest Request Update', 'Your visitor request from Mukulu Mashal has been Rejected.', '2026-02-01 09:37:27', 'unread'),
(6, 0, 0, 0, 'Contact Form: rent', 'From: vivian nawaguma\nEmail: vivian@yahoo.com\nPhone: +256 789 654 321\nRole: Tenant\n\nMessage:\nhow do i pay', '2026-03-07 12:58:54', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `property_id` int NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('mobile_money','card','bank') DEFAULT NULL,
  `transaction_ref` varchar(100) DEFAULT NULL,
  `payment_response` text,
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_payments_tenant` (`tenant_id`),
  KEY `fk_payments_property` (`property_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `tenant_id`, `property_id`, `amount`, `payment_method`, `transaction_ref`, `payment_response`, `status`, `date`, `updated_at`, `created_at`, `due_date`) VALUES
(1, 2, 5, 7000.00, 'mobile_money', 'TXN17729589546475', NULL, 'pending', '2026-03-08 11:35:54', NULL, '2026-03-08 08:35:54', NULL),
(2, 2, 5, 7000.00, 'mobile_money', 'TXN17729589616626', NULL, 'pending', '2026-03-08 11:36:01', NULL, '2026-03-08 08:36:01', NULL),
(3, 2, 5, 7000.00, 'card', 'TXN17729591081394', NULL, 'pending', '2026-03-08 11:38:28', NULL, '2026-03-08 08:38:28', NULL),
(4, 2, 5, 7000.00, 'mobile_money', 'TXN17729591205693', NULL, 'pending', '2026-03-08 11:38:40', NULL, '2026-03-08 08:38:40', NULL),
(5, 2, 5, 7000.00, 'card', 'TXN17729592999167', NULL, 'pending', '2026-03-08 11:41:39', NULL, '2026-03-08 08:41:39', NULL),
(6, 2, 5, 7000.00, 'bank', 'TXN17729593371814', NULL, 'pending', '2026-03-08 11:42:17', NULL, '2026-03-08 08:42:17', NULL),
(7, 2, 5, 7000.00, 'card', 'TXN17729593446463', NULL, 'pending', '2026-03-08 11:42:24', NULL, '2026-03-08 08:42:24', NULL),
(8, 2, 5, 7000.00, 'card', 'TXN17729595219288', NULL, 'pending', '2026-03-08 11:45:21', NULL, '2026-03-08 08:45:21', NULL),
(9, 2, 5, 7000.00, 'bank', 'TXN17729595326036', NULL, 'pending', '2026-03-08 11:45:32', NULL, '2026-03-08 08:45:32', NULL),
(10, 2, 5, 7000.00, 'bank', 'TXN17729596636802', NULL, 'pending', '2026-03-08 11:47:43', NULL, '2026-03-08 08:47:43', NULL),
(11, 5, 1, 1200.00, 'card', 'TXN17735512561785', NULL, 'pending', '2026-03-15 08:07:36', NULL, '2026-03-15 05:07:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `price_breakdown`
--

DROP TABLE IF EXISTS `price_breakdown`;
CREATE TABLE IF NOT EXISTS `price_breakdown` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `monthly_rent` decimal(12,2) NOT NULL,
  `deposit` decimal(12,2) DEFAULT '0.00',
  `service_charge` decimal(12,2) DEFAULT '0.00',
  `other_fees` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `price_breakdown`
--

INSERT INTO `price_breakdown` (`id`, `property_id`, `monthly_rent`, `deposit`, `service_charge`, `other_fees`, `created_at`) VALUES
(1, 1, 1200000.00, 600000.00, 200000.00, 0.00, '2026-01-19 18:03:55'),
(2, 2, 5000000.00, 2500000.00, 200000.00, 0.00, '2026-01-19 18:06:28'),
(3, 3, 3000000.00, 1500000.00, 50000.00, 0.00, '2026-01-19 18:07:28'),
(4, 4, 2000000.00, 500000.00, 50000.00, 0.00, '2026-01-19 18:08:24'),
(5, 5, 7000000.00, 1700000.00, 500000.00, 0.00, '2026-01-19 18:09:24'),
(6, 6, 2500000.00, 1000000.00, 500000.00, 0.00, '2026-01-19 18:10:36'),
(7, 7, 1500000.00, 100000.00, 50000.00, 0.00, '2026-01-19 18:11:31'),
(8, 9, 8000000.00, 4000000.00, 500000.00, 0.00, '2026-01-19 18:12:28'),
(9, 12, 1800000.00, 1000000.00, 50000.00, 0.00, '2026-01-19 18:13:28');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
CREATE TABLE IF NOT EXISTS `properties` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int DEFAULT NULL,
  `property_name` varchar(255) NOT NULL,
  `property_type` varchar(100) DEFAULT NULL,
  `address` text,
  `units` int DEFAULT '0',
  `rent_amount` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `purpose` enum('Rent','Buy','Lease') DEFAULT 'Rent',
  `bedrooms` int DEFAULT NULL,
  `size_sqft` int DEFAULT NULL,
  `amenities` text,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `broker_id` int DEFAULT NULL,
  `status` enum('available','occupied','archived') DEFAULT 'available',
  `description` text,
  `commission_rate` decimal(5,2) DEFAULT '0.00',
  `commission_percentage` decimal(5,2) DEFAULT '0.00' COMMENT 'Commission rate for broker in %',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `owner_id`, `property_name`, `property_type`, `address`, `units`, `rent_amount`, `created_at`, `purpose`, `bedrooms`, `size_sqft`, `amenities`, `latitude`, `longitude`, `broker_id`, `status`, `description`, `commission_rate`, `commission_percentage`) VALUES
(1, 1, 'Sunset Apartments', 'Residential', '123 Main St, Kampala', 3, 1200.00, '2026-01-19 09:17:16', 'Rent', 3, 1400, NULL, NULL, NULL, NULL, 'available', NULL, 0.00, 0.00),
(2, 2, 'Industrial Warehouse', 'industrial', '45 Namanve industrial Area, Kampala', 1, 5000.00, '2026-01-19 09:17:16', 'Lease', 0, 10000, NULL, NULL, NULL, NULL, 'available', NULL, 0.00, 0.00),
(3, 3, 'Sunny Land Plots', 'Land', 'kityo road, Entebbe', 0, 3000.00, '2026-01-19 09:17:16', 'Buy', NULL, 20000, NULL, NULL, NULL, NULL, 'available', NULL, 0.00, 0.00),
(4, 4, 'Rock Wood Rentals', 'Residential', 'Bweyogerere Ward B, Wakiso', 10, 2000.00, '2026-01-19 09:17:16', 'Rent', 2, 1800, NULL, NULL, NULL, NULL, 'available', NULL, 0.00, 0.00),
(5, 5, 'Central Office Block', 'Commercial', 'Block D , Kampala', 2, 7000.00, '2026-01-19 09:17:16', 'Rent', 0, 8000, NULL, NULL, NULL, NULL, 'available', NULL, 0.00, 0.00),
(6, 6, 'Green Acres', 'Land', ' opposite kigungu kids Educare Entebbe', 0, 2500.00, '2026-01-19 09:17:16', 'Buy', NULL, 15000, NULL, NULL, NULL, NULL, 'available', NULL, 0.00, 0.00),
(7, 7, 'Lakeview Apartments', 'Residential', 'Jinja Highway, Jinja', 5, 1500.00, '2026-01-19 09:17:16', 'Rent', 3, 1300, NULL, NULL, NULL, NULL, 'available', NULL, 0.00, 0.00),
(9, 9, 'Hillside Plots', 'Land', '77 Hill Rd, Entebbe', 0, 8000.00, '2026-01-19 09:17:16', 'Buy', NULL, 18000, NULL, NULL, NULL, NULL, 'available', NULL, 0.00, 0.00),
(12, 12, 'Maple Residency', 'Residential', '22 St Mark Church, Jinja', 3, 1800.00, '2026-01-19 09:17:16', 'Rent', 3, 1600, NULL, NULL, NULL, NULL, 'available', NULL, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `property_amenities`
--

DROP TABLE IF EXISTS `property_amenities`;
CREATE TABLE IF NOT EXISTS `property_amenities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `amenity_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `property_id` (`property_id`,`amenity_id`),
  KEY `amenity_id` (`amenity_id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `property_amenities`
--

INSERT INTO `property_amenities` (`id`, `property_id`, `amenity_id`) VALUES
(1, 1, 5),
(2, 1, 8),
(3, 1, 9),
(4, 1, 12),
(5, 1, 14),
(6, 2, 6),
(7, 2, 7),
(8, 2, 10),
(9, 2, 11),
(10, 2, 15),
(11, 3, 1),
(12, 3, 2),
(13, 3, 3),
(14, 3, 5),
(15, 3, 16),
(16, 4, 5),
(17, 4, 8),
(18, 4, 12),
(19, 4, 14),
(20, 4, 9),
(21, 5, 6),
(22, 5, 10),
(23, 5, 11),
(24, 5, 7),
(25, 5, 15),
(26, 6, 1),
(27, 6, 2),
(28, 6, 3),
(29, 6, 5),
(30, 6, 16),
(31, 7, 5),
(32, 7, 8),
(33, 7, 9),
(34, 7, 12),
(35, 7, 14),
(36, 8, 6),
(37, 8, 7),
(38, 8, 10),
(39, 8, 11),
(40, 8, 15),
(41, 9, 1),
(42, 9, 2),
(43, 9, 3),
(44, 9, 5),
(45, 9, 16),
(46, 10, 6),
(47, 10, 10),
(48, 10, 11),
(49, 10, 7),
(50, 10, 15),
(51, 11, 9),
(52, 11, 7),
(53, 11, 14),
(54, 11, 11),
(55, 11, 8),
(56, 12, 5),
(57, 12, 8),
(58, 12, 9),
(59, 12, 12),
(60, 12, 14);

-- --------------------------------------------------------

--
-- Table structure for table `property_charges`
--

DROP TABLE IF EXISTS `property_charges`;
CREATE TABLE IF NOT EXISTS `property_charges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `deposit` decimal(10,2) DEFAULT NULL,
  `service_charge` decimal(10,2) DEFAULT NULL,
  `paid_amenities_cost` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_favorites`
--

DROP TABLE IF EXISTS `property_favorites`;
CREATE TABLE IF NOT EXISTS `property_favorites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `property_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`property_id`),
  KEY `property_id` (`property_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

DROP TABLE IF EXISTS `property_images`;
CREATE TABLE IF NOT EXISTS `property_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_property_images_property` (`property_id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_path`, `is_primary`, `created_at`) VALUES
(1, 1, 'property_media/apartment.png', 0, '2026-01-19 13:42:48'),
(2, 1, 'property_media/sunset_apartment.png', 0, '2026-01-19 13:42:48'),
(3, 1, 'property_media/pool.png', 0, '2026-01-19 13:42:48'),
(4, 1, 'property_media/bedroom.png', 0, '2026-01-19 13:42:48'),
(5, 1, 'property_media/inside.png', 0, '2026-01-19 13:42:48'),
(6, 1, 'property_media/parking.png', 0, '2026-01-19 13:42:48'),
(7, 2, 'property_media/warehouse.png', 0, '2026-01-19 14:06:44'),
(8, 2, 'property_media/mach.png', 0, '2026-01-19 14:06:44'),
(9, 2, 'property_media/i.png', 0, '2026-01-19 14:06:44'),
(10, 2, 'property_media/r.png', 0, '2026-01-19 14:06:44'),
(11, 2, 'property_media/offy.png', 0, '2026-01-19 14:06:44'),
(12, 2, 'property_media/in.png', 0, '2026-01-19 14:06:44'),
(13, 3, 'property_media/v.png', 0, '2026-01-19 14:20:11'),
(16, 4, 'property_media/bed1.png', 0, '2026-01-19 14:38:50'),
(18, 5, 'property_media/ofi.png', 0, '2026-01-19 16:02:17'),
(19, 5, 'property_media/of.png', 0, '2026-01-19 16:02:17'),
(20, 6, 'property_media/w.png', 0, '2026-01-19 16:22:28'),
(21, 6, 'property_media/la.png', 0, '2026-01-19 16:22:28'),
(22, 7, 'property_media/lvp.png', 0, '2026-01-19 16:46:09'),
(23, 7, 'property_media/bedroom.png', 0, '2026-01-19 16:46:09'),
(24, 7, 'property_media/pol.png', 0, '2026-01-19 16:46:09'),
(25, 7, 'property_media/vg.png', 0, '2026-01-19 16:46:09'),
(26, 7, 'property_media/ins.png', 0, '2026-01-19 16:46:09'),
(30, 12, 'property_media/bed2.png', 0, '2026-01-19 17:51:46'),
(29, 9, 'property_media/milo.png', 0, '2026-01-19 17:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `property_reviews`
--

DROP TABLE IF EXISTS `property_reviews`;
CREATE TABLE IF NOT EXISTS `property_reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating_cleanliness` int DEFAULT NULL,
  `rating_security` int DEFAULT NULL,
  `rating_value` int DEFAULT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `user_id` (`user_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `property_visits`
--

DROP TABLE IF EXISTS `property_visits`;
CREATE TABLE IF NOT EXISTS `property_visits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `user_id` int NOT NULL,
  `visitor_name` varchar(150) DEFAULT NULL,
  `visitor_phone` varchar(20) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `status` enum('Pending','Approved','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `fk_visit_user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `property_visits`
--

INSERT INTO `property_visits` (`id`, `property_id`, `user_id`, `visitor_name`, `visitor_phone`, `visit_date`, `visit_time`, `status`, `created_at`) VALUES
(1, 5, 2, NULL, NULL, '2026-03-12', '23:05:00', 'Pending', '2026-03-12 20:38:46'),
(2, 5, 2, NULL, NULL, '2026-03-12', '04:07:00', 'Pending', '2026-03-12 20:39:12');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating_cleanliness` int DEFAULT NULL,
  `rating_security` int DEFAULT NULL,
  `rating_value` int DEFAULT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_property` (`user_id`,`property_id`),
  KEY `idx_property_id` (`property_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text,
  `status` enum('Upcoming','Completed','Cancelled') DEFAULT 'Upcoming',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL DEFAULT 'HousingHub',
  `email` varchar(255) DEFAULT NULL,
  `notification_email` varchar(255) DEFAULT NULL,
  `backup_frequency` enum('daily','weekly','monthly') DEFAULT 'weekly',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `assigned_to` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` date DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `assigned_by` varchar(255) NOT NULL DEFAULT 'Admin',
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `property_id` int DEFAULT NULL,
  `lease_start` date DEFAULT NULL,
  `lease_end` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `national_id` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(50) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `fullname`, `email`, `phone`, `property_id`, `lease_start`, `lease_end`, `created_at`, `gender`, `national_id`, `occupation`, `emergency_name`, `emergency_phone`, `user_id`, `status`) VALUES
(1, 'rovin', 'rovin@gmail.com', '+256 345 869 098', 1, NULL, NULL, '2026-03-14 20:26:09', NULL, NULL, NULL, NULL, NULL, 5, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_documents`
--

DROP TABLE IF EXISTS `tenant_documents`;
CREATE TABLE IF NOT EXISTS `tenant_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_messages`
--

DROP TABLE IF EXISTS `tenant_messages`;
CREATE TABLE IF NOT EXISTS `tenant_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_notes`
--

DROP TABLE IF EXISTS `tenant_notes`;
CREATE TABLE IF NOT EXISTS `tenant_notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_status`
--

DROP TABLE IF EXISTS `tenant_status`;
CREATE TABLE IF NOT EXISTS `tenant_status` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `status` enum('Active','Notice Given','Evicted','Left') DEFAULT 'Active',
  `move_in` date DEFAULT NULL,
  `move_out` date DEFAULT NULL,
  `reason` text,
  `complaints` int DEFAULT '0',
  `damages` text,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tenant_status`
--

INSERT INTO `tenant_status` (`id`, `tenant_id`, `status`, `move_in`, `move_out`, `reason`, `complaints`, `damages`) VALUES
(1, 1, 'Active', '2026-02-01', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenant_utilities`
--

DROP TABLE IF EXISTS `tenant_utilities`;
CREATE TABLE IF NOT EXISTS `tenant_utilities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `water` enum('Free','Paid','Included') DEFAULT 'Paid',
  `electricity` enum('Free','Paid','Included') DEFAULT 'Paid',
  `garbage` enum('Free','Paid','Included') DEFAULT 'Included',
  `security` enum('Free','Paid','Included') DEFAULT 'Included',
  `internet` enum('Free','Paid','Included') DEFAULT 'Paid',
  `parking` enum('Free','Paid','Included') DEFAULT 'Paid',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tenant_utilities`
--

INSERT INTO `tenant_utilities` (`id`, `tenant_id`, `water`, `electricity`, `garbage`, `security`, `internet`, `parking`) VALUES
(1, 2, 'Paid', 'Paid', 'Included', 'Free', 'Paid', 'Included');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'tenant',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `phone` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT '0.00' COMMENT 'Broker commission rate in %',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `created_at`, `phone`, `whatsapp`, `commission_rate`) VALUES
(1, 'Don ', 'don@gmail.com', '$2y$10$qUa7M6QruePVZ1llhkogHunWwGuaWF3I9pbaZhcsP2GyUu0jgi.4S', 'broker', '2026-02-23 09:43:13', NULL, NULL, 0.00),
(2, 'nawaguma vivian', 'nawagumavivian@gmail.com', '$2y$10$IG89oEI53O/KPnA47rSYK.wIMW31trHyTnnzMAAlmGO197J9AB77u', 'admin', '2026-03-07 11:58:26', NULL, NULL, 0.00),
(3, 'Nawaguma vivian', 'nawagumavivian1@gmail.com', '$2y$10$hXtS7FSPEu1JM/Eumo6/sOtM1ruDTzwtCPWbitaimGDUnGazktuWq', 'tenant', '2026-03-14 16:27:43', NULL, NULL, 0.00),
(4, 'vivian', 'vivian@gmail.com', '$2y$10$yXWVxEJDXwJtl4tJI./sgO/LT1ajBA6c7RYTwSrQnsV5hKRF.Xsum', 'tenant', '2026-03-14 16:34:52', NULL, NULL, 0.00),
(5, 'rovin', 'rovin@gmail.com', '$2y$10$idYExacUfhmQliKgPlCu0uezH3VQV1.ZqLfXRHY2X1CEXhv.s5Swi', 'tenant', '2026-03-14 19:14:21', NULL, NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

DROP TABLE IF EXISTS `visitors`;
CREATE TABLE IF NOT EXISTS `visitors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `property_id` int DEFAULT NULL,
  `visitor_name` varchar(150) NOT NULL,
  `relationship` varchar(100) DEFAULT NULL,
  `visitor_phone` varchar(50) DEFAULT NULL,
  `visitor_id` varchar(100) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `purpose` varchar(200) DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'approved',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_visit_date` (`visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
