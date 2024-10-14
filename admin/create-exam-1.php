<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Brand</title>
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,700&display=swap">
  <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
  <link rel="stylesheet" href="assets/css/styles.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css">
  <link rel="stylesheet" href="https://cdn.quilljs.com/1.0.0/quill.snow.css">
</head>

<body id="page-top">
  <!-- Wrapper -->
  <div id="wrapper">
    
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
        
    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content" style="padding: 5px; margin: -2px;">
        
      <?php include 'topbar.php'; ?>

        <!-- Tab Content -->
        <div class="container">
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <a class="nav-link active" data-bs-toggle="tab" href="#tab-1">Question</a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link" data-bs-toggle="tab" href="#tab-2">Preview</a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link" data-bs-toggle="tab" href="#tab-3">Settings</a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link" data-bs-toggle="tab" href="#tab-4">Result</a>
            </li>
          </ul>

          <!-- Tab Panes -->
          <div class="tab-content">
            <!-- Question Tab -->
            <div class="tab-pane active" id="tab-1">
              <div class="col-md-12">
                <div class="input-group" style="margin: 34px; width: 283.6px;">
                  <div class="btn-group open">
                    <button class="btn btn-primary rounded-0 rounded-start" type="button">+ Add question</button>
                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-split rounded-0 rounded-end" data-bs-toggle="dropdown"></button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="create-exam-1_Coding-1.html">Coding</a>
                      <a class="dropdown-item" href="create-exam-1_Identification-1.html">Identification</a>
                      <a class="dropdown-item" href="create-exam-1_Matching.html">Matching</a>
                      <a class="dropdown-item" href="create-exam-1_Multiple-choice.html">Multiple choice</a>
                      <a class="dropdown-item" href="create-exam-1_TrueOrFalse.html">True or false</a>
                    </div>
                  </div>
                </div>
                <!-- Questions Table -->
                <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Question</th>
                        <th>Edit</th>
                        <th>Remove</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>1</td>
                        <td>Question 1<br><small>Multiple choices</small></td>
                        <td><button class="btn btn-primary" type="button">Edit</button></td>
                        <td><button class="btn btn-primary" type="button">Remove</button></td>
                      </tr>
                      <!-- Additional questions go here -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <!-- Preview Tab (empty content) -->
            <div class="tab-pane" id="tab-2"></div>
            <!-- Settings Tab (empty content) -->
            <div class="tab-pane" id="tab-3"></div>
            <!-- Result Tab (empty content) -->
            <div class="tab-pane" id="tab-4"></div>
            
          </div>
        </div>
      </div>
      <?php include 'footer.php'; ?>
    </div> 
  </div>
  
  <!-- JS Scripts -->
  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
