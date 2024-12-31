<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header('Location: loginAdmin.php');
    exit();
}

// Add this after your require and session statements
function safeEcho($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Update query to use exam_date and exam_time
$query = "SELECT e.exam_id, e.exam_name, e.exam_date, e.exam_time, e.student_type, e.duration, 
          e.description, e.status,
          CASE 
              WHEN e.status = 'unscheduled' THEN 'unscheduled'
              WHEN NOW() < CONCAT(e.exam_date, ' ', e.exam_time) THEN 'upcoming'
              WHEN NOW() > DATE_ADD(CONCAT(e.exam_date, ' ', e.exam_time), INTERVAL e.duration MINUTE) THEN 'completed'
              ELSE 'in_progress'
          END as current_status,
          COUNT(ea.student_id) as registered_students,
          (SELECT COUNT(*) FROM exam_results er WHERE er.exam_id = e.exam_id) as completed_exams
          FROM exams e
          LEFT JOIN exam_assignments ea ON e.exam_id = ea.exam_id
          GROUP BY e.exam_id";
$result = $conn->query($query);

$events = array();
$unscheduled_exams = array();

    while ($row = $result->fetch_assoc()) {
    if ($row['exam_date'] && $row['exam_time']) {
        // Get status color
        $statusColor = '';
        switch($row['current_status']) {
            case 'upcoming':
                $statusColor = '#3498db'; // Blue
                break;
            case 'in_progress':
                $statusColor = '#f1c40f'; // Yellow
                break;
            case 'completed':
                $statusColor = '#2ecc71'; // Green
                break;
            default:
                $statusColor = '#95a5a6'; // Gray
        }

        // Combine date and time for calendar
        $start_datetime = $row['exam_date'] . ' ' . $row['exam_time'];
        $start = new DateTime($start_datetime);
        $end = clone $start;
        $end->add(new DateInterval('PT' . $row['duration'] . 'M'));
        
        $events[] = array(
            'id' => $row['exam_id'],
            'title' => $row['exam_name'],
            'start' => $start->format('Y-m-d\TH:i:s'),
            'end' => $end->format('Y-m-d\TH:i:s'),
            'backgroundColor' => $statusColor,
            'borderColor' => $statusColor,
            'description' => $row['description'],
            'registeredStudents' => $row['registered_students'],
            'completedExams' => $row['completed_exams'],
            'duration' => $row['duration'],
            'status' => $row['current_status'],
            'exam_date' => $row['exam_date'],
            'exam_time' => $row['exam_time']
        );
    } else {
        $unscheduled_exams[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Calendar - Exam Schedules</title>

    <!-- External Stylesheets -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans&amp;display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet'>
    
    <style>
        /* Calendar specific styles */
        .calendar-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            height: calc(100vh - 350px);
            min-height: 600px;
            margin-top: 1rem;
        }

        #calendar {
            height: 100%;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .event-tech {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
            border: none !important;
            border-radius: 8px !important;
            color: white !important;
        }

        .event-non-tech {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
            border: none !important;
            border-radius: 8px !important;
            color: white !important;
        }

        .fc .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .fc .fc-button-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .fc .fc-button-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
    </style>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark mb-0">Exam Calendar</h3>
                        <div>
                            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#unscheduledModal">
                                <i class="fas fa-list"></i> Unscheduled Exams
                            </button>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
                                <i class="fas fa-plus"></i> Create Event
                            </button>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="stats-container">
                        <div class="stat-card">
                            <h5>Total Exams</h5>
                            <div class="stat-number"><?php echo count($events) + count($unscheduled_exams); ?></div>
                        </div>
                        <div class="stat-card">
                            <h5>Scheduled Exams</h5>
                            <div class="stat-number"><?php echo count($events); ?></div>
                        </div>
                        <div class="stat-card">
                            <h5>Unscheduled Exams</h5>
                            <div class="stat-number"><?php echo count($unscheduled_exams); ?></div>
                        </div>
                    </div>

                    <!-- Calendar Container -->
                    <div class="calendar-container">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
            <!-- Include Footer -->
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <!-- Unscheduled Exams Modal -->
    <div class="modal fade" id="unscheduledModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Unscheduled Exams</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Type</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unscheduled_exams as $exam): ?>
                                <tr>
                                    <td><?php echo safeEcho($exam['exam_name']); ?></td>
                                    <td><?php echo safeEcho($exam['student_type']); ?></td>
                                    <td><?php echo safeEcho($exam['duration']); ?> mins</td>
                                    <td><span class="badge bg-warning">Unscheduled</span></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" 
                                                onclick="scheduleExam(<?php echo (int)$exam['exam_id']; ?>, '<?php echo addslashes(safeEcho($exam['exam_name'])); ?>')">
                                            <i class="fas fa-calendar"></i> Schedule
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Schedule Exam Modal -->
    <div class="modal fade" id="scheduleExamModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="scheduleExamForm">
                        <input type="hidden" id="examIdToSchedule" name="exam_id">
                        <div class="mb-3">
                            <label class="form-label">Exam Name:</label>
                            <input type="text" class="form-control" id="examNameToSchedule" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="exam_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time:</label>
                            <input type="time" class="form-control" name="exam_time" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveExamSchedule()">Save Schedule</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this new modal for creating events -->
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Calendar Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createEventForm">
                        <div class="mb-3">
                            <label class="form-label">Event Title:</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description:</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="event_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time:</label>
                            <input type="time" class="form-control" name="event_time" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration (minutes):</label>
                            <input type="number" class="form-control" name="duration" value="60" min="15" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Event Type:</label>
                            <select class="form-control" name="event_type" id="eventType" onchange="toggleSpecificEventType()">
                                <option value="meeting">Meeting</option>
                                <option value="reminder">Reminder</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3" id="specificEventTypeContainer" style="display: none;">
                            <label class="form-label">Specific Event Type:</label>
                            <input type="text" class="form-control" name="specific_event_type" id="specificEventType">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveEvent()">Save Event</button>
                </div>
            </div>
        </div>
    </div>

    <a class="border rounded d-inline scroll-to-top" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Scripts -->
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="assets/js/script.min.js"></script>

    <!-- Calendar Initialization Script -->
    <script>
        function updateCalendarStats() {
            // Get all events from the calendar
            const calendar = document.querySelector('#calendar');
            const fc = calendar.querySelector('.fc');
            
            if (!fc) return; // Exit if calendar isn't fully initialized
            
            // Count events by status
            const events = calendar.querySelectorAll('.fc-event');
            let stats = {
                total: events.length,
                upcoming: 0,
                inProgress: 0,
                completed: 0
            };

            events.forEach(event => {
                const backgroundColor = event.style.backgroundColor;
                // Check status based on the color we defined earlier
                if (backgroundColor.includes('3498db')) stats.upcoming++;
                else if (backgroundColor.includes('f1c40f')) stats.inProgress++;
                else if (backgroundColor.includes('2ecc71')) stats.completed++;
            });

            // Update stats in the UI
            const statNumbers = document.querySelectorAll('.stat-number');
            if (statNumbers.length >= 3) {
                statNumbers[0].textContent = stats.total;
                // The second stat (Scheduled Exams) is already handled by PHP
                // The third stat (Unscheduled Exams) is already handled by PHP
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var inlineEvents = <?php echo json_encode($events); ?>; // PHP inline events

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    // Fetch external events
                    fetch('calendar_events.php')
                        .then(response => response.json())
                        .then(function(externalEvents) {
                            // Combine inline and external events
                            var allEvents = inlineEvents.concat(externalEvents);
                            successCallback(allEvents);
                        })
                        .catch(function(error) {
                            console.error('Error fetching events:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    showEventDetails(info.event);
                },
                editable: true, // Enable drag and drop
                eventDrop: function(info) {
                    // Format the date and time properly
                    const newDate = info.event.start.toISOString().split('T')[0];
                    const newTime = info.event.start.toLocaleTimeString('en-US', { 
                        hour12: false, 
                        hour: '2-digit', 
                        minute: '2-digit'
                    });

                    // Show loading indicator
                    Swal.fire({
                        title: 'Updating schedule...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send update request
                    $.ajax({
                        url: 'handlers/update_exam.php',
                        method: 'POST',
                        data: {
                            exam_id: info.event.id,
                            exam_date: newDate,
                            exam_time: newTime
                        },
                        success: function(response) {
                            try {
                                const result = typeof response === 'object' ? response : JSON.parse(response);
                                if (result.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Schedule Updated',
                                        text: 'The exam schedule has been updated successfully',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    throw new Error(result.message || 'Failed to update schedule');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: error.message,
                                    confirmButtonColor: '#d33'
                                });
                                info.revert(); // Revert the calendar change
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Ajax error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to update schedule. Please try again.',
                                confirmButtonColor: '#d33'
                            });
                            info.revert(); // Revert the calendar change
                        }
                    });
                }
            });

            calendar.render();
            setTimeout(updateCalendarStats, 100);

            // Also update stats when events change
            calendar.on('eventAdd', updateCalendarStats);
            calendar.on('eventRemove', updateCalendarStats);
            calendar.on('eventChange', updateCalendarStats);
        });

        function showEventDetails(event) {
            const statusColors = {
                'upcoming': 'info',
                'in_progress': 'warning',
                'completed': 'success',
                'unscheduled': 'secondary'
            };

            const modalHtml = `
                <div class="modal fade" id="eventDetailsModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${event.title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Description:</strong> ${event.extendedProps.description || 'No description'}</p>
                                <p><strong>Duration:</strong> ${event.extendedProps.duration} minutes</p>
                                <p><strong>Date:</strong> ${event.extendedProps.exam_date}</p>
                                <p><strong>Time:</strong> ${event.extendedProps.exam_time}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-${statusColors[event.extendedProps.status]}">
                                        ${event.extendedProps.status.charAt(0).toUpperCase() + event.extendedProps.status.slice(1)}
                                    </span>
                                </p>
                                <p><strong>Registered Students:</strong> ${event.extendedProps.registeredStudents}</p>
                                <p><strong>Completed Exams:</strong> ${event.extendedProps.completedExams}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                ${event.extendedProps.status === 'completed' ? 
                                    `<a href="view_exam_results.php?exam_id=${event.id}" class="btn btn-primary">View Results</a>` :
                                    `<button type="button" class="btn btn-primary" onclick="editExam(${event.id})">Edit</button>`
                                }
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            const existingModal = document.getElementById('eventDetailsModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add new modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
            modal.show();
        }

        function updateExamSchedule(examId, date, time) {
            fetch('handlers/update_exam.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `exam_id=${examId}&exam_date=${date}&exam_time=${time}&enabled=true`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error updating schedule: ' + data.message);
                    location.reload(); // Reload to reset the calendar
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating schedule');
                location.reload(); // Reload to reset the calendar
            });
        }

        function editExam(examId) {
            // Close the details modal
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal'));
            detailsModal.hide();
            
            // Redirect to the exam edit page
            window.location.href = `create-exam.php?edit=${examId}`;
        }

        function scheduleExam(examId, examName) {
            document.getElementById('examIdToSchedule').value = examId;
            document.getElementById('examNameToSchedule').value = examName;
            
            // Hide the unscheduled exams modal and show the schedule modal
            const unscheduledModal = bootstrap.Modal.getInstance(document.getElementById('unscheduledModal'));
            unscheduledModal.hide();
            
            const scheduleModal = new bootstrap.Modal(document.getElementById('scheduleExamModal'));
            scheduleModal.show();
        }

        function saveExamSchedule() {
            const form = document.getElementById('scheduleExamForm');
            const formData = new FormData(form);
            
            fetch('handlers/update_exam.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleExamModal'));
                    modal.hide();
                    alert('Exam scheduled successfully!');
                    location.reload();
                } else {
                    alert('Error scheduling exam: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error scheduling exam. Please try again.');
            });
        }

        function toggleSpecificEventType() {
            const eventType = document.getElementById('eventType').value;
            const specificEventTypeContainer = document.getElementById('specificEventTypeContainer');
            const specificEventTypeInput = document.getElementById('specificEventType');
            
            if (eventType === 'other') {
                specificEventTypeContainer.style.display = 'block';
                specificEventTypeInput.required = true; // Set as required when visible
            } else {
                specificEventTypeContainer.style.display = 'none';
                specificEventTypeInput.value = ''; // Clear value if hidden
                specificEventTypeInput.required = false; // Remove required attribute
            }
        }

        function saveEvent() {
            const form = document.getElementById('createEventForm');
            const formData = new FormData(form);

            // Check if "Other" is selected and replace event_type with the specific event type
            const eventType = formData.get('event_type');
            const specificEventType = formData.get('specific_event_type');
            
            if (eventType === 'other' && specificEventType) {
                formData.set('event_type', specificEventType); // Replace "other" with the specific event type
                formData.delete('specific_event_type'); // Remove the specific_event_type field
            }
            
            fetch('handlers/save_event.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createEventModal'));
                    modal.hide();
                    alert('Event created successfully!');
                    location.reload();
                } else {
                    alert('Error creating event: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating event');
            });
        }
    </script>
</body>
</html>
