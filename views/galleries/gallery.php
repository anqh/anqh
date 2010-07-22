<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

/** @var  Model_Gallery $gallery  */

$approve = isset($approval) && !is_null($approval) ? 'approve' : '';
$images = $approve
	? $gallery->find_images_pending($approval ? null : $user)
	: $gallery->find_images();

if ($approve && $approval) echo Form::open(null, array('id' => 'form-image-approval'));
?>

<ul>

	<?php foreach ($images as $image): ?>

	<li class="unit size1of3">
		<div class="thumb">
			<?php echo HTML::anchor(
				Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => $approve)),
				HTML::image($image->get_url('thumbnail', $gallery->dir)),
					$image->description ? array('title' => HTML::chars($image->description)) : null) ?>
		</div>
		<?php if (!isset($approval)): ?>

			<?php echo HTML::icon_value(array(':comments' => $image->comment_count), ':comments comment', ':comments comments', 'posts') ?>
			<?php echo HTML::icon_value(array(':views' => $image->view_count), ':views view', ':views views', 'views') ?><br />

		<?php elseif ($approval): $field_id = 'field-image-id-' . $image->id; ?>

			<?php echo Form::radio('image_id[' . $image->id . ']', 'approve', null, array('id' => $field_id . '-approve', 'class' => 'image-approve')) ?>
			<?php echo Form::label($field_id . '-approve', __('Approve'), array('title' => __('Approve'))) ?>

			<?php echo Form::radio('image_id[' . $image->id . ']', 'deny', null, array('id' => $field_id . '-deny', 'class' => 'image-deny')) ?>
			<?php echo Form::label($field_id . '-deny', __('Deny'), array('title' => __('Deny'))) ?>

			<?php echo Form::radio('image_id[' . $image->id . ']', 'wait', true, array('id' => $field_id . '-wait', 'class' => 'image-wait')) ?>
			<?php echo Form::label($field_id . '-wait', __('Wait'), array('title' => __('Wait'))) ?>

		<?php endif; ?>
	</li>

	<?php endforeach; ?>

	<?php if ($approve && $approval) echo '<li class="unit size1of1">' . Form::radios_wrap(
		'all',
		array(
			'approve' => __('Approve'),
			'deny'    => __('Deny'),
			'wait'    => __('Wait'),
		),
		null,
		null,
		__('All images'),
		null,
		null,
		'horizontal'
	) ?>

</ul>
<?php
if ($approve && $approval):
	echo Form::csrf();
	echo HTML::icon_value(array(':images' => 0), __('Approved'), null, 'approve') . ' ';
	echo HTML::icon_value(array(':images' => 0), __('Denied'), null, 'deny') . ' ';
	echo HTML::icon_value(array(':images' => count($images)), __('Waiting'), null, 'wait') . '<br />';
	echo Form::submit_wrap('approve', __('Save'), null, Route::get('galleries')->uri(array('action' => 'approval')));
	echo Form::close();

	echo HTML::script_source('
$(function() {

	$("form input[name=all]").change(function() {
		$("form input[value=" + $(this).val() + "]").attr("checked", true);

		$.each([ "approve", "deny", "wait" ], function() {
			$("var.icon." + this).text($("input[name!=all][value=" + this + "]:checked").length);
		});
	});

	$("form input[name^=image_id]").change(function() {
		$(this).closest("li")
			.toggleClass("approved", $(this).val() == "approve" && $(this).attr("checked"))
			.toggleClass("denied", $(this).val() == "deny" && $(this).attr("checked"));

		$.each([ "approve", "deny", "wait" ], function() {
			$("var.icon." + this).text($("input[name!=all][value=" + this + "]:checked").length);
		});
	});

});
');
endif;
