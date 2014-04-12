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
    <style>
	    .page-header{padding-top: 10px;margin-top: 0; margin-bottom: 0;}
	    .navbar{margin-bottom: 0;}
    </style>
  </head>
  <body>
<?php navBar();

$gallery = 0;
if(isset($_GET['g'])) {
	$stmt = $GLOBALS['DB']->prepare("SELECT gid, gtitle, gkey FROM galleries WHERE uid = :uid AND gid = :gid");
	$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
	$stmt->bindValue(':gid', intval($_GET['g']), PDO::PARAM_INT);
	if($stmt->execute()) {
		if($row = $stmt->fetch()) {
			$gallery = $row['gid'];
			echo '<h3 class="page-header text-center navbar-default">'.$row['gtitle'].'<br/><a href="gallery/'.$row['gkey'].'" target="_blank"><small>Public Link</small></a></h3>';
		}
	}
}

$stmt = $GLOBALS['DB']->prepare("SELECT pid, pkey, ptitle FROM pictures WHERE uid=:uid AND gid = :gid");
$stmt->bindValue(':uid', $_SESSION['UID'], PDO::PARAM_INT);
$stmt->bindValue(':gid', $gallery, PDO::PARAM_INT);
if($stmt->execute()) {
	$thumsize = $GLOBALS['CONFIG']['WIDTH'] . "x" . $GLOBALS['CONFIG']['HEIGHT'];
	echo '<div class="container" style="margin-top: 20px;">';
	while($row = $stmt->fetch()) {
		echo '<div class="col-xs-6 col-md-3">';
			echo '<div class="thumbnail">';
				echo '<a href="image/'.$row['pkey'].'" target="_blank" data-lightbox=="imagebox" data-title="'.htmlspecialchars($row['ptitle']).'"><img data-src="holder.js/'.$thumsize.'/text:Thumb" alt="'.htmlspecialchars($row['ptitle']).'" data-url="thumb/'.$row['pkey'].'" class="pic_image" /></a>';
				echo '<p class="text-center text-overflow" style="white-space:nowrap;overflow:hidden;margin-top:.3em;">'.htmlspecialchars($row['ptitle']).'</p>';
				
				echo '<div class="btn-group-vertical btn-block" data-pid="'.$row['pid'].'">';
				echo '<button class="btn btn-info   btn-xs btn-block pic_edit"><span class="glyphicon glyphicon-edit"></span> Edit</button>';
				echo '<button class="btn btn-danger btn-xs btn-block pic_remove"><span class="glyphicon glyphicon-remove"></span> Remove</button>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	}
	echo '</div>';
}
$stmt = null;
?>

<!-- MODIFY Pannel -->
<div class="container col-xs-12 col-sm-6 col-sm-offset-3 hidden" id="pic_edit">
<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title" id="pic_edit_title">Modify Picture</h3>
	</div>
	<div class="panel-body">
	<form role="form">
		<div class="form-group">
			<label for="input_title">Title</label>
			<input type="text" class="form-control" id="input_title" placeholder="Title" />
		</div>
		<div class="form-group">
			<label for="input_gallery">Gallery</label>
			<select class="form-control" id="input_gallery">
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
		<div class="form-group">
			<label for="output_link1">Public Links (Direct / HTML / BBCode)</label>
			<input type="text" class="form-control input-sm" id="output_link1" value="" />
			<input type="text" class="form-control input-sm" id="output_link2" value="" />
			<input type="text" class="form-control input-sm" id="output_link3" value="" />
		</div>
		<button class="btn btn-primary" id="pic_edit_modify">Modify</button>
		<button class="btn btn-info" id="pic_edit_back">Back</button>
	</form>
	</div>
</div>
</div>

<script src="js/jquery-1.11.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/holder.js"></script>
<script src="js/lightbox.min.js"></script>
<script>
	$(document).ready(function() {
		$('ul.nav.navbar-nav li:nth-child(2)').addClass("active");
		var gallery = <?php echo $gallery; ?>;
		var pic_edit_obj;

		$('.pic_edit').click(function(event) {
			event.preventDefault();
			
			pic_edit_obj = $(this).closest('div').parent();
			$('#input_title').val(pic_edit_obj.find('p').text());
			$('#input_title').attr('data-pid', $(this).closest('div').attr('data-pid'));
			$('#input_gallery').val(gallery);
			
			
			var dirname = $(location).attr('href').substring(0, $(location).attr('href').indexOf($(location).attr('pathname'))) + "/";
			var img_big = dirname + pic_edit_obj.find('a').attr('href');
			var img_thumb = img_big.replace('/image/', '/thumb/');
			$('#output_link1').val(img_big);
			$('#output_link2').val('<a href="'+img_big+'"><img src="'+img_thumb+'"/></a>');
			$('#output_link3').val('[url='+img_big+'][img]'+img_thumb+'[/img][/url]');

			$('.thumbnail').addClass('hidden');
			$('#pic_edit').removeClass('hidden');
		});
		
		$('#pic_edit_back').click(function(event) {
			event.preventDefault();
			$('#pic_edit').addClass('hidden');
			$('.thumbnail').removeClass('hidden');
		});

		$('#pic_edit_modify').click(function(event) {
			event.preventDefault();
			$(this).prop('disabled', true);
			var $this = $(this);

			$.ajax({
				url: "ajax.php",
				type: "POST",
				data: {
					action: "picture_modify",
					pid: $('#input_title').attr('data-pid'),
					title: $('#input_title').val(),
					gid: $('#input_gallery').val()
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$this.prop('disabled', false);
					alert(errorThrown);
				},
				success: function(data, textStatus, jqXHR) {
					$this.prop('disabled', false);
					if(data != 'OK') {
						alert(data);
					} else {
						if(gallery != $('#input_gallery').val()) {
							pic_edit_obj.parent().fadeOut("slow").remove();
						} else {
							pic_edit_obj.find('p').text($('#input_title').val());
						}
						$('#pic_edit').addClass('hidden');
						$('.thumbnail').removeClass('hidden');
					}
				}
			});
		});
		
		$('.pic_remove').click(function(event) {
			event.preventDefault();
			tmp_row = $(this).closest('div').parent().parent();
			$(this).prop('disabled', true);
			var $this = $(this);
	
			if(confirm('Are you sure to remove picture '+$(this).closest('div').parent().find('p').text()+'?')) {
			$.ajax({
				url: "ajax.php",
				type: "POST",
				data: {
					action: "picture_remove",
					pid: $(this).closest('div').attr('data-pid')
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$this.prop('disabled', false);
					alert(errorThrown);
				},
				success: function(data, textStatus, jqXHR) {
					if(data != 'OK') {
						$this.prop('disabled', false);
						alert(data);
					} else {
						$(tmp_row).fadeOut("slow").remove();
					}
				}
			});
			} else { $(this).prop('disabled', false); }
		});

		setTimeout(function () {
			$('.pic_image').each(function(index, value) {
				$(this).attr('src', $(this).attr('data-url'));
			});
		}, 100);
	});
</script>
</body>
</html>