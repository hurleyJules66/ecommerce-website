<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

include '../includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: admin_products.php");
    exit;
}

$id = intval($_GET['id']);
$message = '';

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock']);
    $image       = $product['image']; // Keep existing image

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $destination = '../assets/images/' . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            // Delete old image
            $old_path = '../assets/images/' . $product['image'];
            if (file_exists($old_path)) {
                unlink($old_path);
            }
            $image = $filename;
        } else {
            $message = "Image upload failed.";
        }
    }

    if (!$message) {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=? WHERE id=?");
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $image, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_products.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
  <h2>✏️ Edit Product</h2>

  <?php if ($message): ?>
    <div class="alert alert-danger"><?php echo $message; ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Product Name</label>
      <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Price</label>
      <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $product['price']; ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Stock Quantity</label>
      <input type="number" class="form-control" name="stock" min="0" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Current Image</label><br>
      <img src="../assets/images/<?php echo $product['image']; ?>" width="100" alt="Product Image">
    </div>

    <div class="mb-3">
      <label class="form-label">Change Image (optional)</label>
      <input type="file" name="image" class="form-control" accept="image/*">
    </div>

    <button class="btn btn-success">Update Product</button>
    <a href="admin_products.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
