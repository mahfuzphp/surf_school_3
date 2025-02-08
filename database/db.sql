-- Create Database
CREATE DATABASE IF NOT EXISTS surf_school;
USE surf_school;

CREATE TABLE IF NOT EXISTS weather_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(255) NOT NULL,
    weather_data JSON,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_location (location),
    INDEX idx_last_updated (last_updated)
);

-- Users Table
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
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Lessons Table
CREATE TABLE IF NOT EXISTS lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    instructor_id INT NOT NULL,
    level VARCHAR(50) NOT NULL,
    duration INT NOT NULL,
    max_students INT NOT NULL,
    lesson_image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id)
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed Data

-- Users (all passwords are 'password123')
INSERT INTO `users` (`username`, `password`, `email`, `user_type`, `profile_description`) VALUES
('admin', '$2y$10$Yl7rwoP3bxR4/eXmAW6bXOXp3.YI6kKNYPHHKAjwJwJbV7nQUYoGi', 'admin@surfschool.com', 'admin', 'Main administrator of the surf school'),
('john_instructor', '$2y$10$Yl7rwoP3bxR4/eXmAW6bXOXp3.YI6kKNYPHHKAjwJwJbV7nQUYoGi', 'john@surfschool.com', 'instructor', 'Professional surfer with 10 years of teaching experience'),
('sarah_instructor', '$2y$10$Yl7rwoP3bxR4/eXmAW6bXOXp3.YI6kKNYPHHKAjwJwJbV7nQUYoGi', 'sarah@surfschool.com', 'instructor', 'Former pro surfer, specialized in teaching beginners'),
('mike_student', '$2y$10$Yl7rwoP3bxR4/eXmAW6bXOXp3.YI6kKNYPHHKAjwJwJbV7nQUYoGi', 'mike@example.com', 'student', 'Beginner surfer excited to learn!'),
('lisa_student', '$2y$10$Yl7rwoP3bxR4/eXmAW6bXOXp3.YI6kKNYPHHKAjwJwJbV7nQUYoGi', 'lisa@example.com', 'student', 'Intermediate surfer looking to improve technique'),
('tom_student', '$2y$10$Yl7rwoP3bxR4/eXmAW6bXOXp3.YI6kKNYPHHKAjwJwJbV7nQUYoGi', 'tom@example.com', 'student', 'Advanced surfer focusing on competition techniques'),
('emma_instructor', '$2y$10$Yl7rwoP3bxR4/eXmAW6bXOXp3.YI6kKNYPHHKAjwJwJbV7nQUYoGi', 'emma@surfschool.com', 'instructor', 'Specialized in teaching kids and beginners');

-- Lessons
INSERT INTO `lessons` (`title`, `description`, `price`) VALUES
('Beginner Surfing', 'Perfect for first-time surfers. Learn the basics of surfing, including water safety, paddling techniques, and standing up on the board.', 49.99),
('Intermediate Skills', 'For surfers who can already stand and ride waves. Focus on improving technique, turning, and wave selection.', 69.99),
('Advanced Techniques', 'Master advanced surfing maneuvers, reading waves, and competitive strategies.', 89.99),
('Private Lesson', 'One-on-one instruction tailored to your skill level and goals.', 129.99),
('Kids Surf Camp', 'A fun and safe introduction to surfing for children ages 8-12. All equipment provided.', 39.99),
('Group Session', 'Learn with friends! Group lessons for 3-6 people with shared instructor.', 39.99);

-- Bookings (Sample data for the next week)
INSERT INTO `bookings` (`user_id`, `lesson_id`, `booking_date`, `booking_time`) VALUES
(4, 1, CURDATE() + INTERVAL 1 DAY, '09:00:00'),
(5, 2, CURDATE() + INTERVAL 1 DAY, '11:00:00'),
(4, 3, CURDATE() + INTERVAL 2 DAY, '14:00:00'),
(5, 1, CURDATE() + INTERVAL 3 DAY, '10:00:00'),
(4, 4, CURDATE() + INTERVAL 4 DAY, '13:00:00'),
(6, 2, CURDATE() + INTERVAL 2 DAY, '15:00:00'),
(6, 5, CURDATE() + INTERVAL 5 DAY, '10:00:00');

-- Add indexes for better performance
ALTER TABLE `users` ADD INDEX `email_index` (`email`);
ALTER TABLE `lessons` ADD INDEX `price_index` (`price`);
ALTER TABLE `bookings` ADD INDEX `booking_date_index` (`booking_date`);
