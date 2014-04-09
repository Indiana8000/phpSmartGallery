<?php require_once('core.php'); startSession();

header('Vary: Accept');
if (isset($_SERVER['HTTP_ACCEPT']) &&
	(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
	header('Content-type: application/json');
} else {
	header('Content-type: text/plain');
}

if($_SESSION['UID'] > 0) {
	$tttt;
	$resultCount = -1;
	$result = Array();
	$result['files'] = Array();
	$up_dir = $GLOBALS['CONFIG']['DATAPATH'] . "/" . $_SESSION['UID'] . "/";
	foreach ($_FILES as $key => $value) 
	foreach ($value['error'] as $item => $error) {
		$resultCount++;
		$result['files'][$resultCount] = Array();
		$result['files'][$resultCount]['name'] = $value['name'][$item];
		$result['files'][$resultCount]['size'] = filesize($value['tmp_name'][$item]);
		if($value['error'][$item] == UPLOAD_ERR_OK) {
			if(!is_dir($up_dir)) mkdir($up_dir);
			$up_key = genRandHash($value['name'][$item] . $_SESSION['UID']);
			$up_file = $up_dir . $up_key;
			if(move_uploaded_file($value['tmp_name'][$item], $up_file)) {
				if(isset($_POST['gid']) && $_POST['gid'] > 0) {
					$stmt = $GLOBALS['DB']->prepare("SELECT gid FROM galleries WHERE uid = :uid AND gid = :gid");
					$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
					$stmt->bindValue(':gid', $_POST['gid'], PDO::PARAM_INT);
					if($stmt->execute()) {
						if($row = $stmt->fetch())
						 	$_POST['gid'] = $row['gid'];
						 else
						 	$_POST['gid'] = 0;
					} else {
						$_POST['gid'] = 0;
					}
				} else {
					$_POST['gid'] = 0;
				}
				
				$stmt = $GLOBALS['DB']->prepare("INSERT INTO pictures VALUES (null, :key, :name, ".$_SESSION['UID'].", :gid, 0)");
				$stmt->bindValue(':key', $up_key, PDO::PARAM_STR);
				$stmt->bindValue(':name', $value['name'][$item], PDO::PARAM_STR);
				$stmt->bindValue(':gid', $_POST['gid'], PDO::PARAM_INT);
				if($stmt->execute()) {
					$result['files'][$resultCount]['url'] = 'image/' . $up_key;
					$result['files'][$resultCount]['thumbnailUrl'] = 'thumb/' . $up_key;
					$result['files'][$resultCount]['deleteUrl'] = 'delete/' . $up_key;
					$result['files'][$resultCount]['deleteType'] = "DELETE";
				} else {
					$result['files'][$resultCount]['error'] = "Unknown SQL error";
				}
				$stmt = null;
			} else {
				$result['files'][$resultCount]['error'] = "The uploaded file was not readable";
			}
		} else {
			switch($value['error'][$item]) {
				case UPLOAD_ERR_INI_SIZE:
				$result['files'][$resultCount]['error'] = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
				break;
				case UPLOAD_ERR_FORM_SIZE:
				$result['files'][$resultCount]['error'] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
				break;
				case UPLOAD_ERR_PARTIAL:
				$result['files'][$resultCount]['error'] = "The uploaded file was only partially uploaded";
				break;
				case UPLOAD_ERR_NO_FILE:
				$result['files'][$resultCount]['error'] = "No file was uploaded";
				break;
				case UPLOAD_ERR_NO_TMP_DIR:
				$result['files'][$resultCount]['error'] = "Missing a temporary folder";
				break;
				case UPLOAD_ERR_CANT_WRITE:
				$result['files'][$resultCount]['error'] = "Failed to write file to disk";
				break;
				case UPLOAD_ERR_EXTENSION:
				$result['files'][$resultCount]['error'] = "File upload stopped by extension";
				break;
				default:
				$result['files'][$resultCount]['error'] = "Unknown upload error";
				break;
			}			
		}
	}
	echo json_encode($result);
} else {
	echo "Error";
}

?>