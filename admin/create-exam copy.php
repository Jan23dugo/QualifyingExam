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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.0.0/quill.snow.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="assets/css/styles.css"> 
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
<body id="page-top">
    <div id="wrapper">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Include Topbar -->
                <?php include 'topbar.php'; ?>

                <!-- Exam Creation Section -->
                <div class="col-md-6 col-lg-5 col-xl-5 boxContent" style="margin: 36px auto;padding: 37px 20px;color: var(--bs-emphasis-color);font-family: 'Open Sans', sans-serif;">
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
                                <div class="modal-body" style="height: auto;padding: 36px;margin: 9px;">
                                    <div class="input-group">
                                        <span class="input-group-text">Exam Name:</span>
                                        <input class="form-control" type="text">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text">Description:</span>
                                        <input class="form-control" type="text">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text">Duration:</span>
                                        <div class="time-input-wrapper">
                                            <span class="form-control time-input" id="duration-input">
                                                <span id="hours" class="highlight">12</span>:<span id="minutes">00</span> <span id="ampm">PM</span>
                                            </span>
                                            <div class="arrow-buttons">
                                                <button type="button" onclick="adjustTime('up')">
                                                    <i class="fas fa-chevron-up"></i>
                                                </button>
                                                <button type="button" onclick="adjustTime('down')">
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text">Start Date:</span>
                                        <input class="form-control" type="date">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text">End Date:</span>
                                        <input class="form-control" type="date">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <a class="btn btn-primary" role="button" href="create-exam-1.php">Create</a>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="assets/js/script.min.js"></script>
    <script>
        let currentPart = 'hours';

        // Click Event to Highlight and Change the Part of Time Being Adjusted
        document.getElementById('hours').addEventListener('click', function () {
            highlightPart('hours');
        });

        document.getElementById('minutes').addEventListener('click', function () {
            highlightPart('minutes');
        });

        document.getElementById('ampm').addEventListener('click', function () {
            highlightPart('ampm');
        });

        function highlightPart(part) {
            currentPart = part;

            // Remove highlight from all parts
            document.getElementById('hours').classList.remove('highlight');
            document.getElementById('minutes').classList.remove('highlight');
            document.getElementById('ampm').classList.remove('highlight');

            // Add highlight to selected part
            document.getElementById(part).classList.add('highlight');
        }

        function adjustTime(direction) {
            let hoursElement = document.getElementById('hours');
            let minutesElement = document.getElementById('minutes');
            let ampmElement = document.getElementById('ampm');

            let hours = parseInt(hoursElement.innerText);
            let minutes = parseInt(minutesElement.innerText);
            let ampm = ampmElement.innerText;

            if (currentPart === 'hours') {
                hours = direction === 'up' ? (hours % 12) + 1 : (hours - 1 <= 0 ? 12 : hours - 1);
            } else if (currentPart === 'minutes') {
                minutes = direction === 'up' ? (minutes + 1) % 60 : (minutes - 1 < 0 ? 59 : minutes - 1);
            } else if (currentPart === 'ampm') {
                ampm = ampm === 'AM' ? 'PM' : 'AM';
            }

            // Update DOM elements
            hoursElement.innerText = hours;
            minutesElement.innerText = minutes < 10 ? '0' + minutes : minutes;
            ampmElement.innerText = ampm;
        }

        // Remove highlight when user stops interacting
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.time-input-wrapper')) {
                document.getElementById('hours').classList.remove('highlight');
                document.getElementById('minutes').classList.remove('highlight');
                document.getElementById('ampm').classList.remove('highlight');
            }
        });
    </script>
</body>
</html>