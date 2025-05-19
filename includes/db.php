<?php
// Database connection
$host = 'sql303.infinityfree.com';
$user = 'if0_38721226'; 
$password = 'hurleyjules6'; 
$dbname = 'if0_38721226_ecommerce_db';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
