<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "assigner";
$password = "Assignments_789";
$dbname = "volunteers";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get user's registered events
$sql = "SELECT e.*, o.name as organization_name, er.registered_at 
        FROM events e
        JOIN event_registrations er ON e.id = er.event_id
        JOIN organizations o ON e.organization_id = o.id
        WHERE er.user_id = ?
        ORDER BY e.date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$registered_events = $stmt->get_result();

// Get upcoming events (not registered by user)
$sql = "SELECT e.*, o.name as organization_name
        FROM events e
        JOIN organizations o ON e.organization_id = o.id
        WHERE e.date >= CURDATE()
        AND e.id NOT IN (
            SELECT event_id FROM event_registrations WHERE user_id = ?
        )
        ORDER BY e.date ASC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming_events = $stmt->get_result();

// Handle event registration if form submitted
if (isset($_POST['register_event'])) {
    $event_id = $_POST['event_id'];
    
    // Check if already registered
    $check_sql = "SELECT * FROM event_registrations WHERE user_id = ? AND event_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $event_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        // Register for the event
        $register_sql = "INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)";
        $register_stmt = $conn->prepare($register_sql);
        $register_stmt->bind_param("ii", $user_id, $event_id);
        
        if ($register_stmt->execute()) {
            // Refresh the page to show updated registrations
            header("Location: user.php?success=1");
            exit();
        } else {
            $error_message = "Failed to register for event. Please try again.";
        }
    } else {
        $error_message = "You are already registered for this event.";
    }
}

// Handle event cancellation
if (isset($_GET['cancel_event'])) {
    $event_id = $_GET['cancel_event'];
    
    // Delete the registration
    $delete_sql = "DELETE FROM event_registrations WHERE user_id = ? AND event_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $user_id, $event_id);
    
    if ($delete_stmt->execute()) {
        // Refresh the page to show updated registrations
        header("Location: user.php?cancelled=1");
        exit();
    } else {
        $error_message = "Failed to cancel registration. Please try again.";
    }
}

// Search functionality
$search_results = null;
if (isset($_GET['search'])) {
    $search_term = "%" . $_GET['search'] . "%";
    
    $search_sql = "SELECT e.*, o.name as organization_name
                 FROM events e
                 JOIN organizations o ON e.organization_id = o.id
                 WHERE (e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ? OR o.name LIKE ?)
                 AND e.date >= CURDATE()
                 ORDER BY e.date ASC";
    $search_stmt = $conn->prepare($search_sql);
    $search_stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    $search_stmt->execute();
    $search_results = $search_stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vol. Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                
                <div class="logo">Vol<span>.</span></div>
                
                <div class="cta-buttons">
                    <a href="logout.php" class="btn btn-outline">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container dashboard-container">
        <!-- Left Sidebar -->
        <div class="dashboard-sidebar">
            <div class="user-profile">
                <div class="profile-header">
                    <img src="user.png" alt="Profile Picture" class="profile-pic">
                    <h3><?php echo htmlspecialchars($user['fullname']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                
                <div class="volunteer-stats">
                    <div class="stat">
                        <span class="stat-value"><?php echo $registered_events->num_rows; ?></span>
                        <span class="stat-label">Events</span>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Main Content -->
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h2>My Dashboard</h2>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert success-alert">
                <i class="fas fa-check-circle"></i>
                Successfully registered for the event!
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['cancelled'])): ?>
            <div class="alert info-alert">
                <i class="fas fa-info-circle"></i>
                Event registration cancelled.
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert error-alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Search Box -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Search Events</h3>
                </div>
                <form action="user.php" method="GET" class="search-bar">
                    <input type="text" name="search" placeholder="Search events, organizations, locations..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                
                <?php if ($search_results && $search_results->num_rows > 0): ?>
                <div class="search-results">
                    <h4>Search Results</h4>
                    <div class="event-queue">
                        <?php while ($event = $search_results->fetch_assoc()): ?>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="month"><?php echo date('M', strtotime($event['date'])); ?></span>
                                <span class="day"><?php echo date('d', strtotime($event['date'])); ?></span>
                            </div>
                            <div class="event-details">
                                <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                <div class="event-meta">
                                    <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($event['organization_name']); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($event['time'])); ?></span>
                                </div>
                            </div>
                            <form action="user.php" method="POST">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button type="submit" name="register_event" class="btn btn-primary">Register</button>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php elseif (isset($_GET['search'])): ?>
                <div class="no-results">
                    <p>No events found matching your search criteria.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Registered Events -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Registered Events</h3>
                </div>
                
                <?php if ($registered_events->num_rows > 0): ?>
                <div class="event-queue">
                    <?php while ($event = $registered_events->fetch_assoc()): ?>
                    <div class="event-item">
                        <div class="event-date">
                            <span class="month"><?php echo date('M', strtotime($event['date'])); ?></span>
                            <span class="day"><?php echo date('d', strtotime($event['date'])); ?></span>
                        </div>
                        <div class="event-details">
                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                            <div class="event-meta">
                                <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($event['organization_name']); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($event['time'])); ?></span>
                            </div>
                            <p>Registered on: <?php echo date('M d, Y', strtotime($event['registered_at'])); ?></p>
                        </div>
                        <div class="event-status <?php echo $event['status']; ?>">
                            <?php echo ucfirst($event['status']); ?>
                        </div>
                        <a href="user.php?cancel_event=<?php echo $event['id']; ?>" class="btn btn-outline" onclick="return confirm('Are you sure you want to cancel this registration?');">Cancel</a>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="no-events">
                    <p>You haven't registered for any events yet.</p>
                    <p>Browse the upcoming events and start volunteering!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Right Sidebar - Upcoming Events -->
        <div class="dashboard-widgets">
            <div class="widget">
                <h3>Upcoming Events</h3>
                
                <?php if ($upcoming_events->num_rows > 0): ?>
                <div class="event-queue">
                    <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                    <div class="event-item">
                        <div class="event-date">
                            <span class="month"><?php echo date('M', strtotime($event['date'])); ?></span>
                            <span class="day"><?php echo date('d', strtotime($event['date'])); ?></span>
                        </div>
                        <div class="event-details">
                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                            <div class="event-meta">
                                <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($event['organization_name']); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                            </div>
                        </div>
                        <form action="user.php" method="POST">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" name="register_event" class="btn btn-primary btn-sm">Register</button>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
                <a href="#" class="view-all">View All Events <i class="fas fa-arrow-right"></i></a>
                <?php else: ?>
                <div class="no-events">
                    <p>No upcoming events available.</p>
                </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
    
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Vol.</h3>
                    <p>Connecting volunteers with meaningful opportunities to make a difference in their communities.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Find Events</a></li>
                        <li><a href="#">Organizations</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Subscribe</h3>
                    <p>Get updates on new volunteer opportunities.</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your Email" required class="form-input">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Vol. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>