<?php require_once('core.php'); ?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>phpSmartGallery</title>

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="../css/lightbox.css" rel="stylesheet">
    <style>
	    .page-header{padding-top: 10px;margin-top: 0;}
	    .navbar{margin-bottom: 0;}
    </style>
  </head>
  <body>
<?php
if(isset($_GET['key'])) {
	$stmt = $GLOBALS['DB']->prepare("SELECT gid, gtitle FROM galleries WHERE gkey = :gkey");
	$stmt->bindValue(':gkey', $_GET['key'], PDO::PARAM_STR);
	if($stmt->execute()) {
		if($row = $stmt->fetch()) {
			echo '<h3 class="page-header text-center navbar-default">'.$row['gtitle'].'</h3>';
			
			$thumsize = $GLOBALS['CONFIG']['WIDTH'] . "x" . $GLOBALS['CONFIG']['HEIGHT'];
			$stmt = $GLOBALS['DB']->prepare("SELECT pkey, ptitle FROM pictures WHERE gid = :gid");
			$stmt->bindValue(':gid', $row['gid'], PDO::PARAM_INT);
			if($stmt->execute()) {
				echo '<div class="container">';
				while($row = $stmt->fetch()) {
					echo '<div class="col-xs-6 col-md-3">';
						echo '<div class="thumbnail">';
							echo '<a href="image/'.$row['pkey'].'" target="_blank" data-lightbox=="imagebox" data-title="'.htmlspecialchars($row['ptitle']).'"><img data-src="holder.js/'.$thumsize.'/text:Thumb" data-url="thumb/'.$row['pkey'].'" class="pic_image" /></a>';
							echo '<p class="text-center text-overflow" style="white-space:nowrap;overflow:hidden;margin-top:.3em;">'.htmlspecialchars($row['ptitle']).'</p>';
						echo '</div>';
					echo '</div>';
				}
				echo '</div>';
			}
		}
	}
	$stmt = null;
}
?>
<script src="../js/jquery-1.11.0.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/holder.js"></script>
<script src="../js/lightbox.min.js"></script>
<script>
	$(document).ready(function() {
		setTimeout(function () {
			$('.pic_image').each(function(index, value) {
				$(this).attr('src', $(this).attr('data-url'));
			});
		}, 100);
	});
</script>
</body>
</html>