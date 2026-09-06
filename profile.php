```php
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

$message = "";
$error = "";


// ==========================================
// UPDATE PROFILE
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_name = trim($_POST['student_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');


    // Validation
    if (empty($student_name)) {

        $error = "Student name is required.";

    } elseif (empty($email)) {

        $error = "Email is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // ======================================
        // CHECK DUPLICATE EMAIL
        // ======================================

        $email_check = mysqli_prepare(
            $conn,
            "SELECT student_id
             FROM students
             WHERE email = ?
             AND student_id != ?"
        );

        if (!$email_check) {

            $error = "Email check failed: " . mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $email_check,
                "ss",
                $email,
                $student_id
            );

            mysqli_stmt_execute($email_check);

            $email_result = mysqli_stmt_get_result($email_check);

            if (mysqli_num_rows($email_result) > 0) {

                $error = "This email is already registered.";

            }

            mysqli_stmt_close($email_check);
        }


        // ======================================
        // UPDATE DATABASE
        // ======================================

        if (empty($error)) {

            $update_sql = "UPDATE students
                           SET student_name = ?,
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

                $error = "Update query failed: " . mysqli_error($conn);

            } else {

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "ssssss",
                    $student_name,
                    $email,
                    $phone,
                    $address,
                    $date_of_birth,
                    $student_id
                );

                if (mysqli_stmt_execute($update_stmt)) {

                    $message = "Profile updated successfully.";

                } else {

                    $error = "Profile update failed: " .
                             mysqli_stmt_error($update_stmt);
                }

                mysqli_stmt_close($update_stmt);
            }
        }
    }
}


// ==========================================
// GET STUDENT INFORMATION
// ==========================================

$sql = "SELECT *
        FROM students
        WHERE student_id = ?";

$stmt = mysqli_prepare(
    $conn,
    $sql
);

if (!$stmt) {

    die("Database query failed: " . mysqli_error($conn));

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

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Student Profile</title>

<link rel="stylesheet" href="style.css">


<style>

/* ==========================================
   EDIT PROFILE AREA
========================================== */

.edit-profile-box {

    display: none;

    margin-top: 20px;

    padding-top: 20px;

    border-top: 1px solid #e5e7eb;

}

.edit-profile-box.show {

    display: block;

}

.edit-title {

    font-size: 16px;

    color: #172b4d;

    margin-bottom: 15px;

}

.edit-input {

    margin-bottom: 5px;

}

.edit-buttons {

    display: flex;

    gap: 10px;

    margin-top: 20px;

}

.edit-btn {

    display: inline-block;

    border: none;

    background: #0866c6;

    color: #fff;

    padding: 10px 18px;

    border-radius: 6px;

    cursor: pointer;

    font-size: 12px;

    font-weight: 700;

}

.edit-btn:hover {

    background: #063b75;

}

.cancel-btn {

    display: inline-block;

    padding: 10px 18px;

    border-radius: 6px;

    background: #64748b;

    color: #fff;

    font-size: 12px;

    font-weight: 700;

    cursor: pointer;

    border: none;

}

.cancel-btn:hover {

    background: #475569;

}

.edit-profile-button {

    margin-top: 20px;

}


/* ==========================================
   SUCCESS / ERROR
========================================== */

.profile-success {

    max-width: 650px;

    background: #dff5e6;

    color: #168542;

    border: 1px solid #bde5c9;

    padding: 12px 15px;

    border-radius: 6px;

    font-size: 12px;

    font-weight: 600;

    margin-top: 15px;

}

.profile-error {

    max-width: 650px;

    background: #ffe8e8;

    color: #b42318;

    border: 1px solid #f5c2c0;

    padding: 12px 15px;

    border-radius: 6px;

    font-size: 12px;

    font-weight: 600;

    margin-top: 15px;

}


/* ==========================================
   MOBILE
========================================== */

@media (max-width: 650px) {

    .edit-buttons {

        flex-direction: column;

    }

    .edit-btn,
    .cancel-btn {

        width: 100%;

        text-align: center;

    }

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
                );
                ?>

                -

                <?php
                echo htmlspecialchars(
                    $student['semester']
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


    <a href="profile.php" class="active">

        <span>👤</span>
        <span>Profile</span>

    </a>


    <a href="fee-details.php">

        <span>💰</span>
        <span>Fee Details</span>

    </a>


    <a href="pay-fee.php">

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
        My Profile
    </h1>

    <p class="muted">
        Student personal information
    </p>


    <!-- ======================================
         SUCCESS MESSAGE
    ======================================= -->

    <?php if (!empty($message)) { ?>

        <div class="profile-success">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php } ?>


    <!-- ======================================
         ERROR MESSAGE
    ======================================= -->

    <?php if (!empty($error)) { ?>

        <div class="profile-error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php } ?>


    <!-- ======================================
         PROFILE BOX
    ======================================= -->

    <div class="profile-box">


        <!-- PROFILE HEADER -->

        <div class="profile-top">

            <div class="profile-avatar">
                👩
            </div>

            <b>

                <?php
                echo htmlspecialchars(
                    $student['student_name']
                );
                ?>

            </b>

            <p class="muted">

                <?php
                echo htmlspecialchars(
                    $student['course']
                );
                ?>

                -

                <?php
                echo htmlspecialchars(
                    $student['semester']
                );
                ?>

            </p>

        </div>


        <!-- ==================================
             STUDENT INFORMATION
        =================================== -->

        <div class="row">

            <b>
                Student Name
            </b>

            <span>

                <?php
                echo htmlspecialchars(
                    $student['student_name']
                );
                ?>

            </span>

        </div>


        <div class="row">

            <b>
                Student ID
            </b>

            <span>

                <?php
                echo htmlspecialchars(
                    $student['student_id']
                );
                ?>

            </span>

        </div>


        <div class="row">

            <b>
                Course
            </b>

            <span>

                <?php
                echo htmlspecialchars(
                    $student['course']
                );
                ?>

            </span>

        </div>


        <div class="row">

            <b>
                Semester
            </b>

            <span>

                <?php
                echo htmlspecialchars(
                    $student['semester']
                );
                ?>

            </span>

        </div>


        <div class="row">

            <b>
                Email
            </b>

            <span>

                <?php
                echo htmlspecialchars(
                    $student['email'] ?? ''
                );
                ?>

            </span>

        </div>


        <div class="row">

            <b>
                Phone
            </b>

            <span>

                <?php
                echo htmlspecialchars(
                    $student['phone'] ?? ''
                );
                ?>

            </span>

        </div>


        <div class="row">

            <b>
                Address
            </b>

            <span>

                <?php
                echo htmlspecialchars(
                    $student['address'] ?? ''
                );
                ?>

            </span>

        </div>


        <div class="row">

            <b>
                Date of Birth
            </b>

            <span>

                <?php
                echo htmlspecialchars(
                    $student['date_of_birth'] ?? ''
                );
                ?>

            </span>

        </div>


        <!-- ==================================
             EDIT PROFILE BUTTON
        =================================== -->

        <div class="edit-profile-button">

            <button
                type="button"
                class="btn"
                onclick="showEditProfile()"
            >

                ✏️ Edit Profile

            </button>

        </div>


        <!-- ==================================
             EDIT PROFILE FORM
             HIDDEN BY DEFAULT
        =================================== -->

        <div
            id="editProfileBox"
            class="edit-profile-box"
        >

            <h3 class="edit-title">

                Edit Profile

            </h3>


            <form
                method="POST"
                action="profile.php"
            >


                <!-- STUDENT NAME -->

                <label for="student_name">

                    Student Name

                </label>

                <input
                    type="text"
                    id="student_name"
                    name="student_name"
                    class="edit-input"
                    value="<?php

                        echo htmlspecialchars(
                            $student['student_name']
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
                    class="edit-input"
                    value="<?php

                        echo htmlspecialchars(
                            $student['email'] ?? ''
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
                    class="edit-input"
                    value="<?php

                        echo htmlspecialchars(
                            $student['phone'] ?? ''
                        );

                    ?>"
                >


                <!-- ADDRESS -->

                <label for="address">

                    Address

                </label>

                <input
                    type="text"
                    id="address"
                    name="address"
                    class="edit-input"
                    value="<?php

                        echo htmlspecialchars(
                            $student['address'] ?? ''
                        );

                    ?>"
                >


                <!-- DATE OF BIRTH -->

                <label for="date_of_birth">

                    Date of Birth

                </label>

                <input
                    type="date"
                    id="date_of_birth"
                    name="date_of_birth"
                    class="edit-input"
                    value="<?php

                        echo htmlspecialchars(
                            $student['date_of_birth'] ?? ''
                        );

                    ?>"
                >


                <!-- BUTTONS -->

                <div class="edit-buttons">

                    <button
                        type="submit"
                        class="edit-btn"
                    >

                        💾 Save Changes

                    </button>


                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="hideEditProfile()"
                    >

                        Cancel

                    </button>

                </div>


            </form>

        </div>


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

function showEditProfile() {

    const editBox =
        document.getElementById("editProfileBox");

    editBox.classList.add("show");

    editBox.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });

}


function hideEditProfile() {

    const editBox =
        document.getElementById("editProfileBox");

    editBox.classList.remove("show");

}

</script>


</body>

</html>
```
