<?php

//This FIle is for generating a template for importing questions from a csv file

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="question_bank_template.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Write headers first
fputcsv($output, [
    'question_type',
    'question_text',
    'option1',
    'option2',
    'option3',
    'option4',
    'correct_answer',
    'programming_language',
    'test_input',
    'expected_output',
    'is_hidden',
    'description'
]);

// Example rows
$examples = [
    // Multiple Choice Example
    [
        'multiple_choice',
        'What is 2 + 2?',
        '3',
        '4',
        '5',
        '6',
        '2', // This means option2 (4) is correct
        '', // programming_language
        '', // test_input
        '', // expected_output
        '', // is_hidden
        ''  // description
    ],
    // True/False Example
    [
        'true_false',
        'The Earth is flat.',
        '', // option1
        '', // option2
        '', // option3
        '', // option4
        'False', // correct_answer must be 'True' or 'False'
        '', // programming_language
        '', // test_input
        '', // expected_output
        '', // is_hidden
        ''  // description
    ],
    // Programming Example
    [
        'programming',
        'Write a function that adds two numbers.',
        '', // option1
        '', // option2
        '', // option3
        '', // option4
        '', // correct_answer
        'python', // programming_language
        '2 3', // test_input
        '5',   // expected_output
        '0',   // is_hidden (0 for visible, 1 for hidden)
        'Basic addition test case' // description (optional)
    ]
];

// Write example rows
foreach ($examples as $row) {
    fputcsv($output, $row);
}

// Write instructions at the bottom
$instructions = [
    ['# INSTRUCTIONS'],
    ['# 1. Delete all rows starting with #'],
    ['# 2. Keep the header row (first row)'],
    ['# 3. For multiple choice:'],
    ['#    - Fill in option1 through option4'],
    ['#    - correct_answer should be 1-4 (indicating which option is correct)'],
    ['# 4. For true/false:'],
    ['#    - Leave options empty'],
    ['#    - correct_answer must be exactly "True" or "False"'],
    ['# 5. For programming:'],
    ['#    - Fill in programming_language (python/java/c/cpp)'],
    ['#    - test_input: Input for the test case'],
    ['#    - expected_output: Expected output for the test case'],
    ['#    - is_hidden: 0 for visible test case, 1 for hidden test case'],
    ['#    - description: Optional description of the test case'],
    ['# 6. Leave fields empty if not applicable to the question type']
];

foreach ($instructions as $instruction) {
    fputcsv($output, $instruction);
}

fclose($output);
?> 