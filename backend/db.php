<?php

$host = "sql203.infinityfree.com";
$user = "if0_42787088";
$password = "abc12345";
$database = "if0_42787088_online_fee_system";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>
