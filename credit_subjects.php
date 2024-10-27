<?php
session_start();
include('config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = htmlspecialchars($_POST['student_id']);
        // Debugging line to ensure student ID is passed
        echo "Student ID passed: " . $student_id . "<br>";
    
    // Query to fetch the matched subjects for this student
    $query = "SELECT subject_code, subject_description, units FROM matched_courses WHERE student_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<h3>Recommended Credited Subjects</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Subject Code</th><th>Subject Description</th><th>Units</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['subject_code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['subject_description']) . "</td>";
            echo "<td>" . htmlspecialchars($row['units']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No credited subjects found for this student.</p>";
    }

    $stmt->close();
} else {
    echo "<p>Invalid request or missing student ID.</p>";
}

?>

<a href="index.php">Go back to Home</a>
