-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Create Database
CREATE DATABASE IF NOT EXISTS surf_school;
USE surf_school;


-- Dumping structure for table surf_school.bookings
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'confirmed',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `booking_date_index` (`booking_date`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table surf_school.bookings: ~0 rows (approximately)
INSERT INTO `bookings` (`id`, `user_id`, `lesson_id`, `booking_date`, `booking_time`, `created_at`, `status`) VALUES
	(1, 5, 1, '2024-02-15', '09:00:00', '2025-02-08 11:13:18', 'confirmed');
INSERT INTO `bookings` (`id`, `user_id`, `lesson_id`, `booking_date`, `booking_time`, `created_at`, `status`) VALUES
	(2, 6, 2, '2024-02-15', '11:00:00', '2025-02-08 11:13:18', 'confirmed');
INSERT INTO `bookings` (`id`, `user_id`, `lesson_id`, `booking_date`, `booking_time`, `created_at`, `status`) VALUES
	(3, 5, 3, '2024-02-16', '14:00:00', '2025-02-08 11:13:18', 'confirmed');
INSERT INTO `bookings` (`id`, `user_id`, `lesson_id`, `booking_date`, `booking_time`, `created_at`, `status`) VALUES
	(4, 6, 1, '2024-02-17', '10:00:00', '2025-02-08 11:13:18', 'confirmed');
INSERT INTO `bookings` (`id`, `user_id`, `lesson_id`, `booking_date`, `booking_time`, `created_at`, `status`) VALUES
	(6, 7, 3, '2025-02-14', '15:00:00', '2025-02-08 14:03:44', 'pending');
INSERT INTO `bookings` (`id`, `user_id`, `lesson_id`, `booking_date`, `booking_time`, `created_at`, `status`) VALUES
	(7, 9, 1, '2025-02-12', '15:00:00', '2025-02-08 15:36:22', 'confirmed');
INSERT INTO `bookings` (`id`, `user_id`, `lesson_id`, `booking_date`, `booking_time`, `created_at`, `status`) VALUES
	(8, 9, 6, '2025-02-10', '12:00:00', '2025-02-08 15:36:57', 'confirmed');

-- Dumping structure for table surf_school.lessons
DROP TABLE IF EXISTS `lessons`;
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `instructor_id` int NOT NULL,
  `level` varchar(50) NOT NULL DEFAULT 'Beginner',
  `duration` int NOT NULL DEFAULT '60',
  `max_students` int NOT NULL DEFAULT '5',
  `lesson_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `instructor_id` (`instructor_id`),
  CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table surf_school.lessons: ~5 rows (approximately)
INSERT INTO `lessons` (`id`, `title`, `description`, `price`, `instructor_id`, `level`, `duration`, `max_students`, `lesson_image`, `is_active`, `created_at`) VALUES
	(1, 'Beginner Surfing', 'Perfect for first-time surfers. Learn the basics of surfing, including water safety, paddling techniques, and standing up on the board.', 49.99, 2, 'Beginner', 60, 5, '67a67747e090b.jpg', 1, '2025-02-08 11:13:18');
INSERT INTO `lessons` (`id`, `title`, `description`, `price`, `instructor_id`, `level`, `duration`, `max_students`, `lesson_image`, `is_active`, `created_at`) VALUES
	(2, 'Intermediate Skills', 'For surfers who can already stand and ride waves. Focus on improving technique, turning, and wave selection.', 69.99, 3, 'Intermediate', 60, 5, '67a74dcdc748b.jpg', 1, '2025-02-08 11:13:18');
INSERT INTO `lessons` (`id`, `title`, `description`, `price`, `instructor_id`, `level`, `duration`, `max_students`, `lesson_image`, `is_active`, `created_at`) VALUES
	(3, 'Advanced Techniques', 'Master advanced surfing maneuvers, reading waves, and competitive strategies.', 89.99, 4, 'Advanced', 60, 5, '67a74d8081434.jpg', 1, '2025-02-08 11:13:18');
INSERT INTO `lessons` (`id`, `title`, `description`, `price`, `instructor_id`, `level`, `duration`, `max_students`, `lesson_image`, `is_active`, `created_at`) VALUES
	(4, 'Private Lesson', 'One-on-one instruction tailored to your skill level and goals.', 129.99, 2, 'All Levels', 60, 5, '67a683bc7ba02.png', 1, '2025-02-08 11:13:18');
INSERT INTO `lessons` (`id`, `title`, `description`, `price`, `instructor_id`, `level`, `duration`, `max_students`, `lesson_image`, `is_active`, `created_at`) VALUES
	(5, 'Kids Surf Camp', 'A fun and safe introduction to surfing for children ages 8-12. All equipment provided.', 39.99, 3, 'Beginner', 60, 5, '67a75cac4601b.png', 1, '2025-02-08 11:13:18');
INSERT INTO `lessons` (`id`, `title`, `description`, `price`, `instructor_id`, `level`, `duration`, `max_students`, `lesson_image`, `is_active`, `created_at`) VALUES
	(6, 'demo lesson', 'this is a demo lesson', 10.00, 4, 'All Levels', 30, 20, NULL, 1, '2025-02-08 13:59:27');

-- Dumping structure for table surf_school.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_type` enum('admin','instructor','student') NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `profile_description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table surf_school.users: ~8 rows (approximately)
INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `profile_image`, `profile_description`, `created_at`) VALUES
	(1, 'admin', '$2y$10$UO5UuGmWirh74JPC3Sobe.HM360QpRwijAp45.fEa2DI52oXQ76jG', 'admin@surfschool.com', 'admin', NULL, 'Main administrator of the surf school', '2025-02-08 11:13:18');
INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `profile_image`, `profile_description`, `created_at`) VALUES
	(2, 'john_instructor', '$2y$10$cgdJehKEw2Git1XjO2gF2ONjo1fqIJV/vv4SuZ1pgwIDk.40.SeOW', 'john@surfschool.com', 'instructor', '67a7834457977.png', 'Professional surfer with 10 years of teaching experience', '2025-02-08 11:13:18');
INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `profile_image`, `profile_description`, `created_at`) VALUES
	(3, 'sarah_instructor', '$2y$10$IDWzkJx1iPaQxQ4CAKRgBuI5Nv7gNBiNHS/GWhalKC9gmKtrJWZSq', 'sarah@surfschool.com', 'instructor', NULL, 'Former pro surfer, specialized in teaching beginners', '2025-02-08 11:13:18');
INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `profile_image`, `profile_description`, `created_at`) VALUES
	(4, 'emma_instructor', '$2y$10$nNsXwBqIURzvEWrRZ/iFWO9EOB.gI0PG2hykGV4bMs2oK2YCqAjAa', 'emma@surfschool.com', 'instructor', NULL, 'Specialized in teaching kids and beginners', '2025-02-08 11:13:18');
INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `profile_image`, `profile_description`, `created_at`) VALUES
	(5, 'mike_student', '$2y$10$UO5UuGmWirh74JPC3Sobe.HM360QpRwijAp45.fEa2DI52oXQ76jG', 'mike@example.com', 'student', NULL, 'Beginner surfer excited to learn!', '2025-02-08 11:13:18');
INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `profile_image`, `profile_description`, `created_at`) VALUES
	(6, 'lisa_student', '$2y$10$OEjeJhLS5wj35/NaW73GsO3v.kW2.cu82t/RHKxmOt/3pttlhB7PO', 'lisa@example.com', 'student', NULL, 'Intermediate surfer looking to improve technique', '2025-02-08 11:13:18');
INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `profile_image`, `profile_description`, `created_at`) VALUES
	(7, 'tom_student', '$2y$10$1ImVrQUHthJ5WXfMIEjyPuJfUBeywB01KtdqdCDVl4zUT0UnQTb1K', 'tom@example.com', 'student', NULL, 'Advanced surfer focusing on competition techniques', '2025-02-08 11:13:18');
INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `profile_image`, `profile_description`, `created_at`) VALUES
	(9, 'mahfuz', '$2y$10$OH7ZJY/1P7h7JM63/4xq2.RE6ZN313TUcHmjFm/EfiI2esrXMS28e', 'mahfuzphp@gmail.com', 'student', '67a77a6b55836.png', '', '2025-02-08 15:35:26');
INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `profile_image`, `profile_description`, `created_at`) VALUES
	(11, 'newuser', '$2y$10$r4qYBpgnoXb94oInKPZlde82wUh5TbEf/0cKrATjfz6aIOwURbcYm', 'swt.webgeeks@gmail.com', 'student', '67a77da69f108.png', 'info', '2025-02-08 15:48:29');

-- Dumping structure for table surf_school.weather_cache
DROP TABLE IF EXISTS `weather_cache`;
CREATE TABLE IF NOT EXISTS `weather_cache` (
  `id` int NOT NULL AUTO_INCREMENT,
  `location` varchar(255) NOT NULL,
  `weather_data` json DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_location` (`location`),
  KEY `idx_last_updated` (`last_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table surf_school.weather_cache: ~1 rows (approximately)
INSERT INTO `weather_cache` (`id`, `location`, `weather_data`, `last_updated`) VALUES
	(2, 'Bondi Beach', '{"current": {"condition": {"text": "Sunny"}, "wind_speed": "15 km/h", "wave_height": "1.2 m", "air_temperature": "26 °C"}, "location": {"name": "Bondi Beach", "latitude": -33.8915, "localtime": "2025-02-08 15:55", "longitude": 151.2767}}', '2025-02-08 15:55:40');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
