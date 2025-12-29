-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 02:18 AM
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
-- Database: `crypto_exchange`
--

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(60) NOT NULL,
  `decimals` int(11) NOT NULL DEFAULT 8
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `code`, `name`, `decimals`) VALUES
(1, 'BTC', 'Bitcoin', 8),
(2, 'ETH', 'Ethereum', 8),
(3, 'USDT', 'Tether USD', 6),
(6, 'XRP', 'Ripple', 8),
(7, 'SOL', 'Solana', 8),
(8, 'BNB', 'Binance Coin', 10),
(9, 'TRUMP', 'Trump', 12);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `base_currency_id` int(11) NOT NULL,
  `quote_currency_id` int(11) NOT NULL,
  `side` enum('BUY','SELL') NOT NULL,
  `price` decimal(36,18) NOT NULL,
  `amount` decimal(36,18) NOT NULL,
  `status` enum('OPEN','FILLED','CANCELLED') NOT NULL DEFAULT 'OPEN',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `base_currency_id`, `quote_currency_id`, `side`, `price`, `amount`, `status`, `created_at`) VALUES
(2, 1, 1, 3, 'BUY', 50000.000000000000000000, 0.010000000000000000, 'OPEN', '2025-11-17 00:30:33'),
(3, 1, 2, 2, 'BUY', 56.000000000000000000, 1.000000000000000000, 'OPEN', '2025-12-13 01:23:58'),
(4, 8, 1, 3, 'SELL', 1.000000000000000000, 0.500000000000000000, 'FILLED', '2025-12-14 23:21:16'),
(5, 8, 1, 3, 'SELL', 1.000000000000000000, 1.000000000000000000, 'FILLED', '2025-12-14 23:22:36'),
(6, 8, 1, 3, 'SELL', 50000.000000000000000000, 1.000000000000000000, 'FILLED', '2025-12-14 23:23:22'),
(7, 8, 3, 1, 'SELL', 50000.000000000000000000, 50000.000000000000000000, 'FILLED', '2025-12-14 23:24:11'),
(8, 8, 1, 3, 'SELL', 1.000000000000000000, 2500000.000000000000000000, 'FILLED', '2025-12-14 23:24:40'),
(9, 8, 1, 3, 'SELL', 20000.000000000000000000, 1.000000000000000000, 'FILLED', '2025-12-15 01:54:06'),
(10, 8, 1, 3, 'SELL', 10000.000000000000000000, 2.000000000000000000, 'FILLED', '2025-12-15 02:23:24'),
(11, 1, 1, 1, 'BUY', 100.000000000000000000, 1000.000000000000000000, 'OPEN', '2025-12-28 23:43:54');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `type` enum('DEPOSIT','WITHDRAW','FILL_BUY','FILL_SELL') NOT NULL,
  `amount` decimal(36,18) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `wallet_id`, `type`, `amount`, `created_at`) VALUES
(4, 4, '', 0.500000000000000000, '2025-12-14 23:21:16'),
(5, 5, 'DEPOSIT', 0.500000000000000000, '2025-12-14 23:21:16'),
(6, 4, '', 1.000000000000000000, '2025-12-14 23:22:36'),
(7, 5, 'DEPOSIT', 1.000000000000000000, '2025-12-14 23:22:36'),
(8, 4, '', 1.000000000000000000, '2025-12-14 23:23:22'),
(9, 5, 'DEPOSIT', 50000.000000000000000000, '2025-12-14 23:23:22'),
(10, 5, '', 50000.000000000000000000, '2025-12-14 23:24:11'),
(11, 4, 'DEPOSIT', 2500000000.000000000000000000, '2025-12-14 23:24:11'),
(12, 4, '', 2500000.000000000000000000, '2025-12-14 23:24:40'),
(13, 5, 'DEPOSIT', 2500000.000000000000000000, '2025-12-14 23:24:40'),
(14, 4, '', 1.000000000000000000, '2025-12-15 01:54:06'),
(15, 5, 'DEPOSIT', 20000.000000000000000000, '2025-12-15 01:54:06'),
(16, 4, '', 2.000000000000000000, '2025-12-15 02:23:24'),
(17, 5, 'DEPOSIT', 20000.000000000000000000, '2025-12-15 02:23:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `is_admin`) VALUES
(1, 'updated name', 'test7676@gmail.com', '', '2025-11-16 21:25:27', 0),
(2, 'user2', 'user2@test.com', 'dario123', '2025-11-16 21:32:22', 0),
(4, 'Laptop User', 'laptop@example.com', 'secret123', '2025-12-11 00:13:03', 0),
(5, 'Admin', 'admin@example.com', 'admin123', '2025-12-11 18:54:50', 1),
(8, 'RealAdmin', 'realadmin@example.com', '$2y$10$3mSXkkR4yT7nFasYYA3vMOSzQWDynzu67maGVZ4P/0g6myo92tdzO', '2025-12-11 19:35:09', 1),
(9, 'Dario Simanic', 'dariosimanic@gmail.com', '$2y$10$gRv3T0JWNgsoGbSmBsgeSuM87z5hqdyLf.uHsuARkGOC4wCoqEsF.', '2025-12-14 20:49:30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `balance` decimal(36,18) NOT NULL DEFAULT 0.000000000000000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `currency_id`, `balance`) VALUES
(4, 8, 1, 98.000000000000000000),
(5, 8, 3, 21000.000000000000000000),
(6, 1, 2, 2000.000000000000000000),
(7, 1, 1, 100.000000000000000000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_user` (`user_id`),
  ADD KEY `fk_order_base` (`base_currency_id`),
  ADD KEY `fk_order_quote` (`quote_currency_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tx_wallet` (`wallet_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_currency` (`user_id`,`currency_id`),
  ADD KEY `fk_wallet_currency` (`currency_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_base` FOREIGN KEY (`base_currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `fk_order_quote` FOREIGN KEY (`quote_currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_tx_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_wallet_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `fk_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
