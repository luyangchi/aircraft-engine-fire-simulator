<?php
session_start();
include 'db.php';

$name  = trim($_POST['name']);
$email = trim($_POST['email']);

// Validate required fields
if (empty($name) || empty($email)) {
    die("Name and Email are required.");
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format.");
}

// Store in session
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;

// Create attempt record
$name_safe  = $conn->real_escape_string($name);
$email_safe = $conn->real_escape_string($email);

$sql = "INSERT INTO attempts (name, email, start_time)
        VALUES ('$name_safe', '$email_safe', NOW())";

$conn->query($sql);

$_SESSION['attempt_id'] = $conn->insert_id;
$_SESSION['step_id']    = 1;

// Go to overview before starting simulation
header("Location: overview.php");
exit();
?>