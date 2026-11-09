-- Voting System SQL schema for phpMyAdmin import
-- Database: voting_system

CREATE DATABASE IF NOT EXISTS `voting_system`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;
USE `voting_system`;

CREATE TABLE `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id_number` VARCHAR(50) DEFAULT NULL,
  `is_builtin_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_admins_admin_id_number` (`admin_id_number`),
  UNIQUE KEY `uidx_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `value` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_settings_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(255) DEFAULT NULL,
  `last_name` VARCHAR(255) DEFAULT NULL,
  `middle_name` VARCHAR(255) DEFAULT NULL,
  `id_name` VARCHAR(255) DEFAULT NULL,
  `department` VARCHAR(150) DEFAULT NULL,
  `course` VARCHAR(150) DEFAULT NULL,
  `school_year` VARCHAR(100) DEFAULT NULL,
  `year_level` VARCHAR(100) DEFAULT NULL,
  `section` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `face_descriptor` LONGTEXT DEFAULT NULL,
  `face_registered` TINYINT(1) NOT NULL DEFAULT 0,
  `id_photo` VARCHAR(255) DEFAULT NULL,
  `id_photo_hash` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `must_change_password` TINYINT(1) NOT NULL DEFAULT 1,
  `face_enrolled_at` DATETIME DEFAULT NULL,
  `failed_login_attempts` INT NOT NULL DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL,
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_students_student_id` (`student_id`),
  UNIQUE KEY `uidx_students_username` (`username`),
  KEY `idx_students_status` (`status`),
  KEY `idx_students_department_course` (`department`, `course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `elections` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'closed',
  `manual_open` TINYINT(1) NOT NULL DEFAULT 0,
  `start_time` DATETIME DEFAULT NULL,
  `end_time` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `positions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `votes_required` INT NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `parties` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `election_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parties_election_id` (`election_id`),
  CONSTRAINT `fk_parties_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `candidates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(255) NOT NULL,
  `party_id` INT UNSIGNED DEFAULT NULL,
  `position_id` INT UNSIGNED NOT NULL,
  `election_id` INT UNSIGNED DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `eligible_year_levels` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_candidates_party_id` (`party_id`),
  KEY `idx_candidates_position_id` (`position_id`),
  KEY `idx_candidates_election_id` (`election_id`),
  CONSTRAINT `fk_candidates_party` FOREIGN KEY (`party_id`) REFERENCES `parties` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_candidates_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_candidates_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `votes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `candidate_id` INT UNSIGNED NOT NULL,
  `position_id` INT UNSIGNED NOT NULL,
  `election_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_votes_student` (`student_id`),
  KEY `idx_votes_candidate` (`candidate_id`),
  KEY `idx_votes_position` (`position_id`),
  KEY `idx_votes_election` (`election_id`),
  CONSTRAINT `fk_votes_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_votes_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_votes_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_votes_election` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `status` ENUM('present', 'absent', 'late') NOT NULL DEFAULT 'present',
  `marked_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attendance_student` (`student_id`),
  KEY `idx_attendance_date` (`date`),
  CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_attendance_admin` FOREIGN KEY (`marked_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_type` VARCHAR(20) NOT NULL DEFAULT 'system',
  `actor_id` INT UNSIGNED DEFAULT NULL,
  `actor_name` VARCHAR(255) NOT NULL DEFAULT 'System',
  `action` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_audit_logs_action_created` (`action`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `election_tie_winners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `election_id` INT UNSIGNED NOT NULL,
  `position_id` INT UNSIGNED NOT NULL,
  `candidate_id` INT UNSIGNED NOT NULL,
  `selected_by` INT UNSIGNED DEFAULT NULL,
  `selected_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_election_tie_winner_position` (`election_id`, `position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
