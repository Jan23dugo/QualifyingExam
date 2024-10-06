
<?php
// Database connection configuration
$servername = "sql.freedb.tech";
$username = "freedb_Group8";  // Replace with your database username
$password = "a23Ey8n99P!AmB5";  // Replace with your database password
$dbname = "freedb_QualifyingExam";  // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
