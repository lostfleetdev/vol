<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Vol.</title>
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <header>
        <div class="container">
            <nav>
                <a href="index.html"><div class="logo">Vol<span>.</span></div></a>
                <div class="cta-buttons">
                    <a href="login.html" class="btn btn-outline">Log In</a>
                </div>
            </nav>
        </div>
    </header>


    <main>
        <div class="container">
            <div class="login-container">
                <div class="login-header">
                    <h2>Create Account</h2>
                    <p>Please fill in your details to register</p>
                </div>
                <form action="lib/register.php" method="post">
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" class="form-input" placeholder="your name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="your@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm-password" class="form-input" placeholder="••••••••" required>
                    </div>
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" required>
                            I agree to the <a href="#">Terms & Conditions</a>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
                </form>

                <div class="divider">or</div>

                <div class="social-login">
                    <button class="social-btn" title="Register with Google">
                        <i class="fab fa-google"></i>
                    </button>
                    <button class="social-btn" title="Register with Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button class="social-btn" title="Register with Apple">
                        <i class="fab fa-apple"></i>
                    </button>
                </div>

                <div class="signup-link">
                    Already have an account? <a href="login.html">Log in</a>
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
    </footer>
</body>
</html>