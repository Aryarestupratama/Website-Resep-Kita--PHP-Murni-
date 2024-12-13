<?php
session_start();
include '../config/config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $ingredients = $_POST['ingredients'];
    $steps = $_POST['steps'];

    // Proses upload gambar
    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $fileName = uniqid() . '-' . basename($file['name']); // Buat nama file unik
        $targetDirectory = '../uploads/';
        $targetFile = $targetDirectory . $fileName;

        // Memindahkan file gambar ke direktori uploads
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Siapkan dan eksekusi query untuk menyimpan resep
            $stmt = $pdo->prepare("INSERT INTO resep (user_id, title, ingredients, steps, image_url, created_at) VALUES (:user_id, :title, :ingredients, :steps, :image_url, NOW())");
            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'title' => $title,
                'ingredients' => $ingredients,
                'steps' => $steps,
                'image_url' => $targetFile // Simpan URL gambar
            ]);

            // Redirect ke halaman resep setelah berhasil di-upload
            header("Location: ../index.php");
            exit;
        } else {
            echo "Maaf, terjadi kesalahan saat mengunggah gambar.";
        }
    } else {
        echo "Gambar tidak ditemukan.";
    }
} else {
    echo "Metode permintaan tidak valid.";
}
?>
