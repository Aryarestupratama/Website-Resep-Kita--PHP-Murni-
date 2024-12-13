<?php
session_start();
include '../../config/config.php';

if (isset($_GET['term'])) {
    $term = $_GET['term'];
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM search_history WHERE user_id = ? AND search_term = ?");
    $stmt->execute([$userId, $term]);
    
    header('Location: ../index.php'); // Kembali ke halaman utama setelah menghapus
    exit;
}
?>