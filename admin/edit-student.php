```php
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
   GET STUDENT ID
========================================== */

$student_id = trim($_GET['id'] ?? '');

if (empty($student_id)) {

    header("Location: students.php");

    exit();

}


$message = "";
$error = "";


/* ==========================================
   UPDATE STUDENT
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_name =
        trim($_POST['student_name'] ?? '');

    $course =
        trim($_POST['course'] ?? '');

    $semester =
        trim($_POST['semester'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $address =
        trim($_POST['address'] ?? '');

    $date_of_birth =
        trim($_POST['date_of_birth'] ?? '');


    /* ======================================
       VALIDATION
    ====================================== */

    if (empty($student_name)) {

        $error = "Student name is required.";

    } elseif (empty($course)) {

        $error = "Course is required.";

    } elseif (empty($semester)) {

        $error = "Semester is required.";

    } elseif (empty($email)) {

        $error = "Email is required.";

    } elseif (!filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )) {

        $error = "Please enter a valid email.";

    }


    /* ======================================
       CHECK DUPLICATE EMAIL
    ====================================== */

    if (empty($error)) {

        $email_check = mysqli_prepare(
            $conn,
            "SELECT student_id
             FROM students
             WHERE email = ?
             AND student_id != ?"
        );


        if (!$email_check) {

            $error =
                "Email check failed: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $email_check,
                "ss",
                $email,
                $student_id
            );

            mysqli_stmt_execute(
                $email_check
            );

            $email_result =
                mysqli_stmt_get_result(
                    $email_check
                );


            if (
                mysqli_num_rows(
                    $email_result
                ) > 0
            ) {

                $error =
                    "This email is already registered.";

            }


            mysqli_stmt_close(
                $email_check
            );
        }
    }


    /* ======================================
       UPDATE DATABASE
    ====================================== */

    if (empty($error)) {

        $update_sql =

            "UPDATE students
             SET
                student_name = ?,
                course = ?,
                semester = ?,
                email = ?,
                phone = ?,
                address = ?,
                date_of_birth = ?
             WHERE student_id = ?";


        $update_stmt = mysqli_prepare(
            $conn,
            $update_sql
        );


        if (!$update_stmt) {

            $error =
                "Update query failed: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $update_stmt,
                "ssssssss",
                $student_name,
                $course,
                $semester,
                $email,
                $phone,
                $address,
                $date_of_birth,
                $student_id
            );


            if (
                mysqli_stmt_execute(
                    $update_stmt
                )
            ) {

                $message =
                    "Student information updated successfully.";

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
   GET STUDENT INFORMATION
========================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM students
     WHERE student_id = ?"
);


if (!$stmt) {

    die(
        "Database query failed: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $student_id
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result($stmt);


$student =
    mysqli_fetch_assoc($result);


if (!$student) {

    die("Student record not found.");

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Edit Student</title>


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
select,
textarea {

    width: 100%;

    padding: 11px;

    border: 1px solid #ccd5df;

    border-radius: 6px;

    font-size: 13px;

    background: #fff;

    color: #172b4d;

    outline: none;
}

input:focus,
select:focus,
textarea:focus {

    border-color: #0866c6;

    box-shadow:
        0 0 0 2px #0866c620;
}

textarea {

    min-height: 90px;

    resize: vertical;
}


/* ==========================================
   STUDENT ID
========================================== */

.readonly-input {

    background: #f1f5f9;

    color: #64748b;

    cursor: not-allowed;
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

    transition: 0.2s;
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

    z-index: 9;
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

    .form-card {

        padding: 18px;
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


    <a href="students.php"
       class="active">

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

        Edit Student

    </h1>


    <p class="muted">

        Update student personal information.

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


        <form
            method="POST"
            action=""
        >


            <!-- STUDENT ID -->

            <label>
                Student ID
            </label>

            <input
                type="text"
                class="readonly-input"
                value="<?php

                    echo htmlspecialchars(
                        $student['student_id']
                    );

                ?>"
                readonly
            >


            <!-- NAME -->

            <label for="student_name">

                Student Name

            </label>

            <input
                type="text"
                id="student_name"
                name="student_name"
                value="<?php

                    echo htmlspecialchars(
                        $student['student_name']
                    );

                ?>"
                required
            >


            <!-- COURSE -->

            <label for="course">

                Course

            </label>

            <input
                type="text"
                id="course"
                name="course"
                value="<?php

                    echo htmlspecialchars(
                        $student['course']
                    );

                ?>"
                required
            >


            <!-- SEMESTER -->

            <label for="semester">

                Semester

            </label>

            <input
                type="text"
                id="semester"
                name="semester"
                value="<?php

                    echo htmlspecialchars(
                        $student['semester']
                    );

                ?>"
                required
            >


            <!-- EMAIL -->

            <label for="email">

                Email

            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?php

                    echo htmlspecialchars(
                        $student['email']
                        ?? ''
                    );

                ?>"
                required
            >


            <!-- PHONE -->

            <label for="phone">

                Phone

            </label>

            <input
                type="text"
                id="phone"
                name="phone"
                value="<?php

                    echo htmlspecialchars(
                        $student['phone']
                        ?? ''
                    );

                ?>"
            >


            <!-- ADDRESS -->

            <label for="address">

                Address

            </label>

            <textarea
                id="address"
                name="address"
            ><?php

                echo htmlspecialchars(
                    $student['address']
                    ?? ''
                );

            ?></textarea>


            <!-- DATE OF BIRTH -->

            <label for="date_of_birth">

                Date of Birth

            </label>

            <input
                type="date"
                id="date_of_birth"
                name="date_of_birth"
                value="<?php

                    echo htmlspecialchars(
                        $student['date_of_birth']
                        ?? ''
                    );

                ?>"
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
                    href="students.php"
                    class="btn cancel"
                >

                    ← Back to Students

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
```
