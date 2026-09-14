<?php

session_start();

include("../backend/db.php");


/* =====================================================
   IF ALREADY LOGGED IN
===================================================== */

if (isset($_SESSION['admin_id'])) {

    header("Location: dashboard.php");
    exit();

}


$error = "";


/* =====================================================
   LOGIN PROCESS
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $admin_login = trim($_POST['admin_login'] ?? '');
    $password    = trim($_POST['password'] ?? '');


    /* ================================================
       VALIDATION
    ================================================= */

    if (empty($admin_login) || empty($password)) {

        $error = "Please enter Admin ID/Email and Password.";

    } else {


        /* ============================================
           FIND ADMIN
        ============================================ */

        $sql = "SELECT *
                FROM admins
                WHERE admin_id = ?
                OR email = ?
                LIMIT 1";


        $stmt = mysqli_prepare($conn, $sql);


        if (!$stmt) {

            $error =
                "Login query failed: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $admin_login,
                $admin_login
            );


            mysqli_stmt_execute($stmt);


            $result =
                mysqli_stmt_get_result($stmt);


            /* ========================================
               CHECK ADMIN
            ======================================== */

            if (mysqli_num_rows($result) === 1) {

                $admin =
                    mysqli_fetch_assoc($result);


                /* ====================================
                   PASSWORD CHECK
                ==================================== */

                /*
                   This supports both:
                   1. Normal password
                   2. password_hash() password
                */

                $password_valid = false;


                if (
                    password_verify(
                        $password,
                        $admin['password']
                    )
                ) {

                    $password_valid = true;

                } elseif (
                    $password ===
                    $admin['password']
                ) {

                    $password_valid = true;

                }


                if ($password_valid) {


                    /* ================================
                       LOGIN SUCCESS
                    ================================= */

                    session_regenerate_id(true);


                    $_SESSION['admin_id'] =
                        $admin['admin_id'];


                    $_SESSION['admin_name'] =
                        $admin['admin_name'];


                    $_SESSION['admin_email'] =
                        $admin['email'] ?? '';


                    header(
                        "Location: dashboard.php"
                    );

                    exit();


                } else {

                    $error =
                        "Invalid password.";

                }


            } else {

                $error =
                    "Admin ID or Email not found.";

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

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Login | Online Fee Transaction System</title>


<style>

/* =====================================================
   RESET
===================================================== */

* {

    box-sizing: border-box;

    margin: 0;

    padding: 0;

}


/* =====================================================
   BODY
===================================================== */

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    min-height: 100vh;

    background:
        linear-gradient(
            135deg,
            #063b75,
            #0866c6
        );

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

}


/* =====================================================
   LOGIN CONTAINER
===================================================== */

.login-wrapper {

    width: 100%;

    max-width: 950px;

    min-height: 560px;

    background: #ffffff;

    border-radius: 18px;

    overflow: hidden;

    display: grid;

    grid-template-columns:
        1fr 1fr;

    box-shadow:
        0 20px 50px
        rgba(0, 0, 0, 0.20);

}


/* =====================================================
   LEFT PANEL
===================================================== */

.left-panel {

    background:
        linear-gradient(
            145deg,
            #063b75,
            #0866c6
        );

    color: #ffffff;

    display: flex;

    flex-direction: column;

    justify-content: center;

    padding: 50px;

    position: relative;

    overflow: hidden;

}


/* decorative circles */

.left-panel::before {

    content: "";

    position: absolute;

    width: 220px;

    height: 220px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.08);

    top: -70px;

    right: -70px;

}


.left-panel::after {

    content: "";

    position: absolute;

    width: 180px;

    height: 180px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.06);

    bottom: -70px;

    left: -60px;

}


/* =====================================================
   ADMIN ICON
===================================================== */

.admin-icon {

    width: 82px;

    height: 82px;

    background: #ffffff;

    border-radius: 20px;

    display: grid;

    place-items: center;

    font-size: 42px;

    margin-bottom: 25px;

    box-shadow:
        0 8px 20px
        rgba(0,0,0,0.15);

    position: relative;

    z-index: 1;

}


/* =====================================================
   LEFT TITLE
===================================================== */

.left-panel h1 {

    font-size: 28px;

    line-height: 1.3;

    margin-bottom: 15px;

    position: relative;

    z-index: 1;

}


.left-panel p {

    font-size: 14px;

    line-height: 1.7;

    color:
        rgba(255,255,255,0.85);

    max-width: 380px;

    position: relative;

    z-index: 1;

}


/* =====================================================
   FEATURES
===================================================== */

.features {

    margin-top: 30px;

    position: relative;

    z-index: 1;

}


.feature {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 13px;

    font-size: 13px;

}


.feature-icon {

    width: 27px;

    height: 27px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.15);

    display: grid;

    place-items: center;

}


/* =====================================================
   RIGHT PANEL
===================================================== */

.right-panel {

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 45px;

    background: #ffffff;

}


/* =====================================================
   LOGIN FORM
===================================================== */

.login-box {

    width: 100%;

    max-width: 380px;

}


/* =====================================================
   LOGIN HEADER
===================================================== */

.login-header {

    margin-bottom: 30px;

}


.login-header h2 {

    font-size: 25px;

    color: #172b4d;

    margin-bottom: 8px;

}


.login-header p {

    color: #6b7280;

    font-size: 13px;

}


/* =====================================================
   ERROR
===================================================== */

.error {

    background: #fff0f0;

    border: 1px solid #f5c2c0;

    color: #b42318;

    padding: 12px 14px;

    border-radius: 7px;

    font-size: 12px;

    margin-bottom: 18px;

}


/* =====================================================
   FORM GROUP
===================================================== */

.form-group {

    margin-bottom: 18px;

}


.form-group label {

    display: block;

    font-size: 12px;

    font-weight: 700;

    color: #172b4d;

    margin-bottom: 7px;

}


/* =====================================================
   INPUT WRAPPER
===================================================== */

.input-wrapper {

    position: relative;

}


/* =====================================================
   INPUT ICON
===================================================== */

.input-icon {

    position: absolute;

    left: 13px;

    top: 50%;

    transform:
        translateY(-50%);

    font-size: 17px;

    color: #64748b;

    pointer-events: none;

}


/* =====================================================
   INPUT
===================================================== */

.input-wrapper input {

    width: 100%;

    height: 46px;

    border: 1px solid #ccd5df;

    border-radius: 7px;

    padding:
        0 45px 0 42px;

    outline: none;

    font-size: 13px;

    color: #172b4d;

    transition: 0.2s;

}


.input-wrapper input:focus {

    border-color: #0866c6;

    box-shadow:
        0 0 0 3px
        rgba(8,102,198,0.10);

}


/* =====================================================
   PASSWORD TOGGLE
===================================================== */

.password-toggle {

    position: absolute;

    right: 13px;

    top: 50%;

    transform:
        translateY(-50%);

    border: none;

    background: transparent;

    cursor: pointer;

    font-size: 16px;

    color: #64748b;

}


/* =====================================================
   LOGIN BUTTON
===================================================== */

.login-btn {

    width: 100%;

    height: 46px;

    border: none;

    border-radius: 7px;

    background: #0866c6;

    color: #ffffff;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

    margin-top: 8px;

    transition: 0.2s;

}


.login-btn:hover {

    background: #063b75;

    transform:
        translateY(-1px);

}


/* =====================================================
   BACK LINK
===================================================== */

.back-link {

    text-align: center;

    margin-top: 22px;

}


.back-link a {

    color: #0866c6;

    text-decoration: none;

    font-size: 12px;

    font-weight: 600;

}


.back-link a:hover {

    text-decoration: underline;

}


/* =====================================================
   FOOTER TEXT
===================================================== */

.login-footer {

    text-align: center;

    margin-top: 28px;

    color: #94a3b8;

    font-size: 10px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 750px) {

    .login-wrapper {

        grid-template-columns: 1fr;

        max-width: 500px;

    }


    .left-panel {

        padding: 35px;

        min-height: 300px;

    }


    .left-panel h1 {

        font-size: 23px;

    }


    .features {

        display: none;

    }


    .right-panel {

        padding: 35px;

    }

}


@media (max-width: 450px) {

    body {

        padding: 10px;

    }


    .login-wrapper {

        border-radius: 12px;

    }


    .left-panel {

        padding: 28px;

    }


    .right-panel {

        padding: 28px 22px;

    }


    .left-panel p {

        font-size: 12px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     LOGIN WRAPPER
===================================================== -->

<div class="login-wrapper">


    <!-- =================================================
         LEFT PANEL
    ================================================== -->

    <section class="left-panel">


        <div class="admin-icon">

            👨‍💼

        </div>


        <h1>

            Admin Panel

        </h1>


        <p>

            Manage students, fees, payments
            and reports from one secure
            administration dashboard.

        </p>


        <div class="features">


            <div class="feature">

                <div class="feature-icon">
                    👨‍🎓
                </div>

                <span>
                    Manage Students
                </span>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    💰
                </div>

                <span>
                    Manage Fee Records
                </span>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    💳
                </div>

                <span>
                    Monitor Payments
                </span>

            </div>


            <div class="feature">

                <div class="feature-icon">
                    📊
                </div>

                <span>
                    View Reports
                </span>

            </div>


        </div>


    </section>


    <!-- =================================================
         RIGHT PANEL
    ================================================== -->

    <section class="right-panel">


        <div class="login-box">


            <!-- HEADER -->

            <div class="login-header">

                <h2>

                    Welcome Back

                </h2>

                <p>

                    Login to your administrator account.

                </p>

            </div>


            <!-- ERROR -->

            <?php if (!empty($error)) { ?>

                <div class="error">

                    ⚠️

                    <?php

                    echo htmlspecialchars(
                        $error
                    );

                    ?>

                </div>

            <?php } ?>


            <!-- FORM -->

            <form
                method="POST"
                action="login.php"
            >


                <!-- ADMIN ID / EMAIL -->

                <div class="form-group">

                    <label for="admin_login">

                        Admin ID / Email

                    </label>


                    <div class="input-wrapper">


                        <span class="input-icon">

                            👤

                        </span>


                        <input
                            type="text"
                            id="admin_login"
                            name="admin_login"
                            placeholder="Enter Admin ID or Email"
                            autocomplete="username"
                            value="<?php

                                echo htmlspecialchars(
                                    $_POST['admin_login']
                                    ?? ''
                                );

                            ?>"
                            required
                        >


                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">

                        Password

                    </label>


                    <div class="input-wrapper">


                        <span class="input-icon">

                            🔒

                        </span>


                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >


                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword()"
                            id="toggleBtn"
                        >

                            👁️

                        </button>


                    </div>

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    class="login-btn"
                >

                    🔐 Login to Admin Panel

                </button>


            </form>


            <!-- BACK -->

            <div class="back-link">

                <a href="../index.html">

                    ← Back to Student Login

                </a>

            </div>


            <!-- FOOTER -->

            <div class="login-footer">

                © 2026 Online Fee Transaction System

            </div>


        </div>


    </section>


</div>


<!-- =====================================================
     PASSWORD SCRIPT
===================================================== -->

<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const button =
        document.getElementById("toggleBtn");


    if (password.type === "password") {

        password.type = "text";

        button.textContent = "🙈";

    } else {

        password.type = "password";

        button.textContent = "👁️";

    }

}

</script>


</body>

</html>