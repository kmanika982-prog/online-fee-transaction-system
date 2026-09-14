```php
<?php

session_start();

include("../backend/db.php");

/* CHECK ADMIN LOGIN */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'] ?? "Administrator";


/* TOTAL STUDENTS */
$student_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM students"
);

$student_data = mysqli_fetch_assoc($student_query);

$total_students = $student_data['total'] ?? 0;


/* FEE SUMMARY */
$fee_query = mysqli_query(
    $conn,
    "SELECT
        COALESCE(SUM(total_amount),0) AS total_fee,
        COALESCE(SUM(paid_amount),0) AS paid_amount,
        COALESCE(SUM(due_amount),0) AS due_amount
     FROM fee_details"
);

$fee_data = mysqli_fetch_assoc($fee_query);

$total_fee = $fee_data['total_fee'] ?? 0;
$paid_amount = $fee_data['paid_amount'] ?? 0;
$due_amount = $fee_data['due_amount'] ?? 0;


/* TOTAL PAYMENTS */
$payment_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM payments"
);

$payment_data = mysqli_fetch_assoc($payment_query);

$total_payments = $payment_data['total'] ?? 0;

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>


<style>

/* GLOBAL */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f7fb;
    color: #172b4d;
}


/* TOPBAR */

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


/* SIDEBAR */

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


/* MAIN */

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


/* CARDS */

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

    padding: 18px;

    box-shadow:
        0 3px 10px #0000000d;
}

.card-content {

    display: flex;

    align-items: center;

    gap: 13px;
}

.icon {

    width: 46px;
    height: 46px;

    border-radius: 50%;

    display: grid;

    place-items: center;

    font-size: 21px;
}

.blue {
    background: #e7f0ff;
}

.green {
    background: #e5f7eb;
}

.red {
    background: #ffe8e8;
}

.orange {
    background: #fff1dc;
}

.card p {

    font-size: 12px;

    color: #777;

    margin-bottom: 5px;
}

.card h2 {

    font-size: 18px;
}


/* QUICK ACTION */

.section-title {

    font-size: 17px;

    margin: 30px 0 14px;
}

.actions {

    display: grid;

    grid-template-columns:
        repeat(4, 145px);

    gap: 15px;
}

.action {

    background: white;

    border: 1px solid #e1e6ed;

    border-radius: 9px;

    min-height: 110px;

    display: grid;

    place-items: center;

    align-content: center;

    gap: 10px;

    text-decoration: none;

    color: #172b4d;

    font-size: 12px;

    font-weight: 700;
}

.action:hover {

    border-color: #0866c6;

    transform: translateY(-2px);
}


/* FOOTER */

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


/* MOBILE */

@media (max-width: 900px) {

    .cards {

        grid-template-columns:
            repeat(2, 1fr);
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

    .actions {

        grid-template-columns:
            repeat(2, 1fr);
    }

}

</style>

</head>


<body>


<!-- TOPBAR -->

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
            echo htmlspecialchars($admin_name);
            ?>

        </span>

    </div>

</header>


<!-- SIDEBAR -->

<aside class="sidebar">

    <a href="dashboard.php"
       class="active">

        🏠
        <span>Dashboard</span>

    </a>


    <a href="students.php">

        👨‍🎓
        <span>Students</span>

    </a>


    <a href="fees.php">

        💰
        <span>Fee Management</span>

    </a>


    <a href="payments.php">

        💳
        <span>Payments</span>

    </a>


    <a href="reports.php">

        📊
        <span>Reports</span>

    </a>


    <a href="logout.php">

        🚪
        <span>Logout</span>

    </a>

</aside>


<!-- MAIN -->

<main class="main">

    <h1>
        Admin Dashboard
    </h1>

    <p class="muted">

        Welcome,
        <?php
        echo htmlspecialchars($admin_name);
        ?>.
        Manage your Online Fee Transaction System.

    </p>


    <!-- CARDS -->

    <div class="cards">


        <!-- STUDENTS -->

        <div class="card">

            <div class="card-content">

                <div class="icon blue">
                    👨‍🎓
                </div>

                <div>

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

            </div>

        </div>


        <!-- TOTAL FEE -->

        <div class="card">

            <div class="card-content">

                <div class="icon orange">
                    💰
                </div>

                <div>

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

            </div>

        </div>


        <!-- PAID -->

        <div class="card">

            <div class="card-content">

                <div class="icon green">
                    ✅
                </div>

                <div>

                    <p>
                        Paid Amount
                    </p>

                    <h2>

                        Rs.
                        <?php
                        echo number_format(
                            $paid_amount,
                            2
                        );
                        ?>

                    </h2>

                </div>

            </div>

        </div>


        <!-- DUE -->

        <div class="card">

            <div class="card-content">

                <div class="icon red">
                    ⚠️
                </div>

                <div>

                    <p>
                        Due Amount
                    </p>

                    <h2>

                        Rs.
                        <?php
                        echo number_format(
                            $due_amount,
                            2
                        );
                        ?>

                    </h2>

                </div>

            </div>

        </div>

    </div>


    <!-- QUICK ACTIONS -->

    <h2 class="section-title">

        Quick Management

    </h2>


    <div class="actions">


        <a
            href="students.php"
            class="action"
        >

            <span style="font-size:28px;">
                👨‍🎓
            </span>

            <span>
                Manage Students
            </span>

        </a>


        <a
            href="fees.php"
            class="action"
        >

            <span style="font-size:28px;">
                💰
            </span>

            <span>
                Manage Fees
            </span>

        </a>


        <a
            href="payments.php"
            class="action"
        >

            <span style="font-size:28px;">
                💳
            </span>

            <span>
                View Payments
            </span>

        </a>


        <a
            href="reports.php"
            class="action"
        >

            <span style="font-size:28px;">
                📊
            </span>

            <span>
                View Reports
            </span>

        </a>


    </div>


</main>


<!-- FOOTER -->

<footer class="footer">

    © 2026 Online Fee Transaction System.
    Administration Panel.

</footer>


</body>

</html>
```
