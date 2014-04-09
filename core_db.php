<?php

if( !file_exists($GLOBALS['CONFIG']['FULLDBFILE']) ) {
	// Create and initiate Database if not exists
    $GLOBALS['DB'] = new PDO('sqlite:' . $GLOBALS['CONFIG']['FULLDBFILE']);
	$GLOBALS['DB']->exec('CREATE TABLE users     (uid INTEGER PRIMARY KEY, email TEXT, password TEXT, salt TEXT, name TEXT)');

	$GLOBALS['DB']->exec('CREATE TABLE galleries (gid INTEGER PRIMARY KEY, gkey TEXT, gtitle TEXT, uid NUMERIC, gcount NUMERIC)');
	$GLOBALS['DB']->exec('CREATE INDEX idx_g_uid ON galleries (uid)');
	$GLOBALS['DB']->exec('CREATE INDEX idx_g_key ON galleries (gkey)');

	$GLOBALS['DB']->exec('CREATE TABLE pictures  (pid INTEGER PRIMARY KEY, pkey TEXT, ptitle TEXT, uid NUMERIC, gid NUMERIC, pcount NUMERIC)');
	$GLOBALS['DB']->exec('CREATE INDEX idx_p_uid ON pictures (uid, gid)');
	$GLOBALS['DB']->exec('CREATE INDEX idx_p_gid ON pictures (gid)');
	$GLOBALS['DB']->exec('CREATE INDEX idx_p_key ON pictures (ukey)');

	// Create default admin user
	$salt = genRandHash("Initial Administrator");
	$GLOBALS['DB']->exec("INSERT INTO users VALUES (1, 'admin@example.com', '".safePassword("admin", $salt)."', '".$salt."', 'admin')");
} else {
    $GLOBALS['DB'] = new PDO('sqlite:' . $GLOBALS['CONFIG']['FULLDBFILE']);
}
$GLOBALS['DB']->setAttribute(PDO::ATTR_TIMEOUT, 60)

?>