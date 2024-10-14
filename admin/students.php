<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Table - Brand</title>

    <!-- External Stylesheets -->
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
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Include Topbar -->
                <?php include 'topbar.php'; ?>

                <!-- Main Container -->
                <div class="container-fluid">
                    <h3 class="text-dark mb-4">Manage Student</h3>
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <p class="text-primary m-0 fw-bold">Manage Student</p>
                        </div>
                        <div class="card-body" style="font-family: 'Open Sans', sans-serif;">
                            <div class="row">
                                <div class="col-md-6 col-xl-2 text-nowrap">
                                    <div id="dataTable_length" class="dataTables_length" aria-controls="dataTable"></div>
                                    <div id="dataTable_length-1" class="dataTables_length" aria-controls="dataTable" style="height: 49.6px; width: 119px;">
                                        <div class="btn-group">
                                            <button class="btn btn-primary" type="button" style="background: var(--bs-card-cap-bg); color: var(--bs-emphasis-color);">Sort by:</button>
                                            <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" type="button" style="color: var(--bs-body-color); background: var(--bs-btn-hover-color);"></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#">Reference Number</a>
                                                <a class="dropdown-item" href="#">Name</a>
                                                <a class="dropdown-item" href="#">Year</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col">
                                    <input type="search" class="form-control form-control-sm" aria-controls="dataTable" placeholder="Search" style="margin-top: -15px;">
                                </div>
                            </div>

                            <div class="table-responsive table mt-2" id="dataTable">
                                <table class="table my-0">
                                    <thead>
                                        <tr>
                                            <th>Reference Number</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Program</th>
                                            <th>Year</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>123</td>
                                            <td>John Lloyd Dugo</td>
                                            <td>JlDugo@gmail.com</td>
                                            <td>BS</td>
                                            <td>4</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                        <tr>
                                            <td>124</td>
                                            <td>Melody Tapay</td>
                                            <td>mtapay@gmail.com</td>
                                            <td>BS</td>
                                            <td>4</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                        <tr>
                                            <td>125</td>
                                            <td>Jillian Tangi</td>
                                            <td>jtangi12@gmail.com</td>
                                            <td>BS</td>
                                            <td>3</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                        <tr>
                                            <td>126</td>
                                            <td>Angel Lara</td>
                                            <td>allil20@gmail.com</td>
                                            <td>BS</td>
                                            <td>3</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                        <tr>
                                            <td>127</td>
                                            <td>Adi Mae Bontanao</td>
                                            <td>adimaeganda@gmail.com</td>
                                            <td>BS</td>
                                            <td>2</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                        <tr>
                                            <td>128</td>
                                            <td>Hazel Bolima</td>
                                            <td>zelbolima@gmail.com</td>
                                            <td>BS</td>
                                            <td>2</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                        <tr>
                                            <td>129</td>
                                            <td>Joy Capilla</td>
                                            <td>joyc72@gmail.com</td>
                                            <td>BS</td>
                                            <td>2</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                        <tr>
                                            <td>130</td>
                                            <td>Sue Ramirez</td>
                                            <td>suramirez@gmail.com</td>
                                            <td>BS</td>
                                            <td>2</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                        <tr>
                                            <td>131</td>
                                            <td>Ivy Aguas</td>
                                            <td>ivyaguas45@gmail.com</td>
                                            <td>BS</td>
                                            <td>2</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                        <tr>
                                            <td>132</td>
                                            <td>John Cena</td>
                                            <td>johnpogi@gmail.com</td>
                                            <td>BS</td>
                                            <td>2</td>
                                            <td><input type="checkbox"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="row">
                                <div class="col-md-6 align-self-center">
                                    <p id="dataTable_info" class="dataTables_info" role="status" aria-live="polite">Showing 1 to 10 of 27</p>
                                </div>
                                <div class="col-md-6">
                                    <nav class="d-lg-flex justify-content-lg-end dataTables_paginate paging_simple_numbers">
                                        <ul class="pagination">
                                            <li class="page-item disabled"><a class="page-link" aria-label="Previous" href="#"><span aria-hidden="true">«</span></a></li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item"><a class="page-link" aria-label="Next" href="#"><span aria-hidden="true">»</span></a></li>
                                        </ul>
                                    </nav>
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

    <!-- External Scripts -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="assets/js/script.min.js"></script>
</body>

</html>
