-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 03, 2024 at 10:49 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qualifying_exam`
--

-- --------------------------------------------------------

--
-- Table structure for table `coded_courses`
--

CREATE TABLE `coded_courses` (
  `course_id` int NOT NULL,
  `subject_code` varchar(100) NOT NULL,
  `subject_description` varchar(255) NOT NULL,
  `units` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `coded_courses`
--

INSERT INTO `coded_courses` (`course_id`, `subject_code`, `subject_description`, `units`) VALUES
(1, 'COMP 20033', 'Computer Programming 2', '3.00'),
(2, 'COMP 20013', 'Introduction to Computing', '3.00'),
(3, 'COMP 20023', 'Computer Programming 1', '3.00'),
(4, 'CWTS 10013', 'Civic Welfare Training Service 1', '3.00'),
(5, 'GEED 10053', 'Mathematics in the Modern World', '3.00'),
(6, 'GEED 10063', 'Purposive Communication', '3.00'),
(7, 'GEED 10103', 'Filipinolohiya at Pambansang Kaunlaran', '3.00'),
(8, 'PHED 10012', 'Physical Fitness and Self-Testing Activities', '2.00'),
(9, 'COMP 20043', 'Discrete Structures 1', '3.00'),
(10, 'CWTS 10023', 'Civic Welfare Training Service 2', '3.00'),
(11, 'GEED 10033', 'Readings in Philippine History', '3.00'),
(12, 'GEED 10113', 'Pagsasalin sa Kontekstong Filipino', '3.00'),
(13, 'GEED 20023', 'Politics, Governance and Citizenship', '3.00'),
(14, 'PHED 10022', 'Rhythmic Activities', '2.00'),
(15, 'COMP 20013', 'Introduction to Computing', '3.00'),
(16, 'COMP 20023', 'Computer Programming 1', '3.00'),
(17, 'PHED 20023', 'Physical Education 1', '2.00'),
(18, 'NSTP 20023', 'National Service Training Program 1', '3.00'),
(19, 'GEED 10083', 'Science, Technology and Society', '3.00'),
(20, 'GEED 10113', 'Intelektwaslisasyon ng Filipino sa ibat ibang Larangan', '3.00'),
(21, 'MATH 20333', 'Differential Calculus', '3.00'),
(22, 'GEED 10023', 'Understanding the Self', '3.00'),
(23, 'PHED 10022', 'Physical Education', '2.00'),
(24, 'NSTP 10023', 'National Service Training Program 2', '3.00');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `exam_id` int NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `description` text,
  `duration` varchar(50) NOT NULL,
  `schedule_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `folder_id` int DEFAULT NULL,
  `student_type` enum('tech','non-tech') DEFAULT NULL,
  `student_year` year DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`exam_id`, `exam_name`, `description`, `duration`, `schedule_date`, `created_at`, `folder_id`, `student_type`, `student_year`) VALUES
(6, 'CCIS Qualifying Exam', 'Qualifying Exam for Transferees, Ladderized, and Shiftees', '80', '2024-11-11', '2024-11-08 22:03:53', NULL, NULL, NULL),
(7, 'CCIS Qualifying Exam 2', 'Qualifying Exam for Transferees, Ladderized, and Shiftees', '80', '2024-11-14', '2024-11-09 07:32:37', NULL, NULL, NULL),
(8, 'fasf', 'fsa', '80', '2024-12-13', '2024-12-02 03:44:36', NULL, NULL, NULL),
(10, 'test', 'testtt', '90', '2024-12-07', '2024-12-02 10:17:33', NULL, 'non-tech', 2024),
(11, 'inside a folder', 'this is a file inside a folder ', '90', '2024-12-21', '2024-12-03 10:38:00', 1, 'tech', 2024);

-- --------------------------------------------------------

--
-- Table structure for table `exam_assignments`
--

CREATE TABLE `exam_assignments` (
  `assignment_id` int NOT NULL,
  `exam_id` int NOT NULL,
  `student_id` int NOT NULL,
  `assigned_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exam_assignments`
--

INSERT INTO `exam_assignments` (`assignment_id`, `exam_id`, `student_id`, `assigned_date`) VALUES
(6, 7, 44, '2024-11-09 07:34:55'),
(7, 10, 42, '2024-12-02 10:17:33'),
(8, 10, 51, '2024-12-02 10:17:33'),
(9, 10, 52, '2024-12-02 10:17:33'),
(10, 10, 53, '2024-12-02 10:17:33'),
(11, 10, 54, '2024-12-02 10:17:33'),
(12, 10, 55, '2024-12-02 10:17:33'),
(13, 10, 56, '2024-12-02 10:17:33'),
(14, 10, 57, '2024-12-02 10:17:33'),
(15, 10, 58, '2024-12-02 10:17:33'),
(16, 10, 59, '2024-12-02 10:17:33'),
(17, 10, 60, '2024-12-02 10:17:33'),
(18, 10, 61, '2024-12-02 10:17:33'),
(19, 10, 62, '2024-12-02 10:17:33'),
(20, 11, 43, '2024-12-03 10:38:00'),
(21, 11, 44, '2024-12-03 10:38:00'),
(22, 11, 45, '2024-12-03 10:38:00'),
(23, 11, 46, '2024-12-03 10:38:00'),
(24, 11, 48, '2024-12-03 10:38:00'),
(25, 11, 50, '2024-12-03 10:38:00');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

-- First drop the foreign keys if they exist
ALTER TABLE exam_results
DROP FOREIGN KEY IF EXISTS exam_results_ibfk_1;

ALTER TABLE exam_results
DROP FOREIGN KEY IF EXISTS exam_results_ibfk_2;

-- Now modify the table structure one change at a time
ALTER TABLE exam_results
    MODIFY result_id INT AUTO_INCREMENT,
    MODIFY exam_id INT NOT NULL,
    MODIFY student_id INT NOT NULL,
    MODIFY score INT DEFAULT 0;

-- Add total_questions column if it doesn't exist
ALTER TABLE exam_results
    ADD COLUMN IF NOT EXISTS total_questions INT NOT NULL AFTER score;

-- Add created_at column if it doesn't exist
ALTER TABLE exam_results
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Drop the columns we don't need anymore
ALTER TABLE exam_results
    DROP COLUMN IF EXISTS total_points,
    DROP COLUMN IF EXISTS start_time,
    DROP COLUMN IF EXISTS end_time,
    DROP COLUMN IF EXISTS completion_time,
    DROP COLUMN IF EXISTS status,
    DROP COLUMN IF EXISTS submission_date;

-- Add primary key constraint
ALTER TABLE exam_results
    ADD PRIMARY KEY (result_id);

-- Add back the foreign key constraints
ALTER TABLE exam_results
    ADD CONSTRAINT exam_results_ibfk_1 
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id) 
    ON DELETE CASCADE;

ALTER TABLE exam_results
    ADD CONSTRAINT exam_results_ibfk_2 
    FOREIGN KEY (student_id) REFERENCES students(student_id) 
    ON DELETE CASCADE;

-- Add indexes for better query performance
CREATE INDEX IF NOT EXISTS idx_exam_results_exam_id ON exam_results(exam_id);
CREATE INDEX IF NOT EXISTS idx_exam_results_student_id ON exam_results(student_id);
CREATE INDEX IF NOT EXISTS idx_exam_results_created_at ON exam_results(created_at);

-- --------------------------------------------------------

--
-- Table structure for table `exam_sections`
--

CREATE TABLE `exam_sections` (
  `section_id` int NOT NULL,
  `exam_id` int NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `section_description` text,
  `section_order` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exam_sections`
--

INSERT INTO `exam_sections` (`section_id`, `exam_id`, `section_title`, `section_description`, `section_order`, `created_at`, `updated_at`) VALUES
(5, 6, 'Multiple Choice', 'Select the Correct ANSWER', 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(6, 7, 'sample', 'sample', 1, '2024-11-09 07:34:05', '2024-11-09 07:34:05');

-- --------------------------------------------------------

--
-- Table structure for table `exam_settings`
--

CREATE TABLE `exam_settings` (
  `exam_id` int NOT NULL,
  `randomize_questions` tinyint(1) DEFAULT '0',
  `randomize_options` tinyint(1) DEFAULT '0',
  `allow_view_after` tinyint(1) DEFAULT '0',
  `time_limit` int DEFAULT NULL,
  `passing_score` int DEFAULT NULL,
  `show_results_immediately` tinyint(1) DEFAULT '0',
  `allow_retake` tinyint(1) DEFAULT '0',
  `max_attempts` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exam_settings`
--

INSERT INTO `exam_settings` (`exam_id`, `randomize_questions`, `randomize_options`, `allow_view_after`, `time_limit`, `passing_score`, `show_results_immediately`, `allow_retake`, `max_attempts`, `created_at`, `updated_at`) VALUES
(6, 0, 0, 0, NULL, NULL, 0, 0, 1, '2024-11-09 07:15:08', '2024-11-09 07:15:08'),
(7, 0, 0, 0, NULL, NULL, 0, 0, 1, '2024-11-09 07:34:20', '2024-11-09 07:34:20'),
(10, 0, 0, 0, NULL, NULL, 0, 0, 1, '2024-12-02 10:23:04', '2024-12-02 10:23:04');

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `folder_id` int NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_folder_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`folder_id`, `folder_name`, `created_at`, `parent_folder_id`) VALUES
(1, 'new folder', '2024-12-03 09:07:03', NULL),
(2, 'inside a folder ', '2024-12-03 10:41:07', NULL),
(3, 'this is inside', '2024-12-03 10:43:25', 1),
(4, 'this is inside the f', '2024-12-03 10:43:48', 1),
(5, 'another folder', '2024-12-03 10:45:48', 3);

-- --------------------------------------------------------

--
-- Table structure for table `matched_courses`
--

CREATE TABLE `matched_courses` (
  `matched_id` int NOT NULL,
  `subject_code` varchar(100) NOT NULL,
  `original_code` varchar(20) DEFAULT NULL,
  `subject_description` varchar(255) NOT NULL,
  `units` decimal(5,2) NOT NULL,
  `grade` decimal(4,2) DEFAULT NULL,
  `student_id` int NOT NULL,
  `matched_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `matched_courses`
--

INSERT INTO `matched_courses` (`matched_id`, `subject_code`, `original_code`, `subject_description`, `units`, `grade`, `student_id`, `matched_at`) VALUES
(85, 'COMP 20033', NULL, 'Computer Programming 2', '3.00', NULL, 43, '2024-11-08 21:45:59'),
(86, 'COMP 20043', NULL, 'Discrete Structures 1', '3.00', NULL, 43, '2024-11-08 21:45:59'),
(87, 'CWTS 10023', NULL, 'Civic Welfare Training Service 2', '3.00', NULL, 43, '2024-11-08 21:45:59'),
(88, 'GEED 10033', NULL, 'Readings in Philippine History', '3.00', NULL, 43, '2024-11-08 21:45:59'),
(89, 'GEED 10113', NULL, 'Pagsasalin sa Kontekstong Filipino', '3.00', NULL, 43, '2024-11-08 21:45:59'),
(90, 'GEED 20023', NULL, 'Politics, Governance and Citizenship', '3.00', NULL, 43, '2024-11-08 21:45:59'),
(91, 'COMP 20033', NULL, 'Computer Programming 2', '3.00', NULL, 44, '2024-11-08 21:50:44'),
(92, 'COMP 20043', NULL, 'Discrete Structures 1', '3.00', NULL, 44, '2024-11-08 21:50:44'),
(93, 'CWTS 10023', NULL, 'Civic Welfare Training Service 2', '3.00', NULL, 44, '2024-11-08 21:50:44'),
(94, 'GEED 10033', NULL, 'Readings in Philippine History', '3.00', NULL, 44, '2024-11-08 21:50:44'),
(95, 'GEED 10113', NULL, 'Pagsasalin sa Kontekstong Filipino', '3.00', NULL, 44, '2024-11-08 21:50:44'),
(96, 'GEED 20023', NULL, 'Politics, Governance and Citizenship', '3.00', NULL, 44, '2024-11-08 21:50:44'),
(97, 'COMP 20033', NULL, 'Computer Programming 2', '3.00', NULL, 45, '2024-11-09 07:26:19'),
(98, 'COMP 20043', NULL, 'Discrete Structures 1', '3.00', NULL, 45, '2024-11-09 07:26:19'),
(99, 'CWTS 10023', NULL, 'Civic Welfare Training Service 2', '3.00', NULL, 45, '2024-11-09 07:26:19'),
(100, 'GEED 10033', NULL, 'Readings in Philippine History', '3.00', NULL, 45, '2024-11-09 07:26:19'),
(101, 'GEED 10113', NULL, 'Pagsasalin sa Kontekstong Filipino', '3.00', NULL, 45, '2024-11-09 07:26:19'),
(102, 'GEED 20023', NULL, 'Politics, Governance and Citizenship', '3.00', NULL, 45, '2024-11-09 07:26:19'),
(103, 'COMP 20033', NULL, 'Computer Programming 2', '3.00', NULL, 46, '2024-11-09 07:28:49'),
(104, 'COMP 20043', NULL, 'Discrete Structures 1', '3.00', NULL, 46, '2024-11-09 07:28:49'),
(105, 'CWTS 10023', NULL, 'Civic Welfare Training Service 2', '3.00', NULL, 46, '2024-11-09 07:28:49'),
(106, 'GEED 10033', NULL, 'Readings in Philippine History', '3.00', NULL, 46, '2024-11-09 07:28:49'),
(107, 'GEED 10113', NULL, 'Pagsasalin sa Kontekstong Filipino', '3.00', NULL, 46, '2024-11-09 07:28:49'),
(108, 'GEED 20023', NULL, 'Politics, Governance and Citizenship', '3.00', NULL, 46, '2024-11-09 07:28:49'),
(109, 'COMP 20033', NULL, 'Computer Programming 2', '3.00', NULL, 48, '2024-11-09 07:30:45'),
(110, 'COMP 20043', NULL, 'Discrete Structures 1', '3.00', NULL, 48, '2024-11-09 07:30:45'),
(111, 'CWTS 10023', NULL, 'Civic Welfare Training Service 2', '3.00', NULL, 48, '2024-11-09 07:30:45'),
(112, 'GEED 10033', NULL, 'Readings in Philippine History', '3.00', NULL, 48, '2024-11-09 07:30:45'),
(113, 'GEED 10113', NULL, 'Pagsasalin sa Kontekstong Filipino', '3.00', NULL, 48, '2024-11-09 07:30:45'),
(114, 'GEED 20023', NULL, 'Politics, Governance and Citizenship', '3.00', NULL, 48, '2024-11-09 07:30:45'),
(115, 'COMP 20033', NULL, 'Computer Programming 2', '3.00', NULL, 50, '2024-11-09 13:14:07'),
(116, 'COMP 20043', NULL, 'Discrete Structures 1', '3.00', NULL, 50, '2024-11-09 13:14:07'),
(117, 'CWTS 10023', NULL, 'Civic Welfare Training Service 2', '3.00', NULL, 50, '2024-11-09 13:14:07'),
(118, 'GEED 10033', NULL, 'Readings in Philippine History', '3.00', NULL, 50, '2024-11-09 13:14:07'),
(119, 'GEED 10113', NULL, 'Pagsasalin sa Kontekstong Filipino', '3.00', NULL, 50, '2024-11-09 13:14:07'),
(120, 'GEED 20023', NULL, 'Politics, Governance and Citizenship', '3.00', NULL, 50, '2024-11-09 13:14:07'),
(121, 'GEED 10023', 'GEC 1000', 'Understanding the Self', '3.00', '1.00', 53, '2024-11-10 08:49:50'),
(122, 'GEED 10023', 'GEC 1000', 'Understanding the Self', '3.00', '1.00', 54, '2024-11-10 08:58:32'),
(123, 'GEED 10023', 'ENG 1000', 'Understanding the Self', '3.00', '1.25', 56, '2024-11-10 09:28:28'),
(124, 'PHED 10022', 'RZL 1000', 'Rhythmic Activities', '2.00', '1.00', 56, '2024-11-10 09:28:28'),
(125, 'GEED 10023', 'GEC 1000', 'Understanding the Self', '3.00', '1.00', 57, '2024-11-10 09:32:36'),
(126, 'GEED 10053', 'GEC 4000', 'Mathematics in the Modern World', '3.00', '1.00', 57, '2024-11-10 09:32:36'),
(127, 'NSTP 10023', 'NSTP2-M', 'National Service Training Program 2', '3.00', '1.50', 62, '2024-11-10 13:36:55');

-- --------------------------------------------------------

--
-- Table structure for table `multiple_choice_options`
--

CREATE TABLE `multiple_choice_options` (
  `option_id` int NOT NULL,
  `question_id` int NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `option_order` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `multiple_choice_options`
--

INSERT INTO `multiple_choice_options` (`option_id`, `question_id`, `option_text`, `is_correct`, `option_order`, `created_at`, `updated_at`) VALUES
(16, 10, 'Central Power Unit', 0, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(17, 10, 'Central Processing Unit', 1, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(18, 10, 'Computer Peripheral Unit', 0, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(19, 10, 'Core Processing Unit', 0, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(20, 11, 'macOS', 0, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(21, 11, 'Linux', 0, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(22, 11, 'Android', 0, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(23, 11, 'Windows', 1, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(24, 12, 'Data storage', 1, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(25, 12, 'Processing information', 0, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(26, 12, 'Power supply', 0, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(27, 12, 'Network connectivity', 0, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(28, 13, 'Java', 0, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(29, 13, 'Python', 0, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(30, 13, 'HTML/CSS', 0, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(31, 13, 'All of the above', 1, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(32, 14, 'Data storage', 0, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(33, 14, 'Network optimization', 0, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(34, 14, 'Protecting against cyber threats', 1, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(35, 14, 'Software development', 0, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(36, 15, 'Local storage', 0, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(37, 15, 'Virtual storage', 0, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(38, 15, 'On-demand online storage', 1, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(39, 15, 'Offline storage', 0, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(40, 16, 'MySQL', 0, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(41, 16, 'MongoDB', 0, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(42, 16, 'Oracle', 0, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(43, 16, 'All of the above', 1, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(44, 17, 'Human intelligence', 0, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(45, 17, 'Machine learning', 0, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(46, 17, 'Robotics', 0, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(47, 17, 'Simulation of human intelligence', 1, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(48, 18, 'HTTP', 0, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(49, 18, 'FTP', 0, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(50, 18, 'HTTPS', 1, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(51, 18, 'SSH', 0, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(52, 19, 'Network optimization', 0, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(53, 19, 'Data encryption', 0, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(54, 19, 'Blocking unauthorized access', 1, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(55, 19, 'Virus scanning', 0, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(60, 22, 'Three', 0, 0, '2024-12-02 07:24:17', '2024-12-02 07:24:17'),
(61, 22, 'Four', 1, 1, '2024-12-02 07:24:17', '2024-12-02 07:24:17'),
(62, 22, 'Five', 0, 2, '2024-12-02 07:24:17', '2024-12-02 07:24:17'),
(63, 22, 'Six', 0, 3, '2024-12-02 07:24:17', '2024-12-02 07:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `programming_languages`
--

CREATE TABLE `programming_languages` (
  `language_id` int NOT NULL,
  `question_id` int NOT NULL,
  `language_name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int NOT NULL,
  `section_id` int NOT NULL,
  `exam_id` int NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false','programming') NOT NULL,
  `points` int NOT NULL DEFAULT '1',
  `question_order` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `section_id`, `exam_id`, `question_text`, `question_type`, `points`, `question_order`, `created_at`, `updated_at`) VALUES
(10, 5, 6, 'What does CPU stand for?\r\n', 'multiple_choice', 1, 0, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(11, 5, 6, 'Which operating system is developed by Microsoft?', 'multiple_choice', 1, 1, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(12, 5, 6, 'What is the primary function of RAM?', 'multiple_choice', 1, 2, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(13, 5, 6, 'Which programming language is used for web development?', 'multiple_choice', 1, 3, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(14, 5, 6, 'What is cybersecurity\'s main goal?', 'multiple_choice', 1, 4, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(15, 5, 6, 'What is cloud computing?', 'multiple_choice', 1, 5, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(16, 5, 6, 'Which database management system is widely used?', 'multiple_choice', 1, 6, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(17, 5, 6, 'What is artificial intelligence (AI)?', 'multiple_choice', 1, 7, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(18, 5, 6, 'Which network protocol is used for secure communication?', 'multiple_choice', 1, 8, '2024-11-08 22:16:20', '2024-11-08 22:16:20'),
(19, 5, 6, 'What is the purpose of a firewall?', 'multiple_choice', 1, 9, '2024-11-08 22:16:20', '2024-11-09 07:14:24'),
(21, 6, 7, 'sample', 'multiple_choice', 1, 0, '2024-12-02 07:24:17', '2024-12-02 07:24:17'),
(22, 6, 7, 'What is 2 + 2?', 'multiple_choice', 1, 1, '2024-12-02 07:24:17', '2024-12-02 07:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `question_bank`
--

CREATE TABLE `question_bank` (
  `question_id` int NOT NULL,
  `category` varchar(255) NOT NULL,
  `question_type` varchar(50) DEFAULT NULL,
  `question_text` text NOT NULL,
  `correct_answer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `question_bank`
--

INSERT INTO `question_bank` (`question_id`, `category`, `question_type`, `question_text`, `correct_answer`, `created_at`, `updated_at`) VALUES
(10, 'Non-Tech Questions', 'true_false', 'ggggg', NULL, '2024-11-28 10:25:43', '2024-11-28 10:25:43'),
(11, 'Non-Tech Questions', 'true_false', 'ggggg', 'false', '2024-11-28 10:25:43', '2024-11-28 10:25:43'),
(12, 'Non-Tech Questions', 'true_false', 'trutru', 'True', '2024-11-28 10:29:11', '2024-11-28 10:29:11'),
(57, 'Non-Tech Questions', 'multiple_choice', 'What is 2 + 2?', NULL, '2024-11-30 09:28:06', '2024-11-30 09:28:06'),
(58, 'Non-Tech Questions', 'true_false', 'The sky is blue.', NULL, '2024-11-30 09:28:06', '2024-11-30 09:28:06'),
(59, 'testing', 'multiple_choice', 'What is 2 + 2?', NULL, '2024-11-30 09:38:40', '2024-11-30 09:38:40'),
(60, 'testing', 'true_false', 'The sky is blue.', NULL, '2024-11-30 09:38:40', '2024-11-30 09:38:40'),
(67, 'with programming', 'multiple_choice', 'What is 2 + 2?', NULL, '2024-11-30 09:49:07', '2024-11-30 09:49:07'),
(68, 'with programming', 'true_false', 'The sky is blue.', NULL, '2024-11-30 09:49:07', '2024-11-30 09:49:07'),
(69, 'with programming', 'programming', 'Write a function that adds two numbers', NULL, '2024-11-30 09:49:07', '2024-11-30 09:49:07'),
(70, 'Non-Tech Questions', 'multiple_choice', 'dfd', NULL, '2024-12-02 12:37:28', '2024-12-02 12:37:28');

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_answers`
--

CREATE TABLE `question_bank_answers` (
  `answer_id` int NOT NULL,
  `question_id` int DEFAULT NULL,
  `answer_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_choices`
--

CREATE TABLE `question_bank_choices` (
  `choice_id` int NOT NULL,
  `question_id` int DEFAULT NULL,
  `choice_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `question_bank_choices`
--

INSERT INTO `question_bank_choices` (`choice_id`, `question_id`, `choice_text`, `is_correct`) VALUES
(115, 57, 'Three', 0),
(116, 57, 'Four', 1),
(117, 57, 'Five', 0),
(118, 57, 'Six', 0),
(119, 58, 'True', 1),
(120, 58, 'False', 0),
(121, 59, 'Three', 0),
(122, 59, 'Four', 1),
(123, 59, 'Five', 0),
(124, 59, 'Six', 0),
(125, 60, 'True', 1),
(126, 60, 'False', 0),
(145, 67, 'Three', 0),
(146, 67, 'Four', 1),
(147, 67, 'Five', 0),
(148, 67, 'Six', 0),
(149, 68, 'True', 1),
(150, 68, 'False', 0),
(151, 70, 'A', 1),
(152, 70, 'B', 0),
(153, 70, 'C', 0),
(154, 70, 'D', 0);

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_programming`
--

CREATE TABLE `question_bank_programming` (
  `id` int NOT NULL,
  `question_id` int DEFAULT NULL,
  `programming_language` varchar(50) NOT NULL,
  `problem_description` text NOT NULL,
  `input_format` text NOT NULL,
  `output_format` text NOT NULL,
  `constraints` text NOT NULL,
  `solution_template` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `question_bank_programming`
--

INSERT INTO `question_bank_programming` (`id`, `question_id`, `programming_language`, `problem_description`, `input_format`, `output_format`, `constraints`, `solution_template`) VALUES
(1, 69, 'python', 'Create a function called addNumbers that takes two parameters and returns their sum', 'Two integers a and b, one per line', 'Single integer - the sum of a and b', '-100 <= a,b <= 100', 'def addNumbers(a, b):\\n    # Your code here\\n    pass');

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_test_cases`
--

CREATE TABLE `question_bank_test_cases` (
  `id` int NOT NULL,
  `question_id` int DEFAULT NULL,
  `test_input` text NOT NULL,
  `expected_output` text NOT NULL,
  `explanation` text,
  `is_hidden` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `question_bank_test_cases`
--

INSERT INTO `question_bank_test_cases` (`id`, `question_id`, `test_input`, `expected_output`, `explanation`, `is_hidden`) VALUES
(1, 69, '5\\n3', '8', '5 + 3 = 8', 0),
(2, 69, '-2\\n7', '5', '-2 + 7 = 5', 0),
(3, 69, '100\\n-50', '50', '', 1),
(4, 69, '-100\\n-100', '-200', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `student_type` varchar(100) DEFAULT NULL,
  `previous_school` varchar(255) DEFAULT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `previous_program` varchar(255) DEFAULT NULL,
  `desired_program` varchar(255) DEFAULT NULL,
  `tor` varchar(255) DEFAULT NULL,
  `school_id` varchar(255) DEFAULT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  `is_tech` tinyint(1) DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `last_name`, `first_name`, `middle_name`, `gender`, `dob`, `email`, `contact_number`, `street`, `student_type`, `previous_school`, `year_level`, `previous_program`, `desired_program`, `tor`, `school_id`, `reference_id`, `is_tech`, `registration_date`) VALUES
(42, 'Santos', 'Juan', 'Dela Cruz', 'Male', '2002-03-15', 'juansantos2323@gmail.com', '09667311333', '123 Example Street, Quezon City, Metro Manila', 'transferee', 'University of Perpetual', '1', 'Bachelor of Arts in Communication Research (ABCR)', 'BSIT', 'uploads/tor/Screenshot 2024-10-27 204757.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-13392', 0, '2024-11-08 13:00:21'),
(43, 'Reyes', 'Maria', 'Santiago', 'Female', '2001-06-25', 'maria.reyes@gmail.com', '09181234567', '456 Example Ave, Makati City, Metro Manila', 'shiftee', 'Polytechnic University of the Philippines', '1', 'Bachelor of Science in Information Technology (BSIT)', 'BSCS', 'uploads/tor/Screenshot 2024-10-18 080627.png', 'uploads/school_id/449467909_1205163000647861_8408110911620157242_n.png', 'CCIS-2024-36410', 1, '2024-11-08 21:45:56'),
(44, 'Dela Cruz', 'Carlos', 'Mendoza', 'Male', '2000-12-10', 'delacruz@gmail.com', '09191234567', '789 Example Blvd, Pasig City, Metro Manila', 'transferee', 'Technological University of the Philippines', '1', 'Bachelor of Science in Information Technology (BSIT)', 'BSIT', 'uploads/tor/Screenshot 2024-10-18 080627.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-83850', 1, '2024-11-08 21:50:41'),
(45, 'Dugo', 'Janlloyd', 'Yamoyam', 'Male', '2003-01-01', 'jdugo23@gmail.com', '09667311956', 'C Raymundo Ave', 'transferee', 'Polytechnic University of the Philippines', '1', 'Bachelor of Science in Information Technology (BSIT)', 'BSIT', 'uploads/tor/Screenshot 2024-10-18 080627.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-41512', 1, '2024-11-09 07:26:19'),
(46, 'Dugo', 'Janlloyd', 'Yamoyam', 'Male', '2003-01-01', 'janlloyddugo101@gmail.com', '09667311956', 'C Raymundo Ave', 'transferee', 'Polytechnic University of the Philippines', '1', 'Bachelor of Science in Information Technology (BSIT)', 'BSIT', 'uploads/tor/Screenshot 2024-10-18 080627.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-11207', 1, '2024-11-09 07:28:28'),
(48, 'Dugo', 'Janlloyd', 'Yamoyam', 'Male', '2024-11-21', 'janlloyddugo11@gmail.com', '09667311956', 'C Raymundo Ave', 'transferee', 'Polytechnic University of the Philippines', '1', 'Bachelor of Science in Information Technology (BSIT)', 'BSIT', 'uploads/tor/Screenshot 2024-10-18 080627.png', 'uploads/school_id/449467909_1205163000647861_8408110911620157242_n.png', 'CCIS-2024-30169', 1, '2024-11-09 07:30:24'),
(50, 'dfadsdsa', 'fasdfsa', 'asfdsdaf', 'Male', '2003-01-01', 'janlloyddugo22@gmail.com', '09667311888', 'C Raymundo Ave', 'transferee', 'Polytechnic University of the Philippines', '1', 'Bachelor of Science in Information Technology (BSIT)', 'BSIT', 'uploads/tor/Screenshot 2024-10-18 080627.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-37380', 1, '2024-11-09 13:14:03'),
(51, 'ffadsfas', 'fasdfasf', 'asfasfs', 'Female', '2002-01-01', 'janlloyddugo17@gmail.com', '09667311122', 'C Raymundo Ave 1', 'transferee', 'University of Perpetual', '1', 'Bachelor of Science in Entrepreneurship (BSEntrep)', 'BSIT', 'uploads/tor/Screenshot 2024-11-08 094621.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-75947', 0, '2024-11-10 08:32:00'),
(52, 'Dugo', 'Janlloyd', 'sdfasfsdf', 'Female', '2002-01-01', 'janlloyddugo244@gmail.com', '09667311122', 'C Raymundo Ave', 'transferee', 'University of Perpetual', '1', 'Bachelor of Arts in Journalism (ABJ)', 'BSIT', 'uploads/tor/Screenshot 2024-11-08 094621.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-04146', 0, '2024-11-10 08:35:36'),
(53, 'Dugo', 'Janlloyd', 'Yamoyam', 'Male', '2003-01-01', 'janlloyddugo111@gmail.com', '09667311888', 'C Raymundo Ave', 'transferee', 'University of Perpetual', '1', 'Bachelor of Science in Business Administration major in Human Resource Development Management (BSBA-HRDM)', 'BSIT', 'uploads/tor/Screenshot 2024-11-08 094621.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-85771', 0, '2024-11-10 08:49:47'),
(54, 'Dugo', 'Janlloyd', 'Yamoyam', 'Male', '2003-02-01', 'janlloyddugo222@gmail.com', '09667311888', 'C Raymundo Ave', 'transferee', 'University of Perpetual', '1', 'Bachelor in Advertising and Public Relation (BAPR)', 'BSIT', 'uploads/tor/Screenshot 2024-11-08 094621.png', 'uploads/school_id/449467909_1205163000647861_8408110911620157242_n.png', 'CCIS-2024-94142', 0, '2024-11-10 08:58:29'),
(55, 'Dugo', 'Janlloyd', 'Dela Cruz', 'Male', '2001-02-01', 'janlloyddugo109@gmail.com', '09667311122', 'C Raymundo Ave', 'transferee', 'University of Perpetual', '1', 'Bachelor of Arts in Journalism (ABJ)', 'BSIT', 'uploads/tor/Screenshot 2024-11-08 094621.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-02113', 0, '2024-11-10 09:24:56'),
(56, 'Dugo', 'Janlloyd', 'Santiagosdaf', 'Female', '2001-01-01', 'janlloyddugo82@gmail.com', '09667311888', 'C Raymundo Ave', 'transferee', 'University of Perpetual', '1', 'Bachelor of Science in Office Administration (BSOA)', 'BSIT', 'uploads/tor/Screenshot 2024-11-08 094621.png', 'uploads/school_id/449467909_1205163000647861_8408110911620157242_n.png', 'CCIS-2024-08378', 0, '2024-11-10 09:28:25'),
(57, 'fdasdfsadfsadf', 'fasdfadf', 'sdfasfsdf', 'Female', '2003-01-01', 'janlloyddugo133@gmail.com', '09667311332', 'C Raymundo Ave', 'transferee', 'University of Perpetual', '1', 'Bachelor of Arts in Communication Research (ABCR)', 'BSIT', 'uploads/tor/Screenshot 2024-11-08 094621.png', 'uploads/school_id/449292499_481139171214456_1108316609890498134_n.png', 'CCIS-2024-28974', 0, '2024-11-10 09:32:33'),
(58, 'fsf', 'sdfs', 'gsdf', 'Male', '2003-01-01', 'janlloyddugo114@gmail.com', '09667311888', 'C Raymundo Ave', 'transferee', 'Technological University of the Philippines', '1', 'Bachelor of Science in Business Administration major in Human Resource Development Management (BSBA-HRDM)', 'BSIT', 'uploads/tor/3eec681b-c02d-4463-8a52-f45acd08cc2a.jpg', 'uploads/school_id/Educational Post Botox vs. Fillers Explained.jpg', 'CCIS-2024-48560', 0, '2024-11-10 11:26:40'),
(59, 'Dugo', 'Janlloyd', 'fdsa', 'Male', '2003-01-01', 'janlloyddugo242@gmail.com', '09667311122', 'C Raymundo Ave', 'transferee', 'Technological University of the Philippines', '1', 'Bachelor in Advertising and Public Relation (BAPR)', 'BSIT', 'uploads/tor/3eec681b-c02d-4463-8a52-f45acd08cc2a.jpg', 'uploads/school_id/0b5b1c84-9a20-425e-8997-cba22f841adb.jpg', 'CCIS-2024-22033', 0, '2024-11-10 11:32:34'),
(60, 'Dugo', 'Janlloyd', 'Santiago', 'Male', '2001-01-01', 'janlloyddugo177@gmail.com', '09667311332', 'C Raymundo Ave', 'transferee', 'Technological University of the Philippines', '1', 'Bachelor of Science in Office Administration (BSOA)', 'BSIT', 'uploads/tor/3eec681b-c02d-4463-8a52-f45acd08cc2a.jpg', 'uploads/school_id/0b5b1c84-9a20-425e-8997-cba22f841adb.jpg', 'CCIS-2024-10229', 0, '2024-11-10 11:38:08'),
(61, 'Dugo', 'Janlloyd', 'sdfasfsdf', 'Male', '2002-01-01', 'janlloyddugo252@gmail.com', '09667311888', 'C Raymundo Ave', 'transferee', 'Technological University of the Philippines', '1', 'Bachelor in Advertising and Public Relation (BAPR)', 'BSIT', 'uploads/tor/3eec681b-c02d-4463-8a52-f45acd08cc2a.jpg', 'uploads/school_id/Educational Post Botox vs. Fillers Explained.jpg', 'CCIS-2024-60995', 0, '2024-11-10 11:47:12'),
(62, 'Dugo', 'Janlloyd', 'dasf', 'Male', '2001-01-01', 'janlloyddugo141@gmail.com', '09667311332', 'C Raymundo Ave', 'transferee', 'Technological University of the Philippines', '1', 'Bachelor in Advertising and Public Relation (BAPR)', 'BSIT', 'uploads/tor/3c58debb-5663-4974-a303-cdf2b74f8500.jpg', 'uploads/school_id/0b5b1c84-9a20-425e-8997-cba22f841adb.jpg', 'CCIS-2024-50272', 0, '2024-11-10 13:36:53');

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

CREATE TABLE `student_answers` (
  `answer_id` int NOT NULL,
  `result_id` int NOT NULL,
  `question_id` int NOT NULL,
  `student_answer` text,
  `is_correct` tinyint(1) DEFAULT '0',
  `points_earned` int DEFAULT '0',
  `submission_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `test_cases`
--

CREATE TABLE `test_cases` (
  `test_case_id` int NOT NULL,
  `question_id` int NOT NULL,
  `input_data` text,
  `expected_output` text NOT NULL,
  `test_case_order` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university_grading_systems`
--

CREATE TABLE `university_grading_systems` (
  `grading_id` int NOT NULL,
  `university_name` varchar(255) NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `grade_value` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `university_grading_systems`
--

INSERT INTO `university_grading_systems` (`grading_id`, `university_name`, `min_percentage`, `max_percentage`, `grade_value`) VALUES
(1, 'Polytechnic University of the Philippines', '97.00', '100.00', '1.00'),
(2, 'Polytechnic University of the Philippines', '94.00', '96.00', '1.25'),
(3, 'Polytechnic University of the Philippines', '91.00', '93.00', '1.50'),
(4, 'Polytechnic University of the Philippines', '88.00', '90.00', '1.75'),
(5, 'Polytechnic University of the Philippines', '85.00', '87.00', '2.00'),
(6, 'Polytechnic University of the Philippines', '82.00', '84.00', '2.25'),
(7, 'Polytechnic University of the Philippines', '79.00', '81.00', '2.50'),
(8, 'Polytechnic University of the Philippines', '76.00', '78.00', '2.75'),
(9, 'Polytechnic University of the Philippines', '75.00', '75.00', '3.00'),
(10, 'Polytechnic University of the Philippines', '65.00', '74.00', '4.00'),
(11, 'AMA University', '96.00', '100.00', 'A+'),
(12, 'AMA University', '91.00', '95.00', 'A'),
(13, 'AMA University', '86.00', '90.00', 'A-'),
(14, 'AMA University', '81.00', '85.00', 'B+'),
(15, 'AMA University', '75.00', '80.00', 'B'),
(16, 'AMA University', '69.00', '74.00', 'B-'),
(17, 'AMA University', '63.00', '68.00', 'C+'),
(18, 'AMA University', '57.00', '62.00', 'C'),
(19, 'AMA University', '50.00', '56.00', 'C-'),
(20, 'Technological University of the Philippines', '99.00', '100.00', '1.00'),
(21, 'Technological University of the Philippines', '96.00', '98.00', '1.25'),
(22, 'Technological University of the Philippines', '93.00', '95.00', '1.50'),
(23, 'Technological University of the Philippines', '90.00', '92.00', '1.75'),
(24, 'Technological University of the Philippines', '87.00', '89.00', '2.00'),
(25, 'Technological University of the Philippines', '84.00', '86.00', '2.25'),
(26, 'Technological University of the Philippines', '81.00', '83.00', '2.50'),
(27, 'Technological University of the Philippines', '78.00', '80.00', '2.75'),
(28, 'Technological University of the Philippines', '75.00', '77.00', '3.00'),
(29, 'University of Perpetual', '99.00', '100.00', '1.00'),
(30, 'University of Perpetual', '96.00', '98.00', '1.25'),
(31, 'University of Perpetual', '93.00', '95.00', '1.5'),
(32, 'University of Perpetual', '90.00', '92.00', '1.75'),
(33, 'University of Perpetual', '87.00', '89.00', '2.0'),
(34, 'University of Perpetual', '84.00', '86.00', '2.25'),
(35, 'University of Perpetual', '81.00', '83.00', '2.5'),
(36, 'University of Perpetual', '78.00', '80.00', '2.75'),
(37, 'University of Perpetual', '75.00', '77.00', '3.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` VARCHAR(20) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `created_at`, `role`) VALUES
(1, 'ccisfaculty@gmail.com', '$2y$10$sLZ.yEME5ua6u3q53AgxXulQtpNtcpFyzFhjajVDTTCAaLeftu.Ni', '2024-11-01 15:15:30', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coded_courses`
--
ALTER TABLE `coded_courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `folder_id` (`folder_id`);

--
-- Indexes for table `exam_assignments`
--
ALTER TABLE `exam_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_assignment` (`exam_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `exam_sections`
--
ALTER TABLE `exam_sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `exam_settings`
--
ALTER TABLE `exam_settings`
  ADD PRIMARY KEY (`exam_id`);

--
-- Indexes for table `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`folder_id`),
  ADD KEY `parent_folder_id` (`parent_folder_id`);

--
-- Indexes for table `matched_courses`
--
ALTER TABLE `matched_courses`
  ADD PRIMARY KEY (`matched_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `multiple_choice_options`
--
ALTER TABLE `multiple_choice_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `programming_languages`
--
ALTER TABLE `programming_languages`
  ADD PRIMARY KEY (`language_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `question_bank`
--
ALTER TABLE `question_bank`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `question_bank_answers`
--
ALTER TABLE `question_bank_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `question_bank_choices`
--
ALTER TABLE `question_bank_choices`
  ADD PRIMARY KEY (`choice_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `question_bank_programming`
--
ALTER TABLE `question_bank_programming`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `question_bank_test_cases`
--
ALTER TABLE `question_bank_test_cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reference_id` (`reference_id`);

--
-- Indexes for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `result_id` (`result_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `test_cases`
--
ALTER TABLE `test_cases`
  ADD PRIMARY KEY (`test_case_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `university_grading_systems`
--
ALTER TABLE `university_grading_systems`
  ADD PRIMARY KEY (`grading_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coded_courses`
--
ALTER TABLE `coded_courses`
  MODIFY `course_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `exam_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `exam_assignments`
--
ALTER TABLE `exam_assignments`
  MODIFY `assignment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `result_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `exam_sections`
--
ALTER TABLE `exam_sections`
  MODIFY `section_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `folder_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `matched_courses`
--
ALTER TABLE `matched_courses`
  MODIFY `matched_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `multiple_choice_options`
--
ALTER TABLE `multiple_choice_options`
  MODIFY `option_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `programming_languages`
--
ALTER TABLE `programming_languages`
  MODIFY `language_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `question_bank`
--
ALTER TABLE `question_bank`
  MODIFY `question_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `question_bank_answers`
--
ALTER TABLE `question_bank_answers`
  MODIFY `answer_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_choices`
--
ALTER TABLE `question_bank_choices`
  MODIFY `choice_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `question_bank_programming`
--
ALTER TABLE `question_bank_programming`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `question_bank_test_cases`
--
ALTER TABLE `question_bank_test_cases`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `student_answers`
--
ALTER TABLE `student_answers`
  MODIFY `answer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `test_cases`
--
ALTER TABLE `test_cases`
  MODIFY `test_case_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `university_grading_systems`
--
ALTER TABLE `university_grading_systems`
  MODIFY `grading_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`folder_id`);

--
-- Constraints for table `exam_assignments`
--
ALTER TABLE `exam_assignments`
  ADD CONSTRAINT `exam_assignments_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_assignments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_results_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_sections`
--
ALTER TABLE `exam_sections`
  ADD CONSTRAINT `exam_sections_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_settings`
--
ALTER TABLE `exam_settings`
  ADD CONSTRAINT `exam_settings_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`parent_folder_id`) REFERENCES `folders` (`folder_id`) ON DELETE CASCADE;

--
-- Constraints for table `matched_courses`
--
ALTER TABLE `matched_courses`
  ADD CONSTRAINT `matched_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `multiple_choice_options`
--
ALTER TABLE `multiple_choice_options`
  ADD CONSTRAINT `multiple_choice_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `programming_languages`
--
ALTER TABLE `programming_languages`
  ADD CONSTRAINT `programming_languages_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `exam_sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `question_bank_answers`
--
ALTER TABLE `question_bank_answers`
  ADD CONSTRAINT `question_bank_answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `question_bank` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `question_bank_choices`
--
ALTER TABLE `question_bank_choices`
  ADD CONSTRAINT `question_bank_choices_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `question_bank` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `question_bank_programming`
--
ALTER TABLE `question_bank_programming`
  ADD CONSTRAINT `question_bank_programming_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `question_bank` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `question_bank_test_cases`
--
ALTER TABLE `question_bank_test_cases`
  ADD CONSTRAINT `question_bank_test_cases_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `question_bank` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `exam_results` (`result_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `test_cases`
--
ALTER TABLE `test_cases`
  ADD CONSTRAINT `test_cases_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Table structure for table `exam_results`
--

CREATE TABLE IF NOT EXISTS exam_results (
    result_id INT PRIMARY KEY AUTO_INCREMENT,
    exam_id INT,
    student_id INT,
    score INT,
    total_questions INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);

--
-- Indexes for table `exam_results`
--

CREATE INDEX idx_exam_date ON exams(exam_date);
CREATE INDEX idx_exam_type ON exams(exam_type);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
