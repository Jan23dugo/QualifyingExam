<?php

require_once '../config/config.php';
header('Content-Type: application/json');

try {
    // Query to get calendar events
    $query = "SELECT 
        event_id,
        title,
        description,
        event_date,
        event_time,
        duration,
        event_type
        FROM calendar_events 
        WHERE event_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $events = array();
    
    while ($row = $result->fetch_assoc()) {
        // Combine date and time
        $start_datetime = $row['event_date'] . ' ' . $row['event_time'];
        $start = new DateTime($start_datetime);
        $end = clone $start;
        $end->add(new DateInterval('PT' . $row['duration'] . 'M'));
        
        $events[] = array(
            'id' => $row['event_id'],
            'title' => $row['title'],
            'start' => $start->format('Y-m-d\TH:i:s'),
            'end' => $end->format('Y-m-d\TH:i:s'),
            'description' => $row['description'],
            'duration' => $row['duration'],
            'event_type' => $row['event_type']
        );
    }
    
    echo json_encode($events);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
