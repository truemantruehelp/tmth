-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 21, 2026 at 11:18 AM
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
-- Database: `tmth_up`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `fund_purpose` varchar(50) DEFAULT NULL,
  `story` text DEFAULT NULL,
  `urgent` tinyint(1) DEFAULT 0,
  `id_card` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `proof` varchar(255) DEFAULT NULL,
  `ref_name` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `name`, `age`, `address`, `mobile`, `fund_purpose`, `story`, `urgent`, `id_card`, `photo`, `proof`, `ref_name`, `status`, `notes`, `application_date`, `updated_at`) VALUES
(4, 'Aklima', 70, 'Habiganj', '01712345678', 'food', 'this old woman has no one to support for her basic food.', 1, '1765227116_01712345678_id_card.jpg', '1765227116_01712345678_photo.jpg', NULL, NULL, 'approved', '', '2025-12-08 20:51:56', '2025-12-09 19:10:44'),
(6, 'Istiak', 45, 'Dhaka', '01751234567', 'medical', 'need urgent help', 1, NULL, NULL, NULL, 'John', 'pending', NULL, '2025-12-09 18:07:12', '2026-02-21 09:25:24');

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `goal_amount` decimal(15,2) NOT NULL,
  `raised_amount` decimal(15,2) DEFAULT 0.00,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','completed') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `title`, `description`, `short_description`, `goal_amount`, `raised_amount`, `category_id`, `image`, `start_date`, `end_date`, `status`, `featured`, `created_by`, `created_at`, `updated_at`) VALUES
(8, 'House Shelter Renovation', 'Renovated and expanded the local animal shelter facilities', '', 8000.00, 8200.00, 5, 'assets/images/campaigns/campaign_1761204195_68f9d7e34349c.jpg', '0000-00-00', '0000-00-00', 'completed', 0, 1, '2025-10-20 21:30:40', '2025-10-23 07:23:15'),
(11, 'Fruits', 'Fruits for Madrasha ophans', 'Fruits iftar distributions', 20000.00, 0.00, 5, 'assets/images/campaigns/690def32c3954_1762520882.jpg', '2025-11-14', '2025-11-19', 'active', 0, 1, '2025-11-07 09:58:09', '2025-11-07 13:08:02'),
(12, 'Flood Refief', 'Aid needed heavily flood effected people', 'Emergency Flood Relief', 50000.00, 0.00, 17, 'assets/images/campaigns/6914d3b9dfba7_1762972601.jpg', '2025-11-14', '2025-12-14', 'active', 0, 1, '2025-11-12 18:36:41', '2025-11-12 18:36:41'),
(13, 'widow support', 'urgent widow support, as she can\'t afford basic', 'urgent widow support', 5000.00, 0.00, 24, 'assets/images/campaigns/691c8bb2f3bda_1763478450.jpg', '2025-11-11', '2025-11-30', 'active', 1, 1, '2025-11-18 15:07:31', '2025-11-18 15:07:31');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`, `status`, `created_at`) VALUES
(3, 'Education', 'Campaigns focused on education and learning', NULL, 'active', '2025-10-17 19:22:51'),
(4, 'Healthcare', 'Medical and healthcare related campaigns', NULL, 'active', '2025-10-17 19:22:51'),
(5, 'Orphan Welfare', 'Campaigns for orphan protection and care', NULL, 'active', '2025-10-17 19:22:51'),
(6, 'Environmental', 'Environmental conservation and protection', NULL, 'active', '2025-10-17 19:22:51'),
(8, 'Medical Care', 'Direct medical treatment and care', NULL, 'active', '2025-10-20 20:08:37'),
(12, 'Hunger Relief', 'Food security and hunger prevention', NULL, 'active', '2025-10-20 20:08:37'),
(16, 'empoyment', 'empyment', NULL, 'active', '2025-11-07 09:36:54'),
(17, 'Flood', 'flood', NULL, 'active', '2025-11-12 18:30:56'),
(19, 'Healthcare', 'Healthcare', NULL, 'active', '2025-11-16 18:19:36'),
(20, 'Orphan welfare', 'For orphans', NULL, 'active', '2025-11-18 08:29:55'),
(23, 'Emergency Relief', 'Emergency need', NULL, 'active', '2025-11-18 15:01:47'),
(24, 'Widow support', 'urgent widow support', 23, 'active', '2025-11-18 15:04:36');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `donor_name` varchar(100) NOT NULL,
  `donor_email` varchar(100) DEFAULT NULL,
  `donor_phone` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('stripe','paypal','bank_transfer','cash','bikash') NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `is_anonymous` tinyint(1) DEFAULT 0,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `is_guest` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `status`, `is_guest`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@tmth.store', '$2y$10$ery0q3hTLETLPUm/CW08V.PBXu1gGEF7WLeVlcmO.Ql83bwufvyg6', NULL, NULL, 'admin', 'active', 0, '2025-10-17 19:22:51', '2025-11-07 13:04:49'),
(6, 'thomal', NULL, '$2y$10$/SH5oBwvrMMIpC/fk1AjUOauEC0jyFtG6M46Qrczk2Au6dX67215W', '0987654321', NULL, 'user', 'active', 1, '2025-11-07 09:19:50', '2025-11-07 09:19:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_mobile` (`mobile`),
  ADD KEY `idx_created` (`application_date`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `donor_phone` (`donor_phone`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `is_guest` (`is_guest`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `campaigns_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
