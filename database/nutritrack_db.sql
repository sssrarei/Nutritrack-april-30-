-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2026 at 01:48 PM
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
-- Database: `nutritrack_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `anthropometric_records`
--

CREATE TABLE `anthropometric_records` (
  `record_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `height` decimal(5,2) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `muac` decimal(5,2) NOT NULL,
  `date_recorded` date NOT NULL,
  `age_months` int(11) DEFAULT NULL,
  `place_of_measurement` varchar(255) DEFAULT NULL,
  `wfa_status` varchar(100) DEFAULT NULL,
  `hfa_status` varchar(100) DEFAULT NULL,
  `wflh_status` varchar(100) DEFAULT NULL,
  `recorded_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anthropometric_records`
--

INSERT INTO `anthropometric_records` (`record_id`, `child_id`, `height`, `weight`, `muac`, `date_recorded`, `age_months`, `place_of_measurement`, `wfa_status`, `hfa_status`, `wflh_status`, `recorded_by`) VALUES
(53, 4, 85.00, 10.20, 12.30, '2026-01-12', 27, 'Child Development Center', 'Normal', 'Normal', 'Normal', 6),
(54, 8, 103.00, 16.50, 14.50, '2026-01-12', 45, 'Child Development Center', 'Normal', 'Normal', 'Normal', 6),
(55, 6, 95.00, 12.00, 11.80, '2026-01-12', 57, 'Child Development Center', 'Underweight', 'Stunted', 'Normal', 6),
(56, 5, 104.00, 20.50, 16.50, '2026-01-12', 47, 'Child Development Center', 'Normal', 'Normal', 'Overweight', 6),
(57, 2, 110.00, 22.80, 17.20, '2026-01-12', 55, 'Child Development Center', 'Normal', 'Normal', 'Overweight', 6),
(58, 7, 88.00, 11.00, 12.80, '2026-01-12', 31, 'Child Development Center', 'Normal', 'Normal', 'Normal', 6),
(59, 3, 115.00, 18.00, 14.00, '2026-01-12', 70, 'Child Development Center', 'Normal', 'Normal', 'Normal', 6),
(60, 14, 102.00, 14.50, 15.50, '2026-01-12', 47, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(61, 15, 95.00, 13.20, 15.00, '2026-01-12', 33, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(62, 16, 104.00, 13.80, 14.00, '2026-01-12', 52, 'CDC', 'Normal', 'Normal', 'Wasted', 13),
(63, 17, 87.00, 11.50, 13.00, '2026-01-12', 22, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(64, 18, 110.00, 18.50, 16.50, '2026-01-12', 50, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(65, 9, 100.00, 15.00, 15.80, '2026-01-12', 42, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(66, 10, 108.00, 16.00, 15.20, '2026-01-12', 58, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(67, 11, 92.00, 11.00, 13.80, '2026-01-12', 29, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(68, 12, 112.00, 20.00, 17.00, '2026-01-12', 63, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(69, 13, 85.00, 10.80, 14.20, '2026-01-12', 22, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(70, 18, 120.00, 19.50, 15.00, '2026-02-12', 51, 'CDC', 'Normal', 'Tall', 'Normal', 13),
(71, 17, 85.00, 12.50, 14.00, '2026-02-12', 23, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(72, 15, 98.00, 14.50, 16.00, '2026-02-12', 34, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(73, 16, 104.00, 12.80, 13.00, '2026-02-12', 53, 'CDC', 'Underweight', 'Normal', 'Severely Wasted', 13),
(74, 14, 103.00, 15.50, 17.50, '2026-02-12', 48, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(75, 18, 110.00, 20.60, 17.00, '2026-03-12', 52, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(76, 17, 89.00, 14.00, 15.00, '2026-04-13', 25, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(77, 15, 100.00, 16.00, 18.00, '2026-03-12', 35, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(78, 16, 100.00, 10.20, 16.00, '2026-03-13', 54, 'CDC', 'Severely Underweight', 'Normal', 'Severely Wasted', 13),
(79, 14, 88.00, 14.00, 17.50, '2026-03-12', 49, 'CDC', 'Normal', 'Severely Stunted', 'Normal', 13),
(80, 18, 100.00, 22.00, 15.00, '2026-04-02', 52, 'CDC', 'Normal', 'Normal', 'Obese', 13),
(81, 17, 90.00, 15.00, 15.00, '2026-04-02', 25, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(82, 15, 100.00, 16.00, 18.00, '2026-04-02', 35, 'CDC', 'Normal', 'Normal', 'Normal', 13),
(83, 15, 100.00, 69.00, 14.00, '2026-04-02', 35, 'CDC', 'Overweight', 'Normal', 'Obese', 13),
(84, 16, 100.00, 13.00, 15.00, '2026-04-02', 54, 'CDC', 'Underweight', 'Normal', 'Wasted', 13),
(85, 14, 89.00, 15.00, 13.00, '2026-04-02', 50, 'CDC', 'Normal', 'Severely Stunted', 'Overweight', 13);

-- --------------------------------------------------------

--
-- Table structure for table `cdc`
--

CREATE TABLE `cdc` (
  `cdc_id` int(11) NOT NULL,
  `cdc_name` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cdc`
--

INSERT INTO `cdc` (`cdc_id`, `cdc_name`, `barangay`, `address`, `status`, `created_at`) VALUES
(1, 'Niog 1 Day Care Center', 'Baranggay Niog', '1 Aguinaldo Highway, Barangay Niog 1, Bacoor City, Cavite', 'Active', '2026-03-21 21:46:21'),
(2, 'Bayan Luma IV Day Care Development Center', 'Baranggay Bayan', 'Padua St., Barangay Bayan Luma IV, Imus City, Cavite', 'Active', '2026-03-21 21:52:26'),
(3, 'Bucandala 1 Child Development Center', 'Baranggay Bucandula', 'Blk 13 Lot 1, La Terraza Village, Barangay Bucandala I, Imus City, Cavite', 'Active', '2026-03-21 21:52:48'),
(4, 'Tanza Child Development Center', 'Baranggay Amaya', '15-B Tramo St., Barangay Amaya, Tanza, Cavite, 4108', 'Active', '2026-03-21 21:53:22'),
(5, 'Day Care Center Aniban II', 'Baranggay Aniban', '04 Zapote–Molino Road, Barangay Aniban II, Bacoor City, Cavite, Philippines', 'Active', '2026-03-21 21:54:32'),
(6, 'Strike Day Care Center', 'Baranggay Molino II', 'Villa Esperanza / Rambles area, Barangay Molino II, Bacoor City, Cavite', 'Active', '2026-03-21 21:55:49'),
(7, 'Strike Day Care Center – Child Development Center', 'Molino 2', 'Rambles Villa Esperanza, Molino 2, Bacoor City, Cavite', 'Active', '2026-03-31 15:09:06'),
(8, 'Molino 5 Child Development Center', 'Molino 5', 'Humabon St., Molino 5, Bacoor City, Cavite', 'Active', '2026-03-31 15:09:32');

-- --------------------------------------------------------

--
-- Table structure for table `cdw_assignments`
--

CREATE TABLE `cdw_assignments` (
  `assignment_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `cdc_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cdw_assignments`
--

INSERT INTO `cdw_assignments` (`assignment_id`, `user_id`, `cdc_id`, `assigned_at`) VALUES
(1, 5, 2, '2026-03-21 21:56:52'),
(2, 5, 3, '2026-03-21 21:56:52'),
(3, 6, 1, '2026-03-22 13:31:02'),
(4, 6, 6, '2026-03-22 13:31:02'),
(5, 6, 4, '2026-03-22 13:31:02'),
(6, 9, 3, '2026-03-25 22:26:08'),
(7, 9, 1, '2026-03-25 22:26:08'),
(8, 9, 6, '2026-03-25 22:26:08'),
(9, 13, 8, '2026-03-31 15:10:22'),
(10, 13, 7, '2026-03-31 15:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `child_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cdc_id` int(11) DEFAULT NULL,
  `access_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`child_id`, `first_name`, `middle_name`, `last_name`, `birthdate`, `sex`, `address`, `religion`, `guardian_name`, `contact_number`, `created_at`, `cdc_id`, `access_code`) VALUES
(2, 'Liam Gabriel', 'S', 'Cruz', '2021-05-14', 'male', 'Blk 3 Lot 5, Niog II, Bacoor, Cavite', NULL, 'Maria Teresa Cruz', '09173451122', '2026-03-22 13:35:24', 1, 'CH-2530'),
(3, 'Sophia Anne', 'R', 'Dela Cruz', '2020-02-22', 'female', 'Phase 2, Niog I, Bacoor, Cavite', NULL, 'Carlo Dela Cruz', '09285567789', '2026-03-22 13:37:15', 1, 'CH-9845'),
(4, 'Alex', 'S', 'Cruz', '2023-10-11', 'male', 'Niog 1 Day Care Center – Aguinaldo Hwy, Bacoor', NULL, 'Mia Cruz', '09098847865', '2026-03-22 17:58:18', 1, 'CH-7813'),
(5, 'Jr', '', 'Lino', '2022-02-09', 'male', 'Niog', 'Christian', '', '', '2026-03-22 20:09:49', 1, 'CH-5769'),
(6, 'John', 'Michael', 'Reyes', '2021-03-15', 'female', 'Brgy. Molino III, Bacoor City', 'Catholic', 'Maria Reyex', '09123456789', '2026-03-27 10:47:53', 1, 'CH-2535'),
(7, 'maksda', '', '', '2023-06-05', 'female', 'Block 5  Lot 12, Sampaguita St., Brgy. Niog I, Bacoor, Cavite', 'asfasf', '', '', '2026-03-27 10:49:30', 1, 'CH-9728'),
(8, 'Angelica', 'Joy', 'Mendoza', '2022-03-22', 'female', 'Brgy. Niog II, Bacoor City', 'Catholic', '', '', '2026-03-27 14:15:44', 1, 'CH-1743'),
(9, 'Trisha', 'Mae', 'Gutierrez', '2022-06-21', 'Female', 'Molino 5, Bacoor', 'Catholic', 'Ramon Gutierrez', '09181234566', '2026-03-31 15:15:40', 8, 'CH-1564'),
(10, 'Caleb', 'John', 'Velasco', '2021-02-14', 'Male', 'Molino 5, Bacoor', 'Christian', 'Anna Velasco', '09181234567', '2026-03-31 15:16:47', 8, 'CH-3130'),
(11, 'Princess', 'Anne', 'Soriano', '2023-08-09', 'Female', 'Molino 5, Bacoor', 'Catholic', 'Edgar Soriano', '09181234568', '2026-03-31 15:19:27', 8, 'CH-8359'),
(12, 'Nathan', 'Luke', 'Cabrera', '2020-10-05', 'Male', 'Molino 5, Bacoor', 'Catholic', 'Sheila Cabrera', '', '2026-03-31 15:20:49', 8, 'CH-8551'),
(13, 'Aira', 'Nicole', 'Dominguez', '2024-03-12', 'Female', 'Molino 5, Bacoor', 'Christian', 'Victor Dominguez', '09181234570', '2026-03-31 15:22:07', 8, 'CH-3501'),
(14, 'Zion', 'Matthew', 'Delgado', '2022-01-18', 'Male', 'Molino 2, Bacoor', 'Christian', 'Liza Delgado', '09181234561', '2026-03-31 15:23:33', 7, 'CH-5871'),
(15, 'Kiara', 'Elise', 'Navarro', '2023-04-10', 'Female', 'Molino 2, Bacoor', 'Christian', 'Joel Navarro', '09181234562', '2026-03-31 15:24:22', 7, 'CH-8608'),
(16, 'Rafael', 'Andre', 'Pineda', '2021-09-03', 'Male', 'Molino 2, Bacoor', 'Catholic', 'Grace Pineda', '09181234563', '2026-03-31 15:25:16', 7, 'CH-7582'),
(17, 'Danica', 'Joy', 'Alonzo', '2024-02-27', 'Female', 'Molino 2, Bacoor', 'Catholic', 'Carlo Alonzo', '09181234564', '2026-03-31 15:26:14', 7, 'CH-1234'),
(18, 'Bryan', 'Kyle', 'Serrano', '2021-11-12', 'Male', 'Molino 2, Bacoor', 'Christian', 'Mae Serrano', '09181234565', '2026-03-31 15:27:25', 7, 'CH-7375');

-- --------------------------------------------------------

--
-- Table structure for table `child_health_information`
--

CREATE TABLE `child_health_information` (
  `health_info_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `vaccination_card_file_path` varchar(255) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `comorbidities` text DEFAULT NULL,
  `medical_history_file_path` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `child_health_information`
--

INSERT INTO `child_health_information` (`health_info_id`, `child_id`, `vaccination_card_file_path`, `allergies`, `comorbidities`, `medical_history_file_path`, `updated_at`) VALUES
(1, 5, '', '', '', '', '2026-03-22 20:09:49'),
(2, 6, '', 'None', 'None', '', '2026-03-27 10:47:53'),
(3, 7, '', '', '', '', '2026-03-27 10:49:30'),
(4, 4, '', '', '', '', '2026-03-27 12:46:55'),
(5, 8, '', 'None', 'None', '', '2026-03-27 14:15:44'),
(6, 9, '', '', '', '', '2026-03-31 15:15:40'),
(7, 10, '', '', '', '', '2026-03-31 15:16:47'),
(8, 11, '', '', '', '', '2026-03-31 15:19:27'),
(9, 12, '', '', '', '', '2026-03-31 15:20:49'),
(10, 13, '', '', '', '', '2026-03-31 15:22:07'),
(11, 14, '', '', '', '', '2026-03-31 15:23:33'),
(12, 15, '', '', '', '', '2026-03-31 15:24:22'),
(13, 16, '', '', '', '', '2026-03-31 15:25:16'),
(14, 17, '', '', '', '', '2026-03-31 15:26:14'),
(15, 18, '', '', '', '', '2026-03-31 15:27:25');

-- --------------------------------------------------------

--
-- Table structure for table `deworming_records`
--

CREATE TABLE `deworming_records` (
  `deworm_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `deworming_date` date NOT NULL,
  `attendance` enum('Taken','Not Taken') NOT NULL DEFAULT 'Taken',
  `medicine` varchar(100) DEFAULT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deworming_records`
--

INSERT INTO `deworming_records` (`deworm_id`, `child_id`, `deworming_date`, `attendance`, `medicine`, `dosage`, `remarks`, `recorded_by`, `created_at`, `updated_at`) VALUES
(15, 7, '2026-03-01', 'Taken', 'gamot', '500mg', NULL, 6, '2026-03-31 05:42:37', '2026-03-31 05:42:37'),
(16, 4, '2026-03-01', 'Taken', 'gamot', '500mg', NULL, 6, '2026-03-31 05:42:37', '2026-03-31 05:42:37'),
(17, 2, '2026-03-01', 'Taken', 'gamot', '500mg', NULL, 6, '2026-03-31 05:42:37', '2026-03-31 05:42:37'),
(18, 3, '2026-03-01', 'Taken', 'gamot', '500mg', NULL, 6, '2026-03-31 05:42:37', '2026-03-31 05:42:37'),
(19, 5, '2026-03-01', 'Taken', 'gamot', '500mg', NULL, 6, '2026-03-31 05:42:37', '2026-03-31 05:42:37'),
(20, 8, '2026-03-01', 'Taken', 'gamot', '500mg', NULL, 6, '2026-03-31 05:42:37', '2026-03-31 05:42:37'),
(21, 6, '2026-03-01', 'Taken', 'gamot', '500mg', NULL, 6, '2026-03-31 05:42:37', '2026-03-31 05:42:37'),
(22, 17, '2026-01-14', 'Taken', 'gamot', '500mg', NULL, 13, '2026-03-31 16:30:35', '2026-03-31 16:30:35'),
(23, 14, '2026-01-14', 'Taken', 'gamot', '500mg', NULL, 13, '2026-03-31 16:30:35', '2026-03-31 16:30:35'),
(24, 15, '2026-01-14', 'Taken', 'gamot', '500mg', NULL, 13, '2026-03-31 16:30:35', '2026-03-31 16:30:35'),
(25, 16, '2026-01-14', 'Taken', 'gamot', '500mg', NULL, 13, '2026-03-31 16:30:35', '2026-03-31 16:30:35'),
(26, 18, '2026-01-14', 'Taken', 'gamot', '500mg', NULL, 13, '2026-03-31 16:30:35', '2026-03-31 16:30:35'),
(27, 17, '2026-04-12', 'Not Taken', NULL, NULL, 'Not Taken', 13, '2026-03-31 17:18:29', '2026-03-31 17:18:29'),
(28, 14, '2026-04-12', 'Taken', 'Dioxele', '500mg', NULL, 13, '2026-03-31 17:18:29', '2026-03-31 17:18:29'),
(29, 15, '2026-04-12', 'Taken', 'Dioxele', '500mg', NULL, 13, '2026-03-31 17:18:29', '2026-03-31 17:18:29'),
(30, 16, '2026-04-12', 'Taken', 'Dioxele', '500mg', NULL, 13, '2026-03-31 17:18:29', '2026-03-31 17:18:29'),
(31, 18, '2026-04-12', 'Taken', 'Dioxele', '500mg', NULL, 13, '2026-03-31 17:18:29', '2026-03-31 17:18:29');

-- --------------------------------------------------------

--
-- Table structure for table `feeding_records`
--

CREATE TABLE `feeding_records` (
  `feeding_record_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `feeding_date` date NOT NULL,
  `attendance` enum('Present','Absent') NOT NULL DEFAULT 'Present',
  `remarks` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feeding_records`
--

INSERT INTO `feeding_records` (`feeding_record_id`, `child_id`, `feeding_date`, `attendance`, `remarks`, `recorded_by`, `created_at`, `updated_at`) VALUES
(1, 4, '2026-03-29', 'Present', '', 6, '2026-03-29 14:27:42', '2026-03-29 16:38:57'),
(2, 8, '2026-03-29', 'Present', '', 6, '2026-03-29 14:27:42', '2026-03-29 16:38:57'),
(3, 6, '2026-03-29', 'Present', '', 6, '2026-03-29 14:27:42', '2026-03-29 16:38:57'),
(4, 5, '2026-03-29', 'Present', '', 6, '2026-03-29 14:27:42', '2026-03-29 16:38:57'),
(5, 2, '2026-03-29', 'Present', '', 6, '2026-03-29 14:27:42', '2026-03-29 16:38:57'),
(6, 7, '2026-03-29', 'Present', '', 6, '2026-03-29 14:27:42', '2026-03-29 16:38:57'),
(7, 3, '2026-03-29', 'Present', 'eat fast', 6, '2026-03-29 14:27:42', '2026-03-29 16:38:57'),
(8, 4, '2026-03-04', 'Present', '', 6, '2026-03-29 16:40:44', '2026-03-29 16:40:44'),
(9, 8, '2026-03-04', 'Present', '', 6, '2026-03-29 16:40:44', '2026-03-29 16:40:44'),
(10, 6, '2026-03-04', 'Present', '', 6, '2026-03-29 16:40:44', '2026-03-29 16:40:44'),
(11, 5, '2026-03-04', 'Present', '', 6, '2026-03-29 16:40:44', '2026-03-29 16:40:44'),
(12, 2, '2026-03-04', 'Present', '', 6, '2026-03-29 16:40:44', '2026-03-29 16:40:44'),
(13, 7, '2026-03-04', 'Present', '', 6, '2026-03-29 16:40:44', '2026-03-29 16:40:44'),
(14, 3, '2026-03-04', 'Present', '', 6, '2026-03-29 16:40:44', '2026-03-29 16:40:44'),
(15, 4, '2026-03-05', 'Present', '', 6, '2026-03-29 16:43:34', '2026-03-29 16:43:34'),
(16, 8, '2026-03-05', 'Present', '', 6, '2026-03-29 16:43:34', '2026-03-29 16:43:34'),
(17, 6, '2026-03-05', 'Present', '', 6, '2026-03-29 16:43:34', '2026-03-29 16:43:34'),
(18, 5, '2026-03-05', 'Present', '', 6, '2026-03-29 16:43:34', '2026-03-29 16:43:34'),
(19, 2, '2026-03-05', 'Present', '', 6, '2026-03-29 16:43:34', '2026-03-29 16:43:34'),
(20, 7, '2026-03-05', 'Present', '', 6, '2026-03-29 16:43:34', '2026-03-29 16:43:34'),
(21, 3, '2026-03-05', 'Present', '', 6, '2026-03-29 16:43:34', '2026-03-29 16:43:34'),
(22, 18, '2026-03-31', 'Present', '', 13, '2026-03-31 16:12:25', '2026-03-31 18:43:34'),
(23, 17, '2026-03-31', 'Present', '', 13, '2026-03-31 16:12:25', '2026-03-31 18:43:34'),
(24, 15, '2026-03-31', 'Present', '', 13, '2026-03-31 16:12:25', '2026-03-31 18:43:34'),
(25, 16, '2026-03-31', 'Present', '', 13, '2026-03-31 16:12:25', '2026-03-31 18:43:34'),
(26, 14, '2026-03-31', 'Present', '', 13, '2026-03-31 16:12:25', '2026-03-31 18:43:34'),
(27, 18, '2026-03-02', 'Present', '', 13, '2026-03-31 17:57:29', '2026-03-31 18:16:54'),
(28, 17, '2026-03-02', 'Present', '', 13, '2026-03-31 17:57:29', '2026-03-31 18:16:54'),
(29, 15, '2026-03-02', 'Present', '', 13, '2026-03-31 17:57:29', '2026-03-31 18:16:54'),
(30, 16, '2026-03-02', 'Present', '', 13, '2026-03-31 17:57:29', '2026-03-31 18:16:54'),
(31, 14, '2026-03-02', 'Present', '', 13, '2026-03-31 17:57:29', '2026-03-31 18:16:54'),
(32, 18, '2026-03-03', 'Present', '', 13, '2026-03-31 18:19:25', '2026-03-31 18:19:25'),
(33, 17, '2026-03-03', 'Present', '', 13, '2026-03-31 18:19:25', '2026-03-31 18:19:25'),
(34, 15, '2026-03-03', 'Present', '', 13, '2026-03-31 18:19:25', '2026-03-31 18:19:25'),
(35, 16, '2026-03-03', 'Present', '', 13, '2026-03-31 18:19:25', '2026-03-31 18:19:25'),
(36, 14, '2026-03-03', 'Present', '', 13, '2026-03-31 18:19:25', '2026-03-31 18:19:25'),
(37, 18, '2026-03-04', 'Present', '', 13, '2026-03-31 18:27:29', '2026-03-31 18:27:29'),
(38, 17, '2026-03-04', 'Present', '', 13, '2026-03-31 18:27:29', '2026-03-31 18:27:29'),
(39, 15, '2026-03-04', 'Present', '', 13, '2026-03-31 18:27:29', '2026-03-31 18:27:29'),
(40, 16, '2026-03-04', 'Present', '', 13, '2026-03-31 18:27:29', '2026-03-31 18:27:29'),
(41, 14, '2026-03-04', 'Present', '', 13, '2026-03-31 18:27:29', '2026-03-31 18:27:29'),
(42, 18, '2026-03-07', 'Present', '', 13, '2026-03-31 18:44:05', '2026-03-31 18:44:05'),
(43, 17, '2026-03-07', 'Present', '', 13, '2026-03-31 18:44:05', '2026-03-31 18:44:05'),
(44, 15, '2026-03-07', 'Present', '', 13, '2026-03-31 18:44:05', '2026-03-31 18:44:05'),
(45, 16, '2026-03-07', 'Present', '', 13, '2026-03-31 18:44:05', '2026-03-31 18:44:05'),
(46, 14, '2026-03-07', 'Present', '', 13, '2026-03-31 18:44:05', '2026-03-31 18:44:05'),
(47, 18, '2026-04-01', 'Present', '', 13, '2026-04-01 18:49:43', '2026-04-01 18:49:43'),
(48, 17, '2026-04-01', 'Present', '', 13, '2026-04-01 18:49:43', '2026-04-01 18:49:43'),
(49, 15, '2026-04-01', 'Absent', 'Absent', 13, '2026-04-01 18:49:43', '2026-04-01 18:49:43'),
(50, 16, '2026-04-01', 'Absent', 'Absent', 13, '2026-04-01 18:49:43', '2026-04-01 18:49:43'),
(51, 14, '2026-04-01', 'Absent', 'Absent', 13, '2026-04-01 18:49:43', '2026-04-01 18:49:43'),
(52, 18, '2026-04-02', 'Absent', 'Absent', 13, '2026-04-01 18:50:34', '2026-04-01 18:50:34'),
(53, 17, '2026-04-02', 'Present', '', 13, '2026-04-01 18:50:34', '2026-04-01 18:50:34'),
(54, 15, '2026-04-02', 'Present', '', 13, '2026-04-01 18:50:34', '2026-04-01 18:50:34'),
(55, 16, '2026-04-02', 'Present', '', 13, '2026-04-01 18:50:34', '2026-04-01 18:50:34'),
(56, 14, '2026-04-02', 'Present', '', 13, '2026-04-01 18:50:35', '2026-04-01 18:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `feeding_record_items`
--

CREATE TABLE `feeding_record_items` (
  `feeding_item_id` int(11) NOT NULL,
  `feeding_record_id` int(11) NOT NULL,
  `food_group_id` int(11) NOT NULL,
  `food_item_id` int(11) NOT NULL,
  `measurement_text` varchar(100) DEFAULT NULL,
  `quantity` decimal(5,2) NOT NULL DEFAULT 1.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feeding_record_items`
--

INSERT INTO `feeding_record_items` (`feeding_item_id`, `feeding_record_id`, `food_group_id`, `food_item_id`, `measurement_text`, `quantity`, `created_at`) VALUES
(100, 13, 7, 150, '3pcs', 1.00, '2026-03-29 16:40:44'),
(101, 14, 2, 18, '1 Serving', 1.00, '2026-03-29 16:40:44'),
(102, 14, 7, 150, '3pcs', 1.00, '2026-03-29 16:40:44'),
(103, 15, 10, 175, '1tbsp', 1.00, '2026-03-29 16:43:34'),
(104, 15, 11, 182, '2pcs', 1.00, '2026-03-29 16:43:34'),
(105, 15, 3, 78, '1 Serving', 1.00, '2026-03-29 16:43:34'),
(106, 16, 10, 175, '1tbsp', 1.00, '2026-03-29 16:43:34'),
(107, 16, 11, 182, '2pcs', 1.00, '2026-03-29 16:43:34'),
(108, 16, 3, 78, '1 Serving', 1.00, '2026-03-29 16:43:34'),
(109, 17, 10, 175, '1tbsp', 1.00, '2026-03-29 16:43:34'),
(110, 17, 11, 182, '2pcs', 1.00, '2026-03-29 16:43:34'),
(111, 17, 3, 78, '1 Serving', 1.00, '2026-03-29 16:43:34'),
(112, 18, 10, 175, '1tbsp', 1.00, '2026-03-29 16:43:34'),
(113, 18, 11, 182, '2pcs', 1.00, '2026-03-29 16:43:34'),
(114, 18, 3, 78, '1 Serving', 1.00, '2026-03-29 16:43:34'),
(115, 19, 10, 175, '1tbsp', 1.00, '2026-03-29 16:43:34'),
(116, 19, 11, 182, '2pcs', 1.00, '2026-03-29 16:43:34'),
(117, 19, 3, 78, '1 Serving', 1.00, '2026-03-29 16:43:34'),
(118, 20, 10, 175, '1tbsp', 1.00, '2026-03-29 16:43:34'),
(119, 20, 11, 182, '2pcs', 1.00, '2026-03-29 16:43:34'),
(120, 20, 3, 78, '1 Serving', 1.00, '2026-03-29 16:43:34'),
(121, 21, 10, 175, '1tbsp', 1.00, '2026-03-29 16:43:34'),
(122, 21, 11, 182, '2pcs', 1.00, '2026-03-29 16:43:34'),
(123, 21, 3, 78, '1 Serving', 1.00, '2026-03-29 16:43:34'),
(194, 27, 1, 2, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(195, 27, 3, 34, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(196, 28, 1, 2, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(197, 28, 3, 34, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(198, 29, 1, 2, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(199, 29, 3, 34, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(200, 30, 1, 2, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(201, 30, 3, 34, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(202, 31, 1, 2, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(203, 31, 3, 34, '1 Serving', 1.00, '2026-03-31 18:16:54'),
(204, 32, 9, 169, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(205, 32, 2, 12, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(206, 33, 9, 169, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(207, 33, 2, 12, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(208, 34, 9, 169, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(209, 34, 2, 12, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(210, 35, 9, 169, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(211, 35, 2, 12, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(212, 36, 9, 169, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(213, 36, 2, 12, '1 Serving', 1.00, '2026-03-31 18:19:25'),
(214, 37, 8, 155, '2 cup', 1.00, '2026-03-31 18:27:29'),
(215, 38, 8, 155, '2 cup', 1.00, '2026-03-31 18:27:29'),
(216, 39, 8, 155, '2 cup', 1.00, '2026-03-31 18:27:29'),
(217, 40, 8, 155, '2 cup', 1.00, '2026-03-31 18:27:29'),
(218, 41, 8, 155, '2 cup', 1.00, '2026-03-31 18:27:29'),
(219, 22, 10, 178, '2tbsp', 1.00, '2026-03-31 18:43:34'),
(220, 22, 8, 155, '1 cup', 1.00, '2026-03-31 18:43:34'),
(221, 22, 2, 15, '2pieces', 1.00, '2026-03-31 18:43:34'),
(222, 23, 10, 178, '2tbsp', 1.00, '2026-03-31 18:43:34'),
(223, 23, 8, 155, '1 cup', 1.00, '2026-03-31 18:43:34'),
(224, 23, 2, 15, '2pieces', 1.00, '2026-03-31 18:43:34'),
(225, 24, 10, 178, '2tbsp', 1.00, '2026-03-31 18:43:34'),
(226, 24, 8, 155, '1 cup', 1.00, '2026-03-31 18:43:34'),
(227, 24, 2, 15, '2pieces', 1.00, '2026-03-31 18:43:34'),
(228, 25, 10, 178, '2tbsp', 1.00, '2026-03-31 18:43:34'),
(229, 25, 8, 155, '1 cup', 1.00, '2026-03-31 18:43:34'),
(230, 25, 2, 15, '2pieces', 1.00, '2026-03-31 18:43:34'),
(231, 26, 10, 178, '2tbsp', 1.00, '2026-03-31 18:43:34'),
(232, 26, 8, 155, '1 cup', 1.00, '2026-03-31 18:43:34'),
(233, 26, 2, 15, '2pieces', 1.00, '2026-03-31 18:43:34'),
(234, 42, 5, 128, '1 Serving', 1.00, '2026-03-31 18:44:05'),
(235, 42, 10, 178, '2tbsp', 1.00, '2026-03-31 18:44:05'),
(236, 42, 8, 155, '1 cup', 1.00, '2026-03-31 18:44:05'),
(237, 43, 5, 128, '1 Serving', 1.00, '2026-03-31 18:44:05'),
(238, 43, 10, 178, '2tbsp', 1.00, '2026-03-31 18:44:05'),
(239, 43, 8, 155, '1 cup', 1.00, '2026-03-31 18:44:05'),
(240, 44, 5, 128, '1 Serving', 1.00, '2026-03-31 18:44:05'),
(241, 44, 10, 178, '2tbsp', 1.00, '2026-03-31 18:44:05'),
(242, 44, 8, 155, '1 cup', 1.00, '2026-03-31 18:44:05'),
(243, 45, 5, 128, '1 Serving', 1.00, '2026-03-31 18:44:05'),
(244, 45, 10, 178, '2tbsp', 1.00, '2026-03-31 18:44:05'),
(245, 45, 8, 155, '1 cup', 1.00, '2026-03-31 18:44:05'),
(246, 46, 5, 128, '1 Serving', 1.00, '2026-03-31 18:44:05'),
(247, 46, 10, 178, '2tbsp', 1.00, '2026-03-31 18:44:05'),
(248, 46, 8, 155, '1 cup', 1.00, '2026-03-31 18:44:05'),
(249, 47, 2, 17, '1 Serving', 1.00, '2026-04-01 18:49:43'),
(250, 47, 2, 12, '2pcs', 1.00, '2026-04-01 18:49:43'),
(251, 48, 2, 17, '1 Serving', 1.00, '2026-04-01 18:49:43'),
(252, 48, 2, 12, '2pcs', 1.00, '2026-04-01 18:49:43'),
(253, 53, 2, 20, '1 Serving', 1.00, '2026-04-01 18:50:34'),
(254, 53, 8, 155, '1 cup', 1.00, '2026-04-01 18:50:34'),
(255, 54, 2, 20, '1 Serving', 1.00, '2026-04-01 18:50:34'),
(256, 54, 8, 155, '1 cup', 1.00, '2026-04-01 18:50:34'),
(257, 55, 2, 20, '1 Serving', 1.00, '2026-04-01 18:50:34'),
(258, 55, 8, 155, '1 cup', 1.00, '2026-04-01 18:50:34'),
(259, 56, 2, 20, '1 Serving', 1.00, '2026-04-01 18:50:35'),
(260, 56, 8, 155, '1 cup', 1.00, '2026-04-01 18:50:35');

-- --------------------------------------------------------

--
-- Table structure for table `food_groups`
--

CREATE TABLE `food_groups` (
  `food_group_id` int(11) NOT NULL,
  `food_group_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_groups`
--

INSERT INTO `food_groups` (`food_group_id`, `food_group_name`, `created_at`) VALUES
(1, 'Rice, Corn, Root Crops', '2026-03-29 13:53:04'),
(2, 'Bread and Noodles', '2026-03-29 13:53:04'),
(3, 'Vegetables', '2026-03-29 13:53:04'),
(4, 'Fruits', '2026-03-29 13:53:04'),
(5, 'Meat & Poultry', '2026-03-29 13:53:04'),
(6, 'Fish and Shellfish', '2026-03-29 13:53:04'),
(7, 'Egg', '2026-03-29 13:53:04'),
(8, 'Milk and Milk Products', '2026-03-29 13:53:04'),
(9, 'Dried Beans and Nuts', '2026-03-29 13:53:04'),
(10, 'Fats and Oils', '2026-03-29 13:53:04'),
(11, 'Sugar/Sweets', '2026-03-29 13:53:04');

-- --------------------------------------------------------

--
-- Table structure for table `food_items`
--

CREATE TABLE `food_items` (
  `food_item_id` int(11) NOT NULL,
  `food_group_id` int(11) NOT NULL,
  `food_item_name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_items`
--

INSERT INTO `food_items` (`food_item_id`, `food_group_id`, `food_item_name`, `created_at`) VALUES
(1, 1, 'Rice', '2026-03-29 13:53:44'),
(2, 1, 'Lugaw', '2026-03-29 13:53:44'),
(3, 1, 'Suman sa Ibos', '2026-03-29 13:53:44'),
(4, 1, 'Binatog', '2026-03-29 13:53:44'),
(5, 1, 'Corn, boiled', '2026-03-29 13:53:44'),
(6, 1, 'Corn, canned', '2026-03-29 13:53:44'),
(7, 1, 'Sweet Potato', '2026-03-29 13:53:44'),
(8, 1, 'Cassava', '2026-03-29 13:53:44'),
(9, 1, 'Potato', '2026-03-29 13:53:44'),
(10, 1, 'Chest Nut', '2026-03-29 13:53:44'),
(11, 2, 'Pan Amerikano', '2026-03-29 13:53:57'),
(12, 2, 'Pan de sal', '2026-03-29 13:53:57'),
(13, 2, 'Pan de Limon', '2026-03-29 13:53:57'),
(14, 2, 'Rolls (Hotdog/Burger)', '2026-03-29 13:53:57'),
(15, 2, 'Wheat Bread', '2026-03-29 13:53:57'),
(16, 2, 'Galyetas de Patatas', '2026-03-29 13:53:57'),
(17, 2, 'Noodles: Bihon', '2026-03-29 13:53:57'),
(18, 2, 'Noodles: Macaroni', '2026-03-29 13:53:57'),
(19, 2, 'Noodles: Sotanghon', '2026-03-29 13:53:57'),
(20, 2, 'Noodles: Spaghetti', '2026-03-29 13:53:57'),
(21, 2, 'Crackers', '2026-03-29 13:53:57'),
(22, 2, 'French Fries', '2026-03-29 13:53:57'),
(23, 2, 'Oatmeal cooked', '2026-03-29 13:53:57'),
(24, 2, 'Popcorn, plain', '2026-03-29 13:53:57'),
(25, 2, 'Skyflakes', '2026-03-29 13:53:57'),
(26, 2, 'Corn flakes', '2026-03-29 13:53:57'),
(27, 3, 'Alugbati Leaves', '2026-03-29 13:54:07'),
(28, 3, 'Ampalaya', '2026-03-29 13:54:07'),
(29, 3, 'Baguio Beans', '2026-03-29 13:54:07'),
(30, 3, 'Bamboo Shoot', '2026-03-29 13:54:07'),
(31, 3, 'Banana Heart', '2026-03-29 13:54:07'),
(32, 3, 'Bataw Pods', '2026-03-29 13:54:07'),
(33, 3, 'Beets', '2026-03-29 13:54:07'),
(34, 3, 'Cabbage', '2026-03-29 13:54:07'),
(35, 3, 'Cauliflower', '2026-03-29 13:54:07'),
(36, 3, 'Camote Leaves', '2026-03-29 13:54:07'),
(37, 3, 'Celery', '2026-03-29 13:54:07'),
(38, 3, 'Chayote Leaves', '2026-03-29 13:54:07'),
(39, 3, 'Cucumber', '2026-03-29 13:54:07'),
(40, 3, 'Eggplant', '2026-03-29 13:54:07'),
(41, 3, 'Gabi Leaves', '2026-03-29 13:54:07'),
(42, 3, 'Kangkong', '2026-03-29 13:54:07'),
(43, 3, 'Katuray Flowers', '2026-03-29 13:54:07'),
(44, 3, 'Lettuce', '2026-03-29 13:54:07'),
(45, 3, 'Malunggay Leaves', '2026-03-29 13:54:07'),
(46, 3, 'Mushroom fresh', '2026-03-29 13:54:07'),
(47, 3, 'Mustard Leaves', '2026-03-29 13:54:07'),
(48, 3, 'Okra', '2026-03-29 13:54:07'),
(49, 3, 'Onion Bulb', '2026-03-29 13:54:07'),
(50, 3, 'Pako', '2026-03-29 13:54:07'),
(51, 3, 'Papaya, green', '2026-03-29 13:54:07'),
(52, 3, 'Patola', '2026-03-29 13:54:07'),
(53, 3, 'Pepper, Leaves', '2026-03-29 13:54:07'),
(54, 3, 'Pechay', '2026-03-29 13:54:07'),
(55, 3, 'Radish', '2026-03-29 13:54:07'),
(56, 3, 'Saluyot', '2026-03-29 13:54:07'),
(57, 3, 'Sigarilyas Pods', '2026-03-29 13:54:07'),
(58, 3, 'Sitsaro', '2026-03-29 13:54:07'),
(59, 3, 'String Beans', '2026-03-29 13:54:07'),
(60, 3, 'Tomato', '2026-03-29 13:54:07'),
(61, 3, 'Upo', '2026-03-29 13:54:07'),
(62, 3, 'Carrot', '2026-03-29 13:54:07'),
(63, 3, 'Coconut Shoot', '2026-03-29 13:54:07'),
(64, 3, 'Cowpea, Pods', '2026-03-29 13:54:07'),
(65, 3, 'Langka, Hilaw', '2026-03-29 13:54:07'),
(66, 3, 'Lima Beans, pods', '2026-03-29 13:54:07'),
(67, 3, 'Togue', '2026-03-29 13:54:07'),
(68, 3, 'Rimas', '2026-03-29 13:54:07'),
(69, 3, 'Squash Fruit', '2026-03-29 13:54:07'),
(70, 3, 'Singkamas, pods and Tubers', '2026-03-29 13:54:07'),
(71, 3, 'String Beans, Pods', '2026-03-29 13:54:07'),
(72, 3, 'Asparagus Tips', '2026-03-29 13:54:07'),
(73, 3, 'Baby Corn', '2026-03-29 13:54:07'),
(74, 3, 'Green Peas', '2026-03-29 13:54:07'),
(75, 3, 'Golden Sweet Corn', '2026-03-29 13:54:07'),
(76, 3, 'Mushroom', '2026-03-29 13:54:07'),
(77, 3, 'Tomato Juice', '2026-03-29 13:54:07'),
(78, 3, 'Water Chestnut', '2026-03-29 13:54:07'),
(79, 4, 'Anonas', '2026-03-29 13:54:23'),
(80, 4, 'Atis', '2026-03-29 13:54:23'),
(81, 4, 'Dalanghita', '2026-03-29 13:54:23'),
(82, 4, 'Datiles', '2026-03-29 13:54:23'),
(83, 4, 'Guava', '2026-03-29 13:54:23'),
(84, 4, 'Guyabano', '2026-03-29 13:54:23'),
(85, 4, 'Kamachile', '2026-03-29 13:54:23'),
(86, 4, 'Mango, green', '2026-03-29 13:54:23'),
(87, 4, 'Mango, ripe', '2026-03-29 13:54:23'),
(88, 4, 'Papaya, ripe', '2026-03-29 13:54:23'),
(89, 4, 'Strawberry', '2026-03-29 13:54:23'),
(90, 4, 'Suha', '2026-03-29 13:54:23'),
(91, 4, 'Tiesa', '2026-03-29 13:54:23'),
(92, 4, 'Apple', '2026-03-29 13:54:23'),
(93, 4, 'Banana', '2026-03-29 13:54:23'),
(94, 4, 'Chico', '2026-03-29 13:54:23'),
(95, 4, 'Duhat', '2026-03-29 13:54:23'),
(96, 4, 'Durian', '2026-03-29 13:54:23'),
(97, 4, 'Grapes', '2026-03-29 13:54:23'),
(98, 4, 'Jackfruit, ripe', '2026-03-29 13:54:23'),
(99, 4, 'Lanzones', '2026-03-29 13:54:23'),
(100, 4, 'Lychees', '2026-03-29 13:54:23'),
(101, 4, 'Makopa', '2026-03-29 13:54:23'),
(102, 4, 'Melon', '2026-03-29 13:54:23'),
(103, 4, 'Pear', '2026-03-29 13:54:23'),
(104, 4, 'Pineapple', '2026-03-29 13:54:23'),
(105, 4, 'Rambutan', '2026-03-29 13:54:23'),
(106, 4, 'Santol', '2026-03-29 13:54:23'),
(107, 4, 'Siniguelas', '2026-03-29 13:54:23'),
(108, 4, 'Star Apple', '2026-03-29 13:54:23'),
(109, 4, 'Watermelon', '2026-03-29 13:54:23'),
(110, 4, 'Prunes', '2026-03-29 13:54:23'),
(111, 4, 'Buko Water', '2026-03-29 13:54:23'),
(112, 4, 'Buko Meat', '2026-03-29 13:54:23'),
(113, 4, 'Mangosteen', '2026-03-29 13:54:23'),
(114, 4, 'Tamarind, ripe', '2026-03-29 13:54:23'),
(115, 5, 'Lean Meats (beef, carabeef, chicken, pork)', '2026-03-29 13:54:37'),
(116, 5, 'Chicken Leg', '2026-03-29 13:54:37'),
(117, 5, 'Chicken, breast', '2026-03-29 13:54:37'),
(118, 5, 'Liver, blood, gizzard, heart, lungs, small intestine, tripe', '2026-03-29 13:54:37'),
(119, 5, 'Beef (flank, brisket plate, chuck)', '2026-03-29 13:54:37'),
(120, 5, 'Pork, pata (leg)', '2026-03-29 13:54:37'),
(121, 5, 'Brain (beef, pork, carabeef)', '2026-03-29 13:54:37'),
(122, 5, 'Pork, tenderloin', '2026-03-29 13:54:37'),
(123, 5, 'Beef Tongue', '2026-03-29 13:54:37'),
(124, 5, 'Tocino, lean, no sugar', '2026-03-29 13:54:37'),
(125, 5, 'Corned Beef', '2026-03-29 13:54:37'),
(126, 5, 'Frankfurters', '2026-03-29 13:54:37'),
(127, 5, 'Vienna Sausage', '2026-03-29 13:54:37'),
(128, 5, 'Hamburger', '2026-03-29 13:54:37'),
(129, 5, 'Hotdog', '2026-03-29 13:54:37'),
(130, 5, 'Longganisa', '2026-03-29 13:54:37'),
(131, 5, 'Salami', '2026-03-29 13:54:37'),
(132, 6, 'Fish', '2026-03-29 13:54:51'),
(133, 6, 'Alamang', '2026-03-29 13:54:51'),
(134, 6, 'Alimango, alimasag', '2026-03-29 13:54:51'),
(135, 6, 'Lobster', '2026-03-29 13:54:51'),
(136, 6, 'Shrimps', '2026-03-29 13:54:51'),
(137, 6, 'Prawn', '2026-03-29 13:54:51'),
(138, 6, 'Squid', '2026-03-29 13:54:51'),
(139, 6, 'Halaan', '2026-03-29 13:54:51'),
(140, 6, 'Kuhol', '2026-03-29 13:54:51'),
(141, 6, 'Dried Fish', '2026-03-29 13:54:51'),
(142, 6, 'Fishball', '2026-03-29 13:54:51'),
(143, 6, 'Tinapa, bangus', '2026-03-29 13:54:51'),
(144, 6, 'Tuyo: sapsoy, tunsoy', '2026-03-29 13:54:51'),
(145, 6, 'Dried Pusit', '2026-03-29 13:54:51'),
(146, 6, 'Salmon', '2026-03-29 13:54:51'),
(147, 6, 'Tuna in brine/water', '2026-03-29 13:54:51'),
(148, 6, 'Sardines, canned', '2026-03-29 13:54:51'),
(149, 6, 'Tuna sardines', '2026-03-29 13:54:51'),
(150, 7, 'Chicken Egg', '2026-03-29 13:55:03'),
(151, 7, 'Quail Egg', '2026-03-29 13:55:03'),
(152, 7, 'Balut', '2026-03-29 13:55:03'),
(153, 7, 'Penoy', '2026-03-29 13:55:03'),
(154, 8, 'Evaporated', '2026-03-29 13:55:13'),
(155, 8, 'Fresh Milk', '2026-03-29 13:55:13'),
(156, 8, 'Powdered Milk', '2026-03-29 13:55:13'),
(157, 8, 'Light LF milk', '2026-03-29 13:55:13'),
(158, 8, 'Buttermilk Liquid', '2026-03-29 13:55:13'),
(159, 8, 'Buttermilk Powdered', '2026-03-29 13:55:13'),
(160, 8, 'Long life SM', '2026-03-29 13:55:13'),
(161, 8, 'Yogurt', '2026-03-29 13:55:13'),
(162, 8, 'Cottage Cheese', '2026-03-29 13:55:13'),
(163, 8, 'Cheddar Cheese', '2026-03-29 13:55:13'),
(164, 8, 'Cheese, filled', '2026-03-29 13:55:13'),
(165, 9, 'Soybean (utaw)', '2026-03-29 13:55:24'),
(166, 9, 'Tofu', '2026-03-29 13:55:24'),
(167, 9, 'Tokwa', '2026-03-29 13:55:24'),
(168, 9, 'Peanut, roasted', '2026-03-29 13:55:24'),
(169, 9, 'Peanut Butter', '2026-03-29 13:55:24'),
(170, 10, 'Bacon', '2026-03-29 13:55:35'),
(171, 10, 'Butter', '2026-03-29 13:55:35'),
(172, 10, 'Margarine', '2026-03-29 13:55:35'),
(173, 10, 'Mayonnaise', '2026-03-29 13:55:35'),
(174, 10, 'Coconut Cream', '2026-03-29 13:55:35'),
(175, 10, 'Cream Cheese', '2026-03-29 13:55:35'),
(176, 10, 'Sandwich Spread', '2026-03-29 13:55:35'),
(177, 10, 'Oil (Corn, canola, soybean, sunflower, sesame)', '2026-03-29 13:55:35'),
(178, 10, 'Avocado', '2026-03-29 13:55:35'),
(179, 10, 'Peanut oil', '2026-03-29 13:55:35'),
(180, 10, 'Olive Oil', '2026-03-29 13:55:35'),
(181, 11, 'Sugar', '2026-03-29 13:55:52'),
(182, 11, 'Candy', '2026-03-29 13:55:52'),
(183, 11, 'Chocolate', '2026-03-29 13:55:52');

-- --------------------------------------------------------

--
-- Table structure for table `growth_hfa`
--

CREATE TABLE `growth_hfa` (
  `id` int(11) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `age_months` int(11) NOT NULL,
  `severely_stunted_max` decimal(5,2) NOT NULL,
  `stunted_from` decimal(5,2) NOT NULL,
  `stunted_to` decimal(5,2) NOT NULL,
  `normal_from` decimal(5,2) NOT NULL,
  `normal_to` decimal(5,2) NOT NULL,
  `tall_min` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `growth_hfa`
--

INSERT INTO `growth_hfa` (`id`, `sex`, `age_months`, `severely_stunted_max`, `stunted_from`, `stunted_to`, `normal_from`, `normal_to`, `tall_min`) VALUES
(19, 'Male', 0, 44.10, 44.20, 46.00, 46.10, 53.70, 53.80),
(20, 'Male', 1, 48.80, 48.90, 50.70, 50.80, 58.60, 58.70),
(21, 'Male', 2, 52.30, 52.40, 54.30, 54.40, 62.40, 62.50),
(22, 'Male', 3, 55.20, 55.30, 57.20, 57.30, 65.50, 65.60),
(23, 'Male', 4, 57.50, 57.60, 59.60, 59.70, 68.00, 68.10),
(24, 'Male', 5, 59.50, 59.60, 61.60, 61.70, 70.10, 70.20),
(25, 'Male', 6, 61.10, 61.20, 63.20, 63.30, 71.90, 72.00),
(26, 'Male', 7, 62.60, 62.70, 64.70, 64.80, 73.50, 73.60),
(27, 'Male', 8, 63.90, 64.00, 66.10, 66.20, 75.00, 75.10),
(28, 'Male', 9, 65.10, 65.20, 67.40, 67.50, 76.50, 76.60),
(29, 'Male', 10, 66.30, 66.40, 68.60, 68.70, 77.90, 78.00),
(30, 'Male', 11, 67.50, 67.60, 69.80, 69.90, 79.20, 79.30),
(31, 'Male', 12, 68.50, 68.60, 70.90, 71.00, 80.50, 80.60),
(32, 'Male', 13, 69.50, 69.60, 72.00, 72.10, 81.80, 81.90),
(33, 'Male', 14, 70.50, 70.60, 73.00, 73.10, 83.00, 83.10),
(34, 'Male', 15, 71.50, 71.60, 74.00, 74.10, 84.20, 84.30),
(35, 'Male', 16, 72.40, 72.50, 74.90, 75.00, 85.40, 85.50),
(36, 'Male', 17, 73.20, 73.30, 75.90, 76.00, 86.50, 86.60),
(37, 'Male', 18, 74.10, 74.20, 76.80, 76.90, 87.70, 87.80),
(38, 'Male', 19, 74.90, 75.00, 77.60, 77.70, 88.80, 88.90),
(39, 'Male', 20, 75.70, 75.80, 78.50, 78.60, 89.80, 89.90),
(40, 'Male', 21, 76.40, 76.50, 79.30, 79.40, 90.90, 91.00),
(41, 'Male', 22, 77.10, 77.20, 80.10, 80.20, 91.90, 92.00),
(42, 'Male', 23, 77.90, 78.00, 80.90, 81.00, 92.90, 93.00),
(43, 'Male', 24, 77.90, 78.00, 80.90, 81.00, 93.20, 93.30),
(44, 'Male', 25, 78.50, 78.60, 81.60, 81.70, 94.20, 94.30),
(45, 'Male', 26, 79.20, 79.30, 82.40, 82.50, 95.20, 95.30),
(46, 'Male', 27, 79.80, 79.90, 83.00, 83.10, 96.10, 96.20),
(47, 'Male', 28, 80.40, 80.50, 83.70, 83.80, 97.00, 97.10),
(48, 'Male', 29, 81.00, 81.10, 84.40, 84.50, 97.90, 98.00),
(49, 'Male', 30, 81.60, 81.70, 85.00, 85.10, 98.70, 98.80),
(50, 'Male', 31, 82.20, 82.30, 85.60, 85.70, 99.60, 99.70),
(51, 'Male', 32, 82.70, 82.80, 86.30, 86.40, 100.40, 100.50),
(52, 'Male', 33, 83.30, 83.40, 86.80, 86.90, 101.20, 101.30),
(53, 'Male', 34, 83.80, 83.90, 87.40, 87.50, 102.00, 102.10),
(54, 'Male', 35, 84.30, 84.40, 88.00, 88.10, 102.70, 102.80),
(55, 'Male', 36, 84.90, 85.00, 88.60, 88.70, 103.50, 103.60),
(56, 'Male', 37, 85.40, 85.50, 89.10, 89.20, 104.20, 104.30),
(57, 'Male', 38, 85.90, 86.00, 89.70, 89.80, 105.00, 105.10),
(58, 'Male', 39, 86.40, 86.50, 90.20, 90.30, 105.70, 105.80),
(59, 'Male', 40, 86.90, 87.00, 90.80, 90.90, 106.40, 106.50),
(60, 'Male', 41, 87.40, 87.50, 91.30, 91.40, 107.10, 107.20),
(61, 'Male', 42, 87.90, 88.00, 91.80, 91.90, 107.80, 107.90),
(62, 'Male', 43, 88.30, 88.40, 92.30, 92.40, 108.50, 108.60),
(63, 'Male', 44, 88.80, 88.90, 92.90, 93.00, 109.10, 109.20),
(64, 'Male', 45, 89.30, 89.40, 93.40, 93.50, 109.80, 109.90),
(65, 'Male', 46, 89.70, 89.80, 93.90, 94.00, 110.40, 110.50),
(66, 'Male', 47, 90.20, 90.30, 94.30, 94.40, 111.10, 111.20),
(67, 'Male', 48, 90.60, 90.70, 94.80, 94.90, 111.70, 111.80),
(68, 'Male', 49, 91.10, 91.20, 95.30, 95.40, 112.40, 112.50),
(69, 'Male', 50, 91.50, 91.60, 95.80, 95.90, 113.00, 113.10),
(70, 'Male', 51, 92.00, 92.10, 96.30, 96.40, 113.60, 113.70),
(71, 'Male', 52, 92.40, 92.50, 96.80, 96.90, 114.20, 114.30),
(72, 'Male', 53, 92.90, 93.00, 97.30, 97.40, 114.90, 115.00),
(73, 'Male', 54, 93.30, 93.40, 97.70, 97.80, 115.50, 115.60),
(74, 'Male', 55, 93.80, 93.90, 98.20, 98.30, 116.10, 116.20),
(75, 'Male', 56, 94.20, 94.30, 98.70, 98.80, 116.70, 116.80),
(76, 'Male', 57, 94.60, 94.70, 99.20, 99.30, 117.40, 117.50),
(77, 'Male', 58, 95.10, 95.20, 99.60, 99.70, 118.00, 118.10),
(78, 'Male', 59, 95.50, 95.60, 100.10, 100.20, 118.60, 118.70),
(79, 'Male', 60, 96.00, 96.10, 100.60, 100.70, 119.20, 119.30),
(80, 'Male', 61, 96.40, 96.50, 101.00, 101.10, 119.40, 119.50),
(81, 'Male', 62, 96.80, 96.90, 101.50, 101.60, 120.00, 120.10),
(82, 'Male', 63, 97.30, 97.40, 101.90, 102.00, 120.60, 120.70),
(83, 'Male', 64, 97.70, 97.80, 102.40, 102.50, 121.20, 121.30),
(84, 'Male', 65, 98.10, 98.20, 102.90, 103.00, 121.80, 121.90),
(85, 'Male', 66, 98.60, 98.70, 103.30, 103.40, 122.40, 122.50),
(86, 'Male', 67, 99.00, 99.10, 103.80, 103.90, 123.00, 123.10),
(87, 'Male', 68, 99.40, 99.50, 104.20, 104.30, 123.60, 123.70),
(88, 'Male', 69, 99.80, 99.90, 104.70, 104.80, 124.10, 124.20),
(89, 'Male', 70, 100.30, 100.40, 105.10, 105.20, 124.70, 124.80),
(90, 'Male', 71, 100.70, 100.80, 105.60, 105.70, 125.20, 125.30),
(163, 'Female', 0, 43.50, 43.60, 45.30, 45.40, 52.90, 53.00),
(164, 'Female', 1, 47.70, 47.80, 49.70, 49.80, 57.60, 57.70),
(165, 'Female', 2, 50.90, 51.00, 52.90, 53.00, 61.10, 61.20),
(166, 'Female', 3, 53.40, 53.50, 55.50, 55.60, 64.00, 64.10),
(167, 'Female', 4, 55.50, 55.60, 57.70, 57.80, 66.40, 66.50),
(168, 'Female', 5, 57.30, 57.40, 59.50, 59.60, 68.50, 68.60),
(169, 'Female', 6, 58.80, 58.90, 61.10, 61.20, 70.30, 70.40),
(170, 'Female', 7, 60.20, 60.30, 62.60, 62.70, 71.90, 72.00),
(171, 'Female', 8, 61.60, 61.70, 63.90, 64.00, 73.50, 73.60),
(172, 'Female', 9, 62.80, 62.90, 65.20, 65.30, 75.00, 75.10),
(173, 'Female', 10, 64.00, 64.10, 66.40, 66.50, 76.40, 76.50),
(174, 'Female', 11, 65.10, 65.20, 67.60, 67.70, 77.80, 77.90),
(175, 'Female', 12, 66.20, 66.30, 68.80, 68.90, 79.20, 79.30),
(176, 'Female', 13, 67.20, 67.30, 69.90, 70.00, 80.50, 80.60),
(177, 'Female', 14, 68.20, 68.30, 70.90, 71.00, 81.70, 81.80),
(178, 'Female', 15, 69.20, 69.30, 71.90, 72.00, 83.00, 83.10),
(179, 'Female', 16, 70.10, 70.20, 72.90, 73.00, 84.20, 84.30),
(180, 'Female', 17, 71.00, 71.10, 73.90, 74.00, 85.40, 85.50),
(181, 'Female', 18, 71.90, 72.00, 74.80, 74.90, 86.50, 86.60),
(182, 'Female', 19, 72.70, 72.80, 75.70, 75.80, 87.60, 87.70),
(183, 'Female', 20, 73.60, 73.70, 76.60, 76.70, 88.70, 88.80),
(184, 'Female', 21, 74.40, 74.50, 77.40, 77.50, 89.80, 89.90),
(185, 'Female', 22, 75.10, 75.20, 78.30, 78.40, 90.80, 90.90),
(186, 'Female', 23, 75.90, 76.00, 79.10, 79.20, 91.90, 92.00),
(187, 'Female', 24, 75.90, 76.00, 79.20, 79.30, 92.20, 92.30),
(188, 'Female', 25, 76.70, 76.80, 79.90, 80.00, 93.10, 93.20),
(189, 'Female', 26, 77.40, 77.50, 80.70, 80.80, 94.10, 94.20),
(190, 'Female', 27, 78.00, 78.10, 81.40, 81.50, 95.00, 95.10),
(191, 'Female', 28, 78.70, 78.80, 82.10, 82.20, 96.00, 96.10),
(192, 'Female', 29, 79.40, 79.50, 82.80, 82.90, 96.90, 97.00),
(193, 'Female', 30, 80.00, 80.10, 83.50, 83.60, 97.70, 97.80),
(194, 'Female', 31, 80.60, 80.70, 84.20, 84.30, 98.60, 98.70),
(195, 'Female', 32, 81.20, 81.30, 84.80, 84.90, 99.40, 99.50),
(196, 'Female', 33, 81.80, 81.90, 85.50, 85.60, 100.30, 100.40),
(197, 'Female', 34, 82.40, 82.50, 86.10, 86.20, 101.10, 101.20),
(198, 'Female', 35, 83.00, 83.10, 86.70, 86.80, 101.90, 102.00),
(199, 'Female', 36, 83.50, 83.60, 87.30, 87.40, 102.70, 102.80),
(200, 'Female', 37, 84.10, 84.20, 87.90, 88.00, 103.40, 103.50),
(201, 'Female', 38, 84.60, 84.70, 88.50, 88.60, 104.20, 104.30),
(202, 'Female', 39, 85.20, 85.30, 89.10, 89.20, 105.00, 105.10),
(203, 'Female', 40, 85.70, 85.80, 89.70, 89.80, 105.70, 105.80),
(204, 'Female', 41, 86.20, 86.30, 90.30, 90.40, 106.40, 106.50),
(205, 'Female', 42, 86.70, 86.80, 90.80, 90.90, 107.20, 107.30),
(206, 'Female', 43, 87.30, 87.40, 91.40, 91.50, 107.90, 108.00),
(207, 'Female', 44, 87.80, 87.90, 91.90, 92.00, 108.60, 108.70),
(208, 'Female', 45, 88.30, 88.40, 92.40, 92.50, 109.30, 109.40),
(209, 'Female', 46, 88.80, 88.90, 93.00, 93.10, 110.00, 110.10),
(210, 'Female', 47, 89.20, 89.30, 93.50, 93.60, 110.70, 110.80),
(211, 'Female', 48, 89.70, 89.80, 94.00, 94.10, 111.30, 111.40),
(212, 'Female', 49, 90.20, 90.30, 94.50, 94.60, 112.00, 112.10),
(213, 'Female', 50, 90.60, 90.70, 95.00, 95.10, 112.70, 112.80),
(214, 'Female', 51, 91.10, 91.20, 95.50, 95.60, 113.30, 113.40),
(215, 'Female', 52, 91.60, 91.70, 96.00, 96.10, 114.00, 114.10),
(216, 'Female', 53, 92.00, 92.10, 96.50, 96.60, 114.60, 114.70),
(217, 'Female', 54, 92.50, 92.60, 97.00, 97.10, 115.20, 115.30),
(218, 'Female', 55, 92.90, 93.00, 97.50, 97.60, 115.90, 116.00),
(219, 'Female', 56, 93.30, 93.40, 98.00, 98.10, 116.50, 116.60),
(220, 'Female', 57, 93.80, 93.90, 98.40, 98.50, 117.10, 117.20),
(221, 'Female', 58, 94.20, 94.30, 98.90, 99.00, 117.70, 117.80),
(222, 'Female', 59, 94.60, 94.70, 99.40, 99.50, 118.30, 118.40),
(223, 'Female', 60, 95.10, 95.20, 99.80, 99.90, 118.90, 119.00),
(224, 'Female', 61, 95.20, 95.30, 100.00, 100.10, 119.10, 119.20),
(225, 'Female', 62, 95.60, 95.70, 100.40, 100.50, 119.70, 119.80),
(226, 'Female', 63, 96.00, 96.10, 100.90, 101.00, 120.30, 120.40),
(227, 'Female', 64, 96.40, 96.50, 101.30, 101.40, 120.90, 121.00),
(228, 'Female', 65, 96.90, 97.00, 101.80, 101.90, 121.50, 121.60),
(229, 'Female', 66, 97.30, 97.40, 102.20, 102.30, 122.00, 122.10),
(230, 'Female', 67, 97.70, 97.80, 102.60, 102.70, 122.60, 122.70),
(231, 'Female', 68, 98.10, 98.20, 103.10, 103.20, 123.20, 123.30),
(232, 'Female', 69, 98.50, 98.60, 103.50, 103.60, 123.70, 123.80),
(233, 'Female', 70, 98.90, 99.00, 103.90, 104.00, 124.30, 124.40),
(234, 'Female', 71, 99.30, 99.40, 104.40, 104.50, 124.80, 124.90);

-- --------------------------------------------------------

--
-- Table structure for table `growth_wfa`
--

CREATE TABLE `growth_wfa` (
  `id` int(11) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `age_months` int(11) NOT NULL,
  `severely_underweight_max` decimal(5,2) NOT NULL,
  `underweight_from` decimal(5,2) NOT NULL,
  `underweight_to` decimal(5,2) NOT NULL,
  `normal_from` decimal(5,2) NOT NULL,
  `normal_to` decimal(5,2) NOT NULL,
  `overweight_min` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `growth_wfa`
--

INSERT INTO `growth_wfa` (`id`, `sex`, `age_months`, `severely_underweight_max`, `underweight_from`, `underweight_to`, `normal_from`, `normal_to`, `overweight_min`) VALUES
(1, 'Male', 0, 2.10, 2.20, 2.40, 2.50, 4.40, 4.50),
(2, 'Male', 1, 2.90, 3.00, 3.30, 3.40, 5.80, 5.90),
(3, 'Male', 2, 3.80, 3.90, 4.20, 4.30, 7.10, 7.20),
(4, 'Male', 3, 4.40, 4.50, 4.90, 5.00, 8.00, 8.10),
(5, 'Male', 4, 4.90, 5.00, 5.50, 5.60, 8.70, 8.80),
(6, 'Male', 5, 5.30, 5.40, 5.90, 6.00, 9.30, 9.40),
(7, 'Male', 6, 5.70, 5.80, 6.30, 6.40, 9.80, 9.90),
(8, 'Male', 7, 5.90, 6.00, 6.60, 6.70, 10.30, 10.40),
(9, 'Male', 8, 6.20, 6.30, 6.80, 6.90, 10.70, 10.80),
(10, 'Male', 9, 6.40, 6.50, 7.00, 7.10, 11.00, 11.10),
(11, 'Male', 10, 6.60, 6.70, 7.30, 7.40, 11.40, 11.50),
(12, 'Male', 11, 6.80, 6.90, 7.50, 7.60, 11.70, 11.80),
(13, 'Male', 12, 6.90, 7.00, 7.60, 7.70, 12.00, 12.10),
(14, 'Male', 13, 7.10, 7.20, 7.80, 7.90, 12.30, 12.40),
(15, 'Male', 14, 7.20, 7.30, 8.00, 8.10, 12.60, 12.70),
(16, 'Male', 15, 7.40, 7.50, 8.20, 8.30, 12.80, 12.90),
(17, 'Male', 16, 7.50, 7.60, 8.30, 8.40, 13.10, 13.20),
(18, 'Male', 17, 7.70, 7.80, 8.50, 8.60, 13.40, 13.50),
(19, 'Male', 18, 7.80, 7.90, 8.70, 8.80, 13.70, 13.80),
(20, 'Male', 19, 8.00, 8.10, 8.80, 8.90, 13.90, 14.00),
(21, 'Male', 20, 8.10, 8.20, 9.00, 9.10, 14.20, 14.30),
(22, 'Male', 21, 8.20, 8.30, 9.10, 9.20, 14.50, 14.60),
(23, 'Male', 22, 8.40, 8.50, 9.30, 9.40, 14.70, 14.80),
(24, 'Male', 23, 8.50, 8.60, 9.40, 9.50, 15.00, 15.10),
(25, 'Male', 24, 8.60, 8.70, 9.60, 9.70, 15.30, 15.40),
(48, 'Male', 25, 8.70, 8.80, 9.60, 9.70, 15.30, 15.40),
(49, 'Male', 26, 8.80, 8.90, 9.80, 9.90, 15.60, 15.70),
(50, 'Male', 27, 8.90, 9.00, 9.90, 10.00, 15.80, 15.90),
(51, 'Male', 28, 9.00, 9.10, 10.10, 10.20, 16.10, 16.20),
(52, 'Male', 29, 9.20, 9.30, 10.30, 10.40, 16.30, 16.40),
(53, 'Male', 30, 9.30, 9.40, 10.40, 10.50, 16.60, 16.70),
(54, 'Male', 31, 9.40, 9.50, 10.60, 10.70, 16.90, 17.00),
(55, 'Male', 32, 9.50, 9.60, 10.70, 10.80, 17.10, 17.20),
(56, 'Male', 33, 9.60, 9.70, 10.80, 10.90, 17.40, 17.50),
(57, 'Male', 34, 9.70, 9.80, 11.00, 11.10, 17.60, 17.70),
(58, 'Male', 35, 9.80, 9.90, 11.10, 11.20, 17.80, 17.90),
(59, 'Male', 36, 10.00, 10.10, 11.20, 11.30, 18.30, 18.40),
(60, 'Male', 37, 10.10, 10.20, 11.30, 11.40, 18.60, 18.70),
(61, 'Male', 38, 10.20, 10.30, 11.40, 11.50, 18.80, 18.90),
(62, 'Male', 39, 10.30, 10.40, 11.50, 11.60, 19.00, 19.10),
(63, 'Male', 40, 10.40, 10.50, 11.70, 11.80, 19.30, 19.40),
(64, 'Male', 41, 10.50, 10.60, 11.80, 11.90, 19.50, 19.60),
(65, 'Male', 42, 10.60, 10.70, 11.90, 12.00, 19.70, 19.80),
(66, 'Male', 43, 10.70, 10.80, 12.00, 12.10, 20.00, 20.10),
(67, 'Male', 44, 10.80, 10.90, 12.10, 12.20, 20.20, 20.30),
(68, 'Male', 45, 10.90, 11.00, 12.30, 12.40, 20.50, 20.60),
(69, 'Male', 46, 11.00, 11.10, 12.40, 12.50, 20.70, 20.80),
(70, 'Male', 47, 11.10, 11.20, 12.50, 12.60, 20.90, 21.00),
(71, 'Male', 48, 11.20, 11.30, 12.60, 12.70, 21.20, 21.30),
(72, 'Male', 49, 11.30, 11.40, 12.70, 12.80, 21.40, 21.50),
(73, 'Male', 50, 11.40, 11.50, 12.80, 12.90, 21.70, 21.80),
(74, 'Male', 51, 11.50, 11.60, 13.00, 13.10, 21.90, 22.00),
(75, 'Male', 52, 11.60, 11.70, 13.10, 13.20, 22.20, 22.30),
(76, 'Male', 53, 11.70, 11.80, 13.20, 13.30, 22.40, 22.50),
(77, 'Male', 54, 11.80, 11.90, 13.30, 13.40, 22.70, 22.80),
(78, 'Male', 55, 11.90, 12.00, 13.40, 13.50, 22.90, 23.00),
(79, 'Male', 56, 12.00, 12.10, 13.50, 13.60, 23.20, 23.30),
(80, 'Male', 57, 12.10, 12.20, 13.60, 13.70, 23.40, 23.50),
(81, 'Male', 58, 12.20, 12.30, 13.70, 13.80, 23.70, 23.80),
(82, 'Male', 59, 12.30, 12.40, 13.90, 14.00, 23.90, 24.00),
(83, 'Male', 60, 12.40, 12.50, 14.00, 14.10, 24.20, 24.30),
(84, 'Male', 61, 12.70, 12.80, 14.30, 14.40, 24.30, 24.40),
(85, 'Male', 62, 12.80, 12.90, 14.40, 14.50, 24.40, 24.50),
(86, 'Male', 63, 13.00, 13.10, 14.50, 14.60, 24.70, 24.80),
(87, 'Male', 64, 13.10, 13.20, 14.70, 14.80, 24.90, 25.00),
(88, 'Male', 65, 13.20, 13.30, 14.80, 14.90, 25.20, 25.30),
(89, 'Male', 66, 13.30, 13.40, 14.90, 15.00, 25.50, 25.60),
(90, 'Male', 67, 13.40, 13.50, 15.10, 15.20, 25.70, 25.80),
(91, 'Male', 68, 13.60, 13.70, 15.20, 15.30, 26.00, 26.10),
(92, 'Male', 69, 13.70, 13.80, 15.30, 15.40, 26.30, 26.40),
(93, 'Male', 70, 13.80, 13.90, 15.50, 15.60, 26.60, 26.70),
(94, 'Male', 71, 13.90, 14.00, 15.60, 15.70, 26.80, 26.90),
(95, 'Female', 0, 2.00, 2.10, 2.30, 2.40, 4.20, 4.30),
(96, 'Female', 1, 2.70, 2.80, 3.10, 3.20, 5.50, 5.60),
(97, 'Female', 2, 3.40, 3.50, 3.80, 3.90, 6.60, 6.70),
(98, 'Female', 3, 4.00, 4.10, 4.40, 4.50, 7.50, 7.60),
(99, 'Female', 4, 4.40, 4.50, 4.90, 5.00, 8.20, 8.30),
(100, 'Female', 5, 4.80, 4.90, 5.30, 5.40, 8.80, 8.90),
(101, 'Female', 6, 5.10, 5.20, 5.60, 5.70, 9.30, 9.40),
(102, 'Female', 7, 5.30, 5.40, 5.90, 6.00, 9.80, 9.90),
(103, 'Female', 8, 5.60, 5.70, 6.20, 6.30, 10.20, 10.30),
(104, 'Female', 9, 5.80, 5.90, 6.40, 6.50, 10.50, 10.60),
(105, 'Female', 10, 5.90, 6.00, 6.60, 6.70, 10.90, 11.00),
(106, 'Female', 11, 6.10, 6.20, 6.80, 6.90, 11.20, 11.30),
(107, 'Female', 12, 6.30, 6.40, 6.90, 7.00, 11.50, 11.60),
(108, 'Female', 13, 6.40, 6.50, 7.10, 7.20, 11.80, 11.90),
(109, 'Female', 14, 6.60, 6.70, 7.30, 7.40, 12.10, 12.20),
(110, 'Female', 15, 6.70, 6.80, 7.50, 7.60, 12.40, 12.50),
(111, 'Female', 16, 6.90, 7.00, 7.60, 7.70, 12.60, 12.70),
(112, 'Female', 17, 7.00, 7.10, 7.80, 7.90, 12.90, 13.00),
(113, 'Female', 18, 7.20, 7.30, 8.00, 8.10, 13.20, 13.30),
(114, 'Female', 19, 7.30, 7.40, 8.10, 8.20, 13.50, 13.60),
(115, 'Female', 20, 7.50, 7.60, 8.30, 8.40, 13.70, 13.80),
(116, 'Female', 21, 7.60, 7.70, 8.50, 8.60, 14.00, 14.10),
(117, 'Female', 22, 7.80, 7.90, 8.60, 8.70, 14.30, 14.40),
(118, 'Female', 23, 7.90, 8.00, 8.80, 8.90, 14.60, 14.70),
(119, 'Female', 24, 8.10, 8.20, 8.90, 9.00, 14.80, 14.90),
(120, 'Female', 25, 8.20, 8.30, 9.10, 9.20, 15.10, 15.20),
(121, 'Female', 26, 8.40, 8.50, 9.30, 9.40, 15.40, 15.50),
(122, 'Female', 27, 8.50, 8.60, 9.40, 9.50, 15.70, 15.80),
(123, 'Female', 28, 8.60, 8.70, 9.60, 9.70, 16.00, 16.10),
(124, 'Female', 29, 8.80, 8.90, 9.70, 9.80, 16.20, 16.30),
(125, 'Female', 30, 8.90, 9.00, 9.90, 10.00, 16.50, 16.60),
(126, 'Female', 31, 9.00, 9.10, 10.00, 10.10, 16.80, 16.90),
(127, 'Female', 32, 9.10, 9.20, 10.20, 10.30, 17.10, 17.20),
(128, 'Female', 33, 9.30, 9.40, 10.30, 10.40, 17.30, 17.40),
(129, 'Female', 34, 9.40, 9.50, 10.40, 10.50, 17.60, 17.70),
(130, 'Female', 35, 9.50, 9.60, 10.60, 10.70, 17.90, 18.00),
(131, 'Female', 36, 9.60, 9.70, 10.70, 10.80, 18.10, 18.20),
(132, 'Female', 37, 9.70, 9.80, 10.80, 10.90, 18.40, 18.50),
(133, 'Female', 38, 9.80, 9.90, 11.00, 11.10, 18.70, 18.40),
(134, 'Female', 39, 9.90, 10.00, 11.10, 11.20, 19.00, 19.10),
(135, 'Female', 40, 10.10, 10.20, 11.20, 11.30, 19.20, 19.30),
(136, 'Female', 41, 10.20, 10.30, 11.40, 11.50, 19.50, 19.60),
(137, 'Female', 42, 10.30, 10.40, 11.50, 11.60, 19.80, 19.90),
(138, 'Female', 43, 10.40, 10.50, 11.60, 11.70, 20.10, 20.20),
(139, 'Female', 44, 10.50, 10.60, 11.70, 11.80, 20.40, 20.50),
(140, 'Female', 45, 10.60, 10.70, 11.90, 12.00, 20.70, 20.80),
(141, 'Female', 46, 10.70, 10.80, 12.00, 12.10, 20.90, 21.00),
(142, 'Female', 47, 10.80, 10.90, 12.10, 12.20, 21.20, 21.30),
(143, 'Female', 48, 10.90, 11.00, 12.20, 12.30, 21.50, 21.60),
(144, 'Female', 49, 11.00, 11.10, 12.30, 12.40, 21.80, 21.90),
(145, 'Female', 50, 11.10, 11.20, 12.40, 12.50, 22.10, 22.20),
(146, 'Female', 51, 11.20, 11.30, 12.60, 12.70, 22.40, 22.50),
(147, 'Female', 52, 11.30, 11.40, 12.70, 12.80, 22.60, 22.70),
(148, 'Female', 53, 11.40, 11.50, 12.80, 12.90, 22.90, 23.00),
(149, 'Female', 54, 11.50, 11.60, 12.90, 13.00, 23.20, 23.30),
(150, 'Female', 55, 11.60, 11.70, 13.10, 13.20, 23.50, 23.60),
(151, 'Female', 56, 11.70, 11.80, 13.20, 13.30, 23.80, 23.90),
(152, 'Female', 57, 11.80, 11.90, 13.30, 13.40, 24.10, 24.20),
(153, 'Female', 58, 11.90, 12.00, 13.40, 13.50, 24.40, 24.50),
(154, 'Female', 59, 12.00, 12.10, 13.50, 13.60, 24.60, 24.70),
(155, 'Female', 60, 12.10, 12.20, 13.60, 13.70, 24.70, 24.80),
(156, 'Female', 61, 12.40, 12.50, 13.90, 14.00, 24.80, 24.90),
(157, 'Female', 62, 12.50, 12.60, 14.00, 14.10, 25.10, 25.20),
(158, 'Female', 63, 12.60, 12.70, 14.10, 14.20, 25.40, 25.50),
(159, 'Female', 64, 12.70, 12.80, 14.20, 14.30, 25.60, 25.70),
(160, 'Female', 65, 12.80, 12.90, 14.30, 14.40, 25.90, 26.00),
(161, 'Female', 66, 12.90, 13.00, 14.50, 14.60, 26.20, 26.30),
(162, 'Female', 67, 13.00, 13.10, 14.60, 14.70, 26.50, 26.60),
(163, 'Female', 68, 13.10, 13.20, 14.70, 14.80, 26.70, 26.80),
(164, 'Female', 69, 13.20, 13.30, 14.80, 14.90, 27.00, 27.10),
(165, 'Female', 70, 13.30, 13.40, 14.90, 15.00, 27.30, 27.40),
(166, 'Female', 71, 13.40, 13.50, 15.10, 15.20, 27.60, 27.70);

-- --------------------------------------------------------

--
-- Table structure for table `growth_wflh`
--

CREATE TABLE `growth_wflh` (
  `id` int(11) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `height_cm` decimal(5,1) NOT NULL,
  `severely_wasted_max` decimal(5,2) NOT NULL,
  `wasted_from` decimal(5,2) NOT NULL,
  `wasted_to` decimal(5,2) NOT NULL,
  `normal_from` decimal(5,2) NOT NULL,
  `normal_to` decimal(5,2) NOT NULL,
  `overweight_from` decimal(5,2) NOT NULL,
  `overweight_to` decimal(5,2) NOT NULL,
  `obese_min` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `growth_wflh`
--

INSERT INTO `growth_wflh` (`id`, `sex`, `height_cm`, `severely_wasted_max`, `wasted_from`, `wasted_to`, `normal_from`, `normal_to`, `overweight_from`, `overweight_to`, `obese_min`) VALUES
(6, 'Male', 65.0, 5.80, 5.90, 6.20, 6.30, 8.80, 8.90, 9.60, 9.70),
(7, 'Male', 65.5, 5.90, 6.00, 6.30, 6.40, 8.90, 9.00, 9.80, 9.90),
(8, 'Male', 66.0, 6.00, 6.10, 6.40, 6.50, 9.10, 9.20, 9.90, 10.00),
(9, 'Male', 66.5, 6.00, 6.10, 6.50, 6.60, 9.20, 9.30, 10.10, 10.20),
(10, 'Male', 67.0, 6.10, 6.20, 6.60, 6.70, 9.40, 9.50, 10.20, 10.30),
(11, 'Male', 67.5, 6.20, 6.30, 6.70, 6.80, 9.50, 9.60, 10.40, 10.50),
(12, 'Male', 68.0, 6.30, 6.40, 6.80, 6.90, 9.60, 9.70, 10.50, 10.60),
(13, 'Male', 68.5, 6.40, 6.50, 6.90, 7.00, 9.80, 9.90, 10.70, 10.80),
(14, 'Male', 69.0, 6.50, 6.60, 7.00, 7.10, 9.90, 10.00, 10.80, 10.90),
(15, 'Male', 69.5, 6.60, 6.70, 7.10, 7.20, 10.00, 10.10, 11.00, 11.10),
(16, 'Male', 70.0, 6.70, 6.80, 7.20, 7.30, 10.20, 10.30, 11.10, 11.20),
(17, 'Male', 70.5, 6.80, 6.90, 7.30, 7.40, 10.30, 10.40, 11.30, 11.40),
(18, 'Male', 71.0, 6.80, 6.90, 7.40, 7.50, 10.40, 10.50, 11.40, 11.50),
(19, 'Male', 71.5, 6.90, 7.00, 7.50, 7.60, 10.60, 10.70, 11.60, 11.70),
(20, 'Male', 72.0, 7.00, 7.10, 7.60, 7.70, 10.70, 10.80, 11.70, 11.80),
(21, 'Male', 72.5, 7.10, 7.20, 7.70, 7.80, 10.80, 10.90, 11.80, 11.90),
(22, 'Male', 73.0, 7.20, 7.30, 7.80, 7.90, 11.00, 11.10, 12.00, 12.10),
(23, 'Male', 73.5, 7.30, 7.40, 7.80, 7.90, 11.10, 11.20, 12.10, 12.20),
(24, 'Male', 74.0, 7.30, 7.40, 7.90, 8.00, 11.20, 11.30, 12.20, 12.30),
(25, 'Male', 74.5, 7.40, 7.50, 8.00, 8.10, 11.30, 11.40, 12.40, 12.50),
(26, 'Male', 75.0, 7.50, 7.60, 8.10, 8.20, 11.40, 11.50, 12.50, 12.60),
(27, 'Male', 75.5, 7.60, 7.70, 8.20, 8.30, 11.60, 11.70, 12.60, 12.70),
(28, 'Male', 76.0, 7.60, 7.70, 8.30, 8.40, 11.70, 11.80, 12.80, 12.90),
(29, 'Male', 76.5, 7.70, 7.80, 8.40, 8.50, 11.80, 11.90, 12.90, 13.00),
(30, 'Male', 77.0, 7.80, 7.90, 8.40, 8.50, 11.90, 12.00, 13.00, 13.10),
(31, 'Male', 77.5, 7.90, 8.00, 8.50, 8.60, 12.00, 12.10, 13.10, 13.20),
(32, 'Male', 78.0, 7.90, 8.00, 8.60, 8.70, 12.10, 12.20, 13.30, 13.40),
(33, 'Male', 78.5, 8.00, 8.10, 8.70, 8.80, 12.20, 12.30, 13.40, 13.50),
(34, 'Male', 79.0, 8.10, 8.20, 8.70, 8.80, 12.30, 12.40, 13.50, 13.60),
(35, 'Male', 79.5, 8.20, 8.30, 8.80, 8.90, 12.40, 12.50, 13.60, 13.70),
(36, 'Male', 80.0, 8.20, 8.30, 8.90, 9.00, 12.60, 12.70, 13.70, 13.80),
(37, 'Male', 80.5, 8.30, 8.40, 9.00, 9.10, 12.70, 12.80, 13.80, 13.90),
(38, 'Male', 81.0, 8.40, 8.50, 9.10, 9.20, 12.80, 12.90, 14.00, 14.10),
(39, 'Male', 81.5, 8.50, 8.60, 9.20, 9.30, 12.90, 13.00, 14.10, 14.20),
(40, 'Male', 82.0, 8.60, 8.70, 9.20, 9.30, 13.00, 13.10, 14.20, 14.30),
(41, 'Male', 82.5, 8.60, 8.70, 9.30, 9.40, 13.10, 13.20, 14.40, 14.50),
(42, 'Male', 83.0, 8.70, 8.80, 9.40, 9.50, 13.30, 13.40, 14.50, 14.60),
(43, 'Male', 83.5, 8.80, 8.90, 9.50, 9.60, 13.40, 13.50, 14.60, 14.70),
(44, 'Male', 84.0, 8.90, 9.00, 9.60, 9.70, 13.50, 13.60, 14.80, 14.90),
(45, 'Male', 84.5, 9.00, 9.10, 9.80, 9.90, 13.70, 13.80, 14.90, 15.00),
(46, 'Male', 85.0, 9.10, 9.20, 9.90, 10.00, 13.80, 13.90, 15.10, 15.20),
(47, 'Male', 85.5, 9.20, 9.30, 10.00, 10.10, 13.90, 14.00, 15.20, 15.30),
(48, 'Male', 86.0, 9.30, 9.40, 10.10, 10.20, 14.10, 14.20, 15.40, 15.50),
(49, 'Male', 86.5, 9.40, 9.50, 10.20, 10.30, 14.20, 14.30, 15.50, 15.60),
(50, 'Male', 87.0, 9.50, 9.60, 10.30, 10.40, 14.40, 14.50, 15.70, 15.80),
(51, 'Male', 87.5, 9.60, 9.70, 10.40, 10.50, 14.50, 14.60, 15.80, 15.90),
(52, 'Male', 88.0, 9.70, 9.80, 10.50, 10.60, 14.70, 14.80, 16.00, 16.10),
(53, 'Male', 88.5, 9.80, 9.90, 10.60, 10.70, 14.80, 14.90, 16.10, 16.20),
(54, 'Male', 89.0, 9.90, 10.00, 10.70, 10.80, 14.90, 15.00, 16.30, 16.40),
(55, 'Male', 89.5, 10.00, 10.10, 10.80, 10.90, 15.10, 15.20, 16.40, 16.50),
(56, 'Male', 90.0, 10.10, 10.20, 10.90, 11.00, 15.20, 15.30, 16.60, 16.70),
(57, 'Male', 90.5, 10.20, 10.30, 11.00, 11.10, 15.30, 15.40, 16.70, 16.80),
(58, 'Male', 91.0, 10.30, 10.40, 11.10, 11.20, 15.50, 15.60, 16.90, 17.00),
(59, 'Male', 91.5, 10.40, 10.50, 11.20, 11.30, 15.60, 15.70, 17.00, 17.10),
(60, 'Male', 92.0, 10.50, 10.60, 11.30, 11.40, 15.80, 15.90, 17.20, 17.30),
(61, 'Male', 92.5, 10.60, 10.70, 11.40, 11.50, 15.90, 16.00, 17.30, 17.40),
(62, 'Male', 93.0, 10.70, 10.80, 11.50, 11.60, 16.00, 16.10, 17.50, 17.60),
(63, 'Male', 93.5, 10.80, 10.90, 11.60, 11.70, 16.20, 16.30, 17.60, 17.70),
(64, 'Male', 94.0, 10.90, 11.00, 11.70, 11.80, 16.30, 16.40, 17.80, 17.90),
(65, 'Male', 94.5, 11.00, 11.10, 11.80, 11.90, 16.50, 16.60, 17.90, 18.00),
(66, 'Male', 95.0, 11.00, 11.10, 11.90, 12.00, 16.60, 16.70, 18.10, 18.20),
(67, 'Male', 95.5, 11.10, 11.20, 12.00, 12.10, 16.70, 16.80, 18.30, 18.40),
(68, 'Male', 96.0, 11.20, 11.30, 12.10, 12.20, 16.90, 17.00, 18.40, 18.50),
(69, 'Male', 96.5, 11.30, 11.40, 12.20, 12.30, 17.00, 17.10, 18.60, 18.70),
(70, 'Male', 97.0, 11.40, 11.50, 12.30, 12.40, 17.20, 17.30, 18.80, 18.90),
(71, 'Male', 97.5, 11.50, 11.60, 12.40, 12.50, 17.40, 17.50, 18.90, 19.00),
(72, 'Male', 98.0, 11.60, 11.70, 12.50, 12.60, 17.50, 17.60, 19.10, 19.20),
(73, 'Male', 98.5, 11.70, 11.80, 12.70, 12.80, 17.70, 17.80, 19.30, 19.40),
(74, 'Male', 99.0, 11.80, 11.90, 12.80, 12.90, 17.90, 18.00, 19.50, 19.60),
(75, 'Male', 99.5, 11.90, 12.00, 12.90, 13.00, 18.00, 18.10, 19.70, 19.80),
(76, 'Male', 100.0, 12.00, 12.10, 13.00, 13.10, 18.20, 18.30, 19.90, 20.00),
(77, 'Male', 100.5, 12.10, 12.20, 13.10, 13.20, 18.40, 18.50, 20.10, 20.20),
(78, 'Male', 101.0, 12.20, 12.30, 13.20, 13.30, 18.50, 18.60, 20.30, 20.40),
(79, 'Male', 101.5, 12.30, 12.40, 13.30, 13.40, 18.70, 18.80, 20.50, 20.60),
(80, 'Male', 102.0, 12.40, 12.50, 13.50, 13.60, 18.90, 19.00, 20.70, 20.80),
(81, 'Male', 102.5, 12.50, 12.60, 13.60, 13.70, 19.10, 19.20, 20.90, 21.00),
(82, 'Male', 103.0, 12.70, 12.80, 13.70, 13.80, 19.30, 19.40, 21.10, 21.20),
(83, 'Male', 103.5, 12.80, 12.90, 13.80, 13.90, 19.50, 19.60, 21.30, 21.40),
(84, 'Male', 104.0, 12.90, 13.00, 13.90, 14.00, 19.70, 19.80, 21.60, 21.70),
(85, 'Male', 104.5, 13.00, 13.10, 14.10, 14.20, 19.90, 20.00, 21.80, 21.90),
(86, 'Male', 105.0, 13.10, 13.20, 14.20, 14.30, 20.10, 20.20, 22.00, 22.10),
(87, 'Male', 105.5, 13.20, 13.30, 14.30, 14.40, 20.30, 20.40, 22.20, 22.30),
(88, 'Male', 106.0, 13.30, 13.40, 14.40, 14.50, 20.50, 20.60, 22.50, 22.60),
(89, 'Male', 106.5, 13.40, 13.50, 14.60, 14.70, 20.70, 20.80, 22.70, 22.80),
(90, 'Male', 107.0, 13.60, 13.70, 14.70, 14.80, 20.90, 21.00, 22.90, 23.00),
(91, 'Male', 107.5, 13.70, 13.80, 14.80, 14.90, 21.10, 21.20, 23.20, 23.30),
(92, 'Male', 108.0, 13.80, 13.90, 15.00, 15.10, 21.30, 21.40, 23.40, 23.50),
(93, 'Male', 108.5, 13.90, 14.00, 15.10, 15.20, 21.50, 21.60, 23.70, 23.80),
(94, 'Male', 109.0, 14.00, 14.10, 15.20, 15.30, 21.80, 21.90, 23.90, 24.00),
(95, 'Male', 109.5, 14.20, 14.30, 15.40, 15.50, 22.00, 22.10, 24.20, 24.30),
(96, 'Male', 110.0, 14.30, 14.40, 15.50, 15.60, 22.20, 22.30, 24.40, 24.50),
(97, 'Male', 110.5, 14.40, 14.50, 15.70, 15.80, 22.40, 22.50, 24.70, 24.80),
(98, 'Male', 111.0, 14.50, 14.60, 15.80, 15.90, 22.70, 22.80, 25.00, 25.10),
(99, 'Male', 111.5, 14.70, 14.80, 15.90, 16.00, 22.90, 23.00, 25.20, 25.30),
(100, 'Male', 112.0, 14.80, 14.90, 16.10, 16.20, 23.10, 23.20, 25.50, 25.60),
(101, 'Male', 112.5, 14.90, 15.00, 16.20, 16.30, 23.40, 23.50, 25.80, 25.90),
(102, 'Male', 113.0, 15.10, 15.20, 16.40, 16.50, 23.60, 23.70, 26.00, 26.10),
(103, 'Male', 113.5, 15.20, 15.30, 16.50, 16.60, 23.90, 24.00, 26.30, 26.40),
(104, 'Male', 114.0, 15.30, 15.40, 16.70, 16.80, 24.10, 24.20, 26.60, 26.70),
(105, 'Male', 114.5, 15.50, 15.60, 16.80, 16.90, 24.40, 24.50, 26.90, 27.00),
(106, 'Male', 115.0, 15.60, 15.70, 17.00, 17.10, 24.60, 24.70, 27.20, 27.30),
(107, 'Male', 115.5, 15.70, 15.80, 17.10, 17.20, 24.90, 25.00, 27.50, 27.60),
(108, 'Male', 116.0, 15.90, 16.00, 17.30, 17.40, 25.10, 25.20, 27.80, 27.90),
(109, 'Male', 116.5, 16.00, 16.10, 17.40, 17.50, 25.40, 25.50, 28.00, 28.10),
(110, 'Male', 117.0, 16.10, 16.20, 17.60, 17.70, 25.60, 25.70, 28.30, 28.40),
(111, 'Male', 117.5, 16.30, 16.40, 17.80, 17.90, 25.90, 26.00, 28.60, 28.70),
(112, 'Male', 118.0, 16.40, 16.50, 17.90, 18.00, 26.10, 26.20, 28.90, 29.00),
(113, 'Male', 118.5, 16.60, 16.70, 18.10, 18.20, 26.40, 26.50, 29.20, 29.30),
(114, 'Male', 119.0, 16.70, 16.80, 18.20, 18.30, 26.60, 26.70, 29.50, 29.60),
(115, 'Male', 119.5, 16.80, 16.90, 18.40, 18.50, 26.90, 27.00, 29.80, 29.90),
(116, 'Male', 120.0, 17.00, 17.10, 18.50, 18.60, 27.20, 27.30, 30.10, 30.20),
(228, 'Female', 65.0, 5.50, 5.60, 6.00, 6.10, 8.70, 8.80, 9.70, 9.80),
(229, 'Female', 65.5, 5.60, 5.70, 6.10, 6.20, 8.90, 9.00, 9.80, 9.90),
(230, 'Female', 66.0, 5.70, 5.80, 6.20, 6.30, 9.00, 9.10, 10.00, 10.10),
(231, 'Female', 66.5, 5.70, 5.80, 6.30, 6.40, 9.10, 9.20, 10.10, 10.20),
(232, 'Female', 67.0, 5.80, 5.90, 6.30, 6.40, 9.30, 9.40, 10.20, 10.30),
(233, 'Female', 67.5, 5.90, 6.00, 6.40, 6.50, 9.40, 9.50, 10.40, 10.50),
(234, 'Female', 68.0, 6.00, 6.10, 6.50, 6.60, 9.50, 9.60, 10.50, 10.60),
(235, 'Female', 68.5, 6.10, 6.20, 6.60, 6.70, 9.70, 9.80, 10.70, 10.80),
(236, 'Female', 69.0, 6.20, 6.30, 6.70, 6.80, 9.80, 9.90, 10.80, 10.90),
(237, 'Female', 69.5, 6.20, 6.30, 6.80, 6.90, 9.90, 10.00, 10.90, 11.00),
(238, 'Female', 70.0, 6.30, 6.40, 6.90, 7.00, 10.00, 10.10, 11.10, 11.20),
(239, 'Female', 70.5, 6.40, 6.50, 7.00, 7.10, 10.10, 10.20, 11.20, 11.30),
(240, 'Female', 71.0, 6.50, 6.60, 7.00, 7.10, 10.30, 10.40, 11.30, 11.40),
(241, 'Female', 71.5, 6.60, 6.70, 7.10, 7.20, 10.40, 10.50, 11.50, 11.60),
(242, 'Female', 72.0, 6.60, 6.70, 7.20, 7.30, 10.50, 10.60, 11.60, 11.70),
(243, 'Female', 72.5, 6.70, 6.80, 7.30, 7.40, 10.60, 10.70, 11.70, 11.80),
(244, 'Female', 73.0, 6.80, 6.90, 7.40, 7.50, 10.70, 10.80, 11.80, 11.90),
(245, 'Female', 73.5, 6.90, 7.00, 7.50, 7.60, 10.80, 10.90, 12.00, 12.10),
(246, 'Female', 74.0, 6.90, 7.00, 7.50, 7.60, 11.00, 11.10, 12.10, 12.20),
(247, 'Female', 74.5, 7.00, 7.10, 7.60, 7.70, 11.10, 11.20, 12.20, 12.30),
(248, 'Female', 75.0, 7.10, 7.20, 7.70, 7.80, 11.20, 11.30, 12.30, 12.40),
(249, 'Female', 75.5, 7.10, 7.20, 7.80, 7.90, 11.30, 11.40, 12.50, 12.60),
(250, 'Female', 76.0, 7.20, 7.30, 7.90, 8.00, 11.40, 11.50, 12.60, 12.70),
(251, 'Female', 76.5, 7.30, 7.40, 7.90, 8.00, 11.50, 11.60, 12.70, 12.80),
(252, 'Female', 77.0, 7.40, 7.50, 8.00, 8.10, 11.60, 11.70, 12.80, 12.90),
(253, 'Female', 77.5, 7.40, 7.50, 8.10, 8.20, 11.70, 11.80, 12.90, 13.00),
(254, 'Female', 78.0, 7.50, 7.60, 8.20, 8.30, 11.80, 11.90, 13.10, 13.20),
(255, 'Female', 78.5, 7.60, 7.70, 8.30, 8.40, 12.00, 12.10, 13.20, 13.30),
(256, 'Female', 79.0, 7.70, 7.80, 8.30, 8.40, 12.10, 12.20, 13.30, 13.40),
(257, 'Female', 79.5, 7.70, 7.80, 8.40, 8.50, 12.20, 12.30, 13.40, 13.50),
(258, 'Female', 80.0, 7.80, 7.90, 8.50, 8.60, 12.30, 12.40, 13.60, 13.70),
(259, 'Female', 80.5, 7.90, 8.00, 8.60, 8.70, 12.40, 12.50, 13.70, 13.80),
(260, 'Female', 81.0, 8.00, 8.10, 8.70, 8.80, 12.60, 12.70, 13.90, 14.00),
(261, 'Female', 81.5, 8.10, 8.20, 8.80, 8.90, 12.70, 12.80, 14.00, 14.10),
(262, 'Female', 82.0, 8.20, 8.30, 8.90, 9.00, 12.80, 12.90, 14.10, 14.20),
(263, 'Female', 82.5, 8.20, 8.30, 9.00, 9.10, 13.00, 13.10, 14.30, 14.40),
(264, 'Female', 83.0, 8.30, 8.40, 9.10, 9.20, 13.10, 13.20, 14.50, 14.60),
(265, 'Female', 83.5, 8.40, 8.50, 9.20, 9.30, 13.30, 13.40, 14.60, 14.70),
(266, 'Female', 84.0, 8.50, 8.60, 9.30, 9.40, 13.40, 13.50, 14.80, 14.90),
(267, 'Female', 84.5, 8.60, 8.70, 9.40, 9.50, 13.50, 13.60, 14.90, 15.00),
(268, 'Female', 85.0, 8.70, 8.80, 9.50, 9.60, 13.70, 13.80, 15.10, 15.20),
(269, 'Female', 85.5, 8.80, 8.90, 9.60, 9.70, 13.80, 13.90, 15.30, 15.40),
(270, 'Female', 86.0, 8.90, 9.00, 9.70, 9.80, 14.00, 14.10, 15.40, 15.50),
(271, 'Female', 86.5, 9.00, 9.10, 9.80, 9.90, 14.20, 14.30, 15.60, 15.70),
(272, 'Female', 87.0, 9.10, 9.20, 9.90, 10.00, 14.30, 14.40, 15.80, 15.90),
(273, 'Female', 87.5, 9.20, 9.30, 10.00, 10.10, 14.50, 14.60, 15.90, 16.00),
(274, 'Female', 88.0, 9.30, 9.40, 10.10, 10.20, 14.60, 14.70, 16.10, 16.20),
(275, 'Female', 88.5, 9.40, 9.50, 10.20, 10.30, 14.80, 14.90, 16.30, 16.40),
(276, 'Female', 89.0, 9.50, 9.60, 10.30, 10.40, 14.90, 15.00, 16.50, 16.60),
(277, 'Female', 89.5, 9.60, 9.70, 10.40, 10.50, 15.10, 15.20, 16.60, 16.70),
(278, 'Female', 90.0, 9.70, 9.80, 10.50, 10.60, 15.20, 15.30, 16.80, 16.90),
(279, 'Female', 90.5, 9.80, 9.90, 10.60, 10.70, 15.40, 15.50, 17.00, 17.10),
(280, 'Female', 91.0, 9.90, 10.00, 10.70, 10.80, 15.50, 15.60, 17.20, 17.30),
(281, 'Female', 91.5, 10.00, 10.10, 10.80, 10.90, 15.70, 15.80, 17.30, 17.40),
(282, 'Female', 92.0, 10.10, 10.20, 10.90, 11.00, 15.80, 15.90, 17.50, 17.60),
(283, 'Female', 92.5, 10.20, 10.30, 11.00, 11.10, 16.00, 16.10, 17.60, 17.70),
(284, 'Female', 93.0, 10.30, 10.40, 11.20, 11.30, 16.10, 16.20, 17.80, 17.90),
(285, 'Female', 93.5, 10.40, 10.50, 11.30, 11.40, 16.30, 16.40, 17.90, 18.00),
(286, 'Female', 94.0, 10.50, 10.60, 11.40, 11.50, 16.40, 16.50, 18.10, 18.20),
(287, 'Female', 94.5, 10.60, 10.70, 11.50, 11.60, 16.60, 16.70, 18.30, 18.40),
(288, 'Female', 95.0, 10.70, 10.80, 11.60, 11.70, 16.70, 16.80, 18.50, 18.60),
(289, 'Female', 95.5, 10.80, 10.90, 11.70, 11.80, 16.90, 17.00, 18.60, 18.70),
(290, 'Female', 96.0, 10.80, 10.90, 11.80, 11.90, 17.00, 17.10, 18.80, 18.90),
(291, 'Female', 96.5, 10.90, 11.00, 11.90, 12.00, 17.20, 17.30, 19.00, 19.10),
(292, 'Female', 97.0, 11.00, 11.10, 12.00, 12.10, 17.40, 17.50, 19.20, 19.30),
(293, 'Female', 97.5, 11.10, 11.20, 12.10, 12.20, 17.50, 17.60, 19.30, 19.40),
(294, 'Female', 98.0, 11.20, 11.30, 12.20, 12.30, 17.70, 17.80, 19.50, 19.60),
(295, 'Female', 98.5, 11.30, 11.40, 12.30, 12.40, 17.90, 18.00, 19.70, 19.80),
(296, 'Female', 99.0, 11.40, 11.50, 12.40, 12.50, 18.00, 18.10, 19.90, 20.00),
(297, 'Female', 99.5, 11.50, 11.60, 12.60, 12.70, 18.20, 18.30, 20.10, 20.20),
(298, 'Female', 100.0, 11.60, 11.70, 12.70, 12.80, 18.40, 18.50, 20.30, 20.40),
(299, 'Female', 100.5, 11.70, 11.80, 12.80, 12.90, 18.60, 18.70, 20.50, 20.60),
(300, 'Female', 101.0, 11.80, 11.90, 12.90, 13.00, 18.70, 18.80, 20.70, 20.80),
(301, 'Female', 101.5, 11.90, 12.00, 13.00, 13.10, 18.90, 19.00, 20.90, 21.00),
(302, 'Female', 102.0, 12.00, 12.10, 13.20, 13.30, 19.10, 19.20, 21.10, 21.20),
(303, 'Female', 102.5, 12.10, 12.20, 13.30, 13.40, 19.30, 19.40, 21.30, 21.40),
(304, 'Female', 103.0, 12.20, 12.30, 13.40, 13.50, 19.50, 19.60, 21.60, 21.70),
(305, 'Female', 103.5, 12.30, 12.40, 13.50, 13.60, 19.70, 19.80, 21.80, 21.90),
(306, 'Female', 104.0, 12.40, 12.50, 13.60, 13.70, 19.90, 20.00, 22.00, 22.10),
(307, 'Female', 104.5, 12.50, 12.60, 13.80, 13.90, 20.10, 20.20, 22.20, 22.30),
(308, 'Female', 105.0, 12.70, 12.80, 13.90, 14.00, 20.30, 20.40, 22.50, 22.60),
(309, 'Female', 105.5, 12.80, 12.90, 14.00, 14.10, 20.50, 20.60, 22.70, 22.80),
(310, 'Female', 106.0, 12.90, 13.00, 14.10, 14.20, 20.80, 20.90, 23.00, 23.10),
(311, 'Female', 106.5, 13.00, 13.10, 14.20, 14.30, 21.00, 21.10, 23.20, 23.30),
(312, 'Female', 107.0, 13.10, 13.20, 14.40, 14.50, 21.20, 21.30, 23.50, 23.60),
(313, 'Female', 107.5, 13.20, 13.30, 14.50, 14.60, 21.40, 21.50, 23.70, 23.80),
(314, 'Female', 108.0, 13.30, 13.40, 14.60, 14.70, 21.70, 21.80, 24.00, 24.10),
(315, 'Female', 108.5, 13.50, 13.60, 14.70, 14.80, 21.90, 22.00, 24.20, 24.30),
(316, 'Female', 109.0, 13.60, 13.70, 14.90, 15.00, 22.10, 22.20, 24.50, 24.60),
(317, 'Female', 109.5, 13.70, 13.80, 15.00, 15.10, 22.40, 22.50, 24.80, 24.90),
(318, 'Female', 110.0, 13.80, 13.90, 15.10, 15.20, 22.60, 22.70, 25.10, 25.20),
(319, 'Female', 110.5, 14.00, 14.10, 15.30, 15.40, 22.90, 23.00, 25.40, 25.50),
(320, 'Female', 111.0, 14.10, 14.20, 15.40, 15.50, 23.10, 23.20, 25.70, 25.80),
(321, 'Female', 111.5, 14.30, 14.40, 15.60, 15.70, 23.40, 23.50, 26.00, 26.10),
(322, 'Female', 112.0, 14.40, 14.50, 15.70, 15.80, 23.60, 23.70, 26.20, 26.30),
(323, 'Female', 112.5, 14.60, 14.70, 15.90, 16.00, 23.90, 24.00, 26.50, 26.60),
(324, 'Female', 113.0, 14.70, 14.80, 16.00, 16.10, 24.20, 24.30, 26.80, 26.90),
(325, 'Female', 113.5, 14.90, 15.00, 16.20, 16.30, 24.40, 24.50, 27.10, 27.20),
(326, 'Female', 114.0, 15.00, 15.10, 16.30, 16.40, 24.70, 24.80, 27.40, 27.50),
(327, 'Female', 114.5, 15.20, 15.30, 16.50, 16.60, 25.00, 25.10, 27.80, 27.90),
(328, 'Female', 115.0, 15.30, 15.40, 16.70, 16.80, 25.20, 25.30, 28.10, 28.20),
(329, 'Female', 115.5, 15.50, 15.60, 16.80, 16.90, 25.50, 25.60, 28.40, 28.50),
(330, 'Female', 116.0, 15.60, 15.70, 17.00, 17.10, 25.80, 25.90, 28.70, 28.80),
(331, 'Female', 116.5, 15.80, 15.90, 17.20, 17.30, 26.10, 26.20, 29.00, 29.10),
(332, 'Female', 117.0, 15.90, 16.00, 17.40, 17.50, 26.40, 26.50, 29.30, 29.40),
(333, 'Female', 117.5, 16.20, 16.30, 17.70, 17.80, 26.60, 26.70, 29.60, 29.70),
(334, 'Female', 118.0, 16.40, 16.50, 17.90, 18.00, 26.90, 27.00, 29.90, 30.00),
(335, 'Female', 118.5, 16.60, 16.70, 18.10, 18.20, 27.20, 27.30, 30.30, 30.40),
(336, 'Female', 119.0, 16.80, 16.90, 18.40, 18.50, 27.40, 27.50, 30.60, 30.70),
(337, 'Female', 119.5, 17.00, 17.10, 18.60, 18.70, 27.70, 27.80, 30.90, 31.00),
(338, 'Female', 120.0, 17.20, 17.30, 18.80, 18.90, 28.00, 28.10, 31.20, 31.30);

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `guardian_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `relationship_to_child` varchar(50) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `linked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardians`
--

INSERT INTO `guardians` (`guardian_id`, `child_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `relationship_to_child`, `contact_number`, `email`, `address`, `linked_at`, `created_at`, `updated_at`) VALUES
(1, 6, NULL, 'Maria', '', 'Reyex', 'Mother', '09123456789', 'maria.reyes@gmail.com', 'Brgy. Molino III, Bacoor City', NULL, '2026-03-27 10:47:53', '2026-03-27 10:47:53'),
(2, 4, NULL, 'Mia', '', 'Cruz', 'Mother', '09098847865', 'miacruz@gmail.com', 'Niog 1 Day Care Center – Aguinaldo Hwy, Bacoor', NULL, '2026-03-27 12:47:45', '2026-03-27 12:47:45'),
(3, 9, NULL, 'Ramon', '', 'Gutierrez', 'Father', '09181234566', 'ramon.g@gmail.com', 'Molino 5, Bacoor', NULL, '2026-03-31 15:15:40', '2026-03-31 15:15:40'),
(4, 10, NULL, 'Anna', '', 'Velasco', 'Mother', '09181234567', 'anna.velasco@gmail.com', 'Molino 5, Bacoor', NULL, '2026-03-31 15:16:47', '2026-03-31 15:16:47'),
(5, 11, NULL, 'Edgar', '', 'Soriano', 'Father', '09181234568', 'edgar.soriano@gmail.com', 'Molino 5, Bacoor', NULL, '2026-03-31 15:19:27', '2026-03-31 15:19:27'),
(6, 12, NULL, 'Sheila', '', 'Cabrera', 'Mother', '', '', 'Molino 5, Bacoor', NULL, '2026-03-31 15:20:49', '2026-03-31 15:20:49'),
(7, 13, NULL, 'Victor', '', 'Dominguez', 'Father', '09181234570', '', 'Molino 5, Bacoor', NULL, '2026-03-31 15:22:07', '2026-03-31 15:22:07'),
(8, 14, NULL, 'Liza', '', 'Delgado', 'Aunt', '09181234561', '', 'Molino 2, Bacoor', NULL, '2026-03-31 15:23:33', '2026-03-31 15:23:33'),
(9, 15, NULL, 'Joel', '', 'Navarro', 'Uncle', '09181234562', '', 'Molino 2, Bacoor', NULL, '2026-03-31 15:24:22', '2026-03-31 15:24:22'),
(10, 16, NULL, 'Grace', '', 'Pineda', 'Mother', '09181234563', '', 'Molino 2, Bacoor', NULL, '2026-03-31 15:25:16', '2026-03-31 15:25:16'),
(11, 17, NULL, 'Carlo', '', 'Alonzo', 'Father', '09181234564', '', 'Molino 2, Bacoor', NULL, '2026-03-31 15:26:14', '2026-03-31 15:26:14'),
(12, 18, NULL, 'Mae', '', 'Serrano', 'Aunt', '09181234565', '', 'Molino 2, Bacoor', NULL, '2026-03-31 15:27:25', '2026-03-31 15:27:25');

-- --------------------------------------------------------

--
-- Table structure for table `milk_feeding_records`
--

CREATE TABLE `milk_feeding_records` (
  `milk_record_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `feeding_date` date NOT NULL,
  `attendance` enum('Present','Absent') NOT NULL DEFAULT 'Present',
  `milk_type` varchar(100) DEFAULT NULL,
  `amount` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `milk_feeding_records`
--

INSERT INTO `milk_feeding_records` (`milk_record_id`, `child_id`, `feeding_date`, `attendance`, `milk_type`, `amount`, `remarks`, `recorded_by`, `created_at`, `updated_at`) VALUES
(26, 5, '2026-03-04', 'Present', 'Skimmed / Non-Fat Milk', '2/3 cup', NULL, 6, '2026-03-30 14:40:42', '2026-03-30 14:40:42'),
(27, 8, '2026-03-04', 'Present', 'Skimmed / Non-Fat Milk', '2/3 cup', NULL, 6, '2026-03-30 14:40:42', '2026-03-30 14:40:42'),
(28, 6, '2026-03-04', 'Present', 'Skimmed / Non-Fat Milk', '2/3 cup', NULL, 6, '2026-03-30 14:40:42', '2026-03-30 14:40:42'),
(29, 7, '2026-03-05', 'Present', 'Low Fat Milk', '1 cup', 'finish', 6, '2026-03-30 15:02:55', '2026-03-30 15:02:55'),
(30, 4, '2026-03-05', 'Present', 'Low Fat Milk', '1 cup', 'half', 6, '2026-03-30 15:02:55', '2026-03-30 15:02:55'),
(31, 2, '2026-03-05', 'Present', 'Low Fat Milk', '1 cup', 'finish', 6, '2026-03-30 15:02:55', '2026-03-30 15:02:55'),
(32, 3, '2026-03-05', 'Present', 'Low Fat Milk', '1 cup', 'finish', 6, '2026-03-30 15:02:55', '2026-03-30 15:02:55'),
(33, 5, '2026-03-05', 'Present', 'Low Fat Milk', '1 cup', 'finish', 6, '2026-03-30 15:02:55', '2026-03-30 15:02:55'),
(34, 8, '2026-03-05', 'Present', 'Low Fat Milk', '1 cup', 'finish', 6, '2026-03-30 15:02:55', '2026-03-30 15:02:55'),
(35, 6, '2026-03-05', 'Present', 'Low Fat Milk', '1 cup', 'finish', 6, '2026-03-30 15:02:55', '2026-03-30 15:02:55'),
(36, 7, '2026-03-06', 'Absent', NULL, NULL, 'Absent', 6, '2026-03-30 15:03:30', '2026-03-30 15:03:30'),
(37, 4, '2026-03-06', 'Absent', NULL, NULL, 'Absent', 6, '2026-03-30 15:03:30', '2026-03-30 15:03:30'),
(38, 2, '2026-03-06', 'Absent', NULL, NULL, 'Absent', 6, '2026-03-30 15:03:30', '2026-03-30 15:03:30'),
(39, 3, '2026-03-06', 'Absent', NULL, NULL, 'Absent', 6, '2026-03-30 15:03:30', '2026-03-30 15:03:30'),
(40, 5, '2026-03-06', 'Present', 'Whole Milk', '1 cup', 'half', 6, '2026-03-30 15:03:30', '2026-03-30 15:03:30'),
(41, 8, '2026-03-06', 'Present', 'Whole Milk', '1 cup', 'finish', 6, '2026-03-30 15:03:30', '2026-03-30 15:03:30'),
(42, 6, '2026-03-06', 'Present', 'Whole Milk', '1 cup', 'done', 6, '2026-03-30 15:03:30', '2026-03-30 15:03:30'),
(43, 17, '2026-01-13', 'Present', 'Whole Milk', '1 cup', NULL, 13, '2026-03-31 16:31:19', '2026-03-31 16:31:19'),
(44, 14, '2026-01-13', 'Present', 'Whole Milk', '1 cup', NULL, 13, '2026-03-31 16:31:19', '2026-03-31 16:31:19'),
(45, 15, '2026-01-13', 'Present', 'Whole Milk', '1 cup', NULL, 13, '2026-03-31 16:31:19', '2026-03-31 16:31:19'),
(46, 16, '2026-01-13', 'Present', 'Whole Milk', '1 cup', NULL, 13, '2026-03-31 16:31:19', '2026-03-31 16:31:19'),
(47, 18, '2026-01-13', 'Present', 'Whole Milk', '1 cup', NULL, 13, '2026-03-31 16:31:19', '2026-03-31 16:31:19'),
(48, 17, '2026-04-14', 'Absent', NULL, NULL, 'Absent', 13, '2026-03-31 17:17:16', '2026-03-31 17:17:16'),
(49, 14, '2026-04-14', 'Present', 'Whole Milk', '1 cup', NULL, 13, '2026-03-31 17:17:16', '2026-03-31 17:17:16'),
(50, 15, '2026-04-14', 'Present', 'Whole Milk', '1 cup', NULL, 13, '2026-03-31 17:17:16', '2026-03-31 17:17:16'),
(51, 16, '2026-04-14', 'Present', 'Whole Milk', '1 cup', NULL, 13, '2026-03-31 17:17:16', '2026-03-31 17:17:16'),
(52, 18, '2026-04-14', 'Present', 'Whole Milk', '1 cup', NULL, 13, '2026-03-31 17:17:16', '2026-03-31 17:17:16');

-- --------------------------------------------------------

--
-- Table structure for table `parent_child_links`
--

CREATE TABLE `parent_child_links` (
  `link_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `child_id` int(11) DEFAULT NULL,
  `linked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parent_child_links`
--

INSERT INTO `parent_child_links` (`link_id`, `parent_id`, `child_id`, `linked_at`) VALUES
(1, 7, 2, '2026-03-22 16:39:27'),
(2, 8, 4, '2026-03-22 19:12:08'),
(3, 10, 5, '2026-03-26 03:38:45'),
(4, 11, 6, '2026-03-27 10:54:34'),
(5, 12, 8, '2026-03-27 14:40:43');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'CSWD Admin'),
(2, 'CDW'),
(3, 'Guardian');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_active` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `first_name`, `last_name`, `email`, `password`, `contact_number`, `address`, `created_at`, `last_active`) VALUES
(1, 1, 'Admin', 'User', 'admin@gmail.com', '1234', NULL, NULL, '2026-03-25 22:04:10', '2026-03-31 23:10:30'),
(3, 3, 'guar', 'dian', 'guardian@gmail.com', '4321', NULL, NULL, '2026-03-25 22:04:10', NULL),
(5, 2, 'lena', 'bautista', 'lena@gmail.com', 'lena', NULL, NULL, '2026-03-25 22:04:10', NULL),
(6, 2, 'Marlene', 'Tolentino', 'marlene@gmail.com', 'marlene', NULL, NULL, '2026-03-25 22:04:10', '2026-03-31 17:34:38'),
(7, 3, 'Maria Teresa', 'Cruz', 'maria@gmail.com', 'maria', NULL, NULL, '2026-03-25 22:04:10', NULL),
(8, 3, 'Mia', 'Cruz', 'miacruz@gmail.com', 'miacruz', NULL, NULL, '2026-03-25 22:04:10', NULL),
(9, 2, 'demi', 'perez', 'demiperez@gmail.com', 'demi', NULL, NULL, '2026-03-25 22:26:08', NULL),
(10, 3, 'harvey', 'lino', 'harveylino@gmail.com', '4321', NULL, NULL, '2026-03-26 03:38:45', NULL),
(11, 3, 'Maria', 'Reyex', 'maria.reyes@gmail.com', 'mariareyes', NULL, NULL, '2026-03-27 10:54:34', '2026-03-27 18:54:51'),
(12, 3, 'Carlos', 'Mendoza', 'carlosmendoza@gmail.com', 'carlosmendoza', NULL, NULL, '2026-03-27 14:40:43', '2026-03-27 22:40:53'),
(13, 2, 'Anna', 'Natividad', 'annanatividad@gmail.com', 'annanatividad', NULL, NULL, '2026-03-31 15:10:22', '2026-04-02 18:43:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anthropometric_records`
--
ALTER TABLE `anthropometric_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `child_id` (`child_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `cdc`
--
ALTER TABLE `cdc`
  ADD PRIMARY KEY (`cdc_id`);

--
-- Indexes for table `cdw_assignments`
--
ALTER TABLE `cdw_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cdc_id` (`cdc_id`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`child_id`);

--
-- Indexes for table `child_health_information`
--
ALTER TABLE `child_health_information`
  ADD PRIMARY KEY (`health_info_id`),
  ADD UNIQUE KEY `child_id` (`child_id`);

--
-- Indexes for table `deworming_records`
--
ALTER TABLE `deworming_records`
  ADD PRIMARY KEY (`deworm_id`),
  ADD KEY `fk_deworm_child` (`child_id`),
  ADD KEY `fk_deworm_recorded_by` (`recorded_by`);

--
-- Indexes for table `feeding_records`
--
ALTER TABLE `feeding_records`
  ADD PRIMARY KEY (`feeding_record_id`),
  ADD UNIQUE KEY `unique_child_feeding_date` (`child_id`,`feeding_date`),
  ADD KEY `fk_feeding_records_user` (`recorded_by`);

--
-- Indexes for table `feeding_record_items`
--
ALTER TABLE `feeding_record_items`
  ADD PRIMARY KEY (`feeding_item_id`),
  ADD KEY `fk_feeding_items_record` (`feeding_record_id`),
  ADD KEY `fk_feeding_items_group` (`food_group_id`),
  ADD KEY `fk_feeding_items_food` (`food_item_id`);

--
-- Indexes for table `food_groups`
--
ALTER TABLE `food_groups`
  ADD PRIMARY KEY (`food_group_id`),
  ADD UNIQUE KEY `food_group_name` (`food_group_name`);

--
-- Indexes for table `food_items`
--
ALTER TABLE `food_items`
  ADD PRIMARY KEY (`food_item_id`),
  ADD KEY `fk_food_items_group` (`food_group_id`);

--
-- Indexes for table `growth_hfa`
--
ALTER TABLE `growth_hfa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hfa_row` (`sex`,`age_months`);

--
-- Indexes for table `growth_wfa`
--
ALTER TABLE `growth_wfa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wfa_row` (`sex`,`age_months`);

--
-- Indexes for table `growth_wflh`
--
ALTER TABLE `growth_wflh`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wflh_row` (`sex`,`height_cm`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`guardian_id`),
  ADD UNIQUE KEY `child_id` (`child_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `milk_feeding_records`
--
ALTER TABLE `milk_feeding_records`
  ADD PRIMARY KEY (`milk_record_id`),
  ADD KEY `fk_milk_child` (`child_id`),
  ADD KEY `fk_milk_recorded_by` (`recorded_by`);

--
-- Indexes for table `parent_child_links`
--
ALTER TABLE `parent_child_links`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `child_id` (`child_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anthropometric_records`
--
ALTER TABLE `anthropometric_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `cdc`
--
ALTER TABLE `cdc`
  MODIFY `cdc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cdw_assignments`
--
ALTER TABLE `cdw_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `child_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `child_health_information`
--
ALTER TABLE `child_health_information`
  MODIFY `health_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `deworming_records`
--
ALTER TABLE `deworming_records`
  MODIFY `deworm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `feeding_records`
--
ALTER TABLE `feeding_records`
  MODIFY `feeding_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `feeding_record_items`
--
ALTER TABLE `feeding_record_items`
  MODIFY `feeding_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT for table `food_groups`
--
ALTER TABLE `food_groups`
  MODIFY `food_group_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `food_items`
--
ALTER TABLE `food_items`
  MODIFY `food_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT for table `growth_hfa`
--
ALTER TABLE `growth_hfa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=235;

--
-- AUTO_INCREMENT for table `growth_wfa`
--
ALTER TABLE `growth_wfa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `growth_wflh`
--
ALTER TABLE `growth_wflh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=339;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `guardian_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `milk_feeding_records`
--
ALTER TABLE `milk_feeding_records`
  MODIFY `milk_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `parent_child_links`
--
ALTER TABLE `parent_child_links`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anthropometric_records`
--
ALTER TABLE `anthropometric_records`
  ADD CONSTRAINT `anthropometric_records_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anthropometric_records_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cdw_assignments`
--
ALTER TABLE `cdw_assignments`
  ADD CONSTRAINT `cdw_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cdw_assignments_ibfk_2` FOREIGN KEY (`cdc_id`) REFERENCES `cdc` (`cdc_id`);

--
-- Constraints for table `child_health_information`
--
ALTER TABLE `child_health_information`
  ADD CONSTRAINT `child_health_information_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`);

--
-- Constraints for table `deworming_records`
--
ALTER TABLE `deworming_records`
  ADD CONSTRAINT `fk_deworm_child` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_deworm_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `feeding_records`
--
ALTER TABLE `feeding_records`
  ADD CONSTRAINT `fk_feeding_records_child` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feeding_records_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `feeding_record_items`
--
ALTER TABLE `feeding_record_items`
  ADD CONSTRAINT `fk_feeding_items_food` FOREIGN KEY (`food_item_id`) REFERENCES `food_items` (`food_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feeding_items_group` FOREIGN KEY (`food_group_id`) REFERENCES `food_groups` (`food_group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feeding_items_record` FOREIGN KEY (`feeding_record_id`) REFERENCES `feeding_records` (`feeding_record_id`) ON DELETE CASCADE;

--
-- Constraints for table `food_items`
--
ALTER TABLE `food_items`
  ADD CONSTRAINT `fk_food_items_group` FOREIGN KEY (`food_group_id`) REFERENCES `food_groups` (`food_group_id`) ON DELETE CASCADE;

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `guardians_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`),
  ADD CONSTRAINT `guardians_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `milk_feeding_records`
--
ALTER TABLE `milk_feeding_records`
  ADD CONSTRAINT `fk_milk_child` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_milk_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `parent_child_links`
--
ALTER TABLE `parent_child_links`
  ADD CONSTRAINT `parent_child_links_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `parent_child_links_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `children` (`child_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
