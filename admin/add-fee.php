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

$message = "";
$error = "";


/* ==========================================
   ADD NEW FEE
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id   = trim($_POST['student_id'] ?? '');
    $fee_type     = trim($_POST['fee_type'] ?? '');
    $total_amount = trim($_POST['total_amount'] ?? '');

    /* ==============================
       VALIDATION
    ============================== */

    if (empty($student_id)) {

        $error = "Please select a student.";

    } elseif (empty($fee_type)) {

        $error = "Fee type is required.";

    } elseif (
        empty($total_amount) ||
        !is_numeric($total_amount) ||
        $total_amount <= 0
    ) {

        $error = "Please enter a valid fee amount.";

    }


    /* ==============================
       CHECK STUDENT
    ============================== */

    if (empty($error)) {

        $student_stmt = mysqli_prepare(
            $conn,
            "SELECT student_id
             FROM students
             WHERE student_id = ?"
        );

        if (!$student_stmt) {

            $error =
                "Student query failed: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $student_stmt,
                "s",
                $student_id
            );

            mysqli_stmt_execute(
                $student_stmt
            );

            $student_result =
                mysqli_stmt_get_result(
                    $student_stmt
                );

            if (
                mysqli_num_rows(
                    $student_result
                ) === 0
            ) {

                $error =
                    "Selected student does not exist.";

            }

            mysqli_stmt_close(
                $student_stmt
            );
        }
    }


    /* ==============================
       INSERT FEE
    ============================== */

    if (empty($error)) {

        $paid_amount = 0;

        $due_amount =
            (float)$total_amount;

        $status = "Unpaid";


        $stmt = mysqli_prepare(
            $conn,

            "INSERT INTO fee_details
            (
                student_id,
                fee_type,
                total_amount,
                paid_amount,
                due_amount,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );


        if (!$stmt) {

            $error =
                "Insert query failed: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "ssddds",
                $student_id,
                $fee_type,
                $total_amount,
                $paid_amount,
                $due_amount,
                $status
            );


            if (
                mysqli_stmt_execute($stmt)
            ) {

                $message =
                    "New fee added successfully.";

            } else {

                $error =
                    "Fee could not be added: " .
                    mysqli_stmt_error($stmt);

            }


            mysqli_stmt_close($stmt);
        }
    }
}


/* ==========================================
   GET STUDENTS
========================================== */

$students = mysqli_query(
    $conn,

    "SELECT
        student_id,
        student_name,
        course,
        semester
     FROM students
     ORDER BY student_name ASC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Add New Fee</title>

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


/* ==============================
   TOPBAR
============================== */

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

    flex: 1;

    font-size: 15px;

    font-weight: 700;
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


/* ==============================
   SIDEBAR
============================== */

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


/* ==============================
   MAIN
============================== */

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


/* ==============================
   FORM CARD
============================== */

.form-card {

    max-width: 650px;

    background: white;

    margin-top: 25px;

    padding: 25px;

    border-radius: 10px;

    border: 1px solid #e1e6ed;

    box-shadow:
        0 3px 10px #0000000d;
}

label {

    display: block;

    font-size: 12px;

    font-weight: 700;

    margin:
        15px 0 6px;
}

input,
select {

    width: 100%;

    padding: 11px;

    border:
        1px solid #ccd5df;

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


/* ==============================
   BUTTON
============================== */

.buttons {

    display: flex;

    gap: 10px;

    margin-top: 22px;
}

.btn {

    border: none;

    background: #0866c6;

    color: white;

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


/* ==============================
   MESSAGE
============================== */

.success {

    max-width: 650px;

    margin-top: 20px;

    padding: 12px 15px;

    background: #dff5e6;

    color: #168542;

    border:
        1px solid #bde5c9;

    border-radius: 6px;

    font-size: 12px;

    font-weight: 600;
}

.error {

    max-width: 650px;

    margin-top: 20px;

    padding: 12px 15px;

    background: #ffe8e8;

    color: #b42318;

    border:
        1px solid #f5c2c0;

    border-radius: 6px;

    font-size: 12px;

    font-weight: 600;
}


/* ==============================
   FOOTER
============================== */

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


/* ==============================
   MOBILE
============================== */

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

            echo htmlspecialchars(
                $_SESSION['admin_name']
                ?? 'Administrator'
            );

            ?>

        </span>

    </div>

</header>


<!-- SIDEBAR -->

<aside class="sidebar">

    <a href="dashboard.php">
        🏠
        <span>Dashboard</span>
    </a>

    <a href="students.php">
        👨‍🎓
        <span>Students</span>
    </a>

    <a href="fees.php"
       class="active">

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
        Add New Fee
    </h1>

    <p class="muted">
        Add a new fee record for a student.
    </p>


    <!-- SUCCESS -->

    <?php if (!empty($message)) { ?>

        <div class="success">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php } ?>


    <!-- ERROR -->

    <?php if (!empty($error)) { ?>

        <div class="error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php } ?>


    <!-- FORM -->

    <div class="form-card">

        <form
            method="POST"
            action=""
        >


            <!-- STUDENT -->

            <label for="student_id">

                Student

            </label>

            <select
                id="student_id"
                name="student_id"
                required
            >

                <option value="">
                    -- Select Student --
                </option>


                <?php

                if (
                    $students &&
                    mysqli_num_rows($students) > 0
                ) {

                    while (
                        $student =
                        mysqli_fetch_assoc($students)
                    ) {

                ?>

                    <option
                        value="<?php
                            echo htmlspecialchars(
                                $student['student_id']
                            );
                        ?>"
                    >

                        <?php

                        echo htmlspecialchars(
                            $student['student_id']
                        );

                        ?>

                        -
                        <?php

                        echo htmlspecialchars(
                            $student['student_name']
                        );

                        ?>

                        -

                        <?php

                        echo htmlspecialchars(
                            $student['course']
                        );

                        ?>

                    </option>

                <?php

                    }

                }

                ?>

            </select>


            <!-- FEE TYPE -->

            <label for="fee_type">

                Fee Type

            </label>

            <input
                type="text"
                id="fee_type"
                name="fee_type"
                placeholder="Example: Tuition Fee"
                required
            >


            <!-- TOTAL AMOUNT -->

            <label for="total_amount">

                Total Fee Amount (Rs.)

            </label>

            <input
                type="number"
                id="total_amount"
                name="total_amount"
                step="0.01"
                min="1"
                placeholder="Enter total fee amount"
                required
            >


            <!-- BUTTONS -->

            <div class="buttons">

                <button
                    type="submit"
                    class="btn"
                >

                    💾 Add Fee

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


<!-- FOOTER -->

<footer class="footer">

    © 2026 Online Fee Transaction System.
    Administration Panel.

</footer>


</body>

</html>