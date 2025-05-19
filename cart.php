<?php
session_start();
include 'includes/db.php';

// out of stock check
$error_messages = $_SESSION['error_messages'] ?? [];
unset($_SESSION['error_messages']);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $product_id = intval($_GET['id']);
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += 1;
    } else {
        $sql = "SELECT * FROM products WHERE id = $product_id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $_SESSION['cart'][$product_id] = array(
                "name" => $product['name'],
                "price" => $product['price'],
                "quantity" => 1
            );
        }
    }
    header('Location: cart.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $product_id = intval($_GET['id']);
    unset($_SESSION['cart'][$product_id]);
    header('Location: cart.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cart - In N Out Shop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/assets/images/favicon.png" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
        background-color: #f9f9f9;
    }
    .navbar {
        background-color: #1c1c1c;
    }
    .navbar-brand, .nav-link {
        color: white !important;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table {
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
    }
    .btn-danger {
        background-color: #dc3545;
        border: none;
    }
    .btn-danger:hover {
        background-color: #c82333;
    }
    .btn-primary {
        background-color: #ff6f00;
        border: none;
    }
    .btn-primary:hover {
        background-color: #e65c00;
    }
    footer {
        background-color: #1c1c1c;
        color: white;
    }
    @media (max-width: 768px) {
        .table thead {
            display: none;
        }
        .table tbody td {
            display: block;
            width: 100%;
            text-align: right;
            padding-left: 50%;
            position: relative;
        }
        .table tbody td::before {
            content: attr(data-label);
            position: absolute;
            left: 1rem;
            top: 0.75rem;
            font-weight: bold;
            text-align: left;
        }
        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.5rem;
        }
    }
  </style>
</head>
<body>

<!-- out of stock check -->
<?php if (!empty($error_messages)): ?>
    <div class="alert alert-danger container mt-3">
        <ul class="mb-0">
            <?php foreach ($error_messages as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<!-- end -->

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
        <img src="/assets/images/favicon.png" alt="Logo" width="40" height="40" class="me-2">
        In N Out Shop
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon bg-light"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="products.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link active" href="cart.php">Cart</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Cart Content -->
<div class="container my-5">
    <h2 class="text-center mb-4">🛒 Your Cart</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
        <div class="table-responsive">
            <table class="table table-bordered shadow-sm">
                <thead class="table-dark d-none d-md-table-header-group">
                    <tr>
                        <th>Product</th>
                        <th width="120">Price</th>
                        <th width="120">Quantity</th>
                        <th width="120">Subtotal</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['cart'] as $id => $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td data-label="Product"><?= htmlspecialchars($item['name']) ?></td>
                        <td data-label="Price">$<?= number_format($item['price'], 2) ?></td>
                        <td data-label="Quantity"><?= $item['quantity'] ?></td>
                        <td data-label="Subtotal">$<?= number_format($subtotal, 2) ?></td>
                        <td data-label="Remove">
                            <a href="cart.php?action=remove&id=<?= $id ?>" class="btn btn-danger btn-sm">✖</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th colspan="2">$<?= number_format($total, 2) ?></th>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="text-end mt-3">
            <a href="checkout.php" class="btn btn-primary btn-lg">Proceed to Checkout</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            🛍️ Your cart is empty. <a href="products.php" class="alert-link">Browse products</a> to get started!
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="text-center py-4 mt-5">
    &copy; <?= date('Y') ?> In N Out Shop. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
