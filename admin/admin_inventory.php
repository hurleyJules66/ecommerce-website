<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}
include '../includes/db.php';
$products = $conn->query("SELECT * FROM products ORDER BY stock ASC");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Inventory Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
  <h2>📦 Inventory Overview</h2>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $products->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td>$<?php echo number_format($row['price'], 2); ?></td>
          <td><?php echo $row['stock']; ?></td>
          <td>
            <?php
              if ($row['stock'] == 0) {
                echo '<span class="badge bg-danger">Out of Stock</span>';
              } elseif ($row['stock'] <= 5) {
                echo '<span class="badge bg-warning text-dark">Low</span>';
              } else {
                echo '<span class="badge bg-success">In Stock</span>';
              }
            ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
