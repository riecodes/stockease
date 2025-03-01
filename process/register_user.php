<?php
session_start();
include '../include/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Method not allowed';
    header("Location: ../register.php");
    exit();
}

// Get and sanitize inputs
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

// Save old input values so the form can be repopulated on error
$_SESSION['old'] = [
    'name'     => $name,
    'email'    => $email,
    'username' => $username
];

// Validate inputs
if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = 'All fields are required';
    header("Location: ../register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format';
    header("Location: ../register.php");
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match';
    header("Location: ../register.php");
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters';
    header("Location: ../register.php");
    exit();
}

// Check if email already exists
$stmt = $conn->prepare("SELECT admin_id FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = 'Email already registered';
    header("Location: ../register.php");
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new admin
$stmt = $conn->prepare("INSERT INTO admins (name, email, username, password_hash) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $username, $hashed_password);

if ($stmt->execute()) {
    // Clear the old input values on success
    unset($_SESSION['old']);
    $_SESSION['success'] = 'Registration successful! Please log in.';
    header("Location: ../index.php"); // Redirect to login page (or your preferred page)
    exit();
} else {
    error_log("Database error: " . $stmt->error);
    $_SESSION['error'] = 'Registration failed. Please try again later.';
    header("Location: ../register.php");
    exit();
}
