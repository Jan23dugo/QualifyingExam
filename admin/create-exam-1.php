<?php
header('Content-Type: text/html; charset=UTF-8');

// Include the database configuration file
include_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Profile - Brand</title>

    <!-- External Stylesheets -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="assets/css/animate.min.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.0.0/quill.snow.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <nav class="navbar align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0 navbar-dark" style="color: #005684; background: #005684;">
            <div class="container-fluid d-flex flex-column p-0">
                <a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#" style="text-align: center;">
                    <div class="sidebar-brand-icon rotate-n-15"></div>
                    <img src="assets/img/Logo.png" style="width: 47px; opacity: 1;">
                    <div class="sidebar-brand-text mx-3"></div>
                </a>
                <hr class="sidebar-divider my-0">
                <ul class="navbar-nav text-light" id="accordionSidebar">
                    <li class="nav-item">
                        <a class="nav-link" href="admin-dashboard.php" style="font-family: 'Open Sans', sans-serif;">
                            <i class="far fa-square" style="font-size: 21px; width: 20px; height: 20px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create-exam.php">
                            <i class="far fa-edit" style="font-size: 23px; width: 20px; height: 20px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Create Exam</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">
                            <i class="far fa-user" style="font-size: 20px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.php">
                            <i class="fas fa-chart-bar" style="font-size: 21px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Analytics</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="calendar.php">
                            <i class="far fa-calendar-alt" style="font-size: 23px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Calendar</span>
                        </a>
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-out-alt" style="font-size: 20px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Sign Out</span>
                        </a>
                    </li>
                </ul>
                <div class="text-center d-none d-md-inline">
                    <button class="btn rounded-circle border-0" id="sidebarToggle" type="button"></button>
                </div>
            </div>
        </nav>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content" style="padding: 5px; margin: -2px;">
                <!-- Topbar -->
                <nav class="navbar navbar-expand bg-white shadow mb-4 topbar">
                    <div class="container-fluid">
                        <button class="btn btn-link d-md-none rounded-circle me-3" id="sidebarToggleTop" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                        <ul class="navbar-nav flex-nowrap ms-auto">
                            <li class="nav-item dropdown d-sm-none no-arrow">
                                <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown" href="#">
                                    <i class="fas fa-search"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-3 animated--grow-in" aria-labelledby="searchDropdown">
                                    <form class="me-auto navbar-search w-100">
                                        <div class="input-group">
                                            <input class="bg-light border-0 form-control small" type="text" placeholder="Search for ...">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </li>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow">
                                    <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown" href="#" style="width: 60px; height: 60px;">
                                        <i class="fas fa-cog" style="font-size: 30px; color: var(--bs-navbar-disabled-color);"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6>
                                        <a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="me-3">
                                                <div class="bg-primary icon-circle">
                                                    <i class="fas fa-file-alt text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="small text-gray-500">December 12, 2019</span>
                                                <p>A new monthly report is ready to download!</p>
                                            </div>
                                        </a>
                                        <a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="me-3">
                                                <div class="bg-success icon-circle">
                                                    <i class="fas fa-donate text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="small text-gray-500">December 7, 2019</span>
                                                <p>$290.29 has been deposited into your account!</p>
                                            </div>
                                        </a>
                                        <a class="dropdown-item d-flex align-items-center" href="#">
                                            <div class="me-3">
                                                <div class="bg-warning icon-circle">
                                                    <i class="fas fa-exclamation-triangle text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="small text-gray-500">December 2, 2019</span>
                                                <p>Spending Alert: We've noticed unusually high spending for your account.</p>
                                            </div>
                                        </a>
                                        <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                                    </div>
                                </div>
                            </li>
                            <li class="nav-item dropdown no-arrow mx-1">
                                <div class="nav-item dropdown no-arrow">
                                    <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown" href="#" style="width: 60px; height: 60px;">
                                        <i class="far fa-user-circle" style="font-size: 30px; color: var(--bs-navbar-disabled-color); backdrop-filter: brightness(99%); -webkit-backdrop-filter: brightness(99%);"></i>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Main Container for Exam Creation -->
                <div class="container"></div>

                <!-- Tab Navigation -->
                <div>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" role="tab" data-bs-toggle="tab" href="#tab-1">Question</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" role="tab" data-bs-toggle="tab" href="#tab-2">Preview</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" role="tab" data-bs-toggle="tab" href="#tab-3">Settings</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" role="tab" data-bs-toggle="tab" href="#tab-4">Result</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab 1: Add Questions -->
                        <div class="tab-pane" role="tabpanel" id="tab-1">
                            <div class="col-md-12">
                                <div class="input-group" style="margin: 34px; width: 283.6px;">
                                    <span class="input-group-text" style="box-shadow: 0px 0px 4px;">+ Add question</span>
                                    <select class="form-select" style="box-shadow: 0px 0px 10px var(--bs-body-color);">
                                        <optgroup label="Select question type">
                                            <option value="12">Multiple choice</option>
                                            <option value="12" selected>Identification</option>
                                            <option value="13">True or False</option>
                                            <option value="14">Coding</option>
                                            <option value="12">Matching</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <!-- Example for Multiple Choice -->
                            <div class="container" style="margin: 37px; border-style: solid; border-color: var(--bs-body-color); height: 679.4px; width: 887.6px; border-radius: 14px; box-shadow: 0px 0px 7px;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5 style="height: 22px; margin: 17px;">Multiple choice</h5>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <span class="input-group-text"><span style="color: rgb(0, 0, 0);">Input question</span></span>
                                            <textarea class="form-control" name="question"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="input-group" style="padding: 17px;"></div>
                                <small class="d-xxl-flex justify-content-xxl-end">Correct Answer</small>

                                <div class="input-group" style="padding: 17px;">
                                    <span class="input-group-text">Choice 1</span>
                                    <input class="form-control" type="text" name="choice1" style="width: 187.663px; border-color: var(--bs-emphasis-color);">
                                    <button class="btn btn-primary" type="button" style="color: var(--bs-btn-color); background: var(--bs-btn-color); border-color: var(--bs-btn-color); margin-left: 19px;">
                                        <input type="checkbox" name="correct_answer[]">
                                    </button>
                                </div>

                                <!-- More choices can be added similarly -->

                                <div class="input-group d-xxl-flex justify-content-xxl-center" style="padding: 8px; width: 817.8px; margin: 9px;">
                                    <button class="btn btn-primary d-xxl-flex" type="button" style="width: 129.425px; font-size: 12px;">+ Add more choice</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Preview -->
                        <div class="tab-pane active" role="tabpanel" id="tab-2">
                            <div class="container">
                                <div class="row bounce animated">
                                    <div class="col-md-12 col-xl-2 d-xl-flex justify-content-xl-start">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-primary d-xl-flex justify-content-xl-start" type="button">Start attempt</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional tabs for settings, results, etc. -->
                        <!-- Tab 3: Settings, Tab 4: Results... -->
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="bg-white sticky-footer">
                <div class="container my-auto">
                    <div class="text-center my-auto copyright">
                        <span>Copyright &copy; PUP CCIS 2024</span>
                    </div>
                </div>
            </footer>
        </div>

        <a class="border rounded d-inline scroll-to-top" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
    </div>

    <!-- External Scripts -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="assets/js/script.min.js"></script>
</body>

</html>