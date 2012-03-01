<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Multiple upload form
 *
 * @package    Anhq
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open(Arr::get($form, 'action'), array('id' => 'form-multiple-upload', 'method' => 'post', 'enctype' => 'multipart/form-data'));
$field_id = 'field-' . Arr::path($form, 'field.name', 'file');
$field_name = Arr::path($form, 'field.name', 'file') . '[]';
?>

	<fieldset>
		<ul>
			<?php echo Form::file_wrap(
				$field_name,
				array(
					'id'       => $field_id,
					'multiple' => 'multiple'),
				null,
				null,
				__('Allowed image types: :types, maximum image size: :size', array(
					':types' => implode(', ', Kohana::config('image.filetypes')),
					':size'  => Kohana::config('image.filesize')
				))
			) ?>
		</ul>
	</fieldset>

	<table id="progress-files">
		<thead>
			<tr>
				<th><?php echo __('Filename') ?></th>
				<th colspan="2"><?php echo __('Filesize') ?></th>
			</tr>
		</thead>
		<tbody></tbody>
		<tfoot></tfoot>
	</table>

	<fieldset>

		<?php echo Form::csrf() ?>
		<?php echo Form::submit_wrap('save', __('Upload'), null, Arr::get($form, 'cancel'), null, Arr::get($form, 'hidden')) ?>

	</fieldset>

<?php echo Form::close(); ?>

<div id="progress-report">
	<span id="progress-report-status"></span> <span id="progress-report-name"></span>
	<div class="progress-bar"><div><var></var></div></div>
</div>

<div id="progress-thumbnails"></div>

<?php echo HTML::script_source('
head.ready("jquery", function() {
	head.js(
		{ "jquery-upload": "' . URL::base() . 'js/jquery.html5_upload.js?3" },
		function upload_loaded() {
			var $field = $("#' . $field_id . '");

			$field
				.html5_upload({
					url: $("#form-multiple-upload").attr("action"),
					autostart: false,
					sendBoundary: window.FormData || $.browser.mozilla,
					fieldName: "' . $field_name . '",

					onStart: function(event, total) {
						return true;
						//return confirm("You are trying to upload " + total + " files. Are you sure?");
					},

					onStartOne: function(event, name, number, total) {
						$("#upload-" + number + " .progress").text("Uploading...");
						return true;
					},

					onProgress: function(event, progress, name, number, total) {
						progress = Math.ceil(progress * 100);
						$("#upload-" + number + " .progress").text(progress == 100 ? "Processing..." : progress + "%");
						$("#progress-report .progress-bar var").text(progress + "%");
						return true;
					},

					onFinishOne: function(event, response, name, number, total) {
						try {
							response = $.parseJSON(response);
							var message = (response.error) ? "Failed: " + response.error : "Done";
							if (response.thumbnail) {
								$("#progress-thumbnails").append(response.thumbnail);
							}
						} catch (e) {
							var message = "Failed";
						}
						$("#upload-" + number).addClass(response.error ? "error" : "done");
						$("#upload-" + number + " .progress").text(message);
					},

					setName: function(text) {
						$("#progress-report-name").text(text);
					},

					setStatus: function(text) {
						$("#progress-report-status").text(text);
					},

					setProgress: function(val) {
						$("#progress-report .progress-bar div").css("width", Math.ceil(val * 100) + "%");
					}
				})
			.on("change", function addFiles() {
				$("#progress-files").show();
				var files = this.files;
				var total = files.length;
				var $files = $("#progress-files tbody").empty();
				var size = 0;
				$.each(files, function(index, file) {
					size += file.fileSize;
					$files.append("<tr id=\"upload-" + index + "\"><td>" + file.fileName + "</td><td>" + Math.round(file.fileSize / 1024) + "kB</td><td class=\"progress\"></td></tr>");
				});
				$("#progress-files tfoot").html("<tr><th>" + total + " file(s)</th><th colspan=\"2\">" + Math.round(size / 1024) + "kB</th></tr>");
			});

			$("#form-multiple-upload").on("submit", function uploadFiles(event) {
				event.preventDefault();

				$("#progress-report").show();
				$field.trigger("html5_upload.start");

				return false;
			});

		}
	);
});
');
