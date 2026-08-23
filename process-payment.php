```php
<?php

session_start();


// ==========================================
// CHECK LOGIN
// ==========================================

if (!isset($_SESSION['student_id'])) {

    header("Location: ../index.html");
    exit();

}


// ==========================================
// DATABASE CONNECTION
// ==========================================

include("db.php");


$student_id = $_SESSION['student_id'];


// ==========================================
// ONLY POST REQUEST
// ==========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../pay-fee.php");
    exit();

}


// ==========================================
// GET FORM DATA
// ==========================================

$fee_id = isset($_POST['fee_id'])
    ? (int)$_POST['fee_id']
    : 0;

$amount = isset($_POST['amount'])
    ? (float)$_POST['amount']
    : 0;

$payment_method = isset($_POST['payment_method'])
    ? trim($_POST['payment_method'])
    : "";


// ==========================================
// VALIDATION
// ==========================================

if ($fee_id <= 0) {

    die("Invalid fee information.");

}

if ($amount <= 0) {

    die("Invalid payment amount.");

}

if (empty($payment_method)) {

    die("Please select a payment method.");

}


// ==========================================
// GET FEE DETAILS
// ==========================================

$stmt = mysqli_prepare(
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


if (!$stmt) {

    die(
        "Fee query failed: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "is",
    $fee_id,
    $student_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$fee = mysqli_fetch_assoc($result);


// ==========================================
// CHECK FEE
// ==========================================

if (!$fee) {

    die("Selected fee was not found.");

}


// ==========================================
// GET CURRENT DUE
// ==========================================

$due_amount = (float)$fee['due_amount'];


// ==========================================
// CHECK ALREADY PAID
// ==========================================

if ($due_amount <= 0) {

    die("This fee has already been fully paid.");

}


// ==========================================
// PAYMENT CANNOT EXCEED DUE
// ==========================================

if ($amount > $due_amount) {

    die(
        "Payment amount cannot be greater than due amount. " .
        "Maximum payable amount: Rs. " .
        number_format($due_amount, 2)
    );

}


// ==========================================
// GENERATE TRANSACTION ID
// ==========================================

$transaction_id =
    "TXN" .
    date("YmdHis") .
    rand(100, 999);


// ==========================================
// PAYMENT DATE
// ==========================================

$payment_date = date("Y-m-d H:i:s");


// ==========================================
// PAYMENT STATUS
// ==========================================

$status = "paid";


// ==========================================
// START DATABASE TRANSACTION
// ==========================================

mysqli_begin_transaction($conn);


try {


    // ======================================
    // INSERT PAYMENT
    // ======================================

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


    // ======================================
    // CALCULATE NEW PAID AMOUNT
    // ======================================

    $old_paid = (float)$fee['paid_amount'];

    $new_paid = $old_paid + $amount;


    // ======================================
    // CALCULATE NEW DUE AMOUNT
    // ======================================

    $new_due = (float)$fee['total_amount'] - $new_paid;


    // Prevent negative due
    if ($new_due < 0) {

        $new_due = 0;

    }


    // ======================================
    // UPDATE FEE DETAILS
    // ======================================

    $update_stmt = mysqli_prepare(
        $conn,

        "UPDATE fee_details

         SET
            paid_amount = ?,
            due_amount = ?

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
        "ddis",
        $new_paid,
        $new_due,
        $fee_id,
        $student_id
    );


    if (!mysqli_stmt_execute($update_stmt)) {

        throw new Exception(
            "Fee details could not be updated: " .
            mysqli_stmt_error($update_stmt)
        );

    }


    // ======================================
    // COMMIT
    // ======================================

    mysqli_commit($conn);


    // ======================================
    // CLOSE STATEMENTS
    // ======================================

    mysqli_stmt_close($payment_stmt);

    mysqli_stmt_close($update_stmt);


    // ======================================
    // REDIRECT TO RECEIPT
    // ======================================

    header(
        "Location: ../receipt.php?transaction_id=" .
        urlencode($transaction_id)
    );

    exit();


}


// ==========================================
// ERROR HANDLING
// ==========================================

catch (Exception $e) {


    // Rollback database changes
    mysqli_rollback($conn);


    die(
        "Payment failed: " .
        htmlspecialchars($e->getMessage())
    );

}

?>
```
