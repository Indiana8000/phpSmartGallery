$(document).ready(function() {
	$('ul.nav.navbar-nav li:nth-child(1)').addClass("active");
	if( $('#input_gallery option').size() > 1  ) {
		$('#form_gallery').removeClass("hidden");
	}
	
	$('#btn_upload_all').click(function() {
		$('.btn-info').click();
	});

	var url = 'ajax_upload.php';

	var uploadButton = $('<button/>')
		.addClass('btn btn-info btn-xs btn-block')
		.prop('disabled', true)
		.text('Processing...')
		.on('click', function () {
			var $this = $(this),
			data = $this.data();
			$this
			.off('click')
			.text('Abort')
			.on('click', function () {
				$this.text('-').prop('disabled', true).removeClass('btn-info').addClass('btn-default');
				data.abort();
			});
			data.formData = {gid: $('#input_gallery').val()};
			data.submit().always(function () {
				$this.text('-').prop('disabled', true).removeClass('btn-info').addClass('btn-default');
			});
		});

	$('#fileupload').fileupload({
		url: url,
		dataType: 'json',
		autoUpload: false,
		acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		maxFileSize: 5000000, // 5 MB
		// Enable image resizing, except for Android and Opera,
		// which actually support image resizing, but fail to
		// send Blob objects via XHR requests:
		disableImageResize: /Android(?!.*Chrome)|Opera/
			.test(window.navigator.userAgent),
		previewMaxWidth: 120,
		previewMaxHeight: 80,
		previewCrop: true
	}).on('fileuploadadd', function (e, data) {
		data.context = $('<tr/>');
		$.each(data.files, function (index, file) {
			if(!index)
			data.context
					.append($('<td/>').css('width', '128px').text(''))
					.append($('<td/>')
							.append(uploadButton.clone(true).data(data))
							.append($('<p/>').text(file.name))
							.append($('<p/>').addClass("text-warning")
								.append($('<div/>').addClass("progress progress-striped active")
									.append($('<div/>').addClass("progress-bar progress-bar-success").css('width', '0%'))
								)
							)
						);
		});
		data.context.appendTo($('#image_list > tbody:last'));
		$('.btn-primary').removeClass("hidden");
	}).on('fileuploadprocessalways', function (e, data) {
		var file = data.files[data.index];
		if (file.preview) {
			$(data.context).children('td:first').html(file.preview);
		}
		if (file.error) {
			$(data.context).children('td:last').children('p:last').text(file.error);
		}
		if (data.index + 1 === data.files.length) {
			data.context.find('button')
				.text('Upload')
				.prop('disabled', !!data.files.error);
		}
	}).on('fileuploadprogress', function (e, data) {
		if(e.isDefaultPrevented()) {
			return false;
		}
		var progress = Math.floor(data.loaded / data.total * 100);
		data.context.find('.progress')
			.attr('aria-valuenow', progress)
			.children().first().css('width',progress + '%');
	}).on('fileuploaddone', function (e, data) {
		$.each(data.result.files, function (index, file) {
			if(file.error) {
				$(data.context).children('td:last').children('p:last').text(file.error);
			} else {
				$(data.context).children('td:last').children('p:last').text('Done!');
				//$(data.context).hide();
			}
		});
	}).on('fileuploadfail', function (e, data) {
		$(data.context).children('td:last').children('p:last').text('File upload failed!');
	}).prop('disabled', !$.support.fileInput)
		.parent().addClass($.support.fileInput ? undefined : 'disabled');
});