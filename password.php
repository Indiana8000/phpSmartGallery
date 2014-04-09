<?php require_once('core.php'); checkLogin(); ?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>phpSmartGallery</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="css/lightbox.css" rel="stylesheet">
  </head>
  <body>
<?php navBar(); ?>

<div class="container col-xs-12 col-sm-6 col-sm-offset-3">
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Change Password</h3>
  </div>
  <div class="panel-body">
	<form role="form">
	  <div class="form-group">
	    <label for="input_password_old" class="sr-only">Old Password</label>
	    <input type="password" class="form-control" id="input_password_old" placeholder="Old Password">
	  </div>
	  <div class="form-group">
	    <label for="input_password_new" class="sr-only">New Password</label>
	    <input type="password" class="form-control" id="input_password_new" placeholder="New Password">
	  </div>
	  <div class="form-group">
	    <label for="input_password_verify" class="sr-only">Verify Password</label>
	    <input type="password" class="form-control" id="input_password_verify" placeholder="Verify Password">
	  </div>
	  <button class="btn btn-primary" id="btn_change">Save</button>
	</form>
  </div>
</div>
</div>

<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
	$(document).ready(function() {
		$('#btn_change').click(function(event) {
			event.preventDefault();
			
			if($('#input_password_old').val().length == 0) {
				alert('Old password is empty!');
			} else if($('#input_password_new').val().length == 0) {
				alert('New password is empty!');
			} else if($('#input_password_new').val() != $('#input_password_verify').val()) {
				alert('New password missmatch verfication!');
			} else {
				$('#btn_change').addClass("disabled");
				$.ajax({
					url: "ajax.php",
					type: "POST",
					data: {
						action: "password",
						old_pwd: $('#input_password_old').val(),
						new_pwd: $('#input_password_new').val()
					},
					error: function(jqXHR, textStatus, errorThrown) {
						$('#btn_change').removeClass("disabled");
						alert(errorThrown);
					},
					success: function(data, textStatus, jqXHR) {
						$('#btn_change').removeClass("disabled");
						if(data != 'OK') {
							alert(data);
						} else {
							$('#input_password_old').val('');
							$('#input_password_new').val('');
							$('#input_password_verify').val('');
							alert('Password changed!');
						}
					}
				});
			}
		});
	});
</script>
</body>
</html>