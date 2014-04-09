<?php require_once('core.php'); checkLogin();

// Reset Session Parameter
session_start();
$_SESSION['UID'] = 0;
$_SESSION['NAME'] = "Guest";
session_write_close();

// Back to Login
header('Location: login.php');
?>