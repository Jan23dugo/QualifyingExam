<!DOCTYPE html>
<html data-bs-theme="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Dashboard - Brand</title>

    <!-- External CSS and Fonts -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.0.0/quill.snow.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
               <!-- Include Topbar -->
               <?php include 'topbar.php'; ?>

                            
                           
                <!-- Main Content -->
                <div class="container" style="width: 886.6px; height: 382px; padding: 21px;">
                    <div class="row">
                        <div class="col-md-12" style="padding: 19px;"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3" style="height: 174px; box-shadow: 0px 0px rgb(89,89,92); font-family: 'Open Sans', sans-serif;">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 fw-bold" style="color: var(--bs-black); text-align: center; border-color: var(--bs-emphasis-color);">Upcoming Schedule:</h6>
                                </div>
                                <div class="card-body">
                                    <p class="m-0"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3" style="height: 174px; box-shadow: 0px 0px rgb(89,89,92); font-family: 'Open Sans', sans-serif;">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="text-primary m-0 fw-bold" style="text-align: center; color: var(--bs-emphasis-color);">Total Number of Registered Students:</h6>
                                </div>
                                <div class="card-body">
                                    <p class="m-0"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col" style="height: 352px;">
                            <div class="chart-area" style="width: 386.3px; height: 291px; text-align: left;">
                                <canvas data-bss-chart='{
                                    "type": "doughnut",
                                    "data": {
                                        "labels": ["Direct", "Social", "Referral"],
                                        "datasets": [{
                                            "backgroundColor": ["#4e73df", "#1cc88a", "#36b9cc"],
                                            "data": [50, 30, 15]
                                        }]
                                    },
                                    "options": {
                                        "maintainAspectRatio": false,
                                        "legend": {
                                            "display": false
                                        }
                                    }
                                }'></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <?php include 'footer.php'; ?>
    </div>

    <!-- External Scripts -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="assets/js/script.min.js"></script>
    <script src="assets/js/chart.min.min.js"></script>
</body>
</html>
