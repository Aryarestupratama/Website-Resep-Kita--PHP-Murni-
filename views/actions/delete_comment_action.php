<?php
session_start();
include '../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$commentId = $_POST['comment_id'] ?? null;
$resepId = $_POST['resep_id'] ?? null;

if ($commentId && $resepId) {
    // Pastikan pengguna adalah pemilik komentar atau pemilik resep sebelum menghapus
    // Ambil user_id dari komentar
    $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = :comment_id");
    $stmt->execute([':comment_id' => $commentId]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ambil user_id dari resep
    $stmt = $pdo->prepare("SELECT user_id FROM resep WHERE id = :resep_id");
    $stmt->execute([':resep_id' => $resepId]);
    $resep = $stmt->fetch(PDO::FETCH_ASSOC);

    $currentUserId = $_SESSION['user_id'];

    if ($comment && $resep) {
        if ($comment['user_id'] == $currentUserId || $resep['user_id'] == $currentUserId) {
            // Hapus komentar dari database
            $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE id = :comment_id");
            $deleteStmt->execute([':comment_id' => $commentId]);

            // Set notifikasi sukses
            $_SESSION['notif'] = [
                'type' => 'success',
                'message' => 'Komentar berhasil dihapus!'
            ];
        } else {
            // Set notifikasi error
            $_SESSION['notif'] = [
                'type' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menghapus komentar ini.'
            ];
        }
    } else {
        // Set notifikasi error jika komentar atau resep tidak ditemukan
        $_SESSION['notif'] = [
            'type' => 'error',
            'message' => 'Komentar atau resep tidak ditemukan.'
        ];
    }
} else {
    // Set notifikasi error jika data tidak lengkap
    $_SESSION['notif'] = [
        'type' => 'error',
        'message' => 'Data tidak lengkap.'
    ];
}

// Kembali ke halaman resep
header("Location: /views/view_resep.php?id=" . $resepId);
exit;
?>
