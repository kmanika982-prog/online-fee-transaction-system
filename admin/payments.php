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
   SEARCH
========================================== */

$search =
    trim($_GET['search'] ?? '');


/* ==========================================
   PAYMENT QUERY
========================================== */

if ($search !== '') {

    $stmt = mysqli_prepare(
        $conn,

        "SELECT
            p.payment_id,
            p.transaction_id,
            p.student_id,
            s.student_name,
            p.fee_id,
            f.fee_type,
            p.amount,
            p.payment_method,
            p.payment_date,
            p.status
         FROM payments p
         LEFT JOIN students s
            ON p.student_id = s.student_id
         LEFT JOIN fee_details f
            ON p.fee_id = f.fee_id
         WHERE
            p.transaction_id LIKE ?
            OR p.student_id LIKE ?
            OR s.student_name LIKE ?
            OR p.payment_method LIKE ?
            OR p.status LIKE ?
         ORDER BY p.payment_id DESC"
    );


    if (!$stmt) {

        die(
            "Payment query failed: " .
            mysqli_error($conn)
        );

    }


    $search_value =
        "%" . $search . "%";


    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $search_value,
        $search_value,
        $search_value,
        $search_value,
        $search_value
    );


    mysqli_stmt_execute($stmt);


    $result =
        mysqli_stmt_get_result($stmt);

} else {

    $result = mysqli_query(
        $conn,

        "SELECT
            p.payment_id,
            p.transaction_id,
            p.student_id,
            s.student_name,
            p.fee_id,
            f.fee_type,
            p.amount,
            p.payment_method,
            p.payment_date,
            p.status
         FROM payments p
         LEFT JOIN students s
            ON p.student_id = s.student_id
         LEFT JOIN fee_details f
            ON p.fee_id = f.fee_id
         ORDER BY p.payment_id DESC"
    );


    if (!$result) {

        die(
            "Payment query failed: " .
            mysqli_error($conn)
        );

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Payment Management</title>


<style>

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

    color: #fff;

    display: flex;

    align-items: center;

    padding: 0 20px;

    position: fixed;

    top: 0;

    left: 0;

    right: 0;

    z-index: 10;

    box-shadow:
        0 2px 8px #0002;

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

    background: #fff;

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

    z-index: 9;

}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px 18px;

    color: #fff;

    text-decoration: none;

    font-size: 12px;

    transition: 0.2s;

}


.sidebar a:hover,
.sidebar a.active {

    background: #0866c6;

    border-left: 4px solid #fff;

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
   SEARCH
========================================== */

.search-box {

    margin-top: 25px;

    background: #fff;

    border: 1px solid #e1e6ed;

    border-radius: 10px;

    padding: 18px;

    display: flex;

    gap: 10px;

    box-shadow:
        0 3px 10px #0000000d;

}


.search-box input {

    flex: 1;

    padding: 11px;

    border: 1px solid #ccd5df;

    border-radius: 6px;

    font-size: 13px;

    outline: none;

}


.search-box input:focus {

    border-color: #0866c6;

    box-shadow:
        0 0 0 2px #0866c620;

}


/* ==========================================
   BUTTON
========================================== */

.btn {

    border: none;

    background: #0866c6;

    color: #fff;

    padding: 11px 18px;

    border-radius: 6px;

    cursor: pointer;

    font-size: 12px;

    font-weight: 700;

    text-decoration: none;

    display: inline-block;

}


.btn:hover {

    background: #063b75;

}


.clear-btn {

    background: #64748b;

}


.clear-btn:hover {

    background: #475569;

}


/* ==========================================
   TABLE
========================================== */

.table-box {

    margin-top: 20px;

    background: #fff;

    border: 1px solid #e1e6ed;

    border-radius: 10px;

    overflow-x: auto;

    box-shadow:
        0 3px 10px #0000000d;

}


table {

    width: 100%;

    min-width: 1200px;

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

    color: #172b4d;

    font-weight: 700;

}


tr:hover {

    background: #f8fbff;

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


.status-success {

    background: #dff5e6;

    color: #168542;

}


.status-pending {

    background: #fff0d5;

    color: #b56a00;

}


.status-failed {

    background: #ffe8e8;

    color: #b42318;

}


.status-other {

    background: #e8eef5;

    color: #475569;

}


/* ==========================================
   PAYMENT METHOD
========================================== */

.method {

    font-weight: 700;

}


.esewa {

    color: #168542;

}


.khalti {

    color: #6d28d9;

}


.bank {

    color: #0866c6;

}


/* ==========================================
   EMPTY
========================================== */

.empty {

    text-align: center;

    padding: 35px;

    color: #6b7280;

    font-size: 13px;

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

    color: #fff;

    display: grid;

    place-items: center;

    font-size: 10px;

}


/* ==========================================
   MOBILE
========================================== */

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


    .search-box {

        flex-direction: column;

    }


    .search-box .btn {

        width: 100%;

        text-align: center;

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

        <span>🏠</span>

        <span>
            Dashboard
        </span>

    </a>


    <a href="students.php">

        <span>👨‍🎓</span>

        <span>
            Students
        </span>

    </a>


    <a href="fees.php">

        <span>💰</span>

        <span>
            Fee Management
        </span>

    </a>


    <a href="payments.php"
       class="active">

        <span>💳</span>

        <span>
            Payments
        </span>

    </a>


    <a href="reports.php">

        <span>📊</span>

        <span>
            Reports
        </span>

    </a>


    <a href="logout.php">

        <span>🚪</span>

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

        Payment Management

    </h1>


    <p class="muted">

        View and monitor all student payment transactions.

    </p>


    <!-- ======================================
         SEARCH
    ======================================= -->

    <form
        method="GET"
        action="payments.php"
        class="search-box"
    >

        <input
            type="text"
            name="search"
            placeholder="Search by Transaction ID, Student ID, Name, Method or Status..."
            value="<?php

                echo htmlspecialchars(
                    $search
                );

            ?>"
        >


        <button
            type="submit"
            class="btn"
        >

            🔍 Search

        </button>


        <a
            href="payments.php"
            class="btn clear-btn"
        >

            Clear

        </a>

    </form>


    <!-- ======================================
         TABLE
    ======================================= -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>
                        Payment ID
                    </th>

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
                        Fee Type
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Payment Method
                    </th>

                    <th>
                        Payment Date
                    </th>

                    <th>
                        Status
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php

            if (
                $result &&
                mysqli_num_rows($result) > 0
            ) {

                while (
                    $payment =
                    mysqli_fetch_assoc($result)
                ) {


                    /* ==========================
                       STATUS CLASS
                    ========================== */

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
                            'status-success';

                    } elseif (
                        $status === 'pending'
                    ) {

                        $status_class =
                            'status-pending';

                    } elseif (
                        $status === 'failed'
                    ) {

                        $status_class =
                            'status-failed';

                    } else {

                        $status_class =
                            'status-other';

                    }


                    /* ==========================
                       METHOD CLASS
                    ========================== */

                    $method =
                        strtolower(
                            trim(
                                $payment[
                                    'payment_method'
                                ] ?? ''
                            )
                        );


                    $method_class = '';


                    if (
                        strpos(
                            $method,
                            'esewa'
                        ) !== false
                    ) {

                        $method_class = 'esewa';

                    } elseif (
                        strpos(
                            $method,
                            'khalti'
                        ) !== false
                    ) {

                        $method_class = 'khalti';

                    } elseif (
                        strpos(
                            $method,
                            'bank'
                        ) !== false
                    ) {

                        $method_class = 'bank';

                    }

            ?>

                <tr>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $payment[
                                'payment_id'
                            ]
                        );

                        ?>

                    </td>


                    <td>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $payment[
                                    'transaction_id'
                                ]
                                ?? 'N/A'
                            );

                            ?>

                        </strong>

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

                        <?php

                        echo htmlspecialchars(
                            $payment[
                                'fee_type'
                            ]
                            ?? 'N/A'
                        );

                        ?>

                    </td>


                    <td>

                        <strong>

                            Rs.
                            <?php

                            echo number_format(
                                (float)
                                $payment['amount'],
                                2
                            );

                            ?>

                        </strong>

                    </td>


                    <td>

                        <span
                            class="method
                            <?php
                            echo $method_class;
                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $payment[
                                    'payment_method'
                                ]
                                ?? 'N/A'
                            );

                            ?>

                        </span>

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
                        colspan="9"
                        class="empty"
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