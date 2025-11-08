-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 12, 2025 at 01:36 PM
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
-- Database: `task_mgmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_schedule`
--

CREATE TABLE `class_schedule` (
  `id` int(11) NOT NULL,
  `course` varchar(100) NOT NULL,
  `section` varchar(50) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `day` varchar(20) NOT NULL,
  `time` varchar(50) NOT NULL,
  `room` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `date_assigned` date NOT NULL,
  `deadline` date NOT NULL,
  `academic_year` varchar(50) NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `instructor_id`, `title`, `description`, `assigned_to`, `assigned_by`, `date_assigned`, `deadline`, `academic_year`, `status`, `created_at`) VALUES
(1, 0, 'Meeting', 'very important meeting', 3, 5, '2025-08-24', '2025-08-25', '2025-2026', 'completed', '2025-08-24 15:42:30'),
(2, 0, 'Upload your course syllabus', 'Must upload your course syllabus', 3, 5, '2025-08-24', '2025-08-26', '2025-2026', 'completed', '2025-08-24 15:59:33'),
(3, 0, 'test', 'test', 3, 5, '2025-08-24', '2025-08-26', '2025-2026', 'completed', '2025-08-24 17:10:44'),
(4, 0, 'Upload Course Syllabus', 'Must upload your course syllabus by the end of the week.', 6, 5, '2025-09-01', '2025-09-05', '2025-2026', 'pending', '2025-09-01 15:55:53'),
(5, 0, 'test', 'test', 6, 5, '2025-09-10', '2025-09-12', '2025-2026', 'completed', '2025-09-10 05:30:28'),
(6, 0, 'syllabus', 'pag pasa lang', 6, 5, '2025-09-10', '2025-09-11', '2025-2026', 'completed', '2025-09-10 08:15:07'),
(7, 0, 'IT24 Syllabus', 'Pass the IT24 Syllabus', 6, 5, '2025-09-10', '2025-09-11', '2025-2026', 'completed', '2025-09-10 08:18:45'),
(8, 0, 'Syllabus', 'test', 7, 5, '2025-09-10', '2025-09-11', '2025-2026', 'completed', '2025-09-10 10:04:55'),
(9, 0, 'Syllabus', 'test', 7, 5, '2025-09-11', '2025-09-12', '2025-2026', 'completed', '2025-09-11 22:30:22'),
(10, 0, 'Syllabus', 'test', 8, 5, '2025-09-11', '2025-09-19', '2025-2026', 'completed', '2025-09-11 22:41:40'),
(11, 0, 'Syllabus', 'test\'\'', 9, 5, '2025-09-11', '2025-09-12', '2025-2026', 'completed', '2025-09-11 22:43:25'),
(12, 0, 'Syllabus', 'test', 8, 5, '2025-09-11', '2025-09-13', '2025-2026', 'completed', '2025-09-11 22:48:02'),
(13, 0, 'Syllabus', 'test', 11, 5, '2025-09-12', '2025-09-15', '2025-2026', 'pending', '2025-09-12 19:36:05');

-- --------------------------------------------------------

--
-- Table structure for table `task_history`
--

CREATE TABLE `task_history` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `completed_at` datetime NOT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_history`
--

INSERT INTO `task_history` (`id`, `task_id`, `completed_at`, `file_path`) VALUES
(1, 1, '2025-08-24 15:42:51', NULL),
(2, 2, '2025-08-24 16:03:42', '1756022622_Learning.pdf'),
(3, 3, '2025-08-24 17:12:18', '1756026738_Learning.pdf'),
(4, 5, '2025-09-10 05:31:12', '1757453472_Learning.pdf'),
(5, 6, '2025-09-10 08:16:09', '1757463369_Learning.pdf'),
(6, 7, '2025-09-10 08:21:46', '1757463706_Signed - CAFE BSIT Learning Commitment Form.pdf'),
(7, 8, '2025-09-10 10:19:19', '1757470759_Learning.pdf'),
(8, 9, '2025-09-11 22:31:11', '1757601071_Learning.pdf'),
(9, 10, '2025-09-11 22:42:00', '1757601720_Learning.pdf'),
(10, 11, '2025-09-11 22:44:03', '1757601843_Learning.pdf'),
(11, 12, '2025-09-11 22:48:21', '1757602101_Learning.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','coordinator','instructor') NOT NULL,
  `status` enum('pending','active','inactive') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `profile_image`, `password`, `role`, `status`, `created_at`, `is_approved`) VALUES
(1, 'Mickel Angelo Gaudicos', '20222356@nbsc.edu.ph', 'tan.webp', '$2y$10$Kxe.YnUOcWdAp40.cMN8ROi6xo319JRyVkR.LMrorUi0VkbZwOnc6', 'admin', 'pending', '2025-08-24 15:29:24', 0),
(3, 'instructor_example', 'instructor@nbsc.edu.ph', '1756712739_Screenshot (235).png', '$2y$10$ciduSod.63kB3hty13ZPzOHwMSnDoyJRqwpE8TAHrOVBUnP.JUudu', 'instructor', 'pending', '2025-08-24 15:31:10', 0),
(5, 'Frank Joseph', 'coordinator@nbsc.edu.ph', NULL, '$2y$10$HXoRz2mNOGjSF/NbSupxxug5JzsBObpi1ifZG/iEBrCjndGpKmEUG', 'coordinator', 'pending', '2025-08-24 15:38:48', 0),
(6, 'instructor_2', 'instructor_2@nbsc.edu.ph', '1756713093_Screenshot (235).png', '$2y$10$Nnm9Uu3MGdSPN4JxI.6SGOdpNt4BsOWLjlQgrHXVR/p5VFFGoY4.a', 'instructor', 'active', '2025-09-01 15:47:20', 0),
(7, 'Melvin Reyes', 'instructor_3@nbsc.edu.ph', NULL, '$2y$10$dte0UoluxNC235pAc9SUIuaa0wgzXWvTS5iSnY7Yj8.dfOj4Ba7dK', 'instructor', 'active', '2025-09-10 09:50:51', 0),
(8, 'Alladin Cagubcub', 'instructor_4@nbsc.edu.ph', NULL, '$2y$10$zUvSkPdXv93OV1cDMp2iGO/Ejlm56RNWW58XdW/a94a708JBxp.o6', 'instructor', 'active', '2025-09-11 22:04:03', 0),
(9, 'Kaneki Ken', 'instructor_5@nbsc.edu.ph', NULL, '$2y$10$3.gE/9nhajZJKkiB6mnqFeoQxhQBMKiTrO9LvzHXSQSVVh1Sa4vFe', 'instructor', 'active', '2025-09-11 22:05:41', 0),
(11, 'Monkey D. Luffy', 'instructor_6@nbsc.edu.ph', NULL, '$2y$10$bh3UWeyQ9kyE3koZMJvMb.gIAwku5rmlMzH3luWzA8nD40g4BL.TS', 'instructor', 'active', '2025-09-11 22:06:29', 0),
(12, 'Bonsatsky Pitsuu', 'instructor_7@nbsc.edu.ph', NULL, '$2y$10$aygulyoejMUF4kqvGtZF4u3CPSFkzgNVbVup.LE//glKow58Twd3C', 'instructor', 'active', '2025-09-11 22:06:57', 0),
(13, 'Gol D Roger', 'instructor_8@nbsc.edu.ph', NULL, '$2y$10$f.MnL1sS/DeC.G6h7DPDdOYmFWpMc/HMOF7rM6Ce2T4yWNcE3WOOe', 'instructor', 'active', '2025-09-11 22:07:32', 0),
(14, 'Rocks D Xebec', 'instructor_9@nbsc.edu.ph', NULL, '$2y$10$f4tA7IjDlVis7eAAqG2zMO409XXq5N76De7fZkvCG4Ml/OP4rPRbu', 'instructor', 'active', '2025-09-11 22:07:53', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 6, 'User logged in', NULL, NULL, '2025-09-01 08:47:49'),
(2, 5, 'User logged in', NULL, NULL, '2025-09-01 08:47:57'),
(3, 5, 'User logged in', NULL, NULL, '2025-09-01 09:12:55'),
(4, 6, 'User logged in', NULL, NULL, '2025-09-01 09:13:44'),
(5, 5, 'User logged in', NULL, NULL, '2025-09-01 09:13:56'),
(6, 1, 'User logged in', NULL, NULL, '2025-09-01 11:48:47'),
(7, 5, 'User logged in', NULL, NULL, '2025-09-01 12:13:51'),
(8, 5, 'User logged in', NULL, NULL, '2025-09-03 02:02:14'),
(9, 5, 'User logged in', NULL, NULL, '2025-09-03 02:13:27'),
(10, 6, 'User logged in', NULL, NULL, '2025-09-03 02:15:35'),
(11, 5, 'User logged in', NULL, NULL, '2025-09-03 02:17:51'),
(12, 6, 'User logged in', NULL, NULL, '2025-09-03 02:18:00'),
(13, 5, 'User logged in', NULL, NULL, '2025-09-03 02:18:15'),
(14, 6, 'User logged in', NULL, NULL, '2025-09-08 12:17:49'),
(15, 5, 'User logged in', NULL, NULL, '2025-09-08 12:19:35'),
(16, 1, 'User logged in', NULL, NULL, '2025-09-08 12:26:09'),
(17, 5, 'User logged in', NULL, NULL, '2025-09-08 12:28:48'),
(18, 5, 'Updated profile', NULL, NULL, '2025-09-08 12:28:59'),
(19, 5, 'User logged in', NULL, NULL, '2025-09-08 12:29:12'),
(20, 5, 'User logged in', NULL, NULL, '2025-09-08 12:54:06'),
(21, 5, 'User logged in', NULL, NULL, '2025-09-09 21:24:07'),
(22, 5, 'Assigned task \'test\' to instructor ID 6', NULL, NULL, '2025-09-09 21:30:28'),
(23, 6, 'User logged in', NULL, NULL, '2025-09-09 21:30:37'),
(24, 5, 'User logged in', NULL, NULL, '2025-09-09 21:31:26'),
(25, 6, 'User logged in', NULL, NULL, '2025-09-09 22:27:31'),
(26, 1, 'User logged in', NULL, NULL, '2025-09-09 22:27:54'),
(27, 5, 'User logged in', NULL, NULL, '2025-09-10 00:09:41'),
(28, 6, 'User logged in', NULL, NULL, '2025-09-10 00:10:14'),
(29, 1, 'User logged in', NULL, NULL, '2025-09-10 00:10:44'),
(30, 6, 'User logged in', NULL, NULL, '2025-09-10 00:12:37'),
(31, 5, 'User logged in', NULL, NULL, '2025-09-10 00:13:50'),
(32, 5, 'Assigned task \'syllabus\' to instructor ID 6', NULL, NULL, '2025-09-10 00:15:07'),
(33, 6, 'User logged in', NULL, NULL, '2025-09-10 00:15:22'),
(34, 5, 'User logged in', NULL, NULL, '2025-09-10 00:17:51'),
(35, 6, 'User logged in', NULL, NULL, '2025-09-10 00:18:03'),
(36, 5, 'Assigned task \'IT24 Syllabus\' to instructor ID 6', NULL, NULL, '2025-09-10 00:18:45'),
(37, 1, 'User logged in', NULL, NULL, '2025-09-10 01:46:25'),
(38, 5, 'User logged in', NULL, NULL, '2025-09-10 01:46:39'),
(39, 6, 'User logged in', NULL, NULL, '2025-09-10 01:47:35'),
(40, 6, 'User logged in', NULL, NULL, '2025-09-10 01:48:21'),
(41, 5, 'Updated profile', NULL, NULL, '2025-09-10 01:48:40'),
(42, 5, 'User logged in', NULL, NULL, '2025-09-10 01:48:52'),
(43, 1, 'User logged in', NULL, NULL, '2025-09-10 01:50:12'),
(44, 5, 'User logged in', NULL, NULL, '2025-09-10 01:51:01'),
(45, 7, 'User logged in', NULL, NULL, '2025-09-10 01:51:39'),
(46, 6, 'User logged in', NULL, NULL, '2025-09-10 01:54:02'),
(47, 7, 'User logged in', NULL, NULL, '2025-09-10 01:59:40'),
(48, 5, 'User logged in', NULL, NULL, '2025-09-10 02:04:36'),
(49, 5, 'Assigned task \'Syllabus\' to instructor ID 7', NULL, NULL, '2025-09-10 02:04:55'),
(50, 6, 'User logged in', NULL, NULL, '2025-09-11 12:21:10'),
(51, 1, 'User logged in', NULL, NULL, '2025-09-11 12:22:21'),
(52, 6, 'User logged in', NULL, NULL, '2025-09-11 13:27:58'),
(53, 1, 'User logged in', NULL, NULL, '2025-09-11 13:28:12'),
(54, 6, 'User logged in', NULL, NULL, '2025-09-11 13:28:30'),
(55, 1, 'User logged in', NULL, NULL, '2025-09-11 13:28:40'),
(56, 1, 'User logged in', NULL, NULL, '2025-09-11 13:47:32'),
(57, 1, 'User logged in', NULL, NULL, '2025-09-11 14:09:54'),
(58, 7, 'User logged in', NULL, NULL, '2025-09-11 14:29:46'),
(59, 5, 'User logged in', NULL, NULL, '2025-09-11 14:30:09'),
(60, 5, 'Assigned task \'Syllabus\' to instructor ID 7', NULL, NULL, '2025-09-11 14:30:22'),
(61, 1, 'User logged in', NULL, NULL, '2025-09-11 14:30:32'),
(62, 7, 'User logged in', NULL, NULL, '2025-09-11 14:34:40'),
(63, 7, 'User logged in', NULL, NULL, '2025-09-11 14:35:54'),
(64, 5, 'User logged in', NULL, NULL, '2025-09-11 14:41:24'),
(65, 5, 'Assigned task \'Syllabus\' to instructor ID 8', NULL, NULL, '2025-09-11 14:41:40'),
(66, 8, 'User logged in', NULL, NULL, '2025-09-11 14:41:48'),
(67, 5, 'User logged in', NULL, NULL, '2025-09-11 14:43:02'),
(68, 5, 'Assigned task \'Syllabus\' to instructor ID 9', NULL, NULL, '2025-09-11 14:43:25'),
(69, 9, 'User logged in', NULL, NULL, '2025-09-11 14:43:47'),
(70, 5, 'User logged in', NULL, NULL, '2025-09-11 14:47:45'),
(71, 5, 'Assigned task \'Syllabus\' to instructor ID 8', NULL, NULL, '2025-09-11 14:48:02'),
(72, 8, 'User logged in', NULL, NULL, '2025-09-11 14:48:10'),
(73, 5, 'User logged in', NULL, NULL, '2025-09-12 11:35:45'),
(74, 5, 'Assigned task \'Syllabus\' to instructor ID 11', NULL, NULL, '2025-09-12 11:36:05'),
(75, 9, 'User logged in', NULL, NULL, '2025-09-12 11:36:13'),
(76, 11, 'User logged in', NULL, NULL, '2025-09-12 11:36:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `task_history`
--
ALTER TABLE `task_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_schedule`
--
ALTER TABLE `class_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `task_history`
--
ALTER TABLE `task_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD CONSTRAINT `class_schedule_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `task_history`
--
ALTER TABLE `task_history`
  ADD CONSTRAINT `task_history_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`);

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
