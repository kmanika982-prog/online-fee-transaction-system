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


/* ==========================================
   GET FEE ID
========================================== */

$fee_id = trim($_GET['id'] ?? '');

if (empty($fee_id)) {

    header("Location: fees.php");
    exit();

}


$message = "";
$error = "";


/* ==========================================
   UPDATE FEE
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fee_type =
        trim($_POST['fee_type'] ?? '');

    $total_amount =
        (float)($_POST['total_amount'] ?? 0);

    $paid_amount =
        (float)($_POST['paid_amount'] ?? 0);


    /* ======================================
       VALIDATION
    ====================================== */

    if (empty($fee_type)) {

        $error = "Fee type is required.";

    } elseif ($total_amount <= 0) {

        $error = "Total amount must be greater than 0.";

    } elseif ($paid_amount < 0) {

        $error = "Paid amount cannot be negative.";

    } elseif ($paid_amount > $total_amount) {

        $error =
            "Paid amount cannot be greater than total fee.";

    }


    /* ======================================
       CALCULATE DUE
    ====================================== */

    if (empty($error)) {

        $due_amount =
            $total_amount - $paid_amount;


        /* ==================================
           CALCULATE STATUS
        ================================== */

        if ($due_amount <= 0) {

            $status = "Paid";

        } elseif ($paid_amount > 0) {

            $status = "Partial";

        } else {

            $status = "Unpaid";

        }


        /* ==================================
           UPDATE DATABASE
        ================================== */

        $update_stmt = mysqli_prepare(
            $conn,

            "UPDATE fee_details
             SET
                fee_type = ?,
                total_amount = ?,
                paid_amount = ?,
                due_amount = ?,
                status = ?
             WHERE fee_id = ?"
        );


        if (!$update_stmt) {

            $error =
                "Update query failed: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $update_stmt,
                "sdddsi",
                $fee_type,
                $total_amount,
                $paid_amount,
                $due_amount,
                $status,
                $fee_id
            );


            if (
                mysqli_stmt_execute(
                    $update_stmt
                )
            ) {

                $message =
                    "Fee information updated successfully.";

            } else {

                $error =
                    "Update failed: " .
                    mysqli_stmt_error(
                        $update_stmt
                    );

            }


            mysqli_stmt_close(
                $update_stmt
            );
        }
    }
}


/* ==========================================
   GET FEE INFORMATION
========================================== */

$stmt = mysqli_prepare(
    $conn,

    "SELECT
        f.*,
        s.student_name,
        s.course,
        s.semester
     FROM fee_details f
     LEFT JOIN students s
        ON f.student_id = s.student_id
     WHERE f.fee_id = ?"
);


if (!$stmt) {

    die(
        "Database query failed: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $fee_id
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result($stmt);


$fee =
    mysqli_fetch_assoc($result);


if (!$fee) {

    die("Fee record not found.");

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Fee</title>


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

}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px 18px;

    color: #fff;

    text-decoration: none;

    font-size: 12px;

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
   FORM CARD
========================================== */

.form-card {

    max-width: 650px;

    background: #fff;

    border: 1px solid #e1e6ed;

    border-radius: 10px;

    padding: 25px;

    margin-top: 25px;

    box-shadow:
        0 3px 10px #0000000d;

}


/* ==========================================
   STUDENT INFO
========================================== */

.student-info {

    background: #edf3fa;

    padding: 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 12px;

    line-height: 1.8;

}


.student-info strong {

    color: #063b75;

}


/* ==========================================
   LABEL
========================================== */

label {

    display: block;

    font-size: 12px;

    font-weight: 700;

    margin: 14px 0 6px;

}


/* ==========================================
   INPUT
========================================== */

input,
select {

    width: 100%;

    padding: 11px;

    border: 1px solid #ccd5df;

    border-radius: 6px;

    font-size: 13px;

    outline: none;

}


input:focus,
select:focus {

    border-color: #0866c6;

    box-shadow:
        0 0 0 2px #0866c620;

}


.readonly {

    background: #f1f5f9;

    color: #64748b;

}


/* ==========================================
   BUTTONS
========================================== */

.buttons {

    display: flex;

    gap: 10px;

    margin-top: 22px;

}


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

}


.btn:hover {

    background: #063b75;

}


.cancel {

    background: #64748b;

}


.cancel:hover {

    background: #475569;

}


/* ==========================================
   SUCCESS
========================================== */

.success {

    max-width: 650px;

    background: #dff5e6;

    color: #168542;

    border: 1px solid #bde5c9;

    padding: 12px 15px;

    border-radius: 6px;

    margin-top: 20px;

    font-size: 12px;

    font-weight: 600;

}


/* ==========================================
   ERROR
========================================== */

.error {

    max-width: 650px;

    background: #ffe8e8;

    color: #b42318;

    border: 1px solid #f5c2c0;

    padding: 12px 15px;

    border-radius: 6px;

    margin-top: 20px;

    font-size: 12px;

    font-weight: 600;

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


    .buttons {

        flex-direction: column;

    }


    .btn {

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
                $_SESSION['admin_name']
                ?? 'Administrator'
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


    <a href="fees.php"
       class="active">

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


    <a href="reports.php">

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

        Edit Fee

    </h1>


    <p class="muted">

        Update student fee information.

    </p>


    <!-- SUCCESS -->

    <?php if (!empty($message)) { ?>

        <div class="success">

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>

    <?php } ?>


    <!-- ERROR -->

    <?php if (!empty($error)) { ?>

        <div class="error">

            <?php

            echo htmlspecialchars(
                $error
            );

            ?>

        </div>

    <?php } ?>


    <!-- FORM -->

    <div class="form-card">


        <!-- STUDENT INFORMATION -->

        <div class="student-info">

            <strong>Student ID:</strong>

            <?php

            echo htmlspecialchars(
                $fee['student_id']
            );

            ?>

            <br>


            <strong>Student Name:</strong>

            <?php

            echo htmlspecialchars(
                $fee['student_name']
                ?? 'N/A'
            );

            ?>

            <br>


            <strong>Course:</strong>

            <?php

            echo htmlspecialchars(
                $fee['course']
                ?? 'N/A'
            );

            ?>

            <br>


            <strong>Semester:</strong>

            <?php

            echo htmlspecialchars(
                $fee['semester']
                ?? 'N/A'
            );

            ?>

        </div>


        <form method="POST">


            <!-- FEE ID -->

            <label>
                Fee ID
            </label>

            <input
                type="text"
                class="readonly"
                value="<?php

                    echo htmlspecialchars(
                        $fee['fee_id']
                    );

                ?>"
                readonly
            >


            <!-- FEE TYPE -->

            <label for="fee_type">

                Fee Type

            </label>

            <input
                type="text"
                id="fee_type"
                name="fee_type"
                value="<?php

                    echo htmlspecialchars(
                        $fee['fee_type']
                    );

                ?>"
                required
            >


            <!-- TOTAL AMOUNT -->

            <label for="total_amount">

                Total Fee Amount

            </label>

            <input
                type="number"
                id="total_amount"
                name="total_amount"
                min="0"
                step="0.01"
                value="<?php

                    echo htmlspecialchars(
                        $fee['total_amount']
                    );

                ?>"
                required
            >


            <!-- PAID AMOUNT -->

            <label for="paid_amount">

                Paid Amount

            </label>

            <input
                type="number"
                id="paid_amount"
                name="paid_amount"
                min="0"
                step="0.01"
                value="<?php

                    echo htmlspecialchars(
                        $fee['paid_amount']
                    );

                ?>"
                required
            >


            <!-- DUE AMOUNT -->

            <label>
                Due Amount
            </label>

            <input
                type="text"
                class="readonly"
                value="Rs. <?php

                    echo number_format(
                        (float)$fee['due_amount'],
                        2
                    );

                ?>"
                readonly
            >


            <!-- STATUS -->

            <label>
                Status
            </label>

            <input
                type="text"
                class="readonly"
                value="<?php

                    echo htmlspecialchars(
                        $fee['status']
                    );

                ?>"
                readonly
            >


            <!-- BUTTONS -->

            <div class="buttons">


                <button
                    type="submit"
                    class="btn"
                >

                    💾 Save Changes

                </button>


                <a
                    href="fees.php"
                    class="btn cancel"
                >

                    ← Back to Fees

                </a>


            </div>


        </form>


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