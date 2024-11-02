<p?php 
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
    </div>

        <section id="requirements" class="exam-requirements">
            <h2>Exam Requirements</h2>
            <p>To qualify for the CCIS exam, students must meet the following requirements:</p>
            <ul>
                <li>Must <strong>not</strong> have a <strong>failing grade or grade lower than 2.00 (or 85)</strong></li>
                <li>Must be an <strong>incoming Second Year if transferee or shiftee</strong> (must have completed at least 2 semester).
                    <br>If ladderized, must be <strong>graduated on their 3-year diplomat program</strong>. </li>
                <li>Must have <strong>no failing grade, dropped, incomplete, and withdrawn mark</strong> in any subjects.</li>
            </ul>
        </section>

        <!-- Required Documents Section -->
        <section id="documents" class="required-documents">
            <h2>Required Documents</h2>
            <p>The following documents are required to be submitted before taking the exam:</p>
            <ul>
                <li>Submit a copy of your <strong>Transcript of Records (TOR), <br>or Informative or Certified Copy of Grades</strong> <br>(initial requirement of the college only) </li>
                <li>Provide a <strong>valid School ID</strong></li>
            </ul>
        </section>
    </div>
    <?php include 'footer.php'?>
</body>
</html>
