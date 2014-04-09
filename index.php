<?php require_once('core.php'); startSession();
if($_SESSION['UID'] > 0) {
	header("Location: upload.php");
} else {
	header("Location: login.php");
}
?>