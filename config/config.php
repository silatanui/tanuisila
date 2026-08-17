<?php
// Update these values for your environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'ipusgkqs_portfolio_db');
define('DB_USER', 'ipusgkqs_tanui');
define('DB_PASS', 'aKSnlQndlM9WCYM9');
// Admin login credentials for the panel
define('ADMIN_USERNAME', 'silatanuikipngetich@gmail.com');
define('ADMIN_PASSWORD', 'aKSnlQndlM9WCYM9');
// A simple admin key for quick access to the admin form (change this)
define('ADMIN_KEY', 'change-me');
// OpenAI API key for AI-powered blog automation
define('OPENAI_API_KEY', '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');

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
            `institution` VARCHAR(255) NOT NULL,
            `degree` VARCHAR(255),
            `field_name` VARCHAR(255),
            `start_year` VARCHAR(20),
            `end_year` VARCHAR(20),
            `description` TEXT,
            `sort_order` INT DEFAULT 0
        )
    ");

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

    $pdo->exec("INSERT INTO `about` (`id`, `content`) VALUES (1, 'Tanui Kipng\'etich Sila is a Computer Science graduate whose work sits where academic rigour meets practical software delivery. He completed his MSc in Computer Science at the University of Debrecen, Hungary, as a Stipendium Hungaricum scholar, following a First Class Honours BSc in Computer Science at Kirinyaga University, Kenya.\n\nHis work spans applied artificial intelligence, data visualization, geometric modelling and models of computation — consistently turning theoretical ideas into interactive, usable software. Alongside development and research, he has taught programming and ICT at college level and supported technical operations in professional environments.\n\nToday he focuses on building software, conducting research and developing technology projects that solve concrete problems.\n\nComputer Science • Software Development • Research • Technology Innovation • Teaching') ON DUPLICATE KEY UPDATE `content` = VALUES(`content`)");
    $pdo->exec("INSERT INTO `profile` (`id`, `full_name`, `headline`, `bio`, `email`, `phone`, `location`, `website`, `linkedin`, `github`, `summary`) VALUES (1, 'Tanui Kipng\'etich Sila', 'MSc Computer Science Student at University of Debrecen • IT Professional • AI Researcher • Full Stack Developer', 'I am Tanui Kipng\'etich Sila, a Computer Scientist and Educator born on October 20, 1999, in Kondabilet, Marakwet West, Kenya. My journey combines rigorous academic training, software engineering, teaching, and problem-solving to build digital solutions that create real impact.', 'silatanuikipngetich@gmail.com', '+36 20 323 3673 / +254 742 178 644', 'Debrecen, Hungary / Nairobi, Kenya', 'https://tanuiksila.co.ke', 'https://www.linkedin.com/in/tanui-kipngetich-sila/', 'https://github.com/silatanui', 'Computer Scientist and Educator with a passion for AI, software engineering, computational problem solving, and impactful digital solutions.') ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`), `headline` = VALUES(`headline`), `bio` = VALUES(`bio`), `email` = VALUES(`email`), `phone` = VALUES(`phone`), `location` = VALUES(`location`), `website` = VALUES(`website`), `linkedin` = VALUES(`linkedin`), `github` = VALUES(`github`), `summary` = VALUES(`summary`)");
    $pdo->exec("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('site_title', 'Tanui Kipng\'etich Sila'), ('tagline', 'MSc Computer Science Student at University of Debrecen'), ('hero_text', 'IT Professional, AI Researcher, and Full Stack Developer passionate about solving complex computational problems.'), ('show_blog', '1'), ('theme', 'light') ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
    $pdo->exec("INSERT INTO `contact` (`email`, `location`, `phone`) VALUES ('silatanuikipngetich@gmail.com', 'Debrecen, Hungary / Nairobi, Kenya', '+36 20 323 3673 / +254 742 178 644') ON DUPLICATE KEY UPDATE `email` = VALUES(`email`), `location` = VALUES(`location`), `phone` = VALUES(`phone`)");

    $pdo->exec("INSERT IGNORE INTO `skills` (`name`, `level`) VALUES ('Python', 'Advanced'), ('C#', 'Advanced'), ('C++', 'Intermediate'), ('JavaScript', 'Advanced'), ('PHP', 'Advanced'), ('MySQL', 'Advanced'), ('React.js', 'Advanced'), ('Blazor', 'Advanced'), ('Tailwind', 'Advanced'), ('Docker', 'Intermediate'), ('Git/GitHub', 'Advanced'), ('Azure', 'Intermediate'), ('Salesforce', 'Intermediate')");

    $pdo->exec("DELETE FROM `projects` WHERE title NOT IN (
        'AI-Driven E-Commerce Automation',
        'Global AI Impact Visualization',
        'Interactive Curve Design System',
        'Post Correspondence Problem Solver'
    )");

    $projectRows = [
        ['AI-Driven E-Commerce Automation', 'Master\'s research project automating e-commerce workflows with AI technologies on the Blazor Server framework — intelligent product recommendations, inventory management and customer behaviour analysis.', 'https://tanuiksila.co.ke/global_ai_impact_visualization/', 2025],
        ['Global AI Impact Visualization', 'A dashboard presenting AI adoption metrics across 5000+ data points, with an interactive choropleth map and filtering to explore adoption rates and consumer trust levels.', 'https://tanuiksila.co.ke/global_ai_impact_visualization/', 2025],
        ['Interactive Curve Design System', 'A parametric curve design system implementing Bézier, Hermite and B-Spline algorithms with real-time differential geometry analysis.', 'https://tanuiksila.co.ke/CAGD_P1/', 2025],
        ['Post Correspondence Problem Solver', 'An interactive solver and visualization for the Post Correspondence Problem, a classic undecidable problem in formal language theory.', 'https://tanuiksila.co.ke/PCP/', 2024],
    ];

    foreach ($projectRows as $project) {
        $title = $project[0];
        $description = $project[1];
        $url = $project[2];
        $sortOrder = $project[3];

        $exists = $pdo->prepare('SELECT COUNT(*) FROM `projects` WHERE `title` = :title');
        $exists->execute([':title' => $title]);
        if ((int) $exists->fetchColumn() === 0) {
            $insert = $pdo->prepare('INSERT INTO `projects` (`title`, `description`, `url`, `sort_order`) VALUES (:title, :description, :url, :sort_order)');
            $insert->execute([
                ':title' => $title,
                ':description' => $description,
                ':url' => $url,
                ':sort_order' => $sortOrder,
            ]);
        }
    }

    $pdo->exec("DELETE FROM `education`");
    $pdo->exec("INSERT INTO `education` (`institution`, `degree`, `field_name`, `start_year`, `end_year`, `description`, `sort_order`) VALUES
        ('University of Debrecen', 'MSc Computer Science', 'Information and Communication Technologies', '09/09/2024', '26/06/2026', 'Hungary | Final grade: 4.39/5.00 | EQF Level 7 | ECTS: 120 credits\nWebsite: https://inf.unideb.hu/\n\nCore Courses Covered:\n• Advanced Software Development in Large Enterprises (MŰZLI)\n• Advanced Cloud Computing\n• Data Mining\n• Algorithms, Cryptography\n• Models of Computation\n• Visualization and Visual Analytics\n\nResearch Thesis:\n• AI-Driven E-Commerce Automation Using Blazor Web Framework', 2),
        ('Kirinyaga University', 'BSc Computer Science', 'Computer Science', '28/08/2018', '10/10/2022', 'Kenya | Final grade: First Class Honors | EQF Level 6 | Credits: 58 units\nWebsite: https://www.kyu.ac.ke/\n\nCore Foundations Covered:\n• Internet Application Programming & Web Development\n• Human-Computer Interaction (UI/UX Design Principles)\n• Software Engineering & System Analysis methodologies\n• Distributed Systems & Cloud-ready application design\n• Database Management Systems (Backend Data Pipelines)\n• Data Structures, Algorithms, and logical problem-solving', 1)");

    $pdo->exec("DELETE FROM `experience`");
    $pdo->exec("INSERT INTO `experience` (`company`, `role_name`, `location`, `start_date`, `end_date`, `description`, `sort_order`) VALUES
        ('Transcosmos (Fossil Group)', 'Customer Success Specialist | Enterprise Application & Customer Support', 'Debrecen, Hungary', '01/10/2025', '01/02/2026', '• Investigated customer, order, repair, and transaction-related issues across integrated enterprise applications, using structured problem-solving to identify relevant information and support timely resolution.\n• Worked with SAP ERP, Salesforce Commerce Cloud, Adyen, and Computop as part of customer-facing e-commerce and transaction workflows.\n• Validated customer, order, repair, and transaction information across systems to maintain data accuracy and identify discrepancies.\n• Collaborated with internal teams to investigate complex issues, clarify requirements, escalate cases, and communicate resolution status.\n• Developed practical understanding of how enterprise applications, business processes, and customer-facing systems interact within an international environment.', 3),
        ('Infolink College of Technology', 'ICT and Programming Tutor', 'Nairobi, Kenya', '10/04/2023', '01/09/2024', '• Supported the development and maintenance of college website using PHP, JavaScript, HTML troubleshooting both frontend and backend issues.\n• Investigated programming and application issues using structured debugging and problem-solving techniques.\n• Provided technical support and guidance to users with different levels of technical knowledge, explaining application behaviour and solutions clearly.\n• Developed practical programming exercises and technical materials covering software development concepts and application logic.', 2),
        ('Litmed College of Technology', 'IT Technician & Lecturer', 'Nairobi, Kenya', '09/09/2022', '31/03/2023', '• Provided IT support and technical assistance to users, investigating issues and helping maintain reliable day-to-day operations.\n• Supported students and other users through technical guidance, structured instruction, and feedback, strengthening collaboration and knowledge-sharing skills.', 1)");
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
    echo 'Database connection failed. Please edit config.php and verify MySQL is running.';
    exit;
}
