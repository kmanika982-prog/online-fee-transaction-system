```php
<?php

$message = "";
$error = "";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Student Registration</title>

<link rel="stylesheet" href="style.css">

</head>

<body>


<div class="register-container">


    <div class="register-card">


        <!-- REGISTER ICON -->

        <div class="register-icon">
            👩‍🎓
        </div>


        <!-- TITLE -->

        <h1>
            Online Fee Transaction System
        </h1>

        <h2>
            Student Registration
        </h2>


        <!-- MESSAGE -->

        <?php if (!empty($_GET['error'])): ?>

            <div class="register-error">

                <?php
                echo htmlspecialchars($_GET['error']);
                ?>

            </div>

        <?php endif; ?>


        <!-- REGISTRATION FORM -->

        <form
            class="register-form"
            action="backend/register.php"
            method="POST"
            autocomplete="off"
        >


            <!-- STUDENT ID -->

            <label for="student_id">
                Student ID
            </label>

            <input
                type="text"
                id="student_id"
                name="student_id"
                placeholder="Enter your student ID"
                required
            >


            <!-- STUDENT NAME -->

            <label for="student_name">
                Student Name
            </label>

            <input
                type="text"
                id="student_name"
                name="student_name"
                placeholder="Enter your full name"
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
                placeholder="Enter your email"
                required
            >


            <!-- COURSE + SEMESTER -->

            <div class="register-row">


                <div>

                    <label for="course">
                        Course
                    </label>

                    <input
                        type="text"
                        id="course"
                        name="course"
                        placeholder="e.g. BCA"
                        required
                    >

                </div>


                <div>

                    <label for="semester">
                        Semester
                    </label>

                    <select
                        id="semester"
                        name="semester"
                        required
                    >

                        <option value="">
                            Select Semester
                        </option>

                        <option value="1st Semester">
                            1st Semester
                        </option>

                        <option value="2nd Semester">
                            2nd Semester
                        </option>

                        <option value="3rd Semester">
                            3rd Semester
                        </option>

                        <option value="4th Semester">
                            4th Semester
                        </option>

                        <option value="5th Semester">
                            5th Semester
                        </option>

                        <option value="6th Semester">
                            6th Semester
                        </option>

                        <option value="7th Semester">
                            7th Semester
                        </option>

                        <option value="8th Semester">
                            8th Semester
                        </option>

                    </select>

                </div>


            </div>


            <!-- PHONE -->

            <label for="phone">
                Phone Number
            </label>

            <input
                type="tel"
                id="phone"
                name="phone"
                placeholder="Enter your phone number"
                required
            >


            <!-- ADDRESS -->

            <label for="address">
                Address
            </label>

            <input
                type="text"
                id="address"
                name="address"
                placeholder="Enter your address"
                required
            >


            <!-- DATE OF BIRTH -->

            <label for="date_of_birth">
                Date of Birth
            </label>

            <input
                type="date"
                id="date_of_birth"
                name="date_of_birth"
                required
            >


            <!-- PASSWORD -->

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Create a password"
                minlength="6"
                required
            >


            <!-- CONFIRM PASSWORD -->

            <label for="confirm_password">
                Confirm Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="Confirm your password"
                minlength="6"
                required
            >


            <!-- REGISTER BUTTON -->

            <button
                type="submit"
                class="register-btn"
            >

                Create Account

            </button>


        </form>


        <!-- LOGIN LINK -->

        <div class="register-login-text">

            Already have an account?

            <a href="index.html">
                Login
            </a>

        </div>


    </div>


</div>


</body>

</html>
```
