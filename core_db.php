<?php

if( !file_exists($GLOBALS['CONFIG']['FULLDBFILE']) ) {
	// Create and initiate Database if not exists
    $GLOBALS['DB'] = new PDO('sqlite:' . $GLOBALS['CONFIG']['FULLDBFILE']);
	$GLOBALS['DB']->exec('CREATE TABLE users     (uid INTEGER PRIMARY KEY, email TEXT, password TEXT, salt TEXT, name TEXT)');
	$GLOBALS['DB']->exec('CREATE TABLE galleries (gid INTEGER PRIMARY KEY, gkey TEXT, gtitle TEXT, uid NUMERIC, gcount NUMERIC)');
	$GLOBALS['DB']->exec('CREATE TABLE pictures  (pid INTEGER PRIMARY KEY, pkey TEXT, ptitle TEXT, uid NUMERIC, gid NUMERIC, pcount NUMERIC)');

	// Create default admin user
	$salt = genRandHash("admin");
	$GLOBALS['DB']->exec("INSERT INTO users VALUES (1, 'admin', '".safePassword("admin", $salt)."', '".$salt."', 'Admin')");
} else {
    $GLOBALS['DB'] = new PDO('sqlite:' . $GLOBALS['CONFIG']['FULLDBFILE']);
}
$GLOBALS['DB']->setAttribute(PDO::ATTR_TIMEOUT, 60)

?>