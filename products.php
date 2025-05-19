<?php
include 'includes/db.php';

// Filter variables
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$where = [];

if ($search) {
    $escaped = $conn->real_escape_string($search);
    $where[] = "(name LIKE '%$escaped%' OR description LIKE '%$escaped%')";
}

if ($category) {
    $escaped = $conn->real_escape_string($category);
    $where[] = "category = '$escaped'";
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM products $where_clause ORDER BY created_at DESC";
$result = $conn->query($sql);

// Get all categories for dropdown
$cat_result = $conn->query("SELECT DISTINCT category FROM products");
$categories = [];
while ($cat = $cat_result->fetch_assoc()) {
    $categories[] = $cat['category'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop - In N Out Shop</title>
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
        .card {
            border: none;
            transition: transform 0.2s ease-in-out;
            border-radius: 12px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .btn-outline-primary {
            border-color: #ff6f00;
            color: #ff6f00;
        }
        .btn-outline-primary:hover {
            background-color: #ff6f00;
            color: white;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        footer {
            background-color: #1c1c1c;
            color: white;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="/assets/images/favicon.png" alt="Logo" width="40" height="40" class="me-2">
            In N Out Shop
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon bg-light"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="products.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Filters -->
<div class="container mt-4">
    <form method="get" class="row g-3">
        <div class="col-12 col-sm-6">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="🔍 Search products...">
        </div>
        <div class="col-12 col-sm-4">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-sm-2">
            <button type="submit" class="btn btn-dark w-100">Filter</button>
        </div>
    </form>
</div>

<!-- Product Grid -->
<div class="container my-5">
    <h2 class="text-center mb-4">🛍️ Explore Our Products</h2>
    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-6 col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="assets/images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="card-img-top">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="card-text">$<?= number_format($row['price'], 2) ?></p>
                            <div class="mt-auto d-flex justify-content-between">
                                <a href="product_details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">🔍 View</a>
                                <a href="cart.php?action=add&id=<?= $row['id'] ?>" class="btn btn-sm btn-success">🛒 Add</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No products match your filter.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-4 mt-4">
    &copy; <?= date('Y') ?> In N Out Shop. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
