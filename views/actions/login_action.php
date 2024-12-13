<?php
session_start(); // Memulai sesi
include '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Mengambil pengguna dari database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Memeriksa login
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Memeriksa role pengguna
        if ($user['role'] === 'admin') {
            $_SESSION['is_admin'] = true;
            header("Location: ../admin_dashboard.php"); // Arahkan ke dashboard admin
            exit;
        } else {
            header("Location: ../index.php"); // Arahkan ke dashboard pengguna
            exit;
        }
    } else {
        // Redirect jika login gagal
        header("Location: ../login.php?login=failed");
        exit;
    }
}
?>