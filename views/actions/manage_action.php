<?php
session_start();
include '../../config/config.php'; // Pastikan jalur ini benar

// Periksa jika admin sudah login
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit;
}

// Operasi hapus pengguna (hanya untuk admin)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);

        // Set notifikasi sukses untuk penghapusan
        $_SESSION['notif'] = [
            'type' => 'success',
            'message' => 'Pengguna berhasil dihapus.'
        ];
    } catch (Exception $e) {
        // Set notifikasi error jika terjadi kesalahan
        $_SESSION['notif'] = [
            'type' => 'error',
            'message' => 'Gagal menghapus pengguna. Silakan coba lagi.'
        ];
    }
}

// Redirect kembali ke halaman manage_users.php
header("Location: /views/manage_users.php");
exit;
