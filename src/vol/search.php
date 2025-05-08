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
    $results = ['organizations' => [], 'events' => []];

    if (isset($_GET['search'])) {
        $query = trim($_GET['search']);
        if (!empty($query)) {
            $results = searchData($pdo, $query);
        }
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

    <section class="container">
        <div class="search-results" style="padding: 3rem 0;">
            <div class="section-heading">
                <h2>Search</h2>
            </div>

            <form action="search.php" method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search events, organizations, locations..." value="<?php echo htmlspecialchars($query); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            <br>

            <?php if (!empty($query)): ?>
                <div class="section-heading">
                    <h3>Search Results for "<?php echo htmlspecialchars($query); ?>"</h3>
                    <p><?php echo count($results['organizations']) + count($results['events']); ?> results found</p>
                </div>

                <?php if (!empty($results['events'])): ?>
                    <div class="feature-card" style="margin-bottom: 2rem;">
                        <h3>Events (<?php echo count($results['events']); ?>)</h3>
                        <div class="event-queue" style="margin-top: 1.5rem;">
                            <?php foreach ($results['events'] as $event): ?>
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
                                    <div class="event-actions">
                                        <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary apply-btn">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="feature-card" style="margin-bottom: 2rem;">
                        <h3>Events (0)</h3>
                        <p>No matching events found.</p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($results['organizations'])): ?>
                    <div class="feature-card">
                        <h3>Organizations (<?php echo count($results['organizations']); ?>)</h3>
                        <div class="opportunity-grid" style="margin-top: 1.5rem;">
                            <?php foreach ($results['organizations'] as $org): ?>
                                <div class="opportunity-card">
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
                    </div>
                <?php else: ?>
                    <div class="feature-card">
                        <h3>Organizations (0)</h3>
                        <p>No matching organizations found.</p>
                    </div>
                <?php endif; ?>

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