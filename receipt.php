<?php

session_start();


// ==========================================
// CHECK LOGIN
// ==========================================

if (!isset($_SESSION['student_id'])) {

    header("Location: index.html");
    exit();

}


// ==========================================
// DATABASE CONNECTION
// ==========================================

include("backend/db.php");


$student_id = $_SESSION['student_id'];


// ==========================================
// GET TRANSACTION ID
// ==========================================

if (!isset($_GET['transaction_id']) || empty($_GET['transaction_id'])) {

    die("Transaction ID is missing.");

}

$transaction_id = trim($_GET['transaction_id']);


// ==========================================
// GET PAYMENT + STUDENT + FEE INFORMATION
// ==========================================

$stmt = mysqli_prepare(
    $conn,

    "SELECT
        p.transaction_id,
        p.student_id,
        p.fee_id,
        p.amount,
        p.payment_method,
        p.payment_date,
        p.status,

        s.student_name,
        s.course,
        s.semester,

        f.fee_type

     FROM payments p

     INNER JOIN students s
        ON p.student_id = s.student_id

     LEFT JOIN fee_details f
        ON p.fee_id = f.fee_id

     WHERE p.transaction_id = ?
     AND p.student_id = ?"
);


if (!$stmt) {

    die(
        "Receipt query failed: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $transaction_id,
    $student_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$payment = mysqli_fetch_assoc($result);


// ==========================================
// CHECK PAYMENT
// ==========================================

if (!$payment) {

    die("Payment receipt not found.");

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Payment Receipt</title>

<link rel="stylesheet" href="style.css">


<style>

/* ==========================================
   RECEIPT
========================================== */

.receipt-container {

    max-width: 750px;

    margin: 30px auto;

}


.receipt {

    background: #ffffff;

    border-radius: 12px;

    padding: 35px;

    box-shadow:
        0 4px 20px rgba(0, 0, 0, 0.08);

}


.receipt-header {

    text-align: center;

    border-bottom: 2px solid #eeeeee;

    padding-bottom: 20px;

    margin-bottom: 25px;

}


.receipt-header h1 {

    margin: 8px 0;

}


.receipt-header p {

    margin: 5px 0;

}


.success-icon {

    font-size: 55px;

}


.receipt-section {

    margin-top: 25px;

}


.receipt-section h3 {

    margin-bottom: 10px;

}


.receipt-row {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 13px 0;

    border-bottom: 1px solid #eeeeee;

}


.receipt-row:last-child {

    border-bottom: none;

}


.label {

    font-weight: bold;

}


.value {

    text-align: right;

    word-break: break-word;

}


.amount-row {

    font-size: 20px;

}


.status-paid {

    color: green;

    font-weight: bold;

}


.status-pending {

    color: orange;

    font-weight: bold;

}


.status-failed {

    color: red;

    font-weight: bold;

}


.receipt-actions {

    text-align: center;

    margin-top: 30px;

}


.receipt-actions .btn {

    display: inline-block;

    margin: 5px;

    text-decoration: none;

}


.receipt-note {

    text-align: center;

    margin-top: 25px;

    padding-top: 15px;

    border-top: 1px solid #eeeeee;

    font-size: 14px;

}


/* ==========================================
   PRINT
========================================== */

@media print {


    .topbar,
    .sidebar,
    .footer,
    .receipt-actions {

        display: none !important;

    }


    .main {

        margin: 0 !important;

        padding: 0 !important;

    }


    .receipt-container {

        margin: 0;

        max-width: 100%;

    }


    .receipt {

        box-shadow: none;

        border: 1px solid #dddddd;

    }

}

</style>

</head>


<body>


<!-- ==========================================
     HEADER
========================================== -->

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
                    $payment['student_name']
                );

                ?>

            </b>


            <small>

                <?php

                echo htmlspecialchars(
                    $payment['course']
                    . " - "
                    . $payment['semester']
                );

                ?>

            </small>


        </div>


    </div>


</header>



<!-- ==========================================
     SIDEBAR
========================================== -->

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


    <a href="payment-history.php"
       class="active">

        <span>📋</span>

        <span>Payment History</span>

    </a>


    <a href="backend/logout.php">

        <span>🚪</span>

        <span>Logout</span>

    </a>


</aside>



<!-- ==========================================
     MAIN
========================================== -->

<main class="main">


<div class="receipt-container">


    <div class="receipt">


        <!-- ==================================
             RECEIPT HEADER
        ================================== -->

        <div class="receipt-header">


            <div class="success-icon">

                <?php

                if (
                    strtolower($payment['status'])
                    == "paid"
                ) {

                    echo "✅";

                } else {

                    echo "🧾";

                }

                ?>

            </div>


            <h1>

                Payment Receipt

            </h1>


            <p>

                <strong>
                    Online Fee Transaction System
                </strong>

            </p>


            <p class="muted">

                Payment transaction details

            </p>


        </div>



        <!-- ==================================
             STUDENT INFORMATION
        ================================== -->

        <div class="receipt-section">


            <h3>

                Student Information

            </h3>


            <div class="receipt-row">


                <span class="label">

                    Student Name

                </span>


                <span class="value">

                    <?php

                    echo htmlspecialchars(
                        $payment['student_name']
                    );

                    ?>

                </span>


            </div>


            <div class="receipt-row">


                <span class="label">

                    Student ID

                </span>


                <span class="value">

                    <?php

                    echo htmlspecialchars(
                        $payment['student_id']
                    );

                    ?>

                </span>


            </div>


            <div class="receipt-row">


                <span class="label">

                    Course

                </span>


                <span class="value">

                    <?php

                    echo htmlspecialchars(
                        $payment['course']
                    );

                    ?>

                </span>


            </div>


            <div class="receipt-row">


                <span class="label">

                    Semester

                </span>


                <span class="value">

                    <?php

                    echo htmlspecialchars(
                        $payment['semester']
                    );

                    ?>

                </span>


            </div>


        </div>



        <!-- ==================================
             PAYMENT INFORMATION
        ================================== -->

        <div class="receipt-section">


            <h3>

                Payment Information

            </h3>


            <div class="receipt-row">


                <span class="label">

                    Transaction ID

                </span>


                <span class="value">

                    <?php

                    echo htmlspecialchars(
                        $payment['transaction_id']
                    );

                    ?>

                </span>


            </div>


            <div class="receipt-row">


                <span class="label">

                    Fee Type

                </span>


                <span class="value">


                    <?php

                    if (
                        !empty(
                            $payment['fee_type']
                        )
                    ) {

                        echo htmlspecialchars(
                            $payment['fee_type']
                        );

                    } else {

                        echo "Fee Payment";

                    }

                    ?>


                </span>


            </div>


            <div class="receipt-row amount-row">


                <span class="label">

                    Amount Paid

                </span>


                <span class="value">


                    <strong>

                        Rs.
                        <?php

                        echo number_format(
                            (float)$payment['amount'],
                            2
                        );

                        ?>

                    </strong>


                </span>


            </div>


            <div class="receipt-row">


                <span class="label">

                    Payment Method

                </span>


                <span class="value">

                    <?php

                    echo htmlspecialchars(
                        $payment['payment_method']
                    );

                    ?>

                </span>


            </div>


            <div class="receipt-row">


                <span class="label">

                    Payment Date

                </span>


                <span class="value">

                    <?php

                    if (
                        !empty(
                            $payment['payment_date']
                        )
                    ) {

                        echo date(
                            "Y-m-d H:i:s",
                            strtotime(
                                $payment['payment_date']
                            )
                        );

                    } else {

                        echo "-";

                    }

                    ?>

                </span>


            </div>


            <div class="receipt-row">


                <span class="label">

                    Status

                </span>


                <span class="value">


                    <?php

                    $status =
                        strtolower(
                            $payment['status']
                        );


                    if (
                        $status == "paid" ||
                        $status == "success" ||
                        $status == "completed"
                    ) {

                    ?>

                        <span class="status-paid">

                            ✓ Paid

                        </span>

                    <?php

                    }

                    elseif ($status == "pending") {

                    ?>

                        <span class="status-pending">

                            ⏳ Pending

                        </span>

                    <?php

                    }

                    elseif ($status == "failed") {

                    ?>

                        <span class="status-failed">

                            ✕ Failed

                        </span>

                    <?php

                    }

                    else {

                        echo htmlspecialchars(
                            ucfirst($payment['status'])
                        );

                    }

                    ?>

                </span>


            </div>


        </div>



        <!-- ==================================
             ACTION BUTTONS
        ================================== -->

        <div class="receipt-actions">


            <button
                type="button"
                class="btn"
                onclick="window.print()">

                🖨️ Print Receipt

            </button>


            <a
                href="payment-history.php"
                class="btn">

                ← Payment History

            </a>


            <a
                href="dashboard.php"
                class="btn">

                🏠 Dashboard

            </a>


        </div>



        <!-- ==================================
             NOTE
        ================================== -->

        <div class="receipt-note">


            <p class="muted">

                Thank you for your payment.

            </p>


            <p class="muted">

                This is a computer-generated receipt.

            </p>


        </div>


    </div>


</div>


</main>



<!-- ==========================================
     FOOTER
========================================== -->

<footer class="footer">

    © 2026 Online Fee Transaction System.
    All rights reserved.

</footer>


</body>

</html>