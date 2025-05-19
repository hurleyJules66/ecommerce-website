<?php
include 'includes/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "No product selected.";
    exit;
}

$product_id = intval($_GET['id']);
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$reviews_per_page = 5;
$offset = ($page - 1) * $reviews_per_page;

// Fetch product
$sql = "SELECT * FROM products WHERE id = $product_id LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    echo "Product not found.";
    exit;
}
$product = $result->fetch_assoc();

// Fetch average rating
$rating_sql = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM product_reviews WHERE product_id = $product_id";
$rating_result = $conn->query($rating_sql);
$rating_data = $rating_result->fetch_assoc();
$avg_rating = $rating_data['avg_rating'] ? number_format($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total'];

// Paginated reviews
$reviews_sql = "SELECT name, rating, review, created_at FROM product_reviews WHERE product_id = $product_id ORDER BY created_at DESC LIMIT $offset, $reviews_per_page";
$reviews = $conn->query($reviews_sql);

// Count total pages
$total_pages = ceil($total_reviews / $reviews_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - In N Out Shop</title>
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #1c1c1c; }
        .navbar-brand, .nav-link { color: white !important; }
        .product-container { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .product-image { max-height: 400px; object-fit: cover; width: 100%; border-radius: 10px; }
        .btn-primary { background-color: #ff6f00; border: none; }
        .btn-primary:hover { background-color: #e65c00; }
        .rating-stars i { color: #f39c12; }
        footer { background-color: #1c1c1c; color: white; }
        @media (max-width: 768px) {
            .product-image { max-height: 300px; }
            .product-container { padding: 15px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">In N Out Shop</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="products.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-5">
    <div class="row product-container">
        <div class="col-md-6 col-12">
            <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image img-fluid">
        </div>
        <div class="col-md-6 col-12">
            <h1 class="mb-3"><?= htmlspecialchars($product['name']) ?></h1>
            <h4 class="text-success mb-4">$<?= number_format($product['price'], 2) ?></h4>
            <p class="mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <p>
                <strong>Rating:</strong>
                <?php if ($total_reviews > 0): ?>
                    <?php
                    $fullStars = floor($avg_rating);
                    $halfStar = ($avg_rating - $fullStars) >= 0.5;
                    ?>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= $fullStars): ?>
                            <i class="bi bi-star-fill"></i>
                        <?php elseif ($i == $fullStars + 1 && $halfStar): ?>
                            <i class="bi bi-star-half"></i>
                        <?php else: ?>
                            <i class="bi bi-star"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <small class="text-muted">(<?= $avg_rating ?> from <?= $total_reviews ?> reviews)</small>
                <?php else: ?>
                    <span class="text-muted">No ratings yet</span>
                <?php endif; ?>
            </p>

            <a href="cart.php?action=add&id=<?= $product['id'] ?>" class="btn btn-primary btn-lg me-2">🛒 Add to Cart</a>
            <a href="products.php" class="btn btn-outline-secondary btn-lg">⬅ Back to Shop</a>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-8 offset-md-2">
            <h3>Leave a Review</h3>
            <form action="submit_review.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Rating</label>
                    <select name="rating" class="form-select" required>
                        <option value="">-- Select Rating --</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> ★</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Review</label>
                    <textarea name="review" class="form-control" rows="4" required></textarea>
                </div>
                <button class="btn btn-primary">Submit Review</button>
            </form>

            <hr class="my-5">

            <h4>Customer Reviews</h4>
            <?php if ($reviews->num_rows > 0): ?>
                <?php while ($r = $reviews->fetch_assoc()): ?>
                    <div class="mb-4">
                        <strong><?= htmlspecialchars($r['name']) ?></strong>
                        <span class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi <?= $i <= $r['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                            <?php endfor; ?>
                        </span><br>
                        <small class="text-muted"><?= $r['created_at'] ?></small>
                        <p><?= nl2br(htmlspecialchars($r['review'])) ?></p>
                    </div>
                <?php endwhile; ?>

                <!-- Pagination Links -->
                <nav>
                    <ul class="pagination">
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?id=<?= $product_id ?>&page=<?= $p ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>

            <?php else: ?>
                <p>No reviews yet. Be the first!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="text-center py-4 mt-4">
    &copy; <?= date('Y') ?> In N Out Shop. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
