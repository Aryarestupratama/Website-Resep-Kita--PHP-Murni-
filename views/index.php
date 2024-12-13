<?php
    include '../config/config.php';
// Mulai sesi jika belum dimulai
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Mengambil semua resep dari database untuk ditampilkan di halaman utama
    // Ambil kata kunci pencarian dari query string
$searchKeyword = $_GET['search'] ?? '';

// Jika ada kata kunci pencarian, lakukan pencarian resep
if ($searchKeyword) {
    // Lakukan pencarian resep
    $stmt = $pdo->prepare("SELECT resep.*, users.username 
                            FROM resep 
                            JOIN users ON resep.user_id = users.id 
                            WHERE resep.title LIKE :search 
                            ORDER BY resep.created_at DESC");
    $stmt->execute(['search' => '%' . $searchKeyword . '%']);
    $resepList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Simpan pencarian ke dalam riwayat
    $userId = $_SESSION['user_id']; // Pastikan Anda sudah menyimpan ID pengguna di session

    // Cek apakah pencarian sudah ada di riwayat
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM search_history WHERE user_id = ? AND search_term = ?");
    $stmt->execute([$userId, $searchKeyword]);
    $exists = $stmt->fetchColumn() > 0;

    // Jika tidak ada, simpan pencarian ke dalam riwayat
    if (!$exists) {
        $stmt = $pdo->prepare("INSERT INTO search_history (user_id, search_term) VALUES (?, ?)");
        $stmt->execute([$userId, $searchKeyword]);
    }
} else {
    // Jika tidak ada kata kunci pencarian, ambil semua resep dari database
    $stmt = $pdo->query("SELECT resep.*, users.username 
                         FROM resep 
                         JOIN users ON resep.user_id = users.id 
                         ORDER BY resep.created_at DESC");
    $resepList = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/// Ambil riwayat pencarian untuk ditampilkan
    $userId = $_SESSION['user_id'] ?? null; // Menggunakan null jika tidak ada user_id
    if ($userId !== null) { // Pastikan userId ada
        $stmt = $pdo->prepare("SELECT search_term FROM search_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$userId]);
        $searchHistory = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $searchHistory = []; // Jika tidak ada user_id, set searchHistory menjadi array kosong
    }
?>


    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ResepKita - Temukan Resep Favoritmu</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet"> <!-- AOS CSS -->
        <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&display=swap" rel="stylesheet">
        <script src="https://unpkg.com/feather-icons"></script>

        <style>

    

            body {
                font-family: 'Poppins', sans-serif; 
                padding: 10px 20px;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            }

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

            /* Input Form-Control */
            .form-control {
                border-radius: 50px;
                padding: 10px 20px; /* Ruang di dalam input */
                width: 60%; /* Membatasi lebar input */
                max-width: 500px; /* Batas lebar maksimal */
                border: 1px solid #ccc; /* Border lembut */
                background-color: #fff; /* Latar belakang putih */
                transition: all 0.3s ease-in-out; /* Efek transisi */
                font-size: 1rem; /* Ukuran teks nyaman */
                color: #333; /* Warna teks default */
            }

            .form-control:focus {
                border-color: #F0902F;
                box-shadow: 0px 0px 8px rgba(240, 144, 47, 0.6);
                background-color: #fff;
                outline: none;
                transform: scale(1.02); /* Efek perbesar saat fokus */
            }

            .form-control::placeholder {
                color: #aaa;
                font-style: italic;
                transition: color 0.3s ease-in-out;
            }

            .form-control:focus::placeholder {
                color: #F0902F;
                font-style: normal;
            }

            .form-control:hover {
                border-color: #F0902F;
                background-color: #fef9f4;
            }

            /* Search History */
            .search-history {
                position: absolute; /* Posisi absolut untuk menempel ke bawah form */ /* Tepat di bawah form */
                left: 50%; /* Pusatkan horizontal */
                transform: translateX(-50%); /* Koreksi posisi */
                z-index: 10; /* Pastikan di atas elemen lainnya */
                background-color: white; /* Latar belakang putih */
                border-radius: 10px; /* Sudut membulat */
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Bayangan lembut */
                width: 60%; /* Sesuai dengan input */
                max-width: 500px; /* Batasan lebar maksimal */
                padding: 10px; /* Jarak dalam */
                opacity: 0;
                visibility: hidden; /* Sembunyikan awalnya */
                transition: all 0.3s ease-in-out; /* Efek transisi */
            }

            .search-history.show {
                opacity: 1; /* Tampilkan */
                visibility: visible; /* Buat terlihat */
            }

            .search-history h5 {
                margin: 0 0 10px;
                color: #F0902F;
                font-weight: bold;
            }

            .search-history ul {
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .search-history .list-group-item {
                border: none;
                padding: 8px 15px;
                border-radius: 8px;
                transition: background-color 0.3s ease, transform 0.3s ease;
            }

            .search-history .list-group-item:hover {
                background-color: #fef4e8;
                transform: translateX(5px); /* Sedikit geser ke kanan */
            }

            .search-history .search-suggestion {
                color: #F0902F;
                font-weight: bold;
                text-decoration: none;
            }

            .search-history .search-suggestion:hover {
                color: #e07d29;
            }

            .search-history .btn-danger {
                font-size: 0.8rem; /* Ukuran tombol lebih kecil */
                padding: 4px 8px;
                border-radius: 4px;
                transition: all 0.3s ease-in-out;
            }

            .search-history .btn-danger:hover {
                background-color: #F0902F;
                border-color: #F0902F;
                color: white;
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

            .container-form {
                margin-top: 500px;
            }

            #typed-output {
                font-family: 'Noto Sans', serif;
                font-size: 3em;
                font-weight: bold;
                white-space: nowrap;
                overflow: hidden;
                text-align: center;
                color:#F0902F;
            }

            #typedoutput {
                font-family: 'Noto Sans', serif;
                font-size: 1em;
                font-weight: bold;
                white-space: nowrap;
                overflow: hidden;
                text-align: center;
                color: #F0902F;
            }

            #typed-section {   
                margin-top: 200px;
            }

            .container.mt-4 {
                height: 100vh; /* Memberikan jarak antara typed-section dan daftar resep */
            }
            
            .card {
                border-radius: 15px; /* Membuat sudut card lebih bulat */
                transition: transform 0.3s, box-shadow 0.3s ease-in-out;
            }

            .card:hover {
                transform: translateY(-12px); /* Efek hover lebih tinggi */
                box-shadow: 0px 12px 24px rgba(0, 0, 0, 0.15); /* Bayangan lebih halus */
                background-color: #f9f9f9; /* Memberikan latar belakang terang saat hover */
            }

            .card-body {
                padding: 15px; /* Menambahkan padding di dalam card */
            }

            .card-img-top {
                border-top-left-radius: 15px;
                border-top-right-radius: 15px;
            }

            .card-title {
                font-size: 1.25rem; /* Ukuran font yang lebih besar */
                color: #333;
            }
                
            h2 {
                font-family: 'Noto Sans', sans-serif;
                font-size: 2rem;
                font-weight: 700;
                color: #F0902F; /* Warna yang sesuai dengan tema */
                text-align: center;
                margin-top: 100px;
                margin-bottom: 100px; /* Memberikan jarak bawah */
            }

            .btn-outline-secondary {
                border: 2px solid #F0902F; /* Garis border dengan warna yang konsisten */
                color: #F0902F; /* Warna teks sesuai tema */
                border-radius: 25px; /* Membuat tombol lebih bulat */
                padding: 10px 20px; /* Menambah padding agar tombol lebih besar */
                transition: background-color 0.3s, transform 0.2s ease-in-out;
                margin-bottom: 20px;
            }

            .btn-outline-secondary:hover {
                background-color: #F0902F; /* Memberikan latar belakang oranye saat hover */
                color: white; /* Warna teks menjadi putih saat hover */
                transform: scale(1.05); /* Efek zoom saat hover */
                border: 2px solid #F0902F; /* Garis border dengan warna yang konsisten */
            }
        </style>
    </head>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php">ResepKita</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <i data-feather="menu" class="text-white"></i> <!-- Menggunakan ikon menu dari Feather Icons -->
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="resep.php">Resep</a></li>
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
    
    <!-- welcome animation -->
    <div class="container mt-4">
        <div id="typed-section">
            <div id="typed-output"></div>
            <div id="typedoutput"></div>
        </div>
    </div>

        <!-- Pencarian -->
        <div class="container-form">
            <form id="search-form" action="index.php" method="get" class="form-inline justify-content-center">
                <input id="search-input" type="text" name="search" class="form-control mr-2 w-75" placeholder="Cari resep..." />
                <button id="search-btn" type="submit" class="btn btn-primary">Cari</button>
            </form>

            <?php if (!empty($searchHistory)): ?>
            <div class="search-history mt-2" id="search-history">
                <h5>Riwayat Pencarian:</h5>
                <ul class="list-group">
                    <?php foreach ($searchHistory as $term): ?>
                        <li class="list-group-item">
                            <span class="search-suggestion" style="cursor: pointer;" onclick="setSearchTerm('<?= htmlspecialchars($term); ?>')"><?= htmlspecialchars($term); ?></span>
                            <a href="actions/delete_search_history.php?term=<?= urlencode($term); ?>" class="btn btn-danger btn-sm float-right">Hapus</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        </div>

    <!-- Daftar Resep -->
<div class="container mt-4">
    <h2 data-aos="fade-up" data-aos-duration="2500" class="text-center">Rekomendasi Resep</h2>
    <?php if (!empty($resepList)): ?>
        <div class="row" id="resep-list">
            <?php foreach ($resepList as $resep): ?>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-duration="2500"> <!-- AOS animasi -->
                    <div class="card shadow-sm border-0">
                        <a href="view_resep.php?id=<?= $resep['id']; ?>" style="text-decoration: none; color: inherit;"> <!-- Membungkus card dengan link -->
                            <img src="<?= htmlspecialchars($resep['image_url']); ?>" class="card-img-top" alt="Gambar Resep" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title text-center font-weight-bold"><?= htmlspecialchars($resep['title']); ?></h5>
                                <p class="text-center text-muted">
                                    Diunggah oleh: <?= isset($resep['username']) ? htmlspecialchars($resep['username']) : 'Tidak diketahui'; ?>
                                </p>
                            </div>
                        </a>
                        <div class="text-center">
                            <?php if (isLoggedIn()): ?>
                                <a href="actions/bookmarks_action.php?action=add&resid=<?= $resep['id']; ?>" class="btn btn-outline-secondary mt-2">Simpan</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">Tidak ada resep yang ditemukan untuk kata kunci "<?= htmlspecialchars($searchKeyword); ?>".</p>
    <?php endif; ?>
</div>

    
          
    <!-- JS dan AOS -->
      
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Inisialisasi AOS
        AOS.init({
            once: false
        });

       
        let typed1, typed2;
        let animationCompleted = false; // Variabel status untuk memastikan animasi hanya berjalan sekali

        function startTypedAnimations() {
            if (animationCompleted) return; // Jika animasi sudah selesai, jangan jalankan lagi

            typed1 = new Typed('#typed-output', {
                strings: ["Selamat datang di website Resep Kita"],
                typeSpeed: 50,
                startDelay: 500,
                backDelay: 1000,
                loop: false,
                showCursor: false,
                onComplete: function() {
                    typed2.start();
                }
            });

            typed2 = new Typed('#typedoutput', {
                strings: ["Dengan ResepKita, masak jadi lebih mudah dan menyenangkan!"],
                typeSpeed: 50,
                backDelay: 1000,
                loop: false,
                showCursor: false,
                autoInsertCss: true,
                startDelay: 3000
            });

            typed2.stop(); // Stop animasi kedua sampai animasi pertama selesai
            animationCompleted = true; // Set status animasi selesai
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !animationCompleted) {
                    startTypedAnimations(); // Mulai animasi hanya jika belum selesai
                }
            });
        });

        observer.observe(document.querySelector('#typed-section')); // Mendeteksi elemen masuk viewport

        // Menambahkan event listener untuk input pencarian
        const searchInput = document.getElementById('search-input');
        const typedSection = document.getElementById('typed-section');
        const placeholderTexts = [
            "Cari resep dengan bahan yang ada di rumah...",
            "Cari resep yang mudah dan cepat..."
        ];
        const resepList = document.getElementById('resep-list');

        searchInput.addEventListener('input', function() {
            // Sembunyikan animasi saat mengetik
            typedSection.style.display = 'none';
            resepList.style.display = 'block'; // Tampilkan daftar resep setelah pencarian dimulai
        });

        let currentTextIndex = 0;
        let currentCharIndex = 0;

        function typePlaceholder() {
            const currentText = placeholderTexts[currentTextIndex];
            searchInput.placeholder = currentText.substring(0, currentCharIndex + 1);
            currentCharIndex++;

            if (currentCharIndex === currentText.length) {
                setTimeout(deletePlaceholder, 1000); // Menunggu 1 detik sebelum menghapus
            } else {
                setTimeout(typePlaceholder, 100); // Kecepatan mengetik
            }
        }

        function deletePlaceholder() {
            const currentText = placeholderTexts[currentTextIndex];
            searchInput.placeholder = currentText.substring(0, currentCharIndex - 1);
            currentCharIndex--;

            if (currentCharIndex === 0) {
                currentTextIndex = (currentTextIndex + 1) % placeholderTexts.length;
                setTimeout(typePlaceholder, 200); // Jeda sebelum mengetik teks baru
            } else {
                setTimeout(deletePlaceholder, 50); // Kecepatan menghapus
            }
        }

        // Mulai animasi placeholder
        typePlaceholder();

         // Ambil elemen input dan riwayat pencarian
   
        const searchHistory = document.getElementById('search-history');

       // Tambahkan event listener untuk menampilkan riwayat saat input difokuskan
searchInput.addEventListener('focus', function() {
    searchHistory.classList.add('show'); // Tambahkan kelas show untuk memulai animasi muncul
    searchHistory.style.display = 'block'; // Pastikan elemen ditampilkan
});

// Tambahkan event listener untuk menyembunyikan riwayat saat pengguna mengklik di luar
document.addEventListener('click', function(event) {
    if (!searchInput.contains(event.target) && !searchHistory.contains(event.target)) {
        searchHistory.classList.add('fade-out'); // Tambahkan kelas fade-out untuk menghilang

        // Tunggu sampai transisi selesai sebelum menyembunyikan elemen
        setTimeout(() => {
            searchHistory.classList.remove('show'); // Hapus kelas show
            searchHistory.classList.remove('fade-out'); // Hapus kelas fade-out
            searchHistory.style.display = 'none'; // Sembunyikan elemen
        }, 300); // Waktu yang sama dengan durasi transisi
    }
});


    function setSearchTerm(term) {
        document.getElementById('search-input').value = term; // Mengisi input dengan teks yang diklik
    }

</script> 
<script>
      feather.replace();
    </script>
         
    </body>
</html>

