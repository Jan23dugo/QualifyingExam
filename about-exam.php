<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STREAM About Exam</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="about-exam">
    
    <?php include 'navbar.php'?>

    <!-- About Exam Section -->
    <section id="about-exam-info" class="about-exam-info">
        <h1>About the CCIS Qualifying Exam</h1>
        <p>Welcome to the official page for the CCIS Qualifying Exam. Below you will find all the necessary information about the upcoming exams, including the schedule, requirements, and the documents needed to participate in the exam. Please make sure you are prepared and have all the necessary materials before your exam day.</p>
    </section>
    
    <!-- Exam Schedule Section -->
    <section id="schedule" class="about-exam-info">
        <h2>Exam Schedule</h2>
        <p>The exam schedule for the upcoming CCIS Qualifying Exam is as follows:</p>
        <div class="schedule-table-container">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>November 20, 2024</td>
                        <td>8:00 AM - 12:00 PM</td>
                        <td>CCIS Building, Room 101</td>
                    </tr>
                    <tr>
                        <td>November 21, 2024</td>
                        <td>1:00 PM - 5:00 PM</td>
                        <td>CCIS Building, Room 102</td>
                    </tr>
                    <tr>
                        <td>November 22, 2024</td>
                        <td>8:00 AM - 12:00 PM</td>
                        <td>CCIS Building, Room 103</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </section>

    <!-- Exam Requirements Section -->
    <section id="requirements" class="exam-requirements">
        <h2>Exam Requirements</h2>
        <p>To qualify for the CCIS exam, students must meet the following requirements:</p><br>
        <ul>
            <p>Must be a <strong>registered transferee, shiftee, or ladderized</strong> student.</p>
            <p>Must have <strong>completed the required prerequisite courses.</strong></li>
            <p>Must <strong>not</strong> have grade lower than <strong>2.00 (or 85).</strong></li>
        </ul>
    </section>

    <!-- Required Documents Section -->
    <section id="documents" class="required-documents">
        <h2>Required Documents</h2>
        <p>The following documents are required to be submitted before taking the exam:</p>
        <ul>
            <li>Transcript of Records (TOR) for transferees and shiftees.</li>
            <li>Copy of valid school ID (required for all students).</li>
            <li>Birth Certificate (required for all students).</li>
        </ul>
    </section>
</body>
</html>
