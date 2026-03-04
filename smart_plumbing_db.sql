-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2026 at 01:01 PM
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
-- Database: `smart_plumbing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` enum('0','1') DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 'elphas@gmail.com', '685307d38ff9561683494c5d5f2db48d', '2026-03-03 17:04:36', '0', '2026-03-03 15:49:36'),
(2, 'elphas@gmail.com', 'cc1740dd549af38b1f06c9484d56dbaf', '2026-03-03 17:14:14', '0', '2026-03-03 15:59:14'),
(3, 'elphas@gmail.com', '2ab9149e57b420025bc3f501fbc68355', '2026-03-03 17:14:18', '1', '2026-03-03 15:59:18'),
(4, 'shadrack@gmail.com', 'd13344ac4f4dac97dbb9b6f5cf2dddec', '2026-03-03 18:50:56', '1', '2026-03-03 17:35:56'),
(5, 'cyrus@gmail.com', '8d4f8aa70f8e517fc73f34e5f256bd1c', '2026-03-04 12:21:03', '1', '2026-03-04 11:06:03');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `request_id`, `amount`, `payment_method`, `payment_status`, `payment_date`) VALUES
(6, 15, 1000.00, 'cash', 'paid', '2026-03-04 09:47:21'),
(7, 16, 1000.00, 'mpesa', 'paid', '2026-03-04 10:01:11'),
(8, 17, 1000.00, 'cash', 'paid', '2026-03-04 10:30:59'),
(9, 18, 2000.00, 'mpesa', 'paid', '2026-03-04 11:09:20');

-- --------------------------------------------------------

--
-- Table structure for table `plumbers`
--

CREATE TABLE `plumbers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `education_level` varchar(100) DEFAULT NULL,
  `availability_status` enum('available','busy') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plumbers`
--

INSERT INTO `plumbers` (`id`, `user_id`, `level_id`, `experience_years`, `education_level`, `availability_status`) VALUES
(5, 12, 2, 5, 'College', 'busy'),
(6, 15, 1, 4, 'University', 'available'),
(7, 16, 2, 10, 'College', 'available'),
(8, 17, 3, 4, 'College', 'available'),
(9, 18, 1, 4, 'University', 'available'),
(10, 19, 2, 1, 'College', 'busy'),
(11, 20, 3, 4, 'University', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `plumber_levels`
--

CREATE TABLE `plumber_levels` (
  `id` int(11) NOT NULL,
  `level_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plumber_levels`
--

INSERT INTO `plumber_levels` (`id`, `level_name`, `description`, `base_price`) VALUES
(1, 'Basic', 'Handles minor plumbing issues', 500.00),
(2, 'Intermediate', 'Experienced plumber for common problems', 1000.00),
(3, 'Expert', 'Highly skilled professional plumber', 2000.00);

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `plumber_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `request_id`, `plumber_id`, `customer_id`, `rating`, `comment`, `created_at`) VALUES
(1, 17, 7, 22, 5, 'So awesome', '2026-03-04 10:31:58'),
(2, 18, 11, 23, 4, 'Job done successfully', '2026-03-04 11:10:48');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `plumber_id` int(11) DEFAULT NULL,
  `issue_description` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('pending','accepted','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `level_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `customer_id`, `plumber_id`, `issue_description`, `location`, `status`, `created_at`, `level_id`) VALUES
(15, 21, 10, 'Pipe leakage', 'Eldoret town', '', '2026-03-04 09:45:45', 2),
(16, 21, 7, 'Full pipe installl', 'Kisumu City', 'completed', '2026-03-04 09:59:04', 2),
(17, 22, 7, 'pipe replacement', 'khasoko', 'completed', '2026-03-04 10:29:45', 2),
(18, 23, 11, 'Pipe leakage', 'Kibabiii', 'completed', '2026-03-04 11:07:23', 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','plumber','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `created_at`) VALUES
(12, 'Cosmas Nyongesa', 'cosmas@gmail.ccom', '0112432323', '$2y$10$/ex6GjOZa4zklJvcDJ8.X.MUk0lUjTyrgkBIFTGVilBGEAPZ//rN.', 'plumber', '2026-03-04 07:29:21'),
(14, 'lawrence Simiyu', 'lawrence@gmail.com', '0712345678', '$2y$10$.AbOD7EvDo12wQhKtOY5vuY6AFM5BqNVfqofTiP1DxU02bxV.DoI6', 'admin', '2026-03-04 09:35:48'),
(15, 'Dickson kotut', 'dickson@gmail.com', '0111111134', '$2y$10$wUBcV720/bdxwAnvAwnokOGMC6F7B1xUoGcHhPwC//d/xcIL6.7LK', 'plumber', '2026-03-04 09:38:09'),
(16, 'Nelson Sitati', 'nelson@gmail.com', '0756789123', '$2y$10$i3qpsQIKoLbsjtwHpkwjFu4Z7auWw4Z7BEIsG0t24CtzPNgCDvjJm', 'plumber', '2026-03-04 09:38:59'),
(17, 'Steven wafula', 'steven@gmail.com', '0711145434', '$2y$10$PlzoCTH7MyiAl6uNjmH2m.Z8Z5Q5Qv310DsXLntaPYETE1hr4balq', 'plumber', '2026-03-04 09:39:39'),
(18, 'shadrack ouma', 'shadrack@gmail.com', '0748820212', '$2y$10$Owj2qEPV2BV6m6emelK6De8MfFzCJT4l9sMRKE7xNeewy7MmhHquS', 'plumber', '2026-03-04 09:40:23'),
(19, 'kelvin sifuna', 'kelvin@gmail.com', '0756789123', '$2y$10$qQC21tb9P8e8dBZw6bQgo.q5t69AWhkkSrDpYuC3xdt75sWRKuSjq', 'plumber', '2026-03-04 09:41:12'),
(20, 'Moses oduor', 'mosses@gmail.com', '0756789123', '$2y$10$dXMCa7.WNbK4KDGzMKDoge/U3D/yRH0p9Iylv2tFRzUIhU.rco/Xe', 'plumber', '2026-03-04 09:42:04'),
(21, 'Kennedy wandera', 'kennedy@gmail.com', '112432345', '$2y$10$hDkPWUQLMRUZSLDXTv/Kn.3JFtJxZCrCF5AnD28qTJLh2MmdniNPW', 'customer', '2026-03-04 09:44:56'),
(22, 'lucy wesonga', 'lucy@gmail.com', '0712345678', '$2y$10$KH9UJrZXvQ/ozCtXK6gBg.puXy6EmW.36gHfmJ2UxjvnPAkDQULX6', 'customer', '2026-03-04 10:28:39'),
(23, 'Cyrus shikuku', 'cyrus@gmail.com', '07853456754', '$2y$10$YAA.NYSdOUkui/huOoWkaeUFv.hCgBEWRNcMArTYK7.kBw.ehI5sW', 'customer', '2026-03-04 11:05:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `plumbers`
--
ALTER TABLE `plumbers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `level_id` (`level_id`);

--
-- Indexes for table `plumber_levels`
--
ALTER TABLE `plumber_levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `plumber_id` (`plumber_id`);

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
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `plumbers`
--
ALTER TABLE `plumbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `plumber_levels`
--
ALTER TABLE `plumber_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plumbers`
--
ALTER TABLE `plumbers`
  ADD CONSTRAINT `plumbers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plumbers_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `plumber_levels` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`plumber_id`) REFERENCES `plumbers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
