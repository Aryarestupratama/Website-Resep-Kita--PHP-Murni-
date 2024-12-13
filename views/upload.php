<?php
include '../config/config.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Proses form saat dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];
    $title = $_POST['title'];
    $ingredients = $_POST['ingredients'];
    $steps = $_POST['steps'];
    $imageUrl = ''; // Default image URL

    // Cek apakah ada file gambar yang diupload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/"; // Folder untuk menyimpan gambar
        $imageUrl = $targetDir . basename($_FILES['image']['name']);
        
        // Pindahkan file yang diupload ke folder yang ditentukan
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imageUrl)) {
            // Berhasil mengupload gambar
        } else {
            echo "Sorry, there was an error uploading your file.";
            exit;
        }
    }

    // Simpan resep ke database
    $stmt = $pdo->prepare("INSERT INTO resep (user_id, title, ingredients, steps, image_url, created_at) VALUES (:user_id, :title, :ingredients, :steps, :image_url, NOW())");
    $stmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':ingredients' => $ingredients,
        ':steps' => $steps,
        ':image_url' => $imageUrl
    ]);

    header("Location: resep.php"); // Redirect setelah sukses
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah Resep</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet"> <!-- AOS CSS -->
    <style>
        body {
            font-family: 'Noto Sans', serif;
            background-color: #fff;
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
        
        /* Container styling */
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); /* Soft shadow effect */
            padding: 40px;
            max-width: 600px;
            margin: auto;
        }

        /* Header Styling */
        h2 {
            color: #FE9933; /* Dominant color */
            font-weight: 600;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 40px;
            letter-spacing: 1px;
        }

        /* Button Styling */
        .btn-primary {
            background-color: #FE9933;
            border-color: #FE9933;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 30px;
            text-transform: uppercase;
            transition: all 0.3s ease-in-out;
            margin-top: 20px;
        }

        .btn-primary:hover {
            background-color: #e07e1a;
            border-color: #e07e1a;
            transform: translateY(-3px); /* Smooth hover effect */
        }

        .btn-secondary {
            color: #333;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: 500;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-secondary:hover {
            background-color: #e2e6ea;
            border-color: #ddd;
            transform: translateY(-3px); /* Smooth hover effect */
        }

        /* Form Input Styling */
        .form-control, .form-control-file {
            border-radius: 10px;
            box-shadow: none;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 12px 18px;
            font-size: 1rem;
            transition: all 0.3s ease-in-out;
        }

        /* Focus effect on input fields */
        .form-control:focus {
            border-color: #FE9933;
            box-shadow: 0 0 5px rgba(240, 144, 47, 0.6); /* Add glowing effect */
        }

        /* Label Styling */
        label {
            font-weight: 600;
            color: #333;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        /* Style for File Input */
        input[type="file"] {
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        /* Focus effect for file input */
        input[type="file"]:focus {
            border-color: #FE9933;
            box-shadow: 0 0 5px rgba(240, 144, 47, 0.6);
        }

        /* Responsive Design */
        @media (max-width: 767px) {
            .container {
                padding: 20px;
                max-width: 90%;
            }

            h2 {
                font-size: 1.6rem;
                margin-bottom: 20px;
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
    <div class="container mt-5" data-aos="fade-up" data-aos-duration="2000">
        <h2>Unggah Resep Baru</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Judul Resep:</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="ingredients">Bahan-bahan:</label>
                <textarea class="form-control" id="ingredients" name="ingredients" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="steps">Langkah-langkah:</label>
                <textarea class="form-control" id="steps" name="steps" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="image">Gambar Resep:</label>
                <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Unggah Resep</button>
        </form>
        <a  href="index.php" class="btn btn-primary">Kembali ke Dashboard</a>
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
