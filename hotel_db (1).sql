-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2026 at 01:31 AM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `extra_charges` decimal(10,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `damages`
--

INSERT INTO `damages` (`id`, `user_id`, `room_number`, `item`, `description`, `status`, `created_at`, `extra_charges`, `reason`, `booking_id`) VALUES
(1, 6, '301', 'Lamp', '0', 'Resolved', '2026-09-16 19:40:10', 100.00, 'Product Damage - Room 301', 0),
(3, 6, '101', 'TV Remote', '0', 'Resolved', '2026-09-16 20:26:09', 50.00, 'Product Damage - Room 101', 7),
(4, 6, '102', 'TV', '0', 'Pending', '2026-09-16 20:42:50', NULL, NULL, 8),
(5, 6, '999', 'Test Item', '0', 'Pending', '2026-09-16 20:51:44', NULL, NULL, 0),
(6, 6, '202', 'Fridge', 'The fridge door is broken', 'Resolved', '2026-09-16 21:48:47', 200.00, 'Product Damage - Room 202', 9);

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

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `text`, `created_at`) VALUES
(1, 7, 2, 'The  washing mashine is making too much nois', '2026-09-16 15:02:36'),
(2, 7, 2, 'Need extra suport handling laundry', '2026-09-16 15:05:02'),
(3, 7, 6, 'Need help to  keep the dinings clean', '2026-09-16 15:30:31');

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
(2, 6, 'wheelchair', '101at 10:00 am', 'Completed', '2026-09-16 14:50:04'),
(3, 10, 'special', 'Extra Pillow', 'In Progress', '2026-09-16 14:53:46'),
(4, 6, 'transport', '101at 10:00 am', 'Pending', '2026-09-16 14:55:13'),
(5, 7, 'laundry', '101at 10:00 am', 'Pending', '2026-09-16 15:02:59'),
(6, 7, 'food', '101at 10:00 am Biriyani', 'Pending', '2026-09-16 15:29:30'),
(7, 6, 'service_instruction', 'Room 101  needs a bit cleaning', 'Pending', '2026-09-16 17:08:47');

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
(1, 8, 2, 'It is relly a nise room , just like my accepectation', 5, '2026-09-15 23:19:34'),
(2, 9, 3, 'It is a good room', 4, '2026-09-15 23:21:53');

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
(1, '101', 'Standard', 100, 2, 5, 'Wi-Fi, TV, AC, Private Bathroom', 'A comfortable standard room with a queen bed.', 'room-101.jpg'),
(2, '102', 'Standard', 100, 2, 3, 'Wi-Fi, TV, AC, Private Bathroom', 'A comfortable standard room with two twin beds.', 'room-102.jpg'),
(3, '201', 'Deluxe', 150, 3, 4, 'Wi-Fi, TV, AC, Mini Bar, Balcony', 'Spacious deluxe room with a king bed and city view.', 'room-201.jpg'),
(4, '202', 'Deluxe', 150, 3, 2, 'Wi-Fi, TV, AC, Mini Bar, Balcony', 'Spacious deluxe room with a king bed and ocean view.', 'room-202.jpg'),
(5, '301', 'Presidential Suite', 300, 4, 1, 'Wi-Fi, TV, AC, Mini Bar, Balcony, Jacuzzi, Living Room', 'The ultimate luxury experience with panoramic views.', 'room-301.jpg');

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
(5, 'Administrator', 'admin@grandhotel.com', 'admin', '$2y$10$ijrPH8tHATahwRrRZ61fcOv/Gckkla6DR5UP0EStybmpxhnXkrRhS', 'admin', '2026-09-15 23:07:35'),
(6, 'Front Desk', 'receptionist@grandhotel.com', 'receptionist', '$2y$10$ijrPH8tHATahwRrRZ61fcOv/Gckkla6DR5UP0EStybmpxhnXkrRhS', 'receptionist', '2026-09-15 23:07:35'),
(7, 'Room Service', 'roomservice@grandhotel.com', 'roomservice', '$2y$10$ijrPH8tHATahwRrRZ61fcOv/Gckkla6DR5UP0EStybmpxhnXkrRhS', 'roomservice', '2026-09-15 23:07:35'),
(8, 'Lukas Alom', 'lukas@gmail.com', 'Lucas', '$2y$10$uvrFSMn.ZjuN0llqVvu5lOTnMVnI4GbSjpjFouZIXtjLf6Vkz22Zy', 'customer', '2026-09-15 23:18:24'),
(9, 'Jon Smith', 'jon@gmail.com', 'Jon', '$2y$10$3hkOIzmIgo9i8tUGs2EdfO6ybwt9IPqotGgwfrimbAb2VtLUwAmDq', 'customer', '2026-09-15 23:20:44');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `damages`
--
ALTER TABLE `damages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
