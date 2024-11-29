<?php
// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="question_template.csv"');

// Create CSV header row with clear descriptions
$headers = array(
    '### INSTRUCTIONS ###',
    '1. The first row (blue background in Excel) contains the field names',
    '2. Each row after that should contain one question',
    '3. See example questions below',
    '4. Delete these instruction rows before importing',
    '',
    '### FIELD DESCRIPTIONS ###',
    'question_type: Use exactly one of these values: multiple_choice, true_false, essay, programming',
    'question_text: Write your question here',
    'options 1-4: Fill for multiple_choice only, leave empty for others',
    'correct_answer: For multiple_choice use 1-4, for true_false use True/False, leave empty for others',
    'answer_guidelines: Use for essay questions',
    'programming_language: Use python, java, cpp, or c',
    'problem_description: Detailed description for programming questions',
    'input_format: Format of input for programming questions',
    'output_format: Expected output format',
    'constraints: Any constraints on input/output',
    'sample_input: Example input',
    'sample_output: Example output',
    'sample_explanation: Explain the example',
    'solution_template: Starting code template',
    '',
    '### EXAMPLE QUESTIONS BELOW ###',
    ''
);

// Create header row
$field_names = array(
    'question_type',
    'question_text',
    'option_1',
    'option_2',
    'option_3',
    'option_4',
    'correct_answer',
    'answer_guidelines',
    'programming_language',
    'problem_description',
    'input_format',
    'output_format',
    'constraints',
    'sample_input',
    'sample_output',
    'sample_explanation',
    'solution_template'
);

// Create example rows
$example_rows = array(
    // Multiple Choice Example
    array(
        'multiple_choice',
        'What is the capital of France?',
        'London',
        'Berlin',
        'Paris',
        'Madrid',
        '3',
        '',
        '', '', '', '', '', '', '', '', ''
    ),
    // True/False Example
    array(
        'true_false',
        'The Earth is flat.',
        '', '', '', '',
        'False',
        '',
        '', '', '', '', '', '', '', '', ''
    ),
    // Essay Example
    array(
        'essay',
        'Explain the process of photosynthesis.',
        '', '', '', '',
        '',
        'Include key components: sunlight, chlorophyll, water, carbon dioxide, and glucose production.',
        '', '', '', '', '', '', '', '', ''
    ),
    // Programming Example
    array(
        'programming',
        'Sum of Two Numbers',
        '', '', '', '',
        '',
        '',
        'python',
        'Write a function that takes two integers as input and returns their sum.',
        'First line contains two space-separated integers a and b (1 ≤ a, b ≤ 1000)',
        'Print a single integer - the sum of a and b',
        '1 ≤ a, b ≤ 1000',
        '5 3',
        '8',
        'In this example, 5 + 3 = 8',
        'def sum_numbers(a, b):\n    # Write your code here\n    pass'
    )
);

// Open output stream
$output = fopen('php://output', 'w');

// Write instructions
foreach ($headers as $header) {
    fputcsv($output, array($header));
}

// Write field names
fputcsv($output, $field_names);

// Write example rows
foreach ($example_rows as $row) {
    fputcsv($output, $row);
}

// Close the output stream
fclose($output);
?> 