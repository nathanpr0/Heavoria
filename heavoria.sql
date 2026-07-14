-- Active: 1781351314003@@127.0.0.1@3306@heavoria
-- Active: 1781351314003@@127.0.0.1@3306@heavoria03@@127.0.0.1@3306@heavoria03@@127.0.0.1@3306@heavoria
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 12, 2026 at 11:35 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

DROP DATABASE heavoria;

CREATE DATABASE heavoria;

USE heavoria;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `heavoria`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('sushi','cake','drink') NOT NULL,
  `price` int NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `category`, `price`, `description`, `image_url`, `created_at`) VALUES
('menu-1783848908-295', 'MBG', 'sushi', 15000, 'MBG (Makanan Bergizi Gratis)', 'assets/prabowo.jpg', '2026-07-12 09:35:08'),
('nigiri', 'Nigiri', 'sushi', 6000, 'Japanese menu dengan nasi sushi dan topping lembut khas Heavoria.', 'assets/nigiri.jpeg', '2026-07-09 11:10:06'),
('sushi-roll', 'Sushi Roll', 'sushi', 5000, 'Sushi roll praktis dengan rasa gurih dan porsi ringan.', 'assets/sushiroll.jpeg', '2026-07-09 11:10:06'),
('towelcake-grape', 'Towelcake Grape', 'cake', 10000, 'Towel cake lembut rasa grape dengan aroma buah yang ringan.', 'assets/towelcake_grape.jpeg', '2026-07-09 11:10:06'),
('towelcake-mango', 'Towelcake Mango', 'cake', 10000, 'Towel cake lembut rasa mango yang segar dan creamy.', 'assets/towelcake_mango.jpeg', '2026-07-09 11:10:06'),
('towelcake-strawberry', 'Towelcake Strawberry', 'cake', 10000, 'Towel cake lembut rasa strawberry dengan tampilan manis.', 'assets/towelcake_strawberry.jpeg', '2026-07-09 11:10:06');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `table_number` int NOT NULL,
  `subtotal` int NOT NULL,
  `tax` int NOT NULL,
  `service_charge` int NOT NULL,
  `delivery_fee` int NOT NULL DEFAULT '0',
  `distance_km` decimal(6,2) NOT NULL DEFAULT '0.00',
  `fulfillment_type` varchar(20) NOT NULL DEFAULT 'pickup',
  `address_note` text,
  `pickup_date` date DEFAULT NULL,
  `rejection_reason` text,
  `total` int NOT NULL,
  `payment_method` enum('QRIS','Kartu','Tunai') NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'Menunggu Verifikasi',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `username`, `table_number`, `subtotal`, `tax`, `service_charge`, `delivery_fee`, `distance_km`, `fulfillment_type`, `address_note`, `pickup_date`, `rejection_reason`, `total`, `payment_method`, `status`, `created_at`) VALUES
('ORD-67621873', 'Dasvidaniya', 1, 5000, 500, 250, 0, 0.00, 'pickup', NULL, NULL, NULL, 5750, 'QRIS', 'Selesai', '2026-06-17 06:03:38'),
('ORD-67659439', 'Dasvidaniya', 4, 285000, 28500, 14250, 0, 0.00, 'pickup', NULL, NULL, NULL, 327750, 'Tunai', 'Selesai', '2026-06-17 06:09:54'),
('ORD-79452652', 'Daniel', 1, 54000, 5400, 2700, 0, 0.00, 'pickup', NULL, '2026-07-13', NULL, 62100, 'QRIS', 'Selesai', '2026-06-30 04:42:06'),
('ORD-84645686', 'admin', 0, 20000, 0, 0, 0, 0.00, 'pickup', '', '2026-07-13', NULL, 20000, 'Tunai', 'Selesai', '2026-07-12 08:54:16'),
('ORD-84699155', 'Daniel', 0, 6000, 0, 0, 0, 0.00, 'pickup', '', NULL, 'gak mutu', 6000, 'QRIS', 'Ditolak', '2026-07-12 09:03:11'),
('ORD-84728345', 'Daniel', 0, 30000, 0, 0, 4000, 1.00, 'delivery', 'gitulah', '2026-07-13', NULL, 34000, 'Tunai', 'Selesai', '2026-07-12 09:08:03'),
('ORD-84773472', 'Daniel', 0, 5000, 0, 0, 0, 0.00, 'pickup', '', '2026-07-13', NULL, 5000, 'QRIS', 'Siap Diambil', '2026-07-12 09:15:34');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `menu_id` varchar(50) NOT NULL,
  `qty` int NOT NULL,
  `notes` text,
  `price_at_order` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `qty`, `notes`, `price_at_order`) VALUES
(1, 'ORD-67621873', 'sushi-roll', 1, '', 5000),
(2, 'ORD-67659439', 'nigiri', 45, '', 6000),
(3, 'ORD-67659439', 'sushi-roll', 3, '', 5000),
(4, 'ORD-79452652', 'nigiri', 4, '', 6000),
(5, 'ORD-79452652', 'sushi-roll', 6, '', 5000),
(6, 'ORD-84645686', 'sushi-roll', 4, '', 5000),
(7, 'ORD-84699155', 'nigiri', 1, '', 6000),
(8, 'ORD-84728345', 'towelcake-grape', 2, '', 10000),
(9, 'ORD-84728345', 'towelcake-strawberry', 1, '', 10000),
(10, 'ORD-84773472', 'sushi-roll', 1, '', 5000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(160) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password`, `role`, `created_at`, `last_seen`) VALUES
(1, 'admin', NULL, '08123456789', 'admin', 'admin', '2026-06-17 05:29:18', '2026-07-12 16:34:22'),
(2, 'customer', NULL, '08987654321', 'customer', 'user', '2026-06-17 05:29:18', NULL),
(3, 'Dasvidaniya', NULL, '081292340039', '$2y$10$JBQK3OGfIeLoBRrZER41curuM2jAzX5WlzoZ4BE0tsbtG0Hk6x.Sy', 'user', '2026-06-17 06:00:12', NULL),
(4, 'Daniel', NULL, '085714729495', '$2y$10$XdBJezkSwYpezK3mL8pgbuPlW58Zosj4P.elh0VPqZzAbrRMq4Gti', 'user', '2026-06-30 03:51:47', '2026-07-12 16:21:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

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
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1307;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
