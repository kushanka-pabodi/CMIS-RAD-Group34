<?php
require_once 'db_connect.php'; 
session_start();

$loginError = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $redirect = $_POST['redirect'] ?? ''; // Get the "redirect" from the form

    // Validate login credentials
    $sql = "SELECT * FROM admin WHERE name = ? AND password = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Successful login
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = $username;

        // Redirect based on the "redirect" parameter
        if ($redirect === 'academic') {
            header("Location: academic.php");
            exit;
        } elseif ($redirect === 'payment') {
            header("Location: payment.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid username or password.";
            header("Location: admin.php?loginError=1");
            exit();
        }
    } else {
        // Invalid login
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: admin.php?loginError=1");
        exit();
    }
}

// Redirect to admin.php if accessed directly
header("Location: admin.php");
exit;
?>
