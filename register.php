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

      // Generate a unique Reference ID
      $reference_id = uniqid('STU-'); // This will generate something like STU-605c1c1c7a7f7

    // If no errors, process the form data
    if (count($errors) == 0) {
        // Move uploaded files to the designated directory
        move_uploaded_file($_FILES['tor']['tmp_name'], $upload_dir . $tor);
        move_uploaded_file($_FILES['school_id']['tmp_name'], $upload_dir . $school_id);
        move_uploaded_file($_FILES['birth_certificate']['tmp_name'], $upload_dir . $birth_certificate);

         // Insert the data along with the generated Reference ID into the database
        $sql = "INSERT INTO students (last_name, first_name, middle_initial, gender, dob, nationality, email, contact_number, street, barangay, city, province, zip_code, student_type, previous_school, year_level, previous_program, desired_program, tor, school_id, birth_certificate, reference_id)
                VALUES ('$last_name', '$first_name', '$middle_initial', '$gender', '$dob', '$nationality', '$email', '$contact_number', '$street', '$barangay', '$city', '$province', '$zip_code', '$student_type', '$previous_school', '$year_level', '$previous_program', '$desired_program', '$tor', '$school_id', '$birth_certificate', '$reference_id')";

    if (mysqli_query($conn, $sql)) {
    // Send confirmation email to the user
    $to = $email;
    $subject = "Your Registration is Successful!";
    $message = "Dear $first_name $last_name, \n\nYou have successfully registered. Your Reference ID is: $reference_id.\nPlease keep this ID for future reference.\n\nThank you!";
    $headers = "From: admin@yourdomain.com";

    mail($to, $subject, $message, $headers);

    // Redirect to success page with reference ID
    header("Location: success.php?refid=$reference_id");
    exit();
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<section class="form-section">
        <div class="form-group head"> 
        <h1>Student Registration and Requirements Submission</h1>
        <img src="puplogo.png" alt="Right Logo" class="puplogo">
        </div>       

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
        <fieldset>
        <legend>Student Personal Information</legend>
        <div class="form-group name-gender">
            <div class="form-field">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last-name" required>
            </div>
            <div class="form-field">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-field">
                <label for="middle_initial">Middle Initial:</label>
                <input type="text" id="middle_initial" name="middle_initial">
            </div>

            <div class="form-field">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="" disabled selected>-- Select Gender --</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>

            </div>

        <div class="form-group contact">
            <div class="form-field">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required>
            </div>

            <div class="form-field">
                <label for="nationality">Nationality:</label>
                <input type="text" id="nationality" name="nationality" required>
            </div>

            <div class="form-field">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-field">
                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" required>
            </div>
        </div>

            <!-- Address -->
            <div class="form-group address1">
            <div class="form-field">
                <label for="street">Street:</label>
                <input type="text" id="street" name="street" required>
            </div>

            <div class="form-field">
                <label for="barangay">Barangay:</label>
                <input type="text" id="barangay" name="barangay" required>
            </div>

            <div class="form-field">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" required>
            </div>

            <div class="form-field">
                <label for="province">Province/Region:</label>
                <input type="text" id="province" name="province" required>
            </div>

            <div class="form-field">
                <label for="zip_code">Zip Code:</label>
                <input type="text" id="zip_code" name="zip_code" required>
            </div>
        </div>
        </fieldset>

            <!-- Academic Information -->
        <fieldset>
            <legend>Student Academic Information</legend>
            <div class="form-group school">
            <div class="form-field">
                <label for="student_type">Student Type:</label>
                <select id="student_type" name="student_type" required>
                    <option value="" disabled selected>-- Select Student Type --</option>
                    <option value="shiftee">Shiftee</option>
                    <option value="transferee">Transferee</option>
                    <option value="ladderized">Ladderized</option>
                </select>
            </div>

            <div class="form-field">
                <label for="previous_school">Name of Previous School:</label>
                <input type="text" id="previous_school" name="previous_school" required>
            </div>

            <div class="form-field">
                <label for="year_level">Current Year Level</label>
                <input type="text" id="year_level" name="year_level" required>
            </div>
            </div>

            <div class="form-group desired">
            <div class="form-field">
                <label for="previous_program">Previous Program:</label>
                <input type="text" id="previous_program" name="previous_program">
            </div>

            <div class="form-field">
                <label for="desired_program">Desired Program:</label>
                <select id="desired_program" name="desired_program" required>
                    <option value="" disabled selected>-- Select Desired Program --</option>
                    <option value="bsit">BS in Information Technology</option>
                    <option value="bsis">BS in Information Systems</option>
                    <option value="bsce">BS in Civil Engineering</option>
                </select>
            </div>
            </div>
            
            
            <!-- File Uploads -->
            <div class="form-group upload">
            <div class="form-field">
                <label for="tor">Upload Copy of TOR:</label>
                <input type="file" id="tor" name="tor" required>
            </div>
            <div class="form-field">
                <label for="school_id">Upload Copy of School ID:</label>
                <input type="file" id="school_id" name="school_id" required>
            </div>
            <div class="form-field">
                <label for="birth_certificate">Upload Copy of Birth Certificate:</label>
                <input type="file" id="birth_certificate" name="birth_certificate" required>
            </div>
            </div>
            </fieldset>
            <!-- Submit Button -->
            <button type="submit">SUBMIT</button>  
        </form>
    </div>

</body>
</html>
