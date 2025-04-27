<?php
require('lib/functions.php');

$host = 'localhost';
$dbname = 'volunteers';
$username = 'assigner';
$password = 'Assignments_789';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = '';

    // Handle POST and redirect with GET
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
        $query = trim($_POST['query']);
        header("Location: search.php?query=" . urlencode($query));
        exit;
    }

    // Handle GET display
    if (isset($_GET['query'])) {
        $query = trim($_GET['query']);
        $results = searchData($pdo, $query);
    } else {
        $results = null;
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | vol.</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header -->
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

    <!-- Search Results Section -->
    <section class="container">
        <div class="search-results" style="padding: 3rem 0;">
            <div class="section-heading">
                <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
                <?php if ($results): ?>
                    <p><?php echo count($results['organizations']) + count($results['events']); ?> results found</p>
                <?php endif; ?>
            </div>

            <!-- Search Refinement Form -->
            <form action="search.php" method="POST" class="search-bar">
                <input type="text" name="query" value="<?php echo htmlspecialchars($query); ?>" placeholder="Refine your search...">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <?php if ($results): ?>
                <!-- Organizations Results -->
                <div class="feature-card" style="margin-bottom: 2rem;">
                    <h3>Organizations (<?php echo count($results['organizations']); ?>)</h3>
                    <?php if (count($results['organizations']) > 0): ?>
                        <div class="opportunity-grid" style="margin-top: 1.5rem;">
                            <?php foreach ($results['organizations'] as $org): ?>
                                <div class="opportunity-card">
                                    <div class="opportunity-img">
                                        <img src="/api/placeholder/400/200" alt="<?php echo htmlspecialchars($org['name']); ?>">
                                    </div>
                                    <div class="opportunity-content">
                                        <h3><?php echo htmlspecialchars($org['name']); ?></h3>
                                        <div class="opportunity-meta">
                                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($org['email']); ?></span>
                                        </div>
                                        <p><?php echo htmlspecialchars($org['address']); ?></p>
                                        <div class="tags-container">
                                            <span class="tag">Organization</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No matching organizations found.</p>
                    <?php endif; ?>
                </div>

                <!-- Events Results -->
                <div class="feature-card">
                    <h3>Events (<?php echo count($results['events']); ?>)</h3>
                    <?php if (count($results['events']) > 0): ?>
                        <div class="opportunity-grid" style="margin-top: 1.5rem;">
                            <?php foreach ($results['events'] as $event): ?>
                                <div class="opportunity-card">
                                    <div class="opportunity-img">
                                        <img src="/api/placeholder/400/200" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                    </div>
                                    <div class="opportunity-content">
                                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <div class="opportunity-meta">
                                            <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($event['organization_name']); ?></span>
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                        </div>
                                        <p><?php echo htmlspecialchars($event['description']); ?></p>
                                        <div class="tags-container">
                                            <span class="tag">Event</span>
                                        </div>
                                        <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary apply-btn">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No matching events found.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="feature-card" style="text-align: center; padding: 3rem;">
                    <div class="feature-icon" style="margin: 0 auto;">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Start Searching</h3>
                    <p>Use the search bar above to find volunteering events and organizations.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
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
                <p>&copy; <?php echo date('Y'); ?> vol. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>