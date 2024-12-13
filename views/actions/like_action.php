<?php
session_start();
include '../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$resepId = $_GET['resep_id'] ?? null;
$action = $_GET['action'] ?? null;
$userId = $_SESSION['user_id'];

if ($resepId && $action) {
    if ($action === 'like') {
        // Tambah "like" jika belum ada
        $stmt = $pdo->prepare("INSERT IGNORE INTO likes (resep_id, user_id) VALUES (:resep_id, :user_id)");
        $stmt->execute([':resep_id' => $resepId, ':user_id' => $userId]);
    } elseif ($action === 'unlike') {
        // Hapus "like" jika sudah ada
        $stmt = $pdo->prepare("DELETE FROM likes WHERE resep_id = :resep_id AND user_id = :user_id");
        $stmt->execute([':resep_id' => $resepId, ':user_id' => $userId]);
    }
}

header("Location: /views/view_resep.php?id=" . $resepId);
exit;
