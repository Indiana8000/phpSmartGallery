<?php require_once('core.php'); checkLogin(1); ?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>phpSmartGallery</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
  </head>
  <body>
<?php navBar(); ?>

<!-- List Pannel -->
<div class="container col-xs-12 col-sm-6 col-sm-offset-3" id="admin_list">
<div class="panel panel-default">
<table class="table table-condensed table-striped table-hover">
<thead>
	<tr>
		<th>ID</th>
		<th>Username</th>
		<th>Email</th>
		<th>Pictures</th>
		<th class="text-right"><button type="button" class="btn btn-primary btn-xs" id="admin_btn_new"><span class="glyphicon glyphicon-plus"></span> New</button></th>
	</tr>
</thead>
<tbody>
<?php
	if($_SESSION['UID'] == 1) {
		$stmt = $GLOBALS['DB']->prepare("SELECT uid, name, email FROM users");
		if($stmt->execute()) {
			while($row = $stmt->fetch()) {
				echo '<tr data-uid="'.$row['uid'].'">';
					echo '<td class="text-right">'.$row['uid'].'</td>';
					echo '<td>'.$row['name'].'</td>';
					echo '<td>'.$row['email'].'</td>';
					
					foreach ($GLOBALS['DB']->query("SELECT count(*) count FROM pictures WHERE uid = ".$row['uid']) as $row2)
						echo '<td class="text-right"><span class="badge">'.$row2['count'].'</span></td>';

					echo '<td class="text-right">';
					echo '<button type="button" class="btn btn-info   btn-xs admin_btn_edit"><span class="glyphicon glyphicon-edit"></span> Edit</button>&nbsp;';
					echo '<button type="button" class="btn btn-danger btn-xs admin_btn_remove"><span class="glyphicon glyphicon-remove"></span> Remove</button>';
					echo '</td>';
				echo '</tr>';
			}
		}
	}
?>
</tbody>
</table>
</div>
</div>

<!-- NEW/MODIFY Pannel -->
<div class="container col-xs-12 col-sm-6 col-sm-offset-3 hidden" id="admin_new">
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title" id="admin_new_title">New User</h3>
  </div>
  <div class="panel-body">
	<form role="form">
	  <div class="form-group" id="form_username">
	    <label for="input_username">Username</label>
	    <input type="text" class="form-control" id="input_username" placeholder="Username" />
	  </div>
	  <div class="form-group" id="form_email">
	    <label for="input_email">Email</label>
	    <input type="email" class="form-control" id="input_email" placeholder="Email" />
	  </div>
	  <div class="form-group" id="form_password">
	    <label for="input_password">Password</label>
	    <input type="password" class="form-control" id="input_password" placeholder="Password" />
	    <p class="help-block hidden" id="admin_help_modify">Leave password blank if you don't want to change it.</p>
	  </div>
	  <button type="submit" class="btn btn-primary" id="admin_btn_create">Create</button>
	  <button type="submit" class="btn btn-primary hidden" id="admin_btn_modify">Modify</button>
	  <button type="button" class="btn btn-info" id="admin_btn_back">Back</button>
	</form>
  </div>
</div>
</div>


<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
	$(document).ready(function() {
		$('ul.nav.navbar-nav li:nth-child(4)').addClass("active");

		$('#admin_btn_new').click(function(event) {
			event.preventDefault();
			$('#admin_list').addClass("hidden");
			$('#admin_new').removeClass("hidden");
			$('#admin_btn_create').removeClass("hidden");
			$('#admin_btn_modify').addClass("hidden");
			$('#admin_help_modify').addClass("hidden");
			$('#admin_new_title').text("New User");
			$('#input_username').val("");
			$('#input_email').val("");
			$('#input_password').val("");
		});

		$('#admin_btn_back').click(function(event) {
			event.preventDefault();
			$('#admin_new').addClass("hidden");
			$('#admin_list').removeClass("hidden");
		});

		$('#admin_btn_create').click(function(event) {
			event.preventDefault();
			$('#admin_btn_create').addClass("disabled");
			$.ajax({
				url: "ajax.php",
				type: "POST",
				data: {
					action: "admin_create",
					login: $('#input_username').val(),
					email: $('#input_email').val(),
					password: $('#input_password').val()
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$('#admin_btn_create').removeClass("disabled");
					alert(errorThrown);
				},
				success: function(data, textStatus, jqXHR) {
					if(data != 'OK') {
						$('#admin_btn_create').removeClass("disabled");
						alert(data);
					} else {
						document.location.href = 'admin.php';
					}
				}
			});
		});

		$('.admin_btn_remove').click(function(event) {
			event.preventDefault();
			if($(this).closest('tr').attr('data-uid') > 1) {
				if(confirm('Please confirm to remove "' + $(this).closest('tr').children(':nth-child(2)').text() + '" and all pictures!')) {
					tmp_row = $(this).closest('tr');
					$.ajax({
						url: "ajax.php",
						type: "POST",
						data: {
							action: "admin_remove",
							uid: $(this).closest('tr').attr('data-uid')
						},
						error: function(jqXHR, textStatus, errorThrown) {
							alert(errorThrown);
						},
						success: function(data, textStatus, jqXHR) {
							if(data != 'OK') {
								alert(data);
							} else {
								$(tmp_row).fadeOut("slow").remove();
							}
						}
					});
				}
			} else {
				alert('You can\'t remove the Administrator!');
			}
		});

		$('.admin_btn_edit').click(function(event) {
			event.preventDefault();
			$('#admin_list').addClass("hidden");
			$('#admin_new').removeClass("hidden");
			$('#admin_btn_create').addClass("hidden");
			$('#admin_btn_modify').removeClass("hidden");
			$('#admin_help_modify').removeClass("hidden");
			$('#admin_new_title').text("Modify User");
			$('#input_username').val($(this).closest('tr').children(':nth-child(2)').text());
			$('#input_email').val($(this).closest('tr').children(':nth-child(3)').text());
			$('#input_password').val("");
			$('#input_password').attr('data-uid', $(this).closest('tr').attr('data-uid'));
		});

		$('#admin_btn_modify').click(function(event) {
			event.preventDefault();
			$('#admin_btn_modify').addClass("disabled");
			$.ajax({
				url: "ajax.php",
				type: "POST",
				data: {
					action: "admin_modify",
					uid: $('#input_password').attr('data-uid'),
					login: $('#input_username').val(),
					email: $('#input_email').val(),
					password: $('#input_password').val()
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$('#admin_btn_modify').removeClass("disabled");
					alert(errorThrown);
				},
				success: function(data, textStatus, jqXHR) {
					if(data != 'OK') {
						$('#admin_btn_modify').removeClass("disabled");
						alert(data);
					} else {
						document.location.href = 'admin.php';
					}
				}
			});
		});

	});
</script>
</body>
</html>