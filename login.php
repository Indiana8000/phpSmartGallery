<?php require_once('core.php'); startSession(); ?><!DOCTYPE html>
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

<div class="container col-xs-12 col-sm-6 col-sm-offset-3">
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Login</h3>
  </div>
  <div class="panel-body">
	<form role="form">
	  <div class="form-group" id="imp1">
	    <label for="exampleInputEmail1">Username</label>
	    <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Username">
	    <span class="glyphicon glyphicon-remove form-control-feedback hidden" id="imp1g"></span>
	  </div>
	  <div class="form-group" id="imp2">
	    <label for="exampleInputPassword1">Password</label>
	    <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
	    <span class="glyphicon glyphicon-remove form-control-feedback hidden" id="imp2g"></span>
	  </div>
	  <button type="submit" class="btn btn-primary" id="btn_submit">Submit</button>
	</form>
  </div>
</div>
</div>

<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
	$(document).ready(function() {
		$('ul.nav.navbar-nav li:first').addClass("active");
		
		$('#btn_submit').click(function(event) {
			event.preventDefault();
			$('#btn_submit').addClass("disabled");
			$.ajax({
				url: "ajax.php",
				type: "POST",
				data: {
					action: "login",
					login: $('#exampleInputEmail1').val(),
					password: $('#exampleInputPassword1').val()
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$('#btn_submit').removeClass("disabled");
					alert(errorThrown);
				},
				success: function(data, textStatus, jqXHR) {
					$('#btn_submit').removeClass("disabled");
					if(data != 'OK') {
						$('#imp1').addClass("has-error has-feedback");
						$('#imp1g').removeClass("hidden");
						$('#imp2').addClass("has-error has-feedback");
						$('#imp2g').removeClass("hidden");
						alert(data);
					} else {
						document.location.href = 'upload.php';
					}
				}
			});
		});
	});
</script>
</body>
</html>