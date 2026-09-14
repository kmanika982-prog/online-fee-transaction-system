-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2026 at 01:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `online_fee_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `admin_name`, `email`, `password`) VALUES
(1, 'System Administrator', 'admin@gmail.com', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `fee_details`
--

CREATE TABLE `fee_details` (
  `fee_id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `fee_type` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `due_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_details`
--

INSERT INTO `fee_details` (`fee_id`, `student_id`, `fee_type`, `total_amount`, `paid_amount`, `due_amount`, `status`) VALUES
(1, 'BCA2025001', 'Tuition Fee', 50000.00, 17000.00, 33000.00, 'partial'),
(2, 'BCA2025002', 'Tuition Fee', 20000.00, 10000.00, 10000.00, 'partial'),
(3, 'BCA2025002', 'identity card fee', 150.00, 75.00, 75.00, 'partial'),
(4, 'BALLB2025001', 'Tuition Fee', 15000.00, 5000.00, 10000.00, 'partial'),
(6, 'BCA2025003', 'Tuition Fee', 20000.00, 10000.00, 10000.00, 'partial'),
(7, 'BALLB2025001', 'exam fee', 2000.00, 0.00, 2000.00, 'partial'),
(8, 'BCA2025004', 'Tuition fee', 20000.00, 0.00, 20000.00, 'Unpaid'),
(9, 'BALLB2025002', 'Tuition fee', 15000.00, 5000.00, 10000.00, 'partial');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `transaction_id` varchar(30) DEFAULT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `fee_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `transaction_id`, `student_id`, `fee_id`, `amount`, `payment_method`, `payment_date`, `status`) VALUES
(1, 'TXN20260907154829892', 'BCA2025001', 1, 5000.00, 'Cash', '2026-09-07 15:48:29', 'paid'),
(2, 'TXN20260908075402607', 'BCA2025001', 1, 2000.00, 'eSewa', '2026-09-08 07:54:02', 'paid'),
(3, 'TXN20260913052202987', 'BCA2025001', 1, 5000.00, 'Cash', '2026-09-13 05:22:02', 'paid'),
(4, 'TXN20260913053343664', 'BCA2025001', 1, 5000.00, 'Cash', '2026-09-13 05:33:43', 'paid'),
(5, 'TXN20260914124828202', 'BALLB2025002', 9, 5000.00, 'Khalti', '2026-09-14 12:48:28', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `semester` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_name`, `course`, `semester`, `email`, `phone`, `address`, `date_of_birth`, `password`) VALUES
('BALLB2025001', 'nisha kumari sah', 'BALLB', '5th sem', 'nisha@gmail.com', '9807625805', 'samsi_3', '2005-05-29', 'nisha'),
('BALLB2025002', 'pallavi kumari sah', 'BALLB', '5th Semester', 'pallavi@gmail.com', '985124557', 'samsi', '2006-05-24', '$2y$10$gLujqeLYMiyPL2LANoTR.eXDNg0VaMWOoIfdEHsxBQlzCsriIzOBu'),
('BCA2025001', 'Manika Sah', 'BCA', '3rd Semester', 'manika@gmail.com', '9800000000', 'mahotari, samsi_3', '2063-12-15', '123456'),
('BCA2025002', 'rajnita kumari sah', 'BCA', '4th sem', 'rajnita@gmail.com', '9800000000', 'mahotari,sonma', '2007-06-29', 'rajnita'),
('BCA2025003', 'ranjana kumari jha', 'BCA', '5th sem', 'ranjana@gamil.com', '9802154654', 'samsi_5', '2062-12-27', 'ranjana'),
('BCA2025004', 'khusbo kumari yadav', 'BCA', '4th Semester', 'khusbo@gmail.com', '98254155', 'janakpur', '2007-10-15', '$2y$10$/mdKx4VHpSfIRhXZrUhyneIOZpI3oYK5S6hfPQTirS4R7NwFefGYu');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `fee_details`
--
ALTER TABLE `fee_details`
  ADD PRIMARY KEY (`fee_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fee_id` (`fee_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_details`
--
ALTER TABLE `fee_details`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fee_details`
--
ALTER TABLE `fee_details`
  ADD CONSTRAINT `fee_details_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `fee_details` (`fee_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
