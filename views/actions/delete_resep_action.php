<?php
session_start();
include '../../config/config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$resepId = $_POST['resep_id'] ?? null;

if ($resepId) {
    // Hapus resep dari database
    $stmt = $pdo->prepare("DELETE FROM resep WHERE id = :resep_id AND user_id = :user_id");
    $stmt->execute([':resep_id' => $resepId, ':user_id' => $_SESSION['user_id']]);
}

header("Location: /views/resep.php");
exit;
