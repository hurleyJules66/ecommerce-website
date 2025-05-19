<?php
session_start();

// Set your admin credentials
$admin_email = "admin@shop.com";
$admin_pass = "admin"; // In real apps, use hashed passwords and a database

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($email === $admin_email && $password === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_index.php");
        exit;
    } else {
        $message = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card p-4 shadow-sm">
        <h3 class="text-center mb-4">🔐 Admin Login</h3>

        <?php if ($message): ?>
          <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="d-grid">
            <button class="btn btn-primary">Login</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

</body>
</html>
