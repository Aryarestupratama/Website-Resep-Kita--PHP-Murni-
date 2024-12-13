<?php
    include '../config/config.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $resepId = $_GET['id'] ?? null;
    $userId = $_SESSION['user_id'];

    // Ambil detail resep berdasarkan resep_id
    $stmt = $pdo->prepare("SELECT title, ingredients, steps, image_url, user_id AS creator_id FROM resep WHERE id = :resep_id");
    $stmt->execute([':resep_id' => $resepId]);
    $resep = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resep) {
        echo "Resep tidak ditemukan.";
        exit;   
    }

    // Ambil username dari creator resep
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $resep['creator_id']]);
    $creator = $stmt->fetch(PDO::FETCH_ASSOC);

    // Hitung jumlah like untuk resep ini
    $likeStmt = $pdo->prepare("SELECT COUNT(*) as total_likes FROM likes WHERE resep_id = :resep_id");
    $likeStmt->execute([':resep_id' => $resepId]);
    $totalLikes = $likeStmt->fetch(PDO::FETCH_ASSOC)['total_likes'];

    // Cek apakah pengguna sudah memberikan "like" pada resep ini
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE resep_id = :resep_id AND user_id = :user_id");
    $stmt->execute([':resep_id' => $resepId, ':user_id' => $userId]);
    $userLiked = $stmt->fetchColumn() > 0;

    // Ambil semua komentar untuk resep ini
    $stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE resep_id = :resep_id ORDER BY comments.created_at DESC");
    $stmt->execute([':resep_id' => $resepId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($resep['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet"> <!-- AOS CSS -->

    <style>
        /* Warna dasar dan hover pada navbar */
        .navbar {
            background-color: #F0902F;
            padding: 10px 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

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
        body {
            font-family: 'Noto Sans', sans-serif;
            background-color: #fff;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #FE9933;
            padding: 10px 0;
            text-align: center;
            color: white;
        }

        .container {
            position: relative;
            margin-top: 50px;
            width: 90%;
            max-width: 1000px;
            height: 100%;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px; /* Menggunakan border-radius lebih besar */
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1); /* Bayangan lebih besar untuk efek 3D */
        }

        /* Judul resep */
        .resep-title {
            font-size: 2.8rem; /* Ukuran font lebih besar */
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
            color: #FE9933; /* Warna judul sesuai tema */
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Gambar resep */
        .resep-image {
            text-align: center;
            margin-bottom: 30px;
        }

        .resep-image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px; /* Menggunakan border-radius lebih halus */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Bayangan lebih halus untuk gambar */
            transition: transform 0.3s ease-in-out;
        }

        .resep-image img:hover {
            transform: scale(1.05); /* Efek gambar membesar saat hover */
        }

        .no-image {
            text-align: center;
            font-style: italic;
            color: #FE9933;
        }

        /* Bahan-bahan dan langkah-langkah */
        .ingredients-steps h2 {
            font-size: 1.75rem;
            color: #FE9933;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .ingredients, .steps {
            font-size: 1.2rem;
            line-height: 1.8;
            color: #333;
            margin-bottom: 20px;
        }

        /* Informasi pengunggah */
        .creator-info {
            font-size: 1.2rem;
            text-align: right;
            color: #FE9933; /* Sesuaikan dengan tema warna */
            margin-top: 30px;
            font-weight: 500;
        }

        .creator-info:hover {
            color: #F0902F; /* Warna lebih terang pada hover */
            cursor: pointer;
        }

        .btn {
            display: inline-block;
            background-color: #FE9933;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #f37c00;
        }

        .icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            position: absolute; bottom: 20px; 
            left: 20px;
        }
        
        .icon-wrapper .icon-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        /* Hover effect for comment button */
        .comment-button:hover {
            color: #FE9933; /* Warna hover sesuai tema */
            transform: scale(1.1); /* Membesar sedikit saat hover */
        }

        .like-button {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .like-button:hover {
            color: #FE9933; /* Warna hover untuk love button */
            transform: scale(1.1); /* Membesar saat hover */
        }

        .like-button.liked .icon {
            color: #FE9933; /* Warna saat sudah di-like */
        }

        .icon-wrapper .count {
            font-size: 1.1rem;
            color: #888;
            margin-left: 5px
        }

        .icon-item .icon {
            width: 20px;
            height: 20px;
        }

        .comments-container {
            display: none;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            max-width: 900px;
            margin: 20px auto;
        }

        .comments-container h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .comment {
            display: flex;
            align-items: flex-start;
           background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .comment:hover {
            background-color: #f0f0f0;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment strong {
            font-size: 1.1rem;
            font-weight: bold;
            margin-right: 8px;
            color: #333;
        }

        .comment p {
            font-size: 1rem;
            color: #555;
            line-height: 1.5;
        }

        .comment small {
            font-size: 0.85rem;
            color: #888;
            margin-top: 10px;
            display: block;
        }

        .btn-delete {
            background-color: #F0902F;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3 ease;
            font-weight: bold;
        }

        .btn-delete:hover {
            background-color: #e07a1c;
            transform: scale(1.05);
        }

        .btn-delete:active {
            transform: scale(0.98);
            background-color: #d6791a;
        }

        .comment-form form {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .comment-form textarea {
            width: 100%;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            resize: none;
            min-height: 100px;
        }

        .comment-form textarea:focus {
            outline: none;
            border-color: #FE9933;
            box-shadow: 0 0 8px rgba(255, 152, 51, 0.3);
        }

        .comment-form button {
            padding: 10px 20px;
            background-color: #FE9933;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .comment-form button:hover {
            background-color: #e07b2e; /* Warna lebih gelap saat hover */
            transform: scale(1.05); /* Efek memperbesar sedikit saat hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); /* Bayangan lebih besar saat hover */
        }

        .comment-form button:active {
            background-color: #c77a1e; /* Warna saat tombol ditekan */
            transform: scale(1); /* Mengembalikan skala normal saat ditekan */
        }

        /* Notifikasi */
        .notif-box {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateX(100%);
            transition: opacity 0.5s ease, transform 0.5s ease;
            z-index: 1000;
        }

        .notif-box.show {
            opacity: 1;
            transform: translateX(0);
        }

        .notif-box.success {
            background-color: #28a745; /* Hijau untuk sukses */
        }

        .notif-box.error {
            background-color: #dc3545; /* Merah untuk error */
        }

        .notif-box .icon {
            font-size: 20px;
        }
    </style>
</head>
<body>
<!-- Notifikasi -->
<?php
if (isset($_SESSION['notif'])):
    $notif = $_SESSION['notif'];
    $notifClass = $notif['type'] === 'success' ? 'success' : 'error';
?>

    <div class="notif-box <?= $notifClass ?>">
        <span class="icon">
            <?php if ($notif['type'] === 'success'): ?>
                &#10004; <!-- Ceklis -->
            <?php else: ?>
                &#9888; <!-- Tanda peringatan -->
             <?php endif; ?>
        </span>
        <span><?= htmlspecialchars($notif['message']) ?></span>
    </div>
    <?php
        unset($_SESSION['notif']);
    endif;
    ?>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php">ResepKita</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="upload.php">Unggah Resep</a></li>
                    <li class="nav-item"><a class="nav-link" href="bookmarks.php">Bookmark</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item"><a class="nav-link" href="my_profile.php">Profil Saya</a></li>
                    <li class="nav-item"><a class="nav-link" href="actions/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Daftar</a></li>
                <?php endif; ?>
                <!-- Button notifikasi -->
            </ul>
        </div>
    </nav>

    <div  class="container">
        <h1 class="resep-title"><?= htmlspecialchars($resep['title']) ?></h1>

        <!-- Tampilkan gambar resep jika ada -->
        <div class="resep-image">
            <?php if (!empty($resep['image_url'])): ?>
                <img src="<?= htmlspecialchars($resep['image_url']) ?>" alt="<?= htmlspecialchars($resep['title']) ?>" class="img-fluid">
            <?php else: ?>
                <p class="no-image">Tidak ada gambar</p>
            <?php endif; ?>
        </div>

        <div class="ingredients-steps">
            <h2>Bahan-bahan:</h2>
            <p class="ingredients"><?= nl2br(htmlspecialchars($resep['ingredients'])) ?></p>

            <h2>Langkah-langkah:</h2>
            <p class="steps"><?= nl2br(htmlspecialchars($resep['steps'])) ?></p>
        </div>

        <!-- Informasi Pengunggah -->
        <div class="creator-info">
            <p><strong>Diunggah oleh:</strong> <?= htmlspecialchars($creator['username']) ?></p>
        </div>

        <!-- Tombol Like -->
        <div class="icon-wrapper">
            <!-- Like Button -->
            <form action="actions/like_action.php" method="GET" style="display: inline;">
                <input type="hidden" name="resep_id" value="<?= $resepId ?>">
                <input type="hidden" name="action" value="<?= $userLiked ? 'unlike' : 'like' ?>">
                <button type="submit" class="icon-item like-button <?= $userLiked ? 'liked' : '' ?>">
                    <i data-feather="heart" class="icon"></i>
                    <div class="count"><?= $totalLikes ?></div>
                </button>
            </form>

            <!-- Comment Button -->
            <div class="icon-item comment-button" onclick="toggleComments()">
                <i data-feather="message-square" class="icon"></i>
                <div class="count"><?= count($comments) ?></div>
            </div>
        </div>


    <div class="comments-container">
        <h2>Komentar</h2>
        <?php if (count($comments) > 0): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    
                    <div class="comment-content">
                        <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                        <p><?= htmlspecialchars($comment['comment']) ?></p>
                        <small><?= $comment['created_at'] ?></small>

                        <!-- Cek apakah user saat ini adalah pemilik komentar atau pemilik postingan -->
                        <?php if ($comment['user_id'] == $userId || $resep['creator_id'] == $userId): ?>
                        <form id="deleteForm" action="actions/delete_comment_action.php" method="POST" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                            <input type="hidden" name="resep_id" value="<?= $resepId ?>">
                            <button type="submit" class="btn-delete"  id="deleteButton">Hapus</button>
                        </form>
        <?php endif; ?>
    </div>
</div>
        <?php endforeach; ?>
        <?php else: ?>
            <p>Tidak ada komentar untuk resep ini.</p>
        <?php endif; ?>

        <div class="comment-form">
            <form action="actions/comment_action.php" method="POST" style="display: flex; align-items: center; width: 100%;">
                <textarea name="comment" required placeholder="Tulis komentar..."></textarea>
                <input type="hidden" name="resep_id" value="<?= $resepId ?>">
                <button type="submit">Kirim</button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleComments() {
        const commentsContainer = document.querySelector('.comments-container');
        commentsContainer.style.display = (commentsContainer.style.display === 'none' || commentsContainer.style.display === '') ? 'block' : 'none';
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notifBox = document.querySelector('.notif-box');
        if (notifBox) {
            // Tambahkan kelas 'show' untuk memulai animasi
            setTimeout(() => {
                notifBox.classList.add('show');
            }, 100); // Delay kecil untuk memastikan animasi berjalan

            // Hapus notifikasi setelah 4 detik
            setTimeout(() => {
                notifBox.classList.remove('show');
                // Hapus elemen notifikasi setelah animasi selesai
                setTimeout(() => {
                    notifBox.remove();
                }, 500); // Sesuaikan dengan durasi animasi CSS
            }, 4000);
        }
    });
</script>

<script>
    document.querySelector('#deleteButton').addEventListener('click', function (event) {
    event.preventDefault(); // Mencegah submit form langsung
    
    Swal.fire({
        title: 'Anda yakin ingin menghapus komentar ini?',
        text: "Tindakan ini tidak dapat dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit form atau lakukan tindakan penghapusan
            document.querySelector('#deleteForm').submit(); // Gantilah '#deleteForm' dengan ID form yang sesuai
        }
    });
});

</script>
<script>
    feather.replace(); // Menginisialisasi Feather Icons
</script>
<script>
    AOS.init();
    once: false;
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
</body>
</html>
