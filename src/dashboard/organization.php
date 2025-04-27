<?php
// Initialize the session
session_start();

// Check if the organization is logged in, if not redirect to login page
if (!isset($_SESSION["org_logged_in"]) || $_SESSION["org_logged_in"] !== true) {
    header("location: ../org_login.php");
    exit;
}

// Include database connection
require_once "../lib/functions.php";

// Get organization info
$org_id = $_SESSION['org_id'];
$org_name = $_SESSION['org_name'];
$org_email = $_SESSION['org_email'];

// Get all events created by the organization
$events = [];
$conn = getConnection();
$sql = "SELECT * FROM events WHERE organization_id = ? ORDER BY date DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $org_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    $stmt->close();
}

// Handle creating new event
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_event"])) {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $location = trim($_POST["location"]);
    $date = trim($_POST["date"]);
    $time = trim($_POST["time"]);
    $required = trim($_POST["required"]);
    $status = "Open";
    
    // Simple validation
    if (empty($title) || empty($description) || empty($date)) {
        $event_error = "Please fill all required fields";
    } else {
        $sql = "INSERT INTO events (organization_id, title, description, location, date, time, required, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isssssss", $org_id, $title, $description, $location, $date, $time, $required, $status);
            if ($stmt->execute()) {
                header("location: organization.php");
                exit;
            } else {
                $event_error = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

// Handle event deletion
if (isset($_GET["delete_event"]) && !empty($_GET["delete_event"])) {
    $event_id = $_GET["delete_event"];
    
    $sql = "DELETE FROM events WHERE id = ? AND organization_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $event_id, $org_id);
        if ($stmt->execute()) {
            header("location: organization.php");
            exit;
        }
        $stmt->close();
    }
}

// Get registered users for a specific event
$registered_users = [];
if (isset($_GET["event_id"]) && !empty($_GET["event_id"])) {
    $event_id = $_GET["event_id"];
    
    $sql = "SELECT u.id, u.fullname, u.email, er.registered_at 
            FROM users u 
            JOIN event_registrations er ON u.id = er.user_id 
            WHERE er.event_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $registered_users[] = $row;
            }
        }
        $stmt->close();
    }
    
    // Get event details
    $event_details = null;
    $sql = "SELECT * FROM events WHERE id = ? AND organization_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $event_id, $org_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $event_details = $result->fetch_assoc();
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Dashboard - Vol.</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container dashboard-container">
        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <div class="user-profile">
                <div class="profile-header">
                    <div class="logo">Vol<span>.</span></div>
                    <h3><?php echo htmlspecialchars($org_name); ?></h3>
                    <p><?php echo htmlspecialchars($org_email); ?></p>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="#" class="menu-item active">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="#" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    All created events
                    <span class="badge-count"><?php echo count($events); ?></span>
                </a>
                <?php foreach($events as $index => $event): ?>
                    <?php if($index < 2): ?>
                    <div class="event-item" style="padding-left: 40px; font-size: 14px;">
                        <div class="event-details">
                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                            <div class="event-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($event['date'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
            
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>Organization Dashboard</h2>
                <div class="user-profile-mini">
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
            
            <?php if(isset($event_details)): ?>
                <!-- Show specific event details and registered users -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3>Event Details: <?php echo htmlspecialchars($event_details['title']); ?></h3>
                        <a href="organization.php" class="view-all">Back to Dashboard <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="event-details-card" style="background-color: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <h3><?php echo htmlspecialchars($event_details['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($event_details['description'])); ?></p>
                        <div class="event-meta" style="margin-top: 15px;">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event_details['location']); ?></span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($event_details['date'])); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($event_details['time'])); ?></span>
                            <span><i class="fas fa-users"></i> Required: <?php echo htmlspecialchars($event_details['required']); ?></span>
                            <span><i class="fas fa-info-circle"></i> Status: <?php echo htmlspecialchars($event_details['status']); ?></span>
                        </div>
                    </div>
                    
                    <div class="section-header">
                        <h3>Registered Users</h3>
                        <span>Total: <?php echo count($registered_users); ?> / <?php echo htmlspecialchars($event_details['required']); ?></span>
                    </div>
                    
                    <?php if(count($registered_users) > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                            <thead>
                                <tr style="background-color: #f0f0f0;">
                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Name</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Email</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Registered On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($registered_users as $user): ?>
                                    <tr>
                                        <td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($user['fullname']); ?></td>
                                        <td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo date('M d, Y h:i A', strtotime($user['registered_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No users have registered for this event yet.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Dashboard Home -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3>Dashboard Overview</h3>
                    </div>
                    
                    <div class="status-cards-grid">
                        <div class="status-card">
                            <div class="status-card-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="status-card-info">
                                <div class="status-value"><?php echo count($events); ?></div>
                                <div class="status-label">Total Events</div>
                            </div>
                        </div>
                        
                        <?php
                        // Count active events
                        $active_events = 0;
                        $upcoming_events = 0;
                        $registered_users_count = 0;
                        
                        foreach($events as $event) {
                            if($event['status'] == 'Open') {
                                $active_events++;
                            }
                            
                            if(strtotime($event['date']) > time()) {
                                $upcoming_events++;
                            }
                            
                            // Count registered users for all events
                            $conn = getConnection();
                            $sql = "SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?";
                            if ($stmt = $conn->prepare($sql)) {
                                $stmt->bind_param("i", $event['id']);
                                if ($stmt->execute()) {
                                    $result = $stmt->get_result();
                                    $row = $result->fetch_assoc();
                                    $registered_users_count += $row['count'];
                                }
                                $stmt->close();
                            }
                        }
                        ?>
                        
                        <div class="status-card">
                            <div class="status-card-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="status-card-info">
                                <div class="status-value"><?php echo $active_events; ?></div>
                                <div class="status-label">Active Events</div>
                            </div>
                        </div>
                        
                        <div class="status-card">
                            <div class="status-card-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="status-card-info">
                                <div class="status-value"><?php echo $upcoming_events; ?></div>
                                <div class="status-label">Upcoming Events</div>
                            </div>
                        </div>
                        
                        <div class="status-card">
                            <div class="status-card-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="status-card-info">
                                <div class="status-value"><?php echo $registered_users_count; ?></div>
                                <div class="status-label">Total Registrations</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3>Actions</h3>
                    </div>
                    
                    <div class="dashboard-actions" style="display: flex; flex-direction: column; gap: 20px; text-align: center; max-width: 600px; margin: 0 auto;">
                        <div>
                            <button id="createEventBtn" class="btn btn-primary" style="width: 100%; padding: 15px;">
                                Create New Event
                            </button>
                        </div>
                        
                        <div>
                            <button id="manageEventsBtn" class="btn btn-outline" style="width: 100%; padding: 15px;">
                                Edit Events
                            </button>
                        </div>
                        
                        <div>
                            <button id="showUsersBtn" class="btn btn-outline" style="width: 100%; padding: 15px;">
                                Show Registered Users
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Create Event Form (Hidden by default) -->
                <div id="createEventForm" style="display: none;" class="dashboard-section">
                    <div class="section-header">
                        <h3>Create New Event</h3>
                    </div>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label>Event Title</label>
                            <input type="text" name="title" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-input" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" class="form-input" required>
                        </div>
                        
                        <div style="display: flex; gap: 20px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Date</label>
                                <input type="date" name="date" class="form-input" required>
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label>Time</label>
                                <input type="time" name="time" class="form-input" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Required Volunteers</label>
                            <input type="number" name="required" class="form-input" min="1" required>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <button type="submit" name="create_event" class="btn btn-primary">Create Event</button>
                            <button type="button" id="cancelCreateEvent" class="btn btn-outline">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <!-- Manage Events Section (Hidden by default) -->
                <div id="manageEventsSection" style="display: none;" class="dashboard-section">
                    <div class="section-header">
                        <h3>Manage Events</h3>
                    </div>
                    
                    <?php if(count($events) > 0): ?>
                        <div class="event-queue">
                            <?php foreach($events as $event): ?>
                                <div class="event-item">
                                    <div class="event-date">
                                        <span class="month"><?php echo date('M', strtotime($event['date'])); ?></span>
                                        <span class="day"><?php echo date('d', strtotime($event['date'])); ?></span>
                                    </div>
                                    <div class="event-details">
                                        <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <div class="event-meta">
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                            <span><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($event['time'])); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="event-status <?php echo strtolower($event['status']); ?>"><?php echo $event['status']; ?></span>
                                    </div>
                                    <div style="margin-left: 20px;">
                                        <a href="?event_id=<?php echo $event['id']; ?>" class="btn btn-outline">View</a>
                                        <a href="#" class="btn btn-outline edit-event" data-id="<?php echo $event['id']; ?>">Edit</a>
                                        <a href="?delete_event=<?php echo $event['id']; ?>" class="btn btn-outline" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No events created yet. Click "Create New Event" to get started.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Show Registered Users for All Events (Hidden by default) -->
                <div id="showUsersSection" style="display: none;" class="dashboard-section">
                    <div class="section-header">
                        <h3>Events with Registered Users</h3>
                    </div>
                    
                    <?php if(count($events) > 0): ?>
                        <div class="event-queue">
                            <?php foreach($events as $event): 
                                // Get registered users count for this event
                                $conn = getConnection();
                                $registered_count = 0;
                                $sql = "SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?";
                                if ($stmt = $conn->prepare($sql)) {
                                    $stmt->bind_param("i", $event['id']);
                                    if ($stmt->execute()) {
                                        $result = $stmt->get_result();
                                        $row = $result->fetch_assoc();
                                        $registered_count = $row['count'];
                                    }
                                    $stmt->close();
                                }
                                $conn->close();
                            ?>
                                <div class="event-item">
                                    <div class="event-date">
                                        <span class="month"><?php echo date('M', strtotime($event['date'])); ?></span>
                                        <span class="day"><?php echo date('d', strtotime($event['date'])); ?></span>
                                    </div>
                                    <div class="event-details">
                                        <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <div class="event-meta">
                                            <span><i class="fas fa-users"></i> <?php echo $registered_count; ?> / <?php echo $event['required']; ?> registered</span>
                                        </div>
                                    </div>
                                    <div style="margin-left: 20px;">
                                        <a href="?event_id=<?php echo $event['id']; ?>" class="btn btn-outline">View Registrations</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No events created yet. Click "Create New Event" to get started.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // JavaScript for UI interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Create Event Form toggle
            const createEventBtn = document.getElementById('createEventBtn');
            const createEventForm = document.getElementById('createEventForm');
            const cancelCreateEvent = document.getElementById('cancelCreateEvent');
            
            createEventBtn.addEventListener('click', function() {
                createEventForm.style.display = 'block';
                manageEventsSection.style.display = 'none';
                showUsersSection.style.display = 'none';
            });
            
            cancelCreateEvent.addEventListener('click', function() {
                createEventForm.style.display = 'none';
            });
            
            // Manage Events toggle
            const manageEventsBtn = document.getElementById('manageEventsBtn');
            const manageEventsSection = document.getElementById('manageEventsSection');
            
            manageEventsBtn.addEventListener('click', function() {
                manageEventsSection.style.display = 'block';
                createEventForm.style.display = 'none';
                showUsersSection.style.display = 'none';
            });
            
            // Show Users toggle
            const showUsersBtn = document.getElementById('showUsersBtn');
            const showUsersSection = document.getElementById('showUsersSection');
            
            showUsersBtn.addEventListener('click', function() {
                showUsersSection.style.display = 'block';
                createEventForm.style.display = 'none';
                manageEventsSection.style.display = 'none';
            });
        });
    </script>
</body>
</html>