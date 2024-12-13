<?php
include '../config/config.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Ambil daftar bookmark dari database
$stmt = $pdo->prepare("SELECT r.id, r.title, r.image_url FROM bookmarks b JOIN resep r ON b.resep_id = r.id WHERE b.user_id = :user_id");
$stmt->execute([':user_id' => $userId]);
$bookmarks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Bookmark</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Open+Sans:wght@300;400&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet"> <!-- AOS CSS -->
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
         /* Warna dasar dan hover pada navbar */
        .navbar {
            background-color: #F0902F;
            padding: 10px 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Styling untuk navbar-brand */
        .navbar-brand {
            color: #fff !important; /* Warna teks putih */
            background: linear-gradient(45deg, #ffdfb0, #fff); /* Gradien latar belakang */
            -webkit-background-clip: text; /* Memotong latar belakang untuk hanya muncul di teks (WebKit) */
            background-clip: text; /* Memotong latar belakang untuk teks (standar) */
            -webkit-text-fill-color: transparent; /* Mengisi warna teks dengan transparan */
            font-weight: bold; /* Teks tebal */
            font-size: 1.5rem; /* Ukuran font */
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2); /* Bayangan pada teks */
            transition: transform 0.3s ease, text-shadow 0.3s ease; /* Efek transisi */
        }

        .navbar-brand:hover {
            transform: scale(1.1);
            text-shadow: 2px 2px 8px rgba(255, 223, 176, 0.8);
        }

        /* Styling untuk nav-link dan nav-item */
        .nav-item {
            position: relative;
        }

        .nav-link {
            color: #fff !important;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
        }

        /* Hover effect untuk nav-link */
        .nav-link:hover {
            color: #F0902F !important;
            background-color: #ffdfb0;
            transform: scale(1.05); /* Sedikit membesar saat di-hover */
        }

        /* State aktif untuk nav-item */
        .nav-item.active .nav-link {
            background-color: #ffdfb0;
            color: #F0902F !important;
            font-weight: bold;
        }

        /* Style khusus untuk nav-link saat dihover */
        .nav-link::before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -4px;
            height: 2px;
            background-color: #ffdfb0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .nav-link:hover::before {
            opacity: 1;
        }

        /* Styling untuk navbar-toggler (hamburger icon) */
        .navbar-toggler {
            border-color: #fff;
            outline: none;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='rgba%28%255, %255, %255, 0.8%29' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        .navbar-toggler:hover .navbar-toggler-icon {
            opacity: 0.7;
        }

        /* Responsif untuk dropdown navbar saat dalam mode collapse */
        .collapse.show {
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            background-color: #F0902F;
            border-radius: 8px;
        }

        /* Tambahan margin untuk jarak antar link */
        .navbar-nav .nav-item {
            margin-left: 10px;
        }
       h2 {
            font-size: 2.5rem;
            color: #FE9933; /* Warna oranye cerah */
            font-family: 'Roboto', sans-serif;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f4f4f4;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .card.futuristic-card {
            background: #FE9933; /* Warna oranye cerah */
            border-radius: 15px;
            border: none;
            color: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        /* Gambar dalam kartu */
        .card-img-top {
            border-radius: 15px;
            max-height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .card-img-top:hover {
            transform: scale(1.1);
        }
        /* Bagian Isi Kartu */
        .card-body {
            padding: 20px;
            background: #FE9933;
            border-radius: 15px;
            transition: background 0.3s ease;
        }
    
        /* Judul Kartu */
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5); /* Menambahkan bayangan */
        }

        /* Tombol dengan desain futuristik */
        .futuristic-btn {
            padding: 12px 20px;
            font-size: 1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .futuristic-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* Tombol 'Hapus Bookmark' */
        .danger-btn {
            background: #FE9933; /* Warna oranye cerah */
            color: #fff;
            border: 2px solid #FE9933;
        }

        .danger-btn:hover {
            background: #f28700; /* Warna oranye lebih gelap saat hover */
            border-color: #f28700;
        }

        /* Tombol 'Lihat Detail Resep' */
        .info-btn {
            background: #fff;
            color: #FE9933; /* Warna oranye cerah untuk teks */
            border: 2px solid #FE9933;
        }

        .info-btn:hover {
            background: #FE9933;
            color: #fff;
            border-color: #FE9933;
        }

        .btn-secondary{
            background-color: #F0902F;
            border: none;
            border-radius: 50px; /* Membuat tombol lebih bulat */
            color: #fff;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s ease; /* Efek transisi */
            cursor: pointer; /* Mengubah kursor menjadi pointer */
            margin-left: 10px; /* Memberikan jarak antara input dan tombol */
        }

        .btn-secondary:hover {      
            background-color: #ff8c00;
            transform: translateY(-3px); /* Efek mengangkat tombol saat hover */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Efek bayangan halus saat hover */
        }

        .btn-secondary:focus {
            outline: none; /* Menghilangkan outline saat fokus */
            box-shadow: 0 0 10px rgba(240, 144, 47, 0.8); /* Efek fokus dengan bayangan */
        }

        /* Responsif untuk perangkat mobile */
        @media (max-width: 767px) {
            .card-img-top {
                max-height: 200px;
            }

            .card-body {
                padding: 15px;
            }

            .card-title {
                font-size: 1.2rem;
            }

            .futuristic-btn {
                font-size: 0.9rem;
                padding: 10px 18px;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php">ResepKita</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="resep.php">Resep</a></li>
                <li class="nav-item"><a class="nav-link" href="upload.php">Unggah Resep</a></li>
                
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item"><a class="nav-link" href="my_profile.php">Profil Saya</a></li>
                    <li class="nav-item"><a class="nav-link" href="actions/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
   <div class="container mt-5" data-aos="fade-up" data-aos-duration="2500">
    <h2 class="text-center mb-4">Daftar Bookmark</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Kembali ke Beranda</a>
    <div class="row">
        <?php if ($bookmarks): ?>
            <?php foreach ($bookmarks as $bookmark): ?>
                <div class="col-md-4 mb-4">
                    <div class="card futuristic-card">
                        <img src="<?php echo htmlspecialchars($bookmark['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($bookmark['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($bookmark['title']); ?></h5>
                            <div class="d-flex justify-content-between">
                                <!-- Ganti tombol dengan icon tong sampah -->
                                <a href="actions/bookmarks_action.php?action=remove&resid=<?php echo $bookmark['id']; ?>" class="btn futuristic-btn danger-btn">
                                    <i data-feather="trash-2"></i>
                                </a>
                                <a href="view_resep.php?id=<?php echo $bookmark['id']; ?>" class="btn futuristic-btn info-btn">Lihat Detail Resep</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Tidak ada resep yang dibookmark.</p>
        <?php endif; ?>
    </div>
</div>



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
  feather.replace();
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init();
    once: false;
</script>
</body>
</html>
