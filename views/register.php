<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ResepKita</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #F0902F;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            text-align: center;
            max-width: 450px;
        }
        .card {
            background: #fff;
            color: #333;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            padding: 2rem;
            margin-top: 1rem;
            position: relative;
        }
        .description {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #fff;
            background: rgba(0, 0, 0, 0.3); /* Darker background for better contrast */
            padding: 1rem 1.5rem;
            border-radius: 10px;
            text-shadow: 0px 0px 12px rgba(255, 126, 179, 0.9), 0px 0px 25px rgba(255, 126, 179, 0.7);
            max-width: 100%;
            box-sizing: border-box;
        }
        .description p {
            margin: 0;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .form-group {
            margin-top: 20px;
        }
        .btn-primary {
            background: #F0902F;
            border: none;
            transition: background 0.3s ease;
        }
        .btn-primary:hover {
            background: #ff8c00;
        }
        .form-control {
            border-radius: 50px;
        }
        a {
            color: #F0902F;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            color: #ff8c00;
        }
        .footer-text {
            font-size: 1rem;
            color: #fff;
            margin-top: 2rem;
            opacity: 0.85;
            text-shadow: 0px 0px 8px rgba(255, 126, 179, 0.9), 0px 0px 15px rgba(255, 126, 179, 0.7);
        }
        /* Mobile friendly adjustments */
        @media (max-width: 600px) {
            .description {
                font-size: 1.2rem;
                padding: 0.75rem 1rem;
            }
            .footer-text {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Form Card -->
    <div class="card" data-aos="fade-up" data-aos-duration="1000">
        <!-- Description Inside the Form Border -->
        <div class="description" data-aos="fade-up" data-aos-duration="1000">
            <p>Temukan resep favoritmu dan inspirasi kuliner setiap hari di ResepKita!</p>
        </div>

        <h3 class="text-center">Daftar di ResepKita</h3>
        <form action="actions/register_action.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Daftar</button>
            <p class="text-center mt-3">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </p>
        </form>
    </div>
    
    <!-- Motivational Footer Text -->
    <p class="footer-text">Dengan ResepKita, masak jadi lebih mudah dan menyenangkan!</p>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init();
</script>

</body>
</html>
