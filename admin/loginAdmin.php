<?php
// Start session
session_start();

// Include the external database configuration file
require_once '../config/config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL to get user data
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Set session and redirect to success page
        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_id'] = $user['user_id'];
        header("Location: admin-dashboard.php");
        exit();
    } else {
        // Invalid login
        $error = "Invalid email or password.";
    }
    $stmt->close();
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
        }
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Login</title>

    <!-- External Stylesheets -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans&amp;display=swap">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.0.0/quill.snow.css">
    <style>
        .time-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 70%;
        }

        .time-input {
            flex: 1;
            padding-right: 40px; /* Space for buttons */
            text-align: center;
            cursor: pointer;
        }

        .arrow-buttons {
            position: absolute;
            right: 10px;
            display: flex;
            flex-direction: column;
        }

        .arrow-buttons button {
            background: none;
            border: none;
            cursor: pointer;
        }

        .highlight {
            background-color: #d1ecf1;
            border-radius: 4px;
        }

        .input-group {
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-gradient-primary" style="background: url('assets/img/PUP.jpg') center, rgb(182, 184, 188); height: 100vh;">
    <section class="d-lg-flex align-items-lg-center position-relative py-4 py-xl-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-md-8 col-xl-6 text-center mx-auto">
                    <h2>Welcome Back!</h2>
                    <p class="w-lg-50">Please login to access your account.</p>
                </div>
            </div>
            <div class="row d-flex justify-content-center">
                <div class="col-md-6 col-xl-4">
                    <div class="card mb-5">
                        <div class="card-body d-flex flex-column align-items-center" style="border-color: var(--bs-secondary-color); box-shadow: 0px 0px var(--bs-body-color);">
                            <div class="bs-icon-xl bs-icon-circle bs-icon-primary bs-icon my-4" style="background: var(--bs-card-cap-bg);">
                                <img src="assets/img/Logo.png" width="131" height="83" alt="Brand Logo">
                            </div>

                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <?php if (!isset($_SESSION['loggedin'])): ?>
                                <!-- Login Form -->
                                <form class="text-center" action="" method="post">
                                    <div class="mb-3">
                                        <input class="form-control" type="email" name="email" placeholder="Email" style="font-family: 'Open Sans', sans-serif;" required>
                                    </div>
                                    <div class="mb-3">
                                        <input class="form-control" type="password" name="password" placeholder="Password" style="font-family: 'Open Sans', sans-serif;" required>
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-primary d-block w-100" type="submit" style="font-family: 'Open Sans', sans-serif;">Login</button>
                                    </div>
                                    <p class="text-muted" style="font-family: 'Open Sans', sans-serif;">Forgot your password?</p>
                                </form>
                            <?php else: ?>
                                <!-- Success Message after Login -->
                                <div class="text-center">
                                    <h3>Welcome, <?php echo $_SESSION['email']; ?>!</h3>
                                    <p>You are logged in.</p>
                                    <a href="?logout" class="btn btn-danger">Logout</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- External Scripts -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="assets/js/script.min.js"></script>
</body>

</html>
