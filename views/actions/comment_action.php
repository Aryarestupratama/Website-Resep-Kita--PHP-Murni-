<?php
    session_start(); // Pastikan session_start() hanya dipanggil sekali di awal skrip
    include '../../config/config.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $resepId = $_POST['resep_id'] ?? null;
    $comment = $_POST['comment'] ?? '';
    $userId = $_SESSION['user_id'];

    if ($resepId && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO comments (resep_id, user_id, comment) VALUES (:resep_id, :user_id, :comment)");
        $stmt->execute([':resep_id' => $resepId, ':user_id' => $userId, ':comment' => $comment]);
    }


    header("Location: /views/view_resep.php?id=" . $resepId);
    exit;
?>
