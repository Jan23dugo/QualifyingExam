<?php 

// Change these values to match your local MySQL setup
$db = new mysqli('localhost', 'root', '', 'qualifying_exam');

// Handle connection error
if($db->connect_error){
	echo "Error connecting database: " . $db->connect_error;
}

?>
