<?php require_once('core.php'); checkLogin(); ?><!DOCTYPE html>
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
<div class="container col-xs-12 col-sm-6 col-sm-offset-3" id="gal_list">
<div class="panel panel-default">
<table class="table table-condensed table-striped table-hover">
<thead>
	<tr>
		<th>Title</th>
		<th>Pictures</th>
		<th class="text-right"><button type="button" class="btn btn-primary btn-xs" id="gal_btn_new"><span class="glyphicon glyphicon-plus"></span> New</button></th>
	</tr>
</thead>
<tbody>
<?php
	if($_SESSION['UID'] > 0) {
		$stmt = $GLOBALS['DB']->prepare("SELECT gid, gkey, gtitle FROM galleries WHERE uid = :uid ORDER BY gtitle");
		$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
		if($stmt->execute()) {
			while($row = $stmt->fetch()) {
				echo '<tr data-gid="'.$row['gid'].'">';
					echo '<td>'.htmlspecialchars($row['gtitle']).'</td>';
					foreach ($GLOBALS['DB']->query("SELECT count(*) count FROM pictures WHERE uid = ".$_SESSION['UID']." AND gid = ".$row['gid']) as $row2)
						echo '<td class="text-right"><span class="badge">'.$row2['count'].'</span></td>';
					echo '<td class="text-right">';
					echo '<button type="button" class="btn btn-info   btn-xs gal_btn_edit"><span class="glyphicon glyphicon-edit"></span> Edit</button>&nbsp;';
					echo '<button type="button" class="btn btn-danger btn-xs gal_btn_remove"><span class="glyphicon glyphicon-remove"></span> Remove</button>';
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
<div class="container col-xs-12 col-sm-6 col-sm-offset-3 hidden" id="gal_new">
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title" id="gal_new_title">New Gallery</h3>
  </div>
  <div class="panel-body">
	<form role="form">
	  <div class="form-group" id="form_title">
	    <label for="input_title">Title</label>
	    <input type="text" class="form-control" id="input_title" placeholder="Title" />
	  </div>
	  <button type="submit" class="btn btn-primary" id="gal_btn_create">Create</button>
	  <button type="submit" class="btn btn-primary hidden" id="gal_btn_modify">Modify</button>
	  <button type="button" class="btn btn-info" id="gal_btn_back">Back</button>
	</form>
  </div>
</div>
</div>

<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
	$(document).ready(function() {
		$('ul.nav.navbar-nav li:nth-child(3)').addClass("active");
		
		$('tr td:first-child').click(function() {
			if($(this).parent().attr('data-gid') > 0) {
				document.location.href = 'picture.php?g=' + $(this).parent().attr('data-gid');
			}
			
		}).css("cursor", "pointer");

		$('#gal_btn_new').click(function(event) {
			event.preventDefault();
			$('#gal_list').addClass("hidden");
			$('#gal_new').removeClass("hidden");
			$('#gal_btn_create').removeClass("hidden");
			$('#gal_btn_modify').addClass("hidden");
			$('#gal_new_title').text("New Gallery");
			$('#input_title').val("");
		});

		$('#gal_btn_back').click(function(event) {
			event.preventDefault();
			$('#gal_new').addClass("hidden");
			$('#gal_list').removeClass("hidden");
		});		

		$('#gal_btn_create').click(function(event) {
			event.preventDefault();
			$('#gal_btn_create').addClass("disabled");
			$.ajax({
				url: "ajax.php",
				type: "POST",
				data: {
					action: "gal_create",
					title: $('#input_title').val()
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$('#gal_btn_create').removeClass("disabled");
					alert(errorThrown);
				},
				success: function(data, textStatus, jqXHR) {
					if(data != 'OK') {
						$('#gal_btn_create').removeClass("disabled");
						alert(data);
					} else {
						document.location.href = 'gallery.php';
					}
				}
			});
		});		

		$('.gal_btn_remove').click(function(event) {
			event.preventDefault();
			tmp_row = $(this).closest('tr');
			if(confirm('Please confirm to remove "' + $(this).closest('tr').children(':nth-child(1)').text() + '"! Pictures will be moved.'))
			$.ajax({
				url: "ajax.php",
				type: "POST",
				data: {
					action: "gal_remove",
					gid: $(this).closest('tr').attr('data-gid')
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
		});		

		$('.gal_btn_edit').click(function(event) {
			event.preventDefault();
			$('#gal_list').addClass("hidden");
			$('#gal_new').removeClass("hidden");
			$('#gal_btn_create').addClass("hidden");
			$('#gal_btn_modify').removeClass("hidden");
			$('#gal_new_title').text("Modify Gallery");
			$('#input_title').val($(this).closest('tr').children(':first').text());
			$('#input_title').attr('data-gid', $(this).closest('tr').attr('data-gid'));
		});		

		$('#gal_btn_modify').click(function(event) {
			event.preventDefault();
			$('#gal_btn_modify').addClass("disabled");
			$.ajax({
				url: "ajax.php",
				type: "POST",
				data: {
					action: "gal_modify",
					gid: $('#input_title').attr('data-gid'),
					title: $('#input_title').val()
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$('#gal_btn_modify').removeClass("disabled");
					alert(errorThrown);
				},
				success: function(data, textStatus, jqXHR) {
					if(data != 'OK') {
						$('#gal_btn_modify').removeClass("disabled");
						alert(data);
					} else {
						document.location.href = 'gallery.php';
					}
				}
			});
		});

	});
</script>
</body>
</html>