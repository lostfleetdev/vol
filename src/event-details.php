<?php
require "lib/functions.php";

$host = "localhost";
$dbname = "volunteers";
$username = "assigner";
$password = "Assignments_789";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
        $event_id = intval($_GET["id"]);

        $event = getEventDetails($pdo, $event_id);

        if (!$event) {
            
            header("Location: error.php?message=Event not found"); // Create an error.php page
            exit();
        }
    } else {
        header("Location: search.php"); //  redirect to the search page
        exit();
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event["title"]); ?> | vol.</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">vol<span>.</span></div>
                <div class="nav-links">
                    <a href="index.html">Home</a>
                    <a href="opportunities.html">Opportunities</a>
                    <a href="organizations.html">Organizations</a>
                    <a href="about.html">About Us</a>
                </div>
                <div class="cta-buttons">
                    <a href="login.html" class="btn btn-outline">Log In</a>
                    <a href="signup.html" class="btn btn-primary">Sign Up</a>
                </div>
            </nav>
        </div>
    </header>

    <section class="container" style="padding: 3rem 0;">
        <div class="event-details">
            <div class="section-heading">
                <h2><?php echo htmlspecialchars($event["title"]); ?></h2>
            </div>
            <div class="event-content">
                <div class="event-image" style="margin-bottom: 2rem;">
                    <img src="/api/placeholder/800/400" alt="<?php echo htmlspecialchars(
                        $event["title"]
                    ); ?>">
                </div>
                <div class="event-meta">
                    <p><i class="fas fa-building"></i> Organization: <?php echo htmlspecialchars(
                        $event["organization_name"]
                    ); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> Location: <?php echo htmlspecialchars(
                        $event["location"]
                    ); ?></p>
                    <p><i class="fas fa-calendar-alt"></i> Date: <?php echo date(
                        "F j, Y",
                        strtotime($event["date"])
                    ); ?></p>
                    <p><i class="fas fa-clock"></i> Time: <?php echo date(
                        "h:i A",
                        strtotime($event["time"])
                    ); ?></p>
                     <p><i class="fas fa-users"></i> Volunteers Needed: <?php echo htmlspecialchars(
                         $event["required"]
                     ); ?></p>
                    <p>Status: <?php echo htmlspecialchars(
                        $event["status"]
                    ); ?></p>
                </div>
                <div class="event-description" style="margin-top: 2rem;">
                    <h3>Description</h3>
                    <p><?php echo htmlspecialchars(
                        $event["description"]
                    ); ?></p>
                </div>
                <div class="event-registration" style="margin-top: 2rem;">
                     <a href="#" class="btn btn-primary apply-btn">Register Now</a>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>vol.</h3>
                    <p>Connecting volunteers and organizations to make a difference in communities worldwide.</p>
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
                        <li><a href="index.html">Home</a></li>
                        <li><a href="opportunities.html">Find Opportunities</a></li>
                        <li><a href="organizations.html">Organizations</a></li>
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="#">How It Works</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Testimonials</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-envelope"></i> info@vol.org</li>
                        <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Volunteer St, City, Country</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> vol. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
