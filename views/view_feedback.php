<?php

include '../config/config.php'; // Pastikan jalur ini benar

// Periksa jika admin sudah login
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit;
}

// Ambil data likes dan comments untuk setiap resep
$stmt = $pdo->prepare("
    SELECT likes.resep_id, likes.user_id, likes.created_at AS like_time, 
           comments.comment, comments.created_at AS comment_time, 
           users.username, users.email, resep.title AS resep_title
    FROM likes
    LEFT JOIN comments ON likes.resep_id = comments.resep_id AND likes.user_id = comments.user_id
    LEFT JOIN users ON likes.user_id = users.id
    LEFT JOIN resep ON likes.resep_id = resep.id
");
$stmt->execute();
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umpan Balik Pengguna</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Sidebar */
.sidebar {
    background-color: #333;
    color: white;
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding-top: 30px;
    transition: transform 0.3s ease-in-out;
}

/* Sidebar Header */
.sidebar h3 {
    font-size: 1.8rem;
    font-weight: bold;
    color: #FE9933;
    margin-bottom: 40px;
    text-align: center;
    letter-spacing: 1px;
    animation: flash 1.5s infinite alternate; /* Animasi kelap-kelip */
    text-shadow: 0 0 10px rgba(255, 153, 51, 1), 0 0 20px rgba(255, 153, 51, 0.8), 0 0 30px rgba(255, 153, 51, 0.6); /* Efek glow */
    transition: color 0.3s ease;
}

.sidebar h3:hover {
    color: #e07d29;
    text-shadow: 0 0 15px #e07d29, 0 0 30px #e07d29, 0 0 50px #e07d29; /* Lebih terang saat hover */
}

/* Definisikan animasi kelap-kelip */
@keyframes flash {
    0% {
        color: #FE9933;
        text-shadow: 0 0 10px rgba(255, 153, 51, 1), 0 0 20px rgba(255, 153, 51, 0.8), 0 0 30px rgba(255, 153, 51, 0.6);
    }
    50% {
        color: #FFD700; /* Warna kuning lebih terang */
        text-shadow: 0 0 15px rgba(255, 215, 0, 1), 0 0 30px rgba(255, 215, 0, 0.8), 0 0 50px rgba(255, 215, 0, 0.6);
    }
    100% {
        color: #FE9933;
        text-shadow: 0 0 10px rgba(255, 153, 51, 1), 0 0 20px rgba(255, 153, 51, 0.8), 0 0 30px rgba(255, 153, 51, 0.6);
    }
}




.sidebar a {
    color: #fff;
    font-size: 1.1rem;
    text-decoration: none;
    padding: 15px 25px;
    display: block;
    transition: background-color 0.3s ease, padding-left 0.3s ease;
}

.sidebar a:hover {
    background-color: #FE9933;
    padding-left: 30px;
}

/* Main Content */
.main-content {
    margin-left: 260px;
    padding: 30px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
}

.page-title::after {
    content: "";
    position: absolute;
    width: 50%;
    height: 3px;
    background-color: #FE9933;
    bottom: -10px;
    left: 25%;
}

/* Table Styling */
.table {
    width: 100%;
    margin-top: 30px;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 15px;
    text-align: left;
    vertical-align: middle;
    font-size: 1rem;
}

.table th {
    background-color: #FE9933;
    color: white;
    font-weight: bold;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f9f9f9;
}

.table-hover tbody tr:hover {
    background-color: #f1f1f1;
    transition: background-color 0.3s ease;
}

.table-row {
    transition: transform 0.3s ease;
}

.table-row:hover {
    transform: scale(1.02); /* Efek perbesaran sedikit saat hover */
}

/* Button Back */
.btn-back {
    background-color: #FE9933;
    color: white;
    padding: 10px 20px;
    font-weight: bold;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.btn-back:hover {
    background-color: #e07d29;
}

/* General Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
    }
    
    .main-content {
        margin-left: 210px;
    }

    .table th, .table td {
        font-size: 0.9rem;
    }
}

    </style>
</head>
<body>

    <!-- Sidebar -->
<div class="sidebar">
    <h3 class="text-center text-white">ResepKita</h3>
    
    <a href="manage_users.php">Kelola Pengguna</a>
    <a href="manage_recipes.php">Kelola Resep</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="container">
        <h2 class="page-title">Umpan Balik Pengguna</h2>
        <a href="admin_dashboard.php" class="btn btn-back mb-3">Kembali ke Dashboard</a>

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Judul Resep</th>
                    <th>Nama Pengguna</th>
                    <th>Email Pengguna</th>
                    <th>Waktu Like</th>
                    <th>Komentar</th>
                    <th>Waktu Komentar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $feedback): ?>
                    <tr class="table-row">
                        <td><?php echo htmlspecialchars($feedback['resep_title']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['username']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['email']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['like_time']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['comment'] ?? 'Tidak ada komentar'); ?></td>
                        <td><?php echo htmlspecialchars($feedback['comment_time'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
