-- db.sql
CREATE DATABASE IF NOT EXISTS school_results CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_results;

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  roll VARCHAR(50) NOT NULL,
  name VARCHAR(255) NOT NULL,
  class VARCHAR(10) NOT NULL,
  section VARCHAR(20) DEFAULT NULL,
  `group` VARCHAR(50) DEFAULT NULL,
  UNIQUE KEY uniq_student (class, COALESCE(section,''), COALESCE(`group`,''), roll)
);

CREATE TABLE IF NOT EXISTS subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class VARCHAR(10) NOT NULL,
  `group` VARCHAR(50) DEFAULT NULL,
  subject_name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS results (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  marks FLOAT DEFAULT 0,
  max_marks FLOAT DEFAULT 100,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
);
