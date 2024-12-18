<?php

require_once '../config/config.php';
session_start();

// Query to fetch user-created events
$eventQuery = "SELECT title, description, event_date, event_time, duration FROM calendar_events";
$eventResult = $conn->query($eventQuery);

while ($eventRow = $eventResult->fetch_assoc()) {
    $start_datetime = $eventRow['event_date'] . ' ' . $eventRow['event_time'];
    $start = new DateTime($start_datetime);
    $end = clone $start;
    $end->add(new DateInterval('PT' . $eventRow['duration'] . 'M'));

    $events[] = array(
        'id' => null, // No unique ID for custom events
        'title' => $eventRow['title'],
        'start' => $start->format('Y-m-d\TH:i:s'),
        'end' => $end->format('Y-m-d\TH:i:s'),
        'backgroundColor' => '#e74c3c', // Red color for manually created events
        'borderColor' => '#e74c3c',
        'description' => $eventRow['description'],
        'registeredStudents' => null,
        'completedExams' => null,
        'duration' => $eventRow['duration'],
        'status' => 'custom_event',
        'exam_date' => $eventRow['event_date'],
        'exam_time' => $eventRow['event_time']
    );
}

// Encode events array into JSON to send to the frontend
header('Content-Type: application/json');
echo json_encode($events);
exit();

?>
