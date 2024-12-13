<?php
// Memulai session untuk mengelola login, notifikasi, dan interaksi antar pengguna
session_start();

// Konfigurasi koneksi database
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'resepkita_db',
    'username' => 'root',
    'password' => '',
];

// Mencoba koneksi ke database dengan PDO
try {
    $pdo = new PDO("mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'] . ";charset=utf8", $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Mode error untuk debugging
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage()); // Menampilkan pesan error jika koneksi gagal
}

// Base URL untuk URL yang konsisten di seluruh aplikasi
define('BASE_URL', 'http://localhost/resepkita/');

// Fungsi untuk mengarahkan pengguna ke halaman lain dengan cepat
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}

// Fungsi untuk memeriksa apakah pengguna sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mendapatkan ID pengguna yang sedang login
function getLoggedInUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Fungsi untuk menambah bookmark resep ke dalam "playlist" pengguna
function addBookmark($userId, $resepId) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO bookmarks (user_id, resep_id) VALUES (:user_id, :resep_id)");
    $stmt->execute([':user_id' => $userId, ':resep_id' => $resepId]);
}

// Fungsi untuk mendapatkan bookmark pengguna
function getUserBookmarks($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT r.* FROM bookmarks b JOIN resep r ON b.resep_id = r.id WHERE b.user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mencari resep berdasarkan kata kunci
function searchResep($keyword) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM resep WHERE title LIKE :keyword OR ingredients LIKE :keyword OR steps LIKE :keyword");
    $stmt->execute([':keyword' => '%' . $keyword . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
