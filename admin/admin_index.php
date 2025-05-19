<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

include '../includes/db.php';

// Fetch latest products
$latestProducts = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");

// Fetch latest orders
$latestOrders = $conn->query("
    SELECT o.id AS order_id, o.customer_name, o.total, o.created_at,
           GROUP_CONCAT(p.name SEPARATOR ', ') AS product_names
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🛠️ Admin Dashboard</h2>
    <a href="admin_logout.php" class="btn btn-danger">Logout</a>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <a href="admin_products.php" class="text-decoration-none">
        <div class="card border-primary h-100 text-center p-4">
          <h4>🛍️ Manage Products</h4>
          <p>Add, edit, or delete products</p>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="admin_orders.php" class="text-decoration-none">
        <div class="card border-success h-100 text-center p-4">
          <h4>📦 View Orders</h4>
          <p>Check and process customer orders</p>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="admin_add_product.php" class="text-decoration-none">
        <div class="card border-info h-100 text-center p-4">
          <h4>➕ Add Product</h4>
          <p>Quickly add a new product</p>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="admin_inventory.php" class="text-decoration-none">
        <div class="card border-success h-100 text-center p-4">
          <h4>📦 Check Inventory</h4>
          <p>View/manage stock levels</p>
        </div>
      </a>
    </div>

  </div>

  <h4 class="mb-3">🆕 Latest Products</h4>
  <table class="table table-striped table-bordered">
    <thead class="table-secondary">
      <tr><th>Name</th><th>Price</th><th>Image</th><th>Date</th></tr>
    </thead>
    <tbody>
      <?php while ($row = $latestProducts->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td>$<?php echo number_format($row['price'], 2); ?></td>
          <td><img src="../assets/images/<?php echo $row['image']; ?>" width="60"></td>
          <td><?php echo $row['created_at']; ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <h4 class="mt-5 mb-3">📥 Latest Orders</h4>
  <table class="table table-striped table-bordered">
    <thead class="table-secondary">
      <tr><th>Customer</th><th>Product</th><th>Total</th><th>Date</th></tr>
    </thead>
    <tbody>
    <?php while ($row = $latestOrders->fetch_assoc()): ?>
  <tr>
    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
    <td><?php echo htmlspecialchars($row['product_names']); ?></td>
    <td>$<?php echo number_format($row['total'], 2); ?></td>
    <td><?php echo $row['created_at']; ?></td>
  </tr>
<?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
