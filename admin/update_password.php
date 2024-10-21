<?php
// Include the database configuration file
require_once '../config/config.php';

// Set the email and password for the user
$email = "ccisfaculty@gmail.com"; 
$password = "admin1234";  
// Hash the new password using password_hash()
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare the SQL query to update the user's password
$sql = "UPDATE users SET password = ? WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hashedPassword, $email);
$stmt->execute();

// Check if the update was successful
if ($stmt->affected_rows > 0) {
    echo "Password updated successfully!";
} else {
    echo "Error updating password: " . $stmt->error;
}

// Close the statement and the connection
$stmt->close();
$conn->close();
?>
