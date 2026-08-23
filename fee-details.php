<?php

session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: index.html");
    exit();
}

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
   FEE DETAILS
================================ */

$fee_stmt = mysqli_prepare(
    $conn,
    "SELECT fee_id, fee_type, total_amount, paid_amount,
            due_amount, status
     FROM fee_details
     WHERE student_id = ?
     ORDER BY fee_id ASC"
);

if (!$fee_stmt) {
    die("Fee query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($fee_stmt, "s", $student_id);
mysqli_stmt_execute($fee_stmt);

$fee_result = mysqli_stmt_get_result($fee_stmt);


/* ==============================
   TOTAL CALCULATION
================================ */

$total_fee = 0;
$total_paid = 0;
$total_due = 0;

$fees = [];

while ($row = mysqli_fetch_assoc($fee_result)) {

    $fees[] = $row;

    $total_fee += (float)$row['total_amount'];
    $total_paid += (float)$row['paid_amount'];
    $total_due += (float)$row['due_amount'];
}


/* ==============================
   PAYMENT PROGRESS
================================ */

if ($total_fee > 0) {
    $progress = ($total_paid / $total_fee) * 100;
} else {
    $progress = 0;
}

$progress = max(0, min(100, $progress));

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Fee Details</title>

<link rel="stylesheet" href="style.css">

<style>

/* ==============================
   FEE DETAILS PAGE
================================ */

.fee-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 25px;
}

.fee-summary-card {
    background: white;
    padding: 22px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.fee-summary-card p {
    margin: 0 0 8px;
    color: #777;
}

.fee-summary-card h2 {
    margin: 0;
}

.fee-table-box {
    background: white;
    margin-top: 30px;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    overflow-x: auto;
}

.fee-table {
    width: 100%;
    border-collapse: collapse;
}

.fee-table th,
.fee-table td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.fee-table th {
    background: #f5f7fb;
}

.status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
    text-transform: capitalize;
}

.status-paid {
    background: #dff6e7;
    color: #16833b;
}

.status-partial {
    background: #fff1d6;
    color: #b06b00;
}

.status-unpaid {
    background: #ffe0e0;
    color: #c62828;
}

.no-fee {
    text-align: center;
    padding: 30px;
    color: #777;
}

.progress-card {
    background: white;
    margin-top: 30px;
    padding: 22px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.progress-head {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.bar {
    width: 100%;
    height: 12px;
    background: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}

.fill {
    height: 100%;
    background: #2563eb;
    border-radius: 10px;
}

@media (max-width: 800px) {

    .fee-summary {
        grid-template-columns: 1fr;
    }

    .fee-table {
        min-width: 700px;
    }
}

</style>

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

    <a href="dashboard.php">

        <span>🏠</span>
        <span>Dashboard</span>

    </a>


    <a href="profile.php">

        <span>👤</span>
        <span>Profile</span>

    </a>


    <a href="fee-details.php" class="active">

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
        Fee Details
    </h1>

    <p class="muted">

        View your complete fee information.

    </p>


    <!-- ==============================
         SUMMARY
    ================================= -->

    <div class="fee-summary">


        <!-- TOTAL FEE -->

        <div class="fee-summary-card">

            <p>Total Fee</p>

            <h2>
                Rs.
                <?php
                echo number_format($total_fee, 2);
                ?>
            </h2>

        </div>


        <!-- PAID -->

        <div class="fee-summary-card">

            <p>Paid Amount</p>

            <h2>
                Rs.
                <?php
                echo number_format($total_paid, 2);
                ?>
            </h2>

        </div>


        <!-- DUE -->

        <div class="fee-summary-card">

            <p>Due Amount</p>

            <h2>
                Rs.
                <?php
                echo number_format($total_due, 2);
                ?>
            </h2>

        </div>

    </div>


    <!-- ==============================
         FEE TABLE
    ================================= -->

    <div class="fee-table-box">

        <h2>
            Fee Breakdown
        </h2>


        <?php if (count($fees) > 0): ?>

        <table class="fee-table">

            <thead>

                <tr>

                    <th>Fee ID</th>

                    <th>Fee Type</th>

                    <th>Total Amount</th>

                    <th>Paid Amount</th>

                    <th>Due Amount</th>

                    <th>Status</th>

                </tr>

            </thead>


            <tbody>

            <?php foreach ($fees as $fee): ?>

                <?php

                $status = strtolower(
                    trim($fee['status'])
                );

                if ($status == "paid") {

                    $status_class = "status-paid";

                } elseif ($status == "partial") {

                    $status_class = "status-partial";

                } else {

                    $status_class = "status-unpaid";
                }

                ?>

                <tr>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $fee['fee_id']
                        );
                        ?>
                    </td>


                    <td>
                        <?php
                        echo htmlspecialchars(
                            $fee['fee_type']
                        );
                        ?>
                    </td>


                    <td>
                        Rs.
                        <?php
                        echo number_format(
                            (float)$fee['total_amount'],
                            2
                        );
                        ?>
                    </td>


                    <td>
                        Rs.
                        <?php
                        echo number_format(
                            (float)$fee['paid_amount'],
                            2
                        );
                        ?>
                    </td>


                    <td>
                        Rs.
                        <?php
                        echo number_format(
                            (float)$fee['due_amount'],
                            2
                        );
                        ?>
                    </td>


                    <td>

                        <span
                            class="status
                            <?php
                            echo $status_class;
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $fee['status']
                            );
                            ?>

                        </span>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>


        <?php else: ?>

            <div class="no-fee">

                No fee records found.

            </div>

        <?php endif; ?>

    </div>


    <!-- ==============================
         PAYMENT PROGRESS
    ================================= -->

    <div class="progress-card">

        <div class="progress-head">

            <div>

                <b>
                    Overall Fee Payment Progress
                </b>

                <p class="muted">

                    Your total payment progress

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
                style="width:
                <?php echo $progress; ?>%;">
            </div>

        </div>


        <div
            style="
            display:flex;
            justify-content:space-between;
            margin-top:12px;
            "
        >

            <span>

                Paid:
                Rs.
                <?php
                echo number_format(
                    $total_paid,
                    2
                );
                ?>

            </span>


            <span>

                Remaining:
                Rs.
                <?php
                echo number_format(
                    $total_due,
                    2
                );
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