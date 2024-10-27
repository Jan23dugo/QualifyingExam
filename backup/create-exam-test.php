
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Untitled Form</title>
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900&display=swap">
  <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
  <link rel="stylesheet" href="assets/css/styles.min.css">
  <style>
  /* Form container */
    .form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    /* Button styles */
    .add-buttons {
      margin: 20px 0; /* Space above and below the button */
      display: flex;
      justify-content: space-between; /* Space buttons evenly */
    }

    /* Ensure body does not allow horizontal scroll */
    body {
      overflow-x: hidden; /* Prevent body from scrolling horizontally */
    }

    .add-buttons button {
      background: #6200ea; /* Custom button color */
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
    }

    .add-buttons button:hover {
      background: #3700b3; /* Darker shade on hover */
    }

    /* Title styling similar to Google Forms */
    .title-block {
      font-size: 24px;
      font-weight: bold;
      padding-bottom: 5px;
      border-bottom: 2px solid #6200ea; /* Purple underline like Google Forms */
    }

    .description-block {
      font-size: 14px;
      color: #757575;
      margin-top: 10px;
    }
/* Sidebar */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 250px; /* Ensure this width matches the sidebar's intended width */
  height: 100%;
  z-index: 1000; 
  background-color: #005684;
  overflow-x: hidden;
  padding: 0; /* Remove any padding */
  margin: 0; /* Remove any margin */
}

/* Topbar */
.topbar {
  position: fixed;
  top: 0;
  left: 225px; /* Match this to the sidebar width */
  right: 0;
  height: 60px;
  z-index: 1001; /* Ensure the topbar is above the sidebar */
  background-color: white;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
/* Content Wrapper */
#content-wrapper {
  margin-left: 250px; /* This should match the sidebar width exactly */
  margin-top: 60px;
  padding: 20px;
  max-width: calc(100% - 250px); /* Ensure content takes full width minus sidebar */
  overflow-x: hidden;
}

/* Content */
#content {
  padding: 20px;
  margin-top: 60px;
  max-width: 100%; 
  overflow-x: hidden;
  margin-left: 0; /* Make sure there's no extra margin on the content */
}


  </style>
</head>    


<body id="page-top">

<?php include 'sidebar-topbar.php'; ?>
 <!-- Wrapper for the main content -->
<div id="wrapper" class="d-flex flex-column" style="min-height: 100vh;">

<!-- Content Wrapper for form and topbar -->
<div id="content-wrapper" class="d-flex flex-column" style="margin-left: 250px;"> <!-- Adjust for sidebar width -->
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
  <!-- Main content (form container) -->
  <div id="content" style="padding: 20px; margin-top: 60px;"> <!-- Add padding for topbar -->
  
    <!-- Form Content -->
    <div class="container form-container">
      <!-- Title block -->
      <div class="title-block">
        <input type="text" class="form-control form-control-lg mb-2" placeholder="Untitled form" required>
      </div>
      
      <!-- Description block -->
      <div class="description-block">
        <textarea class="form-control form-control-description" rows="1" placeholder="Form description"></textarea>
      </div>

      <!-- Button for adding elements -->
      <div class="add-buttons">
        <button id="addQuestionBtn"><i class="fas fa-plus-circle"></i> Add Question</button>
        <button id="addTitleBtn"><i class="fas fa-file-alt"></i> Add Title</button>
        <button id="addSectionBtn"><i class="fas fa-bars"></i> Add Section</button>
      </div>

      <!-- Question Container -->
      <div id="question-container" class="container mb-4">
        <!-- Default question block -->
        <div class="question-block card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <input type="text" class="form-control" placeholder="Question" required>
              <select class="form-select ms-3" style="width: auto;">
                <option value="Multiple Choice" selected>Multiple Choice</option>
                <option value="True/False">True/False</option>
                <option value="Identification">Identification</option>
                <option value="Matching">Matching</option>
                <option value="Coding">Coding</option>
              </select>
            </div>
            <div class="options-container">
              <div class="mb-3">
                <label class="form-label">Options</label>
                <div class="input-group mb-2">
                  <input type="text" class="form-control" placeholder="Option 1" required>
                  <button class="btn btn-outline-secondary remove-option-btn">Remove</button>
                </div>
                <div class="input-group mb-2">
                  <input type="text" class="form-control" placeholder="Option 2" required>
                  <button class="btn btn-outline-secondary remove-option-btn">Remove</button>
                </div>
                <button class="btn btn-secondary add-option-btn">Add Option</button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Points</label>
              <input type="number" class="form-control" required>
            </div>
            <button class="btn btn-danger remove-question-btn">Remove</button>
          </div>
        </div>
      </div>
    </div>
              <!-- Include Footer (footer.php) -->
              <?php include 'footer.php'; ?>
  </div>


    </div> 

  </div>

  <!-- JS Scripts -->
  <script src="assets/bootstrap/js/bootstrap.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const questionContainer = document.getElementById('question-container');

      // Function to add a question block
      function addQuestion() {
        const questionBlock = document.createElement('div');
        questionBlock.classList.add('question-block', 'card', 'mb-3');
        questionBlock.innerHTML = `
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <input type="text" class="form-control" placeholder="Question" required>
              <select class="form-select ms-3" style="width: auto;">
                <option value="Multiple Choice" selected>Multiple Choice</option>
                <option value="True/False">True/False</option>
                <option value="Identification">Identification</option>
                <option value="Matching">Matching</option>
                <option value="Coding">Coding</option>
              </select>
            </div>
            <div class="options-container">
              <div class="mb-3">
                <label class="form-label">Options</label>
                <div class="input-group mb-2">
                  <input type="text" class="form-control" placeholder="Option 1" required>
                  <button class="btn btn-outline-secondary remove-option-btn">Remove</button>
                </div>
                <div class="input-group mb-2">
                  <input type="text" class="form-control" placeholder="Option 2" required>
                  <button class="btn btn-outline-secondary remove-option-btn">Remove</button>
                </div>
                <button class="btn btn-secondary add-option-btn">Add Option</button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Points</label>
              <input type="number" class="form-control" required>
            </div>
            <button class="btn btn-danger remove-question-btn">Remove</button>
          </div>
        `;

        questionContainer.appendChild(questionBlock);

        // Attach event listener to remove button
        questionBlock.querySelector('.remove-question-btn').addEventListener('click', function () {
          questionBlock.remove();
        });
      }

      // Event listeners for buttons
      document.getElementById('addQuestionBtn').addEventListener('click', addQuestion);
      document.getElementById('addTitleBtn').addEventListener('click', function () {
        addTitleDescription(questionContainer);
      });
      document.getElementById('addSectionBtn').addEventListener('click', function () {
        addSection(questionContainer);
      });

      // Function to add title and description block
      function addTitleDescription(container) {
        const titleBlock = document.createElement('div');
        titleBlock.classList.add('mb-4');
        titleBlock.innerHTML = `
          <input type="text" class="form-control mb-2" placeholder="Title" required>
          <textarea class="form-control" rows="2" placeholder="Description"></textarea>
        `;
        container.appendChild(titleBlock);
      }

      // Function to add section block
      function addSection(container) {
        const sectionBlock = document.createElement('div');
        sectionBlock.classList.add('question-block', 'card', 'mb-3');
        sectionBlock.innerHTML = `
          <div class="card-body">
            <h5 class="card-title">New Section</h5>
          </div>
        `;
        container.appendChild(sectionBlock);
      }
    });
  </script>
</body>
</html>
