<?php
session_start();
session_destroy();
header("Location: take_exam.php");
exit();
?> 