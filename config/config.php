
<?php
// Database connection configuration
$servername = "localhost";
$username = "root";  // Replace with your database username
$password = "akosidugo23";  // Replace with your database password
$dbname = "qualifying_exam";  // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
