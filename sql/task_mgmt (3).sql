-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2025 at 08:35 AM
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
(4, 0, 'Upload Course Syllabus', 'Must upload your course syllabus by the end of the week.', 6, 5, '2025-09-01', '2025-09-05', '2025-2026', 'completed', '2025-09-01 15:55:53'),
(5, 0, 'test', 'test', 6, 5, '2025-09-10', '2025-09-12', '2025-2026', 'completed', '2025-09-10 05:30:28'),
(6, 0, 'syllabus', 'pag pasa lang', 6, 5, '2025-09-10', '2025-09-11', '2025-2026', 'completed', '2025-09-10 08:15:07'),
(7, 0, 'IT24 Syllabus', 'Pass the IT24 Syllabus', 6, 5, '2025-09-10', '2025-09-11', '2025-2026', 'completed', '2025-09-10 08:18:45'),
(8, 0, 'Syllabus', 'test', 7, 5, '2025-09-10', '2025-09-11', '2025-2026', 'completed', '2025-09-10 10:04:55'),
(9, 0, 'Syllabus', 'test', 7, 5, '2025-09-11', '2025-09-12', '2025-2026', 'completed', '2025-09-11 22:30:22'),
(10, 0, 'Syllabus', 'test', 8, 5, '2025-09-11', '2025-09-19', '2025-2026', 'completed', '2025-09-11 22:41:40'),
(11, 0, 'Syllabus', 'test\'\'', 9, 5, '2025-09-11', '2025-09-12', '2025-2026', 'completed', '2025-09-11 22:43:25'),
(12, 0, 'Syllabus', 'test', 8, 5, '2025-09-11', '2025-09-13', '2025-2026', 'completed', '2025-09-11 22:48:02'),
(13, 0, 'Syllabus', 'test', 11, 5, '2025-09-12', '2025-09-15', '2025-2026', 'pending', '2025-09-12 19:36:05'),
(14, 0, 'Syllabus', 'submit your syllabus', 6, 5, '2025-11-08', '2025-11-10', '2025-2026', 'completed', '2025-11-08 18:53:04'),
(15, 0, 'Test', 'Pass requirements ASAP', 7, 18, '2025-11-11', '2025-11-14', '2025', 'pending', '2025-11-11 14:32:11'),
(16, 0, 'Syllabus', 'test', 6, 5, '2025-11-11', '2025-11-13', '2025-2026', 'completed', '2025-11-11 21:23:43'),
(17, 0, 'Syllabus', 'test', 8, 5, '2025-11-11', '2025-11-14', '2024-2025', 'completed', '2025-11-11 21:53:00'),
(18, 0, 'Syllabus', 'testing', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 14:10:33'),
(19, 0, 'Syllabus', 'test deadline notif', 6, 5, '2025-11-12', '2025-11-13', '2024-2025', 'completed', '2025-11-12 14:11:29'),
(20, 0, 'Syllabus', 'Important Task', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 15:19:19'),
(21, 0, 'Syllabus', 'test optional link', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 18:36:34'),
(22, 0, 'Syllabus', 'test 2(optional gdrive link)', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 18:41:59'),
(23, 0, 'Syllabus', 'test 3(order by)', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 18:46:14'),
(24, 0, 'Syllabus', 'test gdrive', 8, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 21:59:10');

-- --------------------------------------------------------

--
-- Table structure for table `task_history`
--

CREATE TABLE `task_history` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `completed_at` datetime NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `drive_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_history`
--

INSERT INTO `task_history` (`id`, `task_id`, `completed_at`, `file_path`, `drive_link`) VALUES
(1, 1, '2025-08-24 15:42:51', NULL, NULL),
(2, 2, '2025-08-24 16:03:42', '1756022622_Learning.pdf', NULL),
(3, 3, '2025-08-24 17:12:18', '1756026738_Learning.pdf', NULL),
(4, 5, '2025-09-10 05:31:12', '1757453472_Learning.pdf', NULL),
(5, 6, '2025-09-10 08:16:09', '1757463369_Learning.pdf', NULL),
(6, 7, '2025-09-10 08:21:46', '1757463706_Signed - CAFE BSIT Learning Commitment Form.pdf', NULL),
(7, 8, '2025-09-10 10:19:19', '1757470759_Learning.pdf', NULL),
(8, 9, '2025-09-11 22:31:11', '1757601071_Learning.pdf', NULL),
(9, 10, '2025-09-11 22:42:00', '1757601720_Learning.pdf', NULL),
(10, 11, '2025-09-11 22:44:03', '1757601843_Learning.pdf', NULL),
(11, 12, '2025-09-11 22:48:21', '1757602101_Learning.pdf', NULL),
(12, 4, '2025-11-08 18:26:09', 'https://drive.google.com/file/d/1GU2CcNJmnCn8upw_HHNou5aT6BHRWAiF/view', NULL),
(13, 14, '2025-11-12 18:05:44', '1762941944_Learning.pdf', NULL),
(14, 16, '2025-11-12 18:06:05', '1762941965_Learning.pdf', NULL),
(15, 19, '2025-11-12 18:23:19', '1762942999_Learning.pdf', NULL),
(16, 18, '2025-11-12 18:23:30', '1762943010_Learning.pdf', NULL),
(17, 20, '2025-11-12 18:27:13', '1762943233_Learning.pdf', NULL),
(18, 21, '2025-11-12 18:41:31', NULL, 'https://drive.google.com/file/d/1GU2CcNJmnCn8upw_HHNou5aT6BHRWAiF/view'),
(19, 22, '2025-11-12 18:42:22', '1762944142_Learning.pdf', NULL),
(20, 23, '2025-11-12 21:35:02', NULL, 'https://drive.google.com/file/d/1GU2CcNJmnCn8upw_HHNou5aT6BHRWAiF/view'),
(21, 17, '2025-11-12 21:49:02', NULL, 'https://drive.google.com/file/d/1GU2CcNJmnCn8upw_HHNou5aT6BHRWAiF/view'),
(22, 24, '2025-11-12 21:59:19', NULL, 'https://drive.google.com/file/d/1GU2CcNJmnCn8upw_HHNou5aT6BHRWAiF/view');

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
(1, 'Mickel Angelo Gaudicos', '20222356@nbsc.edu.ph', 'profile_1_1762779139.jpg', '$2y$10$Kxe.YnUOcWdAp40.cMN8ROi6xo319JRyVkR.LMrorUi0VkbZwOnc6', 'admin', 'pending', '2025-08-24 15:29:24', 0),
(3, 'instructor_example', 'instructor@nbsc.edu.ph', '1756712739_Screenshot (235).png', '$2y$10$ciduSod.63kB3hty13ZPzOHwMSnDoyJRqwpE8TAHrOVBUnP.JUudu', 'instructor', 'pending', '2025-08-24 15:31:10', 0),
(5, 'Frank Joseph', 'coordinator@nbsc.edu.ph', 'profile_5_1762946127.jpg', '$2y$10$HXoRz2mNOGjSF/NbSupxxug5JzsBObpi1ifZG/iEBrCjndGpKmEUG', 'coordinator', 'pending', '2025-08-24 15:38:48', 0),
(6, 'instructor_2', 'instructor_2@nbsc.edu.ph', '1762945726_4174055.jpg', '$2y$10$Nnm9Uu3MGdSPN4JxI.6SGOdpNt4BsOWLjlQgrHXVR/p5VFFGoY4.a', 'instructor', 'active', '2025-09-01 15:47:20', 0),
(7, 'Melvin Reyes', 'instructor_3@nbsc.edu.ph', NULL, '$2y$10$dte0UoluxNC235pAc9SUIuaa0wgzXWvTS5iSnY7Yj8.dfOj4Ba7dK', 'instructor', 'active', '2025-09-10 09:50:51', 0),
(8, 'Alladin Cagubcub', 'instructor_4@nbsc.edu.ph', NULL, '$2y$10$zUvSkPdXv93OV1cDMp2iGO/Ejlm56RNWW58XdW/a94a708JBxp.o6', 'instructor', 'active', '2025-09-11 22:04:03', 0),
(9, 'Kaneki Ken', 'instructor_5@nbsc.edu.ph', NULL, '$2y$10$3.gE/9nhajZJKkiB6mnqFeoQxhQBMKiTrO9LvzHXSQSVVh1Sa4vFe', 'instructor', 'active', '2025-09-11 22:05:41', 0),
(11, 'Monkey D. Luffy', 'instructor_6@nbsc.edu.ph', NULL, '$2y$10$bh3UWeyQ9kyE3koZMJvMb.gIAwku5rmlMzH3luWzA8nD40g4BL.TS', 'instructor', 'active', '2025-09-11 22:06:29', 0),
(16, 'instructor_Example2', 'instructor_example@nbsc.edu.ph', NULL, '$2y$10$6A2iMKDIInCWlLg6p2XfA.vE4t4imSGZnbrSX99CeBDuwajgfLqiW', 'instructor', 'pending', '2025-10-21 11:59:12', 0),
(17, 'instructor_example3', 'instructor_example3@nbsc.edu.ph', NULL, '$2y$10$ryX3RzHz63MbDAfhHHaLSOPajsyQjl7Z6NsWwnzscLOS/j0qBHuKO', 'instructor', 'pending', '2025-10-21 12:00:03', 0),
(18, 'Cliff Amadeus Evangelio', 'cafevangelio@nbsc.edu.ph', NULL, '$2y$10$g.XNRlihM7dyVLDBQLQYx.sbxl3HKEwTqaBRFkLPZkyNWz..ofZ8K', 'coordinator', 'pending', '2025-11-11 14:28:55', 0);

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
(76, 11, 'User logged in', NULL, NULL, '2025-09-12 11:36:22'),
(77, 5, 'User logged in', NULL, NULL, '2025-10-21 03:45:17'),
(78, 6, 'User logged in', NULL, NULL, '2025-10-21 03:45:40'),
(79, 5, 'User logged in', NULL, NULL, '2025-10-21 03:46:51'),
(80, 1, 'User logged in', NULL, NULL, '2025-10-21 03:47:51'),
(81, 5, 'User logged in', NULL, NULL, '2025-10-21 04:02:33'),
(82, 1, 'User logged in', NULL, NULL, '2025-10-21 04:02:52'),
(83, 6, 'User logged in', NULL, NULL, '2025-10-21 04:03:10'),
(84, 1, 'User logged in', NULL, NULL, '2025-10-21 04:08:03'),
(85, 5, 'User logged in', NULL, NULL, '2025-10-21 04:08:45'),
(86, 6, 'User logged in', NULL, NULL, '2025-10-21 04:09:05'),
(87, 5, 'User logged in', NULL, NULL, '2025-11-08 10:16:29'),
(88, 6, 'User logged in', NULL, NULL, '2025-11-08 10:16:55'),
(89, 1, 'User logged in', NULL, NULL, '2025-11-08 10:17:21'),
(90, 6, 'User logged in', NULL, NULL, '2025-11-08 10:18:02'),
(91, 6, 'User logged in', NULL, NULL, '2025-11-08 10:27:06'),
(92, 1, 'User logged in', NULL, NULL, '2025-11-08 10:31:55'),
(93, 5, 'User logged in', NULL, NULL, '2025-11-08 10:32:13'),
(94, 1, 'User logged in', NULL, NULL, '2025-11-08 10:32:35'),
(95, 6, 'User logged in', NULL, NULL, '2025-11-08 10:36:48'),
(96, 1, 'User logged in', NULL, NULL, '2025-11-08 10:41:44'),
(97, 6, 'User logged in', NULL, NULL, '2025-11-08 10:41:55'),
(98, 5, 'User logged in', NULL, NULL, '2025-11-08 10:52:28'),
(99, 5, 'Assigned task \'Syllabus\' to instructor ID 6', NULL, NULL, '2025-11-08 10:53:04'),
(100, 6, 'User logged in', NULL, NULL, '2025-11-08 10:53:18'),
(101, 5, 'User logged in', NULL, NULL, '2025-11-08 11:11:15'),
(102, 5, 'User logged in', NULL, NULL, '2025-11-10 12:35:30'),
(103, 1, 'User logged in', NULL, NULL, '2025-11-10 12:36:30'),
(104, 1, 'User logged in', NULL, NULL, '2025-11-10 12:38:50'),
(105, 6, 'User logged in', NULL, NULL, '2025-11-10 12:39:45'),
(106, 5, 'User logged in', NULL, NULL, '2025-11-10 12:39:56'),
(107, 1, 'User logged in', NULL, NULL, '2025-11-10 12:40:24'),
(108, 6, 'User logged in', NULL, NULL, '2025-11-10 12:55:10'),
(109, 1, 'User logged in', NULL, NULL, '2025-11-10 13:52:32'),
(110, 5, 'User logged in', NULL, NULL, '2025-11-10 13:52:47'),
(111, 1, 'User logged in', NULL, NULL, '2025-11-10 13:59:58'),
(112, 6, 'User logged in', NULL, NULL, '2025-11-11 02:53:17'),
(113, 5, 'User logged in', NULL, NULL, '2025-11-11 02:53:52'),
(114, 1, 'User logged in', NULL, NULL, '2025-11-11 02:54:23'),
(115, 5, 'User logged in', NULL, NULL, '2025-11-11 02:55:52'),
(116, 5, 'User logged in', NULL, NULL, '2025-11-11 06:24:22'),
(117, 1, 'User logged in', NULL, NULL, '2025-11-11 06:28:13'),
(118, 18, 'User logged in', NULL, NULL, '2025-11-11 06:30:00'),
(119, 18, 'Assigned task \'Test\' to instructor ID 7', NULL, NULL, '2025-11-11 06:32:11'),
(120, 5, 'User logged in', NULL, NULL, '2025-11-11 12:46:36'),
(121, 5, 'Assigned task \'Syllabus\' to instructor ID 6', NULL, NULL, '2025-11-11 13:23:43'),
(122, 5, 'Assigned task \'Syllabus\' to instructor ID 8', NULL, NULL, '2025-11-11 13:53:00'),
(123, 5, 'User logged in', NULL, NULL, '2025-11-11 14:37:28'),
(124, 5, 'User logged in', NULL, NULL, '2025-11-11 14:55:26'),
(125, 6, 'User logged in', NULL, NULL, '2025-11-12 06:09:01'),
(126, 6, 'User logged in', NULL, NULL, '2025-11-12 06:09:16'),
(127, 5, 'User logged in', NULL, NULL, '2025-11-12 06:09:34'),
(128, 5, 'Assigned task \'Syllabus\' to instructor ID 6', NULL, NULL, '2025-11-12 06:10:33'),
(129, 5, 'Assigned task \'Syllabus\' to instructor ID 6', NULL, NULL, '2025-11-12 06:11:29'),
(130, 6, 'User logged in', NULL, NULL, '2025-11-12 06:11:50'),
(131, 6, 'User logged in', NULL, NULL, '2025-11-12 06:14:35'),
(132, 5, 'User logged in', NULL, NULL, '2025-11-12 06:17:17'),
(133, 6, 'User logged in', NULL, NULL, '2025-11-12 07:12:49'),
(134, 5, 'User logged in', NULL, NULL, '2025-11-12 07:13:46'),
(135, 5, 'User logged in', NULL, NULL, '2025-11-12 07:14:52'),
(136, 6, 'User logged in', NULL, NULL, '2025-11-12 07:17:42'),
(137, 5, 'Assigned task \'Syllabus\' to instructor ID 6', NULL, NULL, '2025-11-12 07:19:19'),
(138, 6, 'User logged in', NULL, NULL, '2025-11-12 09:40:49'),
(139, 5, 'User logged in', NULL, NULL, '2025-11-12 10:36:13'),
(140, 5, 'Assigned task \'Syllabus\' to instructor ID 6', NULL, NULL, '2025-11-12 10:36:34'),
(141, 5, 'Assigned task \'Syllabus\' to instructor ID 6', NULL, NULL, '2025-11-12 10:41:59'),
(142, 5, 'Assigned task \'Syllabus\' to instructor ID 6', NULL, NULL, '2025-11-12 10:46:14'),
(143, 5, 'User logged in', NULL, NULL, '2025-11-12 11:15:34'),
(144, 1, 'User logged in', NULL, NULL, '2025-11-12 13:38:13'),
(145, 5, 'User logged in', NULL, NULL, '2025-11-12 13:38:49'),
(146, 5, 'User logged in', NULL, NULL, '2025-11-12 13:46:49'),
(147, 8, 'User logged in', NULL, NULL, '2025-11-12 13:48:55'),
(148, 1, 'User logged in', NULL, NULL, '2025-11-12 13:51:40'),
(149, 5, 'User logged in', NULL, NULL, '2025-11-12 13:52:23'),
(150, 8, 'User logged in', NULL, NULL, '2025-11-12 13:53:47'),
(151, 1, 'User logged in', NULL, NULL, '2025-11-12 13:57:45'),
(152, 5, 'User logged in', NULL, NULL, '2025-11-12 13:58:47'),
(153, 5, 'Assigned task \'Syllabus\' to instructor ID 8', NULL, NULL, '2025-11-12 13:59:10'),
(154, 1, 'User logged in', NULL, NULL, '2025-11-12 13:59:39'),
(155, 5, 'User logged in', NULL, NULL, '2025-11-12 14:09:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `task_history`
--
ALTER TABLE `task_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- Constraints for dumped tables
--

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
