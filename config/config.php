<?php
$envFile = __DIR__ . '/../.env';
$autoloadFile = __DIR__ . '/../vendor/autoload.php';

if (is_file($autoloadFile)) {
    require_once $autoloadFile;
}

if (is_file($envFile) && class_exists('Dotenv\\Dotenv')) {
    Dotenv\\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

function envValue(string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($value === false || $value === null) ? $default : (string) $value;
}

define('DB_HOST', envValue('DB_HOST', 'localhost'));
define('DB_NAME', envValue('DB_NAME', 'portfolio_db'));
define('DB_USER', envValue('DB_USER', 'root'));
define('DB_PASS', envValue('DB_PASS'));
define('ADMIN_USERNAME', envValue('ADMIN_USERNAME'));
define('ADMIN_PASSWORD', envValue('ADMIN_PASSWORD'));
define('ADMIN_KEY', envValue('ADMIN_KEY'));
define('OPENAI_API_KEY', envValue('OPENAI_API_KEY'));

function ensureColumnExists(PDO $pdo, string $tableName, string $columnName, string $columnDefinition): void {
    $stmt = $pdo->query("SELECT COUNT(*) AS total
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '{$tableName}'
          AND COLUMN_NAME = '{$columnName}'");

    $exists = (int) $stmt->fetchColumn();

    if ($exists === 0) {
        $pdo->exec("ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$columnDefinition}");
    }
}

function ensurePortfolioSchema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `about` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `content` TEXT
        )
    ");

    $pdo->exec("
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
        )
    ");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `projects` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL UNIQUE,
            `description` TEXT,
            `url` VARCHAR(512),
            `sort_order` INT DEFAULT 0
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `education` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `institution` VARCHAR(255) NOT NULL UNIQUE,
            `degree` VARCHAR(255),
            `field_name` VARCHAR(255),
            `start_year` VARCHAR(20),
            `end_year` VARCHAR(20),
            `description` TEXT,
            `sort_order` INT DEFAULT 0
        )
    ");
    ensureColumnExists($pdo, 'education', 'education_details', 'TEXT');

    $pdo->exec("DELETE older FROM education older
        INNER JOIN education newer ON newer.institution = older.institution
        AND newer.id > older.id
        AND CHAR_LENGTH(COALESCE(newer.description, '')) >= CHAR_LENGTH(COALESCE(older.description, ''))");
    $uniqueEducation = $pdo->query("SELECT COUNT(*) FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'education'
        AND COLUMN_NAME = 'institution' AND NON_UNIQUE = 0")->fetchColumn();
    if ((int) $uniqueEducation === 0) {
        $pdo->exec('ALTER TABLE education ADD UNIQUE KEY unique_education_institution (institution)');
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `experience` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `company` VARCHAR(255) NOT NULL,
            `role_name` VARCHAR(255),
            `location` VARCHAR(255),
            `start_date` VARCHAR(50),
            `end_date` VARCHAR(50),
            `description` TEXT,
            `sort_order` INT DEFAULT 0
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `publications` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `venue` VARCHAR(255),
            `publication_year` VARCHAR(20),
            `url` VARCHAR(512),
            `summary` TEXT,
            `sort_order` INT DEFAULT 0
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `blog_posts` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `excerpt` TEXT,
            `content` LONGTEXT NOT NULL,
            `featured_image` VARCHAR(500),
            `category` VARCHAR(100),
            `tags` TEXT,
            `author_name` VARCHAR(150) NOT NULL DEFAULT 'Tanui Kipng''etich Sila',
            `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
            `reading_time` INT UNSIGNED DEFAULT NULL,
            `published_at` DATETIME DEFAULT NULL,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `seo_title` VARCHAR(255),
            `seo_description` VARCHAR(500),
            `views` INT UNSIGNED NOT NULL DEFAULT 0,
            `allow_comments` BOOLEAN NOT NULL DEFAULT TRUE
        )
    ");

    // Ensure blog_posts columns exist for backward compatibility
    ensureColumnExists($pdo, 'blog_posts', 'slug', "VARCHAR(255) NOT NULL UNIQUE");
    ensureColumnExists($pdo, 'blog_posts', 'featured_image', "VARCHAR(500)");
    ensureColumnExists($pdo, 'blog_posts', 'category', "VARCHAR(100)");
    ensureColumnExists($pdo, 'blog_posts', 'tags', "TEXT");
    ensureColumnExists($pdo, 'blog_posts', 'author_name', "VARCHAR(150) NOT NULL DEFAULT 'Tanui Kipng''etich Sila'");
    ensureColumnExists($pdo, 'blog_posts', 'reading_time', "INT UNSIGNED DEFAULT NULL");
    ensureColumnExists($pdo, 'blog_posts', 'created_at', "DATETIME DEFAULT CURRENT_TIMESTAMP");
    ensureColumnExists($pdo, 'blog_posts', 'updated_at', "DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    ensureColumnExists($pdo, 'blog_posts', 'seo_title', "VARCHAR(255)");
    ensureColumnExists($pdo, 'blog_posts', 'seo_description', "VARCHAR(500)");
    ensureColumnExists($pdo, 'blog_posts', 'views', "INT UNSIGNED NOT NULL DEFAULT 0");
    ensureColumnExists($pdo, 'blog_posts', 'allow_comments', "BOOLEAN NOT NULL DEFAULT TRUE");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `settings` (
            `setting_key` VARCHAR(150) PRIMARY KEY,
            `setting_value` TEXT
        )
    ");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `skills` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `name` VARCHAR(128) NOT NULL UNIQUE,
            `level` VARCHAR(64)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `contact` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `email` VARCHAR(255),
            `location` VARCHAR(255),
            `phone` VARCHAR(50) DEFAULT ''
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `messages` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `subject` VARCHAR(255) DEFAULT '',
            `message` TEXT NOT NULL,
            `is_read` TINYINT DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    ensureColumnExists($pdo, 'contact', 'phone', "VARCHAR(50) DEFAULT ''");
}

try {
    $basePdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $basePdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    ensurePortfolioSchema($pdo);
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database initialization failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}
