<?php
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="question_template.csv"');

// Create output
$output = fopen('php://output', 'w');

// Write headers
$headers = [
    'question_type',    // multiple_choice or true_false
    'question_text',    // The actual question
    'choice1',          // First choice
    'choice2',          // Second choice
    'choice3',          // Third choice (leave empty for true/false)
    'choice4',          // Fourth choice (leave empty for true/false)
    'correct_choice_number'  // 1-4 for multiple choice, 1 for True, 2 for False
];

// Write headers without BOM
fputcsv($output, $headers);

// Write example rows
$examples = [
    // Multiple choice example
    [
        'multiple_choice',
        'What is 2 + 2?',
        'Three',
        'Four',
        'Five',
        'Six',
        '2'  // Second choice (Four) is correct
    ],
    // True/False example
    [
        'true_false',
        'The sky is blue.',
        'True',
        'False',
        '',  // No third choice for true/false
        '',  // No fourth choice for true/false
        '1'  // 1 means True is correct
    ]
];

foreach ($examples as $example) {
    fputcsv($output, $example);
}

fclose($output);
?> 