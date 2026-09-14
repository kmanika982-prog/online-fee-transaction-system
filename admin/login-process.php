```php
<?php

session_start();

include("../backend/db.php");


/* ==========================================
   ONLY POST REQUEST
========================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");

    exit();

}


/* ==========================================
   GET LOGIN DATA
========================================== */

$email = trim($_POST['email'] ?? '');

$password = $_POST['password'] ?? '';


/* ==========================================
   VALIDATION
========================================== */

if (empty($email) || empty($password)) {

    header(
        "Location: login.php?error=" .
        urlencode("Please enter email and password.")
    );

    exit();

}


/* ==========================================
   FIND ADMIN
========================================== */

$sql = "
    SELECT
        admin_id,
        admin_name,
        email,
        password
    FROM admins
    WHERE email = ?
    LIMIT 1
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die(
        "Admin Login Query Failed: " .
        mysqli_error($conn)
    );

}


/* ==========================================
   BIND EMAIL
========================================== */

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);


/* ==========================================
   EXECUTE
========================================== */

mysqli_stmt_execute($stmt);


/* ==========================================
   GET RESULT
========================================== */

$result = mysqli_stmt_get_result($stmt);


/* ==========================================
   CHECK ADMIN
========================================== */

if (mysqli_num_rows($result) === 1) {

    $admin = mysqli_fetch_assoc($result);


    /* ======================================
       CHECK PASSWORD
    ====================================== */

    /*
       For the current admin record created
       using plain password:

       admin123

       We temporarily support both
       password_hash() and plain password.
    */

    $password_correct = false;


    /* Hashed password */

    if (
        password_verify(
            $password,
            $admin['password']
        )
    ) {

        $password_correct = true;

    }


    /* Plain password fallback */

    elseif ($password === $admin['password']) {

        $password_correct = true;

    }


    /* ======================================
       LOGIN SUCCESS
    ====================================== */

    if ($password_correct) {

        $_SESSION['admin_id'] =
            $admin['admin_id'];

        $_SESSION['admin_name'] =
            $admin['admin_name'];

        $_SESSION['admin_email'] =
            $admin['email'];


        mysqli_stmt_close($stmt);


        header(
            "Location: dashboard.php"
        );

        exit();

    }


    /* ======================================
       WRONG PASSWORD
    ====================================== */

    else {

        mysqli_stmt_close($stmt);

        header(
            "Location: login.php?error=" .
            urlencode("Invalid admin password.")
        );

        exit();

    }


}


/* ==========================================
   ADMIN NOT FOUND
========================================== */

else {

    mysqli_stmt_close($stmt);

    header(
        "Location: login.php?error=" .
        urlencode("Admin email not found.")
    );

    exit();

}

?>
```
