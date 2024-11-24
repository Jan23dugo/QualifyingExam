<?php
// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="question_import_template.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write header row
fputcsv($output, ['Question Type', 'Question Text', 'Options', 'Correct Answer']);

// Write sample rows
$sample_data = [
    ['multiple_choice', 'What is 2+2?', '1|2|3|4', '3'],
    ['true_false', 'The Earth is flat.', '', 'false'],
    ['essay', 'Explain the water cycle.', '', ''],
    ['programming', 'Write a function that reverses a string.', '', '']
];

foreach ($sample_data as $row) {
    fputcsv($output, $row);
}

fclose($output); 