<?php
require_once '../../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $duration = $_POST['duration'];
        $event_type = $_POST['event_type'];
        
        $query = "INSERT INTO calendar_events (
            title, 
            description, 
            event_date, 
            event_time, 
            duration, 
            event_type, 
            created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", 
            $title, 
            $description, 
            $event_date,
            $event_time,
            $duration,
            $event_type,
            $_SESSION['user_id']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Event created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 