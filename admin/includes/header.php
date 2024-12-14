<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo $pageTitle ?? 'Qualifying Exam System'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.min.css">
    
    <?php if (isset($additionalHead)) echo $additionalHead; ?>
</head>
