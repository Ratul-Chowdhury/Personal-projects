-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 12, 2026 at 08:15 AM
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
-- Database: `hotel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `room_number` varchar(10) DEFAULT NULL,
  `room_type` varchar(50) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Verified',
  `guests` int(11) DEFAULT NULL,
  `adults` int(11) DEFAULT NULL,
  `children` int(11) DEFAULT NULL,
  `check_in` date DEFAULT NULL,
  `check_out` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `room_id`, `room_number`, `room_type`, `amount`, `method`, `status`, `guests`, `adults`, `children`, `check_in`, `check_out`, `created_at`) VALUES
(2, 7, 3, '201', 'Deluxe', 5500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-12', '2026-09-14', '2026-09-09 17:25:07'),
(3, 9, 3, '201', 'Deluxe', 5500, 'Bkash', 'Verified', 2, 1, 1, '2026-09-18', '2026-09-21', '2026-09-09 18:19:11'),
(4, 10, 2, '102', 'Standard', 3500, 'Bkash', 'Verified', 2, 1, 1, '2026-09-18', '2026-09-19', '2026-09-09 19:10:19'),
(5, 11, 1, '101', 'Standard', 3500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-17', '2026-09-22', '2026-09-09 19:12:32'),
(6, 12, 1, '101', 'Standard', 3500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-12', '2026-09-14', '2026-09-09 19:26:04'),
(7, 13, 2, '102', 'Standard', 3500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-24', '2026-09-29', '2026-09-09 21:02:52'),
(8, 14, 3, '201', 'Deluxe', 5500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-24', '2026-09-30', '2026-09-09 21:30:11'),
(9, 15, 1, '101', 'Standard', 3500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-24', '2026-09-22', '2026-09-09 22:29:54'),
(10, 14, 3, '201', 'Deluxe', 5500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-12', '2026-09-18', '2026-09-09 23:20:42'),
(11, 15, 1, '101', 'Standard', 3500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-12', '2026-09-18', '2026-09-09 23:24:31'),
(12, 12, 4, '202', 'Deluxe', 5500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-18', '2026-09-21', '2026-09-09 23:38:34'),
(13, 9, 1, '101', 'Standard', 3500, 'Bkash', 'Verified', 1, 1, 0, '2026-09-25', '2026-09-29', '2026-09-10 00:10:06');

-- --------------------------------------------------------

--
-- Table structure for table `damages`
--

CREATE TABLE `damages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_number` varchar(10) DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `user_id`, `type`, `text`, `status`, `created_at`) VALUES
(1, 2, 'transport', 'Airport pickup for Room 301 at 2026-09-19T02:48', 'Pending', '2026-09-03 20:52:11'),
(2, 2, 'transport', 'Airport pickup for Room 301 at 2026-09-19T02:48', 'Pending', '2026-09-03 20:52:17'),
(3, 2, 'transport', 'Airport pickup for Room 301 at 2026-09-19T03:11', 'Pending', '2026-09-03 21:11:04'),
(4, 2, 'wheelchair', 'Wheelchair for Room 301', 'Pending', '2026-09-09 19:27:41'),
(5, 2, 'wheelchair', 'Wheelchair for Room 301', 'Pending', '2026-09-09 20:38:34'),
(6, 3, 'food', 'Food (Burger) for Room 301', 'Pending', '2026-09-09 20:40:06'),
(7, 2, 'transport', '101at 10:00 am', 'Pending', '2026-09-09 22:32:03'),
(8, 3, 'laundry', '101at 10:00 am', 'Pending', '2026-09-09 22:33:09');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `booking_id`, `text`, `rating`, `created_at`) VALUES
(1, 10, 4, 'It is a nise room', 5, '2026-09-09 19:10:34'),
(2, 11, 5, 'it is nise room', 5, '2026-09-09 19:13:17'),
(3, 12, NULL, 'This room is like how i axpected', 5, '2026-09-09 19:26:36'),
(4, 14, NULL, 'It is a nise room', 5, '2026-09-09 21:30:34'),
(5, 15, 9, 'This a very comfortable room', 5, '2026-09-09 22:30:43'),
(6, 9, 13, 'it was a nise room', 5, '2026-09-10 00:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `number` varchar(10) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `guests` int(11) NOT NULL,
  `available` int(11) NOT NULL,
  `amenities` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `number`, `type`, `price`, `guests`, `available`, `amenities`, `description`, `image`) VALUES
(1, '101', 'Standard', 3500, 2, 5, 'Free WiFi, AC, Flat-screen TV', 'A comfortable standard room with modern amenities.', 'https://images.unsplash.com/photo-1631049307264?w=800'),
(2, '102', 'Standard', 3500, 2, 3, 'Free WiFi, AC, Flat-screen TV', 'A cozy standard room with all essential amenities.', 'https://images.unsplash.com/photo-1611892440504?w=800'),
(3, '201', 'Deluxe', 5500, 3, 4, 'Free WiFi, AC, Minibar, City View', 'A spacious deluxe room with premium furnishings.', 'https://images.unsplash.com/photo-1590490360182?w=800'),
(4, '202', 'Deluxe', 5500, 3, 2, 'Free WiFi, AC, Minibar, City View', 'An elegant deluxe room with luxury amenities.', 'https://images.unsplash.com/photo-1566665797739?w=800'),
(5, '301', 'Presidential Suite', 12000, 4, 1, 'Free WiFi, AC, Minibar, City View, Jacuzzi', 'Our most luxurious suite.', 'https://images.unsplash.com/photo-1582719478250?w=800');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','receptionist','customer','roomservice') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@hotel.com', 'admin', '$2y$10$H1wc4UENbyA0n5TwMUCyX.XHbEHXftruKL9Z3iP8yFnMeiBTYRQ7C', 'admin', '2026-09-03 15:22:31'),
(2, 'Receptionist', 'reception@hotel.com', 'receptionist', '$2y$10$itZOfoBiwn/D3fJUb22aB.rQSAy3tMxkqID8sfZnobvT44tokghOK', 'receptionist', '2026-09-03 15:22:31'),
(3, 'Room Service', 'roomservice@hotel.com', 'roomservice', '$2y$10$itZOfoBiwn/D3fJUb22aB.rQSAy3tMxkqID8sfZnobvT44tokghOK', 'roomservice', '2026-09-03 15:22:31'),
(5, 'Akash Abdullah', 'akash22@gmail.com', 'Akash Abdullah', '$2y$10$rsUO5ATb4WIL0UqqEkhsQ.RWW0c4bevRYk2YE49735cvUy4d5cYAu', 'customer', '2026-09-03 16:06:10'),
(6, 'David Smith', 'david@gmail.com', 'David Smith', '$2y$10$AY9owjb9iYrYsuSSWlZd0e2S8RGYh8/5/yLzwKDgytRhhdkVyLnDy', 'customer', '2026-09-03 16:19:46'),
(7, 'Tauhid Alom', 'tauhid@gmail.com', 'Tauhid', '$2y$10$fjv3h4BbrYB4yL1.fx9XF.dlxxz9wIo9CLYWLJwkIN0uF.uc3NHPu', 'customer', '2026-09-09 15:38:16'),
(8, 'Kamal Hossain', 'kamal@gmail.com', 'Kamal', '$2y$10$oWCJX9OyuOjtY9cZmhbu/OseYDIHSTkfpqyi0MPThUfmC6QENIpJG', 'customer', '2026-09-09 17:59:46'),
(9, 'Emon Chowdhury', 'emon@gmai.com', 'Emon', '$2y$10$5BW0a8eRpv4fnVBJZ/Z8EugpqbwdDDn4GW.bG5pQujR36uDithGEK', 'customer', '2026-09-09 18:18:33'),
(10, 'Ashfaq', 'ashfaq@gmail.com', 'Ashfaq', '$2y$10$Tn3cNB2bPY8w8TJp0Uv3AuoBPy3Ga0KLXlgdlsAIOWWf1A7YI7nne', 'customer', '2026-09-09 19:09:45'),
(11, 'Latif Hasan', 'latif@gmail.com', 'Latif', '$2y$10$1wz3o9S/690tYyKxktMCe.pmvFj4HFq5iHbqBFqxy8osve92KVt1i', 'customer', '2026-09-09 19:12:04'),
(12, 'Akbor Ali', 'akbor@gmail.com', 'Akbor', '$2y$10$O1zdhLE6QuqD660j5Kxhve7cMpLLrMiQZYgieeXI2nh1yeR552gCi', 'customer', '2026-09-09 19:25:35'),
(13, 'Badol Ahmed', 'badol@gmail.com', 'Badol', '$2y$10$Wf6wQikMI4wcgXqsy7PaVuHDOLmL2y/lxEX5m0RJcRbKXTqSkqdvK', 'customer', '2026-09-09 20:55:24'),
(14, 'Shofik Uddin', 'shofik@gmail.com', 'Shofik', '$2y$10$/0tBdjkvV2g/hPFXSyX49Og912s9rRD5b5cH3HPPyd0/Ji/nE6Jiy', 'customer', '2026-09-09 21:29:46'),
(15, 'Rahim Uddin', 'rahim@gmail.com', 'Rahim', '$2y$10$kGFX8OFo7XCc5zLbaPAnl.EsVuGV4R316L5dcu9yc2Zq/Q2orn/1u', 'customer', '2026-09-09 22:29:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `damages`
--
ALTER TABLE `damages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `damages`
--
ALTER TABLE `damages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
