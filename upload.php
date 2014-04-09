<?php require_once('core.php'); checkLogin(); ?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>phpSmartGallery</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/jquery.fileupload.css">
  </head>
  <body>
<?php navBar(); ?>

<div class="container col-xs-12 col-sm-6 col-sm-offset-3">
<div class="panel panel-default">
	<div class="panel-body">
	    <div class="form-group">
		    <span class="btn btn-primary btn-block fileinput-button">
		        <i class="glyphicon glyphicon-plus"></i>
		        <span>Add files...</span>
		        <!-- The file input field used as target for the file upload widget -->
		        <input id="fileupload" type="file" name="files[]" accept="image/*" multiple>
		    </span>
		</div>
	    <div class="form-group hidden" id="form_gallery">
	    	<label for="input_gallery">Gallery</label>
			<select class="form-control input-sm" id="input_gallery">
				<option value="0">-- none --</option>
				<?php
					$stmt = $GLOBALS['DB']->prepare("SELECT gid, gtitle FROM galleries WHERE uid = :uid ORDER BY gtitle");
					$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
					if($stmt->execute()) {
						while($row = $stmt->fetch()) {
							echo '<option value="'.$row['gid'].'">'.htmlspecialchars($row['gtitle']).'</option>';
						}
					}
				?>
			</select>
		</div>
		<table class="table table-condensed table-striped" id="image_list">
			<thead>
				<tr>
					<th colspan="2">Upload Queue</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		<button class="btn btn-primary btn-block hidden"><i class="glyphicon glyphicon-upload"></i> Upload all</button>
	</div>
</div>
</div>

<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>

<script src="js/jquery.ui.widget.js"></script>
<script src="js/load-image.min.js"></script>
<script src="js/canvas-to-blob.min.js"></script>

<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="js/jquery.fileupload-process.js"></script>
<script src="js/jquery.fileupload-image.js"></script>
<script src="js/jquery.fileupload-validate.js"></script>

<script src="js/upload.js"></script>
</body>
</html>