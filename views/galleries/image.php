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

<?php if ($note): ?>
	<?php echo Form::open(null, array('id' => 'form-note', 'class' => 'ajaxify')) ?>
		<fieldset>
			<ul>
				<?php echo Form::input_wrap('note') ?>
			</ul>
			<?php echo Form::submit_wrap('save', __('Save'), null, '#cancel', null, array(
				'x'       => null,
				'y'       => null,
				'width'   => null,
				'height'  => null,
				'user_id' => null,
			)) ?>
		</fieldset>
	<?php echo Form::close() ?>
<?php endif; ?>
</figure>

<?php if ($image->description): ?>
<footer>
	<?php $names = array(); foreach (explode(',', $image->description) as $name) $names[] = HTML::user(trim($name)); ?>
	<?php echo __('In picture: :users', array(':users' => implode(', ', $names))) ?>
</footer>
<?php endif ?>

<?php if (true || $image->notes): ?>
<br />
<caption>
	<a href="#" class="note-add"><?php echo __('Tag people'); ?></a>
	Huomiot
</caption>
<?php endif; ?>

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
	$("a.image").notes([{ "x": 10, "y": 10, "width": 50, "height": 50, "note": "Jee!" }]);

	$("input[name=note]").autocompleteUser();

	$("a.note-add").click(function() {
		$("a.image").imgAreaSelect({
			onSelectChange: function (img, area) {
				$("#form-note")
					.css({
						left: area.x1 + "px",
						top: area.y1 + area.height + 5 + "px"
					});
					.show();

				$("#form-note input[name=x]").val(area.x1);
				$("#form-note input[name=y]").val(area.y1);
				$("#form-note input[name=width]").val(area.width);
				$("#form-note input[name=height]").val(area.height);
			},
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
		$("a.image").imgAreaSelect({ hide: true });
		$("#form-note").hide();
	});

});
');
