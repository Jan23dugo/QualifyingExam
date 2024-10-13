<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Create Exam - Brand</title>
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
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Include Topbar -->
                <?php include 'topbar.php'; ?>

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
            <?php include 'footer.php'; ?>
        </div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
    </div>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="assets/js/script.min.js"></script>
</body>
</html>
