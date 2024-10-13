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
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.0.0/quill.snow.css">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <nav class="navbar align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0 navbar-dark" style="color: #005684;background: #005684;">
            <div class="container-fluid d-flex flex-column p-0">
                <a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#" style="text-align: center;">
                    <div class="sidebar-brand-icon rotate-n-15"></div>
                    <img src="assets/img/Logo.png" style="width: 47px;opacity: 1;">
                    <div class="sidebar-brand-text mx-3"></div>
                </a>
                <hr class="sidebar-divider my-0">
                <ul class="navbar-nav text-light" id="accordionSidebar">
                    <li class="nav-item">
                        <a class="nav-link" href="admin-dashboard.html" style="font-family: 'Open Sans', sans-serif;">
                            <i class="far fa-square" style="font-size: 21px;width: 20px;height: 20px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="create-exam.html">
                            <i class="far fa-edit" style="font-size: 23px;width: 20px;height: 20px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Create Exam</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.html">
                            <i class="far fa-user" style="font-size: 20px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.html">
                            <i class="fas fa-chart-bar" style="font-size: 21px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Analytics</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="calendar.html">
                            <i class="far fa-calendar-alt" style="font-size: 23px;"></i>
                            <span style="font-family: 'Open Sans', sans-serif;">Calendar</span>
                        </a>
                        <a class="nav-link" href="login.html">
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
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand bg-white shadow mb-4 topbar">
                    <div class="container-fluid">
                        <button class="btn btn-link d-md-none rounded-circle me-3" id="sidebarToggleTop" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                        <form class="d-none d-sm-inline-block me-auto ms-md-3 my-2 my-md-0 mw-100 navbar-search">
                            <div class="input-group">
                                <input class="bg-light form-control border-0 small" type="text" placeholder="Search for ...">
                                <button class="btn btn-primary py-0" type="button" style="background: rgb(255,255,255);">
                                    <i class="fas fa-search" style="font-size: 19px;color: var(--bs-secondary-color);"></i>
                                </button>
                            </div>
                        </form>
                        <ul class="navbar-nav flex-nowrap ms-auto">
                            <li class="nav-item dropdown no-arrow mx-1">
                                <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown" href="#" style="width: 60px;height: 60px;">
                                    <i class="far fa-user-circle" style="font-size: 30px;color: var(--bs-navbar-disabled-color);backdrop-filter: brightness(99%);-webkit-backdrop-filter: brightness(99%);"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
                <!-- Exam Creation Section -->
                <div class="col-md-6 col-lg-5 col-xl-5 boxContent" style="margin: 36px 0px;padding: 37px 20px;color: var(--bs-emphasis-color);font-family: 'Open Sans', sans-serif;">
                    <ul class="list-group">
                        <li class="list-group-item textoPasta">
                            <span><i class="fa fa-folder-open"></i> Qualifying Exam 2024</span>
                        </li>
                        <li class="list-group-item textoPasta">
                            <span><i class="fa fa-folder"></i> Sample Exam</span>
                        </li>
                    </ul>
                    <button class="btn btn-primary active" type="button" style="margin: 13px;" data-bs-target="#modal-1" data-bs-toggle="modal">
                        <strong>+</strong>
                    </button>
                    <!-- Create Exam Modal -->
                    <div class="modal fade" role="dialog" tabindex="-1" id="modal-1">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Create Exam</h4>
                                    <button class="btn-close" type="button" aria-label="Close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" style="height: 317px;padding: 36px;margin: 9px;">
                                    <div class="input-group">
                                        <span class="input-group-text" style="margin: 9px;">Exam Name:</span>
                                        <input class="form-control" type="text" style="margin: 9px;">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text" style="margin: 9px;">Description:</span>
                                        <input class="form-control" type="text" style="margin: 9px;">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text" style="margin: 9px;">Duration:</span>
                                        <input class="form-control" type="text" style="margin: 9px;">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text" style="margin: 9px;">Start Date:</span>
                                        <input class="form-control" type="date" style="margin: 9px;">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text" style="margin: 9px;">End Date:</span>
                                        <input class="form-control" type="date" style="margin: 9px;">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <a class="btn btn-primary" role="button" href="create-exam-1.html">Create</a>
                                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <footer class="bg-white sticky-footer">
                <div class="container my-auto">
                    <div class="text-center my-auto copyright">
                        <span>Copyright © PUP CCIS 2024</span>
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
