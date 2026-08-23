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
// STUDENT INFORMATION
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT student_name, course, semester
     FROM students
     WHERE student_id = ?"
);


if (!$stmt) {

    die("Student query failed: " . mysqli_error($conn));

}


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $student_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$student = mysqli_fetch_assoc($result);


if (!$student) {

    die("Student record not found.");

}


// ==========================================
// PAYMENT MESSAGE
// ==========================================

$message = "";


// ==========================================
// PAYMENT PROCESS
// ==========================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    // Get form values

    $fee_id = isset($_POST['fee_id'])
        ? (int)$_POST['fee_id']
        : 0;


    $amount = isset($_POST['amount'])
        ? (float)$_POST['amount']
        : 0;


    $payment_method = isset($_POST['payment_method'])
        ? trim($_POST['payment_method'])
        : "";


    // ======================================
    // VALIDATION
    // ======================================

    if ($fee_id <= 0) {

        $message = "Please select a fee.";

    }

    elseif ($amount <= 0) {

        $message = "Please enter a valid payment amount.";

    }

    elseif (empty($payment_method)) {

        $message = "Please select a payment method.";

    }

    else {


        // ==================================
        // GET SELECTED FEE
        // ==================================

        $check_stmt = mysqli_prepare(
            $conn,
            "SELECT
                fee_id,
                fee_type,
                total_amount,
                paid_amount,
                due_amount
             FROM fee_details
             WHERE fee_id = ?
             AND student_id = ?"
        );


        if (!$check_stmt) {

            die(
                "Fee query failed: " .
                mysqli_error($conn)
            );

        }


        mysqli_stmt_bind_param(
            $check_stmt,
            "is",
            $fee_id,
            $student_id
        );


        mysqli_stmt_execute($check_stmt);


        $check_result =
            mysqli_stmt_get_result($check_stmt);


        $selected_fee =
            mysqli_fetch_assoc($check_result);


        // ==================================
        // CHECK FEE
        // ==================================

        if (!$selected_fee) {

            $message =
                "Selected fee was not found.";

        }

        else {


            $due_amount =
                (float)$selected_fee['due_amount'];


            // ==================================
            // CHECK DUE AMOUNT
            // ==================================

            if ($due_amount <= 0) {

                $message =
                    "This fee has already been paid.";

            }

            elseif ($amount > $due_amount) {

                $message =
                    "Payment amount cannot be greater than due amount.";

            }

            else {


                // ==================================
                // GENERATE TRANSACTION ID
                // ==================================

                $transaction_id =
                    "TXN" .
                    date("YmdHis") .
                    rand(100, 999);


                $payment_date =
                    date("Y-m-d H:i:s");


                $status = "paid";


                // ==================================
                // START DATABASE TRANSACTION
                // ==================================

                mysqli_begin_transaction($conn);


                try {


                    // ==================================
                    // INSERT PAYMENT
                    // ==================================

                    $payment_stmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO payments
                        (
                            transaction_id,
                            student_id,
                            fee_id,
                            amount,
                            payment_method,
                            payment_date,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );


                    if (!$payment_stmt) {

                        throw new Exception(
                            "Payment query failed: " .
                            mysqli_error($conn)
                        );

                    }


                    mysqli_stmt_bind_param(
                        $payment_stmt,
                        "ssidsss",
                        $transaction_id,
                        $student_id,
                        $fee_id,
                        $amount,
                        $payment_method,
                        $payment_date,
                        $status
                    );


                    if (!mysqli_stmt_execute($payment_stmt)) {

                        throw new Exception(
                            "Payment could not be saved: " .
                            mysqli_stmt_error($payment_stmt)
                        );

                    }


                    // ==================================
                    // CALCULATE NEW FEE AMOUNTS
                    // ==================================

                    $old_paid =
                        (float)$selected_fee['paid_amount'];


                    $old_due =
                        (float)$selected_fee['due_amount'];


                    $new_paid =
                        $old_paid + $amount;


                    $new_due =
                        $old_due - $amount;


                    if ($new_due < 0) {

                        $new_due = 0;

                    }


                    // ==================================
                    // FEE STATUS
                    // ==================================

                    if ($new_due <= 0) {

                        $fee_status = "paid";

                    }

                    else {

                        $fee_status = "partial";

                    }


                    // ==================================
                    // UPDATE FEE DETAILS
                    // ==================================

                    $update_stmt = mysqli_prepare(
                        $conn,
                        "UPDATE fee_details
                         SET
                            paid_amount = ?,
                            due_amount = ?,
                            status = ?
                         WHERE fee_id = ?
                         AND student_id = ?"
                    );


                    if (!$update_stmt) {

                        throw new Exception(
                            "Fee update query failed: " .
                            mysqli_error($conn)
                        );

                    }


                    mysqli_stmt_bind_param(
                        $update_stmt,
                        "ddsis",
                        $new_paid,
                        $new_due,
                        $fee_status,
                        $fee_id,
                        $student_id
                    );


                    if (!mysqli_stmt_execute($update_stmt)) {

                        throw new Exception(
                            "Fee details could not be updated: " .
                            mysqli_stmt_error($update_stmt)
                        );

                    }


                    // ==================================
                    // COMMIT
                    // ==================================

                    mysqli_commit($conn);


                    // ==================================
                    // GO TO RECEIPT
                    // ==================================

                    header(
                        "Location: receipt.php?transaction_id=" .
                        urlencode($transaction_id)
                    );

                    exit();


                }

                catch (Exception $e) {


                    // ==================================
                    // ROLLBACK
                    // ==================================

                    mysqli_rollback($conn);


                    $message = $e->getMessage();

                }

            }

        }

    }

}


// ==========================================
// GET CURRENT DUE FEES
// ==========================================

$fee_stmt = mysqli_prepare(
    $conn,
    "SELECT
        fee_id,
        fee_type,
        total_amount,
        paid_amount,
        due_amount
     FROM fee_details
     WHERE student_id = ?
     AND due_amount > 0
     ORDER BY fee_id ASC"
);


if (!$fee_stmt) {

    die(
        "Fee details query failed: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $fee_stmt,
    "s",
    $student_id
);


mysqli_stmt_execute($fee_stmt);


$fee_result =
    mysqli_stmt_get_result($fee_stmt);

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Pay Fee</title>

<link rel="stylesheet" href="style.css">


<style>

/* ======================================
   PAYMENT PAGE
====================================== */

.payment-box {

    max-width: 700px;

    margin-top: 25px;

}


.form-group {

    margin-bottom: 20px;

}


.form-group label {

    display: block;

    font-weight: bold;

    margin-bottom: 8px;

}


.form-group select,
.form-group input {

    width: 100%;

    padding: 12px;

    border: 1px solid #ddd;

    border-radius: 8px;

    font-size: 15px;

    box-sizing: border-box;

}


.form-group select:focus,
.form-group input:focus {

    outline: none;

    border-color: #4f46e5;

}


.due-text {

    display: block;

    margin-top: 7px;

    font-size: 14px;

}


.alert {

    padding: 14px;

    border-radius: 8px;

    margin-top: 20px;

    margin-bottom: 20px;

    background: #ffe5e5;

    color: #b00020;

}


.no-fee {

    text-align: center;

    padding: 40px;

}


.payment-info {

    background: #f5f7fb;

    padding: 15px;

    border-radius: 10px;

    margin-bottom: 20px;

}


.payment-info p {

    margin: 7px 0;

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
                    $student['student_name']
                );

                ?>

            </b>


            <small>

                <?php

                echo htmlspecialchars(
                    $student['course']
                    . " - "
                    . $student['semester']
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


    <a href="pay-fee.php"
       class="active">

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



<!-- ==========================================
     MAIN CONTENT
========================================== -->

<main class="main">


    <h1>
        Pay Fee
    </h1>


    <p class="muted">
        Make your fee payment securely.
    </p>



    <!-- ERROR MESSAGE -->

    <?php

    if (!empty($message)) {

    ?>

        <div class="alert">

            <?php

            echo htmlspecialchars($message);

            ?>

        </div>

    <?php

    }

    ?>



    <div class="card payment-box">


        <?php

        if (mysqli_num_rows($fee_result) > 0) {

        ?>


            <!-- ==================================
                 PAYMENT FORM
            ================================== -->

            <form method="POST"
                  action="pay-fee.php">


                <!-- FEE -->

                <div class="form-group">


                    <label for="fee_id">

                        Select Fee

                    </label>


                    <select
                        name="fee_id"
                        id="fee_id"
                        required
                        onchange="updateDueAmount()"
                    >


                        <option value="">

                            -- Select Fee --

                        </option>


                        <?php

                        while (
                            $fee =
                            mysqli_fetch_assoc(
                                $fee_result
                            )
                        ) {

                        ?>


                            <option

                                value="<?php
                                    echo $fee['fee_id'];
                                ?>"

                                data-due="<?php
                                    echo $fee['due_amount'];
                                ?>"

                            >

                                <?php

                                echo htmlspecialchars(
                                    $fee['fee_type']
                                );

                                ?>

                                -

                                Due Rs.

                                <?php

                                echo number_format(
                                    $fee['due_amount'],
                                    2
                                );

                                ?>

                            </option>


                        <?php

                        }

                        ?>


                    </select>


                </div>



                <!-- PAYMENT AMOUNT -->

                <div class="form-group">


                    <label for="amount">

                        Payment Amount

                    </label>


                    <input

                        type="number"

                        name="amount"

                        id="amount"

                        step="0.01"

                        min="1"

                        placeholder="Enter payment amount"

                        required

                    >


                    <small
                        id="dueText"
                        class="muted due-text"
                    >

                        Select a fee to see due amount.

                    </small>


                </div>



                <!-- PAYMENT METHOD -->

                <div class="form-group">


                    <label for="payment_method">

                        Payment Method

                    </label>


                    <select
                        name="payment_method"
                        id="payment_method"
                        required
                    >


                        <option value="">

                            -- Select Payment Method --

                        </option>


                        <option value="Cash">

                            Cash

                        </option>


                        <option value="eSewa">

                            eSewa

                        </option>


                        <option value="Khalti">

                            Khalti

                        </option>


                        <option value="Bank Transfer">

                            Bank Transfer

                        </option>


                    </select>


                </div>



                <!-- PAY BUTTON -->

                <button
                    type="submit"
                    class="btn"
                >

                    💳 Pay Now

                </button>


            </form>


        <?php

        }

        else {

        ?>


            <!-- ==================================
                 NO DUE FEE
            ================================== -->

            <div class="no-fee">


                <div style="font-size: 55px;">

                    ✅

                </div>


                <h2>

                    No Due Fee

                </h2>


                <p class="muted">

                    You have no outstanding fee to pay.

                </p>


                <br>


                <a
                    href="dashboard.php"
                    class="btn"
                >

                    ← Back to Dashboard

                </a>


            </div>


        <?php

        }

        ?>


    </div>


</main>



<!-- ==========================================
     FOOTER
========================================== -->

<footer class="footer">

    © 2026 Online Fee Transaction System.
    All rights reserved.

</footer>



<!-- ==========================================
     JAVASCRIPT
========================================== -->

<script>

function updateDueAmount() {


    const feeSelect =
        document.getElementById("fee_id");


    const amountInput =
        document.getElementById("amount");


    const dueText =
        document.getElementById("dueText");


    const selectedOption =
        feeSelect.options[
            feeSelect.selectedIndex
        ];


    if (!selectedOption.value) {


        dueText.innerHTML =
            "Select a fee to see due amount.";


        amountInput.removeAttribute("max");


        return;

    }


    const dueAmount =
        parseFloat(
            selectedOption.getAttribute(
                "data-due"
            )
        );


    dueText.innerHTML =
        "Maximum payable amount: Rs. "
        + dueAmount.toFixed(2);


    amountInput.setAttribute(
        "max",
        dueAmount
    );


    amountInput.value = "";

}

</script>


</body>

</html>