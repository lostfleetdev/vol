<?php
session_start();
require 'functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = login($email, $password);

    if ($result === true) {
        // Redirect to dashboard after successful login
        header("Location: ../dashboard/user.php");
        exit();
    } elseif ($result === "invalid_credentials") {
        echo "<script>alert('Incorrect email or password.'); window.history.back();</script>";
    } else {
        echo "<script>alert(" . json_encode($result) . "); window.history.back();</script>";
    }
}
?>