-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 11:24 AM
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
-- Database: `kebana_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_attendance`
--

CREATE TABLE `tbl_attendance` (
  `attendance_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `status` enum('Present','Absent','Excused') NOT NULL DEFAULT 'Absent',
  `notes` text DEFAULT NULL,
  `marked_by` int(11) DEFAULT NULL,
  `marked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cawangan`
--

CREATE TABLE `tbl_cawangan` (
  `cawangan_id` int(11) NOT NULL,
  `cawangan_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cawangan`
--

INSERT INTO `tbl_cawangan` (`cawangan_id`, `cawangan_name`) VALUES
(1, 'Bintulu'),
(2, 'Sibu'),
(3, 'Miri'),
(4, 'Kuching');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_document`
--

CREATE TABLE `tbl_document` (
  `doc_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `doc_name` varchar(150) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_event`
--

CREATE TABLE `tbl_event` (
  `event_id` int(11) NOT NULL,
  `event_title` varchar(150) NOT NULL,
  `event_date` date NOT NULL,
  `event_end_date` date DEFAULT NULL,
  `venue` varchar(150) NOT NULL,
  `budget_est` decimal(10,2) DEFAULT NULL,
  `guideline_file` varchar(255) DEFAULT NULL,
  `approval_status` varchar(50) DEFAULT 'Pending President',
  `created_by` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Planning',
  `cawangan_id` int(11) DEFAULT NULL,
  `event_level` enum('MASTER','SUB') NOT NULL DEFAULT 'MASTER',
  `parent_event_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_member`
--

CREATE TABLE `tbl_member` (
  `member_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `gender` enum('Lelaki','Perempuan') DEFAULT NULL,
  `ic_number` varchar(20) NOT NULL,
  `village` varchar(100) NOT NULL,
  `phone_no` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transaction`
--

CREATE TABLE `tbl_transaction` (
  `trans_id` int(11) NOT NULL,
  `trans_type` enum('Income','Expense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `trans_date` date NOT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `month_label` varchar(3) DEFAULT NULL,
  `payment_mode` varchar(20) DEFAULT NULL COMMENT 'Cash or Bank',
  `event_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` smallint(6) NOT NULL COMMENT 'Role codes: 1-7 (Pusat), 11-66 (Cawangan), 888 (Super Admin)',
  `email` varchar(100) NOT NULL,
  `cawangan_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `username`, `password_hash`, `role`, `email`, `cawangan_id`) VALUES
(15, 'setiausaha', '$2y$10$p98GzxqbjB4GAwzwpj26Xe7UX9w6kWYxJin90.1UgTZ33oRY04qz2', 4, 'setiausaha@kebana.com', NULL),
(16, 'subintulu', '$2y$10$ozwmI2bogLUmQKNvPomcC.L.0ME6IX9DSQPOcNMMTNKuL9WIwDxyy', 33, 'subintulu@kebana.com', 1),
(17, 'bendahari', '$2y$10$jdRwbownLcJZOqDqr7DeoukJYX37ECnIaHr7t0iQgYuJYczvTKoLG', 6, 'bendahari@kebana.com', NULL),
(18, 'superadmin', '$2y$10$VNTXa2he9pB6tGAH5S/cy.865To.w/Zyn/WDCHofBmqw1cGQWIDkq', 888, 'superadmin@kebana.com', NULL),
(19, 'president', '$2y$10$J/eDfKuh30OGBDdvTfxntuZzofFSuhK6Er2cQKiwH2b/Bpq1RWqeC', 888, 'president@kebana.com', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_attendance`
--
ALTER TABLE `tbl_attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`event_id`,`member_id`),
  ADD KEY `marked_by` (`marked_by`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_member_id` (`member_id`);

--
-- Indexes for table `tbl_cawangan`
--
ALTER TABLE `tbl_cawangan`
  ADD PRIMARY KEY (`cawangan_id`);

--
-- Indexes for table `tbl_document`
--
ALTER TABLE `tbl_document`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `tbl_event`
--
ALTER TABLE `tbl_event`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_event_cawangan` (`cawangan_id`),
  ADD KEY `idx_tbl_event_level_cawangan` (`event_level`,`cawangan_id`),
  ADD KEY `idx_tbl_event_parent_event_id` (`parent_event_id`);

--
-- Indexes for table `tbl_member`
--
ALTER TABLE `tbl_member`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `ic_number` (`ic_number`),
  ADD KEY `fk_member_user` (`created_by`);

--
-- Indexes for table `tbl_transaction`
--
ALTER TABLE `tbl_transaction`
  ADD PRIMARY KEY (`trans_id`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `fk_transaction_event` (`event_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_cawangan` (`cawangan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_attendance`
--
ALTER TABLE `tbl_attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_cawangan`
--
ALTER TABLE `tbl_cawangan`
  MODIFY `cawangan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_document`
--
ALTER TABLE `tbl_document`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_event`
--
ALTER TABLE `tbl_event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `tbl_member`
--
ALTER TABLE `tbl_member`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_transaction`
--
ALTER TABLE `tbl_transaction`
  MODIFY `trans_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_attendance`
--
ALTER TABLE `tbl_attendance`
  ADD CONSTRAINT `tbl_attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `tbl_event` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_attendance_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `tbl_member` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_attendance_ibfk_3` FOREIGN KEY (`marked_by`) REFERENCES `tbl_user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_document`
--
ALTER TABLE `tbl_document`
  ADD CONSTRAINT `tbl_document_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `tbl_event` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_event`
--
ALTER TABLE `tbl_event`
  ADD CONSTRAINT `fk_event_cawangan` FOREIGN KEY (`cawangan_id`) REFERENCES `tbl_cawangan` (`cawangan_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tbl_event_parent` FOREIGN KEY (`parent_event_id`) REFERENCES `tbl_event` (`event_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_event_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `tbl_user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_member`
--
ALTER TABLE `tbl_member`
  ADD CONSTRAINT `fk_member_user` FOREIGN KEY (`created_by`) REFERENCES `tbl_user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_transaction`
--
ALTER TABLE `tbl_transaction`
  ADD CONSTRAINT `fk_transaction_event` FOREIGN KEY (`event_id`) REFERENCES `tbl_event` (`event_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tbl_transaction_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `tbl_user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD CONSTRAINT `fk_user_cawangan` FOREIGN KEY (`cawangan_id`) REFERENCES `tbl_cawangan` (`cawangan_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
