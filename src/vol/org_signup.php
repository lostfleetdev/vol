<?php
session_start();

function org_signup($name, $email, $password, $phone = null, $address = null) {
    $host = 'localhost';
    $dbname = 'volunteers';
    $username = 'assigner';
    $dbpassword = 'Assignments_789';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $stmt = $pdo->prepare("SELECT id FROM organizations WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            return "email_exists"; 
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO organizations (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $phone, $address]);

        return true; // Signup successful
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? null;

    $result = org_signup($name, $email, $password, $phone, $address);

    if ($result === true) {
        header("Location: org_login.php"); // After signup, redirect to org login
        exit();
    } elseif ($result === "email_exists") {
        echo "<script>alert('Email already exists. Please use a different email.'); window.history.back();</script>";
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
    <title>Organization Sign Up | Vol.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <div class="container">
        <nav>
            <a href="index.html"><div class="logo">Vol<span>.</span></div></a>
            <div class="cta-buttons">
                <a href="org_login.php" class="btn btn-outline">Login</a>
            </div>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2>Organization Sign Up</h2>
                <p>Create your organization account</p>
            </div>
            <form action="" method="post">
                <div class="form-group">
                    <label for="name">Organization Name</label>
                    <input type="text" id="name" name="name" class="form-input" placeholder="Organization Name" required>
                </div>
                <div class="form-group">
                    <label for="email">Organization Email</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="org@email.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Create Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone (optional)</label>
                    <input type="text" id="phone" name="phone" class="form-input" placeholder="Phone Number">
                </div>
                <div class="form-group">
                    <label for="address">Address (optional)</label>
                    <textarea id="address" name="address" class="form-input" placeholder="Organization Address"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Sign Up</button>
            </form>

            <div class="signup-link">
                Already have an account? <a href="org_login.php">Log in</a>
            </div>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; 2025 Vol. All rights reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>
