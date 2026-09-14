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
   DELETE FEE
========================================== */

if (isset($_GET['delete'])) {

    $fee_id = (int) $_GET['delete'];

    if ($fee_id > 0) {

        $delete_stmt = mysqli_prepare(
            $conn,
            "DELETE FROM fee_details
             WHERE fee_id = ?"
        );

        if ($delete_stmt) {

            mysqli_stmt_bind_param(
                $delete_stmt,
                "i",
                $fee_id
            );

            mysqli_stmt_execute(
                $delete_stmt
            );

            mysqli_stmt_close(
                $delete_stmt
            );
        }
    }

    header("Location: fees.php");
    exit();
}


/* ==========================================
   SEARCH
========================================== */

$search =
    trim($_GET['search'] ?? '');


/* ==========================================
   GET FEE RECORDS
========================================== */

if ($search !== '') {

    $stmt = mysqli_prepare(
        $conn,

        "SELECT
            f.fee_id,
            f.student_id,
            s.student_name,
            f.fee_type,
            f.total_amount,
            f.paid_amount,
            f.due_amount,
            f.status
         FROM fee_details f
         LEFT JOIN students s
            ON f.student_id = s.student_id
         WHERE
            f.student_id LIKE ?
            OR s.student_name LIKE ?
            OR f.fee_type LIKE ?
         ORDER BY f.fee_id DESC"
    );


    if (!$stmt) {

        die(
            "Fee query failed: " .
            mysqli_error($conn)
        );

    }


    $search_value =
        "%" . $search . "%";


    mysqli_stmt_bind_param(
        $stmt,
        "sss",
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
            f.fee_id,
            f.student_id,
            s.student_name,
            f.fee_type,
            f.total_amount,
            f.paid_amount,
            f.due_amount,
            f.status
         FROM fee_details f
         LEFT JOIN students s
            ON f.student_id = s.student_id
         ORDER BY f.fee_id DESC"
    );


    if (!$result) {

        die(
            "Fee query failed: " .
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

<title>Fee Management</title>


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
   TOOLBAR
========================================== */

.toolbar {

    margin-top: 25px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

}


/* ==========================================
   SEARCH
========================================== */

.search-box {

    background: #fff;

    border: 1px solid #e1e6ed;

    border-radius: 10px;

    padding: 16px;

    display: flex;

    gap: 10px;

    flex: 1;

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
   BUTTONS
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

.add-btn {

    white-space: nowrap;

    background: #168542;

}

.add-btn:hover {

    background: #116c35;

}


/* ==========================================
   TABLE BOX
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


/* ==========================================
   TABLE
========================================== */

table {

    width: 100%;

    min-width: 1050px;

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

    border-radius: 20px;

    font-size: 10px;

    font-weight: 700;

}

.paid {

    background: #dff5e6;

    color: #168542;

}

.partial {

    background: #fff0d5;

    color: #b56a00;

}

.unpaid {

    background: #ffe8e8;

    color: #b42318;

}


/* ==========================================
   ACTION BUTTONS
========================================== */

.edit-btn {

    display: inline-block;

    background: #0866c6;

    color: #fff;

    padding: 6px 10px;

    border-radius: 5px;

    text-decoration: none;

    font-size: 10px;

    font-weight: 700;

}

.edit-btn:hover {

    background: #063b75;

}

.delete-btn {

    display: inline-block;

    background: #dc3545;

    color: #fff;

    padding: 6px 10px;

    border-radius: 5px;

    text-decoration: none;

    font-size: 10px;

    font-weight: 700;

    margin-left: 4px;

}

.delete-btn:hover {

    background: #b42318;

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

@media (max-width: 750px) {

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

    .toolbar {

        flex-direction: column;

        align-items: stretch;

    }

    .search-box {

        flex-direction: column;

    }

    .search-box .btn {

        width: 100%;

        text-align: center;

    }

    .add-btn {

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


    <a href="fees.php"
       class="active">

        <span>💰</span>

        <span>
            Fee Management
        </span>

    </a>


    <a href="payments.php">

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

        Fee Management

    </h1>


    <p class="muted">

        View and manage student fee records.

    </p>


    <!-- ======================================
         TOOLBAR
    ======================================= -->

    <div class="toolbar">


        <!-- SEARCH -->

        <form
            method="GET"
            action="fees.php"
            class="search-box"
        >

            <input
                type="text"
                name="search"
                placeholder="Search by Student ID, Name or Fee Type..."
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
                href="fees.php"
                class="btn clear-btn"
            >

                Clear

            </a>

        </form>


        <!-- ADD FEE -->

        <a
            href="add-fee.php"
            class="btn add-btn"
        >

            ➕ Add New Fee

        </a>


    </div>


    <!-- ======================================
         TABLE
    ======================================= -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>
                        Fee ID
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
                        Total Fee
                    </th>

                    <th>
                        Paid Amount
                    </th>

                    <th>
                        Due Amount
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Action
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
                    $fee =
                    mysqli_fetch_assoc($result)
                ) {


                    /* ==========================
                       STATUS
                    ========================== */

                    $status =
                        strtolower(
                            trim(
                                $fee['status']
                                ?? ''
                            )
                        );


                    if ($status === 'paid') {

                        $status_class =
                            'paid';

                        $status_text =
                            'Paid';

                    } elseif (
                        $status === 'partial'
                    ) {

                        $status_class =
                            'partial';

                        $status_text =
                            'Partial';

                    } else {

                        $status_class =
                            'unpaid';

                        $status_text =
                            'Unpaid';

                    }

            ?>

                <tr>


                    <!-- FEE ID -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $fee['fee_id']
                        );

                        ?>

                    </td>


                    <!-- STUDENT ID -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $fee['student_id']
                        );

                        ?>

                    </td>


                    <!-- STUDENT NAME -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $fee['student_name']
                            ?? 'N/A'
                        );

                        ?>

                    </td>


                    <!-- FEE TYPE -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $fee['fee_type']
                        );

                        ?>

                    </td>


                    <!-- TOTAL -->

                    <td>

                        Rs.
                        <?php

                        echo number_format(
                            (float)
                            $fee['total_amount'],
                            2
                        );

                        ?>

                    </td>


                    <!-- PAID -->

                    <td>

                        Rs.
                        <?php

                        echo number_format(
                            (float)
                            $fee['paid_amount'],
                            2
                        );

                        ?>

                    </td>


                    <!-- DUE -->

                    <td>

                        Rs.
                        <?php

                        echo number_format(
                            (float)
                            $fee['due_amount'],
                            2
                        );

                        ?>

                    </td>


                    <!-- STATUS -->

                    <td>

                        <span
                            class="status
                            <?php
                            echo $status_class;
                            ?>"
                        >

                            <?php

                            echo $status_text;

                            ?>

                        </span>

                    </td>


                    <!-- ACTION -->

                    <td>


                        <a
                            href="edit-fee.php?id=<?php

                                echo urlencode(
                                    $fee['fee_id']
                                );

                            ?>"
                            class="edit-btn"
                        >

                            ✏️ Edit

                        </a>


                        <a
                            href="fees.php?delete=<?php

                                echo urlencode(
                                    $fee['fee_id']
                                );

                            ?>"
                            class="delete-btn"
                            onclick="return confirm(
                                'Are you sure you want to delete this fee record?'
                            );"
                        >

                            🗑️ Delete

                        </a>


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

                        No fee records found.

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