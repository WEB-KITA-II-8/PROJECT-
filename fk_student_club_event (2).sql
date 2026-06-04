-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2026 at 04:19 AM
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
-- Database: `fk_student_club_event`
--

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `club_id` int(11) NOT NULL,
  `club_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `advisor_name` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`club_id`, `club_name`, `description`, `advisor_name`, `status`, `created_at`) VALUES
(1, 'Software Engineering Club', 'Focuses on software development, web systems, mobile applications and software projects.', 'FK UMPSA Staff', 'Active', '2026-05-14 15:15:47'),
(2, 'Cyber Security & Network Club', 'Provides cybersecurity awareness, ethical hacking, network security and competitions.', 'FK UMPSA Staff', 'Active', '2026-05-14 15:15:47'),
(3, 'Artificial Intelligence & Data Analytics Club', 'Focuses on machine learning, artificial intelligence, data analytics and predictive systems.', 'FK UMPSA Staff', 'Active', '2026-05-14 15:15:47'),
(4, 'Multimedia Technology Club', 'Specializes in multimedia systems, graphic design, animation and UI/UX development.', 'FK UMPSA Staff', 'Active', '2026-05-14 15:15:47'),
(5, 'Robotics & Internet of Things Club', 'Engages students in robotics innovation, automation systems and IoT projects.', 'FK UMPSA Staff', 'Active', '2026-05-14 15:15:47'),
(6, 'Game Development Club', 'Focuses on game design, game programming, AR/VR and interactive media development.', 'FK UMPSA Staff', 'Active', '2026-05-14 15:15:47'),
(7, 'Computing Innovation & Entrepreneurship Club', 'Encourages startup innovation, leadership, business technology and entrepreneurship.', 'FK UMPSA Staff', 'Active', '2026-05-14 15:15:47');

-- --------------------------------------------------------

--
-- Table structure for table `clubs_comm`
--

CREATE TABLE `clubs_comm` (
  `id` int(11) NOT NULL,
  `club_name` varchar(150) NOT NULL,
  `advisor_name` varchar(150) NOT NULL,
  `total_members` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clubs_comm`
--

INSERT INTO `clubs_comm` (`id`, `club_name`, `advisor_name`, `total_members`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, 'CAT LOVER', 'ANUAR', 1, 'Inactive', 'KUCING HILANG', '2026-06-03 13:07:30', '2026-06-03 13:07:30'),
(2, '3 K', 'ATIEFFA', 20, 'Active', 'KELAS,KAFE,KATIL', '2026-06-03 13:14:28', '2026-06-03 13:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `committee_members`
--

CREATE TABLE `committee_members` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `position` enum('President','Vice President','Secretary','Treasurer','Committee Member') NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `committee_members`
--

INSERT INTO `committee_members` (`id`, `fullname`, `position`, `email`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Atieffa', 'Vice President', 'atieffa123@gmail.com', '012-3456789', '2026-06-03 13:23:21', '2026-06-03 13:23:21'),
(2, 'Khairul', 'President', 'khairul123@gmail.com', '01125548054', '2026-06-03 13:26:50', '2026-06-03 13:26:50');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_date` date DEFAULT NULL,
  `event_location` varchar(255) DEFAULT NULL,
  `event_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_comm`
--

CREATE TABLE `events_comm` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_location` varchar(255) NOT NULL,
  `event_capacity` int(11) DEFAULT 0,
  `event_description` text DEFAULT NULL,
  `event_start_datetime` datetime NOT NULL,
  `event_end_datetime` datetime NOT NULL,
  `event_latitude` decimal(10,6) DEFAULT NULL,
  `event_longitude` decimal(10,6) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `event_status` enum('Upcoming','Ongoing','Completed','Cancelled') DEFAULT 'Upcoming',
  `event_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events_comm`
--

INSERT INTO `events_comm` (`event_id`, `event_name`, `event_location`, `event_capacity`, `event_description`, `event_start_datetime`, `event_end_datetime`, `event_latitude`, `event_longitude`, `contact_name`, `contact_phone`, `contact_email`, `created_by`, `created_at`, `updated_at`, `event_status`, `event_image`) VALUES
(1, 'CAT FESTIVAL', 'DEWAN SERBAGUNA', 100, 'CAT LOVER', '2026-06-01 13:10:00', '2026-06-06 13:10:00', 0.000000, 0.000000, 'Atieffa', '0121212121', 'tipah123@gmail.com', NULL, '2026-06-02 04:07:06', '2026-06-02 04:07:59', 'Upcoming', NULL),
(3, 'Pikachu RUN', 'PAP', 150, 'LARI JE', '2026-06-20 07:30:00', '2026-06-20 13:00:00', 3.539225, 103.428263, 'Rangges', '0128223123', 'rangges@fk.com', NULL, '2026-06-02 05:19:22', '2026-06-02 05:19:22', 'Upcoming', 'uploads/events/1780377562_641968.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `event_attendance`
--

CREATE TABLE `event_attendance` (
  `attendance_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `club_name` varchar(255) DEFAULT NULL,
  `check_in_time` time DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `attendance_status` enum('present','late','absent','volunteer') DEFAULT 'present',
  `points_awarded` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_attendance`
--

INSERT INTO `event_attendance` (`attendance_id`, `event_id`, `user_id`, `event_name`, `student_id`, `student_name`, `club_name`, `check_in_time`, `attendance_date`, `attendance_status`, `points_awarded`, `created_at`) VALUES
(61, NULL, 2, 'Hackathon 2026', '2', 'ATIEFFA', 'Software Engineering Club', '03:57:57', '2026-05-29', 'late', 5, '2026-05-21 01:57:57'),
(65, NULL, 12, 'Money Talk 2025', '12', 'Tan Jia Hui', 'N/A', '04:02:59', '2026-06-11', 'late', 5, '2026-05-21 02:02:59'),
(72, NULL, 9, 'Talk', '9', 'Daniel Lee', 'N/A', '04:15:48', '2026-05-27', 'late', 5, '2026-05-21 02:15:48'),
(73, NULL, 9, 'Tech Talk 2025', '9', 'Daniel Lee', 'N/A', '04:20:12', '2026-05-21', 'present', 10, '2026-05-21 02:20:12'),
(74, NULL, 11, 'Tech Talk 2025', '11', 'Muhammad Iqbal', 'N/A', '04:20:31', '2026-05-21', 'late', 5, '2026-05-21 02:20:31'),
(76, NULL, 12, 'Running ', '12', 'Tan Jia Hui', 'N/A', '04:44:29', '2026-07-17', 'volunteer', 15, '2026-05-21 02:44:29'),
(77, NULL, 12, 'lllll', '12', 'Tan Jia Hui', 'N/A', '04:45:31', '2026-05-19', 'present', 10, '2026-05-21 02:45:31'),
(78, NULL, 8, 'Event 2026', '8', 'Nur Aisyah', 'N/A', '04:47:16', '2026-06-18', 'present', 10, '2026-05-21 02:47:16'),
(79, NULL, 8, 'Tech Talk 2025', '8', 'Nur Aisyah', 'N/A', '04:48:22', '2026-05-21', 'present', 10, '2026-05-21 02:48:22'),
(80, NULL, 10, 'Tech Talk 2025', '10', 'Siti Hajar', 'N/A', '04:49:01', '2026-05-21', 'present', 10, '2026-05-21 02:49:01');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `registration_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`registration_id`, `event_id`, `user_id`, `student_name`, `student_email`, `phone`, `registered_at`) VALUES
(1, 3, 8, 'Khairul', 'khairul123@gmail.com', '01125548054', '2026-06-02 05:25:28');

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `membership_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `membership_type` enum('member','committee') DEFAULT 'member',
  `committee_role` varchar(50) DEFAULT NULL,
  `joined_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `membership_status` varchar(50) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`membership_id`, `user_id`, `club_id`, `membership_type`, `committee_role`, `joined_date`, `membership_status`) VALUES
(1, 3, 4, 'committee', 'Committee Member', '2026-05-12 16:00:00', 'Active'),
(10, 2, 1, 'member', '', '2026-04-30 16:00:00', 'Inactive'),
(11, 2, 1, '', NULL, '2026-05-16 19:08:36', 'Inactive'),
(12, 2, 3, '', NULL, '2026-05-16 19:33:09', 'Inactive'),
(13, 2, 2, '', NULL, '2026-05-16 19:36:46', 'Inactive'),
(14, 2, 2, '', NULL, '2026-05-16 19:42:48', 'Inactive'),
(15, 2, 1, '', NULL, '2026-05-16 20:09:38', 'Inactive'),
(16, 2, 1, '', NULL, '2026-05-16 20:13:24', 'Inactive'),
(19, 8, 1, 'committee', 'Committee Member', '2026-06-02 04:39:32', 'Active'),
(20, 11, 3, 'committee', 'Treasurer', '2026-06-02 16:00:00', 'Active'),
(21, 8, 3, '', NULL, '2026-06-03 13:39:53', 'Inactive'),
(22, 8, 6, '', NULL, '2026-06-03 13:46:50', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `participation`
--

CREATE TABLE `participation` (
  `participation_id` int(11) NOT NULL,
  `registration_id` int(11) DEFAULT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `attendance_status` enum('Pending','Attended','Absent') DEFAULT 'Pending',
  `participation_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student','committee') NOT NULL,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `reset_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `student_id`, `full_name`, `email`, `phone`, `password`, `role`, `profile_image`, `reset_token`, `created_at`, `bio`) VALUES
(1, 'ADMIN001', 'FAQIHAH', 'admin@fk.com', '0123456789', 'Admin123', 'admin', 'default.png', NULL, '2026-05-13 03:34:22', NULL),
(2, 'STUDENT001', 'ATIEFFA', 'tipah123@gmail.com', '01123432323', 'Atieffa123', 'student', 'default.png', NULL, '2026-05-13 03:41:24', NULL),
(3, 'COMMITTEE001', 'MULTIMEDIA', 'committee@fk.com', '01123456789', 'Comm123', 'student', 'default.png', NULL, '2026-05-13 04:38:47', NULL),
(8, 'CB25036', 'Khairul', 'khairul123@gmail.com', '011222331231', 'Khai123', 'committee', 'default.png', NULL, '2026-06-02 04:28:59', NULL),
(9, 'C001', 'Rangges', 'rangges@comm.edu', '01212121121', 'Rangges123', 'committee', 'default.png', NULL, '2026-06-02 04:47:10', NULL),
(10, 'A001', 'NURUL ATIEFFA', 'admin2@fk.com', '012-3456789', 'Admin1231', 'admin', 'default.png', NULL, '2026-06-03 13:02:02', NULL),
(11, 'S001', 'FAQIHAH', 'faqihah123@student.com', '012123123', 'Faqihah123', 'student', 'default.png', NULL, '2026-06-03 13:19:09', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`club_id`);

--
-- Indexes for table `clubs_comm`
--
ALTER TABLE `clubs_comm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `committee_members`
--
ALTER TABLE `committee_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `events_comm`
--
ALTER TABLE `events_comm`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `unique_registration` (`event_id`,`user_id`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`membership_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `participation`
--
ALTER TABLE `participation`
  ADD PRIMARY KEY (`participation_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `club_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `clubs_comm`
--
ALTER TABLE `clubs_comm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `committee_members`
--
ALTER TABLE `committee_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_comm`
--
ALTER TABLE `events_comm`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_attendance`
--
ALTER TABLE `event_attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `membership_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `participation`
--
ALTER TABLE `participation`
  MODIFY `participation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `memberships`
--
ALTER TABLE `memberships`
  ADD CONSTRAINT `memberships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `memberships_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`club_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
