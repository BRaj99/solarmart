-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jul 02, 2026 at 03:57 PM
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
-- Database: `solarmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `invoice_sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash on Delivery',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','Processing','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `delivery_address`, `payment_method`, `subtotal`, `shipping`, `tax`, `grand_total`, `status`, `created_at`) VALUES
(1, 'ORD-20260520164835-503', 3, 'biraj basaula', 'birajbasaula110@gmail.com', '9874102111', 'kuti galli house no.12', 'Cash on Delivery', 275000.00, 0.00, 35750.00, 310750.00, 'Delivered', '2026-05-20 14:48:35'),
(2, 'ORD-20260525190651-740', 4, 'himal', 'himal123@gmail.com', '123456789', 'house number 11', 'Cash on Delivery', 555000.00, 0.00, 72150.00, 627150.00, 'Pending', '2026-05-25 17:06:51'),
(3, 'ORD-20260526080459-685', 3, 'biraj basaula', 'birajbasaula110@gmail.com', '9874102111', 'kuti galli house no.12', 'Cash on Delivery', 16500.00, 1500.00, 2145.00, 20145.00, 'Processing', '2026-05-26 06:04:59'),
(4, 'ORD-20260526083219-335', 3, 'biraj basaula', 'birajbasaula110@gmail.com', '9874102111', 'kuti galli house no.12', 'Cash on Delivery', 16500.00, 1500.00, 2145.00, 20145.00, 'Processing', '2026-05-26 06:32:19'),
(5, 'ORD-20260526083249-146', 3, 'biraj basaula', 'birajbasaula110@gmail.com', '9874102111', 'kuti galli house no.12', 'Cash on Delivery', 275000.00, 0.00, 35750.00, 310750.00, 'Delivered', '2026-05-26 06:32:49'),
(6, 'ORD-20260526083930-716', 3, 'biraj basaula', 'birajbasaula110@gmail.com', '9874102111', 'kuti galli house no.12', 'Cash on Delivery', 28500.00, 1500.00, 3705.00, 33705.00, 'Pending', '2026-05-26 06:39:30'),
(7, 'ORD-20260526084019-564', 3, 'biraj basaula', 'birajbasaula110@gmail.com', '9874102111', 'kuti galli house no.12', 'Cash on Delivery', 28500.00, 1500.00, 3705.00, 33705.00, 'Pending', '2026-05-26 06:40:19'),
(8, 'ORD-20260526084039-312', 3, 'biraj basaula', 'birajbasaula110@gmail.com', '9874102111', 'kuti galli house no.12', 'Cash on Delivery', 24500.00, 1500.00, 3185.00, 29185.00, 'Pending', '2026-05-26 06:40:39'),
(9, 'ORD-20260629173843-847', 3, 'biraj basaula', 'birajbasaula110@gmail.com', '9874102111', 'kuti galli house no.12', 'Cash on Delivery', 283500.00, 0.00, 36855.00, 320355.00, 'Delivered', '2026-06-29 15:38:43');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `line_total`) VALUES
(1, 1, 7, 'Home Solar Kit 3kW', 275000.00, 1, 275000.00),
(2, 2, 3, 'Lithium Battery 48V 100Ah', 185000.00, 3, 555000.00),
(3, 3, 5, 'Solar Charge Controller MPPT', 16500.00, 1, 16500.00),
(4, 4, 5, 'Solar Charge Controller MPPT', 16500.00, 1, 16500.00),
(5, 5, 7, 'Home Solar Kit 3kW', 275000.00, 1, 275000.00),
(6, 6, 1, 'Mono Solar Panel 550W', 28500.00, 1, 28500.00),
(7, 7, 1, 'Mono Solar Panel 550W', 28500.00, 1, 28500.00),
(8, 8, 4, 'Solar Street Light 120W', 24500.00, 1, 24500.00),
(9, 9, 8, 'Solar Cable 6mm Bundle', 8500.00, 1, 8500.00),
(10, 9, 7, 'Home Solar Kit 3kW', 275000.00, 1, 275000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `category` varchar(80) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `sku` varchar(80) NOT NULL,
  `image` varchar(255) DEFAULT 'images/solar-placeholder.svg',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `low_stock_limit` int(11) NOT NULL DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `brand`, `category`, `price`, `stock`, `sku`, `image`, `description`, `is_active`, `low_stock_limit`, `created_at`, `updated_at`) VALUES
(1, 'Mono Solar Panel 550W', 'SunPeak', 'Panels', 28500.00, 16, 'SP-550', 'images/mono-solar-panel.svg', 'High efficiency mono panel for home and commercial rooftop systems.', 1, 5, '2026-05-18 11:31:01', '2026-05-26 06:40:19'),
(2, 'Hybrid Solar Inverter 5kW', 'VoltEdge', 'Inverters', 125000.00, 7, 'INV-5KW', 'images/hybrid-solar-inverter.svg', 'Smart hybrid inverter with battery support and LCD monitoring.', 1, 5, '2026-05-18 11:31:01', '2026-05-18 11:31:01'),
(3, 'Lithium Battery 48V 100Ah', 'PowerCell', 'Batteries', 185000.00, 2, 'BAT-48V100', 'images/lithium-battery.svg', 'Long-life lithium storage for backup and off-grid power.', 1, 5, '2026-05-18 11:31:01', '2026-05-25 17:06:51'),
(4, 'Solar Street Light 120W', 'BrightWay', 'Lights', 24500.00, 23, 'LIGHT-120W', 'images/solar-street-light.svg', 'Automatic dusk-to-dawn outdoor lighting with motion sensor.', 1, 5, '2026-05-18 11:31:01', '2026-05-26 06:40:39'),
(5, 'Solar Charge Controller MPPT', 'ChargePro', 'Accessories', 16500.00, 12, 'MPPT-CTRL', 'images/solar-charge-controller.svg', 'MPPT controller to improve charging efficiency and battery safety.', 1, 5, '2026-05-18 11:31:01', '2026-05-26 06:32:19'),
(6, 'Solar Water Pump Kit', 'AquaSun', 'Kits', 92000.00, 6, 'PUMP-KIT', 'images/solar-water-pump-kit.svg', 'Reliable irrigation and water supply kit powered directly by sunlight.', 1, 5, '2026-05-18 11:31:01', '2026-05-18 11:31:01'),
(7, 'Home Solar Kit 3kW', 'EcoHome', 'Kits', 275000.00, 5, 'HOME-3KW', 'images/home-solar-kit.svg', 'Complete rooftop package for small family homes.', 1, 5, '2026-05-18 11:31:01', '2026-06-29 15:38:43'),
(8, 'Solar Cable 6mm Bundle', 'SafeWire', 'Accessories', 8500.00, 39, 'CABLE-6MM', 'images/solar-cable-bundle.svg', 'UV-resistant solar cable bundle for safer installation.', 1, 5, '2026-05-18 11:31:01', '2026-06-29 15:38:43');

-- --------------------------------------------------------

--
-- Table structure for table `stock_logs`
--

CREATE TABLE `stock_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_stock` int(11) NOT NULL,
  `stock_date` date NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_logs`
--

INSERT INTO `stock_logs` (`id`, `product_id`, `added_stock`, `stock_date`, `note`, `created_at`) VALUES
(1, 7, 4, '2026-04-17', '507', '2026-05-21 15:32:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'customer',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`, `reset_token`, `reset_expires`, `phone`, `age`, `gender`, `location`, `address`, `created_at`) VALUES
(3, 'biraj basaula', 'birajbasaula110@gmail.com', '$2y$10$Q5dpBMeloe94FmcqvTQrXurldvryuyH7QLnoQRNIakIVUYPvcOHfK', 'customer', NULL, NULL, '9874102111', 20, 'Male', 'chabahil', 'kuti galli house no.12', current_timestamp()),
(4, 'himal', 'himal123@gmail.com', '$2y$10$5Xahwp25NVhBWFMLxny.gOSJ.aSbbX.epsCzRFUA876uVj4pYj55e', 'customer', NULL, NULL, '123456789', 20, 'Male', 'kathmandu', 'house number 11', current_timestamp());

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `fk_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order` (`order_id`),
  ADD KEY `fk_order_items_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_sku` (`sku`),
  ADD KEY `idx_products_category` (`category`),
  ADD KEY `idx_products_active` (`is_active`);

--
-- Indexes for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `stock_date` (`stock_date`),
  ADD KEY `product_id_2` (`product_id`),
  ADD KEY `stock_date_2` (`stock_date`),
  ADD KEY `product_id_3` (`product_id`),
  ADD KEY `stock_date_3` (`stock_date`),
  ADD KEY `product_id_4` (`product_id`),
  ADD KEY `stock_date_4` (`stock_date`);

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
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- OTP verification table for registration and password reset
CREATE TABLE IF NOT EXISTS `otp_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `purpose` varchar(30) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_otp_email_purpose` (`email`,`purpose`),
  KEY `idx_otp_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
