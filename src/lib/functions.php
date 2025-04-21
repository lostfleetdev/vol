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
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 0) {
            return "invalid_credentials"; // Email not found
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if (password_verify($password, $user['password'])) {
            return true; // Login successful
        } else {
            return "invalid_credentials"; // Password mismatch
        }
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

function searchData($pdo, $query) {
    $results = [
        'organizations' => [],
        'events' => []
    ];

    $searchTerm = "%" . $query . "%";

    // Search organizations
    $orgStmt = $pdo->prepare("SELECT * FROM organizations 
        WHERE name LIKE ? OR email LIKE ? OR address LIKE ?");
    $orgStmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $results['organizations'] = $orgStmt->fetchAll(PDO::FETCH_ASSOC);

    // Search events (include organization name)
    $eventStmt = $pdo->prepare("
        SELECT events.*, organizations.name AS organization_name 
        FROM events 
        JOIN organizations ON events.organization_id = organizations.id 
        WHERE events.title LIKE ? 
           OR events.description LIKE ? 
           OR events.location LIKE ? 
           OR organizations.name LIKE ?
    ");
    $eventStmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $results['events'] = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

    return $results;
}

?>
