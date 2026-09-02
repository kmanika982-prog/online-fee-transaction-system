<?php

$host = "YOUR_HOST";
$user = "YOUR_USERNAME";
$password = "YOUR_PASSWORD";
$database = "if0_42787088_online_fee_system";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>
