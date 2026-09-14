```php
<?php

/* ==========================================
   DATABASE CONNECTION
========================================== */

include("db.php");


/* ==========================================
   ONLY POST REQUEST
========================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../register.php");

    exit();
}


/* ==========================================
   GET FORM DATA
========================================== */

$student_id = trim($_POST['student_id'] ?? '');

$student_name = trim($_POST['student_name'] ?? '');

$email = trim($_POST['email'] ?? '');

$course = trim($_POST['course'] ?? '');

$semester = trim($_POST['semester'] ?? '');

$phone = trim($_POST['phone'] ?? '');

$address = trim($_POST['address'] ?? '');

$date_of_birth = trim($_POST['date_of_birth'] ?? '');

$password = $_POST['password'] ?? '';

$confirm_password = $_POST['confirm_password'] ?? '';


/* ==========================================
   VALIDATION
========================================== */

if (
    empty($student_id) ||
    empty($student_name) ||
    empty($email) ||
    empty($course) ||
    empty($semester) ||
    empty($phone) ||
    empty($address) ||
    empty($date_of_birth) ||
    empty($password) ||
    empty($confirm_password)
) {

    header(
        "Location: ../register.php?error=" .
        urlencode("Please fill in all fields.")
    );

    exit();
}


/* ==========================================
   VALID EMAIL
========================================== */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header(
        "Location: ../register.php?error=" .
        urlencode("Please enter a valid email address.")
    );

    exit();
}


/* ==========================================
   PASSWORD LENGTH
========================================== */

if (strlen($password) < 6) {

    header(
        "Location: ../register.php?error=" .
        urlencode("Password must be at least 6 characters.")
    );

    exit();
}


/* ==========================================
   PASSWORD MATCH
========================================== */

if ($password !== $confirm_password) {

    header(
        "Location: ../register.php?error=" .
        urlencode("Passwords do not match.")
    );

    exit();
}


/* ==========================================
   CHECK EXISTING STUDENT ID
========================================== */

$check_id = mysqli_prepare(
    $conn,
    "SELECT student_id
     FROM students
     WHERE student_id = ?"
);


if (!$check_id) {

    die("Database error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $check_id,
    "s",
    $student_id
);


mysqli_stmt_execute($check_id);

$id_result = mysqli_stmt_get_result($check_id);


if (mysqli_num_rows($id_result) > 0) {

    mysqli_stmt_close($check_id);

    header(
        "Location: ../register.php?error=" .
        urlencode("Student ID already exists.")
    );

    exit();
}


mysqli_stmt_close($check_id);


/* ==========================================
   CHECK EXISTING EMAIL
========================================== */

$check_email = mysqli_prepare(
    $conn,
    "SELECT student_id
     FROM students
     WHERE email = ?"
);


if (!$check_email) {

    die("Database error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $check_email,
    "s",
    $email
);


mysqli_stmt_execute($check_email);

$email_result = mysqli_stmt_get_result($check_email);


if (mysqli_num_rows($email_result) > 0) {

    mysqli_stmt_close($check_email);

    header(
        "Location: ../register.php?error=" .
        urlencode("Email is already registered.")
    );

    exit();
}


mysqli_stmt_close($check_email);


/* ==========================================
   HASH PASSWORD
========================================== */

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* ==========================================
   INSERT STUDENT
========================================== */

$sql = "
    INSERT INTO students
    (
        student_id,
        student_name,
        course,
        semester,
        email,
        phone,
        address,
        date_of_birth,
        password
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die("Registration query failed: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $stmt,
    "sssssssss",
    $student_id,
    $student_name,
    $course,
    $semester,
    $email,
    $phone,
    $address,
    $date_of_birth,
    $hashed_password
);


/* ==========================================
   SAVE DATA
========================================== */

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header(
        "Location: ../index.html?registered=1"
    );

    exit();

} else {

    $error = mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    header(
        "Location: ../register.php?error=" .
        urlencode("Registration failed: " . $error)
    );

    exit();
}

?>
```
