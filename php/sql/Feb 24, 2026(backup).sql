-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2026 at 02:46 AM
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
-- Table structure for table `chat_views`
--

CREATE TABLE `chat_views` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `last_viewed` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `coordinator_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `coordinator_id`, `instructor_id`, `created_at`) VALUES
(1, 18, 22, '2025-11-15 19:06:05'),
(2, 21, 6, '2025-11-15 19:17:29'),
(3, 21, 19, '2025-11-15 19:19:35'),
(4, 21, 22, '2025-11-15 19:41:35'),
(5, 21, 11, '2025-11-15 19:47:55'),
(6, 5, 22, '2025-11-15 21:10:27');

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
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `receiver_id`, `message`, `sent_at`, `is_read`) VALUES
(2, 4, 21, 22, 'test', '2025-11-15 19:41:39', 0),
(3, 4, 22, 0, 'test', '2025-11-15 19:42:01', 0),
(4, 4, 21, 22, 'tes2', '2025-11-15 19:58:40', 0),
(5, 4, 22, 0, 'test notif', '2025-11-15 20:07:53', 0),
(6, 4, 22, 0, 'test notif2', '2025-11-15 20:12:55', 0),
(7, 4, 22, 0, 'test3', '2025-11-15 20:17:16', 0),
(8, 4, 22, 0, 'test4', '2025-11-15 20:19:33', 0),
(9, 4, 22, 0, 'test5', '2025-11-15 20:21:50', 0),
(10, 4, 22, 0, 'tes6', '2025-11-15 20:26:45', 0),
(11, 4, 22, 0, 'test7', '2025-11-15 20:26:50', 0),
(12, 4, 22, 0, 'test8', '2025-11-15 20:26:53', 0),
(13, 4, 22, 0, 'test9', '2025-11-15 20:35:07', 0),
(14, 4, 22, 0, 'test10', '2025-11-15 20:35:09', 0),
(15, 4, 22, 0, 'test11', '2025-11-15 20:35:11', 0),
(16, 4, 22, 0, 'test notif12', '2025-11-15 20:40:45', 0),
(17, 4, 22, 0, 'test notif13', '2025-11-15 20:48:03', 0),
(18, 4, 22, 0, 'test', '2025-11-15 20:57:23', 0),
(19, 4, 21, 22, 'test', '2025-11-15 21:25:27', 0),
(20, 4, 21, 22, 'test', '2025-11-15 21:25:44', 0),
(21, 4, 21, 22, 'https://drive.google.com/file/d/1GU2CcNJmnCn8upw_HHNou5aT6BHRWAiF/view', '2025-11-17 14:17:02', 0);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subj_id` int(11) NOT NULL,
  `subj_code` varchar(50) NOT NULL,
  `subj_num` varchar(50) NOT NULL,
  `subj_description` text DEFAULT NULL,
  `subj_units` decimal(4,2) NOT NULL DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subj_id`, `subj_code`, `subj_num`, `subj_description`, `subj_units`, `created_by`, `created_at`) VALUES
(1, 'ICS 84', '121', 'Capstone 2', 3.00, 22, '2025-12-14 23:47:43'),
(5, 'test', '12', 'test', 2.00, 22, '2025-12-15 00:52:54'),
(6, 'TEST notif', '232', 'notif test', 23.00, 22, '2025-12-15 01:06:25');

-- --------------------------------------------------------

--
-- Table structure for table `subject_assignments`
--

CREATE TABLE `subject_assignments` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `coordinator_id` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject_assignments`
--

INSERT INTO `subject_assignments` (`id`, `subject_id`, `instructor_id`, `coordinator_id`, `assigned_at`) VALUES
(4, 1, 22, 22, '2025-12-15 00:29:50'),
(6, 1, 1, 22, '2025-12-15 00:35:33'),
(7, 5, 1, 22, '2025-12-15 00:53:00');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
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
(4, 0, 'Upload Course Syllabus', 'Must upload your course syllabus by the end of the week.', 6, 5, '2025-09-01', '2025-09-05', '2025-2026', 'completed', '2025-09-01 15:55:53'),
(5, 0, 'test', 'test', 6, 5, '2025-09-10', '2025-09-12', '2025-2026', 'completed', '2025-09-10 05:30:28'),
(6, 0, 'syllabus', 'pag pasa lang', 6, 5, '2025-09-10', '2025-09-11', '2025-2026', 'completed', '2025-09-10 08:15:07'),
(7, 0, 'IT24 Syllabus', 'Pass the IT24 Syllabus', 6, 5, '2025-09-10', '2025-09-11', '2025-2026', 'completed', '2025-09-10 08:18:45'),
(11, 0, 'Syllabus', 'test\'\'', 9, 5, '2025-09-11', '2025-09-12', '2025-2026', 'completed', '2025-09-11 22:43:25'),
(13, 0, 'Syllabus', 'test', 11, 5, '2025-09-12', '2025-09-15', '2025-2026', 'pending', '2025-09-12 19:36:05'),
(14, 0, 'Syllabus', 'submit your syllabus', 6, 5, '2025-11-08', '2025-11-10', '2025-2026', 'completed', '2025-11-08 18:53:04'),
(16, 0, 'Syllabus', 'test', 6, 5, '2025-11-11', '2025-11-13', '2025-2026', 'completed', '2025-11-11 21:23:43'),
(18, 0, 'Syllabus', 'testing', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 14:10:33'),
(19, 0, 'Syllabus', 'test deadline notif', 6, 5, '2025-11-12', '2025-11-13', '2024-2025', 'completed', '2025-11-12 14:11:29'),
(20, 0, 'Syllabus', 'Important Task', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 15:19:19'),
(21, 0, 'Syllabus', 'test optional link', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 18:36:34'),
(22, 0, 'Syllabus', 'test 2(optional gdrive link)', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 18:41:59'),
(23, 0, 'Syllabus', 'test 3(order by)', 6, 5, '2025-11-12', '2025-11-14', '2024-2025', 'completed', '2025-11-12 18:46:14'),
(25, 0, 'Syllabus', 'test5', 9, 5, '2025-11-13', '2025-11-14', '2025-2026', 'completed', '2025-11-13 18:38:27'),
(26, 0, 'Syllabus', 'test -test@user', 19, 5, '2025-11-14', '2025-11-17', '2024-2025', 'completed', '2025-11-14 11:45:22'),
(27, 0, 'Syllabus', 'test_user3', 20, 5, '2025-11-14', '2025-11-18', '2024-2025', 'completed', '2025-11-14 11:52:27'),
(28, 0, 'Syllabus', 'Syllabus3', 20, 5, '2025-11-14', '2025-11-19', '2024-2025', 'completed', '2025-11-14 11:57:19'),
(29, 0, 'Syllabus', 'Syllabus4', 20, 5, '2025-11-14', '2025-11-20', '2024-2025', 'completed', '2025-11-14 11:57:50'),
(30, 0, 'Syllabus', 'Syllabus5', 20, 5, '2025-11-14', '2025-11-21', '2024-2025', 'completed', '2025-11-14 11:58:13'),
(31, 0, 'Syllabus', 'Syllabus6', 20, 5, '2025-11-14', '2025-11-24', '2024-2025', 'pending', '2025-11-14 12:00:42'),
(32, 0, 'Syllabus', 'test email notif', 22, 21, '2025-11-15', '2025-11-17', '2024-2025', 'completed', '2025-11-15 15:01:22'),
(33, 0, 'Syllabus', 'test email2', 22, 5, '2025-11-15', '2025-11-17', '2024-2025', 'pending', '2025-11-15 15:06:38'),
(34, 0, 'Syllabus', 'test(gmail notif)', 22, 5, '2025-11-15', '2025-11-16', '2024-2025', 'completed', '2025-11-15 15:22:27'),
(35, 0, 'Syllabus', 'test(email notif)', 22, 21, '2025-11-15', '2025-11-17', '2024-2025', 'pending', '2025-11-15 15:38:00'),
(36, 0, 'Syllabus', 'test4(email notif)', 22, 21, '2025-11-15', '2025-11-17', '2024-2025', 'pending', '2025-11-15 15:48:57'),
(37, 0, 'Syllabus', 'test5(email notif)', 22, 21, '2025-11-15', '2025-11-17', '2024-2025', 'pending', '2025-11-15 15:49:56'),
(38, 0, 'Syllabus', 'testing', 22, 22, '2025-12-04', '2025-12-05', '2024-2025', 'pending', '2025-12-04 21:43:44'),
(39, 0, 'Syllabus', 'test(self assign task(- coordinator', 22, 22, '2025-12-14', '2025-12-16', '2024-2025', 'pending', '2025-12-14 23:07:03'),
(40, 0, 'Syllabus', 'test', 1, 22, '2025-12-14', '2025-12-17', '2024-2025', 'completed', '2025-12-14 23:23:41');

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
(4, 5, '2025-09-10 05:31:12', '1757453472_Learning.pdf', NULL),
(5, 6, '2025-09-10 08:16:09', '1757463369_Learning.pdf', NULL),
(12, 4, '2025-11-08 18:26:09', 'https://drive.google.com/file/d/1GU2CcNJmnCn8upw_HHNou5aT6BHRWAiF/view', NULL),
(13, 14, '2025-11-12 18:05:44', '1762941944_Learning.pdf', NULL),
(14, 16, '2025-11-12 18:06:05', '1762941965_Learning.pdf', NULL),
(15, 19, '2025-11-12 18:23:19', '1762942999_Learning.pdf', NULL),
(16, 18, '2025-11-12 18:23:30', '1762943010_Learning.pdf', NULL),
(17, 20, '2025-11-12 18:27:13', '1762943233_Learning.pdf', NULL),
(18, 21, '2025-11-12 18:41:31', NULL, 'https://drive.google.com/file/d/1GU2CcNJmnCn8upw_HHNou5aT6BHRWAiF/view'),
(19, 22, '2025-11-12 18:42:22', '1762944142_Learning.pdf', NULL),
(20, 23, '2025-11-12 21:35:02', NULL, 'https://drive.google.com/file/d/1GU2CcNJmnCn8upw_HHNou5aT6BHRWAiF/view'),
(23, 25, '2025-11-13 18:38:47', '1763030327_Learning.pdf', NULL),
(25, 27, '2025-11-14 11:55:08', '1763092508_Learning.pdf', NULL),
(26, 28, '2025-11-14 11:58:47', '1763092727_Learning.pdf', NULL),
(27, 29, '2025-11-14 11:58:58', '1763092738_Learning.pdf', NULL),
(29, 40, '2025-12-14 23:24:28', '1765725868_1763283794_Learning.pdf', NULL),
(30, 34, '2025-12-15 01:33:29', '1765733609_Learning.pdf', NULL),
(31, 32, '2026-01-29 19:38:38', '1769686718_Learning.pdf', NULL);

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
  `is_approved` tinyint(1) DEFAULT 0,
  `otp_code` varchar(10) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `profile_image`, `password`, `role`, `status`, `created_at`, `is_approved`, `otp_code`, `reset_token`, `reset_expires`) VALUES
(1, 'Mickel Angelo Gaudicos', '20222356@nbsc.edu.ph', 'profile_1_1762779139.jpg', '$2y$10$OM8SBJi2QaE0kwBPcVIUhO0ZKgN5.zUqOWT5pTmZhAbxTfx/rTLMi', 'admin', 'pending', '2025-08-24 15:29:24', 0, NULL, NULL, NULL),
(5, 'Mr.George', 'coordinator@nbsc.edu.ph', 'profile_5_1762946127.jpg', '$2y$10$HXoRz2mNOGjSF/NbSupxxug5JzsBObpi1ifZG/iEBrCjndGpKmEUG', 'coordinator', 'pending', '2025-08-24 15:38:48', 0, NULL, NULL, NULL),
(6, 'instructor_2', 'instructor_2@nbsc.edu.ph', '1762945726_4174055.jpg', '$2y$10$Nnm9Uu3MGdSPN4JxI.6SGOdpNt4BsOWLjlQgrHXVR/p5VFFGoY4.a', 'instructor', 'active', '2025-09-01 15:47:20', 0, NULL, NULL, NULL),
(9, 'Kaneki Ken', 'instructor_5@nbsc.edu.ph', '1763030564_kaneki.jpg', '$2y$10$3.gE/9nhajZJKkiB6mnqFeoQxhQBMKiTrO9LvzHXSQSVVh1Sa4vFe', 'instructor', 'active', '2025-09-11 22:05:41', 0, NULL, NULL, NULL),
(11, 'Monkey D. Luffy', 'instructor_6@nbsc.edu.ph', NULL, '$2y$10$bh3UWeyQ9kyE3koZMJvMb.gIAwku5rmlMzH3luWzA8nD40g4BL.TS', 'instructor', 'active', '2025-09-11 22:06:29', 0, NULL, NULL, NULL),
(16, 'instructor_Example2', 'instructor_example@nbsc.edu.ph', NULL, '$2y$10$6A2iMKDIInCWlLg6p2XfA.vE4t4imSGZnbrSX99CeBDuwajgfLqiW', 'instructor', 'pending', '2025-10-21 11:59:12', 0, NULL, NULL, NULL),
(18, 'Cliff Amadeus Evangelio', 'cafevangelio@nbsc.edu.ph', NULL, '$2y$10$g.XNRlihM7dyVLDBQLQYx.sbxl3HKEwTqaBRFkLPZkyNWz..ofZ8K', 'coordinator', 'pending', '2025-11-11 14:28:55', 0, NULL, NULL, NULL),
(19, 'test_user', 'test_user@nbsc.edu.ph', NULL, '$2y$10$605ZTpK94nTwP8589o8Ohemu9XRd3DnIBuuk98Kll0RGaxxb86meC', 'instructor', 'active', '2025-11-14 11:43:40', 0, NULL, NULL, NULL),
(20, 'test_user3', 'test_user3@nbsc.edu.ph', NULL, '$2y$10$vh7vcN7Umk12y5J3jB1gO.A2Pcqa2Tm0lLeyu6kRzQ5//NxM4HN6m', 'instructor', 'active', '2025-11-14 11:49:50', 0, NULL, NULL, NULL),
(21, 'Frank Joseph Tabique', '20221807@nbsc.edu.ph', NULL, '$2y$10$isG7iW.p.hLN7KlbaQ7rYerPAMuPyS1kGifXc7Li8G1W1QU3zK6AS', 'coordinator', 'pending', '2025-11-15 14:38:28', 0, NULL, 'f6ab3e55a798e0c3cef0b4f2048a0f3dae4c7b75226b851cc8e5ca9653c0deb413195555e2167c9faab0f0210c6888bfe643', 1763360022),
(22, 'Aladdin Cagubcub', '20201069@nbsc.edu.ph', '1763207695_kaneki.jpg', '$2y$10$VEiod2BodptflxRFGyQ5Jut4mqFWPCVm0jR/AX1kkjO49VZcehCU6', 'coordinator', 'active', '2025-11-15 14:38:54', 0, NULL, NULL, NULL),
(23, 'Melvin Reyes', '20201041@nbsc.edu.ph', NULL, '$2y$10$7eo/pe9TyVrofmXwHhJkrul3c0U3tIF2yeO6g4X127.bIp7kKuPZu', 'instructor', 'active', '2025-11-15 14:45:42', 0, NULL, NULL, NULL);

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
(46, 6, 'User logged in', NULL, NULL, '2025-09-10 01:54:02'),
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
(59, 5, 'User logged in', NULL, NULL, '2025-09-11 14:30:09'),
(60, 5, 'Assigned task \'Syllabus\' to instructor ID 7', NULL, NULL, '2025-09-11 14:30:22'),
(61, 1, 'User logged in', NULL, NULL, '2025-09-11 14:30:32'),
(64, 5, 'User logged in', NULL, NULL, '2025-09-11 14:41:24'),
(65, 5, 'Assigned task \'Syllabus\' to instructor ID 8', NULL, NULL, '2025-09-11 14:41:40'),
(67, 5, 'User logged in', NULL, NULL, '2025-09-11 14:43:02'),
(68, 5, 'Assigned task \'Syllabus\' to instructor ID 9', NULL, NULL, '2025-09-11 14:43:25'),
(69, 9, 'User logged in', NULL, NULL, '2025-09-11 14:43:47'),
(70, 5, 'User logged in', NULL, NULL, '2025-09-11 14:47:45'),
(71, 5, 'Assigned task \'Syllabus\' to instructor ID 8', NULL, NULL, '2025-09-11 14:48:02'),
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
(148, 1, 'User logged in', NULL, NULL, '2025-11-12 13:51:40'),
(149, 5, 'User logged in', NULL, NULL, '2025-11-12 13:52:23'),
(151, 1, 'User logged in', NULL, NULL, '2025-11-12 13:57:45'),
(152, 5, 'User logged in', NULL, NULL, '2025-11-12 13:58:47'),
(153, 5, 'Assigned task \'Syllabus\' to instructor ID 8', NULL, NULL, '2025-11-12 13:59:10'),
(154, 1, 'User logged in', NULL, NULL, '2025-11-12 13:59:39'),
(155, 5, 'User logged in', NULL, NULL, '2025-11-12 14:09:39'),
(156, 5, 'User logged in', NULL, NULL, '2025-11-13 10:26:37'),
(157, 5, 'User logged in', NULL, NULL, '2025-11-13 10:32:36'),
(158, 9, 'User logged in', NULL, NULL, '2025-11-13 10:33:20'),
(159, 5, 'Assigned task \'Syllabus\' to instructor ID 9', NULL, NULL, '2025-11-13 10:38:27'),
(160, 1, 'User logged in', NULL, NULL, '2025-11-13 10:57:08'),
(161, 6, 'User logged in', NULL, NULL, '2025-11-14 03:41:57'),
(162, 19, 'User logged in', NULL, NULL, '2025-11-14 03:44:50'),
(163, 5, 'Assigned task \'Syllabus\' to instructor ID 19', NULL, NULL, '2025-11-14 03:45:22'),
(164, 19, 'User logged in', NULL, NULL, '2025-11-14 03:48:36'),
(165, 20, 'User logged in', NULL, NULL, '2025-11-14 03:51:10'),
(166, 5, 'Assigned task \'Syllabus\' to instructor ID 20', NULL, NULL, '2025-11-14 03:52:27'),
(167, 5, 'Updated profile', NULL, NULL, '2025-11-14 03:56:29'),
(168, 5, 'Assigned task \'Syllabus\' to instructor ID 20', NULL, NULL, '2025-11-14 03:57:19'),
(169, 5, 'Assigned task \'Syllabus\' to instructor ID 20', NULL, NULL, '2025-11-14 03:57:50'),
(170, 5, 'Assigned task \'Syllabus\' to instructor ID 20', NULL, NULL, '2025-11-14 03:58:13'),
(171, 5, 'Assigned task \'Syllabus\' to instructor ID 20', NULL, NULL, '2025-11-14 04:00:42'),
(172, 19, 'User logged in', NULL, NULL, '2025-11-14 04:06:19'),
(173, 19, 'User logged in', NULL, NULL, '2025-11-14 04:07:45'),
(174, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 05:40:12'),
(175, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 05:43:34'),
(176, 1, 'User logged in', NULL, NULL, '2025-11-15 05:46:48'),
(177, 1, 'User logged in', NULL, NULL, '2025-11-15 05:46:55'),
(178, 6, 'User logged in', NULL, NULL, '2025-11-15 05:47:25'),
(179, 1, 'User logged in', NULL, NULL, '2025-11-15 06:25:37'),
(180, 5, 'User logged in', NULL, NULL, '2025-11-15 06:25:47'),
(181, 1, 'User logged in', NULL, NULL, '2025-11-15 06:26:02'),
(182, 21, 'User logged in', NULL, NULL, '2025-11-15 07:00:49'),
(183, 21, 'Assigned task \'Syllabus\' to instructor ID 22', NULL, NULL, '2025-11-15 07:01:22'),
(184, 5, 'User logged in', NULL, NULL, '2025-11-15 07:06:19'),
(185, 5, 'Assigned task \'Syllabus\' to instructor ID 22', NULL, NULL, '2025-11-15 07:06:38'),
(186, 5, 'Assigned task \'Syllabus\' to instructor ID 22', NULL, NULL, '2025-11-15 07:22:27'),
(187, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 07:34:32'),
(188, 21, 'User logged in via 2FA', NULL, NULL, '2025-11-15 07:37:39'),
(189, 21, 'Assigned task \'Syllabus\' to instructor ID 22', NULL, NULL, '2025-11-15 07:38:00'),
(190, 21, 'Assigned task \'Syllabus\' to instructor ID 22', NULL, NULL, '2025-11-15 07:48:57'),
(191, 21, 'Assigned task \'Syllabus\' to instructor ID 22', NULL, NULL, '2025-11-15 07:49:56'),
(192, 22, 'User logged in via 2FA', NULL, NULL, '2025-11-15 07:51:35'),
(193, 21, 'User logged in via 2FA', NULL, NULL, '2025-11-15 07:53:47'),
(194, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 07:54:20'),
(195, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 08:42:22'),
(196, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 09:07:02'),
(197, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 09:23:20'),
(198, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 09:49:43'),
(199, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 09:58:01'),
(200, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 10:21:28'),
(201, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 10:22:23'),
(202, 22, 'User logged in via 2FA', NULL, NULL, '2025-11-15 10:24:53'),
(203, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 10:25:21'),
(204, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-15 11:02:15'),
(205, 22, 'User logged in via 2FA', NULL, NULL, '2025-11-15 11:03:25'),
(206, 22, 'User logged in via 2FA', NULL, NULL, '2025-11-15 11:11:04'),
(207, 21, 'User logged in via 2FA', NULL, NULL, '2025-11-15 11:13:57'),
(208, 21, 'User logged in via 2FA', NULL, NULL, '2025-11-15 12:34:54'),
(209, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-16 08:09:27'),
(210, 22, 'User logged in via 2FA', NULL, NULL, '2025-11-16 11:49:34'),
(211, 21, 'User logged in via 2FA', NULL, NULL, '2025-11-16 11:59:12'),
(212, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-17 05:12:12'),
(213, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-17 05:15:47'),
(214, 21, 'User logged in via 2FA', NULL, NULL, '2025-11-17 06:15:11'),
(215, 22, 'User logged in via 2FA', NULL, NULL, '2025-11-17 06:17:50'),
(216, 21, 'User logged in via 2FA', NULL, NULL, '2025-11-17 06:23:26'),
(217, 1, 'User logged in via 2FA', NULL, NULL, '2025-11-17 06:26:55'),
(218, 22, 'User logged in via 2FA', NULL, NULL, '2025-11-20 06:55:44'),
(219, 1, 'User logged in via 2FA', NULL, NULL, '2025-12-04 12:51:32'),
(220, 22, 'User logged in via 2FA', NULL, NULL, '2025-12-04 13:21:57'),
(221, 22, 'Assigned task \'Syllabus\' to user ID 22', NULL, NULL, '2025-12-04 13:43:44'),
(222, 1, 'User logged in via 2FA', NULL, NULL, '2025-12-04 14:34:13'),
(223, 1, 'User logged in via 2FA', NULL, NULL, '2025-12-14 15:04:10'),
(224, 22, 'User logged in via 2FA', NULL, NULL, '2025-12-14 15:05:42'),
(225, 22, 'Assigned task \'Syllabus\' to user ID 22', NULL, NULL, '2025-12-14 15:07:03'),
(226, 1, 'User logged in via 2FA', NULL, NULL, '2025-12-14 15:13:06'),
(227, 22, 'Assigned task \'Syllabus\' to user ID 1', NULL, NULL, '2025-12-14 15:23:41'),
(228, 22, 'User logged in via 2FA', NULL, NULL, '2025-12-14 17:29:51'),
(229, 1, 'User logged in via 2FA', NULL, NULL, '2026-01-26 07:16:26'),
(230, 22, 'User logged in via 2FA', NULL, NULL, '2026-01-26 07:18:54'),
(231, 21, 'User logged in via 2FA', NULL, NULL, '2026-01-26 07:24:25'),
(232, 1, 'User logged in via 2FA', NULL, NULL, '2026-02-24 01:17:06'),
(233, 22, 'User logged in via 2FA', NULL, NULL, '2026-02-24 01:22:11'),
(234, 1, 'User logged in via 2FA', NULL, NULL, '2026-02-24 01:24:25'),
(235, 22, 'User logged in via 2FA', NULL, NULL, '2026-02-24 01:26:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_views`
--
ALTER TABLE `chat_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`instructor_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subj_id`),
  ADD UNIQUE KEY `unique_subj` (`subj_code`,`subj_num`);

--
-- Indexes for table `subject_assignments`
--
ALTER TABLE `subject_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assign` (`subject_id`,`instructor_id`),
  ADD KEY `fk_sa_instructor` (`instructor_id`),
  ADD KEY `fk_sa_coordinator` (`coordinator_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `tasks_ibfk_1` (`assigned_to`);

--
-- Indexes for table `task_history`
--
ALTER TABLE `task_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_history_ibfk_1` (`task_id`);

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
-- AUTO_INCREMENT for table `chat_views`
--
ALTER TABLE `chat_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subj_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subject_assignments`
--
ALTER TABLE `subject_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `task_history`
--
ALTER TABLE `task_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=236;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`);

--
-- Constraints for table `subject_assignments`
--
ALTER TABLE `subject_assignments`
  ADD CONSTRAINT `fk_sa_coordinator` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sa_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sa_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subj_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `task_history`
--
ALTER TABLE `task_history`
  ADD CONSTRAINT `task_history_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
