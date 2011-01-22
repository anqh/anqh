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

<?php if ($next): ?>
<?php echo HTML::anchor(
	Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $next->id, 'action' => $approve)),
	HTML::image($image->get_url(null, $gallery->dir)),
	array('title' => __('Next image'), 'class' => 'image')) ?>
<?php else: ?>
<?php echo HTML::anchor(
	Route::model($gallery, $approve),
	HTML::image($image->get_url(null, $gallery->dir)),
	array('title' => __('Back to gallery'), 'class' => 'image')) ?>
<?php endif ?>

<?php if ($image->description): ?>
	<?php $names = array(); foreach (explode(',', $image->description) as $name) $names[] = HTML::user(trim($name)); ?>
<footer>
	<?php echo __('In picture: :users', array(':users' => implode(', ', $names))) ?>
</footer>
<?php
endif;

echo HTML::script_source('
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
');
