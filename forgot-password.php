<?php

session_start();

include("backend/db.php");

$message = "";
$error = "";

$student_id = "";
$email = "";


// ==========================================
// FORM SUBMITTED
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    // ======================================
    // VALIDATION
    // ======================================

    if (empty($student_id)) {

        $error = "Please enter your Student ID.";

    } elseif (empty($email)) {

        $error = "Please enter your email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (empty($new_password)) {

        $error = "Please enter a new password.";

    } elseif (strlen($new_password) < 6) {

        $error = "Password must be at least 6 characters.";

    } elseif (empty($confirm_password)) {

        $error = "Please confirm your new password.";

    } elseif ($new_password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {


        // ==================================
        // FIND STUDENT
        // ==================================

        $sql = "
            SELECT student_id
            FROM students
            WHERE student_id = ?
            AND email = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $sql);


        if (!$stmt) {

            $error =
                "Database query failed: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $student_id,
                $email
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);


            // ==================================
            // STUDENT FOUND
            // ==================================

            if (mysqli_num_rows($result) === 1) {


                // ==================================
                // UPDATE PASSWORD
                // ==================================

                $update_sql = "
                    UPDATE students
                    SET password = ?
                    WHERE student_id = ?
                    AND email = ?
                ";

                $update_stmt =
                    mysqli_prepare(
                        $conn,
                        $update_sql
                    );


                if (!$update_stmt) {

                    $error =
                        "Password update failed: " .
                        mysqli_error($conn);

                } else {

                    mysqli_stmt_bind_param(
                        $update_stmt,
                        "sss",
                        $new_password,
                        $student_id,
                        $email
                    );


                    if (
                        mysqli_stmt_execute(
                            $update_stmt
                        )
                    ) {

                        $message =
                            "Password reset successfully.";

                    } else {

                        $error =
                            "Password reset failed: " .
                            mysqli_stmt_error(
                                $update_stmt
                            );
                    }


                    mysqli_stmt_close(
                        $update_stmt
                    );
                }


            } else {

                $error =
                    "Student ID and email do not match.";
            }


            mysqli_stmt_close($stmt);
        }
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Forgot Password - Online Fee Transaction System
    </title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>


<body>


<!-- ==========================================
     FORGOT PASSWORD PAGE
========================================== -->

<div class="forgot-page">


    <div class="forgot-card">


        <!-- ==================================
             BANK ICON
        =================================== -->

        <div class="logo">
            🏛️
        </div>


        <!-- ==================================
             SYSTEM TITLE
        =================================== -->

        <h1>
            Online Fee
            <br>
            Transaction System
        </h1>


        <!-- ==================================
             PAGE TITLE
        =================================== -->

        <h2>
            Forgot Password
        </h2>


        <!-- ==================================
             DESCRIPTION
        =================================== -->

        <p class="description">

            Enter your Student ID and registered
            email address to reset your password.

        </p>


        <!-- ==================================
             SUCCESS MESSAGE
        =================================== -->

        <?php if (!empty($message)) { ?>

            <div class="forgot-success">

                <?php
                echo htmlspecialchars($message);
                ?>

                <br><br>


                <!--
                    GO TO FIRST LOGIN INTERFACE
                -->

                <a
                    href="/online_fee_transtion_system/"
                >
                    Go to Login
                </a>

            </div>

        <?php } ?>


        <!-- ==================================
             ERROR MESSAGE
        =================================== -->

        <?php if (!empty($error)) { ?>

            <div class="forgot-error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php } ?>


        <!-- ==================================
             RESET PASSWORD FORM
        =================================== -->

        <?php if (empty($message)) { ?>


            <form
                action="forgot-password.php"
                method="POST"
            >


                <!-- Student ID -->

                <label for="student_id">
                    Student ID
                </label>

                <input
                    type="text"
                    id="student_id"
                    name="student_id"
                    placeholder="Enter Student ID"
                    value="<?php
                        echo htmlspecialchars(
                            $student_id
                        );
                    ?>"
                    required
                >


                <!-- Email -->

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter Registered Email"
                    value="<?php
                        echo htmlspecialchars(
                            $email
                        );
                    ?>"
                    required
                >


                <!-- New Password -->

                <label for="new_password">
                    New Password
                </label>

                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    placeholder="Enter New Password"
                    minlength="6"
                    required
                >


                <!-- Confirm Password -->

                <label for="confirm_password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm New Password"
                    minlength="6"
                    required
                >


                <!-- Reset Button -->

                <button
                    type="submit"
                    class="btn"
                >
                    Reset Password
                </button>


            </form>


        <?php } ?>


        <!-- ==================================
             BACK TO FIRST LOGIN
        =================================== -->

        <a
            href="/online_fee_transtion_system/"
            class="back-login"
        >
            ← Back to Login
        </a>


    </div>


</div>


</body>

</html>