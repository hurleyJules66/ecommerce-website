<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$message = '';
$cart = $_SESSION['cart'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $mobile  = trim($_POST['mobile']);
    $address = trim($_POST['address']);

    if ($name && $email && $mobile && $address) {
        foreach ($cart as $product_id => $item) {
            $quantity_requested = $item['quantity'];
            $result = $conn->query("SELECT stock FROM products WHERE id = $product_id");
            if ($result && $row = $result->fetch_assoc()) {
                if ($quantity_requested > $row['stock']) {
                    $errors[] = "Sorry, Only {$row['stock']} of {$item['name']} available. We'll restock soon!";
                }
            } else {
                $errors[] = "Product '{$item['name']}' not found.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['error_messages'] = $errors;
            header("Location: cart.php");
            exit;
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_mobile, customer_address, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssd", $name, $email, $mobile, $address, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($cart as $product_id => $item) {
            $pname = $item['name'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $subtotal = $price * $quantity;

            $stmt->bind_param("iisidd", $order_id, $product_id, $pname, $quantity, $price, $subtotal);
            $stmt->execute();

            $conn->query("UPDATE products SET stock = stock - $quantity WHERE id = $product_id");
        }
        $stmt->close();

        $_SESSION['cart'] = [];
        header("Location: thank_you.php");
        exit;
    } else {
        $message = "Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - In N Out Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .checkout-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        @media (max-width: 576px) {
            .btn-lg {
                font-size: 1rem;
                padding: 0.75rem 1rem;
                width: 100%;
            }
            .checkout-card {
                padding: 20px 15px;
            }
            h2, h4 {
                font-size: 1.4rem;
            }
            .form-label {
                font-size: 0.95rem;
            }
            .list-group-item {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="/assets/images/favicon.png" alt="In N Out Shop Logo" width="50" height="50" class="me-2">
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

<!-- Main Content -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="checkout-card">
                <h2 class="mb-4 text-center">🛒 Checkout</h2>

                <?php if ($message): ?>
                    <div class="alert alert-danger"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="post" class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">👤 Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">📧 Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. john@example.com" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">📱 Mobile Number</label>
                        <input type="text" name="mobile" class="form-control" placeholder="e.g. +254712345678" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">🏠 Shipping Address</label>
                        <textarea name="address" class="form-control" rows="4" placeholder="e.g. 123 Nairobi Street, Kenya" required></textarea>
                    </div>
                    <div class="col-12 text-end text-sm-end">
                        <button type="submit" class="btn btn-success btn-lg w-100 w-sm-auto">Place Order</button>
                    </div>
                </form>

                <hr class="my-5">

                <h4 class="mb-3">🧾 Order Summary</h4>
                <div class="table-responsive">
                    <ul class="list-group mb-4">
                        <?php 
                        $total = 0;
                        foreach ($cart as $item) {
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
                                    <div>{$item['name']} <span class='text-muted'>(x{$item['quantity']})</span></div>
                                    <span class='fw-semibold'>\$" . number_format($subtotal, 2) . "</span>
                                  </li>";
                        }
                        ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Total</strong>
                            <strong>$<?php echo number_format($total, 2); ?></strong>
                        </li>
                    </ul>
                </div>

                <div class="text-center">
                    <a href="cart.php" class="btn btn-outline-secondary">← Back to Cart</a>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-4 mt-5 bg-white border-top">
    &copy; <?php echo date('Y'); ?> <strong>In N Out Shop</strong>. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
