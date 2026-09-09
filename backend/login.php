<?php

session_start();

include("db.php");


// ==========================================
// ONLY POST REQUEST
// ==========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../index.html");
    exit();

}


// ==========================================
// GET LOGIN DATA
// ==========================================

$student_id = trim($_POST['student_id'] ?? '');
$password   = trim($_POST['password'] ?? '');


// ==========================================
// VALIDATION
// ==========================================

if (empty($student_id) || empty($password)) {

    echo "<script>
        alert('Please enter Student ID/Email and Password.');
        window.location='../index.html';
    </script>";

    exit();
}


// ==========================================
// FIND STUDENT BY ID OR EMAIL
// ==========================================

$sql = "SELECT *
        FROM students
        WHERE student_id = ?
        OR email = ?
        LIMIT 1";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die(
        "Login Query Failed: " .
        mysqli_error($conn)
    );

}


// ==========================================
// BIND PARAMETER
// ==========================================

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $student_id,
    $student_id
);


// ==========================================
// EXECUTE
// ==========================================

mysqli_stmt_execute($stmt);


// ==========================================
// GET RESULT
// ==========================================

$result = mysqli_stmt_get_result($stmt);


// ==========================================
// CHECK STUDENT
// ==========================================

if (mysqli_num_rows($result) === 1) {

    $student = mysqli_fetch_assoc($result);


    // ======================================
    // CHECK PASSWORD
    // ======================================

    if ($password === $student['password']) {


        // ==============================
        // LOGIN SUCCESS
        // ==============================

        $_SESSION['student_id'] =
            $student['student_id'];

        $_SESSION['student_name'] =
            $student['student_name'];


        // ==============================
        // GO TO DASHBOARD
        // ==============================

        header("Location: ../dashboard.php");
        exit();


    } else {


        // ==============================
        // WRONG PASSWORD
        // ==============================

        echo "<script>

            alert('Invalid Password.');

            window.location='../index.html';

        </script>";

        exit();

    }


} else {


    // ==============================
    // STUDENT NOT FOUND
    // ==============================

    echo "<script>

        alert('Student ID or Email not found.');

        window.location='../index.html';

    </script>";

    exit();

}


// ==========================================
// CLOSE
// ==========================================

mysqli_stmt_close($stmt);

?>