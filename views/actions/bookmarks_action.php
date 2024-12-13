<?php
    session_start();
    include '../../config/config.php';

    // Pastikan pengguna sudah login
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];

    // Cek apakah ada aksi yang diminta
    if (isset($_GET['action']) && isset($_GET['resid'])) {
        $resId = $_GET['resid'];

        if ($_GET['action'] === 'add') {
            // Tambah bookmark ke database
            $stmt = $pdo->prepare("INSERT INTO bookmarks (user_id, resep_id) VALUES (:user_id, :res_id)");
            $stmt->execute([':user_id' => $userId, ':res_id' => $resId]);
        } elseif ($_GET['action'] === 'remove') {
            // Hapus bookmark dari database
            $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = :user_id AND resep_id = :res_id");
            $stmt->execute([':user_id' => $userId, ':res_id' => $resId]);
        }
    }

    // Redirect kembali ke halaman bookmark
    header("Location: ../bookmarks.php");
    exit;
?>
