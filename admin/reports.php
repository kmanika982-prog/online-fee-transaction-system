<?php

session_start();

include("../backend/db.php");


/* ==========================================
   CHECK ADMIN LOGIN
========================================== */

if (!isset($_SESSION['admin_id'])) {

    header("Location: login.php");
    exit();

}


$admin_name =
    $_SESSION['admin_name'] ?? 'Administrator';


/* ==========================================
   STUDENT COUNT
========================================== */

$student_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total_students
     FROM students"
);

$student_data =
    mysqli_fetch_assoc($student_query);

$total_students =
    $student_data['total_students'] ?? 0;


/* ==========================================
   FEE SUMMARY
========================================== */

$fee_query = mysqli_query(
    $conn,
    "SELECT
        COALESCE(SUM(total_amount),0) AS total_fee,
        COALESCE(SUM(paid_amount),0) AS total_paid,
        COALESCE(SUM(due_amount),0) AS total_due
     FROM fee_details"
);

$fee_data =
    mysqli_fetch_assoc($fee_query);


$total_fee =
    (float)($fee_data['total_fee'] ?? 0);

$total_paid =
    (float)($fee_data['total_paid'] ?? 0);

$total_due =
    (float)($fee_data['total_due'] ?? 0);


/* ==========================================
   PAYMENT COUNT
========================================== */

$payment_query = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total_payments
     FROM payments"
);

$payment_data =
    mysqli_fetch_assoc($payment_query);

$total_payments =
    $payment_data['total_payments'] ?? 0;


/* ==========================================
   SUCCESSFUL PAYMENTS
========================================== */

$success_query = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS successful_payments,
        COALESCE(SUM(amount),0) AS successful_amount
     FROM payments
     WHERE LOWER(status)
     IN ('success','successful','paid')"
);

$success_data =
    mysqli_fetch_assoc($success_query);


$successful_payments =
    $success_data['successful_payments'] ?? 0;

$successful_amount =
    (float)($success_data['successful_amount'] ?? 0);


/* ==========================================
   PENDING PAYMENTS
========================================== */

$pending_query = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS pending_payments
     FROM payments
     WHERE LOWER(status) = 'pending'"
);

$pending_data =
    mysqli_fetch_assoc($pending_query);

$pending_payments =
    $pending_data['pending_payments'] ?? 0;


/* ==========================================
   FAILED PAYMENTS
========================================== */

$failed_query = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS failed_payments
     FROM payments
     WHERE LOWER(status) = 'failed'"
);

$failed_data =
    mysqli_fetch_assoc($failed_query);

$failed_payments =
    $failed_data['failed_payments'] ?? 0;


/* ==========================================
   FEE STATUS
========================================== */

$paid_fee_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM fee_details
     WHERE LOWER(status) = 'paid'"
);

$paid_fee_data =
    mysqli_fetch_assoc($paid_fee_query);

$paid_fee_records =
    $paid_fee_data['total'] ?? 0;


$partial_fee_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM fee_details
     WHERE LOWER(status) = 'partial'"
);

$partial_fee_data =
    mysqli_fetch_assoc($partial_fee_query);

$partial_fee_records =
    $partial_fee_data['total'] ?? 0;


$unpaid_fee_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM fee_details
     WHERE LOWER(status) = 'unpaid'"
);

$unpaid_fee_data =
    mysqli_fetch_assoc($unpaid_fee_query);

$unpaid_fee_records =
    $unpaid_fee_data['total'] ?? 0;


/* ==========================================
   PAYMENT METHODS
========================================== */

$method_query = mysqli_query(
    $conn,
    "SELECT
        payment_method,
        COUNT(*) AS total_count,
        COALESCE(SUM(amount),0) AS total_amount
     FROM payments
     GROUP BY payment_method
     ORDER BY total_amount DESC"
);


/* ==========================================
   RECENT PAYMENTS
========================================== */

$recent_query = mysqli_query(
    $conn,
    "SELECT
        p.transaction_id,
        p.student_id,
        s.student_name,
        p.amount,
        p.payment_method,
        p.payment_date,
        p.status
     FROM payments p
     LEFT JOIN students s
        ON p.student_id = s.student_id
     ORDER BY p.payment_id DESC
     LIMIT 10"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Reports</title>


<style>

/* ==========================================
   GLOBAL
========================================== */

* {

    box-sizing: border-box;

    margin: 0;

    padding: 0;

}

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f7fb;

    color: #172b4d;

}


/* ==========================================
   TOPBAR
========================================== */

.topbar {

    height: 62px;

    background: #063b75;

    color: white;

    display: flex;

    align-items: center;

    padding: 0 20px;

    position: fixed;

    top: 0;

    left: 0;

    right: 0;

    z-index: 10;

}

.brand {

    font-size: 15px;

    font-weight: 700;

    flex: 1;

}

.admin-user {

    display: flex;

    align-items: center;

    gap: 8px;

    font-size: 12px;

}

.avatar {

    width: 36px;

    height: 36px;

    border-radius: 50%;

    background: white;

    display: grid;

    place-items: center;

    font-size: 20px;

}


/* ==========================================
   SIDEBAR
========================================== */

.sidebar {

    position: fixed;

    top: 62px;

    bottom: 42px;

    left: 0;

    width: 215px;

    background: #063b75;

    padding-top: 12px;

}

.sidebar a {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px 18px;

    color: white;

    text-decoration: none;

    font-size: 12px;

}

.sidebar a:hover,
.sidebar a.active {

    background: #0866c6;

    border-left: 4px solid white;

}


/* ==========================================
   MAIN
========================================== */

.main {

    margin-left: 215px;

    padding: 88px 32px 70px;

    min-height: 100vh;

}

.main h1 {

    font-size: 23px;

    margin-bottom: 7px;

}

.muted {

    color: #6b7280;

    font-size: 12px;

}


/* ==========================================
   SUMMARY CARDS
========================================== */

.cards {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 16px;

    margin-top: 25px;

}

.card {

    background: white;

    border: 1px solid #e1e6ed;

    border-radius: 10px;

    padding: 20px;

    box-shadow:
        0 3px 10px #0000000d;

}

.card p {

    color: #6b7280;

    font-size: 12px;

    margin-bottom: 8px;

}

.card h2 {

    font-size: 21px;

}


/* ==========================================
   COLORS
========================================== */

.blue {

    border-left: 5px solid #0866c6;

}

.green {

    border-left: 5px solid #168542;

}

.orange {

    border-left: 5px solid #d97706;

}

.red {

    border-left: 5px solid #dc3545;

}


/* ==========================================
   REPORT GRID
========================================== */

.report-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 20px;

    margin-top: 25px;

}


/* ==========================================
   REPORT BOX
========================================== */

.report-box {

    background: white;

    border: 1px solid #e1e6ed;

    border-radius: 10px;

    padding: 20px;

    box-shadow:
        0 3px 10px #0000000d;

}

.report-box h2 {

    font-size: 17px;

    margin-bottom: 18px;

}


/* ==========================================
   REPORT ROW
========================================== */

.report-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 12px 0;

    border-bottom:
        1px solid #edf0f4;

    font-size: 12px;

}

.report-row:last-child {

    border-bottom: none;

}

.report-label {

    color: #64748b;

}

.report-value {

    font-weight: 700;

}


/* ==========================================
   STATUS
========================================== */

.status {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 5px;

    font-size: 10px;

    font-weight: 700;

}

.success {

    background: #dff5e6;

    color: #168542;

}

.pending {

    background: #fff0d5;

    color: #b56a00;

}

.failed {

    background: #ffe8e8;

    color: #b42318;

}


/* ==========================================
   TABLE
========================================== */

.table-box {

    background: white;

    border: 1px solid #e1e6ed;

    border-radius: 10px;

    margin-top: 25px;

    overflow-x: auto;

    box-shadow:
        0 3px 10px #0000000d;

}

table {

    width: 100%;

    min-width: 900px;

    border-collapse: collapse;

    font-size: 12px;

}

th,
td {

    padding: 12px;

    border-bottom:
        1px solid #e1e6ed;

    text-align: left;

}

th {

    background: #edf3fa;

}

tr:hover {

    background: #f8fbff;

}


/* ==========================================
   FOOTER
========================================== */

.footer {

    position: fixed;

    bottom: 0;

    left: 215px;

    right: 0;

    height: 42px;

    background: #063b75;

    color: white;

    display: grid;

    place-items: center;

    font-size: 10px;

}


/* ==========================================
   MOBILE
========================================== */

@media (max-width: 900px) {

    .cards {

        grid-template-columns:
            repeat(2, 1fr);

    }

    .report-grid {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 650px) {

    .sidebar {

        display: none;

    }

    .main {

        margin-left: 0;

        padding:
            82px 15px 65px;

    }

    .footer {

        left: 0;

    }

    .cards {

        grid-template-columns: 1fr;

    }

    .admin-user span {

        display: none;

    }

}

</style>

</head>


<body>


<!-- ==========================================
     TOPBAR
========================================== -->

<header class="topbar">

    <div class="brand">

        Online Fee Transaction System
        - Administration

    </div>


    <div class="admin-user">

        <div class="avatar">

            👨‍💼

        </div>


        <span>

            <?php

            echo htmlspecialchars(
                $admin_name
            );

            ?>

        </span>

    </div>

</header>


<!-- ==========================================
     SIDEBAR
========================================== -->

<aside class="sidebar">


    <a href="dashboard.php">

        🏠

        <span>
            Dashboard
        </span>

    </a>


    <a href="students.php">

        👨‍🎓

        <span>
            Students
        </span>

    </a>


    <a href="fees.php">

        💰

        <span>
            Fee Management
        </span>

    </a>


    <a href="payments.php">

        💳

        <span>
            Payments
        </span>

    </a>


    <a href="reports.php"
       class="active">

        📊

        <span>
            Reports
        </span>

    </a>


    <a href="logout.php">

        🚪

        <span>
            Logout
        </span>

    </a>

</aside>


<!-- ==========================================
     MAIN
========================================== -->

<main class="main">


    <h1>

        Reports & Analytics

    </h1>


    <p class="muted">

        Overview of students, fees and payment transactions.

    </p>


    <!-- ======================================
         SUMMARY CARDS
    ======================================= -->

    <div class="cards">


        <div class="card blue">

            <p>
                Total Students
            </p>

            <h2>

                <?php

                echo number_format(
                    $total_students
                );

                ?>

            </h2>

        </div>


        <div class="card blue">

            <p>
                Total Fee
            </p>

            <h2>

                Rs.
                <?php

                echo number_format(
                    $total_fee,
                    2
                );

                ?>

            </h2>

        </div>


        <div class="card green">

            <p>
                Total Paid
            </p>

            <h2>

                Rs.
                <?php

                echo number_format(
                    $total_paid,
                    2
                );

                ?>

            </h2>

        </div>


        <div class="card red">

            <p>
                Total Due
            </p>

            <h2>

                Rs.
                <?php

                echo number_format(
                    $total_due,
                    2
                );

                ?>

            </h2>

        </div>

    </div>


    <!-- ======================================
         REPORT SECTIONS
    ======================================= -->

    <div class="report-grid">


        <!-- PAYMENT SUMMARY -->

        <div class="report-box">

            <h2>
                Payment Summary
            </h2>


            <div class="report-row">

                <span class="report-label">
                    Total Transactions
                </span>

                <span class="report-value">

                    <?php
                    echo number_format(
                        $total_payments
                    );
                    ?>

                </span>

            </div>


            <div class="report-row">

                <span class="report-label">
                    Successful Payments
                </span>

                <span class="report-value">

                    <?php
                    echo number_format(
                        $successful_payments
                    );
                    ?>

                </span>

            </div>


            <div class="report-row">

                <span class="report-label">
                    Successful Amount
                </span>

                <span class="report-value">

                    Rs.
                    <?php
                    echo number_format(
                        $successful_amount,
                        2
                    );
                    ?>

                </span>

            </div>


            <div class="report-row">

                <span class="report-label">
                    Pending Payments
                </span>

                <span class="report-value">

                    <?php
                    echo number_format(
                        $pending_payments
                    );
                    ?>

                </span>

            </div>


            <div class="report-row">

                <span class="report-label">
                    Failed Payments
                </span>

                <span class="report-value">

                    <?php
                    echo number_format(
                        $failed_payments
                    );
                    ?>

                </span>

            </div>

        </div>


        <!-- FEE STATUS -->

        <div class="report-box">

            <h2>
                Fee Status
            </h2>


            <div class="report-row">

                <span class="report-label">
                    Paid Fee Records
                </span>

                <span class="status success">

                    <?php
                    echo $paid_fee_records;
                    ?>

                    Paid

                </span>

            </div>


            <div class="report-row">

                <span class="report-label">
                    Partial Fee Records
                </span>

                <span class="status pending">

                    <?php
                    echo $partial_fee_records;
                    ?>

                    Partial

                </span>

            </div>


            <div class="report-row">

                <span class="report-label">
                    Unpaid Fee Records
                </span>

                <span class="status failed">

                    <?php
                    echo $unpaid_fee_records;
                    ?>

                    Unpaid

                </span>

            </div>


            <div class="report-row">

                <span class="report-label">
                    Total Fee
                </span>

                <span class="report-value">

                    Rs.
                    <?php
                    echo number_format(
                        $total_fee,
                        2
                    );
                    ?>

                </span>

            </div>


            <div class="report-row">

                <span class="report-label">
                    Remaining Due
                </span>

                <span class="report-value">

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


    </div>


    <!-- ======================================
         PAYMENT METHOD REPORT
    ======================================= -->

    <div class="report-box"
         style="margin-top:25px;">

        <h2>
            Payment Method Summary
        </h2>


        <?php

        if (
            $method_query &&
            mysqli_num_rows(
                $method_query
            ) > 0
        ) {

            while (
                $method =
                mysqli_fetch_assoc(
                    $method_query
                )
            ) {

        ?>

            <div class="report-row">

                <span class="report-label">

                    <?php

                    echo htmlspecialchars(
                        $method[
                            'payment_method'
                        ]
                        ?? 'Unknown'
                    );

                    ?>

                </span>


                <span>

                    <?php

                    echo number_format(
                        $method[
                            'total_count'
                        ]
                    );

                    ?>

                    transactions

                    &nbsp; | &nbsp;

                    <strong>

                        Rs.
                        <?php

                        echo number_format(
                            (float)
                            $method[
                                'total_amount'
                            ],
                            2
                        );

                        ?>

                    </strong>

                </span>

            </div>

        <?php

            }

        } else {

        ?>

            <p class="muted">

                No payment method records found.

            </p>

        <?php

        }

        ?>

    </div>


    <!-- ======================================
         RECENT PAYMENTS
    ======================================= -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>
                        Transaction ID
                    </th>

                    <th>
                        Student ID
                    </th>

                    <th>
                        Student Name
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Method
                    </th>

                    <th>
                        Date
                    </th>

                    <th>
                        Status
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php

            if (
                $recent_query &&
                mysqli_num_rows(
                    $recent_query
                ) > 0
            ) {

                while (
                    $payment =
                    mysqli_fetch_assoc(
                        $recent_query
                    )
                ) {


                    $status =
                        strtolower(
                            trim(
                                $payment['status']
                                ?? ''
                            )
                        );


                    if (
                        $status === 'success' ||
                        $status === 'successful' ||
                        $status === 'paid'
                    ) {

                        $status_class =
                            'success';

                    } elseif (
                        $status === 'pending'
                    ) {

                        $status_class =
                            'pending';

                    } elseif (
                        $status === 'failed'
                    ) {

                        $status_class =
                            'failed';

                    } else {

                        $status_class = '';

                    }

            ?>

                <tr>

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $payment[
                                'transaction_id'
                            ]
                            ?? 'N/A'
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $payment[
                                'student_id'
                            ]
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $payment[
                                'student_name'
                            ]
                            ?? 'N/A'
                        );

                        ?>

                    </td>


                    <td>

                        Rs.
                        <?php

                        echo number_format(
                            (float)
                            $payment['amount'],
                            2
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $payment[
                                'payment_method'
                            ]
                            ?? 'N/A'
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $payment[
                                'payment_date'
                            ]
                            ?? 'N/A'
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
                                $payment[
                                    'status'
                                ]
                                ?? 'N/A'
                            );

                            ?>

                        </span>

                    </td>

                </tr>

            <?php

                }

            } else {

            ?>

                <tr>

                    <td
                        colspan="7"
                        style="
                        text-align:center;
                        padding:30px;
                        color:#6b7280;
                        "
                    >

                        No payment records found.

                    </td>

                </tr>

            <?php

            }

            ?>

            </tbody>

        </table>

    </div>


</main>


<!-- ==========================================
     FOOTER
========================================== -->

<footer class="footer">

    © 2026 Online Fee Transaction System.
    Administration Panel.

</footer>


</body>

</html>