<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Multiple images upload form.
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
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
		echo Form::open(Request::current_uri(), array('id' => 'form-multiple-upload', 'class' => 'dropzone', 'method' => 'post', 'enctype' => 'multipart/form-data'));
		echo Form::csrf();
		echo Form::close();

?>

<!--
<br>
<p class="text-center">

	<?= Form::button('upload', '<i class="fa fa-upload"></i> ' . __('Start upload'), array('type' => 'button', 'class' => 'btn btn-primary btn-lg')) ?>

</p>
-->
<?= HTML::style('//cdnjs.cloudflare.com/ajax/libs/dropzone/3.8.2/css/dropzone.css') ?>

<script>
head.ready('jquery', function() {
	head.js(
		{ 'dropzone': '//cdnjs.cloudflare.com/ajax/libs/dropzone/3.8.2/dropzone.min.js' },
		function () {
			Dropzone.options.formMultipleUpload = {
				acceptedFiles: '.gif,.jpg,.jpeg,.png',
				//			addRemoveLinks:   true,
				//			autoProcessQueue: false,
				maxFilesize:      <?= ceil(Num::bytes(Kohana::$config->load('image.filesize')) / 1024 / 1024) ?>,
				init: function () {
					//				var
					//					$submit  = $('button[name=upload]'),
					//					dropzone = this;
					//
					//				$submit.on('click', function() {
					//					dropzone.processQueue();
					//				});

					this.on('success', function (file, response) {
						var $link = $('<a class="btn btn-sm btn-block btn-default" href="' + response.gallery_url + '" />')
							.append('Go to image');

						$(file.previewTemplate).append($link);
					});
				}
			};
		}
	);
});
</script>

<?php

		return ob_get_clean();
	}

}
