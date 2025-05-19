<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

include '../includes/db.php';

// Delete logic
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: admin_products.php");
    exit;
}

// Get filter and search
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$whereClauses = [];

if ($filter === 'out_of_stock') {
    $whereClauses[] = 'stock = 0';
} elseif ($filter === 'low_stock') {
    $whereClauses[] = 'stock < 5';
}

if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $whereClauses[] = "name LIKE '%$search%'";
}

$whereClause = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Total products count
$countResult = $conn->query("SELECT COUNT(*) as total FROM products $whereClause");
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

// Fetch filtered + searched products
$query = "SELECT * FROM products $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
  <h2>🛍️ Manage Products 
    <a href="admin_add_product.php" class="btn btn-primary btn-sm float-end ms-2">+ Add New</a>
    <a href="export_products.php" class="btn btn-outline-success btn-sm float-end ms-2">📥 CSV</a>
    <a href="export_products_pdf.php" class="btn btn-outline-danger btn-sm float-end">📄 PDF</a>
  </h2>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-3">
      <select name="filter" class="form-select" onchange="this.form.submit()">
        <option value="all" <?php if ($filter === 'all') echo 'selected'; ?>>All Products</option>
        <option value="out_of_stock" <?php if ($filter === 'out_of_stock') echo 'selected'; ?>>Out of Stock</option>
        <option value="low_stock" <?php if ($filter === 'low_stock') echo 'selected'; ?>>Low Stock (&lt; 5)</option>
      </select>
    </div>
    <div class="col-md-5">
      <input type="text" name="search" class="form-control" placeholder="Search by name..." value="<?php echo htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-secondary w-100">Search</button>
    </div>
    <div class="col-md-2">
      <a href="admin_products.php" class="btn btn-outline-secondary w-100">Reset</a>
    </div>
  </form>

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Image</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td>$<?php echo number_format($row['price'], 2); ?></td>
            <td>
              <?php if ($row['stock'] == 0): ?>
                <span class="text-danger fw-bold">0 ❌</span>
              <?php elseif ($row['stock'] < 5): ?>
                <span class="text-warning fw-bold"><?php echo $row['stock']; ?> ⚠️</span>
              <?php else: ?>
                <?php echo $row['stock']; ?>
              <?php endif; ?>
            </td>
            <td><img src="../assets/images/<?php echo $row['image']; ?>" width="60"></td>
            <td>
              <a href="admin_edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
              <a href="admin_products.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">No products found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <nav>
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
          <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>
</body>
</html>
