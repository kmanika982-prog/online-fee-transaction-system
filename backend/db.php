<?php

$host = "127.0.0.1";
$user = "root";
$password = "manika";
$database = "online_fee_system";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

?>
