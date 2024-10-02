<?php
// Include database configuration file
include('config/config.php');

// Initialize variables for error messages or success message
$errors = [];
$success = "";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data and validate
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_initial = mysqli_real_escape_string($conn, $_POST['middle_initial']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $nationality = mysqli_real_escape_string($conn, $_POST['nationality']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    
    // Address details
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $zip_code = mysqli_real_escape_string($conn, $_POST['zip_code']);
    
    // Academic details
    $student_type = mysqli_real_escape_string($conn, $_POST['student_type']);
    $previous_school = mysqli_real_escape_string($conn, $_POST['previous_school']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $previous_program = mysqli_real_escape_string($conn, $_POST['previous_program']);
    $desired_program = mysqli_real_escape_string($conn, $_POST['desired_program']);
    
    // Handle file uploads
    $tor = $_FILES['tor']['name'];
    $school_id = $_FILES['school_id']['name'];
    $birth_certificate = $_FILES['birth_certificate']['name'];

    // Specify the directory where files will be uploaded
    $upload_dir = "uploads/";

    // Check for errors before proceeding
    if (empty($last_name) || empty($first_name) || empty($gender) || empty($dob) || empty($email) || empty($student_type)) {
        $errors[] = "Please fill out all required fields.";
    }

    // If no errors, process the form data
    if (count($errors) == 0) {
        // Move uploaded files to the designated directory
        move_uploaded_file($_FILES['tor']['tmp_name'], $upload_dir . $tor);
        move_uploaded_file($_FILES['school_id']['tmp_name'], $upload_dir . $school_id);
        move_uploaded_file($_FILES['birth_certificate']['tmp_name'], $upload_dir . $birth_certificate);

        // Insert data into the database
        $sql = "INSERT INTO students (last_name, first_name, middle_initial, gender, dob, nationality, email, contact_number, street, barangay, city, province, zip_code, student_type, previous_school, year_level, previous_program, desired_program, tor, school_id, birth_certificate)
                VALUES ('$last_name', '$first_name', '$middle_initial', '$gender', '$dob', '$nationality', '$email', '$contact_number', '$street', '$barangay', '$city', '$province', '$zip_code', '$student_type', '$previous_school', '$year_level', '$previous_program', '$desired_program', '$tor', '$school_id', '$birth_certificate')";

        if (mysqli_query($conn, $sql)) {
            $success = "Registration successful!";
        } else {
            $errors[] = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <style>
        /* Add your CSS styling here, or include from external file */
    </style>
</head>
<body>

    <div class="container">
        <h1>Student Registration and Requirements Submission</h1>

        <!-- Display errors or success message -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="register.php" method="POST" enctype="multipart/form-data">
            <!-- Personal Information -->
            <h3>Student Personal Information</h3>
            <!-- Add input fields for personal info like in the HTML version -->
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>

            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-group">
                <label for="middle_initial">Middle Initial:</label>
                <input type="text" id="middle_initial" name="middle_initial">
            </div>

            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="" disabled selected>-- Select Gender --</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required>
            </div>

            <div class="form-group">
                <label for="nationality">Nationality:</label>
                <input type="text" id="nationality" name="nationality" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" required>
            </div>

            <!-- Address -->
            <!-- Add input fields for address -->
            <h3>Address</h3>
            <div class="form-group">
                <label for="street">Street:</label>
                <input type="text" id="street" name="street" required>
            </div>

            <div class="form-group">
                <label for="barangay">Barangay:</label>
                <input type="text" id="barangay" name="barangay" required>
            </div>

            <div class="form-group">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" required>
            </div>

            <div class="form-group">
                <label for="province">Province/Region:</label>
                <input type="text" id="province" name="province" required>
            </div>

            <div class="form-group">
                <label for="zip_code">Zip Code:</label>
                <input type="text" id="zip_code" name="zip_code" required>
            </div>
            
            <!-- Academic Information -->
            <!-- Add input fields for academic info -->
            <h3>Student Academic Information</h3>
            <div class="form-group">
                <label for="student_type">Student Type:</label>
                <select id="student_type" name="student_type" required>
                    <option value="" disabled selected>-- Select Student Type --</option>
                    <option value="shiftee">Shiftee</option>
                    <option value="transferee">Transferee</option>
                    <option value="ladderized">Ladderized</option>
                </select>
            </div>

            <div class="form-group">
                <label for="previous_school">Name of Previous School:</label>
                <input type="text" id="previous_school" name="previous_school" required>
            </div>

            <div class="form-group">
                <label for="year_level">Year Level:</label>
                <input type="text" id="year_level" name="year_level" required>
            </div>

            <div class="form-group">
                <label for="previous_program">Previous Program:</label>
                <input type="text" id="previous_program" name="previous_program">
            </div>

            <div class="form-group">
                <label for="desired_program">Desired Program:</label>
                <select id="desired_program" name="desired_program" required>
                    <option value="" disabled selected>-- Select Desired Program --</option>
                    <option value="bsit">BS in Information Technology</option>
                    <option value="bsis">BS in Information Systems</option>
                    <option value="bsce">BS in Civil Engineering</option>
                </select>
            </div>
            
            <!-- File Uploads -->
            <div class="form-group">
                <label for="tor">Upload Copy of TOR:</label>
                <input type="file" id="tor" name="tor" required>
            </div>
            <div class="form-group">
                <label for="school_id">Upload Copy of School ID:</label>
                <input type="file" id="school_id" name="school_id" required>
            </div>
            <div class="form-group">
                <label for="birth_certificate">Upload Copy of Birth Certificate:</label>
                <input type="file" id="birth_certificate" name="birth_certificate" required>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <input type="submit" value="SUBMIT">
            </div>
        </form>
    </div>

</body>
</html>
