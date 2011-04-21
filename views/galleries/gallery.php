<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

/** @var  Model_Gallery  $gallery */
/** @var  Model_Image    $image */

$images = $pending
	? $gallery->find_images_pending($approve ? null : $user)
	: $gallery->images();

if ($pending) echo Form::open(null, array('id' => 'form-image-approval'));

$copyright = $multiple = null;
?>

	<?php foreach ($images as $image): ?>

		<?php if ($image->author_id != $copyright): $copyright = $image->author_id; ?>
			<?php if ($multiple): ?>

</ul>

			<?php else: $multiple = true; endif; ?>

<header><?php echo '&copy; ' . HTML::user($copyright) ?></header>
<ul class="line">

		<?php endif; ?>

	<li class="grid2<?php echo Text::alternate(' first', '', '', '') ?>">
		<figure class="thumb">
			<?php echo HTML::anchor(
				Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => $pending ? 'approve' : '')),
				HTML::image($image->get_url('thumbnail', $gallery->dir)),
					$image->description ? array('title' => HTML::chars($image->description)) : null) ?>
			<?php if (!$pending): ?>

			<figcaption>
				<?php echo HTML::icon_value(array(':comments' => (int)$image->comment_count), ':comments comment', ':comments comments', 'posts') ?>
				<?php echo HTML::icon_value(array(':views' => (int)$image->view_count), ':views view', ':views views', 'views') ?><br />
			</figcaption>

			<?php endif; ?>

		</figure>

		<?php if ($pending): $field_id = 'field-image-id-' . $image->id; ?>

			<?php if ($approve): ?>
			<?php echo Form::radio('image_id[' . $image->id . ']', 'approve', null, array('id' => $field_id . '-approve', 'class' => 'image-approve')) ?>
			<?php echo Form::label($field_id . '-approve', __('Approve'), array('title' => __('Approve'))) ?>
			<?php endif; ?>

			<?php echo Form::radio('image_id[' . $image->id . ']', 'deny', null, array('id' => $field_id . '-deny', 'class' => 'image-deny')) ?>
			<?php echo Form::label($field_id . '-deny', $approve ? __('Deny') : __('Delete'), array('title' => $approve ? __('Deny') : __('Delete'))) ?>

			<?php echo Form::radio('image_id[' . $image->id . ']', 'wait', true, array('id' => $field_id . '-wait', 'class' => 'image-wait')) ?>
			<?php echo Form::label($field_id . '-wait', __('Wait'), array('title' => __('Wait'))) ?>

		<?php endif; ?>
	</li>

	<?php endforeach; ?>

	<?php if ($pending) echo '<li class="unit size1of1">' . Form::radios_wrap(
		'all',
		$approve
			? array('approve' => __('Approve'), 'deny' => __('Deny'), 'wait' => __('Wait'))
			: array('deny' => __('Delete'), 'wait' => __('Wait')),
		null,
		null,
		__('All images'),
		null,
		null,
		'horizontal'
	) ?>

</ul>
<?php
if ($pending):
	echo Form::csrf();
	if ($approve) echo HTML::icon_value(array(':images' => 0), __('Approved'), null, 'approve') . ' ';
	echo HTML::icon_value(array(':images' => 0), $approve ? __('Denied') : __('Deleted'), null, 'deny') . ' ';
	echo HTML::icon_value(array(':images' => count($images)), __('Waiting'), null, 'wait') . '<br />';
	echo Form::submit_wrap('approve', __('Save'), null, Route::get('galleries')->uri(array('action' => 'approval')));
	echo Form::close();

	echo HTML::script_source('
head.ready("jquery", function() {

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
