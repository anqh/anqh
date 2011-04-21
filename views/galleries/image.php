<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$approve = isset($approve) && !is_null($approve) ? 'approve' : '';

$note_array = array();
if (count($notes)):
	foreach ($notes as $noted):
		$noted_user   = $noted->user();
		$note_array[] = array(
			'id'     => (int)$noted->id,
			'x'      => (int)$noted->x,
			'y'      => (int)$noted->y,
			'width'  => (int)$noted->width,
			'height' => (int)$noted->height,
			'name'   => $noted_user ? $noted_user['username'] : $noted->name,
			'url'    => $noted_user ? URL::base() . URL::user($noted_user) : null
		);
	endforeach;
endif;
?>

<nav>

	<?php if ($previous): ?>
	<?php echo HTML::anchor(
		Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $previous->id, 'action' => $approve)),
		'&laquo; ' . __('Previous'),
		array('title' => __('Previous image'), 'class' => 'prev')) ?>
	<?php else: ?>
	&laquo; <?php echo __('Previous') ?>
	<?php endif ?>

	<?php echo __(':current of :total', array(':current' => $current, ':total' => $images)) ?>

	<?php if ($next): ?>
	<?php echo HTML::anchor(
		Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $next->id, 'action' => $approve)),
		__('Next') . ' &raquo;',
		array('title' => __('Next image'), 'class' => 'next')) ?>
	<?php else: ?>
	<?php echo __('Next') ?> &raquo;
	<?php endif ?>

</nav>

<figure>

<?php if ($next):
	echo HTML::anchor(
		Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $next->id, 'action' => $approve)),
		HTML::image($image->get_url(null, $gallery->dir)),
		array('title' => __('Next image'), 'class' => 'image'));
else:
	echo HTML::anchor(
		Route::model($gallery, $approve),
		HTML::image($image->get_url(null, $gallery->dir)),
		array('title' => __('Back to gallery'), 'class' => 'image'));
endif; ?>

<?php if ($note || $notes): ?>
	<figcaption class="notes icon tag">
		<ul>

			<?php $i = 0; if ($notes) foreach ($notes as $noted): $i++; $noted_user = $noted->user(); $name = $noted_user ? $noted_user['username'] : $noted->name; ?>
			<li>
				<?php if ($noted_user): ?>
					<?php echo HTML::user($noted_user, null, array('data-note-id' => $noted->id)); ?>
				<?php else: ?>
					<span data-note-id="<?php echo $noted->id; ?>"><?php echo HTML::chars($name); ?></span>
				<?php endif; ?>

				<?php if (Permission::has($noted, Model_Image_Note::PERMISSION_DELETE, $user)): ?>
					<?php echo HTML::anchor(
						Route::get('image_note')->uri(array('id' => $noted->id, 'action' => 'unnote')),
						'&#215;',
						array('class' => 'note-delete', 'data-confirm' => __('Delete note'), 'title' => __('Delete note'))
					) ?>
				<?php endif; ?>

				<?php if ($i < count($notes)) echo ',' ?>
			</li>
			<?php endforeach; // Notes list ?>

			<?php if ($note): ?>
			<li><a href="#" class="action small note-add"><?php echo __('Tag people'); ?></a></li>
			<?php endif; ?>

		</ul>
	</figcaption>

	<?php echo Form::open(
		Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => 'note')),
		array('id' => 'form-note'/*, 'class' => 'ajaxify'*/)
	) ?>
		<fieldset>
			<ul>
				<?php echo Form::input_wrap('name') ?>
			</ul>
			<?php echo Form::submit_wrap('save', __('Save'), null, '#cancel', null, array(
				'x'       => null,
				'y'       => null,
				'width'   => null,
				'height'  => null,
				'user_id' => null,
			)) ?>
		</fieldset>
	<?php echo Form::close() // Note form ?>

<?php endif; // Notes ?>

</figure>

<?php
echo HTML::script_source('

// Keyboard navigation
head.ready("jquery-ui", function() {
	$(document).keyup(function(e) {
		var key = e.keyCode || e.which;
		if (e.target.type === undefined) {
			switch (key) {
				case 37: var link = $(".gallery-image a.prev").first().attr("href"); break;
				case 39: var link = $(".gallery-image a.next").first().attr("href"); break;
			}
			if (link) {
				window.location = link;
			}
		}
	});
});


// Notes
head.ready("anqh", function() {
	$("a.image").notes(' . json_encode($note_array) . ');

	$("input[name=name]").autocompleteUser();

	$("a.note-add").click(function() {

		function updateForm(img, area) {
			$("#form-note")
				.css({
					left: area.x1 + "px",
					top: area.y1 + area.height + 5 + "px"
				})
				.show();

			$("#form-note input[name=x]").val(area.x1);
			$("#form-note input[name=y]").val(area.y1);
			$("#form-note input[name=width]").val(area.width);
			$("#form-note input[name=height]").val(area.height);
		}

		$("a.image img").imgAreaSelect({
			onInit: updateForm,
			onSelectChange: updateForm,
			handles: true,
			persistent: true,
			minWidth: 50,
			minHeight: 50,
			maxWidth: 150,
			maxHeight: 150,
			x1: parseInt($("a.image").width() / 2) - 50,
			y1: parseInt($("a.image").height() / 2) - 50,
			x2: parseInt($("a.image").width() / 2) + 50,
			y2: parseInt($("a.image").height() / 2) + 50
		});

		return false;
	});

	$("#form-note a.cancel").click(function() {
		$("a.image img").imgAreaSelect({ hide: true });
		$("#form-note").hide();
	});

});
');
