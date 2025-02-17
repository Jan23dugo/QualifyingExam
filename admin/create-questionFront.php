<!DOCTYPE html>
<html data-bs-theme="light" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Profile - Brand</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,400i,700,700i">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.0.0/quill.snow.css">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>
            <div class="d-flex flex-column" id="content-wrapper">
                <div id="content" style="padding: 5px;margin: -2px;height: 1509.2px;border-color: rgb(0,0,0);">
                    <nav class="navbar navbar-expand bg-white shadow mb-4 topbar" style="background: var(--bs-nav-link-disabled-color);">
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
                                        <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown" href="#" style="width: 60px;height: 60px;">
                                            <i class="fas fa-cog" style="font-size: 30px;color: var(--bs-navbar-disabled-color);"></i>
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
                                                <div class="me-3"><div class="bg-success icon-circle">
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
                                    <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown" href="#" style="width: 60px;height: 60px;">
                                        <i class="far fa-user-circle" style="font-size: 30px;color: var(--bs-navbar-disabled-color);backdrop-filter: brightness(99%);-webkit-backdrop-filter: brightness(99%);"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end dropdown-list animated--grow-in">
                                        <h6 class="dropdown-header">alerts center</h6>
                                        <a class="dropdown-item d-flex align-items-center" href="#"><div class="dropdown-list-image me-3">
                                            <img class="rounded-circle" src="assets/img/avatars/avatar4.jpeg">
                                            <div class="bg-success status-indicator">

                                            </div>
                                        </div>
                                        <div class="fw-bold"><div class="text-truncate">
                                            <span>Hi there! I am wondering if you can help me with a problem I've been having.</span>
                                        </div>
                                        <p class="small text-gray-500 mb-0">Emily Fowler - 58m</p>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#"><div class="dropdown-list-image me-3">
                                    <img class="rounded-circle" src="assets/img/avatars/avatar2.jpeg">
                                    <div class="status-indicator"></div></div><div class="fw-bold">
                                        <div class="text-truncate">
                                            <span>I have the photos that you ordered last month!</span>
                                        </div>
                                        <p class="small text-gray-500 mb-0">Jae Chun - 1d</p>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image me-3">
                                        <img class="rounded-circle" src="assets/img/avatars/avatar3.jpeg">
                                        <div class="bg-warning status-indicator">

                                        </div>
                                    </div>
                                    <div class="fw-bold"><div class="text-truncate">
                                        <span>Last month's report looks great, I am very happy with the progress so far, keep up the good work!</span>
                                    </div>
                                    <p class="small text-gray-500 mb-0">Morgan Alvarez - 2d</p>
                                </div>
                            </a>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div class="dropdown-list-image me-3">
                                    <img class="rounded-circle" src="assets/img/avatars/avatar5.jpeg">
                                    <div class="bg-success status-indicator">

                                    </div>
                                </div>
                                <div class="fw-bold">
                                    <div class="text-truncate">
                                        <span>Am I a good boy? The reason I ask is because someone told me that people say this to all dogs, even if they aren't good...</span>
                                    </div>
                                    <p class="small text-gray-500 mb-0">Chicken the Dog · 2w</p>
                                </div>
                            </a>
                            <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                        </div>
                    </div>
                    <div class="shadow dropdown-list dropdown-menu dropdown-menu-end" aria-labelledby="alertsDropdown">

                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">

    </div>
    <div>
        <ul class="nav nav-underline" role="tablist">
            <li class="nav-item" role="presentation"><a class="nav-link" role="tab" data-bs-toggle="tab" href="#tab-1">Question</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link active" role="tab" data-bs-toggle="tab" href="#tab-2">Preview</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" role="tab" data-bs-toggle="tab" href="#tab-3">Settings</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link" role="tab" data-bs-toggle="tab" href="#tab-4">Result</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane" role="tabpanel" id="tab-1">
                <div class="col-md-12" style="width: 1147.8px;padding: 13px;">
                    <div class="container" style="width: 1212.8px;">
                    <div class="col" style="box-shadow: 0px 0px 5px 3px;border-radius: 12px;padding: 17px;margin: 48px;width: 1103.8px;">
                        <div>

                        </div>
                        <div class="table-responsive"><table class="table">
                            <thead>
                                <tr>
                                    <th>

                                    </th>
                                    <th class="d-xxl-flex justify-content-xxl-center" style="width: 703.862px;">CCIS Qualifying Exam 2024</th>
                                    <th style="font-size: 14px;"><span style="color: rgba(0, 0, 0, 0.51);">5 questions</span></th>
                                    <th style="font-size: 14px;"><span style="color: rgba(0, 0, 0, 0.51);">5 points</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="width: 70.238px;"><strong>1</strong></td>
                                    <td style="width: 689.85px;">Question 1<br><span style="color: rgba(0, 0, 0, 0.76);">Multiple choices</span></td>
                                    <td>
                                        <div>
                                            <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-7" href="#collapse-7" role="button">Edit</a>
                                            <div class="collapse" id="collapse-7">
                                                <p>Collapse content.</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" type="button">Remove</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>2</strong></td>
                                    <td>Question 2<br><span style="color: rgba(0, 0, 0, 0.74);">Identification</span></td>
                                    <td>
                                        <div>
                                            <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-9" href="#collapse-9" role="button">Edit</a>
                                            <div class="collapse" id="collapse-9">
                                                <p>Collapse content.</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" type="button">Remove</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>3</strong></td>
                                    <td>Question 3<br><span style="color: rgba(0, 0, 0, 0.74);">Identification</span></td>
                                    <td>
                                        <div>
                                            <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-10" href="#collapse-10" role="button">Edit</a>
                                            <div class="collapse" id="collapse-10">
                                                <p>Collapse content.</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><button class="btn btn-primary" type="button">Remove</button></td>
                                </tr>
                                <tr>
                                    <td><strong>4</strong></td>
                                    <td>Question 4<br><span style="color: rgba(0, 0, 0, 0.74);">Identification</span></td>
                                    <td>
                                        <div>
                                            <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-11" href="#collapse-11" role="button">Edit</a>
                                            <div class="collapse" id="collapse-11">
                                                <p>Collapse content.</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><button class="btn btn-primary" type="button">Remove</button></td>
                                </tr>
                                <tr>
                                    <td><strong>5</strong></td><td>Question 5<br><span style="color: rgba(0, 0, 0, 0.74);">Identification</span></td>
                                    <td>
                                        <div>
                                            <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-12" href="#collapse-12" role="button">Edit</a>
                                            <div class="collapse" id="collapse-12">
                                                <p>Collapse content.</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><button class="btn btn-primary" type="button">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div style="height: 187.6px;">
                    <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-3" href="#collapse-3" role="button" style="margin: 18px;"><strong>Add question&nbsp;</strong>
                        <i class="fas fa-plus"></i>
                    </a>
                    <div class="collapse" id="collapse-3">
                        <div style="height: 74px;">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="height: 54px;">
                                                <div style="height: 44.6px;">
                                                    <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-4" href="#collapse-4" role="button" style="background: rgb(255,255,255);color: var(--bs-table-striped-color);height: -68.4px;">Multiple Choice</a>
                                                    <div class="collapse" id="collapse-4">
                                                        <div class="container" style="height: 769px;width: 912.6px;">
                                                            <div style="height: 832px;width: 873.275px;box-shadow: 0px 0px 7px;border-radius: 7px;">
                                                                <div class="table-responsive">
                                                                    <table class="table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th style="width: 707.8125px;height: 15.4px;">
                                                                                    <h5 style="height: 22px;margin: 17px;">Multiple choice</h5>
                                                                                </th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <th style="width: 799.812px;">
                                                                                    <div class="input-group" style="padding: 17px;">
                                                                                        <span class="input-group-text">
                                                                                            <span style="color: rgb(0, 0, 0);">Input question</span>
                                                                                        </span>
                                                                                        <textarea class="form-control" style="height: 61.6px;width: 480.138px;"></textarea>
                                                                                        <div class="input-group-text">
                                                                                            <i class="far fa-file-image" style="font-size: 31px;"></i>
                                                                                        </div>
                                                                                        <div class="input-group-text">
                                                                                            <div class="btn-group">
                                                                                                <button class="btn btn-primary" type="button" style="height: 48px;">
                                                                                                    <i class="fas fa-cog"></i>
                                                                                                </button>
                                                                                                <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" type="button"></button>
                                                                                                <div class="dropdown-menu">
                                                                                                    <a class="dropdown-item" href="create-exam-1_Coding-1.html">Coding</a>
                                                                                                    <a class="dropdown-item" href="create-exam-1_Identification-1.html">Identification</a>
                                                                                                    <a class="dropdown-item" href="create-exam-1_Matching.html">Matching</a><a class="dropdown-item" href="create-exam-1_Multiple-choice.html">Multiple choice</a><a class="dropdown-item" href="create-exam-1_TrueOrFalse.html">True or false</a>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </th>
                                                                            </tr>
                                                                            <tr>
                                                                                <th class="d-xxl-flex justify-content-xxl-end">Correct Answer</th>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div class="table-responsive" style="height: 528px;">
                                                                    <table class="table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th style="width: 245.625px;height: 356.8px;">
                                                                                    <div class="input-group" style="padding: 17px;">
                                                                                        <span class="input-group-text">Choice 1</span>
                                                                                        <input class="form-control" type="text" style="width: 187.663px;border-color: var(--bs-emphasis-color);">
                                                                                        <button class="btn btn-primary" type="button" style="color: var(--bs-btn-color);background: var(--bs-btn-color);border-color: var(--bs-btn-color);margin-left: 19px;"><input type="checkbox"></button>
                                                                                    </div>
                                                                                    <div class="input-group" style="padding: 17px;">
                                                                                        <span class="input-group-text">Choice 2</span>
                                                                                        <input class="form-control" type="text" style="width: 187.663px;border-color: var(--bs-emphasis-color);">
                                                                                        <button class="btn btn-primary" type="button" style="color: var(--bs-btn-color);background: var(--bs-btn-color);border-color: var(--bs-btn-color);margin-left: 19px;"><input type="checkbox"></button>
                                                                                    </div>
                                                                                    <div class="input-group" style="padding: 17px;">
                                                                                        <span class="input-group-text">Choice 3</span>
                                                                                        <input class="form-control" type="text" style="width: 187.663px;border-color: var(--bs-emphasis-color);">
                                                                                        <button class="btn btn-primary" type="button" style="color: var(--bs-btn-color);background: var(--bs-btn-color);border-color: var(--bs-btn-color);margin-left: 19px;"><input type="checkbox"></button>
                                                                                    </div>
                                                                                    <div class="input-group" style="padding: 17px;">
                                                                                        <span class="input-group-text">Choice 4</span>
                                                                                        <input class="form-control" type="text" style="width: 187.663px;border-color: var(--bs-emphasis-color);">
                                                                                        <button class="btn btn-primary" type="button" style="color: var(--bs-btn-color);background: var(--bs-btn-color);border-color: var(--bs-btn-color);margin-left: 19px;"><input type="checkbox"></button>
                                                                                    </div>
                                                                                    <div class="col d-xxl-flex justify-content-xxl-center" style="height: 42px;width: 840.8px;"><div class="form-check" style="width: 175.8px;margin: 13px;">
                                                                                        <input class="form-check-input" type="checkbox" id="formCheck-1">
                                                                                        <label class="form-check-label d-xxl-flex justify-content-xxl-center" for="formCheck-1">Randomized choices</label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col d-xxl-flex justify-content-xxl-center align-items-xxl-center" style="height: 42px;width: 838.8px;">
                                                                                    <small style="font-size: 16px;">Grading:&nbsp;</small>
                                                                                    <input type="text" style="height: 17px;width: 39.8px;padding: 2px 2px;">
                                                                                    <small style="font-size: 16px;">pts.</small>
                                                                                </div>
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>

                                                                        </tr>
                                                                        <tr>
                                                                            <th class="d-xxl-flex justify-content-xxl-center" style="height: 77.4px;">
                                                                                <button class="btn btn-primary d-xxl-flex" type="button" style="width: 129.425px;font-size: 12px;margin: 15px;">+ Add more choice</button>
                                                                            </th>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="btn-group" role="group" style="margin: 11px;">
                                                                <button class="btn btn-primary" type="button" style="background-color: #35aa47;margin-right: 3px;">Add</button>
                                                            </div>
                                                            <div class="input-group d-xxl-flex justify-content-xxl-center" style="padding: 8px;width: 821.8px;margin: 9px;">

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12"><div class="btn-group" role="group">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="height: 51.4px;">
                                        <div>
                                            <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-5" href="#collapse-5" role="button" style="background: rgb(255,255,255);color: var(--bs-emphasis-color);">True or False</a>
                                            <div class="collapse" id="collapse-5"><div style="height: 436px;width: 894.275px;box-shadow: 0px 0px 7px;border-radius: 7px;">
                                                <h5 style="height: 22px;margin: 17px;padding: 22px;">True or False</h5>
                                                <div class="input-group" style="padding: 17px;width: 898.275px;">
                                                    <span class="input-group-text">
                                                        <span style="color: rgb(0, 0, 0);">Input question</span>
                                                    </span>
                                                    <textarea class="form-control" style="height: 61.6px;width: 480.138px;"></textarea>
                                                    <div class="input-group-text">
                                                        <i class="far fa-file-image" style="font-size: 31px;"></i>
                                                    </div>
                                                    <div class="input-group-text">
                                                        <div class="btn-group">
                                                            <button class="btn btn-primary" type="button" style="height: 48px;">
                                                                <i class="fas fa-cog"></i>
                                                            </button>
                                                            <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" type="button"></button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item" href="create-exam-1_Coding-1.html">Coding</a>
                                                                <a class="dropdown-item" href="create-exam-1_Identification-1.html">Identification</a>
                                                                <a class="dropdown-item" href="create-exam-1_Matching.html">Matching</a>
                                                                <a class="dropdown-item" href="create-exam-1_Multiple-choice.html">Multiple choice</a>
                                                                <a class="dropdown-item" href="create-exam-1_TrueOrFalse.html">True or false</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="input-group d-xxl-flex justify-content-xxl-center" style="padding: 17px;height: 57.6px;">
                                                    <p class="input-group-text" style="border-style: none;">Correct Answer</p>
                                                </div>
                                                <div class="input-group d-xxl-flex justify-content-xxl-center" style="padding: 17px;height: 56.6px;">
                                                    <span class="input-group-text">True</span>
                                                    <button class="btn btn-primary" type="button" style="color: var(--bs-btn-color);background: var(--bs-btn-color);border-color: var(--bs-btn-color);margin-left: 19px;"><input type="checkbox"></button>
                                                </div>
                                                <div class="input-group d-xxl-flex justify-content-xxl-center" style="padding: 17px;height: 57.6px;">
                                                    <span class="input-group-text">False</span>
                                                    <button class="btn btn-primary" type="button" style="color: var(--bs-btn-color);background: var(--bs-btn-color);border-color: var(--bs-btn-color);margin-left: 19px;"><input type="checkbox"></button>
                                                </div>
                                                <div class="col d-xxl-flex justify-content-xxl-center align-items-xxl-center" style="height: 38px;width: 893.8px;border-top-style: solid;">
                                                    <small style="font-size: 16px;">Grading:&nbsp;</small><input type="text" style="height: 17px;width: 39.8px;padding: 2px 2px;"><small style="font-size: 16px;">pts.</small>
                                                </div>
                                                <div class="btn-group" role="group" style="padding: 11px;">
                                                    <a class="btn btn-primary border rounded-0" role="button" style="margin-right: 3px;background-color: #35aa47;" href="create-exam-1_Coding-1.html">Add</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="height: 55.8px;">
                                    <div>
                                        <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-6" href="#collapse-6" role="button" style="background: rgb(255,255,255);color: rgb(0,0,0);">Matching</a>
                                        <div class="collapse" id="collapse-6">
                                            <div style="height: 616px;width: 899.275px;box-shadow: 0px 0px 7px;border-radius: 7px;">
                                                <div class="col-md-12">
                                                    <h5 style="height: 22px;margin: 17px;padding: 15px;">Matching</h5>
                                                </div>
                                                <div class="col"><div class="input-group" style="padding: 17px;">
                                                    <span class="input-group-text">
                                                        <span style="color: rgb(0, 0, 0);">Input question</span>
                                                    </span>
                                                    <textarea class="form-control" style="height: 61.6px;width: 440.138px;"></textarea>
                                                    <div class="input-group-text">
                                                        <i class="far fa-file-image" style="font-size: 32px;"></i>
                                                    </div>
                                                    <div class="input-group-text">
                                                        <i class="far fa-trash-alt d-xxl-flex align-items-xxl-center" style="font-size: 30px;"></i>
                                                    </div>
                                                    <div class="d-xxl-flex align-items-xxl-center input-group-text" style="width: 98.8px;height: 64.4px;"><div class="btn-group" style="margin: 4px;height: 31.4px;">
                                                        <button class="btn btn-primary" type="button" style="background: rgb(6,89,149);"><i class="far fa-plus-square" style="font-size: 23px;"></i></button>
                                                        <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" type="button" style="background: rgb(6,89,149);"></button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="create-exam-1_Coding-1.html">Coding</a>
                                                            <a class="dropdown-item" href="create-exam-1_Identification-1.html">Identification</a>
                                                            <a class="dropdown-item" href="create-exam-1_Matching.html">Matching</a>
                                                            <a class="dropdown-item" href="create-exam-1_Multiple-choice.html">Multiple choice</a>
                                                            <a class="dropdown-item" href="create-exam-1_TrueOrFalse.html">True or false</a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12" style="width: 265.8px;">
                                                        <div class="btn-group" role="group">

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-group" style="padding: 23px;width: 833.8px;">
                                            <span class="input-group-text">Question 1</span>
                                            <input class="form-control" type="text" style="border-color: var(--bs-emphasis-color);">
                                            <span class="input-group-text" style="padding: -13px 12px;">Answer 1</span>
                                            <input class="form-control" type="text" style="border-color: var(--bs-emphasis-color);">
                                        </div>
                                        <div class="input-group" style="padding: 23px;width: 833.8px;">
                                            <span class="input-group-text">Question 2</span>
                                            <input class="form-control" type="text" style="border-color: var(--bs-emphasis-color);">
                                            <span class="input-group-text" style="padding: -13px 12px;">Answer 2</span>
                                            <input class="form-control" type="text" style="border-color: var(--bs-emphasis-color);">
                                        </div>
                                        <div class="input-group" style="padding: 23px;width: 833.8px;">
                                            <span class="input-group-text">Question 3</span>
                                            <input class="form-control" type="text" style="border-color: var(--bs-emphasis-color);">
                                            <span class="input-group-text" style="padding: -13px 12px;">Answer 3</span>
                                            <input class="form-control" type="text" style="border-color: var(--bs-emphasis-color);">
                                        </div>
                                        <div class="input-group d-xxl-flex justify-content-xxl-center" style="padding: 8px;width: 817.8px;margin: 9px;">
                                            <button class="btn btn-primary d-xxl-flex" type="button">+ Add more choice</button>
                                        </div>
                                        <div class="col d-xxl-flex justify-content-xxl-center" style="height: 42px;width: 840.8px;">
                                            <div class="form-check" style="width: 175.8px;margin: 13px;">
                                                <input class="form-check-input" type="checkbox" id="formCheck-3">
                                                <label class="form-check-label d-xxl-flex justify-content-xxl-center" for="formCheck-3">Randomized choices</label>
                                            </div>
                                        </div>
                                        <div class="col d-xxl-flex justify-content-xxl-center align-items-xxl-center" style="height: 42px;width: 840.8px;">
                                            <small style="font-size: 16px;">Grading</small>
                                            <input type="text" style="height: 17px;width: 39.8px;padding: 2px 2px;">
                                            <small style="font-size: 16px;">pts.</small>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="btn-group" role="group" style="padding: 10px;">
                                                <button class="btn btn-primary border rounded-0" type="button" style="margin-right: 3px;background-color: #35aa47;">Add</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 269.6px;height: 62.8px;">
                            <div>
                                <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-8" href="#collapse-8" role="button" style="background: var(--bs-btn-color);color: rgb(0,0,0);">Coding</a>
                                <div class="collapse" id="collapse-8"><div class="container" style="margin: 37px;border-style: solid;border-color: var(--bs-body-color);height: 454.4px;width: 853.6px;border-radius: 14px;box-shadow: 0px 0px 7px;">
                                    <div class="row">
                                        <div class="col-md-12" style="height: 48px;width: 737.8px;">
                                            <h5 class="d-xxl-flex justify-content-xxl-center" style="height: 22px;margin: 17px;">Coding</h5>
                                        </div>
                                    </div>
                                    <div class="row" style="width: 836.8px;">
                                        <div class="col-md-12 d-xxl-flex justify-content-xxl-center" style="width: 826.8px;">
                                            <div class="input-group" style="padding: 17px;width: 805.8px;">
                                                <span class="input-group-text"><span style="color: rgb(0, 0, 0);">Input question</span>
                                            </span>
                                            <textarea class="form-control" style="height: 61.6px;width: 440.138px;"></textarea>
                                            <div class="input-group-text">
                                                <i class="far fa-file-image" style="font-size: 32px;"></i>
                                            </div>
                                            <div class="input-group-text">
                                                <i class="far fa-trash-alt d-xxl-flex align-items-xxl-center" style="font-size: 30px;"></i>
                                            </div>
                                            <div class="d-xxl-flex align-items-xxl-center input-group-text" style="width: 98.8px;height: 64.4px;">
                                                <div class="btn-group" style="margin: 4px;height: 31.4px;">
                                                    <button class="btn btn-primary" type="button" style="background: rgb(6,89,149);">
                                                        <i class="far fa-plus-square" style="font-size: 23px;"></i>
                                                    </button>
                                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" type="button" style="background: rgb(6,89,149);"></button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="create-exam-1_Coding-1.html">Coding</a>
                                                        <a class="dropdown-item" href="create-exam-1_Identification-1.html">Identification</a>
                                                        <a class="dropdown-item" href="create-exam-1_Matching.html">Matching</a>
                                                        <a class="dropdown-item" href="create-exam-1_Multiple-choice.html">Multiple choice</a>
                                                        <a class="dropdown-item" href="create-exam-1_TrueOrFalse.html">True or false</a>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" style="width: 265.8px;">
                                                    <div class="btn-group" role="group"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-group" style="padding: 17px;width: 801.8px;">
                                    <div class="btn-group">
                                        <button class="btn btn-primary rounded-0 rounded-start" type="button">Choose programming language:</button>
                                        <button class="btn btn-primary dropdown-toggle dropdown-toggle-split rounded-0" data-bs-toggle="dropdown" aria-expanded="false" type="button"></button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#">C</a>
                                            <a class="dropdown-item" href="#">Java</a>
                                            <a class="dropdown-item" href="#">Python</a>
                                        </div>
                                    </div>
                                    <textarea class="form-control" style="width: 148.8px;"></textarea>
                                </div>
                                <div class="input-group d-xxl-flex justify-content-xxl-center" style="padding: 8px;width: 817.8px;margin: 9px;">
                                    <textarea class="form-control"></textarea>
                                </div>
                                <div class="col d-xxl-flex justify-content-xxl-center align-items-xxl-center" style="height: 42px;width: 788.8px;border-top-style: solid;">
                                    <small style="font-size: 16px;">Grading</small>
                                    <input type="text" style="height: 17px;width: 39.8px;padding: 2px 2px;">
                                    <small style="font-size: 16px;">pts.</small>
                                </div>
                                <div class="col-md-12">
                                    <div class="btn-group" role="group">
                                        <a class="btn btn-primary border rounded-0" role="button" style="margin-right: 3px;background-color: #35aa47;" href="create-exam-1.html">Add</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div>
                        <a class="btn btn-primary" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapse-2" href="#collapse-2" role="button" style="background: var(--bs-btn-color);color: rgb(0,0,0);">Identification</a>
                        <div class="collapse" id="collapse-2">
                            <div class="container" style="margin: 37px;border-style: solid;border-color: var(--bs-body-color);height: 375.4px;width: 830.6px;border-radius: 14px;box-shadow: 0px 0px 7px;">
                                <div class="row"><div class="col-md-12" style="width: 788.8px;">
                                    <h5 style="height: 22px;margin: 17px;">Identification</h5>
                                </div>
                            </div>
                            <div class="row"><div class="col-md-12">
                                <div class="input-group" style="padding: 17px;width: 800.8px;">
                                    <span class="input-group-text">
                                        <span style="color: rgb(0, 0, 0);">Input question</span>
                                    </span>
                                    <textarea class="form-control" style="height: 61.6px;width: 440.138px;"></textarea>
                                    <div class="input-group-text">
                                        <i class="far fa-file-image" style="font-size: 32px;"></i>
                                    </div>
                                    <div class="input-group-text">
                                        <i class="far fa-trash-alt d-xxl-flex align-items-xxl-center" style="font-size: 30px;"></i>
                                    </div>
                                    <div class="d-xxl-flex align-items-xxl-center input-group-text" style="width: 98.8px;height: 64.4px;">
                                        <div class="btn-group" style="margin: 4px;height: 31.4px;">
                                            <button class="btn btn-primary" type="button" style="background: rgb(6,89,149);"><i class="far fa-plus-square" style="font-size: 23px;"></i></button>
                                            <button class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" type="button" style="background: rgb(6,89,149);"></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="create-exam-1_Coding-1.html">Coding</a>
                                                <a class="dropdown-item" href="create-exam-1_Identification-1.html">Identification</a>
                                                <a class="dropdown-item" href="create-exam-1_Matching.html">Matching</a>
                                                <a class="dropdown-item" href="create-exam-1_Multiple-choice.html">Multiple choice</a>
                                                <a class="dropdown-item" href="create-exam-1_TrueOrFalse.html">True or false</a>
                                            </div>
                                        </div>
                                        <div class="col-md-12" style="width: 265.8px;">
                                            <div class="btn-group" role="group"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="input-group" style="padding: 17px;width: 809.8px;">
                            <span class="input-group-text">Answer</span>
                            <input class="form-control" type="text" style="width: 187.663px;border-color: var(--bs-emphasis-color);">
                            <button class="btn btn-primary" type="button" style="color: var(--bs-btn-color);background: var(--bs-btn-color);border-color: var(--bs-btn-color);margin-left: 19px;"></button>
                        </div>
                        <div class="row" style="border-top-style: solid;border-top-color: var(--bs-secondary-color);">
                            <div class="col d-xxl-flex justify-content-xxl-center" style="height: 42px;width: 766.8px;">
                                <div class="form-check" style="width: 175.8px;margin: 13px;">
                                    <input class="form-check-input" type="checkbox" id="formCheck-5">
                                    <label class="form-check-label d-xxl-flex justify-content-xxl-center" for="formCheck-5" style="width: 103.8px;">Case sensitive</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-11 d-xxl-flex justify-content-xxl-center align-items-xxl-center" style="height: 42px;width: 765.8px;">
                            <small style="font-size: 16px;">Grading:&nbsp;</small>
                            <input type="text" style="height: 17px;width: 39.8px;padding: 2px 2px;">
                            <small style="font-size: 16px;">pts.</small>
                        </div>
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <a class="btn btn-primary border rounded-0" role="button" style="margin-right: 3px;background-color: #35aa47;" href="create-exam-1.html">Add</a>
                            </div>
                        </div>
                        </div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
        </table>
                        </div>
                        </div>
                    </div>
                </div>
                </div>
                <div></div>
            </div>
            <div class="col-md-12" style="width: 926.8px;padding: 35px;">
                <div></div>
            </div>
        </div>
        <div class="tab-pane active" role="tabpanel" id="tab-2">
            <div class="container">
                <div class="row"><div class="col-md-12">
                    <div class="btn-group" role="group"></div>
                </div>
            </div>
            <div class="row bounce animated">
                <div class="col-md-12 col-xl-2 d-xl-flex justify-content-xl-start">
                    <div class="btn-group" role="group"></div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <header></header>
        <div class="card">
            <div class="card-body"></div>
            <div class="card-body">
                <h4 class="card-title">CCIS Qualifying Exam 2024</h4>
                <h6 class="text-muted card-subtitle mb-2">Instruction</h6>
                <button class="btn btn-light" type="button" data-bs-toggle="modal" data-bs-target="#modal-1" style="border-color: rgb(0, 0, 0);border-top-color: rgb(0,0,0);border-right-color: 0,;border-bottom-color: 0,;border-left-color: 0,;">Start Attempt</button>
                <div class="modal fade" role="dialog" tabindex="-1" id="modal-1" style="width: 1370px;height: 711.2px;">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">CCIS Qualifying Exam Preview:</h4>
                                <button class="btn-close" type="button" aria-label="Close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body d-xxl-flex justify-content-xxl-center" style="width: 793.4px;height: 352px;">
                                <div style="height: 311px;border-radius: 10px;box-shadow: 0px 0px 8px var(--bs-body-color);width: 439.538px;">
                                    <h5 style="margin: 17px;color: rgb(0,0,0);padding: 20px;">Question 1:&nbsp;<br>What is the ganto ganiyan</h5>
                                    <div class="table-responsive" style="padding: 8px;">
                                        <table class="table">
                                            <thead>
                                                <tr style="width: 988.6px;">
                                                    <th><input type="checkbox">&nbsp;<span style="font-weight: normal !important;">A. Answer 1</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr style="width: 115.6px;height: 40.8px;">
                                                    <td><input type="checkbox">&nbsp;B. Answer 2</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;C. Answer 3</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;D. Answer 4</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-body d-xxl-flex justify-content-xxl-center" style="width: 797.4px;height: 352px;">
                                <div style="height: 311px;border-radius: 10px;box-shadow: 0px 0px 8px var(--bs-body-color);width: 439.538px;">
                                    <h5 style="margin: 17px;color: rgb(0,0,0);padding: 20px;">Question 1:&nbsp;<br>What is the ganto ganiyan</h5>
                                    <div class="table-responsive" style="padding: 8px;">
                                        <table class="table">
                                            <thead>
                                                <tr style="width: 988.6px;">
                                                    <th><input type="checkbox">&nbsp;<span style="font-weight: normal !important;">A. Answer 1</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr style="width: 115.6px;height: 40.8px;">
                                                    <td><input type="checkbox">&nbsp;B. Answer 2</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;C. Answer 3</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;D. Answer 4</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-body d-xxl-flex justify-content-xxl-center" style="width: 796.4px;height: 352px;">
                                <div style="height: 311px;border-radius: 10px;box-shadow: 0px 0px 8px var(--bs-body-color);width: 439.538px;">
                                    <h5 style="margin: 17px;color: rgb(0,0,0);padding: 20px;">Question 1:&nbsp;<br>What is the ganto ganiyan</h5>
                                    <div class="table-responsive" style="padding: 8px;">
                                        <table class="table">
                                            <thead>
                                                <tr style="width: 988.6px;">
                                                    <th><input type="checkbox">&nbsp;<span style="font-weight: normal !important;">A. Answer 1</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr style="width: 115.6px;height: 40.8px;">
                                                    <td><input type="checkbox">&nbsp;B. Answer 2</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;C. Answer 3</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;D. Answer 4</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-body d-xxl-flex justify-content-xxl-center" style="width: 795.4px;height: 352px;">
                                <div style="height: 311px;border-radius: 10px;box-shadow: 0px 0px 8px var(--bs-body-color);width: 439.538px;">
                                    <h5 style="margin: 17px;color: rgb(0,0,0);padding: 20px;">Question 1:&nbsp;<br>What is the ganto ganiyan</h5>
                                    <div class="table-responsive" style="padding: 8px;">
                                        <table class="table">
                                            <thead>
                                                <tr style="width: 988.6px;">
                                                    <th><input type="checkbox">&nbsp;<span style="font-weight: normal !important;">A. Answer 1</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr style="width: 115.6px;height: 40.8px;">
                                                    <td><input type="checkbox">&nbsp;B. Answer 2</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;C. Answer 3</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;D. Answer 4</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-body d-xxl-flex justify-content-xxl-center" style="width: 797.4px;height: 347px;">
                                <div style="height: 311px;border-radius: 10px;box-shadow: 0px 0px 8px var(--bs-body-color);width: 439.538px;">
                                    <h5 style="margin: 17px;color: rgb(0,0,0);padding: 20px;">Question 1:&nbsp;<br>What is the ganto ganiyan</h5>
                                    <div class="table-responsive" style="padding: 8px;">
                                        <table class="table">
                                            <thead>
                                                <tr style="width: 988.6px;">
                                                    <th><input type="checkbox">&nbsp;<span style="font-weight: normal !important;">A. Answer 1</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr style="width: 115.6px;height: 40.8px;">
                                                    <td><input type="checkbox">&nbsp;B. Answer 2</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;C. Answer 3</td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox">&nbsp;D. Answer 4</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary" type="button">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" role="tabpanel" id="tab-3"><p></p>
        <div class="container">
            <div class="row">
                <div class="col-md-12" style="width: 792.6px;">
                    <div class="input-group">
                        <span class="input-group-text">Randomize order:</span>
                        <select class="form-select" style="width: 617.3px;">
                            <option value="12" selected="">No</option>
                            <option value="">Yes</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">Allow students to view result:</span>
                        <select class="form-select" style="width: 348.888px;">
                            <option value="13">Allow</option>
                            <option value="14">Do not allow</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-12" style="width: 792.6px;">
                    <div class="input-group">
                        <span class="input-group-text">Edit Name</span>
                        <input class="form-control" type="text">
                    </div>
                    <div class="input-group"></div>
                </div>
                <div class="col-md-12 col-lg-8 col-xl-7">
                    <div class="btn-group" role="group">
                        <button class="btn btn-primary border rounded-0" type="button" style="margin-right: 3px;background-color: #35aa47;">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" role="tabpanel" id="tab-4"><p></p>
        <div class="container"><div class="row">
            <div class="col-md-12" style="padding: -1px;">
                <div class="btn-group" role="group"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-xl-12" style="height: 433.4px;"><div>
                <ul class="nav nav-underline" role="tablist">
                    <li class="nav-item" role="presentation"><a class="nav-link active" role="tab" data-bs-toggle="tab" href="#tab-5" style="font-family: 'Open Sans', sans-serif;"><span style="font-weight: normal !important;">View by question</span></a></li>
                    <li class="nav-item" role="presentation" style="font-family: 'Open Sans', sans-serif;"><a class="nav-link" role="tab" data-bs-toggle="tab" href="#tab-6" style="font-family: 'Open Sans', sans-serif;"><span style="font-weight: normal !important;">View by students</span></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" role="tabpanel" id="tab-5">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Question 1: <span style="font-weight: normal !important;">Choose the correct answer.</span></th>
                                        <th><a href="#"><span style="font-weight: normal !important;">See stats</span></a></th>
                                        <th><a href="#"><span style="font-weight: normal !important;">View Responses</span></a></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Question 2: </strong>Choose the correct answer.</td>
                                        <td><a href="#">See stats</a></td><td><a href="#">View Responses</a></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Question 3: </strong>Matching</td>
                                        <td><a href="#">See stats</a></td>
                                        <td><a href="#">View Responses</a></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Question 4: </strong>True or False</td>
                                        <td><a href="#">See stats</a></td>
                                        <td><a href="#">View Responses</a></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Question 5: </strong>Coding</td>
                                        <td><a href="#">See stats</a></td>
                                        <td><a href="#">View Responses</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div><p></p>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="tab-6" style="height: 214.4px;">
                        <ul class="list-group"></ul>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 304.85px;">Name</th>
                                        <th style="width: 241.337px;">Reference Number</th>
                                        <th style="width: 218.413px;">Score</th>
                                        <th>Column 4</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Student A</td>
                                        <td>ABC-123</td>
                                        <td><span style="color: rgb(92, 210, 0);">&nbsp;40/50</span></td>
                                        <td><a href="#">View Details</a></td>
                                    </tr>
                                    <tr>
                                        <td>Student B</td>
                                        <td>ABC-124</td>
                                        <td><span style="color: rgb(92, 210, 0);">45/50</span></td>
                                        <td><a href="#">View Details</a></td>
                                    </tr>
                                    <tr>
                                        <td>Student C</td>
                                        <td>ABC-125</td>
                                        <td><span style="color: rgb(92, 210, 0);">39/50</span></td>
                                        <td><a href="#">View Details</a></td>
                                    </tr>
                                    <tr>
                                        <td>Student D</td>
                                        <td>ABC-126</td>
                                        <td><span style="color: rgb(92, 210, 0);">49/50</span></td>
                                        <td><a href="#">View Details</a></td>
                                    </tr>
                                    <tr>
                                        <td>Student E</td>
                                        <td>ABC-127</td>
                                        <td><span style="color: rgb(92, 210, 0);">41/50</span></td>
                                        <td><a href="#">View Details</a></td>
                                    </tr>
                                    <tr>
                                        <td>Student F</td>
                                        <td>ABC-128</td>
                                        <td><span style="color: rgb(92, 210, 0);">42/50</span></td>
                                        <td><a href="#">View Details</a></td>
                                    </tr>
                                    <tr>
                                        <td>Student G</td>
                                        <td>ABC-129</td>
                                        <td><span style="color: rgb(92, 210, 0);">37/50</span></td>
                                        <td><a href="#">View Details</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div><p></p>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="tab-9">
                        <ul class="list-group"></ul>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Column 1</th>
                                        <th>Column 2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Cell 1</td>
                                        <td>Cell 2</td>
                                    </tr>
                                    <tr>
                                        <td>Cell 3</td>
                                        <td>Cell 4</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p>Content for tab 6.</p>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="tab-8">
                        <ul class="list-group"></ul>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Column 1</th>
                                        <th>Column 2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Cell 1</td>
                                        <td>Cell 2</td>
                                    </tr>
                                    <tr>
                                        <td>Cell 3</td>
                                        <td>Cell 4</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p>Content for tab 6.</p>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="tab-7">
                        <p>Content for tab 7.</p>
                    </div>
                </div>
            </div>
            <div class="toast-container"></div>
        </div></div></div></div></div></div></div></div>
        <a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script src="assets/js/script.min.js"></script>
</body>
</html>
