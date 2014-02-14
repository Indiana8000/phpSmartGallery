<?php


// ***
// CONFIGURATION
// ***
require_once('config.php');

// Set internal Variables
$GLOBALS['CONFIG']['DATAPATH'] = realpath($GLOBALS['CONFIG']['DATAPATH']);
$GLOBALS['CONFIG']['FULLDBFILE'] = $GLOBALS['CONFIG']['DATAPATH'] . "/" . $GLOBALS['CONFIG']['DATABASE'];
$GLOBALS['CONFIG']['MENU'] = false;


// ***
// DATABASE
// ***
require_once('core_db.php');


// ***
// SESSION
// ***
session_name($GLOBALS['CONFIG']['SESSION_NAME']);
if(!isset($_REQUEST[$GLOBALS['CONFIG']['SESSION_NAME']])) {
	session_id(genRandHash());
	session_start();
	$_SESSION['UID'] = 0;
	$_SESSION['NAME'] = 0;
} else {
	session_start();
}
session_write_close();

// ----- Helper #1 -----

// Header for each page
function pageHeader() {
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");   // Date in the past

	echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
	echo '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
	echo '<head>' . "\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
	echo '<meta http-equiv="cache-control" content="no-cache, must-revalidate">' . "\n";
	echo '<meta http-equiv="expires" content="0">' . "\n";

	echo '<link type="text/css" href="style.css" rel="stylesheet" />' . "\n";
	//echo '<script type="text/javascript" src="https://code.jquery.com/jquery-1.10.2.min.js"></script>' . "\n";

	echo '<title>phpSmartGallery</title>' . "\n";
	echo '</head>';
	echo '<body>';
}

// Cleanup all at the end of each page
function pageFooter() {
	if($GLOBALS['CONFIG']['MENU']) {
		echo '</td></tr></table>';
	}
	$GLOBALS['DB'] = null;
	echo '</body></html>';
}

// Menü
function pageMenu() {
	$GLOBALS['CONFIG']['MENU'] = true;
	// Main Table
	echo '<table><tr><td valign="top">';
		// Menü
		echo '<table class="tbl_menu">';
		echo '<tr class="grad_blue"><th>Menü</th></tr>';
		if($_SESSION['UID'] > 0) {
			echo '<tr><td><form method="GET"  action="."><input type="submit" name="" value="Pictures" class="in_submit grad_gray" /></form></td></tr>';
			echo '<tr><td><form method="POST" action="."><input type="submit" name="action" value="Logout" class="in_submit grad_gray" /></form></td></tr>';
			if($_SESSION['UID']==1) {
				echo '<tr class="grad_blue"><th>Admin</th></tr>';
				echo '<tr><td><form method="GET"  action="manage.php"><input type="submit" name="" value="List" class="in_submit grad_gray" /></form></td></tr>';
				echo '<tr><td><form method="POST" action="manage.php"><input type="submit" name="action" value="Add"  class="in_submit grad_gray" /></form></td></tr>';
			}
		}
		echo '</table>';
	// Content Table
	echo '</td><td align="left" valign="top" class="table_frame">';
}

// ----- Helper #2 -----

function genRandHash($salt = "***") {
	return str_baseconvert(hash($GLOBALS['CONFIG']['HASH'], str_repeat($salt . time() . $_SERVER['REMOTE_ADDR'] . make_seed(), rand(3,8))), 16, 36);
}

function safePassword($password, $salt) {
	if(strlen($password) < 10) $password = str_pad($password, 10, "*", STR_PAD_RIGHT);
	$password = str_split($password, (strlen($password) / 2) + 2);
	$password = hash($GLOBALS['CONFIG']['HASH'], str_repeat($GLOBALS['CONFIG']['SALT'] . $password[0] . $salt . $password[1], 13));

	for($i = 0 ; $i < 3 ; $i++) {
		$password = hash($GLOBALS['CONFIG']['HASH'], $GLOBALS['CONFIG']['SALT'] . $password . $salt);
	}
	return str_baseconvert($password, 16, 36);
}

function str_baseconvert($str, $frombase=10, $tobase=36) {
    $str = trim($str);
    if (intval($frombase) != 10) {
        $len = strlen($str);
        $q = 0;
        for ($i=0; $i<$len; $i++) {
            $r = base_convert($str[$i], $frombase, 10);
            $q = bcadd(bcmul($q, $frombase), $r);
        }
    }
    else $q = $str;
 
    if (intval($tobase) != 10) {
        $s = '';
        while (bccomp($q, '0', 0) > 0) {
            $r = intval(bcmod($q, $tobase));
            $s = base_convert($r, 10, $tobase) . $s;
            $q = bcdiv($q, $tobase, 0);
        }
    }
    else $s = $q;
 
    return $s;
}

function make_seed() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}

?>