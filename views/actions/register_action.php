<?php
include '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password

    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);

    if ($stmt->rowCount() > 0) {
        die("Username atau email sudah terdaftar.");
    }

    // Insert new user with role 'user'
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'user')");
    $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password]);

    header("Location: ../login.php");
    exit;
}
?>
