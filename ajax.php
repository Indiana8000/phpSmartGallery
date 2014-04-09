<?php require_once('core.php'); startSession();

$feedback = 'Unknown Querry! ' . json_encode($_POST);

if(isset($_POST['action'])) {

	// Login

	if($_POST['action'] == "login" && isset($_POST['login']) && isset($_POST['password'])) {
		$feedback = 'Username or Password wrong!';
		$stmt = $GLOBALS['DB']->prepare("SELECT uid, password, salt, name FROM users WHERE name=:name");
		$stmt->bindValue(':name', $_POST['login'], PDO::PARAM_STR);
		if($stmt->execute()) {
			if($row = $stmt->fetch()) {
				if($row['password'] == safePassword($_POST['password'], $row['salt'])) {
					session_start();
					$_SESSION['UID'] = $row['uid'];
					$_SESSION['NAME'] = $row['name'];
					session_write_close();
					$feedback = 'OK';
				} else {
					$feedback = 'Username or Password wrong!';
				}
			}
		} else {
			$feedback = 'SQL Error!';
		}
		$stmt = null;
	}

	if($_POST['action'] == "password" && $_SESSION['UID'] > 0 && isset($_POST['old_pwd']) && isset($_POST['new_pwd'])) {
		$stmt = $GLOBALS['DB']->prepare("SELECT password, salt, email, name FROM users WHERE uid=:uid");
		$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
		if($stmt->execute()) {
			if($row = $stmt->fetch()) {
				if($row['password'] == safePassword($_POST['old_pwd'], $row['salt'])) {
					$stmt = null;

					$salt = genRandHash($row['name'] . $row['email'] . $_POST['new_pwd'] . $row['salt']);
					$stmt = $GLOBALS['DB']->prepare("UPDATE users SET password=:password, salt=:salt WHERE uid=:uid");
					$stmt->bindValue(':password', safePassword($_POST['new_pwd'], $salt), PDO::PARAM_STR);
					$stmt->bindValue(':salt', $salt, PDO::PARAM_STR);
					$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
					if($stmt->execute()) {
						$feedback = 'OK';
					} else {
						$feedback = 'SQL Error!';
					}

				} else {
					$feedback = 'Wrong Password!';
				}
			} else {
				$feedback = 'SQL Error!';
			}
		} else {
			$feedback = 'SQL Error!';
		}
		$stmt = null;
	}
	
	// Admin

	if($_POST['action'] == "admin_create" && $_SESSION['UID'] == 1 && isset($_POST['login']) && isset($_POST['email']) && isset($_POST['password'])) {
		$salt = genRandHash($_POST['login'] . $_POST['email'] . $_POST['password']);
		$stmt = $GLOBALS['DB']->prepare("INSERT INTO users VALUES (null, :email, :password, :salt, :name)");
		$stmt->bindValue(':email'   , $_POST['email']                        , PDO::PARAM_STR);
		$stmt->bindValue(':password', safePassword($_POST['password'], $salt), PDO::PARAM_STR);
		$stmt->bindValue(':salt'    , $salt                                  , PDO::PARAM_STR);
		$stmt->bindValue(':name'    , $_POST['login']                        , PDO::PARAM_STR);
		if($stmt->execute()) {
			$feedback = 'OK';
		} else {
			$feedback = 'Account creation failed!';
		}
		$stmt = null;
	}

	if($_POST['action'] == "admin_remove" && $_SESSION['UID'] == 1 && isset($_POST['uid'])) {
		if($_POST['uid'] > 1) {
			$stmt = $GLOBALS['DB']->prepare("DELETE FROM users WHERE uid=:uid");
			$stmt->bindValue(':uid', $_POST['uid'], PDO::PARAM_INT);
			if($stmt->execute()) {
				$feedback = 'OK';
				$stmt = $GLOBALS['DB']->prepare("DELETE FROM galleries WHERE uid=:uid");
				$stmt->bindValue(':uid', $_POST['uid'], PDO::PARAM_INT);
				$stmt->execute();

				$stmt = $GLOBALS['DB']->prepare("DELETE FROM pictures WHERE uid=:uid");
				$stmt->bindValue(':uid', $_POST['uid'], PDO::PARAM_INT);
				$stmt->execute();
				
				$up_dir = $GLOBALS['CONFIG']['DATAPATH'] . "/" . $_POST['uid'] . "/";
				if(is_dir($up_dir)) delTree($up_dir);
			} else {
				$feedback = 'Account deletion failed!';
			}
			$stmt = null;
		} else {
			$feedback = 'You can\'t remove the Administrator!';
		}
	}	

	if($_POST['action'] == "admin_modify" && $_SESSION['UID'] == 1 && isset($_POST['uid']) && isset($_POST['login']) && isset($_POST['email']) && isset($_POST['password'])) {
		if($_POST['password']!='') {
			$stmt = $GLOBALS['DB']->prepare("UPDATE users SET name=:name, email=:email, password=:password, salt=:salt WHERE uid=:uid");
			$salt = genRandHash($_POST['login'] . $_POST['email'] . $_POST['password']);
			$stmt->bindValue(':password', safePassword($_POST['password'], $salt), PDO::PARAM_STR);
			$stmt->bindValue(':salt'    , $salt                                  , PDO::PARAM_STR);
		} else {
			$stmt = $GLOBALS['DB']->prepare("UPDATE users SET name=:name, email=:email WHERE uid=:uid");
		}
		$stmt->bindValue(':uid'     , $_POST['uid']                          , PDO::PARAM_INT);
		$stmt->bindValue(':email'   , $_POST['email']                        , PDO::PARAM_STR);
		$stmt->bindValue(':name'    , $_POST['login']                        , PDO::PARAM_STR);
		if($stmt->execute()) {
			$feedback = 'OK';
		} else {
			$feedback = 'Account modification failed!';
		}
		$stmt = null;
	}
	
	// Picture
	
	if($_POST['action'] == "picture_remove" && $_SESSION['UID'] > 0 && isset($_POST['pid'])) {
		$stmt = $GLOBALS['DB']->prepare("SELECT uid, pkey FROM pictures WHERE uid=:uid AND pid=:pid");
		$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
		$stmt->bindValue(':pid', $_POST['pid'], PDO::PARAM_INT);
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
				$stmt->bindValue(':pid', $_POST['pid'], PDO::PARAM_INT);
				if($stmt->execute()) {
					$feedback = 'OK';
				} else {
					$feedback = 'Removing Picture #'.$_POST['pid'].' failed!';
				}
				$stmt = null;
			} else {
				$feedback = 'Picture not found!';
			}
		} else {
			$feedback = 'Unkown SQL error!';
			$stmt = null;
		}
	}

	if($_POST['action'] == "picture_modify" && $_SESSION['UID'] > 0 && isset($_POST['pid']) && isset($_POST['title']) && isset($_POST['gid'])) {
		// Check Picture Permissions
		$stmt = $GLOBALS['DB']->prepare("SELECT pid FROM pictures WHERE uid = :uid AND pid = :pid");
		$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
		$stmt->bindValue(':pid', $_POST['pid'], PDO::PARAM_INT);
		if($stmt->execute()) {
			if($row = $stmt->fetch()) {
				$pid = $row['pid'];
				$stmt = null;
				
				// Check Gallery Permissions
				if($_POST['gid'] != 0) {
					$stmt = $GLOBALS['DB']->prepare("SELECT gid FROM galleries WHERE uid = :uid AND gid = :gid");
					$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
					$stmt->bindValue(':gid', $_POST['gid'], PDO::PARAM_INT);
					if($stmt->execute()) {
						if($row = $stmt->fetch()) {
							$gid = $row['gid'];
							$stmt = null;
						} else {
							$feedback = 'Unknown Gallery';
						}
					} else {
						$feedback = 'Unknown Gallery';
					}
				} else $gid = 0;

				if(!$stmt) {
					// Update Picture
					$stmt = $GLOBALS['DB']->prepare("UPDATE pictures SET ptitle=:title, gid=:gid WHERE pid=:pid");
					$stmt->bindValue(':gid', $gid, PDO::PARAM_INT);
					$stmt->bindValue(':title', $_POST['title'], PDO::PARAM_STR);
					$stmt->bindValue(':pid', $pid, PDO::PARAM_INT);
					if($stmt->execute()) {
						$feedback = 'OK';
					} else {
						$feedback = 'Error updating picture';
					}
				}	
			} else {
				$feedback = 'Unknown Picture';
			}
		} else {
			$feedback = 'Unknown Picture';
		}
		$stmt = null;
	}

	// Gallery

	if($_POST['action'] == "gal_create" && $_SESSION['UID'] > 0 && isset($_POST['title'])) {
		$stmt = $GLOBALS['DB']->prepare("INSERT INTO galleries VALUES (null,:key,:title,:uid,0)");
		$stmt->bindValue(':key'  , genRandHash($_POST['title']), PDO::PARAM_STR);
		$stmt->bindValue(':title', $_POST['title']             , PDO::PARAM_STR);
		$stmt->bindValue(':uid'  , $_SESSION['UID']            , PDO::PARAM_INT);
		if($stmt->execute()) {
			$feedback = 'OK';
		} else {
			$feedback = 'Unkown SQL error!';
		}
		$stmt = null;
	}

	if($_POST['action'] == "gal_remove" && $_SESSION['UID'] > 0 && isset($_POST['gid'])) {
		$stmt = $GLOBALS['DB']->prepare("DELETE FROM galleries WHERE uid = :uid AND gid = :gid");
		$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
		$stmt->bindValue(':gid', $_POST['gid']   , PDO::PARAM_INT);
		if($stmt->execute()) {
			$feedback = 'OK';
			$stmt = $GLOBALS['DB']->prepare("UPDATE pictures SET gid = 0 WHERE uid = :uid AND gid = :gid");
			$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
			$stmt->bindValue(':gid', $_POST['gid']   , PDO::PARAM_INT);
			$stmt->execute();
		} else {
			$feedback = 'Unkown SQL error!';
		}
		$stmt = null;
	}

	if($_POST['action'] == "gal_modify" && $_SESSION['UID'] > 0 && isset($_POST['gid']) && isset($_POST['title'])) {
		$stmt = $GLOBALS['DB']->prepare("UPDATE galleries SET gtitle = :title WHERE uid = :uid AND gid = :gid");
		$stmt->bindValue(':uid'  , $_SESSION['UID'], PDO::PARAM_INT);
		$stmt->bindValue(':gid'  , $_POST['gid']   , PDO::PARAM_INT);
		$stmt->bindValue(':title', $_POST['title'] , PDO::PARAM_STR);
		if($stmt->execute()) {
			$feedback = 'OK';
		} else {
			$feedback = 'Unkown SQL error!';
		}
		$stmt = null;
	}
	
	// ???

}

echo $feedback;

function delTree($dir) { 
	$files = array_diff(scandir($dir), array('.','..')); 
	foreach ($files as $file) { 
		(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
	} 
	return rmdir($dir); 
} 
  
?>