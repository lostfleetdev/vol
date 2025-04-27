<?php
// Initialize the session
session_start();

// Check if the user is logged in as an organization, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "organization") {
    header("location: ../org_login.php");
    exit;
}

// Include database connection
require_once "config.php";

// Define variables and initialize with empty values
$title = $description = $location = $date = $time = $required = $status = "";
$title_err = $description_err = $location_err = $date_err = $time_err = $required_err = $status_err = "";

// Processing form data when form is submitted for adding/editing events
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && ($_POST["action"] == "add" || $_POST["action"] == "edit")) {
    
    // Validate title
    if(empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }
    
    // Validate description
    if(empty(trim($_POST["description"]))) {
        $description_err = "Please enter a description.";
    } else {
        $description = trim($_POST["description"]);
    }
    
    // Validate location
    if(empty(trim($_POST["location"]))) {
        $location_err = "Please enter a location.";
    } else {
        $location = trim($_POST["location"]);
    }
    
    // Validate date
    if(empty(trim($_POST["date"]))) {
        $date_err = "Please enter a date.";
    } else {
        $date = trim($_POST["date"]);
    }
    
    // Validate time
    if(empty(trim($_POST["time"]))) {
        $time_err = "Please enter a time.";
    } else {
        $time = trim($_POST["time"]);
    }
    
    // Validate required volunteers
    if(empty(trim($_POST["required"]))) {
        $required_err = "Please enter the number of required volunteers.";
    } elseif(!is_numeric(trim($_POST["required"])) || trim($_POST["required"]) <= 0) {
        $required_err = "Please enter a valid number.";
    } else {
        $required = trim($_POST["required"]);
    }
    
    // Validate status
    if(empty(trim($_POST["status"]))) {
        $status_err = "Please select a status.";
    } else {
        $status = trim($_POST["status"]);
    }
    
    // Check input errors before inserting or updating in database
    if(empty($title_err) && empty($description_err) && empty($location_err) && empty($date_err) && 
       empty($time_err) && empty($required_err) && empty($status_err)) {
        
        // Add new event
        if($_POST["action"] == "add") {
            // Prepare an insert statement
            $sql = "INSERT INTO events (organization_id, title, description, location, date, time, required, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            if($stmt = mysqli_prepare($link, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "isssssss", $param_org_id, $param_title, $param_description, $param_location, $param_date, $param_time, $param_required, $param_status);
                
                // Set parameters
                $param_org_id = $_SESSION["id"];
                $param_title = $title;
                $param_description = $description;
                $param_location = $location;
                $param_date = $date;
                $param_time = $time;
                $param_required = $required;
                $param_status = $status;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)) {
                    // Event added successfully, show success message
                    $success_message = "Event added successfully.";
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
        // Edit existing event
        else if($_POST["action"] == "edit" && isset($_POST["event_id"])) {
            // Prepare an update statement
            $sql = "UPDATE events SET title = ?, description = ?, location = ?, date = ?, time = ?, required = ?, status = ? WHERE id = ? AND organization_id = ?";
            
            if($stmt = mysqli_prepare($link, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "sssssssii", $param_title, $param_description, $param_location, $param_date, $param_time, $param_required, $param_status, $param_event_id, $param_org_id);
                
                // Set parameters
                $param_title = $title;
                $param_description = $description;
                $param_location = $location;
                $param_date = $date;
                $param_time = $time;
                $param_required = $required;
                $param_status = $status;
                $param_event_id = $_POST["event_id"];
                $param_org_id = $_SESSION["id"];
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)) {
                    // Event updated successfully, show success message
                    $success_message = "Event updated successfully.";
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Process event deletion
if(isset($_GET["delete_event"]) && !empty($_GET["delete_event"])) {
    // Prepare a delete statement
    $sql = "DELETE FROM events WHERE id = ? AND organization_id = ?";
    
    if($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ii", $param_event_id, $param_org_id);
        
        // Set parameters
        $param_event_id = $_GET["delete_event"];
        $param_org_id = $_SESSION["id"];
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)) {
            // Event deleted successfully, redirect to this page
            header("location: organizations.php?delete_success=1");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }
}

// Load event data for editing
$edit_event = null;
if(isset($_GET["edit_event"]) && !empty($_GET["edit_event"])) {
    // Prepare a select statement
    $sql = "SELECT * FROM events WHERE id = ? AND organization_id = ?";
    
    if($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ii", $param_event_id, $param_org_id);
        
        // Set parameters
        $param_event_id = $_GET["edit_event"];
        $param_org_id = $_SESSION["id"];
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)) {
            // Store result
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1) {
                // Fetch the data
                $edit_event = mysqli_fetch_assoc($result);
                
                // Set form values
                $title = $edit_event["title"];
                $description = $edit_event["description"];
                $location = $edit_event["location"];
                $date = $edit_event["date"];
                $time = $edit_event["time"];
                $required = $edit_event["required"];
                $status = $edit_event["status"];
            } else {
                // Event not found, redirect to this page
                header("location: organizations.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }
}

// Get event registrations for a specific event
$view_registrations = null;
$registrations = [];
if(isset($_GET["view_registrations"]) && !empty($_GET["view_registrations"])) {
    // Set view_registrations variable
    $view_registrations = $_GET["view_registrations"];
    
    // Prepare a select statement to get event details
    $sql = "SELECT * FROM events WHERE id = ? AND organization_id = ?";
    
    if($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ii", $param_event_id, $param_org_id);
        
        // Set parameters
        $param_event_id = $view_registrations;
        $param_org_id = $_SESSION["id"];
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)) {
            // Store result
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1) {
                // Fetch the event data
                $event_data = mysqli_fetch_assoc($result);
                
                // Get event registrations
                $sql_registrations = "SELECT er.id, u.fullname, u.email, er.registered_at 
                                     FROM event_registrations er 
                                     JOIN users u ON er.user_id = u.id 
                                     WHERE er.event_id = ?";
                
                if($stmt_reg = mysqli_prepare($link, $sql_registrations)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt_reg, "i", $param_event_id);
                    
                    // Set parameters
                    $param_event_id = $view_registrations;
                    
                    // Attempt to execute the prepared statement
                    if(mysqli_stmt_execute($stmt_reg)) {
                        // Store result
                        $result_reg = mysqli_stmt_get_result($stmt_reg);
                        
                        // Fetch all registrations
                        while($row = mysqli_fetch_assoc($result_reg)) {
                            $registrations[] = $row;
                        }
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    
                    // Close statement
                    mysqli_stmt_close($stmt_reg);
                }
            } else {
                // Event not found, redirect to this page
                header("location: organizations.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }
}

// Get all organization's events
$events = [];
$sql = "SELECT * FROM events WHERE organization_id = ? ORDER BY date DESC";

if($stmt = mysqli_prepare($link, $sql)) {
    // Bind variables to the prepared statement as parameters
    mysqli_stmt_bind_param($stmt, "i", $param_org_id);
    
    // Set parameters
    $param_org_id = $_SESSION["id"];
    
    // Attempt to execute the prepared statement
    if(mysqli_stmt_execute($stmt)) {
        // Store result
        $result = mysqli_stmt_get_result($stmt);
        
        // Fetch all events
        while($row = mysqli_fetch_assoc($result)) {
            // Also get the number of registrations for each event
            $sql_count = "SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?";
            $count = 0;
            
            if($stmt_count = mysqli_prepare($link, $sql_count)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt_count, "i", $param_event_id);
                
                // Set parameters
                $param_event_id = $row["id"];
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt_count)) {
                    // Store result
                    $result_count = mysqli_stmt_get_result($stmt_count);
                    $row_count = mysqli_fetch_assoc($result_count);
                    $count = $row_count["count"];
                }
                
                // Close statement
                mysqli_stmt_close($stmt_count);
            }
            
            // Add count to row
            $row["registrations_count"] = $count;
            $events[] = $row;
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    
    // Close statement
    mysqli_stmt_close($stmt);
}

// Close connection
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">Volunteer<span>Connect</span></div>
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="about.php">About</a>
                    <a href="events.php">Events</a>
                    <a href="contact.php">Contact</a>
                </div>
                <div class="user-profile-mini">
                    <div class="profile-pic-mini" style="background-color: #4CAF50; color: white; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-building"></i>
                    </div>
                    <span><?php echo htmlspecialchars($_SESSION["name"]); ?></span>
                </div>
                <div class="cta-buttons">
                    <a href="logout.php" class="btn btn-outline">Log Out</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-container" style="grid-template-columns: 1fr;">
            <div class="dashboard-content">
                <div class="dashboard-header">
                    <h2>Organization Dashboard</h2>
                </div>

                <?php if(isset($success_message)): ?>
                    <div class="alert success" style="background-color: rgba(76, 175, 80, 0.1); border-left: 4px solid #4CAF50; padding: 1rem; margin-bottom: 1rem;">
                        <p style="color: #2E7D32;"><?php echo $success_message; ?></p>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET["delete_success"])): ?>
                    <div class="alert success" style="background-color: rgba(76, 175, 80, 0.1); border-left: 4px solid #4CAF50; padding: 1rem; margin-bottom: 1rem;">
                        <p style="color: #2E7D32;">Event deleted successfully.</p>
                    </div>
                <?php endif; ?>

                <?php if(!$edit_event && !$view_registrations): ?>
                    <!-- Add New Event Form -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h3>Add New Event</h3>
                        </div>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="background-color: #f9f9f9; padding: 1.5rem; border-radius: 10px;">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-input" value="<?php echo $title; ?>">
                                <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $title_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-input" rows="4"><?php echo $description; ?></textarea>
                                <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $description_err; ?></span>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" class="form-input" value="<?php echo $location; ?>">
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $location_err; ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="date" class="form-input" value="<?php echo $date; ?>">
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $date_err; ?></span>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                <div class="form-group">
                                    <label>Time</label>
                                    <input type="time" name="time" class="form-input" value="<?php echo $time; ?>">
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $time_err; ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label>Required Volunteers</label>
                                    <input type="number" name="required" class="form-input" value="<?php echo $required; ?>" min="1">
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $required_err; ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-input">
                                        <option value="">Select Status</option>
                                        <option value="Open" <?php if($status == "Open") echo "selected"; ?>>Open</option>
                                        <option value="Closed" <?php if($status == "Closed") echo "selected"; ?>>Closed</option>
                                        <option value="Pending" <?php if($status == "Pending") echo "selected"; ?>>Pending</option>
                                    </select>
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $status_err; ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-top: 1rem;">
                                <button type="submit" class="btn btn-primary">Add Event</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <?php if($edit_event): ?>
                    <!-- Edit Event Form -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h3>Edit Event</h3>
                            <a href="organizations.php" class="view-all">Back to Dashboard <i class="fas fa-arrow-right"></i></a>
                        </div>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="background-color: #f9f9f9; padding: 1.5rem; border-radius: 10px;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
                            
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-input" value="<?php echo $title; ?>">
                                <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $title_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-input" rows="4"><?php echo $description; ?></textarea>
                                <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $description_err; ?></span>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" class="form-input" value="<?php echo $location; ?>">
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $location_err; ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="date" class="form-input" value="<?php echo $date; ?>">
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $date_err; ?></span>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                <div class="form-group">
                                    <label>Time</label>
                                    <input type="time" name="time" class="form-input" value="<?php echo $time; ?>">
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $time_err; ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label>Required Volunteers</label>
                                    <input type="number" name="required" class="form-input" value="<?php echo $required; ?>" min="1">
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $required_err; ?></span>
                                </div>
                                
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-input">
                                        <option value="">Select Status</option>
                                        <option value="Open" <?php if($status == "Open") echo "selected"; ?>>Open</option>
                                        <option value="Closed" <?php if($status == "Closed") echo "selected"; ?>>Closed</option>
                                        <option value="Pending" <?php if($status == "Pending") echo "selected"; ?>>Pending</option>
                                    </select>
                                    <span class="error" style="color: #F57C00; font-size: 0.8rem;"><?php echo $status_err; ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-top: 1rem; display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary">Update Event</button>
                                <a href="organizations.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <?php if($view_registrations): ?>
                    <!-- View Registrations -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h3>Registrations for: <?php echo htmlspecialchars($event_data["title"]); ?></h3>
                            <a href="organizations.php" class="view-all">Back to Dashboard <i class="fas fa-arrow-right"></i></a>
                        </div>
                        
                        <div style="background-color: #f9f9f9; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem;">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <div>
                                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event_data["date"]); ?></p>
                                    <p><strong>Time:</strong> <?php echo htmlspecialchars($event_data["time"]); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($event_data["location"]); ?></p>
                                </div>
                                <div>
                                    <p><strong>Status:</strong> <span class="tag"><?php echo htmlspecialchars($event_data["status"]); ?></span></p>
                                    <p><strong>Required Volunteers:</strong> <?php echo htmlspecialchars($event_data["required"]); ?></p>
                                    <p><strong>Registered Volunteers:</strong> <?php echo count($registrations); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if(count($registrations) > 0): ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background-color: #f0f0f0;">
                                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #ddd;">Name</th>
                                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #ddd;">Email</th>
                                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #ddd;">Registration Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($registrations as $registration): ?>
                                            <tr style="border-bottom: 1px solid #ddd;">
                                                <td style="padding: 1rem;"><?php echo htmlspecialchars($registration["fullname"]); ?></td>
                                                <td style="padding: 1rem;"><?php echo htmlspecialchars($registration["email"]); ?></td>
                                                <td style="padding: 1rem;"><?php echo date('F j, Y', strtotime($registration["registered_at"])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; background-color: #f9f9f9; border-radius: 10px;">
                                <p>No registrations found for this event.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!$edit_event && !$view_registrations): ?>
                    <!-- Manage Events -->
                    <div class="dashboard-section" style="margin-top: 2rem;">
                        <div class="section-header">
                            <h3>Manage Events</h3>
                        </div>
                        
                        <?php if(count($events) > 0): ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background-color: #f0f0f0;">
                                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #ddd;">Title</th>
                                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #ddd;">Date</th>
                                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #ddd;">Location</th>
                                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #ddd;">Status</th>
                                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #ddd;">Registrations</th>
                                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #ddd;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($events as $event): ?>
                                            <tr style="border-bottom: 1px solid #ddd;">
                                                <td style="padding: 1rem;"><?php echo htmlspecialchars($event["title"]); ?></td>
                                                <td style="padding: 1rem;"><?php echo date('F j, Y', strtotime($event["date"])); ?></td>
                                                <td style="padding: 1rem;"><?php echo htmlspecialchars($event["location"]); ?></td>
                                                <td style="padding: 1rem;">
                                                    <span class="tag" style="
                                                        <?php if($event["status"] == "Open"): ?>
                                                            background-color: rgba(76, 175, 80, 0.1); color: #4CAF50;
                                                        <?php elseif($event["status"] == "Closed"): ?>
                                                            background-color: rgba(244, 67, 54, 0.1); color: #F44336;
                                                        <?php else: ?>
                                                            background-color: rgba(245, 124, 0, 0.1); color: #F57C00;
                                                        <?php endif; ?>
                                                    ">
                                                        <?php echo htmlspecialchars($event["status"]); ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 1rem;">
                                                    <?php echo $event["registrations_count"]; ?> / <?php echo $event["required"]; ?>
                                                </td>
                                                <td style="padding: 1rem;">
                                                    <div style="display:
                                                    <div style="display: flex; gap: 0.5rem;">
                                                        <a href="organizations.php?view_registrations=<?php echo $event["id"]; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                                            <i class="fas fa-users"></i> View Registrations
                                                        </a>
                                                        <a href="organizations.php?edit_event=<?php echo $event["id"]; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <a href="organizations.php?delete_event=<?php echo $event["id"]; ?>" class="btn btn-outline" 
                                                           style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-color: #F44336; color: #F44336;"
                                                           onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.');">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; background-color: #f9f9f9; border-radius: 10px;">
                                <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                <p>No events found. Create your first event using the form above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>VolunteerConnect</h3>
                    <p style="color: #ccc; margin-bottom: 1rem;">Connecting organizations with passionate volunteers to make a difference in communities.</p>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="events.php">Events</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>For Volunteers</h3>
                    <ul>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="events.php">Find Opportunities</a></li>
                        <li><a href="#">Volunteer Guide</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>For Organizations</h3>
                    <ul>
                        <li><a href="organization-register.php">Register</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="#">Post Opportunities</a></li>
                        <li><a href="#">Resources</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> VolunteerConnect. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight the active status in the filter dropdown if needed
            
            // Optional: Add confirmation for delete actions
            const deleteButtons = document.querySelectorAll('[data-action="delete"]');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>