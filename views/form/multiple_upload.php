<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Multiple upload form
 *
 * @package    Anhq
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
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

	<fieldset>

		<?php echo Form::csrf() ?>
		<?php echo Form::submit_wrap('save', __('Upload'), null, Arr::get($form, 'cancel'), null, Arr::get($form, 'hidden')) ?>

	</fieldset>

<?php echo Form::close(); ?>

<div id="progress-report">
	<ol id="progress-files"></ol>
	<span id="progress-report-status"></span> <span id="progress-report-name"></span>
	<div id="progress-report-bar-container" style="width: 90%; height: 5px;">
		<div id="progress-report-bar" style="background-color: blue; width: 0; height: 100%;"></div>
	</div>
</div>

<div id="progress-thumbnails"></div>

<?php echo HTML::script_source('
$(function() {

	$("#' . $field_id . '")
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
				$("#upload-" + number + " .progress").text(Math.ceil(progress * 100) + "%");
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
				$("#upload-" + number + " .progress").text(message);
			},
			setName: function(text) {
				$("#progress-report-name").text(text);
			},
			setStatus: function(text) {
				$("#progress-report-status").text(text);
			},
			setProgress: function(val) {
				$("#progress-report-bar").css("width", Math.ceil(val * 100) + "%");
			}
		})
	.bind("change", function() {
		var files = this.files;
		var total = files.length;
		var $files = $("#progress-files").empty();
		var size = 0;
		$.each(files, function(index, file) {
			size += file.fileSize;
			$files.append("<li id=\"upload-" + index + "\">" + file.fileName + ", " + Math.round(file.fileSize / 1024) + "kB <span class=\"progress\"></span></li>");
		});
		$files.append("<li>Total: " + total + " files, " + Math.round(size / 1024) + "kB</li>");
	});

	$("#form-multiple-upload").bind("submit", function() {
		$("#' . $field_id . '").trigger("html5_upload.start");
		return false;
	});

});
');
