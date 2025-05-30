<?php
session_start();

function organizationLogin($email, $password) {
    $host = 'localhost';
    $dbname = 'volunteers';
    $username = 'assigner';
    $dbpassword = 'Assignments_789';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Look up organization by email
        $stmt = $pdo->prepare("SELECT id, email, password, name FROM organizations WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 0) {
            return "invalid_credentials"; 
        }

        $organization = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if (password_verify($password, $organization['password'])) {

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Set session variables for organization
            $_SESSION['org_id'] = $organization['id'];
            $_SESSION['org_email'] = $organization['email'];
            $_SESSION['org_name'] = $organization['name'];
            $_SESSION['org_logged_in'] = true;
            $_SESSION["loggedin"] = true;

            return true; // Login successful
        } else {
            return "invalid_credentials"; 
        }
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = organizationLogin($email, $password);

    if ($result === true) {
        header("Location: dashboard/organization.php");
        exit();
    } elseif ($result === "invalid_credentials") {
        echo "<script>alert('Incorrect email or password.'); window.history.back();</script>";
        exit();
    } else {
        echo "<script>alert(" . json_encode($result) . "); window.history.back();</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Login | Vol.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.html"><div class="logo">Vol<span>.</span></div></a>
                <div class="cta-buttons">
                    <a href="organization-signup.html" class="btn btn-outline">Organization Sign Up</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="login-container">
                <div class="login-header">
                    <h2>Organization Login</h2>
                    <p>Please enter your organization's email and password to login</p>
                </div>
                <form action="" method="post"> <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="org@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                    </div>
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            Remember me
                        </label>
                        <a href="#">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Log In</button>
                </form>

                <div class="signup-link">
                    Not an organization account? <a href="login.php">Volunteer Login</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Vol.</h3>
                    <p>Connecting passionate volunteers with impactful causes to create positive change in communities worldwide.</p>
                    <div class="social-links">
                        <a href="#"><i>f</i></a>
                        <a href="#"><i>t</i></a>
                        <a href="#"><i>in</i></a>
                        <a href="#"><i>ig</i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>For Volunteers</h3>
                    <ul>
                        <li><a href="#">Browse Opportunities</a></li>
                        <li><a href="#">Create Profile</a></li>
                        <li><a href="#">Track Hours</a></li>
                        <li><a href="#">Get Verified</a></li>
                        <li><a href="#">Volunteer Resources</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>For Organizations</h3>
                    <ul>
                        <li><a href="#">Post Opportunities</a></li>
                        <li><a href="#">Manage Volunteers</a></li>
                        <li><a href="#">Organization Dashboard</a></li>
                        <li><a href="#">Success Stories</a></li>
                        <li><a href="#">NGO Resources</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>About</h3>
                    <ul>
                        <li><a href="#">Our Mission</a></li>
                        <li><a href="#">How It Works</a></li>
                        <li><a href="#">Team</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Help Center</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Vol. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>