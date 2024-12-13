<?php
    include '../config/config.php';

    // Cek apakah pengguna sudah login
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    
    // Ambil semua resep dari database untuk user yang login
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM resep WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resep Anda</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet"> <!-- AOS CSS -->
</head>
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Noto Sans', serif;
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

        .container {
            max-width: 1200px;
            margin-top: 30px;
        }

        /* Container untuk logo */
        .upload-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100px;
        }

        /* Desain logo dengan border lingkaran */
        .logo {
            background-color: #FE9933;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            padding: 15px 30px;
            border-radius: 10px; /* Membuat border lingkaran */
            border: 3px solid #FE9933; /* Border dengan warna dominan */
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px; /* Jarak antara ikon dan teks */
            transition: all 0.3s ease-in-out; /* Efek transisi hover */
        }

        /* Efek hover pada logo */
        .logo:hover {
            background-color: #ffffff; /* Ganti warna latar belakang saat hover */
            color: #FE9933; /* Ganti warna teks dan ikon saat hover */
            border-color: #ffffff; /* Ganti warna border saat hover */
            transform: scale(1.1); /* Efek membesar saat hover */
        }

        /* Styling ikon */
        .logo i {
            font-size: 1.5rem; /* Ukuran ikon */
        }

        /* Styling teks di dalam logo */
        .logo span {
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        a.logo-link {
            text-decoration: none; /* Menghilangkan garis bawah pada link */
        }

        h1 {
            color: #FE9933; /* Warna oranye terang */
            margin-bottom: 20px;
            font-family: 'Noto Sans', sans-serif;
            font-weight: bold;
            text-align: center;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            max-height: 400px; /* Sesuaikan dengan kebutuhan */
            display: flex;
            flex-direction: column;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .card-title {
            color: #FE9933; /* Warna oranye terang */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 1.2rem; /* Menambah ukuran font */
            font-weight: bold;
            font-family: 'Poppins', sans-serif;
        }

        .card-text {
            max-height: 60px; /* Sesuaikan agar semua teks tidak terlalu tinggi */
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            border-radius: 10px 10px 0 0;
            height: 200px;
            object-fit: cover;
        }
        .d-flex {
            display: flex;
            
            gap: 10px; /* Menambahkan jarak kecil antara tombol */
            width: 100%;
            
        }
        .btn-sm {
            font-size: 0.875rem;
            padding: 6px 12px;
        }

        /* Ikon tong sampah */
        .btn-icon {
            border-radius: 50%;
            padding: 10px;
            background-color: white;
            border: 2px solid #FF4C4C; /* Border merah terang */
            color: #FF4C4C; /* Teks/ikon berwarna merah */
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40px; /* Ukuran tombol */
            height: 40px; /* Ukuran tombol */
            transition: background-color 0.3s, color 0.3s;
        }

        /* Efek hover untuk tombol dengan ikon */
        .btn-icon:hover {
            background-color: #FF4C4C; /* Latar belakang berubah menjadi merah */
            color: white; /* Ikon menjadi putih */
        }


        .btn-primary {
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

        .btn-primary:hover {      
            background-color: #ff8c00;
            transform: translateY(-3px); /* Efek mengangkat tombol saat hover */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Efek bayangan halus saat hover */
        }

        .btn-primary:focus {
            outline: none; /* Menghilangkan outline saat fokus */
            box-shadow: 0 0 10px rgba(240, 144, 47, 0.8); /* Efek fokus dengan bayangan */
        }

        /* Mengatur gaya untuk tombol Tampilkan Detail */
        .btn-outline-primary {
            border-radius: 50px;
            background-color: white;
            border-color: #F0902F; /* Warna biru */
            color: #F0902F; /* Warna teks biru */
            padding: 6px 20px;
            align-items: center;
        }

        /* Hover untuk tombol Tampilkan Detail */
        .btn-outline-primary:hover {
            background-color: #F0902F;
            color: white;
            border-color:#F0902F;
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
            <li class="nav-item"><a class="nav-link" href="bookmarks.php">Bookmark</a></li>
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

<div class="container">
     <a data-aos="fade-up" data-aos-duration="2000" href="index.php" class="btn btn-primary">Kembali ke Dashboard</a>
    <h1 data-aos="fade-up" data-aos-duration="2000" >Resep Anda</h1>

    <div class="upload-container" data-aos="fade-up" data-aos-duration="2500">
        <a href="upload.php" class="logo-link"> <!-- Ganti 'halaman_tujuan.php' dengan URL yang diinginkan -->
            <div class="logo">
                <i class="fas fa-plus"></i> <!-- Ikon Plus -->
                <span>Upload Resep</span>
            </div>
        </a>
    </div>
    <div class="row">
        <?php if (count($recipes) > 0): ?>
            <?php foreach ($recipes as $recipe): ?>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-duration="3000">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class=" card-img-top" onerror="this.onerror=null; this.src='default-image.jpg';">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h5>
                           <div class="d-flex justify-content-between align-items-center">
                                <!-- Button Tampilkan Detail Resep -->
                                <a href="view_resep.php?id=<?php echo $recipe['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> Tampilkan Detail
                                </a>

                                <!-- Form Hapus dengan Ikon Tong Sampah -->
                                <form action="actions/delete_resep_action.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="resep_id" value="<?php echo $recipe['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-icon" onclick="return confirm('Anda yakin ingin menghapus resep ini?');">
                                        <i class="fas fa-trash"></i> <!-- Ikon tong sampah -->
                                    </button>
                                </form>
                            </div>                          
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p>Tidak ada resep ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>
       
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init();
    once: false;
</script>
</body>
</html>