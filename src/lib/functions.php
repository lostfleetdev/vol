<?php
function register($fullname, $email, $password, $confirmPassword, $termsAccepted) {
    if (!$termsAccepted) {
        return "You must accept the Terms & Conditions.";
    }

    if ($password !== $confirmPassword) {
        return "Passwords do not match.";
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $host = 'localhost';
    $dbname = 'volunteers';
    $username = 'assigner';
    $dbpassword = 'Assignments_789';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            return "email_taken";
        }

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$fullname, $email, $hashedPassword]);

        return true;
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

function login($email, $password) {
    $host = 'localhost';
    $dbname = 'volunteers';
    $username = 'assigner';
    $dbpassword = 'Assignments_789';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Look up user by email
        $stmt = $pdo->prepare("SELECT id, email, password, fullname FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 0) {
            return "invalid_credentials"; // Email not found
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Start session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['logged_in'] = true;
            
            return true; // Login successful
        } else {
            return "invalid_credentials"; // Password mismatch
        }
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

// Function to check if user is logged in
function is_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Function to logout user
function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    return true;
}


function searchData(PDO $pdo, string $searchTerm): array
{
    $results = [
        'organizations' => [],
        'events' => []
    ];

    $searchTerm = '%' . $searchTerm . '%'; // Add wildcards for LIKE clause

    // Search organizations
    $sqlOrganizations = "SELECT id, name, email, phone, address FROM organizations WHERE name LIKE :term OR email LIKE :term OR phone LIKE :term OR address LIKE :term";
    $stmtOrganizations = $pdo->prepare($sqlOrganizations);
    $stmtOrganizations->bindParam(':term', $searchTerm, PDO::PARAM_STR);
    $stmtOrganizations->execute();
    $results['organizations'] = $stmtOrganizations->fetchAll(PDO::FETCH_ASSOC);

    // Search events (including the organization name for display)
    $sqlEvents = "
        SELECT
            e.id,
            e.title,
            e.description,
            e.location,
            e.date,
            e.time,
            o.name AS organization_name
        FROM
            events e
        JOIN
            organizations o ON e.organization_id = o.id
        WHERE
            e.title LIKE :term OR e.description LIKE :term OR e.location LIKE :term OR o.name LIKE :term
    ";
    $stmtEvents = $pdo->prepare($sqlEvents);
    $stmtEvents->bindParam(':term', $searchTerm, PDO::PARAM_STR);
    $stmtEvents->execute();
    $results['events'] = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

    return $results;
}
?>
