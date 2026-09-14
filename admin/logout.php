<?php

session_start();

/* ==========================================
   CLEAR ADMIN SESSION
========================================== */

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);


/* ==========================================
   DESTROY SESSION
========================================== */

session_unset();
session_destroy();


/* ==========================================
   REDIRECT TO ADMIN LOGIN
========================================== */

header("Location: login.php");
exit();

?>
