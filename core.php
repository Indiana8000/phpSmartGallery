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
// Helper #1
// ***

function startSession() {
	session_name($GLOBALS['CONFIG']['SESSION_NAME']);
	if(!isset($_REQUEST[$GLOBALS['CONFIG']['SESSION_NAME']])) {
		session_id(genRandHash());
		session_start();
		$_SESSION['UID'] = 0;
		$_SESSION['NAME'] = "Guest";
	} else {
		session_start();
	}
	session_write_close();
}

function checkLogin($lvl = -1) {
	startSession();
	if($lvl == -1) {
		if(!($_SESSION['UID'] > 0)) {
			header('Location: login.php');
			die();
		}
	} else {
		if($_SESSION['UID'] != $lvl) {
			header('Location: .');
			die();
		}
	}
}

function navBar() {
	echo '<nav class="navbar navbar-default" role="navigation">';
	echo '<div class="container-fluid">';
		echo '<div class="navbar-header hidden-xs"><a class="navbar-brand" href="about.php">phpSmartGallery</a></div>';
		if($_SESSION['UID']>0) {
		echo '<ul class="nav navbar-nav">';
			echo '<li><a href="upload.php">Upload</a></li>';
			echo '<li><a href="picture.php">Pictures</a></li>';
			echo '<li><a href="gallery.php">Gallery</a></li>';
			if($_SESSION['UID']==1) echo '<li class="hidden-xs"><a href="admin.php">Admin</a></li>';
			echo '<li class="hidden-xs"><a href="logout.php">Logout</a></li>';
		echo '</ul>';
		echo '<p class="navbar-text navbar-right hidden-xs">Signed in as <a href="password.php" class="navbar-link"><b>' . $_SESSION['NAME'] . '</b></a></p>';
		}
	echo '</div>';
	echo '</nav>';
}

// ***
// Helper #2
// ***

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