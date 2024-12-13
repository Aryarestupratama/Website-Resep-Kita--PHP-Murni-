<?php
    include '../config/config.php';

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        header("Location: admin_login.php");
        exit;
    }

    // Mengambil informasi pengguna dari database
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users");
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_resep FROM resep");
    $stmt->execute();
    $total_recipes = $stmt->fetch(PDO::FETCH_ASSOC)['total_resep'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans', sans-serif;
            background-color: #f5f5f5;
        }
        /* Navbar */
        .navbar {
            background-color: #FE9933;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Menambahkan bayangan untuk kedalaman */
            padding: 15px 20px; /* Memberikan padding lebih pada navbar */
            transition: background-color 0.3s ease; /* Efek transisi halus saat hover */
        }

        .navbar:hover {
            background-color: #e07d29; /* Warna navbar berubah saat hover */
        }

        .navbar-brand {
            color: white;
            font-weight: bold;
            font-size: 1.8rem; /* Ukuran font lebih besar untuk brand */
            letter-spacing: 2px; /* Memberikan jarak antar huruf untuk kesan lebih elegan */
            transition: color 0.3s ease, transform 0.3s ease; /* Efek transisi untuk hover */
        }

        .navbar-brand:hover {
            color: #fff5e6; /* Efek warna lebih terang pada hover */
            transform: scale(1.1); /* Efek pembesaran halus pada hover */
        }

        /* Navbar link default */
        .nav-link {
            color: white;
            font-weight: bold;
            font-size: 1.1rem; /* Ukuran font sedikit lebih besar untuk tampilan yang lebih menonjol */
            padding: 10px 15px; /* Memberikan padding lebih pada link untuk area klik lebih besar */
            transition: color 0.4s ease, transform 0.3s ease, padding 0.3s ease, text-shadow 0.3s ease;
            position: relative;
        }

        /* Hover efek pada navbar link */
        .nav-link:hover {
            color: #f2f2f2; /* Warna saat hover lebih terang untuk kontras */
            padding-left: 20px; /* Efek pergeseran padding */
            transform: translateX(5px); /* Efek pergeseran link ke kanan saat hover */
            font-weight: 600; /* Meningkatkan ketebalan font */
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Memberikan bayangan halus pada teks */
        }

        /* Menambahkan garis bawah halus saat hover */
        .nav-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 2px;
            background-color: #f2f2f2; /* Warna garis bawah saat hover agar kontras dengan latar belakang */
            transform: scaleX(0);
            transform-origin: bottom right;
            transition: transform 0.3s ease-out;
        }

        /* Efek garis bawah muncul saat hover */
        .nav-link:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }


        /* Responsiveness untuk navbar */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.5rem; /* Ukuran font lebih kecil pada perangkat mobile */
            }

            .navbar-nav .nav-link {
                font-size: 1rem; /* Menurunkan ukuran font link pada perangkat mobile */
            }
        }

        /* Card styling */
        .card {
            border: none;
            border-radius: 15px; /* Lebih rounded untuk tampilan yang lebih modern */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            background: linear-gradient(145deg, #ffffff, #f4f4f4); /* Gradient ringan untuk efek modern */
        }

        .card:hover {
            transform: translateY(-8px); /* Efek pergeseran lebih dinamis */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* Bayangan lebih dalam dan lebih tajam */
            background-color: #f8f8f8; /* Warna lebih terang pada hover */
        }

        /* Card header */
        .card-header {
            background-color: #FE9933; /* Warna oranye lebih tajam */
            color: white;
            font-weight: 700;
            text-transform: uppercase; /* Untuk kesan lebih profesional */
            letter-spacing: 1px; /* Spasi huruf agar terlihat lebih modern */
            padding: 15px 20px; /* Padding lebih besar untuk estetika */
        }

        /* Card title */
        .card-title {
            font-size: 2rem; /* Menyesuaikan ukuran font agar lebih seimbang */
            font-weight: bold;
            color: #333;
            transition: color 0.3s ease;
        }

        .card-title:hover {
            color: #FE9933; /* Efek hover untuk judul */
        }

        /* List group styling */
        .list-group-item {
            border-radius: 10px;
            padding: 12px 20px; /* Penambahan padding untuk kenyamanan */
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .list-group-item a {
            color: #FE9933;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .list-group-item a:hover {
            color: #ffffff; /* Teks menjadi putih saat hover */
            transform: translateX(5px); /* Efek geser ringan ke kanan */
        }

        .list-group-item:hover {
            background-color: #f8f8f8; /* Warna latar belakang sedikit lebih terang */
            transform: translateX(5px); /* Efek geser horizontal ringan */
        }

        /* Styling untuk hover animasi di daftar */
        .list-group-item:hover a {
            color: #FE9933; /* Warna teks saat item hover */
            text-decoration: underline; /* Garis bawah lebih modern saat hover */
        }

        .container {
            max-width: 1100px;
        }

        /* Styling untuk Dashboard Admin Heading */
        .admin-heading {
            font-size: 2.5rem; /* Ukuran font yang lebih besar */
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center; /* Menyelaraskan ke tengah */
            text-transform: uppercase; /* Huruf kapital untuk kesan lebih profesional */
            letter-spacing: 2px; /* Memberikan spasi huruf yang lebih lebar */
            position: relative;
        }

        .admin-heading::after {
            content: "";
            position: absolute;
            width: 50%;
            height: 3px;
            background-color: #FE9933; /* Warna garis bawah yang sesuai */
            bottom: -10px;
            left: 25%; /* Posisikan garis tepat di tengah */
        }

        /* Styling untuk Menu Admin Heading */
        .menu-heading {
            font-size: 1.8rem; /* Ukuran font lebih kecil daripada Dashboard */
            font-weight: 600;
            color: #FE9933; /* Warna oranye untuk kesan tajam dan modern */
            margin-top: 40px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px; /* Spasi huruf lebih rapat */
            position: relative;
        }

        .menu-heading::after {
            content: "";
            position: absolute;
            width: 30%;
            height: 2px;
            background-color: #FE9933;
            bottom: -5px;
            left: 35%; /* Menyelaraskan garis bawah di tengah */
        }

        /* Jika ingin menambahkan animasi atau transisi */
        .admin-heading,
        .menu-heading {
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .admin-heading:hover,
        .menu-heading:hover {
            color: #e07d29; /* Memberikan efek perubahan warna saat hover */
            transform: translateY(-3px); /* Efek geser sedikit ke atas */
        }

    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand">ResepKita</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="actions/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="admin-heading">Dashboard Admin</h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-white text-center">
                    <div class="card-header">Total Pengguna</div>
                    <div class="card-body">
                        <h5 class="card-title text-dark"><?php echo $total_users; ?></h5>
                        <p class="card-text text-muted">Jumlah pengguna terdaftar di sistem.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-white text-center">
                    <div class="card-header">Total Resep</div>
                    <div class="card-body">
                        <h5 class="card-title text-dark"><?php echo $total_recipes; ?></h5>
                        <p class="card-text text-muted">Jumlah resep yang telah ditambahkan ke sistem.</p>
                    </div>
                </div>
            </div>
        </div>

        <h4 class="menu-heading">Menu Admin</h4>
        <ul class="list-group mt-3">
            <li class="list-group-item"><a href="manage_users.php">Kelola Pengguna</a></li>
            <li class="list-group-item"><a href="manage_recipes.php">Kelola Resep</a></li>
            <li class="list-group-item"><a href="view_feedback.php">Tampilkan Umpan Balik</a></li>
        </ul>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
