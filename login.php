<?php

session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM students
            WHERE student_id='$student_id'
            AND password='$password'";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Login Query Failed: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) == 1) {

        $_SESSION['student_id'] = $student_id;

        header("Location: ../dashboard.php");
        exit();

    } else {

        echo "<script>
        alert('Invalid Student ID or Password');
        window.location='../index.html';
        </script>";
    }

} else {

    header("Location: ../index.html");
    exit();
}

?>