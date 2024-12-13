<?php
// Memulai sesi
include '../config/config.php'; // Pastikan jalur ini benar

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil informasi pengguna dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika pengguna tidak ditemukan, redirect ke halaman login
if (!$user) {
    header("Location: login.php");
    exit;
}

// Inisialisasi variabel untuk pesan kesalahan dan sukses
$errors = [];
$success_message = '';

// Proses perubahan profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);

    // Validasi input
    if (empty($new_username) || empty($new_email)) {
        $errors[] = "Username dan email tidak boleh kosong.";
    } else {
        // Update informasi pengguna
        $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        $stmt->execute(['username' => $new_username, 'email' => $new_email, 'id' => $_SESSION['user_id']]);
        $_SESSION['username'] = $new_username; // Update session username
        $success_message = "Profil berhasil diperbarui.";
        // Redirect setelah berhasil
        header("Refresh: 2; url=my_profile.php"); // Refresh setelah 2 detik
        exit;
    }
}

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi password
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errors[] = "Semua field password harus diisi.";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "Password baru dan konfirmasi password tidak cocok.";
    } else {
        // Verifikasi password saat ini
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute(['password' => $hashed_password, 'id' => $_SESSION['user_id']]);
            $success_message = "Password berhasil diubah.";
        } else {
            $errors[] = "Password saat ini salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
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
        /* Styling Umum untuk Container */
body {
    font-family: 'Noto Sans', sans-serif;
    background-color: #f4f4f4; /* Latar belakang halaman */
    color: #333;
}

.container {
    margin-top: 40px;
    padding: 30px;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

.container:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}

.section-title {
    font-size: 1.8rem;
    color: #F0902F;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.card {
    background-color: #fff;
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.card-title {
    font-size: 1.5rem;
    color: #F0902F;
    margin-bottom: 15px;
}

/* Menambahkan gaya untuk tombol toggle */
/* Tombol Toggle */
.toggle-form {
    cursor: pointer;
    color: #fff; /* Warna teks putih */
    text-decoration: none; /* Menghilangkan garis bawah */
    font-size: 1rem;
    font-weight: 600;
    padding: 12px 25px; /* Padding tombol */
    background-color: #F0902F; /* Warna latar belakang oranye */
    border-radius: 30px; /* Sudut melengkung lebih besar untuk tombol */
    text-align: center; /* Menengahkan teks */
    transition: all 0.3s ease; /* Transisi semua properti dengan efek halus */
    display: inline-block; /* Agar tombol dapat berbaris dengan elemen lain */
    border: none; /* Menghilangkan border default */
}

/* Efek hover saat cursor berada di atas tombol toggle */
.toggle-form:hover {
    color: #fff; /* Menjaga warna teks tetap putih saat hover */
    background-color: #d6791a; /* Warna latar belakang berubah lebih gelap */
    transform: translateY(-4px); /* Mengangkat tombol saat hover */
    opacity: 0.9; /* Sedikit transparan saat hover */
}

/* Efek saat tombol toggle difokuskan (misal setelah diklik) */
.toggle-form:focus {
    outline: none; /* Menghilangkan outline default */
    color: #fff; /* Menjaga warna teks tetap putih saat difokuskan */
    background-color: #b5671d; /* Warna latar belakang lebih gelap saat fokus */
    box-shadow: 0 0 8px rgba(255, 152, 51, 0.5); /* Bayangan lembut saat fokus */
}

/* Efek saat tombol toggle dalam keadaan aktif (terklik) */
.toggle-form:active {
    background-color: #d6791a; /* Warna latar belakang tetap gelap saat aktif */
    transform: translateY(1px); /* Efek menekan tombol */
}



.hidden-form {
    display: none;
    margin-top: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    border: 1px solid #ddd;
    border-radius: 5px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #F0902F;
    box-shadow: 0 0 5px rgba(255, 152, 51, 0.5);
}

/* Menambahkan penataan lebih lanjut untuk tombol dengan class .btn-primary */
.btn-primary {
    background-color: #F0902F;
    border: none;
    padding: 12px 20px;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: bold;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease, opacity 0.3s ease;
    text-decoration: none;
}

/* Efek hover saat cursor berada di atas tombol */
.btn-primary:hover {
    background-color: #d6791a;
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    opacity: 0.9;
}

/* Efek saat tombol ditekan */
.btn-primary:active {
    transform: translateY(2px) scale(1);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    opacity: 1;
}

/* Efek saat tombol difokuskan (misal setelah diklik) */
.btn-primary:focus {
    outline: none;
    box-shadow: 0 0 10px rgba(255, 152, 51, 0.5);
}

.alert {
    border-radius: 5px;
    margin-bottom: 20px;
    transition: opacity 0.3s ease;
}

.alert-danger {
    background-color: rgba(255, 0, 0, 0.1);
    border: 1px solid #ff0000;
}

.alert-success {
    background-color: rgba(0, 255, 0, 0.1);
    border: 1px solid #00ff00;
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
                    <li class="nav-item"><a class="nav-link" href="bookmarks.php">Bookmark</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                
                    <li class="nav-item"><a class="nav-link" href="actions/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Daftar</a></li>
                <?php endif; ?>
                <!-- Button notifikasi -->
            </ul>
        </div>
    </nav>

<div class="container mt-4">
    <h2 class="section-title">Profil Saya</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <div class="card">
        <h5 class="card-title">Informasi Pengguna</h5>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Terdaftar pada:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
    </div>

    <div class="mb-4">
        <h4 class="section-title">Ubah Profil</h4>
        <p class="toggle-form" id="toggle-profile-form">Ubah profil saya</p>
        <form method="POST" id="profile-form" class="hidden-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
 </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary" name="update_profile">Simpan Perubahan</button>
        </form>
    </div>

    <div class="mb-4">
        <h4 class="section-title">Ganti Password</h4>
        <p class="toggle-form" id="toggle-password-form">Ganti password saya</p>
        <form method="POST" id="password-form" class="hidden-form">
            <div class="form-group">
                <label for="current_password">Password Saat Ini</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password Baru</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary" name="change_password">Ubah Password</button>
        </form>
    </div>
</div>  

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Toggle form visibility
    document.getElementById('toggle-profile-form').addEventListener('click', function() {
        document.getElementById('profile-form').classList.toggle('hidden-form');
    });
    document.getElementById('toggle-password-form').addEventListener('click', function() {
        document.getElementById('password-form').classList.toggle('hidden-form');
    });
</script>
</body>
</html>