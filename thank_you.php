<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Thank You - In N Out Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="10;url=products.php">
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .thank-you-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            padding: 40px 30px;
            animation: fadeIn 1.2s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
        }
        .checkmark svg {
            stroke: #28a745;
            stroke-width: 5;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            animation: draw 1s ease-out forwards;
        }
        @keyframes draw {
            from { stroke-dasharray: 60; stroke-dashoffset: 60; }
            to { stroke-dasharray: 60; stroke-dashoffset: 0; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="/assets/images/favicon.png" alt="In N Out Shop Logo" width="40" height="40" class="me-2">
            In N Out Shop
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="products.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Thank You Section -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="thank-you-card text-center">
                <div class="checkmark">
                    <svg viewBox="0 0 52 52">
                        <path d="M14 27l7 7 17-17" />
                    </svg>
                </div>
                <h1 class="mb-3">Thank You!</h1>
                <p class="lead mb-3">We've received your order and will contact you shortly.</p>
                <p class="text-muted">Redirecting you in <span id="countdown">10</span> seconds...</p>
                
                <!-- Progress Bar -->
                <div class="progress my-3" style="height: 10px;">
                    <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;"></div>
                </div>

                <a href="products.php" class="btn btn-outline-primary mt-3">Go Now</a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-4 mt-5 bg-white border-top">
    &copy; <?php echo date('Y'); ?> <strong>In N Out Shop</strong>. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let seconds = 10;
    const countdown = document.getElementById("countdown");
    const progressBar = document.getElementById("progressBar");

    const interval = setInterval(() => {
        seconds--;
        countdown.textContent = seconds;
        progressBar.style.width = `${(10 - seconds) * 10}%`;

        if (seconds <= 0) {
            clearInterval(interval);
        }
    }, 1000);
</script>
</body>
</html>
