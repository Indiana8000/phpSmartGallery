<?php
// Initiate Configuration Array
$GLOBALS['CONFIG'] = Array();

// Set the PATH where everything get stored. Recommend a path outside webserver.
$GLOBALS['CONFIG']['DATAPATH'] = 'data';

// The SQLite Database
$GLOBALS['CONFIG']['DATABASE'] = 'mysqlitedb.db';

// Session Cookie
$GLOBALS['CONFIG']['SESSION_NAME'] = 'PHPSMARTGALLERY';

// Hash Algorithm
$GLOBALS['CONFIG']['HASH'] = 'sha256';

// Global salt as addition to every private salt.
$GLOBALS['CONFIG']['SALT'] = 'A unique String';

// Thumbnail Size
$GLOBALS['CONFIG']['WIDTH'] = 120;
$GLOBALS['CONFIG']['HEIGHT'] = 80;


?>