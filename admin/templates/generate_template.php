<?php
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="question_template.csv"');

// Create output
$output = fopen('php://output', 'w');

// Write headers
$headers = [
    'question_type',           // multiple_choice, true_false, or programming
    'question_text',           // The actual question
    'choice1',                 // First choice (for multiple_choice) or programming_language (for programming)
    'choice2',                 // Second choice (for multiple_choice) or problem_description
    'choice3',                 // Third choice (for multiple_choice) or input_format
    'choice4',                 // Fourth choice (for multiple_choice) or output_format
    'correct_choice_number',   // 1-4 for multiple choice, 1 for True, 2 for False
    'constraints',             // For programming questions
    'solution_template',       // For programming questions
    'test_input1',            // First test case input
    'test_output1',           // First test case expected output
    'test_explanation1',       // First test case explanation
    'test_input2',            // Second test case input
    'test_output2',           // Second test case expected output
    'test_explanation2',       // Second test case explanation
    'hidden_test_input1',     // First hidden test case input
    'hidden_test_output1',    // First hidden test case expected output
    'hidden_test_input2',     // Second hidden test case input
    'hidden_test_output2'     // Second hidden test case expected output
];

// Write headers without BOM
fputcsv($output, $headers);

// Write example rows
$examples = [
    // Multiple choice example
    [
        'multiple_choice',     // Exact string match
        'What is 2 + 2?',
        'Three',
        'Four',
        'Five',
        'Six',
        '2',
        '', // constraints
        '', // solution_template
        '',                    // test_input1
        '',                    // test_output1
        '',                    // test_explanation1
        '',                    // test_input2
        '',                    // test_output2
        '',                    // test_explanation2
        '',                    // hidden_test_input1
        '',                    // hidden_test_output1
        '',                    // hidden_test_input2
        ''                     // hidden_test_output2
    ],
    // True/False example
    [
        'true_false',          // Exact string match
        'The sky is blue.',
        'True',
        'False',
        '',
        '',
        '1',
        '', // constraints
        '', // solution_template
        '',                    // test_input1
        '',                    // test_output1
        '',                    // test_explanation1
        '',                    // test_input2
        '',                    // test_output2
        '',                    // test_explanation2
        '',                    // hidden_test_input1
        '',                    // hidden_test_output1
        '',                    // hidden_test_input2
        ''                     // hidden_test_output2
    ],
    // Programming example
    [
        'programming',         // Exact string match
        'Write a function that adds two numbers',
        'python',
        'Create a function called addNumbers that takes two parameters and returns their sum',
        'Two integers a and b, one per line',
        'Single integer - the sum of a and b',
        '',
        '-100 <= a,b <= 100',
        'def addNumbers(a, b):\n    # Your code here\n    pass',
        '5\n3',
        '8',
        '5 + 3 = 8',
        '-2\n7',
        '5',
        '-2 + 7 = 5',
        '100\n-50',
        '50',
        '-100\n-100',
        '-200'
    ]
];

foreach ($examples as $example) {
    fputcsv($output, $example);
}

fclose($output);
?> 