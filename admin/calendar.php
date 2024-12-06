<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header('Location: loginAdmin.php');
    exit();
}

// Fetch exam schedules from database with more details
$query = "SELECT e.exam_id, e.exam_name, e.schedule_date, e.student_type, e.duration, 
          e.description, COUNT(ea.student_id) as registered_students,
          (SELECT COUNT(*) FROM exam_results er WHERE er.exam_id = e.exam_id) as completed_exams
          FROM exams e
          LEFT JOIN exam_assignments ea ON e.exam_id = ea.exam_id
          GROUP BY e.exam_id";
$result = $conn->query($query);

$events = array();
while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['schedule_date']);
    $end = clone $start;
    $end->add(new DateInterval('PT' . $row['duration'] . 'M'));
    
    $events[] = array(
        'id' => $row['exam_id'],
        'title' => $row['exam_name'],
        'start' => $start->format('Y-m-d\TH:i:s'),
        'end' => $end->format('Y-m-d\TH:i:s'),
        'className' => $row['student_type'] == 'tech' ? 'event-tech' : 'event-non-tech',
        'description' => $row['description'],
        'registeredStudents' => $row['registered_students'],
        'completedExams' => $row['completed_exams'],
        'duration' => $row['duration']
    );
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Calendar - Exam Schedules</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet'>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.min.css">
    
    <style>
        /* Basic Layout */
        #wrapper {
            display: flex;
        }

        .sidebar {
            width: 224px;
            position: fixed;
            height: 100vh;
            background: #005684;
            z-index: 1000;
        }

        #content-wrapper {
            margin-left: 224px;
            width: calc(100% - 224px);
        }

        #content {
            padding: 1.5rem;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            padding: 1.25rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Calendar Container */
        .calendar-container {
            background: white;
            padding: 1.25rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 650px; /* Fixed height */
        }

        #calendar {
            height: 100%;
        }

        /* Calendar Day Cells */
        .fc .fc-daygrid-day {
            min-height: 100px;
        }

        /* Legend */
        .calendar-legend {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Keep your existing event styles */
        .event-tech {
            background: linear-gradient(45deg, #4e73df, #3867d6) !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 5px 10px !important;
        }

        .event-non-tech {
            background: linear-gradient(45deg, #1cc88a, #16a085) !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 5px 10px !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            #content-wrapper {
                margin-left: 0;
                width: 100%;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .calendar-container {
                height: 500px;
            }
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand bg-white shadow mb-4 topbar static-top navbar-light">
                    <div class="container-fluid">
                        <button class="btn btn-link d-md-none rounded-circle me-3" id="sidebarToggleTop">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </nav>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Exam Calendar</h1>
                    
                    <!-- Calendar Stats -->
                    <div class="stats-container">
                        <div class="stat-card">
                            <h5>Total Exams</h5>
                            <div class="stat-number"><?php echo count($events); ?></div>
                        </div>
                        <div class="stat-card">
                            <h5>Upcoming Exams</h5>
                            <div class="stat-number" id="upcomingExams">0</div>
                        </div>
                        <div class="stat-card">
                            <h5>Today's Exams</h5>
                            <div class="stat-number" id="todayExams">0</div>
                        </div>
                    </div>

                    <!-- Calendar Legend -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="calendar-legend">
                            <div class="legend-item">
                                <div class="legend-color event-tech"></div>
                                <span>Technical Exams</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color event-non-tech"></div>
                                <span>Non-Technical Exams</span>
                            </div>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                            <i class="fas fa-plus"></i> Create Schedule
                        </button>
                    </div>

                    <!-- Today's Exams Indicator -->
                    <div class="today-indicator" id="todayExamsIndicator" style="display: none;">
                        <i class="fas fa-calendar-day"></i> Today's Exams: <span id="todayExamsList"></span>
                    </div>
                    
                    <div class="calendar-container">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
            
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <!-- Enhanced Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Exam Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="eventDetails"></div>
                    <div class="event-actions">
                        <button type="button" class="btn btn-primary" id="viewExamDetails">
                            <i class="fas fa-eye"></i> View Full Details
                        </button>
                        <button type="button" class="btn btn-success" id="viewRegisteredStudents">
                            <i class="fas fa-users"></i> View Registered Students
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Schedule Modal -->
    <div class="modal fade" id="createScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Exam Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="scheduleForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Exam Name</label>
                                <input type="text" class="form-control" name="exam_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Student Type</label>
                                <select class="form-control" name="student_type" required>
                                    <option value="tech">Technical</option>
                                    <option value="non-tech">Non-Technical</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="exam_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time</label>
                                <input type="time" class="form-control" name="exam_time" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" name="duration" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Student Year</label>
                                <select class="form-control" name="student_year" required>
                                    <?php 
                                    $current_year = date('Y');
                                    for($i = $current_year; $i <= $current_year + 4; $i++) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveSchedule()">Save Schedule</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="assets/js/script.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                events: <?php echo json_encode($events); ?>,
                eventClick: function(info) {
                    showEventDetails(info.event);
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: true
                },
                eventDidMount: function(info) {
                    // Add tooltips
                    $(info.el).tooltip({
                        title: info.event.title,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                },
                datesSet: function() {
                    updateCalendarStats();
                }
            });
            calendar.render();
            
            // Initial stats update
            updateCalendarStats();
            updateTodayExams();
        });

        function updateCalendarStats() {
            const events = <?php echo json_encode($events); ?>;
            const now = new Date();
            let upcomingCount = 0;
            let todayCount = 0;

            events.forEach(event => {
                const eventDate = new Date(event.start);
                if (eventDate > now) {
                    upcomingCount++;
                }
                if (eventDate.toDateString() === now.toDateString()) {
                    todayCount++;
                }
            });

            document.getElementById('upcomingExams').textContent = upcomingCount;
            document.getElementById('todayExams').textContent = todayCount;
        }

        function updateTodayExams() {
            const events = <?php echo json_encode($events); ?>;
            const now = new Date();
            const todayEvents = events.filter(event => 
                new Date(event.start).toDateString() === now.toDateString()
            );

            if (todayEvents.length > 0) {
                const eventsList = todayEvents.map(event => event.title).join(', ');
                document.getElementById('todayExamsList').textContent = eventsList;
                document.getElementById('todayExamsIndicator').style.display = 'block';
            }
        }

        function showEventDetails(event) {
            const start = new Date(event.start);
            const end = new Date(event.end);
            
            const details = `
                <h5>${event.title}</h5>
                <div class="event-details">
                    <p><strong>Date:</strong> ${start.toLocaleDateString()}</p>
                    <p><strong>Time:</strong> ${start.toLocaleTimeString()} - ${end.toLocaleTimeString()}</p>
                    <p><strong>Duration:</strong> ${event.extendedProps.duration} minutes</p>
                    <p><strong>Type:</strong> ${event.className.includes('tech') ? 'Technical' : 'Non-Technical'}</p>
                    <p><strong>Description:</strong> ${event.extendedProps.description || 'No description available'}</p>
                    <p><strong>Registered Students:</strong> ${event.extendedProps.registeredStudents}</p>
                    <p><strong>Completed Exams:</strong> ${event.extendedProps.completedExams}</p>
                </div>
            `;
            
            document.getElementById('eventDetails').innerHTML = details;
            
            // Set up action buttons
            document.getElementById('viewExamDetails').onclick = function() {
                window.location.href = `view_exam.php?exam_id=${event.id}`;
            };
            
            document.getElementById('viewRegisteredStudents').onclick = function() {
                window.location.href = `view_registered_students.php?exam_id=${event.id}`;
            };
            
            new bootstrap.Modal(document.getElementById('eventModal')).show();
        }

        function saveSchedule() {
            const form = document.getElementById('scheduleForm');
            const formData = new FormData(form);
            
            // Add datetime formatting
            const date = formData.get('exam_date');
            const time = formData.get('exam_time');
            formData.set('schedule_date', `${date} ${time}`);
            
            fetch('save_exam_schedule.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add the new event to the calendar
                    calendar.addEvent({
                        id: data.exam_id,
                        title: formData.get('exam_name'),
                        start: formData.get('schedule_date'),
                        end: new Date(new Date(formData.get('schedule_date')).getTime() + formData.get('duration') * 60000),
                        className: formData.get('student_type') === 'tech' ? 'event-tech' : 'event-non-tech'
                    });
                    
                    // Close modal and show success message
                    $('#createScheduleModal').modal('hide');
                    alert('Schedule created successfully!');
                    form.reset();
                } else {
                    alert('Error creating schedule: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating schedule. Please try again.');
            });
        }
    </script>
</body>
</html> 