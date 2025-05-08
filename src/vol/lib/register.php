<?php
require('functions.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';
    $termsAccepted = isset($_POST['terms']);

    $result = register($fullname, $email, $password, $confirmPassword, $termsAccepted);

    if ($result === true) {
        // ✅ Redirect to login
        echo "<script>window.location.href = '../login.php';</script>";
    } elseif ($result === "email_taken") {
        // ❌ Email already registered
        echo "<script>alert('Email already registered.'); window.history.back();</script>";
    } else {
        // ❌ Other error (like DB error or validation)
        echo "<script>alert(" . json_encode($result) . "); window.history.back();</script>";
    }
}
?>
