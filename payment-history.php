<?php

session_start();

// Check login
if (!isset($_SESSION['student_id'])) {
    header("Location: index.html");
    exit();
}

// Database connection
include("backend/db.php");

$student_id = $_SESSION['student_id'];

// Get student information
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

// Get payment history
$payment_stmt = mysqli_prepare(
    $conn,
    "SELECT
        p.payment_id,
        p.transaction_id,
        p.student_id,
        p.fee_id,
        p.amount,
        p.payment_method,
        p.payment_date,
        p.status,
        f.fee_type
     FROM payments p
     LEFT JOIN fee_details f
        ON p.fee_id = f.fee_id
     WHERE p.student_id = ?
     ORDER BY p.payment_date DESC"
);

if (!$payment_stmt) {
    die("Payment query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($payment_stmt, "s", $student_id);
mysqli_stmt_execute($payment_stmt);

$payment_result = mysqli_stmt_get_result($payment_stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Payment History</title>

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

    <a href="dashboard.php">
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

    <a href="payment-history.php" class="active">
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
        Payment History
    </h1>

    <p class="muted">
        View your previous fee payment transactions.
    </p>


    <!-- PAYMENT TABLE -->

    <div class="card"
         style="margin-top:22px; overflow-x:auto;">

        <table class="payment-table">

            <thead>

                <tr>

                    <th>S.N.</th>

                    <th>Transaction ID</th>

                    <th>Fee Type</th>

                    <th>Amount</th>

                    <th>Payment Method</th>

                    <th>Payment Date</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php

            $sn = 1;

            if (mysqli_num_rows($payment_result) > 0) {

                while ($payment = mysqli_fetch_assoc($payment_result)) {

            ?>

                <tr>

                    <!-- Serial Number -->

                    <td>
                        <?php echo $sn++; ?>
                    </td>


                    <!-- Transaction ID -->

                    <td>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $payment['transaction_id']
                            );

                            ?>

                        </strong>

                    </td>


                    <!-- Fee Type -->

                    <td>

                        <?php

                        if (!empty($payment['fee_type'])) {

                            echo htmlspecialchars(
                                $payment['fee_type']
                            );

                        } else {

                            echo "Fee Payment";

                        }

                        ?>

                    </td>


                    <!-- Amount -->

                    <td>

                        <strong>

                            Rs.

                            <?php

                            echo number_format(
                                (float)$payment['amount'],
                                2
                            );

                            ?>

                        </strong>

                    </td>


                    <!-- Payment Method -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $payment['payment_method']
                        );

                        ?>

                    </td>


                    <!-- Payment Date -->

                    <td>

                        <?php

                        if (!empty($payment['payment_date'])) {

                            echo date(
                                "Y-m-d",
                                strtotime(
                                    $payment['payment_date']
                                )
                            );

                        } else {

                            echo "-";

                        }

                        ?>

                    </td>


                    <!-- Status -->

                    <td>

                        <?php

                        $status = strtolower(
                            $payment['status']
                        );

                        if (
                            $status == "paid" ||
                            $status == "success" ||
                            $status == "completed"
                        ) {

                            echo '<span class="status paid">
                                    Paid
                                  </span>';

                        } elseif ($status == "pending") {

                            echo '<span class="status pending">
                                    Pending
                                  </span>';

                        } elseif ($status == "failed") {

                            echo '<span class="status failed">
                                    Failed
                                  </span>';

                        } else {

                            echo '<span class="status">' .
                                htmlspecialchars(
                                    $payment['status']
                                ) .
                                '</span>';

                        }

                        ?>

                    </td>


                    <!-- VIEW RECEIPT -->

                    <td>

                        <a
                            href="receipt.php?transaction_id=<?php
                                echo urlencode(
                                    $payment['transaction_id']
                                );
                            ?>"
                            class="btn"
                            style="
                                padding:8px 12px;
                                font-size:13px;
                                display:inline-block;
                                text-decoration:none;
                            "
                        >

                            🧾 View Receipt

                        </a>

                    </td>

                </tr>

            <?php

                }

            } else {

            ?>

                <tr>

                    <td colspan="8"
                        style="
                            text-align:center;
                            padding:30px;
                        ">

                        <div style="font-size:40px;">
                            📋
                        </div>

                        <h3>
                            No Payment History
                        </h3>

                        <p class="muted">
                            You have not made any payments yet.
                        </p>

                    </td>

                </tr>

            <?php

            }

            ?>

            </tbody>

        </table>

    </div>


    <!-- BACK TO DASHBOARD -->

    <div style="margin-top:20px;">

        <a href="dashboard.php" class="btn">

            ← Back to Dashboard

        </a>

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