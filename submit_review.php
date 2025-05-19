<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $name = $conn->real_escape_string(trim($_POST['name']));
    $rating = intval($_POST['rating']);
    $review = $conn->real_escape_string(trim($_POST['review']));

    if ($name && $rating >= 1 && $rating <= 5 && $review) {
        $sql = "INSERT INTO product_reviews (product_id, name, rating, review) 
                VALUES ($product_id, '$name', $rating, '$review')";
        if ($conn->query($sql)) {
            header("Location: product_details.php?id=$product_id&review=success");
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Invalid input.";
    }
} else {
    echo "Invalid request method.";
}
?>
