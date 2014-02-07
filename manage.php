<?php
require_once('core.php');

if($_SESSION['UID'] == 1) {
	$statusmsg = '';
	if(isset($_POST['action']) && $_POST['action']=="Add") {
		if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
			// TBD: Do some more Checks first ... 
			
			$salt = genRandHash($_POST['name'] . $_POST['email'] . $_POST['password']);
			$stmt = $GLOBALS['DB']->prepare("INSERT INTO users VALUES (null, :email, :password, :salt, :name)");
			$stmt->bindValue(':email'   , $_POST['email']                        , PDO::PARAM_STR);
			$stmt->bindValue(':password', safePassword($_POST['password'], $salt), PDO::PARAM_STR);
			$stmt->bindValue(':salt'    , $salt                                  , PDO::PARAM_STR);
			$stmt->bindValue(':name'    , $_POST['name']                         , PDO::PARAM_STR);
			if($stmt->execute()) {
				$statusmsg = 'OK';
				header("Location: manage.php");
			} else {
				$statusmsg = 'Account creation failed!';
			}
			$stmt = null;
		}
		if($statusmsg != 'OK') {
			pageHeader();
			pageMenu();
			echo '<form method="POST">';
			echo '<table>';
			echo '<tr class="tr_header"><th colspan="2">New User</th></tr>';
			echo '<tr>';
			echo '<td>Name:</td>';
			echo '<td class="td_input"><input type="text" name="name" /></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td>Email:</td>';
			echo '<td class="td_input"><input type="text" name="email" /></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td>Password:</td>';
			echo '<td class="td_input"><input type="password" name="password" /></td>';
			echo '</tr>';
			echo '<tr><td>&nbsp;</td><td><input type="submit" name="action" value="Add" class="in_submit" /></td></tr>';
			echo '</table>';
			echo '</form>';
			if($statusmsg != '') echo $statusmsg;
		}
	} else if(isset($_POST['action']) && $_POST['action']=="Edit") {
		$stmt = $GLOBALS['DB']->prepare("SELECT * FROM users WHERE uid=:uid");
		$stmt->bindValue(':uid', $_POST['id'], PDO::PARAM_INT);
		if($stmt->execute()) {
			$row = $stmt->fetch();
		} else {
			$statusmsg = 'OK';
			header("Location: manage.php");
		}
		$stmt = null;
		
		if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
			// TBD: Do some more Checks first ... 

			if($_POST['password']!='') {
				$stmt = $GLOBALS['DB']->prepare("UPDATE users SET name=:name, email=:email, password=:password, salt=:salt WHERE uid=:uid");
				$salt = genRandHash($_POST['name'] . $_POST['email'] . $_POST['password']);
				$stmt->bindValue(':password', safePassword($_POST['password'], $salt), PDO::PARAM_STR);
				$stmt->bindValue(':salt'    , $salt                                  , PDO::PARAM_STR);
			} else {
				$stmt = $GLOBALS['DB']->prepare("UPDATE users SET name=:name, email=:email WHERE uid=:uid");
			}
			$stmt->bindValue(':uid'     , $_POST['id']                           , PDO::PARAM_INT);
			$stmt->bindValue(':email'   , $_POST['email']                        , PDO::PARAM_STR);
			$stmt->bindValue(':name'    , $_POST['name']                         , PDO::PARAM_STR);
			if($stmt->execute()) {
				$row['name'] = $_POST['name'];
				$row['email'] = $_POST['email'];
			} else {
				$statusmsg = 'Account modigfication failed!';
			}
			$stmt = null;
		}

		if($statusmsg != 'OK') {
			pageHeader();
			pageMenu('<tr><td><form method="POST"><input type="submit" name="action" value="Add" class="in_submit" /></form></td></tr>');

			echo '<form method="POST">';
			echo '<table>';
			echo '<tr class="tr_header"><th colspan="2">Edit User</th></tr>';
			echo '<tr>';
			echo '<td>Name:</td>';
			echo '<td class="td_input"><input type="text" name="name" value="'.$row['name'].'" /></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td>Email:</td>';
			echo '<td class="td_input"><input type="text" name="email" value="'.$row['email'].'" /></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td>Password:</td>';
			echo '<td class="td_input"><input type="password" name="password" value="" /></td>';
			echo '</tr>';
			echo '<tr><td>&nbsp;</td><td><input type="hidden" name="id" value="'.$row['uid'].'" /><input type="submit" name="action" value="Edit" class="in_submit" /></td></tr>';
			echo '</table>';
			echo '</form>';
			if($statusmsg != '') echo $statusmsg;
		}
	} else {
		if(isset($_POST['action']) && $_POST['action']=="Remove") {
			// TBD: Ask for confirmation!
			
			if($_POST['id'] > 1) {
				$stmt = $GLOBALS['DB']->prepare("DELETE FROM users WHERE uid=:uid");
				$stmt->bindValue(':uid', $_POST['id'], PDO::PARAM_INT);
				if(!$stmt->execute()) {
					$statusmsg = 'Account deletion failed!';
				}
				$stmt = null;
			} else {
				$statusmsg = 'Admin can not be removed!';
			}
		}

		pageHeader();
		pageMenu('<tr><td><form method="POST"><input type="submit" name="action" value="Add" class="in_submit" /></form></td></tr>');

		$stmt = $GLOBALS['DB']->prepare("SELECT uid, name, email FROM users");
		if($stmt->execute()) {
			echo '<table>';
			echo '<tr class="tr_header"><th colspan="5">User List</th></tr>';
			echo '<tr>';
			echo '<th colspan="2">&nbsp;</th>';
			echo '<th>ID</th>';
			echo '<th>Name</th>';
			echo '<th>Email</th>';
			echo '</tr>';
			while($row = $stmt->fetch()) {
				echo '<tr>';
				echo '<td><form method="POST"><input type="hidden" name="id" value="'.$row['uid'].'" /><input type="submit" name="action" value="Edit" class="in_submit" /></form></td>';
				echo '<td><form method="POST"><input type="hidden" name="id" value="'.$row['uid'].'" /><input type="submit" name="action" value="Remove" class="in_submit" /></form></td>';
				echo '<td align="right">'.$row['uid'].'</td>';
				echo '<td>'.$row['name'].'</td>';
				echo '<td>'.$row['email'].'</td>';
				echo '</tr>';
			}
			$stmt = null;
			echo '</table>';
		}
		if($statusmsg != '') echo $statusmsg;
	}
} else {
	header('Location: .');
}

pageFooter();
?>