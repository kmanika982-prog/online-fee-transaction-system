<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: index.html");
    exit();
}

include("backend/db.php");

$student_id = $_SESSION['student_id'];


/* ==============================
   GET STUDENT INFORMATION
================================ */

$sql = "SELECT *
        FROM students
        WHERE student_id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $student_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student record not found.");
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Student Profile</title>

<link rel="stylesheet" href="style.css">

</head>

<body>


<!-- ==============================
     HEADER
================================ -->

<header class="topbar">


    <div class="menu">
        ☰
    </div>


    <div class="brand">

        Online Fee Transaction System

    </div>


    <div class="user">


        <div class="avatar">
            👩
        </div>


        <div>

            <b>

                <?php

                echo htmlspecialchars(
                    $student['student_name']
                );

                ?>

            </b>


            <small>

                <?php

                echo htmlspecialchars(
                    $student['course']
                );

                ?>

                -

                <?php

                echo htmlspecialchars(
                    $student['semester']
                );

                ?>

            </small>

        </div>


    </div>


</header>


<!-- ==============================
     SIDEBAR
================================ -->

<aside class="sidebar">


    <a href="dashboard.php">

        <span>🏠</span>
        <span>Dashboard</span>

    </a>


    <a href="profile.php"
       class="active">

        <span>👤</span>
        <span>Profile</span>

    </a>


    <a href="fee-details.php">

        <span>💰</span>
        <span>Fee Details</span>

    </a>


    <a href="pay-fee.php">

        <span>💳</span>
        <span>Pay Fee</span>

    </a>


    <a href="payment-history.php">

        <span>📋</span>
        <span>Payment History</span>

    </a>


    <a href="backend/logout.php">

        <span>🚪</span>
        <span>Logout</span>

    </a>


</aside>


<!-- ==============================
     MAIN CONTENT
================================ -->

<main class="main">


    <h1>
        My Profile
    </h1>


    <p class="muted">

        Student personal information

    </p>


    <div class="profile-box"
         style="margin-top:20px">


        <!-- PROFILE TOP -->

        <div class="profile-top">


            <div class="profile-avatar">
                👩
            </div>


            <b>

                <?php

                echo htmlspecialchars(
                    $student['student_name']
                );

                ?>

            </b>


            <p class="muted">

                <?php

                echo htmlspecialchars(
                    $student['course']
                );

                ?>

                -

                <?php

                echo htmlspecialchars(
                    $student['semester']
                );

                ?>

            </p>


        </div>


        <!-- STUDENT NAME -->

        <div class="row">

            <b>
                Student Name
            </b>

            <span>

                <?php

                echo htmlspecialchars(
                    $student['student_name']
                );

                ?>

            </span>

        </div>


        <!-- STUDENT ID -->

        <div class="row">

            <b>
                Student ID
            </b>

            <span>

                <?php

                echo htmlspecialchars(
                    $student['student_id']
                );

                ?>

            </span>

        </div>


        <!-- COURSE -->

        <div class="row">

            <b>
                Course
            </b>

            <span>

                <?php

                echo htmlspecialchars(
                    $student['course']
                );

                ?>

            </span>

        </div>


        <!-- SEMESTER -->

        <div class="row">

            <b>
                Semester
            </b>

            <span>

                <?php

                echo htmlspecialchars(
                    $student['semester']
                );

                ?>

            </span>

        </div>


        <!-- EMAIL -->

        <div class="row">

            <b>
                Email
            </b>

            <span>

                <?php

                echo htmlspecialchars(
                    $student['email'] ?? ''
                );

                ?>

            </span>

        </div>


        <!-- PHONE -->

        <div class="row">

            <b>
                Phone
            </b>

            <span>

                <?php

                echo htmlspecialchars(
                    $student['phone'] ?? ''
                );

                ?>

            </span>

        </div>


        <!-- ADDRESS -->

        <div class="row">

            <b>
                Address
            </b>

            <span>

                <?php

                echo htmlspecialchars(
                    $student['address'] ?? ''
                );

                ?>

            </span>

        </div>


        <!-- DATE OF BIRTH -->

        <div class="row">

            <b>
                Date of Birth
            </b>

            <span>

                <?php

                echo htmlspecialchars(
                    $student['date_of_birth'] ?? ''
                );

                ?>

            </span>

        </div>


        <br>


        <button
            class="btn"
            type="button">

            Edit Profile

        </button>


    </div>


</main>


<!-- ==============================
     FOOTER
================================ -->

<footer class="footer">

    © 2026 Online Fee Transaction System.
    All rights reserved.

</footer>


</body>

</html>