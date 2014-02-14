<?php
require_once('core.php');

if($_SESSION['UID'] > 0) {
	if(isset($_POST['action']) && $_POST['action'] == "Logout") {
		session_start();
		$_SESSION['UID'] = 0;
		session_write_close();
		header('Location: .');
	} else {
		pageHeader();
		pageMenu();

		if(isset($_POST['action']) && $_POST['action']=="Delete") {
			$stmt = $GLOBALS['DB']->prepare("SELECT uid, pkey FROM pictures WHERE uid=:uid AND pid=:pid");
			$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
			$stmt->bindValue(':pid', $_POST['id'], PDO::PARAM_INT);
			if($stmt->execute()) {
				$row = $stmt->fetch();
				$stmt = null;
				if($row !== false) {
					$img_file = $GLOBALS['CONFIG']['DATAPATH'] . "/" . $row['uid'] . "/" . $row['pkey'];
					$resized = $img_file . "_thumb";
					unlink($img_file);
					unlink($resized);
					$stmt = null;
	
					$stmt = $GLOBALS['DB']->prepare("DELETE FROM pictures WHERE uid=:uid AND pid=:pid");
					$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
					$stmt->bindValue(':pid', $_POST['id'], PDO::PARAM_INT);
					if(!$stmt->execute()) {
						echo 'Picture deletion failed!';
					}
					$stmt = null;
				}
			}
		}
		
		$up_dir = $GLOBALS['CONFIG']['DATAPATH'] . "/" . $_SESSION['UID'] . "/";
		if(count($_FILES)) echo '<table><tr class="tr_header"><th colspan="2">Uploaded Files</th></tr>';
		foreach ($_FILES as $key => $value) {
			if($value['error'] == 0) {
				if(!is_dir($up_dir)) mkdir($up_dir);
				$up_key = genRandHash($value['name'] . $_SESSION['UID']);
				$up_file = $up_dir . $up_key;
				if(move_uploaded_file($value['tmp_name'], $up_file)) {
					$stmt = $GLOBALS['DB']->prepare("INSERT INTO pictures VALUES (null, :key, :name, ".$_SESSION['UID'].", 0, 0)");
					$stmt->bindValue(':key', $up_key, PDO::PARAM_STR);
					$stmt->bindValue(':name', basename($value['name']), PDO::PARAM_STR);
					if($stmt->execute()) {
						echo '<tr><td>'.basename($value['name']).'</td><td>Success!</td></tr>';
					} else {
						echo '<tr><td>'.basename($value['name']).'</td><td>Unknown upload error</td></tr>';
					}
					$stmt = null;
				} else {
					echo '<tr><td>'.basename($value['name']).'</td><td>The uploaded file was not readable</td></tr>';
				}
			} else {
			    switch($value['error']) {
			        case UPLOAD_ERR_INI_SIZE:
			            $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
			            break;
			        case UPLOAD_ERR_FORM_SIZE:
			            $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
			            break;
			        case UPLOAD_ERR_PARTIAL:
			            $message = "The uploaded file was only partially uploaded";
			            break;
			        case UPLOAD_ERR_NO_FILE:
			            $message = "No file was uploaded";
			            break;
			        case UPLOAD_ERR_NO_TMP_DIR:
			            $message = "Missing a temporary folder";
			            break;
			        case UPLOAD_ERR_CANT_WRITE:
			            $message = "Failed to write file to disk";
			            break;
			        case UPLOAD_ERR_EXTENSION:
			            $message = "File upload stopped by extension";
			            break;
			        default:
			            $message = "Unknown upload error";
			            break;
			    }
			    echo '<tr><td>'.basename($value['name']).'</td><td>'.$message.'</td></tr>';
			}
		}
		if(count($_FILES)) echo '</table><br />';

		echo '<table class="tbl_menu" style="margin-left: 1px; margin-bottom: 1px;">';
		echo '<form enctype="multipart/form-data" method="POST">';
		echo '<form method="POST">';
		echo '<input type="hidden" name="MAX_FILE_SIZE" value="104857600" />';
		echo '<tr class="grad_blue"><th colspan="2">Upload</th></tr>';
		echo '<tr><td><input type="file" name="img1" /></td><td><input type="submit" name="action" value="Upload" class="in_submit grad_gray" /></td></tr>';	
		echo '</form>';
		echo '</table>';

		$stmt = $GLOBALS['DB']->prepare("SELECT * FROM pictures WHERE uid=:uid AND gid = 0");
		$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
		if($stmt->execute()) {
			while($row = $stmt->fetch()) {
				echo '<div>';
				echo '<table class="tbl_menu">';
				echo '<tr class="grad_blue"><td>'.$row['ptitle'].'</td></tr>';
				echo '<tr><td align="center"><a target="_new" href="image/'.$row['pkey'].'"><img src="thumb/'.$row['pkey'].'" /></a></td></tr>';
				echo '<tr><td align="center"><form method="POST"><input type="hidden" name="id" value="'.$row['pid'].'" /><input type="submit" name="action" value="Delete" class="in_submit grad_gray" /></form></td></tr>';
				echo '</table>';
				echo '</div>';
			}
			$stmt = null;
		}
		
		
	}
} else {
	$loginstatus = "";
	if(isset($_POST['action']) && isset($_POST['email']) && isset($_POST['password'])) {
		$loginstatus = "Username or Password wrong!";
		$stmt = $GLOBALS['DB']->prepare("SELECT uid, password, salt, name FROM users WHERE email=:email");
		$stmt->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
		if($stmt->execute()) {
			$row = $stmt->fetch();
			if($row['password'] == safePassword($_POST['password'], $row['salt'])) {
				session_start();
				$_SESSION['UID'] = $row['uid'];
				$_SESSION['NAME'] = $row['name'];
				session_write_close();
				header('Location: .');
				$loginstatus = "OK";
			}
		}
		$stmt = null;
	}
	if($loginstatus != "OK") {
		pageHeader();
		echo '<table style="height:100%; width:100%;"><tr><td align="center" valign="middle">';
		echo '<form method="POST">';
		echo '<table class="tbl_menu">';
		echo '<tr class="grad_blue"><th colspan="2">Login</th></tr>';
		echo '<tr>';
		echo '<td>Email/Login:</td>';
		echo '<td class="td_input"><input type="text" name="email" /></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Password:</td>';
		echo '<td class="td_input"><input type="password" name="password" /></td>';
		echo '</tr>';
		echo '<tr><td>&nbsp;</td><td><input type="submit" name="action" value="Login" class="in_submit grad_gray" /></td></tr>';

		if($loginstatus != "") echo '<tr><th colspan="2">'.$loginstatus.'</th></tr>';

		echo '</table>';
		echo '</form>';
		echo '</td></tr></table>';
	}
}

pageFooter();
?>