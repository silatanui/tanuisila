<?php
function ensureEducationDetails(PDO $pdo): void {
    $details = [
        'University of Debrecen' => "Hungary | Final grade: 4.39/5.00 | EQF Level 7 | ECTS: 120 credits\nWebsite: https://inf.unideb.hu/\n\nCore Courses Covered:\n- Advanced Software Development in Large Enterprises (MUZLI)\n- Advanced Cloud Computing\n- Data Mining\n- Algorithms, Cryptography\n- Models of Computation\n- Visualization and Visual Analytics\n\nResearch Thesis:\n- AI-Driven E-Commerce Automation Using Blazor Web Framework",
        'Kirinyaga University' => "Kenya | First Class Honors | EQF Level 6 | Credits: 58 units\nWebsite: https://www.kyu.ac.ke/\n\nCore Foundations Covered:\n- Internet Application Programming & Web Development\n- Human-Computer Interaction (UI/UX Design Principles)\n- Software Engineering & System Analysis methodologies\n- Distributed Systems & Cloud-ready application design\n- Database Management Systems (Backend Data Pipelines)\n- Data Structures, Algorithms, and logical problem-solving",
    ];
    $statement = $pdo->prepare('UPDATE education SET education_details = ? WHERE institution = ? AND (education_details IS NULL OR education_details = \'\')');
    foreach ($details as $institution => $description) {
        $statement->execute([$description, $institution]);
    }
}

function educationDescription(string $institution, string $description): string {
    $details = [
        'University of Debrecen' => [
            'Hungary | Final grade: 4.39/5.00 | EQF Level 7',
            "Hungary | Final grade: 4.39/5.00 | EQF Level 7 | ECTS: 120 credits\nWebsite: https://inf.unideb.hu/\n\nCore Courses Covered:\n- Advanced Software Development in Large Enterprises (MUZLI)\n- Advanced Cloud Computing\n- Data Mining\n- Algorithms, Cryptography\n- Models of Computation\n- Visualization and Visual Analytics\n\nResearch Thesis:\n- AI-Driven E-Commerce Automation Using Blazor Web Framework",
        ],
        'Kirinyaga University' => [
            'Kenya | First Class Honors | EQF Level 6',
            "Kenya | First Class Honors | EQF Level 6 | Credits: 58 units\nWebsite: https://www.kyu.ac.ke/\n\nCore Foundations Covered:\n- Internet Application Programming & Web Development\n- Human-Computer Interaction (UI/UX Design Principles)\n- Software Engineering & System Analysis methodologies\n- Distributed Systems & Cloud-ready application design\n- Database Management Systems (Backend Data Pipelines)\n- Data Structures, Algorithms, and logical problem-solving",
        ],
    ];

    if (isset($details[$institution]) && strpos(trim($description), $details[$institution][0]) === 0) {
        return $details[$institution][1];
    }

    return $description;
}
