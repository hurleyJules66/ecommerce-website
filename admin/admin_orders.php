<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}
include '../includes/db.php';

// Handle status update
if (isset($_POST['update_status'])) {
    $conn->query("UPDATE orders SET status='{$_POST['status']}' WHERE id={$_POST['order_id']}");
}

// Handle delete
if (isset($_POST['delete_order'])) {
    $conn->query("DELETE FROM order_items WHERE order_id={$_POST['order_id']}");
    $conn->query("DELETE FROM orders WHERE id={$_POST['order_id']}");
}

// 🔍 Filters
$status_filter = $_GET['status'] ?? 'all';
$search = $conn->real_escape_string($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// WHERE clause builder
$where = [];
if (in_array($status_filter, ['pending', 'shipped', 'delivered'])) {
    $where[] = "status = '$status_filter'";
}
if ($search) {
    $where[] = "(customer_name LIKE '%$search%' OR customer_email LIKE '%$search%' OR customer_mobile LIKE '%$search%')";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Total count for pagination
$total_result = $conn->query("SELECT COUNT(*) as count FROM orders $where_sql");
$total_orders = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_orders / $limit);

// Main query
$orders_sql = "SELECT * FROM orders $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$orders_result = $conn->query($orders_sql);

function getBadgeClass($status) {
    return match ($status) {
        'pending' => 'bg-warning text-dark',
        'shipped' => 'bg-info text-dark',
        'delivered' => 'bg-success',
        default => 'bg-secondary',
    };
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
  <a href="admin_logout.php" class="btn btn-sm btn-outline-danger float-end">Logout</a>
  <h2 class="mb-4">🧾 Customer Orders</h2>

  <!-- 🔍 Search + Filter Form -->
  <form method="get" class="row mb-4 g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label">Filter by Status:</label>
      <select name="status" class="form-select" onchange="this.form.submit()">
        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All</option>
        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
        <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Search by Name/Email/Phone:</label>
      <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. john@example.com or 0712...">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Search</button>
    </div>
  </form>

  <a href="export_orders.php" class="btn btn-success mb-3">⬇️ Export Orders to CSV</a>
  <a href="export_orders_pdf.php" class="btn btn-danger mb-3">🧾 Export Orders to PDF</a>

  <?php if ($orders_result->num_rows > 0): ?>
    <?php while ($order = $orders_result->fetch_assoc()): ?>
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <strong>Order #<?= $order['id'] ?></strong> — 
            <?= htmlspecialchars($order['customer_name']) ?> |
            <?= htmlspecialchars($order['customer_email']) ?> |
            📱 <?= htmlspecialchars($order['customer_mobile']) ?> |
            <strong>$<?= number_format($order['total'], 2) ?></strong>
            <span class="ms-2 badge <?= getBadgeClass($order['status']) ?>">
              <?= ucfirst($order['status']) ?>
            </span>
          </div>
          <small class="text-muted"><?= $order['created_at'] ?></small>
        </div>

        <div class="card-body">
          <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($order['customer_address'])) ?></p>

          <!-- Status & Delete Form -->
          <form method="post" class="d-flex gap-2 align-items-center mb-3">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <select name="status" class="form-select w-auto">
              <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
              <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
            </select>
            <button type="submit" name="update_status" class="btn btn-sm btn-outline-primary">Update</button>
            <button type="submit" name="delete_order" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this order?')">Delete</button>
          </form>

          <!-- Items -->
          <?php
          $items = $conn->query("
            SELECT oi.*, p.name AS product_name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = {$order['id']}
          ");
          ?>
          <?php if ($items->num_rows): ?>
            <table class="table table-sm">
              <thead>
                <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
              </thead>
              <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                  <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td>$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>

    <!-- 🔁 Pagination -->
    <nav>
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?status=<?= $status_filter ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>

  <?php else: ?>
    <div class="alert alert-info">No orders found for this filter/search.</div>
  <?php endif; ?>
</div>
</body>
</html>
