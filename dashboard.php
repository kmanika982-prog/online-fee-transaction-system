<?php

session_start();

/* ==============================
   CHECK LOGIN
================================ */

if (!isset($_SESSION['student_id'])) {
    header("Location: index.html");
    exit();
}


/* ==============================
   DATABASE CONNECTION
================================ */

include("backend/db.php");


$student_id = $_SESSION['student_id'];


/* ==============================
   STUDENT INFORMATION
================================ */

$stmt = mysqli_prepare(
    $conn,
    "SELECT student_name, course, semester
     FROM students
     WHERE student_id = ?"
);

if (!$stmt) {
    die("Student query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $student_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student record not found.");
}


/* ==============================
   FEE INFORMATION
================================ */

$fee_stmt = mysqli_prepare(
    $conn,
    "SELECT
        COALESCE(SUM(total_amount), 0) AS total_fee,
        COALESCE(SUM(paid_amount), 0) AS paid_amount,
        COALESCE(SUM(due_amount), 0) AS due_amount
     FROM fee_details
     WHERE student_id = ?"
);

if (!$fee_stmt) {
    die("Fee query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($fee_stmt, "s", $student_id);
mysqli_stmt_execute($fee_stmt);

$fee_result = mysqli_stmt_get_result($fee_stmt);
$fee = mysqli_fetch_assoc($fee_result);


/* ==============================
   AMOUNTS
================================ */

$total_fee = (float) $fee['total_fee'];
$paid_amount = (float) $fee['paid_amount'];
$due_amount = (float) $fee['due_amount'];


/* ==============================
   PAYMENT STATUS
================================ */

if ($total_fee <= 0) {

    $payment_status = "No Fee";

} elseif ($due_amount <= 0) {

    $payment_status = "Paid";

} elseif ($paid_amount <= 0) {

    $payment_status = "Unpaid";

} else {

    $payment_status = "Partial";
}


/* ==============================
   PAYMENT PROGRESS
================================ */

if ($total_fee > 0) {

    $progress = ($paid_amount / $total_fee) * 100;

} else {

    $progress = 0;
}


/* Keep progress between 0 and 100 */

$progress = max(0, min(100, $progress));

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard</title>

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
                echo htmlspecialchars($student['student_name']);
                ?>
            </b>


            <small>

                <?php
                echo htmlspecialchars(
                    $student['course'] .
                    " - " .
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


    <a href="dashboard.php" class="active">

        <span>🏠</span>

        <span>Dashboard</span>

    </a>



    <a href="profile.php">

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

        Welcome,

        <?php
        echo htmlspecialchars($student['student_name']);
        ?>!

    </h1>


    <p class="muted">

        Student ID:

        <b>

            <?php
            echo htmlspecialchars($student_id);
            ?>

        </b>

    </p>



    <!-- ==============================
         SUMMARY CARDS
    ================================= -->

    <div class="cards" style="margin-top:22px">


        <!-- TOTAL FEE -->

        <div class="card summary">

            <div class="icon blue">
                💰
            </div>


            <div>

                <p>Total Fee</p>

                <h2>

                    Rs.
                    <?php
                    echo number_format($total_fee, 2);
                    ?>

                </h2>

            </div>

        </div>



        <!-- PAID AMOUNT -->

        <div class="card summary">

            <div class="icon green">
                💵
            </div>


            <div>

                <p>Paid Amount</p>

                <h2>

                    Rs.
                    <?php
                    echo number_format($paid_amount, 2);
                    ?>

                </h2>

            </div>

        </div>



        <!-- DUE AMOUNT -->

        <div class="card summary">

            <div class="icon red">
                ⚠️
            </div>


            <div>

                <p>Due Amount</p>

                <h2>

                    Rs.
                    <?php
                    echo number_format($due_amount, 2);
                    ?>

                </h2>

            </div>

        </div>



        <!-- PAYMENT STATUS -->

        <div class="card summary">

            <div class="icon orange">
                💳
            </div>


            <div>

                <p>Payment Status</p>

                <h2>

                    <?php
                    echo htmlspecialchars($payment_status);
                    ?>

                </h2>

            </div>

        </div>


    </div>



    <!-- ==============================
         QUICK ACTIONS
    ================================= -->

    <h2 class="section-title">

        Quick Actions

    </h2>



    <div class="actions">


        <a class="action"
           href="pay-fee.php">

            <span class="icon blue">
                💳
            </span>

            Pay Fee

        </a>



        <a class="action"
           href="fee-details.php">

            <span class="icon green">
                📄
            </span>

            Fee Details

        </a>



        <a class="action"
           href="payment-history.php">

            <span class="icon purple">
                📋
            </span>

            Payment History

        </a>



        <a class="action"
           href="profile.php">

            <span class="icon orange">
                👤
            </span>

            My Profile

        </a>


    </div>



    <!-- ==============================
         PAYMENT PROGRESS
    ================================= -->

    <div class="card progress">


        <div class="progress-head">


            <div>

                <b>
                    Fee Payment Progress
                </b>


                <p class="muted">

                    Your current fee payment status

                </p>

            </div>



            <b>

                <?php
                echo round($progress);
                ?>%

            </b>


        </div>



        <div class="bar">

            <div
                class="fill"
                style="width: <?php echo $progress; ?>%;">
            </div>

        </div>



        <div class="progress-info">


            <span>

                Paid:

                Rs.
                <?php
                echo number_format($paid_amount, 2);
                ?>

            </span>



            <span>

                Remaining:

                Rs.
                <?php
                echo number_format($due_amount, 2);
                ?>

            </span>


        </div>


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