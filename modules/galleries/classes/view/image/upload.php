<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Multiple images upload form.
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Image_Upload extends View_Section {

	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Upload form
		echo Form::open(null, array('id' => 'form-multiple-upload', 'method' => 'post', 'enctype' => 'multipart/form-data'));

?>

<div>

	<div class="droparea hero-unit"><i class="icon-arrow-down"></i> <strong><?= __('Drag and drop files here') ?></strong></div>

	<span class="btn btn-success fileinput-button">
		<i class="icon-plus icon-white"></i> <?= __('Add files...') ?>
		<?= Form::file('file[]', array('multiple' => 'multiple', 'accept' => 'image/*')) ?>
	</span>

	<?= Form::button('upload', '<i class="icon-upload icon-white"></i> ' . __('Start upload'), array('type' => 'submit', 'class' => 'btn btn-primary start')) ?>

	<div class="progress progress-success progress-striped active fade">
		<div class="bar" style="width: 0%;"></div>
	</div>

</div>


<div class="fileupload-loading"></div>

<table class="table table-striped">
	<tbody class="files"></tbody>
</table>

<?php

		echo Form::csrf();

		echo Form::close();


		// jQuery file upload
		$base = URL::base(!Request::current()->is_initial());

?>

<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
<tr class="template-upload fade">
	<td class="preview"><span class="fade"></span></td>
	<td class="name"><span>{%=file.name%}</span></td>
	<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
	{% if (file.error) { %}
	<td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
	{% } else if (o.files.valid && !i) { %}
	<td>
		<div class="progress progress-success progress-striped active"><div class="bar" style="width: 0%;"></div></div>
	</td>
	<td class="start">{% if (!o.options.autoUpload) { %}
		<button class="btn btn-primary">
			<i class="icon-upload icon-white"></i> {%=locale.fileupload.start%}
		</button>
	{% } %}</td>
	{% } else { %}
	<td colspan="2"></td>
	{% } %}
	<td class="cancel">{% if (!i) { %}
		<button class="btn btn-warning">
			<i class="icon-ban-circle icon-white"></i> {%=locale.fileupload.cancel%}
		</button>
	{% } %}</td>
</tr>
{% } %}
</script>

<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
<tr class="template-download fade">
	{% if (file.error) { %}
	<td></td>
	<td class="name"><span>{%=file.name%}</span></td>
	<td class="size">{%=o.formatFileSize(file.size)%}</td>
	<td class="error" colspan="3"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
	{% } else { %}
	<td class="preview">{% if (file.thumbnail_url) { %}
		<a href="{%=file.gallery_url%}" target="_blank"><img src="{%=file.thumbnail_url%}" /></a>
	{% } %}</td>
	<td class="name">
		<a href="{%=file.url%}" title="{%=file.name%}" target="_blank">{%=file.name%}</a>
	</td>
	<td class="size">{%=o.formatFileSize(file.size)%}</td>
	<td class="delete" colspan="3">{% if (file.delete_url) { %}
		<button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
			<i class="icon-trash icon-white"></i> {%=locale.fileupload.destroy%}
		</button>{% } %}
	</td>
	{% } %}
</tr>
{% } %}
</script>


<script>
head.ready('jquery-ui', function fileUpload() {
	head.js(
		{ 'jquery-template':         '<?= $base ?>static/js/vendor/tmpl.js' },
		{ 'jquery-load-image':       '<?= $base ?>static/js/vendor/load-image.js' },
		{ 'jquery-iframe-transport': '<?= $base ?>static/js/vendor/jquery.iframe-transport.js' },
		{ 'jquery-fileupload':       '<?= $base ?>static/js/vendor/jquery.fileupload.js' },
		{ 'jquery-fileupload-ui':    '<?= $base ?>static/js/vendor/jquery.fileupload-ui.js' },
		{ 'jquery-xdr-transport':    '<?= $base ?>static/js/vendor/jquery.xdr-transport.js' },
		function fileUploadLoaded() {

			// Localization
			window.locale = {
				'fileupload': {
					'errors': {
						'maxFileSize':      'File is too big',
						'minFileSize':      'File is too small',
						'acceptFileTypes':  'Filetype not allowed',
						'maxNumberOfFiles': 'Max number of files exceeded',
						'uploadedBytes':    'Uploaded bytes exceed file size',
						'emptyResult':      'Empty file upload result'
					},
					'cancel':  'Cancel',
					'destroy': 'Delete',
					'error':   'Error',
					'start':   'Start'
				}
			};

			// Initialize fileupload
			$('#form-multiple-upload').fileupload({
				acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
				//autoUpload: true,
				maxFileSize:     <?= Num::bytes(Kohana::$config->load('image.filesize')) ?>,
				dropZone:        $('.droparea'),
				formData:        {
					token:    $('input[name=token]').val(),
					multiple: true
				}
			});

			$(document).on('drop dragover', function disableDrop(e) {
				e.preventDefault();
			});

			$('#form-multiple-upload').on('submit', function upload(e) {
				e.preventDefault();

				$('.files .start button').click();
//				$(this).fileupload('send');
			})

		}
	);
});
</script>
<!--[if gte IE 8]><!--<script src="<?= $base ?>static/js/vendor/jquery.xdr-transport.js"></script>--><![endif]-->

<?php


		return ob_get_clean();
	}

}
