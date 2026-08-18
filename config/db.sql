-- SQL dump for portfolio site
CREATE DATABASE IF NOT EXISTS `portfolio_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio_db`;

CREATE TABLE IF NOT EXISTS `about` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `content` TEXT
);

CREATE TABLE IF NOT EXISTS `profile` (
  `id` TINYINT PRIMARY KEY DEFAULT 1,
  `full_name` VARCHAR(255) NOT NULL DEFAULT 'Your Name',
  `headline` VARCHAR(255) DEFAULT 'Developer • Designer • Creator',
  `bio` TEXT,
  `email` VARCHAR(255) DEFAULT '',
  `phone` VARCHAR(50) DEFAULT '',
  `location` VARCHAR(255) DEFAULT '',
  `website` VARCHAR(255) DEFAULT '',
  `linkedin` VARCHAR(255) DEFAULT '',
  `github` VARCHAR(255) DEFAULT '',
  `summary` TEXT
);

CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `url` VARCHAR(512),
  `sort_order` INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS `education` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `institution` VARCHAR(255) NOT NULL UNIQUE,
  `degree` VARCHAR(255),
  `field_name` VARCHAR(255),
  `start_year` VARCHAR(20),
  `end_year` VARCHAR(20),
  `description` TEXT,
  `education_details` TEXT,
  `sort_order` INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS `experience` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `company` VARCHAR(255) NOT NULL,
  `role_name` VARCHAR(255),
  `location` VARCHAR(255),
  `start_date` VARCHAR(50),
  `end_date` VARCHAR(50),
  `description` TEXT,
  `sort_order` INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS `publications` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `venue` VARCHAR(255),
  `publication_year` VARCHAR(20),
  `url` VARCHAR(512),
  `summary` TEXT,
  `sort_order` INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255),
  `excerpt` TEXT,
  `content` LONGTEXT,
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `published_at` DATETIME,
  `sort_order` INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(150) PRIMARY KEY,
  `setting_value` TEXT
);

CREATE TABLE IF NOT EXISTS `skills` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `level` VARCHAR(64)
);

CREATE TABLE IF NOT EXISTS `contact` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `email` VARCHAR(255),
  `location` VARCHAR(255),
  `phone` VARCHAR(50) DEFAULT ''
);

-- Sample data
INSERT INTO about (content) VALUES ('Hi, I\'m Your Name — a developer building delightful web experiences. Replace this text to make it yours.')
ON DUPLICATE KEY UPDATE content = VALUES(content);

INSERT INTO profile (id, full_name, headline, bio, email, phone, location, website, linkedin, github, summary) VALUES
(1, 'Your Name', 'Developer • Designer • Creator', 'I build thoughtful digital experiences and impactful products.', 'you@example.com', '', 'Your City, Country', '', '', '', 'I create human-centered solutions with a focus on thoughtful design, reliable engineering, and measurable impact.')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), headline = VALUES(headline), bio = VALUES(bio), email = VALUES(email), phone = VALUES(phone), location = VALUES(location), website = VALUES(website), linkedin = VALUES(linkedin), github = VALUES(github), summary = VALUES(summary);

INSERT INTO projects (title, description, url, sort_order) VALUES
('Example Project','A short description of the project.','https://example.com',10),
('Another Project','Another sample project description.','',5)
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), url = VALUES(url), sort_order = VALUES(sort_order);

INSERT INTO skills (name, level) VALUES
('PHP','Advanced'),
('JavaScript','Intermediate'),
('MySQL','Intermediate')
ON DUPLICATE KEY UPDATE name = VALUES(name), level = VALUES(level);

INSERT INTO contact (email, location, phone) VALUES ('you@example.com','Your City, Country','')
ON DUPLICATE KEY UPDATE email = VALUES(email), location = VALUES(location), phone = VALUES(phone);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_title', 'Your Name'),
('tagline', 'Developer • Designer • Creator'),
('hero_text', 'I build thoughtful web experiences and delightful products.'),
('show_blog', '1'),
('theme', 'light')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
