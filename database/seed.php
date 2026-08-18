<?php
require_once __DIR__ . '/../config/config.php';

function upsertRows(PDO $pdo, string $table, array $columns, array $rows, string $keyColumn): void {
    $quotedColumns = implode(', ', array_map(static fn(string $column): string => "`{$column}`", $columns));
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $updates = implode(', ', array_map(static fn(string $column): string => "`{$column}` = VALUES(`{$column}`)", array_filter($columns, static fn(string $column): bool => $column !== $keyColumn)));
    $statement = $pdo->prepare("INSERT INTO `{$table}` ({$quotedColumns}) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE {$updates}");

    foreach ($rows as $row) {
        $statement->execute($row);
    }
}

upsertRows($pdo, 'about', ['id', 'content'], [[
    1,
    "Tanui Kipng'etich Sila is a Computer Science graduate whose work sits where academic rigour meets practical software delivery. He completed his MSc in Computer Science at the University of Debrecen, Hungary, as a Stipendium Hungaricum scholar, following a First Class Honours BSc in Computer Science at Kirinyaga University, Kenya.\n\nHis work spans applied artificial intelligence, data visualization, geometric modelling and models of computation, consistently turning theoretical ideas into interactive, usable software.\n\nToday he focuses on building software, conducting research and developing technology projects that solve concrete problems.\n\nComputer Science - Software Development - Research - Technology Innovation - Teaching",
]], 'id');

upsertRows($pdo, 'profile', ['id', 'full_name', 'headline', 'bio', 'email', 'phone', 'location', 'website', 'linkedin', 'github', 'summary'], [[
    1,
    "Tanui Kipng'etich Sila",
    'MSc Computer Science Student at University of Debrecen - IT Professional - AI Researcher - Full Stack Developer',
    "I am Tanui Kipng'etich Sila, a Computer Scientist and Educator focused on software engineering, teaching, research, and practical digital solutions.",
    'silatanuikipngetich@gmail.com',
    '+36 20 323 3673 / +254 742 178 644',
    'Debrecen, Hungary / Nairobi, Kenya',
    'https://tanuiksila.co.ke',
    'https://www.linkedin.com/in/tanui-kipng-etich-sila/',
    'https://github.com/silatanui',
    'Computer Scientist and Educator with a passion for AI, software engineering, computational problem solving, and impactful digital solutions.',
]], 'id');

upsertRows($pdo, 'settings', ['setting_key', 'setting_value'], [
    ['site_title', "Tanui Kipng'etich Sila"],
    ['tagline', 'MSc Computer Science Student at University of Debrecen'],
    ['hero_text', 'IT Professional, AI Researcher, and Full Stack Developer passionate about solving complex computational problems.'],
    ['show_blog', '1'],
    ['theme', 'light'],
], 'setting_key');

upsertRows($pdo, 'contact', ['id', 'email', 'location', 'phone'], [[
    1,
    'silatanuikipngetich@gmail.com',
    'Debrecen, Hungary / Nairobi, Kenya',
    '+36 20 323 3673 / +254 742 178 644',
]], 'id');

upsertRows($pdo, 'skills', ['name', 'level'], [
    ['Python', 'Advanced'], ['C#', 'Advanced'], ['C++', 'Intermediate'], ['JavaScript', 'Advanced'],
    ['PHP', 'Advanced'], ['MySQL', 'Advanced'], ['React.js', 'Advanced'], ['Blazor', 'Advanced'],
    ['Tailwind', 'Advanced'], ['Docker', 'Intermediate'], ['Git/GitHub', 'Advanced'], ['Azure', 'Intermediate'],
    ['Salesforce', 'Intermediate'],
], 'name');

upsertRows($pdo, 'projects', ['title', 'description', 'url', 'sort_order'], [
    ['AI-Driven E-Commerce Automation', 'Master research project automating e-commerce workflows with AI technologies on the Blazor Server framework.', 'https://tanuiksila.co.ke/global_ai_impact_visualization/', 2025],
    ['Global AI Impact Visualization', 'A dashboard presenting AI adoption metrics across 5000+ data points, with an interactive choropleth map and filtering.', 'https://tanuiksila.co.ke/global_ai_impact_visualization/', 2025],
    ['Interactive Curve Design System', 'A parametric curve design system implementing Bezier, Hermite and B-Spline algorithms.', 'https://tanuiksila.co.ke/CAGD_P1/', 2025],
    ['Post Correspondence Problem Solver', 'An interactive solver and visualization for the Post Correspondence Problem.', 'https://tanuiksila.co.ke/PCP/', 2024],
], 'title');

$pdo->exec('DELETE FROM `experience`');
upsertRows($pdo, 'experience', ['company', 'role_name', 'location', 'start_date', 'end_date', 'description', 'sort_order'], [
    ['Transcosmos (Fossil Group)', 'Customer Success Specialist | Enterprise Application & Customer Support', 'Debrecen, Hungary', '01/10/2025', '01/02/2026', 'Supported enterprise application and customer workflows.', 3],
    ['Infolink College of Technology', 'ICT and Programming Tutor', 'Nairobi, Kenya', '10/04/2023', '01/09/2024', 'Supported website maintenance and programming instruction.', 2],
    ['Litmed College of Technology', 'IT Technician & Lecturer', 'Nairobi, Kenya', '09/09/2022', '31/03/2023', 'Provided technical support and instruction.', 1],
], 'company');

echo "Portfolio data seeded successfully.\n";
