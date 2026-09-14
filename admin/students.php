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
   DELETE STUDENT
========================================== */

if (isset($_GET['delete'])) {

    $student_id = trim($_GET['delete']);

    $delete_stmt = mysqli_prepare(
        $conn,
        "DELETE FROM students WHERE student_id = ?"
    );

    if ($delete_stmt) {

        mysqli_stmt_bind_param(
            $delete_stmt,
            "s",
            $student_id
        );

        mysqli_stmt_execute($delete_stmt);

        mysqli_stmt_close($delete_stmt);

    }

    header("Location: students.php");

    exit();

}


/* ==========================================
   SEARCH
========================================== */

$search = trim($_GET['search'] ?? '');


if ($search !== '') {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT
            student_id,
            student_name,
            course,
            semester,
            email,
            phone,
            address,
            date_of_birth
         FROM students
         WHERE student_id LIKE ?
            OR student_name LIKE ?
            OR email LIKE ?
         ORDER BY student_id DESC"
    );

    $search_value = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $search_value,
        $search_value,
        $search_value
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $result = mysqli_query(
        $conn,
        "SELECT
            student_id,
            student_name,
            course,
            semester,
            email,
            phone,
            address,
            date_of_birth
         FROM students
         ORDER BY student_id DESC"
    );

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Student Management</title>


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

    font-size: 12px;

    display: flex;

    align-items: center;

    gap: 8px;
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
   SEARCH
========================================== */

.search-box {

    background: #fff;

    border: 1px solid #e1e6ed;

    border-radius: 10px;

    padding: 18px;

    margin-top: 25px;

    display: flex;

    gap: 10px;
}

.search-box input {

    flex: 1;

    padding: 11px;

    border: 1px solid #ccd5df;

    border-radius: 6px;

    font-size: 13px;

    outline: none;
}

.search-box input:focus {

    border-color: #0866c6;

    box-shadow:
        0 0 0 2px #0866c620;
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
}

.btn:hover {

    background: #063b75;
}

.clear-btn {

    background: #64748b;
}

.clear-btn:hover {

    background: #475569;
}


/* ==========================================
   TABLE
========================================== */

.table-box {

    margin-top: 20px;

    background: white;

    border: 1px solid #e1e6ed;

    border-radius: 10px;

    overflow-x: auto;

    box-shadow:
        0 3px 10px #0000000d;
}

table {

    width: 100%;

    border-collapse: collapse;

    font-size: 12px;

    min-width: 1000px;
}

th,
td {

    border-bottom:
        1px solid #e1e6ed;

    padding: 12px;

    text-align: left;
}

th {

    background: #edf3fa;

    color: #172b4d;

    font-weight: 700;
}

tr:hover {

    background: #f8fbff;
}


/* ==========================================
   ACTION BUTTONS
========================================== */

.edit-btn {

    display: inline-block;

    background: #0866c6;

    color: white;

    padding: 6px 10px;

    border-radius: 5px;

    text-decoration: none;

    font-size: 10px;

    font-weight: 700;
}

.edit-btn:hover {

    background: #063b75;
}

.delete-btn {

    display: inline-block;

    background: #dc3545;

    color: white;

    padding: 6px 10px;

    border-radius: 5px;

    text-decoration: none;

    font-size: 10px;

    font-weight: 700;

    margin-left: 4px;
}

.delete-btn:hover {

    background: #b42318;
}


/* ==========================================
   EMPTY
========================================== */

.empty {

    text-align: center;

    padding: 30px;

    color: #6b7280;

    font-size: 13px;
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

    color: white;

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

    .search-box {

        flex-direction: column;
    }

    .search-box .btn {

        width: 100%;
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
                $_SESSION['admin_name'] ?? 'Administrator'
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
        Student Management
    </h1>


    <p class="muted">

        View and manage registered students.

    </p>


    <!-- SEARCH -->

    <form
        method="GET"
        action="students.php"
        class="search-box"
    >

        <input
            type="text"
            name="search"
            placeholder="Search by Student ID, Name or Email..."
            value="<?php
                echo htmlspecialchars($search);
            ?>"
        >


        <button
            type="submit"
            class="btn"
        >

            🔍 Search

        </button>


        <a
            href="students.php"
            class="btn clear-btn"
        >

            Clear

        </a>

    </form>


    <!-- TABLE -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>
                        Student ID
                    </th>

                    <th>
                        Student Name
                    </th>

                    <th>
                        Course
                    </th>

                    <th>
                        Semester
                    </th>

                    <th>
                        Email
                    </th>

                    <th>
                        Phone
                    </th>

                    <th>
                        Address
                    </th>

                    <th>
                        Date of Birth
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php

            if ($result &&
                mysqli_num_rows($result) > 0) {

                while (
                    $student =
                    mysqli_fetch_assoc($result)
                ) {

            ?>

                <tr>

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $student['student_id']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $student['student_name']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $student['course']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $student['semester']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $student['email'] ?? ''
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $student['phone'] ?? ''
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $student['address'] ?? ''
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $student['date_of_birth'] ?? ''
                        );
                        ?>

                    </td>


                    <td>

                        <a
                            href="edit-student.php?id=<?php
                                echo urlencode(
                                    $student['student_id']
                                );
                            ?>"
                            class="edit-btn"
                        >

                            ✏️ Edit

                        </a>


                        <a
                            href="students.php?delete=<?php
                                echo urlencode(
                                    $student['student_id']
                                );
                            ?>"
                            class="delete-btn"
                            onclick="return confirm(
                                'Are you sure you want to delete this student?'
                            );"
                        >

                            🗑️ Delete

                        </a>

                    </td>

                </tr>

            <?php

                }

            } else {

            ?>

                <tr>

                    <td
                        colspan="9"
                        class="empty"
                    >

                        No students found.

                    </td>

                </tr>

            <?php

            }

            ?>

            </tbody>

        </table>

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
