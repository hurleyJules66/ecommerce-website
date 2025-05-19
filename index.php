<?php
include 'includes/db.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$where = [];

if ($search) {
    $search_escaped = $conn->real_escape_string($search);
    $where[] = "(name LIKE '%$search_escaped%' OR description LIKE '%$search_escaped%')";
}

if ($category) {
    $category_escaped = $conn->real_escape_string($category);
    $where[] = "category = '$category_escaped'";
}

if ($min_price !== '' && is_numeric($min_price)) {
    $where[] = "price >= " . floatval($min_price);
}

if ($max_price !== '' && is_numeric($max_price)) {
    $where[] = "price <= " . floatval($max_price);
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT * FROM products $where_clause ORDER BY created_at DESC LIMIT 12";
$result = $conn->query($sql);

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
    <title>In N Out Shop</title>
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
        }
        .navbar {
            background-color: #232f3e;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .banner {
            background: url('assets/banner.jpg') no-repeat center center;
            background-size: cover;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-shadow: 1px 1px 5px black;
        }
        .product-card {
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: scale(1.03);
        }
        footer {
            background-color: #232f3e;
            color: white;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="/assets/images/favicon.png" alt="In N Out Shop Logo" width="40" height="40" class="me-2">
        In N Out Shop
    </a>
    <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon bg-light"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="products.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Banner -->
<div class="banner text-center">
    <div>
        <h1>Welcome to In N Out Shop</h1>
        <p>Explore top deals and latest trends!</p>
    </div>
</div>

<!-- Filters -->
<div class="container my-4">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-6 col-md-3">
            <label class="form-label">Search</label>
            <select name="search" class="form-select">
                <option value="">All</option>
                <option value="Shoes" <?= $search === 'Shoes' ? 'selected' : '' ?>>Shoes</option>
                <option value="Phones" <?= $search === 'Phones' ? 'selected' : '' ?>>Phones</option>
                <option value="T-Shirts" <?= $search === 'T-Shirts' ? 'selected' : '' ?>>T-Shirts</option>
                <option value="Headphones" <?= $search === 'Headphones' ? 'selected' : '' ?>>Headphones</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Min Price</label>
            <select name="min_price" class="form-select">
                <option value="">No Min</option>
                <option value="10" <?= $min_price == 10 ? 'selected' : '' ?>>$10</option>
                <option value="50" <?= $min_price == 50 ? 'selected' : '' ?>>$50</option>
                <option value="100" <?= $min_price == 100 ? 'selected' : '' ?>>$100</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Max Price</label>
            <select name="max_price" class="form-select">
                <option value="">No Max</option>
                <option value="100" <?= $max_price == 100 ? 'selected' : '' ?>>$100</option>
                <option value="200" <?= $max_price == 200 ? 'selected' : '' ?>>$200</option>
                <option value="500" <?= $max_price == 500 ? 'selected' : '' ?>>$500</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-grid">
            <button type="submit" class="btn btn-dark">Go</button>
        </div>
    </form>
</div>

<!-- Products -->
<div class="container my-5">
    <h2 class="text-center mb-4">Latest Products</h2>
    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4">
                    <div class="card product-card h-100">
                        <img src="assets/images/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" style="height: 220px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="card-text fw-bold">$<?= number_format($row['price'], 2) ?></p>
                            <a href="product_details.php?id=<?= $row['id'] ?>" class="btn btn-outline-dark mt-auto w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No products found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-4">
    &copy; <?= date('Y') ?> In N Out Shop. All Rights Reserved. |
    <a href="admin/admin_index.php" style="color: #ffffff; text-decoration: underline;">Admin Dashboard</a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
